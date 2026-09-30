<?php
//  ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL); 
 
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_SHIPMENT_SALES_LINEWISE_REPORT;

// echo API_SHIPMENT_SALES_LINEWISE_REPORT;

exit;

$FromDate = trim($_POST["FromDate"]);
$ToDate = trim($_POST["ToDate"]);
$Exporter = trim($_POST["Exporter"]);

$bodyContent = array(
    'FromDate' => $FromDate,
    'ToDate' => $ToDate,
    'Exporter' => $Exporter
    
);

$resList = CallAPI($api, "POST", "", $bodyContent);

$error = $resList->Error;

//$ListTotal = $resList->Data->Totals;
?>
<?php if (!isset($error) || empty($error)) {
    $List = $resList->Data;
     ?>
<div class="card">
    <div class="card-body">

        <table id="tblDataTablee" class="table table-hover table-bordered wrap">
            <thead class="thead-light w-100 wrap">
                <tr>
                    <th>S.No</th>
                    <th>Sale Invoice Number</th>
                    <th>Sale Invoice Line Number</th>
                    <th>Sale Invoice Date</th>
                    <th>Customer Name</th>
                    <th>Customer Country</th>
                    <th>Customer Zip Code</th>
                    <th>Purchase Order Number</th>
                    <th>Purchase Order Date</th>
                    <th>Destination State</th>
                    <th>Destination State Code</th>
                    <th>Country of Final Destination</th>
                    <th>Terms of Delivery</th>
                    <th>Terms of Payment</th>
                    <th>Product Description as in Invoice</th>
                    <th>Grade</th>
                    <th>Cooked Form</th>
                    <th>Head Status</th>
                    <th>Shell Status</th>
                    <th>Vein Status</th>
                    <th>Tail Status</th>
                    <th>Other Shrimp Preparation</th>
                    <th>Frozen Form</th>
                    <th>Flavoring</th>
                    <th>Presentation</th>
                    <th>Species</th>
                    <th>Preservative</th>
                    <th>Other Quality Grade</th>
                    <th>Glaze Percentage</th>
                    <th>NWFW</th>
                    <th>NCFC</th>
                    <th>Slabs Bags Per Carton</th>
                    <th>Weight of Each Slab bag pouch</th>
                    <th>Weight Measurement</th>
                    <th>Weight Denomination of Each Slab bag pouch</th>
                    <th>Packing Brand</th>
                    <th>No of Master Cartons</th>
                    <th>Quantity</th>
                    <th>Quantity Unit of Measure</th>
                    <th>Gross Unit Price</th>
                    <th>Value USD</th>
                    <th>Processed and Packed at</th>
                    <th>Importeron Record</th>
                    <th>Exporter</th>
                    <th>Container Number</th>
                    <th>Bill of Lading Number</th>
                    <th>Final Destinationasin BL</th>
                    <th>State Code of Final Destinationas in BL</th>
                    <th>Form 7501 Entry Date</th>
                    <th>Product Description as in Production</th>
                    <th>Parts of Body</th>
                    <th>Qty Converted to Kgs</th>
                    <th>GEX Qty</th>
                    <th>Qty Percentage</th>
                    <th>Value Percentage</th>
                    <th>GIN Packed Count</th>
                    <th>GIN Grade</th>
                    <th>GEX Packed Count</th>
                    <th>GEX Grade</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                <tr>
                    <td><?php echo $item->SNO ?></td>
                    <td><?php echo $item->SaleInvoiceNumber ?></td>
                    <td><?php echo $item->SaleInvoiceLineNumber ?></td>
                    <td><?php echo $item->SaleInvoiceDate ?></td>
                    <td><?php echo $item->CustomerName ?></td>
                    <td><?php echo $item->CustomerCountry ?></td>
                    <td><?php echo $item->CustomerZipCode?></td>
                    <td><?php echo $item->PurchaseOrderNumber ?></td>
                    <td><?php echo $item->PurchaseOrderDate ?></td>
                    <td><?php echo $item->DestinationState ?></td>
                    <td><?php echo $item->DestinationStateCode ?></td>
                    <td><?php echo $item->CountryofFinalDestination?></td>
                    <td><?php echo $item->TermsofDelivery ?></td>
                    <td><?php echo $item->TermsofPayment ?></td>
                    <td><?php echo $item->ProductDescriptionasinInvoice ?></td>
                    <td><?php echo $item->Grade ?></td>
                    <td><?php echo $item->CookedForm?></td>
                    <td><?php echo $item->HeadStatus ?></td>
                    <td><?php echo $item->ShellStatus ?></td>
                    <td><?php echo $item->VeinStatus ?></td>
                    <td><?php echo $item->TailStatus?></td>
                    <td><?php echo $item->OtherShrimpPreparation ?></td>
                    <td><?php echo $item->FrozenForm ?></td>
                    <td><?php echo $item->Flavoring ?></td>
                    <td><?php echo $item->Presentation ?></td>
                    <td><?php echo $item->Species ?></td>
                    <td><?php echo $item->Preservative?></td>
                    <td><?php echo $item->OtherQualityGrade ?></td>
                    <td><?php echo $item->GlazePercentage ?></td>
                    <td><?php echo $item->NWFW ?></td>
                    <td><?php echo $item->NCFC ?></td>
                    <td><?php echo $item->SlabsBagsPerCarton?></td>
                    <td><?php echo $item->WeightofEachSlabbagpouch ?></td>
                    <td><?php echo $item->WeightMeasurement ?></td>
                    <td><?php echo $item->WeightDenominationofEachSlabbagpouch ?></td>
                    <td><?php echo $item->PackingBrand?></td>
                    <td><?php echo $item->NoofMasterCartons?></td>
                    <td><?php echo $item->Quantity ?></td>
                    <td><?php echo $item->QuantityUnitofMeasure ?></td>
                    <td><?php echo $item->GrossUnitPrice ?></td>
                    <td><?php echo $item->ValueUSD ?></td>
                    <td><?php echo $item->ProcessedandPackedat ?></td>
                    <td><?php echo $item->ImporteronRecord?></td>
                    <td><?php echo $item->Exporter ?></td>
                    <td><?php echo $item->ContainerNumber ?></td>
                    <td><?php echo $item->BillofLadingNumber ?></td>
                    <td><?php echo $item->FinalDestinationasinBL?></td>
                    <td><?php echo $item->StateCodeofFinalDestinationasinBL ?></td>
                    <td><?php echo $item->Form7501EntryDate ?></td>
                    <td><?php echo $item->ProductDescriptionasinProduction ?></td>
                    <td><?php echo $item->PartsofBody ?></td>
                    <td><?php echo $item->QtyConvertedtoKgs?></td>
                    <td><?php echo $item->GEXQty ?></td>
                    <td><?php echo $item->QtyPercentage ?></td>
                    <td><?php echo $item->ValuePercentage ?></td>
                    <td><?php echo $item->GINPackedCount ?></td>
                    <td><?php echo $item->GINGrade?></td>
                    <td><?php echo $item->GEXPackedCount ?></td>
                    <td><?php echo $item->GEXGrade ?></td>

                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <!--  <tr>
                <th><b style="font-size: 15px;">Total</b></th>	
                <th><b style="font-size: 15px;"><?php //echo $ListTotal->TotalInvoices ?></b></th>		        
                <th>&nbsp;</th>
                <th><b style="font-size: 15px;"><?php  //echo $ListTotal->TotalNetWeightLbs ?></b></th>
                <th><b style="font-size: 15px;"><?php // echo $ListTotal->TotalNetWeightKgs ?></b></th>
                <th><b style="font-size: 15px;"><?php // echo $ListTotal->TotalFOBValueUSD ?></b></th>
                <th><b style="font-size: 15px;"><?php // echo $ListTotal->TotalFobValueINR ?></b></th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>-->
            </tfoot>
        </table>

    </div>
</div>

<?php } 
else {
    echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : ". $error."</div>";
} 
?>