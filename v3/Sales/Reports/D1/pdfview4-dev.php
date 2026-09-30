<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

//  echo $_SESSION["Token"];

$ID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

if (isset($_GET['ID']) && !empty($_GET['ID'])) {

    $ID = trim($_GET['ID']);

   // $api = API_PDF_CUSTOM_PRINT;

     $api = API_PDF_PROFORMA_PRINT;
    // $api = API_PDF_COMMERCIAL_PRINT;
    // $DocType = 'CMI';

     $DocType = 'CUI';
     $bodyContent = array("ProformaID" => $ID, "DocType" => $DocType);

   // $bodyContent = array("ProformaID" => $ID);

    $resultData = CallAPI($api, "POST", "", $bodyContent);

    // echo json_encode($resultData, JSON_PRETTY_PRINT);

    $result = $resultData->Data;
    $resLabels = $resultData->Data->Labels;
    $resItemsGroup = $resultData->Data->ItemsGroup;
    $resLableKeys = $resultData->Data->LableKeys;
    $resTableLabels = $resultData->Data->TableLabels;
    $resGrandTotal = $resultData->Data->GrandTotal;

    $resPaymentDetails = $resultData->Data->PaymentDetails;
    // echo json_encode($resultData, JSON_PRETTY_PRINT);
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
                                        <button class="generate" id="generate" onclick="downloadpdf('FileName4<?php /* echo str_replace('/', '_', $result->InvoiceNumber) */ ?>.pdf')">Download PDF</button>
                                    </div>

                                    <div id="printTable">
                                        <table class="tblMain">
                                            <tr>
                                                <td>
                                                    <h2 class="text-center bolder" style="font-size: 30px;font-weight: 600;line-height: 42px;">
                                                        PURCHASE CONTRACT
                                                    </h2>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <!-- <table class="bt bl br bb w100"> -->
                                                    <table class="w100">
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100 f-14">
                                                                    <tr>
                                                                        <td class="w50 lh18">

                                                                        </td>
                                                                        <td style="text-align: right;">
                                                                            <table style="width: 300px !important;float: right;">
                                                                                <tr>
                                                                                    <td class="py2px5">CONTRACT NO : </td>
                                                                                    <td class="py2px5 text-left"><?php echo $result->PoNumber ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="py2px5">OTHER REF :</td>
                                                                                    <td class="py2px5 text-left">XXXXXXXX</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="py2px5">DATE :</td>
                                                                                    <td class="py2px5 text-left"><?php echo date('d/m/Y', strtotime($result->PoDate)) ?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <table class="w-100">
                                                                                <tr>
                                                                                    <td style="width: 180px">THE BUYERS</td>
                                                                                    <td>: <?php echo strtoupper($result->ConsigneeName) ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>ADDRESS</td>
                                                                                    <td>: <?php echo strtoupper($result->ConsigneeAddress) ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>THE SELLERS</td>
                                                                                    <td>: <?php echo $result->ExporterName ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>ADDRESS</td>
                                                                                    <td>: <?php echo $result->ExporterAddress ?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin-top: 15px;">THE SELLER AGREES TO SELL AND THE BUYER AGREES TO BUY THE UNDER MENTIONED COMMODITY ACCORDING</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin-top: 10px;">TO THE TERMS AND CONDITIONS STATED BELOW</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="p-0">
                                                                            <table class="w-100 bt bb br bl">
                                                                                <tbody>
                                                                                    <tr class="text-center">
                                                                                        <?php                                                                                    
                                                                                            foreach ($resLableKeys as $keyVal){
                                                                                                echo '<td class="bb br"><b>'. $resTableLabels->$keyVal .'</b></td>';
                                                                                            }
                                                                                        ?>
                                                                                    </tr>
                                                                                    <?php 

                                                                                        $a = 1;
                                                                                        foreach ($resItemsGroup as $ItemsGroup) {
                                                                                            if ($ItemsGroup->ShowSubTotal == true) {
                                                                                                $rowspan = count($ItemsGroup->ItemsList) + 1;
                                                                                            } else {
                                                                                                $rowspan = count($ItemsGroup->ItemsList);
                                                                                            }
                                                                                            echo '
                                                                                            <tr class="text-center">
                                                                                                <td class="py2px5 bb br text-left" rowspan="'.$rowspan.'">'.$ItemsGroup->ItemDescription.'</td>
                                                                                            </tr>';

                                                                                            $j = 1;
                                                                                            foreach ($ItemsGroup->ItemsList as $ListItems) {
                                                                                                $count = count($ItemsGroup->ItemsList);
                                                                                                if ($count != $j) {
                                                                                                    echo '<tr>';
                                                                                                    $s = 1;
                                                                                                    $countKey = count($resLableKeys);
                                                                                                    foreach ($resLableKeys as $keyVal) {  
                                                                                                        if ($countKey == $s) {
                                                                                                            $style = "text-align: right;";
                                                                                                        } else {
                                                                                                            $style = "text-align: center;";
                                                                                                        }
                                                                                                        if(isset($ListItems->$keyVal) && !empty($ListItems->$keyVal)) {
                                                                                                            echo '<td class="py2px5 bb br" style="'.$style.'">' . $ListItems->$keyVal . '</td>';
                                                                                                        }
                                                                                                        $s++;
                                                                                                    }
                                                                                                    echo '</tr>';
                                                                                                    
                                                                                                } else { // Last Record for Subtotal                                                                                                    
                                                                                                    if ($ItemsGroup->ShowSubTotal == true) {
                                                                                                        echo '<tr>';               
                                                                                                        $x = 1;                                                                                         
                                                                                                        $countKey = count($resLableKeys);
                                                                                                        foreach ($resLableKeys as $key => $keyVal) {
                                                                                                            if ($countKey == $s) {
                                                                                                                $style = "text-align: right; ";
                                                                                                            } else {
                                                                                                                $style = "";
                                                                                                            }
                                                                                                            if(isset($ListItems->$keyVal) && !empty($ListItems->$keyVal)) {
                                                                                                                echo '<td class="py2px5 bb br" style="'.$style.'">' . $ListItems->$keyVal . '</td>';
                                                                                                            }
                                                                                                            $x++;

                                                                                                        }
                                                                                                        echo '</tr>';
                                                                                                    }
                                                                                                }
                                                                                                $j++;
                                                                                            }
                                                                                            $a++;
                                                                                        }
                                                                                    ?>

                                                                                    
                                                                                    <tr class="text-center">
                                                                                        <td class="py2px5 bb br"><b><?php echo $resGrandTotal->Lable ?></b></td>
                                                                                        <?php
                                                                                            $i = 1;
                                                                                            $countKey = count($resLableKeys);
                                                                                            foreach ($resLableKeys as $keyVal) {
                                                                                                if ($countKey == $i) {
                                                                                                    $class = "text-right";
                                                                                                } else {
                                                                                                    $class = "";
                                                                                                }

                                                                                                if($i > 1) {                                                                                                    
                                                                                                    echo '<td class="py2px5 bb br '.$class.'"><b>'.$resGrandTotal->$keyVal.'</b></td>';
                                                                                                }

                                                                                                $i++;
                                                                                            }
                                                                                         ?>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="height: 5px;">&nbsp;</td>
                                                                    </tr>
                                                                    <tr><td>1.AMOUNT AND QUANTITY 10% MORE OR LESS ALLOWED.</td></tr>
                                                                    <tr><td>2.PACKING : <?php  foreach ($resItemsGroup as $ItemsGroup) {
                                                                                                 echo $ItemsGroup->PackingStyle;
                                                                                                 }
                                                                                        ?></td></tr>
                                                                    <tr><td>3 CONTAINER SIZE 40.FT</td></tr>
                                                                    <tr><td>4.TIME OF SHIPMENT : <?php echo strtoupper(date('dS F Y', strtotime($result->ShipByDate))); ?></td></tr>
                                                                    <tr><td>5.PORT OF SHIPMENT ANY INDIAN PORT<?php // echo $result->PortOfLoading ?></td></tr>
                                                                    <tr><td>6.PORT OF DESTINATION : <?php echo $result->FinalDestination ?></td></tr>
                                                                    <tr><td>7.COUNTRY OF ORIGIN : <?php echo $result->CountryOfOrgin ?></td></tr>
                                                                    <tr><td>8.10000$ ADV AFTER CONF THE CARGO INSP AND VESSEL SCH AND BLC WILL PAY AFTER CHINA CUSTOMS CLERENCE</td></tr>
                                                                    <tr><td><b>9.SALES COMMISSION USD 3 CENTS PER KG</b></td></tr>

                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="margin-top: 55px;">THE BUYERS:</div>
                                                                            <?php echo strtoupper($result->ConsigneeName); ?><br>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="height: 35px;">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div>AUTHORISED SIGNATORY.</div>
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