<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

function fnTableFullData($result)
{
    $DailyProductionData = $result->DailyProductionData;
    $DailyProductionList = $result->DailyProductionList;
    if (isset($result) && !empty($result)) {
        $error = $result->Error;
        
           // echo '<pre>';print_r($result);die;
        if (isset($result->StockList) && !empty($result->StockList)) {
            $list = $result->StockList->Data;
            $html = getTableData($list);            
        }
    }
    $res = array(
        "Error" => $result->Error,
        "Msg" => $result->Msg,
        "StockData" => $result->StockData,
        "UnitID" => end($result->StockList->Data)->UnitID,
        "BarCodeID" => $result->StockList->BarCodeID,
        "HtmlRes" => $html,
    );

    return $res;
}

function getTableData ($list, $PageCount = '', $TotalCount = '', $PageNo = '',$StockType = ''){
    //page no condition for list page ie. store or on floor page
    $html = '
    <div class="card">
        <!--<input type="hidden" id="hdnBarCodeID" name="hdnBarCodeID" value="' . $result->StockList->BarCodeID . '"/> 
        <input type="hidden" id="hdnBalanceKgs" name="hdnBalanceKgs" value="' . $result->StockList->BalanceKgs . '"/>-->
        <div class="card-body">
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                    '.(!empty($PageNo)?
                        '<th class="cellCheckbox text-center" style="min-width: 80px;">
                            <input type="checkbox" class="big-checkbox" id="checkall" />
                        </th>':'').'
                        <th style="width:50px;">S.No</th>
                        <th>BID</th>
                        <th>Bill No</th>
                        <th>Inward Date</th>
                        <th>Production Plant</th>
                        <th>Store</th>
                        <th>Brand</th>
                        <th>PackingType</th>
                        <th>Po Number</th>
                        <th>Grade</th> 
                        <th>Variety</th> 
                        <th>SlabWeight</th>                                               
                        <th>Packing Style</th> 
                        <th>Sizes</th>
                        <th>Dimentions</th>                                                
                        <th>Colours</th>  
                        <th>Group</th>  
                        <th>Stock Qty</th>
                        '.(!empty($PageNo)?'<th>Utilize Qty</th>':'')
                        .(!empty($PageNo)?'<th>Dummy Stock Qty</th>':'').'  
                        '.(!empty($PageNo)?'<th>Damaged Stock Qty</th>':'').
                        (!empty($PageNo)?'<th>Available Stock Qty</th>':'').
                        (!empty($PageNo)?'<th>Location</th>':'').'
                        <th>Created On</th>
                        <th style="min-width: 110px">Actions</th>
                    </tr>
                </thead>
                <tbody>';
                $i = 1;
                $sno = '';
                foreach ($list as $item) {
                    if(empty($sno)) {
                        $sno = !empty($item->SNO) ? $item->SNO : $i;
                    }                    
                    $html .= '
                    <tr>'
                        .(!empty($PageNo)?'
                            <td class="tdCheckList text-center" >'
                                .(
                                    
                                    //(in_array($item->Group,['Mcs','Pouches']) && !empty($item->PoNo) && !empty($item->ShowCheckBox))?
                                   (!empty($item->ShowCheckBox))?
                                    '<input type="checkbox" class="rowCheck big-checkbox" data-prodid="' . $item->ProductionID . '"  value="' . $item->ID . '" />'
                                    :''
                                ).'
                            </td>':''
                        ).
                        '<td>' . (!empty($item->SNO) ? $item->SNO : $i) . '</td>
                        <td>' . $item->BarCodeID . '</td>
                        <td class="tdBillNo">' . $item->BillNo . '</td>
                        <td>' .date("d-m-Y", strtotime($item->TransactionDate)) . '</td> 
                        <td>' . $item->ProductionPlantName . '</td>  
                        <td class="tdStoreName" data-storeid="' . $item->StoreID . '">' . $item->StoreName . '</td>                     
                        <td class="tdBrand" data-bid="' . $item->Brand . '">' . $item->BrandName . '</td>
                        <td>' . $item->PackingTypeID . '</td>                          
                        <td>' ;
                            if(!empty($item->PoNo)){
                                if($item->DummyPoNo){
                                    $html .= 'Transfer '.$item->DummyPoNo.' To ';
                                }
                                $html .= $item->PoNo;
                            }else{
                                if(!empty($item->DummyPoNo)){
                                    $html .= 'Dummy : '.$item->DummyPoNo;
                                }
                                else{
                                    $html .= '';
                                }
                            }
                        $html .= '</td>
                        <td class="tdGrade">' . $item->Grade . '</td>     
                        <td class="tdVariety">' . $item->Variety . '</td>
                        <td class="tdSlabWeight" data-slab_weight_id="' . $item->SlabWeightId . '">' . $item->SlabWeight . '</td>                                            
                        <td class="tdPackingStyleID" data-packing_style_id="' . $item->PackingStyleID . '">' . $item->PackingStyle . '</td>    
                        <td class="tdSizes">' . $item->Sizes . '</td>
                        <td class="tdDimention">' . $item->Dimention . '</td>
                        <td class="tdColours">' . $item->Colours . '</td>
                        <td class="tdGroup">' . $item->Group . '</td>';
                            $stockQty = $usedStockQty = 0;
                            if($item->Group == 'Mcs') {
                                $stockQty = $item->Mcs;
                                $usedStockQty = (int)$item->UtilizedMcs + (int)$item->DummyStock + (int)$item->DamagedStock;
                                $html .= '<td class="tdMcs">' . $item->Mcs . '</td>' .(!empty($PageNo)?'<td>' . $item->UtilizedMcs . '</td>':''); 
                            }elseif($item->Group == 'Pouches') {
                                $stockQty = (int)$item->LooseBags  + (int)$item->DummyStock + (int)$item->DamagedStock;
                                $usedStockQty = $item->UtilizedPouches; 
                                $html .=  '<td class="tdLooseBags">' . $item->LooseBags . '</td>'.(!empty($PageNo)?'<td>' . $item->UtilizedPouches . '</td>':'') ;
                            }elseif($item->Group == 'Spacers') { 
                                $stockQty = $item->Spacers;
                                $usedStockQty = $item->UtilizedSpacers;
                                $html .= '<td class="tdSpacers">' . $item->Spacers . '</td>'.(!empty($PageNo)?'<td>' . $item->UtilizedSpacers . '</td>':'') ;
                            }elseif (in_array($item->Group, ['Stickers','Tape Rolls','Count Slips'])) { 
                                $stockQty = $item->Count;
                                $usedStockQty = $item->UtilizedCountVal;
                                $html .= '<td class="tdCount">' . $item->Count . '</td>'.(!empty($PageNo)?'<td>' . $item->UtilizedCountVal . '</td>':'') ; 
                            }else { 
                                $html .='<td></td>' .(!empty($PageNo)?'<td></td>':'');
                            }
                         $html .= (!empty($PageNo)?'<td>' . $item->DummyStock . '</td>':'').'
                        '.(!empty($PageNo)?'<td>' . $item->DamagedStock . '</td>':'').'
                        '.(!empty($PageNo)?'<td>' . $item->AvailableQty . '</td>':'').'
                        '.(!empty($PageNo)?'<td>' . $item->Location . '</td>':'').'
                        <td class="tdBillNo">' .date("d-m-Y H:i:s", strtotime($item->CreatedOn))  . '</td>
                        <td>';
                            if($PageNo != ''){
                               // if ($StockType != '') {
                                    if ($item->StockFloorStatus == 0) {
                                        $html .= '
                                        <a class="mr-3 text-primary aListAdd" href="javascript:void(0)" " data-storeRackId=" 0"
                                            data-stockid="'. $item->StockID .'">
                                            <i class="mdi mdi-plus font-size-18"></i>
                                        </a>&nbsp;&nbsp;

                                        <!--<a target="_blank" class="mr-3 text-primary"
                                            href="packing-store-view-store-layout.php?BarCodeId='. $item->BarCodeID .'&StockID='. $item->StockID .'">
                                            <i class="mdi mdi-open-in-app font-size-18"></i>
                                        </a>&nbsp;&nbsp;-->';
                                        if($usedStockQty == 0){
                                            $html .= '
                                            <a target="_blank" class="mr-3 text-primary"
                                                href="packingstore-inward?BarCodeID='. $item->BarCodeID .'&StockID='. $item->StockID.'">
                                                <i class="mdi mdi-pencil font-size-18"></i>&nbsp;&nbsp;
                                            </a>';
                                        }
                                    } 
                                    if ($item->StockFloorStatus == 1 && !empty($item->AvlDummyQty)) {
                                        $html .= '
                                        <!-- <button class="btn text-primary unplacePallate" data-id="'. $item->BarCodeID .'"
                                            data-stockid="'. $item->StockID .'" data-storeRackId="'. $item->StoreRackID .'"
                                            title="Unplace Palette" data-original-title="Unplace Pallate">
                                            <i class="mdi mdi-login font-size-18  text-danger"></i>
                                        </button> -->

                                        <button class="btn text-primary aListAdd" data-id="'. $item->BarCodeID .'"
                                            data-stockid="'. $item->StockID .'" data-storeRackId="'. $item->StoreRackID .'"
                                            title="Shift Palette" data-original-title="Shift Pallate">
                                            <i class="mdi mdi-vector-intersection font-size-18  text-danger"></i>
                                        </button>';
                                    } 
                                //}
                                /* $html.='
                                <a target="_blank" class="mr-3 text-primary"
                                    href="CsBarCodeLabelPrint.php?BID='. $item->BarCodeID .'&StockID='. $item->StockID .'">
                                    <i class="mdi mdi-printer font-size-18"></i>
                                </a>'; */
                            }else{
                                $html .= '
                                <button type="button" id="btnEdit" name="btnEdit"  data-stockid="' . $item->StockID . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                    <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                </button>
                                <button type="button" id="btnDelete" name="btnDelete" data-stockid="' . $item->StockID . '" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                    <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                </button>';
                            }
                        $html .= '                    
                        </td>
                    </tr>';
                    $i++;
                }
                $html .= '
                </tbody>
            </table>';
            if(!empty($PageCount) && !empty($TotalCount)){
                $html .= '
                <div class="mt-3 mb-3">Showing '.$sno.' to '.$item->SNO.' out of '.$TotalCount.' entries</div>';
                $activepagestyle = "border:2px solid #74788d" ;
                $html .= '
                <div style="display: flex;">
                    <input type="hidden" id="hdnTotalRecords" value=" '.$TotalCount.' " />
                    <ul class="pagination pagination-rounded 
                    '.($PageCount<30?'justify-content-center':'').'
                    " style="overflow-x: auto;margin: 0px auto;">
                        <!-- justify-content-center -->';
                        for ($i = 1; $i <= $PageCount; $i++) {
                            $pageActive = ($PageNo == $i ? "active" : "");
                            $html .=  '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                        }
                    $html .= '
                    </ul>
                </div>';
            }
            $html .= '
            </div>
        </div>
    </div>';
    return $html;
}
if ($methodType == "GetPackingStoreDataByFilters") {
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;
    $bodyContent = $_POST;

    $res = CallAPI($api, "GET", "", $bodyContent);
   // echo '<pre>';print_r($res);die;
    header('Content-Type: application/json');
    $PageNo = !empty($_POST['pageNo'])?$_POST['pageNo']:1;
    echo json_encode(["HtmlRes" =>getTableData($res->Data, $res->PageCount, $res->TotalCount, $PageNo, $_POST['stockType'])]);
}
if ($methodType == "create") {
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;
    $bodyContent = array(
        "StockID" => $_POST["stockID"],
        "TransactionDate" => $_POST["txtTransactionDate"],
        "UnitID" => $_POST["drpColdStores"],
        "Mcs" => $_POST["txtMcs"],
        "LooseBags" => $_POST["txtLooseBags"],
        "Group" => $_POST["drpGroup"],
        "PoNo" => $_POST["poNo"],
        "PckgStyle" => $_POST["pckgStyle"],
        "Grade" => $_POST["grade"],
        "TxtProductionPlant" => $_POST["txtProductionPlant"],

        "Sizes" => $_POST["drpSizes"],
        "Colours" => $_POST["drpColours"],
        "Spacers" => $_POST["txtSpacers"],
        "Brand" => $_POST["drpBrand"],
        "BillNo" => $_POST["txtBillNo"],
        "txtCounts" => $_POST["txtCounts"],
        "PostedUserId" => $_POST["PostedUserId"],

        "drpPackingType" => $_POST["drpPackingType"],
        "drpSlabWeight" => $_POST["drpSlabWeight"],
        "drpVariety" => $_POST["drpVariety"],
        "txtDimention" => $_POST["txtDimention"],
    );


    $res = CallAPI($api, "POST", "", $bodyContent);
    //echo '<pre>';print_r($res);die;
    // echo json_encode($res, JSON_PRETTY_PRINT);
    // exit;

    header('Content-Type: application/json');
    echo json_encode(fnTableFullData($res));
}

if ($methodType == "getList") {
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;
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
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;
    $bodyContent = array(
        "ProductionID" => $_POST["productionID"],
        "StockID" => $_POST["stockID"],
        "BarCodeID" => $_POST["barCodeID"],

    );

    $res = CallAPI($api, "DELETE", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode(fnTableFullData($res));
}
if ($methodType == "getBrandListByPoId") {
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;
    $bodyContent = $_POST;

    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);
}
if ($methodType == "placePaletteFromLayOut") {
    //from packing store view layout page
    $apiPlacePallet = API_DAILY_PACKING_STORE_INWARD_DETAILS;

    $bodyContent = array(
        'BarCodeID' => trim($_POST["barCId"]),
        'RackID' => trim($_POST["rackID"]),
        'StockID' => trim($_POST["stockID"]),
        'methodType' => trim($_POST["methodType"])
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;
    $resPlacePallet = CallAPI($apiPlacePallet, "POST", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resPlacePallet);
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
        'existingRackID' => trim($_POST["existingRackID"]),//from stock or on floor page
        'PostedUserId' => trim($_POST["PostedUserId"]),
        'page' => 'PackingStore'
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
    
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;

    $bodyContent = array(
        "method"=> "PoNumber",
        "ProductionPlantID" => $_POST["txtProductionPlant"],
    );

    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);

}

if ($methodType == "getPoBalance") {
    
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;

    $bodyContent = array(
        "method"=> "PoBalance",
        "PoNumber" => $_POST["poNumber"],
        "Group" => $_POST["drpGroup"],
        "Variety" => $_POST["variety"],
    );

    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);

}
/* END POPUP API CALLS */
if ($methodType == "PoDummyTable") {

    $apiDrp = API_COLDSTORE_DROPDOWNS;

    $bodyContent = array(
        'page'=>'daily-production-list',
        'exclude_list'=>array("StockAvailableVariety","ShipoutPoNumber","PoNumberNew",
        'StockBelongsTo','Shifts','FreezingType','Soaking','ProductCategory',
        'Abstatus','StockInVarietyList','InwardGroups','TransactionTypesNew','CsAllTransactionTypes','ProductionPlant','Processor',
        'Plant','PackingStyle','Grade','Brand','PoNumberFreezing','GeneralCategories','BrandType','CsTransactionTypes','ProductForm','ProductionLines','Variety','VarietyList','Exporters','Consignee','Stock','ProductGroup',
        'Floors','Blocks','StoreRacks','ColdStores','OptType')
    );
    $resDrpList = callAPI($apiDrp, "GET", "", $bodyContent);
     $resPackingType = $resDrpList->Data->PackingType;
    $resPoNumber = $resDrpList->Data->PoNumber;
    //echo '<pre>';print_r($resDrpList);die;

    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;
    $bodyContent =$_POST;

    $res = CallAPI($api, "GET", "", $bodyContent);
    //echo '<pre>';print_r( $res->Data);die;
    $html ='<table id="tblDataTablePop" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width:50px;">S.No</th>
                        <th>BID</th>
                        <th>PoNo</th>
                        <th>SlabWeight</th>
                        <th>Packing Style</th>
                         <th>Group</th>
                        <th>Stock Qty</th>
                        <th>Avl Qty</th>
                        <th style=";">Issued Qty<span  style="color:red"></span></th>
                        <th style=" min-width: 100px;">Packing Type<span  style="color:red">*</span></th>
                        <th style=" min-width: 150px;">PO Number</th>
                        <th>Convert to Dummy</th>
                        <th>Damage Qty</th>
                     </tr>
                </thead> ';
                $i = 0;
                foreach($res->Data as $row){
                    $disabledMain = '';
                    if(in_array($row->Group,['Stickers','Tape Rolls','Count Slips','Spacers']) || !$row->PoNo){
                        $disabledMain = 'disabled';
                    }
                    $html .='
                    <tr>
                        <td>'.$row->SNO.'</td>
                        <td>'.$row->BarCodeID.'</td>
                        <td>'.$row->PoNo.'</td>
                        <td>'.$row->SlabWeight.'</td>
                        <td>'.$row->PackingStyle.'</td>
                        <td>'.$row->Group.'</td>
                        <td>'.$row->qty.'</td>
                        <td>'.$row->AvlDummyQty.'</td>
                        <td style=""><input type="number" class="form-control IssuedQty" id="IssuedQty'.$i.'" name="IssuedQty"
                                  data-id="'.$row->ID.'" data-group="'.$row->Group.'"                  
                            value="" max="'.$row->AvlDummyQty.'" required/>
                        </td>
                        <td style=" min-width: 80px;">
                            <select class="drpSelect form-control packingType select2" id="drpPackingType'.$i.'" name="drpPackingType['.$i.']" required  '.$disabledMain.'>
                                <option value="">Select</option>';
                                foreach ($resPackingType as $item) { 
                                    $html .='<option value="'. $item->Name .'">'. $item->Name.'</option>';
                                } 
                            $html .=' 
                            </select>
                        </td>
                        <td style=" min-width: 150px;">
                            <select class="drpSelect form-control poNo select2" id="drpPoNumber'.$i.'" name="drpPoNumber" '.$disabledMain.'>
                                <option value="">Select</option>';
                                foreach ($resPoNumber as $item) { 
                                    $disabled = '';
                                    if($row->PoID == $item->PoID){
                                        $disabled = 'disabled';
                                    }
                                    $html .='
                                    <option value="'. $item->PoID .'" '.$disabled.'
                                        data-i="'. $i .'"
                                        data-plantid="'. $item->ProductionPlantID .'"
                                        data-grade="'.$item->Grade .'"
                                        data-packingstyleid="'.$item->PackingStyles .'">'. $item->PoNo .'
                                    </option>';
                                } 
                            $html .='
                            </select>
                        </td>
                        <td><input type="number" class="form-control AvlDummyQty" id="AvlDummyQty'.$i.'" name="AvlDummyQty"
                                  data-id="'.$row->ID.'" data-group="'.$row->Group.'"                  
                            value="" max="'.$row->AvlDummyQty.'" '.$disabledMain.' required/>
                        </td>
                        <td><input type="number" class="form-control DamageQty" id="DamageQty'.$i.'" name="DamageQty"
                                  data-id="'.$row->ID.'" data-group="'.$row->Group.'"                   
                            value="" max="'.$row->AvlDummyQty.'" '.$disabledMain.' required/>
                        </td>
                    </tr>';
                    $i++;
                }
               $html .=' 
               </table>
               <script>
               $(document).ready(function() {
                    var table = $("#tblDataTablePop").DataTable({
                        scrollX: true,
                        scrollY: "400px",
                        scrollCollapse: true,
                        paging: false,
                        searching: false,
                        ordering: false,
                        autoWidth: false,
                        deferRender: true,
                        info: false ,

                        initComplete: function () {
                            this.api().columns.adjust();
                        }
                    });

                    // force recalculation after DOM is ready
                    setTimeout(function () {
                        $("#tblDataTablePop").DataTable().columns.adjust();
                    }, 300);

                })
               </script>';

    header('Content-Type: application/json');
    echo json_encode($html);
}
if ($methodType == "ConvertToDummy") {
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;
    $bodyContent =$_POST;
    $res = CallAPI($api, "GET", "", $bodyContent);
    //  echo '<pre>';print_r($res);die;
    header('Content-Type: application/json');
    echo json_encode($res);
}
if ($methodType == "updateDamageQty") {
    $api = API_DAILY_PACKING_STORE_INWARD_DETAILS;
    $bodyContent =$_POST;
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
}
?>