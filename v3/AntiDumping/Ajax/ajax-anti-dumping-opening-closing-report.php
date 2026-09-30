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

    $api = API_ANTI_DUMPING_OPENING_CLOSING_REPORT; // API_CREATE_DAILY_PRODUCTION_DATA;

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

    $api = API_ANTI_DUMPING_OPENING_CLOSING_REPORT; // API_CREATE_DAILY_PRODUCTION_DATA;

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
    $api = API_ANTI_DUMPING_OPENING_CLOSING_REPORT;

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

    $api = API_ANTI_DUMPING_OPENING_CLOSING_REPORT;
    $border = '';

    if (isset($_GET["excelDownload"])) {
        $bodyContent = array(
            "ProductionDate" => $_GET["txtProductionDate"],
            "ProductionPlant" => $_GET["ProductionPlant"],
            "ProductionToDate" => $_GET["txtProductionToDate"],
            "PoNumber" => $_GET["drpPoNumber"],
            "Variety" => $_GET["drpVariety"],
            "PackingStyle" => $_GET["drpPackingStyle"],
            "Grade" => $_GET["drpGrade"],
            "FreezingType" => $_GET["drpFreezingType"],
            "Soaking" => $_GET["drpSoaking"],
            "ProductCategory" => $_GET["drpProductCategory"],
            "Type" => $_GET["drpType"],
            "LoadAll" => $_GET["LoadAll"],
            "PageNo" => $_GET["PageNo"],
            "MoreSearch" => $_GET["MoreSearch"],
        );
        $border = "border='1'";
    } else {
        $bodyContent = array(
            "ProductionDate" => $_POST["txtProductionDate"],
            "ProductionPlant" => $_POST["drpProductionPlant"],
            "ProductionToDate" => $_POST["txtProductionToDate"],
            "PoNumber" => $_POST["drpPoNumber"],
            "Variety" => $_POST["drpVariety"],
            "PackingStyle" => $_POST["drpPackingStyle"],
            "Grade" => $_POST["drpGrade"],
            "FreezingType" => $_POST["drpFreezingType"],
            "Soaking" => $_POST["drpSoaking"],
            "ProductCategory" => $_POST["drpProductCategory"],
            "Type" => $_POST["drpType"],
            "LoadAll" => $_POST["LoadAll"],
            "PageNo" => $_POST["PageNo"],
            "MoreSearch" => $_POST["MoreSearch"],
        );
    }

    $res = CallAPI($api, "GET", "", $bodyContent);
    // echo json_encode($res);exit;

    if (!empty($res)) {

        $error = $res->Error;

        if (empty($error)) {

            $listSummary = $res->MonthlySummary;
            $MonthsData = $res->MonthsData;
            $PageNo = !empty($_POST["PageNo"]) ? trim($_POST["PageNo"]) : 1;

            $content = '<div class="card">';

            $content .= '<div class="card-body">
            <table id="tblDataTable" ' . $border . ' cellspacing="0" cellpadding="3" class="table table-hover table-bordered display nowrap" style="border-collapse:collapse;">
            <thead class="thead-light">';

            $content .= '<tr>';
            $content .= '<th >S.No</th>';
            $content .= '<th  style="min-width:120px">Production Plant</th>';
            $content .= '<th >SUPC</th>';
            $content .= '<th  style="min-width:200px">Product</th>';
            $content .= '<th >Grade</th>';
            $content .= '<th  style="min-width:80px">Packing Style</th>';
            foreach ($MonthsData as $month) {
                $content .= '
        <th>Opening Stock</th>
        <th>Production</th>
        <th>Repacking In</th>
        <th>IBT In</th>
        <th>PSP IN</th>
        <th>FG IN</th>
        <th>Return Container</th>
        <th>Shipments</th>
        <th>Thawing</th>
        <th>Repacking Out</th>
        <th>IBT Out</th>
        <th>Audit Out</th>
        <th>Local Sales</th>
        <th>PSP Out</th>
        <th>Closing Stock</th>';
            }
            $content .= '</tr>';
            $content .= '</thead><tbody>';

            // Header Row 1
            $content .= '<tr>';
            $content .= '<td style="background-color: #e1e1e3;"></td>';
            $content .= '<td  style="min-width:120px;background-color: #e1e1e3;"></td>';
            $content .= '<td style="background-color: #e1e1e3;"></td>';
            $content .= '<td  style="min-width:200px;background-color: #e1e1e3;"></td>';
            $content .= '<td style="background-color: #e1e1e3;"></td>';
            $content .= '<td  style="min-width:80px;background-color: #e1e1e3;"></td>';

            if (isset($_GET["excelDownload"])) {
                foreach ($MonthsData as $month) {
                    $monthName = date("F-Y", strtotime($month . "-01"));
                    $content .= '<td colspan="15" style="text-align:center;font-weight:550;background-color: #e1e1e3;">' . $monthName . '</td>';
                }
            } else {
                foreach ($MonthsData as $month) {
                    $monthName = date("F-Y", strtotime($month . "-01"));
                    $content .= '<td colspan="15" style="text-align:center;font-weight:550;background-color: #e1e1e3;">' . $monthName . '</td>
                <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>
            <td style="display: none"></td>';
                }
            }
            $content .= '</tr>';

            // body rows
            $pageStart = 0;
            $lastSno = 0;
            if (isset($_GET["excelDownload"])) {
                $Units = explode(',', $_GET["ProductionPlant"]);
            } else {
                $Units = explode(',', $_POST["drpProductionPlant"]);
            }
            for ($iUnit = 0; $iUnit < count($Units); $iUnit++) {
                $list = $listSummary[$iUnit]->MonthlySummary;
                foreach ($list as $item) {
                    // echo print_r($item);
                    if (empty($pageStart)) {
                        $pageStart = $item->SNo;
                    }
                    $lastSno = $lastSno + 1;

                    $content .= '<tr>
                        <td>' . $lastSno . '</td>
                        <td style="min-width: 120px">' . $item->ProductionPlantName . '</td>
                        <td>' . $item->OurSupc . '</td>
                        <td style="min-width: 200px">' . $item->ProductDescription . '</td>
                        <td>' . $item->Grade . '</td>
                        <td style="min-width: 80px">' . $item->PackingStyle . '</td>';

                    $previousData = [];
                    $prevMonthData = [];
                    foreach ($MonthsData as $month) {

                        $varietyData = isset($item->varietyData) ? (array) $item->varietyData : [];

                        $keys = array_keys($varietyData);

                        $lastKey = null;

                        for ($i = count($keys) - 1; $i >= 0; $i--) {
                            if ($keys[$i] <= $month) {
                                $lastKey = $keys[$i];
                                break;
                            }
                        }


                        //$lastKey = array_keys($varietyData)[count($varietyData) - 1];

                        $lastIndex = [];
                        if (isset($varietyData[$lastKey])) {
                            $lastIndex['OpeningStock'] = [];
                            $lastIndex['OpeningStock']['OpeningStock'] = $varietyData[$lastKey]->OpeningStock->ClosingStock;
                            $lastIndex['OpeningStock']['ClosingStock'] = $varietyData[$lastKey]->OpeningStock->ClosingStock;
                            $lastIndex = json_decode(json_encode($lastIndex), false);// convert array to object

                        }

                        $monthData = isset($varietyData[$month]) ? (array) $varietyData[$month] : (array) $lastIndex;


                        // 👉 STORE previous month BEFORE overriding
                        if (isset($previousData) && !empty($previousData)) {
                            $prevMonthData = $previousData;
                        }

                        // 👉 Extract Opening / Closing safely
                        $opening = '-';
                        $closing = '-';

                        if (isset($monthData['OpeningStock'])) {

                            $opening = $monthData['OpeningStock']->OpeningStock;
                            $closing = $monthData['OpeningStock']->ClosingStock;
                        }

                        // 🔥 APPLY UI CARRY FORWARD
                        if (($opening === '-') && !empty($prevMonthData)) {
                            if (isset($prevMonthData['OpeningStock'])) {
                                $opening = $prevMonthData['OpeningStock']->ClosingStock;
                            }
                        }

                        if (($closing === '-') && !empty($prevMonthData)) {
                            if (isset($prevMonthData['OpeningStock'])) {
                                $closing = $prevMonthData['OpeningStock']->ClosingStock;
                            }
                        }

                        $content .= '
                                <td class="OpeningStock">' . $opening . '</td>
                                <td class="Production">' . ((isset($monthData['Production']) && $monthData['Production']->TotalAvalSlabs != 0) ? $monthData['Production']->TotalAvalSlabs : '-') . '</td>
                                <td class="RepackingIN">' . ((isset($monthData['RepackingIN']) && $monthData['RepackingIN']->TotalAvalSlabs != 0) ? $monthData['RepackingIN']->TotalAvalSlabs : '-') . '</td>
                                <td class="IbtIn">' . ((isset($monthData['IbtIn']) && $monthData['IbtIn']->TotalAvalSlabs != 0) ? $monthData['IbtIn']->TotalAvalSlabs : '-') . '</td>
                                <td class="PhysicalIn">' . ((isset($monthData['PhysicalIn']) && $monthData['PhysicalIn']->TotalAvalSlabs != 0) ? $monthData['PhysicalIn']->TotalAvalSlabs : '-') . '</td>
                                <td class="FGIN">' . ((isset($monthData['FGIN']) && $monthData['FGIN']->TotalAvalSlabs != 0) ? $monthData['FGIN']->TotalAvalSlabs : '-') . '</td>
                                <td class="ReturnContainer">' . ((isset($monthData['ReturnContainer']) && $monthData['ReturnContainer']->TotalAvalSlabs != 0) ? $monthData['ReturnContainer']->TotalAvalSlabs : '-') . '</td>
                                <td class="ShipmentOut">' . ((isset($monthData['ShipmentOut']) && $monthData['ShipmentOut']->TotalAvalSlabs != 0) ? $monthData['ShipmentOut']->TotalAvalSlabs : '-') . '</td>
                                <td class="Melting">' . ((isset($monthData['Melting']) && $monthData['Melting']->TotalAvalSlabs != 0) ? $monthData['Melting']->TotalAvalSlabs : '-') . '</td>
                                <td class="RepackingOut">' . ((isset($monthData['RepackingOut']) && $monthData['RepackingOut']->TotalAvalSlabs != 0) ? $monthData['RepackingOut']->TotalAvalSlabs : '-') . '</td>
                                <td class="IbtOut">' . ((isset($monthData['IbtOut']) && $monthData['IbtOut']->TotalAvalSlabs != 0) ? $monthData['IbtOut']->TotalAvalSlabs : '-') . '</td>
                                <td class="AuditOut">' . ((isset($monthData['AuditOut']) && $monthData['AuditOut']->TotalAvalSlabs != 0) ? $monthData['AuditOut']->TotalAvalSlabs : '-') . '</td>
                                <td class="LocalSales">' . ((isset($monthData['LocalSales']) && $monthData['LocalSales']->TotalAvalSlabs != 0) ? $monthData['LocalSales']->TotalAvalSlabs : '-') . '</td>
                                <td class="PhysicalOut">' . ((isset($monthData['PhysicalOut']) && $monthData['PhysicalOut']->TotalAvalSlabs != 0) ? $monthData['PhysicalOut']->TotalAvalSlabs : '-') . '</td>
                                <td class="ClosingStock">' . $closing . '</td>';

                        // 👉 SAVE CURRENT AS PREVIOUS FOR NEXT LOOP
                        $previousData = $monthData;

                    }

                    // foreach ($MonthsData as $month) {
                    //     $varietyData = isset($item->varietyData) ? (array) $item->varietyData : [];
                    //     // print_r($varietyData);

                    //     $monthData = isset($varietyData[$month]) ? (array) $varietyData[$month] : [];
                    //     // print_r($monthData);exit;

                    //     $content .= '
                    //         <td class="OpeningStock">' . ((isset($monthData['OpeningStock'])) ? $monthData['OpeningStock']->OpeningStock : '-') . '</td>
                    //         <td class="Production">' . ((isset($monthData['Production']) && $monthData['Production']->TotalAvalSlabs != 0) ? $monthData['Production']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="RepackingIN">' . ((isset($monthData['RepackingIN']) && $monthData['RepackingIN']->TotalAvalSlabs != 0) ? $monthData['RepackingIN']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="IbtIn">' . ((isset($monthData['IbtIn']) && $monthData['IbtIn']->TotalAvalSlabs != 0) ? $monthData['IbtIn']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="PhysicalIn">' . ((isset($monthData['PhysicalIn']) && $monthData['PhysicalIn']->TotalAvalSlabs != 0) ? $monthData['PhysicalIn']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="FGIN">' . ((isset($monthData['FGIN']) && $monthData['FGIN']->TotalAvalSlabs != 0) ? $monthData['FGIN']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="ReturnContainer">' . ((isset($monthData['ReturnContainer']) && $monthData['ReturnContainer']->TotalAvalSlabs != 0) ? $monthData['ReturnContainer']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="ShipmentOut">' . ((isset($monthData['ShipmentOut']) && $monthData['ShipmentOut']->TotalAvalSlabs != 0) ? $monthData['ShipmentOut']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="Melting">' . ((isset($monthData['Melting']) && $monthData['Melting']->TotalAvalSlabs != 0) ? $monthData['Melting']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="RepackingOut">' . ((isset($monthData['RepackingOut']) && $monthData['RepackingOut']->TotalAvalSlabs != 0) ? $monthData['RepackingOut']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="IbtOut">' . ((isset($monthData['IbtOut']) && $monthData['IbtOut']->TotalAvalSlabs != 0) ? $monthData['IbtOut']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="AuditOut">' . ((isset($monthData['AuditOut']) && $monthData['AuditOut']->TotalAvalSlabs != 0) ? $monthData['AuditOut']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="LocalSales">' . ((isset($monthData['LocalSales']) && $monthData['LocalSales']->TotalAvalSlabs != 0) ? $monthData['LocalSales']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="PhysicalOut">' . ((isset($monthData['PhysicalOut']) && $monthData['PhysicalOut']->TotalAvalSlabs != 0) ? $monthData['PhysicalOut']->TotalAvalSlabs : '-') . '</td>
                    //         <td class="ClosingStock">' . ((isset($monthData['OpeningStock'])) ? $monthData['OpeningStock']->ClosingStock : '-') . '</td>';

                    // }


                    $content .= '</tr>';
                }

            }
            $content .= '</tbody>';

            // // totals
            // $content .= '<tfoot><tr>
            //     <td></td>
            //     <td><b>Totals</b></td>
            //     <td colspan="4"></td>';

            // foreach ($MonthsData as $month) {
            //     $varietyColData = isset($ColumnTotals) ? (array) $ColumnTotals : [];
            //     $monthColData = isset($varietyColData[$month]) ? (array) $varietyColData[$month] : [];
            //     // echo print_r($monthColData);exit;
            //     $content .= '
            //            <td id="TotalOpeningStock">' . ((isset($monthColData['OpeningStock'])) ? $monthColData['OpeningStock']->OpeningStock : '-') . '</td>
            //             <td id="TotalProduction">' . ((isset($monthColData['Production']) && $monthColData['Production']->TotalAvalSlabs != 0) ? $monthColData['Production']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalRepackingIN">' . ((isset($monthColData['RepackingIN']) && $monthColData['RepackingIN']->TotalAvalSlabs != 0) ? $monthColData['RepackingIN']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalIbtIn">' . ((isset($monthColData['IbtIn']) && $monthColData['IbtIn']->TotalAvalSlabs != 0) ? $monthColData['IbtIn']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalPhysicalIn">' . ((isset($monthColData['PhysicalIn']) && $monthColData['PhysicalIn']->TotalAvalSlabs != 0) ? $monthColData['PhysicalIn']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalFGIN">' . ((isset($monthColData['FGIN']) && $monthColData['FGIN']->TotalAvalSlabs != 0) ? $monthColData['FGIN']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalReturnContainer">' . ((isset($monthColData['ReturnContainer']) && $monthColData['ReturnContainer']->TotalAvalSlabs != 0) ? $monthColData['ReturnContainer']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalShipmentOut">' . ((isset($monthColData['ShipmentOut']) && $monthColData['ShipmentOut']->TotalAvalSlabs != 0) ? $monthColData['ShipmentOut']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalMelting">' . ((isset($monthColData['Melting']) && $monthColData['Melting']->TotalAvalSlabs != 0) ? $monthColData['Melting']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalRepackingOut">' . ((isset($monthColData['RepackingOut']) && $monthColData['RepackingOut']->TotalAvalSlabs != 0) ? $monthColData['RepackingOut']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalIbtOut">' . ((isset($monthColData['IbtOut']) && $monthColData['IbtOut']->TotalAvalSlabs != 0) ? $monthColData['IbtOut']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalAuditOut">' . ((isset($monthColData['AuditOut']) && $monthColData['AuditOut']->TotalAvalSlabs != 0) ? $monthColData['AuditOut']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalLocalSales">' . ((isset($monthColData['LocalSales']) && $monthColData['LocalSales']->TotalAvalSlabs != 0) ? $monthColData['LocalSales']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalPhysicalOut">' . ((isset($monthColData['PhysicalOut']) && $monthColData['PhysicalOut']->TotalAvalSlabs != 0) ? $monthColData['PhysicalOut']->TotalAvalSlabs : '-') . '</td>
            //             <td id="TotalClosingStock">' . ((isset($monthColData['OpeningStock'])) ? $monthColData['OpeningStock']->ClosingStock : '-') . '</td>';
            // }
            $content .= '</table>';

            // pagination (screen only)
            // if (!isset($_GET["excelDownload"]) || $_GET["excelDownload"] != "T") {
            //     $content .= '<div>';
            //     if (!empty($resTotal) && $lastSno > 0) {
            //         $content .= '<div class="mt-3 mb-3">Showing ' . $pageStart . ' to ' . $lastSno . ' out of ' . $resTotal . ' entries </div>';
            //         $content .= '<ul class="pagination pagination-rounded ' . ($PageCount < 30 ? 'justify-content-center' : '') . '" style="overflow-x:auto;margin:0 auto;">';
            //         for ($i = 1; $i <= $PageCount; $i++) {
            //             $pageActive = ($PageNo == $i ? "active" : "");
            //             $content .= '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
            //         }
            //         $content .= '</ul>';
            //     }
            //     $content .= '</div>';
            // }

            $content .= '</div></div>';

            // Excel headers
            if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
                $filename = 'Anti-Dumping-Opening-Closing-Report.xls';
                header("Content-Type: application/vnd.ms-excel; charset=utf-8");
                header("Content-Disposition: attachment; filename=" . $filename);
                header("Pragma: no-cache");
                header("Expires: 0");
            }

            echo $content;

        } else {
            echo '<div>ERROR: ' . $error . '</div>';
        }
    }
}

if ($methodType == "getPackingStyle") {
    $api = API_ANTI_DUMPING_OPENING_CLOSING_REPORT; // API_CREATE_DAILY_PRODUCTION_DATA;

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