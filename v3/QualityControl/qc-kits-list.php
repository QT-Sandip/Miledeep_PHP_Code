<?php

session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterfromDates = "T";
$filterToDates = "T";

$filterButtonKits = "T";

$AccessType = $_SESSION['AccessType'];

?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        .highlight {
            border: 2px solid red;
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

                <?php include_once ROOT . '/includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                    <!--  -->
                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->
    <!-- Modal -->

</div>
<!-- END layout-wrapper -->
<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>


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
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Qc Kits List <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
            "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
        )
            .insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList(pageNo, searchkey, loadAll) {
        var accessType = "<?php echo $AccessType ?>";
        $.ajax({
            url: 'Ajax/ajax-qc-kits-list.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
                accessType: accessType,
            },
            success: function (data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    $(document).ready(function () {
        ShowList("1", "", "0");
    });

    $(document).on("click", "#btnMoreSearch", function () {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function () {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function () {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $(document).on("click", "#btnSearch", function () {
        $("#txtFromDate, #txtToDate").removeClass("highlight");

        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();

        if (fromDate === "" || toDate === "") {
            if (fromDate === "") {
                $("#txtFromDate").addClass("highlight");
            }
            if (toDate === "") {
                $("#txtToDate").addClass("highlight");
            }
            return false;
        }

        ShowList("1", "", "0");
    });

    $(document).on("click", ".btnDelete", function () {

        var selectedID = $(this).attr("data-kitid");

        if (confirm("Are you sure you want to delete this item?") == true) {
            $.ajax({
                url: 'Ajax/ajax-qc-kits-list.php',
                type: 'POST',
                data: {
                    KitID: selectedID,
                    Action: "Delete"
                },
                success: function (data) {
                    console.log(data);
                    if (data.Error != '') {
                        alert(data.Msg);

                        ShowList("1", "", "0");
                    } else {
                        alert(data.Error);
                    }
                    return false;
                }
            });
        }
    });

</script>
</body>

</html>