<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
require_once($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
require_once($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf_barcodes_2d.php");



$IndentID=$_GET['IndentID'];

 $SqlCrateDetails="SELECT * FROM `RmIndentDetails` as RID
left join  RmVehicalDetails as RVD on RVD.ID=RID.VehicalID
left join  IndentCategoryList as ICL on ICL.CategoryId=RID.Category
WHERE RID.ID='$IndentID'";
$StmtCrateDetails=$iconn->query($SqlCrateDetails);
$RowCrateDetails=$StmtCrateDetails->fetch_array();
$IndentCode=$RowCrateDetails['IndentCode'];
$LotIDs=$RowCrateDetails['LotID'];
$StmtCrateDetails=$iconn->query($SqlCrateDetails);


 $SqllotDetails="SELECT * FROM `RmBookingDetatils` WHERE RmBookingID in($LotIDs)";
$StmtlotDetails=$iconn->query($SqllotDetails);

while($RowlotDetails=$StmtlotDetails->fetch_array()){
	$LotNumber[]=$RowlotDetails['BookingNumber'];
	$LotNumbers=implode(',',$LotNumber);
}

	
	class MYPDF extends TCPDF {

       
    public function Header() {
        // Logo
		
        //$image_file = 'http://mourya.silverlights.in/qc/images/ma_logo.jpg';
        $this->Image($image_file, 5, 0, 15, 15, 'JPG', '', 'T', false, 25, '', false, false, 0, false, false, false);
        // Set font
      //  $this->SetFont('helvetica', 'B', 20);
        // Title
      //  $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		
		
		//$fontname = TCPDF_FONTS::addTTFfont('../../../fpdf/font/mourya_font.ttf', 'TrueTypeUnicode', '', 96);

// use the font
		//$this->SetFont($fontname, '', 18, '', false);
		
		//$this->Cell(0 ,15,'Mourya Aquex Pvt Ltd',0,0,'C');
		
    }

    // Page footer
    public function Footer() {
     $this->SetY(-10);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
       // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    $this->writeHTML("<hr>", true, false, false, false, '');
	$this->Cell(0 ,5,'Powered by SilverLights Software Solutions',0,1,'C');
	    
   }
}

$widt=10;
 $height=15;
$pageLayout = array($width, $height); //  or array($height, $width) 
$pdf = new TCPDF('L', 'mm', array('150','100'), true, 'UTF-8', false);
	
   // $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Indent Details');  
    $pdf->SetHeaderData('', '', '', '');  
	//$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => array(255, 0, 0)));
	
    //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(5); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 20, 10,10);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);  
    //$pdf->SetFont('Times', '', 11);  
    $pdf->AddPage('L');  
    $content = '';  
    
   


$header=11;
$sub_header=10;



$DOB_TM_STMP=strtotime($RowBarCode['BarCodeDate']);
$DATE_TIME=date("d-m-Y", $DOB_TM_STMP);

$barcodeobj = new TCPDF2DBarcode('123456', 'QRCODE,H');
//$barcodeobj = new TCPDF2DBarcode($IndentCode, 'QRCODE,H');
  $USER_IMAGE= $barcodeobj->getBarcodeHTML(3, 3, 'black');
  
  $BAR_CODE_VALUE=$RowBarCode['BarCodeValue'];

		
		
	 
    $content = '';   
	
	 $size=count($T_GRADES_ARY)+5;
	  $size=100/$size;

	 
			$content .= '<table width="100%" cellpadding="0" border="0">
    <tr align="center">
        
        
<td width="21%" align="center">
            
             
       
			</td>
       
        <td width="80%" align="center">
            <table width="100%" border="1">
                <tr>
				<td><b><font size="10">Category</b></font></td>
				<td><b><font size="10">'.$RowCrateDetails['CategoryName'].'</b></font></td>
				
				</tr> 
				
				<tr>				
				<td><b><font size="10">Indent Date</b></font></td>
				<td><b><font size="10">'.getFormatedDate($RowCrateDetails['IndentDateTime']).'</b></font></td>				
				</tr>
				
				<tr>				
				<td><b><font size="10">Vehicle Number</b></font></td>
				<td><b><font size="10">'.$RowCrateDetails['VehicalNumber'].'</b></font></td>				
				</tr>    

				<tr>
				<td><b><font size="10">Lot No </b></font></td>
				<td><b><font size="10">'.$LotNumbers.'</b></font></td>
				</tr> 
				
				<tr>
				<td><b><font size="10">Remarks </b></font></td>
				<td><b><font size="10">'.$RowCrateDetails['Remarks1'] .'</b></font></td>
				</tr> 
				
				
				      
                
            </table>   
        </td>     
    </tr>
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



$PalletDataTable = '<table width="100%" cellpadding="0" border="1" align="center">
 ';

//while($RowPID=$StmtCrateDetails->fetch_array()){
	
	//echo $RowPID['JME_MOURYA'];
	$PalletDataTable .='
				<tr>
			
				<th><b><font size="10">Ice Crates</b></font></th>
				<th><b><font size="10">Empty Crates</b></font></th>
				<th><b><font size="10">Net Crates</b></font></th>
				
				</tr>
				<tr>
			
				<td><b><font size="10">'.$RowCrateDetails['IndentIceCrates'].'</b></font></td>
				<td><b><font size="10">'.$RowCrateDetails['IndentEmptyCrates'].'</b></font></td>
				<td><b><font size="10">'.$RowCrateDetails['IndentNetCrates'].'</b></font></td>
				
				</tr>'; 
	
//}

$PalletDataTable .= '</table>';

	$pdf->writeHTML($content,false); 		
			
	$pdf->write2DBarcode($IndentCode,'QRCODE,L', 10, 20, 30, 30, $style, 'N');	
	//$pdf->write2DBarcode('1021011901021','QRCODE,L', 5, 20, 30, 30, $style, 'N');	
	$pdf->Cell(5 ,1,'',0,1,'L');
		$pdf->writeHTML($PalletDataTable,false); 		

  

ob_clean();

$pdf->Output('StockCard.pdf', 'I');

?>

