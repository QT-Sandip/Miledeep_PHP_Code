<?php



session_start();

$PageTitle="Create Designation";
$PageTitleFolder="HRM";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	  $sql_org_update=" INSERT INTO `HrmDesignations`( `Designation`)
 VALUES ('$Designation')"; 
				$stmt2=$iconn->query($sql_org_update); 

	//$MB_REPORT_ID = $iconn->insert_id;
			if($stmt2){
			legacyAuditLogInsertById('HrmDesignations', 'DesigID', $iconn->insert_id, $_POST, array(), 'Hrm');
			header('Location: DesignationCreate.php');}

}

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
			

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
								 <form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
			
		   <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Designation</label>
                  <input required type="text" class="form-control" 
				  name='Designation' id="Designation" 
				  placeholder="Enter Designation">
                </div>
                
				
				
				</div>
              
					

			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name="submit"   class="btn btn-success waves-effect waves-light">
				Submit</button>
              </div>
			  </form>

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
<script>
$('document').ready(function(){
	
	
	  
  $('#DEPARTMENT_ID').change(function(){
	 var DEPT_ID= $('#DEPARTMENT_ID').val();  
   $.ajax({
    type: 'POST',
    url: '/Hrm/Ajax/AjaxCreateOptions.php',
    data: {'DEPT_ID' : DEPT_ID,'TYPE':'SUB'},  
    success: function(response){
		$("#S_DEPT").html(response);
	 }
  });
  });
 
});   	

</script>

