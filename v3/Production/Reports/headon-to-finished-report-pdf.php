<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once ROOT . 'config/api-call.php';


$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$mergeHonLoose = $ArrayClientConfigData['mergeHonLoose'];
$EnabelRejQty = $ArrayClientConfigData['EnabelRejQty'];


// $ProformaID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

$sql_Client = "SELECT * FROM `AdminClientDetails` ORDER BY ClientID ASC LIMIT 1 ";

// $stmt_Client = $iconn->query($sql_Client);
// $client = $stmt_Client->fetch_array();

// $stmt_Client = $iconn->prepare($sql_Client);
// $stmt_Client->execute();
// $client = $stmt_Client->fetch(PDO::FETCH_ASSOC);

$stmt_Client = $iconn->query($sql_Client); // Execute the first query
$client = $stmt_Client->fetch_array();

$ClientName = $client['Name'];

if (isset($_GET['arrivaldate']) && !empty($_GET['arrivaldate'])) {

    $api = API_HOTOFINISHED_REPORT;

    $bodyContent = array(
        'ArrivalDate' => trim($_GET["arrivaldate"]),
        'DataType' => trim($_GET["datatype"]),
        'PlantID' => trim($_GET["plantid"]),
        'ArrivalID' => trim($_GET["arrivalid"]),
        'mergeHonLoose' => $mergeHonLoose,
    );
    // echo json_encode($bodyContent);
    $resDataPrint = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resDataPrint, JSON_PRETTY_PRINT); exit;

    $purchaseData = $resDataPrint->HoToFinishedReportData->PurchaseData[0];
    $yieldsData = $resDataPrint->HoToFinishedReportData->Yields[0];
    $hTFRptData = $resDataPrint->HoToFinishedReportData->HeadonToFinishedReportData;
    $abStatus = $resDataPrint->HoToFinishedReportData->AbStatus;
    //echo '<pre>';print_r($resDataPrint);die;
    $BatchNumber = $yieldsData->BatchNumber;
    $HeadOnWeight = $yieldsData->MixedHeadonWeight;
    $PurchaseCount = $yieldsData->PurchaseCount;
    $ReceivingCount = $yieldsData->ReceivingCount;

    $packingType = $resDataPrint->HoToFinishedReportData->packingType;


    // echo json_encode($purchaseData, JSON_PRETTY_PRINT); exit;

    if ($yieldsData->BatchNumber == '') {
        $BatchNumber = $purchaseData->BatchNumber;
    }

    if ($yieldsData->MixedHeadonWeight == '') {
        $HeadOnWeight = $purchaseData->PurchaseHoQty;

    }
    if ($yieldsData->PurchaseCount == '') {
        $PurchaseCount = $purchaseData->PurchaseCount;

    }
    if ($yieldsData->ReceivingCount == '') {
        $ReceivingCount = $purchaseData->PlantCount;

    }

} else {
}

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="<?php echo ROOT_URL ?>assets/css/InvoiceHtmlPdf.css" rel="stylesheet" type="text/css" />

    <style>
        td {
            padding: 1px 3px;
        }

        .f-11 {
            font-size: 11px;
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
                                        <button class="generate" id="generate"
                                            onclick="downloadpdf('Head-On-To-Finished-Report-<?php echo $purchaseData->BatchNumber ?>.pdf')">Download
                                            PDF</button>
                                    </div>

                                    <div id="printTable"
                                        style="display: flex;justify-content: center;align-items: center;">
                                        <?php
                                        if($packingType == 0){
                                            //HL Product
                                            ?>
                                            <table class="tblMain">
                                                <tr>
                                                    <td>
                                                        <h1 class="page-title">Head On To Finished Report</h1>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <table class="bt bl br bb w100 f-12">
                                                            <tr>
                                                                <td class="p-0 tblHeader">
                                                                    <table class="w100">
                                                                        <tr class="bb">
                                                                            <td class="br" style="min-width:98px"></td>
                                                                            <td class="br" style="min-width:98px"></td>
                                                                            <td class="br" style="min-width:98px"></td>
                                                                            <td class="br" style="min-width:90px"></td>
                                                                            <!-- <td colspan="3" class="br">COASTAL AQUA PVT LTD.</td> -->
                                                                            <td colspan="3" class="br">
                                                                                <?php echo $ClientName ?>
                                                                            </td>
                                                                            <td class="br" style="min-width:98px"></td>
                                                                            <td class="br" style="min-width:98px"></td>
                                                                            <td style="min-width:98px"></td>
                                                                        </tr>
                                                                        <tr class="bb">
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td colspan="4" class="br">HO TO FINISHED
                                                                                PRODUCT YIELD SUMMARY</td>
                                                                            <td class="br"></td>
                                                                            <td></td>
                                                                        </tr>
                                                                        <tr class="bb">
                                                                            <td class="br" colspan="3">DATE :
                                                                                <?php echo $purchaseData->Date ?>
                                                                            </td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td></td>
                                                                        </tr>
                                                                        <tr class="bb">
                                                                            <td class="br" colspan="3">BATCH NO :
                                                                                <?php echo $BatchNumber ?>
                                                                            </td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td colspan="3">BODY YIELD % :
                                                                                <?php echo $yieldsData->bodyYield ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="bb">
                                                                            <td class="br" colspan="3">PLACE :
                                                                                <?php echo $purchaseData->Place ?>
                                                                            </td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td colspan="3">HO TO HL YIELD % :
                                                                                <?php echo $yieldsData->HoToHlYield ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="bb">
                                                                            <td class="br" colspan="3">PURCHASE COUNT :
                                                                                <?php echo $PurchaseCount ?>
                                                                            </td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td colspan="3">HO TO FINISHED YIELD % :
                                                                                <?php echo $yieldsData->HoToFinishedYield ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="bb">
                                                                            <td class="br" colspan="3">PLANT COUNT :
                                                                                <?php echo $ReceivingCount ?>
                                                                            </td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td colspan="3">HL TO FINISHED YIELD % :
                                                                                <?php echo $yieldsData->HlToFinishedYield ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="bb">
                                                                            <td class="br" colspan="3">PURCHASE HO QUANTITY
                                                                                : <?php echo $HeadOnWeight ?></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td class="br"></td>
                                                                            <td colspan="3">AB Status : <span
                                                                                    style="background-color: <?php echo $abStatus->Colour; ?>"><?php echo $abStatus->Name; ?></span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="bb text-center">
                                                                            <td class="br">GRADES</td>
                                                                            <td class="br">HL COUNT</td>
                                                                            <td class="br">VARIETY</td>
                                                                            <td class="br" style="min-width: 120px">HL
                                                                                QUANTITY</td>
                                                                            <td class="br" style="min-width: 120px">VALUE
                                                                                ADDED QUANTITY</td>
                                                                            <td class="br" style="min-width: 120px">HL TO
                                                                                VALUE ADDED YIELD %</td>
                                                                            <td class="br" style="min-width: 100px">SOAKING
                                                                                QUANTITY</td>
                                                                            <td class="br">PICKUP %</td>
                                                                            <td class="br">VARIETY</td>
                                                                            <td class="br" style="min-width: 180px">PACKING
                                                                            </td>
                                                                            <td style="min-width: 120px">FINISHED QUANTITY
                                                                            </td>
                                                                        </tr>


                                                                        <?php
                                                                        $RemarksArray = array();
                                                                        foreach ($hTFRptData as $item) {
                                                                            $Grade = explode(',', $item->Grade);
                                                                            $HlCount = explode(',', $item->HlCount);
                                                                            $GradingVariety = explode(',', $item->GradingVariety);
                                                                            $HlQty = explode(',', $item->HlQuantity);
                                                                            $VaQty = explode(',', $item->VaQuantity);
                                                                            $HlToVaYield = explode(',', $item->HlToVaYieldPercentage);
                                                                            $SoakingQty = explode(',', $item->SoakingQuantity);
                                                                            $PickUp = explode(',', $item->PickupPercentage);
                                                                            $FreezingVarietyList = explode('&', $item->FreezingVariety);
                                                                            $PackingList = explode('&', $item->Packing);
                                                                            $FinishedQuantityList = explode('&', $item->FinishedQuantity);

                                                                            $ProductionQuantityList = explode('&', $item->ProductionQuantity);
                                                                            $RejectionQuantityList = explode('&', $item->RejectionQuantity);

                                                                            if($EnabelRejQty == 1){
                                                                                $PackingOutputExt = $varietyOutputExt = [];
                                                                                foreach ($PackingList as $key => $packing) {
                                                                                    $packingArr = explode(',',$packing);
                                                                                    $RejectionQuantityArr = explode(',',$RejectionQuantityList[$key]);
                                                                                    $FreezingVarietyArr = explode(',',$FreezingVarietyList[$key]);
                                                                                    $FreezingVariety =  $FreezingVarietyList[$key];
                                                                                    $PackingOutputInt = $varietyOutputInt = [];
                                                                                    foreach($packingArr as  $k => $packingRow){                                                                                    
                                                                                        if (
                                                                                            isset($RejectionQuantityArr[$k]) &&
                                                                                            (float)$RejectionQuantityArr[$k] != 0
                                                                                        ) {
                                                                                            $PackingOutputInt[] = $packingRow . '<br>';
                                                                                            $varietyOutputInt[] = $FreezingVarietyArr[$k] . '<br>';
                                                                                        } else {
                                                                                            $PackingOutputInt[] = $packingRow;
                                                                                            $varietyOutputInt[] = $FreezingVarietyArr[$k];
                                                                                        }
                                                                                    }
                                                                                    $PackingOutputExt[] = implode(',', $PackingOutputInt);
                                                                                    $varietyOutputExt[] = implode(',', $varietyOutputInt);
                                                                                }
                                                                            }else{
                                                                                $PackingOutputExt = $PackingList;
                                                                                $varietyOutputExt = $FreezingVarietyList;
                                                                            }

                                                                            if (!empty(trim($item->Remarks))) {
                                                                                // Split remarks by comma
                                                                                $splitRemarks = explode(',', $item->Remarks);

                                                                                // Trim each remark before pushing
                                                                                foreach ($splitRemarks as $rmk) {
                                                                                    $clean = trim($rmk);
                                                                                    if (!empty($clean)) {
                                                                                        $RemarksArray[] = $clean;
                                                                                    }
                                                                                }
                                                                            }

                                                                            $max = max(
                                                                                count($Grade),
                                                                                count($HlCount),
                                                                                count($GradingVariety),
                                                                                count($HlQty),
                                                                                count($VaQty),
                                                                                count($HlToVaYield),
                                                                                count($SoakingQty),
                                                                                count($PickUp),
                                                                                count($FreezingVariety),
                                                                                count($PackingList),
                                                                                count($FinishedQuantityList),
                                                                                count($ProductionQuantityList),
                                                                                count($RejectionQuantityList)
                                                                            );

                                                                            for ($i = 0; $i < $max; $i++) {
                                                                                echo '<tr class="bb">';
                                                                                echo '<td class="br">' . (isset($Grade[$i]) ? $Grade[$i] : '') . '</td>';
                                                                                echo '<td class="br">' . (isset($HlCount[$i]) ? $HlCount[$i] : '') . '</td>';
                                                                                echo '<td class="br">' . (isset($GradingVariety[$i]) ? $GradingVariety[$i] : '') . '</td>';
                                                                                echo '<td class="br">' . (isset($HlQty[$i]) ? $HlQty[$i] : '') . '</td>';
                                                                                echo '<td class="br">' . (isset($VaQty[$i]) ? $VaQty[$i] : '') . '</td>';
                                                                                echo '<td class="br">' . (isset($HlToVaYield[$i]) ? $HlToVaYield[$i] : '') . '</td>';
                                                                                echo '<td class="br">' . (isset($SoakingQty[$i]) ? $SoakingQty[$i] : '') . '</td>';
                                                                                echo '<td class="br">' . (isset($PickUp[$i]) ? $PickUp[$i] : '') . '</td>';

                                                                                // Now explode by comma from each &-split Packing and FinishedQuantity
                                                                                $packingItems = isset($PackingOutputExt[$i]) ? explode(',', $PackingOutputExt[$i]) : [''];
                                                                                $finishedItems = isset($FinishedQuantityList[$i]) ? explode(',', $FinishedQuantityList[$i]) : [''];

                                                                                $ProductionQuantityItems = isset($ProductionQuantityList[$i]) ? explode(',', $ProductionQuantityList[$i]) : [''];
                                                                                $RejectionQuantityItems = isset($RejectionQuantityList[$i]) ? explode(',', $RejectionQuantityList[$i]) : [''];

                                                                                $PrdQuantityItems = [];
                                                                                if($EnabelRejQty == 1){
                                                                                    foreach ($ProductionQuantityItems as $key => $value) {

                                                                                        $rej = isset($RejectionQuantityItems[$key]) 
                                                                                            ? number_format($RejectionQuantityItems[$key], 2)
                                                                                            : '';

                                                                                        $PrdQuantityItems[] = !empty($rej) && $rej != '0.00' && $rej != '0.000' ? number_format($value, 2) . " <br>(Rej-$rej)" : $value;
                                                                                    } 
                                                                                }else{
                                                                                    $PrdQuantityItems = array_merge($PrdQuantityItems, $finishedItems);;
                                                                                }

                                                                                $freezingVarieties = isset($varietyOutputExt[$i]) ? explode(',', $varietyOutputExt[$i]) : [''];

                                                                                echo '<td class="br">' . implode('<br>', $freezingVarieties) . '</td>';
                                                                                echo '<td class="br" style="min-width: 180px">' . implode('<br>', $packingItems) . '</td>';
                                                                                echo '<td class="br" style="min-width: 120px">' . implode('<br>', $PrdQuantityItems) . '</td>';
                                                                                echo '</tr>';
                                                                            }
                                                                        } ?>



                                                                        <tr>
                                                                            <?php
                                                                            if (!empty($RemarksArray) && count($RemarksArray) > 0) { ?>
                                                                                <td class="br" colspan="11">
                                                                                    <strong>Remarks:</strong><br>
                                                                                    <?php
                                                                                    $uniqueRemarks = array_unique($RemarksArray);
                                                                                    $filteredRemarks = array_filter($uniqueRemarks, function ($remark) {
                                                                                        return !empty(trim($remark));
                                                                                    });
                                                                                    //echo implode('; ', $filteredRemarks);
                                                                                    foreach ($filteredRemarks as $remark) {
                                                                                        echo htmlspecialchars($remark) . "<br>";
                                                                                    }
                                                                            }
                                                                            ?>
                                                                            </td>
                                                                            <!-- <td class="br" colspan="10">
                                                                                <span style="color: <?php //echo $yieldsData->Colour; ?>">
                                                                                    <?php //echo $yieldsData->DisplayText .$yieldsData->BatchNumber ?>
                                                                                </span><br>

                                                                                <span style="color: <? php// echo $yieldsData->Colour; ?>">
                                                                                    <? php// echo $yieldsData->DisplayText2 .$yieldsData->MixedHeadonWeight; ?>
                                                                                </span>


                                                                            </td> -->
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
                                        <?php } else {
                                            include("headon-to-finished-report-pdf-for-hon.php");

                                        } ?>

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
    // var invNo = "Head-On-To-Finished-Report-<?php echo $purchaseData->BatchNumber ?>";
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>

    window.jsPDF = window.jspdf.jsPDF;
    var pdf = new jsPDF({
        orientation: 'landscape', // 'portrait' for vertical, 'landscape' for horizontal
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
        var marginLeft = 100;
        var marginTop = 50;
        var marginRight = 50;
        var marginBottom = 50;

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
            margin: [13, 0, 30, 70], // Adds the margin to the PDF view 
            autoPaging: 'text',
            callback: function (pdf) {

                var footerHeight = 20; // Height of the footer
                var gap = 15; // Gap between content and footer

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
                    // pWidth / 2,
                    // pageNumberY,
                    // 'Page ' + i + ' of ' + pdf.internal.getNumberOfPages() + " - " + invNo,
                    // null,
                    // null,
                    // 'center'
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
    $(document).ready(function () {
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