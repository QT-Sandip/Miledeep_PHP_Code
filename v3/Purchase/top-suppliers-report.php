<?php 

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';

$filterCenter = "T";
$filterStartDate = "T";
$filterEndDate = "T";
$buttonBottom = "T";
?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . 'includes/head.php' ?>

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
            function fnShowDataTable() {                
                var table = $('#tblDataTable').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true,
                    dom: 'Blfrtip',
                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btnExcelDataTable',
                        title: 'Payment Summary',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }],
                });            
            }

            function ShowList() {
                $.ajax({
                    url:'Ajax/ajax-top-suppliers-report.php',
                    type:'POST',
                    data:{

                        center: $("#drpCenter").val(),
                        startDate: $("#txtStartDate").val(),
                        endDate: $("#txtEndDate").val()
                    },
                    success:function(data){
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }

                });
            }

            $("#btnSearch").on("click", function() {
                var drpCenter = $("#drpCenter").val();
                var txtStartDate = $("#txtStartDate").val();
                var txtEndDate = $("#txtEndDate").val();

                if(drpCenter == "") {
                    $("#drpCenter").next().find('.select2-selection').addClass('error');
                } else {
                    $("#drpCenter").next().find('.select2-selection').removeClass('error');
                    ShowList();
                } 
            });
        </script>

    </body>
</html>
