<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = $_POST['methodType'] ;

/* ---------- UPDATE STATUS ---------- */
if ($methodType === 'updateStatus') {

    $api = API_PM_PROFORMA_REPORT; // your purchase update API endpoint

    $postData = [
        "purchaseId" => $_POST['purchaseId'] // sent from JS
    ];

    $res = CallAPI($api, "POST", "", $postData);

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

/* ---------- LOAD REPORT ---------- */
if ($methodType === 'report') {

    $postData = [
        "FromDate"  => $_POST['from_date'] ,
        "ToDate"    => $_POST['to_date'] ,
        "Status"    => $_POST['status'] ,
        "SearchKey" => $_POST['searchKey'] ,
        "LoadAll"   => !empty($_POST['loadAll']) && $_POST['loadAll'] != 'false'
    ];

    $api = API_PM_PROFORMA_REPORT;

    $resList = CallAPI($api, "GET", "", $postData);

    if (!empty($resList->Error)) {
        echo "<tr><td colspan='5' class='text-center'>Note: {$resList->Error}</td></tr>";
        exit;
    }

    $List = $resList->Data ;

   if (!empty($List)) {
    $i = 0;
    foreach ($List as $item) {

        // --- Map status to text and color ---
        if (is_numeric($item->Status)) {
            if ($item->Status == 0) {
                $statusText = 'Approve';
                $color = 'orange';
            } elseif ($item->Status == 1) {
                $statusText = 'Pending';
                $color = 'red';
            } else {
                $statusText = 'Completed';
                $color = 'green';
            }
        } else {
            // Already text
            $statusText = $item->Status;
            if ($item->Status === 'Approve') {
                $color = 'orange';
            } elseif ($item->Status === 'Pending') {
                $color = 'red'; //  Pending always red
            } elseif ($item->Status === 'Completed') {
                $color = 'green';
            } else {
                $color = 'black';
            }
        }

        // --- Clickable only if Approve ---
        $isApprove = ($item->Status == 0 || $item->Status === 'Approve');
        $click = $isApprove
            ? "onclick=\"updatePurchaseStatus('{$item->PiID}', this)\" style=\"cursor:pointer;color:$color\""
            : "style=\"color:$color\"";

        // --- Render row ---
        echo "<tr>
                <td>" . ++$i . "</td>
                <td>{$item->DocNo}</td>
                <td>{$item->PiDate}</td>
                <td>{$item->Consignee}</td>
                <td $click>{$statusText}</td>
              </tr>";
    }


    } else {
        echo "<tr><td colspan='5' class='text-center'>No data found</td></tr>";
    }
}
