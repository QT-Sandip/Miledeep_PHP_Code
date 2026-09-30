<?php
include_once '../Config/Config.php';
include_once 'Controller/PorformaRegisterCreateMainController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

// ================= REQUEST METHOD HANDLING =================
if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken, $RequestBody);
} elseif ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
} elseif ($RequestMethod == 'DELETE') {
    $data = validateDeleteData($authToken, $RequestBody);
} else {
    $data = ['IsValid' => false, 'Error' => 'Invalid Request Method'];
}

// ================= IF VALIDATION PASSED =================
if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new PorformaRegisterCreateController($db);

        // ====== GET ======
        if ($RequestMethod == 'GET') {
            // If ProformaID provided -> get single record
            if (!empty($RequestBody['ProformaID'])) {
                $itemCount = $Controller->getProforma($RequestBody['ProformaID']);
            } else {
                // Otherwise load all proforma records
                $itemCount = $Controller->loadProformas();
            }
        }

        // ====== POST (INSERT / UPDATE) ======
        elseif ($RequestMethod == 'POST') {
            $itemCount = $Controller->postProformaDetails($RequestBody);
        }

        // ====== DELETE ======
        elseif ($RequestMethod == 'DELETE') {
            $itemCount = $Controller->deleteProforma($RequestBody);
        }

        // ====== COMMON RESPONSE ======
        $responseData = [
            'Error' => isset($itemCount['Error']) ? $itemCount['Error'] : '',
            'Msg'   => isset($itemCount['Msg']) ? $itemCount['Msg'] : '',
            'Data'  => isset($itemCount['Data']) ? $itemCount['Data'] : null
        ];

        $restApi->response(json_encode($responseData), 200);
    }
} else {
    $responseData = [
        'Error' => $data['Error'],
        'Msg'   => $data['Error']
    ];
    $restApi->response(json_encode($responseData), 200);
}

// ============================================================
// =============== VALIDATION FUNCTIONS =======================
// ============================================================

//  DELETE VALIDATION
function validateDeleteData($authToken, $RequestBody)
{
    $error = null;
    $isValid = false;

    if (isset($RequestBody['ProformaID'])) {
        $ProformaID = $RequestBody['ProformaID'];
    } elseif (isset($_GET['ProformaID'])) {
        $ProformaID = $_GET['ProformaID'];
    } else {
        $ProformaID = '';
    }

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } elseif (empty($ProformaID)) {
        $error = 'Proforma ID not found in request';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}




//  GET VALIDATION (ProformaID optional)
function validateGetData($authToken, $RequestBody)
{
    $error = null;
    $isValid = false;

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true; // ProformaID optional
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

//  POST VALIDATION
function validatePostData($authToken, $RequestBody)
{
    $error = null;
    $isValid = false;

    

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } elseif (empty($RequestBody['pro_no'])) {
        $error = 'Proforma Number not Found';
    }elseif (empty($RequestBody['total_quantity'])) {
        $error = 'Total Quantity not Found';
    } elseif (!isset($RequestBody['commission']) || !is_numeric($RequestBody['commission'])) {
        $error = 'Commission must be a valid number';
    } elseif (!isset($RequestBody['commission_value']) || !is_numeric($RequestBody['commission_value'])) {
        $error = 'Commission Value must be a valid number';
    } elseif (empty($RequestBody['approved_by'])) {
        $error = 'Approved By field is required';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

?>
