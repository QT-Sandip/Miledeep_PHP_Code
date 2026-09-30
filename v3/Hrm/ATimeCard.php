<?php
unset($_SESSION['default_dashboard']);
$PageTitle = "Time Card";
$PageTitleFolder = "HRM";

/*$year=date('Y');
$Currentyear=2021;
 $Startyear=$year-2;
 $endYear=$year+1;*/
$Currentyear = date('Y');


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$Startyear=$Currentyear-1;
$endYear=$Startyear+2;
$SqlHrmDept = "SELECT * FROM `HrmDepartments`";
$StmtHrmDept = $iconn->query($SqlHrmDept);

$start = strtotime('first day of this month');
$endYear=$endYear-1;

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
													<?php while ($RowDept = $StmtHrmDept->fetch_array()) {
														$DeptName = $RowDept['DepartmentName'];
														if (in_array($DeptName, $AryDeptNames)) {

														} else {
															$AryDeptNames[] = $DeptName;


															?>

															<option value="<?php echo $RowDept['DeptID']; ?>">
																<?php echo $RowDept['DepartmentName']; ?>
															</option>
														<?php }
													} ?>
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
													<option value='2'>February</option>
													<option value='1'>January</option>
												<select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group ">
												<!-- <label for="exampleInputEmail1">YEAR </label>-->

												<select class="form-control select2" name="YEAR" id="YEAR">
													<?php
													for ($Startyear; $Startyear <= $endYear; $Startyear++) { ?>

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
													Search <i class='ri-arrow-right-line align-middle ml-2'> </i>
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
									<h4 class="card-title">Employee - TimeCard</h4>


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



	$(document).on('click', '#TimecardPopup', function (e) {
		e.preventDefault();

		var EmpID = $(this).data('id');
		var Year = $('#YEAR').val();
		var Month = $('#MONTH').val();


		$.ajax({
			url: 'Ajax/AjaxTimeCardPopUp.php',
			type: 'POST',
			data: { 'EmpID': EmpID, 'Year': Year, 'Month': Month },
			success: function (data) {

				$('.modal-title').text('Time Card');
				$("#PopUpTable").html(data);

				$('#myModal').modal('show');

			}
		});

	});

	function showData(data) {

		$("#ATT_DATA").html(data);

		//$("#EmpList").html(data);

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
		//var doa_doc='<?php echo $DOA_DOC ?>';
		//var doa_doc='<?php echo $COUNTRY ?>';

		//alert(doa_doc);

		//$("#DOA_DOC").val(doa_doc);
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

			var DeptID = $('#DeptID').val();
			var SubDeptID = $('#SubDeptID').val();
			var Month = $('#MONTH').val();
			var Year = $('#YEAR').val();

			$("#print").show();



			$.ajax({
				url: 'Ajax/AjATimeCard',
				type: 'POST',
				data: { 'DeptID': DeptID, 'SubDeptID': SubDeptID, 'Month': Month, 'Year': Year, 'Type': 'List' },
				success: function (data) {
					showData(data);

				}
			})
			//return false;



		});




		$('#continuebtn').on('click', function () {
			alert('hi');

			$('form').submit();
		});
	});

    $(document).ready(function() {
        $('#YEAR').select2();
        $('#MONTH').select2();
        /*function updateMonthOptions(selectedYear) {
            const monthSelect = $('#MONTH');
            monthSelect.empty();
            if (selectedYear === 2025) {
				monthSelect.append(new Option("April", 4));
				monthSelect.append(new Option("May", 5));
				monthSelect.append(new Option("June", 6));
				monthSelect.append(new Option("July", 7));
				monthSelect.append(new Option("August", 8));
				monthSelect.append(new Option("September", 9));
				monthSelect.append(new Option("October", 10));
				monthSelect.append(new Option("November", 11));
                monthSelect.append(new Option("December", 12));
            } else if (selectedYear === 2026) {
            	monthSelect.append(new Option("Januvary", 12));
            }
            monthSelect.trigger('change.select2');
        }*/
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
