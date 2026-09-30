<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_GS_CHEM_CONSUMPTION;

	if ($methodType == "get") {
		$bodyContentGet = array(
			"ChemicalID" => trim($_POST['chemName']),
			"PlantID" => trim($_POST['plantID']),
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} else if ($methodType == "delete") {

		$bodyContentPost = array(
			"EntryID" => trim($_POST["entryID"]),
			"ChemicalID" => trim($_POST['chemName']),
			"PlantID" => trim($_POST['plantID']),
		);
		$result = callAPI($api, "DELETE", "", $bodyContentPost);
		$msg = $result->Msg;
		echo "<script>alert('$msg');</script>";
		exit;
	} else if ($methodType == "post") {

		$EntryID = trim($_POST["entryID"]);
		$PoNo = trim($_POST["poNo"]);
		$ChemName = trim($_POST["chemName"]);
		$PlantName = trim($_POST["plantName"]);
		$Qty = trim($_POST["qty"]);
		$Bags = trim($_POST["bags"]);
		$Date = trim($_POST["date"]);
		$UsageType = trim($_POST["usageType"]);
		$SendToPlant = trim($_POST["sendToPlant"]);

		$bodyContentPost = array(
			"EntryID" => $EntryID,
			"ChemPoID" => $PoNo,
			"ChemicalID" => $ChemName,
			"PlantID" => $PlantName,
			"QuantityUsed" => $Qty,
			"BagsUsed" => $Bags,
			"EntryDate" => $Date,
			"UsageType" => $UsageType,
			"SendToPlantID" => $SendToPlant,
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);

		$msg = $result->Msg;
		echo "<script>alert('$msg');</script>";
		exit;
	}

	if (isset($result) && !empty($result)) {

		$error = $result->Error;

		if (!isset($error) || empty($error)) {
			$List = $result->Data;
			$Totals = $result->Total;
?>
			<div class="card">
				<div class="card-body">

					<table id="tblDataTable" class="table table-hover table-bordered">
						<thead class="thead-light">
							<tr>
								<th style="width: 25px;">S.NO</th>
								<th>Entry Date</th>
								<th>Chemical Name</th>
								<th>Plant Name</th>
								<th>Transfer Name</th>
								<th>Consumed Bags</th>
								<th>Consumed Quantity</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($List as $item) { ?>
								<tr>
									<td><?php echo $item->SNO ?></td>
									<td><?php echo date('d-m-Y', strtotime($item->EntryDate)) ?></td>
									<td><?php echo $item->ChemicalName ?></td>
									<td><?php echo $item->ProductionPlant ?></td>
									<td><?php echo $item->TransferPlant ?></td>
									<td><?php echo $item->BagsUsed ?></td>
									<td><?php echo $item->QuantityUsed ?></td>
									<td class="text-center">
										<?php if ($result->DeleteAccess) { ?>
											<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="<?php echo $item->EntryID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
												<i class="mdi mdi-trash-can font-size-16"></i></button>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
						<tfoot>
							<tr>
								<td></td>
								<td>Total</td>
								<td></td>
								<td></td>
								<td></td>
								<td><?php echo $Totals->TotalUsedBags ?></td>
								<td><?php echo $Totals->TotalUsedKGs ?></td>
								<td></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
<?php
		} else {
			echo "<script>alert('$error');</script>";
		}
	}
}

?>