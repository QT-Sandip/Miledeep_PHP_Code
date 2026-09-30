<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_HON_PACKING;

	if ($methodType == "getData") {

		$bodyContentPost = array(
			"ArrivalID" => trim($_POST["arrivalID"]),
		);

		$result = CallAPI($api, "GET", "", $bodyContentPost);
	} else if ($methodType == "post") {

		$bodyContentPost = array(
			"ArrivalDate" => trim($_POST["arrivalDate"]),
			"ArrivalID" => trim($_POST["arrivalID"]),
			"HonID" => trim($_POST["honID"]),
			"PlantID" => trim($_POST["productionPlant"]),
			"StationID" => trim($_POST["center"]),
			"GradingDate" => trim($_POST["transactionDate"]),
			"HonCount" => trim($_POST["arrivalCount"]),
			"HonWeight" => trim($_POST["arrivalWeight"]),
			"Remarks" => trim($_POST["remarks"]),
		);

		$result = callAPI($api, "POST", "", $bodyContentPost);
		// echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
		// exit();

	} else if ($methodType == "delete") {

		$bodyContent = array(
			"ArrivalID" => trim($_POST["arrivalID"]),
			"HonID" => trim($_POST["breakUpID"]),
		);

		$result = CallAPI($api, "DELETE", "", $bodyContent);
		// echo json_encode($result, JSON_PRETTY_PRINT);
		// exit;
	}
	$error = $result->Error;
	$msg = $result->Msg;
	$List = $result->Data;

	if (!isset($error) || empty($error)) {
		$htmlResponse = '
              <table id="tblDataTable" class="table table-hover table-bordered">
                 <thead class="thead-light">
                    <tr>
                      <th style="width:50px;">S.No</th>
                      <th>Grading Date</th>
                      <th>Arrival Code </th>
                      <th>Production Plant</th>
                      <th>Center</th>
                      <th>HON Count</th>
                      <th>HON Weight</th>
                      <td>Remarks</td>
                      <th style="width: 60px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>';
		$i = 1;
		foreach ($List as $item) {
			$htmlResponse .= '
                      <tr>
                        <td>' . $i . '</td>
                        <td>' . $item->GradingDate . '</td>
                        <td>' . $item->ArrivalNumber . '</td>
                        <td>' . $item->FullName . '</td>
                        <td>' . $item->UnitName . '</td>
                        <td>' . $item->HonCount . '</td>
                        <td>' . $item->HonWeight . '</td>
                        <td>' . $item->Remarks . '</td>
                        <td>
                          <button type="button" id="btnDelete" name="btnDelete" data-arrivalid="' . $item->LotID . '" data-arrivalbrkupid="' . $item->HonID . '" class="btn btn-link p-0 deleteBrkUp">
                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                          </button>
                        </td>
                      </tr>';
			$i++;
		}
		$htmlResponse .= '
                  </tbody>
                    <tfoot>
                      <tr >
                        <td></td>
                        <td>Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>' . $result->Totals->TotalWeight . '</td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tfoot>
                </table>';
	}

	$response = array('html' => $htmlResponse, "FormData" => $List, "Error" => $error, "Msg" => $msg);
	header('Content-Type: application/json');
	echo json_encode($response);
} else {

	$api = API_RM_PRODUCTION_LIST;

	$bodyContent = array(
		'ArrivalDate' => trim($_POST["arrivalDate"]),
		 'Type' => "HON_PACKING",
		'ArrivalPlant' => "",
		'Supplier' => "",
		'PsId' => ""
	);

	$resList = CallAPI($api, "GET", "", $bodyContent);

	// echo json_encode($resList, JSON_PRETTY_PRINT);

	header('Content-Type: application/json');
	echo json_encode($resList);
}
