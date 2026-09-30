<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = isset($_POST["methodType"]) ? trim($_POST["methodType"]) : '';

if ($methodType) {

    $api = API_PROFORMA_CREATE; // Defined in global.php

    // ===================== GET SINGLE PROFORMA =====================
    if ($methodType === "getSingle") {
        $proforma_id = trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : '');
        $bodyContent = ["ProformaID" => $proforma_id];
        $result = CallAPI($api, "GET", "", $bodyContent);
    }

    // ===================== SAVE PROFORMA =====================
    else if ($methodType === "save") {

        $po_attachment = isset($_POST['old_POAttachment']) ? $_POST['old_POAttachment'] : ''; // retain old attachment by default
        if (!empty($_FILES['po_attachment']['name'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/proforma/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['po_attachment']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['po_attachment']['tmp_name'], $targetFile)) {
                $po_attachment = '/uploads/proforma/' . $fileName; // overwrite if new file uploaded
            }
        }


        $bodyContent = [
            "proforma_id"         => trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : '0'),
            "shipment_date"       => trim(isset($_POST["shipment_date"]) ? $_POST["shipment_date"] : ''),
            "country_origin"      => trim(isset($_POST["country_origin"]) ? $_POST["country_origin"] : ''),
            "country_destination" => trim(isset($_POST["country_destination"]) ? $_POST["country_destination"] : ''),
            "final_destination"   => trim(isset($_POST["final_destination"]) ? $_POST["final_destination"] : ''),
            "pro_no"              => trim(isset($_POST["pro_no"]) ? $_POST["pro_no"] : ''),
            "po_attachment"       => $po_attachment,
            "po_approval_date"    => trim(isset($_POST["po_approval_date"]) ? $_POST["po_approval_date"] : ''),
            "process_packed_by"   => trim(isset($_POST["process_packed_by"]) ? $_POST["process_packed_by"] : ''),
            "port_discharge"      => trim(isset($_POST["port_discharge"]) ? $_POST["port_discharge"] : ''),
            "is_glaze"            => trim(isset($_POST["is_glaze"]) ? $_POST["is_glaze"] : ''),
            "delivery_terms"      => trim(isset($_POST["delivery_terms"]) ? $_POST["delivery_terms"] : ''),
            "buyer_other"         => trim(isset($_POST["buyer_other"]) ? $_POST["buyer_other"] : ''),
            "amount_words"        => trim(isset($_POST["amount_words"]) ? $_POST["amount_words"] : ''),
            "advance_amount"      => trim(isset($_POST["advance_amount"]) ? $_POST["advance_amount"] : ''),

            "total_quantity"      => trim(isset($_POST["total_quantity"]) ? $_POST["total_quantity"] : ''),
            "commission"          => trim(isset($_POST["commission"]) ? $_POST["commission"] : ''),
            "commission_value"    => trim(isset($_POST["commission_value"]) ? $_POST["commission_value"] : ''),
            "approved_by"         => trim(isset($_POST["approved_by"]) ? $_POST["approved_by"] : '')
        ];

        $result = CallAPI($api, "POST", "", $bodyContent);
    }

    // ===================== UPDATE PROFORMA =====================
 else if ($methodType === "update") {

    // retain old attachment if no new file uploaded
    $po_attachment = isset($_POST['old_POAttachment']) ? $_POST['old_POAttachment'] : '';

    if (!empty($_FILES['po_attachment']['name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/proforma/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['po_attachment']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['po_attachment']['tmp_name'], $targetFile)) {
            $po_attachment = '/uploads/proforma/' . $fileName; // overwrite if new file uploaded
        }
    }

    $bodyContent = [
        "proforma_id"         => trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : ''),
        "shipment_date"       => trim(isset($_POST["shipment_date"]) ? $_POST["shipment_date"] : ''),
        "country_origin"      => trim(isset($_POST["country_origin"]) ? $_POST["country_origin"] : ''),
        "country_destination" => trim(isset($_POST["country_destination"]) ? $_POST["country_destination"] : ''),
        "final_destination"   => trim(isset($_POST["final_destination"]) ? $_POST["final_destination"] : ''),
        "pro_no"              => trim(isset($_POST["pro_no"]) ? $_POST["pro_no"] : ''),
        "po_attachment"       => $po_attachment,
        "po_approval_date"    => trim(isset($_POST["po_approval_date"]) ? $_POST["po_approval_date"] : ''),
        "process_packed_by"   => trim(isset($_POST["process_packed_by"]) ? $_POST["process_packed_by"] : ''),
        "port_discharge"      => trim(isset($_POST["port_discharge"]) ? $_POST["port_discharge"] : ''),
        "is_glaze"            => trim(isset($_POST["is_glaze"]) ? $_POST["is_glaze"] : ''),
        "delivery_terms"      => trim(isset($_POST["delivery_terms"]) ? $_POST["delivery_terms"] : ''),
        "buyer_other"         => trim(isset($_POST["buyer_other"]) ? $_POST["buyer_other"] : ''),
        "amount_words"        => trim(isset($_POST["amount_words"]) ? $_POST["amount_words"] : ''),
        "advance_amount"      => trim(isset($_POST["advance_amount"]) ? $_POST["advance_amount"] : ''),
        "total_quantity"      => trim(isset($_POST["total_quantity"]) ? $_POST["total_quantity"] : ''),
        "commission"          => trim(isset($_POST["commission"]) ? $_POST["commission"] : ''),
        "commission_value"    => trim(isset($_POST["commission_value"]) ? $_POST["commission_value"] : ''),
        "approved_by"         => trim(isset($_POST["approved_by"]) ? $_POST["approved_by"] : '')
    ];

    $result = CallAPI($api, "POST", "", $bodyContent);
}


    // ===================== DELETE PROFORMA =====================
    else if ($methodType === "delete") {
        $proforma_id = trim(isset($_POST["proforma_id"]) ? $_POST["proforma_id"] : '');
        if (empty($proforma_id)) {
            echo json_encode(["status" => "error", "msg" => "Proforma ID is missing."]);
            exit;
        }

        // choose one (recommended uppercase)
        $bodyContent = ["ProformaID" => $proforma_id];

        $result = CallAPI($api, "DELETE", "", $bodyContent);
    }



    // ===================== LOAD PROFORMA LIST =====================
    else if ($methodType === "load") {
        $result = CallAPI($api, "GET", "", []);

        $htmlResponse = '<div class="card">
            <div class="card-body">
                <table id="tblProforma" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>PRO No</th>
                            <th>Shipment Date</th>
                            <th>Country (Origin → Destination)</th>
                            <th>Port of Discharge</th>
                            <th>Buyer</th>
                            <th>Advance Amount</th>
                        
                            <th>Total Qty</th>
                            <th>Commission %</th>
                            <th>Commission Value</th>
                            <th>Approved By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

        $i = 1;
        if (!empty($result->Data)) {
            foreach ($result->Data as $proforma) {
                $htmlResponse .= '<tr>
                    <td>' . $i . '</td>
                    <td>' . htmlspecialchars(isset($proforma->ProNo) ? $proforma->ProNo : '') . '</td>
                    <td>' . htmlspecialchars(isset($proforma->ShipmentDate) ? $proforma->ShipmentDate : '') . '</td>
                    <td>' . htmlspecialchars((isset($proforma->CountryOrigin) ? $proforma->CountryOrigin : '') . " → " . (isset($proforma->CountryDestination) ? $proforma->CountryDestination : '')) . '</td>
                    <td>' . htmlspecialchars(isset($proforma->PortDischarge) ? $proforma->PortDischarge : '') . '</td>
                    <td>' . htmlspecialchars(isset($proforma->BuyerOther) ? $proforma->BuyerOther : '') . '</td>
                    <td>' . htmlspecialchars(isset($proforma->AdvanceAmount) ? $proforma->AdvanceAmount : '') . '</td>
                  
                    <td>' . htmlspecialchars(isset($proforma->TotalQuantity) ? $proforma->TotalQuantity : '') . '</td>
                    <td>' . htmlspecialchars(isset($proforma->Commission) ? $proforma->Commission : '') . '</td>
                    <td>' . htmlspecialchars(isset($proforma->CommissionValue) ? $proforma->CommissionValue : '') . '</td>
                    <td>' . htmlspecialchars(isset($proforma->ApprovedBy) ? $proforma->ApprovedBy : '') . '</td>
                    <td>
                        <button type="button" class="btn btn-link p-0 btnEdit" data-id="' . htmlspecialchars(isset($proforma->ProformaID) ? $proforma->ProformaID : '') . '">
                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                        </button>
                        <button type="button" class="btn btn-link p-0 btnDelete" data-id="' . htmlspecialchars(isset($proforma->ProformaID) ? $proforma->ProformaID : '') . '">
                            <i class="mdi mdi-trash-can font-size-16 text-danger"></i>
                        </button>

                        <button type="button" class="btn btn-link p-0 btnView" data-id="' . htmlspecialchars(isset($proforma->ProformaID) ? $proforma->ProformaID : '') . '">
        <i class="mdi mdi-eye font-size-16 text-info"></i>
    </button>
                    </td>
                </tr>';
                $i++;
            }
        } else {
            $htmlResponse .= '<tr><td colspan="12" class="text-center">No records found</td></tr>';
        }

        $htmlResponse .= '</tbody></table></div></div>';

        header('Content-Type: text/html');
        echo $htmlResponse;
        exit;
    }

    // ===================== COMMON RESPONSE =====================
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
