<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_IBT_REQUEST_BreakUp;

	if ($methodType == "get") {

		$IBTRequestID = trim($_POST['ibtID']);

		$bodyContentGet = array(
			"IBTRequestID" => $IBTRequestID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);

		// var_dump($ChemPoID);

	} else if ($methodType == "delete") {

		$IBTBreakupID = trim($_POST["ibtBreakUpID"]);

		$bodyContentPost = array(
			"IBTBreakupID" => $IBTBreakupID
		);
		$result = callAPI($api, "DELETE", "", $bodyContentPost);

	} else if ($methodType == "post") {

		$IBTBreakupID = trim($_POST["ibtBreakupID"]);
		$IBTRequestID = trim($_POST["ibtID"]);
		$Variety = trim($_POST["variety"]);
		$SUPC = trim($_POST["supc"]);
		$Stock = trim($_POST["stock"]);
		$PoID = trim($_POST["poID"]);
		$PackingType = trim($_POST["packingType"]);
		$Mcs = trim($_POST["mcs"]);
		$Bags = trim($_POST["bags"]);
		$Status = trim($_POST["status"]);

		$bodyContentPost = array(
			"IBTBreakupID" => $IBTBreakupID,
			"IBTRequestID" => $IBTRequestID,
			"VarietyID" => $Variety,
			"SUPC" => $SUPC,
			"StockID" => $Stock,
			"PoID" => $PoID,
			"PackingType" => $PackingType,
			"NoOfMcs" => $Mcs,
			"NoOfLooseBags" => $Bags,
			"Status" => $Status
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
	}
	// var_dump($result);
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
						<th>Variety</th>
						<th>SUPC</th>
						<th>Stock</th>
						<th>PoID</th>
						<th>Packing Type</th>
						<th>NoOf Mcs</th>
						<th>NoOf Loose Bags</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($List as $item) { ?>
						<tr>
							<td><?php echo $item->SNO ?></td>
							<td><?php echo $item->FullName ?></td>
							<td><?php echo $item->SUPC ?></td>
							<td><?php echo $item->AvalMcs ?></td>
							<td><?php echo $item->PoIDName ?></td>
							<td><?php echo $item->Name ?></td>
							<td><?php echo $item->NoOfMcs ?></td>
							<td><?php echo $item->NoOfLooseBags ?></td>
							<td>
                                <?php if ($item->Status == "1") { ?>
                                    <button class="btn btn-link badge badge-warning font-size-10" style="width:70px;background:#025009;color:white">
                                        Active
                                    </button>
                                <?php } else { ?>
                                    <button class="btn btn-link badge badge-danger font-size-10" style="width:70px;background:#e20000;color:white">
                                        In-Active
                                    </button>
                                <?php } ?>
                            </td>
							<td class="text-center">
								<?php if ($result->DeleteAccess) { ?>
									<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="<?php echo $item->IBTBreakupID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
										<i class="mdi mdi-trash-can font-size-16"></i></button>
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
							<td></td>
							<td></td>
							<td></td>
							<td><?php echo $total->TotalMcs ?></td>
							<td><?php echo $total->TotalLooseBags ?></td>
							<td></td>
							<td></td>
						</tr>
					<?php } ?>
				</tfoot>
			</table>
<?php
		} else {
			echo "<script>alert('$msg');</script>";
		}
	}
}

?>