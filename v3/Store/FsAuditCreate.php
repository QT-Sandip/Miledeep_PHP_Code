<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resPlant = $resDropdownsAPI->Data->ProcessingPlants;

$apiList = API_FS_AUDIT;

$FsaID = trim($_GET['FsaID']);

$bodyContent = array(
    "FsaID" => $FsaID,
);

if ($FsaID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}


// $CurrentMonth = date('m');
// $CurrentYear = date('Y');

// if ($CurrentMonth > 3) {
//     $PastFY = $CurrentYear - 1;
//     $CurrentFY = $CurrentYear;
// } else {
//     $PastFY = $CurrentYear - 2;
//     $CurrentFY = $CurrentYear - 1;
// }


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* .select2-container--open {
                z-index: 9999999
            }             */
    </style>

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
                                <form role="form" method="post" id="frmAudit">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPlant">Plant Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPlant" id="drpPlant">
                                                <option value="" <?php  echo ($Data->StationName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resPlant as $List) { ?>
                                                    <option value="<?php echo $List->ProcessingStationID ?>" 
                                                    <?php  echo ($Data->StationName == $List->StationName) ? 'selected' : '';?>>
                                                     <?php echo $List->StationName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAuditName">Audit Name<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpAuditName" id="drpAuditName" value='<?php  echo $Data->AuditNameID ?>'>
                                                <option value="" <?php  echo ($Data->AuditNameID == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1" <?php  echo ($Data->AuditNameID == '1') ? 'selected' : ''; ?>>APCB</option>
                                                <option value="2" <?php  echo ($Data->AuditNameID == '2') ? 'selected' : ''; ?>>Plant Approval</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAuditType">Audit Type<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpAuditType" id="drpAuditType" value='<?php  echo $Data->AuditTypeID ?>'>
                                                <option value="" <?php  echo ($Data->AuditTypeID == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1" <?php  echo ($Data->AuditTypeID == '1') ? 'selected' : ''; ?>>FOOD</option>
                                                <option value="2" <?php  echo ($Data->AuditTypeID == '2') ? 'selected' : ''; ?>>SOCIAL</option>
                                                <option value="3" <?php  echo ($Data->AuditTypeID == '3') ? 'selected' : ''; ?>>GOVERNMENT</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCertAuth">Certificate Authority<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpCertAuth" id="drpCertAuth" value='<?php  echo $Data->CertAuthorityID ?>'>
                                                <option value="" <?php  echo ($Data->CertAuthorityID == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1" <?php  echo ($Data->CertAuthorityID == '1') ? 'selected' : ''; ?>>Authority1</option>
                                                <option value="2" <?php  echo ($Data->CertAuthorityID == '2') ? 'selected' : ''; ?>>Authority2</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRegiNo" class="form-label">Registration Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtRegiNo" name="txtRegiNo" placeholder="Enter Registration Number" value='<?php  echo $Data->RegistrartionNo ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCertNo" class="form-label">Certificate Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtCertNo" name="txtCertNo" placeholder="Enter Registration Number" value='<?php  echo $Data->CertificateNo ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtStartDate" class="form-label">Audit Start Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtStartDate" name="txtStartDate" value='<?php  echo $Data->AuditStartDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtEndDate" class="form-label">Audit End Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtEndDate" name="txtEndDate" value='<?php  echo $Data->AuditEndDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAuditProg">Audit Program<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpAuditProg" id="drpAuditProg" value='<?php  echo $Data->AuditProgram ?>'>
                                                <option value="" <?php  echo ($Data->AuditProgram == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1 " <?php  echo ($Data->AuditProgram == '1') ? 'selected' : ''; ?>>Announced</option>
                                                <option value="0" <?php  echo ($Data->AuditProgram == '0') ? 'selected' : ''; ?>>UnAnnounced</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIssuedDate" class="form-label">Issued On<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtIssuedDate" name="txtIssuedDate" value='<?php echo $Data->IssuedOnDt ?>' />
                                        </div>
                                        
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtValidDate" class="form-label">Valid Upto<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtValidDate" name="txtValidDate" value='<?php echo $Data->ValidUpTo ?>' />
                                        </div>
                                        
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRenewaldDate" class="form-label">Renewal Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtRenewaldDate" name="txtRenewaldDate" value='<?php echo $Data->RenewaLDt ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAuditGrade">Audit Grade<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpAuditGrade" id="drpAuditGrade" value='<?php echo $Data->AuditGrade ?>'>
                                                <option value="" <?php echo ($Data->AuditGrade == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1" <?php echo ($Data->AuditGrade == '1') ? 'selected' : ''; ?>>GradeA</option>
                                                <option value="2" <?php echo ($Data->AuditGrade == '2') ? 'selected' : ''; ?>>GradeB</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                            <?php if (isset($_GET['FsaID']) && !empty($_GET['FsaID'])) { ?>
                                                    echo '<option value="1" <?php (($Data->UnitStatus == 1) ? 'selected' : '') ?>>Active</option>
                                                    <option value="0" <?php (($Data->UnitStatus == 0) ? 'selected' : '') ?>>In-Active</option>';
                                                <?php } else { ?>
                                                    echo '
                                                    <option value="">All</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In-Active</option>';
                                              <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $FsaID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="FsAuditList" class="btn btn-danger">Close</a>
                                            <?php if ($FsaID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>
                        <!-- <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

                                </div>
                            </div>
                        </div> -->
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
        var fsaID = "<?php echo isset($_GET["FsaID"]) && !empty($_GET["FsaID"]) ? $_GET["FsaID"] : "" ?>";

        function fnShowDataTable() {
            table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable',
                    title: 'GS Chemical PO -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        $(document).ready(function() {
            $.ajax({
                url: 'Ajax/AjaxFsAuditCreate.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    fsaID: fsaID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        });

        $("#frmAudit").validate({

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
                drpPlant: {
                    required: true,
                },
                drpAuditName: {
                    required: true,
                },
                drpAuditType: {
                    required: true,
                },
                drpCertAuth: {
                    required: true,
                },
                txtRegiNo: {
                    required: true,
                },
                txtCertNo: {
                    required: true,
                },
                txtStartDate: {
                    required: true,
                },
                txtEndDate: {
                    required: true,
                },
                drpAuditProg: {
                    required: true,
                },
                txtIssuedDate: {
                    required: true,
                },
                txtValidDate: {
                    required: true,
                },
                txtRenewaldDate: {
                    required: true,
                },
                drpAuditGrade: {
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
                    url: 'Ajax/AjaxFsAuditCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        fsaID: fsaID,
                        plant: $("#drpPlant").val(),
                        auditName: $("#drpAuditName").val(),
                        auditType: $("#drpAuditType").val(),
                        certAuth: $("#drpCertAuth").val(),
                        regiNo: $("#txtRegiNo").val(),
                        certNo: $("#txtCertNo").val(),
                        startDate: $("#txtStartDate").val(),
                        endDate: $("#txtEndDate").val(),
                        auditProg: $("#drpAuditProg").val(),
                        issuedDate: $("#txtIssuedDate").val(),
                        validDate: $("#txtValidDate").val(),
                        renewalDate: $("#txtRenewaldDate").val(),
                        auditGrade: $("#drpAuditGrade").val(),
                        status: $("#drpStatus").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "FsAuditList.php";
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