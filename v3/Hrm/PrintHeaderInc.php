<?php 
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php"); 

 $PoID;
 include "Constants.php";
	if($PROCESSOR_APP_NUM==928){
		//echo"hii";
					$FooterImage=$_SERVER['DOCUMENT_ROOT']."images/jlsf_footer_928.jpg";
					$HeaderImage=$_SERVER['DOCUMENT_ROOT']."images/jlsf_header.jpg";

	
	}
	else if($PROCESSOR_APP_NUM==924){
		$FooterImage=$_SERVER['DOCUMENT_ROOT']."images/jlsf_footer_924.jpg";
		$HeaderImage=$_SERVER['DOCUMENT_ROOT']."images/jlsf_header_924.jpg";

	}
	
	
	
class MYPDF extends TCPDF { 
    public function Header() { 
		include "Constants.php";
			global $HeaderImage; 

		  $this->Image($HeaderImage, 10, 10, 200, 22, 'JPG', '', 'T', false, 100, '', false, false, 0, false, false, false);
        				  $this->Image($HeaderImage, 10, 10, 200, 22, 'JPG', '', 'T', false, 100, '', false, false, 0, false, false, false);

		
		
    }

    // Page footer
    public function Footer() {
	
        $this->SetY(0);
		
	global $FooterImage; 
		
		$this->Image($FooterImage, 0, 270, 210, 25, 'JPG', '#', '', false, 10, '', false, false, 0, false, false, false);
		
   }
   
}

	$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 
	$pdf->SetCreator(PDF_CREATOR);  
   
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
   // $pdf->SetFooterMargin(100); 
	//$pdf->SetHeaderMargin(200);
	$pdf->SetMargins(PDF_MARGIN_LEFT, '12', PDF_MARGIN_RIGHT,'15');  
	
	/*$pdf->SetHeaderMargin(140);	*/
    //$pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT,'20');  
    //$pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT,'50');  
	//echo $IsHeader;
	$IsHeader=1;
	$IsFooter=1;
	if($IsHeader==1){
    $pdf->setPrintHeader(true); 
	}else{
	$pdf->setPrintHeader(false); 
	}
	
	
	if($IsFooter==1){
    $pdf->setPrintFooter(true);  
	}else{
	$pdf->setPrintFooter(false);  
	}		
    
	
    $pdf->SetAutoPageBreak(TRUE, 30);  
    $pdf->SetFont('helvetica', '', 11);  
	$pdf->AddPage();
    
	//$pdf->Cell(0 ,22,'',0,1,'L');
 
?>