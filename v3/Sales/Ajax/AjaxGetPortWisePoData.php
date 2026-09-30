<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");
$ClientID = $_SESSION['ClientID'];
$DisplayFDAfields = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;
$DisplayConsignee = isset($ClientID) && $ClientID === 'SMEV3' ? 'ConsigneeName' : 'ConsigneeCode';

$PortID = trim($_POST["PortID"]);
$Year = trim($_POST["Year"]);
$Exporter = trim($_POST["Exporter"]);
$Consignee = trim($_POST["Consignee"]);
$Country = trim($_POST["Country"]);
$Brand = trim($_POST["Brand"]);
$Month = trim($_POST["Month"]);
$Type = trim($_POST["Type"]);
$Fbdstatus = trim($_POST["Fbdstatus"]);


// echo $Type."<br>";
// echo $Year."<br>";
// echo $Exporter."<br>";
// echo $Processor."<br>";
// echo $Consignee."<br>";
// echo $Country."<br>";

$api = GetPortWisePoData_API;
$bodyContent = array(
	'Year' => $Year,
	'Exporter' => $Exporter,
	'Consignee' => $Consignee,
	'Country' => $Country,
	'Brand' => $Brand,
	'Month' => $Month,
	'PortID' => $PortID,
	'Type' => $Type,
	'Fbdstatus' => $Fbdstatus,
	'FromDate' => trim($_POST["FromDate"]),
	'ToDate' => trim($_POST["ToDate"])
);

$resGetPortWisePoDataAPI = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resGetPortWisePoDataAPI, JSON_PRETTY_PRINT);
// exit;

$resList = $resGetPortWisePoDataAPI->Data->Data;

?>
<table id="key-datatableFullscreen" data-paging="false" class="table  wrap w-100 table-bordered">
	<thead class="thead-light">
		<tr>
			<th style="min-width: 50px;" class="sticky sno">S No.</th>
			<th style="min-width: 60px;" class="sticky popOnwaterID">ID</th>
			<th style="min-width: 120px;" class="sticky popOnwaterInvNo">InvNo</th>
			<th style="min-width: 180px;" class="sticky popOnwaterBuyer">Buyer</th>
			<th style="min-width: 120px;" class="sticky popOnwaterPoNo">PoNo</th>
			<th style="min-width: 270px;">Product</th>
			<th style="min-width: 270px;">Brand</th>
			<th style="min-width: 120px;">Container No</th>
			<th style="min-width: 150px;">Final Destination</th>
			<!-- <th>Country</th> -->
			<!-- <th>ShipByDate</th> -->
			<th style="min-width: 120px;">On Board Date</th>
			<?php if ($DisplayFDAfields) { ?>
				<th style="min-width: 200px;">Mother Vessel Date</th>
			<?php } ?>
			<th style="min-width: 120px;">ETA FD</th>
			<th style="min-width: 120px;">Gate Out Date</th>
			<th style="min-width: 150px;">Entry Number</th>
			<th style="min-width: 150px;">Liner</th>
			<th style="min-width: 140px;">Delivery Terms</th>
			<th style="min-width: 110px;">Transit Days</th>
			<th style="min-width: 160px;">Transit Days per Liner</th>
			<th style="min-width: 160px;">Status</th>
			<!-- <th style="min-width: 110px;">Empty Return</th> -->
		</tr>
	</thead>
	<tbody>
		<?php // $i = 1; 
		foreach ($resList as $item) {
			?>
			<tr>
				<td class="sticky sno"><?php echo $item->Sno ?></td>
				<td class="sticky popOnwaterID"><?php echo $item->POID ?></td>
				<td class="sticky popOnwaterInvNo"><?php echo $item->CInvNo ?></td>
				<td class="sticky popOnwaterBuyer"><?php echo $item->{$DisplayConsignee} ?></td>
				<td class="sticky popOnwaterPoNo"><?php echo $item->PoNO ?></td>
				<td><?php echo $item->Product ?></td>
				<td><?php echo $item->Brand ?></td>
				<td><?php echo $item->ContainerNo ?></td>
				<td><?php echo $item->FinalDestination ?></td>
				<!-- <td><?php // echo $item->DestinationCountry  ?></td> -->
				<!-- <td><?php // echo $item->ShipByDate  ?></td> -->
				<td><?php echo $item->ShippedOn ?></td>
				<?php if ($DisplayFDAfields) { ?>
					<td><?php echo $item->DepartureDate ?></td>
				<?php } ?>
				<td><?php echo $item->EtaFD ?></td>
				<td><?php echo $item->GateOutDate ?></td>
				<td><?php echo $item->EntryNumber ?></td>
				<td><?php echo $item->Liner ?></td>
				<td><?php echo $item->DeliveryTerms ?></td>
				<td><?php echo $item->TransitDays ?></td>
				<td><?php echo $item->TransitDaysPerLiner ?></td>
				<td><?php echo $item->CurrentStatus ?></td>
				<!-- <td><?php // echo $item->EmptyReturnedDate  ?></td> -->
			</tr>
			<?php

		}
		?>
	</tbody>
</table>