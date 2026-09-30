<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
  
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT . 'config/api-call.php';

$ProformaID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

if (isset($_GET['ID']) && !empty($_GET['ID'])) {

    $ProformaID = trim($_GET['ID']);

    $api = API_PDF_PROFORMA_PRINT;

    $bodyContent = array("ProformaID" => $ProformaID);
    $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

    // var_dump($resPostProfromaPrint);

    $result = $resPostProfromaPrint->Data;
    $resLabels = $resPostProfromaPrint->Data->Labels;
    $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
    $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
    $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
    $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;

} else {

}


?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>
       <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo  rand() ?>"  rel="stylesheet" type="text/css">
    </head>

    <?php  include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . '/includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include_once ROOT . 'includes/page-header.php' ?>   
                       
                    
                        <!-- start page title -->
                        <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body" id="ShowDataTable">
                                                                                
                                                <div  class="pdf-content" id="printArea">
                                                    <div style="text-align: center">
                                                        <button class="generate" id="generate" onclick="downloadpdf('<?php echo str_replace('/', '_', $result->InvoiceNumber) ?>.pdf')">Download PDF</button>
                                                    </div>
                                                    
                                                    <div id="printTable">
                                                        <table class="tblMain">
                                                            <tr>
                                                                <td>
                                                                    <h1 class="page-title">PROFORMA INVOICE</h1>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table class="bt2 bl2 br2 bb2 " class="w100">                                                                        
                                                                        <tr>
                                                                            <td class="p-0 tblHeader">
                                                                                <table class="w100" >
                                                                                    <tr>
                                                                                        <td class="br bb w50">
                                                                                            <span class="f-14 mb-1 d-block"><?php echo $resLabels->Exporter ?>:</span>
                                                                                            <span class="bold"><?php echo $result->ExporterName ?> <br>
                                                                                            <?php echo nl2br($result->ExporterAddress) ?></span>
                                                                                        </td>
                                                                                    
                                                                                        <td class="bb p-0">
                                                                                            <table class="w100">
                                                                                                <tr>
                                                                                                    <td class="br bb" style="width: 70%">
                                                                                                        <?php echo $resLabels->InvoiceNoDt ?> <br>
                                                                                                        <b><?php echo $result->InvoiceNumber . ", " . $result->InvoiceDate ?></b>
                                                                                                    </td>
                                                                                                    <td class="bb f-12">Exporter's Ref: <br>
                                                                                                        <?php
                                                                                                        if (isset($result->ExporterIeCode) && !empty($result->ExporterIeCode)) {
                                                                                                            echo "I.E.CODE: " . $result->ExporterIeCode;
                                                                                                        }
                                                                                                        ?>
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td colspan="2" class="f-14">
                                                                                                        <?php echo $resLabels->PurchseOrderNo ?>:<?php echo $result->PoNumber . ", " . $result->PoDate ?><br>
                                                                                                        <?php echo $resLabels->ReferenceNo ?> : <?php echo $result->ReferenceNumber ?>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td class="br bb">
                                                                                            <span class="mb-1 d-block f-12"><?php echo $resLabels->Consignee ?>:</span>
                                                                                            <span class="bold">
                                                                                                <?php echo $result->ConsigneeName ?> <br>
                                                                                                <?php echo nl2br($result->ConsigneeAddress) ?>
                                                                                            </span>
                                                                                        </td> 
                                                                                      
                                                                                        <td class="p-0 bb">
                                                                                            <table class="w100 p-0">
                                                                                                
                                                                                                <tr>
                                                                                                    <td class="f-12 bb" colspan="2">
                                                                                                        Buyer (if other than consignee): 
                                                                                                            <?php if (isset($result->BuyerName) && !empty($result->BuyerName)) {
                                                                                                                echo $result->BuyerName . "<br>" . nl2br($result->BuyerAddress);
                                                                                                            } ?>
                                                                                                        <br><br>
                                                                                                    </td>
                                                                                                </tr>
                                                                                                
                                                                                                <tr>
                                                                                                    <td class="bolder bb f-13" colspan="2">
                                                                                                        <?php echo $resLabels->Manufacturer ?>: <br> <?php echo $result->ProcessorName ?> <br>
                                                                                                        <?php echo nl2br($result->ProcessorAddress) ?>
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td class="bolder br f-13 w50">
                                                                                                    <?php echo $resLabels->ApprovalNo ?>  <?php echo $result->ApprovalNumber ?> 
                                                                                                    </td> 
                                                                                                    <?php if (isset($resLabels->UsfdaRegistrationNo) && !empty($resLabels->UsfdaRegistrationNo)) { ?>
                                                                                                    <td class="bolder f-13 w50">
                                                                                                    <?php  echo $resLabels->UsfdaRegistrationNo ?>  <?php echo $result->USFDANumber  ?>                                                                                                                                                                                                      </td>
                                                                                                    <?php } ?>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="p-0 br"> 
                                                                                            <table class="w100">
                                                                                                <tr>
                                                                                                    <td class="bb br">Pre-carriage by <br>REFRIGERATED CONTAINER</td>
                                                                                                    <td class="bb">Place of Receipt by pre-carrier</td>
                                                                                                </tr>
                                                                                              
                                                                                                <tr>
                                                                                                    <td class="bb br"><?php echo $resLabels->Vessel ?>  </td>
                                                                                                    <td class="bb"><?php echo $resLabels->PortOfLoading ?><br>ANY INDIAN PORT</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td class="bb br"><?php echo $resLabels->PortOfDischrg ?>: <?php echo $result->PortOfDischarge ?></td>
                                                                                                    <td class="bb"><?php echo $resLabels->FinlDestintn ?>: <?php echo $result->FinalDestination ?></td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td class="br">
                                                                                                        Marks & Nos./ <br>
                                                                                                        <?php echo $resLabels->ContainerNo ?>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        No. & Kind of Packages<br>  
                                                                                                        <?php echo $resGrandTotal->MCS; ?>  <br>                                                                                                    
                                                                                                        MASTER CARTONS
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                        <td class="p-0 ">
                                                                                            <table class="w100">
                                                                                                <tr>
                                                                                                    <td class="br bb w50">
                                                                                                        <?php echo $resLabels->CountryOfOrigin ?> <br> INDIA
                                                                                                    </td>
                                                                                                    <td class="bb w50">
                                                                                                        <?php echo $resLabels->FinalDestintnCountry ?> <br> <?php echo $result->CountryOfDestination ?>
                                                                                                    </td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td colspan="2">
                                                                                                        <p class="bold mb-2"><?php echo $resLabels->PaymentTerms ?> </p> 
                                                                                                        <span><?php echo nl2br($result->PaymentTerms) . "<br>";
                                                                                                        echo $result->ShippingTerms . ", " . $result->FinalDestination .'('.$result->DeliveryTerms.')'?></span>
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td colspan="2">
                                                                                                        <span class="f-18 bold"><?php echo $resLabels->ShipmetDt ?> : <?php echo $result->ShipByDate ?></span>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="p-0 tblBody">
                                                                                <table class="w100  text-center" >
                                                                                    <tr class="text-center  f-12 trBreakUpHeader">

                                                                                        <?php
                                                                                        $i = 1;
                                                                                        $countKey = count($resLableKeys);
                                                                                        foreach ($resLableKeys as $keyVal) {
                                                                                            if ($i == 1) {
                                                                                                echo '<th class="br bt bb" style="width: 50%">' . $resTableLabels->$keyVal . '</th>';
                                                                                            } else {
                                                                                                $class = ($i === $countKey) ? '' : 'br';
                                                                                                echo '<th class="' . $class . ' bt bb" style="">' . $resTableLabels->$keyVal . '</th>';
                                                                                            }
                                                                                            $i++;
                                                                                        }
                                                                                        ?>
                                                                                    </tr>                
                                                                                    <?php

                                                                                    foreach ($resItemsGroup as $ItemsGroup) {
                                                                                        if ($ItemsGroup->ShowSubTotal == true) {
                                                                                            $rowspan = count($ItemsGroup->ItemsList) + 1;
                                                                                        } else {
                                                                                            $rowspan = count($ItemsGroup->ItemsList);
                                                                                        }
                                                                                        echo '
                                                                                            <tr>
                                                                                                <td rowspan="' . $rowspan . '" class="text-left br">
                                                                                                    ' . $ItemsGroup->ItemDescription . ' <br>';
                                                                                        echo isset($ItemsGroup->Species) && !empty($ItemsGroup->Species) ? "SPECIES :" . $ItemsGroup->Species . "<br>" : "";
                                                                                        echo isset($ItemsGroup->PackingStyle) && !empty($ItemsGroup->PackingStyle) ? "PACKING :" . $ItemsGroup->PackingStyle . "<br>" : "";
                                                                                        echo isset($ItemsGroup->Brand) && !empty($ItemsGroup->Brand) ? "<span class='bold'>BRAND :" . $ItemsGroup->Brand . "</span>" : "";
                                                                                        echo '<br><br>
                                                                                                </td>
                                                                                            </tr>';

                                                                                        $j = 1;
                                                                                        foreach ($ItemsGroup->ItemsList as $RowItem) {
                                                                                            $count = count($ItemsGroup->ItemsList);
                                                                                            if ($count != $j) {
                                                                                                echo '<tr>';
                                                                                                $s = 1;
                                                                                                $countKey = count($resLableKeys);
                                                                                                foreach ($resLableKeys as $keyVal) {
                                                                                                    if ($s > 1) {
                                                                                                        $class = ($s === $countKey) ? '' : 'br';
                                                                                                        echo '<td class="' . $class . '">' . $RowItem->$keyVal . '</td>';
                                                                                                    }
                                                                                                    $s++;
                                                                                                }
                                                                                                echo '</tr>';
                                                                                            } else { // Last Record for Subtotal
                                                                                                if ($ItemsGroup->ShowSubTotal == true) {
                                                                                                    echo '<tr>';
                                                                                                    $a = 1;
                                                                                                    $countKey = count($resLableKeys);
                                                                                                    foreach ($resLableKeys as $key => $keyVal) {
                                                                                                        if ($a > 1) {
                                                                                                            $class = ($a === $countKey) ? '' : 'br';
                                                                                                            echo '<td class="' . $class . ' bt bb">' . $RowItem->$keyVal . '</td>';
                                                                                                        }
                                                                                                        $a++;
                                                                                                    }
                                                                                                    echo '</tr>';
                                                                                                }

                                                                                            }
                                                                                            $j++;
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                    <!-- </tr> -->
                                                                                    <tr>
                                                                                        <?php
                                                                                        echo '<td class="bt br bb">' . $resGrandTotal->Lable . '</td>';
                                                                                        $x = 1;
                                                                                        $countKey = count($resLableKeys);
                                                                                        foreach ($resLableKeys as $key => $keyVal) {
                                                                                            if ($x > 1) {
                                                                                                $class = ($x === $countKey) ? '' : 'br';
                                                                                                echo '<td class="' . $class . ' bt bb">' . $resGrandTotal->$keyVal . '</td>';
                                                                                            }
                                                                                            $x++;
                                                                                        }
                                                                                        ?>
                                                                                    </tr>

                                                                                </table>    
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="p-0">
                                                                                <table class="w100 tblFooter" >
                                                                                    <tr>
                                                                                        <td class="bb text-left p-0">
                                                                                            <table  class="w100">
                                                                                                <tr>

                                                                                                    <?php 
                                                                                                    if($result->QtyUnits == "KGS") { 
                                                                                                    ?>
                                                                                                    <td class="text-left w50">
                                                                                                        <?php 
                                                                                                            if (isset($result->NetWeightKGS) && !empty($result->NetWeightKGS)) {
                                                                                                                echo '<span class="bold" style="margin-right: 43px;">Net Weight: </span> '. $result->NetWeightKGS .' Kgs <br>'; 
                                                                                                            }                                                                                                            
                                                                                                            if (isset($result->FWeightKgs) && !empty($result->FWeightKgs)) {
                                                                                                                echo '<span class="bold" style="margin-right: 43px;">Frozen Weight: </span> '. $result->FWeightKgs .' Kgs <br>'; 
                                                                                                            } 
                                                                                                            if (isset($result->GrossWeightKGS) && !empty($result->GrossWeightKGS)) {
                                                                                                                echo '<span class="bold" style="margin-right: 25px;">Gross Weight: </span> ' . $result->GrossWeightKGS .' Kgs'; 
                                                                                                            }
                                                                                                        ?>
                                                                                                    </td>
                                                                                                    
                                                                                                    <?php 
                                                                                                    }

                                                                                                    // echo '<td class="br p-0" style="width: 0px;"></td>'; 
                                                                                                    
                                                                                                    if ($result->QtyUnits != "KGS") { 
                                                                                                    ?>
                                                                                                    <td class="bl">
                                                                                                        <?php 
                                                                                                            if (isset($result->NetWeightLBS) && !empty($result->NetWeightLBS)) {
                                                                                                                echo '<span class="bold" style="margin-right: 43px;">Net Weight: </span> ' . $result->NetWeightLBS .' LBs <br>';
                                                                                                            }
                                                                                                            if (isset($result->FWeightLbs) && !empty($result->FWeightLbs)) {
                                                                                                                echo '<span class="bold" style="margin-right: 43px;">Frozen Weight: </span> ' . $result->FWeightLbs .' LBs <br>';
                                                                                                            }
                                                                                                            if (isset($result->GrossWeightLBS) && !empty($result->GrossWeightLBS)) {
                                                                                                                echo '<span class="bold" style="margin-right: 25px;">Gross Weight: </span> ' . $result->GrossWeightLBS .' LBs';
                                                                                                            }
                                                                                                        ?>
                                                                                                    </td>
                                                                                                    <?php } ?>

                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                        
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="text-left">Amount chargeable(in words ) <br> (<?php echo $result->CurrencyCode ?> : <?php echo $result->AmountInWords ?> ) </td>
                                                                                    </tr>

                                                                                    <?php if (isset($result->BankDetails) && !empty($result->BankDetails)) { ?>
                                                                                        <tr>
                                                                                            <td class="bold text-left">
                                                                                                <?php echo $resLabels->BankDetails . " " . nl2br($result->BankDetails) ?>
                                                                                            </td>
                                                                                        </tr>                        
                                                                                        <tr>
                                                                                            <td class="bold text-left">
                                                                                                <?php echo $resLabels->BankRoutionDetails . " " . nl2br($result->BankRoutionDetails) ?>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td class="bold text-left">
                                                                                                <?php echo $resLabels->BankNegotiationDetails . " " . nl2br($result->BankNegotiationDetails) ?>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php } ?>                

                                                                                    <tr>
                                                                                        <td class="w100 bt p-0 text-left">
                                                                                            <table style="width: 100%">
                                                                                                <tr>
                                                                                                    <td class="br" style="width: 70%">

                                                                                                        <?php echo $resLabels->Declrtn ?> : <br>
                                                                                                        <?php echo $result->Declearation ?> <br><br>
                                                                                                        <?php //echo $result->Remarks ?>
                                                                                                    </td>
                                                                                                    <td style="width: 30%; text-align: center">
                                                                                                        <?php echo $result->SignFor ?>
                                                                                                        <br>
                                                                                                        <br>
                                                                                                        <br>
                                                                                                        <?php echo $result->SignDescription ?>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                    </table>   
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        
                                                    </div>  <!-- Actual Page Content -->
                                                    
                                                    

                                                </div> <!-- END pdf-content -->



                                        </div> <!-- end card body-->
                                    </div> <!-- end card -->
                                </div><!-- end col-->
                            </div>

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include_once ROOT . '/includes/footer.php' ?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . '/includes/common-js.php' ?>
        <?php include_once ROOT . '/includes/app-js.php' ?>

        <script>
            var invNo = "<?php echo $result->InvoiceNumber ?>";
        </script>
        <?php include_once ROOT . '/includes/footer-print-pdf.php' ?>

    </body>
</html>