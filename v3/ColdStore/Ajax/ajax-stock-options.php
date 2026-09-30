<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STOCK_OPTIONS;

$ColdstoreID = trim($_POST["coldStore"]);
$BlocksID = trim($_POST["blocks"]);
$Year = trim($_POST["year"]);
$Month = trim($_POST["month"]);
$Type = trim($_POST["type"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);


$bodyContent = array(
    'StoreID' => $ColdstoreID,
    'BlockID' => $BlocksID,
    'Year' => $Year,
    'Month' => $Month,
    'Type' => $Type,
    'PageNo' => $PageNo,
    'SearchKey' => $SearchKey,
    'LoadAll' => $LoadAll
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
                        <th>ID</th>
                        <th>Entry Date</th>
                        <th>Po No</th>
                        <th>Variety</th>
                        <th>Grade</th>
                        <th>MCS</th>
                        <th>Loose</th>
                        <th>Packing Style</th>
                        <th>Freezing</th>
                        <th>Group</th>
                        <th>Packing Type</th>
                        <th>Batch Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->ID ?></td>
                            <td><?php echo date('d-m-Y', strtotime($item->EntryDate)) ?></td>
                            <td><?php echo $item->PoNumber ?></td>
                            <td><?php echo $item->Variety ?></td>
                            <td><?php echo $item->Grade ?></td>
                            <td><?php echo $item->Mcs ?></td>
                            <td><?php echo $item->Loose ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->FreezingType ?></td>
                            <td><?php echo $item->ProductionType ?></td>
                            <td><?php echo $item->PackingType ?></td>
                            <td><?php echo $item->BatchCode ?></td>
                            <td>
                                <a target="_blank" class="mr-3 text-primary" href='<?php echo $item->EditLink ?>'>
                                    <i class="mdi mdi-pencil font-size-18"></i>
                                </a>
                                <a target="_blank" class="mr-3 text-primary" href='<?php echo $item->PrintLink ?>'>
                                    <i class="mdi mdi-printer font-size-18"></i>
                                </a>
                                <button class="btn btn-danger" data-toggle="popover" data-trigger="focus" data-id="<?php echo $item->ID ?>" id="repck_bt" value="<?php echo $item->ID ?>">Out
                                </button>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php  } ?>
        <!-- <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div> -->
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>