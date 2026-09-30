<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$api = API_IBT_REPORT;

$bodyContent = array(
    "FromDate" => trim($_POST["fromDate"]),
    "ToDate" => trim($_POST["toDate"]),
    "Plant" => trim($_POST["plant"]),
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {

        $ArrayIBTReportData = $resList->ArrayIBTReportData;
?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 100px;width: 100px;">Plant</th>
                    <th style="min-width: 100px;width: 100px;">Order Number</th>
                    <th style="min-width: 130px;width: 130px;">IBT Transfer Date</th>
                     <?php if($ShowBatchCode == 1){?>
                    <th style="min-width:80px;">Arrival Date</th>
                    <th style="min-width:80px;">Track Code</th>
                    <th style="min-width:80px;">Batch Code</th>
                    <?php }?>
                    <?php if($ProductBatchCode == 1){?>
                    <th>Arrival Code</th>
                    <?php }?>
                    <th style="min-width: 100px;width: 100px;">Variety</th>
                    <th style="min-width: 100px;width: 100px;">Packing Style</th>
                    <th style="min-width: 100px;width: 100px;">Grade</th>
                    <th style="min-width: 100px;width: 100px;">Out MCS</th>
                    <th style="min-width: 100px;width: 100px;">Out Loose Bags</th>
                    <th style="min-width: 100px;width: 100px;">Remarks</th>
                    <th style="min-width: 100px;width: 100px;">Balance MCS</th>
                    <th style="min-width: 100px;width: 100px;">Balance Loose Bags</th>

                    <th style="min-width: 100px;width: 100px;">Receiving Plant</th>
                    <th style="min-width: 130px;width: 130px;">Transaction Date</th>
                    <th style="min-width: 130px;width: 130px;">Production Date</th>
                    <th style="min-width: 100px;width: 100px;">Variety</th>
                    <th style="min-width: 100px;width: 100px;">Packing Style</th>
                    <th style="min-width: 100px;width: 100px;">Grade</th>
                    <th style="min-width: 100px;width: 100px;">Received MCS</th>
                    <th style="min-width: 100px;width: 100px;">Received Loose Bags</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ArrayIBTReportData as $item) { ?>
                    <tr>
                        <td style="min-width:50px;"><?php echo $item->SNO ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutFromPlant ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OrderNumber  ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutTransactionDate ?></td>
                          <?php 
                                if($ShowBatchCode == 1){?>
                                  <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>

                                <td style="min-width:80px;"><?php echo $item->TrackCode ?></td>

                                    <td><?php echo $item->LotNumber ?></td>
                                <?php }?>
                                 <?php 
                                if($ProductBatchCode == 1){?>
                                    <td><?php echo $item->ProdBatchCode ?></td>
                                <?php }?>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutVariety ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutPackingStyle ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutGrade ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutMcs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutLooseBags ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Remarks ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->BalanceMcs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->BalanceLooseBags ?></td>
                        <td style="padding: 0px !important;" colspan="12">
                            <table class="subTable table table-bordered mb-0" style="width: 100%">
                                <?php
                                foreach ($item->BreakUpData as $itemBreak) {  ?>
                                    <tr>
                                        <td style="min-width: 120px;max-width: 120px;"><?php echo $itemBreak->InToPlant ?> </td>
                                        <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->InTransactionDate ?></td>
                                        <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->InProductionDate ?></td>
                                        <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->InVariety ?></td>
                                        <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->InPackingStyle ?></td>
                                        <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->InGrade ?></td>
                                        <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->InMcs ?></td>
                                        <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->InLooseBags ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
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
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
            </tr> -->
        </table>
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . htmlspecialchars($error) . "</div>";
    }
}
?>