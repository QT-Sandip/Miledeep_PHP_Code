<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

// $QueryParms = $_GET['Q'];
// if ($QueryParms != '') {
//     $QueryParms = urlDecode64($QueryParms);
//     $queries = array();
//     parse_str($QueryParms, $_GET);
// }


//if (isset($_GET['PoID']) && !empty($_GET['PoID'])) {

$PIID = trim($_GET['ID']);

$api = API_LETTER_OF_GUATANTEE;

$bodyContent = array("PIID" => $PIID);
$reponse = CallAPI($api, "GET", "", $bodyContent);

$error = $reponse->Error;

// echo json_encode($reponse, JSON_PRETTY_PRINT);
//exit;


//
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
            padding: 0px 3px;
            border: 1px solid #999999;
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
            /* max-width: 1150px; */
            letter-spacing: 0.01px !important;
        }

        .f800 {
            font-weight: 800;
        }

        .mgb5 {
            margin-bottom: 5px;
        }

        .mgb4 {
            margin-bottom: 4px;
        }

        .mgb3 {
            margin-bottom: 3px;
        }

        .px-0 {
            padding-left: 0px;
            padding-right: 0px;
        }

        .page-break {
            page-break-inside: avoid;
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

                                <?php
                                if (isset($error) && !empty($error)) {
                                    echo '<div class="text-center">' . $error . '</div>';
                                } else {
                                    $List = $reponse->Data[0];
                                ?>
                                    <div class="pdf-content" id="printArea">
                                        <div style="text-align: center; margin-bottom: 25px;">
                                            <button class="generate" id="generate" onclick="downloadpdf('LETTER-OF-QUARANTEE-<?php echo $List->CustomsInvNo ?>.pdf')">Download PDF</button>
                                            <!-- <button class="generate" id="generate" onclick="downloadpdf('filename')">Download PDF</button> -->
                                        </div>

                                        <div id="printTable" style="max-width: 980px;">

                                            <!----------------------------------------- Page 1 ---------------------->

                                            <!-- <img src="../../images/mourya-aquex-header.jpg" alt="Header Image" style="max-height: 150px; "> -->
                                            <!-- <img src="../../images/jme-logo.jpg" alt="Header Image" style="margin-bottom: 35px;max-height: 150px;"> -->
                                            <?php
                                            if ($List->Client == "MA") {
                                                echo '<header style="text-align: center; margin-bottom: 0px;">
                                                        <!--<img src="https://cybercoast.in/Images/ma_logo_editt.jpg" alt="Header Image" style="margin-bottom: 10px;max-width: 100%;" />-->
                                                        <img src="' . $List->HeaderUrl . '" alt="Header Image" style="margin-bottom: 10px;max-width: 100%;" />
                                                    </header>';
                                            } else {
                                                echo '<header style="text-align: left; margin-bottom: 0px;">
                                                        <!--<img src="https://cybercoast.in//Images/jme_logo.jpg" alt="Header Image" style="margin-bottom: 10px;max-height: 150px;" />-->
                                                        <img src="' . $List->HeaderUrl . '" alt="Header Image" style="margin-bottom: 10px;max-height: 150px;" />
                                                    </header>';
                                            }
                                            ?>

                                            <div class="page f-16 page-break" id="page1-content">

                                                <table class="tblMain w100 b-all0">
                                                    <tr>
                                                        <td class="b-all0 text-right" style="padding: 10px 3px;">Date: <?php echo date('d-m-Y', strtotime($List->CInvDate)) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center b-all0" style="font-size: 24px;padding: 0px 3px;">LETTER OF GUARANTEE</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="b-all0" style="height: 30px;">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="b-all0" style="line-height: 30px;">
                                                            <p>We,<?php echo $List->ExpName ?>, guarantee that the products under Commercial Invoice No. <?php echo $List->CustomsInvNo ?>_ (PO No.<?php echo $List->PoNo ?>) shipped in Container No.<?php echo $List->ContainerNo ?> on (<?php echo date('d-m-Y', strtotime($List->CInvDate)) ?>) comply with the Food Standards Australia New Zealand (FSANZ) Standards Code and conform to the provisions set by <?php echo $List->ConsigneeName ?> in terms of product technical specifications, and labeling and packaging requirements.</p>
                                                            <p>We guarantee that we will undertake full responsibility for the compensation related to the non-conformity of this shipment to the above listed requirements even if those non-conformities are recorded after reception of the products.</p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="b-all0" style="height: 50px;">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="b-all0">
                                                            <table class="b-all0">
                                                                <tr>
                                                                    <td class="b-all0" style="height: 40px;">Signature </td>
                                                                    <td class="b-all0">: <span style="
                                                                            color: #000000;
                                                                            border-bottom: 1px solid #000000;
                                                                            min-width: 430px;
                                                                            display: inline-block;
                                                                        "></span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="b-all0" style="height: 40px;">Name of Authorised Personnel </td>
                                                                    <!-- <td class="b-all0">: ___________________________</td> -->
                                                                    <!-- <td class="b-all0">: R.SHIVA</td> -->
                                                                    <td class="b-all0">: <span style="
                                                                            color: #000000;
                                                                            border-bottom: 1px solid #000000;
                                                                            min-width: 430px;
                                                                            display: inline-block;
                                                                        ">R.SHIVA</span></td>

                                                                </tr>
                                                                <tr>
                                                                    <td class="b-all0" style="height: 40px;">Position </td>
                                                                    <!-- <td class="b-all0">: EXPORT MANAGER </td> -->
                                                                    <td class="b-all0">: 
                                                                        <span style="
                                                                            color: #000000;
                                                                            border-bottom: 1px solid #000000;
                                                                            min-width: 430px;
                                                                            display: inline-block;
                                                                        ">EXPORT MANAGER</span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <br>
                                            </div>

                                            <div id="test"><br></div>
                                            <!------------------------------------- END of Page 1 ---------------------->

                                            <!-- <footer style="text-align: center; font-size: 16px;border-top: 2px solid #000000; padding-top: 15px">                                            
                                                <p style="margin: 0;">
                                                    JAGADEESH MARINE EXPORTS, 1-174, Komarada Road, Rayalam village, Bhimavaram: 534 208, INDIA. <br>
                                                        Phone: +91 8816 227124 / 227996, Fax: 227120 / 227122, Web: www.jmexports.como, E-mail: contact@jmexports.com
                                                </p>
                                                <img src="../../images/jme-footer.jpg" style="width: 100%;" />
                                        </footer> -->

                                            <?php
                                            if ($List->Client == "MA") {

                                                echo '<footer style="padding-top: 15px">
                                                        <!--<img src="https://cybercoast.in/Images/mourya_footer.jpg" style="width: 100%;" />-->
                                                        <img src="' . $List->FooterUrl . '" style="width: 100%;" />
                                                        </footer>';
                                            } else {
                                                echo '<footer style="padding-top: 15px">
                                                        <!--<img src="https://cybercoast.in/Images/jme_footer.jpg" style="width: 100%;" />-->
                                                        <img src="' . $List->FooterUrl . '" style="width: 100%;" />
                                                    </footer>';
                                            }
                                            ?>

                                            <br><br><br><br>



                                        </div> <!-- Actual Page Content -->

                                    </div> <!-- END pdf-content -->

                                <?php } ?>

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
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.3.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.js" integrity="sha512-VqCeCECsaE2fYTxvPQk+OJ7+SQxzI1xZ6IqxuWd0GPKaJoeSFqeakVqNpMbx1AArieciBF71poL0dYTMiNgVxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


</script>
<script>
    window.jsPDF = window.jspdf.jsPDF;
    var pdf = new jsPDF({
        orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
        unit: 'px', // Measurement unit
        format: 'a4', // 'a3', // Page size format, 'a4' for A4 size

    });
    // function test(f) {
    //     downloadpdf(f);
    // }
    function downloadpdf(fileName) {


        //var pdf = new jsPDF();
        var pWidth = pdf.internal.pageSize.getWidth(); // Page width
        var pHeight = pdf.internal.pageSize.getHeight(); // Page height

        // pdf.internal.write(0, "Tw") // <- add this
        // pdf.addFont('Lato-Regular', 'normal');
        // doc.setFont('Lato-Regular', 'normal');
        //pdf.setFont("Time-Italic");  


        // Define the HTML element you want to capture (e.g., a table with id "printTable")
        var element1 = document.getElementById("page1-content");
        var element2 = document.getElementById("page2-content");
        var element = document.getElementById("page1-content");

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

        $(".page-break").height(1090);

        filename = 't';

        var element1 = document.getElementById("page1-content");
        var element2 = document.getElementById("page2-content");
        var element = document.getElementById("printTable"); // printArea

        var doc = new jsPDF('p', 'pt', 'letter');

        var options = {
            background: '#fff',
            pagesplit: true
        };

        // Calculate the x and y position to center the content within the PDF page
        var x = 0; // Left-aligned content

        // pdf.setFont("Arial Unicode MS");  
        pdf.html(element, {
            html2canvas: {
                // scale: 0.61, // a3
                // scale: 0.43, // a3
                scale: 0.41, // a3
                width: contentWidth,
                height: contentHeight,
                removeContainer: false // for analyzing iframe
            },
            x: x,
            margin: [5, 20, 30, 20], // Adds the margin to the PDF view 
            autoPaging: 'text',
            pagesplit: true,
            callback: function(pdf) {

                var timestamp = Date.now();
                var pdfName = fileName;
                pdf.save(pdfName);
                //pdf.output('dataurlnewwindow');

                $("#test").html("<br><br>");
                $(".page-break").height("auto");


            }
        });

        //pdf.output('dataurlnewwindow');

    }
</script>
<script>
    $(document).ready(function() {

        var h = $("#tdGeoGraphic").height();
        var vh = $("#tblHarvest").height();
        // console.log(h);
        if (h > vh) {
            $("#tblHarvest").height(h);
        }

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