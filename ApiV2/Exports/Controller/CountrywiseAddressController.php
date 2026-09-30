<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

session_start();

class CountrywiseAddressController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postCountryDetails($RequestBody)
    {

        $ID = $RequestBody['ID'];

        if ($ID == '') {
            $Data = $this->insertCountryDetails($RequestBody);
        } else {
            $Data = $this->updateCountryDetails($RequestBody);
        }

        return $Data;
    }

    public function insertCountryDetails($RequestBody)
    {
        // $ID = $RequestBody['ID'];
        $ExporterID = $RequestBody['ExporterID'];
        $ExporterName = $RequestBody['ExporterName'];
        $ExporterAddress = $RequestBody['ExporterAddress'];
        $CountryID = $RequestBody['CountryID'];
        $DocType = $RequestBody['DocType'];

        $sql = "SELECT * FROM ScrmCountrywiseAddresses WHERE ExporterID='$ExporterID' and ExporterName='$ExporterName' 
        and CountryID='$CountryID' and DocType='$DocType'";

        $stmtsql = $this->conn->prepare($sql);
        $stmtsql->execute();
        $rowCount = $stmtsql->rowCount();

        if ($rowCount == 0) {

            $sqlInsert = "INSERT INTO ScrmCountrywiseAddresses(ExporterID, ExporterName, ExporterAddress, CountryID, DocType) 
            VALUES ('$ExporterID','$ExporterName','$ExporterAddress','$CountryID','$DocType')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['ID'] = $lastInsertedId;
                $createdAddress = $this->auditLogger->fetchRowById('ScrmCountrywiseAddresses', 'ID', $lastInsertedId);
                $this->auditLogger->logInsert('ScrmCountrywiseAddresses', $lastInsertedId, $createdAddress, $RequestBody, [
                    'ReferenceNo' => $ExporterName,
                    'Remarks' => 'Export countrywise address created',
                ]);

                $CountryData = $this->getCountryDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Data Posted Successfully';
                $Data['Data'] = $CountryData['Data'];
                return $Data;
            } else {

                $Data['Error'] = 'Unable to Insert Data';
                $Data['Msg'] = $this->Error;
                $Data['Data'] = null;
                return $Data;
            }
        } else {
            $Data['Error'] = 'Duplicate Entry';
            $Data['Msg'] = 'Duplicate Entry';
            $Data['Data'] = null;
            return $Data;
        }
    }

    public function updateCountryDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];
        $ExporterID = $RequestBody['ExporterID'];
        $ExporterName = $RequestBody['ExporterName'];
        $ExporterAddress = $RequestBody['ExporterAddress'];
        $CountryID = $RequestBody['CountryID'];
        $DocType = $RequestBody['DocType'];
        $existingAddress = $this->auditLogger->fetchRowById('ScrmCountrywiseAddresses', 'ID', $ID);

        $sqlUpdateCheck = "SELECT * FROM ScrmCountrywiseAddresses WHERE ExporterID='$ExporterID' AND ExporterName='$ExporterName' AND 
         CountryID='$CountryID' AND DocType='$DocType' AND ID != '$ID'";
        // "SELECT * FROM ScrmCountrywiseAddresses WHERE ExporterID='$ExporterID' AND ExporterName='$ExporterName' AND 
        // CountryID='$CountryID' AND DocType='$DocType' AND ID != '$ID'";

        $stmtsql = $this->conn->prepare($sqlUpdateCheck);
        $stmtsql->execute();
        $rowCount = $stmtsql->rowCount();

        if ($rowCount == 0) {

            $sqlUpdate = "UPDATE ScrmCountrywiseAddresses SET ExporterID='$ExporterID',ExporterName='$ExporterName',
            ExporterAddress='$ExporterAddress',CountryID='$CountryID',DocType='$DocType'
            WHERE ID='$ID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            // $errorInfo = $stmtUpdate->errorInfo();
            // var_dump($errorInfo);

            if ($stmtUpdate) {
                $updatedAddress = $this->auditLogger->fetchRowById('ScrmCountrywiseAddresses', 'ID', $ID);
                $this->auditLogger->logUpdate('ScrmCountrywiseAddresses', $ID, $existingAddress, $updatedAddress, $RequestBody, [
                    'ReferenceNo' => $ExporterName,
                    'Remarks' => 'Export countrywise address updated',
                ]);
                $CountryData = $this->getCountryDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Data Updated Successfully';
                $Data['Data'] = $CountryData['Data'];
                return $Data;
            } else {
                $Data['Error'] = 'Unable to Insert Data';
                $Data['Msg'] = $this->Error;
                $Data['Data'] = null;
                return $Data;
            }
        } else {
            $Data['Error'] = 'Duplicate Entry';
            $Data['Msg'] = 'Details Already exist';
            $Data['Data'] = null;
            return $Data;
        }
    }


    function deleteCountryDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];
        $existingAddress = $this->auditLogger->fetchRowById('ScrmCountrywiseAddresses', 'ID', $ID);

        $sqlDelete = "DELETE FROM `ScrmCountrywiseAddresses` WHERE ID='$ID'";

        $stmtUpdate = $this->conn->prepare($sqlDelete);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $this->auditLogger->logDelete('ScrmCountrywiseAddresses', $ID, $existingAddress, $RequestBody, [
                'ReferenceNo' => isset($existingAddress['ExporterName']) ? $existingAddress['ExporterName'] : null,
                'Remarks' => 'Export countrywise address deleted',
            ]);
            $CountryData = $this->getCountryDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Data Deleted Successfully';
            $Data['Data'] = $CountryData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete Data';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getCountryDetails($RequestBody)
    {

        $RecordsPerPage = 100;
        $ID = $RequestBody['ID'];
        $ExpProc = $RequestBody['ExpProc'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';


        if ($ID != "") {
            $Query .= " and ScrmCountrywiseAddresses.ID = '$ID'";
        }

        if ($ExpProc != "") {
            $Query .= " and SCA.ExporterID = '$ExpProc'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ExporterName, ExporterAddress, DocType, DocTypeName, CountryName, ExpName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(ID) as Total FROM `ScrmCountrywiseAddresses` AS SCA
        LEFT JOIN ScrmCountriesList AS SCL ON SCA.CountryID=SCL.ID
        LEFT JOIN AdminExporterDetails AS AED ON SCA.ExporterID=AED.ExpID
        WHERE 1=1 $Query ";

        $stmt = $this->conn->prepare($sqlCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        // SELECT SCAD.ID,name, ExporterID, ExporterName, ExporterAddress, CountryID, DocType,ExpType,ExpApprovalNo 
        // FROM ScrmCountrywiseAddresses as SCAD left join ScrmCountriesList as SCL on SCL.id=SCAD.CountryID left join 
        // AdminExporterDetails as AED on AED.ExpID=SCAD.ExporterID;
        // SELECT ExpID,ExpName,ExpApprovalNo,ExpType, 
        // CASE ExpType WHEN 1 THEN 'Exporter' WHEN 2 THEN 'Processor' WHEN 3 THEN 'Both' END AS ExpTypeName,
        // CONCAT(ExpName, ' - ', CASE ExpType WHEN 1 THEN 'Exporter' WHEN 2 THEN 'Processor' WHEN 3 THEN 'Both' END, ' (', ExpApprovalNo, ')') AS ExporterType FROM AdminExporterDetails WHERE Status=1";


        $sqlFields = "SCA.ID, SCA.ExporterID, SCA.ExporterName, SCA.ExporterAddress, SCA.CountryID, SCA.DocType,
        CASE SCA.DocType WHEN 'PI' THEN 'Proforma Invoice' WHEN 'CUI' THEN 'Customs Invoice' WHEN 'CMI' THEN 'Commercial Invoice' 
        WHEN 'PKL' THEN 'Packing List' WHEN 'BLI' THEN 'Batch List' WHEN 'GLI' THEN 'Glaze Invoice' END AS DocTypeName,
        SCL.name AS CountryName, AED.ExpName, AED.ExpType, AED.ExpName, AED.ExpApprovalNo, 
        CASE AED.ExpType WHEN 1 THEN 'Exporter' WHEN 2 THEN 'Processor' WHEN 3 THEN 'Both' END AS ExpTypeName,
        CONCAT(AED.ExpName, ' - ', CASE AED.ExpType WHEN 1 THEN 'Exporter' WHEN 2 THEN 'Processor' WHEN 3 THEN 'Both' END, ' (', AED.ExpApprovalNo, ')') AS ExporterType";

        if ($ID != '') {

            $sqlQuery = "SELECT $sqlFields FROM ScrmCountrywiseAddresses AS SCA
            LEFT JOIN ScrmCountriesList AS SCL ON SCA.CountryID=SCL.ID
            LEFT JOIN AdminExporterDetails AS AED ON SCA.ExporterID=AED.ExpID
            WHERE SCA.ID='$ID'";
        } else {

            $sqlQuery = "SELECT $sqlFields FROM ScrmCountrywiseAddresses AS SCA
            LEFT JOIN ScrmCountriesList AS SCL ON SCA.CountryID=SCL.ID
            LEFT JOIN AdminExporterDetails AS AED ON SCA.ExporterID=AED.ExpID
            WHERE 1=1 $Query " . $Limit;
        }
    //   echo  $sqlQuery ; exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayCountryData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayCountryList['SNO'] = ++$i;
            $ArrayCountryList['ID'] = $dataRow['ID'];
            $ArrayCountryList['ExporterID'] = $dataRow['ExporterID'];
            $ArrayCountryList['ExpName'] = $dataRow['ExpName'];
            $ArrayCountryList['ExporterPackerName'] = $dataRow['ExporterName'];
            $ArrayCountryList['ExporterAddress'] = $dataRow['ExporterAddress'];
            $ArrayCountryList['CountryID'] = $dataRow['CountryID'];
            $ArrayCountryList['CountryName'] = $dataRow['CountryName'];
            $ArrayCountryList['DocType'] = $dataRow['DocType'];
            $ArrayCountryList['DocTypeName'] = $dataRow['DocTypeName'];
            $ArrayCountryList['ExpApprovalNo'] = $dataRow['ExpApprovalNo'];
            $ArrayCountryList['ExpTypeName'] = $dataRow['ExpTypeName'];
            $ArrayCountryList['ExporterType'] = $dataRow['ExporterType'];

            $ArrayCountryData[] =  $ArrayCountryList;
        }

        if (count($ArrayCountryData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "create-countrywise-addresses.php?ID=";
        $data['Data'] = $ArrayCountryData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
