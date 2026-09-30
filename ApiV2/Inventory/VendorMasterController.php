<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/VendorMasterMainController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;
$responseData = array('Error' => null, 'Msg' => null, 'Data' => null);

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
    $data = array('IsValid' => false, 'Error' => 'Invalid Request Method');
}

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new VendorMasterMainController($db);

        if ($RequestMethod == 'GET') {
            $vendor_id = isset($_GET['vendor_id']) ? $_GET['vendor_id'] : null; //  Get from $_GET for GET requests
            if (is_callable(array($Controller, 'getVendor'))) {
                $itemCount = $Controller->getVendor($vendor_id);
            } else {
                $itemCount = array('Error' => 'Method Not Found', 'Msg' => 'getVendor method does not exist on VendorController', 'Data' => null);
            }


        } else if ($RequestMethod == 'POST') {
            if (is_callable(array($Controller, 'postVendorDetails'))) {
                $itemCount = $Controller->postVendorDetails($RequestBody);
            } else {
                $itemCount = array('Error' => 'Method Not Found', 'Msg' => 'postVendor method does not exist on VendorController', 'Data' => null);
            }
        } else if ($RequestMethod == 'DELETE') {
            if (is_callable(array($Controller, 'deleteVendor'))) {
                $itemCount = $Controller->deleteVendor($RequestBody['vendor_id'],$RequestBody);
            } else {
                $itemCount = array('Error' => 'Method Not Found', 'Msg' => 'deleteVendor method does not exist on VendorController', 'Data' => null);
            }
        }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['sql'] = $itemCount['sql'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        $responseData['Data'] = isset($itemCount['Data']) ? $itemCount['Data'] : null;

        $restApi->response(json_encode($responseData), 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $restApi->response(json_encode($responseData), 200);
}

// ========== VALIDATION FUNCTIONS ==========

function validateDeleteData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } else if (empty($RequestBody['vendor_id'])) {
        $error = 'Vendor ID not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

function validateGetData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    $vendor_id = isset($_GET['vendor_id']) ? $_GET['vendor_id'] : (isset($RequestBody['vendor_id']) ? $RequestBody['vendor_id'] : '');

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        // Always allow GET; if vendor_id exists, it’ll fetch one record; else fetch all
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}



// function validatePostData($authToken, $RequestBody)
// {
//     $isValid = false;
//     $error = null;

//     if (empty($authToken)) {
//         $error = 'Token Not Found';
//     } else if (empty($RequestBody['vendor_name'])) {
//         $error = 'Vendor Name not Found';
//     } else if (empty($RequestBody['vendor_code'])) {
//         $error = 'Vendor Code not Found';
//     } else if (empty($RequestBody['email'])) {
//         $error = 'Email not Found';
//     } else if (empty($RequestBody['tel_no'])) {
//         $error = 'Telephone Number not Found';
//     } else if (empty($RequestBody['address'])) {
//         $error = 'Address not Found';
//     } else if (empty($RequestBody['gstin'])) {
//         $error = 'GSTIN not Found';
//     } else if (empty($RequestBody['state'])) {
//         $error = 'State not Found';
//     } else {
//         $isValid = true;
//     }

//     return ['Error' => $error, 'IsValid' => $isValid];
// }

function validatePostData($authToken, $RequestBody)
{
    return ['Error' => null, 'IsValid' => true];
}


?>
