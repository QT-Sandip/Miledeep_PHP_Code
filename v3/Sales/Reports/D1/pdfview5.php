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
    // echo json_encode($resPostProfromaPrint,JSON_PRETTY_PRINT);
    // exit();
} else {
}


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>" rel="stylesheet" type="text/css">

    <style>
        .py2px5 {
            padding: 2px 5px;
        }

        .text-right {
            text-align: right;
        }

        .py0px5 {
            padding: 0px 5px;
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
                                        <button class="generate" id="generate" onclick="downloadpdf('FileName5<?php /* echo str_replace('/', '_', $result->InvoiceNumber) */ ?>.pdf')">Download PDF</button>
                                    </div>

                                    <div id="printTable">
                                        <table class="tblMain">
                                            <tr>
                                                <td>
                                                    &nbsp;
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <!-- <table class="bt bl br bb w100"> -->
                                                    <table class="w100 f-14">
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100">
                                                                    <tr>
                                                                        <td class="w50 lh18 f-18">
                                                                            &nbsp;
                                                                        </td>
                                                                        <td class="w50 lh18 f-15 text-right">
                                                                            <table class="bt br bb bl" style="float: right">
                                                                                <tr>
                                                                                    <td class="bb  py2px5" style="width: 180 ">Purchase Order</td>
                                                                                    <td class="bb br py2px5 text-right" style="width: 180"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br py2px5">Purchase Order No.</td>
                                                                                    <td class="bb br py2px5 text-right"><?php echo $result->PoNumber ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br py2px5">Other Reference No.</td>
                                                                                    <td class="bb br py2px5 text-right"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br py2px5">Date </td>
                                                                                    <td class="bb br py2px5 text-right"><?php echo $result->PoDate?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            CORPORATE HEADQUARTERS <br>
                                                                            <?php echo $result->ConsigneeName;
                                                                            echo '<br>';
                                                                            echo $result->ConsigneeAddress;
                                                                            
                                                                            ?>
                                                                            <!-- CORPORATE HEADQUARTERS <br>
                                                                            634 S.CROCKER STREET <br>
                                                                            LOS ANGELES, CA 90021-1085 -->
                                                                        </td>
                                                                        <td style="vertical-align: bottom;">
                                                                            Trade terms DDP<br>
                                                                            Ship To :                                                                            
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            Vendor : 41242
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div style="margin: 5px 0px;font-weight: bold;width: 420px;border: 1px solid #333;padding: 5px;min-height: 100px">
                                                                            <?php echo $result->ExporterName .'<br>';
                                                                            echo $result->ExporterAddress;  
                                                                            ?>    
                                                                            <!-- DEVI FISHERIES LIMITED<br>
                                                                                6-21-7, EAST POINT COLONY,<br>
                                                                                VISAKHAPATNAM - 530017 <br>
                                                                                A.P., INDIA -->
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div style="margin: 5px 0px;font-weight: bold;width: 420px;border: 1px solid #333;padding: 5px;min-height: 100px">
                                                                            <?php
                                                                             echo $result->ConsigneeName;
                                                                            echo '<br>';
                                                                            echo $result->ConsigneeAddress;
                                                                            
                                                                            ?>    

                                                                            <!-- ORE-CAL CORPORATION<br>
                                                                                634 S.CROCKER STREET<br>
                                                                                LOS ANGELES, CA 90021-1085 -->
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td>
                                                                            <div style="text-decoration: underline;">To Order of Importer of Record</div>
                                                                            <?php 
                                                                            echo $result->ImporterName . ', ';
                                                                            echo $result->PortOfLoading;
                                                                            ?>
                                                                            <!-- <div>DEVI FISHERIES LIMITED, VISAKHAPATNAM, INDIA.</div> -->
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" style="font-size: 13px;">Changes Since the previous Revision</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="p-0" colspan="2">
                                                                            <table class="w-100 bt br bb bl">
                                                                                <tr>
                                                                                    <td style="width: 25%" class="bb br">Shipping Method</td>
                                                                                    <td style="width: 25%" class="bb br">Payment Terms</td>
                                                                                    <td style="width: 25%" class="bb br">Conform With</td>
                                                                                    <td style="width: 25%" class="bb">Page</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br"></td>
                                                                                    <td class="bb br">L/C</td>
                                                                                    <td class="bb br">MAHENDRA</td>
                                                                                    <td class="bb">1</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="p-0" colspan="4">
                                                                                        <table class="w-100 text-center f-13"> 
                                                                                            <tr>
                                                                                                <td class="bb br">L/N Item Number</td>
                                                                                                <td class="bb br">Item Description</td>
                                                                                                <td class="bb br">LB/CS</td>
                                                                                                <td class="bb br">U/M</td>
                                                                                                <td class="bb br">Ordered</td>
                                                                                                <td class="bb br">Site</td>
                                                                                                <td class="bb br"> Unit Price</td>
                                                                                                <td class="bb">Ext. Prrice</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td class="bb br">Required date</td>
                                                                                                <td class="bb br"></td>
                                                                                                <td class="bb br"></td>
                                                                                                <td class="bb br"></td>
                                                                                                <td class="bb br"></td>
                                                                                                <td class="bb br"></td>
                                                                                                <td class="bb br"></td>
                                                                                                <td class="bb"></td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>49391931</td>
                                                                                                <td class="text-left" style="width:240px">16/2 # KIRKLAND SIGNATURE 21/25 FROZEN </td>
                                                                                                <td>32.00</td>
                                                                                                <td>CS</td>
                                                                                                <td>975</td>
                                                                                                <td>LA01</td>
                                                                                                <td>$ 5.50</td>
                                                                                                <td>$ 171,600.00</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>4/30/2023</td>
                                                                                                <td class="text-left" style="width:240px">
                                                                                                    RAW VANNAMEI WHITE PEELEED AND 
                                                                                                    DEVEINED TAIL ON IQF SHRIMPS (SALT TREATRD) 
                                                                                                    32-0000-000-240
                                                                                                </td>
                                                                                                <td>UPC</td>
                                                                                                <td>96619255511</td>
                                                                                                <td colspan="2"> Shipper format</td>                                                                                                
                                                                                                <td colspan="2"> 96619255511</td>
                                                                                            </tr>
                                                                                            <tr><td class="p-0" colspan="8"><div style="border-top: 1px solid #333333">&nbsp;</div></td></tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="4">
                                                                                        <div style="height: 100px; ">&nbsp;</div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="2"  style="height: 150px;padding-left: 35px">
                                                                                        1 FCL /975 CS TOTAL - DDP OAKLAND U.S.A. <br>
                                                                                        SHIP 30TH APRIL 2023
                                                                                    </td>
                                                                                    <td colspan="2">
                                                                                        <div>&nbsp;</div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td  class="bb" colspan="4">
                                                                                        <div style="height: 25px; ">&nbsp;</div>
                                                                                    </td>
                                                                                </tr>
                                                                                
                                                                                
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="p-0">
                                                                            <table class="br bb bl" style="float: right;width: 310px">
                                                                                <tr>
                                                                                    <td class="bb br py2px5" style="width: 180">Subtotal</td>
                                                                                    <td class="bb br py2px5 text-right" style="width: 180">$ 171,600.00</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br py2px5">Trade Discount</td>
                                                                                    <td class="bb br py2px5 text-right"> $ -</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br py2px5">Freight</td>
                                                                                    <td class="bb br py2px5 text-right"> $ -</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br py2px5">Miscellaneous </td>
                                                                                    <td class="bb br py2px5 text-right"> $ -</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br py2px5">Tax </td>
                                                                                    <td class="bb br py2px5 text-right"> $ -</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="bb br py2px5">Order Total</td>
                                                                                    <td class="bb br py2px5 text-right">$ 171,600.00</td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>


                                                                    <tr>
                                                                        <td colspan="2">
                                                                            
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="height: 35px;">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="border-top: 1px solid #333333;max-width: 250px;text-align: center;margin-left: 80px;">Authorised Signatory</div>
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
    // var invNo = "<?php // echo $result->InvoiceNumber 
                    ?>";
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
            callback: function(pdf) {

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

<?php // include_once ROOT . '/includes/footer-print-pdf.php' 
?>

</body>

</html>