<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$Status = $_SESSION['Status'];
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$User = "general Item";
$PageTitle = $User . " Details";
$PageTitleFolder = "Masters";
?>

<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

    <!-- jQuery (required before Bootstrap JS) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php if ($Status != '') { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            <?php echo $Status;
                            $_SESSION['Status'] = ''; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <form id="generalItemForm">
                        <input type="hidden" id="generalItem_id" name="generalItem_id" value="">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <!-- ================= GENERAL DETAILS ================= -->
                                        <h4 class="mb-3">General Details</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Name</label>
                                                <input type="text" class="form-control" id="generalItem_name"
                                                    name="generalItem_name" placeholder="Enter Name">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Code</label>
                                                <input type="text" class="form-control" id="generalItem_code"
                                                    name="generalItem_code" placeholder="Enter Code">
                                            </div>

                                            <div class="col-md-4">
                                                <label>Category</label>
                                                <select id="category" name="category" class="form-control">
                                                    <option value="">Select Category</option>
                                                    <option value="General">General</option>
                                                    <option value="Packaging_Related ">Packaging Related </option>
                                                    <option value="Sanitory">Sanitory</option>
                                                    <option value="Chemical">Chemical</option>
                                                    <option value="Stationery">Stationery</option>
                                                    <option value="Uniforms">Uniforms</option>
                                                    <option value="Production">Production</option>
                                                    <option value="Machinery">Machinery</option>
                                                    <option value="Lab">Lab</option>
                                                    <option value="Civil">Civil</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label>Reorder Quantity</label>
                                                <input type="number" class="form-control" id="reorder_qty"
                                                    name="reorder_qty" placeholder="Enter Reorder Quantity">
                                            </div>
                                            <!-- <div class="col-md-4 mt-3">
                                                <label>Valuation Method</label>
                                                <select id="valuation_method" name="valuation_method"
                                                    class="form-control">
                                                    <option value="">Select Valuation Method</option>
                                                    <option value="General">General</option>
                                                    <option value="Preferred">Preferred</option>
                                                </select>
                                            </div> -->
                                            <div class="col-md-4 mt-3">
                                                <label>Revision Date</label>
                                                <input type="date" class="form-control" id="revision_date"
                                                    name="revision_date" placeholder="Enter Revision Date">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Item Type</label>
                                                <select id="item_type" name="item_type" class="form-control">
                                                    <option value="">Select Item Type</option>
                                                    <option value="Consumable">Consumable</option>
                                                    <option value="Production Consumable">Production Consumable</option>
                                                    <option value="Need">Need</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>

                                            <!-- <div class="col-md-4 mt-3">
                                                <label>Item Make</label>
                                                <select id="item_make" name="item_make" class="form-control">
                                                    <option value="">Select Item Make</option>
                                                    <option value="Item">Item</option>
                                                </select>
                                            </div> -->
                                            <!-- <div class="col-md-4 mt-3">
                                                <label>Printer</label>
                                                <input type="text" class="form-control" id="printer" name="printer"
                                                    placeholder="Enter Printer">
                                            </div> -->

                                            <!-- <div class="col-md-4 mt-3">
                                                <label>Sub Category</label>
                                                <select id="sub_category" name="sub_category" class="form-control">
                                                    <option value="">Select Sub Category</option>
                                                    <option value="category1">Category 1</option>
                                                    <option value="category2">Category 2</option>
                                                </select>
                                            </div> -->
                                            <div class="col-md-6 mt-3">
                                                <label>Description</label>
                                                <input type="text" class="form-control" id="description"
                                                    name="description" placeholder="Enter Description">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label>Image</label>
                                                <input type="file" class="form-control" id="item_image" name="image"
                                                    placeholder="Select Image">
                                            </div>
                                            <!-- <div class="col-md-4 mt-3">
                                                <label>Perishable Item</label>
                                                <select id="peridhable_item" name="peridhable_item"
                                                    class="form-control">
                                                    <option value="YES">Yes</option>
                                                    <option value="NO">No</option>
                                                </select>
                                            </div> -->
                                            <div class="col-md-4 mt-3" style="display:none">
                                                <label>Group</label>
                                                <select id="group" name="group" class="form-control">
                                                    <option value="">Select Group</option>
                                                    <option value="General_Items">General Items</option>
                                                    <option value="Consumables">Consumables</option>
                                                </select>
                                            </div>
                                        </div>
                                        <hr>




                                        <!-- ================= Units ================= -->
                                        <h4 class="mt-4 mb-3">Units</h4>
                                        <div class="row">
                                            <div class="col-md-4 mt-3">
                                                <label>Default Base Unit</label>
                                                <select class="form-control" name="default_base_unit"
                                                    id="default_base_unit">
                                                    <option value="">Select Default Base Unit</option>
                                                    <option value="Nos">Nos</option>
                                                    <option value="Pairs">Pairs</option>
                                                    <option value="Kgs">Kgs</option>
                                                    <option value="Liters">Liters</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Default Issue Unit</label>
                                                <select class="form-control" name="default_sales_unit"
                                                    id="default_sales_unit">
                                                    <option value="">Select Default Sales Unit</option>
                                                    <option value="Nos">Nos</option>
                                                    <option value="Pairs">Pairs</option>
                                                    <option value="Kgs">Kgs</option>
                                                    <option value="Liters">Liters</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Default Purchase Unit</label>
                                                <select class="form-control" name="default_purchase_unit"
                                                    id="default_purchase_unit">
                                                    <option value="">Select Default Purchase Unit</option>
                                                    <option value="Nos">Nos</option>
                                                    <option value="Pairs">Pairs</option>
                                                    <option value="Kgs">Kgs</option>
                                                    <option value="Liters">Liters</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Length</label>
                                                <input type="text" class="form-control" id="length" name="length"
                                                    placeholder="Enter Length">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Width</label>
                                                <input type="text" class="form-control" id="width" name="width"
                                                    placeholder="Enter Width">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Height</label>
                                                <input type="text" class="form-control" id="height" name="height"
                                                    placeholder="Enter Height">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Weight</label>
                                                <input type="text" class="form-control" id="weight" name="weight"
                                                    placeholder="Enter Weight">
                                            </div>
                                        </div>
                                        <hr>

                                        <!-- ================= Settings ================= -->
                                        <h4 class="mt-4 mb-3">Settings</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Purchase Cost</label>
                                                <input type="text" class="form-control" id="standard_cost"
                                                    name="standard_cost" placeholder="Enter Standard Cost">
                                            </div>
                                            <!-- <div class="col-md-4">
                                                <label>Overhead Cost</label>
                                                <input type="text" class="form-control" id="overhead_cost"
                                                    name="overhead_cost" placeholder="Enter Overhead Cost">
                                            </div> -->
                                            <!-- <div class="col-md-4">
                                                <label>Indirect Cost %</label>
                                                <input type="text" class="form-control" id="indirect_cost"
                                                    name="indirect_cost" placeholder="Enter Indirect Cost">
                                            </div> -->

                                            <!-- <div class="col-md-4 mt-3">
                                                <label>Profit %</label>
                                                <input type="text" class="form-control" id="profit" name="profit"
                                                    placeholder="Enter Profit">
                                            </div> -->
                                            <div class="col-md-4">
                                                <label>Don't Show Products Expiring In (Days)</label>
                                                <input type="text" class="form-control" id="expiring_days"
                                                    name="expiring_days" placeholder="Enter Products Expiring">
                                            </div>
                                            <!-- <div class="col-md-4 mt-3">
                                                <label>GST %</label>
                                                <input type="text" class="form-control" id="gst" name="gst"
                                                    placeholder="Enter GST">
                                            </div> -->

                                        </div>
                                        <hr>

                                        <!-- ================= Clasification ================= -->
                                        <h4 class="mt-4 mb-3" style="display:none">Clasification</h4>
                                        <div class="row" style="display:none">
                                            <div class="col-md-6">
                                                <label>ABC-Code Value</label>
                                                <select id="abc_value" name="abc_value" class="form-control">
                                                    <option value="">Select Value</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label>ABC-Code Margin</label>
                                                <select id="abc_margin" name="abc_margin" class="form-control">
                                                    <option value="">Select Margin</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label>ABC-Code Revenue</label>
                                                <select id="abc_revenue" name="abc_revenue" class="form-control">
                                                    <option value="">Select Revenue</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mt-3">
                                                <label>ABC-Code Carying Cost</label>
                                                <select id="abc_carying_cost" name="abc_carying_cost"
                                                    class="form-control">
                                                    <option value="">Select Carying Cost</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label>Barcode 1</label>
                                                <input type="text" class="form-control" id="barcode1" name="barcode1"
                                                    placeholder="Enter barcode1">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label>Barcode 2</label>
                                                <input type="text" class="form-control" id="barcode2" name="barcode2"
                                                    placeholder="Enter barcode2">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label>Barcode 3</label>
                                                <input type="text" class="form-control" id="barcode3" name="barcode3"
                                                    placeholder="Enter barcode3">
                                            </div>
                                        </div>
                                        <!-- ================= Other Details ================= -->
                                        <!-- <h4 class="mt-4 mb-3">Other Details</h4> -->
                                        <!-- ================= BUTTONS ================= -->
                                        <div class="text-center mt-4">
                                            <a href="general-items" class="btn btn-danger">Close</a>
                                            <button class="btn btn-primary" name="submit" id="btnSavegeneralItem"
                                                type="submit">Save</button>
                                        </div>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>

                    <!-- generalItem list will load here -->
                    <div id="generalItemListDiv" class="mt-4"></div>

                </div> <!-- container-fluid -->
                <?php include($IncludesPath . "footer.php"); ?>
            </div> <!-- page-content -->
        </div> <!-- main-content -->

    </div> <!-- END layout-wrapper -->


    <!-- General Item View Modal -->
    <div class="modal fade" id="generalItemViewModal" tabindex="-1" aria-labelledby="generalItemViewLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="generalItemViewLabel">General Item Details</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>
                <div class="modal-body" id="generalItemDetailsBody">
                    <!-- Item details will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>






    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>



    <script>
        $(document).ready(function () {

            function fnShowDataTable() {

                var table = $('#tblDataTable').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    // paging: false,
                    paging: true, //  Pagination Enabled
                    pageLength: 25, //  Default 25 rows per page
                    lengthMenu: [10, 25, 50, 100, -1], // Same as other modules
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse: true,

                    dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rtip',

                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btnExcelDataTable btn btn-success',
                        title: 'General Items',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // ----------------------------------------------------
                // ADD MORE SEARCH + LOAD ALL (SAME AS DEPARTMENT CODE)
                // ----------------------------------------------------
                setTimeout(() => {

                    // More Search Button
                    if (!$("#btnMoreSearch").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                            .insertAfter("#tblDataTable_filter label");
                    }

                    // Load All Button
                    if (!$("#btnLoadAll").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                            .insertAfter("#btnMoreSearch");
                    }

                }, 200);
            }

            // Load generalItem list on page load
            loadgeneralItems();

            // Save generalItem
            $('#btnSavegeneralItem').click(function (e) {
                e.preventDefault();
                var formData = $('#generalItemForm').serialize() + '&methodType=save';
                $.ajax({
                    url: 'Ajax/ajax-general-items.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            alert(res.Msg);
                            loadgeneralItems();
                            $('#btnSavegeneralItem').text('Save');
                            $('#generalItemForm')[0].reset();
                            $('#generalItem_id').val('');
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                        alert('AJAX request failed!');
                    }
                });
            });

            $(document).on('click', '.btnEdit', function () {
                var generalItem_id = $(this).data('id');
                $('#btnSavegeneralItem').text('Update');
                $.ajax({
                    url: 'Ajax/ajax-general-items.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        generalItem_id: generalItem_id
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            var itemData = res.Data;
                            $('#generalItem_id').val(itemData.GeneralItemId);
                            $('#generalItem_name').val(itemData.name);
                            $('#generalItem_code').val(itemData.code);
                            $('#item_type').val(itemData.item_type);
                            $('#reorder_qty').val(itemData.reorder_qty);
                            // $('#group').val(itemData.group);
                            // $('#valuation_method').val(itemData.valuation_method);
                            $('#revision_date').val(itemData.revision_date);
                            $('#category').val(itemData.category);
                            // $('#item_make').val(itemData.item_make);
                            // $('#printer').val(itemData.printer);
                            // $('#sub_category').val(itemData.sub_category);
                            $('#description').val(itemData.description);
                            $('#item_image').val(itemData.image);
                            // $('#peridhable_item').val(itemData.peridhable_item);
                            $('#default_base_unit').val(itemData.default_base_unit);
                            $('#default_sales_unit').val(itemData.default_sales_unit);
                            $('#default_purchase_unit').val(itemData.default_purchase_unit);
                            $('#length').val(itemData.length);
                            $('#width').val(itemData.width);
                            $('#height').val(itemData.height);

                            $('#weight').val(itemData.weight);
                            $('#standard_cost').val(itemData.standard_cost);
                            // $('#overhead_cost').val(itemData.overhead_cost);
                            // $('#indirect_cost').val(itemData.indirect_cost);
                            // $('#profit').val(itemData.profit);
                            $('#expiring_days').val(itemData.expiring_days);
                            // $('#gst').val(itemData.gst);
                            // $('#abc_value').val(itemData.abc_value);
                            // $('#abc_margin').val(itemData.abc_margin);
                            // $('#abc_revenue').val(itemData.abc_revenue);
                            // $('#abc_carying_cost').val(itemData.abc_carying_cost);
                            // $('#barcode1').val(itemData.barcode1);
                            // $('#barcode2').val(itemData.barcode2);
                            // $('#barcode3').val(itemData.barcode3);
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    },
                    error: function () {
                        alert('AJAX request failed!');
                    }
                });
            });

            // Delete generalItem
            $(document).on('click', '.btnDelete', function () {
                if (!confirm('Are you sure you want to delete this generalItem?')) return;
                var generalItem_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/ajax-general-items.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        generalItem_id: generalItem_id
                    },
                    dataType: 'json',
                    success: function (res) {
                        console.log(res);
                        if (res.status === 'success') {
                            alert(res.Msg);
                            loadgeneralItems();
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    },
                    error: function () {
                        alert('AJAX request failed!');
                    }
                });
            });



            $(document).on("click", "#btnLoadAll", function () {
                loadgeneralItems();
            });

            $(document).on("click", "#btnMoreSearch", function () {
                $(this).data("moresearch", true);

                // Get search input from DataTable filter
                let searchKey = $("#tblDataTable_filter input").val();

                // Trigger load with search key
                loadgeneralItems({
                    searchKey: searchKey,
                    loadAll: false
                });
            });



            // Function to load generalItem list
            function loadgeneralItems(options = {}) {

                let searchKey = options.searchKey || "";
                let loadAll = options.loadAll || false;

                $.ajax({
                    url: 'Ajax/ajax-general-items.php',
                    type: 'POST',
                    data: {
                        methodType: 'load',
                        searchKey: searchKey,
                        loadAll: loadAll
                    },
                    success: function (html) {
                        $('#generalItemListDiv').html(html);
                        fnShowDataTable();
                    }
                });
            }
            // ================= VIEW GENERAL ITEM DETAILS =================
            $(document).on('click', '.btnView', function () {
                const generalItemId = $(this).data('id');

                $.ajax({
                    url: 'Ajax/ajax-general-items.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'getSingle',
                        generalItem_id: generalItemId
                    },
                    success: function (res) {
                        console.log('Response:', res); // Debug: Check data

                        // Accept both "success" or "Msg: Success"
                        if ((res.status === 'success' || res.Msg === 'Success') && res.Data) {
                            let item = res.Data;

                            const excludedFields = [
                                'CreatedDate',
                                'UpdatedDate'
                            ];

                            let html = `
                        <div class="container-fluid">
                            <div class="row">
                                ${Object.entries(item)
                                    .filter(([key]) => !excludedFields.includes(key))
                                    .map(([key, value]) => `
                                        <div class="col-md-3 mb-3">
                                            <div class="border rounded p-3 shadow-sm bg-light" 
                                                 style="word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                                <h6 class="text-muted mb-1" style="text-transform: capitalize;">
                                                    ${key.replace(/_/g, ' ')}
                                                </h6>
                                               <p class="fw-semibold mb-0 text-dark">
${value
                                            && (key.toLowerCase().includes('date'))
                                            ? (() => {
                                                const d = new Date(value);
                                                return isNaN(d)
                                                    ? value
                                                    : `${String(d.getDate()).padStart(2, '0')}-${String(d.getMonth() + 1).padStart(2, '0')}-${d.getFullYear()}`;
                                            })()
                                            : (value ?? '-')
                                        }
</p>

                                            </div>
                                        </div>
                                    `).join('')}
                            </div>
                        </div>
                    `;

                            $('#generalItemDetailsBody').html(html);
                            $('#generalItemViewModal').modal('show');
                        } else {
                            alert(res.Msg || 'Failed to fetch item details.');
                        }
                    },
                    error: function (err) {
                        console.error(err);
                        alert('Error loading item details.');
                    }
                });
            });


        });
    </script>


</body>

</html>