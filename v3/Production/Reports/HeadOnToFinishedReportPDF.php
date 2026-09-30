<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ProformaID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

if (isset($_GET['ID']) && !empty($_GET['ID'])) {

    $ProformaID = trim($_GET['ID']);
    $DocType = 'PI';

    $api = API_PDF_PROFORMA_PRINT;

    $bodyContent = array("ProformaID" => $ProformaID, "DocType" => $DocType);
    $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

    // echo json_encode($resPostProfromaPrint, JSON_PRETTY_PRINT);

    $result = $resPostProfromaPrint->Data;
    $resLabels = $resPostProfromaPrint->Data->Labels;
    $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
    $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
    $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
    $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;
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
                                            onclick="downloadpdf('<?php echo str_replace('/', '_', $result->InvoiceNumber) ?>.pdf')">Download
                                            PDF</button>
                                    </div>

                                    <div id="printTable">
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
                                                                        <td class="br" style="min-width:108px"></td>
                                                                        <td class="br" style="min-width:108px"></td>
                                                                        <td class="br" style="min-width:108px"></td>
                                                                        <td class="br" style="min-width:108px"></td>
                                                                        <td colspan="2" class="br">COSTAL AQUA PVT LTD.</td>                                                                        
                                                                        <td class="br" style="min-width:108px"></td>
                                                                        <td class="br" style="min-width:108px"></td>
                                                                        <td style="min-width:108px"></td>
                                                                    </tr>
                                                                    <tr class="bb">
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td colspan="3" class="br">HO TO FINISHED PRODUCT YIELD SUMMARY</td>                                                                                                                                                
                                                                        <td class="br"></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr class="bb">
                                                                        <td class="br">DATE</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr class="bb">
                                                                        <td class="br">BATCH NO</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr class="bb">
                                                                        <td class="br">PLACE</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td colspan="2">HO TO HL TIELD %</td>
                                                                    </tr>
                                                                    <tr class="bb">
                                                                        <td class="br" colspan="2">PURCHASE COUNT</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td colspan="2">HO TO FINISHED YIELD %</td>
                                                                    </tr>
                                                                    <tr class="bb">
                                                                        <td class="br" colspan="2">PLANT COUNT</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td colspan="2">HL TO FINISHED YIELD %</td>
                                                                    </tr>
                                                                    <tr class="bb">
                                                                        <td class="br" colspan="3">PURCHASE HO QUANTITY</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr class="bb text-center">                                                                        
                                                                        <td class="br">GRADES</td>
                                                                        <td class="br">HL COUNT</td>
                                                                        <td class="br">VARIETY</td>
                                                                        <td class="br">HL QUNTITY</td>
                                                                        <td class="br">VALUE ADDED QUANTITY</td>                                                                        
                                                                        <td class="br">HL TO VALUE ADDED YIELD %</td>
                                                                        <td class="br">VARIETY</td>
                                                                        <td class="br">PACKING</td>
                                                                        <td>FINISHED QUANTITY</td>
                                                                    </tr>
                                                                    <tr class="bb ">                                                                        
                                                                        <td class="br">.8/12</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr class="bb ">                                                                        
                                                                        <td class="br">.11/12</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr class="">                                                                        
                                                                        <td class="br">&nbsp;</td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>                                                                        
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td class="br"></td>
                                                                        <td></td>
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
    var invNo = "<?php echo $result->InvoiceNumber ?>";
</script>
<?php include_once ROOT . '/includes/footer-print-pdf.php' ?>

</body>

</html>