<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_QCERTIFICATE_REPORT;

$FromDate = trim($_POST["FromDate"]);
$ToDate = trim($_POST["ToDate"]);
$Exporter = trim($_POST["Exporter"]);
$Processor = trim($_POST["Processor"]);

$bodyContent = array(
    'FromDate' => $FromDate,
    'ToDate' => $ToDate,
    'Exporter' => $Exporter,
    'Processor' => $Processor
);

$resList = CallAPI($api, "POST", "", $bodyContent);

$error = $resList->Error;
$List = $resList->Data->QCertificateReportData;
$ListTotal = $resList->Data->Totals;

?>

<?php if (!isset($error) || empty($error)) { ?>
<div class="card">
    <div class="card-body">
                                            
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No.</th>
                    <th>Invoice No.</th>
                    <th>Q Certificate No</th>
                    <th>Quantity Net weight LBs</th>
                    <th>Quantity Net weight KGs</th>
                    <th>FOB Value in USD</th>
                    <th>FOB Value in INR</th>	
                    <th>Port of Discharge</th>	
                    <th>Final Destination</th>	
                    <th>Port of Loading</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($List as $item) { ?>
                <tr>
                    <td><?php echo $item->SNO ?></td>
                    <td class="bodyInvNo"><?php echo $item->InvoiceNO ?></td>
                    <td><?php echo $item->QCertificateNo ?></td>
                    <td class="bodyNetWeightLbs"><?php echo $item->QuantityinLbs ?></td>
                    <td class="bodyNetWeightKgs"><?php echo $item->Quantityinkgs ?></td>
                    <td class="bodyFOBValueUSD"><?php echo $item->FobValueUSD ?></td>
                    <td class="bodyFobValueINR"><?php echo $item->FobValueINR ?></td>
                    <td><?php echo $item->PortOfDischarge ?></td>
                    <td><?php echo $item->FinalDestination ?></td>
                    <td><?php echo $item->PortofLoading ?></td>
                </tr>
        <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th><b style="font-size: 15px;">Total</b></th>	
                <th><b style="font-size: 15px;" class="footInvNo"><?php echo $ListTotal->TotalInvoices ?></b></th>		        
                <th>&nbsp;</th>
                <th><b style="font-size: 15px;" class="footTotNetWeightLbs"><?php  echo $ListTotal->TotalNetWeightLbs ?></b></th>
                <th><b style="font-size: 15px;" class="footTotNetWeightKgs"><?php  echo $ListTotal->TotalNetWeightKgs ?></b></th>
                <th><b style="font-size: 15px;" class="footTotFOBValueUSD"><?php  echo $ListTotal->TotalFOBValueUSD ?></b></th>
                <th><b style="font-size: 15px;" class="footTotFobValueINR"><?php  echo $ListTotal->TotalFobValueINR ?></b></th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
        </tfoot>
        </table>

        </div>
    </div>

<?php } ?>
