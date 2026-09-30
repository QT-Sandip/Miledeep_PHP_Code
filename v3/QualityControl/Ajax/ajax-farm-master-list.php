<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_FARM_MASTER;

$PageNo = trim($_POST['pageNo']);
$bodyContent = array(
    'SearchKey' => trim($_POST["searchKey"]),
    'PageNo' => trim($_POST["pageNo"]),
    'LoadAll' => trim($_POST["loadAll"])
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->FarmData;
?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 80px">S.No</th>
                        <th>Farm Name</th>
                        <th>Registration No</th>
                        <th>Farmer Name</th>
                        <th>Farmer Address</th>
                        <th>Phone Number</th>
                        <th>Tonnage Value</th>
                        <th style="width: 100px">Status</th>
                        <th style="width: 100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->OptionName ?></td>
                            <td><?php echo $item->FarmRegistrationNo ?></td>
                            <td><?php echo $item->FarmerName ?></td>
                            <td><?php echo $item->FarmerAddress ?></td>
                            <td><?php echo $item->PhoneNumber ?></td>
                            <td><?php echo $item->TonnageValue ?></td>
                            <td><?php if ($item->Status == 0) {
                                    $color = '#FA0F04';
                                    $colorClass= "btn-danger";
                                } else {
                                    $color = '#025009';
                                    $colorClass= "btn-success";
                                } ?>
                                <div class="bg-active color-palette text-center">
                                    <span class="btn btn-link badge <?php echo $colorClass; ?> font-size-10 btnPIStatus"  data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                        <?php echo ($item->Status == 0) ? "In-Active" : "Active"; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href='<?php echo $item->EditLink ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php  } ?>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' /> -->
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
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>
