<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_FEED_MILL_AUDIT_REPORT;

$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(
    'PlantID' => trim($_POST["productionPlant"]),
    'Year' => trim($_POST["year"]),
    // 'Month' => trim($_POST["month"]),
    'AuditNameID' => trim($_POST["auditName"]),
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
        $List = $resList->Data;
?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 80px">S.No</th>
                        <th>Production Plant</th>
                        <th>Audit Name</th>
                        <th>Farmer Location</th>
                        <th>Certification Body</th>
                        <th>Registration No</th>
                        <th>Certification No</th>
                        <th>BAP Number</th>
                        <th>Audit Date</th>
                        <th>Issued On</th>
                        <th>Validity Upto</th>
                        <th>Renewal Date</th>
                        <th>Checked By</th>
                        <th>Verified By</th>
                        <th>Remarks</th>
                        <th>Total Payments</th>
                        <th>Audit Status</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->StationName ?></td>
                            <td><?php echo $item->CategoryName ?></td>
                            <td><?php echo $item->FarmerName ?></td>
                            <td style="min-width: 100px"><?php echo $item->CertificationBodyName ?></td>
                            <td><?php echo $item->RegistrationNo ?></td>
                            <td><?php echo $item->CertificationNumber ?></td>
                            <td><?php echo $item->BAPNumber ?></td>
                            <td style="min-width:80px"><?php echo $item->AuditDate ? date('d-m-Y', strtotime($item->AuditDate)) : '' ?></td>
                            <td style="min-width:80px"><?php echo $item->IssuedOn ? date('d-m-Y', strtotime($item->IssuedOn)) : '' ?></td>
                            <td style="min-width:80px"><?php echo $item->ValidityUpto ? date('d-m-Y', strtotime($item->ValidityUpto)) : '' ?></td>
                            <td style="min-width:80px"><?php echo $item->RenewalDate ? date('d-m-Y', strtotime($item->RenewalDate)) : '' ?></td>
                            <td><?php echo $item->CheckedByName ?></td>
                            <td><?php echo $item->VerifiedByName ?></td>
                            <td><?php echo $item->Remarks ?></td>
                            <td><?php echo $item->PaymentTotalAmount ?></td>
                            <td style="min-width: 100px;"><?php echo $item->AuditStatusName ?></td>
                            <td>
                                <div class="bg-active color-palette text-center">
                                    <span class="btn btn-link badge badge-warning font-size-10 btnPIStatus" style="width: 70px; color: <?php echo $item->TextColor ?>; background-color: <?php echo $item->DisplayColor ?>;" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                        <?php echo $item->StatusMessage ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
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