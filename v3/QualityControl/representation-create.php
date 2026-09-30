<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_LAB_MASTERS;

$LabId = isset($_GET["LabId"]) && !empty($_GET["LabId"]) ? $_GET["LabId"] : "";

$bodyContent = array(
    "LabId" => $LabId
);

if ($LabId != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    // var_dump($result);

    $Data = $result->Data[0];
}


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
                                <form role="form" method="post" id="frmRepresentNames">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRepresentName">Representation Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtRepresentName' id="txtRepresentName" placeholder="Enter Representation Name" 
                                            <?php // echo (!empty($TenantID) ? "readonly" : "" ) ?> value="<?php echo $Data->LabName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRepresentAddress">Representation Address<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtRepresentAddress' id="txtRepresentAddress" placeholder="Enter Representation Name" 
                                            <?php // echo (!empty($TenantID) ? "readonly" : "" ) ?> value="<?php echo $Data->Address ?>">
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $LabId ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="representation-list" class="btn btn-danger">Close</a>
                                            <?php if ($LabId == '') { ?>
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
        var labID = "<?php echo isset($_GET["LabId"]) && !empty($_GET["LabId"]) ? $_GET["LabId"] : "" ?>";

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmRepresentNames").validate({

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
                txtRepresentName: {
                    required: true,
                    noSpace: true
                },
                txtRepresentAddress: {
                    required: true,
                    noSpace: true
                },
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-representation-create.php',
                    type: 'POST',
                    data: {
                        // methodType: "post",
                        labID: labID,
                        representName: $("#txtRepresentName").val(),
                        representAddress: $("#txtRepresentAddress").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "representation-list.php";
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