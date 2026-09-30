<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/DepartmentMasterMainController.php';

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
        $Controller = new DepartmentMasterMainController($db);

        // ====== GET ======
        if ($RequestMethod == 'GET') {
            // If DepartmentID provided -> get single record
            
// echo print_r($RequestBody);exit;
            if (!empty($RequestBody['DepartmentID'])) {
                $itemCount = $Controller->getDepartment($RequestBody['DepartmentID']);
            } else {
                // Otherwise load all departments
                $itemCount = $Controller->loadDepartments($RequestBody);
            }
        }

        // ====== POST (INSERT / UPDATE) ======
        elseif ($RequestMethod == 'POST') {
            $itemCount = $Controller->postDepartmentDetails($RequestBody);
        }

        // ====== DELETE ======
        elseif ($RequestMethod == 'DELETE') {
            // $itemCount = $Controller->deleteDepartment($RequestBody['department_id']);
            $itemCount = $Controller->deleteDepartment($RequestBody);

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

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } elseif (empty($RequestBody['department_id'])) {
        $error = 'Department Code not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

//  GET VALIDATION (DepartmentID optional)
function validateGetData($authToken, $RequestBody)
{
    $error = null;
    $isValid = false;

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true; // DepartmentID optional
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
    } elseif (empty($RequestBody['department_name'])) {
        $error = 'Department Name not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}
?>
