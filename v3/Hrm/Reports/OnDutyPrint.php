<?php
session_start();
//ini_set('display_errors', 1);

/*require_once('../tcpdf/tcpdf.php');
require_once('../tcpdf/tcpdf_barcodes_2d.php');*/

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");


$OdID=$_GET['OdID'];

	 $SqlOnDutyCount="Select * from $HrmOnDutyRegister as HOR 
left join HrmEmployeeDetails as HED on 	HED.EmpID= HOR.EmpID where OdID='$OdID'"; 
    $StmtOnDutyCount=$iconn->query($SqlOnDutyCount);
	$RowOnDutyCount=$StmtOnDutyCount->fetch_array();

	 //$MonthName = date('F', mktime(0, 0, 0, $Month, 10)); 

	
	class MYPDF extends TCPDF {

       
    public function Header() {
        // Logo
		
        $image_file = '';
        $this->Image($image_file, 5, 0, 15, 15, 'JPG', '', 'T', false, 25, '', false, false, 0, false, false, false);
        // Set font
      //  $this->SetFont('helvetica', 'B', 20);
        // Title
      //  $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		
		
		//$fontname = TCPDF_FONTS::addTTFfont('../../../fpdf/font/mourya_font.ttf', 'TrueTypeUnicode', '', 96);

// use the font
		//$this->SetFont($fontname, '', 18, '', false);
		
		$this->Cell(0 ,15,'',0,0,'C');
		
    }

    // Page footer
    public function Footer() {
     $this->SetY(-10);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        $this->writeHTML("<hr>", true, false, false, false, '');
	$this->Cell(0 ,5,'Powered by SilverLights Software Solutions',0,1,'C');
	    
   }
}

$widt=10;
 $height=15;
$pageLayout = array($width, $height); //  or array($height, $width) 
$pdf = new TCPDF('L', 'mm', array('150','100'), true, 'UTF-8', false);
	
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Stock Card ');  
    $pdf->SetHeaderData('', '', '', '');  
	 $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(5); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 20, 10,10);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->AddPage('L');  
    $content = '';  
    
   


$header=11;
$sub_header=10;


	
		
	 
    $content = '';   
	
	 $size=count($T_GRADES_ARY)+5;
	  $size=100/$size;
	 
			$content .= '<h3  align="center"> OnDuty </h3>
			<table width="100%" cellpadding="0" border="0">
        
        <tr>				
				<td width="25%"><b><font size="10">Name</b></font></td>
				<td width="10%"><b><font size="10">:</b></font></td>
				<td width="65%"><b><font size="10">'.$RowOnDutyCount['EmpName'].'</b></font></td>				
				</tr>    

				<!--<tr>
				<td width="25%"><b><font size="10">Month </b></font></td>
				<td width="10%"><b><font size="10">:</b></font></td>
				<td width="65%"><b><font size="10">'.$MonthName.'</b></font></td>
				</tr> -->
				

       
      </table>'; 

$style = array(
    'position' => '',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => true,
    'cellfitalign' => '',
    'border' => true,
    'hpadding' => '2',
    'vpadding' => '2',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255),
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 4
);

$width='20';

$PalletDataTable = '<table width="100%" cellpadding="0" border="1" align="center">

<tr>
<th width="'.$width.'%" ><b><font size="10">Start Date</b></font></th>
<th width="'.$width.'%"><b><font size="10">End Date</b></font></th>
<th width="'.$width.'%"><b><font size="10">Reason</b></font></th>
<th width="'.$width.'%"><b><font size="10">No.of Days</b></font></th>
<th width="'.$width.'%"><b><font size="10">Hours</b></font></th>
</tr>
 ';
	    $StmtOnDutyCount=$iconn->query($SqlOnDutyCount);

while($RowOnDutyCount=$StmtOnDutyCount->fetch_array()){
	
	//echo $RowOnDutyCount['JME_MOURYA'];
	$PalletDataTable .='
				<tr>
			
				<td><font size="10">'.getFormatedDate($RowOnDutyCount['StartDate']).'</font></td>
				<td><font size="10">'.getFormatedDate($RowOnDutyCount['EndDate']).'</font></td>
				<td><font size="10">'.$RowOnDutyCount['Reason'].'</font></td>
				<td><font size="10">'.$RowOnDutyCount['DaysTotal'].'</font></td>
				<td><font size="10">'.$RowOnDutyCount['TotalHours'].'</font></td>
				
				</tr>'; 
	
}

$PalletDataTable .= '</table>';

	$pdf->writeHTML($content,false); 		
			
	$pdf->write2DBarcode($BAR_CODE_VALUE,'QRCODE,L', 10, 20, 30, 30, $style, 'N');	
	//$pdf->write2DBarcode('1021011901021','QRCODE,L', 5, 20, 30, 30, $style, 'N');	
	$pdf->Cell(5 ,1,'',0,1,'L');
		$pdf->writeHTML($PalletDataTable,false); 		

  

ob_clean();

$pdf->Output('StockCard.pdf', 'I');

?>