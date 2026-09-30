<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
require_once($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
require_once($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf_barcodes_2d.php");
//require_once('../tcpdf/tcpdf.php');
//require_once('../tcpdf/tcpdf_barcodes_2d.php');



$BID=$_GET['BID'];
$StockID=$_GET['StockID'];


// $SqlPID="SELECT *,CPID.FreezingType,CPID.Grade,CPS.PackingStyle as PackingStyle, COALESCE(LotNo,'NA')AS LotNo  FROM `CsStockData` as CPID 
// 	 LEFT JOIN AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID 
//      left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=CPID.VarietyID
//       left join ScrmPoData as SPD on SPD.PoID= CPID.PoID
//      left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
// 	 where CPID.BarCodeID='$BID'";
     
     $SqlPID="SELECT *,CPID.FreezingType,CPID.Grade,CPS.PackingStyle as PackingStyle, COALESCE(LotNo,'NA')AS LotNo  FROM `CsStockData` as CPID 
	 LEFT JOIN AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID 
     left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=CPID.VarietyID
      left join ScrmPoData as SPD on SPD.PoID= CPID.PoID
     left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
	 where CPID.BarCodeID='$BID' and CPID.StockID='$StockID'";
	$StmtPID=$iconn->query($SqlPID);
	


// 	 echo  $SqlBarcode="SELECT BarCodeValue,RackName,BarCodeDate,FloorName,RackName,BlockName 
// from CsBarCodes as CB LEFT JOIN 
// CsStoresRackData as CRD 
// on CB.BarCodeID=CRD.BarCodeID 
// LEFT JOIN CsStoresBlockData as CSBLK on CRD.BlockID=CSBLK.BlockID 
// LEFT JOIN CsStoresFloorData as CSFD on CSFD.FloorID=CRD.FloorID WHERE CB.BarCodeID='$BID'";  exit;

  $SqlBarcode="SELECT BarCodeValue,RackName,BarCodeDate,FloorName,RackName,BlockName 
from CsBarCodes as CB 
LEFT JOIN CsStockData as CSD on CB.BarCodeID=CSD.BarCodeID
LEFT JOIN CsStoresRackData as CRD on CSD.StoreRackID=CRD.RowID
LEFT JOIN CsStoresBlockData as CSBLK on CRD.BlockID=CSBLK.BlockID 
LEFT JOIN CsStoresFloorData as CSFD on CSFD.FloorID=CRD.FloorID WHERE CB.BarCodeID='$BID' and CSD.StockID='$StockID'";  
	$StmtBarCode=$iconn->query($SqlBarcode);
	$RowBarCode=$StmtBarCode->fetch_array();	

	// CB.BarCodeID=CRD.BarCodeID
	
	class MYPDF extends TCPDF {

       
    public function Header() {
        // Logo
		
        $image_file = 'http://mourya.silverlights.in/qc/images/ma_logo.jpg';
        $this->Image($image_file, 5, 0, 15, 15, 'JPG', '', 'T', false, 25, '', false, false, 0, false, false, false);
        // Set font
      //  $this->SetFont('helvetica', 'B', 20);
        // Title
      //  $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		
		
		//$fontname = TCPDF_FONTS::addTTFfont('../../../fpdf/font/mourya_font.ttf', 'TrueTypeUnicode', '', 96);

// use the font
		//$this->SetFont($fontname, '', 18, '', false);
		
		$this->Cell(0 ,15,'Mourya Aquex Pvt Ltd',0,0,'C');
		
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
    $pdf->SetTitle('Stock Card ');  
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
				<td><b><font size="10">BAR CODE NO</b></font></td>
				<td><b><font size="10">'.$RowBarCode['BarCodeValue'].'</b></font></td>
				
				</tr> 
				
				<tr>				
				<td><b><font size="10">DATE</b></font></td>
				<td><b><font size="10">'.$DATE_TIME.'</b></font></td>				
				</tr>    

				<tr>
				<td><b><font size="10">STORE </b></font></td>
				<td><b><font size="10">'.$RowBarCode['BlockName'] .'</b></font></td>
				</tr> 
				
				<tr>
				<td><b><font size="10">FLOOR </b></font></td>
				<td><b><font size="10">'.$RowBarCode['FloorName'] .'</b></font></td>
				</tr> 
				
				<tr>
				<td><b><font size="10">RACK NUMBER</b></font></td>
				<td><b><font size="10">'.$RowBarCode['RackName'] .'</b></font></td>
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

while($RowPID=$StmtPID->fetch_array()){
	
    // var_dump($RowPID);
    // exit;
	//echo $RowPID['JME_MOURYA'];
	$PalletDataTable .='
				<tr>
			
				<td><b><font size="8">'.$RowPID['ShortName'].'<br> ('.$RowPID['PackingTypeID'].'  )</b></font></td>
				<td><b><font size="8">'.$RowPID['Grade'].'<br> ('.$RowPID['FreezingType'].') ( ' .$RowPID['ProductCategoryID'].' )</b></font></td>
				<td><b><font size="8">'.$RowPID['PackingStyle'].'<br> ('.$RowPID['AvalMcs'].' MCS )</b></font></td>
				<td><b><font size="8">'.$RowPID['PoNo'].' - '.$RowPID['LotNo'].'</b></font></td>
				
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

