<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_GS_CHEM_PO_ORDERS;

	if ($methodType == "get") {
		$bodyContentGet = array(
			"ChemPoID" => trim($_POST['chemPoID']),
			"PlantID" => trim($_POST['plantID']),
			"ChemicalID" => trim($_POST['ChemicalID']),
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} else if ($methodType == "delete") {

		$bodyContentPost = array(
			"ReceivedID" => trim($_POST["receivedID"]),
			"ChemPoID" => trim($_POST["chemPoID"]),
			"PlantID" => trim($_POST["plantID"]),
			"ChemicalID" => trim($_POST['ChemicalID']),
		);
		$result = callAPI($api, "DELETE", "", $bodyContentPost);
		$msg = $result->Msg;
		echo "<script>alert('$msg');</script>";
		exit;
	} else if ($methodType == "post") {

		$ReceivedID = trim($_POST["receivedID"]);
		$PoNo = trim($_POST["poNo"]);
		$ChemName = trim($_POST["chemName"]);
		$BlNo = trim($_POST["blNo"]);
		$CtrNo = trim($_POST["ctrNo"]);
		$Qty = trim($_POST["qty"]);
		$Bags = trim($_POST["bags"]);
		$BlEntryNo = trim($_POST["blEntryNo"]);
		$BlDate = trim($_POST["blDate"]);
		$ClrdDate = trim($_POST["clrdDate"]);
		$PrPlnt = trim($_POST["prPlnt"]);

		$bodyContentPost = array(
			"ReceivedID" => $ReceivedID,
			"ChemPoID" => $PoNo,
			"ChemicalID" => $ChemName,
			"BLNo" => $BlNo,
			"ContainerNo" => $CtrNo,
			"QuantityReceived" => $Qty,
			"ReceivedBags" => $Bags,
			"BillOfEntryNo" => $BlEntryNo,
			"BillDate" => $BlDate,
			"ClearedDate" => $ClrdDate,
			"ProcessingPlantID" => $PrPlnt,
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
		$msg = $result->Msg;
		echo "<script>alert('$msg');</script>";
		exit;
	}
	if (isset($result) && !empty($result)) {

		$error = $result->Error;
		$msg = $result->Msg;

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
								<th>PoNo</th>
								<th>Chemical</th>
								<th>BL No</th>
								<th>Container No</th>
								<th>Received Bags</th>
								<th>Quantity Received</th>
								<th>Bill of Entry No</th>
								<th>Bill of Entry Date</th>
								<th>Cleared Date</th>
								<th>Processing Plant</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($List as $item) { ?>
								<tr>
									<td><?php echo $item->SNO ?></td>
									<td><?php echo $item->ChemPoNo ?></td>
									<td><?php echo $item->ChemicalName ?></td>
									<td><?php echo $item->BLNo ?></td>
									<td><?php echo $item->ContainerNo ?></td>
									<td><?php echo $item->ReceivedBags ?></td>
									<td><?php echo $item->QuantityReceived ?></td>
									<td><?php echo $item->BillOfEntryNo ?></td>
									<td><?php echo date('d-m-Y', strtotime($item->BillDate)) ?></td>
									<td><?php echo date('d-m-Y', strtotime($item->ClearedDate)) ?></td>
									<td><?php echo $item->StationName ?></td>
									<td class="text-center">
										<?php if ($result->DeleteAccess) { ?>
											<?php // if ($item->DeleteRow == 'Yes') { ?>
												<button class="btn btn-link text-danger btnActions btnDelete"
													style="position: relative;top: -3px" data-id="<?php echo $item->ReceivedID ?>"
													data-toggle="tooltip" data-placement="top" title="Delete" value=""
													data-original-title="Delete">
													<i class="mdi mdi-trash-can font-size-16"></i></button>
											<?php // } ?>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
						<tfoot>

							<tr>
								<td></td>
								<td></td>
								<td>Totals</td>
								<td></td>
								<td></td>
								<td class="tdPoNo" data-balance="<?php echo $Totals->BalanceBags ?>"><?php echo $Totals->TotalReceivedBags ?></td>
								<td><?php echo $Totals->TotalReceivedQty ?></td>
								<td><?php echo $Totals->BalanceBags ?></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>

						</tfoot>
					</table>
				</div>
			</div>
<?php
		} else {
			echo "<script>alert('$msg');</script>";
		}
	}
}

?>