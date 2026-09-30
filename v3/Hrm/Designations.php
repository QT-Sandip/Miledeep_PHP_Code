<?php

$PageTitle = "Designations";
$PageTitleFolder = "HRM";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlDesignation = "SELECT * FROM `HrmDesignations`";
$query = $iconn->query($SqlDesignation);

?>
<!DOCTYPE html>

<html lang="en">


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


					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body" id="EmpList">

									<div class="col-lg-12 box-footer" align="center">

										<a href="DesignationCreate.php" class="btn btn-success waves-effect waves-light">Add</a>

									</div>

									<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

										<thead class="thead-light">
											<tr>


												<th>Designation Name</th>												
												<!-- <th style="width: 10%">Tools</th> -->


											</tr>
										</thead>
										<tbody>
											<?php

											$s_no = 1;

											while ($row = $query->fetch_assoc()) {
												// $status = ($row['status'])?'<span class="label label-warning pull-right">ontime</span>':
												'<span class="label label-danger pull-right">late</span>';
												echo "
                        <tr id='dtd'" . $row['DeptID'] . ">
                       
						  <td>" . $row['Designation'] . "</td>
						 
						  <!--<td>
						  
						  <a target='_blank' class='mr-3 text-primary' href='DesignationCreate.php?ID=" . $row['DeptID'] .
													"&SUB_ID=" . $row['SubDeptID'] . "'> <i class='mdi mdi-pencil font-size-18'></i>  </a>
						  
                         
						 <a id=" . $row['DeptID'] . " 
	onClick='reply_click(this.id)' class='text-danger delete'>
	<i class='mdi mdi-trash-can font-size-18'></i> </a>
						 
                         
                       
					   </td>-->
                        </tr>
                      ";
												$s_no++;
											}
											?>
										</tbody>
									</table>

								</div>

								<!-- end col -->
							</div> <!-- end row -->





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
	<?php include("Includes/NewScripts.php"); ?>




	<script>
		$(document).ready(function() {
			//showData(data);
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
		});

		$(document).on('click', '#add', function(e) {
			e.preventDefault();
			var DeptName = $('#DEPT_NAME').val();
			var OtAval = $('#OT_AVAILABILITY').val();
			var Type = "AddDept";

			$('#addnew').modal('hide');

			$.ajax({
				type: 'POST',
				url: 'Ajax/AjDeptDetails.php',
				dataType: 'json',
				data: {
					'DeptName': DeptName,
					'OtAval': OtAval,
					'Type': Type
				},
				success: function(response) {
					var obj = JSON.parse(JSON.stringify(response));
					console.log(obj);
					if (obj.error == null) {
						alert(obj.Msg);
						location.reload();
					} else {
						alert(obj.error);
					}
				}
			});
		});


		$(document).on('click', '#add_sub', function(e) {
			e.preventDefault();
			var SubDeptName = $('#SUB_DEPT').val();
			var DepartmentId = $('#DEPARTMENT_ID').val();
			var Type = "AddSubDept";

			$('#addnewsub').modal('hide');
			$.ajax({
				type: 'POST',
				url: 'Ajax/AjDeptDetails.php',
				dataType: 'json',
				data: {
					'SubDeptName': SubDeptName,
					'DepartmentId': DepartmentId,
					'Type': Type
				},

				success: function(response) {
					var obj = JSON.parse(JSON.stringify(response));
					console.log(obj);
					if (obj.error == null) {
						alert(obj.Msg);
						location.reload();
					} else {
						alert(obj.error);
					}
				}
			});
		});




		$(document).on('click', '#add_designation', function(e) {
			e.preventDefault();
			var DepartmentId = $('#DEPT_ID').val();
			var SubDeptId = $('#SUB_ID').val();

			var DesignationName = $('#DESIGNATION_NAME').val();

			var Type = "AddDesignation";

			$('#addnewdesi').modal('hide');

			$.ajax({
				type: 'POST',
				url: 'Ajax/AjDeptDetails.php',
				dataType: 'json',
				data: {
					'DepartmentId': DepartmentId,
					'SubDeptId': SubDeptId,
					'DesignationName': DesignationName,
					'Type': Type
				},
				success: function(response) {
					var obj = JSON.parse(JSON.stringify(response));
					console.log(obj);
					if (obj.error == null) {
						alert(obj.Msg);
						location.reload();
					} else {
						alert(obj.error);
					}
				}
			});
		});


		function showData(data) {

			$("#EmpList").html(data);

			
		}
	</script>
</body>

</html>