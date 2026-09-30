<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// echo $_SESSION["Token"];

$ProformaID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

if (isset($_GET['ID']) && !empty($_GET['ID'])) {

    $ProformaID = trim($_GET['ID']);

    $api = API_PDF_CUSTOM_PRINT;

    $bodyContent = array("ProformaID" => $ProformaID);
    $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

    // var_dump($resPostProfromaPrint);

    $result = $resPostProfromaPrint->Data;
    $resLabels = $resPostProfromaPrint->Data->Labels;
    $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
    $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
    $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
    $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;

    $resPaymentDetails = $resPostProfromaPrint->Data->PaymentDetails;
    //var_dump($result); exit;
    // echo json_encode($resPostProfromaPrint, JSON_PRETTY_PRINT);
    // exit();
} else {

}


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>" rel="stylesheet"
        type="text/css">
    <style>
        td {
            padding: 1px 3px;
        }

        .f-11 {
            font-size: 11px;
        }

        .lh16 {
            line-height: 16px;
        }
    </style>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

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

                                <div class="pdf-content" id="printArea">
                                    <div style="text-align: center">
                                        <button class="generate" id="generate"
                                            onclick="downloadpdf('<?php echo str_replace('/', '_', $result->InvoiceNumber) ?>.pdf')">Download
                                            PDF</button>
                                    </div>

                                    <div id="printTable">
                                        <table class="tblMain">
                                            <tr>
                                                <td>
                                                    <h1 class="page-title">INVOICE / PACKING LIST</h1>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table class="bt bl br bb w100 f-13">
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100">
                                                                    <tr>
                                                                        <td class="br bb w50 lh18">
                                                                            <div class="f-14 mb-1 d-block">
                                                                                <?php echo $resLabels->Exporter ?>:
                                                                            </div>
                                                                            <div class="bold">
                                                                                <?php echo $result->ExporterName ?>
                                                                            </div>
                                                                            <div>
                                                                                <?php echo nl2br($result->ExporterAddress) ?>
                                                                            </div>
                                                                        </td>

                                                                        <td class="bb p-0">
                                                                            <table class="w100">
                                                                                <tr>
                                                                                    <td class="br bb"
                                                                                        style="width: 70%">
                                                                                        <?php echo $resLabels->InvoiceNoDt ?>
                                                                                        <div class="bold">
                                                                                            <?php echo $result->InvoiceNumber . ", " . $resLabels->InvoiceDate . ' ' . $result->InvoiceDate ?>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="bb f-12">Exporter's Ref:
                                                                                        <br>
                                                                                        <?php
                                                                                        if (isset($result->ExporterIeCode) && !empty($result->ExporterIeCode)) {
                                                                                            echo "I.E.CODE: " . $result->ExporterIeCode;
                                                                                        }
                                                                                        ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="2" class="f-14">
                                                                                        <?php echo $resLabels->PurchseOrderNo ?>
                                                                                        :
                                                                                        <?php echo $result->PoNumber . " " .
                                                                                            $resLabels->PurchaseOrderDate . " : " . $result->PoDate ?><br>
                                                                                        <?php echo $resLabels->ReferenceNo ?>
                                                                                        :
                                                                                        <?php echo $result->ReferenceNumber ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="br bb p-0 lh18">
                                                                            <table class="w100">
                                                                                <tr>
                                                                                    <td>
                                                                                        <div class="mb-1 d-block f-12">
                                                                                            <?php echo $resLabels->Consignee ?>:
                                                                                        </div>
                                                                                        <div class="bold"
                                                                                            style="line-height: 16px;">
                                                                                            <?php echo $result->ConsigneeName ?>
                                                                                            <br>
                                                                                            <?php echo nl2br($result->ConsigneeAddress) ?>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>


                                                                                <!-- <tr>
                                                                                                    <td class="bt">
                                                                                                        <span class="mb-1 d-block f-12"><?php echo "ALSO NOTIFY" ?>:</span>
                                                                                                        <span class="bold">
                                                                                                            <?php // echo $result->ConsigneeName ?> <br>
                                                                                                            <?php // echo nl2br($result->ConsigneeAddress) ?>
                                                                                                        </span>
                                                                                                    </td>
                                                                                                </tr> -->
                                                                            </table>
                                                                        </td>

                                                                        <td class="p-0 bb lh18">
                                                                            <table class="w100 p-0">

                                                                                <tr>
                                                                                    <td class="f-12 bb" colspan="2">
                                                                                        Buyer (if other than consignee):
                                                                                        <?php if (isset($result->BuyerName) && !empty($result->BuyerName)) {
                                                                                            echo $result->BuyerName . "<br>" . nl2br($result->BuyerAddress);
                                                                                        } ?>
                                                                                    </td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td class="bb f-13"
                                                                                        style="line-height: 16px;"
                                                                                        colspan="2">
                                                                                        <?php echo $resLabels->Manufacturer ?>
                                                                                        <div class="bold">
                                                                                            <?php echo $result->ProcessorName ?>
                                                                                            <br>
                                                                                            <?php echo nl2br($result->ProcessorAddress) ?>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bold f-13  w50">
                                                                                        <?php echo $resLabels->ApprovalNo ?>
                                                                                        <?php echo $result->ApprovalNumber ?>
                                                                                    </td>
                                                                                    <?php if (isset($result->USFDANumber) && !empty($result->USFDANumber)) { ?>
                                                                                        <td class="bold bl bb f-13 w50">
                                                                                            <?php echo $resLabels->UsfdaRegistrationNo ?>
                                                                                            <?php echo $result->USFDANumber ?>
                                                                                        </td>
                                                                            </td>
                                                                        <?php } ?>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-0 br lh18">
                                                                <table class="w100">
                                                                    <tr>
                                                                        <td class="bb br w50">Pre-carriage by
                                                                            <br>REFRIGERATED CONTAINER
                                                                        </td>
                                                                        <td class="bb">Place of Receipt by pre-carrier
                                                                            <br><?php echo $result->PortOfLoading ?>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="bb br">
                                                                            <?php echo $resLabels->Vessel ?>
                                                                        </td>
                                                                        <td class="bb">
                                                                            <?php echo $resLabels->PortOfLoading ?><br><?php echo $result->PortOfLoading ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="bb br">
                                                                            <?php echo $resLabels->PortOfDischrg ?>:
                                                                            <?php echo $result->PortOfDischarge ?>
                                                                        </td>
                                                                        <td class="bb">
                                                                            <?php echo $resLabels->FinlDestintn ?>:
                                                                            <?php echo $result->FinalDestination ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="br bb">
                                                                            Marks & Nos./ <br>
                                                                            <div class="d-block f-12">
                                                                                <?php echo $resLabels->ContainerNo ?> :
                                                                                <?php echo $result->ContainerNo ?>
                                                                            </div>
                                                                        </td>
                                                                        <td class="bb">
                                                                            No. & Kind of Packages<br>
                                                                            <div>
                                                                                <b><?php echo $resGrandTotal->MCS; ?></b>
                                                                                MASTER CARTONS
                                                                            </div>
                                                                            <!-- <div> MASTER CARTONS</div> -->
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="br f-12">LINER SEAL:
                                                                            <?php echo $result->LinearSealNo ?>
                                                                        </td>
                                                                        <td class="f-12">CUSTOMS SEAL:
                                                                            <?php echo $result->CustomSealNo ?>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td class="p-0 lh18">
                                                                <table class="w100">
                                                                    <tr>
                                                                        <td class="br bb w50 text-center">
                                                                            <?php echo $resLabels->CountryOfOrigin ?>
                                                                            <br> INDIA
                                                                        </td>
                                                                        <td class="bb w50 text-center">
                                                                            <?php echo $resLabels->FinalDestintnCountry ?>
                                                                            <br>
                                                                            <?php echo $result->CountryOfDestination ?>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <p class="bold mb-2">
                                                                                <?php echo $resLabels->PaymentTerms ?>
                                                                            </p>
                                                                            <span>
                                                                                <?php
                                                                                echo nl2br($result->PaymentTerms) . "<br>";
                                                                                echo $result->ShippingTerms . " " . $result->ShippingTermsFinalDestination . ' ( ' . $result->DeliveryTerms . ' )'
                                                                                    ?>
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <!-- <tr>
                                                                                                    <td colspan="2">
                                                                                                        <span class="f-18 bold"><?php // echo $resLabels->ShipmetDt ?> : <?php // echo $result->ShipByDate ?></span>
                                                                                                    </td>
                                                                                                </tr> -->
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <!-- <tr>
                                                                            <td colspan="2" class="bt p-0">
                                                                                <table class="w100">
                                                                                                                                                                                              
                                                                                </table>
                                                                            </td>
                                                                        </tr> -->
                                            <tr>
                                                <td class="p-0 tblBody lh17">
                                                    <table class="w100  text-center f-12">
                                                        <tr class="text-center trBreakUpHeader">

                                                            <?php
                                                            $i = 1;
                                                            $countKey = count($resLableKeys);
                                                            foreach ($resLableKeys as $keyVal) {
                                                                if ($i == 1) {
                                                                    echo '<th class="br bt" style="width: 50%">                                                                                                        
                                                                                                        ' . $resTableLabels->$keyVal . ' 
                                                                                                        <div class="text-left mt-1">"IN FROZEN FORM OTHER THAN AFD"</div>                                                                                                        
                                                                                                        </th>';
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
                                                            echo isset($ItemsGroup->PackingStyle) && !empty($ItemsGroup->PackingStyle) ? "PACKING :" . $ItemsGroup->PackingStyle . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" : "";
                                                            echo isset($ItemsGroup->Brand) && !empty($ItemsGroup->Brand) ? "BRAND :" . $ItemsGroup->Brand . "" : "";
                                                            echo '<div style="height: 4px">&nbsp;</div>
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

                                                        <?php if (isset($result->ChargeAmount) && !empty($result->ChargeAmount) && ($result->ChargeAmount) != '0') { ?>
                                                            <tr>
                                                                <td class="text-left"> </td>
                                                                <td colspan="<?php echo $countKey - 2; ?>"
                                                                    class="bb bl text-right w25">
                                                                    <?php echo $result->ChargeName; ?>
                                                                </td>
                                                                <td class="bb bl text-right w25">
                                                                    <?php echo $result->ChargeAmount; ?>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td class="text-left"> </td>

                                                                <td colspan="<?php echo $countKey - 2; ?>"
                                                                    class="bb bl text-right w25">
                                                                    <?php echo 'Balance Amount Due: ' ?>
                                                                </td>
                                                                <td class="bb  bl text-right w25">
                                                                    <?php echo $result->BalanceAmount; ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>

                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="p-0">
                                                    <table class="w100 tblFooter">
                                                        <tr>
                                                            <td class="bb text-left p-0">
                                                                <table class="w100 lh18">
                                                                    <tr>

                                                                        <?php
                                                                        //  if ($result->QtyUnits == "KGS") {
                                                                        ?>
                                                                        <td class="text-left w50">
                                                                            <?php
                                                                            if (isset($result->NetWeightKGS) && !empty($result->NetWeightKGS)) {
                                                                                echo '<div style="margin-right: 43px;">Net Weight:  ' . $result->NetWeightKGS . ' Kgs </div>';
                                                                            }
                                                                            if (isset($result->FrozenWeightKGS) && !empty($result->FrozenWeightKGS)) {
                                                                                echo '<div style="margin-right: 43px;">Frozen Weight:  ' . $result->FrozenWeightKGS . ' Kgs </div>';
                                                                            }
                                                                            if (isset($result->GrossWeightKGS) && !empty($result->GrossWeightKGS)) {
                                                                                echo '<div style="margin-right: 25px;">Gross Weight:  ' . $result->GrossWeightKGS . ' Kgs </div>';
                                                                            }
                                                                            ?>
                                                                        </td>

                                                                        <td class="text-right w50">
                                                                            <?php
                                                                            if (isset($result->NetWeightLBS) && !empty($result->NetWeightLBS)) {
                                                                                echo '<div style="margin-right: 43px;">Net Weight:  ' . $result->NetWeightLBS . ' LBs </div>';
                                                                            }
                                                                            if (isset($result->FWeightLbs) && !empty($result->FWeightLbs)) {
                                                                                echo '<div style="margin-right: 43px;">Frozen Weight:  ' . $result->FWeightLbs . ' LBs </div>';
                                                                            }
                                                                            if (isset($result->GrossWeightLBS) && !empty($result->GrossWeightLBS)) {
                                                                                echo '<div style="margin-right: 25px;">Gross Weight:  ' . $result->GrossWeightLBS . ' LBs </div>';
                                                                            }
                                                                            ?>
                                                                        </td>

                                                                        <?php
                                                                        //  }
                                                                        
                                                                        // echo '<td class="br p-0" style="width: 0px;"></td>'; 
                                                                        
                                                                        // if ($result->QtyUnits != "KGS") {
                                                                        //   //echo  $result->NetWeightLBS;  
                                                                        //     ?>
                                                                        <!--   <td>-->
                                                                        <?php
                                                                        //             if (isset($result->NetWeightLBS) && !empty($result->NetWeightLBS)) {
                                                                        //                 echo '<div style="margin-right: 43px;">Net Weight:  ' . $result->NetWeightLBS . ' LBs </div>';
                                                                        //             }
                                                                        //             if (isset($result->FWeightLbs) && !empty($result->FWeightLbs)) {
                                                                        //                 echo '<div style="margin-right: 43px;">Frozen Weight:  ' . $result->FWeightLbs . ' LBs </div>';
                                                                        //             }
                                                                        //             if (isset($result->GrossWeightLBS) && !empty($result->GrossWeightLBS)) {
                                                                        //                 echo '<div style="margin-right: 25px;">Gross Weight:  ' . $result->GrossWeightLBS . ' LBs </div>';
                                                                        //             }
                                                                        //             ?>
                                                                        <!--  </td>-->
                                                                        <?php //} ?>

                                                                    </tr>
                                                                </table>
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td class="lh17 bb bold">
                                                                Amount chargeable(in words)
                                                                (<?php // echo $result->CurrencyCode ." : " ?>
                                                                <?php echo $result->AmountInWords ?> )
                                                                <?php
                                                                // if (isset($result->BankDetails) && !empty($result->BankDetails)) { 
                                                                //     echo "<span class='mb-3 d-block'>".$resLabels->BankDetails . " " . nl2br($result->BankDetails) . "</span>";
                                                                //     echo "<span class='mb-3 d-block'>" .$resLabels->BankRoutionDetails . " " . nl2br($result->BankRoutionDetails) . "</span>";
                                                                //     echo "<span class='mb-3 d-block'>" .$resLabels->BankNegotiationDetails . " " . nl2br($result->BankNegotiationDetails)."</span>";
                                                                // } 
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-0 lh17">
                                                                <table class="w100">
                                                                    <tr>
                                                                        <td class="text-left w50"
                                                                            style="line-height: 16px;">
                                                                            <div>S.L NO OF EXPORT PRODUCT - 2</div>
                                                                            <div>PRODUCT CODE : 6- FISH & FISHERY
                                                                                PRODUCT</div>
                                                                            <div>SHIPPED UNDER D2</div>
                                                                            <div>State of origin of goods Andhra Pradesh
                                                                                ( Code:80)</div>
                                                                            <div>HS CODE: 030617</div>
                                                                            <?php if ($result->CountryID == 80) { ?>
                                                                                <div>CEPAJP - INDIA - Japan CEPA</div>
                                                                            <?php } ?>
                                                                            <div class="f-16">
                                                                                <?php echo $result->ProductTypes ?>
                                                                            </div>
                                                                            <div style="height: 5px">&nbsp;</div>
                                                                            <div> GST PROVISIONAL ID : 37AAGCC9463J1Z5
                                                                            </div>
                                                                            <?php if (isset($result->DriverName) && !empty($result->DriverName)) { ?>
                                                                                <div> TRANSPORTER NAME:
                                                                                    <?php echo $result->DriverName ?>
                                                                                </div>
                                                                            <?php } ?>
                                                                            <?php if (isset($result->VehicleNo) && !empty($result->VehicleNo)) { ?>
                                                                                <div>VEHICLE NO:
                                                                                    <?php echo $result->VehicleNo ?>
                                                                                </div>
                                                                            <?php } ?>
                                                                            <div><?php echo $result->CIDescription ?>
                                                                            </div>
                                                                            <div>TRANSPORT FROM PATHARLAGADDA </div>
                                                                            <div>TRANSPORT THE GOODS : FOR EXPORTS
                                                                                THROUGH SEAPORT WITHIN THE COUNTRY</div>
                                                                        </td>
                                                                        <td class="p-0">
                                                                            <table class="w100 tbl-py-0 lh16">
                                                                                <?php if ($result->ShowAntiDumping == 1) { ?>
                                                                                    <tr>
                                                                                        <td class="bl">Invoice Value</td>
                                                                                        <td>$</td>
                                                                                        <td> <?php echo $resPaymentDetails->InvValueUSD ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="bl">Less: Freight</td>
                                                                                        <td>$</td>
                                                                                        <td><?php echo $resPaymentDetails->FrieghtCharge ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="bl"></td>
                                                                                        <td>$</td>
                                                                                        <td><?php echo $resPaymentDetails->InvValueUSDWithOutDuties ?>
                                                                                        </td>
                                                                                    </tr>

                                                                                    <?php
                                                                                    $rtClassBorder = 'bb';
                                                                                    if ($resPaymentDetails->ReciprocalTariffsPerc != 0) {
                                                                                        $rtClassBorder = '';
                                                                                    }

                                                                                    if ($resPaymentDetails->CountervailingDutyPerc != 0) {

                                                                                        ?>
                                                                                        <tr>
                                                                                            <td class="bl">Less :
                                                                                                ADD@<?php echo $resPaymentDetails->AntiDumpingDutyPerc ?>%
                                                                                                + Port
                                                                                                Charges@<?php echo $resPaymentDetails->HarbourMaintenanceFeePerc + $resPaymentDetails->MerchandiseProcessFeePerc ?>%
                                                                                            </td>
                                                                                            <td>$</td>
                                                                                            <!-- <td ><?php //echo $resPaymentDetails->DutiesPrice ?></td> -->
                                                                                            <td><?php echo $resPaymentDetails->AntidumpingandPortChargesPrice ?>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td
                                                                                                class="bl <?php echo $rtClassBorder; ?>">
                                                                                                Less :
                                                                                                CVD@<?php echo $resPaymentDetails->CountervailingDutyPerc ?>
                                                                                                %</td>
                                                                                            <td
                                                                                                class="<?php echo $rtClassBorder; ?>">
                                                                                                $</td>
                                                                                            <td
                                                                                                class="<?php echo $rtClassBorder; ?>">
                                                                                                <?php echo $resPaymentDetails->CountervailingDuty ?>
                                                                                            </td>
                                                                                        </tr>

                                                                                        <?php if ($resPaymentDetails->ReciprocalTariffsPerc != 0) { ?>

                                                                                            <tr>
                                                                                                <td class="bl bb">Less :
                                                                                                    RECIPROCAL
                                                                                                    TARIFF@<?php echo $resPaymentDetails->ReciprocalTariffsPerc ?>
                                                                                                    %</td>
                                                                                                <td class=" bb">$</td>
                                                                                                <td class=" bb">
                                                                                                    <?php echo $resPaymentDetails->ReciprocalTariffsFee ?>
                                                                                                </td>
                                                                                            </tr>

                                                                                        <?php } ?>

                                                                                    <?php } else { ?>

                                                                                        <tr>
                                                                                            <td class="bl bb">Less :
                                                                                                ADD@<?php echo $resPaymentDetails->AntiDumpingDutyPerc ?>%
                                                                                                + Port
                                                                                                Charges@<?php echo $resPaymentDetails->HarbourMaintenanceFeePerc + $resPaymentDetails->MerchandiseProcessFeePerc ?>%
                                                                                            </td>
                                                                                            <td class=" bb">$</td>
                                                                                            <!-- <td ><?php //echo $resPaymentDetails->DutiesPrice ?></td> -->
                                                                                            <td class=" bb">
                                                                                                <?php echo $resPaymentDetails->AntidumpingandPortChargesPrice ?>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php } ?>
                                                                                    <!-- <tr>
                                                                                                                    <td class="bl bb">FOB Value in US$</td>
                                                                                                                    <td class=" bb">$</td>
                                                                                                                    <td class=" bb"> <? php// echo $resPaymentDetails->FOBValueUSD ?></td>
                                                                                                                </tr> -->
                                                                                <?php } ?>

                                                                                <tr>
                                                                                    <td>&nbsp;</td>
                                                                                    <td></td>
                                                                                    <td></td>
                                                                                </tr>
                                                                                <?php if ($result->InvoiceType == "IGST") { ?>
                                                                                    <tr>
                                                                                        <td>FOB VALUE IN US$</td>
                                                                                        <td>$</td>
                                                                                        <td> <?php echo $resPaymentDetails->FOBValueUSD ?>
                                                                                        </td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td>FOB VALUE IN INR</td>
                                                                                        <td>INR</td>
                                                                                        <td> <?php echo $resPaymentDetails->FOBValueINR ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>INVOICE VALUE IN INR</td>
                                                                                        <td>INR</td>
                                                                                        <td><?php echo $resPaymentDetails->InvValueINR ?>
                                                                                        </td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td>IGST @
                                                                                            <?php echo $resPaymentDetails->IgstPerc ?>%
                                                                                        </td>
                                                                                        <td>INR</td>
                                                                                        <td><?php echo $resPaymentDetails->IgstValue ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>TOTAL INVOICE VALUE</td>
                                                                                        <td>INR</td>
                                                                                        <td> <?php echo $resPaymentDetails->TotalInvoiceValueINR ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php } else {
                                                                                    echo '
                                                                                                                <tr>
                                                                                                                    <td>FOB VALUE IN US$</td>
                                                                                                                    <td >$</td>
                                                                                                                    <td > ' . $resPaymentDetails->FOBValueUSD . '</td>
                                                                                                                </tr>
                                                                                                                    <tr>
                                                                                                                        <td>FOB VALUE IN INR</td>
                                                                                                                        <td>INR</td>
                                                                                                                        <td> ' . $resPaymentDetails->FOBValueINR . '</td>
                                                                                                                    </tr>
                                                                                                                    <tr>
                                                                                                                        <td>INVOICE VALUE IN INR</td>
                                                                                                                        <td>INR</td>
                                                                                                                        <td>' . $resPaymentDetails->InvValueINR . '</td>
                                                                                                                    </tr>
                                                                                                                    <!--<tr>
                                                                                                                        <td>TOTAL INVOICE VALUE</td>
                                                                                                                        <td>INR</td>
                                                                                                                        <td> ' . $resPaymentDetails->TotalInvoiceValueINR . '</td>
                                                                                                                    </tr>-->
                                                                                                                ';
                                                                                } ?>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="lh16 font12">
                                                                <div class="mt-1">
                                                                    <div class="underline">SHIPPED UNDER:</div>
                                                                    <div class="f-11" style="line-height: 14px;">
                                                                        <div>1)WE HEREBY DECLARE THAT WE SHALL CLAIM THE
                                                                            BENEFITS UNDER CUSTOMS NOTIFICATION
                                                                            NO.07/2020 DATED:28/01/2020.
                                                                            AS WELL AS CHAPTER 3 OF FTP AS
                                                                            APPLICABLE.FISH AND CRUSTACEANS, MOLLUSCS
                                                                            AND OTHER AQUATIC INVERTEBRATES</div>
                                                                        <div>2) THIS SHIPMENT IS MADE UNDER DUTY DRAW
                                                                            BACK SCHEME AS WELL AS UNDER RoDTEP SCHEME
                                                                        </div>
                                                                        <!-- <div>3) ITC HS CODE:03061720, DRAW BACK SCHEME CODE:19, SL.NO. OF EXPORT PRODUCT:030601, RATE OF ENTITLEMENT:3% ON FOB OR Rs.23.60/KG CAP</div> -->
                                                                        <div>3) ITC HS CODE:03061720, DRAW BACK SCHEME
                                                                            CODE:19, SL.NO. OF EXPORT PRODUCT:030601,
                                                                            RATE OF
                                                                            ENTITLEMENT:<?php echo $resPaymentDetails->DrawbackPercentage; ?>%
                                                                            ON FOB OR Rs.
                                                                            <?php echo $resPaymentDetails->DrawbackRateperKG; ?>/KG
                                                                            CAP
                                                                        </div>
                                                                        <?php if ($result->InvoiceType == "LUT") { ?>
                                                                            <div>4) SUPPLY MENT FOR EXPORT UNDER LETTER OF
                                                                                UNDERTAKING WITHOUT PAYMENT OF IGST LUT NO
                                                                                AD 3703220099171</div>
                                                                        <?php }
                                                                        if ($result->ShowRodTepSchemeDeclaration == "1") { ?>
                                                                            <div class="lh17">
                                                                                <div class="underline">DECLARATION UNDER
                                                                                    RoDTEP SCHEME:</div>
                                                                                <div>We in regard to our claim under RoDTEP
                                                                                    scheme made in this Shipping Bill or
                                                                                    Bill of Export, hereby declare that
                                                                                </div>
                                                                                <div>1. We undertake to abide by the
                                                                                    provisions, including conditions,
                                                                                    restrictions, exclusions and time-limits
                                                                                    as provided under RoDTEP scheme, and
                                                                                    relevant notifications, regulations,
                                                                                    etc., as amended from time to time</div>
                                                                                <div>2. Any claim made in this shipping bill
                                                                                    or bill of export is not with respect to
                                                                                    any duties or taxes or levies which are
                                                                                    exempted or remitted or credited under
                                                                                    any other mechanism outside RoDTEP</div>
                                                                                <div>3. We undertake to preserve and make
                                                                                    available relevant documents relating to
                                                                                    the exported goods for the purposes of
                                                                                    audit in the manner and for the time
                                                                                    period prescribed in the Customs Audit
                                                                                    Regulations, 2018</div>
                                                                            </div>
                                                                        <?php } ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="w100 p-0 text-left lh17">
                                                                <table style="width: 100%">
                                                                    <tr>
                                                                        <td class="p-0" style="width: 70%">
                                                                            <table style="width: 100%">
                                                                                <!-- <tr>
                                                                                                            <td>
                                                                                                                 GST PROVISIONAL ID : 37AAGCC9463J1Z5
                                                                                                            </td>
                                                                                                        </tr>

                                                                                                        <?php // if (isset($result->DriverName) && !empty($result->DriverName)) { ?>
                                                                                                        <tr>
                                                                                                            <td>
                                                                                                            TRANSPORTER NAME: <?php // echo $result->DriverName ?>
                                                                                                         
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                        <?php  // } ?>
                                                                                                       
                                                                                                      <?php //  if (isset($result->VehicleNo) && !empty($result->VehicleNo)) { ?>
                                                                                                        <tr>
                                                                                                            <td>
                                                                                                                 VEHICLE NO: <?php // echo $result->VehicleNo ?>
                                                                                                         
                                                                                                            </td>
                                                                                                        </tr> -->
                                                                                <?php  // } ?>
                                                                                <tr>
                                                                                    <!-- <td>
                                                                                                                <div><?php // echo $result->CIDescription ?></div>
                                                                                                                <div>TRANSPORT FROM PATHARLAGADDA  </div>
                                                                                                                <div>TRANSPORT THE GOODS : FOR EXPORTS THROUGH SEAPORT WITHIN THE COUNTRY</div>
                                                                                                            </td> -->
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>
                                                                                        <?php echo nl2br($result->Remarks) ?>

                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                        <td class="p-0" style="width: 30%">
                                                                            <table style="width: 100%">
                                                                                <!-- <tr>
                                                                                                            <td>
                                                                                                                INVOICE VALUE  INR: <?php // echo $resPaymentDetails->InvValueINR ?>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <td>
                                                                                                                <?php
                                                                                                                // if ($result->QtyUnits == "KGS") {
                                                                                                                // echo 'NET QUANTITY : ' . $result->NetWeightKGS . ' KGS';
                                                                                                                
                                                                                                                // } else {
                                                                                                                //     echo 'NET QUANTITY : ' . $result->NetWeightLBS . ' LBs';
                                                                                                                // }
                                                                                                                ?>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <td>
                                                                                                                <?php
                                                                                                                // if ($result->QtyUnits == "KGS") {
                                                                                                                // echo 'GROSS QUANTITY : ' . $result->GrossWeightKGS . ' KGS';
                                                                                                                // } else {
                                                                                                                //     echo 'GROSS QUANTITY : ' . $result->GrossWeightLBS . ' LBs';
                                                                                                                // }
                                                                                                                ?>
                                                                                                            </td>
                                                                                                        </tr> -->
                                                                            </table>
                                                                        </td>
                                                                    </tr>

                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="w100 bt p-0 text-left">
                                                                <table style="width: 100%">
                                                                    <tr>
                                                                        <td style="width: 50%" class="f-12">

                                                                            <?php echo $resLabels->Declrtn ?> :<br>
                                                                            <?php echo $result->Declearation ?> <br>

                                                                        </td>
                                                                        <td class="br" style="width: 15%;"></td>
                                                                        <td style="width: 35%; text-align: center">
                                                                            <?php echo $result->SignFor ?>
                                                                            <br>
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

                                    </div> <!-- Actual Page Content -->



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