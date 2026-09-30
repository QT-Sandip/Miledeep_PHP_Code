<?php
//ini_set('display_errors', 1); 
session_start();
//error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";

$ID = $_GET['RID'];
$INVOICE_ID = $_GET['PoID'];

$SqlMBRprt = "SELECT *,QLT.LbuDesignation AS TestDsgn,QLV.LbuDesignation AS verifyDsgn  FROM QcMBReport  QMB
left join QcLabTechnologist QLT ON QLT.LbuName=QMB.TestedBy
left join QcLabTechnologist QLV ON QLV.LbuName=QMB.VerifiedBy
 WHERE `ID`='$ID'";
$stmtMBRprt = $iconn->query($SqlMBRprt);
$RowReport = $stmtMBRprt->fetch_array();

$TestedBy = $RowReport['TestedBy'];
$VerifiedBy = $RowReport['VerifiedBy'];
$TYPE = $RowReport['TypeProduct'];
$TestDsgn = $RowReport['TestDsgn'];
$verifyDsgn = $RowReport['verifyDsgn'];


$SqlInvData = "SELECT * FROM ScrmPoData POD
	left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
	left join QcEiaTestResultHeaders as QET on QET.CountryID=POD.DestinationCountry 
	where POID='$INVOICE_ID'";
$StmtInvData = $iconn->query($SqlInvData);
$INVOICE_DATA = $StmtInvData->fetch_array();
$Country = $INVOICE_DATA['Country'];
$BuyerName = $INVOICE_DATA['ConsigneeName'];
$InvNumFull = $INVOICE_DATA['InvNumFull'];
$C_NAME = getCountryName($Country);

$ExporterID = $INVOICE_DATA['ExporterID'];
$ExporterDetails = getExporterDetails($ExporterID);
$ExporterName = $ExporterDetails['ExpName'];

$PoID = $RowReport['InvID'];




$SqlOtherData = "SELECT SPGD.Brand,VL.ShortName,Variety,QcDescription,McsTotal,DayCode,SPGD.Grade,
   VerifiedBy,PL.PackingStyle,PrintStatus FROM  `QcCodeList` AS CL 
   left join ScrmPoGradeData as SPGD on SPGD.ID=CL.PoGradeID
   left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
   left JOIN ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
   LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
   WHERE CL.InvID='$PoID' ";
$StmtOtherData = $iconn->query($SqlOtherData);

while ($RowOtherData = $StmtOtherData->fetch_array()) {


	//ROBO 231012023
	$Brand = trim($RowOtherData['Brand']);
	$PrintStatus = trim($RowOtherData['PrintStatus']);

	if ($Brand != '' && $PrintStatus != 0) {
		$AryBrand[] = $Brand;
	}

	$AryMcsTotal[] = $RowOtherData['McsTotal'];
	$AryDayCode[] = $RowOtherData['DayCode'];
	$AryVerifiedBy[] = $RowOtherData['VerifiedBy'];
	$AryPackStyle[] = $RowOtherData['PackingStyle'];
	$AryShortName[] = $RowOtherData['ShortName'];
	$AryGrade[] = $RowOtherData['Grade'];
	$AryFullName[] = $RowOtherData['QcDescription'] ? $RowOtherData['QcDescription'] : $RowOtherData['Variety'];
}

$Brand = implode(",", array_unique($AryBrand));
$McsTotal = array_sum($AryMcsTotal);
$DayCodes = implode(",", array_unique($AryDayCode));
$PackStyle = implode(",", array_unique($AryPackStyle));
$ShortName = implode(",", array_unique($AryShortName));
$GradesList = implode(",", array_unique($AryGrade));
$FullName = implode("<br>", array_unique($AryFullName));

if ($Brand == '') {
	$Brand = '--';
}


$HEIGHT = 8;


$header = 10;
$sub_header = 8;
$text = 6;

$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->SetFont('', 'BU', 12);
$pdf->Cell(0, 5, 'IN HOUSE LABORATORY MICROBIOLOGY TEST REPORT', 0, 1, 'C');

$HEIGHT = 4;
$pdf->SetFont('', '', 11);
//$pdf->Cell(180 ,$HEIGHT,'To',0,1,'L');
//$pdf->Cell(80 ,$HEIGHT,'The Deputy Director',0,1,'L');
//$pdf->Cell(80 ,$HEIGHT,'Export Inspection Agency - Chennai',0,1,'L');
//$pdf->Cell(80 ,$HEIGHT,'Sub Office - Vishakapatnam',0,1,'L');
//$pdf->Cell(80 ,$HEIGHT,'Vishakapatnam- 530016',0,1,'L');
$pdf->Ln();
$pdf->Ln();

$pdf->Cell(80, $HEIGHT, 'Respected Sir/Madam ', 0, 1, 'L');
// $pdf->Cell(80 ,$HEIGHT,'Sub : Bacteriological Results & Orgenoleptic -Reg',0,1,'L');
$pdf->Cell(80, $HEIGHT, 'Sub : Bacteriological Results - Reg', 0, 1, 'L');
$pdf->Cell(10, $HEIGHT, 'Processor/Packer & Exporter: ' . $ExporterName, 0, 1, 'L');
$HEIGHT = 8;


$pdf->SetFont('', '', 12);
$size = 10;




if ($TYPE == 'Raw') {
	$content .= getRawTable();

} else {
	$content .= getCookedTable();

}

$content .= '</table>';

$pdf->writeHTML(getHeaderTable(), false);

$pdf->SetFont('', '', 11);
// $pdf->Cell(80 ,4,'Have been tested for Organoleptic parameters and Bacteriological parameters, ',0,1,'L');
$pdf->Cell(80, 4, 'Have been tested for  Bacteriological parameters ', 0, 1, 'L');
// $pdf->Cell(30 ,4,'(TPC,E.COLI,STAPHYLOCOCCUS,SALMONELLA,V.P,V.CHOLERAE)Found Satisfactotry.',0,1,'L');
//$pdf->Cell(30 ,4,'free from filth and decomposition',0,1,'L');
$pdf->SetFont('', 'BU', 12);
$pdf->SetMargins(15, 29, 16, true);
$pdf->Cell(180, $HEIGHT, 'BACTERIOLOGICAL RESULTS', 0, 1, 'C');
$pdf->SetFont('', '', 12);


$pdf->writeHTML($content, false);

$pdf->Ln();
$pdf->SetFont('', '', 8);

$HEIGHT = 5;
$pdf->Ln();

$pdf->SetFont($FONT_ARI, 'B', $sub_header);

// 13-02-
$pdf->Cell(90, 10,'',1,0,'C',0);
$pdf->Cell(90, 10,'',1,1,'C',0);  
$pdf->Cell(90, 10, 'Analysed By :' . $TestedBy, 1, 0, 'C', 0);
$pdf->Cell(90, 10, 'Verified By :' . $VerifiedBy, 1, 1, 'C', 0);
$pdf->Cell(90, 6, 'Designation  :' . $TestDsgn, 1, 0, 'C', 0);
$pdf->Cell(90, 6, 'Designation  :' . $verifyDsgn, 1, 1, 'C', 0);
// cell with left and right borders


ob_end_clean();

$pdf->Ln();
$pdf->Output('MicrobiologyReport' . $InvNumFull . '.pdf', 'I');

function getHeaderTable()
{
	global $ID, $McsTotal, $INVOICE_DATA, $DayCodes, $PackStyle, $PoID, $Brand, $ShortName, $GradesList;





	$size = 9;
	$content = '<table border="1" cellspacing="0"  cellpadding="3">
	<tr >
	
	 <th width="20%" align="left"><font size="' . $size . '">P.O NO </font></th>
	 <th width="30%"  align="left"><font size="' . $size . '">' . $INVOICE_DATA['PoNo'] . ' </font></th>
	 <th width="20%" align="left"><font size="' . $size . '">INVOICE NO </font></th>
	 <th width="30%"  align="left"><font size="' . $size . '">' . $INVOICE_DATA['InvNumFull'] . ' </font></th>
	 
	 
	 </tr>
	 <tr >
	 <th width="20%" align="left"><font size="' . $size . '">BUYER </font></th>
	 <th width="30%"  align="left"><font size="' . $size . '">' . $INVOICE_DATA['ConsigneeName'] . ' </font></th>
	 <th width="20%"  align="left"><font size="' . $size . '">BRAND</font></th>
	 <th width="30%"  align="left"><font size="' . $size . '">' . $Brand . ' </font></th>
	 </tr>
	 <tr  >
	 <th width="20%"  align="left"><font size="' . $size . '">TYPE OF PACKING</font></th>
	 <th width="30%"  align="left"><font size="' . $size . '">' . $PackStyle . '</font></th>
	 <th width="20%"  align="left"><font size="' . $size . '">DAY CODES</font></th>
	 <th width="30%"  align="left"><font size="' . $size . '">' . $DayCodes . '</font></th>
	 </tr>
	 <tr>
	 
	 </tr>
	 <tr>
	 <th width="20%" align="left"><font size="' . $size . '">VARIETY</font></th>
	 <th width="30%"  align="left"><font size="' . $size . '">' . $ShortName . '</font></th>
	 <th width="20%"  align="left"><font size="' . $size . '">GRADES</font></th>
	 <th width="30%" align="left"><font size="' . $size . '">' . $GradesList . '</font></th>
	 </tr>
	 <tr>
	
	 </tr>
	 <tr  >
	 <th align="left"><font size="' . $size . '">TOTAL NO.OF MASTER CARTONS</font></th>
	 <th align="left"><font size="' . $size . '">' . $McsTotal . ' MCS</font></th>
	 </tr>
 </table>';


	return $content;
}


function getRawTable()
{
	global $iconn, $ID, $C_NAME, $TblQcEiaTestResultHeaders, $INVOICE_ID, $TblQcMBResult;
	$size = 8;
	$sql = "SELECT * FROM QcMBResult WHERE `MbReportID`='$ID' order by ID ASC ";

	$stmt = $iconn->query($sql);

	while ($post_details = $stmt->fetch_array()) {
		$ary_fields[] = $post_details;
	}


	 $sql = "SELECT * FROM QcEiaTestResultHeaders WHERE TestID=3 and Country='$C_NAME' ORDER BY SortOrder ASC";
	// EXIT;
	$stmt_HEADER = $iconn->query($sql);
	$content = '<table border="1" cellspacing="0"  cellpadding="0">';


	//	print_r($ary_fields);
	while ($post_details = $stmt_HEADER->fetch_array()) {
		$DefValue = $post_details['DefaultValue'];
		$ColumnName = $post_details['ColumnName'];
		$content .= '<tr  >
	 <th align="center"><font size="' . $size . '">' . nl2br($post_details['ParamName']). '</font></th>';


		foreach ($ary_fields as $ITEM) {
			$RowCld = getMcs($ITEM['CLID']);
			$RowCld['VARITY'] = $ITEM['VARITY'];
			$RowCld['TPC'] = $ITEM['Tpc'];
			$RowCld['DateOfAnalysis'] = $ITEM['DateOfAnalysis'];
			$RowCld['DateOfIssuance'] = $ITEM['DateOfIssuance'];

			if ($DefValue != '0') {
				$RowCld["$ColumnName"] = $DefValue;
			}

			$content .= '<th align="center"><font size="' . $size . '">' . $RowCld["$ColumnName"] . '</font></th>';

		}
		$content .= '</tr>';
	}
	return $content;
}


function getCookedTable()
{
	global $iconn, $ID, $C_NAME, $TblQcEiaTestResultHeaders, $TblQcMBResult;

	$IsMultiple = false;
	$size = 8;
	$sql = "SELECT * FROM QcMBResult WHERE `MbReportID`='$ID' order by ID ASC ";
	$stmt = $iconn->query($sql);

	while ($post_details = $stmt->fetch_array()) {
		$ary_fields[] = $post_details;
	}


	 $sql = "SELECT * FROM QcEiaTestResultHeaders WHERE TestID=3 and Country='$C_NAME' ORDER BY SortOrder ASC";
	// EXIT;
	$stmt_HEADER = $iconn->query($sql);
	$content = '<table border="1" cellspacing="0"  cellpadding="0">';

	while ($post_details = $stmt_HEADER->fetch_array()) {

		//print_r($post_details); exit;
		$DefValue = $post_details['DefaultValue'];
		$ColumnName = $post_details['ColumnName'];
		$MULTIPLE_COUNT = $post_details['Multiple'];
		$content .= '<tr>
	 <td align="center"><font size="' . $size . '">' . nl2br($post_details['ParamName']) . '</font></td>';
		// print_r($ary_fields); exit;

		foreach ($ary_fields as $ITEM) {
			$RowCld = getMcs($ITEM['CLID']);
			$RowCld['VARITY'] = $ITEM['VARITY'];
			$RowCld['TPC'] = $ITEM['Tpc'];
			$RowCld['DateOfAnalysis'] = $ITEM['DateOfAnalysis'];
			$RowCld['DateOfIssuance'] = $ITEM['DateOfIssuance'];


			if ($DefValue != '0') {
				$RowCld["$ColumnName"] = $DefValue;
			}

			if ($ColumnName == 'TPC') {

			// echo	$RowCld["$ColumnName"]; 
				$Data = getMultipleTpc($RowCld["$ColumnName"]);
				if(isset($Data) && !empty($Data)) {
					$content .= '<td >' . $Data . '</td>';
				} 
				$IsMultiple = true;
				// $IsMultiple=false;			
			} 
			else if ($ColumnName == 'ECOLI') {

			// echo	$RowCld["$ColumnName"]; 
				$Data = getMultipleEcoli($RowCld["$ColumnName"]);
				$content .= '<td>' . $Data . '</td>';
				$IsMultiple = true;
				// $IsMultiple=false;			
			} else if ($ColumnName == 'STAP') {

			// echo	$RowCld["$ColumnName"]; 
				$Data = getMultipleStaphylococcus($RowCld["$ColumnName"]);
				$content .= '<td>' . $Data . '</td>';
				$IsMultiple = true;
				// $IsMultiple=false;			
			} 
			else

				if ($IsMultiple) {
					$MULTIPLE_COUNT = count(EXPLODE("/", $RowCld['TPC']));
					$Data = getMultiple($RowCld["$ColumnName"], $MULTIPLE_COUNT);
					$content .= '<td>' . $Data . '</td>';
				} 
				else {
					$RowCld["$ColumnName"];
					$content .= '<td align="center"><font size="' . $size . '">' . $RowCld["$ColumnName"] . '</font></td>';
				}
		}
		$content .= '</tr>';
	}
	return $content;
}



function getMultipleTpc($TPC)
{


	$size = 8;

	$TPC = EXPLODE("/", $TPC);

	$TPC_TABLE = '';
	$TPC_TABLE = '<table border="0" cellspacing="0"  cellpadding="3">
	<tr>';
	// foreach ($TPC as $Item) { //uma
		$TpcItem=$TPC[0];
		$TPC_TABLE .= '<th align="center"><font size="' . $size . '">' . $TpcItem . '</font></th>';
		
	// }
	$TPC_TABLE .= '</tr></table>';

	return $TPC_TABLE;
}

function getMultipleEcoli($TPC)
{


	$size = 8;

	$TPC = EXPLODE("/", $TPC);

	$TPC_TABLE = '';
	$TPC_TABLE = '<table border="0" cellspacing="0"  cellpadding="3">
	<tr>';
	// foreach ($TPC as $Item) { //uma
		$TpcItem=$TPC[0];
		$TPC_TABLE .= '<th align="center"><font size="' . $size . '">' . $TpcItem . '</font></th>';
		
	// }
	$TPC_TABLE .= '</tr></table>';

	return $TPC_TABLE;
}function getMultipleStaphylococcus($TPC)
{


	$size = 8;

	$TPC = EXPLODE("/", $TPC);

	$TPC_TABLE = '';
	$TPC_TABLE = '<table border="0" cellspacing="0"  cellpadding="3">
	<tr>';
	// foreach ($TPC as $Item) { //uma
		$TpcItem=$TPC[0];
		$TPC_TABLE .= '<th align="center"><font size="' . $size . '">' . $TpcItem . '</font></th>';
		
	// }
	$TPC_TABLE .= '</tr></table>';

	return $TPC_TABLE;
}



function getMultiple($Value, $COUNT)
{
	$COUNT;
	if((int)$COUNT < 5) {
		$size = 8;
	} else {
		$size = 6.2;
	}
	$TPC_TABLE = '<table border="0" cellspacing="0" cellpadding="0">
	<tr>';
	for ($Item = 0; $Item < $COUNT; $Item++) {
		$TPC_TABLE .= '<th align="center" style="border-right: 1px solid #000000;"><font size="' . $size . '">' ?>
		<?php

		for ($Item1 = 0; $Item1 < 1; $Item1++) {
			$TPC_TABLE .= '<div style="padding-top:5px;padding-bottom: 5px;">' . $Value . '</div>';
		}

		$TPC_TABLE .= '</font></th>';
	}
	$TPC_TABLE .= '</tr></table>';

	return $TPC_TABLE;
}


function getMcs($CLID)
{
	global $ID, $iconn, $INVOICE_ID, $TblQcCodeList, $TblAdminPackingStyles;

	$SqlMcs = "SELECT sum(McsTotal) as TMCS FROM QcCodeList WHERE InvID='$INVOICE_ID'";

	$StmtMcs = $iconn->query($SqlMcs);
	$RowMcs = $StmtMcs->fetch_array();
	$TMCS = $RowMcs['TMCS'];
	$T_Samples = (sqrt($TMCS) + 1) / 2;
	$T_Samples = round($T_Samples);



	$sql_mc = "SELECT *, PL.PackingStyle as PackStyle, (SELECT SUM(McsTotal) FROM QcCodeList WHERE DayCode=CL.DayCode AND InvID='$INVOICE_ID' AND 
		TracebilityID=CL.TracebilityID and PackingStyleID=CL.PackingStyleID and VarietyID=CL.VarietyID) as DayCodeMcsTotal,
		 (SELECT GROUP_CONCAT(Grades) FROM QcCodeList WHERE DayCode=CL.DayCode AND InvID=2598 AND TracebilityID=CL.TracebilityID 
		 and PackingStyleID=CL.PackingStyleID and VarietyID=CL.VarietyID) as DayCodeGrade FROM QcCodeList as CL 

	left join AdminPackingStyles as PL ON CL.PackingStyleID=PL.PCKID 
	left join ScrmPoGradeData as SPGD ON SPGD.ID=CL.PoGradeID 
	left join ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
	left join QcTracebilityList as QCTL on QCTL.TraceID=CL.TracebilityID WHERE CLID='$CLID' ORDER BY `DayCode`,SPGD.PackingStyleID,TracebilityID ASC";
	$stmt_mc = $iconn->query($sql_mc);
	$RowCld = $stmt_mc->fetch_array();
	$RowCld['McsTotal'] = $RowCld['DayCodeMcsTotal'];
	$RowCld['PackingStyle'] = $RowCld['PackStyle'];
	$TypeProduct = $RowCld['TypeProduct'];
	$ShortName = $RowCld['ShortName'];

	$RowCld['ShortName'] = getProductTypeCode($TypeProduct, $ShortName);

	// EXIT; 

	$EachSample = ($RowCld['McsTotal'] / $TMCS) * $T_Samples;
	if ($EachSample - floor($EachSample) > 0.54) {
		$RowCld['EachSample'] = round($EachSample);
	} else {
		$RowCld['EachSample'] = floor($EachSample);
	}


	//   print_r($RowCld);
	return $RowCld;

}




?>