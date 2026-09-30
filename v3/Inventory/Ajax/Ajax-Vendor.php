<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = isset($_POST["methodType"]) ? trim($_POST["methodType"]) : '';

// if (empty($methodType)) {
//     echo json_encode(["status" => "error", "msg" => "Invalid methodType", "data" => []]);
//     exit;
// }

$api = API_VENDOR;

// ================= GET SINGLE VENDOR =================
if ($methodType === "getSingle") {
    $vendor_id = isset($_POST["vendor_id"]) ? trim($_POST["vendor_id"]) : '';

    if (empty($vendor_id)) {
        echo json_encode(["status" => "error", "msg" => "Vendor ID missing", "data" => []]);
        exit;
    }

    $result = CallAPI($api . "?vendor_id=" . urlencode($vendor_id), "GET", "", []);

    $result = json_decode(json_encode($result));

    $msg = isset($result->Msg) ? $result->Msg : "";
    $data = null;
    if (isset($result->Data)) {
        if (is_array($result->Data)) {
            $data = isset($result->Data[0]) ? $result->Data[0] : [];
        } else {
            $data = $result->Data;
        }
    } else {
        $data = [];
    }

    echo json_encode([
        "status" => (isset($result->Error) && $result->Error) ? "error" : "success",
        "msg"    => $msg,
        "data"   => $data
    ]);
    exit;
}
 elseif ($methodType === "save") {
    $bodyContent = $_POST;
    unset($bodyContent['methodType']);

    $result = CallAPI($api, "POST", "", $bodyContent);

    if (!empty($result->Data) && isset($result->Data->VendorID)) {
        echo json_encode([
            "status" => "success",
             "sql" => $result->sql,
            "msg"    => (isset($result->Msg) ? $result->Msg : "Vendor saved successfully"),
            "vendor_id" => $result->Data->VendorID
        ]);
    } else {
        echo json_encode([
            "status" => "error",
             "sql" => $result->sql,
            "msg"    => (isset($result->Msg) ? $result->Msg : "Failed to save vendor"),
            "data"   => (isset($result->Data) ? $result->Data : [])
        ]);
    }
    exit;

    // ================= UPDATE VENDOR =================
} elseif ($methodType === "update") {
    $bodyContent = $_POST;
    unset($bodyContent['methodType']);

    $result = CallAPI($api, "POST", "", $bodyContent);
    $result = json_decode(json_encode($result));

    $status = (isset($result->Error) && $result->Error) ? "error" : "success";
    echo json_encode([
        "status" => $status,
        "msg"    => (isset($result->Msg) ? $result->Msg : "Vendor updated successfully"),
        "data"   => (isset($result->Data) ? $result->Data : [])
    ]);
    exit;

    // ================= DELETE VENDOR =================
} elseif ($methodType === "delete") {
    $vendor_id = isset($_POST["vendor_id"]) ? trim($_POST["vendor_id"]) : '';
    if (empty($vendor_id)) {
        echo json_encode(["status" => "error", "msg" => "Vendor ID missing", "data" => []]);
        exit;
    }

    $bodyContent = ["vendor_id" => $vendor_id];
    $result = CallAPI($api, "DELETE", "", $bodyContent);
    $result = json_decode(json_encode($result));

    $status = (isset($result->Error) && $result->Error) ? "error" : "success";
    $msg = isset($result->Msg) ? $result->Msg : ($status === "success" ? "Vendor deleted successfully" : "Failed to delete vendor");
    echo json_encode([
        "status" => $status,
        "msg"    => $msg,
        "data"   => (isset($result->Data) ? $result->Data : [])
    ]);
    exit;

    // ================= LOAD VENDOR LIST =================
} elseif ($methodType === "load") {
    $result = CallAPI($api, "GET", "", []);
    $result = json_decode(json_encode($result)); // ensure stdClass format

    $htmlResponse = '<div class="card">
        <div class="card-body">
            <table id="tblVendor" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Vendor Name</th>
                        <th>Vendor Code</th>
                        <th>Account Type</th>
                        <th>Email</th>
                        <th>Tel No</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';

    $i = 1;
    if (!empty($result->Data) && is_array($result->Data)) {
        foreach ($result->Data as $vendor) {
            $vendorName = isset($vendor->VendorName) ? $vendor->VendorName : '-';
            $vendorCode = isset($vendor->VendorCode) ? $vendor->VendorCode : '-';
            $accountType = isset($vendor->AccountType) ? $vendor->AccountType : '-';
            $email = isset($vendor->Email) ? $vendor->Email : '-';
            $telNo = isset($vendor->TelNo) ? $vendor->TelNo : '-';
            $vendorId = isset($vendor->VendorID) ? $vendor->VendorID : '';

            $htmlResponse .= '<tr>
                <td>' . $i . '</td>
                <td>' . htmlspecialchars($vendorName) . '</td>
                <td>' . htmlspecialchars($vendorCode) . '</td>
                <td>' . htmlspecialchars($accountType) . '</td>
                <td>' . htmlspecialchars($email) . '</td>
                <td>' . htmlspecialchars($telNo) . '</td>
                <td>
                    <button type="button" class="btn btn-link p-0 btnEdit" data-id="' . htmlspecialchars($vendorId) . '">
                        <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                    </button>
                    <button type="button" class="btn btn-link p-0 btnDelete" data-id="' . htmlspecialchars($vendorId) . '">
                        <i class="mdi mdi-delete font-size-16 text-danger"></i>
                    </button>
                    <button type="button" class="btn btn-link p-0 btnView" data-id="' . htmlspecialchars($vendorId) . '">
        <i class="mdi mdi-eye font-size-16 text-info"></i>
    </button>
                </td>
            </tr>';
            $i++;
        }
    } else {
        $htmlResponse .= '<tr><td colspan="7" class="text-center text-muted">No Vendors Found</td></tr>';
    }

    $htmlResponse .= '</tbody></table></div></div>';

    echo json_encode([
        "status" => "success",
        "html"   => $htmlResponse,
        "data"   => (isset($result->Data) ? $result->Data : [])
    ]);
    exit;

    // ================= INVALID METHOD =================
} else {
    echo json_encode(["status" => "error", "msg" => "Invalid methodType", "data" => []]);
    exit;
}
?>
