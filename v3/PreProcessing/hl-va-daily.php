<?php 

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';

$filterCenter = "T";
$filterSupplier = "T";
$filterArrivalPlant = "T";
$filterStartDate = "T";
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
                                        <div class="text-center">
                                            <a href="hl-va-bills" class="btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i>  Add Bill
                                            </a>        
                                        </div>

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
                        title: 'HL VA Daily',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }],
                });            
            }
            function ShowList() {
                $.ajax({
                    url: 'Ajax/ajax-hl-va-daily.php',
                    type: 'POST',
                    data: {
                        center: $("#drpCenter").val(),
                        supplier: $("#drpSupplier").val(),
                        plant: $("#drpArrivalPlant").val(),
                        startDate: $("#txtStartDate").val(),                        
                    },
                    success: function (data) {
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }
                });
            }
            $(document).ready(function () {
                //  ShowList();
                });

                $("#btnSearch").on("click", function () {
                // ShowList();
                $StartDate = $("#txtStartDate").val();
                if ($StartDate != '') {
                    ShowList();
                }
                else {
                    alert("Please select Date");
                }
                })

                function FormattedDate(oldformatDate) {
                    var dateObject = new Date(oldformatDate);
                    var FormattedDate = dateObject.getDate()+"/"+(dateObject.getMonth()+1)+"/"+dateObject.getFullYear();
                    return FormattedDate;
            }

            $(document).on("click", ".btnDelete", function() {
                var selectedID = $(this).attr("data-id");
                
                console.log(selectedID);

                if (confirm("Are you sure do you want to delete !") == true) {
                    $.ajax({
                        url: 'Ajax/ajax-hl-va-daily.php',
                        type: 'POST',
                        data: {
                            methodType: "delete",
                            billID: selectedID,
                            center: $("#drpCenter").val(),
                            supplier: $("#drpSupplier").val(),
                            plant: $("#drpArrivalPlant").val(),
                            startDate: $("#txtStartDate").val(),

                        },
                        success: function(data) {
                            alert("Deleted Successfully");
                            $("#divDataList").html(data);
                            fnShowDataTable();
                            return false;
                        }
                    });
                    // console.log(BillID);
                }                
            })
        </script>

    </body>
</html>
