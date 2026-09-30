<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);
// $transactionType = trim($_POST["transactionType"]);

if (isset($methodType) && !empty($methodType)) {

    $api = API_MATERAIL_DISPATCH_CREATE;

    if($methodType == "GetList"){
        $fromStation = trim($_POST["fromStation"]);
        $toStation = trim($_POST["toStation"]);
        $transactionType = trim($_POST["transactionType"]); 
        $datatype = trim($_POST["datatype"]);
        $fromdate = trim($_POST["fromdate"]);
        $todate = trim($_POST["todate"]);

        $bodyContentPost = array(
            "FromStation" => $fromStation,
            "ToStation" => $toStation,
            "ToDate" => $todate,
            "FromDate" => $fromdate,
            "DataType" => $datatype   
            
        );

        $result = CallAPI($api, "GET", "", $bodyContentPost);  
            $List = $result->Data->DispatchMaterialList;          
  
    }

  
     else if ($methodType == "delete") {

        $MaterialDispatchID = $_POST["MaterialDispatchID"];

        $bodyContent = array(
            "MaterialDispatchID" => $MaterialDispatchID
        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }  
  
}
?>
        <div class="">
            <div class="">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>S.No</th>
                            <th>Batch No</th>
                            <th>From station</th>
                            <th>To station</th>
                            <th>Transferred Date</th>
                            <th>Grade</th>
                            <th>Variety</th>
                            <th>Quantity</th>
                            <th>Dispatched Crates</th>

                            <?php if(trim($_POST["transactionType"]) == "Receiver"){ ?>
                                <th>Received Crates</th>
                                <th>Difference</th>
                            <?php } ?>
                            <th>Status</th>

                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($List as $item) { ?>
                            <tr>
                                <td><?php echo $item ->SNo?></td>
                                <td><?php echo $item ->BatchNumber ?></td>
                                <td><?php echo $item ->FromStationName ?></td>
                                <td><?php echo $item ->ToStationName ?></td>
                                <td><?php echo $item ->FormattedTransferDate ?></td>
                                <td><?php echo $item ->Grade?></td>
                                <td><?php echo $item ->VarietyName ?></td>
                                <td><?php echo $item ->Quantity ?></td>
                                <td><?php echo $item ->DispatchedCrates ?></td>

                                <?php if(trim($_POST["transactionType"]) == "Receiver"){ ?>
                                    <td><?php echo $item ->ReceivedCrates ?></td>
                                    <td><?php echo $item ->Difference ?></td>
                                <?php } ?>
                                <td> <div class="bg-active color-palette text-center">
                                <span class="btn btn-link badge badge-warning font-size-10"      style="background:<?php echo $item->StatusColour ?>; color:white"> 
                                       <?php echo $item->StatusName ?></span>
                                </div></td>
                                <td style="text-align: center">
                                    <?php if ($item->EditAndDeleteAccess==1) { ?>
                                        <button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-material-dispatch-id="<?php echo $item->MaterialDispatchID?>" 
                                        data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
                                            <i class="mdi mdi-trash-can font-size-16"></i></button>
                                    
                                        <a href='<?php  echo "material-dispatchment-create?MaterialDispatchID=" . $item->MaterialDispatchID ."&TransactionType=$transactionType" ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit"
                                            data-original-title="Edit" target="_blank">
                                            <i class="mdi mdi-pencil font-size-16"></i>
                                        </a>
                                    <?php  } ?>
                                </td>
                            </tr>
                        <?php  } ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php


?>