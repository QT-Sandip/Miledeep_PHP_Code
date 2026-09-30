<?php
$PageTitle = "Employee Salaries";
$PageTitleFolder = "HRM";


$Startyear = $year - 1;
$endYear = $year + 1;

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlHrmDept = "SELECT * FROM `HrmDepartments`";
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
<link href="https://serp.silverlights.in/Clients/Jlsf/User.css" rel="stylesheet" type="text/css" />

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
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

					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body ">

									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<!-- <label for="exampleInputEmail1">DEPARTMENT </label>-->
												<select class="form-control select2" name="Dept" id="Dept">

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
												<select class="form-control select2" name="SubDept" id="SubDept">
													<option value="">All Sub Department</option>
													<select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<!--                  <label for="exampleInputEmail1">MONTH </label>-->
												<select class="form-control select2" name="MONTH" id="MONTH">
													<?php
													for ($i = 0; $i < 12; $i++) {
														$time = strtotime(sprintf('%d months', $i), $start);
														$label = date('F', $time);
														$value = date('n', $time);
														echo "<option value='$value'>$label</option>";
													} ?>
													<select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group ">

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
													Search <i class='ri-arrow-right-line align-middle ml-2'>
													</i> </button>

												<!-- <a style="display: none;"id="print"  target="_blank" class="btn btn-success waves-effect waves-light"><span class="glyphicon glyphicon-print"></span> Print</a>-->

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
									<h4 class="card-title">Employee - Salaries</h4>
									<table  id="mytable-data" class="table dt-responsive wrap w-100" >	
									<thead class="thead-light">
								                  <th>Bio ID</th>
								                  <th>Name</th>
								                  <th>No Of WageDays</th>
								                  <th>Basic Salary</th>
								                  <th>HRA</th>
								                  <th>Conveyence</th>
								                  <th>Earned Wages</th>
								                  <th>Incentives</th>
								                  <th>Total Earned Wages</th>
								                  <th>LESS : PF @12%</th>
								                  <th>LESS : ESI @0.75%</th>
								                  <th>Total Deductions</th>
								                  <th>Net Payable</th> 
								                </thead>
								                <tbody></tbody></table>


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
	<?php include("Includes/NewScripts.php"); ?>

</body>

</html>


<script>
	$(function () {
		$('.edit').click(function (e) {
			e.preventDefault();
			$('#edit').modal('show');
			var id = $(this).data('id');
			getRow(id);
		});

		$('.delete').click(function (e) {
			e.preventDefault();
			$('#delete').modal('show');
			var id = $(this).data('id');
			getRow(id);
		});
	});

	function getRow(id) {
		$.ajax({
			type: 'POST',
			url: 'attendance_row',
			data: { id: id },
			dataType: 'json',
			success: function (response) {
				$('#datepicker_edit').val(response.date);
				$('#attendance_date').html(response.date);
				$('#edit_time_in').val(response.time_in);
				$('#edit_time_out').val(response.time_out);
				$('#attid').val(response.attid);
				$('#employee_name').html(response.firstname + ' ' + response.lastname);
				$('#del_attid').val(response.attid);
				$('#del_employee_name').html(response.firstname + ' ' + response.lastname);
			}
		});
	}
</script>


<Script>

	$('document').ready(function () {
		var doa_doc = '<?php echo $DOA_DOC ?>';
		//var doa_doc='<?php echo $COUNTRY ?>';

		//alert(doa_doc);

		$("#DOA_DOC").val(doa_doc);
		$(document).on('change', '#Dept', function (e) {

			e.preventDefault();

			var Dept = $(this).val();
			//var INV_ID=<?php echo $INVOICE_ID ?>;

			//alert(DEPT_CODE);
			$.ajax({
				url: 'Ajax/AjMonthlyAtt.php',
				type: 'POST',
				data: { 'Dept': Dept },
				success: function (data) {
					//console.log(data);
					// var obj = JSON.parse(data);
					$("#SubDept").html(data);
					$("#PACK_SIZE").html('<option value="">All Designation</option> ');
				}
			})
			//return false;



		});


		$(document).on('change', '#REG_NO', function (e) {
			e.preventDefault();
			var REG_NO = $(this).val();
			//var INV_ID=<?php echo $INVOICE_ID ?>;

			//alert(INV_ID);
			$.ajax({
				url: 'Ajax/AjMonthlySalaries.php',
				type: 'POST',
				data: { 'REG_NO': REG_NO, 'INV_ID': INV_ID },
				success: function (data) {
					//console.log(data);
					// var obj = JSON.parse(data);
					$("#PACK_SIZE").html(data);
				}
			})
			//return false;

		});

		$(document).on('click', '#list', function (e) {
			e.preventDefault();
			loadAdvances();
			

			$(document).on('click', '#print', function (e) {
				e.preventDefault();

				var Dept = $('#Dept').val();
				var SubDept = $('#SubDept').val();
				var Month = $('#MONTH').val();
				var Year = $('#YEAR').val();


				//window.open("/Hrm/MonthlyAtttendancePrint.php?Dept=" + Dept + "&SubDept=" + SubDept + "&Month=" + Month + "&Year=" + Year, "_blank");
				window.open("/Hrm/MonthlySalariesPrint.php?Dept=" + Dept + "&SubDept=" + SubDept + "&Month=" + Month + "&Year=" + Year, "_blank");


			});


			
			//return false;



		});




		$('#continuebtn').on('click', function () {
			alert('hi');

			$('form').submit();
		});
	});

	function loadAdvances() {

		var Dept = $('#Dept').val();
		var SubDept = $('#SubDept').val();
		var Month = $('#MONTH').val();
		var Year = $('#YEAR').val();

		$("#print").show();
		
		if ($.fn.DataTable.isDataTable('#mytable-data')) {
            $('#mytable-data').DataTable().destroy();
        }

        table = "";     
        table = $('#mytable-data').DataTable({        	                        
            serverSide: true,
            ajax: {
                url: 'Ajax/AjMonthlySalaries',
                type: 'POST',
                data: { 'Dept': Dept, 'SubDept': SubDept, 'Month': Month, 'Year': Year, 'Type': 'List' },
            },
            columns: [
                { data: 'BioMetricID' },
                { data: 'EmpName' },
                { data: 'PresentDays' },
                { data: 'BasicSalary' },
                { data: 'HRA' },
                { data: 'Conveyence' },
                { data: 'TotalBasicSalary' },
                { data: 'Incentives' },
                { data: 'TotalSalary' },                        
                { data: 'PF' },                        
                { data: 'ESI' },                        
                { data: 'TotalDeductions' },                        
                { data: 'NetPayableSalary' }                        
            ],
            columnDefs: [
                { targets: [2], orderable: false, searchable: false },
                { targets: [3], orderable: false, searchable: false },
                { targets: [4], orderable: false, searchable: false },                

            ],
            paging: true,
            pageLength: 20,
            lengthMenu: [[20,50,100], [20,50,100]],
            scrollX: true,
            scrollY: '400px',
            scrollCollapse: true,
            processing: true,
        });
    }


</script>