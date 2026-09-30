<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_SOCIAL_AUDIT_REPORT;

$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(
    //'PlantID' => trim($_POST["productionPlant"]),
    'PlantID' => trim($_POST["productionPlant"]),
    'Year' => trim($_POST["year"]),
    'AuditNameID' => trim($_POST["auditName"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'PageNo' => trim($_POST["pageNo"]),
    'LoadAll' => trim($_POST["loadAll"])
);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;

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
                        <th>Audit Type</th>
                        <th>Certification Body</th>
                        <th>Window Period</th>
                        <th>Grade/Score</th>
                        <th>Site</th>
                        <th>Audit Start Date</th>
                        <th>Audit End Date</th>
                        <th>Issued On</th>
                        <th>Validity Upto</th>
                        <th>Renewal Date</th>
                        <th>Checked By</th>
                        <th>Verified By</th>
                        <th>Remarks</th>
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
                            <td><?php echo $item->OptionName ?></td>
                            <td style="min-width: 100px"><?php echo $item->CertificationBodyName ?></td>
                            <td style="min-width:80px"><?php echo $item->WindowPeriod ? date('d-m-Y', strtotime($item->WindowPeriod)) : '' ?></td>
                            <td><?php echo $item->GradeNScore ?></td>
                            <td><?php echo $item->SiteName ?></td>
                            <td style="min-width:80px"><?php echo $item->AuditStartDate ? date('d-m-Y', strtotime($item->AuditStartDate)) : '' ?></td>
                            <td style="min-width:80px"><?php echo $item->AuditEndDate ? date('d-m-Y', strtotime($item->AuditEndDate)) : '' ?></td>
                            <td style="min-width:80px"><?php echo $item->IssuedOn ? date('d-m-Y', strtotime($item->IssuedOn)) : '' ?></td>
                            <td style="min-width:80px"><?php echo $item->ValidityUpto ? date('d-m-Y', strtotime($item->ValidityUpto)) : '' ?></td>
                            <td style="min-width:80px"><?php echo $item->RenewalDate ? date('d-m-Y', strtotime($item->RenewalDate)) : '' ?></td>
                            <td><?php echo $item->CheckedByName ?></td>
                            <td><?php echo $item->VerifiedByName ?></td>
                            <td><?php echo $item->Remarks ?></td>
                            <td style="min-width: 100px;"><?php echo $item->AuditStatusName ?></td>
                            <td>
                                <div class="bg-active color-palette text-center">
                                    <span class="btn btn-link badge font-size-10 btnPIStatus" style="color: <?php echo $item->TextColor ?>; background-color: <?php echo $item->DisplayColor ?>;" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
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
