<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/PmGoodsNoteReportMainController.php';

$database = new Database();
$restApi  = new REST();

$authToken     = $restApi->getRequestHeader();
$RequestBody   = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();


/* ---------------- TOKEN VALIDATION ---------------- */
function validateGetData($authToken)
{
    $isValid = false;
    $error   = null;

    if (!isset($authToken) || empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

$data = validateGetData($authToken);

/* ---------------- API EXECUTION ---------------- */
if ($data['IsValid']) {

    $db = $database->getConnection($authToken);

    if ($db != null) {

        $Controller = new PmGoodsNoteReportMainController($db);

        if ($RequestMethod === 'GET') {

            // LOAD REPORT
            $responseData = $Controller->getGoodsReport($RequestBody);
        } else if ($RequestMethod === 'POST') {

            // UPDATE STATUS
            $responseData = $Controller->updateGoodsStatus($RequestBody);
        }

        $restApi->response(json_encode($responseData), 200);
    }
} else {

    $responseData['Error'] = $data['Error'];
    $responseData['Msg']   = $data['Error'];

    $restApi->response(json_encode($responseData), 200);
}
