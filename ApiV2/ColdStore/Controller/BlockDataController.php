<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class BlockDataController
{
    private $conn;
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

    public function postBlockDetails($RequestBody)
    {
        $BlockID = $RequestBody['BlockID'];

        if ($BlockID == '') {
            $Data = $this->insertBlockDetails($RequestBody);
        } else {
            $Data = $this->updateBlockDetails($RequestBody);
        }

        return $Data;
    }

    public function insertBlockDetails($RequestBody)
    {

        $BlockName = $RequestBody['BlockName'];
        $Floors = $RequestBody['Floor'];
        $Status = $RequestBody['Status'];

        $SqlRowCount = "SELECT  * FROM `CsStoresBlockData` WHERE BlockName='$BlockName'";
        $stmtRowCount = $this->conn->prepare($SqlRowCount);
        $stmtRowCount->execute();
        $RowCount = $stmtRowCount->rowCount();

        if ($RowCount == 0) {

            $sqlInsert = "INSERT INTO `CsStoresBlockData`( `BlockName`, `Floors`, `BlockStatus`) 
            VALUES('$BlockName','$Floors','$Status') ";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['BlockID'] = $lastInsertedId;
                $createdBlock = $this->auditLogger->fetchRowById('CsStoresBlockData', 'BlockID', $lastInsertedId);
                $this->auditLogger->logInsert('CsStoresBlockData', $lastInsertedId, $createdBlock, $RequestBody, [
                    'ReferenceNo' => $BlockName,
                    'Remarks' => 'Cold store block created',
                ]);

                $BlockData = $this->getBlockDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Block Posted Successfully';
                $Data['Data'] = $BlockData['Data'];
                return $Data;
            }
        } 
        
        else {
            $Data['Error'] = $BlockName . ' Already Exists';
            $Data['Msg'] = $this->Error;
            return $Data;
        }

    }

    public function updateBlockDetails($RequestBody)
    {
        $BlockID = $RequestBody['BlockID'];
        $BlockName = $RequestBody['BlockName'];
        $Floors = $RequestBody['Floor'];
        $Status = $RequestBody['Status'];
        $existingBlock = $this->auditLogger->fetchRowById('CsStoresBlockData', 'BlockID', $BlockID);


        $sqlUpdate = "UPDATE `CsStoresBlockData` SET  `BlockName`='$BlockName',`Floors`='$Floors',`BlockStatus`='$Status' 
        WHERE `BlockID`='$BlockID'";
        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedBlock = $this->auditLogger->fetchRowById('CsStoresBlockData', 'BlockID', $BlockID);
            $this->auditLogger->logUpdate('CsStoresBlockData', $BlockID, $existingBlock, $updatedBlock, $RequestBody, [
                'ReferenceNo' => $BlockName,
                'Remarks' => 'Cold store block updated',
            ]);
            $BlockData = $this->getBlockDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Block updated successfully';
            $Data['Data'] = $BlockData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update block';
            $Data['Msg'] = $this->Error;
            return $Data;
        }


    }



    function getBlockDetails($RequestBody)
    {

        // 
        $RecordsPerPage = 100;
        $BlockID = $RequestBody['BlockID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        // if ($BlockID != "") {
        //     $Query .= " and BlockID = '$BlockID'";
        // }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " WHERE CONCAT_WS('', BlockName,BlockID) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsStoresBlockData` ";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "`BlockID`, `BlockName`,  `Floors`,  `BlockStatus`";

        if ($BlockID != '') {
            $sqlQuery = "SELECT  $sqlFields FROM `CsStoresBlockData` WHERE BlockID='$BlockID'";
            $LimitMax = 0;
        } else {

            $sqlQuery = "SELECT  $sqlFields FROM `CsStoresBlockData` as CSBD            
             $Query ORDER BY CSBD.BlockID " . $Limit;
        }

        //    echo $sqlQuery; exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayFloorData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
           $FloorIDs= $dataRow['Floors'];

            $sqlFloor = " SELECT GROUP_CONCAT(FloorName) as FloorName FROM `CsStoresFloorData` WHERE FloorID in ($FloorIDs)";
            $stmtFloor = $this->conn->prepare($sqlFloor);
            $stmtFloor->execute();
            $dataRowFloor = $stmtFloor->fetch(PDO::FETCH_ASSOC);
           

            $ArrayBlockDetails['SNO'] = ++$i;
            $ArrayBlockDetails['BlockID'] = $dataRow['BlockID'];
            $ArrayBlockDetails['BlockName'] = $dataRow['BlockName'];
            $ArrayBlockDetails['FloorIDs'] = $FloorIDs;
            $ArrayBlockDetails['FloorName'] = $dataRowFloor['FloorName'];
            $ArrayBlockDetails['Status'] = $dataRow['BlockStatus'];

            $ArrayBlockData[] = $ArrayBlockDetails;
        }

        if (count($ArrayBlockData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "cs-create-block.php?BlockID=";
        $data['Data'] = $ArrayBlockData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
