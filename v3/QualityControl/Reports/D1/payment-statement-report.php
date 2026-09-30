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

class MYPDF extends TCPDF
{
	public function Footer()
	{
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
	}

	public function Header()
	{

		$image_file = K_PATH_IMAGES . 'jme_logo.jpg';
		$this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
		$this->SetFont('helvetica', 'B', 15);
		$this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	}
}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('EXTERNAL LAB REGISTER Monthly List : ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(45);
$pdf->SetHeaderMargin(15);
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 45);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 40);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage(L);

$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

$pdf->Cell(0, 5, $ClientName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');

//$pdf->Cell(0, 5, 'EXTERNAL LAB REGISTER MONTHLY REPORT', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
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
$pdf->Cell(0, 5, 'External lab invoice statement '.$HeaderDates, 0, 1, 'C', 0, '', 0, false, 'T', 'M');


$table_data = getP_TABLE($iconn, $Query, $DateFilter, $Type);
$pdf->writeHTML($table_data);

$file_name = 'LabRegister' . $row['PO_NUM'] . '.pdf';
ob_end_clean();
$pdf->Output($file_name, 'I');

function getP_TABLE($iconn, $Query, $DateFilter, $Type)
{		
	$filerColumn = " QSLE.Date  ";
	//if($Type == 1){
		$filerColumn = " QSLE.CompletedDate  ";
	//}
	$VerifiedBy = "";
// 	$sqlFields = "QSLE.Date, SPD.PoNo, SCD.ConsigneeName, GROUP_CONCAT(DISTINCT SPVL.ProductDescription) AS ProductDescription, 
// 	GROUP_CONCAT(DISTINCT APS.PackingStyle) AS PackingStyle, 
// 	GROUP_CONCAT(distinct QCL.DayCode) AS DayCode,SPGD.Brand, QSLE.AnalysisReq AS AnalysisReq, SPD.InvNumFull,
// 	SPD.InvDate,QSLE.TestingLab,QCL.VerifiedBy AS CheckedBy,
// 	QSLE.ReportNumber,QSLE.InvoiceDate,QSLE.InvoiceNumber,QSLE.Status,QSLE.InvAmount,QSLE.ReportDate,QSLE.SampleNumber, 
//    GROUP_CONCAT(DISTINCT CASE 
//     WHEN TRIM(QSLE.InvoicercvedBY) <> '' THEN QSLE.InvoicercvedBY  ELSE NULL END) AS InvoicercvedBY,
//     GROUP_CONCAT(DISTINCT CASE  WHEN TRIM(QSLE.VerifiedBy) <> '' THEN QSLE.VerifiedBy   ELSE NULL END) AS VerifiedBy,
// 	QSLE.LabInvoiceNumber AS LabInvoiceNumber,QSLE.Amount AS Amount,QSLE.PreparedBy,QSLE.AccountantName";

// 	$sqlSelect = "SELECT $sqlFields FROM QcSamplingLettersExt AS QSLE
// 	LEFT JOIN ScrmPoData AS SPD ON QSLE.InvID = SPD.PoID
// 	LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
// 	LEFT JOIN ScrmPoGradeData AS SPGD ON SPD.PoID = SPGD.InvId
// 	LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID = SPVL.VarietyID
// 	LEFT JOIN AdminPackingStyles AS APS ON SPGD.PackingStyleID = APS.PCKID
// 	LEFT JOIN QcCodeList AS QCL ON SPD.PoID = QCL.InvID
// 	LEFT JOIN ScrmCustomInvoices AS SCI ON QSLE.InvID = SCI.PiID
// 	WHERE $filerColumn $DateFilter $Query  GROUP BY QSLE.InvID";


$sqlFields = "  QSLE.Date, SPD.PoNo, SCD.ConsigneeName, PD.ProductDescription, PS.PackingStyle, QCL.DayCode, BR.Brand,
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
LEFT JOIN (  SELECT InvID, GROUP_CONCAT(DISTINCT DayCode) AS DayCode FROM QcCodeList GROUP BY InvID) QCL ON QCL.InvID = QSLE.InvID
LEFT JOIN ( SELECT InvId, GROUP_CONCAT(DISTINCT Brand) AS Brand  FROM ScrmPoGradeData   GROUP BY InvId) BR ON BR.InvId = QSLE.InvID
LEFT JOIN ScrmCustomInvoices AS SCI  ON QSLE.InvID = SCI.PiID
WHERE $filerColumn $DateFilter $Query ";


	// echo print_r($sqlSelect);
	// exit;
	$stmtSelect = $iconn->query($sqlSelect);

	
	$content = '';
	$content .= '<table border="1" cellspacing="0" cellpadding="3">	
					<tr>  
						<th  align="center" width="3%"><font size="8">S NO</font></th>
						<th  align="center" width="7%"><font size="8">SAMPLING DATE</font></th>						
						<th  align="center" width="7%"><font size="8">CUSTOM INVOICE NO</font></th>
						<th  align="center" width="5%"><font size="8">PO NO</font></th>
						<th  align="center" width="8%"><font size="8">CONSIGNEE</font></th>
						<th  align="center" width="12%"><font size="8">SAMPLE DESCRIPTION & PACKING</font></th>
						<th  align="center" width="5%"><font size="8">CODES</font></th>
						<th  align="center" width="5%"><font size="8">BRAND</font></th>
						<th  align="center" width="15%"><font size="8">TESTING PARAMETERS</font></th>
						<th  align="center" width="6%"><font size="8">TEST REPORT NO</font></th>
						<th  align="center" width="6S%"><font size="8">AMOUNT</font></th>
						<th  align="center" width="7%"><font size="8">LAB</font></th>
						<th  align="center" width="7%"><font size="8">TAX INVOICE NUMBER/LAB INVOICE NUMBER</font></th>
						<!-- <th  align="center" width="5%"><font size="8">CHECKED BY</font></th> -->
						<th  align="center" width="6%"><font size="8">INVOICE RECEIVED BY</font></th>
						
						

						
						
					</tr>';
	$C = 0;
	$SNO = 0;
	$PreparedByArr = $AccountantNameArr = [];
	while ($dataRow = $stmtSelect->fetch_array()) {
		if($dataRow['PreparedBy']){			
			$PreparedByArr[] = $dataRow['PreparedBy'];
		}
		if($dataRow['AccountantName']){			
			$AccountantNameArr[] = $dataRow['AccountantName'];
		}
		// 	echo print_r($dataRow);
		// exit;
		$Date = $dataRow['Date'] ? date('d-m-Y', strtotime($dataRow['Date'])) : '--';
		$ReportDate = $dataRow['ReportDate'] ? date('d-m-Y', strtotime($dataRow['ReportDate'])) : '';
		$InvoiceDate = $dataRow['InvoiceDate'] ? date('d-m-Y', strtotime($dataRow['InvoiceDate'])) : '';
		$content .= '
				<tr>  
					<th  align="center" width="3%"><font size="8">' . ++$SNO . '</font></th>
					<th  align="center" width="7%"><font size="8">' . $Date . '</font></th>
					<th  align="center" width="7%"><font size="8">' . $dataRow['InvNumFull'] . '<br>' . $InvoiceDate . '</font></th>
					<th  align="center" width="5%"><font size="8">' . $dataRow['PoNo'] . '</font></th>
					<th  align="center" width="8%"><font size="8">' . $dataRow['ConsigneeName'] . '</font></th>
					<th  align="center" width="12%"><font size="8">' . $dataRow['ProductDescription'] . '<br>' . $dataRow['PackingStyle'] . '</font></th>
					<th  align="center" width="5%"><font size="8">' . $dataRow['DayCode'] . '</font></th>
					<th  align="center" width="5%"><font size="8">' . $dataRow['Brand'] . '</font></th>
					
					
					
					 <th align="left" width="15%">
						<font size="8">' . (strlen($dataRow['AnalysisReq']) > 30 	? substr($dataRow['AnalysisReq'], 0, 30) . '... [See more]' : $dataRow['AnalysisReq']) . '</font>
					</th> 



					
					<th  align="center" width="6%"><font size="8">' . $dataRow['SampleNumber'] . '<br>' . $ReportDate . '</font></th>
					<th  align="center" width="6%"><font size="8">' . $dataRow['Amount'] . '</font></th>
					<th  align="center" width="7%"><font size="8">' . $dataRow['TestingLab'] . '</font></th>
					<th  align="center" width="7%"><font size="8">' . $dataRow['LabInvoiceNumber'] . '</font></th>
					<!-- <th  align="center" width="5%"><font size="8">' . $dataRow['VerifiedBy'] . '</font></th> -->
					<th  align="center" width="6%"><font size="8">' . $dataRow['InvoicercvedBY'] . '</font></th>
					
				</tr>';
				if($VerifiedBy == ''){
					$VerifiedBy = $dataRow['VerifiedBy'];
				}
		$C++;
	}
	$AdditionalStr = '';
	$td = 80;
	if(!empty($PreparedByArr)){
		$td -= empty($AccountantNameArr) ? 80 : 40;
		$AdditionalStr .= '<td width="'.(empty($AccountantNameArr) ? 80 : 40).'%"><font size="9"><b>Prepared By :</b> '.implode(',', array_unique($PreparedByArr)).'</font><br><br></td>';
	}
	if(!empty($AccountantNameArr)){
		$td -= empty($PreparedByArr) ? 80 : 40;
		$AdditionalStr .= '<td width="'.(empty($PreparedByArr) ? 80 : 40).'%"><font size="9"><b>Accountant Name :</b> '.implode(',', array_unique($AccountantNameArr)).'</font><br><br></td>';
	}

	// $content .= '<tr>
	// 				<th  align="center" width="100%"><font size="9"></font></th>
	// 			</tr>
	// 			<tr>
	// 				<th  align="center" width="50%"><font size="9">Total Letters</font></th>
	// 				<th  align="center" width="50%"><font size="9">' . $C . '</font></th>
	//             </tr>';
	$content .= '</table>';

$content .= '<br><br>
<table border="0" cellspacing="0" cellpadding="3" width="100%">
    <tr>'.$AdditionalStr.(
			$td != 0 || $td == 80?'<td width="'.$td.'%"></td>':''
		).'<td align="center" width="25%">
            <font size="9"><b>Verified By: </b>' . $VerifiedBy . '</font><br><br><br>
			
        </td>
    </tr>
</table>';

	
	return $content;
}
