<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resFarmName = $resDropdownsAPI->Data->FarmName;
$resHatchery = $resDropdownsAPI->Data->Hatchery;
$resPlant = $resDropdownsAPI->Data->ProcessingPlants;

$apiList = API_FS_AUDIT_MILL;

$AuditID = trim($_GET['AuditID']);

$bodyContent = array(
    "AuditID" => $AuditID
);

if ($AuditID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}
// echo json_encode($result);

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
                                <form role="form" method="post" id="frmAuditMill">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAuditType">Audit Type<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpAuditType" id="drpAuditType" value='<?php echo $Data->AuditType ?>'>
                                                <option value="" <?php echo ($Data->AuditType == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1" <?php echo ($Data->AuditType == '1') ? 'selected' : ''; ?>>Farm</option>
                                                <option value="2" <?php echo ($Data->AuditType == '2') ? 'selected' : ''; ?>>Hatchery</option>
                                                <option value="3" <?php echo ($Data->AuditType == '3') ? 'selected' : ''; ?>>Feed Mill</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpFarmer">Farmer Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFarmer" id="drpFarmer">
                                                <option value="" <?php echo ($Data->FarmerName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resFarmName as $List) { ?>
                                                    <option value="<?php echo $List->TraceID ?>" <?php echo ($Data->FarmerName == $List->FarmerName) ? 'selected' : ''; ?>>
                                                        <?php echo $List->FarmerName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpHatchery">Hatchery Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpHatchery" id="drpHatchery">
                                                <option value="" <?php echo ($Data->Hatchery == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resHatchery as $List) { ?>
                                                    <option value="<?php echo $List->HatcheryID ?>" <?php echo ($Data->Hatchery == $List->Hatchery) ? 'selected' : ''; ?>>
                                                        <?php echo $List->Hatchery ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCertBody">Certified Body<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpCertBody" id="drpCertBody" value='<?php echo $Data->CertifiedBody ?>'>
                                                <option value="" <?php echo ($Data->CertifiedBody == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1" <?php echo ($Data->CertifiedBody == '1') ? 'selected' : ''; ?>>BAP</option>
                                                <option value="2" <?php echo ($Data->CertifiedBody == '2') ? 'selected' : ''; ?>>AOC</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpAssFactory">Associated Factory<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpAssFactory" id="drpAssFactory">
                                                <option value="" <?php echo ($Data->StationName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resPlant as $List) { ?>
                                                    <option value="<?php echo $List->ProcessingStationID ?>" <?php echo ($Data->StationName == $List->StationName) ? 'selected' : ''; ?>>
                                                        <?php echo $List->StationName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpGovtAuth">Government Authority<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpGovtAuth" id="drpGovtAuth" value='<?php echo $Data->AuthorityID ?>'>
                                                <option value="" <?php echo ($Data->AuthorityID == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1" <?php echo ($Data->AuthorityID == '1') ? 'selected' : ''; ?>>MPEDA</option>
                                                <option value="2" <?php echo ($Data->AuthorityID == '2') ? 'selected' : ''; ?>>EIA</option>
                                                <option value="3" <?php echo ($Data->AuthorityID == '3') ? 'selected' : ''; ?>>MINISTRY OF FISHIRIES</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRegiNo" class="form-label">Gov Registration Number <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtRegiNo" name="txtRegiNo" placeholder="Enter Registration Number" value='<?php echo $Data->RegistrationNo ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBAPNo" class="form-label">BAP Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBAPNo" name="txtBAPNo" placeholder="Enter BAP Number" value='<?php echo $Data->BAPNo ?>' />
                                        </div>
                                        
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTonnage" class="form-label">Tonnage/Annum<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTonnage" name="txtTonnage" placeholder="Enter Tonnage/Annum Number" value='<?php echo $Data->ProductionPerAnnum ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtWater" class="form-label">Water Spread Area<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtWater" name="txtWater" placeholder="Enter Water Spread Area" value='<?php echo $Data->WaterSpreadArea ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAuditDate" class="form-label">Audit Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtAuditDate" name="txtAuditDate" value='<?php echo $Data->AuditDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIssuedDate" class="form-label">Issued On Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtIssuedDate" name="txtIssuedDate" value='<?php echo $Data->IssuedOn ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtValidDate" class="form-label">Valid Upto<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtValidDate" name="txtValidDate" value='<?php echo $Data->ValidtyUpto ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['AuditID']) && !empty($_GET['AuditID'])) { ?>
                                                   <option value="1" <?php (($Data->Status == 1) ? 'selected' : '') ?>>Active</option>
                                                    <option value="2" <?php (($Data->Status == 2) ? 'selected' : '') ?>>In-Active</option>
                                                    <option value="3" <?php (($Data->Status == 3) ? 'selected' : '') ?>>Renewal</option>
                                                <?php } else { ?>
                                                    <option value="">All</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="2">In-Active</option>
                                                    <option value="3">Renewal</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $AuditID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="FsAuditMillList" class="btn btn-danger">Close</a>
                                            <?php if ($AuditID == '') { ?>
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
        var auditID = "<?php echo isset($_GET["AuditID"]) && !empty($_GET["AuditID"]) ? $_GET["AuditID"] : "" ?>";

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
                    auditID: auditID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        });

        $("#frmAuditMill").validate({

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
                drpAuditType: {
                    required: true,
                },
                drpFarmer: {
                    required: true,
                },
                drpHatchery: {
                    required: true,
                },
                drpCertBody: {
                    required: true,
                },
                drpAssFactory: {
                    required: true,
                },
                drpGovtAuth: {
                    required: true,
                },
                txtRegiNo: {
                    required: true,
                },
                txtBAPNo: {
                    required: true,
                },
                txtTonnage: {
                    required: true,
                },
                txtWater: {
                    required: true,
                },
                txtAuditDate: {
                    required: true,
                },
                txtIssuedDate: {
                    required: true,
                },
                txtValidDate: {
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
                    url: 'Ajax/AjaxFsAuditMillCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        auditID : auditID ,
                        auditType: $("#drpAuditType").val(),
                        farmer: $("#drpFarmer").val(),
                        hatchery: $("#drpHatchery").val(),
                        certBody: $("#drpCertBody").val(),
                        assocFactory: $("#drpAssFactory").val(),
                        govtAuth: $("#drpGovtAuth").val(),
                        regiNo: $("#txtRegiNo").val(),
                        bapNo: $("#txtBAPNo").val(),
                        tonnage: $("#txtTonnage").val(),
                        water: $("#txtWater").val(),
                        auditDate: $("#txtAuditDate").val(),
                        issuedDate: $("#txtIssuedDate").val(),
                        validDate: $("#txtValidDate").val(),
                        status: $("#drpStatus").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "FsAuditMillList.php";
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