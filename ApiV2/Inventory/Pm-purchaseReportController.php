<?php
include_once '../Config/Config.php';
include_once 'Controller/PmPurchaseReportMainController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

function validateGetData($authToken) {
    $isValid = false;
    $error = null;

    if (!isset($authToken) || empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

$data = validateGetData($authToken);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {

        $Controller = new PmPurchaseReportMainController($db);

        if ($RequestMethod === 'GET') {
            // LOAD PURCHASE REPORT
            $responseData = $Controller->getPurchaseReport($RequestBody);

        } else if ($RequestMethod === 'POST') {
            // UPDATE PURCHASE STATUS
            $responseData = $Controller->updatePurchaseStatus($RequestBody);
        }

        $restApi->response(json_encode($responseData), 200);

    } else {
        $responseData = [
            'Error' => 'DB Connection Failed',
            'Msg' => 'DB Connection Failed',
            'Data' => []
        ];
        $restApi->response(json_encode($responseData), 500);
    }
} else {
    $responseData = [
        'Error' => $data['Error'],
        'Msg' => $data['Error'],
        'Data' => []
    ];
    $restApi->response(json_encode($responseData), 200);
}
?>
