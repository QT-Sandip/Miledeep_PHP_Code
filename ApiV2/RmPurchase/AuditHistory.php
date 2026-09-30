<?php

include_once '../Config/Config.php';
include_once 'Controller/AuditHistoryController.php';

$database = new Database();
$restApi = new REST();
$authToken = $restApi->getRequestHeader();
$requestBody = $restApi->getRequestBody();
$payload = Token::Verify($authToken);
if (is_array($payload)) {
    $requestBody['AuthPayload'] = $payload;
    $requestBody['AuthUserID'] = isset($payload['UserID']) ? $payload['UserID'] : null;
    $requestBody['ClientID'] = (isset($payload['ClientID']) && $payload['ClientID'] !='serp') ? $payload['ClientID'] : (isset($requestBody['ClientID']) ? $requestBody['ClientID'] : null);
}
$data = validateData($authToken);

if ($data['IsValid']) {
    $db = $database->getAuthConnection();
    if ($db != null) {
        $controller = new AuditHistoryController($db);
        $historyData = $controller->getAuditHistory($requestBody);

        $responseData['Error'] = isset($historyData['Error']) ? $historyData['Error'] : null;
        $responseData['Msg'] = isset($historyData['Error']) ? $historyData['Error'] : 'Success';
        $responseData['TotalCount'] = isset($historyData['TotalCount']) ? $historyData['TotalCount'] : 0;
        $responseData['PageCount'] = isset($historyData['PageCount']) ? $historyData['PageCount'] : 0;
        $responseData['RecordsAval'] = isset($historyData['RecordsAval']) ? $historyData['RecordsAval'] : 0;
        $responseData['Filters'] = isset($historyData['Filters']) ? $historyData['Filters'] : null;
        $responseData['ModuleSummary'] = isset($historyData['ModuleSummary']) ? $historyData['ModuleSummary'] : null;
        $responseData['Data'] = isset($historyData['Data']) ? $historyData['Data'] : null;

        $restApi->response(json_encode($responseData), 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $restApi->response(json_encode($responseData), 200);
}

function validateData($authToken)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    return [
        'Error' => $error,
        'IsValid' => $isValid,
    ];
}
