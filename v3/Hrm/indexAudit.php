<?php 
$PageTitle="Employee List";
$PageTitleFolder="HRM";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlDeptList="SELECT * FROM $HrmDepartments";
$StmtDept=$iconn->query($SqlDeptList);
?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Jlsf/User.css"  rel="stylesheet" type="text/css" />
    <head>
        
    <?php include($IncludesPath . "title-meta.php"); ?>
  <?php include("Includes/DbLinks.php"); ?>
  <?php include($IncludesPath . "head-css.php"); ?>

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
<?php include ("Includes/NewScripts.php");?>

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
