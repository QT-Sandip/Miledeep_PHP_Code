<?php
$PageTitle = "Audit Basic Details";
$PageTitleFolder = "HRM";

/*$year=date('Y');
$Currentyear=2021;
 $Startyear=$year-2;
 $endYear=$year+1;*/
 $Startyear=$Currentyear-1;
$endYear=$Startyear+2;
$Currentyear = date('Y');


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlHrmDept = "SELECT * FROM `HrmDepartments` where DeptID=48";
$StmtHrmDept = $iconn->query($SqlHrmDept);

$start = strtotime('first day of this month');

if (isset($_SESSION['YEAR']) == '') {
	$_SESSION['YEAR'] = date("Y");
	$_SESSION['MONTH'] = date("m");
}

$YEAR = $_SESSION['YEAR'];
$MONTH = $_SESSION['MONTH'];

if ($_GET['MONTH'] != '') {
	$month = $_GET['MONTH'];
} else {

	$month = date("m");
	$month = trim($month);

}

if ($_GET['YEAR'] != '') {
	$year = $_GET['YEAR'];
} else {

	$year = date("Y");

}

?>

<!doctype html>
<html lang="en">



<head>

	<?php include($IncludesPath . "title-meta.php"); ?>

	<!-- DataTables -->
	<?php include("Includes/DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">

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
					<?php include($IncludesPath . "PreLoader.php"); ?>
					<?php include "PopUpTable.php"; ?>


					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body ">

									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<!-- <label for="exampleInputEmail1">DEPARTMENT </label>-->
												<select class="form-control select2" name="DeptID" id="DeptID">

													<?php $AryDeptNames = array(); ?>
													<option value="">All Departments</option>
													<?php if($StmtHrmDept){ while ($RowDept = $StmtHrmDept->fetch_array()) {
														$DeptName = $RowDept['DepartmentName'];
														if (in_array($DeptName, $AryDeptNames)) {

														} else {
															$AryDeptNames[] = $DeptName;


															?>

															<option value="<?php echo $RowDept['DeptID']; ?>">
																<?php echo $RowDept['DepartmentName']; ?>
															</option>
														<?php }
													} } ?>
													<select>
											</div>
										</div>


										<div class="col-lg-3">

											<div class="form-group">
												<!--<label for="exampleInputEmail1">SUB DEPARTMENT</label>-->
												<select class="form-control select2" name="SubDeptID" id="SubDeptID">
													<option value="">All Sub Department</option>
													<select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<!--                  <label for="exampleInputEmail1">MONTH </label>-->
												<select class="form-control select2" name="MONTH" id="MONTH">
													<?php
													// $start = 3;
													$start = strtotime('September 1');
													for ($i = 0; $i < 1; $i++) {
														// for ($i = 0; $i < 1; $i++) {
														$time = strtotime(sprintf('%d months', $i), $start);
														$label = date('F', $time);
														$value = date('n', $time);
														//echo "<option value='$value'>$label</option>";
													} ?>
													<!-- <option value='10'>October</option> -->
													<!-- <option value='11'>November</option> -->
													 <!-- <option value='12'>December</option> -->
													<option value='1'>January</option>
													<option value='2'>February</option>
												<select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group ">
												<!-- <label for="exampleInputEmail1">YEAR </label>-->

												<select class="form-control select2" name="YEAR" id="YEAR">
													<?php
													for ($year; $Startyear <= $Currentyear; $Startyear++) { ?>

														<option <?php if ($Startyear == $Currentyear) { ?> selected="selected"
															<?php } ?> value="<?php echo $Startyear ?>">
															<?php echo $Startyear ?>
														</option>

													<?php } ?>
													<select>
											</div>
										</div>



										<div align="center" class="col-lg-12">
											<div class="form-group ">

												<br>
												<!--<label for="exampleInputEmail1"> </label>-->

												<button id="list" align="center" type="button"
													class="btn btn-primary waves-effect waves-light">
													Search<i class='ri-arrow-right-line align-middle ml-2'> </i>
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

					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body" id="ATT_DATA">
									<!-- <h4 class="card-title">Employee - TimeCard</h4>-->


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
	<?php include($IncludesPath . "leave_register.php"); ?>

	</div>
	<!-- end main content-->

	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>


	<!-- Required datatable js -->
	<script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>

	<script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/js/pages/form-advanced.init.js"></script>


	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
	<!-- Buttons examples -->
	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>

	<!-- Responsive examples -->
	<script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
	<script
		src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

	<!-- Datatable init js -->
	<script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

	<script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

</body>

</html>


<script>





	/*function showData(data){
		
		$("#ATT_DATA").html(data);
				
						//$("#EmpList").html(data);
	 
				var Stable=    $('#scroll-vertical-datatable').DataTable({
			"scrollY":        "450px",
			"scrollCollapse": true,
			"paging":         false,
			"language": {
				"paginate": {
					"previous": "<i class='mdi mdi-chevron-left'>",
					"next": "<i class='mdi mdi-chevron-right'>"
				}
			},
			"drawCallback": function () {
				$('.dataTables_paginate > .pagination').addClass('pagination-rounded');
			},
			buttons: ['copy', 'excel', 'pdf', 'colvis']
		});
		
		Stable.buttons().container()
			.appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
			}*/

</script>


<script>

	function showData(data) {

		$("#ATT_DATA").html(data);

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
			order: [[1, 'desc']],
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
<Script>

	$('document').ready(function () {

		$(document).on('change', '#DeptID', function (e) {
			e.preventDefault();
			var DeptId = $(this).val();
			var Type = 'SubDept';

			$.ajax({
				url: 'Ajax/AjDropDowns',
				type: 'POST',
				data: { 'DeptId': DeptId, 'Type': Type },
				success: function (data) {
					$("#SubDeptID").html(data);
				}
			})


		});



		$(document).on('click', '#list', function (e) {
			e.preventDefault();

			getDetails();




		});



		$(document).on('click', '#BtnAddDetails', function (e) {
			e.preventDefault();

			var BioMetricID = $(this).val();
			var AttStartDate = '#AttStartDate' + BioMetricID;
			var AttEndDate = '#AttEndDate' + BioMetricID;
			var WeekOffStartDate = '#WeekOffStartDate' + BioMetricID;
			var StartShift = '#StartShift' + BioMetricID;
			var ShiftOrder = '#ShiftOrder' + BioMetricID;
			var AttAbsents = '#AttAbsents' + BioMetricID;
			var AttLeave = '#AttLeave' + BioMetricID;
			// var ShiftOrder=$('#ShiftOrder').val();
			//alert(ShiftOrder);




			var AttStartDate = $(AttStartDate + "").val();
			var AttEndDate = $(AttEndDate + "").val();
			var WeekOffStartDate = $(WeekOffStartDate + "").val();
			var StartShift = $(StartShift + "").val();
			var ShiftOrder = $(ShiftOrder + "").val();
			var AttAbsents = $(AttAbsents + "").val();
			var AttLeave = $(AttLeave + "").val();
			var Month = $('#MONTH').val();
			var Year = $('#YEAR').val();
			//alert(ShiftOrder);

			var r = confirm("Are You Sure Do You Want To Submit?");
			if (r == true) {
				$.ajax({
					type: 'POST',
					url: 'Ajax/AjAddAuditBasicDetails.php',
					data: {
						'AttAbsents': AttAbsents,
						'AttLeave': AttLeave,
						'AttStartDate': AttStartDate, 'AttEndDate': AttEndDate, 'WeekOffStartDate': WeekOffStartDate, 'StartShift': StartShift,
						'BioMetricID': BioMetricID, 'ShiftOrder': ShiftOrder, 'Type': 'AddAuditBasicDetails', 'Month': Month, 'Year': Year
					},
					//dataType: 'json',
					success: function (response) {
						var data = JSON.parse(response);
						if (data['sts'] == 'Failed') {
							alert(data['msg']);
							getDetails();

						} else {
							$('#myModal').modal('hide');
							//alert(data['msg']);
							//location.reload();

							getDetails();


						}
					}
				});
			} else { }


		});

		$('#continuebtn').on('click', function () {
			alert('hi');

			$('form').submit();
		});
	});


	function getDetails() {

		var DeptID = $('#DeptID').val();
		var SubDeptID = $('#SubDeptID').val();
		var Month = $('#MONTH').val();
		var Year = $('#YEAR').val();

		if (Month == 1 && Year == 2024) {
			alert('This month is already freezed, Please change the year and try');
			return;
		}

		$("#print").show();



		$.ajax({
			url: 'Ajax/AjAuditBasicDetails',
			type: 'POST',
			data: { 'DeptID': DeptID, 'SubDeptID': SubDeptID, 'Month': Month, 'Year': Year, 'Type': 'List' },
			success: function (data) {
				showData(data);

			}
		})
	}



    $(document).ready(function() {
        $('#YEAR').select2();
        $('#MONTH').select2();
        function updateMonthOptions(selectedYear) {
            const monthSelect = $('#MONTH');
            monthSelect.empty();
            let months = [];
            if (selectedYear === 2025) {

            	/*months = [
				    ["January", 1],
				    ["February", 2],
				    ["March", 3],
				    ["April", 4],
				    ["May", 5],
				    ["June", 6],
				    ["July", 7],
				    ["August", 8],
				    ["September", 9],
				    ["October", 10],
				    ["November", 11],
				    ["December", 12]
				];

		        months.forEach(m => {
		            monthSelect.append(new Option(m[0], m[1]));
		        });*/
            } else if (selectedYear === 2026) {
                months = [

					["July", 7],
				];

		        months.forEach(m => {
		            monthSelect.append(new Option(m[0], m[1]));
		        });
            }
            monthSelect.trigger('change.select2');
        }
        $('#YEAR').on('change', function() {
            const selectedYear = parseInt($(this).val());
            updateMonthOptions(selectedYear);
        });
        const defaultYear = parseInt($('#YEAR').val());
        updateMonthOptions(defaultYear);
    });

</script>
