<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// Fetch suppliers for dropdown
$api = API_INVENTORY_DROPDOWNS;
$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resSuppliers = $resDropdownsAPI->Data->Vendors ;

$PageTitle = "Purchase Report";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "topbar.php"); ?>
    <?php include($IncludesPath . "sidebar.php"); ?>

    <div class="main-content">
        <div class="page-content container-fluid">
            <?php include($IncludesPath . "page-title.php"); ?>

            <!-- FILTER SECTION -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
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
                                <option value="">All</option>
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

            <!-- PURCHASE LIST TABLE -->
            <table id="purchaseReportTable" class="table table-bordered table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>S.No</th>

                        <th>Doc No</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>
    </div>

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

    <script>
        $(document).ready(function() {

            const today = new Date().toISOString().split('T')[0];
            $('#filter_from').val(today);
            $('#filter_to').val(today);

            // MAIN LOAD FUNCTION
            function loadPurchaseReport(searchKey = '', loadAll = false) {

                $.ajax({
                    url: 'Ajax/ajax-pm-purchaseReport.php',
                    type: 'POST',
                    data: {
                        methodType: 'report',
                        from_date: loadAll ? '' : $('#filter_from').val(),
                        to_date: loadAll ? '' : $('#filter_to').val(),
                        status: loadAll ? '' : $('#filter_status').val(),
                        searchKey: searchKey,
                        loadAll: loadAll
                    },
                    success: function(html) {
                        $('#purchaseReportTable tbody').html(html);

                        setTimeout(function() {
                            initPurchaseReportDataTable();
                        }, 100);
                    },
                    error: function(err) {
                        console.error('Error loading Purchase report:', err);
                    }
                });
            }

            // DATATABLE INIT
            function initPurchaseReportDataTable() {

                if ($.fn.DataTable.isDataTable("#purchaseReportTable")) {
                    $("#purchaseReportTable").DataTable().clear().destroy();
                }

                $('#purchaseReportTable').DataTable({
                    scrollY: "500px",
                    scrollX: true,
                    paging: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100, -1], // 👈 Load All
                    fixedColumns: true,
                    scrollCollapse: true,
                    ordering: false,
                    dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rtip',
                    buttons: [{
                        extend: 'excel',
                        className: 'btn btn-success',
                        text: 'Download Excel',
                        title: 'Purchase Report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // Inject buttons near search
                setTimeout(function() {

                    if (!$("#btnMoreSearch").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                            .insertAfter("#purchaseReportTable_filter label");
                    }

                    if (!$("#btnLoadAll").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                            .insertAfter("#btnMoreSearch");
                    }

                }, 200);
            }

            // SEARCH BUTTON
            $('#btnSearch').click(function() {
                loadPurchaseReport();
            });

            // MORE SEARCH (keeps filter + datatable search)
            $(document).on("click", "#btnMoreSearch", function() {
                const searchKey = $("#purchaseReportTable_filter input").val();
                loadPurchaseReport(searchKey, false);
            });

            // LOAD ALL (ignores date & status)
            $(document).on("click", "#btnLoadAll", function() {
                loadPurchaseReport('', true);
            });

            // INITIAL LOAD
            loadPurchaseReport();
        });


        function updatePurchaseStatus(id, el) {

            // IMMEDIATE UI CHANGE
            el.innerText = 'Pending';
            el.style.color = 'red';
            el.style.cursor = 'default';
            el.removeAttribute('onclick');

            // BACKGROUND DB UPDATE
            $.ajax({
                url: 'Ajax/ajax-pm-purchaseReport.php', // your purchase AJAX endpoint
                type: 'POST',
                dataType: 'json',
                data: {
                    methodType: 'updateStatus',
                    purchaseId: id // send the purchase ID
                },
                success: function(res) {
                    // optional: you can reload table here if needed
                    // loadPurchaseReport();
                }
            });
        }
    </script>


</body>

</html>