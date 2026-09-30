<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$QueryParms = $_GET['Q'];



session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";



$ReportName = $_GET['ReportName'];
$PoID = $_GET['PoID'];
$TYPE = $_GET['TYPE'];


if ($TYPE == '') {

// 	$sql_code_list = "SELECT sum(SlabWeight*MFactor) as DeWgt, SPGD.Variety,SPGD.QcDescription,Grades,sum(McsGrade)McsGrade,DayCode,VerifiedBy,BestBeforeDt,BestBeforeDtText,
// 	Aps.PackingStyle,TotalSlabs,SlabWeight,UPCCode,ItemNumber FROM QcCodeList as QcCl 
// left Join ScrmPoGradeData AS SPGD on QcCl.PoGradeID=SPGD.ID 
// left join AdminPackingStyles as Aps on QcCl.PackingStyleID=Aps.PCKID 
// WHERE QcCl.InvID='$PoID' Group by  QcCl.DayCode,QcCl.VarietyID,QcCl.Grades ORDER BY `CLID` ASC";
	// Group by QcCl.CLID ORDER BY `CLID` ASC";


	$sql_code_list = "SELECT sum(SlabWeight*MFactor) as DeWgt, SPGD.Variety,SPGD.QcDescription,Grades,sum(McsGrade)McsGrade,DayCode,VerifiedBy,BestBeforeDt,BestBeforeDtText,
	Aps.PackingStyle,TotalSlabs,SlabWeight,UPCCode,ItemNumber FROM QcCodeList as QcCl 
left Join ScrmPoGradeData AS SPGD on QcCl.PoGradeID=SPGD.ID 
left join AdminPackingStyles as Aps on QcCl.PackingStyleID=Aps.PCKID 
WHERE QcCl.InvID='$PoID' Group by  QcCl.DayCode,QcCl.VarietyID,QcCl.Grades ORDER BY `DayCode` ASC";


}

else {
	$sql_code_list = "SELECT DISTINCT(QcCodeList.VarietyID) as VarietyID FROM `QcCodeList`
 LEFT JOIN QcPoVarietyList on QcCodeList.VarietyID= QcPoVarietyList.VarietyID WHERE QcCodeList.InvID='$PoID' ORDER BY `CLID` ASC";
}
// echo $sql_code_list;
// exit;
$StmtCodeList = $iconn->query($sql_code_list);




$SqlVerifiedBy = "SELECT GROUP_CONCAT(distinct Brand)as Brand,QLT.LbuDesignation,QLT.LbuName,PrintStatus,
QLTA.LbuDesignation as DesignationOfApprovedPerson,QLTA.LbuName as ApprovedBy,
GROUP_CONCAT(CASE WHEN ItemNumber != '' THEN ItemNumber END) AS ItemNumber
FROM `QcCodeList` as CL
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
 LEFT JOIN QcLabTechnologist as QLT on QLT.LUID=CL.PreparedByID 
 LEFT JOIN QcLabTechnologist as QLTA on QLTA.LUID=CL.ApprovedByID 
 WHERE InvID='$PoID' ORDER BY `CLID` ASC";

$StmtVerifiedBy = $iconn->query($SqlVerifiedBy);
$rowVerifiedBy = $StmtVerifiedBy->fetch_array();
$VerifiedBy = $rowVerifiedBy['LbuName'];
$Designation = $rowVerifiedBy['LbuDesignation'];
$DesignationOfApprovedPerson = $rowVerifiedBy['DesignationOfApprovedPerson'];
$ApprovedBy = $rowVerifiedBy['ApprovedBy'];
$ItemNumber = $rowVerifiedBy['ItemNumber'];
$ItemNumberArray = array_filter(explode(',', $ItemNumber)); // Split the string into an array
$ItemNumberCount = count($ItemNumberArray);


$StmtBrand = $iconn->query($SqlVerifiedBy);
while ($rowBrand = $StmtBrand->fetch_array()) {

	//ROBO
	$Brand = trim($rowBrand['Brand']);
	$PrintStatus = trim($rowBrand['PrintStatus']);

	// if ($Brand != '' && $PrintStatus != 0) {
	if ($Brand != '') {
		$AryBrand[] = $Brand;
	}
	//$AryBrand[] = trim($rowBrand['Brand']);

}
$Brand = implode(",", array_unique($AryBrand));

if ($Brand == '') {
	$Brand = '--';
}

$sql = "SELECT * FROM `ScrmPoData`
 join ScrmConsigneeDetails on ScrmPoData.ConsigneeID=ScrmConsigneeDetails.ConID
  left join ScrmPortCities on ScrmPoData.PortOfDischarge=ScrmPortCities.PortId
  left join QcNewAddonFieldsData QNAF on QNAF.PoID=ScrmPoData.PoID
 WHERE ScrmPoData.PoID='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();
$SGSRequestDate = (!empty($row['SGSRequestDate']) && $row['SGSRequestDate'] !== "0000-00-00")
	? date('d-m-Y', strtotime($row['SGSRequestDate']))
	: '';

$DestinationCountry = $row['DestinationCountry'];
if ($DestinationCountry == 26) {
	$HEADERS_COUNT = 6;
} else {
	$HEADERS_COUNT = 7;
}


$T_GRADES_ARY = array();




$SqlGrades = "SELECT DISTINCT(Grades) FROM `QcCodeList`
 WHERE InvID='$PoID' ORDER BY ABS(Grades)";
$StmtGrades = $iconn->query($SqlGrades);

while ($RowGrades = $StmtGrades->fetch_array()) {
	$TempGrades = explode(",", $RowGrades['Grades']);
	$T_GRADES_ARY = array_merge($T_GRADES_ARY, $TempGrades);
}

natsort($T_GRADES_ARY);
$T_GRADES_ARY = array_unique($T_GRADES_ARY);
$PortOfLoading = getPortDetails($row['PortOfLoading'], $iconn)['PortName'];
$FinalDestination = getPortDetails($row['FinalDestination'], $iconn)['PortName'];
$CountryName = getCountryDetails($row['ConsigneeCountry'], $iconn)['CountryName'];

$header = 10;
$sub_header = 7;
$head = 11;

$pdf->SetFont($FONT_ARI, 'BU', $head);
// $pdf->Cell(0, 15, 'PRODUCTION INSTRUCTION', 0, 1, 'C');
$pdf->Cell(0, 15, 'CODE LIST ', 0, 1, 'C');
$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(160, 5, 'Date :', 0, 0, 'R');
$pdf->Cell(0, 5, $SGSRequestDate, 0, 1, 'R');

$pdf->Cell(40, 5, 'P.O NUMBER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['PoNo'], 0, 1, 'L');
$pdf->Cell(40, 5, 'BUYER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['ConsigneeName'], 0, 1, 'L');
if ($DestinationCountry != 26) {
	$pdf->Cell(40, 5, 'BRAND', 0, 0, 'L');
	$pdf->Cell(0, 5, ': ' . $Brand, 0, 1, 'L');
}
$pdf->Cell(40, 5, 'SHIPPER', 0, 0, 'L');
$pdf->MultiCell(0, 5, ': ' . $EXPORTOR . "\n" . $EXPORTOR_ADD, 0, 'L', 0, 1);

$pdf->Cell(40, 5, 'PROCESSOR', 0, 0, 'L');
$pdf->MultiCell(0, 5, ': ' . $PROCESSOR . "\n" . $PROCESSOR_ADD, 0, 'L', 0, 1);


$pdf->Cell(40, 5, 'APPROVAL NUMBER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $PROCESSOR_APP_NUM, 0, 1, 'L');

$pdf->Cell(0, 0.5, '', 0, 1, 'L');

$size = 100 / 11;

$content = '';
$content .= '

      	<table border="1" cellspacing="0" cellpadding="3">
				<tr>
           		<th  align="center"  width="' . ($size * 3) . '%"><font size="7">Variety Of The Product/Freezing</font></th>
                <th  align="center" width="' . ($size) . '%"><font size="7">Grade</font></th>
                <th  align="center" width="' . ($size) . '%"><font size="7">Packing</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">No Of Mcs</font></th>
				<!--<th  align="center" width="' . ($size) . '%"><font size="7">Deglazed Weight</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">Packing Count/Lb</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">No of Pieces</font></th>-->
				<th  align="center" width="' . ($size) . '%"><font size="7">Production Date</font></th>
				
				';
// if ($DestinationCountry != 26) {

$content .= '	<th  align="center" width="' . ($size) . '%"><font size="7">Best if Used By</font></th>';
// }

$content .= '					<th  align="center" width="' . ($size) . '%"><font size="7">UPC No`s</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">Item No`s</font></th>

</tr>';

while ($RowCodeData = $StmtCodeList->fetch_array()) {
	$Grades = $RowCodeData['Grades'];
	$SqlPackingCounts = "SELECT * FROM `QcPackingCounts` WHERE Grade='$Grades'";
	$StmtPackingCounts = $iconn->query($SqlPackingCounts);
	$RowPackingCounts = $StmtPackingCounts->fetch_array();
	$NoOfPieces = $RowCodeData['TotalSlabs'] * $RowPackingCounts['PackCount'];

	$Variety = $RowCodeData['QcDescription'] ? $RowCodeData['QcDescription'] : $RowCodeData['Variety'];
	$UPCCode = !empty($RowCodeData['UPCCode']) ? $RowCodeData['UPCCode'] : '--';
	if ($ItemNumberCount == 1) {
		$BreakupwiseItemNumber = '--';
	} else {
		$BreakupwiseItemNumber = !empty($RowCodeData['ItemNumber']) ? $RowCodeData['ItemNumber'] : '--';

	}
	// $BestBeforeDt = $RowCodeData['BestBeforeDt'] ? date('m-d-Y', strtotime($RowCodeData['BestBeforeDt'])) : '--';
	$BestBeforeDt = !empty($RowCodeData['BestBeforeDtText']) ? $RowCodeData['BestBeforeDtText'] :
		(!empty($RowCodeData['BestBeforeDt']) ? date('m-d-Y', strtotime($RowCodeData['BestBeforeDt'])) : '--');

	$content .= '<tr>
           		<th  align="center"  width="' . ($size * 3) . '%"><font size="7">' . $Variety . '</font></th>
                <th  align="center" width="' . ($size) . '%"><font size="7">' . $RowCodeData['Grades'] . '</font></th>
                <th  align="center" width="' . ($size) . '%"><font size="7">' . $RowCodeData['PackingStyle'] . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . $RowCodeData['McsGrade'] . '</font></th>
				<!--<th  align="center" width="' . ($size) . '%"><font size="7">' . round($RowCodeData['DeWgt'], 2) . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . $RowPackingCounts['PackCount'] . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . $NoOfPieces . '</font></th>-->
				<th  align="center" width="' . ($size) . '%"><font size="7">' . $RowCodeData['DayCode'] . '</font></th>';
	// if ($DestinationCountry != 26) {
	$content .= '	<th  align="center" width="' . ($size) . '%"><font size="7">' . $BestBeforeDt . '</font></th>';
	// }
	$content .= '<th  align="center" width="' . ($size) . '%"><font size="7">' . $UPCCode . '</font></th>
				<th  align="center" width="' . ($size) . '%"><font size="7">' . $BreakupwiseItemNumber . '</font></th>
	</tr>';
}


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


function getTotal($PoID, $iconn, $T_GRADES_ARY, $TYPE, $HEADERS_COUNT)
{
	global $size;
	// global $TblQcCodeList;

	if ($TYPE == '') {
		$sql_code_list = "SELECT sum(McsGrade)McsGrade,Grades FROM `QcCodeList` WHERE InvID='$PoID' group by DayCode,VarietyID,Grades;";
		// $sql_code_list = "SELECT McsGrade,Grades FROM `QcCodeList` WHERE InvID='$PoID'  ORDER BY `CLID` ASC";
	} else {
		$sql_code_list = "SELECT McsGrade,Grades FROM `QcCodeList` WHERE InvID='$PoID' AND SHORT_NAME='$TYPE' ORDER BY `CLID` ASC";
	}

	// echo $sql_code_list;
	// exit;
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


$pdf->SetFont($FONT_ARI, 'B', $header);
if ($ItemNumberCount == 1) {
	$pdf->Cell(49, 5, 'Item Number:' . $ItemNumber, 0, 1, 'L');
}
$pdf->Cell(49, 5, '', 0, 1, 'L');

$pdf->Cell(49, 5, $PROCESSOR, 0, 1, 'L');
$pdf->Cell(49, 15, '', 0, 1, 'L');
$pdf->SetFont($FONT_ARI, '', $header);

$pdf->Cell(49, 5, $VerifiedBy, 0, 0, 'L');

$pdf->SetX($pdf->GetPageWidth() - $rightMargin - $cellWidth); // Set position for right alignment
$pdf->Cell($cellWidth, 5, $ApprovedBy, 0, 1, 'R');

$pdf->Cell(49, 5, $Designation, 0, 0, 'L');



// Designation
$pdf->SetX($pdf->GetPageWidth() - $rightMargin - $cellWidth); // Set the same position
$pdf->Cell($cellWidth, 5, $DesignationOfApprovedPerson, 0, 1, 'R');

ob_end_clean();

if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $row['PoNo'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName . '.pdf', 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName;
	$pdf->Output($FileName . '.pdf', $FileType);

}
?>