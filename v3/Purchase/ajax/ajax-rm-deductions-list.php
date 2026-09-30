<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_PRICES_LIST;

$bodyContent = [
    "Species" => trim($_POST["species"]),
    "Year" => trim($_POST["year"]),
    "Month" => trim($_POST["month"]),
];

$resList = CallAPI($api, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {

    $error = $resList->status->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->data->rmDeductionData;
?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="width: 50px">S.No</th>
                    <th>Effective Date</th>
                    <th>Species</th>
                    <th>Purchase Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0; ?>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo ++$i ?></td>
                        <td><?php echo "Effected From " . $item->EffectedDate ?></td>
                        <td><?php echo $item->Species ?></td>
                        <td><?php echo $item->PurchaseCategoryName ?></td>
                        <td>
                            <a href="rm-deductions?RateID=<?php echo $item->DeductionRateID ?>" class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top" title="Edit Deductions" data-original-title="Edit">
                                <i class="mdi mdi-pencil font-size-18"></i>
                            </a>
                            <a href="Reports/rm-deductions-print?RateID=<?php echo $item->DeductionRateID ?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top" title="View Deductions" data-original-title="View">
                                <i class="dripicons-preview font-size-18"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>

<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>