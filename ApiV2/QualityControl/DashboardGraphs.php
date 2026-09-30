<?php

include_once '../Config/Config.php';
include_once 'Controller/DashboardGraphsController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$type = $RequestBody["Type"];
$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new DashboardGraphsController($db);

        if ($type == "antibioticGraphData") {
            $responseData = $Controller->getAntibioticGraphData($RequestBody);
        } else if ($type == "antibioticData") {
            $responseData = $Controller->getAntibioticData($RequestBody);
        } else if ($type == "microGraphData") {
            $responseData = $Controller->getMicroGraphData($RequestBody);
        } else if ($type == "microData") {
            $responseData = $Controller->getMicroData($RequestBody);
        } else if ($type == "swabGraphData") {
            $responseData = $Controller->getSwabGraphData($RequestBody);
        } else if ($type == "swabData") {
            $responseData = $Controller->getSwabData($RequestBody);
        } else if ($type == "waterIceGraphData") {
            $responseData = $Controller->getWaterIceGraphData($RequestBody);
        } else if ($type == "waterIceData") {
            $responseData = $Controller->getWaterIceData($RequestBody);
        } else {
            $responseData = [
                "Error" => true,
                "Msg" => "Invalid Type",
                "Data" => new stdClass()
            ];
        }

        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}


function validateData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
