<?php


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// header('Content-Type: application/json');
$api = API_GOODS_RECEIPT_NOTE_PACKAGING;
$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';
if ($methodType == 'save') {
    $indents = $_POST['breakup_id'];

    $received_qtys = $_POST['received_qty'];
    $pros = $_POST['pro'];
    $attachments = $_POST['attachment'];
    $purposes = $_POST['purpose'];
    $remarks = $_POST['remarks'];
    $order_types = $_POST['order_type'];
    $txtVarietys = $_POST['txtVariety'];
    $txtVarietyIDs = $_POST['txtVarietyID'];
    $packing_styles = $_POST['packing_style'];
    $brands = $_POST['brand'];
    $grades = $_POST['grade'];
    $freezings = $_POST['freezing'];
    $sizes = $_POST['size'];

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
            'indent'       => $indents[$i],
            'received_qty' => $received_qtys[$i],
            'pro'     => $pros[$i],
            'purpose'      => $purposes[$i],
            'remark'       => $remarks[$i],
            'order_type'     => $order_types[$i],
            'txtVariety'      => $txtVarietys[$i],
            'txtVarietyId'      => $txtVarietyIDs[$i],
            'packing_style'       => $packing_styles[$i],
            'grade'       => $grades[$i],
            'brand'       => $brands[$i],
            'freezing'       => $freezings[$i],
            'size'       => $sizes[$i],
        ];
    }


    $bodyContent = array(
        "note_id" => $_POST["price_db_id"],
        "po_no" => $_POST["po_no"],
        "pdate" => $_POST["pdate"],
        "doc_no" => $_POST["doc_no"],
        "supplier" => $_POST["vendor_id"],
        "invoice_no" => $_POST["invoice_no"],
        "invoice_date" => $_POST["invoice_date"],
        "isgst" => $_POST["isgst"],
        "invoice_attachment" => $_POST["i_attachment"],
        "freight" => $_POST["freight"],
        "creditnote" => $_POST["creditnote"],
        "narration" => $_POST["narration"],
        "status" => $_POST["status"],
        "grade" => $_POST["grade"],
        "freezing" => $_POST["freezing"],
        "size" => $_POST["size"],
        "breakUp" => $breakUpData
    );

    // echo print_r($bodyContent);exit;

    $res = CallAPI($api, "POST", "", $bodyContent);
    // alert($res->Msg);
    // echo print_r($res);exit;
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

// elseif ($methodType == 'update') {

//     $indents = $_POST['breakup_id'] ;
//     $received_qtys = $_POST['received_qty'] ;
//     $pros = $_POST['pro'] ;
//     $attachments = $_POST['attachment'] ;
//     $purposes = $_POST['purpose'] ;
//     $remarks = $_POST['remarks'] ;
//     $order_types = $_POST['order_type'] ;
//     $txtVarietys = $_POST['txtVariety'] ;
//     $txtVarietyIDs = $_POST['txtVarietyID'] ;
//     $packing_styles = $_POST['packing_style'] ;
//     $brands = $_POST['brand'] ;

//     $breakUpData = [];

//     $count = count($txtVarietys);

//     for ($i = 0; $i < $count; $i++) {
//         if (empty($txtVarietys[$i])) continue;

//         // Same file upload logic as SAVE
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
//             'breakup_id'    => $indents[$i],
//             'received_qty'  => $received_qtys[$i],
//             'pro'           => $pros[$i],
//             'purpose'       => $purposes[$i],
//             'remarks'       => $remarks[$i],       // ✅ rename
//             'order_type'    => $order_types[$i],
//             'txtVariety'    => $txtVarietys[$i],
//             'txtVarietyID'  => $txtVarietyIDs[$i], // ✅ exact case
//             'packing_style' => $packing_styles[$i],
//             'brand'         => $brands[$i],
//             'attachment'    => $attachmentPath
//         ];
//     }

//     $bodyContent = array(
//         "note_id" => $_POST["price_db_id"],
//         "po_no" => $_POST["po_no"],
//         "pdate" => $_POST["pdate"],
//         "doc_no" => $_POST["doc_no"],
//         "supplier" => $_POST["vendor_id"],
//         "invoice_no" => $_POST["invoice_no"],
//         "invoice_date" => $_POST["invoice_date"],
//         "isgst" => $_POST["isgst"],
//         "invoice_attachment" => $_POST["i_attachment"],
//         "freight" => $_POST["freight"],
//         "creditnote" => $_POST["creditnote"],
//         "narration" => $_POST["narration"],
//         "breakUp" => $breakUpData
//     );

//     $res = CallAPI($api, "POST", "", $bodyContent);


//     header('Content-Type: application/json');
//     echo json_encode($res);
//     exit;
// }


elseif ($methodType == 'update') {

    // --- Fetch all arrays safely ---
    $indents       = $_POST['breakup_id'] ;
    $received_qtys  = $_POST['received_qty'] ;
    $pros           = $_POST['pro'] ;
    $attachments    = $_POST['attachment'] ;
    $purposes       = $_POST['purpose'] ;
    $remarks        = $_POST['remarks'] ;
    $order_types    = $_POST['order_type'] ;
    $txtVarietys    = $_POST['txtVariety'] ;
    $txtVarietyIDs  = $_POST['txtVarietyID'] ;
    $packing_styles = $_POST['packing_style'] ;
    $grades         = $_POST['grade'] ;
    $brands         = $_POST['brand'] ;
    $freezings      = $_POST['freezing'] ;
    $sizes          = $_POST['size'] ;


    $breakUpData = [];

    $count = count($txtVarietys);
    foreach ($txtVarietys as $i => $variety) {

        if (empty($variety)) continue;

        $breakupId = isset($_POST['breakup_id'][$i]) && $_POST['breakup_id'][$i] !== ''
            ? (int)$_POST['breakup_id'][$i]
            : 0;

        // --- Handle file upload ---
        $attachmentPath = $_POST['existing_attachment'][$i] ;

        if (!empty($_FILES['attachment']['name'][$i])) {
            $filename = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $_FILES['attachment']['name'][$i]);
            $tmpname  = $_FILES['attachment']['tmp_name'][$i];
            $folder   = $_SERVER['DOCUMENT_ROOT'] . "/uploads/indent/";

            if (!file_exists($folder)) mkdir($folder, 0777, true);

            $newFileName = time() . "_" . uniqid() . "_" . $filename;
            move_uploaded_file($tmpname, $folder . $newFileName);

            $attachmentPath = "uploads/indent/" . $newFileName;
        }

        $breakUpData[] = [
            'BreakUpId'     => $breakupId,
            'received_qty'  => $received_qtys[$i] ,
            'pro'           => $pros[$i] ,
            'purpose'       => $purposes[$i] ,
            'remarks'       => $remarks[$i] ,
            'order_type'    => $order_types[$i] ,
            'txtVariety'    => $variety,
            'txtVarietyID'  => $txtVarietyIDs[$i] ,
            'packing_style' => $packing_styles[$i] ,
            'brand'         => $brands[$i] ,
            'grade' => $grades[$i] ,
            'freezing' => $freezings[$i] ,
            'size' => $sizes[$i] ,
            'attachment'    => $attachmentPath
        ];
    }


    // --- Prepare body for API ---
    $bodyContent = [
        "note_id"            => $_POST["price_db_id"] ,
        "po_no"              => $_POST["po_no"] ,
        "pdate"              => $_POST["pdate"] ,
        "doc_no"             => $_POST["doc_no"] ,
        "supplier"           => $_POST["vendor_id"] ,
        "invoice_no"         => $_POST["invoice_no"] ,
        "invoice_date"       => $_POST["invoice_date"] ,
        "isgst"              => $_POST["isgst"] ,
        "invoice_attachment" => $_POST["i_attachment"] ,
        "freight"            => $_POST["freight"] ,
        "creditnote"         => $_POST["creditnote"] ,
        "narration"          => $_POST["narration"] ,
        "status"             => $_POST["status"] ,
        "grade"              => $_POST["grade"] ,
        "freezing"           => $_POST["freezing"] ,
        "size"               => $_POST["size"] ,
        "breakUp"            => $breakUpData
    ];

    // --- Debug: log payload ---
    // error_log(print_r($bodyContent, true));

    $res = CallAPI($api, "POST", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'getSingle') {

    $noteId = $_POST['price_db_id'] ;

    if (empty($noteId)) {
        echo json_encode([
            "status" => "fail",
            "Msg"    => "Goods Receipt Note ID missing",
            "Data"   => []
        ]);
        exit;
    }

    // ✅ Prepare request body exactly as API expects
    $bodyContent = [
        "PmGoodsReceiptNoteId" => (int)$noteId,
        "methodType"           => "getSingle"
    ];

    // ✅ Call API (same pattern as PO)
    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'delete') {
    $PmGoodsReceiptNote_id = isset($_POST['PmGoodsReceiptNote_id']) ? $_POST['PmGoodsReceiptNote_id'] : '';

    // Validate input
    if (empty($PmGoodsReceiptNote_id)) {
        echo json_encode([
            "status" => "fail",
            "Msg" => "Item ID not found",
            "Error" => "Missing ID"
        ]);
        exit;
    }

    $bodyContent = [
        "PmGoodsReceiptNote_id" => $PmGoodsReceiptNote_id,
    ];

    $res = CallAPI($api, "DELETE", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'load') {
    $res = CallAPI($api, "GET", "", "");
    $GoodsReceiptNoteDetails = !empty($res->Data) ? $res->Data : [];
    // echo print_r($GoodsReceiptNoteDetails);exit;
    $html = '
     <div class="card">
      <div class="card-body">
       <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>S No</th>
                            <th>Vendor</th>
                            <th>Date</th>
                            <th>Po No</th>
                            <th>Invoice No</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    foreach ($GoodsReceiptNoteDetails as $row) {
        $html .= '<tr>
                <td>' . $row->SNo . '</td>
                <td>' . $row->VendorName . '</td>
                <td>' . $row->Date . '</td>
                <td>' . $row->PoID . '</td>
                <td>' . $row->InvoiceNo . '</td>
                <td>
                  <button type="button" 
            class="btn btn-link p-0 btnEdit"
            data-price_db_id="' . $row->PmGoodsReceiptNoteId . '">
            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
          </button>


                                      <button type="button" class="btn btn-link p-0 deleteBrkUp btnDelete"
    data-id="' . $row->PmGoodsReceiptNoteId . '">
    <i class="mdi mdi-delete font-size-16 text-danger"></i>
</button>


<button type="button"
        class="btn btn-link p-0 btnViewGRN"
        data-id="' . $row->PmGoodsReceiptNoteId . '">
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
} elseif ($methodType == 'poData') {

    $item_id = ($_POST['po_id']);

    $bodyContent = array(
        "PoId" => $item_id,
        "itemId" => $_POST['itemId'],
        "methodType" => "poData"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'viewGRN') {

    $PmGoodsReceiptNoteId = $_POST['PmGoodsReceiptNoteId'] ;

    if (empty($PmGoodsReceiptNoteId)) {
        echo json_encode([
            "status" => "fail",
            "Msg" => "Goods Receipt Note ID missing",
            "Data" => []
        ]);
        exit;
    }

    // Prepare body content to send to the API
    $bodyContent = [
        "methodType" => "view",
        "PmGoodsReceiptNote_id" => $PmGoodsReceiptNoteId
    ];

    // Call central API (same as Material Issue)
    $res = CallAPI($api, "GET", "", $bodyContent);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
