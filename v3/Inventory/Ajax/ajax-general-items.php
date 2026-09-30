<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';

$api = API_GENERAL_ITEMS;


// SAVE generalItem
if ($methodType === 'save') {

    $generalItem_id = ($_POST['generalItem_id']);

    $bodyContent = array(
        "generalItem_id" => $generalItem_id,
        "name" => $_POST["generalItem_name"],
        "code" => $_POST["generalItem_code"],
        "item_type" => $_POST["item_type"],
        "reorder_qty" => $_POST["reorder_qty"],
        // "valuation_method" => $_POST["valuation_method"],
        // "group" => $_POST["group"],
        "revision_date" => $_POST["revision_date"],
        "category" => $_POST["category"],
        // "item_make" => $_POST["item_make"],
        // "printer" => $_POST["printer"],
        // "sub_category" => $_POST["sub_category"],
        "description" => $_POST["description"],

        
        "image" => isset($_POST['item_image']) ? $_POST['item_image'] : null,
        // "peridhable_item" => $_POST["peridhable_item"],
        "default_base_unit" => $_POST["default_base_unit"],
        "default_sales_unit" => $_POST["default_sales_unit"],
        //"Salt" => "", //$_POST["txtSalt"],
        "default_purchase_unit" => $_POST["default_purchase_unit"],
        "weight" => $_POST["weight"],
        "height" => $_POST["height"],
        "width" => $_POST["width"],
        "length" => $_POST["length"],
        "standard_cost" => $_POST["standard_cost"],
        // "overhead_cost" => $_POST["overhead_cost"],
        // "indirect_cost" => $_POST["indirect_cost"],
        // "profit" => $_POST["profit"],
        "expiring_days" => $_POST["expiring_days"],
        // "gst" => $_POST["gst"],
        // "abc_value" => $_POST["abc_value"],
        // "abc_margin" => $_POST["abc_margin"],
        // "abc_revenue" => $_POST["abc_revenue"],
        // "abc_carying_cost" => $_POST["abc_carying_cost"],
        // "barcode1" => $_POST["barcode1"],
        // "barcode2" => $_POST["barcode2"],
        // "barcode3" => $_POST["barcode3"],
    );

    $res = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

// UPDATE generalItem
else if ($methodType === 'update') {
    $generalItem_id = intval($_POST['generalItem_id']);
    if ($generalItem_id <= 0) {
        echo json_encode(["status" => "error", "msg" => "Invalid generalItem ID"]);
        exit;
    }

    $bodyContent = array(
        "generalItem_id" => $generalItem_id,
        "name" => $_POST["name"],
        "code" => $_POST["code"],
        "item_type" => $_POST["item_type"],
        "reorder_qty" => $_POST["reorder_qty"],
        // "valuation_method" => $_POST["valuation_method"],
        // "group" => $_POST["group"],
        "revision_date" => $_POST["revision_date"],
        "category" => $_POST["category"],
        // "item_make" => $_POST["item_make"],
        // "printer" => $_POST["printer"],
        // "sub_category" => $_POST["sub_category"],
        "description" => $_POST["description"],
        "image" => $_POST["image"],
        // "peridhable_item" => $_POST["peridhable_item"],
        "default_base_unit" => $_POST["default_base_unit"],
        "default_sales_unit" => $_POST["default_sales_unit"],
        //"Salt" => "", //$_POST["txtSalt"],
        "default_purchase_unit" => $_POST["default_purchase_unit"],
        "weight" => $_POST["weight"],
        "height" => $_POST["height"],
        "width" => $_POST["width"],
        "length" => $_POST["length"],
        "standard_cost" => $_POST["standard_cost"],
        // "overhead_cost" => "overhead_cost",
        // "indirect_cost" => $_POST["indirect_cost"],
        // "profit" => $_POST["profit"],
        "expiring_days" => $_POST["expiring_days"],
        // "gst" => $_POST["gst"],
        // "abc_value" => $_POST["abc_value"],
        // "abc_margin" => $_POST["abc_margin"],
        // "abc_revenue" => $_POST["abc_revenue"],
        // "abc_carying_cost" => $_POST["abc_carying_cost"],
        // "barcode1" => $_POST["barcode1"],
        // "barcode2" => $_POST["barcode2"],
        // "barcode3" => "barcode3",
    );
    //    echo json_encode($bodyContent);
    //     exit;
    $res = CallAPI($api, "POST", "", $bodyContent);


    exit;
} elseif ($methodType == 'load') {

    $res = CallAPI($api, "GET", "", "");
    $Items = $res->Data;

    $html = '
     <div class="card">
      <div class="card-body">
       <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                    <tr>
                        <th>S No</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Item Type</th>
                       
                        <th>Category</th>
                        <th>Purchase Cost</th>
                        <th>Default Base Unit</th>
                        <th>Reorder Quantity</th>
                        <th>Expiring Days</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($Items as $item) {

        $html .= '<tr>
                <td>' . $item->SNo . '</td>
                <td>' . $item->name . '</td>
                <td>' . $item->code . '</td>
                <td>' . $item->item_type . '</td>
               
                <td>' . $item->category . '</td>
                <td>' . $item->standard_cost . '</td>
                <td>' . $item->default_base_unit . '</td>
                <td>' . $item->reorder_qty . '</td>
                <td>' . $item->expiring_days . '</td>
                <td>
                    <button type="button" id="btnEdit"  name="btnEdit" data-id="' . $item->generalItemId . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                        </button>
                                        <button type="button" id="btnDelete" name="btnDelete" data-id="' . $item->generalItemId . '" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                        </button>

                           <button type="button" id="btnView" name="btnView" 
    data-id="' . $item->generalItemId . '" 
    class="btn btn-link p-0 viewItem btnView" 
    >
    <i class="mdi mdi-eye font-size-16 text-info"></i>
</button>


    </td>
  </tr>';
    }
    $html .= '</tbody></table></div>
            </div>';
    echo $html;

    // header('Content-Type: application/json');
    // echo json_encode($res);

}
// DELETE generalItem
else if ($methodType === 'delete') {
    $generalItem_id = ($_POST['generalItem_id']);

    $bodyContent = array(
        "generalItem_id" => $generalItem_id,
    );
    $res = CallAPI($api, "DELETE", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

// GET Single generalItem
else if ($methodType === 'getSingle') {
    $generalItem_id = ($_POST['generalItem_id']);

    $bodyContent = array(
        "generalItem_id" => $generalItem_id,
        "methodType" => "singleItem"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

// Invalid methodType
else {
    echo json_encode(["status" => "error", "msg" => "Bad request"]);
    exit;
}
