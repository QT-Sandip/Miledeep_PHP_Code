<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HATCHERY_MASTERS;

$bodyContent = array(

    'SearchKey' => trim($_POST["searchKey"]),
    'PageNo' => trim($_POST["pageNo"]),
    'LoadAll' => trim($_POST["loadAll"])
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
                        <th style="width: 30px">S.No</th>
                        <th>Hatchery Name</th>
                        <th>Hatchery Location</th>
                        <th>Hatchery Capacity</th>
                        <th style="width: 30px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td style="width: 30px"><?php echo $item->SNO ?></td>
                            <td><?php echo $item->HatcryName ?></td>
                            <td><?php echo $item->HatcryLocation ?></td>
                            <td><?php echo $item->HatcryCapacity ?></td>
                            <td style="width: 30px" class="text-center">
                                <?php if ($item->EditLink) { ?>
                                    <a href=<?php echo $item->EditLink ?> class="mr-3 text-primary btnActions" data-placement="top" title="Edit Product" data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>
                                <?php  } ?>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
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
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>