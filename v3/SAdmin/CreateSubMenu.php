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
$SqlDepartmentsList="SELECT * FROM `ClientSideBarHeaders` ";  
$StmtDepartmentsList=$iconn->query($SqlDepartmentsList);

$SqlModuleList="SELECT * FROM `ClientSideBarModules` ";  
$StmtModuleList=$iconn->query($SqlModuleList);

if(isset($MenuID)){
$SqlMenuList="SELECT * FROM `ClientSideBarOptions` where ID='$MenuID' ";  
$StmtMenuList=$iconn->query($SqlMenuList);
$RowJobDetails=$StmtMenuList->fetch_assoc();
}
 
extract($_POST);
extract($_GET);

 
if(isset($_POST['submit'])){
	

$SqlCount="SELECT * FROM `AdminRegisteredClients` ";  
$StmtCount=$iconn->query($SqlCount);
$Count=mysqli_num_rows($StmtCount);

$isSubmenuInsert = !isset($MenuID);
if($isSubmenuInsert){
	 $SqlClientInsert="INSERT INTO `ClientSideBarOptions`(`HeaderId`, `Name`,
	 `Directory`, `FileName`,`SortOrder`,`Module`) 
	 VALUES ('$HeaderID','$Name','$Directory','$FileName','$SortOrder','$ModuleName')";
}else{
	$oldSubmenuData = legacyAuditFetchRowById('ClientSideBarOptions', 'ID', $MenuID, 'SAdmin');
    $SqlClientInsert="update  `ClientSideBarOptions` 
    set 
    HeaderId='$HeaderID',
    Name='$Name',
    Directory='$Directory',
    FileName='$FileName',
    SortOrder='$SortOrder',
    Module='$ModuleName'
    where ID='$MenuID'"; 
}
	
	
	 
	$StmtClientInsert=$iconn->query($SqlClientInsert);
	$JobID= $iconn->insert_id;

	
	if($StmtClientInsert){
		if($isSubmenuInsert){
			legacyAuditLogInsertById('ClientSideBarOptions', 'ID', $JobID, $_POST, [
				'ReferenceNo' => $JobID,
				'Remarks' => 'Sub menu created',
			], 'SAdmin');
		}else{
			legacyAuditLogUpdateById('ClientSideBarOptions', 'ID', $MenuID, $oldSubmenuData, $_POST, [
				'ReferenceNo' => $MenuID,
				'Remarks' => 'Sub menu updated',
			], 'SAdmin');
		}
		header('Location:SubMenuList');

	
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
                  <label for="exampleInputEmail1">Menu Name</label>
                  <select class="form-control select2" name="HeaderID" id="HeaderID">
                           
		<?php while($RowDepartmentsList=$StmtDepartmentsList->fetch_array()){?>	

		<option <?php if($RowJobDetails['HeaderId']==$RowDepartmentsList['Sid']){ ?> selected=="selected"
														<?php } ?> value="<?php echo $RowDepartmentsList['Sid'] ;?>" >
		<?php  echo $RowDepartmentsList['Header']; ?></option>
									<?php    } ?>
                                   </select>
                </div>				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Sub Menu Name</label>
                  <input required type="text" class="form-control" name='Name' id="Name"
				  placeholder="Menu Name" value="<?php echo $RowJobDetails['Name']?>"  >
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Directory</label>
                  <input required type="text" class="form-control" name='Directory' id="Directory"
				  placeholder="Enter Designation" value="<?php echo $RowJobDetails['Directory']?>"  >
                </div>

                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Url(File Name Only)</label>
                  <input required type="text" class="form-control" name='FileName' id="FileName"
				  placeholder="Enter Designation" value="<?php echo $RowJobDetails['FileName']?>"  >
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Module Name</label>
                  <select class="form-control select2" name="ModuleName" id="ModuleName" required>
				  	<option value="" <?php echo ($RowJobDetails['Module']== '') ? 'selected' : ''; ?>>Select</option>
					<?php while($RowModuleList=$StmtModuleList->fetch_array()){?>	
					<option <?php if($RowJobDetails['Module']==$RowModuleList['Name']){ ?> selected=="selected"
																	<?php } ?> value="<?php echo $RowModuleList['Name'];?>" >
					<?php  echo $RowModuleList['Name']; ?></option>
									<?php    } ?>
                                   </select>
                </div>
				
				<div class="col-lg-3">
                    <label for="exampleInputEmail1">SortOrder</label>                                     
                    <input required type="text" class="form-control" name='SortOrder' id="SortOrder"
				  placeholder="Enter UserLoginID" value="<?php echo $RowJobDetails['SortOrder']?>"  >
		          
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
