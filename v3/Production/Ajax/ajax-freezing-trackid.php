<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;  //sriram
$AddvalueAddition = $ClientConfigData->AddvalueAddition;  //sriram
$MixedBatchCode = $ClientConfigData->MixedBatchCode;
$MCSRestriction = $ClientConfigData->MCSRestriction; 
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$EnableMcsAndLooseAtFreezingTrackPage = $ClientConfigData->EnableMcsAndLooseAtFreezingTrackPage;
$AllowedHONFreezingWeightExcessPercent = $ClientConfigData->AllowedHONFreezingWeightExcessPercent;


if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

if ($methodType == "poNumber") {

    $apiDepDropdowns = API_DEPENDANT_DROPDOWNS;
    $TubNumber = trim($_POST["type"]);
    if($TubNumber == "REJ"){
        $bodyContent = array(
            'Type' => "Variety",
            'PoID' => trim($_POST["pOID"]),
            'PackingType' => trim($_POST["packingType"]),
            'Variety' => trim($_POST["variety"]),
            'Species' => trim($_POST["species"]),
    
        );
    }else{
        $bodyContent = array(
            'Type' => "Variety",
            'PoID' => trim($_POST["pOID"]),
            'PackingType' => trim($_POST["packingType"]),
            'Species' => trim($_POST["species"]),
    
        );
    }
    $resDepDropdowns = CallAPI($apiDepDropdowns, "GET", "", $bodyContent);
    $resVariety = $resDepDropdowns->DependentDropdownsData->VarietyData;
    $resReadonlyDropdowns = $resDepDropdowns->DependentDropdownsData->ReadonlyDropdowns;
    $error = $resDepDropdowns->Error;
    $msg = $resDepDropdowns->Msg;

    $html = '
    <table class="popVarietyTable table table-hover table-bordered dataTable no-footer" style="width: 100%;">
        <thead>
            <th>S.No</th>
            <th>ShortName</th>
            <th>Description</th>
            <th>Grade</th>                                        
            <th>BrandName</th>
            <th>PackingStyle</th>  
            <th>BuyerSUPC</th>
            <th>Type</th>          
            <th>Actions</th>
        </thead>
        <tbody>';
    $i = 1;
    foreach ($resVariety as $item) {
        $html .= '
                <tr data-varietyid="' . $item->VarietyID . '">
                    <td>' . $i . '</td>
                    <td>' . $item->ShortName . '</td>
                    <td>' . $item->VarietyName . '</td>
                    <td>' . $item->Grade . '</td>                                        
                    <td>' . $item->Brand . '</td>
                    <td>' . $item->PackingStyle . '</td>
                    <td>' . $item->DeviSUPC . '</td>
                    <td>' . $item->TypeProduct . '</td>                   
                    <td>
                        <button  class="btn btn-success selectVariety" data-varietyid="' . $item->VarietyID . '" data-id="' . $item->VarietyID . '" data-po-grade-id="' . $item->PoGradeID. '" data-type="' . $item->TypeProduct . '" value="' . $item->VarietyName . '" style="padding:3px 15px">Select</button>
                    </td>
                </tr>';
        $i++;
    }
    $html .= '
        </tbody>
    </table>';

    $res = array(
        "Error" => $resDepDropdowns->Error,
        "Msg" => $resDepDropdowns->Msg,
        "ReadonlyDropdowns" => $resDepDropdowns->DependentDropdownsData->ReadonlyDropdowns,
        "HtmlRes" => $html,
    );

    header('Content-Type: application/json');
    echo json_encode($res);
}

if ($methodType == "varietyChange") {

    $apiDepDropdowns = API_DEPENDANT_DROPDOWNS;

    $bodyContent = array(
        'Type' => "Grades",
        'PoID' => trim($_POST["pOID"]),
        'VarietyID' => trim($_POST["varietyID"]),
        'PoGradeID' => trim($_POST["poGradeID"]),
        'PackingType' => trim($_POST["packingType"]),
    );

    $resDepDropdowns = CallAPI($apiDepDropdowns, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resDepDropdowns);
}

if ($methodType == "getSingleStock") {
    $api = API_FREEZING_TRACKID_DETAILS;
    $bodyContent = array(
        "ProductionID" => $_POST["productionId"],
        "ProductionDate" => $_POST["productionDate"],
        "TrackID" => $_POST["drpTrackCode"],
        "AddvalueAddition" => $AddvalueAddition,
        "MixedBatchCodeStatus" => $MixedBatchCode,
        "MixedBatchCode" => $_POST["txtMixedBatchCode"],
    );

    $resSingleStock = CallAPI($api, "GET", "", $bodyContent);
    $data = $resSingleStock->Data->DailyFreezingData;
   //echo '<pre>';print_r($resSingleStock);die;

    header('Content-Type: application/json');
    echo json_encode($data);die;
}
if ($methodType == "getBalanceQty") {

    $api = API_FREEZING_TRACKID_DETAILS;

    $bodyContent = array(
        "TubNumber" => $_POST["tubNumber"],
        "TypeofMaterial" => $_POST["typeofMaterial"],
        "TrackID" => $_POST["drpTrackCode"],
        "AddvalueAddition" => $AddvalueAddition,
    );
    $resList = CallAPI($api, "GET", "", $bodyContent);
    $BalanceQty = $resList->Data->BalanceQty;

    header('Content-Type: application/json');
    echo json_encode($BalanceQty);
}

if ($methodType == "getProdData") {

    $api = API_FREEZING_TRACKID_DETAILS;

    $bodyContent = array(
        "ProductionID" => $_POST["prodDataId"],
        "ProductionDate" => $_POST["prodDate"],
        "AddvalueAddition" => $AddvalueAddition,
    );

    $resProdData = CallAPI($api, "GET", "", $bodyContent);
    $List = $resProdData->Data->DailyProductionData;
    header('Content-Type: application/json');
    echo json_encode($List);
}
if ($methodType == "getTotalSlabs") {

    $api = API_FREEZING_TRACKID_DETAILS;
    $bodyContent = $_POST;

    $resProdData = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($resProdData,true);die;
}

if ($methodType == "create" || $methodType == "getList" || $methodType == "deleteBreakup") {

 global $MixedBatchDisplay;

    $api = API_FREEZING_TRACKID_DETAILS;

    if ($methodType == "create") {

        $bodyContent = array(
            "allowedfreezingweight" => $_POST["allowedfreezingweight"],
            "currentFreezingQty" => $_POST["currentFreezingQty"],
            "StockOutID" => $_POST["queryStockId"],
            "ProductionID" => $_POST["productionID"],
            "ClientID" => $_POST["clientID"],
            "ProductionCategory" => $_POST["drpPrdCategory"],
            "ProductionDate" => $_POST["txtProductionDate"],
            "TrackID" => $_POST["drpTrackCode"], //uma on 24-09-2024
            "GradingBreakupID" => $_POST["gradingbreakupID"],//uma
            "TubNumber" => $_POST["tubNumberValue"],
            "TubNumberValue" => $_POST["drpTubNumber"],
            "TransactionDate" => $_POST["txtTransactionDate"],
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
            "ProductCategory" => $_POST["drpProductCategory"],
            "AbStatus" => $_POST["drpAbStatus"],
            "LineNumber" => $_POST["drpLineNumber"],
            "Remarks" => $_POST["txtRemarks"],
            "ProductionWeight" => $_POST["txtProductionWeight"],
            "LooseBags" => "",
            "PoGradeID" => $_POST["poGradeID"],
            "ProductionSlabs" => $_POST["txtProductionSlabs"],
            "RejectionWeight" => $_POST["txtRejectionWeight"],
            "ArrivalDate" => $_POST["txtArrivalDate"],
            "ProductionList" => $_POST["productionList"],
            "StartTimer" => $_POST["txtStartTimer"],
            "EndTimer" => $_POST["txtEndTimer"],
            "IsDeductGlaze" => $_POST["IsDeductGlaze"],
            "IsDeductGlazeFW" => $_POST["IsDeductGlazeFW"],

             "MixedBatchCode" => $_POST['txtMixedBatchCode'],
            "Weight" => $_POST['txtWeight'],
            "MixedBatchCodeStatus" => $MixedBatchCode,
          
            
            "AddvalueAddition" => $AddvalueAddition,
            'MCSRestriction' => $MCSRestriction,

            'EnableMcsAndLooseAtFreezingTrackPage' => $EnableMcsAndLooseAtFreezingTrackPage,
            "txtMcs" => $_POST['txtMcs'],
            "txtLooseBags" => (float)$_POST['txtLooseBags'],
        );
        // var_dump($bodyContent);
        // exit;
        $res = CallAPI($api, "POST", "", $bodyContent);

    } else if ($methodType == "getList") {
        $bodyContent = array(
            "TrackID" => $_POST["trackID"],
            "PlantID" => trim($_POST["plantID"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "AddvalueAddition" => $AddvalueAddition,
             "MixedBatchCode" => $_POST["txtMixedBatchCode"],
            "MixedBatchCodeStatus" => $MixedBatchCode,
        );
        $res = CallAPI($api, "GET", "", $bodyContent);
    } else if ($methodType == "deleteBreakup") {
        $bodyContent = array(
            "ProductionID" => $_POST["productionId"],
            "ProductionDate" => $_POST["productionDate"],
            "TrackID" => $_POST["drpTrackCode"],
            "AddvalueAddition" => $AddvalueAddition,
            "IsDeductGlazeFW" => $_POST["IsDeductGlazeFW"],
            "MixedBatchCode" => $_POST["txtMixedBatchCode"],
            "MixedBatchCodeStatus" => $MixedBatchCode,
        );
        $res = CallAPI($api, "DELETE", "", $bodyContent);
    }


    if (isset($res) && !empty($res)) {
        $error = $res->Error;
        $Msg = $res->Msg;

        if (!isset($error) || empty($error)) {
            $list = $res->Data->DailyFreezingList;
            $Msg = $res->Msg;

        //      var_dump($list);
        // exit;
            ?>
            <div class="card">
                <input type="hidden" id="hdnProductionCode" value="<?php echo $res->DailyProductionData->ProductionCode ?>" />
                <input type="hidden" id="hdnBatchCode" value="<?php echo $res->DailyProductionData->BatchCode ?>" />
                <input type="hidden" id="hdnMsg" value="<?php echo $Msg ?>" />
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px;">S.No</th>
                                <th>Production Date</th>
                                <th>Track Code</th>
                                <?php if($ShowBatchCode == 0 || $MixedBatchCode == 1){ ?>
                                <th>Batch Number</th>
                                <?php } ?>
                                 <?php if($MixedBatchCode == 1){ ?>
                                <!-- <th>Weight</th> -->
                                <?php } ?>
                                <th>SUPC</th>
                                <th style="min-width:180px;">Product</th>
                                <th>Production Plant</th>
                                <th>Group</th>
                                <th>Shift</th>
                                <th>Start Timer</th>
                                <th>End Timer</th>
                                <th style="min-width:90px;">Po Number</th>
                                <th>Grade</th>
                                <th>Packing Style</th>
                                <th>Packing Type</th>
                                <th>Product Category</th>
                                <th>Ab Status</th>
                               
                                <th style="min-width:60px; ">Prod Weight</th>
                                 
                                <th tyle="min-width:60px; ">Slabs</th>
                                <?php 
                                if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
                                    <th>Mcs</th>
                                    <th>LooseBags</th>
                                <?php } ?>
                                <th>Rejection Weight</th>
                                <th>Tub No</th>
                                <th>Line Number</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
            $TotalProductionWeight = 0;
            $TotalProductionSlabs = 0;
            $TotalRejectionWeight = 0;
            foreach ($list as $item) {

        //      var_dump($list);
        // exit;
                $TotalProductionWeight += $item->ProductionWeight;
                $TotalProductionSlabs += $item->ProductionSlabs;
                $TotalRejectionWeight += $item->RejectionWeight;
                ?>
                                <tr>
                                    <td><?php echo $item->SNo ?></td>
                                    <td> <?php echo date('d-m-Y', strtotime($item->ProductionDate)) ?></td>
                                    <th><?php echo $item->TrackCode ?></th>
                                     
                                    <?php if($ShowBatchCode == 0 || $MixedBatchCode == 1){ ?>
                                        <th><?php
                                         echo !empty($item->LotNumber) 
                                                ? $item->LotNumber 
                                                : (!empty($item->MixedBatch) ? 'M-' . $item->MixedBatch : '');
                                                ?></th>
                                    <?php } ?>
                                    
                                      <?php if($MixedBatchCode == 1){ ?>
                                        <!-- <th><?php echo $item->Weight ?></th> -->
                                    <?php } ?>
                                    
                                    <th><?php echo $item->OurSupc ?></th>
                                    <th><?php echo $item->ProductDescription ?></th>
                                    <td><?php echo $item->ProductionPlantName ?></td>
                                    <td><?php echo $item->Group ?></td>
                                    <td><?php echo $item->Shift ?></td>
                                    <td><?php echo $item->StartTimer ?></td>
                                    <td><?php echo $item->EndTimer ?></td>
                                    <td><?php echo $item->PoNumber ?></td>
                                    <td><?php echo $item->Grade ?></td>
                                    <td><?php echo $item->PackingStyle ?></td>
                                    <td><?php echo $item->PackingType ?></td>
                                    <td><?php echo $item->ProductCategory ?></td>
                                    <td><?php echo $item->AbStatus ?></td>
                                    <!-- <td><?php echo $item->ProductionWeight ?></td>
                                    <td><?php echo $item->ProductionSlabs ?></td>
                                    <td><?php echo $item->RejectionWeight ?></td> -->
                                  
                                    <td class="production-weight"><?php echo $item->ProductionWeight ?></td>
                                  
                                    <td class="production-slabs"><?php echo $item->ProductionSlabs ?></td>
                                    <?php 
                                        if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
                                            <td class="production-mcs"><?php echo $item->ProductionMcs ?></td>
                                            <td class="production-loose-bags"><?php echo $item->ProductionLooseBags ?></td>
                                    <?php } ?>
                                    <td class="rejection-weight"><?php echo $item->RejectionWeight ?></td>
                                    <td><?php echo $item->TubCode ?></td>
                                    <td><?php echo $item->LineNumber ?></td>
                                    <td><?php echo $item->Remarks ?></td>
                                    <td>
                                        <?php
                                        if ($item->Mcs != $item->BalanceMcs) {
                                            $isdisabled = "disabled";
                                        } else {
                                            $isdisabled = "";
                                        }
                                        ?>
                                        <?php
                                        if ($item->EditAccess == 1) {
                                            $editDisabled = "";
                                        } else {
                                            $editDisabled = "disabled";
                                        }
                                        ?>
                                        <?php if ($item->EditAccess == 1) { ?>
                                            <button type="button" id="btnEdit" <?php echo $editDisabled; ?> name="btnEdit"
                                                data-prodid="<?php echo $item->ProductionID ?>"
                                                class="btn btn-link p-0 deleteBrkUp btnEdit">
                                                <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                            </button>
                                            <button type="button" id="btnDelete" <?php echo $isdisabled; ?> name="btnDelete"
                                                data-prodid="<?php echo $item->ProductionID ?>"
                                                class="btn btn-link p-0 deleteBrkUp btnDelete">
                                                <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                            </button>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                            } ?>
                        </tbody>
                        <tfoot>
            <tr>
                <td>GrandTotals</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <!-- <td></td> -->
                <!-- <td></td> -->
                <td id="TotalProductionWeight"><?php echo $TotalProductionWeight ?></td>
                <td id="TotalProductionSlabs"><?php echo $TotalProductionSlabs ?></td>
                <?php 
                    if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
                        <td class="TotalProductionMcs"><?php //echo $item->ProductionMcs ?></td>
                        <td class="TotalProductionLooseBags"><?php //echo $item->ProductionLooseBags ?></td>
                <?php } ?>
                <td id="TotalRejectionWeight"><?php echo $TotalRejectionWeight ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
               
            </tr>
        </tfoot>
                    </table>
                </div>
            </div>

            <?php
        } else {
            echo 'Error : ' . $error;
        }
    }
}

if ($methodType == "getTubNumbers") {
    $api = API_FREEZING_TRACKID_DETAILS;
    $bodyContent = array(
        "TrackID" => trim($_POST["trackID"]),
        "PlantID" => trim($_POST["plantID"]),
        "ArrivalDate" => trim($_POST["arrivalDate"]),//uma
        "IsHONPacking" => trim($_POST["isHONPacking"]),//Sriram
        "AddvalueAddition" => $AddvalueAddition,
        'AllowedHONFreezingWeightExcessPercent' => (float)$AllowedHONFreezingWeightExcessPercent
    );
// var_dump($bodyContent);exit;
    $resList = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($resList);
} else if ($methodType == "getTrackCodes") {

    $api = API_RM_PRODUCTION_TRACK_LIST;

    $bodyContent = array(
        "ArrivalDate" => trim($_POST["arrivalDate"]),
        "Type" => trim($_POST["type"]),
        "ReceivingPlantID" => trim($_POST["plantID"]),
        "IsHONPacking" => trim($_POST["isHONPacking"]),//Sriram
    );
    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}
else if ($methodType == "getTimerData") {
    $api = API_FREEZING_TRACKID_DETAILS;
    $bodyContent = array(
        "Shift" => trim($_POST["shift"]),
        "PlantID" => trim($_POST["plantID"]),
        "ProductionDate" => trim($_POST["productionDate"]),//uma
        "TrackID" => trim($_POST["trackID"]),
        "AddvalueAddition" => $AddvalueAddition,

    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($resList);
}
?>