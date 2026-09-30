<?php
$Status = $_SESSION['Status'];
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$User = "Vendor Master";
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

                    <form id="vendorForm">
                        <input type="hidden" id="vendor_id" name="vendor_id" value="">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <!-- ================= GENERAL DETAILS ================= -->
                                        <h4 class="mb-3">General Details</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Vendor Name</label>
                                                <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="Enter Name">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Vendor Code</label>
                                                <input type="text" class="form-control" id="vendor_code" name="vendor_code" placeholder="Enter Code">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Account Type</label>
                                                <select id="account_type" name="account_type" class="form-control">
                                                    <option value="">Select Account Type</option>
                                                    <option value="Credit">Credit</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label>Credit Limit</label>
                                                <input type="number" class="form-control" id="credit_limit" name="credit_limit" placeholder="Enter Credit Limit">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Credit Days</label>
                                                <input type="number" class="form-control" id="credit_days" name="credit_days" placeholder="Enter Credit Days">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Group</label>
                                                <select id="group" name="group" class="form-control">
                                                    <option value="">Select Group</option>
                                                    <option value="General">General</option>
                                                    <option value="Packing">Packing</option>
                                                    <option value="Chemical">Chemical</option>
                                                    <option value="Production">Production</option>
                                                    <option value="Machinery">Machinery</option>
                                                    <option value="Lab">Lab</option>
                                                    <option value="Civil">Civil</option>
                                                    <option value="Stationery">Stationery</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label>Cheque Discount Limit</label>
                                                <input type="text" class="form-control" id="cheque_discount_limit" name="cheque_discount_limit" placeholder="Enter Cheque Discount Limit">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Rate of Interest</label>
                                                <input type="text" class="form-control" id="rate_of_interest" name="rate_of_interest" placeholder="Enter Rate of Interest">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>PDC Discounted A/C</label>
                                                <input type="text" class="form-control" id="pdc_discounted_ac" name="pdc_discounted_ac" placeholder="Enter PDC Discounted A/C">
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label>Bank A/C</label>
                                                <select id="bank_ac" name="bank_ac" class="form-control">
                                                    <option value="">Select Bank Account</option>
                                                    <option value="Bank1">Bank 1</option>
                                                    <option value="Bank2">Bank 2</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Item Desc</label>
                                                <input type="text" class="form-control" id="item_desc" name="item_desc" placeholder="Enter Item Description">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Bank Branch Name</label>
                                                <input type="text" class="form-control" id="bank_branch" name="bank_branch" placeholder="Enter Bank Branch Name">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Cash Flow Classification</label>
                                                <input type="text" class="form-control" id="cash_flow_classification" name="cash_flow_classification" placeholder="Enter Cash Flow Classification">
                                            </div>
                                        </div>
                                        <hr>

                                        <!-- ================= SETTINGS ================= -->
                                        <h4 class="mt-4 mb-3" style="display:none">Settings</h4>
                                        <div class="row" style="display:none">
                                            <?php
                                            $settingsFields = [
                                                "Debit/Credit proposal",
                                                "Debit/Credit Required",
                                                "Exchange Adjustment Gain A/C",
                                                "Exchange Adjustment Loss A/C",
                                                "Primary account",
                                                "Default currency",
                                                "Consolidation method",
                                                "Payment terms",
                                                "Reminder terms",
                                                "Finance charge terms"
                                            ];
                                            foreach ($settingsFields as $field) {
                                                // $fieldName = str_replace([' ', '/'], '_', strtolower($field));
                                                $fieldName = strtolower(str_replace([' ', '/', '&'], '_', $field));
                                                $fieldName = preg_replace('/_+/', '_', $fieldName);
                                                $fieldName = str_replace('_a_c', '_ac', $fieldName);


                                                echo '
    <div class="col-md-4 mt-3">
        <label>' . $field . '</label>
        <select class="form-control" name="' . $fieldName . '" id="' . $fieldName . '">
            <option value="">Select ' . $field . '</option>
            <option value="Option1">Option 1</option>
            <option value="Option2">Option 2</option>
        </select>
    </div>';
                                            }
                                            ?>
                                        </div>


                                        <!-- ================= VENDOR DETAILS ================= -->
                                        <h4 class="mt-4 mb-3">Vendor Details</h4>
                                        <div class="row">

                                            <!-- <div class="col-md-4">
                                                <label>Vendor Name</label>
                                                <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="Enter Vendor Name">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Vendor Code</label>
                                                <input type="text" class="form-control" id="vendor_code" name="vendor_code" placeholder="Enter Vendor Code">
                                            </div> -->
                                            <div class="col-md-4 mt-3">
                                                <label>Address</label>
                                                <input type="text" class="form-control" id="address" name="address" placeholder="Enter Address">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Tel No</label>
                                                <input type="text" class="form-control" id="tel_no" name="tel_no" placeholder="Enter Telephone No">
                                            </div>
                                            <div class="col-md-4  mt-3" style="display: none;">
                                                <label>Qty</label>
                                                <select id="qty" name="qty" class="form-control">
                                                    <option value="">Select Qty Type</option>
                                                    <option value="Nos">Nos</option>
                                                    <option value="Pairs">Pairs</option>
                                                    <option value="Kgs">Kgs</option>
                                                    <option value="Liters">Liters</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label>Fax No</label>
                                                <input type="text" class="form-control" id="fax_no" name="fax_no" placeholder="Enter Fax No">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Delivery Address</label>
                                                <input type="text" class="form-control" id="delivery_address" name="delivery_address" placeholder="Enter Delivery Address">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Pin</label>
                                                <input type="text" class="form-control" id="pin" name="pin" placeholder="Enter PIN Code">
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label>Email</label>
                                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
                                            </div>
                                            <!-- <div class="col-md-4 mt-3">
                                                <label>Password</label>
                                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" autocomplete="new-password">
                                            </div> -->

                                            <div class="col-md-4 mt-3">
                                                <label>Bank Account Name</label>
                                                <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" placeholder="Enter Bank Account Name" autocomplete="off">
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label>Bank Account Number</label>
                                                <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" placeholder="Enter Bank Account Number">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>IFSC Code</label>
                                                <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="Enter IFSC Code">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Payment Type</label>
                                                <select id="payment_type" name="payment_type" class="form-control">
                                                    <option value="">Select Payment Type</option>
                                                    <option value="NEFT">NEFT</option>
                                                    <option value="RTGS">RTGS</option>
                                                    <option value="Cheque">Cheque</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Payment Terms</label>
                                                <select id="payment_terms" name="payment_terms" class="form-control">
                                                    <option value="">Select Payment Term</option>
                                                    <option value="DCMC">30 Days credit fom the date of material received</option>
                                                    <option value="AMR">100% After material received</option>
                                                    <option value="APBD">50 % Advance along with the purchase order balance 50 % before dispatch the material</option>
                                                    <option value="APAR">50 % Along with the purchase order 50 % after material received</option>
                                                    <option value="PO">100 % Advance along with the purchase order</option>
                                                    <option value="DMR">15 Days credit from the date of material received </option>
                                                    <option value="APAI">30 % advance along with the purchase order Balance 70 % after installation</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label>Finance Email</label>
                                                <input type="email" class="form-control" id="finance_email" name="finance_email" placeholder="Enter Finance Email">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>Consignee</label>
                                                <input type="text" class="form-control" id="consignee" name="consignee" placeholder="Enter Consignee">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label>State</label>
                                                <input type="text" class="form-control" id="state" name="state" placeholder="Enter State">
                                            </div>

                                            <div class="col-md-6 mt-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check me-4">
                                                        <input class="form-check-input" type="checkbox" id="send_email_customer" name="send_email_customer">
                                                        <label class="form-check-label" for="send_email_customer">
                                                            Send Email to Customer
                                                        </label>
                                                    </div>
                                                    <div class="form-check ml-5">
                                                        <input class="form-check-input" type="checkbox" id="allow_portal" name="allow_portal">
                                                        <label class="form-check-label" for="allow_portal">
                                                            Allow Customer/Vendor Portal
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <hr>

                                        <!-- ================= GST DETAILS ================= -->
                                        <h4 class="mt-4 mb-3">GST Details</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>GSTIN</label>
                                                <input type="text" class="form-control" id="gstin" name="gstin" placeholder="Enter GSTIN">
                                            </div>
                                            <div class="col-md-6">
                                                <label>State Name</label>
                                                <select id="state_name" name="state_name" class="form-control">
                                                    <option value="">Select State</option>
                                                    <option value="Andhra Pradesh">Andhra Pradesh</option>
                                                    <option value="Tamil Nadu">Tamil Nadu</option>
                                                    <option value="Karnataka">Karnataka</option>
                                                    <option value="Kerala">Kerala</option>
                                                    <option value="Maharashtra">Maharashtra</option>
                                                </select>
                                            </div>



                                            <!-- <div class="col-md-6">
                                                <label>GST Percentage</label>
                                                <input type="number" class="form-control" id="gst_percentage" name="gst_percentage" placeholder="Enter GST %">
                                            </div> -->
                                            <!-- <div class="col-md-4">
                                                <label>GST Applicable From</label>
                                                <input type="date" class="form-control" id="gst_applicable_from" name="gst_applicable_from">
                                            </div> -->
                                        </div>


                                        <hr>

                                        <h4 class="mt-4 mb-3">IGST Details</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>IGST Number</label>
                                                <input type="text" class="form-control" id="igst_number" name="igst_number" placeholder="Enter IGST Number">
                                            </div>
                                            <!-- <div class="col-md-4">
                                                <label>IGST Percentage</label>
                                                <input type="number" step="0.01" class="form-control" id="igst_percentage" name="igst_percentage" placeholder="Enter IGST %">
                                            </div> -->

                                            <div class="col-md-6">
                                                <label>State Name</label>
                                                <select id="igst_state_name" name="state_name" class="form-control">
                                                    <option value="">Select State</option>
                                                    <option value="Andhra Pradesh">Andhra Pradesh</option>
                                                    <option value="Tamil Nadu">Tamil Nadu</option>
                                                    <option value="Karnataka">Karnataka</option>
                                                    <option value="Kerala">Kerala</option>
                                                    <option value="Maharashtra">Maharashtra</option>
                                                </select>
                                            </div>
                                            <!-- <div class="col-md-4">
                                                <label>IGST Applicable From</label>
                                                <input type="date" class="form-control" id="igst_applicable_from" name="igst_applicable_from">
                                            </div> -->
                                        </div>

                                        <!-- ================= BUTTONS ================= -->
                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveVendor" class="btn btn-primary"> Save</button>
                                            <button type="button" id="btnUpdateVendor" class="btn btn-primary" style="display:none;"> <i class="fa fa-edit"> Update</i></button>

                                        </div>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>

                    <!-- Vendor list will load here -->
                    <div id="vendorListDiv" class="mt-4"></div>

                </div> <!-- container-fluid -->
                <?php include($IncludesPath . "footer.php"); ?>
            </div> <!-- page-content -->
        </div> <!-- main-content -->

    </div> <!-- END layout-wrapper -->




    <!-- Vendor View Modal -->
    <div class="modal fade" id="vendorViewModal" tabindex="-1" aria-labelledby="vendorViewLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="vendorViewLabel">Vendor Details</h5>
                    <!-- <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> -->
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>

                </div>
                <div class="modal-body" id="vendorDetailsBody">
                    <!-- Vendor details will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>


    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

    <script>
        $(document).ready(function() {

            // ========== LOAD VENDOR LIST ==========

            // function loadVendors() {
            //     $.ajax({
            //         url: 'Ajax/Ajax-Vendor.php',
            //         type: 'POST',
            //         data: {
            //             methodType: 'load'
            //         },
            //         dataType: 'json', // <---- important: expect JSON
            //         success: function(res) {
            //             if (res.status === 'success') {
            //                 $('#vendorListDiv').html(res.html); // <---- inject only HTML part
            //             } else {
            //                 $('#vendorListDiv').html('<div class="alert alert-warning">' + (res.msg || 'No vendors found') + '</div>');
            //             }
            //         },
            //         error: function() {
            //             alert('Failed to load vendor list.');
            //         }
            //     });
            // }



            // MORE SEARCH
            $(document).on("click", "#btnMoreSearch", function() {
                $(this).data('moresearch', true);

                let searchKey = $("#tblVendor_filter input").val();

                if (searchKey.length > 0) {
                    loadVendors({
                        searchkey: searchKey,
                        loadAll: false
                    });

                }
            });


            // LOAD ALL (same way as your required format)
            $(document).on("click", "#btnLoadAll", function() {
                $(this).data('loadall', true);

                loadVendors({
                    searchkey: "",
                    loadAll: true
                });

            });





            function loadVendors(options = {}) {

                // Default values
                let searchKey = options.searchkey || "";

                let loadAll = options.loadAll || false;

                $.ajax({
                    url: "Ajax/Ajax-Vendor.php",
                    type: "POST",
                    data: {
                        methodType: "load",
                        searchkey: searchKey, // KEEP this
                        loadAll: loadAll
                    },

                    dataType: "json",
                    success: function(res) {

                        if (res.status === "success") {

                            if ($.fn.DataTable.isDataTable("#tblVendor")) {
                                $("#tblVendor").DataTable().destroy();
                            }

                            $("#vendorListDiv").html(res.html);

                            setTimeout(() => {
                                fnVendorDataTable();
                            }, 100);

                        } else {
                            $("#vendorListDiv").html(
                                `<div class="alert alert-warning">
                        ${res.msg || "No vendors found"}
                     </div>`
                            );
                        }
                    }
                });
            }


            loadVendors(); // initial load

            // ADD BELOW INSIDE fnVendorDataTable()

            function fnVendorDataTable() {

                if ($.fn.DataTable.isDataTable("#tblVendor")) {
                    $("#tblVendor").DataTable().clear().destroy();
                }

                $('#tblVendor').DataTable({
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
                        title: 'Vendor List',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                setTimeout(() => {
                    if (!$("#btnMoreSearch").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                            .insertAfter("#tblVendor_filter label");
                    }

                    if (!$("#btnLoadAll").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                            .insertAfter("#btnMoreSearch");
                    }
                }, 200);
            }






            // ========== SAVE ==========
            $('#btnSaveVendor').click(function(e) {
                e.preventDefault();
                const formData = $('#vendorForm').serialize() + '&methodType=save';
                $.ajax({
                    url: 'Ajax/Ajax-Vendor.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            resetForm();
                            loadVendors();
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Save request failed!');
                    }
                });
            });

            // ========== UPDATE ==========
            $('#btnUpdateVendor').click(function(e) {
                e.preventDefault();
                const formData = $('#vendorForm').serialize() + '&methodType=update';
                $.ajax({
                    url: 'Ajax/Ajax-Vendor.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            resetForm();
                            loadVendors();
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Update request failed!');
                    }
                });
            });

            // ========== EDIT VENDOR ==========
            $(document).on('click', '.btnEdit', function() {
                const vendor_id = $(this).data('id');

                $.ajax({
                    url: 'Ajax/Ajax-Vendor.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        vendor_id: vendor_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            const data = res.data;

                            // Basic info
                            $('#vendor_id').val(data.VendorID);
                            $('#vendor_name').val(data.VendorName);
                            $('#vendor_code').val(data.VendorCode);
                            $('#account_type').val(data.AccountType);
                            $('#credit_limit').val(data.CreditLimit);
                            $('#credit_days').val(data.CreditDays);
                            $('#group').val(data.Group);
                            $('#cheque_discount_limit').val(data.ChequeDiscountLimit);
                            $('#rate_of_interest').val(data.RateOfInterest);
                            $('#pdc_discounted_ac').val(data.PdcDiscountedAC);
                            $('#bank_ac').val(data.BankAC);
                            $('#item_desc').val(data.ItemDesc);
                            $('#bank_branch').val(data.BankBranch);
                            $('#cash_flow_classification').val(data.CashFlowClassification);
                            // $('#debit_credit_proposal').val(data.DebitCreditProposal);
                            // $('#debit_credit_required').val(data.DebitCreditRequired);
                            // $('#exchange_adjustment_gain_ac').val(data.ExchangeAdjustmentGainAC);
                            // $('#exchange_adjustment_loss_ac').val(data.ExchangeAdjustmentLossAC);

                            // $('#exchange_adjustment_gain_ac').val(data.ExchangeAdjustmentGainAC);
                            // $('#exchange_adjustment_loss_ac').val(data.ExchangeAdjustmentLossAC);


                            // $('#primary_account').val(data.PrimaryAccount);
                            // $('#default_currency').val(data.DefaultCurrency);
                            // $('#consolidation_method').val(data.ConsolidationMethod);
                            $('#payment_terms').val(data.PaymentTerms);
                            // $('#reminder_terms').val(data.ReminderTerms);
                            // $('#finance_charge_terms').val(data.FinanceChargeTerms);

                            // Contact details
                            $('#address').val(data.Address);
                            $('#tel_no').val(data.TelNo);
                            // $('#qty').val(data.Qty);
                            $('#fax_no').val(data.FaxNo);
                            $('#delivery_address').val(data.DeliveryAddress);
                            $('#pin').val(data.Pin);
                            $('#email').val(data.Email);
                            // $('#password').val(data.Password);
                            $('#bank_account_name').val(data.BankAccountName);
                            $('#bank_account_number').val(data.BankAccountNumber);
                            $('#ifsc_code').val(data.IFSCCode);
                            $('#payment_type').val(data.PaymentType);
                            $('#finance_email').val(data.FinanceEmail);
                            $('#consignee').val(data.Consignee);
                            $('#state').val(data.State);
                            $('#state_name').val(data.StateName);
                            $('#igst_state_name').val(data.IGSTStateName);
                            $('#gstin').val(data.GSTIN);
                            $('#gst_percentage').val(data.GSTPercentage);
                            $('#gst_applicable_from').val(data.GSTApplicableFrom);
                            $('#igst_number').val(data.IGSTNumber);
                            $('#igst_percentage').val(data.IGSTPercentage);
                            $('#igst_applicable_from').val(data.IGSTApplicableFrom);
                            $('#send_email_customer').prop('checked', data.SendEmailCustomer === 'Yes' || data.SendEmailCustomer == 1);
                            $('#allow_portal').prop('checked', data.AllowPortal === 'Yes' || data.AllowPortal == 1);


                            // Optional fields
                            $('#created_date').val(data.CreatedDate);
                            $('#updated_date').val(data.UpdatedDate);

                            // Show correct buttons
                            $('#btnSaveVendor').hide();
                            $('#btnUpdateVendor').show();
                        } else {
                            alert('Vendor not found.');
                        }
                    },
                    error: function() {
                        alert('Failed to fetch vendor details.');
                    }
                });
            });


            // ========== DELETE ==========
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this vendor?')) return;
                const vendor_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/Ajax-Vendor.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        vendor_id: vendor_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadVendors();
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Delete request failed!');
                    }
                });
            });

            // ========== RESET FORM ==========
            function resetForm() {
                $('#vendorForm')[0].reset();
                $('#vendor_id').val('');
                $('#btnSaveVendor').show();
                $('#btnUpdateVendor').hide();
            }

            // ------------------ Load Single Vendor (Edit Page) ------------------
            var urlParams = new URLSearchParams(window.location.search);
            var vendor_id = urlParams.get('vendor_id');
            if (vendor_id) {
                $('#btnSaveVendor').hide();
                $('#btnUpdateVendor').show();
                $.ajax({
                    url: 'Ajax/Ajax-Vendor.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        vendor_id: vendor_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            $.each(res.data, function(key, value) {
                                $('#' + key).val(value);
                            });
                            $('#vendor_id').val(vendor_id);
                        } else {
                            alert('Error loading vendor data: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('AJAX request failed while loading vendor data!');
                    }
                });
            }

        });





        //  View Vendor Details
        $(document).on('click', '.btnView', function() {
            const vendorId = $(this).data('id');

            $.ajax({
                url: 'Ajax/Ajax-Vendor.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    methodType: 'getSingle',
                    vendor_id: vendorId
                },
                success: function(res) {
                    if (res.status === 'success' && res.data) {
                        let vendor = res.data;


                        const excludedFields = [
                            'CreatedDate',
                            'UpdatedDate',

                        ];

                        let html = `
    <div class="container-fluid">
        <div class="row">
            ${Object.entries(vendor)
                .filter(([key]) => !excludedFields.includes(key))
                .map(([key, value]) => `
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3 shadow-sm bg-light" style="word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
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







                        $('#vendorDetailsBody').html(html);
                        $('#vendorViewModal').modal('show');
                    } else {
                        alert(res.msg || 'Failed to fetch vendor details.');
                    }
                },
                error: function() {
                    alert('Error loading vendor details.');
                }
            });
        });
    </script>



</body>

</html>