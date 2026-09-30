<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = isset($_POST["methodType"]) ? trim($_POST["methodType"]) : '';

if ($methodType) {

    $api = API_PROFORMA_REGISTER; // Must be defined in config/global.php

    // ================= GET SINGLE PROFORMA REGISTER =================
   if ($methodType === "getSingle") {
    $proforma_id = trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : '');
    $bodyContent = ["ProformaID" => $proforma_id]; 
    $result = CallAPI($api, "GET", "", $bodyContent);
}


  
// ================= SAVE PROFORMA REGISTER =================
// else if ($methodType === "save") {
//     $bodyContent = [
//         "ProformaID" => trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : '0'),
//         "date_option" => trim(isset($_POST["date_option"]) ? $_POST["date_option"] : ''),
//         "start_date"  => trim(isset($_POST["start_date"]) ? $_POST["start_date"] : ''),
//         "end_date"    => trim(isset($_POST["end_date"]) ? $_POST["end_date"] : ''),
//         "layout"      => trim(isset($_POST["layout"]) ? $_POST["layout"] : ''),
//         "output"      => trim(isset($_POST["output"]) ? $_POST["output"] : '')
//     ];

//     $result = CallAPI($api, "POST", "", $bodyContent);
// }


else if ($methodType === "save") {
    $bodyContent = [
        "ProformaID" => trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : '0'),
        "date_option" => trim(isset($_POST["date_option"]) ? $_POST["date_option"] : ''),
        "start_date"  => trim(isset($_POST["start_date"]) ? $_POST["start_date"] : ''),
        "end_date"    => trim(isset($_POST["end_date"]) ? $_POST["end_date"] : ''),
        "layout"      => trim(isset($_POST["layout"]) ? $_POST["layout"] : ''),
        "output"      => trim(isset($_POST["output"]) ? $_POST["output"] : '')
    ];

    // add this
    if (empty($bodyContent['start_date']) || empty($bodyContent['end_date'])) {
        $today = date('Y-m-d');
        $opt = $bodyContent['date_option'];
        if ($opt === 'today') {
            $bodyContent['start_date'] = $bodyContent['end_date'] = $today;
        } elseif ($opt === 'this_week') {
            $monday = date('Y-m-d', strtotime('monday this week'));
            $sunday = date('Y-m-d', strtotime('sunday this week'));
            $bodyContent['start_date'] = $monday;
            $bodyContent['end_date'] = $sunday;
        } elseif ($opt === 'this_month') {
            $bodyContent['start_date'] = date('Y-m-01');
            $bodyContent['end_date'] = date('Y-m-t');
        } elseif ($opt === 'previous_month') {
            $bodyContent['start_date'] = date('Y-m-01', strtotime('first day of last month'));
            $bodyContent['end_date'] = date('Y-m-t', strtotime('last day of last month'));
        }
    }

    $result = CallAPI($api, "POST", "", $bodyContent);
}

// ================= UPDATE PROFORMA REGISTER =================
// else if ($methodType === "update") {
//     $bodyContent = [
//         "ProformaID" => trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : ''), 
//         "date_option" => trim(isset($_POST["date_option"]) ? $_POST["date_option"] : ''),
//         "start_date"  => trim(isset($_POST["start_date"]) ? $_POST["start_date"] : ''),
//         "end_date"    => trim(isset($_POST["end_date"]) ? $_POST["end_date"] : ''),
//         "layout"      => trim(isset($_POST["layout"]) ? $_POST["layout"] : ''),
//         "output"      => trim(isset($_POST["output"]) ? $_POST["output"] : '')
//     ];

//     $result = CallAPI($api, "POST", "", $bodyContent);
// }


else if ($methodType === "update") {
    $bodyContent = [
        "ProformaID" => trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : ''), 
        "date_option" => trim(isset($_POST["date_option"]) ? $_POST["date_option"] : ''),
        "start_date"  => trim(isset($_POST["start_date"]) ? $_POST["start_date"] : ''),
        "end_date"    => trim(isset($_POST["end_date"]) ? $_POST["end_date"] : ''),
        "layout"      => trim(isset($_POST["layout"]) ? $_POST["layout"] : ''),
        "output"      => trim(isset($_POST["output"]) ? $_POST["output"] : '')
    ];

    //  same logic as SAVE
    if (empty($bodyContent['start_date']) || empty($bodyContent['end_date'])) {
        $today = date('Y-m-d');
        $opt = $bodyContent['date_option'];
        if ($opt === 'today') {
            $bodyContent['start_date'] = $bodyContent['end_date'] = $today;
        } elseif ($opt === 'this_week') {
            $monday = date('Y-m-d', strtotime('monday this week'));
            $sunday = date('Y-m-d', strtotime('sunday this week'));
            $bodyContent['start_date'] = $monday;
            $bodyContent['end_date'] = $sunday;
        } elseif ($opt === 'this_month') {
            $bodyContent['start_date'] = date('Y-m-01');
            $bodyContent['end_date'] = date('Y-m-t');
        } elseif ($opt === 'previous_month') {
            $bodyContent['start_date'] = date('Y-m-01', strtotime('first day of last month'));
            $bodyContent['end_date'] = date('Y-m-t', strtotime('last day of last month'));
        }
    }

    $result = CallAPI($api, "POST", "", $bodyContent);
}


    // ================= DELETE PROFORMA REGISTER =================
   else if ($methodType === "delete") {
    $proforma_id = trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : '');
    $bodyContent = ["ProformaID" => $proforma_id]; 
    $result = CallAPI($api, "DELETE", "", $bodyContent);
}


    // ================= LOAD PROFORMA REGISTER LIST =================
    else if ($methodType === "load") {
        $result = CallAPI($api, "GET", "", []);

        $htmlResponse = '<div class="card">
            <div class="card-body">
                <table id="tblProforma" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Date Option</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Layout</th>
                            <th>Output</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

        $i = 1;
        if (!empty($result->Data)) {
            foreach ($result->Data as $row) {
                $htmlResponse .= '<tr>
                    <td>' . $i . '</td>
                <td>' . htmlspecialchars(ucwords(str_replace('_', ' ', isset($row->DateOption) ? $row->DateOption : ''))) . '</td>

                    <td>' . htmlspecialchars(isset($row->StartDate) ? $row->StartDate : '') . '</td>
                    <td>' . htmlspecialchars(isset($row->EndDate) ? $row->EndDate : '') . '</td>
                    <td>' . htmlspecialchars(isset($row->Layout) ? $row->Layout : '') . '</td>
                    <td>' . htmlspecialchars(isset($row->Output) ? $row->Output : '') . '</td>
                    <td>
                        <button type="button" class="btn btn-link p-0 btnEdit" data-id="' . htmlspecialchars(isset($row->ProformaID) ? $row->ProformaID : '') . '">
                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                        </button>
                        <button type="button" class="btn btn-link p-0 btnDelete" data-id="' . htmlspecialchars(isset($row->ProformaID) ? $row->ProformaID : '') . '">
                            <i class="mdi mdi-trash-can font-size-16 text-danger"></i>
                        </button>
                    </td>
                </tr>';
                $i++;
            }
        } else {
            $htmlResponse .= '<tr><td colspan="7" class="text-center">No records found</td></tr>';
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
        "msg"    => $msg ?: $error,
        "data"   => $data
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "msg" => "Invalid methodType"]);
}
