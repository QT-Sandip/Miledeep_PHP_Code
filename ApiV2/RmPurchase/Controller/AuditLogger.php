<?php

if (!class_exists('Database')) {
    include_once __DIR__ . '/../../Config/Database.php';
}
include_once __DIR__ . '/../../Global/Audit/AuditExceptions.php';

class AuditLogger
{
    private $conn;
    private $auditConn;
    private $moduleName;
    private $tableEnsured = false;
    private $outboxEnsured = false;
    private $generatedTransactionId = null;
    private $auditEnabled = true;
    private $maskedFields = [
        'password',
        'dbpassword',
        'session_token',
        'token',
        'auth_token',
    ];

    //public function __construct($db, $moduleName = 'Purchase', $auditConn = null, $auditEnabled = false) -> disable logging

    public function __construct($db, $moduleName = 'Purchase', $auditConn = null, $auditEnabled = true)
    {
        $this->conn = $db;
        $this->moduleName = $moduleName;
        $this->auditConn = $auditConn instanceof PDO ? $auditConn : $this->createAuditConnection();
        $this->auditEnabled = (bool) $auditEnabled;
    }

    public function enableAudit()
    {
        $this->auditEnabled = true;
    }

    public function disableAudit()
    {
        $this->auditEnabled = false;
    }

    public function isAuditEnabled()
    {
        return $this->auditEnabled;
    }

    public function fetchRowById($tableName, $idField, $recordId)
    {
        try {
            if ($recordId === null || $recordId === '') {
                return null;
            }

            $sql = "SELECT * FROM `{$tableName}` WHERE `{$idField}` = :record_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':record_id', $recordId);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $exception) {
            error_log('Audit fetchRowById failed: ' . $exception->getMessage());
            return null;
        }
    }

    public function fetchOne($sql, array $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (Exception $exception) {
            error_log('Audit fetchOne failed: ' . $exception->getMessage());
            return null;
        }
    }

    public function fetchRowsByConditions($tableName, array $conditions)
    {
        try {
            if (empty($conditions)) {
                return [];
            }

            $whereParts = [];
            $params = [];
            foreach ($conditions as $field => $value) {
                $paramName = ':w_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $field);
                $whereParts[] = "`{$field}` = {$paramName}";
                $params[$paramName] = $value;
            }

            $sql = "SELECT * FROM `{$tableName}` WHERE " . implode(' AND ', $whereParts);
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $paramName => $value) {
                $stmt->bindValue($paramName, $value);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $exception) {
            error_log('Audit fetchRowsByConditions failed: ' . $exception->getMessage());
            return [];
        }
    }

    public function logInsert($tableName, $recordId, array $newData = null, array $requestBody = null, array $options = null)
    {
        $this->writeAuditLog($tableName, 'INSERT', $recordId, null, $newData, $requestBody, $options);
    }

    public function logUpdate($tableName, $recordId, array $oldData = null, array $newData = null, array $requestBody = null, array $options = null)
    {
        $this->writeAuditLog($tableName, 'UPDATE', $recordId, $oldData, $newData, $requestBody, $options);
    }

    public function logDelete($tableName, $recordId, array $oldData = null, array $requestBody = null, array $options = null)
    {
        $this->writeAuditLog($tableName, 'DELETE', $recordId, $oldData, null, $requestBody, $options);
    }

    public function flushPendingOutbox($limit = 100)
    {
        $this->ensureAuditTable();
        $this->ensureOutboxTable();

        $processed = 0;
        $limit = (int) $limit;
        if ($limit <= 0) {
            $limit = 100;
        }

        while ($processed < $limit) {
            $sql = "SELECT `OutboxID`
                FROM `audit_log_outbox`
                WHERE `Status` IN ('pending', 'failed')
                ORDER BY `OutboxID` ASC
                LIMIT 1";
            $stmt = $this->auditConn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !isset($row['OutboxID'])) {
                break;
            }

            $this->flushOutboxRecord((int) $row['OutboxID']);
            $processed++;
        }

        return $processed;
    }

    private function writeAuditLog($tableName, $actionType, $recordId, array $oldData = null, array $newData = null, array $requestBody = null, array $options = null)
    {
        if (!$this->auditEnabled) {
            return;
        }

        try {
            $this->ensureAuditTable();
            $this->ensureOutboxTable();

            if ($requestBody === null) {
                $requestBody = [];
            }
            if ($options === null) {
                $options = [];
            }

            $context = $this->buildContext($requestBody, $options);
            $sanitizedOldData = $this->sanitizeData($oldData);
            $sanitizedNewData = $this->sanitizeData($newData);
            $changedFields = $this->buildChangedFields($sanitizedOldData, $sanitizedNewData);

            $shouldLog =
                !empty($changedFields) ||
                in_array($actionType, ['INSERT', 'DELETE', 'CREATE'], true);

            if ($shouldLog) {
                $event = [
                    'ClientID' => $context['ClientID'] === null ? null : (string) $context['ClientID'],
                    'TransactionID' => $context['TransactionID'],
                    'ModuleName' => $context['ModuleName'],
                    'TableName' => $tableName,
                    'ActionType' => $actionType,
                    'RecordID' => $recordId === null ? null : (string) $recordId,
                    'ReferenceNo' => $context['ReferenceNo'],
                    'OldData' => $sanitizedOldData,
                    'NewData' => $sanitizedNewData,
                    'ChangedFields' => empty($changedFields) ? null : $changedFields,
                    'ActionBy' => $context['ActionBy'] === null ? null : (string) $context['ActionBy'],
                    'ActionRole' => $context['ActionRole'],
                    'ActionDateTime' => date('Y-m-d H:i:s'),
                    'SourceType' => $context['SourceType'],
                    'SourcePath' => $context['SourcePath'],
                    'IPAddress' => $context['IPAddress'],
                    'BrowserInfo' => $context['BrowserInfo'],
                    'SessionID' => $context['SessionID'],
                    'Remarks' => $context['Remarks'],
                ];

                $outboxId = $this->enqueueAuditEvent($event);
                if ($this->shouldFlushImmediately($options)) {
                    $this->flushOutboxRecord($outboxId);
                }
            }
        } catch (AuditException $exception) {
            error_log('Audit log write failed: ' . $exception->getMessage());
        } catch (Exception $exception) {
            error_log('Audit log write failed: ' . $exception->getMessage());
        }
    }

    private function enqueueAuditEvent(array $event)
    {
        $payload = $this->encodeJson($event);
        if ($payload === false || $payload === null) {
            throw new AuditQueueException('Unable to encode audit event payload');
        }

        $sql = "INSERT INTO `audit_log_outbox`
            (`ClientID`, `TransactionID`, `ModuleName`, `TableName`, `ActionType`, `RecordID`, `Status`, `EventPayload`, `AttemptCount`, `CreatedAt`)
            VALUES
            (:client_id, :transaction_id, :module_name, :table_name, :action_type, :record_id, 'pending', :event_payload, 0, NOW())";

        $stmt = $this->auditConn->prepare($sql);
        $stmt->bindValue(':client_id', isset($event['ClientID']) ? $event['ClientID'] : null);
        $stmt->bindValue(':transaction_id', isset($event['TransactionID']) ? $event['TransactionID'] : null);
        $stmt->bindValue(':module_name', isset($event['ModuleName']) ? $event['ModuleName'] : null);
        $stmt->bindValue(':table_name', isset($event['TableName']) ? $event['TableName'] : null);
        $stmt->bindValue(':action_type', isset($event['ActionType']) ? $event['ActionType'] : null);
        $stmt->bindValue(':record_id', isset($event['RecordID']) ? $event['RecordID'] : null);
        $stmt->bindValue(':event_payload', $payload);
        if (!$stmt->execute()) {
            throw new AuditQueueException('Unable to enqueue audit event');
        }

        return (int) $this->auditConn->lastInsertId();
    }

    private function flushOutboxRecord($outboxId)
    {
        if (!$this->auditEnabled) {
            return 0;
        }

        $outboxSql = "SELECT * FROM `audit_log_outbox` WHERE `OutboxID` = :outbox_id LIMIT 1";
        $outboxStmt = $this->auditConn->prepare($outboxSql);
        $outboxStmt->bindValue(':outbox_id', $outboxId, PDO::PARAM_INT);
        $outboxStmt->execute();
        $outboxRow = $outboxStmt->fetch(PDO::FETCH_ASSOC);

        if (!$outboxRow) {
            return;
        }

        if (isset($outboxRow['Status']) && $outboxRow['Status'] === 'done') {
            return;
        }

        $event = $this->decodeJson(isset($outboxRow['EventPayload']) ? $outboxRow['EventPayload'] : null);
        if (!is_array($event)) {
            $this->markOutboxFailed($outboxId, 'Invalid audit outbox payload');
            throw new AuditQueueException('Invalid audit outbox payload');
        }

        try {
            $this->markOutboxProcessing($outboxId);
            $this->insertIntoCentralAuditLog($event);
            $this->markOutboxDone($outboxId);
        } catch (Exception $exception) {
            $this->markOutboxFailed($outboxId, $exception->getMessage());
            throw new AuditWriteException($exception->getMessage(), 0, $exception);
        }
    }

    private function insertIntoCentralAuditLog(array $event)
    {
        $sql = "INSERT INTO `audit_log`
            (`ClientID`, `TransactionID`, `ModuleName`, `TableName`, `ActionType`, `RecordID`, `ReferenceNo`, `OldData`, `NewData`, `ChangedFields`,
             `ActionBy`, `ActionRole`, `ActionDateTime`, `SourceType`, `SourcePath`, `IPAddress`, `BrowserInfo`, `SessionID`, `Remarks`)
            VALUES
            (:client_id, :transaction_id, :module_name, :table_name, :action_type, :record_id, :reference_no, :old_data, :new_data, :changed_fields,
             :action_by, :action_role, :action_datetime, :source_type, :source_path, :ip_address, :browser_info, :session_id, :remarks)";

        $stmt = $this->auditConn->prepare($sql);
        $stmt->bindValue(':client_id', isset($event['ClientID']) ? $event['ClientID'] : null);
        $stmt->bindValue(':transaction_id', isset($event['TransactionID']) ? $event['TransactionID'] : null);
        $stmt->bindValue(':module_name', isset($event['ModuleName']) ? $event['ModuleName'] : null);
        $stmt->bindValue(':table_name', isset($event['TableName']) ? $event['TableName'] : null);
        $stmt->bindValue(':action_type', isset($event['ActionType']) ? $event['ActionType'] : null);
        $stmt->bindValue(':record_id', isset($event['RecordID']) ? $event['RecordID'] : null);
        $stmt->bindValue(':reference_no', isset($event['ReferenceNo']) ? $event['ReferenceNo'] : null);
        $stmt->bindValue(':old_data', isset($event['OldData']) && $event['OldData'] !== null ? $this->encodeJson($event['OldData']) : null);
        $stmt->bindValue(':new_data', isset($event['NewData']) && $event['NewData'] !== null ? $this->encodeJson($event['NewData']) : null);
        $stmt->bindValue(':changed_fields', isset($event['ChangedFields']) && $event['ChangedFields'] !== null ? $this->encodeJson($event['ChangedFields']) : null);
        $stmt->bindValue(':action_by', isset($event['ActionBy']) ? $event['ActionBy'] : null);
        $stmt->bindValue(':action_role', isset($event['ActionRole']) ? $event['ActionRole'] : null);
        $stmt->bindValue(':action_datetime', isset($event['ActionDateTime']) ? $event['ActionDateTime'] : date('Y-m-d H:i:s'));
        $stmt->bindValue(':source_type', isset($event['SourceType']) ? $event['SourceType'] : null);
        $stmt->bindValue(':source_path', isset($event['SourcePath']) ? $event['SourcePath'] : null);
        $stmt->bindValue(':ip_address', isset($event['IPAddress']) ? $event['IPAddress'] : null);
        $stmt->bindValue(':browser_info', isset($event['BrowserInfo']) ? $event['BrowserInfo'] : null);
        $stmt->bindValue(':session_id', isset($event['SessionID']) ? $event['SessionID'] : null);
        $stmt->bindValue(':remarks', isset($event['Remarks']) ? $event['Remarks'] : null);

        if (!$stmt->execute()) {
            throw new AuditWriteException('Unable to write centralized audit record');
        }
    }

    private function markOutboxProcessing($outboxId)
    {
        $sql = "UPDATE `audit_log_outbox`
            SET `Status` = 'processing', `AttemptCount` = `AttemptCount` + 1, `LastError` = NULL
            WHERE `OutboxID` = :outbox_id";
        $stmt = $this->auditConn->prepare($sql);
        $stmt->bindValue(':outbox_id', $outboxId, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function markOutboxDone($outboxId)
    {
        $sql = "UPDATE `audit_log_outbox`
            SET `Status` = 'done', `ProcessedAt` = NOW(), `LastError` = NULL
            WHERE `OutboxID` = :outbox_id";
        $stmt = $this->auditConn->prepare($sql);
        $stmt->bindValue(':outbox_id', $outboxId, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function markOutboxFailed($outboxId, $message)
    {
        $sql = "UPDATE `audit_log_outbox`
            SET `Status` = 'failed', `LastError` = :last_error
            WHERE `OutboxID` = :outbox_id";
        $stmt = $this->auditConn->prepare($sql);
        $stmt->bindValue(':outbox_id', $outboxId, PDO::PARAM_INT);
        $stmt->bindValue(':last_error', $message === null ? null : substr((string) $message, 0, 65535));
        $stmt->execute();
    }

    private function shouldFlushImmediately(array $options)
    {
        if (isset($options['Async']) && (int) $options['Async'] === 1) {
            return false;
        }

        return !$this->isBusinessTransactionActive();
    }

    private function isBusinessTransactionActive()
    {
        return $this->conn instanceof PDO && method_exists($this->conn, 'inTransaction') && $this->conn->inTransaction();
    }

    private function buildContext(array $requestBody, array $options)
    {
        $payload = $requestBody;
        if (isset($requestBody['AuthPayload']) && is_array($requestBody['AuthPayload'])) {
            $payload = $requestBody['AuthPayload'];
        }

        $sourcePath = isset($options['SourcePath']) ? $options['SourcePath'] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null);
        $actionRole = isset($options['ActionRole']) ? $options['ActionRole'] : null;
        if ($actionRole === null && isset($payload['AccessType'])) {
            $actionRole = $payload['AccessType'];
        }
        if ($actionRole === null && isset($payload['UserType'])) {
            $actionRole = $payload['UserType'];
        }

        $sessionData = isset($_SESSION) && is_array($_SESSION) ? $_SESSION : [];
        $clientId = $this->resolveContextValue([
            isset($options['ClientID']) ? $options['ClientID'] : null,
            isset($requestBody['ClientID']) ? $requestBody['ClientID'] : null,
            isset($payload['ClientID']) ? $payload['ClientID'] : null,
            isset($sessionData['ClientID']) ? $sessionData['ClientID'] : null,
        ]);
        $actionBy = $this->resolveContextValue([
            isset($options['ActionBy']) ? $options['ActionBy'] : null,
            isset($requestBody['AuthUserID']) ? $requestBody['AuthUserID'] : null,
            isset($sessionData['UserID']) ? $sessionData['UserID'] : null,
            isset($sessionData['LoginUserID']) ? $sessionData['LoginUserID'] : null,
            isset($sessionData['PostedUserID']) ? $sessionData['PostedUserID'] : null,
            isset($sessionData['AdminID']) ? $sessionData['AdminID'] : null,
            isset($requestBody['UserID']) ? $requestBody['UserID'] : null,
            isset($requestBody['LoginUserID']) ? $requestBody['LoginUserID'] : null,
            isset($requestBody['PostedUserID']) ? $requestBody['PostedUserID'] : null,
            isset($requestBody['CreatedBy']) ? $requestBody['CreatedBy'] : null,
            isset($requestBody['ApprovedBy']) ? $requestBody['ApprovedBy'] : null,
            isset($payload['UserID']) ? $payload['UserID'] : null,
            isset($payload['LoginUserID']) ? $payload['LoginUserID'] : null,
            isset($payload['PostedUserID']) ? $payload['PostedUserID'] : null,
            isset($payload['CreatedBy']) ? $payload['CreatedBy'] : null,
            isset($payload['IPAddress']) ? $payload['IPAddress'] : null,
            isset($payload['BrowserInfo']) ? $payload['BrowserInfo'] : null,
            $this->extractUserIdFromData($requestBody),
        ]);
        if ($clientId === null && $actionBy !== null) {
            $clientId = $this->lookupClientIdByUser($actionBy);
        }
        $transactionId = $this->resolveContextValue([
            isset($options['TransactionID']) ? $options['TransactionID'] : null,
            isset($requestBody['TransactionID']) ? $requestBody['TransactionID'] : null,
            isset($payload['TransactionID']) ? $payload['TransactionID'] : null,
            isset($sessionData['TransactionID']) ? $sessionData['TransactionID'] : null,
        ]);
        if ($transactionId === null) {
            $transactionId = $this->getGeneratedTransactionId();
        }

        $sourceType = isset($options['SourceType']) ? $options['SourceType'] : 'API';
        if (!isset($options['SourceType']) && isset($requestBody['AuditSourceType'])) {
            $sourceType = $requestBody['AuditSourceType'];
        }

        $remarks = isset($options['Remarks']) ? $options['Remarks'] : null;
        if (!isset($options['Remarks']) && isset($requestBody['AuditRemarks'])) {
            $remarks = $requestBody['AuditRemarks'];
        }

        $ipAddress = $this->resolveContextValue([
            isset($options['IPAddress']) ? $options['IPAddress'] : null,
            isset($requestBody['IPAddress']) ? $requestBody['IPAddress'] : null,
            isset($requestBody['IpAddress']) ? $requestBody['IpAddress'] : null,
            isset($requestBody['ClientIP']) ? $requestBody['ClientIP'] : null,
            isset($payload['IPAddress']) ? $payload['IPAddress'] : null,
            isset($payload['IpAddress']) ? $payload['IpAddress'] : null,
            isset($payload['ClientIP']) ? $payload['ClientIP'] : null,
            $this->resolveClientIp(),
        ]);

        $browserInfo = $this->resolveContextValue([
            isset($options['BrowserInfo']) ? $options['BrowserInfo'] : null,
            isset($requestBody['BrowserInfo']) ? $requestBody['BrowserInfo'] : null,
            isset($requestBody['Browser']) ? $requestBody['Browser'] : null,
            isset($requestBody['BrowserName']) ? $requestBody['BrowserName'] : null,
            isset($requestBody['UserAgent']) ? $requestBody['UserAgent'] : null,
            isset($requestBody['userAgent']) ? $requestBody['userAgent'] : null,
            isset($payload['BrowserInfo']) ? $payload['BrowserInfo'] : null,
            isset($payload['Browser']) ? $payload['Browser'] : null,
            isset($payload['BrowserName']) ? $payload['BrowserName'] : null,
            isset($payload['UserAgent']) ? $payload['UserAgent'] : null,
            isset($payload['userAgent']) ? $payload['userAgent'] : null,
            $this->resolveBrowserInfo(),
        ]);

        return [
            'ClientID' => $clientId,
            'TransactionID' => $transactionId,
            'ModuleName' => isset($options['ModuleName']) ? $options['ModuleName'] : $this->moduleName,
            'ReferenceNo' => isset($options['ReferenceNo']) ? (string) $options['ReferenceNo'] : null,
            'ActionBy' => $actionBy,
            'ActionRole' => $actionRole,
            'SourceType' => $sourceType,
            'SourcePath' => $sourcePath,
            'IPAddress' => $ipAddress,
            'BrowserInfo' => $browserInfo,
            'SessionID' => isset($options['SessionID']) ? $options['SessionID'] : (function_exists('session_id') ? session_id() : null),
            'Remarks' => $remarks,
        ];
    }

    private function sanitizeData($data)
    {
        if ($data === null) {
            return null;
        }

        if (!is_array($data)) {
            return $data;
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $this->maskedFields, true)) {
                $sanitized[$key] = '***';
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else if (is_object($value)) {
                $sanitized[$key] = $this->sanitizeData((array) $value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    private function buildChangedFields($oldData, $newData)
    {
        if (!is_array($oldData) || !is_array($newData)) {
            return [];
        }

        $changedFields = [];
        $keys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
        foreach ($keys as $key) {
            $oldValue = array_key_exists($key, $oldData) ? $oldData[$key] : null;
            $newValue = array_key_exists($key, $newData) ? $newData[$key] : null;
            if ($oldValue !== $newValue) {
                $changedFields[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changedFields;
    }

    private function ensureAuditTable()
    {
        if ($this->tableEnsured) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `audit_log` (
            `AuditID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `ClientID` VARCHAR(100) DEFAULT NULL,
            `TransactionID` VARCHAR(100) DEFAULT NULL,
            `ModuleName` VARCHAR(100) NOT NULL,
            `TableName` VARCHAR(100) NOT NULL,
            `ActionType` VARCHAR(20) NOT NULL,
            `RecordID` VARCHAR(100) DEFAULT NULL,
            `ReferenceNo` VARCHAR(255) DEFAULT NULL,
            `OldData` LONGTEXT NULL,
            `NewData` LONGTEXT NULL,
            `ChangedFields` LONGTEXT NULL,
            `ActionBy` VARCHAR(100) DEFAULT NULL,
            `ActionRole` VARCHAR(100) DEFAULT NULL,
            `ActionDateTime` DATETIME NOT NULL,
            `SourceType` VARCHAR(50) DEFAULT NULL,
            `SourcePath` VARCHAR(255) DEFAULT NULL,
            `IPAddress` VARCHAR(64) DEFAULT NULL,
            `BrowserInfo` TEXT NULL,
            `SessionID` VARCHAR(128) DEFAULT NULL,
            `Remarks` TEXT NULL,
            PRIMARY KEY (`AuditID`),
            KEY `idx_audit_client_datetime` (`ClientID`, `ActionDateTime`, `AuditID`),
            KEY `idx_audit_client_actionby_datetime` (`ClientID`, `ActionBy`, `ActionDateTime`, `AuditID`),
            KEY `idx_audit_client_table_action_datetime` (`ClientID`, `TableName`, `ActionType`, `ActionDateTime`, `AuditID`),
            KEY `idx_audit_transaction` (`TransactionID`),
            KEY `idx_audit_module_table` (`ModuleName`, `TableName`),
            KEY `idx_audit_record` (`RecordID`),
            KEY `idx_audit_action_by` (`ActionBy`),
            KEY `idx_audit_action_datetime` (`ActionDateTime`),
            KEY `idx_audit_datetime_id` (`ActionDateTime`, `AuditID`),
            KEY `idx_audit_actionby_datetime` (`ActionBy`, `ActionDateTime`, `AuditID`),
            KEY `idx_audit_table_action_datetime` (`TableName`, `ActionType`, `ActionDateTime`, `AuditID`),
            KEY `idx_audit_module_datetime` (`ModuleName`, `ActionDateTime`, `AuditID`),
            KEY `idx_audit_reference` (`ReferenceNo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->auditConn->exec($sql);
        $this->ensureColumn('audit_log', 'ClientID', 'ALTER TABLE `audit_log` ADD COLUMN `ClientID` VARCHAR(100) DEFAULT NULL AFTER `AuditID`');
        $this->ensureColumn('audit_log', 'TransactionID', 'ALTER TABLE `audit_log` ADD COLUMN `TransactionID` VARCHAR(100) DEFAULT NULL AFTER `ClientID`');
        $this->ensureColumn('audit_log', 'BrowserInfo', 'ALTER TABLE `audit_log` ADD COLUMN `BrowserInfo` TEXT NULL AFTER `IPAddress`');
        $this->ensureIndex('audit_log', 'idx_audit_client_datetime', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_client_datetime` (`ClientID`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log', 'idx_audit_client_actionby_datetime', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_client_actionby_datetime` (`ClientID`, `ActionBy`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log', 'idx_audit_client_table_action_datetime', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_client_table_action_datetime` (`ClientID`, `TableName`, `ActionType`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log', 'idx_audit_transaction', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_transaction` (`TransactionID`)');
        $this->ensureIndex('audit_log', 'idx_audit_datetime_id', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_datetime_id` (`ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log', 'idx_audit_actionby_datetime', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_actionby_datetime` (`ActionBy`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log', 'idx_audit_table_action_datetime', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_table_action_datetime` (`TableName`, `ActionType`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log', 'idx_audit_module_datetime', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_module_datetime` (`ModuleName`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log', 'idx_audit_reference', 'ALTER TABLE `audit_log` ADD INDEX `idx_audit_reference` (`ReferenceNo`)');

        $archiveSql = "CREATE TABLE IF NOT EXISTS `audit_log_archive` (
            `ArchiveID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `AuditID` BIGINT UNSIGNED NOT NULL,
            `ClientID` VARCHAR(100) DEFAULT NULL,
            `TransactionID` VARCHAR(100) DEFAULT NULL,
            `ModuleName` VARCHAR(100) NOT NULL,
            `TableName` VARCHAR(100) NOT NULL,
            `ActionType` VARCHAR(20) NOT NULL,
            `RecordID` VARCHAR(100) DEFAULT NULL,
            `ReferenceNo` VARCHAR(255) DEFAULT NULL,
            `OldData` LONGTEXT NULL,
            `NewData` LONGTEXT NULL,
            `ChangedFields` LONGTEXT NULL,
            `ActionBy` VARCHAR(100) DEFAULT NULL,
            `ActionRole` VARCHAR(100) DEFAULT NULL,
            `ActionDateTime` DATETIME NOT NULL,
            `SourceType` VARCHAR(50) DEFAULT NULL,
            `SourcePath` VARCHAR(255) DEFAULT NULL,
            `IPAddress` VARCHAR(64) DEFAULT NULL,
            `BrowserInfo` TEXT NULL,
            `SessionID` VARCHAR(128) DEFAULT NULL,
            `Remarks` TEXT NULL,
            `ArchivedAt` DATETIME NOT NULL,
            `ArchiveRunID` BIGINT UNSIGNED DEFAULT NULL,
            PRIMARY KEY (`ArchiveID`),
            UNIQUE KEY `uniq_archive_audit_id` (`AuditID`),
            KEY `idx_archive_client_datetime` (`ClientID`, `ActionDateTime`, `AuditID`),
            KEY `idx_archive_client_actionby_datetime` (`ClientID`, `ActionBy`, `ActionDateTime`, `AuditID`),
            KEY `idx_archive_client_table_action_datetime` (`ClientID`, `TableName`, `ActionType`, `ActionDateTime`, `AuditID`),
            KEY `idx_archive_transaction` (`TransactionID`),
            KEY `idx_archive_module_table` (`ModuleName`, `TableName`),
            KEY `idx_archive_record` (`RecordID`),
            KEY `idx_archive_action_by` (`ActionBy`),
            KEY `idx_archive_action_datetime` (`ActionDateTime`),
            KEY `idx_archive_archived_at` (`ArchivedAt`),
            KEY `idx_archive_datetime_audit` (`ActionDateTime`, `AuditID`),
            KEY `idx_archive_actionby_datetime` (`ActionBy`, `ActionDateTime`, `AuditID`),
            KEY `idx_archive_table_action_datetime` (`TableName`, `ActionType`, `ActionDateTime`, `AuditID`),
            KEY `idx_archive_archiverun` (`ArchiveRunID`, `ArchivedAt`),
            KEY `idx_archive_reference` (`ReferenceNo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->auditConn->exec($archiveSql);
        $this->ensureColumn('audit_log_archive', 'ClientID', 'ALTER TABLE `audit_log_archive` ADD COLUMN `ClientID` VARCHAR(100) DEFAULT NULL AFTER `AuditID`');
        $this->ensureColumn('audit_log_archive', 'TransactionID', 'ALTER TABLE `audit_log_archive` ADD COLUMN `TransactionID` VARCHAR(100) DEFAULT NULL AFTER `ClientID`');
        $this->ensureColumn('audit_log_archive', 'BrowserInfo', 'ALTER TABLE `audit_log_archive` ADD COLUMN `BrowserInfo` TEXT NULL AFTER `IPAddress`');
        $this->ensureIndex('audit_log_archive', 'idx_archive_client_datetime', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_client_datetime` (`ClientID`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log_archive', 'idx_archive_client_actionby_datetime', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_client_actionby_datetime` (`ClientID`, `ActionBy`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log_archive', 'idx_archive_client_table_action_datetime', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_client_table_action_datetime` (`ClientID`, `TableName`, `ActionType`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log_archive', 'idx_archive_transaction', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_transaction` (`TransactionID`)');
        $this->ensureIndex('audit_log_archive', 'idx_archive_datetime_audit', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_datetime_audit` (`ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log_archive', 'idx_archive_actionby_datetime', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_actionby_datetime` (`ActionBy`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log_archive', 'idx_archive_table_action_datetime', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_table_action_datetime` (`TableName`, `ActionType`, `ActionDateTime`, `AuditID`)');
        $this->ensureIndex('audit_log_archive', 'idx_archive_archiverun', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_archiverun` (`ArchiveRunID`, `ArchivedAt`)');
        $this->ensureIndex('audit_log_archive', 'idx_archive_reference', 'ALTER TABLE `audit_log_archive` ADD INDEX `idx_archive_reference` (`ReferenceNo`)');

        $retentionSql = "CREATE TABLE IF NOT EXISTS `audit_log_retention_runs` (
            `RunID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `StartedAt` DATETIME NOT NULL,
            `CompletedAt` DATETIME DEFAULT NULL,
            `Status` VARCHAR(20) NOT NULL,
            `ArchiveBeforeDate` DATETIME DEFAULT NULL,
            `PurgeBeforeDate` DATETIME DEFAULT NULL,
            `ArchiveBatchSize` INT UNSIGNED NOT NULL DEFAULT 0,
            `ArchivedCount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `PurgedCount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `ErrorMessage` TEXT NULL,
            PRIMARY KEY (`RunID`),
            KEY `idx_retention_status` (`Status`),
            KEY `idx_retention_started_at` (`StartedAt`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->auditConn->exec($retentionSql);

        $queueSql = "CREATE TABLE IF NOT EXISTS `audit_log_async_queue` (
            `QueueID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `SourceClientID` VARCHAR(100) DEFAULT NULL,
            `OutboxID` BIGINT UNSIGNED DEFAULT NULL,
            `TransactionID` VARCHAR(100) DEFAULT NULL,
            `Status` VARCHAR(20) NOT NULL DEFAULT 'queued',
            `EventPayload` LONGTEXT NOT NULL,
            `AttemptCount` INT UNSIGNED NOT NULL DEFAULT 0,
            `LastError` TEXT NULL,
            `CreatedAt` DATETIME NOT NULL,
            `ProcessedAt` DATETIME DEFAULT NULL,
            PRIMARY KEY (`QueueID`),
            KEY `idx_async_queue_status_id` (`Status`, `QueueID`),
            KEY `idx_async_queue_client` (`SourceClientID`, `CreatedAt`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->auditConn->exec($queueSql);

        $this->tableEnsured = true;
    }

    private function ensureOutboxTable()
    {
        if ($this->outboxEnsured) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `audit_log_outbox` (
            `OutboxID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `ClientID` VARCHAR(100) DEFAULT NULL,
            `TransactionID` VARCHAR(100) DEFAULT NULL,
            `ModuleName` VARCHAR(100) DEFAULT NULL,
            `TableName` VARCHAR(100) DEFAULT NULL,
            `ActionType` VARCHAR(20) DEFAULT NULL,
            `RecordID` VARCHAR(100) DEFAULT NULL,
            `Status` VARCHAR(20) NOT NULL DEFAULT 'pending',
            `EventPayload` LONGTEXT NOT NULL,
            `AttemptCount` INT UNSIGNED NOT NULL DEFAULT 0,
            `LastError` TEXT NULL,
            `CreatedAt` DATETIME NOT NULL,
            `ProcessedAt` DATETIME DEFAULT NULL,
            PRIMARY KEY (`OutboxID`),
            KEY `idx_outbox_status_id` (`Status`, `OutboxID`),
            KEY `idx_outbox_transaction` (`TransactionID`),
            KEY `idx_outbox_client_created` (`ClientID`, `CreatedAt`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->auditConn->exec($sql);
        $this->outboxEnsured = true;
    }

    private function ensureIndex($tableName, $indexName, $alterSql)
    {
        $stmt = $this->auditConn->prepare("SHOW INDEX FROM `{$tableName}` WHERE Key_name = :index_name");
        $stmt->bindValue(':index_name', $indexName);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $this->auditConn->exec($alterSql);
        }
    }

    private function ensureColumn($tableName, $columnName, $alterSql)
    {
        $stmt = $this->auditConn->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE :column_name");
        $stmt->bindValue(':column_name', $columnName);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $this->auditConn->exec($alterSql);
        }
    }

    private function createAuditConnection()
    {
        try {
            // if we want to change to database for logs, change here.
            $database = new Database();
            $auditConn = $database->getAuthConnection();
            if ($auditConn instanceof PDO) {
                $auditConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $auditConn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return $auditConn;
            }
        } catch (Exception $exception) {
            error_log('Audit main-db connection failed: ' . $exception->getMessage());
        }

        return $this->conn;
    }

    private function getGeneratedTransactionId()
    {
        if ($this->generatedTransactionId === null) {
            $prefix = 'AUD';
            try {
                $prefix = 'AUD-' . date('YmdHis') . '-';
            } catch (Exception $exception) {
            }
            $this->generatedTransactionId = uniqid($prefix, false);
        }

        return $this->generatedTransactionId;
    }

    private function lookupClientIdByUser($userId)
    {
        try {
            $stmt = $this->auditConn->prepare("SELECT `ClientID` FROM `AdminLoginDetails` WHERE `UserID` = :user_id LIMIT 1");
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && isset($row['ClientID']) && $row['ClientID'] !== '') {
                return $row['ClientID'];
            }
        } catch (Exception $exception) {
            error_log('Audit client lookup failed: ' . $exception->getMessage());
        }

        return null;
    }

    private function resolveClientIp()
    {
        $candidates = [
            isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : null,
            isset($_SERVER['HTTP_TRUE_CLIENT_IP']) ? $_SERVER['HTTP_TRUE_CLIENT_IP'] : null,
            isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null,
            isset($_SERVER['HTTP_X_FORWARDED']) ? $_SERVER['HTTP_X_FORWARDED'] : null,
            isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null,
            isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : null,
            isset($_SERVER['HTTP_FORWARDED']) ? $_SERVER['HTTP_FORWARDED'] : null,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            $parts = explode(',', $candidate);
            foreach ($parts as $part) {
                $ip = trim($part);
                if (stripos($ip, 'for=') === 0) {
                    $ip = trim(substr($ip, 4), " \t\n\r\0\x0B\"'[]");
                }
                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return null;
    }

    private function resolveBrowserInfo()
    {
        if (isset($_SERVER['HTTP_SEC_CH_UA'])) {
            $clientHints = trim($_SERVER['HTTP_SEC_CH_UA']);
            if ($clientHints !== '') {
                return $clientHints;
            }
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = trim($_SERVER['HTTP_USER_AGENT']);
            return $userAgent !== '' ? $userAgent : null;
        }

        return null;
    }

    private function resolveContextValue(array $values)
    {
        foreach ($values as $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
            }

            return $value;
        }

        return null;
    }

    private function extractUserIdFromData(array $data)
    {
        $keys = [
            'AuthUserID',
            'UserID',
            'LoginUserID',
            'PostedUserID',
            'CreatedBy',
            'ApprovedBy',
        ];

        foreach ($keys as $key) {
            if (isset($data[$key]) && !is_array($data[$key]) && !is_object($data[$key])) {
                $value = is_string($data[$key]) ? trim($data[$key]) : $data[$key];
                if ($value !== '' && $value !== null) {
                    return $value;
                }
            }
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $resolvedValue = $this->extractUserIdFromData($value);
                if ($resolvedValue !== null && $resolvedValue !== '') {
                    return $resolvedValue;
                }
            }
        }

        return null;
    }

    private function encodeJson($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function decodeJson($data)
    {
        if ($data === null || $data === '') {
            return null;
        }

        $decoded = json_decode($data, true);
        return $decoded === null && strtolower(trim($data)) !== 'null' ? null : $decoded;
    }

    public function purgeOldOutboxRows($days = 7)
    {
        $sql = "DELETE FROM `audit_log_outbox`
                WHERE Status IN ('done','failed')
                AND CreatedAt < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->auditConn->prepare($sql);
        $stmt->bindValue(':days', (int) $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
