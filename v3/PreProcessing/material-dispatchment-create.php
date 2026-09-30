<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$MaterialDispatchID = trim($_GET['MaterialDispatchID']);
$TransactionType = isset($_GET['TransactionType']) && !empty($_GET['TransactionType']) ? $_GET['TransactionType'] : "";

// $ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
// $hasWageSelector = $ArrayClientConfigData['hasWageSelector'];
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
// $resShift = $resDropdownsAPI->Data->Shift;
//$resVariety = $resDropdownsAPI->Data->Variety;
$resHlVaBillVariety = $resDropdownsAPI->Data->HlVaBillVariety;
$resGrade = $resDropdownsAPI->Data->Grade;
$resWages = $resDropdownsAPI->Data->Wages;

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
                                <form id="frmValAdd">
                                    <div class="row">
                                    <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductionPlant" 
                                                id="drpProductionPlant" required>
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
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder="" />
                                        </div>

                                        <!-- 02-02-2024 Durga Prasad -->
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpBatchNo" class="form-label">Batch No<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpBatchNo" id="drpBatchNo">
                                                <option value=""> Select</option>
                                                <!-- <option value="0"> Melting</option>
                                                <option value="1"> Reglazing</option> -->
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpFromStation" class="form-label">From Station</label>
                                            <select class="drpSelect form-control" name="drpFromStation" id="drpFromStation">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCenter as $Center) { ?>
                                                    <option value="<?php echo $Center->StationID ?>"><?php echo $Center->UnitName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpToStation" class="form-label">To Station</label>
                                            <select class="drpSelect form-control" name="drpToStation" id="drpToStation">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCenter as $Center) { ?>
                                                    <option value="<?php echo $Center->StationID ?>"><?php echo $Center->UnitName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransferredDate" class="form-label">Transferred Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransferredDate"
                                                name="txtTransferredDate" value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpGrade" id="drpGrade">
                                                <option value="">All</option>
                                                <?php foreach ($resGrade as $Grade) { ?>>
                                                    <option value="<?php echo $Grade->value ?>"><?php echo $Grade->value ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Variety</label>
                                            <select class="drpSelect form-control" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                                <?php foreach ($resHlVaBillVariety as $HlVaBillVariety) { ?>
                                                    <option value="<?php echo $HlVaBillVariety->VarietyID ?>">
                                                        <?php echo $HlVaBillVariety->ShortName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtQuantity" class="form-label">Quantity</label>
                                            <input type="text" class="form-control" id="txtQuantity" name="txtQuantity" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtDispatchedCrates" class="form-label">Dispatched Crates</label>
                                            <input type="text" class="form-control" id="txtDispatchedCrates" name="txtDispatchedCrates" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtReceivedCrates" class="form-label">Received Crates</label>
                                            <input type="text" class="form-control" id="txtReceivedCrates" name="txtReceivedCrates"  disabled/>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpStatus" class="form-label">Status</label>
                                            <select class="drpSelect form-control" name="drpStatus" id="drpStatus" disabled>                                                
                                                <option value="0"> Pending</option>
                                                <option value="1"> Received</option>                                               
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <!-- <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" /> -->
                                        <!-- <a href="rm-arrival-list" class="btn btn-danger">Close</a> -->

                                        <!-- <a href="material-dispatchment-list"
                                            class="newItem btn btn-danger waves-effect waves-light">
                                             Close
                                        </a> -->

                                        <a href="#" id="btnCloseWindow" class="newItem btn btn-danger waves-effect waves-light">    Close </a>
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                        <!-- <input type="hidden" id="hdnIsUpdate" value="" /> -->
                                        <!-- <button class="btn btn-danger" id="btnCancel" type="button">Cancel</button> -->
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="divDataList">

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


    // function fnShowDataTable() {
    //     table = $('#tblDataTable').DataTable({
    //         scrollY: "600px",
    //         scrollX: true,
    //         sScrollXInner: "100%",
    //         paging: false,
    //         destroy: true,
    //         fixedColumns: true,
    //         scrollCollapse: true,
    //         dom: 'Blfrtip',
    //         buttons: [{
    //             extend: 'excel',
    //             text: 'Download Excel',
    //             className: 'btnExcelDataTable',
    //             title: 'Material-Dispatchment-<?php echo date("d-m-Y") ?>',
    //             footer: true,
    //             exportOptions: {
    //                 columns: ':visible'
    //             }
    //         }],
    //     });
    // }
    
    $("#btnCloseWindow").on("click", function (e) {
    e.preventDefault();
    window.close();
});



$(document).ready(function () {

    var materialDispatchID = "<?php echo $MaterialDispatchID ; ?>";
    var TransactionType = "<?php echo $TransactionType ; ?>";

    

    if (TransactionType === "Receiver") {
        $("#drpStatus").prop("disabled", false);
        $("#txtReceivedCrates").prop("disabled", false);
        $("#txtDispatchedCrates").prop("disabled", true);

        
    } else {
        $("#drpStatus").prop("disabled", true);
        $("#txtReceivedCrates").prop("disabled", true);
        
    }

    var materialDispatchID = "<?php echo $MaterialDispatchID ; ?>";

    if (materialDispatchID !== '') {
        $.ajax({
            url: 'Ajax/ajax-material-dispatchment-create.php',
            type: 'POST',
            dataType: 'json',
            data: {
                methodType: 'getFormData', // <- must match PHP
                materialdispatchid: materialDispatchID
            },
            success: function (response) {
                // if (response && response.Data && response.Data.length > 0) {
                //     fillFormWithData(response.Data[0]); // Using first item
                // } else {
                //     console.warn('No DispatchMaterialList data received');
                // }
                if (response && response.DispatchMaterialList && response.DispatchMaterialList.length > 0) {
        fillFormWithData(response.DispatchMaterialList[0]); // First object from array
    } else {
        console.warn('No DispatchMaterialList data received');
    }
            }
            ,
            error: function (xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText || error);
            }
        });
    }

    function fillFormWithData(data) {
        // console.log("Trying to set ArrivalID:", data.ArrivalID);
        $("#drpProductionPlant").val(data.PlantID).trigger('change');

        $("#txtArrivalDate").val(data.ArrivalDate);
        // $("#drpBatchNo").val(data.ArrivalID).trigger('change');
        getarrivals(function () {
        $("#drpBatchNo").val(data.ArrivalID).trigger('change');
    });
        $("#drpFromStation").val(data.FromStationID).trigger('change');
        $("#drpToStation").val(data.ToStationID).trigger('change');
        $("#txtTransferredDate").val(data.TransferredDate);
        $("#drpGrade").val(data.Grade).trigger('change');
        $("#drpVariety").val(data.VarietyID).trigger('change');
        $("#txtQuantity").val(data.Quantity);
        $("#txtDispatchedCrates").val(data.DispatchedCrates);
        $("#txtReceivedCrates").val(data.ReceivedCrates);
        $("#drpStatus").val(data.Status).trigger('change');
    }
});



    //get arrivals based onchange txtArrivalDate
    $(document).on("change", "#txtArrivalDate", function () {
        getarrivals();
      
    });
//get arrivals based onchange change
    $(document).on("change", "#drpProductionPlant", function () {
        getarrivals();
      
    }); 

    // function getarrivals(){
        function getarrivals(callback = null){

        
        var plantid= $("#drpProductionPlant").val();
        var arrivaldate= $("#txtArrivalDate").val();

        if(plantid=='' || arrivaldate==''){
        return;
        }
      
        $.ajax({
            url: 'Ajax/ajax-material-dispatchment-create.php',
            type: 'POST',
            data: {
                ArrivalDate: $("#txtArrivalDate").val(),
                PlantID:plantid,
                methodType: "get"
            },          
            

            success: function (data) {
                // console.log(data.Data.ArrivalsList);
        var list = '<option value="">Select</option>';

        $.each(data.Data.ArrivalsList, function (index, value) {
            list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + "</option>";
        });

        $('#drpBatchNo').html(list); // Bind to dropdown

        /////
        if (typeof callback === "function") {
                setTimeout(() => {
                    callback();
                }, 200); // Adjust timing if needed
            }
       
}
        });
        return "success";

    }

   

    $("#frmValAdd").validate({

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
            txtArrivalDate: {
                required: true,
                selectcheck: true
            },
            drpBatchNo: {
                required: true,
                selectcheck: true
            },
            drpFromStation: {
                required: true,
                selectcheck: true
            },
            drpToStation: {
                required: true,
                selectcheck: true
            },
            txtTransferredDate: {
                required: true,
                noSpace: true
            },
            drpGrade: {
                required: true,
                noSpace: true
            },
            drpVariety: {
                required: true,
                selectcheck: true
            },
            txtQuantity: {
                required: true,
                noSpace: true
            },
            txtDispatchedCrates: {
                required: true,
                noSpace: true
            },
            // txtReceivedCrates: {
            //     required: true,
            //     noSpace: true
            // },
            drpStatus: {
                required: true,
                selectcheck: true
            },
            drpProductionPlant: {
                required: true,
                selectcheck: true
            },
            
        },
        errorPlacement: function () {
            return false;
        },
        submitHandler: function () {
    var materialdispatchid = "<?php echo $MaterialDispatchID; ?>";

    $.ajax({
        url: 'Ajax/ajax-material-dispatchment-create.php',
        type: 'POST',
        data: {
            drpBatchNo: $("#drpBatchNo").val(),
            drpFromStation: $("#drpFromStation").val(),
            drpToStation: $("#drpToStation").val(),
            txtTransferredDate: $("#txtTransferredDate").val(),
            drpGrade: $("#drpGrade").val(),
            drpVariety: $("#drpVariety").val(),
            txtQuantity: $("#txtQuantity").val(),
            txtDispatchedCrates: $("#txtDispatchedCrates").val(),
            txtReceivedCrates: $("#txtReceivedCrates").val(),
            drpStatus: $("#drpStatus").val(),
            drpProductionPlant: $("#drpProductionPlant").val(),
            txtArrivalDate: $("#txtArrivalDate").val(),
            
            materialdispatchid: materialdispatchid,
            methodType:"post"
        },
        success: function (data) {
            // console.log(data); 
            
            var error = data.Error;
            var msg = data.Msg;
           
            if (error === null) {
                alert(msg);
                window.close();
            } else {
                alert(error);
            }

            return false;
        }
    });
}

    });

 
</script>

</body>

</html>