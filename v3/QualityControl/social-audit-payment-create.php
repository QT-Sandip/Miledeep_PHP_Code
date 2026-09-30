<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_QC_DROPDOWNS;

$reslist = CallAPI($api, "GET", "", "");

$resAuditCategory = $reslist->Data->AuditCategory;

// // echo json_encode($resAuditCategory, JSON_PRETTY_PRINT);
// // exit;

$apiList = API_AUDIT_CATEGORIES_OPTIONS;

$AuditOptionID = $_GET["AuditOptionID"];

$bodyContent = array(
    "AuditOptionID" => $AuditOptionID
);

if ($AuditOptionID != '') {
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
                                <form role="form" method="post" id="frmCreateAuditCategories">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpFarmName">Name of Farm/IOM<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFarmName" id="drpFarmName">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpFarmerName">Farmer Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFarmerName" id="drpFarmerName">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCertifyingBody">Certifying Body<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpCertifyingBody" id="drpCertifyingBody">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAssciatedFactory">Associated Factory<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtAssciatedFactory' id="txtAssciatedFactory" placeholder="Enter Associated Factory" value="<?php echo $Data->OptionName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="textGovtIssuingAuthority">Govt.Issuing Authority<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="textGovtIssuingAuthority" id="textGovtIssuingAuthority">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGovtRegisterNo">Govt.Registration No</label>
                                            <input type="text" class="form-control" name='txtGovtRegisterNo' id="txtGovtRegisterNo" placeholder="Enter GovtRegisterNo" value="<?php echo $Data->OptionValue ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBapNumber">BAP Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtBapNumber' id="txtBapNumber" placeholder="Enter BAP Number" value="<?php echo $Data->OptionName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTonnagePerAnnum" class="form-label">Tonnage PerAnnum<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTonnagePerAnnum" name="txtTonnagePerAnnum" placeholder="Enter TonnagePerAnnum" value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtWaterSpreadArea" class="form-label">Water Spread Area<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtWaterSpreadArea" name="txtWaterSpreadArea" placeholder="Enter water spread Area" value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['AuditOptionID']) && !empty($_GET['AuditOptionID'])) { ?>
                                                    <option value="1" style="color: green;" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>Fresh Audit</option>
                                                    <option value="2" style="color: blue;" <?php echo ($Data->Status == "2") ? 'selected' : ''; ?>>Renewal Audit</option>
                                                    <option value="3" style="color: red;" <?php echo ($Data->Status == "3") ? 'selected' : ''; ?>>Re Certification</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" style="color: green;">Active</option>
                                                    <option value="2" style="color: blue;">InActive</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAuditDate" class="form-label">Audit Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtAuditDate" name="txtAuditDate" value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIssuedOn" class="form-label">Issued on <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtIssuedOn" name="txtIssuedOn"  value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtValidityUpto " class="form-label">Validity Upto <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtValidityUpto" name="txtValidityUpto"  value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtVerifiedby2" class="form-label">Verified by<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtVerifiedby2" name="txtVerifiedby2" placeholder="Enter Verified by" value='<?php echo $Data->SamplDt ?>' />
                                        </div>
                                        
                                            <div class="text-center">
                                                <input type="hidden" id="hdnAuditOptionID" name="hdnAuditOptionID" value="<?php echo $AuditOptionID ?>" />
                                                <a href="audit-categories-options-list" class="btn btn-danger">Close</a>
                                                <a href="audit-categories-options-list" class="btn btn-success">Add Payments</a>
                                                <?php if ($AuditOptionID == '') { ?>
                                                    <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
                                                <?php } else { ?>
                                                    <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                                <?php } ?>

                                            </div>
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
                    title: 'Food Audit Payment List <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        var id = "<?php echo isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "" ?>";

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmCreateAuditCategories").validate({

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
                drpFarmerName: {
                    required: true,
                },
                drpCertifyingBody: {
                    required: true,
                },
                txtAssciatedFactory: {
                    required: true,
                },
                textGovtIssuingAuthority: {
                    required: true,
                },
                txtGovtRegisterNo: {
                    required: true,
                },
                txtBapNumber: {
                    required: true,
                },
                txtTonnagePerAnnum: {
                    required: true,
                },
                txtWaterSpreadArea: {
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
                txtValidityUpto: {
                    required: true,
                },
                txtVerifiedby2: {
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
                        farmName: $("#drpFarmName").val(),
                        farmerName: $("#drpFarmerName").val(),
                        certifyingBody: $("#drpCertifyingBody").val(),
                        assciatedFactory: $("#txtAssciatedFactory").val(),
                        govtIssuingAuthority: $("#textGovtIssuingAuthority").val(),
                        govtRegisterNo: $('#txtGovtRegisterNo').val(),
                        bapNumber: $("#txtBapNumber").val(),
                        tonnagePerAnnum: $('#txtTonnagePerAnnum').val(),
                        WaterSpreadArea: $('#txtWaterSpreadArea').val(),
                        status: $('#drpStatus').val(),
                        auditDate:$('#txtAuditDate').val(),
                        issuedOn: $('#txtIssuedOn').val(),
                        validityUpto: $('#txtValidityUpto').val(),
                        verifiedby2: $('#txtVerifiedby2').val(),

                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            // window.location.href = "audit-categories-options-list";
                            $("#divDataList").html(data);
                            fnShowDataTable();
                        } 
                        else {
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