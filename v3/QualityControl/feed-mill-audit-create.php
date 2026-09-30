<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_FEED_MILL_AUDIT;

$FeedMillAuditID = $_GET["FeedMillAuditID"];
$ConfirmPayment = $_GET["ConfirmPayment"];

$bodyContent = array(
    "FeedMillAuditID" => $FeedMillAuditID
);

if ($FeedMillAuditID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;
    $Data = $result->Data[0];
}

$api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resAuditCategory = $resDropdownsAPI->Data->AuditCategory;
$resFarmers = $resDropdownsAPI->Data->Farmers;
$resCertificationBody = $resDropdownsAPI->Data->CertificationBody;
$resLabs = $resDropdownsAPI->Data->LbuDestination;
$resPaymentTypes = $resDropdownsAPI->Data->PaymentTypes;

// echo json_encode($resDropdownsAPI, JSON_PRETTY_PRINT);
// exit;

$ProductionPlant = $_SESSION['UnitID'];
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmFeedMillAudit" enctype="multipart/form-data">
                                    <div class="row">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpArrivalPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpArrivalPlant"
                                                id="drpArrivalPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                        <?php echo $Item->FullName . ' - ' . $Item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpFarmAudit">Name of Audit <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFarmAudit" id="drpFarmAudit">
                                                <option value="" <?php echo ($Data->AuditNameID == '') ? 'selected' : ''; ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditNameID == $item->AuditID) ? 'selected' : ''; ?>>
                                                        <?php echo $item->CategoryName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpFarmer">Farmer Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFarmer" id="drpFarmer">
                                                <option value="" <?php echo ($Data->FarmerNameID == '') ? 'selected' : ''; ?>>Select</option>
                                                <?php foreach ($resFarmers as $item) { ?>
                                                    <option value="<?php echo $item->FarmID ?>" <?php echo ($Data->FarmerNameID == $item->FarmID) ? 'selected' : ''; ?>>
                                                        <?php echo $item->FarmerName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCertifyBody">Certifying Body<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpCertifyBody" id="drpCertifyBody">
                                                <option value="" <?php echo ($Data->CertificationBodyID == '') ? 'selected' : ''; ?>>Select</option>
                                                <?php foreach ($resCertificationBody as $item) { ?>
                                                    <option value="<?php echo $item->CertificationBodyID ?>" <?php echo ($Data->CertificationBodyID == $item->CertificationBodyID) ? 'selected' : ''; ?>><?php echo $item->CertificationBodyName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRegistrationNo">Govt Registration No <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtRegistrationNo'
                                                id="txtRegistrationNo" placeholder="Enter Govt Register Number"
                                                value="<?php echo $Data->RegistrationNo ?>">
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCertificationNumber">Certification Number<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtCertificationNumber'
                                                id="txtCertificationNumber" placeholder="Enter Certification Number"
                                                value="<?php echo $Data->CertificationNumber ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBAPNumber">BAP Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtBAPNumber'
                                                id="txtBAPNumber" placeholder="Enter BAP Number"
                                                value="<?php echo $Data->BAPNumber ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAuditDate" class="form-label">Audit Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtAuditDate"
                                                name="txtAuditDate" value='<?php echo $Data->AuditDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIssuedOn" class="form-label">Issued On <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtIssuedOn" name="txtIssuedOn"
                                                value='<?php echo $Data->IssuedOn ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtValidityUpto" class="form-label">Validity Upto <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtValidityUpto"
                                                name="txtValidityUpto" value='<?php echo $Data->ValidityUpto ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRenewalDate" class="form-label">Renewal Date </label>
                                            <input type="date" class="form-control" id="txtRenewalDate"
                                                name="txtRenewalDate" value='<?php echo $Data->RenewalDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAuditStatus">Audit Status<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpAuditStatus" id="drpAuditStatus">
                                                <?php if (isset($_GET['FoodAuditID']) && !empty($_GET['FoodAuditID'])) { ?>
                                                    <option value="1" style="color: green;" <?php echo ($Data->AuditStatus == "1") ? 'selected' : ''; ?>>Fresh Audit</option>
                                                    <option value="2" style="color: blue;" <?php echo ($Data->AuditStatus == "2") ? 'selected' : ''; ?>>Renewal Audit</option>
                                                    <option value="3" style="color: red;" <?php echo ($Data->AuditStatus == "3") ? 'selected' : ''; ?>>Re Certification</option>
                                                    <option value="4" style="color: yellow;" <?php echo ($Data->AuditStatus == "4") ? 'selected' : ''; ?>>Fresh Audit Announced</option>
                                                    <option value="5" style="color: pink;" <?php echo ($Data->AuditStatus == "5") ? 'selected' : ''; ?>>Fresh Audit UnAnnounced</option>
                                                    <option value="6" style="color: black;" <?php echo ($Data->AuditStatus == "6") ? 'selected' : ''; ?>>Annual audit announced</option> 
                                                    <option value="7" style="color: white;" <?php echo ($Data->AuditStatus == "7") ? 'selected' : ''; ?>>Annual audit UnAnnounced </option>
                                                    <option value="8" style="color: brown;" <?php echo ($Data->AuditStatus == "8") ? 'selected' : ''; ?>>Re Certification announced </option>
                                                    <option value="9" style="color: purple;" <?php echo ($Data->AuditStatus == "9") ? 'selected' : ''; ?>>Re Certification UnAnnounced </option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" style="color: green;">Fresh Audit</option>
                                                    <option value="2" style="color: blue;">Renewal Audit</option>
                                                    <option value="3" style="color: red;">Re Certification</option>
                                                    <option value="4" style="color: yellow;">Fresh Audit Announced</option>
                                                    <option value="5" style="color: pink;">Fresh Audit UnAnnounced</option>
                                                    <option value="6" style="color: black;">Annual audit announced </option>
                                                    <option value="7" style="color: white;">Annual audit UnAnnounced</option>
                                                    <option value="8" style="color: brown;">Re Certification announced </option>
                                                    <option value="9" style="color: purple;">Re Certification UnAnnounced </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCheckedBy">Checked by<span style="color:red">*</span></label>
                                                <select class="drpSelect" name="drpCheckedBy" id="drpCheckedBy">
                                            <option value="" <?php echo ($Data->CheckedBy == '') ? 'selected' : ''; ?>> Select</option>
                                                <?php foreach ($resLabs as $item) { ?>
                                                <?php if ($item->PlantID == $ProductionPlant) { ?>
                                                    <option value="<?php echo $item->LUID ?>" <?php echo ($Data->CheckedBy == $item->LUID) ? 'selected' : ''; ?>>
                                                        <?php echo $item->LbuName ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col col-md-3 mb-3">
                                            <label for="drpVerifiedBy">Verified by<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpVerifiedBy" id="drpVerifiedBy">
                                                <option value="" <?php echo ($Data->VerifiedBy == '') ? 'selected' : ''; ?>> Select</option>
                                                <?php foreach ($resLabs as $item) { ?>
                                                    <?php if ($item->PlantID == $ProductionPlant) { ?>
                                                    <option value="<?php echo $item->LUID ?>" <?php echo ($Data->VerifiedBy == $item->LUID) ? 'selected' : ''; ?>>
                                                        <?php echo $item->LbuName ?>
                                                    </option>
                                                <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRemarks">Remarks</label>
                                            <input type="text" class="form-control" name="txtRemarks" id="txtRemarks"
                                                placeholder="Enter Remarks" value="<?php echo $Data->Remarks ?>">
                                        </div>
                                       
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFileUpload">Upload File
                                                <?php $UploadFilesArray = $Data->UploadFile;
                                                foreach ($UploadFilesArray as $ItemUploadFile) {
                                                    $fileName = basename($ItemUploadFile); ?>
                                                    <a target="_blank" href='<?php echo $ItemUploadFile ?>' class="mr-3 text-success btnActions" data-placement="top" title="Download <?php echo $fileName ?>" data-original-title="Download">
                                                        <i class="mdi mdi-eye font-size-16"></i>
                                                    </a>
                                                <?php } ?>
                                            </label>
                                            <input type="file" class="form-control" name="files[]" id="files" value="<?php echo $Data->UploadFile ?>" multiple>
                                        </div>



                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['FeedMillAuditID']) && !empty($_GET['FeedMillAuditID'])) { ?>
                                                    <option value="1" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>
                                                        Active</option>
                                                    <option value="0" <?php echo ($Data->Status == "0") ? 'selected' : ''; ?>>
                                                        In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In-Active</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <a href="feed-mill-audit-list" class="btn btn-danger">Close</a>
                                            <?php if ($FeedMillAuditID == '') { ?>
                                                <button type="submit" class="btn btn-primary btnSave" name="submit"
                                                    id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit"
                                                    id="submit">Update</button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </form>

                                <form role="form" method="post" id="frmFeedMillAuditPayment">
                                    <hr>
                                    <div class="row">
                                        <div>
                                            <h4><u> Payment Details </u></h4>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPurposeOfInvoice">Purpose Of Invoice <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name="txtPurposeOfInvoice"
                                                id="txtPurposeOfInvoice" placeholder="Enter Purpose of Invoice">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPaymentType">Type of Payment <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPaymentType" id="drpPaymentType">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : ''; ?>>
                                                    Select</option>
                                                <?php foreach ($resPaymentTypes as $item) { ?>
                                                    <option value="<?php echo $item->OptionID ?>" <?php echo ($Data->AuditID == $item->OptionID) ? 'selected' : ''; ?>>
                                                        <?php echo $item->OptionName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoiceNo">Invoice No <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name="txtInvoiceNo"
                                                id="txtInvoiceNo" placeholder="Enter Invoice No">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoieDate">Invoice Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtInvoieDate"
                                                id="txtInvoieDate" placeholder="Invoice Date">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoiceValue">Invoice Value <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name="txtInvoiceValue"
                                                id="txtInvoiceValue" placeholder="Enter Invoice value">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtOthers">Others </label>
                                            <input type="text" class="form-control" name="txtOthers" id="txtOthers"
                                                placeholder="Enter Payment Remarks">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoiceDueDate">Invoice Due Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtInvoiceDueDate"
                                                id="txtInvoiceDueDate" placeholder="Invoice Date">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTotalAmount">Total Amount<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name="txtTotalAmount"
                                                id="txtTotalAmount" placeholder="Enter Total Amount">
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnFeedMillAuditID" name="hdnFeedMillAuditID"
                                                value="<?php echo $FeedMillAuditID ?>" />
                                            <input type="hidden" id="hdnPaymentID" name="hdnPaymentID" value="" />
                                            <button class="btn btn-success btnPaymnet" name="submit" id="submit">Add
                                                Payments</button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>
                        </div>
                    </div>
                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            <?php include_once ROOT . '/includes/footer.php' ?>

        </div>
        <!-- end main content-->
        <!-- Modal -->

    </div>
    <!-- END layout-wrapper -->

    <?php include_once ROOT . '/includes/common-js.php' ?>

    <!-- Required datatable js -->
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <?php include_once ROOT . '/includes/app-js.php' ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>
    <script>
        function fnShowDataTable() {
            var table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                bsort: true,
                dom: 'Blfrtip',
                // dom: '<"top-left"Blfrtip>',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn-left-align',
                    title: 'Feed Mill Audit Payment List <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        function showPaymentList() {
            var confirmPayment = "<?php echo $ConfirmPayment; ?>";
            $.ajax({
                url: 'Ajax/ajax-feed-mill-audit-create.php',
                type: 'POST',
                data: {
                    methodType: 'getList',
                    feedMillAuditID: $("#hdnFeedMillAuditID").val(),
                    confirmPayment: confirmPayment,
                },
                success: function (data) {
                    console.log(data);
                    $("#divDataList").html(data);
                    fnShowDataTable();

                    $("#hdnPaymentID").val('');
                    $(".btnPaymnet").text("Add Payment");
                    return false
                }
            });
        }

        $(document).ready(function () {

            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant != 0) {
                $("#drpArrivalPlant").prop('disabled', true);
                $("#drpArrivalPlant").val(productionPlant).trigger('change');

            } else {
                $("#drpArrivalPlant").prop('disabled', false);
                $("#drpArrivalPlant").val("").trigger('change');
            }

            var feedMillAuditID = $("#hdnFeedMillAuditID").val();
            if (feedMillAuditID != '') {
                showPaymentList();
                $(".btnPaymnet").prop("disabled", false);
            } else {
                $(".btnPaymnet").prop("disabled", true);
            }
            confirmPaymentEvent();

        });

        $(document).on("change", "#drpAuditName", function () {
            var certificationNo = $("#drpAuditName option:selected").data("certificate-no");
            $("#txtRegisterNo").val(certificationNo);
        });

        function confirmPaymentEvent() {
            var confirmPayment = "<?php echo $ConfirmPayment; ?>";
            if (confirmPayment == 1) {
                $("#drpArrivalPlant").prop("disabled", true);
                $("#drpFarmAudit").prop("disabled", true);
                $("#drpFarmer").prop("disabled", true);
                $("#drpCertifyBody").prop("disabled", true);
                $("#txtRegistrationNo").prop("readonly", true);
                $("#txtCertificationNumber").prop("readonly", true);
                $("#txtBAPNumber").prop("readonly", true);
                $("#txtAuditDate").prop("readonly", true);
                $("#txtIssuedOn").prop("readonly", true);
                $("#txtValidityUpto").prop("readonly", true);
                $("#txtRenewalDate").prop("readonly", true);
                $("#drpAuditStatus").prop("disabled", true);
                $("#drpStatus").prop("disabled", true);
                $("#txtRemarks").prop("readonly", true);
                $("#drpCheckedBy").prop("disabled", true);
                $("#drpVerifiedBy").prop("disabled", true);
                $("#drpVerifiedBy").prop("disabled", true);
                $("#txtFileUpload").prop("disabled", true);

                $("#txtPurposeOfInvoice").prop("readonly", true);
                $("#drpPaymentType").prop("disabled", true);
                $("#txtInvoiceNo").prop("readonly", true);
                $("#txtInvoieDate").prop("readonly", true);
                $("#txtInvoiceValue").prop("readonly", true);
                $("#txtInvoiceDueDate").prop("readonly", true);
                $("#txtTotalAmount").prop("readonly", true);
                $("#txtOthers").prop("readonly", true);

                $(".btnPaymnet").prop("disabled", true);
                $(".btnSave").prop("disabled", true);
            } else {
                // $("#drpArrivalPlant").prop("disabled", false);
                $("#drpFarmAudit").prop("disabled", false);
                $("#drpFarmer").prop("disabled", false);
                $("#drpCertifyBody").prop("disabled", false);
                $("#txtRegistrationNo").prop("readonly", false);
                $("#txtCertificationNumber").prop("readonly", false);
                $("#txtBAPNumber").prop("readonly", false);
                $("#txtAuditDate").prop("readonly", false);
                $("#txtIssuedOn").prop("readonly", false);
                $("#txtValidityUpto").prop("readonly", false);
                $("#txtRenewalDate").prop("readonly", false);
                $("#drpAuditStatus").prop("disabled", false);
                $("#drpStatus").prop("disabled", false);
                $("#txtRemarks").prop("readonly", false);
                $("#drpCheckedBy").prop("disabled", false);
                $("#drpVerifiedBy").prop("disabled", false);
                $("#txtFileUpload").prop("disabled", true);

                $("#txtPurposeOfInvoice").prop("readonly", false);
                $("#drpPaymentType").prop("disabled", false);
                $("#txtInvoiceNo").prop("readonly", false);
                $("#txtInvoieDate").prop("readonly", false);
                $("#txtInvoiceValue").prop("readonly", false);
                $("#txtInvoiceDueDate").prop("readonly", false);
                $("#txtTotalAmount").prop("readonly", false);
                $("#txtOthers").prop("readonly", false);

                $(".btnPaymnet").prop("disabled", false);
                $(".btnSave").prop("disabled", false);
            }
        }

        jQuery.validator.addMethod(
            "noSpace",
            function (value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );

        $("#frmFeedMillAudit").validate({

            onkeyup: false,
            onfocusout: false,
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                drpArrivalPlant: {
                    required: true,
                },
                drpFarmAudit: {
                    required: true,
                },
                drpFarmer: {
                    required: true,
                },
                drpCertifyBody: {
                    required: true,
                },
                txtRegistrationNo: {
                    required: true,
                },
                txtCertificationNumber: {
                    required: true,
                },
                txtBAPNumber: {
                    required: true,
                },
                txtAuditDate: {
                    required: true,
                },
                txtIssuedOn: {
                    required: true,
                },
                txtValidityUpto: {
                    required: true,
                },
                // txtRenewalDate: {
                //     required: true,
                // },
                drpAuditStatus: {
                    required: true,
                },
                drpCheckedBy: {
                    required: true,
                },
                // drpVerifiedBy: {
                //     required: true,
                // },
                // txtRemarks: {
                //     required: true,
                // },
                drpStatus: {
                    required: true,
                },

            },
            errorPlacement: function () {
                return false;
            },
            submitHandler: function () {
                //uma//
                const files = $("#files")[0].files;
                const fileData = new FormData();

                // Add files to FormData
                let fileNames = '';
                for (let i = 0; i < files.length; i++) {
                    fileData.append("files[]", files[i]);
                    fileNames += files[i].name + '\n';
                }
                //uma//
                const requestData = {
                    methodType: 'formPost',
                    feedMillAuditID: $("#hdnFeedMillAuditID").val(),
                    productionPlant: $("#drpArrivalPlant").val(),
                    farmAudit: $("#drpFarmAudit").val(),
                    farmer: $("#drpFarmer").val(),
                    certificationBody: $("#drpCertifyBody").val(),
                    registrationNo: $("#txtRegistrationNo").val(),
                    certificationNumber: $("#txtCertificationNumber").val(),
                    bapNumber: $("#txtBAPNumber").val(),
                    auditDate: $("#txtAuditDate").val(),
                    issuedOn: $("#txtIssuedOn").val(),
                    validityUpto: $("#txtValidityUpto").val(),
                    renewalDate: $("#txtRenewalDate").val(),
                    auditStatus: $("#drpAuditStatus").val(),
                    status: $("#drpStatus").val(),
                    remarks: $("#txtRemarks").val(),
                    checkedBy: $("#drpCheckedBy").val(),
                    verifiedBy: $("#drpVerifiedBy").val()
                };
                for (const key in requestData) {
                    fileData.append(key, requestData[key]);
                }
                $.ajax({
                    url: 'Ajax/ajax-feed-mill-audit-create.php',
                    type: 'POST',
                    data: fileData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "feed-mill-audit-create?FeedMillAuditID=" + data.LastInserted;
                        } else {
                            alert(data.Error);
                        }
                        return false;
                    }
                });
                return false;
            }
        });

        $("#frmFeedMillAuditPayment").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                txtPurposeOfInvoice: {
                    required: true,
                },
                drpPaymentType: {
                    required: true,
                },
                txtInvoiceNo: {
                    required: true,
                },
                txtInvoieDate: {
                    required: true,
                },
                txtInvoiceValue: {
                    required: true,
                },
                txtInvoiceDueDate: {
                    required: true,
                },
                // txtPaymentDueDate: {
                //     required: true,
                // },
                txtTotalAmount: {
                    required: true,
                },
            },
            errorPlacement: function () {
                return false;
            },
            submitHandler: function () {
                $.ajax({
                    url: 'Ajax/ajax-feed-mill-audit-create.php',
                    type: 'POST',
                    data: {
                        methodType: 'paymentPost',
                        feedMillAuditPaymentID: $("#hdnPaymentID").val(),
                        feedMillAuditID: $("#hdnFeedMillAuditID").val(),
                        purposeOfInvoice: $("#txtPurposeOfInvoice").val(),
                        paymentType: $("#drpPaymentType").val(),
                        invoiceNo: $("#txtInvoiceNo").val(),
                        invoidDate: $("#txtInvoieDate").val(),
                        invoiceValue: $("#txtInvoiceValue").val(),
                        others: $('#txtOthers').val(),
                        // paymentRemarks: $('#txtPaymentRemarks').val(),
                        invoiceDueDate: $("#txtInvoiceDueDate").val(),
                        // paymentDate: $("#txtPaymentDueDate").val(),
                        totalAmount: $('#txtTotalAmount').val(),
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            showPaymentList();
                            $("#drpPaymentType").val("").trigger("change");
                            $("#txtPurposeOfInvoice,#txtInvoiceNo,#txtOthers,#txtPaymentDueDate,#txtInvoieDate,#txtInvoiceValue,#txtPaymentRemarks,#txtInvoiceDueDate,#txtTotalAmount").val("");
                        } else {
                            alert(data.Error);
                        }
                        return false;
                    }
                });
                return false;
            }
        });


        $(document).on("click", ".editBrkUp", function () {
            var paymentID = $(this).data("paymentid");
            $("#hdnPaymentID").val(paymentID);

            $(".btnPaymnet").text("Update Payment");
            $("#drpPaymentType").val($(this).closest("tr").find(".tdPaymentType").data('paymenttypeid')).trigger("change");
            $("#txtPurposeOfInvoice").val($(this).closest("tr").find(".tdPurposeOfInvoice").text());
            $("#txtInvoiceNo").val($(this).closest("tr").find(".tdInvoiceNumber").text());
            $("#txtInvoieDate").val($(this).closest("tr").find(".tdInvoiceDate").data('invoicedate'));
            $("#txtInvoiceValue").val($(this).closest("tr").find(".tdInvoiceValue").text());
            // $("#txtPaymentDueDate").val($(this).closest("tr").find(".tdPaymentDate").data('paymentdate'));
            $("#txtInvoiceDueDate").val($(this).closest("tr").find(".tdInvoiceDueDate").data('invoiceduedate'));
            $("#txtTotalAmount").val($(this).closest("tr").find(".tdTotalAmount").text()); // tdTotalAmount
            // $("#txtPaymentRemarks").val($(this).closest("tr").find(".tdRemarks").text());
            $("#txtOthers").val($(this).closest("tr").find(".tdOthers").text());
        });

        $(document).on("click", ".confirmPayment", function () {

            var paymentID = $(this).data("paymentid");
            var paymentDate = $(this).closest("tr").find(".txtPaymentDate").val();
            var paymentRemarks = $(this).closest("tr").find(".txtPaymentRemarks").val();

            $.ajax({
                url: 'Ajax/ajax-feed-mill-audit-create.php',
                type: 'POST',
                data: {
                    methodType: 'confirmPayment',
                    feedMillAuditPaymentID: paymentID,
                    feedMillAuditID: $("#hdnFeedMillAuditID").val(),
                    paymentRemarks: paymentRemarks,
                    paymentDate: paymentDate,
                },
                success: function (data) {
                    console.log(data);
                    if (data.Error != '') {
                        alert(data.Msg);
                        showPaymentList();
                    } else {
                        alert(data.Error);
                    }
                    return false;
                }
            });
        });

        $(document).on("click", ".deleteBrkUp", function () {

            var paymentID = $(this).data("paymentid");
            $("#hdnPaymentID").val(paymentID);
            if (confirm("Are you sure you want to delete this item ?") == true) {
                $.ajax({
                    url: 'Ajax/ajax-feed-mill-audit-create.php',
                    type: 'POST',
                    data: {
                        methodType: 'paymentDelete',
                        feedMillAuditID: $("#hdnFeedMillAuditID").val(),
                        feedMillAuditPaymentID: $("#hdnPaymentID").val(),
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            showPaymentList();
                            $("#drpPaymentType").val("").trigger("change");
                            $("#txtPurposeOfInvoice,#txtInvoiceNo,#txtOthers,#txtPaymentDueDate,#txtInvoieDate,#txtInvoiceValue,#txtPaymentRemarks,#txtInvoiceDueDate,#txtTotalAmount").val("");
                        } else {
                            alert(data.Error);
                        }
                        return false;
                    }
                });
            }
        });
    </script>
    </body>

</html>