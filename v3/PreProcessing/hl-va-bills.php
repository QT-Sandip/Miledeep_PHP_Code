    <?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$RstrictNoOfPersons = $ClientConfigData->RstrictNoOfPersons;  //anil new client requirement

$BillID = trim($_GET['BillID']);

$BillID = isset($_GET['BillID']) && !empty($_GET['BillID']) ? $_GET['BillID'] : "";

$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'BillID' => $BillID
);
// var_dump($bodyContent);
// exit;
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resDropdownsAPI);die;
//  echo json_encode($resDropdownsAPI, JSON_PRETTY_PRINT);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resCenter = $resDropdownsAPI->Data->Center;
$resVariety = $resDropdownsAPI->Data->HlVaBillVariety;
$resShift = $resDropdownsAPI->Data->Shift;
$resContractor = $resDropdownsAPI->Data->Contractor;
$resType = $resDropdownsAPI->Data->Type;
$resSupplier = $resDropdownsAPI->Data->Supplier;
$resSubContractor = $resDropdownsAPI->Data->SubContractor;

$List = $resDropdownsAPI->Data;
// var_dump($resVariety);
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

                <?php if (isset($_SESSION['Msg'])) { ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $Status ?>
                    </div>
                    <?php $_SESSION['Error'] = null;
                } ?>

                <?php if (isset($_SESSION['Error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $Status ?>
                    </div>
                    <?php $_SESSION['Msg'] = null;
                } ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmHlVaBill">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">Transaction Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate" name="txtTransactionDate"
                                                value="<?php echo date("Y-m-d"); ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpProductionPlant" id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID ?>">
                                                        <?php echo $Item->StationName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpCenter" class="form-label">Center<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpCenter" id="drpCenter">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCenter as $Item) { ?>
                                                    <option value="<?php echo $Item->StationID; ?>">
                                                        <?php echo $Item->UnitName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpType" class="form-label">Type <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpType" id="drpType" 
                                                <?php 
                                                    if(!empty($BillID)){
                                                        echo "disabled";
                                                    }
                                                ?>
                                            >
                                                <option value=""> Select</option>
                                                <option value="1">HL</option>
                                                <option value="2">Peeling</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSupplier" class="form-label">Supplier Type <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSupplier" id="drpSupplier"
                                             <?php 
                                                    if(!empty($RstrictNoOfPersons) && !empty($BillID)){
                                                        echo "disabled";
                                                    }
                                                ?>>
                                                <option value=""> Select</option>
                                                <option value="1">Contractor</option>
                                                <option value="2">Mestri</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpContractor" class="form-label">Contractor/Mestri <span
                                                    style="color:red">*</span></label>
                                            <!-- <input type="hidden" value="<?php // echo json_encode($resContractor, JSON_PRETTY_PRINT); ?>" id="hdnContractor" /> -->
                                            <select class="drpSelect" name="drpContractor" id="drpContractor"
                                             <?php 
                                                    if(!empty($RstrictNoOfPersons) && !empty($BillID)){
                                                        echo "disabled";
                                                    }
                                                ?>>
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpBatch" class="form-label">Batch <span
                                                    style="color:red">*</span></label>
                                            <!-- <input type="hidden" value='<?php // echo $resSubContractor ?>' id="hdnSubContractor" /> -->
                                            <select class="drpSelect" name="drpBatch" id="drpBatch"
                                             <?php 
                                                    if(!empty($RstrictNoOfPersons) && !empty($BillID)){
                                                        echo "disabled";
                                                    }
                                                ?>>
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPerson" class="form-label">No of Persons<span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtPerson" name="txtPerson"
                                                placeholder="Enter No of Persons" <?php echo !empty($RstrictNoOfPersons)?'readonly="readonly"':'';?>/>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpShift" class="form-label">Shift <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpShift" id="drpShift">
                                                <option value=""> Select</option>
                                                <?php foreach ($resShift as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID; ?>">
                                                        <?php echo $Item->Name; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtAdjustments" class="form-label">Adjustments<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtAdjustments" name="txtAdjustments"
                                                placeholder="" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtExtras" class="form-label">Extras<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtExtras" name="txtExtras"
                                                placeholder="" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                        

                                    </div>

                                    <hr>
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder="" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpLotCode" class="form-label">Arival/Lot Code<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpLotCode" id="drpLotCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Select Variety <span
                                                    style="color:red">*</span>
                                                <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                            </label>
                                            <select class="drpSelect" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                                <?php foreach ($resVariety as $Item) { ?>
                                                    <option value="<?php echo $Item->VarietyID; ?>">
                                                        <?php echo $Item->ShortName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtHLCount" class="form-label">HL Count <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtHLCount"
                                                pattern="^\d*(\.\d{0,2})?$" name="txtHLCount" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtHLWt" class="form-label">HL Wt(Kgs) <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtHLWt"
                                                pattern="^\d*(\.\d{0,3})?$" name="txtHLWt" />
                                        </div>  
                                        <?php 
                                        if(!empty($RstrictNoOfPersons)){?>
                                            <div class="form-group col-sm-3">
                                                <label for="WorkersIds" class="form-label">Select Workers <span
                                                        style="color:red">*</span>
                                                    <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                                </label>
                                                <select class="drpSelect form-control select2" name="WorkersIds[]" id="WorkersIds" multiple required>
                                                    <option value="" disabled> Select</option>
                                                    
                                                </select>
                                            </div>
                                        <?php }?>                                      

                                    </div>


                                    <div class="text-center mt-4">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                       <!-- <a href="rm-arrival-list" class="btn btn-danger">Close</a>-->

                                        <a href="javascript:void(0)" class="newItem btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New
                                        </a>
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                        
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="divDataList"> </div>
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
     var billID = "<?php  echo isset($_GET["BillID"]) && !empty($_GET["BillID"]) ? $_GET["BillID"] : "" ?>";
    var table;

    $(document).on('keydown', 'input[pattern]', function (e) {
        var input = $(this);
        var oldVal = input.val();
        var regex = new RegExp(input.attr('pattern'), 'g');
        
        setTimeout(function () {
            var newVal = input.val();
            if (!regex.test(newVal)) {
                input.val(oldVal);
            }
        }, 1);
    });

    function fnChangeSupplier(SuppID, contactorID) {

        var ContractorList = '<?php echo json_encode($resContractor); ?>';
        var conList = JSON.parse(ContractorList);
        
        var selectedList = [];

        $.each(conList, function(index, obj) {                
            if (obj.Type == SuppID) {                    
                selectedList.push(obj);
            }                 
        });

        var list = '<option value="">Select</option>';
        $.each(selectedList, function(index, item) {                
            list += '<option value="'+item.ContractorID+'">'+item.ContrctrName+'</option>';
        });

        $("#drpContractor").html(list);

        if(contactorID != "") {
            $("#drpContractor").val(contactorID).trigger('change');
        }

        $("#drpBatch").html('<option value="">Select</option>');
    }

    function fnChangeContractor(contID, batchID) {
        
        var SubContractorList = '<?php echo json_encode($resSubContractor); ?>';
        var SubConList = JSON.parse(SubContractorList);
        
        var selectedSubConList = [];

        $.each(SubConList, function(index, obj) {                
            if (obj.ContactorID == contID) {                    
                selectedSubConList.push(obj);
            }                 
        });

        var listSubCon = '<option value="">Select</option>';
        $.each(selectedSubConList, function(index, item) {                
            listSubCon += '<option value="'+item.SubContactorID+'">'+item.SubContractorName+'</option>';
        });

        $("#drpBatch").html(listSubCon);

        if(batchID != "") {
            $("#drpBatch").val(batchID).trigger('change');
        }
    }


    
    $(document).on('change', '#drpSupplier', function () {
        var sID = $("#drpSupplier").val();
        fnChangeSupplier(sID, "");                    
    }); 

    $(document).on('change', '#drpContractor', function () {
        var cID = $("#drpContractor").val();
        fnChangeContractor(cID, "");
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
                title: 'Hl-Va-Bill-<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    $(document).ready(function () {
        // fnShowDataTable();

        if(billID) {
            var frmData = {
                methodType: "get",
                BillID: billID,
            }
            $.ajax({
                url: 'Ajax/ajax-hl-va-bill.php',
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

                    if (billID) {
                        bindFormData(resJson);
                    }
                    return false;
                }
            });
        }
    });


    $(document).on("change", "#txtArrivalDate", function() {    
        $.ajax({
            url: 'Ajax/ajax-hl-va-bill.php',
            type: 'POST',
            data: {
                ArrivalDate: $(this).val(),
            },
            success: function(data) {
                console.log(data.Data);

                var list = '<option value="">Select</option><option value="0"> Melting</option>';
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

        // $("#txtArrivalDate").val("");
        // $("#drpLotCode").val(resJson.ArrivalID).trigger('change');
        $("#drpProductionPlant").val(resJson.PlantID).trigger('change');
        $("#drpCenter").val(resJson.CenterID).trigger('change');
        $("#txtTransactionDate").val(resJson.TransactionDate);
        $("#drpType").val(resJson.Type).trigger('change');
        
        $("#drpSupplier").val(resJson.SupplierTypeID).trigger('change'); // Supplier
        fnChangeSupplier(resJson.SupplierTypeID, resJson.ContractorID);  // Data Bind the Contractor
        fnChangeContractor(resJson.ContractorID, resJson.BatchID); // Data Bind the Batch

        // console.log(resJson.SupplierTypeID);
        // $("#drpContractor").val(resJson.ContractorID).trigger('change');
        // $("#drpBatch").val(resJson.BatchID).trigger('change');

        $("#txtPerson").val(resJson.Persons);
        $("#drpShift").val(resJson.ShiftID).trigger('change');
        $("#txtAdjustments").val(resJson.Adjustments);
        $("#txtExtras").val(resJson.Extras);
        $("#txtRemarks").val(resJson.Remarks);
        
        $("#txtArrivalDate, #txtHLCount, #txtHLWt").val("");
        $("#drpLotCode, #drpVariety").val("").trigger('change');
        

    }

    function removeQueryParameters(url) {
        const urlObject = new URL(url);
        urlObject.search = '';
        return urlObject.toString();
    }


    $(document).on("click", ".newItem", function () {

        $("#txtTransactionDate, #txtPerson, #txtAdjustments, #txtExtras, #txtRemarks, #txtHLCount, #txtHLWt").val("");
        $("#drpLotCode, #drpProductionPlant, #drpCenter, #drpType, #drpSupplier, #drpContractor, #drpBatch, #drpShift, #drpVariety").val("").trigger('change');
        
        billID = "";

        $("#drpType").prop("disabled", false);

        table.clear().draw();
        $("#divDataList").remove();
        $("#hdnNewArrival").val("NEW");

        var url = window.location.href;
        var newUrl = removeQueryParameters(url);
        window.history.replaceState(null, null, newUrl);

        return false;
    });

    $(document).on("change", "#drpType", function() {
        var drptype = $(this).val();
        var RstrictNoOfPersons = "<?php echo !empty($RstrictNoOfPersons)?$RstrictNoOfPersons:0;?>";
        if(drptype == "1" ) {
            $("#drpVariety").val("").trigger('change');
            $( "#drpVariety" ).prop( "disabled", true );
            if(RstrictNoOfPersons == 0){
                $('#txtHLCount').val("").prop('readonly', true);
            }
            $("#drpVariety, #txtHLCount").removeClass("error");
        } else {            
            $("#drpVariety").val("").trigger('change');
            $( "#drpVariety" ).prop( "disabled", false );
            if(RstrictNoOfPersons == 0){
                $('#txtHLCount').val("").prop('readonly', false);
            }
        }
    });

    // $(document).on("change", "#drpLotCode", function() {
    //     $("#txtHLCount, #txtHLWt").val("");
    //     $("#drpVariety").val("").trigger('change');
       
    //     var aid = $(this).val();
    //     // console.log(aid);
    //     if(aid != "Select"){
    //         var frmData = {
    //             methodType: "get",
    //             ArrivalID: aid,
    //         }
    //         $.ajax({
    //             url: 'Ajax/ajax-hl-va-bill.php',
    //             type: 'POST',
    //             data: frmData,
    //             success: function(data) {
    //                 var data = JSON.parse(data);
    //                 console.log(data);
    //                 var htmlData = data.html;
    //                 var resJson = data.json;
    //                 console.log(resJson);

    //                 $("#divDataList").html(htmlData);
    //                 fnShowDataTable();

    //                 // if (arrivalID) {
    //                 //     bindFormData(resJson);
    //                 // }
    //                 return false;
    //             }
    //         });
    //     } 
    // });

    // $(document).on("change", "#drpType", function() {
    //     $("#txtHLWt").val("");
    // }); 

    $.validator.setDefaults({
        ignore: ':hidden, [readonly=readonly]'
    });
    
    $("#frmHlVaBill").validate({
        onkeyup: false,
        onfocusout: false,
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: {
            // drpLotCode: {
            //     required: true,
            //     selectcheck: true
            // },
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
                selectcheck: true
            },
            drpType: {
                required: true,
                noSpace: true
            },
            drpSupplier: {
                required: true,
                noSpace: true
            },
            drpContractor: {
                required: true,
                selectcheck: true
            },
            drpBatch: {
                required: true,
                selectcheck: true
            },
            txtPerson: {
                required: true,
                selectcheck: true
            },
            drpShift: {
                required: true,
                selectcheck: true
            },
            txtAdjustments: {
                required: true,
                noSpace: true
            },
            txtExtras: {
                required: true,
                noSpace: true
            },
            // txtRemarks: {
            //     required: true,
            //     selectcheck: true
            // },
            drpVariety: {
                required: {
                    depends: function(element) {
                        return $("#drpLotCode").val().length > 0;
                    }
                }
            },
            txtHLCount: {
                required: {
                    depends: function(element) {                        
                        isReadonly = $(this).attr("readonly");
                        console.log(isReadonly);
                        if(isReadonly == "readonly") {
                            return false;
                        }else {
                            return $("#drpLotCode").val().length > 0;
                        }
                    }
                }
            },
            txtHLWt: {
                required: {
                    depends: function(element) {                        
                        return $("#drpLotCode").val().length > 0;
                    }
                }
            },
        },

        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {
            var WorkersIdsObj = $('#WorkersIds').val()
            if (jQuery.inArray('All', WorkersIdsObj) !== -1) {
            //if($('#WorkersIds').find("option:selected").text() == 'All'){
                var WorkersIdsObj = [];
                $("#WorkersIds option").each(function() {
                    if($(this).val() != 'All'){
                        WorkersIdsObj.push($(this).val())
                    }
                });
                var workerIds = WorkersIdsObj.join(',')
            }else{
                var WorkersIdsObjNew = [];
                $.each(WorkersIdsObj, function(index, value) {
                    if(value != 'All'){
                        WorkersIdsObjNew.push(value)
                    }
                })
                var workerIds = WorkersIdsObjNew.join(',')
            }
            $.ajax({
                url: 'Ajax/ajax-hl-va-bill.php',
                type: 'POST',
                data: {
                    methodType: "post",
                    ArrivalDate: $("#txtArrivalDate").val(),
                    ArrivalID: $("#drpLotCode").val(),                                    
                    drpProductionPlant: $("#drpProductionPlant").val(),
                    drpCenter: $("#drpCenter").val(),
                    txtTransactionDate: $("#txtTransactionDate").val(),
                    drpType: $("#drpType").val(),
                    drpSupplier: $("#drpSupplier").val(),
                    drpContractor: $("#drpContractor").val(),
                    drpBatch: $("#drpBatch").val(),
                    txtPerson: $("#txtPerson").val(),
                    drpShift: $("#drpShift").val(),
                    txtAdjustments: $("#txtAdjustments").val(),
                    txtExtras: $("#txtExtras").val(),
                    txtRemarks: $("#txtRemarks").val(),
                    drpVariety: $("#drpVariety").val(),
                    txtHLCount: $("#txtHLCount").val(),
                    txtHLWt: $("#txtHLWt").val(),
                    BillID:billID,//uma
                    WorkersIds: workerIds,
                    RstrictNoOfPersons: "<?php echo $RstrictNoOfPersons;?>",
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

                    if (billID == "") {
                        if($("#hdnNewArrival").val() == "NEW") {
                            var newUrl = "/v3/PreProcessing/hl-va-bills"+ "?BillID=" + resJson.BillID;
                        }else {
                            var newUrl = window.location.href + "?BillID=" + resJson.BillID;
                        }
                        // window.history.pushState({}, '', currentUrl);
                        window.location.href = newUrl;
                    }

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    $("#txtArrivalDate, #txtHLCount, #txtHLWt").val("");
                    $("#drpLotCode, #drpVariety,#WorkersIds").val("").trigger('change');
                    
                    return false;
                }
            });
            
            return false;
            
        }
    });

$(document).on("click", ".deleteBrkUp", function() {
    var brkupid = $(this).attr("data-brkupid");
    var billid = $(this).attr("data-billid");    

    var frmData = {
        methodType: "delete",
        bID: billid,
        breakUpID: brkupid
    }
    if (confirm("Are you sure you want to delete this item ?") == true) {
        $.ajax({
            url: 'Ajax/ajax-hl-va-bill.php',
            type: 'POST',
            data: frmData,
            success: function(data) {
                var data = JSON.parse(data);
                console.log(data);
                var htmlData = data.html;
                var resJson = data.json;

                $("#divDataList").html(htmlData);
                fnShowDataTable();

                if (billID) {
                    bindFormData(resJson);
                }
                return false;
            }
        });
    }

});
<?php 
if(!empty($RstrictNoOfPersons)){?>
    $('#drpBatch').on('change',function(){
        var value = $(this).val();
        if(value){
            var frmData = {
                Type : $("#drpType").val(),// type is required parameter for API, Actually not required
                methodType: "GetWorkersBySubContractorID",
                batchId: value,
                BillID:"<?php echo $BillID;?>"
            }
            $.ajax({
                url: 'Ajax/ajax-hl-va-bill.php',
                type: 'POST',
                data: frmData,
                dataType: "json",
                success: function(data) {
                    if(data.Error == null){
                        var noOfPersons = data.Data.NoOfPersonsPerBatch;
                        $('#txtPerson').val(noOfPersons)
                        $('#WorkersIds').select2('destroy').html('<option value="" disabled> Select</option>').select2();
                        if(data.Data.workers.length != 0){
                            var list = "<option value='All'>All</option>";
                            var wIds = [];
                            $.each(data.Data.workers, function(index, value) {
                            // wIds.push(value.WorkerID);
                                list += "<option value='" + value.WorkerID + "'>" + value.WorkerName + "</option>";
                            });
                            //console.log(wIds)
                            $('#WorkersIds').select2('destroy').html(list).select2();
                        }else{
                            alert('Workers are not found. Please add workers first, for this selected Batch.');
                        }
                    }
                }
            });
        }
    })
    $('#drpSupplier,#drpContractor').on('change',function(){
        $('#txtPerson').val('');
        $('#WorkersIds').html('<option value="" disabled> Select</option>')
    })
<?php }?>
</script>

</body>

</html>