<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_FDA_STATUS;

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

$resFDA = CallAPI($api, "GET", "", $bodyContent);
//var_dump($resList);

if (isset($resFDA) && !empty($resFDA)) {

    $error = $resFDA->Error;
    if (!isset($error) || empty($error)) {
        $PageCount = $resFDA->PageCount;
        $resList = $resFDA->Data;
        ?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>SNO</th>
                        <th>ID</th>
                        <th>InvNo</th>
                        <th>PoNo</th>
                        <th>Buyer</th>
                        <th>ShippingBillNo</th>
                        <th>Status</th>
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
                                        style="width:170px;background:<?php echo $item->Color ?>;color:white" data-toggle="popover"
                                        data-trigger="focus" data-id="1103" id="BtnPIStatus" value="1103">
                                        <?php echo $item->Status ?>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <?php if ($resFDA->EditAccess) {


                                    // if ($InvType == "1") { // completed 
                                    if ($InvType != "0") { // completed 
                                        echo '
                                    <a href="' . $resFDA->EditLink . $item->ID . '"
                                        class="mr-3 text-primary btnActions" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>';
                                    } else { // pending
                                        echo '
                                    <a  href="' . $resFDA->EditLink . $item->ID . '"
                                        class="mr-3 text-primary btnActions" data-placement="top" title="Edit Booking" data-original-title="Edit">
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
                <?php echo $resFDA->TotalCount ?> entries
            </div>
            <div>
                <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resFDA->TotalCount ?>' /> -->
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
