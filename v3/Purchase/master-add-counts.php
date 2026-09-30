<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resSpecies = $resDropdownsAPI->Data->Species;

$apiList =  API_MASTERS_COUNTS;

$RateId = trim($_GET['RateId']);

$bodyContent = array(
    "RateId" => $RateId
);


if ($RateId != '') {
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
                                <form role="form" method="post" id="frmCountry">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpSpecies" class="form-label">Species<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" id="drpSpecies" name="drpSpecies">
                                                <option value="">Select </option>
                                                <?php
                                                foreach ($resSpecies as $item)
                                                if($Data->Stype == $item->ShortCode){
                                                    echo '<option value="' . $item->ShortCode . '" selected >' . $item->SpeciesName . '(' . $item->ShortCode . ')'  . '</option>';
                                                }else{
                                                    echo '<option value="' . $item->ShortCode . '" >' . $item->SpeciesName . '(' . $item->ShortCode . ')'  . '</option>';
                                                }
                                                   
                                                ?>

                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCount" class="form-label">Count<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtCount" name="txtCount" placeholder="Enter Count" value='<?php echo $Data->RmCount ?>' />
                                        </div>


                                        <!-- <div class="col col-md-3 mb-3">
                                            <label for="txtGroup" class="form-label">Group<span style="color:red">*</span></label>
                                            <input class="form-control" id="txtGroup" name="txtGroup" placeholder="Enter Group" value="<?php echo $Data->CountGroup ?>">
                                        </div> -->
                                        <!-- <div class="col col-md-3 mb-3">
                                            <label for="txtValue" class="form-label">Value</label>
                                            <input class="form-control" id="txtValue" name="txtValue" placeholder="Enter Value" value="<?php echo $Data->Value ?>">
                                        </div> -->


                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $RateId ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="master-counts" class="btn btn-danger">Close</a>
                                            <?php if ($RateId == '') { ?>
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
        var RateId = "<?php echo isset($_GET["RateId"]) && !empty($_GET["RateId"]) ? $_GET["RateId"] : "" ?>";

        $("#frmCountry").validate({

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
                drpSpecies: {
                    required: true,
                },
                txtCount: {
                    required: true,
                },
                // txtGroup: {
                //      required: true,
                // },
                // txtRate: {
                //     required: true,
                // },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-master-add-counts.php',
                    type: 'POST',
                    data: {
                        rateId: RateId,
                        species: $("#drpSpecies").val(),
                        count: $("#txtCount").val()
                        // group: $("#txtGroup").val(),
                        // rate: $("#txtRate").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "master-counts";
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