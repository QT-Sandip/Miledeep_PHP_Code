<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PROFORMA_LIST;

$ExporterID = trim($_POST["ExporterID"]);
$CountryID = trim($_POST["CountryID"]);
$ConsigneeID = trim($_POST["ConsigneeID"]);
$Status = trim($_POST["Status"]);
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'ExporterID' => $ExporterID,
    'CountryID' => $CountryID,
    'ConsigneeID' => $ConsigneeID,
    'Status' => $Status,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);


$resProforma = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resProforma, JSON_PRETTY_PRINT);
// exit;

if (isset($resProforma) && !empty($resProforma)) {

    $error = $resProforma->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resProforma->PageCount;
        $resList = $resProforma->Data;

?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th>ID</th>
                    <th>Processor</th>
                    <th>PInv Number</th>
                    <th>PInv Date</th>
                    <th>Po No</th>
                    <th>Buyer</th>
                    <th>Production Plant</th>
                    <th>ShipBy Date</th>
                    <th>Status</th>
                    <th style="min-width:140px;" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resList as $item) {
                    //  echo   $resProforma->PrintLink; exit;
                ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->ID ?></td>
                        <td><?php echo $item->Processor ?></td>
                        <td class="nowrap"><?php echo $item->PInvNumber ?></td>
                        <td class="nowrap"><?php echo $item->PInvDate ?></td>
                        <td><?php echo $item->PoNo ?></td>
                        <td><?php echo $item->Buyer ?></td>
                        <td><?php echo $item->FullName ?></td>
                        <td class="nowrap"><?php echo $item->ShipByDate ?></td>
                        <td>
                            <div class="bg-active color-palette text-center">
                                <button class="btn btn-link badge font-size-10 btnPIStatus" style="background:<?php echo $item->Color ?>;color:white" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                    <?php echo $item->Status ?>
                                </button>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($resProforma->EditAccess) { 
                                if($item->IsPoEdit){ ?>
                                    <a href="<?php echo $resProforma->EditLink . $item->ID ?>" class="mr-3 text-primary btnActions" data-placement="top" title="Edit Proforma" data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>
                                <?php }
                                ?>


                            <?php } ?>
                            <a target="_blank" href="<?php echo $resProforma->PrintLink . $item->ID ?>" class="mr-3 text-primary btnActions" data-placement="top" title="Print Proforma" data-original-title="Edit">
                                <i class="mdi mdi-printer  font-size-16"></i>
                            </a>
                            <button class="mr-3 text-primary btnActions btnDuplicate" data-placement="top" title="Duplicate" data-original-title="Edit" data-id="<?php echo $item->ID ?>" data-value="">
                                <i class="ri-file-copy-line  font-size-16"></i>
                            </button>
                            <?php if ($resProforma->DeleteAccess) { ?>
                                <button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="<?php echo $item->ID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
                                    <i class="mdi mdi-trash-can font-size-16"></i></button>
                            <?php } ?>

                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($resList) ?> out of <?php echo $resProforma->TotalCount ?> entries</div>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resProforma->TotalCount ?>' /> -->
            <ul class="pagination pagination-rounded justify-content-center">
<?php
$range = 2; // how many pages to show around current page

// LEFT ARROW (Previous)
if ($PageNo > 1) {
    echo '<li class="page-item">
            <button class="page-link btnPageno" data-pageno="'.($PageNo - 1).'">&laquo;</button>
          </li>';
}

// FIRST PAGE
if ($PageNo > ($range + 1)) {
    echo '<li class="page-item">
            <button class="page-link btnPageno" data-pageno="1">1</button>
          </li>';

    if ($PageNo > ($range + 2)) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
}

// MIDDLE PAGES
for ($i = max(1, $PageNo - $range); $i <= min($PageCount, $PageNo + $range); $i++) {
    $pageActive = ($PageNo == $i ? "active" : "");
    echo '<li class="page-item">
            <button class="page-link '.$pageActive.' btnPageno" data-pageno="'.$i.'">'.$i.'</button>
          </li>';
}

// LAST PAGE
if ($PageNo < ($PageCount - $range)) {
    if ($PageNo < ($PageCount - $range - 1)) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    echo '<li class="page-item">
            <button class="page-link btnPageno" data-pageno="'.$PageCount.'">'.$PageCount.'</button>
          </li>';
}

// RIGHT ARROW (Next)
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
