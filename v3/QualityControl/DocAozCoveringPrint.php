<?php


 require('../fpdf/fpdf.php');
include "../includes/config.php";

//include "Constants.php";
session_start();



$SID=$_GET['RID'];

//$sql="SELECT * FROM `EIA_REPORTS`  WHERE `ID`='$SID'";
$sql="SELECT * FROM `$TblQcSamplingLettersEIA`  WHERE `ID`='$SID'";


$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$INV_ID=$row['INVOICE_ID'];
$ID=$row['INVOICE_ID'];

include "Constants.php";
$TYPE=$row['TYPE_N_NATURE'];



$sql_invoice_data="SELECT * FROM `$TblScrmPoData`  WHERE `PoID`='$INV_ID'";
$STMT_INVOICE_DATA=$iconn->query($sql_invoice_data);
$ROW_INV_DATA=$STMT_INVOICE_DATA->fetch_array();

/*
$PROCESSOR=$ROW_INV_DATA['NAME_ADD_OF_PROCESSOR'];
*/
$IMPORTER=$ROW_INV_DATA['NAME_ADD_OF_CONSIGNEE'];
$BRAND=$ROW_INV_DATA['BRAND'];
$DESTINATION_COUNTRY=$ROW_INV_DATA['DESTINATION_COUNTRY'];
$SCIENTIFIC_NAME=$ROW_INV_DATA['SCIENTIFIC_NAME'];


$TYPE=$row['PRODUCT_NAME'];
$SQL_VARITY_LIST="SELECT * FROM `$TblQcPoVarityList` WHERE InvID='$INV_ID' AND TypeProduct='$TYPE'";
$STMT_VARITY_LIST=$iconn->query($SQL_VARITY_LIST);

$TYPE=$row['PRODUCT_NAME'];

 //$SQL_CODE_LIST="SELECT * FROM `$TblQcCodeList` where InvID='$INV_ID' and SHORT_NAME='$TYPE' ORDER BY `CLID` ASC";
 $SQL_CODE_LIST="SELECT * FROM `$TblQcCodeList` where InvID='$INV_ID'  ORDER BY `CLID` ASC";
 
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);

$PROD_CODES='';
$PACKING='';
$T_O_MCS='';
$GRADES='';
$FARM_REG_ID='';

while($CODE=$STMT_CODE_LIST->fetch_array()){
	$T_O_MCS=$T_O_MCS+$CODE['McsTotal'];

	
	$FARM_GRADE=$CODE['Grades'];
	
	$ARY_GRADES = explode(',', $FARM_GRADE);
	
	foreach($ARY_GRADES as $value){
		
		
				if(in_array($value, $ary_farm)){
					
					}else{
						$ary_farm[]=$value;
						IF($GRADES==''){
						$GRADES=$value;
					}ELSE{
						$GRADES=$GRADES.','.$value;
					}
						
}
		
}
	

	
		 

	
	
	
	$FARM_REG=$CODE['TracebilityID'];
		if(in_array($FARM_REG, $ary_farm)){
					
					}else{
						$ary_farm[]=$FARM_REG;
						IF($FARM_REG_ID==''){
						$FARM_REG_ID=$CODE['FARM_REG_ID'];
					}ELSE{
						$FARM_REG_ID=$FARM_REG_ID.','.$CODE['FARM_REG_ID'];
					}
						
}
	
	 $tedt=$CODE['DayCode'];
		if(in_array($tedt, $ary_prod_codes)){
					
					}else{
						$ary_prod_codes[]=$tedt;
						if($PROD_CODES==''){
							$PROD_CODES=$PROD_CODES.''.$CODE['DAY_CODE'];
						}else{
							$PROD_CODES=$PROD_CODES.','.$CODE['DAY_CODE'];
						}
						
					}
					
					
		$PACK=$CODE['PACKING'];
		if(in_array($PACK, $ary_PACK)){
					
					}else{
						$ary_PACK[]=$PACK;
						if($PACKING==''){
							$PACKING=$CODE['PACKING'].'('.$CODE['GLAZE_PERC'].'% GLAZE )';
						}else{
							$PACKING=$PACKING.','.$CODE['PACKING'].'('.$CODE['GLAZE_PERC'].'% GLAZE )';
						}
						
					}
	
}

$T_O_MCS=$T_O_MCS.' MCS';





class PDF extends FPDF
{
protected $col = 0; // Current column
protected $y0;      // Ordinate of column start

function Header()
{
    // Page header
    global $title;

    $this->SetFont('Arial','B',15);
    $w = $this->GetStringWidth($title)+6;
    $this->SetX((210-$w)/2);
    $this->SetDrawColor(0,80,180);
    $this->SetFillColor(230,230,0);
    $this->SetTextColor(220,50,50);
    $this->SetLineWidth(1);
    $this->Cell($w,9,$title,1,1,'C',true);
    $this->Ln(10);
    // Save ordinate
    $this->y0 = $this->GetY();
}
}
$pdf = new PDF();
$title = '20000 Leagues Under the Seas';
$pdf->SetTitle($title);
 //A4 width : 219mm
//default margin : 10mm each side
//writable horizontal : 219-(10*2)=189mm
//create pdf object
$pdf = new FPDF('P','mm','A4');
//add new page
$pdf->AddPage();
//set font to arial, bold, 14pt
$pdf->SetFont('Arial','B',12);

$logo = "images/jme_logo.jpg";
$footer = "images/jme_footer.jpg";
$eia_logo = "images/eia_logo-min.jpg";
$imgPath = "images/eia_logo-min.jpg";

$FONT_ARI='Arial';
$HEIGHT=8;
$WIDHT_1='Arial';

$header=11;
$sub_header=8.4;
$text=6;

//$pdf->Cell( 0, 0, $pdf->Image($logo, null, null, 33.78), 0, 1, 'L', false );

//$pdf->Image($eia_logo, null, null, 20, 20);

 //$pdf->Cell(1,1, $pdf->Image($eia_logo, null, null, 20,20) ,0,0,'C',0);
 
 
        //$pdf->Cell(2,3, $pdf->Image($eia_logo, $pdf->GetX(), $pdf->GetY(), 20,20) ,0,0,'C',0);
		
				$pdf->SetFont($FONT_ARI,'',$header);
        $pdf->Cell(0,5,'Sl.No.:',0,1,'L');

		
		$pdf->SetFont($FONT_ARI,'B',$header);
        $pdf->Cell(0,7,'ON BEHALF OF EXPORT INSPECTION AGENCY - SO BHIMAVARAM',0,1,'C');
		$pdf->SetFont($FONT_ARI,'B',$header);
        $pdf->Cell(0,7,'(MINISTRY OF COMMERCE & INDUSTRY)',0,1,'C');
        $pdf->Cell(0,7,'GOVT.OF INDIA             Sl.No.:** ',0,1,'C');
        //$pdf->Cell(0,7,'Sl.No.:**',0,1,'c');
		$pdf->SetFont($FONT_ARI,'BU',$header);
		
		

$TEST_DATE=strtotime($row['DATE_TIME_OF_REC_SAMPLE']);
$DATE_TIME_OF_REC_SAMPLE=date("d-m-Y", $TEST_DATE);

        $pdf->Cell(0,7,'COVERING NOTE ACCOMPANYING SAMPLES (FISH & FISHERY PRODUCTS)',0,1,'C');
		$pdf->SetFont($FONT_ARI,'B',$sub_header);
		$pdf->Cell(30,5,'',0,0,'C');
		$pdf->Cell(120,7,'Please receive the following samples. Tests may be carried out for the relevant factors as indicated below.',0,1,'C');
		$pdf->SetFont($FONT_ARI,'B',10);
		$pdf->Cell(0,7,'Approval No. *** ',0,0,'L');
		$pdf->Cell(0,10,'Sample purpose:*** ',0,1,'L');
		$pdf->Cell(0,5,'Certificate number of approved technologist issued by EIA- Attached Approval Technologist certificate',0,1,'');
		  
//$pdf->Output();

$FARMER_NAME='';
function getDS_TABLE($iconn){
$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="3">

			<tr>  
           		<th  width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b> Sl.No</b></font></th>
                <th  width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Particulars</b></font></th>				
                <th  width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10"><b>Details</b></font></th>				
           </tr>
		   
		   <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>1.</b></font></th>
                <th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Office Email ID (to receive soft copy of test report)</b></font></th>				
                 <th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10"><u>'.$qc_mail.'</u><br>'.$qc_mail2.'</font></th>				
           </tr>
		   
		  

		   <tr> 				
		   <th width="'.$w1.'" height ="'.$h1.'" style="text-align:center"><font size="10"><b>2.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Customer Name &  Address</b></font></th>
                <th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$PROCESSOR.','.$PROCESSOR_ADD.'</font></th>				
           </tr> 
		   <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>3.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Processor Code/Approval No</b></font></th>
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$PROCESSOR_APP_NUM.'</font></th>				
         				
           </tr> 
		   
		   <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>4.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Lot/Batch/Code</b></font></th>
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$PROD_CODES.'</font></th>				
         					
           </tr>
		   
		   <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>5.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Name Exporter &Address</b></font></th>
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$PROCESSOR.','.$PROCESSOR_ADD.'</font></th>					
           </tr>
		   <tr >  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>6.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Name Importer &   Address</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$ROW_INV_DATA['NAME_ADD_OF_CONSIGNEE'].'</font></th>					
				
           </tr>
		   <tr >  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>7.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Invoice no & Date</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$INVOICE_NUMBER.'&'.$INVOICE_DATE.'</font></th>			
           </tr> 
		   
		   <tr>  
<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>8.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Quantity/Weight for export</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$QUANTITY_IN_KGS.'</font></th>				
           </tr>
		    <tr>  
<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>9.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Buyers Order No/P.O No, Date</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$PO_NUM.'</font></th>
           </tr>
		    <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>10.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Sampled By, Designation, Date of sampling</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$VERIFIED_BY.','.$DATE.'</font></th>			
           </tr>
		   
		  
		   <tr>  
<th width="'.$w1.'" height ="'.$h1.'"align="left"><font size="10"><b>11.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Sampling request date (in-case of EU/JAPAN)</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$DATE.'</font></th>
           </tr>
		   <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>12.</b></font></th>
 
				<th width="'.$w2.'" align="left"><font size="10"><b>Grade</b></font></th>
		
				<th width="'.$w3.'" align="left"><font size="10">'.$GRADES.'</font></th>				
           </tr> 
		   
		   <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>13.</b></font></th>
 
				<th width="'.$w2.'"height ="'.$h1.'"  align="left"><font size="10"><b>Brand</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$BRAND.'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'"height ="'.$h1.'"  align="left"><font size="10"><b>14.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Reference No </b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$REFERENCE_NUMBER.'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'"align="left"><font size="10"><b>15.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>CAA Farm Registration no./ Vessel No etc</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$FARM_REG_ID.'</font><br></th>				
           </tr>';
		   

$FARMER_NAME .= '</table>'; 
	
return $FARMER_NAME;}	
//$pdf->writeHTML($FARMER_NAME);
	

$pdf->Output();


// MULTILINE END






//$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
               
?>