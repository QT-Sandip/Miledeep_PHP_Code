<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterfromDateOne = "T";
$filtertoDateOne = "T";
$filterButtomButton = "T";
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

<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

<script>
    function fnPoStatusReportExcel(tableID, filename = '') {
        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        var downloadLink;
        var dataType = 'data:application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);

        var clonedTable = tableSelect.cloneNode(true);

        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #eff2f7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                tds[j].style.border = '1px solid #eff2f7';
            }
        }

        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function(cell) {
            cell.style.visibility = 'hidden';
        });

        var tableHTML = clonedTable.outerHTML;

        downloadLink = document.createElement('a');
        document.body.appendChild(downloadLink);

        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            downloadLink.href = dataType + ', ' + encodeURIComponent(tableHTML);
            downloadLink.download = filename;
            downloadLink.click();
        }

        $("#tblDataTable").find(".thead-light  tr").height("0px");
        $("#tblDataTable").find(".thead-light  tr th").height("0px");
        $("#tblDataTable").find(".thead-light  tr th div").height("0px");

        $("#tblDataTable").find("tfoot tr").height("0px");
        $("#tblDataTable").find("tfoot tr td").height("0px");
        $("#tblDataTable").find("tfoot tr th div").height("0px");
    }

    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Traceability Utilized Report1 <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function() {
                    fnPoStatusReportExcel('tblDataTable', 'Traceability Utilized Report');
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

    $(document).on('click', '#btnMoreSearch', function () {
        var pageNo = 1; // or your current page logic
        var searchKey = $('#tblDataTable_filter input').val(); // DataTables search box
        var loadAll = 0; // flag for normal search

        ShowList(pageNo, searchKey, loadAll);
    });

    $(document).on('click', '#btnLoadAll', function () {
        var pageNo = 1;
        var searchKey = $('#tblDataTable_filter input').val();
        var loadAll = 1; // flag for loading all data

        ShowList(pageNo, searchKey, loadAll);
    });

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-traceability-utilized-report1.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    $(document).on("click", "#btnSearch", function() {
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

    $(document).on("click", ".btnPageno", function() {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });
</script>

</body>

</html>
