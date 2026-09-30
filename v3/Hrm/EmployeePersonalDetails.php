<?php
$PageTitle       = "Employee Personal Details";
$PageTitleFolder = "HRM";

include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";
$SqlDeptList = "SELECT DeptID,DepartmentName FROM HrmDepartments";
$StmtDept    = $iconn->query($SqlDeptList);
?>

<!doctype html>
<html lang="en">

    <head>

         <?php include $IncludesPath . "title-meta.php";?>
         <?php include "Includes/DbLinks.php";?>
         <?php include $IncludesPath . "head-css.php";?>

    </head>

    <body data-sidebar="dark">

	 <?php include $IncludesPath . "PreLoader.php";?>



        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php
include $IncludesPath . "topbar.php";
include $IncludesPath . "sidebar.php";
?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include $IncludesPath . "page-title.php";?>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

										<div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                       <select class="form-control select2" name="DeptID" id="DeptID">
                                                            <option value="">Select Department</option>

															<?php while ($DataRow = $StmtDept->fetch_array()) {?>

												<option value="<?php echo $DataRow['DeptID']; ?>" >
												<?php echo $DataRow['DepartmentName']; ?></option>
												<?php }?>
                                                        </select>
													</div>
                                                </div>

												<div class="col-lg-4">
                                                    <div class="form-group">

                                                        <select class="form-control select2"  name="SubDeptID"
														id="SubDeptID">
                                                            <option value="">Select SubDepartment</option>
                                                        </select>
													</div>
                                                </div>

												<div align="center" class="col-lg-3">

                                                       <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
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
                                    	<table id="key-datatable" class="table table-bordered" style="width:100%">
                                            <thead>
                                                <tr>                        
													<th>Bio Id </th>
													<th>Name</th>
													<th>Phone Number</th>
													<th>Aadhar Number</th>
													<th>PAN Number</th>
													<th>ESIC</th>
													<th>Address</th>
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

                 <?php include $IncludesPath . "footer.php";?>

            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->



		<?php include $IncludesPath . "vendor-scripts.php";?>


       		<?php include "Includes/NewScripts.php";?>
       		<script>
       			$(document).on('change','#DeptID',function(e){
			       e.preventDefault();
			       var DeptID=$(this).val();
				   var Type='SubDept';

					$.ajax({
					  url:'Ajax/AjDropDowns',
					  type:'POST',
					  //data:{'DeptID':DeptID,'Type':Type},
					  data:{'DeptId':DeptID,'Type':Type},
					  success:function(data){
						$("#SubDeptID").html(data);
								}
					});

			    });

			    $(document).on('click', '.emp_active', function(e){
					e.preventDefault();

					var EmpId =$(this).data('id');
					var EmpStatus = $(this).val();
					var Type = 'EmpStatus';

					var DeptID=$('#DeptID').val();
					var SubDeptID=$('#SubDeptID').val();

					$.ajax({

					type: 'POST',
					url: 'Ajax/AjEmpSalaryDetails',
					data: {'EmpID': EmpId,'EmpStatus':EmpStatus, 'Type' : Type, 'DeptID':DeptID,'SubDeptID': SubDeptID},

					success: function(data){
						if ($.fn.DataTable.isDataTable('#key-datatable')) {
			                $('#key-datatable').DataTable().destroy();
			            }
			            table = "";
			            loadDynamicTable();

						}
					});

				});

       			$(document).on('click','#list',function(e){
					e.preventDefault();
					if ($.fn.DataTable.isDataTable('#key-datatable')) {
		                $('#key-datatable').DataTable().destroy();
		            }
		            table = "";
		            loadDynamicTable();
				
				});
       			$(document).ready(function () {
                
		            if ($.fn.DataTable.isDataTable('#key-datatable')) {
		                $('#key-datatable').DataTable().destroy();
		            }
		            table = "";
		            loadDynamicTable();

		        });

				function loadDynamicTable() {                
		            var DeptID = $('#DeptID').val();
					var SubDeptID = $('#SubDeptID').val();
		            table = $('#key-datatable').DataTable({
		                "lengthMenu": [[20,50,100], [20,50,100]],
		                scrollX: true,
		                scrollY: '400px',
		                scrollCollapse: true,
		                paging: true,
		                processing: true,
		                serverSide: true,
		                ajax: {
		                    url: 'Ajax/AjEmployeePersonalDetails',
		                    type: 'POST',
		                    data:{'DeptID':DeptID,'SubDeptID': SubDeptID, 'Type': 'List'},
		                },
		                columns: [
		                    { data: 'BioMetricID' },
		                    { data: 'EmpName' },
		                    { data: 'PhoneNumber' },
		                    { data: 'AadharNumber' },
		                    { data: 'PANNo' },
		                    { data: 'ESICNo' },
		                    { data: 'PresentAddress' },                    
		                    { data: 'Tools' }                        
		                ],
		                columnDefs: [
		                    { targets: [3], orderable: false, searchable: false },
		                    { targets: [4], orderable: false, searchable: false },
		                    { targets: [5], orderable: false, searchable: false },
		                    { targets: [6], orderable: false, searchable: false },
		                    { targets: [7], orderable: false, searchable: false }

		                ],
		                drawCallback: function () {
		                    // // Initialize editable fields
		                    // $('.editable').editable();
		                }
		            });
		        } 
       		</script>

    </body>

</html>