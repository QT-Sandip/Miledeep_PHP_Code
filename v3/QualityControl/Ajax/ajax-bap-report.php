<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_BAAP_SHEET_REPORT;

$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(
    'FromDate' => trim($_POST["fromDate"]),
    'ToDate' => trim($_POST["toDate"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'PlantID' => trim($_POST["plantID"]),
    'LoadAll' => trim($_POST["loadAll"]),
);
$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->BapReportData;
        ?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width: 40px">S.No</th>
                        <th>Rm Quantity</th>
                        <th>Head Less</th>
                        <th style="min-width: 180px;">Variety</th>
                        <th>Grade</th>
                        <th>Code</th>
                        <th style="min-width: 90px">Farmer Id</th>
                        <th style="min-width: 90px">Registration Number</th>
                        <th style="min-width: 90px">Packing</th>
                        <th style="min-width: 150px">Brand</th>
                        <th>Finished</th>
                        <th>Total No.Of M/C</th>
                        <th>Phosphate(Carnal Carfosel)/Non Phosphate(NEO,BT)</th>
                        <th>Salt</th>
                        <th>Duplex/ Pouch(PP)</th>
                        <th>Master Cartons(CP)</th>
                        <th style="min-width: 150px">Po Number</th>
                        <th style="min-width: 150px">Invoice Number</th>
                        <th style="min-width: 150px">Invoice Line Number</th>
                        <th>Container No</th>
                        <th>Destination</th>
                        <th style="min-width: 150px">Buyer Name</th>
                        <th style="min-width: 100px">Shipping Date</th>
                        <th style="min-width: 90px">BAP Star Status</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td style="min-width: 40px"><?php echo $item->SNO ?></td>
                            <td data-value=<?php echo $item->ReceivedQty ?>><?php echo number_format($item->ReceivedQty, 3) ?></td>
                            <td data-value=<?php echo $item->HeadlessQty ?>><?php echo number_format($item->HeadlessQty, 3) ?></td>
                            <td style="min-width: 180px;"><?php echo $item->ProductDescription ?></td>
                            <td><?php echo $item->Grade ?></td>
                            <td><?php echo $item->DayCode ?></td>
                            <td style="min-width: 90px"><?php echo $item->FarmerID ?></td>
                            <td style="min-width: 90px"><?php echo $item->RegistrationNumber ?></td>
                            <td style="min-width: 90px"><?php echo $item->PackingStyle ?></td>
                            <td style="min-width: 150px"><?php echo $item->Brand ?></td>
                            <td data-value=<?php echo $item->FinishedQty ?>><?php echo number_format($item->FinishedQty, 3) ?></td>
                            <td><?php echo $item->McsTotal ?></td>
                            <td><?php echo $item->ChemicalBatchNo ?></td>
                            <td><?php echo $item->SaltOrNpt ?></td>
                            <td><?php echo $item->PackingMasterCartons ?></td>
                            <td><?php echo $item->PackingPouch ?></td>
                            <td style="min-width: 150px"><?php echo $item->PoNo ?></td>
                            <td style="min-width: 150px"><?php echo $item->CustomsInvNo ?></td>
                            <td style="min-width: 150px"><?php echo $item->SaleInvoiceLineNumber ?></td>
                            <td><?php echo $item->ContainerNo ?></td>
                            <td><?php echo $item->Destination ?></td>
                            <td style="min-width: 150px"><?php echo $item->ConsigneeName ?></td>
                            <td style="min-width: 100px"><?php echo $item->ShipByDate ?></td>
                            <td style="min-width: 90px"><?php echo $item->CerificationName ?></td>

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
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>
