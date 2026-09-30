<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$QueryParms = $_GET['Q'];
if ($QueryParms != '') {
    $QueryParms = urlDecode64($QueryParms);
    $queries = array();
    parse_str($QueryParms, $_GET);
}

// echo $_GET["PoID"]."oooo";
// echo $_GET["RID"];
// echo $_GET["ReportName"];

if (isset($_GET['PoID']) && !empty($_GET['PoID'])) {

    // $OrderID = trim($_GET['ID']);

    $api = API_QC_SIMP;

    $bodyContent = array("OrderID" => $_GET["PoID"]);
    $reponse = CallAPI($api, "GET", "", $bodyContent);

    //var_dump($reponse);


    $resShimp = $reponse->OrderDetails;
    $LineItemData = $reponse->LineItemData;
    $Products = $reponse->Products;
    $Tracibility = $reponse->Tracibility;
    $LineItemsPurchaseBillNowiseData = $reponse->LineItemsPurchaseBillNowise;

    $countList = count($LineItemData);

    //    echo json_encode($LineItemData, JSON_PRETTY_PRINT);
    // var_dump($LineItemData);

}

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>" rel="stylesheet"
        type="text/css">

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

                                <div class="pdf-content" id="printArea">
                                    <div style="text-align: center; margin-bottom: 25px;">
                                        <button class="generate" id="generate"
                                            onclick="downloadpdf('<?php echo $resShimp->FileName ?>_SIMP.pdf')">Download
                                            PDF</button>
                                        <!-- <button class="generate" id="generate" onclick="downloadpdf('filename')">Download PDF</button> -->
                                    </div>

                                    <div id="printTable" style="max-width: 1100px;">

                                        <!----------------------------------------- Page 1 ---------------------->

                                        <div class="page f-11 page-break" id="page1-content">

                                            <table class="tblMain w100 bt bl bb br" style="page-break-inside:avoid">
                                                <tr>
                                                    <td style="width: 205px;vertical-align: middle;">INV NO:
                                                        <?php echo $resShimp->InvNumber ?></td>
                                                    <td colspan="4" class="text-center br0"
                                                        style="font-size: 24px;padding: 20px 3px;">AQUACULTURE SIMP DATA
                                                    </td>
                                                    <td class="bl0" style="width: 156px;"></td>
                                                </tr>
                                                <tr>
                                                    <td>IMPORTER: <?php echo $resShimp->ConsigneeName ?></td>
                                                    <td style="width: 195px">U.S. CONSIGNEE:
                                                        <?php echo $resShimp->ConsigneeName ?></td>
                                                    <td colspan="3" class="text-center"> IFTP Permit Name & Number :
                                                        <br>
                                                        <?php echo $resShimp->ConsigneeName ?>,&<?php echo $resShimp->ConsigneeIFTPCODE ?>
                                                    </td>
                                                    <td>BILL OF LADING # <?php echo $resShimp->BillOfLading ?> <br>
                                                        Container # <?php echo $resShimp->ContainerNo ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="p-0">
                                                        <table id="tblHarvest" class="w100">
                                                            <tr>
                                                                <td class="bt0 bl0" style="width: 205px;">HARVESTING
                                                                    COUNTRY: <br> INDIA</td>
                                                                <td class="bt0 br0">WILD CAUGHT OR <br> FARM RAISED:
                                                                    FARM RAISED</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bb0 bl0">Fishing Gear Type: NA</td>
                                                                <td class="bb0 br0">
                                                                    <div class="mgb5">Processing Description:</div>
                                                                    <?php
                                                                    $i = "";
                                                                    foreach ($Products as $item) {
                                                                        echo '<div class="mgb5">' . $i . ' ' . $item->QcDescription . ' </div>';
                                                                        // $i++;
                                                                    }
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                    <td colspan="4" id="tdGeoGraphic">
                                                        <div class="mgb5">GEOGRAPHIC LOCATION</div>
                                                        <div class="mgb5">(IF FARMED RAISED,REGION,STATE,CITY):</div>
                                                        <?php
                                                        $i = 1;
                                                        foreach ($Tracibility as $item) {
                                                            echo '<div class="mgb5">FARM-' . $i . ' ' . $item->TraceNumberWOBatchCode . '-' . $item->AddressContinued . '</div>';
                                                            $i++;
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                                $a = 1;
                                                foreach ($LineItemData as $item) {
                                                    $ReceivedQty = $item->ReceivedQty;
                                                    echo '
                                                            <tr>
                                                                <td>LINE ' . $a . '-Product:<br> HON ' . $item->ScientificName . ' </td>
                                                                <td>SCIENTIFIC SPECIES CODE: ' . $item->ProductASFIS . ' </td>
                                                                <td class="text-center ">'
                                                        . $item->ProductAddress . '
                                                                </td>
                                                                <td class="text-center" style="width:98px">
                                                                    Harvesting Date: <br>' . $item->HarvestDate . '
                                                                </td> 
                                                                
                                                                <td class="text-center" style="width:98px">
                                                                    Receiving Date: <br>' . $item->ReceivingDt . '
                                                                </td>

                                                                <td>Weight in kilograms: <br>' . $item->GroupWeightRcved . '</td>
                                                                
                                                                ';
                                                    // foreach ($ReceivedQty as $Weight) {
                                                    //     echo '  <td>Weight in kilograms: <br>' . $item->Weight . '</td>';
                                                    // }
                                                    echo ' 
                                                                 </tr>';
                                                    $a++;
                                                }
                                                ?>
                                                <!-- <tr>
                                                    <td>LINE 1-Product:<br> HON VANNAMEI </td>
                                                    <td>SCIENTIFIC SPECIES CODE: PNV</td>
                                                    <td class="text-center">
                                                        Aquaculture Facility:farm 1
                                                        KANTHETI BALARAMA RAJU, SITANAGARAM,
                                                        SANTHABOMMALI, SRIKAKULAM DISTRICT,
                                                        ANDHRA PRADESH, INDIA.
                                                        PHONE NUMBER - 9182555248
                                                    </td>
                                                    <td class="text-center">
                                                        Harvesting Date: <br>08.21.2023
                                                    </td>
                                                    <td>Weight in killograms: 1817kgs</td>
                                                </tr> -->
                                            </table>
                                            <br>
                                        </div>

                                        <div id="test"><br></div>
                                        <!------------------------------------- END of Page 1 ---------------------->

                                        <!-- <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br> -->

                                        <!-------------------------------------  Page 2 ---------------------->
                                        <div class="page f-11" id="page2-content">
                                            <table class="tblMain w100 bt bl bb br" nobr="true">
                                                <tr>
                                                    <td colspan="2" class="text-center f-14">Model Reprocessing
                                                        Certificate for Traceability - Primary or Secondary Processing
                                                        (one species per form) </td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 20%;" class="text-center">INV NO:
                                                        <?php echo $resShimp->InvNumber; ?></td>
                                                    <td class="text-center">PO: <?php echo $resShimp->PoNumber; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">( ) Wild Harvest or ( <span><img
                                                                src="../../../assets/images/tick.png"
                                                                style="width:10px" /></span> ) Farm Raised: FARM RAISED
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="tblMain w100 bl bb br">
                                                <tr>
                                                    <td colspan="4" class="bt0">
                                                        <div>(1) Unique Catch or Production Document Identifier*</div>
                                                        <div>(Purchase bill number, Registration Number,Pond id: )
                                                        </div>
                                                    </td>
                                                    <td class="bt0"></td>
                                                    <td class="bt0"></td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 56px;"></td>
                                                    <td>
                                                        Purchase <br>
                                                        bill number
                                                    </td>
                                                    <td style="width: 120px;">
                                                        Registration
                                                        Number
                                                    </td>
                                                    <td>POND ID</td>
                                                    <td>Company Product Received From</td>
                                                    <td style="width: 100px;">PHONE NUMBER</td>
                                                </tr>
                                                <?php
                                                $b = 1;
                                                foreach ($LineItemData as $item) {
                                                    echo '
                                                        <tr>
                                                            <td>FARM-' . $b . '</td>
                                                            <td>' . rtrim($item->PurchaseBillNumber, ",") . '</td>
                                                            <td>' . $item->Tracebility . '</td>
                                                            <td>' . $item->PondNumber . '</td>
                                                            <td style="width:440px">FARM-' . $b . ':' . $item->FarmerName . ',' . $item->AddressContinued . '</td>
                                                            <td>' . $b . '.' . $item->FarmerPhoneNumber . '</td>
                                                        </tr>';
                                                    $b++;
                                                }
                                                ?>
                                                <!-- <tr>
                                                    <td>FARM-2</td>
                                                    <td>1956</td>
                                                    <td>AP047339</td>
                                                    <td>001</td>
                                                    <td>FARM-2: KANTHETI BALARAMA RAJU, SITANAGARAM, SANTHABOMMALI, SRIKAKULAM DISTRICT, ANDHRA PRADESH, INDIA.</td>
                                                    <td>2. 9182555248</td>
                                                </tr> -->

                                                <tr>
                                                    <td colspan="2">
                                                        Processor Name: <br>
                                                        <?php echo $resShimp->ProcessorName ?>
                                                    </td>
                                                    <td colspan="2">
                                                        Processor Address: <br><?php echo $resShimp->ProcessorName ?>,
                                                        <br>
                                                        <?php echo nl2br($resShimp->PackerAddress) ?>,<br>
                                                        <?php echo $resShimp->ExpPhoneNumber ?>,
                                                        <?php echo $resShimp->ExpMailID ?>, <?php echo "08912595672" ?>,
                                                        <?php echo "exports@devifisheries.com" ?>
                                                    </td>
                                                    <td colspan="2">
                                                        Processor Approval or Registration Number:
                                                        <?php echo $resShimp->ExpUsfdaNo ?> (FDA Registration Number)
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">Commodity Scientific Name</td>
                                                    <td>Commodity Market Name : </td>
                                                    <td colspan="3">Product ASFIS#</td>
                                                </tr>
                                                <?php
                                                $c = 1;
                                                foreach ($LineItemData as $item) {
                                                    echo '
                                                            <tr>
                                                                <td colspan="3">FARM-' . $c . ': ' . $item->ScientificName . '</td>
                                                                <td> FARM-' . $c . ':' . $item->CommodityMarketName . ' </td>
                                                                <td colspan="3">FARM-' . $c . ': ' . $item->ProductASFIS . '</td>
                                                            </tr>';
                                                    $c++;
                                                }


                                                ?>
                                            </table>
                                            <table class="tblMain w100  bl br">
                                                <tr>
                                                    <td colspan="3" class="p-0 bt0">
                                                        <table class="tblMain w100">
                                                            <tr>
                                                                <td colspan="3" class="bt0 bl0 br0">Received Weight [ (
                                                                    ) lbs or ( <span><img
                                                                            src="../../../assets/images/tick.png"
                                                                            style="width:10px" /></span> ) kg]</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 60px;" class="bt0 bb0 bl0"></td>
                                                                <td style="width: 133px;" class="bt0 bb0 ">HOSO WEIGHT
                                                                </td>
                                                                <td style="width: 50px;" class="bt0 bb0 br0">GRADE</td>
                                                            </tr>
                                                        </table>

                                                    </td>
                                                    <td class="bt0">
                                                        VARIETY OF
                                                        FINISHED
                                                        PRODUCT
                                                    </td>
                                                    <td class="bt0">Received Lot Identifiers(s)* </td>
                                                    <td class="bt0">Received Product Form:</td>
                                                </tr>
                                                <?php
                                                $d = 1;
                                                foreach ($LineItemData as $item) {

                                                    echo ' 
                                                        <tr>
                                                            <td style="width: 60px;">FARM-' . $d . '</td>
                                                            <td style="width: 133px;">' . $item->GroupWeightRcved . '</td>
                                                            <td style="width: 50px;">' . $item->GradeName . '</td>
                                                            <td> ' . $item->VarietyShortName . '</td>
                                                            <td>FARM ' . $d . ': ' . $item->ReceivedLotIdentifiers . '</td>
                                                            <td>FARM-' . $d . ': HON ' . $item->ScientificName . '</td>
                                                        </tr>';
                                                    $d++;
                                                }
                                                ?>
                                                <!-- <tr>
                                                    <td>FARM-2</td>
                                                    <td>1817Kgs</td>
                                                    <td>16/20</td>
                                                    <td> V.PDTO</td>
                                                    <td>FARM 2: AP047339-001</td>
                                                    <td>FARM-2: HON VANNAMEI</td>
                                                </tr>
                                                -->

                                                <tr>
                                                    <td colspan="4">Finished Product Weight [ ( ) lbs or ( <span><img
                                                                src="../../../assets/images/tick.png"
                                                                style="width:10px" /></span> ) kg ],GRADE & Product</td>
                                                    <td>Finished Product Lot Identifier(s)* with Batch no:</td>
                                                    <td>Finished Product Form:</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 60px;"></td>
                                                    <td>FINISHED PRODUCT WEIGHT</td>
                                                    <td>GRADE</td>
                                                    <td>PRODUCT</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <?php
                                                $e = 1;
                                                foreach ($LineItemsPurchaseBillNowiseData as $item) {
                                                    //$item->TypeProduct . ' ' . $item->SpeciesCode  . ' ' 
                                                    echo '
                                                        <tr>
                                                            <td>FARM-' . $e . '</td>
                                                            <td>' . $item->FinishedWeight . '</td>
                                                            <td>' . $item->GradeName . '</td>
                                                            <td>' . $item->VarietyShortName . '</td>
                                                            <td> ' . $e . '. ' . $item->TracebilityFull . '</td>
                                                            <td style="width: 470px;"> ' . $e . '. ' . $item->Variety . '</td>
                                                        </tr>';
                                                    $e++;
                                                }


                                                ?>
                                                <!-- <tr>
                                                    <td>FARM-2</td>
                                                    <td>1135.000kgs</td>
                                                    <td> 16/20</td>
                                                    <td>V.PDTO</td>
                                                    <td> 2. SKB-AP047339-001</td>
                                                    <td> 2. FROZEN RAW PEELED AND DEVEINED TAIL ON VANNAMEI WHITE - IQF SHRIMPS (V.PDTO)</td>
                                                </tr>-->
                                                <tr>
                                                    <td colspan="4">Production Date(s): </td>
                                                    <td># of Packages (IWP,IVP,Bulk Fish) </td>
                                                    <td>Type of Packaging (IWP,IVP,Bulk Fish,Inner carton,Master Carton)
                                                    </td>
                                                </tr>
                                                <?php
                                                $f = 1;
                                                foreach ($LineItemData as $item) {

                                                    // Determine the correct production date
                                                    $prodDate = "N/A";

                                                    if (!empty($item->ProductionDt) && $item->ProductionDt != '01-01-1970' && $item->ProductionDt != '0000-00-00') {
                                                        $dt = DateTime::createFromFormat('m-d-Y', $item->ProductionDt);
                                                        if ($dt) $prodDate = $dt->format('m-d-Y');
                                                    } elseif (!empty($item->HarvestDate) && $item->HarvestDate != '01-01-1970' && $item->HarvestDate != '0000-00-00') {
                                                        $dt = DateTime::createFromFormat('m-d-Y', $item->HarvestDate);
                                                        if ($dt) $prodDate = $dt->format('m-d-Y');
                                                    }

                                                    echo '
        <tr style="height:20px;">
            <td colspan="4">' . $f . ') ' . $prodDate . ' </td>
            <td>' . $f . ') MASTER CARTONS- ' . $item->McsGrade . '</td>
            <td>' . $f . ') ' . $item->PackingStyle . ' </td>
        </tr>';
                                                    $f++;
                                                }
                                                ?>

                                                <!-- <tr>
                                                    <td colspan="4">2) 08.21.2023 </td>
                                                    <td>2) MASTER CARTONS- 250</td>
                                                    <td>2) 5X2LBS </td>
                                                </tr>
                                                 -->
                                            </table>
                                            <table class="tblMain w100  bl bb br">
                                                <tr>
                                                    <td colspan="4" class="bt0">
                                                        <div>* Note: A Unique Document Identifier is provided by the
                                                            harverster or landing recipient and should reflect the
                                                            unique identifier of a catch or landing certificate.</div>
                                                        <div>* Note: A Lot Identifier is generated by the processor
                                                            based on its products tracking and record-keeping protocols.
                                                        </div>
                                                        <div>* Note: Date Format(Month-Date-Year)</div>
                                                    </td>
                                                </tr>

                                            </table>
                                        </div>
                                        <!-------------------------------------  END Page 2 ---------------------->

                                        <br><br><br><br>



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
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.3.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.js"
    integrity="sha512-VqCeCECsaE2fYTxvPQk+OJ7+SQxzI1xZ6IqxuWd0GPKaJoeSFqeakVqNpMbx1AArieciBF71poL0dYTMiNgVxA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.4.0/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/rasterizehtml/1.3.0/rasterizeHTML.allinone.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js" 
        integrity="sha384-NaWTHo/8YCBYJ59830LTz/P4aQZK1sS0SneOgAvhsIl3zBu8r9RevNg5lHCHAuQ/"
        crossorigin="anonymous"> -->
</script>
<script>
    /*
    function downloadpdf(filename) {
        window.jsPDF = window.jspdf.jsPDF;
        var pdf = new jsPDF({
            orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
            unit: 'px', // Measurement unit
            format: 'a4', // 'a3', // Page size format, 'a4' for A4 size
        });

        function addElementToPDF(index) {
            var elementId = '#page' + (index + 1) + '-content';
            // var element =  document.getElementById(elementId); //$(elementId);
            var element = $(elementId);

            html2canvas(element[0], {
                allowTaint: true,
                dpi: 300,
                letterRendering: true,
                logging: false,

                // scale: 1, // Adjust the scale as needed
                scale: 0.77, // Adjust the scale as needed
                removeContainer: false
            }).then(function(canvas) {
                var imgData = canvas.toDataURL('image/png');

                // Add the image to the PDF
                pdf.addImage(imgData, 'PNG', 10, 10);

                if (index < 1) { // Page No needs to change
                    pdf.addPage();
                    addElementToPDF(index + 1);
                } else {
                    pdf.save(filename);
                }
            });             
        }

        addElementToPDF(0);
    }
*/





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
        $("#status").fadeIn(), $("#preloader").fadeIn();

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




        // var contentHeight = document.querySelector('.page-break').offsetHeight;
        // var contentHeight = element.offsetHeight;
        // const maxHeightPerPage = 827; // For example, assuming each page can hold up to 800px of content
        // //const maxHeightPerPage = 800; // For example, assuming each page can hold up to 800px of content
        // console.log("hi" + contentHeight);
        // var count = 1;
        // var breaks = "<br><br><br>";
        // while (contentHeight < maxHeightPerPage) {


        //     console.log(element);
        //     breaks = breaks + "<br><br><br>";
        //     document.getElementById("test").innerHTML = breaks;
        //     element = document.getElementById("test");
        //     contentHeight = element.offsetHeight;
        //     console.log(element);
        //     console.log(contentHeight);
        //     // if(count>1000){
        //     //     break;
        //     // }
        //     count++;

        // }
        // // Set the maximum height of each page (adjust as needed)


        // // Calculate the number of pages needed
        // var totalPages = Math.ceil(contentHeight / maxHeightPerPage);
        // console.log(contentHeight);
        // console.log(totalPages);
        //totalPages=10;

        //$(".page-break").height(1410);
        $(".page-break").css('min-height', 1410);


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
            margin: [15, 0, 30, 0], // Adds the margin to the PDF view 
            autoPaging: 'text',
            pagesplit: true,
            callback: function(pdf) {

                var timestamp = Date.now();
                var pdfName = fileName;
                addFooters(pdf);
                pdf.save(pdfName);
                setTimeout(function() {
                    $("#status").fadeOut(), $("#preloader").delay(350).fadeOut("slow");
                    window.location.reload();
                }, 2000);
                //pdf.output('dataurlnewwindow');

                // $("#test").html("<br><br>");
                // $(".page-break").height("auto");


            }
        });



        //pdf.output('dataurlnewwindow');




    }

    function addFooters(doc) {
        const pageCount = doc.internal.getNumberOfPages();
        const pageWidth = doc.internal.pageSize.width; // Get the width of the page
        const pageHeight = doc.internal.pageSize.height; // Get the height of the page

        // Set the desired font size
        //doc.setFont("helvetica");
        doc.setFontSize(6);

        for (var i = 1; i <= pageCount; i++) {
            doc.setPage(i);

            // Create the text "Page X/Y"
            const pageNumberText = `Page ${i} / ${pageCount}`;

            // Calculate the center position for the text
            const textWidth = doc.getTextWidth(pageNumberText);
            const xPosition = (pageWidth - textWidth) / 2; // Center horizontally
            const yPosition = pageHeight - 10; // 10 units from the bottom

            doc.text(pageNumberText, xPosition, yPosition);
        }
    }
















    /*
    
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
            //var contentHeight = element.offsetHeight - marginTop - marginBottom;
    
            var contentHeight = document.querySelector('.page-break').offsetHeight;
            var contentHeight = element.offsetHeight;
            const maxHeightPerPage = 800; // For example, assuming each page can hold up to 800px of content
            //const maxHeightPerPage = 800; // For example, assuming each page can hold up to 800px of content
            console.log("hi" + contentHeight);
            var count = 1;
            var breaks = "<br><br><br>";
            while (contentHeight < maxHeightPerPage) {
    
    
                console.log(element);
                breaks = breaks + "<br><br><br>";
                document.getElementById("test").innerHTML = breaks;
                element = document.getElementById("test");
                contentHeight = element.offsetHeight;
                console.log(element);
                console.log(contentHeight);
                // if(count>1000){
                //     break;
                // }
                count++;
    
            }
            // Set the maximum height of each page (adjust as needed)
    
    
            // Calculate the number of pages needed
            var totalPages = Math.ceil(contentHeight / maxHeightPerPage);
            console.log(contentHeight);
            console.log(totalPages);
    
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
    
            pdf.html(element, {
                html2canvas: {
                    // scale: 0.61, // a3
                    scale: 0.43, // a3
                    width: contentWidth,
                    height: contentHeight,
                    removeContainer: false // for analyzing iframe
                },
                // element1,
                x: x,
                margin: [15, 0, 30, 0], // Adds the margin to the PDF view 
                autoPaging: 'text',
                pagesplit: true,
                callback: function(pdf) {
    
                    var timestamp = Date.now();
                    var pdfName = fileName;
                    //pdf.output(pdfName);
                    pdf.output('dataurlnewwindow');
    
                    $("#test").html("<br><br>");
                }
            });
    
        }
    */
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