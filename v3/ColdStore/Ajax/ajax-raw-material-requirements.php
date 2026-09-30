<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RUNNING_ORDERS;

$bodyContent = array(

    // 'Consignee' => trim($_POST["consignee"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
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
                        <th>SNO</th>
                        <th>Specie</th>
                        <th>Grade</th>
                        <th>variety</th>
                        <th>Pform</th>
                        <th>Freezing Type</th>
                        <th>Frozen Col</th>
                        <th>Glaze</th>
                        <th>Soaked</th>
                        <th>HonCount</th>
                        <th>Qty in KGS</th>
                        <th>Qty in TONS</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                            <td><?php echo "TEST"; ?></td>
                        </tr>
                    <?php // } ?>
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