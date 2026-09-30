<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = $_SESSION['ClientConfigData'];
$DisabledABReportFields = $ClientConfigData['DisabledABReportFields'];

$methodType = trim($_POST["methodType"]);
$fromDate = trim($_POST["fromDate"]);
$toDate = trim($_POST["toDate"]);
$ReqType = isset($_POST["reqType"]) ? trim($_POST["reqType"]) : "";
$PageNo = isset($_POST['PageNo']) ? intval($_POST['PageNo']) : 1;

$api = API_DASHBOARD_GRAPHS;

// ---------- Graph Data for all types including new ones ----------
if (in_array($methodType, array("antibioticGraphData", "microGraphData", "swabGraphData", "waterIceGraphData"))) {

    $bodyContent = array(
        'FromDate' => $fromDate,
        'ToDate' => $toDate,
        'ReqType' => $ReqType,
        'Type' => $methodType
    );

    $resList = CallAPI($api, "POST", "", $bodyContent);
    // echo json_encode($resList);exit;
    header('Content-Type: application/json');

    // Safely get months & series from API response
    $months = isset($resList->Data->months)
        ? $resList->Data->months
        : (isset($resList->Data->GraphData->months)
            ? $resList->Data->GraphData->months
            : array());

    $series = isset($resList->Data->series)
        ? $resList->Data->series
        : (isset($resList->Data->GraphData->series)
            ? $resList->Data->GraphData->series
            : array());

    // 🔹 Normalize Swab Graph Data Keys
    if ($methodType == "swabGraphData" && !empty($series)) {
        $formattedSeries = [];
        foreach ($series as $item) {
            $formattedItem = new stdClass();
            foreach ($item as $key => $value) {
                $lookup = strtolower(preg_replace('/[^a-z0-9]/', '', $key));

                if (strpos($lookup, 'worker') !== false) {
                    $newKey = 'Workers Swab';
                } elseif (strpos($lookup, 'food') !== false && strpos($lookup, 'contact') !== false) {
                    $newKey = 'Food Contact Surfaces';
                } elseif (strpos($lookup, 'flake') !== false || strpos($lookup, 'plate') !== false || strpos($lookup, 'ice') !== false) {
                    $newKey = 'FLATE ICE & PLATE ICE CHUTE';
                } elseif (strpos($lookup, 'detected') !== false) {
                    $newKey = 'Detected Samples';
                } else {
                    $newKey = $key;
                }

                $formattedItem->{$newKey} = $value;
            }
            $formattedSeries[] = $formattedItem;
        }
        $series = $formattedSeries;
    }

    // 🔹 Handle empty data (default fallback for graphs)
    if (!isset($resList->Data) || empty($months) || empty($series)) {

        $defaultMonth = !empty($fromDate) ? date('Y-m', strtotime($fromDate)) : date('Y-m');

        if ($methodType == "microGraphData") {
            $months = [$defaultMonth];
            $series = [
                ['name' => 'Tested Samples', 'data' => [0]],
                ['name' => 'Detected Samples', 'data' => [0]]
            ];
        } elseif ($methodType == "swabGraphData") {
            $months = [$defaultMonth];
            $series = [
                ['name' => 'Workers Swab', 'data' => [0]],
                ['name' => 'Food Contact Surfaces', 'data' => [0]],
                ['name' => 'FLATE ICE & PLATE ICE CHUTE', 'data' => [0]]
            ];
        } elseif ($methodType == "waterIceGraphData") {
            $months = [$defaultMonth];
            $series = [
                ['name' => 'TPC Result', 'data' => [0]],
                ['name' => 'Yeast & Mold', 'data' => [0]],
                ['name' => 'Coliform', 'data' => [0]]
            ];
        } else {
            $months = [$defaultMonth];
            $series = [['name' => 'Samples', 'data' => [0]]];
        }

        echo json_encode([
            "Error" => false,
            "Msg" => "No Data (defaulted to zero values)",
            "Data" => [
                    "months" => $months,
                    "series" => $series
                ]
        ]);
        exit;
    }

    // 🔹 Send final data to frontend
    echo json_encode(array(
        "Error" => isset($resList->Error) ? $resList->Error : false,
        "Msg" => isset($resList->Msg) ? $resList->Msg : "Success",
        "Data" => array(
                "months" => $months,
                "series" => $series
            )
    ));
    exit;
}

// ---------- Antibiotic Table ----------
else if ($methodType == "antibioticData") {

    $bodyContent = array(
        'FromDate' => $fromDate,
        'ToDate' => $toDate,
        'Type' => $methodType,
        'ReqType' => $ReqType
    );

    $resList = CallAPI($api, "POST", "", $bodyContent);

    if (!empty($resList) && isset($resList->Error)) {
        $error = $resList->Error;

        if (!$error) {
            $PageCount = isset($resList->PageCount) ? $resList->PageCount : 1;
            $resTotal = isset($resList->TotalCount) ? $resList->TotalCount : count($resList->Data);
            $List = isset($resList->Data) ? $resList->Data : array();
            ?>
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>S.No</th>
                            <th>Batch No</th>
                            <th>Sample Type</th>
                            <th>Sample Details <br>(Date/Time/G.P:-No)</th>
                            <th>Sampler Name</th>
                            <th>Received By</th>
                            <th>Date of Analysis</th>
                            <th>Date of Completion</th>
                            <th>CAP</th>
                            <th>AOZ</th>
                            <th>AMOZ</th>
                            <th>AHD</th>
                            <th>SEM</th>
                            <th>Analyzed By</th>
                            <th>Verified By</th>
                            <th>External Sample Sending Date</th>
                            <th>External Lab Name</th>
                            <?php 
                            if(!empty($DisabledABReportFields)){?>
                                <th>CAP</th>
                                <th>AOZ</th>
                                <th>AMOZ</th>
                                <th>AHD</th>
                                <th>SEM</th>
                            <?php } ?>
                            <th>External Issued Result Date</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($List as $item) { 
                            $InternalCAP = $InternalAOZ = $InternalAMOZ = $InternalSEM = $InternalAHD = '--';
                            $ExternalCAP = $ExternalAOZ = $ExternalAMOZ = $ExternalSEM = $ExternalAHD = '--';

                            $type = (!empty($DisabledABReportFields) && !empty($item->ExternalLab))
                                ? 'External'
                                : 'Internal';

                            ${$type . 'CAP'}  = $item->CAP;
                            ${$type . 'AOZ'}  = $item->AOZ;
                            ${$type . 'AMOZ'} = $item->AMOZ;
                            ${$type . 'SEM'}  = $item->SEM;
                            ${$type . 'AHD'}  = $item->AHD;
                        ?>
                            <tr>
                                <td><?= isset($item->SNO) ? $item->SNO : '' ?></td>
                                <td><?= isset($item->BatchNumber) ? $item->BatchNumber : '' ?></td>
                                <td><?= isset($item->SampleType) ? $item->SampleType : '' ?></td>
                                <td><?= isset($item->SampleDetails) ? $item->SampleDetails : '' ?></td>
                                <td><?= isset($item->SamplerName) ? $item->SamplerName : '' ?></td>
                                <td><?= isset($item->ReceivedBy) ? $item->ReceivedBy : '' ?></td>
                                <td><?= isset($item->AnalysisDate) ? $item->AnalysisDate : '' ?></td>
                                <td><?= isset($item->CompletionDate) ? $item->CompletionDate : '' ?></td>
                                <td><?php echo $InternalCAP ?></td>
                                <td><?php echo $InternalAOZ ?></td>
                                <td><?php echo $InternalAMOZ ?></td>
                                <td><?php echo $InternalAHD ?></td>
                                <td><?php echo $InternalSEM ?></td>
                                <td><?= isset($item->AnalyzedBy) ? $item->AnalyzedBy : '' ?></td>
                                <td><?= isset($item->VerifiedBy) ? $item->VerifiedBy : '' ?></td>
                                <td><?= isset($item->ExternalSampleSendingDate) ? $item->ExternalSampleSendingDate : '' ?></td>
                                <td><?= isset($item->ExternalLab) ? $item->ExternalLab : '' ?></td>
                                <?php 
                                    if(!empty($DisabledABReportFields)){?>
                                        <td><?php echo $ExternalCAP ?></td>
                                        <td><?php echo $ExternalAOZ ?></td>
                                        <td><?php echo $ExternalAMOZ ?></td>
                                        <td><?php echo $ExternalAHD ?></td>
                                        <td><?php echo $ExternalSEM ?></td>
                                <?php }?>
                                <td><?= isset($item->ExternalIssuedResultDate) ? $item->ExternalIssuedResultDate : '' ?></td>
                                <td><?= isset($item->Result) && isset($item->ExternalLab) ? $item->Result : '' ?></td>
                            </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <div class="mt-3 mb-3">
                    Showing 1 to <?= count($List) ?> out of <?= $resTotal ?> entries
                </div>

                <input type='hidden' id='hdnTotalRecords' value='<?= $resTotal ?>' />
                <ul class="pagination pagination-rounded justify-content-center">
                    <?php
                    for ($i = 1; $i <= $PageCount; $i++) {
                        $pageActive = ($PageNo == $i ? "active" : "");
                        echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                    }
                    ?>
                </ul>
            <?php
        } else {
            echo "<div class='text-center mt-3 mb-3 font-weight-bold'>NOTE: " . $error . "</div>";
        }
    } else {
        echo "<div class='text-center mt-3 mb-3 font-weight-bold'>No data found</div>";
    }
}

// ---------- Water & Ice Table ----------
else if ($methodType == "waterIceGraphData") {

    $bodyContent = array(
        'FromDate' => $fromDate,
        'ToDate' => $toDate,
        'tpcType' => isset($_POST['tpcType']) ? $_POST['tpcType'] : 'TPC1',
        'ReqType' => $ReqType
    );

    $resList = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');

    if (!empty($resList) && isset($resList->Error) && !$resList->Error) {

        $months = isset($resList->Data->months) ? $resList->Data->months : [];
        $series = isset($resList->Data->series) ? $resList->Data->series : [];

        echo json_encode([
            'Error' => false,
            'Msg' => isset($resList->Msg) ? $resList->Msg : 'Success',
            'Data' => [
                    'months' => $months,
                    'series' => $series
                ]
        ]);
    } else {
        // fallback 0 values
        $defaultMonth = !empty($fromDate) ? date('Y-m', strtotime($fromDate)) : date('Y-m');
        echo json_encode([
            'Error' => false,
            'Msg' => 'No graph data (default 0)',
            'Data' => [
                    'months' => [$defaultMonth],
                    'series' => [
                            ['name' => 'TPC Result', 'data' => [0]],
                            ['name' => 'Yeast & Mold', 'data' => [0]],
                            ['name' => 'Coliform', 'data' => [0]]
                        ]
                ]
        ]);
    }
    exit;
}

// ---------- Food Contact Surface Table and Flake Ice Table ----------
?>