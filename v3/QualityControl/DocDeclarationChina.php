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

$code_list_row=$stmt_code_list->fetch_array();
$stmt_code_list=$iconn->query($sql_code_list);



$T_DAY_CODE='';
$T_PACKING='';
$T_MCS='';
while($code_list_row=$stmt_code_list->fetch_array()){
       
	   $DAY_CODE=$code_list_row['DayCode'];
	   $PACKING=$code_list_row['PackingStyle'];
	   $GLAZE_PERC=$code_list_row['GlazePerc'];
	   $MCS=$code_list_row['McsTotal'];
	   
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

/*$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();*/

//echo $row['PHOSPHATE'];

if($row['PHOSPHATE']=='TREATED'){
	$PHSPT='Water, salt ,shrimp,Sodium Tripoly phosphate';
}else{
	$PHSPT='Citric Acid, Paprica & Allura Red';
}

	

function getHeader($ID,$iconn){

$contents = '';
	 	global $PoID;
global $TblScrmPoData;
global $TblScrmConsigneeDetails;
global $TblQcCoveringLetter;
		
		
$sql="SELECT * FROM `$TblScrmPoData` as pd LEFT JOIN $TblScrmConsigneeDetails as scd on scd.ConID=pd.ConsigneeID
left JOIN $TblQcCoveringLetter as cvl   on  cvl.InvID=pd.PoID
 WHERE `PoID`='$PoID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$PO_TME_STAMP=strtotime($row['PoDate']);
$PO_DATE=date("d-m-Y", $PO_TME_STAMP);

$DO_SAMP=strtotime($row['Date']);
$DO_SAMPLING=date("d/m/Y", $DO_SAMP);

	$TypeCountry=$row['TypeCountry'] ;
	
	if($TypeCountry=='2'){
		$TYPE_OF_COUNTRY='EU';
	}
	else{
			$TYPE_OF_COUNTRY='NON-EU';
	
	}

//$INV_TME_STAMP=strtotime($row['INVOICE_DATE']);

$INV_DATE=date("d/m/y", strtotime($row['InvDate']) );

$INVOICEDate = date("d-m-Y", strtotime($INV_DATE));
  
$contents .='<p>Sir,<br/>Sub :- Request for Health Certificate for Fishery products to be exported to <B>'.$row['ConsigneeCountry'].'</B> <BR/>
Please issue us a Health certificate for the consignment bearing Purchase Order No: <B>' .$row['PoNo'] .' </B>
Dt. <B>' .$PO_DATE .' </B>  
against Invoice No. <B>' .$row['InvNo'] .' </B> 
 Dt. <B>' .$INVOICEDate .' </B> 
The Particulars of Purchase Order are given Below are  to be exported to . <B>' .$TYPE_OF_COUNTRY.' </B> 
Destination .The Certificate fee is paid through Online System and details are attached .
The Certificate for Export relating to this consignment (Pink copy of Certificate NO: <B>' .$row['QCertNo'] .' </B> 
or export enclosed)</p>';
return $contents;
}



		
	

	$from_title = date('d M, Y');
	$to_title = date('d M, Y');

	
//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;



 

$SamplingDate = date("d-m-Y", strtotime($row['DO_SAMPLING']));
$INVDate = date("d-m-Y", strtotime($row['INVOICE_DATE']));


$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(0 ,15,'Date:'.$SamplingDate,0,1,'R');
$pdf->Cell(0 ,5,'To,',0,1,'L');

$pdf->Cell(150 ,5,'THE OFFICER INCHARGE,',0,1,'L');
$pdf->Cell(150 ,5,'EXPORT INSPECTION AGENCY-CHENNAI',0,1,'L');
$pdf->Cell(49 ,5,'SUB OFFICE - BHIMAVARAM.',0,1,'L');
$pdf->Ln();


$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(0 ,5,'CONSIGNMENT DETAILS:',0,1,'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(0 ,5,'INVOICE NO:'.$row['INVOICE_NUMBER'] .' DT:'.$INVDate,0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,'PRODUCT:',0,1,'L');

$pdf->SetFont($FONT_ARI,'',$header);



$pdf->SetFont($FONT_ARI,'B',$sub_header);
/*while($varity_list_row=$stmt_varity_list->fetch_array()){
	$ary_varity_list[]=$varity_list_row['FULL_NAME'];
//$pdf->Cell(5 ,$H1,'',0,0,'L');
$pdf->Cell($W2 ,5,$varity_list_row['FULL_NAME'],0,1,'L');
}*/

$pdf->SetFont($FONT_ARI,'',$header);
//$pdf->Ln();

/*
foreach($ary_varity_list as $value) {
  $pdf->Cell($W2 ,5,$value,0,1,'L');
}
*/

$pdf->Cell(0 ,5,'',0,1,'L');

/*$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(15 ,5,'Net Wt',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['QUANTITY_IN_KGS'],0,1,'L');
$pdf->Ln();
*/

$pdf->Cell(40 ,5,'PRODUCTION CODE ',0,0,'L');
$pdf->SetFont($FONT_ARI,'B',$header);
$pdf->Cell(0 ,5,''.$T_DAY_CODE,0,1,'L');
$pdf->Ln();

$pdf->SetFont($FONT_ARI,'BU',15);
$pdf->Cell(0 ,5,'DECLARATION',0,1,'L');
$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',$header);



$pdf->Cell(0 ,5,'',0,1,'L');
$contents='<p>I. We here by declare that the samples from the frozen shrimp consignment as per details
given above drawn and tested in our laboratory for bacteriological and organoleptic factors and found 
satisfactory,
<br><br>II.We further declare that we are  used shrimps,water,salt, and not used
 Sodium Metabisulphite(E223) any Colours,Spice condiments('.$PHSPT.')  to the above Consignment.
<br><br> III.Parasites like has been visually examined and found satisfactory

</p>';
$pdf->writeHTML($contents);


$pdf->Ln();


$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->Cell(35,5,'Q-certificate No ' ,0,0,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(0 ,5,''.$row['CERTIFICATE_NO'],0,1,'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI,'',$header);

$pdf->Cell(0 ,5,'Yours faith fully,',0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,'For ' .$PROCESSOR,0,1,'L');
$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',15);
$pdf->Cell(0 ,5,$row['VARIFIED_ID'],0,1,'L');
$pdf->SetFont($FONT_ARI,'',15);

$pdf->Cell(0 ,5,'QC TECHNOLOGIST',0,1,'L');






$pdf->Ln();
 //$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);



ob_clean();

$pdf->Output('payroll.pdf', 'I');

?>