<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ProformaID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

if (isset($_GET['ID']) && !empty($_GET['ID'])) {

    $ProformaID = trim($_GET['ID']);
    $DocType = 'CMI';

    $api = API_PDF_COMMERCIAL_PRINT;

    $bodyContent = array("ProformaID" => $ProformaID, "DocType" => $DocType);
    $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

    // var_dump($resPostProfromaPrint);

    $result = $resPostProfromaPrint->Data;
    //  var_dump($result);exit;
    $resLabels = $resPostProfromaPrint->Data->Labels;
    $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
    $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
    $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
    $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;
    $resPaymentDetails = $resPostProfromaPrint->Data->PaymentDetails;

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
        .p-1-5 {
            padding: 1px 5px !important;
        }

        td {
            padding: 1px 3px;
        }

        .f-11 {
            font-size: 11px;
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
                                                    <h1 class="page-title">COMMERCIAL INVOICE</h1>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table class="bt bl br bb w100 f-13">
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100  ">
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
                                                                                        <div class="bold">
                                                                                            <?php echo $result->ConsigneeName ?>
                                                                                            <br>
                                                                                            <?php echo nl2br($result->ConsigneeAddress) ?>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                <?php if (isset($result->NotifyPartyName) && !empty($result->NotifyPartyName)) { ?>
                                                                                    <tr>
                                                                                        <td class="bt">
                                                                                            <span
                                                                                                class="mb-1 d-block f-12"><?php echo "ALSO NOTIFY" ?>:</span>
                                                                                            <span class="bold">
                                                                                                <?php echo $result->NotifyPartyName ?>
                                                                                                <br>
                                                                                                <?php echo nl2br($result->NotifyPartyNameAddress) ?>
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php } ?>

                                                                            </table>
                                                                        </td>

                                                                        <td class="p-0 lh18">
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
                                                                                    <td class="bb" colspan="2">
                                                                                        <?php echo $resLabels->Manufacturer ?>
                                                                                        <div class="bold">
                                                                                            <?php echo $result->ProcessorName ?>
                                                                                            <br>
                                                                                            <?php echo nl2br($result->ProcessorAddress) ?>
                                                                                        </div>
                                                                                        <!-- <div>
                                                                                                             <?php // echo $resLabels->ApprovalNo ?>  <?php // echo $result->ApprovalNumber ?> 
                                                                                                        </div>
                                                                                                        <?php //if (isset($result->USFDANumber) && !empty($result->USFDANumber)) { ?>
                                                                                                                <div class="bold">
                                                                                                                    <?php //echo $resLabels->UsfdaRegistrationNo ?>                      <?php //echo $result->USFDANumber ?>   
                                                                                                            </td>
                                                                                                        <?php //} ?> -->

                                                                                    </td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td class="bold bb w50">
                                                                                        <?php echo $resLabels->ApprovalNo ?>
                                                                                        <?php echo $result->ApprovalNumber ?>
                                                                                    </td>
                                                                                    <?php if (isset($result->USFDANumber) && !empty($result->USFDANumber)) { ?>
                                                                                        <td class="bold bb bl w50">
                                                                                            <?php echo $resLabels->UsfdaRegistrationNo ?>
                                                                                            <?php echo $result->USFDANumber ?>
                                                                                        </td>
                                                                            </td>
                                                                        <?php } ?>
                                                                    </tr>

                                                                    <?php if (isset($result->ImporterName) && !empty($result->ImporterName)) { ?>
                                                                        <tr>
                                                                            <td colspan="2" class="bt  w50">
                                                                                <span
                                                                                    class="mb-1 d-block"><?php echo "IMPORTER ON RECORD" ?>:
                                                                                    <span
                                                                                        class="bold"><?php echo $result->ImporterName ?></span></span>
                                                                                <span class="bold">
                                                                                    <?php echo nl2br($result->ImporterAddress) ?>
                                                                                </span>
                                                                            </td>

                                                                            <!-- <td class="bt bb w50">
                                                                                                           
                                                                                                                </td> -->
                                                                        </tr>
                                                                    <?php } ?>


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
                                                                        <td class="bb">Place of Receipt by
                                                                            pre-carrier<br><?php echo $result->PortOfLoading ?>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="bb br">
                                                                            <?php echo $resLabels->Vessel ?> :<br>
                                                                            <?php echo $result->Vessel ?>
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
                                                                            <?php if (isset($result->LinearSealNo) && !empty($result->LinearSealNo)) { ?>
                                                                                <div class="d-block f-12">SEAL NO:
                                                                                    <?php echo $result->LinearSealNo ?>
                                                                                </div>
                                                                            <?php } ?>
                                                                        </td>
                                                                        <td class="bb">
                                                                            No. & Kind of Packages<br>
                                                                            <!-- <div class="bold"><?php // echo $resGrandTotal->MCS; ?> MASTER CARTONS</div>   -->
                                                                            <div>
                                                                                <b><?php echo $resGrandTotal->MCS; ?></b>
                                                                                MASTER CARTONS
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <?php // if (isset($result->LinearSealNo) && !empty($result->LinearSealNo)) { ?>
                                                                    <!--<tr>-->
                                                                    <!--<td class="f-12" colspan="2"> SEAL NO: <?php // echo $result->LinearSealNo ?></td>-->
                                                                    <!-- <td class="f-12">CUSTOMS seal: <?php // echo $result->CustomSealNo ?></td> -->
                                                                    <!--</tr> -->
                                                                    <?php // } ?>

                                                                    <?php

                                                                    // if ((isset($result->BLNo) && !empty($result->BLNo)) || (isset($result->BLDate) && !empty($result->BLDate))) {
                                                                    //     echo '
                                                                    //     <tr> ';
                                                                    //     if (isset($result->BLNo) && !empty($result->BLNo)) {
                                                                    //         if (!isset($result->BLDate) || empty($result->BLDate)) {
                                                                    //             $blD = "";
                                                                    //             $colspan = "colspan='2'";
                                                                    //         } else {
                                                                    //             $blD = "br";
                                                                    //         }
                                                                    //         echo '<td class="' . $blD . ' bt f-12" ' . $colspan . '>BL No: ' . $result->BLNo . '</td>';
                                                                    //     }
                                                                    //     if (isset($result->BLDate) && !empty($result->BLDate)) {
                                                                    //         if (!isset($result->BLNo) || empty($result->BLNo)) {
                                                                    //             $blD = "";
                                                                    //             $colspan = "colspan='2'";
                                                                    //         } else {
                                                                    //             $blD = "";
                                                                    //         }
                                                                    //         echo '<td class="' . $blD . ' bt f-12" ' . $colspan . '>BL Date: ' . $result->BLDate . '</td>';
                                                                    //     }
                                                                    //     echo '      
                                                                    //    </tr>';
                                                                    // }
                                                                    
                                                                    // if (isset($resLabels->ShippingBillNo) && !empty($resLabels->ShippingBillNo)) {
                                                                    //     echo '
                                                                    //         <tr>
                                                                    //             <td class="bt f-12" colspan="2">' . $resLabels->ShippingBillNo . ': ' . $result->ShippingBillNo . '</td>
                                                                    //              <!-- <td class="f-12 bt">BL Date: '. $result->CustomSealNo .'</td> -->
                                                                    //          </tr>';
                                                                    // }
                                                                    ?>

                                                                </table>
                                                            </td>
                                                            <td class="p-0 lh18">
                                                                <table class="w100">
                                                                    <tr>
                                                                        <td class="br bb bt w50 text-center">
                                                                            <?php echo $resLabels->CountryOfOrigin ?>
                                                                            <br> INDIA
                                                                        </td>
                                                                        <td class="bb bt w50 text-center">
                                                                            <?php echo $resLabels->FinalDestintnCountry ?>
                                                                            <br>
                                                                            <?php echo $result->CountryOfDestination ?>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td colspan="2" class="bb">
                                                                            <p class="bold mb-2">
                                                                                <?php echo $resLabels->PaymentTerms ?>
                                                                            </p>
                                                                            <span>
                                                                                <?php

                                                                                echo $resLabels->ShippingTerm . ' ' . $result->ShippingTerms . " ";
                                                                                echo $result->ShippingTermsFinalDestination . '(' . $result->DeliveryTerms . ')' . "<br>";
                                                                                echo nl2br($result->PaymentTerms) . "<br>";

                                                                                if (isset($result->LcNo) && !empty($result->LcNo)) {
                                                                                    echo "LC No : " . $result->LcNo;
                                                                                }
                                                                                if (isset($result->LcDate) && !empty($result->LcDate)) {
                                                                                    echo " &nbsp;&nbsp;DT : " . $result->LcDate . "<br>";
                                                                                }
                                                                                ?>
                                                                            </span>
                                                                            <br>

                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <?php
                                                                            if ((isset($result->BLNo) && !empty($result->BLNo)) || (isset($result->BLDate) && !empty($result->BLDate))) {
                                                                                if (isset($result->BLNo) && !empty($result->BLNo)) {
                                                                                    echo 'BL No: ' . $result->BLNo;
                                                                                }
                                                                                if (isset($result->BLDate) && !empty($result->BLDate)) {
                                                                                    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                                                    echo ' BL Date: ' . $result->BLDate;
                                                                                }
                                                                            }
                                                                            ?>

                                                                        </td>
                                                                    </tr>
                                                                    <?php
                                                                    if (isset($resLabels->ShippingBillNo) && !empty($resLabels->ShippingBillNo)) {
                                                                        echo '
                                                                                                            <tr>
                                                                                                                <td  colspan="2">' . $resLabels->ShippingBillNo . ': ' . $result->ShippingBillNo . '</td>
                                                                                                                <!-- <td class="f-12 bt">BL Date: <?php //echo $result->CustomSealNo ?></td> -->
                                                                                                            </tr>';
                                                                    }
                                                                    ?>
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
                                                    <table class="w100  text-center  f-12">
                                                        <tr class="text-center  f-12 trBreakUpHeader">

                                                            <?php
                                                            $i = 1;
                                                            $countKey = count($resLableKeys);
                                                            foreach ($resLableKeys as $keyVal) {
                                                                if ($i == 1) {
                                                                    echo '<th class="br bb" style="width: 50%">' . $resTableLabels->$keyVal . '</th>';
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
                                                        <?php if (isset($result->AdvanceAmount) && !empty($result->AdvanceAmount)) { ?>

                                                            <tr>
                                                                <td class="  text-left "> </td>

                                                                <td colspan="<?php echo $countKey - 2; ?>"
                                                                    class="bb bl text-right w25">
                                                                    <?php echo 'LESS ADVANCE RECEIVED : ' ?>
                                                                </td>

                                                                <td class="bb bl text-right w25">
                                                                    <?php echo $result->AdvanceAmount; ?>
                                                                </td>

                                                            </tr>
                                                        <?php } ?>

                                                        <?php if (isset($result->ChargeAmount) && !empty($result->ChargeAmount) && ($result->ChargeAmount) != '0') { ?>
                                                            <tr>
                                                                <td class="  text-left "> </td>

                                                                <td colspan="<?php echo $countKey - 2; ?>"
                                                                    class="bb bl text-right w25">
                                                                    <?php echo $result->ChargeName; ?>
                                                                </td>

                                                                <td class="bb bl text-right w25">
                                                                    <?php echo $result->ChargeAmount; ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>

                                                        <?php if ((isset($result->ChargeAmount) && !empty($result->ChargeAmount) && ($result->ChargeAmount) != '0') || (isset($result->AdvanceAmount) && !empty($result->AdvanceAmount))) { ?>

                                                            <tr>
                                                                <td class="  text-left "> </td>

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
                                                            <td class="bb  text-left p-0">
                                                                <table class="w100 lh18">

                                                                    <?php
                                                                    echo '' . nl2br($result->ComDescriptionSpl) . '';

                                                                    // if ($result->QtyUnits == "KGS") {
                                                                    // echo nl2br($result->ComDescriptionSpl);
                                                                    
                                                                    ?>

                                                                    <tr>



                                                                        <td class="bt text-left w50">

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

                                                                        <td class="bt text-right w50">
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
                                                                        //   }
                                                                        
                                                                        // echo '<td class="br p-0" style="width: 0px;"></td>'; 
                                                                        
                                                                        // if ($result->QtyUnits != "KGS") {
                                                                        //     ?>
                                                                        <!--  <td>-->
                                                                        <?php
                                                                        //                 if (isset($result->NetWeightLBS) && !empty($result->NetWeightLBS)) {
                                                                        //                     echo '<div style="margin-right: 43px;">Net Weight:  ' . $result->NetWeightLBS . ' LBs </div>';
                                                                        //                 }
                                                                        //                 if (isset($result->FWeightLbs) && !empty($result->FWeightLbs)) {
                                                                        //                     echo '<div style="margin-right: 43px;">Frozen Weight:  ' . $result->FWeightLbs . ' LBs </div>';
                                                                        //                 }
                                                                        //                 if (isset($result->GrossWeightLBS) && !empty($result->GrossWeightLBS)) {
                                                                        //                     echo '<div style="margin-right: 25px;">Gross Weight:  ' . $result->GrossWeightLBS . ' LBs </div>';
                                                                        //                 }
                                                                        ?>
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
                                                                        <td class="text-left w50">
                                                                            <!-- <div class="f-16">NCPTI-NO PTA/FTA</div> -->
                                                                            <table class="w100 tbl-py-0">
                                                                                <?php if (isset($result->BankDetails) && !empty($result->BankDetails)) { ?>
                                                                                    <tr>
                                                                                        <td colspan="7"
                                                                                            class="bold text-left lh17"
                                                                                            style="padding-bottom: 5px;">
                                                                                            <?php echo $resLabels->BankDetails . " " . nl2br($result->BankDetails) ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td colspan="7"
                                                                                            class="bold text-left lh17"
                                                                                            style="padding-bottom: 5px;">
                                                                                            <?php echo $resLabels->BankRoutionDetails . " " . nl2br($result->BankRoutionDetails) ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td colspan="7"
                                                                                            class="bold text-left lh17"
                                                                                            style="padding-bottom: 5px;">
                                                                                            <?php echo $resLabels->BankNegotiationDetails . " " . nl2br($result->BankNegotiationDetails) ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php } ?>
                                                                            </table>
                                                                        </td>

                                                                        <td class="p-0 w50">
                                                                            <table class="w100 tbl-py-0">
                                                                                <?php if ($result->ShowAntiDumping == 1) { ?>
                                                                                    <tr>
                                                                                        <td class="bl p-1-5">Invoice value
                                                                                        </td>
                                                                                        <td class="p-1-5">$</td>
                                                                                        <td class="p-1-5">
                                                                                            <?php echo $resPaymentDetails->InvValueUSD ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="bl p-1-5">Less: Freight
                                                                                        </td>
                                                                                        <td class="p-1-5">$</td>
                                                                                        <td class="p-1-5">
                                                                                            <?php echo $resPaymentDetails->FrieghtCharge ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="bl p-1-5"></td>
                                                                                        <td class="p-1-5">$</td>
                                                                                        <td class="p-1-5">
                                                                                            <?php echo $resPaymentDetails->InvValueUSDWithOutDuties ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <!-- <tr>
                                                                                                                                <td class="bl p-1-5">Less : ADD@1.35% + Port Charges@0.47%</td>
                                                                                                                                <td class="p-1-5">$</td>
                                                                                                                                <td class="p-1-5"><?php //echo $resPaymentDetails->DutiesPrice ?></td>
                                                                                                                            </tr> -->
                                                                                    <?php
                                                                                    $rtClassBorder = 'bb';
                                                                                    if ($resPaymentDetails->ReciprocalTariffsPerc != 0) {
                                                                                        $rtClassBorder = '';
                                                                                    }
                                                                                    if ($resPaymentDetails->CountervailingDutyPerc != 0) { ?>
                                                                                        <tr>
                                                                                            <td class="bl">Less :
                                                                                                ADD@<?php echo $resPaymentDetails->AntiDumpingDutyPerc ?>%
                                                                                                + Port
                                                                                                Charges@<?php echo $resPaymentDetails->HarbourMaintenanceFeePerc + $resPaymentDetails->MerchandiseProcessFeePerc ?>%
                                                                                            </td>
                                                                                            <td class="p-1-5">$</td>
                                                                                            <!-- <td ><?php //echo $resPaymentDetails->DutiesPrice ?></td> -->
                                                                                            <td class="p-1-5">
                                                                                                <?php echo $resPaymentDetails->AntidumpingandPortChargesPrice ?>
                                                                                            </td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td
                                                                                                class="bl <?php echo $rtClassBorder; ?>">
                                                                                                Less :
                                                                                                CVD@<?php echo $resPaymentDetails->CountervailingDutyPerc ?>
                                                                                                %</td>
                                                                                            <td
                                                                                                class="p-1-5 <?php echo $rtClassBorder; ?>">
                                                                                                $</td>
                                                                                            <td
                                                                                                class="p-1-5 <?php echo $rtClassBorder; ?>">
                                                                                                <?php echo $resPaymentDetails->CountervailingDuty ?>
                                                                                            </td>
                                                                                        </tr>

                                                                                        <?php if ($resPaymentDetails->ReciprocalTariffsPerc != 0) { ?>

                                                                                            <tr>
                                                                                                <td class="bl bb">Less :
                                                                                                    RECIPROCAL
                                                                                                    TARIFF@<?php echo $resPaymentDetails->ReciprocalTariffsPerc ?>
                                                                                                    %</td>
                                                                                                <td class="p-1-5 bb">$</td>
                                                                                                <td class="p-1-5 bb">
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
                                                                                            <td class="p-1-5 bb">$</td>
                                                                                            <!-- <td ><?php //echo $resPaymentDetails->DutiesPrice ?></td> -->
                                                                                            <td class="p-1-5 bb">
                                                                                                <?php echo $resPaymentDetails->AntidumpingandPortChargesPrice ?>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php } ?>
                                                                                    <tr>
                                                                                        <td class="bl bb p-1-5">FOB Value in
                                                                                            US$</td>
                                                                                        <td class="p-1-5 bb">$</td>
                                                                                        <td class="p-1-5 bb">
                                                                                            <?php echo $resPaymentDetails->FOBValueUSD ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php } ?>
                                                                                <tr>
                                                                                    <td>&nbsp;</td>
                                                                                    <td></td>
                                                                                    <td></td>
                                                                                </tr>
                                                                                <!-- <?php //if ($result->InvoiceType == "IGST") { ?>
                                                                                                                        <tr>
                                                                                                                            <td>FOB Value in INR</td>
                                                                                                                            <td>INR</td>
                                                                                                                            <td> <?php //echo $resPaymentDetails->FOBValueINR ?></td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td>INVOICE VALUE IN INR</td>
                                                                                                                            <td>INR</td>
                                                                                                                            <td><?php //echo $resPaymentDetails->InvValueINR ?></td>
                                                                                                                        </tr>
                                                                                                                
                                                                                                                        <tr>
                                                                                                                            <td>IGST @ <?php //echo $resPaymentDetails->IgstPerc ?>%</td>
                                                                                                                            <td>INR</td>
                                                                                                                            <td><?php //echo $resPaymentDetails->IgstValue ?></td>
                                                                                                                        </tr>
                                                                                                                        <?php // if(isset($resPaymentDetails->TotalInvoiceValueINR) && !empty($resPaymentDetails->TotalInvoiceValueINR)) { ?>
                                                                                                                        <tr>
                                                                                                                            <td>TOTAL INVOICE VALUE</td>
                                                                                                                            <td>INR</td>
                                                                                                                            <td> <?php //echo $resPaymentDetails->TotalInvoiceValueINR ?></td>
                                                                                                                        </tr>
                                                                                                                        <?php // } ?>
                                                                                                            <?php ?>-->
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr> <?php if ($result->ShowEUSelfCertification == 1) { ?>
                                                                <td class="w100 bt p-0 text-left lh17">
                                                                    <div>
                                                                        <div align="center"
                                                                            style="text-decoration: underline">STATEMENT ON
                                                                            ORIGIN </div>
                                                                        <div align="center">(Under the EU GSP Self -
                                                                            Certification)</div>
                                                                        <div>&nbsp;</div>
                                                                        <div>WE COASTAL AQUA PRIVATE LIMITED, 68-6-7/1,
                                                                            ASHOK NAGAR, KAKINADA - 533003, ANDHRA PRADESH,
                                                                            INDIA - HAVING REX Reg No.
                                                                            INREX2611000417MP010 OF THE PRODUCTS COVERED BY
                                                                            THIS DOCUMENT DECLARES THAT , EXCEPT WHERE
                                                                            OTHERWISE CLEARLY INDICATED,
                                                                            THESE PRODUCTS ARE OF INDIAN PREFERENTIAL ORIGIN
                                                                            "P" ACCORDING TO RULES OF ORIGIN OF THE
                                                                            GENERALISED SYSTEM OF PREFERENCES
                                                                            OF THE EUROPEAN UNION AND THE ORIGIN CRITERION
                                                                            MET IS "P"</div>

                                                                        <div>HS CODE -030617</div>

                                                                        <!-- <div class="mt-1"><?php //echo nl2br($result->Remarks) ?></div> -->
                                                                    </div>
                                                                </td>
                                                            <?php } ?>
                                                        </tr>

                                                        <tr>
                                                            <td class="w100 bt p-0 text-left">
                                                                <table style="width: 100%">
                                                                    <tr>
                                                                        <td class="br f-12" style="width: 60%">
                                                                            <?php echo $resLabels->Declrtn ?> :<br>
                                                                            <?php echo $result->Declearation ?>
                                                                        </td>
                                                                        <td style="width: 40%; text-align: center">
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