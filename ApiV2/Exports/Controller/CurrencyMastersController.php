<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class CurrencyMastersController
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

    public function postCurrencyDetails($RequestBody)
    {
        $CurrencyID = $RequestBody['CurrencyID'];

        if ($CurrencyID == '') {
            $Data = $this->insertCurrencyDetails($RequestBody);
        } else {
            $Data = $this->updateCurrencyDetails($RequestBody);
        }

        return $Data;
    }

    public function insertCurrencyDetails($RequestBody)
    {
        $CurrencyID = $RequestBody['CurrencyID'];
        $CurrencyName = $RequestBody['CurrencyName'];
        $CurrencyCode = $RequestBody['CurrencyCode'];
        $CurrenySymbol = $RequestBody['CurrenySymbol'];
        $CurrencyShortCode = $RequestBody['CurrencyShortCode'];
        $Status = $RequestBody['Status'];

    
        $sqlInsertCheck = "SELECT * FROM ScrmCurrencyMaster WHERE (CurrencyName = '$CurrencyName' OR CurrencyCode = '$CurrencyCode' OR 
        CurrenySymbol = '$CurrenySymbol') ";
        $stmtCheckInsert = $this->conn->prepare($sqlInsertCheck);
        $stmtCheckInsert->execute();
        $rowCountInsert = $stmtCheckInsert->rowCount();
        if($rowCountInsert == 0){

        

        $sqlInsert = "INSERT INTO ScrmCurrencyMaster(CurrencyID, CurrencyName, CurrencyCode, CurrenySymbol, CurrencyShortCode, Status) 
        VALUES ('$CurrencyID','$CurrencyName','$CurrencyCode','$CurrenySymbol','$CurrencyShortCode', '$Status')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['CurrencyID'] = $lastInsertedId;
            $createdCurrency = $this->auditLogger->fetchRowById('ScrmCurrencyMaster', 'CurrencyID', $lastInsertedId);
            $this->auditLogger->logInsert('ScrmCurrencyMaster', $lastInsertedId, $createdCurrency, $RequestBody, [
                'ReferenceNo' => $CurrencyCode,
                'Remarks' => 'Export currency created',
            ]);

            $CurrencyData = $this->getCurrencyDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Currency Posted Successfully';
            $Data['Data'] = $CurrencyData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Currency';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    } else{
        $Data['Error'] = 'Currency with the same name, code, or symbol already exists';
            $Data['Msg'] = 'Currency with the same name, code, or symbol already exists';
            return $Data;

    }
    }

    public function updateCurrencyDetails($RequestBody)
    {
        $CurrencyID = $RequestBody['CurrencyID'];
        $CurrencyName = $RequestBody['CurrencyName'];
        $CurrencyCode = $RequestBody['CurrencyCode'];
        $CurrenySymbol = $RequestBody['CurrenySymbol'];
        $CurrencyShortCode = $RequestBody['CurrencyShortCode'];
        $Status = $RequestBody['Status'];
        $existingCurrency = $this->auditLogger->fetchRowById('ScrmCurrencyMaster', 'CurrencyID', $CurrencyID);


        $sqlCheckUpdate = "SELECT * FROM ScrmCurrencyMaster WHERE (CurrencyName = '$CurrencyName' OR CurrencyCode = '$CurrencyCode' OR 
        CurrenySymbol = '$CurrenySymbol') AND CurrencyID != '$CurrencyID'";
        $stmtCheckUpdate = $this->conn->prepare($sqlCheckUpdate);
        $stmtCheckUpdate->execute();
        $rowCountUpdate = $stmtCheckUpdate->rowCount();

        if ($rowCountUpdate == 0) {
            $sqlUpdate = "UPDATE ScrmCurrencyMaster SET CurrencyName='$CurrencyName', CurrencyCode='$CurrencyCode',
            CurrenySymbol='$CurrenySymbol', CurrencyShortCode='$CurrencyShortCode', Status='$Status'
            WHERE CurrencyID='$CurrencyID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $updatedCurrency = $this->auditLogger->fetchRowById('ScrmCurrencyMaster', 'CurrencyID', $CurrencyID);
                $this->auditLogger->logUpdate('ScrmCurrencyMaster', $CurrencyID, $existingCurrency, $updatedCurrency, $RequestBody, [
                    'ReferenceNo' => $CurrencyCode,
                    'Remarks' => 'Export currency updated',
                ]);
                $CurrencyData = $this->getCurrencyDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Currency Updated Successfully';
                $Data['Data'] = $CurrencyData['Data'];
                return $Data;
            } else {
                $Data['Error'] = 'Unable to update Currency';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $Data['Error'] = 'Currency with the same name, code, or symbol already exists';
            $Data['Msg'] = 'Currency with the same name, code, or symbol already exists';
            return $Data;
        }
    }

    
 
    function getCurrencyDetails($RequestBody)
    {

        $RecordsPerPage = 10;
        $CurrencyID = $RequestBody['CurrencyID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', CurrencyName,CurrencyID,CurrencyCode,CurrenySymbol,CurrencyShortCode) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `ScrmCurrencyMaster`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "CurrencyID, CurrencyName, CurrencyCode, CurrenySymbol, CurrencyShortCode, Status";

        if ($CurrencyID != "") {
            $sqlQuery = "SELECT $sqlFields FROM ScrmCurrencyMaster WHERE CurrencyID='$CurrencyID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM ScrmCurrencyMaster 
        WHERE 1=1 $Query ORDER BY CurrencyID " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayCurrencyData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayCurrencyList['SNO'] = ++$i;
            $ArrayCurrencyList['CurrencyID'] = $dataRow['CurrencyID'];
            $ArrayCurrencyList['CurrencyName'] = $dataRow['CurrencyName'];
            $ArrayCurrencyList['CurrencyCode'] = $dataRow['CurrencyCode'];
            $ArrayCurrencyList['CurrenySymbol'] = $dataRow['CurrenySymbol'];
            $ArrayCurrencyList['CurrencyShortCode'] = $dataRow['CurrencyShortCode'];
            $ArrayCurrencyList['Status'] = $dataRow['Status'];

            $ArrayCurrencyData[] =  $ArrayCurrencyList;
        }
        if (count($ArrayCurrencyData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['EditLink'] = "currency-create.php?CurrencyID=";
        $data['Data'] = $ArrayCurrencyData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
