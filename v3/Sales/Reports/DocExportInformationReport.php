<style>
	th {
		font-size: 12px;
		text-align: center;
		background: white;
		position: sticky;
		top: 0;
		left: 260px;
	}

	.sticky-row {
		top: 0;
		position: -webkit-sticky;
		position: sticky;
		background-color: white;
	}

	.sticky-col {
		position: -webkit-sticky;
		position: sticky;
		background-color: white;
		/* top: 13%; */
	}

	td {
		font-size: 14px;
		min-width: 150px;
		align-items: center;
		text-align: center;
	}

	.first-col {
		width: 100px;
		min-width: 100px;
		max-width: 100px;
		left: 0px;
	}

	.second-col {
		width: 150px;
		min-width: 150px;
		max-width: 150px;
		left: 100px;
	}
</style>


<?php
//ini_set( 'display_errors', 1 );

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

/* $Month = $_GET['Month'];
$Year = $_GET['Year'];
$B_TYPE = $_GET['TYPE'];
$BAR_CODE_ID = $_GET['BID'];
$LOT_ID = $_GET['ID'];

$from_date = $_GET['from_date'];
$to_date = $_GET['to_date']; */

$Type = $_GET['Type'];

if ($Type == 2 || $Type == 1) {

	$Month = $_GET['Month'];
	$Year = $_GET['Year'];
	$PsID = $_GET['PsID'];
} else {
	$Month = $_GET['from_date'];
	$Year = $_GET['to_date'];
}

$Exporter = $_GET['Exporter'];

if ($Type == 1) {
	$Query = "month(CInvDate)='$Month'  and year(CInvDate)='$Year'  ";
} else {
	$Query = "CInvDate between '$Month'  and '$Year'  ";
}

if ($Exporter == '') {
	$Query .= '';
} else {
	// if($Exporter==1){
	// 	$Query.='and SPD.ExporterID!=5';
	// }
	// 	else{

	$Query .= ' and SPD.ExporterID=' . $Exporter;
	//}
	$SqlExporter = "SELECT * FROM AdminExporterDetails where ExpID=$Exporter";
	$StmtExporter = $iconn->query($SqlExporter);
	$RowExporter = $StmtExporter->fetch_array();
	$ExporterName = $RowExporter['ExpName'];
}

$monthName = date("F", mktime(0, 0, 0, $Month, 10));

if ($Type == 1) {
	$Header = 'Export Information Report-(' . $monthName . ')';
} else {
	$Header = 'Export Information Report-(' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}
$file_name = 'Export Information Report -' . getFormatedDate($Month) . '-' . getFormatedDate($Year) . '.xlsx';

$date_end = $to_date . ' 23:59:59';




$SqlPo = "
		Select
			SCI.*,
			SPD.*,
			SBD.BuyerName,
			SCD.ConsigneeName As Consignee,
			SCL.name As DestinationCountry,
			SPC.Name AS PortofDischarge,
			SPC.CountryCode AS CountryCode,
			SC.name AS PortofDischargeCountry,
			SL.name AS PortOfLoadingCountry,
			SP.Name AS PortOfLoading,
			SCF.name AS FinalDestinationCountry,
			SPCF.Name AS FinalDestination,
			SPCF.CountryCode AS FinalDestinationCountryCode,
			SCPT.PaymentName,
			SEB.EbrcReleasedUSD,SEB.EbrcNumber,SEB.EbrcDate,
			SEB.EbrcDate2,SEB.EbrcDate3,SEB.EbrcDate4,	
			SEB.AdvanceRefNo,SEB.AdvanceDate,SEB.LcNumber,
			SEB.LcDate,SEB.LcReceiptFrom,SEB.AwbNumber,
			SEB.AwbDate,SEB.RoadTransport,SEB.OceanFreightINR,
			SEB.FreightCostPerKg,SEB.ThcNOthers,
			SID.SealNo,SID.BLNo,
			SSBD.ShippingBillNo,SSBD.ShippingBillDate,SSBD.LEODate,
			SSBD.SBFOBUSD,SSBD.SBFOBINR,
			SLM.LinerName,SLM.LinerAddress, SLM.LinerCode,
			FDA.ContainerGateOutDate,FDA.FDAEUHoldDate,FDA.FDAEUReleaseCFIADate,
			FDA.FDAEntryNumber,FDA.FDAEntryDate,FDA.ActualPaidUSD,
			FDA.EmptyContainerEntryNumber,FDA.EmptyContainerReturnDate,
			FDA.FDAContainerStatus,FDA.ShippingNotes,
			SLD.VesselName,SLD.VOY,SLD.VesselDepartureDate,SLD.VesselNumber,
			SEAD.LocalAgentName As LocalAgent,
			SEAD.OverseasAgent,
			SEAD.BillRealizationDate,
			SEAD.RealizationEarlyDate,
			SEAD.RealizationLateDate,
			SLL.Name AS CHA,
			(SELECT GROUP_CONCAT(BankRefNo) FROM 
			ScrmCollectionBreakUP Where PiID = SPD.PoID) AS BankRefNo,
			(SELECT	GROUP_CONCAT(DATE_FORMAT(ColDate, '%d-%m-%Y'))  FROM 
			ScrmCollectionBreakUP Where PiID = SPD.PoID) AS ColDate,
			SCMD.LcNo,SCMD.LcDate,SCMD.Freight As TotalFreight,
			SCAD.ClearingAgentInvoiceNo,SCAD.ClearingAgentInvoiceDate,
			SCAD.ADCharges,SCAD.CVDCharges,SCAD.RTCharges,
			SCAD.MPCharges,SCAD.CandFCharges,SCAD.HmDutyCharges,
			(SELECT GROUP_CONCAT(DISTINCT DayCode) FROM QcCodeList Where InvID = SPD.PoID) AS DayCode
		FROM
			ScrmCustomInvoices AS SCI
			LEFT JOIN ScrmPoData AS SPD ON SPD.PoID = SCI.PiID
			LEFT JOIN ScrmBuyerDetails AS SBD ON SBD.BuyerID = SPD.BuyerID
			LEFT JOIN ScrmConsigneeDetails AS SCD ON SCD.ConID = SPD.ConsigneeID
			LEFT JOIN ScrmCountriesList AS SCL ON SCL.id = SPD.DestinationCountry
			LEFT JOIN ScrmShipmentDetails AS SSD ON SSD.PiID = SCI.PiID
			LEFT JOIN ScrmShipmentDispatchDetails AS SD ON SD.PIID = SCI.PiID
			LEFT JOIN ScrmLogisticsDetails AS SLD ON SLD.PiID = SCI.PiID
			LEFT JOIN ScrmPortCities AS SPC ON SPC.PortId = SPD.PortofDischarge
			LEFT JOIN ScrmPortCities AS SP ON SP.PortId = SPD.PortOfLoading
			LEFT JOIN ScrmCountriesList AS SC ON SC.id = SPC.Country
			LEFT JOIN ScrmCountriesList AS SL ON SL.id = SP.Country
			LEFT JOIN ScrmPortCities AS SPCF ON SPD.FinalDestination = SPCF.PortId
			LEFT JOIN ScrmCountriesList AS SCF ON SCF.id = SPCF.Country
			LEFT JOIN ScrmPaymentTerms AS SCPT ON SCPT.ID = SPD.PaymentTerms
			LEFT JOIN ScrmEIRBankNFieghtDetaails AS SEB ON SEB.PoRefNumber = SPD.PoID
			LEFT JOIN ScrmEirAgentNDiscountDetails AS SEAD ON SEAD.PoRefNumber = SPD.PoID
			LEFT JOIN ScrmISFDetails AS SID ON SID.PIID = SPD.PoID
			LEFT JOIN ScrmShippingBillDetails AS SSBD ON SSBD.InvID = SPD.PoID
			LEFT JOIN ScrmEirFdaNOtherDetails AS SEF ON SEF.PoRefNumber = SPD.PoID
			LEFT JOIN ScrmLogisticsMaster AS SLL ON SLL.ID = SLD.ConsolidatorNameAddress
			LEFT JOIN ScrmLinerMaster AS SLM ON SLM.LinerID = SCI.ShippingLineID
			LEFT JOIN ScrmFdaDetails AS FDA ON FDA.PoID = SPD.PoID
			LEFT JOIN ScrmShipmentTermsMaster AS SSTM ON SSTM.TermName = SPD.ShippingTerms
			LEFT JOIN ScrmCommercialData AS SCMD ON SCMD.PoID = SPD.PoID
			LEFT JOIN ScrmClearingAgentDetails AS SCAD ON SCAD.PoID = SPD.PoID
		WHERE
			$Query
		ORDER BY
			CInvNo ASC,
			CInvDate ASC
		";

// echo $SqlPo;exit;
$StmtPo = $iconn->query($SqlPo);


$content = '';


$size = 40;

$size1 = 60;





$S_NO = 1;
$width = 11;
$width_s = 8;
$width_w = $width + 5;

$content .= '
				<h3 align="center">' . $ExporterName . '</h3>
			  <h3 align="center">' . $Header . '</h3>
			  
			  
			  <div class="view">
  				<div class="wrapper">
				<table   border="1" cellspacing="0" cellpadding="5">
				<thead>  
                <td class="sticky-row  first-col" align="center"><B>S No.</B></td>             
				<td class="sticky-row  second-col" align="center"><B>Invoice #</B></td>
				<th align="center" width="15%"><B>Date</B></th>	
				<th align="center"><B>Buyer Order #</B></th>					   
				<th align="center"><B>Buyer Order Date</B></th>
				<th align="center" width="35%"><B>Name of the Buyer</B></th>					     
				<th align="center"><B>Country</B></th>					   
				<th align="center"><B>Local Agent</B></th>			
				<th align="center"><B>Sales Commission</B></th>			
				<th align="center"><B>OverSeas Commission</B></th>
				<th align="center"><B>Port of Loading</B></th>	
				<th align="center"><B>CHA</B></th>	
				<th align="center"><B>Terms</B></th>
				<th align="center"><B>Port of Discharge</B></th>			
                <th align="center"><B>Final Destination</B></th>
                <th align="center"><B>Net Weight Kgs</B></th>
			    <th align="center"><B>Qty/Mcs</B></th>
                <th align="center"><B>Variety</B></th>
                <th align="center"><B>Product</B></th>
                <th align="center"><B>Pack Type</B></th>
                <th align="center"><B>Sizes</B></th>
                <th align="center"><B>Brand</B></th>
				<th align="center"><B>Production Codes</B></th>
                <th align="center"><B>Invoice Value US$</B></th>';

$content .= ' <th align="center"><B>Terms of Payment</B></th>
                <th align="center"><B>Advance Recd in USD</B></th>
                <th align="center"><B>Rate(Advance)</B></th>
                <th align="center"><B>Balance Bill in USD</B></th>
                <th align="center"><B>Rate(Bank)</B></th>
                <th align="center"><B>Total Bill US$</B></th>';

$content .= ' <th align="center"><B>Bill Value INR</B></th>
                <th align="center"><B>Bill Ref No</B></th>
                <th align="center"><B>Bill Discounted Date</B></th>
                <th align="center"><B>Bill Date of Realization</B></th>
                <th align="center"><B>Realization Early Days</B></th>
                <th align="center"><B>Realization Late Days</B></th>
                <th align="center"><B>Discount Details in USD</B></th>
                <th align="center"><B>E-BRC Realized USD</B></th>
                <th align="center"><B>E_BRC_No</B></th>
                <th align="center"><B>E_BRC_Date</B></th>
                <th align="center"><B>Advance Ref No </B></th>
                <th align="center"><B>Advance Date</B></th>
                <th align="center"><B>LC No</B></th>
                <th align="center"><B>LC Date</B></th>
                <th align="center"><B>LC Receipt From</B></th>
                <th align="center"><B>AWB No</B></th>
                <th align="center"><B>AWB Date</B></th>
                <th align="center"><B>Date of Shipment</B></th>
                <th align="center"><B>Road Transporter</B></th>
                <th align="center"><B>Vehicle #</B></th>
                <th align="center"><B>Shipping Company</B></th>
                <th align="center"><B>Freight Rate US $</B></th>
                <th align="center"><B>Ocean Freight Rate INR</B></th>
				';
//if($Exporter!=5){
$content .= ' <th align="center"><B>Freight Cost Per Kg</B></th>
                ';
//}
$content .= '
				 <th align="center"><B>THC and Others if Extra</B></th>
				 ';
/*if($Exporter==5){
					$content .= ' <th  align="center" <B>CHA Charges</B></th>
                ';
				}*/
$content .= '
                
                <th  align="center" <B>Total Freight INR</B></th>
                <th  align="center" <B>Container #</B></th>
                <th  align="center" <B>Line Seal #</B></th>
                <th  align="center" <B>Entered By</B></th>
				';
if ($Exporter != 5) {
	$content .= ' <th  align="center" <B>Pack Seal</B></th>
                ';
}
$content .= '
                <th  align="center" <B>B/L No</B></th>
                <th  align="center" <B>Feder Vessel Name/VOY</B></th>
                <th  align="center" <B> Vessel ETD</B></th>
                <th  align="center" <B> Vessel ETA</B></th>
                <th  align="center" <B>Container Gate Out at Destination</B></th>
				';
//if($Exporter!=5){
$content .= ' 
					<th  align="center" <B>FDA/EU Hold Date</B></th>
					<th  align="center" <B>FDA/EU Release/CFIA Date</B></th>
                ';
//}
/*ELSE{
               $content .= '
                <th  align="center" <B>FDA/EU Release Date</B></th>';
				}*/
$content .= ' <th  align="center" <B>FDA Entry No</B></th>
                <th  align="center" <B>FDA Entry Date</B></th>
                <th  align="center" <B>Agent Invoice</B></th>
                <th  align="center" <B>Actual A Duty Paid US$</B></th>
                <th  align="center" <B>CVD Paid US$</B></th>
                <th  align="center" <B>Others HMF US$</B></th>
                <th  align="center" <B>Others MPF US$</B></th>
				<th  align="center" <B>RT TAX Duty </B></th>
				';
if ($Exporter != 5) {
	$content .= ' 
                <th  align="center" <B>Others Charges</B></th>
					 ';
} else {
	$content .= '
                <th  align="center" <B>Others ALL US$</B></th>';
}
$content .= '
                <th  align="center" <B>Total Paid US$</B></th>
                <th  align="center" <B>Shipping Bill #</B></th>
                <th  align="center" <B>Date</B></th>
				';
//if($Exporter!=5){
$content .= ' 
                <th  align="center" <B>LET Export Date</B></th>
					 ';
//}
/*ELSE{
               $content .= '
                <th  align="center" <B>LEO Date</B></th>
';				}*/
$content .= '
                <th  align="center" <B>FOb Value  as per SB US$</B></th>
                <th  align="center" <B>FOb Value  as per SB INR</B></th>
                <th  align="center" <B>Drawback Receivable 3%</B></th>
				
				';
//if($Exporter!=5){
$content .= ' 
                <th align="center"><B>RODTEP Receivable 2.50%</B></th>
				<th align="center"><B>Drawback/ST Received </B></th>
                <th align="center"><B>Drawback/ST Recd Date</B></th>
				<th align="center"><B>MEIS Received </B></th>
                <th align="center"><B>MEIS APPL.Date</B></th>
				';

$content .= '
                <th align="center"><B>`Q` Certificate #</B></th>
                <th align="center"><B>Date Issue</B></th>
                <th align="center"><B>FOB value INR</B></th>
                <th align="center"><B>Plant Approval #</B></th>
                <th align="center"><B>HC No & Date</B></th>
                <th align="center"><B>GSP No & Date</B></th>
                <th align="center"><B>Co # & Date</B></th>
                <th align="center"><B>Insurance Details</B></th>

				
               <!-- <th  align="center" <B>BILLING INFORMATION</B>
				 <table border="1" cellspacing="0" cellpadding="5">
				
				 <tr>
<td align="center"> <B>Bill Value INR</B></td>
<td align="center"> <B>Bill Ref No</B></td>
<td align="center"> <B>Bill Discounted Date</B></td>
<td align="center"> <B>Date of Realization</B></td>
</tr></table>
</th>-->
				
                
    </thead>
               </tr><tbody> 
			   
			  ';


//	
$TotalFOBUS = 0;
$TotalFOB = 0;
$GrandTotalDiscount = 0;
$TotalNetWgtKgs = 0;
$TotalNetWgtLbs = 0;
$OtherChargesSum = 0;


while ($RowPo = $StmtPo->fetch_array()) {
	$PoID = $RowPo['PiID'];
	$ProcessorID = $RowPo['ProcessorID'];
	$ExpID = $RowPo['ExporterID'];
	$Discount = $RowPo['Discount'];
	$PiID = $RowPo['PiID'];
	$FrieghtCharge = $RowPo['FrieghtCharge'];
	$DestinationCountryId = $RowPo['DestinationCountry'];
	$CInvCode = $RowPo['CInvCode'];
	$IGST = $RowPo['IGST'];
	$ShippingTerms = $RowPo['ShippingTerms'];
	$CountryName = $RowPo['DestinationCountry'];
	$AdvanceAmount = $RowPo['AdvanceAmount'];
	$PortOfLoading = $RowPo['PortOfLoading'];
	$ActualDutyPaidUSD = $RowPo['ActualDutyPaidUSD'];
	$CvdPaidUSD = $RowPo['CvdPaidUSD'];
	$OthersHMFUSD = $RowPo['OthersHMFUSD'];
	$OthersMPFUSD = $RowPo['OthersMPFUSD'];
	$ExchangeRate = $RowPo['CIExchangeRate'];
	$AwbNumber = $RowPo['AwbNumber'];
	$MeisRcvdAmount = $RowPo['MeisRcvdAmount'];
	$SBDate = $RowPo['SBDate'] ? date('d-m-Y', strtotime($RowPo['SBDate'])) : '';
	$TotalFreight = $RowPo['ThcNOthers'] + $RowPo['OceanFreightINR'];
	$OtherCharges = explode(',', $RowPo['OtherCharges']);
	$NameofRepresentative = $RowPo['NameofRepresentative '];

	$FinalDestination = $RowPo['FinalDestination'] . ' ' . $RowPo['FinalDestinationCountryCode'] . ' ,' . $RowPo['FinalDestinationCountry'];


	// print_r($OtherCharges);
	$OtherChargesSum = array_sum($OtherCharges);
	//echo $OtherChargesSum=array_sum($OtherChargesSum);
	$ExporterData = getExporterDetails($ExpID);

	$CurrentYear = Date(Y);
	$NxtYear = $CurrentYear + 1;
	//$InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);
	$InvNO = $RowPo['CustomsInvNo'];



	$InvDate = getFormatedDate($RowPo['CInvDate']);
	$PoDate = getFormatedDate($RowPo['PoDate']);
	$BillDiscountDate = getFormatedDate($RowPo['BillDiscountDate']);
	$BillRealizationDate = getFormatedDate($RowPo['BillRealizationDate']);
	$LetExportDate = $RowPo['LetExportDate'] ? getFormatedDate($RowPo['LetExportDate']) : '';
	$DrawBackRcvdDate = getFormatedDate($RowPo['DrawBackRcvdDate']);
	$MeisApplDate = getFormatedDate($RowPo['MeisApplDate']);
	$HCDate = getFormatedDate($RowPo['HCDate']);
	$GPSDate = getFormatedDate($RowPo['GPSDate']);
	$CoDate = getFormatedDate($RowPo['CoDate']);
	$EbrcDate = getFormatedDate($RowPo['EbrcDate']);
	$EbrcDate2 = getFormatedDate($RowPo['EbrcDate2']);
	$EbrcDate3 = getFormatedDate($RowPo['EbrcDate3']);
	$EbrcDate4 = getFormatedDate($RowPo['EbrcDate4']);

	$AdvanceDate = getFormatedDate($RowPo['AdvanceDate']);
	$LcDate = getFormatedDate($RowPo['LcDate']);
	$AwbDate = getFormatedDate($RowPo['AwbDate']);
	$ShipByDate = getFormatedDate($RowPo['ShipByDate']);
	$ContainerOutDate = getFormatedDate($RowPo['ContainerOutDate']);
	$FdaNEuHoldDate = getFormatedDate($RowPo['FdaNEuHoldDate']);
	$FdaNEuReleaseDate = getFormatedDate($RowPo['FdaNEuReleaseDate']);
	$FdaEntryDate = getFormatedDate($RowPo['FdaEntryDate']);
	$QDateIssue = getFormatedDate($RowPo['QDateIssue']);
	$ShippingBillDate = getFormatedDate($RowPo['ShippingBillDate']);
	$EbrcNumber = explode(',', $RowPo['EbrcNumber']);



	$HCNumberDate = $RowPo['HCNumber'] . ' / ' . $HCDate;
	$GPSNumberGPSDate = $RowPo['GPSNumber'] . ' / ' . $GPSDate;
	$CoNumberCoDate = $RowPo['CoNumber'] . ' / ' . $CoDate;

	if ($RowPo['HCNumber'] == '' && $HCDate == '') {

		$HCNumberDate = '';
	}
	if ($RowPo['GPSNumber'] == '' && $GPSDate == '') {

		$GPSNumberGPSDate = '';
	}
	if ($RowPo['CoNumber'] == '' && $CoDate == '') {

		$CoNumberCoDate = '';
	}
	$ETD = getFormatedDateNA($RowPo['ETD']);
	$ETA = getFormatedDateNA($RowPo['ETA']);


	$TotalPaidUSD = $OtherChargesSum + $ActualDutyPaidUSD + $CvdPaidUSD + $OthersHMFUSD + $OthersMPFUSD;

	$SqlTenplus = "SELECT * FROM `ScrmLogisticsDetails` WHERE PiID='$PiID'";
	$StmtTenPlus = $iconn->query($SqlTenplus);
	$RowTenPlus = $StmtTenPlus->fetch_array();

	$VesselNameVOY = $RowTenPlus['VesselName'] . '/' . $RowTenPlus['VOY'];

	$SqlProcessor = "SELECT * FROM `AdminExporterDetails` WHERE ExpID='$ProcessorID'";
	$StmtProcessor = $iconn->query($SqlProcessor);
	$RowProcessor = $StmtProcessor->fetch_array();

	/********uma********/
	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,QCertNo";


	$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmCIGradeData` 
			as PGD 
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID 
			left join QcCoveringLetter as QCL on QCL.InvID=PGD.InvId 
			WHERE PGD.InvId='$PiID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();


	$NetWgtLbs = $RowTotal['NetWgtLbs'];
	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	$RowPo['QtyUnit'];

	if ($RowPo['QtyUnit'] == 'KGS') {
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$Price = $RowTotal['PriceKgs'];
	} else {
		$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;

		$Price = $RowTotal['PriceLbs'];
	}


	$UsdValue = $Price;

	$AryPaymentData = getPaymentValues($iconn, $PiID, '');
	$InsuranceValue = $AryPaymentData['InsuranceValue'];
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$DiscountPerc = $AryPaymentData['DiscountPerc'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$IgstPerc = $AryPaymentData['IgstPerc'];
	$IgstValue = $AryPaymentData['IgstValue'];
	$InvValueUSD = $AryPaymentData['InvValueUSD'];
	$InvValueInr = $AryPaymentData['InvValueINR'];

	$TotalMcs = $AryPaymentData['TotalMcs'];
	$Price = $AryPaymentData['Price'];

	$DutiesExemptionShippingTerms = ['CIF', 'CNF', 'CFR', 'FOB'];

	if ($RowPo['ShowFrightCharge'] == 1 && $RowPo['ShowFrieght'] == 1 && !in_array($ShippingTerms, $DutiesExemptionShippingTerms)) {
		$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
		$FOBValueINR = $AryPaymentData['FOBValueINR'];
	} else {
		$FOBValueUSD = $AryPaymentData['InvValueUSDWithOutDuties'];
		$FOBValueINR = $AryPaymentData['InvValueINRWithOutDuties'];
	}

	// echo $RowPo['ShowFrightCharge'];
	// echo json_encode($AryPaymentData, JSON_PRETTY_PRINT);
	// exit;

	/*$DiscountAmount=($Price*$Discount)/100; 
			
			IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}
				
			
					 						 

					 if($ShippingTerms=='CIF'){
									$InvPrice= ($TotalDiscount*0.11)/100;
									$TotalDiscount=$TotalDiscount-$InvPrice;
								 }
								 else{
								$TotalDiscount=$TotalDiscount;	 
								 }
								 
							
					 
					 $DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35; 
					 $DutiesPrice=$DutiesPrice+125;
					 
					
					 if($CountryName=='China'||$CountryName=='UAE'||$ShippingTerms=='CNF'){
				
						$FOBValue= ($TotalDiscount-$FrieghtCharge)*$ExchangeRate;
						$FOBValueUS=$TotalDiscount-$FrieghtCharge;
					 }
					 ELSE{
						 
				$FOBValue=	($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate;	 
				$FOBValueUS=	$TotalDiscount-$FrieghtCharge-$DutiesPrice;	 
				
					 }
					 
					 IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}*/

	$BalanceAmountUS = $InvValueUSD - $AdvanceAmount;
	/**uma(16/01/22)**/
	$TotalFOBUS += $FOBValueUSD;
	$TotalFOB += $FOBValueINR;
	$GrandTotalDiscount += $InvValueUSD;
	$TotalNetWgtKgs += $NetWgtKgs;
	$TotalNetWgtLbs += $NetWgtLbs;

	/***18/07/2022**/
	//$SqlVarietyId="SELECT distinct(VarietyID) FROM `$ScrmCIGradeData`  WHERE InvId='$PiID'"; 
	$SqlVarietyId = "SELECT distinct(PackingStyleID),VarietyID FROM `ScrmCIGradeData` WHERE InvId='$PiID'";
	$StmtvarietyId = $iconn->query($SqlVarietyId);
	$VarietyCount = mysqli_num_rows($StmtvarietyId);



	/***18/07/2022**/



	/*********uma*******/

	$content .=

		' <tr>
			<td class="sticky-col first-col" align="center"  > <B>' . $S_NO . '</B> </td> 
			<td class="sticky-col second-col" align="center"> <B>' . $InvNO . '</B> </td>
			<td  align="center" width="auto"><B>' . $InvDate . '</B> </td>';
	if ($NameofRepresentative == '') {
		$content .=  '<td  align="center"  ><font color="red">' . $RowPo['PoNo'] . '</font> </td>';
	} else {
		$content .=  '<td  align="center"  >' . $RowPo['PoNo'] . '</td>';
	}

	$content .= '	
				   <td  align="center"> <B>' . $PoDate . '</B> </td>
				   <td  align="center" width="35%"><B>' . $RowPo['Consignee'] . '</B> </td>					   
				   
				   <td  align="center"> <B>' . strtoupper($RowPo['DestinationCountry']) . '</B> </td>					   
				   <td  align="center"> <B>' . $RowPo['LocalAgent'] . '</B> </td>			
				   <td  align="center"> <B>' . $RowPo['LocalAgentComisn'] . '</B> </td>			
				   <td  align="center"> <B>' . $RowPo['OverseasAgentComisn'] . '</B> </td>			
				   <td  align="center"> <B>' . $RowPo['PortOfLoading'] . '</B> </td>	
				   <td  align="center"> <B>' . $RowPo['CHA'] . '</B> </td>	
				   <td  align="center"> <B>' . $RowPo['ShippingTerms'] . '</B> </td>
				   <td  align="center"> <B>' . strtoupper($RowPo['PortofDischarge']) . ' ' . $RowPo['CountryCode'] . ',' . strtoupper($RowPo['PortofDischargeCountry']) . '</B> </td>			

                <td  align="center"> <B>' . strtoupper($FinalDestination) . '</B> </td>
				<td  align="center"> <B>' . number_format($NetWgtKgs, 3) . '</B> </td>';
	if ($VarietyCount == 1) {
		while ($RowVarietyId = $StmtvarietyId->fetch_array()) {


			$VarietyID = $RowVarietyId['VarietyID'];
			$PackingStyleID = $RowVarietyId['PackingStyleID'];

			$SqlVariety = "SELECT ShortName,sum(Mcs) as Mcs,SPDT.FreezingType,APS.PackingStyle,Brand,SPDT.Grade FROM `ScrmCIGradeData` SPDT LEFT join 
			ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID LEFT join 
			AdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID 
			WHERE SPDT.InvId='$PiID' and SPDT.VarietyID='$VarietyID'and SPDT.PackingStyleID='$PackingStyleID'";
			$Stmtvariety = $iconn->query($SqlVariety);
			$RowVariety = $Stmtvariety->fetch_array();

			$content .= '     <td  align="center"> ' . $RowVariety['Mcs'] . ' </td>
				 <td  align="center"> ' . $RowVariety['ShortName'] . ' VANNAMEI SHRIMPS </td>
				 <td  align="center"> ' . $RowVariety['FreezingType'] . ' </td>
				 <td  align="center"> ' . $RowVariety['PackingStyle'] . ' </td>
				 <td  align="center"> ' . $RowVariety['Grade'] . ' </td>
				 <td  align="left"  > ' . $RowVariety['Brand'] . ' </td>';
		}
	}
	if ($VarietyCount != 1) {
		$content .= '<td  align="center"></td>
				 <td  align="center"  ></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				 <td  align="left"  ></td>';
	}

	$content .= '<td  align="center" > <B>' . $RowPo['DayCode'] . '</B> </td>
				<td  align="center" > <B>' . number_format($InvValueUSD, 2) . '</B> </td>
				<td  align="center"><B>' . $RowPo['PaymentName'] . '</B> </td>
				<td  align="center" > <B>' . number_format($AdvanceAmount, 2) . '</B> </td>
				<td  align="center" > <B>' . $RowPo['ExchangeRateperUnit'] . '</B> </td>
				<td  align="center" > <B>' . number_format($BalanceAmountUS, 2) . '</B> </td>
				<td  align="center" > <B>' . $ExchangeRate . '</B> </td>
				<td  align="center" > <B>' . number_format($InvValueUSD, 2) . '</B> </td>';
	// $BankRefNo = explode($RowPo['BankRefNo'], ',');
	$content .= '   <td  align="center" > <B>' . number_format($FOBValueINR, 2) . '</B> </td>

                <td  align="center" > <B>' . $RowPo['BankRefNo'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['ColDate'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['BillRealizationDate'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['RealizationEarlyDate'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['RealizationLateDate'] . '</B> </td>
                <td  align="center" > <B>' . number_format($DiscountAmount, 2) . '</B> </td>
                <td  align="center" > <B>' . $RowPo['EbrcReleasedUSD'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['EbrcNumber'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['EbrcDate'] . '<br>' . $RowPo['EbrcDate2'] . '<br>' . $RowPo['EbrcDate3'] . '<br>' . $RowPo['EbrcDate4'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['AdvanceRefNo'] . '</B> </td>
			  <td  align="center" > <B>' . $RowPo['AdvanceDate'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['LcNumber'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['LcDate'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['LcReceiptFrom'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['AwbNumber'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['AwbDate'] . '</B> </td>
                <td  align="center" > <B>' . $ShippingBillDate . '</B> </td>
                <td  align="center" > <B>' . $RowPo['ByRoad'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['VehicelNumber'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['LinerName'] . '</B> </td>
				
                <td  align="center" > <B>' . $RowPo['FrieghtCharge'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['OceanFreightINR'] . '</B> </td>';

	$content .= '  <td  align="center" > <B>' . $RowPo['FreightCostPerKg'] . '</B> </td>';
	$content .= '  <td  align="center" > <B>' . $RowPo['ThcNOthers'] . '</B> </td>';

	$content .= ' <td  align="center" > <B>' . $RowPo['TotalFreight'] . '</B> </td> 
			  <td  align="center" > <B>' . $RowPo['ContainerNo'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['SealNo'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['UserName'] . '</B> </td>';
	if ($Exporter != 5) {
		$content .= '     <td  align="center" > <B>' . $RowPo['CustomSealNo'] . '</B> </td>';
	}
	$content .= ' <td  align="center" > <B>' . $RowPo['BLNo'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['VesselName'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['VesselDepartureDate'] . '</B> </td>
                <td  align="center" > <B>' . $ETA . '</B> </td>
              <td  align="center" > <B>' . $RowPo['ContainerGateOutDate'] . '</B> </td>';

	$content .= '   <td  align="center" > <B>' . $RowPo['FDAEUHoldDate'] . '</B> </td>
              <td  align="center" > <B>' . $RowPo['FDAEUReleaseCFIADate'] . '</B> </td>'; //}

	$content .= '   <td  align="center" > <B>' . $RowPo['FdaEntryNumber'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['FDAEntryDate'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['ClearingAgentInvoiceNo'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['ADCharges'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['CVDCharges'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['HmDutyCharges'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['MPCharges'] . '</B> </td>
				<td  align="center" > <B>' . $RowPo['RTCharges'] . '</B> </td>';
	$content .= '    <td  align="center" > <B>' . $OtherChargesSum . '</B> </td>';

	$content .= '  <td  align="center" > <B>' . $TotalPaidUSD . '</B> </td>
                <td  align="center" > <B>' . $RowPo['ShippingBillNo'] . '</B> </td>
                <td  align="center" > <B>' . $ShippingBillDate . '</B> </td>
				           
                <td  align="center" > <B>' . $RowPo['LEODate'] . '</B> </td>
            <td  align="center" > <B>' . number_format($FOBValueUSD, 2) . '</B> </td>
                <td  align="center" > <B>' . number_format($FOBValueINR, 2) . '</B> </td>
                <td  align="center" > <B>' . $RowPo['DrawBackRecvd'] . '</B> </td>';
	//if($Exporter!=5){
	$content .= '    <td  align="center" > <B>' . $RowPo['RodtepRecvd'] . '</B> </td>
                <td  align="center" > <B>' . $RowPo['DrawBackRcvdAmount'] . '</B> </td>
                <td  align="center" > <B>' . $DrawBackRcvdDate . '</B> </td>
                <td  align="center">  <B>' . $MeisRcvdAmount . '</B> </td>
                <td  align="center" > <B>' . $MeisApplDate . '</B> </td>'; //}

	$content .= '  <td  align="center" > <B>' . $RowTotal['QCertNo'] . '</B> </td>
                <td  align="center" > <B>' . $QDateIssue . '</B> </td>
                <td  align="center" > <B>' . number_format($FOBValueINR, 2) . '</B> </td>
                <td  align="center" > <B>' . $RowProcessor['ExpApprovalNo'] . '</B> </td>
                 <td  align="center" > <B>' . $HCNumberDate . '</B> </td>
                <td  align="center" > <B>' . $GPSNumberGPSDate . '</B> </td>
                <td  align="center" > <B>' . $CoNumberCoDate . '</B> </td>
                <td  align="center" > <B>' . $RowPo['InsuranceDtls'] . '</B> </td>
                
			
				 </tr>';

	/**uma**/


	$StmtvarietyId = $iconn->query($SqlVarietyId);
	if ($VarietyCount > 1) {

		while ($RowVarietyId = $StmtvarietyId->fetch_array()) {


			$VarietyID = $RowVarietyId['VarietyID'];
			$PackingStyleID = $RowVarietyId['PackingStyleID'];


			$SqlVariety = "SELECT ShortName, Mcs,SPDT.FreezingType,APS.PackingStyle,Brand,SPDT.Grade FROM `ScrmCIGradeData` SPDT LEFT join 
			ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID LEFT join 
			AdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID 
			WHERE SPDT.InvId='$PiID' and SPDT.VarietyID='$VarietyID'and SPDT.PackingStyleID='$PackingStyleID'";
			$Stmtvariety = $iconn->query($SqlVariety);
			$TotalGrades = '';
			$TotalMcs = '';
			$ary_Mcs = '';
			while ($RowVariety = $Stmtvariety->fetch_array()) {
				$Grades = $RowVariety['Grade'];
				$Mcs = $RowVariety['Mcs'];
				$ShortName = $RowVariety['ShortName'];
				$PackingStyle = $RowVariety['PackingStyle'];
				$FreezingType = $RowVariety['FreezingType'];
				$Brand = $RowVariety['Brand'];

				if (!in_array($Grades, $ary_code, true)) {
					$ary_code[] = $Grades;

					if ($TotalGrades == '') {
						$TotalGrades = $Grades;
					} else {
						$TotalGrades = $TotalGrades . ',' . $Grades;
					}
				} else {
					//echo $DAY_CODE;
				}

				if (!in_array($Mcs, $ary_Mcs, true)) {
					$ary_Mcs[] = $Mcs;

					if ($TotalMcs == '') {
						$TotalMcs = $Mcs;
					} else {
						$TotalMcs = $TotalMcs . ',' . $Mcs;
					}
				} else {
					//echo $DAY_CODE;
				}
			}
			$content .=

				'<tr>
				 <td  align="center" ></td>

				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"> ' . $TotalMcs . ' </td>
				 <td  align="center"  > ' . $ShortName . ' VANNAMEI SHRIMPS </td>
				 <td  align="center"> ' . $FreezingType . ' </td>
				 <td  align="center"> ' . $PackingStyle . ' </td>
				 <td  align="center"> ' . $TotalGrades . ' </td>
				 <td  align="left"  > ' . $Brand . ' </td>
				 <td  align="center"  ></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				  <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				  <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				  <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				 <td  align="center"></td>
				  <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				   <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 ';
			if ($Exporter != 5) {
				$content .= '<td  align="center"  ></td>
                ';
			}

			$content .= ' </tr>';
		}
	}
	/**uma**/




	$S_NO++;
}



$content .= ' </tbody></table></div></div>';


echo $content;


?>