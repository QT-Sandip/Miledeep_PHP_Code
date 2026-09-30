<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$gradOverallReportViewChg = $ArrayClientConfigData['gradOverallReportViewChg'];
$mergeHonLoose = $ArrayClientConfigData['mergeHonLoose'];

$api = API_CONSOLIDATION_REPORT_PACKING_STYLE;

$bodyContent = array(
    'ArrivalDate' => trim($_POST["arrivalDate"]),
    'ReceivingPlantID' => trim($_POST["receivingPlantId"]),
    'IsPlantFilter' => trim($_POST["IsPlantFilter"]),
    'TypeProduct' => trim($_POST["typeProduct"]),
    'Grade' => trim($_POST["grade"]),
    'MergeHonLoose' => $mergeHonLoose,
    'FromDate' => trim($_POST["FromDate"]),
    'ToDate' => trim($_POST["ToDate"]),
    'drpType' => trim($_POST["drpType"]),
);

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $GradingData = $resList->Data->GradingData;
        $GradingList = $GradingData->List;
        $GradingWeights = $GradingData->Weights;
        $GradingProductTypeWeights = $GradingData->ProductTypeWeights;

        $FreezingData = $resList->Data->FreezingData;
        $CombinationData = $FreezingData->VarietyCombinationData;
        //echo '<pre>';print_r($CombinationData);die;
        $PackingStyle = $FreezingData->PackingStyle;
        ?>
        <style>
            .table-sticky {
                table-layout: fixed;
                width: max-content;
                border-collapse: collapse;
            }

            .table-sticky th,
            .table-sticky td {
                white-space: nowrap;
                min-width: 50px;
            }

            .table-sticky thead th {
                position: sticky;
                top: 0;
                background: #fff;
                z-index: 3;
            }

            .sticky {
                position: sticky;
                background: #fff;
                z-index: 4;
            }

            .stickyCol1 {
                left: 0px;
            }

            .stickyCol2 {
                left: 50px;
            }

            .stickyCol3 {
                left: 100px;
            }

            .stickyCol4 {
                left: 150px;
            }

            .stickyCol5 {
                left: 200px;
            }


            .footer-bold {
                font-family: 'Arial Black';
                /* Bold font */
                font-weight: 700;

                color: #000;
                /* default black */
                text-align: center;
                vertical-align: middle;
                font-size: 12px;

            }
        </style>

        <div style="overflow-x: auto;">
            <div class="row">     
                <?php 
                if(!empty($GradingList)){?>
                    <div class="col-md-<?php echo empty($CombinationData) ? '12' : '6';?>" >
                        <h4>Consolidation Report</h4>
                        <table id="table1" class="table table-bordered w-100 table-hover">
                            <thead>
                                <tr> 				
                                    <th>SNO</th>
                                    <th>Variety</th>
                                    <th>Grade</th>
                                    <th>Count</th>
                                    <th>Treatment</th>
                                    <th>Totals</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach($GradingList as $GradingItem){?>
                                    <tr>					
                                        <td><?php echo $GradingItem->SNo;?></td>
                                        <td><?php echo $GradingItem->Variety;?></td>
                                        <td><?php echo $GradingItem->PackingGrade;?></td>
                                        <td><?php echo $GradingItem->HLCount;?></td>
                                        <td><?php echo $GradingItem->Treatment;?></td>
                                        <td><?php echo $GradingItem->HLWeight;?></td>
                                    </tr>
                                <?php }
                                
                                if(!empty($GradingWeights->HonWeight) || !empty($GradingWeights->HLWeight)){?>
                                    <tr> 				
                                        <td></td>
                                        <td>Totals</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>HOn : <?php echo (float)$GradingWeights->HonWeight;?><br>
    HL : <?php echo (float)$GradingWeights->HLWeight;?></td>
                                    </tr>
                                <?php 
                                }
                                $yeild =  ((float) $GradingWeights->HLWeight / (float) $GradingWeights->HonWeight) * 100;
                                if(!empty($yeild)){       ?>
                                    <tr> 				
                                        <td></td>
                                        <td>Yield</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            
                                            Grand Yield : <?php echo round($yeild, 2);?> %</td>
                                    </tr>
                                <?php
                                } 
                                if(!empty($GradingProductTypeWeights)){?>
                                    <tr> 				
                                        <td></td>
                                        <td>Type Product</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                        <?php echo $GradingProductTypeWeights;?>    
                                        </td>
                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                <?php }
                if(!empty($CombinationData)){?>
                    <div class="col-md-<?php echo empty($GradingList) ? '12' : '6';?> ">
                        <table id="table2" class="table w-100  table-hover table-bordered">
                                <thead class="thead-light">
                                    
                                    <tr>
                                        <th style="width: 50px; vertical-align: middle; text-align: center;" class="">SNO</th>
                                        <th style="vertical-align: middle; text-align: center;" class="">Packing Style</th>
                                        <th style="vertical-align: middle; text-align: center;" class="">Grade</th>
                                        <?php 
                                        if(!empty($CombinationData)){
                                            foreach($CombinationData as $varietyData){?>
                                                <th style="vertical-align: middle; text-align: center;" class=""><?php echo $varietyData->Variety;?></th>
                                            <?php 
                                            }?>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if(!empty($PackingStyle)){
                                        $sno = 0;
                                        foreach($PackingStyle as $item){?>
                                            <tr>
                                                <td style="vertical-align: middle; text-align: center;"><?php echo ++$sno;?></td>
                                                <td style="vertical-align: middle; text-align: center;"><?php echo $item->PackingStyle;?></td>
                                                <td style="vertical-align: middle; text-align: center;"><?php echo $item->Grade;?></td>
                                                <?php 
                                                if(!empty($item->GradeValueData)){
                                                    foreach($item->GradeValueData as $GradeValue){?>
                                                        <td style="vertical-align: middle; text-align: center;"><?php echo $GradeValue;?></td>
                                                    <?php }?>
                                                <?php } ?>                                   
                                            </tr>
                                        <?php   
                                        }
                                    }?>                  
                                    
                                </tbody>
                            </table>
                    </div>
                <?php }?>
            </div>

        </div>
    <?php } else{
         echo "<div class='alert alert-danger'>$error</div>";
     }?>
<?php
} else {
    echo "<div class='alert alert-danger'>No data found. Please check your request parameters.</div>";
}
?>