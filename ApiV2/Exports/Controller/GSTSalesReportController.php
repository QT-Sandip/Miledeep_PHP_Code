<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// ini_set('log_errors', 1);
// ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/api_php_errors.log');
// ini_set('max_execution_time', 300);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class GSTSalesReportController
{

    private $conn;
    private $db_table = "";
    private $utilController;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetGSTSalesDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ExporterID = $RequestBody['ExporterID'];
        $ConfigDetails = $RequestBody['ConfigDetails'];
        $ClientID = $ConfigDetails['ClientID'];
        $ClientConfigData = $ConfigDetails['ClientConfigData'];
        $ClientInvoiceConfigData = $ConfigDetails['ClientInvoiceConfigData'];

        $AryConfigDetails['CLFCharges'] = $ClientInvoiceConfigData['CLFCharges'];
        $AryConfigDetails['ClientID'] = $ClientInvoiceConfigData['ClientID'];

        $DateFilter = "where CInvDate between '$FromDate' and '$ToDate'";

        $Query = '';
        if ($ExporterID != "") {
            $Query .= 'and POD.ExporterID in (' . $ExporterID . ')';
        }

        $SqlFieldsList = "
        SCI.CustomsInvNo,
        SCI.CInvDate,
        SCI.ExRate,
        POD.PoID,
        SPC.PortCode,
        SCOM.ShippingBillNo,
        SCOM.ShippingBillDate ,IGST ,QtyUnit";

        $SqlGSTSalesQuery = "SELECT $SqlFieldsList  FROM ScrmCustomInvoices as SCI 
        left join ScrmPoData as POD on POD.PoID=SCI.PiID
        left join ScrmPortCities as SPC on POD.PortofLoading=SPC.PortId
        left join ScrmShippingBillDetails as SCOM on POD.PoID=SCOM.InvID 
        $DateFilter $Query and POD.Status not in (4) Order by IGST desc ";

        $stmt = $this->conn->prepare($SqlGSTSalesQuery);
        $stmt->execute();

        $AryGSTData = [];
        $i = 0;

        // error_log("SQL Query: " . $SqlGSTSalesQuery);exit;

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $InvoiceType = $dataRow['IGST'];
            if ($dataRow['IGST'] == 'IGST') {
                $ExportType = 'WPAY';
            } else {
                $ExportType = 'WOPAY';
            }

            $PoID = $dataRow['PoID'];
            $QtyUnit = $dataRow['QtyUnit'];
            $ExRate = $dataRow['ExRate'];

            $ConfigDetails['ClientID'] = $ClientID;
            $ArrayPaymentValues = $this->utilController->getPaymentValues($PoID, 'CUI', $AryConfigDetails);
            $ArrayPaymentValues = array_map(function ($value) {
                return is_numeric($value) ? round($value, 2) : $value;
            }, $ArrayPaymentValues);
            $aryTypeCount = $this->GetTypeDetails($PoID, $dataRow['QtyUnit']);

            // if ($dataRow['PoID'] == '5924') {
            //     error_log("PoID: " . $dataRow['PoID']);
            //     error_log("Payment Values: " . json_encode($ArrayPaymentValues));
            //     error_log("Type Count: " . json_encode($aryTypeCount));
            //     exit;
            // }

            $ArrayGSTSalesDetails['SNO'] = ++$i;
            $ArrayGSTSalesDetails['ExportType'] = $ExportType;
            $ArrayGSTSalesDetails['InvoiceNumber'] = $dataRow['CustomsInvNo'];
            $ArrayGSTSalesDetails['InvoiceValueUSD'] = number_format($ArrayPaymentValues['InvValueUSD'], 2);
            $ArrayGSTSalesDetails['ExRate'] = $ExRate;
            $ArrayGSTSalesDetails['InvoiceDate'] = $this->utilController->getFormatedDatelines($dataRow['CInvDate']);
            $ArrayGSTSalesDetails['ShippingBillDate'] = $this->utilController->getFormatedDatelines($dataRow['ShippingBillDate']);
            $ArrayGSTSalesDetails['InvoiceValueINR'] = number_format($ArrayPaymentValues['InvValueINR'], 2);
            $ArrayGSTSalesDetails['PortCode'] = $dataRow['PortCode'];
            $ArrayGSTSalesDetails['ShippingBillNumber'] = $dataRow['ShippingBillNo'];
            $ArrayGSTSalesDetails['RawShrimpQuantity'] = '0';
            $ArrayGSTSalesDetails['RawShrimpInvoiceValueAsPerShippingBill'] = '0';
            $ArrayGSTSalesDetails['RawShrimpTaxableValueAsPerShippingBill'] = '0';
            $ArrayGSTSalesDetails['IGSTOnRawShrimp'] = '-';
            $ArrayGSTSalesDetails['CookedShrimpQuantity'] = '0';
            $ArrayGSTSalesDetails['TotalInvoiceValueAsPerShippingBill'] = '0';
            $ArrayGSTSalesDetails['TotalTaxableValueAsPerShippingBill'] = '0';
            $ArrayGSTSalesDetails['CookedShrimpInvoiceValueAsPerShippingBill'] = '0';
            $ArrayGSTSalesDetails['CookedShrimpTaxableValueAsPerShippingBill'] = '0';
            $ArrayGSTSalesDetails['IGSTOnCookedShrimp'] = '-';
            $ArrayGSTSalesDetails['ApplicableTaxRatePerc'] = '0';
            $ArrayGSTSalesDetails['BreakUp'] = $aryTypeCount;
            $ArrayGSTSalesDetails['PaymentValues'] = $ArrayPaymentValues;

            $TotalGstValue = 0;
            $TotalTaxableValueAsPerShippingBill = 0;
            $TotalInvoiceValueAsPerShippingBill = 0;
            $AryGstPer = array();

            $SauceUsdValue = 0;

            $TotalInvoiceValueAsPerShippingBill = $ArrayPaymentValues['InvValueINR'];

            $TotalRawQuantity = 0;
            $TotalCookedQuantity = 0;
            $TotalSauceQuantity = 0;
            $TotalRawInvValue = 0;
            $TotalCookedInvValue = 0;
            $TotalSauceInvValue = 0;
            $TotalRawFOBValue = 0;
            $TotalCookedFOBValue = 0;
            $TotalSauceFOBValue = 0;
            $TotalShrimpQty = 0;
            $RawGstPerc = 0;
            $CookedGstPerc = 0;

            foreach ($aryTypeCount as $TypeCount) {

                // $TotalShrimpItemPrice = 0;

                // $PoBreakUpData = $TypeCount['BreakUpData'];
                // foreach ($PoBreakUpData as $gradeData) {
                //     $gradeData['QtyToDisplay'] = 'KGS';
                //     $ShrimpItemPrice = 0;
                //     if ($gradeData['HasSauce'] == 'Yes') {
                //         $gradeData['PackingStyle'] = $gradeData['ShrimpPackingStyle'];
                //         // $ArySauceData = $this->utilController->getSauceData($gradeData);
                //         // $TotalSauceQty += $ArySauceData['SauceQty'];
                //         // $SauceUsdValue += $ArySauceData['SauceUsdValue'];
                //     }
                //     // $AryShrimpData = $this->utilController->getShrimpData($gradeData);
                //     // $TotalShrimpQty += $AryShrimpData['ShrimpQty'];
                //     // $ShrimpItemPrice = $AryShrimpData['ShrimpUsdValue'] * $ExRate;
                //     // $TotalShrimpItemPrice += $ShrimpItemPrice;
                // }

                // if($TypeCount['SaucePackingStyle'] != '') {
                //     $ArySauceData = $this->utilController->getSauceData($TypeCount);
                //     $TotalSauceQuantity += $ArySauceData['SauceQty'];
                //     $TotalSauceInvValue += $ArySauceData['SauceUsdValue'];
                //     $ArrayGSTSalesDetails['SauceData'] = $ArySauceData;
                // }

                $AryGstPer[] = $TypeCount['GstPerc'];

                $ItemPriceInr = $TypeCount['ItemPrice'] * $ExRate;
                // $gstInr = (($ItemPriceInr * $TypeCount['GstPerc']) / 100);
                // $TotalGstValue +=  $gstInr;

                switch ($TypeCount['TypeProduct']) {
                    case 'RAW':
                        $TotalRawQuantity = round($TypeCount['Weight'], 2);
                        $TotalRawInvValue = round($TypeCount['ItemPrice'], 2);
                        $RawGstPerc = $TypeCount['GstPerc'];

                        $ArrayGSTSalesDetails['RawShrimpQuantity'] = $TotalRawQuantity;
                        $ArrayGSTSalesDetails['RawInvValueUsd'] = $TotalRawInvValue;
                        // $ArrayGSTSalesDetails['RawShrimpTaxableValueAsPerShippingBill'] = round($ItemPriceInr, 2);
                        // $ArrayGSTSalesDetails['IGSTOnRawShrimp'] = round($gstInr, 2);
                        break;

                    case 'COOKED':
                        $TotalCookedQuantity = round($TypeCount['Weight'], 2);
                        $TotalCookedInvValue = round($TypeCount['ItemPrice'], 2);
                        $CookedGstPerc = $TypeCount['GstPerc'];
                        $ArrayGSTSalesDetails['CookedShrimpQuantity'] = $TotalCookedQuantity;
                        $ArrayGSTSalesDetails['CookedInvValueUsd'] = $TotalCookedInvValue;
                        // $ArrayGSTSalesDetails['CookedShrimpTaxableValueAsPerShippingBill'] = round($ItemPriceInr, 2);
                        // $ArrayGSTSalesDetails['IGSTOnCookedShrimp'] = round($gstInr, 2);
                        break;
                }
            }

            $TotalSauceQuantity += $ArrayPaymentValues['SauceLbsWeight'] * 0.454;

            $TotalShrimpQty = $TotalRawQuantity + $TotalCookedQuantity + $TotalSauceQuantity;
            $TotalRawFOBValue = ($TotalRawInvValue / $ArrayPaymentValues['InvValueUSD'])  * $ArrayPaymentValues['FOBValueUSD'];
            $TotalCookedFOBValue = ($TotalCookedInvValue / $ArrayPaymentValues['InvValueUSD'])  * $ArrayPaymentValues['FOBValueUSD'];
            $TotalSauceFOBValue = ($ArrayPaymentValues['SauceValueUsd'] / $ArrayPaymentValues['InvValueUSD'])  * $ArrayPaymentValues['FOBValueUSD'];

            $ArrayGSTSalesDetails['RawFOBValue'] = round($TotalRawFOBValue, 2);
            $ArrayGSTSalesDetails['CookedFOBValue'] = round($TotalCookedFOBValue, 2);
            $ArrayGSTSalesDetails['SauceFOBValue'] = round($TotalSauceFOBValue, 2);
            $ArrayGSTSalesDetails['TotalFOBValueUSD'] = round($ArrayPaymentValues['FOBValueUSD'], 2);

            $IGSTOnRawShrimp = round((($TotalRawFOBValue * $RawGstPerc) / 100) * $ExRate, 2);
            $IGSTOnCookedShrimp = round((($TotalCookedFOBValue * $CookedGstPerc) / 100) * $ExRate, 2);

            $TotalGstValue = $IGSTOnRawShrimp + $IGSTOnCookedShrimp;

            $ArrayGSTSalesDetails['RawShrimpFOBINR'] = round($TotalRawFOBValue, 2) * $ExRate;
            $ArrayGSTSalesDetails['CookedShrimpFOBINR'] = round($TotalCookedFOBValue, 2) * $ExRate;
            $ArrayGSTSalesDetails['SauceShrimpFOBINR'] = round($TotalSauceFOBValue, 2) * $ExRate;

            $ArrayGSTSalesDetails['TotalFOBValueINR'] = $ArrayPaymentValues['FOBValueINR'];
            $ArrayGSTSalesDetails['FreightValue'] = $ArrayPaymentValues['CustomInvoiceFrieghtCharge'] * $ExRate;
            // $ArrayGSTSalesDetails['AntiDumpingDuty'] = $ArrayPaymentValues['AntiDumpingDuty'] * $ExRate;
            $TotalDutiesPrice =  $ArrayPaymentValues['DutiesPrice'] +  $ArrayPaymentValues['ClearingAndForwardingCharges'];
            $ArrayGSTSalesDetails['AntiDumpingDuty'] = $TotalDutiesPrice * $ExRate;
            $ArrayGSTSalesDetails['InvValueINR'] = $ArrayPaymentValues['InvValueINR'];
            $ArrayGSTSalesDetails['RawShrimpInvoiceValueAsPerShippingBill'] = $TotalRawInvValue * $ExRate;
            $ArrayGSTSalesDetails['CookedShrimpInvoiceValueAsPerShippingBill'] = $TotalCookedInvValue * $ExRate;
            $ArrayGSTSalesDetails['SauceShrimpInvoiceValueAsPerShippingBill'] = 0;

            $ArrayGSTSalesDetails['SauceQuantity'] = $TotalSauceQuantity;
            $ArrayGSTSalesDetails['SauceShrimpInvoiceValueAsPerShippingBill'] = round($ArrayPaymentValues['SauceValueUsd'], 2) * $ExRate;

            //echo $TotalTaxableValueAsPerShippingBill;
            $ArrayGSTSalesDetails['TotalInvoiceValueAsPerShippingBill'] = number_format($ArrayPaymentValues['InvValueINR'], 2);
            $ArrayGSTSalesDetails['TotalTaxableValueAsPerShippingBill'] = number_format($ArrayPaymentValues['FOBValueINR'], 2);
            $ArrayGSTSalesDetails['ApplicableTaxRatePerc'] = implode("&", $AryGstPer);

            if ($InvoiceType != 'IGST') {
                // $ArrayGSTSalesDetails['RawShrimpQuantity'] = "-";
                // $ArrayGSTSalesDetails['RawShrimpInvoiceValueAsPerShippingBill'] = "-";
                $ArrayGSTSalesDetails['RawShrimpTaxableValueAsPerShippingBill'] = "-";
                // $ArrayGSTSalesDetails['CookedShrimpQuantity'] = "-";
                //$ArrayGSTSalesDetails['CookedShrimpInvoiceValueAsPerShippingBill'] = "-" ;
                $ArrayGSTSalesDetails['CookedShrimpTaxableValueAsPerShippingBill'] = "-";
                $ArrayGSTSalesDetails['SauceShrimpTaxableValueAsPerShippingBill'] = "-";
                $ArrayGSTSalesDetails['TotalTaxableValueAsPerShippingBill'] = $TotalGstValue;
                $ArrayGSTSalesDetails['TotalIGSTValue'] = "0";
                $ArrayGSTSalesDetails['IGSTOnRawShrimp'] = '-';
                $ArrayGSTSalesDetails['IGSTOnCookedShrimp'] = '-';
                $ArrayGSTSalesDetails['IGSTOnSauceShrimp'] = '-';
            } else {
                $ArrayGSTSalesDetails['TotalIGSTValue'] = "" . $TotalGstValue;
                $ArrayGSTSalesDetails['IGSTOnRawShrimp'] = $IGSTOnRawShrimp;
                $ArrayGSTSalesDetails['IGSTOnCookedShrimp'] = $IGSTOnCookedShrimp;
            }

            //  $ArrayGSTSalesDetails['ApplicableTaxRatePerc'] = 'Doubt?5,12';


            /*   $ArrayGSTSalesDetails[''] = 'Doubt';
            $ArrayGSTSalesDetails[''] = 'Doubt';
            $ArrayGSTSalesDetails['TotalInvoiceValueAsPerShippingBill'] = 'Doubt';
            $ArrayGSTSalesDetails['RawShrimpTaxableValueAsPerShippingBill'] = 'Doubt';
            $ArrayGSTSalesDetails['TotalTaxableValueAsPerShippingBill'] = 'Doubt';
            $ArrayGSTSalesDetails['ApplicableTaxRatePerc'] = 'Doubt';
            $ArrayGSTSalesDetails['IGSTOnRawShrimp'] = 'Doubt';
            $ArrayGSTSalesDetails['IGSTOnCookedShrimp'] = 'Doubt';
            */

            $AryGSTData[] = $ArrayGSTSalesDetails;
        }
        if (count($AryGSTData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Data'] = $AryGSTData;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }



    public function GetTypeDetails($OrderID, $QtyUnit)
    {
        $sqlQuery = "SELECT GROUP_CONCAT(SPGD.ID) as ItemID , SPVL.TypeProduct ,
        (SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1)
		GstPerc,
		SPM.GstPerc GstPercP
        FROM ScrmPoGradeData As SPGD
        LEFT JOIN ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
        LEFT JOIN ScrmPIVarietyList As SPVL on SPGD.VarietyID=SPVL.VarietyID
        LEFT JOIN ScrmPreparationMaster AS SPM on SPM.ItemType=SPVL.TypeProduct
        WHERE SPM.ShortCode=SPVL.PreparationType  and InvId='$OrderID' GROUP by TypeProduct";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            while ($dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC)) {
                $itemID = $dataRow['ItemID'];

                // $groupWeight = $this->utilController->getItemWeightandPrice($itemID, $QtyUnit);
                // $groupPrice = $this->utilController->getProductWisePriceDetails($varietyID, 'KGS');

                $breakUpData = $this->utilController->getGradeBreakUpData($itemID, '', '');

                $itemData['TypeProduct'] = $dataRow['TypeProduct'];

                // $itemData['Weight'] = $groupWeight['WeightKgs'];
                // $itemData['ItemPrice'] = $groupWeight['ItemPrice'];

                $itemData['Weight'] = $breakUpData['NetWgtKgs'];
                $itemData['ItemPrice'] = $breakUpData['UsdValue'];

                $itemData['GstPerc'] = !empty($dataRow['GstPerc']) ? $dataRow['GstPerc'] : $dataRow['GstPercP'];

                // $itemData['GstValue'] = $groupWeight['ItemPrice'] - (($groupWeight['ItemPrice'] * $dataRow['GstPerc']) / 100);
                // $breakUpData = $this->GetPoBreakUpDetails($itemID);

                $itemData['BreakUpData'] = $breakUpData;
                $aryData[] = $itemData;
                $itemData['TypeProduct'];
            }
            return $aryData;
        }
    }

    public function GetPoBreakUpDetails($OrderID)
    {
        $sqlQuery = "SELECT SCD.ID, SCD.Mcs, SCD.Price, SCD.CUPS, SCD.CupsPrice, SCD.TraysQuantity, SCD.InvId,
        SPVL.HasSauce, SPVL.TypeProduct, SPVL.PackingStyle, SPVL.ShrimpPackingStyle, SPVL.SaucePackingStyle FROM `ScrmPoGradeData`  as SCD
        LEFT JOIN ScrmPIVarietyList AS SPVL on SPVL.VarietyID=SCD.VarietyID
        WHERE ID IN ($OrderID) ORDER BY `ID` ASC";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        return $stmtExec->fetchAll(PDO::FETCH_ASSOC);
    }
}
