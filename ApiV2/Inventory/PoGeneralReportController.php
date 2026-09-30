<?php
include_once '../Config/Config.php';
include_once 'Controller/PoGeneralReportMainController.php';

header('Content-Type: application/json');

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestMethod = $restApi->get_request_method();
$RequestBody = $restApi->getRequestBody();
$db = $database->getConnection($authToken);
if ($db != null) {
    $controller = new PoGeneralReportMainController($db);
    $response = [];

    if ($RequestMethod == "GET") {


        $data = $controller->loadPoGeneralReport($RequestBody);

        $response["StatusCode"] = 200;
        $response["Message"] = "Success";
        $response["Data"] = $data;
    } else if ($RequestMethod == "POST") {
        $response = $controller->updatePoStatus($RequestBody);
    }

    echo json_encode($response);
    exit;
} ?>