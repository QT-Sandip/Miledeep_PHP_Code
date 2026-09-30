<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

if ($methodType == "getProdData") {

    $api = API_DAILY_PRODUCTION_DETAILS;

    $bodyContent = array(
        "ProductionID" => $_POST["prodDataId"],
        "ProductionDate" => $_POST["prodDate"]
    );

    $resProdData = CallAPI($api, "GET", "", $bodyContent);

    $List = $resProdData;
    header('Content-Type: application/json');
    // echo json_encode($resProdData);
}

if ($methodType == "getList") {

    $api = API_IBT_OUT;

    $PageNo = $_POST["pageNo"];
    $bodyContent = array(
        "IBTRequestID" => $_POST["ibtRequestID"],
        "ProductionDate" => $_POST["productionDate"],
        "ProductionID" => $_POST["productionID"],
        "PackingType" => $_POST["packingType"],
        "ColdStore" => $_POST["coldStore"],
        "VarietyID" => $_POST["variety"],
        "ShortName" => $_POST["shortName"],
        "PoID" => $_POST["poNumber"],
        "Blocks" => $_POST["blocks"],
        "Grade" => $_POST["grade"],
        "PackingStyle" => $_POST["pckgStyle"],
        "Species" => $_POST["species"],
        "FreezingType" => $_POST["freezingType"],
        "Soaking" => $_POST["fsoaking"],
        "TypeofProduct" => $_POST["productType"],
        "SearchKey" => $_POST["searchKey"],
        "PageNo" => $_POST["pageNo"],
        "LoadAll" => $_POST["loadAll"],
        "TransferDate" => $_POST["stockDate"],
        "IBTOutReason" => $_POST["stockOutReason"],
        "RequestStatus" => $_POST["requestStatus"],
        "PDCategory" => $_POST["pDCategory"],
        "SoakingType" => $_POST["soakingType"],
        "abStatus" => $_POST["abStatus"],
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
    //echo '<pre>';print_r($resList);die;
    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;
    $error = $resList->Error;
    $PageCount = $resList->PageCount;
    $resTotal = $resList->TotalCount;
    $List = $resList->DailyStockOutData;
    $TotalKgs = $resList->TotalKgs;
    $SumTotalMcs = $resList->SumTotalMcs;
    $SumTotalLooseBags = $resList->SumTotalLooseBags;
    $SumLooseBags = $resList->SumLooseBags;
    $SumMcs = $resList->SumMcs;

    if (isset($error) && !empty($error)) {
        echo '
            <div class="card">
                <div class="card-body"> Note: ' . $error . ' </div>
            </div>';
    } else {
        echo '
        <div class="card">
            <div class="card-body"> 
        <table  id="tblDataTable" data-paging="false" class="table table-hover table-bordered dataTable no-footer" >
            <thead class="thead-light">
                <tr>
                    <th class="cellCheckbox text-center" style="min-width: 80px;">
                        <!--<input type="checkbox" class="big-checkbox" id="checkall" /> -->
                    </th>
                    <th style="min-width:55px;">S.No</th>
                    <th>Location</th>
                    <th style="min-width:55px;">PO.No</th>
                    <th>Production Date</th>	
                    <th>Transaction Date</th>	
                    <th>Variety</th>	
                    <th>Grade</th>
                    <th>Species</th>
                    <th>FreezingType</th>
                    <th>Soaking</th>
                    <th>TypeofProduct</th>
                    <th>Total Mcs</th>
                    <th>Total Loose Bags</th>
                    <th>Mcs</th>
                    <th>Loose Bags</th>
                    <th>Packing Style</th>
                    <th>Packing Type</th>
                    <th>Category</th>
                    <th>AB Status</th>
                    <th>Store Name</th>
                    <th>Store Kgs</th>
                </tr>
            </thead>
            <tbody>';
        $i = 1;
        foreach ($resList->DailyStockOutData as $item) {
            echo '
                    <tr class="rowid' . $item->SNo . '">
                        <td class="tdCheckList text-center" >
                            <input type="checkbox" class="rowCheck big-checkbox" data-prodid="' . $item->ProductionID . '"  value="' . $item->SNo . '" />
                        </td>
                        <td>' . $item->SNo . '</td>	
                        <td>' . $item->Location . '</td>
                        <td>' . $item->PoNumber . '</td>	
                        <td>' . date('d-m-Y', strtotime($item->ProductionDate)) . '</td>	
                        <td>' . date('d-m-Y', strtotime($item->TransactionDate)) . '</td>	
                        <td class="' . ($i == 1 ? "tdVariety" : "") . '">' . $item->Variety . '</td>
                        <td class="' . ($i == 1 ? "tdGrade" : "") . '">' . $item->Grade . '</td>
                        <td>' . $item->Species . '</td>	
                        <td>' . $item->FreezingType . '</td>
                        <td>' . $item->Soaking . '</td>
                        <td>' . $item->TypeofProduct . '</td>
                        <td id="' . $item->SNo . '-txtTotalMCS">' . $item->TotalMcs . '</td>
                        <td id="' . $item->SNo . '-txtTotalLooseBags">' . $item->TotalBags . '</td>
                       
                        <td data-Mcs=""> 
                            <div class="col-md-6 col-lg-3 mb-3">                        
                                <input type="text" style="width: 150px;" class="form-control txtMCS" placeholder="Enter MCS" id="' . $item->SNo . '-txtMCS" name="txtMCS" value="' . $item->Mcs . '" data-mcs="' . $item->Mcs . '" />
                            </div>
                        </td>  
                        <td data-Bags="">
                            <div class="col-md-6 col-lg-3 mb-3">                        
                                <input id="' . $item->SNo . '-txtLooseBags" type="text" style="width: 150px;" class="form-control txtLooseBags" data-bags="' . $item->Bags . '" placeholder="Enter Loose Bags"
                                    ' . ($item->Bags == "0" ? "disabled" : "") . '
                                value="' . $item->Bags . '" />
                            </div>
                        </td>
                        <td class="' . ($i == 1 ? "tdPackingStyle" : "") . '">' . $item->PackingStyle . '</td>
                        <td>' . $item->PackingType . '</td>
                        <td>' . $item->ProductCategory . '</td>
                        <td>' . $item->AbStatus . '</td>
                        <td>' . $item->StoreName . '</td>
                        <td>
                            ' . $item->Kgs . '
                            <input type="hidden" class="hdnStockId" value="' . $item->StockID . '" />
                            <input type="hidden" class="hdnBarCodeID" value="' . $item->BarCodeID . '" />
                            <input type="hidden" class="hdnStoreID" value="' . $item->StoreID . '" />
                            <input type="hidden" class="hdnRackID" value="' . $item->RackID . '" />
                            <input type="hidden" class="hdnBlockID" value="' . $item->BlockID . '" />
                            <input type="hidden" class="hdnProductionDate" value="' . $item->ProductionDate . '" />
                            <input type="hidden" class="hdnAvailStock" value="' . $item->AvailableStock . '" />
                        </td>

                    </tr>';
            $i++;
        }
        echo '
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td><b>Totals</b></td>
                    <td></td>	
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                     <td>' . $SumTotalMcs . '</td>
                     <td>' . $SumTotalLooseBags . '</td>
                     <td>' . $SumMcs . '</td>
                     <td>' . $SumLooseBags . '</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>' . $TotalKgs . '</td>
                </tr>
            </tfoot>
        </table>
            <div class="mt-3 mb-3">Showing 1 to ' . count($List) . ' out of ' . $resTotal . ' entries</div>';
        $activepagestyle = "border:2px solid #74788d";

        echo '<div style="display: flex;">
            <input type="hidden" id="hdnTotalRecord" value="' . $List->TotalCount . '" />';

        if ($_POST["loadAll"] != "1") {
            echo '
                    <ul class="pagination pagination-rounded" style="overflow-x: auto;margin: 0px auto;">';
            for ($i = 1; $i <= $PageCount; $i++) {
                $pageActive = ($PageNo == $i ? "active" : "");
                echo '<li class="page-item">
                                    <button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button>
                                </li>';
            }
            echo ' </ul>';
        }
        // }
        echo '
        </div>
        </div>
        </div>';
    }
}
if ($methodType == "stockOut") {

    $api = API_IBT_REQUEST_BreakUp;
    // echo $_POST["stockOutReason"].'<br>' .$_POST["stockOutDate"]; exit;

    $bodyContent = array(
        "ProductionID" => $_POST["productionID"],
        "ProductionDate" => $_POST["productionDate"],
        "StockOutDate" => $_POST["stockOutDate"],
        "StockOutReason" => $_POST["stockOutReason"],
        "ProductionID" => $_POST["productionID"],
        "TotalMsc" => $_POST["totalMsc"],
        "TotalLooseBags" => $_POST["totalLooseBags"],
        "IBTRequestID" => $_POST["ibtRequestID"],
        "RequestStatus" => $_POST["requestStatus"],
        "ToStore" => $_POST["toStore"],
        "PlantID" => $_POST["FromPlantID"],
        "FromStore" => $_POST["FromStoreID"],
        "StockItems" => $_POST["stockItems"]

    );


    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;
    $res = CallAPI($api, "POST", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);
}
