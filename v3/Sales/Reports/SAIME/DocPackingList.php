<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
require_once __DIR__ . '/Includes/SaimeReportData.php';
require_once __DIR__ . '/Includes/SaimeBreakupRows.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
require_once __DIR__ . '/Includes/SaimeSinglePagePdf.php';


$PoID = $_GET['id'];
$Type = $_GET['Type'];

$SqlCustom = "SELECT ExporterID  FROM  ScrmPoData WHERE PoID='$PoID'";
$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom->fetch_assoc();

$ExpID = $RowCustom['ExporterID'];

$DocType = 'PKL';

class MYPDF extends TCPDF
{

	public function Header()
	{




		global $Header;
		$image_file = 'images/ds_logo.jpg';
		$this->SetFont('helvetica', 'B', 8);
		$this->Cell(100, 0, 'PACKING LIST' . $Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
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
$pdf->SetTitle('PACKING LIST' . $Header);
$pdf->SetTitle('' . $Header2);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(4);
$pdf->SetMargins(8, 8, 8, 5);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();


$TableData = getPackingListNewFormatData($iconn, $PoID);
writeSaimeSinglePage($pdf, $TableData);
$pdf->SetFont('helvetica', '', 6);


$ArrayFilename = getfilename($iconn, $ExpID, $PoID);

$InvShortCode = $ArrayFilename['InvShortCode'];
$CInvNo = $ArrayFilename['CInvNo'];
$FinancialYear = $ArrayFilename['FinancialYear'];

$file_name = $InvShortCode . '-PL-' . $CInvNo . '-' . $FinancialYear . '.pdf';
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





function getPackingListNewFormatData($iconn, $PoID)
{
	$linerName = SaimeReportData::linerName($iconn, $PoID);
	$e = function ($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	};
	$nl = function ($value) use ($e) {
		return nl2br($e($value));
	};
	$date = function ($value) {
		$time = strtotime((string) $value);
		return $time ? date('m/d/Y', $time) : (string) $value;
	};

	$data = getReportData($iconn, $PoID);

	$party = array();
	$stmtParty = $iconn->prepare("SELECT SPD.NotifyPartyID, SNPT.NotifyPartyName, SNPT.NPartyAddress,
            SNPT.NPartyContactNumber, SNPT.NPartyFaxNo, SIR.ImporterName, SIR.ImporterAddress, SIR.ImporterCode,
            SBD.BuyerIRSNumber,
            SCV.PackDescription AS ShipmentPackDescription, SCV.Remarks AS ShipmentRemarks,
            SCV.Note AS ShipmentNote, SCV.NameofRepresentative,
            AED.ExpPhoneNumber, AED.ExpFax, AED.ExpMailID
        FROM ScrmPoData SPD
        LEFT JOIN ScrmNotifyparty SNPT ON SNPT.NotifyPartyID = SPD.NotifyPartyID
        LEFT JOIN ScrmImporterOfRecord SIR ON SIR.ImporterID = SPD.ImporterID
        LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID = SPD.BuyerID
        LEFT JOIN ScrmCustomInvoices SCV ON SCV.PiID = SPD.PoID
        LEFT JOIN AdminExporterDetails AED ON AED.ExpID = SPD.ExporterID
        WHERE SPD.PoID = ? ORDER BY SCV.CInvID DESC LIMIT 1");
	$stmtParty->bind_param('i', $PoID);
	$stmtParty->execute();
	$party = $stmtParty->get_result()->fetch_assoc() ?: array();

	$stmtItems = $iconn->prepare("SELECT CIG.*, APS.PackingStyle, APS.TotalSlabs, APS.SlabWeight,
            APS.MFactor, APS.LbsFactor, APS.Uom, SBM.PrintStatus,
            PGD.BuyerItemCode AS PoBuyerItemCode, PGD.GrossWt AS EnteredGrossWt
        FROM ScrmCIGradeData CIG
        LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CIG.PackingStyleID
        LEFT JOIN ScrmPoGradeData PGD ON PGD.ID = CIG.PoGradeID
        LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName = CIG.Brand
        WHERE CIG.InvId = ?
        ORDER BY CIG.Variety, CIG.PackingStyleID, CIG.Brand, ABS(CIG.Grade)");
	$stmtItems->bind_param('i', $PoID);
	$stmtItems->execute();
	$result = $stmtItems->get_result();

	// Pending POs may not have ScrmCIGradeData yet. In that case, render the
	// original PO breakup rows so the Packing List never produces an empty grid.
	if ($result->num_rows === 0) {
		$stmtItems = $iconn->prepare("SELECT PGD.*, APS.PackingStyle, APS.TotalSlabs, APS.SlabWeight,
                APS.MFactor, APS.LbsFactor, APS.Uom, SBM.PrintStatus,
                PGD.BuyerItemCode AS PoBuyerItemCode, PGD.GrossWt AS EnteredGrossWt
            FROM ScrmPoGradeData PGD
            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PGD.PackingStyleID
            LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName = PGD.Brand
            WHERE PGD.InvId = ?
            ORDER BY PGD.Variety, PGD.PackingStyleID, PGD.Brand, ABS(PGD.Grade)");
		$stmtItems->bind_param('i', $PoID);
		$stmtItems->execute();
		$result = $stmtItems->get_result();
	}
	$rows = array();
	$totalCases = 0;
	$totalKg = 0.0;
	$totalLb = 0.0;
	while ($row = $result->fetch_assoc()) {
		$cases = (int) $row['Mcs'];
		$isGrams = strtoupper(trim((string) $row['Uom'])) === 'GRAMS';
		$kg = $cases * (float) $row['TotalSlabs'] * (float) $row['SlabWeight']
			* (float) $row['MFactor'];
		$lbFactor = $isGrams
			? (float) $row['MFactor'] * 2.2046226218
			: (float) $row['LbsFactor'];
		$lbs = $cases * (float) $row['TotalSlabs'] * (float) $row['SlabWeight'] * $lbFactor;
		$row['_cases'] = $cases;
		$row['_kg'] = $kg;
		$row['_lbs'] = $lbs;
		$rows[] = $row;
		$totalCases += $cases;
		$totalKg += $kg;
		$totalLb += $lbs;
	}

	$isKgs = strtoupper(trim((string) $data['QtyUnit'])) === 'KGS';
	$displayTotal = $isKgs ? $totalKg : $totalLb;
	$displayUnit = $isKgs ? 'KGS' : 'LBS';
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
	$placeOfReceipt = !empty($data['PlaceOfReceipt']) ? $data['PlaceOfReceipt']
		: (!empty($data['PlaceofReceipt']) ? $data['PlaceofReceipt'] : $data['PortOfLoading']);
	$irsNo = trim((string) $party['ImporterCode']);
	if ($irsNo === '')
		$irsNo = trim((string) $party['BuyerIRSNumber']);
	if ($irsNo === '' && stripos((string) $data['ExporterName'], 'SAI MARINE') !== false) {
		$irsNo = '044601-01127';
	}
	$importerName = trim((string) $party['ImporterName']);
	if ($importerName === '')
		$importerName = $data['ExporterName'];
	$importerAddress = trim((string) $party['ImporterAddress']);
	$importerAddress = saimeReportValue($iconn, $PoID, 'ImporterAddress', $importerAddress);
	if ($importerAddress === '')
		$importerAddress = $data['ExporterAddress'];
	$consigneeName = $data['ConsigneeName'];
	$consigneeAddress = trim((string) $data['ConsigneeAddress']);
	if ($consigneeAddress === '') {
		$consigneeLike = '%' . trim((string) $consigneeName) . '%';
		$stmtConsignee = $iconn->prepare('SELECT BuyerName, BuyerAddress FROM ScrmBuyerDetails WHERE BuyerName LIKE ? ORDER BY CHAR_LENGTH(BuyerAddress) DESC LIMIT 1');
		$stmtConsignee->bind_param('s', $consigneeLike);
		$stmtConsignee->execute();
		$consigneeFallback = $stmtConsignee->get_result()->fetch_assoc();
		if ($consigneeFallback && !empty($consigneeFallback['BuyerAddress'])) {
			$consigneeName = $consigneeFallback['BuyerName'];
			$consigneeAddress = $consigneeFallback['BuyerAddress'];
		}
	}
	$processorName = $data['ProcessorName'];
	$processorAddress = $data['ProcessorAddress'];
	$processorName = saimeReportValue($iconn, $PoID, 'ProcessorName', $processorName);
	$processorAddress = saimeReportValue($iconn, $PoID, 'ProcessorAddress', $processorAddress);
	$packingNote = trim((string) $party['ShipmentPackDescription']);
	if ($packingNote === '')
		$packingNote = trim((string) $party['ShipmentRemarks']);
	if ($packingNote === '')
		$packingNote = trim((string) $party['ShipmentNote']);
	if ($packingNote === '')
		$packingNote = trim((string) $data['PackDescription']);
	$exporterContact = '';
	if (!empty($party['ExpPhoneNumber']))
		$exporterContact .= '<br><b>PH NO: ' . $e($party['ExpPhoneNumber']) . '</b>';
	if (!empty($party['ExpFax']))
		$exporterContact .= '<br><b>FAX NO: ' . $e($party['ExpFax']) . '</b>';
	if (!empty($party['NameofRepresentative']) || !empty($party['ExpMailID'])) {
		$exporterContact .= '<br><b>ATTN: ' . $e($party['NameofRepresentative']) . ($party['ExpMailID'] !== '' ? ' : Email:' . $e($party['ExpMailID']) : '') . '</b>';
	}
	$notifyText = '';
	if (!empty($party['NotifyPartyName']))
		$notifyText .= '<b>' . $e(strtoupper($party['NotifyPartyName'])) . '</b>';
	if (!empty($party['NPartyAddress']))
		$notifyText .= '<br><b>' . $nl(strtoupper($party['NPartyAddress'])) . '</b>';
	if (!empty($party['NPartyContactNumber']))
		$notifyText .= '<br><b>TEL: ' . $e($party['NPartyContactNumber']) . '</b>';
	if (!empty($party['NPartyFaxNo']))
		$notifyText .= (!empty($party['NPartyContactNumber']) ? ', ' : '<br>') . '<b>FAX: ' . $e($party['NPartyFaxNo']) . '</b>';

	// Same product + packing style + brand is one group: the packages count
	// and the product/pack/brand/BL/packing-note text print once. Production
	// and best-before dates stay per row since they can differ by grade/lot.
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

	// Prepare one display row per product for the shared aligned-row renderer.
	$breakupRows = array();
	$seenGroups = array();
	foreach ($rows as $index => $row) {
		$description = trim((string) ($row['CIDescription'] !== '' ? $row['CIDescription'] : $row['Variety']));
		$itemCode = trim((string) $row['PoBuyerItemCode']);
		$brand = trim((string) $row['Brand']);

		$groupKey = $row['Variety'] . '|' . $row['PackingStyleID'] . '|' . $row['Brand'];
		$isFirstInGroup = !isset($seenGroups[$groupKey]);
		$seenGroups[$groupKey] = true;

		$marks = $index === 0
			? '<b><u>CONTAINER NO &amp; SEAL NO</u><br><br>' . $e($data['ContainerNo']) . '<br>' . $e($data['SealNo']) . '</b>'
			: '';
		$productionDateLine = !empty($row['ProductionDate']) ? 'PRODUCTION DATE: ' . $e($date($row['ProductionDate'])) : '';
		$bestBeforeDateLine = !empty($row['BestBeforeDate']) ? 'BEST BEFORE DATE: ' . $e($date($row['BestBeforeDate'])) : '';
		$ownDateLines = implode('<br>', array_filter(array($productionDateLine, $bestBeforeDateLine)));

		$descriptionHtml = $isFirstInGroup
			? '<b>' . number_format($groupCases[$groupKey]) . ' M/CARTONS OF ' . $nl($description)
			. (trim((string) $data['CerificationName']) !== '' ? ' (' . $e($data['CerificationName']) . ')' : '')
			. '<br>PACK: ' . $e($row['PackingStyle'])
			. ($brand !== '' ? '<br>BRAND: ' . $e($brand) : '')
			. (!empty($data['DataLogger']) ? '<br><br>TEMPERATURE NUMBER: ' . $e($data['DataLogger']) : '')
			. ($packingNote !== '' ? '<br><br>' . $nl(strtoupper($packingNote)) : '')
			. ($ownDateLines !== '' ? '<br><br>' . $ownDateLines : '') . '</b>'
			: ($ownDateLines !== '' ? '<b>' . $ownDateLines . '</b>' : '');

		$breakupRow = array(
			0 => $marks,
			2 => $descriptionHtml,
			3 => '<b>' . $e($itemCode) . '</b>',
			4 => '<b>' . $e($row['Grade']) . '</b>',
			5 => '<b>' . number_format($row['_cases']) . '</b>',
			6 => '<b>' . number_format($isKgs ? $row['_kg'] : $row['_lbs'], 3) . '</b>'
		);
		if ($isFirstInGroup) {
			$breakupRow[1] = array('html' => '<b>' . number_format($groupCases[$groupKey]) . '<br>M/Cartons</b>', 'rowspan' => $groupCounts[$groupKey]);
		}

		$breakupRows[] = $breakupRow;
	}
	$html = '<style>
        table{border-collapse:collapse;font-family:helvetica;font-size:6.1pt;color:#000}
        td,th{padding:1px 2px;line-height:1.07}
        .label{font-size:5.5pt}
        .small{font-size:5.2pt;line-height:1.04}
    </style>';
	$html .= '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
	$html .= '<tr><th colspan="10" align="center" style="font-size:12pt;height:18px"><b>PACKING LIST</b></th></tr>';
	$html .= '<tr><td colspan="5" rowspan="3" style="height:78px"><span class="label">Exporter:</span><br><b>' . $e(strtoupper($data['ExporterName'])) . '</b><br><b>' . $nl(strtoupper($data['ExporterAddress'])) . '</b>' . $exporterContact . '</td>';
	$html .= '<td colspan="5"><span class="label">INVOICE No. &amp; Date :</span><br><b>' . $e($data['InvNo']) . ' DT: ' . $e($data['InvDate']) . '</b></td></tr>';
	$html .= '<tr><td colspan="5"><span class="label">Buyer&#39;s order no &amp; date</span><br><b>PO :' . $e($data['PoNo']) . ' ' . $e($data['PoDate']) . '</b></td></tr>';
	$html .= '<tr><td colspan="5"><span class="label">Other Reference(s) :</span><br><b>IE CODE: ' . $e($data['ExporterIeCode']) . '<br>USFDA BIOTERRORISM REGN NO: ' . $e($data['ProcessorUsfdaNo']) . '<br>APPROVAL NO : ' . $e($data['ProcessorApprovalNo']) . '</b></td></tr>';
	$html .= '<tr><td colspan="5" rowspan="2" style="height:116px"><b><u>IMPORTER OF RECORD</u></b><br><b>' . ($irsNo !== '' ? 'IRS NO:' . $e($irsNo) . '.<br>' : '') . $e(strtoupper($importerName)) . '</b><br><b>' . $nl(strtoupper($importerAddress)) . '</b><br><b><u>NOTIFY PARTY :</u></b><br>' . $notifyText . '</td>';
	$html .= '<td colspan="5" style="height:94px"><span class="label">Consignee :</span><br><b>' . $e(strtoupper($consigneeName)) . '</b><br><b>' . $nl(strtoupper($consigneeAddress)) . '</b></td></tr>';
	$html .= '<tr><td colspan="5" cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td width="50%" align="center" style="border-right:1px solid #000"><span class="label">Country of Origin of Goods :</span><br><b>' . saimeReportHtml($iconn, $PoID, 'CountryOfOrigin', 'INDIA') . '</b></td><td width="50%" align="center"><span class="label">Country of Final Destination :</span><br><b>' . $e(strtoupper($data['DestinationCountry'])) . '</b></td></tr></table></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">PRE-CARRIAGE BY*</span><br><b>CONTAINER</b></td><td colspan="3"><span class="label">PLACE OF RECEIPT BY *</span><br><b>' . $e(strtoupper($placeOfReceipt)) . '</b></td><td colspan="5" rowspan="3"><b><u>Terms of Delivery &amp; Payment :</u></b><br><b>' . $nl(strtoupper($data['PaymentTerms'])) . '</b><br><b>TRANSPORT TERMS: ' . $e(strtoupper($data['DeliveryTerms'])) . '</b></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">VESSEL VOYAGE NO</span><br><b>' . $e(strtoupper($data['VesselNumber'])) . '</b></td><td colspan="3"><span class="label">PORT OF LOADING</span><br><b>' . $e(strtoupper($data['PortOfLoading'])) . '</b></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">PORT OF DISCHARGE</span><br><b>' . $e(strtoupper($data['PortOfDischarge'])) . '</b></td><td colspan="3"><span class="label">FINAL DESTINATION</span><br><b>' . $e($finalDestination) . '</b></td></tr>';

	$html .= '<tr><th width="12%">Marks &amp; Nos.<br>Container No.</th><th width="10%">No of Kind<br>Packages</th><th width="37%">Description of Goods</th><th width="7%">ITEM<br>CODE</th><th width="9%">SIZE<br>COUNT/LB</th><th width="8%">No. Of<br>CTNS</th><th width="17%">QTY IN<br>' . $displayUnit . '</th></tr>';
	$breakupColumns = array(
		array('width' => '12%'),
		array('width' => '10%', 'align' => 'center'),
		array('width' => '37%', 'borderAfter' => true),
		array('width' => '7%', 'align' => 'center', 'borderAfter' => true),
		array('width' => '9%', 'align' => 'center', 'borderAfter' => true),
		array('width' => '8%', 'align' => 'center', 'borderAfter' => true),
		array('width' => '17%', 'align' => 'center')
	);

	$html .= '<tr><td colspan="7" cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%">';
	$html .= renderSaimeBreakupRows($breakupRows, $breakupColumns, 250);
	$html .= '<tr><td colspan="3" width="59%" style="height:20px;border-right:1px solid #000"></td>';
	$html .= '<td width="7%" style="height:20px;border-right:1px solid #000"></td><td width="9%" style="height:20px;border-right:1px solid #000"></td>';
	$html .= '<td width="8%" align="center" style="height:20px;border-top:1px solid #000;border-right:1px solid #000"><b>' . number_format($totalCases) . '</b></td>';
	$html .= '<td width="17%" align="center" style="height:20px;border-top:1px solid #000"><b>' . number_format($displayTotal, 3) . '</b></td></tr>';
	$html .= '</table></td></tr>';
	$cartonWords = strtoupper(numberTowords($totalCases));
	$html .= '<tr><td colspan="7" cellpadding="0" style="height:76px"><table border="0" cellpadding="1" cellspacing="0" width="100%"><tr><td colspan="2"><b>TOTAL MASTER CARTONS ' . $e($cartonWords) . ' ONLY</b></td></tr><tr><td width="60%"></td><td width="40%" align="right"><b>KGS</b></td></tr><tr><td width="60%"><b>NET WEIGHT:</b></td><td width="40%" align="right"><b>' . number_format($totalKg, 3) . '</b></td></tr><tr><td width="60%"><b>GROSS WEIGHT:</b></td><td width="40%" align="right"><b>' . number_format($grossKg, 3) . '</b></td></tr></table></td></tr>';
	$html .= '<tr nobr="true"><td colspan="4" class="small" style="height:76px;border:1px solid #000"><b><u>PROCESSED AND PACKED BY :</u></b><br><b>' . $e(strtoupper($processorName)) . '</b><br>' . $nl(strtoupper($processorAddress)) . '<br><br>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct' . '</td>';
	$html .= '<td colspan="3" align="center" valign="bottom" style="height:76px;border:1px solid #000"><span class="label">Signature &amp; Date</span><br><b>FOR ' . $e(strtoupper($data['ExporterName'])) . '</b><br><br>' . (!empty($data['AuthSign']) ? '<b>' . $e($data['AuthSign']) . '</b><br>' : '') . '<br><br><br><b>Authorized Signatory</b></td></tr>';

	$html .= '</table>';
	return $html;
}

function getCustomInvData($iconn, $PoID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");



	$AryData = getReportData($iconn, $PoID);

	// echo json_encode($AryData, JSON_PRETTY_PRINT);
	// exit;

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
	$PackDescription = $AryData['PackDescription'];
	$BenfName = $AryData['BenfName'];
	$BenfAccNo = $AryData['BenfAccNo'];
	$BenfBankName = $AryData['BenfBankName'];
	$BenfBankAddress = $AryData['BenfBankAddress'];
	$BenfBankCode = $AryData['BenfBankCode'];
	$AdvanceAmount = $AryData['AdvanceAmount'];
	$AMCDeductionUSD = $AryData['AMCDeductionUSD'];
	$AMCDeductionINR = $AryData['AMCDeductionINR'];
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
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];
	$ShowContent = $AryData['ShowContent'];
	$ShowBankDetails = $AryData['ShowBankDetails'];
	$ShowPoNo = $AryData['ShowPoNo'];
	$showSignature = $AryData['showSignature'];
	$ShowRemarks = $AryData['ShowRemarks'];
	$ShowGrossweight = $AryData['ShowGrossweight'];



	$showProUsFda = $AryData['showProUsFda'];
	$showProFEI = $AryData['showProFEI'];
	$PortOfLoading = $AryData['PortOfLoading'];
	$showAdvance = $AryData['showAdvance'];

	$ContainerNo = $AryData['ContainerNo'];
	$SealNo = $AryData['SealNo'];



	$showIgst = $AryData['showIgst'];
	$showDiscount = $AryData['showDiscount'];

	$AryPaymentData = getPaymentValues($iconn, $PoID);
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


	/*	$content .= '		<tr>  

				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				<br>
				</font></th>

				</tr>

				 '; */


	if ($BuyerID == '' || $BuyerID == 0) { {
			$content .= '		<tr>  
						   
						<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
						<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '
						
						
						</font></th>
						</tr>';
		}
	} else if ($ConsigneeID != $BuyerID) {
		$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '<br>
				<b><u>Applicant</u></b><br>
				' . $BuyerName . ',<br>' . nl2br($BuyerAddress) . '
				</font></th>
				</tr>';
	} else {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
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

	/*if($ShippingTerms=='FOB'){
					$FinalDestination=$PortOfLoading;
				}
				*/

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
				<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN # ' . $ProcessorIeCustomsNo . '
				<br> ' . nl2br($ExporterRecAdd) . ' </font></th>
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
				</b><br>
				 SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($FinalDestination) . '';

	if ($ShowDeliveryTerms) {
		$content .= '(' . $DeliveryTerms . ')';
	}


	/*if($ShowInsurance){
					$content .= '<BR><b> INSURANCE: '.number_format($InsuranceValue,2).'</b>'; 
				 }*/
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
				<font size="7">Quantity in ' . $QtyUnit . '</font></th>';
	if ($ShowGrossweight) {

		$content .= '  <th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Gross Weight<br>in KGS  </font></th>';
	}

	if ($ShowRemarks) {

		$content .= '  <th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Remarks </font></th>';
	}



	$content .= '  </tr>
				
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
			
			</tr>
			';

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

		/*	if($ShowBrand){
			$content.='NET WEIGHT PER CARTON.'.$Whitspace.'<B> BRAND </B>: '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
			}*/


		$content .= ' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>
			

			
			<tr>
			
			<th align="center" width="40%" ><font size="' . $FontSize . '"> <u>' . $TextSize . '</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="28%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';


		$SqlVariety = "SELECT * FROM `ScrmCIGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'
			and Variety='$Variety' and Brand='$Brand'  
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";
		$StmtVariety = $iconn->query($SqlVariety);

		while ($RowVariety = $StmtVariety->fetch_assoc()) {

			//$AryPriceBreakUp=getBreakUpData($iconn,$RowVariety['ID']);
			$AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['PoGradeID']);
			$Qty = $AryPriceBreakUp['Qty'];
			$UsdValue = $AryPriceBreakUp['UsdValue'];
			$TotalMcs = $AryPriceBreakUp['TotalMcs'];
			$Price = $AryPriceBreakUp['Price'];
			$UnitPrice = $AryPriceBreakUp['UnitPrice'];
			//$GrossWeightKgs=$AryPriceBreakUp['GrossWeightKgs'];
			$GrossWeight = $AryPriceBreakUp['GrossWeight'];




			$content .= '
				<tr>
				<td align="center" width="40%"  ><font size="' . $FontSize . '">
				' . $RowVariety['Grade'] . '</font></td>
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . $TotalMcs . '</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($Qty, 3) . '	</font></td>';

			if ($ShowGrossweight) {
				$content .= '<td align="center" width="28%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($GrossWeight, 3) . '</font></td>';
			}

			$content .= '</tr>';
		}
	}




	//$AryPriceData=getBreakUpData($iconn,$RowVariety['ID'],$PoID);
	$AryPriceData = getBreakUpData($iconn, $RowVariety['PoGradeID'], $PoID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];
	//$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
	$GrossWeight = $AryPriceData['GrossWeight'];



	$content .= '
				<tr>
				
				<td align="center" width="40%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black;border-bottom: 1px solid black; border-right: 1px solid black;" ><font size="7">
				' . $TotalMcs . '</font></td>
				
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;"><font size="7">
				' . number_format($Qty, 3) . '</font></td>';

	if ($ShowGrossweight) {
		$content .= '<td align="center" width="28%" style="border-top: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($GrossWeight, 3) . '</font></td>';
	} else {
		$content .= '
				
				<td align="center" width="28%" style="border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;"><font size="7">
				</font></td>';
	}


	$content .= '</tr>';

	//$AryPaymentData=getPaymentValues($iconn,$PoID);
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$DiscountPerc = $AryPaymentData['DiscountPerc'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$IgstPerc = $AryPaymentData['IgstPerc'];
	$IgstValue = $AryPaymentData['IgstValue'];

	$InvValueUSD = $AryPaymentData['InvValueUSD'] - $AdvanceAmount;
	//$InvValueInr=$AryPaymentData['InvValueINR'];
	$InvValueInr = $InvValueUSD * $ExchangeRate;




	$content .= '</table>
			  </tr>';

	$BILL_WORD = numberTowords($InvValueUSD);

	$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
	$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
	$DutiesPrice = $AryPaymentData['DutiesPrice'];
	$FOBValueUSD = $AryPaymentData['FOBValueUSD'];



	/*$NetWgtKgs=$AryPriceData['NetWgtKgs'];
$NetWgtLbs=$AryPriceData['NetWgtLbs'];
$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];*/





	// $content.='<tr>
	// <table border ="0" cellspacing="0" cellpadding="0">'; 
	// $content.='<tr>
	// <td><font size="7"><br> SHIPPING TERMS   : '.$ShippingTerms.'  '.strtoupper($FinalDestination).'
	// <BR>'.str_replace(array("\r\n"," \r", " \n"), "<br>",$PackDescription).' 
	// </font></td>
	// </tr>';	
	$content .= '<tr>
			<table border ="0" cellspacing="0" cellpadding="0">';
	$content .= '<tr>
			<td><font size="7"><br> SHIPPING TERMS   : ' . $ShippingTerms . '  ' . strtoupper($FinalDestination) . '
			<BR>' . nl2br($PackDescription) . ' 
            </font></td>
			</tr>';



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



	$content .= '</tr>
				
						
	        
				<tr>  
           		<!--<th  align="LEFT"  width="100%"><font size="7"> AMOUNT CHARGABLE IN US.$: 
				<b>' . $BILL_WORD . '</b><BR></font></th>  -->  
				
				 
				
                
</tr>
<tr>
<th  align="bottom"  width="70%"><font size="8"></font></th>';

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

	$CustomInvData = SaimeReportData::packingInvoice($iconn, $PoID);
	$IGST = $CustomInvData['IGST'];
	$OverSeasCommission = $CustomInvData['OverSeasCommission'];
	$CInvDate = $CustomInvData['CInvDate'];
	$CInvCode = $CustomInvData['CInvCode'];
	$BLDate = getFormatedDateNA($CustomInvData['BLDate']);
	$SBDate = getFormatedDateNA($CustomInvData['ShippingBillDate']);
	$BLNo = $CustomInvData['BLNo'];
	$DataLogger = isset($CustomInvData['DataLogger']) ? trim((string) $CustomInvData['DataLogger']) : '';
	$SBNo = $CustomInvData['ShippingBillNo'];
	$LcNumber = $CustomInvData['LcNo'];
	$LcDate = getFormatedDateNA($CustomInvData['LcDate']);
	$CIDescription = $CustomInvData['CIDescription'];
	$PackDescription = $CustomInvData['ComRemarks'];
	$TotalGrossWt = $CustomInvData['TotalGrossWt'];

	$SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 	PIID='$PoID' order by TspID asc LIMIT 1";
	//exit;
	$StmtOrgVessel = $iconn->query($SqlOrgVessel);
	$RowOrgVessel = $StmtOrgVessel->fetch_assoc();

	$OrgVesselName = $RowOrgVessel['VesselName'];
	$OrgVOYNO = $RowOrgVessel['VOYNO'];

	$VesselNumber = $OrgVesselName . ' ' . $OrgVOYNO;
	//$VesselNumber=$CustomInvData['VesselName'].' '.$CustomInvData['VOY'];


	$SqlDedINR = "SELECT * FROM `ScrmDatewiseValues` where FromDate<='$CInvDate' and Type= 'AMCDeductionINR' ORDER BY ID DESC limit 1";

	$SqlDedUSD = "SELECT * FROM `ScrmDatewiseValues` where FromDate<='$CInvDate' and Type= 'AMCDeductionUSD' ORDER BY ID DESC limit 1";

	$StmtDedINR = $iconn->query($SqlDedINR);
	$StmtDedUSD = $iconn->query($SqlDedUSD);

	$RowDedINR = $StmtDedINR->FETCH_ARRAY();
	$RowDedUSD = $StmtDedUSD->FETCH_ARRAY();





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
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode 
	FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country 
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms

 WHERE PoID='$PoID'";


	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ApprovedBy = $PoDataRow['ApprovedBy'];
	$LcStatus = $PoDataRow['LcStatus'];
	$CountryID = $PoDataRow['DestinationCountry'];


	//  $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
	// $StmtLogin=$iconn->query($SqlLogin); 
	// $RowLogin = $StmtLogin->fetch_assoc();
	// $SignURL=$RowLogin['SignURL'];

	// 		$ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
	// $ProcessorData=getExporterDetails($ProcessorID,$PoID,$CountryID,$DocType);

	$ExporterData = getExporterDetails($ExpID, $CountryID, $DocType);
	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);



	if ($CustomInvData['BankID'] == 0) {
		$BankID = $PoDataRow['BankID'];
	} else {
		$BankID = $CustomInvData['BankID'];
	}

	$SqlBank = "SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$RowBank = $StmtBank->fetch_assoc();
	$BenfName = $RowBank['AccntName'];
	$BenfAccNo = $RowBank['AccountNumber'];
	$BenfBankName = $RowBank['BankName'];
	$BenfBankAddress = $RowBank['BankAddress'];
	$BenfBankCode = $RowBank['SwiftCode'];


	/*if($CustomInvData['Discount']!=0){
		$AryData['showDiscount']=true;	
		}else{
		$AryData['showDiscount']=false;	
		}	*/

	/*if($CustomInvData['IGST']=='DISCOUNT'){
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
		$AryData['ShowShippingTerms'] = false;	//uma
	} else {
		$AryData['ShowShippingTerms'] = false;
	}

	/*if($PoDataRow['DestinationCountry']!=26 && $BankID!=5){
		$AryData['ShowBankDetails']=true;	//uma
		}
		else{
		$AryData['ShowBankDetails']=false;	
		}

		if($PoDataRow['DestinationCountry']!=26 && $BankID==5){
		$AryData['ShowContent']=true;	//uma
		}else{
		$AryData['ShowContent']=false;	
		}
		*/


	/*if($PoDataRow['ShippingTerms']=='FOB'){
		$AryData['ShowInvoicevalue']=true;	//uma
		}else{
		$AryData['ShowInvoicevalue']=false;	
		}*/



	if ($PoDataRow['DestinationCountry'] == 157) {
		$AryData['showExpUsFda'] = true;
	} else {
		$AryData['showExpUsFda'] = false;
	}


	if ($ExpID == 5 || $PoDataRow['DestinationCountry'] != 26) {
		$AryData['showExpIECode'] = true;
		$AryData['showExpGSTNo'] = true;
	} else {
		$AryData['showExpIECode'] = false;
		$AryData['showExpGSTNo'] = false;
	}



	if ($PoDataRow['DestinationCountry'] == 157) {
		$AryData['showProUsFda'] = true;
	} else {
		$AryData['showProUsFda'] = false;
	}

	if ($PoDataRow['DestinationCountry'] != 26) {
		$AryData['showSignature'] = true;
		$AryData['showProFEI'] = true;

		$AryData['ShowDeliveryTerms'] = true;
		$AryData['ShowPoNo'] = true;
	} else {
		$AryData['showSignature'] = false;
		$AryData['showProFEI'] = false;

		$AryData['ShowDeliveryTerms'] = false;
		$AryData['ShowPoNo'] = false;
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



	if ($CustomInvData['AdvanceAmount'] > 0) {
		$AryData['showAdvance'] = true;
	} else {
		$AryData['showAdvance'] = false;
	}



	if ($PoDataRow['PoNo'] == '') {
		$AryData['PoDate'] = '';
	} else {
		$AryData['PoDate'] = "& Dated :" . getFormatedDate($PoDataRow['PoDate']);
	}

	$AryData['UOM'] = $QtyUnit;

	if ($PoDataRow['DestinationCountry'] == 26) {
		// $AryData['UOM'] = 'KGS';
		$AryData['ShowGrossweight'] = true;
		$AryData['ShowRemarks'] = false;
		$AryData['TextSize'] = 'SIZE(PCS/LB)';
		//uma
	} else {
		// $AryData['UOM'] = 'LBS';
		$AryData['ShowGrossweight'] = false;
		$AryData['ShowRemarks'] = true;
		$AryData['TextSize'] = 'SIZE';
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
	$AryData['DataLogger'] = $DataLogger;
	$AryData['SBNo'] = $SBNo;
	$AryData['LcNumber'] = $LcNumber;
	$AryData['LcDate'] = $LcDate;
	$AryData['CIDescription'] = $CIDescription;
	$AryData['PackDescription'] = $PackDescription;
	$AryData['BenfName'] = $BenfName;
	$AryData['BenfAccNo'] = $BenfAccNo;
	$AryData['BenfBankName'] = $BenfBankName;
	$AryData['BenfBankAddress'] = $BenfBankAddress;
	$AryData['BenfBankCode'] = $BenfBankCode;
	$AryData['AdvanceAmount'] = $CustomInvData['AdvanceAmount'];
	$AryData['AMCDeductionINR'] = $RowDedINR['Value'];
	$AryData['AMCDeductionUSD'] = $RowDedUSD['Value'];
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



	$AryData['ConsigneeID'] = $PoDataRow['ConsigneeID'];
	$AryData['BuyerID'] = $PoDataRow['BuyerID'];
	$AryData['ConsigneeName'] = $PoDataRow['ConsigneeName'];
	$AryData['ConsigneeAddress'] = $PoDataRow['ConsigneeAddress'];
	$AryData['BuyerName'] = $PoDataRow['BuyerName'];
	$AryData['BuyerAddress'] = $PoDataRow['BuyerAddress'];
	$AryData['PoNo'] = $PoDataRow['PoNo'];
	$AryData['QtyUnit'] = $PoDataRow['QtyUnit'];



	$AryData['PortOfDischarge'] = strtoupper($CustomInvData['PortofDischarge']) . ' ' . strtoupper($CustomInvData['CountryCode']) . ', ' . $CustomInvData['PortofDischargeCountry'];


	if ($PoDataRow['DestinationCountry'] == 26) {

		//$AryData['PlaceOfReceiptBy']=str_ireplace("PORT"," ",$CustomInvData['PlaceOfRecipt']);
		$AryData['PlaceofReceipt'] = str_ireplace("PORT", " ", strtoupper($CustomInvData['PortOfLoading'])) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
		$AryData['PortOfLoading'] = str_ireplace("PORT", " ", strtoupper($CustomInvData['PortOfLoading'])) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	} else {
		$AryData['PlaceOfReceipt'] = $CustomInvData['PlaceOfReceipt'] != '' ? $CustomInvData['PlaceOfReceipt'] : 'ANY PORT, INDIA';
		// $AryData['PlaceofReceipt'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
		$AryData['PortOfLoading'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	}


	$AryData['FinalDestination'] = $CustomInvData['FinalDestination'] . ' ' . $CustomInvData['FinalDestinationCountryCode'] . ' ,' . $CustomInvData['FinalDestinationCountry'];



	$AryData['InvNo'] = $CustomInvData['CustomsInvNo'];
	$AryData['InvDate'] = getFormatedDate($CInvDate);
	$AryData['PoNo'] = $PoDataRow['PoNo'];



	$AryData['ProcessorName'] = saimeReportValue($iconn, $PoID, 'ProcessorName', $ProcessorData['ExpName']);
	$AryData['ProcessorAddress'] = !empty($ProcessorData['PackerAddress'])
		? $ProcessorData['PackerAddress']
		: $ProcessorData['ExpAddress'];
	$AryData['ProcessorUsfdaNo'] = $ProcessorData['ExpUsfdaNo'];
	$AryData['ProcessorFEI'] = $ProcessorData['ExpFeiNo'];
	$AryData['ProcessorApprovalNo'] = $ProcessorData['ExpApprovalNo'];
	$AryData['ProcessorIeCustomsNo'] = $ProcessorData['ExpIeCustomsNo'];


	$AryData['ShippingTerms'] = $PoDataRow['ShippingTerms'];
	$AryData['PaymentTerms'] = $PoDataRow['PaymentName'];
	$AryData['DestinationCountry'] = $PoDataRow['name'];
	$AryData['DeliveryTerms'] = $PoDataRow['DeliveryTerms'];
	//$AryData['QtyUnit']=$PoDataRow['QtyUnit'];
	$AryData['ShipByDate'] = $PoDataRow['ShipByDate'];
	$AryData['SignURL'] = $SignURL;
	$AryData['PreparedBy'] = $PoDataRow['PreparedBy'];
	$AryData['BankID'] = $PoDataRow['BankID'];
	$AryData['IGST'] = $IGST;
	$AryData['CInvCode'] = $CInvCode;



	$AryData['ContainerNo'] = $CustomInvData['ContainerNo'];
	//$AryData['SealNo']=$CustomInvData['SealNo'];
	$AryData['SealNo'] = $CustomInvData['LinerSealNo'];


	return $AryData;
}


function getBreakUpData($iconn, $GradeID, $PiID)
{

	if ($PiID == '') {
		$Qry = "WHERE PGD.ID in ($GradeID)";
	} else {
		$Qry = "WHERE InvId='$PiID'";
	}

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

	// $SqlTotal="SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
	// sum(Price) as Price,			
	// sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
	// sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
	// `ScrmCIGradeData` 
	// as PGD left join AdminPackingStyles as PS 
	// on PGD.PackingStyleID=PS.PCKID $Qry"; 

	$SqlTotal = "SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,sum(GrossWt) as EnteredGrossWt,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];

	$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'";
	$StmtQtyUnit = $iconn->query($SqlQtyUnit);
	$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
	$QtyUnit = $RowQtyUnit['QtyUnit'];
	$UnitPrice = number_format($RowTotal['Price'], 2);


	if ($QtyUnit == 'KGS') {
		$Qty = $RowTotal['NetWgtKgs'];
		$UsdValue = $RowTotal['PriceKgs'];
		$UnitPrice = number_format($RowTotal['Price'], 2);
	} else {
		//$Qty=$RowTotal['NetWgtLbs']*0.454;
		$Qty = $RowTotal['NetWgtLbs'];
		$UsdValue = $RowTotal['PriceLbs'];
		$UnitPrice = round(($UsdValue / $Qty), 6);
	}

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['UnitPrice'] = $UnitPrice;

	/*$AryPriceData['NetWgtKgs']=$RowTotal['NetWgtKgs'];
			$AryPriceData['NetWgtLbs']=$RowTotal['NetWgtLbs'];
			$AryPriceData['GrossWeightKgs']=$RowTotal['GrossWeightKgs'];
			$AryPriceData['GrossWeightLbs']=$RowTotal['GrossWeightLbs'];*/
	$EnteredGrossWt = $RowTotal['EnteredGrossWt'];
	if ($QtyUnit == 'KGS') {
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	} else {
		$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $RowTotal['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	}


	$AryPriceData['NetWgtKgs'] = $NetWgtKgs;
	$AryPriceData['NetWgtLbs'] = $NetWgtLbs;
	$AryPriceData['GrossWeightKgs'] = $GrossWeightKgs;
	$AryPriceData['GrossWeightLbs'] = $GrossWeightLbs;

	if ($EnteredGrossWt != 0) { //uma
		$AryPriceData['GrossWeight'] = $EnteredGrossWt;
	} else {
		$AryPriceData['GrossWeight'] = $GrossWeightKgs;
	}

	return $AryPriceData;
}


function getBankDetails($iconn, $BankID)
{

	$SqlBank = "SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$AryBankData = $StmtBank->fetch_assoc();

	return $AryBankData;
}
