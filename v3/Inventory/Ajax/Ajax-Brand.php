<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = isset($_POST["methodType"]) ? trim($_POST["methodType"]) : '';

if ($methodType) {

    $api = API_BRAND; // Must be defined in config.php

    // ================= GET SINGLE BRAND =================
    if ($methodType === "getSingle") {
        $brand_id = trim(isset($_POST["BrandID"]) ? $_POST["BrandID"] : '');
        $bodyContent = ["BrandID" => $brand_id];
        $result = CallAPI($api, "GET", "", $bodyContent);
    }

    // ================= SAVE BRAND =================
    else if ($methodType === "save") {

        $bodyContent = [
            "BrandID"      => "0",
            "BrandCode"    => isset($_POST['BrandCode']) ? trim($_POST['BrandCode']) : '',
            "BrandName"    => isset($_POST['BrandName']) ? trim($_POST['BrandName']) : '',
            "Category"     => isset($_POST['Category']) ? trim($_POST['Category']) : '',
            "CompanyName"  => isset($_POST['CompanyName']) ? trim($_POST['CompanyName']) : '',
            "Status"       => isset($_POST['Status']) ? trim($_POST['Status']) : '',
        ];

        $result = CallAPI($api, "POST", "", $bodyContent);
    }


    // ================= UPDATE BRAND =================
    else if ($methodType === "update") {

        // Get the BrandID from POST
        $brand_id = isset($_POST['BrandID']) ? trim($_POST['BrandID']) : '';

        // Handle logo upload
        $logoFile = '';
        if (!empty($_FILES['Logo']['name']) && $_FILES['Logo']['error'] === 0) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/brand_logos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['Logo']['name']);
            $uploadPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['Logo']['tmp_name'], $uploadPath)) {
                $logoFile = $fileName;
            }
        }

        // Prepare request body
        $bodyContent = [
            "BrandID"      => $brand_id,
            "BrandCode"    => isset($_POST['BrandCode']) ? trim($_POST['BrandCode']) : '',
            "BrandName"    => isset($_POST['BrandName']) ? trim($_POST['BrandName']) : '',
            "Category"     => isset($_POST['Category']) ? trim($_POST['Category']) : '',
            "CompanyName"  => isset($_POST['CompanyName']) ? trim($_POST['CompanyName']) : '',
            "Status"       => isset($_POST['Status']) ? trim($_POST['Status']) : '',
            // "Description"  => isset($_POST['Description']) ? trim($_POST['Description']) : '',
            // "Logo"         => $logoFile
        ];

        // Use POST (controller handles insert/update via POST)
        $result = CallAPI($api, "POST", "", $bodyContent);
    }

    // ================= DELETE BRAND =================
    else if ($methodType === "delete") {
        $brand_id = isset($_POST['BrandID']) ? trim($_POST['BrandID']) : '';
        $bodyContent = ["BrandID" => $brand_id];
        $result = CallAPI($api, "DELETE", "", $bodyContent);
    }

    // ================= LOAD BRAND LIST =================
    else if ($methodType === "load") {
        $result = CallAPI($api, "GET", "", []);

        $htmlResponse = '<div class="card">
            <div class="card-body">
                <table id="tblBrand" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Brand Code</th>
                            <th>Brand Name</th>
                            <th>Category</th>
                            <th>Buyer Name</th>
                            <th>Status</th>
                           
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

        $i = 1;
        if (!empty($result->Data)) {
            foreach ($result->Data as $brand) {
                $logo = !empty($brand->Logo)
                    ? '<img src="/uploads/brand_logos/' . htmlspecialchars($brand->Logo) . '" width="40">'
                    : '-';

                $htmlResponse .= '<tr>
                    <td>' . $i . '</td>
                    <td>' . htmlspecialchars(isset($brand->BrandCode) ? $brand->BrandCode : '') . '</td>
                    <td>' . htmlspecialchars(isset($brand->BrandName) ? $brand->BrandName : '') . '</td>
                    <td>' . htmlspecialchars(isset($brand->Category) ? $brand->Category : '') . '</td>
                    <td>' . htmlspecialchars(isset($brand->CompanyName) ? $brand->CompanyName : '') . '</td>
                    <td>' . htmlspecialchars(isset($brand->Status) ? $brand->Status : '') . '</td>
                   
                    <td>
                        <button type="button" class="btn btn-link p-0 btnEdit" data-id="' . htmlspecialchars(isset($brand->BrandID) ? $brand->BrandID : '') . '">
                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                        </button>
                        <button type="button" class="btn btn-link p-0 btnDelete" data-id="' . htmlspecialchars(isset($brand->BrandID) ? $brand->BrandID : '') . '">
                            <i class="mdi mdi-trash-can font-size-16 text-danger"></i>
                        </button>
                    </td>
                </tr>';
                $i++;
            }
        } else {
            $htmlResponse .= '<tr><td colspan="8" class="text-center">No records found</td></tr>';
        }

        $htmlResponse .= '</tbody></table></div></div>';

        header('Content-Type: text/html');
        echo $htmlResponse;
        exit;
    }

    // ================= COMMON RESPONSE =================
    if (isset($result) && is_object($result)) {
        $error = isset($result->Error) ? $result->Error : '';
        $msg   = isset($result->Msg) ? $result->Msg : '';
        $data  = isset($result->Data) ? $result->Data : [];
    } else {
        $error = 'Invalid API Response';
        $msg   = '';
        $data  = [];
    }

    header('Content-Type: application/json');
    echo json_encode([
        "status" => empty($error) ? "success" : "error",
        "msg"    => !empty($msg) ? $msg : "Saved completed successfully",

        "data"   => $data
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "msg" => "Invalid methodType"]);
}
