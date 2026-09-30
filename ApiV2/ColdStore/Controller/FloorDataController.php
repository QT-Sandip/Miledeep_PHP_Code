<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FloorDataController
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

    public function postFloorDetails($RequestBody)
    {
        $FloorID = $RequestBody['FloorID'];

        if ($FloorID == '') {
            $Data = $this->insertFloorDetails($RequestBody);
        } else {
            $Data = $this->updateFloorDetails($RequestBody);
        }

        return $Data;
    }

    public function insertFloorDetails($RequestBody)
    {

        $FloorName = $RequestBody['FloorName'];
        $Status = $RequestBody['Status'];

        $SqlRowCount = "SELECT  * FROM `CsStoresFloorData` WHERE FloorName='$FloorName'";
        $stmtRowCount = $this->conn->prepare($SqlRowCount);
        $stmtRowCount->execute();
        $RowCount = $stmtRowCount->rowCount();

        if ($RowCount == 0) {
            $sqlInsert = "INSERT INTO `CsStoresFloorData`(`FloorName`,`Status`)  VALUES ('$FloorName','$Status')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['FloorID'] = $lastInsertedId;
                $createdFloor = $this->auditLogger->fetchRowById('CsStoresFloorData', 'FloorID', $lastInsertedId);
                $this->auditLogger->logInsert('CsStoresFloorData', $lastInsertedId, $createdFloor, $RequestBody, [
                    'ReferenceNo' => $FloorName,
                    'Remarks' => 'Cold store floor created',
                ]);

                $FloorData = $this->getFloorDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Floor Posted Successfully';
                $Data['Data'] = $FloorData['Data'];
                return $Data;
            }
        } else {
            $Data['Error'] = $FloorName . ' Already Exists';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateFloorDetails($RequestBody)
    {
        $FloorID = $RequestBody['FloorID'];
        $FloorName = $RequestBody['FloorName'];
        $Status = $RequestBody['Status'];
        $existingFloor = $this->auditLogger->fetchRowById('CsStoresFloorData', 'FloorID', $FloorID);



        $sqlUpdate = "UPDATE `CsStoresFloorData` SET  `FloorName`='$FloorName',`Status`='$Status' WHERE `FloorID`='$FloorID'";
        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedFloor = $this->auditLogger->fetchRowById('CsStoresFloorData', 'FloorID', $FloorID);
            $this->auditLogger->logUpdate('CsStoresFloorData', $FloorID, $existingFloor, $updatedFloor, $RequestBody, [
                'ReferenceNo' => $FloorName,
                'Remarks' => 'Cold store floor updated',
            ]);
            $FloorData = $this->getFloorDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Floor Updated Successfully';
            $Data['Data'] = $FloorData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Floor';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }



    function getFloorDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $FloorID = $RequestBody['FloorID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';
        
        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " WHERE  CONCAT_WS('', FloorName,FloorID) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsStoresFloorData` ";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "`FloorID`, `FloorName`, `Status`";

        if ($FloorID != '') {
            $sqlQuery = "SELECT  $sqlFields FROM `CsStoresFloorData` as CSFD  WHERE  FloorID='$FloorID'";
            $LimitMax = 0;
        } else {

            $sqlQuery = "SELECT  $sqlFields FROM `CsStoresFloorData` as CSFD            
             $Query ORDER BY CSFD.FloorID " . $Limit;
        }

        //    echo $sqlQuery; exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayFloorData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayFloorDetails['SNO'] = ++$i;
            $ArrayFloorDetails['FloorID'] = $dataRow['FloorID'];
            $ArrayFloorDetails['FloorName'] = $dataRow['FloorName'];
            $ArrayFloorDetails['Status'] = $dataRow['Status'];

            $ArrayFloorData[] = $ArrayFloorDetails;
        }

        if (count($ArrayFloorData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "cs-create-floor.php?FloorID=";
        $data['Data'] = $ArrayFloorData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
