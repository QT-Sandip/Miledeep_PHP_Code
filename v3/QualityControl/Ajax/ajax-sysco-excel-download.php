<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_SYSCO_PSIP_REPORT_PDF;

$bodyContent = array(
    "PlantID" => trim($_POST["plant"]),
    "BuyerID" => trim($_POST["consigneeList"]),
    "Year" => trim($_POST["year"]),

);

$resList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->SiscoData;
?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
            <!-- <table id="tblDataTable" border="1" class="table table-hover table-bordered"> -->
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px">S.No</th>
                        <th style="min-width: 120px;width: 120px;">PoNo</th>
                        <th style="min-width: 200px;width: 200px;">Vendor</th>
                        <!--  -->
                        <th style="min-width: 120px;width: 120px;">PSIP ID</th>
                        <th style="min-width: 180px;width: 180px;">Product</th>
                        <th style="min-width: 120px;width: 120px;">PSIP Sent Date</th>
                        <th style="min-width: 120px;width: 120px;">Shipment Date</th>
                        <th style="min-width: 120px;width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td colspan="8" style=" text-align: center"><b><?php echo $item->ConsigneeName ?></b></td>
                        </tr>
                        <?php foreach ($item->PoData as $ItemBreakUp) { ?>
                            <tr>
                                <td><?php echo $ItemBreakUp->SNO ?></td>
                                <td><?php echo $ItemBreakUp->PoNo ?></td>
                                <td><?php echo $ItemBreakUp->ShortName ?></td>
                                <td><?php echo $ItemBreakUp->PsiID ?></td>
                                <td><?php echo $ItemBreakUp->Product ?></td>
                                <td><?php echo $ItemBreakUp->PsipSentDate ?></td>
                                <td><?php echo $ItemBreakUp->ShipmentDate ?></td>
                                <td><?php echo $ItemBreakUp->DisplayMessage ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
} ?>