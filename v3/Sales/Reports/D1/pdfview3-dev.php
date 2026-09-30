<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// echo $_SESSION["Token"];

$ID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

if (isset($_GET['ID']) && !empty($_GET['ID'])) {

    $ID = trim($_GET['ID']);

    //$api = API_PDF_CUSTOM_PRINT;
     $api = API_PDF_PROFORMA_PRINT;
     $DocType = 'CUI';
    
     $bodyContent = array("ProformaID" => $ID, "DocType" => $DocType);

  //  $bodyContent = array("ProformaID" => $ID);
    $resultData = CallAPI($api, "POST", "", $bodyContent);

    // echo json_encode($resultData, JSON_PRETTY_PRINT);

    $result = $resultData->Data;
    $resLabels = $resultData->Data->Labels;
    $resItemsGroup = $resultData->Data->ItemsGroup;
    $resLableKeys = $resultData->Data->LableKeys;
    $resTableLabels = $resultData->Data->TableLabels;
    $resGrandTotal = $resultData->Data->GrandTotal;

    $resPaymentDetails = $resultData->Data->PaymentDetails;
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;
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
                                        <button class="generate" id="generate" onclick="downloadpdf('FileName3<?php /* echo str_replace('/', '_', $result->InvoiceNumber) */ ?>.pdf')">Download PDF</button>
                                    </div>

                                    <div id="printTable">
                                        <table class="tblMain">
                                            <tr>
                                                <td>
                                                    <h2 class="text-center" style="font-size: 30px;font-weight: 600;border-bottom: 3px solid #333333;line-height: 42px;">
                                                    <?php echo $result->ConsigneeName .'<br>'. $result->ConsigneeAddress; ?>   
                                                    <!-- ARISTA INDUSTRIES INC<br>
                                                        557 DANBURY RD, WILTON, CT 06897, U.S.A -->
                                                    </h2>                                                    
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <!-- <table class="bt bl br bb w100"> -->
                                                    <table class="w100">
                                                        <tr>
                                                            <td class="p-0 tblHeader">
                                                                <table class="w100 f-16">
                                                                    <tr>
                                                                        <td class="w50 lh18">
                                                                            <div class="mb-1 d-block">
                                                                                ARISTA PO # <?php echo $result->PoNumber ?>
                                                                            </div>
                                                                            <br>
                                                                            <div>
                                                                            <?php echo $result->ExporterName ?><br>
                                                                            <?php echo nl2br($result->ExporterAddress) ?>
                                                                            <!-- Devi Fisheries Limited<br>
                                                                            6-21-7, East Point Colony,<br>
                                                                            Visakhapatnam - 530 017.<br>
                                                                            ANDHRA PRADESH ,INDIA<br> -->
                                                                            </div>
                                                                        </td>
                                                                        <td style="text-align: right;">
                                                                            <div class="mb-1 d-block">Date: <?php echo strtoupper(date('dS F, Y', strtotime($result->PoDate))); ?></div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="text-center">
                                                                            <span style="font-size: 20px;border-bottom: 2px solid #333333;"><b> PURCHASE ORDER - <?php echo $result->PoNumber ?></b></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">
                                                                            <div><?php echo $result->ConsigneeName.', '.$result->ConsigneeAddress ?>. we hereby confirm having bought from you the following:</div>
                                                                        </td>
                                                                    </tr>

                                                                    <?php 
                                                                        foreach ($resItemsGroup as $ItemsGroup) {
                                                                            echo '
                                                                                <tr>
                                                                                    <td colspan="2">
                                                                                        <div>
                                                                                            PRODUCT : '. $ItemsGroup->ItemDescription .'
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                
                                                                                <tr>
                                                                                    <td colspan="2">
                                                                                        <table class="w-100">
                                                                                            <tr>';                                                                                
                                                                                                    foreach ($resLableKeys as $keyVal){
                                                                                                        echo '<td><b>'. $resTableLabels->$keyVal .'</b></td>';
                                                                                                    }
                                                                                            echo '
                                                                                            </tr>';                                                                                                
                                                                                                    $j = 1;                                                                                  
                                                                                                    foreach ($ItemsGroup->ItemsList as $ListItems) {
                                                                                                        $count = count($ItemsGroup->ItemsList);
                                                                                                        if ($count != $j) {
                                                                                                            echo '<tr>';                                                                                                
                                                                                                            foreach ($resLableKeys as $keyVal) {                                                                                                        
                                                                                                                if(isset($ListItems->$keyVal) && !empty($ListItems->$keyVal)) {
                                                                                                                    echo '<td>' . $ListItems->$keyVal . '</td>';
                                                                                                                }
                                                                                                            }
                                                                                                            echo '</tr>';
                                                                                                        } 
                                                                                                        else { // Last Record for Subtotal                                                                                                    
                                                                                                            if ($ItemsGroup->ShowSubTotal == true) {
                                                                                                                echo '<tr>';
                                                                                                                foreach ($resLableKeys as $key => $keyVal) {
                                                                                                                    echo '<td>' . $ListItems->$keyVal . '</td>';
                                                                                                                }
                                                                                                                echo '</tr>';
                                                                                                            } 
                                                                                                        }
                                                                                                        $j++; 
                                                                                                    }
                                                                                        echo '
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>';
                                                                        }  
                                                                    ?>

                                                                    <tr>
                                                                        <td style="height: 5px;">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <table class="w-100">
                                                                                <tr>
                                                                                    <td>QUANTITY </td><td>: <?php echo $resGrandTotal->FWeightLB ?> LBS.</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>BRAND </td>
                                                                                    <td>: 
                                                                                        <?php 
                                                                                            foreach ($resItemsGroup as $ItemsGroup) {
                                                                                                echo  $ItemsGroup->Brand;
                                                                                            }
                                                                                        ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>PACKING </td>
                                                                                    <td>: 
                                                                                        <?php 
                                                                                            foreach ($resItemsGroup as $ItemsGroup) {
                                                                                                echo  $ItemsGroup->PackingStyle;
                                                                                            }
                                                                                        ?>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>SHIPMENT </td><td>: <?php echo strtoupper(date('dS F Y.', strtotime($result->ShipByDate))) ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>DESTINATION </td><td>: <?php echo $result->FinalDestination .' ('. $result->DeliveryTerms .')';?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>PAYMENT </td><td>: <?php echo $result->PaymentTerms ?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    
                                                                    <tr>
                                                                        <td colspan="2"><div style="margin-top: 15px;">For <?php echo $result->ConsigneeName ?>.,</div></td>
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