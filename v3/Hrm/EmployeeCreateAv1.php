<?php
// error_reporting(E_ALL);
// error_reporting(-1);
// ini_set('error_reporting', E_ALL);


$Status=$_SESSION['Status'];


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlShiftDetails="Select * from HrmShiftDetails";
$StmtShiftDetails=$iconn->query($SqlShiftDetails);  
extract($_POST);
extract($_GET);
$EmpID=$_GET['EmpID'];

$SqlEmployeeDetails="Select * from HrmAuditEmployeeDetails where EmpID='$EmpID'";
$StmtEmployeeDetails=$iconn->query($SqlEmployeeDetails);
$RowEmployeeDetails=$StmtEmployeeDetails->fetch_array();
$Count=mysqli_num_rows($StmtEmployeeDetails);

if($Count==0){
$PageTitle="New Employee";
}

else{
$PageTitle="Edit Employee";
}

$PageTitleFolder="HRM";


if(isset($_POST['submit'])){
	
	$SqlEmployeeDetails="Select * from HrmAuditEmployeeDetails where BioMetricID='$BioMetricID'";
$StmtEmployeeDetails=$iconn->query($SqlEmployeeDetails);
$BioCount=mysqli_num_rows($StmtEmployeeDetails);
//if($BioCount==0){
	$DateOfCreation= date('Y-m-d');
		if($Count==0){
			if($BioCount==0){
$SqlNewEmployee="INSERT INTO `HrmAuditEmployeeDetails`
( `BioMetricID`, `DeptID`, `SubDeptID`, `EmpName`, `EmpGender`, 
`EmpCategory`,`EmpSubCategory`, `DesigID`, `ShiftID`, `DateOfCreation`,
`JoiningDate`, `EmpStatus`,`BranchID`,`ShiftStartFrom`,`ShiftsAvailable`,`ContractorID`) 
VALUES ('$BioMetricID','$DeptID','$SubDeptID','$EmpName','$EmpGender','$EmpCategory',
'$EmpSubCategory','$DesigID','$ShiftID','$DateOfCreation','$JoiningDate','1','$BranchID',
'$ShiftStartFrom','$ShiftsAvailable','$ContractorID')";
if($StmtNewEmployee=$iconn->query($SqlNewEmployee)){
	$lastInsertedId = $iconn->insert_id;
	legacyAuditLogInsertById('HrmAuditEmployeeDetails', 'EmpID', $lastInsertedId, $_POST, array(), 'Hrm');
	$_SESSION['Status'] = 'Employee added successfully';
	header("Location:EmployeeListAv1");
	}else{
	$_SESSION['Status'] = $iconn->error ."Unable To Create Employee";
	header("Location:EmployeeCreateAv1");	
	}
		
		}
else{
$message = "Biometric Id Alreay Exists";
echo "<script type='text/javascript'>alert('$message');</script>";}
}
		


else{
   $SqlNewEmployee="UPDATE `HrmAuditEmployeeDetails` SET 
    `BioMetricID`='$BioMetricID',`DeptID`='$DeptID',
	`SubDeptID`='$SubDeptID',`EmpName`='$EmpName',
	`EmpGender`='$EmpGender',`EmpCategory`='$EmpCategory',
	`EmpSubCategory`='$EmpSubCategory',`DesigID`='$DesigID',
	`ShiftID`='$ShiftID',`JoiningDate`='$JoiningDate',
	`BranchID`='$BranchID',
	`ShiftStartFrom`='$ShiftStartFrom',
	`ShiftsAvailable`='$ShiftsAvailable',
	`ContractorID`='$ContractorID'
	WHERE EmpID='$EmpID'";
//}
//ECHO $SqlNewEmployee;
//EXIT;
	$oldEmployeeData = legacyAuditFetchRowById('HrmAuditEmployeeDetails', 'EmpID', $EmpID, 'Hrm');
	  
	if($StmtNewEmployee=$iconn->query($SqlNewEmployee)){
	legacyAuditLogUpdateById('HrmAuditEmployeeDetails', 'EmpID', $EmpID, $oldEmployeeData, $_POST, array(), 'Hrm');
	$_SESSION['Status'] = 'Employee Updated successfully';
	header("Location:EmployeeListAv1");
	}else{
	$_SESSION['Status'] = $iconn->error ."Unable To Update Employee";
	header("Location:EmployeeListAv1.php");	
	}
			
}	  
	  
}

	$SqlShiftList="SELECT * FROM HrmShiftDetails where ShiftName!='General' Order by ShiftName";
	$stmtShiftList=$iconn->query($SqlShiftList);
	$SqlShiftAvailable="SELECT * FROM HrmShiftDetails where ShiftName!='General' Order by ShiftName";
    $stmtShiftAvailable=$iconn->query($SqlShiftAvailable);
?>


<div class="modal fade" id="myModal1" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body checkdiv1">
			
			<div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Shift Start from</label>
                <select class="form-control" name="ShiftStartFrom" id="ShiftStartFrom"  required>
				  <?php while($RowDept=$stmtShiftList->fetch_array()){?>
					<option <?php if($RowEmployeeDetails['ShiftStartFrom']==$RowDept['ShiftID']){ ?>selected="selected" <?php } ?>
					
					value="<?php echo $RowDept['ShiftID'] ;?>" >
					<?php  echo $RowDept['ShiftName']; ?>
					</option>
				
				 		<?php }   ?>
				  <select>
            </div>
			
			<?php 

			  $Name='Shifts Available';		  ?>			   
			   <h4><?php echo  $Name;?></h4>	
			  
			  <?php while($RowDeptList=$stmtShiftAvailable->fetch_array()){ ?>
		
		  
				<div class = "checkbox">
               <label>		    
				<input 
				type = "checkbox" id="<?php echo $RowDeptList['ShiftID'];?>" 
				value = "<?php echo $RowDeptList['ShiftName'];?>">
				<?php echo $RowDeptList['ShiftName'];?>
			   
			   </label>
			   </div>
			   
			  <?php } ?>

			
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!DOCTYPE html>


<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 <?php  include ("Includes/DbLinks.php");?>
         <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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
			
                    <div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1">Branch</label>
                 <select class="form-control select2" id="BranchID" name="BranchID" >
				  <option value="">Select Branch</option>
				 <?php
				 $SqlBranches="SELECT * FROM HrmBranchMaster Order by BranchID";
                 $StmtBranches=$iconn->query($SqlBranches);
				 while($BranchesRow=$StmtBranches->fetch_array()){?>
					<option <?php if($RowEmployeeDetails['BranchID']==$BranchesRow['BranchID']){ ?>selected="selected" <?php } ?>
					
					value="<?php echo $BranchesRow['BranchID'] ;?>" >
					<?php  echo $BranchesRow['BranchName'].'('.$BranchesRow['BranchCode'].')'; ?>
					</option>
				
				 		<?php }   ?>
						</select>
                </div>
			


			<div class="form-group col-lg-3" >
			<label for="exampleInputEmail1">Department</label>
                
                 <select class="form-control select2" id="DeptID" name="DeptID" required>
				  <option value="">Select Department</option>
				 <?php
				 $SqlDeptList="SELECT * FROM HrmDepartments Order by DepartmentName";
                 $StmtDept=$iconn->query($SqlDeptList);
				  if($EmpID==''){
				 while($DeptRow=$StmtDept->fetch_array()){?>
					<option value="<?php echo $DeptRow['DeptID'] ;?>" >
					<?php  echo $DeptRow['DepartmentName']; ?>
					</option>
				
				  <?php }  } else{

while($DeptRow=$StmtDept->fetch_array()){ ?>

<option <?php if($RowEmployeeDetails['DeptID']==$DeptRow['DeptID']){ ?>selected="selected" <?php } ?>
value="<?php echo $DeptRow['DeptID'] ;?>" >
					<?php  echo $DeptRow['DepartmentName']; ?>
					</option>
				
				  <?php }  } ?>
						</select>
                </div>
				
				
				
			<div class="form-group col-lg-3" > 
			<label for="exampleInputEmail1">Sub Department</label>
                 <select class="form-control select2" id="SubDeptID" name="SubDeptID" >
				  <option value="">Select Sub Department</option>
				  <?php if($EmpID!=''){
$SqlDeptList="SELECT * FROM HrmSubDepartments Order by SubDeptName  ";
                 $StmtDept=$iconn->query($SqlDeptList);					  
				  while($DeptRow=$StmtDept->fetch_array()){?>
					<option <?php if($RowEmployeeDetails['SubDeptID']==$DeptRow['SubDeptID']){ ?>selected="selected" <?php } ?>
					
					value="<?php echo $DeptRow['SubDeptID'] ;?>" >
					<?php  echo $DeptRow['SubDeptName']; ?>
					</option>
				
				  <?php }  }?>
				 </select>
                </div>
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Biometric Id</label>
                    <input class="form-control" type="text" 
					id="BioMetricID" name="BioMetricID"
					placeholder="Biometric Id"  maxlength="4" value="<?php echo $RowEmployeeDetails['BioMetricID'] ?>" required>                 
                  </div>
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Employee Name</label>
                    <input class="form-control" type="search"
					id="EmpName" name="EmpName"
					placeholder="Employee Name" value="<?php echo $RowEmployeeDetails['EmpName'] ?>" required>                 
                  </div>
				  
				<!--<div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1">Contractor Name</label>
                 <select class="form-control select2" id="ContractorID" name="ContractorID" >
				  <option value="">Select Contractor</option>
				 <?php
				 $SqlContractorList="SELECT * FROM HrmContractorList Order by ContractorID";
                 $StmtContractor=$iconn->query($SqlContractorList);
				 while($DeptRow=$StmtContractor->fetch_array()){?>
					<option <?php 
					
					if($RowEmployeeDetails['ContractorID']==$DeptRow['ContractorID'])
					{ ?>selected="selected" <?php } ?>
					
					value="<?php echo $DeptRow['ContractorID'] ;?>" >
					<?php  echo $DeptRow['ContractorName']; ?>
					</option>
				
				 		<?php }   ?>
						</select>
                </div>
                -->
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Gender</label>
                   <select class="form-control select2" name='EmpGender' id="EmpGender" >
				   <option value="">Select Gender </option>
				   <option <?php if($RowEmployeeDetails['EmpGender']=="Male"){ ?>selected="selected" <?php } ?>
				   
				   value="Male">Male</option>
				   <option <?php if($RowEmployeeDetails['EmpGender']=="Female"){ ?>selected="selected" <?php } ?>
				   value="Female">Female</option>
				   <option <?php if($RowEmployeeDetails['EmpGender']=="Others"){ ?>selected="selected" <?php } ?>
				   value="Others">Others</option>
				  </select>
                </div>
				
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Category</label>
                   <select required class="form-control select2" name='EmpCategory' id="EmpCategory" >
				   <option value="">Select Category </option>
				   <option <?php if($RowEmployeeDetails['EmpCategory']=="Staff"){ ?>selected="selected" <?php } ?>
				   
				   value="Staff">Staff</option>
				   <option <?php if($RowEmployeeDetails['EmpCategory']=="Worker"){ ?>selected="selected" <?php } ?>
				   value="Worker">Worker</option>
				  </select>
                </div>
				
				<!--<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Sub Category </label>
                   <select class="form-control select2" name='EmpSubCategory' id="EmpSubCategory" >
				   <option value="">Select SubCategory </option>
				   <option <?php if($RowEmployeeDetails['EmpSubCategory']=="Regular"){ ?>selected="selected" <?php } ?>
				   
				   value="Regular">Regular</option>
				   <option <?php if($RowEmployeeDetails['EmpSubCategory']=="ContractWise"){ ?>selected="selected" <?php } ?>
				   value="ContractWise">Contract Wise</option>
				  </select>
                </div>-->
				
				<div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1">Designation</label>
                 <select class="form-control select2" id="DesigID" name="DesigID" >
				  <option value="">Select Designation</option>
				 <?php
				 $SqlDeptList="SELECT * FROM HrmDesignations Order by Designation";
                 $StmtDept=$iconn->query($SqlDeptList);
				 while($DeptRow=$StmtDept->fetch_array()){?>
					<option <?php if($RowEmployeeDetails['DesigID']==$DeptRow['DesigID']){ ?>selected="selected" <?php } ?>
					
					value="<?php echo $DeptRow['DesigID'] ;?>" >
					<?php  echo $DeptRow['Designation']; ?>
					</option>
				
				 		<?php }   ?>
						</select>
                </div>
				
			<!--	<div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1">Shift</label>
                 <select class="form-control select2" id="ShiftID" name="ShiftID" >
				  <option value="">Select Shift</option>
				 <?php
				 $SqlDeptList="SELECT * FROM HrmShiftDetails Order by ShiftName";
                 $StmtDept=$iconn->query($SqlDeptList);
				 while($DeptRow=$StmtDept->fetch_array()){?>
					<option <?php if($RowEmployeeDetails['ShiftID']==$DeptRow['ShiftID']){ ?> selected="selected" <?php } ?>
					
					value="<?php echo $DeptRow['ShiftID'] ;?>" >
					<?php  echo $DeptRow['ShiftName']; ?>
					</option>
				
				 		<?php }   ?>
						</select>
                </div>
				-->

                 <div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Shift Category</label>
                   <select class="form-control select2" name='ShiftCategory' id="ShiftCategory" >
				   
				   <option <?php if($RowEmployeeDetails['ShiftCategory']=="General"){ ?>selected="selected" <?php } ?>				   
				   value="General">General</option>
				   <option <?php if($RowEmployeeDetails['ShiftCategory']=="Rotational"){ ?>selected="selected" <?php } ?>
				   value="Rotational">Rotational</option>
				  </select>
                </div>

				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Joining Date</label>
                    <input class="form-control" type="date"
					placeholder="Employee Name" value="<?php echo $RowEmployeeDetails['JoiningDate'] ?>" 
					id="JoiningDate" name="JoiningDate" >                 
                  </div>
				
				
				
			  
        <!-- end row -->
									</div>
									<div class="col-xs-12 box-footer"  align="right">
									 <a target="" href="EmployeeListAv1"   class="btn btn-danger  "> Close</a>
               <?php if($Count==0){ ?> <button  type="submit"  name='submit'  class="btn btn-success waves-effect waves-light">
				
			   Submit</button> <?php } else{?>
				
				<button  type="submit"  name='submit'  class="btn btn-success waves-effect waves-light">
			
			   Update</button><?php } ?>
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


<Script>
$("#BioMetricID").keyup(function() {
    $("#BioMetricID").val(this.value.match(/[0-9]*/));
});

$(document).on('change','#DeptID',function(e){
		e.preventDefault();
		
		var DeptId=$(this).val();
			$.ajax({
		  url:'Ajax/AjDropDowns',
		  type:'POST',
		   data:{'Type':'SubDept','DeptId':DeptId},
		  success:function(data){
			$("#SubDeptID").html(data);
		}
			})
    	});

/*$(document).on('change','#ShiftCategory',function(e){
		e.preventDefault();
		
		var DeptId=$(this).val();
			$.ajax({
		  url:'Ajax/AjDropDowns',
		  type:'POST',
		   data:{'Type':'SubDept','DeptId':DeptId},
		  success:function(data){
			$("#SubDeptID").html(data);
		}
			})
    	});*/
		
$(document).on('change','#ShiftCategory',function(e){
	
     var ShiftCategory =$(this).val(); 
	 
	
		 if(ShiftCategory=='Rotational'){
			 // $('#myModal1').modal('toggle');
		 }

 });

   

</script>








