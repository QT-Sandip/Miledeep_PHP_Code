<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_SYSCO_PSIP_STATUS_REPORT;

$bodyContent = array(
    "PlantID" => trim($_POST["plant"]),
    "Year" => trim($_POST["year"]),
    "BuyerID" => trim($_POST["consigneeList"]),

);
// var_dump($bodyContent);
$resList = CallAPI($api, "GET", "", $bodyContent);

//var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
//  exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->SiscoData;
        ?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px">S.No</th>
                        <th style="min-width: 120px;width: 120px;">PoNo</th>
                        <th style="min-width: 200px;width: 200px;">Vendor</th>
                        <th style="min-width: 120px;width: 120px;">Invoice No</th>
                        <th style="min-width: 120px;width: 120px;">Country</th>
                        <th style="min-width: 120px;width: 120px;">Shipment Date</th>
                        <th style="min-width: 120px;width: 120px;">Status</th>
                        <th style="width: 60px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td style="width: 40px"><?php echo $item->SNO ?></td>
                            <td style="min-width: 120px;width: 120px;"><?php echo $item->PoNo ?></td>
                            <td style="min-width: 200px;width: 200px;"><?php echo $item->ConsigneeName ?></td>
                            <td style="min-width: 200px;width: 200px;"><?php echo $item->InvoiceNumber ?></td>
                            <td style="min-width: 200px;width: 200px;"><?php echo $item->DestinationCountry ?></td>
                            <td style="min-width: 200px;width: 200px;"><?php echo $item->ShipmentDate ?></td>
                            <td style="min-width: 120px;width: 120px;">
                                <div class="bg-active color-palette text-center">
                                    <span class="btn btn-link badge font-size-10 btnPIStatus"
                                        style="color: <?php echo $item->TextColor ?>; background-color: <?php echo $item->DisplayColor ?>;"
                                        data-toggle="popover" data-trigger="focus">
                                        <?php echo $item->DisplayMessage; ?>
                                    </span>
                                </div>
                            </td>

                            <td style="width: 60px">
                                <a href="DocAddReports?PoID=<?php echo $item->PoID ?>" class="mr-3 text-primary btnActions"
                                    data-placement="top" title="Edit " data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
        <?php } ?>
    <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $error . "</div>";
    }
}
?>
