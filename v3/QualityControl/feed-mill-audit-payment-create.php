<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_QC_DROPDOWNS;

$reslist = CallAPI($api, "GET", "", "");

$resAuditCategory = $reslist->Data->AuditCategory;

// echo json_encode($resAuditCategory, JSON_PRETTY_PRINT);
// exit;

$apiList = API_AUDIT_CATEGORIES_OPTIONS;

$PaymentID = $_GET["Payment$PaymentID"];

$bodyContent = array(
    "Payment$PaymentID" => $PaymentID
);

if ($PaymentID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;
    $Data = $result->Data[0];
}

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
                                <form role="form" method="post" id="frmHatcheryPayment">
                                    <div class="row">

                                    <div class="col col-md-3 mb-3">
                                            <label for="txtNameOfAudit">Name Of Audit<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtNameOfAudit' id="txtNameOfAudit" placeholder="Enter Name Of Farm" value="<?php echo $Data->OptionName ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFarmerName">Farmer Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtFarmerName' id="txtFarmerName" placeholder="Enter Farmer Name" value="<?php echo $Data->OptionName ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCertifyingBody">Certifying Body<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtCertifyingBody' id="txtCertifyingBody" placeholder="Enter Certifying Body" value="<?php echo $Data->OptionName ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRegisterNo">Govt Registration No</label>
                                            <input type="text" class="form-control" name='txtRegisterNo' id="txtRegisterNo" placeholder="Enter Register Number" value="<?php echo $Data->OptionValue ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCertificateNo">Certificate No<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtCertificateNo' id="txtCertificateNo" placeholder="Enter Certificate No" value="<?php echo $Data->OptionName ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>


                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBAPNumber">BAP Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtBAPNumber' id="txtBAPNumber" placeholder="Enter BAP Number" value="<?php echo $Data->OptionName ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus"<?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?>>
                                                <?php if (isset($_GET['HatcheryID']) && !empty($_GET['HatcheryID'])) { ?>
                                                    <option value="1" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>Active</option>
                                                    <option value="0" <?php echo ($Data->Status == "0") ? 'selected' : ''; ?>>In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1">Active</option>
                                                    <option value="0">In-Active</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAuditDate" class="form-label">Audit Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtAuditDate" name="txtAuditDate" value="<?php echo $Data->SamplDt ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?> >
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIssuedOn">Issued On<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtIssuedOn" id="txtIssuedOn" value="<?php echo $Data->SamplDt ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCheckedBy">Checked By<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtCheckedBy" id="txtCheckedBy" value="<?php echo $Data->SamplDt ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?>>
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtVerifiedBy">Verified By<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtVerifiedBy" id="txtVerifiedBy" value="<?php echo $Data->SamplDt ?>"<?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?>>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnPaymnetID" name="hdnPaymnetID" value="<?php echo $PaymnetID ?>" />
                                            <a href="feed-mill-audit-list" class="btn btn-danger">Close</a>
                                            <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
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
                    title: 'Feed Mill Payment List <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }
        function showList() {
            $.ajax({
                url: 'Ajax/ajax-feed-mill-audit-payment-create.php',
                type: 'POST',
                data: {
                    methodType: "getList",
                    paymentID: $("#hdnPaymnetID").val(),
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();

                    return false;
                }
            });
        }

        $(document).ready(function() {
            showList();
        });


        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmHatcheryPayment").validate({

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
                txtNameOfFarm: {
                    required: true,
                },
                txtFarmerName: {
                    required: true,
                },
                drpCertifyBody: {
                    required: true,
                },
                txtGovtRegistrationNo: {
                    required: true,
                },
                txtBAPNumber: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                },
                txtAuditDate: {
                    required: true,
                },
                txtIssuedOn: {
                    required: true,
                },
                txtCheckedBy: {
                    required: true,
                },
                txtVerifiedBy: {
                    required: true,
                },
              
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {
                $.ajax({
                    url: 'Ajax/ajax-feed-mill-audit-payment-create.php',
                    type: 'POST',
                    data: {
                        methodType: "formPost",
                        nameOfAudit: $("#txtNameOfAudit").val(),
                        farmerName: $("#txtFarmerName").val(),
                        certifyBody: $("#drpCertifyBody").val(),
                        govtregistrationNo: $("#txtGovtRegistrationNo").val(),
                        bapNumber: $("#txtBAPNumber").val(),
                        status: $("#drpStatus").val(),
                        auditDate: $("#txtAuditDate").val(),
                        issuedOn: $("#txtIssuedOn").val(),
                        checkedBy: $("#txtCheckedBy").val(),
                        verifiedBy: $("#txtVerifiedBy").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            // window.location.href = "feed-mill-audit-list";
                            $("#divDataList").html(data);
                            fnShowDataTable();


                        } else {
                            alert(data.Error);
                        }
                        return false;
                    }
                });
                return false;
            }
        });
    </script>

    </body>

</html>