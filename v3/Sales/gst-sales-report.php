<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterfromDate = "T";
$filtertoDate = "T";
$filterExporter = "T";

$filterSideButton = "T";

?>
<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>

    </head>

    <?php  include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . '/includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include_once ROOT . '/includes/page-header.php' ?>   

                        <?php // include_once ROOT . 'Sales/includes/page-filter.php' ?>
                        <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>                           

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <!-- <div class="card">
                                    <div class="card-body"> -->
                                        
                                        <div id="divDataList">
                                            <!-- -->
                                        </div>

                                    <!-- </div>
                                </div> -->


                            </div>
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
        
        <?php include_once ROOT . 'includes/app-js.php' ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

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
                    buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title:"GST Sales Report", footer: true,
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function (data, row, column, node) {

                                const dateColumns = [3,6];
                                var decodedData = $('<div>').html(data).text();
                                if (dateColumns.includes(column)) {
                                    if(decodedData == "--") {
                                        return "--";    
                                    }else {
                                        return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                    }
                                } else {
                                    return decodedData;
                                }

                            }
                        }
                        
                    }}],
                });
            }
 $("#btnSearch").on("click", function(){  
    var exporter = $("#drpExporterList").val();
    var txtStartDate = $("#fromDate").val();
    var txtEndDate = $("#toDate").val();

    var startDate = new Date(txtStartDate);
    var endDate = new Date(txtEndDate);
    
    var timeDiff = endDate.getTime() - startDate.getTime();
    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    var days = daysDiff + 1;

    console.log(days);

    if(days > 31) {
        var url = 'Ajax/AjaxGSTSalesReport.php?Exporter='+exporter+'&FromDate='+txtStartDate+'&ToDate='+txtEndDate+'&excelDownload=T';
        window.open(url, '_blank');
    } else {
        $.ajax({
                    url:'Ajax/AjaxGSTSalesReport.php',
                    type:'POST',
                    data:{
                        ExporterID: $("#drpExporterList").val(),
                        FromDate: $("#fromDate").val(),
                        ToDate: $("#toDate").val(),
                    },
                    success:function(data){
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }
                });
            }
            });
$("#fromDate").on("change", function () {
    $("#divDataList").html("");
});
        </script>

    </body>
</html>
                            
                
