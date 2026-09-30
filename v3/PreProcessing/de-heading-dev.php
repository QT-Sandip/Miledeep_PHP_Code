<?php 

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

// var_dump($ArrivalID);
// exit;
$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID
);
// var_dump($bodyContent);
// exit;


$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resDropdownsAPI);

$resCenter = $resDropdownsAPI->Data->Center;
$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resShift = $resDropdownsAPI->Data->Shift;
$resSupervisor = $resDropdownsAPI->Data->Supervisor;
// $ListItems = $resDropdownsAPI->Data->DeheadingData;
// echo json_encode($ListItems, JSON_PRETTY_PRINT);
// exit;

// $error = $resDropdownsAPI->Error;
$List = $resDropdownsAPI->Data;

// $List = $resDropdownsAPI->Data->DeheadingData;

// echo json_encode($List, JSON_PRETTY_PRINT);
// exit;

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
                                <form id="frmDeHeading">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder="" />
                                                
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpAriavalLotCode" class="form-label">Arival/Lot Code<span
                                                    style="color:red">*</span>
                                                    <button type="button" class="btn btn-success" style="padding: 0px 5px;margin-left: 10px;">Search</button>
                                                </label>

                                            <select class="drpSelect" name="drpAriavalLotCode"
                                                id="drpAriavalLotCode">
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
                                                <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                    <?php echo $Item->StationName; ?></option>
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
                                            <label for="drpShift" class="form-label">Select Shift <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpShift"
                                                id="drpShift">
                                                <option value=""> Select</option>
                                                <?php foreach ($resShift as $Item) { ?>
                                                <option value="<?php echo $Item->OptID; ?>">
                                                    <?php echo $Item->Name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-sm-3">
                                            <label for="drpSuperVisor" class="form-label">SuperVisor <span
                                                    style="color:red">*</span> 
                                                    <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                            </label>
                                            <select class="drpSelect" name="drpSuperVisor" id="drpSuperVisor">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSupervisor as $Item) { ?>
                                                <option value="<?php echo $Item->ID; ?>">
                                                    <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtHeadONCount" class="form-label">HeadON Count After Checking Count-2 <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtHeadONCount" pattern="^\d*(\.\d{0,2})?$"
                                                name="txtHeadONCount"  />
                                        </div>

                                        

                                        <div class="form-group col-sm-3">
                                            <label for="txtIceConsumption" class="form-label">Ice Consumption <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtIceConsumption" pattern="^\d*(\.\d{0,2})?$"
                                                name="txtIceConsumption"  />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <hr />
                                        <div class="form-group col-sm-3">
                                            <label for="txtHLCount" class="form-label">HL Count <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtHLCount" pattern="^\d*(\.\d{0,2})?$"
                                                name="txtHLCount"  />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtHLQuantity" class="form-label">HL Quantity(Kgs) <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtHLQuantity"  pattern="^\d*(\.\d{0,3})?$"
                                                name="txtHLQuantity"  />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtCountNetYield" class="form-label">Count Net Yield(%) <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtCountNetYield" max="99" min="1" pattern="^\d{0,2}\.?\d{0,2}?$"
                                                name="txtCountNetYield" />
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <!-- <a href="rm-arrival-list" class="btn btn-danger">Close</a> -->                                        
                                        <a href="javascript:void(0)" class="newItem btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New
                                        </a>
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
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
                                                
                                                <th>LotId </th>
                                                <th>Production Plant</th>
                                                <th>Center</th>
                                                <th>Transaction Date</th>
                                                <th>Select Shift</th>
                                                <th>SuperVisor</th>
                                                <th>HeadON Count After Checking Count-2</th>
                                                <th>HL Count</th>
                                                <th>HL Quantity(Kgs)</th>
                                                <th>Count Net Yield(%)</th>
                                                <th>Ice Consumption</th>
                                                <th>Remarks</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php // foreach ($List as $item) { ?>
                                            <tr >
                                                <td><?php // echo $item->SNO ?></td>                                                
                                                <td><?php // echo $item->LotNo ?></td>
                                                <th><?php // echo $item->Plant ?></th>
                                                <th><?php // echo $item->Center ?></th>
                                                <th><?php // echo $item->TransactionDate ?></th>
                                                <th><?php // echo $item->Shift ?></th>
                                                <th><?php // echo $item->UserName ?></th>
                                                <th><?php // echo $item->HeadonCountAfterCheckingCount2 ?></th>
                                                <th><?php // echo $item->HLCount ?></th>
                                                <th><?php // echo $item->HLQuantityKgs ?></th>
                                                <th><?php // echo $item->CountNetYield ?></th>
                                                <th><?php // echo $item->IceConsumption ?></th>
                                                <th><?php // echo $item->Remarks ?></th>
                                                <td>
                                                    <button type="button" id="btnDelete" name="btnDelete" data-ArrivalBrkUpID="'. $item->ArrivalBrkUpID.'" class="btn btn-link p-0 deleteBrkUp" '. $DeleteAccess .' >
                                                        <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php // } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td>Total</td>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th><span style="font-weight: 600;font-size: 16px;"><?php // echo $item->TotalHLQuantity ?></span></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
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
            title: 'De-Heading-<?php echo date("d-m-Y") ?>',
            footer: true,
            exportOptions: {
                columns: ':visible'
            }
        }],
    });
}

$(document).ready(function() {
    // fnShowDataTable();

    var frmData = {
        methodType: "get",
        ArrivalID: arrivalID,
    }
    $.ajax({
        url: 'Ajax/AjaxDeHeading.php',
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
        url: 'Ajax/AjaxDeHeading.php',
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
            $('#drpAriavalLotCode').html(list);
            $('#drpAriavalLotCode').trigger("change");            
            return false;
        }
    });
});


function bindFormData(resJson) {
    
    $("#txtArrivalDate").val("");
    $("#drpAriavalLotCode").val(resJson.ArrivalID).trigger('change');
    $("#drpProductionPlant").val(resJson.PlantID).trigger('change');
    $("#drpCenter").val(resJson.CenterID).trigger('change');

    $("#txtTransactionDate").val(resJson.TransactionDate);
    $("#drpShift").val(resJson.ShiftID).trigger('change');    
    $("#drpSuperVisor").val(resJson.SupervisorID).trigger('change');    
    $("#txtHeadONCount").val(resJson.HeadonCountAfterCheckingCount2);
    $("#txtHLCount").val(resJson.HLCount);
    $("#txtHLQuantity").val(resJson.HLQuantityKgs);
    $("#txtCountNetYield").val(resJson.CountNetYield);
    $("#txtIceConsumption").val(resJson.IceConsumption);
    $("#txtRemarks").val(resJson.Remarks);

    // funFormChange();
}

function removeQueryParameters(url) {
    const urlObject = new URL(url);
    urlObject.search = '';
    return urlObject.toString();
}


$(document).on("click", ".newItem", function() {

    $("#txtTransactionDate, #txtHeadONCount, #txtHLCount, #txtHLQuantity, #txtCountNetYield, #txtIceConsumption, #txtRemarks").val("");
    $("#drpAriavalLotCode, #drpProductionPlant, #drpCenter, #drpShift, #drpSuperVisor").val("").trigger('change');
    arrivalID = "";

    table.clear().draw();
    // $("#divDataList").remove();
    $("#hdnNewArrival").val("NEW");

    var url = window.location.href;
    var newUrl = removeQueryParameters(url);
    window.history.replaceState(null, null, newUrl);

    return false;
});

$(document).on("change", "#drpAriavalLotCode", function() {
    $("#txtTransactionDate, #txtHeadONCount, #txtHLCount, #txtHLQuantity, #txtCountNetYield, #txtIceConsumption, #txtRemarks").val("");
    $("#drpProductionPlant, #drpCenter, #drpShift, #drpSuperVisor").val("").trigger('change');

    var aid = $(this).val();
    // console.log(aid);
    if(aid != "Select"){
        var frmData = {
            methodType: "get",
            ArrivalID: aid,
        }
        $.ajax({
            url: 'Ajax/AjaxDeHeading.php',
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

                // if (arrivalID) {
                //     bindFormData(resJson);
                // }
                return false;
            }
        });
    }
    
    // $("#divDataList").html(htmlData);
    // fnShowDataTable();
});


$("#frmDeHeading").validate({
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
        drpAriavalLotCode: {
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
        drpShift: {
            required: true,
            selectcheck: true
        },
        drpSuperVisor: {
            required: true,
            selectcheck: true
        },
        txtHeadONCount: {
            required: true
        },
        txtHLCount: {
            required: true
        },
        txtHLQuantity: {
            required: true
        },
        // txtCountNetYield: {
        //     required: true
        // },
        // txtIceConsumption: {
        //     required: true
        // }
    },
    errorPlacement: function() {
        return false; // suppresses error message text
    },
    submitHandler: function() {

        // var isInsert = '<?php  echo isset($ArrivalID) ?>';
        // if(isInsert.length > 0) {

        $.ajax({
            url: 'Ajax/AjaxDeHeading.php',
            type: 'POST',
            data: {
                methodType: "post",
                ArrivalDate: $("#txtArrivalDate").val(),
                ArrivalID: $("#drpAriavalLotCode").val(),
                drpProductionPlant: $("#drpProductionPlant").val(),
                drpCenter: $("#drpCenter").val(),
                txtTransactionDate: $("#txtTransactionDate").val(),
                drpShift: $("#drpShift").val(),
                drpSuperVisor: $("#drpSuperVisor").val(),
                txtHeadONCount: $("#txtHeadONCount").val(),
                txtHLCount: $("#txtHLCount").val(),
                txtHLQuantity: $("#txtHLQuantity").val(),
                txtCountNetYield: $("#txtCountNetYield").val(),
                txtIceConsumption: $("#txtIceConsumption").val(),
                txtRemarks: $("#txtRemarks").val()
            },
            success: function(data) {
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

                // if (arrivalID == "") {
                //     if($("#hdnNewArrival").val() == "NEW") {
                //         var newUrl = "/v3/PreProcessing/de-heading-dev"+ "?ArrivalID=" + resJson.RmArrivalID;
                //     }else {
                //         var newUrl = window.location.href + "?ArrivalID=" + resJson.RmArrivalID;
                //     }
                //     // window.history.pushState({}, '', currentUrl);
                //     window.location.href = newUrl;
                // }

                $("#divDataList").html(htmlData);
                fnShowDataTable();

                $("#txtHLCount, #txtHLQuantity, #txtCountNetYield").val("");

                // if (arrivalID) {
                //     bindFormData(resJson);
                // }
                // $("#drpVariety, #drpMasterRates").val("").trigger('change');
                // $("#txtArrivalCount, #txtArrivalWeight").val("");
                // $("#txtArrivalRate").val("");

                return false;
            }
        });
        // } else {
        //     return true;
        // }                        
    }
});


$(document).on("click", ".deleteBrkUp", function() {
    var arrId = $(this).attr("data-ArrivalId");
    var brkUpID = $(this).attr("data-ArrivalBrkUpID");
    console.log(brkUpID);

    var frmData = {
        methodType: "delete",
        ArrivalID: arrId,
        BreakUpID: brkUpID,
    }
    if (confirm("Are you sure you want to delete this item ?") == true) {
        $.ajax({
            url: 'Ajax/AjaxDeHeading.php',
            type: 'POST',
            data: frmData,
            success: function(data) {
                var data = JSON.parse(data);
                console.log(data);
                var htmlData = data.html;
                var resJson = data.json;

                $("#divDataList").html(htmlData);
                fnShowDataTable();

                if (arrivalID) {
                    bindFormData(resJson);
                }
                return false;
            }
        });
    }

});

</script>

</body>

</html>