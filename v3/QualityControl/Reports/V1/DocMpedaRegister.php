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



$sql = "SELECT *,PD.ExporterID FROM ScrmPoData  as PD
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID 
left join AdminExporterDetails as AED on AED.ExpID=PD.ProcessorID
WHERE `PoID`='$PoID'";
$stmt_REP = $iconn->query($sql);
$row = $stmt_REP->fetch_array();
$ProcessorID = $row['ProcessorID'];
$ProcessorName = $row['ExpName'];
$PackerAddress = $row['PackerAddress'];

$SqlPo = "SELECT * FROM ScrmPoData WHERE `PoID`='$PoID'";
$StmtPo = $iconn->query($SqlPo);
$RowPo = $StmtPo->fetch_array();


  $SqlVarifiedBy = "SELECT VerifiedBy, LbuDesignation FROM `QcCoveringLetter` as CL 
					LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
				 	WHERE InvID='$PoID' ";  

$StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
$RowVarifiedBy = $StmtVarifiedBy->fetch_array();
$VerifiedBy = $RowVarifiedBy['VerifiedBy'];
$Designation = $RowVarifiedBy['LbuDesignation'];

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Raw Material Register : ' . $row['PO_NUM'] . ' - ' . $row['PO_DATE']);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(20);
$pdf->SetHeaderMargin(10);
$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT, 10);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 7);

$pdf->AddPage();
$pdf->Cell(0, 4, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 4, $ProcessorName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 4, $PackerAddress, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 4, 'Raw Material Register', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

//$content = getPoDetails($RowPo, $iconn);
$content = getPoDetails($row, $iconn);
$pdf->writeHTML($content, false);
$table_data = getP_TABLE($iconn, $ID);
$pdf->writeHTML($table_data);




$file_name = 'MpedaRegister' . $row['PO_NUM'];





function getP_TABLE($iconn, $ID)
{

	global $PoID;
	global $TblQcCodeList;




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
	// $INVOICE_DATE=$R_INV_DATA['CInvDate'];
	$INVOICE_DATE = $R_INV_DATA['InvDate'];


	$content = '';

	$size = 40;
	$size1 = 60;
	$PO_DATE = date("d-m-Y", strtotime($PO_DATE));
	$INVOICE_DATE = date("d-m-Y", strtotime($INVOICE_DATE));
	
	$InvNO = getInvoiceNumber($iconn, $ExpID, $PoID);

			

				$content .= '<table border="1" cellspacing="0" cellpadding="2">
				<!--	<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> </font></th>
               </tr>-->
				
				<tr>  
           		<th  align="center"  width="3.5%"><font size="6"><b>S.NO</b> </font></th>
				<th  align="center"  width="6%"><font size="6"><b>DATE OF RECEIPT OF RAW MATERIAL</b></font></th>
                <th  align="center" width="18%"><font size="6"><b>NAME & ADDRESS OF THE FARMER WITH FARM LOCATION & FARM ID</b></font></th>
                <th  align="center" width="7%"><font size="6"><b>SOURCE</b></font></th>
				<th  align="center" width="8%"><font size="6"><b>SPECIES</b></font></th>
                
                <th  align="center" width="6%"><font size="6"><b>TYPE OF RAW MATERIAL </b></font></th>
                <th  align="center" width="6%"><font size="6"><b>RECEIVED QTY IN KGS</b></font></th>
                <th  align="center" width="5.5%"><font size="6"><b>USED QTY IN KGS</b></font></th>
                <th  align="center" width="6%"><font size="6"><b>DATE OF <BR>PROCESSING</b></font></th>
                <th  align="center" width="6%"><font size="6"><b>PRODUCT</b></font></th>
                <th  align="center" width="5%"><font size="6"><b>YIELD %</b></font></th>
                <th  align="center" width="7%"><font size="6"><b>FINISHED QTY IN KGS</b></font></th>
                <th  align="center" width="6%"><font size="6"><b>PACKING</b></font></th>
                <th  align="center" width="4.5%"><font size="6"><b>TOTAL M/CS</b></font></th>
                <th  align="center" width="5.5%"><font size="6"><b> CODE</b></font></th>
               
               </tr>';

	$SqlRmr = "SELECT * FROM `QcRawMaterialPurchaseReg` WHERE InvID='$PoID'";


$SqlRmr = "SELECT CLID,Tracebility,DayCode,PVL.ShortName FROM `QcCodeList` as QCL
LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
left join QcTracebilityList As QTL on QTL.TraceID=QCL.TracebilityID
where QCL.InvID='$PoID' GROUP by QCL.TracebilityID,PVL.ShortName,QCL.DayCode ORDER BY CLID ASC";
	$StmtRmr = $iconn->query($SqlRmr);

	while ($RowRmr = $StmtRmr->fetch_array()) {

		$CodeListId = $RowRmr['CLID'];
		$Source = $RowRmr['Source'] = 'FARM   AQUACULTURE';
		$Yield = $RowRmr['Yield'];
		$YieldPerc = $RowRmr['YieldPerc'];
		$ReceivedQty = $RowRmr['ReceivedQty'];

		$SqlCode = "SELECT cl.*,QTL.*,PVL.TypeProduct,PDG.Variety,PDG.QcDescription,PVL.ShortName,SSM.SpeciesName FROM `QcCodeList` as cl 
   LEFT JOIN `ScrmPoGradeData` as PDG on PDG.ID=cl.VarietyID
   LEFT JOIN ScrmPIVarietyList as PVL on PDG.VarietyID=PVL.VarietyID
   LEFT JOIN ScrmSpeciesMaster as SSM on SSM.ShortCode=PVL.Species
   LEFT JOIN `QcTracebilityList` as QTL on cl.TracebilityID=QTL.TraceID 
			WHERE CLID='$CodeListId'";
		$stmtCode = $iconn->query($SqlCode);
		$RowCodeList = $stmtCode->fetch_array();

		$YieldID = $RowCodeList['YieldID'];

		$TypeProduct=$RowCodeList['TypeProduct']; 
		$ShortName=$RowCodeList['ShortName']; 

		$RowCodeList['ShortName']= getProductTypeCode($TypeProduct,$ShortName);
		$ProductName = $RowCodeList['ShortName'];
		$Species = $RowCodeList['SpeciesName'];

		$DayCode = $RowCodeList['ShortName'];
		$DayCode = $RowCodeList['DayCode'];
		$VarietyID = $RowCodeList['VarietyID'];
		$Brand = $RowCodeList['Brand'];
		$TracebilityID = $RowCodeList['TracebilityID'];
		$PackingStyleID = $RowCodeList['PackingStyleID'];

		if($RowCodeList['FarmerCode']!=''){
			$FarmerCode=$RowCodeList['FarmerCode'].' - ';
		}
		$TracebilityName = $RowCodeList['FarmerName'] ." ". $RowCodeList['AddressContinued'] . '<br>' .$FarmerCode .$RowCodeList['Tracebility'];

		$DateOfReciept = $ProcessingDate = $ProductionDate = getDayCodeDate($RowCodeList['DayCode']);

		$SqlYield = "SELECT * FROM `QcStandardYields` WHERE ID='$YieldID'";
		$stmtYield = $iconn->query($SqlYield);
		$RowYield = $stmtYield->fetch_array();
		$Yield = $RowYield['Yield'];

		
			
			 $sqlMcs = "SELECT sum(McsTotal) as McsTotal,Yeild,APS.PackingStyle,
			sum(TotalSlabs*SlabWeight*MFactor*McsTotal) as NetWgtKgs ,sum(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs
			FROM `$TblQcCodeList` as QCL 
			LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
			left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
			left join AdminPackingStyles As APS on APS.PCKID=QCL.PackingStyleID
			WHERE 
			QCL.InvID='$PoID' 
			and DayCode='$DayCode' 
			and PVL.ShortName='$ShortName'  
			and TracebilityID='$TracebilityID' ";
		$stmtMcs = $iconn->query($sqlMcs); 

		$RowMcs = $stmtMcs->fetch_array();

		$Mcs = $RowMcs['McsTotal'];
		$PackingStyle = $RowMcs['PackingStyle'];
		if ($YieldPerc == '') {
			$Yield = $RowMcs['Yeild'];
		} else {
			$Yield = $YieldPerc;
		}
if($QtyUnit=='KGS'){
	$FinishedQty = $RowMcs['NetWgtKgs'];
}
else{
	$FinishedQty = $RowMcs['NetWgtLbs']*0.454;
}

		$UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
		$ReceivedQty = $UsedQty = round($UsedQty, 3);
		$TotalUsedQty += $UsedQty;
		$TotalRedQty += $ReceivedQty;

		$TotalFinishedQty += $FinishedQty;

		$TotalMCS += $Mcs;
		$FonthSize='3';
		$content .= '
				<tr>  
           		<th  align="center"  width="3.5%"><font size="6">' . ++$Sno . '</font></th>
				<th  align="center"  width="6%"><font size="6">' . $ProductionDate . '</font></th>
                <th  align="center" width="18%"><font size="6">' . $TracebilityName . '</font></th>
                <th  align="center" width="7%"><font size="6">FARM <br> AQUACULTURE</font></th>
				<th  align="center" width="8%"><font size="6">' . $Species . '</font></th>
                <th  align="center" width="6%"><font size="6">HEAD ON VANNAMEI SHRIMPS</font></th>
                <th  align="center" width="6%"><font size="6">' . $ReceivedQty . '</font></th>
                <th  align="center" width="5.5%"><font size="6">' . $UsedQty . '</font></th>
                <th  align="center" width="6%"><font size="6">' . $ProductionDate . '</font></th>
                <th  align="center" width="6%"><font size="6">' . $ProductName . '</font></th>
                <th  align="center" width="5%"><font size="6">' . $Yield . '</font></th>
                <th  align="center" width="7%"><font size="6">' . number_format($FinishedQty,3) . '</font></th>
                <th  align="center" width="6%"><font size="6">' . $PackingStyle . '</font></th>
                <th  align="center" width="4.5%"><font size="6">' . $Mcs . '</font></th>
                <th  align="center" width="5.5%"><font size="6">' . $DayCode . ' </font></th>
               
               </tr>';

		$COUNT++;
	}



	$content .= '<tr>  
           		<th  align="center"  width="48.5%"><font size="7">GRAND TOTAL</font></th>
                <th  align="center" width="6%"><font size="7">' . $TotalRedQty . '</font></th>
                <th  align="center" width="5.5%"><font size="7">' . $TotalUsedQty . '</font></th>
                <th  align="center" width="6%"><font size="7"></font></th>
                <th  align="center" width="6%"><font size="7"></font></th>
                <th  align="center" width="5%"><font size="7"></font></th>
                <th  align="center" width="7%"><font size="7">' . number_format($TotalFinishedQty,3) . '</font></th>
                <th  align="center" width="6%"><font size="7"></font></th>
                <th  align="center" width="4.5%"><font size="7">' . $TotalMCS . '</font></th>
                <th  align="center" width="5.5%"><font size="7"></font></th>
               
               </tr></table>';


	return $content;


}
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(90, 5, 'NAME : ' . $VerifiedBy, 0, 1, 'L');
$pdf->Cell(90, 5, 'DESIGNATION :' . $Designation, 0, 0, 'L');
$pdf->Cell(150, 5, 'COMPANY SEAL:', 0, 1, 'R');

ob_end_clean();


if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $RowPo['InvNumFull'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName.'.pdf', 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName.'.pdf';
	$pdf->Output($FileName, $FileType);

}
?>