<?php
session_start();


$PageTitle="New Department";
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
			

			



				
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Department Name</label>
                  <input required type="text" class="form-control" name='DeptName' id="DeptName"
				  placeholder="Enter Department Name">
                </div>
			
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">OT Availability</label>
                   <select class="form-control select2" name='OtAval' id="OtAval" required>
				   <option value=""> Select </option>
				   <option value="Yes"> Yes</option>
				   <option value="No">No</option>
				  </select>
                </div>

				
				
				
				
			  
        <!-- end row -->
									</div>
									<div class="col-xs-12 box-footer"  align="right">
							 <a target="" href="HrmDepartments.php"   class="btn btn-danger  "> Close</a>

                <button  id="add"   class="btn btn-success waves-effect waves-light">Submit</button>
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
	var DeptName = $('#DeptName').val();
	 var OtAval = $('#OtAval').val();
	var Type = "AddDept";
	
if(DeptName==''){
	alert("Enter Department Name");
	return;
}else if(OtAval==''){
	alert("Enter Ot Availavility");
	return;
}



  $.ajax({ 
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
	dataType: 'json',
    data: {'DeptName':DeptName,'OtAval':OtAval,'Type':Type},

    success: function(response){
		 var obj = JSON.parse(JSON.stringify(response));
		 console.log(obj);
		 if(obj.error == null){
			 alert(obj.Msg);
			 location.reload();
		 }else{
			 alert(obj.error);
		 }
			   
			   
		
   /*   $('#DEPARTMENT_ID').val(response.DEPARTMENT_NAME);
      $('#attid').val(id);
     $('#del_attid').val(id);
	 */
    
    }
  });


  });	


    });    

</script>







