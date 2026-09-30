<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';




/* ---------- UPDATE STATUS ---------- */
if ($_POST['methodType'] === 'updateStatus') {

    $api = API_GOODS_REPORT;

    $postData = [
        "grnId" => $_POST['grnId']
    ];

    $res = CallAPI($api, "POST", "", $postData);

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}


if ($_POST['methodType'] === 'report') {

    $postData = [
        "FromDate"  => $_POST['from_date'],
        "ToDate"    => $_POST['to_date'] ,
       "Status" => isset($_POST['status']) && $_POST['status'] !== ''
            ? (int) $_POST['status']
            : '',

        "SearchKey" => $_POST['searchKey'] ,
        "LoadAll" => (isset($_POST['loadAll']) && $_POST['loadAll'] === 'true')

    ];

    $api = API_GOODS_REPORT;
    $res = CallAPI($api, "GET", "", $postData);

    if (!empty($res->Error)) {
        echo "<tr><td colspan='6' class='text-center'>{$res->Error}</td></tr>";
        exit;
    }

    $list = $res->Data;
    if ($list) {
        $i = 0;
        foreach ($list as $row) {

            $statusText = ($row->status == 0 ? 'Approve' : ($row->status == 1 ? 'Pending' : 'Completed'));
            $color = ($row->status == 0 ? 'orange' : ($row->status == 1 ? 'red' : 'green'));

            $click = ($row->status == 0)
                ? "onclick=\"updateGoodsStatus('{$row->ID}', this)\" style=\"cursor:pointer;color:$color\""
                : "style=\"color:$color\"";

            echo "<tr>
        <td>" . ++$i . "</td>
        <td>{$row->DocNo}</td>
        <td>{$row->Date}</td>
        <td>{$row->VendorId}</td>
        <td>{$row->InvoiceNumber}</td>
        <td $click>{$statusText}</td>
    </tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center'>No data found</td></tr>";
    }
}
