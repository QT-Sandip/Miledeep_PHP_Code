<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PPC_STATIONS_LIST;

$StationID = trim($_GET['StationID']);

$bodyContent = array(
    "StationID" => $StationID
);

if ($StationID != '') {
    $result = CallAPI($api, "GET", "", $bodyContent);

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
                                <form role="form" method="post" id="frmPpcStation">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtStation">Station<span style="color:red">*</span> </label>
                                            <input type="text" class="form-control" id="txtStation" name="txtStation" placeholder="Enter Station" value="<?php echo $Data->UnitName ?>" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtLocation">Unit Location<span style="color:red">*</span> </label>
                                            <input type="text" class="form-control" id="txtLocation" name="txtLocation" placeholder="Enter Unit Location" value="<?php echo $Data->UnitLocation ?>" />
                                        </div>



                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPerPerKgs" class="form-label">Extra Percentage PerKgs<span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtPerPerKgs" name="txtPerPerKgs" min="0" placeholder="Enter Percentage" value="<?php echo $Data->ExtraPercPerKgs ?>" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAmountPerPerson" class="form-label">Extra Amount Per Person<span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtAmountPerPerson" name="txtAmountPerPerson" placeholder="Enter Amount" value="<?php echo $Data->ExtraAmountPerPerson ?>" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php
                                                if (isset($_GET['StationID']) && !empty($_GET['StationID'])) {

                                                    echo '<option value="1" ' . (($Data->UnitStatus == 1) ? 'selected' : '') . '>In-House</option>
                                                    <option value="0" ' . (($Data->UnitStatus == 0) ? 'selected' : '') . '  >Out-Side</option>';
                                                } else {
                                                    echo '
                                                    <option value="">All</option>
                                                    <option value="1" selected>In-House</option>
                                                    <option value="0">Out-Side</option>';
                                                }
                                                ?>

                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $StationID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="ppc-stations-list" class="btn btn-danger">Close</a>
                                            <?php if ($StationID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

                                </div>
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
        var stationID = "<?php echo isset($_GET["StationID"]) && !empty($_GET["StationID"]) ? $_GET["StationID"] : "" ?>";
        // var table;

        function fnShowDataTable() {
            table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable',
                    title: 'Ppc-Stations-List-<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        $(document).ready(function() {
            $.ajax({
                url: 'Ajax/ajax-create-ppc-stations.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    StationID: stationID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;

                }
            });
        });


        $("#frmPpcStation").validate({

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
                txtStation: {
                    required: true,
                },
                txtLocation: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                },
                txtPerPerKgs: {
                    required: true,
                    min: 0  
                },
                txtAmountPerPerson: {
                    required: true,
                    min: 0  
                },

            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-create-ppc-stations.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        stationID: stationID,
                        unitName: $("#txtStation").val(),
                        unitLocation: $("#txtLocation").val(),
                        unitStatus: $("#drpStatus").val(),
                        extraPercPerKgs: $("#txtPerPerKgs").val(),
                        extraAmountPerPerson: $("#txtAmountPerPerson").val(),

                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>