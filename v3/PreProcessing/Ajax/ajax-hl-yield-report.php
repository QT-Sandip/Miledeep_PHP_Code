<?php
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$api = API_HL_YIELD_REPORT;

$startDate = trim($_POST["startDate"]);
$endDate = trim($_POST["endDate"]);

$bodyContent = array(
    // "CenterID" => $center,
    "StartDate" => $startDate,
    "EndDate" => $endDate
);
$AsPerStdYieldDisplayFrom = '01-04-2025';

$resList = CallAPI($api, "GET", "", $bodyContent);

if(isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data->DeheadingData;
        $ListTotals = $resList->Data->Totals;
?>
                                            
    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>

                <th>SNo </th>
                <!-- <th>BreakUp ID </td> -->
                <th>Rm Date </th>
                <th>DeHeading Date </th>
                <th>Unit Name </th>
                <th>Lot No </th>
                <th>Agent Name </th>
                <th>Grader Name </th>
                <th>Purchase Weight </th>
                <th>Bill Count </th>
                <th>Chk Count1 </th>
                <th>Chk Count2 </th>
                <th>HL Qty </th>
                <th>Grading Qty </th>
                <th>Hl Yield </th>
                <th>Count Net Yield </th>
                <th>Count Net Qty </th>
                <th>Standard Yield </th>
                <th>As per standard yield % </th>             
                <th>CountNet Yield Perc </th>

                <th>Count Rate</th>
                <th>Standard yield Amount</th>
                <th>Count Net Yield Amount</th>

                <th>Supervisor </th>
                <th>Batch1 </th>
                <th>Batch2 </th>
                <th>Batch3 </th>
                <th>Batch4 </th>
                <th>Batch5 </th>
                <th>Batch6 </th>

                <!-- <th>Sl No</th>
                <th>Deheading Date</th>	
                <th>RM Date	</th>
                <th>Unit</th>
                <th>Lot Number</th>
                <th>Agent Name</th>	
                <th>Grader Name</th>	
                <th>Purchase Weight</th>	
                <th>Bill Count</th>	
                <th>CHECKING COUNT-1</th>	
                <th>CHECKING COUNT-2</th>	
                <th>HL QTY</th>	
                <th>HL YIELD</th>	
                <th>Count Yield Net</th>	
                <th>Count Net Qty(Kgs)</th>	
                <th>Standard Yield</th>	
                <th>(+) OR (-) Kgs/Count NetYield%</th>	
                <th>Supervisor</th>	
                <th>PHONE NO</th>	
                <th>BATCH 1</th>	
                <th>BATCH 2</th>	
                <th>BATCH 3</th>	
                <th>BATCH 4</th>	
                <th>BATCH 5</th>	
                <th>BATCH 6</th>	
                <th>BATCH 6</th>	 -->
            </tr>
        </thead>
        <tbody>
            <?php foreach($List as $item) { 
                $percentStandardYeild = $item->AsPerStdYieldPerc;
                /* if( strtotime($item->DeHeadingDate) >=
                    strtotime($AsPerStdYieldDisplayFrom)){
                    $percentStandardYeild = round((($item->HLQty / $item->StandardYield) * 100 ) - $item->PurchaseWeight , 3);
                } */
                ?>
            <tr>
                <td><?php echo $item->SNo ?> </td>
                <!-- <td><?php //echo $item->BreakUpID ?> </td> -->
                <td><?php echo $item->RmDate ?> </td>
                <td><?php echo $item->DeHeadingDate ?> </td>
                <td><?php echo $item->UnitName ?> </td>
                <td><?php echo $item->LotNo ?> </td>
                <td><?php echo $item->AgentName ?> </td>
                <td><?php echo $item->GraderName ?> </td>
                <td><?php echo $item->PurchaseWeight ?> </td>
                <td><?php echo $item->BillCount ?> </td>
                <td><?php echo $item->ChkCount1 ?> </td>
                <td><?php echo $item->ChkCount2 ?> </td>
                <td><?php echo $item->HLQty ?> </td>
                <td><?php echo $item->GradingQty ?> </td>
                <td><?php echo $item->HlYield ?> </td>
                <td><?php echo $item->CountNetYield ?> </td>
                <td><?php echo $item->CountNetQty ?> </td>
                <td><?php echo $item->StandardYield ?> </td>
                <td><?php echo $percentStandardYeild;?> </td>
                <td><?php echo $item->CountNetYieldPerc ?> </td>

                <td><?php echo $item->CountRate ?> </td>
                <td><?php echo $item->StdYieldKgsAmount ?> </td>
                <td><?php echo $item->CountNetYieldKgsAmount ?> </td>

                <td><?php echo $item->Supervisor ?> </td>
                <td><?php echo $item->Batch1 ?> </td>
                <td><?php echo $item->Batch2 ?> </td>
                <td><?php echo $item->Batch3 ?> </td>
                <td><?php echo $item->Batch4 ?> </td>
                <td><?php echo $item->Batch5 ?> </td>
                <td><?php echo $item->Batch6 ?> </td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
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
                <td></td>
            </tr>
        </tfoot>
    </table>

    
<?php 
        } else {
                echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
            }
        } 

?>