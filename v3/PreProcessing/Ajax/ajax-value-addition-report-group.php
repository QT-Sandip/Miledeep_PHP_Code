<?php
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';


$api = API_VALUE_ADDITION_REPORT_GROUP;

$Plant = trim($_POST["Plant"]);
$startDate = trim($_POST["startDate"]);
$endDate = trim($_POST["endDate"]);
$station = trim($_POST["station"]);
$IsPlantFilter = trim($_POST["IsPlantFilter"]);

$bodyContent = array(
    'FromDate' => $startDate,
    'ToDate' => $endDate,
    'PlantID' => $Plant,
    'Station' => $station,
    'IsPlantFilter' => $IsPlantFilter,
    'ProductionShifts' => $_POST['ProductionShifts']

);

$result = CallAPI($api, "GET", "", $bodyContent);
//	echo '<pre>';print_r($result);die;
// echo json_encode($result);
// exit;

?>
              

<?php
$error = $result->Error;
$Totals = $result->ValueAdditionDetails->Totals;

// if (!isset($result) && !empty($result)) {

    $List = $result->ValueAdditionDetails->ValueAdditionData;
    
    // if (isset($List) && !empty($List)) {
        ?>

              
    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
             
                <th style="width:50px;">S.No</th>
                                    <th>Arrival Date</th>
                                    <th>Batch Number</th>
                                    <th style="min-width: 100px;">Track Code</th>
                                    <th>Production Plant</th>
                                    <th>Station</th>
                                    <th>Value Addition Date</th>
                                    <th>Shift</th>
                                    <th>Variety </th>
                                    <th>Grade </th>
                                    <th>HL Wt(Kgs)</th>
                                    <th>HL Count</th>
                                    <th>VA Out Quantity</th>
                                    <th>Yield</th>
                                    <th>Remarks</th>

            </tr>
        </thead>
        <tbody>
            <?php 
            if (isset($List) && !empty($List)) {
             foreach ($List as $Items) { 
                echo '
            <tr>
                <td> '. $Items->SNo .' </td>
                 <td> '. $Items->ArrivalDate .' </td>
                <td> '. $Items->BatchNumber .' </td>
                 <td> '. $Items->TrackCode .' </td>
                <td> '. $Items->ProductionPlant .' </td>
                <td> '. $Items->Station .' </td>
                <td> '. $Items->ValueAdditionDate .' </td>
                <td> '. $Items->Shift .' </td>
                <td> '. $Items->Variety .' </td>
                <td> '. $Items->Grade .' </td>
                <td class="HLWtKgs"> '. $Items->HLWtKgs .' </td>
                <td> '. $Items->HLCount .' </td>
                <td class="VAOutQty"> '. $Items->WtAftrVA .' </td>
                <td class="Yield"> '. $Items->Yield .' </td>
                <td> '. $Items->Remarks .' </td>         
            </tr>'; } 
            
            } else {
                echo "
                <td style='display: none'>&nbsp;</td>
                  <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td colspan='12'> <div class='text-center'> No data available </div></td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                <td style='display: none'>&nbsp;</td>
                 ";
            }
            ?>
            <?php // } ?>
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
                <td></td>	
                <td></td>
                <td></td>	
                <td id="TotalHLWtKgs"><?php echo $Totals->TotalHLWtQuantity ?></td>	
                <td></td>	
                <td id="TotalVAOutQty"><?php echo $Totals->TotalWtAftrVA ?></td>	
                <td id="TotalYield"><?php echo $Totals->TotalYield ?></td>
                <td></td>	
	
               	
            </tr>
        </tfoot>
    </table>
    <?php
    // } else {
    //     echo "<div class='text-center'>" . $error . "</div>";
    // }
//} 
?>