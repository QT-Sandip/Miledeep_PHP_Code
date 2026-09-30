<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_DAILY_SLABS_PRODUCTION_REPORT;

$Type = trim($_POST["dayMonthSelection"]);
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

    "Brand" => trim($_POST["Brand"]),
    "Variety" => trim($_POST["Variety"]),
    "Grade" => trim($_POST["Grade"]),
    "Group" => trim($_POST["Group"]),
    "PckgStyle" => trim($_POST["PckgStyle"]),
    "PackingType" => trim($_POST["PackingType"]),
);

// echo json_encode($bodyContent,JSON_PRETTY_PRINT);
// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
// echo json_encode($resList,JSON_PRETTY_PRINT);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $DayWiseData = $resList->Data;
        $Totals = $resList->Totals;
        ?>
            <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width: 60px;">S.No</th>
                        <th style="min-width: 12px;">Po.No</th>
                        <th style="min-width: 180px;">Brand</th>
                        <th style="min-width: 180px;">Variety</th>
                        <th style="min-width: 100px;width: 100px;">Grade</th>
                        <th style="min-width: 100px;width: 100px;">Packing Style</th>
                        <th style="min-width: 100px;width: 100px;">Total Slabs</th>
                        <th style="min-width: 100px;width: 100px;">Total Weight</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($DayWiseData as $item) { ?>
                        <tr>
                            <td style="min-width:50px;"><?php echo $item->SNo; ?></td>
                            <td style="min-width: 100px;width: 100px;"><?php echo $item->PoNo; ?></td>
                            <td style="min-width: 100px;width: 100px;"><?php echo $item->BrandName;?></td>
                            <td style="min-width: 100px;width: 100px;"><?php echo $item->ShortName; ?></td>
                            <td style="min-width: 100px;width: 100px;"><?php echo $item->Grade; ?></td>
                            <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle; ?></td>
                            <td class="Slabs" style="min-width: 100px;width: 100px;"><?php echo number_format($item->Slabs); ?></td>
                            <td class="Kgs" style="min-width: 100px;width: 100px;"><?php echo number_format($item->Kgs, 2); ?></td>
                           
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>

                        <td class="text-center" colspan="2">Grand Totals</td>

                        <td  id="totalSlabs"><?php echo number_format($Totals->Slabs)?></td>
                        <td  id="totalKgs"><?php echo number_format($Totals->Kgs, 2)?></td>
                    </tr>
                </tfoot>
            </table>
            <script>
                $(document).ready(function() {             
                    $('#tblDataTable_filter input').off('keyup').on('keyup', function () {
                        var table = fnShowDataTable();
                        var value = $(this).val();
                        if (value !== '') {
                            table
                                //.column(1)   // change index if needed
                               // .search('^' + value + '$', true, false)
                                .search(value, true, false)
                                .draw();
                        } else {
                            table.search('').draw();
                        }
                    });
                })
            </script>
        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>