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

    $api =  API_ANTI_DUMPING_REPORT; // API_CREATE_DAILY_PRODUCTION_DATA;

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

    $api = API_ANTI_DUMPING_REPORT; // API_CREATE_DAILY_PRODUCTION_DATA;

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
    $api = API_ANTI_DUMPING_REPORT;

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

if ($methodType == "getList" || isset($_GET["excelDownload"])) {

    $api = API_ANTI_DUMPING_REPORT;
    $border = '';
    if(isset($_GET["excelDownload"])){
        $bodyContent = array(
            "ProductionDate" => $_GET["txtProductionDate"],
            "ProductionToDate" => $_GET["txtProductionToDate"],
            "ProductionPlant" => $_GET["drpProductionPlant"],
            "GroupType" => $_GET["drpGroupType"],
            "Group" => $_GET["drpGroup"],
            "PoNumber" => $_GET["drpPoNumber"],
            "Variety" => $_GET["drpVariety"],
            "PackingStyle" => $_GET["drpPackingStyle"],
            "Grade" => $_GET["drpGrade"],
            "FreezingType" => $_GET["drpFreezingType"],
            "Soaking" => $_GET["drpSoaking"],
            "ProductCategory" => $_GET["drpProductCategory"],
            "LoadAll" => $_GET["LoadAll"],
            "PageNo" => $_GET["PageNo"],
            "MoreSearch" => $_GET["MoreSearch"],
        );
        $border = "border='1'";
    }else{
        $bodyContent = array(
            "ProductionDate" => $_POST["txtProductionDate"],
            "ProductionPlant" => $_POST["drpProductionPlant"],
            "ProductionToDate" => $_POST["txtProductionToDate"],
            "GroupType" => $_POST["drpGroupType"],
            "Group" => $_POST["drpGroup"],
            "PoNumber" => $_POST["drpPoNumber"],
            "Variety" => $_POST["drpVariety"],
            "PackingStyle" => $_POST["drpPackingStyle"],
            "Grade" => $_POST["drpGrade"],
            "FreezingType" => $_POST["drpFreezingType"],
            "Soaking" => $_POST["drpSoaking"],
            "ProductCategory" => $_POST["drpProductCategory"],
            "LoadAll" => $_POST["LoadAll"],
            "PageNo" => $_POST["PageNo"],
            "MoreSearch" => $_POST["MoreSearch"],
        );
    }

    $res = CallAPI($api, "GET", "", $bodyContent);

    if (isset($res) && !empty($res)) {

        $error = $res->Error;

        if (!isset($error) || empty($error)) {
            //echo '<pre>';print_r($res);die;
            $list = $res->DailyProductionList;
            $PageCount = $res->PageCount;
			$resTotal = $res->TotalCount;
            $PageNo = !empty($_POST["PageNo"])?trim($_POST["PageNo"]):1;
            $Totals = $res->Totals;
            $content ='<div class="card">';
                if (!isset($_GET["excelDownload"]) && $_GET["excelDownload"] != "T") { 
                    $content .= '
                    <input type="hidden" id="hdnProductionCode" value="'. $res->DailyProductionData->ProductionCode .'" />
                    <input type="hidden" id="hdnBatchCode" value="'. $res->DailyProductionData->BatchCode .'" />
                    <input type="hidden" id="hdnNewAddedProdId" value="'.$res->NewAddedProdId .'" />';
                }
                $content .= '
                <div class="card-body">
                    <table id="tblDataTable" '.$border.' class="table table-hover table-bordered" data-info="false">
                        <thead class="thead-light">';
                            if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
                                $content .= '<tr><th colspan="15" align="center"><b>Anti Dumping Report</b></th></tr>';
                                        
                            }
                            $content .= '
                            <tr>
                                <th style="width:50px;">S.No</th>
                                <th style="min-width: 110px">Production Plant</th>
                                <!-- <th>Stock Belongs to</th> -->
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
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>';
                            $i = 1;
                            $pageStart = 0;
                            foreach ($list as $item) {
                                if(empty($pageStart)) {
                                    $pageStart = $item->SNo;
                                }
                                $content .= '
                                <tr>
                                    <td>'. $item->SNo .'</td>
                                    <td style="min-width: 110px">'. $item->ProductionPlantName .'</td>
                                    <!-- <td>'. $item->StoreName .'</td> -->
                                    <td style="min-width: 70px">'. $item->ProductionDate .'</td>
                                    <td>'. $item->Group .'</td>
                                    <th>'. $item->PoNo .'</th>
                                    <th>'. $item->OurSupc .'</th>
                                    <th>'. $item->ProductDescription .'</th>
                                    <th style="min-width: 80px">'. $item->FromPlant .'</th>
                                    <th style="min-width: 80px">'. $item->ToPlant .'</th>
                                    <td>'. $item->Grade .'</td>
                                    <td>'. $item->PackingStyle .'</td>
                                    <td>'. $item->ProductCategory .'</td>
                                    <td class="AvalSlabs">'. $item->AvalSlabs .'</td>
                                    <td class="Kgs">'. $item->Kgs .'</td>
                                    <td>'. $item->Remarks .'</td>
                                </tr>';
                                 $i++;
                            } 
                            $content .= '
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Totals</td>
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
                                <td id="TotalSlabs">'.$Totals->TotalSlabs.'</td>
                                <td id="GrandTotalWeight">'.$Totals->GrandTotalWeight.'</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>';
                    if (!isset($_GET["excelDownload"]) && $_GET["excelDownload"] != "T") { 
                        $content .= '
                        <div>';
                            if(!empty($resTotal)){
                                $content .= '
<div class="row align-items-center">
<div class=" col-6 mt-3 mb-3">
    Showing '. $pageStart.' to '. $item->SNo .' out of '. $resTotal .' entries
</div>
<div class="col-6">
<ul class="pagination pagination-rounded justify-content-end mb-0" style="overflow-x:auto;margin:0 auto;">';

            // Previous button
            if ($PageNo > 1) {
                $content .= '<li class="page-item">
                    <button class="page-link btnPageno" data-pageno="'.($PageNo - 1).'">&laquo;</button>
                </li>';
            }

            $range = 2; // pages around current

            for ($i = 1; $i <= $PageCount; $i++) {

                if (
                    $i == 1 ||
                    $i == $PageCount ||
                    ($i >= $PageNo - $range && $i <= $PageNo + $range)
                ) {

                    $active = ($PageNo == $i) ? "active" : "";

                    $content .= '<li class="page-item">
                        <button class="page-link '.$active.' btnPageno" data-pageno="'.$i.'">'.$i.'</button>
                    </li>';

                } elseif (
                    $i == $PageNo - $range - 1 ||
                    $i == $PageNo + $range + 1
                ) {
                    // dots
                    $content .= '<li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>';
                }
            }

            //  Next button
            if ($PageNo < $PageCount) {
                $content .= '<li class="page-item">
                    <button class="page-link btnPageno" data-pageno="'.($PageNo + 1).'">&raquo;</button>
                </li>';
            }

            $content .= '</ul>';
                            }
                        $content .= '
                        </div></div>';
                    }
                    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {  
                        $content .='
                        <table>
                         <tbody>
                            <tr>
                                <th>Totals</th>
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
                                <th>'.$Totals->TotalSlabs.'</th>
                                <th>'.$Totals->GrandTotalWeight.'</th>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>';
                    }
                    $content .= '
                </div>
            </div>';
                            
            if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {            
                $filename =  'Anti-Dumping-Report.xls';
                header("Content-Type: application/vnd.ms-excel; charset=utf-8");
                header("Content-Disposition: attachment; filename=" . $filename);
                //die;
            }
            echo $content ;
        } else {
            echo '<div>ERROR: ' . $error . '</div>';
        }
    }
}
if ($methodType == "getPackingStyle") {
    $api = API_ANTI_DUMPING_REPORT; // API_CREATE_DAILY_PRODUCTION_DATA;

    $bodyContent = array(
        "PackingStyle" => $_POST["packingStyle"],
        "type" => "packingStyle"
    );

    $resSingleStock = CallAPI($api, "GET", "", $bodyContent);

    $data = $resSingleStock->Data;
    header('Content-Type: application/json');
    echo json_encode($data);
}

?>
