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

    <style>
        .py2px5{
            padding: 2px 5px;
        }
        .text-right{
            text-align: right;
        }
        .py0px5{
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
                                        <button class="generate" id="generate" onclick="downloadpdf('FileName6<?php /* echo str_replace('/', '_', $result->InvoiceNumber) */ ?>.pdf')">Download PDF</button>
                                    </div>

                                    <div id="printTable">
                                        <table class="tblMain">
                                            <tr>
                                                <td>
                                                    <h2 class="text-center" style="font-size: 45px;font-weight: 600;">EASTERN FISH COMPANY, LLC.,</h2>
                                                    <p class="text-center" style="    font-size: 30px;margin-top: 20px;margin-bottom: 5px;">Purchase Order</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table class="bt bl br bb w100">
                                                        <!-- <table class="w100"> -->
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100 f-15">
                                                                    <!-- <tr>
                                                                        <td class="w50 lh18">
                                                                            <div class="f-14 mb-1 d-block">PO. NO : DF/DA-2672</div>
                                                                        </td>
                                                                        <td style="text-align: right;">
                                                                            <div class="f-14 mb-1 d-block">PO Date : 02/24/2023.</div>
                                                                        </td>
                                                                    </tr> -->
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <table class="w100">
                                                                                <tr>
                                                                                    <td class="py0px5" style="width: 170px">PURCHASE ORDER#</td>
                                                                                    <td class="py0px5" style="width: 310px;">
                                                                                        <?php echo $result->PoNumber ?>
                                                                                    </td>
                                                                                    <td class="text-right py0px5" style="width: 170px;text-decoration: underline;"> Vendor:</td>
                                                                                    <td class="py0px5">
                                                                                        <?php echo $result->ExporterName ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="py0px5">Date </td>
                                                                                    <td class="py0px5">
                                                                                       <?php echo date('d.m.Y', strtotime($result->PoDate)) ?>
                                                                                    </td>
                                                                                    <td class="py0px5"></td>
                                                                                    <td class="py0px5">
                                                                                        <?php echo $result->ExporterAddress?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="py0px5">Other Ref :</td>
                                                                                    <td class="py0px5"></td>
                                                                                    <td class="py0px5"></td>
                                                                                    <td class="py0px5"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="py0px5">CUSTOMER</td>
                                                                                    <td class="py0px5">
                                                                                        <?php echo $result->ConsigneeName . '<br>'. $result->ConsigneeAddress ; ?>
                                                                                    </td>
                                                                                    <td class="py0px5 text-right" style="text-decoration: underline;">Importer of Record:</td>
                                                                                    <td class="py0px5">
                                                                                        <?php echo '<br>'. $result->ImporterName .'<br>'. $result->ImporterAddress; ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="4">&nbsp;</td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    
                                                                    <tr>
                                                                        <td colspan="2" class="p-0">
                                                                            <table class="w-100">
                                                                                <tr>
                                                                                    <?php
                                                                                    $i = 1;
                                                                                    foreach ($resLableKeys as $KeyValues) {
                                                                                        $countKey = count($resLableKeys);
                                                                                        if ($i == 1) {
                                                                                            $class = "bt br ";
                                                                                        } else {
                                                                                            $class = "bt bb br text-center ";
                                                                                        }
                                                                                        echo '<td class="'.$class.'">'. $resTableLabels->$KeyValues .'</td>';
                                                                                        $i++;
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
                                                                                                    <td class="py2px5 bb br text-left" rowspan="'.$rowspan.'">'.$ItemsGroup->ItemDescription.'<div>BRAND : '.$ItemsGroup->Brand.'</div></td>
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
                                                                                                    echo '<td class="bb br" style="'.$style.'">'. $ListItems->$keyVal .'</td>';
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
                                                                                                        if ($countKey == $x) {
                                                                                                            $style = "text-align: right;";
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
                                                                                <tr>
                                                                                    <td class="py2px5 bb br text-center"><?php echo $resGrandTotal->Lable ?></td>
                                                                                            <?php
                                                                                                $i = 1;
                                                                                                $countKey = count($resLableKeys);
                                                                                                foreach ($resLableKeys as $keyVal) {
                                                                                                    if ($countKey == $i) {
                                                                                                        $class = "text-right";
                                                                                                    } else {
                                                                                                        $class = "text-center";
                                                                                                    }

                                                                                                    if($i > 1) {                                                                                                    
                                                                                                        echo '<td class="py2px5 bb br '.$class.'">'.$resGrandTotal->$keyVal.'</td>';
                                                                                                    }

                                                                                                    $i++;
                                                                                                }
                                                                                            ?>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="6" class="bb" style="vertical-align:middle;height: 45px;">Shipment: <?php echo strtoupper(date('dS M Y', strtotime($result->ShipByDate))) ?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <td>
                                                                        <table class="w-100">
                                                                            <tr>
                                                                                <td style="width:200px">Terms of Payment: </td>
                                                                                <td colspan="2"><?php echo $result->PaymentTerms ?> </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="width:200px">Port of Discharge: </td>
                                                                                <td><?php echo $result->PortOfDischarge ?></td>
                                                                                <td> Final Destination: <?php echo $result->FinalDestination ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="3" style="width:200px">&nbsp;</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="width:200px">Processed and Packed by: </td>
                                                                                <td>
                                                                                    <?php
                                                                                    echo $result->ProcessorName ."<br>"; 
                                                                                    echo $result->ProcessorAddress; ?>
                                                                                </td>
                                                                                <td>
                                                                                    For Eastern Fish Company LLC
                                                                                    <br>
                                                                                    <br>
                                                                                    <br>
                                                                                    <br>
                                                                                    Authorised Signatory
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
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