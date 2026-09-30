<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


if(isset($_POST["type"]) && !empty($_POST["type"])) {

    $type = $_POST["type"];

    $apiUpdate = API_ADD_PACKING_DATA;

    $OrderId = trim($_POST["OrderId"]);
   // $PoGradId = trim($_POST["PoGradId"]);
    $ApprovaldDate = trim($_POST["ApprovaldDate"]);
    $OrderedDate = trim($_POST["OrderedDate"]);
    $OrderRemarks = trim($_POST["OrderRemarks"]);
    $MasterCases = trim($_POST["MasterCases"]);
    $InnerBags = trim($_POST["InnerBags"]);
  $ListPrority = trim($_POST["ListPrority"]);

    $body = array(
        "OrderID"=> $OrderId,
        //"POGradeId"=> $PoGradId,
        "ApprovalDate"=> $ApprovaldDate,
        "OrderedDate"=> $OrderedDate,
        "OrderRemarks"=> $OrderRemarks,
        "MasterCases"=> $MasterCases,
        "InnerBags"=> $InnerBags,
        "Priority"=> $ListPrority
    );   

    $resUpdate = CallAPI($apiUpdate, "POST", "", $body);

    header('Content-Type: application/json');
    echo json_encode($resUpdate);

} else {



$api = API_PACKING_REPORT_DATA;

$exporterID = trim($_POST["exporterID"]);
$processorID = trim($_POST["processorID"]);
$consigneeID = trim($_POST["consigneeID"]);
$brandID = trim($_POST["brandID"]);
 $prority = trim($_POST["prority"]); 

$bodyContent = array(
    'Exporter' => $exporterID,
    'Processor' => $processorID,
    'Buyer' => $consigneeID,
    'Brand' => $brandID,
    'Priority' => $prority
);

$resList = CallAPI($api, "POST", "", $bodyContent);
// var_dump($resList);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit();


if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
?>
        <div class="card">
            <div class="card-body">

                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="min-width: 60px;" class="sticky sno">S.No</th>	
                            <th style="min-width: 200px" class="sticky poNumber">Po Number</th>	
                            <th style="min-width: 200px" >Buyer</th>
                            <th  style="min-width: 200px">Destination</th>
                            <th  style="min-width: 200px">Shipment</th>
                            <th  style="min-width: 200px">Brand</th>	
                            <th  style="min-width: 200px">Processor</th>
                            <th  style="min-width: 200px">Pack</th>	
                            <th  style="min-width: 200px">Form</th>
                            <th  style="min-width: 200px">Variety</th>			
                            <th  style="min-width: 200px">Grade</th>		
                            <th  style="min-width: 200px">Spec</th>		
                            <th  style="min-width: 200px">B.UP</th>
                            <th  style="min-width: 200px">Confirmation Date</th>
                            <th  style="min-width: 200px">Order Date</th>		
                            <th  style="min-width: 200px">Order Remarks</th>		
                            <th  style="min-width: 200px">Master Cases</th>		
                            <th  style="min-width: 200px">Inner Bags</th>		   		
                            <th  style="min-width: 200px">Priority</th>	  
                            <th  style="min-width: 60px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($List as $item) { ?>
                            <tr>
                                <td class="sticky sno"><?php echo $item->SNO ?></td>	
                                <td class="sticky poNumber"><?php echo $item->PoNumber ?></td>
                                <td><?php echo $item->Buyer ?></td>		
                                <td><?php echo $item->Destination ?></td>
                                <td><?php echo $item->Shipment ?></td>
                                <td><?php echo $item->Brand ?></td>	
                                <td><?php echo $item->Pro ?></td>	
                                <td><?php echo $item->Pack ?></td>
                                <td><?php echo $item->Form ?></td>	
                                <td><?php echo $item->Variety ?></td>
                                <td><?php echo $item->Grade ?></td>			
                                <td><?php echo $item->Spec ?></td>		
                                <td><?php echo $item->Bup ?></td>
                                <td><?php echo '<input  type="date" class="form-control txtApprovaldDate" value="'.$item->ConfirmationDate.'" data-date="'.$item->ConfirmationDate.'" />'; ?></td>
                                <td><?php echo '<input  type="date" class="form-control txtOrderedDate" value="'.$item->OrderDate.'"  data-date="'.$item->OrderDate.'" />'; ?></td>
                                <td><?php echo '<input  type="text" class="form-control txtOrderRemarks" value="'.$item->OrderRemarks.'"  />'; ?></td>		
                                <td><?php echo '<input  type="text" class="form-control txtMasterCases" value="'.$item->MasterCases.'" />'; ?></td>		
                                <td><?php echo '<input  type="text" class="form-control txtInnerBags" value="'.$item->InnerBags.'" />' ?></td>		   		
                                <td>                                                                      
                                    <select class="form-control drpSelect drpListPrority">
                                        <option value="">Select Priority</option>
                                        <option value="High" <?php echo $item->Priority == "High" ? "selected" : "" ?> >High Priority</option>
                                        <option value="Low" <?php echo $item->Priority == "Low" ? "selected" : "" ?> >Low Priority</option>
                                    <select>                                    
                                </td>	  
                                <td>
                                    <?php 
                                        echo '<button data-orderid="'.$item->OrderID.'" data-pogradid="'.$item->POGradeId.'" align="Center" class="btn btn-success btn-sm btn-flat btnSavePackOrder"> Save </button>';
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>

<?php

    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}

}

?>