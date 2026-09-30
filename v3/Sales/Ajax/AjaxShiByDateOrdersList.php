<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include($cp . "Includes/IncludePage.php");

$YEAR = $_POST['YEAR'];
$Month = $_POST['Month'];
if ($Month < 10) {
	$Month = '0' . $Month;
}
$DateStart = $YEAR . '-04-01';
$YearEnd = $YEAR + 1;
$DateEnd = $YearEnd . '-' . $Month . '-31';
$AccessType = $_SESSION["AccessType"];
$ShipByDate = $YEAR . '-' . $Month . '-31';

$EXPORTER = $_POST['EXPORTER'];
$TYPE = $_POST['TYPE'];
if ($YEAR == '') {
	$Query = '';
} else {
}

if ($EXPORTER != " ") {
	$Query = 'and POD.ExporterID=' . $EXPORTER;
} else {
	$Query = '';
}

if ($TYPE == 'POLIST') {


	$sql = "SELECT *,SSC.Name as PoStatus ,SPC.Name as Port ,
 COALESCE(DATEDIFF(POD.ShipByDate,now())-1,0) as DaysRemaining  FROM ScrmPoData as POD 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmPortCities as SPC on POD.PortOfDischarge	=SPC.PortId 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmShipmentDetailsBreif as SDB on POD.PoID=SDB.PiID
WHERE POD.Status=5 and (TransportedDate='' or TransportedDate is null) and ShipByDate <= '$ShipByDate' 
 $Query order by ShipByDate asc";
	//AvailableDate!='' and  only Recieved Conditions 23/12/2023 By Bharath #Teja
}


$stmt = $iconn->query($sql);


$data = ' 
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

      <tr>
	  <th>ID</th>
	  <th>Po No	</th>	
	  <th>Po Date</th>
	  <th>Production Start Date</th>
	  <th>Country</th>	
	  <th>Buyer</th>
	  <th>Port</th>
	  <th>Ship by date</th>
	  
	  <th>Status</th>
	  <th>Days Rem/Delayed</th>
	 
		</tr>

    </thead>
	<tbody>';



while ($row = $stmt->fetch_array()) {
	$DaysRemaining = $row['DaysRemaining'];
	$PoID = $row['PoID'];
	$SqlMcs = "SELECT SUM(Mcs) AS TotalMcs,SUM(CurrentMcs) AS FinishedMcs
 	FROM `ScrmPoGradeData`  WHERE InvId='$PoID' ";
	$stmtMcs = $iconn->query($SqlMcs);
	$RowMcs = $stmtMcs->fetch_array();


	$FinishedMcs = $RowMcs['FinishedMcs'];
	$TotalMcs = $RowMcs['TotalMcs'];
	$Percentage = round(($FinishedMcs / $TotalMcs) * 100, 2);
	$Percentage = $Percentage . '%';

	$ProductionStartedDate = $row['ProductionStartedDate'];

	if ($ProductionStartedDate == '01-01-1970') {
		$ProductionStartedDate = '*****';
	} else {
		$ProductionStartedDate = strtotime($row['ProductionStartedDate']);
		$ProductionStartedDate = date("d-m-Y", $ProductionStartedDate);
	}

	$PO_TIME_STAMP = strtotime($row['InvDate']);
	$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);

	$Todaydate = date('d-m-Y');

	$PODate = strtotime($row['PoDate']);
	$PO_Date = date("d-m-Y", $PODate);

	if ($PO_Date == '01-01-1970') {
		$PO_Date = '*****';
	}

	if ($INVOICE_DATE == '01-01-1970') {

		$INVOICE_DATE = '****';
	}

	$ConsigneeName = mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
	$PoNumber = mb_strimwidth($row['PoNo'], 0, 15, "...");
	$PoStatus = $row['PoStatus'];
	$CLR = 'yellow';


	$data .= ' <tr>
	<td id="' . $row['ID'] . '" > 
	 <a target="_blank" id="PoDetails" href="#" data-id="' . $row['PoID'] . '" data-value="' . $row['PoNo'] . '" class="fill-div">' . $row['PoID'] . ' </a>
	</td>
	<td id="' . $row['ID'] . '" > ' . $PoNumber . '</td>
	<td id="' . $row['ID'] . '" > ' . $PO_Date . '</td>
	<td id="' . $row['ID'] . '" > ' . getFormatedDateNA($row['ProductionStartedDate']) . '</td>
	<td id="' . $row['ID'] . '" > ' . $row['name'] . '</td>	
	<td id="' . $row['ID'] . '" > ' . $ConsigneeName . '</td>	
	
	<td id="' . $row['ID'] . '" > ' . $row['Port'] . '</td>
	<td id="' . $row['ID'] . '" > ' . getFormatedDate($row['ShipByDate']) . '</td>	
		
		';
	$data .= ' 
 	<td id="' . $row['ID'] . '" >	<div  class=" badge badge-warning font-size-10 "
	> ' . $Percentage . '
		 </div>	</td>
		 
		 <td>' . $DaysRemaining . '</td>
		
		</tr>';
}
echo $data .= '</tbody></table>';


//}
