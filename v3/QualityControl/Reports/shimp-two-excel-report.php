<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$QueryParms = $_GET['Q'];
if ($QueryParms != '') {
    $QueryParms = urlDecode64($QueryParms);
    $queries = array();
    parse_str($QueryParms, $_GET);
}

if (isset($_GET['PoID']) && !empty($_GET['PoID'])) {

    $api = API_QC_SIMP;

    $bodyContent = array("OrderID" => $_GET["PoID"]);
    $reponse = CallAPI($api, "GET", "", $bodyContent);

    $resShimp = $reponse->OrderDetails;
    $LineItemData = $reponse->LineItemData;
    $Products = $reponse->Products;
    $Tracibility = $reponse->Tracibility;
    $LineItemsPurchaseBillNowiseData = $reponse->LineItemsPurchaseBillNowise;

    $countList = count($LineItemData);
}

$content = '';
$content .= '<table class="tblMain w100 bt bl bb br" style="page-break-inside:avoid">';

$content .= '<tr>
        <td style="width: 205px;vertical-align: middle;">INV NO: ' . $resShimp->InvNumber . '</td>
        <td colspan="4" class="text-center br0" style="font-size: 24px;padding: 20px 3px;">AQUACULTURE SIMP DATA</td>
        <td class="bl0" style="width: 156px;"></td>
    </tr>';
$content .= '<tr>
        <td>IMPORTER: ' . $resShimp->ConsigneeName . '</td>
        <td style="width: 195px">U.S. CONSIGNEE: ' . $resShimp->ConsigneeName . '</td>
        <td colspan="3" class="text-center"> IFTP Permit Name & Number : <br> ' . $resShimp->ConsigneeName . ',&' . $resShimp->ConsigneeIFTPCODE . '</td>
        <td>BILL OF LADING # ' . $resShimp->BillOfLading . ' <br> Container # ' . $resShimp->ContainerNo . '</td>
    </tr>';
$content .= '<tr>
        <td colspan="2" class="p-0">
            <table id="tblHarvest" class="w100">';
$content .= '<tr>
                    <td class="bt0 bl0" style="width: 205px;">HARVESTING COUNTRY: <br> INDIA</td>
                    <td class="bt0 br0">WILD CAUGHT OR <br> FARM RAISED: FARM RAISED</td>
                </tr>';
$content .= '<tr>
                    <td class="bb0 bl0">Fishing Gear Type: NA</td>
                    <td class="bb0 br0">
                        <div class="mgb5">Processing Description:</div>';

$i = "";
foreach ($Products as $item) {
    $content .= '<div class="mgb5">' . $i . ' ' . $item->VarietyFullName . ' </div>';
    // $i++;
}
$content .= '</td>
                </tr>
            </table>
        </td>';
$content .= '<td colspan="4" id="tdGeoGraphic">
            <div class="mgb5">GEOGRAPHIC LOCATION</div>
            <div class="mgb5">(IF FARMED RAISED,REGION,STATE,CITY):</div>';

$i = 1;
foreach ($Tracibility as $item) {
    $content .= '<div class="mgb5">FARM-' . $i . ' ' . $item->TraceNumberWOBatchCode . '-' . $item->AddressContinued . '</div>';
    $i++;
}
$content .= '</td>
    </tr>';

$a = 1;
foreach ($LineItemData as $item) {
    $ReceivedQty = $item->ReceivedQty;
    $content .= '<tr>
            <td>LINE ' . $a . '-Product:<br> HON ' . $item->ScientificName . ' </td>
            <td>SCIENTIFIC SPECIES CODE: ' . $item->ProductASFIS . ' </td>
            <td class="text-center ">' . $item->ProductAddress . ' </td>
            <td class="text-center" style="width:98px">Harvesting Date: <br>' . $item->HarvestDate . ' </td> 
            <td class="text-center" style="width:98px">Receiving Date: <br>' . $item->ReceivingDt . '</td>
            <td>Weight in kilograms: <br>' . $item->GroupWeightRcved . '</td>
        </tr>';
    $a++;
}
$content .= '</table>';

$content .= '<table class="tblMain w100 bt bl bb br" nobr="true">
    <tr>
        <td colspan="6" class="text-center f-14">Model Reprocessing Certificate for Traceability - Primary or Secondary Processing (one species per form) </td>
    </tr>';
$content .= '<tr>
        <td style="width: 20%;" class="text-center">INV NO: ' . $resShimp->InvNumber . '</td>
        <td colspan="5" class="text-center">PO: ' . $resShimp->PoNumber . '</td>
    </tr>';
$content .= '<tr>
        <td colspan="5">( ) Wild Harvest or ( <span>&#10004;</span>  ) Farm Raised: FARM RAISED</td>
    </tr>
</table>';

$content .= '<table border="0" class="tblMain w100 bl bb br" nobr="true">
    <tr>
        <td colspan="6" class="bt0">
            <div>(1) Unique Catch or Production Document Identifier* (Purchase bill number, Registration Number,Pond id: ) </div>
        </td>
    </tr>
    <tr style="font-weight: bold;">
        <td style="width: 56px;"></td>
        <td>Purchase bill number</td>
        <td style="width: 120px;">Registration Number</td>
        <td>POND ID</td>
        <td>Company Product Received From</td>
        <td style="width: 100px;">PHONE NUMBER</td>
    </tr>';
    // <td>
    //         Purchase <br>
    //         bill number
    //     </td>
$b = 1;
foreach ($LineItemData as $item) {
    $content .= '<tr>
            <td>FARM-' . $b . '</td>
            <td>' . $item->PurchaseBillNumber . '</td>
            <td>' . $item->Tracebility . '</td>
            <td>' . $item->PondNumber . '</td>
            <td style="width:440px">FARM-' . $b . ':' . $item->FarmerName . ',' . $item->AddressContinued . '</td>
            <td>' . $b . '.' . $item->FarmerPhoneNumber . '</td>
        </tr>';
    $b++;
}

$content .= '<tr>
    <td colspan="2"> Processor Name: <br>' . $resShimp->ProcessorName . '</td>';
$content .= '<td colspan="2"> Processor Address: <br>' . $resShimp->ProcessorName . ', <br> ' . nl2br($resShimp->PackerAddress) . ',<br>
        ' . $resShimp->ExpPhoneNumber . ',' . $resShimp->ExpMailID . ',' . "08912595672" . ',' . "exports@devifisheries.com" . '</td>';
$content .= '<td colspan="2"> Processor Approval or Registration Number: ' . $resShimp->ExpUsfdaNo . ' (FDA Registration Number)</td>';
$content .= '</tr>
<tr style="font-weight: bold;">
    <td colspan="3">Commodity Scientific Name</td>
    <td>Commodity Market Name : </td>
    <td colspan="3">Product ASFIS#</td>
</tr>';
$c = 1;
foreach ($LineItemData as $item) {
    $content .= '<tr>
            <td colspan="3">FARM-' . $c . ': ' . $item->ScientificName . '</td>
            <td> FARM-' . $c . ':' . $item->CommodityMarketName . ' </td>
            <td colspan="3">FARM-' . $c . ': ' . $item->ProductASFIS . '</td>
        </tr>';
    $c++;
}
$content .= '</table>';
$content .= '<table class="tblMain w100  bl br">
    <tr>
        <td colspan="3" class="p-0 bt0">
            <table class="tblMain w100">
                <tr>
                    <td colspan="3" class="bt0 bl0 br0">Received Weight [ ( ) lbs or ( <span>&#10004;</span>  ) kg]</td>
                </tr>
                <tr style="font-weight: bold;">
                    <td style="width: 60px;" class="bt0 bb0 bl0"></td>
                    <td style="width: 133px;" class="bt0 bb0 ">HOSO WEIGHT</td>
                    <td style="width: 50px;" class="bt0 bb0 br0">GRADE</td>
                </tr>
            </table>

        </td>
        <td class="bt0" style="font-weight: bold;">VARIETY OF FINISHED PRODUCT</td>
        <td class="bt0" style="font-weight: bold;">Received Lot Identifiers(s)* </td>
        <td class="bt0" style="font-weight: bold;">Received Product Form:</td>
    </tr>';
$d = 1;
foreach ($LineItemData as $item) {
    $content .= '<tr>
                <td style="width: 60px;">FARM-' . $d . '</td>
                <td style="width: 133px;">' . $item->GroupWeightRcved . '</td>
                <td style="width: 50px;">' . $item->GradeName . '</td>
                <td> ' . $item->VarietyShortName . '</td>
                <td>FARM ' . $d . ': ' . $item->ReceivedLotIdentifiers . '</td>
                <td>FARM-' . $d . ': HON ' . $item->ScientificName . '</td>
            </tr>';
    $d++;
}

$content .= '<tr>
    <td colspan="4">Finished Product Weight [ ( ) lbs or ( <span>&#10004;</span>  ) kg ],GRADE & Product</td>
    <td>Finished Product Lot Identifier(s)* with Batch no:</td>
    <td>Finished Product Form:</td>
</tr>
<tr style="font-weight: bold;">
    <td style="width: 60px;"></td>
    <td>FINISHED PRODUCT WEIGHT</td>
    <td>GRADE</td>
    <td>PRODUCT</td>
    <td></td>
    <td></td>
</tr>';
$e = 1;
foreach ($LineItemsPurchaseBillNowiseData as $item) {
    $content .= '<tr>
            <td>FARM-' . $e . '</td>
            <td>' . $item->FinishedWeight . '</td>
            <td>' . $item->GradeName . '</td>
            <td>' .  $item->VarietyShortName . '</td>
            <td> ' . $e . '. ' . $item->TracebilityFull . '</td>
            <td style="width: 470px;"> ' . $e . '. ' . $item->Variety . '</td>
        </tr>';
    $e++;
}

$content .= '<tr style="font-weight: bold;">
    <td colspan="4">Production Date(s): </td>
    <td># of Packages (IWP,IVP,Bulk Fish) </td>
    <td>Type of Packaging (IWP,IVP,Bulk Fish,Inner carton,Master Carton) </td>
</tr>';

$f = 1;
foreach ($LineItemData as $item) {
    $content .= '<tr style="height:27px;">
        <td colspan="4">' . $f . ') ' . $item->ProductionDt . ' </td>
        <td class="" >' . $f . ') MASTER CARTONS- ' . $item->McsGrade . '</td>
        <td> ' . $f . ') ' . $item->PackingStyle . ' </td>
    </tr>';
    $f++;
}

$content .= '</table>
<table class="tblMain w100  bl bb br">
    <tr>
        <td colspan="6" class="bt0">
            <div>* Note: A Unique Document Identifier is provided by the harverster or landing recipient and should reflect the unique identifier of a catch or landing certificate.</div>
            <div>* Note: A Lot Identifier is generated by the processor based on its products tracking and record-keeping protocols.</div>
            <div>* Note: Date Format(Month-Date-Year)</div>
        </td>
    </tr>
</table>';


$filename =  $resShimp->InvNumber . '.xls';
// $filename =  $resShimp->InvNumber . '.doc';
// $filename =  $resShimp->InvNumber . '.xlsx';
// header('Content-Type: application/vnd.ms-excel');
// header("Content-Disposition: attachment; filename=" . $filename . "");


// header("Content-Type: application/vnd.ms-excel;base64");
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

// header("Content-Type: application/vnd.ms-word;base64");
header('Content-Disposition: attachment;filename='.$filename.'');
header("Pragma: ");
header("Cache-Control: ");
    
echo "<html><head><style>
table, tr, th, td {
  border: 1px solid black;
}
</style></head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
echo '<body style="border: 0px solid #ccc">';
echo $content;
echo "</body>";
echo "</html>";


// echo $content;
