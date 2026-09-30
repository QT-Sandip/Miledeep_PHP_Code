<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = isset($_POST["methodType"]) ? trim($_POST["methodType"]) : '';


if ($methodType) {

    $api = API_DEPARTMENT; // Must be defined in config.php

    // ================= GET SINGLE DEPARTMENT =================
    if ($methodType === "getSingle") {
        $department_id = trim(isset($_POST["department_id"]) ? $_POST["department_id"] : '');
        $bodyContent = ["DepartmentID" => $department_id]; // Correct key
        $result = CallAPI($api, "GET", "", $bodyContent);
    }

    // ================= SAVE DEPARTMENT =================
    else if ($methodType === "save") {
        $department_id = trim(isset($_POST["department_id"]) ? $_POST["department_id"] : '0'); // auto-increment id (hidden)
        $bodyContent = [
            "department_id"   => $department_id,
            "department_code" => trim(isset($_POST["department_code"]) ? $_POST["department_code"] : ''),
            "department_name" => trim(isset($_POST["department_name"]) ? $_POST["department_name"] : ''),
            "category"        => trim(isset($_POST["category"]) ? $_POST["category"] : ''),
            "stat"            => trim(isset($_POST["stat"]) ? $_POST["stat"] : '')
        ];
        $result = CallAPI($api, "POST", "", $bodyContent);
    }

    // ================= UPDATE DEPARTMENT =================
    else if ($methodType === "update") {
        $bodyContent = [
            "department_id"   => trim(isset($_POST["department_id"]) ? $_POST["department_id"] : ''),
            "department_code" => trim(isset($_POST["department_code"]) ? $_POST["department_code"] : ''),
            "department_name" => trim(isset($_POST["department_name"]) ? $_POST["department_name"] : ''),
            "category"        => trim(isset($_POST["category"]) ? $_POST["category"] : ''),
            "stat"            => trim(isset($_POST["stat"]) ? $_POST["stat"] : '')
        ];

        // Keep using POST (controller expects POST for create/update)
        $result = CallAPI($api, "POST", "", $bodyContent);
    }

    // ================= DELETE DEPARTMENT =================
    else if ($methodType === "delete") {
        $department_id = trim(isset($_POST["department_id"]) ? $_POST["department_id"] : '');
        $bodyContent = ["department_id" => $department_id];
        $result = CallAPI($api, "DELETE", "", $bodyContent);
    }

    // ================= LOAD DEPARTMENT LIST =================
else if ($methodType === "load") {

    $result = CallAPI($api, "GET", "", []);

    $htmlResponse = '<div class="card">
        <div class="card-body">
            <table id="tblDepartment" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>S.No</th>
                       <!-- <th>Department Code</th> -->
                        <th>Department Name</th>
                        <th>Sub Department Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';

    $i = 1;

    if (!empty($result->Data)) {
        foreach ($result->Data as $department) {

            $htmlResponse .= '<tr>
                <td>' . $i . '</td>
                <!-- <td>' . htmlspecialchars($department->DepartmentCode ) . '</td> -->                
                <td>' . htmlspecialchars($department->Category ) . '</td>
                <td>' . htmlspecialchars($department->DepartmentName ) . '</td>
                <td>' . htmlspecialchars($department->Status ) . '</td>
                <td>
                    <button class="btn btn-link p-0 btnEdit" data-id="' . ($department->DepartmentID ) . '">
                        <i class="mdi mdi-pencil text-primary"></i>
                    </button>
                    <button class="btn btn-link p-0 btnDelete" data-id="' . ($department->DepartmentID ) . '">
                        <i class="mdi mdi-trash-can text-danger"></i>
                    </button>
                </td>
            </tr>';

            $i++;
        }
    } else {
        $htmlResponse .= '<tr><td colspan="7" class="text-center">No records found</td></tr>';
    }

    $htmlResponse .= '</tbody></table></div></div>';

    echo json_encode([
        "status" => "success",
        "html" => $htmlResponse
    ]);

    exit;
}
else if ($methodType === "getSubDepartments") {
        $bodyContent = $_POST;
        $result = CallAPI($api, "GET", "", $bodyContent);
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
        "msg"    => $msg ?: $error,
        "data"   => $data
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "msg" => "Invalid methodType"]);
}
