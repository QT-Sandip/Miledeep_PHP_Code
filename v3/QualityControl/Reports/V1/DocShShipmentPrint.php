<?php
session_start();
$IsHeader=1;
$IsFooter=1;
$QueryParms=$_GET['Q'];  
$QueryParm=$_GET['Q'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($cp."Includes/IncludePage.php");


$PoID=$_GET['PoID'];
 $ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];

include "PrintHeaderInc.php";

$sql_code_list="
SELECT * FROM `QcCodeList` cl 
LEFT JOIN AdminPackingStyles as ps on cl.PackingStyleID=ps.PCKID 
LEFT JOIN ScrmPoGradeData as vl on cl.PoGradeID=vl.ID WHERE cl.InvID='$PoID'"; 
$stmt_code_list=$iconn->query($sql_code_list);



/*
$sql_varity_list="SELECT * FROM `$TblQcPoVarityList` WHERE InvID='$PoID'";
$stmt_varity_list=$iconn->query($sql_varity_list);
*/


$SqlScrmPodata="SELECT PoNo FROM `$TblScrmPoData` WHERE PoID='$PoID'";
$StmtPo=$iconn->query($SqlScrmPodata);
$RowPo=$StmtPo->fetch_array();

$T_DAY_CODE='';
$T_PACKING='';
$T_MCS='';
$v_FullName='';
while($code_list_row=$stmt_code_list->fetch_array()){
       
	   $DAY_CODE=$code_list_row['DayCode'];
	   $PACKING=$code_list_row['PackingStyle'];
	   $GLAZE_PERC=$code_list_row['GlazePerc'];
	   $MCS=$code_list_row['McsTotal'];
	   $FullName=$code_list_row['QcDescription']?$code_list_row['QcDescription'] : $code_list_row['Variety'] ;
	   
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
	  
	  IF($v_FullName==''){
		  $v_FullName=$FullName;
	  }
	  else{
		  $v_FullName=$v_FullName.','.$FullName;
	  }
	  }
	  
	 
	
}

 $T_MCS= $T_MCS.' MCS';

//$sql="SELECT * FROM `QcCoveringLetter`  WHERE `InvID`='$PoID'";

$sql="SELECT * FROM `QcCoveringLetter`  as CL 
LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
WHERE `InvID`='$PoID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();
 
function getHeader($PoID,$iconn){

$contents = '';
	global $TblQcShortShipmentLetter; 	
 $sql="SELECT * FROM `$TblQcShortShipmentLetter`  WHERE `InvID`='$PoID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

 $contents .='<p>This is to inform you that, we are loading only <B>'.$row['McsLoading'].'</B> 
master cartons instead of <B>' .$row['McsOriginal'] .' </B>
Master cartons because buyer requested us to ship only <B>'.$row['McsLoading'].'</B> master cartons ,after issuing PO .Our test report is with <B>' .$row['McsOriginal'] .' </B> 
Master cartons, so please kindly accept and issue the health certificate as per invoice.  
 </p>';
 

 
return $contents;
}



		
	

	$from_title = date('M d, Y');
	$to_title = date('M d, Y');

	//require_once('../tcpdf/tcpdf.php'); 
	
	
	
 
 /*----------------------------Declearation --------------------------------------*/
 

 
 
$header=11;
$sub_header=12;




$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(0 ,5,'To',0,1,'L');
$pdf->Cell(150 ,5,'The Deputy Director,',0,1,'L');
$pdf->Cell(150 ,5,'Export Inspection Agency-Chennai',0,1,'L');
$pdf->Cell(49 ,5,'Sub off:Bhimavaram',0,1,'L');
$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',$header);
//$pdf->Cell(0 ,5,'Consignment Details:',0,1,'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI,'B',$header);

$v_FullName = str_replace(",","\n",$v_FullName);


$pdf->MultiCell(150, 5, $v_FullName);
	 //$pdf->Cell($W2 ,5,$v_FullName,0,1,'L');




$pdf->Cell(0 ,5,'',0,1,'L');

$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(15 ,5,'Net Wt',0,0,'L');
$pdf->Cell(0 ,5,': '. getPriceDetails($PoID,'KGS',$iconn)['NetWeightKGS'].' KGS',0,1,'L');
$pdf->Ln();


$pdf->Cell(35 ,5,'Production Codes',0,0,'L');
$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(0 ,5,': '.$T_DAY_CODE,0,1,'L');
$pdf->Ln();

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(0 ,5,'SHORT SHIPMENT DECLARATION',0,1,'C');
$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',$header);



  $content .= getHeader($PoID,$iconn);  

$pdf->writeHTML($content);


$pdf->Ln();


$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,'Thanking you,',0,1,'L');
$pdf->Cell(0 ,5,'For '.$PROCESSOR,0,1,'L');
$pdf->Ln();
$pdf->Cell(0 ,5,'',0,1,'L');


$pdf->SetFont($FONT_ARI,'B',$header);
$pdf->Cell(0 ,5,$row['VerifiedBy'],0,1,'L');
$pdf->Cell(0 ,5,$row['LbuDesignation'],0,1,'L');
//$pdf->Cell(0 ,5,'Q.C Technologist',0,1,'L');








ob_clean();


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