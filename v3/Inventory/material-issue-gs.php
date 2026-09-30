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


$User = "Material Issue General Stores";
$PageTitle = $User;
$PageTitleFolder = "Indent";


// $jsonData = '{
//     "scissors": [{"date":"10/10/2025","qty":10,"price":"35.00"},{"date":"10/09/2025","qty":15,"price":"33.00"}],
//     "gloves": [{"date":"8/10/2025","qty":10,"price":"99.00"},{"date":"8/09/2025","qty":15,"price":"80.00"}],
//     "screwdriver": [{"date":"5/10/2025","qty":10,"price":"20.00"},{"date":"5/09/2025","qty":15,"price":"18.00"}]
// }';

// $previousData = json_decode($jsonData, true);

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
            width: 310px;
            /* Item column wider */
        }

        #itemTable th:nth-child(2),
        #itemTable td:nth-child(2) {
            width: 100px;
            /* UoM */
        }

        #itemTable th:nth-child(3),
        #itemTable td:nth-child(3) {
            width: 100px;
            /* Available Qty */
        }

        #itemTable th:nth-child(4),
        #itemTable td:nth-child(4) {
            width: 100px;
            /* Item Price */
        }

        #itemTable th:nth-child(5),
        #itemTable td:nth-child(5) {
            width: 100px;
            /* Requested Qty */
        }

        #itemTable th:nth-child(6),
        #itemTable td:nth-child(6) {
            width: 100px;
            /* Base Value */
        }

        #itemTable th:nth-child(7),
        #itemTable td:nth-child(7) {
            width: 100px;
            /* GST% */
        }

        #itemTable th:nth-child(8),
        #itemTable td:nth-child(8) {
            width: 100px;
            /* Invoice Amount */
        }

        #itemTable th:nth-child(9),
        #itemTable td:nth-child(9) {
            width: 100px;
            /* Purpose - reduced */
        }

        #itemTable th:nth-child(10),
        #itemTable td:nth-child(10) {
            width: 100px;
            /* Attachment - reduced */
        }

        #itemTable th:nth-child(11),
        #itemTable td:nth-child(11) {
            width: 80px;
            /* Remarks */
        }

        #itemTable th:nth-child(12),
        #itemTable td:nth-child(12) {
            width: 100px;
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
                        <!-- <input type="hidden" id="indent_id" name="indent_id" value=""> -->
                        <input type="hidden" id="indent_id" name="indent_id" value="">



                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <?php
                                                // Example: IND-YYYYMMDD-XXXX (XXXX = random 4-digit number)
                                                $docNo = "IND-" . date('Ymd') . "-" . rand(1000, 9999);
                                                ?>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" value="<?php echo $docNo; ?>" readonly>
                                            </div> -->
                                            <div class="col-md-3">
                                                <label>Doc No</label>
                                                <select class="form-control" id="doc_no" name="doc_no">
                                                </select>
                                            </div>



                                            <div class="col-md-3">
                                                <label>Date</label>
                                                <input type="date" class="form-control" id="pdate" name="pdate" value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label>Location</label>
                                                <select id="location" name="location" class="form-control">
                                                    <option value="">Select Location</option>
                                                    <option value="1">A1</option>
                                                    <option value="2">B1</option>
                                                </select>
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
                                                <input type="text" class="form-control" id="narration" name="narration" placeholder="Enter Narration">
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
                                                    <th>Item Price</th>
                                                    <th>Batch</th>
                                                    <th>Quantity</th>
                                                    <th>Base Value</th>
                                                    <th>GST%</th>
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
                                                        <input type="hidden" class="breakUpId" name="breakUpId[]" value="">
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
                                                    <td><input type="text" class="form-control uom" name="uom[]" readonly></td>
                                                    <td><input type="number" class="form-control avail_qty" name="avail_qty[]" readonly></td>
                                                    <td><input type="number" class="form-control item_price" name="item_price[]" readonly></td>
                                                    <td><input type="text" class="form-control batch" name="batch[]" readonly></td>
                                                    <td><input type="number" class="form-control requested_qty" name="requested_qty[]" value="0.00" min="0"></td>
                                                    <td><input type="number" class="form-control base_value" name="base_value[]" value="0.00" readonly></td>
                                                    <td><input type="number" class="form-control gst" name="gst[]" readonly></td>
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

                                        <!-- Container where item data will appear -->
                                        <div id="itemDataList" style="margin-top:10px;"></div>

                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveIndent" class="btn btn-primary">Save</button>
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


    <!-- Material Issue View Modal -->
    <div class="modal fade" id="miViewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Material Issue Details</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>
                <div class="modal-body" id="miDetailsBody">
                    <!-- Details will be loaded dynamically -->
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div> -->
            </div>
        </div>
    </div>


    <?php include($IncludesPath . "vendor-scripts.php"); ?>

    <!-- DataTables + Buttons -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>



    <script>
        const previousData = <?php echo json_encode($previousData); ?>;
        $(document).on('change', '.itemSelect', function() {
            const selectedItem = $(this).val();
            const selectedName = $(this).find("option:selected").data("name");
            // const itemKey = selectedItem.toLowerCase(); // normalize to lowercase
            const itemData = previousData[selectedItem]; // get the relevant data array

            // Get the container
            const listContainer = $('#itemDataList');
            listContainer.empty(); // clear previous data

            if (!selectedItem) {
                // listContainer.html('<p style="color:#888;text-align:center;">Please select an item to view data.</p>');
                listContainer.html('<p style="color:#888;text-align:center;"></p>');
                return;
            }

            if (!itemData || itemData.length === 0) {
                listContainer.html(`<p style="color:red;text-align:center;">No previous data found for ( ${selectedName} ).</p>`);
                return;
            }

            // Build the HTML list
            let html = `
                <h5 style="margin-bottom:5px;text-align:center;">Last 5 Months ( ${selectedName} ) Indent Data</h5>
                <table class="table table-bordered table-sm" >
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Vendor</th>
                            <th>Requested Qty</th>
                            <th>PO Qty</th>
                            <th>Received Qty</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            itemData.forEach(entry => {
                html += `
                    <tr>
                        <td>${entry.date}</td>
                        <td>${entry.VendorName}</td>
                        <td>${entry.qty}</td>
                         <td>${entry.poqty}</td>
                        <td>${entry.reqty}</td>
                    </tr>
                `;
            });
            html += `
                    </tbody>
                </table>
            `;

            listContainer.html(html);
        });
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
            getIndents();

            function getIndents() {
                $.ajax({
                    url: 'Ajax/ajax-material-issue-gs.php',
                    type: 'POST',
                    data: {
                        methodType: 'indents'
                    },
                    success: function(res) {
                        console.log(res);
                        var list = "<option value=''>Select</option>";
                        if (res && res.Data) {
                            $.each(res.Data, function(index, value) {
                                list += "<option value=" + value.NoteId + ">" + value.DocNo + "</option>";
                            });
                        }

                        $('#doc_no').html(list).trigger('change.select2');
                    }
                });
            }

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
                    url: 'Ajax/ajax-material-issue-gs.php',
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
                        getAvailableDetails(itemId, row);
                    },
                    error: function(xhr, status, err) {
                        console.error('Error:', err);
                    }
                });
            }


            // $("#doc_no").on("change", function () {
            //     var supplier = $(this).find(':selected').data('supplier');
            //     $('#vendor_id').val(supplier).trigger('change');
            // });
            // Add new row
            $(document).on('click', '.btnAddRow', function() {
                var newRow = $('#itemTable tbody tr:first').clone();
                newRow.find('input').val('');
                newRow.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                newRow.find('td:last').html('<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>');
                $('#itemTable tbody').append(newRow);
            });

            // Remove row
            $(document).on('click', '.btnRemoveRow', function() {
                $(this).closest('tr').remove();
            });

            // Auto-fill data when item is selected
            $(document).on('change', '.itemSelect', function() {
                const itemName = $(this).val();
                const row = $(this).closest('tr');
                if (itemName && itemData[itemName]) {
                    row.find('.uom').val(itemData[itemName].uom);
                    row.find('.avail_qty').val(itemData[itemName].avail_qty);
                    row.find('.item_price').val(itemData[itemName].price);
                    row.find('.gst').val(itemData[itemName].gst);
                    row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                } else {
                    row.find('input').val('');
                    row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                }
            });


            // Show selected file name
            $(document).on('change', '.attachment', function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).closest('td').find('.file-name').text(fileName);
            });


            // Calculate Base Value and Invoice Amount on Requested Qty change
            $(document).on('input', '.requested_qty', function() {
                const row = $(this).closest('tr');
                const qty = parseFloat($(this).val()) || 0;
                const price = parseFloat(row.find('.item_price').val()) || 0;
                const gstPercent = parseFloat(row.find('.gst').val()) || 0;

                const baseValue = qty * price;
                const invoiceAmt = baseValue + (baseValue * gstPercent / 100);

                row.find('.base_value').val(baseValue.toFixed(2));
                row.find('.invoice_amt').val(invoiceAmt.toFixed(2));
            });

            // Save Indent
            // $('#btnSaveIndent').click(function() {
            //     var formData = new FormData($('#indentForm')[0]);
            //     formData.append('methodType', 'save');
            //     $.ajax({
            //         url: 'Ajax/ajax-material-issue-gs.php',
            //         type: 'POST',
            //         data: formData,
            //         processData: false,
            //         contentType: false,
            //         dataType: 'json',
            //         success: function(res) {
            //             if (res.status === 'success') {
            //                 alert(res.Msg);
            //                 $('#indentForm')[0].reset();
            //                 $('#itemTable tbody tr:gt(0)').remove();
            //                 $('#itemTable tbody tr:first input').val('');
            //                 $('#itemTable tbody tr:first .requested_qty, .base_value, .invoice_amt').val('0.00');
            //                 loadIndents();
            //             } else {
            //                 alert('Error: ' + res.msg);
            //             }
            //         },
            //         error: function() {
            //             alert('AJAX request failed!');
            //         }
            //     });
            // });


            $('#btnSaveIndent').click(function() {
                var formData = new FormData($('#indentForm')[0]);

                // Determine whether it's save or update
                var indentId = $('#indent_id').val();
                var methodType = indentId ? 'update' : 'save';

                formData.append('methodType', methodType);
                formData.append('indent_id', indentId); // ensure it is sent

                // Optional: log to debug
                console.log("MethodType:", methodType, "Indent ID:", indentId);

                $.ajax({
                    url: 'Ajax/ajax-material-issue-gs.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.Msg || 'Saved successfully!');
                            $('#indentForm')[0].reset();
                            $('#itemTable tbody tr:gt(0)').remove();
                            $('#itemTable tbody tr:first input, select').val('');
                            $('#itemTable tbody tr:first .requested_qty, .base_value, .invoice_amt').val('0.00');
                            $('#btnSaveIndent').text('Save');
                            loadIndents(); // reload data table
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    },
                    error: function(xhr, status, err) {
                        console.error(err);
                        alert('AJAX request failed!');
                    }
                });
            });



            function loadIndents(searchKey = '', loadAll = false) {
                $.ajax({
                    url: 'Ajax/ajax-material-issue-gs.php',
                    type: 'POST',
                    data: {
                        methodType: 'load',
                        searchKey: searchKey,
                        loadAll: loadAll
                    },
                    success: function(html) {

                        $('#indentListDiv').html(html);


                        setTimeout(() => {
                            let paginate = $(".dataTables_paginate");
                            if (paginate.find(".pagination-container").length === 0) {
                                paginate.wrapInner('<div class="pagination-container"></div>');
                            }
                        }, 300);
                        initializeDataTable();
                    }
                });
            }




            // ================= INITIALIZE DATATABLE WITH BUTTONS =================
            function initializeDataTable() {
                const tableId = "#tblDataTable";

                if (!$(tableId).length) return;

                if ($.fn.DataTable.isDataTable(tableId)) {
                    $(tableId).DataTable().clear().destroy();
                }

                setTimeout(() => {
                    const table = $(tableId).DataTable({
                        scrollY: "600px",
                        scrollX: true,
                        sScrollXInner: "100%",
                        paging: true,
                        pageLength: 25,
                        lengthMenu: [10, 25, 50, 100, -1],

                        scrollCollapse: true,
                        fixedColumns: false,

                        // PUT BUTTONS FIRST → Search box comes automatically AFTER this
                        dom: '<"d-flex justify-content-end mb-2" B>ftip',

                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btn btn-success ml-2',
                            title: 'Material Issue'
                        }],

                        initComplete: function() {
                            const filterBox = $(tableId + '_filter');

                            // 1) Search box already exists

                            // 2) More Search button
                            if (!$("#btnMoreSearch").length) {
                                $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                                    .insertAfter(filterBox.find('input'));
                            }

                            // 3) Load All button
                            if (!$("#btnLoadAll").length) {
                                $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                                    .insertAfter("#btnMoreSearch");
                            }

                            // 4) Move download button LAST
                            $('.buttons-excel')
                                .addClass('ml-2')
                                .appendTo(filterBox);
                        }
                    });
                }, 300);
            }





            // ================= MORE SEARCH & LOAD ALL CLICK =================
            $(document).on("click", "#btnMoreSearch", function() {
                const searchKey = $("#tblDataTable_filter input").val();
                loadIndents(searchKey, false);
            });

            $(document).on("click", "#btnLoadAll", function() {
                loadIndents("", true);
            });




            $(document).on('click', '.btnEdit', function() {
                const noteId = $(this).data('id');
                $('#btnSaveIndent').text('Update');
                $('#indent_id').val(noteId);

                $.ajax({
                    url: 'Ajax/ajax-material-issue-gs.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'view',
                        NoteId: noteId
                    },
                    success: function(res) {
                        if (res.status === 'success' && res.Data) {
                            const h = res.Data;
                            const $templateRow = $('#itemTable tbody tr:first').clone();
                            $('#itemTable tbody').html('');

                            // Fill main form
                            $('#doc_no').val(h.DocNo);
                            $('#pdate').val(h.Date);
                            $('#location').val(h.Location).trigger('change');
                            $('#department').val(h.Department).trigger('change');
                            $('#narration').val(h.Narration);

                            // Fill items
                            h.breakUp.forEach(item => {
                                const $newRow = $templateRow.clone();
                                $newRow.find('.breakUpId').val(item.BreakUpId || '');
                                $newRow.find('.itemSelect').val(item.GeneralItemId).trigger('change');
                                $newRow.find('.uom').val(item.Units);
                                $newRow.find('.avail_qty').val(item.AvailableQty);
                                $newRow.find('.item_price').val(item.UnitPrice);
                                $newRow.find('.batch').val(item.Batch || '');
                                $newRow.find('.requested_qty').val(item.Quantity);
                                $newRow.find('.base_value').val(item.BaseValue || 0);
                                $newRow.find('.gst').val(item.GST || 0);
                                $newRow.find('.invoice_amt').val(item.InvoiceAmt || 0);
                                $newRow.find('.purpose').val(item.Purpose || '');
                                $newRow.find('.remarks').val(item.Remarks || '');

                                if (item.Attachment) {
                                    $newRow.find('.file-name').text(item.Attachment.split('/').pop());
                                }

                                $('#itemTable tbody').append($newRow);

                                if ($.fn.select2) $newRow.find('.itemSelect').select2({
                                    placeholder: "Select Item",
                                    width: "100%"
                                });
                            });
                        } else {
                            alert('Record not found!');
                        }
                    }
                });
            });



            // Delete generalItem
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this generalItem?')) return;
                var generalItem_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/ajax-material-issue-gs.php',
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
                            loadIndents();
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


        // View Material Issue details
        $(document).on('click', '.btnView', function() {
            const noteId = $(this).data('id');

            $.ajax({
                url: 'Ajax/ajax-material-issue-gs.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    methodType: 'view',
                    NoteId: noteId
                },
                success: function(res) {
                    if (res.status === 'success' && res.Data) {
                        const h = res.Data;
                        const items = h.breakUp || [];

                        let headerHTML = `
          <div class="container-fluid mb-3">
            <div class="row">
              <div class="col-md-4"><strong>Doc No:</strong> ${h.DocNo || '-'}</div>
              <div class="col-md-4"><strong>Date:</strong> ${h.Date || '-'}</div>
              <div class="col-md-4"><strong>Location:</strong> ${h.Location || '-'}</div>
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
                  <th>UoM</th>
                  <th>Qty</th>
                  <th>Base Value</th>
                  <th>GST%</th>
                  <th>Invoice Amt</th>
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
                        <td>${i.Units || '-'}</td>
                        <td>${i.Quantity || 0}</td>
                        <td>${i.BaseValue || 0}</td>
                        <td>${i.GST || 0}</td>
                        <td>${i.InvoiceAmt || 0}</td>
                        <td>${i.Purpose || '-'}</td>
                        <td>${i.Remarks || '-'}</td>
                        <td>${i.Attachment ? `<a href="${i.Attachment}" target="_blank">View</a>` : '-'}</td>
                      </tr>
                    `).join('')
                    : `<tr><td colspan="9" class="text-center text-muted">No items found</td></tr>`
                }
              </tbody>
            </table>
          </div>
        `;

                        $('#miDetailsBody').html(headerHTML + tableHTML);
                        $('#miViewModal').modal('show');
                    } else {
                        alert(res.Msg || 'No details found for this Material Issue.');
                    }
                },
                error: function() {
                    alert('Error loading Material Issue details.');
                }
            });
        });
    </script>

</body>

</html>