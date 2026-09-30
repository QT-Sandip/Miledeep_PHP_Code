<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include "Constants.php";

$PageTitleFolder="Help";
$PageTitle="Add Issue";

 $ClientID=$_SESSION["ClientID"];
$ClientName=$_SESSION["ClientName"];
$SideBarPath=$_SESSION["SideBarPath"];
$DeptID=$_SESSION["DeptID"];
$RaisedBy=$_SESSION["UserID"];
$CreateDate=date('Y-m-d');

$SqlOptions="SELECT * FROM `AdminSerpTicketOptions` WHERE Status=1";
$StmtOptions=$iconn->query($SqlOptions);



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	//echo $_FILES['file']['name'];exit;
/*$target_dir = "Uploads/";
echo $target_file = $target_dir . basename($_FILES["file"]["name"]);exit;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));*/

	 
	if (($_FILES['file']['name']!="")){
	 $target_dir = dirname(__FILE__) ."/Uploads/";
	$file = $_FILES['file']['name'];
	$path = pathinfo($file);
	$filename = $path['filename'];
	$ext = $path['extension'];
	$temp_name = $_FILES['file']['tmp_name'];
	 $path_filename_ext = $target_dir.$filename.".".$ext;
	  $path_filename = $SideBarPath."/HelpLine/Uploads/".$filename.".".$ext;
	

          if(move_uploaded_file($temp_name,$path_filename_ext))
{
	      $FilePath=$path_filename;
}
else
{
   echo "move_uploaded_file failed";
   exit();
}

   

}

 $Sqlinsert="INSERT INTO `AdminSerpTickets`(`ClientId`, `DeptId`, `TicketType`, `IssueDescription`, `IssueLink`,
 `IssueImage`, `RaisedBy`, `CreateDate`) 
 VALUES ('$ClientID','$DeptID','$TicketType','$IssueDescription','$IssueLink','$FilePath','$RaisedBy',
 '$CreateDate')";
 $StmtInsert=$iconn->query($Sqlinsert);
	if($StmtInsert){
		$newTicketId = $iconn->insert_id;
		legacyAuditLogInsertById('AdminSerpTickets', 'TicketId', $newTicketId, $_POST, ['Remarks' => 'HelpLine issue raised'], 'HelpLine');
		
		header('Location: ViewIssues.php');
	}
	//}
	else{
		echo '<script language="javascript" type="text/javascript"> 
                alert("Packing Style Already Exists");
                window.location = "ViewIssues.php";
        </script>';
	
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
								
								<form role="form" method="post" id="myForm" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">
			
			
		 <!--	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Client Name</label>
				  
				  <select class="form-control" name="ClientId" id="ClientId" required>
				  <option value="">Select Client</option>
				  <?php /*while($RowClients=$StmtClients->fetch_array()) { 
				
				?> 
				  
				  <option <?php if($RowTaskList['ClientId']==$RowClients['Cl_Id']){  ?> selected=="selected" <?php } ?>
				  
				  value="<?php echo $RowClients['Cl_Id'];?>"><?php echo $RowClients['Cl_Name'];?></option>
				  				<?php }*/ ?>

				  <select>
				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Module</label>
				  
				  <select class="form-control" name="Module" id="Module" required>
				  <option value="">Select Module</option>
				  <?php /*while($RowDept=$StmtDept->fetch_array()) { 
				
				?> 
				  
				  <option 
				  <?php if($RowTaskList['Module']==$RowDept['ID']){  ?> selected=="selected" <?php } ?>
				  
				  value="<?php echo $RowDept['ID'];?>"><?php echo $RowDept['DEPT_NAME'];?></option>
				  				<?php }*/ ?>

				  <select>
                </div> -->
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Issue Type</label>
				  
				  
				  <select class="form-control select2" name="TicketType" id="TicketType" required>
				  <option value="">Select Issue</option>
				  <?php while($RowOptions=$StmtOptions->fetch_array()) { 
				
				?> 
				  
				  <option <?php if($RowTaskList['TicketType']==$RowOptions['Id']){  ?> selected=="selected" <?php } ?>
				  
				  value="<?php echo $RowOptions['Id'];?>"><?php echo $RowOptions['TicketName'];?></option>
				  				<?php } ?>

				  </select>
				  
                  
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Link</label>
                  <input  type="text" class="form-control" name='IssueLink' id="IssueLink" 
				  placeholder="Provide Link" value="<?php echo $RowTaskList['IssueLink'];?>" >
                </div>
				
				<div class="form-group col-lg-4" >
                  <label for="exampleInputEmail1">Issue Description</label>
				  
				  <textarea required name="IssueDescription" class="form-control" rows="4" cols="40"
                    id="IssueDescription"   
					placeholder="Enter Description" ><?php echo $RowTaskList['IssueDescription'] ?></textarea>
				 		
		
                </div>	
				
				
				<div class="form-group col-sm-3"  align="center">
	        <input type="file"  name="file" id="file" >

              </div>
				
				<!--<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Raised By</label>
                  <input  type="text" class="form-control" name='RaisedBy' id="RaisedBy" 
				  placeholder="Enter Raised By" value="<?php echo $RowTaskList['RaisedBy'];?>" required>
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Issue Date</label>
                  <input  type="date" class="form-control" name='IssueDate' id="IssueDate" 
				  placeholder="Enter Issue Date" value="<?php echo $RowTaskList['IssueDate'];?>" required>
                </div> 
			
			
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Priority</label>
				   <select class="form-control" name="Priority" id="Priority">
				   				  <option value="">Select Priority</option>

  <option <?php if($RowTaskList['Priority']=='High'){  ?> selected=="selected" <?php } ?> value="High">High</option>
  <option  <?php if($RowTaskList['Priority']=='Medium'){  ?> selected=="selected" <?php } ?> value="Medium">Medium</option>
  <option  <?php if($RowTaskList['Priority']=='Low'){  ?> selected=="selected" <?php } ?> value="Low">Low</option>
</select>
                
				  
                  
                </div> 
		
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Assigned To</label>
				  <select class="form-control" name="AssignedTo" id="AssignedTo">
				  <option value="">Select </option>

  <option <?php if($RowTaskList['AssignedTo']=='Surya'){  ?> selected=="selected" <?php } ?> value="Surya">Surya</option>
  <option <?php if($RowTaskList['AssignedTo']=='Priya'){  ?> selected=="selected" <?php } ?> value="Priya">Priya</option>
  <option <?php if($RowTaskList['AssignedTo']=='Uma'){  ?> selected=="selected" <?php } ?> value="Uma">Uma</option>
  <option <?php if($RowTaskList['AssignedTo']=='Lokesh'){  ?> selected=="selected" <?php } ?> value="Lokesh">Lokesh</option>
</select>
				  
                  
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Completed By</label>
				   <select class="form-control" name="CompletedBy" id="CompletedBy">
				  <option value="">Select </option>

  <option <?php if($RowTaskList['CompletedBy']=='Surya'){  ?> selected=="selected" <?php } ?>value="Surya">Surya</option>
  <option <?php if($RowTaskList['CompletedBy']=='Priya'){  ?> selected=="selected" <?php } ?> value="Priya">Priya</option>
  <option <?php if($RowTaskList['CompletedBy']=='Uma'){  ?> selected=="selected" <?php } ?> value="Uma">Uma</option>
  <option <?php if($RowTaskList['CompletedBy']=='Lokesh'){  ?> selected=="selected" <?php } ?> value="Lokesh">Lokesh</option>
</select>
				 
				  
                      </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Completed Date</label>
                  <input type="date" class="form-control" name='CompletedDate' id="CompletedDate" 
				  placeholder="Enter Completed Date" value="<?php echo $RowTaskList['CompletedDate'];?>">
                </div> 
				

								<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Dev Remarks</label>
                  <input type="text" class="form-control" name='DevRemarks' id="DevRemarks" 
				  placeholder="Enter Dev Remarks" value="<?php echo $RowTaskList['DevRemarks'];?>">
                </div> 
								<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Tested By</label>
				  
				   <select class="form-control" name="TestedBy" id="TestedBy">
				  <option value="">Select </option>

  <option <?php if($RowTaskList['TestedBy']=='Surya'){  ?> selected=="selected" <?php } ?>value="Surya">Surya</option>
  <option <?php if($RowTaskList['TestedBy']=='Priya'){  ?> selected=="selected" <?php } ?>value="Priya">Priya</option>
  <option <?php if($RowTaskList['TestedBy']=='Uma'){  ?> selected=="selected" <?php } ?>value="Uma">Uma</option>
  <option <?php if($RowTaskList['TestedBy']=='Lokesh'){  ?> selected=="selected" <?php } ?>value="Lokesh">Lokesh</option>
</select>
			
                 
                </div> 
								<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Test Remarks</label>
                  <input type="text" class="form-control" name='TestRemarks' id="TestRemarks" 
				  placeholder="Enter Test Remarks" value="<?php echo $RowTaskList['TestRemarks'];?>">
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Issue Status</label>
				  <select class="form-control" name="IssueStatus" id="IssueStatus">
				  <option value="">Select Issue Status</option>

  <option  <?php if($RowTaskList['IssueStatus']=='Pending'){  ?> selected=="selected" <?php } ?> value="Pending">Pending</option>
  <option <?php if($RowTaskList['IssueStatus']=='Working'){  ?> selected=="selected" <?php } ?>value="Working">Working</option>
  <option <?php if($RowTaskList['IssueStatus']=='Doubt'){  ?> selected=="selected" <?php } ?>value="Doubt">Doubt</option>
  <option <?php if($RowTaskList['IssueStatus']=='Done'){  ?> selected=="selected" <?php } ?>value="Done">Done</option>
  <option <?php if($RowTaskList['IssueStatus']=='Test Pending'){  ?> selected=="selected" <?php } ?>value="Test Pending">Test Pending</option>
</select>
                  
                </div> -->
                </div>
				
				
			<div class="col-xs-12 box-footer"  align="right">
						  <a target="" href="ViewIssues.php"   class="btn btn-danger  "> Close</a>

                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div>
				
				 
				
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
