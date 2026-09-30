<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_FARM_MASTER;

$FarmID = $_GET["FarmID"];

$bodyContent = array(
    "FarmID" => $FarmID
);
if ($FarmID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;
    $Data = $result->FarmData[0];
}

$api = $api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resAuditCategory = $resDropdownsAPI->Data->AuditCategory;
$resFarms = $resDropdownsAPI->Data->Farms;

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
                                        <div class="col col-md-3 mb-3">
                                            <label for="drpFarm">Farm <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFarm" id="drpFarm">
                                                <option value="" <?php echo ($Data->FarmName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resFarms as $Item) { ?>
                                                    <option value="<?php echo $Item->OptionID; ?>" <?php echo ($Data->FarmName == $Item->OptionID) ? 'selected' : '' ?>>
                                                        <?php echo $Item->OptionName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRegistrationNo">Farm Registration No<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtRegistrationNo' id="txtRegistrationNo" placeholder="Enter Registration Number" value="<?php echo $Data->FarmRegistrationNo ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFarmerName">Farmer Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtFarmerName' id="txtFarmerName" placeholder="Enter Fermer Name" value="<?php echo $Data->FarmerName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFarmerAddress">Farmer Address<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtFarmerAddress' id="txtFarmerAddress" placeholder="Enter Farmer Address" value="<?php echo $Data->FarmerAddress ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFarmerPhonNumber">Farmer Phone Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtFarmerPhonNumber' id="txtFarmerPhonNumber" placeholder="Enter Phone Number" value="<?php echo $Data->PhoneNumber ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTonnageValue">Tonnage Value<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtTonnageValue' id="txtTonnageValue" placeholder="Enter Tonnage Value" value="<?php echo $Data->TonnageValue ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['FarmID']) && !empty($_GET['FarmID'])) { ?>
                                                    <option value="1" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>Active</option>
                                                    <option value="0" <?php echo ($Data->Status == "0") ? 'selected' : ''; ?>>In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">InActive</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnFarmID" name="hdnFarmID" value="<?php echo $FarmID ?>" />
                                            <a href="farm-master-list" class="btn btn-danger">Close</a>
                                            <?php if ($FarmID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

    <script>
        $(document).ready(function() {

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
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmCerticationBody").validate({

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
                txtFarmName: {
                    required: true,
                },
                txtRegistrationNo: {
                    required: true,
                },
                txtFarmerName: {
                    required: true,
                },
                txtFarmerAddress: {
                    required: true,
                },
                txtFarmerPhonNumber: {
                    required: true,
                },
                txtTonnageValue: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                }
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {
                $.ajax({
                    url: 'Ajax/ajax-farm-master-create.php',
                    type: 'POST',
                    data: {
                        farmID: $("#hdnFarmID").val(),
                        farmName: $("#drpFarm").val(),
                        registrationNo: $("#txtRegistrationNo").val(),
                        farmerName: $("#txtFarmerName").val(),
                        farmerAddress: $("#txtFarmerAddress").val(),
                        farmerPhoneNumber: $("#txtFarmerPhonNumber").val(),
                        tonnageValue: $("#txtTonnageValue").val(),
                        status: $("#drpStatus").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            alert(data.Msg);
                            window.location.href = "farm-master-list";
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