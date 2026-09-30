<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// echo $_SESSION["Token"];

$api = API_QC_ORDERS_LIST;

$ExporterID = trim($_POST["ExporterID"]);
$CountryID = trim($_POST["CountryID"]);
$ConsigneeID = trim($_POST["ConsigneeID"]);
$Year = trim($_POST["Year"]);
$InvType = trim($_POST["InvType"]);
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);



$bodyContent = array(
    'ExporterID' => $ExporterID,
    'CountryID' => $CountryID,
    'Year' => $Year,
    'InvType' => $InvType,
    'ConsigneeID' => $ConsigneeID,
    'ProcessorID' => "",
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resCustom = CallAPI($api, "GET", "", $bodyContent);

if (isset($resCustom) && !empty($resCustom)) {

    $error = $resCustom->Error;


    if (!isset($error) || empty($error)) {
        $PageCount = $resCustom->PageCount;
        $resList = $resCustom->Data;

        ?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>SNO</th>
                    <th>ID</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Po No</th>
                    <th>Buyer</th>

                    <!-- <th>PInvoice No</th>
            <th>Stock Status</th> -->

                   
                    <th>Internal Lot No</th>

                    <th style="min-width:100px;" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resList as $item) { ?>
                    <tr>
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

                        <!-- <td class="qc-hide">
                <?php // echo $item->PInvoiceNO ?>
            </td>
            <td class="qc-hide">
                <div class="bg-active color-palette text-center">
                    <button class="btn btn-link badge badge-warning font-size-10 "
                        style="width:70px;background:<?php // echo $item->Color ?>;color:white" data-toggle="popover"
                        data-trigger="focus" data-id="1103" id="BtnPIStatus" value="1103">
                        <?php // echo $item->StockStatus ?>
                    </button>
                </div>
            </td> -->


                      
                        <td><?php echo $item->InternalLotNo ?></td>

                        <td class="text-center">
                            <?php // if ($InvType == "1") { ?>
                            <!-- <a target="_blank" href="<?php // echo $resCustom->DownloadExcel . $item->ID ?>"
                                    class="mr-3 text-primary btnActions" data-toggle="tooltip" data-placement="top" title="Excel"
                                    data-original-title="View">
                                    <i class="mdi mdi-download font-size-16"></i>
                                </a> -->
                            <?php // } ?>

                            <?php if ($resCustom->EditAccess) {
                                if ($InvType == "1") { // completed 
                                    echo '
                            <a href="' . $resCustom->EditLink . $item->ID . '"
                                class="mr-3 text-primary btnActions" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                <i class="mdi mdi-pencil font-size-16"></i>
                            </a>';
                                } else { // pending
                                    echo '
                            <a  href="' . $resCustom->EditLink . $item->ID . '"
                                class="mr-3 text-primary btnActions" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                <i class="mdi mdi-plus font-size-16"></i>
                            </a>';
                                }

                                echo '
                                <a  href="../ColdStore/CreateShipmentStatus.php?PoID=' .  $item->ID . '"
                                    class="mr-3 text-primary btnActions" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                    <i class="mdi mdi-eye font-size-16"></i>
                                </a>';
                                ?>
                            <?php } ?>

                            <!-- <button id="Duplicate" class="mr-3 text-primary btnActions btnDuplicate" data-placement="top"
                    title="Duplicate" data-original-title="Edit" data-id="<?php // echo $item->ID ?>" data-value=""><i
                        class="ri-file-copy-line  font-size-16"></i></button> -->

                            <!-- <button id="View" class="mr-3 text-primary btnActions btnViewDocRpt" data-placement="top" title="View"
                                data-id="<?php // echo $item->ID ?>">
                                <i class="dripicons-preview font-size-18"></i></button> -->

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