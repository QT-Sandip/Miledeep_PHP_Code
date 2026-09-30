<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ERROR);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}


if ($methodType == "getList" || isset($_GET["excelDownload"])) {

    $api = API_ANTI_DUMPING_CLOSING_REPORT;
    $border = '';

    if (isset($_GET["excelDownload"])) {
        $Units = explode(',', $_GET["ProductionPlant"]);
        $bodyContent = array(
            "ProductionDate" => $_GET["txtProductionDate"],
            "ProductionPlant" => $_GET["ProductionPlant"],
            "ProductionToDate" => $_GET["txtProductionToDate"],
            "PoNumber" => $_GET["drpPoNumber"],
            "Variety" => ($_GET["drpVariety"]!='undefined')?$_GET["drpVariety"]:'',
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
        $Units = explode(',', $_POST["drpProductionPlant"]);
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
    //echo '<pre>';print_r( $res);die;
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

            $content .= '
            <tr>';
                $content .= '<th >S.No</th>';
                $content .= '<th  style="min-width:120px">Production Plant</th>';
                $content .= '<th >SUPC</th>';
                $content .= '<th  style="min-width:200px">Product</th>';
                $content .= '<th >Grade</th>';
                $content .= '<th  style="min-width:80px">Packing Style</th>';
                //foreach ($MonthsData as $month) {
                    $content .= '
                    <th>Slabs</th>
                    <th>M/C</th>
                    <th>Kgs</th>';
               // }
                $content .= '
                <th>Remarks</th>
            </tr>';
            $content .= '
            </thead><tbody>';


            // body rows
            $pageStart = 0;
            $lastSno = 0;
            
            //echo '<pre>';print_r($listSummary);die;
            $VarietyWiseConsolidateArray = [];
            foreach($listSummary as $key => $row){//plant wise
                // echo '<pre>';print_r($key);
                foreach($row->MonthlySummary as $item){
                    $varietyData = isset($item->varietyData) ? (array) $item->varietyData : [];
                    $keys = array_keys($varietyData);
                    $lastKey = end($keys);
                    $ClosingMcs = 0;
                    $ClosingSlabs = 0;
                    $ClosingKgs = 0;

                    if (isset($varietyData[$lastKey])) {
                        //$lastIndex['OpeningStock'] = [];
                        $ClosingMcs = $varietyData[$lastKey]->OpeningStock->ClosingMcs?$varietyData[$lastKey]->OpeningStock->ClosingMcs:0;
                        $ClosingSlabs = $varietyData[$lastKey]->OpeningStock->ClosingSlabs?$varietyData[$lastKey]->OpeningStock->ClosingSlabs:0;//2808
                        $ClosingKgs = $varietyData[$lastKey]->OpeningStock->ClosingKgs?$varietyData[$lastKey]->OpeningStock->ClosingKgs:0;
                        //$lastIndex = json_decode(json_encode($lastIndex), false);// convert array to object
                    }
                    $searchId = $item->varietyId;
                    $foundKey = array_search(
                                    $searchId,
                                    array_column($VarietyWiseConsolidateArray, 'varietyId')
                                );
                    $ids = array_column($VarietyWiseConsolidateArray, 'varietyId');

                    $foundKey = array_search((string)$searchId, array_map('strval', $ids));
                    if ($foundKey !== false) {
                       // echo $foundKey;die;
                        $VarietyWiseConsolidateArray[$foundKey]['ClosingMcs'] += $ClosingMcs;
                        $VarietyWiseConsolidateArray[$foundKey]['ClosingSlabs'] += $ClosingSlabs;
                        $VarietyWiseConsolidateArray[$foundKey]['ClosingKgs'] += $ClosingKgs;
                    }else{
                        $VarietyWiseConsolidate['varietyId'] = $item->varietyId;
                        $VarietyWiseConsolidate['ProductDescription'] = $item->ProductDescription;
                        $VarietyWiseConsolidate['Grade'] = $item->Grade;
                        $VarietyWiseConsolidate['PackingStyle'] = $item->PackingStyle;
                        $VarietyWiseConsolidate['OurSupc'] = $item->OurSupc;
                       // $VarietyWiseConsolidate['varietyData'] = $item->varietyData;
                        $VarietyWiseConsolidate['PlantID'] = $item->PlantID;
                        $VarietyWiseConsolidate['ProductionPlantName'] = count($Units) <= 1 ? $item->ProductionPlantName : 'All';
                        $VarietyWiseConsolidate['ClosingMcs'] = $ClosingMcs;
                        $VarietyWiseConsolidate['ClosingSlabs'] = $ClosingSlabs;
                        $VarietyWiseConsolidate['ClosingKgs'] = $ClosingKgs;
                        $VarietyWiseConsolidateArray[] = $VarietyWiseConsolidate;
                    }
                }               
            }//echo count($Units);die;
           //echo '<pre>';print_r($VarietyWiseConsolidateArray); die;

            //for ($iUnit = 0; $iUnit < count($Units); $iUnit++) {
               // $list = $listSummary[$iUnit]->MonthlySummary;
                foreach ($VarietyWiseConsolidateArray as $item) {
                    // echo print_r($item);
                    if($item['ClosingSlabs'] > 0){
                        if (empty($pageStart)) {
                            $pageStart = $item->SNo;
                        }
                        $lastSno = $lastSno + 1;

                        $content .= '<tr>
                            <td>' . $lastSno . '</td>
                            <td style="min-width: 120px">' . $item['ProductionPlantName'] . '</td>
                            <td>' . $item['OurSupc'] . '</td>
                            <td style="min-width: 200px">' . $item['ProductDescription'] . '</td>
                            <td>' . $item['Grade'] . '</td>
                            <td style="min-width: 80px">' . $item['PackingStyle'] . '</td>';

                    

                            $content .= '
                            <td class="ClosingSlabs">' . $item['ClosingSlabs'] . '</td>
                            <td class="ClosingMcs">' . $item['ClosingMcs'] . '</td>
                            <td class="ClosingKgs">' . $item['ClosingKgs'] . '</td>';

                            // 👉 SAVE CURRENT AS PREVIOUS FOR NEXT LOOP
                        //  $previousData = $monthData;

                        //} // foreach end

                        $content .= '
                        <td></td> <!-- Remarks -->
                        </tr>';
                    }
                }

           // }
            $content .= '</tbody>';
            $content .= '</table>';

            $content .= '</div></div>';

            // Excel headers
            if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
                $filename = 'Anti-Dumping-Closing-Report.xls';
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


?>