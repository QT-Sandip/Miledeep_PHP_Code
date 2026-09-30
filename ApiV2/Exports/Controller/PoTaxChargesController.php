<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class PoTaxChargesController
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

    public function postPoTaxChargesDetails($RequestBody)
    {
        // $PoID = $RequestBody["PoIDs"];
        // $ReciprocalTariff = $RequestBody["ReciprocalTariff"];
        // $RevisedPo = $RequestBody["RevisedPo"];
        // $PoDate = $RequestBody["PoDate"];
        // $PoIDs = explode(",", $PoIDs);
        // $result = [];
        // foreach ($PoIDs as $key => $PoID) {
        //     $sql = "INSERT INTO `ReciprocalTariffs` (PoID, ReciprocalTariff, RevisedPo, PoDate)VALUES ($PoID, $ReciprocalTariff, $RevisedPo, '$PoDate')";
        //     $stmt = $this->conn->prepare($sql);

        //     if (!$stmt->execute()) {
        //         $result['Error'] = $sql;
        //         $result['Msg'] = "Failed to insert data for PO ID: " . $PoID;
        //         $result['Data'] = [];
        //         return $result;
        //     }
        // }
        // $ExpTRID = $RequestBody['ExpTRID'];
        // $ExpID = $RequestBody['ExpID'];
        // $CountryID = $RequestBody['CountryID'];
        // $AntiDumpingDuty = $RequestBody['AntiDumpingDuty'];
        // $ClearanceCharge = $RequestBody['ClearanceChargePercentage'];
        // $MerchandiseProcessFee = $RequestBody['MerchandiseProcessFee'];
        // $HarbourMaintenanceFee = $RequestBody['HarbourMaintenanceFee'];
        // $CountervailingDuty = $RequestBody['CountervailingDuty'];
        // $ServiceTaxPercentage = $RequestBody['ServiceTaxPercentage'];
        // $DrawbackRateperKG = $RequestBody['DrawbackRateperKG'];
        // $DrawbackPercentage = $RequestBody['DrawbackPercentage'];
        // $BLServiceTax = $RequestBody['BLServiceTax'];
        // $ISFStaticDuty = $RequestBody['ISFStaticDuty'];
        // $InsurancePremiumRate = $RequestBody['InsurancePremiumRate'];
        // $UsClearance = $RequestBody['UsClearance'];
        // $FromDate = $RequestBody['FromDate'];
        // $UptoDate = $RequestBody['UptoDate'];
        // $UptoDate = $RequestBody['ClearanceCharge'];

        // $ReciprocalTariffs = $RequestBody['ReciprocalTariffs'];
        // $ClearingCharges = $RequestBody['ClearingCharges'];
        // $OtherTax = $RequestBody['OtherTax'];

        $PoID = $RequestBody["PoID"];
        $InvoiceType = $RequestBody["InvoiceType"];
        $RevisedPo = $RequestBody["RevisedPo"];
        // $PoDate = $RequestBody["PoDate"];
        // $ExpID = $RequestBody["ExpID"];

        $ClearanceCharge = $RequestBody["ClearanceCharge"];
        $AntiDumpingDuty = $RequestBody["AntiDumpingDuty"];
        $MerchandiseProcessFee = $RequestBody["MerchandiseProcessFee"];
        $HarbourMaintenanceFee = $RequestBody["HarbourMaintenanceFee"];
        $CountervailingDuty = $RequestBody["CountervailingDuty"];
        $ServiceTaxPercentage = $RequestBody["ServiceTaxPercentage"];
        $DrawbackRateperKG = $RequestBody["DrawbackRateperKG"];
        $DrawbackPercentage = $RequestBody["DrawbackPercentage"];
        $BLServiceTax = $RequestBody["BLServiceTax"];
        $ISFStaticDuty = $RequestBody["ISFStaticDuty"];
        $InsurancePremiumRate = $RequestBody["InsurancePremiumRate"];
        $UsClearance = $RequestBody["UsClearance"];
        $RevisedReciprocalTariff = $RequestBody["RevisedReciprocalTariff"];
        $ClearingCharges = $RequestBody["ClearingCharges"];
        $OtherTax = $RequestBody["OtherTax"];
        $Tariff = $RequestBody["Tariff"];
        $PrintTariff = $RequestBody["PrintTariff"];
        $PrintReciprocalTariff = $RequestBody["PrintReciprocalTariff"];

        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");


        $SqlCount = "SELECT count(*) as Count, PoID FROM `ScrmPoTaxRates` WHERE PoID='$PoID' AND InvoiceType='$InvoiceType'";
        $stmtCount = $this->conn->prepare($SqlCount);
        $stmtCount->execute();
        $dataRowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $RecordCount = $dataRowCount['Count'];

        if ($RecordCount == 0) {

            $sqlInsert = "INSERT INTO ScrmPoTaxRates(
                PoID, InvoiceType, RevisedPo,
                ClearanceCharge, AntiDumpingDuty, MerchandiseProcessFee,
                HarbourMaintenanceFee, CountervailingDuty, ServiceTaxPercentage,
                DrawbackRateperKG, DrawbackPercentage, BLServiceTax,
                ISFStaticDuty, InsurancePremiumRate, UsClearance,
                ReciprocalTariff, ClearingCharges, OtherTax, Tariff, PrintTariff, PrintReciprocalTariff, PostedDate
            ) VALUES (
                '$PoID','$InvoiceType','$RevisedPo',
                '$ClearanceCharge','$AntiDumpingDuty','$MerchandiseProcessFee',
                '$HarbourMaintenanceFee','$CountervailingDuty','$ServiceTaxPercentage',
                '$DrawbackRateperKG','$DrawbackPercentage','$BLServiceTax',
                '$ISFStaticDuty','$InsurancePremiumRate','$UsClearance',
                '$RevisedReciprocalTariff','$ClearingCharges','$OtherTax','$Tariff','$PrintTariff','$PrintReciprocalTariff','$PostedDate'
            )";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $createdTax = $this->auditLogger->fetchRowsByConditions('ScrmPoTaxRates', ['PoID' => $PoID, 'InvoiceType' => $InvoiceType]);
                $createdTaxRow = count($createdTax) > 0 ? $createdTax[0] : null;
                $recordId = $createdTaxRow && isset($createdTaxRow['ID']) ? $createdTaxRow['ID'] : $PoID . '-' . $InvoiceType;
                $this->auditLogger->logInsert('ScrmPoTaxRates', $recordId, $createdTaxRow, $RequestBody, [
                    'ReferenceNo' => $PoID,
                    'Remarks' => 'Export PO tax charge created',
                ]);
                $this->Error = null;
                $this->Msg = 'Tax Charges Posted Successfully';
            }
        } else {
            $existingTax = $this->auditLogger->fetchRowsByConditions('ScrmPoTaxRates', ['PoID' => $PoID, 'InvoiceType' => $InvoiceType]);
            $existingTaxRow = count($existingTax) > 0 ? $existingTax[0] : null;
            $sqlUpdate = "UPDATE ScrmPoTaxRates SET
                    RevisedPo = '$RevisedPo',
                    ClearanceCharge = '$ClearanceCharge',
                    AntiDumpingDuty = '$AntiDumpingDuty',
                    MerchandiseProcessFee = '$MerchandiseProcessFee',
                    HarbourMaintenanceFee = '$HarbourMaintenanceFee',
                    CountervailingDuty = '$CountervailingDuty',
                    ServiceTaxPercentage = '$ServiceTaxPercentage',
                    DrawbackRateperKG = '$DrawbackRateperKG',
                    DrawbackPercentage = '$DrawbackPercentage',
                    BLServiceTax = '$BLServiceTax',
                    ISFStaticDuty = '$ISFStaticDuty',
                    InsurancePremiumRate = '$InsurancePremiumRate',
                    UsClearance = '$UsClearance',
                    ReciprocalTariff = '$RevisedReciprocalTariff',
                    ClearingCharges = '$ClearingCharges',
                    OtherTax = '$OtherTax',
                    Tariff = '$Tariff',
                    PrintTariff = '$PrintTariff',
                    PrintReciprocalTariff = '$PrintReciprocalTariff',
                    PostedDate = '$PostedDate'
                WHERE PoID = '$PoID'
                AND InvoiceType = '$InvoiceType'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $updatedTax = $this->auditLogger->fetchRowsByConditions('ScrmPoTaxRates', ['PoID' => $PoID, 'InvoiceType' => $InvoiceType]);
                $updatedTaxRow = count($updatedTax) > 0 ? $updatedTax[0] : null;
                $recordId = $updatedTaxRow && isset($updatedTaxRow['ID']) ? $updatedTaxRow['ID'] : $PoID . '-' . $InvoiceType;
                $this->auditLogger->logUpdate('ScrmPoTaxRates', $recordId, $existingTaxRow, $updatedTaxRow, $RequestBody, [
                    'ReferenceNo' => $PoID,
                    'Remarks' => 'Export PO tax charge updated',
                ]);
                $this->Error = null;
                $this->Msg = 'PO Tax Charges Updated Successfully';
            }
        }

        $getData = $this->getPoTaxCharges($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];
        return $Data;
    }


    function getPoTaxChargesDetails($RequestBody)
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
        CountervailingDuty,ReciprocalTariffs,ClearingCharges,OtherTax,Tariff,PrintTariff,PrintReciprocalTariff, ServiceTaxPercentage, DrawbackRateperKG, DrawbackPercentage, BLServiceTax,
        ISFStaticDuty, InsurancePremiumRate, UsClearance, IGSTRaw, IGSTBrainFrozen, IGSTCooked, IGSTCookedInShell, 
        IGSTBlanched, FromDate, UptoDate, PostedDate,ExpName, GROUP_CONCAT(ScCL.name) AS CountryNames";
        //uma//

        if ($Type == 'List') {
            $sqlQuery = "SELECT $sqlFields FROM ScrmExporterTaxRates AS ScETR
            LEFT JOIN AdminExporterDetails AS AED ON ScETR.ExpID=AED.ExpID
            LEFT JOIN ScrmCountriesList AS ScCL ON FIND_IN_SET(ScCL.id, ScETR.CountryID)
            WHERE 1=1 $Query GROUP BY ExpTRID ORDER BY ExpTRID, FromDate  ASC " . $Limit;
        }
        //uma//
        else { //uma//
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
            $ArrayTaxChargesList['PrintTariff'] = $dataRow['PrintTariff'];
            $ArrayTaxChargesList['PrintReciprocalTariff'] = $dataRow['PrintReciprocalTariff'];

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

    function getPoTaxCharges($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $DocType = $RequestBody['InvoiceType'];

        $dataRow = [];

        $sqlQuery = "SELECT * FROM ScrmPoTaxRates WHERE PoID = '$PoID' AND InvoiceType = '$DocType'";
        $ssqlQuery = $sqlQuery;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dataRow) {
            $sqlQuery = "SELECT SCETR.*, SCETR.ReciprocalTariffs AS ReciprocalTariff FROM ScrmPoData AS SPD
            LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID = SPD.PoID
            LEFT JOIN ScrmExporterTaxRates AS SCETR ON SCETR.ExpID = SPD.ExporterID
            WHERE SPD.PoID = '$PoID' AND (SCI.CInvDate IS NULL OR SCETR.FromDate<= SCI.CInvDate)
            ORDER BY SCETR.ExpTRID DESC limit 1";
            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();
            $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        }

        $dataRow['Query'] = $sqlQuery;
        $dataRow['ssqlQuery'] = $ssqlQuery;

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Data'] = $dataRow;

        return $data;
    }
}
