<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_PAYMENT_SUPPLIER_WISE_REPORT;

$Plant = trim($_POST["Plant"]);
$Center = trim($_POST["Center"]);
$Supplier = trim($_POST["Supplier"]);
$StartDate = trim($_POST["startDate"]);
$Type = trim($_POST["Type"]);
$Agent = trim($_POST["Agent"]);
$ReportType = trim($_POST["RptType"]);

$bodyContent = array(
    'Plant' => $Plant,
    'CenterID' => $Center,
    'SupplierID' => $Supplier,
    'StartDate' => $StartDate,
    'Type' => $Type,
    'AgentID' => $Agent,
    'ReportType' => $ReportType,
);

$resList = CallAPI($api, "GET", "", $bodyContent);
$Totals = $resList->Data->Totals;

$error = $resList->Error;
$List = $resList->Data->PaymentCenterwiseReportData;

?>


<?php if (!isset($error) || empty($error)) { ?>

    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th style="min-width: 50px;">Sl.No</th>
                <th style="min-width: 50px;">Center Name</th>
                <th style="min-width: 160px;">Supplier</th>
                <th style="min-width: 130px;">Pan Number</th>
                <th style="min-width: 130px;">GST</th>
                <?php if ($ReportType != 'M') { ?>
                    <th style="min-width: 130px;">Bill Date</th>
                    <th style="min-width: 160px;">Bill Number</th>
                <?php } ?>
                <th style="min-width: 130px;">Bill Amount</th>
                <th style="min-width: 130px;">Paid Amount</th>
                <th style="min-width: 130px;">OS Amount</th>
                <?php if ($ReportType != 'M') { ?>
                <th style="min-width: 130px;">Sub Total</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($List as $item) { ?>
                <?php if ($item->SNO != "") { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->Center ?></td>
                        <td> <?php echo $item->Supplier ?></td>
                        <td><?php echo $item->PanNumber ?></td>
                        <td><?php echo $item->GstNo ?> </td>
                       
                        <?php if ($ReportType != 'M') { ?>
                         <td><?php echo $item->BillDate ?></td>
                        <td><?php echo $item->BillNo ?></td>
                        <?php } ?>
                        <td><?php echo $item->BillAmount ?></td>
                        <td><?php echo $item->PaidAmount ?></td>
                        <td><?php echo $item->OSAmount ?></td>
                        <?php if ($ReportType != 'M') { ?>
                          <td>&nbsp;</td>
                        <?php } ?>

                    </tr>
                <?php } else { ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td><b style="color: #6eaeff"><?php echo $item->Supplier ?></b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        
                        <?php if ($ReportType != 'M') { ?>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        <?php } ?>

                        <td>&nbsp;</td>
                        <?php if ($ReportType != 'M') { ?>
                            <td><b style="color: #6eaeff">Sub Total<b></td>
                        <?php }else{ ?>
                            <td><b style="color: #6eaeff">Sub Total<b></td>
                            <?php } ?>
                        <td><b style="color: #6eaeff">&nbsp;<b></td>
                        <?php if ($ReportType != 'M') { ?>
                            <td><b style="color: #6eaeff"><?php echo $item->SubTotal ?><b></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td>Totals</td>
                <td></td>
                <td></td>
                <td></td>
               
                <?php if ($ReportType != 'M') { ?>
                    <td></td>
                    <td></td>
                <?php } ?>
                <td>
                    <?php echo $Totals->TotalBillAmount ?>
                </td>
                <td>
                    <?php echo $Totals->TotalPaidAmount ?>
                </td>
                <td>
                    <?php echo $Totals->TotalOSAmount ?>
                </td>
                
                <?php if ($ReportType != 'M') { ?>
                    <td></td>
                <?php } ?>


            </tr>
        </tfoot>
    </table>

<?php } else {
    echo "Note : " . $error;
} ?>