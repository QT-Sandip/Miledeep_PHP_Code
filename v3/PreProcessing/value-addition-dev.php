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

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resDropdownsAPI);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resCenter = $resDropdownsAPI->Data->Center;
$resShift = $resDropdownsAPI->Data->Shift;
$resVariety = $resDropdownsAPI->Data->Variety;

$List = $resDropdownsAPI->Data;
// echo json_encode($List, JSON_PRETTY_PRINT);
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
                                <form id="frmValAdd">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder="" />
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpLotCode" class="form-label">Arival/Lot Code<span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpLotCode" id="drpLotCode">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPurchaseCenters as $Item) { ?>
                                                <option value="<?php echo $Item->PurchaseStationID; ?>">
                                                    <?php echo $Item->StationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpProductionPlant" id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                <option value="<?php echo $Item->ProcessingStationID ?>">
                                                    <?php echo $Item->StationName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpCenter" class="form-label">Center<span style="color:red">*</span></label>
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
                                            <input type="date" class="form-control" id="txtTransactionDate" name="txtTransactionDate"
                                            value="<?php echo date("Y-m-d"); ?>"  max="<?php echo date('Y-m-d'); ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpShift" class="form-label">Select Shift <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpShift" id="drpShift">
                                                <option value=""> Select</option>
                                                <?php foreach ($resShift as $Item) { ?>
                                                <option value="<?php echo $Item->OptID; ?>">
                                                    <?php echo $Item->Name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label for="txtIceConsumption" class="form-label">Ice Consumption <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtIceConsumption" pattern="^\d*(\.\d{0,2})?$" name="txtIceConsumption"  />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50" id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">

                                        <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Select Variety <span style="color:red">*</span> 
                                                    <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                            </label>
                                            <select class="drpSelect" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                                <?php foreach ($resVariety as $Item) { ?>
                                                <option value="<?php echo $Item->OptID; ?>">
                                                    <?php echo $Item->Name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtHLCount" class="form-label">HL Count <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtHLCount" pattern="^\d*(\.\d{0,2})?$" name="txtHLCount"  />
                                        </div> 

                                         <div class="form-group col-sm-3">
                                            <label for="txtHLWt" class="form-label">HL Wt(Kgs) <span style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtHLWt" pattern="^\d*(\.\d{0,3})?$" name="txtHLWt"  />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtReceivedHLWt" class="form-label">Received HL Wt(Kgs) <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtReceivedHLWt" pattern="^\d*(\.\d{0,3})?$" name="txtReceivedHLWt"  />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtWeightAfterVA" class="form-label">Weight After VA(Kgs) <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtWeightAfterVA" pattern="^\d*(\.\d{0,3})?$" name="txtWeightAfterVA"  />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtCountAfterVA" class="form-label">Count After VA <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtCountAfterVA" pattern="^\d*(\.\d{0,2})?$" name="txtCountAfterVA"  />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtReturnWt" class="form-label">Return Wt(Kgs) <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtReturnWt" pattern="^\d*(\.\d{0,3})?$" name="txtReturnWt" />
                                        </div>

                                    </div>
                                    
                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <a href="rm-arrival-list" class="btn btn-danger">Close</a>
                                        
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
                                                <th>Variety </th>
                                                <th>HL Wt(Kgs)</th>
                                                <th>HL Count</th>
                                                <th>Received HL Wt(Kgs)</th>
                                                <th>Weight after VA(Kgs)</th>
                                                <th>Count after VA </th>
                                                <th>Return Wt(Kgs)</th>
                                                <th>Ice Consumption</th>
                                                <th>Remarks</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr >
                                                <td>1</td>
                                                <td>02/05/2023</td>
                                                <td>12546</td>
                                                <td>Production Plant 1</td>
                                                <td>Center 1</td>
                                                <td>02/05/2023</td>
                                                <td>Shift List</td>
                                                <td>SuperVisor</td>
                                                <td>25.25</td>
                                                <td>25.205</td>
                                                <td>25.25</td>
                                                <td>25.050</td>
                                                <td>25.050</td>
                                                <td>25.05</td>
                                                <td>25.050</td>
                                                <td>25.25</td>
                                                <td>Any Remarks </td>
                                                <td>
                                                    <button type="button" id="btnDelete" name="btnDelete" data-ArrivalBrkUpID="'. $item->ArrivalBrkUpID.'" class="btn btn-link p-0 deleteBrkUp" '. $DeleteAccess .' >
                                                        <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td>Total</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td><span style="font-weight: 600;font-size: 16px;">25.222</span></td>
                                                <td></td>
                                                <td><span style="font-weight: 600;font-size: 16px;">25.222</span></td>
                                                <td><span style="font-weight: 600;font-size: 16px;">25.222</span></td>
                                                <td></td>
                                                <td><span style="font-weight: 600;font-size: 16px;">25.222</span></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td><span  style="font-weight: 600;font-size: 16px;">Grand Total</span></td> 
                                                <td></td>
                                                <td></td>
                                                <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->WeightTotal . '</span></td>
                                                <td></td>
                                                <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->AvgRate . '</span></td>
                                                <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->ValueTotal . '</span></td>
                                                <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->BillAmtTotal . '</span></td>
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
        methodType : "get",
        ArrivalID : arrivalID,
    }
    $.ajax ({
        url : 'Ajax/ajax-value-addition.php',
        type : 'POST',
        data : frmData,
        success : function(data) {
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

$(document).on("change", "#txtArrivalDate", function(){
    $.ajax({
        url : 'Ajax/ajax-value-addition.php',
        type : 'POST',
        data : {
            ArrivalDate : $(this).val(),
        },
        success : function(data) {
            console.log(data.Data);

            var list = "<option>Select</option>";
            $.each(data.Data, function(index,value){
                list += "<option value='"+value.RmArrivalID+"'>"+value.ArrivalNumber+" - "+value.ArrivalCount+" - "+value.ArrivalWeight+"</option>";
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
    $("#drpProductionPlant").val(resJson,PlantID).trigger('change');
    $("#drpCenter").val(resJson.CenterID).trigger('change');
    $("#txtTransactionDate").val(resJson.TransactionDate);
    $("#drpShift").val(resJson.ShiftID).trigger('change');
    $("#txtIceConsumption").val(resJson.IceConsumption);
    $("#txtRemarks").val(resJson.Remarks);
    $("#drpVariety").val(resJson.VarietyID).trigger('change');
    $("#txtHLCount").val(resJson.HLCount);
    $("#txtHLWt").val(resJson.HLQuantityKgs);
    $("#txtReceivedHLWt").val(resJon.ReceivedWeight);
    $("#txtWeightAfterVA").val(resJon.WtAftrVA);
    $("#txtCountAfterVA").val(resJson.CountAftrVA);
    $("#txtReturnWt").val(resJson.ReturnWeight);
    
}

function removeQueryParameters(url) {
    const urlObject = new URL(url);
    urlObject.search = '';
    return urlObject.toString();
}

$(document).on("click", ".newItem", function() {

    $("#txtTransactionDate, #txtIceConsumption, #txtRemarks, #txtHLCount, #txtHLWt, #txtReceivedHLWt, #txtReturnWt, #txtWeightAfterVA, #txtCountAfterVA").val("");
    $("#drpLotCode, #drpProductionPlant, #drpCenter, #drpShift, #drpVariety").val("").trigger('change');
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
    $("#txtTransactionDate, #txtIceConsumption, #txtRemarks, #txtHLCount, #txtHLWt, #txtReceivedHLWt, #txtReturnWt, #txtWeightAfterVA, #txtCountAfterVA").val("");
    $("#drpProductionPlant, #drpCenter, #drpShift, #drpVariety").val("").trigger('change');
    
    var aid = $(this).val();
    // console.log(aid);
    if(aid != "Select"){
        var frmData = {
            methodType: "get",
            ArrivalID: aid,
        }
        $.ajax({
            url: 'Ajax/ajax-value-addition.php',
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
});

$("#frmValAdd").validate({
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
        drpShift: {
            required: true,
            noSpace: true
        },
        txtIceConsumption: {
            required: true,
            selectcheck: true
        },
        drpVariety: {
            required: true,
            selectcheck: true
        },
        txtHLCount: {
            required: true,
            selectcheck: true
        },
        txtHLWt: {
            required: true,
            noSpace: true
        },
        txtReceivedHLWt: {
            required: true,
            noSpace: true
        },
        txtWeightAfterVA: {
            required: true,
            selectcheck: true
        },
        txtCountAfterVA: {
            required: true,
            noSpace: true
        },
        txtReturnWt: {
            required: true,
            selectcheck: true
        },
    },
    errorPlacement: function() {
        return false; 
    },
    submitHandler: function() {
        $.ajax({
            url : 'Ajax/ajax-value-addition.php',
            type : 'POST',
            data  : {
                methodType : "post",
                ArrivalDate : $("#txtArrivalDate").val(),
                ArrivalID : $("#drpLotCode").val(),
                drpProductionPlant : $("#drpProductionPlant").val(),
                drpCenter : $("#drpCenter").val(),
                txtTransactionDate : $("#txtTransactionDate").val(),
                drpShift : $("#drpShift").val(),
                txtIceConsumption : $("#txtIceConsumption").val(),
                txtRemarks : $("#txtRemarks").val(),
                drpVariety : $("#drpVariety").val(),
                txtHLCount : $("#txtHLCount").val(),
                txtHLWt : $("#txtHLWt").val(),
                txtReceivedHLWt : $("#txtReceivedHLWt").val(),
                txtWeightAfterVA : $("#txtWeightAfterVA").val(),
                txtCountAfterVA : $("#txtCountAfterVA").val(),
                txtReturnWt : $("#txtReturnWt").val()
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

                    $("#txtHLCount, #txtHLWt, #txtReceivedHLWt, #txtWeightAfterVA, #txtCountAfterVA, #txtReturnWt").val("");
                    $("#drpVariety").val("").trigger('change');
                    return false;
                }
        });
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
            url: 'Ajax/ajax-value-addition.php',
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