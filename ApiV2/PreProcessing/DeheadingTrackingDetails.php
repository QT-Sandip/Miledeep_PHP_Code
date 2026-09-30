<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/DeheadingTrackingDetailsController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();



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
        $Controller = new DeheadingTrackingDetailsController($db);
        if ($RequestMethod == 'GET') {
            $responseData = $Controller->getDeheadingDetails($RequestBody);
        } else if ($RequestMethod == 'POST') {
            $responseData = $Controller->postDeheadingDetails($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $responseData = $Controller->postDeleteDeheading($RequestBody);
        }

        //     $responseData['Error']=$itemCount['Error'];
        //     $responseData['Msg']= $itemCount['Msg'];
        //     $responseData['EditLink'] = '#';
        //     $responseData['EditAccess'] = true;
        //     $responseData['DeleteAccess'] = true;
        //    // $responseData['BreakUpData'] = isset($itemCount['BreakUpData'])  ? $itemCount['BreakUpData']: null;
        //     $responseData['Data'] = isset($itemCount['Data'])  ? $itemCount['Data']: null;
        //    // $responseData['GrandTotal'] = isset($itemCount['GrandTotal'])  ? $itemCount['GrandTotal']: null;

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
    } else if (!isset($RequestBody['TrackID']) || empty($RequestBody['TrackID'])) {
        $error = 'TrackID not Found';
    } else if (!isset($RequestBody['DeheadingBreakUpID']) || empty($RequestBody['DeheadingBreakUpID'])) {
        $error = 'Deheading BreakUpID not Found';
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
    } else if (!isset($RequestBody['TrackID']) || empty($RequestBody['TrackID'])) {
        $error = 'TrackID not Found';
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
    } else if (!isset($RequestBody['TrackID']) && empty($RequestBody['TrackID'])) {
        $error = 'TrackID not Found';
    } else if ($RequestBody['Type'] == '') {

        if ((!isset($RequestBody['Plant']) || empty($RequestBody['Plant'])) && $RequestBody['IsPlantFilter'] != 0) {
            $error = 'Plant Details not Found';
        // } else if (!isset($RequestBody['Center']) || empty($RequestBody['Center'])) { // commented for Session purpose 11/04/2024 by durgaPrasad
        //     $error = 'Center Details not Found '. $RequestBody['Center'];
        } else if (!isset($RequestBody['TransactionDate']) || empty($RequestBody['TransactionDate'])) {
            $error = 'Transaction Date not Found';
        } else if (!isset($RequestBody['Shift']) || empty($RequestBody['Shift'])) {
            $error = 'Shift Details not Found';
        } else if ((!isset($RequestBody['Supervisor']) || empty($RequestBody['Supervisor'])) && empty($RequestBody['DeHeadingBreakup'])) {
            $error = 'Supervisor Details not Found';
        } else {
            $isValid = true;
        }
    } else if ($RequestBody['Type'] != '') {
        if (!isset($RequestBody['HLCount']) || empty($RequestBody['HLCount'])) {
            $error = 'HL Count not Found';
        } 
        else if (!isset($RequestBody['ReceivalBreakUpID']) || empty($RequestBody['ReceivalBreakUpID'])) {
            $error = 'ReceivalBreakUpID not Found';
        } 
        else if (!isset($RequestBody['HLQuantity']) || empty($RequestBody['HLQuantity'])) {
            $error = 'HL Quantity not Found';
        } else if (!isset($RequestBody['HLQuantity']) || empty($RequestBody['HLQuantity'])) {
            $error = 'HL Quantity not Found';
        } else {
            $isValid = true;
        }
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>