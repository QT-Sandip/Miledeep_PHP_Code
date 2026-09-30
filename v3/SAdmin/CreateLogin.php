<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Create Client";

$PageTitleFolder="Admin";

$AccessType=$_SESSION['AccessType'];


$date=date(D-M-Y);
$SqlDepartmentsList="SELECT * FROM `AdminDepartments` ";  
$StmtDepartmentsList=$iconn->query($SqlDepartmentsList);

$SqlClientsList="SELECT * FROM `AdminRegisteredClients` ";  
$StmtClientsList=$iconn->query($SqlClientsList);
 
extract($_POST);
extract($_GET);

 
if(isset($_POST['submit'])){
	

$SqlCount="SELECT * FROM `AdminRegisteredClients` ";  
$StmtCount=$iconn->query($SqlCount);
$Count=mysqli_num_rows($StmtCount);

  $UserPassword = password_hash($UserPassword, PASSWORD_DEFAULT);
 
	 $SqlClientInsert="INSERT INTO `AdminLoginDetails`(`DeptID`, `SubDeptID`,
	 `DesignationID`, `UserName`, `AccessType`, `DeviceID`, `UserLoginID`, `Hashpassword`,`ClientID`,`LastLogin`) 
	 VALUES ('$DeptID','99','$DesignationID','$UserName','$AccessType','$DeviceID','$UserLoginID','$UserPassword','$ClientID','$date')";
	//echo  $SqlClientInsert;
	//exit;
	
	 
	$StmtClientInsert=$iconn->query($SqlClientInsert);
		        $JobID= $iconn->insert_id;

	
	if($StmtClientInsert){
		legacyAuditLogInsertById('AdminLoginDetails', 'UserID', $JobID, $_POST, [
			'ReferenceNo' => $JobID,
			'Remarks' => 'Admin login created',
		], 'SAdmin');
		
					header('Location:index.php');

	
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
                  <select class="form-control select2" name="ClientID" id="ClientID">
                          
		<?php while($RowClientsList=$StmtClientsList->fetch_array()){?>	

		<option value="<?php echo $RowClientsList['ClientAccountID'] ;?>" >
		<?php  echo $RowClientsList['ClientName']; ?></option>
									<?php    } ?>
                                   </select>
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Department</label>
                  <select class="form-control select2" name="DeptID" id="DeptID">
                           
		<?php while($RowDepartmentsList=$StmtDepartmentsList->fetch_array()){?>	

		<option value="<?php echo $RowDepartmentsList['DeptID'] ;?>" >
		<?php  echo $RowDepartmentsList['DepartmentName']; ?></option>
									<?php    } ?>
                                   </select>
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Access Type</label>
                  <select class="form-control select2" name="AccessType" id="AccessType">                        
		<option value="3" >User</option>
		<option value="2" > Admin</option>	
		<option value="1" >Super Admin</option>
										
                                   </select>
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">User Name</label>
                  <input required type="text" class="form-control" name='UserName' id="UserName"
				  placeholder="UserName" value="<?php echo $RowJobDetails['UserName']?>"  >
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Designation</label>
                  <input required type="text" class="form-control" name='DesignationID' id="DesignationID"
				  placeholder="Enter Designation" value="<?php echo $RowJobDetails['DesignationID']?>"  >
                </div>
				
				<div class="col-lg-3">
                    <label for="exampleInputEmail1">User ID</label>                                     
                    <input required type="text" class="form-control" name='UserLoginID' id="UserLoginID"
				  placeholder="Enter UserLoginID" value="<?php echo $RowJobDetails['UserLoginID']?>"  >
		          
                </div>
			
					

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Password</label>
                 <input required type="text" class="form-control" name='UserPassword' id="UserPassword"
				  placeholder="Enter Password" value="<?php echo $RowJobDetails['UserPassword']?>"  >
		          
                </div>
				
			
				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Create</button>
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
