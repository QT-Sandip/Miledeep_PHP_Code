<?php
$PageTitle = "View Stock";
$PageTitleFolder = "ColdStore";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$UnitID = $_SESSION['UnitID'];
$AccessType = $_SESSION['AccessType'];

if ($AccessType != 1) {
	//	$SQry="where ProcessingStationID=$UnitID";

}

$SqlDeptList = "SELECT * FROM RmPurchaseStations";
$StmtDept = $iconn->query($SqlDeptList);

$SqlColdstore = "SELECT * FROM `CsStores` $SQry";
$StmtColdstore = $iconn->query($SqlColdstore);

$SqlTenant = "SELECT * FROM `CsTenant`";
$StmtTenant = $iconn->query($SqlTenant);

//$SqlPoData="SELECT * FROM `ScrmPoData` where PoStatus = 1 order by PoID  DESC  limit 100";
$SqlPoData = "SELECT * FROM `ScrmPoData` as CPD  
 left join ScrmShipmentDetailsBreif as SDB on CPD.PoID=SDB.PiID

  WHERE  (TransportedDate='' or TransportedDate is null) order by PoID DESC"; //ProductionStartedDate!=''and
$StmtPoData = $iconn->query($SqlPoData);


$SqlVarietyList = "SELECT * FROM `ScrmPIVarietyList` ORDER by ABS(ShortName)";
$StmtVarietyList = $iconn->query($SqlVarietyList);


$SqlPackingStyles = "SELECT * FROM `AdminPackingStyles` ORDER by Uom,ABS(PackingStyle)";
$StmtPacking = $iconn->query($SqlPackingStyles);


$GeneralOptionsQuery = "SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='CS' ORDER BY SortOrder asc";
$GeneralOptionsExec = $iconn->query($GeneralOptionsQuery);
$ARY_OPTIONS = array();
while ($ROW = $GeneralOptionsExec->FETCH_ARRAY()) {
	$ARY_OPTIONS[] = $ROW;

}


?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Jlsf/User.css" rel="stylesheet" type="text/css" />



<head>

	<?php include($IncludesPath . "title-meta.php"); ?>

	<!-- DataTables -->
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
		rel="stylesheet" type="text/css" />
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
		rel="stylesheet" type="text/css" />
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css"
		rel="stylesheet" type="text/css" />

	<!-- Responsive datatable examples -->
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
		rel="stylesheet" type="text/css" />


	<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css"
		rel="stylesheet">
	<link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css"
		rel="stylesheet">
	<link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css"
		rel="stylesheet" />
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
										<!--  <div class="form-group col-sm-3" >
				  <label for="exampleInputEmail1">Station</label>
				<select class="form-control select2" name="UnitID" id="UnitID">
				<?php foreach ($ARY_OPTIONS as $value) {
					if ($value['OptionType'] == 'CS_STATIONS') {
						$OPTIONS = $value['Name'];
						$VALUE = $value['CsOptID'];
						?>
			 <option <?php if ($UNIT == $VALUE) { ?> selected="selected" <?php } ?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php }
				} ?>
				<select>
			</div>-->

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">ColdStores</label>
											<select required class="form-control select2" name="ColdstoreID"
												id="ColdstoreID">
												<option value="">All</option>
												<?php while ($RowColdstore = $StmtColdstore->fetch_array()) { ?>
													<option value="<?php echo $RowColdstore['StoreID'] ?>">
														<?php echo $RowColdstore['StoreName'] ?>
													</option>

												<?php } ?>

											</select>
										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Stock belongs to</label>
											<select required class="form-control select2" name="TenantID" id="TenantID">
												<option value="">All </option>
												<?php while ($RowTenant = $StmtTenant->fetch_array()) { ?>
													<option value="<?php echo $RowTenant['TenantID'] ?>">
														<?php echo $RowTenant['TenantName'] ?>
													</option>

												<?php } ?>


											</select>
										</div>


										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputEmail1">Po No</label>
												<select class="form-control select2" name="PoID" id="PoID">
													<option Value=""> Select</option>
													<?php while ($RowPoData = $StmtPoData->fetch_array()) { ?>

														<option value="<?php echo $RowPoData['PoID']; ?>">
															<?php echo $RowPoData['PoNo']; ?>
														</option>
													<?php } ?>
												</select>
											</div>
										</div>

										<div class="form-group col-sm-3">

											<label for="exampleInputEmail1">Variety</label>
											<input hidden type="text" id="PoGradeId" name="PoGradeId"
												class="form-control hidden" value="">
											<select required class="form-control select2" name="VarietyID"
												id="VarietyID">
												<option value="">Select Variety</option>
												<?php while ($RowVData = $StmtVarietyList->fetch_array()) { ?>
													<option value="<?php echo $RowVData['VarietyID'] ?>">
														<?php echo $RowVData['ShortName'] . ' - ' . $RowVData['TypeProduct'] ?>
													</option>

												<?php } ?>
											</select>
										</div>


										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Grade</label>
											<select required class="form-control select2" name="GradeID" id="GradeID">
												<option value="">Select Grade</option>
												<?php foreach ($ARY_OPTIONS as $value) {
													if ($value['OptionType'] == 'GRADES') {
														$OPTIONS = $value['Name'];
														$VALUE = $value['Name']; ?>

														<option value="<?php echo $VALUE ?>">
															<?php echo $OPTIONS ?>
														</option>
													<?php }
												} ?>

												<select>
										</div>






										<div class="form-group col-sm-3">
											<label for="exampleInputPassword1">Packing Style</label>


											<select required class="form-control select2" name="PackingStyleID"
												id="PackingStyleID">
												<option value="">Select Packing Style</option>
												<?php while ($RowPAcking = $StmtPacking->fetch_array()) { ?>
													<option value="<?php echo $RowPAcking['PCKID'] ?>">
														<?php echo $RowPAcking['PackingStyle'] ?>
													</option>

												<?php } ?>

											</select>
										</div>


										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Packing Type</label>
											<select class="form-control select2" name="PackingTypeID"
												id="PackingTypeID">
												<?php foreach ($ARY_OPTIONS as $value) {
													if ($value['OptionType'] == 'PACKING_TYPE') {
														$OPTIONS = $value['Name'];
														$VALUE = $value['Name']; ?>

														<option value="<?php echo $VALUE ?>">
															<?php echo $OPTIONS ?>
														</option>
													<?php }
												} ?>
												<select>
										</div>
										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Product Category</label>
											<select class="form-control select2" name="ProductCategoryID"
												id="ProductCategoryID">
												<?php foreach ($ARY_OPTIONS as $value) {
													if ($value['OptionType'] == 'PRODUCT_CATEGORY') {
														$OPTIONS = $value['Name'];
														$VALUE = $value['Name']; ?>

														<option value="<?php echo $VALUE ?>">
															<?php echo $OPTIONS ?>
														</option>
													<?php }
												} ?>



												<select>
										</div>
										<!--		<div class="col-lg-3">
													<div class="form-group">
													<label for="exampleInputEmail1">Ab Status </label>
													<select class="form-control select2" name="AbStatus" id="AbStatus">
															
													<option value="">Ab Status</option>

															<?php while ($DataRow = $StmtDept->fetch_array()) { ?>	
				 
												<option value="<?php echo $DataRow['PurchaseStationID']; ?>" >
												<?php echo $DataRow['StationName']; ?></option>
												<?php } ?>
														</select>
													</div>
												</div>
												-->



										<div align="center" class="col-lg-12">

											<button id="list" align="center" type="button"
												class="btn btn-primary waves-effect waves-light">
												Search <i class="ri-arrow-right-line align-middle ml-2"></i>
											</button>
										</div>





									</div>
									<!-- end row -->
								</div>
							</div>
						</div>

						<!-- end col -->
					</div> <!-- end row -->

					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body" id="EmpList">
									<h4 class="card-title">Stock Details</h4>


								</div> <!-- end card body-->
							</div> <!-- end card -->
						</div><!-- end col-->
					</div>
					<!-- end row-->


				</div> <!-- end card body-->
			</div> <!-- end card -->
		</div><!-- end col-->
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


	<!-- Required datatable js -->

	<?php include($IncludesPath . "NewScripts.php"); ?>


</body>

</html>

<script>
	$('document').ready(function () {
		//getData();

		$(document).on('click', '#list', function (e) {
			e.preventDefault();

			getData()
		});



		$('#print').on('click', function () {
			var MONTH = $('#MONTH').val();
			var YEAR = $('#YEAR').val();
			var UNIT = $('#UNIT').val();
			window.open('../Accounts/LotReportPrint.php?Y=' + YEAR + '&M=' + MONTH + '&U=' + UNIT);


			//alert('print');
		});
	});


	function getData() {

		var UnitID = $('#UnitID').val();
		var PoID = $('#PoID').val();
		var VarietyID = $('#VarietyID').val();
		var GradeID = $('#GradeID').val();
		var PackingStyleID = $('#PackingStyleID').val();
		var PackingTypeID = $('#PackingTypeID').val();
		var ProductCategoryID = $('#ProductCategoryID').val();
		var AbStatus = $('#AbStatus').val();
		var ColdstoreID = $('#ColdstoreID').val();
		var TenantID = $('#TenantID').val();

		//alert(YEAR);


		$.ajax({
			url: 'Ajax/AjaxStockSelect.php',
			type: 'POST',
			data: {
				'PoID': PoID, 'VarietyID': VarietyID,
				'GradeID': GradeID, 'PackingStyleID': PackingStyleID,
				'PackingTypeID': PackingTypeID,
				'ProductCategoryID': ProductCategoryID, 'AbStatus': AbStatus,
				'TenantID': TenantID, 'ColdstoreID': ColdstoreID, 'Type': 1
			},
			success: function (data) {
				showData(data);

			}
		});

	}

</script>

<script>



	function showData(data) {

		$("#EmpList").html(data);

		var table = $('#key-datatable').DataTable({
			scrollY: "600px",
			scrollX: true,
			//sScrollX: "100%",
			sScrollXInner: "150%",
			// scrollCollapse:true,
			paging: true,
			destroy: true,
			fixedColumns: true,
			bSort: false,
			"bStateSave": true,
			"fnStateSave": function (oSettings, oData) {
				localStorage.setItem('key-datatable', JSON.stringify(oData));
			},
			"fnStateLoad": function (oSettings) {
				return JSON.parse(localStorage.getItem('key-datatable'));
			}
		});
	}

</script>