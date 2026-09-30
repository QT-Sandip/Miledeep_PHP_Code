<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class SpeciesMastersController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postSpeciesDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];
        $SpeciesName = $RequestBody['SpeciesName'];
        $ShortCode = $RequestBody['ShortCode'];
        $QcSpeciesShortCode = $RequestBody['QcSpeciesShortCode'];
        $Description = $RequestBody['Description'];

        if ($ID == '') {

            $sqlCheckInsert = "SELECT * FROM ScrmSpeciesMaster WHERE `SpeciesName` = '$SpeciesName'";

            $stmtCheckInsert = $this->conn->prepare($sqlCheckInsert);
            $stmtCheckInsert->execute();
            $rowCount = $stmtCheckInsert->rowCount();

            if ($rowCount == 0) { 

                $sqlInsert = " INSERT INTO `ScrmSpeciesMaster`(`ID`, `SpeciesName`, `ShortCode`, `QcSpeciesShortCode`, 
                `Description`, `Status`) 
                VALUES ('$ID','$SpeciesName', '$ShortCode', '$QcSpeciesShortCode', '$Description','1')";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                // $errorInfo = $stmtInsert->errorInfo();
                // var_dump($errorInfo);


                if ($stmtInsert) {
                    $lastInsertedId = $this->conn->lastInsertId();
                    $RequestBody['ID'] = $lastInsertedId;
                    $createdSpecies = $this->auditLogger->fetchRowById('ScrmSpeciesMaster', 'ID', $lastInsertedId);
                    $this->auditLogger->logInsert('ScrmSpeciesMaster', $lastInsertedId, $createdSpecies, $RequestBody, [
                        'ReferenceNo' => $ShortCode,
                        'Remarks' => 'Export species created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Species Posted Successfully';
                }
            } else {
                $this->Error = 'Species Name Already Exists';
                $this->Msg = 'Species Name Already Exists';
            }
        } else {

            $sqlUpdateCheck = "SELECT * FROM ScrmSpeciesMaster 
            WHERE `SpeciesName` = '$SpeciesName' AND ID != '$ID'";

            $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
            $stmtUpdateCheck->execute();
            $rowCountUpdate = $stmtUpdateCheck->rowCount();

            if ($rowCountUpdate == 0) {
                $existingSpecies = $this->auditLogger->fetchRowById('ScrmSpeciesMaster', 'ID', $ID);

                $sqlUpdate = "UPDATE `ScrmSpeciesMaster`  SET  `SpeciesName` = '$SpeciesName',`ShortCode` = '$ShortCode',
                `QcSpeciesShortCode` = '$QcSpeciesShortCode',`Description` = '$Description', `Status` = '1'
                WHERE `ID` = '$ID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $updatedSpecies = $this->auditLogger->fetchRowById('ScrmSpeciesMaster', 'ID', $ID);
                    $this->auditLogger->logUpdate('ScrmSpeciesMaster', $ID, $existingSpecies, $updatedSpecies, $RequestBody, [
                        'ReferenceNo' => $ShortCode,
                        'Remarks' => 'Export species updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Species Updated Successfully';
                }
            } else {
                $this->Error = 'Species Name Already Exists';
                $this->Msg = 'Species Name Already Exists';
            }
        }

        $getData = $this->getSpeciesDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }


    function getSpeciesDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ID = $RequestBody['ID'];
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
            $Query = " and CONCAT_WS('', ID, SpeciesName, ShortCode, QcSpeciesShortCode, Description, Status) LIKE '%$SearchKey%'";
        }

        $sqlSpeciesCount = "SELECT COUNT(*) as Total FROM ScrmSpeciesMaster WHERE 1=1 $Query";
        $stmt = $this->conn->prepare($sqlSpeciesCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "ID, SpeciesName, ShortCode, QcSpeciesShortCode, Description ,Status";

        if ($ID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `ScrmSpeciesMaster` WHERE ID='$ID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `ScrmSpeciesMaster` WHERE Status=1 $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayBranchData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArraySpeciesList['SNO'] = ++$i;
            $ArraySpeciesList['ID'] = $dataRow['ID'];
            $ArraySpeciesList['SpeciesName'] = $dataRow['SpeciesName'];
            $ArraySpeciesList['ShortCode'] = $dataRow['ShortCode'];
            $ArraySpeciesList['QcSpeciesShortCode'] = $dataRow['QcSpeciesShortCode'];
            $ArraySpeciesList['Description'] = $dataRow['Description'];
            $ArraySpeciesList['EditLink'] = "species-master-create?ID=" . $dataRow['ID'];

            $ArraySpeciesData[] =  $ArraySpeciesList;
        }

        if (count($ArraySpeciesData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArraySpeciesData;

        return $data;
    }
}
