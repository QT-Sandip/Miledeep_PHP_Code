<?php

/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$Year = $_POST['Year'];
$ExpID = trim($_POST['ExpID']);
$ConsigneeID = trim($_POST['ConsigneeID']);
$CountryID = trim($_POST['CountryID']);
$Month = trim($_POST['Month']);
$Brand = trim($_POST['Brand']);

$FreightFromDate = trim($_POST['FreightFromDate']);
$FreightToDate = trim($_POST['FreightToDate']);


if ($Year != '') {
	$FromDate = $Year . '-04-01';
	$ToDate = ($Year + 1) . '-03-31';
	$Year = '';
} else {
	$FromDate = trim($_POST['FromDate']);
	$ToDate = trim($_POST['ToDate']);
}



$PortId = $_POST['PortId'];
$LinerId = $_POST['LinerId'];
$ClientID = !empty($_SESSION["ClientID"]) ? $_SESSION["ClientID"] : '';
$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$FreightRatesGraph = $ClientConfigData->FreightRatesGraph;

// temp conditions for SME
$JoinQuery = isset($ClientID) && $ClientID === 'SMEV3' ? ' left join ScrmRealizationDetails as SRD on SPD.PoID=SRD.PoID' : '';
$ClientQuery = isset($ClientID) && $ClientID === 'SMEV3' ? 'and SPD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 )
and SCI.PaymentType="Negotiations" and SRD.RezStatus=0' : 'and SPD.PoID not in(SELECT PoID from ScrmRealizationDetails where RezStatus=1)';

$SalesQty = (isset($_POST['Only']) && $_POST['Only']) ? 'True' : '';

$CurrentMonth = date('m');


if ($Year == '') {
	$Currentyear = date('Y');
} else {
	$Currentyear = $Year;
}

if ($CurrentMonth > 3 && $Year == '') {
	$FinClsYear = $Currentyear;
} else if ($Year != '') {
	$FinClsYear = $Year;
} else {
	$FinClsYear = $Currentyear - 1;
}


if ($CurrentMonth > 3) {
	$RunningFinStartYear = date('Y');
} else {
	$RunningFinStartYear = (date('Y') - 1);
}
//$FinClsYear=2023;

// $DateFilter = prepareDateFilter($Year, $Month);

$currentYear = date("Y");
$currentMonth = date("m");
if ($currentMonth >= 4) {
	// FY is currentYear-04-01 to (currentYear+1)-03-31
	$fyStart = $currentYear . "-04-01";
	$fyEnd = ($currentYear + 1) . "-03-31";
} else {
	// FY is (currentYear-1)-04-01 to currentYear-03-31
	$fyStart = ($currentYear - 1) . "-04-01";
	$fyEnd = $currentYear . "-03-31";
}


$isCurrentFY = ($FromDate >= $fyStart && $FromDate <= $fyEnd);


$DateFilter = "BETWEEN '$FromDate' and '$ToDate'";


// echo $DateFilter;
function getPoIds()
{
	global $iconn, $FinClsYear, $ExpID, $ConsigneeID, $CountryID;
	//Based Date Filter
	//Based On 
	$SqlOrderStatus = "";
	$OrdersExec = $iconn->query($SqlOrderStatus);
}

function getFilterQry()
{
	global $iconn, $FinClsYear, $ExpID, $ConsigneeID, $CountryID, $Brand;
	$Qry = "";

	if ($ExpID != '') {
		$Qry .= " and SPD.ExporterID='$ExpID'";
	}
	if ($ConsigneeID != '') {
		$Qry .= " and SPD.ConsigneeID='$ConsigneeID'";
	}
	if ($CountryID != '') {
		$Qry .= " and SPD.DestinationCountry='$CountryID'";
	}
	if ($Brand != '') {
		$Qry .= " and SPD.PoID  in (SELECT DISTINCT(InvId) FROM `ScrmPoGradeData` WHERE Brand='$Brand')";
	}

	return $Qry;
}

if ($SalesQty) {
	echo getDashBoardSalesQty();
} else {
	echo getDashBoard();
}

function getDashBoardSalesQty()
{
	global $iconn, $FinClsYear, $ExpID, $ConsigneeID, $RunningFinStartYear, $Currentyear;
	$ArySaleQty = getSalesShipmentsQty();
	$AryData['ArySaleQty'] = $ArySaleQty;
	return json_encode($AryData);
}




function getDashBoard()
{

	global $iconn, $FinClsYear, $ExpID, $ConsigneeID, $RunningFinStartYear, $Currentyear, $FreightToDate, $FreightFromDate, $isCurrentFY, $FreightRatesGraph;
	$AryPlantwiseGraphDtls = getPlantwiseGraph();

	$AryOrdersStatus = getOrdersStatus();
	// $AryPoDtls = getPoGraph();

	//Port - Containers
	$AryPortContainerDtls = getPortContainerGraph();
	$ArySaleNos = getSalesShipmentsNos();
	//$ArySaleQty = getSalesShipmentsQty();
	$CountryWiseDetailsPie = getCountryWiseOrdersPie();

	if (!empty($FreightRatesGraph)) {
		$FreightChargesData = getRmFreightCharges($FreightFromDate, $FreightToDate);
		$AryData['FreightChargesData'] = $FreightChargesData;
	}

	//var_dump($AryOrdersStatus);
	//if from date is current financial year then only show pending contracts
	//give me the code


	if (!$isCurrentFY) {
		$AryOrdersStatus['PendingContracts'] = 0;
	}


	$AryData['TileID1'] = $AryOrdersStatus['PendingContracts'];
	$AryData['TileID2'] = $AryOrdersStatus['OnWater'];
	$AryData['TileID3'] = $AryOrdersStatus['PendingPayments'];
	$AryData['TileID4'] = $AryOrdersStatus['OrdersReceived']; // no use
	$AryData['TileID4'] = $AryData['TileID1'] + $AryOrdersStatus['OnWater'] + $AryOrdersStatus['Delivered']; // By Ramessh DFL
	$AryData['TileID5'] = $AryOrdersStatus['Delivered'];
	$AryData['TileID6'] = $AryOrdersStatus['PaymentsReceived'];


	$AryData['ArySaleNos'] = $ArySaleNos;
	$AryData['ArySaleQty'] = $ArySaleQty;
	$AryData['AryPortContainerDtls'] = $AryPortContainerDtls;
	$AryData['CountryWiseDetailsPie'] = $CountryWiseDetailsPie;
	$AryData['AryPlantwiseGraphDtls'] = $AryPlantwiseGraphDtls;

	// $AryData['AryCountryPoDtls'] = $AryCountryPoDtls;



	// $AryData['AryPoDtls'] = $AryPoDtls;

	return json_encode($AryData);
}



function getOrdersStatus()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter, $JoinQuery, $ClientQuery, $ClientID;

	$Qry = getFilterQry();


	//Pending Contracts
	//  $SqlPendingContracts="SELECT Count(*) as PendingContracts from ScrmPoData as SPD WHERE PoID NOT in 
	// 			(SELECT PiID FROM `ScrmCustomInvoices` WHERE StockOutDate!='') $Qry and SPD.Status not in (2,3,4)";



	$SqlPendingContracts = "SELECT Count(*) as PendingContracts from ScrmPoData as SPD WHERE PoID NOT in 
				(SELECT PiID FROM `ScrmCustomInvoices` WHERE (StockOutDate IS NOT NULL AND StockOutDate != '')) $Qry and SPD.Status not in (2,3,4)";

	$PendingContractsExec = $iconn->query($SqlPendingContracts);
	$PendingContractsRow = $PendingContractsExec->fetch_array();

	$OnWaterDeliveredQuery = "AND (ISF.EmptyReturn IS NULL OR ISF.EmptyReturn = '')";

	// if ($ClientID == 'DFLV3') {
	// 	$OnWaterDeliveredQuery = "AND (
    //     (ISF.EmptyReturn IS NULL OR ISF.EmptyReturn = '')
    //     OR (SPD.DestinationCountry = 157 AND ISF.FDAExam = 'Yes'))";
	// }

	$SqlOnWater = "SELECT Count(*) as OnWater from ScrmPoData as SPD left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
					left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID WHERE (SCI.StockOutDate is not null AND SCI.StockOutDate != '')
					$OnWaterDeliveredQuery and SCI.CInvDate $DateFilter $Qry  AND  SPD.Status NOT IN (4)";
	//left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID WHERE TransportedDate!='' and DeliveredDate='' and    TransportedDate $DateFilter $Qry";


	$PendingOnWaterExec = $iconn->query($SqlOnWater);
	$OrdersOnWaterRow = $PendingOnWaterExec->fetch_array();
	$OnWater = $OrdersOnWaterRow['OnWater'];
	$OrderStatusRow['OnWater'] = $OnWater;


	//Delivered
	$SqlDelivered = "SELECT Count(*) as Delivered from ScrmPoData as SPD left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
	left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID where (SCI.StockOutDate !='' AND SCI.StockOutDate IS NOT NULL) and (ISF.EmptyReturn!='' AND ISF.EmptyReturn IS NOT NULL) 
	and   SCI.CInvDate $DateFilter $Qry and SPD.Status not in (4)";

	$DeliveredExec = $iconn->query($SqlDelivered);
	$OrdersDeliveredRow = $DeliveredExec->fetch_array();
	$Delivered = $OrdersDeliveredRow['Delivered'];
	$OrderStatusRow['Delivered'] = $Delivered;

	//Pending Contracts
	//$SqlOrdersReceived="SELECT Count(*) as OrdersReceived from ScrmPoData as SPD WHERE PoDate $DateFilter $Qry";
	$SqlOrdersReceived = "SELECT Count(*) as OrdersReceived from ScrmPoData as SPD  
		 where  SPD.PoID  in 
		(SELECT PiID FROM `ScrmCustomInvoices` WHERE  StockOutDate !='' and CInvDate $DateFilter $Qry )";

	$SqlOrdersReceived = "SELECT Count(*) as OrdersReceived from ScrmPoData as SPD where SPD.PoDate  $DateFilter $Qry";
	$PendingOrdersReceivedExec = $iconn->query($SqlOrdersReceived);
	$OrdersReceivedRow = $PendingOrdersReceivedExec->fetch_array();
	$OrdersReceived = $OrdersReceivedRow['OrdersReceived'];
	$OrderStatusRow['OrdersReceived'] = $OrdersReceived;





	// Pending Payments
	// $SqlPendingPayments = "SELECT Count(*) as PendingPayments from ScrmPoData as SPD 
	// 						left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
	// 						WHERE StockOutDate!='' and  PoID NOT in 
	// 						(SELECT PIID from ScrmPaymentCollections where Status=1) and CInvDate $DateFilter $Qry";

	$SqlPendingPayments = "SELECT Count(*) as PendingPayments from ScrmPoData as SPD 
		left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
		WHERE StockOutDate!='' and  PoID NOT in
		(SELECT PoID from ScrmRealizationDetails where RezStatus=1) and CInvDate $DateFilter $Qry and SPD.Status not in (2,3,4)";

	$SqlPendingPayments123 = "SELECT Count(*) as PendingPayments FROM  ScrmPoData as SPD
			left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
			left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on SPD.PoID=SSDB.PiID
			left join ScrmISFDetails as SIF on SPD.PoID=SIF.PIID
			left join ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
			left join ScrmPaymentCollections as SPCL on SPCL.PIID=SPD.PoID
			left join ScrmCommercialData as SCOD on SCOD.PoID=SCI.PiID
			left join ScrmBankDetailsMaster as SBDM on SBDM.ID=SCOD.BankID
			$JoinQuery		 
			WHERE SSDB.TransportedDate!='' and SPD.Status not in (3,7,4)  and  SCI.CInvDate $DateFilter $Qry
			$ClientQuery
			order by SPD.PoID desc";

	$PendingPaymentsExec = $iconn->query($SqlPendingPayments);
	$PendingPaymentsRow = $PendingPaymentsExec->fetch_array();


	//Collection Recieved
	// $SqlCollectionRecieved = "SELECT Count(*) as CollectionRecieved
	// 				from ScrmPoData as SPD left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
	// 						WHERE StockOutDate!='' and  PoID  in 
	// 						(SELECT PIID from ScrmPaymentCollections where Status=1 and Type=1)  and CInvDate $DateFilter $Qry";

	$SqlCollectionRecieved = "SELECT Count(*) as PaymentRecieved from ScrmPoData as SPD 
left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
WHERE StockOutDate!='' and  PoID  in 
(SELECT PoID from ScrmRealizationDetails where RezStatus=1)  and CInvDate $DateFilter  $Qry
and SPD.Status not in (4)";

	$CollectionRecievedExec = $iconn->query($SqlCollectionRecieved);
	$CollectionRecievedRow = $CollectionRecievedExec->fetch_array();

	//Negotiation Recieved 
	// 	 $SqlNegotiationRecieved = "SELECT Count(*) as NegotiationRecieved from ScrmPoData as SPD 
	// left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
	// left join ScrmRealizationDetails as SREZ on SREZ.PoID=SPD.PoID WHERE 
	// SREZ.RezStatus=1 and SCI.PaymentType='Negotiations'  and CInvDate $DateFilter $Qry";  //and SREZ.Type=2
	// 	$NegotiationRecievedExec = $iconn->query($SqlNegotiationRecieved);
	// 	$NegotiationRecievedRow = $NegotiationRecievedExec->fetch_array();

	// echo $CollectionRecievedRow['CollectionRecieved']; 

	$OrderStatusRow['PendingContracts'] = $PendingContractsRow['PendingContracts'];
	$OrderStatusRow['PendingPayments'] = $PendingPaymentsRow['PendingPayments'];
	// $OrderStatusRow['PaymentsReceived'] = $CollectionRecievedRow['CollectionRecieved']+ $NegotiationRecievedRow['NegotiationRecieved'];
	$OrderStatusRow['PaymentsReceived'] = $CollectionRecievedRow['PaymentRecieved'];



	return $OrderStatusRow;
}


function getSalesShipmentsNos()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter;

	$Qry = getFilterQry();



	$SqlSales = "SELECT MN.MonthName as MonthName,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif as SDB left join ScrmPoData as SPD on SPD.PoID=SDB.PiID 
 WHERE Month(TransportedDate)=MN.Month and TransportedDate $DateFilter $Qry AND SPD.PoID != '' AND SPD.Status NOT IN (4)) as Shipments,
(SELECT COUNT(*) from ScrmCustomInvoices  as SDB left join ScrmPoData as SPD on SPD.PoID=SDB.PiID  
 WHERE Month(CInvDate)=MN.Month and CInvDate $DateFilter $Qry AND (SPD.PoID != '' AND SPD.PoID  IS NOT NULL ) AND SPD.Status NOT IN (4)) as Orders
from ScrmMonthNames as MN  order by SortOrder asc";




	error_log("✅ getSalesShipmentsNos: " . print_r($SqlSales, true));
	$SalesExec = $iconn->query($SqlSales);


	while ($SalesRow = $SalesExec->fetch_array()) {


		$InvIds = $SalesRow['InvIds'];
		$MonthName[] = $SalesRow['MonthName'];
		$Orders[] = $SalesRow['Orders'];
		$Shipments[] = $SalesRow['Shipments'];
	}

	$AryData['MonthName'] = $MonthName;
	$AryData['Orders'] = $Orders;
	$AryData['Shipments'] = $Shipments;

	return $AryData;
}

function getPlantwiseGraph()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter, $isCurrentFY, $Currentyear;

	$Qry = getFilterQry();
	$SqlPlantWiseOrdersQuery = "SELECT 
    AED.ExpApprovalNo AS StationName, 
    AED.ExpDisplayName,
    AED.ExpID,

    (SELECT COUNT(*)
     FROM ScrmPoData SPD 
     WHERE SPD.ProcessorID = AED.ExpID 
       AND SPD.PoDate $DateFilter  $Qry
    ) AS Total,

    (SELECT COUNT(*)
     FROM ScrmPoData SPD
     LEFT JOIN ScrmCustomInvoices SCI ON SPD.PoID = SCI.PiID 
     WHERE SPD.ProcessorID = AED.ExpID 
       AND (SCI.StockOutDate IS NOT NULL AND SCI.StockOutDate != '')  AND SPD.Status NOT IN (2, 3, 4)
       AND SCI.CInvDate $DateFilter
      	$Qry
    ) AS Shipped,

    (SELECT Count(*) as PendingContracts from ScrmPoData as SPD WHERE PoID NOT in 
				(SELECT PiID FROM `ScrmCustomInvoices` WHERE (StockOutDate IS NOT NULL AND StockOutDate != '')) $Qry and SPD.Status not in (2,3,4) AND SPD.ProcessorID = AED.ExpID ) AS Pending

FROM AdminExporterDetails AED
WHERE AED.ExpType IN (2, 3)  
  AND AED.Status = 1
GROUP BY AED.ExpApprovalNo, AED.ExpDisplayName, AED.ExpID
ORDER BY AED.ExpDisplayName ASC;
";
	$stationNamesArray = [];
	$ordersReceivedArray = [];
	$shippedArray = [];
	$PendingArray = [];
	$ExporterDetails = [];

	$AllTotal = 0;
	$AllShipped = 0;
	$AllPending = 0;
	// Execute the query
	$SqlPlantWiseOrdersDetails = $iconn->query($SqlPlantWiseOrdersQuery);
	// Fetch data row by row and collect in arrays
	while ($SqlPlantWiseOrdersRows = $SqlPlantWiseOrdersDetails->fetch_array(MYSQLI_ASSOC)) {


		$pendingValue = ($isCurrentFY) ? $SqlPlantWiseOrdersRows['Pending'] : 0;
		$shippedValue = $SqlPlantWiseOrdersRows['Shipped'];
		if ($pendingValue > 0 || $shippedValue > 0) {
			$stationNamesArray[] = $SqlPlantWiseOrdersRows['ExpDisplayName'] ? $SqlPlantWiseOrdersRows['ExpDisplayName'] . '(' . $SqlPlantWiseOrdersRows['StationName'] . ')' : $SqlPlantWiseOrdersRows['StationName'];
			$ordersReceivedArray[] = floatval($SqlPlantWiseOrdersRows['Shipped']) + floatval($pendingValue);
			$shippedArray[] = $SqlPlantWiseOrdersRows['Shipped'];
			$PendingArray[] = $pendingValue;
			$ExporterDetails[] = $SqlPlantWiseOrdersRows['ExpID'];

			$AllTotal += floatval($SqlPlantWiseOrdersRows['Shipped']) + floatval($pendingValue);
			$AllShipped += floatval($SqlPlantWiseOrdersRows['Shipped']);
			$AllPending += floatval($pendingValue);
		}
	}

	$stationNamesArray[] = 'All';
	$ExporterDetails[] = 'ALL';
	$series = [];
	$shippedArray[] = $AllShipped;
	if ($isCurrentFY) {
		$ordersReceivedArray[] = $AllTotal;
		$PendingArray[] = $AllPending;
		$series[] = [
			'name' => 'Total',
			'data' => $ordersReceivedArray
		];
		$series[] = [
			'name' => 'Shipped',
			'data' => $shippedArray
		];
		$series[] = [
			'name' => 'Pending',
			'data' => $PendingArray
		];
	} else {
		$series[] = [
			'name' => 'Shipped',
			'data' => $shippedArray
		];
	}

	$AryData['stationNames'] = $stationNamesArray;
	$AryData['series'] = $series;
	$AryData['exporterDetails'] = $ExporterDetails;

	return $AryData;
}


function getCountryWiseOrders()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter;

	$Qry = getFilterQry();

	$SqlSales = "SELECT COUNT(*) Orders,COALESCE(SCL.name,'Other') as CountryName  from ScrmPoData as SPD 
LEFT join ScrmCountriesList as SCL On SCL.id=SPD.DestinationCountry 
WHERE SPD.PoDate  $DateFilter $Qry GROUP by SPD.DestinationCountry 
ORDER by Orders desc";


	$SalesExec = $iconn->query($SqlSales);


	while ($SalesRow = $SalesExec->fetch_array()) {

		$InvIds = $SalesRow['InvIds'];
		$CountryName[] = $SalesRow['CountryName'];
		$Orders[] = $SalesRow['Orders'];
	}




	$AryData['CountryName'] = $CountryName;
	$AryData['Orders'] = $Orders;

	return $AryData;
}

function getCountryWiseOrdersPie()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter;

	$Qry = getFilterQry();



	$SqlSales = "SELECT COUNT(*) Orders,COALESCE(SCL.name,'Other') as CountryName  from ScrmPoData as SPD 
LEFT join ScrmCountriesList as SCL On SCL.id=SPD.DestinationCountry 
WHERE  SPD.PoID  in 
(SELECT PiID FROM `ScrmCustomInvoices` WHERE CInvDate $DateFilter and StockOutDate !='' AND StockOutDate IS NOT NULL )  $Qry and SPD.Status not in (4) GROUP by SPD.DestinationCountry
ORDER by Orders desc";
	$SalesExec = $iconn->query($SqlSales);

	while ($RowPorts = $SalesExec->fetch_array()) {
		$RowPorts['Color'] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
		$PortSeries[] = $RowPorts['Orders'] + 0;
		$PortLables[] = $RowPorts['CountryName'];
		$PortColors[] = $RowPorts['Color'];
		$Cjs['x'] = $RowPorts['CountryName'];
		$Cjs['y'] = round($RowPorts['Orders'] + 0);
		$Cjs['PortID'] = $RowPorts['1'];

		$dataPoints[] = $Cjs;
		$AryPortDtls[] = $RowPorts;
	}

	$AryData['PortSeries'] = $PortSeries;
	$AryData['PortLables'] = $PortLables;
	$AryData['PortColors'] = $PortColors;
	$AryData['PortList'] = $AryPortDtls;
	$AryData['dataPoints'] = $dataPoints;
	return $AryData;
}

function getSalesShipmentsQty()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter, $isCurrentFY, $Currentyear;

	$Qry = getFilterQry();

	//Exported Pos 
	$SqlExported = "SELECT COALESCE(GROUP_CONCAT(PoID),0)  as ExportPoIds from ScrmPoData as SPD 
	left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
 	left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID where (SCI.StockOutDate !='' OR SCI.StockOutDate is not null) and    SCI.CInvDate $DateFilter $Qry and SPD.Status not in (2,3,4)";
	$SqlExportedExec = $iconn->query($SqlExported);
	$SqlExportedRow = $SqlExportedExec->fetch_array();

	$ExportPoIds = $SqlExportedRow['ExportPoIds'];


	$SqlPendingContracts = "SELECT COALESCE(GROUP_CONCAT(PoID),0)  as  ShipPoIds from ScrmPoData as SPD 
	WHERE PoID NOT in (SELECT PiID FROM `ScrmCustomInvoices` where (StockOutDate !='' AND StockOutDate is not null) )   $Qry and SPD.Status not in (2,3,4)";
	$PendingContractsExec = $iconn->query($SqlPendingContracts);
	$PendingContractsRow = $PendingContractsExec->fetch_array();


	$ShipPoIds = $PendingContractsRow['ShipPoIds'];


	$SqlToBeShippedIds = "SELECT COALESCE(GROUP_CONCAT(SGD.InvId), 0) AS toBeShippedIds
FROM `ScrmPoGradeData` AS SGD
LEFT JOIN `ScrmPoData` AS SPD ON SGD.InvId = SPD.PoID
LEFT JOIN `ScrmCustomInvoices` AS SCI ON SCI.PiID = SGD.InvId
WHERE SCI.CInvDate IS NULL 
  AND (SCI.StockOutDate = '' OR SCI.StockOutDate IS NULL) $Qry  AND SPD.Status NOT IN (2, 3, 4)";
	$SqlToBeShippedExec = $iconn->query($SqlToBeShippedIds);
	$SqlToBeShippedRow = $SqlToBeShippedExec->fetch_array();

	$toBeShippedIds = $SqlToBeShippedRow['toBeShippedIds'];


	$SqlShippedIds = "SELECT COALESCE(GROUP_CONCAT(SGD.InvId), 0) AS ShippedIds
		FROM `ScrmPoGradeData` AS SGD 
		LEFT JOIN ScrmCustomInvoices AS SCI	ON SCI.PiID = SGD.InvId 
		left join ScrmPoData as SPD on SCI.PiID=SPD.PoID 
		WHERE  SCI.CInvDate IS NOT NULL AND SCI.CInvDate  $DateFilter AND 
		(SCI.StockOutDate != '' AND SCI.StockOutDate IS NOT NULL)  $Qry  and SPD.Status not in (2,3,4)";
	$SqlShippedExec = $iconn->query($SqlShippedIds);
	$SqlShippedRow = $SqlShippedExec->fetch_array();

	$ShippedIds = $SqlShippedRow['ShippedIds'];



	$SqlShipments = "SELECT Grade, 
	 (SELECT COALESCE(sum(Mcs*TotalSlabs*SlabWeight*Mfactor),0) from ScrmPoGradeData as PGD1
					left join AdminPackingStyles as APS on APS.PCKID=PGD1.PackingStyleID 
                    WHERE InvId in ($ShippedIds) and PGD1.Grade=PGD.Grade ) as Shipped,
      (SELECT COALESCE(sum(Mcs*TotalSlabs*SlabWeight*Mfactor),0) from ScrmPoGradeData as PGD2
					left join AdminPackingStyles as APS on APS.PCKID=PGD2.PackingStyleID 
                    WHERE InvId in ($toBeShippedIds) and PGD2.Grade=PGD.Grade ) as Export
                   from ScrmPoGradeData as PGD
                    group by Grade
					ORDER by Abs(Grade) ASC";
	$ShipmentsExec = $iconn->query($SqlShipments);


	while ($ShipmentsRow = $ShipmentsExec->fetch_array()) {

		if ($ShipmentsRow['Export'] != 0 || $ShipmentsRow['Shipped'] != 0) {

			$ExportKgs[] = ($isCurrentFY) ? round($ShipmentsRow['Export'] / 1000, 3) : 0;
			$ShippedKgs[] = round($ShipmentsRow['Shipped'] / 1000, 3);
			$Grade[] = $ShipmentsRow['Grade'];
		}
	}

	$TotalExportKgs = round(array_sum($ExportKgs));
	$TotalShippedKgs = round(array_sum($ShippedKgs));



	$AryData['ExportKgs'] = $ExportKgs;
	$AryData['ShippedKgs'] = $ShippedKgs;
	$AryData['Grade'] = $Grade;
	$AryData['TotalExportKgs'] = $TotalExportKgs;
	$AryData['TotalShippedKgs'] = $TotalShippedKgs;

	return $AryData;
}

function getPortContainerGraph()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter, $ClientID;

	$Qry = getFilterQry();



	$SqlPorts = "SELECT COALESCE(SC.name,'Na') as CountryName,
Max(SPD.PoDate) as LastShipment, PortId,
COALESCE(SPC.Name,'Na') as PortName,COUNT(*) as Total FROM 
`ScrmShipmentDetailsBreif` as  SSD LEFT join
ScrmPoData  as SPD on SPD.PoID=SSD.PiID
left join ScrmPortCities  as SPC on SPD.PortOfDischarge=SPC.PortId
left join ScrmCountriesList  as SC on SC.id=SPC.Country
left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
left join ScrmISFDetails as SIF on SPD.PoID=SIF.PIID
WHERE SCI.CInvDate $DateFilter  and SSD.TransportedDate!=''  $Qry and  SCI.StockOutDate !='' 
and (SIF.EmptyReturn is null or  SIF.EmptyReturn='')
 GROUP by PortId ORDER by Total DESC";
	//and SSD.DeliveredDate=''
	//$StmtPorts=$iconn->query($SqlPorts);  //

	$OnWaterCondition = "(SIF.EmptyReturn IS NULL OR SIF.EmptyReturn = '')";

	// if ($ClientID == 'DFLV3') {
	// 	$OnWaterCondition = "(
    //     (SIF.EmptyReturn IS NULL OR SIF.EmptyReturn = '')
    //     OR (SPD.DestinationCountry = 157 AND SIF.FDAExam = 'Yes'))";
	// }

	$SqlPorts = "SELECT 
    COALESCE(SC.name, 'Na') AS CountryName,
    MAX(SPD.PoDate) AS LastShipment, 
    SPC.PortId,
    COALESCE(SPC.Name, 'Na') AS PortName,

    -- Total count (keeping the COUNT as it is for Total)
    COUNT(*) AS Total,

    -- OnWater Count (assuming this is the condition where goods are still on water)
    COUNT(
        CASE
            WHEN ((SCI.StockOutDate != '' AND SCI.StockOutDate IS NOT NULL) AND $OnWaterCondition AND IsfID IS NOT NULL AND  SPD.Status NOT IN (4))
            THEN SPD.PoID
        END
    ) AS OnWater,

    -- Delivered Count (assuming this is when goods are delivered)
    COUNT(
        CASE
            WHEN (SCI.StockOutDate != '' AND SCI.StockOutDate IS NOT NULL) AND (SIF.EmptyReturn != '' AND SIF.EmptyReturn IS NOT NULL ) AND  SPD.Status NOT IN (4)
            THEN SPD.PoID
        END
    ) AS Delivered

FROM 
    ScrmShipmentDetailsBreif SSD
LEFT JOIN 
    ScrmPoData SPD ON SPD.PoID = SSD.PiID
LEFT JOIN 
    ScrmPortCities SPC ON SPD.PortOfDischarge = SPC.PortId
LEFT JOIN 
    ScrmCountriesList SC ON SC.id = SPC.Country
LEFT JOIN 
    ScrmCustomInvoices SCI ON SCI.PiID = SPD.PoID
LEFT JOIN 
    ScrmISFDetails SIF ON SPD.PoID = SIF.PIID

WHERE 
   (SCI.StockOutDate != '' AND SCI.StockOutDate IS NOT NULL) AND  SCI.CInvDate $DateFilter $Qry and SPD.Status not in (4)

GROUP BY 
    SPC.PortId

ORDER BY 
    Total DESC;

	";


	$PortsExec = $iconn->query($SqlPorts);


	while ($RowPorts = $PortsExec->fetch_array()) {

		$PortID = $RowPorts['PortId'];

		$RowPorts['Color'] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
		$PortSeries[] = $RowPorts['Total'] + 0;
		$PortLables[] = $RowPorts['PortName'];
		$PortColors[] = $RowPorts['Color'];
		$Cjs['x'] = $RowPorts['PortName'];
		$Cjs['y'] = round($RowPorts['Total'] + 0);
		$Cjs['PortID'] = $PortID;
		$Cjs['OnWaterAndDelivered'] = [$RowPorts['OnWater'], $RowPorts['Delivered']];

		$dataPoints[] = $Cjs;
		$AryPortDtls[] = $RowPorts;
	}

	$AryData['PortSeries'] = $PortSeries;
	$AryData['PortLables'] = $PortLables;
	$AryData['PortColors'] = $PortColors;
	$AryData['PortList'] = $AryPortDtls;
	$AryData['dataPoints'] = $dataPoints;
	return $AryData;
	// return $AryPortDtls;

}
// echo json_encode($AryData, JSON_PRETTY_PRINT);
// exit;


function getPoGraph()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter;

	$Qry = getFilterQry();


	$SqlShiftDetails = "SELECT COUNT(*) Total,ProcessorID,COALESCE(ExpApprovalNo,'Other') as Name , 
COALESCE(Color,'#ff3d60')as Color from ScrmPoData as SPD 
LEFT join ScrmShipmentStatusCodes as SSC On SPD.Status=SSC.Code 
LEFT join AdminExporterDetails as AED On AED.ExpID=SPD.ProcessorID 

  WHERE SPD.PoID NOT in 
(SELECT PiID FROM `ScrmCustomInvoices` where StockOutDate!='' AND CInvDate $DateFilter )AND SPD.PoDate $DateFilter $Qry and SPD.Status not in (2,3,4) GROUP by SPD.ProcessorID ";



	$StmtShiftDetails = $iconn->query($SqlShiftDetails);
	while ($AttShiftRow = $StmtShiftDetails->fetch_array()) {
		$AttShiftRow['Color'] = $AttShiftRow['Color'];
		$PoSeries[] = $AttShiftRow['Total'] + 0;
		$PoLables[] = $AttShiftRow['Name'];
		$PoColors[] = $AttShiftRow['Color'];
		$PoTotal = array_sum($Series);

		$Cjs['x'] = $AttShiftRow['Name'];
		$Cjs['y'] = round($AttShiftRow['Total'] + 0);
		$Cjs['ProcessorID'] = round($AttShiftRow['ProcessorID']);
		$dataPoints[] = $Cjs;

		$AryPoDtls[] = $AttShiftRow;
	}




	$AryData['PoSeries'] = $PoSeries;
	$AryData['PoLables'] = $PoLables;
	$AryData['PoColors'] = $PoColors;
	$AryData['PoTotal'] = $PoTotal;
	$AryData['PoDtls'] = $AryPoDtls;
	$AryData['dataPoints'] = $dataPoints;


	return $AryData;
}


function getPlantWiseShipmets()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter, $Query;

	$SqlShiftDetails = "SELECT COUNT(*) Total,COALESCE(Name,'Other') as Name ,
	Status,COALESCE(Color,'#ff3d60')as Color from ScrmPoData as SPD 
	LEFT join ScrmShipmentStatusCodes as SSC On SPD.Status=SSC.Code 
	WHERE SPD.PoDate $DateFilter $Query
	GROUP by Status";
	$StmtShiftDetails = $iconn->query($SqlShiftDetails);
	while ($AttShiftRow = $StmtShiftDetails->fetch_array()) {
		$AttShiftRow['Color'] = $AttShiftRow['Color'];
		$PoSeries[] = $AttShiftRow['Total'] + 0;
		$PoLables[] = $AttShiftRow['Name'];
		$PoColors[] = $AttShiftRow['Color'];
		$PoTotal = array_sum($Series);

		$AryPoDtls[] = $AttShiftRow;
	}


	$AryData['PlntSeries'] = $PoSeries;
	$AryData['PlntLables'] = $PoLables;
	$AryData['PlntColors'] = $PoColors;
	$AryData['PlntTotal'] = $PoTotal;
	$AryData['PlntDtls'] = $AryPoDtls;

	return $AryData;
}
/*freight rates graph starts*/
function getRmFreightCharges($FreightFromDate, $FreightToDate)
{
	global $iconn, $PortId, $LinerId;
	$RmFromDate = $FreightFromDate;
	$RmToDate = $FreightToDate;
	$Query = '';
	$PortId = trim(implode(',', $PortId), ',');
	if ($PortId != '') {
		$Query .= " AND fg.portId IN($PortId)";
	}
	//echo '<pre>';echo count($LinerId);die;
	$LinerIds = trim(implode(',', $LinerId), ',');
	if (!empty($LinerIds)) {
		$Query .= " AND fg.LinerID  IN($LinerIds)";
	}

	$sqlCQuery = "WITH RECURSIVE DateRange AS (
                    SELECT DATE('$RmFromDate') AS DATE
                    UNION ALL
                    SELECT DATE_ADD(DATE, INTERVAL 1 DAY)
                    FROM DateRange
                    WHERE DATE < '$RmToDate'
                    ),
                     liners AS (
                        (SELECT NULL AS `ParentID`,NULL DestinationPortID,LinerID, LinerName,COALESCE(0, 0) AS Rate
                            ,NULL AS FromDate,NULL ToDate ,NULL portId 
                            FROM ScrmLinerMaster 
                            WHERE LinerID NOT IN(SELECT Liner FROM ScrmFreightRates 
                                WHERE Country != 0 -- newly added
                            )
                        )
                        UNION 
                        (SELECT `ParentID`,DestinationPortID,slm.LinerID LinerID,CONCAT(LinerName,' - ',`Name`) LinerName,COALESCE(sfrb.Rate, 0) AS Rate
                            ,sfr.FromDate,sfr.ToDate,sfrb.`DestinationPortID` portId 
                            FROM `ScrmPortCities` spc
                            LEFT JOIN `ScrmFreightRatesBreakUp` sfrb ON sfrb.`DestinationPortID`=spc.`PortId`
                            JOIN DateRange d    
                             JOIN ScrmFreightRates sfr  ON sfr.`ID` = sfrb.`ParentID`  AND d.Date BETWEEN sfr.FromDate AND sfr.ToDate
                           -- JOIN ScrmFreightRates sfr  ON sfr.`ID` = sfrb.`ParentID`  AND sfr.FromDate <= '$RmFromDate'  AND sfr.ToDate >= '$RmToDate'
							JOIN ScrmLinerMaster slm ON slm.LinerID=sfr.`Liner`
                             WHERE sfr.Country != 0 -- newly added
                            GROUP BY `ParentID`,sfrb.`DestinationPortID`,CONCAT(LinerName,' - ',`Name`) ORDER BY `BreakupID` DESC
                        )
                    ),
                    FullGrid AS (
                        SELECT d.Date, `ParentID`,DestinationPortID,l.LinerID, l.LinerName,Rate
                        ,FromDate,ToDate,portId
                        FROM DateRange d
                        CROSS JOIN liners l
                    )
                    SELECT 
                        fg.Date,`ParentID`,DestinationPortID,
                        LinerName,LinerID,
                        CASE WHEN sfr.Liner IS NOT NULL THEN COALESCE(fg.Rate, 0) ELSE COALESCE(0, 0) END AS Rate
                    FROM 
                        FullGrid fg 
                        LEFT JOIN ScrmFreightRates sfr  ON  sfr.Liner = fg.LinerID  AND fg.Date BETWEEN fg.FromDate AND fg.ToDate
                        WHERE 1 = 1  
                         $Query
                        -- and ParentID=66 and LinerName in('PIL LINE - AR RUWAYS')
                        /*AND slm.LinerID IS NOT NULL*/
                         -- Optional: filter by Country  PIL LINE - AR RUWAYS, PIL LINE - KHOR AL FAKKAN `ParentID`=32,
                         GROUP BY fg.Date,
                        -- `ParentID`,
                         DestinationPortID,
                         LinerName,Rate
                    ORDER BY  fg.Date, fg.LinerName,ParentID";
	//echo '<pre>'; echo $sqlCQuery;die;
	$stmtC = $iconn->query($sqlCQuery);
	while ($dataRow = $stmtC->fetch_assoc()) {
		$aryData[] = $dataRow;
	}

	foreach ($aryData as $row) {
		$date = $row['Date'];
		$liner = $row['LinerName'];
		$rate = (float) $row['Rate'];


		if (!isset($final[$date])) {
			$final[$date] = ['Date' => date("d-m-Y", strtotime($date))];
		}
		if (!isset($final[$date][$liner])) {
			$final[$date][$liner] = $rate;
		} else {

			//if(in_array($liner,array_keys($final[$date])) && $final[$date][$liner]<$rate) {
			if (in_array($liner, array_keys($final[$date])) && $rate !== 0.00) {
				$final[$date][$liner] = $rate;
			}
		}
		//$final[$date][$liner] = $rate;
	}

	// Reindex by values to get array of objects
	$final = array_values($final);
	//echo '<pre>';print_r($final);die;
	$finalArr = [];
	$k = 0;
	foreach ($final as $i => $ItemArr) {
		foreach ($ItemArr as $key => $Item) {
			// $date = $Item;
			//if ($key === 'Date') continue; 
			$res = checkValueExistsOrNot($finalArr, $key, $Item); //for removing doublicate values
			if (!empty($res)) {
				// $finalArr[$i][key] = $Item;
				//   $finalArr[$i][$key] = $Item;
				$finalArr[$i] = $ItemArr;
			}
		}
	}
	// $finalArr = array_filter($finalArr);
	$finalArr = array_values($finalArr);
	$result = null;
	/* if(!empty($final)){
		$result = array_merge($finalArr, [end($final)]);
	} */
	return $finalArr;
	return $final;
}
function checkValueExistsOrNot($arr = [], $MainArryKey, $rate)
{
	if (!empty($arr)) {
		$arr = end($arr); //0
		foreach ($arr as $k => $row) {
			//if($liner != 'Date' && $row[$liner] != $rate){
			if ($MainArryKey != 'Date' && $MainArryKey == $k && $row != $rate) {
				return true;
			}
		}
		return false;
	}
	return true;
}
/*freight rates graph ends*/