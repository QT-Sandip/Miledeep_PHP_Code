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

    // $api = API_PDF_CUSTOM_PRINT;
    $api = API_PDF_PROFORMA_PRINT;
    $DocType = 'CUI';
    
    $bodyContent = array("ProformaID" => $ProformaID, "DocType" => $DocType);

    // $bodyContent = array("ProformaID" => $ProformaID);
    $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

    // var_dump($resPostProfromaPrint);

    $result = $resPostProfromaPrint->Data;
    $resLabels = $resPostProfromaPrint->Data->Labels;
    $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
    $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
    $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
    $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;

    $resPaymentDetails = $resPostProfromaPrint->Data->PaymentDetails;
    // echo json_encode($resPostProfromaPrint, JSON_PRETTY_PRINT);
    // exit();
} else {
}


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>" rel="stylesheet" type="text/css">


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
                                        <button class="generate" id="generate" onclick="downloadpdf('FileName2<?php /* echo str_replace('/', '_', $result->InvoiceNumber) */ ?>.pdf')">Download PDF</button>
                                    </div>

                                    <div id="printTable">
                                        <table class="tblMain">
                                            <tr>
                                                <td>
                                                    <h2 class="text-center" style="font-size: 32px;font-weight: 500;font-style: italic">CHICKEN OF THE SEA FROZENFOODS</h2>
                                                    <p class="text-center" style="font-size: 18px;">2150 EAST GRAND AVENUE, EL SEGUNDO, CA 90245, U.S.A</p>
                                                    <p class="text-center" style="margin-top: 15px;font-size: 20px;">PURCHASE ORDER</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table class="bt bl br bb w100">
                                                    <!-- <table class="w100"> -->
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100">
                                                                    <tr style="border-bottom: 1px solid #333;">
                                                                        <td class="w50 lh18">
                                                                            <div class="f-14 mb-1 d-block"><b>PO. NO : <?php echo $result->PoNumber ?></b></div>
                                                                        </td>
                                                                        <td style="text-align: right;">
                                                                            <div class="f-14 mb-1 d-block">PO Date : <?php echo date('d.m.Y', strtotime($result->PoDate)) ?>.</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="border-bottom: 1px solid #333;">
                                                                        <td colspan="2">
                                                                            <span style="border-bottom: 1px solid #333;font-weight: 800;">Exporter/ Shipper </span>
                                                                            <div style="padding-top: 6px;">
                                                                                <?php echo $result->ExporterName ?><br>
                                                                                <?php echo nl2br($result->ExporterAddress) ?>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="border-bottom: 1px solid #333;">
                                                                        <td colspan="2">
                                                                            <span style="border-bottom: 1px solid #333;font-weight: 800;">Processed/ Packed by:</span>
                                                                            <div style="padding-top: 6px;">
                                                                                <?php echo $result->ProcessorName ?><br>
                                                                                <?php echo nl2br($result->ProcessorAddress) ?><br>
                                                                            <span style="font-weight: 800;">E.I.C. APPROVAL NO. <?php echo $result->ApprovalNumber ?></span> 
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        
                                                                        <td>
                                                                            <span style="border-bottom: 1px solid #333;font-weight: 800;">Notify Party:</span><br>
                                                                            <div>
                                                                                <?php echo $result->NotifyPartyName; ?><br>
                                                                                <?php echo nl2br($result->NotifyPartyNameAddress) ?><br>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <table>
                                                                                <tr>
                                                                                    <td class="" style="text-align: right;padding: 0 5px"><b>Ship by :</b></td>
                                                                                    <td style="padding: 0 5px"><?php echo $result->ShipByDate ?>.</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="text-align: right;padding: 0 5px"><b>Carrier :</b></td>
                                                                                    <td style="padding: 0 5px"><?php echo $result->Carrier ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="text-align: right;padding: 0 5px"><b>Port of Loading :</b></td>
                                                                                    <td style="padding: 0 5px"><?php echo $result->PortOfLoading ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="text-align: right;padding: 0 5px"><b>Port of Discharge :</b></td>
                                                                                    <td style="padding: 0 5px"><?php echo $result->PortOfDischarge ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="text-align: right;padding: 0 5px"><b>Place of Delivery :</b></td>
                                                                                    <td style="padding: 0 5px"><?php echo $result->FinalDestination ?></td>
                                                                                </tr>
                                                                            </table>
                                                                           

                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="p-0">
                                                                            <table class="w-100" style="min-height: 150px;">
                                                                                <tr>
                                                                                    <!-- <td class="br bt bb text-center" style="width: 50px;">SL. #</td> -->
                                                                                    <?php
                                                                                        $i = 1;
                                                                                        // $countKey = count($resLableKeys);
                                                                                        foreach ($resLableKeys as $keyVal) {
                                                                                            // if ($i == 1) {
                                                                                                echo '<td class="br bt bb text-center"><b>' . $resTableLabels->$keyVal . '</b></td>';
                                                                                            // }
                                                                                            $i++;
                                                                                        }
                                                                                    ?>
                                                                                </tr>
                                                                                <!-- <tr>
                                                                                    <td class="br text-center">1</td> -->
                                                                                    <?php
                                                                                        $a = 1;
                                                                                        foreach ($resItemsGroup as $ItemsGroup) {
                                                                                            
                                                                                            if ($ItemsGroup->ShowSubTotal == true) {
                                                                                                $rowspan = count($ItemsGroup->ItemsList) + 1;
                                                                                            } else {
                                                                                                $rowspan = count($ItemsGroup->ItemsList);
                                                                                            }
                                                                                            echo '
                                                                                            <tr>
                                                                                                <td rowspan="' . $rowspan . '" class="text-left br">
                                                                                                    '.$a.") &nbsp;". $ItemsGroup->ItemDescription . ' <br>';
                                                                                                echo isset($ItemsGroup->Species) && !empty($ItemsGroup->Species) ? "SPECIES :" . $ItemsGroup->Species . "<br>" : "";
                                                                                                // echo isset($ItemsGroup->PackingStyle) && !empty($ItemsGroup->PackingStyle) ? "PACKING :" . $ItemsGroup->PackingStyle . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" : "";
                                                                                                echo isset($ItemsGroup->Brand) && !empty($ItemsGroup->Brand) ? "BRAND :" . $ItemsGroup->Brand . "" : "";
                                                                                                echo '<br><br>
                                                                                                </td>
                                                                                            </tr>';

                                                                                            $j = 1;
                                                                                            foreach ($ItemsGroup->ItemsList as $RowItem) {
                                                                                                $count = count($ItemsGroup->ItemsList);
                                                                                                if ($count != $j) {
                                                                                                    echo '<tr class="text-center">';
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
                                                                                            $a++;

                                                                                        }
                                                                                    ?>

                                                                                    <tr>
                                                                                        <?php
                                                                                        echo '<td class="bt br text-center"><b>' . $resGrandTotal->Lable . '</b></td>';
                                                                                        $x = 1;
                                                                                        $countKey = count($resLableKeys);
                                                                                        foreach ($resLableKeys as $key => $keyVal) {
                                                                                            if ($x > 1) {
                                                                                                $class = ($x === $countKey) ? '' : 'br';
                                                                                                echo '<td class="' . $class . ' bt"><b>' . $resGrandTotal->$keyVal . '</b></td>';
                                                                                            }
                                                                                            $x++;
                                                                                        }
                                                                                        ?>
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
                                            <tr>
                                                <td>NOTE:</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-left: 15px;">1 PAYMENT TERMS : <?php echo $result->PaymentTerms ?>.</td>
                                            </tr>
                                            <tr>
                                                <td style="height: 35px;">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div style="text-align: right;">Authorised Signatory</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height: 35px;">&nbsp;</td>
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
   // var invNo = "<?php // echo $result->InvoiceNumber ?>";
</script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script> 
window.jsPDF = window.jspdf.jsPDF;
var pdf = new jsPDF({
    orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
    unit: 'px', // Measurement unit
    format: 'a4', // Page size format, 'a4' for A4 size
    
});

function downloadpdf(fileName) {
    var pWidth = pdf.internal.pageSize.getWidth(); // Page width
    var pHeight = pdf.internal.pageSize.getHeight(); // Page height

    // Define the HTML element you want to capture (e.g., a table with id "printTable")
    var element = document.getElementById("printTable");

    // Adjust the width of the HTML content to match the PDF page width
    // element.style.width = pWidth + "px"; // Set the width in points

    // Calculate the adjusted height to maintain the original aspect ratio
    var aspectRatio = element.offsetWidth / element.offsetHeight;
    var adjustedHeight = pWidth / aspectRatio;

    // Calculate the margins (adjust as needed)
    var marginLeft = 50;
    var marginTop = 30;
    var marginRight = 50;
    var marginBottom = 30;

    var contentWidth = element.offsetWidth - marginLeft - marginRight;
    console.log(contentWidth);
    var contentHeight = element.offsetHeight - marginTop - marginBottom;

    // Calculate the x and y position to center the content within the PDF page
    var x = 0; // Left-aligned content

    pdf.html(element, {
        html2canvas: {
            scale: 0.43,
            width: contentWidth,
            height: contentHeight,
            removeContainer: false // for analyzing iframe
        },
        x: x,
        margin: [15, 0, 30, 0], // Adds the margin to the PDF view 
        autoPaging: 'text',
        callback: function (pdf) {
            
            var footerHeight = 20; // Height of the footer
            var gap = 10; // Gap between content and footer

            // for (var i = 1; i <= pdf.internal.getNumberOfPages(); i++) {
            //     pdf.setPage(i);
            //     pdf.setFontSize(12);

            //     // Calculate the Y position for the footer
            //     var footerY = pHeight - marginBottom + gap;

            //     // Calculate the Y position for the page number text
            //     var pageNumberY = footerY + (footerHeight / 2);

            //     // pdf.setDrawColor(0); // Set border color to black
            //     // // pdf.line(margin, footerY, pWidth - margin, footerY); // Draw border at the top of the footer
            //     // pdf.line(1, 1, 1, 1); // Draw border at the top of the footer


            //     pdf.text(
            //         pWidth / 2,
            //         pageNumberY,
            //        'Page ' + i + ' of ' + pdf.internal.getNumberOfPages() + " - " + invNo,
            //         null,
            //         null,
            //         'center'
            //     );
            // }

            var timestamp = Date.now();
            var pdfName = fileName;
            pdf.save(pdfName);
        }
    });
}

</script> 

<script>
    $(document).ready(function() {
        // Check if the user agent string contains "Android" or "iOS"
        if (navigator.userAgent.match(/Android/i)) {
            // For Android devices, set a specific viewport meta tag
            $('meta[name=viewport]').attr('content', 'width=1150');
        } else if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
            // For iOS devices, set a different viewport meta tag
            $('meta[name=viewport]').attr('content', 'width=1150');
        }
    });
</script>

<?php // include_once ROOT . '/includes/footer-print-pdf.php' ?>

</body>

</html>