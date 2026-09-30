<?php 
$totalHonQty = !empty($resDataPrint->HoToFinishedReportData->Totals->totalHonQty) ? $resDataPrint->HoToFinishedReportData->Totals->totalHonQty : 0;
$totalFinishedQty = !empty($resDataPrint->HoToFinishedReportData->Totals->totalFinishedQty) ? $resDataPrint->HoToFinishedReportData->Totals->totalFinishedQty : 0;
?>
<table class="tblMain">
    <tr>
        <td>
            <h1 class="page-title">Head On To Finished Report</h1>
        </td>
        </tr>
    <tr>
        <td>
            <table class="bt bl br bb w100 f-12">
                <tr>
                    <td class="p-0 tblHeader">
                        <table class="w100">
                            <tr class="bb">
                                <td class="br" style="min-width:98px;"></td>
                                <!-- <td colspan="3" class="br">COASTAL AQUA PVT LTD.</td> -->
                                <td colspan="4" class="br"  style="text-align: center;">
                                    <?php echo $ClientName ?>
                                </td>
                                <td style="min-width:98px"></td>
                            </tr>
                            <tr class="bb">
                                <td class="br"  style="min-width:98px;"></td>
                                <td colspan="4" class="br"  style="text-align: center;">HO TO FINISHED
                                    PRODUCT YIELD SUMMARY</td>
                                <td></td>
                            </tr>
                            <tr class="bb">
                                <td class="br" colspan="3">DATE :
                                    <?php echo $purchaseData->Date ?>
                                </td>
                                <td class="br"  style="min-width:98px;"></td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="bb">
                                <td class="br" colspan="3">BATCH NO :
                                    <?php echo $BatchNumber ?>
                                </td>
                                <td class="br"  style="min-width:98px;"></td>
                                <td colspan="3">HO TO FINISHED YIELD % :
                                    <?php echo $yieldsData->HoToFinishedYield ?>
                                </td>
                            </tr>
                            <tr class="bb">
                                <td class="br" colspan="3">PLACE :
                                    <?php echo $purchaseData->Place ?>
                                </td>
                                <td class="br"  style="min-width:98px;"></td>
                                <td colspan="3">AB Status : <span
                                        style="background-color: <?php echo $abStatus->Colour; ?>"><?php echo $abStatus->Name; ?></span>
                                </td>
                            </tr>
                            <tr class="bb">
                                <td class="br" colspan="3">PURCHASE COUNT :
                                    <?php echo $PurchaseCount ?>
                                </td>
                              <td class="br"  style="min-width:98px;"></td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="bb">
                                <td class="br" colspan="3">PLANT COUNT :
                                    <?php echo $ReceivingCount ?>
                                </td>
                               <td class="br"  style="min-width:98px;"></td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="bb">
                                <td class="br" colspan="3">PURCHASE HO QUANTITY
                                    : <?php echo $HeadOnWeight ?></td>  
                                <td class="br"  style="min-width:98px;"></td>                              
                                <td colspan="3"></td>
                            </tr>
                            <tr class="bb text-center">
                                <td class="br">HON COUNT</td>
                                <td class="br" style="min-width: 120px">HON QUANTITY</td>                                
                                <td class="br" colspan="2" style="min-width: 180px">PACKING</td>
                                <td colspan="2" style="min-width: 100px">FINISHED QUANTITY</td>
                            </tr>
                            <?php 
                            foreach ($hTFRptData as $item) {
                                $ArrivalCount = $item->ArrivalCount;
                                $ArrivalWeight = $item->ArrivalWeight;
                                $FreezingVarietyList = explode('&', $item->FreezingVariety);
                                $PackingList = explode('&', $item->Packing);
                                $FinishedQuantityList = explode('&', $item->FinishedQuantity);
                                $max = max(
                                    count($FreezingVariety),
                                    count($PackingList),
                                    count($FinishedQuantityList)
                                );
                                for ($i = 0; $i < $max; $i++) {
                                    echo '<tr class="bb text-center">';
                                        echo '<td class="br">' . (isset($ArrivalCount) ? $ArrivalCount : '') . '</td>';
                                        echo '<td class="br"  style="min-width: 120px">' . (isset($ArrivalWeight) ? $ArrivalWeight : '') . '</td>';
                                    
                                        // Now explode by comma from each &-split Packing and FinishedQuantity
                                        $packingItems = isset($PackingList[$i]) ? explode(',', $PackingList[$i]) : [''];
                                        $finishedItems = isset($FinishedQuantityList[$i]) ? explode(',', $FinishedQuantityList[$i]) : [''];
                                        $freezingVarieties = isset($FreezingVarietyList[$i]) ? explode(',', $FreezingVarietyList[$i]) : [''];
                                    //  echo '<td class="br">' . implode('<br>', $freezingVarieties) . '</td>';
                                        echo '<td class="br" colspan="2" style="min-width: 180px" >' . implode('<br>', $packingItems) . '</td>';
                                        echo '<td class="br" colspan="2" style="min-width: 100px">' . implode('<br>', $finishedItems) . '</td>';
                                    echo '</tr>';
                                }
                            }?>
                            <tr class="bb text-center">
                                <td class="br">Totals</td>
                                <td class="br" style="min-width: 120px"><?php echo $totalHonQty;?></td>                                
                                <td class="br" colspan="2" style="min-width: 180px"></td>
                                <td colspan="2" style="min-width: 100px"><?php echo $totalFinishedQty;?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>