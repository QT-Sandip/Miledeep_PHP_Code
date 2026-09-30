<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// API for Goods Receipt Note
$api = API_GRN_REPORT;
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
else if ($methodType === 'report') {

    $doc_no = $_POST['doc_no'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $status = $_POST['status'];

    $bodyContent = [
        "FromDate" => $from_date,
        "ToDate" => $to_date,
        "Status" => $status
    ];

    $response = CallAPI($api, "GET", "", $bodyContent);
    $GRNList = $response->Data->Data;
    $sn = 1;
    $html = '  <div class="card">
      <div class="card-body">
       <table id="tblDataTable" class="table table-hover table-bordered" style="width: 100% !important">
                        <thead class="thead-light">
                            <tr>
                                <th>S.No</th>
                                <th>Doc No</th>
                                <th>Date</th>
                                <th>Vendor</th>
                                <th>Invoice No</th>
                                <th>PO No</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';

    // foreach ($GRNList as $rec) {
    //     // Filter Doc No
    //     if ($doc_no && stripos($rec->PoNo, $doc_no) === false)
    //         continue;

    //     // Filter Status (ignore if status is empty or "All")
    //     if ($status && strtolower($status) !== 'all') {
    //         if (strcasecmp(trim($rec->Status), $status) != 0)
    //             continue;
    //     }

    //     // Filter Dates
    //     $recDate = date('Y-m-d', strtotime($rec->Date));
    //     if ($from_date && $recDate < $from_date)
    //         continue;
    //     if ($to_date && $recDate > $to_date)
    //         continue;
    //     $Status = ($rec->Status == 0 ? 'Approve' : ($rec->Status == 1 ? 'Pending' : 'Completed'));
    //      $Color = ($rec->Status == 0 ? 'Orange' : ($rec->Status == 1 ? 'Red' : 'Green'));

    //        $clickAction = ($rec->Status == 0)
    // ? "onclick=\"updateStatus('{$rec->Id}')\" style=\"cursor:pointer;color:$Color;\""
    // : "style=\"color:$Color;\"";

    //     $html .= '<tr>
    //                 <td>' . $sn . '</td>
    //                 <td>' . $rec->PoNo . '</td>
    //                 <td>' . $recDate . '</td>
    //                 <td>' . $rec->VendorName . '</td>
    //                 <td>' . $rec->InvoiceNo . '</td>
    //                 <td>' . $rec->PoNo . '</td>
    //               <td '. $clickAction .'>' . $Status . '</td>
    //               </tr>';
    //     $sn++;
    // }


    foreach ($GRNList as $rec) {

    // Filter Doc No
    if ($doc_no && stripos($rec->PoNo, $doc_no) === false)
        continue;

    // Map numeric status to text
    $recStatusText = ($rec->Status == 0 ? 'Approve' : ($rec->Status == 1 ? 'Pending' : 'Completed'));

    // Filter Status
    if ($status && strtolower($status) !== 'all') {
        if (strcasecmp(trim($recStatusText), trim($status)) != 0)
            continue;
    }

    // Filter Dates
    $recDate = date('Y-m-d', strtotime($rec->Date));
    if ($from_date && $recDate < $from_date)
        continue;
    if ($to_date && $recDate > $to_date)
        continue;

    $Color = ($rec->Status == 0 ? 'Orange' : ($rec->Status == 1 ? 'Red' : 'Green'));

    $clickAction = ($rec->Status == 0)
        ? "onclick=\"updateStatus('{$rec->Id}')\" style=\"cursor:pointer;color:$Color;\""
        : "style=\"color:$Color;\"";

    $html .= '<tr>
                <td>' . $sn . '</td>
                <td>' . $rec->PoNo . '</td>
                <td>' . $recDate . '</td>
                <td>' . $rec->VendorName . '</td>
                <td>' . $rec->InvoiceNo . '</td>
                <td>' . $rec->PoNo . '</td>
                <td '. $clickAction .'>' . $recStatusText . '</td>
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