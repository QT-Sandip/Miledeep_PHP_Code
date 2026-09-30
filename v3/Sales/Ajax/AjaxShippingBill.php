<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_SHIPPING_BILL;

$ExporterID = trim($_POST["ExporterID"]);
$CountryID = trim($_POST["CountryID"]);
$ConsigneeID = trim($_POST["ConsigneeID"]);
$Year = trim($_POST["Year"]);
$ProcessorID = trim($_POST["ProcessorID"]);
$BillsType = trim($_POST["BillsType"]);
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'ExporterID' => $ExporterID,
    'CountryID' => $CountryID,
    'Year' => $Year,
    'BillsType' => $BillsType,
    'ProcessorID' => $ProcessorID,
    'ConsigneeID' => $ConsigneeID,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resShipping = CallAPI($api, "GET", "", $bodyContent);

if (isset($resShipping) && !empty($resShipping)) {

    $error = $resShipping->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resShipping->PageCount;
        $resList = $resShipping->Data;
        ?>



        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th>ID</th>
                    <th>Inv No</th>
                    <th>PoNo</th>
                    <th>Consignee Name</th>
                    <th>Shipping Bill No</th>
                    <th>Invoice Status</th>
                    <th>Actions</th>
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
                        <td>
                            <?php echo $item->InvNo ?>
                        </td>
                        <td>
                            <?php echo $item->PoNo ?>
                        </td>
                        <td>
                            <?php echo $item->Buyer ?>
                        </td>
                        <td>
                            <?php echo $item->ShippingBillNo ?>
                        </td>

                        <td>
                            <div class="bg-active color-palette text-center">
                                <button class="btn btn-link badge font-size-10 "
                                    style="width:70px;background:<?php echo $item->Color ?>;color:white" data-toggle="popover"
                                    data-trigger="focus" data-id="" id="BtnPIStatus" value="">
                                    <?php echo $item->InvoiceStatus ?>
                                </button>
                            </div>
                        </td>
                        <td>
                            <?php if ($resShipping->EditAccess) {
                                if ($BillsType == "1") {
                                    echo
                                        '<a  href="' . $resShipping->EditLink . $item->ID . '" class="mr-3 text-primary" data-placement="top" title="Edit" data-original-title="Edit">
                                <i class="mdi mdi-pencil font-size-16"></i>
                            </a>';
                                } else {
                                    echo
                                        '<a  href="' . $resShipping->EditLink . $item->ID . '" class="mr-3 text-primary" data-placement="top" title="Add" data-original-title="Add">
                                <i class="mdi mdi-plus font-size-16"></i>
                            </a>';
                                }

                                ?>

                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="mt-3 mb-3">Showing 1 to
            <?php echo count($resList) ?> out of
            <?php echo $resShipping->TotalCount ?> entries
        </div>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resShipping->TotalCount ?>' /> -->
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
