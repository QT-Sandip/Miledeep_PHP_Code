<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// header('Content-Type: application/json');

$api = API_MATERIAL_ISSUE;

$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';

// if ($methodType == 'save') {

//      $indent_id = $_POST['indent_id'];
//     $indents = explode(',',$indent_id);

//     $items = $_POST['item'];
//     $received_qtys = $_POST['requested_qty'];
//     $attachments = $_POST['attachment'];
//     $purposes = $_POST['purpose'];
//     $remarks = $_POST['remarks'];
//     $uoms = $_POST['uom'];

//     $breakUpData = [];

//     $count = count($items);

//     for ($i = 0; $i < $count; $i++) {
//         if (empty($items[$i])) continue;

//         $attachmentPath = '';
//         if (isset($_FILES['attachment']['name'][$i]) && $_FILES['attachment']['name'][$i] != '') {
//             $filename = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $_FILES['attachment']['name'][$i]);
//             $tmpname = $_FILES['attachment']['tmp_name'][$i];
//             $folder = $_SERVER['DOCUMENT_ROOT'] . "/uploads/indent/";
//             if (!file_exists($folder)) mkdir($folder, 0777, true);
//             $newFileName = time() . "_" . uniqid() . "_" . $filename;
//             move_uploaded_file($tmpname, $folder . $newFileName);
//             $attachmentPath = "uploads/indent/" . $newFileName;
//         }

//         $breakUpData[] = [
//             'attachment'   => $attachmentPath,
//             'item'         => $items[$i],
//             'indent'       => $indents[$i],
//             'received_qty' => $received_qtys[$i],
//             'purpose'      => $purposes[$i],
//             'remark'       => $remarks[$i],
//             'uom'       => $uoms[$i]
//         ];
//     }


//     $bodyContent = array(
//         "po_no" => $_POST["po_no"],
//         "pdate" => $_POST["pdate"],
//         "doc_no" => $_POST["doc_no"],
//         "department" => $_POST["department"],
//         "location" => $_POST["location"],
//         "narration" => $_POST["narration"],
//         "breakUp" => $breakUpData
//     );

//     $res = CallAPI($api, "POST", "", $bodyContent);
//     // alert($res->Msg);
//     // echo print_r($res);
//     header('Content-Type: application/json');
//     echo json_encode($res);
//     exit;

// }


if ($methodType == 'save' || $methodType == 'update') {

    $indent_id   = $_POST['indent_id'] ; 
    $items       = $_POST['item'] ;
    $breakUpIds  = $_POST['breakUpId'] ;
    $received_qtys = $_POST['requested_qty'] ;
    $attachments   = $_FILES['attachment'] ;
    $purposes      = $_POST['purpose'] ;
    $remarks       = $_POST['remarks'] ;
    $uoms          = $_POST['uom'] ;

    $breakUpData = [];
    for ($i = 0; $i < count($items); $i++) {
        if (empty($items[$i])) continue;

        $attachmentPath = '';
        if (isset($attachments['name'][$i]) && $attachments['name'][$i] != '') {
            $filename = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $attachments['name'][$i]);
            $tmpname  = $attachments['tmp_name'][$i];
            $folder   = $_SERVER['DOCUMENT_ROOT'] . "/uploads/indent/";
            if (!file_exists($folder)) mkdir($folder, 0777, true);
            $newFileName = time() . "_" . uniqid() . "_" . $filename;
            move_uploaded_file($tmpname, $folder . $newFileName);
            $attachmentPath = "uploads/indent/" . $newFileName;
        }

        $breakUpData[] = [
            'BreakUpId'    => $breakUpIds[$i] , // important for update
            'attachment'   => $attachmentPath,
            'item'         => $items[$i],
            'received_qty' => $received_qtys[$i] ,
            'purpose'      => $purposes[$i] ,
            'remark'       => $remarks[$i] ,
            'uom'          => $uoms[$i] 
        ];
    }

   

    // normalize indent id
    $indent_id = !empty($indent_id) ? intval($indent_id) : null;

    $bodyContent = [
        "po_no"     => $_POST["po_no"] ,
        "pdate"     => $_POST["pdate"] ,
        "doc_no"    => $_POST["doc_no"] ,
        "department"=> $_POST["department"] ,
        "location"  => $_POST["location"] ,
        "narration" => $_POST["narration"] ,
        "breakUp"   => $breakUpData,
        "methodType"=> $methodType // tell API if this is 'save' or 'update'
    ];

    // For update, include both NoteId (existing API uses) and material_id (postMaterialIssue expects)
    if ($methodType === 'update' && $indent_id) {
        $bodyContent['NoteId']     = $indent_id;
        $bodyContent['material_id'] = $indent_id; // this ensures postMaterialIssue treats it as update
    }

    // Call API (backend must handle update when material_id/NoteId present)
    $res = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;

}

elseif ($methodType == 'getSingle') {

    $price_db_id = ($_POST['price_db_id']);

    $bodyContent = array(
        "MaterialIssue_id" => $price_db_id,
        "methodType" => "singlePrice"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}


elseif ($methodType == 'delete') {

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
                        <table id="tblDataTable" class="table table-hover table-bordered">
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

                                        <button type="button" id="btnView" name="btnView" data-id="' . $row->MaterialIssueId . '" class="btn btn-link p-0 btnView">
        <i class="mdi mdi-eye font-size-16 text-info"></i>
    </button>


                </td>
            </tr>';
    }
    $html .= '</tbody></table></div></div>';
    echo $html;
} else if($methodType == 'indents') {
    $bodyContent = array(
        "methodType" => "getIndents"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} else if($methodType == 'indentsBreakUp') {
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
} 



elseif ($methodType == 'view') {

    //  This is the new section for viewing a full Material Issue
    $noteId = $_POST['NoteId'];

    $bodyContent = [
        "NoteId" => $noteId,
        "methodType" => "view"
    ];

    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;

} 

else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
?>