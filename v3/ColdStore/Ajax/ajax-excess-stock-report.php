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
                        <th>Plant</th>
                        <th>Variety</th>
                        <th>Pform</th>
                        <th>Freeze</th>
                        <th>Specie</th>
                        <th>Brand</th>
                        <th>Is soaked</th>
                        <th>Glaze</th>
                        <th>Hon Count</th>
                        <th>26/30</th>
                        <th>41/50</th>
                        <th>51/60</th>
                        <th>60/80</th>
                        <th>50/70</th>
                        <th>100/200</th>
                        <th>71/90</th>
                        <th>Broken</th>
                        <th>31/40</th>
                        <th>45634</th>
                        <th>21/25</th>
                        <th>61/70</th>
                        <th>MIX</th>
                        <th>110/150</th>
                        <th>91/110</th>
                        <th>100/150</th>
                        <th>13/15</th>
                        <th>Total Mcs</th>
                        <th>Total Slab</th>
                        <th>Total Wt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // foreach ($List as $item) { 
                    ?>
                    <tr>
                        <td><?php echo "TEST SNO" ?></td>
                        <td><?php echo "TEST Plant" ?></td>
                        <td><?php echo "TEST Variety" ?></td>
                        <td><?php echo "TEST Pform" ?></td>
                        <td><?php echo "TEST Freeze" ?></td>
                        <td><?php echo "TEST Specie" ?></td>
                        <td><?php echo "TEST Brand" ?></td>
                        <td><?php echo "TEST Is soaked" ?></td>
                        <td><?php echo "TEST Glaze" ?></td>
                        <td><?php echo "TEST Hon Count" ?></td>
                        <td><?php echo "TEST 26/30" ?></td>
                        <td><?php echo "TEST 41/50" ?></td>
                        <td><?php echo "TEST 51/60" ?></td>
                        <td><?php echo "TEST 60/80" ?></td>
                        <td><?php echo "TEST 50/70" ?></td>
                        <td><?php echo "TEST 100/200" ?></td>
                        <td><?php echo "TEST 71/90" ?></td>
                        <td><?php echo "TEST Broken" ?></td>
                        <td><?php echo "TEST 31/40" ?></td>
                        <td><?php echo "TEST 45634" ?></td>
                        <td><?php echo "TEST 21/25" ?></td>
                        <td><?php echo "TEST 61/70" ?></td>
                        <td><?php echo "TEST MIX" ?></td>
                        <td><?php echo "TEST 110/150" ?></td>
                        <td><?php echo "TEST 91/110" ?></td>
                        <td><?php echo "TEST 100/150" ?></td>
                        <td><?php echo "TEST 13/15" ?></td>
                        <td><?php echo "TEST Total Mcs" ?></td>
                        <td><?php echo "TEST Total Slab" ?></td>
                        <td><?php echo "TEST Total Wt" ?></td>
                        <td><?php echo "TEST Actions" ?></td>
                    </tr>
                    <?php // } 
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td colspan="3">Grand Total</td>
                        <!-- <td></td> -->
                        <!-- <td></td> -->
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo "TEST TOTAL" ?></td>
                        <td><?php echo "TEST TOTAL" ?></td>
                        <td><?php echo "TEST TOTAL" ?></td>
                    </tr>
                </tfoot>
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