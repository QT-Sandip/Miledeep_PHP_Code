<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$AccessType = !empty($_SESSION['AccessType']) ? $_SESSION['AccessType'] : 0;
$CurrentDate = new DateTime();

if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

if ($methodType == "poNumber") {

    $apiDepDropdowns = API_DEPENDANT_DROPDOWNS;

    $bodyContent = array(
        'Type' => "Variety",
        'PoID' => trim($_POST["pOID"]),
        // 'varietyID' => trim($_POST["varietyID"])
    );

    $resDepDropdowns = CallAPI($apiDepDropdowns, "GET", "", $bodyContent);


    // echo json_encode($resDepDropdowns, JSON_PRETTY_PRINT);
    // exit;

    $resVariety = $resDepDropdowns->DependentDropdownsData->VarietyData;
    $resReadonlyDropdowns = $resDepDropdowns->DependentDropdownsData->ReadonlyDropdowns;
    $error = $resDepDropdowns->Error;
    $msg = $resDepDropdowns->Msg;

    $html = '
    <table id="popVarietyTable" class="popVarietyTable table table-hover table-bordered dataTable no-footer" style="width: 100%;">
        <thead>
            <th>S.No</th>
            <th>ShortName</th>
            <th>Description</th>
            <th>Grade</th>
            <th>PackingStyle</th> 
            <th>Brand</th>
            <th>BuyerSUPC</th>
            <th>Type</th>
            <th>Species</th> 
            <th>Actions</th>
        </thead>
        <tbody>';
    $i = 1;
    foreach ($resVariety as $item) {
        preg_match('/\((\d+)\s*Mcs\)/', $item->VarietyName, $matches);
        $pendingMcs = isset($matches[0]) ? $matches[0] : '';
        $html .= '
                <tr data-varietyid="' . $item->VarietyID . '">
                    <td>' . $i . '</td>
                    <td class="tdShortName">' . $item->ShortName . '</td>
                    <td>' . $item->VarietyName . '</td>
                    <td class="tdGrade">' . $item->Grade . '</td>    
                    <td class="tdPackingStyle" data-packingstyle="' . $item->PackingStyleID . '">' . $item->PackingStyle . '</td>
                     <td class="tdbrand" data-brandid="' . $item->BrandID . '">' . $item->Brand . '</td>
                    <td>' . $item->DeviSUPC . '</td>
                    <td class="tdTypeProduct" data-freezingtype="' . $item->FreezingType . '" data-soaking="' . $item->Soaking . '">' . $item->TypeProduct . '</td>
                    <td class="tdSpecies">' . $item->Species . '</td>
                    <td>
                        <button  class="btn btn-success selectVariety" data-varietyid="' . $item->VarietyID . '" data-id="' . $item->VarietyID . '" data-po-grade-id="' . $item->PoGradeID . '" value="' . $item->VarietyName . '" style="padding:3px 15px"
                        data-pending-mcs="' . $pendingMcs . '" data-shortname="' . $item->ShortName . '">Select</button>
                    </td>
                </tr>';
        $i++;
    }
    $html .= '
            </tbody>
        </table>
        <script>
        $(document).ready(function () {
            $("#popVarietyTable").DataTable({
                paging: true,
                searching: true,
                info: true
            });
        });
    </script>';

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
        'Type' => "ADGrades",
        'PoID' => trim($_POST["pOID"]),
        'VarietyID' => trim($_POST["varietyID"]),
        'PoGradeID' => trim($_POST["poGradeID"]),
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);

    $resDepDropdowns = CallAPI($apiDepDropdowns, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resDepDropdowns);
}
if ($methodType == "getVarietyAllGrades") {

    $api = API_ANTI_DUMPING_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;

    $bodyContent = array(
        'type' => "Grades",
        'PoID' => trim($_POST["pOID"]),
        'VarietyID' => trim($_POST["varietyID"]),
    );

    $resAllGrades = CallAPI($api, "GET", "", $bodyContent);

    $data = $resAllGrades->data;
    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // echo json_encode($resSingleStock, JSON_PRETTY_PRINT);
    header('Content-Type: application/json');
    echo json_encode($data);
}


if ($methodType == "getSingleStock") {

    $api = API_ANTI_DUMPING_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;

    $bodyContent = array(
        "ProductionID" => $_POST["productionId"],
        "ProductionDate" => $_POST["productionDate"]
    );

    $resSingleStock = CallAPI($api, "GET", "", $bodyContent);

    $data = $resSingleStock->DailyProductionData;
    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // echo json_encode($resSingleStock, JSON_PRETTY_PRINT);
    header('Content-Type: application/json');
    echo json_encode($data);
}

if ($methodType == "getPoTotal") {

    $api = API_ANTI_DUMPING_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;

    $bodyContent = array(
        "ProductionDate" => $_POST["txtProductionDate"],
        "ColdstoreID" => $_POST["ColdstoreID"],
        "PoID" => $_POST["PoID"],
        "VarietyID" => $_POST["varietyID"],
        "type" => 'getPoTotal'
        // "PageNo" => "1",
    );

    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);

}

if ($methodType == "allPoNumbers") {

    $api = API_ANTI_DUMPING_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;

    $bodyContent = array(
        'type' => "allPoNumbers",
        'ProductionPlant' => trim($_POST["ProductionPlant"]),
        'ProductionDate' => trim($_POST["ProductionDate"]),
    );

    $resAllGrades = CallAPI($api, "GET", "", $bodyContent);
    // echo json_encode($resAllGrades, JSON_PRETTY_PRINT);
    // exit;

    $data = $resAllGrades->data;
    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // echo json_encode($resSingleStock, JSON_PRETTY_PRINT);
    header('Content-Type: application/json');
    echo json_encode($data);
}

if ($methodType == "delete") {

    $api = API_ANTI_DUMPING_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;
    $bodyContent = array(
        'ProductionID' => trim($_POST["ProductionID"]),
        'ProductionDate' => trim($_POST["ProductionDate"])
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;

    $resList = CallAPI($api, "DELETE", "", $bodyContent);
}

if ($methodType == "deleteSelected") {

    $api = API_ANTI_DUMPING_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;
    $bodyContent = array(
        'ProductionID' => trim($_POST["ProductionID"]),
        'ProductionDate' => trim($_POST["ProductionDate"]),
        'type' => 'deleteSelected'
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;

    $resList = CallAPI($api, "DELETE", "", $bodyContent);
}

if ($methodType == "getProdData") {

    // $api = API_CREATE_DAILY_PRODUCTION_DATA;
    $api = API_ANTI_DUMPING_DETAILS;

    $bodyContent = array(
        "ProductionID" => $_POST["prodDataId"],
        "ProductionDate" => $_POST["prodDate"],
    );

    $resProdData = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // echo json_encode($resProdData, JSON_PRETTY_PRINT);
    // exit;

    $List = $resProdData->DailyProductionData;
    header('Content-Type: application/json');
    echo json_encode($List);
}

if ($methodType == "getDataWithoutBrand") {

    $api = API_ANTI_DUMPING_DETAILS;

    $bodyContent = array(
        "VarietyID" => $_POST["varietyID"],
        "ProductionDate" => $_POST["txtProductionDate"],
        "type" => $_POST["methodType"],
        "LoadAll" => "1",
    );
    $res = CallAPI($api, "GET", "", $bodyContent);

    if (isset($res) && !empty($res)) {

        $error = $res->Error;

        if (!isset($error) || empty($error)) {
            $list = $res->DailyProductionList;
            $TotalSlabs = $res->TotalSlabs;
            $TotalKgs = $res->TotalKgs;
            ?>

            <div class="card">
                <input type="hidden" id="hdnProductionCode" value="<?php echo $res->DailyProductionData->ProductionCode ?>" />
                <input type="hidden" id="hdnBatchCode" value="<?php echo $res->DailyProductionData->BatchCode ?>" />
                <input type="hidden" id="hdnNewAddedProdId" value="<?php echo $res->NewAddedProdId ?>" />

                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th><input type="checkbox" class="chkSelectAll" id="chkSelectAll"></th>
                                <th style="width:50px;">S.No</th>
                                <th style="min-width: 110px">Production Plant</th>
                                <th style="min-width: 90px">Stock Belongs to</th>
                                <th style="min-width: 70px">Date</th>
                                <th>Group</th>
                                <th>Po Number</th>
                                <th>SUPC</th>
                                <th style="min-width:180px;">Product</th>
                                <th style="min-width: 80px">From Plant</th>
                                <th style="min-width: 80px">To Plant</th>
                                <th>Grade</th>
                                <th>Packing Style</th>
                                <th>Product Category</th>
                                <th>Slabs</th>
                                <th>Kgs</th>
                                <th>Last Updated Date</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($list as $item) {
                                ?>
                                <tr>
                                    <td><input value="<?php echo $item->ProductionID ?>" type="checkbox" class="chkSelectRecList"></td>
                                    <td><?php echo $i ?></td>
                                    <td style="min-width: 110px"><?php echo $item->ProductionPlantName ?></td>
                                    <td style="min-width: 90px"><?php echo $item->StoreName ?></td>
                                    <td style="min-width: 70px"><?php echo $item->ProductionDate ?></td>
                                    <td><?php echo $item->Group ?></td>
                                    <th><?php echo $item->PoNo ?></th>
                                    <th><?php echo $item->OurSupc ?></th>
                                    <th><?php echo $item->ProductDescription ?></th>
                                    <th style="min-width: 80px"><?php echo $item->FromPlant ?></th>
                                    <th style="min-width: 80px"><?php echo $item->ToPlant ?></th>
                                    <td><?php echo $item->Grade ?></td>
                                    <td><?php echo $item->PackingStyle ?></td>
                                    <td><?php echo $item->ProductCategory ?></td>
                                    <td class="AvalSlabs"><?php echo $item->AvalSlabs ?></td>
                                    <td class="Kgs"><?php echo $item->Kgs ?></td>
                                    <td><?php echo $item->LastUpdatedDate ?></td>
                                    <td><?php echo $item->Remarks ?></td>
                                </tr>
                                <?php $i++;
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php
        }
    }


}

if ($methodType == "create" || $methodType == "getList") {

    $api = API_ANTI_DUMPING_DETAILS;

    if ($methodType == "create") {

        $bodyContent = array(
            "StockOutID" => $_POST["queryStockId"],
            "ProductionID" => $_POST["productionID"],
            "ProductionDate" => $_POST["txtProductionDate"],
            "GroupType" => $_POST["drpGroupType"],
            "Group" => $_POST["drpGroup"],
            "ColdstoreID" => $_POST["drpStockBelongs"],
            "PlantID" => $_POST["drpProductionPlant"],
            "Variety" => $_POST["drpVariety"],
            "PackingStyle" => $_POST["drpPackingStyle"],
            "Grade" => $_POST["drpGrade"],
            "PoID" => $_POST["drpPoNumber"],
            "FreezingType" => $_POST["drpFreezingType"],
            "Glaze" => $_POST["txtGlaze"],
            "Soaking" => $_POST["drpSoaking"],
            "OpeningStock" => $_POST["txtOpeningStock"],
            "ClosingStock" => $_POST["txtClosingStock"],
            //"Salt" => "", //$_POST["txtSalt"],
            "ProductCategory" => $_POST["drpProductCategory"],
            "Remarks" => $_POST["txtRemarks"],
            "Slabs" => $_POST["txtSlabs"],
            "PoGradeID" => $_POST["poGradeID"],
            "FromPlant" => $_POST["drpFromPlant"],
            "ToPlant" => $_POST["drpToPlant"],
            "Type" => "ProductUpdate",
            "IsDeductGlaze" => $_POST["IsDeductGlaze"],
        );
        //    echo json_encode($bodyContent);
        //     exit;
        $res = CallAPI($api, "POST", "", $bodyContent);

        // echo json_encode($res, JSON_PRETTY_PRINT);
        // exit;

    } else if ($methodType == "getList") {

        $bodyContent = array(
            "ProductionDate" => $_POST["txtProductionDate"],
            "ColdstoreID" => $_POST["ColdstoreID"],
            "LoadAll" => "1",
            // "PageNo" => "1",
        );
        $res = CallAPI($api, "GET", "", $bodyContent);
        // echo json_encode($res, JSON_PRETTY_PRINT);
        // exit;

    }


    if (isset($res) && !empty($res)) {

        $error = $res->Error;

        if (!isset($error) || empty($error)) {
            $list = $res->DailyProductionList;
            $TotalSlabs = $res->TotalSlabs;
            $TotalKgs = $res->TotalKgs;
            ?>

            <div class="card">
                <input type="hidden" id="hdnProductionCode" value="<?php echo $res->DailyProductionData->ProductionCode ?>" />
                <input type="hidden" id="hdnBatchCode" value="<?php echo $res->DailyProductionData->BatchCode ?>" />
                <input type="hidden" id="hdnNewAddedProdId" value="<?php echo $res->NewAddedProdId ?>" />

                <!-- <input type="hidden" id="hdnBarCodeID" name="hdnBarCodeID" value="<?php // echo $res->StockData->BarCodeID 
                            ?>"/> -->
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th><input type="checkbox" class="chkSelectAll" id="chkSelectAll"></th>
                                <th style="width:50px;">S.No</th>
                                <th style="min-width: 110px">Production Plant</th>
                                <th style="min-width: 90px">Stock Belongs to</th>
                                <th style="min-width: 70px">Date</th>
                                <th>Group</th>
                                <th>Po Number</th>
                                <th>SUPC</th>
                                <th style="min-width:180px;">Product</th>
                                <th style="min-width: 80px">From Plant</th>
                                <th style="min-width: 80px">To Plant</th>
                                <!-- <th>Variety</th> -->
                                <th>Grade</th>
                                <th>Packing Style</th>
                                <!-- <th>Freezing Type</th>                                                
                            <th>Glaze(%)	</th>
                            <th>Soaking</th> -->
                                <th>Product Category</th>
                                <th>Slabs</th>
                                <th>Kgs</th>
                                <th>Last Updated Date</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($list as $item) {
                                $date1 = new DateTime($item->ProductionDate);
                                $diff = $date1->diff($CurrentDate);
                                $enableActions = 0;
                                if($diff->days < 90 || $AccessType == 1){
                                    $enableActions = 1;
                                }
                                ?>
                                <tr>
                                    <!-- "StockID":"10",
                        "BarCodeID":"17", -->
                                    <?php // echo $item->StockHolderID 
                                                    ?>
                                    <?php // echo $item->PlantID 
                                                    ?>
                                    <?php // echo $item->Coldstore 
                                                    ?>
                                    <?php // echo $item->ShiftID 
                                                    ?>
                                    <?php // echo $item->VarietyID 
                                                    ?>
                                    <?php // echo $item->PoGradeId 
                                                    ?>
                                    <?php // echo $item->PackingStyleID 
                                                    ?>
                                    <td>
                                        <?php 
                                        if($enableActions == 1){?>
                                            <input value="<?php echo $item->ProductionID ?>" type="checkbox" class="chkSelectRecList">
                                        <?php }?>
                                    </td>
                                    <td><?php echo $i ?></td>
                                    <td style="min-width: 110px"><?php echo $item->ProductionPlantName ?></td>
                                    <td style="min-width: 90px"><?php echo $item->StoreName ?></td>
                                    <td style="min-width: 70px"><?php echo $item->ProductionDate ?></td>
                                    <td><?php echo $item->Group ?></td>
                                    <th><?php echo $item->PoNo ?></th>
                                    <th><?php echo $item->OurSupc ?></th>
                                    <th><?php echo $item->ProductDescription ?></th>
                                    <th style="min-width: 80px"><?php echo $item->FromPlant ?></th>
                                    <th style="min-width: 80px"><?php echo $item->ToPlant ?></th>
                                    <!-- <td><?php // echo $item->Variety 
                                                    ?></td> -->
                                    <td><?php echo $item->Grade ?></td>
                                    <td><?php echo $item->PackingStyle ?></td>
                                    <!-- <td><?php // echo $item->FreezingType 
                                                    ?></td>                                                 -->
                                    <!-- <td><?php // echo $item->Galze 
                                                    ?></td> -->
                                    <!-- <td><?php // echo $item->Soaking 
                                                    ?></td> -->
                                    <td><?php echo $item->ProductCategory ?></td>
                                    <td class="AvalSlabs"><?php echo $item->AvalSlabs ?></td>
                                    <td class="Kgs"><?php echo $item->Kgs ?></td>
                                    <td><?php echo $item->LastUpdatedDate ?></td>
                                    <td><?php echo $item->Remarks ?></td>
                                    <td>
                                        <?php
                                        if ($item->AvalSlabs != $item->BalanceSlabs) {
                                            $isdisabled = "";
                                        } else {
                                            $isdisabled = "";
                                        }
                                        ?>
                                         <?php 
                                        if($enableActions == 1){?>
                                            <button type="button" id="btnEdit" <?php echo $isdisabled; ?> name="btnEdit"
                                                data-prodid="<?php echo $item->ProductionID ?>"
                                                class="btn btn-link p-0 deleteBrkUp btnEdit">
                                                <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                            </button>
                                            <button type="button" id="btnDelete" <?php echo $isdisabled; ?> name="btnDelete"
                                                data-prodid="<?php echo $item->ProductionID ?>"
                                                class="btn btn-link p-0 deleteBrkUp btnDelete">
                                                <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                            </button>
                                        <?php }?>
                                    </td>
                                </tr>
                                <?php $i++;
                            } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th style="width:50px;"></th>
                                <th style="min-width: 110px"></th>
                                <th style="min-width: 90px"></th>
                                <th style="min-width: 70px"></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="min-width:180px;"></th>
                                <th style="min-width: 80px"></th>
                                <th style="min-width: 80px"></th>
                                <!-- <th>Variety</th> -->
                                <th></th>
                                <th></th>
                                <!-- <th>Freezing Type</th>                                                
                            <th>Glaze(%)	</th>
                            <th>Soaking</th> -->
                                <th></th>
                                <th id="TotalSlabs"><?php echo $TotalSlabs ?></th>
                                <th id="GrandTotalWeight"><?php echo $TotalKgs ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <?php
        } else {
             header('Content-Type: application/json');
            echo json_encode($res);
        }
    }
}
if ($methodType == "getPackingStyle") {
    $api = API_ANTI_DUMPING_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;

    $bodyContent = array(
        "PackingStyle" => $_POST["packingStyle"],
        "type" => "packingStyle"
    );

    $resSingleStock = CallAPI($api, "GET", "", $bodyContent);

    $data = $resSingleStock->Data;
    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // echo json_encode($resSingleStock, JSON_PRETTY_PRINT);
    header('Content-Type: application/json');
    echo json_encode($data);
}
// if($methodType == "placePalette") {

//     $apiPlacePallet = API_PLACE_PALLET;

//     $bodyContent = array(
//         'BarCodeID' => trim($_POST["barCodeID"]),
//         'RackID' => trim($_POST["rackID"])
//     );

//     $resPlacePallet = CallAPI($apiPlacePallet, "POST", "", $bodyContent);

//     header('Content-Type: application/json');
//     echo json_encode($resPlacePallet);

// }
?>