<?php


$QueryParms = $_GET['Q'];



session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";



$ReportName = $_GET['ReportName'];
$PoID = $_GET['PoID'];
$TYPE = $_GET['TYPE'];


if ($TYPE == '') {
	$sql_code_list = "SELECT sum(SlabWeight*MFactor) as DeWgt, SPGD.Variety,SPGD.QcDescription,Grades,McsGrade,DayCode,VerifiedBy ,
TotalSlabs,SlabWeight FROM QcCodeList as QcCl 
left Join ScrmPoGradeData AS SPGD on QcCl.PoGradeID=SPGD.ID 
left join AdminPackingStyles as Aps on QcCl.PackingStyleID=Aps.PCKID 
WHERE QcCl.InvID='$PoID' Group by QcCl.CLID ORDER BY `CLID` ASC";
} else {
	$sql_code_list = "SELECT DISTINCT($TblQcCodeList.VarietyID) as VarietyID FROM `$TblQcCodeList`
 LEFT JOIN $TblQcPoVarityList on $TblQcCodeList.VarietyID= $TblQcPoVarityList.VarietyID WHERE $TblQcCodeList.InvID='$PoID' ORDER BY `CLID` ASC";
}
//echo $sql_code_list;  exit;
$StmtCodeList = $iconn->query($sql_code_list);


$SqlVarifiedBy = "SELECT VerifiedBy, LbuDesignation FROM `QcCoveringLetter` as CL 
				LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
				 WHERE InvID='$PoID' ";
$StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
$RowVarifiedBy = $StmtVarifiedBy->fetch_array();
$VERIFIED_BY = $RowVarifiedBy['VerifiedBy'];
$Designation = $RowVarifiedBy['LbuDesignation'];


$SqlVarifiedBy = "SELECT VerifiedBy,Brand,LbuDesignation,PrintStatus FROM `$TblQcCodeList` 
as CL LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
WHERE InvID='$PoID' ORDER BY `CLID` ASC";
$StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
while ($rowBrand = $StmtVarifiedBy->fetch_array()) {
	
	//ROBO
	$Brand = trim($rowBrand['Brand']);
	 $PrintStatus = trim($rowBrand['PrintStatus']); 

	if($Brand!='' && $PrintStatus!=0){
		$AryBrand[] = $Brand;
	}
	//$AryBrand[] = trim($rowBrand['Brand']);

}
$Brand = implode(",", array_unique($AryBrand));

IF($Brand==''){
	$Brand='--'; 
 }
 
$sql = "SELECT * FROM `$TblScrmPoData`
 join $TblScrmConsigneeDetails on $TblScrmPoData.ConsigneeID=$TblScrmConsigneeDetails.ConID
  left join $TblScrmPortCities on $TblScrmPoData.PortOfDischarge=$TblScrmPortCities.PortId

 WHERE $TblScrmPoData.PoID='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();

$DestinationCountry = $row['DestinationCountry'];
if ($DestinationCountry == 26) {
	$HEADERS_COUNT = 5;
} else {
	$HEADERS_COUNT = 6;
}


$T_GRADES_ARY = array();




$SqlGrades = "SELECT DISTINCT(Grades) FROM `$TblQcCodeList`
 WHERE InvID='$PoID' ORDER BY ABS(Grades)";
$StmtGrades = $iconn->query($SqlGrades);

while ($RowGrades = $StmtGrades->fetch_array()) {
	$TempGrades = explode(",", $RowGrades['Grades']);
	$T_GRADES_ARY = array_merge($T_GRADES_ARY, $TempGrades);
}

natsort($T_GRADES_ARY);
$T_GRADES_ARY = array_unique($T_GRADES_ARY);
$PortOfLoading = getPortDetails($row['PortOfLoading'],$iconn)['PortName'];
$FinalDestination = getPortDetails($row['FinalDestination'],$iconn)['PortName'];
$CountryName =  getCountryDetails($row['ConsigneeCountry'],$iconn)['CountryName'];

$header = 11;
$sub_header = 7;
$head = 16;

$pdf->SetFont($FONT_ARI, 'BU', $head);
$pdf->Cell(0, 10, 'PRODUCTION INSTRUCTION', 0, 1, 'C');
$pdf->SetFont($FONT_ARI, '', $header);
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
$pdf->Cell(0, 5, ': ' . strtoupper($FinalDestination) . ',' . $CountryName, 0, 1, 'L');
$pdf->Cell(0, 0.5, '', 0, 1, 'L');

$size = 100 / 11;

$content = '';
$content .= '

      	<table border="1" cellspacing="0" cellpadding="3">
				<tr>
           		<th  align="center"  width="' . ($size * 3) . '%"><font size="7">Variety Of The Product/Freezing</font></th>
                <th  align="center" width="' . ($size) . '%"><font size="7">Grade</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">No Of Mcs</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">Deglazed Weight</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">Packing Count/Lb</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">No of Pieces</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">Production Date</font></th>';
if ($DestinationCountry != 26) {

	$content .= '	<th  align="center" width="' . ($size) . '%"><font size="7">Best if Used By</font></th>';
}
$content .= '	</tr>';








while ($RowCodeData = $StmtCodeList->fetch_array()) {
	$Grades = $RowCodeData['Grades'];
	$SqlPackingCounts = "SELECT * FROM `QcPackingCounts` WHERE Grade='$Grades'";
	$StmtPackingCounts = $iconn->query($SqlPackingCounts);
	$RowPackingCounts = $StmtPackingCounts->fetch_array();
	$NoOfPieces = $RowCodeData['TotalSlabs'] * $RowPackingCounts['PackCount'];

	$Variety = $RowCodeData['QcDescription'] ? $RowCodeData['QcDescription'] : $RowCodeData['Variety'];

	$content .= '<tr>
           		<th  align="center"  width="' . ($size * 3) . '%"><font size="7">' . $Variety . '</font></th>
                <th  align="center" width="' . ($size) . '%"><font size="7">' . $RowCodeData['Grades'] . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . $RowCodeData['McsGrade'] . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . round($RowCodeData['DeWgt'], 2) . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . $RowPackingCounts['PackCount'] . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . $NoOfPieces . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . getDayCodeDate($RowCodeData['DayCode']) . '</font></th>';
	if ($DestinationCountry != 26) {
		$content .= '	<th  align="center" width="' . ($size) . '%"><font size="7">' . getUsebyDate(getDayCodeDate($RowCodeData['DayCode'])) . '</font></th>';
	}
	$content .= '</tr>';
}

/* foreach($varity_ids as $VARITY_ID){

	$VarityName=getVarityName($VARITY_ID,$iconn);
	$content .= generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$VarityName,100,$TYPE,$HEADERS_COUNT);

	}*/


$content .= '<tr>';
if ($DestinationCountry != 26) {
	$content .= '<th  align="center"  width="' . ($size * ($HEADERS_COUNT - 2)) . '%"><font size="7"> TOTAL </font></th>';
} else {
	$content .= '<th  align="center"  width="' . ($size * ($HEADERS_COUNT - 1)) . '%"><font size="7"> TOTAL </font></th>';
}
$content .= getTotal($PoID, $iconn, $T_GRADES_ARY, $TYPE, $HEADERS_COUNT);
$content .= '</tr>';
$content .= '</table>';
$pdf->writeHTML($content, false);




$pdf->Ln();

$COUNT = 1;








function generateRow($VARIETY_ID, $iconn, $T_GRADES_ARY, $nameee, $t_width, $TYPE, $HEADERS_COUNT)
{
	global $TblQcCodeList;
	global $TblQcPoVarityList;
	global $TblQcTracebilityList;

	$SqlCl = "SELECT * FROM $TblQcCodeList as CL
						left join $TblQcPoVarityList  as VL on CL.VarietyID=VL.VarietyID
						left join $TblQcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.VarietyID='$VARIETY_ID' ORDER BY `CLID` ASC";
	$StmtCl = $iconn->query($SqlCl);

	$contents .= '<tr>
				  <td align="left"  width="' . $t_width . '%" >
				  <font size="8"><b> VARIETY :: </b>' . $nameee . '</font></td>
				  </tr>';


	while ($RowCl = $StmtCl->fetch_assoc()) {



		$McsGrade = $RowCl['McsGrade'];
		$McsGrade = explode(',', $McsGrade);

		$ARY_GRADES = $RowCl['Grades'];
		$ARY_GRADES = explode(',', $ARY_GRADES);

		global $size;

		$contents .= '
				<tr>
				<td align="center"  width="' . $size . '%"><font size="8">' . $RowCl['DayCode'] . '</font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="8">' . $RowCl['ShortName'] . '<br> ' .
			$RowCl['PACKING'] . $GLAZE . '  </font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="7">' . $RowCl['Brand'] . '</font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="7">' . $RowCl['Tracebility'] . '</font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="7">' . $RowCl['FarmTraceNo'] . '</font></td>
			';



		foreach ($T_GRADES_ARY as $ItemGrade) {
			if (in_array($ItemGrade, $ARY_GRADES, true)) {
				$Index = array_search($ItemGrade, $ARY_GRADES);
				$MCS = $McsGrade[$Index];
				$contents .= '<td align="center" width="' . $size . '%"><font size="8">' . $MCS . '</font></td>';
			} else {
				$contents .= '<td align="center" width="' . $size . '%"><font size="8"> *** </font></td>';
			}
		}
		$contents .= '<td align="center" width="' . $size . '%" ><font size="8">' . array_sum($McsGrade) . '</font></td></tr>';
	}
	return $contents;
}

function generateTableHead($T_GRADES_ARY, $HEADERS_COUNT)
{


	global $size;
	foreach ($T_GRADES_ARY as $value) {
		$contents .= '<th width="' . $size . '%" align="center"><font size="7">' . $value . '</font></th>';
	}
	return $contents;
}



function getTotal($PoID, $iconn, $T_GRADES_ARY, $TYPE, $HEADERS_COUNT)
{
	global $size;
	global $TblQcCodeList;

	if ($TYPE == '') {
		$sql_code_list = "SELECT McsGrade,Grades FROM `$TblQcCodeList` WHERE InvID='$PoID'  ORDER BY `CLID` ASC";
	} else {
		$sql_code_list = "SELECT McsGrade,Grades FROM `$TblQcCodeList` WHERE InvID='$PoID' AND SHORT_NAME='$TYPE' ORDER BY `CLID` ASC";
	}

	//echo $sql_code_list;
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
		//$contents .= '<td align="center"  width="'.$size.'%"><font size="8">'.$MCS.'</font></td>';
	}
	$contents .= '<td align="center"  width="' . $size . '%"><font size="8">' . array_sum($AryTMcs) . '</font></td>';
	return $contents;
}

/*function getVarityName($VARIETY_ID,$iconn){
	global $TblQcPoVarityList;
  $sql="SELECT * FROM `$TblQcPoVarityList`  WHERE `VarietyID`='$VARIETY_ID'";  
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();
  $name=$row['FullName'];
 return $name;
}*/

/*function getRowWidthh($T_GRADES_ARY,$HEADERS_COUNT){
	 $size=count($T_GRADES_ARY)+$HEADERS_COUNT;
	 $size=100/$size;
		return $size;
	}*/
$pdf->Cell(49, 15, '', 0, 1, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);

$pdf->Cell(49, 5, 'For ' . $EXPORTOR, 0, 1, 'L');
$pdf->Cell(49, 15, '', 0, 1, 'L');
$pdf->SetFont($FONT_ARI, '', $header);

$pdf->Cell(49, 5, $VERIFIED_BY, 0, 1, 'L');
$pdf->Cell(49, 5, $Designation, 0, 1, 'L');

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