<?php

session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once ROOT . 'config/api-call.php';

$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

// var_dump($ArrivalID);
// exit;
$bodyContent = array(
    'ArrivalID' => $ArrivalID
);

$sql_LAB_USERS = "SELECT * FROM QcLabTechnologist where LbuStatus=1 ";
$stmtVerifiedBy = $iconn->query($sql_LAB_USERS);
$stmtInspectedBy = $iconn->query($sql_LAB_USERS);

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

                             
                                <form id="frmDeHeading">
                                    <div class="row">
                                        <input type="hidden" id="hdnID" name="hdnID" value="" />

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date</label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>


                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtBatchCode" class="form-label">Batch Number<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control  BatchCode form-control " name="txtBatchCode[]"
                                                id="txtBatchCode" required
                                                    multiple="multiple"> 
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        
                                           

                                        

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtVariety" class="form-label">Variety</label>
                                            <input type="text" class="form-control" id="txtVariety" name="txtVariety"
                                                placeholder="Enter Variety" value="HON-HL" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtCount" class="form-label">Count</label>
                                            <input type="text" class="form-control" id="txtCount" name="txtCount"
                                                placeholder="Enter Count" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtReceivingQuantity" class="form-label">Head On Weight<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtReceivingQuantity"
                                                name="txtReceivingQuantity">
                                        </div>


                                        <div class="form-group col-sm-3">
                                            <label for="txtReceivingTime" class="form-label">Receiving Time</label>
                                            <input type="time" class="form-control" id="txtReceivingTime"
                                                name="txtReceivingTime" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtGeneralAppearance" class="form-label">General Appearance<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtGeneralAppearance"
                                                name="txtGeneralAppearance" value="Good" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtColour" class="form-label">Colour<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtColour" name="txtColour"
                                                value="Light" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtFreshness" class="form-label">Freshness<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtFreshness"
                                                name="txtFreshness" value="S" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtOdour" class="form-label">Odour<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtOdour" name="txtOdour"
                                                value="Fresh" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtTexture" class="form-label">Texture<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTexture" name="txtTexture"
                                                value="Firm" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtTemperature" class="form-label">Temperature<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTemperature"
                                                name="txtTemperature">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtDeterioration" class="form-label">Deterioration</label>
                                            <input type="text" class="form-control" id="txtDeterioration"
                                                name="txtDeterioration" value="Nill">
                                                
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtDiscolouration" class="form-label">Discolouration</label>
                                            <input type="text" class="form-control" id="txtDiscolouration"
                                                name="txtDiscolouration" value="Nill">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtBlackSpots" class="form-label">Black Spots</label>
                                            <input type="text" class="form-control" id="txtBlackSpots"
                                                name="txtBlackSpots">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtSoftShell" class="form-label">Soft Shell</label>
                                            <input type="text" class="form-control" id="txtSoftShell"
                                                name="txtSoftShell">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtBrokenDamaged" class="form-label">Broken Damaged</label>
                                            <input type="text" class="form-control" id="txtBrokenDamaged"
                                                name="txtBrokenDamaged">
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtForeignMeterial" class="form-label">Foreign Meterial</label>
                                            <input type="text" class="form-control" id="txtForeignMeterial"
                                                name="txtForeignMeterial"  >
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtSendingQuantity" class="form-label">HL QTY<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSendingQuantity"
                                                name="txtSendingQuantity">
                                        </div>


                                        <div class="form-group col-sm-3">
                                            <label for="txtSendingTime" class="form-label">Sending Time</label>
                                            <input type="time" class="form-control" id="txtSendingTime"
                                                name="txtSendingTime" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtAttachedLegs" class="form-label">Attached Legs</label>
                                            <input type="text" class="form-control" id="txtAttachedLegs"
                                                name="txtAttachedLegs">
                                        </div>

                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="txthangingmeat" class="form-label">Hanging Meat</label>
                                            <!-- <input type="text" class="form-control" id="txthangingmeat"
                                                name="txthangingmeat"> -->
                                                <input type="text" class="form-control" id="txtHangingMeat" name="HangingMeat">

                                        </div>

                                           <div class="form-group col-sm-3">
                                                <label for="drpInspectedBy">Inspected By</label>
                                                <select class="drpSelect form-control select2" name="drpInspectedBy" id="drpInspectedBy">
                                                    
                                                    <?php while ($RowVerifiedBy = $stmtVerifiedBy->fetch_array()) { ?>
                                                        <option value="<?php echo $RowVerifiedBy['LbuName'] ?>">
                                                            <?php echo $RowVerifiedBy['LbuName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        
                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="drpVerifiedBy" class="form-label">Verified By</label>
                                            <select class="drpSelect form-control" name="drpVerifiedBy"
                                                id="drpVerifiedBy">
                                                <option value=""> Select</option>
                                                <option value="KL Ganesh" selected>KL Ganesh</option>
                                                 <?php while ($RowInspectedBy = $stmtInspectedBy->fetch_array()) { ?>
                                                        <option value="<?php echo $RowInspectedBy['LbuName'] ?>">
                                                            <?php echo $RowInspectedBy['LbuName'] ?>
                                                        </option>
                                                    <?php } ?>
                                            </select>
                                        </div> 

                                                                                                      
                                <div class="form-group col-sm-3 mb-3">
                                    <label for="txtRemarks" class="form-label">Remarks</label>
                                    <input type="text" class="form-control" id="txtRemarks"
                                        name="txtRemarks">
                                </div>


                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $ArrivalID ?>" />

                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <!-- <a href="#" class="btn btn-danger">Close</a> -->

                                        <!-- <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save </button> -->
                                        <button class="btn btn-primary" name="submit" id="submit" type="submit">
                                            <?php
                                            if ($ID == '') {
                                                echo "Save";
                                            } else {
                                                echo "Update";
                                            }
                                            ?>
                                        </button>
                                        <!-- <button class="btn btn-success" name="btnTransfer" id="btnTransfer" type="button">Transfer</button> -->

                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="divDataList">
                            <!--  -->
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
    function clearForm(full=false) {
        if (full) {
            $("#txtArrivalDate").val("");
        }
        $("#hdnID").val("");
        $("#txtBatchCode").val("").trigger("change")
        $("#txtVariety").val("HON-HL");
        $("#txtCount").val("");     
        $("#txtReceivingQuantity").val("");
        $("#txtGeneralAppearance").val("Good");
        $("#txtColour").val("Light");
        $("#txtFreshness").val("Good");
        $("#txtOdour").val("Fresh");
        $("#txtTexture").val("Firm");
        $("#txtTemperature").val("");
        $("#txtDeterioration").val("");
        $("#txtSendingQuantity").val("");
        $("#txtDiscolouration").val("");
        $("#txtBlackSpots").val("");
        $("#txtSoftShell").val("");
        $("#txtBrokenDamaged").val("");
        $("#txthangingmeat").val("");
        $("#txtForeignMeterial").val("");
         $("#txtAttachedLegs").val("");
        $("#drpVerifiedBy").val("KL Ganesh").trigger("change");
        $("#drpInspectedBy").val("P.DEEPA").trigger("change");
         $("#txtRemarks").val("");
        $("button[name='submit']").text("Save").removeClass("btn-primary").addClass("btn-primary"); 
        
    }
    $(document).ready(function () {
        //  loadData();

        function loadData() {
            // debugger
            console.log($("#txtBatchCode").val());
            $.ajax({
                url: 'Ajax/ajax-deheading-check-list.php',
                type: 'POST',

                data: {
                    methodType: "GET",
                    ArrivalDate: $("#txtArrivalDate").val(),
                    BatchCode: $("#txtBatchCode").val(),
                    
                },
                success: function (data) {
                    fnSuccess(data);
                }
            });
        }


        function fnSuccess(data) {
            $("#divDataList").html(data);
            fnShowDataTable();
        }

        function fnShowDataTable() {
            $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                 dom: 'Blfrtip',
                 buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'Deheading Check List',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
            });
        }

        $(document).ready(function () {
            let isFirstSaveDone = false;

            function initializeValidation() {
                $("#frmDeHeading").validate({
                    onkeyup: false,
                    onfocusout: false,
                    invalidHandler: function (form, validator) {
                        var errors = validator.numberOfInvalids();
                        if (errors) {
                            var firstInvalidElement = $(validator.errorList[0].element);
                            firstInvalidElement.focus();
                        }
                    },
                    ignore: "",
                    rules: {
                        txtBatchCode: {
                            required: true
                        },
                        txtReceivingQuantity: {
                            required: true
                        },
                        txtGeneralAppearance: {
                            required: true
                        },
                        txtTemperature: {
                            required: true
                        },
                        txtSendingQuantity: {
                            required: true
                        },

                    },
                    messages: {
                        txtBatchCode: {
                            required: "Please select a BatchCode."
                        },
                        txtReceivingQuantity: {
                            required: "Please select a ReceivingQuantity."
                        },
                        txtGeneralAppearance: {
                            required: "Please select a GeneralAppearance."
                        },
                        txtTemperature: {
                            required: "Please select a Temperature."
                        },
                        txtSendingQuantity: {
                            required: "Please select a SendingQuantity."
                        },


                    },
                    errorPlacement: function () {
                        return false;
                    },
                    invalidHandler: function (form, validator) {
                        if (validator.numberOfInvalids()) {
                            $(validator.errorList[0].element).focus();
                        }
                    }
                });
            }

            initializeValidation();



            // Submit handler for form
            // $('#frmDeHeading').on('submit', function (e) {
            //    e.preventDefault();
            //     $.ajax({
            //         url: 'Ajax/ajax-deheading-check-list.php',
            //         type: 'POST',
            //         data: {
            //             methodType: "getBatchList",
            //             ArrivalDate: $('#txtArrivalDate').val(),
            //         },
            //         success: function (data) {
            //             loadData();
            //             $('#frmDeHeading')[0].reset();
            //         }
            //     });
            // });


            $(document).on("change", "#txtArrivalDate", function () {
                $("#frmDeHeading").validate().resetForm();
        $("#frmDeHeading").find('.is-invalid').removeClass('is-invalid');
                
                getArrivals();
                loadData()

            });

      
    $(document).on("change", "#txtBatchCode", function () {
    $("#frmDeHeading").validate().resetForm();
    $("#frmDeHeading").find('.is-invalid').removeClass('is-invalid');

    var selected = $(this).find('option:selected');

    var counts = [];
    var headonWeights = [];
    var hlWeights =[];

    selected.each(function () {
        counts.push($(this).data('count'));
        headonWeights.push($(this).data('headonweight'));
        hlWeights.push($(this).data('hlweight'));
    });

    $('#txtCount').val(counts.join(", "));
    $('#txtReceivingQuantity').val(headonWeights.join(", "));
     $('#txtSendingQuantity').val(hlWeights.join(", "));
});



            function getArrivals() {
                $.ajax({
                    url: 'Ajax/ajax-deheading-check-list.php',
                    type: 'POST',
                    data: {
                        ArrivalDate: $('#txtArrivalDate').val(),
                        methodType: "GetBatchNumbers",
                        // removePlantCode: removePlantCode

                    },
                    success: function (data) {
                        console.log(data);
                        var list = "<option value=''>Select</option>";
                        if (data?.Data?.BatchData) {
                            $.each(data.Data.BatchData, function (index, value) {
                                list += "<option value='" + value.ArrivalId + "' data-count='" + value.ReceivingCount + "' data-headonweight='" + value.ReceivingQty + "' data-hlweight='" + value.HLWeight +"'  data-arrivalnumber = '"+ value.ArrivalNumber +"'>" + value.ArrivalNumber + "</option>";
                            });
                        }
                        // console.log(list)
                        $('#txtBatchCode').select2('destroy');
                        $('#txtBatchCode').html(list);
                        $('#txtBatchCode').select2();

                        return false;
                    }
                });
            }

            $(document).on("click", ".btnEdit", function () {
                if (confirm('Are you sure do you want to edit ?')) {
                    var row = $(this).closest("tr");
                    //   debugger
                    var Deheadingid = $(this).attr("data-id");
                    var arrivalDate = $(this).attr("data-arrival");
                    // var tdBatchCode = row.find(".tdBatchCode").data("value");
                    var tdBatchCodeRaw = row.find(".tdBatchCode").data("value");
                    var tdBatchCode = String(tdBatchCodeRaw || "");
                    var tdVariety = row.find(".tdVariety").text();
                    var tdCount = row.find(".tdCount").text();
                    var tdReceivingQuantity = row.find(".tdReceivingQuantity").text();
                    var tdGeneralAppearance = row.find(".tdGeneralAppearance").text();
                    var tdColour = row.find(".tdColour").text();
                    var tdFreshness = row.find(".tdFreshness").text();
                    var tdOdour = row.find(".tdOdour").text();
                    var tdTexture = row.find(".tdTexture").text();
                    var tdTemperature = row.find(".tdTemperature").text();
                    var tdDeterioration = row.find(".tdDeterioration").text();
                    var tdSendingQuantity = row.find(".tdSendingQuantity").text();
                    var tdDiscolouration = row.find(".tdDiscolouration").text();
                    var tdBlackSpots = row.find(".tdBlackSpots").text();
                    var tdSoftShell = row.find(".tdSoftShell").text();
                    var tdBrokenDamaged = row.find(".tdBrokenDamaged").text();
                    var tdReceivingTime = row.find(".tdReceivingTime").text();
                    var tdSendingTime = row.find(".tdSendingTime").text();
                    var tdHangingMeat = row.find(".tdHangingMeat").text();
                     var tdAttachedLegs = row.find(".tdAttachedLegs").text();
                     var tdInspectedBy = row.find(".tdInspectedBy").text();
                    var tdVerifiedBy = row.find(".tdVerifiedBy").text();
                    var tdForeignMeterial = row.find(".tdForeignMeterial").text();
                     var tdRemarks = row.find(".tdRemarks").text();



                    
                    // console.log(tdRemarks);

                    $("#hdnID").val(Deheadingid);
                    $("#txtArrivalDate").val(arrivalDate);
                    $("#txtBatchCode").val(tdBatchCode.split(',')).trigger("change");
                    $("#txtVariety").val(tdVariety).trigger("change");
                    $("#txtCount").val(tdCount.split(',')).trigger("change");
                    // $("#txtReceivingQuantity").val(tdReceivingQuantity).trigger("change");
                     $("#txtReceivingQuantity").val(tdReceivingQuantity.split(',')).trigger("change");
                    $("#txtGeneralAppearance").val(tdGeneralAppearance).trigger("change");
                    $("#txtColour").val(tdColour).trigger("change");
                    $("#txtFreshness").val(tdFreshness).trigger("change");
                    $("#txtOdour").val(tdOdour).trigger("change");
                    $("#txtTexture").val(tdTexture).trigger("change");
                    $("#txtTemperature").val(tdTemperature).trigger("change");
                    $("#txtDeterioration").val(tdDeterioration).trigger("change");
                    $("#txtSendingQuantity").val(tdSendingQuantity).trigger("change");
                    $("#txtDiscolouration").val(tdDiscolouration).trigger("change");
                    $("#txtBlackSpots").val(tdBlackSpots).trigger("change");
                    $("#txtSoftShell").val(tdSoftShell).trigger("change");
                    $("#txtBrokenDamaged").val(tdBrokenDamaged).trigger("change");
                    $("#drpVerifiedBy").val(tdVerifiedBy).trigger("change");
                    $("#drpInspectedBy").val(tdInspectedBy).trigger("change");
                     
                    $("#txtReceivingTime").val(tdReceivingTime.substring(0, 5));
                    $("#txtSendingTime").val(tdSendingTime.substring(0, 5));
                   $("#txthangingmeat").val(tdHangingMeat).trigger("change");
                    $("#txtAttachedLegs").val(tdAttachedLegs).trigger("change");                  
                    $("#txtForeignMeterial").val(tdForeignMeterial).trigger("change");
                    $("#txtRemarks").val(tdRemarks).trigger("change");

                    $("button[name='submit']").text("Update").removeClass("btn-primary").addClass("btn-primary");

                    
                }
            });

            $(document).on("click", ".btnDelete", function () {


                if (confirm("Are you sure you want to delete this item ?") == true) {

                    var row = $(this).closest("tr");
                    //  debugger
                    var Deheadingid = $(this).attr("data-id");
                    $("#hdnID").val(Deheadingid);
                    $.ajax({
                        url: 'Ajax/ajax-deheading-check-list.php',
                        type: 'POST',
                        data: {
                            methodType: "deleteRow",
                            Deheadingid: $("#hdnID").val(),


                        },
                        success: function (data) {
                            console.log(data);
                            loadData();
                             $('#frmDeHeading')[0].reset();
                               $("button[name='submit']").text("Save").removeClass("btn-primary").addClass("btn-primary"); 
                               


                            if (data.Error) {
                                alert(data.Error);
                            } else {
                                alert(data.Msg);

                            }

                            return false;
                        }
                    });
                }

            });



            // $(document).on("click", ".submit", function () {
            $(document).ready(function () {
                $('#frmDeHeading').on('submit', function (e) {
                    e.preventDefault();

                      var BatchCode = $("#txtBatchCode").val(); 
                      var arrivalNumbers = $("#txtBatchCode option:selected").map(function() {
                            return $(this).data("arrivalnumber");
                        }).get();

                        // console.log("All arrivalNumbers:", arrivalNumbers);
                    //   console.log("BatchCode ",BatchCode.join(","))

                    if (!$("#frmDeHeading").valid()) {
                        console.log("invalid",$("#frmDeHeading").valid());

                        return;
                    }

                    $.ajax({
                        url: 'Ajax/ajax-deheading-check-list.php',
                        type: 'POST',
                        data: {
                            methodType: "TableSave",
                            Deheadingid: $("#hdnID").val(),
                            ArrivalDate: $("#txtArrivalDate").val(),
                            BatchCode: BatchCode.join(","),
                            ArrivalNumber: arrivalNumbers.join(","),
                            Variety: $("#txtVariety").val(),
                            Count: $("#txtCount").val(),
                            ReceivingQuantity: $("#txtReceivingQuantity").val(),
                            ReceivingTime: $("#txtReceivingTime").val(),
                            GeneralAppearance: $("#txtGeneralAppearance").val(),
                            Colour: $("#txtColour").val(),
                            Freshness: $("#txtFreshness").val(),
                            Odour: $("#txtOdour").val(),
                            Texture: $("#txtTexture").val(),
                            Temperature: $("#txtTemperature").val(),
                            Deterioration: $("#txtDeterioration").val(),
                            SendingQuantity: $("#txtSendingQuantity").val(),
                            Discolouration: $("#txtDiscolouration").val(),
                            BlackSpots: $("#txtBlackSpots").val(),
                            SoftShell: $("#txtSoftShell").val(),
                            BrokenDamaged: $("#txtBrokenDamaged").val(),
                            ForeignMeterial: $("#txtForeignMeterial").val(),
                            SendingTime: $("#txtSendingTime").val(),
                            HangingMeat: $("#txtHangingMeat").val(),
                             AttachedLegs: $("#txtAttachedLegs").val(),
                            VerifiedBy: $("#drpVerifiedBy").val(),
                            InspectedBy: $("#drpInspectedBy").val(),
                             Remarks: $("#txtRemarks").val(),   
                        },
                        success: function (data) {
                            if (data.Error) {
                                alert(data.Error);
                            } else {
                                alert(data.Msg);

                            }
                            loadData();
                            clearForm()
                            
                            isFirstSaveDone = true;
                            return false;

                        }
                    });
                }

                )
            }

            );
        });
    });
</script>


</script>

</body>

</html>