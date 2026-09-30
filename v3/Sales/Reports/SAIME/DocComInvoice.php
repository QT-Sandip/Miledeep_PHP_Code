<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
// include($cp . "Includes/IncludePage.php");
include("../../Includes/IncludePage.php");
require_once __DIR__ . '/Includes/SaimeReportData.php';
require_once __DIR__ . '/Includes/SaimeBreakupRows.php';

include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
require_once __DIR__ . '/Includes/SaimeSinglePagePdf.php';

$style = '<style>
th {
    font-size: 8px;
	padding : 10px;
	
}
td {
    font-size:  8px;
	padding : 10px;
	
}
p {
	line-height: 0.9;
}
</style> 
';

$PoID = $_GET['id'];
$Type = $_GET['Type'];

$AdminConfigDetails = json_decode(json_encode($_SESSION['ClientConfigData']), true);
// Temporary line added only for the DEV client. This will be removed soon.
$AdminConfigDetails['ComInvPrintReciprocal'] = true;

$SqlCustom = "SELECT ExporterID, DestinationCountry, InvDate FROM  ScrmPoData WHERE PoID='$PoID'";
$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom->fetch_assoc();

$ExpID = $RowCustom['ExporterID'];
$DestinationCountryId = $RowCustom['DestinationCountry'];
$InvDate = $RowCustom['InvDate'];

$CanadaLbsFactor = $DestinationCountryId == 23 && $InvDate >= '2026-03-01' ? 2.2046 : 'LbsFactor';

$DocType = 'CMI';

class MYPDF extends TCPDF
{

	public function Header()
	{
		global

		$IGST;
		if ($IGST == 'IGST') {
			$Header2 = 'SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
		} else {
			$Header2 = '';
		}

		global $Header;
		$image_file = 'images/ds_logo.jpg';
		$this->SetFont('helvetica', 'B', 8);
		$this->Cell(120, 0, 'COMMERCIAL INVOICE' . $Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', ' ', 8);
		$this->Cell(130, 0, '' . $Header2, 0, 0, 'R', 0, '', 0, false, 'T', 'M');

		$this->SetTextColor(255, 0, 0);

		$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');

	}

	public function Footer()
	{
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}

/*if($Type==''){
$Type='I';
}*/

$pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('CUSTOMS INVOICE CUM PACKING LIST' . $Header);
$pdf->SetTitle('' . $Header2);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(4);
$pdf->SetMargins(8, 8, 8, 5);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();


$TableData = getCommercialNewFormatData($iconn, $PoID);
writeSaimeSinglePage($pdf, $TableData);
$pdf->SetFont('helvetica', '', 6);



/***uma 28-07-2022**/
$ArrayFilename = getfilename($iconn, $ExpID, $PoID);

$InvShortCode = $ArrayFilename['InvShortCode'];
$CInvNo = $ArrayFilename['CInvNo'];
$FinancialYear = $ArrayFilename['FinancialYear'];

$file_name = $InvShortCode . '-CI-' . $CInvNo . '-' . $FinancialYear . '.pdf';
//$file_name=$file_name.'- CI.pdf';


//echo $Type;
ob_end_clean();
if ($Type == '') {
	$Type = 'I';
} else {
	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '', $file_name);
	;
	$file_name = $Fname . $file_name;
}

$pdf->Output($file_name, $Type);





function getCommercialNewFormatData($iconn, $PoID)
{
	$linerName = SaimeReportData::linerName($iconn, $PoID);
	global $DocType, $AdminConfigDetails, $CanadaLbsFactor;

	$e = function ($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	};
	$nl = function ($value) use ($e) {
		return nl2br($e($value));
	};
	$rate = function ($value) {
		$value = (float) str_replace(',', '', (string) $value);
		return abs($value) >= 1000 ? $value / 1000 : $value;
	};

	$data = getReportData($iconn, $PoID);
	$referenceNo = '';
	$stmtPackingParties = $iconn->prepare('SELECT SPD.ReferenceNo, SIR.ImporterName, SIR.ImporterAddress, SIR.ImporterCode,
            SBD.BuyerIRSNumber
        FROM ScrmPoData SPD
        LEFT JOIN ScrmImporterOfRecord SIR ON SIR.ImporterID = SPD.ImporterID
        LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID = SPD.BuyerID
        WHERE SPD.PoID = ? LIMIT 1');
	$stmtPackingParties->bind_param('i', $PoID);
	$stmtPackingParties->execute();
	$packingParties = $stmtPackingParties->get_result()->fetch_assoc();
	if ($packingParties) {
		$referenceNo = trim((string) $packingParties['ReferenceNo']);
		if (trim((string) $packingParties['ImporterName']) !== '')
			$data['ImporterName'] = $packingParties['ImporterName'];
		if (trim((string) $packingParties['ImporterAddress']) !== '')
			$data['ImporterAddress'] = $packingParties['ImporterAddress'];
		if (trim((string) $packingParties['ImporterCode']) !== '') {
			$data['ImporterCode'] = $packingParties['ImporterCode'];
		} elseif (trim((string) $packingParties['BuyerIRSNumber']) !== '') {
			$data['ImporterCode'] = $packingParties['BuyerIRSNumber'];
		}
	}
	if (trim((string) $data['ImporterName']) === '')
		$data['ImporterName'] = $data['ExporterName'];
	if (trim((string) $data['ImporterAddress']) === '') {
		$data['ImporterAddress'] = $data['ExporterAddress'];
	}
	if (trim((string) $data['ImporterCode']) === '' && stripos((string) $data['ExporterName'], 'SAI MARINE') !== false) {
		$data['ImporterCode'] = '044601-01127';
	}
	$data['ProcessorName'] = saimeReportValue($iconn, $PoID, 'ProcessorName', $data['ProcessorName']);
	$data['ImporterName'] = saimeReportValue($iconn, $PoID, 'ImporterName', $data['ImporterName']);
	$data['ImporterAddress'] = saimeReportValue($iconn, $PoID, 'ImporterAddress', $data['ImporterAddress']);
	$data['ProcessorAddress'] = saimeReportValue($iconn, $PoID, 'ProcessorAddress', $data['ProcessorAddress']);
	// Fetch the notify-party row directly. The commercial report query joins several
	// tables with duplicate column names, so relying on SELECT * can hide its address.
	$notifyPartyId = isset($data['NPID']) ? (int) $data['NPID'] : 0;
	if ($notifyPartyId > 0) {
		$stmtNotify = $iconn->prepare('SELECT NotifyPartyName, NPartyAddress, NPartyContactNumber, NPartyFaxNo FROM ScrmNotifyparty WHERE NotifyPartyID = ? LIMIT 1');
		$stmtNotify->bind_param('i', $notifyPartyId);
		$stmtNotify->execute();
		$notifyRow = $stmtNotify->get_result()->fetch_assoc();
		if ($notifyRow) {
			$data['NotifyPartyName'] = $notifyRow['NotifyPartyName'];
			$data['NotifyPartyAddress'] = $notifyRow['NPartyAddress'];
			$data['NPartyContactNumber'] = $notifyRow['NPartyContactNumber'];
			$data['NPartyFaxNo'] = $notifyRow['NPartyFaxNo'];
		}
	}
	$payment = getPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetails);

	$bank = array();
	$bankId = (int) $data['BankID'];
	if ($bankId > 0) {
		$stmtBank = $iconn->prepare('SELECT * FROM ScrmBankDetailsMaster WHERE ID = ?');
		$stmtBank->bind_param('i', $bankId);
		$stmtBank->execute();
		$bank = $stmtBank->get_result()->fetch_assoc() ?: array();
	}
	$sourceIsKgs = strtoupper(trim((string) $data['QtyUnit'])) === 'KGS';
	$selectedUnit = $sourceIsKgs ? 'KGS' : 'LBS';
	$isKgs = $selectedUnit === 'KGS';
	$weightUnit = $isKgs ? 'KGS' : 'LBS';
	$rateUnit = $isKgs ? 'KG' : 'LB';

	$stmt = $iconn->prepare("SELECT PGD.*, POGD.BuyerItemCode AS BreakupBuyerItemCode,
		APS.PackingStyle, APS.TotalSlabs, APS.SlabWeight, APS.MFactor, APS.LbsFactor, APS.Uom
        FROM ScrmCIGradeData PGD
        LEFT JOIN AdminPackingStyles APS ON PGD.PackingStyleID = APS.PCKID
		LEFT JOIN ScrmPoGradeData POGD ON POGD.ID = PGD.PoGradeID
        WHERE PGD.InvId = ? ORDER BY PGD.Variety, PGD.PackingStyleID, PGD.Brand, ABS(PGD.Grade)");
	$stmt->bind_param('i', $PoID);
	$stmt->execute();
	$items = $stmt->get_result();

	$rows = array();
	$specParts = array();
	$totalCases = 0;
	$totalKg = 0.0;
	$totalLb = 0.0;
	$itemTotal = 0.0;
	while ($row = $items->fetch_assoc()) {
		$cases = (int) $row['Mcs'];
		$isGrams = strtoupper(trim((string) $row['Uom'])) === 'GRAMS';
		$kg = $cases * (float) $row['TotalSlabs'] * (float) $row['SlabWeight']
			* (float) $row['MFactor'];
		$lbFactor = $isGrams
			? (is_numeric($CanadaLbsFactor)
				? (float) $row['MFactor'] * (float) $CanadaLbsFactor
				: (float) $row['LbsFactor'])
			: (float) $row['LbsFactor'];
		$lbs = $cases * (float) $row['TotalSlabs'] * (float) $row['SlabWeight'] * $lbFactor;
		$sourceRate = $rate($row['Price']);
		$sourceWeight = $sourceIsKgs ? $kg : $lbs;
		$displayWeight = $isKgs ? $kg : $lbs;
		$amount = $sourceWeight * $sourceRate;
		$unitRate = $displayWeight > 0 ? $amount / $displayWeight : 0;
		$row['_cases'] = $cases;
		$row['_kg'] = $kg;
		$row['_lbs'] = $lbs;
		$row['_displayWeight'] = $displayWeight;
		$row['_rate'] = $unitRate;
		$row['_amount'] = $amount;
		$rows[] = $row;
		$totalCases += $cases;
		$totalKg += $kg;
		$totalLb += $lbs;
		$itemTotal += $amount;
		if (!empty($row['Remarks'])) {
			$specParts[] = trim((string) $row['Remarks']);
		}
	}

	$displayTotal = $isKgs ? $totalKg : $totalLb;

	$isBeaverStreetConsignee = isSaimeBeaverStreetConsignee(isset($data['ConsigneeID']) ? $data['ConsigneeID'] : 0);
	$productSpecs = '';
	if ($isBeaverStreetConsignee) {
		$stmtRemarks = $iconn->prepare('SELECT Remarks FROM ScrmPoData WHERE PoID = ?');
		$stmtRemarks->bind_param('i', $PoID);
		$stmtRemarks->execute();
		$remarksRow = $stmtRemarks->get_result()->fetch_assoc();
		$defaultRemarks = $remarksRow && !empty($remarksRow['Remarks']) ? $remarksRow['Remarks'] : '';
		$specItems = array();
		foreach ($rows as $row) {
			$specItemCode = trim((string) $row['BreakupBuyerItemCode']);
			$specItems[] = array(
				'itemCode' => $specItemCode,
				'description' => !empty($row['Description']) ? $row['Description'] : $row['Variety'],
				'size' => $row['Grade'],
				'packing' => $row['PackingStyle'],
				'brand' => $row['Brand'],
				'uom' => $row['Uom'],
				'slabWeight' => $row['SlabWeight'],
				'totalSlabs' => $row['TotalSlabs'],
				'certificationName' => $data['CerificationName'],
				'remarks' => isset($row['Remarks']) ? $row['Remarks'] : ''
			);
		}
		$productSpecs = buildSaimeProductSpecsFromBreakup($specItems, $defaultRemarks);
	}

	$invoiceValue = !empty($payment['InvValueUSD']) ? (float) $payment['InvValueUSD'] : $itemTotal;
	// Keep a saved freight charge visible even when legacy PO visibility flags
	// are inconsistent, and support both legacy and corrected field spellings.
	$freight = isset($payment['FrieghtCharge'])
		? (float) $payment['FrieghtCharge']
		: (isset($payment['FreightCharge']) ? (float) $payment['FreightCharge'] : 0.0);
	$showFreight = !empty($data['ShowFrightCharge']) || $freight != 0.0;
	if (!$showFreight) {
		$freight = 0.0;
	}
	$subTotal = $showFreight && !empty($payment['InvValueUSDWithOutDuties'])
		? (float) $payment['InvValueUSDWithOutDuties']
		: $invoiceValue - $freight;
	$destinationCountryId = isset($data['DestinationCountryId']) ? (int) $data['DestinationCountryId'] : 0;
	$destinationCountryName = strtoupper(trim((string) $data['DestinationCountry']));
	$isUsaDestination = $destinationCountryId === 157 || in_array($destinationCountryName, array('USA', 'U.S.A.', 'UNITED STATES', 'UNITED STATES OF AMERICA'), true);
	$duties = $isUsaDestination ? (float) $payment['DutiesPrice'] : 0.0;
	$fobUsd = $isUsaDestination && !empty($payment['FOBValueUSD']) ? (float) $payment['FOBValueUSD'] : $subTotal - $duties;
	$fobInr = (float) $payment['FOBValueINR'];
	$exchangeRate = (float) $payment['ExchangeRate'];
	$antiDumpingDutyPerc = (float) $payment['AntiDumpingDutyPerc'];
	$merchandiseProcessFeePerc = (float) $payment['MerchandiseProcessFeePerc'];
	$harbourMaintenanceFeePerc = (float) $payment['HarbourMaintenanceFeePerc'];
	$countervailingDutyPerc = (float) $payment['CountervailingDutyPerc'];
	$reciprocalTariffsPerc = (float) $payment['ReciprocalTariffsPerc'];
	$tariffPerc = (float) $payment['TariffPerc'];
	$totalDutyPerc = $antiDumpingDutyPerc + $merchandiseProcessFeePerc + $harbourMaintenanceFeePerc
		+ $countervailingDutyPerc + $reciprocalTariffsPerc + $tariffPerc;
	if ($isKgs) {
		$grossKg = $totalKg + 3000.0;
		$grossLb = $grossKg * 2.2046226218;
	} else {
		// LBS invoices add 1 LB of packaging for each master carton.
		$grossLb = $totalLb + $totalCases;
		$grossKg = $totalKg + 3000.0;
	}
	// Keep the calculated packaging allowance instead of overriding it with saved gross weight.

	$finalDestination = strtoupper((string) $data['FinalDestination']);
	$referenceNoHtml = $referenceNo !== '' ? '<br><b>' . $e($referenceNo) . '</b>' : '';
	$shippingTerms = trim((string) $data['ShippingTerms'] . ' ' . $finalDestination);
	$processorCode = $data['ProcessorApprovalNo'];
	$invoiceWords = strtoupper(numberTowords($invoiceValue));
	$invoiceTimestamp = strtotime((string) $data['InvDate']);
	$lutRef = SaimeReportData::lutReference($iconn, $data['ExporterID'], 'LUT', date('Y-m-d', $invoiceTimestamp ?: time()));
	$lutNumber = !empty($lutRef['ReferenceNumber']) ? trim((string) $lutRef['ReferenceNumber']) : trim((string) $data['LutNumber']);
	$invoiceYear = $invoiceTimestamp ? (int) date('Y', $invoiceTimestamp) : (int) date('Y');
	$invoiceMonth = $invoiceTimestamp ? (int) date('n', $invoiceTimestamp) : (int) date('n');
	$financialYearStart = $invoiceMonth >= 4 ? $invoiceYear : $invoiceYear - 1;
	$financialYear = $financialYearStart . '-' . substr((string) ($financialYearStart + 1), -2);
	$exportSupplyText = $data['IGST'] === 'LUT'
		? 'SUPPLY MEANT FOR EXPORT UNDER LUT WITHOUT PAYMENT OF IGST'
		: ($data['IGST'] === 'IGST' ? 'SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST' : '');

	$notifyDetails = '<b>' . ($data['NotifyPartyName'] !== '' ? 'C/O. ' . $e(strtoupper(rtrim($data['NotifyPartyName'], ','))) . ',' : '') . '</b>';
	if (!empty($data['NotifyPartyAddress']))
		$notifyDetails .= '<br><b>' . $nl(strtoupper($data['NotifyPartyAddress'])) . '</b>';
	if (!empty($data['NPartyContactNumber']))
		$notifyDetails .= '<br><b>TEL: ' . $e($data['NPartyContactNumber']) . '</b>';
	if (!empty($data['NPartyFaxNo']))
		$notifyDetails .= '<br><b>FAX: ' . $e($data['NPartyFaxNo']) . '</b>';

	$markedNotifyPartyHtml = '<span class="label">Consignee :</span><br><b>' . $e(strtoupper($data['ConsigneeName'])) . '</b><br><b>' . $nl(strtoupper($data['ConsigneeAddress'])) . '</b>';

	$html = '<style>
        table { border-collapse:collapse; font-family:helvetica; font-size:6.5pt; color:#000; }
        td, th { padding:1px 2px; line-height:1.08; }
        .label { font-size:5.9pt; }
        .amount { font-size:6.5pt; }
        .total-row { font-size:6.9pt; }
        .weight-row { font-size:7.4pt; }
        .scheme-text { font-family:dejavusanscondensed; font-size:5pt; line-height:1.04; }
        .processor-text { font-size:5.6pt; }
        .signature-text { font-size:5.5pt; }
    </style>';
	$html .= '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
	$html .= '<tr><th colspan="10" align="center" style="font-size:11pt;height:18px"><b>COMMERCIAL INVOICE</b></th></tr>';
	if ($isKgs && $exportSupplyText !== '') {
		$html .= '<tr><th colspan="10" align="center" style="font-size:6.2pt;height:13px"><b>' . $e($exportSupplyText) . '</b></th></tr>';
	}
	$html .= '<tr><td colspan="5" rowspan="3" style="height:70px"><span class="label"><b>EXPORTER:</b></span><br><b>' . $e(strtoupper($data['ExporterName'])) . '</b><br><b>' . $nl(strtoupper($data['ExporterAddress'])) . '</b><br><br><span class="label"><b>PROCESSED &amp; PACKED BY:</b></span><br><b>' . $e(strtoupper($data['ProcessorName'])) . '</b><br><b>' . $nl(strtoupper($data['ProcessorAddress'])) . '</b></td>';
	$html .= '<td colspan="5" style="height:21px"><span class="label">INVOICE No. &amp; Date :</span><br><b>' . $e($data['InvNo']) . ' DT: ' . $e($data['InvDate']) . '</b></td></tr>';
	$html .= '<tr><td colspan="5" style="height:21px"><span class="label">Buyer\'s order no &amp; date</span><br><b>PO :' . $e($data['PoNo']) . ($data['PoDate'] !== '' ? ' DT: ' . $e($data['PoDate']) : '') . '</b></td></tr>';
	$html .= '<tr><td colspan="5" style="height:28px"><span class="label">Other Reference(s) :</span><br><b>PROCESSOR CODE: ' . $e($processorCode) . '<br>IE CODE: ' . $e($data['ExporterIeCode']) . '<br>USFDA BIOTERRORISM REGN NO: ' . $e($data['ProcessorUsfdaNo']) . '</b></td></tr>';
	$html .= '<tr><td colspan="5" rowspan="2" cellpadding="0" style="height:92px"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td><b>IMPORTER OF RECORD</b><br><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="62%"><b>' . $e(strtoupper($data['ImporterName'])) . '</b></td><td width="38%" align="left"><b>' . (!empty($data['ImporterCode']) ? 'IRS NO. ' . $e($data['ImporterCode']) : '') . '</b></td></tr></table><b>' . $nl(strtoupper($data['ImporterAddress'])) . '</b><br><b>NOTIFY PARTY:</b><br>' . $notifyDetails . '</td></tr></table></td>';
	$html .= '<td colspan="5" style="height:70px">' . $markedNotifyPartyHtml . '</td></tr>';
	$html .= '<tr><td colspan="5" cellpadding="0" style="height:22px"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td width="50%" align="center" style="border-right:1px solid #000"><span class="label">Country of Origin of Goods :</span><br><b>' . saimeReportHtml($iconn, $PoID, 'CountryOfOrigin', 'INDIA') . '</b></td><td width="50%" align="center"><span class="label">Country of Final Destination :</span><br><b>' . $e(strtoupper($data['DestinationCountry'])) . '</b></td></tr></table></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">Pre- Carriage by :</span><br><b>CONTAINER</b></td><td colspan="3"><span class="label">Place of Receipt by :</span><br><b>' . $e(strtoupper($data['PlaceofReceipt'])) . '</b></td><td colspan="5" rowspan="3"><span class="label">Terms of Delivery &amp; Payment :</span><br><b>' . $nl(strtoupper($data['PaymentTerms'])) . '</b><br><b>SHIPPING TERM: ' . $e(strtoupper($data['ShippingTerms'])) . ' ' . $e($finalDestination) . '</b><br><b>TRANSPORT TERMS: ' . $e(strtoupper($data['DeliveryTerms'])) . '</b>' . $referenceNoHtml . '</td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">Vessel/ Voyage No.</span><br><b>' . $e(strtoupper($data['VesselNumber'])) . '</b></td><td colspan="3"><span class="label">Port of Loading :</span><br><b>' . $e(strtoupper($data['PortOfLoading'])) . '</b></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">Port of Discharge :</span><br><b>' . $e(strtoupper($data['PortOfDischarge'])) . '</b></td><td colspan="3"><span class="label">Final Destination :</span><br><b>' . $e($finalDestination) . '</b></td></tr>';
	$html .= '<tr><th width="9%">Marks &amp; Nos.<br>Container No.</th><th width="8%">No of Kind<br>Packages</th><th width="30%" colspan="2">Description of Goods</th><th width="8%">ITEM<br>NO.</th><th width="7%">SIZE</th><th width="7%">No. Of<br>Cases</th><th width="10%">Total Wt.<br>(' . $weightUnit . ')</th><th width="8%">Rate/' . $rateUnit . '<br>' . saimeReportHtml($iconn, $PoID, 'UnitCurrency', 'USD') . '</th><th width="13%" align="center">AMOUNT<br>' . saimeReportHtml($iconn, $PoID, 'UnitCurrency', 'USD') . '</th></tr>';

	// Same product + packing style + brand is one group: the packages count
	// and description print once, spanning every grade row in that group, and
	// each grade below only adds its own item code, size, cases, weight, rate
	// and amount.
	$groupCases = array();
	$groupCounts = array();
	foreach ($rows as $row) {
		$groupKey = $row['Variety'] . '|' . $row['PackingStyleID'] . '|' . $row['Brand'];
		if (!isset($groupCases[$groupKey])) {
			$groupCases[$groupKey] = 0;
			$groupCounts[$groupKey] = 0;
		}
		$groupCases[$groupKey] += (int) $row['_cases'];
		$groupCounts[$groupKey]++;
	}

	// Prepare one display row per product. The common renderer keeps wrapped
	// descriptions aligned with item code, size, cases, weight, rate and amount.
	$breakupRows = array();
	$seenGroups = array();
	foreach ($rows as $index => $row) {
		$description = !empty($row['Description']) ? $row['Description'] : $row['Variety'];
		$itemCode = trim((string) $row['BreakupBuyerItemCode']);

		$groupKey = $row['Variety'] . '|' . $row['PackingStyleID'] . '|' . $row['Brand'];
		$isFirstInGroup = !isset($seenGroups[$groupKey]);
		$seenGroups[$groupKey] = true;

		$breakupRow = array(
			0 => $index === 0
				? '<b>CONTAINER NO &amp; SEAL NO<br>' . $e($data['ContainerNo']) . '<br>' . $e($data['SealNo']) . '</b>'
				: '',
			3 => '<b>' . $e($itemCode) . '</b>',
			4 => '<b>' . $e($row['Grade']) . '</b>',
			5 => '<b>' . number_format($row['_cases']) . '</b>',
			6 => '<b>' . number_format($row['_displayWeight'], 3) . '</b>',
			7 => '<b>' . rtrim(rtrim(number_format($row['_rate'], 5), '0'), '.') . '</b>',
			8 => '<b>' . number_format($row['_amount'], 2) . '</b>'
		);
		if ($isFirstInGroup) {
			$descriptionHtml = '<b>' . number_format($groupCases[$groupKey]) . ' M/CARTONS OF ' . $nl($description)
				. '<br>PACK: ' . $e($row['PackingStyle']) . '<br>BRAND: ' . $e($row['Brand'])
				. (trim((string) $data['CerificationName']) !== '' ? ' (' . $e(formatSaimeCertificationName($data['CerificationName'])) . ')' : '')
				. ($isKgs && $data['CInvCode'] !== '' ? '<br>HS CODE: ' . $e($data['CInvCode']) : '') . '</b>';
			$breakupRow[1] = array('html' => '<b>' . number_format($groupCases[$groupKey]) . '<br>M/CARTONS</b>', 'rowspan' => $groupCounts[$groupKey]);
			$breakupRow[2] = array('html' => $descriptionHtml, 'rowspan' => $groupCounts[$groupKey]);
		}

		$breakupRows[] = $breakupRow;
	}
	$financialHtml = '<table border="0" cellpadding="1" cellspacing="0" width="100%">';
	$financialHtml .= '<tr><td width="58%"><b>INVOICE VALUE</b></td><td width="8%" align="right"><b>' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . '</b></td><td width="34%" align="right"><b>' . number_format($invoiceValue, 2) . '</b></td></tr>';
	if ($freight != 0) {
		$financialHtml .= '<tr><td><b>LESS: FREIGHT PREPAID</b></td><td align="right"><b>' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . '</b></td><td align="right" style="border-bottom:1px solid #000"><b>' . number_format($freight, 2) . '</b></td></tr>';
	}
	$financialHtml .= '<tr><td></td><td></td><td align="right"><b>' . number_format($subTotal, 2) . '</b></td></tr>';
	if ($duties != 0) {
		$dutyLabels = array();
		if ($antiDumpingDutyPerc != 0)
			$dutyLabels[] = 'ANTIDUMPING DUTY @ ' . rtrim(rtrim(number_format($antiDumpingDutyPerc, 4), '0'), '.') . '% &amp;';
		if ($merchandiseProcessFeePerc != 0 || $harbourMaintenanceFeePerc != 0) {
			$mpfHarbourPerc = $merchandiseProcessFeePerc + $harbourMaintenanceFeePerc;
			$dutyLabels[] = 'MPF &amp; HARBOUR MAINTAINCE ' . rtrim(rtrim(number_format($mpfHarbourPerc, 4), '0'), '.') . '%';
		}
		if ($countervailingDutyPerc != 0)
			$dutyLabels[] = 'CVD @ ' . rtrim(rtrim(number_format($countervailingDutyPerc, 4), '0'), '.');
		if ($reciprocalTariffsPerc != 0)
			$dutyLabels[] = 'RECIPROCAL TARIFF @ ' . rtrim(rtrim(number_format($reciprocalTariffsPerc, 4), '0'), '.') . '%';
		if ($tariffPerc != 0)
			$dutyLabels[] = 'TARIFF @ ' . rtrim(rtrim(number_format($tariffPerc, 4), '0'), '.') . '%';
		$financialHtml .= '<tr><td colspan="3"><br><b>' . implode('<br>', $dutyLabels) . '</b></td></tr>';
		$financialHtml .= '<tr><td><b>(' . number_format($subTotal, 2, '.', '') . 'X' . rtrim(rtrim(number_format($totalDutyPerc, 4), '0'), '.') . '/' . rtrim(rtrim(number_format(100 + $totalDutyPerc, 4), '0'), '.') . ')</b></td><td align="right"><b>' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . '</b></td><td align="right"><b>' . number_format($duties, 2) . '</b></td></tr>';
	}
	$financialHtml .= '<tr><td><br><b>FOB VALUE</b></td><td align="right"><br><b>' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . '</b></td><td align="right" style="border-top:1px solid #000"><br><b>' . number_format($fobUsd, 2) . '</b></td></tr>';
	$financialHtml .= '<tr><td><b>FOB VALUE IN RS.</b></td><td></td><td align="right"><b>' . number_format($fobInr, 2) . '</b></td></tr>';
	if ($exchangeRate > 0)
		$financialHtml .= '<tr><td colspan="3"><b>(EXCHANGE RATE (' . number_format($exchangeRate, 2) . '))</b></td></tr>';
	$financialHtml .= '</table>';

	$breakupColumns = array(
		array('width' => '9%', 'align' => 'center'),
		array('width' => '8%', 'align' => 'center'),
		array('width' => '30%', 'borderAfter' => true),
		array('width' => '8%', 'align' => 'center', 'borderAfter' => true),
		array('width' => '7%', 'align' => 'center', 'borderAfter' => true),
		array('width' => '7%', 'align' => 'center', 'borderAfter' => true),
		array('width' => '10%', 'align' => 'center', 'borderAfter' => true),
		array('width' => '8%', 'align' => 'center', 'borderAfter' => true),
		array('width' => '13%', 'align' => 'center')
	);

	$html .= '<tr><td colspan="10" cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%">';
	$html .= '<tr><td width="47%" style="height:26px;border-right:1px solid #000"></td>';
	$html .= '<td width="8%" style="height:26px;border-right:1px solid #000"></td><td width="7%" style="height:26px;border-right:1px solid #000"></td><td width="7%" style="height:26px;border-right:1px solid #000"></td><td width="10%" style="height:26px;border-right:1px solid #000"></td><td width="8%" style="height:26px;border-right:1px solid #000"></td>';
	$html .= '<td width="13%" align="center" style="height:26px;border-bottom:1px solid #000"><b><u>' . $e(strtoupper($data['ShippingTerms'])) . '</u></b><br><b>' . $e($finalDestination) . '</b></td></tr>';
	$html .= renderSaimeBreakupRows($breakupRows, $breakupColumns, 82);

	if ($productSpecs !== '') {
		$html .= '<tr><td colspan="3" width="47%" style="height:93px;border-top:1px solid #000;border-right:1px solid #000"><b>PRODUCT SPECS:</b> ' . $nl($productSpecs) . '</td>';
		$html .= '<td width="8%" style="height:93px;border-right:1px solid #000"></td><td width="7%" style="height:93px;border-right:1px solid #000"></td><td width="7%" style="height:93px;border-right:1px solid #000"></td><td width="10%" style="height:93px;border-right:1px solid #000"></td><td width="8%" style="height:93px;border-right:1px solid #000"></td><td width="13%" style="height:93px"></td></tr>';
	}

	$html .= '<tr><td colspan="3" width="47%" cellpadding="0" style="height:168px;border-top:1px solid #000;border-right:1px solid #000"><table border="0" cellpadding="2" cellspacing="0" width="100%">';
	$html .= '<tr><td width="40%"></td><td width="27%" align="right"><b>KGS</b></td><td width="33%" align="right"><b>LBS</b></td></tr>';
	$html .= '<tr><td width="40%"><b>NET WEIGHT:</b></td><td width="27%" align="right"><b>' . number_format($totalKg, 3) . '</b></td><td width="33%" align="right"><b>' . number_format($totalLb, 3) . '</b></td></tr>';
	$html .= '<tr><td width="40%"><b>GROSS WEIGHT:</b></td><td width="27%" align="right"><b>' . number_format($grossKg, 3) . '</b></td><td width="33%" align="right"><b>' . number_format($grossLb, 3) . '</b></td></tr>';
	$html .= '<tr><td colspan="3" cellpadding="0"><br>' . $financialHtml . '</td></tr></table></td>';
	// Keep financial details in their own row. Totals are printed in the next,
	// actual final row so TCPDF cannot move them upward.
	$html .= '<td width="8%" style="height:168px;border-right:1px solid #000"></td><td width="7%" style="height:168px;border-right:1px solid #000"></td>';
	$html .= '<td width="7%" style="height:168px;border-right:1px solid #000"></td><td width="10%" style="height:168px;border-right:1px solid #000"></td><td width="8%" style="height:168px;border-right:1px solid #000"></td><td width="13%" style="height:168px"></td></tr>';
	$html .= '<tr><td width="47%"></td><td width="8%" style="border-right:1px solid #000"></td><td width="7%" style="border-right:1px solid #000"></td>';
	$html .= '<td width="7%" align="center" style="border-top:1px solid #000;border-right:1px solid #000"><b>' . number_format($totalCases) . '</b></td>';
	$html .= '<td width="10%" align="center" style="border-top:1px solid #000;border-right:1px solid #000"><b>' . number_format($displayTotal, 3) . '</b></td>';
	$html .= '<td width="8%" style="border-right:1px solid #000"></td>';
	$html .= '<td width="13%" align="center" style="border-top:1px solid #000"><b>' . number_format($invoiceValue, 2) . '</b></td></tr>';
	$html .= '</table></td></tr>';
	$html .= '<tr><td colspan="10"><b>' . strtoupper(saimeReportHtml($iconn, $PoID, 'CurrencyName', 'US DOLLARS')) . ' ' . $e($invoiceWords) . ' ONLY</b></td></tr>';
	$html .= '<tr nobr="true"><td colspan="10"><b>PLEASE REMIT ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($invoiceValue, 2) . '</b><br>WE CERTIFY THAT ALL GOODS MENTIONED ON THIS INVOICE HAS NOT BEEN REFUSED BY ANY COUNTRY PRIOR TO SHIPMENT TO THE U.S.</td></tr>';
	if (!empty($bank)) {
		$intermediateBank = trim((string) $bank['NegotiatingBank']);
		if ($intermediateBank === '') {
			$intermediateBank = trim((string) $bank['RoutingThrough']);
		}
		$intermediateBank = preg_replace('/(?:\r?\n)?ACCOUNT NO\s*:\s*[^\r\n]*$/i', '', $intermediateBank);
		$intermediateBankHtml = $intermediateBank !== '' ? $nl(strtoupper($intermediateBank)) : '';
		if (!empty($bank['ABA_Numnber'])) {
			$intermediateBankHtml .= ($intermediateBankHtml !== '' ? '<br>' : '') . '<b>ABA ROUTING NO. ' . $e($bank['ABA_Numnber']) . '</b>';
		}

		$forwardCreditParts = array();
		if (!empty($bank['BankName']))
			$forwardCreditParts[] = '<b>' . $e(strtoupper($bank['BankName'])) . '</b>';
		if (!empty($bank['BankAddress']))
			$forwardCreditParts[] = $nl(strtoupper($bank['BankAddress']));
		if (!empty($bank['AccountNumber']))
			$forwardCreditParts[] = '<b>ACCOUNT NO. ' . $e($bank['AccountNumber']) . '</b>';
		if (!empty($bank['SwiftCode']))
			$forwardCreditParts[] = '<b>SWIFT CODE: ' . $e($bank['SwiftCode']) . '</b>';
		if (!empty($bank['IFSC']))
			$forwardCreditParts[] = '<b>IFSC / CODE: ' . $e($bank['IFSC']) . '</b>';
		if (!empty($bank['MICR']))
			$forwardCreditParts[] = '<b>MICR: ' . $e($bank['MICR']) . '</b>';
		if (!empty($bank['ABA_Numnber']))
			$forwardCreditParts[] = '<b>ABA ROUTING NO. ' . $e($bank['ABA_Numnber']) . '</b>';
		$forwardCreditHtml = implode('<br>', $forwardCreditParts);

		$beneficiary = '<b>' . $e(strtoupper($data['ExporterName'])) . '</b><br>' . $nl(strtoupper($data['ExporterAddress']));
		$html .= '<tr nobr="true"><td colspan="10" cellpadding="0"><table border="0" cellpadding="3" cellspacing="0" width="100%"><tr nobr="true">';
		$html .= '<td width="28%" align="left" valign="top" style="border-right:1px solid #000;font-size:5.9pt;line-height:1.12"><b><u>INTERMEDIATE BANK:</u></b><br>' . $intermediateBankHtml . '</td>';
		$html .= '<td width="44%" align="left" valign="top" style="border-right:1px solid #000;font-size:5.9pt;line-height:1.12"><b><u>FORWARD CREDIT TO:</u></b><br>' . $forwardCreditHtml . '</td>';
		$html .= '<td width="28%" align="left" valign="top" style="font-size:5.9pt;line-height:1.12"><b><u>BENEFICIARY:</u></b><br>' . $beneficiary . '</td>';
		$html .= '</tr></table></td></tr>';
	}
	$html .= '<tr nobr="true"><td colspan="6" class="processor-text" style="height:72px;border:1px solid #000"><b>PROCESSED AND PACKED BY:</b><br><b>' . $e($data['ProcessorName']) . '</b><br>' . $nl($data['ProcessorAddress']) . '<br><b>Declaration:</b><br>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</td><td colspan="4" class="signature-text" align="center" valign="bottom" style="height:72px;border:1px solid #000">Signature &amp; date<br><b>For ' . $e($data['ExporterName']) . '</b><br><br><br><b>AUTHORISED SIGNATORY</b></td></tr>';
	if ($linerName !== '') {
		$html .= '<tr><td colspan="10" style="height:13px;border-top:1px solid #000"><b>LINER - ' . $e(strtoupper($linerName)) . '</b></td></tr>';
	}
	$html .= '</table>';

	return $html;
}


function getCustomInvData($iconn, $PoID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $DocType, $style, $AdminConfigDetails, $CanadaLbsFactor;

	$AryData = getReportData($iconn, $PoID);



	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];
	$LcStatus = $AryData['LcStatus'];
	$BLDate = $AryData['BLDate'];
	$SBDate = $AryData['SBDate'];
	$BLNo = $AryData['BLNo'];
	$SBNo = $AryData['SBNo'];
	$LcNumber = $AryData['LcNumber'];
	$LcDate = $AryData['LcDate'];
	$CIDescription = $AryData['CIDescription'];
	$BenfName = $AryData['BenfName'];
	$BenfAccNo = $AryData['BenfAccNo'];
	$BenfBankName = $AryData['BenfBankName'];
	$BenfBankAddress = $AryData['BenfBankAddress'];
	$BenfBankCode = $AryData['BenfBankCode'];
	$BenfIFSC = isset($AryData['BenfIFSC']) ? $AryData['BenfIFSC'] : '';
	$BenfMICR = isset($AryData['BenfMICR']) ? $AryData['BenfMICR'] : '';
	$AdvanceAmount = $AryData['AdvanceAmount'];
	$VesselNumber = $AryData['VesselNumber'];



	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];
	$PoNo = $AryData['PoNo'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$PlaceofReceipt = $AryData['PlaceofReceipt'];
	$UOM = $AryData['UOM'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];
	$TextSize = $AryData['TextSize'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$ShowExtraCharges = $AryData['ShowExtraCharges'];
	$ShowInvoicevalue = $AryData['ShowInvoicevalue'];

	$ShowBrand = $AryData['ShowBrand'];
	$ShowFrightCharge = $AryData['ShowFrightCharge'];
	$ShowInsurance = $AryData['ShowInsurance'];
	$ShippingTerms = $AryData['ShippingTerms'];
	$ProcessorIeCustomsNo = $AryData['ProcessorIeCustomsNo'];
	$ExporterRecAdd = $AryData['ExporterRecAdd'];
	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$DestinationCountry = $AryData['DestinationCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$QtyUnit = $AryData['QtyUnit'];
	$TotalMcs = $AryData['TotalMcs'];
	$IGST = $AryData['IGST'];
	$CInvCode = $AryData['CInvCode'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$NoOfDays = $AryData['NoOfDays'];


	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];
	$ShowContent = $AryData['ShowContent'];
	$ShowBankDetails = $AryData['ShowBankDetails'];
	$ShowPoNo = $AryData['ShowPoNo'];
	$showSignature = $AryData['showSignature'];



	$showProUsFda = $AryData['showProUsFda'];
	$ShowNoofDays = $AryData['ShowNoofDays'];
	$showProFEI = $AryData['showProFEI'];
	$PortOfLoading = $AryData['PortOfLoading'];
	$showAdvance = $AryData['showAdvance'];

	$ContainerNo = $AryData['ContainerNo'];
	$SealNo = $AryData['SealNo'];



	$showIgst = $AryData['showIgst'];
	$showDiscount = $AryData['showDiscount'];

	if (is_numeric($CanadaLbsFactor) && $CanadaLbsFactor > 0) {
		$AdminConfigDetails['CanadaLbsFactor'] = $CanadaLbsFactor;
	}
	$AryPaymentData = getPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetails);
	$InsuranceValue = $AryPaymentData['InsuranceValue'];

	//robo
	$NPID = $AryData['NPID'];
	$NotifyPartyName = $AryData['NotifyPartyName'];
	$NPartyAddress = $AryData['NPartyAddress'];
	$NPartyContactNumber = $AryData['NPartyContactNumber'];


	$ExporterRecAdd = $AryData['ImporterAddress'];
	$ImporterName = $AryData['ImporterName'];
	$ImporterCode = $AryData['ImporterCode'];

	$content = '';


	$size = 45;
	$size1 = 55;



	$StyleTbl = 'Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 10px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';

	$StyleTd = 'Style=" margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;"';

	$content .= '<style>table{border-collapse:collapse;font-family:helvetica;color:#000}th,td{padding:1px 2px;line-height:1.05}</style><table border="1" cellspacing="0" cellpadding="1" width="100%"><tr><th colspan="2" align="center" style="font-size:11pt"><b>COMMERCIAL INVOICE</b></th></tr>
				
				
				 <tr>  
				<th width="50%">
				<table border="1" cellspacing="0" cellpadding="2">';

	$content .= '
				<tr>
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '';

	if ($showExpUsFda) {
		$content .= '	<BR>USFDA REGISTRATION NO  : ' . $ExporterUsfdaNo . '<BR>';
	}

	if ($showExpIECode) {
		$content .= 'IE CODE  : ' . $ExporterIeCode;

	}

	if ($showExpGSTNo) {
		$content .= '<BR>GST NO  : ' . $ExporterGstNo;
	}


	$content .= '</font></th></tr>';


	/* $content .= '		<tr>  

																					   <th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
																					   <br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
																					   <br>
																					   </font></th>

																					   </tr>

																						'; */

	$BuyerLabelText = "Applicant";
	$consineeLabelText = '';
	if ($_SESSION['ClientID'] == 'SMEV3') {
		$BuyerLabelSql = "SELECT InvDate FROM ScrmPIData WHERE PoID=$PoID";
		$StmtBuyerLabel = $iconn->query($BuyerLabelSql);
		$RowBuyerLabel = $StmtBuyerLabel->fetch_assoc();
		$InvDate1 = $RowBuyerLabel['InvDate'];
		if (!empty($InvDate1) && $InvDate1 >= '2026-04-01') {
			$BuyerLabelText = "Buyer";
			$consineeLabelText = 'Ultimate ';
		}
	}
	if ($BuyerID == '' || $BuyerID == 0) { {
			$content .= '		<tr>  
						   
						<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '/Ultimate Consignee</u></b>
						<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '
						
						
						</font></th>
						</tr>';
		}
	} else if ($ConsigneeID != $BuyerID) {
		$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '</u></b><br>
				' . $BuyerName . ',<br>' . nl2br($BuyerAddress) . '<br>
				<b><u>' . $consineeLabelText . 'Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '
				
				</font></th>
				</tr>';
	} else {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '/Ultimate Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '
				
				
				</font></th>
				</tr>';
	}

	if ($NPID != 0) {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b> <u>Notify Party</u></b>
				<br>' . $NotifyPartyName . ',<br>' . nl2br($NPartyAddress) . '
				<!--<br>TEL:' . $NPartyContactNumber . '-->
				
				
				</font></th>
				</tr>';
	}


	$content .= '	<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">' . $PlaceofReceipt . '</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR align="center">  ' . $VesselNumber . ' 
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">' . $PortOfLoading . '</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">' . $PortOfDischarge . '</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">' . strtoupper($FinalDestination) . '</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="50%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				<br> ' . $InvNo . ',Dated ' . $InvDate . ' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Buyer`s Order No. & Date. </b>
				<br> ' . $PoNo . $PoDate . ' </font></th>
				</tr>';

	if ($ShippingTerms == 'FOB') {
		$FinalDestination = $PortOfLoading;
	}


	$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# ' . $ProcessorApprovalNo . '</b>
				<br>
				<u><b> Packer</u> :</b><br> ' . $ProcessorName . '<br> ' . $ProcessorAddress . ' ';

	if ($showProUsFda) {
		$content .= '<br> USFDA REGISTRATION NO  : ' . $ProcessorUsfdaNo;
	}

	if ($showProFEI) {
		$content .= '<BR> FEI # :  ' . $ProcessorFEI;
	}

	$content .= '</font></th></tr>';











	if ($ShowShippingTerms) {

		$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br>
				<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN # ' . $ImporterCode . '
				<br> ' . $ImporterName . '
				<br> ' . $ExporterRecAdd . ' </font></th>
				</tr>';
	} else {

		$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br></font></th>
				</tr>';
	}

	$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center">' . saimeReportHtml($iconn, $PoID, 'CountryOfOrigin', 'INDIA') . '</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  ' . $DestinationCountry . '</b></font></th>
				</tr>
				<tr> 
				<th  align="LEFT"  width="100%" ><font size="7"><b><u> Terms of Delivery and Payment:</u>
				</b><br><BR> ' . $PaymentTerms . ' 
				';
	/* if($ShowNoofDays){
		$content .= ' ' .$NoOfDays .'';
		}*/
	$content .= '<br>
				 SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($FinalDestination) . '';

	if ($ShowDeliveryTerms) {
		$content .= '(' . $DeliveryTerms . ')';
	}
	if ($LcStatus == 1) {
		$content .= '
				<br> D.C NUMBER : ' . $LcNumber . '  ,DATED : ' . $LcDate . '<br> BL NUMBER : ' . $BLNo . '    ,DATED :' . $BLDate . ' <br> SB NUMBER : ' . $SBNo . '  ,DATED : ' . $SBDate . ' ';
	} else {
		$content .= '
				<br> BL NUMBER : ' . $BLNo . '    ,DATED :' . $BLDate . ' <br> SB NUMBER : ' . $SBNo . '  ,DATED : ' . $SBDate . '';
	}

	$content .= '</font> </th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> 
				
				
				
				
				
				
				<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> ' . $TotalMcs . '</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br></font><font size="10"><b align="center">' . $ContainerNo . '</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br></font><font size="10"><b align="center">' . $SealNo . '</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in ' . $QtyUnit . '</font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>' . saimeReportHtml($iconn, $PoID, 'UnitCurrency', 'USD') . '/' . $QtyUnit . ' ' . $ShippingTerms . ' <br><b>' . strtoupper($FinalDestination) . '</b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in ' . saimeReportHtml($iconn, $PoID, 'UnitCurrency', 'USD') . ' <br>' . $ShippingTerms . '<br><b> ' . strtoupper($FinalDestination) . '</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>';


	$description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL ';
	if ($ShowPoNo) {
		$description_head .= '<BR>PO NO. ' . $PoNo . '<BR>';
	}

	$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
	$Whitspace = '<b style="color:white"> yuhju</b>';
	$FontSize = "7";
	//$TOTAL_MCS=0;
	$content .= '<tr >
				
			<table border="0" cellspacing="0" cellpadding="0">
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
		. $description_head . ' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			
			</tr>
			'
	;

	$SqlVarietyMcs = "
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,
			PackingStyleID,BAP,PrintStatus FROM 
			`ScrmCIGradeData` CIG
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			left join `ScrmBrandsMaster` SBM on  SBM.BrandName=CIG.Brand
			 WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {


		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];
		$BAP = $VarietyMcsRow['BAP'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];

		if ($BAP == 'NA') {
			$BAP = '';
		} else {
			$BAP = $BAP;
		}

		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
			. $VarietyMcsRow['VarietyMcs'] . ' CARTONS OF ' . $VarietyMcsRow['Variety'] . '<br>
			PACKING : ' . $VarietyMcsRow['PackingStyle'] . '  
			';


		if ($BrandPrintStatus) {
			$BrandName = '<B> BRAND </B>: ' . $VarietyMcsRow['Brand'] . ' ';
		} else {
			$BrandName = '';

		}
		$content .= 'NET WEIGHT PER CARTON.' . $Whitspace . $BrandName . ' <B><br>' . $BAP . '</B>';

		/* if($ShowBrand){
			$content.='NET WEIGHT PER CARTON.'.$Whitspace.'<B> BRAND </B>: '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
			}
			*/

		$content .= ' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>
			

			
			<tr>
			
			<th align="center" width="40%" ><font size="' . $FontSize . '"> <u>' . $TextSize . '</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';


		$SqlVariety = "SELECT * FROM `ScrmCIGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'
			and Variety='$Variety' and Brand='$Brand'  
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";
		$StmtVariety = $iconn->query($SqlVariety);

		while ($RowVariety = $StmtVariety->fetch_assoc()) {

			$AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['ID']);
			$Qty = $AryPriceBreakUp['Qty'];
			$UsdValue = $AryPriceBreakUp['UsdValue'];
			$TotalMcs = $AryPriceBreakUp['TotalMcs'];
			$Price = $AryPriceBreakUp['Price'];
			$UnitPrice = $AryPriceBreakUp['UnitPrice'];



			$content .= '
				<tr>
				<td align="center" width="40%"  ><font size="' . $FontSize . '">
				' . $RowVariety['Grade'] . '</font></td>
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . $TotalMcs . '</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($Qty, 3) . '	</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
					' . $UnitPrice . '</font></td>
				<td align="center" width="14.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($UsdValue, 2) . '</font></td>
				
				</tr>';
		}


	}




	$AryPriceData = getBreakUpData($iconn, $RowVariety['ID'], $PoID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];

	//var_dump($AryPriceData); exit;

	$content .= '
				<tr>
				
				<td align="center" width="40%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black;border-bottom: 1px solid black; border-right: 1px solid black;" ><font size="7">
				' . $TotalMcs . '</font></td>
				
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;"><font size="7">
				' . number_format($Qty, 3) . '</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;" >
				<font size="7">
				</font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;"><font size="7">
				' . number_format($UsdValue, 2) . '</font></td>
				
				
				</tr>';

	//$AryPaymentData=getPaymentValues($iconn,$PoID);
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$DiscountPerc = $AryPaymentData['DiscountPerc'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$IgstPerc = $AryPaymentData['IgstPerc'];
	$IgstValue = $AryPaymentData['IgstValue'];

	$InvValueUSD = $AryPaymentData['InvValueUSD'] - $AdvanceAmount;
	//$InvValueInr=$AryPaymentData['InvValueINR'];
	$InvValueInr = $InvValueUSD * $ExchangeRate;

	if ($showAdvance) {
		$content .= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;
                border-bottom: 1px solid black;"><font size="7"><B>
				LESS ADVANCE  : </B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black; 
				border-bottom: 1px solid black;"><font size="7"><B>
				' . number_format($AdvanceAmount, 2) . '</B></font></td>
				</tr><tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black; 
				border-bottom: 1px solid black;"><font size="7"><B>
				BALANCE RECEIVABLE AMOUNT IN ' . saimeReportHtml($iconn, $PoID, 'UnitCurrency', 'USD') . '  : </B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black; border-bottom: 1px solid black;"><font size="7"><B>
				' . number_format($InvValueUSD, 2) . '</B></font></td>
				</tr>';
	}

	if ($showDiscount) {
		$content .= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				LESS TRADE DISCOUNT ' . $DiscountPerc . '%</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				' . number_format($DiscountAmount, 2) . '</B></font></td>
				</tr><tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				TOTAL AMOUNT:</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;" ><font size="7"><B>
				' . number_format($InvValueUSD, 2) . '</B></font></td>
				</tr>';
	}




	$content .= '</table>
			  </tr>';

	$BILL_WORD = numberTowords($InvValueUSD);

	$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
	$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
	$DutiesPrice = $AryPaymentData['DutiesPrice'];
	$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
	$FOBValueINR = round($AryPaymentData['FOBValueINR']);



	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];
	$Freight = $AryData['Freight'];

	if ($ShowFrightCharge) {
		$DisplayFreight = '<BR>  OCEAN FREIGHT : <b>' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($Freight, 2) . '</b>';
	}


	if ($ShowExtraCharges) {
		$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
		$DiscountAmount = $AryPaymentData['DiscountAmount'];
		$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
		$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
		$DutiesPrice = $AryPaymentData['DutiesPrice'];
		$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
		$AntiDumpingDuty = round($AryPaymentData['AntiDumpingDuty'], 2);
		$MerchantDuty = round($AryPaymentData['MerchantDuty'], 2);
		$HarborDuty = round($AryPaymentData['HarborDuty'], 2);
		$FOBValueINR = round($AryPaymentData['FOBValueINR']);
		$InvValueUSD = $AryPaymentData['InvValueUSD'];
		$ISFStatic = $AryPaymentData['ISFStatic'];
		$AntiDumpingDutyPerc = $AryPaymentData['AntiDumpingDutyPerc'];
		$HarbourMaintenanceFeePerc = $AryPaymentData['HarbourMaintenanceFeePerc'];
		$CountervailingDutyPerc = $AryPaymentData['CountervailingDutyPerc'];
		$MerchandiseProcessFeePerc = $AryPaymentData['MerchandiseProcessFeePerc'];
		$CountervailingDuty = round($AryPaymentData['CountervailingDuty'], 2);
		$ReciprocalTariffsPerc = $AryPaymentData['ReciprocalTariffsPerc'];
		$ReciprocalTariffsFee = round($AryPaymentData['ReciprocalTariffsFee'], 2);
		$TariffPerc = $AryPaymentData['TariffPerc'];
		$TariffFee = round($AryPaymentData['TariffFee'], 2);
		$PrintTariff = $AryPaymentData['PrintTariff'];
		$PrintReciprocalTariff = $AryPaymentData['PrintReciprocalTariff'];

		$ShowInvoicevalue = true;

		$content .= $style;

		$content .= '
    <table border="1" cellspacing="0" cellpadding="2">
        <tr>
            <td width="58.5%">
                <table border="0" cellspacing="0" cellpadding="0">';


		if ($ShowFrightCharge) {
			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">INVOICE VALUE </td>
        <td align="left" width="10%"><b>: </b></td>
        <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($InvValueUSD, 2) . '</b></td>
    </tr>';

			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">LESS OCEAN FREIGHT </td>
        <td align="left" width="10%"><b>: </b></td>
        <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' <u>' . number_format($FrieghtCharge, 2) . '</u></b></td>
    </tr>';

			if ($ISFStatic) {
				$content .= '<tr>
            <td width="1%"></td>
            <td align="left" width="70%">(LESS : MERCHANDISE PROCESS FEE @ ' . htmlspecialchars((string) $MerchandiseProcessFeePerc, ENT_QUOTES, 'UTF-8') . '%) </td>
            <td align="left" width="10%"><b>: </b></td>
            <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($MerchantDuty, 2) . '</b></td>
        </tr>';
			}

			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">SUB TOTAL </td>
        <td align="left" width="10%"><b>: </b></td>
        <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($InvValueUSDWithOutDuties, 3) . '</b></td>
    </tr>';

			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">(LESS : ANTI-DUMPING DUTY @ ' . $AntiDumpingDutyPerc . '%)</td>
		<td align="left" width="10%"><b>: </b></td>
        <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($AntiDumpingDuty, 2) . '</b></td>
    </tr>';

			if ($CountervailingDutyPerc != 0) {
				$content .= '<tr>
            <td width="1%"></td>
            <td align="left" width="70%">(LESS : COUNTERVAILING DUTY @ ' . $CountervailingDutyPerc . '%) </td>
			<td align="left" width="10%"><b>: </b></td>
            <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($CountervailingDuty, 2) . '</b></td>
        </tr>';
			}

			if (!$ISFStatic) {
				$content .= '<tr>
            <td width="1%"></td>
            <td align="left" width="70%">(LESS : MERCHANDISE PROCESS FEE @ ' . $MerchandiseProcessFeePerc . '%) </td>
			<td align="left" width="10%"><b>: </b></td>
            <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($MerchantDuty, 2) . '</b></td>
        </tr>';
			}

			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">(LESS : HARBOR MAINTENANCE FEE @ ' . $HarbourMaintenanceFeePerc . '%) </td>
		<td align="left" width="10%"><b>: </b></td>
        <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($HarborDuty, 2) . '</b></td>
    </tr>';

			if ($PrintReciprocalTariff && $ReciprocalTariffsPerc != 0) {
				$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%">(LESS : RECIPROCAL TARIFFS @ ' . $ReciprocalTariffsPerc . '%) </td>
			<td align="left" width="10%"><b>: </b></td>
			<td align="left" width="20%" > <b>' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($ReciprocalTariffsFee, 2) . '</b></td>
			</tr>';
			}

			if ($PrintTariff && $TariffPerc != 0) {
				$content .= '<tr>
		<td width="1%"></td>
		<td align="left" width="70%">(LESS : TARIFF @ ' . $TariffPerc . '%) </td>
		<td align="left" width="10%"><b>: </b></td>
		<td align="left" width="20%"><b>' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($TariffFee, 2) . '</b></td>
		</tr>';
			}

			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">TOTAL DUTY </td>
		<td align="left" width="10%"><b>: </b></td>
        <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' <u>' . number_format($DutiesPrice, 2) . '</u></b></td>
    </tr>';

			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">FOB VALUE IN ' . saimeReportHtml($iconn, $PoID, 'UnitCurrency', 'USD') . ' </td>
		<td align="left" width="10%"><b>: </b></td>
        <td align="left" width="20%"><b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($FOBValueUSD, 2) . '</b></td>
    </tr>';
		} elseif ($ShowInvoicevalue) {
			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="35%">INVOICE VALUE ' . htmlspecialchars($UnitCurrency) . ' ' . htmlspecialchars($CurrenySymbol) . ' </td>
        <td align="left" width="15%"><b>: </b></td>
        <td align="left" width="60%"><b>' . number_format($InvValueUSD, 2) . '</b></td>
    </tr>';
		} else {
			$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="35%">FOB VALUE IN US $ </td>
        <td align="left" width="15%"><b>: </b></td>
        <td align="left" width="60%"><b>' . number_format($InvValueUSDWithOutDuties, 2) . '</b></td>
    </tr>';
		}
		$content .= '</table></td>';
		$content .= '<td width="41.5%">';
		$content .= '</td></tr></table>';
	}







	$content .= '<tr>
			<table border ="0" cellspacing="0" cellpadding="0">';
	$content .= '<tr>
			<td><font size="7"><br> SHIPPING TERMS   : ' . $ShippingTerms . '  ' . strtoupper($FinalDestination) . '
			<BR>' . nl2br($CIDescription) . ' <BR>  ITC HS CODE : <b>' . $CInvCode . '</b>
			' . $DisplayFreight . '
            </font></td>
			</tr>';
	if ($ShowBankDetails) {
		$content .= '<tr>
			<td width="100%"><font size="7"><br><B><U style="background-color:grey">OUR BANKING DETAILS:</U></B><br><br></font>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr><td width="23%"><font size="7">BENEFICIARY NAME</font></td><td width="2%"><font size="7">:</font></td><td width="75%"><font size="7"><b>' . $BenfName . '</b></font></td></tr>
				<tr><td width="23%"><font size="7">BENEFICIARY ACCOUNT NUMBER</font></td><td width="2%"><font size="7">:</font></td><td width="75%"><font size="7"><b>' . $BenfAccNo . '</b></font></td></tr>
				<tr><td width="23%"><font size="7">BANK NAME</font></td><td width="2%"><font size="7">:</font></td><td width="75%"><font size="7"><b>' . $BenfBankName . '</b></font></td></tr>
				<tr><td width="23%"><font size="7">BANK ADDRESS</font></td><td width="2%"><font size="7">:</font></td><td width="75%"><font size="7"><b>' . nl2br($BenfBankAddress) . '</b></font></td></tr>
				<tr><td width="23%"><font size="7">SWIFT CODE</font></td><td width="2%"><font size="7">:</font></td><td width="75%"><font size="7"><b>' . $BenfBankCode . '</b></font></td></tr>
				<tr><td width="23%"><font size="7">IFSC / CODE</font></td><td width="2%"><font size="7">:</font></td><td width="75%"><font size="7"><b>' . $BenfIFSC . '</b></font></td></tr>
				<tr><td width="23%"><font size="7">MICR</font></td><td width="2%"><font size="7">:</font></td><td width="75%"><font size="7"><b>' . $BenfMICR . '</b></font></td></tr>
			</table></td>
			</tr>';
	} else {

		$content .= '<tr> <br> 	</tr>';
	}





	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];


	/**uma 20-12-2023* */

	// $GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
// $GrossWeightLbs=$AryPriceData['GrossWeightLbs'];

	$TotalGrossWtKgs = $AryData['TotalGrossWtKgs'];

	if ($TotalGrossWtKgs != '') {
		if ($QtyUnit == 'KGS') {
			$GrossWeightKgs = $TotalGrossWtKgs;
			$GrossWeightLbs = $TotalGrossWtKgs * 2.204;
		} else {
			$GrossWeightKgs = $TotalGrossWtKgs * 0.454;
			$GrossWeightLbs = $TotalGrossWtKgs;
		}
	} else {
		$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
		$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];
	}

	$content .= '<tr>
		<td width="23%"></td>
		<td align="center" width="18%"><font size="7"><b>KGS</b></font></td>
		<td align="center" width="18%"><font size="7"><b>LBS</b></font></td>
		<td width="41%"></td>
		</tr>
		<tr>
		<td align="left" width="23%"><font size="7"><b>NET WEIGHT:</b></font></td>
		<td align="right" width="18%"><font size="7"><b>' . number_format($NetWgtKgs, 3) . '</b></font></td>
		<td align="right" width="18%"><font size="7"><b>' . number_format($NetWgtLbs, 3) . '</b></font></td>
		<td width="41%"></td>
		</tr>
		<tr>
		<td align="left" width="23%"><font size="7"><b>GROSS WEIGHT:</b></font></td>
		<td align="right" width="18%"><font size="7"><b>' . number_format($GrossWeightKgs, 3) . '</b></font></td>
		<td align="right" width="18%"><font size="7"><b>' . number_format($GrossWeightLbs, 3) . '</b></font></td>
		<td width="41%"></td>
		</tr>';

	$content .= '</table></tr>';


	$content .= '<tr>';




	/*$ShipmentUnder="1) WE HEREBY DECLARE THAT WE SHALL CLAIM THE BENEFITS UNDER CUSTOMS NOTIFICATION NO. 07/2020 DATED: 28-01-2020. AS WELL AS CHAPTER 3 OF FTP AS APPLICABLE. FISH AND CRUSTACEANS, MOLLUSCS AND OTHER AQUATIC INVERTEBRATES
																		 <br>2) THIS SHIPMENT IS MADE UNDER DUTY DRAW BACK SCHEME AS WELL AS UNDER RoDTEP SCHEME.
																		 <br>3) ITC HS CODE <b>".$CInvCode."</b>,DRAW BACK SCHEME CODE:19, SL.NO. OF EXPORT PRODUCT :030601B, RATE OF ENTITLEMENT : 3% ON FOB OR Rs.23.6/KG CAP, IN FROZEN FORM OTHER THAN AFD
																		 ";

		IF ($IGST=='LUT'){
		$ShipmentUnder.="<br>4)SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF INTEGRATED TAX: LUT No. $LutNumber " ;
		}

			$Decleration="
																		 I/WE, IN REGARD TO MY/OUR CLAIM UNDER RoDTEP SCHEME MADE IN THIS SHIPPING BILL OR BILL OF EXPORT, HEREBY DECLARE THAT:
																		 <br>1. I/ WE UNDERTAKE TO ABIDE BY THE PROVISIONS, INCLUDING CONDITIONS, RESTRICTIONS, EXCLUSIONS AND TIME-LIMITS AS PROVIDED UNDER RoDTEP SCHEME, AND RELEVANT NOTIFICATIONS, REGULATIONS, ETC., AS AMENDED FROM TIME TO TIME.

																		 <br>2. ANY CLAIM MADE IN THIS SHIPPING BILL OR BILL OF EXPORT IS NOT WITH RESPECT TO ANY DUTIES OR TAXES OR LEVIES WHICH ARE EXEMPTED OR REMITTED OR CREDITED UNDER ANY OTHER MECHANISM OUTSIDE RoDTEP.

																		 <br>3. I/WE UNDERTAKE TO PRESERVE AND MAKE AVAILABLE RELEVANT DOCUMENTS RELATING TO THE EXPORTED GOODS FOR THE PURPOSES OF AUDIT IN THE MANNER AND FOR THE TIME PERIOD PRESCRIBED IN THE CUSTOMS AUDIT REGULATIONS, 2018
																					 ";*/
	$content .= '</tr>
				
						
	        
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> AMOUNT CHARGABLE IN US.$: 
				<b>' . $BILL_WORD . '</b><BR></font></th>    
				
				 
				
                
</tr>
<tr>
<th  align="bottom"  width="70%"><font size="8"><B><U> DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>';
	if ($showSignature) {

		$content .= '<th  align="center"  width="30%"><font size="7"> Signature:<br><br>
				<B> ' . $AuthSign . ',</B><BR><BR><BR>
				 
				<!--<img src="' . $SignURL . '" 
				 width="100" height="30"style="width: 100" align="center" align="middle"/>-->
								
				
				<p STYLE="text-align:center" > Authorised Signature   </p></font></th>';
	} else {

		$content .= '<th  align="center"  width="30%"><font size="7"> Signature:<br><br><br><br><br><br></font></th>';
	}
	$content .= '</tr>
				
				
				</table>';

	return $content;

}












$SqlPaymentTerms = "SELECT * FROM $TblScrmPoData as SPD 
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'";





function getReportData($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $DocType;
	/*$CustomInvoiceSql="SELECT *  
																			  FROM ScrmCustomInvoices as SCV 
																			  left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
																			  WHERE SCV.PiID='$PoID'"; */

	// $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
	// SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading  
	// FROM ScrmCustomInvoices as SCV 
	// left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
	// left JOIN ScrmShipmentDispatchDetails as SSDD on SSDD.PIID=SCV.PiID
	// left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
	// left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
	// left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
	// left JOIN ScrmCountriesList as SL on SL.id=SP.Country
	// left JOIN ScrmLogisticsDetails as SLD on SLD.PiID=SCV.PiID
	// left JOIN ScrmEIRBankNFieghtDetaails as SEB on SEB.PoRefNumber=SCV.PiID

	// WHERE SCV.PiID='$PoID'"; 

	$CustomInvData = SaimeReportData::commercialInvoice($iconn, $PoID);
	$IGST = $CustomInvData['IGST'];
	$OverSeasCommission = $CustomInvData['OverSeasCommission'];
	$CInvDate = $CustomInvData['CInvDate'];
	$CInvCode = $CustomInvData['CInvCode'];
	$BLDate = getFormatedDateNA($CustomInvData['BLDate']);
	$SBDate = getFormatedDateNA($CustomInvData['ShippingBillDate']);
	$BLNo = $CustomInvData['BLNo'];
	$SBNo = $CustomInvData['ShippingBillNo'];
	$LcNumber = $CustomInvData['LcNo'];
	$LcDate = getFormatedDateNA($CustomInvData['LcDate']);
	$CIDescription = $CustomInvData['ComDescriptionSpl'];
	$ComShippingTerms = $CustomInvData['ComShippingTerms'];
	$ComPaymentTerms = $CustomInvData['PaymentName'];
	$TotalGrossWt = $CustomInvData['TotalGrossWt'];


	$NoOfDays = $CustomInvData['NoOfDays'];

	// $SqlOrgVessel="SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 
	// FromTOPID='$LoadingPortName' and PIID='$PoID'";

	$SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 	PIID='$PoID' order by TspID asc LIMIT 1";
	//exit;
	$StmtOrgVessel = $iconn->query($SqlOrgVessel);
	$RowOrgVessel = $StmtOrgVessel->fetch_assoc();

	$OrgVesselName = $RowOrgVessel['VesselName'];
	$OrgVOYNO = $RowOrgVessel['VOYNO'];

	$VesselNumber = $OrgVesselName . ' ' . $OrgVOYNO;


	if ($NoOfDays == 0) {
		$NoOfDays = '';

	} else {

		$NoOfDays = $NoOfDays . ' DAYS FROM BL';
	}







	$SqlMcsTotal = "SELECT sum(Mcs) as Mcs FROM `ScrmCIGradeData` where InvId='$PoID'  ";
	$StmtMcsTotal = $iconn->query($SqlMcsTotal);
	$RowMcs = $StmtMcsTotal->fetch_assoc();
	$TotalMcs = $RowMcs['Mcs'];




	$SqlScrmPoData = "SELECT *,SCDB.BuyerName as BuyerName,
 SCDB.BuyerAddress as BuyerAddress,SPD.Status as PoStatus,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,

ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,
ABD.SwiftCode as SwiftCode,
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode,
SOC.CerificationName as CerificationName

	FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId 
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country 
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
 left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
LEFT JOIN ScrmOrderCertification as SOC on SOC.CerificationID =SPD.Certification

 WHERE PoID='$PoID'";


	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ApprovedBy = $PoDataRow['ApprovedBy'];
	$LcStatus = $PoDataRow['LcStatus'];
	$CountryID = $PoDataRow['DestinationCountry'];

	$SqlHsCode = "SELECT DISTINCT HSCode FROM ScrmPoGradeData as SPGD 
   left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
   left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType and SPM.SpeciesShortCode=SPVL.Species
   WHERE InvId='$PoID'";


	$StmtHsCode = $iconn->query($SqlHsCode);
	while ($RowHsCode = $StmtHsCode->fetch_assoc()) {
		$ArrayHsCode[] = $RowHsCode['HSCode'];
		$CInvCode = implode(',', $ArrayHsCode);
	}

	// $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
// $StmtLogin=$iconn->query($SqlLogin); 
// $RowLogin = $StmtLogin->fetch_assoc();
// $SignURL=$RowLogin['SignURL'];

	// $ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
// $ProcessorData=getExporterDetails($ProcessorID,$PoID,$CountryID,$DocType);

	$ExporterData = getExporterDetails($ExpID, $CountryID, $DocType);
	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);


	if ($CustomInvData['BankID'] == 0) {
		$BankID = $PoDataRow['BankID'];
	} else {
		$BankID = $CustomInvData['BankID'];
	}
	$ShowBank = $CustomInvData['ShowBank'];
	// $SqlBank="SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
	$SqlBank = "SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$RowBank = $StmtBank->fetch_assoc();
	$BenfName = $RowBank['AccntName'];
	$BenfAccNo = $RowBank['AccountNumber'];
	$BenfBankName = $RowBank['BankName'];
	$BenfBankAddress = $RowBank['BankAddress'];
	$BenfBankCode = $RowBank['SwiftCode'];
	$BenfIFSC = isset($RowBank['IFSC']) ? $RowBank['IFSC'] : '';
	$BenfMICR = isset($RowBank['MICR']) ? $RowBank['MICR'] : '';


	if ($CustomInvData['Discount'] != 0 || $CustomInvData['IGST'] == 'DISCOUNT') {
		$AryData['showDiscount'] = true;
	} else {
		$AryData['showDiscount'] = false;
	}

	/*if($CustomInvData['IGST']=='DISCOUNT'){
		$AryData['showDiscount']=true; 
		}else{
		$AryData['showDiscount']=false; 
		}
		*/


	if ($CustomInvData['IGST'] == 'IGST') {
		$AryData['showIgst'] = true;
	} else {
		$AryData['showIgst'] = false;
	}

	$AryData['ShowExtraCharges'] = false;
	if ($PoDataRow['ShippingTerms'] == 'DDP') {
		$AryData['ShowShippingTerms'] = true; //uma
		if ($PoDataRow['DestinationCountry'] == 157 || $PoDataRow['DestinationCountry'] == 124) {
			$AryData['ShowExtraCharges'] = true;
		}
	} else {
		$AryData['ShowShippingTerms'] = false;
	}

	//if($PoDataRow['DestinationCountry']!=26 && $BankID!=5){
	if ($ShowBank != 0) {
		$AryData['ShowBankDetails'] = true; //uma
	} else {
		$AryData['ShowBankDetails'] = false;
	}

	//if($PoDataRow['DestinationCountry']!=26 && $BankID==5){
	if ($ShowBank == 0) {
		$AryData['ShowContent'] = true; //uma
	} else {
		$AryData['ShowContent'] = false;
	}

	if ($PoDataRow['DestinationCountry'] != 26) {
		$AryData['ShowPoNo'] = true; //uma
	} else {
		$AryData['ShowPoNo'] = false;
	}

	if ($PoDataRow['ShippingTerms'] == 'FOB') {
		$AryData['ShowInvoicevalue'] = true; //uma
	} else {
		$AryData['ShowInvoicevalue'] = false;
	}


	if ($PoDataRow['DestinationCountry'] != 26) {
		//if($PoDataRow['DestinationCountry']!=26 && $PoDataRow['ShippingTerms']!='FOB'){ //uma
		$AryData['ShowDeliveryTerms'] = true;
	} else {
		$AryData['ShowDeliveryTerms'] = false;
	}


	if ($PoDataRow['DestinationCountry'] == 157 || $PoDataRow['DestinationCountry'] == 124) {
		$AryData['showExpUsFda'] = true;
	} else {
		$AryData['showExpUsFda'] = false;
	}
	if ($PoDataRow['DestinationCountry'] != 26) {
		$AryData['showExpIECode'] = true;
		$AryData['showExpGSTNo'] = true;
	} else {
		$AryData['showExpIECode'] = false;
		$AryData['showExpGSTNo'] = false;
	}


	if ($PoDataRow['DestinationCountry'] != 26) {
		$AryData['showSignature'] = true;
	} else {
		$AryData['showSignature'] = false;
	}

	if ($PoDataRow['DestinationCountry'] != 26) {
		$AryData['showProFEI'] = true;
	} else {
		$AryData['showProFEI'] = false;
	}

	if ($PoDataRow['ShowBrand'] == 1) {
		$AryData['ShowBrand'] = true;
	} else {
		$AryData['ShowBrand'] = false;
	}
	if ($PoDataRow['ShowFrightCharge'] == 1 && $PoDataRow['ShowFrieght'] == 1) {
		$AryData['ShowFrightCharge'] = true;
	} else {
		$AryData['ShowFrightCharge'] = false;
	}

	if ($PoDataRow['Insurance'] != 0) {
		$AryData['ShowInsurance'] = true;
	} else {
		$AryData['ShowInsurance'] = false;
	}

	if ($PoDataRow['DestinationCountry'] == 157) {
		$AryData['showProUsFda'] = true;
	} else {
		$AryData['showProUsFda'] = false;
	}

	if ($CustomInvData['AdvanceAmount'] > 0) {
		$AryData['showAdvance'] = true;
	} else {
		$AryData['showAdvance'] = false;
	}

	if ($IGST != 'DISCOUNT') {
		$AryData['ShowNoofDays'] = true;
	} else {
		$AryData['ShowNoofDays'] = false;
	}

	if ($PoDataRow['PoNo'] == '') {
		$AryData['PoDate'] = '';
	} else {
		$AryData['PoDate'] = "& Dated :" . getFormatedDate($PoDataRow['PoDate']);
	}

	if ($PoDataRow['DestinationCountry'] == 26) {
		$UOM = 'KGS'; //uma
	} else {
		$UOM = 'LBS';
	}

	if ($PoDataRow['DestinationCountry'] == 26) {
		$AryData['TextSize'] = 'SIZE(PCS/LB)'; //uma
	} else {
		$AryData['TextSize'] = 'SIZE';
	}

	if ($ComPaymentTerms != '') {
		$PaymentTerms = $CustomInvData['PaymentName'];
	} else {
		$PaymentTerms = $PoDataRow['PaymentName'];
	}
	if ($ComShippingTerms != '') {
		$ShippingTerms = $CustomInvData['ComShippingTerms'];
	} else {
		$ShippingTerms = $PoDataRow['ShippingTerms'];
	}

	/* Return Data*/
	$AryData['PoStatus'] = $PoDataRow['PoStatus'];
	$AryData['TotalGrossWtKgs'] = $TotalGrossWt;
	$AryData['ApprovedBy'] = $ApprovedBy;
	$AryData['PaymentTerms'] = $PaymentTerms;
	$AryData['DeliveryTerms'] = $DeliveryTerms;
	$AryData['TotalMcs'] = $TotalMcs;
	$AryData['ExporterID'] = $ExpID;
	$AryData['LcStatus'] = $LcStatus;
	$AryData['BLDate'] = $BLDate;
	$AryData['SBDate'] = $SBDate;
	$AryData['BLNo'] = $BLNo;
	$AryData['SBNo'] = $SBNo;
	$AryData['LcNumber'] = $LcNumber;
	$AryData['LcDate'] = $LcDate;
	$AryData['CIDescription'] = $CIDescription;
	$AryData['BenfName'] = $BenfName;
	$AryData['BenfAccNo'] = $BenfAccNo;
	$AryData['BenfBankName'] = $BenfBankName;
	$AryData['BenfBankAddress'] = $BenfBankAddress;
	$AryData['BenfBankCode'] = $BenfBankCode;
	$AryData['BenfIFSC'] = $BenfIFSC;
	$AryData['BenfMICR'] = $BenfMICR;
	$AryData['AdvanceAmount'] = $CustomInvData['AdvanceAmount'];
	//robo 17-10-2023
	$AryData['NPID'] = $CustomInvData['NPID'];
	$AryData['NotifyPartyName'] = $CustomInvData['NotifyPartyName'];
	$AryData['NPartyAddress'] = $CustomInvData['NPartyAddress'];
	$AryData['NPartyContactNumber'] = $CustomInvData['NPartyContactNumber'];
	$AryData['NPartyFaxNo'] = $CustomInvData['NPartyFaxNo'];
	$AryData['Freight'] = $CustomInvData['Freight'];
	//robo

	$AryData['VesselNumber'] = $VesselNumber;


	$AryData['ExporterName'] = $ExporterData['ExpName'];
	;
	$AryData['ExporterAddress'] = $ExporterData['ExpAddress'];
	$AryData['ExporterUsfdaNo'] = $ExporterData['ExpUsfdaNo'];
	$AryData['ExporterIeCode'] = $ExporterData['ExpIeCode'];
	$AryData['ExporterGstNo'] = $ExporterData['ExpGstNo'];
	$AryData['DestinationCountryId'] = $PoDataRow['DestinationCountry'];
	$AryData['ExporterRecAdd'] = $ExporterData['ImporterRecordAddress'];
	$AryData['AuthSign'] = $ExporterData['AuthSign'];
	$AryData['LutNumber'] = $ExporterData['LutNumber'];



	$AryData['ConsigneeID'] = $PoDataRow['ConsigneeID'];
	$AryData['BuyerID'] = $PoDataRow['BuyerID'];
	$AryData['ConsigneeName'] = $PoDataRow['ConsigneeName'];
	$AryData['ConsigneeAddress'] = $PoDataRow['ConsigneeAddress'];
	$AryData['BuyerName'] = $PoDataRow['BuyerName'];
	$AryData['BuyerAddress'] = $PoDataRow['BuyerAddress'];
	$AryData['PoNo'] = $PoDataRow['PoNo'];
	$AryData['UOM'] = $UOM;



	$AryData['PortOfDischarge'] = strtoupper($CustomInvData['PortofDischarge']) . ' ' . strtoupper($CustomInvData['CountryCode']) . ', ' . $CustomInvData['PortofDischargeCountry'];
	//$AryData['PortOfDischarge']=strtoupper($CustomInvData['PortofDischarge']).', '.$CustomInvData['PortofDischargeCountry'];


	if ($PoDataRow['DestinationCountry'] == 26) {

		//$AryData['PlaceofReceiptBy']=str_ireplace("PORT"," ",$CustomInvData['PlaceOfRecipt']);
		$AryData['PlaceofReceipt'] = str_ireplace("PORT", " ", strtoupper($CustomInvData['PortOfLoading'])) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
		$AryData['PortOfLoading'] = str_ireplace("PORT", " ", strtoupper($CustomInvData['PortOfLoading'])) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	} else {
		$AryData['PlaceofReceipt'] = $CustomInvData['PlaceofReceipt'] != '' ? $CustomInvData['PlaceofReceipt'] : 'ANY PORT, INDIA';
		// $AryData['PlaceofReceipt'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
		$AryData['PortOfLoading'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	}


	$AryData['FinalDestination'] = $CustomInvData['FinalDestination'] . ' ' . $CustomInvData['FinalDestinationCountryCode'] . ' ,' . $CustomInvData['FinalDestinationCountry'];
	//$AryData['FinalDestination']=$CustomInvData['FinalDestination'].' ,'.$CustomInvData['FinalDestinationCountry'];


	// $AryData['InvNo']=getInvoiceNumber($iconn,$ExpID,$PoID) ;
	$AryData['InvNo'] = $CustomInvData['CustomsInvNo'];
	$AryData['InvDate'] = getFormatedDate($CInvDate);
	$AryData['PoNo'] = $PoDataRow['PoNo'];



	$AryData['ProcessorName'] = saimeReportValue($iconn, $PoID, 'ProcessorName', $ProcessorData['ExpName']);
	//$AryData['ProcessorAddress']=$ProcessorData['PackerAddress'];
	$AryData['ProcessorAddress'] = $ProcessorData['ExpAddress'];
	$AryData['ProcessorUsfdaNo'] = $ProcessorData['ExpUsfdaNo'];
	$AryData['ProcessorFEI'] = $ProcessorData['ExpFeiNo'];
	$AryData['ProcessorApprovalNo'] = $ProcessorData['ExpApprovalNo'];
	$AryData['ProcessorIeCustomsNo'] = $ProcessorData['ExpIeCustomsNo'];


	$AryData['ShippingTerms'] = $ShippingTerms;
	//$AryData['PaymentTerms']=$PoDataRow['PaymentName'];
	$AryData['DestinationCountry'] = $PoDataRow['name'];
	$AryData['DeliveryTerms'] = $PoDataRow['DeliveryTerms'];
	$AryData['QtyUnit'] = $PoDataRow['QtyUnit'];
	$AryData['ShipByDate'] = $PoDataRow['ShipByDate'];
	$AryData['SignURL'] = $SignURL;
	$AryData['PreparedBy'] = $PoDataRow['PreparedBy'];
	$AryData['BankID'] = $PoDataRow['BankID'];
	$AryData['IGST'] = $IGST;
	$AryData['CInvCode'] = $CInvCode;
	$AryData['NoOfDays'] = $NoOfDays;

	$AryData['ImporterAddress'] = saimeReportValue($iconn, $PoID, 'ImporterAddress', $PoDataRow['ImporterAddress']);
	$AryData['ImporterName'] = $PoDataRow['ImporterName'];
	$AryData['ImporterCode'] = $PoDataRow['ImporterCode'];

	$AryData['ContainerNo'] = $CustomInvData['ContainerNo'];
	$AryData['SealNo'] = $CustomInvData['LinerSealNo'];
	$AryData['CerificationName'] = $PoDataRow['CerificationName'];


	return $AryData;
}


function getBreakUpData($iconn, $GradeID, $PiID)
{
	global $CanadaLbsFactor;

	if ($PiID == '') {
		$Qry = "WHERE ID='$GradeID'";
	} else {
		$Qry = "WHERE InvId='$PiID'";
	}

	// $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
	// 			sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
	// 			sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
	// 			sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
			sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
			CASE WHEN PS.Uom = 'GRAMS' THEN
				sum(Mcs*TotalSlabs*SlabWeight*MFactor*$CanadaLbsFactor)
			ELSE
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor)
			END AS NetWgtLbs,
			CASE WHEN PS.Uom = 'GRAMS' THEN
				sum(Mcs*GrossWeight*MFactor*$CanadaLbsFactor)
			ELSE
				sum(Mcs*GrossWeight*LbsFactor)
			END AS GrossWeightLbs";

	// $SqlTotal = "SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
	// 		sum(Price) as Price,			
	// 		sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
	// 		sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
	// 		`ScrmCIGradeData` 
	// 		as PGD left join AdminPackingStyles as PS 
	// 		on PGD.PackingStyleID=PS.PCKID $Qry";

	$SqlTotal = "SELECT PS.Uom as Uom, sum(Mcs) as MCS, InvId, $sqlExt,
		SUM(Price) AS Price,
		SUM(Mcs * TotalSlabs * SlabWeight * Price * Mfactor) AS PriceKgs,
		CASE WHEN PS.Uom = 'GRAMS' THEN
			sum(Mcs * TotalSlabs * SlabWeight * Price * MFactor * $CanadaLbsFactor)
		ELSE
			sum(Mcs * TotalSlabs * SlabWeight * Price * LbsFactor)
		END AS PriceLbs
		FROM `ScrmCIGradeData` AS PGD
		LEFT JOIN AdminPackingStyles AS PS ON PGD.PackingStyleID = PS.PCKID $Qry";

	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];

	// echo $SqlTotal;exit;
	// echo json_encode($RowTotal);exit;

	$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'";
	$StmtQtyUnit = $iconn->query($SqlQtyUnit);
	$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
	$QtyUnit = $RowQtyUnit['QtyUnit'];
	$UnitPrice = number_format($RowTotal['Price'], 3);


	if ($QtyUnit == 'KGS') {
		$Qty = $RowTotal['NetWgtKgs'];
		$UsdValue = $RowTotal['PriceKgs'];
	} else {
		//$Qty=$RowTotal['NetWgtLbs']*0.454;
		$Qty = $RowTotal['NetWgtLbs'];
		$UsdValue = $RowTotal['PriceLbs'];
		//$UnitPrice=round(($UsdValue/$Qty),6);
	}

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['UnitPrice'] = $UnitPrice;

	if ($QtyUnit == 'KGS') {
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	} else {
		$NetWgtKgs = $CanadaLbsFactor > 0 ? $RowTotal['NetWgtLbs'] / $CanadaLbsFactor : $RowTotal['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $CanadaLbsFactor > 0 ? $RowTotal['GrossWeightLbs'] / $CanadaLbsFactor : $RowTotal['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	}


	$AryPriceData['NetWgtKgs'] = $NetWgtKgs;
	$AryPriceData['NetWgtLbs'] = $NetWgtLbs;
	$AryPriceData['GrossWeightKgs'] = $GrossWeightKgs;
	$AryPriceData['GrossWeightLbs'] = $GrossWeightLbs;
	return $AryPriceData;

}


function getBankDetails($iconn, $BankID)
{

	//$SqlBank="SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
	$SqlBank = "SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$AryBankData = $StmtBank->fetch_assoc();

	return $AryBankData;

}


?>