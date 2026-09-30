<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$MethodType = trim($_POST["MethodType"]);

if ($MethodType == "Delete") {
    $ArrivalID = trim($_POST["ArrivalID"]);
    $ArrivalDate = trim($_POST["ArrivalDate"]);

    $api = API_RM_ARRIVAL_LIST;

    $bodyContent = array(
        'RmArrivalID' => $ArrivalID,
        'ArrivalDate' => $ArrivalDate
    );

    $resList = CallAPI($api, "DELETE", "", $bodyContent);

    $List = $resList->Data;

    header('Content-Type: application/json');

    $response = array("Error" => $List->Error, "Msg" => $List->Msg);
    echo json_encode($response);

    exit();
} else {


    $api = API_RM_ARRIVAL_LIST;

    $PsId = trim($_POST["PsId"]);
    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $ArrivalPlant = trim($_POST["ArrivalPlant"]);
    $Supplier = trim($_POST["Supplier"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        'ArrivalPlant' => $ArrivalPlant,
        'Supplier' => $Supplier,
        'PsId' => $PsId
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;
    $resList = CallAPI($api, "GET", "", $bodyContent);
    // echo json_encode($resList);

    $error = $resList->Error;
    $List = $resList->Data;
?>


    <?php if (!isset($error) || empty($error)) { ?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th>Arrival Number</th>
                    <th>Arrival Date </th>
                    <th>Arrival Plant </th>
                    <th>Purchase Type</th>
                    <th>Supplier</th>
                    <th>Grader</th>
                    <th>Agent</th>
                    <th>Arrival Count</th>
                    <th>Arrival Weight</th>
                    <th>Arrival Rate</th>
                    <th>Total Amt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->ArrivalNumber ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->ArrivalDate)) ?></td>
                        <td><?php echo $item->ArrivalPlant ?></td>
                        <td><?php echo $item->PurchaseType ?></td>
                        <td><?php echo $item->Supplier ?></td>
                        <td><?php echo $item->Grader ?></td>
                        <td><?php echo $item->Agent ?></td>
                        <td><?php echo $item->ArrivalCount ?></td>
                        <td><?php echo $item->ArrivalWeight ?></td>
                        <td><?php echo $item->ArrivalRate ?></td>
                        <td class="text-right"><?php echo $item->TotalAmt ?></td>
                        <td class="text-center">
                            <?php if ($resList->EditAccess && $item->EditLink != '') { ?>
                                <a target="_blank" href="<?php echo $item->EditLink . "?ArrivalID=" . $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            <?php } ?>
                            &nbsp;&nbsp;
                            <input type="hidden" class="hdnReceivingCount" value="<?php echo $item->ReceivingCount ?>" />
                            <?php if ($item->DeleteAccess) { ?>
                                <button type="button" class="mr-3 text-primary btnDelete" data-id="<?php echo $item->RmArrivalID ?>" style="border: 0px; background: none;" title="Delete">
                                    <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                </button>
                            <?php  } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>

                    <th>Total</th>
                    <th><?php echo $item->TableTotalWeight ?></th>
                    <th>&nbsp;</th>
                    <th><?php echo $item->TableTotalAmount ?></th>
                    <th>&nbsp;</th>
                </tr>
                </tr>
            </tfoot>
        </table>

<?php
    }
}
?>