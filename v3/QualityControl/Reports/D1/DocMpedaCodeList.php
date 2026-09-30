<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$IsHeader = 1;
$IsFooter = 1;
$QueryParms = $_GET['Q'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
if(!empty($_GET['sourceUrl']) && $cp ){
    $cp = str_replace(['PreProcessing', 'ColdStore', 'Sales'],'QualityControl', $cp);
}
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";
$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$TYPE = $_GET['TypeProduct'];
$FileType = $_GET['Type'];
// echo $_SESSION['ClientID'];
// EXIT;


$SqlCodeList = "SELECT *,QLT.LbuDesignation as DesignationOfVerifiedBY,QLT.LbuName as VerifiedBy,
QLTA.LbuDesignation as DesignationOfApprovedPerson,QLTA.LbuName as ApprovedBy FROM QcCodeList as CL
LEFT JOIN QcLabTechnologist as QLT on QLT.LUID=CL.PreparedByID
 LEFT JOIN QcLabTechnologist as QLTA on QLTA.LUID=CL.ApprovedByID 
WHERE InvID='$PoID' ORDER BY `CLID` ASC";


$StmtVerifiedBy = $iconn->query($SqlCodeList);
$rowVerifiedBy = $StmtVerifiedBy->fetch_array();
$VerifiedBy = $rowVerifiedBy['VerifiedBy'];
$Designation = $rowVerifiedBy['DesignationOfVerifiedBY'];
$DesignationOfApprovedPerson = $rowVerifiedBy['DesignationOfApprovedPerson'];
$ApprovedBy = $rowVerifiedBy['ApprovedBy'];

$SqlItemNumber = "SELECT GROUP_CONCAT(CASE WHEN ItemNumber != '' THEN ItemNumber END) AS ItemNumber FROM `QcCodeList` WHERE InvID='$PoID' ";
$StmtItemNumber = $iconn->query($SqlItemNumber);
$rowItemNumber = $StmtItemNumber->fetch_array();
$ItemNumber = $rowItemNumber['ItemNumber'];

$ItemNumberArray = array_filter(explode(',', $ItemNumber)); // Split the string into an array

$ItemNumberCount = count($ItemNumberArray);




$stmt_code = $iconn->query($SqlCodeList);
$varity_ids = array();

while ($row_Code = $stmt_code->fetch_array()) {

	$FULL_NAME = $row_Code['VarietyID'];
	// $VARIFIED_BY = $row_Code['VerifiedBy'];
	$GRADES = $row_Code['Grades'];

	if (in_array($FULL_NAME, $varity_ids)) {
	} else {
		$FULL_NAME;
		$varity_ids[] = $FULL_NAME;
	}
}



$sql = "SELECT *,PD.ExporterID  FROM ScrmPoData  as PD
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID 
left join AdminExporterDetails as AED on AED.ExpID=PD.ProcessorID
WHERE `PoID`='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();
$ProcessorID = $row['ProcessorID'];
$ProcessorName = $row['ExpName'];
$ExpApprovalNo = $row['ExpApprovalNo'];
if (!empty($ExpApprovalNo) && $ExpApprovalNo == 990) {
	$ProcessorName = str_ireplace("unit 3", $ExpApprovalNo, $ProcessorName);
}


$T_GRADES_ARY = array();
$FARM_GRADE = $row_Code['GRADES'];


$from_title = date('M d, Y');
$to_title = date('M d, Y');

$PO_TIME_STAMP = strtotime($row['PO_DATE']);
$PO_DATE = date("d-m-Y", $PO_TIME_STAMP);

$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
// $pdf->SetTitle('MPEDACodeList: ' . $row['PO_NUM'] . ' - ' . $PO_DATE);
$pdf->SetTitle('InternalCodeList');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetHeaderMargin(145);
if( $PoID == 6004 ){
	$pdf->SetMargins(PDF_MARGIN_LEFT, 25, PDF_MARGIN_RIGHT, 45);
}else{
	$pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT, 45);
}
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);


$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage('L');
$content = '';

$header = 11;
$sub_header = 10;

$pdf->SetFont($FONT_ARI, 'B', 14);

$pdf->Cell(0, 5, $ProcessorName, 0, 1, 'C');
$pdf->SetFont($FONT_ARI, 'BU', 14);
$pdf->Cell(0, 14, 'CODELIST', 0, 1, 'C');

$pdf->SetFont($FONT_ARI, '', $header);

$size = count($T_GRADES_ARY) + 5;
$size = 100 / $size;
// $size = 100 / 9;

//$t_width = getRowWidth($T_GRADES_ARY);
$t_width = $t_width + (4 * $size + 10);
$size = 10;
// $size = 11;


$content = getPoInformation($row, $iconn);
$pdf->writeHTML($content, false);
$pdf->Cell(0, 5, '', 0, 1, 'L');
$header_size = 9;

$content = '';
$content .= '<table border="1" cellspacing="0" cellpadding="0">  
				<tr align="center">  
           		<th  valign="ceter"  width="' . ($size + 10) . '%"><font size="' . $header_size . '"><br><b>PRODUCTION CODE / DATE</b></font></th>
                <th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>PACKING</b></font></th>
                <th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>VARIETY</b></font></th>
				<th  align="center" width="' . ($size + 2) . '%"><font size="' . $header_size . '"><br><b>TRACEABILITY</b></font></th> 
				<th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>GRADE</b></font></th>
				<th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>SERIAL NUMBER</b></font></th>
				<th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>BUYER CODE / LOT CODE</b></font></th>
				<th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>BEST BY</b></font></th>';
$content .= '<th  align="center" width="' . ($size + 0) . '%"><font size="' . $header_size . '"><br><b>NO OF MASTER CARTONS</b></font></th>
				</tr>';

//print_r($varity_ids);exit;
// foreach ($varity_ids as $value) {

// 	$nameee = getVarityName($value, $iconn);
// 	$size = count($T_GRADES_ARY) + 2;
// 	$size = 100 / $size;
// 	generateRow($value, $iconn, $T_GRADES_ARY, $nameee, $t_width);
// 	$content .= generateRow($value, $iconn, $T_GRADES_ARY, $nameee, $t_width);
// }




// Get all unique Day Codes for this PO
$sqlDayCodes = "SELECT DISTINCT DayCode FROM QcCodeList WHERE InvID='$PoID' ORDER BY DayCode ASC";
$stmtDayCodes = $iconn->query($sqlDayCodes);

while ($rowDC = $stmtDayCodes->fetch_assoc()) {
	$DayCode = $rowDC['DayCode'];
	$content .= generateRowByDayCode($DayCode, $iconn);
}

$size = count($T_GRADES_ARY) + 5;
// $size = 100 / $size;
$size = 10;
$t_width = (3 * $size + 10);
$t_width = 92;

$content .= '<tr> <th  align="RIGHT"  width="' . $t_width . '%"><font size="9"><B> TOTAL </B></font></th>';
$content .= getTotals($PoID, $iconn, $T_GRADES_ARY, 10);
$content .= '</tr>';
$content .= '</table>';
$pdf->writeHTML($content, false);

$pdf->Ln();

$COUNT = 1;

$sqlFactorySelect = "SELECT FactoryID,LotCode,CerificationName FROM ScrmPoData as SPD 
left join  ScrmOrderCertification as SOC on  SPD.Certification=SOC.CerificationID
left join QcNewAddonFieldsData as QNAF on QNAF.PoID=SPD.PoID
WHERE SPD.PoID = '$PoID'";

$stmtFactorySelect = $iconn->query($sqlFactorySelect);
$dataFactoryRow = $stmtFactorySelect->fetch_assoc();
$FactoryID = $dataFactoryRow['FactoryID'];
$LotCode = $dataFactoryRow['LotCode'];
$CerificationName = $dataFactoryRow['CerificationName'];

$pdf->SetFont('helvetica', 'B', 10);
if ($FactoryID != '') {
	$pdf->Cell(90, 5, 'FACTORY ID : ' . $FactoryID, 0, 1, 'L');
}
if ($LotCode != '') {
	$pdf->Cell(90, 5, 'LOT CODE : ' . $LotCode, 0, 1, 'L');
}
if ($CerificationName != '') {
	$pdf->Cell(90, 5, 'CERTIFICATION : ' . $CerificationName, 0, 1, 'L');
}
if ($ItemNumberCount == 1) {
	$pdf->Cell(90, 5, 'ITEM NUMBER  : ' . $ItemNumber, 0, 1, 'L');
}
$pdf->Cell(10, 5, '', 0, 1, '');
$rightMargin = 10; // Distance from the right edge of the page
$cellWidth = 150;



// $pdf->Cell(49, 15, '', 0, 1, 'L');
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
	$FileName = $Fname . $FileName . '.pdf';
	$pdf->Output($FileName, $FileType);
}




// function generateRow($PoGradeID)
// {
// 	global $iconn;
// 	$contents = '';

// 	$sql_code_list = "SELECT SPVL.Species,SPVL.ShortName,SPVL.TypeProduct,CL.DayCode,CL.Grades,CL.McsTotal,PL.PackingStyle,
// 	TL.Tracebility,SerialNumber,BuyerCode,BestBeforeDt,BestBeforeDtText FROM QcCodeList as CL 
// 	left join ScrmPoGradeData as SPGD on SPGD.ID=CL.PoGradeID 
// 	left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
// 	left join AdminPackingStyles as PL on PL.PCKID=CL.PackingStyleID 
// 	left join QcTracebilityList as TL on TL.TraceID=CL.TracebilityID 
// 	WHERE PoGradeID='$PoGradeID' ORDER BY `CLID` ASC";
// 	// WHERE PoGradeID='$PoGradeID' ORDER BY `DayCode` ASC";
// 	$stmt_code_list = $iconn->query($sql_code_list);

// 	while ($row = $stmt_code_list->fetch_assoc()) {

// 		// $size = 100 / 9;
// 		// $size = 11;
// 		$size = 10;
// 		$header_size = 9;
// 		$dayCode = strtoupper(trim($row['DayCode']));
// 		$date = getDayCodeDate($dayCode);
// 		$BestBeforeDt = !empty($row['BestBeforeDtText']) ? $row['BestBeforeDtText'] : (!empty($row['BestBeforeDt']) ? date('m-d-Y', strtotime($row['BestBeforeDt'])) : '--');
// 		$TypeProduct = $row['TypeProduct'];
// 		$ShortName = $row['ShortName'];

// 		$row['ShortName'] = getProductTypeCode($TypeProduct, $ShortName);
// 		$SerialNumber = !empty($row['SerialNumber']) ? $row['SerialNumber'] : '--';
// 		$BuyerCode = !empty($row['BuyerCode']) ? $row['BuyerCode'] : '--';


// 		$contents .= '
// 				<tr>
				
// 				<td align="ceter"  width="' . ($size + 10) . '%"><font size="' . $header_size . '"><b>' . $row['DayCode'] . ' (' . $date . ')</b></font></td>
// 				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $row['PackingStyle'] . '  </b></font></td>
// 				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $row['Species'] . "-" . $row['ShortName'] . '</b></font></td>
// 				<td align="center"  width="' . ($size + 2) . '%" ><font size="' . $header_size . '"><b>' . $row['Tracebility'] . '</b></font></td>
// 				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $row['Grades'] . '</b></font></td>
// 				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $SerialNumber . '</b></font></td>
// 				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $BuyerCode . '</b></font></td>
// 				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $BestBeforeDt . '</b></font></td>
// 				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $row['McsTotal'] . '</b></font></td>
// 			';

// 		$contents .= '</tr>';
// 	}
// 	return $contents;
// }


function generateRowByDayCode($DayCode, $iconn) {
	global $PoID; // Make sure $PoID is available
	$contents = '';

	$sql = "SELECT SPVL.ShortName, SPVL.TypeProduct, CL.DayCode, CL.Grades, CL.McsTotal, PL.PackingStyle,
	TL.Tracebility, SerialNumber, BuyerCode, BestBeforeDt, BestBeforeDtText
	FROM QcCodeList AS CL
	LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.ID = CL.PoGradeID
	LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = SPGD.VarietyID
	LEFT JOIN AdminPackingStyles AS PL ON PL.PCKID = CL.PackingStyleID
	LEFT JOIN QcTracebilityList AS TL ON TL.TraceID = CL.TracebilityID
	WHERE CL.DayCode = '$DayCode' AND CL.InvID = '$PoID'
	ORDER BY CL.CLID ASC";

	$stmt = $iconn->query($sql);

	while($row = $stmt->fetch_assoc()){
		$size = 10;
		$header_size = 9;
		$dayCode = strtoupper(trim($row['DayCode']));
		$date = getDayCodeDate($dayCode);
		$BestBeforeDt = !empty($row['BestBeforeDtText']) ? $row['BestBeforeDtText'] : (!empty($row['BestBeforeDt']) ? date('m-d-Y', strtotime($row['BestBeforeDt'])) : '--');
		$ShortName = getProductTypeCode($row['TypeProduct'], $row['ShortName']);
		$SerialNumber = !empty($row['SerialNumber']) ? $row['SerialNumber'] : '--';
		$BuyerCode = !empty($row['BuyerCode']) ? $row['BuyerCode'] : '--';

		$contents .= '
			<tr>
				<td align="ceter" width="'.($size+10).'%"><font size="'.$header_size.'"><b>'.$row['DayCode'].' ('.$date.')</b></font></td>
				<td align="center" width="'.$size.'%"><font size="'.$header_size.'"><b>'.$row['PackingStyle'].'</b></font></td>
				<td align="center" width="'.$size.'%"><font size="'.$header_size.'"><b>'.$ShortName.'</b></font></td>
				<td align="center" width="'.($size+2).'%"><font size="'.$header_size.'"><b>'.$row['Tracebility'].'</b></font></td>
				<td align="center" width="'.$size.'%"><font size="'.$header_size.'"><b>'.$row['Grades'].'</b></font></td>
				<td align="center" width="'.$size.'%"><font size="'.$header_size.'"><b>'.$SerialNumber.'</b></font></td>
				<td align="center" width="'.$size.'%"><font size="'.$header_size.'"><b>'.$BuyerCode.'</b></font></td>
				<td align="center" width="'.$size.'%"><font size="'.$header_size.'"><b>'.$BestBeforeDt.'</b></font></td>
				<td align="center" width="'.$size.'%"><font size="'.$header_size.'"><b>'.$row['McsTotal'].'</b></font></td>
			</tr>';
	}

	return $contents;
}
