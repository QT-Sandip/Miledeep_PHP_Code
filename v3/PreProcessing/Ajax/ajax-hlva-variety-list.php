<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HLVA_VARIETY_LIST;

$Station = trim($_POST["station"]);
$VarietyID = trim($_POST["shortName"]);
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'Station' => $Station,
    'VarietyID' => $VarietyID,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resList = CallAPI($api, "GET", "", $bodyContent);

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
                    <th>S.NO</th>
                    <th>Species</th>
                    <th>Type Product</th>
                    <th>Short Name</th>
                    <th>Full Name</th>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Product Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->Species ?></td>
                        <td><?php echo $item->TypeProduct ?></td>
                        <td><?php echo $item->ShortName ?></td>
                        <td><?php echo $item->FullName ?></td>
                        <td><?php echo $item->Category ?></td>
                        <td><?php echo $item->Product ?></td>
                        <td><?php echo $item->ProductDescription ?></td>
                        <td><?php if ($item->Status == 0) {
                                $color = '#FA0F04';
                                $colorClass = "badge-danger";
                            } else {
                                $color = '#025009';
                                $colorClass = "badge-success";
                            } ?>
                            <div class="bg-active color-palette text-center">
                                <span class="btn btn-link badge <?php echo $colorClass; ?> font-size-10 btnPIStatus" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                    <?php echo ($item->Status == 0) ? "In-Active" : "Active"; ?>
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($resList->EditAccess) { ?>
                                <a href='<?php echo $resList->EditLink . $item->VarietyID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit Variety" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
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
