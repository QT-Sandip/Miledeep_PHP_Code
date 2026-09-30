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

$dhid = isset($_GET['dhid']) && !empty($_GET['dhid']) ? $_GET['dhid'] : "";


$api = API_PREPROCESSING_DROPDOWNS;
$resDropdownsAPI1 = CallAPI($api, "GET", "", $bodyContent);

$api = API_RM_DROPDOWNS;

$bodyContent = array(
    'DHID' => $dhid
);

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resDropdownsAPI);die;
//$resVariety = $resDropdownsAPI->Data->Variety;
$resVariety = $resDropdownsAPI->Data->RmVariety;

$resCenter = $resDropdownsAPI1->Data->Center;
$resSupervisor = $resDropdownsAPI1->Data->Supervisor;

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
                        if(empty($_GET['type']) && $_GET['type'] != 'view'){?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                    <form id="frmDeheadingBreakUp">
                                        <div class="row">
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpCenter" class="form-label">De Heading Station<span
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
                                            <div class="form-group col-sm-3">
                                                <label for="drpSuperVisor" class="form-label">SuperVisor <span
                                                        style="color:red">*</span></label>
                                                <select class="drpSelect form-control" name="drpSuperVisor"
                                                    id="drpSuperVisor" >
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resSupervisor as $Item) { ?>
                                                        <option value="<?php echo $Item->ID; ?>">
                                                            <?php echo $Item->UserName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpVariety" class="form-label">Variety<span
                                                        style="color:red">*</span></label>
                                                <select class="drpSelect form-control" name="drpVariety"
                                                    id="drpVariety">
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resVariety as $Item) { ?>
                                                        <option value="<?php echo $Item->VarietyID; ?>">
                                                            <?php echo $Item->VarietyName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                        
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtNoofPieces" class="form-label">No.of Pieces <span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtNoofPieces"
                                                    name="txtNoofPieces" maxlength="15" placeholder="Enter No.of Pieces">
                                            </div>

                                        

                                        </div>
                                        <input type="hidden" id="hdnID" name="hdnID" value="" />

                                        <div class="text-center">
                                            <input type="hidden" id="dhid" name="dhid"
                                                value="<?php echo $dhid ?>" />
                                            
                                            <button class="btn btn-danger" name="submit" id="close"
                                                type="button" onclick="closeCurrentTab()">Close</button>
                                        
                                                <button class="btn btn-primary" name="submit" id="submit"
                                                type="submit">Save</button>
                                            

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
    getBreakupData();
});



// Existing function to get crate data
function getBreakupData() {
    var DHID = '<?php echo $dhid; ?>';
    var ArrivalID = '<?php echo $ArrivalID; ?>';
    $.ajax({
        url: 'Ajax/ajax-de-heading-trackid-breakup.php',
        type: 'POST',
        data: {
            methodType: "getData",
            DHID: DHID,
            type: "<?php echo $_GET['type'];?>",
        },
        success: function (data) {
            // console.log(data.FormData.LotNumber);
            console.log("data",data);
            
            if (data.htmlRes) {
                $("#divDataList").html(data.htmlRes);
                fnShowDataTable();
            }
            var SuperVisorId = CenterId = '';
            $(data.Data).each(function(index, element) {
                if(SuperVisorId == '' || SuperVisorId == 0){
                    SuperVisorId = element.SuperVisorId;
                }
                if(CenterId == '' || CenterId == 0){
                    CenterId = element.CenterId;
                }
            }); 
            if(SuperVisorId != '' && SuperVisorId != 0){
                $("#drpSuperVisor").val(SuperVisorId).trigger("change").prop('disabled', true);;
            }else{
                $("#drpSuperVisor").val('').trigger("change").prop('disabled', false);;
            }
            if(CenterId != '' && CenterId != 0){
                $("#drpCenter").val(CenterId).trigger("change").prop('disabled', true);;
            }else{
                $("#drpCenter").val('').trigger("change").prop('disabled', false);;
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
    
    var table;
    function fnShowDataTable() {

         var excelHeader = "De-Heading Breakup";

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
                sheetName: 'De-Heading-Breakup',
                footer: true,
                exportOptions: {
                    // columns: ':visible',
                    /* columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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

    $("#frmDeheadingBreakUp").validate({
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
            drpVariety: {
                required: true,
            },
            txtNoofPieces: {
                required: true,
            },
            drpSuperVisor: {
                required: true,
            },
            drpCenter: {
                required: true,
            },
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {
            var dhid='<?php echo $dhid; ?>';

        
            $.ajax({
                url: 'Ajax/ajax-de-heading-trackid-breakup.php',
                type: 'POST',
                data: {
                    methodType: "FormPost",
                    dhid: dhid,
                    drpVarietyID: $("#drpVariety").val(),
                    txtNoofPieces: $("#txtNoofPieces").val(),
                    hdnID: $("#hdnID").val(),
                    superVisor: $("#drpSuperVisor").val(),
                    center: $("#drpCenter").val(),
                },  
                success: function (data) {
                    //console.log(data.FormData.LotNumber);
                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();
                    }
                    fnShowDataTable();
                    alert(data.Msg);  // You can customize this message as needed
                    if(data.Error == null){
                        $("#frmDeheadingBreakUp").find("input:not(#drpVariety#hdnID), select:not(#txtNoofPieces)").val("").trigger("change"); // ✅ Important for select fields           
                    }
                     var SuperVisorId = CenterId = '';
                    $(data.Data).each(function(index, element) {
                        if(SuperVisorId == '' || SuperVisorId == 0){
                            SuperVisorId = element.SuperVisorId;
                        }
                        if(CenterId == '' || CenterId == 0){
                            CenterId = element.CenterId;
                        }
                    }); 
                    if(SuperVisorId != '' && SuperVisorId != 0){
                        $("#drpSuperVisor").val(SuperVisorId).trigger("change").prop('disabled', true);;
                    }else{
                        $("#drpSuperVisor").val('').trigger("change").prop('disabled', false);;
                    }
                    if(CenterId != '' && CenterId != 0){
                        $("#drpCenter").val(CenterId).trigger("change").prop('disabled', true);;
                    }else{
                        $("#drpCenter").val('').trigger("change").prop('disabled', false);;
                    }
                    return false;
                }
            });
        }
    });

    $(document).on("click", ".btnEdit", function () {
        if (confirm("Are you sure you want to Edit this item ?") == true) {
            $("#hdnID").val($(this).attr("data-id"));
            $("#txtNoofPieces").val($(this).attr("data-Noof_peaces"));
            var variety_id = $(this).attr("data-variety_id");
            $("#drpVariety").val(variety_id).trigger('change');

             var supervisor_id = $(this).attr("data-supervisor_id");
            $("#drpSuperVisor").val(supervisor_id).trigger('change');
             var center_id = $(this).attr("data-center_id");
            $("#drpCenter").val(center_id).trigger('change');
        }
    })
    $(document).on("click", ".btnDelete", function () {

        var ID = $(this).attr("data-breakup_id");
        var dhid='<?php echo $dhid; ?>';
        // alert(CrateDataID);

        if (confirm("Are you sure you want to delete this item ?") == true) {
            $("#frmDeheadingBreakUp").find("input:not(#drpVariety#hdnID), select:not(#txtNoofPieces)").val("").trigger("change"); // ✅ Important for select fields           
            $.ajax({
                url: 'Ajax/ajax-de-heading-trackid-breakup.php',
                type: 'POST',
                data: {
                    methodType: "deleteRow",
                    ID: ID,
                    dhid: dhid,

                },
                success: function (data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                      
                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();

                            // fnValidateBreakup(); // Breakup Validation
                        }

                        var SuperVisorId = CenterId = '';
                        $(data.Data).each(function(index, element) {
                            if(SuperVisorId == '' || SuperVisorId == 0){
                                SuperVisorId = element.SuperVisorId;
                            }
                            if(CenterId == '' || CenterId == 0){
                                CenterId = element.CenterId;
                            }
                        }); 
                        if(SuperVisorId != '' && SuperVisorId != 0){
                            $("#drpSuperVisor").val(SuperVisorId).trigger("change").prop('disabled', true);;
                        }else{
                            $("#drpSuperVisor").val('').trigger("change").prop('disabled', false);;
                        }
                        if(CenterId != '' && CenterId != 0){
                            $("#drpCenter").val(CenterId).trigger("change").prop('disabled', true);;
                        }else{
                            $("#drpCenter").val('').trigger("change").prop('disabled', false);;
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