<?php
//session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");

$Type = (isset($_POST["Type"]) && !empty($_POST["Type"])) ? trim($_POST["Type"]) : "";

if ($Type == "Dashboard") {

	$Exporter = trim($_POST["Exporter"]);
	$Year = trim($_POST["Year"]);
	$Month = trim($_POST["Month"]);
	$Buyer = trim($_POST["Buyer"]);
	$Brand = trim($_POST["Brand"]);
	$Country = trim($_POST["Country"]);
	$ConsigneeID = trim($_POST["ConsigneeID"]);

	$bodyContent = array(
		"FromDate" => "",
		"ToDate" => "",
		"Exporter" => $Exporter,
		"Processor" => "",
		"EtdEta" => "",
		"Type" => $Type,
		"Year" => $Year,
		"Month" => $Month,
		"Buyer" => $Buyer,
		"ConsigneeID" => $ConsigneeID,
		"Brand" => $Brand,
		"Country" => $Country,
		'FromDate' => trim($_POST["FromDate"]),
		'ToDate' => trim($_POST["ToDate"]),
	);
} else {
	if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
		$ExporterID = trim($_GET["Exporter"]);
		$ProcessorID = trim($_GET["Processor"]);
		$ETDETA = trim($_GET["ETDETA"]);
		$FromDate = trim($_GET["FromDate"]);
		$ToDate = trim($_GET["ToDate"]);
		$ConsigneeID = trim($_GET["ConsigneeID"]);
		$border = "border='1'";
	} else {

		$ExporterID = trim($_POST["Exporter"]);
		$ProcessorID = trim($_POST["Processor"]);
		$ETDETA = trim($_POST["ETDETA"]);
		$FromDate = trim($_POST["FromDate"]);
		$ToDate = trim($_POST["ToDate"]);
		$ConsigneeID = trim($_POST["ConsigneeID"]);
		$border = "";
	}
	$bodyContent = array(
		"FromDate" => $FromDate,
		"ToDate" => $ToDate,
		"Exporter" => $ExporterID,
		"Processor" => $ProcessorID,
		"EtdEta" => $ETDETA,
		"ConsigneeID" => $ConsigneeID,
		"Type" => "",
		"Year" => "",
		"Month" => "",
		"Buyer" => "",
		"Brand" => "",
		"Country" => ""
	);
}

$api = GetEtdEtaReportAPI;
// var_dump($bodyContent);

$resGetEtdEtaReportAPI = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resGetEtdEtaReportAPI, JSON_PRETTY_PRINT);
// exit;
$resList = $resGetEtdEtaReportAPI->Data->Data;

// var_dump($resList);

if ($Type == "Dashboard") {
	$content = '<table  id="key-datatableFullscreen" data-paging="false" class="table  table-bordered" >';
} else {
	$content = '<table  id="key-datatable"  ' . $border . ' data-paging="false" class="table table-hover table-bordered" >';
	// $content .= '<table  id="key-datatable"  ' . $border . ' data-paging="false" class="table dt-responsive " >';
}


$content .= '<thead class="thead-light">
<tr style="height: 22px">
        <th style="min-width: 45px;" class="sticky etdEtaReportCol1">S.No</th>
        <th style="min-width: 110px;" class="sticky etdEtaReportCol2">Inv No</th>
        <th style="min-width: 300px;">Buyer</th>
        <th style="min-width: 110px;">PO NO</th>
        <th style="min-width: 110px;">Processor</th>
        <th style="min-width: 280px;">Product</th>
        <th style="min-width: 150px;">B/L.No</th>
        <th style="min-width: 110px;">Container No</th>
        <th style="min-width: 150px;">Final Destination</th>
        <th style="min-width: 110px;">On board Date</th>
        <th style="min-width: 110px;">ETA/FDA</th>
        <th style="min-width: 110px;">Gate OutFull</th>
        <th style="min-width: 110px;">Empty CY Date</th>
        <th style="min-width: 110px;">Transit Days</th>
        <th style="min-width: 180px;">Transit Days as per Liner</th>
        <th style="min-width: 160px;">Liners</th>
        <th style="min-width: 80px;">CY/SD</th>
        <th style="min-width: 110px;">Entry Number</th>
    </tr>
</thead>
<tbody>';
foreach ($resList as $item) {
	$content .= '<tr style="height: 25px">
        <td class="sticky etdEtaReportCol1">' . $item->Sno . '</td>
        <td class="sticky etdEtaReportCol2">' . $item->InvNo . '</td>
        <td>' . $item->ConsigneeName . '</td>
        <td>' . $item->PoNo . '</td>
        <td>' . $item->Processor . '</td>';
	// $content .= '<td>' .  $item->Product . '</td>';
	if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
	$content .= '<td style="width:450px; mso-number-format:\'\@\' ; white-space: nowrap;">' . $item->Product . '</td>';

} else {

   $formattedProduct = preg_replace('/,/', ', ', trim($item->Product));         // Ensure space after commas
    $formattedProduct = preg_replace('/, /', ',<br>', $formattedProduct);        // Line break after each comma
    $content .= '<td>' . $formattedProduct . '</td>';

}
	$content .= '<td>' . $item->BLNo . '</td>
        <td>' . $item->ContainerNo . '</td>';
	if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
		$content .= '<td>' . str_replace('<br>', '', $item->FinalDestination) . '</td>';
	} else {
		$content .= '<td>' . $item->FinalDestination . '</td>';
	}
	$content .= '<td>' . $item->ShippingOnboardDt . '</td>
        <td>' . $item->EtaDate . '</td>
        <td>' . $item->GateOutFull . '</td>
        <td>' . $item->EmptyCYDate . '</td>
        <td>' . $item->TransitDays . '</td>
        <td>' . $item->TransitDaysasperLiner . '</td>
        <td>' . $item->LinerName . '</td>
        <td>' . $item->CYSD . '</td>
        <td>' . $item->EntryNumber . '</td>
    </tr>';
}
$content .= '</tbody>
</table>';

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

	$filename = 'OUT_of_ETD_ETA_Report.xls';
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
