<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GS_CHEM_PO;

$Year = trim($_POST["year"]);
$Supplier = trim($_POST["supplier"]);
$PoNo = trim($_POST["poNo"]);
$Chemical = trim($_POST["chemical"]);
$Port = trim($_POST["port"]);

$bodyContent = array(
    'Year' => $Year,
    'Supplier' => $Supplier,
    'PoNo' => $PoNo,
    'Chemical' => $Chemical,
    'Port' => $Port,
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
$resList = CallAPI($api, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="width: 25px;">S.NO</th>
                    <th>Supplier Name</th>
                    <th>PoNo</th>
                    <th>PO Date</th>
                    <th>Chemicals</th>
                    <th>Port Name</th>
                    <th>Mode of Import</th>
                    <th>Quantity(Bags)</th>
                    <th>Quantity(KGs)</th>
                    <th>Total Amount</th>
                    <!-- <th>Amount(USD)</th> -->
                    <th>ETD Date</th>
                    <th>ETA Date</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->SupplierName ?></td>
                        <td><?php echo $item->ChemPoNo ?></td>
                        <td style="width: 100px"><?php echo date('d-m-Y', strtotime($item->ChemPoDate)) ?></td>
                        <td><?php echo $item->ChemicalNames ?></td>
                        <td><?php echo $item->PortName ?></td>
                        <td><?php echo $item->Mode ?></td>
                        <td><?php echo $item->TotalBags ?></td>
                        <td><?php echo $item->TotalQty ?></td>
                        <td><?php echo $item->TotalAmount ?></td>
                        <!-- <td><?php echo $item->TotalAmountUSD ? '$ ' . $item->TotalAmountUSD : '' ?></td> -->
                        <td style="width: 100px"><?php echo $item->ChemETDDate ? date('d-m-Y', strtotime($item->ChemETDDate)) : '' ?></td>
                        <td style="width: 100px"><?php echo $item->ChemETADate ? date('d-m-Y', strtotime($item->ChemETADate)) : '' ?></td>
                        <td><?php echo $item->Remarks ?></td>
                        <td class="text-center" style="width: 130px">
                            <?php if ($resList->EditAccess) { ?>
                                <a href='<?php echo $resList->EditLink . $item->ChemPoID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit PO" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            <?php  } ?>
                            <?php if ($resList->AddAccess) { ?>
                                <a href='<?php echo $resList->AddLink . $item->ChemPoID ?>' class="mr-3 text-primary  btnActions" data-placement="top" title="ADD PO" data-original-title="Edit">
                                    <i class="mdi mdi-plus font-size-16"></i></i>
                                </a>
                            <?php  } ?>
                            <?php if ($resList->PrintAccess) { ?>
                                <a target="_blank" href='<?php echo $resList->PrintLink . $item->ChemPoID ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Print PO " data-original-title="Edit">
                                    <i class="mdi mdi-printer  font-size-16"></i>
                                </a>
                                <a target="_blank" href='Reports/chemical-po-word-doc.php?ChemPoID=<?php echo $item->ChemPoID ?>' class="mr-3 text-success btnActions" data-placement="top" title="Doc " data-original-title="Edit">
                                    <i class="mdi mdi-file-word  font-size-16"></i>
                                </a>
                            <?php  } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>
<?php }
} ?>
