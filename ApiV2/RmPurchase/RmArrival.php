<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/RmArrivalController.php';

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
        $Controller = new RmArrivalController($db);
        $payload = Token::Verify($authToken);
        $RequestBody['ClientID'] = $payload['ClientID'];

        if ($RequestMethod == 'GET') {
            $itemCount = $Controller->getArrivalDetails($RequestBody);
        } else if ($RequestMethod == 'POST') {
            $itemCount = $Controller->postArrivalDetails($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $itemCount = $Controller->postDeleteArrivalBrkUP($RequestBody);
        }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        $responseData['BreakUpData'] = isset($itemCount['BreakUpData']) ? $itemCount['BreakUpData'] : null;
        $responseData['ArrivalData'] = isset($itemCount['ArrivalData']) ? $itemCount['ArrivalData'] : null;
        $responseData['GrandTotal'] = isset($itemCount['GrandTotal']) ? $itemCount['GrandTotal'] : null;
        $responseData['ArrivalNumberData'] = isset($itemCount['ArrivalNumberData']) ? $itemCount['ArrivalNumberData'] : null;
        $responseData['RmRate'] = isset($itemCount['RmRate']) ? $itemCount['RmRate'] : null;
        $responseData['ShowAccess'] = isset($itemCount['ShowAccess']) ? $itemCount['ShowAccess'] : "1";

        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);

    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}


function validateDeleteData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;
    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
        $error = 'Arrival ID not Found';
    } else if (!isset($RequestBody['BreakUpID']) || empty($RequestBody['BreakUpID'])) {
        $error = 'BreakUp ID not Found';
    } else {
        $isValid = true;
    }
    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;
}

function validateGetData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
        // } else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
        //     $error = 'Arrival ID not Found';
        // 
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}
function validatePostData($authToken, $RequestBody)
{

    //   var_dump($RequestBody);

    //echo $RequestBody['GraderID'];
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if (!isset($RequestBody['GraderID']) && empty($RequestBody['GraderID'])) {
        $error = 'Grader Details not Found';
    } else if (!isset($RequestBody['AgentID']) && empty($RequestBody['AgentID'])) {
        $error = 'Agent Details not Found';
    } else if (!isset($RequestBody['ArrivalNumber']) && empty($RequestBody['ArrivalNumber'])) {
        $error = 'Arrival Number not Found';
    } else if (!isset($RequestBody['DriverID']) && empty($RequestBody['DriverID'])) {
        $error = 'Driver Details not Found';
    } else if (!isset($RequestBody['VehicalID']) && empty($RequestBody['VehicalID'])) {
        $error = 'Vehicle Details not Found';
    } else if (!isset($RequestBody['FarmAddress']) && empty($RequestBody['FarmAddress'])) {
        $error = 'Farm Details not Found';
    } else if (!isset($RequestBody['PurchaseCategoryID']) && empty($RequestBody['PurchaseCategoryID'])) {
        $error = 'Purchase  not Found';
    } else if (!isset($RequestBody['ArrivalDate']) && empty($RequestBody['ArrivalDate'])) {
        $error = 'Arrival Date Details not Found';
    } else if (!isset($RequestBody['ArrivalStationID']) && empty($RequestBody['ArrivalStationID'])) {
        $error = 'Center Details not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>
