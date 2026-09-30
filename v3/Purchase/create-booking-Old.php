<?php 

include_once '../config/global.php';
include_once '../config/api-call.php';

$BookingID = isset($_GET['BookingID']) && !empty($_GET['BookingID']) ? $_GET['BookingID'] : "";

$api = API_DROPDOWNS;
$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resSpecies = $resDropdownsAPI->Data->Species;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters; // Purchase Stations / Purchase centers 
$resPurchaseCategory = $resDropdownsAPI->Data->PurchaseCategory; 
$resVehicles = $resDropdownsAPI->Data->Vehicles;
$resRmAgents = $resDropdownsAPI->Data->RmAgents;
$resDrivers = $resDropdownsAPI->Data->Drivers;
$resRmGraders = $resDropdownsAPI->Data->RmGraders;
$resRmBillName = $resDropdownsAPI->Data->RmBillName;
$resRmVariety = $resDropdownsAPI->Data->RmVariety;

if($BookingID != "") {
    $apiGetSingleBookings = API_RM_BOOKING;

    $bodyContent = array(
        "BookingID" => $BookingID
    );
    
    $resGetSingleBookingsAPI = CallAPI($apiGetSingleBookings, "GET", "", $bodyContent);
    $resGetSingleBookingsList = $resGetSingleBookingsAPI->BreakUpData;
    $resGetSingleBookings = $resGetSingleBookingsAPI->BookingData[0];

}else{
    $resGetSingleBookingsList = "";
    $resGetSingleBookings = "";
}

if(isset($_POST['submit'])){

	$apiPost = API_RM_BOOKING;
		
	$drpSpecies = $_POST["drpSpecies"];	
	$drpBookingStationId = $_POST["drpBookingStationId"];	
	$drpPurchaseType= $_POST["drpPurchaseType"];	
	$txtBookingNumber = $_POST["txtBookingNumber"];	
	$drpVehicleNumber = $_POST["drpVehicleNumber"];	
	$drpDriverName = $_POST["drpDriverName"];	
	$drpGraderName = $_POST["drpGraderName"];	
    $txtBookingDate = $_POST["txtBookingDate"];	
    $drpAgentName = $_POST["drpAgentName"];	
    $txtFarmLocation = $_POST["txtFarmLocation"];	
    $txtRemarks = $_POST["txtRemarks"];	
    $txtBookingCount = $_POST["txtBookingCount"];	
    $txtBookingWeight = $_POST["txtBookingWeight"];	
    $txtBookingRate = $_POST["txtBookingRate"];			

	$bodyContent = array(
		"Species"=>$drpSpecies,
		"BookingStationID"=>$drpBookingStationId,
		"PurchaseCategoryID"=>$drpPurchaseType,
		"BookingNumber"=>$txtBookingNumber,
		"VehicalID"=>$drpVehicleNumber,
		"DriverID"=>$drpDriverName,
		"GraderID"=>$drpGraderName,
		"BookingDate"=>$txtBookingDate,
		"AgentID"=>$drpAgentName,
		"FarmAddress"=>$txtFarmLocation,
		"Remarks"=>$txtRemarks,
        "BookingCount"=>$txtBookingCount,
        "BookingWeight"=>$txtBookingWeight,
        "BookingRate"=>$txtBookingRate,
        "BookingID"=>$BookingID
    );
    
	$resPostClearingAgentDetailsAPI = CallAPI($apiPost, "POST", "", $bodyContent);
	$resPost = $resPostClearingAgentDetailsAPI->Data; 
	
	if(isset($resPost->Error)){
		$Status = "Error has Occurred";
		$_SESSION['Error'] = 'T';
	}else{
		$Status = $resPost->Msg;
		$_SESSION['Msg'] = 'T';
	}
	
		$url = "ClearingAgentForm?id=".$ID;
	
	//header("Location: $url");    
}

// function funPrintValue($key)
// {
//     // return isset($key) && !empty($key) ? $key :  "";
//     if (isset($key) && !empty($key)) {
//         echo  $key;
//     } else {
//         echo "";
//     }
// }

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>

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
                                <form id="frmCreateBookings">
                                    <div class="row">


                                        <div class="form-group col-sm-3">
                                            <label for="drpSpecies" class="form-label">Species <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpSpecies" id="drpSpecies" >
                                                <option value=""> Select</option>
                                                <?php foreach ($resSpecies as $Item) { ?>
                                                    <option  value="<?php echo $Item->ShortCode; ?>" 
                                                        <?php 
                                                            if(isset($resGetSingleBookings->Species) && !empty($resGetSingleBookings->Species)) {
                                                                echo ($resGetSingleBookings->Species == $Item->ShortCode ? "selected" : "" );
                                                            }
                                                        ?>
                                                    > <?php echo $Item->SpeciesName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                       
                                        <div class="form-group col-sm-3">
                                            <label for="drpBookingStationId" class="form-label">Booking Station<span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpBookingStationId" 
                                                id="drpBookingStationId">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPurchaseCenters as $Item) { ?>
                                                    <option value="<?php echo $Item->PurchaseStationID; ?>"
                                                        <?php
                                                            if (isset($resGetSingleBookings->BookingStationID) && !empty($resGetSingleBookings->BookingStationID)) {
                                                                echo ($resGetSingleBookings->BookingStationID == $Item->PurchaseStationID ? "selected" : "");
                                                            }
                                                        ?>
                                                    > <?php echo $Item->StationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpPurchaseType" class="form-label">Purchase Type <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpPurchaseType" id="drpPurchaseType" >
                                                <option value=""> Select</option>
                                                <?php foreach ($resPurchaseCategory as $Item) { ?>
                                                    <option value="<?php echo $Item->RmCategoryID; ?>"
                                                        <?php
                                                            if (isset($resGetSingleBookings->PurchaseCategoryID) && !empty($resGetSingleBookings->PurchaseCategoryID)) {
                                                                echo ($resGetSingleBookings->PurchaseCategoryID == $Item->RmCategoryID ? "selected" : "");
                                                            }
                                                        ?>
                                                        <?php // echo ($resGetSingleBookings->PurchaseCategoryID == $Item->RmCategoryID ? "selected" : "") ?>
                                                    > <?php echo $Item->RmCategoryName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                      

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingNumber" class="form-label">Booking Number <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBookingNumber" 
                                                name="txtBookingNumber" placeholder="Enter Booking Number" 
                                                value="<?php echo  isset($resGetSingleBookings->BookingNumber) && !empty($resGetSingleBookings->BookingNumber) ? $resGetSingleBookings->BookingNumber : ""  ?>" />
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label for="drpVehicleNumber" class="form-label">Vehicle Number <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpVehicleNumber" id="drpVehicleNumber" >
                                                <option value=""> Select</option>
                                                <?php foreach ($resVehicles as $Item) { ?>
                                                    <option value="<?php echo $Item->VehicalID; ?>" 
                                                        <?php
                                                            if (isset($resGetSingleBookings->VehicalID) && !empty($resGetSingleBookings->VehicalID)) {
                                                                echo ($resGetSingleBookings->VehicalID == $Item->VehicalID ? "selected" : "");
                                                            }
                                                        ?>
                                                    > <?php echo $Item->VehicalNumber; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpDriverName" class="form-label">Driver Name <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpDriverName" id="drpDriverName" >
                                                <option value=""> Select</option>
                                                <?php foreach ($resDrivers as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>" 
                                                        <?php
                                                            if (isset($resGetSingleBookings->DriverID) && !empty($resGetSingleBookings->DriverID)) {
                                                                echo ($resGetSingleBookings->DriverID == $Item->UserID ? "selected" : "");
                                                            }
                                                        ?>
                                                    > <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-sm-3">
                                            <label for="drpGraderName" class="form-label">Grader Name <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpGraderName" id="drpGraderName" >
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmGraders as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>"
                                                        <?php
                                                            if (isset($resGetSingleBookings->GraderID) && !empty($resGetSingleBookings->GraderID)) {
                                                                echo ($resGetSingleBookings->GraderID == $Item->UserID ? "selected" : "");
                                                            }
                                                        ?>
                                                > <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingDate" class="form-label">Booking Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtBookingDate" name="txtBookingDate"
                                                placeholder="" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" >
                                        </div>


                                        <div class="form-group col-sm-3">
                                            <label for="drpAgentName" class="form-label">Agent Name <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpAgentName" id="drpAgentName" >
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmAgents as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>" 
                                                        <?php
                                                            if (isset($resGetSingleBookings->AgentID) && !empty($resGetSingleBookings->AgentID)) {
                                                                echo ($resGetSingleBookings->AgentID == $Item->UserID ? "selected" : "");
                                                            }
                                                        ?>
                                                    > <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFarmLocation" class="form-label">Farm Location <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtFarmLocation" name="txtFarmLocation" 
                                                placeholder="Enter Farm Location" 
                                                value="<?php echo isset($resGetSingleBookings->FarmAddress) && !empty($resGetSingleBookings->FarmAddress) ? $resGetSingleBookings->FarmAddress : "" ?>"
                                                />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpBillName" class="form-label">Bill Name <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpBillName" id="drpBillName" >
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmBillName as $Item) { ?>
                                                        <option value="<?php echo $Item->SupplierID; ?>" 
                                                            <?php
                                                            if (isset($resGetSingleBookings->SupplierID) && !empty($resGetSingleBookings->SupplierID)) {
                                                                echo ($resGetSingleBookings->SupplierID == $Item->SupplierID ? "selected" : "");
                                                            }
                                                            ?>
                                                        > <?php echo $Item->SupplierName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtAdditionDeduction" class="form-label">Addition/Deduction Per Kg <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtAdditionDeduction" name="txtAdditionDeduction" 
                                                placeholder="Enter Addition Deduction" 
                                                value="<?php echo isset($resGetSingleBookings->AdditionDeduction) && !empty($resGetSingleBookings->AdditionDeduction) ? $resGetSingleBookings->AdditionDeduction : "" ?>"
                                                />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50" id="txtRemarks" 
                                                placeholder="Enter Remarks(Optional)"  value="<?php echo isset($resGetSingleBookings->Remarks) && !empty($resGetSingleBookings->Remarks) ? $resGetSingleBookings->Remarks : "" ?>"
                                            ></textarea>
                                        </div>


                                    </div>

                                    <hr>


                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpVariety" class="form-label">Variety</label>
                                            <select class="drpSelect" name="drpVariety" id="drpVariety" >
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmVariety as $Item) { ?>
                                                        <option value="<?php echo $Item->VarietyID; ?>" 
                                                            <?php
                                                            if (isset($resGetSingleBookings->VarietyID) && !empty($resGetSingleBookings->VarietyID)) {
                                                                echo ($resGetSingleBookings->VarietyID == $Item->VarietyID ? "selected" : "");
                                                            }
                                                            ?>
                                                        > <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingCount" class="form-label">Booking Count </label>
                                            <input type="number" class="form-control" id="txtBookingCount"
                                                name="txtBookingCount" placeholder="Enter Booking Count" >
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingWeight" class="form-label">Booking Weight(Kgs) </label>
                                            <input type="text" class="form-control" id="txtBookingWeight"
                                                name="txtBookingWeight" placeholder=" Enter Booking Weight(Kgs)" >
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingRate" class="form-label">Booking Rate</label>
                                            <input type="text" class="form-control" id="txtBookingRate" name="txtBookingRate"
                                                placeholder="Enter Booking Rate" >
                                        </div>

                                    </div>
                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $BookingID ?>" /> 
                                        <button class="btn btn-primary" name="submit" id="submit" type="submit">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="divDataList">
                            <?php                            
                                if(isset($resGetSingleBookingsList) && !empty($resGetSingleBookingsList)) { 
                            ?>
                            <div class="card">
                                <div class="card-body">
                                    
                                    <table id="tblDataTable" class="table table-hover table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>S.No</th>	
                                                <!-- <th>Booking Break ID </th> -->
                                                <th>Count</th>	
                                                <th>Weight</th>	
                                                <th>Rate</th>
                                                <th>Actions</th>    
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach($resGetSingleBookingsList as $item) { ?>
                                            <tr>
                                                <td><?php  echo $i ?></td>
                                                <!-- <td><?php // echo $item->BookingBrkUpID ?></td> -->
                                                <td><?php  echo $item->Count ?></td>
                                                <td><?php  echo $item->Weight ?></td>
                                                <td><?php  echo $item->Rate ?></td>	
                                                <td>
                                                    <button type="button" id="btnDelete" name="btnDelete" disabled>Delete</button>
                                                </td>    
                                            </tr>
                                            <?php $i++; } ?>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                            <?php } ?>
                        </div>


                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . '/includes/common-js.php' ?>



<!-- Required datatable js -->
<script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

		<script>
            function fnShowDataTable() {
                var table = $('#tblDataTable').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true
                });
            }
        </script>
		<script>

            $(document).ready(function(){
                // $.ajax({
                //     url:'ajax/AjaxAddBooking.php',
                //     type:'POST',
                //     data:{
                //         hdnSaveType: $("#hdnSaveType").val(),
                //         drpSpecies: $("#drpSpecies").val(),
                //         drpBookingStationId: $("#drpBookingStationId").val(),
                //         drpPurchaseType: $("#drpPurchaseType").val(),
                //         txtBookingNumber: $("#txtBookingNumber").val(),
                //         drpVehicleNumber: $("#drpVehicleNumber").val(),
                //         drpDriverName: $("#drpDriverName").val(),
                //         drpGraderName: $("#drpGraderName").val(),
                //         txtBookingDate: $("#txtBookingDate").val(),
                //         drpAgentName: $("#drpAgentName").val(),
                //         txtFarmLocation: $("#txtFarmLocation").val(),
                //         txtRemarks: $("#txtRemarks").val(),
                //         txtBookingCount: $("#txtBookingCount").val(),
                //         txtBookingWeight: $("#txtBookingWeight").val(),
                //         txtBookingRate: $("#txtBookingRate").val(),
                //     },
                //     success:function(data){
                //         $("#divDataList").html(data);
                //         fnShowDataTable()
                //         return false;
                //     }
                // });
            });
			
			// $(document).ready(function(){
				$("#frmCreateBookings").validate({
					onkeyup: true,
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
						drpSpecies: { required: true, selectcheck: true },
						drpBookingStationId: { required: true, selectcheck: true },
                        drpPurchaseType: { required: true, selectcheck: true },
                        txtBookingNumber: { required: true, noSpace: true },
                        drpVehicleNumber: { required: true, selectcheck: true },
                        drpDriverName: { required: true, selectcheck: true },
                        drpGraderName: { required: true, selectcheck: true },
                        txtBookingDate: { required: true, noSpace: true },
                        drpAgentName: { required: true, selectcheck: true },
                        txtFarmLocation: { required: true, noSpace: true },
                        drpBillName: { required: true, selectcheck: true },
                        txtAdditionDeduction: { required: true, noSpace: true },
                        //txtRemarks: { required: true, noSpace: true },

                        // txtBookingCount: { required: true, noSpace: true },
                        // txtBookingWeight: { required: true, noSpace: true },
                        txtBookingRate: {  number: true, noSpace: true }						
					},
					errorPlacement: function () {
						return false;  // suppresses error message text
					},
					submitHandler: function () {
                        
                        var isInsert = '<?php echo isset($BookingID) ?>';
                        if(isInsert.length > 0) {
                            $.ajax({
                                url:'ajax/AjaxAddBooking.php',
                                type:'POST',
                                data:{
                                    hdnSaveType: $("#hdnSaveType").val(),
                                    drpSpecies: $("#drpSpecies").val(),
                                    drpBookingStationId: $("#drpBookingStationId").val(),
                                    drpPurchaseType: $("#drpPurchaseType").val(),
                                    txtBookingNumber: $("#txtBookingNumber").val(),
                                    drpVehicleNumber: $("#drpVehicleNumber").val(),
                                    drpDriverName: $("#drpDriverName").val(),
                                    drpGraderName: $("#drpGraderName").val(),
                                    txtBookingDate: $("#txtBookingDate").val(),
                                    drpAgentName: $("#drpAgentName").val(),
                                    txtFarmLocation: $("#txtFarmLocation").val(),
                                    txtRemarks: $("#txtRemarks").val(),
                                    txtBookingCount: $("#txtBookingCount").val(),
                                    txtBookingWeight: $("#txtBookingWeight").val(),
                                    txtBookingRate: $("#txtBookingRate").val(),
                                },
                                success:function(data){
                                    $("#divDataList").html(data);
                                    fnShowDataTable()
                                    return false;
                                }
                            });
                        } else {
                            return true;
                        }                        
					}
				});
			// });
		</script>

        

</body>

</html>