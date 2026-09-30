<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// header('Content-Type: application/json');

$api = API_INDENT_DETAILS;
$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';

if ($methodType == 'save') {

    $indent_id = isset($_POST['indent_id']) ? intval($_POST['indent_id']) : 0;

    $isUpdate = ($indent_id > 0);

    $bodyContent = [
        "pdate" => $_POST["pdate"],
        "doc_no" => $_POST["doc_no"],
        "department" => $_POST["department"],
        "narration" => $_POST["narration"],
        "breakUp" => [],
        "methodType" => $isUpdate ? 'update' : 'save'
    ];

    if ($isUpdate) {
        // THE ONLY KEY API NEEDS
        $bodyContent['IndentDetailsId'] = $indent_id;
    }

    $items = $_POST['item'];
    $qtys = $_POST['requested_qty'];
    $purposes = $_POST['purpose'];
    $remarks = $_POST['remarks'];
    $breakupIds = $_POST['breakup_id'];
    // $existingAttachments = isset($_POST['existing_attachment']) ? $_POST['existing_attachment'] : [];

    // for ($i = 0; $i < count($items); $i++) {
    //     if (empty($items[$i]))
    //         continue;

    //     $attachmentPath = isset($existingAttachments[$i]) ? $existingAttachments[$i] : '';

    //     if (!empty($_FILES['attachment']['name'][$i])) {
    //         $folder = $_SERVER['DOCUMENT_ROOT'] . "/uploads/indent/";
    //         if (!file_exists($folder))
    //             mkdir($folder, 0777, true);

    //         $filename = time() . "_" . uniqid() . "_" . $_FILES['attachment']['name'][$i];
    //         move_uploaded_file($_FILES['attachment']['tmp_name'][$i], $folder . $filename);
    //         $attachmentPath = "uploads/indent/" . $filename;
    //     }

    //     // IMPORTANT:
    //     $BreakUpId = (!empty($breakupIds[$i]) && $breakupIds[$i] != "null")
    //         ? intval($breakupIds[$i])
    //         : 0;

    //     $bodyContent['breakUp'][] = [
    //         "BreakUpId" => $BreakUpId, // <-- THIS KEEPS OLD ROW UPDATED
    //         "item" => $items[$i],
    //         "received_qty" => $qtys[$i],
    //         "purpose" => $purposes[$i],
    //         "remark" => $remarks[$i],
    //         "attachment" => $attachmentPath
    //     ];
    // }

  $existingAttachments = isset($_POST['existing_attachment']) ? $_POST['existing_attachment'] : array();

    for ($i = 0; $i < count($items); $i++) {

        if (empty($items[$i])) continue;

        // ✅ existing attachments
        $existing = array();
        if (isset($existingAttachments[$i]) && $existingAttachments[$i] != '') {
            $existing = explode(',', $existingAttachments[$i]);
        }

        $uploadedPaths = array();

        // ✅ check if files exist for this row
        if (isset($_FILES['attachment']['name'][$i]) && is_array($_FILES['attachment']['name'][$i])) {

            $folder = $_SERVER['DOCUMENT_ROOT'] . "/uploads/indent/";
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            foreach ($_FILES['attachment']['name'][$i] as $j => $fileName) {

                // skip empty file inputs
                if (!isset($fileName) || $fileName == '') continue;

                $tmpName = $_FILES['attachment']['tmp_name'][$i][$j];

                // extra safety check
                if (!is_uploaded_file($tmpName)) continue;

                $newFileName = time() . "_" . uniqid() . "_" . basename($fileName);

                if (move_uploaded_file($tmpName, $folder . $newFileName)) {
                    $uploadedPaths[] = "uploads/indent/" . $newFileName;
                }
            }
        }

        // ✅ merge existing + new
        $allAttachments = array_merge($existing, $uploadedPaths);

        // remove empty values (important)
        $allAttachments = array_filter($allAttachments);

        $attachmentPath = implode(',', $allAttachments);

        // ✅ breakup id logic
        $BreakUpId = 0;
        if (!empty($breakupIds[$i]) && $breakupIds[$i] != "null") {
            $BreakUpId = intval($breakupIds[$i]);
        }

        $bodyContent['breakUp'][] = array(
            "BreakUpId" => $BreakUpId,
            "item" => $items[$i],
            "received_qty" => $qtys[$i],
            "purpose" => isset($purposes[$i]) ? $purposes[$i] : '',
            "remark" => isset($remarks[$i]) ? $remarks[$i] : '',
            "attachment" => $attachmentPath
        );
    }

    $res = CallAPI($api, "POST", "", $bodyContent);
    echo json_encode($res);
    exit;
} elseif ($methodType == 'updateStatus') {

    $indentId = ($_POST['indentId']);

    $bodyContent = array(
        "indentId" => $indentId,
        "methodType" => "updateStatus"
    );
    $res = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'getSingle') {

    $price_db_id = ($_POST['price_db_id']);

    $bodyContent = array(
        "IndentDetails_id" => $price_db_id,
        "methodType" => "singlePrice"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'delete') {

    $price_db_id = ($_POST['price_db_id']);

    $bodyContent = array(
        "IndentDetails_id" => $price_db_id,
    );
    $res = CallAPI($api, "DELETE", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'load') {

    $res = CallAPI($api, "GET", "", "");
    $gstDetails = !empty($res->Data) ? $res->Data : [];
    $html = '<div class="card">
                        <div class="card-body">
                        <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>S No</th>
                            <th>Doc No</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    foreach ($gstDetails as $row) {
        $html .= '<tr>
                <td>' . $row->SNo . '</td>
                <td>' . $row->DocNo . '</td>
                 <td>' . date('d-m-Y', strtotime($row->Date)) . '</td>

                <td>
                     <button type="button" id="btnEdit"  name="btnEdit" data-id="' . $row->IndentDetailsId . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                        </button>
                                        <button type="button" id="btnDelete" name="btnDelete" data-id="' . $row->IndentDetailsId . '" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                        </button>

                                      <button type="button" class="btn btn-link p-0 btnViewIndent" data-id="' . $row->IndentDetailsId . '">
    <i class="mdi mdi-eye font-size-16 text-info"></i>
</button>



                </td>
            </tr>';
    }
    $html .= '</tbody></table></div></div>';
    echo $html;
} elseif ($methodType == 'itemDetails') {

    $item_id = ($_POST['item_id']);
    $effectiveDate = date('Y-m-d', strtotime($_POST['effective_date']));

    $bodyContent = array(
        "GeneralItemId" => $item_id,
        "EffectiveDate" => $effectiveDate,
        "methodType" => "itemDetails"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'availableData') {

    $item_id = ($_POST['item_id']);

    $bodyContent = array(
        "GeneralItemId" => $item_id,
        "methodType" => "availableData"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'view') {
    $indent_id = isset($_POST['indent_id']) ? $_POST['indent_id'] : 0;

    $bodyContent = array(
        "methodType" => "view",
        "indent_id" => $indent_id
    );

    // Call the same API you use for save/load/delete
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
