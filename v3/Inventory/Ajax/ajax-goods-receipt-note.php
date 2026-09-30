<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
// header('Content-Type: application/json');
$api = API_GOODS_RECEIPT_NOTE;
$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';
if ($methodType == 'save') {
    $items = $_POST['item'] ;
    $received_qtys = $_POST['received_qty'] ;
    $locations = $_POST['location'] ;
    $purposes = $_POST['purpose'] ;
    $remarks = $_POST['remarks'] ;
    $indents = $_POST['breakup_id'] ;
    $old_received_qtys = $_POST['old_received_qty'] ;
    
    $breakUpData = [];
    $count = count($items);

    for ($i = 0; $i < $count; $i++) {
        $item = $items[$i] ;
        if ($item === '') continue;

        // Uploaded attachment
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
            'indent'       => $indents[$i] ,
            'item'         => (int)$item,
            'received_qty' => (float)($received_qtys[$i] ),
            'old_received_qty' => (float)($old_received_qtys[$i] ),
            'location'     => (int)($locations[$i] ),
            'purpose'      => $purposes[$i] ,
            'remark'       => $remarks[$i],
            'attachment'   => $attachmentPath
        ];
    }

    $bodyContent = [
        "note_id"      => $_POST['price_db_id'] ,
        // "note_id"      => $_POST['note_id'] ?? 0,

        "po_no"        => $_POST["po_no"],
        "PoId"         => $_POST["doc_no"] , 
        "supplier"     => $_POST["vendor_id"] ,
        "invoice_no"   => $_POST["invoice_no"] ,
        "invoice_date" => $_POST["invoice_date"] ,
        "code"         => $_POST["code"] ,
        "pdate"        => $_POST["pdate"] ,
        "narration"    => $_POST["narration"] ,
        "status"       => $_POST["status"] ,
        "freight"      => $_POST["freight"] ,
        "breakUp"      => $breakUpData
    ];

    $res = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

 elseif ($methodType == 'getSingle') {
    $grn_id = isset($_POST['GoodsReceiptNoteId']) ? $_POST['GoodsReceiptNoteId'] : 0;

    $bodyContent = array(
        "GoodsReceiptNoteId" => $grn_id,
        "methodType" => "getSingleGRN"  // or the correct method your API expects for full data
    );

    $res = CallAPI($api, "GET", "", $bodyContent);

   

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;




} elseif ($methodType == 'delete') {
    $price_db_id = ($_POST['price_db_id']);
    $bodyContent = array(
        "GoodsReceiptNote_id" => $price_db_id,
    );
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
                            <th>Code</th>
                           
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
foreach ($GoodsReceiptNoteDetails as $row) {
    $html .= '<tr>
            <td>' . $row->SNo . '</td>
            <td>' . $row->VendorName . '</td>
            <td>' . date('d-m-Y', strtotime($row->Date)) . '</td>
            <td>' . $row->PoNo . '</td>
            <td>' . $row->InvoiceNo . '</td>
            <td>' . $row->Code . '</td>
          
            <td>
                 <button type="button" id="btnEdit"  name="btnEdit" data-id="' . $row->GoodsReceiptNoteId . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                        <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                    </button>
                                    <button type="button" id="btnDelete" name="btnDelete" data-id="' . $row->GoodsReceiptNoteId . '" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                        <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                    </button>
                                    <button type="button" class="btn btn-link p-0 btnView" data-id="' . $row->GoodsReceiptNoteId . '">
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


}

elseif ($methodType == 'view') {
    $grn_id = isset($_POST['GoodsReceiptNoteId']) ? $_POST['GoodsReceiptNoteId'] : 0;

    $bodyContent = array(
        "methodType" => "view",
        "GoodsReceiptNoteId" => $grn_id
    );

    // Debug log
    // error_log("Calling API with: " . print_r($bodyContent, true));

    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

// ===================== GRN REPORT LIST =====================
// elseif ($methodType == 'report') {

//     $doc_no    = $_POST['doc_no'] ?? '';
//     $from_date = $_POST['from_date'] ?? '';
//     $to_date   = $_POST['to_date'] ?? '';
//     $status    = $_POST['status'] ?? '';

//     $response = CallAPI($api, "GET", "", "");
//     $GRNList = $response->Data ?? [];
//     $sn = 1;

//     foreach ($GRNList as $rec) {

//         // Filter Doc No
//         if ($doc_no && stripos($rec->PoNo, $doc_no) === false) continue;

//         // Filter Status (ignore if status is empty or "All")
//         if ($status && strtolower($status) !== 'all') {
//             if (strcasecmp(trim($rec->Status), $status) != 0) continue;
//         }

//         // Filter Dates
//         $recDate = date('Y-m-d', strtotime($rec->Date));
//         if ($from_date && $recDate < $from_date) continue;
//         if ($to_date && $recDate > $to_date) continue;

//         echo "<tr>";
//         echo "<td>{$sn}</td>";
//         echo "<td>{$rec->PoNo}</td>";
//         echo "<td>{$recDate}</td>";
//         echo "<td>{$rec->VendorName}</td>";
//         echo "<td>{$rec->InvoiceNo}</td>";
//         echo "<td>{$rec->PoNo}</td>";
//         echo "<td>{$rec->Status}</td>";
//         echo "</tr>";

//         $sn++;
//     }

//     exit;
// }







else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
?>