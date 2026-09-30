<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$MethodType = $_POST['methodType'];

$api = API_QC_KITS;


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
        "TotalWells" => trim($_POST["totalWells"]),
        "Parameter" => trim($_POST["parameter"]),
    );
    $result = callAPI($api, "POST", "", $bodyContentPost);

    header('Content-Type: application/json');
    echo json_encode($result);
} else if ($MethodType == "getBreakUpData") {
    $bodyContent = array(
        "KitID" => trim($_POST["kitID"]),
        "BreakUpData" => "1",
    );
    $result = callAPI($api, "GET", "", $bodyContent);

    if (isset($result) && !empty($result)) {

        $error = $result->Error;

        if (!isset($error) || empty($error)) {
            $Data = $result->Data;
            ?>
            <?php if (!isset($error) || empty($error)) { ?>
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 80px">S.No</th>
                                <th>Kits Ordered Date</th>
                                <th>Kits Received Date</th>
                                <th>Kits Breakup Lot No</th>
                                <th>Used Wells</th>
                                <th>Damaged Wells</th>
                                <th>Balance Wells</th>

                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($Data as $item) { ?>
                                <tr>
                                    <td><?php echo $item->SNO ?></td>
                                    <td style="min-width:80px">
                                    <?php echo $item->KitsOrderedDate ? date('d-m-Y', strtotime($item->KitsOrderedDate)) : '' ?>
                                    </td>
                                    <td style="min-width:80px">
                                    <?php echo $item->KitsReceivedDate ? date('d-m-Y', strtotime($item->KitsReceivedDate)) : '' ?>
                                    </td>
                                    <td class="tdKitBreakupLotNo"><?php echo $item->KitBreakupLotNo ?></td>
                                    <td class="tdUtilizedWells"><?php echo $item->UtilizedWells ?></td>
                                    <td class="tdDamagedWells"><?php echo $item->DamagedWells ?></td>
                                    <td class="tdBalanceWells"><?php echo $item->BalanceWells ?></td>

                                </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <div class="mt-3 mb-3">Showing 1 to <?php echo count($Data) ?> out of <?php echo count($Data) ?> entries</div>
            <?php } ?>
            <?php
            } else {
                echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
            }
    }
}
?>