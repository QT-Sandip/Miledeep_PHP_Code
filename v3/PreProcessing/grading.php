<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID
);


$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resDropdownsAPI, JSON_PRETTY_PRINT);
$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resCenter = $resDropdownsAPI->Data->Center;
$resGradingmachine = $resDropdownsAPI->Data->Gradingmachine;
// $resDefects = $resDropdownsAPI->Data->Defects;
$resVariety = $resDropdownsAPI->Data->HlVaBillVariety;
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

                <?php // if (isset($_SESSION['Msg'])) { ?>
                    <!-- <div class="alert alert-success" role="alert">
                        <?php // echo $Status ?>
                    </div> -->
                <?php // $_SESSION['Error'] = null;
                // } ?>

                <?php // if (isset($_SESSION['Error'])) { ?>
                    <!-- <div class="alert alert-danger" role="alert">
                        <?php // echo $Status ?>
                    </div> -->
                <?php // $_SESSION['Msg'] = null;
                // } ?>

                <div class="alert alert-success mt-2" role="alert" style="display:none" id="alertMixIds">
                    
                </div>
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
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder=""
                                            value="<?php echo date("Y-m-d"); ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpLotCode" class="form-label">Arrival/Lot Code<span style="color:red">*</span></label>

                                            <select class="drpSelect  form-control" name="drpLotCode" id="drpLotCode" multiple="multiple">
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
                                            <label for="drpCenter" class="form-label">Grading Station<span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpCenter" id="drpCenter">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCenter as $Item) { ?>
                                                    <option value="<?php echo $Item->StationID; ?>">
                                                        <?php echo $Item->UnitName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">Grading Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate" name="txtTransactionDate" value="<?php echo date("Y-m-d"); ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>




                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGradingMachine" class="form-label">Grading Machine <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpGradingMachine" id="drpGradingMachine">
                                                <option value=""> Select</option>
                                                <?php foreach ($resGradingmachine as $Item) { ?>
                                                    <option value="<?php echo $Item->MachineID; ?>">
                                                        <?php echo $Item->MachineName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtStartTime" class="form-label">Start Time <span style="color:red">*</span>
                                                <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                            </label>
                                            <input type="time" class="form-control" id="txtStartTime" name="txtStartTime" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtEndTime" class="form-label">End Time <span style="color:red">*</span></label>

                                            <input type="time" class="form-control" id="txtEndTime" name="txtEndTime" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtBreakTime" class="form-label">Break Time(Minutes) <span style="color:red">*</span></label>

                                            <input type="text" maxlength="3" class="form-control" id="txtBreakTime" name="txtBreakTime" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50" id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>

                                        <hr>
                                        <div class="row">
                                            <!-- <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="drpDefects" class="form-label">Defects</label>
                                                <select class="drpSelect" name="drpDefects" id="drpDefects">
                                                    <option value=""> Select</option>
                                                    <?php
                                                    // foreach ($resDefects as $Item) {
                                                    //     echo '<option value="' . $Item->DefectID . '"> ' . $Item->DefectName . '</option>';
                                                    // }
                                                    ?>
                                                </select>
                                            </div> -->

                                            <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Select Variety <span style="color:red">*</span>
                                                <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                            </label>
                                            <select class="drpSelect" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                                <?php foreach ($resVariety as $Item) { ?>
                                                    <option value="<?php echo $Item->VarietyID; ?>">
                                                        <?php echo $Item->ShortName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>



                                       
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtArrivalCount" class="form-label">HL Count(Per Lb) </label>
                                                <input type="number" class="form-control" id="txtArrivalCount" name="txtArrivalCount" placeholder="Enter HL Count">
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtArrivalWeight" class="form-label">HL Weight(Kgs)
                                                </label>
                                                <input type="text" class="form-control" id="txtArrivalWeight" name="txtArrivalWeight" placeholder=" Enter HL Weight(Kgs)">
                                            </div>
                                            <div hidden class="col-md-6 col-lg-3 mb-3">
                                                <label for="drpMasterRates" class="form-label">Master Rates</label>
                                                <select class="drpSelect" name="drpMasterRates" id="drpMasterRates">
                                                    <option value=""> Select</option>
                                                    <?php
                                                    $MasterRate = "270/260";
                                                    $MasterList = explode("/", $MasterRate);

                                                    foreach ($MasterList as $Item) { ?>
                                                        <option value="<?php echo $Item ?>"> <?php echo $Item ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtNoOfCrates" class="form-label">No Of Crates</label>
                                                <input type="text" class="form-control" id="txtNoOfCrates" name="txtNoOfCrates" placeholder="Enter No Of Crates">
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ArrivalID ?>" />
                                            <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                           <!-- <a href="rm-arrival-list" class="btn btn-danger">Close</a>-->
                                           <a href="javascript:void(0)" class="newItem btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New
                                        </a>
                                           
                                            <button class="btn btn-primary" name="submit" id="submit" type="submit">Save</button>
                                            <button class="btn btn-danger" name="btnOverallDelete" id="btnOverallDelete" type="button" disabled>
                                                <i class="mdi mdi-delete font-size-14 text-danger" style="color: #FFFFFF !important;"></i>
                                                Delete</button>
                                        </div>
                                </form>

                            </div>
                        </div>

                        <div id="divDataList" style="padding: 15px;">


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

    $(document).ready(function (){
        $('#drpLotCode').select2();
    });
    $(document).ready(function (){
        $.ajax({
            url: 'Ajax/ajax-grading.php',
            type: 'POST',
            data: {
                ArrivalDate: $("#txtArrivalDate").val(),
                Type: "Grading",
            },
            success: function(data) {
                console.log(data.Data);

                var list = "<option value=''>Select</option>";
                $.each(data.Data, function(index, value) {
                    list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + " - " + value.ArrivalCount + " - " + value.ArrivalWeight + "</option>";
                });

                $('#drpLotCode').select2('destroy');
                $('#drpLotCode').html(list);
                $('#drpLotCode').select2({
                    width: "100%",
                    maximumSelectionLength: 1
                });
                
                return false;
            }
        });
    });

    $(document).on("change", "#txtArrivalDate", function() {
       
        $.ajax({
            url: 'Ajax/ajax-grading.php',
            type: 'POST',
            data: {
                ArrivalDate: $(this).val(),
                Type: "Grading",
            },
            success: function(data) {
                console.log(data.Data);

                var list = "<option value=''>Select</option>";
                $.each(data.Data, function(index, value) {
                    list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + " - " + value.ArrivalCount + " - " + value.ArrivalWeight + "</option>";
                });

                $('#drpLotCode').select2('destroy');
                $('#drpLotCode').html(list);
                $('#drpLotCode').select2({
                    width: "100%",
                    maximumSelectionLength: 1
                });
                

                // $('#drpLotCode').trigger("change");
                return false;
            }
        });
    });

    function bindFormData(resJson) {
        if (resJson) {
            // $("#txtArrivalDate").val("");
            // $("#drpLotCode").val(resJson.ArrivalID).trigger('change');
            $("#drpProductionPlant").val(resJson.PlantID).trigger('change');
            $("#drpCenter").val(resJson.StationID).trigger('change');
            $("#txtTransactionDate").val(resJson.GradingDate);//val(resJson.TransactionDate);
            $("#drpGradingMachine").val(resJson.MachineID).trigger('change');
            $("#txtStartTime").val(resJson.StartTime);
            $("#txtEndTime").val(resJson.EndTime);
            $("#txtBreakTime").val(resJson.BreakTime);
            // $("#drpDefects").val(resJson.Defect).trigger('change');
            $("#drpVariety").val(resJson.Variety).trigger('change');
         //   $("#drpGrade").val(resJson.Grade).trigger('change');
            $("#txtArrivalCount").val(resJson.Count);
            $("#txtArrivalWeight").val(resJson.Weight);
            // $("#drpMasterRates").val(resJson.MasterRates).trigger('change');
            $("#txtNoOfCrates").val(resJson.NoCrates);
            $("#txtRemarks").val(resJson.Remarks);
        } else {
            // $("#txtArrivalDate").val("");
            // $("#drpLotCode").val(resJson.ArrivalID).trigger('change');
            $("#drpProductionPlant").val("").trigger('change');
            $("#drpCenter").val("").trigger('change');
            // $("#txtTransactionDate").val("");
            $("#drpGradingMachine").val("").trigger('change');
            $("#txtStartTime").val("");
            $("#txtEndTime").val("");
            $("#txtBreakTime").val("");
            // $("#drpDefects").val("").trigger('change');
            $("#drpVariety").val("").trigger('change');
         //   $("#drpGrade").val("").trigger('change');
            $("#txtArrivalCount").val("");
            $("#txtArrivalWeight").val("");
            // $("#drpMasterRates").val(resJson.MasterRates).trigger('change');
            $("#txtNoOfCrates").val("");
            $("#txtRemarks").val("");
        }
    }

    function removeQueryParameters(url) {
        const urlObject = new URL(url);
        urlObject.search = '';
        return urlObject.toString();
    }

    $(document).on("click", ".newItem", function() {

        // $("#txtTransactionDate, #txtStartTime, #txtEndTime, #txtBreakTime, #txtArrivalCount, #txtArrivalWeight, #txtNoOfCrates").val("");
        // $("#drpLotCode, #drpProductionPlant, #drpCenter, #drpDefects, #drpGradingMachine").val("").trigger('change');

        $("#txtArrivalCount, #txtArrivalWeight, #txtNoOfCrates").val("");
        $("#drpVariety").val("").trigger('change');
        // $("#drpDefects").val("").trigger('change');

        arrivalID = "";

        table.clear().draw();
        // $("#divDataList").html("");
        $("#hdnNewArrival").val("NEW");

        var url = window.location.href;
        var newUrl = removeQueryParameters(url);
        window.history.replaceState(null, null, newUrl);

        // $("#btnOverallDelete" ).prop( "disabled", true);

        return false;
    });

    $(document).on("change", "#drpLotCode", function() {
        $(" #txtStartTime, #txtEndTime, #txtBreakTime, #txtArrivalCount, #txtArrivalWeight, #txtNoOfCrates, #txtRemarks").val("");
        // $("#drpProductionPlant, #drpCenter, #drpDefects, #drpGradingMachine").val("").trigger('change');
        $("#drpProductionPlant, #drpCenter, #drpGradingMachine, #drpVariety").val("").trigger('change');

        var arrid = $(this).val();

        var lots = $('#drpLotCode').val();
        // console.log(lots);
        lots = lots.join("_");
        var lastArrivalId = lots.split("_").pop();

        // if(arrid != "" && arrid != "select") {
        var frmData = {
            methodType: "get",
            ArrivalID: arrid,
        }

        $.ajax({
            url: 'Ajax/ajax-grading.php',
            type: 'POST',
            data: frmData,
            success: function(data) {
                var data = JSON.parse(data);
                console.log(data);
                var htmlData = data.html;
                var resJson = data.json;
                // console.log(htmlData);

                $("#divDataList").html(htmlData);
                fnShowDataTable();
                bindFormData(resJson);

                $("#btnOverallDelete").prop("disabled", false);

                $("#txtArrivalCount, #txtArrivalWeight, #txtNoOfCrates").val("");
                $("#drpVariety").val("").trigger('change');

                return false;
            }
        });

        
        // }
    });

    $("#frmGrading").validate({
        onkeyup: false,
        onfocusout: false,
        invalidHandler: function(form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: {
            txtArrivalDate: {
                required: true,
                selectcheck: true
            },
            drpLotCode: {
                required: true,
                selectcheck: true
            },
            drpProductionPlant: {
                required: true,
                selectcheck: true
            },
            drpCenter: {
                required: true,
                selectcheck: true
            },
            txtTransactionDate: {
                required: true,
                noSpace: true
            },
            drpGradingMachine: {
                required: true,
                noSpace: true
            },
            txtStartTime: {
                required: true,
                selectcheck: true
            },
            txtEndTime: {
                required: true,
                selectcheck: true
            },
            txtBreakTime: {
                required: true,
                selectcheck: true
            },
            // drpDefects: {
            //     required: true,
            //     noSpace: true
            // },
            drpVariety: {
                required: true,
            },
            // drpGrade: {
            //     required: true,
            // },
            txtArrivalCount: {
                required: true,
                noSpace: true
            },
            txtArrivalWeight: {
                required: true,
                selectcheck: true
            },
            txtNoOfCrates: {
                required: true,
                noSpace: true
            },
        },
        errorPlacement: function() {
            return false;
        },
        submitHandler: function() {

            var lots = $('#drpLotCode').val();
            lots = lots.join(",");
            var lastArrivalId = lots.split("_").pop();

            $.ajax({
                url: 'Ajax/ajax-grading.php',
                type: 'POST',
                data: {
                    methodType: "post",
                    ArrivalDate: $("#txtArrivalDate").val(),
                    ArrivalID: lastArrivalId,
                    MixIds: lots,
                    drpProductionPlant: $("#drpProductionPlant").val(),
                    drpCenter: $("#drpCenter").val(),
                    txtTransactionDate: $("#txtTransactionDate").val(),
                    drpGradingMachine: $("#drpGradingMachine").val(),
                    txtStartTime: $("#txtStartTime").val(),
                    txtEndTime: $("#txtEndTime").val(),
                    txtBreakTime: $("#txtBreakTime").val(),
                    drpDefects: "", // $("#drpDefects").val(),

                    drpVariety: $("#drpVariety").val(),
                 //   drpGrade: $("#drpGrade").val(),
                    txtArrivalCount: $("#txtArrivalCount").val(),
                    txtArrivalWeight: $("#txtArrivalWeight").val(),
                    // drpMasterRates : $("#drpMasterRates").val(),
                    txtNoOfCrates: $("#txtNoOfCrates").val(),
                    txtRemarks: $("#txtRemarks").val(),
                },
                success: function(data) {
                    var data = JSON.parse(data);
                    var htmlData = data.html;
                    var resJson = data.json;
                    var error = data.Error;
                    var msg = data.Msg;

                    console.log(data);
                    // console.log(resJson);

                    if (error === null) {
                        // alert(msg);
                        $("#alertMixIds").text(msg).show();
                    } else {
                        alert(error);
                        return false;
                    }
                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    $("#txtArrivalCount, #txtArrivalWeight, #txtNoOfCrates").val("");
                    $("#drpVariety").val("").trigger('change');
                    // $("#drpDefects").val("").trigger('change');

                    $("#btnOverallDelete").prop("disabled", false);
                    return false;
                }
            });

            return false;
        }
    });

    function fnDeleteAjax(deleteBodyContent) {

        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-grading.php',
                type: 'POST',
                data: deleteBodyContent,
                success: function(data) {
                    var data = JSON.parse(data);
                    console.log(data);
                    var htmlData = data.html;
                    var resJson = data.json;

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    $("#btnOverallDelete").prop("disabled", false);

                    if (arrivalID) {
                        bindFormData(resJson);
                    }
                    return false;
                }
            });
        }
    }

    $(document).on("click", ".deleteBrkUp", function() {
        var arrId = $(this).attr("data-arrivalid");
        var brkUpID = $(this).attr("data-arrivalbrkupid");
        console.log(brkUpID);

        var frmData = {
            methodType: "delete",
            ArrivalID: arrId,
            BreakUpID: brkUpID,
            Type: ""
        }

        fnDeleteAjax(frmData);

    });

    $(document).on("click", "#btnOverallDelete", function() {

        var lots = $('#drpLotCode').val();
        lots = lots.join("_");
        var lastArrivalId = lots.split("_").pop();

        var frmData = {
            methodType: "delete",
            ArrivalID:  lastArrivalId,
            BreakUpID: "",
            Type: "DeleteTotalGrading"
        }

        fnDeleteAjax(frmData);

    });

    // btnDelete

    // function getMixedIds(ArrivalIds){
    //     var Arrivals[] = ArrivalIds;

    // //alert(ArrivalIds);
    // var MixedIds = Arrivals.join();
    // var Ids=MixedIds.replace(/,/g, "_");
    //  var Ids=ArrivalIds.replace(/,/g, "_");


    // return Ids;
    // }
</script>

</body>

</html>