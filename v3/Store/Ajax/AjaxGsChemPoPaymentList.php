<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GS_CHEM_PO_PAYMT_LIST;

$ChemPoID = trim($_POST["chemPoID"]);
$Buyer = trim($_POST["buyer"]);
$Supplier = trim($_POST["supplier"]);
$Year = trim($_POST["year"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

$bodyContent = array(
    'ChemPoID' => $ChemPoID,
    'BuyerName' => $Buyer,
    'ChemPoDate' => $Year,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll,
    'Supplier' => $Supplier
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
                    <th>Chemical Name</th>
                    <th>Party Name</th>
                    <th>Recieved Quantity (Kgs)</th>
                    <th>Amount (Rupees)</th>
                    <th>Paid Amount (Rupees)</th>
                    <th>Bank Reference No</th>
                    <th>Payment  Date</th>
                   <!--  <th>Invoice Amount</th>
                    <th>Payment Details</th> -->
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->ChemPoNo ?></td>
                        <td><?php echo $item->ChemicalNames ?></td>
                        <td><?php echo $item->BuyerName ?></td>
                        <td><?php echo $item->TotalQty ?></td>
                        <td><?php echo $item->Amount ?></td>
                        <td><?php echo $item->PaidAmount ?></td>
                        <td><?php echo $item->InvoiceNos ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->LstInvDt)) ?></td>
                        <!-- <td><?php echo $item->TotalInvAmount ?></td>
                        <td><?php echo $item->LstPmtDtls ?></td> -->
                        <td><?php echo $item->Remarks ?></td>
                        <td><?php if ($item->Status == 0) {
                                $color = '#FA0F04';
                            } else {
                                $color = '#025009';
                            } ?>
                            <div class="bg-active color-palette text-center">
                                <span class="btn btn-link badge badge-warning font-size-10 btnPIStatus" style="width:70px;background:<?php echo $color ?>;color:white" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                    <?php echo ($item->Status == 0) ? "PENDING" : "PAYMENT DONE"; ?>
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($resList->EditAccess) { ?>
                                <a href='<?php echo $resList->EditLink . $item->LatestChemPaymentID.'&ChemPoID='.$item->ChemPoID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit PO" data-original-title="Edit">
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