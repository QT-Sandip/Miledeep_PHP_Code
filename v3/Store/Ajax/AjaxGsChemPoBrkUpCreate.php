<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_GS_CHEM_PO_BrkUP;

	if ($methodType == "get") {

		$ChemPoID = trim($_POST['ChemPoID']);

		$bodyContentGet = array(
			"ChemPoID" => $ChemPoID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);

		// var_dump($ChemPoID);

	} else if ($methodType == "delete") {

		$ChemPoBreakUpID = trim($_POST["breakUpID"]);

		$bodyContentPost = array(
			"ChemPoBreakUpID" => $ChemPoBreakUpID
		);
		$result = callAPI($api, "DELETE", "", $bodyContentPost);
	} else if ($methodType == "post") {

		$BreakUpID = trim($_POST["breakUpID"]);
		$ChemPoID = trim($_POST["ChemPoID"]);
		$Chemical = trim($_POST["chemical"]);
		$Bags = trim($_POST["bags"]);
		$Quantity = trim($_POST["quantity"]);
		$CurrencyType = trim($_POST["currency"]);
		$Rate = trim($_POST["rate"]);

		$bodyContentPost = array(
			"ChemPoBreakUpID" => $BreakUpID,
			"ChemPoID" => $ChemPoID,
			"ChemicalID" => $Chemical,
			"QuantityInKgs" => $Quantity,
			"QuantityInBags" => $Bags,
			"CurrencyType" => $CurrencyType,
			"RatePerKgInRupees" => $Rate
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
	}
	// echo json_encode($result);
	if (isset($result) && !empty($result)) {

		$error = $result->Error;

		if (!isset($error) || empty($error)) {
			$List = $result->Data;
			$Totals = $result->Total;
?>

			<table id="tblDataTable" class="table table-hover table-bordered">
				<thead class="thead-light">
					<tr>
						<th style="width: 25px;">S.NO</th>
						<th>Chemical</th>
						<th>Noof Bags</th>
						<th>Quantity</th>
						<th>Rate</th>
						<th>Currency Type</th>
						<th>Amount</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($List as $item) { ?>
						<tr>
							<td><?php echo $item->SNO ?></td>
							<td><?php echo $item->ChemicalName ?></td>
							<td><?php echo $item->QuantityInBags ?></td>
							<td><?php echo $item->QuantityInKgs ?></td>
							<td><?php echo $item->RatePerKgInRupees ?></td>
							<td><?php echo $item->CurrencyName ?></td>
							<td><?php echo $item->Amout ?></td>
							<td class="text-center">
								<?php if ($result->DeleteAccess) { ?>
									<?php // if ($item->DeleteRow == 'Yes') { ?>
										<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="<?php echo $item->ChemPoBreakUpID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
											<i class="mdi mdi-trash-can font-size-16"></i></button>
									<?php // } ?>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
				<tfoot>
					<?php foreach ($Totals as $total) { ?>
						<tr>
							<td></td>
							<td>Totals</td>
							<td></td>
							<td><?php echo $total->TotalQty ?></td>
							<td></td>
							<td></td>
							<td><?php echo $total->TotalAmount ?></td>
							<td></td>
						</tr>
					<?php } ?>
				</tfoot>
			</table>
<?php
		} else {
			// echo "<script>alert('$msg');</script>";
		}
	}
}

?>