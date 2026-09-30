<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$PageTitle = "Goods Receipt Report";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "topbar.php"); ?>
    <?php include($IncludesPath . "sidebar.php"); ?>

    <div class="main-content">
        <div class="page-content container-fluid">
            <?php include($IncludesPath . "page-title.php"); ?>

            <!-- FILTER -->
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
                                <option value="0">Approve</option>
                                <option value="1">Pending</option>
                                <option value="2">Completed</option>
                            </select>


                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button id="btnSearch" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <table id="goodsReportTable" class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>S.No</th>
                        <th>Doc No</th>
                        <th>Date</th>
                        <th>Vendor</th>
                        <th>Invoice No</th>
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

            function loadGoodsReport(searchKey = '', loadAll = false) {
                $.ajax({
                    url: 'Ajax/ajax-pm-goodsnoteReport.php',
                    type: 'POST',
                    data: {
                        methodType: 'report',
                        from_date: loadAll ? '' : $('#filter_from').val(),
                        to_date: loadAll ? '' : $('#filter_to').val(),
                        status: $('#filter_status').val(),
                        searchKey: searchKey,
                        loadAll: loadAll

                    },
                    success: function(html) {
                        $('#goodsReportTable tbody').html(html);
                    }
                });
            }

           

            $('#btnSearch').click(() => loadGoodsReport());
            loadGoodsReport();

        });





        function updateGoodsStatus(id, el) {

            //  IMMEDIATE UI CHANGE
            el.innerText = 'Pending';
            el.style.color = 'red';
            el.style.cursor = 'default';
            el.removeAttribute('onclick');

            //  BACKGROUND DB UPDATE
            $.ajax({
                url: 'Ajax/ajax-pm-goodsnoteReport.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    methodType: 'updateStatus',
                    grnId: id
                },
                success: function(res) {
                    // optional silent refresh later
                    // loadGoodsReport();
                }
            });
        }
    </script>

</body>

</html>