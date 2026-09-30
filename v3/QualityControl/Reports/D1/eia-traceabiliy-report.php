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
include "PrintHeaderInc.php";

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$TYPE = $_GET['TypeProduct'];
$FileType = $_GET['Type'];
$SAMP_ID = $_GET['RID'];

$SqlSamplingLtr = "SELECT *
FROM QcSamplingLettersExt as SL
left join ScrmPoData as PD on PD.PoID=SL.InvID
left join ScrmConsigneeDetails as CD on PD.ConsigneeID=CD.ConID WHERE SL.SMPLID='$SAMP_ID'";
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

$HEIGHT = 8;
$WIDHT_1 = 'Arial';

$header = 12;
$sub_header = 8;
$text = 6;

$TEST_TMSP = strtotime($row['DATE']);
$TEST_DATE = date("d-m-Y", $TEST_TMSP);

$pdf->SetFont($FONT_ARI, 'BU', $header);
// $pdf->Ln();
// $pdf->Cell(0, 15, 'TRACEABILITY REPORT', 0, 1, 'C');

// Move heading up a bit (reduce space above)
$pdf->SetY($pdf->GetY() - 5); 

// Add the heading
$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->Cell(0, 8, 'TRACEABILITY REPORT', 0, 1, 'C');

// Add extra space *below* heading
$pdf->Ln(1); 


$pdf->SetFont($FONT_ARI, '', $sub_header);

$TestingLab = $RowSampling['TestingLab'];

$SqlEiaLabs = "SELECT * FROM `QcEiaLabNames` WHERE LabName='$TestingLab'";
$StmtEiaLabs = $iconn->query($SqlEiaLabs);
$RowLab = $StmtEiaLabs->fetch_array();

$w1 = '30%';
$w2 = '65%';
$w3 = '5%';

$SqlProductList = "SELECT Species,TypeProduct,QcDescription,Variety,Brand,PoNo,ConsigneeName,
ConsigneeAddress,Mcs,CustomsInvNo as InvNumFull,CInvDate as InvDate, APS.PackingStyle,SpeciesName,QCertNo,SCI.ContainerNo,StockOutDate ,QCL.Date as CFEDate,
(SELECT SUM(McsTotal) FROM QcCodeList WHERE InvID = SPGD.InvID) AS McsTotalFromQcCodeList
FROM `ScrmPoGradeData` as SPGD 
left join ScrmPIVarietyList AS SPVL on SPVL.VarietyID=SPGD.VarietyID 
LEFT JOIN ScrmPoData AS SPD ON SPGD.InvID = SPD.PoID
LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
LEFT JOIN AdminPackingStyles AS APS ON SPGD.PackingStyleID = APS.PCKID
LEFT JOIN ScrmSpeciesMaster AS SSM ON SPVL.Species = SSM.ShortCode
LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=SPD.PoID
LEFT JOIN QcCoveringLetter AS QCL ON QCL.InvID=SPD.PoID
 WHERE SPGD.InvId='$PoID' $Qry"; // ScrmCustomInvoices 


$StmtProductist = $iconn->query($SqlProductList);
// $RowProductName = $StmtProductist->fetch_assoc();


$ary_varity_list = [];
$ary_packing_list = [];

while ($varity_list_row = $StmtProductist->fetch_array()) {
    $QcDescription = $varity_list_row['QcDescription'] ? $varity_list_row['QcDescription'] : $varity_list_row['Variety'];
    $ary_varity_list[] = $QcDescription;
    $ary_packing_list[] = $varity_list_row['PackingStyle'];

}

// Reset the pointer
$StmtProductist->data_seek(0);
$RowProductName = $StmtProductist->fetch_assoc();


$ProductNameList = implode(",", array_unique($ary_varity_list));
$PoNumber = $RowProductName['PoNo'];
$Brand = $RowProductName['Brand'];
$ConsigneeName = $RowProductName['ConsigneeName'];
$ConsigneeAddress = $RowProductName['ConsigneeAddress'];
$Mcs = $RowProductName['McsTotalFromQcCodeList'];
$InvNumFull = $RowProductName['InvNumFull'];
$InvDate = $RowProductName['InvDate'];
// $PackingStyle = $RowProductName['PackingStyle'];
$PackingStyle = implode(",", array_unique($ary_packing_list));
$Species = $RowProductName['SpeciesName'];
$ContainerNo = $RowProductName['ContainerNo'];
$QCertificateNo = $RowProductName['QCertNo'];
$StockOutDate = $RowProductName['StockOutDate'];
$CFEDate = $RowProductName['CFEDate'];

$SNO = 0;
$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="3">
			 <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
           		<th width="' . $w1 . '" align="left">Name of the adress of Processor & Packer with EU Approval No</th>
                <th width="' . $w2 . '" align="left">' . $PROCESSOR . '<br>' . $PROCESSOR_ADD . ' Approval No:' . $PROCESSOR_APP_NUM . '</th>				
           </tr>
		   
		   <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Exporter</th>
                <th width="' . $w2 . '" align="left">' . $EXPORTOR . '<br> ' . $EXPORTOR_ADD . '</th>				
           </tr>

		   <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Country of Origin</th>
                <th width="' . $w2 . '" align="left">India</th>				
           </tr>

		   <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Consignee</th>
                <th width="' . $w2 . '" align="left">' . $ConsigneeName . '<br>' . $ConsigneeAddress . '</th>				
           </tr>

		   <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Purchase Order Number</th>
                <th width="' . $w2 . '" align="left">' . $PoNumber . '</th>				
           </tr> 

           <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Brand</th>
                <th width="' . $w2 . '" align="left">' . $Brand . '</th>				
           </tr>
		   
		   <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Species/ Type</th>
                <th width="' . $w2 . '" align="left">' . $Species . '</th>				
           </tr>

		   <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Product Variety</th>
                <th width="' . $w2 . '" align="left">' . str_replace(',', '<br>', $ProductNameList) . '</th>	
           </tr>

		   <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Type Of Packing</th>
                <th width="' . $w2 . '" align="left">' . $PackingStyle . '</th>				
           </tr> 
		
		    <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Total Number of Master Cartons</th>
                <th width="' . $w2 . '" align="left">' . $Mcs . '</th>				
           </tr>

		    <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">Container Number</th>
                <th width="' . $w2 . '" align="left">' . $ContainerNo . '</th>				
           </tr>
		  
		   <tr>  
                <th width="' . $w3 . '" align="left">' . ++$SNO . '</th>	
				<th width="' . $w1 . '" align="left">CFE Number</th>
                <th width="' . $w2 . '" align="left">' . $QCertificateNo . '</th>				
           </tr>
		   </table>';
$pdf->writeHTML($FARMER_NAME);

$FARMER_NAME_2 = '';
$FARMER_NAME_2 .= '<table border="1" cellspacing="0" cellpadding="3">
            <thead>
                <tr style="font-weight: bold;">
                    <th align="left" width="5%">S.No</th>
                    <th align="left" width="15%">Name of the Farmer</th>
                    <th align="left" width="10%">Farm Registration Number</th>
                    <th align="left" width="10%">Harvest Date</th>
                    <th align="left" width="10%">Received Quantity(in Kgs)Whole</th>
                    <th align="left" width="10%">Net Quantity in Kgs</th>
                    <th align="left" width="10%">Freezing Production Date</th>
                    <th align="left" width="10%">NO.M/c</th>
                    <th align="left" width="10%">Production Code</th>
                    <th align="left" width="10%">Best Before Date</th>
                </tr>
            <thead>
            </thead>
            <tbody>';



$sqlFields = "FarmerName, Tracebility, HarvestDt, SUM(McsTotal) McsTotal, BestBeforeDt, BestBeforeDtText, PurchaseLotCode, 
DayCode,QTL.TraceID, Yeild, QtyUnit,
SUM(TotalSlabs*SlabWeight*MFactor*McsTotal) as NetWgtKgs ,SUM(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
QLT.LbuDesignation as DesignationofVerifiedPerson,QLT.LbuName as VerifiedBy, 
QLTA.LbuDesignation as DesignationOfApprovedPerson,QLTA.LbuName as ApprovedBy";

// $sqlSelect = "SELECT $sqlFields FROM QcTracebilityList  AS QTL
// LEFT JOIN QcCodeList AS QCL ON QTL.TraceID = QCL.TracebilityID
// LEFT JOIN ScrmPoData AS SPD ON QCL.InvID = SPD.PoID
// LEFT JOIN AdminPackingStyles AS APS ON QCL.PackingStyleID = APS.PCKID
// left join QcLabTechnologist as QLT on QLT.LUID=QCL.PreparedByID
//  LEFT JOIN QcLabTechnologist as QLTA on QLTA.LUID=QCL.ApprovedByID 
// WHERE QCL.InvID = '$PoID' GROUP BY DayCode,QTL.TraceID  ORDER BY `CLID` ASC";


$sqlSelect = "SELECT $sqlFields FROM QcTracebilityList  AS QTL
LEFT JOIN QcCodeList AS QCL ON QTL.TraceID = QCL.TracebilityID
LEFT JOIN ScrmPoData AS SPD ON QCL.InvID = SPD.PoID
LEFT JOIN AdminPackingStyles AS APS ON QCL.PackingStyleID = APS.PCKID
left join QcLabTechnologist as QLT on QLT.LUID=QCL.PreparedByID
 LEFT JOIN QcLabTechnologist as QLTA on QLTA.LUID=QCL.ApprovedByID 
WHERE QCL.InvID = '$PoID' GROUP BY DayCode,Grades,QTL.TraceID  ORDER BY `DayCode` ASC";

$stmtSelect = $iconn->query($sqlSelect);
$dataRowVerifiedBy = $stmtSelect->fetch_assoc();
$VerifiedBy = $dataRowVerifiedBy['VerifiedBy'];
$Designation = $dataRowVerifiedBy['DesignationofVerifiedPerson'];
$DesignationOfApprovedPerson = $dataRowVerifiedBy['DesignationOfApprovedPerson'];
$ApprovedBy = $dataRowVerifiedBy['ApprovedBy'];

$SNo = 0;
$stmtSelect = $iconn->query($sqlSelect);

while ($dataRow = $stmtSelect->fetch_assoc()) {
    $ProductionDate = getDayCodeDate($dataRow["DayCode"]);
    $Yield = $dataRow['Yeild'];
    $QtyUnit = $dataRow['QtyUnit'];
    $BestBeforeDt = !empty($dataRow['BestBeforeDtText']) ? $dataRow['BestBeforeDtText'] :
        (!empty($dataRow['BestBeforeDt']) ? date('m-d-Y', strtotime($dataRow['BestBeforeDt'])) : '--');

    if ($QtyUnit == 'KGS') {
        $FinishedQty = $dataRow['NetWgtKgs'];
    } else {
        $FinishedQty = $dataRow['NetWgtLbs'] * 0.454;
    }
    $UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
    $ReceivedQty = $UsedQty = round($UsedQty, 3);

    $ExplodeTraceability = explode('-', $dataRow['Tracebility']);
    // $Traceability = $ExplodeTraceability[1] . '-' . $ExplodeTraceability[2];
    $Traceability = $dataRow['Tracebility'];

    $FARMER_NAME_2 .= '<tr>
                    <td align="left" width="5%">' . ++$SNo . '</td>
                    <td align="left" width="15%">' . $dataRow['FarmerName'] . '</td>
                    <td align="left" width="10%">' . $Traceability . '</td>
                    <td align="left" width="10%">' . getFormatedDate($dataRow['HarvestDt']) . '</td>
                    <td align="left" width="10%">' . $ReceivedQty . '</td>
                    <td align="left" width="10%">' . number_format($FinishedQty, 3) . '</td>
                    <td align="left" width="10%">' . $ProductionDate . '</td>
                    <td align="left" width="10%">' . $dataRow['McsTotal'] . '</td>
                    <td align="left" width="10%">' . $dataRow['DayCode'] . '</td>
                    <!--<td align="left" width="10%">' . $dataRow['PurchaseLotCode'] . '</td>-->
                    <td align="left" width="10%">' . $BestBeforeDt . '</td>
                </tr>';
    $GrandTotalMcs += $dataRow['McsTotal'];
    $TotalReceivedQty += $UsedQty;
    $TotalFinishedQty += $FinishedQty;
}
$FARMER_NAME_2 .= '<tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                    <td align="left" colspan="4">Total Quantity</td>
                    <td align="left">' . round($TotalReceivedQty, 3) . '</td>
                    <td align="left">' . number_format($TotalFinishedQty, 3) . '</td>
                    <td align="left"></td>
                    <td align="left">' . $GrandTotalMcs . ' M/C</td>
                    <td align="left"></td>
                    <td align="left"></td>
                </tr>
            <tfoot>
        </table>';

$pdf->writeHTML($FARMER_NAME_2);

$InvDate = getFormatedDate($InvDate);
$CFEDate = getFormatedDate($CFEDate);

$pdf->SetFont('helvetica', '', 9);
// $Declaration = "<p>This is to certify that the Raw material shrimp used for the production of the finished shrimp products 
// for export consignment vide CFE.No.<b>$QCertificateNo</b> dt:<b>$CFEDate</b> & Invoice No: <b>$InvNumFull</b> dt:<b>$InvDate</b> was 
// procured only from the farms certified by Department of fisheries /CAA & Declared by as EIA Chennai .S.O Visakhapatnam & it is 
// declared of fisheries /CAA &that this material is free from Antibiotics, sulphites and microbiological parameters</p>";


$Declaration = "<p>This is to certify that the raw material shrimp used for the production of finished products 
for export consignment vide CFE No.:<b>$QCertificateNo</b> Dt:<b>$CFEDate</b>, Invoice No.: <b>$InvNumFull</b> Dt:<b>$InvDate</b> was 
procured only from MPEDA-certified farms. We hereby declare to EIA Chennai, SO Visakhapatnam that this material is free from antibiotics residues, sulphite, and microbiological contaminants.</p>";

$pdf->writeHTML($Declaration);

$pdf->Ln();
// $pdf->Cell(180, 15, '', 0, 1, 'R');
// $pdf->Cell(180, 5, 'A.RAMA LAKSHMI', 0, 1, 'R');
// $pdf->Cell(180, 5, 'Senior Manager-Q.A', 0, 1, 'R');

$pdf->Cell(49, 15, '', 0, 1, 'L');
// $pdf->SetFont($FONT_ARI, '', $header);
$pdf->Cell(49, 5, $VerifiedBy, 0, 0, 'L');

$pdf->SetX($pdf->GetPageWidth() - $rightMargin - $cellWidth); // Set position for right alignment
$pdf->Cell($cellWidth, 5, $ApprovedBy, 0, 1, 'R');

$pdf->Cell(49, 5, $Designation, 0, 0, 'L');
// Designation
$pdf->SetX($pdf->GetPageWidth() - $rightMargin - $cellWidth); // Set the same position
$pdf->Cell($cellWidth, 5, $DesignationOfApprovedPerson, 0, 1, 'R');

$pdf->Ln();

$pdf->SetFont($FONT_ARI, '', $header);

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
