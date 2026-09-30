<?php
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

  $Styles=file_get_contents('https://serp.silverlights.in/Clients/DFL/Sales/styles/InvoiceHtmlPdf.css');
 $Styless=include_once 'ViewProformaInvoice.php';
   $Styless= file_get_contents('ViewProformaInvoice.php?ID=974');
//exit;

 class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
if($Status==0){
	$StatusTerm="(Approval Pending)";
}else if($Status==2){
	$StatusTerm="(Cancelled)";
}else if($Status==4){
	$StatusTerm="(Rejected)";
}
$StatusTerm='';
	global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 8);
		$this->Cell(110, 0, 'PROFORMA INVOICE '.$Header, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
		 $this->SetTextColor(255,0,0);
		$this->SetFont('helvetica', '', 8);
		$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');	
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Proforma Invoice: '.$Header);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 10, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
$HeaderTable=$Styles.$Styless;

	
	
	$pdf->writeHTML($HeaderTable.$BreakUpTable.$FooterTable);
	/*$pdf->writeHTML($HeaderTable);
	$pdf->writeHTML($BreakUpTable);
	$pdf->writeHTML($FooterTable);*/
	//$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);
//echo $pdf->getAliasNbPages(); exit;


$file_name=$Fname.$file_name.'.pdf';
// $file_name="DFL-".$Fname."-".$file_name.'.pdf';
 


ob_end_clean();



$pdf->Output('hi.pdf', 'I');

?>