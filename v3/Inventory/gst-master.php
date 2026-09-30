<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_INVENTORY_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resGeneralItems = $resDropdownsAPI->Data->GeneralItems;
$resVendors = $resDropdownsAPI->Data->Vendors;
// echo print_r($resVendors);exit;

$User = "GST Master";
$PageTitle = $User . " Details";
$PageTitleFolder = "Masters";

// Example categories (can be fetched from DB later)
$units = ["Nos", "Kgs", "Lts", "Lbs"];
$status_options = ["Active", "Inactive"];
?>

<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">


                    <?php include($IncludesPath . "page-title.php"); ?>

                    <form id="gstForm">
                        <input type="hidden" id="price_db_id" name="price_db_id" value="">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-md-6">
                                                <label>General Item</label>
                                                <select class="form-control" id="item_id" name="item_id">
                                                    <option value="">Select Item</option>
                                                    <?php foreach ($resGeneralItems as $item): ?>
                                                        <option value="<?php echo $item->GeneralItemId ?>">
                                                            <?php echo $item->name ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label>Vendor</label>
                                                <select class="form-control" id="vendor_id" name="vendor_id">
                                                    <option value="">Select Vendor</option>
                                                    <?php foreach ($resVendors as $vendor): ?>
                                                        <option value="<?php echo $vendor->VendorID ?>">
                                                            <?php echo $vendor->VendorName ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">

                                            <div class="col-md-6">
                                                <label>From Date</label>
                                                <input type="date" class="form-control" id="from_date" name="from_date"
                                                    placeholder="Enter Price Name">
                                            </div>

                                            <div class="col-md-6">
                                                <label>Units</label>
                                                <select class="form-control" id="units" name="units">
                                                    <option value="">Select Units</option>
                                                   <option value="Nos">Nos</option>
                                                    <option value="Pairs">Pairs</option> 
                                                    <option value="Kgs">Kgs</option>
                                                    <option value="Liters">Liters</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label>GST %</label>
                                                <input type="text" class="form-control" id="gst" name="gst"
                                                    placeholder="Enter GST">
                                            </div>
                                            <div class="col-md-6">
                                                <label>Narration</label>
                                                <input type="text" class="form-control" id="narration" name="narration"
                                                    placeholder="Enter Narration">
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label>Approved By</label>
                                                <input type="text" class="form-control" id="approved_by" name="approved_by"
                                                    placeholder="Enter Approved By">
                                            </div>
                                        </div>

                                        <!-- ================= BUTTONS ================= -->
                                        <div class="text-center mt-4">
                                            <a href="price-master" class="btn btn-danger">Close</a>
                                            <button class="btn btn-primary" name="submit" id="btnSaveGst"
                                                type="submit">Save</button>
                                        </div>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>

                    <!-- Price list -->
                    <div id="priceListDiv" class="mt-4"></div>

                </div> <!-- container-fluid -->
                <?php include($IncludesPath . "footer.php"); ?>
            </div> <!-- page-content -->
        </div> <!-- main-content -->

    </div> <!-- END layout-wrapper -->

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

    <script>
        $(document).ready(function() {

            // Load price list on page load
            loadPrices();

            // ================== SAVE DEPARTMENT ==================
            $('#btnSaveGst').click(function(e) {
                e.preventDefault();
                var formData = $('#gstForm').serialize() + '&methodType=save';
                $.ajax({
                    url: 'Ajax/ajax-gst-master.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.Msg);
                            loadPrices();
                            $('#btnSaveGst').text('Save');
                            $('#gstForm')[0].reset();
                            $('#price_db_id').val('');
                        } else {
                            alert('Error' + res.Msg);
                        }
                    },
                    error: function() {
                        alert('AJAX request failed!');
                    }
                });
            });

            // ================== EDIT DEPARTMENT ==================
            $(document).on('click', '.btnEdit', function() {
                var price_db_id = $(this).data('id');
                $('#btnSaveGst').text('Update');

                $.ajax({
                    url: 'Ajax/ajax-gst-master.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        price_db_id: price_db_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            var gstData = res.Data;
                            $('#price_db_id').val(gstData.GstMasterId);
                            $('#item_id').val(gstData.GeneralItemId);
                            $('#vendor_id').val(gstData.VendorID);
                            $('#from_date').val(gstData.FromDate);
                            $('#units').val(gstData.Units);
                            $('#gst').val(gstData.Gst);
                            $('#narration').val(gstData.Narration);
                            $('#approved_by').val(gstData.ApprovedBy);
                            // loadPrices();
                        } else {
                            alert('Error loading price data: ' + res.Msg);
                        }
                    }
                });
            });

            // ================== DELETE DEPARTMENT ==================
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this price?')) return;
                var price_db_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/ajax-gst-master.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        price_db_id: price_db_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.Msg);
                            loadPrices();
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    }
                });
            });

            function fnShowDataTable() {

                table = $('#tblDataTable').DataTable({
                   scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100, -1],
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse: true,
                    dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rtip',
                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btnExcelDataTable btn btn-success',
                        title: 'GST List',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // -----------------------------
                // ADD MORE SEARCH + LOAD ALL BUTTONS
                // -----------------------------
                setTimeout(() => {

                    // More Search button
                    if (!$("#btnMoreSearch").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                            .insertAfter("#tblDataTable_filter label");
                    }

                    // Load All button
                    if (!$("#btnLoadAll").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                            .insertAfter("#btnMoreSearch");
                    }

                }, 200);
            }


            // --------------------------------
            // BUTTON EVENTS
            // --------------------------------

            // MORE SEARCH CLICKED
            $(document).on("click", "#btnMoreSearch", function() {
                $(this).data("moresearch", true);

                let searchKey = $("#tblDataTable_filter input").val().trim();

                if (searchKey.length > 0) {
                    loadPrices({
                        searchKey: searchKey,
                        loadAll: false
                    });
                }
            });

            // LOAD ALL CLICKED
            $(document).on("click", "#btnLoadAll", function() {
                $(this).data("loadall", true);

                loadPrices({
                    searchKey: "",
                    loadAll: true
                });
            });


            function loadPrices({
                searchKey = "",
                loadAll = false
            } = {}) {
                $.ajax({
                    url: 'Ajax/ajax-gst-master.php',
                    type: 'POST',
                    data: {
                        methodType: 'load',
                        searchKey: searchKey,
                        loadAll: loadAll
                    },
                    success: function(html) {
                        $('#priceListDiv').html(html);
                        fnShowDataTable();
                    }
                });
            }


        });
    </script>
</body>

</html>


</body>

</html>