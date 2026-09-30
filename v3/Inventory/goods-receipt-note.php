<?php
$Status = $_SESSION['Status'];
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_INVENTORY_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resGeneralItems = $resDropdownsAPI->Data->GeneralItems;
$resVendors = $resDropdownsAPI->Data->Vendors;

$PageTitle = "Goods Receipt Note(General Stores)";


$jsonData = '{
    "scissors": [{"date":"10/10/2025","qty":10,"price":"35.00"},{"date":"10/09/2025","qty":15,"price":"33.00"}],
    "gloves": [{"date":"8/10/2025","qty":10,"price":"99.00"},{"date":"8/09/2025","qty":15,"price":"80.00"}],
    "screwdriver": [{"date":"5/10/2025","qty":10,"price":"20.00"},{"date":"5/09/2025","qty":15,"price":"18.00"}]
}';

$previousData = json_decode($jsonData, true);

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


    <style>
        #itemTable {
            table-layout: fixed;
            min-width: 2240px;
            /* wide enough for all columns */
            width: 100%;
            border-collapse: collapse;
        }

        #itemTable th,
        #itemTable td {
            text-align: center;
            vertical-align: middle;
            padding: 5px;
            border: 1px solid #ddd;
            white-space: nowrap;
            box-sizing: border-box;
            /* ensures padding stays inside cell */
        }


        Adjust column widths #itemTable th:nth-child(1),
        #itemTable td:nth-child(1) {
            width: 80px;
            /* Item column wider */
        }

        #itemTable th:nth-child(2),
        #itemTable td:nth-child(2) {
            width: 80px;
            /* UoM */
        }

        #itemTable th:nth-child(3),
        #itemTable td:nth-child(3) {
            width: 140px;
            /* Available Qty */
        }

        #itemTable th:nth-child(4),
        #itemTable td:nth-child(4) {
            width: 140px;
            /* PO Qty */
        }

        #itemTable th:nth-child(5),
        #itemTable td:nth-child(5) {
            width: 140px;
            /* Received Qty */
        }

        #itemTable th:nth-child(6),
        #itemTable td:nth-child(6) {
            width: 140px;
            /* Deviation Qty */
        }

        #itemTable th:nth-child(7),
        #itemTable td:nth-child(7) {
            width: 80px;
            /* Location */
        }

        #itemTable th:nth-child(8),
        #itemTable td:nth-child(8) {
            width: 80px;
            /* Batch */
        }

        #itemTable th:nth-child(9),
        #itemTable td:nth-child(9) {
            width: 140px;
            /* Item Price */
        }

        #itemTable th:nth-child(10),
        #itemTable td:nth-child(10) {
            width: 140px;
            /* Base Value */
        }

        #itemTable th:nth-child(11),
        #itemTable td:nth-child(11) {
            width: 140px;
            /* Deviation Amount */
        }

        #itemTable th:nth-child(12),
        #itemTable td:nth-child(12) {
            width: 120px;
            /* GST% */
        }

        #itemTable th:nth-child(13),
        #itemTable td:nth-child(13) {
            width: 140px;
            /* Tax Amount */
        }

        #itemTable th:nth-child(14),
        #itemTable td:nth-child(14) {
            width: 140px;
            /* Invoice Amount */
        }

        #itemTable th:nth-child(15),
        #itemTable td:nth-child(15) {
            width: 120px;
            /* Purpose - reduced */
        }

        #itemTable th:nth-child(16),
        #itemTable td:nth-child(16) {
            width: 120px;
            /* Attachment - reduced */
        }

        #itemTable th:nth-child(17),
        #itemTable td:nth-child(17) {
            width: 120px;
            /* Remarks */
        }

        #itemTable th:nth-child(18),
        #itemTable td:nth-child(18) {
            width: 80px;
            /* Action button */
        }

        .select-status-container {
            max-width: 440px !important;
            /* left: -12px !important;
            bottom: 38px !important; */
        }

        .status {
            margin-top: 33px !important;
            margin-left: -236px !important;
            max-width: 470px !important;
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

                    <!-- ================= INDENT FORM ================= -->
                    <form id="indentForm" enctype="multipart/form-data">
                        <input type="hidden" id="price_db_id" name="price_db_id" value="">
                        <input type="hidden" name="old_received_qty[]" class="old_received_qty">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Doc No</label>
                                                <select class="form-control" id="doc_no" name="doc_no"></select>
                                            </div>


                                            <div class="col-md-4">
                                                <label>Code</label>
                                                <input type="text" class="form-control" id="code" name="code" placeholder="Enter Code">
                                            </div>

                                            <div class="col-md-4">
                                                <label>Date</label>
                                                <input type="date" class="form-control" id="pdate" name="pdate" value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        <div class="row mt-2">

                                            <div class="col-md-4">
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



                                            <div class="col-md-4">
                                                <label>Invoice Number</label>
                                                <input type="text" class="form-control" id="invoice_no" name="invoice_no" placeholder="Enter Invoice No">
                                            </div>

                                            <div class="col-md-4">
                                                <label>Invoice Date</label>
                                                <input type="date" class="form-control" id="invoice_date" name="invoice_date">
                                            </div>
                                        </div>

                                        <div class="row mt-2">

                                            <div class="col-md-4">
                                                <label>PO No.</label>
                                                <input type="text" class="form-control" id="po_no" name="po_no" placeholder="Enter PO No">
                                            </div>



                                            <div class="col-md-4">
                                                <label>Freight Type</label>
                                                <select id="freight" name="freight" class="form-control">
                                                    <option value="">Select Freight</option>
                                                    <option value="inclusive">Inclusive</option>
                                                    <option value="exclusive">Exclusive</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label>Narration</label>
                                                <input type="text" class="form-control" id="narration" name="narration" placeholder="Enter Narration">
                                            </div>
                                        </div>

                                        <div class="row mt-2 mb-3">
                                            <div class="col-md-4">
                                                <label>Status</label>
                                                <input type="text" class="form-control status" id="status" name="status" value="Approve" readonly>
                                            </div>
                                        </div>



                                        <hr class="mt-5">

                                        <!-- Item Details -->
                                        <h4 class="mt-4 mb-2">Work Flow</h4>
                                        <div style="overflow-x:auto; width:100%;">
                                            <table class="table table-bordered" id="itemTable">
                                                <thead>
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>UoM</th>
                                                        <th>Available Qty</th>
                                                        <th>PO Qty</th>
                                                        <th>Received Qty</th>
                                                        <th>Deviation Qty</th>
                                                        <th>Location</th>
                                                        <th>Batch</th>
                                                        <th>Item Price</th>
                                                        <th>Base Value</th>
                                                        <th>Deviation Amount</th>
                                                        <th>GST%</th>
                                                        <th>GST Tax Amount</th>
                                                        <th>Invoice Amount</th>
                                                        <th>Purpose</th>
                                                        <th>Attachment</th>
                                                        <th>Remarks</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" class="breakup_id" name="breakup_id[]">
                                                            <select class="form-control itemSelect" name="item[]">
                                                                <option value="">Select Item</option>
                                                                <?php foreach ($resGeneralItems as $item): ?>
                                                                    <option value="<?php echo $item->GeneralItemId ?>">
                                                                        <?php echo $item->name ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        <td><input type="text" class="form-control uom" name="uom[]" readonly></td>
                                                        <td><input type="number" class="form-control avail_qty" name="avail_qty[]" readonly></td>
                                                        <td><input type="number" class="form-control po_qty" name="po_qty[]" readonly></td>
                                                        <td><input type="number" class="form-control received_qty" name="received_qty[]" value="0.00" min="0"></td>
                                                        <td><input type="number" class="form-control deviation_qty" name="deviation_qty[]" value="0.00" readonly></td>
                                                        <td><input type="text" class="form-control location" name="location[]" value="" readonly></td>
                                                        <td><input type="text" class="form-control Batch" name="Batch[]" value="A" readonly></td>
                                                        <td><input type="number" class="form-control item_price" name="item_price[]" readonly></td>
                                                        <td><input type="number" class="form-control base_value" name="base_value[]" value="0.00" readonly></td>
                                                        <td><input type="number" class="form-control deviation_amt" name="deviation_amt[]" value="0.00" readonly></td>
                                                        <td><input type="number" class="form-control gst" name="gst[]" readonly></td>
                                                        <td><input type="number" class="form-control tax_amount" name="tax_amount[]" readonly></td>
                                                        <td><input type="number" class="form-control invoice_amt" name="invoice_amt[]" value="0.00" readonly></td>
                                                        <td><input type="text" class="form-control purpose" name="purpose[]"></td>
                                                        <!-- <td><input type="file" class="form-control attachment" name="attachment[]"></td> -->
                                                        <td>
                                                            <label class="btn btn-light btn-sm mb-0" style="cursor:pointer;">
                                                                <i class="fa fa-paperclip"></i>
                                                                <input type="file" class="attachment" name="attachment[]" style="display:none;">
                                                            </label>
                                                            <span class="file-name" style="margin-left:5px;"></span>
                                                        </td>

                                                        <td><input type="text" class="form-control remarks" name="remarks[]"></td>
                                                        <td><button type="button" class="btn btn-success btnAddRow"><i class="fa fa-plus"></i></button></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Container where item data will appear -->
                                        <!-- <div id="itemDataList" style="margin-top:10px;"></div> -->

                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveIndent" class="btn btn-primary">Save</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Existing Records -->
                    <div id="indentListDiv" class="mt-4"></div>

                </div>
                <?php include($IncludesPath . "footer.php"); ?>
            </div>
        </div>

    </div>



    <div class="modal fade" id="grnViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">GRN Details</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>
                <div class="modal-body" id="grnDetailsBody">
                    <!-- GRN details will be dynamically loaded here -->
                </div>
                <!-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div> -->
            </div>
        </div>
    </div>



    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

    <script>
        $(document).ready(function() {

            getIndents();

            function fnShowDataTable(searchKey = '', loadAll = false) {
                if ($.fn.DataTable.isDataTable("#tblDataTable")) {
                    $("#tblDataTable").DataTable().destroy();
                }

                var table = $('#tblDataTable').DataTable({
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
                        title: 'Brand List',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // Add Load All & More Search buttons
                setTimeout(() => {
                    if (!$("#btnMoreSearch").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                            .insertAfter("#tblDataTable_filter label");
                    }

                    if (!$("#btnLoadAll").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                            .insertAfter("#btnMoreSearch");
                    }
                }, 200);
            }



            // Mock item data (replace with AJAX fetch if needed)
            const itemData = {
                "Gloves": {
                    uom: "Units",
                    avail_qty: 100,
                    price: 50,
                    gst: 18
                },
                "Scissors": {
                    uom: "Units",
                    avail_qty: 50,
                    price: 120,
                    gst: 12
                },
                "Screwdriver": {
                    uom: "Units",
                    avail_qty: 30,
                    price: 200,
                    gst: 18
                }
            };

            function getIndents() {
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note.php',
                    type: 'POST',
                    data: {
                        methodType: 'indents'
                    },
                    success: function(res) {
                        var list = "<option value=''>Select</option>";
                        if (res && res.Data) {
                            $.each(res.Data, function(index, value) {
                                list += "<option value=" + value.PoId + " data-supplier='" + value.SupplierId + "' data-indent='" + value.Id + "'>" + value.DocNo + "</option>";
                            });
                        }

                        $('#doc_no').html(list).trigger('change.select2');
                    }
                });
            }




            $("#doc_no").on("change", function() {
                var supplier = $(this).find(':selected').data('supplier');
                $('#vendor_id').val(supplier).trigger('change');
                getIndentsBreakUp();
            });

            function getIndentsBreakUp() {
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note.php',
                    type: 'POST',
                    data: {
                        indent_id: $("#doc_no").val(),
                        methodType: 'indentsBreakUp'
                    },
                    success: function(res) {
                        if (res && res.Data && res.Data.length > 0) {
                            var breakUpList = res.Data;
                            console.log("break", breakUpList);

                            // Save the first row as template before clearing
                            // var $templateRow = $('#itemTable tbody tr:first').clone();


                            var $templateRow = $('#itemTable tbody tr:first').clone();

                            //  REMOVE select2 before cloning
                            $templateRow.find('.itemSelect').each(function() {
                                if ($(this).hasClass('select2-hidden-accessible')) {
                                    $(this).select2('destroy');
                                }
                            });
                            $templateRow.find('.select2-container').remove();


                            // Remove all rows
                            $('#itemTable tbody').html('');

                            $.each(breakUpList, function(index, value) {
                                var $newRow = $templateRow.clone();

                                // Clear inputs/selects
                                $newRow.find('input').val('');
                                $newRow.find('select').val('').trigger('change');

                                // Set breakup_id
                                if (value.breakup_id != null && value.breakup_id != undefined) {
                                    $newRow.find('input[name="breakup_id[]"]').val(value.breakup_id);
                                } else {
                                    $newRow.find('input[name="breakup_id[]"]').val('');
                                }

                                // Set item dropdown
                                if (value.GeneralItemId != null && value.GeneralItemId != undefined) {
                                    $newRow.find('.itemSelect').val(value.GeneralItemId).trigger('change');
                                    getItemDetails(value.GeneralItemId, $newRow);
                                }

                                // If needed, fill other fields (uncomment as needed)
                                // $newRow.find('input[name="received_qty[]"]').val(value.received_qty || '');
                                // $newRow.find('input[name="location[]"]').val(value.location || '');
                                // $newRow.find('input[name="purpose[]"]').val(value.purpose || '');
                                // $newRow.find('input[name="remark[]"]').val(value.remark || '');

                                // Append new row
                                $('#itemTable tbody').append($newRow);

                                // Reinitialize select2 safely (only if loaded)
                                if (typeof $.fn.select2 === "function") {
                                    $newRow.find('.itemSelect').select2({
                                        placeholder: "Select Item",
                                        width: "100%"
                                    });
                                }
                            });
                        }
                    }
                });
            }

            // Add new row
            $(document).on('click', '.btnAddRow', function() {
                var newRow = $('#itemTable tbody tr:first').clone();
                newRow.find('input').val('');
                newRow.find('.po_qty, .base_value, .invoice_amt,.received_qty,.deviation_qty,.deviation_amt').val('0.00');
                newRow.find('td:last').html('<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>');
                $('#itemTable tbody').append(newRow);
            });

            // Remove row
            $(document).on('click', '.btnRemoveRow', function() {
                $(this).closest('tr').remove();
            });

            // Calculate Base Value and Invoice Amount on Requested Qty change
            $(document).on('input', '.received_qty', function() {
                const row = $(this).closest('tr');
                const qty = parseFloat($(this).val());
                const price = parseFloat(row.find('.item_price').val());
                const gstPercent = parseFloat(row.find('.gst').val());
                const po_qty = parseFloat(row.find('.po_qty').val());

                var deviation = qty - po_qty;
                var deviation_amt = deviation * price;

                const baseValue = (qty * price) - deviation_amt;
                const tax_amount = ((baseValue + deviation_amt) * gstPercent / 100);
                const invoiceAmt = (baseValue + deviation_amt) + tax_amount;

                row.find('.deviation_qty').val(deviation);
                row.find('.deviation_amt').val(deviation_amt.toFixed(2));
                row.find('.base_value').val(baseValue.toFixed(2));
                row.find('.tax_amount').val(tax_amount.toFixed(2));
                row.find('.invoice_amt').val(invoiceAmt.toFixed(2));
            });

            // Auto-fill data when item is selected
            $(document).on('change', '.itemSelect', function() {
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


            // function getItemDetails(itemId, row) {
            //     $.ajax({
            //         url: 'Ajax/ajax-goods-receipt-note.php',
            //         type: 'POST',
            //         dataType: 'json', // ✅ ensures res is automatically parsed
            //         data: {
            //             item_id: itemId,
            //             effective_date: $("#pdate").val(),
            //             methodType: 'itemDetails'
            //         },
            //         success: function(res) {
            //             if (res && res.Data) {
            //                 const data = res.Data;

            //                 // ✅ fill values safely
            //                 row.find('.uom').val(data.Units);
            //                 row.find('.item_price').val(data.UnitPrice);
            //                 row.find('.gst').val(data.Gst);
            //                 row.find('.received_qty, .base_value, .invoice_amt').val('0.00');
            //             } else {
            //                 // reset row if no data
            //                 row.find('input').val('');
            //                 row.find('.received_qty, .base_value, .invoice_amt').val('0.00');
            //             }
            //             getPoDetails(itemId, row);
            //             getAvailableDetails(itemId, row);
            //         },
            //         error: function(xhr, status, err) {
            //             console.error('Error:', err);
            //         }
            //     });
            // }

            function getItemDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        item_id: itemId,
                        effective_date: $("#pdate").val(),
                        methodType: 'itemDetails'
                    },
                    success: function(res) {
                        if (res && res.Data) {
                            const data = res.Data;

                            // ✅ Only fill item-related fields, DO NOT reset received_qty
                            row.find('.uom').val(data.Units);
                            row.find('.item_price').val(data.UnitPrice);
                            row.find('.gst').val(data.Gst);
                        } else {
                            // reset item-related fields if no data
                            row.find('.uom, .item_price, .gst').val('');
                        }

                        // Fill PO qty and available qty as needed
                        getPoDetails(itemId, row);
                        getAvailableDetails(itemId, row);
                    },
                    error: function(xhr, status, err) {
                        console.error('Error fetching item details:', err);
                    }
                });
            }


            function getPoDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note.php',
                    type: 'POST',
                    dataType: 'json', // ✅ ensures res is automatically parsed
                    data: {
                        po_id: $("#doc_no").val(),
                        itemId: itemId,
                        methodType: 'poData'
                    },
                    success: function(res) {
                        if (res && res.Data) {
                            const data = res.Data;

                            // ✅ fill values safely
                            row.find('.po_qty').val(data?.Quantity ? data.Quantity : 0);
                        } else {
                            // reset row if no data
                            row.find('.po_qty').val(0);
                        }
                    },
                    error: function(xhr, status, err) {
                        console.error('Error:', err);
                    }
                });
            }


            // Show selected file name
            $(document).on('change', '.attachment', function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).closest('td').find('.file-name').text(fileName);
            });


            // Calculate Base Value and Invoice Amount on Requested Qty change
            $(document).on('input', '.po_qty', function() {
                const row = $(this).closest('tr');
                const qty = parseFloat($(this).val());
                const price = parseFloat(row.find('.item_price').val());
                const gstPercent = parseFloat(row.find('.gst').val());

                const baseValue = qty * price;
                const invoiceAmt = baseValue + (baseValue * gstPercent / 100);

                row.find('.base_value').val(baseValue.toFixed(2));
                row.find('.invoice_amt').val(invoiceAmt.toFixed(2));
            });

            $('#btnSaveIndent').click(function(e) {
                e.preventDefault();

                var formData = new FormData($('#indentForm')[0]);

                // Attach note_id for updates
                formData.append('note_id', $('#price_db_id').val() || 0);

                // Attach old received qty for existing rows
                $('#itemTable tbody tr').each(function(i, row) {
                    var oldQty = $(row).find('.old_received_qty').val() || 0;
                    formData.append('old_received_qty[]', oldQty);
                });

                formData.append('methodType', 'save');

                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.Msg);

                            // 1️⃣ Reload the GRN list immediately
                            loadIndents();

                            // 2️⃣ Reset the form for a new entry
                            $('#indentForm')[0].reset();

                            // 3️⃣ Reset the item table (keep only first template row)
                            $('#itemTable tbody tr:gt(0)').remove();

                            // Reset all input values in the first row
                            $('#itemTable tbody tr:first').find('input, select').val('').trigger('change');

                            // Reset select2 fields
                            $('#doc_no').val('').trigger('change.select2');
                            $('#vendor_id').val('').trigger('change');

                            // Reset Save button text to "Save"
                            $('#btnSaveIndent').text('Save');

                        } else {
                            alert(res.Msg || 'Error saving/updating GRN.');
                        }
                    },
                    error: function() {
                        alert('AJAX request failed.');
                    }
                });
            });


            // MORE SEARCH (use current search box value)
            $(document).on("click", "#btnMoreSearch", function() {
                const searchKey = $("#tblDataTable_filter input").val();
                loadIndents(searchKey, false);
            });

            // LOAD ALL (ignore search, fetch all records)
            $(document).on("click", "#btnLoadAll", function() {
                loadIndents("", true);
            });



            function loadIndents(searchKey = '', loadAll = false) {
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note.php',
                    type: 'POST',
                    data: {
                        methodType: 'load',
                        searchKey: searchKey,
                        loadAll: loadAll
                    },
                    success: function(html) {
                        $('#indentListDiv').html(html);
                        fnShowDataTable(searchKey, loadAll); // Initialize DataTable after load
                    }
                });
            }


            $(document).on('click', '.btnEdit', function() {
                const grnId = $(this).data('id');
                $('#btnSaveIndent').text('Update');

                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note.php',
                    type: 'POST',
                    data: {
                        methodType: 'view',
                        GoodsReceiptNoteId: grnId
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.Data) {
                            const h = res.Data;

                            // Set note_id for update
                            $('#price_db_id').val(h.Id || grnId);

                            // Populate header fields (temporarily store PoId and SupplierId)
                            const poId = h.PoId;
                            const supplierId = h.SupplierID;


                            // Clear table and keep template row
                            // const $templateRow = $('#itemTable tbody tr:first').clone();


                            const $templateRow = $('#itemTable tbody tr:first').clone();

                            //  CLEAN select2 before reuse
                            $templateRow.find('.itemSelect').each(function() {
                                if ($(this).hasClass('select2-hidden-accessible')) {
                                    $(this).select2('destroy');
                                }
                            });
                            $templateRow.find('.select2-container').remove();


                            $('#itemTable tbody').html('');

                            // First, reload doc_no dropdown and select correct PO
                            $.ajax({
                                url: 'Ajax/ajax-goods-receipt-note.php',
                                type: 'POST',
                                data: {
                                    methodType: 'indents'
                                },
                                dataType: 'json',
                                success: function(poRes) {
                                    if (poRes && poRes.Data) {
                                        let list = "<option value=''>Select</option>";
                                        $.each(poRes.Data, function(index, value) {
                                            list += `<option value="${value.PoId}" data-supplier="${value.SupplierId}" data-indent="${value.Id}">${value.DocNo}</option>`;
                                        });
                                        $('#doc_no').html(list).trigger('change.select2');

                                        // Now set the selected PO (ensure string to avoid type issues)
                                        $('#doc_no').val(String(poId)).trigger('change.select2');

                                        // Because some select2/DOM timing may interfere, set vendor after a short delay
                                        // Prefer supplierId from record, fallback to data-supplier on selected option
                                        setTimeout(function() {
                                            let supplierToSet = supplierId;
                                            if (!supplierToSet) {
                                                supplierToSet = $('#doc_no').find(':selected').data('supplier') || '';
                                            }

                                            if (supplierToSet !== '' && $('#vendor_id option[value="' + supplierToSet + '"]').length) {
                                                $('#vendor_id').val(String(supplierToSet)).trigger('change');
                                            } else {
                                                // if option not present yet or empty, try to set by matching string
                                                $('#vendor_id').val(String(supplierToSet)).trigger('change');
                                            }
                                        }, 50);

                                        // Populate other header fields
                                        $('#pdate').val(h.Date || '');
                                        $('#code').val(h.Code || '');
                                        $('#invoice_no').val(h.InvoiceNo || '');
                                        $('#invoice_date').val(h.InvoiceDate || '');
                                        $('#narration').val(h.Narration || '');
                                        $('#status').val(h.Status || '');
                                        $('#po_no').val(h.PoNo || '');

                                        // Populate breakup items
                                        if (h.breakUp && h.breakUp.length > 0) {
                                            h.breakUp.forEach(item => {
                                                const $newRow = $templateRow.clone();
                                                $newRow.find('input, select').val('').trigger('change');

                                                // set breakup and old received qty (both name and class)
                                                $newRow.find('input[name="breakup_id[]"]').val(item.BreakUpId || 0);
                                                $newRow.find('input[name="old_received_qty[]"]').val(item.ReceivedQuantity || 0);
                                                $newRow.find('input.old_received_qty').val(item.ReceivedQuantity || 0);

                                                // select item and fetch its details (will fill price, uom, gst and PO/avail qty)
                                                $newRow.find('.itemSelect').val(item.GeneralItemId).trigger('change');
                                                getItemDetails(item.GeneralItemId, $newRow);

                                                // fill row-specific editable values
                                                $newRow.find('.received_qty').val(item.ReceivedQuantity || 0);
                                                $newRow.find('.location').val(item.Location || '');
                                                $newRow.find('.purpose').val(item.Purpose || '');
                                                $newRow.find('.remarks').val(item.Remarks || '');

                                                // append row
                                                $('#itemTable tbody').append($newRow);

                                                if ($.fn.select2) {
                                                    $newRow.find('.itemSelect').select2({
                                                        placeholder: "Select Item",
                                                        width: "100%"
                                                    });
                                                }
                                            });
                                        }
                                    }
                                },
                                error: function() {
                                    alert('Failed to load PO options.');
                                }
                            });
                        } else {
                            alert(res.Msg || 'Error fetching GRN details for edit.');
                        }
                    },
                    error: function() {
                        alert('AJAX request failed while fetching GRN details.');
                    }
                });
            });




            // Delete generalItem
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this generalItem?')) return;
                var generalItem_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note.php',
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


            loadIndents();

        });



        $(document).on('click', '.btnView', function() {
            const grnId = $(this).data('id');

            $.ajax({
                url: 'Ajax/ajax-goods-receipt-note.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    methodType: 'view',
                    GoodsReceiptNoteId: grnId
                },
                success: function(res) {
                    if (res.status === 'success' && res.Data) {
                        const h = res.Data;
                        const items = h.breakUp || [];

                        let headerHTML = `
                    <div class="container-fluid mb-3">
                        <div class="row">
                            <div class="col-md-4"><strong>Doc No:</strong> ${h.DocNo || '-'}</div>
                            <div class="col-md-4"><strong>Date:</strong> ${h.Date ? (new Date(h.Date).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-')) : '-'}</div>
                            <div class="col-md-4"><strong>Supplier Firm Name:</strong> ${h.SupplierName || '-'}</div>
                            <div class="col-md-4"><strong>PO No:</strong> ${h.PoNo || '-'}</div>
                            <div class="col-md-4"><strong>Invoice No:</strong> ${h.InvoiceNo || '-'}</div>
                            <div class="col-md-4"><strong>Code:</strong> ${h.Code || '-'}</div>
                            <div class="col-md-4"><strong>Invoice Date:</strong> ${h.InvoiceDate ? (new Date(h.InvoiceDate).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-')) : '-'}</div>
                            <div class="col-md-12"><strong>Narration:</strong> ${h.Narration || '-'}</div>
                            <div class="col-md-4"><strong>Status:</strong> ${h.Status || '-'}
                        </div>
                    </div>
                `;

                        let tableHTML = `
                    <h5>Item Details</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Received Qty</th>
                                    <th>Location</th>
                                    <th>Purpose</th>
                                    <th>Remarks</th>
                                    <th>Attachment</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${
                                  items.length
                                    ? items.map(i => `
                                        <tr>
                                            <td>${i.ItemName || '-'}</td>
                                            <td>${i.ReceivedQuantity || 0}</td>
                                            <td>${i.Location || '-'}</td>
                                            <td>${i.Purpose || '-'}</td>
                                            <td>${i.Remarks || '-'}</td>
                                            <td>${i.Attachment ? `<a href="${i.Attachment}" target="_blank">View</a>` : '-'}</td>
                                        </tr>`).join('')
                                    : `<tr><td colspan="6" class="text-center text-muted">No items found</td></tr>`
                                }
                            </tbody>
                        </table>
                    </div>
                `;

                        $('#grnDetailsBody').html(headerHTML + tableHTML);
                        $('#grnViewModal').modal('show');
                    } else {
                        alert(res.Msg || 'No details found for this GRN.');
                    }
                },
                error: function() {
                    alert('Error loading GRN details.');
                }
            });
        });
    </script>

</body>

</html>