<?php
session_start();
$Status = isset($_SESSION['Status']) ? $_SESSION['Status'] : '';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_INVENTORY_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resGeneralItems = $resDropdownsAPI->Data->GeneralItems;
$resVendors = $resDropdownsAPI->Data->Vendors;

$User = "Purchase Order (General Store)";
$PageTitle = $User . " Details";
$PageTitleFolder = "PO-General-Store";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        #workflowTable {
            table-layout: fixed;
            min-width: 1800px;
            /* wide enough for all columns */
            width: 100%;
            border-collapse: collapse;
        }

        #workflowTable th,
        #workflowTable td {
            text-align: center;
            vertical-align: middle;
            padding: 5px;
            border: 1px solid #ddd;
            white-space: nowrap;
            box-sizing: border-box;
            /* ensures padding stays inside cell */
        }

        /* Column widths (adjusted proportionally) */
        #workflowTable th:nth-child(1),
        td:nth-child(1) {
            width: 120px;
        }

        /* Item */
        #workflowTable th:nth-child(2),
        td:nth-child(2) {
            width: 60px;
        }

        /* Unit */
        #workflowTable th:nth-child(3),
        td:nth-child(3) {
            width: 90px;
        }

        /* Available Qty */
        #workflowTable th:nth-child(4),
        td:nth-child(4) {
            width: 80px;
        }

        /* Unit Price */
        #workflowTable th:nth-child(5),
        td:nth-child(5) {
            width: 80px;
        }

        /* Quantity */
        #workflowTable th:nth-child(6),
        td:nth-child(6) {
            width: 90px;
        }

        /* Supplier Rate */
        #workflowTable th:nth-child(7),
        td:nth-child(7) {
            width: 100px;
        }

        /* Total Value */
        #workflowTable th:nth-child(8),
        td:nth-child(8) {
            width: 70px;
        }

        /* Discount% */
        #workflowTable th:nth-child(9),
        td:nth-child(9) {
            width: 70px;
        }

        /* Discount */
        #workflowTable th:nth-child(10),
        td:nth-child(10) {
            width: 70px;
        }

        /* GST% */
        #workflowTable th:nth-child(11),
        td:nth-child(11) {
            width: 90px;
        }

        /* SGST Amt */
        #workflowTable th:nth-child(12),
        td:nth-child(12) {
            width: 90px;
        }

        /* CGST Amt */
        #workflowTable th:nth-child(13),
        td:nth-child(13) {
            width: 90px;
        }

        /* IGST Amt */
        #workflowTable th:nth-child(14),
        td:nth-child(14) {
            width: 100px;
        }

        /* Tax Amount */
        #workflowTable th:nth-child(15),
        td:nth-child(15) {
            width: 100px;
        }

        /* Invoice Amt */
        #workflowTable th:nth-child(16),
        td:nth-child(16) {
            width: 120px;
        }

        /* Dispatched Date */
        #workflowTable th:nth-child(17),
        td:nth-child(17) {
            width: 150px;
        }

        /* L-Purchase Req No */
        #workflowTable th:nth-child(18),
        td:nth-child(18) {
            width: 150px;
        }

        /* Remarks */

        /* Inputs and selects fill the cell correctly */
        #workflowTable input,
        #workflowTable select {
            width: 100%;
            box-sizing: border-box;
            padding: 4px 6px;
            font-size: 14px;
            text-align: center;
        }


        .select-status-container {
            max-width: 440px !important;
            /* left: -12px !important;
            bottom: 38px !important; */
        }

        .status {
            margin-top: 33px !important;
            margin-left: -236px !important;
            max-width: 345px !important;
        }
    </style>

</head>

<body data-sidebar="dark">
    <?php include($IncludesPath . "PreLoader.php"); ?>

    <div id="layout-wrapper">
        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <!-- ================= PURCHASE ORDER FORM ================= -->
                    <form id="poForm" enctype="multipart/form-data">
                        <input type="hidden" id="po_id" name="po_id" value="">

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Doc No</label>
                                                <select class="form-control" id="doc_no" name="doc_no">
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label>Code</label>
                                                <input type="text" class="form-control" id="code" name="code" placeholder="Enter Code">
                                            </div>

                                            <div class="col-md-3">
                                                <label>Date</label>
                                                <input type="date" class="form-control" id="pdate" name="pdate"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>

                                            <div class="col-md-3">
                                                <label>Supplier Firm Name</label>
                                                <select class="form-control" id="vendor_id" name="vendor_id">
                                                    <option value="">Select Vendor</option>
                                                    <?php foreach ($resVendors as $vendor): ?>
                                                        <option value="<?php echo $vendor->VendorID ?>">
                                                            <?php echo $vendor->VendorName ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-3">
                                                <label>Payment & Terms</label>
                                                <select class="form-control" id="payment_terms" name="payment_terms">
                                                    <option value="">Select Terms</option>
                                                    <option value="Advance">Advance</option>
                                                    <option value="30_Days">30 Days</option>
                                                    <option value="45_Days">45 Days</option>
                                                </select>
                                            </div>



                                            <!-- <div class="col-md-3">
                                                <label>Is IGST</label>
                                                <select class="form-control" id="is_igst" name="is_igst">
                                                    <option value="">Select</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div> -->


                                            <div class="col-md-3">
                                                <label>Freight Type</label>
                                                <select class="form-control" id="freight_type" name="freight_type">
                                                    <option value="">Select Freight Type</option>
                                                    <option value="Paid">Inclusive</option>
                                                    <option value="To_Pay">Exclusive</option>
                                                    <!-- <option value="Free">Free</option> -->
                                                </select>
                                            </div>

                                            <!-- <div class="col-md-3">
                                                <label>Sub Delivery</label>
                                                <select class="form-control" id="sub_delivery" name="sub_delivery">
                                                    <option value="">Select Delivery</option>
                                                    <option value="Immediate">Immediate</option>
                                                    <option value="Week">Within a Week</option>
                                                    <option value="Month">Within a Month</option>
                                                </select>
                                            </div> -->

                                            <div class="col-md-3">
                                                <label>Sub Delivery Date</label>
                                                <input type="date" class="form-control" id="sub_delivery" name="sub_delivery">
                                            </div>

                                            <div class="col-md-3">
                                                <label>Narration</label>
                                                <input type="text" class="form-control" id="narration" name="narration"
                                                    placeholder="Enter Narration">
                                            </div>



                                        </div>


                                        <div class="row mt-2 mb-3">
                                            <div class="col-md-4">
                                                <label>Status</label>
                                                <input type="text" class="form-control status" id="status" name="status" value="Approve" readonly>
                                            </div>
                                        </div>


                                        <hr class="mt-5">

                                        <h4 class="mt-4 mb-3">Work Flow (From Indent)</h4>
                                        <div style="overflow-x:auto; width:100%;">
                                            <table class="table table-bordered" id="workflowTable">
                                                <thead>
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Unit</th>
                                                        <th>Available Qty</th>
                                                        <th>Unit Price</th>
                                                        <th>Quantity</th>
                                                        <th>Supplier Rate</th>
                                                        <th>Total Value</th>
                                                        <th>Discount%</th>
                                                        <th>Discount</th>
                                                        <th>GST%</th>
                                                        <th>SGST Amt</th>
                                                        <th>CGST Amt</th>
                                                        <th>IGST Amt</th>
                                                        <th>Tax Amount</th>
                                                        <th>Invoice Amt</th>
                                                        <th>Dispatched Date</th>
                                                        <!-- <th>L-Purchase Req No</th> -->
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" class="breakup_id" name="breakup_id[]"
                                                                value="">


                                                            <select class="form-control item" name="item[]">
                                                                <option value="">Select Item</option>
                                                                <?php foreach ($resGeneralItems as $item): ?>
                                                                    <option value="<?php echo $item->GeneralItemId ?>">
                                                                        <?php echo $item->name ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        <td><input type="text" class="form-control uom" name="uom[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control avail_qty"
                                                                name="avail_qty[]" readonly></td>
                                                        <td><input type="number" class="form-control item_price"
                                                                name="item_price[]" readonly></td>
                                                        <td><input type="number" class="form-control requested_qty"
                                                                name="requested_qty[]" placeholder="0.00" min="0"></td>
                                                        <td><input type="number" class="form-control supplier_rate"
                                                                name="supplier_rate[]" readonly></td>
                                                        <td><input type="number" class="form-control total_value"
                                                                name="total_value[]" readonly></td>
                                                        <td><input type="number" class="form-control discount_perc"
                                                                name="discount_perc[]" placeholder="0.00"></td>
                                                        <td><input type="number" class="form-control discount"
                                                                name="discount[]" value="0.00" readonly></td>
                                                        <td><input type="number" class="form-control gst" name="gst[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control sgst" name="sgst[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control cgst" name="cgst[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control igst" name="igst[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control tax_amt"
                                                                name="tax_amt[]" value="0.00" readonly></td>
                                                        <td><input type="number" class="form-control invoice_amt"
                                                                name="invoice_amt[]" value="0.00" readonly></td>
                                                        <td><input type="date" class="form-control disp_date"
                                                                name="disp_date[]"></td>
                                                        <!-- <td><input type="text" class="form-control pur_req"
                                                                name="pur_req[]"></td> -->
                                                        <td><input type="text" class="form-control remarks"
                                                                name="remarks[]"></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>



                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSavePO" class="btn btn-primary">Save</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- PO Records Section -->
                    <div class="mt-4">
                        <h4>Existing POs</h4>
                        <div id="poListDiv"></div>
                    </div>

                </div>
                <?php include($IncludesPath . "footer.php"); ?>
            </div>
        </div>
    </div>

    <?php include($IncludesPath . "vendor-scripts.php"); ?>


    <!-- ========== MODAL FOR VIEW ========== -->
    <div class="modal fade" id="poViewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">PO Details</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>
                <div class="modal-body" id="poDetailsBody">
                    <!-- PO details will be dynamically loaded here -->
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div> -->
            </div>
        </div>
    </div>





    <!-- Required datatable js -->
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <!-- DataTables Buttons extension (required for the Excel/Buttons) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {

            getIndents();

            function loadPOList(searchKey = "", loadAll = false) {
                $.ajax({
                    url: 'Ajax/ajax-po-general-stores.php',
                    type: 'POST',
                    data: {
                        methodType: 'load',
                        searchKey: searchKey,
                        loadAll: loadAll
                    },
                    success: function(html) {
                        $('#poListDiv').html(html);
                        fnShowPODataTable(); // Correct function name
                    }
                });
            }


            // Initialize PO DataTable
            function fnShowPODataTable() {

                if ($.fn.DataTable.isDataTable("#tblDataTable")) {
                    $("#tblDataTable").DataTable().destroy();
                }

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

                    dom: 'Bfrtip',

                    buttons: [{
                        extend: 'excelHtml5',
                        text: 'Download Excel',
                        className: 'btn btn-success',
                        title: 'General Store List',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // -----------------------------------------
                // More Search + Load All Buttons
                // -----------------------------------------
                setTimeout(() => {

                    // More Search
                    if (!$("#btnMoreSearch").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                            .insertAfter("#tblDataTable_filter label");
                    }

                    // Load All
                    if (!$("#btnLoadAll").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                            .insertAfter("#btnMoreSearch");
                    }

                }, 200);
            }




            // ------------------- MORE SEARCH -------------------
            $(document).on("click", "#btnMoreSearch", function() {

                $(this).data("moresearch", true);

                let searchKey = $("#tblDataTable_filter input").val();

                loadPOList(searchKey, false); // FIXED
            });


            // ------------------- LOAD ALL -------------------
            $(document).on("click", "#btnLoadAll", function() {

                $(this).data("loadall", true);

                loadPOList("", true); // FIXED

            });



            function getIndents() {
                $.ajax({
                    url: 'Ajax/ajax-po-general-stores.php',
                    type: 'POST',
                    data: {
                        methodType: 'indents'
                    },
                    success: function(res) {
                        var list = "<option value=''>Select</option>";
                        if (res && res.Data) {
                            $.each(res.Data, function(index, value) {
                                list += "<option value=" + value.Id + ">" + value.DocNo + "</option>";
                            });
                        }

                        $('#doc_no').html(list).trigger('change.select2');
                    }
                });
            }

            $("#doc_no").on("change", function() {
                getIndentsBreakUp();
            });

            // function getIndentsBreakUp() {
            //     $.ajax({
            //         url: 'Ajax/ajax-po-general-stores.php',
            //         type: 'POST',
            //         data: {
            //             indent_id: $("#doc_no").val(),
            //             methodType: 'indentsBreakUp'
            //         },
            //         success: function(res) {
            //             if (res && res.Data && res.Data.length > 0) {
            //                 var breakUpList = res.Data;
            //                 console.log("break", breakUpList);

            //                 // Save the first row as template before clearing
            //                 var $templateRow = $('#workflowTable tbody tr:first').clone();

            //                 // Remove all rows
            //                 $('#workflowTable tbody').html('');

            //                 $.each(breakUpList, function(index, value) {
            //                     var $newRow = $templateRow.clone();

            //                     // Clear inputs/selects
            //                     $newRow.find('input').val('');
            //                     $newRow.find('select').val('').trigger('change');

            //                     // Set breakup_id
            //                     if (value.breakup_id != null && value.breakup_id != undefined) {
            //                         $newRow.find('input[name="breakup_id[]"]').val(value.breakup_id);
            //                     } else {
            //                         $newRow.find('input[name="breakup_id[]"]').val('');
            //                     }

            //                     // Set item dropdown
            //                     if (value.GeneralItemId != null && value.GeneralItemId != undefined) {
            //                         $newRow.find('.item').val(value.GeneralItemId).trigger('change');
            //                         getItemDetails(value.GeneralItemId, $newRow);
            //                     }

            //                     // If needed, fill other fields (uncomment as needed)
            //                     // $newRow.find('input[name="received_qty[]"]').val(value.received_qty || '');
            //                     // $newRow.find('input[name="location[]"]').val(value.location || '');
            //                     // $newRow.find('input[name="purpose[]"]').val(value.purpose || '');
            //                     // $newRow.find('input[name="remark[]"]').val(value.remark || '');

            //                     // Append new row
            //                     $('#workflowTable tbody').append($newRow);

            //                     // Reinitialize select2 safely (only if loaded)
            //                     if (typeof $.fn.select2 === "function") {
            //                         $newRow.find('.item').select2({
            //                             placeholder: "Select Item",
            //                             width: "100%"
            //                         });
            //                     }
            //                 });
            //             }
            //         }
            //     });
            // }

            function getIndentsBreakUp() {
                $.ajax({
                    url: 'Ajax/ajax-po-general-stores.php',
                    type: 'POST',
                    data: {
                        indent_id: $("#doc_no").val(),
                        methodType: 'indentsBreakUp'
                    },
                    success: function(res) {

                        if (!(res && res.Data && res.Data.length > 0)) return;

                        var breakUpList = res.Data;
                        console.log("break", breakUpList);

                        //  DESTROY select2 on existing rows BEFORE cloning
                        $('#workflowTable tbody .item').each(function() {
                            if ($.fn.select2 && $(this).data('select2')) {
                                $(this).select2('destroy');
                            }
                        });

                        // Clone clean template row
                        var $templateRow = $('#workflowTable tbody tr:first').clone(false);
                        $templateRow.find('.select2').remove();
                        $templateRow.find('td').removeAttr('style');
                        $templateRow.removeAttr('style');

                        // Clear table
                        $('#workflowTable tbody').empty();

                        $.each(breakUpList, function(index, value) {

                            var $newRow = $templateRow.clone(false);

                            // Clear inputs
                            $newRow.find('input').val('');
                            $newRow.find('select').val('');

                            // breakup_id
                            $newRow.find('input[name="breakup_id[]"]').val(value.breakup_id || '');

                            // Append FIRST
                            $('#workflowTable tbody').append($newRow);

                            //  Init select2 AFTER append
                            if ($.fn.select2) {
                                $newRow.find('.item').select2({
                                    placeholder: "Select Item",
                                    width: "100%"
                                });
                            }

                            // Set item value AFTER select2 init
                            if (value.GeneralItemId) {
                                $newRow.find('.item').val(value.GeneralItemId).trigger('change');
                                getItemDetails(value.GeneralItemId, $newRow);
                            }
                        });
                    }
                });
            }






            // Delete generalItem
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this generalItem?')) return;
                var generalItem_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/ajax-po-general-stores.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        price_db_id: generalItem_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        console.log(res);
                        if (res.status === 'success') {
                            alert(res.Msg);
                            location.reload();
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    },
                    error: function() {
                        alert('AJAX request failed!');
                    }
                });
            });

            loadPOList();

            // Calculate Base Value and Invoice Amount on Requested Qty change
            $(document).on('input', '.requested_qty', function() {
                const row = $(this).closest('tr');
                const qty = parseFloat($(this).val());
                const price = parseFloat(row.find('.item_price').val());
                const gstPercent = parseFloat(row.find('.gst').val());

                const baseValue = qty * price;
                const gstAmount = (baseValue * gstPercent / 100);
                const invoiceAmt = baseValue + gstAmount;

                row.find('.tax_amt').val(gstAmount.toFixed(2));
                row.find('.total_value').val(baseValue.toFixed(2));
                row.find('.invoice_amt').val(invoiceAmt.toFixed(2));
            });

            $(document).on('input', '.discount_perc', function() {
                const row = $(this).closest('tr');
                const discount_perc = parseFloat($(this).val());
                const totalValue = parseFloat(row.find('.total_value').val());
                const invValue = parseFloat(row.find('.invoice_amt').val());
                const gst = parseFloat(row.find('.gst').val());

                const discAmt = (totalValue * discount_perc / 100);
                const totalAmt = totalValue - discAmt;
                const gstAmount = totalAmt * (gst / 100);
                const invoiceAmt = totalAmt + gstAmount;

                row.find('.total_value').val(totalAmt.toFixed(2));
                row.find('.discount').val(discAmt.toFixed(2));
                row.find('.tax_amt').val(gstAmount.toFixed(2));
                row.find('.invoice_amt').val(invoiceAmt.toFixed(2));
            });

            // Auto-fill data when item is selected
            $(document).on('change', '.item', function() {
                const itemId = $(this).val();
                console.log("itemId", itemId);
                const row = $(this).closest('tr');
                getItemDetails(itemId, row);
            });

            function getAvailableDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/Ajax-indent.php',
                    type: 'POST',
                    dataType: 'json', // ✅ ensures res is automatically parsed
                    data: {
                        item_id: itemId,
                        methodType: 'availableData'
                    },
                    success: function(res) {
                        if (res && res.Data) {
                            const data = res.Data;

                            // ✅ fill values safely
                            row.find('.avail_qty').val(data?.AvailableQuantity ? data.AvailableQuantity : 0);
                        } else {
                            // reset row if no data
                            row.find('.avail_qty').val(0);
                        }
                    },
                    error: function(xhr, status, err) {
                        console.error('Error:', err);
                    }
                });
            }

            function getItemDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/Ajax-indent.php',
                    type: 'POST',
                    dataType: 'json', // ✅ ensures res is automatically parsed
                    data: {
                        item_id: itemId,
                        effective_date: $("#pdate").val(),
                        methodType: 'itemDetails'
                    },
                    success: function(res) {
                        if (res && res.Data) {
                            const data = res.Data;

                            row.find('.uom').val(data.Units || '');
                            row.find('.item_price').val(data.UnitPrice || '');
                            row.find('.gst').val(data.Gst || '');
                            row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                        } else {
                            // reset row if no data
                            row.find('input').val('');
                            row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                        }
                        getAvailableDetails(itemId, row);
                    },
                    error: function(xhr, status, err) {
                        console.error('Error:', err);
                    }
                });
            }

            // Save PO via AJAX

            $('#btnSavePO').click(function(e) {
                e.preventDefault();

                var formData = new FormData($('#poForm')[0]);
                var poId = $('#po_id').val(); // Hidden field in form

                // Remove old breakup arrays from FormData
                ['breakup_id[]', 'item[]', 'requested_qty[]', 'pur_req[]', 'remarks[]', 'disp_date[]', 'discount_perc[]'].forEach(name => formData.delete(name));

                // collect breakup rows and append to FormData
                $('#workflowTable tbody tr').each(function() {
                    const $row = $(this);
                    const itemId = $row.find('.item').val() || '';
                    if (!itemId) return;

                    formData.append('breakup_id[]', $row.find('.breakup_id').val() || '');
                    formData.append('item[]', itemId);
                    formData.append('requested_qty[]', $row.find('.requested_qty').val() || 0);
                    formData.append('pur_req[]', $row.find('.pur_req').val() || '');
                    formData.append('remarks[]', $row.find('.remarks').val() || '');
                    formData.append('disp_date[]', $row.find('.disp_date').val() || '');
                    formData.append('discount_perc[]', $row.find('.discount_perc').val() || 0);
                });

                // Set methodType
                formData.set('methodType', poId ? 'update' : 'save');
                if (poId) formData.set('po_id', poId);

                $.ajax({
                    url: 'Ajax/ajax-po-general-stores.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.Msg);
                            window.location.reload();
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    },
                    error: function() {
                        alert('AJAX request failed!');
                    }
                });
            });

            // ======== Edit handler ========
            $(document).on('click', '.btnEdit', function() {
                const poId = $(this).data('id');
                if (!poId) return;

                $('#btnSavePO').text('Update');
                $('#po_id').val(poId); // Correct hidden field

                $.ajax({
                    url: 'Ajax/ajax-po-general-stores.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'view',
                        po_id: poId
                    },
                    success: function(res) {
                        if (!(res && res.status === 'success' && res.Data)) {
                            alert(res && res.Msg ? res.Msg : 'Unable to load PO details.');
                            return;
                        }

                        const h = res.Data;

                        // --- Populate header fields ---
                        const headerMap = (obj, keys) => keys.find(k => obj[k] !== undefined && obj[k] !== null) ? obj[keys.find(k => obj[k] !== undefined && obj[k] !== null)] : '';

                        $('#doc_no').val(headerMap(h, ['DocNo', 'doc_no'])).trigger('change.select2');
                        $('#code').val(headerMap(h, ['Code', 'code']));
                        $('#pdate').val(headerMap(h, ['Date', 'PDate', 'date']));
                        $('#vendor_id').val(headerMap(h, ['Supplier', 'VendorId'])).trigger('change.select2');
                        $('#payment_terms').val(headerMap(h, ['payment_terms', 'PaymentTerms']));
                        $('#freight_type').val(headerMap(h, ['freight_type', 'FreightType']));
                        $('#sub_delivery').val(headerMap(h, ['sub_delivery', 'SubDeliveryDate']));
                        $('#narration').val(headerMap(h, ['Narration', 'narration']));
                        $('#status').val(headerMap(h, ['Status', 'status']));

                        // --- Populate breakup table ---
                        const breakupList = headerMap(h, ['breakUp', 'breakup', 'BreakUp', 'break_up']) || [];
                        const $templateRow = $('#workflowTable tbody tr:first').clone();
                        $('#workflowTable tbody').html('');

                        if (!breakupList.length) {
                            const $empty = $templateRow.clone();
                            $empty.find('input').not('.breakup_id').val('');
                            $empty.find('select').val('').trigger('change');
                            $('#workflowTable tbody').append($empty);
                            return;
                        }

                        breakupList.forEach(item => {
                            const $row = $templateRow.clone();
                            $row.find('input').not('.breakup_id').val('');
                            $row.find('select').val('').trigger('change');

                            // IDs
                            const bid = item.BreakUpId;
                            // set hidden input by name so FormData will include it
                            $row.find('input[name="breakup_id[]"]').val(bid);

                            // Item
                            const genItemId = item.GeneralItemId;
                            if ($.fn.select2 && genItemId) {
                                $row.find('.item').select2({
                                    placeholder: 'Select Item',
                                    width: '100%'
                                }).val(genItemId).trigger('change');
                            }

                            // Fill other fields
                            // $row.find('.item').val(genItemId).trigger('change');
                            $row.find('.uom').val(item.Unit);
                            $row.find('.avail_qty').val(item.AvailableQty);
                            $row.find('.item_price').val(item.UnitPrice);
                            $row.find('.requested_qty').val(item.RequestedQty);
                            $row.find('.discount_perc').val(item.DiscountPerc);
                            $row.find('.disp_date').val(item.DispDate);
                            $row.find('.remarks').val(item.Remarks);

                            $('#workflowTable tbody').append($row);
                        });
                    },
                    error: function() {
                        alert('Failed to load PO for edit.');
                    }
                });
            });



        });
    </script>



    <script>
        // Auto-update Total Value based on Quantity
        $(document).on('input', '.quantity, .discountPercent', function() {
            const row = $(this).closest('tr');
            const qty = parseFloat(row.find('.quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('td:nth-child(4)').text()) || 0;
            const discountPercent = parseFloat(row.find('.discountPercent').val()) || 0;
            const amount = qty * unitPrice;
            const discount = amount * discountPercent / 100;
            const gstPercent = parseFloat(row.find('td:nth-child(10)').text()) || 0;
            const taxAmount = (amount - discount) * gstPercent / 100;

            row.find('.totalValue').text(amount.toFixed(2));
            row.find('.discountValue').text(discount.toFixed(2));
            row.find('.taxAmount').text(taxAmount.toFixed(2));
            row.find('.invoiceAmount').text((amount - discount + taxAmount).toFixed(2));
        });

        $(document).on('click', '.btnView', function() {
            const poId = $(this).data('id');

            $.ajax({
                url: 'Ajax/ajax-po-general-stores.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    methodType: 'view',
                    po_id: poId
                },
                success: function(res) {
                    if (res.status === 'success' && res.Data) {
                        const header = res.Data;
                        const items = res.Data.breakUp || [];

                        let headerHTML = `
                    <div class="container-fluid mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-3"><strong>Doc No:</strong> ${header.DocNo || '-'}</div>
                            <div class="col-md-4 mb-3"><strong>Code:</strong> ${header.Code || '-'}</div>
                            <div class="col-md-4 mb-3"><strong>Date:</strong> ${
                                header.Date
                                    ? (function(d) {
                                        const dateObj = new Date(d);
                                        if (isNaN(dateObj)) return d;
                                        const day = String(dateObj.getDate()).padStart(2, '0');
                                        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                                        const year = dateObj.getFullYear();
                                        return `${day}-${month}-${year}`;
                                    })(header.Date)
                                    : '-'
                            }</div>
                            <div class="col-md-4 mb-3"><strong>Supplier:</strong> ${header.Supplier || '-'}</div>
                            <div class="col-md-4 mb-3"><strong>Payment Terms:</strong> ${header.payment_terms || '-'}</div>
                            <div class="col-md-4 mb-3"><strong>Freight Type:</strong> ${header.freight_type || '-'}</div>
                            <div class="col-md-4 mb-3"><strong>Sub Delivery Date:</strong> ${
                                header.sub_delivery
                                    ? (function(d) {
                                        const dateObj = new Date(d);
                                        if (isNaN(dateObj)) return d;
                                        const day = String(dateObj.getDate()).padStart(2, '0');
                                        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                                        const year = dateObj.getFullYear();
                                        return `${day}-${month}-${year}`;
                                    })(header.sub_delivery)
                                    : '-'
                            }</div>
                            <div class="col-md-12 mb-3"><strong>Narration:</strong> ${header.Narration || '-'}</div>
                            <div class="col-md-4 mb-3"><strong>Status:</strong> ${header.Status || '-'}
                        </div>
                    </div>
                `;

                        // --- ITEM DETAILS TABLE ---
                        let tableHTML = `
                    <h5 class="mb-3">Item Details</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Unit</th>
                                    <th>Available Qty</th>
                                    <th>Unit Price</th>
                                    <th>Requested Qty</th>
                                    <th>Discount %</th>
                                    <th>GST %</th>
                                    <th>Total Value</th>
                                    <th>Tax Amt</th>
                                    <th>Invoice Amt</th>
                                    <th>Disp Date</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${
                                    items.length > 0
                                        ? items.map(item => `
                                            <tr>
                                                <td>${item.ItemName || '-'}</td>
                                                <td>${item.Unit || '-'}</td>
                                                <td>${item.AvailableQty || 0}</td>
                                                <td>${item.UnitPrice || 0}</td>
                                                <td>${item.RequestedQty || 0}</td>
                                                <td>${item.DiscountPerc || 0}</td>
                                                <td>${item.Gst || 0}</td>
                                                <td>${item.TotalValue || 0}</td>
                                                <td>${item.TaxAmt || 0}</td>
                                                <td>${item.InvoiceAmt || 0}</td>
                                                <td>${item.DispDate || '-'}</td>
                                                <td>${item.Remarks || '-'}</td>
                                            </tr>
                                        `).join('')
                                        : `<tr><td colspan="12" class="text-center text-muted">No items found</td></tr>`
                                }
                            </tbody>
                        </table>
                    </div>
                `;

                        $('#poDetailsBody').html(headerHTML + tableHTML);
                        $('#poViewModal').modal('show');
                    } else {
                        alert(res.Msg || 'No details found for this PO.');
                    }
                },
                error: function() {
                    alert('Error loading PO details.');
                }
            });
        });
    </script>

</body>

</html>