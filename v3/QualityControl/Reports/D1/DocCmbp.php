<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";

$ID = $_GET['RID'];
$INVOICE_ID = $_GET['PoID'];

$sql = "SELECT * FROM QcMBReport  WHERE `ID`='$ID'";
$stmt_BUYER_MB_REPORT = $iconn->query($sql);
$RowReport = $stmt_BUYER_MB_REPORT->fetch_array();

$TestedBy = $RowReport['TestedBy'];
$VerifiedBy = $RowReport['VerifiedBy'];

$TYPE = $RowReport['TypeProduct'];



$SQL_INVOICE_DATA = "SELECT * FROM ScrmPoData POD
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join QcEiaTestResultHeaders as QET on QET.CountryID=POD.DestinationCountry 
where POID='$INVOICE_ID'";
$STMT_INVOICE_DATA = $iconn->query($SQL_INVOICE_DATA);
$INVOICE_DATA = $STMT_INVOICE_DATA->fetch_array();
$Country = $INVOICE_DATA['Country'];
$BuyerName = $INVOICE_DATA['ConsigneeName'];
$C_NAME = getCountryName($Country);

$CMBPMethod = getLabMethodName($C_NAME, $iconn);
$PoID = $RowReport['InvID'];

$SqlOtherData = "SELECT SPGD.Brand,VL.ShortName,Variety,QcDescription,McsTotal,DayCode,
VerifiedBy,PL.PackingStyle,PrintStatus FROM  `QcCodeList` AS CL 
left join ScrmPoGradeData as SPGD on SPGD.ID=CL.PoGradeID
left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left JOIN ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
WHERE CL.InvID='$PoID' ";

$StmtOtherData = $iconn->query($SqlOtherData);

while ($RowOtherData = $StmtOtherData->fetch_array()) {
	$Brand = trim($RowOtherData['Brand']);
	$PrintStatus = trim($RowOtherData['PrintStatus']);

	// if ($Brand != '' && $PrintStatus != 0) {
	if ($Brand != '') {
		$AryBrand[] = $Brand;
	}
	$AryMcsTotal[] = $RowOtherData['McsTotal'];
	$AryDayCode[] = $RowOtherData['DayCode'];
	$AryVerifiedBy[] = $RowOtherData['VerifiedBy'];
	$AryPackStyle[] = $RowOtherData['PackingStyle'];
	$AryShortName[] = getProductTypeCode($TYPE, $RowOtherData['ShortName']);
	$AryFullName[] = $RowOtherData['QcDescription'] ? $RowOtherData['QcDescription'] : $RowOtherData['Variety'];
}

$Brand = implode(",", array_unique($AryBrand));
$McsTotal = array_sum($AryMcsTotal);
$DayCodes = implode(",", array_unique($AryDayCode));
$PackStyle = implode(",", array_unique($AryPackStyle));
$ShortName = implode(",", array_unique($AryShortName));
$FullName = implode(",", array_unique($AryShortName));


if ($Brand == '') {
	$Brand = '--';
}

$sqlMaxCompletionDate = "SELECT max(STR_TO_DATE(DateOfIssuance, '%d-%m-%Y')) as CompletionDate FROM QcMBResult WHERE `MbReportID`='$ID' order by ID ASC ";
$stmtMaxCompletionDate = $iconn->query($sqlMaxCompletionDate);
$RowMaxCompletionDate = $stmtMaxCompletionDate->fetch_array();
$CompletionDate = date('d-m-Y', strtotime($RowMaxCompletionDate['CompletionDate']));

$HEIGHT = 5;


$header = 9;
$sub_header = 8;
$text = 6;

$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->SetFont('', 'BU', 12);
$pdf->Cell(0, 0, 'IN HOUSE LABORATORY MICROBIOLOGY TEST REPORT', 0, 1, 'C');


$pdf->SetFont('', '', 10);
$pdf->Cell(130, $HEIGHT, ' ', 0, 0);
$pdf->Cell(0, $HEIGHT, 'Date:' . $CompletionDate, 0, 1, 'R');
// $pdf->SetFont($FONT_ARI, 'BU', $header);
// $pdf->Cell(180, $HEIGHT, 'PRODUCT DESCRIPTION : ', 0, 0, 'L');


// $pdf->SetFont($FONT_ARI, 'B', $header);
// $pdf->writeHTML($FullName);

// $pdf->SetFont($FONT_ARI, 'BU', $header);

// $pdf->Cell(30, $HEIGHT, 'BUYER : ', 0, 0, 'L');
// $pdf->SetFont($FONT_ARI, 'B', $header);

// $pdf->Cell(0, $HEIGHT, '' . $BuyerName, 0, 1, 'L');
// $pdf->SetFont($FONT_ARI, 'BU', $header);

// $pdf->Cell(30, $HEIGHT, 'BRAND : ', 0, 0, 'L');
// $pdf->SetFont($FONT_ARI, 'B', $header);

// $pdf->Cell(0, $HEIGHT, '' . $Brand, 0, 1, 'L');

// $pdf->SetFont('', '', 12);
// $size = 10;

if ($TYPE == 'Raw') {
	$content .= getRawTable();
} else {
	// $content .= getRawTable();
	$content .= getCookedTable();
}

$content .= '</table>';

$pdf->writeHTML(getHeaderTable(), false);
$pdf->SetFont('', '', 10);
$pdf->SetFont('', 'BU', 12);
$pdf->SetMargins(15, 29, 16, true);
$pdf->Cell(180, $HEIGHT, 'TEST RESULT ' . $TYPE, 0, 1, 'L');
$pdf->SetFont('', '', 12);


$pdf->writeHTML($content, false);


$HEIGHT = 5;

$pdf->SetFont('', '', 9);
$pdf->Cell(130, $HEIGHT, ' Note: The above results are below the limits,so the above Product Export Worthy', 0, 1);

$pdf->SetFont($FONT_ARI, 'B', $sub_header);
$pdf->Cell(90, 5, 'Tested By :' . $TestedBy, 1, 0, 'L', 0);
$pdf->Cell(90, 5, 'Verified By :' . $VerifiedBy, 1, 1, 'L', 0);  // cell with left and right borders

$pdf->Cell(90, 10, 'Signature', 1, 0, 'L', 0);
$pdf->Cell(90, 10, 'Signature', 1, 1, 'L', 0);  // cell with left and right borders

ob_end_clean();
$pdf->Ln();
$pdf->Output('MicrobiologyReport.pdf', 'I');

function getHeaderTable()
{
	global $ID, $McsTotal, $INVOICE_DATA, $DayCodes, $PackStyle, $FullName, $BuyerName, $Brand;
	$size = 9;
	$content = '<table border="1" cellspacing="0"  cellpadding="3">
	<tr  >
	 <th width="30%" align="left"><font size="' . $size . '">P.O NO </font></th>
	 <th width="70%"  align="left"><font size="' . $size . '">' . $INVOICE_DATA['PoNo'] . ' </font></th>
	 </tr>
	 <tr>
	 <th align="left"><font size="' . $size . '">PRODUCTION CODES</font></th>
	 <th align="left"><font size="' . $size . '">' . $DayCodes . '</font></th>
	 </tr><tr  >
	 <th align="left"><font size="' . $size . '">TYPE OF PACKING</font></th>
	 <th align="left"><font size="' . $size . '">' . $PackStyle . '</font></th>
	 </tr><tr  >
	 <th align="left"><font size="' . $size . '">TOTAL MASTER CARTONS</font></th>
	 <th align="left"><font size="' . $size . '">' . $McsTotal . ' MCS</font></th>
	 </tr>
     <tr>
	 <th align="left"><font size="' . $size . '">PRODUCT DESCRIPTION</font></th>
	 <th align="left"><font size="' . $size . '">' . $FullName . ' </font></th>
	 </tr>	 
	 <tr>
	 <th align="left"><font size="' . $size . '">BUYER</font></th>
	 <th align="left"><font size="' . $size . '">' . $BuyerName . ' </font></th>
	 </tr><tr>
    <th align="left"><font size="' . $size . '">BRAND</font></th>
	 <th align="left"><font size="' . $size . '">' . $Brand . ' </font></th>
	 </tr>



 </table>';

	return $content;
}


// function getRawTable()
// {
// 	global $iconn, $ID, $C_NAME, $INVOICE_ID;
// 	$size = 8;
// 	$sql = "SELECT GROUP_CONCAT(QMR.CLID) CLID,GROUP_CONCAT(distinct DateOfAnalysis) DateOfAnalysis,
// 	GROUP_CONCAT(distinct DateOfIssuance) DateOfIssuance ,Tpc,Listeria,VarietyID,DayCode FROM QcMBResult  as QMR 
// 	left join QcCodeList as QCL on QCL.CLID=QMR.CLID WHERE `MbReportID`='$ID' GROUP by DayCode,VarietyID order by QCL.CLID ASC; ";

// 	$stmt = $iconn->query($sql);

// 	while ($post_details = $stmt->fetch_array()) {
// 		$ary_fields[] = $post_details;
// 	}

// 	$sql = "SELECT * FROM QcEiaTestResultHeaders WHERE TestID=3 and Status=1  and Country in ('','$C_NAME') ORDER BY SortOrder ASC";
// 	$stmt_HEADER = $iconn->query($sql);
// 	$content = '<table border="1" cellspacing="0"  cellpadding="0">';

// 	while ($post_details = $stmt_HEADER->fetch_array()) {
// 		$DefValue = htmlspecialchars($post_details['DefaultValue']);
// 		$ColumnName = $post_details['ColumnName'];
// 		$content .= '<tr  >
// 	 <th align="center"><font size="' . $size . '">' . $post_details['ParamName'] . '</font></th>';


// 		foreach ($ary_fields as $ITEM) {
// 			$RowCld = getMcs($ITEM['CLID']);
// 			$RowCld['VARITY'] = $ITEM['VARITY'];
// 			$RowCld['TPC'] = $ITEM['Tpc'];
// 			$RowCld['DateOfAnalysis'] = $ITEM['DateOfAnalysis'];
// 			$RowCld['DateOfIssuance'] = $ITEM['DateOfIssuance'];
// 			$RowCld['Listeria'] = $ITEM['Listeria'];

// 			if ($DefValue != '0') {
// 				$RowCld["$ColumnName"] = $DefValue;
// 			}

// 			$content .= '<th align="center"><font size="' . $size . '">' . $RowCld["$ColumnName"] . '</font></th>';
// 		}
// 		$content .= '</tr>';
// 	}
// 	return $content;
// }


function getRawTable()
{
	global $iconn, $ID, $C_NAME;
	$size = 8;

	// Fetch all MBResult data
	// $sql = "SELECT GROUP_CONCAT(QMR.CLID) CLID,
	// 			   GROUP_CONCAT(DISTINCT DateOfAnalysis) DateOfAnalysis,
	// 			   GROUP_CONCAT(DISTINCT DateOfIssuance) DateOfIssuance,
	// 			   Tpc, Listeria, VarietyID, DayCode 
	// 		FROM QcMBResult AS QMR 
	// 		LEFT JOIN QcCodeList AS QCL ON QCL.CLID = QMR.CLID 
	// 		WHERE MbReportID='$ID' 
	// 		GROUP BY DayCode, VarietyID 
	// 		ORDER BY QCL.CLID ASC;";



$sql = "SELECT GROUP_CONCAT(QMR.CLID) CLID,
				   GROUP_CONCAT(DISTINCT DateOfAnalysis) DateOfAnalysis,
				   GROUP_CONCAT(DISTINCT DateOfIssuance) DateOfIssuance,
				   Tpc, Listeria, VarietyID, DayCode 
			FROM QcMBResult AS QMR 
			LEFT JOIN QcCodeList AS QCL ON QCL.CLID = QMR.CLID 
			WHERE MbReportID='$ID' 
			GROUP BY DayCode, VarietyID 
			ORDER BY QCL.DayCode ASC;";


	$stmt = $iconn->query($sql);
	$ary_fields = [];
	while ($row = $stmt->fetch_array()) {
		$ary_fields[] = $row;
	}

	// Fetch headers
	$sql = "SELECT * FROM QcEiaTestResultHeaders 
			WHERE TestID=3 AND Status=1 AND Country IN ('', '$C_NAME') 
			ORDER BY SortOrder ASC";
	$stmt_HEADER = $iconn->query($sql);

	$headers = [];
	while ($header = $stmt_HEADER->fetch_array(MYSQLI_ASSOC)) {
		$headers[] = $header;
	}

	$content = '<table border="1" cellspacing="0" cellpadding="0">';

	// Print headers
	foreach ($headers as $header) {
		$ColumnName = $header['ColumnName'];
		$DefValue = htmlspecialchars($header['DefaultValue']);

		$content .= '<tr>
						<th align="center"><font size="' . $size . '">' . $header['ParamName'] . '</font></th>';

		foreach ($ary_fields as $ITEM) {
			$RowCld = getMcs($ITEM['CLID']);
			$RowCld['TPC'] = $ITEM['Tpc'];
			$RowCld['DateOfAnalysis'] = $ITEM['DateOfAnalysis'];
			$RowCld['DateOfIssuance'] = $ITEM['DateOfIssuance'];
			$RowCld['Listeria'] = $ITEM['Listeria'];

			// Use default if provided
			if ($DefValue != '0') {
				$RowCld[$ColumnName] = $DefValue;
			}

			// Force Listeria print
			if (strtolower($ColumnName) == 'listeria') {
				$valueToPrint = !empty($RowCld['Listeria']) ? $RowCld['Listeria'] : 'ND'; // ND = Not Detected
			} else {
				$valueToPrint = isset($RowCld[$ColumnName]) ? $RowCld[$ColumnName] : '';
			}


			$content .= '<th align="center"><font size="' . $size . '">' . $valueToPrint . '</font></th>';
		}
		$content .= '</tr>';
	}

	// Add extra Listeria row if not already included in headers
	$hasListeria = false;
	foreach ($headers as $header) {
		if (strtolower($header['ColumnName']) == 'listeria') {
			$hasListeria = true;
			break;
		}
	}
	if (!$hasListeria) {
		$content .= '<tr>
						<th align="center"><font size="' . $size . '">Listeria</font></th>';
		foreach ($ary_fields as $ITEM) {
			$content .= '<th align="center"><font size="' . $size . '">' . $ITEM['Listeria'] . '</font></th>';
		}
		$content .= '</tr>';
	}

	$content .= '</table>';
	return $content;
}


// function getCookedTable()
// {
// 	global $iconn, $ID, $C_NAME;

// 	$IsMultiple = false;
// 	$size = 8;
// 	$sql = "SELECT GROUP_CONCAT(QMR.CLID) CLID,GROUP_CONCAT(distinct DateOfAnalysis) DateOfAnalysis,
// 	GROUP_CONCAT(distinct DateOfIssuance) DateOfIssuance ,Tpc,Listeria,VarietyID,DayCode FROM QcMBResult  as QMR 
// 	left join QcCodeList as QCL on QCL.CLID=QMR.CLID WHERE `MbReportID`='$ID' GROUP by DayCode,VarietyID order by QCL.CLID ASC; ";

// 	$stmt = $iconn->query($sql);

// 	while ($post_details = $stmt->fetch_array()) {
// 		$ary_fields[] = $post_details;
// 	}

// 	$sql = "SELECT * FROM QcEiaTestResultHeaders WHERE TestID=3 and Status=1  and Country in ('','$C_NAME') ORDER BY SortOrder ASC";
// 	$stmt_HEADER = $iconn->query($sql);
// 	$content = '<table border="1" cellspacing="0"  cellpadding="0">';

// 	while ($post_details = $stmt_HEADER->fetch_array()) {


// 		$DefValue = htmlspecialchars($post_details['DefaultValue']);
// 		$ColumnName = $post_details['ColumnName'];

// 		$MULTIPLE_COUNT = $post_details['Multiple'];
// 		$content .= '<tr>
// 	 <th align="left"><span> </span><font size="' . $size . '">' . nl2br($post_details['ParamName']) . '</font></th>';

// 		foreach ($ary_fields as $ITEM) {
// 			$RowCld = getMcs($ITEM['CLID']);
// 			$RowCld['VARITY'] = $ITEM['VARITY'];
// 			$RowCld['TPC'] = $ITEM['Tpc'];
// 			$RowCld['DateOfAnalysis'] = $ITEM['DateOfAnalysis'];
// 			$RowCld['DateOfIssuance'] = $ITEM['DateOfIssuance'];
// 			$RowCld['Listeria'] = $ITEM['Listeria'];


// 			if ($DefValue != '0') {
// 				$RowCld["$ColumnName"] = $DefValue;
// 			}

// 			if ($ColumnName == 'TPC') {
// 				$Data = getMultipleTpc($RowCld["$ColumnName"]);
// 				$content .= '<th>' . $Data . '</th>';

// 				$IsMultiple = true;
// 			} else if ($MULTIPLE_COUNT == 0) {
// 				$content .= '<th align="center"><font size="' . $size . '">' . $RowCld["$ColumnName"] . '</font></th>';
// 			} else if ($IsMultiple) {
// 				$MULTIPLE_COUNT = count(EXPLODE("/", $RowCld['TPC']));


// 				$Data = getMultiple($RowCld["$ColumnName"], $MULTIPLE_COUNT);
// 				$content .= '<th>' . $Data . '</th>';
// 			} else {
// 				$RowCld["$ColumnName"];
// 				$content .= '<th align="center"><font size="' . $size . '">' . $RowCld["$ColumnName"] . '</font></th>';
// 			}
// 		}
// 		$content .= '</tr>';
// 	}
// 	return $content;
// }


function getCookedTable()
{
	global $iconn, $ID, $C_NAME;
	$size = 8;
	$IsMultiple = false;

	// $sql = "SELECT GROUP_CONCAT(QMR.CLID) CLID,
    //                GROUP_CONCAT(DISTINCT DateOfAnalysis) DateOfAnalysis,
    //                GROUP_CONCAT(DISTINCT DateOfIssuance) DateOfIssuance,
    //                Tpc, Listeria, VarietyID, DayCode 
    //         FROM QcMBResult AS QMR 
    //         LEFT JOIN QcCodeList AS QCL ON QCL.CLID = QMR.CLID 
    //         WHERE MbReportID='$ID' 
    //         GROUP BY DayCode, VarietyID 
    //         ORDER BY QCL.CLID ASC;";



$sql = "SELECT GROUP_CONCAT(QMR.CLID) CLID,
                   GROUP_CONCAT(DISTINCT DateOfAnalysis) DateOfAnalysis,
                   GROUP_CONCAT(DISTINCT DateOfIssuance) DateOfIssuance,
                   Tpc, Listeria, VarietyID, DayCode 
            FROM QcMBResult AS QMR 
            LEFT JOIN QcCodeList AS QCL ON QCL.CLID = QMR.CLID 
            WHERE MbReportID='$ID' 
            GROUP BY DayCode, VarietyID 
            ORDER BY QCL.DayCode ASC;";



	$stmt = $iconn->query($sql);
	$ary_fields = [];
	while ($row = $stmt->fetch_array()) {
		$ary_fields[] = $row;
	}

	$sql = "SELECT * FROM QcEiaTestResultHeaders 
            WHERE TestID=3 AND Status=1 AND Country IN ('', '$C_NAME') 
            ORDER BY SortOrder ASC";
	$stmt_HEADER = $iconn->query($sql);

	$content = '<table border="1" cellspacing="0" cellpadding="0">';

	while ($header = $stmt_HEADER->fetch_array()) {
		$ColumnName = $header['ColumnName'];
		$DefValue = htmlspecialchars($header['DefaultValue']);
		$MULTIPLE_COUNT = $header['Multiple'];
		if($ColumnName == 'Listeria'){
			$printRow = 0;
			foreach ($ary_fields as $ITEM) {// for checking listeria value
				if(!empty($ITEM['Listeria']) && $ITEM['Listeria'] != '-' && $printRow == 0){
					$printRow = 1;
				}
			}
		}else{
			$printRow = 1;
		}
		if($printRow ==1){
			$content .= '<tr>
							<th align="left"><font size="' . $size . '">' . nl2br($header['ParamName']) . '</font></th>';

			foreach ($ary_fields as $ITEM) {
				$RowCld = getMcs($ITEM['CLID']);
				$RowCld['TPC'] = $ITEM['Tpc'];
				$RowCld['DateOfAnalysis'] = $ITEM['DateOfAnalysis'];
				$RowCld['DateOfIssuance'] = $ITEM['DateOfIssuance'];
				$RowCld['Listeria'] = $ITEM['Listeria'];

				if ($DefValue != '0') {
					$RowCld[$ColumnName] = $DefValue;
				}

				// Multiple TPC handling
				if ($ColumnName == 'TPC') {
					$Data = getMultipleTpc($RowCld[$ColumnName]);
					$content .= '<th>' . $Data . '</th>';
					$IsMultiple = true;
				} elseif ($MULTIPLE_COUNT == 0) {
					$valueToPrint = isset($RowCld[$ColumnName]) ? $RowCld[$ColumnName] : '';
					if ($ColumnName == 'Listeria') {
						$valueToPrint = $RowCld['Listeria'];
					}
					$content .= '<th align="center"><font size="' . $size . '">' . $valueToPrint . '</font></th>';
				} elseif ($IsMultiple) {
					$MULTIPLE_COUNT = count(explode("/", $RowCld['TPC']));
					$Data = getMultiple($RowCld[$ColumnName], $MULTIPLE_COUNT);
					$content .= '<th>' . $Data . '</th>';
				} else {
					$content .= '<th align="center"><font size="' . $size . '">' . $RowCld[$ColumnName] . '</font></th>';
				}
			}
			$content .= '</tr>';
		}
	}

	// Add extra Listeria row if missing in headers
	$content .= ' <!-- <tr>
                    <th align="left"><font size="' . $size . '">Listeria</font></th>';
	foreach ($ary_fields as $ITEM) {
    $ListeriaValue = !empty($ITEM['Listeria']) ? $ITEM['Listeria'] : 'ND';
    $content .= '<th align="center"><font size="' . $size . '">' . $ListeriaValue . '</font></th>';
}

	$content .= '</tr> -->';

	$content .= '</table>';
	return $content;
}


function getMultipleTpc($TPC)
{

	$size = 7;

	$AryTPC = EXPLODE("/", $TPC);

	$TPC_TABLE = '';
	$TPC_TABLE = '<table border="0" cellspacing="0"  cellpadding="0"><tr>';
	foreach ($AryTPC as $Item) {
		$TPC_TABLE .= '<th align="center"><font size="' . $size . '">' . $Item . '</font></th>';
	}
	$TPC_TABLE .= '</tr></table>';

	return $TPC_TABLE;
}



function getMultiple($Value, $COUNT)
{
	$COUNT;
	$size = 8;
	$TPC_TABLE = '<table border="1" cellspacing="0"  cellpadding="2"><tr>
	';
	for ($Item = 0; $Item < $COUNT; $Item++) {
		$PrintValue[] = $Value;
		$TPC_TABLE .= '<th align="center"><font size="' . $size . '">' ?>
		<?php

		for ($Item1 = 0; $Item1 < 1; $Item1++) {
			$TPC_TABLE .= '' . $Value . '';
		}

		$TPC_TABLE .= '</font></th>';
	}

	$Value = implode(',', $PrintValue);
	$TPC_TABLE .= '</tr></table>';

	return $TPC_TABLE;
}


function getMcs($CLID)
{
	global $ID, $iconn, $INVOICE_ID;

	$SqlMcs = "SELECT sum(McsTotal) as TMCS FROM QcCodeList WHERE InvID='$INVOICE_ID'";
	$StmtMcs = $iconn->query($SqlMcs);
	$RowMcs = $StmtMcs->fetch_array();
	$TMCS = $RowMcs['TMCS'];
	$T_Samples = (sqrt($TMCS) + 1) / 2;
	$T_Samples = round($T_Samples);

	$SqlCodeList = "SELECT QCLD.Brand, group_concat(distinct Grades) as Grades,DayCode,group_concat(distinct Tracebility) as Tracebility,TracebilityID,QCLD.PackingStyleID,TypeProduct,PreparationType,
	Species,ShortName FROM `QcCodeList` as QCLD left join   `ScrmPoGradeData` as SPGD  on QCLD.PoGradeID=SPGD.ID 
	left join ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
	left join QcTracebilityList as QTL on QTL.TraceID=QCLD.TracebilityID
	WHERE CLID in ($CLID)";
	$StmtCodeList = $iconn->query($SqlCodeList);
	$RowCodeList = $StmtCodeList->fetch_array();

	$RowCodeList['Traceability'] = $RowCodeList['Tracebility'];
	$TypeProduct = $RowCodeList['TypeProduct'];
	$ShortName = $RowCodeList['ShortName'];


	$RowCodeList['ShortName'] = getProductTypeCode($TypeProduct, $ShortName);
	return $RowCodeList;
}


		?>