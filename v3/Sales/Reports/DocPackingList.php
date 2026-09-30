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
    font-size: 8px;
	padding : 10px;
}
p {
	//line-height: 0.01;
}
</style>
';


$PoID = $_GET['id'];
$Type = $_GET['Type'];
$PType = $_GET['PType'];
if ($PType == '') {
	$PType = 'LBS';
}





$SqlCustom = "SELECT DestinationCountry,ConsigneeID,IGST,SPD.ExporterID as ExporterID,CustomsInvNo,CInvDate FROM `ScrmCustomInvoices`  as SCV
left join ScrmPoData as SPD on  SPD.PoID=SCV.PiID
WHERE PiID='$PoID'";
$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom->fetch_assoc();
$IGST = $RowCustom['IGST'];
$ExpID = $RowCustom['ExporterID'];
//$Status=$RowCustom['Status']; 
$DocType = 'PKL';
$CountryID = $RowCustom['DestinationCountry'];
$BuyerID = $RowCustom['ConsigneeID'];
$CustomsInvNo = $RowCustom['CustomsInvNo'];
$CustInvDate = date("d-m-Y", strtotime($RowCustom['CInvDate']));


$AryLables = getInvoiceLables($DocType, $CountryID, $BuyerID);
$AryShowConditions = getInvoiceShowConditions($DocType, $CountryID, $BuyerID);

$SqlSauce = "SELECT EXISTS (SELECT 1 FROM `ScrmPoGradeData` WHERE `InvId` = '$PoID' AND `Cups` > 0) AS HasCups;";
$StmtSauce = $iconn->query($SqlSauce);
$rowSauce = $StmtSauce->fetch_assoc();
$HasSauce = $rowSauce['HasCups'];

$ExporterPrefix = $CountryID == 127 ? 'M/S.' : '';

class MYPDF extends TCPDF
{

	public function Header()
	{
		//	
		/*getCustomInvData($iconn,$PoID);
					   $IGST=$AryData['IGST'];*/
		global $IGST;
		/*if($IGST=='IGST'){
						   $Header2='SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
					   }
					   else{
						   $Header2='';
					   }*/

		global $Header;
		$image_file = 'images/ds_logo.jpg';
		if ($this->page == 1) {  // show header in page1 only
			$this->SetFont('helvetica', 'B', 12);
			$this->Cell(0, 0, 'PACKING LIST' . $Header, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
			$this->SetFont('helvetica', ' ', 8);
			$this->Cell(130, 0, '' . $Header2, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

			$this->SetTextColor(255, 0, 0);

			$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
		}
	}

	public function Footer()
	{
		global $CustomsInvNo, $CustInvDate;
		$this->SetFont('helvetica', 'I', 8);
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . '(' . $CustomsInvNo . ' / Dt.' . $CustInvDate . ')', 0, false, 'C', 0, '', 0, false, 'T', 'M');

		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('PACKING LIST' . $Header);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(7);
$pdf->SetHeaderMargin(5);
$pdf->SetMargins(10, 15, 10, 5);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(true, 5);
$pdf->AddPage();
//include_once('DocComInvoiceDBMethods.php');
include_once('DocPackingListDBMethods.php');

$ClientID = !empty($_SESSION["ClientID"]) ? $_SESSION["ClientID"] : '';
if ($ClientID == 'DFLV3' && $BuyerID == 13) {
	include_once('DocPackingListForNordic.php');
} else {
	include_once('DocPackingListForRegular.php');
}

$AryData = getReportData($iconn, $PoID);
$HeaderTable = getHeaderTable($iconn, $PoID);
if ($HasSauce == 1) {
	$BreakUpTable = getSauceBreakTable($iconn, $PoID);
} else {
	$BreakUpTable = getBreakTable($iconn, $PoID);
}
$FooterTable = getFooterTable($iconn, $PoID);

$pdf->writeHTML($HeaderTable . $BreakUpTable . $FooterTable);

/*$TableData=getCustomInvData($iconn,$PoID);
   $pdf->writeHTML($TableData);*/
$pdf->SetFont('helvetica', '', 6);


/*$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$ProcessorInvShortCode=$ArrayFilename['ProcessorInvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];*/

//$file_name=$InvShortCode.'-'.$ProcessorInvShortCode.'-'.$CInvNo.'-'.$FinancialYear.'-PL'.'.pdf';

$file_name = str_replace('/', '_', $CustomsInvNo);
$Fname = '-PL';
$file_name = $file_name . $Fname . '.pdf';
ob_end_clean();

if ($Type == '') {
	$Type = 'I';
} else {
	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '_', $file_name);
	$file_name = $Fname . $file_name;
}

$pdf->Output($file_name, $Type);
