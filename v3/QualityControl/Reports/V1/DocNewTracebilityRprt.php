<?php

$IsHeader = 0;
$IsFooter = 0;
$QueryParms = $_GET['Q'];

include ($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include ($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$TYPE = $_GET['TypeProduct'];
$FileType = $_GET['Type'];

$SqlInvoiceDetails = "SELECT *,PD.ExporterID,SCI.ContainerNo FROM ScrmPoData  as PD
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID
left join ScrmISFDetails as ISF on ISF.PIID=PD.PoID 
left join AdminExporterDetails as AED on AED.ExpID=PD.ProcessorID
WHERE `PoID`='$PoID'";

$stmtInvoiceDetails = $iconn->query($SqlInvoiceDetails);
$row = $stmtInvoiceDetails->fetch_array();
$ProcessorID = $row['ProcessorID'];
$ProcessorName = $row['ExpName'];
$PackerAddress = $row['PackerAddress'];

$SqlVarifiedBy = "SELECT VerifiedBy, LbuDesignation,QCertNo FROM `QcCoveringLetter` as CL 
					LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
				 	WHERE InvID='$PoID' ";

$StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
$RowVarifiedBy = $StmtVarifiedBy->fetch_array();
$VerifiedBy = $RowVarifiedBy['VerifiedBy'];
$Designation = $RowVarifiedBy['LbuDesignation'];
$QCertNo = $RowVarifiedBy['QCertNo'];

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Tracebility Report : ' . $row['PO_NUM'] . ' - ' . $row['PO_DATE']);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(45);
$pdf->SetHeaderMargin(15);
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 45);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();
$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 5, $ProcessorName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');

$pdf->Cell(0, 5, 'TRACEABILITY REPORT', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Ln();
$content = getPoDataTable($row, $iconn);
$pdf->writeHTML($content, false);
$table_data = getP_TABLE($iconn, $ID);
$pdf->writeHTML($table_data);

$file_name = 'Tracebility Register' . $row['PO_NUM'];

function getPoDataTable($INV_DATA_ARY, $iconn)
{

	global $QCertNo;
	$ExpID = $INV_DATA_ARY['ExporterID'];
	$ProcessorID = $INV_DATA_ARY['ProcessorID'];
	$PoID = $INV_DATA_ARY['PoID'];
	$ExporterData = getExporterDetails($ExpID);
	$ConsigneeID = $INV_DATA_ARY['ConsigneeID'];
	$INVOICE_ID = $INV_DATA_ARY['PoID'];
	$InvNO = $INV_DATA_ARY['CustomsInvNo'];
	$ContainerNo = $INV_DATA_ARY['ContainerNo'];

	$ExporterName = $ExporterData['ExpName'];
	$ExporterAddress = $ExporterData['ExpAddress'];
	$ProcessorData = getExporterDetails($ProcessorID);
	$ProcessorName = $ProcessorData['ExpName'];
	$ProcessorAddress = $ProcessorData['ExpAddress'];
	$ProcApprovalNo = $ProcessorData['ExpApprovalNo'];

	$SqlConDetails = "SELECT * FROM ScrmConsigneeDetails  WHERE `ConID`='$ConsigneeID'";
	$StmtConDetails = $iconn->query($SqlConDetails);
	$ConData = $StmtConDetails->fetch_array();
	$ConsigneeName = $ConData['ConsigneeName'];

	$sqlPoVarietyLis = "SELECT SPGD.Variety,SPGD.QcDescription,SPVL.GlazeStatus,SPVL.GlazePerc
	 FROM `ScrmPoGradeData` SPGD
	LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.DeviSUPC=SPGD.DeviSupc  WHERE  `InvID`='$INVOICE_ID'";
	$StmtPoVarietyList = $iconn->query($sqlPoVarietyLis);

	while ($row = $StmtPoVarietyList->fetch_array()) {
		$name = $row['QcDescription'] ? $row['QcDescription'] : $row['Variety'];
		$AryVariety[] = $name;

		$GlazeStatus = $row['GlazeStatus'];
		if ($GlazeStatus == 'Yes') {

			$GlazePerc = $row['GlazePerc'];
			$GlazePerc = '[' . $GlazePerc . '% Glaze ]';
		}

	}

	$AryVariety = array_unique($AryVariety);
	$FULL_NAME = implode(',', $AryVariety);

	$SqlCodeList = "SELECT * FROM QcCodeList as Qcl
	left join AdminPackingStyles as Aps On Qcl.PackingStyleID=Aps.PCKID
	LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=Qcl.Brand
	where InvID='$INVOICE_ID'";
	$StmtCodeList = $iconn->query($SqlCodeList);
	while ($CODE = $StmtCodeList->fetch_array()) {

		$PrintStatus = trim($CODE['PrintStatus']);
		$AryPackings[] = $CODE['PackingStyle'];
	}

	$AryPackings = array_unique($AryPackings);
	$PACKING = implode(',', $AryPackings);

	$CInvDate = strtotime($INV_DATA_ARY['CInvDate']);
	$CInvDate = date("d-m-Y", $CInvDate);
	$PoDate = strtotime($INV_DATA_ARY['PoDate']);
	$PO_DATE = date("d-m-Y", $PoDate);

	$header_size = 7;
	$size = 15;
	$size2 = 35;
	$size3 = 40;
	$content = '<table border="1" cellspacing="0" cellpadding="3">  
	           
				<tr>  
           		<th  align="left"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>NAME AND ADDRESS OF PROCESSOR & PACKER WITH EU APPROVAL NO.</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $ProcessorName . '<br>' . $ProcessorAddress . ',Approval No.' . $ProcApprovalNo . '</b></font></th>
                <th  align="left" width="' . ($size) . '%"><font size="' . $header_size . '"><b>EXPORTER</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $ExporterName . '<br>' . $ExporterAddress . '</b></font></th>
				</tr>
				
				<tr>  
           		<th  align="left"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>CONSIGNEE</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $ConsigneeName . '</b></font></th>
                <th  align="left" width="' . ($size) . '%"><font size="' . $header_size . '"><b>VARIETY</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $FULL_NAME . '</b></font></th>
				</tr>
				
				
				<tr>  
           		<th  align="left"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>PO NO & Date</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $INV_DATA_ARY['PoNo'] . ' Date  ' . $PO_DATE . '</b></font></th>
                <th  align="left" width="' . ($size) . '%"><font size="' . $header_size . '"><b>PACKING </b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b> ' . $PACKING . $GlazePerc . '</b></font></th>
				</tr>
				
				
				<tr>  
           		<th  align="left"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>INVOICE NO & DATE</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $InvNO . ' Date ' . $CInvDate . '</b></font></th>
                <th  align="left" width="' . ($size) . '%"><font size="' . $header_size . '"><b>COUNTRY OF ORIGIN</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>INDIA</b></font></th>
				</tr>

				<tr>  
           		<th  align="left"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>CONTAINER NO & CFE NO</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $ContainerNo . ' &  ' . $QCertNo . '</b></font></th>
                <th  align="left" width="' . ($size) . '%"><font size="' . $header_size . '"><b> SPECIES </b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>LITOPENAEUS VANNAMEI</b></font></th>
				</tr>

				</table>';
	return $content;
}


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

	$SqlPoDetails = "SELECT *,ScrmPoData.ExporterID FROM `ScrmPoData` 
left JOIN ScrmConsigneeDetails ON ScrmConsigneeDetails.ConID=ScrmPoData.ConsigneeID 
left join ScrmCustomInvoices as SCI on SCI.PiID=ScrmPoData.PoID
 WHERE PoID='$PoID'";
	$StmtPoDetails = $iconn->query($SqlPoDetails);
	$RowPoDetails = $StmtPoDetails->fetch_array();

	$ExpID = $RowPoDetails['ExporterID'];
	$QtyUnit = $RowPoDetails['QtyUnit'];
	$InvoiceNumber = $RowPoDetails['InvNumFull'];
	$ExporterData = getExporterDetails($ExpID);
	$InvShortCode = $ExporterData['InvShortCode'];
	$SPACE = '                                ';
	$PoDate = $RowPoDetails['PoDate'];
	$InvDate = $RowPoDetails['InvDate'];
	$CInvDate = $RowPoDetails['CInvDate'];

	$size = 40;
	$size1 = 60;
	$PO_DATE = date("d-m-Y", strtotime($PoDate));
	$INVOICE_DATE = date("d-m-Y", strtotime($InvDate));
	$CInvDate = date("d-m-Y", strtotime($CInvDate));
	$ColumnWidth = 8.6;

	global $InvoiceNumber;

	$content = '';



	$content .= '<table border="1" cellspacing="0" cellpadding="3">
				<!--	<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> </font></th>
               </tr>-->
				
				<tr>  
					<th  align="center"  width="4.5%"><font size="7"><b>S.NO </b></font></th>
					<th  align="center" width="18%"><font size="7"><b>NAME OF THE FARMER</b></font></th>
					<th  align="center"  width="' . $ColumnWidth . '%"><font size="7"><b>FARM REG. NO.</b></font></th>
					<th  align="center"  width="' . $ColumnWidth . '%"><font size="7"><b>HARVEST DATE</b></font></th>
					<th  align="center" width="' . $ColumnWidth . '%"><font size="7"><b>RECEIVED QTY IN KGS</b></font></th>
					<th  align="center" width="' . $ColumnWidth . '%"><font size="7"><b>USED QTY IN KGS</b></font></th>
					<th  align="center" width="' . $ColumnWidth . '%"><font size="7"><b>FINISHED QTY IN KGS</b></font></th>
					<th  align="center" width="' . $ColumnWidth . '%"><font size="7"><b>TOTAL M/CS</b></font></th>
					<th  align="center" width="' . $ColumnWidth . '%"><font size="7"><b>FREEZING/ PRODUCTION DATE</b></font></th>
					<th  align="center" width="' . $ColumnWidth . '%"><font size="7"><b>BEST BEFORE DATE</b></font></th>
					<th  align="center" width="' . $ColumnWidth . '%"><font size="7"><b>PRODUCTION CODE</b></font></th>
               
               </tr>';


	$SqlRmr = "SELECT  GROUP_CONCAT(CLID order by CLID asc ) as CLID,Tracebility,DayCode,PVL.ShortName FROM `QcCodeList` as QCL
LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
left join QcTracebilityList As QTL on QTL.TraceID=QCL.TracebilityID
where QCL.InvID='$PoID' GROUP by QCL.TracebilityID ORDER BY QCL.CLID ASC ";
	$StmtRmr = $iconn->query($SqlRmr);

	while ($RowRmr = $StmtRmr->fetch_array()) {

		$CodeListId = $RowRmr['CLID'];
		$Yield = $RowRmr['Yield'];
		$YieldPerc = $RowRmr['YieldPerc'];

		$SqlCode = "SELECT cl.*,QTL.*,PVL.TypeProduct,PDG.Variety,PDG.QcDescription,PVL.ShortName,SSM.SpeciesName 
		FROM `QcCodeList` as cl 
   LEFT JOIN `ScrmPoGradeData` as PDG on PDG.ID=cl.VarietyID
   LEFT JOIN ScrmPIVarietyList as PVL on PDG.VarietyID=PVL.VarietyID
   LEFT JOIN ScrmSpeciesMaster as SSM on SSM.ShortCode=PVL.Species
   LEFT JOIN `QcTracebilityList` as QTL on cl.TracebilityID=QTL.TraceID 
			WHERE  cl.InvID='$PoID'  and  cl.CLID in ($CodeListId) 
			order by cl.CLID ASC";

		$stmtCode = $iconn->query($SqlCode);
		$RowCodeList = $stmtCode->fetch_array();

		$YieldID = $RowCodeList['YieldID'];
		$TypeProduct = $RowCodeList['TypeProduct'];
		$ShortName = $RowCodeList['ShortName'];
		$RowCodeList['ShortName'] = getProductTypeCode($TypeProduct, $ShortName);
		$ProductName = $RowCodeList['ShortName'];
		$DayCode = $RowCodeList['DayCode'];
		$VarietyID = $RowCodeList['VarietyID'];
		$Brand = $RowCodeList['Brand'];
		$TracebilityID = $RowCodeList['TracebilityID'];
		$PackingStyleID = $RowCodeList['PackingStyleID'];
		$TracebilityName = $RowCodeList['FarmerName'] . $RowCodeList['AddressContinued'] . '<br>' . $RowCodeList['Tracebility'];
		$DateOfReciept = $ProcessingDate = $ProductionDate = getDayCodeDate($RowCodeList['DayCode']);

		if($RowCodeList['FarmerCode']!=''){
			$FarmerCode=$RowCodeList['FarmerCode'].' - ';
		}
		$Tracebility = $FarmerCode.$RowCodeList['Tracebility'];


		$SqlYield = "SELECT * FROM `QcStandardYields` WHERE ID='$YieldID'";
		$stmtYield = $iconn->query($SqlYield);
		$RowYield = $stmtYield->fetch_array();
		$Yield = $RowYield['Yield'];


		$sqlMcs = "SELECT sum(McsTotal) as McsTotal,
		sum(TotalSlabs*SlabWeight*MFactor*McsTotal) as NetWgtKgs ,
		sum(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
		GROUP_CONCAT(TotalSlabs*SlabWeight*MFactor*McsTotal)  as GroupWeightFinished ,
        GROUP_CONCAT(Yeild)  as Yeild ,

		QCertNo,BestBeforeDt,QCVL.Date AS QCertDt,
		APS.PackingStyle FROM `QcCodeList` as QCL 
		LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
		LEFT join QcCoveringLetter as QCVL on QCVL.InvID=QCL.InvID
		left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
		left join AdminPackingStyles As APS on APS.PCKID=QCL.PackingStyleID
		WHERE QCL.InvID='$PoID'    and  QCL.CLID in ($CodeListId) 
			order by QCL.CLID ASC";
		;
		$stmtMcs = $iconn->query($sqlMcs);
		$RowMcs = $stmtMcs->fetch_array();

		$Mcs = $RowMcs['McsTotal'];
		$PackingStyle = $RowMcs['PackingStyle'];
		$QCertNo = $RowMcs['QCertNo'];
		$QCertDt = $RowMcs['QCertDt'];
		$QCertDt = date("d-m-Y", strtotime($QCertDt));
		$BestBeforeDate = date("d-m-Y", strtotime($RowMcs['BestBeforeDt']));
		if ($BestBeforeDate == '01-01-1970') {
			$BestBeforeDate = '--';
		}


		/*********************Yield Calc************************ */

		$Yield = $RowMcs['Yeild'];
		$GroupWeightFinished = $RowMcs['GroupWeightFinished']; //echo "<br>";
		if ($YieldPerc == '') {
			$Yield = $RowMcs['Yeild'];
		} else {
			$Yield = $YieldPerc;
		}




		$aryYield = explode(",", $Yield);
		$aryWeightKgs = explode(",", $GroupWeightFinished);
		//  var_dump( $aryYield); exit;
		$aryUsedQty = array();
		$aryUsedFinished = array();

		if (count($aryYield) > 1) {
			$UsedQty = 0;
			$FinishedQty = $GroupWeightFinished = $RowRMRWeights['GroupWeightFinished'];
			$i = 0;

			foreach ($aryYield as $Item) {
				$Item;


				$FinishedQty = $aryWeightKgs[$i];
				// $FinishedQty = round($FinishedQty);
				$FinishedQty = sprintf('%0.3f', ($FinishedQty), 3);// $FinishedQty . '.000';
				$aryUsedFinished[] = $FinishedQty;


				$GroupWeightRcved = ((($aryWeightKgs[$i] / $Item) * 100) + 0.001);
				$GroupWeightRcved = round($GroupWeightRcved);

				$aryUsedReceived[] = $GroupWeightRcved;//uma
				$UsedQty += $GroupWeightRcved;
				$GroupWeightRcved = $GroupWeightRcved . '.000';
				$aryUsedQty[] = $GroupWeightRcved;

				$i++;
			}
			$UsedQty = $UsedQty . '.000';
			$GroupWeightRcved = implode("<br>", $aryUsedQty);
			$FinishedQty = $GroupWeightFinished = implode("<br>", $aryUsedFinished);
		} else {
			$UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
			$ReceivedQty = $UsedQty = round($UsedQty);
			// $ReceivedQty = $GroupWeightRcved = implode("<br>", $aryUsedReceived);
			$GroupWeightRcved = $UsedQty = $UsedQty . '.000';
		}



		/**********************Yield Calc************************* */
		/***uma */
		if ($QtyUnit == 'KGS') {
			$FinishedQty = $RowMcs['NetWgtKgs'];
		} else {
			$FinishedQty = $RowMcs['NetWgtLbs'] * 0.454;
		}

		$UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
		//$ReceivedQty = $UsedQty = round($UsedQty, 3); //robo 13/03/2024
		$ReceivedQty = $UsedQty = round($UsedQty) . '.000';
		$TotalUsedQty += $UsedQty;
		$TotalRedQty += $ReceivedQty;

		$TotalFinishedQty += $FinishedQty;

		$TotalMCS += $Mcs;

		$content .= '
				<tr>  
           		<th  align="center"  width="4.5%"><font size="7">' . ++$Sno . '</font></th>
				<th  align="center" width="18%"><font size="7">' . $RowCodeList['FarmerName'] . '</font></th>
				<th  align="center"  width="' . $ColumnWidth . '%"><font size="7">' . $Tracebility. '</font></th>
				<th  align="center"  width="' . $ColumnWidth . '%"><font size="7">' . $ProductionDate . '</font></th>
                <th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $ReceivedQty . '</font></th>
                <th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $UsedQty . '</font></th>
				<th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . number_format($FinishedQty, 3) . '</font></th>
				<th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $Mcs . '</font></th>   
				<th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $ProductionDate . '</font></th>
				<th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $BestBeforeDate . '</font></th>
                <th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $DayCode . ' </font></th>
              
                
               </tr>';

		$COUNT++;
	}

	//ECHO $InvoiceNumber; EXIT;

	$content .= '<tr>  
           		<th  align="center"  width="39.7%"><font size="7">GRAND TOTAL</font></th>
                <th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $TotalRedQty . '.000</font></th>
                <th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $TotalUsedQty . '.000</font></th>
                <th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . number_format($TotalFinishedQty, 3) . '</font></th>    
                <th  align="center" width="' . $ColumnWidth . '%"><font size="7">' . $TotalMCS . '</font></th>
                <th  align="center" width="26%"><font size="7"></font></th>               
               </tr>
               <tr><th  align="center" width="100%"><font size="7">This is to certify that the
			   Raw material shrimp used for the production of the finished shrimp products for
			   export consignment vide CFE. No.' . $QCertNo . ' dt:' . $QCertDt . '& Invoice No: ' . $RowPoDetails['InvNumFull'] . ', 
			   dt:' . $CInvDate . ' was procured only from certified MPEDA farms declared by us to EIA  
			   Chennai,S.O Visakhapatnam & it is declared that this material is free from Antibiotics, 
			   Sulphites and microbiological parameters</font></th>
               </tr>
			   
			   
			   </table>';


	return $content;
}

// $pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->Cell(90, 5, 'NAME : ' . $VerifiedBy, 0, 1, 'L');
$pdf->Cell(90, 5, 'DESIGNATION :' . $Designation, 0, 0, 'L');
//$pdf->Cell(150, 5, 'COMPANY SEAL:', 0, 1, 'R');



ob_end_clean();

/*
$pdf->Output($file_name, 'I');

$pdf->Output('MpedaRegister.pdf', 'I');*/

if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $row['InvNumFull'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName . '.pdf', 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName . '.pdf';
	$pdf->Output($FileName, $FileType);
}
