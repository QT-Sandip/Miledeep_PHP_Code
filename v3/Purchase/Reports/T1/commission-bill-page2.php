<?php



// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);



include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';




 $ArrivalBillID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";



 if (isset($_GET['ID']) && !empty($_GET['ID'])) {



   $ArrivalBillID = trim($_GET['ID']);



     $api = API_RM_BILLS;



   $bodyContent = array("ArrivalID" => $ArrivalBillID);

    $resPostPurchaseBillPrint = CallAPI($api, "GET", "", $bodyContent);



      //var_dump($resPostPurchaseBillPrint); exit;



     $result = $resPostPurchaseBillPrint->Data; 

     $resPurchaseData = $resPostPurchaseBillPrint->Data->ArrivalData;

     $resPurchaseBreakUpData = $resPostPurchaseBillPrint->Data->BreakUpData;

     $resGrandTotal = $resPostPurchaseBillPrint->Data->GrandTotal;

     $resBillerDetails = $resPostPurchaseBillPrint->Data->BillerDetails;

    $c_billDate = new DateTime($resPurchaseData->BillDate);              
    $c_startDate = new DateTime('2025-08-01');

    $billDateLessThanAugOne = 0;
    if ($c_billDate >= $c_startDate) {
        $billDateLessThanAugOne = 1;
    }

    //  echo json_encode($resPurchaseData, JSON_PRETTY_PRINT);

     //echo "<pre>"; print_r(); echo "</pre>";

 } else {



 }



$lable_Harvest_Expenses = "Harvest Expenses";
$lable_Harvest = "Harvest";
if($billDateLessThanAugOne == 1){
    $lable_Harvest_Expenses = "Commission";
    $lable_Harvest = "Rate";    
}

?>



<!doctype html>

<html lang="en">



    <head>

        

        <?php include_once ROOT . '/includes/head.php' ?>

        <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>"  rel="stylesheet" type="text/css">



        <style>

            h1{

                font-size: 20px;

                color: #000000;

            }

            .w495{

                width: 48.5%;

            }

            .w1{

                width: 3%;

            }

            .f-800{

                font-weight: 800;

            }

            .text-right{

                text-align: right;

            }

            td {

                padding: 0px 3px;

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

                                                        <button class="generate" id="generate" onclick="downloadpdf('Commision-bill-<?php echo $resPurchaseData-> ArrivalNumber; ?>.pdf')">Download PDF</button>

                                                    </div>

                                                    

                                                    <div id="printTable">



                                                        <div class="page">

                                                            <table class="tblMain w100 bt bl bb br">

                                                                <tr>

                                                                    <td class="text-center w495" style="padding-right: 0px;padding-top: 3px;padding-left:5px">

                                                                        <div class="bt bl bb br f-800 f-12">Original</div>

                                                                    </td>

                                                                    <td class="w1">&nbsp;</td>

                                                                    <td class="text-center w495" style="padding-left: 0px;padding-right:5px;padding-top: 3px;">

                                                                        <div class="bt bl bb br f-800 f-12">Duplicate</div>

                                                                    </td>

                                                                </tr>

                                                                <tr>

                                                                    <td colspan="3" class="bl bb br f-13" style="padding: 2px 5px 5px 5px;">

                                                                        <table class=" bl bb w100">                                                                        

                                                                            <tr>

                                                                                <td class="w495 bt p-0">

                                                                                    <table class="w100">

                                                                                        <tr>

                                                                                            <td class="p-0 br ">

                                                                                                <table class="tblHeader w100 f-13 f-800">

                                                                                                    <tr>

                                                                                                        <td colspan="2" style="padding: 0px 3px">GST. <?php echo $resBillerDetails->GSTNumber ?></td>

                                                                                                    </tr>

                                                                                                    <!-- <tr class="trimg">

                                                                                                        <td colspan="2" class="text-left " style="padding: 0px 5px" >                                                                                                            

                                                                                                            <img src="<?php echo $resBillerDetails->LogoUrl ?>" style="height:45px;" /> 

                                                                                                        </td>

                                                                                                    </tr>

                                                                                                    <tr>

                                                                                                        <td colspan="2" class="text-center">

                                                                                                            <h1 style="margin-bottom: 2px;" class="bold">  <?php echo $resBillerDetails->CompanyName ?> </h1>

                                                                                                            <p class="f-16" style="margin-bottom: 2px;">BOUGHT NOTE</p>

                                                                                                        </td>

                                                                                                    </tr> -->

                                                                                                    <tr class="trimg">

                                                                                                        <td colspan="2" class="text-left " style="padding: 2px 5px;">

                                                                                                            <!-- <img src="<?php // echo ROOT_URL ?>assets/images/logo-Devi.png" style="height:45px;float: left" />  -->

                                                                                                            <img src="<?php echo $resBillerDetails->LogoUrl ?>" style="height:45px;float: left" /> 

                                                                                                            <div class="text-center">

                                                                                                            <h1 style="margin-bottom: 2px;" class="bold">  <?php echo $resBillerDetails->CompanyName ?> </h1>

                                                                                                            <p class="f-14" style="margin-bottom: 2px;">BOUGHT NOTE</p>

                                                                                                            </div>

                                                                                                        </td>

                                                                                                    </tr> 

                                                                                                    <tr>

                                                                                                        <td>Bill No: <?php echo $resPurchaseData->ArrivalNumber ?></td>

                                                                                                        <td class="text-right" style="width: 190px;">Bill Date: <?php echo $resPurchaseData->BillDate ?></td>

                                                                                                    </tr>

                                                                                                    <tr>

                                                                                                        <td colspan="2">Supplier: <?php echo $resPurchaseData->BillName ?></td>

                                                                                                        

                                                                                                    </tr>

                                                                                                    <tr><td>Address: <?php echo $resPurchaseData->SupplierAddress ?></td>

                                                                                                    <td class="text-right">

                                                                                                            <?php 

                                                                                                                if(isset($resPurchaseData->PhoneNumber) && !empty($resPurchaseData->PhoneNumber)) {

                                                                                                                    echo "Phone: ".$resPurchaseData->PhoneNumber;

                                                                                                                }

                                                                                                            ?>

                                                                                                        </td>

                                                                                                    </tr>

                                                                                                    <tr>

                                                                                                        <td style="text-align: left">PAN: <?php echo $resPurchaseData->AgentPanNumber ?></td>                                                                                                       

                                                                                                        <td style="text-align: right">

                                                                                                            <?php 

                                                                                                                if(isset($resPurchaseData->GSTNumber) && !empty($resPurchaseData->GSTNumber)) {

                                                                                                                    echo "GST: ".$resPurchaseData->GSTNumber;

                                                                                                                }

                                                                                                            ?>

                                                                                                        </td> 

                                                                                                    </tr>                        

                                                                                                </table>   

                                                                                                <table class="bt  w100">

                                                                                                    <tr class="f-12 f-800 bb">

                                                                                                       

                                                                                                        <td class="br text-center" style="width:34%">Variety</td>

                                                                                                        <td class="br text-center" style="width:26%">Quantity - In kgs</td>

                                                                                                        <td class="br text-center" style="width:16%"><?php echo $lable_Harvest; ?></td>

                                                                                                        <td class="text-center" style="width:24%">Amount In Rs</td>

                                                                                                    </tr> 

                                                                                                    <tr class="">

                                                                                                        <td class="br text-center" ><?php echo $lable_Harvest_Expenses; ?></td>                   

                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>

                                                                                                        <td class="br text-right"><?php echo $resGrandTotal-> HarvestCommission ?></td>

                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpanses?></td>

                                                                                                    </tr> 



                                                                                                    <tr class="bb">

                                                                                                        <td class="br text-center" style="height: 240px;">&nbsp;</td>                   

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class=" text-right">&nbsp;</td>

                                                                                                    </tr> 

                                                                                                 

                                                                                                    <tr class=" bb">

                                                                                                        <td class="br text-center" colspan="1">Total</td>

                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpanses?></td>

                                                                                                    </tr> 
                                                                                                    <?php if( $billDateLessThanAugOne ){ 
                                                                                                            $amt_pay_aft_tds_lable = "Amount Payable After TDS";
                                                                                                            $amt_HarvestExpAfter = $resGrandTotal->HarvestExpAfterCommision;
                                                                                                        ?>
                                                                                                        <tr class=" bb">

                                                                                                            <td class="br text-center" colspan="1">Less TDS @ 2 %</td>

                                                                                                            <td class="br text-right">&nbsp;</td>

                                                                                                            <td class="br text-right">&nbsp;</td>

                                                                                                            <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpansesCommision?></td>

                                                                                                        </tr>

                                                                                                    <?php }else{ 
                                                                                                        $amt_pay_aft_tds_lable = "Amount Payable After TDS";
                                                                                                        $amt_HarvestExpAfter = $resGrandTotal->HarvestExpAfterTds;
                                                                                                        ?>
                                                                                                         <tr class=" bb">

                                                                                                            <td class="br text-center" colspan="1">Less TDS @ 0.1 %</td>

                                                                                                            <td class="br text-right">&nbsp;</td>

                                                                                                            <td class="br text-right">&nbsp;</td>

                                                                                                            <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpansesTds?></td>

                                                                                                        </tr>
                                                                                                    <?php } ?>
                                                                                                    <tr class=" bb">

                                                                                                        <td class="br text-center" colspan="2"><?php echo $amt_pay_aft_tds_lable; ?></td>

                                                                                                        <!-- <td class="br text-right"></td> -->

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class=" text-right"><?php  echo  $amt_HarvestExpAfter?></td>

                                                                                                    </tr> 

                                                                                                    <tr class=" bb">

                                                                                                        <td class="br text-center" colspan="1">Amount in Words </td>

                                                                                                        <td class=" text-center" colspan="3"><?php  echo  $resGrandTotal->HarvestExpAmountInWords?></td>

                                                                                                    </tr> 

                                                                                                    <tr class="">

                                                                                                        <td class="text-center p-0" colspan="6">

                                                                                                            <table class="w100">

                                                                                                                <tr>

                                                                                                                    <td class="br bb" style="width:24%">&nbsp;<br><br></td> 

                                                                                                                    <td class="br bb" style="width:36%">&nbsp;</td>

                                                                                                                    <td class="bb" style="width:40%">&nbsp;</td>

                                                                                                                </tr>

                                                                                                                <tr>

                                                                                                                    <td class="br" style="width:24%">Supplier <br>Signature</td>

                                                                                                                    <td class="br" style="width:36%"><br>Checked By</td>

                                                                                                                    <td style="width:40%"><br>Authorized Sign</td>

                                                                                                                </tr>

                                                                                                            </table>

                                                                                                        </td>

                                                                                                    </tr> 

                                                                                                </table>               

                                                                                            </td>

                                                                                        </tr>       

                                                                                    </table>

                                                                                </td>

                                                                                <td class="w1">&nbsp;</td>

                                                                                <td class="w495 bl bt p-0">

                                                                                    <table class="w100">

                                                                                        <tr>

                                                                                            <td class="p-0 br ">

                                                                                                <table class="tblHeader w100 f-13 f-800">

                                                                                                    <tr>

                                                                                                        <td colspan="2" style="padding: 0px 3px">GST. <?php echo $resBillerDetails->GSTNumber ?></td>

                                                                                                    </tr>

                                                                                                    <!-- <tr class="trimg">

                                                                                                        <td colspan="2" class="text-left " style="padding: 0px 5px" >                                                                                                            

                                                                                                            <img src="<?php echo $resBillerDetails->LogoUrl ?>" style="height:45px;" /> 

                                                                                                        </td>

                                                                                                    </tr>

                                                                                                    <tr>

                                                                                                        <td colspan="2" class="text-center">

                                                                                                            <h1 style="margin-bottom: 2px;" class="bold">  <?php echo $resBillerDetails->CompanyName ?> </h1>

                                                                                                            <p class="f-16" style="margin-bottom: 2px;">BOUGHT NOTE</p>

                                                                                                        </td>

                                                                                                    </tr> -->

                                                                                                    <tr class="trimg">

                                                                                                        <td colspan="2" class="text-left " style="padding: 2px 5px;">

                                                                                                            <!-- <img src="<?php // echo ROOT_URL ?>assets/images/logo-Devi.png" style="height:45px;float: left" />  -->

                                                                                                            <img src="<?php echo $resBillerDetails->LogoUrl ?>" style="height:45px;float: left" /> 

                                                                                                            <div class="text-center">

                                                                                                            <h1 style="margin-bottom: 2px;" class="bold">  <?php echo $resBillerDetails->CompanyName ?> </h1>

                                                                                                            <p class="f-14" style="margin-bottom: 2px;">BOUGHT NOTE</p>

                                                                                                            </div>

                                                                                                        </td>

                                                                                                    </tr> 

                                                                                                    <tr>

                                                                                                        <td>Bill No: <?php echo $resPurchaseData->ArrivalNumber ?></td>

                                                                                                        <td class="text-right" style="width: 190px;">Bill Date: <?php echo $resPurchaseData->BillDate ?></td>

                                                                                                    </tr>

                                                                                                    <tr>

                                                                                                        <td colspan="2">Supplier: <?php echo $resPurchaseData->BillName ?></td>

                                                                                                        

                                                                                                    </tr>

                                                                                                    <tr><td>Address: <?php echo $resPurchaseData->SupplierAddress ?></td>

                                                                                                    <td class="text-right">

                                                                                                            <?php 

                                                                                                                if(isset($resPurchaseData->PhoneNumber) && !empty($resPurchaseData->PhoneNumber)) {

                                                                                                                    echo "Phone: ".$resPurchaseData->PhoneNumber;

                                                                                                                }

                                                                                                            ?>

                                                                                                        </td>

                                                                                                    </tr>

                                                                                                    <tr>

                                                                                                        <td style="text-align: left">PAN: <?php echo $resPurchaseData->AgentPanNumber ?></td>                                                                                                       

                                                                                                        <td style="text-align: right">

                                                                                                            <?php 

                                                                                                                if(isset($resPurchaseData->GSTNumber) && !empty($resPurchaseData->GSTNumber)) {

                                                                                                                    echo "GST: ".$resPurchaseData->GSTNumber;

                                                                                                                }

                                                                                                            ?>

                                                                                                        </td> 

                                                                                                    </tr>                        

                                                                                                </table>   

                                                                                                <table class="bt  w100">

                                                                                                     <tr class="f-12 f-800 bb">

                                                                                                       

                                                                                                        <td class="br text-center" style="width:34%">Variety</td>

                                                                                                        <td class="br text-center" style="width:26%">Quantity - In kgs</td>

                                                                                                        <td class="br text-center" style="width:16%"><?php echo $lable_Harvest; ?></td>

                                                                                                        <td class="text-center" style="width:24%">Amount In Rs</td>

                                                                                                    </tr> 

                                                                                                    <tr class="">

                                                                                                        <td class="br text-center" ><?php echo $lable_Harvest_Expenses; ?></td>                   

                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>

                                                                                                        <td class="br text-right"><?php echo $resGrandTotal-> HarvestCommission ?></td>

                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpanses?></td>

                                                                                                    </tr> 



                                                                                                    <tr class="bb">

                                                                                                        <td class="br text-center" style="height: 240px;">&nbsp;</td>                   

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class=" text-right">&nbsp;</td>

                                                                                                    </tr> 

                                                                                                 

                                                                                                    <tr class=" bb">

                                                                                                        <td class="br text-center" colspan="1">Total</td>

                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpanses?></td>

                                                                                                    </tr> 
                                                                                                    <?php if( $billDateLessThanAugOne ){ 
                                                                                                        $amt_pay_aft_tds_lable = "Amount Payable After TDS";
                                                                                                        $amt_HarvestExpAfter = $resGrandTotal->HarvestExpAfterCommision;
                                                                                                        ?>
                                                                                                    <tr class=" bb">

                                                                                                        <td class="br text-center" colspan="1">Less TDS @ 2 %</td>

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class=" text-right"><?php  echo $resGrandTotal->HarvestExpansesCommision?></td>

                                                                                                    </tr>                                                                                                     
                                                                                                     <?php }else{ 

                                                                                                        $amt_pay_aft_tds_lable = "Amount Payable After TDS";
                                                                                                        $amt_HarvestExpAfter = $resGrandTotal->HarvestExpansesTds;
                                                                                                        ?>
                                                                                                    <tr class=" bb">

                                                                                                        <td class="br text-center" colspan="1">Less TDS @ 0.1 %</td>

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpansesTds?></td>

                                                                                                    </tr> 
                                                                                                    <?php } ?>
                                                                                                    <tr class=" bb">

                                                                                                        <td class="br text-center" colspan="2"><?php echo $amt_pay_aft_tds_lable; ?> </td>

                                                                                                        <!-- <td class="br text-right"></td> -->

                                                                                                        <td class="br text-right">&nbsp;</td>

                                                                                                        <td class=" text-right"><?php  echo  $amt_HarvestExpAfter?></td>

                                                                                                    </tr> 

                                                                                                    <tr class=" bb">

                                                                                                        <td class="br text-center" colspan="1">Amount in Words </td>

                                                                                                        <td class=" text-center" colspan="3"><?php  echo  $resGrandTotal->HarvestExpAmountInWords?></td>

                                                                                                    </tr> 

                                                                                                    <tr class="">

                                                                                                        <td class="text-center p-0" colspan="6">

                                                                                                            <table class="w100">

                                                                                                                <tr>

                                                                                                                    <td class="br bb" style="width:24%">&nbsp;<br><br></td> 

                                                                                                                    <td class="br bb" style="width:36%">&nbsp;</td>

                                                                                                                    <td class="bb" style="width:40%">&nbsp;</td>

                                                                                                                </tr>

                                                                                                                <tr>

                                                                                                                    <td class="br" style="width:24%">Supplier <br>Signature</td>

                                                                                                                    <td class="br" style="width:36%"><br>Checked By</td>

                                                                                                                    <td style="width:40%"><br>Authorized Sign</td>

                                                                                                                </tr>

                                                                                                            </table>

                                                                                                        </td>

                                                                                                    </tr> 

                                                                                                </table>               

                                                                                            </td>

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

            scale: 0.61, // a3

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





                // pdf.text(

                //     pWidth / 2,

                //     pageNumberY,

                //    'Page ' + i + ' of ' + pdf.internal.getNumberOfPages() + " - " + invNo,

                //     null,

                //     null,

                //     'center'

                // );

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