<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$MethodType = $_POST['methodType'];

$api = API_MEDIA_KITS;

$loadall = 0;
if(isset($_POST['loadAll']) && $_POST['loadAll']){
    $loadall = 1;    
}
if ($MethodType == "postFormData") {
    $bodyContentPost = array(
        "KitID" => trim($_POST["kitID"]),
        "KitsOrderedDate" => trim($_POST["kitsOrderedDate"]),
        "KitsReceivedDate" => trim($_POST["receivedDate"]),
        "KitsLotNo" => trim($_POST["kitsLotNo"]),
        "NoOfKitsOrdered" => trim($_POST["orderedKits"]),
        "NoOfKitsReceived" => trim($_POST["receivedKits"]),
        "InvoiceNo" => trim($_POST["invoiceNo"]),
        "ManufactureDate" => trim($_POST["manufactureDate"]),
        "ExpiryDate" => trim($_POST["expiryDate"]),
        "BottleWeight" => trim($_POST["bottleWeight"]),
        "media" => trim($_POST["media"]),
        "Parameter" => trim($_POST["parameter"]),
        "loadall" => $loadall,
    );
    $result = callAPI($api, "POST", "", $bodyContentPost);

    header('Content-Type: application/json');
    echo json_encode($result);
} else if ($MethodType == "getBreakUpData") {
    $bodyContent = array(
        "KitID" => trim($_POST["kitID"]),
        "BreakUpData" => "0",
    );
    $result = callAPI($api, "GET", "", $bodyContent);
    $finalData = [];
// echo "<pre>"; print_r($result); exit;
    if (isset($result) && !empty($result)) {

        $error = $result->Error;

        if (!isset($error) || empty($error)) {
            $totalRecords = $result->TotalCount;
            $totalFiltered = $result->PageCount;
            $Data = $result->Data;            
            ?>
            <?php if (!isset($error) || empty($error)) { 
                foreach( $Data as $items ){
                    $inner_array = array(
                        "SNO" => $items->SNO,
                         "MediaName" => $items->MediaName,
                        "KitsOrderedDate" => $items->mKitsOrderedDate,
                        "KitsReceivedDate" => $items->mKitsReceivedDate,
                        "mKitsLotNo" => $items->mKitsLotNo,
                        "mNoOfKitsOrdered" => $items->mNoOfKitsOrdered,
                        "mNoOfKitsReceived" => $items->mNoOfKitsReceived,
                        "mInvoiceNo" => $items->mInvoiceNo,
                        "mManufactureDate" => $items->mManufactureDate,
                        "mExpiryDate" => $items->mExpiryDate,
                        "mBottleWeight" => $items->mBottleWeight,
                    );
                    $finalData[]=$inner_array;
                }
             } 
            } else {
                echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
            }
    }

    echo json_encode([
        "draw"            => 1,
        "recordsTotal"    => $totalRecords?intval($totalRecords):0,
        "recordsFiltered" => $totalFiltered?intval($totalFiltered):0,
        "data"            => $finalData, // could be empty, and that's okay
    ]);
    exit;
}
?>