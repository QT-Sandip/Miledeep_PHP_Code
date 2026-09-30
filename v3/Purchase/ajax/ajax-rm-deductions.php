<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST['methodType']);

$api = API_RM_PRICES;

if ($methodType == 'formPost') {
    $bodyContentPost = array(
        "RateID" => trim($_POST["rateID"]),
        "RateBreakUpID" => trim($_POST["breakUpID"]),
        "PurchaseType" => trim($_POST["purchaseType"]),
        "Species" => trim($_POST["species"]),
        "CenterId" => trim($_POST["centers"]),
        "EffectedDate" => trim($_POST["effectedDate"]),
        "StartCount" => trim($_POST["startCount"]),
        "EndCount" => trim($_POST["endCount"]),
        "StartWeight" => trim($_POST["startWeight"]),
        "EndWeight" => trim($_POST["endWeight"]),
        "Extra" => trim($_POST["extraRate"]),
    );
    // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
    // exit;
    $result = callAPI($api, "POST", "", $bodyContentPost);
} else if ($methodType == 'getList') {
    $bodyContent = [
        "RateID" => trim($_POST['rateID']),
    ];
    $result = callAPI($api, "GET", "", $bodyContent);
}
$rmBreakUpData = $result->data->rmDeductionBreakUpData;
$RateID = $result->data->rmDeductionData->RateID;

$Error = $result->status->Error;
$Msg = $result->status->Msg;

$content = '';
$content .= '<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th style="width: 80px">S.No</th>
            <th>Start Count</th>
            <th>End Count</th>
            <th>Start Weight</th>
            <th>End Weight</th>
            <th>Extra Rate</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>';
foreach ($rmBreakUpData as $item) {
    $content .= '
      <tr>
                <td>' . $item->SNo . '</td>
                <td class="tdStartCount">' . $item->StartCount . '</td>
                <td class="tdEndCount">' . $item->EndCount . '</td>
                <td class="tdStartWeight">' . $item->StartWeight . '</td>
                <td class="tdEndWeight">' . $item->EndWeight . '</td>
                <td class="tdExtra">' . $item->Extra . '</td>
                <td class="text-center">
                    <button type="button" id="btnEdit" name="btnEdit" data-brkUpID="' . $item->DeductionBreakupID . '" class="btn btn-link p-0 btnEdit" title="Edit">
                        <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                    </button>
                </td>
            </tr>';
}
$content .= '</tbody>
</table>
<div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of ' . $resTotal . ' entries</div>';

$ArrayData = [
    'Content' => $content,
    'Error' => $Error,
    'Msg' => $Msg,
    'RateID' => $RateID,
];

header('Content-Type: application/json');
echo json_encode($ArrayData);
