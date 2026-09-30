<style>
	table tr td {
		font-size: 16px;
		min-width: 180px !important;
		text-align: center;
	}

	table tr th {
		font-size: 14px;
		text-align: center;
		background: white;
		position: sticky;
		top: 0;
		min-width: 100px !important;
	}
</style>
<?php

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$Type = $_GET['Type'];
if ($Type == 2 || $Type == 1) {
	$Month = $_GET['Month'];
	$Year = $_GET['Year'];
	$PsID = $_GET['PsID'];
} else {
	$Month = $_GET['from_date'];
	$Year = $_GET['to_date'];
}

$B_TYPE = $_GET['TYPE'];
$BAR_CODE_ID = $_GET['BID'];
$LOT_ID = $_GET['ID'];

if ($Type == 1) {
	$Query = "month(CInvDate)='$Month'  and year(CInvDate)='$Year'  ";
} else {
	$Query = "CInvDate between '$Month'  and '$Year'  ";
}

$Exporter = $_GET['Exporter'];

if ($Exporter == '') {
	$Query .= '';
} else {
	// if($Exporter==1){
	// 	$Query.='and SPD.ExporterID!=5';
	// }
	// 	else{

	$Query .= ' and SPD.ExporterID=' . $Exporter;
	//	}
	$SqlExporter = "SELECT * FROM AdminExporterDetails where ExpID=$Exporter";
	$StmtExporter = $iconn->query($SqlExporter);
	$RowExporter = $StmtExporter->fetch_array();
	$ExporterName = $RowExporter['ExpName'];
}

$file_name = 'FBD Report -' . $from_date . '-' . $to_date . '.xlsx';

$date_end = $to_date . ' 23:59:59';


$monthName = date("F", mktime(0, 0, 0, $Month, 10));

if ($Type == 1) {
	$Header = 'FBD Report-(' . $monthName . ')';
} else {
	$Header = 'FBD Report-(' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}


$SqlPo = "SELECT *,SPC.Name as PortofDischarge,SLM.LinerName as SteamshipCompany,
	GROUP_CONCAT(SCB.BankRefNo) AS BankRefNo, GROUP_CONCAT(SCB.ColDate) AS ColDate,
	ISF.FDAEUReleaseCFIADate, ISF.FDAEntryNumber, SPCL.DueDte As NegoDueDate,
	(SELECT TranshipDt FROM ScrmTranshipmentPortsList WHERE PIID=SCI.PiID and Status=1 ORDER BY `TspID` DESC LIMIT 1) As ETA_Date 
	FROM `ScrmCustomInvoices` as SCI 
	LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
	LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
    LEFT JOIN ScrmBankDetailsMaster as ABD on ABD.ID=SPD.BankID	
	LEFT JOIN ScrmLinerMaster as SLM on SLM.LinerID=SCI.ShippingLineID  
	LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortofDischarge 
	LEFT JOIN ScrmCollectionBreakUP as SCB on SCB.PiID=SPD.PoID
	LEFT JOIN ScrmISFDetails as ISF on ISF.PIID=SPD.PoID 
	LEFT JOIN ScrmPaymentCollections as SPCL on SPCL.PIID=SPD.PoID where $Query 
	and SPD.PoID not in (SELECT PoID from ScrmRealizationDetails where RezStatus=1) GROUP BY SCI.CInvNo";

$StmtPo = $iconn->query($SqlPo);

//and CHAR_LENGTH(SEA.BillRefNo)> 15


$content = '';


$size = 40;

$size1 = 60;


$S_NO = 1;
$width = 13;
$width_s = 8;
$width_w = $width + 5;

$content .= '
			<h3 align="center">' . $ExporterName . '</h3>
			<h3 align="center">' . $Header . '</h3>
			  
			  
			  <table width="100% "border="1" cellspacing="0" cellpadding="5">
			   <tr>  
                <th align="center"><b>S. No.</b></th>
				<th align="center"><b>Inv No</b></th>
				<th align="center"><b>Bill Value US $</b></th>
				<th align="center"><b>Name of the Buyer</b></th>
				<th align="center"><b>Buyer Order #</b></th>
				<th align="center"><b>Bank</b></th>
				<th align="center"><b>Ref Bill No.</b></th>
				<th align="center"><b>Bill Discount Date</b></th>
				<th align="center"><b>Due date As per bank</b></th>
				<th align="center"><b>Terms of Payment</b></th>
				<th align="center"><b>Container ETA Date</b></th>
				<th align="center"><b>POD / LINE</b></th>
				<th align="center"><b>Container Number</b></th>
				<th align="center"><b>FDA/EU Release/CFIA Date</b></th>
				<th align="center"><b>APPROVAL #</b></th>
				<th align="center"><b>US BROKER</b></th>
				<th align="center"><b>Buyer Agent</b></th>
               </tr> 
			  ';


$TotalFOBUS = 0;
$TotalFOB = 0;
$GrandTotalDiscount = 0;
$TotalNetWgtKgs = 0;
$TotalNetWgtLbs = 0;

while ($RowPo = $StmtPo->fetch_array()) {

	$PoID = $RowPo['PoID'];

	$SqlPaymentTerms = "SELECT * FROM ScrmPoData as SPD left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID WHERE PoID='$PoID'";
	$StmtPaymentTerms = $iconn->query($SqlPaymentTerms);
	$RowPaymentTerms = $StmtPaymentTerms->fetch_assoc();

	$SqlTenPlusTwo = "SELECT * FROM `$ScrmLogisticsDetails`  WHERE PiID='$PoID'";
	$StmtTenPlusTwo = $iconn->query($SqlTenPlusTwo);
	$RowTenPlusTwo = $StmtTenPlusTwo->fetch_assoc();


	$FbdEta = getFormatedDateNA($RowTenPlusTwo['FbdEta']);
	$ColDate = $RowPo['ColDate'];
	$colDateArr = array_filter(
		array_map('trim', explode(',', $ColDate))
	);
	$colDateArr = array_map('getFormatedDateNA', $colDateArr);
	$ColDate = implode(', ', $colDateArr);

	$NegoDueDate = getFormatedDateNA($RowPo['NegoDueDate']);
	$FDAEUReleaseCFIADate = isset($RowPo['FDAEUReleaseCFIADate']) ? getFormatedDateNA($RowPo['FDAEUReleaseCFIADate']) : 'NA';
	$ETA_Date = isset($RowPo['ETA_Date']) ? getFormatedDateNA($RowPo['ETA_Date']) : 'NA';

	$PaymentTerms = $RowPaymentTerms['PaymentName'];

	$BankName = $RowPo['BankName'];
	$ContainerNo = $RowPo['ContainerNo'];
	$PoNo = $RowPo['PoNo'];
	$CInvDate = $RowPo['CInvDate'];
	$ProcessorID = $RowPo['ProcessorID'];
	$Discount = $RowPo['Discount'];
	$PiID = $RowPo['PoID'];
	$FrieghtCharge = $RowPo['FrieghtCharge'];
	$DestinationCountryId = $RowPo['DestinationCountry'];
	$ExchangeRate = $RowPo['ExchangeRate'];
	$CInvCode = $RowPo['CInvCode'];
	$IGST = $RowPo['IGST'];
	$ShippingTerms = $RowPo['ShippingTerms'];
	$DestinationCountryId = $RowPo['DestinationCountry'];


	$SqlProcessor = "SELECT * FROM `AdminExporterDetails` WHERE ExpID='$ProcessorID'";
	$StmtProcessor = $iconn->query($SqlProcessor);
	$RowProcessor = $StmtProcessor->fetch_array();

	$CanadaLbsFactor = $DestinationCountryId == 23 && $CInvDate >= '2026-03-01' ? 2.2046 : 'LbsFactor';

	/********uma********/
	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


	$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PiID'";


	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	$NetWgtKgs = $RowTotal['NetWgtKgs'];
	$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
	$NetWgtLbs = $RowTotal['NetWgtLbs'];
	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	$RowPo['QtyUnit'];

	if ($RowPo['QtyUnit'] == 'KGS') {
		$Weight = $RowTotal['NetWgtKgs'];
		$Price = $RowTotal['PriceKgs'];
	} else {
		$Weight = $RowTotal['NetWgtLbs'] * 0.454;
		//$Weight = $RowTotal['NetWgtKgs'];// Suspisios
		$Price = $RowTotal['PriceLbs'];
	}



	$AdminConfigDetails['CanadaLbsFactor'] = $CanadaLbsFactor;
	$AryPaymentData = getPaymentValues($iconn, $PiID, '', $AdminConfigDetails);
	$InsuranceValue = $AryPaymentData['InsuranceValue'];
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$DiscountPerc = $AryPaymentData['DiscountPerc'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$IgstPerc = $AryPaymentData['IgstPerc'];
	$IgstValue = $AryPaymentData['IgstValue'];
	$InvValueUSD = $AryPaymentData['InvValueUSD'] + $InsuranceValue;
	$InvValueInr = $AryPaymentData['InvValueINR'];

	$TotalMcs = $AryPaymentData['TotalMcs'];
	$Price = $AryPaymentData['Price'];
	$TotalFOBUS += $FOBValueUS;
	$TotalFOB += $FOBValue;
	$GrandTotalDiscount += $InvValueUSD;
	$TotalNetWgtKgs += $NetWgtKgs;
	$TotalNetWgtLbs += $NetWgtLbs;

	$BillDiscountDate = getFormatedDate($RowPo['BillDiscountDate']);
	$DueDate = getFormatedDate($RowPo['DueDate']);


	/*********ma*******/

	$content .=

		'<tr>
			<td  align="center">' . $S_NO . '</td>
			<td  align="center">' . $RowPo['CInvNo'] . '</td>
			<td  align="center">' . number_format($InvValueUSD, 2) . '</td>
			<td  align="center">' . $RowPo['ConsigneeName'] . '</td>
			<td  align="center">' . $PoNo . '</td>
			<td  align="center">' . $BankName . '</td>
			<td  align="center">' . $RowPo['BankRefNo'] . '</td>
			<td  align="center">' . $ColDate . '</td>
			<td  align="center">' . $NegoDueDate . '</td>
			<td  align="center">' . $PaymentTerms . '</td>
			<td  align="center">' . $ETA_Date . '</td>
			<td  align="center">' . $RowPo['PortofDischarge'] . '/' . $RowPo['SteamshipCompany'] . '</td>
			<td  align="center">' . $ContainerNo . '</td>
			<td  align="center">' . $FDAEUReleaseCFIADate . '</td>
			<td  align="center">' . $RowProcessor['ExpApprovalNo'] . '</td>
			<td  align="center">' . $RowPo['UsBroker'] . '</td>
			<td  align="center">' . $RowPo['BuyerAgent'] . '</td>
		</tr>';

	$S_NO++;
}

$content .=
	'<tr>  
		<td  align="center"><b>Total</b></td>				 
		<td  align="center"></td>				 	 
		<td  align="center"><b>' . number_format($GrandTotalDiscount, 2) . '</b></td>				 
		<td  align="center"></td>				 
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
		<td  align="center"></td>
	</tr>';

$content .= '</table>';


echo $content;


?>