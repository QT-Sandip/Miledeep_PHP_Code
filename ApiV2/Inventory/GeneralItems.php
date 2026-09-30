<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/GeneralItemsController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

// echo print_r($RequestBody);exit;

if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken, $RequestBody);
} else if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
} else if ($RequestMethod == 'DELETE') {
    $data = validateDeleteData($authToken, $RequestBody);
} else {
    $data = array('IsValid' => false, 'Error' => 'Invalid Request Method');
}

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new GeneralItemsController($db);

        if ($RequestMethod == 'GET') {
            if (is_callable(array($Controller, 'loadGeneralItems'))) {
                $methodType = isset($RequestBody['methodType']) ? $RequestBody['methodType'] : '';
                if($methodType == 'singleItem'){
                     $itemCount = $Controller->getGeneralItem($RequestBody);
                }else{
                    $itemCount = $Controller->loadGeneralItems($RequestBody);
                }
            } else {
                $itemCount = array('Error' => 'Method Not Found', 'Msg' => 'loadGeneralItems method does not exist on GeneralItemsController', 'Data' => null);
            }
        } else if ($RequestMethod == 'POST') {
            // echo print_r("call");exit;
            if (is_callable(array($Controller, 'postGeneralItems'))) {
                $itemCount = $Controller->postGeneralItems($RequestBody);
            } else {
                $itemCount = array('Error' => 'Method Not Found', 'Msg' => 'postGeneralItems method does not exist on GeneralItemsController', 'Data' => null);
            }
        } else if ($RequestMethod == 'DELETE') {
            if (is_callable(array($Controller, 'deleteGeneralItem'))) {
                $itemCount = $Controller->deleteGeneralItem($RequestBody);
            } else {
                $itemCount = array(
                    'Error' => 'Method Not Found',
                    'Msg' => 'deleteGeneralItem method does not exist on generalItemController',
                    'Data' => null
                );
            }
        }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        $responseData['status'] = $itemCount['status'];
        $responseData['Data'] = isset($itemCount['Data']) ? $itemCount['Data'] : null;

        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData['status'] = $itemCount['status'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

// ========== VALIDATION FUNCTIONS ==========

function validateDeleteData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) || empty($authToken)) {
        $error = 'Token Not Found';
    }
    if (!isset($RequestBody['generalItem_id']) || empty($RequestBody['generalItem_id'])) {
        $error = 'Item ID not Found';
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

    if (!isset($authToken) || empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;
}

function validatePostData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) || empty($authToken)) {
        $error = 'Token Not Found';
    }  else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;
}
