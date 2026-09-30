<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// header('Content-Type: application/json');

$api = API_PO_GS;

$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';

if ($methodType == 'save') {

    $indents = $_POST['breakup_id'];

    $items = $_POST['item'];
    $received_qtys = $_POST['requested_qty'];
    $discount_percs = $_POST['discount_perc'];
    $disp_dates = $_POST['disp_date'];
    $remarks = $_POST['remarks'];
    $pur_reqs = $_POST['pur_req'];

    $breakUpData = [];

    $count = count($items);

    for ($i = 0; $i < $count; $i++) {
        if (empty($items[$i])) continue;

        $breakUpData[] = [
            'item'         => $items[$i],
            'indent'       => $indents[$i],
            'received_qty' => $received_qtys[$i],
            'pur_req'      => $pur_reqs[$i],
            'discount_perc'      => $discount_percs[$i],
            'disp_date'      => $disp_dates[$i],
            'remark'       => $remarks[$i]
        ];
    }


    $bodyContent = array(
        "pdate" => $_POST["pdate"],
        "doc_no" => $_POST["doc_no"],
        "code" => $_POST["code"],
        "supplier" => $_POST["vendor_id"],
        "narration" => $_POST["narration"],
        "status" => $_POST["status"],
        "payment_terms" => $_POST["payment_terms"],
        "is_igst" => $_POST["is_igst"],
        "freight_type" => $_POST["freight_type"],
        "sub_delivery" => $_POST["sub_delivery"],
        "po_id" => $_POST['po_id'],
        "breakUp" => $breakUpData
    );

    $res = CallAPI($api, "POST", "", $bodyContent);
    // alert($res->Msg);
    // echo print_r($res);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'update') {

    $breakUpData = [];

    foreach ($_POST['item'] as $i => $item) {
        $breakUpData[] = [
            'breakup_id'   => $_POST['breakup_id'][$i],
            'item'         => $item,
            'received_qty' => $_POST['requested_qty'][$i],
            'pur_req'      => $_POST['pur_req'][$i],
            'discount_perc' => $_POST['discount_perc'][$i],
            'disp_date'    => $_POST['disp_date'][$i],
            'remark'       => $_POST['remarks'][$i]
        ];
    }


    $bodyContent = [
        "methodType"    => "update",
        "poId"          => $_POST['po_id'],

        // IMPORTANT: master fields must also be sent!
        "pdate"         => $_POST["pdate"],
        "doc_no"        => $_POST["doc_no"],
        "code"          => $_POST["code"],
        "supplier"      => $_POST["vendor_id"],
        "narration"     => $_POST["narration"],
        "status"        => $_POST["status"],
        "payment_terms" => $_POST["payment_terms"],
        "is_igst"       => $_POST["is_igst"],
        "freight_type"  => $_POST["freight_type"],
        "sub_delivery"  => $_POST["sub_delivery"],

        "breakUp"       => $breakUpData
    ];

    $res = CallAPI($api, "POST", "", $bodyContent);
    echo json_encode($res);
    exit;
} elseif ($methodType == 'getSingle') {

    // $price_db_id = ($_POST['price_db_id']);

    // $bodyContent = array(
    //     "GsProformaOrder_id" => $price_db_id,
    //     "methodType" => "singlePrice"
    // );


    $po_id = $_POST['po_id'];

    $bodyContent = array(
        "GsProformaOrder_id" => $po_id,
        "methodType" => "singlePrice"
    );

    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'delete') {

    $price_db_id = ($_POST['price_db_id']);

    $bodyContent = array(
        "GsProformaOrder_id" => $price_db_id,
    );
    $res = CallAPI($api, "DELETE", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'view') {
    $po_id = $_POST['po_id'];

    $bodyContent = array(
        "methodType" => "view",
        "GsProformaOrderId" => $po_id
    );

    $res = CallAPI($api, "GET", "", $bodyContent);


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
                            <th>Code</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    foreach ($gstDetails as $row) {
        $html .= '<tr>
                <td>' . $row->SNo . '</td>
                <td>' . $row->DocNo . '</td>
                <td>' . $row->Code . '</td>
                <td>' . date('d-m-Y', strtotime($row->Date)) . '</td>
                <td>' . $row->Supplier. '</td>
                <td>
                     <button type="button" id="btnEdit"  name="btnEdit" data-id="' . $row->GsProformaOrderId . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                        </button>
                                        <button type="button" id="btnDelete" name="btnDelete" data-id="' . $row->GsProformaOrderId . '" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i> </button>
                                      <button type="button" id="btnView" name="btnView" data-id="' . $row->GsProformaOrderId . '" class="btn btn-link p-0 btnView">
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
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
