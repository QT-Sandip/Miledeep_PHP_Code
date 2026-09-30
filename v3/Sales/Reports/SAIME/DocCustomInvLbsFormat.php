<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include("../../Includes/IncludePage.php");
require_once __DIR__ . '/Includes/SaimeReportData.php';
require_once __DIR__ . '/Includes/SaimeBreakupRows.php';
// include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
require_once __DIR__ . '/Includes/SaimeSinglePagePdf.php';

//echo $cp ; exit;
$AdminConfigDetails = json_decode(json_encode($_SESSION['ClientConfigData']), true);


// Temporary line added only for the DEV client. This will be removed soon.
$AdminConfigDetails['ComInvPrintReciprocal'] = true;

$PoID = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_GET['ID']) ? (int) $_GET['ID'] : 0);
$RequestedType = isset($_GET['type']) ? strtoupper(trim((string) $_GET['type'])) : '';
if ($RequestedType === '' && isset($_GET['Type']) && in_array(strtoupper(trim((string) $_GET['Type'])), array('KGS', 'LBS'), true)) {
	$RequestedType = strtoupper(trim((string) $_GET['Type']));
}
$UnitType = in_array($RequestedType, array('KGS', 'LBS'), true) ? $RequestedType : '';
$Type = isset($_GET['OutputType']) ? $_GET['OutputType'] : '';
if ($Type === '' && isset($_GET['Type']) && !in_array(strtoupper(trim((string) $_GET['Type'])), array('KGS', 'LBS'), true)) {
	$Type = $_GET['Type'];
}

$SqlCustom = "SELECT SPD.DestinationCountry, SPD.InvDate, SPD.ExporterID,
    SCV.CInvID, SCV.IGST, SCV.CInvDate, SCV.CInvNo, SCV.CustomsInvNo
    FROM ScrmPoData AS SPD
    LEFT JOIN ScrmCustomInvoices AS SCV ON SCV.PiID = SPD.PoID
    WHERE SPD.PoID='$PoID'
    ORDER BY SCV.CInvID DESC
    LIMIT 1";
$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom ? $StmtCustom->fetch_assoc() : null;
if (!$RowCustom) {
	http_response_code(404);
	exit('Purchase order data was not found for ID ' . $PoID . '.');
}
$IGST = isset($RowCustom['IGST']) ? $RowCustom['IGST'] : '';
$ExpID = (int) $RowCustom['ExporterID'];
$DocType = 'CUI';

$DestinationCountryId = $RowCustom['DestinationCountry'];
$InvDate = $RowCustom['InvDate'];
$CInvDate = !empty($RowCustom['CInvDate']) ? $RowCustom['CInvDate'] : $InvDate;

$CanadaLbsFactor = $DestinationCountryId == 23 && $CInvDate >= '2026-03-01' ? 2.2046 : 'LbsFactor';

class MYPDF extends TCPDF
{

	public function Header()
	{
		// 
		/*getCustomInvData($iconn,$PoID);
																																										$IGST=$AryData['IGST'];*/
		global $IGST;
		if ($IGST == 'IGST') {
			$Header2 = 'SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
		} else {
			$Header2 = '';
		}

		global $Header;
		$image_file = 'images/ds_logo.jpg';
		$this->SetFont('helvetica', 'B', 8);
		$this->Cell(120, 0, 'CUSTOMS INVOICE CUM PACKING LIST' . $Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
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
$pdf->SetTitle('INVOICE' . $Header);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(4);
$pdf->SetMargins(8, 8, 8, 6);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 7);
$pdf->AddPage();


$TableData = getCustomInvLbsFormatData($iconn, $PoID);
writeSaimeSinglePage($pdf, $TableData);
$pdf->SetFont('helvetica', '', 6);


$ArrayFilename = getfilename($iconn, $ExpID, $PoID);

$InvShortCode = !empty($ArrayFilename['InvShortCode']) ? $ArrayFilename['InvShortCode'] : 'SAIME';
$CInvNo = !empty($ArrayFilename['CInvNo']) ? $ArrayFilename['CInvNo'] : (!empty($RowCustom['CInvNo']) ? $RowCustom['CInvNo'] : $PoID);
$FinancialYear = !empty($ArrayFilename['FinancialYear']) ? $ArrayFilename['FinancialYear'] : '';
if ($FinancialYear === '') {
	$fileYear = (int) date('Y', strtotime($CInvDate));
	$fileMonth = (int) date('n', strtotime($CInvDate));
	$fileYearStart = $fileMonth >= 4 ? $fileYear : $fileYear - 1;
	$FinancialYear = $fileYearStart . '-' . substr((string) ($fileYearStart + 1), -2);
}

$file_name = $InvShortCode . '-CUS-' . $CInvNo . '-' . $FinancialYear . '.pdf';

//sme/pi/233/2022-23




//echo $Type;
ob_end_clean();

if ($Type == '') {
	$Type = 'I';
} else {
	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '', $file_name);
	$file_name = $Fname . $file_name;
}

$pdf->Output($file_name, $Type);



function formatPackingStyleForUnit($packingStyle, $targetUnit)
{
	$packingStyle = trim((string) $packingStyle);
	$targetUnit = strtoupper(trim((string) $targetUnit));
	if ($packingStyle === '' || !in_array($targetUnit, array('KGS', 'LBS'), true)) {
		return $packingStyle;
	}

	return preg_replace_callback(
		'/(\d+(?:\.\d+)?)\s*(KGS?|KILOGRAMS?|LBS?|POUNDS?|OZ|OUNCES?)\b/i',
		function ($match) use ($targetUnit) {
			$value = (float) $match[1];
			$sourceUnit = strtoupper($match[2]);
			if (in_array($sourceUnit, array('KG', 'KGS', 'KILOGRAM', 'KILOGRAMS'), true)) {
				$sourceUnit = 'KGS';
			} elseif (in_array($sourceUnit, array('LB', 'LBS', 'POUND', 'POUNDS'), true)) {
				$sourceUnit = 'LBS';
			} else {
				$sourceUnit = 'OZ';
			}

			if ($sourceUnit === $targetUnit) {
				return $match[0];
			}
			if ($sourceUnit === 'KGS') {
				$converted = $value * 2.2046226218;
			} elseif ($sourceUnit === 'LBS') {
				// Use the SAIME commercial packing conversion: 1 LB = 0.454 KG.
				$converted = $targetUnit === 'KGS' ? $value * 0.454 : $value;
			} else {
				$converted = $targetUnit === 'KGS' ? ($value / 16) * 0.454 : $value / 16;
			}
			return number_format($converted, 3, '.', '') . ' ' . $targetUnit;
		},
		$packingStyle,
		1
	);
}

function getCustomInvLbsFormatData($iconn, $PoID)
{
	$linerName = SaimeReportData::linerName($iconn, $PoID);
	global $DocType, $AdminConfigDetails, $CanadaLbsFactor, $UnitType, $CInvDate;

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
	$payment = getPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetails);
	$sourceIsKgs = strtoupper(trim((string) $data['QtyUnit'])) === 'KGS';
	$selectedUnit = $UnitType !== '' ? $UnitType : ($sourceIsKgs ? 'KGS' : 'LBS');
	$isKgs = $selectedUnit === 'KGS';
	$weightUnit = $isKgs ? 'KGS' : 'LBS';
	$rateUnit = $isKgs ? 'KG' : 'LB';

	$stmt = $iconn->prepare("SELECT PGD.*, APS.PackingStyle, APS.TotalSlabs, APS.SlabWeight, APS.MFactor, APS.LbsFactor, APS.Uom, PV.TypeProduct AS InvoiceProductType
        FROM ScrmPoGradeData PGD
        LEFT JOIN AdminPackingStyles APS ON PGD.PackingStyleID = APS.PCKID
        LEFT JOIN ScrmPIVarietyList PV ON PV.VarietyID = PGD.VarietyID
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
	$hasCookedItems = false;
	$hasOtherItems = false;
	while ($row = $items->fetch_assoc()) {
		$productType = strtoupper(trim((string) $row['InvoiceProductType']));
		if ($productType === 'COOKED') {
			$hasCookedItems = true;
		} else {
			$hasOtherItems = true;
		}
		$cases = (int) (!empty($row['PIMcs']) ? $row['PIMcs'] : $row['Mcs']);
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
			$specItemCode = trim((string) $row['BuyerItemCode']);
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
	// Some older records do not have both freight visibility flags enabled even
	// though a freight charge was saved. A saved non-zero amount must therefore
	// remain visible on the customs invoice.
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
	$isUsaDestination = $destinationCountryId === 157 || in_array(
		$destinationCountryName,
		array('USA', 'U.S.A.', 'UNITED STATES', 'UNITED STATES OF AMERICA'),
		true
	);
	$duties = $isUsaDestination ? (float) $payment['DutiesPrice'] : 0.0;
	$fobUsd = $isUsaDestination && !empty($payment['FOBValueUSD'])
		? (float) $payment['FOBValueUSD']
		: $subTotal - $duties;
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
	$lutRef = SaimeReportData::lutReference($iconn, $data['ExporterID'], 'LUT', $CInvDate);
	$lutNumber = !empty($lutRef['ReferenceNumber']) ? trim((string) $lutRef['ReferenceNumber']) : trim((string) $data['LutNumber']);
	$lutRemarks = !empty($lutRef['Remarks']) ? trim((string) $lutRef['Remarks']) : '';
	$invoiceTimestamp = strtotime((string) $CInvDate);
	$invoiceYear = $invoiceTimestamp ? (int) date('Y', $invoiceTimestamp) : (int) date('Y');
	$invoiceMonth = $invoiceTimestamp ? (int) date('n', $invoiceTimestamp) : (int) date('n');
	$financialYearStart = $invoiceMonth >= 4 ? $invoiceYear : $invoiceYear - 1;
	$invoiceFinancialYear = $financialYearStart . '-' . substr((string) ($financialYearStart + 1), -2);
	$financialYear = !empty($lutRef['FinancialYear'])
		? trim((string) $lutRef['FinancialYear'])
		: $invoiceFinancialYear;
	$igstType = strtoupper(trim((string) $data['IGST']));
	// Print the IGST heading only for cooked-only KGS invoices.
	$showIgstHeading = $isKgs && $igstType === 'IGST' && $hasCookedItems && !$hasOtherItems;
	$exportSupplyText = $showIgstHeading ? 'SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST' : '';

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
	$html .= '<tr><th colspan="10" align="center" style="font-size:11pt;height:18px"><b>INVOICE</b></th></tr>';
	if ($exportSupplyText !== '') {
		$html .= '<tr><th colspan="10" align="center" style="font-size:6.2pt;height:13px"><b>' . $e($exportSupplyText) . '</b></th></tr>';
	}
	$html .= '<tr><td colspan="5" rowspan="3" style="height:70px"><span class="label"><b>EXPORTER:</b></span><br><b>' . $e(strtoupper($data['ExporterName'])) . '</b><br><b>' . $nl(strtoupper($data['ExporterAddress'])) . '</b><br><br><span class="label"><b>PROCESSED &amp; PACKED BY:</b></span><br><b>' . $e(strtoupper($data['ProcessorName'])) . '</b><br><b>' . $nl(strtoupper($data['ProcessorAddress'])) . '</b></td>';
	$html .= '<td colspan="5" style="height:21px"><span class="label">INVOICE No. &amp; Date :</span><br><b>' . $e($data['InvNo']) . ' DT: ' . $e($data['InvDate']) . '</b></td></tr>';
	$html .= '<tr><td colspan="5" style="height:21px"><span class="label">Buyer\'s order no &amp; date</span><br><b>PO :' . $e($data['PoNo']) . ($data['PoDate'] !== '' ? ' DT: ' . $e($data['PoDate']) : '') . '</b></td></tr>';
	$html .= '<tr><td colspan="5" style="height:28px"><span class="label">Other Reference(s) :</span><br><b>PROCESSOR CODE: ' . $e($processorCode) . '<br>IE CODE: ' . $e($data['ExporterIeCode']) . '<br>USFDA BIOTERRORISM REGN NO: ' . $e($data['ProcessorUsfdaNo']) . '</b></td></tr>';
	$html .= '<tr><td colspan="5" rowspan="2" cellpadding="0" style="height:92px"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td><b>IMPORTER OF RECORD</b><br><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="62%"><b>' . $e(strtoupper($data['ImporterName'])) . '</b></td><td width="38%" align="left"><b>' . (!empty($data['ImporterCode']) ? 'IRS NO. ' . $e($data['ImporterCode']) : '') . '</b></td></tr></table><b>' . $nl(strtoupper($data['ImporterAddress'])) . '</b><br><b>NOTIFY PARTY:</b><br><b>' . ($data['NotifyPartyName'] !== '' ? 'C/O. ' . $e(strtoupper(rtrim($data['NotifyPartyName'], ','))) . ',' : '') . '</b><br><b>' . $nl(strtoupper($data['NotifyPartyAddress'])) . '</b></td></tr></table></td>';
	$html .= '<td colspan="5" style="height:70px">' . $markedNotifyPartyHtml . '</td></tr>';
	$html .= '<tr><td colspan="5" cellpadding="0" style="height:22px"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td width="50%" align="center" style="border-right:1px solid #000"><span class="label">Country of Origin of Goods :</span><br><b>' . saimeReportHtml($iconn, $PoID, 'CountryOfOrigin', 'INDIA') . '</b></td><td width="50%" align="center"><span class="label">Country of Final Destination :</span><br><b>' . $e(strtoupper($data['DestinationCountry'])) . '</b></td></tr></table></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">Pre- Carriage by :</span><br><b>CONTAINER</b></td><td colspan="3"><span class="label">Place of Receipt by :</span><br><b>' . $e(strtoupper($data['PlaceofReceipt'])) . '</b></td><td colspan="5" rowspan="3"><span class="label">Terms of Delivery &amp; Payment :</span><br><b>' . $nl(strtoupper($data['PaymentTerms'])) . '</b><br><b>1)TRANSPORT TERMS: ' . $e(strtoupper($data['DeliveryTerms'])) . '</b>' . $referenceNoHtml . '</td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">Vessel/ Flight No.</span></td><td colspan="3"><span class="label">Port of Loading :</span><br><b>' . $e(strtoupper($data['PortOfLoading'])) . '</b></td></tr>';
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
		$itemCode = trim((string) $row['BuyerItemCode']);

		$groupKey = $row['Variety'] . '|' . $row['PackingStyleID'] . '|' . $row['Brand'];
		$isFirstInGroup = !isset($seenGroups[$groupKey]);
		$seenGroups[$groupKey] = true;

		$breakupRow = array(
			0 => $index === 0 ? '<b>' . $e($data['ContainerNo']) . '</b>' : '',
			3 => '<b>' . $e($itemCode) . '</b>',
			4 => '<b>' . $e($row['Grade']) . '</b>',
			5 => '<b>' . number_format($row['_cases']) . '</b>',
			6 => '<b>' . number_format($row['_displayWeight'], 3) . '</b>',
			7 => '<b>' . rtrim(rtrim(number_format($row['_rate'], 5), '0'), '.') . '</b>',
			8 => '<b>' . number_format($row['_amount'], 2) . '</b>'
		);
		if ($isFirstInGroup) {
			$descriptionHtml = '<b>' . number_format($groupCases[$groupKey]) . ' M/CARTONS OF ' . $nl($description)
				. '<br>PACK: ' . $e(formatPackingStyleForUnit($row['PackingStyle'], $selectedUnit)) . '<br>BRAND: ' . $e($row['Brand'])
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
	if ($isKgs && $igstType === 'LUT' && $lutNumber !== '') {
		$financialHtml .= '<tr><td colspan="3" style="height:15px;line-height:15px"></td></tr>';
		if ($lutRemarks !== '') {
			$financialHtml .= '<tr><td colspan="3" style="border-top:1px solid #000;padding-top:2px"><b>LUT# ' . $e($lutNumber) . ' ' . $e($financialYear) . '</b></td></tr>';
			$financialHtml .= '<tr><td colspan="3" style="border-bottom:1px solid #000;padding-bottom:2px"><b>' . $e($lutRemarks) . '</b></td></tr>';
		} else {
			$financialHtml .= '<tr><td colspan="3" style="border-top:1px solid #000;border-bottom:1px solid #000;padding:2px 0"><b>LUT# ' . $e($lutNumber) . ' ' . $e($financialYear) . '</b></td></tr>';
		}
	}
	$financialHtml .= '</table>';

	if ($isKgs) {
		$weightBreakupHtml = '<tr><td width="60%"></td><td width="40%" align="right"><b>KGS</b></td></tr>';
		$weightBreakupHtml .= '<tr><td width="60%"><b>NET WEIGHT:</b></td><td width="40%" align="right"><b>' . number_format($totalKg, 3) . '</b></td></tr>';
		$weightBreakupHtml .= '<tr><td width="60%"><b>GROSS WEIGHT:</b></td><td width="40%" align="right"><b>' . number_format($grossKg, 3) . '</b></td></tr>';
		$weightColumnCount = 2;
	} else {
		$weightBreakupHtml = '<tr><td width="40%"></td><td width="27%" align="right"><b>KGS</b></td><td width="33%" align="right"><b>LBS</b></td></tr>';
		$weightBreakupHtml .= '<tr><td width="40%"><b>NET WEIGHT:</b></td><td width="27%" align="right"><b>' . number_format($totalKg, 3) . '</b></td><td width="33%" align="right"><b>' . number_format($totalLb, 3) . '</b></td></tr>';
		$weightBreakupHtml .= '<tr><td width="40%"><b>GROSS WEIGHT:</b></td><td width="27%" align="right"><b>' . number_format($grossKg, 3) . '</b></td><td width="33%" align="right"><b>' . number_format($grossLb, 3) . '</b></td></tr>';
		$weightColumnCount = 3;
	}
	$weightBreakupHtml .= '<tr><td colspan="' . $weightColumnCount . '" style="height:10px;line-height:10px"></td></tr>';

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
	$html .= $weightBreakupHtml;
	$html .= '<tr><td colspan="' . $weightColumnCount . '" cellpadding="0">' . $financialHtml . '</td></tr></table></td>';
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
	$html .= '<tr><td colspan="10" class="scheme-text"><b>THIS SHIPMENT IS MADE UNDER DUTY DRAW BACK SCHEME AS WELL AS UNDER RODTEP SCHEME.</b><br>I/WE, IN REGARD TO MY/OUR CLAIM UNDER RODTEP SCHEME MADE IN THIS SHIPPING BILL OR BILL OF EXPORT, HEREBY DECLARE THAT:<br>1) I/WE UNDERTAKE TO ABIDE BY THE APPLICABLE PROVISIONS, CONDITIONS, RESTRICTIONS, EXCLUSIONS AND TIME-LIMITS UNDER THE RODTEP SCHEME.<br>2) THIS CLAIM IS NOT WITH RESPECT TO DUTIES, TAXES OR LEVIES EXEMPTED, REMITTED OR CREDITED UNDER ANOTHER MECHANISM.<br>3) I/WE UNDERTAKE TO PRESERVE AND MAKE AVAILABLE RELEVANT EXPORT DOCUMENTS FOR AUDIT UNDER THE CUSTOMS AUDIT REGULATIONS, 2018.</td></tr>';
	$html .= '<tr nobr="true"><td colspan="6" class="processor-text" style="height:72px;border:1px solid #000"><b>PROCESSED AND PACKED BY:</b><br><b>' . $e($data['ProcessorName']) . '</b><br>' . $nl($data['ProcessorAddress']) . '<br><b>Declaration:</b><br>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</td><td colspan="4" class="signature-text" align="center" valign="bottom" style="height:72px;border:1px solid #000">Signature &amp; date<br><b>For ' . $e($data['ExporterName']) . '</b><br><br><br><br><b>CHAIRMAN</b></td></tr>';
	if ($linerName !== '') {
		$html .= '<tr><td colspan="10" style="height:13px;border-top:1px solid #000"><b>LINER - ' . $e(strtoupper($linerName)) . '</b></td></tr>';
	}
	$html .= '</table>';

	return $html;
}

function getCustomInvData($iconn, $PoID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $DocType, $AdminConfigDetail, $CanadaLbsFactor;
	$AryData = getReportData($iconn, $PoID);
	// echo json_encode($AryData, JSON_PRETTY_PRINT);exit;

	$AryBreakupData = getBreakUpData($iconn, '', $PoID);

	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];

	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$PlaceofReceipt = $AryData['PlaceofReceipt'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];

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
	// echo $ShowFrightCharge;exit;
	$ShowInsurance = $AryData['ShowInsurance'];
	$ShowOverSeasCommission = $AryData['ShowOverSeasCommission'];
	$OverSeasCommission = $AryData['OverSeasCommission'];
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
	$NoOfDays = $AryData['NoOfDays'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];

	$showProUsFda = $AryData['showProUsFda'];
	$ShowNoofDays = $AryData['ShowNoofDays'];
	$showProFEI = $AryData['showProFEI'];
	$PortOfLoading = $AryData['PortOfLoading'];

	$ContainerNo = $AryData['ContainerNo'];
	$SealNo = $AryData['SealNo'];



	$showIgst = $AryData['showIgst'];
	$showDiscount = $AryData['showDiscount'];

	$ExporterRecAdd = $AryData['ImporterAddress'];
	$ImporterName = $AryData['ImporterName'];
	$ImporterCode = $AryData['ImporterCode'];

	// if( is_numeric($CanadaLbsFactor) && $CanadaLbsFactor > 0 ) {
	$AdminConfigDetail['CanadaLbsFactor'] = $CanadaLbsFactor;
	// }
	$AryPaymentData = getPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetail);
	// echo json_encode($AryPaymentData, JSON_PRETTY_PRINT);exit;
	$InsuranceValue = $AryPaymentData['InsuranceValue'];


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

	$content .= '<br><br><table border="1" cellspacing="0" cellpadding="0">  
				
				
				 <tr>  
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';

	$content .= '
				<tr>
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '';

	if ($showExpUsFda) {
		$content .= '<BR>USFDA REGISTRATION NO  : ' . $ExporterUsfdaNo . '<BR>';
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
		$BuyerLabelSql = "SELECT InvDate FROM ScrmPIData WHERE PoID=$PoID";
		$StmtBuyerLabel = $iconn->query($BuyerLabelSql);
		$RowBuyerLabel = $StmtBuyerLabel->fetch_assoc();
		$InvDate1 = $RowBuyerLabel['InvDate'];
		if (!empty($InvDate1) && $InvDate1 >= '2026-04-01') {
			$BuyerLabelText = "Buyer";
			$consineeLabelText = 'Ultimate ';
		}
	}
	if ($BuyerID == '' || $BuyerID == 0) {


		$content .= '		<tr>  
						   
						<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '/Ultimate Consignee</u></b>
						<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '
						
						
						</font></th>
						</tr>';
	} elseif ($ConsigneeID != $BuyerID) {
		$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '</u></b><br>
				' . $BuyerName . ',<br>' . $BuyerAddress . '<br>
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



	$content .= '	<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">' . $PlaceofReceipt . '</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR>  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">' . $PortOfLoading . '</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">' . $PortOfDischarge . '</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">' . $FinalDestination . '</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
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
				<u><b> Packer</u> :</b><br> ' . $ProcessorName . '<br> ' . nl2br($ProcessorAddress) . ' ';

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
				</b><br><BR> ' . $PaymentTerms . ' ';
	if ($ShowNoofDays) {
		$content .= ' ' . $NoOfDays . '';
	}
	$content .= '<br> SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($FinalDestination) . '';

	if ($ShowDeliveryTerms) {
		$content .= '(' . $DeliveryTerms . ')<br>';
	}

	$content .= '<table width="100%"><tr>';

	if ($ShowInsurance) {
		$content .= '<BR><th align="left" width="70%"><b> INSURANCE: ' . number_format($InsuranceValue, 2) . '</b></th>';
	}

	if ($ShowOverSeasCommission) {
		if ($AryBreakupData['QtyUnit'] == 'KGS') {
			$WtQty = $AryBreakupData['NetWgtKgs'];
		} else if ($AryBreakupData['QtyUnit'] == 'LBS') {
			$WtQty = $AryBreakupData['NetWgtLbs'];
		}
		$content .= '<th align="right" width="100%"><b>OVERSEAS COMMISSION: ' . number_format($OverSeasCommission * $WtQty, 2) . '</b></th>';
	}

	$content .= '</tr></table></font></th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> 	<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> ' . $TotalMcs . '</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br></font><font size="10"><b align="center">' . $ContainerNo . '</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br></font><font size="10"><b align="center">' . $SealNo . '</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in KGS </font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>' . saimeReportHtml($iconn, $PoID, 'UnitCurrency', 'USD') . '/KGS ' . $ShippingTerms . ' <br><b>' . strtoupper($FinalDestination) . '</b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in ' . saimeReportHtml($iconn, $PoID, 'UnitCurrency', 'USD') . ' <br>' . $ShippingTerms . '<br><b> ' . strtoupper($FinalDestination) . '</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>';


	$description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL IN FROZEN FORM OTHER THAN AFD';
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
			';

	$SqlVarietyMcs = "
			SELECT sum(Mcs) As VarietyMcs,Variety,Grade,PackingStyle,Brand,
			PackingStyleID,BAP,PrintStatus FROM 
			`ScrmPoGradeData` PGD
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			left join `ScrmBrandsMaster` SBM on  SBM.BrandName=PGD.Brand
			 WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand";

	//ECHO $SqlVarietyMcs; EXIT;
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {


		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];

		$BAP = $VarietyMcsRow['BAP'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];

		if ($BAP == 'NA') {
			$BAP = '';
		} else {
			$BAP = $BAP;
		}

		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
			. $VarietyMcsRow['VarietyMcs'] . ' CARTONS OF ' . $VarietyMcsRow['Variety'] . '<br>
			PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' NET WEIGHT PER CARTON.' . $Whitspace . ' 
			';


		if ($BrandPrintStatus) {
			$BrandName = '<B> BRAND </B>: ' . $VarietyMcsRow['Brand'] . ' ';
		} else {
			$BrandName = '';
		}

		//echo $BrandName; exit;
		/* if($ShowBrand){
																																										$Brandp=$VarietyMcsRow['Brand'];
																																										if( $VarietyMcsRow['Brand']=='NEUTRAL'){
																																											$Brandp='';
																																										}
																																										$content.='<B> BRAND </B>: '.$Brandp.' <B><br>'.$BAP.'</B>';
																																										}*/

		$content .= $BrandName . ' <B><br>' . $BAP . '</B>';


		$content .= ' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>
			

			
			<tr>
			
			<th align="center" width="40%" ><font size="' . $FontSize . '"> <u>SIZE</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';


		$SqlVariety = "SELECT * FROM `ScrmPoGradeData` as PGD
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
	// echo json_encode($AryPriceData, JSON_PRETTY_PRINT);exit;
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];

	// exit;

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

	$InvValueUSD = $AryPaymentData['InvValueUSD'];
	$InvValueInr = $AryPaymentData['InvValueINR'];



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


	if ($showIgst) {
		$content .= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				INVOICE VALUE IN R.S@' . $ExchangeRate . '  </B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				' . number_format($InvValueInr, 2) . '</B></font></td>
				</tr>
				<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				IGST@' . $IgstPerc . ' % </B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				' . number_format($IgstValue, 2) . '</B></font></td>
				</tr>';
	}

	$content .= '</table>
			  </tr>';

	$BILL_WORD = numberTowords($InvValueUSD);

	$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
	//$InvValueINRWithOutDuties=$AryPaymentData['InvValueUSDWithOutDuties']*$ExchangeRate;
	$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
	$DutiesPrice = $AryPaymentData['DutiesPrice'];
	$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
	$FOBValueINR = round($AryPaymentData['FOBValueINR']);

	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];

	$content .= '<tr>
			<table border ="0" cellspacing="0" cellpadding="0">';
	if (!$ShowExtraCharges) {
		$content .= '<tr>
			<td><font size="7"><br>   SHIPPING TERMS   : ' . $ShippingTerms . '  ' . $FinalDestination . '</font></td>
			</tr>';
	}

	// echo $ShowFrightCharge . ' ' . $ShowExtraCharges;exit;
	if ($ShowExtraCharges) {
		$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
		$DiscountAmount = $AryPaymentData['DiscountAmount'];
		$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
		$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
		$DutiesPrice = $AryPaymentData['DutiesPrice'];
		$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
		$AntiDumpingDuty = round($AryPaymentData['AntiDumpingDuty'], 2);
		$CountervailingDuty = round($AryPaymentData['CountervailingDuty'], 2);
		$ReciprocalTariffsFee = round($AryPaymentData['ReciprocalTariffsFee'], 2);

		$MerchantDuty = round($AryPaymentData['MerchantDuty'], 2);
		$HarborDuty = round($AryPaymentData['HarborDuty'], 2);
		$FOBValueINR = round($AryPaymentData['FOBValueINR']);
		$InvValueUSD = $AryPaymentData['InvValueUSD'];
		$ISFStatic = $AryPaymentData['ISFStatic'];
		$CenttoUsd = $AryPaymentData['CenttoUsd'];
		$AntiDumpingDutyPerc = $AryPaymentData['AntiDumpingDutyPerc'];
		$HarbourMaintenanceFeePerc = $AryPaymentData['HarbourMaintenanceFeePerc'];
		$CountervailingDutyPerc = $AryPaymentData['CountervailingDutyPerc'];
		$MerchandiseProcessFeePerc = $AryPaymentData['MerchandiseProcessFeePerc'];
		$ReciprocalTariffsPerc = $AryPaymentData['ReciprocalTariffsPerc'];

		$content .= '
			<table border="1" cellspacing="0" cellpadding="2">
			
			<tr>
			<td >
			
			<table border="0" cellspacing="0" cellpadding="0">';
		$content .= '<tr>
			<td  width="55%"><font size="7"><br>  SHIPPING TERMS   : ' . $ShippingTerms . '  ' . $FinalDestination . '</font></td>
			</tr>';


		if ($ShowFrightCharge) {

			$content .= '<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">INVOICE VALUE US </font></td>
					<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
					<td align="left" width="29%" ><font size="7"> <b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($InvValueUSD, 2) . '</b></font></td>
					</tr>';

			$content .= '
					<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">LESS OCEAN FREIGHT  $</font></td>
					<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
					<td align="left" width="29%" ><font size="7"> <b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' <u>' . number_format($FrieghtCharge, 2) . '</u></b></font></td>
					</tr>';

			if ($ISFStatic) {
				$content .= '
					<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">(LESS : MERCHANDISE PROCESS FEE @ ' . htmlspecialchars((string) $MerchandiseProcessFeePerc, ENT_QUOTES, 'UTF-8') . '%)</font></td>
					<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
					<td align="left" width="44%" ><font size="7"> <b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($MerchantDuty, 2) . '</b></font></td>
					</tr>';
			}
			$content .= '
					<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">SUB TOTAL</font></td>
					<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
					<td align="left" width="29%" ><font size="7"> <b> ' . saimeReportHtml($iconn, $PoID, 'CurrencySymbol', '$') . ' ' . number_format($InvValueUSDWithOutDuties, 2) . '</b></font></td>
					</tr>';
			$content .= '
					<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">(LESS : ANTI-DUMPING DUTY @ ' . $AntiDumpingDutyPerc . '%) </font></td>
					<td align="left" width="44%" ><font size="7"> <b>: $ ' . number_format($AntiDumpingDuty, 2) . '</b></font></td>
					</tr>';
			//uma cvd calculation included //18-03-2024

			if ($CountervailingDutyPerc != 0) {
				$content .= '
					<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">(LESS : COUNTER VAILING DUTY @ ' . $CountervailingDutyPerc . '%) </font></td>
					<td align="left" width="44%" ><font size="7"> <b>: $ ' . number_format($CountervailingDuty, 2) . '</b></font></td>
					</tr>';
			}

			//uma cvd calculation included //18-03-2024
			if (!$ISFStatic) {
				$content .= '
					<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">(LESS : MERCHANDISE PROCESS FEE @ ' . $MerchandiseProcessFeePerc . '%)</font></td>
					<td align="left" width="44%" ><font size="7"> <b>: $ ' . number_format($MerchantDuty, 2) . '</b></font></td>
					</tr>';
			}
			$content .= '<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">(LESS : HARBOR MAINTENANCE FEE @ ' . $HarbourMaintenanceFeePerc . '%)</font></td>
					<td align="left" width="44%" ><font size="7"> <b>: $ ' . number_format($HarborDuty, 2) . '</b></font></td>
					</tr>';

			if ($ReciprocalTariffsPerc != 0) {
				$content .= '
						<tr>
						<td  width="1%"></td>
						<td align="left" width="55%"><font size="7">(LESS : RECIPROCAL TARIFFS @ ' . $ReciprocalTariffsPerc . '%) </font></td>
						<td align="left" width="44%" ><font size="7"> <b>: $ ' . number_format($ReciprocalTariffsFee, 2) . '</b></font></td>
						</tr>';
			}

			$content .= '<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">TOTAL DUTY</font></td>
					<td align="left" width="44%" ><font size="7"> <b>: $ <u>' . number_format($DutiesPrice, 2) . '</u></b></font></td>
					</tr>';

			$content .= '<tr>
					<td  width="1%"></td>
					<td align="left" width="55%"><font size="7">FOB VALUE  IN USD  </font></td>
					<td align="left" width="15%" ><font size="7"> <b>: $ ' . number_format($FOBValueUSD, 2) . '</b></font></td>
					<td align="left" width="30%" ><font size="7"> <b> FOB VALUE  :  ' . number_format(round($FOBValueINR), 2) . '</b></font></td>
					</tr>';
		} else if ($ShowInvoicevalue) {
			$content .= '<tr>
			<td  width="1%"></td><td align="left" width="35%"><font size="7">INVOICE VALUE US $</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: ' . number_format($InvValueUSD, 2) . '</b></font></td>
			</tr>';

			$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN US $ </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($InvValueUSDWithOutDuties, 2) . '</b></font></td>
			<td align="left" width="60%" ><font size="7"> 
			<b> FOB VALUE  :  ' . round($InvValueINRWithOutDuties) . '.00</b></font></td>
			</tr>';
		} else {
			$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN US $ </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($InvValueUSDWithOutDuties, 2) . '</b></font></td>
			<td align="left" width="60%" ><font size="7"> 
			<b> FOB VALUE  :  ' . round($InvValueINRWithOutDuties) . '.00</b></font></td>
			</tr>';
		}


		$content .= '</table></td>';
		$content .= '</table></tr>';
	} else if ($ShowFrightCharge) {
		$content .= '<tr>
			<td  width="1%"></td><td align="left" width="35%"><font size="7">INVOICE VALUE US $</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: ' . number_format($InvValueUSD, 2) . '</b></font></td>
			</tr>';

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">LESS OCEAN FREIGHT PREPAID IN US $</font></td>
			<td align="left" width="30%" ><font size="7"><b>:  <u>' . number_format($FrieghtCharge, 2) . '</u></b></font></td>
			</tr>';
		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">SUB TOTAL</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: ' . number_format($InvValueUSDWithOutDuties, 2) . '</b></font></td>
			</tr>';
		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">LESS DUTIES, PORT TAXES & CLEARANCE US $ </font></td>
			<td align="left" width="60%" ><font size="7"> <b>: <u>' . number_format($DutiesPrice, 2) . '</u></b></font></td>
			</tr>';
		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN US $ </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($FOBValueUSD, 2) . '</b></font></td>
			<td align="left" width="60%" ><font size="7"> <b> FOB VALUE  :  ' . number_format(round($FOBValueINR), 2) . '</b></font></td>
			</tr>';
	} else if ($ShowInvoicevalue) {
		$content .= '<tr>
			<td  width="1%"></td><td align="left" width="35%"><font size="7">INVOICE VALUE US $</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: ' . number_format($InvValueUSD, 2) . '</b></font></td>
			</tr>';

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN US $ </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($InvValueUSDWithOutDuties, 2) . '</b></font></td>
			<td align="left" width="60%" ><font size="7"> 
			<b> FOB VALUE  :  ' . round($InvValueINRWithOutDuties) . '.00</b></font></td>
			</tr>';
	} else {
		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN US $ </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($InvValueUSDWithOutDuties, 2) . '</b></font></td>
			<td align="left" width="60%" ><font size="7"> 
			<b> FOB VALUE  :  ' . round($InvValueINRWithOutDuties) . '.00</b></font></td>
			</tr>';
	}




	if ($QtyUnit == 'LBS' && $AryPriceData['Uom'] == 'KGS') {
		$NetWgtKgs = $AryPriceData['Qty'];
	} else {
		$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	}

	if (!is_numeric($CanadaLbsFactor)) {
		$NetKgstoLbs = round($NetWgtKgs / 0.454, 2);
		$NetWgtLbs = $AryPriceData['NetWgtLbs'];
		if (abs($NetWgtLbs - $NetKgstoLbs) > 0.1) {
			$NetWgtLbs = $NetKgstoLbs;
		}
	}


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

	/**uma 20-12-2023* */

	if ($QtyUnit == 'KGS') {
		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7"><b>NET WEIGHT: </b></font></td>
			<td align="left" width="15%" ><font size="7"> <b>' . number_format($NetWgtKgs, 3) . ' KGS</b></font></td>
			</tr>';

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7"><b>GROSS WEIGHT:</b> </font></td>
			<td align="left" width="15%" ><font size="7"> <b>' . number_format($GrossWeightKgs, 3) . ' KGS</b></font></td>
			</tr>';
	} else {

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7">NET WEIGHT </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($NetWgtLbs, 3) . ' LBS</b></font></td>
			<td align="left" width="60%" ><font size="7"> <b> ' . number_format($NetWgtKgs, 3) . ' KGS</b></font></td>
			</tr>';

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7">GROSS WEIGHT </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($GrossWeightLbs, 3) . ' LBS</b></font></td>
			<td align="left" width="30%" ><font size="7"> <b> ' . number_format($GrossWeightKgs, 3) . ' KGS</b></font></td>
			</tr>';
	}


	$content .= '</table></tr>';


	$content .= '<tr>';

	$ShipmentUnder = "1) WE HEREBY DECLARE THAT WE SHALL CLAIM THE BENEFITS UNDER CUSTOMS NOTIFICATION NO. 07/2020 DATED: 28-01-2020. AS WELL AS CHAPTER 3 OF FTP AS APPLICABLE. FISH AND CRUSTACEANS, MOLLUSCS AND OTHER AQUATIC INVERTEBRATES
<br>2) THIS SHIPMENT IS MADE UNDER DUTY DRAW BACK SCHEME AS WELL AS UNDER RoDTEP SCHEME.
<br>3) ITC HS CODE <b>" . $CInvCode . "</b>,DRAW BACK SCHEME CODE:19, SL.NO. OF EXPORT PRODUCT :030601B, RATE OF ENTITLEMENT : 3% ON FOB OR Rs.23.6/KG CAP, IN FROZEN FORM OTHER THAN AFD
";

	if ($IGST == 'LUT') {
		$savedLutDate = saimeReportValue($iconn, $PoID, 'CInvDate', date('Y-m-d'));
		$savedLut = SaimeReportData::lutReference($iconn, $ExporterID, 'LUT', $savedLutDate);
		if (!empty($savedLut['ReferenceNumber'])) {
			$LutNumber = $savedLut['ReferenceNumber'];
		} elseif (trim((string) $LutNumber) === '' && $savedLutDate >= '2026-04-01') {
			$LutNumber = 'AD370426002621F';
		}
		$ShipmentUnder .= "<br>4)SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF INTEGRATED TAX: LUT No. $LutNumber ";
	}

	$Decleration = "
I/WE, IN REGARD TO MY/OUR CLAIM UNDER RoDTEP SCHEME MADE IN THIS SHIPPING BILL OR BILL OF EXPORT, HEREBY DECLARE THAT:
<br>1. I/ WE UNDERTAKE TO ABIDE BY THE PROVISIONS, INCLUDING CONDITIONS, RESTRICTIONS, EXCLUSIONS AND TIME-LIMITS AS PROVIDED UNDER RoDTEP SCHEME, AND RELEVANT NOTIFICATIONS, REGULATIONS, ETC., AS AMENDED FROM TIME TO TIME.

<br>2. ANY CLAIM MADE IN THIS SHIPPING BILL OR BILL OF EXPORT IS NOT WITH RESPECT TO ANY DUTIES OR TAXES OR LEVIES WHICH ARE EXEMPTED OR REMITTED OR CREDITED UNDER ANY OTHER MECHANISM OUTSIDE RoDTEP.

<br>3. I/WE UNDERTAKE TO PRESERVE AND MAKE AVAILABLE RELEVANT DOCUMENTS RELATING TO THE EXPORTED GOODS FOR THE PURPOSES OF AUDIT IN THE MANNER AND FOR THE TIME PERIOD PRESCRIBED IN THE CUSTOMS AUDIT REGULATIONS, 2018
			";
	$content .= '</tr>
				
						
	        
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> AMOUNT CHARGABLE IN US.$: 
				<b>' . $BILL_WORD . '</b></font><BR> 
				<font size="5"><b>SHIPPED UNDER:</b><br>' . $ShipmentUnder . '<br>
				<BR><b> DECLARATION:</b><br>' . $Decleration . '<BR>	<br>			
				<b> INFORMATION CODE: RODTEPY</b><br>
				
				</font></th>    
	           
</tr>
<tr>
<th  align="bottom"  width="70%"><font size="8"><B><U> DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>
           
		   <th  align="center"  width="30%"><font size="7"> Signature:<br><br>
				<B> ' . $AuthSign . ',</B><BR><BR><BR>
				 
				<!--<img src="' . $SignURL . '" 
				 width="100" height="30"style="width: 100" align="center" align="middle"/>-->
								
				
				<p STYLE="text-align:center" > Authorised Signature   </p></font></th>
</tr>
				
				
				</table>';

	return $content;
}

$SqlPaymentTerms = "SELECT * FROM ScrmPoData as SPD 
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'";

function getReportData($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $DocType;

	/*$CustomInvoiceSql="SELECT *  
																										   FROM ScrmCustomInvoices as SCV 
																										   left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
																										   WHERE SCV.PiID='$PoID'"; */

	//26-09-2023 #teja
	//left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID

	$CustomInvData = SaimeReportData::customInvoice($iconn, $PoID);
	$IGST = isset($CustomInvData['IGST']) ? $CustomInvData['IGST'] : '';
	$OverSeasCommission = isset($CustomInvData['OverSeasCommission']) ? $CustomInvData['OverSeasCommission'] : 0;
	$CInvDate = isset($CustomInvData['CInvDate']) ? $CustomInvData['CInvDate'] : '';
	// echo $CInvDate;exit;
	//$CInvCode=$CustomInvData['CInvCode'];
	$NoOfDays = isset($CustomInvData['NoOfDays']) ? $CustomInvData['NoOfDays'] : 0;
	$TotalGrossWt = isset($CustomInvData['TotalGrossWt']) ? $CustomInvData['TotalGrossWt'] : 0;

	if ($NoOfDays == 0) {
		$NoOfDays = '';
	} else {

		$NoOfDays = $NoOfDays . ' DAYS FROM BL';
	}

	//$SqlMcsTotal="SELECT sum(Mcs) as Mcs FROM `ScrmCIGradeData` where InvId='$PoID'  ";
	$SqlMcsTotal = "SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
	$StmtMcsTotal = $iconn->query($SqlMcsTotal);
	$RowMcs = $StmtMcsTotal->fetch_assoc();
	$TotalMcs = $RowMcs['Mcs'];

	// $SqlHsCode = "SELECT DISTINCT HSCode FROM ScrmPoGradeData as SPGD 
	// left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
	// left join ScrmPreparationMaster as SPM on SPM.ID=SPVL.ProductID WHERE InvId='$PoID'"; 

	$CInvCode = '';
	$ArrayHsCode = array();
	$SqlHsCode = "SELECT DISTINCT HSCode FROM ScrmPoGradeData as SPGD 
left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType and SPM.SpeciesShortCode=SPVL.Species
WHERE InvId='$PoID'";


	$StmtHsCode = $iconn->query($SqlHsCode);
	while ($RowHsCode = $StmtHsCode->fetch_assoc()) {
		$hsCode = trim((string) $RowHsCode['HSCode']);
		if ($hsCode !== '') {
			$ArrayHsCode[] = $hsCode;
		}
	}
	if (count($ArrayHsCode) > 0) {
		$CInvCode = implode(',', array_unique($ArrayHsCode));
	}


	$SqlScrmPoData = "SELECT *,SCDB.BuyerName as BuyerName,
	SCDB.BuyerAddress as BuyerAddress,SPD.Status as PoStatus,
	SCD.ConsigneeName as ConsigneeName,
	 COALESCE(
        (
            SELECT Address 
            FROM ScrmConsigneeAddressHistory AS SCAH
            WHERE SCAH.ConID = SCD.ConID AND SCAH.EffectiveFrom <=  SPD.PoDate
            ORDER BY SCAH.EffectiveFrom DESC
            LIMIT 1
        ),
        SCD.ConsigneeAddress
    ) AS ConsigneeAddress,
	ABD.AccntName as AccntName,
	ABD.AccountNumber as AccountNumber,
	ABD.BankName as BankName,
	ABD.BankAddress as BankAddress,
	ABD.SwiftCode as SwiftCode,
	SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode ,
	SOC.CerificationName as CerificationName
  
	FROM ScrmPoData as SPD 
	LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
	LEFT JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
	LEFT JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
	LEFT JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
	LEFT JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
	LEFT JOIN ScrmCountriesList as SC on SC.id=SPC.Country
	LEFT JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
	LEFT JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
	LEFT JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
	LEFT JOIN ScrmOrderCertification as SOC on SOC.CerificationID =SPD.Certification


	WHERE PoID='$PoID'";

	// ALD.SignURL, left JOIN AdminLoginDetails as ALD on SC.id=SPD.ApprovedBy removed from Query

	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData ? $StmtPoData->fetch_assoc() : null;
	if (!$PoDataRow) {
		throw new RuntimeException('Unable to load purchase order data for ID ' . $PoID . '.');
	}
	if ($CInvDate === '') {
		$CInvDate = $PoDataRow['InvDate'];
	}
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ApprovedBy = $PoDataRow['ApprovedBy'];
	$CountryID = $PoDataRow['DestinationCountry'];


	// $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
	// $StmtLogin=$iconn->query($SqlLogin); 
	// $RowLogin = $StmtLogin->fetch_assoc();
	// $SignURL=$RowLogin['SignURL'];

	//$ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
	$ExporterData = getExporterDetails($ExpID, $CountryID, $DocType);
	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);

	/*$ExporterData=getExporterDetails($ExpID);
																					  $ProcessorData=getExporterDetails($ProcessorID);*/


	$ExporterUsfdaNumber = $ExporterData['ExpUsfdaNo'];
	//exit;

	if ($CustomInvData['Discount'] != 0) {
		$AryData['showDiscount'] = true;
	} else {
		$AryData['showDiscount'] = false;
	}

	/*if($CustomInvData['Discount']=='DISCOUNT'){
																					  $AryData['showDiscount']=true; 
																					  }else{
																					  $AryData['showDiscount']=false; 
																					  }*/

	if ($CustomInvData['IGST'] == 'IGST') {
		$AryData['showIgst'] = true;
	} else {
		$AryData['showIgst'] = false;
	}


	if ($PoDataRow['ShippingTerms'] == 'DDP') {
		$AryData['ShowShippingTerms'] = false;
		$AryData['ShowExtraCharges'] = true; //uma
	} else {
		$AryData['ShowShippingTerms'] = false;
	}

	if ($PoDataRow['ShippingTerms'] == 'FOB') {
		$AryData['ShowInvoicevalue'] = true; //uma
	} else {
		$AryData['ShowInvoicevalue'] = false;
	}


	//if($PoDataRow['DestinationCountry']==26){
	if ($PoDataRow['DestinationCountry'] != 26 && $PoDataRow['ShippingTerms'] != 'FOB') { //uma
		$AryData['ShowDeliveryTerms'] = true;
	} else {
		$AryData['ShowDeliveryTerms'] = false;
	}


	if ($PoDataRow['DestinationCountry'] == 157) {
		$AryData['showExpUsFda'] = true;
	}
	/*else if($ExpID==5 && $PoDataRow['DestinationCountry']!=26){
																					  $AryData['showExpUsFda']=true; 
																					  }
																					  */ else {
		$AryData['showExpUsFda'] = false;
	}
	$AryData['showExpIECode'] = true;
	$AryData['showExpGSTNo'] = true;
	/*if($ExpID==5){
																					  $AryData['showExpIECode']=true; 
																					  }else{
																					  $AryData['showExpIECode']=false; 
																					  }*/

	/*if($ExpID==5){
																					  $AryData['showExpGSTNo']=true; 
																					  }else{
																					  $AryData['showExpGSTNo']=false; 
																					  }*/

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

	if ($OverSeasCommission != 0) {
		$AryData['ShowOverSeasCommission'] = true;
	} else {
		$AryData['ShowOverSeasCommission'] = false;
	}

	if ($PoDataRow['DestinationCountry'] == 157) {
		$AryData['showProUsFda'] = true;
	} else {
		$AryData['showProUsFda'] = false;
	}

	if ($PoDataRow['PoNo'] == '') {
		$AryData['PoDate'] = '';
	} else {
		$AryData['PoDate'] = getFormatedDate($PoDataRow['PoDate']);
	}
	if ($IGST != 'DISCOUNT') {
		$AryData['ShowNoofDays'] = true;
	} else {
		$AryData['ShowNoofDays'] = false;
	}

	/* Return Data*/
	$AryData['PoStatus'] = $PoDataRow['PoStatus'];
	$AryData['TotalGrossWtKgs'] = $TotalGrossWt;
	$AryData['ApprovedBy'] = $ApprovedBy;
	$AryData['PaymentTerms'] = $PaymentTerms;
	$AryData['DeliveryTerms'] = $DeliveryTerms;
	$AryData['TotalMcs'] = $TotalMcs;
	$AryData['ExporterID'] = $ExpID;

	$AryData['ExporterName'] = $ExporterData['ExpName'];
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

	$AryData['PortOfDischarge'] = strtoupper($CustomInvData['PortofDischarge']) . ' ' . strtoupper($CustomInvData['CountryCode']) . ', ' . $CustomInvData['PortofDischargeCountry'];
	//$AryData['PortOfDischarge']=strtoupper($CustomInvData['PortofDischarge']).', '.$CustomInvData['PortofDischargeCountry'];

	$AryData['PlaceofReceipt'] = $CustomInvData['PlaceofReceipt'] != '' ? $CustomInvData['PlaceofReceipt'] : 'ANY PORT, INDIA';
	$AryData['PortOfLoading'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	$AryData['FinalDestination'] = strtoupper($CustomInvData['FinalDestination']) . ' ' . strtoupper($CustomInvData['FDCountryCode']) . ', ' . strtoupper($CustomInvData['FinalDestinationCountry']);
	//$AryData['FinalDestination']=strtoupper($CustomInvData['FinalDestination']) .', '.strtoupper($CustomInvData['FinalDestinationCountry']);;

	//$AryData['InvNo']=getInvoiceNumber($iconn,$ExpID,$PoID) ;
	$AryData['InvNo'] = !empty($CustomInvData['CustomsInvNo']) ? $CustomInvData['CustomsInvNo'] : (!empty($PoDataRow['InvNumFull']) ? $PoDataRow['InvNumFull'] : $PoDataRow['InvNo']);
	$AryData['InvDate'] = getFormatedDate($CInvDate);
	$AryData['PoNo'] = $PoDataRow['PoNo'];

	$AryData['ProcessorName'] = saimeReportValue($iconn, $PoID, 'ProcessorName', $ProcessorData['ExpName']);
	//$AryData['ProcessorAddress']=$ProcessorData['PackerAddress'];
	$AryData['ProcessorAddress'] = $ProcessorData['ExpAddress'];
	$AryData['ProcessorUsfdaNo'] = $ProcessorData['ExpUsfdaNo'];
	$AryData['ProcessorFEI'] = $ProcessorData['ExpFeiNo'];
	$AryData['ProcessorApprovalNo'] = $ProcessorData['ExpApprovalNo'];
	$AryData['ProcessorIeCustomsNo'] = $ProcessorData['ExpIeCustomsNo'];

	$AryData['ShippingTerms'] = $PoDataRow['ShippingTerms'];
	$AryData['PaymentTerms'] = $PoDataRow['PaymentName'];
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

	$AryData['ContainerNo'] = $CustomInvData['ContainerNo'];
	//$AryData['SealNo']=$CustomInvData['SealNo'];
	$AryData['SealNo'] = $CustomInvData['LinerSealNo'];

	$AryData['OverSeasCommission'] = $OverSeasCommission;




	$AryData['ImporterAddress'] = saimeReportValue($iconn, $PoID, 'ImporterAddress', $PoDataRow['ImporterAddress']);
	$AryData['ImporterName'] = $PoDataRow['ImporterName'];
	$AryData['ImporterCode'] = $PoDataRow['ImporterCode'];
	if (trim((string) $AryData['ImporterCode']) === '' && stripos((string) $AryData['ExporterName'], 'SAI MARINE') !== false) {
		$AryData['ImporterCode'] = '044601-01127';
	}
	$AryData['NPID'] = isset($CustomInvData['NPID']) ? $CustomInvData['NPID'] : 0;
	$AryData['NotifyPartyName'] = isset($CustomInvData['NotifyPartyName']) ? $CustomInvData['NotifyPartyName'] : '';
	$AryData['NotifyPartyAddress'] = isset($CustomInvData['NPartyAddress']) ? $CustomInvData['NPartyAddress'] : '';
	$AryData['NPartyContactNumber'] = isset($CustomInvData['NPartyContactNumber']) ? $CustomInvData['NPartyContactNumber'] : '';
	$AryData['NPartyFaxNo'] = isset($CustomInvData['NPartyFaxNo']) ? $CustomInvData['NPartyFaxNo'] : '';

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

	// $SqlTotal = "SELECT PS.Uom as Uom, sum(Mcs) as MCS,InvId,  $sqlExt  ,
	// 		sum(Price) as Price,			
	// 		sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
	// 		sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
	// 		`ScrmPoGradeData` 
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
		FROM `ScrmPoGradeData` AS PGD
		LEFT JOIN AdminPackingStyles AS PS ON PGD.PackingStyleID = PS.PCKID $Qry";

	// echo $SqlTotal; echo '<br><br>';

	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];

	$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'";
	$StmtQtyUnit = $iconn->query($SqlQtyUnit);
	$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
	$QtyUnit = $RowQtyUnit['QtyUnit'];


	if ($QtyUnit == 'KGS') {
		$Qty = $RowTotal['NetWgtKgs'];
		$UsdValue = $RowTotal['PriceKgs'];
		$UnitPrice = round($RowTotal['Price'], 6);
	} else {
		if ($QtyUnit == 'LBS' && $RowTotal['Uom'] == 'KGS') {
			$Qty = $RowTotal['NetWgtKgs'];
		} else {
			$Qty = $CanadaLbsFactor > 0 ? $RowTotal['NetWgtLbs'] / $CanadaLbsFactor : $RowTotal['NetWgtLbs'] * 0.454;
		}
		$UsdValue = $RowTotal['PriceLbs'];
		$UnitPrice = round(($UsdValue / $Qty), 6);
	}
	if ($QtyUnit == 'KGS') {
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	} else {
		$NetWgtKgs = $CanadaLbsFactor > 0 ? $RowTotal['NetWgtLbs'] / $CanadaLbsFactor : $RowTotal['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $CanadaLbsFactor > 0 ? $RowTotal['GrossWeightLbs'] / $CanadaLbsFactor : $RowTotal['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	}


	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['UnitPrice'] = $UnitPrice;
	$AryPriceData['Uom'] = $RowTotal['Uom'];
	$AryPriceData['QtyUnit'] = $QtyUnit;


	$AryPriceData['NetWgtKgs'] = $NetWgtKgs;
	$AryPriceData['NetWgtLbs'] = $NetWgtLbs;
	$AryPriceData['GrossWeightKgs'] = $GrossWeightKgs;
	$AryPriceData['GrossWeightLbs'] = $GrossWeightLbs;

	return $AryPriceData;
}


function getBankDetails($iconn, $BankID)
{

	$SqlBank = "SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$AryBankData = $StmtBank->fetch_assoc();

	return $AryBankData;
}
