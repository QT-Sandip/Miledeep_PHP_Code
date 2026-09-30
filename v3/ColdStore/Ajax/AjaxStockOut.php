<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = $_SESSION['ClientConfigData'];
$ShowBatchCode = $ClientConfigData['ShowBatchCode'];
$IsStoreProductionLinked = !empty($ClientConfigData['IsStoreProductionLinked']) ? trim($ClientConfigData['IsStoreProductionLinked']):[]; //anil
$convertLooseToMcs = !empty($ClientConfigData['convertLooseToMcs']) ? trim($ClientConfigData['convertLooseToMcs']):[]; //anil
$UserID = !empty($_SESSION['UserID'])?$_SESSION['UserID']:'';

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
    echo json_encode($resProdData);
}

// echo  $_POST["coldStore"]; exit;
// echo  $_POST["productType"]; exit;


if ($methodType == "getList") {

    $api = API_STOCKOUT;

    $PageNo = $_POST["pageNo"];
    $bodyContent = array(
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
        "Soaking" => $_POST["soaking"],
        "TypeofProduct" => $_POST["productType"],
        "SearchKey" => $_POST["searchKey"],
        "PageNo" => $_POST["pageNo"],
        "LoadAll" => $_POST["loadAll"],
        "AbStatus" => $_POST["abStatus"],
        "ProductCategory" => $_POST["productCategory"],
        'IsStoreProductionLinked' => $IsStoreProductionLinked,
    );


    $resList = CallAPI($api, "GET", "", $bodyContent);
    // var_dump($resList);
    $error = $resList->Error;
    $PageCount = $resList->PageCount;
    $resTotal = $resList->TotalCount;
    $List = $resList->DailyStockOutData;
    $TotalKgs = $resList->TotalKgs;
    $TotalMcs = $resList->TotalMcs;
    $TotalBags = $resList->TotalBags;

    if (isset($error) && !empty($error)) {
        echo '
            <div class="card">
                <div class="card-body"> Note: ' . $error . ' </div>
            </div>';
    } else {echo '
<div class="card">
    <div class="card-body"> 
        <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered dataTable no-footer">
            <thead class="thead-light">
                <tr>
                    <th class="cellCheckbox text-center" style="min-width: 60px;">
                        <input type="checkbox" class="big-checkbox" id="checkall" />
                    </th>
                    <th>S.No</th>
                    <th>Location</th>
                    <th>PO.No</th>';

if ($ShowBatchCode == 1) {
    echo '
                    <th style="min-width:80px;">Arrival Date</th>
                    <th style="min-width:80px;">Batch Code</th>';
}

echo '
                    <th>Production Date</th>
                    <th>Transaction Date</th>
                    <th>Variety</th>
                    <th>Grade</th>
                    <th>Species</th>
                    <th>FreezingType</th>
                    <th>Soaking</th>
                    <th>Type of Product</th>
                    <th style="width: 150px;">Mcs</th>
                    <th style="width: 150px;">Loose Bags</th>
                    <th>Packing Style</th>
                    <th>Packing Type</th>
                    <th>Category</th>
                    <th>Store Name</th>
                    <th>Kgs</th>
                </tr>
            </thead>
            <tbody>';
            
$i = 1;
foreach ($resList->DailyStockOutData as $item) {
    echo '
                <tr class="rowid' . $item->SNo . '">
                    <td class="tdCheckList text-center" style="min-width: 60px;">
                        <input type="checkbox" class="rowCheck big-checkbox" data-prodid="' . $item->ProductionID . '" value="' . $item->SNo . '" />
                    </td>
                    <td>' . $item->SNo . '</td>	
                    <td>' . $item->Location . '</td>
                    <td>' . $item->PoNumber . '</td>';
    
    if ($ShowBatchCode == 1) {
        echo '
                    <td style="min-width:80px;">' . $item->ArrivalDate . '</td>
                    <td>' . $item->LotNumber . '</td>';
    }

    echo '
                    <td>' . date('d-m-Y', strtotime($item->ProductionDate)) . '</td>	
                    <td>' . date('d-m-Y', strtotime($item->TransactionDate)) . '</td>	
                    <td class="' . ($i == 1 ? "tdVariety" : "") . '">' . $item->Species . '-' .  $item->Variety . '</td>
                    <td class="' . ($i == 1 ? "tdGrade" : "") . '">' . $item->Grade . '</td>
                    <td>' . $item->Species . '</td>	
                    <td>' . $item->FreezingType . '</td>
                    <td>' . $item->Soaking . '</td>
                    <td>' . $item->TypeofProduct . '</td>
                    <td data-Mcs=""> 
                        <div class="col-md-6 col-lg-3 mb-3">                        
                            <input type="text" style="width: 150px;" class="form-control txtMCS" placeholder="Enter MCS" id="' . $item->SNo . '-txtMCS" name="txtMCS" value="' . $item->Mcs . '" data-mcs="' . $item->Mcs . '" />
                        </div>
                    </td>  
                    <td data-Bags="">
                        <div class="col-md-6 col-lg-3 mb-3">                        
                            <input id="' . $item->SNo . '-txtLooseBags" type="text" style="width: 150px;" class="form-control txtLooseBags" data-bags="' . $item->Bags . '" placeholder="Enter Loose Bags"
                            value="' . $item->Bags . '" />
                        </div>
                    </td>
                    <td class="' . ($i == 1 ? "tdPackingStyle" : "") . '">' . $item->PackingStyle . '</td>
                    <td>' . $item->PackingType . '</td>
                    <td>' . $item->ProductCategory . '</td>
                    <td>' . $item->StoreName . '</td>
                    <td>
                        ' . $item->Kgs . '
                        <input type="hidden" class="hdnStockId" value="' . $item->StockID . '" />
                        <input type="hidden" class="hdnBarCodeID" value="' . $item->BarCodeID . '" />
                        <input type="hidden" class="hdnStoreID" value="' . $item->StoreID . '" />
                        <input type="hidden" class="hdnRackID" value="' . $item->RackID . '" />
                        <input type="hidden" class="hdnBlockID" value="' . $item->BlockID . '" />
                        <input type="hidden" class="hdnProductionDate" value="' . $item->ProductionDate . '" />
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
                    <td><b>Totals</b></td>';
                    
if ($ShowBatchCode == 1) {
    echo '
                    <td></td>
                    <td></td>';
}

echo '
                    <td></td>	
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>' . $TotalMcs . '</td>
                    <td>' . $TotalBags . '</td>
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

        // if(isset($_POST["loadAll"]) && !empty($_POST["loadAll"])) {
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

    $api = API_STOCKOUT;

    $isShipment = (isset($_POST['IsShipment']) && $_POST['IsShipment'] !== '') ? (int)$_POST['IsShipment'] : 0;
    $remainingLooseBags = (isset($_POST['remainingLooseBags']) && $_POST['remainingLooseBags'] !== '') ? json_decode($_POST['remainingLooseBags'], true) : "";
    $selectedLooseMcs = (isset($_POST['selectedLooseMcs']) && $_POST['selectedLooseMcs'] !== '') ? json_decode($_POST['selectedLooseMcs'],true) : "";

    $bodyContent = array(
        "ProductionID" => $_POST["productionID"],
        "ProductionDate" => $_POST["productionDate"],
        "StockOutDate" => $_POST["stockOutDate"],
        "StockOutReason" => $_POST["stockOutReason"],
        "ReStockinStage" => $_POST["reStockinstage"],
        "Remarks" => $_POST["remarks"],
        "TotalMsc" => $_POST["totalMsc"],
        "TotalLooseBags" => $_POST["totalLooseBags"],
        "StockItems" => isset($_POST['stockItems']) ? json_decode($_POST['stockItems'], true) : [],
        "TransTypeId" => $_POST["selectedTransTypeId"],
        "PlantID" => $_POST["productionPlant"],
        "IsDeductGlaze" => $_POST["IsDeductGlaze"],
        "IsDeductGlazeFW" => $_POST["IsDeductGlazeFW"],
        "IsDeductFWFCGlaze" => $_POST["IsDeductFWFCGlaze"],
        "selectedLooseMcs" => $selectedLooseMcs,
        "IsShipment" => $isShipment,
        "convertLooseToMcs" => $convertLooseToMcs,
        "remainingLooseBags" => $remainingLooseBags,

    );

    $res = CallAPI($api, "POST", "", $bodyContent);

    //echo '<pre>';print_r($res);die;
    header('Content-Type: application/json');
    echo json_encode($res);
}
