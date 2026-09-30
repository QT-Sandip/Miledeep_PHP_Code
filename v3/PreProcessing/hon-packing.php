<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID
);


$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resCenter = $resDropdownsAPI->Data->Center;
$List  = $resDropdownsAPI->Data;

// var_dump($List);

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . 'includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>

                <!-- start page title -->

                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmGrading">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder="" value="<?php echo date("Y-m-d"); ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpLotCode" class="form-label">Arrival/Lot Code<span style="color:red">*</span></label>
                                            <select class="drpSelect  form-control" name="drpLotCode" id="drpLotCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpProductionPlant" id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID ?>">
                                                        <?php echo $Item->FullName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpCenter" class="form-label">Station<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpCenter" id="drpCenter">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCenter as $Item) { ?>
                                                    <option value="<?php echo $Item->StationID; ?>">
                                                        <?php echo $Item->UnitName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">Transaction Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate" name="txtTransactionDate" value="<?php echo date("Y-m-d"); ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50" id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>

                                        <hr>
                                        <div class="row">

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtArrivalCount" class="form-label">Hon Count</label>
                                                <input type="number" class="form-control" id="txtArrivalCount" name="txtArrivalCount" placeholder="Enter Hon Count">
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtArrivalWeight" class="form-label">Hon Weight(Kgs)</label>
                                                <input type="text" class="form-control" id="txtArrivalWeight" name="txtArrivalWeight" placeholder=" Enter Hon Weight(Kgs)">
                                            </div>

                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnArrivalID" name="hdnArrivalID" value="<?php echo $ArrivalID ?>" />
                                            <input type="hidden" id="hdnHonID" name="hdnHonID" value="" />
                                            <!-- <a href="#" class="btn btn-danger">Close</a> -->
                                            <button class="btn btn-primary" name="submit" id="submit" type="submit">Save</button>
                                        </div>
                                </form>

                            </div>
                        </div>

                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div id="divDataList">
                                <!--  -->
                            </div>
                        </div>
                    </div>

                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->


<?php include_once ROOT . 'includes/common-js.php' ?>

<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

<script>
    var arrivalID = "<?php echo isset($_GET["ArrivalID"]) && !empty($_GET["ArrivalID"]) ? $_GET["ArrivalID"] : "" ?>";
    var table;

    $(document).on('keydown', 'input[pattern]', function(e) {
        var input = $(this);
        var oldVal = input.val();
        var regex = new RegExp(input.attr('pattern'), 'g');

        setTimeout(function() {
            var newVal = input.val();
            if (!regex.test(newVal)) {
                input.val(oldVal);
            }
        }, 1);
    });


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
                title: 'Day-Arrival-Report-<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    $(document).ready(function() {
        getArrivals();
    });

    $(document).on("change", "#txtArrivalDate", function() {
        getArrivals();

        $("#drpProductionPlant").val("").trigger('change');
        $("#drpCenter").val("").trigger('change');
        $("#txtRemarks").val("");
        $("#txtArrivalCount").val("");
        $("#txtArrivalWeight").val("");
    });

    function getArrivals() {
        $.ajax({
            url: 'Ajax/ajax-hon-packing.php',
            type: 'POST',
            data: {
                arrivalDate: $("#txtArrivalDate").val(),
                // type: "Grading",
            },
            success: function(data) {
                console.log(data.Data);

                var list = "<option value=''>Select</option>";
                $.each(data.Data, function(index, value) {
                    list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + " - " + value.ArrivalCount + " - " + value.ArrivalWeight + "</option>";
                });

                $('#drpLotCode').select2('destroy');
                $('#drpLotCode').html(list);
                $('#drpLotCode').select2();

                return false;
            }
        });
    }

    $(document).on("change", "#drpLotCode", function() {

        $.ajax({
            url: 'Ajax/ajax-hon-packing.php',
            type: 'POST',
            data: {
                methodType: "getData",
                arrivalID: $(this).val(),
            },
            success: function(data) {

                console.log(data);
                // return;
                console.log(data.FormData);

                if (data.FormData.length > 0) {
                    $("#hdnArrivalID").val(data.FormData[0].LotID);
                    $("#hdnHonID").val(data.FormData[0].HonID);
                    $("#drpProductionPlant").val(data.FormData[0].PlantID).trigger("change");
                    $("#drpCenter").val(data.FormData[0].StationID).trigger("change");
                    $("#txtTransactionDate").val(data.FormData[0].GradingDate);
                    $("#txtRemarks").val(data.FormData[0].Remarks);
                    $("#txtArrivalCount").val(data.FormData[0].HonCount);
                    $("#txtArrivalWeight").val(data.FormData[0].HonWeight);
                } else {
                    $("#hdnArrivalID").val("");
                    $("#hdnHonID").val("");
                    $("#drpProductionPlant").val("").trigger("change");
                    $("#drpCenter").val("").trigger("change");
                    $("#txtTransactionDate").val("");
                    $("#txtRemarks").val("");
                    $("#txtArrivalCount").val("");
                    $("#txtArrivalWeight").val("");
                }
                if (data.html) {
                    $("#divDataList").html(data.html);
                    fnShowDataTable();
                } else {
                    $("#divDataList").html("");
                }

                return false;
            }
        });
    });


    $("#frmGrading").validate({
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
            txtArrivalDate: {
                required: true,
            },
            drpLotCode: {
                required: true,
            },
            drpProductionPlant: {
                required: true,
            },
            drpCenter: {
                required: true,
            },
            txtTransactionDate: {
                required: true,
            },
            txtArrivalCount: {
                required: true,
            },
            txtArrivalWeight: {
                required: true,
            },
        },
        errorPlacement: function() {
            return false;
        },
        submitHandler: function() {

            $.ajax({
                url: 'Ajax/ajax-hon-packing.php',
                type: 'POST',
                data: {
                    // drpLotCode,drpProductionPlant, drpCenter, txtTransactionDate, txtRemarks, txtArrivalCount, txtArrivalWeight
                    methodType: "post",
                    arrivalDate: $("#txtArrivalDate").val(),
                    arrivalID: $("#drpLotCode").val(),
                    honID: $("#hdnHonID").val(),
                    productionPlant: $("#drpProductionPlant").val(),
                    center: $("#drpCenter").val(),
                    transactionDate: $("#txtTransactionDate").val(),
                    arrivalCount: $("#txtArrivalCount").val(),
                    arrivalWeight: $("#txtArrivalWeight").val(),
                    remarks: $("#txtRemarks").val(),
                },
                success: function(data) {

                    console.log(data);
                    // return;
                    console.log(data.FormData);
                    alert(data.Msg)

                    if (data.FormData) {
                        $("#hdnArrivalID").val(data.FormData[0].LotID);
                        $("#hdnHonID").val(data.FormData[0].HonID);
                        $("#drpProductionPlant").val(data.FormData[0].PlantID).trigger("change");
                        $("#drpCenter").val(data.FormData[0].StationID).trigger("change");
                        $("#txtTransactionDate").val(data.FormData[0].GradingDate);
                        $("#txtRemarks").val(data.FormData[0].Remarks);
                        $("#txtArrivalCount").val(data.FormData[0].HonCount);
                        $("#txtArrivalWeight").val(data.FormData[0].HonWeight);
                    } else {
                        $("#hdnArrivalID").val("");
                        $("#hdnHonID").val("");
                        $("#drpLotCode").val("").trigger("change");
                        $("#drpProductionPlant").val("").trigger("change");
                        $("#drpCenter").val("").trigger("change");
                        $("#txtTransactionDate").val("");
                        $("#txtRemarks").val("");
                        $("#txtArrivalCount").val("");
                        $("#txtArrivalWeight").val("");
                    }
                    if (data.html) {
                        $("#divDataList").html(data.html);
                        fnShowDataTable();
                    } else {
                        $("#divDataList").html("");
                    }

                    return false;
                }
            });
        }
    });


    $(document).on("click", ".deleteBrkUp", function() {

        if (confirm("Are you sure you want to delete this item ?") == true) {
            var arrId = $(this).attr("data-arrivalid");
            var brkUpID = $(this).attr("data-arrivalbrkupid");
            console.log(brkUpID);

            $.ajax({
                url: 'Ajax/ajax-hon-packing.php',
                type: 'POST',
                data: {
                    methodType: "delete",
                    arrivalID: arrId,
                    breakUpID: brkUpID,
                },
                success: function(data) {

                    alert(data.Msg)
                    console.log(data.FormData);


                    if (data.FormData.length > 0) {
                        $("#hdnArrivalID").val(data.FormData[0].LotID);
                        $("#hdnHonID").val(data.FormData[0].HonID);
                        $("#drpProductionPlant").val(data.FormData[0].PlantID).trigger("change");
                        $("#drpCenter").val(data.FormData[0].StationID).trigger("change");
                        $("#txtTransactionDate").val(data.FormData[0].GradingDate);
                        $("#txtRemarks").val(data.FormData[0].Remarks);
                    } else {
                        $("#hdnArrivalID").val("");
                        $("#hdnHonID").val("");
                        $("#drpLotCode").val("").trigger("change");
                        $("#drpProductionPlant").val("").trigger("change");
                        $("#drpCenter").val("").trigger("change");
                        $("#txtTransactionDate").val("");
                        $("#txtRemarks").val("");
                        $("#txtArrivalCount").val("");
                        $("#txtArrivalWeight").val("");
                    }
                    if (data.html) {
                        $("#divDataList").html(data.html);
                        fnShowDataTable();
                    } else {
                        $("#divDataList").html("");
                    }

                    return false;
                }
            });
        }
    });
</script>

</body>

</html>