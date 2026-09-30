<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$chckBold = $_SESSION['ClientConfigData']['performa_invoice_remarks_bold'];
$ProformaID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

if (isset($_GET['ID']) && !empty($_GET['ID'])) {

    $ProformaID = trim($_GET['ID']);
    $DocType = 'PI';

    $api = API_PDF_PROFORMA_PRINT;

    $bodyContent = array("ProformaID" => $ProformaID, "DocType" => $DocType);
    $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

    // echo json_encode($resPostProfromaPrint, JSON_PRETTY_PRINT);exit;

    $result = $resPostProfromaPrint->Data;
    $resLabels = $resPostProfromaPrint->Data->Labels;
    $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
    $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
    if ($result->InvoiceDate >= '28-05-2026') {
        if (in_array('WeightKG', $resLableKeys) && !$result->ShowFrozenWeight) {
            $key = array_search('WeightKG', $resLableKeys);
            if ($key !== false) {
                unset($resLableKeys[$key]);
            }
        }
    }
    if ($ProformaID == '1383') {
        if (in_array('WeightKG', $resLableKeys) && !$result->ShowFrozenWeight) {
            $key = array_search('WeightKG', $resLableKeys);
            if ($key !== false) {
                unset($resLableKeys[$key]);
            }
        }
    }
    $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
    $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;
} else {
}

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="<?php echo ROOT_URL ?>assets/css/InvoiceHtmlPdf.css" rel="stylesheet" type="text/css" />

    <style>
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
                                                    <h1 class="page-title">PROFORMA INVOICE</h1>
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
                                                                            <div class="mb-1 d-block">
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
                                                                                        <?php echo $resLabels->PurchseOrderNo ?>:<?php echo $result->PoNumber . ", " . $result->PoDate ?><br>
                                                                                        <?php echo $resLabels->ReferenceNo ?>
                                                                                        :
                                                                                        <?php echo $result->ReferenceNumber ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td class="br bb lh18">
                                                                            <div class="mb-1 d-block f-12">
                                                                                <?php echo $resLabels->Consignee ?>:
                                                                            </div>
                                                                            <div class="bold"
                                                                                style="line-height: 16px;">
                                                                                <?php echo $result->ConsigneeName ?>
                                                                                <br>
                                                                                <?php echo nl2br($result->ConsigneeAddress) ?>
                                                                            </div>
                                                                            <?php if (isset($result->NotifyPartyName) && !empty($result->NotifyPartyName)) { ?>
                                                                                <div
                                                                                    style="border-top: 1px solid #333; padding-top: 4px; padding-left: 2px;">
                                                                                    <div>Also Notify: <br>
                                                                                        <?php echo $result->NotifyPartyName ?>
                                                                                    </div>
                                                                                    <div>
                                                                                        <?php echo nl2br($result->NotifyPartyNameAddress) ?>
                                                                                    </div>
                                                                                </div>
                                                                            <?php } ?>
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
                                                                                    <td class="bb" colspan="2">
                                                                                        <?php echo $resLabels->Manufacturer ?>
                                                                                        <div class="bold"
                                                                                            style="line-height: 16px;">
                                                                                            <?php echo $result->ProcessorName ?>
                                                                                            <br>
                                                                                            <?php echo nl2br($result->ProcessorAddress) ?>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bold  w50">
                                                                                        <?php echo $resLabels->ApprovalNo ?>
                                                                                        <?php echo $result->ApprovalNumber ?>
                                                                                    </td>
                                                                                    <?php if (isset($result->USFDANumber) && !empty($result->USFDANumber)) { ?>
                                                                                        <td class="bold bl bb w50">
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
                                                                            <br>REFRIGERATED CONTAINER<br>
                                                                        </td>
                                                                        <td class="bb">Place of Receipt by pre-carrier
                                                                            <br>ANY INDIAN PORT
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="bb br">
                                                                            <?php echo $resLabels->Vessel ?>
                                                                        </td>
                                                                        <td class=" bb">
                                                                            <?php echo $resLabels->PortOfLoading ?><br>ANY
                                                                            INDIAN PORT
                                                                        </td>
                                                                    </tr>
                                                                    <!-- <tr>
                                                                                                    <td class="bb br"><?php //echo $resLabels->Vessel 
                                                                                                                        ?> / </td>
                                                                                                    <td class="bb">ANY INDIAN PORT</td>
                                                                                                </tr> -->
                                                                    <tr>
                                                                        <td class="bb br">
                                                                            <?php echo $resLabels->PortOfDischrg ?><br>
                                                                            <?php echo $result->PortOfDischarge ?>
                                                                        </td>
                                                                        <td class="bb">
                                                                            <?php echo $resLabels->FinlDestintn ?><br>
                                                                            <?php echo $result->FinalDestination ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="br">
                                                                            Marks & Nos./ <br>
                                                                            <?php echo $resLabels->ContainerNo ?>
                                                                        </td>
                                                                        <td>
                                                                            No. & Kind of Packages<br>
                                                                            <?php echo $resGrandTotal->MCS ?> MASTER
                                                                            CARTONS
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td class="p-0 lh18">
                                                                <table class="w100 ">
                                                                    <tr>
                                                                        <td class="br bb text-center">
                                                                            <?php echo $resLabels->CountryOfOrigin ?>
                                                                            <br> INDIA
                                                                        </td>
                                                                        <td class="bb  text-center">
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
                                                                            <span><?php echo nl2br($result->PaymentTerms) . "<br>";
                                                                                    echo $result->ShippingTerms . " " . $result->ShippingTermsFinalDestination . ' ( ' . $result->DeliveryTerms . ' )' ?></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <span
                                                                                class="f-18 bold"><?php echo $resLabels->ShipmetDt ?>
                                                                                :
                                                                                <?php echo $result->ShipByDate ?></span>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="p-0 tblBody lh17">
                                                    <table class="w100  text-center  f-12">
                                                        <tr class="text-center trBreakUpHeader">

                                                            <?php
                                                            $i = 1;
                                                            $countKey = count($resLableKeys);
                                                            foreach ($resLableKeys as $keyVal) {
                                                                if ($i == 1) {
                                                                    echo '<th class="br bt bb" style="width: 50%">' . $resTableLabels->$keyVal . '</th>';
                                                                } else {
                                                                    $isLast = ($i === $countKey);
                                                                    $class = $isLast ? '' : 'br';
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
                                                                                                    ' . nl2br($ItemsGroup->ItemDescription) . ' <br>';
                                                            echo isset($ItemsGroup->Species) && !empty($ItemsGroup->Species) ? "SPECIES :" . $ItemsGroup->Species . "<br>" : "";
                                                            echo isset($ItemsGroup->PackingStyle) && !empty($ItemsGroup->PackingStyle) ? "PACKING :" . $ItemsGroup->PackingStyle . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" : "";
                                                            echo isset($ItemsGroup->Brand) && !empty($ItemsGroup->Brand) ? "BRAND :" . $ItemsGroup->Brand . "" : "";
                                                            echo '<br><br>
                                                                                                </td>
                                                                                            </tr>';

                                                            $j = 1;
                                                            foreach ($ItemsGroup->ItemsList as $RowItem) {
                                                                $count = count($ItemsGroup->ItemsList);
                                                                if ($count != $j) {
                                                                    echo '<tr>';
                                                                    $s = 1;
                                                                    foreach ($resLableKeys as $key => $keyVal) {
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
                                                                        foreach ($resLableKeys as $key => $keyVal) {
                                                                            if ($a > 1) {
                                                                                $class = ($a === $countKey) ? '' : 'br';
                                                                                echo '<td class="' . $class . ' bt bb">' . $RowItem->$keyVal . '</td>';
                                                                            }
                                                                            $a++;
                                                                        }
                                                                        echo '</tr>';
                                                                    }
                                                                    $s = 1;
                                                                    $s = 1;
                                                                    foreach ($resLableKeys as $key => $keyVal) {
                                                                        if ($s > 1) {
                                                                            $class = ($s === ($countKey + 1)) ? '' : 'br';
                                                                            echo '<td class="' . $class . '" style="height: 10px;"></td>';
                                                                        }
                                                                        $s++;
                                                                    }
                                                                    // echo '<tr style="height: 10px;"></tr>';
                                                                }
                                                                $j++;
                                                            }
                                                            // echo '<tr>';
                                                            // for($i=0 ; $i < $countKey+1 ; $i++){
                                                            //     echo '<td style="height: 10px;"></td>';
                                                            // }
                                                            // echo '</tr>';
                                                            // echo '</br>';
                                                        }
                                                        ?>

                                                        <tr>
                                                            <?php
                                                            echo '<td class="bt br bb">' . $resGrandTotal->Lable . '</td>';
                                                            $x = 1;
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
                                                                <!-- 11-03-2024 durga prasad -->
                                                                <td colspan="<?php echo $countKey - 2; ?>"
                                                                    class="bb bl text-right w25">
                                                                    <?php echo 'Balance Amount Due: ' ?>
                                                                </td>

                                                                <td colspan="2" class="bb  bl text-center w25">
                                                                    <?php echo $result->BalanceAmount; ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>




                                                    </table>
                                                </td>
                                            </tr>
                                            <?php if (isset($result->Remarks) && !empty($result->Remarks)) { ?>
                                                <tr>
                                                    <td class="p-0">
                                                        <table class="w100 tblFooter">
                                                            <tr>
                                                                <td class="bb text-left p-1">
                                                                    <span><b>Remarks:</b></span><br>
                                                                    <?php if ($chckBold) { ?>
                                                                        <b><?php echo nl2br($result->Remarks) ?></b><br>
                                                                    <?php } else { ?>
                                                                        <?php echo nl2br($result->Remarks) ?><br>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td class="p-0">
                                                    <table class="w100 tblFooter">
                                                        <tr>
                                                            <td class="bb text-left p-0">

                                                                <table class="w100 lh18">
                                                                    <tr>
                                                                        <!-- <?php echo nl2br($result->Remarks) ?><br> -->
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
                                                                        //     
                                                                        ?>
                                                                        <!--  <td>-->
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
                                                                        //             
                                                                        ?>
                                                                        <!--  </td>-->
                                                                        <?php //} 
                                                                        ?>

                                                                    </tr>
                                                                </table>
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td colspan="7" class="text-left">Amount chargeable(in words
                                                                ) <br> (<?php echo $result->CurrencyCode ?> :
                                                                <?php echo $result->AmountInWords ?> )
                                                            </td>
                                                        </tr>

                                                        <?php if (isset($result->BankDetails) && !empty($result->BankDetails)) { ?>
                                                            <tr>
                                                                <td colspan="7" class="bold text-left  f-14">
                                                                    <?php echo $resLabels->BankDetails . " " . nl2br($result->BankDetails) ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="7" class="bold text-left   f-14">
                                                                    <?php echo $resLabels->BankRoutionDetails . " " . nl2br($result->BankRoutionDetails) ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="7" class="bold text-left  f-14">
                                                                    <?php echo $resLabels->BankNegotiationDetails . " " . nl2br($result->BankNegotiationDetails) ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>

                                                        <tr>
                                                            <td colspan="7" class="w100 bt p-0 text-left ">
                                                                <table style="width: 100%">
                                                                    <tr>
                                                                        <td style="width: 50%" class="f-12">

                                                                            <!-- <?php echo $result->Remarks ?><br> -->
                                                                            <?php echo $resLabels->Declrtn ?> : <br>
                                                                            <?php echo $result->Declearation ?> <br>
                                                                        </td>
                                                                        <td class="br" style="width: 20%;"></td>
                                                                        <td style="width: 30%; text-align: center">
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