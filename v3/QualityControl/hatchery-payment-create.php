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

$PaymnetID = $_GET["PaymnetID"];

$bodyContent = array(
    "PaymnetID" => $PaymnetID
);

if ($PaymnetID != '') {
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
                                            <label for="drpFarmName">Name of Farm <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFarmName" id="drpFarmName" <?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?>>
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpFarmer">Farmer Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFarmer" id="drpFarmer" <?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?>>
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCertifyBody">Certifying Body<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpCertifyBody" id="drpCertifyBody" <?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?>>
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRegisterNo">Govt Registration No</label>
                                            <input type="text" class="form-control" name='txtRegisterNo' id="txtRegisterNo" placeholder="Enter Register Number" value="<?php echo $Data->OptionValue ?>" <?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBAPNumber">BAP Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtBAPNumber' id="txtBAPNumber" placeholder="Enter BAP Number" value="<?php echo $Data->OptionName ?>" <?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtProductionPerAnnum">Production Per Annum<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtProductionPerAnnum' id="txtProductionPerAnnum" placeholder="Enter Production Per Cycle" value="<?php echo $Data->OptionName ?>" <?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAuditDate" class="form-label">Audit Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtAuditDate" name="txtAuditDate" value='<?php echo $Data->SamplDt ?>' <?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?> />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtExpiryOn" class="form-label">Expiry on <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtExpiryOn" name="txtExpiryOn" value='<?php echo $Data->SamplDt ?>' <?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?> />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRenewalDate" class="form-label">Renewal Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtRenewalDate" name="txtRenewalDate" value='<?php echo $Data->SamplDt ?>' <?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?> />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtVerifiedBy">Verified by<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtVerifiedBy' id="txtVerifiedBy" placeholder="Enter Audit Category Option Name" value="<?php echo $Data->OptionName ?>" <?php echo isset($PaymnetID) && $PaymnetID ? 'readonly' : ''; ?>>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus" <?php echo isset($PaymnetID) && $PaymnetID ? 'disabled' : ''; ?>>
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

                                        <div class="text-center">
                                            <input type="hidden" id="hdnPaymnetID" name="hdnPaymnetID" value="<?php echo $PaymnetID ?>" />
                                            <a href="hatchery-audit-list" class="btn btn-danger">Close</a>
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
                    title: 'Hatchery Payment List <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }


        function showList() {
            $.ajax({
                url: 'Ajax/ajax-hatchery-payment-create.php',
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
                drpFarmName: {
                    required: true,
                },
                drpFarmer: {
                    required: true,
                },
                drpCertifyBody: {
                    required: true,
                },
                txtRegisterNo: {
                    required: true,
                },
                txtBAPNumber: {
                    required: true,
                },
                txtProductionPerAnnum: {
                    required: true,
                },
                txtAuditDate: {
                    required: true,
                },
                txtExpiryOn: {
                    required: true,
                },
                txtRenewalDate: {
                    required: true,
                },
                txtVerifiedBy: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {
                $.ajax({
                    url: 'Ajax/ajax-hatchery-payment-create.php',
                    type: 'POST',
                    data: {
                        methodType: "formPost",
                        farmName: $("#drpFarmName").val(),
                        farmer: $("#drpFarmer").val(),
                        certifyBody: $("#drpCertifyBody").val(),
                        registerNo: $("#txtRegisterNo").val(),
                        bapNumber: $("#txtBAPNumber").val(),
                        productPerAnnum: $("#txtProductionPerAnnum").val(),
                        auditDate: $("#txtAuditDate").val(),
                        expiryOn: $("#txtExpiryOn").val(),
                        renewalDate: $("#txtRenewalDate").val(),
                        verifiedBy: $("#txtVerifiedBy").val(),
                        status: $("#drpStatus").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            // window.location.href = "hatchery-audit-list";
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