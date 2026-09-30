<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$QueryParms = $_GET['ID'];
if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);        
	$queries = array();
	parse_str($QueryParms, $_GET);
}
// string(36) "PoID=13&RID=13&ReportName=NOAA(SIMP)"


// $ArrivalBillID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

if(isset($_GET['PoID']) && !empty($_GET['PoID'])) {

    $OrderID = trim($_GET['ID']);

    $api = API_QC_SIMPV1;

    $bodyContent = array("OrderID" => $_GET["PoID"]);
    $resShimp = CallAPI($api, "GET", "", $bodyContent);

    $List = $resShimp->Data;

    $countList =count($List);

    // echo json_encode($resShimp, JSON_PRETTY_PRINT);

    
} 

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
            /* max-width: fit-content; */
            max-width: 1400px;
            overflow-x: auto;
            letter-spacing: 0.01px !important;
        }
        .f800{
            font-weight: 800;
        }
        .underline{
            /* text-decoration: underline; */
        }
        #page1-content{
            position: relative;
        }
        #page1-content img{
            width: 75px;
            position: absolute;
            right: 40px;
            top: 35px;
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
                                        <?php $name = $resShimp->FileName."_NOAA.pdf" ?>
                                        <button class="generate" id="generate" onclick="downloadpdf('<?php echo $name ?>')">Download PDF</button>
                                    </div>

                                    <div id="printTable">

                                        <!----------------------------------------- Page 1 ---------------------->

                                        <div class="page f-12" id="page1-content">
                                            <img src="noaa.jpg" style="width: 75px" />
                                            <table class="text-center w100">
                                                <tr>
                                                    <td class="b-all0 f-16">NOAA</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0 f-15">MANDATORY SIMP US ENTRY DATA LIST</td>
                                                </tr>
                                                <tr><td class="b-all0 text-left  f-14">CONTAINER NUMBER: <?php echo $resShimp->ContainerNo ?></td></tr>
                                                <tr><td class="b-all0 text-left  f-14">NET WEIGHT : <?php echo $resShimp->NetWeightKgs ?> KGS</td></tr>
                                                <tr><td class="b-all0 text-left  f-14"></td></tr>
                                                <tr><td class="b-all0 text-left  f-14"></td></tr>
                                                <tr><td class="b-all0 text-left  f-14"></td></tr>
                                            </table>
                                            <table class="tblMain w100 bt bl bb br">                                                
                                                <tr style="background: #cfcfcf;">
                                                    <td style="min-width: 220px;">Field Description</td>
                                                    <td style="min-width: 180px;">Field Notes </td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>Entry Data</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">IFTP#</td>
                                                    <td>Reference at bottoom right of form below barcode</td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td></td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Species ALPHA Code </td>
                                                    <td>See NOAA web link. (ie PNV for penaeus Vannamei, White Legged Shrimp)</td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>'.$item->ProductASFIS.'</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Harvest Source Code</td>
                                                    <td>ie HBA for Hatchery Based Agriculture</td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>'.$item->Tracebility.'</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Country of Harvest</td>
                                                    <td></td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>INDIA</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Area of Harvest</td>
                                                    <td>Same as Country if Farm Raised </td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>INDIA</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Date of Harvest (MM/DD/YYYY)</td>
                                                    <td></td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>'.$item->HarvestDt.'</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Form of Product at Time of Lading/Trans-Shipment</td>
                                                    <td>ie DRESSED (NDR) or OTHER</td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>NOT</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Weight and UOM</td>
                                                    <td>specify if LBS or KGS </td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>'.$item->ReceivedQty.'</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Farm Name and address </td>
                                                    <td></td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td style="max-width: 300px;word-break: break-word;">'.$item->FarmerName.', '.nl2br($item->AddressContinued).'</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Point of Contact </td>
                                                    <td></td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>'.$item->PurchaseManager.'</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td class="f800 underline">Point of Contact - email or phone number</td>
                                                    <td></td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>'.$item->PurchaseManagerNumber.'</td>';
                                                        }
                                                    ?>
                                                </tr>
                                                <tr style="background: #cfcfcf;">
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <?php
                                                        foreach($List as $item) {
                                                            echo '<td>&nbsp;</td>';
                                                        }
                                                    ?>
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
        var element = document.getElementById("page1-content");

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
                scale: 0.445, // 2 + 6
                // scale: 0.383, // 2 + 7
                // scale: 0.355, // 2 + 8
                width: contentWidth,
                height: contentHeight,
                removeContainer: false // for analyzing iframe
            },
            x: x,
            margin: [25, 0, 5, 20], // Adds the margin to the PDF view 
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