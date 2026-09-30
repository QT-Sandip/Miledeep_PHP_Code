<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_DAILY_INWARD;

$Date = trim($_POST["date"]);
$ColdStores = trim($_POST["coldStore"]);
$StockBlnTo = trim($_POST["stockBlnTO"]);
$PackingType = trim($_POST["packingType"]);
$PackingStyle = trim($_POST["PackingStyle"]);
$poNumber = trim($_POST["poNumber"]);
$variety = trim($_POST["variety"]);
$Group = trim($_POST["group"]);
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);


$bodyContent = array(
    'TransactionDate' => $Date,
    'ColdStoreID' => $ColdStores,
    'StockHolderID' => $StockBlnTo,
    'PackingType' => $PackingType,
    'PackingStyle' => $PackingStyle,
    'PoNumber' => $poNumber,
    'Variety' => $variety,
    'Group' => $group,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->DailyInwardData;
        $Totals = $resList->Totals;
        // var_dump($Totals);

        $Count = count($List);
?>
        <div class="card">
            <div class="card-body">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>S.NO</th>
                            <th>Production Date</th>
                            <th>Transaction Date</th>
                            <th>Group</th>
                            <th>Po No </th>
                            <th>Packing Style</th>
                            <th>Variety</th>
                            <th>Grade</th>
                            <th>Packing Type</th>
                            <th>Mcs</th>
                            <th>Loose Bags</th>
                            <th>Kgs</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($List as $item) { ?>
                            <tr>
                                <td><?php echo $item->SNo ?></td>
                                <td><?php echo date('d-m-Y', strtotime($item->ProductionDate)) ?></td>
                                <td><?php echo date('d-m-Y', strtotime($item->TransactionDate)) ?></td>
                                <td><?php echo $item->Group ?></td>
                                <td><?php echo $item->PoNo  ?></td>
                                <td><?php echo $item->PackingStyle ?></td>
                                <td><?php echo $item->Variety ?></td>
                                <td><?php echo $item->Grade ?></td>
                                <td><?php echo $item->PackingType ?></td>
                                <td><?php echo $item->Mcs ?></td>
                                <td><?php echo $item->LooseBags ?></td>
                                <td><?php echo $item->Kgs ?></td>
                                <td><?php echo $item->Remarks ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Totals</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $Totals->TotalMcs ?></td>
                            <td><?php echo $Totals->TotalLooseBags  ?></td>
                            <td><?php echo $Totals->TotalKGs  ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $Count ?> entries</div>

                <?php // $activepagestyle = "border:2px solid #74788d" 
                ?>
                <div>
                    <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' /> -->
                    <ul class="pagination pagination-rounded justify-content-center">
                        <?php
                        // for ($i = 1; $i <= $PageCount; $i++) {
                        //     $pageActive = ($PageNo == $i ? "active" : "");
                        //     echo '<li class="page-item"><button class="page-link ' . $pageActive . 'btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                        // }
                        ?>
                    </ul>
                </div>
        <?php } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
} ?>
            </div>
        </div>