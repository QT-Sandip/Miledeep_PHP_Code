<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class NotificationsController
{

	private $conn;
	// private $db_table = "ScrmClientInputFields";

	public $id;
	public $name;
	private $utilController;

	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
	}

	public function getNotificationsDetails($RequestBody)
	{

		$data['Error'] = null;
		$data['Msg'] = 'Success';
		$data['ShipmentsData'] = $this->getShipmentDetails(RequestBody);
		$data['PendingContractsData'] = $this->getPendingContractsDetails(RequestBody);
		$data['revisedOrdersData'] = $this->getRevisedOrdersDetails(RequestBody);
		$data['NegotiationData'] = $this->getNegotiationDetails(RequestBody);
		$data['PaymentsReceivedData'] = $this->getPaymentReceivedDetails(RequestBody);
		return $data;


	}

	function getShipmentDetails($RequestBody)
	{

		$SqlFields = 'SPD.PoID,SCD.ConsigneeName,SPD.PoNO,SCI.CustomsInvNo,SCI.CInvDate,SCI.StockOutDate';


		$StartDate = date('Y-m-d', strtotime("-2 days"));
		$EndDate = date('Y-m-d');

		// $SqlShipmentDetailsList = "SELECT  $SqlFields  FROM ScrmPoData as SPD 
		// 			left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
		// 			left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID 	                    				
		//             left join ScrmISFDetails as ISF on ISF.PIID=SPD.PoID				
		// 			 WHERE  ShippingOnboardDt <= CURDATE() -7 and SPD.Status not in (2,3,4) order by PoID desc";  

		$SqlShipmentDetailsList = "SELECT  $SqlFields  FROM ScrmCustomInvoices as SCI 
		left join  ScrmPoData as SPD on SCI.PiID=SPD.PoID 
		left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
		 WHERE  StockOutDate between '$StartDate' and '$EndDate' and SPD.Status not in (2,3,4)
		order by StockOutDate asc";
		//and SPD.PoID=992 		 

		$stmtShipmentDetailsList = $this->conn->prepare($SqlShipmentDetailsList);
		$stmtShipmentDetailsList->execute();
		// $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$i = 0;
		while ($dataRow = $stmtShipmentDetailsList->fetch(PDO::FETCH_ASSOC)) {
			$Product = $this->utilController->getVariety($dataRow['PoID']);
			$RowShipmentDetails['SNo'] = ++$i;
			$RowShipmentDetails['InvoiceNo'] = $dataRow['CustomsInvNo'];
			$RowShipmentDetails['ConsigneeName'] = $dataRow['ConsigneeName'];
			$RowShipmentDetails['PoNo'] = $dataRow['PoNO'];
			$RowShipmentDetails['Product'] = $Product;
			$RowShipmentDetails['StockOutDate'] = $this->utilController->getFormatedDatelines($dataRow['StockOutDate']);



			$AryRowShipmentDetails[] = $RowShipmentDetails;

		}
		// $TotalCount=$i++;
		// $ArrayTotals['ShipmentCount']=$TotalCount;
		// $AryRowShipmentDetails['TotalCount']=$TotalCount;
		//  echo	$i++;
		$Data['ShipmentCount'] = $i++;
		$Data['ShipmentDetails'] = $AryRowShipmentDetails;

		return $Data;

	}
	function getRevisedOrdersDetails($RequestBody)
	{
		//based on poforma invoicedate,display profrorma invoice date confirmed by surya

		$StartDate = date('Y-m-d', strtotime("-2 days"));
		$EndDate = date('Y-m-d');

		// Define the SQL fields to retrieve
		$SqlFields = 'SPA.poID, 
              COALESCE(SPA.number, "--") AS number,
              GROUP_CONCAT(CONCAT(
                  COALESCE(SPA.fieldName, "--"), ";", 
                  COALESCE(SPA.previousValue, "--"), ";", 
                  COALESCE(SPA.currentValue, "--"), ";", 
                  COALESCE(SPA.modifyDate, "--"), ";", 
                  COALESCE(SPG.Variety, "--"), ";", 
                  COALESCE(SPA.number, "--"), "#")) AS groupedData,
              SPD.InvNo, 
              COALESCE(SPD.PoID, "--") AS PoID, 
              COALESCE(SCD.ConsigneeName, "--") AS ConsigneeName, 
              COALESCE(SPD.PoNO, "--") AS PoNO, 
              COALESCE(SPD.InvNumFull, "--") AS InvNumFull';

		// Construct the SQL query
		$SqlRevisedOrdersList = "SELECT $SqlFields FROM ScrmPoAuditLog AS SPA 
								LEFT JOIN ScrmPoData AS SPD ON SPA.poId = SPD.PoID 
								LEFT JOIN ScrmPoGradeData AS SPG ON SPA.itemId = SPG.ID
								LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID 
								WHERE (SPA.fieldName = 'MCS' or SPA.fieldName = 'PoGradeId' or SPA.fieldName = 'PortOfDischarge' or SPA.fieldName = 'Description' or SPA.fieldName = 'Variety') AND SPA.modifyDate BETWEEN :startDate AND :endDate 
								GROUP BY SPD.InvNo";

		// Prepare the SQL statement
		$stmtRevisedOrdersList = $this->conn->prepare($SqlRevisedOrdersList);

		// Bind parameters to prevent SQL injection
		$stmtRevisedOrdersList->bindParam(':startDate', $StartDate);
		$stmtRevisedOrdersList->bindParam(':endDate', $EndDate);

		// Execute the statement
		$stmtRevisedOrdersList->execute();

		$i = 0; // Initialize counter
		$AryRowRevisedOrders = []; // Initialize array for revised orders

		// Fetch data and process it
		while ($dataRow = $stmtRevisedOrdersList->fetch(PDO::FETCH_ASSOC)) {
			$Product = $this->utilController->getVariety($dataRow['InvNo']);			
			$RowRevisedOrders = []; // Initialize array for each row
			$RowRevisedOrders['SNo'] = ++$i; // Set serial number
			$RowRevisedOrders['BreakUp'] = $this->createBreakUpData($dataRow['groupedData']);
			$RowRevisedOrders['InvoiceNo'] = $dataRow['InvNumFull'];
			$RowRevisedOrders['ConsigneeName'] = $dataRow['ConsigneeName'];
			$RowRevisedOrders['PoNo'] = $dataRow['PoNO'];
			$RowRevisedOrders['product'] = $Product;
			$RowRevisedOrders['name'] = $dataRow['name'];

			$AryRowRevisedOrders[] = $RowRevisedOrders; // Add the row to the main array
		}

		// Prepare final data
		$Data['revisedOrdersCount'] = $i; // Corrected to assign the count of revised orders
		$Data['revisedOrdersdetails'] = $AryRowRevisedOrders; // Assign details of revised orders

		// $Data = $AryRowPendingContracts;

		return $Data;


	}
	function getPendingContractsDetails($RequestBody)
	{ //based on poforma invoicedate,display profrorma invoice date confirmed by surya

		$StartDate = date('Y-m-d', strtotime("-2 days"));
		$EndDate = date('Y-m-d');

		$SqlFields = 'SPD.PoID,SCD.ConsigneeName,SPD.PoNO,SPD.InvNumFull,SPD.InvDate,SPD.ShipByDate';

		// $SqlPendingContractsList = "SELECT  $SqlFields  FROM ScrmPoData as SPD 
		// 			left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
		// 			left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID 	
		//             left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID				
		// 			 WHERE SDB.TransportedDate='' and SPD.PoID NOT in 
		// 		(SELECT PiID FROM `ScrmCustomInvoices`  where StockOutDate!='') and SPD.Status not in (2,3,4) 
		// 		and InvDate between '$StartDate' and '$EndDate' order by PoID desc";  		//and SPD.PoID=992 	

		// TransportedDate is nothing but stockoutdate
		$SqlPendingContractsList = "SELECT  $SqlFields  FROM ScrmPoData as SPD 
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 						
                    left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID				
					 WHERE SDB.TransportedDate='' and SPD.PoID NOT in 
				(SELECT PiID FROM `ScrmCustomInvoices`  where StockOutDate!='') and SPD.Status not in (2,3,4) 
				and SPD.PoDate between '$StartDate' and '$EndDate' order by PoDate asc";

		//InvDate	
		// PoID desc
		// left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID 

		$stmtPendingContractsList = $this->conn->prepare($SqlPendingContractsList);
		$stmtPendingContractsList->execute();
		// $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$i = 0;
		while ($dataRow = $stmtPendingContractsList->fetch(PDO::FETCH_ASSOC)) {
			$Product = $this->utilController->getVariety($dataRow['PoID']);
			$RowPendingContracts['SNo'] = ++$i;
			$RowPendingContracts['InvoiceNo'] = $dataRow['InvNumFull'];
			$RowPendingContracts['ConsigneeName'] = $dataRow['ConsigneeName'];
			$RowPendingContracts['PoNo'] = $dataRow['PoNO'];
			$RowPendingContracts['Product'] = $Product;
			$RowPendingContracts['ShipByDate'] = $this->utilController->getFormatedDatelines($dataRow['ShipByDate']);
			// $RowPendingContracts['InvoiceDate'] = $this->utilController->getFormatedDatelines($dataRow['InvDate']);

			$AryRowPendingContracts[] = $RowPendingContracts;

		}
		$Data['PendingContractsCount'] = $i++;
		$Data['PendingContractsDetails'] = $AryRowPendingContracts;

		// $Data = $AryRowPendingContracts;

		return $Data;

	}
	function getNegotiationDetails($RequestBody)
	{

		$SqlFields = "SPD.PoID,
       	SCD.ConsigneeName,				
		SPD.PoNO,
		SCI.CustomsInvNo,		
		SCI.CInvDate,CASE WHEN Type = 1 THEN 'Collection' WHEN Type = 2 THEN 'Negotiation' END AS PaymentType
		,(SELECT ColDate FROM `ScrmCollectionBreakUP` SCB where SCB.PiID=SPC.PIID order by ColBrkID desc limit 1) as CollectionorNegotiationDate ";

		//and SPD.PoID=776
		$StartDate = date('Y-m-d', strtotime("-2 days"));
		$EndDate = date('Y-m-d');

		$SqlNegotiationList = "SELECT  $SqlFields     
		FROM ScrmPaymentCollections as SPC left join ScrmCustomInvoices as SCI on SPC.PIID=SCI.PiID  
		left join ScrmPoData as SPD  on SCI.PiID=SPD.PoID 	
		left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
		left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID				
		 WHERE SDB.TransportedDate!='' and SPD.PoID  in 
	(SELECT PiID FROM `ScrmCollectionBreakUP`  where ColDate between '$StartDate' and '$EndDate') and SPD.Status not in (2,3,4) order by CustomsInvNo asc"; 		//and SPD.PoID=992 		 
		$stmtNegotiationList = $this->conn->prepare($SqlNegotiationList);
		$stmtNegotiationList->execute();
		// $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$i = 0;
		while ($dataRow = $stmtNegotiationList->fetch(PDO::FETCH_ASSOC)) {
			$RowNegotiation['SNo'] = ++$i;
			$RowNegotiation['InvoiceNo'] = $dataRow['CustomsInvNo'];
			$RowNegotiation['ConsigneeName'] = $dataRow['ConsigneeName'];
			$RowNegotiation['PoNo'] = $dataRow['PoNO'];
			$RowNegotiation['PaymentType'] = $dataRow['PaymentType'];
			$RowNegotiation['InvoiceDate'] = $this->utilController->getFormatedDatelines($dataRow['CInvDate']);
			$RowNegotiation['CollectionorNegotiationDate'] = $this->utilController->getFormatedDatelines($dataRow['CollectionorNegotiationDate']);



			$AryRowNegotiation[] = $RowNegotiation;

		}
		$Data['NegotiationCount'] = $i++;
		$Data['NegotiationDetails'] = $AryRowNegotiation;
		// $Data = $AryRowNegotiation;
		return $Data;

	}
	function getPaymentReceivedDetails($RequestBody)
	{
		$StartDate = date('Y-m-d', strtotime("-2 days"));
		$EndDate = date('Y-m-d');


		$SqlPoIds = "SELECT GROUP_CONCAT(MaxDates.PoID) as PoID FROM 
( SELECT CASE WHEN RezDate1 >= RezDate2 AND RezDate1 >= RezDate3 THEN RezDate1 WHEN RezDate2 >= RezDate1 
AND RezDate2 >= RezDate3 THEN RezDate2 ELSE RezDate3 END AS MaxDateColumn ,RezStatus,Type,PoID FROM ScrmRealizationDetails ) AS MaxDates
 WHERE MaxDates.MaxDateColumn BETWEEN '$StartDate ' AND '$EndDate' and MaxDates.RezStatus=1 and MaxDates.Type=2 
 group by MaxDates.MaxDateColumn";

		$stmtPoIds = $this->conn->prepare($SqlPoIds);
		$stmtPoIds->execute();
		$dataRowPoIds = $stmtPoIds->fetch(PDO::FETCH_ASSOC);
		$PoIds = $dataRowPoIds['PoID'];
		$Query = '';

		if ($PoIds != '') {
			$Query .= " 
	 or SPD.PoID  in ($PoIds)";

		}

		$SqlFields = "
		SPD.PoID,
       	SCD.ConsigneeName,				
		SPD.PoNO,
		SCI.CustomsInvNo,
		SPD.PoDate,
		SPD.InvDate,		
		SCI.CInvDate,CASE WHEN Type = 1 THEN 'Collection' WHEN Type = 2 THEN 'Negotiation' END AS PaymentType
		,(SELECT ColDate FROM `ScrmCollectionBreakUP` SCB where SCB.PiID=SRD.PoID order by ColBrkID desc limit 1) as CollectionorNegotiationDate
		";
		// 		echo $SqlPaymentReceivedList = "SELECT  $SqlFields FROM ScrmRealizationDetails as SRD
// 		left join ScrmCustomInvoices as SCI on SRD.PoID=SCI.PiID 
// 		left join ScrmPoData as SPD  on SCI.PiID=SPD.PoID 	 
// 		left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
// 		left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID				
// 		WHERE SDB.TransportedDate!='' and ( SPD.PoID  in (
// 	(SELECT PiID FROM `ScrmCollectionBreakUP`  where ColDate between '$StartDate' and '$EndDate'  and SRD.Type=1 ) or ($PoIds))
// 	 and SPD.Status not in (2,3,4)  and RezStatus=1 order by CustomsInvNo asc"; 		//and SPD.PoID=992 		
// exit;

		$SqlPaymentReceivedList = "SELECT  $SqlFields FROM ScrmRealizationDetails as SRD
		left join ScrmCustomInvoices as SCI on SRD.PoID=SCI.PiID 
		left join ScrmPoData as SPD  on SCI.PiID=SPD.PoID 	 
		left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
		left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID				
		WHERE SDB.TransportedDate!='' and SPD.Status not in (2,3,4) and  SPD.PoID  in 
	(SELECT PiID FROM `ScrmCollectionBreakUP`  where ColDate between '$StartDate' and '$EndDate'  and SRD.Type=1 and RezStatus=1) $Query
	    order by CustomsInvNo asc"; 		//and SPD.PoID=992 	




		$stmtPaymentReceivedList = $this->conn->prepare($SqlPaymentReceivedList);
		$stmtPaymentReceivedList->execute();
		$i = 0;
		while ($dataRow = $stmtPaymentReceivedList->fetch(PDO::FETCH_ASSOC)) {
			$RowPaymentReceived['SNo'] = ++$i;
			$RowPaymentReceived['InvoiceNo'] = $dataRow['CustomsInvNo'];
			$RowPaymentReceived['ConsigneeName'] = $dataRow['ConsigneeName'];
			$RowPaymentReceived['PoNo'] = $dataRow['PoNO'];
			$RowPaymentReceived['PaymentType'] = $dataRow['PaymentType'];
			$RowPaymentReceived['InvoiceDate'] = $this->utilController->getFormatedDatelines($dataRow['InvDate']);
			$RowPaymentReceived['CollectionorNegotiationDate'] = $this->utilController->getFormatedDatelines($dataRow['CollectionorNegotiationDate']);


			$AryPaymentReceivedList[] = $RowPaymentReceived;

		}

		$Data['PaymentReceivedCount'] = $i++;
		$Data['PaymentReceivedDetails'] = $AryPaymentReceivedList;
		// $Data = $AryRowPendingContracts;
		return $Data;

	}

	function createBreakUpData($data)
	{

		// Break up the groupedData into individual components
		$dataBreakUp = explode("#", str_replace("#,", "#", $data));
		$breakUpAry = []; // Initialize array for breakdown

		foreach ($dataBreakUp as $item) {
			if (strlen($item) > 5) {
				$breakUp = [];
				$breakUp['fieldName'] = explode(";", $item)[0];
				$breakUp['previousValue'] = explode(";", $item)[1];
				$breakUp['currentValue'] = explode(";", $item)[2];

				$Countryname ="";
				if( $breakUp['fieldName'] == "PortOfDischarge" || $breakUp['fieldName'] == "FinalDestination" ){
					$Countrydata = $this->utilController->getPortDetails($breakUp['currentValue']);
					if(isset($Countrydata['PortName']) && isset($Countrydata['PortName'])){
						$Countryname =$Countrydata['PortName'];
					}
				}	
				$GetVariety="";$SqlGetVariety ="";
				if($breakUp['fieldName'] == "VarietyID"){					
					
					$SqlGetVariety = "SELECT GROUP_CONCAT(Grade,'-',ProductDescription) as Variety FROM `ScrmPIVarietyList` WHERE VarietyID='".$breakUp['currentValue']."'";
						$stmtGetVariety = $this->conn->prepare($SqlGetVariety);
		        $stmtGetVariety->execute();
		        $rowGetVariety = $stmtGetVariety->fetch(PDO::FETCH_ASSOC);

		        $GetVariety = $rowGetVariety['Variety'];
		        $GetVariety = str_replace(',', ',<br>', $GetVariety);
				}
				$breakUp['modifyDate'] = $this->utilController->getFormatedDatelines(explode(";", $item)[3]);
				$breakUp['Variety'] = explode(";", $item)[4];
				$breakUp['Product'] = $GetVariety;
				$breakUp['number'] = explode(";", $item)[5];
				$breakUp['Countryname'] = $Countryname;
				$breakUpAry[] = $breakUp; // Add to the breakdown array
			}
		}
		return $breakUpAry;
	}

	// function getShipmentBreakup($PoID){


	// }
}

