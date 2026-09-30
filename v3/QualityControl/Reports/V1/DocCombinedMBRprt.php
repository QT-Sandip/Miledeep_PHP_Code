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
//$C_NAME=$RowReport['Country'];
//$INVOICE_ID=$RowReport['InvID'];
 $TYPE=$RowReport['TypeProduct'];


 $SqlInvData="SELECT * FROM $TblScrmPoData POD
	left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
	left join QcEiaTestResultHeaders as QET on QET.CountryID=POD.DestinationCountry 
	where POID='$INVOICE_ID'";
$StmtInvData=$iconn->query($SqlInvData);
$RowInvData=$StmtInvData->fetch_array();
 
$Country=$RowInvData['Country'];
$BuyerName=$RowInvData['ConsigneeName'];
$ExporterID=$RowInvData['ExporterID'];
$InvoiceNo=$RowInvData['InvNumFull'];
$C_NAME=getCountryName($Country);

 $PoID=$RowReport['InvID'];
 
 
     $SqlOtherData="SELECT SPGD.Brand,VL.ShortName,Variety,QcDescription,McsTotal,DayCode,
	 VerifiedBy,PL.PackingStyle,PrintStatus FROM  `QcCodeList` AS CL 
   left join ScrmPoGradeData as SPGD on SPGD.ID=CL.PoGradeID
   left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
   left JOIN ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
   LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
   WHERE CL.InvID='$PoID' "; 
   
  // ECHO $SqlOtherData; EXIT;
$StmtOtherData=$iconn->query($SqlOtherData);

while($RowOtherData=$StmtOtherData->fetch_array()){
	
		
		//ROBO 231012023
	$Brand = trim($RowOtherData['Brand']);
	 $PrintStatus = trim($RowOtherData ['PrintStatus']); 

	if($Brand!='' && $PrintStatus!=0){
		$AryBrand[] = $Brand;
	}
	 $AryMcsTotal[]=$RowOtherData['McsTotal']; 
	$AryDayCode[]=$RowOtherData['DayCode'];
	$AryVerifiedBy[]=$RowOtherData['VerifiedBy'];
	$AryPackStyle[]=$RowOtherData['PackingStyle'];
	$AryShortName[]=$RowOtherData['ShortName'];
	$AryFullName[]=$RowOtherData['QcDescription']?$RowOtherData['QcDescription'] : $RowOtherData['Variety'] ;
}

$ExporterData = getExporterDetails($ExporterID);
$ExporterName=$ExporterData['ExpName'];

$Brand=implode(",",array_unique($AryBrand));
$McsTotal=array_sum($AryMcsTotal);
$DayCodes=implode(",",array_unique($AryDayCode));
$PackStyle=implode(",",array_unique($AryPackStyle));
//$VerifiedBy=implode(",",array_unique($AryVerifiedBy));
$ShortName=implode(",",array_unique($AryShortName));
$FullName=implode("<br>",array_unique($AryFullName));

//ROBO 231012023
IF($Brand==''){
	$Brand='--'; 
 }	


$HEIGHT=8;


$header=10;
$sub_header=8;
$text=6;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->SetFont('','BU',12);
$pdf->Cell(0 ,5,'IN HOUSE LABORATORY MICROBIOLOGY TEST REPORT',0,1,'C');

$HEIGHT=4;
/*
$pdf->SetFont('','',11);
$pdf->Cell(180 ,$HEIGHT,'To',0,1,'L');
$pdf->Cell(80 ,$HEIGHT,'The Deputy Director',0,1,'L');
$pdf->Cell(80 ,$HEIGHT,'Export Inspection Agency - Chennai',0,1,'L');
$pdf->Cell(80 ,$HEIGHT,'Sub Office - Vishakapatnam',0,1,'L');
$pdf->Cell(80 ,$HEIGHT,'Vishakapatnam- 530016',0,1,'L');
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(80 ,$HEIGHT,'Respected Sir/Madam ',0,1,'L');
$pdf->Cell(80 ,$HEIGHT,'Sub : Bacteriological Results & Orgenoleptic -Reg',0,1,'L');
*/
$HEIGHT=12;
/*
$pdf->SetFont($FONT_ARI,'BU',$header);

$pdf->Cell(30 ,$HEIGHT,'PROCESSOR, PACKER & EXPORTER',0,0,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(120 ,$HEIGHT,'     '.$ExporterName,0,1,'R');

$pdf->SetFont($FONT_ARI,'BU',$header);

$pdf->Cell(30 ,$HEIGHT,'BUYER',0,0,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(0 ,$HEIGHT,''.$BuyerName,0,1,'L');
$pdf->SetFont($FONT_ARI,'BU',$header);

$pdf->Cell(30 ,$HEIGHT,'INVOICE NO',0,0,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(0 ,$HEIGHT,''.$InvoiceNo,0,1,'L');
*/
//Brand

$pdf->SetFont('','',12);
$size=10;

		 
		

		 if($TYPE =='Raw'){
		 	$content .= getRawTable(); 
			
		 }else{
			$content .= getCookedTable();
			
		 }
		 		
		 $content .= '</table>'; 
		 $pdf->Ln();
         $pdf->Ln();
		  $pdf->writeHTML(getHeaderTable(), false); 
		  
		  $pdf->SetFont('','',11);
//$pdf->Cell(80 ,4,'Have been tested for Orgenoleptic parameters and Bacteriological parameters, ',0,1,'L');
//$pdf->Cell(30 ,4,'(TPD,E.COLI,STAPHYLOCOCCUS,SALMONELLA,V.P,V.CHOLERAE)Found Satisfactotry.This Product is ',0,1,'L');
//$pdf->Cell(30 ,4,'free from filth and decomposition',0,1,'L');
		 $pdf->SetFont('','BU',12);
		 $pdf->SetMargins(15, 29, 16,true);
		 $pdf->Cell(180 ,$HEIGHT,'BACTERIOLOGICAL RESULTS',0,1,'C');
		 $pdf->SetFont('','',12);
		
		 
		 $pdf->writeHTML($content, false); 






$pdf->Ln();
$pdf->SetFont('','',8);

$HEIGHT=5;
$pdf->Ln();

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell(90,10,'Analysed By :'.$TestedBy .'(QC TECHNOLOGIST)',1,0,'C',0);
$pdf->Cell(90,10,'Verified By :'.$VerifiedBy.'(SENIOR MANAGER-Q.A)',1,1,'C',0);  // cell with left and right borders

/*
$pdf->Cell(90,10,'Signature',1,0,'L',0);
$pdf->Cell(90,10,'Signature',1,1,'L',0);  // cell with left and right borders
*/
			
ob_end_clean(); 

$pdf->Ln();
$pdf->Output('MicrobiologyReport.pdf', 'I');




function getHeaderTable(){
	
	
global $ID,$McsTotal,$RowInvData,$DayCodes,$PackStyle,$ExporterName,$Brand,$ShortName;	
 $size=9;
 $content = '<table border="1" cellspacing="0"  cellpadding="3">
 
     <tr >
	 <th width="30%" align="left"><font size="'.$size.'">PROCESSOR, PACKER & EXPORTER</font></th>
	 <th width="70%"  align="left"><font size="'.$size.'">'.$ExporterName.' </font></th>
	 </tr>
	 
	 <tr >
	 <th width="30%" align="left"><font size="'.$size.'">INVOICE NO</font></th>
	 <th width="70%"  align="left"><font size="'.$size.'">'.$RowInvData['InvNumFull'].' </font></th>
	 </tr>
	 
	 <tr >
	 <th width="30%" align="left"><font size="'.$size.'">P.O NO </font></th>
	 <th width="70%"  align="left"><font size="'.$size.'">'.$RowInvData['PoNo'].' </font></th>
	 </tr>
	 
	 
	 
	 <tr>
	 <th align="left"><font size="'.$size.'">PRODUCT </font></th>
	 <th align="left"><font size="'.$size.'">'.$ShortName.'</font></th>
	 </tr>
	 
	 <tr  >
	 <th align="left"><font size="'.$size.'">TOTAL </font></th>
	 <th align="left"><font size="'.$size.'">'.$McsTotal.' MCS</font></th>
	 </tr>
	 
	  <tr  >
	 <th align="left"><font size="'.$size.'"> PACKING</font></th>
	 <th align="left"><font size="'.$size.'">'.$PackStyle.'</font></th>
	 </tr>
	 <tr >
	 <th width="30%" align="left"><font size="'.$size.'">BRAND </font></th>
	 <th width="70%"  align="left"><font size="'.$size.'">'.$Brand.' </font></th>
	 </tr>
	
	 <tr>
	 <th align="left"><font size="'.$size.'">BUYER </font></th>
	 <th align="left"><font size="'.$size.'">'.$RowInvData['ConsigneeName'].'</font></th>
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
	//ECHO $sql; EXIT;
	$stmt_HEADER=$iconn->query($sql);
	$content = '<table border="1" cellspacing="0"  cellpadding="0">';
	
while($post_details=$stmt_HEADER->fetch_array()){
	
	//print_r($post_details); exit;
	 $DefValue=$post_details['DefaultValue']; 
	  $ColumnName=$post_details['ColumnName']; 
	
	$MULTIPLE_COUNT=$post_details['Multiple'];
	  $content .= '<tr>
	 <th align="center"><font size="'.$size.'">'.$post_details['ParamName'].'</font></th>';
	// print_r($ary_fields); exit;
	 
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
			 $RowCld["$ColumnName"];		 
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
	 $TPC_TABLE .= '<th align="center"><font size="'.$size.'">' ?>
	<?php 
	
	for($Item1=0 ; $Item1 < 1 ; $Item1++){
		$TPC_TABLE .= ''.$Value.'';										
	   }
	 
	   $TPC_TABLE .= '</font></th>';										
	}
	$TPC_TABLE .= '</tr></table>';
	
	return $TPC_TABLE;
	}
	
	
function getMcs($CLID){
	global $ID,$iconn,$INVOICE_ID,$TblQcCodeList,$TblAdminPackingStyles;
	
	 $SqlMcs="SELECT sum(McsTotal) as TMCS FROM $TblQcCodeList WHERE InvID='$INVOICE_ID'"; 
	 //$SqlMcs="SELECT McsTotal as TMCS FROM $TblQcCodeList WHERE InvID='$INVOICE_ID'"; 
	$StmtMcs=$iconn->query($SqlMcs);
	$RowMcs=$StmtMcs->fetch_array();
	 $TMCS=$RowMcs['TMCS'];
	$T_Samples =(sqrt($TMCS)+1)/2;
	$T_Samples =round($T_Samples);
	
		 
		 $sql_mc="SELECT *, PL.PackingStyle as PackStyle, (SELECT SUM(McsTotal) FROM QcCodeList WHERE DayCode=CL.DayCode AND InvID='$INVOICE_ID' AND 
		TracebilityID=CL.TracebilityID and PackingStyleID=CL.PackingStyleID and VarietyID=CL.VarietyID) as DayCodeMcsTotal,
		 (SELECT GROUP_CONCAT(Grades) FROM QcCodeList WHERE DayCode=CL.DayCode AND InvID=2598 AND TracebilityID=CL.TracebilityID 
		 and PackingStyleID=CL.PackingStyleID and VarietyID=CL.VarietyID) as DayCodeGrade FROM QcCodeList as CL 

	left join AdminPackingStyles as PL ON CL.PackingStyleID=PL.PCKID 
	left join ScrmPoGradeData as SPGD ON SPGD.ID=CL.PoGradeID 
	left join ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
	left join QcTracebilityList as QCTL on QCTL.TraceID=CL.TracebilityID WHERE CLID='$CLID' ORDER BY `DayCode`,SPGD.PackingStyleID,TracebilityID ASC";  
		  $stmt_mc=$iconn->query($sql_mc);
		   $RowCld=$stmt_mc->fetch_array();
		   $RowCld['McsTotal']=$RowCld['DayCodeMcsTotal']; 
		   $RowCld['PackingStyle']=$RowCld['PackStyle']; 
		   $TypeProduct=$RowCld['TypeProduct']; 
		   $ShortName=$RowCld['ShortName']; 

		   $RowCld['ShortName']= getProductTypeCode($TypeProduct,$ShortName);
		  
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