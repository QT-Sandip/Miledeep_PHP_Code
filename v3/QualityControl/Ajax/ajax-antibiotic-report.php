<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = $_SESSION['ClientConfigData'];
$DisabledABReportFields = $ClientConfigData['DisabledABReportFields'];

$api = API_ANTIBIOTIC_REPORT;

$methodType = $_POST["methodType"];

if($methodType == "GET"){

    $PageNo = trim($_POST["pageNo"]);
    $bodyContent = array(
        'FromDate' => trim($_POST["fromDate"]),
        'ToDate' => trim($_POST["toDate"]),
        'LotIds'=> trim($_POST["lotIds"]),
        'Type' => $methodType,
         'SearchKey' => trim($_POST["SearchKey"]),
        'pageNo' => trim($_POST["pageNo"]),
        'loadAll' => trim($_POST["loadAll"]),
        'hdnTotalRecords' => trim($_POST["hdnTotalRecords"])
    );
    
    
    $resList = CallAPI($api, "GET", "", $bodyContent);
    //echo '<pre>';print_r($resList);die;
}else if ($methodType == "GET_BATCH") {

    $bodyContent = array(
        'FromDate' => trim($_POST["fromDate"]),
        'ToDate' => trim($_POST["toDate"]),
        'Type' => $methodType,
    );
    
    $resList = CallAPI($api, "POST", "", $bodyContent);
    //echo '<pre>';print_r($resList);die;
    header('Content-Type: application/json');
    echo json_encode($resList);
    exit;
}

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->AntibioticReportData;
        // echo json_encode($List);
        // exit;
        $totalRows = $resList->totalRows;
        $PageNo = !empty($_POST['pageNo'])?$_POST['pageNo']:1;
        ?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>S.No</th>
                        <th>Batch No</th>
                        <th> Sample Type </th>
                        <!-- <th>Sample Details <br>(Date/Time/G.P:-No)</th> -->
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
                    <?php 
                        $pageStart = 0;
                        foreach ($List as $item) { 
                            if(empty($pageStart)) {
                                $pageStart = $item->SNO;
                            }
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
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->BatchNumber ?></td>
                            <td><?php echo $item->SampleType ?></td>
                            <!-- <td><?php echo $item->SampleDetails ?></td> -->
                            <td><?php echo $item->SamplerName ?></td>
                            <td><?php echo $item->ReceivedBy ?></td>
                            <td><?php echo $item->AnalysisDate ?></td>
                            <td><?php echo $item->CompletionDate ?></td>
                            <td><?php echo $InternalCAP ?></td>
                            <td><?php echo $InternalAOZ ?></td>
                            <td><?php echo $InternalAMOZ ?></td>
                            <td><?php echo $InternalAHD ?></td>
                            <td><?php echo $InternalSEM ?></td>
                            <td><?php echo $item->AnalyzedBy ?></td>
                            <td><?php echo $item->VerifiedBy ?></td>
                            <td><?php echo $item->ExternalSampleSendingDate ?></td>
                            <td><?php echo $item->ExternalLab ?></td>
                            <?php 
                                if(!empty($DisabledABReportFields)){?>
                                    <td><?php echo $ExternalCAP ?></td>
                                    <td><?php echo $ExternalAOZ ?></td>
                                    <td><?php echo $ExternalAMOZ ?></td>
                                    <td><?php echo $ExternalAHD ?></td>
                                    <td><?php echo $ExternalSEM ?></td>
                            <?php }?>
                            <td><?php echo $item->ExternalIssuedResultDate ?></td>
                            <td><?php echo !empty($item->ExternalLab) ? $item->Result : ''; ?></td>
                            <!-- <td></td> -->

                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing <?php echo $pageStart;?>  to <?php echo $item->SNO ?> out of <?php echo empty($_POST['loadAll']) ? $totalRows : $item->SNO ?> entries</div>
            <!-- $content .= '<div class="mt-3 mb-3">Showing 1 to ' . count($List) . ' out of ' . count($List) . ' entries</div>'; -->
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo count($List) ?>' />
            <ul class="pagination pagination-rounded <?php echo $PageCount<30?'justify-content-center':'';?>" style="overflow-x:auto;margin:0 auto;">

    <!-- Left Arrow -->
    <li class="page-item <?php echo ($PageNo == 1) ? 'disabled' : ''; ?>">
        <button class="page-link btnPageno" data-pageno="<?php echo $PageNo - 1; ?>">&laquo;</button>
    </li>

    <?php
    $range = 1; // pages around current
    $dotsAdded = false;

    for ($i = 1; $i <= $PageCount; $i++) {

        if (
            $i <= 2 || // first 2 pages
            $i > $PageCount - 2 || // last 2 pages
            ($i >= $PageNo - $range && $i <= $PageNo + $range) // near current
        ) {
            $pageActive = ($PageNo == $i) ? "active" : "";
            $disable = ($PageNo == $i) ? "disabled" : "";

            echo '<li class="page-item">
                    <button class="page-link '.$pageActive.' btnPageno" '.$disable.' data-pageno="'.$i.'">'.$i.'</button>
                  </li>';

            $dotsAdded = false;
        } else {
            if (!$dotsAdded) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                $dotsAdded = true;
            }
        }
    }
    ?>

    <!-- Right Arrow -->
    <li class="page-item <?php echo ($PageNo == $PageCount) ? 'disabled' : ''; ?>">
        <button class="page-link btnPageno" data-pageno="<?php echo $PageNo + 1; ?>">&raquo;</button>
    </li>

</ul>
        </div>
        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
}
?>
