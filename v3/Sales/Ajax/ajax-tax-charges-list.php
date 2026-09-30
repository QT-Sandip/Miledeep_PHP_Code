<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$HasPIOtherTax = $ArrayClientConfigData['HasPIOtherTax'];

$ArrayInvoiceConfigData = json_decode(json_encode($_SESSION['ClientInvoiceConfigData']), true);
$HasCLFCharges = $ArrayInvoiceConfigData['CLFCharges'];
$HasTariff = $ArrayInvoiceConfigData['Tariff'];

$ReciprocalTariffsName = 'Reciprocal Tariffs';
$api = API_TAX_CHARGES;

$bodyContent = array(
    "Exporter" => trim($_POST["exporter"]),
    "Country" => trim($_POST["country"]),
    "PageNo" => trim($_POST["pageNo"]),
    "SearchKey" => trim($_POST["searchKey"]),
    "LoadAll" => trim($_POST["loadAll"]),
    "Type" => 'List', //uma
);
$resTaxList = CallAPI($api, "GET", "", $bodyContent);
// var_dump($resVarietyList); 
// exit; 


if (isset($resTaxList) && !empty($resTaxList)) {

    $error = $resTaxList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resTaxList->PageCount;
        $resList = $resTaxList->Data;
        $resTotal = $resTaxList->TotalCount;
        // var_dump($resList[0]->ID);
?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.NO</th>
                    <th>Exporter Name</th>
                    <th>FromDate</th>
                    <th>Clearance Charge %</th>
                    <th>AntiDumpingDuty %</th>
                    <th>MerchandiseProcess %</th>
                    <th>HarbourMaintenance %</th>
                    <th>CountervailingDuty</th>
                    <th>ServiceTaxPercentage</th>
                    <th>DrawbackRateperKG</th>
                    <th>DrawbackPercentage</th>
                    <th>BLServiceTax</th>
                    <th>ISFStaticDuty</th>
                    <th>InsurancePremiumRate</th>
                    <th><?php echo $ReciprocalTariffsName ?></th>
                    <?php if ($HasCLFCharges == 1) { ?>
                        <th>Clearing & Forwarding Charges</th>
                    <?php } ?>
                    <?php if ($HasPIOtherTax == 1) { ?>
                        <th>Other Tax</th>
                    <?php } ?>
                    <?php if ($HasTariff == 1) { ?>
                        <th>Tariff</th>
                        <!-- <th>Print Tariff</th> -->
                    <?php } ?>
                    <!-- <th>UsClearance</th> -->
                    <!-- <th>IGSTRaw</th> -->
                    <!-- <th>IGSTBrainFrozen</th> -->
                    <!-- <th>IGSTCooked</th> -->
                    <!-- <th>IGSTCookedInShell</th> -->
                    <!-- <th>IGSTBlanched</th> -->
                    <!-- <th>UptoDate</th> -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resList as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->ExpName ?></td>
                        <td><?php echo date('d-m-Y', strtotime($item->FromDate)) ?></td>
                        <td><?php echo $item->ClearanceChargeFee ?></td>
                        <td><?php echo $item->AntiDumpingDuty ?></td>
                        <td><?php echo $item->MerchandiseProcessFee ?></td>
                        <td><?php echo $item->HarbourMaintenanceFee ?></td>
                        <td><?php echo $item->CountervailingDuty ?></td>
                        <td><?php echo $item->ServiceTaxPercentage ?></td>
                        <td><?php echo $item->DrawbackRateperKG ?></td>
                        <td><?php echo $item->DrawbackPercentage ?></td>
                        <td><?php echo $item->BLServiceTax ?></td>
                        <td><?php echo $item->ISFStaticDuty ?></td>
                        <td><?php echo $item->InsurancePremiumRate ?></td>
                        <td><?php echo $item->ReciprocalTariffs ?></td>
                        <?php if ($HasCLFCharges == 1) { ?>
                                <td><?php echo $item->ClearingCharges ?></td>
                        <?php } ?>
                        <?php if ($HasPIOtherTax == 1) { ?>
                            <td><?php echo $item->OtherTax ?></td>
                        <?php } ?>
                        <?php if ($HasTariff == 1) { ?>
                                <td><?php echo $item->Tariff ?></td>
                                <!-- <td><?php echo ($item->PrintTariff == 1) ? 'YES' : 'NO'; ?></td> -->
                        <?php } ?>
                        <!-- <td><?php //echo $item->UsClearance 
                                    ?></td> -->
                        <!-- <td><? php // echo $item->IGSTRaw 
                                    ?></td> -->
                        <!-- <td><?php //echo $item->IGSTBrainFrozen 
                                    ?></td> -->
                        <!-- <td><?php //echo $item->IGSTCooked 
                                    ?></td> -->
                        <!-- <td><?php //echo $item->IGSTCookedInShell 
                                    ?></td> -->
                        <!-- <td><?php //echo $item->IGSTBlanched 
                                    ?></td> -->
                        <!-- <td><? php // echo $item->UptoDate 
                                    ?></td> -->
                        <td class="text-center">
                            <?php if ($item->ExpTRID != '') { ?>
                                <a href=<?php echo $item->EditLink ?> class="mr-3 text-primary btnActions" data-placement="top"
                                    title="Edit" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($resList) ?> out of <?php echo $resTotal ?> entries</div>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTaxList->TotalCount ?>' /> -->
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