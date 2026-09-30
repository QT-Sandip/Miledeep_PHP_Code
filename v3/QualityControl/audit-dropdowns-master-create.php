<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_CATEGORY_OPTIONS_MASTERS;

$OptionID = $_GET["OptionID"];

$bodyContent = array(
    "OptionID" => $OptionID
);
if ($OptionID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    $Data = $result->OptionsData[0];
}

$api = $api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
$resCategoryOptions = $resDropdownsAPI->Data->CategoryOptions;
$resProductionPlant = $resDropdownsAPI->Data->Plant;

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
                                <form role="form" method="post" id="frmCategoryOptions">
                                    <div class="row">

                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductionPlant" id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                        <?php echo $Item->FullName . ' - ' . $Item->StationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div> -->

                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="drpOptionCategory" class="form-label">Option Category <span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpOptionCategory" id="drpOptionCategory">
                                                <option value=""> Select</option>
                                                <?php //foreach ($resCategoryOptions as $Item) { ?>
                                                    <option value="<?php //echo $Item->OptionCategoryName; ?>"
                                                        <?php //echo ($Data->OptionCategoryName == $Item->OptionCategoryName) ? 'selected' : '' ?>>
                                                        <?php //echo $Item->OptionCategoryName; ?></option>
                                                <?php //} ?>
                                            </select>
                                        </div> -->

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpOptionCategory" class="form-label">Option Category <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpOptionCategory"
                                                id="drpOptionCategory">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCategoryOptions as $CategoryOptions) {
                                                    $selected = '';
                                                    $selectedCategoryOptions = explode(",", $Data->OptionCategoryName);
                                                    if (in_array($CategoryOptions, $selectedCategoryOptions)) {
                                                        $selected = 'selected="selected"';
                                                    }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $CategoryOptions ?>">
                                                        <?php echo $CategoryOptions ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <div class="col col-md-3 mb-3">
                                            <label for="txtOptionName">Option Name<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtOptionName'
                                                id="txtOptionName" placeholder="Enter Option Name"
                                                value="<?php echo $Data->OptionName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['OptionID']) && !empty($_GET['OptionID'])) { ?>
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
                                            <input type="hidden" id="hdnOptionID" name="hdnOptionID"
                                                value="<?php echo $OptionID ?>" />
                                            <a href="audit-dropdowns-master-list" class="btn btn-danger">Close</a>
                                            <?php if ($OptionID == '') { ?>
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
        // $(document).ready(function() {
        //     var productionPlant = <?php echo $ProductionPlant ?>;
        //     if (productionPlant != 0) {
        //         $("#drpProductionPlant").prop('disabled', true);
        //         $("#drpProductionPlant").val(productionPlant).trigger('change');
        //     } else {
        //         $("#drpProductionPlant").prop('disabled', false);
        //         $("#drpProductionPlant").val("").trigger('change');
        //     }
        // });

        jQuery.validator.addMethod(
            "noSpace",
            function (value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmCategoryOptions").validate({

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
                // drpProductionPlant: {
                //     required: true,
                // },
                drpOptionCategory: {
                    required: true,
                },
                txtOptionName: {
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
                    url: 'Ajax/ajax-audit-dropdowns-master-create.php',
                    type: 'POST',
                    data: {
                        optionID: $("#hdnOptionID").val(),
                        // productionPlant: $("#drpProductionPlant").val(),
                        optionCategory: $("#drpOptionCategory").val(),
                        optionName: $("#txtOptionName").val(),
                        status: $("#drpStatus").val(),
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            alert(data.Msg);
                            window.location.href = "audit-dropdowns-master-list";
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