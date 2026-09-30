<?php
// ini_set( 'display_errors', 1 );

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$PoID = $_GET['id'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");



$ID = $_GET['id'];
$Type = $_GET['Type'];
//include "/QualityControl/PrintHeaderInc.php";

$SqlStatus = "SELECT * FROM ScrmPoData  as SPD
left JOIN AdminExporterDetails as AED on AED.ExpID=SPD.ProcessorID
WHERE PoID='$PoID'";
$StmtStatus = $iconn->query($SqlStatus);
$PoStatus = $StmtStatus->fetch_assoc();




$CustomInvoiceSql = "SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading 
,SCF.name as FinalDestinationCountry,SPCF.Name as FinalDestination,SPCF.CountryCode as FinalDestinationCountryCode,SCI.ContainerNo
FROM ScrmCustomInvoices as SCI
left JOIN ScrmPoData as SSD on SSD.PoID=SCI.PiID
left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
left JOIN ScrmCountriesList as SCF on SCF.id=SPCF.Country
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN ScrmCountriesList as SL on SL.id=SP.Country
  left JOIN ScrmLinerMaster as SLM on SLM.LinerID=SCI.ShippingLineID
 left JOIN ScrmISFDetails as ISF on ISF.PIID=SCI.PiID


 WHERE SCI.PiID='$PoID'";
/* $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SP.Name as PortOfLoading FROM $ScrmCustomInvoices as SCV 
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading

 WHERE SCV.PiID='$PoID'";*/
$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
$IGST = $CustomInvoiceRow['IGST'];



$SqlScrmPoData = "SELECT *,SCDB.BuyerName as BuyerName,SCDB.BuyerAddress as BuyerAddress,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress
 FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'";

$StmtPoData = $iconn->query($SqlScrmPoData);
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID = $PoDataRow['ExporterID'];
$ProcessorID = $PoDataRow['ProcessorID'];
$ApprovedBy = $PoDataRow['ApprovedBy'];
$CountryID = $PoDataRow['DestinationCountry'];


$ExporterData = getExporterDetails($ExpID, $PoID, $CountryID, $DocType);
$ProcessorData = getExporterDetails($ProcessorID, $PoID, $CountryID, $DocType);


$PaymentTerms = $PoDataRow['PaymentTerms'];
$DeliveryTerms = $PoDataRow['DeliveryTerms'];
$BankID = $PoDataRow['BankID'];
$Status = $PoStatus['Status'];
$ProcessorBAPNo = $PoStatus['ProcessorBAPNo'];






class MYPDF extends TCPDF
{

	public function Header()
	{

		global $Status, $IGST;

		global $Header, $ExporterData, $ExpID;
		$HeaderLogo=$ExporterData['ExpLogo'];
		// $image_file = 'images/ds_logo.jpg';
		// if ($ExpID == 5) {
		// 	$img_file = 'https://sme.silverlights.in/qc/images/sunriselogo.jpeg';
		// 	$this->Image($img_file, 180, 7, 20, 15, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);

		// } else {
			//$img_file = 'https://sme.silverlights.in/qc/images/sme_logo.jpg';
			$this->Image($HeaderLogo, 180, 6, 20, 20, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);

		//}

		$ExpArray = str_split($ExporterData['ExpAddress'], 52);

		$this->SetFont('helvetica', 'B', 10);

		/*$Data='<table style="background-color: #99A3A4;" width="100%" cellspacing="3" cellpadding="5%">
					  <tr valign="bottom">
							<td  rowspan="10" align="center" valign="middle"
								  width="89%">'.$ExporterData['ExpName'] .'<br>'.$ExpArray[0].'<br>'.$ExpArray[1].'</td>
					  </tr></table>';*/



		//uma
		//$this->SetFillColor(169,169,169);
// $this->Cell(130, 0, '', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		//$this->SetFillColor(130,130,130);
		//$this->Cell(130, 0, '', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
// $this->MultiCell(175, 10, $ExporterData['ExpName'], 1, 'C', 1, 0, '', '', true, 0, true, true, 0);
		$this->Cell(130, 0, '', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		//$this->Cell(175, 0, ''.$ExporterData['ExpAddress'], 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', 'B', 9);
		$this->writeHTML($Data);
		//$this->writeHTML('<p>'.$ExporterData['ExpAddress'].'</p>');

		//$this->Cell(130, 0, '', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', 'BU', 10);

		//$this->Cell(130, 0, 'IMPORTER SECURITY FILING (ISF) DATA SHEET 10+2', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetTextColor(255, 0, 0);

		$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');

	}

	public function Footer()
	{
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}


$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Shipment Data');

$pdf->SetTitle('' . $Header2);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);

$pdf->SetHeaderMargin(10);

$pdf->SetMargins(10, 10, 10, 10);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetAutoPageBreak(TRUE, 10);


$pdf->SetFont('helvetica', '', 11);
//$pdf->ln;  
$pdf->AddPage();
//$pdf->Cell(40 ,15,'',0,1,'L');
//$pdf->ln;  


/*$pdf->SetLineStyle( array( 'width' => 0.1, 'color' => array(5,5,5)));
	   $pdf->Rect(6, 6, $pdf->getPageWidth()-12, $pdf->getPageHeight()-12);
	   $pdf->Rect(5.4, 5.4, $pdf->getPageWidth()-10.8, $pdf->getPageHeight()-10.8);
   */

$table_data = getDS_TABLE($iconn, $ID);
$pdf->writeHTML($table_data);
$pdf->SetFont('helvetica', '', 6);


/***$file_name=$ExporterData['InvShortCode'].'_'.$PoDataRow['InvNo'].'.pdf';**/



/***uma 28-07-2022**/
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

$file_name = $ExporterData['InvShortCode'] . '-AZ_SD-' . $CustomInvoiceRow['CInvNo'] . '-' . $CurrentYear . '-' . $NxtYear . '.pdf';
//sme/pi/233/2022-23


//echo $Type;
ob_end_clean();
if ($Type == '') {
	$Type = 'I';
} else {
	$Fname = $MailTempFolder;
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '', $file_name);
	$file_name = $Fname . $file_name;
}

$pdf->Output($file_name, $Type);





function getDS_TABLE($iconn, $ID)
{

	global $PoDataRow;

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");
	include "../SalesInculdes/SalesConstants.php";
	global $ProcessorBAPNo;
	$SqlBreakup = "SELECT * FROM `ScrmCIGradeData`  as SCD
left join AdminPackingStyles as APS on APS.PCKID=SCD.PackingStyleID
left join `ScrmBrandsMaster` SBM on  SBM.BrandName=SCD.Brand
 WHERE InvId='$ID'";
	$StmtBreakUp = $iconn->query($SqlBreakup);

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

	$SQL_PO_GRADE_DATA = "SELECT * FROM `ScrmCIGradeData` where InvId='$ID'  ";
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








	global $CustomInvoiceRow, $ExpID;
	global $PoDataRow;
	global $ExporterData;
	global $ProcessorData;
	global $TblAdminBankDetails;
	global $BankID;
	global $TblScrmPaymentTerms;
	global $TblAdminExporterDetails;
	global $SignURL, $PoID;

	//Exporter /Shipper Details

	//$ExporterID=$ExporterData['ExpID'];
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

	 $SqlVessel=" SELECT TranshipDt,ToTOPID as PortofTranshipment,
	 (SELECT VesselName FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` asc Limit 1) as originatingVessel ,
	 (SELECT VOYNO FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` asc Limit 1) as originatingVoy ,
	 (SELECT VesselName FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` desc Limit 1) as FinalImportingVessel ,
	 (SELECT VOYNO FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` desc Limit 1) as FinalImportingVoy 
	 FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` desc Limit 1;";
			
	$StmtVessel=$iconn->query($SqlVessel);
	$RowVessel=$StmtVessel->fetch_assoc();
	$VesselName=$RowTenPlusTwo['originatingVessel'];
			
	 $OriginatingVesselNumber = $RowVessel['originatingVessel'].' '.$RowVessel['originatingVoy']; 
	 $FinalVesselNumber = $RowVessel['FinalImportingVessel'].' '.$RowVessel['FinalImportingVoy']; 
	//Processor/Packer

	$ProcessorID = $PoDataRow['ProcessorID'];
	$ProcessorNamee = $ProcessorData['ExpName'];
	if ($ProcessorID == 3) {
		$SqlProcessor = "select substring_index ('$ProcessorNamee',' ',4) AS STRING  ";
		$StmtProcessor = $iconn->query($SqlProcessor);
		$RowProcessor = $StmtProcessor->fetch_assoc();
		$ProcessorName = $RowProcessor['STRING'];

		$SqlProcessor1 = "select substring_index ('$ProcessorNamee',' ',-2) AS STRING  ";
		$StmtProcessor1 = $iconn->query($SqlProcessor1);
		$RowProcessor1 = $StmtProcessor1->fetch_assoc();
		$ProcessorName1 = $RowProcessor1['STRING'];
	} else {
		$ProcessorName = $ProcessorNamee;
	}
	$ProcessorAddress = $ProcessorData['PackerAddress'];
	$ProcessorUsfdaNo = $ProcessorData['ExpUsfdaNo'];
	$ProcessorApprovalNo = $ProcessorData['ExpApprovalNo'];
	$ProcessorFEI = $ProcessorData['ExpFeiNo'];
	$CurrentMonth = strftime("%m");
	if ($CurrentMonth <= 3) {

		$NxtYear = strftime("%y");
		$CurrentYear = strftime("%Y") - 1;

	} else {
		$CurrentYear = strftime("%Y");
		$NxtYear = strftime("%y") + 1;
	}
	$InvNO = $CustomInvoiceRow['CustomsInvNo'];




	$InvDate = getFormatedDate($CustomInvoiceRow['CInvDate']);
	$PoDate = getFormatedDate($CustomInvoiceRow['CpoDate']);

	$PlaceOfReceiptBy = $CustomInvoiceRow['PlaceOfRecipt'];
	$PortOfLoading = $CustomInvoiceRow['PortOfLoading'];
	//$SealNo = $CustomInvoiceRow['SealNo'];
	$SealNo = $CustomInvoiceRow['LinerSealNo'];
	$ContainerNumber = $CustomInvoiceRow['ContainerNo'];
	$PackSealNo = $CustomInvoiceRow['PackSealNo'];
	
	$FrieghtCharge = $CustomInvoiceRow['FrieghtCharge'];
	$ExchangeRate = $CustomInvoiceRow['ExchangeRate'];
	$CInvCode = $CustomInvoiceRow['CInvCode'];
	$IGST = $CustomInvoiceRow['IGST'];
	$PortOfDischarge = $CustomInvoiceRow['PortofDischarge'];
	$FinalDestination=$CustomInvoiceRow['FinalDestination'].'  '.$CustomInvoiceRow['FinalDestinationCountryCode'].' ,'.$CustomInvoiceRow['FinalDestinationCountry'];

	$NameofRepresentative = $CustomInvoiceRow['NameofRepresentative'];
	$BLNo = $CustomInvoiceRow['BLNo'];
	$Discount = $CustomInvoiceRow['Discount'];
	//$TranshipmentPort = $CustomInvoiceRow['TranshipmentPort'];
	$TranshipmentPort = $RowVessel['PortofTranshipment'];
	$LinerCode = $CustomInvoiceRow['LinerCode'];
	$BLDate = $CustomInvoiceRow['BLDate'];
	$VOY = $CustomInvoiceRow['VOY'];
	//$ETA = getFormatedDateNA($CustomInvoiceRow['EstimatedETA']);
	$ETA = getFormatedDateNA($RowVessel['TranshipDt']);
	//$TranshipmentDate = getFormatedDateNA($CustomInvoiceRow['TranshipmentDate']);
	$TranshipmentDate = getFormatedDateNA($RowVessel['TranshipDt']);





	$SqlTenPlusTwo = "SELECT *,SLM.LinerName as SteamshipCompany FROM `ScrmLogisticsDetails` as STD 
LEFT JOIN ScrmLogisticsMaster as SL on SL.ID=STD.ConsolidatorNameAddress
LEFT JOIN ScrmLinerMaster as SLM on SLM.LinerID=STD.SteamshipCompany 

 WHERE PiID='$PoID'";
	$StmtTenPlusTwo = $iconn->query($SqlTenPlusTwo);
	$RowTenPlusTwo = $StmtTenPlusTwo->fetch_assoc();


	$VesselName = $RowTenPlusTwo['VesselName'];
	$VOY = $RowTenPlusTwo['VOY'];
	$VesselDepartureDate = date('d-m-Y', strtotime($RowTenPlusTwo['VesselDepartureDate']));
	$ImportingVessel = $RowTenPlusTwo['ImportingVessel'];
	$ImportingVOY = $RowTenPlusTwo['ImportingVOY'];
	$SteamshipCompany = $RowTenPlusTwo['SteamshipCompany'];
	$ShippingToNameAddress = $RowTenPlusTwo['ShippingToNameAddress'];
	$ConsolidatorNameAddress = $RowTenPlusTwo['Address'];

	$PoNo = $PoDataRow['PoNo'];
	//$PoDate=getFormatedDate($PoDataRow['PoDate']);

	$PoDate = "& Dated :" . $PoDate;
	if ($PoNo == '') {

		$PoDate = ' ';
	}

	if ($VesselDepartureDate == '01-01-1970') {

		$VesselDepartureDate = 'NA';

	} else {
		$VesselDepartureDate;
	}
	$ConsigneeID = $PoDataRow['ConsigneeID'];
	$BuyerID = $PoDataRow['BuyerID'];



	if ($BuyerID == '' || $BuyerID == 0) {
		$ConsigneeName = $PoDataRow['ConsigneeName'];
		$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];
	} else if ($ConsigneeID != $BuyerID) {
		$ConsigneeName = $PoDataRow['BuyerName'];
		$ConsigneeAddress = $PoDataRow['BuyerAddress'];
	} else {
		$ConsigneeName = $PoDataRow['ConsigneeName'];
		$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];

	}
	/*$PortOfDischarge=$PoDataRow['Name'];
				$FinalDestination=$PoDataRow['FinalDestination'];*/
	$DestinationCountry = $PoDataRow['name'];
	$ByPayment = $PoDataRow['ByPayment'];
	$PreparedBy = $PoDataRow['PreparedBy'];
	$PaymentTerms = $PoDataRow['PaymentName'];
	$DeliveryTerms = $PoDataRow['DeliveryTerms'];
	$ShippingTerms = $PoDataRow['ShippingTerms'];
	$QtyUnit = $PoDataRow['QtyUnit'];





	$content = '';


	$size = 45;
	$size1 = 55;
	$FontSize = 9;


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
	$w1 = '28%';
	$w2 = '59%';
	$w4 = '10%';
	$w3 = '25%';
	$w5 = '3%';

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


	$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$ID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	$NetWgtLbs = $RowTotal['NetWgtLbs'];
	//$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	$TotalGrossWtKgs = $CustomInvoiceRow['TotalGrossWt'];
	if($TotalGrossWtKgs!=''){
		if($QtyUnit=='KGS'){
		//$GrossWeightKgs=$TotalGrossWtKgs;
		$GrossWeightLbs=$TotalGrossWtKgs*2.204;
		}else{
		//$GrossWeightKgs=$TotalGrossWtKgs*0.454;
		$GrossWeightLbs=$TotalGrossWtKgs;
		}
	}
	else{
		//$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];			
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	}
	// if ($QtyUnit == 'KGS') {

	// 	$NetWgtKgs = $RowTotal['NetWgtKgs'];
	// 	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];


	// } else {
	// 	$NetWgtKgs = $NetWgtLbs * 0.454;
	// 	$GrossWeightKgs = $RowTotal['GrossWeightLbs'] * 0.454;


	// }

	//$GrossWeightLbs=$RowTotal['GrossWeightLbs'];	
	if ($PoDataRow['QtyUnit'] == 'KGS') {
		$Weight = $RowTotal['NetWgtKgs'];
		//$Weight = $RowTotal['NetWgtLbs']*0.454;	
		$Price = $RowTotal['PriceKgs'];
	} else {
		$Weight = $RowTotal['NetWgtLbs'];
		//$Weight = $RowTotal['NetWgtKgs'];
		$Price = $RowTotal['PriceLbs'];
	}

	if ($IGST == 'DISCOUNT') {

		$DiscountAmount = ($Price * $Discount) / 100;
		$TotalDiscount = $Price - $DiscountAmount;
	} else {
		$TotalDiscount = $Price;
	}


	$content .= '<table border="0" cellspacing="0" cellpadding="0">  
							

				<tr> <br> <br> <br><br> <br><br> 
						   <th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"> </font></th>

           		<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Supplier Name </font></th>
                <th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">
				<b>' . $ExporterName . '</b></font></th>				
           </tr>
		   <br> 
		   <tr> 
		   		   <th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"> </font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Buyer Name</font></th>
                           		<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

				<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"><b> ' . $ConsigneeName . '</b></font></th>				
           </tr>
		   
		  

		   <tr> 
		   <br>           
		   <th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"> </font></th>

		   
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Payment Terms</font></th>
                           		<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>
      
				<!--<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> 
				<B>' . $PaymentTerms . '</b></font></th>	-->
				<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"> 
				<B></b></font></th>				
           </tr> 

		   <tr>
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Shipment Terms</font></th>
                           		<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

				<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $ShippingTerms . '  ' . strtoupper($FinalDestination) . '</font></th>				
           </tr>
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Country of Origin</font></th>
                           		<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

				<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"><b>' . strtoupper($CustomInvoiceRow['PortOfLoadingCountry']) . '</b></font></th>				
           </tr>
		   <tr>
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Purchase Order Number </font></th>
                <th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

			   <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $PoNo . '</font></th>				
           </tr> 
		   
		  
		   
		  
		   <tr> 
<br>
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>
		   
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Invoice Number </font></th>
                           		<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

				<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $InvNO . '</font></th>				
           </tr>
		   <tr> 
<br>		  
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>
 
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Invoice Date</font></th>
                 <th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>
  
				<th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . getFormatedDate($InvDate) . '</font></th>				
           </tr> 
		   
		  <tr> 
<br>		  
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Total Invoice Amount</font></th> 
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . number_format($TotalDiscount, 2) . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Total Pounds Shipped</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . number_format($GrossWeightLbs, 3) . '</font></th>				
           </tr> 

		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">BL Number</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $BLNo . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Container Number</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $ContainerNumber . '</font></th>				
           </tr> 
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Seal Number</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $SealNo . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">SCAC CODE</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $LinerCode . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">BAP # of Plant</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $ProcessorBAPNo . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Port Of Loading</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . strtoupper($PortOfLoading) . ',' . strtoupper($CustomInvoiceRow['PortOfLoadingCountry']) . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Shipped Onboard Date</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">
				' . getFormatedDate($BLDate) . '</font></th>				
           </tr> 
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Originating Vessel Name</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">
				' . $OriginatingVesselNumber . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Port Of Transhipment</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' .strtoupper( $TranshipmentPort) . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Date Of Transhipment</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $TranshipmentDate . '</font></th>				
           </tr> 
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Final Importing Vessel Name</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $FinalVesselNumber. '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Port Of Discharge</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . strtoupper($PortOfDischarge) . '  ' . $CustomInvoiceRow['CountryCode'] . ' , ' . strtoupper($CustomInvoiceRow['PortofDischargeCountry']) . '</font></th>				
           </tr> 
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">ETA Port Of Discharge Date</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $ETA . '</font></th>				
           </tr> 
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">Final Destination</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . strtoupper($FinalDestination) . '</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="' . $w5 . '" align="left"><font size="' . $FontSize . '"></font></th>

				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">ETA Final Destination Date</font></th>
				<th width="' . $w4 . '" align="left"><font size="' . $FontSize . '">  : </font></th>

                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">' . $ETA . '</font></th>				
           </tr>
		 
		   
		   
		</table>
				</th>
				
				
				<th width="100%"><br>
				
<div class="row">
  <div class="column">
  <table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="CENTER"  ><font size="7" ><b>Item Size</b></font></th>
				<th  align="CENTER" ><font size="7"><b> Item Description</b></font></th>
				<th  align="CENTER" ><font size="7"><b>Package</b></font></th>
				<th  align="CENTER"  ><font size="7" ><b>Quantity (Cases)</b></font></th>
				<th  align="CENTER" ><font size="7"><b>Price Per LB</b></font></th>
				<th  align="CENTER" ><font size="7"><b>Brand Name</b></font></th>
				
	
				</tr>';
	while ($RowBreakup = $StmtBreakUp->fetch_assoc()) {
		$Grade = $RowBreakup['Grade'];
		$Mcs = $RowBreakup['Mcs'];
		$Price = $RowBreakup['Price'];
		
		$Variety = $RowBreakup['Variety'];
		$PackingStyle = $RowBreakup['PackingStyle'];
		$BrandPrintStatus = $RowBreakup['PrintStatus'];
		
		if($BrandPrintStatus){
			$Brand = $RowBreakup['Brand'];
			}
			else{
			$Brand='-';
			
			}

		$content .= '
				
				
				<tr> 
				
				<th  align="CENTER"  ><font size="' . $FontSize . '" >' . $Grade . '</font></th>
				<th  align="CENTER"  ><font size="' . $FontSize . '" >' . $Mcs . ' CARTONS OF ' . $Variety . '</font></th>
				<th  align="CENTER"  ><font size="' . $FontSize . '" >' . $PackingStyle . '</font></th>
				<th  align="CENTER"  ><font size="' . $FontSize . '" >' . $Mcs . '</font></th>
				<th  align="CENTER"  ><font size="' . $FontSize . '" >' . $Price . '</font></th>
				<th  align="CENTER"  ><font size="' . $FontSize . '" >' . $Brand . '</font></th>
				
				</tr>';

	}

	$content .= '	
				
				
				</table>
			
</div>
  </div>
					</th>
				
				</tr> 
				
		
				
				</table>
				</th>
				</tr>
				
				';


	$content .= '</table>';
	return $content;

}


?>