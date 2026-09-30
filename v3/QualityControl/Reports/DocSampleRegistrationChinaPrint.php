<?php
session_start();

 //ini_set('display_errors', 1);

/*$ID=$_GET['id'];
$PoID=$_GET['PoID'];*/

$IsHeader=1;
$IsFooter=1;
$QueryParms=$_GET['Q']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";

$PoID=$_GET['PoID']; 
$SAMP_ID=$_GET['RID'];

 $ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];


  $sql="SELECT * FROM $TblQcSamplingLettersExt as SL
left join $TblScrmPoData as PD on PD.PoID=SL.InvID
left join $TblQcLabTechnologist as QLT on QLT.LbuName=SL.VerifiedBy

left join $TblScrmConsigneeDetails as CD on PD.ConsigneeID=CD.ConID WHERE SL.SMPLID='$SAMP_ID'"; 

$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$VERIFIED_BY=$row['VerifiedBy'];
$Designation=$row['LbuDesignation'];
$VERIFIED=explode('-',$row['VerifiedBy']);
$VERIFIED=$VERIFIED[0];
$REFERENCE_NUMBER=$row['SampleNumber'];
$DATE=getFormatedDateNA($row['Date']);
$TYPE=$row['TypeProduct'];

 $sql_invoice_data="SELECT *,pd.ExporterID as ExporterID FROM `$TblScrmPoData` as pd 
LEFT JOIN $TblScrmConsigneeDetails as scd on scd.ConID=pd.ConsigneeID
left JOIN $TblQcCoveringLetter as cvl   on  cvl.InvID=pd.PoID
left join ScrmCustomInvoices as SCI on SCI.PiID=pd.PoID
left join $TblScrmCountriesList as SCL on SCL.id=pd.DestinationCountry

 WHERE `PoID`='$PoID'";  
$STMT_INVOICE_DATA=$iconn->query($sql_invoice_data);
$RowPo=$STMT_INVOICE_DATA->fetch_array();

//echo $sql_invoice_data; exit;

$COUNTRY=$RowPo['name'];
$IMPORTER=$RowPo['ConsigneeName'];
$BRAND=$RowPo['BRAND'];
$INVOICE_NUMBER=$RowPo['InvNo'];
$PO_NUM=$RowPo['PoNo'];
$QtyUnit=$RowPo['QtyUnit'];
$INVOICE_DATE=getFormatedDateNA($RowPo['InvDate']);


$SqlSctnificName="SELECT SpeciesName,QcDescription FROM `ScrmPoData` SPD
LEFT JOIN ScrmPoGradeData SPGD ON SPGD.InvId=SPD.PoID
LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID=SPGD.VarietyID
LEFT JOIN ScrmSpeciesMaster SSM ON SSM.ShortCode=SPVL.Species
WHERE SPD.PoID='$PoID';";
$StmtSctnificName=$iconn->query($SqlSctnificName);
WHILE($RowSctnificName=$StmtSctnificName->fetch_array()){
	
	$ArySpeciesName[]=$RowSctnificName['SpeciesName'];
	$AryProductName[]=$RowSctnificName['QcDescription'];
}

$SciName=implode(",",array_unique($ArySpeciesName));
$ProductName=implode(",",array_unique($AryProductName));


// $sqlMcs = "SELECT sum(McsTotal) as McsTotal,Yeild,
// 			sum(TotalSlabs*SlabWeight*MFactor*McsTotal) as NetWgtKgs ,sum(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs
// 			FROM `$TblQcCodeList` as QCL 
// 			left join AdminPackingStyles As APS on APS.PCKID=QCL.PackingStyleID
// 			left join ScrmPIVarietyList As SPV on SPV.VarietyID=QCL.PackingStyleID
// 			left join ScrmSpeciesMaster As SPM on SPM.ShortCode=SPV.Species
// 			WHERE 
// 			InvID='$PoID' ";
// 		$stmtMcs = $iconn->query($sqlMcs);

// 		$RowMcs = $stmtMcs->fetch_array();

		
		
// if($QtyUnit=='KGS'){
// 	$FinishedQty = $RowMcs['NetWgtKgs'];
// }
// else{
// 	$FinishedQty = $RowMcs['NetWgtLbs']*0.454;
// }

/********UMA*****/
 
			 $ExpID=$RowPo['ExporterID']; 
			$ExporterData=getExporterDetails($ExpID);
            $InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);



/********UMA*****/



 $SQL_VARITY_LIST="SELECT * FROM `$TblQcPoVarityList` WHERE InvID='$PoID' AND TypeProduct='$TYPE'";
$STMT_VARITY_LIST=$iconn->query($SQL_VARITY_LIST);


   $SQL_CODE_LIST="SELECT * FROM `$TblQcCodeList`AS  QCL
 LEFT JOIN $TblAdminPackingStyles as APS ON APS.PCKID=QCL.PackingStyleID left join $TblQcTracebilityList as QTL  ON QTL.TraceID=QCL.TracebilityID
 WHERE InvID='$PoID' ORDER BY 
 `DayCode` ASC"; 
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
$PROD_CODES='';
$PACKING='';
$T_O_MCS='';
$GRADES='';
$FARM_REG_ID='';
$VARIETY_NAME='';
$POND_ID='';


while($VARIETY_LIST=$STMT_VARITY_LIST->fetch_array()){
	
	
	
		$FULL_NAME=$VARIETY_LIST['FullName'];
		if(in_array($FULL_NAME, $ary_FULL_NAME)){
					
					}else{
						$ary_FULL_NAME[]=$FULL_NAME;
						if($VARIETY_NAME==''){
							$VARIETY_NAME=$VARIETY_NAME.''.$VARIETY_LIST['FullName'];
						}else{
							$VARIETY_NAME=$VARIETY_NAME.'.<BR/>'.$VARIETY_LIST['FullName'];
						}
						
					}
						
}



while($CODE=$STMT_CODE_LIST->fetch_array()){
	$T_O_MCS=$T_O_MCS+$CODE['McsTotal'];
	$BRAND=$CODE['Brand'];
	
	
	
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
	
		$FARM_REG=$CODE['Tracebility'];
		$PondNumber=$CODE['PondNumber'];
		
		
		if(in_array($FARM_REG, $ary_farm)){
					
					}else{
						$ary_farm[]=$FARM_REG;
						IF($FARM_REG_ID==''){
						$FARM_REG_ID=$CODE['Tracebility'];
					}ELSE{
						$FARM_REG_ID=$FARM_REG_ID.','.$CODE['Tracebility'];
					}
						
}
if(in_array($PondNumber, $ary_pond)){
					
					}else{
						$ary_pond[]=$PondNumber;
						IF($POND_ID==''){
						$POND_ID=$PondNumber;
					}ELSE{
						$POND_ID=$POND_ID.','.$PondNumber;
					}
						
}
	
	
	
	
	 $tedt=$CODE['DayCode'];
		if(in_array($tedt, $ary_prod_codes)){
					
					}else{
						$ary_prod_codes[]=$tedt;
						if($PROD_CODES==''){
							$PROD_CODES=$PROD_CODES.''.$CODE['DayCode'];
						}else{
							$PROD_CODES=$PROD_CODES.','.$CODE['DayCode'];
						}
						
					}
					
					
		$PACK=$CODE['PackingStyle'];
		if(in_array($PACK, $ary_PACK)){
					
					}else{
						$ary_PACK[]=$PACK;
						$GLAZE_PERC=$CODE['GlazePerc'];
						
						
						
						IF($PACKING==''){
		  if($GLAZE_PERC==0){
			  $PACKING=$PACK;
		  }else{
			  $PACKING=$PACK; 
		  }
		 
	  }
	  else{
		  if($GLAZE_PERC==0){
			 $PACKING=$PACK.','.$PACKING;
		  }else{
			  $PACKING=$PACK.','.$PACKING;
		  }
		  
	  }
						
					}
	
}

IF($PROCESSOR==$UNIT_1){
	
	$logo = $UNIT_1_LOGO;
	
}ELSE{
	
	$logo = $UNIT_2_LOGO;
}


$T_O_MCS=$T_O_MCS.' MCS';

$HEIGHT=8;
$WIDHT_1='Arial';

$header=12;
$sub_header=8;
$text=6;


$TEST_TMSP=strtotime($row['Date']);
$TEST_DATE=date("d-m-Y", $TEST_TMSP);

 
 

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(0,5,'SAMPLE REGISTRATION FORM',0,1,'C');
		
$pdf->SetFont($FONT_ARI,'',$sub_header);

$w1='10%';
$w2='30%';
$w3='60%';
$h1='35';
$EXPORTOR_MAIL=explode(',',$EXPORTOR_MAIL);
$qc_mail=$EXPORTOR_MAIL[0];
$qc_mail2=$EXPORTOR_MAIL[1];


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
		   <th width="'.$w1.'" height ="'.$h1.'" style="text-align:left"><font size="10"><b>2.</b></font></th>
 
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
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$RowPo['ConsigneeName'].','.$RowPo['ConsigneeAddress'].'</font></th>					
				
           </tr>
		   <tr >  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>7.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Invoice no & Date</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$InvNO.'&'.$INVOICE_DATE.'</font></th>			
           </tr> 
		   
		   <tr>  
<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>8.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Quantity/Weight for export</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.getPriceDetails($PoID,'KGS',$iconn)['NetWeightKGS'].' KGS</font></th>				
           </tr>
		    <tr>  
<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>9.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Buyers Order No/P.O No, Date</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$PO_NUM.'</font></th>
           </tr>
		    <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>10.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Sampled By, Designation, Date of sampling</b></font></th>
		
				<!--<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.strtoupper($VERIFIED_BY).','.$Designation.','.$DATE.'</font></th>	-->		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.strtoupper($VERIFIED_BY).','.$DATE.'</font></th>			
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
           </tr></table>';
		   $pdf->Cell(0 ,15,'',0,1,'L');

		   $pdf->writeHTML($FARMER_NAME);

$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Cell(0 ,15,'',0,1,'L');
		   
	$FARMER_NAME='<table border="1" cellspacing="0" cellpadding="3">
	<tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>16.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Pond No.</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$POND_ID.'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'"align="left"><font size="10"><b>17.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Type of Packing,</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$PACKING.'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>18.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'"  align="left"><font size="10"><b>Scientific Name</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$SciName.'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>19.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>No of Cartons</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$T_O_MCS.'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>20.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Product name/description</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$ProductName.'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>21.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Destination / Exported to </b></font></th>
		
				<th width="'.$w3.'"height ="'.$h1.'" align="left"><font size="10">'.strtoupper($COUNTRY).'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>22.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Production Code</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">'.$PROD_CODES.'</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>23.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Number of sample</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">1(TEST SAMPLE & REFERENCE SAMPLE)</font></th>				
           </tr>
		  
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>24.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Parameter to be  Tested</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">To be marked in page 2</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>25.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'"  align="left"><font size="10"><b>Others Information to be
				mentioned in the report , If any</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">****</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>26.</b></font></th>
 
				<th width="'.$w2.'" height ="'.$h1.'" align="left"><font size="10"><b>Details of Fees paid. 
Amount / Bank / DD No / Date
</b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">ONLINE PAYMENT RECEIPT ATTACHED</font></th>				
           </tr>
		  <tr>  
				<th width="'.$w1.'" height ="'.$h1.'" align="left"><font size="10"><b>27.</b></font></th>
 
				<th width="'.$w2.'" align="left"><font size="10"><b>Remarks if any:  </b></font></th>
		
				<th width="'.$w3.'" height ="'.$h1.'" align="left"><font size="10">****</font></th>				
           </tr>
		   
		   
		   
		   </table>  
		';

//$pdf->Ln();

$pdf->writeHTML($FARMER_NAME);



$pdf->SetFont($FONT_ARI,'BU',10);
$pdf->Cell(190 ,$H1,'','T',1,'L');
$pdf->Ln();
$pdf->Cell(190 ,$H1,'For '. $PROCESSOR ,0,1,'L');
//$pdf->Ln();
$pdf->SetFont($FONT_ARI,'B',10);

$pdf->Cell(95,5,''.$VERIFIED,0,1,'L',0);
$pdf->Cell(95,5,'QC-TECHNOLOGIST',0,1,'L',0);
$pdf->Ln();

  
ob_end_clean(); 
      
if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowPo['PoNo'];
$FileName=getFormatedFileName($FileName);
$pdf->Output($FileName, 'I');
}else{
	$Fname=$MailTempFolder;
	$FileName=$_GET['FileName'];
	$FileName = str_replace('/','', $FileName );
	$FileName=$Fname.$FileName;
	$pdf->Output($FileName, $FileType);

}
?>