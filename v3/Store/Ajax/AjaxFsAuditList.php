<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_FS_AUDIT;

$FsaID = trim($_POST["fsaID"]);
$PlantID = trim($_POST["proPlant"]);
$Year = trim($_POST["year"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

$bodyContent = array(
    'FsaID' => $FsaID,
    'PlantID' => $PlantID,
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
                    <th>Plant Name</th>
                    <th>Audit Name </th>
                    <th>Audit Type</th>
                    <th>Certification Authority</th>
                    <th>Registration Number</th>
                    <th>Certification Number</th>
                    <th>Audit Start Date</th>
                    <th>Audit End Date</th>
                    <th>Audit Program</th>
                    <th>Issued On</th>
                    <th>Valid Upto</th>
                    <th>Renewal Date</th>
                    <th>Audit Grade</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->StationName ?></td>
                        <td><?php echo $item->AuditName ?></td>
                        <td><?php echo $item->AuditType ?></td>
                        <td><?php echo $item->CertAuthority ?></td>
                        <td><?php echo $item->RegistrartionNo ?></td>
                        <td><?php echo $item->CertificateNo ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->AuditStartDt)) ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->AuditEndDt)) ?></td>
                        <td><?php echo $item->AuditPrgm ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->IssuedOnDt)) ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->ValidUpTo)) ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->RenewaLDt)) ?></td>
                        <td><?php echo $item->AuditGrd ?></td>
                        <td><?php if ($item->Status == 0) {
                                $color = '#FA0F04';
                            } else {
                                $color = '#025009';
                            } ?>
                            <div class="bg-active color-palette text-center">
                                <span class="btn btn-link badge badge-warning font-size-10 btnPIStatus" style="width:70px;background:<?php echo $color ?>;color:white" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                    <?php echo ($item->Status == 0) ? "In-Active" : "Active"; ?>
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($resList->EditAccess) { ?>
                                <a href='<?php echo $resList->EditLink . $item->FsaID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit Fs Audit" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            <?php  } ?>
                            <?php if ($resList->AddAccess) { ?>
                                <a href='<?php echo $resList->AddLink . $item->FsaID ?>' class="mr-3 text-primary  btnActions" data-placement="top" title="Add Payment" data-original-title="Edit">
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