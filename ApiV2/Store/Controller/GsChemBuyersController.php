<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GsChemBuyersController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Store');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postBuyerDetails($RequestBody)
    {
        $ChemBuyerID = $RequestBody['ChemBuyerID'];

        if ($ChemBuyerID == '') {
            $Data = $this->insertBuyerDetails($RequestBody);
        } else {
            $Data = $this->updateBuyerDetails($RequestBody);
        }

        return $Data;
    }

    public function insertBuyerDetails($RequestBody)
    {
        $ChemBuyerID = $RequestBody['ChemBuyerID'];
        $BuyerName = $RequestBody['BuyerName'];
        $BuyerPhoneNumber = $RequestBody['BuyerPhoneNumber'];
        $BuyerAddress = $RequestBody['BuyerAddress'];
        $BuyerCountry = $RequestBody['BuyerCountry'];
        $EmailAddress = $RequestBody['EmailAddress'];
        $Status = $RequestBody['Status'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        // $PostedDateTime = $RequestBody['PostedDateTime'];
        $PostedUserID = $RequestBody['PostedUserID'];
        // echo 
        $sqlInsert = "INSERT INTO GsChemBuyersData(ChemBuyerID, BuyerName, BuyerPhoneNumber, BuyerAddress, BuyerCountry, EmailAddress, 
        Status, PostedDateTime, PostedUserID) 
        VALUES ('$ChemBuyerID','$BuyerName','$BuyerPhoneNumber','$BuyerAddress', '$BuyerCountry', '$EmailAddress', '$Status',
        '$PostedDate', '$PostedUserID')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        // $errorInfo = $stmtInsert->errorInfo();
        // var_dump($errorInfo);

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['ChemPoID'] = $lastInsertedId;
            $createdBuyer = $this->auditLogger->fetchRowById('GsChemBuyersData', 'ChemBuyerID', $lastInsertedId);
            $this->auditLogger->logInsert('GsChemBuyersData', $lastInsertedId, $createdBuyer, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'Store chemical buyer created',
            ]);

            $chemBuyerData = $this->getChemBuyerDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Buyer Posted Successfully';
            $Data['Data'] = $chemBuyerData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Buyer';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateBuyerDetails($RequestBody)
    {
        $ChemBuyerID = $RequestBody['ChemBuyerID'];
        $BuyerName = $RequestBody['BuyerName'];
        $BuyerPhoneNumber = $RequestBody['BuyerPhoneNumber'];
        $BuyerAddress = $RequestBody['BuyerAddress'];
        $BuyerCountry = $RequestBody['BuyerCountry'];
        $EmailAddress = $RequestBody['EmailAddress'];
        $Status = $RequestBody['Status'];
        // $PostedDateTime = $RequestBody['PostedDateTime'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $PostedUserID = $RequestBody['PostedUserID'];
        $existingBuyer = $this->auditLogger->fetchRowById('GsChemBuyersData', 'ChemBuyerID', $ChemBuyerID);

        $sqlUpdate = "UPDATE GsChemBuyersData SET BuyerName='$BuyerName',BuyerPhoneNumber='$BuyerPhoneNumber',BuyerAddress='$BuyerAddress',
        BuyerCountry='$BuyerCountry',EmailAddress='$EmailAddress',Status='$Status',PostedDateTime='$PostedDate',PostedUserID='$PostedUserID' 
        WHERE ChemBuyerID='$ChemBuyerID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $updatedBuyer = $this->auditLogger->fetchRowById('GsChemBuyersData', 'ChemBuyerID', $ChemBuyerID);
            $this->auditLogger->logUpdate('GsChemBuyersData', $ChemBuyerID, $existingBuyer, $updatedBuyer, $RequestBody, [
                'ReferenceNo' => $ChemBuyerID,
                'Remarks' => 'Store chemical buyer updated',
            ]);
            $chemBuyerData = $this->getChemBuyerDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Buyer Updated Successfully';
            $Data['Data'] = $chemBuyerData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Buyer';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getChemBuyerDetails($RequestBody)
    {
        $RecordsPerPage = 10;
        $ChemBuyerID = $RequestBody['ChemBuyerID'];
        $BuyerCountry = $RequestBody['BuyerCountry'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($ChemBuyerID != "") {
            $Query .= " and GCBD.ChemBuyerID = '$ChemBuyerID'";
        }

        if ($BuyerCountry != "") {
            $Query .= " and GCBD.BuyerCountry = '$BuyerCountry'";
        }

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ChemBuyerID,BuyerName,BuyerAddress,BuyerPhoneNumber,BuyerCountry, EmailAddress) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `GsChemBuyersData`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "GCBD.ChemBuyerID, GCBD.BuyerName, GCBD.BuyerAddress, GCBD.BuyerPhoneNumber, GCBD.BuyerCountry, SCL.name, GCBD.EmailAddress, 
                      GCBD.Status, GCBD.PostedDateTime, GCBD.PostedUserID";

        // SELECT ChemBuyerID, BuyerName, BuyerAddress, BuyerPhoneNumber, BuyerCountry, 
        // EmailAddress, Status, PostedDateTime, PostedUserID FROM GsChemBuyersData WHERE 1

        // SELECT id, iso, name, phonecode, Symbol, SymbolNative, CountryType, ShowFrightCharge, 
        // ShowBrand, showDelivery, GstCountryCode FROM ScrmCountriesList WHERE 1

        if ($ChemBuyerID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsChemBuyersData AS GCBD
            LEFT JOIN ScrmCountriesList AS SCL ON GCBD.BuyerCountry = SCL.id
            WHERE GCBD.ChemBuyerID='$ChemBuyerID'
            GROUP BY GCBD.ChemBuyerID";
        } else {

            $sqlQuery = "SELECT $sqlFields FROM GsChemBuyersData AS GCBD
            LEFT JOIN ScrmCountriesList AS SCL ON GCBD.BuyerCountry = SCL.id
            WHERE 1=1 $Query" . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayBuyerData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayBuyerList['SNO'] = ++$i;
            $ArrayBuyerList['ChemBuyerID'] = $dataRow['ChemBuyerID'];
            $ArrayBuyerList['BuyerName'] = $dataRow['BuyerName'];
            $ArrayBuyerList['BuyerAddress'] = $dataRow['BuyerAddress'];
            $ArrayBuyerList['BuyerPhoneNumber'] = $dataRow['BuyerPhoneNumber'];
            $ArrayBuyerList['BuyerCountry'] = $dataRow['BuyerCountry'];
            $ArrayBuyerList['CountryName'] = $dataRow['name'];
            $ArrayBuyerList['EmailAddress'] = $dataRow['EmailAddress'];
            $ArrayBuyerList['Status'] = $dataRow['Status'];
            $ArrayBuyerList['PostedDateTime'] = $dataRow['PostedDateTime'];
            $ArrayBuyerList['PostedUserID'] = $dataRow['PostedUserID'];
            // $ArrayBuyerList['TotalAmountUSD'] = $dataRow['TotalAmountUSD'];

            $ArrayBuyerData[] =  $ArrayBuyerList;
        }

        if (count($ArrayBuyerData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "GsChemBuyerCreate.php?ChemBuyerID=";
        $data['Data'] = $ArrayBuyerData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
