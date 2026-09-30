<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_BIO_METRIC_DATA;

$data = $_POST["jData"];	

// echo $_POST["jData"];
// exit;

//$bodyContent = $data;
$bodyContent = file_get_contents("php://input");

$result = callAPI($api, "POST", "", $bodyContent);

// echo json_encode($bodyContent);
// exit;

header('Content-Type: application/json');
echo json_encode($result);

		// $error = $result->Error;
		// $msg = $result->Msg;

		// if (!isset($error) && empty($error)) {

		// 	$List = $result->Data;
		// 	if (isset($List) && !empty($List)) {
		// 		echo "<script>alert('$msg');</script>";
		// 		echo '<script>window.location.href = "agent-list.php";</script>';
		// 		exit;
		// 	}
		// } else {
		// 	echo "<div class='text-center mt-3 mb-3 text-bold' style='color: red;'>NOTE : " . $error . "</div>";
		// }
	// }
// }
