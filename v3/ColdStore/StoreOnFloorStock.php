<?php
$PageTitle = "Floor Stock";
$PageTitleFolder = "Cold Store";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$UnitID = $_SESSION['UnitID'];
$AccessType = $_SESSION['AccessType'];

if ($AccessType != 1) {
	$SQry = "where ProcessingStationID=$UnitID";
}


$GeneralOptionsQuery = "SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='CS' ORDER BY SortOrder asc";
$GeneralOptionsExec = $iconn->query($GeneralOptionsQuery);
$ARY_OPTIONS = array();
while ($ROW = $GeneralOptionsExec->FETCH_ARRAY()) {
	$ARY_OPTIONS[] = $ROW;
}

$SqlColdstore = "SELECT * FROM `CsStores` ";
$StmtColdstore = $iconn->query($SqlColdstore);

$SqlTenant = "SELECT * FROM `CsTenant`";
$StmtTenant = $iconn->query($SqlTenant);


$SqlVarietyList = "SELECT * FROM `ScrmPIVarietyList` ORDER by ABS(ShortName)";
$StmtVarietyList = $iconn->query($SqlVarietyList);


$SqlPackingStyles = "SELECT * FROM `AdminPackingStyles` ORDER by Uom,ABS(PackingStyle)";
$StmtPacking = $iconn->query($SqlPackingStyles);


?>

<!doctype html>
<html lang="en">

<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>

	<!-- DataTables -->
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

	<!-- Responsive datatable examples -->
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />


	<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
	<link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
	<link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
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

					<?php include($IncludesPath . "page-title.php");
					include "PopUpTable.php";
					?>

					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">

									<div class="row">

										<div class="col-md-3 mb-2">
											<label for="exampleInputEmail1">ColdStores</label>
											<select required class="form-control select2" name="ColdstoreID" id="ColdstoreID">
												<option value="">All</option>
												<?php while ($RowColdstore = $StmtColdstore->fetch_array()) { ?>
													<option value="<?php echo $RowColdstore['StoreID'] ?>">
														<?php echo $RowColdstore['StoreName'] ?>
													</option>
												<?php } ?>
											</select>
										</div>

										<div class="col-md-3 mb-2">
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

										<div class="col-md-3 mb-2">

											<label for="exampleInputEmail1">Variety</label>
											<input hidden type="text" id="PoGradeId" name="PoGradeId" class="form-control hidden" value="">
											<select required class="form-control select2" name="VarietyID" id="VarietyID">
												<option value="">Select Variety</option>
												<?php while ($RowVData = $StmtVarietyList->fetch_array()) { ?>
													<option value="<?php echo $RowVData['VarietyID'] ?>">
														<?php echo $RowVData['ShortName'] . ' - ' . $RowVData['TypeProduct'] ?>
													</option>

												<?php } ?>

											</select>
										</div>


										<div class="col-md-3 mb-2">
											<label for="exampleInputEmail1">Grade</label>
											<select required class="form-control select2" name="GradeID" id="GradeID">
												<option value="">All</option>
												<?php foreach ($ARY_OPTIONS as $value) {
													if ($value['OptionType'] == 'GRADES') {
														$OPTIONS = $value['Name'];
														$VALUE = $value['Name'];
												?>

														<option value="<?php echo $VALUE ?>">
															<?php echo $OPTIONS ?>
														</option>
												<?php }
												}
												?>

											<select>
										</div>


										<div class="col-md-3 mb-2">
											<label for="exampleInputPassword1">Packing Style</label>
											<select required class="form-control select2" name="PackingStyleID" id="PackingStyleID">
												<option value="">All</option>
												<?php while ($RowPAcking = $StmtPacking->fetch_array()) { ?>
													<option value="<?php echo $RowPAcking['PckID'] ?>">
														<?php echo $RowPAcking['PackingStyle'] ?>
													</option>

												<?php } ?>

											</select>
										</div>

										<div class="col-md-3 mb-2">
											<label for="list" class="invisible" style="display: block;">Label</label>
											<button id="list" type="button" class="btn btn-primary waves-effect waves-light">
												Search <i class="ri-arrow-right-line align-middle ml-2"></i>
											</button>
										</div>
									</div>
								</div>
								<!-- end row -->
							</div>
						</div>
					</div>

					<!-- end col -->
				</div> <!-- end row -->
				<div class="container-fluid">
					<div class="row">
						<div class="col-12">
							<div id="ShowDataTable">
								<!-- <h4 class="card-title">Purchased Lot - List</h4> -->


							</div> <!-- end card body-->

						</div><!-- end col-->
					</div>
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



	<?php include($IncludesPath . "vendor-scripts.php");
	include($IncludesPath . "NewScripts.php");
	include($IncludesPath . "ShowDataTable.php");

	?>

</body>

</html>

<script>
	$('document').ready(function() {

		$(document).on('click', '#list', function(e) {
			e.preventDefault();
			getData()
		});

		$(document).on('click', '.Delete', function(e) {
			e.preventDefault();

			var StockID = $(this).data('id');
			var r = confirm("Are You Sure Do You Want To Delete?");
			if (r == true) {
				$.ajax({
					type: 'POST',
					url: 'Ajax/AjaxAddStock.php',
					data: {
						'PID': StockID,
						'type': 'PALLET_DELETE'
					},

					success: function(data) {

						var obj = JSON.parse(JSON.stringify(data));
						if (obj.error == null) {
							alert("Stock Deleted Successfully");
							//location.reload();
							getData();
						} else {
							alert(obj.error);
						}
					}
				});
			} else {}
		});

		$(document).on('click', '#Update', function(e) {

			var StockID = $('#Update').val();
			var RowID = $('#PopUpRackId').val();

			var r = confirm("Are You Sure Do You Want To Update?");
			if (r == true) {
				$.ajax({
					url: 'Ajax/AjaxAddtoPallet.php',
					type: 'POST',
					data: {
						'StockID': StockID,
						'RowID': RowID,
						'Type': 'AddToPallet'
					},
					success: function(data) {
						getData();
					}
				});
			} else {}
		});

		$(document).on('click', '.AddToPallet', function(e) {
			e.preventDefault();

			var StockID = $(this).data('id');
			$('#Update').val(StockID);

			var r = confirm("Are You Sure Do You Want To Add To Existing Pallet ???");
			if (r == true) {

				$.ajax({
					url: 'Ajax/AjaxAttachStockToPallet.php',
					type: 'POST',
					data: {
						'StockID': StockID
					},
					success: function(data) {

						$('.modal-title').text('Place Pallet - ' + StockID);
						$("#PopUpTable").html(data);

						$('#myModal').modal('show');
						//$("#RackId").select2();	
					}
				});
			} else {}
		});
	});


	function getData() {

		var PoGradeId = $('#GradeID').val();
		var VarietyID = $('#VarietyID').val();
		var PackingStyleID = $('#PackingStyleID').val();
		var ColdstoreID = $('#ColdstoreID').val();
		var TenantID = $('#TenantID').val();

		$.ajax({
			url: 'Ajax/AjaxFloorStock',
			type: 'POST',
			data: {
				'PoGradeId': PoGradeId,
				'VarietyID': VarietyID,
				'PackingStyleID': PackingStyleID,
				'TenantID': TenantID,
				'ColdstoreID': ColdstoreID,
				'Type': 1
			},
			success: function(data) {
				showData(data);
			}
		});
	}
</script>