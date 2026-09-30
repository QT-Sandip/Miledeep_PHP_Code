<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_SOCIAL_AUDIT;

$SocialAuditID = $_GET["SocialAuditID"];
$ConfirmPayment = $_GET["ConfirmPayment"];

$bodyContent = array(
    "SocialAuditID" => $SocialAuditID
);

if ($SocialAuditID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;
    $Data = $result->Data[0];
    $AuditTypeID = $Data->AuditTypeID;
}

$api = $api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resAuditCategory = $resDropdownsAPI->Data->AuditCategory;
$resAuditCategoryOptions = $resDropdownsAPI->Data->AuditCategoryOptions;
$resCertificationBody = $resDropdownsAPI->Data->CertificationBody;
$resSiteOptions = $resDropdownsAPI->Data->SiteOptions;
$resPaymentTypes = $resDropdownsAPI->Data->PaymentTypes;
$resLabs = $resDropdownsAPI->Data->LbuDestination;

// echo json_encode($resAuditCategoryOptions, JSON_PRETTY_PRINT);
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
                                <form role="form" method="post" id="frmSocialAudit"
                                enctype="multipart/form-data">
                                    <div class="row">
                                        <input type="hidden" id="hdnAuditType" value="<?php echo htmlspecialchars(json_encode($resAuditCategoryOptions), ENT_QUOTES, 'UTF-8'); ?>" />

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpArrivalPlant" class="form-label">Production Plant<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpArrivalPlant" id="drpArrivalPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                        <?php echo $Item->FullName . ' - ' . $Item->StationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAuditName">Name of the Audit <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpAuditName" id="drpAuditName">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : ''; ?>> Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <?php // if ($item->PlantID == $ProductionPlant) { 
                                                    ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditNameID == $item->AuditID) ? 'selected' : ''; ?>
                                                        data-certificate-no="<?php echo $item->AuditCertificationNo ?>">
                                                        <?php echo $item->CategoryName ?>
                                                    </option>
                                                    <?php // } 
                                                    ?>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAuditType">Type of Audit<span style="color:red">*</span></label>
                                            <input type="hidden" id="hdnAuditTypeID" name="hdnAuditTypeID" value="<?php echo $AuditTypeID ?>" />
                                            <select class="drpSelect" name="drpAuditType" id="drpAuditType">
                                                <option value="">Select</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCertificationBody">Certification body<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpCertificationBody" id="drpCertificationBody">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resCertificationBody as $item) { ?>
                                                    <?php // if ($item->PlantID == $ProductionPlant) { 
                                                    ?>
                                                    <option value="<?php echo $item->CertificationBodyID ?>" <?php echo ($Data->CertificationBodyID == $item->CertificationBodyID) ? 'selected' : ''; ?>>
                                                        <?php echo $item->CertificationBodyName ?>
                                                    </option>
                                                    <?php // } 
                                                    ?>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtWindowPeriod">Window Period<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name='txtWindowPeriod' id="txtWindowPeriod" placeholder="Enter Window Period" value="<?php echo $Data->WindowPeriod ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGradeNScore">Grade/Score<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtGradeNScore' id="txtGradeNScore" placeholder="Enter Grade/Score" value="<?php echo $Data->GradeNScore ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtSiteId" class="form-label">Site Id</label>
                                            <input type="text" class="form-control" id="txtSiteId" name="txtSiteId" placeholder="Enter Site ID" value='<?php echo $Data->SiteId ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAuditStart" class="form-label">Audit Start Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtAuditStart" name="txtAuditStart" value='<?php echo $Data->AuditStartDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAuditEnd" class="form-label">Audit End Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtAuditEnd" name="txtAuditEnd" value='<?php echo $Data->AuditEndDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIssuedOn" class="form-label">Issued on <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtIssuedOn" name="txtIssuedOn" value='<?php echo $Data->IssuedOn ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtValidUpto" class="form-label">Validity Upto <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtValidUpto" name="txtValidUpto" value='<?php echo $Data->ValidityUpto ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRenewalDate" class="form-label">Renewal Date </label>
                                            <input type="date" class="form-control" id="txtRenewalDate" name="txtRenewalDate" value='<?php echo $Data->RenewalDate ?>' />
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
                                            <input type="text" class="form-control" name="txtRemarks" id="txtRemarks" placeholder="Enter Remarks" value="<?php echo $Data->Remarks ?>">
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
                                                <?php if (isset($_GET['SocialAuditID']) && !empty($_GET['SocialAuditID'])) { ?>
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
                                            <a href="social-audit-list" class="btn btn-danger">Close</a>
                                            <?php if ($SocialAuditID == '') { ?>
                                                <button type="submit" class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </form>
                                <form role="form" method="post" id="frmSocialAuditPayment">
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
                                            <label for="drpPaymentType">Type of Payment <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPaymentType" id="drpPaymentType">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : ''; ?>> Select</option>
                                                <?php foreach ($resPaymentTypes as $item) { ?>
                                                    <option value="<?php echo $item->OptionID ?>" <?php echo ($Data->AuditID == $item->OptionID) ? 'selected' : ''; ?>>
                                                        <?php echo $item->OptionName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoiceNo">Invoice No <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name="txtInvoiceNo" id="txtInvoiceNo" placeholder="Enter Invoice No">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoieDate">Invoice Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtInvoieDate" id="txtInvoieDate" placeholder="Invoice Date">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoiceValue">Invoice Value <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name="txtInvoiceValue" id="txtInvoiceValue" placeholder="Enter Invoice value">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtOthers">Others </label>
                                            <input type="text" class="form-control" name="txtOthers" id="txtOthers" placeholder="Enter Other Remarks">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoiceDueDate">Invoice Due Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtInvoiceDueDate" id="txtInvoiceDueDate" placeholder="Invoice Date">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTotalAmount">Total Amount<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name="txtTotalAmount" id="txtTotalAmount" placeholder="Enter Total Amount">
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSocialAuditID" name="hdnSocialAuditID" value="<?php echo $SocialAuditID ?>" />
                                            <input type="hidden" id="hdnPaymentID" name="hdnPaymentID" value="" />
                                            <button class="btn btn-success btnPaymnet" name="submit" id="submit">Add Payments</button>
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
                </div>
            </div>
            </form>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
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
                    title: 'Social Audit Payment List <?php echo date("d-m-Y") ?>',
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
                url: 'Ajax/ajax-social-audit-create.php',
                type: 'POST',
                data: {
                    methodType: 'getList',
                    socialAuditID: $("#hdnSocialAuditID").val(),
                    confirmPayment: confirmPayment,
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();

                    $("#hdnPaymentID").val('');
                    $(".btnPaymnet").text("Add Payment");
                    return false
                }
            });
        }

        $(document).ready(function() {

            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant != 0) {
                $("#drpArrivalPlant").prop('disabled', true);
                $("#drpArrivalPlant").val(productionPlant).trigger('change');

            } else {
                $("#drpArrivalPlant").prop('disabled', false);
                $("#drpArrivalPlant").val("").trigger('change');
            }
 
            getAuditType();

            $('#drpProductCategory').select2({
                placeholder: 'Select',
                allowClear: true
            });

            var socialAuditID = $("#hdnSocialAuditID").val();
            if (socialAuditID != '') {
                showPaymentList();
                $(".btnPaymnet").prop("disabled", false);
            } else {
                $(".btnPaymnet").prop("disabled", true);
            }

            var auditType = $("#hdnAuditTypeID").val();
            console.log('hdnAuditTypeID ' + auditType);
            if (auditType != '') {
                $("#drpAuditType").val(auditType).trigger("change");
            }

            confirmPaymentEvent();
        });

        $(document).on("change", "#drpAuditName", function() {
            var certificationNo = $("#drpAuditName option:selected").data("certificate-no");
            $("#txtRegisterNo").val(certificationNo);
            getAuditType();
        });


        function getAuditType() {
            var auditTypeList = JSON.parse($("#hdnAuditType").val());
            var selectedAuditID = $("#drpAuditName").find(':selected').val();
            console.log(selectedAuditID);
            if (selectedAuditID != "") {
                var listAuditType = '<option value="">Select</option>';
                $.each(auditTypeList, function(index, val) {
                    if (selectedAuditID == val.AuditID) {
                        listAuditType += '<option value="' + val.AuditOptionID + '">' + val.OptionName + ' </option>';
                    }
                });
                $("#drpAuditType").html("").html(listAuditType);
            }
        }

        function confirmPaymentEvent() {
            var confirmPayment = "<?php echo $ConfirmPayment; ?>";
            if (confirmPayment == 1) {
                $("#drpArrivalPlant").prop("disabled", true);
                $("#drpAuditName").prop("disabled", true);
                $("#drpAuditType").prop("disabled", true);
                $("#drpProductCategory").prop("disabled", true);
                $("#drpCertificationBody").prop("disabled", true);
                $("#txtRegisterNo").prop("readonly", true);
                $("#txtGradeNScore").prop("readonly", true);
                $("#txtAuditStart").prop("readonly", true);
                $("#txtAuditEnd").prop("readonly", true);
                $("#txtIssuedOn").prop("readonly", true);
                $("#txtValidUpto").prop("readonly", true);
                $("#txtRenewalDate").prop("readonly", true);
                $("#drpAuditStatus").prop("disabled", true);
                $("#drpCheckedBy").prop("disabled", true);
                $("#drpVerifiedBy").prop("disabled", true);
                $("#txtRemarks").prop("readonly", true);
                $("#txtFileUpload").prop("disabled", true);
                $("#drpStatus").prop("disabled", true);
                $("#txtWindowPeriod").prop("readonly", true);
                $("#txtSiteId").prop("readonly", true);

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
                $("#drpAuditName").prop("disabled", false);
                $("#drpAuditType").prop("readonly", false);
                $("#drpProductCategory").prop("disabled", false);
                $("#drpCertificationBody").prop("disabled", false);
                $("#txtRegisterNo").prop("readonly", false);
                $("#txtGradeNScore").prop("readonly", false);
                $("#txtAuditStartDate").prop("readonly", false);
                $("#txtAuditEndDate").prop("readonly", false);
                $("#txtIssuedOn").prop("readonly", false);
                $("#txtValidUpto").prop("readonly", false);
                $("#txtRenewalDate").prop("readonly", false);
                $("#drpAuditStatus").prop("disabled", false);
                $("#drpCheckedBy").prop("disabled", false);
                $("#drpVerifiedBy").prop("disabled", false);
                $("#txtRemarks").prop("readonly", false);
                $("#txtFileUpload").prop("disabled", false);
                $("#drpStatus").prop("disabled", false);

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
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmSocialAudit").validate({

            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
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
                drpAuditName: {
                    required: true,
                },
                drpAuditType: {
                    required: true,
                },
                drpCertificationBody: {
                    required: true,
                },
                txtWindowPeriod: {
                    required: true,
                },
                txtGradeNScore: {
                    required: true,
                },
                // drpSite: {
                //     required: true,
                // },
                txtAuditStart: {
                    required: true,
                },
                txtAuditEnd: {
                    required: true,
                },
                txtIssuedOn: {
                    required: true,
                },
                txtValidUpto: {
                    required: true,
                },
                // txtRenewalDate: {
                //     required: true,
                // },
                drpAuditStatus: {
                    required: true,
                },
                drpStatus: {
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
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

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
                // alert(fileNames);
                // var productCategory = $("#drpProductCategory").val();
                // productCategory = productCategory.join(",");

                const requestData = {
                        methodType: 'formPost',
                        socialAuditID: $("#hdnSocialAuditID").val(),
                        plant: $("#drpArrivalPlant").val(),
                        auditName: $("#drpAuditName").val(),
                        auditType: $("#drpAuditType").val(),
                        certificationBody: $("#drpCertificationBody").val(),
                        windowPeriod: $('#txtWindowPeriod').val(),
                        gradeNScore: $("#txtGradeNScore").val(),
                        // site: $('#drpSite').val(),
                        site: $('#txtSiteId').val(),
                        auditStart: $('#txtAuditStart').val(),
                        auditEnd: $('#txtAuditEnd').val(),
                        issuedOn: $('#txtIssuedOn').val(),
                        validUpto: $('#txtValidUpto').val(),
                        renewalDate: $('#txtRenewalDate').val(),
                        auditStatus: $('#drpAuditStatus').val(),
                        status: $('#drpStatus').val(),
                        checkedBy: $('#drpCheckedBy').val(),
                        verifiedBy: $('#drpVerifiedBy').val(),
                        remarks: $('#txtRemarks').val(),
                };
                for (const key in requestData) {
                    fileData.append(key, requestData[key]);
                }
                $.ajax({
                    url: 'Ajax/ajax-social-audit-create.php',
                    type: 'POST',
                    data: fileData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "social-audit-create?SocialAuditID=" + data.LastInserted;
                            // $('#ModalInsType').modal('show');
                        } else {
                            alert(data.Error);
                        }
                        return false;
                    }
                });
                return false;
            }
        });

        $("#frmSocialAuditPayment").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
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
                txtPaymentDueDate: {
                    required: true,
                },
                txtTotalAmount: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {
                $.ajax({
                    url: 'Ajax/ajax-social-audit-create.php',
                    type: 'POST',
                    data: {
                        methodType: 'paymentPost',
                        socialAuditPaymentID: $("#hdnPaymentID").val(),
                        socialAuditID: $("#hdnSocialAuditID").val(),
                        purposeOfInvoice: $("#txtPurposeOfInvoice").val(),
                        paymentType: $("#drpPaymentType").val(),
                        invoiceNo: $("#txtInvoiceNo").val(),
                        invoidDate: $("#txtInvoieDate").val(),
                        invoiceValue: $("#txtInvoiceValue").val(),
                        paymentRemarks: $('#txtPaymentRemarks').val(),
                        others: $('#txtOthers').val(),
                        invoiceDueDate: $("#txtInvoiceDueDate").val(),
                        paymentDate: $("#txtPaymentDueDate").val(),
                        totalAmount: $('#txtTotalAmount').val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            showPaymentList();
                            $("#drpPaymentType").val("").trigger("change");
                            $("#txtPurposeOfInvoice,#txtInvoiceNo,#txtInvoieDate,#txtOthers,#txtPaymentDueDate,#txtInvoiceValue,#txtPaymentRemarks,#txtInvoiceDueDate,#txtTotalAmount").val("");
                        } else {
                            alert(data.Error);
                        }
                        return false;
                    }
                });
                return false;
            }
        });

        $(document).on("click", ".editBrkUp", function() {
            var paymentID = $(this).data("paymentid");
            $("#hdnPaymentID").val(paymentID);

            $(".btnPaymnet").text("Update Payment");
            $("#drpPaymentType").val($(this).closest("tr").find(".tdPaymentType").data('paymenttypeid')).trigger("change");
            $("#txtPurposeOfInvoice").val($(this).closest("tr").find(".tdPurposeOfInvoice").text());
            $("#txtInvoiceNo").val($(this).closest("tr").find(".tdInvoiceNumber").text());
            $("#txtInvoieDate").val($(this).closest("tr").find(".tdInvoiceDate").data('invoicedate'));
            $("#txtInvoiceValue").val($(this).closest("tr").find(".tdInvoiceValue").text());
            $("#txtInvoiceDueDate").val($(this).closest("tr").find(".tdInvoiceDueDate").data('invoiceduedate'));
            $("#txtPaymentDueDate").val($(this).closest("tr").find(".tdPaymentDate").data('paymentdate'));
            $("#txtTotalAmount").val($(this).closest("tr").find(".tdTotalAmount").text()); // tdTotalAmount
            $("#txtOthers").val($(this).closest("tr").find(".tdOthers").text());
            $("#txtPaymentRemarks").val($(this).closest("tr").find(".tdRemarks").text());
        });

        $(document).on("click", ".confirmPayment", function() {

            var paymentID = $(this).data("paymentid");
            var paymentDate = $(this).closest("tr").find(".txtPaymentDate").val();
            var paymentRemarks = $(this).closest("tr").find(".txtPaymentRemarks").val();

            $.ajax({
                url: 'Ajax/ajax-social-audit-create.php',
                type: 'POST',
                data: {
                    methodType: 'confirmPayment',
                    socialAuditPaymentID: paymentID,
                    socialAuditID: $("#hdnSocialAuditID").val(),
                    paymentRemarks: paymentRemarks,
                    paymentDate: paymentDate,
                },
                success: function(data) {
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

        $(document).on("click", ".deleteBrkUp", function() {

            var paymentID = $(this).data("paymentid");
            $("#hdnPaymentID").val(paymentID);
            if (confirm("Are you sure you want to delete this item ?") == true) {
                $.ajax({
                    url: 'Ajax/ajax-social-audit-create.php',
                    type: 'POST',
                    data: {
                        methodType: 'paymentDelete',
                        socialAuditID: $("#hdnSocialAuditID").val(),
                        socialAuditPaymentID: $("#hdnPaymentID").val(),
                    },
                    success: function(data) {
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
            }
        });
    </script>

    </body>

</html>