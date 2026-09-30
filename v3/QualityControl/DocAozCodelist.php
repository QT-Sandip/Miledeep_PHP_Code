<?php
session_start();
$PoID=$_GET['PoID'];
include "../includes/config.php";
include "Constants.php";
include "PrintHeaderInc.php";


$INV_ID=$ID;




 $sql_code_list="SELECT * FROM `$TblQcCodeList` as cl left JOIN $TblQcPoVarityList as pvl on pvl.VarietyID=cl.VarietyID 
 LEFT JOIN AdminPackingStyles as ps on ps.PCKID=cl.PackingStyleID
WHERE cl.InvID='$PoID' ORDER BY `DayCode` ASC"; 
$stmt_code_list=$iconn->query($sql_code_list);

/*$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$ID'";
$stmt_varity_list=$iconn->query($sql_varity_list);
*/
$T_DAY_CODE='';
$T_PACKING='';
$T_MCS='';
$T_BRAND='';
$T_FULL_NAME='';
while($code_list_row=$stmt_code_list->fetch_array()){
       
	   $DAY_CODE=$code_list_row['DayCode'];
	   $PACKING=$code_list_row['PackingStyle'];
	   $GLAZE_PERC=$code_list_row['GlazePerc'];
	   $MCS=$code_list_row['McsTotal'];
	   $Brand=$code_list_row['Brand'];
	   $FullName=$code_list_row['FullName'];
	   
	   $T_MCS=$T_MCS+$MCS;
       
	   
	  if(in_array($DAY_CODE,$ary_code)){                               		 
	  }
	  else{
		  $ary_code[]=$DAY_CODE;
	  
	  IF($T_DAY_CODE==''){
		  $T_DAY_CODE=$DAY_CODE;
	  }
	  else{
		  $T_DAY_CODE=$T_DAY_CODE.','.$DAY_CODE;
	  }
	  }	

	  if(in_array($FullName,$ary_FullName)){                               		 
	  }
	  else{
		  $ary_FullName[]=$FullName;
	  
	  IF($T_FULL_NAME==''){
		  $T_FULL_NAME=$FullName;
	  }
	  else{
		  $T_FULL_NAME=$T_FULL_NAME.','.$FullName;
	  }
	  }	   
	  
	  
	  if(in_array($Brand,$ary_Brand)){                               		 
	  }
	  else{
		  $ary_Brand[]=$Brand;
	  
	  IF($T_BRAND==''){
		  $T_BRAND=$Brand;
	  }
	  else{
		  $T_BRAND=$T_BRAND.','.$Brand;
	  }
	  }	  
	  
	  
	  
	  
	  
	  
	  
	  if(in_array($PACKING,$ary_PACKING)){                               		 
	  }
	  else{
		  $ary_PACKING[]=$PACKING;
	  
	  IF($T_PACKING==''){
		  $T_PACKING=$PACKING;
	  }
	  else{
		  $T_PACKING=$T_PACKING.','.$PACKING;
	  }
	  
	  }
	
}

 $T_MCS= $T_MCS.' MCS';

/*$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();
*/

$sql_eia="SELECT * FROM `$TblQcSamplingLettersExt` WHERE InvID='$ID'";
$stmt_eia=$iconn->query($sql_eia);
$row_eia=$stmt_eia->fetch_array();
$ref_no=$row_eia['SampleNumber'];
$VERIFIED_BY=explode('-',$row_eia['VerifiedBy']);
$VERIFIED_BY=$VERIFIED_BY[0];
$analysis=$row_eia['AnalysisReq'];
$date=date('d.m.y',strtotime($row_eia['Date']));



//echo $row['PHOSPHATE'];

if($row['PHOSPHATE']=='TREATED'){
	$PHSPT='Water, salt ,shrimp,Sodium Tripoly phosphate';
}else{
	$PHSPT='Citric Acid,Sodium Metabisulphate Paprica & Allura Red';
}



function getHeader($ID,$iconn){

$contents = '';
	 	
$sql="SELECT * FROM `$TblScrmPoData` as pd LEFT JOIN $TblScrmConsigneeDetails as scd on scd.ConID=pd.ConsigneeID
left JOIN $TblQcCoveringLetter as cvl   on  cvl.InvID=pd.PoID
 WHERE `PoID`='$PoID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$PO_TME_STAMP=strtotime($row['PoDate']);
$PO_DATE=date("d-m-Y", $PO_TME_STAMP);

	

$INV_TME_STAMP=strtotime($row['InvDate']);
$INV_DATE=date("d-m-Y", $INV_TME_STAMP);


}



		
	

	$from_title = date('d M, Y');
	$to_title = date('d M, Y');

	
//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(0,5,'CODELIST',0,1,'C');

//$pdf->Cell(180 ,35,'',0,1,'L');


$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',$header);

 $content = ''; 
	$s_no='1' ;
	$w1='50';
	$w2='150';
	$w3='70';


$sql="SELECT * FROM `$TblScrmPoData` as pd LEFT JOIN $TblScrmConsigneeDetails as scd on scd.ConID=pd.ConsigneeID
left JOIN $TblQcCoveringLetter as cvl   on  cvl.InvID=pd.PoID
 WHERE `PoID`='$PoID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();


$content .= '
      	
      	<table border="0" cellspacing="0" cellpadding="3">  
		
				<tr>  
           		<th  align="left"  width="'.$w2.'"><font size="10"><b>Product Description</b></font></th>
				<th  align="left" width="'.$w2.'"><font size="10"><b>:'.$T_FULL_NAME.'</b></font></th></tr>

	<tr>  
                <th  align="left" width="'.$w2.'"><font size="10"><b>Type Of Packing</b></font></th>
                <th  align="left" width="'.$w2.'"><font size="10"><b>:'.$T_PACKING.'</b></font></th>
	</TR><tr>  
                <th  align="left" width="'.$w2.'"><font size="10"><b>Production Codes</b></font></th>
                <th  align="left" width="'.$w2.'"><font size="10"><b>:'.$T_DAY_CODE.'</b></font></th>
	</TR><tr>  
                <th  align="left" width="'.$w2.'"><font size="10"><b>PO no.</b></font></th>
                <th  align="left" width="'.$w2.'"><font size="10"><b>:'.$row['PoNo'].'</b></font></th>
	</TR><tr>  
                <th  align="left" width="'.$w2.'"><font size="10"><b>Brand</b></font></th>
                <th  align="left" width="'.$w2.'"><font size="10"><b>:'.$T_BRAND.'</b></font></th>
	</TR><tr>  
                <th  align="left" width="'.$w2.'"><font size="10"><b>Buyer</b></font></th>
                <th  align="left" width="'.$w2.'"><font size="10"><b>:'.$row['ConsigneeName'].'</b></font></th>
	</TR><tr>  
                <th  align="left" width="'.$w2.'"><font size="10"><b>Country</b></font></th>
                <th  align="left" width="'.$w2.'"><font size="10"><b>:'.$row['ConsigneeCountry'].'</b><br></font></th>
	</TR></table>';
	$pdf->Ln();

	$content .= '
      	
      	<table border="1" cellspacing="0" cellpadding="3">  
		
				<tr>  
           		<th  align="center"  width="'.$w1.'"><font size="10"><b> Lot No</b></font></th>
                <th  align="center" width="'.$w1.'"><font size="10"><b>Product</b></font></th>
				<th  align="center" width="'.$w2.'"><font size="10"><b>Reg No</b></font></th>
		
				
				
				<th  align="center" width="'.$w1.'"><font size="10"><b>Type of Packing</b></font></th>
				<th  align="center" width="'.$w1.'"><font size="10"><b>Brand</b></font></th>
				<th  align="center" width="'.$w1.'"><font size="10"><b>Code</b></font></th>
				<th  align="center" width="'.$w1.'"><font size="10"><b>Grade</b></font></th>
				<th  align="center" width="'.$w1.'"><font size="10"><b>Total M/c.</b></font></th>
				
				</tr>';
	$content .= '			<tr>  
           		<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
           		<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
				
				<th align="CENTER" width="'.$w2.'" ><b></b></th>
           		
				
				
				<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
           		<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
           		<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
           		<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
           		<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
                
				
				</tr>';
				$content .= '			<tr>  
           		<th  align="center" style="border-top: none" width="'.$w1.'"><font size="9"></font></th>
           		<th  align="center"  width="200"><font size="9"></font></th>
           		<th  align="center"  width="200"><font size="10">Total</font></th>
           		<th  align="center"  width="'.$w1.'"><b><font size="10"> </b></font></th>
				
								</tr>
</table>'; 
$s_no++;				
	$pdf->writeHTML($content);

$pdf->SetFont($FONT_ARI,'',$header);
//$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,''.$VERIFIED_BY,0,1,'L');

$pdf->Cell(0 ,5,'Q.C, TECHNOLOGIST',0,1,'L');
$pdf->Cell(0 ,5,'Place:Bhimavaram',0,1,'L');



$pdf->Output('payroll.pdf', 'I');

?>