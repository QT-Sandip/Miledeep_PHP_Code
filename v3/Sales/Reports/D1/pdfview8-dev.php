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
    // echo json_encode($resPostProfromaPrint,JSON_PRETTY_PRINT);
    // exit; 
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
                                        <button class="generate" id="generate" onclick="downloadpdf('FileName8<?php /* echo str_replace('/', '_', $result->InvoiceNumber) */ ?>.pdf')">Download PDF</button>
                                    </div>

                                    <div id="printTable">
                                        <table class="tblMain">
                                            <!-- <tr>
                                                <td>
                                                    <h2 class="text-center" style="font-size: 40px;font-weight: 500;font-style: italic;margin-bottom: 30px;">STANLEY PEARLMAN ENTERPRISES, INC.</h2>
                                                    <p class="text-center" style="font-size: 20px;">
                                                        7775 CHESAPEAKE BAY COURT, JESSUP, MD 20794, U.S.A.<br>
                                                        TEL: 410-799-7655.
                                                    </p>
                                                </td>
                                            </tr> -->
                                            <tr>
                                                <td>
                                                    <h2 class="text-center"
                                                        style="font-size: 40px;font-weight: 500;font-style: italic;margin-bottom: 30px;">
                                                        <?php echo $result->ConsigneeName ?>
                                                    </h2>
                                                    <p class="text-center" style="font-size: 20px;">
                                                        <?php echo $result->ConsigneeAddress ?>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <!-- <table class="bt bl br bb w100"> -->
                                                    <table class="w100">
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100">
                                                                    <tr>
                                                                        <td class="w50 lh18">
                                                                            <div class="f-14 mb-1 d-block"><span><b><u>PO. NO</u></span> : <?php echo $result->PoNumber ?></b></div>
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <div class="f-14 mb-1 d-block">PO Date : <?php echo date('d/m/Y.', strtotime($result->PoDate)) ?></div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <table class="w100">
                                                                                <tr>
                                                                                    <td><span><b><u>Supplier :</u></b></span></td>
                                                                                    <td style="width: 300px;">
                                                                                        <?php
                                                                                        echo $result->ExporterName . '<br>' .
                                                                                            $result->ExporterAddress;
                                                                                        ?>
                                                                                    </td>
                                                                                    <td class="text-right"><span><b><u>Purchaser :</u></b></span></td>
                                                                                    <td style="width: 300px;">
                                                                                        <?php
                                                                                        echo $result->ConsigneeName . '<br>' .
                                                                                            $result->ConsigneeAddress;
                                                                                        ?>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="height: 50px;">&nbsp;</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="border-top: 1px dashed #333;border-bottom: 1px dashed #333;height: 25px">&nbsp;</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div><span style="display: inline-block; width: 180px">Pmnt. Terms </span> : <?php echo $result->PaymentTerms ?></div>
                                                                            <div><span style="display: inline-block; width: 180px">Ship Terms </span> : <?php echo $result->PortOfDischarge ?></div>
                                                                            <div><span style="display: inline-block; width: 180px">Shipment Date</span> : <?php echo date('d/m/Y.', strtotime($result->ShipByDate)) ?></div>
                                                                            <div><span style="display: inline-block; width: 180px">CTRY ORIGIN </span> : <?php echo $result->CountryOfOrgin ?>.</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div style="border-top: 1px dashed #333;border-bottom: 1px dashed #333;height: 25px">&nbsp;</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <table class="w-100">
                                                                                <tr>
                                                                                    <?php
                                                                                    $i = 1;
                                                                                    foreach ($resLableKeys as $KeyValues) {
                                                                                        echo '<td>' . $resTableLabels->$KeyValues . '</td>';
                                                                                        $i++;
                                                                                    }
                                                                                    ?>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="6">
                                                                                        <div style="border-top: 1px dashed #333;border-bottom: 1px dashed #333;height: 25px">&nbsp;</div>
                                                                                    </td>
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
                                                                                        <tr>
                                                                                            <td rowspan="' . $rowspan . '">' . $ItemsGroup->Product . '</td>
                                                                                            <td rowspan="' . $rowspan . '"> 
                                                                                            <div> ';
                                                                                            foreach ($ItemsGroup->ItemsList as $Item) {
                                                                                                echo $Item->Grade;
                                                                                            }
                                                                                          echo ' ' . $ItemsGroup->ItemDescription . '</div>
                                                                                                <div>PACKING : ' . $ItemsGroup->PackingStyle . '</div>
                                                                                                <div>BRAND : ';
                                                                                    foreach ($ItemsGroup->ItemsList as $Item) {
                                                                                        echo $Item->Brand;
                                                                                    }
                                                                                    echo '</div>
                                                                                                </td>
                                                                                            </tr>';
                                                                                    $j = 1;
                                                                                    foreach ($ItemsGroup->ItemsList as $ListItems) {
                                                                                        $count = count($ItemsGroup->ItemsList);
                                                                                        if ($count != $j) {
                                                                                            echo '<tr>';
                                                                                            $countKey = count($resLableKeys);
                                                                                            foreach ($resLableKeys as $keyVal) {
                                                                                                if (isset($ListItems->$keyVal) && !empty($ListItems->$keyVal)) {
                                                                                                    echo '<td>' . $ListItems->$keyVal . '</td>';
                                                                                                }
                                                                                            }
                                                                                            echo '</tr>';
                                                                                        } else { // Last Record for Subtotal                                                                                                    
                                                                                            if ($ItemsGroup->ShowSubTotal == true) {
                                                                                                echo '<tr>';
                                                                                                $countKey = count($resLableKeys);
                                                                                                foreach ($resLableKeys as $key => $keyVal) {
                                                                                                    echo '<td>' . $ListItems->$keyVal . '</td>';
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
                                                                                    <td colspan="6" style="height: 80px;border-bottom: 2px solid #333333">&nbsp;</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="2">
                                                                                        <table class="w-50">
                                                                                            <tr>
                                                                                                <td>Total Units : </td>
                                                                                                <td class="text-right"><?php echo $resGrandTotal->MCS ?></td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>Total Weight : </td>
                                                                                                <td class="text-right"><?php echo $resGrandTotal->Weight ?></td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                    <td colspan="4">&nbsp;</td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td colspan="2">&nbsp;</td>
                                                                                    <td colspan="4">
                                                                                        <table class="w-100">
                                                                                            <tr>
                                                                                                <td>TOTAL AMOUNT : </td>
                                                                                                <td style="border-bottom: 1px double #333333;border-top: 1px dashed #333333;">$</td>
                                                                                                <td style="border-bottom: 1px double #333333;border-top: 1px dashed #333333;"><?php echo $resGrandTotal->ItemPrice ?> </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>

                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><!-- For STANLEY PEARLMAN
                                                                            ENTERPRISES, INC -->
                                                                        <?php echo $result->ConsigneeName ;?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="height: 35px;">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div>Authorised Signatory</div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" style="text-align: center;padding-top: 30px;">
                                                                          <!--  7775 CHESAPEAKE BAY COURT, JESSUP, MD 20794, U.S.A<br>
                                                                            TEL: 410-799-7655.-->
                                                                            <?php echo $result->ConsigneeAddress ?>
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