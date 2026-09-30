<?php 
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GRADING_REPORT_V3;

$bodyContent = array(
    "FromDate" => trim($_POST["FromDate"]),
    "ToDate" => trim($_POST["ToDate"]),
    "PsId" => trim($_POST["PsId"]),
);

$result = CallAPI($api, "POST", "", $bodyContent);

//echo '<pre>';print_r($result);die;
if(empty($result->Error)){
    $Results = $result->Results;
    $Grades = $Results->Grades;
    $GradeBreakUp = $Results->GradeBreakUp;
    $ArrayDayGrades = [];
    ?>
    <style>
        th,td {
            text-align: center !important;
            vertical-align: middle !important;
        }
    </style>
    <table id="ConsutaBPM" border="1" cellspacing="0" cellpadding="1" class="table table-hover table-bordered">
        <thead class="thead-light">            
            <tr>
                <th style="min-width:150px;"><B>Grading Machine</B></font></th>
                <th style="min-width:120px;"><B>Rm Date </B></font></th>
                <th style="min-width:120px;"><B>Grading Date </B></font></th>
                <th style="min-width:100px;"><B>Lot No</B></font></th>
                <th style="min-width:150px;"><B>Grader Name</B></font></th>
                <th style="min-width:150px;"><B>Agent Name</B></font></th>
                <th style="min-width:80px;"><B>Rm Count</B></font></th>
                <th align="CENTER"><B>Rm Qty</B></font></th>
                <th align="CENTER"><B>HL Count/KG</B></font></th>
                <th align="CENTER"><B>HL Qty</B></font></th>
                <?php 
                    foreach($Grades as $Grade){?>
                        <th  align="CENTER"><B><?php echo $Grade->Grade;?></B></font></th>
                <?php } ?>
                <th align="CENTER"><B>Grading Total</B></font></th>
                <th align="CENTER"><B>Grading Yield</B></font></th>
                <th align="CENTER"><B>Variation HL To Grading</B></font></th>
                <th align="CENTER"><B>CountNet Yield Perc</B></font></th>
                <th align="CENTER"><B>Standard Yield</B></font></th>
                <th align="CENTER"><B>Avg Count</B></font></th>
                <th align="CENTER"><B>Diff Count</B></font></th>
            </tr>
        </thead>
        <tbody>
            <?php $SNo = 0;
            foreach($GradeBreakUp as $GradeBreakUpItem){?>
                <tr>
                    <!-- <td><?php echo ++$SNo;?></td> -->
                    <td><?php echo $GradeBreakUpItem->MachineName;?></td>
                    <td><?php echo $GradeBreakUpItem->RmDate;?></td>
                    <td><?php echo $GradeBreakUpItem->GradingDate;?></td>
                    <td><?php echo $GradeBreakUpItem->LotNumber;?></td>
                    <td><?php echo $GradeBreakUpItem->GraderName;?></td>
                    <td><?php echo $GradeBreakUpItem->AgentName;?></td>
                    <td><?php echo $GradeBreakUpItem->PurchaseCount;?></td>
                    <td><?php echo $GradeBreakUpItem->ProductionWeight;?></td>
                    <td><?php echo $GradeBreakUpItem->HLCount;?></td>
                    <td><?php echo $GradeBreakUpItem->HLQty;?></td>
                    <?php 
                        foreach($GradeBreakUpItem->TotalData as $GradeItems){?>
                            <td  align="CENTER"><B><?php echo $GradeItems->WEIGHT;?></B></font></td>
                    <?php } ?>
                    <td><b><?php echo $GradeBreakUpItem->GradQty == 0 ? 'NA' : $GradeBreakUpItem->GradQty;?></b></td>
                    <td><?php echo $GradeBreakUpItem->GradYield;?></td>
                    <td style="<?php echo $GradeBreakUpItem->QtyVariation < 0 ? 'color:red' : '';?>"><?php echo $GradeBreakUpItem->QtyVariation;?></td>
                    <td><?php echo $GradeBreakUpItem->CountNetYieldPerc;?></td>
                    <td><?php echo $GradeBreakUpItem->standardYieldPerc;?></td>
                    <td><?php echo $GradeBreakUpItem->GradCount;?></td>
                    <td style="<?php echo $GradeBreakUpItem->CountVariation < 0 ? 'color:red' : '';?>"><?php echo $GradeBreakUpItem->CountVariation;?></td>
                </tr>
                <?php 
                $SNO++;
                foreach($GradeBreakUpItem->TotalData as $D){
                    $ArrayDayGrades[] = $D;
                }
            }?>
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?php echo $Results->RmWeighTotal;?></td>
                <td></td>
                <td><?php echo $Results->HLWEIGHTALL;?></td>
                <?php 
                foreach($Grades as $Grade){
                    $weight = 0;
		            $T_HL = 0;
                    foreach ($ArrayDayGrades as $RowGrades) {
                        //foreach ($RowGrades as $RowGrade) {
                            if ($RowGrades->GRADE == $Grade->Grade) {
                                $weight += $RowGrades->WEIGHT;
                                $T_HL += $RowGrades->WEIGHT;
                                $T_HL += $RowGrades->WEIGHT;
                            }
                        //}
                    }?>
                    <td><?php echo $weight;?></td>
                <?php
                } 
                $ArrayDayGrades = [];
                $HL_GRADING_VAR = $Results->HLWEIGHTALL - $Results->GradingTotal;
                ?>
                <td><b><?php echo $Results->GradingTotal;?></b></td>
                <td align="CENTER" ><B></B></font></td>
                <td align="CENTER" ><B><?php echo $Results->GradingTotal - $Results->HLWEIGHTALL;?></B></font></td>

                <td align="CENTER" ><B></B></font></td>
                <td align="CENTER" ><B></B></font></td>
                <td align="CENTER" ><B></B></font></td>
                <td align="CENTER" ><B></B></font></td>
            </tr>
        </tfoot>
        <?php 
        $DAY_PUR_TOT = 0;
        $T_HL = 0;
        ?>
    </table>
        
    <?php
}