<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class StoreTenantsController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postTenantDetails($RequestBody)
    {
        $TenantID = $RequestBody['TenantID'];

        if ($TenantID == '') {
            $Data = $this->insertTenantDetails($RequestBody);
        } else {
            $Data = $this->updateTenantDetails($RequestBody);
        }

        return $Data;
    }

    public function insertTenantDetails($RequestBody)
    {
        $TenantID = $RequestBody['TenantID'];
        $TenantName = $RequestBody['TenantName'];
        $TenantAddress = $RequestBody['TenantAddress'];
        $TenantType = $RequestBody['TenantType'];
        $TenantStatus = $RequestBody['TenantStatus'];
        $ProcessorID = $RequestBody['ProcessorID'];

        $sqlInsert = "INSERT INTO CsTenant(TenantID, TenantName, TenantAddress, TenantType, TenantStatus,  ProcessorID)
        VALUES ('$TenantID','$TenantName','$TenantAddress','$TenantType','$TenantStatus','$ProcessorID')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['TenantID'] = $lastInsertedId;
            $createdTenant = $this->auditLogger->fetchRowById('CsTenant', 'TenantID', $lastInsertedId);
            $this->auditLogger->logInsert('CsTenant', $lastInsertedId, $createdTenant, $RequestBody, [
                'ReferenceNo' => $TenantName,
                'Remarks' => 'Cold store tenant created',
            ]);

            $TetantData = $this->getTetantDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Tetant Posted Successfully';
            $Data['Data'] = $TetantData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Tetant';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateTenantDetails($RequestBody)
    {
        $TenantID = $RequestBody['TenantID'];
        $TenantName = $RequestBody['TenantName'];
        $TenantAddress = $RequestBody['TenantAddress'];
        $TenantType = $RequestBody['TenantType'];
        $TenantStatus = $RequestBody['TenantStatus'];
        $ProcessorID = $RequestBody['ProcessorID'];
        $existingTenant = $this->auditLogger->fetchRowById('CsTenant', 'TenantID', $TenantID);

        $sqlUpdate = "UPDATE CsTenant SET TenantName='$TenantName', TenantAddress='$TenantAddress',
        TenantType='$TenantType',TenantStatus='$TenantStatus',ProcessorID='$ProcessorID' WHERE TenantID='$TenantID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedTenant = $this->auditLogger->fetchRowById('CsTenant', 'TenantID', $TenantID);
            $this->auditLogger->logUpdate('CsTenant', $TenantID, $existingTenant, $updatedTenant, $RequestBody, [
                'ReferenceNo' => $TenantName,
                'Remarks' => 'Cold store tenant updated',
            ]);
            $TetantData = $this->getTetantDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Tenant Updated Successfully';
            $Data['Data'] = $TetantData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Tetant';
            $Data['Msg'] = $this->Error;
            return $Data;
        }

    }

    function getTetantDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $TenantID = $RequestBody['TenantID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($TenantID != "") {
            $Query .= " and CsTenant.TenantID = '$TenantID'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " CONCAT_WS('', TenantID,TenantName,TenantAddress,TenantType, TenantStatus,ProcessorID) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsTenant` ";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "CTN.TenantID, CTN.TenantName, CTN.TenantAddress, CTN.TenantType, CTN.TenantStatus,CTN.ProcessorID,RPS.StationName,
        CASE CTN.TenantType WHEN 1 THEN 'For Own' WHEN 2 THEN 'For Others' END AS TenantTypeName";

        if ($TenantID != '') {
            $sqlQuery = "SELECT $sqlFields FROM CsTenant AS CTN
            LEFT JOIN RmProcessingStations AS RPS ON CTN.ProcessorID = RPS.ProcessingStationID
            WHERE  CTN.TenantID='$TenantID'";
        } else {
  
            $sqlQuery = "SELECT $sqlFields FROM CsTenant AS CTN
            LEFT JOIN RmProcessingStations AS RPS ON CTN.ProcessorID=RPS.ProcessingStationID
              $Query ORDER BY CTN.TenantID " . $Limit;
        }
        //echo $sqlQuery; exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayTenantData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayTenantList['SNO'] = ++$i;
            $ArrayTenantList['TenantID'] = $dataRow['TenantID'];
            $ArrayTenantList['TenantName'] = $dataRow['TenantName'];
            $ArrayTenantList['TenantAddress'] = $dataRow['TenantAddress'];
            $ArrayTenantList['TenantType'] = $dataRow['TenantType'];
            $ArrayTenantList['TenantTypeName'] = $dataRow['TenantTypeName'];
            $ArrayTenantList['TenantStatus'] = $dataRow['TenantStatus'];
            // $ArrayTenantList['SortOrder'] = $dataRow['SortOrder'];
            $ArrayTenantList['ProcessorID'] = $dataRow['ProcessorID'];
            $ArrayTenantList['StationName'] = $dataRow['StationName'];

            $ArrayTenantData[] =  $ArrayTenantList;
        }

        if (count($ArrayTenantData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "cs-create-tenants.php?TenantID=";
        $data['Data'] = $ArrayTenantData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
