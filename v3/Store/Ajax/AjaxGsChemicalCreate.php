<?php

	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_CHEMICAL;

	if ($methodType == "get") {

		$ChemID = trim($_POST['chemID']);

		$bodyContentGet = array(
			"ChemID" => $ChemID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} 

	if ($methodType == "post") {

		$ChemID = trim($_POST["chemID"]);
		$ChemicalName = trim($_POST["chemical"]);
		$ChemicalDescription = trim($_POST["description"]);
		$NoOfKgPerBag = trim($_POST["kgPerBag"]);

		$bodyContentPost = array(
			"ChemID" => $ChemID,
			"ChemicalName" => $ChemicalName, 
			"ChemicalDescription" => $ChemicalDescription,
			"NoOfKgPerBag" => $NoOfKgPerBag
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
		header('Content-Type: application/json');
		echo json_encode($result);

	}

}

?>