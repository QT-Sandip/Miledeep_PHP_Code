<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$FinClsYear = $_POST['Year'];
$ExpID = trim($_POST['ExpID']);
$ConsigneeID = trim($_POST['ConsigneeID']);
$CountryID = trim($_POST['CountryID']);
$Month = trim($_POST['Month']);
$Brand = trim($_POST['Brand']);
//$FinClsYear=2023;

if ($Month != '') {
	$Month = sprintf('%02d', $Month);
	if ($Month > 3) {
		$DateStart = $FinClsYear . '-' . $Month . '-01';
		$DateEnd = ($FinClsYear) . '-' . $Month . '-31';
	} else {
		$DateStart = ($FinClsYear + 1) . '-' . $Month . '-01';
		$DateEnd = ($FinClsYear + 1) . '-' . $Month . '-31';
	}
	$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
} else {
	$DateStart = $FinClsYear . '-04-01';
	$DateEnd = ($FinClsYear + 1) . '-03-31';
	$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
}


//echo $DateFilter;
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

echo getDashBoard();




function getDashBoard()
{

	$AryOrdersStatus = getOrdersStatus();
	$AryPoDtls = getPoGraph();
	$AryPortDtls = getPortGraph();
	$ArySaleNos = getSalesShipmentsNos();
	$ArySaleQty = getSalesShipmentsQty();
	//$AryCountryPoDtls = getCountryWiseOrders();
	$AryCountryPoDtlsPie = getCountryWiseOrdersPie();

	//var_dump($AryOrdersStatus);
	$AryData['TileID1'] = $AryOrdersStatus['PendingContracts'];
	$AryData['TileID2'] = $AryOrdersStatus['OnWater'];
	$AryData['TileID3'] = $AryOrdersStatus['PendingPayments'];
	$AryData['TileID4'] = $AryOrdersStatus['OrdersReceived']; // no use
	$AryData['TileID4'] = $AryOrdersStatus['PendingContracts'] + $AryOrdersStatus['OnWater'] + $AryOrdersStatus['Delivered']; // By Ramessh DFL
	$AryData['TileID5'] = $AryOrdersStatus['Delivered'];
	$AryData['TileID6'] = $AryOrdersStatus['PaymentsReceived'];


	$AryData['ArySaleNos'] = $ArySaleNos;
	$AryData['ArySaleQty'] = $ArySaleQty;
	$AryData['AryPortDtls'] = $AryPortDtls;
	$AryData['AryPoDtls'] = $AryPoDtls;
	$AryData['AryCountryPoDtls'] = $AryCountryPoDtls;
	$AryData['AryCountryPoDtlsPie'] = $AryCountryPoDtlsPie;

	return json_encode($AryData);
}


function getOrdersStatus()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter;

	$Qry = getFilterQry();


	$SqlOnWater = "SELECT Count(*) as OnWater from ScrmPoData as SPD left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
					left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID where (SCI.StockOutDate !=''  OR SCI.StockOutDate IS NOT NULL)
					and (ISF.EmptyReturn is null or  ISF.EmptyReturn='') and    SCI.CInvDate $DateFilter $Qry and SPD.Status not in (4)";
	//left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID WHERE TransportedDate!='' and DeliveredDate='' and    TransportedDate $DateFilter $Qry";


	$PendingOnWaterExec = $iconn->query($SqlOnWater);
	$OrdersOnWaterRow = $PendingOnWaterExec->fetch_array();
	$OnWater = $OrdersOnWaterRow['OnWater'];
	$OrderStatusRow['OnWater'] = $OnWater;


	//Delivered
	/*
					  $SqlDelivered="SELECT Count(*) as Delivered from ScrmPoData as SPD left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
					 left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID WHERE TransportedDate!='' and DeliveredDate !='' and    DeliveredDate $DateFilter $Qry";
					 */

	$SqlDelivered = "SELECT Count(*) as Delivered from ScrmPoData as SPD left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
	left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID where SCI.StockOutDate !='' and ISF.EmptyReturn!='' 
	and    SCI.CInvDate $DateFilter $Qry and SPD.Status not in (4)";

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


	//Pending Contracts
	//  $SqlPendingContracts="SELECT Count(*) as PendingContracts from ScrmPoData as SPD WHERE PoID NOT in 
	// 			(SELECT PiID FROM `ScrmCustomInvoices` WHERE StockOutDate!='') $Qry and SPD.Status not in (2,3,4)";

	$SqlPendingContracts = "SELECT Count(*) as PendingContracts from ScrmPoData as SPD 
	left join ScrmCustomInvoices as SCI on SPD.PoID=SCI.PiID
	WHERE ((SCI.CInvID ='' OR SCI.CInvID IS NULL) OR (SCI.StockOutDate='' OR SCI.StockOutDate IS NULL)) $Qry and SPD.Status not in (2,3,4)";
	$PendingContractsExec = $iconn->query($SqlPendingContracts);
	$PendingContractsRow = $PendingContractsExec->fetch_array();


	// Pending Payments
	// $SqlPendingPayments = "SELECT Count(*) as PendingPayments from ScrmPoData as SPD 
	// 						left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
	// 						WHERE StockOutDate!='' and  PoID NOT in 
	// 						(SELECT PIID from ScrmPaymentCollections where Status=1) and CInvDate $DateFilter $Qry";

	$SqlPendingPayments = "SELECT Count(*) as PendingPayments from ScrmPoData as SPD 
							left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
							WHERE StockOutDate!='' and  PoID NOT in 
							(SELECT PoID from ScrmRealizationDetails where RezStatus=1) and CInvDate $DateFilter $Qry and SPD.Status not in (2,3,4)";




	$PendingPaymentsExec = $iconn->query($SqlPendingPayments);
	$PendingPaymentsRow = $PendingPaymentsExec->fetch_array();


	//Collection Recieved
	// $SqlCollectionRecieved = "SELECT Count(*) as CollectionRecieved
	// 				from ScrmPoData as SPD left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
	// 						WHERE StockOutDate!='' and  PoID  in 
	// 						(SELECT PIID from ScrmPaymentCollections where Status=1 and Type=1)  and CInvDate $DateFilter $Qry";

	$SqlCollectionRecieved = "SELECT Count(*) as PaymentRecieved
					from ScrmPoData as SPD left join ScrmCustomInvoices as SCI On SPD.PoID=SCI.PiID
							WHERE StockOutDate!='' and  PoID  in 
							(SELECT PoID from ScrmRealizationDetails where RezStatus=1)  and CInvDate $DateFilter $Qry
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
 WHERE Month(TransportedDate)=MN.Month and TransportedDate $DateFilter  $Qry  AND SPD.PoID != '' AND SPD.Status NOT IN (4)) as Shipments,
(SELECT COUNT(*) from ScrmCustomInvoices  as SDB left join ScrmPoData as SPD on SPD.PoID=SDB.PiID  
 WHERE Month(CInvDate)=MN.Month and CInvDate $DateFilter  $Qry AND SPD.Status NOT IN (4)) as Orders
from ScrmMonthNames as MN  order by SortOrder asc";


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
(SELECT PiID FROM `ScrmCustomInvoices` WHERE CInvDate $DateFilter and StockOutDate !='' $Qry ) GROUP by SPD.DestinationCountry
ORDER by Orders desc";


	$SalesExec = $iconn->query($SqlSales);



	while ($RowPorts = $SalesExec->fetch_array()) {
		$RowPorts['Color'] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
		;
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
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter;

	$Qry = getFilterQry();

	//Exported Pos 
	$SqlExported = "SELECT COALESCE(GROUP_CONCAT(PoID),0)  as ExportPoIds from ScrmPoData as SPD left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
 left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID where SCI.StockOutDate !='' and    SCI.CInvDate $DateFilter $Qry";
	$SqlExportedExec = $iconn->query($SqlExported);
	$SqlExportedRow = $SqlExportedExec->fetch_array();

	$ExportPoIds = $SqlExportedRow['ExportPoIds'];


	$SqlPendingContracts = "SELECT COALESCE(GROUP_CONCAT(PoID),0)  as  ShipPoIds from ScrmPoData as SPD WHERE PoID NOT in 
(SELECT PiID FROM `ScrmCustomInvoices` where StockOutDate!='') $Qry and SPD.Status not in (2,3,4)";
	$PendingContractsExec = $iconn->query($SqlPendingContracts);
	$PendingContractsRow = $PendingContractsExec->fetch_array();


	$ShipPoIds = $PendingContractsRow['ShipPoIds'];



	$SqlShipments = "SELECT Grade, 
	 (SELECT COALESCE(sum(Mcs*TotalSlabs*SlabWeight*Mfactor),0) from ScrmPoGradeData as PGD1
					left join AdminPackingStyles as APS on APS.PCKID=PGD1.PackingStyleID 
                    WHERE InvId in ($ShipPoIds) and PGD1.Grade=PGD.Grade ) as Shipped,
      (SELECT COALESCE(sum(Mcs*TotalSlabs*SlabWeight*Mfactor),0) from ScrmPoGradeData as PGD2
					left join AdminPackingStyles as APS on APS.PCKID=PGD2.PackingStyleID 
                    WHERE InvId in ($ExportPoIds) and PGD2.Grade=PGD.Grade ) as Export
                   from ScrmPoGradeData as PGD
                    group by Grade
					ORDER by Abs(Grade)";


	$ShipmentsExec = $iconn->query($SqlShipments);


	while ($ShipmentsRow = $ShipmentsExec->fetch_array()) {

		if ($ShipmentsRow['Export'] != 0 && $ShipmentsRow['Shipped'] != 0) {
			$ExportKgs[] = round($ShipmentsRow['Export'] / 1000, 3);
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

function getPortGraph()
{
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter;

	$Qry = getFilterQry();
	// $SqlPorts = "SELECT COALESCE(SC.name,'Na') as CountryName,
// Max(SPD.PoDate) as LastShipment, PortId,
// COALESCE(SPC.Name,'Na') as PortName,COUNT(*) as Total FROM 
// `ScrmShipmentDetailsBreif` as  SSD LEFT join
// ScrmPoData  as SPD on SPD.PoID=SSD.PiID
// left join ScrmPortCities  as SPC on SPD.PortOfDischarge=SPC.PortId
// left join ScrmCountriesList  as SC on SC.id=SPC.Country
// WHERE SSD.TransportedDate!=''  $Qry 
//  GROUP by PortId ORDER by Total DESC";  


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
and (SIF.EmptyReturn is null or  SIF.EmptyReturn='')	and SPD.Status not in (2,3,4)     
 GROUP by PortId ORDER by Total DESC";
	//and SSD.DeliveredDate=''
	//$StmtPorts=$iconn->query($SqlPorts);  //


	$PortsExec = $iconn->query($SqlPorts);


	while ($RowPorts = $PortsExec->fetch_array()) {

		$PortID = $RowPorts['PortId'];

		$RowPorts['Color'] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
		$PortSeries[] = $RowPorts['Total'] + 0;
		$PortLables[] = $RowPorts['PortName'];
		$PortColors[] = $RowPorts['Color'];
		$Cjs['x'] = $RowPorts['PortName'];
		$Cjs['y'] = round($RowPorts['Total'] + 0);
		$Cjs['PortID'] = $RowPorts['PortId'];
		$Cjs['PortID'] = $PortID;

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
(SELECT PiID FROM `ScrmCustomInvoices` where StockOutDate!='') $Qry and SPD.Status not in (2,3,4) GROUP by SPD.ProcessorID ";



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
	global $iconn, $ExpID, $Type, $FinClsYear, $DateFilter;

	$SqlShiftDetails = "SELECT COUNT(*) Total,COALESCE(Name,'Other') as Name ,
	Status,COALESCE(Color,'#ff3d60')as Color from ScrmPoData as SPD 
	LEFT join ScrmShipmentStatusCodes as SSC On SPD.Status=SSC.Code 
	WHERE SPD.PoDate $DateFilter
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
