<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterArrivalPlant = "T";
$filterCenter = "T";
$filterAgent = "T";
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
        var CenterName= $("#select2-drpCenter-container").text();
        var StartDate= FormattedDate ($("#txtStartDate").val());             
           //   var EndDate= FormattedDate($("#txtEndDate").val());
         
            var DatePeriod= StartDate  ;

            if(CenterName=="Select Center") {
                CenterName=' ';
               }
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: CenterName + '  Details Of Supplier Wise Out Standing Amount on Date : - ' +DatePeriod,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    function ShowList() {

        $.ajax({
            url: 'Ajax/ajax-agent-billwise-outstanding-report.php',
            type: 'POST',
            data: {
                Plant: $("#drpArrivalPlant").val(),
                Center: $("#drpCenter").val(),
                Agent: $("#drpAgent").val(),
                startDate: $("#txtStartDate").val()
                
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
        $EndDate = $("#txtEndDate").val();
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
</script>

</body>

</html>