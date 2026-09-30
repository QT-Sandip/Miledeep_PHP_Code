<?php

include_once __DIR__ . '/CentralAuditLogger.php';

class AutoAuditPDO extends PDO
{
    private $autoAuditSuspended = 0;
    private $moduleName;
    private $auditLogger;
    private $requestBody;
    private $primaryKeyCache = [];
    private $manualAuditFileCache = [];
    private $excludedTables = [
        'audit_log',
        'audit_log_archive',
        'audit_log_outbox',
        'audit_log_retention_runs',
    ];

    public function __construct($dsn, $username = null, $password = null, $options = null)
    {
        parent::__construct($dsn, $username, $password, is_array($options) ? $options : []);
        $this->moduleName = $this->resolveModuleName();
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['AutoAuditPDOStatement', [$this]]);
    }

    public function beginMutationAudit($sql, array $params = [])
    {
        if ($this->isAuditSuspended()) {
            return null;
        }

        if ($this->isManualAuditControllerActive()) {
            return null;
        }

        $materializedSql = $this->materializeSql($sql, $params);
        $parsed = $this->parseMutationSql($materializedSql);
        if ($parsed === null || $this->isExcludedTable($parsed['table'])) {
            return null;
        }

        $oldRows = [];
        if ($parsed['action'] === 'UPDATE' || $parsed['action'] === 'DELETE') {
            $oldRows = $this->fetchRowsForClause($parsed['table'], $parsed['where']);
        }

        return [
            'parsed' => $parsed,
            'oldRows' => $oldRows,
            'requestBody' => $this->getRequestBody(),
            'sql' => $materializedSql,
        ];
    }

    public function completeMutationAudit($context)
    {
        if ($context === null) {
            return;
        }

        $parsed = $context['parsed'];
        $requestBody = $context['requestBody'];
        $table = $parsed['table'];

        $this->suspendAudit();
        try {
            $logger = $this->getAuditLogger();
            if ($parsed['action'] === 'INSERT') {
                $pkField = $this->getPrimaryKeyField($table);
                $recordId = $this->lastInsertId();
                $newRow = ($pkField !== null && $recordId !== null && $recordId !== '' && $recordId !== '0')
                    ? $logger->fetchRowById($table, $pkField, $recordId)
                    : null;

                if ($newRow === null) {
                    $newRow = ['ExecutedSQL' => $context['sql']];
                }

                $logger->logInsert($table, $recordId !== '' ? $recordId : null, $newRow, $requestBody, [
                    'ReferenceNo' => $this->deriveReferenceNo($newRow, $recordId),
                    'Remarks' => 'Auto-audited insert',
                    'ModuleName' => $this->moduleName,
                    'SourcePath' => isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null,
                ]);
                return;
            }

            foreach ($context['oldRows'] as $oldRow) {
                $pkField = $this->getPrimaryKeyField($table);
                $recordId = $pkField !== null && isset($oldRow[$pkField]) ? $oldRow[$pkField] : null;

                if ($parsed['action'] === 'UPDATE') {
                    $newRow = $recordId !== null ? $logger->fetchRowById($table, $pkField, $recordId) : null;
                    if ($newRow === null) {
                        $newRow = ['ExecutedSQL' => $context['sql']];
                    }

                    $logger->logUpdate($table, $recordId, $oldRow, $newRow, $requestBody, [
                        'ReferenceNo' => $this->deriveReferenceNo($newRow, $recordId),
                        'Remarks' => 'Auto-audited update',
                        'ModuleName' => $this->moduleName,
                        'SourcePath' => isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null,
                    ]);
                    continue;
                }

                $logger->logDelete($table, $recordId, $oldRow, $requestBody, [
                    'ReferenceNo' => $this->deriveReferenceNo($oldRow, $recordId),
                    'Remarks' => 'Auto-audited delete',
                    'ModuleName' => $this->moduleName,
                    'SourcePath' => isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null,
                ]);
            }
        } catch (Exception $exception) {
            error_log('Auto audit failed: ' . $exception->getMessage());
        }
        $this->resumeAudit();
    }

    public function suspendAudit()
    {
        $this->autoAuditSuspended++;
    }

    public function resumeAudit()
    {
        if ($this->autoAuditSuspended > 0) {
            $this->autoAuditSuspended--;
        }
    }

    public function isAuditSuspended()
    {
        return $this->autoAuditSuspended > 0;
    }

    public function exec($statement)
    {
        $context = $this->beginMutationAudit($statement);
        $result = parent::exec($statement);
        $this->completeMutationAudit($context);
        return $result;
    }

    private function getAuditLogger()
    {
        if ($this->auditLogger === null) {
            $this->auditLogger = new CentralAuditLogger($this, $this->moduleName);
        }
        return $this->auditLogger;
    }

    private function resolveModuleName()
    {
        $paths = [
            isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : null,
            isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null,
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null,
        ];

        foreach ($paths as $path) {
            if ($path && preg_match('#ApiV2[\\\\/]+([^\\\\/]+)#i', $path, $matches)) {
                return $matches[1];
            }
        }

        return 'General';
    }

    private function getRequestBody()
    {
        if ($this->requestBody !== null) {
            return $this->requestBody;
        }

        $rawInput = @file_get_contents('php://input');
        $decoded = json_decode($rawInput, true);
        $this->requestBody = is_array($decoded) ? $decoded : [];
        return $this->requestBody;
    }

    private function isManualAuditControllerActive()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
        foreach ($trace as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            $file = $frame['file'];
            if (strpos($file, DIRECTORY_SEPARATOR . 'ApiV2' . DIRECTORY_SEPARATOR) === false) {
                continue;
            }

            if (strpos($file, DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR) === false) {
                continue;
            }

            if (array_key_exists($file, $this->manualAuditFileCache)) {
                return $this->manualAuditFileCache[$file];
            }

            $content = @file_get_contents($file);
            $hasManualAudit = is_string($content) && preg_match('/CentralAuditLogger|AuditLogger|logInsert\s*\(|logUpdate\s*\(|logDelete\s*\(/', $content) === 1;
            $this->manualAuditFileCache[$file] = $hasManualAudit;
            return $hasManualAudit;
        }

        return false;
    }

    private function parseMutationSql($sql)
    {
        $sql = trim($sql);

        if (preg_match('/^INSERT\s+INTO\s+`?([A-Za-z0-9_]+)`?/i', $sql, $matches)) {
            return [
                'action' => 'INSERT',
                'table' => $matches[1],
                'where' => null,
            ];
        }

        if (preg_match('/^UPDATE\s+`?([A-Za-z0-9_]+)`?(?:\s+[A-Za-z0-9_]+)?\s+SET\s+.+?\s+WHERE\s+(.+)$/is', $sql, $matches)) {
            return [
                'action' => 'UPDATE',
                'table' => $matches[1],
                'where' => trim($matches[2]),
            ];
        }

        if (preg_match('/^DELETE\s+FROM\s+`?([A-Za-z0-9_]+)`?(?:\s+[A-Za-z0-9_]+)?\s+WHERE\s+(.+)$/is', $sql, $matches)) {
            return [
                'action' => 'DELETE',
                'table' => $matches[1],
                'where' => trim($matches[2]),
            ];
        }

        return null;
    }

    private function isExcludedTable($table)
    {
        return in_array(strtolower($table), $this->excludedTables, true);
    }

    private function fetchRowsForClause($table, $whereClause)
    {
        if ($whereClause === null || $whereClause === '') {
            return [];
        }

        $sql = "SELECT * FROM `{$table}` WHERE {$whereClause}";
        $this->suspendAudit();
        $rows = [];
        try {
            $stmt = parent::query($sql);
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $exception) {
            error_log('Auto audit fetchRowsForClause failed: ' . $exception->getMessage());
        }
        $this->resumeAudit();
        return $rows;
    }

    private function getPrimaryKeyField($table)
    {
        if (array_key_exists($table, $this->primaryKeyCache)) {
            return $this->primaryKeyCache[$table];
        }

        $this->suspendAudit();
        try {
            $stmt = parent::query("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            $this->primaryKeyCache[$table] = $row && isset($row['Column_name']) ? $row['Column_name'] : null;
        } catch (Exception $exception) {
            $this->primaryKeyCache[$table] = null;
        }
        $this->resumeAudit();

        return $this->primaryKeyCache[$table];
    }

    private function deriveReferenceNo($row, $fallback)
    {
        if (is_array($row)) {
            foreach (['DocNo', 'DocNumber', 'ReferenceNo', 'Code', 'Name', 'ID', 'Id'] as $field) {
                if (isset($row[$field]) && $row[$field] !== '') {
                    return $row[$field];
                }
            }
        }

        return $fallback;
    }

    private function materializeSql($sql, array $params)
    {
        if (empty($params)) {
            return $sql;
        }

        $materializedSql = $sql;
        $normalizedParams = [];
        foreach ($params as $key => $value) {
            $normalizedParams[$key] = $this->quoteValue($value);
        }

        $hasNamedParams = false;
        foreach (array_keys($normalizedParams) as $key) {
            if (!is_int($key)) {
                $hasNamedParams = true;
                $placeholder = strpos($key, ':') === 0 ? $key : ':' . $key;
                $materializedSql = str_replace($placeholder, $normalizedParams[$key], $materializedSql);
            }
        }

        if ($hasNamedParams) {
            return $materializedSql;
        }

        foreach ($normalizedParams as $value) {
            $materializedSql = preg_replace('/\?/', $value, $materializedSql, 1);
        }

        return $materializedSql;
    }

    private function quoteValue($value)
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return parent::quote((string) $value);
    }
}

class AutoAuditPDOStatement extends PDOStatement
{
    private $autoAuditConnection;
    private $boundParams = [];

    protected function __construct($autoAuditConnection)
    {
        $this->autoAuditConnection = $autoAuditConnection;
    }

    public function bindValue($param, $value, $type = PDO::PARAM_STR)
    {
        $this->boundParams[$param] = [
            'byRef' => false,
            'value' => $value,
        ];
        return parent::bindValue($param, $value, $type);
    }

    public function bindParam($param, &$var, $type = PDO::PARAM_STR, $length = null, $driverOptions = null)
    {
        $this->boundParams[$param] = [
            'byRef' => true,
            'value' => &$var,
        ];

        if ($length === null && $driverOptions === null) {
            return parent::bindParam($param, $var, $type);
        }

        if ($driverOptions === null) {
            return parent::bindParam($param, $var, $type, $length);
        }

        return parent::bindParam($param, $var, $type, $length, $driverOptions);
    }

    public function execute($params = null)
    {
        $resolvedParams = is_array($params) ? $params : $this->resolveBoundParams();
        $context = $this->autoAuditConnection->beginMutationAudit($this->queryString, $resolvedParams);
        $result = parent::execute($params);
        $this->autoAuditConnection->completeMutationAudit($context);
        return $result;
    }

    private function resolveBoundParams()
    {
        $resolved = [];
        foreach ($this->boundParams as $key => $config) {
            $resolved[$key] = $config['value'];
        }
        return $resolved;
    }
}
