<?php
$ID=$_GET['id'];
 $QueryParms=$_GET['Q']; 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";

$QueryParms=$_GET['Q']; 

$INVOICE_ID=$_GET['PoID'];

  $sql="SELECT * FROM $TblQcIHMBReport  WHERE `ID`='$ID'";
$stmt_BUYER_MB_REPORT=$iconn->query($sql);
$RowReport=$stmt_BUYER_MB_REPORT->fetch_array();

$C_NAME=$RowReport['COUNTRY'];
$C_NAME='USA';
//$INVOICE_ID=$RowReport['InvID'];
$TYPE=$RowReport['VARITY'];


$SQL_INVOICE_DATA="SELECT * FROM $TblScrmPoData where POID='$INVOICE_ID'";
$STMT_INVOICE_DATA=$iconn->query($SQL_INVOICE_DATA);
$INVOICE_DATA=$STMT_INVOICE_DATA->fetch_array();

$PO_NUM=$INVOICE_DATA['PoNo'];
$PO_DATA=$INVOICE_DATA['InvDate'];

$TYPE_DEST=$INVOICE_DATA['TypeCountry'];


  $sql_code_list="SELECT * FROM $TblQcCodeList as cl LEFT JOIN $TblAdminPackingStyles as ps on ps.PCKID=cl.PackingStyleID

WHERE InvID='$INVOICE_ID'  ORDER BY `DayCode` ASC";
$stmt_code_list=$iconn->query($sql_code_list); 

while($code_list_row=$stmt_code_list->fetch_array()){
       
	   $DAY_CODE=$code_list_row['DayCode'];
	   $PACKING=$code_list_row['PackingStyle'];
	   $MCS=$code_list_row['McsTotal'];
	 	  
	   $ary_code[]=$DAY_CODE;
	   $ary_PACKING[]=$PACKING;
	   $T_MCS=$T_MCS+$MCS;	
}

$ary_code=array_unique($ary_code);
$ary_PACKING=array_unique($ary_PACKING);
 $CODES=implode(',',$ary_code);
 $PACKING=implode(',',$ary_PACKING);
 
 /*
$PO_VARIETY_LIST_SQL="SELECT * FROM $TblQcPoVarityList WHERE InvID='$INVOICE_ID' and TYPE='$TYPE'";
$STMT_PO_VARIETY_LIST=$iconn->query($PO_VARIETY_LIST_SQL);
*/

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
$pdf->Cell(90,5,'Tested By :'.$BUYER_MB_REPORT_ROW['TestedBy'],1,0,'L',0);
$pdf->Cell(90,5,'Verified By :'.$BUYER_MB_REPORT_ROW['VerifiedBy'],1,1,'L',0);  // cell with left and right borders


$pdf->Cell(90,10,'Signature',1,0,'L',0);
$pdf->Cell(90,10,'Signature',1,1,'L',0);  // cell with left and right borders

ob_clean();

$pdf->Ln();
$pdf->Output('payroll.pdf', 'I');




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
function getRawTable(){
global $iconn,$ID,$C_NAME,$TblQcEiaTestResultHeaders;	
 $size=8;
  $sql="SELECT * FROM $TblQcMBResult WHERE `Mb_REPORT_ID`='$ID' order by ID ASC " ;
  $stmt=$iconn->query($sql);
 
	while($post_details=$stmt->fetch_array()){ 
	   $ary_fields[]=$post_details;
	}


	$sql="SELECT * FROM $TblQcEiaTestResultHeaders WHERE TestID=3 and Country='$C_NAME' ORDER BY SortOrder ASC";
	$stmt_HEADER=$iconn->query($sql);
 $content = '<table border="1" cellspacing="0"  cellpadding="0">';
	
while($post_details=$stmt_HEADER->fetch_array()){
	 $DefValue=$post_details['DefaultValue'];
	 $ColumnName=$post_details['ColumnName'];
	  $content .= '<tr  >
	 <th align="center"><font size="'.$size.'">'.$post_details['ParamName'].'</font></th>';

	 
	 foreach($ary_fields as $ITEM){
		 $RowCld=getMcs($ITEM['P_CODES_REG_NUM'],$ITEM['DOA_DOC']);
		 $RowCld['VARITY']=$ITEM['VARITY'];
		 $RowCld['TPC']=$ITEM['TPC'];
		
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
   echo $sql="SELECT * FROM $TblQcMBResult WHERE `MbReportID`='$ID' order by ID ASC " ; exit;
  $stmt=$iconn->query($sql);
 
	while($post_details=$stmt->fetch_array()){
	   $ary_fields[]=$post_details;
	}


	 $sql="SELECT * FROM $TblQcEiaTestResultHeaders WHERE TestID=3 and Country='$C_NAME' ORDER BY SortOrder ASC";
	$stmt_HEADER=$iconn->query($sql);
	$content = '<table border="1" cellspacing="0"  cellpadding="0">';
	
while($post_details=$stmt_HEADER->fetch_array()){
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
	
	echo $SqlMcs="SELECT sum(McsTotal) as TMCS FROM $TblQcCodeList WHERE InvID='$INVOICE_ID'"; exit;
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