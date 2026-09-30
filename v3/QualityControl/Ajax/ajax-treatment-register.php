<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
if (!empty($_POST['submit'])) {
	$api = API_QC_TREATMENT_REGISTER_POST;
	unset($_POST['submit']);
	$bodyContentPost = $_POST;
	$result = callAPI($api, "POST", "", $bodyContentPost);

	// echo json_encode($result, JSON_PRETTY_PRINT);
	// exit;

	header('Content-Type: application/json');
	echo json_encode($result);
	die;
} elseif (!empty($_POST['methodType']) && $_POST['methodType'] == 'GetBatchNumbers') {
	$api = API_QC_GET_UNIQUE_BATCH_IDS;
	$bodyContentPost = $_POST;
	$result = callAPI($api, "POST", "", $bodyContentPost);
	header('Content-Type: application/json');
	echo json_encode($result);
	die;
} elseif (!empty($_POST['methodType']) && $_POST['methodType'] == 'DeleteTreatmentReg') {
	$api = API_DELETE_TREATMENT_REG_BY_ID;
	$bodyContentPost = $_POST;
	$result = callAPI($api, "POST", "", $bodyContentPost);
	header('Content-Type: application/json');
	echo json_encode($result);
	die;
} elseif (!empty($_POST['methodType']) && $_POST['methodType'] == 'getVaraityByLotId') {
	$api = API_QC_TREATMENT_REGISTER_VERAITY_LIST;
	$bodyContentPost = $_POST;
	$result = callAPI($api, "POST", "", $bodyContentPost);
	header('Content-Type: application/json');
	echo json_encode($result);
	die;
}  elseif (!empty($_POST['methodType']) && $_POST['methodType'] == 'getGradesByLotId') {
	$api = API_QC_TREATMENT_REGISTER_GRADE_LIST;
	$bodyContentPost = $_POST;
	$result = callAPI($api, "POST", "", $bodyContentPost);
	header('Content-Type: application/json');
	echo json_encode($result);
	die;
} else {
	$PageNo = !empty($_POST["pageNo"]) ? trim($_POST["pageNo"]) : 1;
	$api = API_QC_TREATMENT_REGISTER_LIST;
	$bodyContentPost = $_POST;
	$results = callAPI($api, "POST", "", $bodyContentPost);
	//echo '<pre>';print_r($results);die;
	if (!empty($results->Data)) {
		$resList = $results->Data;
		$error = $resList->Error;
		if (!isset($error) || empty($error)) {
			$PageCount = $resList->PageCount;
			$resTotal = $resList->TotalCount;
			$List = $resList->Data;
?>
			<table id="tblDataTable" class="table table-hover table-bordered">
				<thead class="thead-light">
					<tr>
						<th style="width: 80px">S.No</th>
						<!-- <th>Arrival Date</th> -->
						<th>Soaking Date</th>
						<th>Batch No.</th>
						<th>VarietyName</th>
						<th>Grade</th>
						<th>Chlorine dioxide (ML)</th>
						<th>InTemperature</th>
						<th>OutTemperature</th>
						<th>InspectedBy</th>
						<th>VerifiedBy</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>

					<?php foreach ($List as $item) { ?>
						<tr>
							<td><?php echo $item->SNO; ?></td>
							<td><?php echo date("d-m-Y", strtotime($item->ArrivalDate)); ?></td>
							<!-- <td><?php echo empty($item->ReglazeBatchNo)?$item->SoakingDate:$item->ArrivalDate; ?></td> -->
							<td><?php echo !empty($item->BatchNoType) && $item->BatchNoType=='Arrival'?$item->BatchNoType.'-'.$item->ArrivalNumber:$item->BatchNoType.'-'.$item->ReglazeBatchNo; ?></td>
							<td><?php echo $item->VarietyName;?>
								<?php echo !empty($item->TubNumber) ? '/'.$item->TubNumber : ''; ?></td>
							<td><?php echo $item->Grade; ?></td>
							<td><?php echo !empty($item->ChlorineDioxide)?$item->ChlorineDioxide.' ML':''; ?></td>
							<td><?php echo $item->InTemperature; ?></td>
							<td><?php echo $item->OutTemperature; ?></td>
							<td><?php echo $item->InspectedBy; ?></td>
							<td><?php echo $item->VerifiedBy; ?></td>
							
							<td>
								<a href="javascript:void(0);"
									data-treatment_id="<?php echo $item->TreatmentId; ?>"
									data-arrival_date="<?php echo $item->ArrivalDate; ?>"
									data-batch_ids="<?php echo $item->BatchId; ?>"
									data-chemical_id="<?php echo $item->ChemicalId; ?>"
									data-in_temperature="<?php echo $item->InTemperature; ?>"
									data-out_temperature="<?php echo $item->OutTemperature; ?>"
									data-inspected_by="<?php echo $item->InspectedBy; ?>"
									data-verified_by="<?php echo $item->VerifiedBy; ?>"
									
									data-chlorine_dioxide="<?php echo $item->ChlorineDioxide; ?>"
									data-remarks="<?php echo $item->Remarks; ?>"
									data-salt_batch_number="<?php echo $item->SaltBatchNumber; ?>"
									data-phosphate_batch_number="<?php echo $item->PhosphateBatchNumber; ?>"
									data-non_phosphate_batch_number="<?php echo $item->NonPhosphateBatchNumber; ?>"
									data-shift="<?php echo $item->Shift; ?>"
									data-grade="<?php echo $item->Grade; ?>"
									data-variety="<?php echo $item->Variety; ?>"
									data-reglaze_batchno="<?php echo $item->ReglazeBatchNo; ?>"
									data-batch_no_type="<?php echo $item->BatchNoType; ?>"
									data-in_qty="<?php echo $item->SoakInQuantity; ?>"
									data-tub_number="<?php echo $item->TubNumber; ?>"
									class="edit"><i class="mdi mdi-pencil font-size-16"></i></a>
								<a href="javascript:void(0);" data-treatment_id="<?php echo $item->TreatmentId; ?>" class="delete text-danger"><i class="mdi mdi-delete font-size-16"></i></a>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<div>
				<input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
				<ul class="pagination pagination-rounded  <?php echo $PageCount<30?'justify-content-center':'';?>" style="overflow-x: auto;margin: 0px auto;">
					<?php
					for ($i = 1; $i <= $PageCount; $i++) {
						$pageActive = ($PageNo == $i ? "active" : "");
						echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
					}
					?>
				</ul>
			</div>
<?php } else {
			echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
		}
	} else {
		echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : No Data Found</div>";
	}
}
