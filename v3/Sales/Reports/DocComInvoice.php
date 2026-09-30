<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");


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
$PType = $_GET['PType'];
if ($PType == '') {
	$PType = 'LBS';
}


$SqlStatus = "SELECT * FROM ScrmPoData  WHERE PoID='$PoID'";
$StmtStatus = $iconn->query($SqlStatus);
// if (!$iconn) {
//     die("Query failed: " . $iconn->error);
// }

$PoStatus = $StmtStatus->fetch_assoc();


$Status = $PoStatus['Status'];
$DocType = 'CMI';
$CountryID = $PoStatus['DestinationCountry'];
$BuyerID = $PoStatus['ConsigneeID'];

$ArrayInvoiceConfigData = json_decode(json_encode($_SESSION['ClientInvoiceConfigData']), true);
$HasCLFCharges = $ArrayInvoiceConfigData['CLFCharges'];
$HasTariff = $ArrayInvoiceConfigData['Tariff'];

$AryLables = getInvoiceLables($DocType, $CountryID, $BuyerID);
$AryShowConditions = getInvoiceShowConditions($DocType, $CountryID, $BuyerID);


$SqlCustom = "SELECT IGST,SPD.ExporterID as ExporterID,CustomsInvNo,CInvDate FROM `ScrmCustomInvoices`  as SCV
left join ScrmPoData as SPD on  SPD.PoID=SCV.PiID
WHERE PiID='$PoID'";
$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom->fetch_assoc();
$IGST = $RowCustom['IGST'];
$ExpID = $RowCustom['ExporterID'];
$CustomsInvNo = $RowCustom['CustomsInvNo'];
$CustInvDate = date("d-m-Y", strtotime($RowCustom['CInvDate']));

$SqlSauce = "SELECT EXISTS (SELECT 1 FROM `ScrmPoGradeData` WHERE `InvId` = '$PoID' AND `Cups` > 0) AS HasCups;";
$StmtSauce = $iconn->query($SqlSauce);
$rowSauce = $StmtSauce->fetch_assoc();
$HasSauce = $rowSauce['HasCups'];

$BreakUpGradeIDs = '';

$SqlRevPoTaxRates = "SELECT * FROM `ScrmPoTaxRates` where PoID='$PoID' AND InvoiceType = '$DocType' ORDER BY ID DESC";
$StmtRevPoTaxRates = $iconn->query($SqlRevPoTaxRates);

if ($StmtRevPoTaxRates && $StmtRevPoTaxRates->num_rows > 0) {
	$RevPoTaxRatesRecord = $StmtRevPoTaxRates->fetch_assoc();
	$RevisedPo = $RevPoTaxRatesRecord['RevisedPo'];
}

$ExporterPrefix = $CountryID == 127 ? 'M/S.' : '';

class MYPDF extends TCPDF
{

	public function Header()
	{
		//	
		/*getCustomInvData($iconn,$PoID);
					   $IGST=$AryData['IGST'];*/
		global $IGST, $CustomsInvNo;

		//ECHO $CustomsInvNo; EXIT;
		/*if($IGST=='IGST'){
						   $Header2='SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
					   }
					   else{
						   $Header2='';
					   }*/

		global $Header, $RevisedPo;
		$image_file = 'images/ds_logo.jpg';
		if ($this->page == 1) {  // show header in page1 only

			$this->SetFont('helvetica', 'B', 13);
			if ($RevisedPo) {
				$this->Cell(0, 0, 'COMMERCIAL INVOICE' . $Header . " (REVISED)", 0, 1, 'C', 0, '', 0, false, 'T', 'M');
			} else {
				$this->Cell(0, 0, 'COMMERCIAL INVOICE' . $Header, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
			}
			$this->SetFont('helvetica', ' ', 8);
			$this->Cell(130, 0, '' . $Header2, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

			$this->SetTextColor(255, 0, 0);

			$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
		}
	}

	public function Footer()
	{

		global $IGST, $CustomsInvNo, $CustInvDate;

		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . '(' . $CustomsInvNo . ' / Dt.' . $CustInvDate . ')', 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->writeHTML('<hr>');
		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('COMMERCIAL INVOICE' . $Header);
$pdf->SetFont('helvetica', '', 19);
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(7);
$pdf->SetHeaderMargin(4);
$pdf->SetMargins(10, 15, 10, 15);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(true, 7);
$pdf->AddPage();
if($PoID == 4947){
	$pdf->SetMargins(10, 10, 10, 15);
}

include_once('DocComInvoiceDBMethods.php');
$ClientID = !empty($_SESSION["ClientID"]) ? $_SESSION["ClientID"] : '';
if ($ClientID == 'DFLV3' && $BuyerID == 13) {
	include_once('DocComInvoiceContentForNordic.php');
} else if ($ClientID == 'DFLV3' && $HasSauce) {
	if ($PType == 'UNITS') {
		include_once('DocComInvoiceContentForRegular.php');
	} else {
		include_once('DocComInvoiceContentForSauce.php');
	}
} else {
	include_once('DocComInvoiceContentForRegular.php');
}

$AryData = getReportData($iconn, $PoID);

$HeaderTable = getHeaderTable($iconn, $PoID);

$BreakUpTable = getBreakTable($iconn, $PoID);

//$FooterTableBank=getFooterTableBank($iconn,$PoID);
$FooterTable = getFooterTable($iconn, $PoID);

$pdf->writeHTML($HeaderTable . $BreakUpTable . $FooterTable);
if($PoID == 4947){
	$totalPages = $pdf->getNumPages();
	for ($page = 2; $page <= $totalPages; $page++) {

	    $pdf->setPage($page);

	    $pdf->SetLineWidth(0.3);
	    $pdf->SetDrawColor(0, 0, 0);

	    // Top horizontal line
	    $pdf->Line(10, 10, 200, 10);
	}
}
/*$pageNumber=$pdf->PageNo();
   if($pdf->PageNo()>1){
	   $pdf->deletePage(1);
	   $pdf->deletePage(1);
	   $pdf->AddPage();
	   $pdf->writeHTML($HeaderTable.$BreakUpTable.$FooterTableBank);
	   $pdf->AddPage();	
	   $pdf->writeHTML($FooterTable);
   }
*/
$file_name = str_replace('/', '_', $CustomsInvNo);
$Fname = '-CI';
$file_name = $file_name . $Fname . '.pdf';
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
