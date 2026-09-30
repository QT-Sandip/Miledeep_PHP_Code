<?php
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$gradOverallReportViewChg = $ArrayClientConfigData['gradOverallReportViewChg'];
$api = API_GRADING_SUMMERY_REPORT;


$bodyContent = array(
    'GradingDate' => trim($_POST["GradingDate"]),
    'Plant' => trim($_POST["Plant"]),
);


$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
        $GradeData = $List->GradeData;
        $CombinationData = $List->VarietyCombinationData;
        $VarietyWiseTotals = $List->VarietyWiseTotals;
        ?>
        <div style="overflow-x: auto;">
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Grade</th>
                        <?php 
                        foreach($CombinationData as $variety){?>
                            <th><?php echo $variety->Variety;?></th>
                        <?php 
                        }?>
                    </tr>
                </thead>
                <tbody>                    
                    <?php 
                    foreach($GradeData as $Grade){?> 
                        <tr>
                            <td><?php echo $Grade->Grade;?></td>
                            <?php 
                            foreach($Grade->GradeValueData as $value){?>
                                <td><?php echo $value;?></td>
                           <?php }?>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <?php 
                        foreach($VarietyWiseTotals as $Total){?> 
                            <td><?php echo $Total;?></td>
                        <?php }?>
                    </tr>
                </tfoot>
            </table>
        </div>
    
    <?php
    }else{
        echo "Error: " . $error;
    }
}else{
    echo "<div class='alert alert-danger'>No data found. Please check your request parameters.</div>";
}
?>
   