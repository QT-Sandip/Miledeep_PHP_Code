<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if(isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

if($methodType == "poNumber") {

    $apiDepDropdowns = API_DEPENDANT_DROPDOWNS;
    
    $bodyContent = array(
        'Type' => "Variety",
        'PoID' => trim($_POST["pOID"]),
        // 'varietyID' => trim($_POST["varietyID"])
    );

    $resDepDropdowns = CallAPI($apiDepDropdowns, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resDepDropdowns);

}

if($methodType == "varietyChange") {

    $apiDepDropdowns = API_DEPENDANT_DROPDOWNS;
    
    $bodyContent = array(
        'Type' => "Grades",
        'PoID' => trim($_POST["pOID"]),
        'VarietyID' => trim($_POST["varietyID"]),
        'PoGradeID' => trim($_POST["poGradeID"])
    );

    $resDepDropdowns = CallAPI($apiDepDropdowns, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resDepDropdowns);

}

if($methodType == "getSingleStock") {

    // $api = API_DAILY_INWARD_DETAILS;
    $api = API_DAILY_FREEZING_INWARD_DETAILS;
        
    $bodyContent = array(
        "ProductionID" => $_POST["ProductionID"],
        "StockID" => $_POST["stockId"],
        "FreezingID" => $_POST["stockId"],
        "BarCodeID" => $_POST["barCodeID"],
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    $resSingleStock = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resSingleStock);
    // $List = $resSingleStock->StockData->Data[0];
    $List = $resSingleStock->FreezingData->Data[0];
    header('Content-Type: application/json');
    echo json_encode($List);

}

if($methodType == "getProdData") {

       $api = API_DAILY_PRODUCTION_DETAILS;
        
    $bodyContent = array(
        "ProductionID" => $_POST["prodDataId"],
        "ProductionDate" => $_POST["prodDate"]
    );

    $resProdData = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resProdData, JSON_PRETTY_PRINT);

    $List = $resProdData;
    header('Content-Type: application/json');
    echo json_encode($resProdData);

}

if($methodType == "getRackList") {

    $api = API_STORE_RACK_DETAILS;
     
    $bodyContent = array(
        "BlockID" => trim($_POST["blockID"])
    );

    $resRackData = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resRackData, JSON_PRETTY_PRINT);
    header('Content-Type: application/json');
    echo json_encode($resRackData->RackData);

}

if($methodType == "create" || $methodType == "getList") {

     $api = API_DAILY_FREEZING_INWARD_DETAILS;

    if($methodType == "create") {

        $bodyContent = array(
            "ProductionID" => $_POST["ProductionID"],
            "FreezingID" => $_POST["stockID"],
            "BarCodeID" => $_POST["barCodeID"],
            //"ProductionDate" => $_POST["txtProductionDate"],
            "TransactionDate" => $_POST["txtTransactionDate"],
            /*
            "Group" => $_POST["drpGroup"],
            "StockHolderID" => $_POST["drpStockBelongs"],
            "ColdstoreID" => $_POST["drpStore"],
            "PlantID" => $_POST["drpProductionPlant"],
            "Shift" => $_POST["drpShift"],
            "ProductionCode" => $_POST["txtProductionCode"],
            "BatchCode" => $_POST["txtBatchCode"],
            "PoID" => $_POST["drpPoNumber"],
            "Variety" => $_POST["drpVariety"],
            "PackingType" => $_POST["drpPackingType"],
            "PackingStyle" => $_POST["drpPackingStyle"],
            "Grade" => $_POST["drpGrade"],
            "FreezingType" => $_POST["drpFreezingType"],
            "Glaze" => $_POST["txtGlaze"],
            "Soaking" => $_POST["drpSoaking"],
            //"Salt" => "", //$_POST["txtSalt"],
            "ProductCategory" => $_POST["drpProductCategory"],
            "AbStatus" => $_POST["drpAbStatus"],
            // "TubNo" => ,
            // "LineNumber" => ,
            */
            "Remarks" => $_POST["txtRemarks"],
            "ArrivalDate" => $_POST["txtArrivalDate"],
            "ArrivalID" => $_POST["drpLotCode"],
            "HLCount" => $_POST["drpHLCount"],
            "Mcs" => $_POST["txtMcs"],
            "LooseBags" => $_POST["txtLooseBags"],
            "TubNo" => $_POST["txtTubNo"],
            //"PoGradeID" => $_POST["poGradeID"],
        );

        $res = CallAPI($api, "POST", "", $bodyContent);

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
        // exit;
        // echo json_encode($res, JSON_PRETTY_PRINT);
        
    } else if($methodType == "getList") {
        
        $bodyContent = array(
            "StockID" => "",
            "BarCodeID" => $_POST["barCodeID"],
        );

        $res = CallAPI($api, "GET", "", $bodyContent);

    }


    if (isset($res) && !empty($res)) {

        $error = $res->Error;

        if (!isset($error) || empty($error)) {
            $list = $res->FreezingData->Data;
    ?>

        <div class="card">
            <input type="hidden" id="hdnBarCodeID" name="hdnBarCodeID" value="<?php echo $res->FreezingData->BarCodeID ?>"/>
            <input type="hidden" id="hdnBalanceKgs" name="hdnBalanceKgs" value="<?php echo $res->FreezingData->BalanceKgs ?>"/>

            <div class="card-body">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:50px;">S.No</th>
                            <th>Production Plant</th>
                            <th>Production Date</th>
                            <th>Transaction Date</th>
                            <th>Group</th>
                            <th>Stock Belongs to</th>
                            <!-- <th>Store</th>-->
                            <th>Shift</th> 
                            <th>Production Code</th>
                            <th>Batch Code</th>
                            <th>Po Number</th>
                            <th>Variety</th>
                            <th>Packing Type</th>
                            <th>Packing Style</th>
                            <th>Grade</th>                                                
                            <th>Freezing Type</th>                                                
                            <th>Glaze(%)	</th>
                            <th>Soaking</th>
                            <th>Product Category</th>
                            <th>Ab Status</th>
                            <th>Remarks</th>
                            <th>Mcs</th>
                            <th>Loose Bags</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $i = 1;
                        foreach($list as $item) { 
                    ?>
                        <tr>
                        <!-- "StockID":"10",
                        "BarCodeID":"17", -->
                        <?php // echo $item->StockHolderID ?>
                        <?php // echo $item->PlantID ?>
                        <?php // echo $item->Coldstore ?>
                        <?php // echo $item->ShiftID ?>
                        <?php // echo $item->VarietyID ?>
                        <?php // echo $item->PoGradeId ?>
                        <?php // echo $item->PackingStyleID ?>
                            <td><?php echo $i ?></td>
                            <td><?php echo $item->ProductionPlantName ?></td>
                            <td><?php echo $item->ProductionDate ?></td>
                            <td><?php echo $item->TransactionDate ?></td>
                            <td><?php echo $item->Group ?></td>
                            <td><?php echo $item->StockHolderName ?></td>
                            <!-- <td><?php // echo $item->StoreName ?></td> -->
                            <td><?php echo $item->Shift ?></td>
                            <td><?php echo $item->ProductionCode ?></td>
                            <td><?php echo $item->BatchCode ?></td>
                            <td><?php echo $item->PoNumber ?></td>
                            <td><?php echo $item->Variety ?></td>
                            <td><?php echo $item->PackingType ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->Grade ?></td>                                                
                            <td><?php echo $item->FreezingType ?></td>                                                
                            <td><?php echo $item->Galze ?></td>
                            <td><?php echo $item->Soaking ?></td>
                            <td><?php echo $item->ProductCategory ?></td>
                            <td><?php echo $item->AbStatus ?></td>
                            <td><?php echo $item->Remarks ?></td>
                            <td><?php echo $item->Mcs ?></td>
                            <td><?php echo $item->LooseBags ?></td>
                            <td>
                                <button type="button" id="btnEdit" name="btnEdit"  data-stockid="<?php echo $item->FreezingID ?>" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                    <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                </button>
                                <button type="button" id="btnDelete" name="btnDelete" data-stockid="<?php echo $item->FreezingID ?>" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                    <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                </button>
                            </td>
                        </tr>
                        <?php $i++; } ?>
                    </tbody>
                </table>
            </div>
        </div>
            
    <?php 
        } else{
            echo '<div>ERROR: '. $error .'</div>';
        }
    }

} 

if($methodType == "placePalette") {
    
    $apiPlacePallet = API_PLACE_PALLET;
    
    $bodyContent = array(
        'BarCodeID' => trim($_POST["barCodeID"]),
        'RackID' => trim($_POST["rackID"])
    );

    $resPlacePallet = CallAPI($apiPlacePallet, "POST", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resPlacePallet);

}

if($methodType == "getArrivalCodes") {

    $api = API_RM_PRODUCTION_LIST;

    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $Type = trim($_POST["Type"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        'Type' => $Type,
        'ArrivalPlant' =>"",
        'Supplier' =>"",
        'PsId' => ""
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    // var_dump($resList);
    // exit;
    header('Content-Type: application/json');
    echo json_encode($resList);
}

if($methodType == "getHLCount") {
    $api = API_VALUE_ADDITION_DETAILS;

    $ArrivalID = trim($_POST["ArrivalID"]);

    $bodyContentPost = array(
        "ArrivalID" => $ArrivalID
    );

    $result = CallAPI($api, "GET", "", $bodyContentPost);

    $error = $result->Error;
    $msg = $result->Msg;

    $HLCounts = $result->Data->HLCounts;

    $response = array('hlCounts' => $HLCounts, "Error"=> $error, "Msg" => $msg);
    echo json_encode($response);

}
?>