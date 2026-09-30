<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
$HEADERS_COUNT=5;
$QueryParms=$_GET['Q'];  

$IsHeader=1;
$IsFooter=1;


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$PoID = $_GET['PoID'];
//include "PrintHeaderInc.php";

  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET); 
  }

$PoID=$_GET['PoID'];
include "Constants.php";
include "PrintHeaderInc.php";

$ReportName=$_GET['ReportName'];
$FileType=$_GET['Type']; 


$SqlTotals="SELECT sum(McsTotal) as McsTotal ,GROUP_CONCAT(Grades) as Grades,
GROUP_CONCAT(VarietyID) as VarietyID 
FROM QcCodeList where InvID='$PoID'"; 
$StmtTotals=$iconn->query($SqlTotals);
$RowTotal=$StmtTotals->fetch_array();  

$SqlVarifiedBy="SELECT VerifiedBy,Brand,LbuDesignation,sum(McsGrade) as TotalMcs 
FROM QcCodeList as CL 
LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
WHERE InvID='$PoID' ORDER BY CLID ASC"; 
$StmtVarifiedBy=$iconn->query($SqlVarifiedBy);

while($rowBrand=$StmtVarifiedBy->fetch_array()){
	$AryBrand[]=$rowBrand['Brand'];
	
	$VerifiedBy=$rowBrand['VerifiedBy'];
    $LabUserDesignation=$rowBrand['LbuDesignation'];
	
}
 $Brand=implode(",",array_unique($AryBrand)); 
 
 
$SqlCodeList="SELECT  ID as PoGradeID, SPGD.VarietyID,SPVL.ShortName FROM `ScrmPoGradeData` as SPGD
 left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
 WHERE InvId='$PoID' GROUP by SPVL.ShortName ORDER BY  SPGD.ID ASC";
 
$StmtCodeList = $iconn->query($SqlCodeList);
$varity_ids = array();

while ($RowCodeList = $StmtCodeList->fetch_array()) {
	$Item['VarietyID'] = $RowCodeList['VarietyID'];
	$Item['PoGradeID'] = $RowCodeList['PoGradeID'];
	$ShortName= $RowCodeList['ShortName'];
	$varity_ids[]=$Item;
}


$sql="SELECT *,SCL.name as DestinationCountry ,SPC.Name as Port FROM ScrmPoData as SPD
 left join ScrmConsigneeDetails on SPD.ConsigneeID=ScrmConsigneeDetails.ConID
 left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 WHERE SPD.PoID='$PoID'";
$StmtCountries=$iconn->query($sql);
$row=$StmtCountries->fetch_array();



$TotalMcs=$RowTotal['McsTotal'];
$AryGrades=explode(',', $RowTotal['Grades']);
$AryGrades=array_unique($AryGrades);
natsort($AryGrades);

$AryVarietyID=explode(',',$RowTotal['VarietyID']);
$AryVarietyID=array_unique($AryVarietyID);

 $SqlSamples="SELECT * FROM QcFilthSamplesCount WHERE LowerLimit<='$TotalMcs' &&
 UpperLimit >='$TotalMcs'";
         $StmtSamples=$iconn->query($SqlSamples);
         $RowSamples=$StmtSamples->FETCH_ARRAY();
         $TotalSamples=	$RowSamples['NoofSamples'];

//$TotalSamples =(sqrt($TotalMcs)+1)/2;
//$TotalSamples =round((sqrt($TotalMcs)+1)/2); 



$header=11;
$sub_header=10;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(0 ,10,'SAMPLE WORK SHEET ',0,1,'C');
$pdf->SetFont($FONT_ARI,'',$header);
//ROBO


$pdf->Cell(40 ,5,'BUYER',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['ConsigneeName'],0,1,'L');
$pdf->Cell(40 ,5,'APPROVAL NUMBER',0,0,'L');
$pdf->Cell(0 ,5,': '.$PROCESSOR_APP_NUM,0,1,'L');
$pdf->Cell(40 ,5,'P.O NUMBER',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['PoNo'],0,1,'L');

if($DestinationCountry!=26){
$pdf->Cell(40 ,5,'BRAND',0,0,'L');
$pdf->Cell(0 ,5,': '.$Brand,0,1,'L');
}
$pdf->Cell(40 ,5,'DESTINATION',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['Port'].','.$row['DestinationCountry'],0,1,'L');
IF($RowPo['ExporterID']==5){
$pdf->Cell(40 ,5,'EXPORTER',0,0,'L');
$pdf->Cell(0 ,5,': '.$RowPo['ExpName'],0,1,'L');}
$pdf->Cell(0 ,0.5,'',0,1,'L');

//ROBO
	
function getGradeTotal($PoID, $iconn, $T_GRADES_ARY, $TYPE, $HEADERS_COUNT)
{
	global $size;
	//global QcCodeList;
	global $TblQcPoVarityList;

	if ($TYPE == '') {
		$sql_code_list = "SELECT McsGrade,Grades FROM `QcCodeList` WHERE InvID='$PoID'  ORDER BY `CLID` ASC";
	} else {

		$sql_code_list = "
		SELECT McsGrade,Grades FROM `QcCodeList` as Qcl
left join  ScrmPoGradeData as SPGD on Qcl.PoGradeID=SPGD.ID
left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
WHERE Qcl.InvID='$PoID' AND TypeProduct='$TYPE' ORDER BY `CLID` ASC";
	}

	//echo $sql_code_list; exit;
	$stmt_code_list = $iconn->query($sql_code_list);
	$AryData = array();
	$AryTMcs = array();


	while ($row = $stmt_code_list->fetch_assoc()) {
		$AryData[] = $row;
	}

	foreach ($T_GRADES_ARY as $ItemGrade) {
		$MCS = 0;
		foreach ($AryData as $RowData) {
			$ARY_MCS = explode(',', $RowData['McsGrade']);
			$ARY_GRADES = explode(',', $RowData['Grades']);

			if (in_array($ItemGrade, $ARY_GRADES, true)) {
				$Index = array_search($ItemGrade, $ARY_GRADES);
				$MCS += $ARY_MCS[$Index];
			}
		}
		$AryTMcs[] = $MCS;
		$contents .= '<td align="center"  width="' . $size . '%"><font size="8">' . $MCS . '</font></td>';
	}
	$contents .= '<td align="center"  width="' . $size . '%"><font size="8">' . array_sum($AryTMcs) . '</font></td>';
	return $contents;
}
$pdf->Cell(0 ,0.5,'',0,1,'L');
$size=$size=getRowWidth($AryGrades,$HEADERS_COUNT);


    $content = '';
    $content .= '

      	<table border="1" cellspacing="0" cellpadding="3">
				<tr>
           		<th  align="center"  width="'.$size.'%"><font size="7"> DAY CODE </font></th>
                <th  align="center" width="'.($size).'%"><font size="7">VARIETY TYPE OF PACKING</font></th>
				<th  align="center" width="'.($size).'%"><font size="7">TRACEABILITY</font></th>';

				$content .= generateTableHead($AryGrades);
				$content .= '
				<th  align="center" width="'.$size.'%"><font size="7"> TOTAL MCS </font></th>
				<th  align="center" width="'.$size.'%"><font size="7"> SAMPLES </font></th></tr>';


				foreach($AryVarietyID as $VARIETY_ID){

			  $VarityName=getVarityName($VARIETY_ID,$iconn);
			  $content .= generateRow($VARIETY_ID,$iconn,$AryGrades,$VarityName,100,$TYPE,$HEADERS_COUNT);

			  }


			$content .= '<tr>
			<th  align="center"  width="'.($size*3).'%"><font size="7"> TOTAL </font></th>
			' ;
			$content .= getGradeTotal($PoID,$iconn,$AryGrades,$McsTotal);
			$content .= '</tr>' ;
			$content .= '</table>';
			$pdf->writeHTML($content,false);




$pdf->Ln();

$COUNT=1;

//echo $TotalSamples; exit;
/*
$pdf->Cell(49 ,5,'As Per sampling code (Squareroot(n) + 1)/2 = '.floor($TotalSamples),0,1,'L');
$pdf->Cell(49 ,5,'Total Mcs n = '.$TotalMcs,0,1,'L');
$pdf->Cell(49 ,5,'Where n = Total Master Cartons.',0,1,'L');*/

$pdf->Ln();
$pdf->Cell(150 ,5,'Sample Drawn by : ',0,1,'L');

$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,$VerifiedBy,0,1,'L');
$pdf->Cell(0 ,5,$LabUserDesignation,0,1,'L');





function generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$nameee,$t_width,$TYPE,$HEADERS_COUNT){

			global $size,$PoID,$ShortName;
			global $TotalMcs; 
			global $TotalSamples,$TotalSqrSamples,$TotalSubTotalSamples;
			global $TblQcCodeList;
			global $TblQcTracebilityList;
			global $TblQcPoVarityList;
			global $TblAdminPackingStyles,$McsTotal;
	
	$TOTAL_SAMPLES =(sqrt($TotalMcs)+1)/2;
	$TOTAL_SAMPLES =round($TOTAL_SAMPLES);



	$sql_code_list="SELECT *,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as McsGrade_New
	FROM $TblQcCodeList as CL
						left join $TblQcPoVarityList  as VL on CL.VarietyID=VL.VarietyID
						left join $TblAdminPackingStyles  as APS on APS.PCKID=CL.PackingStyleID
						left join $TblQcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.VarietyID='$VARIETY_ID' GROUP by DayCode,PackingStyleID,TracebilityID
						ORDER BY CLID ASC";
//uma(25/03/2022)
	
	$stmt_code_list=$iconn->query($sql_code_list);

	$contents .= '<tr>
				  <td align="left"  width="'.$t_width.'%" >
				  <font size="8"><b> VARIETY :: </b>'.$nameee.'</font></td>
				  </tr>';


		while($row = $stmt_code_list->fetch_assoc()){
		$FARM_ID=$row['TracebilityID'];
		$PackingStyleID=$row['PackingStyleID'];

			$sql_FARM_ID="SELECT * FROM $TblQcTracebilityList WHERE TraceID='$FARM_ID'";
			$stmt_FARM_ID=$iconn->query($sql_FARM_ID);
			$r_FARM_ID = $stmt_FARM_ID->fetch_assoc();

			$FarmTraceID=$r_FARM_ID['Tracebility'];


			$sql_varity_list="SELECT * FROM $TblQcPoVarityList WHERE VarietyID='$VARIETY_ID'";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$r_varity_list = $stmt_varity_list->fetch_assoc();
			
			$SqlPackStyle="SELECT * FROM $TblAdminPackingStyles WHERE PCKID='$PackingStyleID'";
			$StmtPackStyle=$iconn->query($SqlPackStyle);
			$RowPackStyle = $StmtPackStyle->fetch_assoc();


			$ARY_MCS=$row['McsGrade'];
			$ARY_MCS=$row['McsGrade_New'];
			$ARY_MCS = explode(',', $ARY_MCS);

			$ARY_GRADES=$row['Grades'];
			$ARY_GRADES=$row['Grades_New'];
			$ARY_GRADES = explode(',', $ARY_GRADES);
			
$SqlCodeListData="SELECT *,CL.Brand AS BrandName,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as 
	  McsGrade_New FROM QcCodeList as CL 
left join  ScrmPoGradeData as SPGD on SPGD.ID=CL.VarietyID 
left join  ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID 
left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID 
WHERE  CL.InvId='$PoID'   and VL.ShortName='$ShortName' GROUP by DayCode,
CL.PackingStyleID,CL.Brand,TracebilityID ORDER BY `CLID` ASC";
//echo $SqlCodeListData; exit;
$StmtCodeListData= $iconn->query($SqlCodeListData);
$RowCl = $StmtCodeListData->fetch_assoc();

        $TypeProduct=$RowCl['TypeProduct']; 
		$ShortName=$RowCl['ShortName'];
		 $RowCl['ShortName']= getProductTypeCode($TypeProduct,$ShortName); 

			$contents .= '
				<tr>
				<td align="center"  width="'.$size.'%"><font size="8">'.$row['DayCode'].'</font></td>
				<td align="center"  width="'.($size).'%" ><font size="8">'.$RowCl['ShortName'].'<br> 
				'.$RowPackStyle['PackingStyle'].  $GLAZE . '  </font></td>

				<td align="center"  width="'.($size).'%" ><font size="7">'.$FarmTraceID.'</font></td>
			';

		//	print_r($T_GRADES_ARY);

	$v_sample=0;
		foreach($T_GRADES_ARY as $ItemGrade){
			if(in_array($ItemGrade,$ARY_GRADES,true)){
			 $Index = array_search($ItemGrade, $ARY_GRADES);
			 $MCS=$ARY_MCS[$Index];

         $SqlSamples="SELECT * FROM QcFilthSamplesCount WHERE LowerLimit<='$MCS' && UpperLimit >='$MCS'";
         $StmtSamples=$iconn->query($SqlSamples);
         $RowSamples=$StmtSamples->FETCH_ARRAY();
         $each_sample=	$RowSamples['NoofSamples'];	 
			 
			 
			// $each_sample=($MCS/$TotalMcs)*$TotalSamples;
			/* $each_sample=($MCS/$TotalMcs)*$TotalSamples;
			 $each_sample=round($each_sample);*/
				$v_sample=$v_sample+$each_sample;

		
			$contents .= '
			<td align="center" width="'.$size.'%"><font size="8">'.$MCS.'</font></td>
			<!--<td align="center" width="'.$size.'%"><font size="8">'.$each_sample.'</font></td>-->';
			}else{
			$contents .= '
			<!--<td align="center" width="'.$size.'%"><font size="8"> * </font></td>-->
			<td align="center" width="'.$size.'%"><font size="8"> * </font></td>';
			}
		}
		$contents .= '
		<td align="center" width="'.$size.'%"><font size="8">'.array_sum($ARY_MCS).'</font></td>
		<td align="center" width="'.$size.'%" ><font size="8">'.$v_sample.'</font></td></tr>';
		}

		return $contents;
	}


	function generateTableHead($T_GRADES_ARY){
		global $size;



		foreach($T_GRADES_ARY as $value){
			$contents .= '<th width="'.$size.'%" align="center"><font size="7">'.$value.'</font></th>';
		}
		return $contents;
	}



ob_end_clean();


if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$row['PoNo'];
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