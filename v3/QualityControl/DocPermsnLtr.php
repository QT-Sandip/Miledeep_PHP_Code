<?php
session_start();
$ID=$_GET['id'];
include "../includes/config.php";
include "Constants.php";
include "PrintHeaderInc.php";


$INV_ID=$ID;




$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `CODE_LIST_ID` ASC";
$stmt_code_list=$iconn->query($sql_code_list);

$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$ID'";
$stmt_varity_list=$iconn->query($sql_varity_list);

$T_DAY_CODE='';
$T_PACKING='';
$T_MCS='';
while($code_list_row=$stmt_code_list->fetch_array()){
       
	   $DAY_CODE=$code_list_row['DAY_CODE'];
	   $PACKING=$code_list_row['PACKING'];
	   $GLAZE_PERC=$code_list_row['GLAZE_PERC'];
	   $MCS=$code_list_row['T_O_MCS'];
	   
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
	  if(in_array($PACKING,$ary_PACKING)){                               		 
	  }
	  else{
		  $ary_PACKING[]=$PACKING;
	  
	  IF($T_PACKING==''){
		  $T_PACKING=$PACKING.'('.$GLAZE_PERC.' % GLAZE)';
	  }
	  else{
		  $T_PACKING=$T_PACKING.','.$PACKING.'('.$GLAZE_PERC.' % GLAZE)';
	  }
	  
	  }
	
}

 $T_MCS= $T_MCS.' MCS';

$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

//echo $row['PHOSPHATE'];

if($row['PHOSPHATE']=='TREATED'){
	$PHSPT='Water, salt ,shrimp,Sodium Tripoly phosphate';
}else{
	$PHSPT='Citric Acid,Sodium Metabisulphate Paprica & Allura Red';
}



function getHeader($ID,$iconn){

$contents = '';
	 	
$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$PO_TME_STAMP=strtotime($row['PO_DATE']);
$PO_DATE=date("d-m-Y", $PO_TME_STAMP);

	

$INV_TME_STAMP=strtotime($row['INVOICE_DATE']);
$INV_DATE=date("d-m-Y", $INV_TME_STAMP);

$contents .='<p>Sir,<br/>Sub :- Request for Health Certificate for Fishery products to be exported to <B>'.$row['DESTINATION_COUNTRY'].'</B> <BR/>
Please issue us a Health certificate for the consignment bearing Purchase Order No: <B>' .$row['PO_NUM'] .' </B>
Dt. <B>' .$PO_DATE .' </B>  
against Invoice No. <B>' .$row['INVOICE_NUMBER'] .' </B> 
 Dt. <B>' .$INV_DATE .' </B> 
The Particulars of Purchase Order are given Below are  to be exported to . <B>' .$row['TYPE_OF_COUNTRY'] .' </B> 
Destination .The Certificate fee is paid through Online System and details are attached .
The Certificate for Export relating to this consignment (Pink copy of Certificate NO: <B>' .$row['CERTIFICATE_NO'] .' </B> 
or export enclosed)</p>';
return $contents;
}



		
	

	$from_title = date('d M, Y');
	$to_title = date('d M, Y');

	
//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;



 



$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->Cell(150 ,5,'Date:01/05/2020,',0,1,'R');
$pdf->Cell(150 ,5,'',0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');
$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(0 ,5,'Ref:GORT NO209,LR NO:828/FISH/A1/2020',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$header);



$pdf->SetFont($FONT_ARI,'',$sub_header);
//$pdf->Ln();

/*
foreach($ary_varity_list as $value) {
  $pdf->Cell($W2 ,5,$value,0,1,'L');
}
*/

$pdf->Cell(0 ,5,'',0,1,'L');

$pdf->SetFont($FONT_ARI,'',$header);



$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',$header);



$pdf->Cell(0 ,5,'',0,1,'L');
$contents='<p>As per mention the GO:- FISH and Shrimp are been classified as essentially commodity and exempted
 from lock down orders to support farmers and production of material along with other 
 industries related to aqua
<br><br>We request concern transportation and law enforcement authorities to allow Mrs.C.Kota Priyanka 
Vehicle number AP 37 WT 7113 to inspect the material to our processing plants in M/s.Jagadeesh Marine Exports
Private Limited,Rayeelam,Bhimavaram Mandal,West Godavari Dt.
<br><br> 
</p>';
$pdf->writeHTML($contents);


$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();


$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,'Thanking you,',0,1,'L');
$pdf->Cell(0 ,5,'For M/s.' .$PROCESSOR,0,0,'L');
$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(0 ,15,'Mobile no:  +91 95505 88887',0,1,'R');   
//$pdf->Cell(0 ,5,$row['VARIFIED_ID'],0,1,'L');
//$pdf->Cell(0 ,5,'Q.C Technologist',0,1,'L');






$pdf->Ln();
 //$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);





$pdf->Output('payroll.pdf', 'I');

?>