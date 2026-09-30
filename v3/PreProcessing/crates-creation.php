<style>
/* Custom grey-out style for disabled fields */
input:disabled, select:disabled {
    background-color: #f0f0f0 !important; /* Apply background color with !important */
    color: #888888 !important; /* Grey text with !important */
    border: 1px solid #dcdcdc !important; /* Light grey border */
}
</style>

<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = base64_decode($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

// $ArrivalID = trim($_GET['ArrivalID']);

$LotID = isset($_GET['LotID']) && !empty($_GET['LotID']) ? $_GET['LotID'] : "";
$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";
$saveType = isset($_GET['saveType']) && !empty($_GET['saveType']) ? trim($_GET['saveType'], "\"'") : "";


$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'LotID' => $LotID
);

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resDefects = $resDropdownsAPI->Data->Defects;
$resSupervisor = $resDropdownsAPI->Data->Supervisor;



$List = $resDropdownsAPI->Data;

$PlantID = $_SESSION['UnitID'];


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
                        <?php 
                        if(!empty($_GET['saveType']) && $_GET['saveType'] == 'save'){?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                    <form id="frmReceival">
                                        <div class="row">                                      

                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpArrivalPlant" class="form-label">H/O Plant<span
                                                        style="color:red">*</span></label>
                                                <select class="drpSelect form-control" name="drpArrivalPlant"
                                                    id="drpArrivalPlant">
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resProductionPlant as $Item) { ?>
                                                        <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                            <?php echo $Item->FullName . ' - ' . $Item->StationName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtBatchNumber" class="form-label">Batch Number <span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtBatchNumber"
                                                    name="txtBatchNumber" maxlength="15" placeholder="Enter Batch Number" readonly>
                                            </div>

                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpVariety" class="form-label">Variety<span
                                                        style="color:red">*</span></label>
                                                <select class="drpSelect form-control" name="drpVariety" id="drpVariety" disabled>
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resDefects as $Item) { ?>
                                                        <option value="<?php echo $Item->DefectID; ?>">
                                                            <?php echo $Item->DefectName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtNoofPieces" class="form-label">No.of Pieces <span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtNoofPieces"
                                                    name="txtNoofPieces" maxlength="15" placeholder="Enter No.of Pieces" disabled>
                                            </div>

                                        
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtCrateNumber" class="form-label">Crate Number <span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtCrateNumber"
                                                    name="txtCrateNumber" maxlength="15" placeholder="Enter Crate Number" disabled>
                                            </div>
                                            <!-- <div class="form-group col-sm-3 mb-3">
                                                <label for="txtActualCrateWeight" class="form-label">Actual Crate Weight<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtActualCrateWeight"
                                                    name="txtActualCrateWeight" maxlength="15" placeholder="Enter Actual Crate Weight" disabled>
                                            </div> -->

                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtReceivedCrateWeight" class="form-label">Received Crate Weight<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtReceivedCrateWeight"
                                                    name="txtReceivedCrateWeight" maxlength="15" placeholder="Enter Received Crate Weight" disabled>
                                            </div>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpSupervisor" class="form-label">Supervisor
                                                        <span style="color:red"></span>
                                                </label>
                                                <select class="drpSelect form-control" name="drpSupervisor"
                                                    id="drpSupervisor" disabled>
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resSupervisor as $Item) { ?>
                                                        <option value="<?php echo $Item->ID; ?>">
                                                            <?php echo $Item->UserName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>



                                        </div>
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="" />

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                                value="<?php echo $LotID ?>" />
                                            
                                            <button class="btn btn-danger" name="submit" id="close"
                                                type="button" onclick="closeCurrentTab()">Close</button>
                                        
                                                <button class="btn btn-primary" name="submit" id="submit"
                                                type="submit">Save</button>
                                                
                                                <button class="btn btn-success"  id="plantCheckBtn"
                                                type="button">PlantCheck</button> 
                                                
                                                <button class="btn btn-info"  id="crateBtn"
                                                type="button">Crate</button>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php }?>

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
   
$(document).ready(function () {

    // $("#submit").prop("disabled", true);
    var SaveTypeSave = <?php echo json_encode($saveType); ?>;
    SaveTypeSave = (SaveTypeSave == 'view') ? 'PlantCheck' : SaveTypeSave;
    if(SaveTypeSave != ''){
        $('#hdnSaveType').val(SaveTypeSave);
        getCratesData(SaveTypeSave);//SaveType Value
    }

    var productionPlant = <?php echo $PlantID ?>;

    if(typeof(IsPlantFilter)  === "undefined"){
        var IsPlantFilter = 0;
    }
    
    if (productionPlant != 0 || IsPlantFilter == 0) {
        $("#drpArrivalPlant").prop('disabled', false);
        $("#drpArrivalPlant").val(productionPlant).trigger('change');
    } else {
        $("#drpArrivalPlant").prop('disabled', false);
    }

   
    // Button click event for Plant Check
    $("#plantCheckBtn").click(function () {
        // Enable/Disable fields based on button click
        $('#hdnSaveType').val('PlantCheck');  // Set SaveType to "Plantcheck"

        $("#drpVariety, #txtNoofPieces, #drpSupervisor").prop('disabled', false);
        $("#txtCrateNumber, #txtReceivedCrateWeight").prop('disabled', true);

        getCratesData('save');//SaveType Value
        getCratesData('PlantCheck');//SaveType Value
        // checkSaveType('PlantCheck');
    });

    // Button click event for Crate
    $("#crateBtn").click(function () {
        // Enable/Disable fields based on button click
        $('#hdnSaveType').val('Crate');  // Set SaveType to "Crate"

        $("#txtCrateNumber, #txtReceivedCrateWeight").prop('disabled', false);
        $("#drpVariety, #txtNoofPieces, #drpSupervisor").prop('disabled', true);
        getCratesData('save');//SaveType Value
        getCratesData('Crate'); //SaveType Value
        // checkSaveType('Crate');

    });

});

//function checkSaveType(saveType) {
     //   let saveTypeValue = saveType; // Get value and remove spaces

        // Enable button only if saveTypeValue is not empty
       // $("#btnSave").prop("disabled", saveTypeValue === "");
    //}

// Existing function to get crate data
function getCratesData(saveType) {
    var lotID = '<?php echo $LotID; ?>';
    var ArrivalID = '<?php echo $ArrivalID; ?>';
    $.ajax({
        url: 'Ajax/ajax-crate-creation.php',
        type: 'POST',
        data: {
            methodType: "getLotData",
            Variety: $('#drpVariety').val(),
            LotID: lotID,
            ArrivalID: ArrivalID,
            SaveType: saveType,
            pageType : '<?php echo $saveType;?>' ,
        },
        success: function (data) {
            // console.log(data.FormData.LotNumber);
            console.log("data",data);
            <?php 
            if(!empty($_GET['saveType']) && $_GET['saveType'] == 'save'){?>
                if (data.FormData) {
                    if(saveType == 'PlantCheck'){
                        var SuperVisorId = '';
                        $(data.FormData.PlantCheckDetails).each(function(index, element) {
                            if(SuperVisorId == '' || SuperVisorId == 0){
                                SuperVisorId = element.SuperVisorId;
                            }
                        });
                        if(SuperVisorId != '' && SuperVisorId != 0){
                            $("#drpSupervisor").val(SuperVisorId).trigger("change").prop('disabled', true);;
                        }else{
                            $("#drpSupervisor").val('').trigger("change").prop('disabled', false);;
                        }
                    }else{
                        $("#drpSupervisor").val('').trigger("change").prop('disabled', true);;
                    }
                    $("#txtBatchNumber").val(data.FormData.LotNumber);
                    if(data.FormData?.PlantCheckDetails[0]?.PlantID){
                        $("#drpArrivalPlant").val(data.FormData.PlantCheckDetails[0].PlantID).trigger("change");
                    }
                
                }
            <?php }?>
            if (data.htmlRes) {
                $("#divDataList").html(data.htmlRes);
                fnShowDataTable();
            }

            return false;
        }
    });
}
</script>



<script>
    function closeCurrentTab() {
        window.close();
    }
</script>

<script>
    var regex1 = /^\d{0,3}(\.\d{0,3})?$/; // txtSampleQty  999.999
    var regex2 = /^\d{0,4}?$/; // txtNoOfPcs  9999
    var regex3 = /^\d{1,3}(\.\d{0,3})?$/; //  txtRecvCount  999.999
    var regex4 = /^\d{1,5}(\.\d{0,3})?$/; // txtRecvQty 



    var arrivalID = "<?php echo isset($_GET["ArrivalID"]) && !empty($_GET["ArrivalID"]) ? $_GET["ArrivalID"] : "" ?>";
    var table;


    function fnDateformat(dt) {
        // var arrivalDate = $("#txtArrivalDate").val();
        var parts = dt.split("-");
        return parts[2] + "-" + parts[1] + "-" + parts[0];
    }

    function fnShowDataTable() {

        // var excelHeader = "Recevied__" + fnDateformat($("#txtArrivalDate").val()) + "_" + $.trim($("#drpVehicleNumber option:selected").text()) + "_" + $("#txtTrackCode").val() + "_" + $("#txtSealNumber").val();
        var excelHeader = 'Crates Data';
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
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                 title: excelHeader, //'Recevied  -<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    // columns: ':visible',
                   /*  columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    format: {
                        body: function (inner, rowidx, colidx, node) {
                            if ($(node).children("input").length > 0) {
                                return $(node).children("input").first().val();
                            } else {
                                return inner;
                            }
                        }
                    } */
                }
            }],
        });
    }


    

    $("#frmReceival").validate({
        onkeyup: false,
        onfocusout: false,
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                firstInvalidElement.focus();
            }
        },
        ignore: "readonly",
        rules: {
            txtArrivalDate: {
                required: true,
            },
            drpVehicleNumber: {
                required: true,
            },
            drpArrivalPlant: {
                required: true,
            },
            txtTrackCode: {
                required: true,
            },
            txtSealNumber: {
                required: true,
            },
            /* drpSupervisor: {
                required: true,
            }, */
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {
            var lotID='<?php echo $LotID; ?>';
            var arrivalID='<?php echo $ArrivalID; ?>';

            var saveType = $('#hdnSaveType').val();
            if(saveType==''){
                alert("Save type not found");
                return false; 
            }
        
            $.ajax({
                url: 'Ajax/ajax-crate-creation.php',
                type: 'POST',
                data: {
                    methodType: "FormPost",
                    lotID: lotID,
                    arrivalID:arrivalID,
                    drpVarietyID: $("#drpVariety").val(),
                    arrivalPlantId: $("#drpArrivalPlant").val(),
                     txtNoofPieces: $("#txtNoofPieces").val(),
                    txtCrateNumber: $("#txtCrateNumber").val(),
                    txtReceivedCrateWeight: $("#txtReceivedCrateWeight").val(),
                    // txtActualCrateWeight: $("#txtActualCrateWeight").val(),
                    drpSupervisor : $("#drpSupervisor").val(),

                    SaveType:saveType
                },
    

    success: function (data) {
            console.log(data.FormData.LotNumber);


            if (data.FormData) {
                $("#txtBatchNumber").val(data.FormData.LotNumber);
              
            }
            if(saveType == 'PlantCheck'){
                var SuperVisorId = '';
                $(data.FormData.PlantCheckDetails).each(function(index, element) {
                    if(SuperVisorId == '' || SuperVisorId == 0){
                        SuperVisorId = element.SuperVisorId;
                    }
                }); 
                if(SuperVisorId != '' && SuperVisorId != 0){
                    $("#drpSupervisor").val(SuperVisorId).trigger("change").prop('disabled', true);;
                }else{
                    $("#drpSupervisor").val('').trigger("change").prop('disabled', false);;
                }
            }else{
                $("#drpSupervisor").val('').trigger("change").prop('disabled', true);;
            }
            if(saveType != 'save'){
                if (data.htmlRes) {
                    $("#divDataList").html(data.htmlRes);
                    fnShowDataTable();
                }
            }
            alert(data.Msg);  // You can customize this message as needed
           if(SuperVisorId != '' && SuperVisorId != 0){
                $("#frmReceival")
                // .find("input:not(#drpArrivalPlant,#txtBatchNumber, #hdnSaveType), textarea, select")
                .find("input:not(#drpArrivalPlant,#txtBatchNumber, #hdnSaveType), textarea, select:not(#drpArrivalPlant, #drpSupervisor)")

                .val("")
                .trigger("change"); // ✅ Important for select fields
            }else{
                $("#frmReceival")
                // .find("input:not(#drpArrivalPlant,#txtBatchNumber, #hdnSaveType), textarea, select")
                .find("input:not(#drpArrivalPlant,#txtBatchNumber, #hdnSaveType), textarea, select:not(#drpArrivalPlant)")

                .val("")
                .trigger("change"); // ✅ Important for select fields   
            }

    // ✅ Reset checkboxes and radio buttons
    $("#frmReceival").find("input[type=checkbox], input[type=radio]").prop("checked", false);
           
            return false;
        }
            });
        }
    });


    $(document).on("click", ".btnDelete", function () {

        var CrateDataID = $(this).attr("data-breakup-id");
        var lotID='<?php echo $LotID; ?>';
        var saveType = $('#hdnSaveType').val();
        // alert(CrateDataID);

        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-crate-creation.php',
                type: 'POST',
                data: {
                    methodType: "deleteRow",
                    CrateDataID: CrateDataID,
                    SaveType: saveType,
                    LotID: lotID,

                },
                success: function (data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                        if(saveType == 'PlantCheck'){
                            var SuperVisorId = '';
                            $(data.FormData.PlantCheckDetails).each(function(index, element) {
                                if(SuperVisorId == '' || SuperVisorId == 0){
                                    SuperVisorId = element.SuperVisorId;
                                }
                            });
                            if(SuperVisorId != '' && SuperVisorId != 0){
                                $("#drpSupervisor").val(SuperVisorId).trigger("change").prop('disabled', true);;
                            }else{
                                $("#drpSupervisor").val('').trigger("change").prop('disabled', false);;
                            }
                        }else{
                            $("#drpSupervisor").val('').trigger("change").prop('disabled', true);;
                        }
                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();

                            // fnValidateBreakup(); // Breakup Validation
                        }
                    }
                    return false;
                }
            });
        }

    });




</script>

</body>

</html>