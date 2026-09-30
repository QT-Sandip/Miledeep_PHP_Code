<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
$Processor = $_GET['Processor'];

$LabNames = $_GET['LabNames'];
$FromDate = $_GET['FromDate'];
$ToDate = $_GET['ToDate'];
$Type = $_GET['Type'];
$Invoice = $_GET['Invoice'];

$DateFilter = " BETWEEN '$FromDate' AND '$ToDate'";
$Query = "";
if ($LabNames != '') {
	$Query .= " AND TestingLab = '$LabNames'";
}
if ($Processor != '') {
	$Query .= " AND ProcessorID = '$Processor'";
}
if ($Invoice != '') {
	$Query .= " AND QSLE.InvID  = '$Invoice' ";
}
$ClientName = $_SESSION['ClientName'];


$table_data = getP_TABLE($iconn, $Query, $DateFilter);

header("Content-Type: application/xls");
header('Content-Disposition: attachment;filename=payment-statement-report.xls');
// Fix for crappy IE bug in download.
header("Pragma: ");
header("Cache-Control: ");
echo $table_data;
function getP_TABLE($iconn, $Query, $DateFilter, $Type)
{
	global $FromDate,$ToDate;
	$filerColumn = " QSLE.Date  ";
	//if($Type == 1){
		$filerColumn = " QSLE.CompletedDate  ";
	//}
// 	$sqlFields = "QSLE.Date, SPD.PoNo, SCD.ConsigneeName, GROUP_CONCAT(DISTINCT SPVL.ProductDescription) AS ProductDescription, 
// 	GROUP_CONCAT(DISTINCT APS.PackingStyle) AS PackingStyle, 
// 	GROUP_CONCAT(distinct QCL.DayCode) AS DayCode,SPGD.Brand, GROUP_CONCAT(DISTINCT QSLE.AnalysisReq) AS AnalysisReq, SPD.InvNumFull,SPD.InvDate,QSLE.TestingLab,
// 	QSLE.ReportNumber,QSLE.InvoiceDate,QSLE.InvoiceNumber,QSLE.Status,QSLE.InvAmount,QSLE.ReportDate,QSLE.SampleNumber,
//    GROUP_CONCAT(DISTINCT CASE 
//     WHEN TRIM(QSLE.InvoicercvedBY) <> '' THEN QSLE.InvoicercvedBY 
//     ELSE NULL 
// END) AS InvoicercvedBY,
// GROUP_CONCAT(DISTINCT CASE 
//     WHEN TRIM(QSLE.VerifiedBy) <> '' THEN QSLE.VerifiedBy 
//     ELSE NULL 
// END) AS VerifiedBy,
// 	QSLE.LabInvoiceNumber AS LabInvoiceNumber,QSLE.Amount AS Amount,QSLE.PreparedBy,QSLE.AccountantName";

// 	$sqlSelect = "SELECT $sqlFields FROM QcSamplingLettersExt AS QSLE
// 	LEFT JOIN ScrmPoData AS SPD ON QSLE.InvID = SPD.PoID
// 	LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
// 	LEFT JOIN ScrmPoGradeData AS SPGD ON SPD.PoID = SPGD.InvId
// 	LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID = SPVL.VarietyID
// 	LEFT JOIN AdminPackingStyles AS APS ON SPGD.PackingStyleID = APS.PCKID
// 	LEFT JOIN QcCodeList AS QCL ON SPD.PoID = QCL.InvID
// 	LEFT JOIN ScrmCustomInvoices AS SCI ON QSLE.InvID = SCI.PiID
// 	WHERE $filerColumn $DateFilter $Query GROUP BY QSLE.InvID";


$sqlFields = "  QSLE.Date, SPD.PoNo, SCD.ConsigneeName, PD.ProductDescription, PS.PackingStyle, DC.DayCode, BR.Brand,
    QSLE.AnalysisReq,  SPD.InvNumFull,   SPD.InvDate,  QSLE.TestingLab,  QSLE.ReportNumber, QSLE.InvoiceDate,
    QSLE.InvoiceNumber,   QSLE.Status,   QSLE.InvAmount,  QSLE.ReportDate,  QSLE.SampleNumber,QSLE.InvoicercvedBY,
    QSLE.VerifiedBy,   QSLE.LabInvoiceNumber,   QSLE.Amount,   QSLE.PreparedBy,  QSLE.AccountantName";

$sqlSelect = "SELECT $sqlFields FROM QcSamplingLettersExt AS QSLE
LEFT JOIN ScrmPoData AS SPD  ON QSLE.InvID = SPD.PoID
LEFT JOIN ScrmConsigneeDetails AS SCD  ON SPD.ConsigneeID = SCD.ConID
LEFT JOIN ( SELECT SPGD.InvId, GROUP_CONCAT(DISTINCT SPVL.ProductDescription) AS ProductDescription FROM ScrmPoGradeData SPGD
LEFT JOIN ScrmPIVarietyList SPVL ON SPGD.VarietyID = SPVL.VarietyID GROUP BY SPGD.InvId) PD ON PD.InvId = QSLE.InvID
LEFT JOIN (  SELECT SPGD.InvId, GROUP_CONCAT(DISTINCT APS.PackingStyle) AS PackingStyle FROM ScrmPoGradeData SPGD
LEFT JOIN AdminPackingStyles APS ON SPGD.PackingStyleID = APS.PCKID GROUP BY SPGD.InvId) PS ON PS.InvId = QSLE.InvID
LEFT JOIN (  SELECT InvID, GROUP_CONCAT(DISTINCT DayCode) AS DayCode FROM QcCodeList GROUP BY InvID) DC ON DC.InvID = QSLE.InvID
LEFT JOIN ( SELECT InvId, GROUP_CONCAT(DISTINCT Brand) AS Brand  FROM ScrmPoGradeData   GROUP BY InvId) BR ON BR.InvId = QSLE.InvID
LEFT JOIN ScrmCustomInvoices AS SCI  ON QSLE.InvID = SCI.PiID
WHERE $filerColumn $DateFilter $Query";

	$stmtSelect = $iconn->query($sqlSelect);

	$content = '';
	$HeaderFromDate = date("d/m/Y", strtotime($FromDate));
	$HeaderToDate = date("d/m/Y", strtotime($ToDate));
	$HeaderDates = '';
	if(!empty($FromDate) && !empty($ToDate)){
		$HeaderDates = '('.$HeaderFromDate.' - '.$HeaderToDate.')';
	}elseif(!empty($FromDate)){
		$HeaderDates = '('.$HeaderFromDate.')';
	}elseif(!empty($ToDate)){
		$HeaderDates = '('.$HeaderToDate.')';
	}
	$content .= '
	 <!-- <h3 align="CENTER" width="75%">EXTERNAL LAB REGISTER MONTHLY REPORT</h3> -->
	 <h3 align="CENTER" width="75%">External lab invoice statement '.$HeaderDates.')</h3>
	 <table border="1" cellspacing="0" cellpadding="3">	
					<tr>  
						<th  align="center">S NO</font></th>
						<th  align="center">SAMPLING DATE</font></th>
						<th  align="center">CUSTOM INVOICE NO</font></th>
						<th  align="center">PO NO</font></th>
						<th  align="center">CONSIGNEE</font></th>
						<th  align="center">SAMPLE DESCRIPTION & PACKING</font></th>
						<th  align="center">CODES</font></th>
						<th  align="center">BRAND</font></th>
						<th  align="center">TESTING PARAMETERS</font></th>
						<th  align="center">TEST REPORT NO</font></th>						
						<th  align="center">AMOUNT</font></th>
						<th  align="center">LAB</font></th>
						<th  align="center">TAX INVOICE NUMBER/LAB INVOICE NUMBER</font></th>
						<!--<th  align="center">CHECKED BY</font></th>-->
						<th  align="center">INVOICE RECEIVED BY</font></th>
					</tr>';
	$C = 0;
	$SNO = 0;
	$PreparedByArr = $AccountantNameArr = [];$VerifiedBy = '';
	while ($dataRow = $stmtSelect->fetch_array()) {

		if($dataRow['PreparedBy']){			
			$PreparedByArr[] = $dataRow['PreparedBy'];
		}
		if($dataRow['AccountantName']){			
			$AccountantNameArr[] = $dataRow['AccountantName'];
		}

		$Date = $dataRow['Date'] ? date('d-m-Y', strtotime($dataRow['Date'])) : '--';
		$ReportDate = $dataRow['ReportDate'] ? date('d-m-Y', strtotime($dataRow['ReportDate'])) : '';
		$InvoiceDate = $dataRow['InvoiceDate'] ? date('d-m-Y', strtotime($dataRow['InvoiceDate'])) : '';
		$content .= '
				<tr>  
					<th  align="center">' . ++$SNO . '</font></th>
					<th  align="center">' . $Date . '</font></th>
					<th  align="center">' . $dataRow['InvNumFull'] . '<br>' . $InvoiceDate . '</font></th>
					<th  align="center">' . $dataRow['PoNo'] . '</font></th>
					<th  align="center">' . $dataRow['ConsigneeName'] . '</font></th>
					<th  align="center">' . $dataRow['ProductDescription'] . '<br>' . $dataRow['PackingStyle'] . '</font></th>
					<th  align="center">' . $dataRow['DayCode'] . '</font></th>
					<th  align="center">' . $dataRow['Brand'] . '</font></th>
					<th  align="center">' . $dataRow['AnalysisReq'] . '</font></th>
					<th  align="center">' . $dataRow['SampleNumber'] . '<br>' . $ReportDate . '</font></th>
					<th  align="center">' . $dataRow['Amount'] . '</font></th>
					<th  align="center">' . $dataRow['TestingLab'] . '</font></th>
					<th  align="center">' . $dataRow['LabInvoiceNumber'] . '</font></th>
					<!--<th  align="center">' . $dataRow['VerifiedBy'] . '</font></th>-->
					<th  align="center">' . $dataRow['InvoicercvedBY'] . '</font></th>
				</tr>';
				if($VerifiedBy == ''){
					$VerifiedBy = $dataRow['VerifiedBy'];
				}
		$C++;
	}

	// $content .= '<tr>
	// 				<th  align="center" width="100%"><font size="9"></font></th>
	// 			</tr>
	// 			<tr>
	// 				<th  align="center" width="50%"><font size="9">Total Letters</font></th>
	// 				<th  align="center" width="50%"><font size="9">' . $C . '</font></th>
	//             </tr>';

	
	$AdditionalStr = '';
	$td = 80;$colspan = 10;
	if(!empty($PreparedByArr)){
		$td -= empty($AccountantNameArr) ? 80 : 40;
		$colspan -= empty($AccountantNameArr) ? 10 : 5;
		$AdditionalStr .= '<td colspan="'.(empty($AccountantNameArr) ? 10 : 5).'" style="width:'.(empty($AccountantNameArr) ? 80 : 40).'%" ><font><b>Prepared By :</b> '.implode(',', array_unique($PreparedByArr)).'</font></td>';
	}
	if(!empty($AccountantNameArr)){
		$td -= empty($PreparedByArr) ? 80 : 40;
		$colspan -= empty($PreparedByArr) ? 10 : 5;
		$AdditionalStr .= '<td colspan="'.(empty($PreparedByArr) ? 10 : 5).'" style="width:'.(empty($PreparedByArr) ? 80 : 40).'%"  ><font ><b>Accountant Name :</b> '.implode(',', array_unique($AccountantNameArr)).'</font></td>';
	}
	$content .= '<tr>'.$AdditionalStr.(
			$td != 0 || $td == 80?'<td style="width:'.$td.'%" colspan="'.$colspan.'"></td>':''
		).'<td style="width:20%"  colspan="4"><font ><b>Verified By :</b> '. $VerifiedBy .'</font></td></tr>';
	
	$content .= '</table>';
	//ECHO $content;die;
	return $content;
}
