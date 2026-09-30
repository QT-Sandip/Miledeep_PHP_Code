<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GS_CHEM_CONSUMPSTION_LIST;

$ChemPoID = trim($_POST["chemPoID"]);
$Chemical = trim($_POST["chemical"]);
$StationID = trim($_POST["proPlant"]);
// $FromDate = trim($_POST["fromDate"]);
// $ToDate = trim($_POST["toDate"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

$bodyContent = array(
    'ChemPoID' => $ChemPoID,
    'ChemicalID' => $Chemical,
    'PlantID' => $StationID,
    // 'ChemPoDate' => $StationID,
    // 'ChemPoDate' => $ChemID,
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
                    <th>PoNo</th>
                    <th>Date</th>
                    <th>Invoice Number</th>
                    <th>Plant Name</th>
                    <th>Supplier</th>
                    <th>CONSUMPTION Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->ChemPoNo ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->EntryDate)) ?></td>
                        <td><?php echo $item->InvoiceNos ?></td>
                        <td><?php echo $item->Plants ?></td>
                        <td><?php echo $item->Buyers ?></td>
                        <td><?php echo $item->TotalQty ?></td>
                        <td class="text-center">
                            <?php if ($resList->EditAccess) { ?>
                                <a href='<?php echo $resList->EditLink . $item->ChemPoID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit Payment" data-original-title="Edit">
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