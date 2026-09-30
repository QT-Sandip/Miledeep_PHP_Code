<?php
$Status = isset($_SESSION['Status']) ? $_SESSION['Status'] : '';
// ====================== CONFIG ======================
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

// Fallbacks in case variables are not set
$IncludesPath = isset($IncludesPath) ? $IncludesPath : $_SERVER['DOCUMENT_ROOT'] . '/includes/';
$href = isset($href) ? $href : '/assets/css/styles.css';

$User = "Proforma Register";
$PageTitle = $User . " Create";
$PageTitleFolder = "Reports";

// Dropdown options
$countries = ["India", "USA", "Germany", "Japan", "China", "France"];
$processBy = ["Factory A", "Factory B", "Outsource Partner"];
$ports = ["Chennai Port", "Mumbai Port", "Cochin Port", "Visakhapatnam Port"];
$isGlazeOptions = ["Yes", "No"];
$buyers = ["ABC Corp", "XYZ Exports", "Global Traders", "Oceanic Buyers"];
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

    <div id="layout-wrapper">
        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

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

                    <!-- ================= PROFORMA REGISTER CREATE FORM ================= -->
                    <form id="proformaCreateForm" enctype="multipart/form-data">
                        <input type="hidden" id="proforma_id" name="proforma_id" value="">

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="mb-3">Proforma Register - Create</h4>

                                        <div class="row">
                                            <!-- Shipment Date -->
                                            <div class="col-md-6">
                                                <label>Shipment Date</label>
                                                <input type="date" class="form-control" id="shipment_date" name="shipment_date" required>
                                            </div>

                                            <!-- PRO No -->
                                            <div class="col-md-6">
                                                <label>PRO No</label>
                                                <input type="text" class="form-control" id="pro_no" name="pro_no" placeholder="Enter PRO No" required>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Country of Origin -->
                                            <div class="col-md-6">
                                                <label>Country of Origin</label>
                                                <select class="form-control" id="country_origin" name="country_origin" required>
                                                    <option value="">Select Country</option>
                                                    <?php foreach ($countries as $country): ?>
                                                        <option value="<?= $country ?>"><?= $country ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Country of Final Destination -->
                                            <div class="col-md-6">
                                                <label>Country of Final Destination</label>
                                                <select class="form-control" id="country_destination" name="country_destination" required>
                                                    <option value="">Select Country</option>
                                                    <?php foreach ($countries as $country): ?>
                                                        <option value="<?= $country ?>"><?= $country ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Final Destination -->
                                            <div class="col-md-6">
                                                <label>Final Destination</label>
                                                <select class="form-control" id="final_destination" name="final_destination" required>
                                                    <option value="">Select Destination</option>
                                                    <option value="Chennai">Chennai</option>
                                                    <option value="Mumbai">Mumbai</option>
                                                    <option value="Cochin">Cochin</option>
                                                    <option value="Delhi">Delhi</option>
                                                </select>
                                            </div>

                                            <!-- Port of Discharge -->
                                            <div class="col-md-6">
                                                <label>Port of Discharge</label>
                                                <select class="form-control" id="port_discharge" name="port_discharge" required>
                                                    <option value="">Select Port</option>
                                                    <?php foreach ($ports as $port): ?>
                                                        <option value="<?= $port ?>"><?= $port ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Purchase Order Attachment -->
                                            <div class="col-md-6">
                                                <label>Purchase Order Attachment</label>
                                                <!-- File input for new upload -->
                                                <input type="file" class="form-control" id="po_attachment" name="po_attachment" accept=".pdf,.doc,.jpg,.png">

                                                <!-- Hidden input to store old attachment path -->
                                                <input type="hidden" id="old_POAttachment" name="old_POAttachment" value="">

                                                <!-- Preview / link to existing file -->
                                                <div id="po_attachment_preview" class="mt-1"></div>
                                            </div>


                                            <!-- PO Approval Date -->
                                            <div class="col-md-6">
                                                <label>PO Approval Date</label>
                                                <input type="date" class="form-control" id="po_approval_date" name="po_approval_date">
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Process and Packed By -->
                                            <div class="col-md-6">
                                                <label>Process and Packed By</label>
                                                <select class="form-control" id="process_packed_by" name="process_packed_by">
                                                    <option value="">Select Processor</option>
                                                    <?php foreach ($processBy as $p): ?>
                                                        <option value="<?= $p ?>"><?= $p ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Is Glaze -->
                                            <div class="col-md-6">
                                                <label>Is Glaze</label>
                                                <select class="form-control" id="is_glaze" name="is_glaze">
                                                    <option value="">Select Option</option>
                                                    <?php foreach ($isGlazeOptions as $opt): ?>
                                                        <option value="<?= $opt ?>"><?= $opt ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Delivery Terms -->
                                            <div class="col-md-6">
                                                <label>Delivery Terms</label>
                                                <input type="text" class="form-control" id="delivery_terms" name="delivery_terms" placeholder="Enter Delivery Terms">
                                            </div>

                                            <!-- Buyers (Other than Consignee) -->
                                            <div class="col-md-6">
                                                <label>Buyers (Other than Consignee)</label>
                                                <select class="form-control" id="buyer_other" name="buyer_other">
                                                    <option value="">Select Buyer</option>
                                                    <?php foreach ($buyers as $b): ?>
                                                        <option value="<?= $b ?>"><?= $b ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Amount in Words -->
                                            <div class="col-md-6">
                                                <label>Amount in Words</label>
                                                <input type="text" class="form-control" id="amount_words" name="amount_words" placeholder="Enter Amount in Words">
                                            </div>

                                            <!-- Advance Amount -->
                                            <div class="col-md-6">
                                                <label>Advance Amount</label>
                                                <input type="number" class="form-control" id="advance_amount" name="advance_amount" placeholder="Enter Advance Amount">
                                            </div>
                                        </div>


                                        <div class="row mt-3">
                                            <!-- Total Quantity -->
                                            <div class="col-md-6">
                                                <label>Total Quantity</label>
                                                <input type="number" class="form-control" id="total_quantity" name="total_quantity" placeholder="Enter Total Quantity">
                                            </div>

                                            <!-- Commission -->
                                            <div class="col-md-6">
                                                <label>Commission (%)</label>
                                                <input type="number" step="0.01" class="form-control" id="commission" name="commission" placeholder="Enter Commission %">
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Commission Value -->
                                            <div class="col-md-6">
                                                <label>Commission Value</label>
                                                <input type="number" step="0.01" class="form-control" id="commission_value" name="commission_value" placeholder="Enter Commission Value">
                                            </div>

                                            <!-- Approved By -->
                                            <div class="col-md-6">
                                                <label>Approved By</label>
                                                <input type="text" class="form-control" id="approved_by" name="approved_by" placeholder="Enter Approver Name">
                                            </div>
                                        </div>


                                        <!-- ================= BUTTONS ================= -->
                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveProforma" class="btn btn-primary">Save</button>
                                            <button type="button" id="btnUpdateProforma" class="btn btn-primary" style="display:none;">
                                                <i class="fa fa-edit"></i> Update
                                            </button>
                                        </div>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>

                    <div id="proformaListDiv" class="mt-4"></div>

                </div> <!-- container-fluid -->
                <?php include($IncludesPath . "footer.php"); ?>
            </div> <!-- page-content -->
        </div> <!-- main-content -->

    </div> <!-- END layout-wrapper -->


    <div class="modal fade" id="proformaViewModal" tabindex="-1" aria-labelledby="proformaViewLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="proformaViewLabel">Proforma Details</h5>
                       <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                    <!-- <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> -->
                </div>
                <div class="modal-body" id="proformaDetailsBody">
                    <!-- Details will load here -->
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

    <!-- <script>
        $(document).ready(function() {
            loadProformas();

            $('#btnSaveProforma').click(function(e) {
                e.preventDefault();

                var formData = new FormData($('#proformaCreateForm')[0]);
                formData.append('methodType', 'save');

                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegisterCreate.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadProformas();
                            $('#proformaCreateForm')[0].reset();
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Save request failed!');
                    }
                });
            });

            function loadProformas() {
                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegisterCreate.php',
                    type: 'POST',
                    data: {
                        methodType: 'load'
                    },
                    success: function(html) {
                        $('#proformaListDiv').html(html);
                    }
                });
            }
        });
    </script> -->

    <script>
        $(document).ready(function() {

            // ================= LOAD EXISTING PROFORMAS =================
            loadProformas();

            // ================= AUTO CALCULATE COMMISSION VALUE =================
            $('#commission, #advance_amount').on('input', function() {
                var commission = parseFloat($('#commission').val()) || 0;
                var amount = parseFloat($('#advance_amount').val()) || 0;
                var value = (commission / 100) * amount;
                $('#commission_value').val(value.toFixed(2));
            });

            // ================= SAVE OR UPDATE BUTTON CLICK =================
            $('#btnSaveProforma').click(function(e) {
                e.preventDefault();

                var mode = $(this).data('mode') || 'save';
                var formData = new FormData($('#proformaCreateForm')[0]);
                formData.append('methodType', mode);

                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegisterCreate.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadProformas();
                            $('#proformaCreateForm')[0].reset();
                            $('#btnSaveProforma').text('Save').data('mode', 'save'); // Reset button
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Save/Update request failed!');
                    }
                });
            });

            // ================= LOAD PROFORMA LIST =================
            function loadProformas() {
                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegisterCreate.php',
                    type: 'POST',
                    data: {
                        methodType: 'load'
                    },
                    success: function(html) {
                        $('#proformaListDiv').html(html);
                    },
                    error: function() {
                        alert('Failed to load Proforma list!');
                    }
                });
            }

            // ================= EDIT BUTTON CLICK =================
            $(document).on('click', '.btnEdit', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegisterCreate.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        proforma_id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            var p = res.data;
                            $('#proforma_id').val(p.ProformaID);
                            $('#pro_no').val(p.ProNo);
                            $('#shipment_date').val(p.ShipmentDate);
                            $('#country_origin').val(p.CountryOrigin);
                            $('#country_destination').val(p.CountryDestination);
                            $('#final_destination').val(p.FinalDestination);
                            $('#port_discharge').val(p.PortDischarge);
                            $('#po_approval_date').val(p.POApprovalDate);
                            $('#process_packed_by').val(p.ProcessPackedBy);
                            $('#is_glaze').val(p.IsGlaze);
                            $('#delivery_terms').val(p.DeliveryTerms);
                            $('#buyer_other').val(p.BuyerOther);
                            $('#amount_words').val(p.AmountWords);
                            $('#advance_amount').val(p.AdvanceAmount);

                            // OLD attachment handling
                            $('#old_POAttachment').val(p.POAttachment); // store old file path
                            if (p.POAttachment) {
                                $('#po_attachment_preview').html('<a href="' + p.POAttachment + '" target="_blank">View Current File</a>');
                            } else {
                                $('#po_attachment_preview').html('');
                            }

                            $('#total_quantity').val(p.TotalQuantity);
                            $('#commission').val(p.Commission);
                            $('#commission_value').val(p.CommissionValue);
                            $('#approved_by').val(p.ApprovedBy);

                            // switch button to update mode
                            $('#btnSaveProforma').text('Update').data('mode', 'update');
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Failed to fetch Proforma details!');
                    }
                });
            });


            // ================= DELETE BUTTON CLICK =================
            $(document).on('click', '.btnDelete', function() {
                var id = $(this).data('id');
                if (confirm('Are you sure you want to delete this Proforma?')) {
                    $.ajax({
                        url: 'Ajax/Ajax-PorformaRegisterCreate.php',
                        type: 'POST',
                        data: {
                            methodType: 'delete',
                            proforma_id: id
                        },
                        dataType: 'json',
                        success: function(res) {
                            alert(res.msg);
                            if (res.status === 'success') loadProformas();
                        },
                        error: function() {
                            alert('Delete request failed!');
                        }
                    });
                }
            });


             $(document).on('click', '.btnView', function() {
                const proformaId = $(this).data('id');

                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegisterCreate.php', // adjust path if needed
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'getSingle',
                        proforma_id: proformaId
                    },
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            let proforma = res.data;

                            const excludedFields = [
                                'CreatedDate',
                                'UpdatedDate'
                            ];

                            let html = `
                    <div class="container-fluid">
                        <div class="row">
                            ${Object.entries(proforma)
                                .filter(([key]) => !excludedFields.includes(key))
                                .map(([key, value]) => `
                                    <div class="col-md-3 mb-3">
                                        <div class="border rounded p-3 shadow-sm bg-light" 
                                             style="word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                            <h6 class="text-muted mb-1" style="text-transform: capitalize;">
                                                ${key.replace(/_/g, ' ')}
                                            </h6>
                                            <p class="fw-semibold mb-0 text-dark">${value ?? '-'}</p>
                                        </div>
                                    </div>
                                `).join('')}
                        </div>
                    </div>
                `;

                            $('#proformaDetailsBody').html(html);
                            $('#proformaViewModal').modal('show');
                        } else {
                            alert(res.msg || 'Failed to fetch proforma details.');
                        }
                    },
                    error: function() {
                        alert('Error loading proforma details.');
                    }
                });
            });


        });

      
    </script>


</body>

</html>