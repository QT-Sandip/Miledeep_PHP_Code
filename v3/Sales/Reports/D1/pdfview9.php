<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// echo $_SESSION["Token"];

// $ProformaID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

// if (isset($_GET['ID']) && !empty($_GET['ID'])) {

//     $ProformaID = trim($_GET['ID']);

//     $api = API_PDF_CUSTOM_PRINT;

//     $bodyContent = array("ProformaID" => $ProformaID);
//     $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

//     // var_dump($resPostProfromaPrint);

//     $result = $resPostProfromaPrint->Data;
//     $resLabels = $resPostProfromaPrint->Data->Labels;
//     $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
//     $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
//     $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
//     $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;

//     $resPaymentDetails = $resPostProfromaPrint->Data->PaymentDetails;
// } else {
// }


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
                                        <button class="generate" id="generate" onclick="downloadpdf('FileName9<?php /* echo str_replace('/', '_', $result->InvoiceNumber) */ ?>.pdf')">Download PDF</button>
                                    </div>

                                    <div id="printTable">
                                        <table class="tblMain">
                                            <tr>
                                                <td>
                                                    <h2 class="text-center" style="font-size: 26px;font-weight: 500;margin-bottom: 30px;text-decoration: underline;">BOHERASH FOODS LIMITED</h2>
                                                    <p class="text-center" style="font-size: 20px;text-decoration: underline;">PURCHASE ORDER</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <!-- <table class="bt bl br bb w100"> -->
                                                    <table class="w100 f-17">
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100">
                                                                    <tr>
                                                                        <td colspan="2" class="w50 lh18 f-18">
                                                                            Date : 20/04/2023

                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class=" f-18">
                                                                            PO : TFFL 24
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px;">We confirm having purchased the following from you on behalf of our principals:</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px;font-weight: bold;">FROZEN RAW VANNAMEI WHITE PEELED AND DEVEINED TAIL OFF IQF SHRIMPS – 6 X 800 GMS,</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <table>
                                                                                <tr>
                                                                                    <td>Gade </td>
                                                                                    <td>Cases </td>
                                                                                    <td> Price – Usd/Kg</td>
                                                                                    <td>(BRAND : LITTLE TIGER) (NWNC )(BPT)</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>21/25 </td>
                                                                                    <td> 400 </td>
                                                                                    <td> 10.80</td>
                                                                                    <td>- $ 20,736.00</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>26/30</td>
                                                                                    <td>2300</td>
                                                                                    <td> 9.80</td>
                                                                                    <td> - $ 108,192.00</td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px;font-weight: bold;">FROZEN RAW HEADLESS SHELL ON VANNAMEI WHITE IQF SHRIMPS – 6 X 800 GMS</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <table>
                                                                                <tr>
                                                                                    <td>Gade </td>
                                                                                    <td>Cases </td>
                                                                                    <td> Price – Usd/Kg</td>
                                                                                    <td>(BRAND : LITTLE TIGER)( NWNC) (BPT)</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>26/30 </td>
                                                                                    <td> 300 </td>
                                                                                    <td>8.90</td>
                                                                                    <td>- $ 12,816.00</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>31/40</td>
                                                                                    <td>300</td>
                                                                                    <td>7.90</td>
                                                                                    <td> - $ 11,376.00</td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px">Total Value - $ 153,120.00</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px">Total No. of cartons – 3300 cartons</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px">Prices – PER KG CFR IRELAND</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px">Destination – DUBLIN, IRELAND</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px">Latest shipment date – 20TH JUNE 2023.</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px">Origin - India.</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px"><span style="font-weight: bold;">Payment:</span> BY LC 75 Days From BL Date. LC FROM EITHER BUYER OR IMPORTER</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px"><span style="font-weight: bold;text-decoration: underline;">Consignee:</span> BOHERASH FOODS LIMITED, NEWTWOPOT HOUSE, BUTTEVANT, CO. CORK, IRELAND.</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin: 5px 0px"><span style="font-weight: bold;text-decoration: underline;">Supplier:</span> Devi Fisheries Limited, 6-21-7, East Point Colony, Visakhapatnam – 530 017, Andhra Pradesh, India.</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><div style="margin-top: 35px">For BOHERASH FOODS LIMITED</div></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="height: 35px;">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div>Authorised Signatory</div>
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