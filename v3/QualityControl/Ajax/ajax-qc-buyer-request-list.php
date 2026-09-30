<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_BUYER_REPORT;

$bodyContent = array(
    'ProductionPlant' => trim($_POST["arrivalPlant"]),
    'Year' => trim($_POST["year"]),
    'BuyerID' => $_POST['consigneeList']
);
// var_dump($bodyContent);
$resList = CallAPI($api, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->BuyerData;

        ?>
        <?php if (!isset($error) || empty($error)) { ?>
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>SNO</th>
                        <th>Po No</th>
                        <th>Product Description</th>
                        <th>Packing Style</th>
                        <th>Grades</th>
                        <th>Request Sent Date</th>
                        <th>Sample Sent Date</th>
                        <th>Report Expecting Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr style=" background-color: lightgrey;">
                            <!-- <td colspan="8" class='text-center'><b><?php echo $item->ConsigneeName; ?></b></td> -->
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style=" text-align: center"><b><?php echo $item->ConsigneeName; ?></b></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <?php
                        // if (isset($item->BuyerBreakUpData) && !empty($item->BuyerBreakUpData)) {
                        foreach ($item->BuyerBreakUpData as $ItemBreakUp) {
                            ?>
                            <tr>
                                <td><?php echo $ItemBreakUp->SNO; ?></td>
                                <td><?php echo $ItemBreakUp->PoNo; ?></td>
                                <td><?php echo $ItemBreakUp->ProductDescription; ?></td>
                                <td><?php echo $ItemBreakUp->PackingStyle; ?></td>
                                <td><?php echo $ItemBreakUp->Grade; ?></td>
                                <td><?php echo $ItemBreakUp->RequestSentDate; ?></td>
                                <td><?php echo $ItemBreakUp->SampleSendingDate; ?></td>
                                <td><?php echo $ItemBreakUp->ReportExpectingDate; ?></td>
                            </tr>
                        <?php //}
                        }
                        // else { 
                        ?>
                        <!-- No Data Available for this Consignee -->
                        <!-- <tr>
                                <td colspan="8" class="text-center">No data available for this consignee</td>
                            </tr> -->
                    <?php //}
                    

                                } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
        <?php }
    } else { ?>
        <?php
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $error . "</div>";
    }
}
?>