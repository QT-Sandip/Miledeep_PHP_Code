<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
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


$SqlCodeList = "SELECT * FROM QcCodeList WHERE InvID='$PoID' ORDER BY `CLID` ASC";
$stmt_code = $iconn->query($SqlCodeList);
$varity_ids = array();

while ($row_Code = $stmt_code->fetch_array()) {

	$FULL_NAME = $row_Code['VarietyID'];
	$VARIFIED_BY = $row_Code['VerifiedBy'];
	$GRADES = $row_Code['Grades'];

	if (in_array($FULL_NAME, $varity_ids)) {
	} else {
		$FULL_NAME;
		$varity_ids[] = $FULL_NAME;
	}


}



$sql = "SELECT *,PD.ExporterID FROM ScrmPoData  as PD
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID 
left join AdminExporterDetails as AED on AED.ExpID=PD.ProcessorID
WHERE `PoID`='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();
$ProcessorID = $row['ProcessorID'];
$ProcessorName = $row['ExpName'];
$PackerAddress = $row['PackerAddress'];
/*
if ($ProcessorID == 1) {
	$Unit = 'UNIT- I';
} elseif ($ProcessorID == 3) {
	$Unit = 'UNIT- II';

}
*/

$T_GRADES_ARY = array();
$FARM_GRADE = $row_Code['GRADES'];


$SqlVerified = "SELECT VerifiedBy, LbuDesignation FROM `QcCoveringLetter` as CL 
LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
 WHERE InvID='$PoID' ";

$StmtVerified = $iconn->query($SqlVerified);
$RowVerified = $StmtVerified->fetch_array();



$from_title = date('M d, Y');
$to_title = date('M d, Y');





$PO_TIME_STAMP = strtotime($row['PoDate']);
$PO_DATE = date("d-m-Y", $PO_TIME_STAMP);


// $pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('MPEDACodeList:  - ' . $PO_DATE);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetHeaderMargin(70);
$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT, 20);
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
$pdf->Cell(0, 5, $PackerAddress, 0, 1, 'C');
$pdf->SetFont($FONT_ARI, 'BU', 14);
$pdf->Cell(0, 14, 'CODE LIST', 0, 1, 'C');

$pdf->SetFont($FONT_ARI, '', $header);

$size = count($T_GRADES_ARY) + 5;
$size = 100 / $size;

//$t_width = getRowWidth($T_GRADES_ARY);
$t_width = $t_width + (4 * $size + 10);
$size = 15;


$content = getPoDetails($row, $iconn);
$pdf->writeHTML($content, false);
$pdf->Cell(0, 5, '', 0, 1, 'L');
$header_size = 9;

$content = '';
$content .= '<table border="1" cellspacing="0" cellpadding="0">  
				<tr align="center">  
           		<th  valign="ceter"  width="' . ($size + 10) . '%"><font size="' . $header_size . '"><br><b>PRODUCTION CODE</b></font></th>
                <th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>PACKING</b></font></th>
                <th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>VARIETY</b></font></th>
				<th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>POND ID/TRACEABILITY</b></font></th> 
				<th  align="center" width="' . ($size) . '%"><font size="' . $header_size . '"><br><b>GRADE</b></font></th>';
$content .= '<th  align="center" width="' . ($size + 0) . '%"><font size="' . $header_size . '"><br><b>NO OF MASTER CARTONS</b></font></th>
				</tr>';

//print_r($varity_ids);
foreach ($varity_ids as $value) {

	$nameee = getVarityName($value, $iconn);
	$size = count($T_GRADES_ARY) + 2;
	$size = 100 / $size;
	generateRow($value, $iconn, $T_GRADES_ARY, $nameee, $t_width);
	$content .= generateRow($value, $iconn, $T_GRADES_ARY, $nameee, $t_width);
}

$size = count($T_GRADES_ARY) + 5;
$size = 100 / $size;
$t_width = (3 * $size + 10);
$t_width = 85;

$content .= '<tr>
			<th  align="RIGHT"  width="' . $t_width . '%"><font size="9"><B> TOTAL          </B></font></th>
			';
$content .= getTotals($PoID, $iconn, $T_GRADES_ARY);
$content .= '</tr>';
$content .= '</table>';
$pdf->writeHTML($content, false);

$pdf->Ln();

$COUNT = 1;



$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(10, 5, '', 0, 1, '');
$pdf->Cell(90, 5, '', 0, 1, 'C');
$pdf->Cell(90, 5, '', 0, 1, 'C');
$pdf->Cell(90, 5, 'Signature' , 0, 1, 'L');
$pdf->Cell(90, 5, 'NAME : ' . $RowVerified['VerifiedBy'], 0, 1, 'L');

$pdf->Cell(90, 5, 'DESIGNATION: ' . $RowVerified['LbuDesignation'], 0, 0, 'L');
$pdf->Cell(150, 5, 'COMPANY SEAL:', 0, 1, 'R');
ob_end_clean();
if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $row['InvNumFull'];
	$FileName = getFormatedFileName($FileName);
	$FileName = $FileName.'.pdf';
	$pdf->Output($FileName, 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName.'.pdf';
	$pdf->Output($FileName, $FileType);

}




function generateRow($PoGradeID)
{
global $iconn;
$contents ='';

	 $sql_code_list = "SELECT SPVL.ShortName,SPVL.TypeProduct,CL.DayCode,CL.Grades,CL.McsTotal,PL.PackingStyle,
	 TL.Tracebility,TL.FarmerCode FROM QcCodeList as CL 
	left join ScrmPoGradeData as SPGD on SPGD.ID=CL.PoGradeID 
	left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
	left join AdminPackingStyles as PL on PL.PCKID=CL.PackingStyleID 
	left join QcTracebilityList as TL on TL.TraceID=CL.TracebilityID WHERE PoGradeID='$PoGradeID' ORDER BY CL.CLID ASC"; 
	$stmt_code_list = $iconn->query($sql_code_list);

	while ($row = $stmt_code_list->fetch_assoc()) {
		
		$size = 100 / 6;
		$size = 15;
		$header_size = 9;
		$date = getDayCodeDate($row['DayCode']);
		 $TypeProduct=$row['TypeProduct']; 
		$ShortName=$row['ShortName']; 
		if($row['FarmerCode']!=''){
			$FarmerCode=$row['FarmerCode'].' - ';
		}
		$Traceability= $FarmerCode.$row['Tracebility'] ;

		 $row['ShortName']= getProductTypeCode($TypeProduct,$ShortName); 

		$contents .= '
				<tr>
				
				<!-- <td align="ceter"  width="' . ($size + 10) . '%"><font size="' . $header_size . '"><b>' . $row['DayCode'] . ' (' . $date . ')</b></font></td>-->
				<td align="ceter"  width="' . ($size + 10) . '%"><font size="' . $header_size . '"><b>' . $row['DayCode'] . ' </b></font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $row['PackingStyle'] . '  </b></font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $row['ShortName'] . '</b></font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $Traceability . '</b></font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $row['Grades'] . '</b></font></td>
				<td align="center"  width="' . ($size) . '%" ><font size="' . $header_size . '"><b>' . $row['McsTotal'] . '</b></font></td>
			';

		$contents .= '</tr>';

	}
	return $contents;
}
?>