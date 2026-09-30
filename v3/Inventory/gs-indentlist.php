<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

//$filterDailyWages = "T";
$filterToDateNew = "T";
$filterFromDate = "T";
$filterStatus = "T";
$buttonButtom = "T";

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
                title: 'GS-IndentList Report - <?php echo date('Y-m-d') ?> ',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
            },


            ],
        });

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
            "<a class='btn btn-primary' id='LoadAll' href='javascript:void(0);' style='padding: 3px 5px;margin-left: 10px;'>Load All</a>"
        ).insertAfter("#tblDataTable_filter label");
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-gs-indentlist-report.php',
            type: 'POST',
            data: {
                startDate: $("#txtFromDate").val(),
                endDate: $("#txtToDate").val(),
                status: $("#drpStatus").val()
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    function updateStatus(id) {
        $.ajax({
            url: 'Ajax/Ajax-indent.php',
            type: 'POST',
            data: {
                indentId: id,
                methodType: "updateStatus"
            },
            success: function (data) {
                if (data.status === 'success') {
                    alert(data.Msg);
                    ShowList();
                } else {
                    alert(data.Msg);
                }
                return false;
            }
        });
    }

    $(document).on('click', '#btnMoreSearch', function (e) {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", "", "0", searchkey);
        }
    })

    $("#btnSearch").on("click", function () {

        var fromDate = $("#txtFromDate").val();

        if (fromDate) {
            ShowList();
        } else {
            // alert("Enter center");
        }
    })
</script>

</body>

</html>