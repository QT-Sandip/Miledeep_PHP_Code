<?php
$HEADERS_COUNT = 5;
$QueryParms = $_GET['Q'];


include ($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include ($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";


if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$FileType = $_GET['Type'];

$GroupBy = " GROUP by DayCode,PackingStyleID,TracebilityID ORDER BY `CLID` ASC";
$SqlDesignation = "SELECT distinct VerifiedBy,LbuDesignation,sum(McsGrade) as TotalMcs FROM 
`QcCodeList` QCL left join QcLabTechnologist QLT on QLT.LbuName=QCL.VerifiedBy WHERE InvID=$PoID LIMIT 1";
$StmtDesignation = $iconn->query($SqlDesignation);
$RowDesignation = $StmtDesignation->fetch_array();

$SqlTotals = "SELECT sum(McsTotal) as McsTotal ,GROUP_CONCAT(Grades) as Grades,
GROUP_CONCAT(VarietyID) as VarietyID 
FROM `QcCodeList` where InvID='$PoID'";
$StmtTotals = $iconn->query($SqlTotals);
$RowTotal = $StmtTotals->fetch_array();

$SqlVarifiedBy = "SELECT VerifiedBy,Brand,LbuDesignation,PrintStatus FROM `QcCodeList` 
      as CL LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
  WHERE InvID='$PoID' ORDER BY `CLID` ASC";

$StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
while ($rowBrand = $StmtVarifiedBy->fetch_array()) {
	//ROBO
	$Brand = trim($rowBrand['Brand']);
	$PrintStatus = trim($rowBrand['PrintStatus']);

	if ($Brand != '' && $PrintStatus != 0) {
		$AryBrand[] = $Brand;
	}
	//$AryBrand[]=$rowBrand['Brand'];

}
$Brand = implode(",", array_unique($AryBrand));
if ($Brand == '') {
	$Brand = '--';
}
// ECHO $Brand; EXIT;

$sql = "SELECT *,SCL.name as DestinationCountry ,SPC.Name as Port FROM `ScrmPoData` as SPD
 left join ScrmConsigneeDetails on SPD.ConsigneeID=ScrmConsigneeDetails.ConID
 left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 WHERE SPD.PoID='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();



$TotalMcs = $RowTotal['McsTotal'];





$AryGrades = explode(',', $RowTotal['Grades']);
$AryGrades = array_unique($AryGrades);
natsort($AryGrades);

/*
$sql_code_list="SELECT  GROUP_CONCAT(VarietyID)  as VarietyID FROM `ScrmPoGradeData` WHERE InvId='$PoID' ";
$stmt_code=$iconn->query($sql_code_list);
$row_vid=$stmt_code->fetch_array();
 $AryVarietyID=explode(',',$row_vid['VarietyID']); 
$AryVarietyID=array_unique($AryVarietyID);*/

if ($TYPE == '') {
	$sql_code_list = "SELECT  ID as PoGradeID, VarietyID FROM `ScrmPoGradeData` as SPGD WHERE InvId='$PoID' GROUP by VarietyID ORDER BY  SPGD.ID ASC";
} else {
	$sql_code_list = "SELECT ID as PoGradeID, SPGD.VarietyID FROM `ScrmPoGradeData` as SPGD 
	 left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
	 WHERE InvId='$PoID' and SPVL.TypeProduct='$TYPE' GROUP by SPGD.VarietyID ORDER BY SPGD.ID ASC";
}

$stmt_code = $iconn->query($sql_code_list);
$varity_ids = array();

while ($row = $stmt_code->fetch_array()) {
	$Item['VarietyID'] = $row['VarietyID'];
	$Item['PoGradeID'] = $row['PoGradeID'];
	$AryVarietyID[] = $Item;
}

$sql = "SELECT * FROM `ScrmPoData`
   join ScrmConsigneeDetails on ScrmPoData.ConsigneeID=ScrmConsigneeDetails.ConID
   WHERE ScrmPoData.PoID='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();

$PortOfLoading = getPortDetails($row['PortOfLoading'], $iconn)['PortName'];
$FinalDestination = getPortDetails($row['FinalDestination'], $iconn)['PortName'];
$CountryName = getCountryDetails($row['ConsigneeCountry'], $iconn)['CountryName'];

$TotalSamples = (sqrt($TotalMcs) + 1) / 2;


$SqlVarifiedBy = "SELECT VerifiedBy, LbuDesignation FROM `QcCoveringLetter` as CL 
					LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
				 	WHERE InvID='$PoID' ";

$StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
$RowVarifiedBy = $StmtVarifiedBy->fetch_array();
$VERIFIED_BY = $RowVarifiedBy['VerifiedBy'];
$Designation = $RowVarifiedBy['LbuDesignation'];

$header = 11;
$sub_header = 10;

$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->Cell(0, 10, 'SAMPLE WORK SHEET ', 0, 1, 'C');
$pdf->SetFont($FONT_ARI, '', $header);
//ROBO


$pdf->Cell(40, 5, 'BUYER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['ConsigneeName'], 0, 1, 'L');
$pdf->Cell(40, 5, 'APPROVAL NUMBER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $PROCESSOR_APP_NUM, 0, 1, 'L');
$pdf->Cell(40, 5, 'P.O NUMBER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['PoNo'], 0, 1, 'L');

if ($DestinationCountry != 26) {
	$pdf->Cell(40, 5, 'BRAND', 0, 0, 'L');
	$pdf->Cell(0, 5, ': ' . $Brand, 0, 1, 'L');
}
$pdf->Cell(40, 5, 'DESTINATION', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $CountryName . ',' . strtoupper($FinalDestination), 0, 1, 'L');
if ($RowPo['ExporterID'] == 5) {
	$pdf->Cell(40, 5, 'EXPORTER', 0, 0, 'L');
	$pdf->Cell(0, 5, ': ' . $RowPo['ExpName'], 0, 1, 'L');
}
$pdf->Cell(0, 0.5, '', 0, 1, 'L');

//ROBO

$pdf->Cell(0, 0.5, '', 0, 1, 'L');
$size = $size = getRowWidth($AryGrades, $HEADERS_COUNT);


$content = '';
$content .= '

      	<table border="1" cellspacing="0" cellpadding="3">
				<tr>
           		<th  align="center"  width="' . $size . '%"><font size="7"> DAY CODE </font></th>
                <th  align="center" width="' . ($size) . '%"><font size="7">VARITY TYPE OF PACKING</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">TRACEABILITY</font></th>';

$content .= generateTableHead($AryGrades);
$content .= '
				<th  align="center" width="' . $size . '%"><font size="7"> TOTAL MCS </font></th>
				<th  align="center" width="' . $size . '%"><font size="7"> SAMPLES </font></th></tr>';


foreach ($AryVarietyID as $VARIETY_ID) {

	$VarityName = getVarityName($VARIETY_ID['PoGradeID'], $iconn);
	$content .= generateRow($VARIETY_ID['VarietyID'], $iconn, $AryGrades, $VarityName, 100, $TYPE, $HEADERS_COUNT);



}


$content .= '<tr>
			<th  align="center"  width="' . ($size * 3) . '%"><font size="7"> TOTAL </font></th>
			';
$content .= getGradeTotal($PoID, $iconn, $AryGrades, $McsTotal);
$content .= '</tr>';
$content .= '</table>';
$pdf->writeHTML($content, false);




$pdf->Ln();

$COUNT = 1;

//echo $TotalSamples; exit;

$pdf->Cell(49, 5, 'As Per sampling code (Squareroot(n) + 1)/2 = ' . floor($TotalSamples), 0, 1, 'L');
$pdf->Cell(49, 5, 'Total Mcs n = ' . $TotalMcs, 0, 1, 'L');
$pdf->Cell(49, 5, 'Where n = Total Master Cartons.', 0, 1, 'L');

$pdf->Ln();
$pdf->Cell(150, 5, 'Sample Drawn by : ', 0, 1, 'L');

$VERIFIED_BY = $RowVarifiedBy['VerifiedBy'];
$Designation = $RowVarifiedBy['LbuDesignation'];



$pdf->Cell(0, 15, '', 0, 1, 'L');
$pdf->Cell(0, 5, $VERIFIED_BY, 0, 1, 'L');
$pdf->Cell(0, 5, $Designation, 0, 1, 'L');







function generateRow($VARIETY_ID, $iconn, $T_GRADES_ARY, $nameee, $t_width, $TYPE, $HEADERS_COUNT)
{

	global $size;
	global $TotalMcs;
	global $TotalSamples, $TotalSqrSamples, $TotalSubTotalSamples;
	global $McsTotal, $PoID;

	$TOTAL_SAMPLES = (sqrt($TotalMcs) + 1) / 2;
	$TOTAL_SAMPLES = round($TOTAL_SAMPLES);



	$sql_code_list = "SELECT *,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as McsGrade_New
	FROM QcCodeList as CL
						left join QcPoVarityList  as VL on CL.VarietyID=VL.VarietyID
						left join AdminPackingStyles  as APS on APS.PCKID=CL.PackingStyleID
						left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.VarietyID='$VARIETY_ID' GROUP by DayCode,PackingStyleID,TracebilityID
						ORDER BY `CLID` ASC";


	$sql_code_list = "SELECT * FROM QcCodeList as CL 
left join  ScrmPoGradeData as SPGD on SPGD.ID=CL.VarietyID 
left join  ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID 
left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID 
WHERE SPGD.VarietyID='$VARIETY_ID' and CL.InvId='$PoID' ORDER BY `CLID` ASC";
	//uma(25/03/2022)

	$stmt_code_list = $iconn->query($sql_code_list);

	$contents .= '<tr>
				  <td align="left"  width="' . $t_width . '%" >
				  <font size="8"><b> VARIETY :: </b>' . $nameee . '</font></td>
				  </tr>';


	while ($row = $stmt_code_list->fetch_assoc()) {
		$FARM_ID = $row['TracebilityID'];
		$PackingStyleID = $row['PackingStyleID'];

		$sql_FARM_ID = "SELECT * FROM QcTracebilityList WHERE TraceID='$FARM_ID'";
		$stmt_FARM_ID = $iconn->query($sql_FARM_ID);
		$r_FARM_ID = $stmt_FARM_ID->fetch_assoc();

		if($r_FARM_ID['FarmerCode']!=''){
			$FarmerCode=$r_FARM_ID['FarmerCode'].' - ';
		}

		$FarmTraceID = $FarmerCode.$r_FARM_ID['Tracebility'];


		$sql_varity_list = "SELECT * FROM QcPoVarietyList WHERE VarietyID='$VARIETY_ID'";
		$stmt_varity_list = $iconn->query($sql_varity_list);
		$r_varity_list = $stmt_varity_list->fetch_assoc();

		$SqlPackStyle = "SELECT * FROM AdminPackingStyles WHERE PCKID='$PackingStyleID'";
		$StmtPackStyle = $iconn->query($SqlPackStyle);
		$RowPackStyle = $StmtPackStyle->fetch_assoc();


		$ARY_MCS = $row['McsGrade'];
		//$ARY_MCS=$row['McsGrade_New'];
		$ARY_MCS = explode(',', $ARY_MCS);

		$ARY_GRADES = $row['Grades'];
		//$ARY_GRADES=$row['Grades_New'];
		$ARY_GRADES = explode(',', $ARY_GRADES);



		$contents .= '
				<tr>
				<td align="center"  width="' . $size . '%"><font size="8">' . $row['DayCode'] . '</font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="8">' . $r_varity_list['ShortName'] . '<br> 
				' . $RowPackStyle['PackingStyle'] . $GLAZE . '  </font></td>

				<td align="center"  width="' . ($size) . '%" ><font size="7">' . $FarmTraceID . '</font></td>
			';

		//	print_r($T_GRADES_ARY);

		$v_sample = 0;
		foreach ($T_GRADES_ARY as $ItemGrade) {
			if (in_array($ItemGrade, $ARY_GRADES, true)) {
				$Index = array_search($ItemGrade, $ARY_GRADES);
				$MCS = $ARY_MCS[$Index];
				// $each_sample=($MCS/$TotalMcs)*$TotalSamples;
				$each_sample = ($MCS / $TotalMcs) * $TotalSamples;
				$each_sample = round($each_sample);
				$v_sample = $v_sample + $each_sample;


				$contents .= '
			<td align="center" width="' . $size . '%"><font size="8">' . $MCS . '</font></td>
			<!--<td align="center" width="' . $size . '%"><font size="8">' . $each_sample . '</font></td>-->';
			} else {
				$contents .= '
			<!--<td align="center" width="' . $size . '%"><font size="8"> *** </font></td>-->
			<td align="center" width="' . $size . '%"><font size="8"> *** </font></td>';
			}
		}
		$contents .= '
		<td align="center" width="' . $size . '%"><font size="8">' . array_sum($ARY_MCS) . '</font></td>
		<td align="center" width="' . $size . '%" ><font size="8">' . $v_sample . '</font></td></tr>';
	}

	return $contents;
}


function generateTableHead($T_GRADES_ARY)
{
	global $size;



	foreach ($T_GRADES_ARY as $value) {
		$contents .= '<th width="' . $size . '%" align="center"><font size="7">' . $value . '</font></th>';
	}
	return $contents;
}




function getGradeTotal($PoID, $iconn, $T_GRADES_ARY, $S_W_T_MCS)
{
	global $size;
	global $TotalMcs;
	global $TotalSamples, $TotalSubTotalSamples;


	$TOTAL_SAMPLES = (sqrt($S_W_T_MCS) + 1) / 2;
	$TOTAL_SAMPLES = round($TOTAL_SAMPLES);

	$sql_code_list = "SELECT *,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as McsGrade_New
	FROM QcCodeList as CL
						left join QcPoVarietyList  as VL on CL.VarietyID=VL.VarietyID
						left join AdminPackingStyles  as APS on APS.PCKID=CL.PackingStyleID
						left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.InvID='$PoID' GROUP by DayCode,PackingStyleID,TracebilityID
						ORDER BY `CLID` ASC";
	//$stmt_code_list=$iconn->query($sql_code_list);

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
			/*uma(25/03/2022)*/
			$ARY_MCS = explode(',', $RowData['McsGrade_New']);
			$ARY_GRADES = explode(',', $RowData['Grades_New']);
			/*uma(25/03/2022)*/
			if (in_array($ItemGrade, $ARY_GRADES, true)) {
				$Index = array_search($ItemGrade, $ARY_GRADES);
				$MCS += $ARY_MCS[$Index];
			}
		}

		//$each_sample=($MCS/$TotalMcs)*$TotalSamples;
		$each_sample = ($MCS / $TotalMcs) * $TotalSamples;
		$each_sample = round($each_sample);

		$AryTMcs[] = $each_sample;
		$contents .= '
		<td align="center"  width="' . $size . '%"><font size="8">' . $MCS . '</font></td>
	<!--	<td align="center"  width="' . $size . '%"><font size="8">' . $each_sample . '</font></td>-->';
	}
	$contents .= '
		<td align="center" width="' . $size . '%"><font size="8">' . $TotalMcs . '</font></td>
		<td align="center"  width="' . $size . '%"><font size="8">' . array_sum($AryTMcs) . '</font></td>';
	return $contents;

}

ob_end_clean();


if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $row['PoNo'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName, 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName;
	$pdf->Output($FileName, $FileType);

}

?>