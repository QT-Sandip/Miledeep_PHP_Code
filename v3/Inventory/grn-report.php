<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_INVENTORY_DROPDOWNS;
$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resVendors = $resDropdownsAPI->Data->Vendors;

$PageTitle = "GRN Report";
?>

<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>

    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php"); ?>

        <div class="main-content">
            <div class="page-content">
                <?php include($IncludesPath . "page-title.php"); ?>

                <!-- ================= FILTER SECTION ================= -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <!-- <div class="col-md-3">
                            <label>Doc No</label>
                            <input type="text" id="filter_doc_no" class="form-control">
                        </div> -->
                            <div class="col-md-3">
                                <label>From Date</label>
                                <input type="date" id="filter_from" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>To Date</label>
                                <input type="date" id="filter_to" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Status</label>
                                <select id="filter_status" class="form-control">
                                    <option value="All">All</option>
                                    <option value="Approve">Approve</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button id="btnSearch" class="btn btn-primary">Search</button>
                        </div>
                    </div>
                </div>

                <div id="grnReportTable">
                    <!-- ================= GRN LIST TABLE ================= -->
                </div>



            </div>
        </div>
    </div>
    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

    <script>
        function updateStatus(id) {
            $.ajax({
                url: 'Ajax/Ajax-grn-report.php',
                type: 'POST',
                data: {
                    poId: id,
                    methodType: "updateStatus"
                },
                success: function (data) {
                    if (data.status === 'success') {
                        alert(data.Msg);
                        loadGRNReport();
                    } else {
                        alert(data.Msg);
                    }
                    return false;
                }
            });
        }

        // ================= LOAD GRN REPORT =================
        function loadGRNReport(searchKey = '', loadAll = false) {
            const data = {
                methodType: 'report',
                doc_no: $('#filter_doc_no').val(),
                from_date: $('#filter_from').val(),
                to_date: $('#filter_to').val(),
                status: $('#filter_status').val(),
                searchKey: searchKey,
                loadAll: loadAll
            };

            $.ajax({
                url: 'Ajax/Ajax-grn-report.php',
                type: 'POST',
                data: data,
                success: function (html) {
                    $('#grnReportTable').html(html);
                    fnShowDataTable(searchKey, loadAll);
                },
                error: function (err) {
                    console.error('Error loading GRN report:', err);
                }
            });
        }
        $(document).ready(function () {

            const today = new Date().toISOString().split('T')[0];
            $('#filter_from').val(today);
            $('#filter_to').val(today);

            // ================= INITIALIZE DATATABLE =================
            function fnShowDataTable(searchKey = '', loadAll = false) {

                // Destroy if already exists (prevents reinit error)
                if ($.fn.DataTable.isDataTable('#tblDataTable')) {
                    $('#tblDataTable').DataTable().destroy();
                }

                $('#tblDataTable').DataTable({
                    scrollY: "500px",
                    scrollX: true,
                    pageLength: 25,
                    paging: true,
                    lengthChange: true,
                    fixedColumns: true,
                    responsive: true,
                    scrollCollapse: true,
                    dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rtip',
                    buttons: [{
                        extend: 'excel',
                        className: 'btn btn-success mb-2',
                        text: 'Download Excel',
                        title: 'GRN Report',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }],
                    initComplete: function () {
                        if (!$("#btnMoreSearch").length) {
                            $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                                .insertAfter("#tblDataTable_filter label");
                        }
                        if (!$("#btnLoadAll").length) {
                            $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                                .insertAfter("#btnMoreSearch");
                        }
                    }
                });
            }

            // ================= EVENT LISTENERS =================
            // Default search button
            $('#btnSearch').click(() => loadGRNReport());

            // More Search (use DataTable filter input)
            $(document).on("click", "#btnMoreSearch", function () {
                const searchKey = $("#grnReportTable_filter input").val();
                loadGRNReport(searchKey, false);
            });

            // Load All (ignore filters)
            $(document).on("click", "#btnLoadAll", function () {
                loadGRNReport('', true);
            });

            // ================= INITIAL LOAD =================
            loadGRNReport();
        });
    </script>


</body>

</html>