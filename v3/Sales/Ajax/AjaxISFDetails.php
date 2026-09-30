<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_ISF_DETAILS;


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
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll,
);

$resISF = CallAPI($api, "GET", "", $bodyContent);

if (isset($resISF) && !empty($resISF)) {

    $error = $resISF->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resISF->PageCount;
        $resList = $resISF->Data;
        ?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th>ID</th>
                    <th>PInvoice No</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Po No</th>
                    <th>
                        <center>Buyer</center>
                    </th>
                    <th>Status</th>
                    <th style="width:100px">Actions</th>
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
                            <?php echo $item->PInvoiceNo ?>
                        </td>
                        <td>
                            <?php echo $item->InvoiceNo ?>
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
                            <div class="bg-active color-palette text-center">
                                <button class="btn btn-link badge font-size-10 "
                                    style="background:<?php echo $item->Color ?>;color:white" data-toggle="popover"
                                    data-trigger="focus" data-id="1103" id="BtnPIStatus" value="1103">
                                    <?php echo $item->Status ?>
                                </button>
                            </div>
                        </td>
                        <td style="display:flex">
                            <?php if ($resISF->EditAccess) { 
                                if($InvType == "1") {  //completed
                                echo'
                                    <a  href="'.$resISF->EditLink . $item->ID.'" class="mr-3 text-primary"
                                    data-placement="top" title="Edit " data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>';
                               } 

                                else{
                                    echo'
                                    <a href="'.$resISF->EditLink . $item->ID.'" class="mr-3 text-primary"
                                    data-placement="top" title="Add " data-original-title="Add">
                                    <i class="mdi mdi-plus font-size-16"></i>
                                </a>';
                                } ?>
                            <?php }  if($InvType == "1") { //completed ?>

                            <a target="_blank" href="<?php echo $resISF->TenplusTwoPrintLink . $item->ID ?>"
                                class="mr-3 text-primary btnActions" data-placement="top" title="TenplustwoPrint"
                                data-original-title="Print">
                                <i class="mdi mdi-printer  font-size-16"></i>
                            </a>
                            <a target="_blank" href="<?php echo $resISF->ShipmentDetailsPrintLink . $item->ID ?>"
                                class="mr-3 text-primary btnActions" data-placement="top" title="ShipmentDetailsPrint "
                                data-original-title="Print">
                                <i class="mdi mdi-printer  font-size-16 " style="color:green"></i>
                            </a>
                            <button id="view" class="mr-3 text-primary btnActions btnViewDocRpt" data-placement="top" title="view"
                                data-original-title="Edit" data-id="<?php echo $item->ID ?>" data-value=""><i class="dripicons-preview font-size-16"
                                    style="color:green"></i></button>
                         <?php   } ?>
                        </td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="mt-3 mb-3">Showing 1 to
            <?php echo count($resList) ?> out of
            <?php echo $resISF->TotalCount ?> entries
        </div>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resISF->TotalCount ?>' /> -->
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
