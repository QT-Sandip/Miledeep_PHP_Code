<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GeneralCategoriesController
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

    public function postCategoryDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];

        if ($ID == '') {
            $Data = $this->insertCategoryDetails($RequestBody);
        } else {
            $Data = $this->updateCategoryDetails($RequestBody);
        }

        return $Data;
    }

    public function insertCategoryDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];
        $CategoryName = $RequestBody['CategoryName'];
        $Status = $RequestBody['Status'];

        $sqlInsertCheck = "SELECT ID FROM AdminGeneralCategories WHERE CategoryName = '$CategoryName'";
        $stmtInsertCheck = $this->conn->prepare($sqlInsertCheck);
        $stmtInsertCheck->execute();

        $rowCount = $stmtInsertCheck->rowCount();

        if ($rowCount == 0) {

            $sqlInsert = "INSERT INTO AdminGeneralCategories(ID, CategoryName, Status) VALUES ('$ID','$CategoryName','$Status')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['ID'] = $lastInsertedId;
                $createdCategory = $this->auditLogger->fetchRowById('AdminGeneralCategories', 'ID', $lastInsertedId);
                $this->auditLogger->logInsert('AdminGeneralCategories', $lastInsertedId, $createdCategory, $RequestBody, [
                    'ReferenceNo' => $CategoryName,
                    'Remarks' => 'Cold store general category created',
                ]);

                $CategoryData = $this->getCategoryDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Category Posted Successfully';
                $Data['Data'] = $CategoryData['Data'];
                return $Data;
            } else {
                $Data['Error'] = 'Unable to Insert Category';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $Data['Error']  = 'Category Name already exists';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateCategoryDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];
        $CategoryName = $RequestBody['CategoryName'];
        $Status = $RequestBody['Status'];
        $existingCategory = $this->auditLogger->fetchRowById('AdminGeneralCategories', 'ID', $ID);

        $sqlUpdateCheck = "SELECT * FROM AdminGeneralCategories WHERE CategoryName = '$CategoryName' AND ID != '$ID'";
        $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
        $stmtUpdateCheck->execute();

        $rowCount = $stmtUpdateCheck->rowCount();

        if ($rowCount == 0) {
            $sqlUpdate = "UPDATE AdminGeneralCategories SET CategoryName='$CategoryName',Status='$Status' WHERE ID='$ID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $updatedCategory = $this->auditLogger->fetchRowById('AdminGeneralCategories', 'ID', $ID);
                $this->auditLogger->logUpdate('AdminGeneralCategories', $ID, $existingCategory, $updatedCategory, $RequestBody, [
                    'ReferenceNo' => $CategoryName,
                    'Remarks' => 'Cold store general category updated',
                ]);
                $CategoryData = $this->getCategoryDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Category Updated Successfully';
                $Data['Data'] = $CategoryData['Data'];
                return $Data;
            } else {
                $Data['Error'] = 'Unable to update Category';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $Data['Error']  = 'Category Name already exists';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getCategoryDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ID = $RequestBody['ID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($ID != "") {
            $Query .= " and AdminGeneralCategories.ID = '$ID'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ID,CategoryName,Status) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `AdminGeneralCategories` WHERE Status=1";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        if ($ID != '') {
            $sqlQuery = "SELECT ID, CategoryName, Status FROM AdminGeneralCategories WHERE  ID='$ID'";
        } else {
            // echo 
            $sqlQuery = "SELECT ID, CategoryName, Status FROM AdminGeneralCategories WHERE 1=1  $Query ORDER BY ID " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayCategoryData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayCategoryList['SNO'] = ++$i;
            $ArrayCategoryList['ID'] = $dataRow['ID'];
            $ArrayCategoryList['CategoryName'] = $dataRow['CategoryName'];
            $ArrayCategoryList['Status'] = $dataRow['Status'];

            $ArrayCategoryData[] =  $ArrayCategoryList;
        }

        if (count($ArrayCategoryData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "cs-create-general-categories.php?ID=";
        $data['Data'] = $ArrayCategoryData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
