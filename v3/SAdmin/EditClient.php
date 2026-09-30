<?php

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Create Client";

$PageTitleFolder="Admin";

$AccessType=$_SESSION['AccessType'];
$ClientID=$_GET['ClientID'];

$SqlRoles = "SELECT ID,`RoleName` FROM `AdminClientRoles`";
$StmtRoles = $iconn->query($SqlRoles);

 $SqlClientDetails="SELECT * FROM `AdminRegisteredClients` where ClientID='$ClientID' ";  
$StmtClientDetails=$iconn->query($SqlClientDetails);
$RowClientDetails=$StmtClientDetails->fetch_assoc();
 
extract($_POST);
extract($_GET);

 
if(isset($_POST['submit'])){
	

    $SqlClientInsert="update  `AdminRegisteredClients` 
    set 
    RoleID='$RoleID'
    
    where ClientID='$ClientID'";
	
	
	 
	$StmtClientInsert=$iconn->query($SqlClientInsert);


	
	if($StmtClientInsert){
		
	header('Location:AdminClientsList');

	
	}
else{
	    echo "<script type='text/javascript'>alert('User ID Already exists')</script>";

	// echo "<script type='text/javascript'> alert(User ID Already exists </script>";
}

}

 ?>


<!DOCTYPE html>
<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php			include ($IncludesPath."PreLoader.php"); ?>

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
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>
	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">			
					
					
			    
				
				
				
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Client Name</label>
                  <input readonly required type="text" class="form-control" name='MenuName' id="MenuName"
				  placeholder="Menu Name" value="<?php echo $RowClientDetails['ClientName']?>"  >
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Client Code</label>
                  <input readonly required type="text" class="form-control" name='SideMenuIcon' id="SideMenuIcon"
				  placeholder="Enter Designation" value="<?php echo $RowClientDetails['ClientAccountID']?>"  >
                </div>
				
				<div class="form-group col-sm-3" >
											<label for="role">Role<span style="color:red">*</span></label>
											<select required class="form-control select2" name="RoleID" id="RoleID">
												<option value="">Select</option>
												
                                                <?php 
													 while($RowRoles=$StmtRoles->fetch_array()) { ?>
														<option <?php if($RowClientDetails['RoleID']==$RowRoles['ID']){ ?> selected=="selected"
														<?php } ?>	value="<?php echo $RowRoles['ID']?>"><?php echo$RowRoles['RoleName']?></option>
													 <?php  } 
												?>
											<select>
										</div>
			
					
			
				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Update</button>
              </div>
            </form>
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
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
</html>				
<script>
$(document).on('change','#BranchID',function(e){
       e.preventDefault();
       var BranchID=$(this).val();
	   var Type='BranchCode';

		$.ajax({
		  url:'Ajax/AjInputvalues.php',
		  type:'POST',
		  data:{'BranchID':BranchID,'Type':Type},
		  success:function(data){
			$("#BranchCode").val(data);

					}
			})
		
    
    	});
			
		
	  
	
		
		
</script>
