<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

$api = API_PURCHASE_RATES;

if ($methodType == 'getCentersList') {
	$api = API_PURCHASE_RATES;

	$bodyContent = [
		'RmPriceDate' => trim($_POST['rmDate']),
		'Species' => trim($_POST['species']),
	];
	$resList = CallAPI($api, "GET", "", $bodyContent);
	$centersList = $resList->CentersData;
	// echo json_encode($centersList, JSON_PRETTY_PRINT);
	// exit;
	header('Content-Type: application/json');
	echo json_encode($centersList);
	exit;
} else if ($methodType == 'updateData') {

	$bodyContentPost = array(
		'BaseRates' =>  $_POST["arrayData"],
		'Species' =>  trim($_POST["species"]),
		'DateId' =>  trim($_POST["dateId"]),
		'IsEdit' =>  trim($_POST["isEdit"]),
		'PurchaseCenter' =>  trim($_POST["purchaseCenter"]),
		'SubmissionDate' =>  trim($_POST["submissionDate"]),
	);
	$resList = CallAPI($api, "POST", "", $bodyContentPost);
} else if ($methodType == "getList") {

	$bodyContent = array(
		'Species' =>  trim($_POST["species"]),
		'DateId' =>  trim($_POST["dateId"]),
	);
	$resList = CallAPI($api, "GET", "", $bodyContent);
} else if ($methodType == "copy") {
	$bodyContent = array(
		'SubmissionDate' =>  trim($_POST["submissionDate"]),
		'DateId' =>  trim($_POST["DateId"]),
		'Stype' =>  trim($_POST["Stype"]),
		'Type' => "copy",
	);

	$resList = CallAPI($api, "POST", "", $bodyContent);
} else if ($methodType == "delete") {
	$bodyContent = array(
		'DateId' =>  trim($_POST["DateId"]),
		'Stype' =>  trim($_POST["Stype"]),
		'Type' => "delete",
	);

	$resList = CallAPI($api, "POST", "", $bodyContent);
}
$Error = $resList->Error;
$Msg = $resList->Msg;
$TrackId = $resList->TrackId;
$resData = $resList->Data;

$content = '';
$RmPriceDate = '';
$CenterID = '';

if (!empty($resData)) {
	$RmPriceDate = $resData[0]->RmPriceDate;
	$CenterID = $resData[0]->CenterID;

	$Size = count($resData);
	$columns = 10;
	$rows = ceil($Size / $columns);

	$content = '';

	$content .= ' <table id="tblDataTable" class="table table-hover table-bordered">
    <tbody>
	<input type="hidden" id="hdnDate" name="hdnDate" value="' . $RmPriceDate . '">
	<input type="hidden" id="hdnDate" name="hdnDate" value="' . $CenterID . '">';

	$index = 0;
	for ($i = 0; $i < $rows; $i++) {
		$content .= ' <tr>';
		for ($j = 0; $j < $columns; $j++) {
			if ($index < $Size) {
				$RmCount = $resData[$index]->RmCount;
				$RateId = $resData[$index]->RateId;
				$RmPrice = $resData[$index]->RmPrice;
				$content .= '<td><input type="hidden" class="hdnRmCount" value="' . $RmCount . '" ><b style="color: #0728AA; font-weight: bold;">' . $RmCount . '</b></td>
                         <td><input type="text" class="form-control baseRate" name="baseRate" placeholder="Enter Base Price" value="' . $RmPrice . '">
						 <input type="hidden" class="hdnRateId" value="' . $RateId . '"</td>';
				$index++;
			} else {
				$content .= '<td></td><td></td>';
			}
		}
		$content .= ' </tr>';
	}
	$content .= '</tbody>
	</table>';
} else {
	$content .= '<p align="center" >Note : No Data Available </p>';
}

header('Content-Type: application/json');

echo json_encode([
	'content' => $content,
	'RmPriceDate' => $RmPriceDate,
	'CenterID' => $CenterID,
	'Error' => $Error,
	'Msg' => $Msg,
	'TrackId' => $TrackId
]);
