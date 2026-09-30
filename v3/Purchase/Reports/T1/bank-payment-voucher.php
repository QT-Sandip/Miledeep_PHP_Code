<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$SupplierID = isset($_GET["SupplierID"]) && !empty($_GET["SupplierID"]) ? $_GET["SupplierID"] : "";
$PayDate = isset($_GET["PayDate"]) && !empty($_GET["PayDate"]) ? $_GET["PayDate"] : "";
$VoucherNo = isset($_GET["Voucher"]) && !empty($_GET["Voucher"]) ? $_GET["Voucher"] : "";
$BillerType = isset($_GET["BillerType"]) && !empty($_GET["BillerType"]) ? $_GET["BillerType"] : "";
$AgentID = isset($_GET["AgentID"]) && !empty($_GET["AgentID"]) ? $_GET["AgentID"] : "";


if (isset($_GET['SupplierID']) && !empty($_GET['SupplierID']) && isset($_GET['PayDate']) && !empty($_GET['PayDate']) && isset($_GET['Voucher']) && !empty($_GET['Voucher']) && isset($_GET['BillerType']) && !empty($_GET['BillerType']) ) {

    $SupplierID = trim($_GET['SupplierID']);
    $PayDate = trim($_GET['PayDate']);
    $VoucherNo = trim($_GET['Voucher']);
    $BillerType = trim($_GET['BillerType']);
    $AgentID = trim($_GET['AgentID']);
    
    $api = API_PAYMENT_VOUCHER;

    $bodyContent = array(
        "SupplierID" => $SupplierID,
        "PayDate" => $PayDate,
        "VoucherNo" => $VoucherNo,
        "BillerType" => $BillerType,
        "AgentID" => $AgentID
    );
    
    $resPayment = CallAPI($api, "GET", "", $bodyContent);

   
    $resultBankPayment = $resPayment->Data->PaymentVoucherReportData; 
    $resultBankPaymentBillNos = $resPayment->Data->BillNumbers; 

      json_encode($resultBankPayment, JSON_PRETTY_PRINT);
     

} else {

}


?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>
        <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>"  rel="stylesheet" type="text/css">

        <style>
            h1{
                font-size: 27px;
                color: #000000;
            }
            .w495{
                width: 49.5%;
            }
            .w1{
                width: 1%;
            }
            .text-right{
                text-align: right;
            }
            td {
                padding: 2px 3px;
            }
             @media print {
                .page {
                    page-break-before: always;
                }
            }
            .page {
                page-break-before: always; /* Force a page break before this content */
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
                                                                                
                                                <div  class="pdf-content" id="printArea">
                                                    <div style="text-align: center; margin-bottom: 25px;">
                                                        <button class="generate" id="generate" onclick="downloadpdf('BANK-PAYMENT-VOUCHER .pdf')">Download PDF</button>
                                                    </div>
                                                    
                                                    <div id="printTable">

                                                        <div class="page">
                                                            <table class="tblMain w100 bt bl bb br">

                                                                            <tr>
                                                                                <td class="p-0 br ">
                                                                                    <table class="tblHeader w100">
                                                                                        <tr class="trimg">
                                                                                            <td class="text-left " style="padding: 0px 5px" >
                                                                                                <img src="<?php echo $resultBankPayment->LogoUrl; ?>" style="height:45px;" /> 
                                                                                            </td>
                                                                                            <td class="text-right" style="vertical-align: middle;"></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td colspan="2" class="text-center">
                                                                                                <h1 style="margin-bottom: 2px;" class="bold">  <?php echo $resultBankPayment->CompanyName; ?> </h1>
                                                                                                <div class="f-16">
                                                                                                <?php echo nl2br($resultBankPayment->Address); ?>
                                                                                                  <br>  BANK PAYMENT VOUCHER 
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>                                                                                            
                                                                                            <td class="text-left">Voucher :<?php echo $VoucherNo; ?> , Cheque No : <?php echo $resultBankPayment->ChequeNO; ?></td>
                                                                                            <td  class="text-right">Payment Date : <?php echo date('d-m-Y',strtotime($PayDate)); ?></td>
                                                                                        </tr>                                                                                                             
                                                                                    </table>   
                                                                                    <table class="bt  w100">
                                                                                        <tr class=" bb">
                                                                                            <td class="br text-center" style="width:20%">Serial No</td>
                                                                                            <td class="br text-center" style="width:60%">Account to be Dedited</td>
                                                                                            <td class="text-center" style="width:20%">Amount </td>
                                                                                        </tr> 
                                                                                        <tr class=" bb">
                                                                                            <td class="br text-center" >&nbsp;</td>
                                                                                            <td class="br text-center">
                                                                                                <div class="">
                                                                                                <?php 
                                                                                                if(isset($_GET['AgentID']) && $_GET['AgentID']>0){
                                                                                                    echo $resultBankPaymentBillNos->UserName;
                                                                                                }else{
                                                                                                    echo $resultBankPaymentBillNos->SupplierName; } ?><br>
                                                                                                    Towards Part / Full Payment of SHRIMP Purchase For The Following Bought Note Numbers: <br>
                                                                                                    <?php echo $resultBankPaymentBillNos->BillNos; ?> <br><br><br><br>
                                                                                                </div>
                                                                                            </td>
                                                                                            <td class=" text-right"><?php echo $resultBankPayment->Amount; ?></td>
                                                                                        </tr> 
                                                                                        <tr class=" bb">
                                                                                            <td class="br text-center"  colspan="2">Total</td>
                                                                                            <td class=" text-right"><?php echo $resultBankPayment->Amount; ?></td>
                                                                                        </tr> 
                                                                                        
                                                                                        <tr class=" bb">
                                                                                            <td class="br"  colspan="2">
                                                                                                <div>
                                                                                                    Rupees: (In Words) <br>
                                                                                                    <?php echo $resultBankPayment->AmountinRupees; ?>
                                                                                                </div>
                                                                                            </td>
                                                                                            <td class=" text-right">Received Payment</td>
                                                                                        </tr> 
                                                                                        <tr class="">
                                                                                            <td class="text-center p-0" colspan="6">
                                                                                                <table class="w100">
                                                                                                    <tr>
                                                                                                        <td class="br bb" style="width:20%">&nbsp;<br><br><br></td> 
                                                                                                        <td class="br bb" style="width:20%">&nbsp;</td>
                                                                                                        <td class="br bb" style="width:20%">&nbsp;</td>
                                                                                                        <td class="br bb" style="width:20%">&nbsp;</td>
                                                                                                        <td class="bb" style="width:20%">&nbsp;</td>
                                                                                                    </tr>
                                                                                                    <tr>

                                                                                                        <td class="br">Cashier Signature</td> 
                                                                                                        <td class="br">Authorized Sign</td>
                                                                                                        <td class="br">Authorized Sign</td>
                                                                                                        <td class="br">Directory</td>
                                                                                                        <td >Receipient</td>
                                                                                                    </tr>
                                                                                                </table>
                                                                                            </td>
                                                                                        </tr> 
                                                                                    </table>               
                                                                                </td>
                                                                            </tr>     
                                                            </table>
                                                        </div>
                                                       
                                                        
                                                        
                                                    </div>  <!-- Actual Page Content -->
                                                    

                                                    
                                                    

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
    orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
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
            // scale: 0.61, // a3
             scale: 0.43, // a3
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

            for (var i = 1; i <= pdf.internal.getNumberOfPages(); i++) {
                pdf.setPage(i);
                pdf.setFontSize(12);

                // Calculate the Y position for the footer
                var footerY = pHeight - marginBottom + gap;

                // Calculate the Y position for the page number text
                var pageNumberY = footerY + (footerHeight / 2);

                // pdf.setDrawColor(0); // Set border color to black
                // // pdf.line(margin, footerY, pWidth - margin, footerY); // Draw border at the top of the footer
                // pdf.line(1, 1, 1, 1); // Draw border at the top of the footer


                pdf.text(
                    pWidth / 2,
                    pageNumberY,
                   'Page ' + i + ' of ' + pdf.internal.getNumberOfPages() + " - " + invNo,
                    null,
                    null,
                    'center'
                );
            }

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

    </body>
</html>