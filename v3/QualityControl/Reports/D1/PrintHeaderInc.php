<?php
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

if ($PoID != '') {
	$SqlPoData = "SELECT ProcessorID FROM `ScrmPoData` WHERE PoID='$PoID'";
	$StmtPoData = $iconn->query($SqlPoData);
	$RowPoData = $StmtPoData->fetch_array();
	$ProcessorID = $RowPoData['ProcessorID'];

	$SqlLogos = "SELECT LogoUrl,HeaderUrl,FooterUrl,WaterMark,ExpName FROM  `QcDocLetterHeads` as QDLH
left join `AdminExporterDetails` as AED on QDLH.ProcessorID=AED.ExpID where QDLH.ProcessorID='$ProcessorID'";

	$StmtLogos = $iconn->query($SqlLogos);
	$RowLogos = $StmtLogos->fetch_array();

}

class MYPDF extends TCPDF
{

	public function Header()
	{
		global $RowLogos;
		$logo = $RowLogos['LogoUrl'];
		$HeaderUrl = $RowLogos['HeaderUrl'];
		$FooterUrl = $RowLogos['FooterUrl'];
		$WaterMark = $RowLogos['WaterMark'];
		$ExpName = $RowLogos['ExpName'];

		$bMargin = $this->getBreakMargin();
		// echo $bMargin;
		// exit;
		// get current auto-page-break mode
		$auto_page_break = $this->AutoPageBreak;
		// disable auto-page-break
		$this->SetAutoPageBreak(true, 0);
		// set bacground image
		// $img_file = K_PATH_IMAGES . 'image_demo.jpg';
		if ($WaterMark == '') {
			$this->Image($HeaderUrl, 5, 5, 200, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
		} else {
			$this->Image($WaterMark, 17, 7, 280, 0, '', '', '', false, 300, '', false, false, 0);
		}
		// restore auto-page-break status
		$this->SetAutoPageBreak($auto_page_break, $bMargin);
		// set the starting point for the page content
		$this->setPageMark();


		// $img_file = 'http://sme.silverlights.in/qc/images/sme_lhh.jpg';



	}

	// Page footer
	public function Footer()
	{
		global $RowLogos;
		$logo = $RowLogos['LogoUrl'];
		$HeaderUrl = $RowLogos['HeaderUrl'];
		$FooterUrl = $RowLogos['FooterUrl'];
		$WaterMark = $RowLogos['WaterMark'];

		$ExpName = $RowLogos['ExpName'];


		if ($WaterMark == '') {
			$this->SetY(0);



			$this->Image($FooterUrl, 0, 270, 210, 25, 'JPG', '#', '', false, 10, '', false, false, 0, false, false, false);
		}

	}

}


$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);

$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
if (isset($IsHeader) && $IsHeader == 0) {
	$pdf->setPrintHeader(false);
	$pdf->SetMargins(PDF_MARGIN_LEFT, '50', PDF_MARGIN_RIGHT, '-220');
} else {
	$pdf->setPrintHeader(true);
	$pdf->SetMargins(PDF_MARGIN_LEFT, '50', PDF_MARGIN_RIGHT, '-220');
	//echo "hi";	
}
if (isset($IsLetterHead) && $IsLetterHead == 0) {
	$pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT, '-220'); //SIMP
}



if (isset($IsFooter) && $IsFooter == 0) {
	$pdf->setPrintFooter(false);
} else {
	$pdf->setPrintFooter(true);
}

$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 11);

if (isset($Orientation) && isset($Orientation) != '') {
	$pdf->AddPage($Orientation);
} else {
	$pdf->AddPage();
}


?>