<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$ClientID = $ClientConfigData->ClientID;  //sriram

if ($ClientID == 'SMEV3') {
	$Count = 17;
} else {
	$Count = 16;
}
$Type = trim($_POST["Type"]);
$Year = trim($_POST["YEAR"]);
// $Exporter = trim($_POST["Exporter"]);
$Exporter = trim($_POST["EXPORTER"]);
$Processor = trim($_POST["Processor"]);
$Consignee = trim($_POST["Buyer"]);
$Country = trim($_POST["Country"]);
$Brand = trim($_POST["Brand"]);
$AccessType = trim($_POST["AccessType"]);

// echo $Type."<br>";
// echo $Year."<br>";
// echo $Exporter."<br>";
// echo $Processor."<br>";
// echo $Consignee."<br>";
// echo $Country."<br>";

$api = GetPendingContracts_API;
$bodyContent = array(
	'Type' => $Type,
	'Year' => $Year,
	'Exporter' => $Exporter,
	'Processor' => $Processor,
	'Consignee' => $Consignee,
	'Brand' => $Brand,
	'Country' => $Country,
	'ClientID' => $ClientID
);

$resGetPendingContractsAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetPendingContractsAPI->Data->Data;
// echo json_decode($resList,JSON_PRETTY_PRINT);
// exit;
// var_dump($resList);

?>
<div id="buttons"></div>
<table id="key-datatableFullscreen" data-paging="false" class="table wrap w-150 table-bordered">
	<thead class="thead-light">
		<tr>
			<th class="sticky sno" style="min-width: 80px;">S No.</th>
			<th class="sticky buyer" style="min-width: 150px;">Buyer</th>
			<th class="sticky destination" style="min-width: 120px;">Destination</th>
			<th class="sticky shipment" style="min-width: 150px;">Shipment</th>
			<th class="sticky invoice" style="min-width: 150px;">Invoice No.</th>
			<th class="sticky pono" style="min-width: 100px;">P.O.NO.</th>
			<th class="podate" style="min-width: 100px;">P.O.Date</th>
			<th class="exp-proc" style="min-width: 100px;">Proc./Exp</th>
			<th class="brand" style="min-width: 150px;">Brand</th>
			<th class="variety" style="min-width: 150px;">Variety</th>
			<th class="form" style="min-width: 100px;">Form</th>
			<th class="packing" style="min-width: 100px;">Packing</th>
			<th class="glaze" style="min-width: 100px;">Glaze</th>
			<th class="certification" style="min-width: 120px;">Certification</th>
			<th class="g1" style="min-width: 100px;">6/8</th>
			<th class="g2" style="min-width: 100px;">8/12</th>
			<th class="g3" style="min-width: 100px;">13/15</th>
			<th class="g4" style="min-width: 100px;">16/20</th>
			<th class="g5" style="min-width: 100px;">21/25</th>
			<th class="g6" style="min-width: 100px;">26/30</th>
			<th class="g7" style="min-width: 100px;">31/35</th>
			<th class="g8" style="min-width: 100px;">31/40</th>
			<th class="g9" style="min-width: 100px;">41/50</th>
			<th class="g10" style="min-width: 100px;">51/60</th>
			<th class="g11" style="min-width: 100px;">61/70</th>
			<th class="g12" style="min-width: 100px;">71/90</th>
			<th class="g13" style="min-width: 100px;">91/110</th>
			<th class="g14" style="min-width: 100px;">100/150</th>
			<th class="g15" style="min-width: 100px;">150/200</th>
			<th class="g16" style="min-width: 100px;">200/250</th>
			<?php if ($ClientID == 'SMEV3') { ?>
				<th class="g17" style="min-width: 100px;">BKN</th><?php } ?>
			<th class="total" style="min-width: 100px;">Total</th>
			<?php if ($AccessType == 1) { ?>
				<th class="invoice-value" style="min-width: 150px;">Invoice Value</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($resList as $item) {
			$breakupList = is_array($item->BreakUpData) ? $item->BreakUpData : [];
			// If BreakUpData is empty, use one empty item to render the first row
			$hasBreakups = count($breakupList) > 0;
			$rowIndex = 0;
			if (!$hasBreakups) {
				$breakupList[] = null; // Push a dummy row
			}
			foreach ($breakupList as $itemBreak) { ?>
				<tr>

					<td class="sticky sno"><?php echo $item->Sno ?></td>
					<td class="sticky buyer"><?php echo $item->ConsigneeName ?></td>
					<td class="sticky destination"><?php echo $item->PortOfDischarge ?></td>
					<td class="sticky shipment"><?php echo $item->ShipByDate ?></td>
					<td class="sticky invoice"><?php echo $item->InvNumFull ?></td>
					<td class="sticky pono"><?php echo $item->PoNO ?></td>
					<td class="podate"><?php echo $item->PoDate ?></td>
					<td class="exp-proc"><?php echo $item->ExpProc ?></td>
					<td class="brand"><?php echo $itemBreak->Brand ?></td>


					<td class="variety">
						<?php echo $itemBreak->Species . '-' . $itemBreak->Variety . '-' . $itemBreak->TypeProduct ?>
					</td>
					<td class="form"><?php echo $itemBreak->Form ?></td>
					<td class="packing"><?php echo $itemBreak->Packing ?></td>
					<td class="glaze"><?php echo $itemBreak->Glaze ?></td>
					<td class="certification"><?php echo $item->Cerification ?></td>

					<?php for ($i = 1; $i <= $Count; $i++) {
						$gx = "G" . $i;
						$gVal = isset($itemBreak->$gx) ? explode("&", $itemBreak->$gx) : ["", ""];
						?>
						<td class="g<?= $i ?>">
							<?php
							$gValue = explode(")", $gVal[0]);
							$gValue = $gValue[1] ? $gValue[0] . ') ' . $gValue[1] : $gValue[0];
							echo $gValue; ?>
							<?php if (!empty($gVal[1])) { ?>
								<span class="breakUpPrice" style="display: none;"> (<?= $gVal[1] ?>)</span>
							<?php } ?>
						</td>
					<?php } ?>

					<td class="total"><?php echo $itemBreak->Total ?></td>

					<?php if ($AccessType == 1 && ($rowIndex == 0 || count($breakupList) == 1)) { ?>
						<td class="invoice-value"><?php echo $item->InvoiceValue ?></td>
					<?php } else if ($AccessType == 1) { ?>
							<td class="invoice-value"></td>
					<?php } ?>
				</tr>
				<?php $rowIndex++;
			}
		} ?>
		<!-- <tr> DATA: <?php echo json_encode($resList, JSON_PRETTY_PRINT) ?> </tr> -->
	</tbody>
</table>