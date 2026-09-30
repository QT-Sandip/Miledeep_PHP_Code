<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_CERTIFICATION_BODY_MASTER;

$CertificationBodyID = $_GET["CertificationBodyID"];

$bodyContent = array(
    "CertificationBodyID" => $CertificationBodyID
);
if ($CertificationBodyID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    $Data = $result->CertificationBodyData[0];
}

$api = $api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
// $resProductionPlant = $resDropdownsAPI->Data->Plant;
$resAuditCategory = $resDropdownsAPI->Data->AuditCategory;

$ProductionPlant = $_SESSION['UnitID'];
// echo $productionPlant;
// exit;
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
                                <form role="form" method="post" id="frmCerticationBody">
                                    <div class="row">

                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="drpArrivalPlant" class="form-label">Production Plant<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpArrivalPlant" id="drpArrivalPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID; ?>"
                                                        <?php echo ($Data->PlantID == $Item->ProcessingStationID) ? 'selected' : '' ?>>
                                                        <?php echo $Item->FullName . ' - ' . $Item->StationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div> -->

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpNameOfAudit" class="form-label">Name of Audit<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpNameOfAudit"
                                                id="drpNameOfAudit">
                                                <option value=""> Select</option>
                                                <?php foreach ($resAuditCategory as $Item) { ?>
                                                    <!-- <?php //if ($Item->PlantID == $ProductionPlant) { ?> -->
                                                    <option value="<?php echo $Item->AuditID; ?>" <?php echo ($Data->AuditNameID == $Item->AuditID) ? 'selected' : '' ?>>
                                                        <?php echo $Item->CategoryName; ?></option>
                                                    <? php// } ?>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCerticationBodyName">Certification Body Name<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtCerticationBodyName'
                                                id="txtCerticationBodyName" placeholder="Enter Certification Body Name"
                                                value="<?php echo $Data->CertificationBodyName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['CertificationBodyID']) && !empty($_GET['CertificationBodyID'])) { ?>
                                                    <option value="1" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>
                                                        Active</option>
                                                    <option value="0" <?php echo ($Data->Status == "0") ? 'selected' : ''; ?>>
                                                        In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">InActive</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnCertificationBodyID"
                                                name="hdnCertificationBodyID"
                                                value="<?php echo $CertificationBodyID ?>" />
                                            <a href="certification-body-master-list" class="btn btn-danger">Close</a>
                                            <?php if ($CertificationBodyID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit"
                                                    id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit"
                                                    id="submit">Update</button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </form>
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
        $(document).ready(function () {

            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant != 0) {
                $("#drpArrivalPlant").prop('disabled', true);
                $("#drpArrivalPlant").val(productionPlant).trigger('change');

            } else {
                $("#drpArrivalPlant").prop('disabled', false);
                $("#drpArrivalPlant").val("").trigger('change');
            }
        });

        jQuery.validator.addMethod(
            "noSpace",
            function (value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmCerticationBody").validate({

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
                // drpArrivalPlant: {
                //     required: true,
                //     noSpace: true
                // },
                drpNameOfAudit: {
                    required: true,
                },
                txtCerticationBodyName: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                }
            },
            errorPlacement: function () {
                return false;
            },
            submitHandler: function () {
                $.ajax({
                    url: 'Ajax/ajax-certification-body-master-create.php',
                    type: 'POST',
                    data: {
                        certificationBodyID: $("#hdnCertificationBodyID").val(),
                        certicationBodyName: $("#txtCerticationBodyName").val(),
                        status: $("#drpStatus").val(),
                        //arrivalPlant: $("#drpArrivalPlant").val(),
                        nameOfAudit: $("#drpNameOfAudit").val(),
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "certification-body-master-list";
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