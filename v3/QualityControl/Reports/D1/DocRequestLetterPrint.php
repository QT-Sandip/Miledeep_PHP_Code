<?php
//ini_set('display_errors', 1); 

session_start();
$IsHeader = 1;
$IsFooter = 1;
$QueryParms = $_GET['Q'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$TYPE = $_GET['TypeProduct'];
$FileType = $_GET['Type'];
$SAMP_ID = $_GET['RID'];

$SqlSamplingLtr = "SELECT *,GROUP_CONCAT(DISTINCT Type) AS TestTypes FROM QcSamplingLettersExt as SL
left join ScrmPoData as PD on PD.PoID=SL.InvID
left join QcLabTechnologist as QLT on QLT.LUID=SL.ApprovedByID
left join ScrmConsigneeDetails as CD on PD.ConsigneeID=CD.ConID 
LEFT JOIN QcEiaAnalysisRequired AS QAR ON SL.SMPLID = QAR.ReportId
WHERE SL.SMPLID='$SAMP_ID'";
$StmtSamplingLtr = $iconn->query($SqlSamplingLtr);
$RowSampling = $StmtSamplingLtr->fetch_array();

$Type = $RowSampling['TypeProduct'];

if ($Type == '') {
	$Qry = "";
} else {
	$Qry = "and TypeProduct='$Type'";
}

$TYPE = $row['TYPE_N_NATURE'];

$sql = "SELECT * FROM QcEiaAnalysisRequired WHERE `ReportId`='$SAMP_ID'";
$stmt_HEADER = $iconn->query($sql);

$sqlFields = "GROUP_CONCAT(CASE WHEN ItemNumber != '' THEN ItemNumber END) AS ItemNumbers,GROUP_CONCAT(DayCode) AS DayCodes,  ProductDescription,   PoNo, 
GROUP_CONCAT( PL.PackingStyle) as PackingStyle,GROUP_CONCAT( Grades) as Grades,sum(McsTotal) as McsTotal,
GROUP_CONCAT(SPGD.Brand) as Brand, GROUP_CONCAT( Tracebility) AS Tracebilitys,ConsigneeName,ConsigneeAddress,FactoryID";
// , SamsClubFactoryIdentificationNumber

$SqlSelect = "SELECT $sqlFields FROM `QcCodeList` AS CL 
left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
LEFT join ScrmPoGradeData as SPGD on SPGD.ID=CL.PoGradeID
LEFT JOIN ScrmPoData AS SPD ON SPGD.InvID = SPD.PoID
left JOIN ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName = CL.Brand
left join QcTracebilityList as TL on TL.TraceID = CL.TracebilityID 
LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
LEFT JOIN QcNewAddonFieldsData AS QNAD ON QNAD.PoID = SPD.PoID
WHERE CL.InvID='$PoID' $Qry GROUP BY CL.InvID order by CL.CLID asc";

$StmtSelect = $iconn->query($SqlSelect);
$McsTotall = 0;

$dataRow = $StmtSelect->fetch_array();
$PackingStyleArray = array_unique(explode(',', $dataRow['PackingStyle'])); // Split the string into an array
$PackingStyle = implode(',', $PackingStyleArray);

$BrandArray = array_unique(explode(',', $dataRow['Brand'])); // Split the string into an array
$Brand = implode(',', $BrandArray);

$DayCodesArray = array_unique(explode(',', $dataRow['DayCodes'])); // Split the string into an array
$DayCodes = implode(',', $DayCodesArray);

$GradesArray = array_unique(explode(',', $dataRow['Grades'])); // Split the string into an array
$Grade = implode(',', $GradesArray);

$TracebilityArray = array_unique(explode(',', $dataRow['Tracebilitys'])); // Split the string into an array
$Traceabilities = implode(',', $TracebilityArray);

$ItemNumber = $dataRow['ItemNumbers'];
// $ItemNumberArray = array_filter(explode(',', $ItemNumber)); // Split the string into an array
// $ItemNumberCount = count($ItemNumberArray);
// if ($ItemNumberCount == 1) {
// 	$PrintItemNumber = $ItemNumber;
// }

$PrintItemNumber = setItemNumber($ItemNumber);

//echo $Brand; exit;
if ($Brand == '') {
	$Brand = '--';
}


$HEIGHT = 8;
$WIDHT_1 = 'Arial';

$header = 12;
$sub_header = 8;
$text = 6;

$TEST_TMSP = strtotime($row['DATE']);
$TEST_DATE = date("d-m-Y", $TEST_TMSP);

$pdf->Cell(0, 10, "", 0, 1, 'C');
$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->Cell(0, 5, "TEST REQUEST FORM (SAM'S CLUB)", 0, 1, 'C');
$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 5, "Request Letter", 0, 1, 'C');

$pdf->SetFont($FONT_ARI, '', $sub_header);
$pdf->Cell(0, 5, 'DATE :' . getFormatedDate($RowSampling['Date']), 0, 1, 'L');

$TestingLab = $RowSampling['TestingLab'];

$SqlEiaLabs = "SELECT * FROM `$QcEiaLabNames` WHERE LabName='$TestingLab'";
$StmtEiaLabs = $iconn->query($SqlEiaLabs);
$RowLab = $StmtEiaLabs->fetch_array();

$pdf->Cell(0, 5, 'To', 0, 1, 'L');
$pdf->Cell(0, 5, $TestingLab . ',', 0, 1, 'L');
$pdf->Cell(0, 5, $RowLab['Address'] . '.', 0, 1, 'L');
$pdf->SetFont($FONT_ARI, '', $sub_header);
$pdf->Ln();

$w1 = '30%';
$w2 = '68%';
$w3 = '2%';
$SqlProductList = "SELECT Species,TypeProduct,QcDescription,Variety FROM `ScrmPoGradeData` as SPGD 
left join ScrmPIVarietyList SPVL on SPVL.VarietyID=SPGD.VarietyID 
 WHERE InvId='$PoID' $Qry";
$StmtProductist = $iconn->query($SqlProductList);
//ECHO $SqlProductList; EXIT;
while ($RowProductName = $StmtProductist->fetch_assoc()) {

	$QcDescription = $RowProductName['QcDescription'] ? $RowProductName['QcDescription'] : $RowProductName['Variety'];
	$AryProductName[] = trim($QcDescription);
}

$AryProductName = array_unique($AryProductName);
$ProductNameList = implode(" ,", array_unique($AryProductName));

$SpeciesName = getSpeciesDetails($Species, $iconn)['SpeciesName'];

$Table = '
		<table border="0" cellspacing="0" cellpadding="3">
			<tr>  
				<th width="' . $w1 . '" align="left">Client/Vendor</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $dataRow['ConsigneeName'] . '<br>' . $dataRow['ConsigneeAddress'] . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Sample Name/Product Name</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $ProductNameList . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Manufacturer</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $PROCESSOR . '.' . $PROCESSOR_ADD . '</th>				
			</tr> 
			<tr>  
				<th width="' . $w1 . '" align="left">Sampling Date/Inspection Date</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left"></th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Purchase Order No</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $dataRow['PoNo'] . '</th>				
			</tr> 
			<tr>  
				<th width="' . $w1 . '" align="left">Sam`s Club Factory Identification No.</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $dataRow['FactoryID'] . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Item Number</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $PrintItemNumber . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Production Code`s</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $DayCodes . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Packing Type</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $PackingStyle . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Total No of M/Cs</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $dataRow['McsTotal'] . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Brand</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $Brand . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Grade`s</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $Grade . '</th>				
			</tr>
			<tr>  
				<th width="' . $w1 . '" align="left">Regis.No.of Farm/Aquaculture Pond</th>
				<th width="' . $w3 . '" align="right">:</th>
				<th width="' . $w2 . '" align="left">' . $Traceabilities . '</th>				
			</tr>
		</table>';
$pdf->writeHTML($Table);

$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 5, '', 0, 1, 'L');
$pdf->Cell(0, 5, 'FACTORS TO BE ANALYSED:', 0, 1, 'L');

$TestTypes = $RowSampling['TestTypes'];
$pdf->SetFont($FONT_ARI, '', 9);
$content = "<p>$TestTypes as per Sam's Club Protocol</p>";
$pdf->writeHTML($content);

// $pdf->Ln();
// $pdf->Cell(180, 10, '', 0, 1, 'L');
// $pdf->Cell(180, 15, 'Authorized Signature', 0, 1, 'L');
// $pdf->Cell(180, 5, 'A.RAMA LAKSHMI', 0, 1, 'L');
// $pdf->Cell(180, 5, 'Senior Manager-Q.A', 0, 1, 'L');

$pdf->Cell(180, 5, 'For ' . $PROCESSOR, 0, 1, 'R');
$pdf->Cell(180, 15, 'Authorized Signature', 0, 1, 'R');

$pdf->SetX($pdf->GetPageWidth() - $rightMargin - $cellWidth); // Set position for right alignment
$pdf->Cell($cellWidth, 5, $RowSampling['LbuName'], 0, 1, 'R');
// Designation
$pdf->SetX($pdf->GetPageWidth() - $rightMargin - $cellWidth); // Set the same position
$pdf->Cell($cellWidth, 5, $RowSampling['LbuDesignation'], 0, 1, 'R');
ob_end_clean();

if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $RowSampling['PoNo'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName . '.pdf', 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName;
	$pdf->Output($FileName . '.pdf', $FileType);
}

function setItemNumber($ItemNumber)
{
	$ItemNumberArray = array_filter(explode(',', $ItemNumber));
	$ItemNumberArray = array_map('trim', $ItemNumberArray);
	$ItemNumberCount = count($ItemNumberArray);

	if ($ItemNumberCount === 1) {
		return reset($ItemNumberArray);
	}

	$ItemNumberArray = array_unique($ItemNumberArray);
	$ItemNumberCount = count($ItemNumberArray);

	if ($ItemNumberCount === 1) {
		return reset($ItemNumberArray);
	}

	return implode(',', $ItemNumberArray);
}