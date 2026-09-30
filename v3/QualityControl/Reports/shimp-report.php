<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$QueryParms = $_GET['ID'];
if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);        
	$queries = array();
	parse_str($QueryParms, $_GET);
}

// echo $_GET["PoID"];
// echo $_GET["RID"];
// echo $_GET["ReportName"];

if(isset($_GET['PoID']) && !empty($_GET['PoID'])) {

    $OrderID = trim($_GET['ID']);

    $api = API_QC_SIMPV1;

    $bodyContent = array("OrderID" => $_GET["PoID"]);
    $resShimp = CallAPI($api, "GET", "", $bodyContent);

    $List = $resShimp->Data;

    $countList =count($List);

//    echo json_encode($resShimp, JSON_PRETTY_PRINT);

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
        .f800{
            font-weight: 800;
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
                                        <button class="generate" id="generate" onclick="downloadpdf('<?php echo $resShimp->FileName ?>_SIMP.pdf')">Download PDF</button>
                                        <!-- <button class="generate" id="generate" onclick="downloadpdf('filename')">Download PDF</button> -->
                                    </div>

                                    <div id="printTable">

                                        <!----------------------------------------- Page 1 ---------------------->

                                        <div class="page f-12 page-break" id="page1-content">
                                            <table class="tblMain w100 bt bl bb br">
                                                <tr>
                                                    <td class="text-center f-14">Model Transshipping Certificate for Tracebility</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">{ } Wild Harvest or { # } Farm Raised</td>
                                                </tr>
                                                <tr>
                                                    <td class="f-12 f800">Referenced Unique Catch Document Identifier</td>
                                                </tr>
                                                <?php
                                                    foreach($List as $item) {
                                                        echo '<tr><td>'.$item->SNO.'. '.$item->Tracebility.'</td></tr>';
                                                    }
                                                ?>
                                                <tr>
                                                    <td class="f-12 f800">Port or Storage Location of Loading / Unloading</td>
                                                </tr>
                                                <?php
                                                    for ($i = 1; $i <= $countList; $i++) {
                                                        echo '<tr>
                                                                <td>'.$i.'. '.$resShimp->ExpName.'</td>
                                                            </tr>';
                                                    }                                                      
                                                ?>
                                            </table>
                                            <table class="tblMain w100 bt0 bl bb br">
                                                <tr class="f-12 bold">
                                                    <td class="f800" style="width: 26%;">Name of Vessel/Cold Storage Unloaded from</td>
                                                    <td class="f800" style="width: 24%;">Unique Unloaded Vessel Identifier (registration, documentation, or license number)</td>
                                                    <td class="f800" style="width: 50%;">Date(s) of Unloading/Loading - MM/DD/YYY</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <?php echo $resShimp->ExpName ?>, <br>
                                                        <?php echo  nl2br($resShimp->ExpAddress); ?>
                                                    </td>
                                                    <td class="text-center"><?php echo $resShimp->ExpUsfdaNo ?></td>
                                                    <td class="text-center"><?php echo $resShimp->LoadingDate ?></td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td class="f800">Name of Vessel/Cold Storage Loaded to</td>
                                                    <td class="f800" colspan="2">Unique  Receiving Vessel Identifier (registation, documentation, or license number)</td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td style="line-height: 30px;"><?php echo $resShimp->VesselName ." " .$resShimp->VesselNO ?></td>
                                                    <td style="line-height: 30px;" colspan="2"><?php echo $resShimp->BillOfLading ?></td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td class="f800">Commodity Scientific Name</td>
                                                    <td class="f800">Commodity Market Name</td>
                                                    <td class="f800">Product ASFIS#</td>
                                                </tr>
                                                <?php
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'. '.$item->ScientificName.'  </td>
                                                                <td>'.$item->SNO.'. '.$item->CommodityMarketName.' </td>
                                                                <td>'.$item->SNO.'. '.$item->ProductASFIS.' </td>
                                                            </tr>';
                                                    }                                                      
                                                ?>
                                                
                                                <tr class="f-12 f800">
                                                    <td>Transhipped Weight [{ }lbs or { # }Kg]</td>
                                                    <td>Transshipped Lot Identifier(s)</td>
                                                    <td>Transshipped Product Form</td>
                                                </tr>
                                                <?php 
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'.'; 
                                                                    if(isset($item->FinishedWeight) && !empty($item->FinishedWeight)) {
                                                                        echo $item->FinishedWeight;
                                                                    }else {
                                                                        echo " Weight not entered";
                                                                    }
                                                                echo '
                                                                </td>
                                                                <td>'.$item->SNO.'. '.$item->Tracebility.'</td>
                                                                <td>'.$item->SNO.'. '.$item->Variety.'</td>
                                                            </tr>';
                                                    }
                                                ?>
                                               
                                                <tr class="f-12 f800">
                                                    <td>Production Date(s) - MM/DD/YYYY</td>
                                                    <td># of packages</td>
                                                    <td>Type of Packaging</td>
                                                </tr>
                                                <?php 
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'.  '. date('m/d/Y', strtotime($item->DayCode)).' </td>
                                                                <td>'.$item->SNO.'. '.$item->McsGrade.'</td>
                                                                <td>'.$item->SNO.'. '.$item->PackingStyle.'</td>
                                                            </tr>';
                                                    }
                                                ?>
                                               
                                            </table>
                                            <table class="tblMain w100">
                                                <tr>
                                                    <td class="b-all0">*Note: A Unique Document Identifier is provided by the harvester or landing recipient and should reflect the unique identifier of a catch or landing certificate</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0">*Note: A Lot Identifier is generated by the shipper based on its cargo tracking and record-keeping protocols</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!------------------------------------- END of Page 1 ---------------------->

                                        <br><br><br><br>

                                        <!-------------------------------------  Page 2 ---------------------->
                                        <div class="page f-12 page-break" id="page2-content">
                                            <table class="tblMain w100 bt bl bb br">
                                                <tr>
                                                    <td class="text-center f-14">Model Reprocessing Certificate for Traceability - Primary or Secondary Processing</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">() Wild Harvest or (<span><img src="../../../assets/images/tick.png" style="width:10px" /></span>) Farm Raised</td>
                                                </tr>
                                            </table>
                                            <table class="tblMain w100">
                                                <tr class="f800">
                                                    <td style="width: 250px;">Referenced Unique Catch Document Identifier(s)</td>
                                                    <td colspan="3">Company Product Received From: </td>
                                                </tr>
                                                <?php 
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'. '.$item->Tracebility.'</td>
                                                                <td colspan="3">'.$item->SNO.'. '.$item->Tracebility.', '.$item->FarmerName.', '.$item->AddressContinued.'</td>
                                                            </tr>';
                                                    }
                                                ?>
                                                <tr class="f800">
                                                    <td>Processor Name </td>
                                                    <td style="width: 250px;">Processor Address</td>
                                                    <td style="width: 265px;">Processor Approval or Registration No.</td>
                                                    <td>Phone:   Email:</td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $resShimp->ExpName ?> </td>
                                                    <td>
                                                        <?php echo $resShimp->ExpName ?>, <br />
                                                        <?php echo  nl2br($resShimp->ExpAddress); ?>
                                                    </td>
                                                    <td class="text-center"><?php echo $resShimp->ExpUsfdaNo ?></td>
                                                    <td>Phone: <?php echo $resShimp->ExpFax ?> <br>
                                                        Email: <?php echo $resShimp->ExpMailID ?></td>
                                                </tr>

                                                <tr  class="f800">
                                                    <td>Commodity Scientific Name</td>
                                                    <td colspan="2">Commodity Market Name</td>
                                                    <td>Product ASFIS#</td>
                                                </tr>
                                                <?php
                                                    for ($i = 1; $i <= $countList; $i++) {
                                                        echo '<tr>
                                                                <td>'.$i.'. '.$item->ScientificName.'  </td>
                                                                <td colspan="2">'.$i.'. '.$item->CommodityMarketName.' </td>
                                                                <td>'.$i.'. '.$item->ProductASFIS.' </td>                                                                
                                                            </tr>';
                                                    }                                                      
                                                ?>
                                                <tr  class="f800">
                                                    <td>Received Weight [{ }lbs or {<span><img src="../../../assets/images/tick.png" style="width:10px" /></span>}Kg]</td>
                                                    <td>Received Lot Identifier(s)*</td>
                                                    <td colspan="2">Received Product Form</td>
                                                </tr>
                                                <?php 
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'.'; 
                                                                    if(isset($item->ReceivedQty) && !empty($item->ReceivedQty)) {
                                                                        echo $item->ReceivedQty;
                                                                    }else {
                                                                        echo " Weight not entered";
                                                                    }
                                                                echo '
                                                                </td>
                                                                <td>'.$item->SNO.'. '.$item->Tracebility.'</td>
                                                                <td colspan="2">'.$item->SNO.'. NOT</td>
                                                            </tr>';
                                                    }
                                                ?>
                                                <tr class="f-12 f800">
                                                    <td>Processed Weight [{ }lbs or {<span><img src="../../../assets/images/tick.png" style="width:10px" /></span>}Kg]</td>
                                                    <td>Finished Lot Identifier(s)*</td>
                                                    <td colspan="2">Finished Product Form</td>
                                                </tr>
                                                <?php 
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'.'; 
                                                                    if(isset($item->ReceivedQty) && !empty($item->ReceivedQty)) {
                                                                        echo $item->ReceivedQty;
                                                                    }else {
                                                                        echo " Weight not entered";
                                                                    }
                                                                echo '
                                                                </td>
                                                                <td>'.$item->SNO.'. '.$item->Tracebility.'</td>
                                                                <td colspan="2">'.$item->SNO.'. '.$item->Variety.'</td>
                                                            </tr>';
                                                    }
                                                ?>                                                                                                
                                                <tr class="f800">
                                                    <td colspan="4">Finished Product Weight [{ }lbs or { <span><img src="../../../assets/images/tick.png" style="width:10px" /></span> }Kg]</td>
                                                </tr>
                                                <?php
                                                    foreach($List as $item) {
                                                        echo '<tr><td colspan="4">'.$item->SNO.'. '.$item->FinishedWeight.'</td></tr>';
                                                    }                                                      
                                                ?>
                                                <tr class="f800">
                                                    <td>Production Date(s) - MM/DD/YYYY</td>
                                                    <td># of Package(s)</td>
                                                    <td colspan="2">Type of Packaging</td>
                                                </tr>
                                                <?php 
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'. '. date('m/d/Y', strtotime($item->DayCode)).' </td>
                                                                <td>'.$item->SNO.'. '.$item->McsGrade.'</td>
                                                                <td colspan="2">'.$item->SNO.'. '.$item->PackingStyle.'</td>
                                                            </tr>';
                                                    }
                                                ?>    
                                                
                                            </table>
                                            <table class="tblMain w100">
                                                <tr>
                                                    <td class="b-all0">*Note: A Unique Document Identifier is provided by the harvester or landing recipient and should reflect the unique identifier of a catch or landing certificate</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0">*Note: A Lot Identifier is generated by the processor based on its product tracking and record-keeping protocols</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!-------------------------------------  END Page 2 ---------------------->

                                        <br><br><br><br>

                                        <!-------------------------------------  Page 3 ---------------------->
                                        <div class="page f-12" id="page3-content">
                                            <table class="tblMain w100 bt bl br">
                                                <tr>
                                                    <td class="text-center f-14" colspan="2">Model Catch Certificate for Traceabilty - Harvest and Landing/Receipt</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0 p-0 ">
                                                        <table class="subtable w100">
                                                            <tr class="f800">
                                                                <td>(1) Unique Catch or Production Document Identifier(s)*</td>
                                                            </tr>
                                                            <?php
                                                                $i = 1;
                                                                foreach($List as $item) {
                                                                    if($i == $countList) {
                                                                        $tdClass = "bb0";
                                                                    } else{
                                                                        $tdClass = "";
                                                                    }
                                                                    echo '<tr><td class="'.$tdClass.'">'.$item->SNO.'. '.$item->Tracebility.'</td></tr>';
                                                                    $i++;
                                                                }
                                                            ?>
                                                        </table>
                                                    </td>
                                                    <td class="bb0">
                                                        ( ) Wild Capture Fishery (Complete Section 2) <br />
                                                        (<span><img src="../../../assets/images/tick.png" style="width:10px" /></span>) Farm Raised (Complete Section 3)
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="tblMain w100 ">
                                                <tr>
                                                    <td colspan="2" class="f800">(2) Complete this section for wild capture fish products</td>
                                                </tr>
                                                <tr>
                                                    <td  style="width: 50%;">
                                                        <div>Flag State of vessel : </div>
                                                        <div class="text-center mt2">Not Applicable</div>
                                                    </td>
                                                    <td>
                                                        <div>Name of Harvesting Vessel** :</div>
                                                        <div class="text-center mt2"> Not Applicable</div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div>Vessel Registration or Documentation Number** :</div>
                                                        <div class="text-center mt2">Not Applicable</div>
                                                    </td>
                                                    <td>
                                                        <div>Fishing Permit Number** :</div>
                                                        <div class="text-center mt2"> Not Applicable </div>                                                        
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div>Fishing/Catch Area:</div>
                                                        <div class="text-center mt2">Not Applicable</div>
                                                    </td>
                                                    <td>
                                                        <div>Fishing Gear:</div>
                                                        <div class="text-center mt2">Not Applicable</div>
                                                    </td>
                                                </tr>
                                            </table>

                                            <table class="tblMain w100 ">
                                                <tr>
                                                    <td colspan="2" class="f800">(3) Complete this section for farm raised fish products </td>
                                                </tr>
                                                <tr>
                                                    <td>State of jurisdiction :</td>
                                                    <td>Facility Licence or Authorization**</td>
                                                </tr>
                                                <?php
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'. IN (INDIA)</td>
                                                                <td>'.$item->SNO.'. '.$item->Tracebility.'</td>
                                                            </tr>';
                                                    }
                                                ?>
                                                
                                                <tr>
                                                    <td colspan="2" class="f800">Name and Location of Aquaculture Facility**</td>
                                                </tr>
                                                <?php 
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td colspan="2">'.$item->SNO.'. '.$item->Tracebility.', '.$item->FarmerName.', '.$item->AddressContinued.'</td>
                                                            </tr>';
                                                    }
                                                ?>
                                            </table>
                                            <table class="tblMain w100 ">
                                                <tr>
                                                    <td colspan="3" class="f800">(4) Complete this section For receipt of fish products </td>
                                                </tr>
                                                <tr class="f800">
                                                    <td>Name of Recipient, Processor or Buyer</td>
                                                    <td>Telephone: <?php echo $resShimp->ExpFax ?></td>
                                                    <td>Business Address</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <?php echo $resShimp->ExpName ?>, <br>
                                                        <?php echo  nl2br($resShimp->ExpAddress); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $resShimp->RecipientName ?> <br>
                                                        Email:  <?php echo $resShimp->ExpMailID ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $resShimp->ExpName ?>, <br>
                                                        <?php echo  nl2br($resShimp->PackerAddress); ?>
                                                    </td>
                                                </tr>
                                                <tr class="f800">
                                                    <td>Receiving Facility or Vessel</td>
                                                    <td>Date of Landing/Transshipment (MM/DD/YYYY)</td>
                                                    <td>Landing Port or Delivery Location </td>
                                                </tr>
                                                <tr>
                                                    <td rowspan="<?php echo $countList + 1  ?>">
                                                        <?php echo $resShimp->ExpName ?>, <br>
                                                        <?php echo  nl2br($resShimp->ExpAddress); ?>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'. '.date('m/d/Y', strtotime($item->DayCode)).'</td>
                                                                <td>'.$item->SNO.'. '.$resShimp->ExpName.' </td>
                                                            </tr>';
                                                    }
                                                ?>
                                                <tr class="f800">
                                                    <td>Species of Fish (ASFIS 3 alpha code)</td>
                                                    <td>Landed of Delivered Weight</td>
                                                    <td>Product Form</td>
                                                </tr>
                                                <?php
                                                    foreach($List as $item) {
                                                        echo '<tr>
                                                                <td>'.$item->SNO.'. '.$item->ProductASFIS.'</td>
                                                                <td>'.$item->SNO.'. '.$item->ReceivedQty.' { }lb or {<span><img src="../../../assets/images/tick.png" style="width:10px" /></span>} kg</td>
                                                                <td>'.$item->SNO.'. NOT</td>
                                                            </tr>';
                                                    }
                                                ?>
                                                
                                            </table>
                                            <table class="tblMain w100">
                                                <tr>
                                                    <td class="b-all0">*Note: Unique Document Identifier is provided by the harvester or landing recipient or competent authority </td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0">**Not required if completing an aggregated catch certificate for small scale vessels or aquaculture facilities </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!-------------------------------------  END Page 3 ---------------------->

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.js" integrity="sha512-VqCeCECsaE2fYTxvPQk+OJ7+SQxzI1xZ6IqxuWd0GPKaJoeSFqeakVqNpMbx1AArieciBF71poL0dYTMiNgVxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    // function generatePDF(filename) {
    //         window.jsPDF = window.jspdf.jsPDF;
    //         var pdf = new jsPDF({
    //             orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
    //             unit: 'px', // Measurement unit
    //             format: 'a4', // 'a3', // Page size format, 'a4' for A4 size
                
    //         });

    //         function addElementToPDF(index) {
    //             var elementId = '#page' + (index + 1) + '-content';
    //             // var element =  document.getElementById(elementId); //$(elementId);
    //             var element = $(elementId);

    //             html2canvas(element[0], {
    //                 scale: 0.77, // Adjust the scale as needed
    //                 // scale: 0.7, // Adjust the scale as needed
    //                 removeContainer: false
    //             }).then(function (canvas) {
    //                 var imgData = canvas.toDataURL('image/png');

    //                 // Add the image to the PDF
    //                 pdf.addImage(imgData, 'PNG', 10, 10);

    //                 if (index < 2) {
    //                     pdf.addPage();
    //                     addElementToPDF(index + 1);
    //                 } else {
    //                     pdf.save(filename);
    //                 }
    //             });

    //             // pdf.html(element, {
    //             //     html2canvas: {
    //             //         // scale: 0.61, // a3
    //             //         scale: 0.43, // a3
    //             //         // width: contentWidth,
    //             //         // height: contentHeight,
    //             //         removeContainer: false // for analyzing iframe
    //             //     },
    //             //     x: 0,
    //             //     margin: [15, 0, 30, 0], // Adds the margin to the PDF view 
    //             //     autoPaging: 'text',
    //             //     callback: function () {

    //             //         if (index < 2) {
    //             //             pdf.addPage();
    //             //             addElementToPDF(index + 1);
    //             //         } else {
    //             //             pdf.save('multi_page_with_styles.pdf');
    //             //         }
                       
    //             //         // pdf.save(fileName);
    //             //     }
    //             // });               
    //         }

    //         addElementToPDF(0);
    //     }

        

        


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

        // Define the HTML element you want to capture (e.g., a table with id "printTable")
        // var element1 = document.getElementById("page1-content");
        // var element2 = document.getElementById("page2-content");
        // var element3 = document.getElementById("page3-content");
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

        $(".page-break").height(1320); // 1410

        filename = 't';

        // var element1 = document.getElementById("page1-content");
        // var element2 = document.getElementById("page2-content");
        // var element3 = document.getElementById("page3-content");
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
                scale: 0.42, // a3
                width: contentWidth,
                height: contentHeight,
                removeContainer: false // for analyzing iframe
            },
            x: x,
            margin: [15, 0, 30, 5], // Adds the margin to the PDF view 
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