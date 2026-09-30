<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_QC_KITS;

$PageNo = trim($_POST["pageNo"]);
$AccessType = trim($_POST["accessType"]);
$Action = trim($_POST["Action"]);
$DeleteAccess = trim($_POST["DeleteAccess"]);

if ($Action == "Delete") {
    $KitID = trim($_POST["KitID"]);
    $bodyContent = array('KitID' => $KitID, 'PageNo' => $PageNo);
    $DeleteAccess = trim($_POST["DeleteAccess"]);


    // Call the API to delete the record
    $resDelete = CallAPI($api, "DELETE", "", $bodyContent);

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($resDelete);
} else {
    $bodyContent = array(
        'FromDate' => trim($_POST["fromDate"]),
        'ToDate' => trim($_POST["toDate"]),
        'SearchKey' => trim($_POST["searchKey"]),
        'PageNo' => trim($_POST["pageNo"]),
        'LoadAll' => trim($_POST["LoadAll"]),
        'DeleteAccess' => $DeleteAccess,
    );
    $resList = CallAPI($api, "GET", "", $bodyContent);
}
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;
    //$List = $resList->Data;

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
                        <th>Parameter</th>
                        <th>Ordered Date</th>
                        <th>Received Date</th>
                        <th>Lot No</th>
                        <th>Ordered Kits</th>
                        <th>Received Kits</th>
                        <th>Invoice No</th>
                        <th>Total Wells</th>
                        <th>Manufacture Date</th>
                        <th>Expiry Date</th>
                        <?php if ($AccessType == "2" || $AccessType == "1") { ?>
                            <th>Actions</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->Parameter ?></td>
                            <td style="min-width:80px">
                                <?php echo $item->KitsOrderedDate ? date('d-m-Y', strtotime($item->KitsOrderedDate)) : '' ?>
                            </td>
                            <td style="min-width:80px">
                                <?php echo $item->KitsReceivedDate ? date('d-m-Y', strtotime($item->KitsReceivedDate)) : '' ?>
                            </td>
                            <td><?php echo $item->KitsLotNo ?></td>
                            <td><?php echo $item->NoOfKitsOrdered ?></td>
                            <td><?php echo $item->NoOfKitsReceived ?></td>
                            <td><?php echo $item->InvoiceNo ?></td>
                            <td><?php echo $item->TotalWells ?></td>
                            <td style="min-width:80px">
                                <?php echo $item->ManufactureDate ? date('d-m-Y', strtotime($item->ManufactureDate)) : '' ?>
                            </td>

                            <td style="min-width:80px">
                                <?php echo $item->ExpiryDate ? date('d-m-Y', strtotime($item->ExpiryDate)) : '' ?>
                            </td>
                            <?php if ($AccessType == "2" || $AccessType == "1") { ?>
                                <td class="text-center">


                                    <?php if ($item->DeleteAccess == 1) { ?>

                                        <a href='<?php echo $item->EditURL ?>' class="mr-3 text-primary btnActions" data-placement="top"
                                            title="Edit" data-original-title="Edit">
                                            <i class="mdi mdi-pencil font-size-16"></i>
                                        </a>


                                        <button type="button" name="btnDelete" data-kitid="<?php echo $item->KitID ?>"
                                            class="btn btn-link p-0 btnDelete">
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                        </button>

                                    <?php } else { ?>

                                        <a href='<?php echo $item->EditURL . "&DeleteAccess=1" ?>' class="mr-3 text-primary btnActions"
                                            data-placement="top" title="Edit" data-original-title="Edit">
                                            <i class="mdi mdi-pencil font-size-16"></i>
                                        </a>

                                        <button type="button" class="btn btn-link p-0 btnDelete" disabled>
                                            <i class="mdi mdi-delete font-size-16 text-secondary"></i>
                                        </button>
                                    <?php } ?>

                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
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