<?php

include_once '../Config/Config.php';
include_once 'Controller/RawMaterialRequirementReportSMEController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

$payload = Token::Verify($authToken);

$RequestBody['ClientID'] = $payload['ClientID'];

if ($RequestMethod == 'GET') {

    $data = validateGetData($authToken);

    if ($data['IsValid']) {

        $db = $database->getConnection($authToken);

        if ($db != null) {

            $Controller = new RawMaterialRequirementReportSMEController($db);

            $responseData = $Controller->getRequiredRawMaterialDetails($RequestBody);

            $responseData = json_encode($responseData);

            $restApi->response($responseData, 200);
        }
    } else {

        $responseData['Error'] = $data['Error'];
        $responseData['Msg'] = $data['Error'];

        $responseData = json_encode($responseData);

        $restApi->response($responseData, 200);
    }
}

function validateGetData($authToken)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) || empty($authToken)) {

        $error = 'Token Not Found';

    } else {

        $isValid = true;
    }

    return [
        'Error' => $error,
        'IsValid' => $isValid
    ];
}