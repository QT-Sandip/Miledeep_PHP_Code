<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
//include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

$PoID = $_GET['id'];
$IsHeader = 1;
$IsFooter = 1;
include "PrintHeaderInc.php";

// $IsHeader = 1;
// $IsFooter = 1;


include($_SERVER['DOCUMENT_ROOT'] . "/" . $_SESSION["IncludePath"] . "SalesInculdes/SalesConstants.php");
//include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Includes/Globals.php");



$ID = $_GET['id'];
$Type = $_GET['Type'];


$SqlStatus = "SELECT * FROM ScrmPIData  WHERE PoID='$PoID'";
$StmtStatus = $iconn->query($SqlStatus);
$PoStatus = $StmtStatus->fetch_assoc();


$CustomInvoiceSql = "SELECT *,SPCFD.Name AS FinalDst,SPCFD.CountryCode AS CountryCode,SPC.Name as PortofDischarge,SCV.ContainerNo,
	 SP.Name as PortOfLoading 
	 FROM ScrmCustomInvoices as SCV 
	  left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID
	 left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
	 left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
	 left JOIN ScrmPortCities as SPCFD on SSD.FinalDestination=SPCFD.PortId
	 left JOIN ScrmISFDetails as ISF on ISF.PIID=SCV.PiID

	 WHERE SCV.PiID='$PoID'";
$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
$IGST = $CustomInvoiceRow['IGST'];



$SqlScrmPoData = "SELECT * FROM ScrmPoData as SPD 
	 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
	 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
	 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
	 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
	 WHERE PoID='$PoID'";


$StmtPoData = $iconn->query($SqlScrmPoData);
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID = $PoDataRow['ExporterID'];
$ProcessorID = $PoDataRow['ProcessorID'];
$ApprovedBy = $PoDataRow['ApprovedBy'];


$SqlLogos = "SELECT LogoUrl,HeaderUrl,FooterUrl,WaterMark,ExpName FROM  `QcDocLetterHeads` as QDLH
	left join `AdminExporterDetails` as AED on QDLH.ProcessorID=AED.ExpID where QDLH.ProcessorID='$ProcessorID'";
$StmtLogos = $iconn->query($SqlLogos);
$RowLogos = $StmtLogos->fetch_array();

$ExporterData = getExporterDetails($ExpID);
$ProcessorData = getExporterDetails($ProcessorID);

$PaymentTerms = $PoDataRow['PaymentTerms'];
$DeliveryTerms = $PoDataRow['DeliveryTerms'];
$BankID = $PoDataRow['BankID'];
$Status = $PoStatus['Status'];

//   class MYPDF extends TCPDF { 

//     public function Header() {


// global $Header;		
//         $image_file = 'images/ds_logo.jpg';
//         $this->SetFont('helvetica', 'B', 10);
// 		// $this->Cell(150, 0, 'ANNEXURE "C" TO TRADE FACILITY CIRCULAR No: 01/2017'.$Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
// 		//         $this->SetFont('helvetica', '', 10);

// 		// $this->Cell(110, 0, 'Dated.06.09.2017', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
// 		// $this->Cell(125, 0, 'Examination Report for Self-sealed container', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
// 		 $this->SetTextColor(255,0,0);

// 		$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');

//     }

//     public function Footer() {
//         $this->SetFont('helvetica', 'I', 8);
// 		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
// 		$this->SetY(-5);
//    }
// }




if ($Type == '') {
	$Type = 'I';
	//$Header=' -  VIEW ONLY';
}
// $this->SetFont('helvetica', '', 8);

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('');
//$pdf->SetTitle(''.$Header2);  
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetHeaderMargin(40);
$pdf->SetMargins(10, 40, 10, 5);


$pdf->SetFont('helvetica', '', 11);

$pdf->AddPage();



$CurrentMonth = date('n', strtotime($CustomInvoiceRow['CInvDate']));
if ($PoDataRow['ExporterID'] == 5) {
	if ($CurrentMonth <= 3) {


		$NxtYear = date('y', strtotime($CustomInvoiceRow['CInvDate']));
		$CurrentYear = date('y', strtotime($CustomInvoiceRow['CInvDate'])) - 1;

	} else {

		$CurrentYear = date('y', strtotime($CustomInvoiceRow['CInvDate']));
		$NxtYear = date('y', strtotime($CustomInvoiceRow['CInvDate'])) + 1;

	}
} else {
	if ($CurrentMonth <= 3) {



		$NxtYear = date('y', strtotime($CustomInvoiceRow['CInvDate']));
		$CurrentYear = date('Y', strtotime($CustomInvoiceRow['CInvDate'])) - 1;


	} else {

		$CurrentYear = date('Y', strtotime($CustomInvoiceRow['CInvDate']));
		$NxtYear = date('y', strtotime($CustomInvoiceRow['CInvDate'])) + 1;

	}
}
/***uma 28-07-2022**/

$file_name = $ExporterData['InvShortCode'] . '-A.C-' . $CustomInvoiceRow['CInvNo'] . '-' . $CurrentYear . '-' . $NxtYear . '.pdf';
//sme/pi/233/2022-23

//echo $Type;

/*ob_end_clean();
   $pdf->Output($file_name, $Type);
   */




//function getDS_TABLE($iconn,$ID){

global $PoDataRow, $PoID;

//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");
//include "../SalesInculdes/SalesConstants.php"; 



$F_NAME = '';
$T_O_MCS = '';
$DOE = '';
$DOS = '';
$TOTAL_MCS = 0;
$TOTAL_SLAB = 0;
$T_EACH_SLAB = 0;
$TOTAL_NET_WT_KGS = 0;
$TOTAL_NET_WT_LBS = 0;


//uma

$SQL_PO_GRADE_DATA = "SELECT * FROM `ScrmPoGradeData` where InvId='$ID'  ";
$STMT_PO_GRADE_DATA = $iconn->query($SQL_PO_GRADE_DATA);

while ($PO_GRADE_DATA = $STMT_PO_GRADE_DATA->fetch_assoc()) {

	$SLAB = $PO_GRADE_DATA['SLAB'];
	$TOTAL_SLAB += $SLAB;

	$EACH_SLAB = $PO_GRADE_DATA['EACH_SLAB'];
	$T_EACH_SLAB += $EACH_SLAB;

	$MCS = $PO_GRADE_DATA['Mcs'];
	$TOTAL_MCS += $MCS;

	$UOM = $PO_GRADE_DATA['UOM'];
	/*  $BAP=	$PO_GRADE_DATA['BAP']; 
						  if($BAP=='NA'){
							 $BAP='';  
						   }
						  ELSE{
							$BAP=$BAP;  
						  }*/

	if ($UOM == "LBS") {
		$NET_WT_LBS = (($SLAB * ($EACH_SLAB)) * $MCS) . "LBS";
		// $NET_WT_KGS=(($SLAB*($EACH_SLAB*453))*$MCS)/1000 ."KGS"; 
		$NET_WT_KGS = (($SLAB * ($EACH_SLAB)) * $MCS) / 2.2 . "KGS";


	} else if ($UOM == "KGS") {
		$NET_WT_KGS = (($SLAB * ($EACH_SLAB)) * $MCS) . "KGS";


		$NET_WT_LBS = (($SLAB * ($EACH_SLAB * 2.2)) * $MCS) . "LBS";

	}
	$TOTAL_NET_WT_KGS = $NET_WT_KGS + $NET_WT_KGS;
	$TOTAL_NET_WT_LBS = $NET_WT_LBS + $NET_WT_LBS;
}
$TOTAL_NET_WT_KGS;
$TOTAL_NET_WT_LBS;
$TOTAL_MCS;






$ExporterName = $ExporterData['ExpName'];
$ExporterAddress = $ExporterData['ExpAddress'];
$ExporterUsfdaNo = $ExporterData['ExpUsfdaNo'];
$ExporterApprovalNo = $ExporterData['ExpApprovalNo'];
$ExporterIeCustomsNo = $ExporterData['ExpIeCustomsNo'];
$ExporterGstNo = $ExporterData['ExpGstNo'];
$ExporterIeCode = $ExporterData['ExpIeCode'];
$ExpPanNumber = $ExporterData['ExpPanNumber'];
$ExpBranchCode = $ExporterData['ExpBranchCode'];
$AuthSign = $ExporterData['AuthSign'];
$InvShortCode = $ExporterData['InvShortCode'];

//Processor/Packer

$ProcessorID = $PoDataRow['ProcessorID'];

$ProcessorAddress = $ProcessorData['ExpAddress'];
$ProcessorUsfdaNo = $ProcessorData['ExpUsfdaNo'];
$ProcessorApprovalNo = $ProcessorData['ExpApprovalNo'];
$ProcessorFEI = $ProcessorData['ExpFeiNo'];


$InvNO = $InvShortCode . $CustomInvoiceRow['CInvNo'] . '/' . $CurrentYear . '-' . $NxtYear;




$InvDate = getFormatedDate($CustomInvoiceRow['CInvDate']);
$PoDate = getFormatedDate($CustomInvoiceRow['CpoDate']);

$PlaceOfReceiptBy = $CustomInvoiceRow['PlaceOfRecipt'];
$PortOfLoading = $CustomInvoiceRow['PortOfLoading'];
$SealNo = $CustomInvoiceRow['SealNo'];
$ContainerNumber = $CustomInvoiceRow['ContainerNo'];
$PackSealNo = $CustomInvoiceRow['PackSealNo'];
$VesselNumber = $CustomInvoiceRow['VesselNumber'];
$FrieghtCharge = $CustomInvoiceRow['FrieghtCharge'];
$ExchangeRate = $CustomInvoiceRow['ExchangeRate'];
$CInvCode = $CustomInvoiceRow['CInvCode'];
$IGST = $CustomInvoiceRow['IGST'];
$PortOfDischarge = $CustomInvoiceRow['PortofDischarge'];
//$FinalDestination=$CustomInvoiceRow['FinalDestination'];
$CountryCode = $CustomInvoiceRow['CountryCode'];
$FinalDestination = $CustomInvoiceRow['FinalDst'] . ' ' . $CountryCode;

$NameofRepresentative = $CustomInvoiceRow['AuthoritySign'];
$StuffingDate = getFormatedDate($CustomInvoiceRow['StuffingDate']);
$StuffingStartTime = $CustomInvoiceRow['StuffingStartTime'];
$StuffingCompletionTime = $CustomInvoiceRow['StuffingCompletionTime'];
$SBNo = $CustomInvoiceRow['SBNo'];
$SBDate = getFormatedDate($CustomInvoiceRow['SBDate']);
$ContainerWeight = $CustomInvoiceRow['ContainerWeight'];

$SBNoDate = $SBNo . '/' . $SBDate;
$TotalStuffingTime = round(abs(strtotime($StuffingCompletionTime) - strtotime($StuffingStartTime)) / 3600, 2) . ' hrs';

$SqlUserDetails = "SELECT * FROM `AdminUserDetails` WHERE UserID='$NameofRepresentative' ";
$StmtUserDetails = $iconn->query($SqlUserDetails);
$RowUserDetails = $StmtUserDetails->fetch_assoc();
$ContactNo = $RowUserDetails['ContactNo'];

$PoNo = $PoDataRow['PoNo'];
//$PoDate=getFormatedDate($PoDataRow['PoDate']);

$PoDate = "& Dated :" . $PoDate;
if ($PoNo == '') {

	$PoDate = ' ';
}

$ConsigneeName = $PoDataRow['ConsigneeName'];
$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];


/*$PortOfDischarge=$PoDataRow['Name'];
			$FinalDestination=$PoDataRow['FinalDestination'];*/
$DestinationCountry = $PoDataRow['name'];
$ByPayment = $PoDataRow['ByPayment'];
$PreparedBy = $PoDataRow['PreparedBy'];
$PaymentTerms = $PoDataRow['PaymentName'];
$DeliveryTerms = $PoDataRow['DeliveryTerms'];
$ShippingTerms = $PoDataRow['ShippingTerms'];
$QtyUnit = $PoDataRow['QtyUnit'];

$SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 
				FromTOPID='$PortOfLoading' and PIID='$ID'";
//exit;
$StmtOrgVessel = $iconn->query($SqlOrgVessel);
$RowOrgVessel = $StmtOrgVessel->fetch_assoc();

$OrgVesselName = $RowOrgVessel['VesselName'] . ' ' . $RowOrgVessel['VOYNO'];
//$OrgVOYNO=$RowOrgVessel['VOYNO'];

// $SqlTotal = "SELECT  sum(Mcs*GrossWeight*MFactor*TotalSlabs) as GrossWeightKgs 
// 			 FROM `ScrmPoGradeData` 
// 			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$ID'"; 
$SqlTotal = "SELECT  sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs FROM `ScrmPoGradeData` as PGD 
	left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID 
	WHERE InvId='$ID'";
$StmtTotal = $iconn->query($SqlTotal);
$RowTotal = $StmtTotal->fetch_assoc();
$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
// $TotalGrossWt = $CustomInvoiceRow['TotalGrossWt'];
$TotalGrossWtKgs = $CustomInvoiceRow['TotalGrossWt'];
/**uma 20-12-2023* */

if ($TotalGrossWtKgs != '') {
	if ($QtyUnit == 'KGS') {
		$GrossWeightKgs = $TotalGrossWtKgs;
		//$GrossWeightLbs=$TotalGrossWtKgs*2.204;
	} else {
		$GrossWeightKgs = $TotalGrossWtKgs * 0.454;
		//$GrossWeightLbs=$TotalGrossWtKgs;
	}
} else {
	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	//$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];
}

/**uma 20-12-2023* */

// ECHO $GrossWeightKgs;  EXIT;

if ($ContainerWeight != null && $ContainerWeight != 0) {
	$GrossMassweightkgs = $GrossWeightKgs + $ContainerWeight;
} else {
	$GrossMassweightkgs = $GrossWeightKgs + 4700;
}


$content = '';


$size = 45;
$size1 = 55;
$FontSize = 10;


$Whitspace = '<b style="color:white"> yuhju</b>';

$StyleTbl = 'Style="border: 1px solid #ccc;
	  color: #37393b;
	  margin: 10px 0 0 0;
	  text-shadow: #fff 1px -1px 1px;
	  text-align: left;
	  width: 100%;"';

$StyleTd = 'Style=" margin: 0;
	  padding: 0;
	  border: 0;
	  font-size: 100%;
	  vertical-align: baseline;
	  background: transparent;"';
$w1 = '15%';
$w2 = '50%';
$w3 = '35%';
$content .= '
	 <h4 align="right">Annex-1</h4>
	 <h3 align="center"><U>INFORMATION ABOUT VERIFIED GROSS MASS OF CONTAINER</U></h3>'
;
$content .= '<table border="1" cellspacing="0" cellpadding="5px">  
					
					<tr> 
					<th width="' . $w1 . '" align="center"><font size="' . $FontSize . '"><b> Sr.No</b></font></th>
					<th width="' . $w2 . '" align="center"><font size="' . $FontSize . '"><b>Details of Information</b></font></th>
					<th width="' . $w3 . '" align="center"><font size="' . $FontSize . '"><b>Particulars</b></font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 1* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> Name of the shipper </font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '"> ' . $ExporterName . ',<br>
					' . $ExporterAddress . '</font></th>				
			   </tr> 

			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 2* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> Shipper Registration/License No.(IEC No/CIN No)
					</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '"> ' . $ExporterIeCode . '</font></th>				
			   </tr> 
			   
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 3* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> Name and designation of official of the shipper
					authorised to sign document
					</font></th>
					<!--<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '"> ' . $PreparedBy . '<br><br>
					AUTHORISED SIGNATORY</font></th>-->
					
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '"> ' . $RowUserDetails['UserName'] . '<br><br>
					AUTHORISED SIGNATORY</font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 4* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> 24 x 7 contact details of authorised official of shipper
					</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '">' . $ContactNo . ' </font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 5* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> Container No.</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '"> ' . $ContainerNumber . '
					</font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 6* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> Container Size(TEU/FEU/Other)</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '"> 40FT
					</font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 7* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> Maximum permissible weight Of container
					as per the CSC plate</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '">34,000.000 KGS</font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 8* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">Verified gross mass of container(method-1/method-2)</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '">' . number_format($GrossMassweightkgs, 3) . '(METHOD-2)  </font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 9* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">Date and time of weighing</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '"> ' . $InvDate . ' & 11.00 AM </font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 10* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">Type(Normal/Reefer/Hazardous/others)</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '">REFER  </font></th>				
			   </tr> 
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 11* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">If Hazardous, UN No,IMDG Class</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '"> <U>N.A</U> </font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 12* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">Vessel Name and VCN No</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '">  </font></th>				
			   </tr>
			   <tr> 
					<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"> 13* </font></th>
					<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">Final Destination</font></th>
					<th width="' . $w3 . '" align="left"><font size="' . $FontSize . '">' . strtoupper($FinalDestination) . ' ' . strtoupper($DestinationCountry) . '  </font></th>				
			   </tr>
	</table>';
//$table_data=getDS_TABLE($iconn,$ID);
$pdf->writeHTML($content);
$pdf->SetFont($FONT_ARI, '', 10);
//$pdf->Cell(190 ,$H1,'','T',1,'R');
//$pdf->Ln();
$pdf->Cell(190, 10, 'Signature of authorised person of shipper', 0, 1, 'R');
$pdf->Cell(190, 10, 'Name:' . $RowUserDetails['UserName'], 0, 1, 'R');
$pdf->Cell(190, 10, 'Date:' . $InvDate, 0, 1, 'R');
$pdf->SetFont('helvetica', '', 6);
$file_name = $ExporterData['InvShortCode'] . '-A.C-' . $CustomInvoiceRow['CInvNo'] . '-' . $CurrentYear . '-' . $NxtYear . '.pdf';

ob_end_clean();
$pdf->Output($file_name, $Type);

// return $content;

//}


?>