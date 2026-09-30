<?php
$Status = $_SESSION['Status'];
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_INVENTORY_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resGeneralItems = $resDropdownsAPI->Data->GeneralItems;
$resVendors = $resDropdownsAPI->Data->Vendors;
$resDepartments = $resDropdownsAPI->Data->Departments;
$previousData = $resDropdownsAPI->Data->LastFiveMonthsData;

$User = "Indent Raised";
$PageTitle = $User . " Details";
$PageTitleFolder = "Indent";


// $jsonData = '{
//     "1": [{"date":"10/10/2025","qty":10,"price":"35.00"},{"date":"10/09/2025","qty":15,"price":"33.00"}],
//     "2": [{"date":"8/10/2025","qty":10,"price":"99.00"},{"date":"8/09/2025","qty":15,"price":"80.00"}],
//     "3": [{"date":"5/10/2025","qty":10,"price":"20.00"},{"date":"5/09/2025","qty":15,"price":"18.00"}]
// }';

// $previousData = ($LastFiveMonthsData);
// echo print_r($previousData);exit;

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
            width: 100%;
        }

        #itemTable th,
        #itemTable td {
            padding: 5px 8px;
            /* reduce cell padding */
            font-size: 14px;
            /* slightly smaller font */
            vertical-align: middle;
        }



        /* Adjust column widths */
        #itemTable th:nth-child(1),
        #itemTable td:nth-child(1) {
            width: 12%;
            /* Item column wider */
        }

        #itemTable th:nth-child(2),
        #itemTable td:nth-child(2) {
            width: 4%;
            /* UoM */
        }

        #itemTable th:nth-child(3),
        #itemTable td:nth-child(3) {
            width: 4%;
            /* Available Qty */
        }

        #itemTable th:nth-child(4),
        #itemTable td:nth-child(4) {
            width: 4%;
            /* Item Price */
        }

        #itemTable th:nth-child(5),
        #itemTable td:nth-child(5) {
            width: 4%;
            /* Requested Qty */
        }

        #itemTable th:nth-child(6),
        #itemTable td:nth-child(6) {
            width: 4%;
            /* Base Value */
        }

        #itemTable th:nth-child(7),
        #itemTable td:nth-child(7) {
            width: 4%;
            /* GST% */
        }

        #itemTable th:nth-child(8),
        #itemTable td:nth-child(8) {
            width: 4%;
            /* Invoice Amount */
        }

        #itemTable th:nth-child(9),
        #itemTable td:nth-child(9) {
            width: 4%;
            /* Purpose - reduced */
        }

        #itemTable th:nth-child(10),
        #itemTable td:nth-child(10) {
            width: 3%;
            /* Attachment - reduced */
        }

        #itemTable th:nth-child(11),
        #itemTable td:nth-child(11) {
            width: 5%;
            /* Remarks */
        }

        #itemTable th:nth-child(12),
        #itemTable td:nth-child(12) {
            width: 2%;
            /* Action button */
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
                        <input type="hidden" id="indent_id" name="indent_id" value="">
                        <!-- <input type="hidden" name="breakup_id[]" value=""> -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                // Example: IND-YYYYMMDD-XXXX (XXXX = random 4-digit number)
                                                $docNo = "IND-" . date('Ymd') . "-" . rand(1000, 9999);
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <div class="col-md-3">
                                                <label>Doc No</label>
                                               
                                                <input type="text" class="form-control" id="doc_no" name="doc_no"
                                                    value="<?php echo $docNo; ?>">
                                            </div>



                                            <div class="col-md-3">
                                                <label>Date</label>
                                                <input type="date" class="form-control" id="pdate" name="pdate"
                                                    value="<?php echo date('d-m-Y'); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label>Department</label>
                                                <select id="department" name="department" class="form-control">
                                                    <option value="">Select Department</option>
                                                    <?php foreach ($resDepartments as $department): ?>
                                                        <option value="<?php echo $department->DepartmentID ?>">
                                                            <?php echo $department->DepartmentName ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Narration</label>
                                                <input type="text" class="form-control" id="narration" name="narration"
                                                    placeholder="Enter Narration">
                                            </div>
                                        </div>
                                        <hr>

                                        <!-- Item Details -->
                                        <h4 class="mt-4 mb-3">Item Details</h4>
                                        <table class="table table-bordered" id="itemTable">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>UoM</th>
                                                    <th>Available Qty</th>
                                                    <!-- <th>Item Price</th> -->
                                                    <th>Requested Qty</th>
                                                    <!-- <th>Base Value</th> -->
                                                    <!-- <th>GST%</th> -->
                                                    <!-- <th>Invoice Amount</th> -->
                                                    <th>Purpose</th>
                                                    <th>Attachment</th>
                                                    <th>Remarks</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <!-- per-row BreakUpId for update mapping -->
                                                    <input type="hidden" name="breakup_id[]" class="breakup-id" value="">
                                                    <td>
                                                        <select class="form-control itemSelect" name="item[]">
                                                            <option value="">Select Item</option>
                                                            <?php foreach ($resGeneralItems as $item): ?>
                                                                <option
                                                                    value="<?php echo $item->GeneralItemId; ?>"
                                                                    data-name="<?php echo htmlspecialchars($item->name); ?>">
                                                                    <?php echo $item->name ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>

                                                    <td><input type="text" class="form-control uom" name="uom[]"
                                                            readonly></td>
                                                    <td><input type="number" class="form-control avail_qty"
                                                            name="avail_qty[]" readonly></td>
                                                    <!-- <td><input type="number" class="form-control item_price"
                                                            name="item_price[]" readonly></td> -->
                                                    <td><input type="number" class="form-control requested_qty"
                                                            name="requested_qty[]" placeholder="0.00" min="0"></td>
                                                    <!-- <td><input type="number" class="form-control base_value"
                                                            name="base_value[]" value="0.00" readonly></td> -->
                                                    <!-- <td><input type="number" class="form-control gst" name="gst[]"
                                                            readonly></td> -->
                                                    <!-- <td><input type="number" class="form-control invoice_amt"
                                                            name="invoice_amt[]" value="0.00" readonly></td> -->
                                                    <td><input type="text" class="form-control purpose"
                                                            name="purpose[]"></td>
                                                    <!-- <td><input type="file" class="form-control attachment" name="attachment[]"></td> -->
                                                    <td>
                                                        <label class="btn btn-light btn-sm mb-0"
                                                            style="cursor:pointer;">
                                                            <i class="fa fa-paperclip"></i>
                                                            <input type="file" class="attachment" name="attachment[0][]" multiple
                                                                style="display:none;">
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

                                        <!-- Container where item data will appear -->
                                        <div id="itemDataList" style="margin-top:10px;"></div>

                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveIndent"
                                                class="btn btn-primary">Save</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Indent Records -->
                    <div class="mt-4">
                        <h4>Existing Indents</h4>
                        <div id="indentListDiv"></div>
                    </div>

                </div>
                <?php include($IncludesPath . "footer.php"); ?>
            </div>
        </div>

    </div>


    <div class="modal fade" id="indentViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Indent Details</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>
                <div class="modal-body" id="indentDetailsBody">
                    <!-- Indent details will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>


    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

    <script>
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
                    title: 'Indent List',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });

            // ---------------------------------------
            // ADD MORE SEARCH + LOAD ALL BUTTONS
            // ---------------------------------------
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



        $(document).ready(function() {

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

            // Add new row
            // $(document).on('click', '.btnAddRow', function() {
            //     var newRow = $('#itemTable tbody tr:first').clone();
            //     newRow.find('input').val('');
            //     newRow.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
            //     newRow.find('td:last').html('<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>');
            //     $('#itemTable tbody').append(newRow);
            // });
            // ================= ADD NEW ROW =================

            let rowIndex = 0; // global counter

$(document).on('click', '.btnAddRow', function () {

    var $templateRow = $('#itemTable tbody tr:first').clone();

    // increase index
    rowIndex++;

    // clear inputs
    $templateRow.find('input, select').val('').trigger('change');

    $templateRow.find('input[name="breakup_id[]"]').val('');
    $templateRow.find('input[name="existing_attachment[]"]').val('');
    $templateRow.find('.file-name').text('');
    $templateRow.find('.requested_qty, .base_value, .invoice_amt').val('0.00');

    // 🔥 IMPORTANT: update file input name
    $templateRow.find('.attachment').attr('name', 'attachment[' + rowIndex + '][]');

    // Set remove button
    $templateRow.find('td:last').html(
        '<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>'
    );

    $('#itemTable tbody').append($templateRow);

    // reinitialize select2
    if ($.fn.select2) {
        $templateRow.find('.itemSelect').select2({
            placeholder: "Select Item",
            width: "100%"
        });
    }
});
            
            // $(document).on('click', '.btnAddRow', function() {
            //     var $templateRow = $('#itemTable tbody tr:first').clone();

            //     // clear all inputs for new row, INCLUDING per-row breakup id
            //     $templateRow.find('input, select').val('').trigger('change');
            //     $templateRow.find('input[name="breakup_id[]"]').val(''); // <-- important
            //     $templateRow.find('input[name="existing_attachment[]"]').val('');
            //     $templateRow.find('.file-name').text('');
            //     $templateRow.find('.requested_qty, .base_value, .invoice_amt').val('0.00');

            //     // Set Action button
            //     $templateRow.find('td:last').html('<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>');

            //     $('#itemTable tbody').append($templateRow);

            //     if ($.fn.select2) {
            //         $templateRow.find('.itemSelect').select2({
            //             placeholder: "Select Item",
            //             width: "100%"
            //         });
            //     }
            // });

            // Remove row
            // $(document).on('click', '.btnRemoveRow', function() {
            //     $(this).closest('tr').remove();
            // });



            // $(document).on('click', '.btnRemoveRow', function() {
            //     var rowCount = $('#itemTable tbody tr').length;
            //     if (rowCount > 1) {
            //         $(this).closest('tr').remove();
            //     } else {
            //         // clear only the inputs if it's the last row
            //         var $row = $(this).closest('tr');
            //         $row.find('input, select').val('').trigger('change');
            //         $row.find('input[name="breakup_id[]"]').val('');
            //         $row.find('input[name="existing_attachment[]"]').val('');
            //         $row.find('.file-name').text('');
            //         $row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
            //     }
            // });

            $(document).on('click', '.btnRemoveRow', function () {

                var $row = $(this).closest('tr');
                var rowCount = $('#itemTable tbody tr').length;

                if (rowCount > 1) {
                    $row.remove();
                } else {
                    // clear only if last row
                    $row.find('input, select').val('').trigger('change');
                    $row.find('input[name="breakup_id[]"]').val('');
                    $row.find('input[name="existing_attachment[]"]').val('');
                    $row.find('.file-name').text('');
                    $row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                }

                // 🔥 IMPORTANT: Reindex all rows after remove
                $('#itemTable tbody tr').each(function (index) {

                    // update file input name
                    $(this).find('.attachment').attr('name', 'attachment[' + index + '][]');

                });

            });

            

            // Show selected file name
            // $(document).on('change', '.attachment', function() {
            //     const fileName = $(this).val().split('\\').pop();
            //     $(this).closest('td').find('.file-name').text(fileName);
            // });

            $(document).on("change", ".attachment", function () {
                let files = this.files;
                let names = [];

                for (let i = 0; i < files.length; i++) {
                    names.push(files[i].name);
                }

                $(this).closest("td").find(".file-name").text(names.join(", "));
            });


            $('#btnSaveIndent').on('click', function(e) {
                e.preventDefault();

                var formData = new FormData($('#indentForm')[0]);
                var indentId = $('#indent_id').val(); // existing ID when editing

                formData.set('methodType', 'save'); // always save

                // send indent_id only when updating
                if (indentId && indentId !== '') {
                    formData.set('indent_id', indentId);
                }


                let breakUp = [];

                $("#itemTable tbody tr").each(function() {
                    breakUp.push({
                        break_up_id: $(this).find('input[name="breakup_id[]"]').val(),
                        item_id: $(this).find(".itemSelect").val(),
                        requested_qty: $(this).find(".requested_qty").val(),
                        purpose: $(this).find(".purpose").val(),
                        remark: $(this).find(".remarks").val(),
                        attachment: $(this).find(".attachment").val(),
                    });
                });

                formData.set("breakUp", JSON.stringify(breakUp));



                $.ajax({
                    url: 'Ajax/Ajax-indent.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.Msg);

                            //-----------------------------------------
                            // RESET ONLY IF NEW ENTRY (NOT UPDATE)
                            //-----------------------------------------
                            if (!indentId) {
                                // reset form
                                $('#indentForm')[0].reset();
                                $('#indent_id').val('');
                                $('#btnSaveIndent').text('Save');

                                // Reset table to one empty row
                                const $templateRow = $('#itemTable tbody tr:first').clone();
                                $templateRow.find('input, select').val('').trigger('change');
                                $templateRow.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                                $templateRow.find('.file-name').text('');
                                $templateRow.find('input[name="breakup_id[]"]').val('');
                                $templateRow.find('td:last').html(
                                    '<button type="button" class="btn btn-success btnAddRow"><i class="fa fa-plus"></i></button>'
                                );

                                $('#itemTable tbody').html($templateRow);
                            }

                            //-----------------------------------------
                            // Reload Indents Table After Save/Update
                            //-----------------------------------------
                            loadIndents();
                        } else {
                            alert('Error: ' + (res.Msg || JSON.stringify(res)));
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX error:', xhr.responseText || xhr.statusText);
                        alert('AJAX request failed! Check console/network.');
                    }
                });
            });



            // ------------------- MORE SEARCH -------------------
            $(document).on("click", "#btnMoreSearch", function() {
                $(this).data("moresearch", true);

                let searchKey = $("#tblDataTable_filter input").val();

                loadIndents({
                    searchKey: searchKey,
                    loadAll: false
                });
            });


            // ------------------- LOAD ALL -------------------
            $(document).on("click", "#btnLoadAll", function() {
                $(this).data("loadall", true);

                loadIndents({
                    searchKey: "",
                    loadAll: true
                });
            });



            function loadIndents(params = {}) {
                $.ajax({
                    url: 'Ajax/Ajax-indent.php',
                    type: 'POST',
                    data: {
                        methodType: 'load',
                        searchKey: params.searchKey || '',
                        loadAll: params.loadAll || false
                    },
                    success: function(html) {
                        $('#indentListDiv').html(html);
                        fnShowDataTable();
                    }
                });
            }




            // ================== EDIT INDENT ==================

            // Edit button click

            // $(document).on('click', '.btnEdit', function(e) {
            //     e.preventDefault();
            //     const indentId = $(this).data('id');
            //     if (!indentId) return;

            //     $('#btnSaveIndent').text('Update');
            //     $('#indent_id').val(indentId);

            //     $.ajax({
            //         url: 'Ajax/Ajax-indent.php',
            //         type: 'POST',
            //         dataType: 'json',
            //         data: {
            //             methodType: 'view',
            //             indent_id: indentId
            //         },
            //         success: function(res) {
            //             if (res.status !== 'success' || !res.Data) {
            //                 alert(res.Msg || 'Could not load indent data.');
            //                 return;
            //             }

            //             const h = res.Data;
            //             const rows = h.items && h.items.length ? h.items : h.breakUp || [];

            //             const $templateRow = $('#itemTable tbody tr:first').clone();
            //             $templateRow.find('input, select').val('').trigger('change');
            //             $templateRow.find('input[name="breakup_id[]"]').val('');
            //             $templateRow.find('input[name="existing_attachment[]"]').val('');
            //             $templateRow.find('.file-name').text('');
            //             $('#itemTable tbody').html('');

            //             // --------------------------
            //             // Header fields
            //             // --------------------------
            //             $('#doc_no').val(h.DocNo || '');
            //             $('#pdate').val(h.Date || '');
            //             $('#narration').val(h.Narration || '');
            //             // setTimeout(() => $('#department').val(h.DepartmentId || '').trigger('change.select2'), 50);

            //             setTimeout(() => {
            //                 if (h.DepartmentId) {
            //                     $('#department').val(h.DepartmentId).trigger('change');
            //                 } else if (h.Department) {
            //                     $("#department option").filter(function() {
            //                         return $(this).text().trim() === h.Department.trim();
            //                     }).prop("selected", true).trigger("change");
            //                 }
            //             }, 100);


            //             // --------------------------
            //             // Fill Item Rows
            //             // --------------------------
            //             rows.forEach(row => {
            //                 const $newRow = $templateRow.clone();

            //                 //  FIXED: BreakUpId Mapping (Prevents new item insert)
            //                 const breakupId =
            //                     row.BreakUpId ||
            //                     row.breakup_id ||
            //                     row.BreakUpID ||
            //                     row.Id ||
            //                     row.id ||
            //                     '';

            //                 $newRow.find('input[name="breakup_id[]"]').val(breakupId);

            //                 // Item select
            //                 const genItemId = row.GeneralItemId || '';

            //                 if ($.fn.select2) {
            //                     $newRow.find('.itemSelect').select2({
            //                         placeholder: "Select Item",
            //                         width: "100%"
            //                     });

            //                     setTimeout(() => {
            //                         if (genItemId)
            //                             $newRow.find('.itemSelect').val(genItemId).trigger('change.select2');
            //                     }, 100);
            //                 } else {
            //                     if (genItemId) $newRow.find('.itemSelect').val(genItemId).trigger('change');
            //                 }

            //                 // Fields
            //                 $newRow.find('.requested_qty').val(row.Quantity || row.ReceivedQuantity || 0);
            //                 $newRow.find('.purpose').val(row.Purpose || row.purpose || '');
            //                 $newRow.find('.remarks').val(row.Remarks || row.Remark || '');
            //                 $newRow.find('.uom').val(row.UoM || '');
            //                 $newRow.find('.avail_qty').val(row.AvailableQty || '');
            //                 $newRow.find('.item_price').val(row.ItemPrice || '');
            //                 $newRow.find('.base_value').val(row.BaseValue || 0);
            //                 $newRow.find('.gst').val(row.GST || 0);
            //                 $newRow.find('.invoice_amt').val(row.InvoiceAmount || 0);

            //                 // Attachments
            //                 $newRow.append(
            //                     '<input type="hidden" name="existing_attachment[]" value="' + (row.Attachment || '') + '">'
            //                 );

            //                 if (row.Attachment) {
            //                     const fileName = row.Attachment.split('/').pop();
            //                     $newRow.find('.file-name').text(fileName);
            //                 }

            //                 // Action button
            //                 $newRow.find('td:last').html(
            //                     '<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>'
            //                 );

            //                 $('#itemTable tbody').append($newRow);
            //             });

            //             if (rows.length === 0) {
            //                 $('#itemTable tbody').append($templateRow);
            //             }
            //         },
            //         error: function(xhr) {
            //             console.error(xhr.responseText || xhr.statusText);
            //             alert('Error loading indent details');
            //         }
            //     });
            // });

            $(document).on('click', '.btnEdit', function(e) {
                e.preventDefault();

                const indentId = $(this).data('id');
                if (!indentId) return;

                $('#btnSaveIndent').text('Update');
                $('#indent_id').val(indentId);

                $.ajax({
                    url: 'Ajax/Ajax-indent.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'view',
                        indent_id: indentId
                    },
                    success: function(res) {

                        if (res.status !== 'success' || !res.Data) {
                            alert(res.Msg || 'Could not load indent data.');
                            return;
                        }

                        const h = res.Data;
                        const rows = (h.items && h.items.length) ? h.items : (h.breakUp || []);

                        // =====================================================
                        //  CREATE CLEAN TEMPLATE ROW (NO SELECT2 DOM)
                        // =====================================================
                        let $templateRow = $('#itemTable tbody tr:first').clone(false);

                        //  DESTROY select2 if exists
                        if ($.fn.select2) {
                            $templateRow.find('.itemSelect').each(function() {
                                if ($(this).hasClass('select2-hidden-accessible')) {
                                    $(this).select2('destroy');
                                }
                            });
                        }

                        //  REMOVE leftover select2 containers
                        $templateRow.find('.select2').remove();

                        // Clear values
                        $templateRow.find('input, select').val('');
                        $templateRow.find('input[name="breakup_id[]"]').val('');
                        $templateRow.find('input[name="existing_attachment[]"]').val('');
                        $templateRow.find('.file-name').text('');

                        // Clear table body completely
                        $('#itemTable tbody').empty();

                        // =====================================================
                        // HEADER FIELDS
                        // =====================================================
                        $('#doc_no').val(h.DocNo || '');
                        $('#pdate').val(h.Date || '');
                        $('#narration').val(h.Narration || '');

                        setTimeout(() => {
                            if (h.DepartmentId) {
                                $('#department').val(h.DepartmentId).trigger('change');
                            } else if (h.Department) {
                                $("#department option").filter(function() {
                                    return $(this).text().trim() === h.Department.trim();
                                }).prop("selected", true).trigger("change");
                            }
                        }, 100);

                        // =====================================================
                        // FILL ITEM ROWS
                        // =====================================================
                        rows.forEach(row => {

                            let $newRow = $templateRow.clone(false);

                            // Breakup ID
                            const breakupId =
                                row.BreakUpId ||
                                row.breakup_id ||
                                row.BreakUpID ||
                                row.Id ||
                                row.id ||
                                '';

                            $newRow.find('input[name="breakup_id[]"]').val(breakupId);

                            // Fill fields
                            $newRow.find('.requested_qty').val(row.Quantity || row.ReceivedQuantity || 0);
                            $newRow.find('.purpose').val(row.Purpose || '');
                            $newRow.find('.remarks').val(row.Remarks || '');
                            $newRow.find('.uom').val(row.UoM || '');
                            $newRow.find('.avail_qty').val(row.AvailableQty || '');
                            $newRow.find('.item_price').val(row.ItemPrice || '');
                            $newRow.find('.base_value').val(row.BaseValue || 0);
                            $newRow.find('.gst').val(row.GST || 0);
                            $newRow.find('.invoice_amt').val(row.InvoiceAmount || 0);

                            // Attachment
                            // $newRow.append(
                            //     '<input type="hidden" name="existing_attachment[]" value="' + (row.Attachment || '') + '">'
                            // );

                            // if (row.Attachment) {
                            //     const fileName = row.Attachment.split('/').pop();
                            //     $newRow.find('.file-name').text(fileName);
                            // }

                            // set existing attachment (comma separated)
                            $newRow.append(
                                '<input type="hidden" name="existing_attachment[]" value="' + (row.Attachment || '') + '">'
                            );

                            if (row.Attachment) {
                                const fileNames = row.Attachment.split(',').map(x => x.split('/').pop());
                                $newRow.find('.file-name').text(fileNames.join(", "));
                            }

                            // Action button
                            $newRow.find('td:last').html(
                                '<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>'
                            );

                            // ✅ APPEND ROW FIRST
                            $('#itemTable tbody').append($newRow);

                            // =================================================
                            // ✅ INITIALIZE SELECT2 AFTER APPEND
                            // =================================================
                            if ($.fn.select2) {
                                $newRow.find('.itemSelect').select2({
                                    placeholder: "Select Item",
                                    width: "100%"
                                });
                            }

                            // Set selected item
                            if (row.GeneralItemId) {
                                $newRow.find('.itemSelect')
                                    .val(row.GeneralItemId)
                                    .trigger('change.select2');
                            }
                        });

                        // If no rows
                        if (!rows.length) {
                            $('#itemTable tbody').append($templateRow);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText || xhr.statusText);
                        alert('Error loading indent details');
                    }
                });
            });






            // Delete generalItem
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this generalItem?')) return;
                var generalItem_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/Ajax-indent.php',
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

            // Calculate Base Value and Invoice Amount on Requested Qty change
            $(document).on('input', '.requested_qty', function() {
                const row = $(this).closest('tr');
                const qty = parseFloat($(this).val());
                const price = parseFloat(row.find('.item_price').val());
                const gstPercent = parseFloat(row.find('.gst').val());

                const baseValue = qty * price;
                const invoiceAmt = baseValue + (baseValue * gstPercent / 100);

                row.find('.base_value').val(baseValue.toFixed(2));
                row.find('.invoice_amt').val(invoiceAmt.toFixed(2));
            });

            // Auto-fill data when item is selected
            $(document).on('change', '.itemSelect', function() {
                const itemId = $(this).val();
                const row = $(this).closest('tr');
                getAvailableDetails(itemId, row);
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
            //         url: 'Ajax/Ajax-indent.php',
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
            //                 row.find('.uom').val(data.Units || '');
            //                 row.find('.item_price').val(data.UnitPrice || '');
            //                 row.find('.gst').val(data.Gst || '');
            //                 row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
            //             } else {
            //                 // reset row if no data
            //                 row.find('input').val('');
            //                 row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
            //             }
            //         },
            //         error: function(xhr, status, err) {
            //             console.error('Error:', err);
            //         }
            //     });
            // }

            function getItemDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/Ajax-indent.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        item_id: itemId,
                        effective_date: $("#pdate").val(),
                        methodType: 'itemDetails'
                    },
                    success: function(res) {
                        console.log(res);
                        if (res && res.Data) {
                            const data = res.Data;

                            // ONLY update these fields
                            row.find('.uom').val(data.Units || '');

                            // ⚠️ DO NOT overwrite requested_qty, purpose, remarks
                        } else {
                            // Optional: only clear item-specific fields
                            row.find('.uom, .item_price, .gst').val('');
                        }
                    },
                    error: function(xhr, status, err) {
                        console.error('Error:', err);
                    }
                });
            }


            loadIndents();

        });

        $(document).on('click', '.btnViewIndent', function() {
            const indentId = $(this).data('id');


            $.ajax({
                url: 'Ajax/Ajax-indent.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    methodType: 'view',
                    indent_id: indentId
                },
                success: function(res) {
                    if (res.status === 'success' && res.Data) {
                        const h = res.Data;
                        const items = h.items || [];

                        let headerHTML = `
                    <div class="container-fluid mb-3">
                        <div class="row">
                            <div class="col-md-4"><strong>Doc No:</strong> ${h.DocNo || '-'}</div>
                            <div class="col-md-4"><strong>Date:</strong> ${
                                h.Date
                                    ? (() => {
                                        const d = new Date(h.Date);
                                        if (isNaN(d)) return h.Date;
                                        const day = String(d.getDate()).padStart(2, '0');
                                        const month = String(d.getMonth() + 1).padStart(2, '0');
                                        const year = d.getFullYear();
                                        return `${day}-${month}-${year}`;
                                    })()
                                    : '-'
                            }</div>
                            <div class="col-md-4"><strong>Department:</strong> ${h.Department || '-'}</div>
                            <div class="col-md-12"><strong>Narration:</strong> ${h.Narration || '-'}</div>
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
                                   
                                  
                                    <th>Requested Qty</th>
                                    
                                    <th>Purpose</th>
                                    <th>Remarks</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                ${
                                    items.length
                                    ? items.map(i => `
                                        <tr>
                                            <td>${i.ItemName || '-'}</td>
                                           
                                           
                                            <td>${i.Quantity || 0}</td>
                                            
                                            <td>${i.Purpose || '-'}</td>
                                            <td>${i.Remarks || '-'}</td>
                                           
                                        </tr>`).join('')
                                    : `<tr><td colspan="10" class="text-center text-muted">No items found</td></tr>`
                                }
                            </tbody>
                        </table>
                    </div>
                `;

                        $('#indentDetailsBody').html(headerHTML + tableHTML);
                        $('#indentViewModal').modal('show');
                    } else {
                        alert(res.Msg || 'No details found for this Indent.');
                    }
                },
                error: function() {
                    alert('Error loading Indent details.');
                }
            });
        });
    </script>

</body>

</html>