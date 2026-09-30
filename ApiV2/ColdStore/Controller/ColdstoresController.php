<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class ColdstoresController
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

    public function postCsStoresDetails($RequestBody)
    {
        $StoreID = $RequestBody['StoreID'];

        if ($StoreID == '') {
            $Data = $this->insertStoreDetails($RequestBody);
        } else {
            $Data = $this->updateStoreDetails($RequestBody);
        }

        return $Data;
    }

    public function insertStoreDetails($RequestBody)
    {
        $StoreID = $RequestBody['StoreID'];
        $StoreName = $RequestBody['StoreName'];
        $StoreLocation = $RequestBody['StoreLocation'];
        $StoreAddress = $RequestBody['StoreAddress'];
        $Incharge = $RequestBody['Incharge'];
        // $SortOrder = $RequestBody['SortOrder'];
        $StoreStatus = $RequestBody['StoreStatus'];
        $StorePhoneNumber = $RequestBody['StorePhoneNumber'];
        $StoreGSTNumber = $RequestBody['StoreGSTNumber'];
        $ProductionPlantID = $RequestBody['ProductionPlantID'];
        $StoreBlocks = $RequestBody['StoreBlocks'];
        

        $existingStoreName = $this->checkDuplicateStoreName($StoreName);

        if (!$existingStoreName) {

        $sqlInsert = "INSERT INTO CsStores(StoreID, StoreName, StoreLocation, StoreAddress, Incharge, StoreStatus, 
        StorePhoneNumber, StoreGSTNumber,ProductionPlantID,StoreBlocks) 
        VALUES ('$StoreID','$StoreName','$StoreLocation','$StoreAddress','$Incharge','$StoreStatus',
        '$StorePhoneNumber','$StoreGSTNumber','$ProductionPlantID','$StoreBlocks')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        // $errorInfo = $stmtInsert->errorInfo();
        // var_dump($errorInfo);

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['StoreID'] = $lastInsertedId;
            $createdStore = $this->auditLogger->fetchRowById('CsStores', 'StoreID', $lastInsertedId);
            $this->auditLogger->logInsert('CsStores', $lastInsertedId, $createdStore, $RequestBody, [
                'ReferenceNo' => $StoreName,
                'Remarks' => 'Cold store created',
            ]);

            $StoreData = $this->getCsStoresDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Station Posted Successfully';
            $Data['Data'] = $StoreData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Station';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
        } else {
            $Data['Error']  = 'Store Name already exists';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateStoreDetails($RequestBody)
    {
        $StoreID = $RequestBody['StoreID'];
        $StoreName = $RequestBody['StoreName'];
        $StoreLocation = $RequestBody['StoreLocation'];
        $StoreAddress = $RequestBody['StoreAddress'];
        $Incharge = $RequestBody['Incharge'];
        // $SortOrder = $RequestBody['SortOrder'];
        $StoreStatus = $RequestBody['StoreStatus'];
        $StorePhoneNumber = $RequestBody['StorePhoneNumber'];
        $StoreGSTNumber = $RequestBody['StoreGSTNumber'];
        $StoreBlocks = $RequestBody['StoreBlocks'];
        $ProductionPlantID = $RequestBody['ProductionPlantID'];
        $existingStore = $this->auditLogger->fetchRowById('CsStores', 'StoreID', $StoreID);

        $existingUpdateStoreName = $this->checkDuplicateUpdateStoreName($StoreName, $StoreID);

        if (!$existingUpdateStoreName) {

        $sqlUpdate = "UPDATE CsStores SET StoreName='$StoreName', StoreLocation='$StoreLocation',StoreAddress='$StoreAddress',
        Incharge='$Incharge',StoreStatus='$StoreStatus',StorePhoneNumber='$StorePhoneNumber',StoreGSTNumber='$StoreGSTNumber', 
        StoreBlocks='$StoreBlocks',ProductionPlantID='$ProductionPlantID'
        WHERE StoreID='$StoreID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedStore = $this->auditLogger->fetchRowById('CsStores', 'StoreID', $StoreID);
            $this->auditLogger->logUpdate('CsStores', $StoreID, $existingStore, $updatedStore, $RequestBody, [
                'ReferenceNo' => $StoreName,
                'Remarks' => 'Cold store updated',
            ]);
            $StoreData = $this->getCsStoresDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Store Updated Successfully';
            $Data['Data'] = $StoreData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Store';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
        } else {
            $Data['Error']  = 'Store Name already exists';
            $Data['Msg'] = $this->Error;
            return $Data;
        }

    }

    public function checkDuplicateStoreName($StoreName)
    {
        $sqlCheck = "SELECT StoreID FROM CsStores WHERE StoreName = :StoreName";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':StoreName', $StoreName, PDO::PARAM_STR);
        $stmtCheck->execute();

        $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $stmtCheck->rowCount();
        return $stmtCheck->rowCount();
    }

    public function checkDuplicateUpdateStoreName($StoreName, $StoreID)
    {
        $sqlCheck = "SELECT StoreID FROM CsStores WHERE StoreName = :StoreName AND StoreID != :StoreID";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':StoreName', $StoreName, PDO::PARAM_STR);
        $stmtCheck->bindParam(':StoreID', $StoreID, PDO::PARAM_INT);
        $stmtCheck->execute();

        return  $stmtCheck->rowCount();
    }


    function getCsStoresDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $StoreID = $RequestBody['StoreID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($StoreID != "") {
            $Query .= " and CsStores.StoreID = '$StoreID'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', StoreID,StoreBlocks, StoreName, StoreLocation, StoreAddress, CS.Incharge, StoreStatus, 
            StorePhoneNumber, StoreGSTNumber,ProductionPlantID,StationName) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsStores` WHERE 1=1 $Query";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlQuery = "StoreID,StoreBlocks, StoreName, StoreLocation, StoreAddress, CS.Incharge, StoreStatus, 
        StorePhoneNumber, StoreGSTNumber,ProductionPlantID,StationName ";

        if ($StoreID != '') {
            $sqlQuery = "SELECT $sqlQuery FROM CsStores As CS 
            Left Join RmProcessingStations As RPS On CS.ProductionPlantID = RPS.ProcessingStationID
            WHERE  StoreID='$StoreID' ";
        } else {
            $sqlQuery = "SELECT $sqlQuery FROM CsStores As CS
            Left Join RmProcessingStations As RPS On CS.ProductionPlantID = RPS.ProcessingStationID
            WHERE 1=1 $Query ORDER BY StoreID " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayStoresData = [];

        $i = $LimitMax;
        $SNo='1';
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayStoresList['SNO'] = $SNo++;
            $ArrayStoresList['StoreID'] = $dataRow['StoreID'];
            $ArrayStoresList['StoreName'] = $dataRow['StoreName'];
            $ArrayStoresList['StoreLocation'] = $dataRow['StoreLocation'];
            $ArrayStoresList['StoreAddress'] = $dataRow['StoreAddress'];
            $ArrayStoresList['Incharge'] = $dataRow['Incharge'];
            $ArrayStoresList['StoreStatus'] = $dataRow['StoreStatus'];
            $ArrayStoresList['StoreBlocks'] = $dataRow['StoreBlocks'];
            $ArrayStoresList['StorePhoneNumber'] = $dataRow['StorePhoneNumber'];
            $ArrayStoresList['StoreGSTNumber'] = $dataRow['StoreGSTNumber'];
            $ArrayStoresList['ProductionPlantID'] = $dataRow['ProductionPlantID'];
            $ArrayStoresList['StationName'] = $dataRow['StationName'];

            $ArrayStoresData[] =  $ArrayStoresList;
        }

        if (count($ArrayStoresData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "cs-create-coldstores.php?StoreID=";
        $data['Data'] = $ArrayStoresData;

        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }


}
