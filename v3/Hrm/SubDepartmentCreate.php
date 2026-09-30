<?php

$PageTitle="New Sub Department";
$PageTitleFolder="HRM";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ("Includes/DbLinks.php");?>


            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
<?php
			include ($IncludesPath."PreLoader.php"); ?>

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
			
<!--<h2 align="center">New Sub Department</h2>-->

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										<div class="row">
			

			

				<div class="form-group col-lg-3">
				<label for="exampleInputPassword1">Select Department</label>
                    <select class="form-control select2" name="DEPARTMENT_ID" id="DEPARTMENT_ID" required>
                        <option value="" selected>- Select -</option>

                        <?php
                          $SqlDept = "SELECT * FROM HrmDepartments";
                          $StmtDept = $iconn->query($SqlDept);
						   
                          while($RowDept = $StmtDept->fetch_assoc()){
                            echo "
                              <option value='".$RowDept['DeptID']."'>".$RowDept['DepartmentName']."</option>
                            "; 
                          }
                        ?>
                      </select>
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Sub-Department Name</label>
                 <input type="text" class="form-control" id="SUB_DEPT" name="SUB_DEPARTMENT_NAME" placeholder="Enter Sub Department Name " required>
                </div>
                </div>
         
		  
			  
			  
           
 

<div class="col-xs-12 box-footer"  align="right">
                <button  id="add"   class="btn btn-primary waves-effect waves-light">Submit</button>
              </div>
									</div>
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

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ("Includes/NewScripts.php");?>
      

       
    </body>
</html>				
<Script>

$('document').ready(function(){

$(document).on('click', '#add', function(e){
    e.preventDefault();
	var SubDeptName = $('#SUB_DEPT').val();
	var DepartmentId = $('#DEPARTMENT_ID').val();
	var Type = "AddSubDept";
	
if(DepartmentId==''){
	alert("Select Department ");
	return;
}else if(SubDeptName==''){
	alert("Enter Sub-Department");
	return;
}



	
	



  $.ajax({
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
	dataType: 'json',
    data: {'SubDeptName':SubDeptName,'DepartmentId':DepartmentId,'Type':Type},

    success: function(response){
		 var obj = JSON.parse(JSON.stringify(response));
		 console.log(obj);
		 if(obj.error == null){
			 alert(obj.Msg);
			 location.reload();
		 }else{
			 alert(obj.error);
		 }
			   

     }
  });

  });	


    });    

</script>





</html>

