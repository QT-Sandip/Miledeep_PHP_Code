<?php



// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);



include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';



$api = API_MONTHLY_SLABS_PRODUCTION_REPORT;



$Type = trim($_POST["dayMonthSelection"]);
$SlabType = trim($_POST["slabType"]);

$bodyContent = array(

    "FromDate" => trim($_POST["fromDate"]),

    "ToDate" => trim($_POST["toDate"]),

    "Species" => trim($_POST["species"]),

    "ProductionPlant" => trim($_POST["productionPlant"]),

    "TypeProduct" => trim($_POST["TypeProduct"]),

    "FreezingType" => trim($_POST["freezingType"]),

    "Month" => trim($_POST["month"]),

    "Year" => trim($_POST["year"]),

    "Type" => trim($_POST["dayMonthSelection"]),

    "ColdStore" => trim($_POST["coldStore"]),

    "SlabType" => trim($_POST["slabType"]),

);



// echo json_encode($bodyContent,JSON_PRETTY_PRINT);

// exit;

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList,JSON_PRETTY_PRINT);

$SlabType = trim($_POST["slabType"]);
if($SlabType == 'KGS'){
    $SlabType = 'Slabs';
}



if (isset($resList) && !empty($resList)) {



    $error = $resList->Error;



    if (!isset($error) || empty($error)) {

        $DayWiseData = $resList->DayWiseData;

        $Dates = $resList->Dates;

        $TypeProductDateWiseData = $resList->TypeProductDateWiseData;

        $DateWiseGrandTotals = $resList->DateWiseGrandTotals;

        // echo $DateWiseGrandTotals->TypeProduct;

        // echo json_encode($DateWiseGrandTotals, JSON_PRETTY_PRINT);

        // exit;



        $MonthWiseData = $resList->MonthWiseData->MonthWiseArrayData;

        $MonthWiseTotals = $resList->MonthWiseTotals;

        $GrandTotalGrossweight = $resList->MonthWiseData->GrandTotalGrossweight;

        $GrandTotalSlabs = $resList->MonthWiseData->GrandTotalSlabs;

        $TypeProductData = $resList->TypeProductData;



        // echo json_encode($TypeProductDateWiseData, JSON_PRETTY_PRINT);

        // exit;



        if ($Type == "DayWise") {

            ?>

            <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">

                <thead class="thead-light">

                    <tr>

                        <th style="min-width: 60px;">S.No</th>

                        <th style="min-width: 12px;">Species</th>

                        <th style="min-width: 180px;">Variety</th>

                        <th style="min-width: 100px;width: 100px;">Type Product</th>

                        <th style="min-width: 100px;width: 100px;">Packing Style</th>

                        <th style="min-width: 100px;width: 100px;">Treatment</th>

                        <?php foreach ($Dates as $date) { ?>

                            <th style="min-width: 130px;"><?php echo $date; ?></th>

                        <?php } ?>

                        <th>Totals</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($DayWiseData as $item) { ?>

                        <tr>

                            <td style="min-width:50px;"><?php echo $item->SNo; ?></td>

                            <td style="min-width: 100px;width: 100px;"><?php echo $item->Species; ?></td>

                            <td style="min-width: 100px;width: 100px;"><?php echo $item->Species . '-' . $item->ShortName ?></td>

                            <td style="min-width: 100px;width: 100px;"><?php echo $item->TypeProduct; ?></td>

                            <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle; ?></td>

                            <td style="min-width: 100px;width: 100px;"><?php echo $item->SockingType; ?></td>

                            <!-- <?php foreach ($item->SlabsData as $ItemBreakUp) { ?>

                                <td style="min-width: 130px;"><?php echo $ItemBreakUp->TotalSlabs; ?></td>

                            <?php } ?> -->

                            <?php foreach ($item->SlabsData as $ItemBreakUp) { ?>

                                <td style="min-width: 130px;">

                                    <?php 

                                        echo $ItemBreakUp->TotalSlabs;

                                    ?>

                                    <?php ?>

                                </td>

                            <?php } ?>

                        </tr>

                    <?php } ?>

                </tbody>

                <!-- <tfoot> -->

                <?php foreach ($TypeProductDateWiseData as $ItemData) { ?>

                    <tr style="font-weight: bold;">

                        <td style="min-width:50px;"></td>

                        <td style="min-width: 100px;width: 100px;"></td>

                        <td style="min-width: 100px;width: 100px;" colspan="2">Total <?php echo $ItemData->TypeProduct; ?> Production

                        </td>

                        <td style="min-width: 100px;width: 100px;"></td>

                        <td style="min-width: 100px;width: 100px;"></td>

                        <?php foreach ($ItemData->TypeProductData as $ItemDataBreakUp) { ?>

                            <!-- <td style="min-width: 130px;"><?php echo $ItemDataBreakUp->TypeWiseTotalSlabs; ?></td> -->

                            <td style="min-width: 130px;">

                                    <?php 

                                        echo $ItemDataBreakUp->TypeWiseTotalSlabs;

                                     ?>

                                    <?php ?>

                                </td>

                        <?php } ?>

                    </tr>

                <?php } ?>

                <tr style="font-weight: bold;">

                    <td style="min-width:50px;"></td>

                    <td style="min-width: 100px;width: 100px;"></td>

                    <td style="min-width: 100px;width: 100px;" colspan="2"> <?php echo $DateWiseGrandTotals[0]->TypeProduct; ?></td>

                    <td style="min-width: 100px;width: 100px;"></td>

                    <td style="min-width: 100px;width: 100px;"></td>

                    <?php foreach ($DateWiseGrandTotals[0]->TypeProductData as $ItemDataBreakUp) { ?>

                        <!-- <td style="min-width: 130px;"><?php echo $ItemDataBreakUp->TypeWiseTotalSlabs; ?></td> -->

                        <td style="min-width: 130px;">

                                    <?php

                                        echo $ItemDataBreakUp->TypeWiseTotalSlabs;

                                     ?>

                                    <?php ?>

                                </td>

                    <?php } ?>

                </tr>

                <!-- </tfoot> -->

            </table>

        <?php } else if ($Type == "MonthWise") { ?>



                <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">

                    <thead class="thead-light">

                        <tr>

                            <th style="min-width: 100px;width: 100px;">S.No</th>

                            <th style="min-width: 100px;width: 100px;">Species</th>

                            <th style="min-width: 100px;width: 100px;">Variety</th>

                            <th style="min-width: 100px;width: 100px;">Type Product</th>

                            <th style="min-width: 100px;width: 100px;">Packing Style</th>

                            <th style="min-width: 100px;width: 100px;">Treatment</th>

                            <th style="min-width: 100px;width: 100px;"><?php echo $SlabType ?></th>

                            <th style="min-width: 100px;width: 100px;">Gross Weight</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($MonthWiseData as $item) { ?>

                            <tr>

                                <td style="min-width: 100px;width: 100px;"><?php echo $item->SNo; ?></td>

                                <td style="min-width: 100px;width: 100px;"><?php echo $item->Species; ?></td>

                                <td style="min-width: 100px;width: 100px;"><?php echo $item->Species . '-' . $item->ShortName; ?></td>

                                <td style="min-width: 100px;width: 100px;"><?php echo $item->TypeProduct; ?></td>

                                <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle; ?></td>

                                <td style="min-width: 100px;width: 100px;"><?php echo $item->SockingType; ?></td>

                                <td style="min-width: 100px;width: 100px;"><?php echo $item->TotalSlabs; ?></td>

                                <td style="min-width: 100px;width: 100px;"><?php echo isset($item->GrossWeight)?round($item->GrossWeight,2):0; ?></td>

                            </tr>

                    <?php } ?>

                    </tbody>

                    <!-- <tfoot> -->
                        
                <?php foreach ($MonthWiseTotals as $index => $ItemMonthwiseTotals) { ?>

                        <tr style="font-weight: bold;">

                            <td></td>

                            <td colspan="3"><?php echo "Total ".$index." Production"; ?></td>

                            <td></td>

                            <td></td>

                            <td><?php echo $ItemMonthwiseTotals->TotalSlabs ?></td>

                            <td><?php echo round($ItemMonthwiseTotals->GrossWeight,2) ?></td>

                        </tr>

                <?php } ?>

                    <tr style="font-weight: bold;">

                        <td></td>

                        <td>Grand Totals</td>

                        <td></td>

                        <td></td>

                        <td></td>

                        <td></td>

                        <td><?php echo $GrandTotalSlabs; ?></td>

                        <td><?php echo round($GrandTotalGrossweight,2); ?></td>

                    </tr>
                
                    <!-- </tfoot> -->

                </table>

        <?php } ?>



        <?php if ($Type == "DayWise") { ?>

            <div class="mt-3 mb-3">Showing 1 to <?php echo count($DayWiseData) ?> out of <?php echo count($DayWiseData) ?> entries

            </div>

        <?php } else if ($Type == "MonthWise") { ?>

                <div class="mt-3 mb-3">Showing 1 to <?php echo count($MonthWiseData) ?> out of <?php echo count($MonthWiseData) ?>

                    entries</div>

        <?php } ?>

        <div>

            <ul class="pagination pagination-rounded justify-content-center">

                <?php

                for ($i = 1; $i <= $resList->PageCount; $i++) {

                    $pageActive = ($resList->PageNo == $i ? "active" : "");

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