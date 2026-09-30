<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

if (isset($_POST["methodType"]) && !empty($_POST["methodType"]) && $_POST["methodType"] == 'DELETE_ROW') {

    $id = $_POST['id'];

    if (!is_numeric($id)) {
        echo "Invalid ID";
        exit;
    }

    $oldSlctData = legacyAuditFetchRowById('ShippingLinesChargeTransactions', 'id', $id, 'Sales');
    $deleteSQL = "DELETE FROM ShippingLinesChargeTransactions WHERE id = $id";

    if ($iconn->query($deleteSQL)) {
        legacyAuditLogDeleteById('ShippingLinesChargeTransactions', 'id', $id, $oldSlctData, [], ['Remarks' => 'Shipping line charge transaction deleted'], 'Sales');
        echo "success";
    } else {
        echo "Error: " . $iconn->error;
    }

    // } else if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {

    //     $method = $_POST["methodType"];
    //     $categories = $_POST['drpSelecCat']; // array of category codes
    //     $billNumbers = $_POST['billNumber']; // array of bill numbers
    //     $billDates = $_POST['billDate']; // array of bill dates
    //     $exRates = $_POST['exRate']; // array of exchange rates
    //     $amountsInINR = $_POST['billAmountInINR']; // array of INR amounts
    //     $amountsInUSD = $_POST['billAmountInUSD'];
    //     $sgstList = $_POST['sgst'];
    //     $igstList = $_POST['igst'];
    //     $ids = $_POST['id'];
    //     $poId = $_POST['po_id']; // array of USD amounts

    //     for ($i = 0; $i < count($categories); $i++) {
    //         $id = trim($ids[$i]);
    //         $category = mysqli_real_escape_string($iconn, $categories[$i]);
    //         $billNo = mysqli_real_escape_string($iconn, $billNumbers[$i]);
    //         // $billDate = mysqli_real_escape_string($iconn, $billDates[$i]);

    //         $rawDate = $billDates[$i];
    // $billDate = date('Y-m-d', strtotime(str_replace('/', '-', $rawDate)));


    //         $exRate = floatval($exRates[$i]);
    //         $inrAmount = floatval($amountsInINR[$i]);
    //         $usdAmount = floatval($amountsInUSD[$i]);
    //         $sgst = floatval($sgstList[$i]);
    //         $igst = floatval($igstList[$i]);

    //         if ($id === "") {
    //             $query = "
    //         INSERT INTO ShippingLinesChargeTransactions 
    //         (po_id, category_code, bill_number, bill_date, exchange_rate, amount_in_inr, amount_in_usd, sgst, igst) 
    //         VALUES 
    //         ('$poId', '$category', '$billNo', '$billDate', $exRate, $inrAmount, $usdAmount, $sgst, $igst)
    //     ";

    //         } else {
    //             $query = "
    //             UPDATE ShippingLinesChargeTransactions SET
    //                 category_code = '$category',
    //                 bill_number = '$billNo',
    //                 bill_date = '$billDate',
    //                 exchange_rate = $exRate,
    //                 amount_in_inr = $inrAmount,
    //                 amount_in_usd = $usdAmount,
    //                 igst = $igst,
    //                 sgst = $sgst
    //             WHERE id = $id
    //         ";
    //         }


    //         if (!$iconn->query($query)) {
    //             echo "Error on row $i: " . $iconn->error;
    //             exit;
    //         }
    //     }






    //     header('Content-Type:application/json');

    //     echo json_encode($method);



} else if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {

    $method = $_POST["methodType"];
    $categories = $_POST['drpSelecCat']; // array of category codes
    $billNumbers = $_POST['billNumber']; // array of bill numbers
    $billDates = $_POST['billDate']; // array of bill dates
    $exRates = $_POST['exRate']; // array of exchange rates
    $amountsInINR = $_POST['billAmountInINR']; // array of INR amounts
    $amountsInUSD = $_POST['billAmountInUSD'];
    $sgst = $_POST['sgst'];
    $igst = $_POST['igst'];
    $cgst = $_POST['cgst'];
    $grandTotal = $_POST['grandTotal'];
    $ids = $_POST['id'];
    $poId = $_POST['po_id']; // PO ID

    // Step 1: Ensure a record exists in shippinglinescharges
    $chargesId = 0;
    $mainBillNo = mysqli_real_escape_string($iconn, $billNumbers);
    $mainSgst = floatval($sgst);
    $mainIgst = floatval($igst);
    $mainCgst = floatval($cgst);

    $checkChargesQuery = "SELECT id FROM ShippingLinesCharges WHERE po_id = '$poId' LIMIT 1";
    $result = $iconn->query($checkChargesQuery);

    if ($result === false) {
        die("Query Error: " . $iconn->error);
    }

    if ($row = $result->fetch_assoc()) {
        $chargesId = $row['id'];
        $oldSlcData = legacyAuditFetchRowById('ShippingLinesCharges', 'id', $chargesId, 'Sales');

        $query = "
                UPDATE ShippingLinesCharges SET
                    bill_number = '$mainBillNo',
                    bill_date = '$billDates',
                    igst = $mainIgst,
                    sgst = $mainSgst,
                    cgst = $mainCgst,
                    grand_total = $grandTotal
                WHERE id = $chargesId
            ";

        if (!$iconn->query($query)) {
            echo "Error on updating charges : " . $iconn->error;
            exit;
        }
        legacyAuditLogUpdateById('ShippingLinesCharges', 'id', $chargesId, $oldSlcData, $_POST, ['Remarks' => 'Shipping lines charges updated'], 'Sales');
    } else {
        $query = "INSERT INTO ShippingLinesCharges (po_id, bill_number, bill_date, igst, sgst, cgst, grand_total)
                          VALUES ('$poId', '$mainBillNo', '$billDates', $mainIgst, $mainSgst, $mainCgst, $grandTotal)";
        if ($iconn->query($query)) {
            $chargesId = $iconn->insert_id;
        } else {
            echo "Error inserting into ShippingLinesCharges: " . $iconn->error;
            exit;
        }
        legacyAuditLogInsertById('ShippingLinesCharges', 'id', $chargesId, $_POST, ['Remarks' => 'Shipping lines charges created'], 'Sales');
    }



    // Step 2: Insert/update transaction rows
    for ($i = 0; $i < count($categories); $i++) {
        $id = trim($ids[$i]);
        $category = mysqli_real_escape_string($iconn, $categories[$i]);
        $exRate = floatval($exRates[$i]);
        $inrAmount = floatval($amountsInINR[$i]);
        $usdAmount = floatval($amountsInUSD[$i]);

        if ($id === "") {
            $query = "
                INSERT INTO ShippingLinesChargeTransactions 
                ( category_code, exchange_rate, amount_in_inr, amount_in_usd, charges_id) 
                VALUES 
                ( '$category', $exRate, $inrAmount, $usdAmount, $chargesId)
            ";

            if (!$iconn->query($query)) {
                echo "Error on row $i: " . $iconn->error;
                exit;
            }
            legacyAuditLogInsertById('ShippingLinesChargeTransactions', 'id', $iconn->insert_id, $_POST, ['Remarks' => 'Shipping line charge transaction added'], 'Sales');


        } else {
            $oldSlctRowData = legacyAuditFetchRowById('ShippingLinesChargeTransactions', 'id', $id, 'Sales');
            $query = "
                UPDATE ShippingLinesChargeTransactions SET
                    category_code = '$category',
                    exchange_rate = $exRate,
                    amount_in_inr = $inrAmount,
                    amount_in_usd = $usdAmount
                WHERE id = $id
            ";

            if (!$iconn->query($query)) {
                echo "Error on row $i: " . $iconn->error;
                exit;
            }
            legacyAuditLogUpdateById('ShippingLinesChargeTransactions', 'id', $id, $oldSlctRowData, $_POST, ['Remarks' => 'Shipping line charge transaction updated'], 'Sales');
        }
    }

    header('Content-Type:application/json');
    echo json_encode(["Msg" => "Saved successfully", "Error" => null]);



} else {
    $api = API_SHIPPING_LINES_CHARGES;

    $FromDate = trim($_POST["FromDate"]);
    $ToDate = trim($_POST["ToDate"]);
    $Status = trim($_POST["Status"]);
    $CountryID = isset($_POST["CountryID"]) ? $_POST["CountryID"] : [];
    $PageNo = trim($_POST["PageNo"]);
    $SearchKey = trim($_POST["SearchKey"]);
    $LoadAll = trim($_POST["LoadAll"]);

    $LinerId = trim($_POST["LinerId"]);

    $bodyContent = array(
        'FromDate' => $FromDate,
        'Status' => $Status,
        'CountryID' => $CountryID,
        'ToDate' => $ToDate,
        'SearchKey' => $SearchKey,
        'PageNo' => $PageNo,
        'LoadAll' => $LoadAll,
        'LinerId' => $LinerId,
    );

    $resClearing = CallAPI($api, "GET", "", $bodyContent);

    //var_dump($resClearing);

    if (isset($resClearing) && !empty($resClearing)) {

        $error = $resClearing->Error;

        if (!isset($error) || empty($error)) {
            $PageCount = $resClearing->PageCount;
            $resList = $resClearing->Data;
            ?>

                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>S.No</th>
                            <th>Inv No</th>
                            <th>Buyer</th>
                            <th>BL No</th>
                            <th>Port Of Discharge</th>
                            <th>Liner</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($resList as $item) { ?>
                            <tr>
                                <td>
                                <?php echo $item->SNO ?>
                                </td>
                                <td>
                                <?php echo $item->InvNo ?>
                                </td>
                                <td>
                                <?php echo $item->Buyer ?>
                                </td>
                                <td>
                                <?php echo $item->BLNo ?>
                                </td>
                                <td>
                                <?php echo $item->PortOfDischarge ?>
                                </td>
                                <td>
                                <?php echo $item->LinerName ?>
                                </td>
                                <td>
                                    <!-- <?php if ($resClearing->DeleteAccess) { ?>
                                <button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px"
                                    data-id="<?php echo $item->ID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value=""
                                    data-original-title="Delete">
                                    <i class="mdi mdi-trash-can font-size-16"></i></button>
                            <?php } ?> -->
                                    <!-- <button id="view" class="mr-3 text-primary btnActions" data-placement="top" title="view"
                            data-original-title="Edit" data-id="1170" data-value=""><i class="dripicons-preview font-size-16" style="color:green" ></i></button>
                            -->
                                <?php if ($Status == "1") { // completed 
                                                        echo '
                                        <a href="' . $resClearing->EditLink . $item->ID . '"
                                            class="mr-3 text-primary btnActions" data-placement="top" title="Edit" data-original-title="Edit">
                                            <i class="mdi mdi-pencil font-size-16"></i>
                                        </a>
                                        <button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="' . $item->ID . '" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
                                    <i class="mdi mdi-trash-can font-size-16"></i></button>
                                        ';
                                                    } else { // pending
                                                        echo '
                                        <a  href="' . $resClearing->EditLink . $item->ID . '"
                                            class="mr-3 text-primary btnActions" data-placement="top" title="Add" data-original-title="Add">
                                            <i class="mdi mdi-plus font-size-16"></i>
                                        </a>';
                                                    } ?>
                                </td>
                            </tr>
                    <?php } ?>
                    </tbody>
                </table>



                <div class="mt-3 mb-3">Showing 1 to
                <?php echo count($resList) ?> out of
                <?php echo $resClearing->TotalCount ?> entries
                </div>
                <div>
                    <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resClearing->TotalCount ?>' /> -->
                    <ul class="pagination pagination-rounded justify-content-center">
                        <?php
                        for ($i = 1; $i <= $PageCount; $i++) {
                            $pageActive = ($PageNo == $i ? "active" : "");
                            echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                        }
                        ?>
                    </ul>
                </div>
            <?php
        } else {
            echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
        }
    }
}

?>
