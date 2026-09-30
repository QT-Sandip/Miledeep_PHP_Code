<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CLOSING_AND_OPENING_STOCK_REPORT;

$bodyContent = array(
    "ProductionDate" => trim($_POST["productionDate"]),
    "VarietyID" => trim($_POST["variety"]),
    "ColdstoreID" => trim($_POST["coldStore"]),
    "PackingStyle" => trim($_POST["pckgStyle"]),
    "Grade" => trim($_POST["grade"]),
    "StockType" => trim($_POST["StockType"]),
    "Soaking" => trim($_POST["soaking"]),
    "TypeProduct" => trim($_POST["TypeProduct"]),
    "ProductCategory" => trim($_POST["ProductCategory"]),
    "PackingType" => trim($_POST["packingType"]),
    "FreezingType" => trim($_POST["freezingType"]),
    'Species' => trim($_POST["species"]),
    'AbStatus' => trim($_POST["abstatus"]),

);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;


// {"ProductionDate":"2024-07-25","TypeProduct":"RAW","Species":"VM","ProductionPlant":"3"}
$StockType = $_POST["StockType"];

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
        $Totals = $resList->Totals;
        // $resGrades = $resList->Grades;

        if ($StockType == 'ClosingDate') {

            $slabs = "Closing Stock Slabs";
            $mcs = "Closing Stock Mcs";
        } else {
            $slabs = "Opening Stock Slabs";
            $mcs = "Opening Stock Mcs";
        }

        ?>
        <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 100px;width: 100px;">Plant</th>
                    <th style="min-width: 100px;width: 100px;">Packing Type </th>
                    <th style="min-width: 100px;width: 100px;">Species </th>
                    <th style="min-width: 100px;width: 100px;">Variety</th>
                    <th style="min-width: 100px;width: 100px;">Treatment</th>
                    <th style="min-width: 100px;width: 100px;">Freezing Type</th>
                    <th style="min-width: 100px;width: 100px;">PackingStyle</th>
                    <th style="min-width: 100px;width: 100px;">Grade</th>
                    <th style="min-width: 100px;width: 100px;">Glaze</th>
                    <th style="min-width: 100px;width: 100px;">TypeProduct</th>
                    <th style="min-width: 100px;width: 100px;"><?php echo $mcs ?></th>
                    <th style="min-width: 100px;width: 100px;"><?php echo $slabs ?></th>
                    <th style="min-width: 100px;width: 100px;">Kgs</th>
                    <th style="min-width: 100px;width: 100px;">Product Category</th>


                    <!-- <th style="min-width: 100px;width: 100px;">Opening Stock Slabs</th>
                    <th style="min-width: 100px;width: 100px;">Opening Stock Mcs</th>
                    <th style="min-width: 100px;width: 100px;">Closing Stock Slabs</th>
                    <th style="min-width: 100px;width: 100px;">Closing Stock Mcs</th> -->
                </tr>
            </thead>
            <tbody>
                <?php
                $TotalStockKgs = 0;
                 foreach ($List as $item) { 
                    $TotalStockKgs += $item->Kgs;?>
                    <tr>
                        <td style="min-width:50px;"><?php echo $item->SNo ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Plant ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingType ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Species ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Species . '-' . $item->Variety ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->SockingType ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->FreezingType ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Grade ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Glaze ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->TypeProduct ?></td>
                        <td class="Mcs" style="min-width: 100px;width: 100px;"><?php echo $item->Mcs ?></td>
                        <td class="Slabs" style="min-width: 100px;width: 100px;"><?php echo $item->Slabs ?></td>
                        <td class="Kgs" style="min-width: 100px;width: 100px;"><?php echo $item->Kgs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ProductCategory ?></td>




                        <!-- <td style="min-width: 100px;width: 100px;"><?php echo $item->OpeningSlabs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OpeningMcs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ClosingSlabs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ClosingMcs ?></td>
 -->

                    </tr>
                <?php } ?>
            </tbody>

            <tfoot>
                <tr>
                    <th></th>
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th id="TotalStockMcs"><?php echo $Totals->TotalStockMcs ?> </th>
                    <th id="TotalStockSlabs"><?php echo $Totals->TotalStockSlabs ?> </th>                    
                    <th id="TotalStockKgs"><?php echo $TotalStockKgs;?></th>
                    <th></th>
                </tr>
            </tfoot>

        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>
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