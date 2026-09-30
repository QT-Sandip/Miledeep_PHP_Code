<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resTestList = $resDropdownsAPI->Data->TestList;

$apiList = API_REQUIRED_ANALYSIS;

$TestID = isset($_GET["TestID"]) && !empty($_GET["TestID"]) ? $_GET["TestID"] : "";

$bodyContent = array(
    "TestID" => $TestID
);

if ($TestID != '') {
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
                                <form role="form" method="post" id="frmAnalysis">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpTestCatagory">Test Category Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpTestCatagory" id="drpTestCatagory" <?php echo (!empty($TestID) ? "disabled" : "" ) ?>>
                                                <option value="" <?php echo ($Data->TestCategory == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resTestList as $List) { ?>
                                                    <option value="<?php echo $List->TestCategory ?>" <?php echo ($Data->TestCategory == $List->TestCategory) ? 'selected' : ''; ?>>
                                                        <?php echo $List->TestCategory ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTestCatName">Test Category Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtTestCatName' id="txtTestCatName" placeholder="Enter Test Category Name" 
                                            <?php echo (!empty($TestID) ? "readonly" : "" ) ?> value="<?php echo $Data->TestCategory ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTestName">Test Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtTestName' id="txtTestName" placeholder="Enter Test Name" 
                                            <?php // echo (!empty($TenantID) ? "readonly" : "" ) ?> value="<?php echo $Data->TestName ?>">
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $TestID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="analysis-required-list" class="btn btn-danger">Close</a>
                                            <?php if ($TestID == '') { ?>
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
        var testID = "<?php echo isset($_GET["TestID"]) && !empty($_GET["TestID"]) ? $_GET["TestID"] : "" ?>";

        $(document).ready(function() {
            $('#drpTestCatagory').change(function() {
                var selectedValue = $(this).val();
                if (selectedValue !== '') {
                    $('#txtTestCatName').prop('readonly', true);
                } else {
                    $('#txtTestCatName').prop('readonly', false);
                }
            });

            $('#txtTestCatName').on('input', function() {
                var inputValue = $(this).val();
                if (inputValue !== '') {
                    $('#drpTestCatagory').prop('disabled', true);
                } else {
                    $('#drpTestCatagory').prop('disabled', false);
                }
            });

        });

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmAnalysis").validate({

            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: ":disabled, [readonly]",
            rules: {
                drpTestCatagory: {
                    required: true,
                },
                txtTestCatName: {
                    required: true,
                    noSpace: true
                },
                txtTestName: {
                    required: true,
                    noSpace: true
                },
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                var testCatName;
                var drpTestCatagory = $("#drpTestCatagory").val();
                var txtTestCatName = $("#txtTestCatName").val();

                if (drpTestCatagory !== '') {
                    testCatName = drpTestCatagory;
                } else if (txtTestCatName !== '') {
                    testCatName = txtTestCatName;
                } else {
                    return false;
                }

                $.ajax({
                    url: 'Ajax/ajax-analysis-required-create.php',
                    type: 'POST',
                    data: {
                        // methodType: "post",
                        testID: testID,
                        testCatName: testCatName,
                        testName: $("#txtTestName").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "analysis-required-list.php";
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