<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class AuditCategoriesMastersController
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

    public function postAuditCategoriesDetails($RequestBody)
    {
        $AuditID = $RequestBody['AuditID'];
        $CategoryName = $RequestBody['CategoryName'];
        $PlantID = $RequestBody['PlantID'];
        $Status = $RequestBody['Status'];
        $AuditCertificationNo = $RequestBody['AuditCertificationNo'];

        //$Status = 1;

        if ($AuditID == '') {

            $sqlCheckInsert = "SELECT * FROM QcAuditCategoriesMaster WHERE `CategoryName` = '$CategoryName' and `PlantID` = '$PlantID'";

            $stmtCheckInsert = $this->conn->prepare($sqlCheckInsert);
            $stmtCheckInsert->execute();
            $rowCount = $stmtCheckInsert->rowCount();

            if ($rowCount == 0) {
                $sqlInsert = "INSERT INTO `QcAuditCategoriesMaster`(`CategoryName`,`PlantID`, `AuditCertificationNo`, `Status`) VALUES ('$CategoryName','$PlantID','$AuditCertificationNo','$Status')";
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();
                if ($stmtInsert) {
                    $lastInsertedId = $this->conn->lastInsertId();
                    $createdCategory = $this->auditLogger->fetchRowById('QcAuditCategoriesMaster', 'AuditID', $lastInsertedId);
                    $this->auditLogger->logInsert('QcAuditCategoriesMaster', $lastInsertedId, $createdCategory, $RequestBody, [
                        'ReferenceNo' => $lastInsertedId,
                        'Remarks' => 'QC audit category master created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Audit Category Posted Successfully';
                }
            } else {
                $this->Error = 'Audit Category Name Already Exists';
                $this->Msg = 'Audit Category Name Already Exists';
            }
        } else {

            $sqlUpdateCheck = "SELECT * FROM QcAuditCategoriesMaster 
            WHERE `CategoryName` = '$CategoryName' AND `PlantID` = '$PlantID' AND AuditID != '$AuditID'";

            $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
            $stmtUpdateCheck->execute();
            $rowCountUpdate = $stmtUpdateCheck->rowCount();

            if ($rowCountUpdate == 0) {
                $existingCategory = $this->auditLogger->fetchRowById('QcAuditCategoriesMaster', 'AuditID', $AuditID);

                $sqlUpdate = "UPDATE `QcAuditCategoriesMaster`  SET  `CategoryName` = '$CategoryName',`AuditCertificationNo` = '$AuditCertificationNo',`Status` = '$Status'
                WHERE `AuditID` = '$AuditID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $updatedCategory = $this->auditLogger->fetchRowById('QcAuditCategoriesMaster', 'AuditID', $AuditID);
                    $this->auditLogger->logUpdate('QcAuditCategoriesMaster', $AuditID, $existingCategory, $updatedCategory, $RequestBody, [
                        'ReferenceNo' => $AuditID,
                        'Remarks' => 'QC audit category master updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Audit Category Updated Successfully';
                }
            } else {
                $this->Error = 'Audit Category Name Already Exists';
                $this->Msg = 'Audit Category Name Already Exists';
            }
        }

        $getData = $this->getAuditCategoryDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }


    function getAuditCategoryDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $AuditID = $RequestBody['AuditID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $PlantID = $RequestBody['PlantID'];

        $Query = '';

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($PlantID != '') {
            $Query = " and PlantID='$PlantID'";
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', AuditID, CategoryName, Status) LIKE '%$SearchKey%'";
        }

        $sqlSpeciesCount = "SELECT COUNT(*) as Total FROM QcAuditCategoriesMaster WHERE 1=1 $Query";
        $stmt = $this->conn->prepare($sqlSpeciesCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "AuditID,PlantID,CategoryName,Status,AuditCertificationNo";

        if ($AuditID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `QcAuditCategoriesMaster` WHERE AuditID='$AuditID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `QcAuditCategoriesMaster` WHERE 1=1 $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        $ArrayAuditData = [];
        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAuditList['SNO'] = ++$i;
            $ArrayAuditList['AuditID'] = $dataRow['AuditID'];
            $ArrayAuditList['CategoryName'] = $dataRow['CategoryName'];
            $ArrayAuditList['Status'] = $dataRow['Status'];
            $ArrayAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayAuditList['AuditCertificationNo'] = $dataRow['AuditCertificationNo'];
            $ArrayAuditList['EditLink'] = "audit-categories-create?AuditID=" . $dataRow['AuditID'];

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
