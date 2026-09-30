<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GeneralOptionsController
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

    public function postOptionsDetails($RequestBody)
    {
        $OptID = $RequestBody['OptID'];

        if ($OptID == '') {
            $Data = $this->insertOptionsDetails($RequestBody);
        } else {
            $Data = $this->updateOptionsDetails($RequestBody);
        }

        return $Data;
    }

    public function insertOptionsDetails($RequestBody)
    {
        $OptID = $RequestBody['OptID'];
        $DeptID = $RequestBody['DeptID'];
        $OptionType = $RequestBody['OptionType'];
        $Value = $RequestBody['Value'];
        $Name = $RequestBody['Name'];
        // $SortOrder = $RequestBody['SortOrder'];
        $Status = $RequestBody['Status'];

        // $existingCategory = $this->checkDuplicateCategory($CategoryName);

        // if (!$existingCategory) {

        $sqlInsert = "INSERT INTO AdminGeneralOptions(OptID, DeptID, OptionType, Value, Name, Status) 
            VALUES ('$OptID','$DeptID','$OptionType','$Value','$Name','$Status')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['OptID'] = $lastInsertedId;
            $createdOption = $this->auditLogger->fetchRowById('AdminGeneralOptions', 'OptID', $lastInsertedId);
            $this->auditLogger->logInsert('AdminGeneralOptions', $lastInsertedId, $createdOption, $RequestBody, [
                'ReferenceNo' => $Name,
                'Remarks' => 'Cold store general option created',
            ]);

            $OptionsData = $this->getOptionDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Option Posted Successfully';
            $Data['Data'] = $OptionsData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Option';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
        // } else {
        //     $Data['Error']  = 'Category Name already exists';
        //     $Data['Msg'] = $this->Error;
        //     return $Data;
        // }
    }

    public function updateOptionsDetails($RequestBody)
    {
        $OptID = $RequestBody['OptID'];
        $DeptID = $RequestBody['DeptID'];
        $OptionType = $RequestBody['OptionType'];
        $Value = $RequestBody['Value'];
        $Name = $RequestBody['Name'];
        $Status = $RequestBody['Status'];
        $existingOption = $this->auditLogger->fetchRowById('AdminGeneralOptions', 'OptID', $OptID);

        $sqlUpdate = "UPDATE AdminGeneralOptions SET OptID='$OptID',DeptID='$DeptID',OptionType='$OptionType',Value='$Value',Name='$Name',Status='$Status' 
        WHERE OptID='$OptID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedOption = $this->auditLogger->fetchRowById('AdminGeneralOptions', 'OptID', $OptID);
            $this->auditLogger->logUpdate('AdminGeneralOptions', $OptID, $existingOption, $updatedOption, $RequestBody, [
                'ReferenceNo' => $Name,
                'Remarks' => 'Cold store general option updated',
            ]);
            $OptionsData = $this->getOptionDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Data Updated Successfully';
            $Data['Data'] = $OptionsData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Option';
            $Data['Msg'] = $this->Error;
            return $Data;
        }

    }


    function getOptionDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $OptID = $RequestBody['OptID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($OptID != "") {
            $Query .= " and AdminGeneralOptions.ID = '$OptID'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', OptID,DeptID,OptionType,Value, Name) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `AdminGeneralOptions` WHERE Status=1";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        if ($OptID != '') {
            $sqlQuery = "SELECT OptID, DeptID, OptionType, Value, Name, Status FROM AdminGeneralOptions WHERE   OptID='$OptID'";
        } else {
            // echo 
            $sqlQuery = "SELECT OptID, DeptID, OptionType, Value, Name, Status FROM AdminGeneralOptions WHERE 1=1  $Query ORDER BY OptID " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayOptionsData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayOptionsList['SNO'] = ++$i;
            $ArrayOptionsList['OptID'] = $dataRow['OptID'];
            $ArrayOptionsList['DeptID'] = $dataRow['DeptID'];
            $ArrayOptionsList['OptionType'] = $dataRow['OptionType'];
            $ArrayOptionsList['Value'] = $dataRow['Value'];
            $ArrayOptionsList['Name'] = $dataRow['Name'];
            $ArrayOptionsList['Status'] = $dataRow['Status'];

            $ArrayOptionsData[] =  $ArrayOptionsList;
        }

        if (count($ArrayOptionsData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "cs-create-general-options.php?OptID=";
        $data['Data'] = $ArrayOptionsData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
