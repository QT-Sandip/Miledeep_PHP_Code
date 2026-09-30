<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$MethodType = trim($_POST["methodType"]);

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$HlVaApproval = $ArrayClientConfigData['HlVaApproval'];

if ($MethodType == "delete") {
    $Center = trim($_POST["center"]);
    $Supplier = trim($_POST["supplier"]);
    $Plant = trim($_POST["plant"]);
    $BatchDate = trim($_POST["startDate"]);
    $BillID = trim($_POST["billID"]);

    $api = API_HL_VA_DAILY;

    $bodyContent = array(
        'CenterID' => $Center,
        'SupplierID' => $Supplier,
        'Plant' => $Plant,
        'BatchDate' => $BatchDate,
        'BillID' => $BillID
    );

    $result = CallAPI($api, "DELETE", "", $bodyContent);

    // header('Content-Type: application/json');

    // $response = array("Error" => $resdel->Error, "Msg" => $resdel->Msg);    

} else {

    $api = API_HL_VA_DAILY;

    $Center = trim($_POST["center"]);
    $Plant = trim($_POST["plant"]);
    $Supplier = trim($_POST["supplier"]);
    $BatchDate = trim($_POST["startDate"]);

    $bodyContent = array(
        'Plant' => $Plant,
        'CenterID' => $Center,
        'SupplierID' => $Supplier,
        'BatchDate' => $BatchDate
    );

    $result = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($List, JSON_PRETTY_PRINT);
    // exit;
}
// echo API_HL_VA_DAILY;
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// echo json_encode($result, JSON_PRETTY_PRINT);

$error = $result->Error;

if (!isset($error) || empty($error)) {

    $List = $result->Data;
    if (isset($List) && !empty($List)) {
        ?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.NO</th>
                    <th>Center</th>
                    <th>Contractor</th>
                    <th>Batch Name</th>
                    <th>Batch Date</th>
                    <th>Bus/Auto</th>
                    <th>Persons</th>
                    <th>Shift</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Adjustments</th>
                    <th>Extras</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $Items) { ?>
                    <tr>
                        <td><?php echo $Items->SNO ?></td>
                        <td><?php echo $Items->UnitName ?></td>
                        <td><?php echo $Items->ContrctrName ?></td>
                        <td><?php echo $Items->SubContractorName ?></td>
                        <td><?php echo date('d-m-Y', strtotime($BatchDate)) ?></td>
                        <td><?php echo $Items->AutoCharge ?></td>
                        <td><?php echo $Items->Persons ?></td>
                        <td><?php echo $Items->Shift ?></td>
                        <td><?php echo $Items->Type ?></td>
                        <td><?php echo $Items->Qty ?></td>
                        <td><?php echo $Items->Adjustments ?></td>
                        <td><?php echo $Items->Extras ?></td>
                        <td><?php echo $Items->Remarks ?></td>
                        <td><?php if (($HlVaApproval == 0 || $_SESSION["AccessType"] == '1') || ($HlVaApproval == 1 && $Items->ApprovalStatus == 0)) { ?>
                                <a target="_blank" href="<?php echo $result->EditLink . "?BillID=" . $Items->DailyBatchID ?>"
                                    class="mr-3 text-primary" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>

                                &nbsp;&nbsp;
                                <?php //if ($result->EditAccess) { 
                                                ?>
                                <button type="button" class="mr-3 text-primary btnDelete" data-id="<?php echo $Items->DailyBatchID ?>"
                                    style="border: 0px; background: none;" title="Delete">
                                    <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                </button>
                            <?php } ?>
                            <a target="_blank" href="Reports/hl-pd-bill-print?BillID=<?php echo $Items->DailyBatchID ?>"
                                class="mr-3 text-primary" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                <i class="mdi mdi-printer font-size-16"></i>
                            </a>
                            <?php // } 
                                        ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<div class='text-center'>" . $error . "</div>";
    }
} ?>