<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
require_once __DIR__ . '/Includes/SaimeSinglePagePdf.php';
include($cp . "Includes/IncludePage.php");
require_once __DIR__ . '/Includes/SaimeReportData.php';
require_once __DIR__ . '/Includes/SaimeBreakupRows.php';
include_once ROOT . 'config/api-call.php';

/** Folder Includes **/



$ID = isset($_GET['ID']) ? trim((string) $_GET['ID']) : (isset($_GET['id']) ? trim((string) $_GET['id']) : '');
if ($ID === '') {
	http_response_code(400);
	exit('Proforma invoice ID is required.');
}
$Type = isset($_GET['Type']) ? $_GET['Type'] : '';
$PoID = $ID;
$DocType = 'PI';
//echo $_SESSION["SignURL"]; exit;

$SqlStatus = "SELECT * FROM ScrmPIData  WHERE PoID='$PoID'";
$StmtStatus = $iconn->query($SqlStatus);
$PoStatus = $StmtStatus->fetch_assoc();

$SqlScrmPoData = "SELECT *,SPD.NotifyPartyID AS NPID,SCDB.BuyerName as BuyerName,SCDB.BuyerAddress as BuyerAddress,
SCD.ConsigneeName as ConsigneeName,
	SCD.ConsigneeName as ConsigneeName,
	 COALESCE(
        (
            SELECT Address 
            FROM ScrmConsigneeAddressHistory AS SCAH
            WHERE SCAH.ConID = SCD.ConID AND SCAH.EffectiveFrom <= SPD.PoDate
            ORDER BY SCAH.EffectiveFrom DESC
            LIMIT 1
        ),
        SCD.ConsigneeAddress
    ) AS ConsigneeAddress,
SPR.name as PlaceofReceiptName,
SCDB.BuyerIRSNumber AS BuyerIRSNumber,
SC.name as PortofDischargeCountry ,
SPC.CountryCode as CountryCode ,SPCF.CountryCode as FinalDestinationCountryCode ,SPC.Name as PortOfDischarge,SPCF.Name as FinalDestination,SCF.name as FinalDestinationCountry,
SOC.CerificationName as CerificationName, SCFD.name AS FinalDestinationCountryName, SPD.Remarks AS PIRemarks
FROM ScrmPIData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmNotifyparty as SNPT on SPD.NotifyPartyID=SNPT.NotifyPartyID
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPortCities as SPCF on SPD.FinalDestination=SPCF.PortId
 left JOIN ScrmPortCities as SPR on SPD.PlaceofReceipt=SPR.PortId
 left JOIN ScrmCountriesList as SCF on SCF.id=SPCF.Country
 LEFT JOIN ScrmCountriesList AS SCFD ON SCFD.id = SPD.FinalDestinationCountry
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
  left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
  left JOIN ScrmOrderCertification as SOC on SOC.CerificationID =SPD.Certification
  



 WHERE PoID='$PoID'";

// echo $SqlScrmPoData;
// exit;

$StmtPoData = $iconn->query($SqlScrmPoData);
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID = $PoDataRow['ExporterID'];
$ProcessorID = $PoDataRow['ProcessorID'];
$ApprovedBy = $PoDataRow['ApprovedBy'];
$CountryID = $PoDataRow['DestinationCountry'];
$BuyerID = $PoDataRow['ConsigneeID'];
$LoginId = $PoDataRow['LoginId'];
$Certification = $PoDataRow['CerificationName'];
$InvDate = $PoDataRow['InvDate'];
$DestinationCountryId = $PoDataRow['DestinationCountry'];

/***Api call to get signature */
$api = API_DIGITAL_SIGNATURE;

// $bodyContent = array("ApprovedByID" => $ApprovedBy);
$bodyContent = array("ApprovedByID" => $ApprovedBy, "LoginID" => $LoginId, "Type" => "Sign");
$resgetDigitalSignature = CallAPI($api, "GET", "", $bodyContent);

$result = $resgetDigitalSignature->Data;
//var_dump($result); exit;

$SignURL = $result->SignURL;

/***Api call to get signature */


/***Api call to get prepared by */
$api = API_DIGITAL_SIGNATURE;

// $bodyContent = array("ApprovedByID" => $ApprovedBy);
$bodyContent = array("ApprovedByID" => $ApprovedBy, "LoginID" => $LoginId, "Type" => "Preparedby");
$resgetPreparedby = CallAPI($api, "GET", "", $bodyContent);

$result = $resgetPreparedby->Data;
//var_dump($result); exit;

$Preparedby = $result->Preparedby;


//$Preparedby=getUserDetails($LoginId);
/***Api call to get prepared by */




// $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
// $StmtLogin=$iconn->query($SqlLogin); 
// $RowLogin = $StmtLogin->fetch_assoc();
// $SignURL=$RowLogin['SignURL'];


// $ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
// $ProcessorData=getExporterDetails($ProcessorID,$PoID,$CountryID,$DocType);

$ExporterData = getExporterDetails($ExpID, $CountryID, $DocType);
$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);
// echo json_encode($ExporterData, JSON_PRETTY_PRINT);
// echo json_encode($ProcessorData, JSON_PRETTY_PRINT);
//$ProcessorData=getExporterDetails($ProcessorID);
//print_r($ProcessorData);

$PaymentTerms = $PoDataRow['PaymentTerms'];
$DeliveryTerms = $PoDataRow['DeliveryTerms'];
$BankID = $PoDataRow['BankID'];
$Status = $PoStatus['Status'];

//ROBO
if ($PoDataRo['NPID'] != 0) {
	$showNotifyParty = true;
} else {
	$showNotifyParty = false;
}


// $showNotifyParty;


//ROBO

//require_once('../tcpdf/tcpdf.php'); 


$CanadaLbsFactor = $DestinationCountryId == 23 && $InvDate >= '2026-03-01' ? 2.2046 : 'LbsFactor';

class MYPDF extends TCPDF
{

	public function Header()
	{

		global $Status;

		if ($Status == 0) {
			$StatusTerm = "(Approval Pending)";
		} else if ($Status == 2) {
			$StatusTerm = "(cancelled)";
		} else if ($Status == 4) {
			$StatusTerm = "(Rejected)";
		}
		global $Header;
		$image_file = 'images/ds_logo.jpg';
		$this->SetFont('helvetica', 'B', 8);
		$this->Cell(110, 0, 'PROFORMA INVOICE ' . $Header, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetTextColor(255, 0, 0);
		$this->SetFont('helvetica', '', 8);
		$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
	}

	public function Footer()
	{
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$documentInvoiceNumber = trim((string) $PoDataRow['InvNumFull']) !== '' ? $PoDataRow['InvNumFull'] : $PoDataRow['InvNo'];
$pdf->SetTitle('Proforma Invoice: ' . $documentInvoiceNumber);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(5);
$pdf->SetMargins(7, 6, 7, 4);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetAutoPageBreak(TRUE, 4);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();



$table_data = getExcelFormatTable($iconn, $ID);
writeSaimeSinglePage($pdf, $table_data);
$pdf->SetFont('helvetica', '', 6);
/***uma 28-07-2022**/
$CurrentMonth = date('n', strtotime($PoDataRow['InvDate']));
if ($PoDataRow['ExporterID'] == 5) {
	if ($CurrentMonth <= 3) {


		$NxtYear = date('y', strtotime($PoDataRow['InvDate']));
		$CurrentYear = date('y', strtotime($PoDataRow['InvDate'])) - 1;
	} else {

		$CurrentYear = date('y', strtotime($PoDataRow['InvDate']));
		$NxtYear = date('y', strtotime($PoDataRow['InvDate'])) + 1;
	}
} else {
	if ($CurrentMonth <= 3) {



		$NxtYear = date('y', strtotime($PoDataRow['InvDate']));
		$CurrentYear = date('Y', strtotime($PoDataRow['InvDate'])) - 1;
	} else {

		$CurrentYear = date('Y', strtotime($PoDataRow['InvDate']));
		$NxtYear = date('y', strtotime($PoDataRow['InvDate'])) + 1;
	}
}
/***uma 28-07-2022**/

$invoiceShortCode = !empty($ExporterData['InvShortCode']) ? $ExporterData['InvShortCode'] : (stripos((string) $ExporterData['ExpName'], 'SAI MARINE') !== false ? 'SMEPL' : 'PI');
$file_name = $invoiceShortCode . '-PI-' . $PoDataRow['InvNo'] . '-' . $CurrentYear . '-' . $NxtYear . '.pdf';
$file_name = str_replace('/', '', $file_name);
;
$file_name = $Fname . $file_name;

//sme/pi/233/2022-23


if (ob_get_level()) {
	ob_end_clean();
}

if ($Type == '') {
	$Type = 'I';
} else {
	if (!is_dir($MailTempFolder)) {
		mkdir($MailTempFolder, 0755, true);
	}

	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '', $file_name);
	$file_name = $Fname . $file_name;
}

if (ob_get_level()) {
	ob_end_clean();
}

$pdf->Output($file_name, $Type);


function getExcelFormatTable($iconn, $ID)
{
	global $PoDataRow, $ExporterData, $ProcessorData, $BankID;

	$e = function ($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	};
	$nl = function ($value) use ($e) {
		return nl2br($e($value));
	};
	$number = function ($value) {
		return (float) str_replace(',', '', (string) $value);
	};
	$normalRate = function ($value) use ($number) {
		$rate = $number($value);
		return abs($rate) >= 1000 ? $rate / 1000 : $rate;
	};

	$exporterName = $ExporterData['ExpName'];
	$exporterAddress = $ExporterData['ExpAddress'];
	$processorName = $ProcessorData['ExpName'];
	$processorAddress = $ProcessorData['ExpAddress'];
	$processorName = saimeReportValue($iconn, $ID, 'ProcessorName', $processorName);
	$processorAddress = saimeReportValue($iconn, $ID, 'ProcessorAddress', $processorAddress);
	$invoiceNumber = trim((string) $PoDataRow['InvNumFull']) !== '' ? $PoDataRow['InvNumFull'] : $PoDataRow['InvNo'];
	$invoiceDate = getFormatedDate($PoDataRow['InvDate']);
	$poDate = getFormatedDate($PoDataRow['PoDate']);
	$poDate = $PoDataRow['PoNo'] !== '' ? $poDate : '';

	$importerName = !empty($PoDataRow['ImporterName']) ? $PoDataRow['ImporterName'] : $PoDataRow['BuyerName'];
	$importerAddress = !empty($PoDataRow['ImporterAddress']) ? $PoDataRow['ImporterAddress'] : $PoDataRow['BuyerAddress'];
	$importerCode = isset($PoDataRow['ImporterCode']) ? $PoDataRow['ImporterCode'] : '';
	if (!empty($PoDataRow['ImporterID'])) {
		$stmtImporter = $iconn->prepare('SELECT ImporterName, ImporterAddress, ImporterCode FROM ScrmImporterOfRecord WHERE ImporterID = ?');
		$stmtImporter->bind_param('i', $PoDataRow['ImporterID']);
		$stmtImporter->execute();
		$importerRow = $stmtImporter->get_result()->fetch_assoc();
		if ($importerRow) {
			$importerName = $importerRow['ImporterName'];
			$importerAddress = $importerRow['ImporterAddress'];
			if (trim((string) $importerRow['ImporterCode']) !== '') {
				$importerCode = $importerRow['ImporterCode'];
			}
		}
	}
	if (trim((string) $importerCode) === '' && !empty($PoDataRow['BuyerIRSNumber'])) {
		$importerCode = $PoDataRow['BuyerIRSNumber'];
	}
	// Sai Marine's approved Importer of Record IRS number is not populated on
	// the importer/buyer records used by older PIs, so preserve the reference
	// format with a company-specific final fallback.
	if (trim((string) $importerCode) === '' && stripos((string) $exporterName, 'SAI MARINE') !== false) {
		$importerCode = '044601-01127';
	}

	$ultimateName = $PoDataRow['ConsigneeName'];
	$ultimateAddress = $PoDataRow['ConsigneeAddress'];
	if (!empty($PoDataRow['UltimateConsigneeID'])) {
		$stmtUltimate = $iconn->prepare('SELECT ConsigneeName, ConsigneeAddress FROM ScrmUltimateConsigneeDetails WHERE ConID = ?');
		$stmtUltimate->bind_param('i', $PoDataRow['UltimateConsigneeID']);
		$stmtUltimate->execute();
		$ultimateRow = $stmtUltimate->get_result()->fetch_assoc();
		if ($ultimateRow) {
			$ultimateName = $ultimateRow['ConsigneeName'];
			$ultimateAddress = $ultimateRow['ConsigneeAddress'];
		}
	}
	// Keep the printed party section consistent with DocPackingList.php,
	// which resolves these parties from ScrmPoData for the same document ID.
	$referenceNo = '';
	$stmtPackingParties = $iconn->prepare('SELECT SPD.ReferenceNo, SCD.ConsigneeName, SCD.ConsigneeAddress,
		SIR.ImporterName, SIR.ImporterAddress, SIR.ImporterCode, SBD.BuyerIRSNumber
		FROM ScrmPoData SPD
		LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID = SPD.ConsigneeID
		LEFT JOIN ScrmImporterOfRecord SIR ON SIR.ImporterID = SPD.ImporterID
		LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID = SPD.BuyerID
		WHERE SPD.PoID = ? LIMIT 1');
	$stmtPackingParties->bind_param('i', $ID);
	$stmtPackingParties->execute();
	$packingParties = $stmtPackingParties->get_result()->fetch_assoc();
	if ($packingParties) {
		$referenceNo = trim((string) $packingParties['ReferenceNo']);
		if (trim((string) $packingParties['ConsigneeName']) !== '') {
			$ultimateName = $packingParties['ConsigneeName'];
		}
		if (trim((string) $packingParties['ConsigneeAddress']) !== '') {
			$ultimateAddress = $packingParties['ConsigneeAddress'];
		}
		$importerName = trim((string) $packingParties['ImporterName']);
		if ($importerName === '') {
			$importerName = $exporterName;
		}
		$importerAddress = trim((string) $packingParties['ImporterAddress']);
		if ($importerAddress === '') {
			$importerAddress = $exporterAddress;
		}
		if (trim((string) $packingParties['ImporterCode']) !== '') {
			$importerCode = $packingParties['ImporterCode'];
		} elseif (trim((string) $packingParties['BuyerIRSNumber']) !== '') {
			$importerCode = $packingParties['BuyerIRSNumber'];
		}
	}
	$importerName = saimeReportValue($iconn, $ID, 'ImporterName', $importerName);
	$importerAddress = saimeReportValue($iconn, $ID, 'ImporterAddress', $importerAddress);
	if (trim((string) $ultimateAddress) === '') {
		$ultimateLike = '%' . trim((string) $ultimateName) . '%';
		$stmtUltimateFallback = $iconn->prepare('SELECT BuyerName, BuyerAddress FROM ScrmBuyerDetails WHERE BuyerName LIKE ? ORDER BY CHAR_LENGTH(BuyerAddress) DESC LIMIT 1');
		$stmtUltimateFallback->bind_param('s', $ultimateLike);
		$stmtUltimateFallback->execute();
		$ultimateFallback = $stmtUltimateFallback->get_result()->fetch_assoc();
		if ($ultimateFallback && !empty($ultimateFallback['BuyerAddress'])) {
			$ultimateName = $ultimateFallback['BuyerName'];
			$ultimateAddress = $ultimateFallback['BuyerAddress'];
		}
	}

	$notifyPartyHtml = '<b>NOTIFY PARTY:</b>';
	$stmtPackingNotify = $iconn->prepare('SELECT SNPT.NotifyPartyName, SNPT.NPartyAddress,
			SNPT.NPartyContactNumber, SNPT.NPartyFaxNo
		FROM ScrmPoData SPD
		LEFT JOIN ScrmNotifyparty SNPT ON SNPT.NotifyPartyID = SPD.NotifyPartyID
		WHERE SPD.PoID = ? LIMIT 1');
	$stmtPackingNotify->bind_param('i', $ID);
	$stmtPackingNotify->execute();
	$packingNotify = $stmtPackingNotify->get_result()->fetch_assoc();
	if ($packingNotify) {
		$notifyPartyName = trim((string) $packingNotify['NotifyPartyName']);
		if ($notifyPartyName !== '') {
			$notifyPartyHtml .= '<br><b>C/O. '
				. $e(strtoupper(rtrim($notifyPartyName, ','))) . ',</b>';
		}
		if (trim((string) $packingNotify['NPartyAddress']) !== '') {
			$notifyPartyHtml .= '<br><b>' . $nl(strtoupper($packingNotify['NPartyAddress'])) . '</b>';
		}
		if (trim((string) $packingNotify['NPartyContactNumber']) !== '') {
			$notifyPartyHtml .= '<br><b>TEL: ' . $e($packingNotify['NPartyContactNumber']) . '</b>';
		}
		if (trim((string) $packingNotify['NPartyFaxNo']) !== '') {
			$notifyPartyHtml .= '<br><b>FAX: ' . $e($packingNotify['NPartyFaxNo']) . '</b>';
		}
	}

	$bank = array();
	$stmtBank = $iconn->prepare('SELECT * FROM ScrmBankDetailsMaster WHERE ID = ?');
	$stmtBank->bind_param('i', $BankID);
	$stmtBank->execute();
	$bankRow = $stmtBank->get_result()->fetch_assoc();
	if ($bankRow) {
		$bank = $bankRow;
	}

	$paymentValues = getPaymentValues($iconn, $ID, 'PI');
	$proformaTax = SaimeReportData::proformaTax($iconn, $ID);
	$otherTax = $proformaTax['percentage'];
	$otherTaxMultiplier = $proformaTax['multiplier'];


	$sqlItems = "SELECT PGD.*, APS.PackingStyle, APS.TotalSlabs, APS.SlabWeight, APS.MFactor, APS.LbsFactor, APS.Uom
        FROM ScrmPoGradeData PGD
        LEFT JOIN AdminPackingStyles APS ON PGD.PackingStyleID = APS.PCKID
        WHERE PGD.InvId = ? ORDER BY PGD.Variety, PGD.PackingStyleID, PGD.Brand, ABS(PGD.Grade)";
	$stmtItems = $iconn->prepare($sqlItems);
	$stmtItems->bind_param('i', $ID);
	$stmtItems->execute();
	$resultItems = $stmtItems->get_result();

	$groups = array();
	$totalCases = 0;
	$totalKg = 0.0;
	$totalLb = 0.0;
	$totalAmount = 0.0;
	$productSpecParts = array();
	while ($row = $resultItems->fetch_assoc()) {
		// Older/incomplete grade rows can have PIMcs unset; Mcs is the reliable
		// case count in that situation (same fallback used for CI grade data).
		if (empty($row['PIMcs'])) {
			$row['PIMcs'] = $row['Mcs'];
		}
		if (!empty($row['Remarks'])) {
			$productSpecParts[] = trim((string) $row['Remarks']);
		}
		$key = $row['Variety'] . '|' . $row['PackingStyleID'] . '|' . $row['Brand'];
		$isGrams = strtoupper(trim((string) $row['Uom'])) === 'GRAMS';
		$weightKg = (float) $row['PIMcs'] * (float) $row['TotalSlabs'] * (float) $row['SlabWeight']
			* (float) $row['MFactor'];
		// LbsFactor is already stored per source unit (for example 0.0022
		// for GRAMS), so applying the GRAMS scale again reduces pounds by
		// another 1,000. Canada uses its configured KG-to-LBS conversion.
		$lbsFactor = $isGrams && is_numeric($CanadaLbsFactor)
			? (float) $row['MFactor'] * (float) $CanadaLbsFactor
			: (float) $row['LbsFactor'];
		$weightLb = (float) $row['PIMcs'] * (float) $row['TotalSlabs'] * (float) $row['SlabWeight']
			* $lbsFactor;
		$sourceWeight = strtoupper((string) $PoDataRow['QtyUnit']) === 'KGS' ? $weightKg : $weightLb;
		$sourceRate = $normalRate($row['Price']) * $otherTaxMultiplier;
		$amount = $sourceWeight * $sourceRate;
		$rateLb = $weightLb > 0 ? $amount / $weightLb : 0;
		$row['_weightKg'] = $weightKg;
		$row['_weightLb'] = $weightLb;
		$row['_rateLb'] = $rateLb;
		$row['_amount'] = $amount;
		if (!isset($groups[$key])) {
			$groups[$key] = array('meta' => $row, 'items' => array(), 'cases' => 0);
		}
		$groups[$key]['items'][] = $row;
		$groups[$key]['cases'] += (int) $row['PIMcs'];
		$totalCases += (int) $row['PIMcs'];
		$totalKg += $weightKg;
		$totalLb += $weightLb;
		$totalAmount += $amount;
	}

	// The controller is authoritative for the PDF total; the row calculation
	// above is retained only to display each grade's amount and converted rate.
	if (isset($paymentValues['InvValueUSD'])) {
		$totalAmount = (float) $paymentValues['InvValueUSD'];
	}

	$isBeaverStreetConsignee = isSaimeBeaverStreetConsignee(isset($PoDataRow['ConsigneeID']) ? $PoDataRow['ConsigneeID'] : 0);
	$productSpecs = '';
	if ($isBeaverStreetConsignee) {
		$specItems = array();
		foreach ($groups as $group) {
			$meta = $group['meta'];
			foreach ($group['items'] as $item) {
				$specItemCode = trim((string) $item['BuyerItemCode']);
				$specItems[] = array(
					'itemCode' => $specItemCode,
					'description' => !empty($meta['Description']) ? $meta['Description'] : $meta['Variety'],
					'size' => $item['Grade'],
					'packing' => $meta['PackingStyle'],
					'uom' => $meta['Uom'],
					'slabWeight' => $meta['SlabWeight'],
					'totalSlabs' => $meta['TotalSlabs'],
					'brand' => $meta['Brand'],
					'certificationName' => $PoDataRow['CerificationName'],
					'remarks' => isset($item['Remarks']) ? $item['Remarks'] : ''
				);
			}
		}


		$defaultRemarks = isset($PoDataRow['PIRemarks']) ? $PoDataRow['PIRemarks'] : '';
		$productSpecs = buildSaimeProductSpecsFromBreakup($specItems, $defaultRemarks);

		// echo json_encode($productSpecs, JSON_PRETTY_PRINT);
		// exit;
	}
	if (strtoupper(trim((string) $PoDataRow['QtyUnit'])) === 'KGS') {
		$grossKg = $totalKg + 3000.0;
		$grossLb = $grossKg * 2.2046226218;
	} else {
		// LBS invoices add 1 LB of packaging for each master carton.
		$grossLb = $totalLb + $totalCases;
		$grossKg = $totalKg + 3000.0;
	}
	// Keep the calculated proforma packaging allowance; saved PIGrossWt may contain net weight.

	$bankHtml = '';
	$correspondingBankHtml = '';
	if (!empty($bank)) {
		$bankHtml .= '<b>Bank Adress:</b><br>';
		if (!empty($bank['AccntName']))
			$bankHtml .= 'BENEFICIARY NAME: <b>' . $e($bank['AccntName']) . '</b><br>';
		if (!empty($bank['AccountNumber']))
			$bankHtml .= 'BENEFICIARY ACCOUNT NUMBER: <b>' . $e($bank['AccountNumber']) . '</b><br>';
		if (!empty($bank['BankName']))
			$bankHtml .= 'BANK NAME: <b>' . $e($bank['BankName']) . '</b><br>';
		if (!empty($bank['BankAddress']))
			$bankHtml .= 'BANK ADDRESS: ' . $nl($bank['BankAddress']) . '<br>';
		if (!empty($bank['SwiftCode']))
			$bankHtml .= 'SWIFT CODE: <b>' . $e($bank['SwiftCode']) . '</b><br>';
		if (!empty($bank['IFSC']))
			$bankHtml .= 'IFSC / CODE: <b>' . $e($bank['IFSC']) . '</b><br>';
		if (!empty($bank['MICR']))
			$bankHtml .= 'MICR: <b>' . $e($bank['MICR']) . '</b><br>';
		if (!empty($bank['ABA_Numnber']))
			$bankHtml .= 'ABA ROUTING NO: <b>' . $e($bank['ABA_Numnber']) . '</b><br>';
		$correspondingBankHtml = '<b>Corresponding Bank Address:</b><br>';
		$correspondingBankAddress = trim((string) $bank['NegotiatingBank']);
		if ($correspondingBankAddress === '') {
			$correspondingBankAddress = trim((string) $bank['RoutingThrough']);
		}
		// The ABA number has its own reference-format line; avoid displaying a
		// different account-number line embedded in NegotiatingBank.
		$correspondingBankAddress = preg_replace('/(?:\r?\n)?ACCOUNT NO\s*:\s*[^\r\n]*$/i', '', $correspondingBankAddress);
		if ($correspondingBankAddress !== '') {
			$correspondingBankHtml .= $nl($correspondingBankAddress) . '<br>';
		}
		if (!empty($bank['ABA_Numnber'])) {
			$correspondingBankHtml .= '<b>ABA ROUTING NO. ' . $e($bank['ABA_Numnber']) . '</b>';
		}
	}

	$countryName = !empty($PoDataRow['FinalDestinationCountryName']) ? $PoDataRow['FinalDestinationCountryName'] : $PoDataRow['FinalDestinationCountry'];
	$portDischarge = trim($PoDataRow['PortOfDischarge'] . ' ' . $PoDataRow['CountryCode'] . ', ' . $PoDataRow['PortofDischargeCountry']);
	$finalDestination = trim($PoDataRow['FinalDestination'] . ' ' . $PoDataRow['FinalDestinationCountryCode'] . ', ' . $countryName);
	$placeReceipt = !empty($PoDataRow['PlaceofReceiptName']) ? $PoDataRow['PlaceofReceiptName'] : 'INDIAN PORTS';
	$paymentTerms = !empty($PoDataRow['PaymentName']) ? $PoDataRow['PaymentName'] : $PoDataRow['PaymentTerms'];
	$shippingLine = trim((string) $PoDataRow['DeliveryTerms']);
	$referenceNoHtml = $referenceNo !== '' ? '<br><b>' . $e($referenceNo) . '</b>' : '';
	$amountCountry = strtoupper(trim((string) $countryName));
	if (in_array($amountCountry, array('USA', 'US', 'UNITED STATES', 'UNITED STATES OF AMERICA'), true)) {
		$amountCountry = 'U.S.A.';
	}
	$amountDestination = rtrim(strtoupper(trim((string) $PoDataRow['FinalDestination'])), " ,") . ', ' . $amountCountry;

	$html = '<style>
        table { border-collapse: collapse; font-family: helvetica; font-size: 6.6pt; color: #000; }
        td, th { padding: 1px 2px; line-height: 1.06; }
        .label { font-size: 6pt; }
        .strong { font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .amount { font-size: 6.4pt; }
    </style>';

	/* The printed layout is kept in three simple, separately editable files. */
	require __DIR__ . '/Includes/ProformaInvoiceHeader.php';
	require __DIR__ . '/Includes/ProformaInvoiceBody.php';
	require __DIR__ . '/Includes/ProformaInvoiceFooter.php';

	return $html;

	/* Old combined layout below is retained only as a format reference. */
	$html .= '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
	$html .= '<tr><th colspan="10" align="center" style="font-size:12pt"><b>PROFORMA INVOICE</b></th></tr>';
	$html .= '<tr><td colspan="5" rowspan="2" style="height:56px"><span class="label">Exporter:</span><br><b>' . $e($exporterName) . '</b><br>' . $nl($exporterAddress) . '</td>';
	$html .= '<td colspan="5" style="height:28px"><span class="label">INVOICE No. &amp; Date :</span><br><b>' . $e($invoiceNumber) . ', DT: ' . $e($invoiceDate) . '</b></td></tr>';
	$html .= '<tr><td colspan="5" style="height:28px"><span class="label">Buyer\'s order no &amp; date</span><br><b>' . $e($PoDataRow['PoNo']) . ($poDate ? ', ' . $e($poDate) : '') . '</b></td></tr>';
	$html .= '<tr><td colspan="5" cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="65%"><b>IMPORTER OF RECORD</b></td><td width="35%" align="right"><b>' . ($importerCode !== '' ? 'IRS NO. ' . $e($importerCode) : '') . '</b></td></tr></table><b>' . $e(strtoupper($importerName)) . '</b><br><b>' . $nl(strtoupper($importerAddress)) . '</b><br>' . $notifyPartyHtml . '</td></tr><tr><td style="border-top:1px solid #000"><span class="label">Ultimate Consignee :</span><br><b>' . $e(strtoupper($ultimateName)) . '</b><br><b>' . $nl(strtoupper($ultimateAddress)) . '</b></td></tr></table></td>';
	$html .= '<td colspan="5" cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td><b>PROCESSOR CODE:</b> ' . $e($ProcessorData['ExpApprovalNo']) . '<br><b>IE CODE:</b> ' . $e($ExporterData['ExpIeCode']) . '<br><b>USFDA BIOTERRORISM REGN NO:</b> ' . $e($ProcessorData['ExpUsfdaNo']) . '</td></tr><tr><td style="border-top:1px solid #000">' . $bankHtml . '</td></tr><tr><td style="border-top:1px solid #000">' . $correspondingBankHtml . '</td></tr></table></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">Pre-Carriage by:</span><br><b>CONTAINER</b></td><td colspan="3"><span class="label">Place of Receipt by:</span><br><b>' . $e($placeReceipt) . '</b></td><td colspan="2" align="center"><span class="label">Country of Origin of Goods :</span><br><b>' . saimeReportHtml($iconn, $ID, 'CountryOfOrigin', 'INDIA') . '</b></td><td colspan="3" align="center"><span class="label">Country of Final Destination :</span><br><b>' . $e(strtoupper($countryName)) . '</b></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">Vessel/ Flight No.</span></td><td colspan="3"><span class="label">Port of Loading :</span><br><b>' . saimeReportHtml($iconn, $ID, 'LoadingPort', 'INDIAN PORTS') . '</b></td><td colspan="5" rowspan="2"><span class="label">Terms of Delivery &amp; Payment :</span><br><b>' . $nl($paymentTerms) . '</b><br>TRANSPORT TERMS: ' . $e($shippingLine) . $referenceNoHtml . '<br><b>SHIPMENT DATE: ' . $e(getFormatedDate($PoDataRow['ShipByDate'])) . '</b></td></tr>';
	$html .= '<tr><td colspan="2"><span class="label">Port of Discharge:</span><br><b>' . $e(strtoupper($portDischarge)) . '</b></td><td colspan="3"><span class="label">Final Destination:</span><br><b>' . $e(strtoupper($finalDestination)) . '</b></td></tr>';
	$html .= '<tr><th width="8%">Marks &amp; Nos.<br>Container No.</th><th width="9%">No of Kind<br>Packages</th><th width="30%" colspan="2">Description of Goods</th><th width="7%">ITEM<br>CODE</th><th width="8%">SIZE</th><th width="5%">No. Of<br>Cases</th><th width="9%">Total Wt.<br>(LBS)</th><th width="8%">Rate/LB<br>' . saimeReportHtml($iconn, $ID, 'UnitCurrency', 'USD') . '</th><th width="16%" align="center">AMOUNT<br>' . saimeReportHtml($iconn, $ID, 'UnitCurrency', 'USD') . '</th></tr>';

	$marksHtml = '';
	$packagesHtml = '';
	$descriptionHtml = '';
	$itemCodeHtml = '';
	$sizeHtml = '';
	$casesHtml = '';
	$weightHtml = '';
	$rateHtml = '';
	$amountHtml = '';
	$itemIndex = 0;
	foreach ($groups as $group) {
		$meta = $group['meta'];
		foreach ($group['items'] as $item) {
			$description = !empty($meta['Description']) ? $meta['Description'] : $meta['Variety'];
			$separator = $itemIndex > 0 ? '<br><br>' : '';
			$itemTopSpace = $itemIndex === 0 ? '<br><br><br>' : '';
			$itemCode = trim((string) $item['BuyerItemCode']);
			$marksHtml .= $separator . $itemTopSpace;
			$packagesHtml .= $separator . $itemTopSpace . '<b>' . number_format($item['PIMcs']) . '<br>M/CARTONS</b>';
			$descriptionHtml .= $separator . $itemTopSpace . '<b>' . number_format($item['PIMcs']) . ' M/CARTONS OF ' . $nl($description)
				. '<br>PACK: ' . $e($meta['PackingStyle']) . '<br>BRAND: ' . $e($meta['Brand'])
				. (trim((string) $PoDataRow['CerificationName']) !== '' ? ' (' . $e(formatSaimeCertificationName($PoDataRow['CerificationName'])) . ')' : '') . '</b>';
			$itemCodeHtml .= $separator . $itemTopSpace . '<b>' . $e($itemCode) . '</b>';
			$sizeHtml .= $separator . $itemTopSpace . '<b>' . $e($item['Grade']) . '</b>';
			$casesHtml .= $separator . $itemTopSpace . '<b>' . number_format($item['PIMcs']) . '</b>';
			$weightHtml .= $separator . $itemTopSpace . '<b>' . number_format($item['_weightLb'], 3) . '</b>';
			$rateHtml .= $separator . $itemTopSpace . '<b>' . rtrim(rtrim(number_format($item['_rateLb'], 3), '0'), '.') . '</b>';
			$amountHtml .= $separator . '<br><b>' . number_format($item['_amount'], 2) . '</b>';
			$itemIndex++;
		}
	}

	$productRowHeight = $productSpecs !== '' ? 88 : 76;
	$specRowHeight = $productSpecs !== '' ? 92 : 0;
	$weightRowHeight = 58;
	$columnHeightValue = $productRowHeight + $specRowHeight + $weightRowHeight;
	$detailHeightValue = $columnHeightValue - 20;
	// Keep the amount total aligned with the cases and total-weight totals.
	$amountDetailHeightValue = $columnHeightValue - 46;
	$leftBreakup = '<table border="0" cellpadding="1" cellspacing="0" width="100%">';
	$leftBreakup .= '<tr><td width="17.02%" align="center" valign="top" style="height:' . $productRowHeight . 'px">' . $marksHtml . '</td><td width="19.15%" align="center" valign="top" style="height:' . $productRowHeight . 'px">' . $packagesHtml . '</td><td width="63.83%" valign="top" style="height:' . $productRowHeight . 'px">' . $descriptionHtml . '</td></tr>';
	if ($productSpecs !== '') {
		$leftBreakup .= '<tr><td colspan="3" style="border-top:1px solid #000;height:' . $specRowHeight . 'px"><b>PRODUCT SPECS :</b> ' . $nl($productSpecs) . '</td></tr>';
	}
	$leftBreakup .= '<tr><td colspan="3" cellpadding="0" style="border-top:1px solid #000;height:' . $weightRowHeight . 'px"><table border="0" cellpadding="1" cellspacing="0" width="100%">';
	$leftBreakup .= '<tr><td width="40%"></td><td width="27%" align="right"><b>KGS</b></td><td width="33%" align="right"><b>LBS</b></td></tr>';
	$leftBreakup .= '<tr><td width="40%"><b>NET WEIGHT:</b></td><td width="27%" align="right"><b>' . number_format($totalKg, 3) . '</b></td><td width="33%" align="right"><b>' . number_format($totalLb, 3) . '</b></td></tr>';
	$leftBreakup .= '<tr><td width="40%"><b>GROSS WEIGHT:</b></td><td width="27%" align="right"><b>' . number_format($grossKg, 3) . '</b></td><td width="33%" align="right"><b>' . number_format($grossLb, 3) . '</b></td></tr>';
	$leftBreakup .= '</table></td></tr></table>';

	$columnHeight = $columnHeightValue . 'px';
	$html .= '<tr><td colspan="4" cellpadding="0">' . $leftBreakup . '</td>';
	$html .= '<td align="center" valign="top" style="height:' . $columnHeight . '">' . $itemCodeHtml . '</td>';
	$html .= '<td align="center" valign="top" style="height:' . $columnHeight . '">' . $sizeHtml . '</td>';
	$html .= '<td cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td align="center" style="height:' . $detailHeightValue . 'px">' . $casesHtml . '</td></tr><tr><td align="center" style="border-top:1px solid #000"><b>' . number_format($totalCases) . '</b></td></tr></table></td>';
	$html .= '<td cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td align="center" style="height:' . $detailHeightValue . 'px">' . $weightHtml . '</td></tr><tr><td align="center" style="border-top:1px solid #000"><b>' . number_format($totalLb, 3) . '</b></td></tr></table></td>';
	$html .= '<td align="center" valign="top" style="height:' . $columnHeight . '">' . $rateHtml . '</td>';
	$html .= '<td cellpadding="0"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td align="center" style="height:26px;border-bottom:1px solid #000"><b><u>' . $e(strtoupper($PoDataRow['ShippingTerms'])) . '</u></b><br><b>' . $e($amountDestination) . '</b></td></tr><tr><td align="center" style="height:' . $amountDetailHeightValue . 'px">' . $amountHtml . '</td></tr><tr><td align="center" style="border-top:1px solid #000"><b>' . number_format($totalAmount, 2) . '</b></td></tr></table></td></tr>';
	$html .= '<tr><td colspan="10"><b>' . strtoupper(saimeReportHtml($iconn, $ID, 'CurrencyName', 'US DOLLARS')) . ' ' . $e(strtoupper(numberTowords($totalAmount))) . ' ONLY.</b></td></tr>';
	$html .= '<tr nobr="true"><td colspan="6" style="height:58px;font-size:6pt"><b>PROCESSED AND PACKED BY</b><br><b>' . $e($processorName) . '</b><br>FDA REGISTRATION NO:' . $e($ProcessorData['ExpUsfdaNo']) . '<br>' . $nl($processorAddress) . '<br>' . saimeProcessorContactHtml($iconn, $ID) . '<br><br>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</td><td colspan="4" align="center" valign="bottom" style="font-size:6pt">Signature &amp; date<br><b>for ' . $e($exporterName) . '</b><br><br><br><br><br>CHAIRMAN</td></tr>';
	$html .= '</table>';

	return $html;
}

function getDS_TABLE($iconn, $ID)
{

	global $PoDataRow;

	$otherTaxMultiplier = 1;

	// echo print_r($PoDataRow);
	// exit;

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");




	$F_NAME = '';
	$T_O_MCS = '';
	$DOE = '';
	$DOS = '';
	$TOTAL_MCS = 0;
	$TOTAL_SLAB = 0;
	$T_EACH_SLAB = 0;
	$TOTAL_NET_WT_KGS = 0;
	$TOTAL_NET_WT_LBS = 0;


	//uma

	$SQL_PO_GRADE_DATA = "SELECT * FROM `ScrmPoGradeData` where InvId='$ID'  ";
	$STMT_PO_GRADE_DATA = $iconn->query($SQL_PO_GRADE_DATA);

	while ($PO_GRADE_DATA = $STMT_PO_GRADE_DATA->fetch_assoc()) {

		$SLAB = $PO_GRADE_DATA['SLAB'];
		$TOTAL_SLAB += $SLAB;

		$EACH_SLAB = $PO_GRADE_DATA['EACH_SLAB'];
		$T_EACH_SLAB += $EACH_SLAB;

		//$MCS= $PO_GRADE_DATA['Mcs']; 
		$MCS = $PO_GRADE_DATA['PIMcs']; //uma 
		$TOTAL_MCS += $MCS;

		$UOM = $PO_GRADE_DATA['UOM'];
		/* $BAP= $PO_GRADE_DATA['BAP']; 
			if($BAP=='NA'){
			$BAP=''; 
				}
			ELSE{
				$BAP=$BAP; 
			}*/

		if ($UOM == "LBS") {
			$NET_WT_LBS = (($SLAB * ($EACH_SLAB)) * $MCS) . "LBS";
			// $NET_WT_KGS=(($SLAB*($EACH_SLAB*453))*$MCS)/1000 ."KGS"; 
			$NET_WT_KGS = (($SLAB * ($EACH_SLAB)) * $MCS) / 2.2 . "KGS";
		} else if ($UOM == "KGS") {
			$NET_WT_KGS = (($SLAB * ($EACH_SLAB)) * $MCS) . "KGS";


			$NET_WT_LBS = (($SLAB * ($EACH_SLAB * 2.2)) * $MCS) . "LBS";
		}
		$TOTAL_NET_WT_KGS = $NET_WT_KGS + $NET_WT_KGS;
		$TOTAL_NET_WT_LBS = $NET_WT_LBS + $NET_WT_LBS;
	}
	$TOTAL_NET_WT_KGS;
	$TOTAL_NET_WT_LBS;
	$TOTAL_MCS;

	$SHIPMENT_DATE = date('d-m-Y', strtotime($PoDataRow['ShipByDate']));








	global $PoDataRow, $ExpID;
	global $ExporterData;
	global $ProcessorData;
	global $TblAdminBankDetails;
	global $BankID;
	global $TblScrmPaymentTerms;
	global $TblAdminExporterDetails;
	global $SignURL, $ExpID, $Preparedby;
	global $CanadaLbsFactor;

	// echo $Preparedby; exit;

	$CountryName = $PoDataRow['name'];


	//Exporter /Shipper Details

	//$ExporterID=$ExporterData['ExpID'];
	$ExporterName = $ExporterData['ExpName'];
	$ExporterAddress = $ExporterData['ExpAddress'];
	$ExporterUsfdaNo = $ExporterData['ExpUsfdaNo'];
	$ExporterApprovalNo = $ExporterData['ExpApprovalNo'];
	$ExporterIeCustomsNo = $ExporterData['ExpIeCustomsNo'];
	$ExporterGstNo = $ExporterData['ExpGstNo'];
	$ExporterIeCode = $ExporterData['ExpIeCode'];
	$AuthSign = $ExporterData['AuthSign'];
	$ExporterRecAdd = saimeReportValue($iconn, $ID, 'ImporterAddress', $PoDataRow['ImporterAddress']);
	$ImporterName = $PoDataRow['ImporterName'];
	$ImporterCode = $PoDataRow['ImporterCode'];
	$InvShortCode = $ExporterData['InvShortCode'];
	//Processor/Packer

	$ProcessorID = $PoDataRow['ProcessorID'];
	$ExporterID = $PoDataRow['ExporterID'];
	$ProcessorNamee = saimeProcessorDisplayName($ProcessorData['ExpName']);
	$ProcessorIeCustomsNo = $ProcessorData['ExpIeCustomsNo'];
	$DestinationCountryId = $PoDataRow['DestinationCountry'];

	if ($ProcessorID == 3) {
		$SqlProcessor = "select substring_index ('$ProcessorNamee',' ',4) AS STRING  ";
		$StmtProcessor = $iconn->query($SqlProcessor);
		$RowProcessor = $StmtProcessor->fetch_assoc();
		$ProcessorName = $RowProcessor['STRING'];

		$SqlProcessor1 = "select substring_index ('$ProcessorNamee',' ',-2) AS STRING  ";
		$StmtProcessor1 = $iconn->query($SqlProcessor1);
		$RowProcessor1 = $StmtProcessor1->fetch_assoc();
		// $ProcessorName1=$RowProcessor1['STRING']; 
	} else {
		$ProcessorName = $ProcessorNamee;
	}
	// $InvDate=date('m',strtotime($PoDataRow['InvDate'])); 

	// $CurrentMonth = strftime("%m"); 
	$CurrentMonth = date('n', strtotime($PoDataRow['InvDate']));
	if ($PoDataRow['ExporterID'] == 5) {
		if ($CurrentMonth <= 3) {

			/*$NxtYear = strftime("%y"); 
				$CurrentYear = strftime("%y")-1; */
			$NxtYear = date('y', strtotime($PoDataRow['InvDate']));
			$CurrentYear = date('y', strtotime($PoDataRow['InvDate'])) - 1;
		} else {
			/* $CurrentYear = strftime("%Y"); 
				$NxtYear = strftime("%y")+1; */
			$CurrentYear = date('y', strtotime($PoDataRow['InvDate']));
			$NxtYear = date('y', strtotime($PoDataRow['InvDate'])) + 1;
		}
	} else {
		if ($CurrentMonth <= 3) {

			/*$NxtYear = strftime("%y"); 
				$CurrentYear = strftime("%Y")-1; */

			$NxtYear = date('y', strtotime($PoDataRow['InvDate']));
			$CurrentYear = date('Y', strtotime($PoDataRow['InvDate'])) - 1;
		} else {
			/*$CurrentYear = strftime("%Y"); 
				$NxtYear = strftime("%y")+1; */
			$CurrentYear = date('Y', strtotime($PoDataRow['InvDate']));
			$NxtYear = date('y', strtotime($PoDataRow['InvDate'])) + 1;
		}
	}

	$ProcessorName = saimeProcessorDisplayName($ProcessorData['ExpName']);
	$ProcessorAddress = $ProcessorData['ExpAddress'];
	//$ProcessorAddress=$ProcessorData['PackerAddress'];
	$ProcessorUsfdaNo = $ProcessorData['ExpUsfdaNo'];
	$ProcessorApprovalNo = $ProcessorData['ExpApprovalNo'];
	$ProcessorFEI = $ProcessorData['ExpFeiNo'];

	//$InvNO='SME/PI/'.$PoDataRow['InvNo'].'/2021-22';


	/*if($ExpID==5){
		$InvNO=$InvShortCode.$PoDataRow['InvNo'].'/'.$CurrentYear.'-'.$NxtYear;
		}*/
	//else{
	// $InvNO=$InvShortCode.'PI/'.$PoDataRow['InvNo'].'/'.$CurrentYear.'-'.$NxtYear;
	//$InvNO=getPIInvoiceNumber($iconn,$ExpID,$PoDataRow['PoID']);
	$InvNO = $PoDataRow['InvNumFull'];

	//}
	$InvDate = getFormatedDate($PoDataRow['InvDate']);

	$PoNo = $PoDataRow['PoNo'];
	$PoDate = getFormatedDate($PoDataRow['PoDate']);
	$PoDate = "& Dated :" . $PoDate;
	if ($PoNo == '') {

		$PoDate = ' ';
	}


	$ConsigneeName = $PoDataRow['ConsigneeName'];
	$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];
	/*09/08/2022*/
	$BuyerName = $PoDataRow['BuyerName'];
	$BuyerAddress = $PoDataRow['BuyerAddress'];
	$ConsigneeID = $PoDataRow['ConsigneeID'];
	$BuyerID = $PoDataRow['BuyerID'];
	/*09/08/2022*/

	$PlaceofReceipt = $PoDataRow['PlaceofReceiptName'] != '' ? $PoDataRow['PlaceofReceiptName'] : 'ANY PORT, INDIA';

	$PortOfDischarge = $PoDataRow['PortOfDischarge'];
	$PortofDischargeCountry = $PoDataRow['PortofDischargeCountry'];
	$country = !empty($PoDataRow['FinalDestinationCountryName'])
		? $PoDataRow['FinalDestinationCountryName']
		: $PoDataRow['FinalDestinationCountry'];
	$FinalDestination = $PoDataRow['FinalDestination'] . ' ' . $PoDataRow['FinalDestinationCountryCode'] . ', ' . $country;
	$DestinationCountry = $PoDataRow['name'];
	$ByPayment = $PoDataRow['ByPayment'];
	// $PreparedBy=$PoDataRow['PreparedBy'];
	$PaymentTerms = $PoDataRow['PaymentName'];
	$DeliveryTerms = $PoDataRow['DeliveryTerms'];
	$ShippingTerms = $PoDataRow['ShippingTerms'];
	$QtyUnit = $PoDataRow['QtyUnit'];


	$NotifyPartyName = $PoDataRow['NotifyPartyName'];
	$NPartyAddress = $PoDataRow['NPartyAddress'];
	$NPartyContactNumber = $PoDataRow['NPartyContactNumber'];



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

	$content .= '<table border="1" cellspacing="0" cellpadding="0">  
				
				 <tr>  
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';

	if ($PoDataRow['ExporterID'] == 2) {
		$content .= '<tr>
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '<BR>IE CODE  : ' . $ExporterIeCode . '<BR>GST NO  : ' . $ExporterGstNo . '
				</font></th>
		</tr>';
	} else {
		$content .= '<tr> 
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '<BR>';
		if ($DestinationCountryId != '157' || $PoDataRow['ExporterID'] != 2) {

			$content .= 'IE CODE  : ' . $ExporterIeCode . '<BR>GST NO  : ' . $ExporterGstNo . '</font></th></tr>';
		} else {
			$content .= 'USFDA REGISTRATION NO  : ' . $ExporterUsfdaNo . '<BR>IE CODE  : ' . $ExporterIeCode . '<BR>GST NO  : ' . $ExporterGstNo . '</font></th>
				</tr>';
		}
	}
	$BuyerLabelText = "Applicant";
	$consineeLabelText = '';
	if ($_SESSION['ClientID'] == 'SMEV3') {
		$BuyerLabelSql = "SELECT InvDate FROM ScrmPIData WHERE PoID=$ID";
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
						<br>' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
						
						
						</font></th>
						</tr>';
		}
	} else if ($ConsigneeID != $BuyerID) {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '</u></b>
				<br>' . $BuyerName . ',<br>' . $BuyerAddress . '
				<br><b><u>' . $consineeLabelText . 'Consignee</u></b><br>
				' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
				
				
				</font></th>
				</tr>';
	} else {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '/Ultimate Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
				
				
				</font></th>
				</tr>';
	}

	//ECHO 'HII'; $PoDataRo['NPID']; EXIT;
	if ($PoDataRow['NPID'] != 0) {
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
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR>' . $VESSEL_FREIGHT_NO . '  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">' . saimeReportHtml($iconn, $ID, 'LoadingPort', 'ANY PORT, INDIA') . '</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">' . strtoupper($PortOfDischarge) . ' ' . strtoupper($PoDataRow['CountryCode']) . ', ' . $PortofDischargeCountry . '</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">' . strtoupper($FinalDestination) . '</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				<br> ' . $InvNO . ',Dated ' . $InvDate . ' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Buyer`s Order No. & Date. </b>
				<br> ' . $PoNo . $PoDate . ' </font></th>
				</tr>';
	if ($ProcessorID == 3) {
		$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# ' . $ProcessorApprovalNo . '</b>
				<br>
				<u><b> Packer</u> :</b><br> ' . $ProcessorName . '' . $ProcessorName1 . '<br> ' . $ProcessorAddress . ' ';
		if ($DestinationCountryId == '157') {
			$content .= '
				
				<br> USFDA REGISTRATION NO  : ' . $ProcessorUsfdaNo . '<BR> FEI # :  ' . $ProcessorFEI . '';
		}
		$content .= '</font></th>
			</tr>';
	} else {
		$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# ' . $ProcessorApprovalNo . '</b>
				<br>
				<u><b> Packer</u> :</b><br> ' . $ProcessorName . '<br> ' . $ProcessorAddress . '  
				';
		if ($DestinationCountryId == '157') {
			$content .= '
				
				<br> USFDA REGISTRATION NO  : ' . $ProcessorUsfdaNo . '<BR> FEI # :  ' . $ProcessorFEI . '';
		}
		$content .= '</font></th>
			</tr>';
	}



	if ($PoDataRow['ShippingTerms'] == 'DDP') {

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
				<br><b align="center">' . saimeReportHtml($iconn, $ID, 'CountryOfOrigin', 'INDIA') . '</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  ' . $DestinationCountry . '</b></font></th>
				</tr>
				<tr> 
				<th  align="LEFT"  width="100%" ><font size="7"><b><u> Terms of Delivery and Payment:</u>
				</b><br><BR> ' . $PaymentTerms . ' <br>
				 SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . ' ';
	if ($DestinationCountryId != 26) {


		$content .= '(' . $DeliveryTerms . ')<br>';
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
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> ' . $TOTAL_MCS . '</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br><b align="center">1 X 40 FCL</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br><b align="center">' . $SEAL_NO . '</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in ' . $QtyUnit . ' </font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>' . saimeReportHtml($iconn, $ID, 'UnitCurrency', 'USD') . '/' . $QtyUnit . ' ' . $PoDataRow['ShippingTerms'] . ' <br><b>' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . '</b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in ' . saimeReportHtml($iconn, $ID, 'UnitCurrency', 'USD') . ' ' . $PoDataRow['ShippingTerms'] . '<br><b> ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . '</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>
				
				';


	$description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
	$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
	$Whitspace = '<b style="color:white"> yuhju</b>';
	$FontSize = "7";
	//$TOTAL_MCS=0;
	$content .= '<tr  >
				<th width="100%" >
			<table border="0" cellspacing="0" cellpadding="1">
			
			';


	// $SqlVarietyMcs="
	// SELECT sum(McS) As VarietyMcs,PrintStatus,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP
	// FROM `ScrmPoGradeData` as PGD
	// left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
	// left join `ScrmBrandsMaster` SBM on  SBM.BrandName=PGD.Brand
	//  WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand"; 

	$SqlVarietyMcs = "
			SELECT sum(PIMcs) As VarietyMcs,BuyerItemCode,PrintStatus,Description as Description,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP
			FROM `ScrmPoGradeData` as PGD
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			left join `ScrmBrandsMaster` SBM on  SBM.BrandName=PGD.Brand
			 WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {


		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];
		$BAP = $VarietyMcsRow['BAP'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		$BreakUpDescription = $VarietyMcsRow['Description'];
		$BuyerItemCode = $VarietyMcsRow['BuyerItemCode'];


		if ($BrandPrintStatus) {
			$BrandName = '<B> BRAND </B>: ' . $VarietyMcsRow['Brand'] . ' ';
		} else {
			$BrandName = '';
		}


		if ($BAP == 'NA') {
			$BAP = '';
		} else {
			$BAP = $BAP;
		}

		// if($BreakUpDescription!=''){
		// $Description= $VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$BreakUpDescription.'<br>';
		// }


		// else{
		// $Description=$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].'<br>';
		// }
		$Description = $VarietyMcsRow['VarietyMcs'] . ' CARTONS OF ' . $VarietyMcsRow['Variety'];

		if ($PoDataRow['CerificationName']) {
			$Description .= ' (' . $PoDataRow['CerificationName'] . ')';
		}
		$Description .= '<br>';
		if ($DestinationCountryId != 26) {
			$Description .= 'PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' NET WEIGHT PER CARTON.' . $Whitspace . $BrandName . ' <B><br>' . $BAP . '</B>';
		} else {
			$Description .= '<b>PACKING : ' . $VarietyMcsRow['PackingStyle'] . $Whitspace . '</b>';
		}


		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
			. nl2br($Description) . '</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>
			

			
			<tr>';
		if ($BuyerItemCode) {
			$content .= '<th align="center" width="10%" ><font size="' . $FontSize . '"> <u>ITEM NUMBER</u></font></th>';
		} else {
			$content .= '<th align="center" width="10%" ><font size="' . $FontSize . '"> </font></th>';
		}

		$content .= '<th align="center" width="30%" ><font size="' . $FontSize . '"> <u>SIZE</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';

		//UMA
		$SqlVariety = "SELECT * FROM `ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
		and	PackingStyleID='$PackingStyleID' order by abs(Grade)";

		// AND Grade='$Grade'
		$StmtVariety = $iconn->query($SqlVariety);
		$paymentValues = getExporterTaxRates($iconn, $ExporterID);
		$OtherTax = $paymentValues['OtherTax'];

		// $otherTaxMultiplier = 1;
		if ($PoDataRow['OtherCharges'] && $OtherTax > 0) {
			$otherTaxMultiplier += $OtherTax / 100; // e.g. 10% becomes 1.10
			$otherTaxMultiplier = round($otherTaxMultiplier, 3);
		}


		while ($RowVariety = $StmtVariety->fetch_assoc()) {
			$RowPrice = round($RowVariety['Price'] * $otherTaxMultiplier, 3);


			// 26 ID China
			// if ($PoDataRow['QtyUnit'] == 'KGS') {
			// $Qty = $RowVariety['PIMcs'] * $RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['MFactor'];
			// $UsdValue = ($Qty) * $RowPrice;
			// } else {
			// $Qty = $RowVariety['PIMcs'] * $RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['LbsFactor'];
			// $UsdValue = ($Qty) * $RowPrice;
			// }

			$LbsFactor = is_double($CanadaLbsFactor) && $RowVariety['Uom'] == 'GRAMS' ? $CanadaLbsFactor / 1000 : $RowVariety['LbsFactor'];
			$Qty = $RowVariety['PIMcs'] * $RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'];
			switch ($PoDataRow['QtyUnit']) {
				case 'KGS':
					$Qty *= $RowVariety['MFactor'];
					break;
				case 'LBS':
					$Qty *= $LbsFactor;
					break;
			}

			$UsdValue = ($Qty) * $RowPrice;

			// '.$RowVariety['Treatment'].'
			$content .= '
				<tr>
				
			<!--	<td align="center" width="12.5%" ><font size="7">
				' . $RowVariety['Variety'] . '</font></td> -->
				';
			if ($BuyerItemCode) {
				$content .= '<td align="center" width="10%"  ><font size="' . $FontSize . '">
					<b>' . $RowVariety['BuyerItemCode'] . '</b></font></td>';
			} else {
				$content .= '<td align="center" width="10%"  ><font size="' . $FontSize . '"></font></td>';
			}

			$content .= '<td align="center" width="30%"  ><font size="' . $FontSize . '">
				' . $RowVariety['SKUNo'] . ' <b>' . $RowVariety['Grade'] . '</b></font></td>
				<!--<td align="center" width="22.5%"><font size="7">
				' . $RowVariety['PackingStyle'] . '</font></td> -->
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . $RowVariety['PIMcs'] . '</font></td>
				
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($Qty, 3) . '</font></td>
				
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($RowPrice, 3) . '</font></td>
				
				<td align="center" width="14.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($UsdValue, 2) . '</font></td>
				
				</tr>';
		}
	} //UMA

	// $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
	// 			sum(Mcs*TotalSlabs*GrossWeight*MFactor) as GrossWeightKgs,
	// 			sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
	// 			sum(Mcs*TotalSlabs*GrossWeight*LbsFactor) as GrossWeightLbs";

	$sqlExt = "sum(PIMcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
			sum(PIMcs*GrossWeight*MFactor) as GrossWeightKgs,
			CASE WHEN PS.Uom = 'GRAMS' THEN
				sum(PIMcs*TotalSlabs*SlabWeight*MFactor*$CanadaLbsFactor)
			ELSE
				sum(PIMcs*TotalSlabs*SlabWeight*LbsFactor)
			END AS NetWgtLbs,
			CASE WHEN PS.Uom = 'GRAMS' THEN
				sum(PIMcs*GrossWeight*MFactor*$CanadaLbsFactor)
			ELSE
				sum(PIMcs*GrossWeight*LbsFactor)
			END AS GrossWeightLbs";

	// $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
	// 		sum(Mcs*GrossWeight) as GrossWeightKgs,
	// 		sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
	// 		sum(Mcs*GrossWeight) as GrossWeightLbs";

	$SqlTotal = "SELECT sum(PIMcs) as MCS, $sqlExt,
			sum(Price) as Price,
			sum(PIMcs * TotalSlabs * SlabWeight * Price * Mfactor) as PriceKgs,
			CASE WHEN PS.Uom = 'GRAMS' THEN
				sum(PIMcs * TotalSlabs * SlabWeight * Price * MFactor * $CanadaLbsFactor)
			ELSE
				sum(PIMcs * TotalSlabs * SlabWeight * Price * LbsFactor)
			END AS PriceLbs,
			FROM `ScrmPoGradeData`
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$ID'";


	$SqlTotal = "SELECT SUM(PIMcs) AS MCS, $sqlExt,
    SUM(Price) AS Price,
    SUM(PIMcs * TotalSlabs * SlabWeight * Price * Mfactor * $otherTaxMultiplier) AS PriceKgs,
	CASE WHEN PS.Uom = 'GRAMS' THEN
		sum(PIMcs * TotalSlabs * SlabWeight * Price * MFactor * $CanadaLbsFactor * $otherTaxMultiplier)
	ELSE
		sum(PIMcs * TotalSlabs * SlabWeight * Price * LbsFactor * $otherTaxMultiplier)
	END AS PriceLbs
	FROM `ScrmPoGradeData` AS PGD
	LEFT JOIN AdminPackingStyles AS PS ON PGD.PackingStyleID = PS.PCKID
	WHERE InvId = '$ID'";

	// ECHO $SqlTotal; EXIT;

	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	$NetWgtKgs = $RowTotal['NetWgtKgs'];
	$NetWgtLbs = $RowTotal['NetWgtLbs'];
	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	if ($PoDataRow['QtyUnit'] == 'KGS') {
		$Weight = $RowTotal['NetWgtKgs'];
		$Price = $RowTotal['PriceKgs'];
	} else {
		$Weight = $RowTotal['NetWgtLbs'];
		$Price = $RowTotal['PriceLbs'];
	}

	$DiscountAmount = 0;
	if ($PoDataRow['OtherCharges'] == 1 && $OtherTax > 0) {
		$DiscountAmount = ($Price * $OtherTax) / 100;
		// $Price = $Price - $DiscountAmount;
	}

	$content .= '
				<tr>
				
				<td align="center" width="40%" style="border-top: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; border-right: 1px solid black;" ><font size="7">
				' . $RowTotal['MCS'] . '</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				' . number_format($Weight, 3) . '</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				' . number_format($Price, 2) . '</font>';

	$content .= '</td>			
				
				</tr>';

	if ($PoDataRow['OtherCharges'] == 1 && $OtherTax > 0 && false) {
		$content .= '
			<tr>
			<td align="center" width="40%" style="border-top: 1px solid black;" ></td>
			<td align="center" width="18.5%" style="border-top: 1px solid black;" ></td>
			<td align="center" width="13.5%" style="border-top: 1px solid black;"></td>
			<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
			Other Tax: ' . $OtherTax . '%</font></td>
			<td align="center" width="14.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
			' . number_format($DiscountAmount, 2) . '</font></td>
			</tr>';
	}

	$content .= '</table>
	</th></tr>';
	// $SqlBank="SELECT * FROM $TblAdminBankDetails WHERE ID='$BankID'";
	$SqlBank = "SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$RowBank = $StmtBank->fetch_assoc();
	$BenfName = $RowBank['AccntName'];
	$BenfAccNo = $RowBank['AccountNumber'];
	$BenfBankName = $RowBank['BankName'];
	$BenfBankAddress = $RowBank['BankAddress'];
	$BenfBankCode = $RowBank['SwiftCode'];


	$BILL_WORD = numberTowords($Price);
	$content .= '<tr > ' .
		$TOTAL . '
            
				</tr>

				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> SHIPPING TERMS   : ' . $PoDataRow['ShippingTerms'] . '  ' . strtoupper($FinalDestination) . '
				<BR> TOLERANCE           : QUANTITY/AMOUNT 10% PLUS OR MINUS
				<BR> SHIPMENT DATE :  ' . $SHIPMENT_DATE . ' OR EARLY<BR> <BR><B><U style="background-color:grey">OUR BANKING DETAILS        : </U></B><br>
				
				<br> BENEFICIARY NAME<b style="color:white">-----------------------</b>: <b>' . $RowBank['AccntName'] . '</b>
				<BR> BENEFICIARY ACCOUNT NUMBER<b style="color:white">----</b>: <b>' . $RowBank['AccountNumber'] . '</b> 
				<BR> BANK NAME<b style="color:white">------------------------------------</b>: <b>' . $RowBank['BankName'] . ' </b>
				<BR> BANK ADDRESS<b style="color:white">------------------------------</b>: <b>' . $RowBank['BankAddress'] . ' </b>
				<BR> SWIFT CODE<b style="color:white">-----------------------------------</b>: <b>' . $RowBank['SwiftCode'] . '</b>
				<BR> IFSC / CODE<b style="color:white">-----------------------------------</b>: <b>' . (isset($RowBank['IFSC']) ? $RowBank['IFSC'] : '') . '</b>
				<BR> MICR<b style="color:white">--------------------------------------------</b>: <b>' . (isset($RowBank['MICR']) ? $RowBank['MICR'] : '') . '</b>';


	$NetWgtKgs = $RowTotal['NetWgtKgs'];
	$NetWgtLbs = $RowTotal['NetWgtLbs'];




	/***uma (07-11-2023) */
	// $GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	// $GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	$TotalGrossWtKgs = getGrossweight($iconn, $ID);
	if ($TotalGrossWtKgs != 0) {
		if ($QtyUnit == 'KGS') {
			$GrossWeightKgs = $TotalGrossWtKgs;
			$GrossWeightLbs = $TotalGrossWtKgs * 2.204;
		} else {
			$GrossWeightKgs = $TotalGrossWtKgs * 0.454;
			$GrossWeightLbs = $TotalGrossWtKgs;
		}
	} else {
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	}

	// echo $GrossWeightKgs; exit;
	/***uma (07-11-2023) */



	if ($QtyUnit == 'KGS') {
		$content .= '<BR><BR><b> NET WEIGHT <b style="color:white">------</b>: ' . number_format($NetWgtKgs, 3, '.', '') . ' KGS</b>
				<BR><b> GROSS WEIGHT <b style="color:white">-</b>: ' . number_format($GrossWeightKgs, 3, '.', '') . ' KGS</b> 
				</font></th>';
	} else {
		$content .= '<BR><BR> NET WEIGHT<b style="color:white">-----------------------------------</b>: 
				<b>' . number_format($NetWgtLbs, 3, '.', '') . 'LBS</b>
				<b style="color:white">--------------------------</b><b>' . number_format($NetWgtKgs, 3, '.', '') . ' KGS</b>
				<BR> GROSS WEIGHT<b style="color:white">------------------------------</b>: 
				<b>' . number_format($GrossWeightLbs, 3, '.', '') . 'LBS</b>
				<b style="color:white">--------------------------</b><b>' . number_format(($GrossWeightLbs * 0.454), 3, '.', '') . ' KGS</b> 
				</font></th>';
	}

	$taxConditionDate = '2025-07-31';
	$taxConditionDateRT = '2025-09-01';
	$PInvDate = date('Y-m-d', strtotime($InvDate));
	if ($PInvDate >= $taxConditionDateRT) {
		$tariffPercent = '50%';
	} else if ($PInvDate >= $taxConditionDate) {
		$tariffPercent = '25%';
	} else {
		$tariffPercent = '10%';
	}
	if ($PInvDate >= '2026-02-10') { //CAM-2293
		$tariffPercent = '18%';
	}

	$RemarksCountryList = ['157', '41', '124']; // List of country IDs for which remarks should be added
	$content .= '</tr>
				
						
	        
				<tr>  
					<th  align="LEFT"  width="70%"><font size="7"> AMOUNT CHARGABLE IN US.$: <b>' . $BILL_WORD . ' ONLY</b><BR> <BR> 
					<BR> PREPARED BY:  ' . $Preparedby . '<BR><BR><BR><B><U> DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
					THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT
					<BR><BR>';
	if (in_array($DestinationCountryId, $RemarksCountryList) && $ShippingTerms == 'DDP') {
		if ($PInvDate >= '2026-03-10') {
			$Remarks = $PoDataRow['Remarks'];
			$content .= '<B> ' . $Remarks . '</B>';
		} else {
			$content .= '<B> NOTE:  TARIFF ADDED ' . $tariffPercent . ' IF ANY INCREASE OR DECREASE AT THE TIME TO ENTRY MUST BE TO BUYER </B>';
		}
	}

	$content .= '</font></th>

					
					
					<th  align="center"  width="30%"><font size="7"> Signature:<br><br>
					<B> ' . $AuthSign . ',</B>
					
					<img src="' . $SignURL . '" 
					width="100" height="30"style="width: 100" align="center"     valign="middle"/>
									
					
					<p STYLE="text-align:center" ><b> Authorised Signature </b> </p></font></th>
				</tr>
				
				
				</table>';

	// return tcpdf_encode($content);


	// return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');


	// return base64_encode($content);

	// return mb_convert_encoding($content, 'UTF-8', 'auto');
	return $content;
}

function getGrossweight($iconn, $ID)
{

	// $SqlGrossweight="SELECT sum(GrossWt) as TotalGrossweight FROM `ScrmPoGradeData` WHERE InvId ='$ID'"; 
	$SqlGrossweight = "SELECT sum(GrossWt) as TotalGrossweight FROM `ScrmPoGradeData` WHERE InvId ='$ID'";

	$stmtGrossweight = $iconn->query($SqlGrossweight);
	$dataRowGrossweight = $stmtGrossweight->fetch_assoc();

	$TotalGrossweight = $dataRowGrossweight['TotalGrossweight'];

	return $TotalGrossweight;
}
