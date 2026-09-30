<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_IBT_REQUEST;

$IBTID = trim($_POST["ibtID"]);
$ColdStore = trim($_POST["coldStore"]);
$ibtType = trim($_POST["ibtType"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

$Month = trim($_POST["month"]);
$Year = trim($_POST["year"]);

$bodyContent = array(
    'IBTID' => $IBTID,
    'ibtType' => $ibtType,
    'ColdStore' => $ColdStore,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll,
    'Month' => $Month,
    'Year' => $Year,
);

$resList = CallAPI($api, "GET", "", $bodyContent);


// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;

?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 80px">S.No</th>
                        <th>Order Number</th>
                        <th>Order Date</th>
                        <th>From Store</th>
                        <th>To Store</th>
                        <th>IBT Type</th>

                        <th>Grade</th>
                        <th>PackingStyle</th>
                        <th>ProductDescription</th>
                        
                        <th>Total Mcs</th>
                        <th>Total NoOf Bags</th>
                        <?php if ($ibtType == '1') { ?>
                            <th>Balance Mcs</th>
                            <th>Balance Loose Bags</th>
                        <?php } else { ?>
                        <?php } ?>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->IBTOrderNumber ?></td>
                            <td><?php echo date('d-m-Y', strtotime($item->OrderDate)) ?></td>
                            <td><?php echo $item->FromStoreName ?></td>
                            <td><?php echo $item->ToStoreName ?></td>
                            <td><?php echo $item->IBTType ?></td>

                            <td><?php echo $item->Grade ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->ProductDescription ?></td>

                            <td><?php echo $item->TotalMcs ?></td>
                            <td><?php echo $item->TotalBags ?></td>
                            <?php if ($ibtType == '1') { ?>
                                <td><?php echo $item->BalanceMcs ?></td>
                                <td><?php echo $item->BalanceLoose ?></td>
                            <?php } else { ?>
                            <?php } ?>
                            <td>
                                <?php if ($item->RequestStatus == "1") { ?>
                                    <button class="btn btn-link badge badge-warning font-size-10" style="width:70px;background:yellow;color:Black">
                                        Active
                                    </button>
                                <?php } else if ($item->RequestStatus == "3" && $item->BalanceStatus == 1) { ?>
                                    <button class="btn btn-link badge badge-success font-size-10" style="width:70px;background:#025009;color:white">
                                        Completed
                                    </button>
                                <?php } else if ($item->RequestStatus == "0" || $item->BalanceStatus == 0) { ?>
                                    <button class="btn btn-link badge badge-danger font-size-10" style="width:70px;background:#e20000;color:white">
                                        Pending
                                    </button>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <?php if ($item->RequestStatus == 1 || $item->IBTType == 'IBT OUT') { ?>
                                    <a href='cs-ibt-breakup-preview.php?IBTID=<?php echo $item->IBTID ?>&RequestStatus=<?php echo $item->RequestStatus ?>&Type=<?php echo $item->IBTType ?>' class="mr-3 text-success btnActions" data-placement="top" title="View Request" data-original-title="Edit">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php } else if ($item->RequestStatus == 1 && $item->IBTType = 'IBT OUT') { ?>
                                    <!-- <a href='cs-ibt-breakup-data ' class="mr-3 text-primary btnActions" data-placement="top" title="Edit Request" data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a> -->
                                <?php } else if ($item->RequestStatus == "3" && $item->IBTType == 'IBT IN') { ?>
                                    <?php if ($item->BalanceMcs > 0 || $item->BalanceLoose > 0) { ?>
                                        <a target="_blank" href='inward?ProductionDataID=<?php echo $item->ProductionID ?>&RequestStatus=<?php echo $item->RequestStatus ?>&StoreId=<?php echo $item->ToStore ?>' class="mr-3 text-primary  btnActions" data-placement="top" title="IBT IN" data-original-title="Edit">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <!-- <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div> -->
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php  } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
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
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $error . "</div>";
    }
}
?>