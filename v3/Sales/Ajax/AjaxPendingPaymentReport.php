<?php
// session_start();
// include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

// include ($cp."Includes/IncludePage.php");

include("../RestAPI.php");

$ClientID = $_SESSION['ClientID'];
$DisplayFDAfields = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;

$FromDate = trim($_POST["FromDate"]);
$ToDate = trim($_POST["ToDate"]);
$Exporter = trim($_POST["Exporter"]);
$Processor = trim($_POST["Processor"]);
$Country = trim($_POST["Country"]);
$Consignee = trim($_POST["Consignee"]);

$api = GetPendingPaymentsReportAPI;

$bodyContent = array(
   "FromDate" => $FromDate,
   "ToDate" => $ToDate,
   "Exporter" => $Exporter,
   "Processor" => $Processor,
   "Country" => $Country,
   "Consignee" => $Consignee
);

$resGetPendingInsuranceListAPI = CallAPI($api, "POST", "", $bodyContent);

$resList = $resGetPendingInsuranceListAPI->Data->PendingPaymentReportData;

?>

<table id="key-datatable" data-paging="false" class="table  wrap w-100">
   <thead class="thead-light">
      <tr>
         <th style="min-width: 60px;" class="sticky sno">S.No</th>
         <th style="min-width: 140px;" class="sticky invNo">Inv No</th>
         <th style="min-width: 120px;">PO NO</th>
         <th style="min-width: 400px;">Buyer Name</th>
         <th style="min-width: 100px;">Container No</th>
         <th style="min-width: 120px;">Port</th>
         <th style="min-width: 400px;">Payment Terms</th>
         <th style="min-width: 120px;">No. of Carton</th>
         <th style="min-width: 160px;">Invoice Value USD</th>
         <th style="min-width: 200px;">Remain INV.VALUE USD.</th>
         <th style="min-width: 120px;">On board Date</th>
         <th style="min-width: 120px;">ETA Date</th>
         <th style="min-width: 150px;">ETA Modify Date </th>
         <th style="min-width: 160px;">Gate OutFull Date</th>
         <th style="min-width: 120px;">FDA EU Release Date</th>
         <?php if ($DisplayFDAfields) { ?>
            <th style="min-width: 120px;">FDA Container Status</th>
            <th style="min-width: 200px;">Bank Details</th>
         <?php } ?>
         <th style="min-width: 140px;">Due on Date</th>


         <!-- <th  style="min-width: 60px;">S.No</th>
         <th  style="min-width: 60px;">Inv No</th>
         <th  style="min-width: 60px;">PO NO</th>
         <th  style="min-width: 60px;">Buyer</th>
         <th  style="min-width: 60px;">Container No</th>
         <th  style="min-width: 60px;">Port</th>
         <th  style="min-width: 60px;">Payment Terms</th>
         <th  style="min-width: 60px;">No. of Carton</th>
         <th  style="min-width: 60px;">Invoice Value USD</th>
         <th  style="min-width: 60px;">Remain INV.VALUE USD.</th>
         <th  style="min-width: 60px;">On board Date</th>
         <th  style="min-width: 60px;">ETA Date</th>
         <th  style="min-width: 60px;">ETA Modify Date	</th>
         <th  style="min-width: 60px;">Gate OutFull</th>
         <th  style="min-width: 60px;">FDA Cleared</th>
         <th  style="min-width: 60px;">Due on Date</th> -->

         <!-- <th  style="min-width: 120px;">Bank Due Date</th> -->
      </tr>
   </thead>
   <tbody>
      <?php foreach ($resList as $item) {        ?>

         <tr>
            <td class="sticky sno"><?php echo $item->Sno ?></td>
            <td class="sticky invNo bodyInvNo"><?php echo $item->InvoiceNo ?></td>
            <td><?php echo $item->PoNo ?></td>
            <td><?php echo $item->Buyer ?></td>
            <td><?php echo $item->ContainerNo ?></td>
            <td><?php echo $item->Port ?></td>
            <td><?php echo $item->PaymentTerms ?></td>
            <td><?php echo $item->NoofCarton ?></td>
            <td class="bodyTotInvoiceValueUSD"><?php echo number_format($item->InvoiceValueUSD, 2) ?></td>
            <td class="bodyTotRemainInvoiceValueUSD"><?php echo number_format($item->RemainingInvoiceValueUSD, 2) ?></td>
            <td><?php echo $item->OnBoardDate ?></td>
            <td><?php echo $item->ETADate ?></td>
            <td><?php echo $item->ETAModifyDate ?></td>
            <td><?php echo $item->GateOutFullDate ?></td>
            <td><?php echo $item->FDAEUReleaseCFIADate ?></td>
            <?php if ($DisplayFDAfields) { ?>
               <td><?php echo $item->FDAContainerStatus ?></td>
               <td><?php echo $item->BankDetails ?></td>
            <?php } ?>
            <td><?php echo $item->DueOn ?></td>
            <!-- <td><?php //echo $item->BankDueOn 
                     ?></td> -->
         </tr>
      <?php } ?>

   </tbody>
   <tfoot>
      <tr>
         <th style="min-width: 60px;" class="sticky sno"><b style="font-size: 15px;">Total</b></th>
         <th style="min-width: 140px;" class="sticky invNo"><b style="font-size: 15px;" class="footTotInv"><?php echo $resGetPendingInsuranceListAPI->Data->Totals->TotalInvoices ?></b></th>
         <th style="min-width: 120px;">&nbsp;</th>
         <th style="min-width: 400px;">&nbsp;</th>
         <th style="min-width: 100px;">&nbsp;</th>
         <th style="min-width: 120px;">&nbsp;</th>
         <th style="min-width: 400px;">&nbsp;</th>
         <th style="min-width: 120px;"><b style="font-size: 15px;">Total Value USD</b></th>
         <th style="min-width: 200px;"><b style="font-size: 15px;" class="footTotInvoiceValueUSD"><?php echo number_format($resGetPendingInsuranceListAPI->Data->Totals->TotalInvoiceValueUSD, 2) ?></b></th>
         <th style="min-width: 120px;"><b style="font-size: 15px;" class="footTotlRemainInvoiceValueUSD"><?php echo number_format($resGetPendingInsuranceListAPI->Data->Totals->TotalRemainingInvoiceValueUSD, 2) ?></b></th>
         <th style="min-width: 120px;">&nbsp;</th>
         <th style="min-width: 120px;">&nbsp;</th>
         <th style="min-width: 150px;">&nbsp;</th>
         <th style="min-width: 160px;">&nbsp;</th>
         <th style="min-width: 120px;">&nbsp;</th>
         <?php if ($DisplayFDAfields) { ?>
            <th style="min-width: 120px;">&nbsp;</th>
            <th style="min-width: 120px;">&nbsp;</th>
            <th style="min-width: 200px;">&nbsp;</th>
         <?php } ?>
         <th style="min-width: 140px;">&nbsp;</th>
      </tr>
   </tfoot>
</table>