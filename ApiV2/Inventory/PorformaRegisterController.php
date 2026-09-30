<?php
include_once '../Config/Config.php';
include_once 'Controller/PorformaRegisterMainController.php';

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
        $Controller = new PorformaRegisterController($db);

        // ====== GET ======
        if ($RequestMethod == 'GET') {
            if (!empty($RequestBody['ProformaID'])) {
                $itemCount = $Controller->getProforma($RequestBody['ProformaID']);
            } else {
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

// DELETE VALIDATION
function validateDeleteData($authToken, $RequestBody)
{
    $error = null;
    $isValid = false;

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } elseif (empty($RequestBody['ProformaID'])) {
        $error = 'Proforma ID not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

// GET VALIDATION
function validateGetData($authToken, $RequestBody)
{
    $error = null;
    $isValid = false;

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

// POST VALIDATION
function validatePostData($authToken, $RequestBody)
{
    $error = null;
    $isValid = false;

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } elseif (empty($RequestBody['date_option'])) {
        $error = 'Date Option not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}
?>
