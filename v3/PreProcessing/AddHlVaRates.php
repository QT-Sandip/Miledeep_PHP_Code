<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");
include_once ROOT . 'config/api-call.php';

$api = API_HLVA_RATES_LIST;



$RateID = $_GET['ID'];
$PageTitle = "Rates";
$PageTitleFolder = "PreProcessing";

$SqlVaStations = " SELECT * FROM `PpcVAStations` WHERE UnitStatus=1";
$StmtVaStations = $iconn->query($SqlVaStations);

$SqlFinanciers = " SELECT * FROM `RmFinanciers` WHERE Status=1";
$StmtFinanciers = $iconn->query($SqlFinanciers);

$SqlContractor = " SELECT * FROM `PpcContractMaster` WHERE Status=1";
$StmtContractor = $iconn->query($SqlContractor);

//$SqlVariety="SELECT * FROM `ScrmPIVarietyList` WHERE Status=1";
$SqlVariety = "SELECT OptID,Name FROM `PpcOptions` WHERE Value='VARIETY' and Status=1 "; //Depreciated
$SqlVariety = "SELECT VarietyID,ShortName FROM `PpcHLVAVarietyList` WHERE  Status=1";

$StmtVariety = $iconn->query($SqlVariety);

$filterFields = "StationID, Variety, ContractorType, Shift, EffectiveDate";
$sqlFilters = "SELECT $filterFields FROM PpcHLVARatesHistory WHERE ID='$RateID'";
$stmtFilters = $iconn->query($sqlFilters);
$dataFilters = $stmtFilters->fetch_array();

$StationID = $dataFilters['StationID'];
$Variety = $dataFilters['Variety'];
$ContractorType = $dataFilters['ContractorType'];
$fiterShift = $dataFilters['Shift'];
$EffectiveDate = $dataFilters['EffectiveDate'];



?>



<!DOCTYPE html>

<html lang="en">


<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">


	<?php include($IncludesPath . "PreLoader.php"); ?>

	<!-- Begin page -->
	<div id="layout-wrapper">


		<?php
		include($IncludesPath . "topbar.php");
		include($IncludesPath . "sidebar.php");
		?>



		<!-- ============================================================== -->
		<!-- Start right Content here -->
		<!-- ============================================================== -->
		<div class="main-content">

			<div class="page-content">
				<div class="container-fluid">



					<?php include($IncludesPath . "page-title.php"); ?>


					<div class="row">
						<div class="col-12">
							<div class="card">

								<div class="card-body">
									<div class="row">

										<div class="form-group col-sm-3">
											<label for="exampleInputPassword1">Station<span style="color:red">*</span></label>

											<select required class="form-control select2" name="StationID" id="StationID">
												<option value=""> All</option>
												<?php while ($RowStations = $StmtVaStations->fetch_array()) {	?>
													<option Value="<?php echo $RowStations['StationID']; ?>"
													<?php if ($RowStations['StationID'] == $StationID) echo 'selected'; ?>>
														<?php echo $RowStations['UnitName']; ?></option>
												<?php } ?>
											</select>
										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputPassword1">Variety<span style="color:red">*</span> </label>
											<select required class="form-control select2" name="VarietyId" id="VarietyId">
												<option value=""> Select</option>
												<?php while ($RowVariety = $StmtVariety->fetch_array()) {
												?>
													<option Value="<?php echo $RowVariety['VarietyID']; ?>"
													<?php if ($RowVariety['VarietyID'] == $Variety) echo 'selected'; ?>>
														<?php echo $RowVariety['ShortName']; ?></OPTION>
												<?php } ?>
												<select>

										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputPassword1">Supplier Type</label>
											<select class="form-control select2" name="Type" id="Type">
												<option value=""> Select</option>
												<option value="1" <?php if ($ContractorType == "1") echo 'selected'; ?>>Contractor</option>
												<option value="2" <?php if ($ContractorType == "2") echo 'selected'; ?>>Mestri</option>
											</select>
										</div>



										<div class="form-group col-sm-3">
											<label for="exampleInputPassword1">Shift </label>
											<select class="form-control select2" name="Shift" id="Shift">
												<option value=""> Select</option>
												<option value="1"<?php if($fiterShift == "1") echo 'selected'; ?>> Day Shift</option>
												<option value="2"<?php if($fiterShift == "2") echo 'selected'; ?>> Night Shift</option>
												<option value="3"<?php if($fiterShift == "3") echo 'selected'; ?>> Both</option>

											</select>

										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Date</label>
											<input required type="date" class="form-control" name='SelectDate' id="SelectDate" max="<?php echo date("Y-m-d"); ?>" 
											placeholder="Enter  Date" value="<?php echo !empty($EffectiveDate) ? $EffectiveDate : date("Y-m-d"); ?>">

										</div>


									</div>

									<div align="center" class="col-lg-12">
										<button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
											Search <i class="ri-arrow-right-line align-middle ml-2"></i> </button>

									</div>
									<!-- end col -->
								</div> <!-- end row -->

							</div> <!-- end card body-->
						</div> <!-- end card -->


					</div>

					<!-- Main content -->


					<!-- Small boxes (Stat box) -->

					<div id="tdetails" class="table-responsive">
						<div align="center" class="card">

							<div class="card-body">

								<table id="CostingRatesTable" class="table table-bordered table-hover" style="width:50%">
								</table>


							</div>
						</div>


					</div>
					<!-- end row-->

				</div> <!-- container-fluid -->
			</div>
			<!-- End Page-content -->

			<?php include($IncludesPath . "footer.php"); ?>

		</div>
		<!-- end main content-->

	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>

</body>
<script>
	$('document').ready(function() {



		$(document).on('click', '#list', function(e) {
			e.preventDefault();

			getData();
		});


	});
</script>


<script>
	function reply_click() {
		var InvID = $("#SubmitBtn").val();
		var SelectDate = $("#SelectDate").val();
		var IsEdit = '<?php echo $IsEdit ?>';


		var StationID = $('#StationID').val();
		var SupplierType = $('#Type').val();
		var VarietyId = $('#VarietyId').val();
		var Shift = $('#Shift').val();
		var EffectiveDate = $('#SelectDate').val();



		if (StationID == '') {
			alert("Please Select Station");
			return;
		} else if (VarietyId == '') {
			alert("Please Select  Variety");
			return;
		} else if (SupplierType == '') {
			alert("Please Supplier  Type");
			return;
		}

		if (!IsEdit && EffectiveDate == '') {
			alert("Please Date");
			document.getElementById("SelectDate").focus();
			return;
		}


		// 
		var Cost = [];
		var grades = [];
		for (i = 0; i < InvID; i++) {


			var DayCodeID = '#DayCode' + i;
			var GradeID = '#Grade' + i;
			var Grade = $(GradeID + "").text();
			var DayCode = $(DayCodeID + "").val();
			/*alert(DayCodeID);
			alert(DayCode);*/
			Cost.push(DayCode);
			grades.push(Grade);



			if (!Number(DayCode) && DayCode != '-') {
			console.log(DayCode);
				alert('Only Number Allowed for Count ' + Grade);

				document.getElementById("DayCode" + i).focus();

				return;

			}

			if (DayCode == '' || DayCode == 0) {
				alert('Enter Price for  ' + Grade);

				document.getElementById("DayCode" + i).focus();

				return;

			}


		}

		$('#myModal').modal('show');



		$.ajax({
			url: 'Ajax/AjaxPostHLVARates.php',
			type: 'POST',
			data: {
				'IsEdit': IsEdit,
				'Grades': grades,
				'Rates': Cost,
				'StationID': StationID,
				'SupplierType': SupplierType,
				'VarietyId': VarietyId,
				'Shift': Shift,
				'EffectiveDate': EffectiveDate
			},
			success: function(data) {
				if (data == 'Success') {

					// window.location = "index.php?Msg=Costing Rates Updated Successfully";
				} else {
					//window.location = "index.php?Msg=Costing Rates Updated Successfully";

					alert(data);
				}


			}
		});


		$(document).ajaxStart(function() {
			$('#myModal').modal('show');
		});
		$(document).ajaxComplete(function() {
			$('#myModal').modal('hide');
		});
	}
</script>


<script>
	$(document).ready(function() {
		//showData();
		var RateID = '<?php echo $RateID  ?>';
		if (RateID != '') {
			getData();
		}



	});




	function showData(data) {

		$("#ClientList").html(data);

		var table = $('#key-datatable').DataTable({
			scrollY: "600px",
			scrollX: true,
			//sScrollX: "100%",
			sScrollXInner: "150%",
			// scrollCollapse:true,
			paging: true,
			destroy: true,
			fixedColumns: true,
			bSort: true,
			"bStateSave": true,
			"fnStateSave": function(oSettings, oData) {
				localStorage.setItem('key-datatable', JSON.stringify(oData));
			},
			"fnStateLoad": function(oSettings) {
				return JSON.parse(localStorage.getItem('key-datatable'));
			}
		});



	}
</script>


<script>
	function getData() {


		var StationID = $('#StationID').val();
		var SupplierType = $('#Type').val();
		var VarietyId = $('#VarietyId').val();
		var Shift = $('#Shift').val();
		var EffectiveDate = $('#SelectDate').val();
		var RateID = '<?php echo $RateID  ?>';

		/*

		*/

		if (StationID == '') {
			alert("Please Select Station");
			return;
		} else if (VarietyId == '') {
			alert("Please Select  Variety");
			return;
		} else if (SupplierType == '') {
			alert("Please Supplier  Type");
			return;
		}else if($('#Shift').val() == ''){
			alert("Please Select Shift");
			return;
		}


		$.ajax({
			url: 'Ajax/AjaxGetHLVARates.php',
			type: 'POST',
			dataType: "json",
			data: {
				'RateID': RateID,
				'StationID': StationID,
				'SupplierType': SupplierType,
				'VarietyId': VarietyId,
				'Shift': Shift,
				'$EffectiveDate': EffectiveDate
			},
			success: function(data) {
				var JsonData = JSON.parse(JSON.stringify(data));
				$("#CostingRatesTable").html(JsonData.Table);
				$("#SubmitBtn").val(JsonData.Count);
				//showData(JsonData.Table);


			}
		});

	}
</script>