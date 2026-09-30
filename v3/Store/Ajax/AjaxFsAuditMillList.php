<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_FS_AUDIT_MILL;

$AuditID = trim($_POST["auditID"]);
$AuditType = trim($_POST["auditType"]);
$Year = trim($_POST["year"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

$bodyContent = array(
    'AuditID' => $AuditID,
    'AuditType' => $AuditType,
    'ChemPoDate' => $Year,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="width: 25px;">S.NO</th>
                    <th>Audit Type</th>
                    <th>Farm Name</th>
                    <th>Farmer Name </th>
                    <th>Hatchry Location </th>
                    <th>Certified Body</th>
                    <th>Associated Factory</th>
                    <th>Government Authority </th>
                    <th>Gov Registration Number</th>
                    <th>BAP Number</th>
                    <th>Tonnage/Annum</th>
                    <th>Water Spread Area</th>
                    <th>Audit Date </th>
                    <th>Issued On Date </th>
                    <th>Validty Upto </th>
                    <th>Status </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->Audit ?></td>
                        <td><?php echo $item->HatcryName ?></td>
                        <td><?php echo $item->FarmerName ?></td>
                        <td><?php echo $item->HatcryLocation ?></td>
                        <td><?php echo $item->CertiBody ?></td>
                        <td><?php echo $item->StationName ?></td>
                        <td><?php echo $item->Authority ?></td>
                        <td><?php echo $item->RegistrationNo ?></td>
                        <td><?php echo $item->BAPNo ?></td>
                        <td><?php echo $item->ProductionPerAnnum ?></td>
                        <td><?php echo $item->WaterSpreadArea ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->AuditDate)) ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->IssuedOn)) ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->ValidtyUpto)) ?></td>
                        <td><?php if ($item->Status == 1) {
                                $statusText = "Active";
                                $color = '#025009';
                            } elseif ($item->Status == 2) {
                                $statusText = "In-Active";
                                $color = '#FA0F04';
                            } elseif ($item->Status == 3) {
                                $statusText = "Renewal";
                                $color = '#74788d';
                            } else {
                                $statusText = "Unknown";
                                $color = '#000000';
                            } ?>
                            <div class="bg-active color-palette text-center">
                                <span class="btn btn-link badge badge-warning font-size-10 btnPIStatus" style="width:70px;background:<?php echo $color ?>;color:white" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($resList->EditAccess) { ?>
                                <a href='<?php echo $resList->EditLink . $item->AuditID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit Fs Audit" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            <?php  } ?>
                            <?php if ($resList->AddAccess) { ?>
                                <a href='<?php echo $resList->AddLink . $item->AuditID ?>' class="mr-3 text-primary  btnActions" data-placement="top" title="Add Payment" data-original-title="Edit">
                                    <i class="btn-success fa fa-plus"></i></i>
                                </a>
                            <?php  } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' /> -->
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . 'btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
<?php }
} ?>