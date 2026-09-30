<?php
//ini_set('display_errors', 1); 
session_start();
//error_reporting(E_ALL);
		include "../includes/config.php";
    	include "Constants.php";
    	include "PrintHeaderInc.php";

$ID=$_GET['id'];
$INVOICE_ID=$_GET['PoID'];

$sql="SELECT * FROM $TblQcMBReport  WHERE `ID`='$ID'";
$stmt_BUYER_MB_REPORT=$iconn->query($sql);
$RowReport=$stmt_BUYER_MB_REPORT->fetch_array();

$C_NAME=$RowReport['Country'];
$C_NAME='USA';
//$INVOICE_ID=$RowReport['InvID'];
$TYPE=$RowReport['TypeProduct'];


$SQL_INVOICE_DATA="SELECT * FROM $TblScrmPoData as spd LEFT JOIN $TblScrmConsigneeDetails as scd on scd.ConID=spd.ConsigneeID 
 where POID='$INVOICE_ID'";
$STMT_INVOICE_DATA=$iconn->query($SQL_INVOICE_DATA);
$INVOICE_DATA=$STMT_INVOICE_DATA->fetch_array();
$BuyerName=$INVOICE_DATA['ConsigneeName'];



 $PoID=$RowReport['InvID'];
 $SqlOtherData="SELECT Brand,ShortName,FullName,McsTotal,DayCode,VerifiedBy,PackingStyle FROM `QcCodeList` AS CL 
left join $TblAdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left JOIN $TblQcPoVarityList as VL on CL.VarietyID=VL.VarietyID WHERE CL.InvID='$PoID' ";
$StmtOtherData=$iconn->query($SqlOtherData);

while($RowOtherData=$StmtOtherData->fetch_array()){
	$AryBrand[]=$RowOtherData['Brand'];
	$AryMcsTotal[]=$RowOtherData['McsTotal'];
	$AryDayCode[]=$RowOtherData['DayCode'];
	$AryVerifiedBy[]=$RowOtherData['VerifiedBy'];
	$AryPackStyle[]=$RowOtherData['PackingStyle'];
	$AryShortName[]=$RowOtherData['ShortName'];
	$AryFullName[]=$RowOtherData['FullName'];
}

$Brand=implode(",",array_unique($AryBrand));
$McsTotal=array_sum($AryMcsTotal);
$DayCodes=implode(",",array_unique($AryDayCode));
$PackStyle=implode(",",array_unique($AryPackStyle));
$VerifiedBy=implode(",",array_unique($AryVerifiedBy));
$ShortName=implode(",",array_unique($AryShortName));
$FullName=implode("<br>",array_unique($AryFullName));



require_once('../tcpdf/tcpdf.php'); 
	
include "Constants.php";

$HEIGHT=8;


$header=10;
$sub_header=8;
$text=6;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->SetFont('','BU',12);
$pdf->Cell(0 ,5,'IN HOUSE MICROBIOLOGICAL ANALYSIS REPORT',0,1,'C');
$pdf->SetFont('','B',11);

$pdf->Cell(30 ,$HEIGHT,'Buyer Name : ',0,0,'L');
$pdf->SetFont('','',11);

$pdf->Cell(180 ,$HEIGHT,''.$BuyerName,0,1,'L');
$pdf->SetFont('','B',11);

$pdf->Cell(30 ,$HEIGHT,'Approval no : ',0,0,'L');
$pdf->SetFont('','',11);
$pdf->Cell(180 ,$HEIGHT,''.$EXPORTOR_APP_NUM,0,1,'L');
$pdf->SetFont('','B',11);


$pdf->Cell(10 ,$HEIGHT,'SUB: ',0,0,'L');
$pdf->SetFont('','',9);
$pdf->Cell(180 ,$HEIGHT,' MICROBIOLOGICAL ANALYSIS OF '.$FullName,0,1,'L');


/*$pdf->SetFont($FONT_ARI,'B',$header);
$pdf->writeHTML($FullName);
*/

$pdf->SetFont('','',12);
$size=10;

		 
		

		 if($TYPE =='Raw'){
		 	$content .= getRawTable(); 
		 }else{
			$content .= getCookedTable();  
		 }
		 		
		 $content .= '</table>'; 
		 
		  //$pdf->writeHTML(getHeaderTable(), false); 
		 $pdf->SetFont('','BU',12);
		 $pdf->SetMargins(15, 29, 16,true);
		 //$pdf->Cell(180 ,$HEIGHT,'TEST RESULT',0,1,'L');
		 $pdf->SetFont('','',12);
		
		 
		 $pdf->writeHTML($content, false); 






$pdf->Ln();
$pdf->SetFont('','',8);

$HEIGHT=5;
$pdf->Ln();

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell(90,5,'Tested By :'.$BUYER_MB_REPORT_ROW['TESTED_BY'],1,0,'L',0);
$pdf->Cell(90,5,'Verified By :'.$BUYER_MB_REPORT_ROW['VERIFIED_BY'],1,1,'L',0);  // cell with left and right borders


$pdf->Cell(90,10,'Signature',1,0,'L',0);
$pdf->Cell(90,10,'Signature',1,1,'L',0);  // cell with left and right borders


$pdf->Ln();
$pdf->Output('payroll.pdf', 'I');



/*
function getHeaderTable(){
global $ID,$McsTotal,$INVOICE_DATA,$DayCodes,$PackStyle;	
 $size=9;
 $content = '<table border="1" cellspacing="0"  cellpadding="3">
	<tr  >
	 <th width="30%" align="left"><font size="'.$size.'">P.O NO & Date</font></th>
	 <th width="70%"  align="left"><font size="'.$size.'">'.$INVOICE_DATA['PoNo'].' & '.$INVOICE_DATA['PoDate'].'</font></th>
	 </tr>
	 <tr>
	 <th align="left"><font size="'.$size.'">PRODUCTION CODES</font></th>
	 <th align="left"><font size="'.$size.'">'.$DayCodes.'</font></th>
	 </tr><tr  >
	 <th align="left"><font size="'.$size.'">TYPE OF PACKING</font></th>
	 <th align="left"><font size="'.$size.'">'.$PackStyle.'</font></th>
	 </tr><tr  >
	 <th align="left"><font size="'.$size.'">TOTAL MASTER CARTONS</font></th>
	 <th align="left"><font size="'.$size.'">'.$McsTotal.' MCS</font></th>
	 </tr>
 </table>';
	

return $content;
}
*/

function getRawTable(){
global $iconn,$ID,$C_NAME,$TblQcEiaTestResultHeaders,$INVOICE_ID,$TblQcMBResult;	
 $size=8;
    $sql="SELECT * FROM $TblQcMBResult WHERE `MbReportID`='$ID' order by ID ASC " ; 
  $stmt=$iconn->query($sql);
 
	while($post_details=$stmt->fetch_array()){
	   $ary_fields[]=$post_details;
	}


	 $sql="SELECT * FROM $TblQcEiaTestResultHeaders WHERE TestID=3 and Country='$C_NAME' ORDER BY SortOrder ASC";
	$stmt_HEADER=$iconn->query($sql);
 $content = '<table border="1" cellspacing="0"  cellpadding="0">';
	
	
//	print_r($ary_fields);
while($post_details=$stmt_HEADER->fetch_array()){
	 $DefValue=$post_details['DefaultValue'];
	 $ColumnName=$post_details['ColumnName'];
	  $content .= '<tr  >
	 <th align="center"><font size="'.$size.'">'.$post_details['ParamName'].'</font></th>';

	 
	 foreach($ary_fields as $ITEM){
		 $RowCld=getMcs($ITEM['CLID']);
		 $RowCld['VARITY']=$ITEM['VARITY'];
		 $RowCld['TPC']=$ITEM['Tpc'];
		 $RowCld['DateOfAnalysis']=$ITEM['DateOfAnalysis'];
		 $RowCld['DateOfIssuance']=$ITEM['DateOfIssuance'];
		
		if($DefValue !='0'){
		 $RowCld["$ColumnName"]=$DefValue;
		 }
		
		 $content .= '<th align="center"><font size="'.$size.'">'.$RowCld["$ColumnName"].'</font></th>';					
		 		 
		}	 
	  $content .= '</tr>';
}
return $content;
}


function getCookedTable(){
global $iconn,$ID,$C_NAME,$TblQcEiaTestResultHeaders,$TblQcMBResult;	
  $IsMultiple=false;
  $size=8;
   $sql="SELECT * FROM $TblQcMBResult WHERE `MbReportID`='$ID' order by ID ASC " ;
  $stmt=$iconn->query($sql);
 
	while($post_details=$stmt->fetch_array()){
	   $ary_fields[]=$post_details;
	}


	 $sql="SELECT * FROM $TblQcEiaTestResultHeaders WHERE TestID=3 and Country='$C_NAME' ORDER BY SortOrder ASC";
	$stmt_HEADER=$iconn->query($sql);
	$content = '<table border="1" cellspacing="0"  cellpadding="0">';
	
while($post_details=$stmt_HEADER->fetch_array()){
	
	//print_r($post_details); exit;
	 $DefValue=$post_details['DefaultValue']; 
	  $ColumnName=$post_details['ColumnName'];
	 $MULTIPLE_COUNT=$post_details['Multiple'];
	  $content .= '<tr>
	 <th align="center"><font size="'.$size.'">'.$post_details['ParamName'].'</font></th>';

	 
	 foreach($ary_fields as $ITEM){
		 $RowCld=getMcs($ITEM['CLID']);
		 $RowCld['VARITY']=$ITEM['VARITY'];
		  $RowCld['TPC']=$ITEM['Tpc'];
		 $RowCld['DateOfAnalysis']=$ITEM['DateOfAnalysis'];
		 $RowCld['DateOfIssuance']=$ITEM['DateOfIssuance'];
		
		
		if($DefValue !='0'){
		  $RowCld["$ColumnName"]=$DefValue;
		 }
		 
		if($ColumnName == 'TPC'){			 
			$Data=getMultipleTpc($RowCld["$ColumnName"]);
			$content .= '<th>'.$Data.'</th>';
			$IsMultiple=true;			
		 }else if($IsMultiple){
			$MULTIPLE_COUNT=count(EXPLODE("/",$RowCld['TPC']));
			$Data=getMultiple($RowCld["$ColumnName"],$MULTIPLE_COUNT);
			$content .= '<th>'.$Data.'</th>';					
		 }else{		
//echo $RowCld["$ColumnName"];		 
		  $content .= '<th align="center"><font size="'.$size.'">'.$RowCld["$ColumnName"].'</font></th>';					
		 }		 
		}	 
	  $content .= '</tr>';
}
return $content;
}



function getMultipleTpc($TPC){
	
	
	$size=8;
	
echo	$TPC=EXPLODE("/",$TPC); exit;
	
	$TPC_TABLE='';
	$TPC_TABLE='<table border="1" cellspacing="0"  cellpadding="3">
	<tr>';
	foreach($TPC as $Item){
	 $TPC_TABLE .= '<th align="center"><font size="'.$size.'">'.$Item.'</font></th>';										
	}
	$TPC_TABLE .= '</tr></table>';
	
	return $TPC_TABLE;
	}
	
	
	
function getMultiple($Value,$COUNT){
	 $COUNT;
	$size=8;
	$TPC_TABLE='<table border="1" cellspacing="0"  cellpadding="3">
	<tr>';
	for($Item=0 ; $Item < $COUNT ; $Item++){
	 $TPC_TABLE .= '<th align="center"><font size="'.$size.'">'.$Value.'</font></th>';										
	}
	$TPC_TABLE .= '</tr></table>';
	
	return $TPC_TABLE;
	}
	
	
function getMcs($CLID){
	global $ID,$iconn,$INVOICE_ID,$TblQcCodeList,$TblQcPoVarityList,$TblAdminPackingStyles;
	
	$SqlMcs="SELECT sum(McsTotal) as TMCS FROM $TblQcCodeList WHERE InvID='$INVOICE_ID'";
	$StmtMcs=$iconn->query($SqlMcs);
	$RowMcs=$StmtMcs->fetch_array();
	$TMCS=$RowMcs['TMCS'];
	$T_Samples =(sqrt($TMCS)+1)/2;
	$T_Samples =round($T_Samples);
		

		 
		   $sql_mc="SELECT * FROM $TblQcCodeList  as CL
left join $TblQcPoVarityList as VL on CL.VarietyID=VL.VarietyID
left join $TblAdminPackingStyles as PL ON CL.PackingStyleID=PL.PCKID  WHERE  CLID='$CLID' ORDER BY `DayCode` ASC";  
		  $stmt_mc=$iconn->query($sql_mc);
		   $RowCld=$stmt_mc->fetch_array();
		  
		  
		  
		   
		   $EachSample=($RowCld['McsTotal']/$TMCS)* $T_Samples;
		   $RowCld['EachSample']=floor($EachSample);
		   
		//   print_r($RowCld);
		 return $RowCld;
	
}




?>   