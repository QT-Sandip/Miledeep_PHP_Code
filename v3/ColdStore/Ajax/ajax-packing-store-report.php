<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PACKING_STORE_REPORT;

$bodyContent = array(
    "ProductionPlant" => trim($_POST["plant"]),
    "FromDate" => trim($_POST["fromDate"]),
    "ToDate" => trim($_POST["toDate"]),    
    "ColdStore" => trim($_POST["ColdStore"]),
    "drpGroup" => trim($_POST["drpGroup"]),
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;


if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {

        $Data = $resList->Data;
        $Totals=$resList->Totals;

?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">SNo</th>
                    <!-- <th style="min-width: 100px;width: 100px;">Recieved Date</th>
                    <th style="min-width: 120px;width: 120px;">Bill No</th>
                    <th style="min-width: 120px;width: 120px;">Po Number</th> -->
                    <th style="min-width: 120px;width: 120px;">Brand</th>
                    <th style="min-width: 100px;width: 100px;">Dimentions</th>
                    <!-- <th style="min-width: 100px;width: 100px;">Colour</th> -->
                    <th style="min-width: 100px;width: 100px;">Group</th>
                    <th style="min-width: 100px;width: 100px;">Packing Type</th>
                    <th style="min-width: 100px;width: 100px;">Packing Style</th>
                    <th style="min-width: 100px;width: 100px;">Variety</th>
                    <th style="min-width: 100px;width: 100px;">Opening Balance</th>
                    <th style="min-width: 100px;width: 100px;">Recieved Qty</th>
                    <th style="min-width: 100px;width: 100px;">Utilized Qty</th>
                    <th style="min-width: 120px;width: 120px;">Damaged</th>
                    <th style="min-width: 120px;width: 120px;">Available Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($Data as $item) { ?>
                    <tr>
                        <td style="min-width:50px;"><?php echo $item->SNO ?></td>
                        <!-- <td style="min-width: 100px;width: 100px;"><?php echo $item->TransactionDate ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->BillNo?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PoNo?></td> -->
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->BrandName?></td>
                        <td style="min-width: 120px;width: 100px;"><?php echo $item->Dimention ?></td>
                        <!-- <td style="min-width: 100px;width: 100px;"><?php echo $item->Colour ?></td> -->
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Group ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingTypeID ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Variety ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OpeningBal ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->RecievedQty ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->UtilizedQty ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Damazed ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->AvailableQty ?></td>
                        
                        <!-- <td style="display: none"></td> -->
                    </tr>
                <?php  } ?>
            </tbody>
                <!-- <tr style="font-weight: bold;">
                    <th style="min-width:50px;"></th>
                    <th style="min-width: 100px;width: 100px;"></th>
                    <th style="min-width: 100px;width: 100px;"></th>
                    <th style="min-width: 100px;width: 100px;"></th>
                    <th style="min-width: 100px;width: 100px;"></th>
                    <th style="min-width: 100px;width: 100px;">Grand Totals</th>
                    <th style="min-width: 100px;width: 100px;"></th>
                    <th style="min-width: 100px;width: 100px;"></th>
                    <th style="min-width: 100px;width: 100px;"></th>
                    <th style="min-width: 100px;width: 100px;"></th>
                    <th style="min-width: 100px;width: 100px;"><?php echo $Totals->TotalOpeningBal?></th>
                    <th style="min-width: 100px;width: 100px;"><?php echo $Totals->TotalRecievedQty?></th>
                    <th style="min-width: 100px;width: 100px;"><?php echo $Totals->TotalUtilizedQty?></th>
                    <th style="min-width: 100px;width: 100px;"><?php echo $Totals->TotalDamazed?></th>
                    <th style="min-width: 100px;width: 100px;"><?php echo $Totals->TotalAvailableQty?></th>
                 </tr> -->
        </table>
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . htmlspecialchars($error) . "</div>";
    }
}
?>