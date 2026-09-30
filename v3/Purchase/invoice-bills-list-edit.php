<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterYear = "T";
$filterCenter = "T";
$filterPaymentMode = "T";
$filterType = "T";
$buttonBottom = "T";
?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . 'includes/head.php' ?>
        <style>
            #btnSearch{
                max-width: 150px;
                margin: 0px auto;
            }
        </style>

    </head>

    <?php include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . 'includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include_once ROOT . 'includes/page-header.php' ?>   
                        <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>   

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">

                                        <div id="divDataList">
                                            <!-- -->
                                        </div>

                                    </div>
                                </div>


                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include_once ROOT . 'includes/footer.php' ?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . 'includes/common-js.php' ?>

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . 'includes/app-js.php' ?>

        <script>
           

            var tblPaymentMode;
            function fnShowDataTable() {                
                var table = $('#tblDataTable').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true,
                    // columnDefs: [{
                    //     searchable: false,
                    //     orderable: false,
                    //     targets: 0
                    // }],
                    bSort : true,
                        "bStateSave": true,
                        "fnStateSave": function (oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function (oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    }
                });            
            }
            
            function ShowList() {
                $.ajax({
                    url:'Ajax/AjaxInvoiceBillsListEdit.php',
                    type:'POST',
                    data:{
                        // PsId: $("#drpPurchaseList").val(),
                        // ArrivalDate: $("#txtArrivalDate").val()
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
