<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_CREATE_HLVA_VARIETY;

	if ($methodType == "get") {

		$VarietyID = trim($_POST['VarietyID']);

		$bodyContentGet = array(
			"VarietyID" => $VarietyID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} else if ($methodType == "post") {

		$VarietyID = trim($_POST["varietyID"]);
		$Species = trim($_POST["species"]);
		$TypeProduct = trim($_POST["typeProduct"]);
		$ShortName = trim($_POST["shortName"]);
		$FullName = trim($_POST["fullName"]);
		$Category = trim($_POST["category"]);
		$Status = trim($_POST["status"]);
		$Product = trim($_POST["product"]);
		$ProductText = trim($_POST["productText"]);
		$PdDescription = trim($_POST["productDesc"]);

		$bodyContentPost = array(
			"VarietyID" => $VarietyID,
			"Species" => $Species,
			"TypeProduct" => $TypeProduct,
			"ShortName" => $ShortName,
			"FullName" => $FullName,
			"Category" => $Category,
			"Status" => $Status,
			"ScrmProductID" => $Product,
			"ProductID" => $ProductText,
			"ProductDescription" => $PdDescription

		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
	}

	$error = $result->Error;
	$msg = $result->Msg;

	if (!isset($error) || empty($error)) {

		$List = $result->Data->Data;
		if (isset($List) && !empty($List)) {
			echo "<script>alert('$msg');</script>";
			echo '<script>window.location.href = "hlva-variety-list.php";</script>';
			exit;
		}
	} else {
		echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
	}
}

?>