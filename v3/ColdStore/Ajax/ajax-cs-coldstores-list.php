<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_COLDSTORES;

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

        if (!isset($error) || empty($error)) {
?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 80px">S.No</th>
                        <th>Store Name</th>
                        <th>Store Address</th>
                        <th>Production Plant</th>
                        <th>Store Location</th>
                        <th>Store Phone Number</th>
                        <th>GST Number</th>
                        <th>Status</th>
                        <th style="width: 100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->StoreName ?></td>
                            <td><?php echo $item->StoreAddress ?></td>
                            <td><?php echo $item->StationName ?></td>
                            <td><?php echo $item->StoreLocation ?></td>
                            <td><?php echo $item->StorePhoneNumber ?></td>
                            <td><?php echo $item->StoreGSTNumber ?></td>
                            <td><?php if ($item->StoreStatus == 0) {
                                    $color = '#FA0F04';
                                    $colorClass = "badge-danger";
                                } else {
                                    $color = '#025009';
                                    $colorClass = "badge-success";
                                } ?>
                                <div class="bg-active color-palette text-center">
                                    <span class="btn btn-link badge <?php echo $colorClass; ?> font-size-10 btnPIStatus" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                        <?php echo ($item->StoreStatus == 0) ? "In-Active" : "Active"; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($resList->EditAccess) {
                                ?>
                                    <a href='<?php echo $resList->EditLink . $item->StoreID
                                                ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit" data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>
                                <?php  }
                                ?>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal  ?> entries</div>
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
