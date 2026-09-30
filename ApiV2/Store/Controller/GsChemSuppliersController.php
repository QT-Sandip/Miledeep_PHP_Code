<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GsChemSuppliersController
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

    public function postSupplierDetails($RequestBody)
    {
        $ChemSupplierID = $RequestBody['ChemSupplierID'];

        if ($ChemSupplierID == '') {
            $Data = $this->insertSupplierDetails($RequestBody);
        } else {
            $Data = $this->updateSupplierDetails($RequestBody);
        }

        return $Data;
    }

    public function insertSupplierDetails($RequestBody)
    {
        $ChemSupplierID = $RequestBody['ChemSupplierID'];
        $SupplierName = $RequestBody['SupplierName'];
        $SupplierPhoneNumber = $RequestBody['SupplierPhoneNumber'];
        $SupplierAddress = $RequestBody['SupplierAddress'];
        $SupplierCountry = $RequestBody['SupplierCountry'];
        $SupplierEmailAddress = $RequestBody['SupplierEmailAddress'];
        $Status = $RequestBody['Status'];
        // $PostedDateTime = $RequestBody['PostedDateTime'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $PostedUserID = $RequestBody['PostedUserID'];
        $sqlInsert = "INSERT INTO GsChemSuppliersData(ChemSupplierID, SupplierName, SupplierPhoneNumber, SupplierAddress, 
        SupplierCountry, SupplierEmailAddress, Status, PostedDateTime, PostedUserID) 
        VALUES ('$ChemSupplierID','$SupplierName','$SupplierPhoneNumber','$SupplierAddress', '$SupplierCountry', 
        '$SupplierEmailAddress', '$Status', '$PostedDate', '$PostedUserID')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        // $errorInfo = $stmtInsert->errorInfo();
        // var_dump($errorInfo);

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['ChemSupplierID'] = $lastInsertedId;
            $createdSupplier = $this->auditLogger->fetchRowById('GsChemSuppliersData', 'ChemSupplierID', $lastInsertedId);
            $this->auditLogger->logInsert('GsChemSuppliersData', $lastInsertedId, $createdSupplier, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'Store chemical supplier created',
            ]);

            $supplierData = $this->getSupplierDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Supplier Posted Successfully';
            $Data['Data'] = $supplierData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Supplier';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateSupplierDetails($RequestBody)
    {
        $ChemSupplierID = $RequestBody['ChemSupplierID'];
        $SupplierName = $RequestBody['SupplierName'];
        $SupplierPhoneNumber = $RequestBody['SupplierPhoneNumber'];
        $SupplierAddress = $RequestBody['SupplierAddress'];
        $SupplierCountry = $RequestBody['SupplierCountry'];
        $SupplierEmailAddress = $RequestBody['SupplierEmailAddress'];
        $Status = $RequestBody['Status'];
        // $PostedDateTime = $RequestBody['PostedDateTime'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $PostedUserID = $RequestBody['PostedUserID'];
        $existingSupplier = $this->auditLogger->fetchRowById('GsChemSuppliersData', 'ChemSupplierID', $ChemSupplierID);
        // echo
        $sqlUpdate = "UPDATE GsChemSuppliersData SET SupplierName='$SupplierName',SupplierPhoneNumber='$SupplierPhoneNumber',
        SupplierAddress='$SupplierAddress', SupplierCountry='$SupplierCountry',SupplierEmailAddress='$SupplierEmailAddress',
        Status='$Status',PostedDateTime='$PostedDate',PostedUserID='$PostedUserID' 
        WHERE ChemSupplierID='$ChemSupplierID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $updatedSupplier = $this->auditLogger->fetchRowById('GsChemSuppliersData', 'ChemSupplierID', $ChemSupplierID);
            $this->auditLogger->logUpdate('GsChemSuppliersData', $ChemSupplierID, $existingSupplier, $updatedSupplier, $RequestBody, [
                'ReferenceNo' => $ChemSupplierID,
                'Remarks' => 'Store chemical supplier updated',
            ]);
            $supplierData = $this->getSupplierDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Suppliers Updated Successfully';
            $Data['Data'] = $supplierData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Suppliers';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getSupplierDetails($RequestBody)
    {
        $RecordsPerPage = 10;
        $ChemSupplierID = $RequestBody['ChemSupplierID'];
        $SupplierCountry = $RequestBody['SupplierCountry'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($ChemSupplierID != "") {
            $Query .= " and GCSD.ChemSupplierID = '$ChemSupplierID'";
        }

        if ($SupplierCountry != "") {
            $Query .= " and GCSD.SupplierCountry = '$SupplierCountry'";
        }

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ChemSupplierID, SupplierName, SupplierAddress, SupplierPhoneNumber, SupplierCountry, SupplierEmailAddress) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `GsChemSuppliersData`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "GCSD.ChemSupplierID, GCSD.SupplierName, GCSD.SupplierAddress, GCSD.SupplierPhoneNumber, GCSD.SupplierCountry, 
        SCL.name, GCSD.SupplierEmailAddress, GCSD.Status, GCSD.PostedDateTime, GCSD.PostedUserID";

        // SELECT GCSD.ChemSupplierID, GCSD.SupplierName, GCSD.SupplierAddress, GCSD.SupplierPhoneNumber, GCSD.SupplierCountry, GCSD.SupplierEmailAddress, GCSD.Status, 
        // GCSD.PostedDateTime, GCSD.PostedUserID FROM GsChemSuppliersData

        // SELECT id, iso, name, phonecode, Symbol, SymbolNative, CountryType, ShowFrightCharge, 
        // ShowBrand, showDelivery, GstCountryCode FROM ScrmCountriesList WHERE 1

        if ($ChemSupplierID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsChemSuppliersData AS GCSD
            LEFT JOIN ScrmCountriesList AS SCL ON GCSD.SupplierCountry = SCL.id
            WHERE GCSD.ChemSupplierID='$ChemSupplierID'
            GROUP BY GCSD.ChemSupplierID";
        } else {

            $sqlQuery = "SELECT $sqlFields FROM GsChemSuppliersData AS GCSD
            LEFT JOIN ScrmCountriesList AS SCL ON GCSD.SupplierCountry = SCL.id
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
            $ArrayBuyerList['ChemSupplierID'] = $dataRow['ChemSupplierID'];
            $ArrayBuyerList['SupplierName'] = $dataRow['SupplierName'];
            $ArrayBuyerList['SupplierAddress'] = $dataRow['SupplierAddress'];
            $ArrayBuyerList['SupplierPhoneNumber'] = $dataRow['SupplierPhoneNumber'];
            $ArrayBuyerList['SupplierCountry'] = $dataRow['SupplierCountry'];
            $ArrayBuyerList['CountryName'] = $dataRow['name'];
            $ArrayBuyerList['SupplierEmailAddress'] = $dataRow['SupplierEmailAddress'];
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
        $data['EditLink'] = "GsChemSupplierCreate.php?ChemSupplierID=";
        $data['Data'] = $ArrayBuyerData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
