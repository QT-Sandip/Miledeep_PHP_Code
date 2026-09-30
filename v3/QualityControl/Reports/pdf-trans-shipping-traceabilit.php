<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

//  $ArrivalBillID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

//  if (isset($_GET['ID']) && !empty($_GET['ID'])) {

//    $ArrivalBillID = trim($_GET['ID']);

//      $api = API_RM_BILLS;

//    $bodyContent = array("ArrivalID" => $ArrivalBillID);
//     $resPostPurchaseBillPrint = CallAPI($api, "GET", "", $bodyContent);

//    //   var_dump($resPostPurchaseBillPrint);

//      $result = $resPostPurchaseBillPrint->Data; 
//      $resPurchaseData = $resPostPurchaseBillPrint->Data->ArrivalData;
//      $resPurchaseBreakUpData = $resPostPurchaseBillPrint->Data->BreakUpData;
//      $resGrandTotal = $resPostPurchaseBillPrint->Data->GrandTotal;

// 	// var_dump($resPurchaseData);

//  } else {

//  }


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
                                        <button class="generate" id="generate" onclick="generatePDF('3-page.pdf')">Download PDF</button>
                                        <!-- <button class="generate" id="generate" onclick="downloadpdf('filename')">Download PDF</button> -->
                                    </div>

                                    <div id="printTable">

                                        <!----------------------------------------- Page 1 ---------------------->

                                        <div class="page f-12" id="page1-content">
                                            <table class="tblMain w100 bt bl bb br">
                                                <tr>
                                                    <td class="text-center f-14">Model Transhipping Certificate for Tracebility</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">{ } Wild Harvest or { # } Farm Raised</td>
                                                </tr>
                                                <tr>
                                                    <td class="f-12 bold">Referenced Unique Cash Document Identifier('')</td>
                                                </tr>
                                                <tr>
                                                    <td>1. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>2. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>3. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>4. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>5. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>6. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>7. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>8. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td class="f-12 bold">Port or Storage Location of Loading / Unloading</td>
                                                </tr>
                                                <tr>
                                                    <td>1. COSTAL AQUA PRIVATE LIMITED</td>
                                                </tr>
                                                <tr>
                                                    <td>2. COSTAL AQUA PRIVATE LIMITED</td>
                                                </tr>
                                                <tr>
                                                    <td>3. COSTAL AQUA PRIVATE LIMITED</td>
                                                </tr>
                                                <tr>
                                                    <td>4. COSTAL AQUA PRIVATE LIMITED</td>
                                                </tr>
                                                <tr>
                                                    <td>5. COSTAL AQUA PRIVATE LIMITED</td>
                                                </tr>
                                                <tr>
                                                    <td>6. COSTAL AQUA PRIVATE LIMITED</td>
                                                </tr>
                                                <tr>
                                                    <td>7. COSTAL AQUA PRIVATE LIMITED</td>
                                                </tr>
                                                <tr>
                                                    <td>8. COSTAL AQUA PRIVATE LIMITED</td>
                                                </tr>
                                            </table>
                                            <table class="tblMain w100 bt0 bl bb br">
                                                <tr class="f-12 bold">
                                                    <td style="width: 24%;">Name of Vessel/Cold Storage Unloaded from</td>
                                                    <td style="width: 24%;">Unique Unloaded Vessel Identifier (registation, documentation, or license number)</td>
                                                    <td style="width: 52%;">Date(s) of Unloading/Loading - MM/DD/YYY</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        Costal Aqua Private Limited, <br>
                                                        164 Patharlagada, <br>
                                                        Kakinada - 533468 <br>
                                                        Andhra Pradesh, India
                                                    </td>
                                                    <td>11022784438</td>
                                                    <td>10/31/2022</td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td>Name of Vessel/Cold Storage Loaded to</td>
                                                    <td colspan="2">Unique Unloaded Vessel Identifier (registation, documentation, or license number)</td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td>KMTC MUMBAI V.OFDA0E1MA</td>
                                                    <td colspan="2">IGCO146076</td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td>Commodity Scientific Name</td>
                                                    <td>Commodity Market Name</td>
                                                    <td>Product ASFIS#</td>
                                                </tr>
                                                <tr>
                                                    <td>1. Penaeus vannamei </td>
                                                    <td>1. White Leg Shrimp</td>
                                                    <td>1. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>2. Penaeus vannamei </td>
                                                    <td>2. White Leg Shrimp</td>
                                                    <td>2. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>3. Penaeus vannamei </td>
                                                    <td>3. White Leg Shrimp</td>
                                                    <td>3. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>4. Penaeus vannamei </td>
                                                    <td>4. White Leg Shrimp</td>
                                                    <td>4. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>5. Penaeus vannamei </td>
                                                    <td>5. White Leg Shrimp</td>
                                                    <td>5. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>6. Penaeus vannamei </td>
                                                    <td>6. White Leg Shrimp</td>
                                                    <td>6. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>7. Penaeus vannamei </td>
                                                    <td>7. White Leg Shrimp</td>
                                                    <td>7. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>8. Penaeus vannamei </td>
                                                    <td>8. White Leg Shrimp</td>
                                                    <td>8. PNV</td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td>Transhipped Weight [{ }lbs or { # }Kg]</td>
                                                    <td>Transshipped Lot Identifier(s)</td>
                                                    <td>Transshipped Product Form</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 1997.600 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td>1. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>2. 1997.600 </td>
                                                    <td>2. AP048773/001</td>
                                                    <td>2. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>3. 1997.600 </td>
                                                    <td>3. AP048773/001</td>
                                                    <td>3. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>4. 1997.600 </td>
                                                    <td>4. AP048773/001</td>
                                                    <td>4. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>5. 1997.600 </td>
                                                    <td>5. AP048773/001</td>
                                                    <td>5. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>6. 1997.600 </td>
                                                    <td>6. AP048773/001</td>
                                                    <td>6. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>7. 1997.600 </td>
                                                    <td>7. AP048773/001</td>
                                                    <td>7. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>8. 1997.600 </td>
                                                    <td>8. AP048773/001</td>
                                                    <td>8. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td>Production Date(s) - MM/DD/YYYY</td>
                                                    <td># of packages</td>
                                                    <td>Type of Packaging</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 09/09/2023</td>
                                                    <td>1. 220</td>
                                                    <td>1. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>2. 09/09/2023</td>
                                                    <td>2. 220</td>
                                                    <td>2. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>3. 09/09/2023</td>
                                                    <td>3. 220</td>
                                                    <td>3. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>4. 09/09/2023</td>
                                                    <td>4. 220</td>
                                                    <td>4. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>5. 09/09/2023</td>
                                                    <td>5. 220</td>
                                                    <td>5. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>6. 09/09/2023</td>
                                                    <td>6. 220</td>
                                                    <td>6. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>7. 09/09/2023</td>
                                                    <td>7. 220</td>
                                                    <td>7. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>8. 09/09/2023</td>
                                                    <td>8. 220</td>
                                                    <td>8. 4X5 Lbs</td>
                                                </tr>
                                            </table>
                                            <table>
                                                <tr>
                                                    <td class="b-all0">*Note: A Unique Document Identifier is provided by the harvester or landing recipient and should reflect the identifier of a catch or landing certificate</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0">*Note: A Lot Identifier is generated by the shipper based on its cargo tracking and record-keeping protocols</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!------------------------------------- END of Page 1 ---------------------->

                                        <br><br>

                                        <!-------------------------------------  Page 2 ---------------------->
                                        <div class="page f-12" id="page2-content">
                                            <table class="tblMain w100 bt bl bb br">
                                                <tr>
                                                    <td class="text-center f-14">Model Reprocessing Certificate for Traceability - Primary or Secondary Processing</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">() Wild Harvest or () Farm Raised</td>
                                                </tr>
                                            </table>
                                            <table>
                                                <tr>
                                                    <td style="width: 230px;">Referenced Unique Catch Document </td>
                                                    <td colspan="3">Company Product Received From: </td>
                                                </tr>
                                                <tr>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="3">1. AP048773/001, MERLA1. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EAST GODAVARI, ANDHARA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td>2. AP048773/001</td>
                                                    <td colspan="3">2. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EAST GODAVARI, ANDHARA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td>3. AP048773/001</td>
                                                    <td colspan="3">3. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EAST GODAVARI, ANDHARA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td>4. AP048773/001</td>
                                                    <td colspan="3">4. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EAST GODAVARI, ANDHARA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td>5. AP048773/001</td>
                                                    <td colspan="3">5. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EAST GODAVARI, ANDHARA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td>6. AP048773/001</td>
                                                    <td colspan="3">6. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EAST GODAVARI, ANDHARA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td>7. AP048773/001</td>
                                                    <td colspan="3">7. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EAST GODAVARI, ANDHARA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td>8. AP048773/001</td>
                                                    <td colspan="3">8. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EAST GODAVARI, ANDHARA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td>Processor Name </td>
                                                    <td>Processor Address</td>
                                                    <td>Processor Approval or Regisration Number</td>
                                                    <td>Phone</td>
                                                </tr>
                                                <tr>
                                                    <td>COASTAL AQUA PRIVATE LIMITED </td>
                                                    <td>
                                                        Coastal Aqua Private Limited, <br>
                                                        1-164 Patharlagadda <br>
                                                        kakinada - 533468 <br>
                                                        Andhra Pradesh, India
                                                    </td>
                                                    <td>11022784438</td>
                                                    <td>Phone: 0884-2382525 <br>
                                                        Email: Info@coastalaqua.in</td>
                                                </tr>

                                                <tr>
                                                    <td>Commodity Scientific Name</td>
                                                    <td colspan="2">Commodity Market Name</td>
                                                    <td>Product ASFIS#</td>
                                                </tr>
                                                <tr>
                                                    <td>1. Penaeus vannamei </td>
                                                    <td colspan="2">1. White Leg Shrimp</td>
                                                    <td>1. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>2. Penaeus vannamei </td>
                                                    <td colspan="2">2. White Leg Shrimp</td>
                                                    <td>2. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>3. Penaeus vannamei </td>
                                                    <td colspan="2">3. White Leg Shrimp</td>
                                                    <td>3. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>4. Penaeus vannamei </td>
                                                    <td colspan="2">4. White Leg Shrimp</td>
                                                    <td>4. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>5. Penaeus vannamei </td>
                                                    <td colspan="2">5. White Leg Shrimp</td>
                                                    <td>5. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>6. Penaeus vannamei </td>
                                                    <td colspan="2">6. White Leg Shrimp</td>
                                                    <td>6. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>7. Penaeus vannamei </td>
                                                    <td colspan="2">7. White Leg Shrimp</td>
                                                    <td>7. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>8. Penaeus vannamei </td>
                                                    <td colspan="2">8. White Leg Shrimp</td>
                                                    <td>8. PNV</td>
                                                </tr>
                                                <tr>
                                                    <td>Recevied Weght [{ }lbs or { }Kg]</td>
                                                    <td>Commodity Market Name</td>
                                                    <td colspan="2">Product ASFIS#</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 2968.202 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 2968.202 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 2968.202 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 2968.202 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 2968.202 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 2968.202 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 2968.202 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 2968.202 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. NOT</td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td>Transhipped Weight [{ }lbs or { # }Kg]</td>
                                                    <td>Transshipped Lot Identifier(s)</td>
                                                    <td colspan="2">Transshipped Product Form</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 1997.600 </td>
                                                    <td>1. AP048773/001</td>
                                                    <td colspan="2">1. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>2. 1997.600 </td>
                                                    <td>2. AP048773/001</td>
                                                    <td colspan="2">2. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>3. 1997.600 </td>
                                                    <td>3. AP048773/001</td>
                                                    <td colspan="2">3. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>4. 1997.600 </td>
                                                    <td>4. AP048773/001</td>
                                                    <td colspan="2">4. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>5. 1997.600 </td>
                                                    <td>5. AP048773/001</td>
                                                    <td colspan="2">5. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>6. 1997.600 </td>
                                                    <td>6. AP048773/001</td>
                                                    <td colspan="2">6. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>7. 1997.600 </td>
                                                    <td>7. AP048773/001</td>
                                                    <td colspan="2">7. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr>
                                                    <td>8. 1997.600 </td>
                                                    <td>8. AP048773/001</td>
                                                    <td colspan="2">8. FORZON RAW PEELED AND DEVEINED TAIL ON VANNAMEI SHRIMPS - IQF</td>
                                                </tr>
                                                <tr class="f-12 bold">
                                                    <td>Transhipped Weight [{ }lbs or { # }Kg]</td>
                                                    <td>Transshipped Lot Identifier(s)</td>
                                                    <td colspan="2">Transshipped Product Form</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">Finished Product Weight [{ }lbs or { }Kg]</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">1. 1997.600</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">2. 1997.600</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">3. 1997.600</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">4. 1997.600</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">5. 1997.600</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">6. 1997.600</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">7. 1997.600</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">8. 1997.600</td>
                                                </tr>
                                                <tr>
                                                    <td>Production Date(s) - MM/DD/YYYY</td>
                                                    <td># of Package</td>
                                                    <td colspan="2">Type of Packaging</td>
                                                </tr>
                                                <tr>
                                                    <td>1. 09/09/2023</td>
                                                    <td>1. 220</td>
                                                    <td colspan="2">1. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>2. 09/09/2023</td>
                                                    <td>2. 220</td>
                                                    <td colspan="2">2. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>3. 09/09/2023</td>
                                                    <td>3. 220</td>
                                                    <td colspan="2">3. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>4. 09/09/2023</td>
                                                    <td>4. 220</td>
                                                    <td colspan="2">4. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>5. 09/09/2023</td>
                                                    <td>5. 220</td>
                                                    <td colspan="2">5. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>6. 09/09/2023</td>
                                                    <td>6. 220</td>
                                                    <td colspan="2">6. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>7. 09/09/2023</td>
                                                    <td>7. 220</td>
                                                    <td colspan="2">7. 4X5 Lbs</td>
                                                </tr>
                                                <tr>
                                                    <td>8. 09/09/2023</td>
                                                    <td>8. 220</td>
                                                    <td colspan="2">8. 4X5 Lbs</td>
                                                </tr>
                                            </table>
                                            <table>
                                                <tr>
                                                    <td class="b-all0">*Note: A Unique Document Identifier is provided by the harvester or landing recipient and should reflect the identifier of a catch or landing certificate</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0">*Note: A Lot Identifier is generated by the shipper based on its cargo tracking and record-keeping protocols</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!-------------------------------------  END Page 2 ---------------------->

                                        <br><br>

                                        <!-------------------------------------  Page 3 ---------------------->
                                        <div class="page f-12" id="page3-content">
                                            <table class="tblMain w100 bt bl bb br">
                                                <tr>
                                                    <td class="text-center f-14" colspan="2">Model Catch Certificate for Traceabilty - Harvest and Landing/Receipt</td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0 p-0 ">
                                                        <table class="subtable w100">
                                                            <tr>
                                                                <td>(1) Unique Catch or Production Document Identifier*</td>
                                                            </tr>
                                                            <tr>
                                                                <td>1. AP048773/001</td>
                                                            </tr>
                                                            <tr>
                                                                <td>2. AP048773/001</td>
                                                            </tr>
                                                            <tr>
                                                                <td>3. AP048773/001</td>
                                                            </tr>
                                                            <tr>
                                                                <td>4. AP048773/001</td>
                                                            </tr>
                                                            <tr>
                                                                <td>5. AP048773/001</td>
                                                            </tr>
                                                            <tr>
                                                                <td>6. AP048773/001</td>
                                                            </tr>
                                                            <tr>
                                                                <td>7. AP048773/001</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bb0">8. AP048773/001</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                    <td>
                                                        ( ) Wild Capture Fishery (Complete Section 2) <br />
                                                        ( ) Farm Raised (Complete Section 3)
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="tblMain w100 ">
                                                <tr>
                                                    <td colspan="2">(2) Complete this section for wild capture fish productsp</td>
                                                </tr>
                                                <tr>
                                                    <td>Flag State of vessel : Not Applicable</td>
                                                    <td>Name of Harvesting Vessel** : Not Applicable</td>
                                                </tr>
                                                <tr>
                                                    <td>Vessel Registration or Documentation Number** : Not Applicable</td>
                                                    <td>Fishing Permit Number** : Not Applicable</td>
                                                </tr>
                                                <tr>
                                                    <td>Fishing/Catch Area:Not Applicable</td>
                                                    <td>Fishing Gear:Not Applicable</td>
                                                </tr>
                                            </table>

                                            <table class="tblMain w100 ">
                                                <tr>
                                                    <td colspan="2">(3) Complete this section for farm raised fish products </td>
                                                </tr>
                                                <tr>
                                                    <td>State of jurisdiction :</td>
                                                    <td>Facility Licence or Authorization**</td>
                                                </tr>
                                                <tr>
                                                    <td>1. IN (INDIA)</td>
                                                    <td>1. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>2. IN (INDIA)</td>
                                                    <td>2. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>3. IN (INDIA)</td>
                                                    <td>3. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>4. IN (INDIA)</td>
                                                    <td>4. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>5. IN (INDIA)</td>
                                                    <td>5. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>6. IN (INDIA)</td>
                                                    <td>6. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>7. IN (INDIA)</td>
                                                    <td>7. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td>8. IN (INDIA)</td>
                                                    <td>8. AP048773/001</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">Name and Location of Aquaculture Facility**</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">1. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EASH GODAVARI, ANDHRA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">2. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EASH GODAVARI, ANDHRA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">3. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EASH GODAVARI, ANDHRA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">4. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EASH GODAVARI, ANDHRA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">5. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EASH GODAVARI, ANDHRA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">6. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EASH GODAVARI, ANDHRA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">7. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EASH GODAVARI, ANDHRA PRADESH</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">8. AP048773/001, MERLA MADHURA MEENAKSHI, NARASAPURAPU PETA, RAMACHANDRAPURAM, EASH GODAVARI, ANDHRA PRADESH</td>
                                                </tr>



                                            </table>
                                            <table>
                                                <tr>
                                                    <td colspan="3">(4) Complete this section of fish products</td>
                                                </tr>
                                                <tr>
                                                    <td>Name of Recipient, Processor or Buyer</td>
                                                    <td>Telephone: 0884-2382525</td>
                                                    <td>Business Address</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        Costal Aqua Private Limited,
                                                        164 Patharlagada,
                                                        Kakinada - 533468
                                                        Andhra Pradesh, India
                                                    </td>
                                                    <td>
                                                        M R Murthy
                                                        Email: info@coastalaqua.in
                                                    </td>
                                                    <td>
                                                        Costal Aqua Private Limited,
                                                        164 Patharlagada,
                                                        Kakinada - 533468
                                                        Andhra Pradesh, India
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Receiving Facility or Vessel</td>
                                                    <td>Date of Landing/Transshipment (MM/DD/YYYY)</td>
                                                    <td>Landing Port or Delivery Location </td>
                                                </tr>
                                                <tr>
                                                    <td rowspan="9">
                                                        Costal Aqua Private Limited,
                                                        164 Patharlagada,
                                                        Kakinada - 533468
                                                        Andhra Pradesh, India
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>1. 09/09/2023</td>
                                                    <td>1. Coastal Aqua Private Limited </td>
                                                </tr>
                                                <tr>
                                                    <td>2. 09/09/2023</td>
                                                    <td>2. Coastal Aqua Private Limited </td>
                                                </tr>
                                                <tr>
                                                    <td>3. 09/09/2023</td>
                                                    <td>3. Coastal Aqua Private Limited </td>
                                                </tr>
                                                <tr>
                                                    <td>4. 09/09/2023</td>
                                                    <td>4. Coastal Aqua Private Limited </td>
                                                </tr>
                                                <tr>
                                                    <td>5. 09/09/2023</td>
                                                    <td>5. Coastal Aqua Private Limited </td>
                                                </tr>
                                                <tr>
                                                    <td>6. 09/09/2023</td>
                                                    <td>6. Coastal Aqua Private Limited </td>
                                                </tr>
                                                <tr>
                                                    <td>7. 09/09/2023</td>
                                                    <td>7. Coastal Aqua Private Limited </td>
                                                </tr>
                                                <tr>
                                                    <td>8. 09/09/2023</td>
                                                    <td>8. Coastal Aqua Private Limited </td>
                                                </tr>
                                                <tr>
                                                    <td>Species of Fish (ASFIS alpha code)</td>
                                                    <td>Landed of Delivered Weight</td>
                                                    <td>Product Form</td>
                                                </tr>
                                                <tr>
                                                    <td>1. PNV</td>
                                                    <td>1. 2968.202 { }lb or { } kg</td>
                                                    <td>1. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>2. PNV</td>
                                                    <td>2. 2968.202 { }lb or { } kg</td>
                                                    <td>2. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>3. PNV</td>
                                                    <td>3. 2968.202 { }lb or { } kg</td>
                                                    <td>3. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>4. PNV</td>
                                                    <td>4. 2968.202 { }lb or { } kg</td>
                                                    <td>4. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>5. PNV</td>
                                                    <td>5. 2968.202 { }lb or { } kg</td>
                                                    <td>5. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>6. PNV</td>
                                                    <td>6. 2968.202 { }lb or { } kg</td>
                                                    <td>6. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>7. PNV</td>
                                                    <td>7. 2968.202 { }lb or { } kg</td>
                                                    <td>7. NOT</td>
                                                </tr>
                                                <tr>
                                                    <td>8. PNV</td>
                                                    <td>8. 2968.202 { }lb or { } kg</td>
                                                    <td>8. NOT</td>
                                                </tr>
                                            </table>
                                            <table>
                                                <tr>
                                                    <td class="b-all0">*Note: Unique Document Identifier is provided by yhe harvester or landing recipient or competent authority </td>
                                                </tr>
                                                <tr>
                                                    <td class="b-all0">**Not required if completing an aggregated catch certificate for small vessels or aquaculture facilities </td>
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
    function generatePDF(filename) {
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
                    scale: 0.77, // Adjust the scale as needed
                    // scale: 0.7, // Adjust the scale as needed
                    removeContainer: false
                }).then(function (canvas) {
                    var imgData = canvas.toDataURL('image/png');

                    // Add the image to the PDF
                    pdf.addImage(imgData, 'PNG', 10, 10);

                    if (index < 2) {
                        pdf.addPage();
                        addElementToPDF(index + 1);
                    } else {
                        pdf.save(filename);
                    }
                });

                // pdf.html(element, {
                //     html2canvas: {
                //         // scale: 0.61, // a3
                //         scale: 0.43, // a3
                //         // width: contentWidth,
                //         // height: contentHeight,
                //         removeContainer: false // for analyzing iframe
                //     },
                //     x: 0,
                //     margin: [15, 0, 30, 0], // Adds the margin to the PDF view 
                //     autoPaging: 'text',
                //     callback: function () {

                //         if (index < 2) {
                //             pdf.addPage();
                //             addElementToPDF(index + 1);
                //         } else {
                //             pdf.save('multi_page_with_styles.pdf');
                //         }
                       
                //         // pdf.save(fileName);
                //     }
                // });               
            }

            addElementToPDF(0);
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