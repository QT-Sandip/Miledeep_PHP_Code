<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

//  $ArrivalBillID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

//  if (isset($_GET['ID']) && !empty($_GET['ID'])) {

//    $ArrivalBillID = trim($_GET['ID']);

//      $api = API_RM_BILLS;

//    $bodyContent = array("ArrivalID" => $ArrivalBillID);
//     $resPostPurchaseBillPrint = CallAPI($api, "GET", "", $bodyContent);

//    //   var_dump($resPostPurchaseBillPrint);

//      $result = $resPostPurchaseBillPrint->Data; 
//      $resPurchaseData = $resPostPurchaseBillPrint->Data->ArrivalData;
//      $resPurchaseBreakUpData = $resPostPurchaseBillPrint->Data->BreakUpData;
//      $resGrandTotal = $resPostPurchaseBillPrint->Data->GrandTotal;

// 	// var_dump($resPurchaseData);

//  } else {

//  }


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>" rel="stylesheet" type="text/css">

    <style>
        h1 {
            font-size: 27px;
            color: #000000;
        }

        .w495 {
            width: 49.5%;
        }

        .w1 {
            width: 1%;
        }

        .text-right {
            text-align: right;
        }

        table td {
            padding: 2px 3px;
            border: 1px solid #333333;
        }

        @media print {
            .page {
                page-break-before: always;
            }
        }

        .page {
            page-break-before: always;
            /* Force a page break before this content */
        }

        .f-11 {
            font-size: 11px;
        }

        .b-all0 {
            border: 0px;
        }

        .subtable td {
            border-left: 0px;
            border-right: 0px;
            border-top: 0px;
            border-left: 0px;
        }
        #printTable {
            max-width: fit-content;
            overflow-x: auto;
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
                                    <div style="text-align: center; margin-bottom: 25px;">
                                        <!-- <button class="generate" id="generate" onclick="generatePDF('filename')">Download PDF</button> -->
                                        <button class="generate" id="generate" onclick="downloadpdf('filename')">Download PDF</button>
                                    </div>

                                    <div id="printTable">

                                        <!----------------------------------------- Page 1 ---------------------->

                                        <div class="page f-12" id="page1-content">
                                            <table class="text-center w100">
                                                <tr>
                                                    <td class="b-all0 f-16">NOAA</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0 f-15">MANDATORY SIMP US ENTRY DATA LIST</td>
                                                </tr>
                                                <tr><td class="b-all0 text-left  f-14">CONTAINER NUMBER: CGMU6549351</td></tr>
                                                <tr><td class="b-all0 text-left  f-14">NET WEIGHT: 14982.000 KGS</td></tr>
                                                <tr><td class="b-all0 text-left  f-14"></td></tr>
                                                <tr><td class="b-all0 text-left  f-14"></td></tr>
                                                <tr><td class="b-all0 text-left  f-14"></td></tr>
                                            </table>
                                            <table class="tblMain w100 bt bl bb br">                                                
                                                <tr style="background: #cfcfcf;">
                                                    <td style="min-width: 180px;">Field Description</td>
                                                    <td style="min-width: 180px;">Field Notes </td>
                                                    <td>Entry Data</td>
                                                    <td>Entry Data</td>
                                                    <td>Entry Data</td>
                                                    <td>Entry Data</td>
                                                    <td>Entry Data</td>
                                                    <td>Entry Data</td>
                                                    <td>Entry Data</td>
                                                    <td>Entry Data</td>
                                                </tr>
                                                <tr>
                                                    <td>IFTP#</td>
                                                    <td>Reference at bottoom right of form below barcode</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>Species ALPHS Code </td>
                                                    <td>See NOAA web link. (ie PNV for penaeus Vannamei, White Legged Shrimp)</td>
                                                    <td>PNV</td>
                                                    <td>PNV</td>
                                                    <td>PNV</td>
                                                    <td>PNV</td>
                                                    <td>PNV</td>
                                                    <td>PNV</td>
                                                    <td>PNV</td>
                                                    <td>PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>Harvest Source Code</td>
                                                    <td>ie HBA for Hatchery Based Agriculture</td>
                                                    <td>AP048773/001</td>
                                                    <td>AP048773/001</td>
                                                    <td>AP048773/001</td>
                                                    <td>AP048773/001</td>
                                                    <td>AP048773/001</td>
                                                    <td>AP048773/001</td>
                                                    <td>AP048773/001</td>
                                                    <td>AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>Country of Harvest</td>
                                                    <td></td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                </tr>
                                                <tr>
                                                    <td>Area of Harvest</td>
                                                    <td>Same as Country if Farm Raised </td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                    <td>INDIA</td>
                                                </tr>
                                                <tr>
                                                    <td>Date of Harvest (MM/DD/YYYY)</td>
                                                    <td></td>
                                                    <td>09/09/2023</td>
                                                    <td>09/09/2023</td>
                                                    <td>09/09/2023</td>
                                                    <td>09/09/2023</td>
                                                    <td>09/09/2023</td>
                                                    <td>09/09/2023</td>
                                                    <td>09/09/2023</td>
                                                    <td>09/09/2023</td>
                                                </tr>
                                                <tr>
                                                    <td>Form of Product at Time of Lading/Trans-Shipment</td>
                                                    <td>ie DRESSED (NDR) or OTHER</td>
                                                    <td>NOT</td>
                                                    <td>NOT</td>
                                                    <td>NOT</td>
                                                    <td>NOT</td>
                                                    <td>NOT</td>
                                                    <td>NOT</td>
                                                    <td>NOT</td>
                                                    <td>NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>Weight and UOM</td>
                                                    <td>specify if LBS or KGS </td>
                                                    <td>2968.202</td>
                                                    <td>2968.202</td>
                                                    <td>2968.202</td>
                                                    <td>2968.202</td>
                                                    <td>2968.202</td>
                                                    <td>2968.202</td>
                                                    <td>2968.202</td>
                                                    <td>2968.202</td>
                                                </tr>
                                                <tr>
                                                    <td>Farm Name and address </td>
                                                    <td></td>
                                                    <td>
                                                        MERLA MADHURA <br>
                                                        MEENAKSHI, NARASAPURAPU<br>
                                                        PETA, RAMACHANDRAPURAM, 
                                                        EAST GODAVARI, <br>
                                                        ANDHRA PRADESH
                                                    </td>
                                                    <td>
                                                        MERLA MADHURA <br>
                                                        MEENAKSHI, NARASAPURAPU<br>
                                                        PETA, RAMACHANDRAPURAM, 
                                                        EAST GODAVARI, <br>
                                                        ANDHRA PRADESH
                                                    </td>
                                                    <td>
                                                        MERLA MADHURA <br>
                                                        MEENAKSHI, NARASAPURAPU<br>
                                                        PETA, RAMACHANDRAPURAM, 
                                                        EAST GODAVARI, <br>
                                                        ANDHRA PRADESH
                                                    </td>
                                                    <td>
                                                        MERLA MADHURA <br>
                                                        MEENAKSHI, NARASAPURAPU<br>
                                                        PETA, RAMACHANDRAPURAM, 
                                                        EAST GODAVARI, <br>
                                                        ANDHRA PRADESH
                                                    </td>
                                                    <td>
                                                        MERLA MADHURA <br>
                                                        MEENAKSHI, NARASAPURAPU<br>
                                                        PETA, RAMACHANDRAPURAM, 
                                                        EAST GODAVARI, <br>
                                                        ANDHRA PRADESH
                                                    </td>
                                                    <td>
                                                        MERLA MADHURA <br>
                                                        MEENAKSHI, NARASAPURAPU<br>
                                                        PETA, RAMACHANDRAPURAM, 
                                                        EAST GODAVARI, <br>
                                                        ANDHRA PRADESH
                                                    </td>
                                                    <td>
                                                        MERLA MADHURA <br>
                                                        MEENAKSHI, NARASAPURAPU<br>
                                                        PETA, RAMACHANDRAPURAM, 
                                                        EAST GODAVARI, <br>
                                                        ANDHRA PRADESH
                                                    </td>
                                                    <td>
                                                        MERLA MADHURA <br>
                                                        MEENAKSHI, NARASAPURAPU<br>
                                                        PETA, RAMACHANDRAPURAM, 
                                                        EAST GODAVARI, <br>
                                                        ANDHRA PRADESH
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Point of Contact </td>
                                                    <td></td>
                                                    <td>SDSV SUBBARAJU</td>
                                                    <td>SDSV SUBBARAJU</td>
                                                    <td>SDSV SUBBARAJU</td>
                                                    <td>SDSV SUBBARAJU</td>
                                                    <td>SDSV SUBBARAJU</td>
                                                    <td>SDSV SUBBARAJU</td>
                                                    <td>SDSV SUBBARAJU</td>
                                                    <td>SDSV SUBBARAJU</td>
                                                </tr>
                                                <tr>
                                                    <td>Point of Contact - email or phone number</td>
                                                    <td></td>
                                                    <td>87909 17555</td>
                                                    <td>87909 17555</td>
                                                    <td>87909 17555</td>
                                                    <td>87909 17555</td>
                                                    <td>87909 17555</td>
                                                    <td>87909 17555</td>
                                                    <td>87909 17555</td>
                                                    <td>87909 17555</td>
                                                </tr>
                                                <tr style="background: #cfcfcf;">
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                            </table>
                                            <table>
                                                <tr>
                                                    <td class="b-all0">**All FIELDS ARE MANDATORY AND MUST BE FILLED OUT FOR A NOAA ENTRY TO BE PROCESSED</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0">**COMPLETE ONE PAGE PER COMMERCIAL INVOICE LINE</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!------------------------------------- END of Page 1 ---------------------->

                                        <br><br>


                                        <br><br>

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
    var invNo = "<?php echo "commission-bill" ?>";
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


<script>
    window.jsPDF = window.jspdf.jsPDF;
    var pdf = new jsPDF({
        orientation: 'landscape', // 'portrait' for vertical, 'landscape' for horizontal
        unit: 'px', // Measurement unit
        format: 'a4', // 'a3', // Page size format, 'a4' for A4 size

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
        var marginLeft = 5;
        var marginTop = 5;
        var marginRight = 5;
        var marginBottom = 5;

        var contentWidth = element.offsetWidth - marginLeft - marginRight;
        console.log(contentWidth);
        var contentHeight = element.offsetHeight - marginTop - marginBottom;
        console.log(contentHeight);

        // Calculate the x and y position to center the content within the PDF page
        var x = 0; // Left-aligned content

        pdf.html(element, {
            html2canvas: {
                // scale: 0.61, // a3
                // scale: 0.540, // 2 + 4
                // scale: 0.477, // 2 + 5
                // scale: 0.430, // 2 + 6
                // scale: 0.383, // 2 + 7
                scale: 0.355, // 2 + 8
                width: contentWidth,
                height: contentHeight,
                removeContainer: false // for analyzing iframe
            },
            x: x,
            margin: [25, 0, 5, 5], // Adds the margin to the PDF view 
            autoPaging: 'text',
            callback: function (pdf) {

                // var footerHeight = 20; // Height of the footer
                // var gap = 10; // Gap between content and footer

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

                //     // pdf.text(
                //     //     pWidth / 2,
                //     //     pageNumberY,
                //     // 'Page ' + i + ' of ' + pdf.internal.getNumberOfPages() + " - " + invNo,
                //     //     null,
                //     //     null,
                //     //     'center'
                //     // );
                //     // pdf.addPage();
                // }

                // var timestamp = Date.now();
                // var pdfName = fileName;
                pdf.save(fileName);
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

</body>

</html>