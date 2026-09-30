<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GS_CHEM_PO_ORDERS_LIST;

$ReceivedID = trim($_POST["receivedID"]);
$ProPlant = trim($_POST["proPlant"]);
$Year = trim($_POST["year"]);
// $Status = trim($_POST['status']);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

$bodyContent = array(
    'ReceivedID' => $ReceivedID,
    'ProcessingPlantID' => $ProPlant,
    'BillDate' => $Year,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
//echo '<pre>';print_r($resList);die;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="width: 25px;">S.NO</th>
                    <th>PoNo</th>
                    <th>Chemical</th>
                    <th>Received Bags</th>
                    <th>Received Quantity</th>
                    <th>Balance Bags</th>
                    <th>Balance Quantity</th>
                    <!-- <th>Processing Plant</th> -->
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $SNO == 0 ?>
                <?php foreach ($List as $item) { ?>
                    <?php // if ($item->QtyStatus == $Status) { 
                    ?>
                    <tr>
                        <td><?php echo ++$SNO ?></td>
                        <td><?php echo $item->ChemPoNo ?></td>
                        <td><?php echo $item->ChemicalNames ?></td>
                        <td><?php echo $item->ReceivedBags ?></td>
                        <td><?php echo $item->QuantityReceived ?></td>
                        <td><?php echo $item->RequiredBags ?></td>
                        <td><?php echo $item->BalanceQty ?></td>
                        <!-- <td><?php echo $item->StationName ?></td> -->
                        <td><?php if ($item->QtyStatus == 0) {
                                $color = '#FA0F04';
                                $Status = "PENDING";
                            } else  if ($item->QtyStatus == 1) {
                                $color = '#025009';
                                $Status = "ORDER RECEIVED";
                            } else if ($item->QtyStatus == 2) {
                                $color = '#333cff';
                                $Status = "RECEIVED";
                            }
                            ?>
                            <div class="bg-active color-palette text-center">
                                <span class="btn btn-link badge badge-warning font-size-10 btnPIStatus"
                                    style="width:100px;background:<?php echo $color ?> !important;color:white" data-toggle="popover"
                                    data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                    <?php echo $Status ?>
                                </span>
                            </div>
                        </td>
                        
                    </tr>
                    <?php // } 
                    ?>
                <?php } ?>
            </tbody>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' /> -->
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
<?php }
} ?>
