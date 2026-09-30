<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COMMERCIAL_INVOICE;


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
    'LoadAll' => $LoadAll
);



$resCommerical = CallAPI($api, "GET", "", $bodyContent);
//var_dump($resList);

if (isset($resCommerical) && !empty($resCommerical)) {

    $error = $resCommerical->Error;
    if (!isset($error) || empty($error)) {
        $PageCount = $resCommerical->PageCount;
        $resList = $resCommerical->Data;
        ?>


        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>SNO</th>
                        <th>ID</th>
                        <th>P.Invoice NO</th>
                        <th>Invoice NO</th>
                        <th>Invoice Date</th>
                        <th>Po NO</th>
                        <th>Buyer</th>
                        <th>Status</th>
                        <th style="min-width: 80px;">Actions</th>

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
                                <?php echo $item->PInvoiceNO ?>
                            </td>
                            <td>
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
                                <div class="bg-active color-palette text-center">
                                    <button class="btn btn-link badge font-size-10 "
                                        style="width:70px;background:<?php echo $item->Color ?>;color:white" data-toggle="popover"
                                        data-trigger="focus" data-id="1103" id="BtnPIStatus" value="1103">
                                        <?php echo $item->Status ?>
                                    </button>
                                </div>
                            </td>
                            <td>

                                <?php if ($resCommerical->EditAccess) {
                                    if ($InvType == "1") {
                                        echo
                                            ' <a  href="' . $resCommerical->EditLink . $item->ID . '" class="mr-3 text-primary" data-placement="top" title="Edit"
                                        data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>';
                                    } else {
                                        echo
                                            ' <a  href="' . $resCommerical->EditLink . $item->ID . '" class="mr-3 text-primary" data-placement="top" title="Add"
                                        data-original-title="Add">
                                        <i class="mdi mdi-plus font-size-16"></i>
                                    </a>';
                                    } ?>
                                <?php }
                                if ($InvType == "1" ) {

                                    if($resCommerical->CommercialInvoicePrintLink!=''){
                                    echo

                                        '<a target="_blank" href="' . $resCommerical->CommercialInvoicePrintLink . $item->ID . '" class="mr-3 text-primary btnActions" data-toggle="tooltip" data-placement="top"
                                title="Commercial" data-original-title="View">
                                <i class="mdi mdi-printer font-size-16"></i>
                            </a>

                                <a target="_blank" href="' . $resCommerical->PackingInvoicePrintLink . $item->ID . '" class="mr-3 text-success btnActions" data-toggle="tooltip" data-placement="top"
                                title="Packing List" data-original-title="View">
                                <i class="mdi mdi-printer font-size-16"></i>
                            </a> ';
                                }
                                echo
                             '<a target="_blank" href="' . $resCommerical->ViewReportsLink . $item->ID . '" class="mr-3 text-success btnActions" data-toggle="tooltip" data-placement="top"
                                title="Reports" data-original-title="View">
                                <i class="mdi mdi-eye font-size-16"></i>
                            </a> 
                            
                        ';
                                } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to
                <?php echo count($resList) ?> out of
                <?php echo $resCommerical->TotalCount ?> entries
            </div>
            <div>
                <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resCommerical->TotalCount ?>' /> -->
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
}
?>
