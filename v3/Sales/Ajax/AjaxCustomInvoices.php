<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CUSTOM_INVOICES;

$ExporterID = trim($_POST["ExporterID"]);
$CountryID = trim($_POST["CountryID"]);
$ConsigneeID = trim($_POST["ConsigneeID"]);
$Year = trim($_POST["Year"]);
$InvType = trim($_POST["InvType"]);
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);
$PiID = trim($_POST["PiID"]);
$poStatus = trim($_POST["poStatus"]);
$poStatusRemark = trim($_POST["poStatusRemark"]);

$bodyContent = array(
    'ExporterID' => $ExporterID,
    'CountryID' => $CountryID,
    'Year' => $Year,
    'InvType' => $InvType,
    'ConsigneeID' => $ConsigneeID,
    'ProcessorID' => $ProcessorID,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll,
    'PiID' => $PiID,
    'CustomsInvoiceStatus' => $poStatus,
    'ReasonforCancellation' => $poStatusRemark
);

// echo json_encode($bodyContent);
// exit;

if (isset($PiID) && !empty($PiID)) {
    $resCustom = CallAPI($api, "POST", "", $bodyContent);
} else {
    $resCustom = CallAPI($api, "GET", "", $bodyContent);
}


// echo json_encode($resCustom, JSON_PRETTY_PRINT);
// exit;

if (isset($resCustom) && !empty($resCustom)) {

    $error = $resCustom->Error;


    if (!isset($error) || empty($error)) {
        $PageCount = $resCustom->PageCount;
        $resList = $resCustom->Data;
        // var_dump($resList[0]->CustomPoUrl);
        // exit;
?>
        <style>
            .sauce-row {
                background-color: #fff3cd !important;
            }
        </style>
        <input type="hidden" id="hdnAPIStatus" value="<?php echo $resCustom->Msg ?>" />
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">SNO</th>
                    <th style="min-width:50px;">ID</th>
                    <th style="min-width:120px;">Invoice No</th>
                    <th style="min-width:50px;">Invoice Date</th>
                    <th style="min-width:140px;">Po No</th>
                    <th style="min-width:300px;">Buyer</th>
                    <th style="min-width:120px;">PInvoice No</th>
                    <th style="min-width:70px;">Stock Status</th>
                    <th style="min-width:200px;" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resList as $item) {
                    $class = "";
                    if ($item->IsSauce == 'Yes') {
                        $class = "sauce-row";
                    }
                ?>
                    <!-- var_dump($item->CustomPoUrl); -->
                    <tr class="<?php echo $class ?>">
                        <td>
                            <?php echo $item->SNO ?>
                        </td>
                        <td>
                            <?php echo $item->ID ?>
                        </td>
                        <td class="tdCInvNo">
                            <?php echo $item->InvoiceNO ?>
                        </td>
                        <td>
                            <?php echo $item->InvoiceDate ?>
                        </td>
                        <td>
                            <?php echo $item->PoNO ?>
                        </td>
                        <td>
                            <?php echo $item->Buyer ?>
                        </td>
                        <td>
                            <?php echo $item->PInvoiceNO ?>
                        </td>
                        <td>
                            <?php if ($InvType == "1") { ?>
                                <div class="bg-active color-palette text-center">
                                    <button class="btn btn-link badge font-size-10 btnPIStatus" style="background:<?php echo $item->Color ?>;color:white" data-toggle="popover" data-trigger="focus" data-id="<?php echo $item->ID ?>" data-popupstatus="<?php echo $item->PopUpStatus ?>">
                                        <?php echo $item->StockStatus ?>
                                    </button>
                                </div>
                            <?php } else { ?>
                                <div class="bg-active color-palette text-center">
                                    <button class="btn btn-link badge font-size-10" style="background:<?php echo $item->Color ?>;color:white" data-toggle="popover" data-trigger="focus" data-id="<?php echo $item->ID ?>" data-popupstatus="<?php echo $item->PopUpStatus ?>">
                                        <?php echo $item->StockStatus ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </td>

                        <td class="text-center">
                            <?php if ($InvType != "7") { ?>
                                <?php if ($InvType == "1" || $InvType == "4") {  ?>
                                    <a target="_blank" href="<?php echo $resCustom->DownloadExcel . $item->ID ?>" class="mr-3 text-primary btnActions" data-toggle="tooltip" data-placement="top" title="Excel" data-original-title="View">
                                        <i class="mdi mdi-download font-size-16"></i>
                                    </a>
                                <?php } ?>

                                <?php if ($resCustom->EditAccess) {
                                    if ($InvType == "1" || $InvType == "4") { // completed 
                                        echo '
                                    <a href="' . $resCustom->EditLink . $item->ID . '"
                                        class="mr-3 text-primary btnActions" data-placement="top" title="Edit " data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>';
                                    } else { // pending
                                        echo '
                                    <a  href="' . $resCustom->EditLink . $item->ID . '"
                                        class="mr-3 text-primary btnActions" data-placement="top" title="Edit " data-original-title="Edit">
                                        <i class="mdi mdi-plus font-size-16"></i>
                                    </a>';
                                    }
                                ?>
                                <?php } ?>
                                <button id="Duplicate" class="mr-3 text-primary btnActions btnDuplicate" data-placement="top" title="Duplicate" data-original-title="Edit" data-id="<?php echo $item->ID ?>" data-value=""><i class="ri-file-copy-line  font-size-16"></i></button>

                                <?php if ($InvType == "1" || $InvType == "4") {  ?>
                                    <button id="View" class="mr-3 text-primary btnActions btnViewDocRpt" data-placement="top" title="View" data-id="<?php echo $item->ID ?>" data-sauce="<?php echo $item->IsSauce ?>">
                                        <i class="dripicons-preview font-size-18"></i></button>
                                <?php } ?>
                                <!-- Durga -->

                                <?php if ($item->CustomPoUrl != '') { ?>
                                    <a target="_blank" href="<?php echo $item->CustomPoUrl . $item->ID ?>" class="mr-3 text-primary btnActions" id="printButton" data-placement="top" title="Print PO " data-original-title="Edit">
                                        <i class="mdi mdi-printer  font-size-16"></i>
                                    </a>
                                <?php  } ?>
                            <?php  } ?>
                        </td>
                    <?php } ?>
                    </tr>

            </tbody>
        </table>
        <div class="mt-3 mb-3">Showing 1 to
            <?php echo count($resList) ?> out of
            <?php echo $resCustom->TotalCount ?> entries
        </div>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resCustom->TotalCount ?>' /> -->
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
