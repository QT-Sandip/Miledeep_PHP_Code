<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
$IsHeader = 1;
$IsFooter = 1;
$QueryParms = $_GET['Q'];



include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$PoID = $_GET['PoID'];
include "PrintHeaderInc.php";

//echo $logo; exit;
if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$TYPE = $_GET['RID'];
$FileType = $_GET['Type'];

IF($TYPE==''){
  $SqlCodeList="SELECT  ID as PoGradeID, SPGD.VarietyID,SPVL.ShortName 
  FROM `ScrmPoGradeData` as SPGD
 left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
  WHERE InvId='$PoID' GROUP by SPVL.ShortName ORDER BY  SPGD.ID ASC";
}ELSE{
  $SqlCodeList="SELECT ID as PoGradeID, SPGD.VarietyID,SPVL.ShortName 
  FROM `ScrmPoGradeData` as SPGD 
  left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
  WHERE InvId='$PoID' and SPVL.TypeProduct='$TYPE' GROUP by SPVL.ShortName ORDER BY SPGD.ID ASC";
}

//echo  $SqlCodeList; exit;

$stmt_code = $iconn->query($SqlCodeList);
$varity_ids = array();

while ($row = $stmt_code->fetch_array()) {
	$Item['VarietyID'] = $row['VarietyID'];
	$Item['PoGradeID'] = $row['PoGradeID'];
	$Item['ShortName'] = $row['ShortName'];
	$varity_ids[]=$Item;
}



$SqlVarifiedBy = "SELECT VerifiedBy, LbuDesignation FROM `QcCoveringLetter` as CL 
					LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
				 	WHERE InvID='$PoID' ";

$StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
$RowVarifiedBy = $StmtVarifiedBy->fetch_array();
$VERIFIED_BY = $RowVarifiedBy['VerifiedBy'];
$Designation = $RowVarifiedBy['LbuDesignation'];

$SqlVarifiedBy = "SELECT Brand,PrintStatus FROM `QcCodeList` CL
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



 $SqlPoGrdData="SELECT BAP,APS.PackingStyle,SPVL.GlazeStatus,SPVL.GlazePerc FROM `ScrmPoGradeData` SPGD
 LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SPGD.PackingStyleID 
 LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.DeviSUPC=SPGD.DeviSupc 
 where InvId='$PoID'";
//echo $SqlPoGrdData; exit;
 $stmtPoGrdData = $iconn->query($SqlPoGrdData);
 While($rowPoData = $stmtPoGrdData->fetch_array()){
	 
	 $BAP = trim($rowPoData['BAP']);
	 $PackingStyle = $rowPoData['PackingStyle'];
	 $GlazeStatus = $rowPoData['GlazeStatus'];
	 if($GlazeStatus=='Yes'){

		$GlazePerc = $rowPoData['GlazePerc'];
		$GlazePerc = '['.$GlazePerc.'% Glaze ]';
	 }
	
	 $AryBAP[] = $BAP;	
	 $AryPackingStyle[] = $PackingStyle;	
 
 }
 $BAPCert = implode(" ", array_unique($AryBAP));
 $PackingStyle = implode(" ,", array_unique($AryPackingStyle));
 
 IF($BAPCert==''){
	 $BAPCert='  '; 
  }

$SqlPoData = "SELECT * FROM `ScrmPoData`
 join ScrmConsigneeDetails on ScrmPoData.ConsigneeID=ScrmConsigneeDetails.ConID
 WHERE $TblScrmPoData.PoID='$PoID'";
$stmtPoData= $iconn->query($SqlPoData);
$row = $stmtPoData->fetch_array();

$IntLotNo = "CODE LIST " . $row['LotNo'];

$DestinationCountry = $row['DestinationCountry']; 
$ExporterID = $row['ExporterID'];
$ExporterDetails=getExporterDetails($ExporterID);
$ExporterName=$ExporterDetails['ExpName'];

$PortOfLoading = getPortDetails($row['PortOfLoading'],$iconn)['PortName'];
$FinalDestination = getPortDetails($row['FinalDestination'],$iconn)['PortName'];

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

$pdf->Cell(0, 10, $IntLotNo, 0, 1, 'C');
$pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(40, 5, 'BUYER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['ConsigneeName'], 0, 1, 'L');
$pdf->Cell(40, 5, 'P.O NUMBER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['PoNo'], 0, 1, 'L');
$pdf->Cell(40, 5, 'INVOICE NUMBER', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $row['InvNumFull'], 0, 1, 'L');
$pdf->Cell(40, 5, 'BRAND', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $Brand, 0, 1, 'L');	

$pdf->Cell(40, 5, 'PACKING', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . $PackingStyle. $GlazePerc  , 0, 0, 'L');

$pdf->Cell(0, 5,  strtoupper($BAPCert) , 0, 1, 'R');
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


foreach ($varity_ids as $VARIETY_ID) {


	$VarityName = getVarityName($VARIETY_ID['PoGradeID'], $iconn);
	$content .= generateRow($VARIETY_ID['VarietyID'], $iconn, $T_GRADES_ARY, $VarityName, 100, $TYPE, $HEADERS_COUNT,$VARIETY_ID['ShortName']);

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








function generateRow($VARIETY_ID, $iconn, $T_GRADES_ARY, $nameee, $t_width, $TYPE, $HEADERS_COUNT,$ShortName)
{
	//global QcCodeList;
	global $TblQcPoVarityList;
	global $TblQcTracebilityList;
	global $TblAdminPackingStyles, $DestinationCountry, $PoID;


	
	/*SELECT * FROM QcCodeList as CL
						left join $TblQcPoVarityList  as VL on CL.VarietyID=VL.VarietyID
						left join $TblAdminPackingStyles  as APS on APS.PCKID=CL.PackingStyleID
						left join $TblQcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.VarietyID='$VARIETY_ID' 
						ORDER BY `CLID` ASC";*/
if($TYPE==''){
	
	$Qry=" ";
	
}else{
	$Qry="and VL.TypeProduct='$TYPE' ";
}

//echo $Qry; exit;

	  $SqlCl = "SELECT *,CL.Brand AS BrandName,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as 
	  McsGrade_New FROM QcCodeList as CL 
left join  ScrmPoGradeData as SPGD on SPGD.ID=CL.VarietyID 
left join  ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID 
left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID 
WHERE  CL.InvId='$PoID'   and VL.ShortName='$ShortName' $Qry GROUP by DayCode,CL.PackingStyleID,CL.Brand,TracebilityID ORDER BY `CLID` ASC"; // and and SPGD.VarietyID='$VARIETY_ID' 

//echo  $SqlCl; exit;
	echo '<br>';
	$contents = '';


	$StmtCl = $iconn->query($SqlCl);

	$contents .= '<tr>
				 <!-- <td align="left"  width="' . $t_width . '%" >-->
				  <td align="left"  width="100%" >
				  <font size="8"><b> VARIETY :: </b>' . $nameee . '</font></td>
				  </tr>';


	while ($RowCl = $StmtCl->fetch_assoc()) {


        $PrintStatus = trim($RowCl['PrintStatus']); 
        $Brand = trim($RowCl['BrandName']); 

	if($Brand!='' && $PrintStatus!=0){
		 $Brand= $Brand;
	}else{
		
		$Brand='--';
	}
		$McsGrade = $RowCl['McsGrade'];
		$McsGrade=$RowCl['McsGrade_New'];
		$McsGrade = explode(',', $McsGrade);

		$ARY_GRADES = $RowCl['Grades'];
		$ARY_GRADES=$RowCl['Grades_New'];
		$ARY_GRADES = explode(',', $ARY_GRADES);

		global $size;

		$TypeProduct=$RowCl['TypeProduct']; 
		$ShortName=$RowCl['ShortName']; 

		if($RowCl['FarmerCode']!=''){
			$FarmerCode=$RowCl['FarmerCode'].' - ';
		}
		$Traceability= $FarmerCode.$RowCl['Tracebility'] ;

		$RowCl['ShortName']= getProductTypeCode($TypeProduct,$ShortName);

		$contents .= '
				<tr>
				<td align="center"  width="' . $size . '%"><font size="8">' . $RowCl['DayCode'] . '</font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="8">' . $RowCl['ShortName'] . '<br> ' .
			$RowCl['PackingStyle'] . $GLAZE . '  </font></td>';
		if ($DestinationCountry != 26) {
			$contents .= '
					<td align="center"  width="' . ($size) . '%" ><font size="7">' . $Brand . '</font></td>';
		}
		$contents .= '	<td align="center"  width="' . ($size) . '%" ><font size="7">' . $Traceability. '</font></td>
			';


		var_dump($ARY_GRADES);
		foreach ($T_GRADES_ARY as $ItemGrade) {
			//	echo $ItemGrade;
			if (in_array($ItemGrade, $ARY_GRADES, true)) {
				$Index = array_search($ItemGrade, $ARY_GRADES);
				echo $MCS = $McsGrade[$Index];
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

	//echo $sql_code_list; exit;
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


$pdf->SetFont($FONT_ARI, '', $header);

$pdf->Cell(30, 5, 'DESTINATION', 0, 0, 'L');
$pdf->Cell(0, 5, ': ' . strtoupper($FinalDestination) . ',' . $DestinationCountryName, 0, 1, 'L');

$pdf->Cell(49, 10, '', 0, 1, 'L');
$pdf->SetFont($FONT_ARI, 'B', $header);
$pdf->Cell(49, 5, 'For ' . $ExporterName, 0, 1, 'L');
$pdf->Cell(49, 15, '', 0, 1, 'L');
$pdf->SetFont($FONT_ARI, '', $header);

$pdf->Cell(49, 5, $VERIFIED_BY, 0, 1, 'L');
$pdf->Cell(49, 5, $Designation, 0, 1, 'L');


ob_end_clean();

if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $row['PoNo'].'.pdf';
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