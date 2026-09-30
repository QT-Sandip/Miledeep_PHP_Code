<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/PmProformaInvoiceDetailsController.php';

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
        $Controller = new PmProformaInvoiceDetailsController($db);

        if ($RequestMethod == 'GET') {
            if (is_callable(array($Controller, 'loadProformaDetails'))) {
                $methodType = isset($RequestBody['methodType']) ? $RequestBody['methodType'] : '';
                if ($methodType == 'singlePrice') {
                    $itemCount = $Controller->getProformaDetails($RequestBody);
                } else if ($methodType == 'view') {
                    // Call your new view function
                    $PiID = $RequestBody['PiID'];
                    $itemCount = $Controller->getProformaInvoiceForView($PiID);
                } else if ($methodType == 'itemDetails') {
                    $itemCount = $Controller->getItemDetails($RequestBody);
                } else if ($methodType == 'availableData') {
                    $itemCount = $Controller->getAvailableQuantity($RequestBody);
                } else {
                    $itemCount = $Controller->loadProformaDetails($RequestBody);
                }
            } else {
                $itemCount = array('Error' => 'Method Not Found', 'Msg' => 'loadProformaDetails method does not exist on PmProformaInvoiceDetailsController', 'Data' => null);
            }
        } else if ($RequestMethod == 'POST') {
            // echo print_r("call");exit;
            if (is_callable(array($Controller, 'postProformaDetails'))) {
                $itemCount = $Controller->postProformaDetails($RequestBody);
            } else {
                $itemCount = array('Error' => 'Method Not Found', 'Msg' => 'postProformaDetails method does not exist on PmProformaInvoiceDetailsController', 'Data' => null);
            }
        } else if ($RequestMethod == 'DELETE') {
            if (is_callable(array($Controller, 'deleteProformaDetails'))) {
                $itemCount = $Controller->deleteProformaDetails($RequestBody);
            } else {
                $itemCount = array(
                    'Error' => 'Method Not Found',
                    'Msg' => 'deleteProformaDetails method does not exist on PmProformaInvoiceDetailsController',
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
    if (!isset($RequestBody['ProformaDetails_id']) || empty($RequestBody['ProformaDetails_id'])) {
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
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;
}
