<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterFromStation = "T";
$filterToStation = "T";
$buttonBottom = "T";
$filterFromDate = "T";
$filterToDate = "T";
$PlantID = $_SESSION['UnitID'];
$AccessType = $_SESSION['AccessType'];

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

       
        var fileName = 'Material-Dispatchment-Report-<?php echo date("d-m-Y") ?>';
        console.log(fileName);

        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: fileName,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    
    }

    $(document).ready(function() {
       
    });

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-material-dispatchment-report.php',
            type: 'POST',
            data: {
                fromStation: $("#drpFromStation").val(),
                toStation: $("#drpToStation").val(),
                fromdate: $("#txtFromDate").val(),
                todate: $("#txtToDate").val(), 
                            
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    $("#btnSearch").on("click", function() {
        if($("#txtFromDate").val() == "") {
            alert("Please select  From Date");
            return; 
                } 
        if($("#txtToDate").val() == "") {
            alert("Please select  To Date");
            return; 
        } 
        if($("#drpFromStation").val() == ""  ) {
            alert("Please select  From Station");
            return; 

        } 
        ShowList();
    })


  
</script>

</body>

</html>