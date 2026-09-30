<?php 




$PageTitle="Employee List"; 
$PageTitleFolder="HRM";
 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlDeptList="SELECT * FROM HrmDepartments";
$StmtDept=$iconn->query($SqlDeptList);
?>

<!doctype html>
<html lang="en">


    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>

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
        <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
	
	 <?php include ($IncludesPath."PreLoader.php"); ?>
	


        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include ($IncludesPath."page-title.php");?>
						
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										<div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                       <select class="form-control select2" name="DeptID" id="DeptID">
                                                            <option value="">Select Department</option>
														
															<?php while($DataRow=$StmtDept->fetch_array()){?>	
				 
												<option value="<?php echo $DataRow['DeptID'] ;?>" >
												<?php  echo $DataRow['DepartmentName']; ?></option>
												<?php    } ?>
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
												
												<div class="col-lg-4">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="EmpStatus" 
														id="EmpStatus">
                                                            <option value="">All</option>
                                                            <option value="1">Active</option>
                                                            <option value="0">In Active</option>
                                                        </select>
													</div>
                                                </div>
												
												<div align="center" class="col-lg-12">
                                                     
                                                       <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button>
											
						<a href="EmployeeCreateAv1.php" class="btn btn-success waves-effect waves-light"> Add</a>
													</div>
                                               
											    <!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>--> 
	
									 
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
                                        <h4 class="card-title">Employee - List</h4>
                                        

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

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
      

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
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

        <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

    </body>
</html>

<script>
$(document).on('change','#DeptID',function(e){
       e.preventDefault();
       var DeptId=$(this).val();
	   var Type='SubDept';

		$.ajax({
		  url:'Ajax/AjDropDowns',
		  type:'POST',
		  data:{'DeptId':DeptId,'Type':Type},
		  success:function(data){
			$("#SubDeptID").html(data);
					}
			})
		
    
    	});
		

$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			var DeptID=$('#DeptID').val();
			var SubDeptID=$('#SubDeptID').val();
			var EmpStatus=$('#EmpStatus').val();
			$.ajax({
			url:'Ajax/AjAuditEmpList',
		  type:'POST',
		  data:{'DeptID':DeptID,'SubDeptID': SubDeptID,'EmpStatus': EmpStatus},
		  success:function(data){
			  showData(data);

		  }
 
})
			//return false;
});				
		
		
		
		
$(document).on('click', '.EmpActive', function(e){
e.preventDefault();
	//alert('hiiiii');
   
     var EmpId =$(this).data('id');
	 var EmpStatus = $(this).val();
	 var Type = 'EmpStatus';
	 
	 var DeptID=$('#DeptID').val();
	 var SubDeptID=$('#SubDeptID').val();
	 
	$.ajax({
	
	type: 'POST',
    url: 'Ajax/AjAuditEmpList.php',
    data: {'EmpID': EmpId,'EmpStatus':EmpStatus, 'Type' : Type, 'DeptID':DeptID,'SubDeptID': SubDeptID},
    
    success: function(data){
		 showData(data);
	 
     }
  });

    //getRow(id,type);
  });
  
  
  $(document).on('click', '.Delete', function(e){
e.preventDefault();
	//alert('hiiiii');
	
	 var DeptID=$('#DeptID').val();
	 var SubDeptID=$('#SubDeptID').val();
	  var EmpId =$(this).data('id');
	 var EmpStatus = $(this).val();
	  var Type = 'DeleteEmployee';
   
    var r = confirm("Are You Sure You Want To Delete?");
  if (r == true) {
     
     
	$.ajax({
	
	type: 'POST',
    url: 'Ajax/AjAuditEmpList.php',
    data: {'EmpID': EmpId,'EmpStatus': EmpStatus,'Type': Type,'DeptID': DeptID,'SubDeptID': SubDeptID},
    
    success: function(data){
		showData(data);
	//console.log(data);
     //$("#ATT_DATA").html(data);
     //$('#example1').DataTable()
	 
     }
  });
} else{}
    //getRow(id,type);
  });
  


function showData(data){
			
					$("#EmpList").html(data);
 
 	var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : true,
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
  } );
		}

</script>
