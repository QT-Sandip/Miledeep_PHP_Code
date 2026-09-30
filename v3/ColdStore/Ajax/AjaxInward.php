<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = $_SESSION['ClientConfigData'];
$IsDeductGlazeFW = $ClientConfigData['IsDeductGlazeFW'];  //sriram
$IsDeductFWFCGlaze = $ClientConfigData['IsDeductFWFCGlaze'];  //sriram
$MCSRestriction = $ClientConfigData['MCSRestriction'];  //anil
$MixedBatchCode = $ClientConfigData['MixedBatchCode'];
$ShowBatchCode = $ClientConfigData['ShowBatchCode'];

$ClientID = isset($_SESSION['ClientID']) ? $_SESSION['ClientID'] : '';
$ArrivalDisplayName = ($ClientID != 'JLSFV3') ? 'Batch Code' : 'Arrival Code';


if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

function fnTableFullData($result)
{
      global $ArrivalDisplayName, $ClientID, $ShowBatchCode,  $MixedBatchCode;

    $DailyProductionData = $result->DailyProductionData;
    $DailyProductionList = $result->DailyProductionList;

    if (isset($result) && !empty($result)) {

        $error = $result->Error;


        if (isset($result->StockList) && !empty($result->StockList)) {
            $list = $result->StockList->Data;
            $html = '
            <div class="card">
                <!--<input type="hidden" id="hdnBarCodeID" name="hdnBarCodeID" value="' . $result->StockList->BarCodeID . '"/> 
                <input type="hidden" id="hdnBalanceKgs" name="hdnBalanceKgs" value="' . $result->StockList->BalanceKgs . '"/>-->

                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px;">S.No</th>
                                <th>SUPC</th>
                                <th style="min-width:180px;">Product</th>
                                <th>Production Plant</th>
                                <th>Production Date</th>
                                <th>Transaction Date</th>
                                <th>Group</th>
                                <th>Stock Belongs to</th>
                                <th>Store</th>
                                <th>Shift</th>
                                <th>Production Code</th>
                                <th>Production Day Code</th>'.(
                                    ($ShowBatchCode == 1 ||  $MixedBatchCode == 1 || $ClientID == 'JLSFV3')?
                                 '<th>' . $ArrivalDisplayName . '</th>' : ''
                                 ).
                                '<th>Po Number</th>
                                <th>Grade</th>                                                
                                <th>Packing Style</th>                                
                                <th>Packing Type</th>                                
                                <th>Product Category</th>
                                <th>Ab Status</th>
                                <th>Remarks</th>
                                <th>Mcs</th>
                                <th>Loose Bags</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
            $i = 1;
            foreach ($list as $item) {
                $html .= '
                                    <tr>
                                        <td>' . $i . '</td>
                                        <th>' . $item->OurSupc . '</th>
                                        <th>' . $item->ProductDescription . '</th>
                                        <td>' . $item->ProductionPlantName . '</td>
                                        <td>' . $item->ProductionDate . '</td>
                                        <td>' . $item->TransactionDate . '</td>
                                        <td>' . $item->Group . '</td>
                                        <td>' . $item->StockHolderName . '</td>
                                        <td>' . $item->StoreName . '</td>
                                        <td>' . $item->Shift . '</td>
                                        <td>' . $item->ProductionCode . '</td>
                                        <td>' . $item->BatchCode . '</td>'.(
                                            ($ShowBatchCode == 1 ||  $MixedBatchCode == 1  || $ClientID == 'JLSFV3')?
                                            '<td>' . $item->LotNumber . '</td>':''
                                        ).'<td>' . $item->PoNumber . '</td>
                                        <td>' . $item->Grade . '</td>                                                
                                        <td>' . $item->PackingStyle . '</td>                                        
                                        <td>' . $item->PackingType . '</td>                                        
                                        <td>' . $item->ProductCategory . '</td>
                                        <td>' . $item->AbStatus . '</td>
                                        <td>' . $item->Remarks . '</td>
                                        <td class="tdMcs">' . $item->Mcs . '</td>
                                        <td class="tdLooseBags">' . $item->LooseBags . '</td>
                                        <td>
                                            <button type="button" id="btnEdit" name="btnEdit"  data-stockid="' . $item->StockID . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                                <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                            </button>
                                            <button type="button" id="btnDelete" name="btnDelete" data-stockid="' . $item->StockID . '" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                                <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                            </button>
                                        </td>
                                    </tr>';
                $i++;
            }
            $html .= '
                        </tbody>
                    </table>
                </div>
            </div>';
        }
    }

    $res = array(
        "Error" => $result->Error,
        "Msg" => $result->Msg,
        "StockData" => $result->StockData,
        "UnitID" => end($result->StockList->Data)->UnitID,
        "DailyProductionData" => $DailyProductionData,
        "DailyProductionList" => $DailyProductionList,
        "BarCodeID" => $result->StockList->BarCodeID,
        "BalanceKgs" => $result->StockList->BalanceKgs,
        "HtmlRes" => $html,
    );

    return $res;
}


if ($methodType == "create") {
    $api = API_DAILY_INWARD_DETAILS;
    $bodyContent = array(
        "ProductionDate" => $_POST["txtProductionDate"],
        "ProductionID" => $_POST["ProductionID"],
        "StockID" => $_POST["stockID"],
        "BarCodeID" => $_POST["barCodeID"],
        "TransactionDate" => $_POST["txtTransactionDate"],
        "Remarks" => $_POST["txtRemarks"],
        "UnitID" => $_POST["drpColdStores"],
        "Mcs" => $_POST["txtMcs"],
        "LooseBags" => $_POST["txtLooseBags"],
        "Group" => $_POST["drpGroup"],
        "StockBlgsTo" => $_POST["StockBlgsTo"],
        "Shift" => $_POST["shift"],
        "PoNo" => $_POST["poNo"],
        "PoGradeId" =>$_POST["poGradeId"],
        "Variety" => $_POST["variety"],
        "PckgStyle" => $_POST["pckgStyle"],
        "Grade" => $_POST["grade"],
        "PackingType" => $_POST["packingType"],
        "FreezingType" => $_POST["freezingType"],
        "Soaking" => $_POST["soaking"],
        "PDCategory" => $_POST["pDCategory"],
        "Abstatus" => $_POST["abstatus"],
        "TxtProductionPlant" => $_POST["txtProductionPlant"],
        "IsDeductGlaze" => $_POST["IsDeductGlaze"],
        "IsDeductGlazeFW" => $IsDeductGlazeFW,
        "IsDeductFWFCGlaze" => $IsDeductFWFCGlaze,
         "IsStoreProductionLinked" => $_POST["IsStoreProductionLinked"] ,
         "txtBatchNo" => $_POST["txtBatchNo"],
         'MCSRestriction' => $MCSRestriction
    );


    $res = CallAPI($api, "POST", "", $bodyContent);
    //echo '<pre>';print_r($res);die;
    // echo json_encode($res, JSON_PRETTY_PRINT);
    // exit;

    header('Content-Type: application/json');
    echo json_encode(fnTableFullData($res));
}

if ($methodType == "getList") {
    $api = API_DAILY_INWARD_DETAILS;
    $bodyContent = array(
        "ProductionID" => $_POST["prodDataId"],
        "BarCodeID" => $_POST["barCodeID"],
        "StockID" => $_POST["stockId"],
        "ProductionDate" => $_POST["prodDate"],
        "ProductionPlantID" => $_POST["productionPlantID"],
        "IsEdit" => $_POST["isEdit"],
    );

    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode(fnTableFullData($res));
}

if ($methodType == "deleteData") {
    $api = API_DAILY_INWARD_DETAILS;
    $bodyContent = array(
        "ProductionID" => $_POST["productionID"],
        "StockID" => $_POST["stockID"],
        "BarCodeID" => $_POST["barCodeID"]

    );

    $res = CallAPI($api, "DELETE", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode(fnTableFullData($res));
}
?>

<?php

/* POPUP API CALLS */

if ($methodType == "getRackList") {

    $api = API_STORE_RACK_DETAILS;

    $bodyContent = array(
        "BlockID" => trim($_POST["blockID"]),
        "StoreID" => trim($_POST["storeID"]),
        "FloorID" => trim($_POST["floorID"])
    );

    $resRackData = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resRackData->RackData);
}
if ($methodType == "validateRackId") {

    $api = API_STORE_RACK_DETAILS;
    $bodyContent =$_POST;

    $res = CallAPI($api, "GET", "", $bodyContent);
    //echo '<pre>';print_r( $res);die;

    header('Content-Type: application/json');
    echo json_encode($res);
}

if ($methodType == "placePalette") {

    $apiPlacePallet = API_ATTACH_STOCK_TOALREADY_FILLED_PALLET;

    $bodyContent = array(
        'RackID' => trim($_POST["rackID"]),
        'StockID' => trim($_POST["stockID"]),
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;

    // $apiPlacePallet = API_PLACE_PALLET;
    // $bodyContent = array(
    //     'BarCodeID' => trim($_POST["barCodeID"]),
    //     'RackID' => trim($_POST["rackID"])
    // );

    $resPlacePallet = CallAPI($apiPlacePallet, "POST", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resPlacePallet);
}


if ($methodType == "getPoNumber") {
    
    $api = API_DAILY_INWARD_DETAILS;

    $bodyContent = array(
        "method"=> "PoNumber",
        "ProductionPlantID" => $_POST["txtProductionPlant"],
    );

    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);

}

if ($methodType == "getPoBalance") {
    
    $api = API_DAILY_INWARD_DETAILS;

    $bodyContent = array(
        "method"=> "PoBalance",
        "PoNumber" => $_POST["poNumber"],
        "Group" => $_POST["drpGroup"],
        "Variety" => $_POST["variety"],
        "StockID" => $_POST["hdnStockID"],
    );

    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);

}
/* END POPUP API CALLS */
?>