<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
  


include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';

$ProformaID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

$filterExporter="T";
$filterConsignee="T";
$filterCountry="T";
$filterStatus="T";
?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>
       <link href="https://serp.silverlights.in/Clients/DFL/Sales/styles/InvoiceHtmlPdf.css" rel="stylesheet" />
    </head>

    <?php // include_once ROOT . '/includes/body-preloader.php' ?>  

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
                                                    <div style="text-align: center">
                                                        <a href="PrintProformaInvoice?ID=<?php echo $ProformaID ?>" target="_blank" class="generate" >Get PDF / PRINT </a><br><br>
                                                        <!-- <button class="generate" id="generate">Get PDF / PRINT </button> -->
                                                    </div>
                                                    <?php include_once 'ViewProformaInvoice.php'  ?>                                                   

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

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . '/includes/app-js.php' ?>

        <script> 
            function fnShowDataTable() {                
                var table = $('#tblDataTable').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true
                });            
            }
            function ShowList() {
                $.ajax({
                    url:'Ajax/AjaxProformaList.php',
                    type:'POST',
                    data:{
                        ExporterID: $("#drpExporterList").val(),
                        ConsigneeID: $("#drpConsigneeList").val(),
                        CountryID: $("#drpList").val(),
                        Status: $("#drpShimpmentStatuscodelist").val(),
                    },
                    success:function(data){
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }
                });
            }
            $(document).ready(function(){
                ShowList();
            });

            $("#btnSearch").on("click", function(){
                   
                ShowList();
            })
        </script>

    </body>
</html>