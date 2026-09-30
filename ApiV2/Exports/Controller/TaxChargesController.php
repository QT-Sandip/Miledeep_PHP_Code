<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class TaxChargesController
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

    public function postTaxChargesDetails($RequestBody)
    {

        $ExpTRID = $RequestBody['ExpTRID'];
        $ExpID = $RequestBody['ExpID'];
        $CountryID = $RequestBody['CountryID'];
        $AntiDumpingDuty = $RequestBody['AntiDumpingDuty'];
        $ClearanceCharge = $RequestBody['ClearanceChargePercentage'];
        $MerchandiseProcessFee = $RequestBody['MerchandiseProcessFee'];
        $HarbourMaintenanceFee = $RequestBody['HarbourMaintenanceFee'];
        $CountervailingDuty = $RequestBody['CountervailingDuty'];
        $ServiceTaxPercentage = $RequestBody['ServiceTaxPercentage'];
        $DrawbackRateperKG = $RequestBody['DrawbackRateperKG'];
        $DrawbackPercentage = $RequestBody['DrawbackPercentage'];
        $BLServiceTax = $RequestBody['BLServiceTax'];
        $ISFStaticDuty = $RequestBody['ISFStaticDuty'];
        $InsurancePremiumRate = $RequestBody['InsurancePremiumRate'];
        $UsClearance = $RequestBody['UsClearance'];
        $IGSTRaw = $RequestBody['IGSTRaw'];
        $IGSTBrainFrozen = $RequestBody['IGSTBrainFrozen'];
        $IGSTCooked = $RequestBody['IGSTCooked'];
        $IGSTCookedInShell = $RequestBody['IGSTCookedInShell'];
        $IGSTBlanched = $RequestBody['IGSTBlanched'];
        $FromDate = $RequestBody['FromDate'];
        $UptoDate = $RequestBody['UptoDate'];
        $UptoDate = $RequestBody['ClearanceCharge'];

        $ReciprocalTariffs = $RequestBody['ReciprocalTariffs'];
        $ClearingCharges = $RequestBody['ClearingCharges'];
        $OtherTax = $RequestBody['OtherTax'];
        $Tariff = $RequestBody['Tariff'];
        // $PrintTariff = $RequestBody['PrintTariff'];
        // $PrintReciprocalTariff = $RequestBody['PrintReciprocalTariff'];

        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");


        $SqlCount = "SELECT count(*) as Count,ExpTRID FROM `ScrmExporterTaxRates` WHERE ExpID='$ExpID' and FromDate='$FromDate'";
        $stmtCount = $this->conn->prepare($SqlCount);
        $stmtCount->execute();
        $dataRowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $RecordCount = $dataRowCount['Count'];

        if ($RecordCount == 0 && $ExpTRID == '') {
            // if ($ExpTRID == 0) {

            $sqlInsert = "INSERT INTO ScrmExporterTaxRates(ExpID, CountryID,ClearanceCharge, AntiDumpingDuty, MerchandiseProcessFee, HarbourMaintenanceFee, 
            CountervailingDuty, ServiceTaxPercentage, DrawbackRateperKG, DrawbackPercentage, BLServiceTax, ISFStaticDuty, InsurancePremiumRate, 
            UsClearance, IGSTRaw, IGSTBrainFrozen, IGSTCooked, IGSTCookedInShell, IGSTBlanched,ReciprocalTariffs,ClearingCharges,OtherTax,Tariff,FromDate, UptoDate, PostedDate)
            VALUES ('$ExpID','$CountryID','$ClearanceCharge','$AntiDumpingDuty','$MerchandiseProcessFee','$HarbourMaintenanceFee','$CountervailingDuty',
            '$ServiceTaxPercentage','$DrawbackRateperKG','$DrawbackPercentage','$BLServiceTax','$ISFStaticDuty','$InsurancePremiumRate',
            '$UsClearance','$IGSTRaw','$IGSTBrainFrozen','$IGSTCooked','$IGSTCookedInShell','$IGSTBlanched',' $ReciprocalTariffs', '$ClearingCharges','$OtherTax','$Tariff', '$FromDate','$UptoDate', '$PostedDate')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['ExpTRID'] = $lastInsertedId;
                $createdTax = $this->auditLogger->fetchRowById('ScrmExporterTaxRates', 'ExpTRID', $lastInsertedId);
                $this->auditLogger->logInsert('ScrmExporterTaxRates', $lastInsertedId, $createdTax, $RequestBody, [
                    'ReferenceNo' => $ExpID,
                    'Remarks' => 'Export tax charge created',
                ]);
                $this->Error = null;
                $this->Msg = 'Tax Charges Posted Successfully';
            }
        } else {

            // $SqlFromDate = "SELECT FromDate FROM `ScrmExporterTaxRates` WHERE ExpTRID='$ExpTRID' ";
            // $stmtFromDate = $this->conn->prepare($SqlFromDate);
            // $stmtFromDate->execute();
            // $dataRowFromDate = $stmtFromDate->fetch(PDO::FETCH_ASSOC);
            // $RecordCount = $dataRowFromDate['Count'];
            $NewExpTRID = $dataRowCount['ExpTRID'];

            // echo $NewExpTRID; exit;
            if ($NewExpTRID == $ExpTRID || is_null($NewExpTRID)) {
                $existingTax = $this->auditLogger->fetchRowById('ScrmExporterTaxRates', 'ExpTRID', $ExpTRID);
                $sqlUpdate = "UPDATE ScrmExporterTaxRates SET ExpID = '$ExpID', CountryID = '$CountryID',ClearanceCharge = '$ClearanceCharge', AntiDumpingDuty = '$AntiDumpingDuty', 
            MerchandiseProcessFee = '$MerchandiseProcessFee', HarbourMaintenanceFee = '$HarbourMaintenanceFee', CountervailingDuty = '$CountervailingDuty', 
            ServiceTaxPercentage = '$ServiceTaxPercentage', DrawbackRateperKG = '$DrawbackRateperKG', DrawbackPercentage = '$DrawbackPercentage', 
            BLServiceTax = '$BLServiceTax', ISFStaticDuty = '$ISFStaticDuty', InsurancePremiumRate = '$InsurancePremiumRate', UsClearance = '$UsClearance', 
            IGSTRaw = '$IGSTRaw', IGSTBrainFrozen = '$IGSTBrainFrozen', IGSTCooked = '$IGSTCooked', IGSTCookedInShell = '$IGSTCookedInShell', 
            IGSTBlanched = '$IGSTBlanched', FromDate = '$FromDate', UptoDate = '$UptoDate', PostedDate = '$PostedDate',ReciprocalTariffs='$ReciprocalTariffs',
            ClearingCharges='$ClearingCharges',OtherTax='$OtherTax',Tariff='$Tariff'
            WHERE ExpTRID = '$ExpTRID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $updatedTax = $this->auditLogger->fetchRowById('ScrmExporterTaxRates', 'ExpTRID', $ExpTRID);
                    $this->auditLogger->logUpdate('ScrmExporterTaxRates', $ExpTRID, $existingTax, $updatedTax, $RequestBody, [
                        'ReferenceNo' => $ExpID,
                        'Remarks' => 'Export tax charge updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Tax Charges Updated Successfully';
                }
            } else {
                $this->Error = 'Duplicate Entry';
                $this->Msg = 'Duplicate Entry';
            }
        }

        $getData = $this->getTaxChargesDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];
        return $Data;
        // }

        // $getData = $this->getTaxChargesDetails($RequestBody);
        // $Data['Error'] = 'Duplicate Entry';
        // $Data['Msg'] = 'Duplicate Entry';
        // $Data['Data'] = $getData['Data'];

        // return $Data;
    }


    function getTaxChargesDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ExpTRID = $RequestBody['ExpTRID'];
        $Exporter = $RequestBody['Exporter'];
        $FromDate = $RequestBody['FromDate'];
        $Country = $RequestBody['Country'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $Type = $RequestBody['Type']; //uma

        $Query = '';

        if ($Exporter != "") {
            $Query .= " and ScETR.ExpID = '$Exporter'";
        }
        if ($Country != "") {
            $Query .= " and ScETR.CountryID = '$Country'";
        }
        if ($FromDate != "") {
            $Query .= " and ScETR.FromDate = '$FromDate'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ExpTRID, ScETR.ExpID, CountryID, ClearanceCharge,AntiDumpingDuty, MerchandiseProcessFee, HarbourMaintenanceFee, 
            CountervailingDuty, ServiceTaxPercentage, DrawbackRateperKG, DrawbackPercentage, BLServiceTax,
            ISFStaticDuty, InsurancePremiumRate, UsClearance, IGSTRaw, IGSTBrainFrozen, IGSTCooked, IGSTCookedInShell, 
            IGSTBlanched, FromDate, UptoDate, PostedDate,ExpName, ScCL.name) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM ScrmExporterTaxRates AS ScETR
        LEFT JOIN AdminExporterDetails AS AED ON ScETR.ExpID=AED.ExpID
        LEFT JOIN ScrmCountriesList AS ScCL ON ScETR.CountryID=ScCL.id
        WHERE 1=1 $Query ";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "ExpTRID, ScETR.ExpID, CountryID, ClearanceCharge,AntiDumpingDuty, MerchandiseProcessFee, HarbourMaintenanceFee, 
        CountervailingDuty,ReciprocalTariffs,ClearingCharges,OtherTax,Tariff, ServiceTaxPercentage, DrawbackRateperKG, DrawbackPercentage, BLServiceTax,
        ISFStaticDuty, InsurancePremiumRate, UsClearance, IGSTRaw, IGSTBrainFrozen, IGSTCooked, IGSTCookedInShell, 
        IGSTBlanched, FromDate, UptoDate, PostedDate,ExpName, GROUP_CONCAT(ScCL.name) AS CountryNames";

        if ($Type == 'List') {
            $sqlQuery = "SELECT $sqlFields FROM ScrmExporterTaxRates AS ScETR
            LEFT JOIN AdminExporterDetails AS AED ON ScETR.ExpID=AED.ExpID
            LEFT JOIN ScrmCountriesList AS ScCL ON FIND_IN_SET(ScCL.id, ScETR.CountryID)
            WHERE 1=1 $Query GROUP BY ExpTRID ORDER BY ExpTRID, FromDate  ASC " . $Limit;
        } else {
            if ($ExpTRID != '') {
                $sqlQuery = "SELECT $sqlFields FROM ScrmExporterTaxRates AS ScETR
                LEFT JOIN AdminExporterDetails AS AED ON ScETR.ExpID=AED.ExpID
                LEFT JOIN ScrmCountriesList AS ScCL ON ScETR.CountryID=ScCL.id
                WHERE 1=1 AND ExpTRID='$ExpTRID' GROUP BY ExpTRID  ORDER BY ExpTRID, FromDate ASC ";
            } else {
                // $sqlQuery = "SELECT $sqlFields FROM ScrmExporterTaxRates AS ScETR
                // LEFT JOIN AdminExporterDetails AS AED ON ScETR.ExpID=AED.ExpID
                // LEFT JOIN ScrmCountriesList AS ScCL ON FIND_IN_SET(ScCL.id, ScETR.CountryID)
                // WHERE 1=1 $Query GROUP BY ExpTRID ORDER BY ExpTRID, FromDate  ASC " . $Limit;

                $sqlQuery = "SELECT $sqlFields FROM ScrmExporterTaxRates AS ScETR
                LEFT JOIN AdminExporterDetails AS AED ON ScETR.ExpID=AED.ExpID
                LEFT JOIN ScrmCountriesList AS ScCL ON FIND_IN_SET(ScCL.id, ScETR.CountryID)
                WHERE 1=1 $Query GROUP BY ExpTRID order by ExpTRID desc LIMIT 1";
            }
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayTaxChargesData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayTaxChargesList['SNO'] = ++$i;
            $ArrayTaxChargesList['ExpTRID'] = $dataRow['ExpTRID'];
            $ArrayTaxChargesList['ExpID'] = $dataRow['ExpID'];
            $ArrayTaxChargesList['ExpName'] = $dataRow['ExpName'];
            $ArrayTaxChargesList['CountryID'] = $dataRow['CountryID'];
            $ArrayTaxChargesList['CountryNames'] = $dataRow['CountryNames'];
            $ArrayTaxChargesList['AntiDumpingDuty'] = $dataRow['AntiDumpingDuty'];
            $ArrayTaxChargesList['ClearanceChargeFee'] = $dataRow['ClearanceCharge'];
            $ArrayTaxChargesList['MerchandiseProcessFee'] = $dataRow['MerchandiseProcessFee'];
            $ArrayTaxChargesList['HarbourMaintenanceFee'] = $dataRow['HarbourMaintenanceFee'];
            $ArrayTaxChargesList['CountervailingDuty'] = $dataRow['CountervailingDuty'];
            $ArrayTaxChargesList['ReciprocalTariffs'] = $dataRow['ReciprocalTariffs'];
            $ArrayTaxChargesList['ClearingCharges'] = $dataRow['ClearingCharges'];
            $ArrayTaxChargesList['OtherTax'] = $dataRow['OtherTax'];
            $ArrayTaxChargesList['Tariff'] = $dataRow['Tariff'];
            // $ArrayTaxChargesList['PrintReciprocalTariff'] = $dataRow['PrintReciprocalTariff'];
            // $ArrayTaxChargesList['PrintTariff'] = $dataRow['PrintTariff'];

            $ArrayTaxChargesList['ServiceTaxPercentage'] = $dataRow['ServiceTaxPercentage'];
            $ArrayTaxChargesList['DrawbackRateperKG'] = $dataRow['DrawbackRateperKG'];
            $ArrayTaxChargesList['DrawbackPercentage'] = $dataRow['DrawbackPercentage'];
            $ArrayTaxChargesList['BLServiceTax'] = $dataRow['BLServiceTax'];
            $ArrayTaxChargesList['ISFStaticDuty'] = $dataRow['ISFStaticDuty'];
            $ArrayTaxChargesList['InsurancePremiumRate'] = $dataRow['InsurancePremiumRate'];
            $ArrayTaxChargesList['UsClearance'] = $dataRow['UsClearance'];
            $ArrayTaxChargesList['IGSTRaw'] = $dataRow['IGSTRaw'];
            $ArrayTaxChargesList['IGSTBrainFrozen'] = $dataRow['IGSTBrainFrozen'];
            $ArrayTaxChargesList['IGSTCooked'] = $dataRow['IGSTCooked'];
            $ArrayTaxChargesList['IGSTCookedInShell'] = $dataRow['IGSTCookedInShell'];
            $ArrayTaxChargesList['IGSTBlanched'] = $dataRow['IGSTBlanched'];
            $ArrayTaxChargesList['FromDate'] = $dataRow['FromDate'];
            $ArrayTaxChargesList['UptoDate'] = $dataRow['UptoDate'];
            $ArrayTaxChargesList['PostedDate'] = $dataRow['PostedDate'];
            $ArrayTaxChargesList['EditLink'] = "create-tax-charges.php?ExpTRID=" . $dataRow['ExpTRID'];

            $ArrayTaxChargesData[] = $ArrayTaxChargesList;
        }

        if (count($ArrayTaxChargesData) < $RecordsPerPage) {
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
        $data['Data'] = $ArrayTaxChargesData;

        return $data;
    }
}
