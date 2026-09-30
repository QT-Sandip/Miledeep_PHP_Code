<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");

$Year = trim($_POST["year"]);
$Exporter = trim($_POST["exporterList"]);
$Consignee = trim($_POST["consigneeList"]);
$Country = trim($_POST["countryList"]);
$inrExchange = trim($_POST["inrExchange"]);

// $api = GetPendingContracts_API;
$api = GetPendingOrdersonHand_API;

$bodyContent = array(
    'Year' => $Year,
    'Exporter' => $Exporter,
    'Consignee' => $Consignee,
    'Country' => $Country
);

$resGetPendingContractsAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetPendingContractsAPI->Data->Data;

// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

?>
<div id="buttons"></div>
<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th>S.No</th>
            <th>INVOICE</th>
            <th>ORDER NO</th>
            <th>NAME OF THE BUYER</th>
            <th>INVOICE VALUE US$</th>
            <th>INR VALUE</th>
            <th>PAYMENT TERMS</th>
            <th>DUE DATE OF SHIPMENT</th>
            <!-- <th>PORT OF DISCHARGE</th> -->
            <th>FINAL DESTINATION</th>
            <th>REMARKS</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resList as $List) { ?>
            <tr>
                <td><?php echo $List->Sno ?></td>
                <td><?php echo $List->InvNumFull ?></td>
                <td><?php echo $List->PoNO ?></td>
                <td><?php echo $List->ConsigneeName ?></td>
                <td><?php echo $List->InvoiceValue ?></td>
                <td>
                    <?php
                    $invoiceValue = floatval(preg_replace("/[^0-9.]/", "", $List->InvoiceValue));
                    $inrExchangeVal = (float) $inrExchange;
                    $INRVALUE = round($invoiceValue * $inrExchangeVal, 2);
                    echo $INRVALUE;
                    ?>
                </td>
                <td><?php echo $List->PaymentName ?></td>
                <td><?php echo $List->ShipByDate ?></td>
                <!-- <td><?php //echo $List->PortOfDischarge ?></td> -->
                <td><?php echo $List->FinalDestinationPort ?></td>
                <td><?php echo $List->Remarks ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <?php
            $totalInvoiceValueSum = 0;
            $totalINRVALUESum = 0;

            foreach ($resList as $List) {

                $invoiceValue = floatval(preg_replace("/[^0-9.]/", "", $List->InvoiceValue));

                $inrExchangeVal = (float) $inrExchange;

                $INRVALUE = round($invoiceValue * $inrExchangeVal, 2);

                $totalInvoiceValueSum += $invoiceValue;
                $totalINRVALUESum += $INRVALUE;
            }
            ?>
            <td></td>
            <td colspan="2">Grand Total</td>
            <td></td>
            <td><?php echo $totalInvoiceValueSum ?></td>
            <td><?php echo $totalINRVALUESum ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tfoot>
</table>
<div class="mt-3 mb-3">Showing 1 to <?php echo count($resList) ?> out of <?php echo count($resList) ?> entries</div>