<?php 
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
	
class MYPDF extends TCPDF {
	

		
	//	include "Constants.php";

    //Page header
    public function Header() {
		
		
$image_file = 'http://jm.silverlights.in/qc/images/jme_logo.jpg';
   //     $this->Image($image_file, 165, 2, 32, 32, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
    $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        
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
		
			//$img_file = 'http://sme.silverlights.in/qc/images/sme_lhh.jpg';

        // Render the image
      //  $this->Image($img_file, 0, 0, 213, 280, '', '', '', false, 380, '', false, false, 0);
	//	$this->Image($img_file, 0, 0, 230, 290, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);
		
		
		
    }

    // Page footer
    public function Footer() {
		
		//sme_footer.jpg
        // Position at 15 mm from bottom
        $this->SetY(0);
		
		$image_file = 'http://jm.silverlights.in/qc/images/jme_footer.jpg';
       // $this->Image($image_file, 165, 2, 32, 32, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
		
		$this->Image($image_file, 0, 270, 210, 25, 'JPG', '#', '', false, 10, '', false, false, 0, false, false, false);

        // Set font
/*	   $this->SetFont('freesans', 'B', 8);
       $this->Cell(0 ,5,'REGISTERED OFFICE & FACTORY:',0,1,'L');
	   $this->SetFont('helvetica', '', 8);
	   $this->Cell(0 ,5,'Surya Food Park,Rs.No.:130,yanamadurru,Bhimavaram -534 239',0,1,'L');
	   $this->Cell(0 ,5,'W.G.Dist.,A.P.,India',0,1,'L');
	   $this->SetFont('freesans', 'B', 8);
       $this->Cell(0 ,5,'CORPORATE OFFICE :',0,1,'L');
        // Page number
       // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    $this->writeHTML("<vr>", true, false, false, false, '');
	$this->Cell(0 ,5,'SURYA MITRA EXIM PVT LTD, R.S.NO-130 YANAMADURRU VILLAGE BHIMAVARAM MANDAL AP 534239, INDIA.',0,1,'C');
	$this->Cell(0 ,5,'SURYA MITRA EXIM PVT LTD, R.S.NO-130 YANAMADURRU VILLAGE BHIMAVARAM MANDAL AP 534239, INDIA.',0,1,'C');
	$this->Cell(0 ,5,'Phone: +91 8816 250999. E-mail:contact@mouryaaquex.com',0,0,'C');


*/
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
    //$pdf->SetFooterMargin(10); 
	//$pdf->SetHeaderMargin(150);	
    $pdf->SetMargins(PDF_MARGIN_LEFT, '40', PDF_MARGIN_RIGHT,'0');




	if(isset($IsHeader)&& $IsHeader==0){
	$pdf->setPrintHeader(false); 
	}else{
	$pdf->setPrintHeader(true);
//echo "hi";	
	}
	
	
	if(isset($IsFooter)&&  $IsFooter==0){
		$pdf->setPrintFooter(false);
	}else{
	   $pdf->setPrintFooter(true);  
	}  
	
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();


//$pdf->Cell(180 ,35,'',0,1,'L');
 
?>