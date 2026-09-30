<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/IndentDetailsController.php';

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
        $Controller = new IndentDetailsController($db);
if ($RequestMethod == 'GET') {
    if (is_callable(array($Controller, 'loadIndentDetails'))) {
        $methodType = isset($RequestBody['methodType']) ? $RequestBody['methodType'] : '';

        if ($methodType == 'singlePrice') {
            $itemCount = $Controller->getIndentDetails($RequestBody);
        } else if ($methodType == 'itemDetails') {
            $itemCount = $Controller->getItemDetails($RequestBody);
        } else if ($methodType == 'availableData') {
            $itemCount = $Controller->getAvailableQuantity($RequestBody);
        } else if ($methodType == 'view') {
            // ✅ Added view case for Indent modal
            if (is_callable(array($Controller, 'getFullIndentDetails'))) {
                $itemCount = $Controller->getFullIndentDetails($RequestBody);
            } else {
                $itemCount = [
                    'Error' => 'Method Not Found',
                    'Msg' => 'getFullIndentDetails method does not exist on IndentDetailsController',
                    'Data' => null
                ];
            }
        } else {
            $itemCount = $Controller->loadIndentDetails($RequestBody);
        }
    } else {
        $itemCount = [
            'Error' => 'Method Not Found',
            'Msg' => 'loadIndentDetails method does not exist on IndentDetailsController',
            'Data' => null
        ];
    }

        } else if ($RequestMethod == 'POST') {
            // echo print_r("call");exit;
            if (is_callable(array($Controller, 'postIndentDetails'))) {
                 $methodType = isset($RequestBody['methodType']) ? $RequestBody['methodType'] : '';

                if ($methodType == 'updateStatus') {
                    $itemCount = $Controller->updateIndentStatus($RequestBody);
                }else{
                    $itemCount = $Controller->postIndentDetails($RequestBody);
                }
            } else {
                $itemCount = array('Error' => 'Method Not Found', 'Msg' => 'postIndentDetails method does not exist on IndentDetailsController', 'Data' => null);
            }
        } else if ($RequestMethod == 'DELETE') {
            if (is_callable(array($Controller, 'deleteIndentDetails'))) {
                $itemCount = $Controller->deleteIndentDetails($RequestBody);
            } else {
                $itemCount = array(
                    'Error' => 'Method Not Found',
                    'Msg' => 'deleteIndentDetails method does not exist on IndentDetailsController',
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
    $responseData['status'] = false; // Default when validation fails
    $responseData['Data'] = null;
    
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
    if (!isset($RequestBody['IndentDetails_id']) || empty($RequestBody['IndentDetails_id'])) {
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
