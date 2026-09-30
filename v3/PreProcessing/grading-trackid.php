<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

$ClientConfigData = $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData['IsPlantFilter'];  //sriram
$IsHONPacking = $ClientConfigData['IsHONPacking'];  //sriram
$ShowBatchCode = $ClientConfigData['ShowBatchCode'];

$dbValue = 0;

$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID
);

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resCenter = $resDropdownsAPI->Data->Center;
$resGradingmachine = $resDropdownsAPI->Data->Gradingmachine;
$resVariety = $resDropdownsAPI->Data->HlVaBillVariety;
$List = $resDropdownsAPI->Data;
$resSupervisor = $resDropdownsAPI->Data->Supervisor;

// SESSION Values

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ShowDeheadingStationID = $ArrayClientConfigData['ShowDeheadingStationID'];
$IsShowAddValueAddtion = $ArrayClientConfigData['AddvalueAddition'];

$DeheadingPlantID = $_SESSION['UnitID'];

// echo $DeheadingPlantID;
// exit;
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>
    <style>
        .divRecvList {
            display: inline-block;
            padding: 2px 8px;
            background: #e5e5e5;
            border-radius: 6px;
            margin: 2px 7px 2px 0px;
            font-size: .85rem;
        }
    </style>

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

                                <form id="frmGrading">
                                    <div class="row">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductionPlant"
                                                id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID ?>">
                                                        <?php echo $Item->FullName . '-' . $Item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTrackCode" class="form-label">Track ID<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTrackCode"
                                                id="drpTrackCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpRecvCount" class="form-label">Receiving Count<span
                                                    style="color:red">*</span></label>
                                            <div class="form-control" id="divRecvCount" style="min-height: 38px;">
                                            </div>
                                            <input type="hidden" id="hdnResCountTrackIds" />
                                            <input type="hidden" id="hdnReceivalBreakupIds" />
                                        </div>

                                        <?php if ($ShowDeheadingStationID == 1) { ?>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpCenter" class="form-label">Grading Station<span
                                                        style="color:red">*</span></label>
                                                <select class="drpSelect form-control" name="drpCenter" id="drpCenter">
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resCenter as $Item) { ?>
                                                        <option value="<?php echo $Item->StationID; ?>">
                                                            <?php echo $Item->UnitName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        <?php } ?>


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">Grading Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate"
                                                name="txtTransactionDate" value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGradingMachine" class="form-label">Grading Machine <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGradingMachine"
                                                id="drpGradingMachine">
                                                <option value=""> Select</option>
                                                <?php foreach ($resGradingmachine as $Item) { ?>
                                                    <?php if ($DeheadingPlantID == 0 || $DeheadingPlantID == $Item->UnitID) { ?>
                                                        <option value="<?php echo $Item->MachineID; ?>"
                                                            data-plantId="<?php echo $Item->UnitID; ?>">
                                                            <?php echo $Item->MachineName; ?>
                                                        </option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtStartTime" class="form-label">Start Time</label>
                                            <input type="time" class="form-control" id="txtStartTime"
                                                name="txtStartTime" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtEndTime" class="form-label">End Time</label>
                                            <input type="time" class="form-control" id="txtEndTime" name="txtEndTime" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtBreakTime" class="form-label">Break Time(Minutes)</label>
                                            <input type="text" maxlength="3" class="form-control" id="txtBreakTime"
                                                name="txtBreakTime" placeholder="Enter Break Time" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpSuperVisor" class="form-label">SuperVisor <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpSuperVisor"
                                                id="drpSuperVisor">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSupervisor as $Item) { ?>
                                                    <option value="<?php echo $Item->ID; ?>">
                                                        <?php echo $Item->UserName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>

                                        <div class="col-md-12 col-lg-12 mb-3 text-center">
                                            <label class="form-label"></label><br>
                                            <button class="btn btn-primary" type="submit" id="btnSaveGrading">Save
                                                Grading </button>
                                            <input type="hidden" id="hdnGradeId" value="" />
                                            <button type="button" class="btn btn-success btnAddNewLine"
                                                id="btnAddNewLine" disabled><i class="fa fa-plus"></i> Add Line
                                            </button>
                                            <button type="button" class="btn btn-danger btnDeleteLine"
                                                id="btnDeleteLine" disabled><i class="fa fa-trash"></i> Delete </button>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>


                        <div id="divDataList">
                            <!--  -->
                        </div>


                    </div>
                </div>

            </div>
        </div><!-- End Page-content -->

        <div class="modal fade" id="modalReceivingCount" data-backdrop="static" tabindex="-1"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Receiving Count</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="divReceivingCount"></div>
                        </div>
                    </div>
                    <div class="modal-footer text-center" style="display: block;">
                        <button type="button" class="btn btn-primary btnOkReceivingCount"
                            data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalValueAddition" data-backdrop="static" tabindex="-1"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Value Addition</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">                        
                        <div class="row">
                            <div class="form-group col-sm-3">
                                <label for="drpValAdd" class="form-label">Value Addition</label>
                                <select class="form-control" name="vaddid" id="vaddid" >
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                </select>
                                <input type="hidden" name="valadd_id" id="valadd_id" value="0">
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-md-12 col-lg-12 mb-3 text-center">
                                            <label class="form-label"></label><br>
                                            <button class="btn btn-primary" type="submit" onclick="saveValueAddtion()">Save
                                                 </button>                            
                        </div>
                    </div>
                    <div class="modal-footer text-center" style="display: none;">
                        <button type="button" class="btn btn-primary btnOkReceivingCount"
                            data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <?php include_once ROOT . 'includes/footer.php' ?>

    </div><!-- end main content-->

</div><!-- END layout-wrapper -->


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

    function fnDateformat(dt) {
        var parts = dt.split("-");
        return parts[2] + "-" + parts[1] + "-" + parts[0];
    }

function clean(str) {
    return str.replace(/[^a-zA-Z0-9_-]/g, "").substring(0,20);
}

    function fnShowDataTable() {

        // var excelHeader = "Grading_ArrivalDate:" + fnDateformat($("#txtArrivalDate").val()) + "_" + $.trim($("#drpTrackCode option:selected").text())
        //  + "_" + $(".divRecvList").text() + "_GradingDate: " + fnDateformat($("#txtTransactionDate").val()) 
        // + "_" + $.trim($("#drpGradingMachine option:selected").text());


    let arrivalDate = fnDateformat($("#txtArrivalDate").val());
    let trackCode = clean($.trim($("#drpTrackCode option:selected").text()));
    let gradingDate = fnDateformat($("#txtTransactionDate").val());
    let machine = clean($.trim($("#drpGradingMachine option:selected").text()));

    var excelHeader = `Grading_ArrivalDate:${arrivalDate}_Track:${trackCode}_GradingDate:${gradingDate}_Machine:${machine}`;
    var BillDetails = $('#BillDetails').val();
    var total_rows = $('#BillDetails').attr('data-total_rows');


        table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bSort: false,
            dom: 'Blfrtip',
             <?php 
            if(!empty($ShowBatchCode)){?>
            columnDefs: [
                {
                    targets: [1],
                    visible: false
                }
            ],
            <?php }?>
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: excelHeader, //'De-Heading-13-04-2024',
                footer: true,
                exportOptions: {
                    <?php 
                    if(!empty($ShowBatchCode)){?>
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                    <?php }else{?>
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                    <?php }?>
                    format: {
                        body: function(inner, rowidx, colidx, node) {
                            if ($(node).children("input").length > 0) {
                                return $(node).children("input").first().val();
                            } else if ($(node).children("select").length > 0) {
                                return $(node).children("select").first().find(":selected").text();
                                // let selectedText = $(node).children("select").first().find(":selected").text();
                                // return selectedText == 'Select' ? '' : selectedText;
                            } else if ($(node).children("button").length > 0) {
                                return "";
                            } else {
                                return inner;
                            }
                        }
                    }
                },
                 <?php 
                    if(!empty($ShowBatchCode)){?>
                        customize: function (xlsx) {

                            var sheet = xlsx.xl.worksheets['sheet1.xml'];

                            // Example: merge A2:A5
                            let startRow = 3;
                            let endRow = parseInt(total_rows) + 2;

                            $('mergeCells', sheet).append(
                                //'<mergeCell ref="B3:B15"/>'
                                `<mergeCell ref="B${startRow}:B${endRow}"/>`
                            );

                            $('mergeCells', sheet).attr('count', 1);
                        }
                <?php }?>
            }],
        });
    }

    function getTrackIDs() {

        var deheadingPlant = <?php echo $DeheadingPlantID ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        if (deheadingPlant != 0 || IsPlantFilter == 0) {
            $("#drpProductionPlant").prop('disabled', true);
            if(IsPlantFilter == 0){
                $("#drpProductionPlant").val("");
            }else{
                $("#drpProductionPlant").val(deheadingPlant).trigger('change');
            }
        } else {
            $("#drpProductionPlant").prop('disabled', false);
        }
        $.ajax({
            url: 'Ajax/ajax-grading-trackid.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                plantID: $("#drpProductionPlant").val(),
                type: "grading",
            },
            success: function(data) {
                console.log(data);

                if (data.Error) {
                    alert(data.Error);
                } else {
                    var list = "<option value=''>Select</option>";
                    if (data.Data.RmDeHeadingTrackIds) {
                        $.each(data.Data.RmDeHeadingTrackIds, function(index, value) {

                            list += "<option value='" + value.TrackID + "'  data-plantid='" + value.PlantID + "' data-species='" + value.Species + "'>" + value.TrackCode + "</option>";
                        });
                    }

                    $('#drpTrackCode').select2('destroy');
                    $('#drpTrackCode').html(list);
                    $('#drpTrackCode').select2();
                }

                return false;
            }
        });
    }

    $(document).on("change", "#txtArrivalDate", function() {
        getTrackIDs();

        $("#divRecvCount").html("");
        $("#hdnResCountTrackIds, #hdnReceivalBreakupIds").val("");

        $("#drpCenter").val("").trigger("change");
        $("#drpGradingMachine").val("").trigger("change");

        $("#txtStartTime, #txtEndTime, #txtBreakTime, #txtRemarks, #hdnGradeId").val("");
        $("#divDataList").html("");
        $("#btnAddNewLine").prop("disabled", true);
        $("#btnDeleteLine").prop("disabled", true);
    });

    $(document).ready(function() {
        getTrackIDs();

        $(document).on('change', '#drpProductionPlant', function() {

            var productionPlant = <?php echo $DeheadingPlantID ?>;
            if (productionPlant == 0) {
                $("#txtArrivalDate").trigger('change');
            }
        });
    });

    $(document).on('change', '#drpProductionPlant', function() {
        const productionPlant = $(this).val();
        filterGradingMachines(productionPlant);
    });

    function filterGradingMachines(productionPlant) {
        $('#drpGradingMachine').empty().append('<option value="">Select</option>');

        <?php foreach ($resGradingmachine as $Item) { ?>
            var unitId = "<?php echo $Item->UnitID; ?>";
            var machineId = "<?php echo $Item->MachineID; ?>";
            var machineName = "<?php echo $Item->MachineName; ?>";

            if (productionPlant == 0 || productionPlant == unitId) {
                $('#drpGradingMachine').append(
                    $('<option>', {
                        value: machineId,
                        text: machineName,
                        'data-plantId': unitId
                    })
                );
            }
        <?php } ?>
    }

    var poptable;
    poptable = $('.popupReceivingCountTable').DataTable({
        scrollX: true,
        sScrollXInner: "100%",
        paging: false,
        destroy: true,
        fixedColumns: true,
        scrollCollapse: true,
        bInfo: false,
        ordering: false
    });
    $(document).on('shown.bs.modal', '#modalReceivingCount', function() {
        if ($.fn.dataTable.isDataTable('.popupReceivingCountTable')) {
            poptable.destroy();
        }
        poptable = $('.popupReceivingCountTable').DataTable({
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            ordering: false
        });

    });

    $(document).on("change", "#drpTrackCode", function() {

        $("#drpCenter").val("").trigger("change");
        $("#drpGradingMachine").val("").trigger("change");

        $("#txtStartTime, #txtEndTime, #txtBreakTime, #txtRemarks, #hdnGradeId").val("");
        $("#divDataList").html("");
        $("#btnAddNewLine").prop("disabled", true);
        $("#btnDeleteLine").prop("disabled", true);
        
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram

        if ($(this).val().length > 0) {
            $.ajax({
                url: 'Ajax/ajax-grading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "getReceivingCount",
                    arrivalDate: $.trim($("#txtArrivalDate").val()),
                    trackID: $.trim($("#drpTrackCode").val()),
                    IsHONPacking: IsHONPacking
                },
                success: function(data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $("#divRecvCount").html("");
                        $("#hdnResCountTrackIds, #hdnReceivalBreakupIds").val("");
                        $("#divReceivingCount").html("").html(data.ResvCountDtsHTML + "<br>");
                    }
                    return false;
                }
            });
        } else {
            $("#divRecvCount").html("");
            $("#hdnResCountTrackIds, #hdnReceivalBreakupIds").val("");
            $("#divReceivingCount").html("").append("<br>");
        }
    });

    $("#divRecvCount").on("click", function() {
        $('#modalReceivingCount').modal('show');
    });

    function valAdd(id,val){
        $('#vaddid').val(val);
        $('#modalValueAddition').modal('show');
        $('#valadd_id').val(id);
    }
    function saveValueAddtion(){
        var valadd_value = $('#vaddid').val();
        if(valadd_value == ""){
            alert("Please select Value Addition");
            return false;
        }
        $.ajax({
            url: 'Ajax/ajax-grading-trackid.php',
            type: 'POST',
            data: {
                id: $('#valadd_id').val(),
                value: valadd_value,
                type: "SAVE_VALUE_ADDITION",
            },
            success: function(data) {
                $('#modalValueAddition').modal('hide');
            }
        });
    }

    function fnCheckResList() {
        var selectedResCntArvId = [];
        var selectedResCntBrkId = [];
        var selectedResCntBillNo = [];

        $('.chkSelectRecList:checked').each(function() {
            selectedResCntArvId.push($(this).val());
            selectedResCntBrkId.push($(this).attr("data-receivalbreakupid"));
            selectedResCntBillNo.push($(this).attr("data-billnumber"));
        });
        var selectedResCntArvIdString = selectedResCntArvId.join(",");
        var selectedResCntBrkIdString = selectedResCntBrkId.join(",");

        $("#hdnResCountTrackIds").val(selectedResCntArvIdString);
        $("#hdnReceivalBreakupIds").val(selectedResCntBrkIdString);

        var listHtml = "";
        $.each(selectedResCntBillNo, function(index, value) {
            listHtml += '<div class="divRecvList"> ' + value + '</div>';
        });
        $("#divRecvCount").html(listHtml);
    }

    $(document).on("change", ".chkSelectRecList", function() {

        var gradeStatus = $(this).attr("data-gradingstatus");

        if ($(this).prop('checked') && gradeStatus == 1) {

            $('.chkSelectRecList').not(this).prop('disabled', true).prop('checked', false);

        } else if (!$(this).prop('checked') && gradeStatus == 1) {
            $('.chkSelectRecList').prop('disabled', false);
        }

        if ($(this).prop('checked') && gradeStatus == 0) {

            $('.chkSelectRecList').each(function() {
                if ($(this).attr("data-gradingstatus") == 0) {
                    $(this).prop('disabled', false);
                } else {
                    $(this).prop('disabled', true);
                    $(this).prop('checked', false);
                }
            });
        } else if (!$(this).prop('checked') && gradeStatus == 0) {
            $('.chkSelectRecList').prop('disabled', false);
        }

        var overalDeleteAccess = $(this).data('overaldeleteaccess');
        if (overalDeleteAccess == 0) {
            $("#btnDeleteLine").prop("disabled", true);
        } else {
            $("#btnDeleteLine").prop("disabled", false);
        }

        fnCheckResList();
    });

    $(document).on("click", "#chkSelectAll", function() {
        $('.chkSelectRecList').prop('checked', $(this).prop('checked'));
        fnCheckResList();
    });

    $(document).on('hidden.bs.modal', '#modalReceivingCount', function() { //
        
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
        if ($('.chkSelectRecList:checked').length > 0) {

            $.ajax({
                url: 'Ajax/ajax-grading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "getList",
                    arrivalDate: $.trim($("#txtArrivalDate").val()),
                    trackID: $.trim($("#drpTrackCode").val()),
                    trackSpecies: $("#drpTrackCode option:selected").data("species"),
                    receivingCount: $.trim($("#hdnResCountTrackIds").val()),
                    receivalBreakupId: $.trim($("#hdnReceivalBreakupIds").val()),
                    IsHONPacking: IsHONPacking
                },
                success: function(data) {
               
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        if (data.FormData) {
                            if (data.FormData.PlantID) {
                                $("#drpCenter").val(data.FormData.StationID).trigger("change");
                                $("#txtTransactionDate").val(data.FormData.TransactionDate);
                                $("#drpGradingMachine").val(data.FormData.MachineID).trigger("change");
                                $("#txtStartTime").val(data.FormData.StartTime);
                                $("#txtEndTime").val(data.FormData.EndTime);
                                $("#txtBreakTime").val(data.FormData.BreakTime);
                                $("#txtRemarks").val(data.FormData.Remarks);

                                if (data.FormData.MachineID) {
                                    $("#btnSaveGrading").prop("disabled", true);
                                } else {
                                    $("#btnSaveGrading").prop("disabled", false);
                                }

                                if (data.FormData.GradingID) {
                                    $("#hdnGradeId").val(data.FormData.GradingID);
                                    $("#btnAddNewLine").prop("disabled", false);
                                    // $("#btnDeleteLine").prop("disabled", false);
                                }
                            } else {

                                $("#drpCenter").val("").trigger("change");
                                $("#drpGradingMachine").val("").trigger("change");
                                $("#txtStartTime").val("");
                                $("#txtEndTime").val("");
                                $("#txtBreakTime").val("");
                                $("#txtRemarks").val("");

                                $("#hdnGradeId").val("");
                                $("#btnAddNewLine").prop("disabled", true);
                                $("#btnDeleteLine").prop("disabled", true);

                                $("#btnSaveGrading").prop("disabled", false);
                            }
                        }
                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                            $('.drpVariety').select2();
                            $('.drpGrade').select2();
                            $('.drpTreatment').select2();
                            $('.drpIsBlock').select2();
                        }
                    }
                    return false;
                }
            });
        }

    })

    $(document).ready(function () {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram

        var validationRules = {
            txtArrivalDate: {
                required: true
            },
            drpTrackCode: {
                required: true
            },
            txtTransactionDate: {
                required: true
            },
            drpGradingMachine: {
                required: true
            },
        };

    if (IsPlantFilter != 0) {
        validationRules.drpProductionPlant = { required: true };
        validationRules.drpCenter = { required: true };
    }

    $("#frmGrading").validate({ //
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
        rules: validationRules,
        errorPlacement: function() {
            return false;
        },
        submitHandler: function() {
            // var MixedCountIDS=sort($("#hdnResCountTrackIds").val());
            // var unsort=$("#hdnResCountTrackIds").val();
            // alert(sort);
            // alert(MixedCountIDS);
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
            $.ajax({
                url: 'Ajax/ajax-grading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "postForm",
                    arrivalDate: $("#txtArrivalDate").val(),
                    arrivalID: $("#hdnResCountTrackIds").val(),
                    receivalBreakupID: $("#hdnReceivalBreakupIds").val(),
                    gradingID: $("#hdnGradeId").val(),
                    trackID: $("#drpTrackCode").val(),
                    trackSpecies: $("#drpTrackCode option:selected").data("species"),
                    productionPlant: $("#drpProductionPlant").val(),
                    center: $("#drpCenter").val(),
                    transactionDate: $("#txtTransactionDate").val(),
                    gradingMachine: $("#drpGradingMachine").val(),
                    startTime: $("#txtStartTime").val(),
                    endTime: $("#txtEndTime").val(),
                    breakTime: $("#txtBreakTime").val(),
                    remarks: $("#txtRemarks").val(),
                    IsPlantFilter: IsPlantFilter,
                    IsHONPacking: IsHONPacking,
                    drpSuperVisor: $("#drpSuperVisor").val(),
                },
                success: function(data) {

                    console.log(data);

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);


                        if (data.FormData.GradingID) {
                            $("#hdnGradeId").val(data.FormData.GradingID);
                            $("#btnAddNewLine").prop("disabled", false);
                            $("#btnDeleteLine").prop("disabled", false);
                        }

                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();
                        var countCountTrackIds = $("#hdnResCountTrackIds").val().split(',').length;
                        if (countCountTrackIds > 1) {
                            $("#drpTrackCode").trigger("change");
                        }
                        $("#btnSaveGrading").prop("disabled", true);
                    }
                    return false;
                }
            });
        }
    });
});

    $(document).on("click", "#btnDelete", function() { //

        var brkUpID = $(this).attr("data-breakup-id");
        console.log(brkUpID);
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram

        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-grading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "deleteRow",
                    breakUpID: brkUpID,
                    arrivalDate: $("#txtArrivalDate").val(),
                    trackID: $("#drpTrackCode").val(),
                    trackSpecies: $("#drpTrackCode option:selected").data("species"),
                    arrivalID: $("#hdnResCountTrackIds").val(),
                    receivalBreakupID: $("#hdnReceivalBreakupIds").val(),
                    IsHONPacking: IsHONPacking
                },
                success: function(data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                        if (data.FormData) {
                            $("#drpCenter").val(data.FormData.StationID).trigger("change");
                            $("#txtTransactionDate").val(data.FormData.TransactionDate);
                            $("#drpGradingMachine").val(data.FormData.MachineID).trigger("change");
                            $("#txtStartTime").val(data.FormData.StartTime);
                            $("#txtEndTime").val(data.FormData.EndTime);
                            $("#txtBreakTime").val(data.FormData.BreakTime);
                            $("#txtRemarks").val(data.FormData.Remarks);
                        }
                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                        }
                    }
                    return false;
                }
            });
        }
    });

    $(document).on("click", "#btnDeleteLine", function() { // 

        var brkUpID = $(this).attr("data-breakup-id");
        console.log(brkUpID);
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-grading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "deleteMixedIds",
                    breakUpID: brkUpID,
                    arrivalDate: $("#txtArrivalDate").val(),
                    trackID: $("#drpTrackCode").val(),
                    trackSpecies: $("#drpTrackCode option:selected").data("species"),
                    arrivalID: $("#hdnResCountTrackIds").val(),
                    receivalBreakupID: $("#hdnReceivalBreakupIds").val(),
                    IsHONPacking: IsHONPacking
                },
                success: function(data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                        if (data.FormData) {
                            $("#drpCenter").val(data.FormData.StationID).trigger("change");
                            if (data.FormData.TransactionDate) {
                                $("#txtTransactionDate").val(data.FormData.TransactionDate);
                            } else {
                                $("#txtTransactionDate").val('<?php echo date('Y-m-d'); ?>');
                            }

                            $("#drpGradingMachine").val(data.FormData.MachineID).trigger("change");
                            $("#txtStartTime").val(data.FormData.StartTime);
                            $("#txtEndTime").val(data.FormData.EndTime);
                            $("#txtBreakTime").val(data.FormData.BreakTime);
                            $("#txtRemarks").val(data.FormData.Remarks);
                        }

                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                        }
                        $("#divRecvCount").text("");
                        $("#hdnResCountTrackIds, #hdnReceivalBreakupIds").val("");

                        $('.chkSelectRecList').prop('disabled', false).prop('checked', false);

                        $("#drpTrackCode").trigger("change");
                    }
                    return false;
                }
            });
        }
    });

    $(document).on("click", ".btnAdd", function() {

        var dataBrkupID = $(this).attr("data-brkup-id");

        var treatmentDropdown = $(this).closest("tr").find("select[id^='drpTreatment']");
        var treatment = treatmentDropdown.val();
        var block = $(this).closest("tr").find(".drpIsBlock").val();
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
        var IsShowAddValueAddtion = <?php echo $IsShowAddValueAddtion ?>; // sriram

        if (treatment == '') {
            treatmentDropdown.addClass("error").focus();
        } else {
            $.ajax({
                url: 'Ajax/ajax-grading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "AddCrate",
                    dataBrkupID: dataBrkupID,
                    trackID: $("#drpTrackCode").val(),
                    trackSpecies: $("#drpTrackCode option:selected").data("species"),
                    variety: $(this).closest("tr").find(".drpVariety").val(),
                    hlCount: $(this).closest("tr").find(".txthlCount").val(),
                    prodGrade: $(this).closest("tr").find(".drpGrade").val(),
                    treatment: $(this).closest("tr").find(".drpTreatment").val(),
                    isBlock: $(this).closest("tr").find(".drpIsBlock").val(),
                    crateQty: $(this).closest("tr").find(".txtCrateQty").val(),
                    gradingQty: $(this).closest("tr").find(".txtGradingQty").val(),
                    noCrates: $(this).closest("tr").find(".txtNoOfCrates").val(),
                    gradingID: $("#hdnGradeId").val(),
                    arrivalID: $("#hdnResCountTrackIds").val(),
                    receivalBreakupID: $("#hdnReceivalBreakupIds").val(),
                    IsHONPacking: IsHONPacking
                },
                success: function(data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        if(IsShowAddValueAddtion == 1){
                            if(block == '1'){
                                $.ajax({
                                    url: 'Ajax/ajax-grading-trackid.php',
                                    type: 'POST',
                                    data: {
                                        id: dataBrkupID,
                                        value: 0,
                                        type: "SAVE_VALUE_ADDITION",
                                    },
                                    success: function(data) {
                                    }
                                });
                            }else{
                                $.ajax({
                                    url: 'Ajax/ajax-grading-trackid.php',
                                    type: 'POST',
                                    data: {
                                        id: dataBrkupID,
                                        value: 1,
                                        type: "SAVE_VALUE_ADDITION",
                                    },
                                    success: function(data) {
                                    }
                                });
                            }
                        }
                        if (data.FormData) {
                            $("#drpCenter").val(data.FormData.StationID).trigger("change");
                            $("#txtTransactionDate").val(data.FormData.TransactionDate);
                            $("#drpGradingMachine").val(data.FormData.MachineID).trigger("change");
                            $("#txtStartTime").val(data.FormData.StartTime);
                            $("#txtEndTime").val(data.FormData.EndTime);
                            $("#txtBreakTime").val(data.FormData.BreakTime);
                            $("#txtRemarks").val(data.FormData.Remarks);
                        }
                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                            $('.drpVariety').select2();
                            $('.drpGrade').select2();
                            $('.drpTreatment').select2();
                            $('.drpIsBlock').select2();
                        }
                    }
                    return false;
                }
            });
        }

    });

    $(document).on("click", ".btnRemove", function() { // 

        var dataBrkupID = $(this).attr("data-brkup-id");

        var treatmentDropdown = $(this).closest("tr").find("select[id^='drpTreatment']");
        var treatment = treatmentDropdown.val();
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
        if (treatment == '') {
            treatmentDropdown.addClass("error").focus();
        } else {
            $.ajax({
                url: 'Ajax/ajax-grading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "RemoveCrate",
                    dataBrkupID: dataBrkupID,
                    trackID: $("#drpTrackCode").val(),
                    trackSpecies: $("#drpTrackCode option:selected").data("species"),
                    variety: $(this).closest("tr").find(".drpVariety").val(),
                    hlCount: $(this).closest("tr").find(".txthlCount").val(),
                    prodGrade: $(this).closest("tr").find(".drpGrade").val(),
                    treatment: $(this).closest("tr").find(".drpTreatment").val(),
                    isBlock: $(this).closest("tr").find(".drpIsBlock").val(),
                    crateQty: $(this).closest("tr").find(".txtCrateQty").val(),
                    gradingQty: $(this).closest("tr").find(".txtGradingQty").val(),
                    noCrates: $(this).closest("tr").find(".txtNoOfCrates").val(),
                    gradingID: $("#hdnGradeId").val(),
                    arrivalID: $("#hdnResCountTrackIds").val(),
                    receivalBreakupID: $("#hdnReceivalBreakupIds").val(),
                    IsHONPacking: IsHONPacking
                },
                success: function(data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        if (data.FormData) {
                            $("#drpCenter").val(data.FormData.StationID).trigger("change");
                            $("#txtTransactionDate").val(data.FormData.TransactionDate);
                            $("#drpGradingMachine").val(data.FormData.MachineID).trigger("change");
                            $("#txtStartTime").val(data.FormData.StartTime);
                            $("#txtEndTime").val(data.FormData.EndTime);
                            $("#txtBreakTime").val(data.FormData.BreakTime);
                            $("#txtRemarks").val(data.FormData.Remarks);
                        }
                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                            $('.drpVariety').select2();
                            $('.drpGrade').select2();
                            $('.drpTreatment').select2();
                            $('.drpIsBlock').select2();
                        }
                    }
                    return false;
                }
            });
        }

    });

    $(document).on("click", ".btnAddNewLine", function() { //
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
        $.ajax({
            url: 'Ajax/ajax-grading-trackid.php',
            type: 'POST',
            data: {
                methodType: "AddNewLine",
                gradingID: $("#hdnGradeId").val(),
                trackID: $("#drpTrackCode").val(),
                trackSpecies: $("#drpTrackCode option:selected").data("species"),
                arrivalID: $("#hdnResCountTrackIds").val(),
                receivalBreakupID: $("#hdnReceivalBreakupIds").val(),
                IsHONPacking: IsHONPacking
            },
            success: function(data) {
                
                if (data.Error) {
                    alert(data.Error);
                } else {
                    alert(data.Msg);
                    if (data.FormData) {
                        $("#drpCenter").val(data.FormData.StationID).trigger("change");
                        $("#txtTransactionDate").val(data.FormData.TransactionDate);
                        $("#drpGradingMachine").val(data.FormData.MachineID).trigger("change");
                        $("#txtStartTime").val(data.FormData.StartTime);
                        $("#txtEndTime").val(data.FormData.EndTime);
                        $("#txtBreakTime").val(data.FormData.BreakTime);
                        $("#txtRemarks").val(data.FormData.Remarks);

                    }
                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();

                        setTimeout(function () {
                            // Scroll to the <tfoot> element of the table
                            var $tfoot = $("#tblDataTable tfoot");
                            if ($tfoot.length) {
                                $('html, body').animate({
                                    scrollTop: $tfoot.offset().top
                                }, 600); // smooth scroll duration
                            }
                        }, 200);
                    }
                    $(".drpSelect").select2({
                        width: "100%",
                    });
                }

                return false;
            }
        });

    });
</script>

</body>

</html>