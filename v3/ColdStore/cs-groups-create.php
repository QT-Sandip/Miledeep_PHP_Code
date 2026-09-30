<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_CS_GROUP_MASTERS;

$TrnsID = isset($_GET["TrnsID"]) && !empty($_GET["TrnsID"]) ? $_GET["TrnsID"] : "";

$bodyContent = array(
    "TrnsID" => $TrnsID
);

if ($TrnsID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    // var_dump($result);

    $Data = $result->Data[0];
}
 //echo json_encode($Data);


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
                                <form role="form" method="post" id="frmGroupCreate">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGroupName">Group Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtGroupName' id="txtGroupName" placeholder="Enter Group Name" 
                                            <?php echo (!empty($TrnsID) ? "readonly" : "" ) ?> value="<?php echo $Data->TransCategory ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpType">TransType<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpType" id="drpType"  <?php echo (!empty($TrnsID) ? "disabled" : "" ) ?>>
                                                <?php if (isset($_GET['TrnsID']) && !empty($_GET['TrnsID'])) { ?>
                                                    <option value="1" <?php echo ($Data->TransType == "1") ? 'selected' : ''; ?>>Production</option>
                                                    <option value="2" <?php echo ($Data->TransType == "2") ? 'selected' : ''; ?>>Stock out</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1">Production</option>
                                                    <option value="2">Stock out</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpIsReturnable">Is Returnable<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpIsReturnable" id="drpIsReturnable"  <?php echo (!empty($TrnsID) ? "disabled" : "" ) ?>> 
                                                <?php if (isset($_GET['TrnsID']) && !empty($_GET['TrnsID'])) { ?>
                                                    <option value="1" <?php echo ($Data->IsReturnable == "1") ? 'selected' : ''; ?>>No</option>
                                                    <option value="0" <?php echo ($Data->IsReturnable == "0") ? 'selected' : ''; ?>>Yes</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" >No</option>
                                                    <option value="0">Yes</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['TrnsID']) && !empty($_GET['TrnsID'])) { ?>
                                                    <option value="1" <?php echo ($Data->TransStatus == "1") ? 'selected' : ''; ?>>Active</option>
                                                    <option value="0" <?php echo ($Data->TransStatus == "0") ? 'selected' : ''; ?>>In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">InActive</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $TrnsID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="cs-group-list" class="btn btn-danger">Close</a>
                                            <?php if ($TrnsID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
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
        var trnsID = "<?php echo isset($_GET["TrnsID"]) && !empty($_GET["TrnsID"]) ? $_GET["TrnsID"] : "" ?>";

        // function fnShowDataTable() {
        //     table = $('#tblDataTable').DataTable({
        //         scrollY: "600px",
        //         scrollX: true,
        //         sScrollXInner: "100%",
        //         paging: false,
        //         destroy: true,
        //         fixedColumns: true,
        //         scrollCollapse: true,
        //         dom: 'Blfrtip',
        //         // dom: '<"top-left-buttons"Bf>rt<"bottom"lip>',
        //         buttons: [{
        //             extend: 'excel',
        //             text: 'Download Excel',
        //             className: 'btnExcelDataTable',
        //             title: 'Agent List - <?php // echo date("d-m-Y") 
                                            ?>',
        //             footer: true,
        //             exportOptions: {
        //                 columns: ':visible'
        //             }
        //         }],
        //     });
        // }

        // $(document).ready(function() {
        //     $.ajax({
        //         url: 'Ajax/ajax-create-agent.php',
        //         type: 'POST',
        //         data: {
        //             methodType: "get",
        //             ID: id
        //         },
        //         success: function(data) {
        //             $("#divDataList").html(data);
        //             fnShowDataTable();
        //             return false;
        //         }
        //     });
        // });

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmGroupCreate").validate({

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
                    noSpace: true
                },
                drpType: {
                    required: true,
                },
                drpIsReturnable: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                }
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-cs-groups-create.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        trnsID: trnsID,
                        groupName: $("#txtGroupName").val(),
                        type: $("#drpType").val(),
                        isReturnable: $("#drpIsReturnable").val(),
                        status: $("#drpStatus").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "cs-group-list";
                        } else {
                            alert(data.Error);
                        }
                        // $("#divDataList").html(data);
                        // fnShowDataTable();
                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>