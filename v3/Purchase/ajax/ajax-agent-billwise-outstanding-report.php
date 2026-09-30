<?php
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$api = API_AGENT_BILL_REPORT;

$Plant = trim($_POST["Plant"]);
$Center = trim($_POST["Center"]);
$AgentID = trim($_POST["Supplier"]);
$StartDate = trim($_POST["startDate"]);

$bodyContent = array(
    'Plant' => $Plant,   
    'CenterID' => $Center,
    'AgentID' => $AgentID,
    'StartDate' => $StartDate,

);
echo json_encode($bodyContent, JSON_PRETTY_PRINT);

$result = CallAPI($api, "GET", "", $bodyContent);

//  json_encode($result, JSON_PRETTY_PRINT);

$error = $result->Error;
$List = $result->Data->AgentBillwiseReportData;
// echo json_encode($List, JSON_PRETTY_PRINT);
// exit;

?>
  
<?php  if (!isset($error) || empty($error)) { ?>
    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>	
                <th>Agent</th>
                <th>Pan/GST</th>
                <th>Bill Date</th>	
                <th>Bill Number</th>	
                <th>Bill Amount</th>	
                <th>Paid Amount</th>	
                <th>OS Amount</th>	
                	
            </tr>
        </thead>
        <tbody>
            <?php foreach ($List as $Items) { ?>
                <tr>
                    <td><?php echo $Items->SNO ?></td>	
                    <td><?php echo $Items->Supplier ?></td>
                    <td><?php echo $Items->PanNumber ?></td>
                    <td><?php echo $Items->BillDate ?></td>	
                    <td><?php echo $Items->BillNo ?></td>	
                    <td><?php echo $Items->BillAmount ?></td>	
                    <td><?php echo $Items->PaidAmount ?></td>	
                    <td><?php echo $Items->OSAmount ?></td>	
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <td></td>
            <td></td>
            <td></td>
            <td>Total</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tfoot>
    </table>
<?php 
   } else {
     echo "Note : ". $error;
   } 
?>