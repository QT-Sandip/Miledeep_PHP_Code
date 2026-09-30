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

$api = GetFDAReleaseInformationAPI;

$bodyContent = array(
   "FromDate" => $FromDate,
   "ToDate" => $ToDate,
   "Exporter" => $Exporter,
   "Processor" => $Processor,
   "Country" => $Country,
   "Consignee" => $Consignee
);

$resGetFDAReleaseInformationAPI = CallAPI($api, "POST", "", $bodyContent);
$resList = $resGetFDAReleaseInformationAPI->Data->FdaReleaseInfoData;
//echo '<pre>';print_r($resList);die;
?>

<table id="key-datatable" data-paging="false" class="table  wrap w-100">
   <thead class="thead-light">
      <tr>
         <th style="min-width: 60px;" class="sticky sno">S.No</th>
         <th style="min-width: 120px;" class="sticky invNo">Inv No</th>
         <th style="min-width: 100px;">Date</th>
         <th style="min-width: 100px;">Buyer</th>
         <th style="min-width: 120px;">Value In US $</th>
         <th style="min-width: 100px;">FDA Entry No</th>
         <th style="min-width: 100px;">Release Date</th>
         <th style="min-width: 100px;">ETA</th>
         <th style="min-width: 100px;">Overdue Due Days</th>
         <th style="min-width: 100px;">Unit Code No</th>
      </tr>
   </thead>
   <tbody>
      <?php
      $i = 1;
      foreach ($resList as $item) {
      ?>
         <tr>
            <td class="sticky sno"><?php echo $i++ ?></td>
            <td class="sticky invNo bodyInvNo"><?php echo $item->InvoiceNo ?></td>
            <td><?php echo $item->CInvDate ?></td>
            <td><?php echo $item->ConsigneeName ?></td>
            <td><?php echo number_format($item->InvoiceValueUSD, 2) ?></td>
            <td><?php echo $item->FDAEntryNumber ?></td>
            <td><?php echo $item->FDAEUReleaseCFIADate ?></td>
            <td><?php echo $item->ETA_Date ?></td>
            <td style="color: <?php echo ($item->OverDueDays > 0) ? 'red' : 'black'; ?>"><?php echo $item->OverDueDays ?></td>
            <td><?php echo $item->ExpApprovalNo ?></td>
         </tr>
      <?php } ?>

   </tbody>
   <tfoot>
      <tr>
         <th style="min-width: 60px;">&nbsp;</th>
         <th style="min-width: 120px;">&nbsp;</th>
         <th style="min-width: 100px;">&nbsp;</th>
         <th style="min-width: 100px;">&nbsp;</th>
         <th style="min-width: 120px;">&nbsp;</th>
         <!-- <th style="min-width: 100px;"><b style="font-size: 15px;">Total Value USD</b></th>
         <th style="min-width: 120px;"><b style="font-size: 15px;" class="footTotInvoiceValueUSD"><?php echo $resGetFDAReleaseInformationAPI->Data->Totals->TotalInvoiceValueUSD ?></b></th> -->
         <th style="min-width: 100px;">&nbsp;</th>
         <th style="min-width: 100px;">&nbsp;</th>
         <th style="min-width: 100px;">&nbsp;</th>
         <th style="min-width: 100px;">&nbsp;</th>
         <th style="min-width: 100px;">&nbsp;</th>
      </tr>
   </tfoot>
</table>