<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($cp . "Includes/IncludePage.php");
include_once ROOT . 'config/api-call.php';

// $api = API_PO_STATUS_REPORT;

// $bodyContent = array(
//     'Plant' => trim($_POST["plant"]),
//     'Variety' => trim($_POST["variety"]),
//     'Group' => trim($_POST["group"]),
//     'Grade' => trim($_POST["grade"]),
//     'PackingStyle' => trim($_POST["pckgStyle"]),
//     'ColdStore' => trim($_POST["coldStore"]),
//     'Brand' => trim($_POST["brand"]),
//     'PoNo' => trim($_POST["poNumber"]),
//     'ClosingDate' => trim($_POST["closingDate"]),
//     'PageNo' => trim($_POST["pageNo"]),
//     'SearchKey' => trim($_POST["searchKey"]),
//     'LoadAll' => trim($_POST["loadAll"])
// );

// $resList = CallAPI($api, "GET", "", $bodyContent);

// // echo json_encode($resList, JSON_PRETTY_PRINT);
// // exit;
// if (isset($resList) && !empty($resList)) {

//     $error = $resList->Error;

//     if (!isset($error) || empty($error)) {
//         $PageCount = $resList->PageCount;
//         $resTotal = $resList->TotalCount;
//         $List = $resList->Data;

//         if (!isset($error) || empty($error)) {
?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Click for Stockminus</th>
                        <th>Plant</th>
                        <th>Order Number</th>
                        <th>Cold Store</th>
                        <th>Rack</th>
                        <th>Last Inward Date</th>
                        <th>Combination</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // foreach ($List as $item) { ?>
                        <tr>
                            <td>Click for Stockminus</td>
                            <td>Plant</td>
                            <td>Order Number</td>
                            <td>Cold Store</td>
                            <td>Rack</td>
                            <td>Last Inward Date</td>
                            <td>Combination</td>
                            <td>Quantity</td>
                        </tr>
                    <?php  // } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php // echo count($List) ?> out of <?php // echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php // } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php //  echo $List->TotalCount ?>' />
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
//     } else {
//         echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
//     }
// }
?>
