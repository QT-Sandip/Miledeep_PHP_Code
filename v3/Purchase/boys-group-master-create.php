<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resFreezingType = $resDropdownsAPI->Data->FreezingType;

$apiList =API_BOYS_GROUP_MASTERS;

$GroupID =trim($_GET["GroupID"]);

$bodyContent = array(
    "GroupID" => $GroupID
);


if ($GroupID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;
    $Data = $result->Data[0];
}
// echo json_encode($result); 
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
                                <form role="form" method="post" id="frmCreateGroup">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGroupName">Group Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtGroupName' id="txtGroupName" placeholder="Enter Group Name" value="<?php echo $Data->GroupName ?>">
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnGroupID" name="hdnGroupID" value="<?php echo $GroupID ?>" />
                                            <a href="boys-group-master-list" class="btn btn-danger">Close</a>
                                            <?php if ($GroupID == '') { ?>
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
    var id = "<?php echo isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "" ?>";
        
        $("#frmCreateGroup").validate({

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
                txtGroupName: {
                    required: true,
                    
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {
                $.ajax({
                    url: 'Ajax/ajax-boys-group-master-create.php',
                    type: 'POST',
                    data: {
                        groupID: $("#hdnGroupID").val(),
                        groupName: $("#txtGroupName").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "boys-group-master-list";
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