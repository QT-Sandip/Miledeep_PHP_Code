<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class AuditCategoriesOptionsMastersController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postAuditCategoriesOptionDetails($RequestBody)
    {
        $AuditOptionID = $RequestBody['AuditOptionID'];
        $AuditID = $RequestBody['AuditID'];
        $OptionName = $RequestBody['OptionName'];
        $OptionValue = $RequestBody['OptionValue'];
        $Status = $RequestBody['Status'];
        $PlantID = $RequestBody['PlantID'];

        if ($AuditOptionID == '') {

            $sqlCheckInsert = "SELECT * FROM QcAuditCategoriesOptionsMaster WHERE `AuditID` = '$AuditID' AND `OptionName` = '$OptionName' and `PlantID` = '$PlantID'";

            $stmtCheckInsert = $this->conn->prepare($sqlCheckInsert);
            $stmtCheckInsert->execute();
            $rowCount = $stmtCheckInsert->rowCount();

            if ($rowCount == 0) {

                $sqlInsert = " INSERT INTO `QcAuditCategoriesOptionsMaster`(`AuditID`, `OptionName`, `OptionValue`,`Status`,`PlantID`) 
                VALUES ('$AuditID','$OptionName','$OptionValue','$Status','$PlantID')";


                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                if ($stmtInsert) {
                    $lastInsertedId = $this->conn->lastInsertId();
                    $createdOption = $this->auditLogger->fetchRowById('QcAuditCategoriesOptionsMaster', 'AuditOptionID', $lastInsertedId);
                    $this->auditLogger->logInsert('QcAuditCategoriesOptionsMaster', $lastInsertedId, $createdOption, $RequestBody, [
                        'ReferenceNo' => $AuditID,
                        'Remarks' => 'QC audit category option created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Audit Option Posted Successfully';
                }
            } else {
                $this->Error = 'Audit Option Name Already Exists';
                $this->Msg = 'Audit Option Name Already Exists';
            }
        } else {

            $sqlUpdateCheck = "SELECT * FROM QcAuditCategoriesOptionsMaster 
            WHERE `OptionName` = '$OptionName' AND AuditOptionID != '$AuditOptionID' and PlantID='$PlantID'";

            $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
            $stmtUpdateCheck->execute();
            $rowCountUpdate = $stmtUpdateCheck->rowCount();

            if ($rowCountUpdate == 0) {
                $existingOption = $this->auditLogger->fetchRowById('QcAuditCategoriesOptionsMaster', 'AuditOptionID', $AuditOptionID);

                $sqlUpdate = "UPDATE `QcAuditCategoriesOptionsMaster` SET `AuditID`='$AuditID',`OptionName`='$OptionName',
                `OptionValue`='$OptionValue',`Status`='$Status' WHERE `AuditOptionID`='$AuditOptionID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();


                if ($stmtUpdate) {
                    $updatedOption = $this->auditLogger->fetchRowById('QcAuditCategoriesOptionsMaster', 'AuditOptionID', $AuditOptionID);
                    $this->auditLogger->logUpdate('QcAuditCategoriesOptionsMaster', $AuditOptionID, $existingOption, $updatedOption, $RequestBody, [
                        'ReferenceNo' => $AuditID,
                        'Remarks' => 'QC audit category option updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Audit Option Updated Successfully';
                }
            } else {
                $this->Error = 'Audit Option Name Already Exists';
                $this->Msg = 'Audit Option Name Already Exists';
            }
        }

        $getData = $this->getAuditCategoryOptionDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }


    function getAuditCategoryOptionDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $AuditID = $RequestBody['AuditID'];
        $AuditOptionID = $RequestBody['AuditOptionID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $PlantID = $RequestBody['PlantID'];


        $Query = '';

        if ($PlantID != '') {
            $Query = " AND QACOM.PlantID='$PlantID'";
        }
        if ($AuditID != '') {
            $Query .= " AND QACOM.AuditID='$AuditID'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', `AuditOptionID`, `AuditID`, `OptionName`, `OptionValue`, QACOM.Status) LIKE '%$SearchKey%'";
        }

        $sqlSpeciesCount = "SELECT COUNT(*) as Total FROM QcAuditCategoriesOptionsMaster WHERE 1=1 $Query";
        $stmt = $this->conn->prepare($sqlSpeciesCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "AuditOptionID, QACOM.AuditID, OptionName, OptionValue, QACOM.Status, CategoryName,QACOM.PlantID";

        if ($AuditOptionID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `QcAuditCategoriesOptionsMaster` AS QACOM
            LEFT JOIN `QcAuditCategoriesMaster` AS QACM ON QACOM.AuditID = QACM.AuditID 
            WHERE AuditOptionID='$AuditOptionID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `QcAuditCategoriesOptionsMaster` AS QACOM
            LEFT JOIN `QcAuditCategoriesMaster` AS QACM ON QACOM.AuditID = QACM.AuditID
            WHERE 1=1 $Query " . $Limit;
        }
        // echo $sqlQuery;
        // exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayAuditData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAuditList['SNO'] = ++$i;
            $ArrayAuditList['AuditOptionID'] = $dataRow['AuditOptionID'];
            $ArrayAuditList['AuditID'] = $dataRow['AuditID'];
            $ArrayAuditList['CategoryName'] = $dataRow['CategoryName'];
            $ArrayAuditList['OptionName'] = $dataRow['OptionName'];
            $ArrayAuditList['OptionValue'] = $dataRow['OptionValue'];
            $ArrayAuditList['Status'] = $dataRow['Status'];
            $ArrayAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayAuditList['EditLink'] = "audit-categories-create?AuditID=" . $dataRow['AuditID'];
            $ArrayAuditList['EditLink'] = "audit-categories-option-create?AuditOptionID=" . $dataRow['AuditOptionID'];

            $ArrayAuditData[] = $ArrayAuditList;
        }

        if (count($ArrayAuditData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayAuditData;

        return $data;
    }
}
