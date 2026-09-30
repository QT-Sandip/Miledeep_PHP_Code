<?php

// ini_set('display_errors', 1);
// error_reporting(E_ALL);
session_start();

$IsHeader = 1;
$IsFooter = 1;
$QueryParms = $_GET['Q'];



include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";

$ClientConfigData = $_SESSION['ClientConfigData'];
$EnableTrackCodeInAbReport = $ClientConfigData['EnableTrackCodeInAbReport'];//anil

$pdf->SetAutoPageBreak(false, 0);
$pdf->SetMargins(10, 15, 10, true);
$pdf->SetFont($FONT_ARI, '', 8);

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$TYPE = $_GET['TypeProduct'];
$FileType = $_GET['Type'];
$RepID = $_GET['RID'];



$sql = "SELECT *,IHAB.TracebilityID as TraceId,IHAB.VerifiedBy as VerifiedBy, ExpName,ExpAddress,ProcessorID,
QLT.LbuDesignation AS VerifiedByDesignation, QLT2.LbuDesignation AS TestedByDesignation FROM QcPhAbReports as IHAB 
left JOIN QcCodeList as cl on cl.CLID=IHAB.TracebilityID
left join QcTracebilityList as TL on TL.TraceID=cl.TracebilityID 
left join ScrmPoData as PD on PD.PoID=IHAB.InvID 
LEFT JOIN AdminExporterDetails  AS AED ON PD.ProcessorID = AED.ExpID
LEFT JOIN QcLabTechnologist AS QLT ON IHAB.VerifiedByID = QLT.LUID
LEFT JOIN QcLabTechnologist AS QLT2 ON IHAB.TestedByID = QLT2.LUID
WHERE IHAB.ID='$RepID'";
// AdminExporterDetails ->ExpID,ExpName,ExpAddress

$stmt_REP = $iconn->query($sql);
$RowIhRep = $stmt_REP->fetch_array();

$TraceID = $RowIhRep['TraceId'];
$TypeCountry = $RowIhRep['TypeCountry'];
$AnalysisDate = $RowIhRep['AnalysisDate'];
$ProcessorID = $RowIhRep['ProcessorID'];

$SqlOtherData = "SELECT Brand,ShortName,McsTotal,DayCode,VerifiedBy,PL.PackingStyle FROM `QcCodeList` AS CL 
left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left JOIN ScrmPIVarietyList as VL on CL.VarietyID=VL.VarietyID WHERE CL.InvID='$PoID' AND CLID='$TraceID'";
$StmtOtherData = $iconn->query($SqlOtherData);

while ($RowOtherData = $StmtOtherData->fetch_array()) {
	$AryBrand[] = $RowOtherData['Brand'];
	$AryMcsTotal[] = $RowOtherData['McsTotal'];
	$AryDayCode[] = $RowOtherData['DayCode'];
	$AryVerifiedBy[] = $RowOtherData['VerifiedBy'];
	$AryPackStyle[] = $RowOtherData['PackingStyle'];
	$AryShortName[] = $RowOtherData['ShortName'];
}

$SqlCodeList = "SELECT * FROM `QcCodeList` where CLID='$TraceID'";
$StmtCodeList = $iconn->query($SqlCodeList);
$RowCodeList = $StmtCodeList->fetch_array();

$DayCode = $RowCodeList['DayCode'];



$Brand = implode(",", array_unique($AryBrand));
$McsTotal = implode(",", array_unique($AryMcsTotal));
$DayCodes = implode(",", array_unique($AryDayCode));
$PackStyle = implode(",", array_unique($AryPackStyle));
$VerifiedBy = implode(",", array_unique($AryVerifiedBy));
$ShortName = implode(",", array_unique($AryShortName));

$ID = $row['INVOICE_ID'];
$TypeCountry = 1;
//$SqlResult = "SELECT * FROM QcInHouseAbResult WHERE TypeCountry='$TypeCountry' and '$AnalysisDate'  >=  `FromDate` AND '$AnalysisDate'<= `ToDate` and ProcessorID='$ProcessorID' ORDER BY SortOrder asc";
$SqlResult = "SELECT *
				FROM QcInHouseAbResult q
				INNER JOIN (
					SELECT `Parameter`, MAX(`ID`) AS MaxID
					FROM QcInHouseAbResult
					WHERE TypeCountry = '$TypeCountry'
					AND `FromDate` <= '$AnalysisDate'
					AND ProcessorID = '$ProcessorID'
					GROUP BY `Parameter`
				) t ON q.`ID` = t.MaxID
				ORDER BY q.SortOrder ASC";
/* echo '<pre>'.$SqlResult;
exit; */
$StmtResult = $iconn->query($SqlResult);
$HEIGHT = 8;
$header = 10;
$sub_header = 8;
$text = 6;
$Text = 8;
$SnoSize = 5;
$Size = 40;
$Size1 = 55;

$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->SetY($pdf->GetY() - 5);
$pdf->SetFont('', 'BU', 12);
$pdf->Cell(0, 5, 'IN-HOUSE ELISA PRE HARVEST TEST REPORT', 0, 1, 'C');

$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->Cell(95, $HEIGHT, 'SAMPLE PARTICULARS:', 0, 0, 'L', 0);
$pdf->SetX(-105);
$pdf->Cell(95, $HEIGHT, 'Rec No: ' . $RowIhRep['RecordNo'], 0, 1, 'R', 0);
$pdf->SetFont($FONT_ARI, '', $sub_header);

$D_TEST_RPRT_TMSP = strtotime($row['TEST_REPORT_DATE']);
$TEST_RPRT = date("d-m-Y", $D_TEST_RPRT_TMSP);

$D_SAMPLNG_TMSP = strtotime($row['DATE_OF_SAMPLING']);
$D_SAMPLING = date("d-m-Y", $D_SAMPLNG_TMSP);

$D_ANALYSIS_TMSP = strtotime($row['DATE_OF_ANALYSIS']);
$D_ANALYSIS = date("d-m-Y", $D_ANALYSIS_TMSP);

$D_ISSUANCE_TMSP = strtotime($row['DATE_OF_ISSUANCE']);
$D_ISSUANCE = date("d-m-Y", $D_ISSUANCE_TMSP);

$SamplingTime = date("h:i A", strtotime($RowIhRep['SamplingTime']));

$OV_COUNT = 1;

$W1 = 10;
$W2 = 60;
$W3 = 120;
$H1 = 5;

$w1 = '34%';
$w3 = '6%';
$w2 = '60%';
$Sno = 0;


// Initialize row counter
$Sno = 0;
$ExplodeTraceability = explode('-', $RowIhRep['Tracebility']);
$Traceability = $ExplodeTraceability[1] . '-' . $ExplodeTraceability[2];
// First column (left side) - add the first half of the rows

$leftColumn = '<table border="1" cellspacing="0" cellpadding="3">';
$leftColumn .= ' <tr>  
		   		<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
           		<th width="' . $w1 . '" align="left"><B>Name and address of the Farmer </B></th>
                <th width="' . $w2 . '" align="left"><B>' . $RowIhRep['FarmerName'] . ',' . $RowIhRep['AddressContinued'] . '</B></th>				
           </tr>
		   <tr>  
		   		<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
           		<th width="' . $w1 . '" align="left"><B>Production Code </B></th>
                <th width="' . $w2 . '" align="left"><B>  - </B></th>				
           </tr>
		   <tr>  
		   		<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Farm ID and Pond ID (or) Farm Reg. No / Regn. authority </B></th>
                <!--<th width="' . $w2 . '" align="left"><B>' . $RowIhRep['Tracebility'] . '<br>' . $RowIhRep['RegAuthority'] . '</B></th>	-->			
                <th width="' . $w2 . '" align="left"><B>' . $Traceability . '</B></th>				
           </tr>
		   
		   <tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Name of the supplier in case the raw material received from the middleman) </B></th>
                <th width="' . $w2 . '" align="left"><B>NA </B></th>				
           </tr> 
		   	<tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Type/Species Cultured </B></th>
                <th width="' . $w2 . '" align="left"><B>LITOPENAEUS VANNAMEI</B></th>				
           </tr> 
		    <tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Quatity Received at the Establishment (KG).</B></th>
                <th width="' . $w2 . '" align="left"><B>' . $RowIhRep['ReceivedQty'] . '</B></th>				
           	</tr>
		  ';
// Add more rows for the left column here
$leftColumn .= '</table>';

// Second column (right side) - add the remaining rows
$rightColumn = '<table border="1" cellspacing="0" cellpadding="3.2">';
$rightColumn .= ' <tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Name and address of Processor</B></th>
                <th width="' . $w2 . '" align="left"><B>' . $RowIhRep['ExpName'] . '&' . $RowIhRep['ExpAddress'] . '</B></th>				
           </tr> 
<tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Approval number of Establishment</B></th>
                <th width="' . $w2 . '" align="left"><B>' . $PROCESSOR_APP_NUM . '</B></th>				
           </tr> 
		   <tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Name of the representation/Technologist who 
				has drawn the sample</B></th>
                <th width="' . $w2 . '" align="left"><B>' . $RowIhRep['LabRep'] . '</B></th>				
           	</tr> 
		   
		   
		   	<tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Date and time of sample collection <br><br> Sample Quantity </B></th>
                <th width="' . $w2 . '" align="left"><B>' . getFormatedDate($RowIhRep['SamplingDate']) . ' & ' . $SamplingTime . '<br><br><br>' . $RowIhRep['SampleQuantity'] . '</B></th>				
           </tr> 
		   <tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Sample ID/Code </B></th>
                <th width="' . $w2 . '" align="left"><B>' . $RowIhRep['SampleID'] . '</B></th>				
           </tr> 
		   <tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Date of Analysis </B></th>
                <th width="' . $w2 . '" align="left"><B>' . getFormatedDate($RowIhRep['AnalysisDate']) . '</B></th>				
           </tr> 
		   
		   	<tr>  
		    	<th width="' . $w3 . '" align="left"><B>' . ++$Sno . '</B></th>
				<th width="' . $w1 . '" align="left"><B>Date of Issuance </B></th>
                <th width="' . $w2 . '" align="left"><B>' . getFormatedDate($RowIhRep['DateIssuence']) . '</B></th>				
           </tr> ';
// Add more rows for the right column here
$rightColumn .= '</table>';

// Display the two tables side-by-side in a single row
$FARMER_NAME = '<table width="100%"><tr>
                    <td width="50%">' . $leftColumn . '</td>
                    <td width="50%">' . $rightColumn . '</td>
                  </tr></table>';

// Write the combined table to the PDF


$pdf->writeHTML($FARMER_NAME);

/* $CAP = '0.031';
$AHD = '0.110';
$AMOZ = '0.191';
$AOZ = '0.132';
$SEM = '0.247';
$DNSH = ''; */
if(!empty($EnableTrackCodeInAbReport) && !empty($ProcessorID) && !empty($AnalysisDate)){

	$SqlHarvestDt = "SELECT HarvestDt FROM `QcCodeList` where InvID='$PoID' ORDER BY HarvestDt DESC LIMIT 1";
	$StmtHarvestDt = $iconn->query($SqlHarvestDt);
	$HarvestDt = $StmtHarvestDt->fetch_assoc()['HarvestDt'];
	
	$Sql = "SELECT 
				REPLACE(CAP, ' PPB', '') CAP,
				REPLACE(AHD, ' PPB', '') AHD,
				REPLACE(AMOZ, ' PPB', '') AMOZ,
				REPLACE(AOZ, ' PPB', '') AOZ,
				REPLACE(SEM, ' PPB', '') SEM,
				DNSH
	 		FROM ABReportParameterCutOffValues WHERE FIND_IN_SET('$ProcessorID',ProcessorIDs) > 0 
	  		AND EffectedFromForElisaReport <= '$AnalysisDate'
	   		ORDER BY ID DESC LIMIT 1";
	$Stmt=$iconn->query($Sql);
	$Results = $Stmt->fetch_assoc();
	$rowCount = $Stmt->num_rows;
	if($rowCount > 0 && $HarvestDt){
		$CAP = $Results['CAP'];
		$AHD = $Results['AHD'];
		$AMOZ = $Results['AMOZ'];
		$AOZ = $Results['AOZ'];
		$SEM = $Results['SEM'];
	}
}
$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->Cell(180, $HEIGHT, 'TEST RESULT :', 0, 1, 'L');
$pdf->SetFont($FONT_ARI, 'B', $sub_header);

$Sno = 0;
$content = '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="center"  width="' . ($SnoSize) . '%"><font size="' . $Text . '">S.NO</font></th>
           		<th  align="center"  width="25%"><font size="' . $Text . '">Parameters Tested</font></th>
                <th  align="center" width="15%"><font size="' . $Text . '">CCB (µg/kg)</font></th>
                <th  align="center" width="15%"><font size="' . $Text . '">Cut Off Value(µg/kg)</font></th>
                <th  align="center" width="15%"><font size="' . $Text . '">Test Result *</font></th>
                <th  align="center" width="25%"><font size="' . $Text . '">Analytical Method/Equipment used</font></th>
				</tr>';
while ($RowResult = $StmtResult->fetch_array()) {
	$param = $RowResult['Parameter'];

    $RowResult['Detection'] =
        stripos($param, 'CAP')  !== false && $CAP  ? $CAP :
        (stripos($param, 'AHD') !== false && $AHD ? $AHD :
        (stripos($param, 'AMOZ') !== false && $AMOZ ? $AMOZ :
        (stripos($param, 'AOZ')  !== false && $AOZ ? $AOZ :
        (stripos($param, 'SEM')  !== false && $SEM ? $SEM :
        (stripos($param, 'DNSH') !== false && $DNSH ? $DNSH :
        $RowResult['Detection'])))));
	//echo '<pre>';print_r($RowResult['Parameter']);die;
	$content .= '<tr>  
           		<th  align="center"  width="' . ($SnoSize) . '%"><font size="' . $Text . '">' . ++$Sno . '</font></th>
           		<th  align="center"  width="25%"><font size="' . $Text . '">' . $RowResult['Parameter'] . '</font></th>
                <th  align="center" width="15%"><font size="' . $Text . '">' . $RowResult['CCBLimits'] . '</font></th>
                <th  align="center" width="15%"><font size="' . $Text . '">' . $RowResult['Detection'] . '</font></th>
                <th  align="center" width="15%"><font size="' . $Text . '">' . $RowResult['Result'] . '</font></th>
                <th  align="center" width="25%"><font size="' . $Text . '">' . $RowResult['AnalyticMethod'] . '</font></th>
				</tr>';
}
$content .= '</table>';
$pdf->writeHtml($content);

// $pdf->Ln();

$HEIGHT = 4;
$pdf->Cell(130, $HEIGHT, '1.Test results are based on ELISA screening. ', 0, 1);
$pdf->Cell(130, $HEIGHT, '2.Test result should be reported as Screened positive or negative.', 0, 1);
$pdf->Cell(130, $HEIGHT, '3.Values obtained below detection limit can be reported as negative and above detection limit as positive.', 0, 1);
$pdf->Cell(130, $HEIGHT, '4.Actual values obtained above detection limit as positive shall be reported.', 0, 1);

// $pdf->Ln();

$pdf->SetFont($FONT_ARI, 'B', $sub_header);

$pdf->Cell(90, 10, '', 'LTR', 0, 'L', 0);
$pdf->Cell(90, 10, '', 'LTR', 1, 'L', 0);

$pdf->Cell(90, 5, '' . $RowIhRep['TestedBy'] . ' - ' . $RowIhRep['TestedByDesignation'], 'LRB', 0, 'L', 0);
$pdf->Cell(90, 5, '' . $RowIhRep['VerifiedBy'] . ' - ' . $RowIhRep['VerifiedByDesignation'], 'LRB', 1, 'L', 0);  // cell with left and right borders

$pdf->Cell(90, 5, "Authorized by (Name, designation & signature of the", 'LTR', 0, 'L', 0);
$pdf->Cell(90, 5, 'Verified by (Name/Designation/Signature)', 'LTR', 1, 'L', 0);  // cell with left and right borders

$pdf->Cell(90, 5, " approved technologist)", 'LRB', 0, 'L', 0);
$pdf->Cell(90, 5, '', 'LRB', 1, 'L', 0);  // cell with left and right borders

// $pdf->Cell(130, $HEIGHT, 'The test report refers only to the particular samples submitted for testing.', 0, 1);
$pdf->MultiCell(0, $HEIGHT, "The traceability module is currently not working we will send the Elisa data on offline mode. Once traceability module available we will send the Elisa data on module.\n\nThe test report refers only to the particular samples submitted for testing.", 0, 'L');



//$pdf->Cell( 189, 40, $pdf->Image($footer, null, 0, 180), 0, 0, 'L' );

$pdf->Ln();
//$pdf->Cell(12,5, $pdf->Image($footer, 10,265, 190,0),0,0,'C',0);

ob_end_clean();

if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $RowIhRep['PoNo'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName . '.pdf', 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName . '.pdf';
	$pdf->Output($FileName, $FileType);
}

