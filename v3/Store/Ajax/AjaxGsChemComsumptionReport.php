<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GS_CHEM_PO;

// $ChemPoID = trim($_POST["chemPoID"]);
$Plant = trim($_POST["plant"]);
$Chemical = trim($_POST["chemical"]);
$FromDate = trim($_POST["fromDate"]);
$ToDate = trim($_POST["toDate"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

$bodyContent = array(
    // 'ChemPoID' => $ChemPoID,
    'ChemPlant' => $Plant,
    'Chemical' => $Chemical,
    'FromDate' => $FromDate,
    'ToDate' => $ToDate,
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
                    <th>Chemical Name</th>
                    <th>Date</th>
                    <th>Trans Type</th>
                    <th>Supplier</th>
                    <th>Recieved Plant</th>
                    <th>Shift to Plant</th>
                    <th>Opening Stock in Bags</th>
                    <th>Issued Stock in Bags</th>
                    <th>Closing Stock in Bags</th>
                    <th>Opening Stock in KGs</th>
                    <th>Issued Stock in KGs</th>
                    <th>Closing Stock in KGs</th>
                </tr>
            </thead>
            <tbody>
                <?php // foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo "1" ?></td>
                        <td><?php echo "TEST CHEMICAL" ?></td>
                        <td><?php echo "02-02-2024" ?></td>
                        <td><?php echo "TEST TYPE" ?></td>
                        <td><?php echo "TEST SUPPLIER" ?></td>
                        <td><?php echo "TEST PLANT" ?></td>
                        <td><?php echo "TEST PLANT 2" ?></td>
                        <td><?php echo "2" ?></td>
                        <td><?php echo "4" ?></td>
                        <td><?php echo "5" ?></td>
                        <td><?php echo "10" ?></td>
                        <td><?php echo "15" ?></td>
                        <td><?php echo "20" ?></td>
                    </tr>
                <?php // } ?>
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