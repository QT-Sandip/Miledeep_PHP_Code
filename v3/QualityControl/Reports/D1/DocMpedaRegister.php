<?php


$IsHeader = 0;
$IsFooter = 0;
$QueryParms = $_GET['Q'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$TYPE = $_GET['TypeProduct'];
$FileType = $_GET['Type'];



$sql = "SELECT *,PD.ExporterID  FROM ScrmPoData  as PD
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID 
left join AdminExporterDetails as AED on AED.ExpID=PD.ProcessorID
WHERE `PoID`='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();
$ProcessorID = $row['ProcessorID'];
$ProcessorName = $row['ExpName'];
$ExpApprovalNo = $row['ExpApprovalNo'];
if (!empty($ExpApprovalNo) && $ExpApprovalNo == 990) {
	$ProcessorName = str_ireplace("unit 3", $ExpApprovalNo, $ProcessorName);
}

$SqlPo = "SELECT * FROM ScrmPoData WHERE `PoID`='$PoID'";
$StmtPo = $iconn->query($SqlPo);
$RowPo = $StmtPo->fetch_array();


$SqlVerifiedBy = "SELECT LbuName, LbuDesignation FROM `QcCodeList` as CL 
					LEFT JOIN QcLabTechnologist as QLT on QLT.LUID=CL.ApprovedByID
				 	WHERE InvID='$PoID' and ApprovedByID!='' ";


$StmtVerifiedBy = $iconn->query($SqlVerifiedBy);
$RowVerifiedBy = $StmtVerifiedBy->fetch_array();
$ApprovedBy = $RowVerifiedBy['LbuName'];
$ApprovedByDesignation = $RowVerifiedBy['LbuDesignation'];

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Raw Material Register : ' . $row['PO_NUM'] . ' - ' . $row['PO_DATE']);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
// $pdf->SetFooterMargin(45);
$pdf->SetFooterMargin(15);
$pdf->SetHeaderMargin(15);
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 45);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
// $pdf->SetAutoPageBreak(TRUE, 40);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();
// $pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 3, $ProcessorName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 3, 'Raw Material Register', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

$content = getPoInformation($row, $iconn);
$pdf->writeHTML($content, false);
$table_data = getP_TABLE($iconn, $ID);
$pdf->writeHTML($table_data);

$file_name = 'MpedaRegister' . $row['PO_NUM'];

function getP_TABLE($iconn, $ID)
{

	global $PoID;

	$SqlPoData = "SELECT 
	GROUP_CONCAT(Brand) As Brand,
	GROUP_CONCAT(FullName) As FullName,
	GROUP_CONCAT(PackingStyle) As PackingStyle
	FROM `QcCodeList` as QCL 
	left Join QcPoVarietyList AS PVL on QCL.VarietyID=PVL.VarietyID 
	left join AdminPackingStyles as APS on QCL.PackingStyleID=APS.PCKID 
	WHERE QCL.InvID='$PoID' ORDER BY `CLID` ASC";

	$StmtPoData = $iconn->query($SqlPoData);
	$RowPoData = $StmtPoData->fetch_array();

	$AryBrand = explode(",", $RowPoData['Brand']);
	$AryFullName = explode(",", $RowPoData['FullName']);
	$AryPackingStyle = explode(",", $RowPoData['PackingStyle']);

	$Brand = implode(",", array_unique($AryBrand, SORT_REGULAR));
	$FullName = implode(",", array_unique($AryFullName, SORT_REGULAR));
	$PackingStyle = implode(",", array_unique($AryPackingStyle, SORT_REGULAR));

	$sql_inv_data = "SELECT *,ScrmPoData.ExporterID FROM `ScrmPoData` 
left JOIN ScrmConsigneeDetails ON ScrmConsigneeDetails.ConID=ScrmPoData.ConsigneeID 
left join ScrmCustomInvoices as SCI on SCI.PiID=ScrmPoData.PoID
 WHERE PoID='$PoID'";
	$stmt_inv_data = $iconn->query($sql_inv_data);
	$R_INV_DATA = $stmt_inv_data->fetch_array();
	$ExpID = $R_INV_DATA['ExporterID'];
	$QtyUnit = $R_INV_DATA['QtyUnit'];
	$ExporterData = getExporterDetails($ExpID);
	$InvShortCode = $ExporterData['InvShortCode'];

	$SPACE = '                                ';
	$PO_DATE = $R_INV_DATA['PoDate'];
	$INVOICE_DATE = $R_INV_DATA['InvDate'];
	$content = '';

	$size = 40;
	$size1 = 60;
	$PO_DATE = date("d-m-Y", strtotime($PO_DATE));
	$INVOICE_DATE = date("d-m-Y", strtotime($INVOICE_DATE));

	$InvNO = getInvoiceNumber($iconn, $ExpID, $PoID);

	$content .= '<table border="1" cellspacing="0" cellpadding="3">	<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> </font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="3.5%"><font size="7">S.NO </font></th>
				<th  align="center"  width="7%"><font size="7">DATE OF RECEIPT OF RAW MATERIAL</font></th>
                <th  align="center" width="20%"><font size="7">NAME & ADDRESS OF THE FARMER WITH FARM LOCATION & FARM ID</font></th>
                <th  align="center" width="6%"><font size="7">SOURCE</font></th>
				<th  align="center" width="8%"><font size="7">SPECIES</font></th>
                
                <th  align="center" width="6%"><font size="7">TYPE OF RAW MATERIAL </font></th>
                <th  align="center" width="6%"><font size="7">RECEIVED QTY IN KGS</font></th>
                <th  align="center" width="5.5%"><font size="7">USED QTY IN KGS</font></th>
                <th  align="center" width="6%"><font size="7">DATE OF <BR>PROCESSING</font></th>
                <th  align="center" width="7%"><font size="7">PRODUCT</font></th>                
                <th  align="center" width="7%"><font size="7">FINISHED QTY IN KGS</font></th>
				<th  align="center" width="5.5%"><font size="7">BALANCE QTY </font></th>
                <th  align="center" width="5%"><font size="7">TOTAL M/CS</font></th>
                <th  align="center" width="7.5%"><font size="7"> CODE</font></th>
               
               </tr>';

	$SqlRmr = "SELECT * FROM `QcRawMaterialPurchaseReg` WHERE InvID='$PoID'";


// 	$SqlRmr = "SELECT CLID,Tracebility,DayCode,PVL.ShortName FROM `QcCodeList` as QCL
// LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
// left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
// left join QcTracebilityList As QTL on QTL.TraceID=QCL.TracebilityID
// where QCL.InvID='$PoID' GROUP by QCL.TracebilityID,PVL.ShortName,QCL.DayCode order by QCL.CLID asc";


	$SqlRmr = "SELECT CLID,Tracebility,DayCode,PVL.ShortName FROM `QcCodeList` as QCL
LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
left join QcTracebilityList As QTL on QTL.TraceID=QCL.TracebilityID
where QCL.InvID='$PoID' GROUP by QCL.TracebilityID,PVL.ShortName,QCL.DayCode order by QCL.DayCode asc";

	//  print_r($SqlRmr);exit;
	$StmtRmr = $iconn->query($SqlRmr);

	while ($RowRmr = $StmtRmr->fetch_array()) {

		$CodeListId = $RowRmr['CLID'];
		$Source = $RowRmr['Source'] = "AQUACULTURE";
		$Yield = $RowRmr['Yield'];
		$YieldPerc = $RowRmr['YieldPerc'];


		$SqlCode = "SELECT cl.*,QTL.*,PVL.TypeProduct,PDG.Variety,PDG.QcDescription,PVL.ShortName,
		SSM.SpeciesName FROM `QcCodeList` as cl 
   LEFT JOIN `ScrmPoGradeData` as PDG on PDG.ID=cl.VarietyID
   LEFT JOIN ScrmPIVarietyList as PVL on PDG.VarietyID=PVL.VarietyID
   LEFT JOIN ScrmSpeciesMaster as SSM on SSM.ShortCode=PVL.Species
   LEFT JOIN `QcTracebilityList` as QTL on cl.TracebilityID=QTL.TraceID 
   
			WHERE CLID='$CodeListId'";

		//LEFT JOIN `ScrmPoGradeData` as pvl on pvl.ID=cl.VarietyID 
		$stmtCode = $iconn->query($SqlCode);
		$RowCodeList = $stmtCode->fetch_array();

		// $YieldID = $RowCodeList['YieldID'];
		$YieldID = $RowCodeList['Yeild'];

		$TypeProduct = $RowCodeList['TypeProduct'];
		$ShortName = $RowCodeList['ShortName'];


		$RowCodeList['ShortName'] = getProductTypeCode($TypeProduct, $ShortName);
		$ProductName = $RowCodeList['ShortName'];
		$Species = $RowCodeList['SpeciesName'];

		$DayCode = $RowCodeList['DayCode'];
		$VarietyID = $RowCodeList['VarietyID'];
		$Brand = $RowCodeList['Brand'];
		$TracebilityID = $RowCodeList['TracebilityID'];
		$PackingStyleID = $RowCodeList['PackingStyleID'];
		$TracebilityName = $RowCodeList['FarmerName'] . ',<br>' . $RowCodeList['AddressContinued'] . '<br>' . $RowCodeList['Tracebility'];


		$dayCode = strtoupper(trim($RowCodeList['DayCode']));
		$ProductionDate = getDayCodeDate($dayCode);

		$SqlYield = "SELECT * FROM `QcStandardYields` WHERE ID='$YieldID'";
		$stmtYield = $iconn->query($SqlYield);
		$RowYield = $stmtYield->fetch_array();
		$Yield = $RowYield['Yield'];


		$sqlMcs = "SELECT sum(McsTotal) as McsTotal,Yeild,
			sum(TotalSlabs*SlabWeight*MFactor*McsTotal) as NetWgtKgs ,sum(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs
			FROM `QcCodeList` as QCL 
			LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
			left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
			left join AdminPackingStyles As APS on APS.PCKID=QCL.PackingStyleID
			WHERE 
			QCL.InvID='$PoID' 
			and DayCode='$DayCode' 
			and PVL.ShortName='$ShortName'  
			and TracebilityID='$TracebilityID' ";

		//ECHO  $sqlMcs; EXIT;
		$stmtMcs = $iconn->query($sqlMcs);

		$RowMcs = $stmtMcs->fetch_array();

		$Mcs = $RowMcs['McsTotal'];
		if ($YieldPerc == '') {
			$Yield = $RowMcs['Yeild'];
		} else {
			$Yield = $YieldPerc;
		}
		/***uma */
		if ($QtyUnit == 'KGS') {
			$FinishedQty = $RowMcs['NetWgtKgs'];
		} else {
			$FinishedQty = $RowMcs['NetWgtLbs'] * 0.454;
		}
		//echo $FinishedQty; exit;
		/***uma */

		//$FinishedQty = round($RowMcs['FinishedQty'],2);
		$UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
		$ReceivedQty = $UsedQty = round($UsedQty, 3);
		$BalanceQuantity = 0; //$ReceivedQty-$UsedQty;
		$TotalUsedQty += $UsedQty;
		$TotalRedQty += $ReceivedQty;
		$TotalBalanceQuantity += $BalanceQuantity;

		$TotalFinishedQty += $FinishedQty;

		$TotalMCS += $Mcs;

		//  print_r($ProductionDate);exit;

		$content .= '
				<tr>  
           		<th  align="center"  width="3.5%"><font size="7">' . ++$Sno . '</font></th>
				<th  align="center"  width="7%"><font size="7">' . $ProductionDate . '</font></th>
                <th  align="center" width="20%"><font size="7">' . $TracebilityName . '</font></th>
                <th  align="center" width="6%"><font size="7">' . $Source . '</font></th>
				<th  align="center" width="8%"><font size="7">' . $Species . '</font></th>
                <th  align="center" width="6%"><font size="7">HO.ON</font></th>
                <th  align="center" width="6%"><font size="7">' . $ReceivedQty . '</font></th>
                <th  align="center" width="5.5%"><font size="7">' . $UsedQty . '</font></th>
                <th  align="center" width="6%"><font size="7">' . $ProductionDate . '</font></th>
                <th  align="center" width="7%"><font size="7">' . $ProductName . '</font></th>
                <th  align="center" width="7%"><font size="7">' . number_format($FinishedQty, 3) . '</font></th>
				<th  align="center" width="5.5%"><font size="7">' . $BalanceQuantity . '</font></th>
                <th  align="center" width="5%"><font size="7">' . $Mcs . '</font></th>
                <th  align="center" width="7.5%"><font size="7">' . $DayCode . ' </font></th>
               
               </tr>';

		$COUNT++;
	}



	$content .= '<tr>  
           		<th  align="center"  width="50.5%"><font size="7">GRAND TOTAL</font></th>
                <th  align="center" width="6%"><font size="7">' . $TotalRedQty . '</font></th>
                <th  align="center" width="5.5%"><font size="7">' . $TotalUsedQty . '</font></th>
                <th  align="center" width="6%"><font size="7"></font></th>
                <th  align="center" width="7%"><font size="7"></font></th>
                <th  align="center" width="7%"><font size="7">' . number_format($TotalFinishedQty, 3) . '</font></th>
				<th  align="center" width="5.5%"><font size="7"> ' . $TotalBalanceQuantity . '</font></th>
                <th  align="center" width="5%"><font size="7">' . $TotalMCS . '</font></th>
                <th  align="center" width="7.5%"><font size="7"></font></th>
               
               </tr></table>';


	return $content;
}
$pdf->Cell(90, 5, 'NAME : ' . $ApprovedBy, 0, 1, 'L');
$pdf->Cell(90, 5, 'DESIGNATION :' . $ApprovedByDesignation, 0, 0, 'L');
$pdf->Cell(150, 5, 'COMPANY SEAL:', 0, 1, 'R');



ob_end_clean();

/*
$pdf->Output($file_name, 'I');

$pdf->Output('MpedaRegister.pdf', 'I');*/

if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $RowPo['PoNo'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName . '.pdf', 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName . '.pdf';
	$pdf->Output($FileName, $FileType);
}
