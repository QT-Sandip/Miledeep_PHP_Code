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
                                            <label for="txtNameOfFarm">Name Of Farm<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtNameOfFarm' id="txtNameOfFarm" placeholder="Enter Name Of Farm" value="<?php echo $Data->OptionName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFarmerName">Farmer Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtFarmerName' id="txtFarmerName" placeholder="Enter Farmer Name" value="<?php echo $Data->OptionName ?>">
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFarmerName">Farmer Address<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtFarmerName' id="txtFarmerName" placeholder="Enter Farmer Name" value="<?php echo $Data->OptionName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRegistrationNo">Registration No<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtRegistrationNo' id="txtRegistrationNo" placeholder="Enter Registration No" value="<?php echo $Data->OptionName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAssociatedFactory">Associated Factory<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtAssociatedFactory' id="txtAssociatedFactory" placeholder="Enter Associated Factory" value="<?php echo $Data->OptionName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpGovtIssuingAuthority">Govt Issuing Authority</label>
                                            <input type="text" class="form-control" name='drpGovtIssuingAuthority' id="drpGovtIssuingAuthority" placeholder="Enter Govt Issuing Authority" value="<?php echo $Data->OptionValue ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpSite">Site<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSite" id="drpSite">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBAPNumber" class="form-label">BAP Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBAPNumber" name="txtBAPNumber" placeholder="Enter BAP Number" value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtASCNO" class="form-label">ASC NO<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtASCNO" name="txtASCNO"  placeholder="Enter ASC NO" value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTonnagePerCycle " class="form-label">Tonnage Per Cycle <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTonnagePerCycle" name="txtTonnagePerCycle" placeholder="Enter Tonnage Per Cycle " value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtWaterSpreadArea" class="form-label">Water Spread Area <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtWaterSpreadArea" name="txtWaterSpreadArea"  placeholder="Enter Water Spread Area" value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpSpecies">Species<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSpecies" id="drpSpecies">
                                                <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resAuditCategory as $item) { ?>
                                                    <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditID == $item->AuditID) ? 'selected' : ''; ?>><?php echo $item->CategoryName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
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
                                            <label for="txtStatus" class="form-label">Status<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtStatus" name="txtStatus"  placeholder="Enter Status" value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCertificationNo" class="form-label">Certification No<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtCertificationNo" name="txtCertificationNo"  placeholder="Enter Certification No" value='<?php echo $Data->SamplDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAuditDate">Audit Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtAuditDate" id="txtAuditDate" value='<?php echo $Data->SamplDt ?>'/>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIssuedOn">Issued On<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtIssuedOn" id="txtIssuedOn" value='<?php echo $Data->SamplDt ?>'/>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtValidityUpto">Validity Upto<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtValidityUpto" id="txtValidityUpto" value='<?php echo $Data->SamplDt ?>'/>
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtVerifiedBy1">Verified By 1<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtVerifiedBy1" id="txtVerifiedBy1" value='<?php echo $Data->SamplDt ?>'/>
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtVerifiedBy">Verified By<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" name="txtVerifiedBy" id="txtVerifiedBy" value='<?php echo $Data->SamplDt ?>'/>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="farm-details-list" class="btn btn-danger">Close</a>
                                            <?php if ($ID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
                                        </div>

                                        <!-- <div class="text-center">
                                            <input type="hidden" id="hdnAuditOptionID" name="hdnAuditOptionID" value="<?php echo $AuditOptionID ?>" />
                                            <a href="hatchery-audit-list" class="btn btn-danger">Close</a>
                                            <button class="btn btn-success" name="btnAddPayment" id="btnAddPayment"><i class="fa fa-plus"></i> Add Payments</button>
                                            <?php if ($AuditOptionID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
                                        </div> -->
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                </div>
                                <input type="hidden" id="hdnActionType" />
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
            rules:  {
                txtNameOfFarm: {
                    required: true,
                },
                txtFarmerName: {
                    required: true,
                },
                txtFarmerAddress: {
                    required: true,
                },
                txtRegistrationNo: {
                    required: true,
                },
                txtAssociatedFactory: {
                    required: true,
                },
                drpGovtIssuingAuthority: {
                    required: true,
                },
                drpSite: {
                    required: true,
                },
                txtBAPNumber: {
                    required: true,
                },
                txtASCNO: {
                    required: true,
                },
                txtTonnagePerCycle: {
                    required: true,
                },
                txtWaterSpreadArea: {
                    required: true,
                },
                drpSpecies: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                },
                txtStatus: {
                    required: true,
                },
                txtCertificationNo: {
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
                txtVerifiedBy1: {
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
                    url: 'Ajax/ajax-farm-details-create.php',
                    type: 'POST',
                    data: {
                        auditOptionID: $("#hdnAuditOptionID").val(),
                        nameOfFarm: $("#txtNameOfFarm").val(),
                        farmerName: $("#txtFarmerName").val(),
                        farmerAddress: $("#drpFarmerAddress").val(),
                        registrationNo: $("#txtRegistrationNo").val(),
                        associatedFactory: $("#txtAssociatedFactory").val(),
                        govtIssuingAuthority: $("#txtGovtIssuingAuthority").val(),
                        site: $("#drpSite").val(),
                        bapNumber: $("#txtBAPNumber").val(),
                        ascNo: $("#txtASCNo").val(),
                        tonnagePerCycle: $("#txtTonnagePerCycle").val(),
                        waterSpreadArea: $("#txtWaterSpreadArea").val(),
                        species: $("#drpSpecies").val(),
                        status: $("#drpStatus").val(),
                        status: $("#txtStatus").val(),
                        auditDate: $("#txtAuditDate").val(),
                        issuedOn: $("#txtIssuedOn").val(),
                        validityUpto: $("#txtValidityUpto").val(),
                        verifiedBy1: $("#txtVerifiedBy1").val(),
                        verifiedBy: $("#txtVerifiedBy").val()                        
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                           // alert(data.Msg);
                           alert('sucess');
                            // window.location.href = "audit-farm-list";
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