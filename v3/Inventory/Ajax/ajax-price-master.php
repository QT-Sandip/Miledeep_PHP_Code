<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// header('Content-Type: application/json');

$api = API_PRICE_MASTER;

$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';

if ($methodType == 'save') {

    $price_db_id = $_POST['price_db_id'];

    $bodyContent = array(
        "PriceMaster_id" => $price_db_id,
        "item_id" => $_POST["item_id"],
        "vendor_id" => $_POST["vendor_id"],
        "from_date" => $_POST["from_date"],
        "units" => $_POST["units"],
        "unit_price" => $_POST["unit_price"],
        "narration" => $_POST["narration"],
        "approved_by" => $_POST["approved_by"],
    );

    $res = CallAPI($api, "POST", "", $bodyContent);
    // alert($res->Msg);
    // echo print_r($res);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;

} elseif ($methodType == 'getSingle') {

    $price_db_id = ($_POST['price_db_id']);

    $bodyContent = array(
        "PriceMaster_id" => $price_db_id,
        "methodType" => "singlePrice"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'delete') {

    $price_db_id = ($_POST['price_db_id']);

    $bodyContent = array(
        "PriceMaster_id" => $price_db_id,
    );
    $res = CallAPI($api, "DELETE", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'load') {

    $res = CallAPI($api, "GET", "", "");
    $prices = !empty($res->Data) ? $res->Data : [];
    $html = '<div class="card">
                        <div class="card-body">
                        <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>S No</th>
                            <th>General Item</th>
                            <th>Vendor</th>
                            <th>Effective Date</th>
                            <th>Units</th>
                            <th>Unit Price</th>
                            <th>Narration</th>
                            <th>Approved By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    foreach ($prices as $row) {
        $html .= '<tr>
                <td>' . $row->SNo . '</td>
                <td>' . $row->itemName . '</td>
                <td>' . $row->VendorName . '</td>
                <td>' . $row->FromDate . '</td>
                <td>' . $row->Units . '</td>
                <td>' . $row->UnitPrice . '</td>
                 <td>' . $row->Narration . '</td>
                <td>' . $row->ApprovedBy . '</td>
                <td>
                     <button type="button" id="btnEdit"  name="btnEdit" data-id="' . $row->PriceMasterId . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                        </button>
                                        <button type="button" id="btnDelete" name="btnDelete" data-id="' . $row->PriceMasterId . '" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                        </button>
                </td>
            </tr>';
    }
    $html .= '</tbody></table></div></div>';
    echo $html;
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
?>