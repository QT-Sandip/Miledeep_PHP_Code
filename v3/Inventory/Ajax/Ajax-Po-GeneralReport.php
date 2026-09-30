<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PO_REPORT;
$methodType = $_POST['methodType'];
if($methodType == 'updateStatus') {

    $poId = ($_POST['poId']);

    $bodyContent = array(
        "poId" => $poId,
        "methodType" => "updateStatus"
    );
    $res = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}
else if ($methodType == 'report') {

    $doc_no = $_POST['doc_no'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $status = $_POST['status'];

    $bodyContent = [
        "FromDate" => $from_date,
        "ToDate" => $to_date,
        "Status" => $status
    ];

    $res = CallAPI($api, "GET", "", $bodyContent);

    $POList = $res->Data->Data;

    $html = "";
    $sn = 1;

    $html = '  <div class="card">
      <div class="card-body">
       <table id="tblDataTable" class="table table-hover table-bordered" style="width: 100% !important">
                        <thead class="thead-light">
                    <tr>
                        <th>S.No</th>
                        <th>Doc No</th>
                        <th>Code</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($POList as $po) {

        $poDate = date('Y-m-d', strtotime($po->Date));
        $Status = ($po->Status == 0 ? 'Approve' : ($po->Status == 1 ? 'Pending' : 'Completed'));
        $Color = ($po->Status == 0 ? 'Orange' : ($po->Status == 1 ? 'Red' : 'Green'));

        $clickAction = ($po->Status == 0)
            ? "onclick=\"updateStatus('{$po->Id}')\" style=\"cursor:pointer;color:$Color;\""
            : "style=\"color:$Color;\"";

        $html .= '<tr>
                    <td>' . $sn . '</td>
                    <td>' . $po->DocNo . '</td>
                    <td>' . $po->Code . '</td>
                    <td>' . date('d-m-Y', strtotime($po->Date)) . '</td>
                    <td>' . $po->Supplier . '</td>
                    <td ' . $clickAction . '>' . $Status . '</td>
                  </tr>';

        $sn++;
    }

    $html .= '</tbody></table></div></div>';
    echo $html;
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
?>