<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_GENERAL_CATEGORY;

$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
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
                        <th>Category Name</th>
                        <th style="width: 100px">Status</th>
                        <th style="width: 100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->CategoryName ?></td>
                            <td><?php if ($item->Status == 0) {
                                    $colorclass = 'badge-danger';
                                } else {
                                    $colorclass = 'badge-success';
                                } ?>
                                <div class="bg-active color-palette text-center">
                                    <span class="btn btn-link badge <?php echo $colorclass; ?> font-size-10 btnPIStatus" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                        <?php echo ($item->Status == 0) ? "In-Active" : "Active"; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($resList->EditAccess) { ?>
                                    <a href='<?php echo $resList->EditLink . $item->ID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit" data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>
                                <?php  } ?>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?></div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php  } ?>
        <div>
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item">
                        <button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button>
                    </li>';
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
