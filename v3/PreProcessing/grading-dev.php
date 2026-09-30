<?php 

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

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
$resDefects = $resDropdownsAPI->Data->Defects;

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

                <?php if(isset($_SESSION['Msg'])) { ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $Status ?>
                </div>
                <?php $_SESSION['Error'] = null; } ?>

                <?php if (isset($_SESSION['Error'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $Status ?>
                </div>
                <?php $_SESSION['Msg'] = null; } ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmGrading">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder="" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpLotCode" class="form-label">Arival/Lot Code<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpLotCode" multiple
                                                id="drpLotCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpProductionPlant"
                                                id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                <option value="<?php echo $Item->ProcessingStationID ?>">
                                                    <?php echo $Item->StationName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpCenter" class="form-label">Center<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpCenter"
                                                id="drpCenter">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCenter as $Item) { ?>
                                                <option value="<?php echo $Item->StationID; ?>">
                                                    <?php echo $Item->UnitName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">Transaction Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate" name="txtTransactionDate"
                                            value="<?php echo date("Y-m-d"); ?>"  max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGradingMachine" class="form-label">Grading Machine <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpGradingMachine"
                                                id="drpGradingMachine">
                                                <option value=""> Select</option>
                                                <?php foreach ($resGradingmachine as $Item) { ?>
                                                <option value="<?php echo $Item->MachineID; ?>">
                                                    <?php echo $Item->MachineName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-sm-3">
                                            <label for="txtStartTime" class="form-label">Start Time <span
                                                    style="color:red">*</span> 
                                                    <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                            </label>
                                            <input type="time" class="form-control" id="txtStartTime" name="txtStartTime" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtEndTime" class="form-label">End Time <span
                                                    style="color:red">*</span></label>

                                            <input type="time" class="form-control" id="txtEndTime"
                                                name="txtEndTime" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtBreakTime" class="form-label">Break Time(Minutes) <span
                                                    style="color:red">*</span></label>

                                            <input type="text" maxlength="3" class="form-control" id="txtBreakTime" name="txtBreakTime" />
                                        </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpDefects" class="form-label">Defects</label>
                                            <select class="drpSelect" name="drpDefects" id="drpDefects">
                                                <option value=""> Select</option>
                                                <?php 
                                                    foreach ($resDefects as $Item) { 
                                                        echo '<option value="'.$Item->DefectID.'"> '.$Item->DefectName .'</option>';
                                                    } 
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalCount" class="form-label">Arrival Count </label>
                                            <input type="number" class="form-control" id="txtArrivalCount"
                                                name="txtArrivalCount" placeholder="Enter Arrival Count">
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalWeight" class="form-label">Arrival Weight(Kgs)
                                            </label>
                                            <input type="text" class="form-control" id="txtArrivalWeight"
                                                name="txtArrivalWeight" placeholder=" Enter Arrival Weight(Kgs)">
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
                                            <label for="txtNoOfCrates" class="form-label">Enter No Of Crates</label>
                                            <input type="text" class="form-control" id="txtNoOfCrates"
                                                name="txtNoOfCrates" placeholder="Enter Enter No Of Crates">
                                        </div>
                                    </div>
                                 
                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <a href="rm-arrival-list" class="btn btn-danger">Close</a>
                                        
                                        <a href="javascript:void(0)" class="btn btn-success waves-effect waves-light">
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

                        <div id="divDataList">
                        
                            <!-- <div class="card">
                                <div class="card-body">
                                    <table id="tblDataTable" class="table table-hover table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:50px;">S.No</th>
                                                <th>Date</th>
                                                <th>LotId </th>
                                                <th>Variety</th>
                                                <th>Count</th>
                                                <th>Weight</th>
                                                <th style="width: 60px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr >
                                                <td>1</td>
                                                <td>02/05/2023</td>
                                                <td>12546</td>
                                                <td>125</td>
                                                <td>100</td>
                                                <td>25.256</td>
                                                <td>
                                                    <button type="button" id="btnDelete" name="btnDelete" data-ArrivalBrkUpID="'. $item->ArrivalBrkUpID.'" class="btn btn-link p-0 deleteBrkUp" '. $DeleteAccess .' >
                                                        <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr >
                                                <td></td>
                                                <td>Total</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td>25.256</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div> -->


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

$(document).on('keydown', 'input[pattern]', function(e){
  var input = $(this);
  var oldVal = input.val();
  var regex = new RegExp(input.attr('pattern'), 'g');

  setTimeout(function(){
    var newVal = input.val();
    if(!regex.test(newVal)){
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

    var frmData = {
        methodType: "get",
        ArrivalID:arrivalID,
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
            console.log(resJson);

            $("#divDataList").html(htmlData);
            fnShowDataTable();

            if (arrivalID) {
                bindFormData(resJson);
            }
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
        },
        success: function(data) {
           console.log(data.Data);

            var list = "<option>Select</option>";
            $.each(data.Data, function (index,value) {
                list +=  "<option value='"+value.RmArrivalID+"'>"+value.ArrivalNumber+" - "+ value.ArrivalCount+" - "+value.ArrivalWeight+"</option>";                
            });
            $('#drpLotCode').html(list);
            $('#drpLotCode').trigger("change");            
            return false;
        }
    });
});

function bindFormData(resJson) {
    
    $("#txtArrivalDate").val("");
    $("#drpLotCode").val(resJson.ArrivalID).trigger('change');
    $("#drpProductionPlant").val(resJson.PlantID).trigger('change');
    $("#drpCenter").val(resJson.StationID).trigger('change');
    $("#txtTransactionDate").val(resJson.TransactionDate);
    $("#drpGradingMachine").val(resJson.MachineID).trigger('change');    
    $("#txtStartTime").val(resJson.StartTime);    
    $("#txtEndTime").val(resJson.EndTime);
    $("#txtBreakTime").val(resJson.BreakTime);
    $("#drpDefects").val(resJson.Defect).trigger('change');
    $("#txtArrivalCount").val(resJson.Count);
    $("#txtArrivalWeight").val(resJson.Weight);
    // $("#drpMasterRates").val(resJson.MasterRates).trigger('change');
    $("#txtNoOfCrates").val(resJson.NoCrates);
}

function removeQueryParameters(url) {
    const urlObject = new URL(url);
    urlObject.search = '';
    return urlObject.toString();
}

$(document).on("click", ".newItem", function() {

    $("#txtTransactionDate, #txtStartTime, #txtEndTime, #txtBreakTime, #txtArrivalCount, #txtArrivalWeight, #txtNoOfCrates").val("");
    $("#drpLotCode, #drpProductionPlant, #drpCenter, #drpDefects, #drpGradingMachine").val("").trigger('change');
    arrivalID = "";

    table.clear().draw();
    $("#divDataList").remove();
    $("#hdnNewArrival").val("NEW");

    var url = window.location.href;
    var newUrl = removeQueryParameters(url);
    window.history.replaceState(null, null, newUrl);

    return false;
});

$(document).on("change", "#drpLotCode", function() {
    $("#txtTransactionDate, #txtStartTime, #txtEndTime, #txtBreakTime, #txtArrivalCount, #txtArrivalWeight, #txtNoOfCrates").val("");
    $("#drpProductionPlant, #drpCenter, #drpDefects, #drpGradingMachine").val("").trigger('change');
    
    var arrid = $(this).val();

    var lots = $('#drpLotCode').val();
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
                console.log(htmlData);

                $("#divDataList").html(htmlData);
                fnShowDataTable();
                
                $("#btnOverallDelete" ).prop( "disabled", false);

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
        drpDefects: {
            required: true,
            noSpace: true
        },
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
        lots = lots.join("_");
        var lastArrivalId = lots.split("_").pop();

        $.ajax({
            url : 'Ajax/ajax-grading.php',
            type : 'POST',
            data  : {
                methodType : "post",
                ArrivalDate : $("#txtArrivalDate").val(),
                ArrivalID : lastArrivalId,
                MixIds:lots,
                drpProductionPlant : $("#drpProductionPlant").val(),
                drpCenter : $("#drpCenter").val(),
                txtTransactionDate : $("#txtTransactionDate").val(),
                drpGradingMachine : $("#drpGradingMachine").val(),
                txtStartTime : $("#txtStartTime").val(),
                txtEndTime : $("#txtEndTime").val(),
                txtBreakTime : $("#txtBreakTime").val(),
                drpDefects : $("#drpDefects").val(),
                txtArrivalCount : $("#txtArrivalCount").val(),
                txtArrivalWeight : $("#txtArrivalWeight").val(),
                // drpMasterRates : $("#drpMasterRates").val(),
                txtNoOfCrates : $("#txtNoOfCrates").val(),
            },
            success : function(data) {
                var data = JSON.parse(data);
                var htmlData = data.html;
                var resJson = data.json;
                var error = data.Error;
                var msg = data.Msg;

                console.log(data);
                console.log(resJson);

                if (error === null) {
                    alert(msg);
                } else {
                    alert(error);
                    return false;
                }
                $("#divDataList").html(htmlData);
                fnShowDataTable();

                $("#txtArrivalCount, #txtArrivalWeight, #txtNoOfCrates").val("");
                $("#drpDefects").val("").trigger('change');

                $("#btnOverallDelete" ).prop( "disabled", false);
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

                $("#btnOverallDelete" ).prop( "disabled", false);

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
        ArrivalID: lastArrivalId,
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