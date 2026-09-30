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

if($methodType == "getProdData") {

    $api = API_DAILY_PRODUCTION_DETAILS;
        
    $bodyContent = array(
        "ProductionID" => $_POST["prodDataId"],
        "ProductionDate" => $_POST["prodDate"]
    );

    $resProdData = CallAPI($api, "GET", "", $bodyContent);

    $List = $resProdData;
    header('Content-Type: application/json');
    echo json_encode($resProdData);

}


if($methodType == "getList") {

    $api = API_STOCKOUT;
        
    $bodyContent = array(
        "ProductionDate" => $_POST["productionDate"],
        "ProductionID" => $_POST["productionID"],
        "PackingType" => $_POST["packingType"],
        "ColdStore" => $_POST["coldStore"],
        "VarietyID" => $_POST["variety"],
        "PoID" => $_POST["poNumber"],
        "StockBelongsTo" => $_POST["stockBelongsTo"],
        "Grade" => $_POST["grade"],
        "FreezingType" => $_POST["freezingType"],
        "ProductCategory" => $_POST["productCategory"],
        "Soaking" => $_POST["soaking"],
        "AbStatus" => $_POST["abStatus"],
        "Group" => $_POST["group"],
        "Brand" => $_POST["brand"],
        "TypeProduct" => $_POST["typeProduct"],
        "Species" => $_POST["species"],
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    $error = $resList->Error;
    if(isset($error) && !empty($error)) {
            echo '
            <div class="card">
                <div class="card-body"> Error: '.$error .' </div>
            </div>';
    }else {        
        echo '
        <div class="card">
            <div class="card-body"> 
        <table  id="tblDataTable" data-paging="false" class="table table-hover table-bordered dataTable no-footer" >
            <thead class="thead-light">
                <tr>
                    <th class="cellCheckbox text-center" style="min-width: 80px;">
                        <input type="checkbox" class="big-checkbox" id="checkall" />
                    </th>
                    <th style="min-width:55px;">S.No</th>
                    <th style="min-width:55px;">PO.No</th>
                    <th>Variety</th>	
                    <th>Grade</th>
                    <th>Mcs</th>
                    <th>Loose Bags</th>
                    <th>Packing Style</th>
                    <th>Packing Type</th>
                    <th>Category</th>
                    <th>Store Name</th>
                    <th>Location</th>
                    <th>Kgs</th>

                    <!--<th>RackID</th>
                    <th>StockID</th>
                    <th>BarCodeID</th>
                    <th>StoreID</th>-->
                </tr>
            </thead>
            <tbody>';
                $i = 1;
                foreach($resList->DailyStockOutData as $item) { 
                    echo '
                    <tr>
                        <td class="tdCheckList text-center" >
                            <input type="checkbox" class="rowCheck big-checkbox" data-prodid="'.$item->ProductionID.'"  value="'. $item->SNo.'" />
                        </td>
                        <td>'.$item->SNo  .'</td>	
                        <td>'.$item->PoNumber.'</td>	
                        <td class="'.($i == 1 ? "tdVariety" : "").'">'.$item->Variety .'</td>
                        <td class="'.($i == 1 ? "tdGrade" : "").'">'.$item->Grade .'</td>
                        <td data-Mcs=""> 
                            <div class="col-md-6 col-lg-3 mb-3">                        
                                <input type="text" style="width: 150px;" class="form-control txtMCS" placeholder="Enter MCS" id="" name="txtMCS" value="'.$item->Mcs.'" data-mcs="'.$item->Mcs.'" />
                            </div>
                        </td>  
                        <td data-Bags="">
                            <div class="col-md-6 col-lg-3 mb-3">                        
                                <input type="text" style="width: 150px;" class="form-control txtLooseBags" data-bags="'.$item->Bags.'" placeholder="Enter Loose Bags"
                                    '.($item->Bags == "0" ? "disabled" : "").'
                                value="'.$item->Bags.'" />
                            </div>
                        </td>
                        <td class="'.($i == 1 ? "tdPackingStyle" : "").'">'.$item->PackingStyle .'</td>
                        <td>'.$item->PackingType .'</td>
                        <td>'.$item->ProductCategory.'</td>
                        <td>'.$item->StoreName.'</td>
                        <td>'.$item->Location.'</td>
                        <!-- <td>'.$item->RackName.'</td>-->
                      
                        <td>
                            '.$item->Kgs .'
                            <input type="hidden" class="hdnStockId" value="'.$item->StockID .'" />
                            <input type="hidden" class="hdnBarCodeID" value="'.$item->BarCodeID .'" />
                            <input type="hidden" class="hdnStoreID" value="'.$item->StoreID .'" />
                            <input type="hidden" class="hdnRackID" value="'.$item->RackID .'" />
                            <input type="hidden" class="hdnBlockID" value="'.$item->BlockID .'" />
                        </td>

                    </tr>';
                    $i ++;
                }
            echo '
            </tbody>
        </table>
        </div>
        </div>';
    }
    // $List = $resProdData;
    // header('Content-Type: application/json');
    // echo json_encode($resProdData);

}

if($methodType == "stockOut") {

    $api = API_STOCKOUT;
        
    $bodyContent = array(
        "ProductionID" => $_POST["productionID"],
        "ProductionDate" => $_POST["productionDate"],
        "StockOutDate" => $_POST["stockOutDate"],
        "StockOutReason" => $_POST["stockOutReason"],       
        "Remarks" => $_POST["remarks"],       
        "TotalMsc" => $_POST["totalMsc"],
        "TotalLooseBags" => $_POST["totalLooseBags"],
        "StockItems" => $_POST["stockItems"],
        "TransTypeId" => $_POST["selectedTransTypeId"],

    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;
    $res = CallAPI($api, "POST", "", $bodyContent);
    // echo json_encode($res, JSON_PRETTY_PRINT);
    // exit;

    header('Content-Type: application/json');
    echo json_encode($res);

}

?>