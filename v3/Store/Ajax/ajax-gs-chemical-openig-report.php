<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$PlantID = trim($_POST["proPlant"]);

$api = API_CHEMICAL_OPENING_REPORT;
$bodyContent = array(
    'PlantID' => $PlantID,
    'ChemicalID' => trim($_POST["chemical"]),
    'BillDate' => trim($_POST["billDate"]),
    'fromDate' => trim($_POST["fromDate"]),
    'toDate' => trim($_POST["toDate"]),
    'Month' => trim($_POST["Month"]),
    'year' => trim($_POST["year"]),
);
// Add optional fields if present

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
// echo json_encode($resList, JSON_PRETTY_PRINT);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $MonthList = $resList->Data;
?>
        <!-- Excel Download Button -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;margin-right: 20px">
            <button id="btnExportExcel" class="btn btn-success">Download Excel</button>
        </div>

        <!-- Table Wrapper -->
        <div id="chemicalReportWrapper">
            <?php foreach ($MonthList as $item1) {
                $List = $item1->Data; ?>
                <!-- <table id="tblDataTable" class="table table-hover table-bordered"> -->
                <table class="table table-hover table-bordered text-center">
                    <thead class="thead-light">
                        <tr>
                            <th colspan="11" style="text-align: left; font-size: 16px;">
                                <h5><?php echo (string)$item1->Month; ?></h5>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 25px;">S.NO</th>
                            <th style="width: 260px;">Chemical</th>
                            <!-- <th>Production Plant</th> -->
                            <!-- <th>KGs per Bag</th>
                    <th>Opening Bags</th> -->
                            <th>Opening KGs</th>
                            <!-- <th>Total Purchase Bags</th> -->
                            <th>Total Purchase KGs</th>
                            <!-- <th>Total Consumption Bags</th> -->
                            <th>Total Consumption KGs</th>
                            <?php if ($PlantID != '') { ?>
                                <th>KGs Sent to Other Plant</th>
                            <?php } ?>
                            <!-- <th>Closing Bags</th> -->
                            <th>Closing KGs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($List)) {
                            $i = 0;
                            foreach ($List as $item) {
                                if ($item->OpeningQuanting > 0 || $item->ClosingQuanting > 0) { ?>
                                    <tr>
                                        <td><?php echo ++$i; //echo $item->SNO 
                                            ?></td>
                                        <td><?php echo $item->ChemicalName ?></td>
                                        <!-- <td><?php echo $item->NoOfKgPerBag ?></td> -->
                                        <!-- <td><?php echo $item->OpeningBags ?></td> -->
                                        <td><?php echo $item->OpeningQuanting ?></td>
                                        <!-- <td><?php echo $item->TotalReceivedBags ?></td> -->
                                        <td><?php echo $item->TotalQuantityReceived ?></td>
                                        <!-- <td><?php echo $item->TotalBagsUsed ?></td> -->
                                        <td><?php echo $item->TotalQuantityUsed ?></td>
                                        <?php if ($PlantID != '') { ?>
                                            <td><?php echo $item->TotalQuantityUsed2 ?></td>
                                        <?php } ?>
                                        <!-- <td><?php echo $item->ClosingBags ?></td> -->
                                        <td><?php echo $item->ClosingQuanting ?></td>
                                    </tr>
                            <?php
                                }
                            }
                        } else { ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted">No data available in table</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <!-- <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div> -->

            <?php } ?>
        </div>
<?php } else {
        echo $error;
    }
} ?>