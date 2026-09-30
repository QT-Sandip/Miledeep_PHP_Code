<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

session_start();
$IsHeader = 1;
$IsFooter = 1;
$QueryParms = $_GET['Q'];



include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$PoID = $_GET['PoID'];

include "PrintHeaderInc.php";

// echo $WaterMark;
// exit;
if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$TYPE = $_GET['RID'];
$FileType = $_GET['Type'];

if ($TYPE == '') {


	$SqlCodeList = "SELECT  ID as PoGradeID, SPGD.VarietyID,SPVL.ShortName,TypeProduct,BAP 
  FROM `ScrmPoGradeData` as SPGD
 left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
  WHERE InvId='$PoID' GROUP by SPVL.ShortName,SPVL.TypeProduct ORDER BY  SPGD.ID ASC"; //new query uma 24-04-2024
} else {
	$SqlCodeList = "SELECT ID as PoGradeID, SPGD.VarietyID,SPVL.ShortName,TypeProduct 
  FROM `ScrmPoGradeData` as SPGD 
  left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
  WHERE InvId='$PoID' and SPVL.TypeProduct='$TYPE' GROUP by SPVL.ShortName ORDER BY SPGD.ID ASC";
}

// echo $SqlCodeList;
// exit;

$stmt_code = $iconn->query($SqlCodeList);
$varity_ids = array();

while ($row = $stmt_code->fetch_array()) {
	$Item['VarietyID'] = $row['VarietyID'];
	$Item['PoGradeID'] = $row['PoGradeID'];
	$Item['ShortName'] = $row['ShortName'];
	$Item['TypeProduct'] = $row['TypeProduct']; // uma 24-04-2024
	$varity_ids[] = $Item;
}



$SqlVerifiedBy = "SELECT QLT.LbuDesignation,QLT.LbuName,CL.Brand,PrintStatus,
QLTA.LbuDesignation as DesignationOfApprovedPerson,QLTA.LbuName as ApprovedBy FROM `QcCodeList` CL
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

$StmtBrand = $iconn->query($SqlVerifiedBy);
while ($rowBrand = $StmtBrand->fetch_array()) {
	//ROBO
	$Brand = trim($rowBrand['Brand']);
	$PrintStatus = trim($rowBrand['PrintStatus']);


	// if ($Brand != '' && ($PrintStatus != 0)) {
	if ($Brand != '') {
		$AryBrand[] = $Brand;
	}
	//$AryBrand[] = trim($rowBrand['Brand']);

}

$Brand = implode(",", array_unique($AryBrand));

if ($Brand == '') {
	$Brand = '--';
}

$SqlPoData = "SELECT *,(SELECT TRIM(LEADING ',' FROM COALESCE(GROUP_CONCAT(DISTINCT BAP), '')) 
FROM `ScrmPoGradeData` WHERE InvId = $PoID) AS BAP FROM `ScrmPoData`
 join ScrmConsigneeDetails on ScrmPoData.ConsigneeID=ScrmConsigneeDetails.ConID
 WHERE ScrmPoData.PoID='$PoID'";

$stmtPoData = $iconn->query($SqlPoData);
$row = $stmtPoData->fetch_array();

// $IntLotNo = "CODELIST :" . $row['LotNo'];
$IntLotNo = "CODELIST ";

$DestinationCountry = $row['DestinationCountry'];

$PortOfLoading = getPortDetails($row['PortOfLoading'], $iconn)['PortName'];
$FinalDestination = getPortDetails($row['FinalDestination'], $iconn)['PortName'];

$SqlCountryList = "SELECT * FROM ScrmCountriesList where id='$DestinationCountry'";
$StmtCountryList = $iconn->query($SqlCountryList);
$RowCountry = $StmtCountryList->fetch_array();
$DestinationCountryName = $RowCountry['name'];

$ProcessorID = $row['ProcessorID'];
if ($DestinationCountry == 26) {
	$HEADERS_COUNT = 4;
} else {
	$HEADERS_COUNT = 5;
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


$header = 11;
$sub_header = 10;
$head = 13;

$pdf->SetFont($FONT_ARI, 'B', $head);

$pdf->Cell(0, 15, $IntLotNo, 0, 1, 'C');
$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(40, 5, 'CONSIGNEE', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['ConsigneeName'], 0, 1, 'L');
if ($DestinationCountry != 26) {
	$pdf->Cell(40, 5, 'BRAND', 0, 0, 'L');
	$pdf->Cell(0, 5, ': ' . $Brand, 0, 1, 'L');
}
$pdf->Cell(40, 5, 'APPROVAL NUMBER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $PROCESSOR_APP_NUM, 0, 1, 'L');
$pdf->Cell(40, 5, 'P.O NUMBER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['PoNo'], 0, 1, 'L');

$pdf->Cell(40, 5, 'DESTINATION', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . strtoupper($FinalDestination) . ',' . $DestinationCountryName, 0, 1, 'L');
$pdf->Cell(0, 0.5, '', 0, 1, 'L');
//$pdf->SetAutoPageBreak(TRUE, 10);
//$pdf->AddPage();

$size = getRowWidth($T_GRADES_ARY, $HEADERS_COUNT);

$content = '';
$content .= '

      	<table border="1" cellspacing="0" cellpadding="3">
				<tr>
           		<th  align="center"  width="' . $size . '%"><font size="7"> DAY CODE </font></th>
                <th  align="center" width="' . ($size) . '%"><font size="7">VARIETY TYPE OF PACKING</font></th>';
if ($DestinationCountry != 26) {
	$content .= '				<th  align="center" width="' . ($size) . '%"><font size="7">BRAND</font></th>';
}
$content .= '<th  align="center" width="' . ($size) . '%"><font size="7">TRACEABILITY</font></th>';

$content .= generateTableHead($T_GRADES_ARY, $HEADERS_COUNT);
$content .= '<th  align="center" width="' . $size . '%"><font size="7"> TOTAL MCS </font></th>
				</tr>';


// foreach ($varity_ids as $VARIETY_ID) {


// 	$VarityName = getVarityName($VARIETY_ID['PoGradeID'], $iconn);
// 	// $content .= generateRow($VARIETY_ID['VarietyID'], $iconn, $T_GRADES_ARY, $VarityName, 100, $TYPE, $HEADERS_COUNT,$VARIETY_ID['ShortName']);
// 	$content .= generateRow($VARIETY_ID['VarietyID'], $iconn, $T_GRADES_ARY, $VarityName, 100, $TYPE, $HEADERS_COUNT, $VARIETY_ID['ShortName'], $VARIETY_ID['TypeProduct']); // uma 24-04-2024

// }




$sqlAllRows = "
SELECT 
    CL.*, 
    SPGD.VarietyID,
    SPVL.ShortName,
    SPVL.TypeProduct,
    APS.PackingStyle,
    TL.Tracebility,
    QLTA.LbuName AS ApprovedBy,
    QLTA.LbuDesignation AS DesignationOfApprovedPerson,
    QLT.LbuName AS VerifiedBy,
    QLT.LbuDesignation AS DesignationOfVerifiedBY,
    GROUP_CONCAT(CL.Grades) AS Grades_New,
    GROUP_CONCAT(CL.McsGrade) AS McsGrade_New
FROM QcCodeList AS CL
LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.ID = CL.PoGradeID
LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = SPGD.VarietyID
LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CL.PackingStyleID
LEFT JOIN QcTracebilityList AS TL ON TL.TraceID = CL.TracebilityID
LEFT JOIN QcLabTechnologist AS QLT ON QLT.LUID = CL.PreparedByID
LEFT JOIN QcLabTechnologist AS QLTA ON QLTA.LUID = CL.ApprovedByID
WHERE CL.InvId='$PoID'
GROUP BY CL.CLID
ORDER BY CAST(SUBSTRING(CL.DayCode,3) AS UNSIGNED) ASC, CL.CLID ASC
";

$stmtAllRows = $iconn->query($sqlAllRows);

while ($row = $stmtAllRows->fetch_assoc()) {
    $content .= generateRow($row, $T_GRADES_ARY);
}


//echo $VarityName; exit;

$content .= '<tr>
			<th  align="center"  width="' . ($size * ($HEADERS_COUNT - 1)) . '%"><font size="7"> TOTAL </font></th>
			';
$content .= getGradeTotal($PoID, $iconn, $T_GRADES_ARY, $TYPE, $HEADERS_COUNT);
$content .= '</tr>';
$content .= '</table>';
$pdf->writeHTML($content, false);


//$pdf->AddPage();


$pdf->Ln();

$COUNT = 1;








// function generateRow($VARIETY_ID, $iconn, $T_GRADES_ARY, $nameee, $t_width, $TYPE, $HEADERS_COUNT,$ShortName)
// function generateRow($VARIETY_ID, $iconn, $T_GRADES_ARY, $nameee, $t_width, $TYPE, $HEADERS_COUNT, $ShortName, $TypeProduct)
// {

// 	global $DestinationCountry, $PoID;


// 	if ($TYPE == '') {

// 		// $Qry=" ";
// 		$Qry = " and VL.TypeProduct='$TypeProduct' ";
// 	} else {
// 		$Qry = "and VL.TypeProduct='$TYPE' ";
// 	}


// 	$SqlCl = "SELECT *,CL.Brand AS BrandName,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as 
// 	McsGrade_New FROM QcCodeList as CL 
// left join  ScrmPoGradeData as SPGD on SPGD.ID=CL.VarietyID 
// left join  ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
// LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
// left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID 
// left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID 
// WHERE  CL.InvId='$PoID'   and VL.ShortName='$ShortName' $Qry 
// GROUP by TypeProduct,DayCode,CL.PackingStyleID,CL.Brand,TracebilityID ORDER BY `CLID` ASC";  // //new query uma 24-04-2024 

// 	// echo  $SqlCl; exit;
// 	echo '<br>';
// 	$contents = '';


// 	$StmtCl = $iconn->query($SqlCl);

// 	$contents .= '<tr>
// 				 <!-- <td align="left"  width="' . $t_width . '%" >-->
// 				  <td align="left"  width="100%" >
// 				  <font size="8"><b> VARIETY :: </b>' . $nameee . '</font></td>
// 				  </tr>';


// 	while ($RowCl = $StmtCl->fetch_assoc()) {

// 		// echo json_encode($RowCl, JSON_PRETTY_PRINT);
// 		// exit;
// 		$PrintStatus = trim($RowCl['PrintStatus']);
// 		$Brand = trim($RowCl['BrandName']);

// 		// if ($Brand != '' && $PrintStatus != 0) {
// 		if ($Brand != '') {
// 			$Brand = $Brand;
// 		} else {

// 			$Brand = '--';
// 		}
// 		// $McsGrade = $RowCl['McsGrade'];
// 		// $McsGrade = $RowCl['McsGrade_New'];
// 		// $McsGrade = explode(',', $McsGrade);
// 		$McsGrade = explode(',', $RowCl['McsGrade_New']);
// 		// $TotalMcsGrade += $McsGrade; 

// 		// $ARY_GRADES = $RowCl['Grades'];
// 		// $ARY_GRADES = $RowCl['Grades_New'];
// 		// $ARY_GRADES = explode(',', $ARY_GRADES);
// 		$ARY_GRADES = explode(',', $RowCl['Grades_New']);

// 		global $size;

// 		$TypeProduct = $RowCl['TypeProduct'];
// 		$ShortName = $RowCl['ShortName'];

// 		$RowCl['ShortName'] = getProductTypeCode($TypeProduct, $ShortName);
// 		$ExplodeTraceability = explode('-', $RowCl['Tracebility']);
// 		// $Traceability = $ExplodeTraceability[1] . '-' . $ExplodeTraceability[2];
// 		$Traceability = $RowCl['Tracebility'];
// 		$contents .= '
// 				<tr>
// 				<td align="center"  width="' . $size . '%"><font size="8">' . $RowCl['DayCode'] . '</font></td>
// 				<td align="center"  width="' . ($size) . '%" ><font size="8">' . $RowCl['Species'] . "-" . $RowCl['ShortName'] . '<br> ' .
// 			$RowCl['PackingStyle'] . $GLAZE . '  </font></td>';
// 		if ($DestinationCountry != 26) {
// 			$contents .= '
// 					<td align="center"  width="' . ($size) . '%" ><font size="7">' . $Brand . '</font></td>';
// 		}
// 		$contents .= '	<td align="center"  width="' . ($size) . '%" ><font size="7">' . $Traceability . '</font></td>';

// 		var_dump($ARY_GRADES);
// 		$sumMcsGrade = 0;
// 		foreach ($T_GRADES_ARY as $ItemGrade) {
// 			//	echo $ItemGrade;
// 			if (in_array($ItemGrade, $ARY_GRADES, true)) {
// 				$Index = array_search($ItemGrade, $ARY_GRADES);
// 				$MCS = $McsGrade[$Index];
// 				$contents .= '<td align="center" width="' . $size . '%"><font size="8">' . $MCS . '</font></td>';
// 				$sumMcsGrade += (int) $MCS;
// 			} else {
// 				$contents .= '<td align="center" width="' . $size . '%"><font size="8"> *** </font></td>';
// 			}
// 		}
// 		// $sumMcsGrade = array_sum($McsGrade);
// 		$contents .= '<td align="center" width="' . $size . '%" ><font size="8">' . $sumMcsGrade . '</font></td></tr>';
// 	}
// 	return $contents;
// }




function generateRow($RowCl, $T_GRADES_ARY)
{
    global $DestinationCountry, $size, $GLAZE, $Brand;

    $contents = '';

    // BRAND
    /*$Brand = trim($RowCl['BrandName']);
    if ($Brand == '') {
        $Brand = '--';
    }*/

    // GRADES & MCS
    $ARY_GRADES = explode(',', $RowCl['Grades_New']);
    $McsGrade   = explode(',', $RowCl['McsGrade_New']);

    // PRODUCT NAME (same logic)
    $TypeProduct = $RowCl['TypeProduct'];
    $ShortName   = $RowCl['ShortName'];
    $RowCl['ShortName'] = getProductTypeCode($TypeProduct, $ShortName);

    // TRACEABILITY
    $Traceability = $RowCl['Tracebility'];

    // START ROW (same UI)
    $contents .= '
        <tr>
            <td align="center" width="' . $size . '%">
                <font size="8">' . $RowCl['DayCode'] . '</font>
            </td>

            <td align="center" width="' . $size . '%">
                <font size="8">' . $RowCl['ShortName'] . '<br>' .
                    $RowCl['PackingStyle'] . $GLAZE . '
                </font>
            </td>';

    // BRAND COLUMN
    if ($DestinationCountry != 26) {
        $contents .= '
            <td align="center" width="' . $size . '%">
                <font size="7">' . $Brand . '</font>
            </td>';
    }

    // TRACEABILITY COLUMN
    $contents .= '
            <td align="center" width="' . $size . '%">
                <font size="7">' . $Traceability . '</font>
            </td>';

    // GRADES LOOP (unchanged logic)
    $sumMcsGrade = 0;
    foreach ($T_GRADES_ARY as $ItemGrade) {

        if (in_array($ItemGrade, $ARY_GRADES, true)) {
            $Index = array_search($ItemGrade, $ARY_GRADES);
            $MCS = (int)$McsGrade[$Index];

            $contents .= '
                <td align="center" width="' . $size . '%">
                    <font size="8">' . $MCS . '</font>
                </td>';

            $sumMcsGrade += $MCS;

        } else {
            $contents .= '
                <td align="center" width="' . $size . '%">
                    <font size="8"> *** </font>
                </td>';
        }
    }

    // TOTAL MCS COLUMN
    $contents .= '
            <td align="center" width="' . $size . '%">
                <font size="8">' . $sumMcsGrade . '</font>
            </td>
        </tr>';

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
		$contents .= '<td align="center"  width="' . $size . '%"><font size="8">' . $MCS . '</font></td>';
	}
	$contents .= '<td align="center"  width="' . $size . '%"><font size="8">' . array_sum($AryTMcs) . '</font></td>';
	return $contents;
}
$pdf->Cell(1, 15, '', 0, 0, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(49, 5, $row['BAP'], 0, 1, 'L');

$rightMargin = 10; // Distance from the right edge of the page
// $LeftMargin = 0; // Distance from the right edge of the page
$cellWidth = 150;

$pdf->SetFont($FONT_ARI, '', $header);

$pdf->SetX($pdf->GetPageWidth() - $rightMargin - $cellWidth); // Set the same position
$pdf->Cell($cellWidth, 5, 'Verified by:', 0, 1, 'R');
$pdf->Cell(20, 10, '', 0, 1, 'L');

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
	$FileName = $ReportName . '_' . $row['PoNo'] . '.pdf';
	$FileName = getFormatedFileName($FileName);

	$pdf->Output($FileName, 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName . '.pdf';


	$pdf->Output($FileName, $FileType);
}
