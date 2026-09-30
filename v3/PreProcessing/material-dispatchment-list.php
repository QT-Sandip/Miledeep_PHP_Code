<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterFromStation = "T";
$filterToStation = "T";
$filterTransactionType = "T";
$filterFromDate = "T";
$filterToDate = "T";
$buttonBottom = "T";

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
                                <!-- <div class="text-center">
                                    <a href="material-dispatchment-create" class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i> New Transfer
                                    </a>
                                </div> -->

                                <div class="text-center">
    <a id="btnNewTransfer" href="#" class="btn btn-success waves-effect waves-light">
        <i class="fa fa-plus"></i> New Transfer
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

        // var drpPurchaseName = $("#select2-drpPurchaseList-container").text();
        // if (drpPurchaseName == "Select Purchase") {
        //     drpPurchaseName = "";
        // }
        var fileName = 'Material-Dispatchment-List-<?php echo date("d-m-Y") ?>';
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

    $(document).ready(function () {
    // Handle dropdown change
    $("#drpTransactionType").on("change", function () {
        var transactionType = $(this).val();

        if (transactionType === "Receiver") {
            $("#btnNewTransfer").addClass("disabled").css("pointer-events", "none");
        } else {
            $("#btnNewTransfer").removeClass("disabled").css("pointer-events", "auto");
        }
    });

    // Handle button click
    $("#btnNewTransfer").on("click", function (e) {
        e.preventDefault();

        var transactionType = $("#drpTransactionType").val();

        if (transactionType === "") {
            alert("Please select a Transaction Type");
            return;
        }

        if (transactionType === "Receiver") {
            return; // Stop if still Receiver
        }

        // Redirect with selected transactionType
        // window.location.href = "material-dispatchment-create?TransactionType=" + encodeURIComponent(transactionType);
        var url = "material-dispatchment-create?TransactionType=" + encodeURIComponent(transactionType);
    window.open(url, '_blank');
    });
});



    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-material-dispatchment-list.php',
            type: 'POST',
            data: {
                fromStation: $("#drpFromStation").val(),
                toStation: $("#drpToStation").val(),
                transactionType: $("#drpTransactionType").val(),
                fromdate: $("#txtFromDate").val(),
                todate: $("#txtToDate").val(),                
                datatype: "getDispatchMaterialList"  ,
                methodType:"GetList"  
                            
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


    $(document).on("click", ".btnDelete", function() {
        var selectedID = $(this).attr("data-material-dispatch-id");
        console.log(selectedID);

        if (confirm("Are you sure do you want to delete !") == true) {
            $.ajax({
                url: 'Ajax/ajax-material-dispatchment-list.php',
                type: 'POST',
                data: {
                    MaterialDispatchID: selectedID,
                    methodType: "delete"
                },
                success: function(data) {
                    if (data.Error == null) {
                        alert(data.Msg);
                        ShowList();
                    }
                    return false;
                }
            });
        } else {

        }
    })
</script>

</body>

</html>