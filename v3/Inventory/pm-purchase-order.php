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
$resVariety = $resDropdownsAPI->Data->Variety;
$resPackingStyle = $resDropdownsAPI->Data->PackingStyle;
$resGrade = $resDropdownsAPI->Data->Grade;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resBrand = $resDropdownsAPI->Data->Brand;


$User = "Purchase Order (PM)";
$PageTitle = $User;
$PageTitleFolder = "PO-General-Store";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <?php include_once ROOT . 'includes/head.php' ?>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>




    <style>
        #workflowTable {
            table-layout: fixed;
            min-width: 2200px;
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
            width: 250px;
        }

        /* Item */
        #workflowTable th:nth-child(2),
        td:nth-child(2) {
            width: 150px;
        }

        /* Unit */
        #workflowTable th:nth-child(3),
        td:nth-child(3) {
            width: 250px;
        }

        /* Available Qty */
        #workflowTable th:nth-child(4),
        td:nth-child(4) {
            width: 150px;
        }

        /* Unit Price */
        #workflowTable th:nth-child(5),
        td:nth-child(5) {
            width: 150px;
        }

        /* Quantity */
        #workflowTable th:nth-child(6),
        td:nth-child(6) {
            width: 150px;
        }

        /* Supplier Rate */
        #workflowTable th:nth-child(7),
        td:nth-child(7) {
            width: 150px;
        }

        /* Total Value */
        #workflowTable th:nth-child(8),
        td:nth-child(8) {
            width: 150px;
        }

        /* Discount% */
        #workflowTable th:nth-child(9),
        td:nth-child(9) {
            width: 150px;
        }

        /* Discount */
        #workflowTable th:nth-child(10),
        td:nth-child(10) {
            width: 150px;
        }

        /* GST% */
        #workflowTable th:nth-child(11),
        td:nth-child(11) {
            width: 150px;
        }

        /* SGST Amt */
        #workflowTable th:nth-child(12),
        td:nth-child(12) {
            width: 150px;
        }

        /* CGST Amt */
        #workflowTable th:nth-child(13),
        td:nth-child(13) {
            width: 150px;
        }

        /* IGST Amt */
        #workflowTable th:nth-child(14),
        td:nth-child(14) {
            width: 120px;
        }

        /* Tax Amount */
        #workflowTable th:nth-child(15),
        td:nth-child(15) {
            width: 120px;
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

        #workflowTable th:nth-child(19),
        td:nth-child(19) {
            width: 150px;
        }

        #workflowTable th:nth-child(20),
        td:nth-child(20) {
            width: 150px;
        }

        #workflowTable th:nth-child(21),
        td:nth-child(21) {
            width: 60px;
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
                                                <label>Date</label>
                                                <input type="date" class="form-control" id="pdate" name="pdate"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-3">
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

                                            <div class="col-md-3">
                                                <label>Payment & Terms</label>
                                                <select id="payment_terms" name="payment_terms" class="form-control">
                                                    <option value="">Select Payment Type</option>
                                                    <option value="DCMC">30 Days credit fom the date of material received</option>
                                                    <option value="AMR">100% After material received</option>
                                                    <option value="APBD">50 % Advance along with the purchase order balance 50 % before dispatch the material</option>
                                                    <option value="APAR">50 % Along with the purchase order 50 % after material received</option>
                                                    <option value="PO">100 % Advance along with the purchase order</option>
                                                    <option value="DMR">15 Days credit from the date of material received </option>
                                                    <option value="APAI">30 % advance along with the purchase order Balance 70 % after installation</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">

                                            <div class="col-md-3">
                                                <label>Freight Type</label>
                                                <select class="form-control" id="freight_type" name="freight_type">
                                                    <option value="">Select Freight Type</option>
                                                    <option value="Inclusive">Inclusive</option>
                                                    <option value="Exclusive">Exclusive</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Freight Charges</label>
                                                <input type="number" class="form-control" id="Freight" name="Freight"
                                                    placeholder="0.00">
                                            </div>

                                            <div class="col-md-3">
                                                <label>Subject</label>
                                                <input type="text" class="form-control" id="subject" name="subject"
                                                    placeholder="Enter Subject">
                                            </div>

                                            <div class="col-md-3">
                                                <label>Narration</label>
                                                <input type="text" class="form-control" id="narration" name="narration"
                                                    placeholder="Enter Narration">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Status</label>
                                                <input type="text" class="form-control status" id="status" name="status" value="Approve" readonly>
                                            </div>
                                        </div>

                                        <hr>

                                        <h4 class="mt-4 mb-3">Work Flow (From Indent)</h4>
                                        <div style="overflow-x:auto; width:100%;">
                                            <table class="table table-bordered" id="workflowTable">
                                                <thead>
                                                    <tr>
                                                        <th>PRO Number</th>
                                                        <th>Approved Date</th>
                                                        <th>Variety <span style="color:red">*</span></th>
                                                        <th>Grade</th>
                                                        <th>Packing Style</th>
                                                        <th>Process Type</th>
                                                        <th>Brand</th>
                                                        <th>Size in mm</th>
                                                        <th>Available Qty</th>
                                                        <th>PRO Qty</th>
                                                        <th>Weight(KGs)</th>
                                                        <th>Rate</th>
                                                        <th>Gross</th>
                                                        <th>GST %</th>
                                                        <th>SGST</th>
                                                        <th>CGST</th>
                                                        <th>Invoice Amount</th>
                                                        <th>Tax Amount</th>
                                                        <th>Dispatched Date</th>
                                                        <th>Plant Code</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <!-- <input type="hidden" name="BreakUpId[]" class="BreakUpId" value="0"> -->
                                                        <!-- <td><input type="text" class="form-control pro"
                                                            name="pro[]"></td> -->
                                                        <td>
                                                            <input type="hidden" name="BreakUpId[]" class="BreakUpId" value="0">
                                                            <select class="drpSelect form-control pro" name="pro[]"></select>
                                                        </td>
                                                        <td><input type="date" class="form-control a_date"
                                                                name="a_date[]"></td>
                                                        <td>
                                                            <input type="input" class="form-control txtVariety"
                                                                name="txtVariety[]" placeholder="Click to Select Variety"
                                                                readonly />
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
                                                            <select class="drpSelect form-control brand"
                                                                name="brand[]">
                                                                <option value=""> Select</option>
                                                                <?php foreach ($resBrand as $item) { ?>
                                                                    <option value="<?php echo $item->BrandID; ?>">
                                                                        <?php echo $item->BrandName ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td><input type="text" class="form-control size" name="size[]"></td>
                                                        <!-- <td><input type="file" class="form-control attachment" name="attachment[]"></td> -->
                                                        <td><input type="number" class="form-control availqty"
                                                                name="avail-qty[]"></td>
                                                        <td><input type="number" class="form-control pro-qty"
                                                                name="pro-qty[]"></td>

                                                        <td><input type="text" class="form-control weight" name="weight[]">
                                                        </td>

                                                        <td><input type="number" class="form-control rate"
                                                                name="rate[]" value="0.00" readonly></td>

                                                        <td><input type="number" class="form-control gross" name="gross[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control gst" name="gst[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control cgst" name="cgst[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control sgst" name="sgst[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control invoice_amt"
                                                                name="invoice_amt[]" value="0.00" readonly></td>
                                                        <td><input type="number" class="form-control tax_amt"
                                                                name="tax_amt[]" value="0.00" readonly></td>
                                                        <td><input type="date" class="form-control disp_date"
                                                                name="disp_date[]"></td>
                                                        <td><input type="text" class="form-control code" name="code[]"></td>
                                                        <td><button type="button" class="btn btn-success btnAddRow"><i
                                                                    class="fa fa-plus"></i></button></td>
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
    <?php include($IncludesPath . "ShowDataTable.php"); ?>



    <!-- DataTables -->
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <!-- DataTables Buttons -->
    <link href="<?php echo WORKING_URL ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet">

    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>

    <script src="<?php echo WORKING_URL ?>assets/libs/jszip/jszip.min.js"></script>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>




    <div class="modal fade" id="viewPOModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Purchase Order - View</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="viewPOContent"></div>
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

            $(document).on('click', '.btnAddRow', function() {
                var newRow = $('#workflowTable tbody tr:first').clone();
                newRow.find('input').val('');
                newRow.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                newRow.find('td:last').html('<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>');
                $('#workflowTable tbody').append(newRow);
            });

            // Remove row
            $(document).on('click', '.btnRemoveRow', function() {
                $(this).closest('tr').remove();
            });
            getIndents();



            // function loadPOList() {
            //     $.ajax({
            //         url: 'Ajax/ajax-pm-purchase-order.php',
            //         type: 'POST',
            //         data: {
            //             methodType: 'load'
            //         },
            //         success: function(html) {
            //             $('#poListDiv').html(html);
            //         }
            //     });
            // }



            function loadPOList() {
                $.ajax({
                    url: 'Ajax/ajax-pm-purchase-order.php',
                    type: 'POST',
                    data: {
                        methodType: 'load'
                    },
                    success: function(html) {
                        $("#poListDiv").html(html);

                        setTimeout(function() {
                            initPODataTable();
                        }, 100);
                    }
                });
            }

            function initPODataTable() {

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
                        title: 'Purchase Order List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // Inject buttons near search (same as vendor)
                setTimeout(function() {

                    if (!$("#btnMoreSearchPO").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearchPO">More Search</button>')
                            .insertAfter("#tblDataTable_filter label");
                    }

                    if (!$("#btnLoadAllPO").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAllPO">Load All</button>')
                            .insertAfter("#btnMoreSearchPO");
                    }

                }, 200);
            }
            // MORE SEARCH (UI refresh only)
            $(document).on("click", "#btnMoreSearchPO", function() {
                loadPOList();
            });

            // LOAD ALL (UI refresh only)
            $(document).on("click", "#btnLoadAllPO", function() {
                loadPOList();
            });








            function getIndents() {
                $.ajax({
                    url: 'Ajax/ajax-pm-purchase-order.php',
                    type: 'POST',
                    data: {
                        methodType: 'indents'
                    },
                    success: function(res) {
                        console.log(res);
                        var list = "<option value=''>Select</option>";
                        if (res && res.Data) {
                            $.each(res.Data, function(index, value) {
                                list += "<option value=" + value.Id + ">" + value.DocNo + "</option>";
                            });
                        }

                        $('#doc_no').html(list).trigger('change.select2');
                        $('.pro').html(list).trigger('change.select2');
                    }
                });
            }

            $("#doc_no").on("change", function() {
                getIndentsBreakUp();
            });

            function getIndentsBreakUp() {
                $.ajax({
                    url: 'Ajax/ajax-pm-purchase-order.php',
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
                            // var $templateRow = $('#workflowTable tbody tr:first').clone();

                            // // Remove all rows
                            // $('#workflowTable tbody').html('');

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
                            //         $newRow.find('.item').val(value.GeneralItemId).trigger('change');
                            //         getItemDetails(value.GeneralItemId, $newRow);
                            //     }

                            //     // If needed, fill other fields (uncomment as needed)
                            //     // $newRow.find('input[name="received_qty[]"]').val(value.received_qty || '');
                            //     // $newRow.find('input[name="location[]"]').val(value.location || '');
                            //     // $newRow.find('input[name="purpose[]"]').val(value.purpose || '');
                            //     // $newRow.find('input[name="remark[]"]').val(value.remark || '');

                            //     // Append new row
                            //     $('#workflowTable tbody').append($newRow);

                            //     // Reinitialize select2 safely (only if loaded)
                            //     if (typeof $.fn.select2 === "function") {
                            //         $newRow.find('.item').select2({
                            //             placeholder: "Select Item",
                            //             width: "100%"
                            //         });
                            //     }
                            // });
                        }
                    }
                });
            }



            $(document).on('click', '.btnEdit', function() {
                const poId = $(this).data('id');

                if (!poId) return;

                $('#btnSavePO').text('Update');
                $('#po_id').val(poId);

                $.ajax({
                    url: 'Ajax/ajax-pm-purchase-order.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'getSingle',
                        PmPurchaseOrder_id: poId
                    },
                    success: function(res) {
                        if (!(res && res.status === 'success')) {
                            alert(res?.Msg || 'Unable to load PO details.');
                            return;
                        }

                        const data = res.Data; // flattened header + breakUp array
                        const breakups = data.breakUp || [];

                        // --- Fill header fields ---
                        $('#doc_no').val(data.ProformaID || '').trigger('change.select2');
                        $('#pdate').val(data.PoDate || '');
                        $('#vendor_id').val(data.Supplier || '').trigger('change.select2');
                        $('#narration').val(data.Narration || '');
                        $('#payment_terms').val(data.PaymentTerms || '');
                        $('#Freight').val(data.FreightCharges || '');
                        $('#freight_type').val(data.FreightType || '');
                        $('#subject').val(data.Subject || '');
                        $('#status').val(data.Status || '');

                        // --- Clear existing breakup rows ---
                        const $templateRow = $('#workflowTable tbody tr:first').clone();
                        $('#workflowTable tbody').empty();

                        if (breakups.length) {
                            breakups.forEach(item => {
                                const $row = $templateRow.clone();

                                $row.find('.BreakUpId').val(item.BreakUpID || 0);
                                $row.find('.pro').val(item.ProformaID || '').trigger('change.select2');
                                $row.find('.a_date').val(item.ApprovedDate || '');

                                $row.find('.disp_date').val(item.DispacthedDate || ''); // match DB spelling
                                $row.find('.txtVarietyID').val(item.VarietyID || '');
                                $row.find('.packing_style').val(item.PackingStyle || '').trigger('change.select2');
                                $row.find('.brand').val(item.Brand || '').trigger('change.select2');
                                $row.find('.grade').val(item.grade || '').trigger('change.select2');
                                $row.find('.freezing').val(item.freezing || '').trigger('change.select2');
                                $row.find('.weight').val(item.weight || '').trigger('change.select2');
                                $row.find('.size').val(item.size || '').trigger('change.select2');
                                $row.find('.availqty').val(item.availqty || '').trigger('change.select2');


                                $row.find('.pro-qty').val(item.ProQty || 0);
                                $row.find('.code').val(item.PlantCode || '');

                                // Clear unused fields
                                $row.find('.txtVariety, .rate, .gross, .gst, .sgst, .cgst, .invoice_amt, .tax_amt')
                                    .val('').trigger('change.select2');

                                $('#workflowTable tbody').append($row);
                            });
                        } else {
                            const $emptyRow = $templateRow.clone();
                            $emptyRow.find('input, select').val('').trigger('change.select2');
                            $('#workflowTable tbody').append($emptyRow);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('Failed to load PO details. ' + error);
                    }
                });
            });












            // Delete Purchase Order
            $(document).on('click', '.btnDelete', function() {
                var btn = $(this); // the clicked delete button
                var PmPurchaseOrder_id = btn.data('id'); // matches the controller key

                if (!PmPurchaseOrder_id) {
                    alert('Purchase Order ID not found.');
                    return;
                }

                if (!confirm('Are you sure you want to delete this Purchase Order?')) return;

                $.ajax({
                    url: 'Ajax/ajax-pm-purchase-order.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        PmPurchaseOrder_id: PmPurchaseOrder_id
                    },
                    success: function(res) {
                        try {
                            res = (typeof res === 'string') ? JSON.parse(res) : res;

                            if (res.status === 'success') {
                                alert('Deleted Successfully!');
                                // Remove the deleted row from the table
                                btn.closest('tr').fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                alert('Error: ' + (res.Msg || 'Unable to delete.'));
                            }
                        } catch (e) {
                            alert('Error parsing server response.');
                            console.error(e, res);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('AJAX Error: ' + error);
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

                            // ✅ fill values safely
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


            $('#btnSavePO').click(function() {
                var poId = $('#po_id').val(); // hidden field
                var methodType = poId ? 'update' : 'save';

                // --- Collect breakup table data ---
                var breakupData = [];
                $('#workflowTable tbody tr').each(function() {
                    var $row = $(this);

                    // Skip empty rows (optional)
                    if (!$row.find('.txtVarietyID').val()) return;

                    breakupData.push({
                        indent: $row.find('.BreakUpId').val() || '', // existing BreakUpId or empty for new
                        pro: $row.find('.pro').val() || 0,
                        a_date: $row.find('.a_date').val() || '',
                        txtVarietyID: $row.find('.txtVarietyID').val() || 0,
                        packing_style: $row.find('.packing_style').val() || 0,
                        brand: $row.find('.brand').val() || 0,
                        size: $row.find('.size').val() || '',
                        grade: $row.find('.grade').val() || '',
                        availqty: $row.find('.availqty').val() || '',
                        freezing: $row.find('.freezing').val() || '',
                        weight: $row.find('.weight').val() || '',
                        proqty: $row.find('.pro-qty').val() || 0,
                        disp_date: $row.find('.disp_date').val() || '',
                        code: $row.find('.code').val() || ''
                    });
                });

                // --- Prepare FormData ---
                var formData = new FormData($('#poForm')[0]);
                formData.set('methodType', methodType);
                if (poId) formData.set('po_id', poId);
                formData.set('breakupData', JSON.stringify(breakupData));

                // --- AJAX call ---
                $.ajax({
                    url: 'Ajax/ajax-pm-purchase-order.php',
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
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('AJAX request failed! ' + error);
                    }
                });
            });




            $(document).on('click', '.btnViewPO', function() {
                const poId = $(this).data('id');

                $.ajax({
                    url: 'Ajax/ajax-pm-purchase-order.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'viewPurchaseOrder',
                        PmPurchaseOrderId: poId
                    },
                    success: function(res) {
                        if (res.status === 'success' && res.Data) {
                            renderPOViewModal(res.Data);
                            $('#viewPOModal').modal('show');
                        } else {
                            alert(res.Msg || 'No data found');
                        }
                    }
                });
            });



            function renderPOViewModal(data) {

                let headerHTML = `
    <h6>Header Details</h6>
    <div class="container-fluid mb-3">

        <div class="row mb-2">
            <div class="col-md-3"><strong>Doc No:</strong></div>
            <div class="col-md-3">${data.DocNo || '-'}</div>
            <div class="col-md-3"><strong>PO Date:</strong></div>
            <div class="col-md-3">${data.PoDate || '-'}</div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Supplier:</strong></div>
            <div class="col-md-3">${data.Supplier || '-'}</div>
            <div class="col-md-3"><strong>Payment Terms:</strong></div>
            <div class="col-md-3">${data.PaymentTerms || '-'}</div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Freight Type:</strong></div>
            <div class="col-md-3">${data.FreightType || '-'}</div>
            <div class="col-md-3"><strong>Freight Charges:</strong></div>
            <div class="col-md-3">${data.FreightCharges || '-'}</div>
        </div>

       <div class="row mb-2">
            <div class="col-md-3"><strong>Subject:</strong></div>
            <div class="col-md-3">${data.Subject || '-'}</div>

            <div class="col-md-3"><strong>Narration:</strong></div>
            <div class="col-md-3">${data.Narration || '-'}</div>
    </div>

   

     <div class="row mb-2">
            <div class="col-md-3"><strong>Status:</strong></div>
            <div class="col-md-3">${data.Status || '-'}</div>

    </div>
    `;

                let tableHTML = `
    <h6 class="mt-3">Item Breakup</h6>
    <table class="table table-bordered table-sm">
        <thead class="table-primary">
            <tr>
                <th>Variety</th>
                <th>Grade</th>
                <th>Packing Style</th>
                <th>Process Type</th>
                <th>Brand</th>
                <th>Size in mm </th>
                <th>Available Qty</th>
                <th>Weight</th>
                <th>Pro Qty</th>
                <th>Approved Date</th>
                <th>Dispatched Date</th>
                <th>Plant Code</th>
            </tr>
        </thead>
        <tbody>
    `;

                if (data.breakUp && data.breakUp.length) {
                    data.breakUp.forEach(item => {
                        tableHTML += `
            <tr>
                <td>${item.VarietyName || '-'}</td>
                <td>${item.grade || '-'}</td>
                <td>${item.PackingStyleName || '-'}</td>
                <td>${item.freezing || '-'}</td>
                <td>${item.BrandName || '-'}</td>
                <td>${item.size || '-'}</td>
                <td>${item.availqty || 0}</td>
                <td>${item.weight || '-'}</td>
                <td>${item.ProQty || 0}</td>
                <td>${item.ApprovedDate || '-'}</td>
                <td>${item.DispacthedDate || '-'}</td>
                <td>${item.PlantCode || '-'}</td>
            </tr>`;
                    });
                } else {
                    tableHTML += `
            <tr>
                <td colspan="7" class="text-center">No items</td>
            </tr>`;
                }

                tableHTML += `</tbody></table>`;

                $('#viewPOContent').html(headerHTML + tableHTML);
            }
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
    </script>





</body>

</html>