<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_TRACEABILITY_UTILIZED_REPORT;

$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(
    'FromDate' => trim($_POST["fromDate"]),
    'ToDate' => trim($_POST["toDate"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'PageNo' => trim($_POST["pageNo"]),
    'LoadAll' => trim($_POST["loadAll"])
);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// śexit;

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->TraceabilityData;
        ?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 80px">S.No</th>
                        <th style="min-width: 120px;width: 120px;">Traceability</th>
                        <th style="min-width: 120px;width: 120px;">Tonnage</th>
                        <th style="min-width: 120px;width: 120px;">Balance</th>
                        <th style="min-width: 120px;width: 120px;">Plant</th>
                        <th style="min-width: 120px;width: 120px;">Utilized Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td style="width: 80px"><?php echo $item->SNO ?></td>
                            <td style="min-width: 120px;width: 120px;"><?php echo $item->Tracebility ?></td>
                            <td style="min-width: 120px;width: 120px;"><?php echo $item->Tonnage ?></td>
                            <td style="min-width: 120px;width: 120px;"><?php echo $item->BalanceQty ?></td>
                            <td style="padding: 0px !important;" colspan="2">
                                <table class="subTable table table-bordered mb-0" style="width: 100%">
                                    <?php foreach ($item->BreakUpData as $ItemBreakUp) { ?>
                                        <tr>
                                            <td style="min-width: 120px;width: 120px;"><?php echo $ItemBreakUp->StationName ?></td>
                                            <td style="min-width: 120px;width: 120px;"><?php echo $ItemBreakUp->UsedQty ?></td>
                                        </tr>

                                    <?php } ?>
                                </table>
                            </td>
                            <td style="display: none"></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
             <ul class="pagination pagination-rounded justify-content-center">

<?php
// Previous button
if ($PageNo > 1) {
    echo '<li class="page-item">
            <button class="page-link btnPageno" data-pageno="'.($PageNo - 1).'">&laquo;</button>
          </li>';
}

$range = 1; // pages around current page

for ($i = 1; $i <= $PageCount; $i++) {

    // Always show first 2 and last 2 pages
    if (
        $i <= 2 ||
        $i > $PageCount - 2 ||
        ($i >= $PageNo - $range && $i <= $PageNo + $range)
    ) {

        $pageActive = ($PageNo == $i ? "active" : "");

        echo '<li class="page-item">
                <button class="page-link '.$pageActive.' btnPageno" data-pageno="'.$i.'">'.$i.'</button>
              </li>';

    } else {

        // Add dots only once
        if (
            ($i == 3 && $PageNo > 4) ||
            ($i == $PageCount - 2 && $PageNo < $PageCount - 3)
        ) {
            echo '<li class="page-item disabled">
                    <span class="page-link">...</span>
                  </li>';
        }
    }
}

// Next button
if ($PageNo < $PageCount) {
    echo '<li class="page-item">
            <button class="page-link btnPageno" data-pageno="'.($PageNo + 1).'">&raquo;</button>
          </li>';
}
?>

</ul>
        </div>
        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $error . "</div>";
    }
}
?>
