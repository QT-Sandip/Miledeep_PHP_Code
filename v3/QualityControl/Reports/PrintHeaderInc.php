<?php 
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

if($PoID!=''){
$SqlPoData="SELECT ProcessorID FROM `ScrmPoData` WHERE PoID='$PoID'";
$StmtPoData=$iconn->query($SqlPoData);
$RowPoData=$StmtPoData->fetch_array();
$ProcessorID=$RowPoData['ProcessorID'];

$SqlLogos="SELECT LogoUrl,HeaderUrl,FooterUrl,WaterMark,ExpName FROM  `QcDocLetterHeads` as QDLH
left join `AdminExporterDetails` as AED on QDLH.ProcessorID=AED.ExpID where QDLH.ProcessorID='$ProcessorID'";
$StmtLogos=$iconn->query($SqlLogos);
$RowLogos=$StmtLogos->fetch_array();

}
	
class MYPDF extends TCPDF {
	
    public function Header() {
		global $RowLogos;
		$logo=$RowLogos['LogoUrl'];
		$HeaderUrl=$RowLogos['HeaderUrl'];
		$FooterUrl=$RowLogos['FooterUrl'];
		 $WaterMark=$RowLogos['WaterMark'];
		$ExpName=$RowLogos['ExpName'];

		
		
	//$image_file = 'http://jm.silverlights.in/qc/images/jme_logo.jpg';
///$image_file = 'https://camaroo.in/Images/jme_logo.jpg';
   //     $this->Image($image_file, 165, 2, 32, 32, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
   // $this->Image($logo, 5, 5, 200, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
	//$this->Image($WaterMark, 0, 0, 0, 0, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
//	$this->Image($WaterMark, 0, 0, 200, 297, '', '', '', false, 600, '', false, false, 0);
	$bMargin = $this->getBreakMargin();
	// get current auto-page-break mode
	$auto_page_break = $this->AutoPageBreak;
	// disable auto-page-break
	$this->SetAutoPageBreak(false, 0);
	// set bacground image
	$img_file = K_PATH_IMAGES.'image_demo.jpg';
	if($WaterMark==''){
		$this->Image($HeaderUrl, 5, 5, 200, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
	}
else{
	$this->Image($WaterMark, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
}
	// restore auto-page-break status
	$this->SetAutoPageBreak($auto_page_break, $bMargin);
	// set the starting point for the page content
	$this->setPageMark();
	//$this->Image($logo, 0, 0, 250, 290, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);
	/*	
// Set font
		
		//$fontname = TCPDF_FONTS::addTTFfont('../fpdf/font/mourya_font.ttf', 'TrueTypeUnicode', '', 96);

// use the font
//$this->SetFont($fontname, '', 20, '', false);
		
        $this->SetFont('freesans', 'B', 20);
        // Title
       // $this->Cell(0, 15, 'Mourya Aquex Pvt Ltd', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		$this->SetTextColor(33,50,143);
		$this->Cell(135 ,0,'',0,1,'L');
		$this->Cell(135 ,0,'SURYAMITRA EXIM PVT.LTD.',0,1,'L');
		$this->SetFont('freesans', '', 12);
		$this->Cell(135 ,0,'(A Govt. Recgd. 3 Star Export House )',0,1,'L');
		
		*/
		
			$img_file = 'http://sme.silverlights.in/qc/images/sme_lhh.jpg';

        // Render the image
      //  $this->Image($img_file, 0, 0, 213, 280, '', '', '', false, 380, '', false, false, 0);
	//	$this->Image($img_file, 0, 0, 230, 290, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);
		
		
		
    }

    // Page footer
    public function Footer() {
		global $RowLogos;
		$logo=$RowLogos['LogoUrl'];
		$HeaderUrl=$RowLogos['HeaderUrl'];
		$FooterUrl=$RowLogos['FooterUrl']; 
		$WaterMark=$RowLogos['WaterMark'];
		$ExpName=$RowLogos['ExpName'];

		// if($WaterMark==''){
		// 	$this->Image($FooterUrl, 165, 2, 32, 32, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
		// }
		
		//sme_footer.jpg
        // Position at 15 mm from bottom
		if($WaterMark==''){
       $this->SetY(0);
		
		
       //$this->Image($image_file, 165, 2, 32, 32, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
		
		$this->Image($FooterUrl, 0, 270, 210, 25, 'JPG', '#', '', false, 10, '', false, false, 0, false, false, false);
		}

   }
   
}


	// $IsHeader=1;
	//$IsFooter=1;


	$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 
	$pdf->SetCreator(PDF_CREATOR);  
   
   // $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    //$pdf->SetFooterMargin(0); 
	//$pdf->SetHeaderMargin(0);	
	
   

//echo $IsHeader; exit;


	if(isset($IsHeader)&& $IsHeader==0){
	$pdf->setPrintHeader(false); 
	$pdf->SetMargins(PDF_MARGIN_LEFT, '40', PDF_MARGIN_RIGHT,'-220');
	}else{
	$pdf->setPrintHeader(true);
	$pdf->SetMargins(PDF_MARGIN_LEFT, '40', PDF_MARGIN_RIGHT,'-220');
//echo "hi";	
	}
	if(isset($IsLetterHead)&& $IsLetterHead==0){
		$pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT,'-220'); //SIMP
		}
	
	
	
	if(isset($IsFooter)&&  $IsFooter==0){
		$pdf->setPrintFooter(false);
	}else{
	   $pdf->setPrintFooter(true);  
	}  
	
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->SetFont('helvetica', '', 11);  

	if(isset($Orientation) && isset($Orientation)!=''){
    $pdf->AddPage($Orientation);
	}else{
		$pdf->AddPage();
	}


//$pdf->Cell(180 ,35,'',0,1,'L');
 
?>