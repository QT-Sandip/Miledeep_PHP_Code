<?php 

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

if(!isset($ArrivalID) || empty($ArrivalID)) {
    header("Location: rm-bills-list");
}


$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

// $resSpecies = $resDropdownsAPI->Data->Species;
// $resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters; // Purchase Stations / Purchase centers 
// $resPurchaseCategory = $resDropdownsAPI->Data->PurchaseCategory; 
// $resVehicles = $resDropdownsAPI->Data->Vehicles;
$resRmAgents = $resDropdownsAPI->Data->RmAgents;
// $resDrivers = $resDropdownsAPI->Data->Drivers;
// $resRmGraders = $resDropdownsAPI->Data->RmGraders;
$resRmSuppliers = $resDropdownsAPI->Data->RmSuppliers;


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>
    <style>
        .disable-link {
            pointer-events: none;
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
                                <form id="frmCreateBill">
                                    <div class="row">


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSpecies" class="form-label">Species </label>
                                            <input type="text" class="form-control" id="txtSpecies"
                                                name="txtSpecies" placeholder=" Enter Species" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPurchaseStation" class="form-label">Purchase Station </label>
                                            <input type="text" class="form-control" id="txtPurchaseStation"
                                                name="txtPurchaseStation" placeholder=" Enter Purchase Station" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProcessingStation" class="form-label">Processing Station </label>
                                            <input type="text" class="form-control" id="txtProcessingStation"
                                                name="txtProcessingStation" placeholder=" Enter Processing Station" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPurchaseType" class="form-label">Purchase Type </label>
                                            <input type="text" class="form-control" id="txtPurchaseType"
                                                name="txtPurchaseType" placeholder=" Enter Purchase Type" readonly>  
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtLotNumber" class="form-label">Bill Number </label> <!-- lot / Bill no./ Arrival No. -->
                                            <input type="text" class="form-control" id="txtLotNumber"
                                                name="txtLotNumber" placeholder=" Enter Lot Number" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtVehicleNumber" class="form-label">Vehicle Number </label>
                                            <input type="text" class="form-control" id="txtVehicleNumber"
                                                name="txtVehicleNumber" placeholder=" Enter Vehicle Number" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtDriverName" class="form-label">Driver Name </label>
                                            <input type="text" class="form-control" id="txtDriverName"
                                                name="txtDriverName" placeholder=" Enter Driver Name" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtGraderName" class="form-label">Grader Name </label>
                                            <input type="text" class="form-control" id="txtGraderName"
                                                name="txtGraderName" placeholder=" Enter Grader Name" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPurchaseDate" class="form-label">Purchase Date </label>
                                            <input type="text" class="form-control" id="txtPurchaseDate"
                                                name="txtPurchaseDate" placeholder=" Enter Purchase Date" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtAgentName" class="form-label">Agent Name </label>
                                            <select class="drpSelect" name="drpAgentName" id="drpAgentName" disabled>
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmAgents as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>" > <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                            <!-- <input type="text" class="form-control" id="txtAgentName"
                                                name="txtAgentName" placeholder=" Enter Agent Name" readonly> -->
                                        </div>

                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPurchaseCount" class="form-label">Purchase Count </label>
                                            <input type="text" class="form-control" id="txtPurchaseCount"
                                                name="txtPurchaseCount" placeholder=" Enter Purchase Count" readonly>
                                        </div> -->

                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPurchaseWeight" class="form-label">Purchase Weight (Kgs)  </label>
                                            <input type="text" class="form-control" id="txtPurchaseWeight"
                                                name="txtPurchaseWeight" placeholder=" Enter Purchase Weight " readonly>
                                        </div> -->   

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFarmLocation" class="form-label">Farm Location </label>
                                            <input type="text" class="form-control" id="txtFarmLocation"
                                                name="txtFarmLocation" placeholder=" Enter Farm Location" readonly >
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks </label>
                                            <input type="text" class="form-control" id="txtRemarks"
                                                name="txtRemarks" placeholder=" Enter Remarks" readonly>
                                        </div>

                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBillName" class="form-label">Supplier Name </label>  <!-- SupplierName -->
                                            <select class="drpSelect" name="drpSuppliers" id="drpSuppliers" disabled>
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmSuppliers as $Item) { ?>
                                                        <option value="<?php echo $Item->SupplierID; ?>" > <?php echo $Item->FinancierName."-".$Item->PanNO; ?></option>
                                                <?php } ?>
                                            </select>
                                            <!-- <input type="text" class="form-control" id="txtBillName"
                                                name="txtBillName" placeholder=" Enter Bill Name" readonly> -->
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtExtra" class="form-label">Addidtion/Deduction Per Kg <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtExtra"
                                                name="txtExtra" placeholder="Enter Addidtion/Deduction Per Kg" >
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBillRemarks" class="form-label">Remarks </label>
                                            <input type="text" class="form-control" id="txtBillRemarks"
                                                name="txtBillRemarks" placeholder=" Enter Remarks" >
                                        </div>

                                    </div>
                                    
                                
                                
                                    <div id="divDataList">
                                        
                                    </div>


                                    <div class="text-center">
                                        <a href="rm-bills-list" class="btn btn-danger">Close</a>
                                        <button class="btn btn-primary" name="submit" id="submit" type="submit">Save</button>
                                        <button class="btn btn-primary" name="submit" id="btnEdit" type="button">Edit</button>
                                        <!-- <button class="btn btn-success" name="btnPrintPreview" id="btnPrintPreview" type="button">Print Preview</button> -->

                                        <!-- <a href="<?php echo WORKING_URL ?>/Sales/Reports/T1/commission-bill?ID=<?php echo $ArrivalID ?>" target="_blank" class="btn btn-success">Print Preview</a> -->
                                        
                                        <input type="hidden" id="hdnRmSupplierID" />
                                        
                                    </div>
                                </form>
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



<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

        <script>
            var arrivalID = "<?php echo isset($_GET["ArrivalID"]) && !empty($_GET["ArrivalID"]) ? $_GET["ArrivalID"] : "" ?>";

            function fnShowDataTable() {
                var table = $('#tblDataTable').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true,
                    language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons: [],
                });
                $('.drpSelect').select2({
                    width: '100%'
                });
            }
            function bindFormData(resJson) {

                //SupplierName , ArrivalNumber, ArrivalDate (purchase / bill date), Species
                 console.log(resJson);
                $("#txtSpecies").val(resJson.Species);
                $("#txtPurchaseStation").val(resJson.PurchaseStation);
                $("#txtProcessingStation").val(resJson.ProcessingStation);
                $("#txtPurchaseType").val(resJson.PurchaseType);
                $("#txtLotNumber").val(resJson.ArrivalNumber);
                $("#txtVehicleNumber").val(resJson.VehicalNumber);
                $("#txtDriverName").val(resJson.DriverName);
                $("#txtGraderName").val(resJson.GraderName);
                $("#txtPurchaseDate").val(resJson.BillDate);
                // $("#txtAgentName").val(resJson.AgentName);
                $("#drpAgentName").val(resJson.AgentID).trigger("change");

                // $("#txtPurchaseCount").val(resJson.AgentID);
                // $("#txtPurchaseWeight").val(resJson.FarmAddress);
                $("#txtFarmLocation").val(resJson.FarmAddress);
                $("#txtRemarks").val(resJson.PurchaseRemarks);


                // $("#txtBillName").val(resJson.SupplierName);
                $("#drpSuppliers").val(resJson.RmSupplierID).trigger("change");

                $("#txtExtra").val(resJson.Extra);
                $("#txtBillRemarks").val(resJson.BillRemarks);
                $("#hdnRmSupplierID").val(resJson.RmSupplierID);

                if(resJson.Editable == 1) {
                    $("#submit, #btnEdit").prop("disabled", false);
                }else{
                    $("#submit, #btnEdit").prop("disabled", true);
                }


                // console.log(resJson.BillStatus);
                if(resJson.BillStatus == 1) {
                    $("#submit").text("Update");
                }else{
                    $("#submit").text("Save");
                }

                if(resJson.PriceEditable == 1) {                   
                    $(".txtBrkupRate").attr("readonly", false); 
                }else{
                    $(".txtBrkupRate").attr("readonly", true); 
                }
        
            }
            
            $(document).on("click", "#btnEdit", function() {
                $("#drpAgentName, #drpSuppliers").prop("disabled", false);
            });
        
            $(document).ready(function(){
                
                var frmData = {
                    methodType: "get",
                    ArrivalID: arrivalID,
                }
                $.ajax({
                    url:'Ajax/AjaxBills.php',
                    type:'POST',
                    data: frmData,
                    success:function(data){
                        // console.log(data);
                        var data = JSON.parse(data);
                        
                        var htmlData = data.html;
                        var resJson = data.json;

                        $("#divDataList").html(htmlData);
                        fnShowDataTable(resJson);

                        if(arrivalID) {
                            // console.log();
                            bindFormData(resJson);
                        }
                        if(data.BillStatus == "1") {
                            $("#btnPrintPreview").prop('disabled', true);
                        }

                        return false;
                    }
                });
            });

            $(document).on("keyup", ".txtBrkupRate", function(){
                var rate = parseInt($(this).val());
                var weight = parseInt($(this).closest("tr").find(".brkupWeight input").val());
                
                var totalVal = rate * weight;

                if(!isNaN(totalVal)) {
                    $(this).closest("tr").find(".brkupBreakUpTotal input").val(totalVal);
                }else{
                    $(this).closest("tr").find(".brkupBreakUpTotal input").val("");

                }

            })
            
                $("#frmCreateBill").validate({
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
                        txtExtra: { required: true, noSpace: true }
                    },
                    errorPlacement: function () {
                        return false;  // suppresses error message text
                    },
                    submitHandler: function () {
                            
                            var jsonData = [];
                            $(".brkupArrivalBrkUpID").each(function() {
                                var rowData = {};

                                var customKey = "ArrivalBrkUpID";
                                var customValue = $(this).attr("data-trarrivalbrkupid");
                                rowData[customKey] = customValue;

                                $(this).find(".brkupVariety").each(function() {
                                    var customKey = "Variety";
                                    var customValue = $(this).text();
                                    rowData[customKey] = customValue;
                                });
                                $(this).find(".brkupCount").each(function() {
                                    var customKey = "Count";
                                    var customValue = $(this).find("input").val();
                                    rowData[customKey] = customValue;
                                });
                                $(this).find(".brkupWeight").each(function() {
                                    var customKey = "Weight";
                                    var customValue = $(this).find("input").val();
                                    rowData[customKey] = customValue;
                                });
                                // $(this).find(".brkupMasterRate").each(function() {
                                //     var customKey = "MasterRate";
                                //     var customValue = $(this).find("select").val();
                                //     rowData[customKey] = customValue;
                                // });

                                $(this).find(".brkupMasterRate").each(function() {
                                    var customKey = "MasterRate";
                                    var customValue = $(this).find("input").val();
                                    rowData[customKey] = customValue;
                                });                                

                                $(this).find(".brkupRate").each(function() {
                                    var customKey = "Rate";
                                    var customValue = $(this).find("input").val();
                                    rowData[customKey] = customValue;
                                });
                                $(this).find(".brkupBreakUpTotal").each(function() {
                                    var customKey = "BreakUpTotal";
                                    var customValue = $(this).find("input").val();
                                    rowData[customKey] = customValue;
                                });

                                jsonData.push(rowData);

                            });

                            var breakUpData = JSON.stringify(jsonData, null, 2);                             
                            //console.log(breakUpData);
                        
                            $.ajax({
                                url:'Ajax/AjaxBills.php',
                                type:'POST',
                                data:{
                                    methodType: "post",
                                    ArrivalID: arrivalID,
                                    RmSupplierID: $("#drpSuppliers").val(),
                                    AgentID: $("#drpAgentName").val(),
                                    txtExtra: $("#txtExtra").val(),                                    
                                    txtBillRemarks: $("#txtBillRemarks").val(),
                                    BillBreakup: breakUpData,
                                },
                                success:function(data){
                                    var data = JSON.parse(data);
                                    var htmlData = data.html;
                                    var resJson = data.json;
                                    var error = data.Error;
                                    var msg = data.Msg;

                                    console.log(data);

                                    if(error === null){
                                        var frmData = {
                                            methodType: "POST",
                                            ArrivalID: arrivalID,
                                            BillDate: $("#txtPurchaseDate").val()
                                        }
                                        $.ajax({
                                            url:'Ajax/AjaxBillsBGTdsCal.php',
                                            type:'POST',
                                            data: frmData,
                                            success:function(data){
                                                alert(msg);
                                            }
                                        });    
                                        var printURL = '<?php echo WORKING_URL ?>Purchase/Reports/T1/commission-bill-page1?ID=<?php echo $ArrivalID ?>';

                                        window.open(printURL, '_blank');

                                    }else{
                                        alert(error);
                                        return false;
                                    }

                                    if(arrivalID == "") {
                                        var newUrl = window.location.href+"?ArrivalID="+resJson.RmArrivalID;
                                        window.location.href = newUrl;
                                    }

                                    $("#divDataList").html(htmlData);
                                    fnShowDataTable();                                     

                                    if(arrivalID) {
                                        bindFormData(resJson);
                                    }
                                    $("#drpVariety").val("").trigger('change');
                                    $("#txtArrivalCount").val("");
                                    $("#txtArrivalWeight").val("");
                                    $("#txtArrivalRate").val("");
                                    $("#drpAgentName, #drpSuppliers").prop("disabled", true);
                                    return false;
                                }
                            });              
                    }  
                });

                var printURL = '<?php echo WORKING_URL ?>Purchase/Reports/T1/commission-bill?ID=<?php echo $ArrivalID ?>';

                $("#btnPrintPreview").click(function() {
                    window.open(printURL, '_blank');
                })
                
        </script> 

</body>

</html>
