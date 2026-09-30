<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// header('Content-Type: application/json');

$api = API_MATERIAL_ISSUE_PACKAGING;

$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';


if ($methodType == 'save') {

    $indent_id = $_POST['indent_id'];
    $indents = explode(',', $indent_id);

    $txtVarietys = $_POST['txtVariety'];
    $txtVarietyIDs = $_POST['txtVarietyID'];
    $received_qtys = $_POST['requested_qty'];
    $attachments = $_POST['attachment'];
    $purposes = $_POST['purpose'];
    $remarks = $_POST['remarks'];
    $locations = $_POST['location'];
    $packing_styles = $_POST['packing_style'];
    $brands = $_POST['brand'];
    $grades = $_POST['grade'];
    $freezings = $_POST['freezing'];

    $breakUpData = [];

    $count = count($txtVarietys);

    for ($i = 0; $i < $count; $i++) {
        if (empty($txtVarietys[$i])) continue;

        $attachmentPath = '';
        if (isset($_FILES['attachment']['name'][$i]) && $_FILES['attachment']['name'][$i] != '') {
            $filename = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $_FILES['attachment']['name'][$i]);
            $tmpname = $_FILES['attachment']['tmp_name'][$i];
            $folder = $_SERVER['DOCUMENT_ROOT'] . "/uploads/indent/";
            if (!file_exists($folder)) mkdir($folder, 0777, true);
            $newFileName = time() . "_" . uniqid() . "_" . $filename;
            move_uploaded_file($tmpname, $folder . $newFileName);
            $attachmentPath = "uploads/indent/" . $newFileName;
        }

        $breakUpData[] = [
            'attachment'   => $attachmentPath,
            'txtVariety'         => $txtVarietys[$i],
            'txtVarietyId'      => $txtVarietyIDs[$i],
            'indent'       => $indents[$i],
            'received_qty' => $received_qtys[$i],
            'purpose'      => $purposes[$i],
            'remark'       => $remarks[$i],
            'location'       => $locations[$i],
            'packing_style'       => $packing_styles[$i],
            'brand'       => $brands[$i],
            'grade'       => $grades[$i],
            'freezing'       => $freezings[$i],
        ];

    }


    $bodyContent = array(
        "indent_id" => $_POST["indent_id"] ,
        "po_no" => $_POST["po_no"],
        "pdate" => $_POST["pdate"],
        "doc_no" => $_POST["doc_no"],
        "pro" => $_POST["pro"],
        "narration" => $_POST["narration"],
        "breakUp" => $breakUpData
    );


    $res = CallAPI($api, "POST", "", $bodyContent);
    // alert($res->Msg);
    // echo print_r($res);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'update') {

    $MaterialIssueId = (int)$_POST['indent_id'];

    // Prepare all breakup rows
    $breakUpArr = [];
    $rowCount = count($_POST['location'] );
    for ($i = 0; $i < $rowCount; $i++) {
        $breakUpArr[] = [
            "BreakUpId" => (int)$_POST['BreakUpId'][$i],

            "txtVarietyId"  => $_POST['txtVarietyID'][$i],
            "received_qty"  => $_POST['requested_qty'][$i],
            "packing_style" => $_POST['packing_style'][$i],
            "brand"         => $_POST['brand'][$i],
            "grade"         => $_POST['grade'][$i],
            "freezing"      => $_POST['freezing'][$i],
            "location"      => $_POST['location'][$i],
            "purpose"       => $_POST['purpose'][$i],
            "remark"        => $_POST['remarks'][$i],
            "attachment"    => $attachmentPath 
        ];
    }

    // Send header + all breakup rows together
    $headerRes = CallAPI($api, "POST", "", [
        "methodType" => "update",
        "indent_id"  => $MaterialIssueId,
        "doc_no"     => $_POST['doc_no'],
        "pdate"      => $_POST['pdate'],
        "pro"        => $_POST['pro'],
        "narration"  => $_POST['narration'],
        "breakUp"    => $breakUpArr  // <<< all rows here
    ]);

    if (!$headerRes || $headerRes->status != "success") {
        echo json_encode([
            "status" => "error",
            "Msg"    => "Header or Breakup update failed",
            "api"    => $headerRes
        ]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "Msg"    => "Updated Successfully"
    ]);
    exit;
} elseif ($methodType == 'getSingle') {
    $materialIssueId = $_POST['MaterialIssue_id'] ;
    if ($materialIssueId == '') {
        echo json_encode([
            "status" => "error",
            "Msg" => "MaterialIssue_id missing",
            "Data" => []
        ]);
        exit;
    }

    // Prepare API request
    $bodyContent = [
        "MaterialIssue_id" => $materialIssueId,
        "methodType" => "singlePrice"
    ];

    $res = CallAPI($api, "GET", "", $bodyContent);

    if (!$res) {
        echo json_encode([
            "status" => "error",
            "Msg" => "API call failed",
            "Data" => []
        ]);
        exit;
    }

    // Grab the first record if API returns an array
    if (is_array($res->Data) && count($res->Data) > 0) {
        $data = $res->Data[0];
    } else {
        $data = $res->Data;
    }

    // Normalize breakup data for JS
    $normalizedBreakups = [];
    if (isset($data->breakUp) && !empty($data->breakUp)) {
        foreach ($data->breakUp as $item) {
            $normalizedBreakups[] = (object)[
                'BreakUpId'      => $item->BreakUpId ,
                'VarietyID'      => $item->VarietyID ,
                'VarietyName'    => $item->VarietyName ,
                'PackingStyleID' => $item->PackingStyleID ,
                'PackingStyle'   => $item->PackingStyle ,
                'BrandID'        => $item->BrandID ,
                'BrandName'      => $item->BrandName ,
                'Location'       => ($item->Location ? $item->Location : $item->Warehouse) ,
                'UOM'            => $item->UOM ,
                'Grade'          => $item->Grade ,
                'Freezing'       => $item->Freezing ,
                'AvailableQty'   => ($item->AvailableQty ? $item->AvailableQty : ($item->QuantityAvailable )),
                'ItemPrice'      => $item->ItemPrice ,
                'Batch'          => $item->Batch ,
                'RequestedQty'   => ($item->RequestedQty ? $item->RequestedQty : ($item->Quantity )),
                'BaseValue'      => $item->BaseValue ,
                'GST'            => $item->GST ,
                'InvoiceAmt'     => $item->InvoiceAmt ,
                'Purpose'        => $item->Purpose ,
                'Attachment'     => $item->Attachment ,
                'Remark'         => ($item->Remark ? $item->Remark : ($item->Remarks ))
            ];
        }
    }

    $data->breakUp = $normalizedBreakups;

    // Ensure header fields exist
    $data->DocNo = $data->DocNo ;
    $data->Date = $data->Date ;
    $data->Narration = $data->Narration ;
    $data->ProformaID = ($data->ProformaID ? $data->ProformaID : ($data->ProNo ));

    // Return JSON
    $res->Data = $data;
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'delete') {



    $price_db_id = ($_POST['price_db_id']);

    $bodyContent = array(
        "MaterialIssue_id" => $price_db_id,
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
                        <table id="tblDataTable" class="table table-hover">
                        <thead class="thead-light">
                        <tr>
                            <th>S No</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    foreach ($gstDetails as $row) {
        $html .= '<tr>
                <td>' . $row->SNo . '</td>
                <td>' . $row->Date . '</td>
                <td>
                     <button type="button" id="btnEdit"  name="btnEdit" data-id="' . $row->MaterialIssueId . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                        </button>
                                        <button type="button" id="btnDelete" name="btnDelete" data-id="' . $row->MaterialIssueId . '" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                        </button>

                                        <button type="button" id="btnView" name="btnView" data-id="' . $row->MaterialIssueId . '" class="btn btn-link p-0 btnViewMaterial">
    <i class="mdi mdi-eye font-size-16 text-info"></i>
</button>

                                        
                </td>
            </tr>';
    }
    $html .= '</tbody></table></div></div>';
    echo $html;
} else if ($methodType == 'indents') {
    $bodyContent = array(
        "methodType" => "getIndents"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} else if ($methodType == 'indentsBreakUp') {
    $indent_id = ($_POST['indent_id']);
    $bodyContent = array(
        "indent" => $indent_id,
        "methodType" => "getIndentsBreakUp"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
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
} elseif ($methodType == 'viewMaterial') {

    $MaterialIssueId = $_POST['MaterialIssueId'] ;

    if (empty($MaterialIssueId)) {
        echo json_encode([
            "status" => "fail",
            "Msg" => "Material Issue ID missing",
            "Data" => []
        ]);
        exit;
    }

    // Prepare body content to send to the API
    $bodyContent = [
        "methodType" => "view",
        "MaterialIssue_id" => $MaterialIssueId
    ];

    // Call your central API (like PO sample)
    $res = CallAPI($api, "GET", "", $bodyContent);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
