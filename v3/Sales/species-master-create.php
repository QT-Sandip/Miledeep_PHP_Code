<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resFreezingType = $resDropdownsAPI->Data->FreezingType;

$apiList = API_SPECIES_MASTARS;

$ID = trim($_GET['ID']);

$bodyContent = array(
    "ID" => $ID
);


if ($ID != '') {
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
                                            <label for="txtSpeciesName" class="form-label">Species Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSpeciesName" name="txtSpeciesName" placeholder="Enter Species Name" value='<?php echo $Data->SpeciesName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtShortCode" class="form-label">Short Code<span style="color:red">*</span></label>
                                            <input class="form-control" id="txtShortCode" name="txtShortCode" placeholder="Enter Short Code" value="<?php echo $Data->ShortCode ?>">
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtQCSpeciesShortCode" class="form-label">QC Species Short Code</label>
                                            <input class="form-control" id="txtQCSpeciesShortCode" name="txtQCSpeciesShortCode" placeholder="Enter QC Species Short Code" value="<?php echo $Data->QcSpeciesShortCode ?>">
                                        </div>


                                        <div class="col col-md-3 mb-3">
                                            <label for="txtDesc" class="form-label"> Description<span style="color:red">*</span></label>
                                            <textarea class="form-control" id="txtDesc" name="txtDesc" rows="1" placeholder="Enter Description"><?php echo $Data->Description ?></textarea>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="species-master-list" class="btn btn-danger">Close</a>
                                            <?php if ($ID == '') { ?>
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
        var id = "<?php echo isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "" ?>";

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
                txtSpeciesName: {
                    required: true,
                },
                txtShortCode: {
                    required: true,
                },
                // txtQCSpeciesShortCode: {
                //      required: true,
                // },
                txtDesc: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-species-master-create.php',
                    type: 'POST',
                    data: {
                        id: id,
                        speName: $("#txtSpeciesName").val(),
                        shortCode: $("#txtShortCode").val(),
                        qcSpeShortCode: $("#txtQCSpeciesShortCode").val(),
                        desc: $("#txtDesc").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "species-master-list";
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