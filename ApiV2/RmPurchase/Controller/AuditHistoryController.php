<?php

class AuditHistoryController
{
    private $conn;
    private $currentRequestBody = [];
    private $currentClientId = '';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAuditHistory($requestBody)
    {
        $this->currentRequestBody = $requestBody;
        $this->currentClientId = trim((string) (isset($requestBody['ClientID']) ? $requestBody['ClientID'] : ''));
        $this->ensureAuditTable();

        $auditId = trim((string) (isset($requestBody['AuditID']) ? $requestBody['AuditID'] : ''));
        if ($auditId !== '') {
            return $this->getAuditHistoryDetail($auditId);
        }

        $pageNo = (int) (isset($requestBody['PageNo']) ? $requestBody['PageNo'] : 1);
        $pageNo = $pageNo > 0 ? $pageNo : 1;
        $loadAll = (int) (isset($requestBody['LoadAll']) ? $requestBody['LoadAll'] : 0);
        $clientId = $this->currentClientId;
        $searchKey = trim((string) (isset($requestBody['SearchKey']) ? $requestBody['SearchKey'] : ''));
        $startDate = trim((string) (isset($requestBody['StartDate']) ? $requestBody['StartDate'] : ''));
        $endDate = trim((string) (isset($requestBody['EndDate']) ? $requestBody['EndDate'] : ''));
        $actionBy = trim((string) (isset($requestBody['ActionBy']) ? $requestBody['ActionBy'] : ''));
        $moduleName = trim((string) (isset($requestBody['ModuleName']) ? $requestBody['ModuleName'] : ''));
        $actionType = trim((string) (isset($requestBody['ActionType']) ? $requestBody['ActionType'] : ''));

        $recordsPerPage = 50;
        $offset = ($pageNo - 1) * $recordsPerPage;

        $whereParts = ["1=1"];
        $params = [];

        if ($clientId === '') {
            return [
                'Error' => 'ClientID not found',
                'Data' => [],
            ];
        }
        $whereParts[] = "AL.ClientID = :client_id";
        $params[':client_id'] = $clientId;

        if ($startDate !== '') {
            $whereParts[] = "DATE(AL.ActionDateTime) >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== '') {
            $whereParts[] = "DATE(AL.ActionDateTime) <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if ($actionBy !== '') {
            $whereParts[] = "AL.ActionBy = :action_by";
            $params[':action_by'] = $actionBy;
        }

        if ($moduleName !== '') {
            $whereParts[] = "AL.TableName = :table_name";
            $params[':table_name'] = $moduleName;
        }

        if ($actionType !== '') {
            $whereParts[] = "AL.ActionType = :action_type";
            $params[':action_type'] = strtoupper($actionType);
        }

        if ($searchKey !== '') {
            $whereParts[] = "CONCAT_WS(' ', AL.ReferenceNo, AL.RecordID, AL.TableName, AL.SourcePath, AL.Remarks, COALESCE(ALD.UserLoginID, AL.ActionBy, '')) LIKE :search_key";
            $params[':search_key'] = '%' . $searchKey . '%';
        }

        $whereSql = implode(' AND ', $whereParts);

        $countSql = "SELECT COUNT(*) AS TotalCount
            FROM `audit_log` AS AL
            LEFT JOIN `AdminLoginDetails` AS ALD ON ALD.UserID = AL.ActionBy
            WHERE {$whereSql}";
        $countStmt = $this->conn->prepare($countSql);
        foreach ($params as $param => $value) {
            $countStmt->bindValue($param, $value);
        }
        $countStmt->execute();
        $totalCount = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['TotalCount'];
        $pageCount = $totalCount > 0 ? (int) ceil($totalCount / $recordsPerPage) : 1;

        $limitSql = '';
        if ($loadAll !== 1) {
            $limitSql = " LIMIT {$recordsPerPage} OFFSET {$offset}";
        }

        $listSql = "SELECT
                AL.AuditID,
                AL.ClientID,
                AL.TransactionID,
                AL.ModuleName,
                AL.TableName,
                AL.ActionType,
                AL.RecordID,
                AL.ReferenceNo,
                AL.ActionBy,
                COALESCE(ALD.UserLoginID, AL.ActionBy) AS ActionByName,
                AL.ActionRole,
                AL.ActionDateTime,
                AL.SourceType,
                AL.SourcePath,
                AL.IPAddress,
                AL.BrowserInfo,
                AL.Remarks,
                AL.ChangedFields
            FROM `audit_log` AS AL
            LEFT JOIN `AdminLoginDetails` AS ALD ON ALD.UserID = AL.ActionBy
            WHERE {$whereSql}
            ORDER BY AL.AuditID DESC{$limitSql}";

        $listStmt = $this->conn->prepare($listSql);
        foreach ($params as $param => $value) {
            $listStmt->bindValue($param, $value);
        }
        $listStmt->execute();

        $historyRows = [];
        $sno = $offset;
        while ($row = $listStmt->fetch(PDO::FETCH_ASSOC)) {
            $changedFields = $this->decodeJson($row['ChangedFields']);
            $historyRows[] = [
                'SNO' => ++$sno,
                'AuditID' => $row['AuditID'],
                'ClientID' => $row['ClientID'],
                'TransactionID' => $row['TransactionID'],
                'ModuleName' => $row['ModuleName'],
                'TableName' => $row['TableName'],
                'ActionType' => $row['ActionType'],
                'RecordID' => $row['RecordID'],
                'ReferenceNo' => $row['ReferenceNo'],
                'ActionBy' => $row['ActionBy'],
                'ActionByName' => $row['ActionByName'],
                'ActionRole' => $row['ActionRole'],
                'ActionDateTime' => $row['ActionDateTime'],
                'SourceType' => $row['SourceType'],
                'SourcePath' => $row['SourcePath'],
                'IPAddress' => $row['IPAddress'],
                'BrowserInfo' => $row['BrowserInfo'],
                'Remarks' => $row['Remarks'],
                'ChangedFieldCount' => is_array($changedFields) ? count($changedFields) : 0,
            ];
        }

        return [
            'TotalCount' => $totalCount,
            'PageCount' => $pageCount,
            'RecordsAval' => count($historyRows) >= $recordsPerPage ? 1 : 0,
            'Data' => $historyRows,
            'Filters' => $this->getFilterOptions(),
            'ModuleSummary' => $this->getModuleSummary($clientId, $startDate, $endDate, $actionBy, $moduleName, $actionType, $searchKey),
        ];
    }

    private function getAuditHistoryDetail($auditId)
    {
        $sql = "SELECT
                AL.*,
                COALESCE(ALD.UserLoginID, AL.ActionBy) AS ActionByName
            FROM `audit_log` AS AL
            LEFT JOIN `AdminLoginDetails` AS ALD ON ALD.UserID = AL.ActionBy
            WHERE AL.AuditID = :audit_id
                AND AL.ClientID = :client_id
            LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':audit_id', $auditId);
        $stmt->bindValue(':client_id', $this->currentClientId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'Error' => 'Audit history not found',
                'Data' => null,
            ];
        }

        $row['OldData'] = $this->decodeJson($row['OldData']);
        $row['NewData'] = $this->decodeJson($row['NewData']);
        $row['ChangedFields'] = $this->decodeJson($row['ChangedFields']);

        return [
            'Error' => null,
            'Data' => $row,
        ];
    }

    private function getFilterOptions()
    {
        $clientId = $this->currentClientId;
        $users = [];
        $userSql = "SELECT DISTINCT AL.ActionBy, COALESCE(ALD.UserLoginID, AL.ActionBy) AS UserName
            FROM `audit_log` AS AL
            LEFT JOIN `AdminLoginDetails` AS ALD ON ALD.UserID = AL.ActionBy
            WHERE AL.ClientID = :client_id
                AND AL.ActionBy IS NOT NULL AND AL.ActionBy <> ''
            ORDER BY UserName ASC";
        $userStmt = $this->conn->prepare($userSql);
        $userStmt->bindValue(':client_id', $clientId);
        $userStmt->execute();
        while ($row = $userStmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = [
                'UserID' => $row['ActionBy'],
                'UserName' => $row['UserName'],
            ];
        }

        $modules = [];
        $moduleSql = "SELECT DISTINCT TableName FROM `audit_log` WHERE ClientID = :client_id AND TableName IS NOT NULL AND TableName <> '' ORDER BY TableName ASC";
        $moduleStmt = $this->conn->prepare($moduleSql);
        $moduleStmt->bindValue(':client_id', $clientId);
        $moduleStmt->execute();
        while ($row = $moduleStmt->fetch(PDO::FETCH_ASSOC)) {
            $modules[] = [
                'ModuleName' => $row['TableName'],
            ];
        }

        $actionTypes = [];
        $actionSql = "SELECT DISTINCT ActionType FROM `audit_log` WHERE ClientID = :client_id AND ActionType IS NOT NULL AND ActionType <> '' ORDER BY ActionType ASC";
        $actionStmt = $this->conn->prepare($actionSql);
        $actionStmt->bindValue(':client_id', $clientId);
        $actionStmt->execute();
        while ($row = $actionStmt->fetch(PDO::FETCH_ASSOC)) {
            $actionTypes[] = [
                'ActionType' => $row['ActionType'],
            ];
        }

        return [
            'Users' => $users,
            'Modules' => $modules,
            'ActionTypes' => $actionTypes,
        ];
    }

    private function getModuleSummary($clientId, $startDate, $endDate, $actionBy, $moduleName, $actionType, $searchKey)
    {
        $whereParts = ["1=1"];
        $params = [':summary_client_id' => $clientId];
        $whereParts[] = "AL.ClientID = :summary_client_id";

        if ($startDate !== '') {
            $whereParts[] = "DATE(AL.ActionDateTime) >= :summary_start_date";
            $params[':summary_start_date'] = $startDate;
        }

        if ($endDate !== '') {
            $whereParts[] = "DATE(AL.ActionDateTime) <= :summary_end_date";
            $params[':summary_end_date'] = $endDate;
        }

        if ($actionBy !== '') {
            $whereParts[] = "AL.ActionBy = :summary_action_by";
            $params[':summary_action_by'] = $actionBy;
        }

        if ($moduleName !== '') {
            $whereParts[] = "AL.TableName = :summary_table_name";
            $params[':summary_table_name'] = $moduleName;
        }

        if ($actionType !== '') {
            $whereParts[] = "AL.ActionType = :summary_action_type";
            $params[':summary_action_type'] = strtoupper($actionType);
        }

        if ($searchKey !== '') {
            $whereParts[] = "CONCAT_WS(' ', AL.ReferenceNo, AL.RecordID, AL.TableName, AL.SourcePath, AL.Remarks) LIKE :summary_search_key";
            $params[':summary_search_key'] = '%' . $searchKey . '%';
        }

        $whereSql = implode(' AND ', $whereParts);

        $sql = "SELECT
                AL.TableName,
                COUNT(*) AS TotalActions,
                SUM(CASE WHEN AL.ActionType = 'INSERT' THEN 1 ELSE 0 END) AS InsertCount,
                SUM(CASE WHEN AL.ActionType = 'UPDATE' THEN 1 ELSE 0 END) AS UpdateCount,
                SUM(CASE WHEN AL.ActionType = 'DELETE' THEN 1 ELSE 0 END) AS DeleteCount
            FROM `audit_log` AS AL
            WHERE {$whereSql}
            GROUP BY AL.TableName
            ORDER BY TotalActions DESC, AL.TableName ASC
            LIMIT 12";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();

        $summary = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $summary[] = $row;
        }

        return $summary;
    }

    private function decodeJson($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    private function ensureAuditTable()
    {
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

        $this->conn->exec($sql);
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
    }

    private function ensureIndex($tableName, $indexName, $alterSql)
    {
        $stmt = $this->conn->prepare("SHOW INDEX FROM `{$tableName}` WHERE Key_name = :index_name");
        $stmt->bindValue(':index_name', $indexName);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $this->conn->exec($alterSql);
        }
    }

    private function ensureColumn($tableName, $columnName, $alterSql)
    {
        $stmt = $this->conn->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE :column_name");
        $stmt->bindValue(':column_name', $columnName);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $this->conn->exec($alterSql);
        }
    }
}
