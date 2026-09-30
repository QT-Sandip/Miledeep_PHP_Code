
<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = $_SESSION['ClientConfigData'];
$MCSRestriction = $ClientConfigData['MCSRestriction'];  
$IsDeductGlaze = $ClientConfigData['IsDeductGlaze'];

$ClientID = $_SESSION['ClientID'];
$ArrivalDisplayName =$ClientID != 'JLSFV3' ? 'Batch Code' : 'Arrival Code';

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
        'PackingType' => trim($_POST["packingType"]),
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
            <th>BrandName</th>
            <th>PackingStyle</th>  
            <th>BuyerSUPC</th>
            <th>Type</th>
            <th>Species</th> 
            <th>Actions</th>
        </thead>
        <tbody>';
    $i = 1;
    foreach ($resVariety as $item) {
        $html .= '
                <tr data-varietyid="' . $item->VarietyID . '">
                    <td>' . $i . '</td>
                    <td class="tdShortName">' . $item->ShortName . '</td>
                    <td>' . $item->VarietyName . '</td>
                    <td class="tdGrade">' . $item->Grade . '</td>                                        
                    <td>' . $item->Brand . '</td>
                    <td class="tdPackingStyle" data-packingstyle="' . $item->PackingStyleID . '">' . $item->PackingStyle . '</td>
                    <td>' . $item->DeviSUPC . '</td>
                    <td class="tdTypeProduct" data-glazetype="' . $item->Glaze . '" data-glaze="' . $item->GlazePerc . '" data-freezingtype="' . $item->FreezingType . '" data-soaking="' . $item->Soaking . '">' . $item->TypeProduct . '</td>
                    <td class="tdSpecies">' . $item->Species . '</td>
                    <td>
                        <button  class="btn btn-success selectVariety" data-varietyid="' . $item->VarietyID . '" data-id="' . $item->VarietyID . '" data-po-grade-id="' . $item->PoGradeID . '" value="' . $item->VarietyName . '" style="padding:3px 15px">Select</button>
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
        'Type' => "Grades",
        'PoID' => trim($_POST["pOID"]),
        'VarietyID' => trim($_POST["varietyID"]),
        'PoGradeID' => trim($_POST["poGradeID"]),
        'PackingType' => trim($_POST["packingType"])
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);

    $resDepDropdowns = CallAPI($apiDepDropdowns, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resDepDropdowns);
}

if ($methodType == "getSingleStock") {

    $api =  API_DAILY_PRODUCTION_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;

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

if ($methodType == "getProdData") {

    // $api = API_CREATE_DAILY_PRODUCTION_DATA;
    $api = API_DAILY_PRODUCTION_DETAILS;

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

if ($methodType == "create" || $methodType == "getList") {

    $api = API_DAILY_PRODUCTION_DETAILS;

    if ($methodType == "create") {

        $bodyContent = array(
            "StockOutID" => $_POST["queryStockId"],
            "ProductionID" => $_POST["productionID"],
            // "BarCodeID" => $_POST["barCodeID"],
            "ProductionDate" => $_POST["txtProductionDate"],
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
            //"Salt" => "", //$_POST["txtSalt"],
            "ProductCategory" => $_POST["drpProductCategory"],
            "AbStatus" => $_POST["drpAbStatus"],
            // "TubNo" => ,
            "LineNumber" => $_POST["drpLineNumber"],
            "Remarks" => $_POST["txtRemarks"],
            "Mcs" => $_POST["txtMcs"],
            "LooseBags" => $_POST["txtLooseBags"],
            "PoGradeID" => $_POST["poGradeID"],
            "Type" => "ProductUpdate",
            "IsDeductGlaze" => $_POST["IsDeductGlaze"],
            "IsDeductGlazeFW" => $_POST["IsDeductGlazeFW"],
            "IsDeductFWFCGlaze" => $_POST["IsDeductFWFCGlaze"],
            'MCSRestriction' => $MCSRestriction,
            'IsDeductGlaze' => $IsDeductGlaze
        );

        //    echo json_encode($bodyContent);
        //     exit;
        $res = CallAPI($api, "POST", "", $bodyContent);
        if($res->mcsStatus){
            header('Content-Type: application/json');
            echo json_encode($res);die;
        }
        // echo json_encode($res, JSON_PRETTY_PRINT);
        // exit;

    } else if ($methodType == "getList") {

        $bodyContent = array(
            "ProductionDate" => $_POST["txtProductionDate"],
            "ColdstoreID" => $_POST["ColdstoreID"],
            "LoadAll" => "1",
            // "PageNo" => "1",
             'IsDeductGlaze' => $IsDeductGlaze
        );

        $res = CallAPI($api, "GET", "", $bodyContent);
        // echo json_encode($res, JSON_PRETTY_PRINT);
        // exit;

    }


    if (isset($res) && !empty($res)) {

        $error = $res->Error;

        if (!isset($error) || empty($error)) {
            $list = $res->DailyProductionList;
?>

            <div class="card">
                <input type="hidden" id="hdnProductionCode" value="<?php echo $res->DailyProductionData->ProductionCode  ?>" />
                <input type="hidden" id="hdnBatchCode" value="<?php echo $res->DailyProductionData->BatchCode  ?>" />
                <input type="hidden" id="hdnNewAddedProdId" value="<?php echo $res->NewAddedProdId  ?>" />

                <!-- <input type="hidden" id="hdnBarCodeID" name="hdnBarCodeID" value="<?php // echo $res->StockData->BarCodeID 
                                                                                        ?>"/> -->
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px;">S.No</th>
                                <th>SUPC</th>
                                <th style="min-width:180px;">Product</th>
                                <th>Production Plant</th>
                                <!-- <th>Production Date</th> -->
                                <!-- <th>Transaction Date</th> -->
                                <th>Group</th>
                                <th>Stock Belongs to</th>
                                <!-- <th>Store</th> -->
                                <th>Shift</th>
                                <th>Production Code</th>
                               <th><?php echo $ArrivalDisplayName; ?></th>
                                <th style="min-width:90px;">Po Number</th>
                                <!-- <th>Variety</th> -->
                                <th>Grade</th>
                                <th>Packing Style</th>
                                <th>Packing Type</th>
                                <!-- <th>Freezing Type</th>                                                
                            <th>Glaze(%)	</th>
                            <th>Soaking</th> -->
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
                            foreach ($list as $item) {
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
                                    <td><?php echo $i ?></td>
                                    <th><?php echo $item->OurSupc ?></th>
                                    <th><?php echo $item->ProductDescription ?></th>
                                    <td><?php echo $item->ProductionPlantName ?></td>
                                    <!-- <td><?php // echo $item->ProductionDate 
                                                ?></td> -->
                                    <!-- <td><?php // echo $item->TransactionDate 
                                                ?></td> -->
                                    <td><?php echo $item->Group ?></td>
                                    <td><?php echo $item->StockHolderName ?></td>
                                    <!-- <td><?php // echo $item->StoreName 
                                                ?></td> -->
                                    <td><?php echo $item->Shift ?></td>
                                    <td><?php echo $item->ProductionCode ?></td>
                                    <td><?php echo $item->BatchCode ?></td>
                                    <td><?php echo $item->PoNumber ?></td>
                                    <!-- <td><?php // echo $item->Variety 
                                                ?></td> -->
                                    <td><?php echo $item->Grade ?></td>
                                    <td><?php echo $item->PackingStyle ?></td>
                                    <td><?php echo $item->PackingType ?></td>
                                    <!-- <td><?php // echo $item->FreezingType 
                                                ?></td>                                                 -->
                                    <!-- <td><?php // echo $item->Galze 
                                                ?></td> -->
                                    <!-- <td><?php // echo $item->Soaking 
                                                ?></td> -->
                                    <td><?php echo $item->ProductCategory ?></td>
                                    <td><?php echo $item->AbStatus ?></td>
                                    <td><?php echo $item->Remarks ?></td>
                                    <td><?php echo $item->Mcs ?></td>
                                    <td><?php echo $item->LooseBags ?></td>
                                    <td>
                                        <?php
                                        if ($item->Mcs != $item->BalanceMcs) {
                                            $isdisabled = "disabled";
                                        } else {
                                            $isdisabled = "";
                                        }
                                        ?>

                                        <button type="button" id="btnEdit" <?php echo $isdisabled; ?> name="btnEdit" data-prodid="<?php echo $item->ProductionID ?>" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                        </button>
                                        <button type="button" id="btnDelete" <?php echo $isdisabled; ?> name="btnDelete" data-prodid="<?php echo $item->ProductionID ?>" class="btn btn-link p-0 deleteBrkUp btnDelete">
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php $i++;
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>

<?php
        } else {
            echo '<div>ERROR: ' . $error . '</div>';
        }
    }
}
if ($methodType == "getPackingStyle") {
    $api =  API_DAILY_PRODUCTION_DETAILS; // API_CREATE_DAILY_PRODUCTION_DATA;

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
