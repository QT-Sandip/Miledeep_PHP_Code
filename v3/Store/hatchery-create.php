<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_HATCHERY_MASTERS;

$bodyContent = array(
    "HatcheryID" => trim($_GET['HatcheryID']),
);

$result = CallAPI($apiList, "GET", "", $bodyContent);

$Data = $result->Data[0];
// echo $HatcheryID;
// var_dump($result);

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
                                <form role="form" method="post" id="frmHarchery">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtHatcheryName" class="form-label">Hatchery Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtHatcheryName" name="txtHatcheryName" placeholder="Enter Hatchery Name" value='<?php echo $Data->HatcryName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtHatcheryLocation" class="form-label">Hatchery Location<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtHatcheryLocation" name="txtHatcheryLocation" placeholder="Enter Hatchery Location" value='<?php echo $Data->HatcryLocation ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtHatcryCapacity" class="form-label">Hatchery Capacity<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtHatcryCapacity" name="txtHatcryCapacity" placeholder="Enter Hatchery Capacity" value='<?php echo $Data->HatcryCapacity ?>' />
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $HatcheryID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="hatchery-list" class="btn btn-danger">Close</a>
                                            <?php if ($HatcheryID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
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
        var hatcheryID = "<?php echo isset($_GET["HatcheryID"]) && !empty($_GET["HatcheryID"]) ? $_GET["HatcheryID"] : "" ?>";

        $("#frmHarchery").validate({

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
                txtHatcheryName: {
                    required: true,
                },
                txtHatcheryLocation: {
                    required: true,
                },
                txtHatcryCapacity: {
                    required: true,
                },

            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-hatchery-create.php',
                    type: 'POST',
                    data: {
                        hatcheryID: hatcheryID,
                        hatcheryName: $("#txtHatcheryName").val(),
                        hatcheryLocation: $("#txtHatcheryLocation").val(),
                        hatcryCapacity: $("#txtHatcryCapacity").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {

                            alert(data.Msg);
                            window.location.href = "hatchery-list";

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