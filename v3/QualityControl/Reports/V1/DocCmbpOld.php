<?php
//ini_set('display_errors', 1); 
session_start();
//error_reporting(E_ALL);
 include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
    	include "PrintHeaderInc.php";

$ID=$_GET['RID'];
$INVOICE_ID=$_GET['PoID'];

 $sql="SELECT * FROM $TblQcMBReport  WHERE `ID`='$ID'"; 
$stmt_BUYER_MB_REPORT=$iconn->query($sql);
$RowReport=$stmt_BUYER_MB_REPORT->fetch_array();

$TestedBy=$RowReport['TestedBy'];
 $VerifiedBy=$RowReport['VerifiedBy'];

//$C_NAME='USA';
$C_NAME=$RowReport['Country'];
//$INVOICE_ID=$RowReport['InvID'];
 $TYPE=$RowReport['TypeProduct'];


$SQL_INVOICE_DATA="SELECT * FROM $TblScrmPoData POD
	left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
	where POID='$INVOICE_ID'";
$STMT_INVOICE_DATA=$iconn->query($SQL_INVOICE_DATA);
$INVOICE_DATA=$STMT_INVOICE_DATA->fetch_array();

$BuyerName=$INVOICE_DATA['ConsigneeName'];

 $PoID=$RowReport['InvID'];
 
 
 
 
    $SqlOtherData="SELECT Brand,ShortName,FullName,McsTotal,DayCode,VerifiedBy,PackingStyle 
	FROM `QcCodeList` AS CL 

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
//$VerifiedBy=implode(",",array_unique($AryVerifiedBy));
$ShortName=implode(",",array_unique($AryShortName));
$FullName=implode("<br>",array_unique($AryFullName));

//require_once('../tcpdf/tcpdf.php'); 
	


$HEIGHT=8;


$header=10;
$sub_header=8;
$text=6;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->SetFont('','BU',12);
$pdf->Cell(0 ,5,'IN HOUSE LABORATORY MICROBIOLOGY TEST REPORT',0,1,'C');

$pdf->Cell(180 ,$HEIGHT,'VARIETY',0,1,'L');


$pdf->SetFont($FONT_ARI,'B',$header);
$pdf->writeHTML($FullName);

$pdf->SetFont($FONT_ARI,'BU',$header);

$pdf->Cell(30 ,$HEIGHT,'BUYER',0,0,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(0 ,$HEIGHT,''.$BuyerName,0,1,'L');
$pdf->SetFont($FONT_ARI,'BU',$header);

$pdf->Cell(30 ,$HEIGHT,'BRAND',0,0,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(0 ,$HEIGHT,''.$Brand,0,1,'L');


//Brand

$pdf->SetFont('','',12);
$size=10;

		 
		

		 if($TYPE =='Raw'){
		 	$content .= getRawTable(); 
			
		 }else{
			$content .= getCookedTable();
			
		 }
		 		
		 $content .= '</table>'; 
		 
		  $pdf->writeHTML(getHeaderTable(), false); 
		 $pdf->SetFont('','BU',12);
		 $pdf->SetMargins(15, 29, 16,true);
		 $pdf->Cell(180 ,$HEIGHT,'TEST RESULT',0,1,'L');
		 $pdf->SetFont('','',12);
		
		 
		 $pdf->writeHTML($content, false); 






$pdf->Ln();
$pdf->SetFont('','',8);

$HEIGHT=5;
$pdf->Ln();

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell(90,5,'Tested By :'.$TestedBy,1,0,'L',0);
$pdf->Cell(90,5,'Verified By :'.$VerifiedBy,1,1,'L',0);  // cell with left and right borders


$pdf->Cell(90,10,'Signature',1,0,'L',0);
$pdf->Cell(90,10,'Signature',1,1,'L',0);  // cell with left and right borders

			
ob_end_clean(); 

$pdf->Ln();
$pdf->Output('MicrobiologyReport.pdf', 'I');




function getHeaderTable(){
global $ID,$McsTotal,$INVOICE_DATA,$DayCodes,$PackStyle;	
 $size=9;
 $content = '<table border="1" cellspacing="0"  cellpadding="3">
	<tr  >
	 <th width="30%" align="left"><font size="'.$size.'">P.O NO & Date</font></th>
	 <th width="70%"  align="left"><font size="'.$size.'">'.$INVOICE_DATA['PoNo'].' & '.date('d-m-Y',strtotime($INVOICE_DATA['PoDate'])).'</font></th>
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
	
	$TPC=EXPLODE("/",$TPC);
	
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
	 //$SqlMcs="SELECT McsTotal as TMCS FROM $TblQcCodeList WHERE InvID='$INVOICE_ID'"; 
	$StmtMcs=$iconn->query($SqlMcs);
	$RowMcs=$StmtMcs->fetch_array();
	 $TMCS=$RowMcs['TMCS'];
	$T_Samples =(sqrt($TMCS)+1)/2;
	$T_Samples =round($T_Samples);
	
	

		 
		  /* $sql_mc="SELECT * FROM $TblQcCodeList  as CL
left join $TblQcPoVarityList as VL on CL.VarietyID=VL.VarietyID
left join $TblAdminPackingStyles as PL ON CL.PackingStyleID=PL.PCKID  WHERE  CLID='$CLID' ORDER BY `DayCode` ASC";  
		 */
		 
		 $sql_mc="SELECT *, 
		 (SELECT SUM(McsTotal) FROM $TblQcCodeList WHERE DayCode=CL.DayCode 
		 AND InvID=$INVOICE_ID  AND TracebilityID=CL.TracebilityID and PackingStyleID=CL.PackingStyleID and VarietyID=CL.VarietyID) as DayCodeMcsTotal,
		 
		 (SELECT GROUP_CONCAT(Grades) FROM $TblQcCodeList WHERE DayCode=CL.DayCode AND 
		 InvID=$INVOICE_ID AND TracebilityID=CL.TracebilityID and PackingStyleID=CL.PackingStyleID and VarietyID=CL.VarietyID) as DayCodeGrade 
		 
		 FROM $TblQcCodeList as CL left join $TblQcPoVarityList as VL on CL.VarietyID=VL.VarietyID
		 left join $TblAdminPackingStyles as PL ON CL.PackingStyleID=PL.PCKID 
		 WHERE CLID='$CLID' ORDER BY `DayCode`,PackingStyleID,TracebilityID ASC;";  
		  $stmt_mc=$iconn->query($sql_mc);
		   $RowCld=$stmt_mc->fetch_array();
		   $RowCld['McsTotal']=$RowCld['DayCodeMcsTotal']; 
		   $RowCld['Grades']=$RowCld['DayCodeGrade']; 
		  
		// EXIT; 
		   
		  $EachSample=($RowCld['McsTotal']/$TMCS)* $T_Samples;
		  if($EachSample - floor($EachSample) > 0.54){
		  $RowCld['EachSample']=round($EachSample);
		  }else{
			$RowCld['EachSample']=floor($EachSample);
		  }
		 
		   
		//   print_r($RowCld);
		 return $RowCld;
	
}




?>   