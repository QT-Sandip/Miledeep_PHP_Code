<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_RM_AGENT_CREATE;

	if ($methodType == "get") {

		$AgentID = trim($_POST['ID']);

		$bodyContentGet = array(
			"ID" => $AgentID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} else if ($methodType == "post") {

		$AgentID = trim($_POST["agentID"]);
		$AgentName = trim($_POST["agentName"]);
		$GstNumber = trim($_POST["gstNumber"]);
		$PanNumber = trim($_POST["panNumber"]);
		$BankName = trim($_POST["bankName"]);
		$BankAccNo = trim($_POST["bankAccNo"]);
		$Ifsc = trim($_POST["ifsc"]);
		$Company = trim($_POST["company"]);

		$bodyContentPost = array(
			"ID" => $AgentID,
			"UserName" => $AgentName,
			"GstNo" => $GstNumber,
			"PanNumber" => $PanNumber,
			"BankName" => $BankName,
			"BankAccNo" => $BankAccNo,
			"IFSC" => $Ifsc,
			"IsCompany" => $Company

		);
		$result = callAPI($api, "POST", "", $bodyContentPost);


		$error = $result->Error;
		$msg = $result->Msg;

		if (!isset($error) && empty($error)) {

			$List = $result->Data;
			if (isset($List) && !empty($List)) {
				echo "<script>alert('$msg');</script>";
				echo '<script>window.location.href = "agent-list.php";</script>';
				exit;
			}
		} else {
			echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
		}
	}
}
