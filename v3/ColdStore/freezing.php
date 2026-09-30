<?php 

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

// if(isset($_GET['ProductionDataID'])){

//     $api = API_DAILY_PRODUCTION_DETAILS;    
//     $bodyContent = array("ProductionID" => $_GET["ProductionDataID"]);
//     $resProdData = CallAPI($api, "GET", "", $bodyContent);
// }
// var_dump($resProdData );


$api = API_STORE_RACK_DETAILS;

$bodyContent = array(
    "BlockID" => ""
);

$resRackDetails = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resRackDetails, JSON_PRETTY_PRINT);
// exit;
$resStoresData = $resRackDetails->StoresData;
$resBlockData = $resRackDetails->BlockData;

// $resProductGroup = $resDropdownsAPI->Data->ProductGroup;
// $resStockBelongsTo = $resDropdownsAPI->Data->StockBelongsTo;

// $resColdStores = $resDropdownsAPI->Data->ColdStores; -- popup

// $resPlant = $resDropdownsAPI->Data->Plant;
// $resShifts = $resDropdownsAPI->Data->Shifts;
// $resPoNumber = $resDropdownsAPI->Data->PoNumber;
// $resVariety = $resDropdownsAPI->Data->Variety;
// $resPackingType = $resDropdownsAPI->Data->PackingType;
// $resPackingStyle = $resDropdownsAPI->Data->PackingStyle;
// $resGrade = $resDropdownsAPI->Data->Grade;
// $resFreezingType = $resDropdownsAPI->Data->FreezingType;
// $resSoaking = $resDropdownsAPI->Data->Soaking;
// $resProductCategory = $resDropdownsAPI->Data->ProductCategory;
// $resAbstatus = $resDropdownsAPI->Data->Abstatus;


// $resFloors = $resDropdownsAPI->Data->Floors;
// // $resLineNumber = $resDropdownsAPI->Data->LineNumber;
// // $error = $resDropdownsAPI->Error;





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
                                <form id="frmInward">
                                    <div class="row div_inward">
									
									
									        <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtProductionDate" class="form-label">Production Date <span style="color:red">*</span></label>
                                                <input type="date" class="form-control" id="txtProductionDate" name="txtProductionDate" value="<?php echo date("Y-m-d") ?>" />                                                
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="drpBatchCodes" class="form-label">Batch Codes</label>
                                                <select class="drpSelect" name="drpBatchCodes" id="drpBatchCodes">
                                                    <option value=""> Select</option>
                                                    <!-- <?php // foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php // echo $item->ShortCode; ?>">
                                                        <?php // echo $item->TreatmentName ?></option>
                                                    <?php // } ?> -->
                                                </select>
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtTransactionDate" class="form-label">Transaction Date <span
                                                        style="color:red">*</span></label>
                                                <input type="date" class="form-control" id="txtTransactionDate" name="txtTransactionDate" value="<?php echo date("Y-m-d") ?>" />                                                
                                            </div>
											
											 <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtProductionPlant" class="form-label">Production Plant <span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtProductionPlant" name="txtProductionPlant" value="<?php echo $resProdData -> ProductionPlantName ?>" />                                                
                                            </div>
											
											 <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtGroup" class="form-label">Group<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtGroup" name="txtGroup" value="" />                                                
                                            </div>
											
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtStockBelongs" class="form-label">Stock Belongs to<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtStockBelongs" name="txtStockBelongs" value="" />                                                
                                            </div>
											
											<!-- <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtStore" class="form-label">Store<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtStore" name="txtStore" value="" />                                                
                                            </div> -->
											
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtShift" class="form-label">Shift<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtShift" name="txtShift" value="" />                                                
                                            </div>
											
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtPoNumber" class="form-label">Po Number<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtPoNumber" name="txtPoNumber" value="" />                                                
                                            </div>
											
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtVariety" class="form-label">Variety<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtVariety" name="txtVariety" value="" />                                                
                                            </div>
											
											
										   	
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtPackingStyle" class="form-label">Packing Style<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtPackingStyle" name="txtPackingStyle" value="" />                                                
                                            </div>
											
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtGrade" class="form-label">Grade<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtGrade" name="txtGrade" value="" />                                                
                                            </div>
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtPackingType" class="form-label">Packing Type<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtPackingType" name="txtPackingType" value="" />                                                
                                            </div>
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtFreezingType" class="form-label">Freezing Type<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtFreezingType" name="txtFreezingType" value="" />                                                
                                            </div>
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtSoaking" class="form-label">Soaking<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtSoaking" name="txtSoaking" value="" />                                                
                                            </div>
											
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtProductCategory" class="form-label">Product Category<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtProductCategory" name="txtProductCategory" value="" />                                                
                                            </div>
											
											<div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtAbStatus" class="form-label">AbStatus<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtAbStatus" name="txtAbStatus" value="" />                                                
                                            </div>
											
										

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtRemarks" class="form-label">Remarks</label>
                                                <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                    id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtTotalWeight" class="form-label">Total Weight</label>
                                                <input name="txtTotalWeight" class="form-control" rows="1" cols="50" id="txtTotalWeight" placeholder="Enter Total Weight" readonly="">
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtBalanceWeight" class="form-label">Balance Weight</label>
                                                <input name="txtBalanceWeight" class="form-control" rows="1" cols="50" id="txtBalanceWeight" placeholder="Enter Balance Weight" readonly="">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div>
                                                <hr />
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtArrivalDate" class="form-label">Arrival Date <span style="color:red">*</span></label>
                                                <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder="" />
                                            </div>

                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpLotCode" class="form-label">Arrival/Lot Code<span style="color:red">*</span></label>
                                                <select class="drpSelect" name="drpLotCode" id="drpLotCode">
                                                    <option value=""> Select</option>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtHLCount" class="form-label">HL Count <span style="color:red">*</span></label>
                                                <!-- <input type="number" class="form-control" id="txtHLCount" pattern="^\d*(\.\d{0,2})?$" name="txtHLCount" /> -->
                                                <select class="drpSelect" name="drpHLCount" id="drpHLCount">
                                                    <option value="" data-hlbreakup=""> Select</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtMcs" class="form-label">Mcs</label>
                                                <input type="number" class="form-control" id="txtMcs" name="txtMcs"  />
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtLooseBags" class="form-label">Loose Bags</label>
                                                <input type="number" class="form-control" id="txtLooseBags" name="txtLooseBags"  />
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtTubNo" class="form-label">Tub No</label>
                                                <input type="number" class="form-control" id="txtTubNo" name="txtTubNo"  />
                                            </div>
                                        </div>

                                    
                                    
                                    
                                    <div class="text-center">
                                        <input type="hidden" id="hdnStockID" name="hdnStockID" value="" />
                                        
                                        <a href="daily-production-list" class="btn btn-danger">Close</a>
                                        <button class="btn btn-primary" name="submit" id="submit" type="submit">Save</button>

                                        <!-- <span style="float: right;">
                                            <a href="javascript:void(0)" id="aNewPalette" class="newItem btn btn-success waves-effect waves-light" >
                                                <i class="fa fa-plus"></i> New Palette
                                            </a>
                                            <button type="button" id="aNewPlacePalette" class="newItem btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> Place Palette
                                            </button>
                                            <a href="javascript:void(0)" id="aViewStore" class="newItem btn btn-success waves-effect waves-light">
                                                View Store
                                            </a>
                                        </span> -->
                                        
                                    </div>
                                </form>
                            </div>
                        </div>


                        <div id="divDataList"></div>

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
    // var barCodeID = "<?php // echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? $_GET["BarCodeID"] : "" ?>";
    
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
            title: 'InWard List',
            footer: true,
            exportOptions: {
                columns: ':visible'
            }
        }],
    });
}

$(document).on("change", "#txtArrivalDate", function() {
    var type = "ValueAddition";
    $.ajax({
        url: 'Ajax/AjaxFreezing.php',
        type: 'POST',
        data: {
            methodType: "getArrivalCodes",
            ArrivalDate: $(this).val(),
            Type: type,
        },
        success: function(data) {
            console.log(data.Data);

            var list = '<option value="">Select</option>';
            $.each(data.Data, function(index, value) {
                list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + " - " + value.ArrivalCount + " - " + value.ArrivalWeight + "</option>";
            });
            $('#drpLotCode').html(list);
            $('#drpLotCode').trigger("change");
            return false;
        }
    });
});

$(document).on("change", "#drpLotCode", function() {
        // $("#txtTransactionDate, #txtIceConsumption, #txtRemarks, #txtHLCount, #txtHLWt, #txtReceivedHLWt, #txtReturnWt, #txtWeightAfterVA, #txtCountAfterVA").val("");
        // $("#drpProductionPlant, #drpCenter, #drpShift, #drpVariety").val("").trigger('change');    

        var aid = $(this).val();
        
        if (aid != "") {
            
            $.ajax({
                url: 'Ajax/AjaxFreezing.php',
                type: 'POST',
                data: {
                    methodType: "getHLCount",
                    ArrivalID: aid,
                },
                success: function(data) {
                    var data = JSON.parse(data);
                    console.log(data);
                    var hlCount = data.hlCounts;

                    var listHlCount = "<option value='' data-hlbreakup=''>Select</option>";
                    $.each(hlCount, function(index, val) {                        
                        listHlCount += '<option value="'+val.HLCount+'" data-hlbreakup="'+val.HLBreakupID+'">'+val.Title+'</option>';                        
                    });

                    $("#drpHLCount").html("").html(listHlCount);

                }
            });
        }

        return false;
    });

// function getVarieties() {
//     $.ajax({
//         type: 'POST',
//         url: 'Ajax/AjaxInward.php',
//         data: { 
//             'methodType': 'poNumber',
//             'pOID': $("#drpPoNumber").val()
//         },
//         success: function (data) {                
//             var arryData = data.DependentDropdownsData.VarietyData;

//             var list = '<option value=""> Select</option>';                
//             $.each(arryData, function(key, item) {
//                 console.log(item.VarietyID + " -- " + item.VarietyName);
//                 list += '<option value="'+item.VarietyID+'" data-po-grade-id="'+item.PoGradeID+'">' +item.VarietyName+ '</option>';                    
//             });
            
//             $('#drpVariety').html("").html(list);
//             console.log(data.DependentDropdownsData.PackingStyle);
//             if(data.DependentDropdownsData.PackingStyle == "Enable") {
//                 console.log("hi");
//                 $("#drpPackingStyle").prop("disabled", false);
//             } else {
//                 console.log("else");
//                 $("#drpPackingStyle").prop("disabled", true);
//             }
//         }
//     });
// } 

function fnGetProdData(id) {
    console.log(id);
    var barID = "<?php echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? $_GET["BarCodeID"] : "" ?>";
    if(id != "" ) {
        $.ajax({
            url: 'Ajax/AjaxFreezing.php',
            type: 'POST',
            data: {
                methodType: "getProdData",            
                prodDataId: id,
                barCodeID: barID,
                prodDate: $("#txtProductionDate").val()
            },
            success: function(data) {
                // alert("hiii");
                console.log(data);
                var arylist = data.DailyProductionList;
                data = data.DailyProductionData;

                $("#txtProductionDate").val(data.ProductionDate);                

                var list = '<option value=""> Select</option>';
                $.each(arylist, function(key, item) {
                    list += "<option value='"+item.ProductionID+"' >"+item.OurSupc+" - "+item.BatchCode+" - "+item.ProductDescription+"</option>";
                });
                $('#drpBatchCodes').html("").html(list).val(data.ProductionID);

                $("#txtGroup").val(data.Group);
                $("#txtStockBelongs").val(data.StockHolderName);
                $("#txtStore").val(data.StoreName);
                $("#txtProductionPlant").val(data.ProductionPlantName);
                $("#txtShift").val(data.Shift);
                $("#txtProductionCode").val(data.ProductionCode);
                $("#txtBatchCode").val(data.BatchCode);
                $("#txtVariety").val(data.Variety);
                            
                $("#txtPackingType").val(data.PackingType);
                $("#txtPackingStyle").val(data.PackingStyle);
                $("#txtFreezingType").val(data.FreezingType);
                $("#txtSoaking").val(data.Soaking);
                $("#txtGrade").val(data.Grade);
                $("#txtTotalWeight").val(data.Kgs);
                $("#txtBalanceWeight").val(data.BalanceKgs);
                
                // $("#txtSalt").val(data.);
                $("#txtProductCategory").val(data.ProductCategory);
                $("#txtAbStatus").val(data.AbStatus);
                $("#txtRemarks").val(data.Remarks);
                // $("#txtMcs").val(data.Mcs);
                // $("#txtLooseBags").val(data.LooseBags);

                // $("#drpPackingStyle").val(data.PackingStyleID).trigger("change");
                // $("#drpGrade").prop("disabled", false).val(data.PoGradeId).trigger("change").prop("disabled", true);
                // $("#drpFreezingType").val(data.FreezingType).trigger("change");
                // $("#txtGlaze").val(data.Galze);
                // $("#drpSoaking").val(data.Soaking).trigger("change");

                // getVarieties();

                $("#txtPoNumber").val(data.PoNo);   
                $('#drpPoNumber').select2();    

                // $.ajax({
                //     type: 'POST',
                //     url: 'Ajax/AjaxInward.php',
                //     data: { 
                //         'methodType': 'poNumber',
                //         'pOID': data.PoID
                //     },
                //     success: function (res) {                
                //         var arryData = res.DependentDropdownsData.VarietyData;

                //         var list = '<option value=""> Select</option>';                
                //         $.each(arryData, function(key, item) {
                //             list += '<option value="'+item.VarietyID+'" data-po-grade-id="'+item.PoGradeID+'">' +item.VarietyName+ '</option>';                    
                //         });
                        
                //         $('#txtVariety').html("").html(list);
                //         // if(res.DependentDropdownsData.PackingStyle == "Enable") {                        
                //         //     $("#drpPackingStyle").prop("disabled", false);
                //         // } else {
                //         //     $("#drpPackingStyle").prop("disabled", true);
                //         // }
                //         $("#txtPackingStyle").prop("disabled", true);

                //         $("#txtVariety").val(data.VarietyID);
                //         $('#drpVariety').select2();
                        
                //     }
                // });

              //  $.ajax({
                //     type: 'POST',
                //     url: 'Ajax/AjaxInward.php',
                //     data: { 
                //         'methodType': 'varietyChange',
                //         'pOID': data.PoID,
                //         'varietyID': data.VarietyID,
                //         'poGradeID': data.PoGradeId
                //     },
                //     complete: function () {
                //         $('#submit').prop("disabled", false);
                        
                //     },
                //     success: function (data) {
                //         var obj = data.DependentDropdownsData.GradeData;
                //         $("#drpGrade").val(obj.Grade).trigger("change").prop("disabled", true);
                //         $("#drpFreezingType").val(obj.FreezingTypeID).trigger("change").prop("disabled", true);
                //         $("#txtGlaze").val(obj.GlazePerc).attr("readonly", true);
                //         $("#drpSoaking").val(obj.SoakingType).trigger("change").prop("disabled", true);
                //         // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

                //         // if(obj.PackingStyle == "Enable") {
                //         //     $("#drpPackingStyle").prop("disabled", false).val(obj.PackingStyleID).trigger("change");
                //         // } else {
                //         //     $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");
                //         // }
                //             $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");

                        
                //     }
                // });     
                
                return false;
            }
        });

        $(".div_inward input:not(#txtProductionDate, #txtTransactionDate)").attr('readonly', true);
        // $(".div_inward input, .div_inward select").attr('readonly', true);
        $(".div_inward select:not(#drpBatchCodes)").prop("disabled", true);
    } else {
        console.log("Else");
    }
}

$(document).ready(function() {
    // fnShowDataTable();
   // getVarieties();
    
   // alert('hi');

     var barID = "<?php echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? $_GET["BarCodeID"] : "" ?>";

    if(barID != ""){

        $.ajax({
            url: 'Ajax/AjaxFreezing.php',
            type: 'POST',
            data: {
                methodType: "getList",            
                barCodeID: barID,
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();

                $("#txtMcs, #txtLooseBags, #txtTubNo").val("");

                var bId = $("#hdnBarCodeID").val();
                
                if(bId == null) {
                    $("#aNewPlacePalette").prop("disabled", true);
                } else {
                    $("#aNewPlacePalette").prop("disabled", false);        
                }
                
                return false;
            }
        });

    } else {
        $("#aNewPlacePalette").prop("disabled", true);
    }    

    var prodID = "<?php  echo isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"]) ? $_GET["ProductionDataID"] : "" ?>";
	
    if(prodID == "") {
        fnGetProdDataByDate();
    } else {
        fnGetProdData(prodID);
    }

    // $("#aNewPlacePalette").prop("disabled", true);

});

function fnGetProdDataByDate() {

    if($("#txtProductionDate").val() != "" ) {
        $.ajax({
            url: 'Ajax/AjaxFreezing.php',
            type: 'POST',
            data: {
                methodType: "getProdData",            
                prodDataId: "",
                prodDate: $("#txtProductionDate").val()
            },
            success: function(data) {
                // alert("hiii");
                console.log(data);
                var arylist = data.DailyProductionList;
                // data = data.DailyProductionData;

                // $("#txtProductionDate").val(data.ProductionDate);                

                var list = '<option value=""> Select</option>';
                $.each(arylist, function(key, item) {
                    list += "<option value='"+item.ProductionID+"' >"+item.OurSupc+" - "+item.BatchCode+" - "+item.ProductDescription+"</option>";
                });
                $('#drpBatchCodes').html("").html(list).val(data.ProductionID);
/*
                $("#txtGroup").val(data.Group);
                $("#txtStockBelongs").val(data.StockHolderName);
                $("#txtStore").val(data.StoreName);
                $("#txtProductionPlant").val(data.ProductionPlantName);
                $("#txtShift").val(data.Shift);
                $("#txtProductionCode").val(data.ProductionCode);
                $("#txtBatchCode").val(data.BatchCode);
                $("#txtVariety").val(data.Variety);
                            
                $("#txtPackingType").val(data.PackingType);
                $("#txtPackingStyle").val(data.PackingStyle);
                $("#txtFreezingType").val(data.FreezingType);
                $("#txtSoaking").val(data.Soaking);
                $("#txtGrade").val(data.Grade);
                $("#txtTotalWeight").val(data.Kgs);
                $("#txtBalanceWeight").val(data.BalanceKgs);
                
                // $("#txtSalt").val(data.);
                $("#txtProductCategory").val(data.ProductCategory);
                $("#txtAbStatus").val(data.AbStatus);
                $("#txtRemarks").val(data.Remarks);
                // $("#txtMcs").val(data.Mcs);
                // $("#txtLooseBags").val(data.LooseBags);

                // $("#drpPackingStyle").val(data.PackingStyleID).trigger("change");
                // $("#drpGrade").prop("disabled", false).val(data.PoGradeId).trigger("change").prop("disabled", true);
                // $("#drpFreezingType").val(data.FreezingType).trigger("change");
                // $("#txtGlaze").val(data.Galze);
                // $("#drpSoaking").val(data.Soaking).trigger("change");

                $("#txtPoNumber").val(data.PoNo);   
                $('#drpPoNumber').select2();    
*/
                return false;
            }
        });

        $(".div_inward input:not(#txtProductionDate, #txtTransactionDate)").attr('readonly', true);
        // $(".div_inward input, .div_inward select").attr('readonly', true);
        $(".div_inward select:not(#drpBatchCodes)").prop("disabled", true);
    } else {
        console.log("Else");
    }
}

$("#txtProductionDate").on("change", function() {
    
    fnGetProdDataByDate();
})


$("#drpBatchCodes").on("change", function() {    
    var prodID = $(this).val();
    fnGetProdData(prodID);
});

// $("#drpPoNumber").on("change", function() {    
//     getVarieties();
// });

// function getGrades() {
//     poGraidID = $.trim($("#drpVariety").find('option:selected').attr("data-po-grade-id"));

//         $.ajax({
//             type: 'POST',
//             url: 'Ajax/AjaxInward.php',
//             data: { 
//                 'methodType': 'varietyChange',
//                 'pOID': $("#drpPoNumber").val(),
//                 'varietyID': $("#drpVariety").val(),
//                 'poGradeID': poGraidID
//             },
//             success: function (data) {
//                 var obj = data.DependentDropdownsData.GradeData;
//                 $("#drpGrade").val(obj.Grade).trigger("change").prop("disabled", true);
//                 $("#drpFreezingType").val(obj.FreezingTypeID).trigger("change").prop("disabled", true);
//                 $("#txtGlaze").val(obj.GlazePerc).attr("readonly", true);
//                 $("#drpSoaking").val(obj.SoakingType).trigger("change").prop("disabled", true);
//                 // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

//                 if(obj.PackingStyle == "Enable") {
//                     $("#drpPackingStyle").prop("disabled", false).val(obj.PackingStyleID).trigger("change");
//                 } else {
//                     $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");
//                 }
//                 // obj.PackingStyle
//             }
//         });
// }

// $("#drpVariety").on("change", function() {
//     // if($(this).val() != "") {
//         getGrades();

//     // }
//     // $("#drpVariety").val("").trigger("change");
// });

// $("#drpPackingType").on("change", function() {
//     if($(this).val() == "DUMMY") {
//         $("#drpPackingStyle").val("").trigger("change");
//     } else {
//         getGrades();
//     }
// });


// $.validator.setDefaults({
//     ignore: ':hidden, [readonly=readonly], :disabled'
// });

$("#frmInward").validate({
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
    ignore: ":disabled",
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass(errorClass).addClass('valid');
        $(":disabled").removeClass(errorClass).addClass('valid');
    },
    rules: {
        // txtProductionDate: {
        //     required: true,
        //     noSpace: true
        // },
        // txtTransactionDate: {
        //     required: true,
        //     noSpace: true
        // },
        drpBatchCodes: {
            required: true
        },
        drpGroup: {
            required: true
        },
        drpStockBelongs: {
            required: true
        },
        drpStore: {
            required: true
        },
        drpProductionPlant: {
            required: true
        },
        drpShift: {
            required: true
        },
        txtProductionCode: {
            required: true,
            noSpace: true
        },
        txtBatchCode: {
            required: true,
            noSpace: true
        },
        // drpPoNumber: {
        //     required: true
        // },
        drpVariety: {
            required: true
        },
        drpPackingType: {
            required: true
        },
        drpPackingStyle: {
            required: true
        },
        drpGrade: {
            required : true
        },
        txtFreezingType : {
            required : true
        },
        // txtGlaze : {
        //     required: true,
        //     noSpace: true
        // },
        drpSoaking : {
            required: true
        },
        drpProductCategory : {
            required: true
        },
        drpAbStatus : {
            required: true
        },
        // txtRemarks : {
        //     required: true,
        //     noSpace: true
        // },
        txtArrivalDate : {
            required: true,
            noSpace: true
        },
        drpLotCode : {
            required: true
        },
        drpHLCount : {
            required: true
        },
        txtMcs : {
            required: true,
            noSpace: true
        },
        txtLooseBags : {
            required: true,
            noSpace: true
        }       
    },
    errorPlacement: function() {
        return false; // suppresses error message text
    },
    submitHandler: function() {

        // console.log($("#hdnBarCodeID").val());
        var poGId = $.trim($("#drpVariety").find('option:selected').attr("data-po-grade-id"));
        var prodID = "<?php  echo isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"]) ? $_GET["ProductionDataID"] : "" ?>";
        var frmData = {
           
            methodType: "create",
            stockID: $.trim($("#hdnStockID").val()),
            barCodeID: $.trim($("#hdnBarCodeID").val()),
            txtProductionDate : $.trim($("#txtProductionDate").val()),
            txtTransactionDate : $.trim($("#txtTransactionDate").val()),
            ProductionID : $.trim($("#drpBatchCodes").val()),
            /*
            drpGroup : $.trim($("#drpGroup").val()),
            drpStockBelongs : $.trim($("#drpStockBelongs").val()),
            drpStore : $.trim($("#drpStore").val()),
            drpProductionPlant : $.trim($("#drpProductionPlant").val()),
            drpShift : $.trim($("#drpShift").val()),
            txtProductionCode : $.trim($("#txtProductionCode").val()),
            txtBatchCode : $.trim($("#txtBatchCode").val()),
            drpPoNumber : $.trim($("#drpPoNumber").val()),
            drpVariety : $.trim($("#drpVariety").val()),
            txtPackingType : $.trim($("#txtPackingType").val()),
            txtPackingStyle : $.trim($("#txtPackingStyle").val()),
            drpGrade : $.trim($("#drpGrade").val()),
            txtFreezingType : $.trim($("#txtFreezingType").val()),
            txtGlaze : $.trim($("#txtGlaze").val()),
            drpSoaking : $.trim($("#drpSoaking").val()),
            txtSalt : $.trim($("#txtSalt").val()),
            drpProductCategory : $.trim($("#drpProductCategory").val()),
            drpAbStatus : $.trim($("#drpAbStatus").val()),
            poGradeID: poGId
            */
            txtRemarks : $.trim($("#txtRemarks").val()),
            txtArrivalDate : $.trim($("#txtArrivalDate").val()),
            drpLotCode : $.trim($("#drpLotCode").val()),
            drpHLCount : $.trim($("#drpHLCount").val()),
            txtMcs : $.trim($("#txtMcs").val()),
            txtLooseBags : $.trim($("#txtLooseBags").val()),
            txtTubNo : $.trim($("#txtTubNo").val()),
            
        }

        $.ajax({
            url: 'Ajax/AjaxFreezing.php',
            type: 'POST',
            data: frmData,
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();

                $("#txtMcs, #txtLooseBags, #txtTubNo").val("");
                
                var balanceKgs = $("#hdnBalanceKgs").val();
                $("#txtBalanceWeight").val(balanceKgs);

                var bidId = $("#hdnBarCodeID").val();
    
                if(bidId == null) {
                    $("#aNewPlacePalette").prop("disabled", true);
                } else {
                    $("#aNewPlacePalette").prop("disabled", false);        
                }

                return false;
            }
        });
    }
});

$(document).on("click", ".btnEdit", function() {        
    
    var sId = $(this).attr("data-stockid");
    var barID = $("#hdnBarCodeID").val();

    $('#drpPoNumber, #drpVariety').select2('destroy');
    //$('#submit').prop("disabled", true);   

    // console.log(stockId);
    // console.log($("#hdnBarCodeID").val());

    $.ajax({
        url: 'Ajax/AjaxFreezing.php',
        type: 'POST',
        data: {
            methodType: "getSingleStock",            
            stockId: sId, // Row Freezing ID
            barCodeID: barID,  // Not manditaory
            ProductionID: $.trim($("#drpBatchCodes").val()),
        },
        success: function(data) {            
            console.log(data);
            // $("#hdnBarCodeID").val();
            $("#hdnStockID").val(data.FreezingID);
            $("#txtProductionDate").val(data.ProductionDate);
            $("#txtTransactionDate").val(data.TransactionDate);
            $("#drpGroup").val(data.Group).trigger("change");
            $("#drpStockBelongs").val(data.StockHolderID).trigger("change");
            $("#drpStore").val(data.StoreID).trigger("change");
            $("#drpProductionPlant").val(data.PlantID).trigger("change");
            $("#drpShift").val(data.ShiftID).trigger("change");
            $("#txtProductionCode").val(data.ProductionCode);
            $("#txtBatchCode").val(data.BatchCode);
                        
            $("#txtPackingType").val(data.PackingType);
            
            // $("#txtSalt").val(data.);
            $("#drpProductCategory").val(data.ProductCategory).trigger("change");
            $("#drpAbStatus").val(data.AbStatus).trigger("change");
            $("#txtRemarks").val(data.Remarks);
            $("#txtMcs").val(data.Mcs);
            $("#txtLooseBags").val(data.LooseBags);
            $("#txtTubNo").val(data.TubNo);

            // $("#drpPackingStyle").val(data.PackingStyleID).trigger("change");
            // $("#drpGrade").prop("disabled", false).val(data.PoGradeId).trigger("change").prop("disabled", true);
            // $("#drpFreezingType").val(data.FreezingType).trigger("change");
            // $("#txtGlaze").val(data.Galze);
            // $("#drpSoaking").val(data.Soaking).trigger("change");

            // getVarieties();

            $("#drpPoNumber").val(data.PoID);   
            $('#drpPoNumber').select2();    

            // $.ajax({
            //     type: 'POST',
            //     url: 'Ajax/AjaxInward.php',
            //     data: { 
            //         'methodType': 'poNumber',
            //         'pOID': data.PoID
            //     },
            //     success: function (res) {                
            //         var arryData = res.DependentDropdownsData.VarietyData;

            //         var list = '<option value=""> Select</option>';                
            //         $.each(arryData, function(key, item) {
            //             list += '<option value="'+item.VarietyID+'" data-po-grade-id="'+item.PoGradeID+'">' +item.VarietyName+ '</option>';                    
            //         });
                    
            //         $('#drpVariety').html("").html(list);
            //         if(res.DependentDropdownsData.PackingStyle == "Enable") {                        
            //             $("#drpPackingStyle").prop("disabled", false);
            //         } else {
            //             $("#drpPackingStyle").prop("disabled", true);
            //         }

            //         $("#drpVariety").val(data.VarietyID);
            //         $('#drpVariety').select2();
                    
            //     }
            // });

            // $.ajax({
            //     type: 'POST',
            //     url: 'Ajax/AjaxInward.php',
            //     data: { 
            //         'methodType': 'varietyChange',
            //         'pOID': data.PoID,
            //         'varietyID': data.VarietyID,
            //         'poGradeID': data.PoGradeId
            //     },
            //     complete: function () {
            //         $('#submit').prop("disabled", false);
                    
            //     },
            //     success: function (data) {
            //         var obj = data.DependentDropdownsData.GradeData;
            //         $("#drpGrade").val(obj.Grade).trigger("change").prop("disabled", true);
            //         $("#drpFreezingType").val(obj.FreezingTypeID).trigger("change").prop("disabled", true);
            //         $("#txtGlaze").val(obj.GlazePerc).attr("readonly", true);
            //         $("#drpSoaking").val(obj.SoakingType).trigger("change").prop("disabled", true);
            //         // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

            //         if(obj.PackingStyle == "Enable") {
            //             $("#drpPackingStyle").prop("disabled", false).val(obj.PackingStyleID).trigger("change");
            //         } else {
            //             $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");
            //         }

                    
            //     }
            // });           

            // $('#submit').prop("disabled", false);


            return false;
        }
    });
    $("html, body").animate({ scrollTop: 0 }, "slow");
});

</script>


</body>

</html>