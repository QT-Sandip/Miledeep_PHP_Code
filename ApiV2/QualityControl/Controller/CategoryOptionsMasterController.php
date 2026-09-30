<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class CategoryOptionsMasterController
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

    public function postCategoryOptionsMasterDetails($RequestBody)
    {
        $OptionID = $RequestBody['OptionID'];
        $OptionCategoryName = $RequestBody['OptionCategoryName'];
        $PlantID = $RequestBody['PlantID'];
        $OptionName = $RequestBody['OptionName'];
        $Status = $RequestBody['Status'];

        if ($OptionID == '') {
            $sqlInsertCheck = "SELECT COUNT(*) AS InsertRowCount FROM QcAuditDropdownMasters 
            WHERE OptionCategoryName = '$OptionCategoryName' AND OptionName = '$OptionName' AND PlantID = '$PlantID'";
            $stmtInsertCheck = $this->conn->prepare($sqlInsertCheck);
            $stmtInsertCheck->execute();
            $rowInsertCheck =  $stmtInsertCheck->fetch(PDO::FETCH_ASSOC);
            $InsertRowCount = $rowInsertCheck['InsertRowCount'];
            if ($InsertRowCount == 0) {

                $sqlInsert = "INSERT INTO QcAuditDropdownMasters(OptionCategoryName, OptionName,PlantID, Status) 
                VALUES ('$OptionCategoryName', '$OptionName', '$PlantID', '$Status')";
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();
                // $errorInfo = $stmtInsert->errorInfo();
                // var_dump($errorInfo);

                if ($stmtInsert) {
                    $lastInsertedId = $this->conn->lastInsertId();
                    $createdOption = $this->auditLogger->fetchRowById('QcAuditDropdownMasters', 'OptionID', $lastInsertedId);
                    $this->auditLogger->logInsert('QcAuditDropdownMasters', $lastInsertedId, $createdOption, $RequestBody, [
                        'ReferenceNo' => $lastInsertedId,
                        'Remarks' => 'QC category option master created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Category Option Posted Successfully';
                } else {
                    $this->Error = "Unable to Post Category Option";
                    $this->Msg = "Unable to Post Category Option";
                }
            } else {
                $this->Error = "Category Option already exist";
                $this->Msg = "Category Option already exist";
            }
        } else {
            $sqlUpdateCheck = "SELECT COUNT(*) AS UpdateRowCount FROM QcAuditDropdownMasters 
            WHERE OptionCategoryName = '$OptionCategoryName' AND OptionName = '$OptionName' AND PlantID = '$PlantID' AND OptionID != '$OptionID'";
            $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
            $stmtUpdateCheck->execute();
            $rowUpdateCheck =  $stmtUpdateCheck->fetch(PDO::FETCH_ASSOC);
            $UpdateRowCount = $rowUpdateCheck['UpdateRowCount'];
            if ($UpdateRowCount == 0) {
                $existingOption = $this->auditLogger->fetchRowById('QcAuditDropdownMasters', 'OptionID', $OptionID);
                $sqlUpdate = "UPDATE QcAuditDropdownMasters SET OptionCategoryName='$OptionCategoryName',
                OptionName='$OptionName', PlantID='$PlantID', Status='$Status' 
                WHERE OptionID='$OptionID'";
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();
                // $errorInfo = $stmtUpdate->errorInfo();
                // var_dump($errorInfo);

                if ($stmtUpdate) {
                    $updatedOption = $this->auditLogger->fetchRowById('QcAuditDropdownMasters', 'OptionID', $OptionID);
                    $this->auditLogger->logUpdate('QcAuditDropdownMasters', $OptionID, $existingOption, $updatedOption, $RequestBody, [
                        'ReferenceNo' => $OptionID,
                        'Remarks' => 'QC category option master updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Category Option Updated Successfully';
                } else {
                    $this->Error = "Unable to Update Category Option";
                    $this->Msg = "Unable to Update Category Option";
                }
            } else {
                $this->Error = "Category Option already exist";
                $this->Msg = "Category Option already exist";
            }
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;

        return $Data;
    }

    function getCategoryOptionsMasterData($RequestBody)
    {
        $PlantID = $RequestBody['PlantID'];
        $OptionID = $RequestBody['OptionID'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($PlantID != '') {
            $Query .= " AND PlantID = '$PlantID'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('',OptionID, OptionCategoryName, OptionName, PlantID, Status) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcAuditDropdownMasters 
        WHERE 1=1 $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "OptionID, OptionCategoryName, OptionName, PlantID, Status";

        if ($OptionID) {
            $sqlSelect = "SELECT $sqlFields FROM QcAuditDropdownMasters
            WHERE OptionID = '$OptionID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcAuditDropdownMasters 
            WHERE 1=1 $Query  ORDER BY OptionCategoryName " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $erroInfo = $stmtSelect->errorInfo();
        // var_dump($erroInfo);

        $ArrayData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayList['SNO'] = ++$i;
            $ArrayList['OptionID'] = $dataRow['OptionID'];
            $ArrayList['OptionCategoryName'] = $dataRow['OptionCategoryName'];
            $ArrayList['OptionName'] = $dataRow['OptionName'];
            $ArrayList['Status'] = $dataRow['Status'];
            $ArrayList['EditLink'] = 'audit-dropdowns-master-create?OptionID=' . $dataRow['OptionID'];

            $ArrayData[] = $ArrayList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['OptionsData'] = $ArrayData;

        return $Data;
    }

    public function deleteCategoryOptionsMasterDetals($RequestBody)
    {
        $OptionID = $RequestBody['OptionID'];
        $existingOption = $this->auditLogger->fetchRowById('QcAuditDropdownMasters', 'OptionID', $OptionID);

        $sqlDelete = "DELETE FROM QcAuditDropdownMasters WHERE OptionID = '$OptionID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        // $errorInfo = $stmtDelete->errorInfo();
        // var_dump($errorInfo);

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcAuditDropdownMasters', $OptionID, $existingOption, $RequestBody, [
                'ReferenceNo' => $OptionID,
                'Remarks' => 'QC category option master deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'Category Option Delete Successfully';
        } else {
            $this->Error = "Unable to Delete Category Option";
            $this->Msg = "Unable to Delete Category Option";
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        return  $Data;
    }
}
