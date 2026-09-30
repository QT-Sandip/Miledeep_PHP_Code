<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$mergeHonLoose = $ClientConfigData->mergeHonLoose;
$CheckingCount2 = $ClientConfigData->CheckingCount2;
$V2reportFormat = $ClientConfigData->V2reportFormat;
$hideColumsForCAPL = (!empty($_SESSION['ClientID']) && $_SESSION['ClientID'] == 'CAPLV3') ? 1 : 0;

$api = API_DEHEADING_OVERALL_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $StartDate = trim($_GET["fromDate"]);
    $EndDate = trim($_GET["toDate"]);
    $ReceivingPlantID = trim($_GET["receivingPlantID"]);
    $PlantName = trim($_GET["plantName"]);
    $border = "border=''";
    $firstRow = '<td colspan="19" style="text-align: center; font-weight: bold;">
            Deheading Report_Date:(' . date('d/m/Y', strtotime($StartDate)) . ' - ' . date('d/m/Y', strtotime($EndDate)) . ')_ReceivingPlant: ' . htmlspecialchars($PlantName) . '
            </td>';
} else {
    $StartDate = trim($_POST["fromDate"]);
    $EndDate = trim($_POST["toDate"]);
    $ReceivingPlantID = trim($_POST["receivingPlantID"]);
    $border = "";
    $firstRow = '';
}

$bodyContent = array(
    "StartDate" => $StartDate,
    "EndDate" => $EndDate,
    "ReceivingPlantID" => $ReceivingPlantID,
    "MergeHonLoose" => $mergeHonLoose,
    'V2reportFormat' => $V2reportFormat
);

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
        $resGrandTotals = $resList->GrandTotals;

        $content = '';
        $content .= '<table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered">';
        $content .= $firstRow;
        $V2Th = $V2RemarksTd = $V2footerTd = '';
        $ColSpan = 14;
        if($V2reportFormat == 1){
            $ColSpan = 22;
            $V2Th .= '<th style="min-width: 140px;width: 140px;">Plant</th>
                    <th style="min-width: 140px;width: 140px;">Agent Name</th>
                    <th style="min-width: 140px;width: 140px;">Grader Name</th>
                    <th style="min-width: 140px;width: 140px;">De Heading Date</th>';
            $V2RemarksTd .= '<td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>';
            $V2footerTd .= '<td style="min-width: 140px;width: 140px;"></td>
                            <td style="min-width: 140px;width: 140px;"></td>
                            <td style="min-width: 140px;width: 140px;"></td>
                            <td style="min-width: 140px;width: 140px;"></td>';
        }
        if($hideColumsForCAPL == 1){
            $ColSpan = 9;
        }

        $content .= '<thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 90px;width: 90px;">'.(($V2reportFormat == 1) ? 'RM Date' : 'Arrival Date').'</th>'.
                    (($V2reportFormat == 1) ? '' : '<th style="min-width: 140px;width: 140px;">Plant</th>').'                    
                    <th style="min-width: 140px;width: 140px;">Track Number</th>
                    
                    <th style="min-width: 120px;max-width: 120px;">BillNo</th>'
                    .$V2Th.'
                    <th style="min-width: 240px;max-width: 240px;">Variety</th>
                    <th style="min-width: 100px;max-width: 100px;">Count</th>
                    <th style="min-width: 100px;max-width: 100px;">Weight</th>'.
                    ($hideColumsForCAPL == 0 ? '<th style="min-width: 100px;max-width: 100px;">Sample HON Qty</th>' : '' ). 
                    (
                        ($V2reportFormat == 1)
                        ? '<th style="min-width: 100px;max-width: 100px;">HON Qty Recieved</th>
                        <th style="min-width: 100px;max-width: 100px;">HON Return</th>
                        <!-- <th style="min-width: 100px;max-width: 100px;">Checking Count</th> -->
                        <th style="min-width: 100px;max-width: 100px;">Checking Count-2</th>'
                        : ''
                    ).
                    ($hideColumsForCAPL == 0 ? 
                    '<th style="min-width: 100px;max-width: 100px;">No.of Pcs</th>
                    <th style="min-width: 100px;max-width: 100px;">Sample HL Qty</th>
                    <th style="min-width: 100px;max-width: 100px;">Count KGs</th>
                    <th style="min-width: 100px;max-width: 100px;">Count LBS</th>' : ''
                    ).
                    '<th style="min-width: 100px;max-width: 100px;">'.(($V2reportFormat == 1) ? 'Count Net Yield' : 'De-heading <br>Sample Yield').'</th>'.
                    (($V2reportFormat == 1) ? '<th style="min-width: 100px;max-width: 100px;">HL Yield	</th>' : '').'          
                    
                    <th style="min-width: 100px;max-width: 100px;">Grading HON Qty</th>
                    <th style="min-width: 100px;max-width: 100px;">Grading HL Qty</th>                    
                    <th style="min-width: 100px;max-width: 100px;">Grading Yield</th>

                    <th style="min-width: 100px;max-width: 100px;">Yield Difference</th>
                     <th style="min-width: 140px;width: 140px;">Remarks</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($List as $item) {
            $content .= '<tr>
                <td style="min-width:50px;">' . $item->SNo . '</td>
                <td style="min-width: 90px;width: 90px;">' . $item->ArrivalDate . '</td>'
                .(($V2reportFormat == 1) ? '' : '<td style="min-width: 140px;width: 140px;">' . $item->StationName . '</td>').'                
                <td style="min-width: 140px;width: 140px;">' . $item->TrackCode . '</td>
                <td style="padding: 0px !important;" colspan="'.$ColSpan.'">
                    <table ' . $border . ' class="subTable table table-bordered mb-0" style="width: 100%;">';

            $TotalArrivalQtyTotals = 0;
            $TotalHLQuantityKgs = 0;
            $GradingHON = 0;
            $GradingWeight = 0;
            foreach ($item->BreakUpData as $itemBreak) {
                $TotalArrivalQtyTotals += $itemBreak->ArrivalWeight;
                $TotalHLQuantityKgs += isset($itemBreak->HLQuantityKgs) ? floatval($itemBreak->HLQuantityKgs) : 0;
                $GradingHON += $itemBreak->GradingTotalHON;
                $GradingWeight += $itemBreak->GradingTotalHL;

                $content .= '<tr>
                    <td style="min-width: 120px;max-width: 120px;">' . $itemBreak->BillNumber . '</td>'.
                    (
                        ($V2reportFormat == 1)
                        ? '<td style="min-width: 140px;width: 140px;">' . ( !empty($itemBreak->StationName) ? $itemBreak->StationName : '' ). '</td>' .
                        '<td style="min-width: 140px;width: 140px;">' . ( !empty($itemBreak->Agent) ? $itemBreak->Agent : '' ) . '</td>' .
                        '<td style="min-width: 140px;width: 140px;">' . ( !empty($itemBreak->Grader) ? $itemBreak->Grader : '' ) . '</td>'.
                        '<td style="min-width: 140px;width: 140px;">' . ( !empty($itemBreak->DeheadingDate) ? $itemBreak->DeheadingDate : '' ) . '</td>'
                        : ''

                    ).'
                    <td style="min-width: 240px;max-width: 2400px;">' . $itemBreak->OverallVariety . '</td>
                    <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->ArrivalCount . '</td>
                    <td class="tdArrivalWeight" style="min-width: 100px;max-width: 100px;">' . $itemBreak->ArrivalWeight . '</td>'.
                    ($hideColumsForCAPL == 0 ? '<td style="min-width: 100px;max-width: 100px;">' . $itemBreak->HonQuantity . '</td>' : '' ).
                    (
                        ($V2reportFormat == 1)
                        ? '<td style="min-width: 100px;max-width: 100px;">' . ( !empty($itemBreak->HonQty) ? $itemBreak->HonQty : '0.00' ) . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . ( !empty($itemBreak->HonReturn) ? $itemBreak->HonReturn : '0.00' ) . '</td>'.
                        '<!-- <td style="min-width: 100px;max-width: 100px;">' . ( !empty($itemBreak->CheckingCount) ? $itemBreak->CheckingCount : '' ) . '</td> -->'.
                        '<td style="min-width: 100px;max-width: 100px;">' . ( !empty($itemBreak->CheckingCount2) ? $itemBreak->CheckingCount2 : '' ) . '</td>'
                        : ''
                    ).
                    ($hideColumsForCAPL == 0 ? 
                        '<td class="tdHlCount" style="min-width: 100px;max-width: 100px;">' . round($itemBreak->HLCount) . '</td>
                        <td class="tdHLQuantityKgs" style="min-width: 100px;max-width: 100px;">' . $itemBreak->HLQuantityKgs . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->ctHonCount . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->ctHLCount . '</td>' : ''
                    ).
                    '<td style="min-width: 100px;max-width: 100px;">' . $itemBreak->Yield . '</td>'.
                     (($V2reportFormat == 1) ? '<td style="min-width: 100px;max-width: 100px;">' . $itemBreak->HLYield . '</td>' : '').'

                    <td class="tdGradingTotalHon" style="min-width: 100px;max-width: 100px;">' . $itemBreak->GradingTotalHON . '</td>
                    <td class="tdGradingTotalHl" style="min-width: 100px;max-width: 100px;">' . $itemBreak->GradingTotalHL . '</td>                    
                    <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->GradingYield . '</td>

                    <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->YieldDiff . '</td>
                </tr>';
            }

            $content .= '</table>
          
                </td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>'. 
                        ($hideColumsForCAPL == 0 ? '<td style="display: none"></td>' : '' ).
                        $V2RemarksTd.
                        ($hideColumsForCAPL == 0 ? 
                            '<td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>' :  ''
                        ).
                        (
                            ($V2reportFormat == 1)
                            ? '<td style="display: none"></td>
                            <td style="display: none"></td>
                               <!-- <td style="display: none"></td> -->
                                <td style="display: none"></td>'
                            : ''
                        ).'
                        <td style="display: none"></td>'.
                        (
                            ($V2reportFormat == 1)
                            ? '<td style="display: none"></td>'
                            : ''
                        ).'

                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                         <td style="min-width: 140px;width: 140px;">' . $item->Remarks . '</td>
            </tr>';
        }
        $TotalGradingHON = !empty($resGrandTotals->TotalGradingHON) ? $resGrandTotals->TotalGradingHON : 0;
        $TotalGradingWeight = !empty($resGrandTotals->TotalGradingWeight) ? $resGrandTotals->TotalGradingWeight : 0;
        $TotalGreadingYeild = ($TotalGradingWeight / $TotalGradingHON)*100;
        $content .= '</tbody>
          <tfoot>
                <tr style = "font-weight: bold;">
                    <td style="font-weight: bold;">Grand Totals</td>'
                     .(($V2reportFormat == 1) ? '' : '<td></td>').'                    
                    <td></td>
                    <td></td>
                    <td></td>'.
                    $V2footerTd.'
                    <td></td>
                    <td></td>
                    <td id="ArrivalQtyTotals" style="font-weight: bold;"><b>' . number_format($resGrandTotals->ArrivalQtyTotals, 2) . '</b></td>'.
                    ($hideColumsForCAPL == 0 ? '<td></td>' : '' ).
                    (
                        ($V2reportFormat == 1)
                        ? '<td></td>
                            <td></td>
                            <td></td>'
                        : ''
                    ).
                    ($hideColumsForCAPL == 0 ? 
                    '<td></td>
                    <td id="TotalHLQuantityKgs" style="font-weight: bold;"><b>' . number_format($resGrandTotals->TotalSampleHLQty, 2) . '</b></td>
                    <td></td>
                    <td></td>' : '').
                    '<td></td>'.
                        (
                            ($V2reportFormat == 1)
                            ? '<td></td>'
                            : ''
                        ).'
                    <td id="TotalGradingHON" style="font-weight: bold;"><b>' . number_format($resGrandTotals->TotalGradingHON, 2) . '</b></td>
                    <td id="TotalGradingWeight" style="font-weight: bold;"><b>' . $resGrandTotals->TotalGradingWeight . '</b></td>
                    <td id="TotalGradingYeild"  style="font-weight: bold;"><b>'.number_format($TotalGreadingYeild,2).'</td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <input type="hidden" id="hdnTotalRecords" value="' . $List->TotalCount . '" />
        <ul class="pagination pagination-rounded justify-content-center">';

        for ($i = 1; $i <= $PageCount; $i++) {
            $pageActive = ($PageNo == $i ? "active" : "");
            $content .= '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
        }

        $content .= '</ul>';


    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $filename = 'Deheading_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename);
}
echo $content;
