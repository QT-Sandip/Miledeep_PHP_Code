<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Libraries/tcpdf/tcpdf.php';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$Type = isset($_GET["Type"]) && !empty($_GET["Type"]) ? $_GET["Type"] : "1";;
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle("BOUGHT BILL $id");
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(true, 5);

$arrivalIds = explode(',', $_GET['ArrivalIds']);
// echo print_r($arrivalIds); exit;
// Create temp folder
// $tempDir = sys_get_temp_dir() . '/bulk_bills_' . uniqid();
// mkdir($tempDir);

foreach ($arrivalIds as $id) {
    $ArrivalBillID = trim($id);

    $api = API_RM_BILLS;

    $bodyContent = array("ArrivalID" => $ArrivalBillID);
    $resPostPurchaseBillPrint = CallAPI($api, "GET", "", $bodyContent);

    //   var_dump($resPostPurchaseBillPrint);

    $result = $resPostPurchaseBillPrint->Data;
    $resPurchaseData = $resPostPurchaseBillPrint->Data->ArrivalData;
    $resPurchaseBreakUpData = $resPostPurchaseBillPrint->Data->BreakUpData;
    $resGrandTotal = $resPostPurchaseBillPrint->Data->GrandTotal;
    $resBillerDetails = $resPostPurchaseBillPrint->Data->BillerDetails;

    $ClientName = $_SESSION['ClientName'];

    $c_billDate = new DateTime($resPurchaseData->BillDate);
    $c_startDate = new DateTime('2025-08-01');

    $billDateLessThanAugOne = 0;
    if ($c_billDate >= $c_startDate) {
        $billDateLessThanAugOne = 1;
    }

    $lable_Harvest_Expenses = "Harvest Expenses";
    $lable_Harvest = "Harvest";
    if($billDateLessThanAugOne == 1){
        $lable_Harvest_Expenses = "Commission";
        $lable_Harvest = "Rate";
    }

    // Generate HTML


    $html = '
<style>
    .w100 { width: 100%; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .f-800 { font-weight: bold; }
    .f-12 { font-size: 10px; }
    .f-13 { font-size: 13px; }
    .f-14 { font-size: 14px; }
    .bold { font-weight: bold; }
    table { border-collapse: collapse; }
    .bordered { border: 1px solid #000; }
    .bordered td, .bordered th { border: 1px solid #000; }
    .page-break { page-break-after: always; }
</style>

<div class="page">
    <!-- Original Bill -->
    <table class="w100 bordered">
        <tr>
            <td class="text-center f-800 f-12" style="width: 49.5%;">Original</td>
            <td style="width: 1%;"></td>
            <td class="text-center f-800 f-12" style="width: 49.5%;">Duplicate</td>
        </tr>
    </table>

    <table class="w100 bordered">
        <tr>
            <td style="width: 49.5%; vertical-align: top;">
                <!-- Original Side -->
                <table class="w100">
                    <tr>
                        <td colspan="2" class="f-13 f-800">GST: ' . $resBillerDetails->GSTNumber . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width: 20%; text-align:left; vertical-align:middle;">
                                        <img src="'.$resBillerDetails->LogoUrl.'" style="height:45px;">
                                    </td>
                                    <td style="width: 80%; text-align: center; vertical-align: middle;">
                                        <h1 class="bold">' . ($resBillerDetails->CompanyName ? $resBillerDetails->CompanyName : $ClientName) . '</h1>
                                        <p class="f-14">BOUGHT NOTE</p>
                                    </td>

                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>Bill No: ' . $resPurchaseData->ArrivalNumber . '</td>
                        <td class="text-right">Bill Date: ' . $resPurchaseData->BillDate . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">Supplier: ' . ($Type == '2'?$resPurchaseData->BillName:$resPurchaseData->SupplierName) . '</td>
                    </tr>
                    <tr>
                        <td>Address: ' . $resPurchaseData->SupplierAddress . '</td>
                        <td class="text-right">';
    if (isset($resPurchaseData->PhoneNumber) && !empty($resPurchaseData->PhoneNumber)) {
        $html .= 'Phone: ' . $resPurchaseData->PhoneNumber;
    }
    $html .= '</td>
                    </tr>
                    <tr>
                        <td>PAN: ' . ($Type == '2'?$resPurchaseData->AgentPanNumber:$resPurchaseData->PanNumber) . '</td>
                        <td class="text-right">';
    if (isset($resPurchaseData->GSTNumber) && !empty($resPurchaseData->GSTNumber)) {
        $html .= 'GST: ' . $resPurchaseData->GSTNumber;
    }
    $html .= '</td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table class="w100 bordered">';
    if ($Type == '1') {


        $html .= '<tr class="f-12 f-800">
                        <th style="width:10%">Sl.No</th>
                        <th style="width:16%">Variety</th>
                        <th style="width:12%">Count</th>
                        <th style="width:20%">Qty in KGs</th>
                        <th style="width:19%">Rate In Rs</th>
                        <th style="width:23%">Amt In Rs</th>
                    </tr>';

        $i = 1;
        foreach ($resPurchaseBreakUpData as $item) {
            $html .= '<tr>
                            <td class="text-center">' . $i . '</td>
                            <td>' . $item->Variety . '</td>
                            <td class="text-center">' . $item->Count . '</td>
                            <td class="text-right">' . $item->Weight . '</td>
                            <td class="text-right">' . $item->Rate . '</td>
                            <td class="text-right">' . $item->BreakupTotal . '</td>
                        </tr>';
            $i++;
        }

        // Add empty rows if needed
        $listCount = count($resPurchaseBreakUpData);
        if ($listCount <= 8) {
            $ite = 8 - $listCount;
            for ($a = 0; $a < $ite; $a++) {
                $html .= '<tr>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                    </tr>';
            }
        }

        $html .= '
                    <tr class="f-800">
                        <td colspan="3" class="text-center">Total</td>
                        <td class="text-right">' . $resGrandTotal->WeightTotal . '</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->ValueTotal . '</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center">Less TDS @ 0.1 %</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->ValueTotalExTds . '</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center">Amount Payable After TDS</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->TdsValue . '</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center f-12">Amount in Words</td>
                        <td colspan="3" class="text-center f-12">' . $resGrandTotal->AmountInWords . '</td>
                    </tr>';
    } else if ($Type == '2') {
        $html .= '<tr class="f-12 f-800">
                        <th style="width:34%">Variety</th>
                        <th style="width:26%">Quantity - In kgs</th>
                        <th style="width:16%">'.$lable_Harvest.'</th>
                        <th style="width:24%">Amount In Rs</th>
                    </tr>
                    <tr>
                        <td class="text-center">'.$lable_Harvest_Expenses.'</td>
                        <td>' . $resGrandTotal->WeightTotal . '</td>
                        <td class="text-center">' . $resGrandTotal->HarvestCommission . '</td>
                        <td class="text-right">' . $resGrandTotal->HarvestExpanses . '</td>
                    </tr>
                     <tr>
                        <td style="border: none; height: 140px;">&nbsp;</td>
                        <td style="border: none;">&nbsp;</td>
                        <td style="border: none;">&nbsp;</td>
                        <td style="border: none;">&nbsp;</td>
                    </tr>

                    <tr class="f-800">
                        <td colspan="1" class="text-center">Total</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->WeightTotal . '</td>
                        <td class="text-right">' . $resGrandTotal->HarvestExpanses . '</td>
                    </tr>';
 if( $billDateLessThanAugOne ){
    $amt_pay_aft_tds_lable = "Amount Payable After TDS";
    $amt_HarvestExpAfter = $resGrandTotal->HarvestExpAfterCommision;
                     $html .='<tr>
                        <td colspan="1" class="text-center">Less TDS @ 2 %</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->HarvestExpansesCommision . '</td>
                    </tr>';
}else{
    $amt_pay_aft_tds_lable = "Amount Payable After TDS";
    $amt_HarvestExpAfter = $resGrandTotal->HarvestExpAfterTds;
             $html .='<tr>
                        <td colspan="1" class="text-center">Less TDS @ 0.1 %</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->HarvestExpansesTds . '</td>
                    </tr>';
}

                    $html .= '<tr>
                        <td colspan="2" class="text-center">'.$amt_pay_aft_tds_lable.'</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $amt_HarvestExpAfter . '</td>
                    </tr>
                    <tr>
                        <td colspan="1" class="text-center f-12">Amount in Words</td>
                        <td colspan="3" class="text-center f-12">' . $resGrandTotal->HarvestExpAmountInWords . '</td>
                    </tr>';
    }
    $html .= '</table>

                <!-- Signature Table -->
                <table class="w100 bordered">
                    <tr>
                        <td style="width:24%; height: 40px;">&nbsp;</td>
                        <td style="width:36%; height: 40px;">&nbsp;</td>
                        <td style="width:40%; height: 40px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="text-center">Supplier<br>Signature</td>
                        <td class="text-center">Checked By</td>
                        <td class="text-center">Authorized Sign</td>
                    </tr>
                </table>
            </td>

            <td style="width: 1%;"></td>

            <!-- Duplicate Side -->
            <td style="width: 49.5%; vertical-align: top;">
                <table class="w100">
                    <tr>
                        <td colspan="2" class="f-13 f-800">GST: ' . $resBillerDetails->GSTNumber . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width: 20%; text-align:left; vertical-align:middle;">
                                        <img src="'.$resBillerDetails->LogoUrl.'" style="height:45px;">
                                    </td>
                                    <td style="width: 80%; text-align: center; vertical-align: middle;">
                                        <h1 class="bold">' . ($resBillerDetails->CompanyName ? $resBillerDetails->CompanyName : $ClientName) . '</h1>
                                        <p class="f-14">BOUGHT NOTE</p>
                                    </td>

                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>Bill No: ' . $resPurchaseData->ArrivalNumber . '</td>
                        <td class="text-right">Bill Date: ' . $resPurchaseData->BillDate . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">Supplier: ' . ($Type == '2'?$resPurchaseData->BillName:$resPurchaseData->SupplierName) . '</td>
                    </tr>
                    <tr>
                        <td>Address: ' . $resPurchaseData->SupplierAddress . '</td>
                        <td class="text-right">';
    if (isset($resPurchaseData->PhoneNumber) && !empty($resPurchaseData->PhoneNumber)) {
        $html .= 'Phone: ' . $resPurchaseData->PhoneNumber;
    }
    $html .= '</td>
                    </tr>
                    <tr>
                        <td>PAN: ' . ($Type == '2'?$resPurchaseData->AgentPanNumber:$resPurchaseData->PanNumber) . '</td>
                        <td class="text-right">';
    if (isset($resPurchaseData->GSTNumber) && !empty($resPurchaseData->GSTNumber)) {
        $html .= 'GST: ' . $resPurchaseData->GSTNumber;
    }
    $html .= '</td>
                    </tr>
                </table>

                <!-- Items Table for Duplicate -->
                <table class="w100 bordered">';
    if ($Type == '1') {


        $html .= '<tr class="f-12 f-800">
                        <th style="width:10%">Sl.No</th>
                        <th style="width:16%">Variety</th>
                        <th style="width:12%">Count</th>
                        <th style="width:20%">Qty in KGs</th>
                        <th style="width:19%">Rate In Rs</th>
                        <th style="width:23%">Amt In Rs</th>
                    </tr>';

        $i = 1;
        foreach ($resPurchaseBreakUpData as $item) {
            $html .= '<tr>
                            <td class="text-center">' . $i . '</td>
                            <td>' . $item->Variety . '</td>
                            <td class="text-center">' . $item->Count . '</td>
                            <td class="text-right">' . $item->Weight . '</td>
                            <td class="text-right">' . $item->Rate . '</td>
                            <td class="text-right">' . $item->BreakupTotal . '</td>
                        </tr>';
            $i++;
        }

        // Add empty rows if needed
        $listCount = count($resPurchaseBreakUpData);
        if ($listCount <= 8) {
            $ite = 8 - $listCount;
            for ($a = 0; $a < $ite; $a++) {
                $html .= '<tr>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                        <td style="border: none;">&nbsp;</td>
                                    </tr>';
            }
        }

        $html .= '
                    <tr class="f-800">
                        <td colspan="3" class="text-center">Total</td>
                        <td class="text-right">' . $resGrandTotal->WeightTotal . '</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->ValueTotal . '</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center">Less TDS @ 0.1 %</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->ValueTotalExTds . '</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center">Amount Payable After TDS</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->TdsValue . '</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center f-12">Amount in Words</td>
                        <td colspan="3" class="text-center f-12">' . $resGrandTotal->AmountInWords . '</td>
                    </tr>';
    } else if ($Type == '2') {
        $html .= '<tr class="f-12 f-800">
                        <th style="width:34%">Variety</th>
                        <th style="width:26%">Quantity - In kgs</th>
                        <th style="width:16%">Harvest</th>
                        <th style="width:24%">Amount In Rs</th>
                    </tr>
                    <tr>
                        <td class="text-center">'.$lable_Harvest_Expenses.'</td>
                        <td>' . $resGrandTotal->WeightTotal . '</td>
                        <td class="text-center">' . $resGrandTotal->HarvestCommission . '</td>
                        <td class="text-right">' . $resGrandTotal->HarvestExpanses . '</td>
                    </tr>
                    <tr>
                        <td style="border: none; height: 140px;">&nbsp;</td>
                        <td style="border: none;">&nbsp;</td>
                        <td style="border: none;">&nbsp;</td>
                        <td style="border: none;">&nbsp;</td>
                    </tr>

                    <tr class="f-800">
                        <td colspan="1" class="text-center">Total</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->WeightTotal . '</td>
                        <td class="text-right">' . $resGrandTotal->HarvestExpanses . '</td>
                    </tr>';
if( $billDateLessThanAugOne ){
    $amt_pay_aft_tds_lable = "Amount Payable After TDS";
    $amt_HarvestExpAfter = $resGrandTotal->HarvestExpAfterCommision;
    $html .= '<tr>
                        <td colspan="1" class="text-center">Less TDS @ 2 %</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->HarvestExpansesCommision . '</td>
                    </tr>';
}else{
    $amt_pay_aft_tds_lable = "Amount Payable After TDS";
    $amt_HarvestExpAfter = $resGrandTotal->HarvestExpansesTds;
    $html .= '<tr>
                        <td colspan="1" class="text-center">Less TDS @ 0.1 %</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $resGrandTotal->HarvestExpansesTds . '</td>
                    </tr>';
}

                    $html .= '<tr>
                        <td colspan="2" class="text-center">'.$amt_pay_aft_tds_lable.'</td>
                        <td>&nbsp;</td>
                        <td class="text-right">' . $amt_HarvestExpAfter . '</td>
                    </tr>
                    <tr>
                        <td colspan="1" class="text-center f-12">Amount in Words</td>
                        <td colspan="3" class="text-center f-12">' . $HarvestExpAmountInWords . '</td>
                    </tr>';
    }
    $html .= '</table>

                <!-- Signature Table for Duplicate -->
                <table class="w100 bordered">
                    <tr>
                        <td style="width:24%; height: 40px;">&nbsp;</td>
                        <td style="width:36%; height: 40px;">&nbsp;</td>
                        <td style="width:40%; height: 40px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="text-center">Supplier<br>Signature</td>
                        <td class="text-center">Checked By</td>
                        <td class="text-center">Authorized Sign</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>';
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');
}

$pdfName = "BOUGHT" . ($Type == 1 ? "-BILLS" : "-COMMISSIION-BILLS") . ".pdf";
$pdf->Output($pdfName, 'D');
