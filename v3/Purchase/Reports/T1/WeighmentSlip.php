<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';
$ClientID = $_SESSION["ClientID"];
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);

$hasHarvestDate = $ArrayClientConfigData['hasHarvestDate'];

$BookingID = isset($_GET['BookingID']) && !empty($_GET['BookingID']) ? $_GET['BookingID'] : "";

$HarvestID = $_GET['HarvestingID'];

$apiHarvestData = API_HARVESTING_DETAILS;

$bodyContent = array(

    "HarvestingID" => $HarvestID,

    "BookingID" => $BookingID

);

$result = CallAPI($apiHarvestData, "GET", "", $bodyContent);
$HarvestData = $result->Data[0];
$OverallBreakUpData = $result->Data[0]->OverallBreakUpData;
$BillerDetails = $result->Data[0]->BillerDetails;
// var_dump($HarvestData);
// exit;
$WeightTotalHO = 0;
foreach ($OverallBreakUpData as $item) {
    if (strtolower($item->Variety) == "head on") {
        $WeightTotalHO += $item->TotalWeight;
    }
}

$HONweight = $WeightTotalHO + $HarvestData->CheckingQty - $HarvestData->MaterialTotalCrates;
$imgData = base64_encode(file_get_contents(ROOT . "assetsv2/assets/images/qr/coastal_qr.png"));
$src = 'data: ' . mime_content_type(ROOT . "assetsv2/assets/images/qr/coastal_qr.png") . ';base64,' . $imgData;
// echo "<img src=\"$src\" >"; 

$SpaceHeight = count($OverallBreakUpData) > 1 ? 200 - count($OverallBreakUpData) * 40 : 100;
function countTheTrays($BreakUpData)
{
    $totalTraysCount = 0;
    for ($rowIndex = 0; $rowIndex < count($BreakUpData); $rowIndex++) {
        $NoofCrates = intval($BreakUpData[$rowIndex]->NoofCrates);
        $totalTraysCount = $totalTraysCount + $NoofCrates;
    }

    return $totalTraysCount;
}
$CheckingCrate = 0;

if ($hasHarvestDate == 1 && $HarvestData->CheckingCrate > 0) {
    $CheckingCrate = intval($HarvestData->CheckingCrate);
}
$TotalTraysCount = countTheTrays($HarvestData->BreakUpData);
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>" rel="stylesheet"
        type="text/css">

    <style>
        h1 {
            font-size: 20px;
            color: #000000;
        }

        .w495 {
            width: 48.5%;
        }

        .w1 {
            width: 3%;
        }

        .f-800 {
            font-weight: 800;
        }

        .text-right {
            text-align: right;
        }

        td {
            padding: 0px 3px;
        }

        @media print {
            .page {
                page-break-before: always;
                 /* page-break-inside: avoid; */
            }
        }

        .page {
             page-break-before: always; 
             /* page-break-inside: avoid; */
            /* Force a page break before this content */
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
                                            onclick="downloadpdf('Weighment-Slip-<?php echo $HarvestID; ?>.pdf')">Download
                                            PDF</button>
                                    </div>

                                    <div id="printTable">

                                        <div class="page">
                                            <table class="tblMain w50 bt bl bb br">
                                                <!-- <tr>
                                                    <td class="text-center w495"
                                                        style="padding-right: 0px;padding-top: 3px;padding-left:5px">
                                                        <div class="bt bl bb br f-800 f-12">Original</div>
                                                    </td>
                                                   
                                                    <td class="w1">&nbsp;</td>
                                                    <td class="text-center w495"
                                                        style="padding-left: 0px;padding-right:5px;padding-top: 3px;">
                                                        <div class="bt bl bb br f-800 f-12">Duplicate</div>
                                                    </td>
                                                </tr> -->
                                                <tr>
                                                    <td colspan="3" class="bl bb br f-13"
                                                        style="padding: 2px 5px 5px 5px;">
                                                        <table class=" bl bb w100">
                                                            <tr>
                                                                <td class="w495 bt p-0">
                                                                    <table class="w100">
                                                                        <tr>
                                                                            <td class="p-0 br ">
                                                                                <table
                                                                                    class="tblHeader w100 f-13 f-800">
                                                                                    <tr class="trimg">
                                                                                        <td colspan="2"
                                                                                            class="text-left"
                                                                                            style="padding: 2px 5px;">
                                                                                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                                                                                <?PHP echo $ClientID == "CAPLV3" ? "<img src='$src' height='15%' width='15%' alt='qr'/>" : '<div style:"height=15% width=15%"/>' ?>
                                                                                                <div class="text-center">
                                                                                                    <h1 style="margin-bottom: 2px;" class="bold"> <?php echo $BillerDetails->CompanyName ?> </h1>
                                                                                                    <!-- <p class="f-14" style="margin-bottom: 2px;"><?php echo $BillerDetails->Address ?></p> -->
                                                                                                    <p class="f-14" style="margin-bottom: 2px;">WEIGHMENT SLIP</p>
                                                                                                </div>
                                                                                                <div style="width: 40px;" />
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>Bill No:
                                                                                            <?php echo $HarvestData->BookingNumber ?>
                                                                                        </td>
                                                                                        <td class="text-right"
                                                                                            style="width: 190px;">Bill
                                                                                            Date:
                                                                                            <!-- <?php echo $HarvestData->HarvestDate ?> -->
                                                                                            <?php echo date('d/m/Y', strtotime($HarvestData->HarvestDate)); ?>
                                                                                        </td>
                                                                                        
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td colspan="2">Supervisor:
                                                                                            <?php echo $HarvestData->Supplier ?>
                                                                                        </td>

                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td colspan="2">Farmer:
                                                                                            <?php echo $HarvestData->FarmerName ?>
                                                                                        </td>

                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>Address:
                                                                                            <?php echo $HarvestData->FarmAddress ?>
                                                                                        </td>
                                                                                        <td class="text-right">
                                                                                            <?php
                                                                                            if (isset($HarvestData->PhoneNumber) && !empty($HarvestData->PhoneNumber)) {
                                                                                                echo "Phone: " . $HarvestData->PhoneNumber;
                                                                                            }
                                                                                            ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                                <table class="bt  w100">
                                                                                    <tr class="f-12 f-800 bb">
                                                                                        <td class="br text-center"
                                                                                            style="width:34%">Variety
                                                                                        </td>
                                                                                        <td class="br text-center"
                                                                                            style="width:16%">Count</td>
                                                                                        <td class=" text-center"
                                                                                            style="width:26%">Quantity -
                                                                                            In kgs</td>
                                                                                    </tr>
                                                                                    <?php foreach ($OverallBreakUpData as $item) {
                                                                                        if (strtolower($item->Variety) == "head on") { ?>
                                                                                            <tr class="">
                                                                                                <td class="br text-center">
                                                                                                    <?php echo $item->Variety ?>
                                                                                                </td>
                                                                                                <td class="br text-center">
                                                                                                    <?php echo number_format($item->Count, 2) ?>
                                                                                                </td>
                                                                                                <td class=" text-right">
                                                                                                    <?php echo number_format($HONweight, 3) ?>
                                                                                                </td>
                                                                                            </tr>
                                                                                        <?php } else { ?>
                                                                                            <tr class="">
                                                                                                <td class="br text-center">
                                                                                                    <?php echo $item->Variety ?>
                                                                                                </td>
                                                                                                <td class="br text-center">
                                                                                                    <?php echo number_format($item->Count, 2) ?>
                                                                                                </td>
                                                                                                <td class=" text-right">
                                                                                                    <?php echo number_format($item->TotalWeight, 3) ?>
                                                                                                </td>
                                                                                            </tr>
                                                                                    <?php }
                                                                                    } ?>
                                                                                    <tr class="bb">
                                                                                        <td class="br text-center" style="height: <?php echo $SpaceHeight ?>px;">&nbsp;</td>
                                                                                        &nbsp;
                                                                            </td>
                                                                            <td class="br text-center">
                                                                                &nbsp;</td>
                                                                            <td class=" text-right">&nbsp;
                                                                            </td>
                                                                        </tr>

                                                                        <tr class=" bb">
                                                                            <td class="br text-center"
                                                                                colspan="1">Total</td>
                                                                            <td class="br text-right">&nbsp;
                                                                            </td>
                                                                            <td class=" text-right">
                                                                                <?php echo number_format($HarvestData->WithOutNetQty, 3) ?>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <table class="bb  w100">
                                                                        <tr>
                                                                            <td style="width: 150px;">Empty
                                                                                Crates: </td>
                                                                            <td class="br">
                                                                                <?php echo $HarvestData->MTCrates ?>
                                                                            </td>
                                                                            <td style="width: 120px;">Nets:
                                                                            </td>
                                                                            <td> <?php echo $HarvestData->Nets ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="width: 150px;">
                                                                                Savels: </td>
                                                                            <td class="br">
                                                                                <?php echo $HarvestData->Savels ?>
                                                                            </td>
                                                                            <td style="width: 120px;">Weight
                                                                                Stones: </td>
                                                                            <td> <?php echo $HarvestData->WeightStones ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="width: 150px;">
                                                                                WaterTin: </td>
                                                                            <td class="br">
                                                                                <?php echo $HarvestData->WaterTin ?>
                                                                            </td>
                                                                            <td style="width: 120px;">Boys
                                                                                Group: </td>
                                                                            <td> <?php echo $HarvestData->GroupName ?>
                                                                            </td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td style="width: 150px;">
                                                                                Working Boys: </td>
                                                                            <td class="br">
                                                                                <?php echo $HarvestData->WorkingBoys ?>
                                                                            </td>
                                                                            <td style="width: 120px;">
                                                                                Packing Trays : </td>
                                                                            <td> <?php echo  $TotalTraysCount + $CheckingCrate ?>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <table class="  w100">

                                                                        <tr>
                                                                            <td>Vehicle No:
                                                                                <?php echo $HarvestData->VehicalNumber ?>
                                                                            </td>
                                                                            <td class="text-right"
                                                                                style="width: 190px;">Seal
                                                                                No:
                                                                                <?php echo $HarvestData->SealNumber ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Driver Name:
                                                                                <?php echo $HarvestData->DriverName ?>
                                                                            </td>
                                                                            <td class="text-right">
                                                                                Supervisor:
                                                                                <?php echo $HarvestData->GraderName ?>
                                                                            </td>
                                                                        </tr>

                                                                        <tr class="bt">
                                                                            <td class="text-center p-0"
                                                                                colspan="6">
                                                                                <table class="w100">
                                                                                    <tr>
                                                                                        <td class="br bb"
                                                                                            style="width:24%">
                                                                                            &nbsp;<br><br>
                                                                                        </td>
                                                                                        <td class="br bb"
                                                                                            style="width:36%">
                                                                                            &nbsp;</td>
                                                                                        <td class="bb"
                                                                                            style="width:40%">
                                                                                            &nbsp;</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="br"
                                                                                            style="width:24%">
                                                                                            Supervisor
                                                                                            <br>Signature
                                                                                        </td>
                                                                                        <td class="br"
                                                                                            style="width:36%">
                                                                                            <br>Checked By
                                                                                        </td>
                                                                                        <td
                                                                                            style="width:40%">
                                                                                            <br>Authorized
                                                                                            Sign
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
                                                    <!-- <td class="w1">&nbsp;</td> -->
                                                    <!-- <td class="w495 bl bt p-0">
                                                        <table class="w100"> -->
                                                            <!-- <tr>
                                                                <td class="p-0 br ">
                                                                    <table
                                                                        class="tblHeader w100 f-13 f-800"> -->
                                                                        <!-- <tr class="trimg">
                                                                            <td colspan="2"
                                                                                class="text-left"
                                                                                style="padding: 2px 5px;">
                                                                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                                                                    <?PHP echo $ClientID == "CAPLV3" ? "<img src='$src' height='15%' width='15%' alt='qr'/>" : '<div style:"height=15% width=15%"/>' ?>
                                                                                    <div class="text-center">
                                                                                        <h1 style="margin-bottom: 2px;" class="bold"> <?php echo $BillerDetails->CompanyName ?> </h1>
                                                                                       <p class="f-14" style="margin-bottom: 2px;"><?php echo $BillerDetails->Address ?></p>
                                                                                        <p class="f-14" style="margin-bottom: 2px;">WEIGHMENT SLIP</p>
                                                                                    </div>
                                                                                    <div style="width: 40px;" />
                                                                                </div>
                                                                            </td>
                                                                        </tr> -->
                                                                        <!-- <tr>
                                                                            <td>Bill No:
                                                                                <?php echo $HarvestData->BookingNumber ?>
                                                                            </td>
                                                                            <td class="text-right"
                                                                                style="width: 190px;">Bill
                                                                                Date:
                                                                               <?php echo $HarvestData->HarvestDate ?> 
                                                                                 <?php echo date('d/m/Y', strtotime($HarvestData->HarvestDate)); ?>
                                                                            </td>
                                                                        </tr> -->
                                                                        <!-- <tr>
                                                                            <td colspan="2">Supervisor:
                                                                                <?php echo $HarvestData->Supplier ?>
                                                                            </td>

                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan="2">Farmer:
                                                                                <?php echo $HarvestData->FarmerName ?>
                                                                            </td>

                                                                        </tr>
                                                                        <tr>
                                                                            <td>Address:
                                                                                <?php echo $HarvestData->FarmAddress ?>
                                                                            </td>
                                                                            <td class="text-right">
                                                                                <?php
                                                                                if (isset($HarvestData->PhoneNumber) && !empty($HarvestData->PhoneNumber)) {
                                                                                    echo "Phone: " . $HarvestData->PhoneNumber;
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <table class="bt  w100">
                                                                        <tr class="f-12 f-800 bb">
                                                                            <td class="br text-center"
                                                                                style="width:34%">Variety
                                                                            </td>
                                                                            <td class="br text-center"
                                                                                style="width:16%">Count</td>
                                                                            <td class=" text-center"
                                                                                style="width:26%">Quantity -
                                                                                In kgs</td>
                                                                        </tr>
                                                                        <?php foreach ($OverallBreakUpData as $item) {
                                                                            if (strtolower($item->Variety) == "head on") { ?>
                                                                                <tr class="">
                                                                                    <td class="br text-center">
                                                                                        <?php echo $item->Variety ?>
                                                                                    </td>
                                                                                    <td class="br text-center">
                                                                                        <?php echo number_format($item->Count, 2) ?>
                                                                                    </td>
                                                                                    <td class=" text-right">
                                                                                        <?php echo number_format($HONweight, 3) ?>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php } else { ?>
                                                                                <tr class="">
                                                                                    <td class="br text-center">
                                                                                        <?php echo $item->Variety ?>
                                                                                    </td>
                                                                                    <td class="br text-center">
                                                                                        <?php echo number_format($item->Count, 2) ?>
                                                                                    </td>
                                                                                    <td class=" text-right">
                                                                                        <?php echo number_format($item->TotalWeight, 3) ?>
                                                                                    </td>
                                                                                </tr>
                                                                        <?php }
                                                                        } ?>
                                                                        <tr class="bb">
                                                                            <td class="br text-center" style="height: <?php echo $SpaceHeight ?>px;">&nbsp;</td>
                                                                            &nbsp;
                                                                </td>
                                                                <td class="br text-center">
                                                                    &nbsp;</td>
                                                                <td class=" text-right">&nbsp;
                                                                </td>
                                                            </tr> -->

                                                            <!-- <tr class=" bb">
                                                                <td class="br text-center"
                                                                    colspan="1">Total</td>
                                                                <td class="br text-right">&nbsp;
                                                                </td>
                                                                <td class=" text-right">
                                                                    <?php echo number_format($HarvestData->WithOutNetQty, 3) ?>
                                                                </td>
                                                            </tr> -->
                                                        <!-- </table>
                                                        <table class="bb  w100">
                                                            <tr>
                                                                <td style="width: 150px;">Empty
                                                                    Crates: </td>
                                                                <td class="br">
                                                                    <?php echo $HarvestData->MTCrates ?>
                                                                </td>
                                                                <td style="width: 120px;">Nets:
                                                                </td>
                                                                <td> <?php echo $HarvestData->Nets ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 150px;">
                                                                    Savels: </td>
                                                                <td class="br">
                                                                    <?php echo $HarvestData->Savels ?>
                                                                </td>
                                                                <td style="width: 120px;">Weight
                                                                    Stones: </td>
                                                                <td> <?php echo $HarvestData->WeightStones ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 150px;">
                                                                    WaterTin: </td>
                                                                <td class="br">
                                                                    <?php echo $HarvestData->WaterTin ?>
                                                                </td>
                                                                <td style="width: 120px;">Boys
                                                                    Group: </td>
                                                                <td> <?php echo $HarvestData->GroupName ?>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td style="width: 150px;">
                                                                    Working Boys: </td>
                                                                <td class="br">
                                                                    <?php echo $HarvestData->WorkingBoys ?>
                                                                </td>
                                                                <td style="width: 120px;">
                                                                    Packing Trays : </td>
                                                                <td> <?php echo $TotalTraysCount + $CheckingCrate ?>
                                                                </td>
                                                            </tr>
                                                        </table> -->
                                                        <!-- <table class="  w100">

                                                            <tr>
                                                                <td>Vehicle No:
                                                                    <?php echo $HarvestData->VehicalNumber ?>
                                                                </td>
                                                                <td class="text-right"
                                                                    style="width: 190px;">Seal
                                                                    No:
                                                                    <?php echo $HarvestData->SealNumber ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Driver Name:
                                                                    <?php echo $HarvestData->DriverName ?>
                                                                </td>
                                                                <td class="text-right"
                                                                    style="width: 190px;">
                                                                    Supervisor:
                                                                    <?php echo $HarvestData->GraderName ?>
                                                                </td>
                                                            </tr>

                                                            <tr class="bt">
                                                                <td class="text-center p-0"
                                                                    colspan="6">
                                                                    <table class="w100">
                                                                        <tr>
                                                                            <td class="br bb"
                                                                                style="width:24%">
                                                                                &nbsp;<br><br>
                                                                            </td>
                                                                            <td class="br bb"
                                                                                style="width:36%">
                                                                                &nbsp;</td>
                                                                            <td class="bb"
                                                                                style="width:40%">
                                                                                &nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="br"
                                                                                style="width:24%">
                                                                                Supervisor
                                                                                <br>Signature
                                                                            </td>
                                                                            <td class="br"
                                                                                style="width:36%">
                                                                                <br>Checked By
                                                                            </td>
                                                                            <td
                                                                                style="width:40%">
                                                                                <br>Authorized
                                                                                Sign
                                                                            </td>
                                                                        </tr>
                                                                    </table> -->
                                                                <!-- </td> -->
                                                            <!-- </tr> -->
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
        var element = document.getElementById("printTable");

        // Adjust the width of the HTML content to match the PDF page width
        // element.style.width = pWidth + "px"; // Set the width in points

        // Calculate the adjusted height to maintain the original aspect ratio
        var aspectRatio = element.offsetWidth / element.offsetHeight;
        var adjustedHeight = pWidth / aspectRatio;

        // Calculate the margins (adjust as needed)
        var marginLeft = 30;
        var marginTop = 30;
        var marginRight = 30;
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
            callback: function(pdf) {

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