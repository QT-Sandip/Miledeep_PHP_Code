<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CUSTOM_INVOICES;

$ExporterID = trim($_POST["ExporterID"]);
$CountryName = trim($_POST["CountryID"]);
$ConsigneeID = trim($_POST["ConsigneeID"]);
$Year = trim($_POST["Year"]);
$InvType = trim($_POST["InvType"]);
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$DisplayPOListBasedOnLogin = $ClientConfigData->DisplayPOListBasedOnLogin;

$UnitID = !empty($_SESSION['UnitID']) && !empty($DisplayPOListBasedOnLogin) ? $_SESSION['UnitID'] : '';

if(isset($_POST["qc"]) && !empty($_POST["qc"])) {
    $qc = trim($_POST["qc"]);
}else{
    $qc = "F";
}

$bodyContent = array(
    'ExporterID' => $ExporterID,
    'CountryID' => $CountryName,
    'Year' => $Year,
    'InvType' => $InvType,
    'ConsigneeID' => $ConsigneeID,
    'ProcessorID' => $ProcessorID,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll,
    'PlantID' => $UnitID
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
                    <?php if($qc == "F") { // if not QC module ?>
                        <th>PInvoice No</th>
                        <th>Stock Status</th>
                    <?php } ?>
                    <?php if ($qc == "T") { // if not QC module ?>
                        <th>Country </th>
                        <th>Internal Lot No</th>
                    <?php } ?>
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
                        <?php if($qc == "F") { // if not QC module ?>
                            <td class="qc-hide">
                                <?php echo $item->PInvoiceNO ?>
                            </td>
                            <td class="qc-hide">
                                <div class="bg-active color-palette text-center">
                                    <button class="btn btn-link badge badge-warning font-size-10 "
                                        style="width:70px;background:<?php echo $item->Color ?>;color:white" data-toggle="popover"
                                        data-trigger="focus" data-id="1103" id="BtnPIStatus" value="1103">
                                        <?php echo $item->StockStatus ?>
                                    </button>
                                </div>
                            </td>
                        <?php } ?>

                        <?php if ($qc == "T") { // if QC module ?>
                            <td><?php echo $item->CountryName ?> </td>
                            <td><?php echo $item->InternalLotNo ?></td>
                        <?php } ?>

                        <td class="text-center">
                            <!-- <?php if($InvType == "1") {  ?>
                            <a target="_blank" href="<?php echo $resCustom->DownloadExcel . $item->ID ?>" class="mr-3 text-primary btnActions" data-toggle="tooltip" data-placement="top"
                                title="Excel" data-original-title="View">
                                <i class="mdi mdi-download font-size-16"></i>
                            </a>
                            <?php } ?> -->

                            <?php if ($resCustom->EditAccess) {
                                 if($InvType == "1") { // completed 
                                    echo '
                                    <a href="DocAddReports?PoID='. $item->ID.'"
                                        class="mr-3 text-primary btnActions" data-placement="top" title="Add QC Docs" data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>';
                                 } else{ // pending
                                    echo '
                                    <a  href="DocAddReports?PoID=' . $item->ID.'"
                                        class="mr-3 text-primary btnActions" data-placement="top" title="Add QC Docs" data-original-title="Edit">
                                        <i class="mdi mdi-plus font-size-16"></i>
                                    </a>';
                                 }
                            ?>
                            <?php } ?>
                            <!-- <button id="Duplicate" class="mr-3 text-primary btnActions btnDuplicate" data-placement="top" title="Duplicate"
                                data-original-title="Edit" data-id="<?php echo $item->ID ?>" data-value=""><i
                                    class="ri-file-copy-line  font-size-16"></i></button> -->

                            <!-- <button id="View" class="mr-3 text-primary btnActions btnViewDocRpt" data-placement="top" title="View" 
                                    data-id="<?php echo $item->ID ?>">
                                    <i class="dripicons-preview font-size-18"  ></i></button> -->
                                                
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