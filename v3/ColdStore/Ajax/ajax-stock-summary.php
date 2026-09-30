<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STOCK_SUMMARY;

// $FromDate = trim($_GET["FromDate"]);
// $ToDate = trim($_GET["ToDate"]);

// $PlantName = trim($_GET['PlantName']);
// $StoreName = trim($_GET['StoreName']);

// $bodyContent = array(
//     "FromDate" => trim($_GET["FromDate"]),
//     "ToDate" => trim($_GET["ToDate"]),
//     "PlantID" => trim($_GET["PlantID"]),
//     "ColdStoreID" => trim($_GET["ColdStoreID"]),
// );
$FromDate = trim($_POST["fromDate"]);
$ToDate = trim($_POST["closingDate"]);

$PlantName = trim($_POST['plantName']);
$StoreName = trim($_POST['storeName']);

$bodyContent = array(
    "FromDate" => trim($_POST["fromDate"]),
    "ToDate" => trim($_POST["closingDate"]),
    "PlantID" => trim($_POST["plant"]),
    "ColdStoreID" => trim($_POST["coldStore"]),
    "PDCategory" => trim($_POST["drpPDCategory"])
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $InwardData = $resList->InwardData;
        $TotalInwardKGs = $resList->TotalInwardKGs;
        $OutwardDate = $resList->OutwardDate;
        $TotalOutwardKGs = $resList->TotalOutwardKGs;
        $OpeningData = $resList->OpeningData;
        $ClosinggData = $resList->ClosinggData;

        if (!isset($error) || empty($error)) {
?>
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="text-align: right;">SUMMARY OF STOCK STATEMENT IN KGS FOR THE </th>
                        <th> PERIOD FROM <?php echo date('d/m/Y', strtotime($FromDate)) . ' TO ' . date('d/m/Y', strtotime($ToDate)) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td>OPENING BALANCE</td>
                        <td><?php echo round($OpeningData->OpeningActualKGs, 3) ?></td>
                    </tr>
                    <?php foreach ($InwardData as $itemInward) { ?>
                        <tr>
                            <td><?php echo $itemInward->Group ?></td>
                            <td><?php echo round($itemInward->InwardKGs, 3) ?></td>
                        </tr>
                    <?php  } ?>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td>TOTAL RECEIPTS</td>
                        <td><?php echo round($TotalInwardKGs, 3) ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>

                    <?php foreach ($OutwardDate as $itemOutward) { ?>
                        <tr>
                            <td><?php echo $itemOutward->Group ?></td>
                            <td><?php echo round($itemOutward->OutwardKGs, 3) ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr style="font-weight: bold;">
                        <td>TOTAL ISSUES</td>
                        <td><?php echo round($TotalOutwardKGs, 3) ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td>CLOSING BALANCE</td>
                        <td><?php echo round($ClosinggData->ClosingActualKGs, 3) ?></td>
                    </tr>
                </tbody>
            </table>
<?php
        }
    }
}

?>
