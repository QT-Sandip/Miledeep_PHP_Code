<?php
session_start();
$PoID = $_GET['PoID'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";


$SqlCodeList = "SELECT DISTINCT(DayCode),PackingStyle,Brand,PrintStatus
FROM QcCodeList QCL
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=QCL.Brand
left join AdminPackingStyles as Aps On QCL.PackingStyleID=Aps.PCKID
 WHERE InvID='$PoID' ORDER BY DayCode ASC";
//echo $SqlCodeList; exit;
$StmtCodeList = $iconn->query($SqlCodeList);

$SqlVList = "SELECT DISTINCT(Variety) FROM `ScrmPoGradeData` WHERE InvId='$PoID'";
$StmtVList = $iconn->query($SqlVList);

$SqlProductList = "SELECT Species,QcDescription,Variety FROM `ScrmPoGradeData` as SPGD 
left join ScrmPIVarietyList SPVL on SPVL.VarietyID=SPGD.VarietyID 
left join QcCodeList QCL on QCL.PoGradeID=SPGD.ID
WHERE SPGD.InvId='$PoID' ORDER BY QCL.CLID ASC";

//ECHO $SqlProductList; EXIT;
$StmtProductist = $iconn->query($SqlProductList);

while ($RowProductName = $StmtProductist->fetch_assoc()) {

	$QcDescription = $RowProductName['QcDescription'] ? $RowProductName['QcDescription'] : $RowProductName['Variety'];
	$AryProductName[] = trim($QcDescription);
}

$AryProductName = array_unique($AryProductName);
$ProductNameList = implode(" ", array_unique($AryProductName));

$SqlTotalMcs = "SELECT sum(McsTotal) as TotalMcs FROM QcCodeList QCL WHERE InvID='$PoID'";
$StmtTotalMcs = $iconn->query($SqlTotalMcs);
$RowTotalMcs = $StmtTotalMcs->fetch_array();
$TotalMcs = trim($RowTotalMcs['TotalMcs']);


while ($RowCodeList = $StmtCodeList->fetch_array()) {

	$DAY_CODE = trim($RowCodeList['DayCode']);
	$Brand = trim($RowCodeList['Brand']);
	$PrintStatus = trim($RowCodeList['PrintStatus']);

	$AryPackings[] = $RowCodeList['PackingStyle'];
	if ($DAY_CODE != '') {
		$AryDayCodes[] = $DAY_CODE;
	}

	if ($Brand != '' && $PrintStatus != 0) {
		$AryBrand[] = $Brand;
	}
}

$DayCodes = implode(",", array_unique($AryDayCodes));

$BrandList = implode(",", array_unique($AryBrand));
$AryPackings = array_unique($AryPackings);
$PACKING = implode(',', $AryPackings);





$SqlPoDetails = "SELECT *,PD.ExporterID from QcCoveringLetter as CL 
left join ScrmPoData as PD on PD.PoID=CL.InvID 
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID
WHERE InvID ='$PoID'";
$StmtPoDetails = $iconn->query($SqlPoDetails);
$row = $StmtPoDetails->fetch_array();
$DestinationCountry = $row['DestinationCountry'];
$PoNo = $row['PoNo'];
$ConsigneeID = $row['ConsigneeID'];
$DeclarationDate = $row['DeclarationDate'];

$SqlConDetails = "SELECT * FROM ScrmConsigneeDetails  WHERE `ConID`='$ConsigneeID'";
$StmtConDetails = $iconn->query($SqlConDetails);
$ConData = $StmtConDetails->fetch_array();
$ConsigneeName = $ConData['ConsigneeName'];

if ($row['TypeTreated'] == 'TREATED') {
	$PHSPT = 'Water, salt ,shrimp,Sodium Tripoly phosphate';
} else {
	$PHSPT = 'Citric Acid, Paprica & Allura Red';
}

$header = 11;
$sub_header = 10;


$SqlVarifiedBy = "SELECT VerifiedBy, LbuDesignation FROM `QcCoveringLetter` as CL 
LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
 WHERE InvID='$PoID' ";

$StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
$RowVarifiedBy = $StmtVarifiedBy->fetch_array();
$VERIFIED_BY = $RowVarifiedBy['VerifiedBy'];
$Designation = $RowVarifiedBy['LbuDesignation'];

/**uma**/



$ExpID = $row['ExporterID'];

$ExporterData = getExporterDetails($ExpID);
$ExporterName = $ExporterData['ExpName'];
$InvNO = getInvoiceNumber($iconn, $ExpID, $PoID);


//$InvNO=$ExporterData['InvShortCode'].$row['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

$InvDate = getFormatedDateNA($row['InvDate']);
$PoDate = getFormatedDateNA($row['CpoDate']);


/*****uma***/



//$SmplngDate = getFormatedDateNA($row['CInvDate']);
$SmplngDate = getFormatedDateNA($DeclarationDate);

$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 15, 'Date:' . $SmplngDate, 0, 1, 'R');
$pdf->Cell(0, 5, 'To,', 0, 1, 'L');

$pdf->Cell(150, 5, 'The Deputy Director,', 0, 1, 'L');
$pdf->Cell(150, 5, 'Export Inspection Agency-Chennai', 0, 1, 'L');
$pdf->Cell(49, 5, 'Sub off:VISAKHAPATNAM ', 0, 1, 'L');
$pdf->Ln();


// 


$pdf->SetFont($FONT_ARI, 'BU', 15);
$pdf->Cell(0, 5, 'DECLARATION', 0, 1, 'C');
$pdf->Ln();


$padding = 20;

$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->SetX($padding);
$pdf->Cell(0, 5, 'CONSIGNMENT DETAILS:', 0, 1, 'L');

$pdf->SetFont($FONT_ARI, '', $header);
$pdf->SetX($padding);
$pdf->Cell(0, 5, 'INVOICE NO: ' . $InvNO . ' DT: ' . $SmplngDate, 0, 1, 'L');

$pdf->SetX($padding);
$pdf->Cell(0, 5, 'PO NO      : ' . $PoNo, 0, 1, 'L');

$pdf->SetX($padding);
$pdf->Cell(0, 5, 'BUYER      : ' . $ConsigneeName, 0, 1, 'L');

$pdf->SetX($padding);
$pdf->Cell(0, 5, 'TOTAL      : ' . $TotalMcs . 'MCS', 0, 1, 'L');

$pdf->Ln();

$pdf->SetX($padding);
$pdf->Cell(0, 5, 'PRODUCT:', 0, 1, 'L');

$pdf->SetFont($FONT_ARI, 'B', $sub_header);
foreach ($AryProductName as $Item) {
	$pdf->SetX($padding);
	$pdf->Cell($W1, $H1, $Item, 0, 1, 'L');
}

$pdf->SetFont($FONT_ARI, '', $header);
if ($BrandList != '') {
	$pdf->SetX($padding);
	$pdf->Cell(20, 5, 'BRAND', 0, 0, 'L');
	$pdf->SetFont($FONT_ARI, 'B', $header);
	$pdf->Cell(0, 5, $BrandList, 0, 1, 'L');
	$pdf->SetFont($FONT_ARI, '', $header);
}

$pdf->SetX($padding);
$pdf->Cell(20, 5, 'PACKING', 0, 0, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(0, 5, $PACKING, 0, 1, 'L');

$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 5, '', 0, 1, 'L');

$pdf->SetX($padding);
$pdf->Cell(40, 5, 'PRODUCTION CODE ', 0, 0, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(0, 5, $DayCodes, 0, 1, 'L');
$pdf->Ln(1);
//$pdf->SetAutoPageBreak(true, 30);


$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 5, '', 0, 1, 'L');

$TypeTreated = trim($row['TypeTreated']);

if ($TypeTreated === 'TREATED') {
    $contents  = '<p style="text-indent:100px;">We are Hereby declare that samples from Frozen Shrimps consignment as per the details ';
    $contents .= 'given above draw and tested in our Laboratory for Bacteriological and Organoleptic Factors and found Satisfactory.</p>';

    $contents .= '<br><br>We further declare that we are using Salt and Phosphate (Sodium Triphosphate (E451), Sodium Polyphosphate (E452).';
    $contents .= '<br><br>We are not using Sodium metabisulphite (E-223), Paprika (E-160c), Allergens, Spice Condiments & Allure Red.';
}

if ($TypeTreated === 'NOTTREATED') {
   $contents  = '<p style="text-indent:100px;">We are Hereby declare that samples from Frozen Shrimps consignment as per the details ';
    $contents .= 'given above draw and tested in our Laboratory for Bacteriological and Organoleptic Factors and found Satisfactory.</p>';

    $contents .= '<br><br>We further declare that we are using Salt and Non Phosphate (Sodium Carbonates (E500), Sodium Citrates (E331).';
    $contents .= '<br><br>We are not using Sodium metabisulphite (E-223), Paprika (E-160c), Allergens, Spice Condiments & Allure Red.';
}

$contents .= '</p>';

/*if ($DestinationCountry == 26) {
	$contents .= '<br><br>II.We are not using   Sodium metabisulphites(E-223) any Colors,Spice condiments,Citric Acid,
Paprika & Allura Red to the above Consignment.
<br><br>II.Parasites has been visually examined and not found.And product is satisfactory.';
} else {
	$contents .= '
<br><br>II.We further declare that we are not using Allergenes colours,spice condiments.
<!--(' . $PHSPT . ')  to the above consignment.-->
<br><br>III.We are using  Shrimp, Water, Salt and Sodium Tripoly phosphate to the above Consignment.
<br><br>IV.Parasites has been visually examined and not found.And product is satisfactory.
';
}*/
$contents .= '

</p>';
$pdf->writeHTML($contents);

//$pdf->Ln();
$pdf->Cell(0, 5, '', 0, 1, 'L');
$pdf->Cell(35, 5, 'Q-certificate No ', 0, 0, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(0, 5, '' . $row['QCertNo'] . '  Date :' . $SmplngDate, 0, 0, 'L');
//$pdf->Cell(0, 5, 'Date :'. $SmplngDate, 0, 0, 'R');

$pdf->Ln();
$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Ln();
$pdf->Cell(0, 5, 'Yours faith fully,', 0, 1, 'L');
$pdf->Ln();
$pdf->Cell(0, 5, 'For ' . $ExporterName, 0, 1, 'L');
$pdf->Ln();
 $pdf->Ln();
 $pdf->Ln();
// $pdf->Ln();
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(0, 5, $VERIFIED_BY, 0, 1, 'L');
$pdf->Cell(0, 5, $Designation, 0, 1, 'L');
$pdf->SetFont($FONT_ARI, 'B', 15);
//$pdf->Cell(0 ,5,'QC TECHNOLOGIST',0,1,'L');
$pdf->Ln();
ob_clean();
$FileName = 'Declaration_' . $InvNO;
$FileName = getFormatedFileName($FileName);
$pdf->Output($FileName . '.pdf', 'I');
//$pdf->Output('Declaration'.$InvNO.'.pdf', 'I');
