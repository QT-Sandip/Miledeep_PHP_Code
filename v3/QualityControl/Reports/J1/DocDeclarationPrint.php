<?php
session_start();
$PoID = $_GET['PoID'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include($cp . "Includes/IncludePage.php");

include "../PrintHeaderInc.php";


$sql_code_list = "SELECT DISTINCT(DayCode),Brand,PrintStatus FROM QcCodeList QCL
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=QCL.Brand
 WHERE InvID='$PoID' ORDER BY DayCode ASC";
$stmt_code_list = $iconn->query($sql_code_list);

$SqlVList = "SELECT DISTINCT(Variety) FROM `ScrmPoGradeData` WHERE InvId='$PoID'";
$StmtVList = $iconn->query($SqlVList);


while ($code_list_row = $stmt_code_list->fetch_array()) {

	$DAY_CODE = trim($code_list_row['DayCode']);
	$Brand = trim($code_list_row['Brand']);
	 $PrintStatus = trim($code_list_row['PrintStatus']); 

	if($DAY_CODE!=''){
	$AryDayCodes[] = $DAY_CODE;
	}

	if($Brand!='' && $PrintStatus!=0){
		$AryBrand[] = $Brand;
		}

}

$DayCodes = implode(",", $AryDayCodes);

$BrandList = implode(",", array_unique($AryBrand)); 

//echo $BrandList; exit;

$sql = "SELECT *,PD.ExporterID from QcCoveringLetter as CL 
left join ScrmPoData as PD on PD.PoID=CL.InvID 
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID
WHERE InvID ='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();
$DestinationCountry = $row['DestinationCountry'];
$SaltTreated = $row['TypeTreated'];



if ($row['TypeTreated'] == 'TREATED') {
	$PHSPT = 'Water, salt ,shrimp,Sodium Tripoly phosphate';
} else {
	$PHSPT = 'Citric Acid, Paprica & Allura Red';
}

$header = 11;
$sub_header = 10;


if ($row['TypeTreated'] == 'TREATED') {
	
	$PhosphateCondition = 'sodium tripolyphosphate (stabilizzante E 451, E452)';
	
} else {
    $PhosphateCondition = ' Sodium Carbonate & Sodium Citrate,';	
}

if ($row['TypeTreated'] == 'TREATEDNNOTTREATED') {
$BothCondition="V. We hereby declare that samples from the Frozen Prawns consignment as per the details given above
 have been drawn and tested in our laboratory for bacteriological and organoleptic factors and
 found Satisfactory. We further declare that we used only Salt & sodium tripolyphosphate
 (stabilizzante E 451, E452) Not using any Allergenes, Colours, Spice condiments (Sodium 
 Metabisulphite, Paprika & Allura Red) to the above Consignment.";
} else {
	
	$BothCondition="";
	}
	

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

$InvNO = getInvoiceNumber($iconn, $ExpID, $PoID);
$InvDate = getFormatedDateNA($row['CInvDate']); 
$PoDate = getFormatedDateNA($row['CpoDate']);


/*****uma***/



$SmplngDate = getFormatedDateNA($row['Date']);

$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 15, 'Date:' . $SmplngDate, 0, 1, 'R');
$pdf->Cell(0, 5, 'To,', 0, 1, 'L');

$pdf->Cell(150, 5, 'The Deputy Director,', 0, 1, 'L');
$pdf->Cell(150, 5, 'Export Inspection Agency-Chennai', 0, 1, 'L');
$pdf->Cell(49, 5, 'Sub off:Bhimavaram', 0, 1, 'L');
$pdf->Ln();


$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->Cell(0, 5, 'CONSIGNMENT DETAILS:', 0, 1, 'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 5, 'INVOICE NO:' . $InvNO . ' DT:' . $InvDate, 0, 1, 'L');
$pdf->Ln();

$pdf->Cell(0, 5, 'PRODUCT:', 0, 1, 'L');

$pdf->SetFont($FONT_ARI, '', $header);
$pdf->SetFont($FONT_ARI, 'B', $sub_header);

while ($varity_list_row = $StmtVList->fetch_array()) {
	$QcDescription = $varity_list_row['QcDescription'] ? $varity_list_row['QcDescription'] : $varity_list_row['Variety'];
	$ary_varity_list[] = $QcDescription;
	$pdf->Cell($W2, 5, $QcDescription, 0, 1, 'L');
}
$pdf->SetFont($FONT_ARI, '', $header);
if($BrandList ==''){
	
}else{
	
$pdf->Cell(20, 5, 'BRAND', 0, 0, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(0, 5, $BrandList , 0, 1, 'L');
$pdf->SetFont($FONT_ARI, '', $header);

}
$pdf->Cell(0, 5, '', 0, 1, 'L');
$pdf->Cell(40, 5, 'PRODUCTION CODE ', 0, 0, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(0, 5, '' . $DayCodes, 0, 1, 'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI, 'BU', 15);
$pdf->Cell(0, 5, 'DECLARATION', 0, 1, 'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 5, '', 0, 1, 'L');

if($SaltTreated == 'SALTTREATMENT'){
	$contents .= '<p> We here by declare that samples from the Frozen Prawns consignment as per the details 
	given above have drawn and tested in our laboratory for bacteriological and organoleptic factors and 
	found Satisfactory. We further declare that we used only Salt Not using any Allergenes, Colours, Spice
    condiments (Sodium Metabisulphite, Paprica & Allura Red) to the above Consignment.</p>';
}
elseif($SaltTreated == 'UNTREATED'){
	$contents .= '<p> We here by declare that samples from the Frozen Prawns consignment as per the details 
	given above have drawn and tested in our laboratory for bacteriological and organoleptic factors and found Satisfactory. 
	We further declare that we were not used any chemical or any Allergenes, Colours, Spice condiments 
	(Sodium Metabisulphite, Paprica & Allura Red) to the above Consignment.</p>';
}
else{
	$contents = '<p>I. We here by declare that the sample from the frozen shrimp consignment as per details
given above draw and tested in our laboratory for bacteriological and organoleptic factors and found 
satisfactory
';
if ($DestinationCountry == 26) {
	$contents .= '<br><br>II.We are not using   Sodium metabisulphites(E-223) any Colors,Spice condiments,Citric Acid,
Paprika & Allura Red to the above Consignment.
<br><br>II.Parasites has been visually examined and not found.And product is satisfactory.';
} else {
	$contents .= '
<br><br>II.We further declare that we are not using Allergenes colours,spice condiments (Sodium Metabisulphite, Paprika & Allura 
 Red) to the above Consignment.
<!--(' . $PHSPT . ')  to the above consignment.-->
<br><br>III.We are using  Shrimp, Water, Salt and '.$PhosphateCondition.' to the above Consignment.
<br><br>IV.Parasites has been visually examined and not found.And product is satisfactory.
<br>'.$BothCondition.'

 
';
}
$contents .= '

</p>';
}

$pdf->writeHTML($contents);

$pdf->Ln();
$pdf->Cell(0, 5, '', 0, 1, 'L');
$pdf->Cell(35, 5, 'Q-certificate No ', 0, 0, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(0, 5, '' . $row['QCertNo'], 0, 1, 'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(0, 5, 'Yours faith fully,', 0, 1, 'L');
$pdf->Ln();
$pdf->Cell(0, 5, 'For ' . $PROCESSOR, 0, 1, 'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(0, 5, $VERIFIED_BY, 0, 1, 'L');
$pdf->Cell(0, 5, $Designation, 0, 1, 'L');
$pdf->SetFont($FONT_ARI, 'B', 15);
//$pdf->Cell(0 ,5,'QC TECHNOLOGIST',0,1,'L');
$pdf->Ln();
ob_clean();
$pdf->Output('Declaration.pdf', 'I');
?>