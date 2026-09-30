<?php
// ini_set( 'display_errors', 1 );

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");



$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}
/* $Month=$_GET['Month']; 
$Year=$_GET['Year']; 
$B_TYPE=$_GET['TYPE'];
$BAR_CODE_ID=$_GET['BID'];
$LOT_ID=$_GET['ID'];

$from_date=$_GET['from_date']; 
$to_date=$_GET['to_date']; */
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

//echo $SqlPo; //exit;


$StmtPo = $iconn->query($SqlPo);

$content = '';


$size = 40;

$size1 = 60;
$S_NO = 1;
$width = 11;
$width_s = 8;
$width_w = $width + 5;

function fnCheckIsset($name)
{

	if (isset($name) && !empty($name)) {
		return $name . ", ";
	} else {
		return "";
	}
}

$content .= '
				<h3  >' . $ExporterName . '</h3>
			  <h3  >' . $Header . '</h3>
			  
			  
			  
			  <table border="1" cellspacing="0" cellpadding="" >
			   <thead style=" font-weight: 600">  

           		

                <th>S No.</th>             
				<th>Invoice #</th>
				<th>Date</th>	
				<th>Buyer Order #</th>					   
				<th>Buyer Order Date</th>
				<th>Name of the Buyer</th>			   
				<th>Country</th>					   
				<th>Local Agent</th>			
				<th>Sales Commission</th>			
				<th>OverSeas Commission</th>
				<th>Port of Loading</th>	
				<th>CHA</th>	
				<th>Terms</th>
				<th>Port of Discharge</th>		
                <th>Final Destination</th>
                <th>Net Weight Kgs</th>
			    <th>Qty/Mcs</th>
                <th>Variety</th>
                <th>Product</th>
                <th>Pack Type</th>
                <th>Sizes</th>
                <th>Brand</th>
				<th>Production Codes</th>
                <th>Invoice Value US$</th>';

$content .= ' <th>Terms of Payment </th>
                <th>Advance Recd in USD</th>
                <th>Rate(Advance)</th>
                <th>Balance Bill in USD</th>
                <th>Rate(Bank)</th>
                <th>Total Bill US$</th>';

$content .= ' <th>Bill Value INR</th>
                <th>Bill Ref No</th>
                <th>Bill Discounted Date</th>
                <th>Bill Date of Realization</th>
                <th>Realization Early Days</th>
                <th>Realization Late Days</th>
                <th>Discount Details in USD</th>
                <th>E-BRC Realized USD</th>
                <th>E_BRC_No</th>
                <th>E_BRC_Date</th>
                <th>Advance Ref No </th>
                <th>Advance Date</th>
                <th>LC No</th>
                <th>LC Date</th>
                <th>LC Receipt From</th>
                <th>AWB No</th>
                <th>AWB Date</th>
                <th>Date of Shipment</th>
                <th>Road Transporter</th>
                <th>Vehicle #</th>
                <th>Shipping Company</th>
                <th>Freight Rate US $</th>
                <th>Ocean Freight Rate INR</th>
				';
//if($Exporter!=5){
$content .= ' <th>Freight Cost Per Kg</th>';
//}
$content .= '
				 <th>THC and Others if Extra</th>
				 ';
/*if($Exporter==5){
				$content .= ' <th>CHA Charges</th>
			';
			}*/
$content .= '
                
                <th>Total Freight INR</th>
                <th>Container #</th>
                <th>Line Seal #</th>
                <th>Entered By</th>
				';
if ($Exporter != 5) {
	$content .= ' <th>Pack Seal</th>
                ';
}
$content .= '
                <th>B/L No</th>
                <th>Feder Vessel Name/VOY</th>
                <th> Vessel ETD</th>
                <th> Vessel ETA</th>
                <th>Container Gate Out at Destination</th>
				';
//if($Exporter!=5){
$content .= ' 
					<th>FDA/EU Hold Date</th>
					<th>FDA/EU Release/CFIA Date</th>
                ';
//}
/*ELSE{
		   $content .= '
			<th>FDA/EU Release Date</th>';
			}*/
$content .= ' <th>FDA Entry No</th>
                <th>FDA Entry Date</th>
                <th>Agent Invoice</th>
                <th>Actual A Duty Paid US$</th>
                <th>CVD Paid US$</th>
                <th>Others HMF US$</th>
                <th>Others MPF US$</th>
				<th  align="center" <B>RT TAX Duty </B></th>
				';
if ($Exporter != 5) {
	$content .= ' 
                <th>Others Charges</th>
					 ';
} else {
	$content .= '
                <th>Others ALL US$</th>';
}
$content .= '
                <th>Total Paid US$</th>
                <th>Shipping Bill #</th>
                <th>Date</th>
				';
//if($Exporter!=5){
$content .= ' 
                <th>LET Export Date</th>
					 ';
//}
/*ELSE{
		   $content .= '
			<th>LEO Date</th>
';				}*/
$content .= '
                <th>FOb Value  as per SB US$</th>
                <th>FOb Value  as per SB INR</th>
                <th>Drawback Receivable 3%</th>
				
				';
//if($Exporter!=5){
$content .= ' 
                <th>RODTEP Receivable 2.50%</th>
				<th>Drawback/ST Received </th>
                <th>Drawback/ST Recd Date</th>
				<th>MEIS Received </th>
                <th>MEIS APPL.Date</th>
				';

$content .= '
                  <th>`Q` Certificate #</th>
                <th>Date Issue</th>
                <th>FOB value INR</th>
                <th>Plant Approval #</th>
                <th>HC No & Date</th>
                <th>GSP No & Date</th>
                <th>Co # & Date</th>
                <th>Insurance Details</th>
			    </thead>';


//	
$TotalFOBUS = 0;
$TotalFOB = 0;
$GrandTotalDiscount = 0;
$TotalNetWgtKgs = 0;
$TotalNetWgtLbs = 0;
$OtherChargesSum = 0;


while ($RowPo = $StmtPo->fetch_array()) {
	// ECHO $PoID = $RowPo['PiID'];
	// ECHO '<BR>';

	//include "../SalesInculdes/SalesConstants.php";

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


	$BalanceAmountUS = $InvValueUSD - $AdvanceAmount;
	/**uma(16/01/22)**/
	$TotalFOBUS += $FOBValueUSD;
	$TotalFOB += $FOBValueINR;
	$GrandTotalDiscount += $InvValueUSD;
	$TotalNetWgtKgs += $NetWgtKgs;
	$TotalNetWgtLbs += $NetWgtLbs;

	$VarietyCount = 1;

	/***18/07/2022**/
	//$SqlVarietyId="SELECT distinct(VarietyID) FROM `$ScrmCIGradeData`  WHERE InvId='$PiID'"; 
	$SqlVarietyId = "SELECT distinct(PackingStyleID),VarietyID FROM `ScrmCIGradeData` WHERE InvId='$PiID'";
	$StmtvarietyId = $iconn->query($SqlVarietyId);
	$VarietyCount = mysqli_num_rows($StmtvarietyId);



	/***18/07/2022**/



	/*********uma*******/

	$content .= '<tr>				
                <td>' . $S_NO . '</td> 				
				<td>' . $InvNO . '</td>
				<td>' . $InvDate . '</td>';
	if ($NameofRepresentative == '') {
		$content .= '<td><font color="red">' . $RowPo['PoNo'] . '</font> </td>';
	} else {
		$content .= '<td>' . $RowPo['PoNo'] . '</td>';
	}
	$content .= '	
				<td>' . $PoDate . '</td>
				<td>' . $RowPo['ConsigneeName'] . '</td>					     
				<td>' . strtoupper($RowPo['DestinationCountry']) . '</td>					   
				<td>' . $RowPo['LocalAgent'] . '</td>			
				<td>' . $RowPo['LocalAgentComisn'] . '</td>			
				<td>' . $RowPo['OverseasAgentComisn'] . '</td>			
				<td>' . $RowPo['PortOfLoading'] . '</td>	
				<td>' . $RowPo['CHA'] . '</td>	
				<td>' . $RowPo['ShippingTerms'] . '</td>
				<td>' . strtoupper($RowPo['PortofDischarge']) . ' ' . $RowPo['CountryCode'] . ',' . strtoupper($RowPo['PortofDischargeCountry']) . '</td>
                <td>' . strtoupper($FinalDestination) . '</td>
				<td>' . number_format($NetWgtKgs, 3) . '</td>';
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


			$content .= '<td>' . $RowVariety['Mcs'] . '</td>
					<td>' . $RowVariety['ShortName'] . ' VANNAMEI SHRIMPS</td>
					<td>' . $RowVariety['FreezingType'] . '</td>
					<td>' . $RowVariety['PackingStyle'] . '</td>
					<td>' . $RowVariety['Grade'] . '</td>
					<td align="left" >' . $RowVariety['Brand'] . '</td>
					<td></td>';
		}
	}
	if ($VarietyCount != 1) {
		$content .= '<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td align="left"></td>
					<td></td>';
	}
	//}
	$content .= '<td  align="center" > <B>' . $RowPo['DayCode'] . '</B> </td>
				<td>' . number_format($InvValueUSD, 2) . '</td>				
				<td>' . $RowPo['PaymentName'] . ' </td>
				<td>' . number_format($AdvanceAmount, 2) . '</td>
				<td>' . $RowPo['ExchangeRateperUnit'] . '</td>
				<td>' . number_format($BalanceAmountUS, 2) . '</td>
				<td>' . $ExchangeRate . '</td>
				<td>' . number_format($InvValueUSD, 2) . '<td>';
	$content .= '<td>' . number_format($FOBValueINR, 2) . '</td>			
                <td>' . $RowPo['BankRefNo'] . '</td>
                <td>' . $RowPo['ColDate'] . '</td>
                <td>' . $RowPo['BillRealizationDate'] . '</td>
                <td>' . $RowPo['RealizationEarlyDate'] . '</td>
                <td>' . $RowPo['RealizationLateDate'] . '</td>
                <td>' . number_format($DiscountAmount, 2) . '</td>
                <td>' . $RowPo['EbrcReleasedUSD'] . '</td>
				<td  align="center" > <B>' . $RowPo['EbrcNumber'] . '</B> </td>
				<td  align="center" > <B>' . $RowPo['EbrcDate'] . '<br>' . $RowPo['EbrcDate2'] . '<br>' . $RowPo['EbrcDate3'] . '<br>' . $RowPo['EbrcDate4'] . '</B> </td>
                <td>' . $RowPo['AdvanceRefNo'] . '</td>
				<td>' . $RowPo['AdvanceDate'] . '</td>
                <td>' . $RowPo['LcNumber'] . '</td>
                <td>' . $RowPo['LcDate'] . '</td>
                <td>' . $RowPo['LcReceiptFrom'] . '</td>
                <td>' . $RowPo['AwbNumber'] . '</td>
                <td>' . $RowPo['AwbDate'] . '</td>
                <td>' . $ShippingBillDate . '</td>
                <td>' . $RowPo['ByRoad'] . '</td>
                <td>' . $RowPo['VehicelNumber'] . '</td>
                <td>' . $RowPo['LinerName'] . '</td>
				
                <td>' . $RowPo['FrieghtCharge'] . '</td>
                <td>' . $RowPo['OceanFreightINR'] . '</td>';

	$content .= '  <td>' . $RowPo['FreightCostPerKg'] . '</td>';
	$content .= '  <td>' . $RowPo['ThcNOthers'] . '</td>';
	$content .= ' <td>' . $RowPo['TotalFreight'] . '</td>
				<td>' . $RowPo['ContainerNo'] . '</td>
				<td>' . $RowPo['SealNo'] . '</td>
				<td>' . $RowPo['UserName'] . '</td>';
	if ($Exporter != 5) {
		$content .= '<td>' . $RowPo['CustomSealNo'] . '</td>';
	}
	$content .= '<td>' . $RowPo['BLNo'] . '</td>
				<td>' . $RowPo['VesselName'] . '</td>
				<td>' . $RowPo['VesselDepartureDate'] . '</td>
				<td>' . $ETA . '</td>
				<td>' . $RowPo['ContainerGateOutDate'] . '</td>';

	$content .= '<td>' . $RowPo['FDAEUHoldDate'] . '</td>
              	<td>' . $RowPo['FDAEUReleaseCFIADate'] . '</td>'; //}

	$content .= '<td>' . $RowPo['FdaEntryNumber'] . '</td>
                <td>' . $RowPo['FDAEntryDate'] . '</td>
                <td>' . $RowPo['ClearingAgentInvoiceNo'] . '</td>
                <td>' . $RowPo['ADCharges'] . '</td>
                <td>' . $RowPo['CVDCharges'] . '</td>
                <td>' . $RowPo['HmDutyCharges'] . '</td>
                <td>' . $RowPo['MPCharges'] . '</td>
				<td  align="center" > <B>' . $RowPo['RTCharges'] . '</B> </td>';
	$content .= '<td>' . $OtherChargesSum . '</td>';

	$content .= '<td>' . $TotalPaidUSD . '</td>
                <td>' . $RowPo['ShippingBillNo'] . '</td>
                <td>' . $ShippingBillDate . '</td>				           
                <td>' . $RowPo['LEODate'] . '</td>
            	<td>' . number_format($FOBValueUSD, 2) . '</td>
                <td>' . number_format($FOBValueINR, 2) . '</td>
                <td>' . $RowPo['DrawBackRecvd'] . '</td>';
	//if($Exporter!=5){
	$content .= '<td>' . $RowPo['RodtepRecvd'] . '</td>
                <td>' . $RowPo['DrawBackRcvdAmount'] . '</td>
                <td>' . $DrawBackRcvdDate . '</td>
                <td>' . $MeisRcvdAmount . '</td>
                <td>' . $MeisApplDate . '</td>'; //}

	$content .= '<td>' . $RowTotal['QCertNo'] . '</td>
				<td>' . $QDateIssue . '</td>
				<td>' . number_format($FOBValueINR, 2) . '</td>
				<td>' . $RowProcessor['ExpApprovalNo'] . '</td>
				<td>' . $HCNumberDate . '</td>
				<td>' . $GPSNumberGPSDate . '</td>
				<td>' . $CoNumberCoDate . '</td>
				<td>' . $RowPo['InsuranceDtls'] . '</td></tr>';

	/**uma**/


	$StmtvarietyId = $iconn->query($SqlVarietyId);
	if ($VarietyCount > 1) {

		while ($RowVarietyId = $StmtvarietyId->fetch_array()) {


			$VarietyID = $RowVarietyId['VarietyID'];
			$PackingStyleID = $RowVarietyId['PackingStyleID'];


			$SqlVariety = "SELECT ShortName, Mcs,SPDT.FreezingType,APS.PackingStyle,Brand,SPDT.Grade
			FROM `ScrmCIGradeData` SPDT LEFT join 
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
				 <td></td>

				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td> ' . $TotalMcs . ' </td>
				 <td> ' . $ShortName . ' VANNAMEI SHRIMPS </td>
				 <td> ' . $FreezingType . ' </td>
				 <td> ' . $PackingStyle . ' </td>
				 <td> ' . $TotalGrades . ' </td>
				 <td  align="left"  > ' . $Brand . ' </td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 <td></td>
				 ';
			if ($Exporter != 5) {
				$content .= '<td     ></td>
                ';
			}

			$content .= ' </tr>';
		}
	}
	/**uma**/




	$S_NO++;
}



$content .= ' </table>';


$filename = $Header . '.xls';

// header("Content-Type: application/xls");
header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $filename . "");



// ob_clean();
// flush();

echo $content;
