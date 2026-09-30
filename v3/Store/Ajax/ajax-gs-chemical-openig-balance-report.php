<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CHEMICAL_OPENING_BALANCE_REPORT;
$bodyContent = array(
    'PlantID' => trim($_POST["proPlant"]),
    'ChemicalID' => trim($_POST["chemical"]),
    'BillDate' => trim($_POST["billDate"]),
    'fromDate' => trim($_POST["fromDate"]),
    'toDate' => trim($_POST["toDate"])
);
// Add optional fields if present

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
?>
        <table id="tblDataTable" class="table table-hover table-bordered">
             <!-- <table class="table table-hover table-bordered"> -->
            <thead class="thead-light">
                <tr>
                    <th style="width: 25px;">S.NO</th>
                    <th>Chemical</th>
                    <!-- <th>Production Plant</th> -->
                    <th>KGs per Bag</th>
                    <th>Total Bags</th>
                    <th>Total KGs</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->ChemicalName ?></td>
                        <!-- <td><?php echo $item->StationName ?></td> -->
                        <td><?php echo $item->NoOfKgPerBag ?></td>
                        <td><?php echo $item->RemainingBags ?></td>
                        <td><?php echo $item->RemainingKGs ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div> -->
        
<?php }else{
    echo $error;
}

} ?>