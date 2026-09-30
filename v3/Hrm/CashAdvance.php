<?php

$PageTitle = "Remarks";
$PageTitleFolder = "HRM";
$Currentyear = date('Y');


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


$SqlDepartments = "SELECT * FROM `HrmDepartments`";
$StmtDepartments = $iconn->query($SqlDepartments);


$Startyear = $Currentyear - 1;
$endYear = $Currentyear + 1;

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
					<?php include "PopUpTable.php"; ?>



					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">

									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<!-- <label for="exampleInputEmail1">DEPARTMENT </label>-->
												<select class="form-control select2" name="Dept" id="Dept">

													<?php $AryDepartmentNames = array(); ?>
													<option value="">All Departments</option>
													<?php while ($RowDepartment = $StmtDepartments->fetch_array()) {
														$DeptName = $RowDepartment['DepartmentName'];
														if (in_array($DeptName, $AryDepartmentNames)) {

														} else {
															$AryDepartmentNames[] = $DeptName;


															?>

															<option value="<?php echo $RowDepartment['DeptID']; ?>">
																<?php echo $RowDepartment['DepartmentName']; ?>
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

												<select class="form-control select2" name="MONTH" id="MONTH" size='1'>
													<?php

													$start = strtotime('first day of this month');
													for ($i = 0; $i < 12; $i++) {
														$time = strtotime(sprintf('%d months', $i), $start);

														$label = date('F', $time);
														$value = date('n', $time);
														echo "<option value='$value'>$label</option>";
													}


													?>
												</select>
											</div>
										</div>

										<!--<div class="col-lg-3">
			 <div class="form-group">
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
				  </div>-->

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
													</i></button>
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
									<h4 class="card-title">Employee - Advance</h4>
									<table  id="mytable-data" class="table dt-responsive nowrap w-100" >
										<thead>
											<tr>
												<th>Bio Id </th>
												<th>Name</th>
												<th>Department</th>
												<th>Reason</th>
												<th style="width: 20%">Tools</th>
											</tr>
										</thead>
									</table>

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
	<Script>

	$('document').ready(function () {
		var doa_doc = '<?php echo $DOA_DOC ?>';

		$("#DOA_DOC").val(doa_doc);
		$(document).on('change', '#Dept', function (e) {

			e.preventDefault();

			var Dept = $(this).val();

			$.ajax({
				url: 'Ajax/AjMonthlyAtt',
				type: 'POST',
				data: { 'Dept': Dept },
				success: function (data) {
					//console.log(data);
					// var obj = JSON.parse(data);
					$("#SubDept").html(data);
					$("#PACK_SIZE").html('<option value="">All Designation</option> ');
				}
			})

		});

		$('#continuebtn').on('click', function () {

			$('form').submit();
		});

		var doa_doc = '<?php echo $DOA_DOC ?>';

		$("#DOA_DOC").val(doa_doc);


		$(document).on('click', '#list', function (e) {
			e.preventDefault();


			loadAdvances();


		});

		loadAdvances();

	});



	$(document).on('click', '.BtnViewAdvDeductions', function (e) {
		e.preventDefault();

		var EmpId = $(this).data('id');
		var Month = $('#MONTH').val();
		var Year = $('#YEAR').val();

		$.ajax({
			url: 'Ajax/AjPopupAdvDeduction.php',
			type: 'POST',
			data: { 'EmpId': EmpId, 'Month': Month, 'Year': Year, 'TYPE': 'AdvanceDeductions' },
			success: function (data) {

				//$('.modal-title').text('Advance Deductions '+EmpId+' ?');
				$('.modal-title').text('Advance Deductions');
				$("#PopUpTable").html(data);
				$('#myModal').modal('show');
			}
		});

	});


	$(document).on('click', '.updateadvdeduction', function (e) {
		e.preventDefault();
		var AdvDedId = $(this).attr('id');
		var DeductionAmount = 'DeductionAmount' + AdvDedId;

		var DeductionAmount = $('#' + DeductionAmount).val();

		//alert(AdvDedId);


		$.ajax({
			type: 'POST',
			url: 'Ajax/AjAddAdvanceAmount.php',
			data: { 'DeductionAmount': DeductionAmount, 'Type': 'UpdateAdvDeduction', 'AdvDedId': AdvDedId },

			//dataType: 'json',

			success: function (response) {
				var data = JSON.parse(response);
				if (data['sts'] == 'failed') {
					alert(data['msg']);
				} else {
					$('#myModal').modal('hide');
					alert(data['msg']);
					//location.reload();
					loadAdvances();

				}
			}
		});

	});



	$(document).on('click', '#BtnEditManualAttend', function (e) {
		e.preventDefault();
		//alert("hi");
		var EmpBioID = $(this).val();
		var AdvDateId = '#MONTH';
		var RemarksId = '#Remarks' + EmpBioID;

		var AdvDate = $(AdvDateId).val();
		var Remarks = $(RemarksId + "").val();

		/***uma(07/07/2022)**/
		var IsValid = false;
		if (Remarks == '') {
			alert("Please Enter Remarks");
			document.getElementById("Remarks").focus();
		}
		else {
			IsValid = true;
		}


		if (IsValid == false) {
			return;
		}
		/***uma(07/07/2022)**/




		else {

			//if(is_numeric(AdvAmount)){

			$.ajax({
				type: 'POST',
				url: 'Ajax/AjAddAdvanceAmount.php',
				data: { 'EmpID': EmpBioID, 'AdvDate': AdvDate, 'Type': 'Add', 'Remarks': Remarks },

				//dataType: 'json',

				success: function (response) {
					var data = JSON.parse(response);
					if (data['sts'] == 'failed') {
						alert(data['msg']);
					} else {
						//$('#myModal').modal('hide');
						alert(data['msg']);
						//location.reload();

						loadAdvances();


					}
				}
			});


		}
	});



	$(document).ajaxStart(function () {
		$("#wait").css("display", "block");
	});
	$(document).ajaxComplete(function () {
		$("#wait").css("display", "none");
	});


	function loadAdvances() {

		var Dept = $('#Dept').val();
		var SubDept = $('#SubDept').val();
		var MONTH = $('#MONTH').val();
		var DATE = $('#DATE').val();
		var YEAR = $('#YEAR').val();

		if ($.fn.DataTable.isDataTable('#mytable-data')) {
            $('#mytable-data').DataTable().destroy();
        }

        table = "";
        table = $('#mytable-data').DataTable({
            serverSide: true,
            ajax: {
                url: 'Ajax/AjEmpAdvList',
                type: 'POST',
                data: function(d) {
	                d.DeptID = Dept;
	                d.SubDeptID = SubDept;
	                d.Month = MONTH;
	                d.Year = YEAR;
	            }
            },
            columns: [
                { data: 'BioMetricID' },
                { data: 'EmpName' },
                { data: 'DepartmentName' },
                { data: 'Remarks' },
                { data: 'Tools' }
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
</body>

</html>
<?php if(0){ ?>
<Script>

	$('document').ready(function () {
		var doa_doc = '<?php echo $DOA_DOC ?>';

		$("#DOA_DOC").val(doa_doc);
		$(document).on('change', '#Dept', function (e) {

			e.preventDefault();

			var Dept = $(this).val();

			$.ajax({
				url: 'Ajax/AjMonthlyAtt',
				type: 'POST',
				data: { 'Dept': Dept },
				success: function (data) {
					//console.log(data);
					// var obj = JSON.parse(data);
					$("#SubDept").html(data);
					$("#PACK_SIZE").html('<option value="">All Designation</option> ');
				}
			})

		});

		$('#continuebtn').on('click', function () {

			$('form').submit();
		});
	});



	$(document).on('click', '.BtnViewAdvDeductions', function (e) {
		e.preventDefault();

		var EmpId = $(this).data('id');
		var Month = $('#MONTH').val();
		var Year = $('#YEAR').val();




		$.ajax({
			url: 'Ajax/AjPopupAdvDeduction.php',
			type: 'POST',
			data: { 'EmpId': EmpId, 'Month': Month, 'Year': Year, 'TYPE': 'AdvanceDeductions' },
			success: function (data) {

				//$('.modal-title').text('Advance Deductions '+EmpId+' ?');
				$('.modal-title').text('Advance Deductions');
				$("#PopUpTable").html(data);
				$('#myModal').modal('show');
			}
		});

	});


	$(document).on('click', '.updateadvdeduction', function (e) {
		e.preventDefault();
		var AdvDedId = $(this).attr('id');
		var DeductionAmount = 'DeductionAmount' + AdvDedId;

		var DeductionAmount = $('#' + DeductionAmount).val();

		//alert(AdvDedId);


		$.ajax({
			type: 'POST',
			url: 'Ajax/AjAddAdvanceAmount.php',
			data: { 'DeductionAmount': DeductionAmount, 'Type': 'UpdateAdvDeduction', 'AdvDedId': AdvDedId },

			//dataType: 'json',

			success: function (response) {
				var data = JSON.parse(response);
				if (data['sts'] == 'failed') {
					alert(data['msg']);
				} else {
					$('#myModal').modal('hide');
					alert(data['msg']);
					//location.reload();
					loadAdvances();

				}
			}
		});

	});



</script>


<script>
	$(document).on('click', '#BtnEditManualAttend', function (e) {
		e.preventDefault();
		//alert("hi");
		var EmpBioID = $(this).val();
		var AdvDateId = '#AdvDate' + EmpBioID;
		var AdvAmountId = '#AdvAmount' + EmpBioID;
		var InstallmentsId = '#Installments' + EmpBioID;
		var RemarksId = '#Remarks' + EmpBioID;

		var AdvDate = $(AdvDateId + "").val();
		var AdvAmount = $(AdvAmountId + "").val();
		var Installments = $(InstallmentsId + "").val();
		var Remarks = $(RemarksId + "").val();

		/***uma(07/07/2022)**/
		var IsValid = false;
		if (AdvAmount == '') {
			alert("Please Enter Advance Amount");
			document.getElementById("AdvAmount").focus();
		}
		//var is_numeric!Number
		else if (!Number(AdvAmount)) {
			alert("Please Enter Valid Advance Amount");
			document.getElementById("AdvAmount").focus();
		}


		else if (Installments == '') {
			alert("Please Enter Deduction");
			document.getElementById("Installments").focus();
		} else if (Remarks == '') {
			alert("Please Enter Remarks");
			document.getElementById("Remarks").focus();
		}
		else {
			IsValid = true;
		}


		if (IsValid == false) {
			return;
		}
		/***uma(07/07/2022)**/




		else {

			//if(is_numeric(AdvAmount)){

			$.ajax({
				type: 'POST',
				url: 'Ajax/AjAddAdvanceAmount.php',
				data: { 'EmpID': EmpBioID, 'AdvDate': AdvDate, 'AdvAmount': AdvAmount, 'Installments': Installments, 'Type': 'Add', 'Remarks': Remarks },

				//dataType: 'json',

				success: function (response) {
					var data = JSON.parse(response);
					if (data['sts'] == 'failed') {
						alert(data['msg']);
					} else {
						//$('#myModal').modal('hide');
						alert(data['msg']);
						//location.reload();

						loadAdvances();


					}
				}
			});


		}
	});

</script>



<Script>

	$('document').ready(function () {
		var doa_doc = '<?php echo $DOA_DOC ?>';

		$("#DOA_DOC").val(doa_doc);


		$(document).on('click', '#list', function (e) {
			e.preventDefault();


			loadAdvances();


		});




		$('#continuebtn').on('click', function () {
			//alert('hi');

			$('form').submit();
		});
	});

	$(document).ajaxStart(function () {
		$("#wait").css("display", "block");
	});
	$(document).ajaxComplete(function () {
		$("#wait").css("display", "none");
	});


</script>


<script>

	function loadAdvances() {

		var Dept = $('#Dept').val();
		var SubDept = $('#SubDept').val();
		var MONTH = $('#MONTH').val();
		var DATE = $('#DATE').val();
		var YEAR = $('#YEAR').val();


		$.ajax({
			url: 'Ajax/AjEmpAdvList',
			type: 'POST',
			data: { 'DeptID': Dept, 'SubDeptID': SubDept, 'Month': MONTH, 'Year': YEAR },
			success: function (data) {
				//  console.log(data);
				//$("#ATT_DATA").html(data);
				showData(data);

			}
		})
		//return false;

	}
</script>
<script>
	function showData(data) {

		$("#ATT_DATA").html(data);

		//$("#EmpList").html(data);


		var Stable = $('#scroll-vertical-datatable').DataTable({
			"scrollY": "450px",
			"scrollCollapse": true,
			"paging": false,
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
	}

</script>
<?php } ?>
