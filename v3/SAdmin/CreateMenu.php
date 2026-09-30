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
$MenuID=$_GET['MenuID'];




$date=date('D-M-Y');
$SqlDepartmentsList="SELECT * FROM `AdminDepartments` ";  
$StmtDepartmentsList=$iconn->query($SqlDepartmentsList);

if(isset($MenuID)){
$SqlMenuList="SELECT * FROM `ClientSideBarHeaders` where Sid='$MenuID' ";  
$StmtMenuList=$iconn->query($SqlMenuList);
$RowJobDetails=$StmtMenuList->fetch_assoc();
}
 
extract($_POST);
extract($_GET);

 
if(isset($_POST['submit'])){
	

$SqlCount="SELECT * FROM `AdminRegisteredClients` ";  
$StmtCount=$iconn->query($SqlCount);
$Count=mysqli_num_rows($StmtCount);

$isMenuInsert = !isset($MenuID);
if($isMenuInsert){
	 $SqlClientInsert="INSERT INTO `ClientSideBarHeaders`(`DeptID`, `Header`,`Status`,
	 `SortOrder`, `SideMenuIcon`) 
	 VALUES ('$DeptID','$MenuName','$Status','$SortOrder','$SideMenuIcon')";
}else{
	$oldMenuData = legacyAuditFetchRowById('ClientSideBarHeaders', 'Sid', $MenuID, 'SAdmin');
    $SqlClientInsert="update  `ClientSideBarHeaders` 
    set 
    DeptID='$DeptID',
    Header='$MenuName',
    SortOrder='$SortOrder',
    Status='$Status',
    SideMenuIcon='$SideMenuIcon'
    where Sid='$MenuID'";
}
	
	
	 
	$StmtClientInsert=$iconn->query($SqlClientInsert);
	$JobID= $iconn->insert_id;

	
	if($StmtClientInsert){
		if($isMenuInsert){
			legacyAuditLogInsertById('ClientSideBarHeaders', 'Sid', $JobID, $_POST, [
				'ReferenceNo' => $JobID,
				'Remarks' => 'Menu created',
			], 'SAdmin');
		}else{
			legacyAuditLogUpdateById('ClientSideBarHeaders', 'Sid', $MenuID, $oldMenuData, $_POST, [
				'ReferenceNo' => $MenuID,
				'Remarks' => 'Menu updated',
			], 'SAdmin');
		}
		header('Location:MenuList');

	
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
                  <label for="exampleInputEmail1">Department</label>
                  <select class="form-control select2" name="DeptID" id="DeptID">
                           
		<?php while($RowDepartmentsList=$StmtDepartmentsList->fetch_array()){?>	

		<option value="<?php echo $RowDepartmentsList['DeptID'] ;?>" >
		<?php  echo $RowDepartmentsList['DepartmentName']; ?></option>
									<?php    } ?>
                                   </select>
                </div>
				
				
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Menu Name</label>
                  <input required type="text" class="form-control" name='MenuName' id="MenuName"
				  placeholder="Menu Name" value="<?php echo $RowJobDetails['Header']?>"  >
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Menu Icon</label>
                  <input required type="text" class="form-control" name='SideMenuIcon' id="SideMenuIcon"
				  placeholder="Enter Designation" value="<?php echo $RowJobDetails['SideMenuIcon']?>"  >
                </div>
				
				<div class="col-lg-3">
                    <label for="exampleInputEmail1">SortOrder</label>                                     
                    <input required type="text" class="form-control" name='SortOrder' id="SortOrder"
				  placeholder="Enter UserLoginID" value="<?php echo $RowJobDetails['SortOrder']?>"  >
		          
                </div>

                <div class="col-lg-3">
                    <label for="exampleInputEmail1">Status</label>                                     
                    <input required type="text" class="form-control" name='Status' id="Status"
				  placeholder="Enter UserLoginID" value="<?php echo $RowJobDetails['Status']?>"  >
		          
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
