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


$User = "Material Issue General Stores";
$PageTitle = $User;
$PageTitleFolder = "Indent";


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
        #itemTable {
            table-layout: fixed;
            min-width: 2220px;
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



        /* Adjust column widths */
        #itemTable th:nth-child(1),
        #itemTable td:nth-child(1) {
            width: 120px;
            /* Item column wider */
        }

        #itemTable th:nth-child(2),
        #itemTable td:nth-child(2) {
            width: 250px;
            /* UoM */
        }

        #itemTable th:nth-child(3),
        #itemTable td:nth-child(3) {
            width: 150px;
            /* Available Qty */
        }

        #itemTable th:nth-child(4),
        #itemTable td:nth-child(4) {
            width: 150px;
            /* Item Price */
        }

        #itemTable th:nth-child(5),
        #itemTable td:nth-child(5) {
            width: 150px;
            /* Requested Qty */
        }

        #itemTable th:nth-child(6),
        #itemTable td:nth-child(6) {
            width: 180px;
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
            width: 100px;
            /* Remarks */
        }

        #itemTable th:nth-child(12),
        #itemTable td:nth-child(12) {
            width: 100px;
            /* Action button */
        }

        #itemTable th:nth-child(13),
        #itemTable td:nth-child(13) {
            width: 100px;
            /* Base Value */
        }

        #itemTable th:nth-child(14),
        #itemTable td:nth-child(14) {
            width: 100px;
            /* GST% */
        }

        #itemTable th:nth-child(15),
        #itemTable td:nth-child(15) {
            width: 100px;
            /* Invoice Amount */
        }

        #itemTable th:nth-child(16),
        #itemTable td:nth-child(16) {
            width: 100px;
            /* Purpose - reduced */
        }

        #itemTable th:nth-child(17),
        #itemTable td:nth-child(17) {
            width: 100px;
            /* Attachment - reduced */
        }

        #itemTable th:nth-child(18),
        #itemTable td:nth-child(18) {
            width: 80px;
            /* Remarks */
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
                                                <input type="date" class="form-control" id="pdate" name="pdate"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label>PRO Number</label>
                                                <select class="form-control" id="pro" name="pro">
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
                                        <div style="overflow-x:auto; width:100%;">
                                            <table class="table table-bordered" id="itemTable">
                                                <thead>
                                                    <tr>
                                                        <th>Warehouse</th>
                                                        <th>Item</th>
                                                        <th>Grade</th>
                                                        <th>Packing Style</th>
                                                        <th>Process Type</th>
                                                        <th>Brand</th>
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

                                                        <td style="display:none;">
                                                            <input type="hidden" class="BreakUpId" name="BreakUpId[]" value="">
                                                        </td>

                                                        <td><input type="text" class="form-control location"
                                                                name="location[]" value=""></td>
                                                        <td>
                                                            <input type="input" class="form-control txtVariety"
                                                                name="txtVariety[]"
                                                                placeholder="Click to Select Variety" readonly />
                                                            <input type="hidden" name="txtVarietyID[]"
                                                                class="txtVarietyID">
                                                        </td>
                                                        <td>
                                                            <select class="drpSelect form-control grade" name="grade[]"
                                                                >
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
                                                                name="packing_style[]" readonly>
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
                                                            <select class="drpSelect form-control brand" name="brand[]"
                                                                readonly>
                                                                <option value=""> Select</option>
                                                                <?php foreach ($resBrand as $item) { ?>
                                                                    <option value="<?php echo $item->BrandID; ?>">
                                                                        <?php echo $item->BrandName ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td><input type="text" class="form-control uom" name="uom[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control avail_qty"
                                                                name="avail_qty[]" readonly></td>
                                                        <td><input type="number" class="form-control item_price"
                                                                name="item_price[]" readonly></td>
                                                        <td><input type="text" class="form-control batch" name="batch[]"
                                                                readonly></td>
                                                        <td><input type="number" class="form-control requested_qty"
                                                                name="requested_qty[]" value="0.00" min="0"></td>
                                                        <td><input type="number" class="form-control base_value"
                                                                name="base_value[]" value="0.00" readonly></td>
                                                        <td><input type="number" class="form-control gst" name="gst[]"
                                                                readonly></td>
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









    <div class="modal fade" id="viewMaterialModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Material Issue - View</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="viewMaterialContent"></div>
                </div>

            </div>
        </div>
    </div>


    <?php include($IncludesPath . "vendor-scripts.php"); ?>

    <!-- DataTables CSS -->
    <!-- DataTables CSS -->
    <link rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <!-- DataTables Buttons Bootstrap 5 CSS -->
    <link rel="stylesheet"
        href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- DataTables core JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <!-- Excel dependency -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>






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

            var selectedVarietyID = $(this).attr("data-varietyid");
            var typeproduct = $(this).attr("data-type");
            var description = $(this).attr("data-description");
            var freezing = $(this).attr("data-freezing");
            var packingstyle = $(this).attr("data-packingstyle");
            var grade = $(this).attr("data-grade");
            var brand = $(this).attr("data-brand");

            $(".txtVariety").val(selectedVariety)
                .attr("data-varietyid", selectedVarietyID);
            $(".grade").val(grade);
            $(".freezing").val(freezing);
            $(".packing_style").val(packingstyle);
            $(".brand").val(brand);
            $(".description").val(description);


            // Hide the modal
            $('#modalVarietyList').modal('hide');
            // Optionally, call other functions like getGrades() or update UI
        });


        const previousData = <?php echo json_encode($previousData); ?>;
        $(document).on('change', '.itemSelect', function() {
            const selectedItem = $(this).val();
            const itemKey = selectedItem.toLowerCase(); // normalize to lowercase
            const itemData = previousData[itemKey]; // get the relevant data array

            // Get the container
            const listContainer = $('#itemDataList');
            listContainer.empty(); // clear previous data

            if (!selectedItem) {
                // listContainer.html('<p style="color:#888;text-align:center;">Please select an item to view data.</p>');
                listContainer.html('<p style="color:#888;text-align:center;"></p>');
                return;
            }

            if (!itemData || itemData.length === 0) {
                listContainer.html(`<p style="color:red;text-align:center;">No previous data found for ${selectedItem}.</p>`);
                return;
            }

            // Build the HTML list
            let html = `
                <h5 style="margin-bottom:5px;text-align:center;">Last 5 Months ${selectedItem} Indent Data</h5>
                <table class="table table-bordered table-sm" >
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            itemData.forEach(entry => {
                html += `
                    <tr>
                        <td>${entry.date}</td>
                        <td>${entry.qty}</td>
                        <td>${entry.price}</td>
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
                    url: 'Ajax/ajax-material-issue-packaging.php',
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
                        $('#pro').html(list).trigger('change.select2');
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
                    url: 'Ajax/ajax-material-issue-packaging.php',
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





            

            $('#btnSaveIndent').click(function(e) {
                e.preventDefault(); // Prevent default form submission

                // Enable disabled selects so their values are submitted
                $('#itemTable select:disabled').prop('disabled', false);

                // Collect form data
                var formData = new FormData($('#indentForm')[0]);
                var materialId = $('#indent_id').val();

                if (materialId && materialId != "0") {
                    formData.append('methodType', 'update');
                    formData.append('indent_id', materialId);
                } else {
                    formData.append('methodType', 'save');
                }

                // AJAX call
                $.ajax({
                    url: 'Ajax/ajax-material-issue-packaging.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.Msg);
                            // Refresh the whole page to reflect changes
                            location.reload();
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.responseText);
                        alert('AJAX request failed! Check console.');
                    }
                });
            });







            // function loadIndents() {
            //     $.ajax({
            //         url: 'Ajax/ajax-material-issue-packaging.php',
            //         type: 'POST',
            //         data: {
            //             methodType: 'load'
            //         },
            //         success: function(html) {
            //             $('#indentListDiv').html(html);
            //         }
            //     });
            // }


            // Load indents via AJAX
            function loadIndents() {
                $.ajax({
                    url: 'Ajax/ajax-material-issue-packaging.php',
                    type: 'POST',
                    data: {
                        methodType: 'load'
                    },
                    success: function(html) {
                        $('#indentListDiv').html(html);

                        // Init DataTable AFTER HTML is injected
                        setTimeout(function() {
                            initMaterialDataTable();
                        }, 100);
                    },
                    error: function(xhr, status, error) {
                        console.error("Failed to load indents:", error);
                    }
                });
            }


            // Initialize DataTable with buttons
            function initMaterialDataTable() {

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
                        title: 'Material Issue List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // Inject buttons near search box (SAME AS SAMPLE)
                setTimeout(function() {

                    if (!$("#btnMoreSearchMaterial").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearchMaterial">More Search</button>')
                            .insertAfter("#tblDataTable_filter label");
                    }

                    if (!$("#btnLoadAllMaterial").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAllMaterial">Load All</button>')
                            .insertAfter("#btnMoreSearchMaterial");
                    }

                }, 200);
            }


            // MORE SEARCH
            $(document).on("click", "#btnMoreSearchMaterial", function() {
                loadIndents();
            });

            // LOAD ALL
            $(document).on("click", "#btnLoadAllMaterial", function() {
                loadIndents();
            });









            $(document).on('click', '.btnViewMaterial', function() {
                const materialId = $(this).data('id');

                if (!materialId) return alert("Invalid Material Issue selected.");

                $.ajax({
                    url: 'Ajax/ajax-material-issue-packaging.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'viewMaterial',
                        MaterialIssueId: materialId
                    },
                    success: function(res) {
                        if (res.status === 'success' && res.Data) {
                            renderMaterialViewModal(res.Data);
                            $('#viewMaterialModal').modal('show');
                        } else {
                            alert(res.Msg || 'No data found');
                        }
                    },
                    error: function(xhr, status, err) {
                        console.error('AJAX Error:', err);
                        alert('Failed to fetch material data. Check console.');
                    }
                });
            });

            function renderMaterialViewModal(data) {

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
                <div class="col-md-3"><strong>PRO Number:</strong></div>
                <div class="col-md-3">${data.ProNo || '-'}</div>
                <div class="col-md-3"><strong>Narration:</strong></div>
                <div class="col-md-3">${data.Narration || '-'}</div>
            </div>
        </div>
    `;

                /* ================= BREAKUP TABLE ================= */
                let tableHTML = `
        <h6 class="mt-3">Item Breakup</h6>
        <table class="table table-bordered table-sm">
            <thead class="table-primary">
                <tr>
                    <th>Warehouse</th>
                    <th>Variety</th>
                    <th>Packing Style</th>
                    <th>Process Type </th>
                    <th>Brand</th>
                    <th>Quantity</th>
                    <th>Purpose</th>
                    <th>Attachment</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
    `;

                if (Array.isArray(data.breakUp) && data.breakUp.length > 0) {

                    data.breakUp.forEach(item => {
                        tableHTML += `
                <tr>
                    <td>${item.Warehouse || '-'}</td>
                    <td>${item.VarietyName || item.VarietyID || '-'}</td>
                    <td>${item.PackingStyleName || item.PackingStyleID || '-'}</td>
                    <td>${item.freezing || '-'}</td>
                    <td>${item.BrandName || item.Brand || '-'}</td>
                    <td>${item.Quantity || 0}</td>
                    <td>${item.Purpose || '-'}</td>
                    <td>
                        ${item.Attachment 
                            ? `<a href="${item.Attachment}" target="_blank">View</a>` 
                            : '-'}
                    </td>
                    <td>${item.Remarks || '-'}</td>
                </tr>
            `;
                    });

                } else {
                    tableHTML += `
            <tr>
                <td colspan="8" class="text-center">No items</td>
            </tr>
        `;
                }

                tableHTML += `</tbody></table>`;

                $('#viewMaterialContent').html(headerHTML + tableHTML);
            }









            $(document).on('click', '.btnEdit', function() {

                const materialIssueId = $(this).data('id');
                if (!materialIssueId) {
                    alert('Invalid Material Issue selected');
                    return;
                }

                $('#btnSaveIndent').text('Update');
                $('#indent_id').val(materialIssueId);

                $.ajax({
                    url: 'Ajax/ajax-material-issue-packaging.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'viewMaterial',
                        MaterialIssueId: materialIssueId
                    },
                    success: function(res) {

                        if (!res || res.status !== 'success') {
                            alert(res?.Msg || 'Failed to load Material Issue');
                            return;
                        }

                        const data = res.Data;
                        const breakups = Array.isArray(data.breakUp) && data.breakUp.length ?
                            data.breakUp : [{}];

                        /* ================= HEADER ================= */

                        $('#doc_no').val(data.DocNo || '');
                        $('#pdate').val(data.Date || '');
                        $('#pro').val(data.ProNo || '').trigger('change.select2');
                        $('#narration').val(data.Narration || '');

                        /* ================= CLEAR TABLE ================= */

                        const $tbody = $('#itemTable tbody');
                        const $templateRow = $tbody.find('tr:first').clone();

                        $tbody.empty();

                        /* ================= BREAKUP ================= */

                        breakups.forEach(item => {

                            const $row = $templateRow.clone();

                            $row.find('.BreakUpId').val(item.BreakUpId || 0);
                            $row.find('.location').val(item.Warehouse || '');

                            $row.find('.txtVariety').val(item.VarietyName || '');
                            $row.find('.txtVarietyID').val(item.VarietyID || '');

                          $row.find('.grade').val(item.Grade).trigger('change.select2');
                          $row.find('.freezing').val(item.freezing).trigger('change.select2');

                            $row.find('.requested_qty').val(item.Quantity || 0);
                            $row.find('.purpose').val(item.Purpose || '');
                            $row.find('.remarks').val(item.Remarks || '');

                            //  NORMAL SELECT SET (NO trigger, NO select2)
                            $row.find('.packing_style').val(item.PackingStyleID || '');
                            $row.find('.brand').val(item.Brand || '');

                            $tbody.append($row);
                        });

                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('AJAX Error: ' + error);
                    }
                });
            });









            // Delete generalItem
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this generalItem?')) return;
                var generalItem_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/ajax-material-issue-packaging.php',
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
    </script>

</body>

</html>