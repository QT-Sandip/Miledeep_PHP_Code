<?php
include_once '../Config/Config.php';
include_once 'Controller/BrandMasterMainController.php';

$database = new Database();
$restApi = new REST();

$authToken     = $restApi->getRequestHeader();
$RequestBody   = $restApi->getRequestBody();
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
        $Controller = new BrandMasterMainController($db);

        // ====== GET ======
        if ($RequestMethod == 'GET') {
            if (!empty($RequestBody['BrandID'])) {
                // Get single brand record
                $itemCount = $Controller->getBrand($RequestBody['BrandID']);
            } else {
                // Load all brands
                $itemCount = $Controller->loadBrands();
            }
        }

        // ====== POST (INSERT / UPDATE) ======
        elseif ($RequestMethod == 'POST') {
            $itemCount = $Controller->postBrandDetails($RequestBody);
        }

        // ====== DELETE ======
        elseif ($RequestMethod == 'DELETE') {
            $itemCount = $Controller->deleteBrand($RequestBody);
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
    } elseif (empty($RequestBody['BrandID'])) {
        $error = 'Brand ID not Found';
    } else {
        $isValid = true;
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}

//  GET VALIDATION (BrandID optional)
function validateGetData($authToken, $RequestBody)
{
    $error = null;
    $isValid = false;

    if (empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true; // BrandID optional for full list
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
    } else {
        $brandName = isset($RequestBody['BrandName']) ? $RequestBody['BrandName'] : (isset($RequestBody['brand_name']) ? $RequestBody['brand_name'] : '');
        $brandCode = isset($RequestBody['BrandCode']) ? $RequestBody['BrandCode'] : (isset($RequestBody['brand_code']) ? $RequestBody['brand_code'] : '');
        $category  = isset($RequestBody['Category'])  ? $RequestBody['Category']  : (isset($RequestBody['category']) ? $RequestBody['category'] : '');

        if (empty($brandName)) {
            $error = 'Brand Name not Found';
        } elseif (empty($brandCode)) {
            $error = 'Brand Code not Found';
        } elseif (empty($category)) {
            $error = 'Category not Found';
        } else {
            $isValid = true;
        }
    }

    return ['Error' => $error, 'IsValid' => $isValid];
}
?>
