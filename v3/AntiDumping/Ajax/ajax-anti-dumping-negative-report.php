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
if ($methodType == "allPoNumbers") {

    $api = API_ANTI_DUMPING_NEGATIVE_REPORT; // API_CREATE_DAILY_PRODUCTION_DATA;

    $bodyContent = array(
        'type' => "allPoNumbers",
        'ProductionPlant' => trim($_POST["ProductionPlant"]),
        'ProductionDate' => trim($_POST["ProductionDate"]),
        'ProductionToDate' => trim($_POST["ProductionToDate"])
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

    $api = API_ANTI_DUMPING_NEGATIVE_REPORT; // API_CREATE_DAILY_PRODUCTION_DATA;

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

if ($methodType == "getProdData") {

    // $api = API_CREATE_DAILY_PRODUCTION_DATA;
    $api = API_ANTI_DUMPING_NEGATIVE_REPORT;

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

if ($methodType == "getList") {

    $api = API_ANTI_DUMPING_NEGATIVE_REPORT;

    $bodyContent = array(
        "ProductionDate" => $_POST["txtProductionDate"],
        "ProductionPlant" => $_POST["drpProductionPlant"],
        "ProductionToDate" => $_POST["txtProductionToDate"],
        "PoNumber" => $_POST["drpPoNumber"],
        "Variety" => $_POST["drpVariety"],
        "Grade" => $_POST["drpGrade"],
        "LoadAll" => $_GET["LoadAll"],
        "PageNo" => $_GET["PageNo"],
        "MoreSearch" => $_GET["MoreSearch"],
        // "PageNo" => "1",
    );

    $res = CallAPI($api, "GET", "", $bodyContent);

    // echo print_r($res->DailyProductionList);exit;

    if (isset($res) && !empty($res)) {

        $error = $res->Error;

        if (!isset($error) || empty($error)) {
            $list = $res->DailyProductionList;
            ?>
 <div class="card">
                <input type="hidden" id="hdnProductionCode" value="<?php echo $res->DailyProductionData->ProductionCode ?>" />
                <input type="hidden" id="hdnBatchCode" value="<?php echo $res->DailyProductionData->BatchCode ?>" />
                <input type="hidden" id="hdnNewAddedProdId" value="<?php echo $res->NewAddedProdId ?>" />
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:30px;">S.No</th>
                                <th style="min-width: 110px">Production Plant</th>
                                <th style="min-width: 70px">Date</th>
                                <th>SUPC</th>
                                <th style="min-width:180px;">Product</th>
                                <th>Grade</th>
                                <th>Packing Style</th>
                                <th>Opening Stock</th>
                                <th>Closing Stock</th>

                                <th style="min-width: 30px">S.No</th>
                                <th style="min-width: 70px">Group</th>
                                <th style="min-width: 70px">Po Number</th>
                                <th style="min-width: 70px">Product Category</th>
                                <th style="min-width: 50px">Slabs</th>
                                <th style="min-width: 70px">Kgs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($list as $item) {
                                $breakupCount = !empty($item->BreakUpData) ? count($item->BreakUpData) : 1;
                                ?>
                                <tr>
                                    <td ><?php echo $i; ?></td>
                                    <td ><?php echo $item->StationName; ?></td>
                                    <td ><?php echo $item->StockDate; ?></td>
                                    <td ><?php echo $item->DeviSupc; ?></td>
                                    <td ><?php echo $item->ProductDescription; ?></td>
                                    <td ><?php echo $item->Grade; ?></td>
                                    <td ><?php echo $item->PackingStyle; ?></td>
                                    <td ><?php echo $item->OpeningStock; ?></td>
                                    <td ><?php echo $item->ClosingStock; ?></td>

                                    <td style="padding: 0px !important;" colspan="7">
                                        <table class="subTable table table-bordered mb-0" style="width: 100%">
                                            <?php
                                            $j =0 ;
                                            foreach ($item->BreakUpData as $itemBreak) {  ?>
                                                <tr>
                                                    <td style="min-width: 30px"><?php echo ++$j ?></td>
                                                    <td style="min-width: 70px"><?php echo $itemBreak->Group; ?></td>
                                                    <td style="min-width: 70px"><?php echo $itemBreak->PoNo; ?></td>
                                                    <td style="min-width: 70px"><?php echo $itemBreak->ProductCategory; ?></td>
                                                    <td style="min-width: 50px"><?php echo $itemBreak->AvalSlabs; ?></td>
                                                    <td style="min-width: 70px"><?php echo $itemBreak->Kgs; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </table>
                                    </td>
                                    <td style="display: none"></td>
                                    <td style="display: none"></td>
                                    <td style="display: none"></td>
                                    <td style="display: none"></td>
                                    <td style="display: none"></td>
                                </tr>

                                <?php
                                $i++;
                            }
                            ?>
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

?>