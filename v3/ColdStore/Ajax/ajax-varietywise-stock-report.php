<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_VARIETYWISE_STOCK_REPORT;

$apiSummary = API_STOCK_SUMMARY_REPORT;

$bodyContent = array(
    "ProductionDate" => $_POST['productionDate'],
    "ColdstoreID" => $_POST['coldStores'],
    "TypeProduct" => $_POST['typeProduct'],
    "Type" => $_POST['type'],
    "FreezingType" => $_POST['freezingType'],
    "Variety" => $_POST['typeVariety'],
    "Treatment" => $_POST['typeSoaking'],
    // "PackingStyle" => $_POST['pakingStyle'],
    "Grade" => $_POST['typeGrade'],
    "Species" => $_POST['typeSpecies']
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
$List = $resList->Data;
// echo json_encode($List, JSON_PRETTY_PRINT);
// foreach ($List as $Item)   {
//     // echo $Item->FreezingData;
//     echo json_encode($Item->FreezingData, JSON_PRETTY_PRINT);
//     // foreach ($Item->FreezingData as $ItemFreezingData) {
//     //     echo $ItemFreezingData->FreezingWeight;
//     // }
// }
// exit;

$resSummary = CallAPI($apiSummary, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
        $GrandTotals = $resList->GrandTotals;
        // ShipmentData
        $ShipmentData = $resSummary->ShipmentDetails;
        // Melting Data
        $MeltingData = $resSummary->MeltingDetails;
        $MeltingOutData = $MeltingData->MeltingOut;
        $MeltingInData = $MeltingData->MeltingIn;
        // Repacking Data
        $RepackingData = $resSummary->RepackingDetails;
        $RepackingOutData = $RepackingData->RepackingOut;
        $RepackingInData = $RepackingData->RepackingIn;
        // Physical Less
        $PhysicalLessData = $resSummary->PhysicalLessDetails;
        $PhysicalExcessData = $resSummary->PhysicalExcessDetails;
        // IBT Details
        $IBTData = $resSummary->IBTDetails;
        $IBTOutData = $IBTData->IBTOutDetails;
        $IBTInData = $IBTData->IBTInDetails;


        if (!empty($List) ) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $Item) { ?>
                        <tr>
                            <td>
                                <table class="subTable table table-hover table-bordered">
                                    <h4 class="stickCol"><?php echo $Item->TypeProduct ?></h4>
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="stickCol" style="width: 40px;"><b>Grade</b></th>
                                            <?php if (!empty($Item->VarietyCombinationData)) { ?>
                                                <?php foreach ($Item->VarietyCombinationData as $ItemBreakUp) { ?>
                                                    <th style="min-width: 80px;"><?php echo $ItemBreakUp->Species . '<br>' . $ItemBreakUp->ShortName . '<br>' . $ItemBreakUp->SockingType . '<br>' . $ItemBreakUp->Glaze .  '<br> ' . $ItemBreakUp->PackingStyle ?></th>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <th>No Variety Combination Data Available</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($Item->GradesData)) { ?>
                                            <?php foreach ($Item->GradesData as $itemGrades) { ?>
                                                <tr>
                                                    <td class="stickCol"><b><?php echo $itemGrades->Grade ?></b></td>
                                                    <?php foreach ($itemGrades->GradeValueData as $gradeValues) { ?>
                                                        <td><?php echo $gradeValues->Value ?></td>
                                                    <?php } ?>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="<?php echo !empty($Item->VarietyCombinationData) ? count($Item->VarietyCombinationData) + 1 : 2; ?>">No Grades Data Available</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="stickCol" style="font-weight: bold;">Totals</td>
                                            <?php foreach ($Item->GrandTotals as $ItemGrandTotals) { ?>
                                                <td><?php echo $ItemGrandTotals->TotalValue ?></td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <td class="stickCol" style="font-weight: bold;">Production</td>
                                            <?php foreach ($Item->FreezingData as $ItemFreezingData) { ?>
                                                <td><?php echo $ItemFreezingData->FreezingWeight ?></td>
                                            <?php } ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <br>
                    <tr>
                        <td>
                            <table class="table table-hover table-bordered">
                                <tbody>
                                    <?php foreach ($GrandTotals  as $index => $ItemGrandTotals) { ?>
                                        <tr style="font-weight: bold;">
                                            <td style="width: 50px;">Total <br><?php echo $index ?><br> Production</td>
                                            <td style="width: 80px;"><?php echo $ItemGrandTotals->Value ?></td>
                                            <td></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <?php if (!empty($ShipmentData)) { ?>
                        <br>
                        <tr>
                            <td>
                                <table class="table table-hover table-bordered">
                                    <h4>Shipment Details</h4>
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">SNO</th>
                                            <th style="width: 120px;">PoNo</th>
                                            <th style="width: 120px;">Invoice Number</th>
                                            <th style="width: 50px;">Ordered Mcs</th>
                                            <th style="width: 100px;">Product</th>
                                            <th style="width: 100px;">Packing Style</th>
                                            <th style="width: 80px;">Grade</th>
                                            <th style="width: 80px;">Mcs</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ShipmentData as $ItemShipment) { ?>
                                            <tr>
                                                <td style="width: 50px;"><?php echo $ItemShipment->SNO ?></td>
                                                <td style="width: 120px;"><?php echo $ItemShipment->PoNo ?></td>
                                                <td style="width: 120px;"><?php echo $ItemShipment->CustomsInvNo ?></td>
                                                <td style="width: 50px;"><?php echo $ItemShipment->OrderedMcs ?></td>
                                                <td style="width: 100px;"><?php echo $ItemShipment->ProductDescription ?></td>
                                                <td style="width: 100px;"><?php echo $ItemShipment->PackingStyle ?></td>
                                                <td style="width: 80px;"><?php echo $ItemShipment->Grade ?></td>
                                                <td style="width: 80px;"><?php echo $ItemShipment->OutMcs ?></td>
                                                <td></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($MeltingOutData)) { ?>
                        <br>
                        <tr>
                            <td>
                                <h4>Melting Out Details</h4>
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <td style="width: 50px;">SNO</td>
                                            <td style="width: 100px;">Product</td>
                                            <td style="width: 100px;">Packing Style</td>
                                            <td style="width: 80px;">Grade</td>
                                            <td style="width: 80px;">Mcs</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($MeltingOutData as $meltingOutItem) { ?>
                                            <tr>
                                                <td style="width: 50px;"><?php echo $meltingOutItem->SNO ?></td>
                                                <td style="width: 100px;"><?php echo $meltingOutItem->ProductDescription ?></td>
                                                <td style="width: 100px;"><?php echo $meltingOutItem->PackingStyle ?></td>
                                                <td style="width: 80px;"><?php echo $meltingOutItem->Grade ?></td>
                                                <td style="width: 80px;"><?php echo $meltingOutItem->OutMcs ?></td>
                                                <td></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($MeltingInData)) { ?>
                        <br>
                        <tr>
                            <td>
                                <h4>Melting In Details</h4>
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <td style="width: 50px;">SNO</td>
                                            <td style="width: 100px;">Product</td>
                                            <td style="width: 100px;">Packing Style</td>
                                            <td style="width: 80px;">Grade</td>
                                            <td style="width: 80px;">Mcs</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($MeltingInData as $meltingInItem) {
                                        ?>
                                            <tr>
                                                <td style="width: 50px;"><?php echo $meltingInItem->SNO ?></td>
                                                <td style="width: 100px;"><?php echo $meltingInItem->ProductDescription ?></td>
                                                <td style="width: 100px;"><?php echo $meltingInItem->PackingStyle ?></td>
                                                <td style="width: 80px;"><?php echo $meltingInItem->Grade ?></td>
                                                <td style="width: 80px;"><?php echo $meltingInItem->ActualMcs ?></td>
                                                <td></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($RepackingOutData)) { ?>
                        <br>
                        <tr>
                            <td>
                                <h4>Repacking Out Details</h4>
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <td style="width: 50px;">SNO</td>
                                            <td style="width: 100px;">Product</td>
                                            <td style="width: 100px;">Packing Style</td>
                                            <td style="width: 80px;">Grade</td>
                                            <td style="width: 80px;">Mcs</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($RepackingOutData as $repackingOutItem) {
                                        ?>
                                            <tr>
                                                <td style="width: 50px;"><?php echo $repackingOutItem->SNO ?></td>
                                                <td style="width: 100px;"><?php echo $repackingOutItem->ProductDescription ?></td>
                                                <td style="width: 100px;"><?php echo $repackingOutItem->PackingStyle ?></td>
                                                <td style="width: 80px;"><?php echo $repackingOutItem->Grade ?></td>
                                                <td style="width: 80px;"><?php echo $repackingOutItem->OutMcs ?></td>
                                                <td></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($RepackingInData)) { ?>
                        <br>
                        <tr>
                            <td>
                                <h4>Repacking In Details</h4>
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <td style="width: 50px;">SNO</td>
                                            <td style="width: 100px;">Product</td>
                                            <td style="width: 100px;">Packing Style</td>
                                            <td style="width: 80px;">Grade</td>
                                            <td style="width: 80px;">Mcs</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($RepackingInData as $repackingInItem) {
                                        ?>
                                            <tr>
                                                <td style="width: 50px;"><?php echo $repackingInItem->SNO ?></td>
                                                <td style="width: 100px;"><?php echo $repackingInItem->ProductDescription ?></td>
                                                <td style="width: 100px;"><?php echo $repackingInItem->PackingStyle ?></td>
                                                <td style="width: 80px;"><?php echo $repackingInItem->Grade ?></td>
                                                <td style="width: 80px;"><?php echo $repackingInItem->ActualMcs ?></td>
                                                <td></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($PhysicalExcessData)) { ?>
                        <br>
                        <tr>
                            <td>
                                <h4>Physical Excess</h4>
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <td style="width: 50px;">SNO</td>
                                            <td style="width: 100px;">Product</td>
                                            <td style="width: 100px;">Packing Style</td>
                                            <td style="width: 80px;">Grade</td>
                                            <td style="width: 80px;">Mcs</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($PhysicalExcessData as $physicalExcessItem) {
                                        ?>
                                            <tr>
                                                <td style="width: 50px;"><?php echo $physicalExcessItem->SNO ?></td>
                                                <td style="width: 100px;"><?php echo $physicalExcessItem->ProductDescription ?></td>
                                                <td style="width: 100px;"><?php echo $physicalExcessItem->PackingStyle ?></td>
                                                <td style="width: 80px;"><?php echo $physicalExcessItem->Grade ?></td>
                                                <td style="width: 80px;"><?php echo $physicalExcessItem->AvalMcs ?></td>
                                                <td></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($PhysicalLessData) && ($PhysicalLessData != null)) { ?>
                        <br>
                        <tr>
                            <td>
                                <!-- <h4>Physical Less</h4> -->
                                <?php foreach ($PhysicalLessData as $ItemPhysicalLess) { ?>
                                    <?php if (!empty($ItemPhysicalLess->PhysicalLessData)) { ?>
                                        <table class="table table-hover table-bordered">
                                            <h5><?php echo $ItemPhysicalLess->TransCategory ?></h5>
                                            <thead class="thead-light">
                                                <tr>
                                                    <td style="width: 50px;">SNO</td>
                                                    <td style="width: 100px;">Product</td>
                                                    <td style="width: 100px;">Packing Style</td>
                                                    <td style="width: 80px;">Grade</td>
                                                    <td style="width: 80px;">Mcs</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ItemPhysicalLess->PhysicalLessData as $ItemBreakUp) { ?>
                                                    <tr>
                                                        <td style="width: 50px;"><?php echo $ItemBreakUp->SNO ?></td>
                                                        <td style="width: 100px;"><?php echo $ItemBreakUp->ProductDescription ?></td>
                                                        <td style="width: 100px;"><?php echo $ItemBreakUp->PackingStyle ?></td>
                                                        <td style="width: 80px;"><?php echo $ItemBreakUp->Grade ?></td>
                                                        <td style="width: 80px;"><?php echo $ItemBreakUp->OutMcs ?></td>
                                                        <td></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($IBTOutData)) { ?>
                        <br>
                        <tr>
                            <td>
                                <h4>IBT Out Details</h4>
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <td style="width: 50px;">SNO</td>
                                            <td style="width: 100px;">To Store</td>
                                            <td style="width: 100px;">Product</td>
                                            <td style="width: 100px;">Packing Style</td>
                                            <td style="width: 80px;">Grade</td>
                                            <td style="width: 80px;">Mcs</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($IBTOutData as $ibtItem) {
                                        ?>
                                            <tr>
                                                <td style="width: 50px;"><?php echo $ibtItem->SNO ?></td>
                                                <td style="width: 100px;"><?php echo $ibtItem->ToStoreName ?></td>
                                                <td style="width: 100px;"><?php echo $ibtItem->ProductDescription ?></td>
                                                <td style="width: 100px;"><?php echo $ibtItem->PackingStyle ?></td>
                                                <td style="width: 80px;"><?php echo $ibtItem->Grade ?></td>
                                                <td style="width: 80px;"><?php echo $ibtItem->AvalMcs ?></td>
                                                <td></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($IBTInData)) { ?>
                        <br>
                        <tr>
                            <td>
                                <h4>IBT Out Details</h4>
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <td style="width: 50px;">SNO</td>
                                            <td style="width: 100px;">From Store</td>
                                            <td style="width: 100px;">Product</td>
                                            <td style="width: 100px;">Packing Style</td>
                                            <td style="width: 80px;">Grade</td>
                                            <td style="width: 80px;">Mcs</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($IBTInData as $ibtItem) {
                                        ?>
                                            <tr>
                                                <td style="width: 50px;"><?php echo $ibtItem->SNO ?></td>
                                                <td style="width: 100px;"><?php echo $ibtItem->FromStoreName ?></td>
                                                <td style="width: 100px;"><?php echo $ibtItem->ProductDescription ?></td>
                                                <td style="width: 100px;"><?php echo $ibtItem->PackingStyle ?></td>
                                                <td style="width: 80px;"><?php echo $ibtItem->Grade ?></td>
                                                <td style="width: 80px;"><?php echo $ibtItem->AvalMcs ?></td>
                                                <td></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else {
            echo "<div class='text-center mt-3 mb-3 text-bold'>No Data Available</div>";
        } ?>
        <!-- <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
            <ul class="pagination pagination-rounded justify-content-center"></ul>
        </div> -->

<?php } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>