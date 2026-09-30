<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// header('Content-Type: application/json');

$api = API_PM_PURCHASE_ORDER;

$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';


if ($methodType == 'save') {

    $indents = $_POST['breakup_id'];

    $pros = $_POST['pro'];
    $a_dates = $_POST['a_date'];
    $txtVarietyIDs = $_POST['txtVarietyID'];
    $packing_styles = $_POST['packing_style'];
    $brands = $_POST['brand'];
    $grades = $_POST['grade'];
    $freezings = $_POST['freezing'];
    $weight = $_POST['weight'];
    $size = $_POST['size'];
    $availqty=$_POST['availqty'];
    $proqtys = $_POST['pro-qty'];
    $disp_dates = $_POST['disp_date'];
    $codes = $_POST['code'];


    $breakUpData = [];

    $count = count($txtVarietyIDs);

    for ($i = 0; $i < $count; $i++) {
        if (empty($txtVarietyIDs[$i])) continue;

        $breakUpData[] = [
            'indent' => $indents[$i],
            'pro'         => $pros[$i],
            'a_date'       => $a_dates[$i],
            'txtVarietyID' => $txtVarietyIDs[$i],
            'packing_style'      => $packing_styles[$i],
            'brand'      => $brands[$i],
            'grade'      => $grades[$i],
            'size'       => $size[$i],
            'availqty'  => $availqty[$i],
            'freezing'   => $freezings[$i],
            'weight'     => $weight[$i],
            'proqty'      => $proqtys[$i],
            'disp_date'   => $disp_dates[$i],
            'code'       => $codes[$i]
        ];
    }

    $bodyContent = array(
        "pdate" => $_POST["pdate"],
        "doc_no" => $_POST["doc_no"],
        "supplier" => $_POST["vendor_id"],
        "narration" => $_POST["narration"],
        "payment_terms" => $_POST["payment_terms"],
        "Freight" => $_POST["Freight"],
        "freight_type" => $_POST["freight_type"],
        "subject" => $_POST["subject"],
        "poId" => $_POST['po_id'],
        "status" => $_POST["status"],
        "breakUp" => $breakUpData
    );

    $res = CallAPI($api, "POST", "", $bodyContent);
    // alert($res->Msg);
    // echo print_r($res);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;

}


elseif ($methodType == 'update') {

    $breakUpData = json_decode($_POST['breakupData'], true) ;

    $bodyContent = [
        "pdate"        => $_POST["pdate"],
        "doc_no"       => $_POST["doc_no"],
        "supplier"     => $_POST["vendor_id"],
        "narration"    => $_POST["narration"],
        "payment_terms"=> $_POST["payment_terms"],
        "Freight"      => $_POST["Freight"],
        "freight_type" => $_POST["freight_type"],
        "subject"      => $_POST["subject"],
        "poId"         => $_POST['po_id'],  // important for update
        "status"       => $_POST["status"],
        "breakUp"      => $breakUpData
    ];

    // Call API for update
    // $res = CallAPI($api, "PUT", "", $bodyContent); // or POST if your API uses POST for update
    $res = CallAPI($api, "POST", "", $bodyContent);


    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}


// elseif ($methodType == 'getSingle') {

//     $PmPurchaseOrder_id = $_POST['PmPurchaseOrder_id'] ?? '';

//     if (empty($PmPurchaseOrder_id)) {
//         echo json_encode([
//             "status" => "fail",
//             "Msg" => "Purchase Order ID missing",
//             "Data" => []
//         ]);
//         exit;
//     }

//     $res = CallAPI($api, "GET", [
//         "PmPurchaseOrderId" => $PmPurchaseOrder_id
//     ], "");

//     if (empty($res->Data) || !is_array($res->Data)) {
//         echo json_encode([
//             "status" => "fail",
//             "Msg" => "No Purchase Order found",
//             "Data" => []
//         ]);
//         exit;
//     }

//     // Filter rows for this PO
//     $breakups = array_filter($res->Data, function($row) use ($PmPurchaseOrder_id) {
//         return ($row->PmPurchaseOrderId ?? '') == $PmPurchaseOrder_id;
//     });

//     if (empty($breakups)) {
//         echo json_encode([
//             "status" => "fail",
//             "Msg" => "No breakup data found for this PO",
//             "Data" => []
//         ]);
//         exit;
//     }

//     // Header fields (from first row)
//     $firstRow = reset($breakups);

//     // Build flat Data object
//     $data = [
//         "PoID"           => $firstRow->PoID ?? '',
//         "ProformaID"     => $firstRow->ProformaID ?? '',
//         "PoDate"         => $firstRow->PoDate ?? '',
//         "Supplier"       => $firstRow->Supplier ?? '',
//         "PaymentTerms"   => $firstRow->PaymentTerms ?? '',
//         "FreightType"    => $firstRow->FreightType ?? '',
//         "FreightCharges" => $firstRow->FreightCharges ?? '',
//         "Subject"        => $firstRow->Subject ?? '',
//         "Narration"      => $firstRow->Narration ?? '',
//         // flatten breakup array directly
//         "breakUp"        => array_map(function($row) {
//             return [
//                 "BreakUpID"     => $row->BreakUpID ?? 0,
//                 "PoID"          => $row->PoID ?? '',
//                 "ProformaID"    => $row->ProformaID ?? '',
//                 "ApprovedDate"  => $row->ApprovedDate ?? '',
//                 "VarietyID"     => $row->VarietyID ?? '',
//                 "PackingStyle"  => $row->PackingStyle ?? '',
//                 "Brand"         => $row->Brand ?? '',
//                 "ProQty"        => $row->ProQty ?? 0,
//                 "DispacthedDate"=> $row->DispacthedDate ?? '',
//                 "PlantCode"     => $row->PlantCode ?? ''
//             ];
//         }, array_values($breakups))
//     ];

//     echo json_encode([
//         "status"       => "success",
//         "Msg"          => "Success",
//         "Data"         => $data,
//         "EditAccess"   => true,
//         "DeleteAccess" => true,
//         "EditLink"     => "#",
//         "Error"        => null
//     ]);
//     exit;
// }


elseif ($methodType == 'getSingle') {

    $po_id = $_POST['PmPurchaseOrder_id']; // received from AJAX

    if (empty($po_id)) {
        echo json_encode([
            "status" => "fail",
            "Msg" => "Purchase Order ID missing",
            "Data" => []
        ]);
        exit;
    }

    // Prepare the request body exactly as the API expects
    $bodyContent = [
        "PmPurchaseOrder_id" => $po_id,  // matches controller method param
        "methodType" => "getSingle"
    ];

    // Call the "API" (could be internal function instead of external API)
    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}
















elseif ($methodType == 'delete') {

    $PmPurchaseOrder_id = $_POST['PmPurchaseOrder_id'];

    $bodyContent = array(
        "PmPurchaseOrder_id" => $PmPurchaseOrder_id
    );

    $res = CallAPI($api, "DELETE", "", $bodyContent);

    echo json_encode($res);
    exit;
}
 elseif ($methodType == 'load') {

    

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
                            <th>Vendor Account </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    foreach ($gstDetails as $row) {
        $html .= '<tr>
                <td>' . $row->SNo . '</td>
                <td>' . $row->ProformaID . '</td>
                <td>' . $row->Date . '</td>
                <td>' . $row->Supplier . '</td>
                <td>
                     <button 
    type="button" 
    class="btn btn-link p-0 btnEdit"
    data-id="' . $row->PmPurchaseOrderId . '">
    <i class="mdi mdi-pencil font-size-16 text-primary"></i>
</button>



                                       <button 
    type="button" 
    class="btn btn-link p-0 deleteBrkUp btnDelete"
    data-id="' . $row->PmPurchaseOrderId . '">
    <i class="mdi mdi-delete font-size-16 text-danger"></i>
</button>


 <button 
        type="button"
        class="btn btn-link p-0 btnViewPO"
        data-id="' . $row->PmPurchaseOrderId . '">
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



elseif ($methodType == 'viewPurchaseOrder') {

    $PmPurchaseOrderId = $_POST['PmPurchaseOrderId'];

    if (empty($PmPurchaseOrderId)) {
        echo json_encode([
            "status" => "fail",
            "Msg" => "Purchase Order ID missing",
            "Data" => []
        ]);
        exit;
    }

    $bodyContent = [
        "methodType" => "view",
        "PmPurchaseOrderId" => $PmPurchaseOrderId
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