<?php
$Status = $_SESSION['Status'];
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_INVENTORY_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resGeneralItems = $resDropdownsAPI->Data->GeneralItems;
$resVendors = $resDropdownsAPI->Data->Vendors;
$resVariety = $resDropdownsAPI->Data->Variety;
$resPackingStyle = $resDropdownsAPI->Data->PackingStyle;
$resGrade = $resDropdownsAPI->Data->Grade;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resBrand = $resDropdownsAPI->Data->Brand;

$PageTitle = "Goods Receipt Note (PM)";


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
    <?php include_once ROOT . 'includes/head.php' ?>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .row {
            margin-bottom: 10px;
        }

        #itemTable {
            table-layout: fixed;
            min-width: 3400px;
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
            width: 150px;
            /* Item column wider */
        }

        #itemTable th:nth-child(2),
        #itemTable td:nth-child(2) {
            width: 150px;
            /* UoM */
        }

        #itemTable th:nth-child(3),
        #itemTable td:nth-child(3) {
            width: 260px;
            /* Available Qty */
        }

        #itemTable th:nth-child(4),
        #itemTable td:nth-child(4) {
            width: 150px;
            /* PO Qty */
        }

        #itemTable th:nth-child(5),
        #itemTable td:nth-child(5) {
            width: 150px;
            /* Received Qty */
        }

        #itemTable th:nth-child(6),
        #itemTable td:nth-child(6) {
            width: 150px;
            /* Deviation Qty */
        }

        #itemTable th:nth-child(7),
        #itemTable td:nth-child(7) {
            width: 180px;
            /* Location */
        }

        #itemTable th:nth-child(8),
        #itemTable td:nth-child(8) {
            width: 120px;
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
            width: 120px;
            /* GST% */
        }

        #itemTable th:nth-child(19),
        #itemTable td:nth-child(19) {
            width: 140px;
            /* Tax Amount */
        }

        #itemTable th:nth-child(20),
        #itemTable td:nth-child(20) {
            width: 140px;
            /* Invoice Amount */
        }

        #itemTable th:nth-child(21),
        #itemTable td:nth-child(21) {
            width: 120px;
            /* Purpose - reduced */
        }

        #itemTable th:nth-child(22),
        #itemTable td:nth-child(22) {
            width: 120px;
            /* Attachment - reduced */
        }

        #itemTable th:nth-child(23),
        #itemTable td:nth-child(23) {
            width: 120px;
            /* Remarks */
        }

        #itemTable th:nth-child(24),
        #itemTable td:nth-child(24) {
            width: 80px;
            /* Action button */
        }


        .select-status-container {
            max-width: 460px !important;
            /* left: -12px !important;
            bottom: 38px !important; */
        }

        .status {
            margin-top: 15px !important;
            margin-left: -236px !important;
            max-width: 473px !important;
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
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <div class="col-md-4">
                                                <label>Doc No</label>
                                                <select class="form-control" id="doc_no" name="doc_no">
                                                </select>
                                            </div>



                                            <div class="col-md-4">
                                                <label>Date</label>
                                                <input type="date" class="form-control" id="pdate" name="pdate"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Vendor Account</label>
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
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Freight Type</label>
                                                <select id="freight" name="freight" class="form-control">
                                                    <option value="">Select Freight</option>
                                                    <option value="inclusice">Inclusice</option>
                                                    <option value="exclusive">Exclusive</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Is GST</label>
                                                <select id="isgst" name="isgst" class="form-control">
                                                    <option value="YES">Yes</option>
                                                    <option value="NO" selected>No</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label>Invoice Number</label>
                                                <input type="text" class="form-control" id="invoice_no"
                                                    name="invoice_no" placeholder="Enter Invoice No">
                                            </div>
                                        </div>
                                        <div class="row">

                                            <div class="col-md-4">
                                                <label>Invoice Date</label>
                                                <input type="date" class="form-control" id="invoice_date"
                                                    name="invoice_date" placeholder="Enter Invoice Date">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Invoice Attachment</label>
                                                <input type="File" class="form-control" id="i_attachment"
                                                    name="i_attachment" placeholder="Select File">
                                            </div>

                                            <div class="col-md-4">
                                                <label>Credit Note Type</label>
                                                <select id="creditnote" name="creditnote" class="form-control">
                                                    <option value="YES">With Credit Note</option>
                                                    <option value="NO" selected>Without Credit Note</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">

                                            <div class="col-md-4">
                                                <label>Narration</label>
                                                <input type="text" class="form-control" id="narration" name="narration"
                                                    placeholder="Enter Narration">
                                            </div>

                                            <div class="col-md-4">

                                                <label>Status</label>
                                                <input type="text" class="form-control status" id="status" name="status" value="Approve" readonly>

                                            </div>
                                        </div>
                                        <hr>

                                        <!-- Item Details -->
                                        <h4 class="mt-4 mb-3">Work Flow</h4>
                                        <div style="overflow-x:auto; width:100%;">
                                            <table class="table table-bordered" id="itemTable">
                                                <thead>
                                                    <tr>
                                                        <th>PRO Number</th>
                                                        <th>Type of Order</th>
                                                        <th>Variety <span style="color:red">*</span></th>
                                                        <th>Grade</th>
                                                        <th>Packing Style</th>
                                                        <th>Process Type</th>
                                                        <th>Brand</th>
                                                        <th>Size in mm</th>
                                                        <th>Available Qty</th>
                                                        <th>PPO Qty</th>
                                                        <th>Received Qty</th>
                                                        <th>Deviation Qty</th>
                                                        <th>Location</th>
                                                        <th>Batch</th>
                                                        <th>Item Price</th>
                                                        <th>Base Value</th>
                                                        <th>Deviation Amount</th>
                                                        <th>GST%</th>
                                                        <th>Tax Amount</th>
                                                        <th>Invoice Amount</th>
                                                        <th>Purpose</th>
                                                        <th>Attachment</th>
                                                        <th>Remarks</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td style="display:none;">
                                                            <input type="hidden" class="BreakUpId" name="breakup_id[]" value="">

                                                        </td>
                                                        <!-- <input type="hidden" class="BreakUpId" name="breakup_id[]" value="${b.BreakUpId}"> -->

                                                        <!-- <td><input type="text" class="form-control pro" name="pro[]"> -->
                                                        <td>
                                                            <select class="drpSelect form-control pro" name="pro[]"></select>
                                                        </td>

                                                        <td><input type="text" class="form-control order_type"
                                                                name="order_type[]"></td>
                                                        <td>
                                                            <input type="input" class="form-control txtVariety"
                                                                name="txtVariety[]"
                                                                placeholder="Click to Select Variety" readonly />
                                                            <input type="hidden" name="txtVarietyID[]" class="txtVarietyID">
                                                        </td>
                                                        <td>
                                                            <select class="drpSelect form-control grade" name="grade[]">
                                                                <option value=""> Select</option>
                                                                <?php foreach ($resGrade as $item) { ?>
                                                                    <option value="<?php echo $item->value; ?>">
                                                                        <?php echo $item->Options ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="drpSelect form-control packing_style"
                                                                name="packing_style[]">
                                                                <option value=""> Select</option>
                                                                <?php foreach ($resPackingStyle as $item) { ?>
                                                                    <option value="<?php echo $item->PCKID; ?>">
                                                                        <?php echo $item->PackingStyle ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="drpSelect form-control freezing"
                                                                name="freezing[]">
                                                                <option value=""> Select</option>
                                                                <?php foreach ($resFreezingType as $item) { ?>
                                                                    <option value="<?php echo $item->FreezingTypeName; ?>">
                                                                        <?php echo $item->FreezingTypeName ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="drpSelect form-control brand" name="brand[]">
                                                                <option value=""> Select</option>
                                                                <?php foreach ($resBrand as $item) { ?>
                                                                    <option value="<?php echo $item->BrandID; ?>">
                                                                        <?php echo $item->BrandName ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td><input type="text" class="form-control size" name="size[]">
                                                        </td>
                                                        <td><input type="number" class="form-control avail_qty"
                                                                name="avail_qty[]" readonly></td>
                                                        <td><input type="number" class="form-control po_qty"
                                                                name="po_qty[]" readonly></td>
                                                        <td><input type="number" class="form-control received_qty"
                                                                name="received_qty[]" value="0.00" min="0"></td>
                                                        <td><input type="number" class="form-control deviation_qty"
                                                                name="deviation_qty[]" value="0.00" readonly></td>
                                                        <td><input type="text" class="form-control location"
                                                                name="location[]" value="" readonly></td>
                                                        <td><input type="text" class="form-control Batch" name="Batch[]"
                                                                value="A" readonly></td>
                                                        <td><input type="number" class="form-control item_price"
                                                                name="item_price[]" readonly></td>
                                                        <td><input type="number" class="form-control base_value"
                                                                name="base_value[]" value="0.00" readonly></td>
                                                        <td><input type="number" class="form-control deviation_amt"
                                                                name="deviation_amt[]" value="0.00" readonly></td>
                                                        <td><input type="number" class="form-control gst" name="gst[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control tax_amount"
                                                                name="tax_amount[]" readonly></td>
                                                        <td><input type="number" class="form-control invoice_amt"
                                                                name="invoice_amt[]" value="0.00" readonly></td>
                                                        <td><input type="text" class="form-control purpose"
                                                                name="purpose[]"></td>
                                                        <!-- <td><input type="file" class="form-control attachment" name="attachment[]"></td> -->
                                                        <td>
                                                            <label class="btn btn-light btn-sm mb-0"
                                                                style="cursor:pointer;">
                                                                <i class="fa fa-paperclip"></i>
                                                                <input type="file" class="attachment"
                                                                    name="attachment[]" style="display:none;">
                                                            </label>
                                                            <span class="file-name" style="margin-left:5px;"></span>
                                                        </td>

                                                        <td><input type="text" class="form-control remarks"
                                                                name="remarks[]"></td>
                                                        <td><button type="button" class="btn btn-success btnAddRow"><i
                                                                    class="fa fa-plus"></i></button></td>
                                                    </tr>
                                                </tbody>


                                            </table>
                                        </div>

                                        <!-- Container where item data will appear -->
                                        <!-- <div id="itemDataList" style="margin-top:10px;"></div> -->

                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveIndent"
                                                class="btn btn-primary">Save</button>
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
        <div class="modal fade" id="modalVarietyList" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Variety List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="modalVariety"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>




    <div class="modal fade" id="viewGRNModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Goods Receipt Note - View</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="viewGRNContent"></div>
                </div>

            </div>
        </div>
    </div>


    <script>
        var table;
        $(document).on('keydown', 'input[pattern]', function(e) {
            var input = $(this);
            var oldVal = input.val();
            var regex = new RegExp(input.attr('pattern'), 'g');

            setTimeout(function() {
                var newVal = input.val();
                if (!regex.test(newVal)) {
                    input.val(oldVal);
                }
            }, 1);
        });


        let poptable = null;
        let currentVarietyInput = null;

        // Use delegated handler so dynamically added .txtVariety inputs work
        $(document).on('click', '.txtVariety', function(e) {
            e.preventDefault();
            // Remember which input opened the modal
            currentVarietyInput = $(this);
            getVarieties();
        });
        $(document).on('shown.bs.modal', '#modalVarietyList', function() {
            // If a DataTable instance exists, destroy it and remove table markup to avoid duplicates
            if ($.fn.dataTable && $.fn.dataTable.isDataTable('.popVarietyTable')) {
                try {
                    $('.popVarietyTable').DataTable().destroy();
                } catch (err) {
                    // ignore
                }
                // Optional: clear any previous table html to avoid duplicates
                // $('.popVarietyTable').empty();
            }

            // Initialize datatable (delegated modal content is already injected via AJAX)
            poptable = $('.popVarietyTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                ordering: false
            });

            // if you need column adjustment after rendering
            if (poptable && typeof poptable.columns === 'function') {
                poptable.columns.adjust().draw(false);
            }
        });


        $(".btn-close").on("click", function() {

            $('#modalVarietyList').modal('hide');

        });

        function getVarieties() {
            $.ajax({
                type: 'POST',
                url: 'Ajax/ajax-pm-proforma-invoice.php',
                data: {
                    'methodType': 'varieties',
                },
                success: function(data) {
                    if (data) {
                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            $('#modalVariety').html("").html(data.HtmlRes);
                            $('#modalVarietyList').modal('show');
                        }
                    }
                }
            });
        }

        // Auto-fill data when item is selected
        // $(document).on('change', '.txtVariety', function () {
        //     const itemId = $(this).val();
        //     const row = $(this).closest('tr');
        //     getAvailableDetails(itemId,row);
        //     getItemDetails(itemId, row);
        // });


        // When the modal is hidden, clear the reference to the input (optional)
        $(document).on('hidden.bs.modal', '#modalVarietyList', function() {
            currentVarietyInput = null;
        });

        // Selection handler — delegated, so it works for dynamically created rows inside the modal
        $(document).on("click", ".selectVariety", function(e) {
            e.preventDefault();

            var selectedVariety = $(this).val() || $(this).text();
            let selectedVarietyID = $(this).attr("data-varietyid");

            let grade = $(this).attr("data-grade");
            let freezing = $(this).attr("data-freezing");
            let packingstyle = $(this).attr("data-packingstyle");
            let brand = $(this).attr("data-brand");

            // update only the row where user clicked
            let row = currentVarietyInput.closest("tr");

            row.find(".txtVariety").val(selectedVariety);
            row.find(".txtVarietyID").val(selectedVarietyID);

            row.find(".grade").val(grade);
            row.find(".freezing").val(freezing);
            row.find(".packing_style").val(packingstyle);
            row.find(".brand").val(brand);

            $("#modalVarietyList").modal("hide");
        });

        $(document).ready(function() {

            getIndents();

            function fnShowDataTable() {
                table = $('#tblDataTable').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse: true,
                    dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rt',
                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btnExcelDataTable btn btn-success',
                        title: 'General Items',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }],
                });
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
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
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
                        $('.pro').html(list).trigger('change.select2');
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
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
                    type: 'POST',
                    data: {
                        indent_id: $("#doc_no").val(),
                        methodType: 'indentsBreakUp'
                    },
                    success: function(res) {
                        if (res && res.Data && res.Data.length > 0) {
                            var breakUpList = res.Data;
                            console.log("break", breakUpList);

                            // // Save the first row as template before clearing
                            // var $templateRow = $('#itemTable tbody tr:first').clone();

                            // // Remove all rows
                            // $('#itemTable tbody').html('');

                            // $.each(breakUpList, function (index, value) {
                            //     var $newRow = $templateRow.clone();

                            //     // Clear inputs/selects
                            //     $newRow.find('input').val('');
                            //     $newRow.find('select').val('').trigger('change');

                            //     // Set breakup_id
                            //     if (value.breakup_id != null && value.breakup_id != undefined) {
                            //         $newRow.find('input[name="breakup_id[]"]').val(value.breakup_id);
                            //     } else {
                            //         $newRow.find('input[name="breakup_id[]"]').val('');
                            //     }

                            //     // Set item dropdown
                            //     if (value.GeneralItemId != null && value.GeneralItemId != undefined) {
                            //         $newRow.find('.itemSelect').val(value.GeneralItemId).trigger('change');
                            //         getItemDetails(value.GeneralItemId, $newRow);
                            //     }

                            //     // If needed, fill other fields (uncomment as needed)
                            //     // $newRow.find('input[name="received_qty[]"]').val(value.received_qty || '');
                            //     // $newRow.find('input[name="location[]"]').val(value.location || '');
                            //     // $newRow.find('input[name="purpose[]"]').val(value.purpose || '');
                            //     // $newRow.find('input[name="remark[]"]').val(value.remark || '');

                            //     // Append new row
                            //     $('#itemTable tbody').append($newRow);

                            //     // Reinitialize select2 safely (only if loaded)
                            //     if (typeof $.fn.select2 === "function") {
                            //         $newRow.find('.itemSelect').select2({
                            //             placeholder: "Select Item",
                            //             width: "100%"
                            //         });
                            //     }
                            // });
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


            function getItemDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
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

                            // ✅ fill values safely
                            row.find('.uom').val(data.Units);
                            row.find('.item_price').val(data.UnitPrice);
                            row.find('.gst').val(data.Gst);
                            row.find('.received_qty, .base_value, .invoice_amt').val('0.00');
                        } else {
                            // reset row if no data
                            row.find('input').val('');
                            row.find('.received_qty, .base_value, .invoice_amt').val('0.00');
                        }
                        getPoDetails(itemId, row);
                        getAvailableDetails(itemId, row);
                    },
                    error: function(xhr, status, err) {
                        console.error('Error:', err);
                    }
                });
            }

            function getPoDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
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

            // Save Indent



            $('#btnSaveIndent').click(function() {
                const priceId = $('#price_db_id').val(); // Hidden field
                const methodType = priceId ? 'update' : 'save';
                // Collect form data
                const formData = new FormData($('#indentForm')[0]);
                formData.set('methodType', methodType);
                if (priceId) formData.set('price_db_id', priceId);
                // Collect breakup table rows
                const breakupData = [];
                $('#itemTable tbody tr').each(function() {
                    const $row = $(this);
                    // Only include rows with a selected Variety
                    const varietyID = $row.find('.txtVarietyID').val();
                    if (!varietyID) return;
                    breakupData.push({
                        BreakUpId: $row.find('.BreakUpId').val() || 0,
                        pro: $row.find('.pro').val() || '',
                        order_type: $row.find('.order_type').val() || '',
                        txtVariety: $row.find('.txtVariety').val() || '',
                        txtVarietyID: varietyID,
                        grade: $row.find('.grade').val() || '',
                        packing_style: $row.find('.packing_style').val() || '',
                        freezing: $row.find('.freezing').val() || '',
                        brand: $row.find('.brand').val() || '',
                        size: $row.find('.size').val() || '',
                        received_qty: $row.find('.received_qty').val() || 0,
                        purpose: $row.find('.purpose').val() || ''
                    });
                });
                formData.set('breakup_json', JSON.stringify(breakupData));
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.Msg || (methodType === 'save' ? 'Saved successfully' : 'Updated successfully'));
                            // Reset form & table
                            $('#indentForm')[0].reset();
                            $('#price_db_id').val('');
                            $('#btnSaveIndent').text('Save');
                            // Keep only ONE empty template row
                            const $templateRow = $('#itemTable tbody tr:first').clone();
                            $templateRow.find('input, select').val('');
                            $templateRow.find('.received_qty').val('0.00');
                            $templateRow.find('.BreakUpId').val(0);
                            $('#itemTable tbody').html($templateRow);
                            // Reload your indents list
                            loadIndents && loadIndents();
                        } else {
                            alert('Error: ' + (res.Msg || JSON.stringify(res)));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText || error);
                        alert('AJAX request failed: ' + error);
                    }
                });
            });






            // function loadIndents() {
            //     $.ajax({
            //         url: 'Ajax/ajax-goods-receipt-note-packaging.php',
            //         type: 'POST',
            //         data: {
            //             methodType: 'load'
            //         },
            //         success: function(html) {
            //             $('#indentListDiv').html(html);
            //         }
            //     });
            // }




            function loadIndents() {
                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
                    type: 'POST',
                    data: {
                        methodType: 'load'
                    },
                    success: function(html) {
                        $('#indentListDiv').html(html);

                        // Init DataTable AFTER HTML is injected
                        setTimeout(function() {
                            initGoodsDataTable();
                        }, 100);
                    }
                });
            }


            function initGoodsDataTable() {

                if ($.fn.DataTable.isDataTable("#tblDataTable")) {
                    $("#tblDataTable").DataTable().clear().destroy();
                }

                $('#tblDataTable').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100, -1], // 👈 Load All
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse: true,
                    ordering: false,
                    dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rtip',
                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btn btn-success',
                        title: 'Goods Receipt Note List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // Inject buttons near search box
                setTimeout(function() {

                    if (!$("#btnMoreSearchGRN").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearchGRN">More Search</button>')
                            .insertAfter("#tblDataTable_filter label");
                    }

                    if (!$("#btnLoadAllGRN").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAllGRN">Load All</button>')
                            .insertAfter("#btnMoreSearchGRN");
                    }

                }, 200);
            }
            // MORE SEARCH
            $(document).on("click", "#btnMoreSearchGRN", function() {
                loadIndents();
            });

            // LOAD ALL
            $(document).on("click", "#btnLoadAllGRN", function() {
                loadIndents();
            });










            $(document).on('click', '.btnEdit', function() {

                const priceId = $(this).attr('data-price_db_id');
                if (!priceId) return;

                $('#btnSaveIndent').text('Update');
                $('#price_db_id').val(priceId);

                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'getSingle',
                        price_db_id: priceId
                    },
                    success: function(res) {

                        if (res.status !== 'success' || !res.Data?.length) {
                            alert(res.Msg || 'No data');
                            return;
                        }

                        const header = res.Data[0];

                        /* ===== HEADER ===== */
                        $('#vendor_id').val(header.VendorId).trigger('change.select2');
                        $('#doc_no').val(header.DocNo);
                        $('#invoice_no').val(header.InvoiceNumber);
                        $('#pdate').val(header.Date);
                        $('#invoice_date').val(header.InvoiceDate);
                        $('#narration').val(header.Narration);
                        $('status').val(header.Status);
                        $('#freight').val(header.FreightType);

                        /* ===== BREAKUPS ===== */
                        const $tbody = $('#itemTable tbody');
                        const $templateRow = $tbody.find('tr:first').clone(false);
                        $tbody.empty();

                        if (!header.BreakUps || !header.BreakUps.length) {
                            $tbody.append($templateRow);
                            return;
                        }

                        header.BreakUps.forEach(item => {

                            const $row = $templateRow.clone(false);

                            // CLEAR OLD VALUES
                            $row.find('input:not(.BreakUpId), select, textarea').val('');
                            $row.find('.file-name').text('');

                            // HIDDEN ID
                            $row.find('.BreakUpId').val(item.BreakUpId ? item.BreakUpId : (item.breakup_id ? item.breakup_id : 0));

                            /* ===== SAME AS FIRST CODE STYLE ===== */

                            // NORMAL SELECT SET (NO helper)
                            $row.find('.pro')
                                .val(item.pro)
                                .trigger('change.select2');

                            $row.find('.grade')
                                .val(item.grade)
                                .trigger('change.select2');

                            $row.find('.packing_style')
                                .val(item.packing_style)
                                .trigger('change.select2');

                            $row.find('.freezing')
                                .val(item.freezing)
                                .trigger('change.select2');

                            $row.find('.brand')
                                .val(item.brand)
                                .trigger('change.select2');

                            $row.find('.size')
                                .val(item.size)
                                .trigger('change.select2');

                            // INPUTS
                            $row.find('.order_type').val(item.order_type);
                            $row.find('.txtVariety').val(item.txtVariety);
                            $row.find('.txtVarietyID').val(item.txtVarietyID);
                            $row.find('.received_qty').val(item.received_qty);
                            $row.find('.purpose').val(item.purpose);
                            $row.find('.remarks').val(item.remarks);

                            // Attachment
                            $row.find('.file-name').text(
                                item.attachment ? item.attachment.split('/').pop() : ''
                            );

                            $tbody.append($row);
                        });
                    }
                });
            });





            $(document).on('click', '.btnViewGRN', function() {

                const grnId = $(this).data('id');
                if (!grnId) return alert('Invalid GRN selected');

                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'viewGRN',
                        PmGoodsReceiptNoteId: grnId
                    },
                    success: function(res) {
                        if (res.status === 'success' && res.Data) {
                            renderGRNView(res.Data);
                            $('#viewGRNModal').modal('show');
                        } else {
                            alert(res.Msg || 'No data found');
                        }
                    },
                    error: function() {
                        alert('Failed to load GRN details');
                    }
                });
            });


            function renderGRNView(data) {

                /* ================= HEADER ================= */
                let headerHTML = `
        <h6>Header Details</h6>
        <div class="container-fluid mb-3">
            <div class="row mb-2">
                <div class="col-md-3"><strong>Doc No:</strong></div>
                <div class="col-md-3">${data.DocNo || '-'}</div>
                <div class="col-md-3"><strong>Date:</strong></div>
                <div class="col-md-3">${data.Date || '-'}</div>
            </div>


            <div class="row mb-2">
                <div class="col-md-3"><strong>Vendor:</strong></div>
                <div class="col-md-3">${data.VendorId || '-'}</div>
                <div class="col-md-3"><strong>Invoice Date:</strong></div>
                <div class="col-md-3">${data.InvoiceDate || '-'}</div>
            </div>

              <div class="row mb-2">
                <div class="col-md-3"><strong>Is GST</strong></div>
                <div class="col-md-3">${data.IsGST || '-'}</div>
                <div class="col-md-3"><strong>Credit Note Type:</strong></div>
                <div class="col-md-3">${data.CreditNoteType || '-'}</div>
            </div>



            <div class="row mb-2">
                <div class="col-md-3"><strong>Invoice No:</strong></div>
                <div class="col-md-3">${data.InvoiceNumber || '-'}</div>
                <div class="col-md-3"><strong>Narration:</strong></div>
                <div class="col-md-3">${data.Narration || '-'}</div>
            </div>

            

             <div class="row mb-2">
                <div class="col-md-3"><strong>Status:</strong></div>
                <div class="col-md-3">${data.status || '-'}</div>
                <div class="col-md-3"><strong>Freight Type:</strong></div>
                <div class="col-md-3">${data.FreightType || '-'}</div>
         </div>
        </div>
    `;

                /* ================= BREAKUP TABLE ================= */
                let tableHTML = `
        <h6 class="mt-3">Item Breakup</h6>
        <table class="table table-bordered table-sm">
            <thead class="table-primary">
                <tr>
                <th>PRO Number</th>
                    <th>Variety</th>
                    <th>Grade</th>
                   <th>Type Of Order</th>
                    <th>Packing Style</th>
                    <th>Process Type</th>
                    
                    <th>Brand</th>
                    <th>Size in mm</th>
                   
                    <th>Quantity</th>
                    <th>Purpose</th>
                    <th>Attachment</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
    `;

                if (Array.isArray(data.BreakUps) && data.BreakUps.length > 0) {

                    data.BreakUps.forEach(item => {
                        tableHTML += `
                <tr>
                <td>${item.ProformaID || '-'}</td>
                    <td>${item.VarietyName || item.VarietyID || '-'}</td>
                    <td>${item.Grade || '-'}</td>
                   <td>${item.TypeofOrder || '-'}</td>
                    <td>${item.PackingStyleName || item.PackingStyle || '-'}</td>
                    <td>${item.freezing || '-'}</td>
                    
                    <td>${item.BrandName || item.Brand || '-'}</td>
                    <td>${item.size || '-'}</td>
                    <td>${item.ReceivedQty || 0}</td>
                    <td>${item.Purpose || '-'}</td>
                    <td>${item.Attachment ? `<a href="${item.Attachment}" target="_blank">View</a>` : '-'}</td>
                    <td>${item.Remarks || '-'}</td>


                </tr>
            `;
                    });

                } else {
                    tableHTML += `
            <tr>
                <td colspan="6" class="text-center">No items</td>
            </tr>
        `;
                }

                tableHTML += `</tbody></table>`;

                $('#viewGRNContent').html(headerHTML + tableHTML);
            }










            // Delete Goods Receipt Note
            $(document).on('click', '.btnDelete', function() {

                var btn = $(this);
                var PmGoodsReceiptNote_id = btn.data('id');

                if (!PmGoodsReceiptNote_id) {
                    alert('Item ID not found.');
                    return;
                }

                if (!confirm('Are you sure you want to delete this Goods Receipt Note?')) return;

                $.ajax({
                    url: 'Ajax/ajax-goods-receipt-note-packaging.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        PmGoodsReceiptNote_id: PmGoodsReceiptNote_id
                    },
                    success: function(res) {
                        res = (typeof res === 'string') ? JSON.parse(res) : res;

                        if (res.status === 'success') {
                            alert('Deleted Successfully!');
                            btn.closest('tr').fadeOut(300, function() {
                                $(this).remove();
                            });
                            loadIndents();
                        } else {
                            alert('Error: ' + (res.Msg || 'Unable to delete.'));
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('AJAX Error: ' + error);
                    }
                });
            });






            loadIndents();

        });
    </script>

</body>

</html>