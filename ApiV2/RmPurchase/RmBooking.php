<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Config/Config.php';
include_once 'Controller/RmBookingController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();
$payload = Token::Verify($authToken);
if (is_array($payload)) {
    $RequestBody['AuthPayload'] = $payload;
    $RequestBody['AuthUserID'] = isset($payload['UserID']) ? $payload['UserID'] : null;
    $RequestBody['ClientID'] = isset($payload['ClientID']) ? $payload['ClientID'] : null;
}

if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken, $RequestBody);
} else if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
} else if ($RequestMethod == 'DELETE') {
    $data = validateDeleteData($authToken, $RequestBody);
} else {
}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmBookingController($db);
        if ($RequestMethod == 'GET') {
            $itemCount = $Controller->getBookingDetails($RequestBody);
        } else if ($RequestMethod == 'POST') {
            $itemCount = $Controller->postBookingDetails($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $itemCount = $Controller->postDeleteBookingBrkUP($RequestBody);
        }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        $responseData['BreakUpData'] = isset($itemCount['BreakUpData'])  ? $itemCount['BreakUpData'] : null;
        $responseData['BookingData'] = isset($itemCount['BookingData'])  ? $itemCount['BookingData'] : null;
        $responseData['GrandTotal'] = isset($itemCount['GrandTotal'])  ? $itemCount['GrandTotal'] : null;
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateGetData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else  if (!isset($RequestBody['BookingID']) || empty($RequestBody['BookingID'])) {
        $error = 'Boooking ID not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}


function validateDeleteData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else  if (!isset($RequestBody['BookingID']) || empty($RequestBody['BookingID'])) {
        $error = 'Booking ID not Found';
    } else  if (!isset($RequestBody['BreakUpID']) || empty($RequestBody['BreakUpID'])) {
        $error = 'BreakUp ID not Found';
    } else {
        $isValid = true;
    }
    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;
}
function validatePostData($authToken, $RequestBody)
{

    // var_dump($RequestBody);
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else  if (!isset($RequestBody['GraderID']) || empty($RequestBody['GraderID'])) {
        $error = 'Grader Details not Found';
    } else  if (!isset($RequestBody['AgentID']) || empty($RequestBody['AgentID'])) {
        $error = 'Agent Details not Found';
    } else  if (!isset($RequestBody['BookingNumber']) || empty($RequestBody['BookingNumber'])) {
        $error = 'Booking Number not Found';
    } else  if (!isset($RequestBody['DriverID']) || empty($RequestBody['DriverID'])) {
        $error = 'Driver Details not Found';
    } else  if (!isset($RequestBody['VehicalID']) || empty($RequestBody['VehicalID'])) {
        $error = 'Vehicle Details not Found';
    } else  if (!isset($RequestBody['FarmAddress']) || empty($RequestBody['FarmAddress'])) {
        $error = 'Farm Details not Found';
    } else  if (!isset($RequestBody['PurchaseCategoryID']) || empty($RequestBody['PurchaseCategoryID'])) {
        $error = 'Purchase  not Found';
    } else  if (!isset($RequestBody['BookingDate']) || empty($RequestBody['BookingDate'])) {
        $error = 'Booking Date Details not Found';
    } else  if (!isset($RequestBody['BookingStationID']) || empty($RequestBody['BookingStationID'])) {
        $error = 'Center Details not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
