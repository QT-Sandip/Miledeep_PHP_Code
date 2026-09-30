

<?php

// error_reporting(E_ALL);
// error_reporting(-1);
// ini_set('error_reporting', E_ALL);


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $Type=$_POST['Type']; 
$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$Month=sprintf('%02d',$_POST['Month']);
$Year=$_POST['Year'];

$StartDate=$Year .'-'.$Month.'-01';


if($Type=='Dept'){

	$SqlSubDept="SELECT * FROM HrmSubDepartments WHERE `DeptID`='$DeptID'";
	$StmtSubDept=$iconn->query($SqlSubDept);	
?>	
			<option value="">Select Sub Departments</option> 

			<?php	while($row=$StmtSubDept->fetch_array()){ ?>
			<option value="<?php echo $row["SubDeptID"]; ?>"><?php echo $row["SubDeptName"]; ?></option>
			<?php   } 
}else {
	
	


?>
	
	
   
          <!--   <table id="scroll-vertical-datatable" 
										class="table table-striped 
										table-bordered dt-responsive nowrap">-->
	<table  id="key-datatable" data-paging="false" class="table  nowrap w-100" >										
		
                <thead>
				<tr>
                  <th>Bio Id </th>
				  <th>Name</th>
				  <th>Attendance Start Date</th>
				  <th>Attendance End Date</th>
				  <th>Weekoff Start Date</th>				  
				  
				  <th>Shift Start</th>
				  <th>ShiftOrder</th>
				  <th>Absents(Count)</th>
				  <th>Leave(Day)</th>
				  <th>Actions</th>
				</tr>
                </thead>
                <tbody>
				
<?php 
if($SubDeptID!=''){
	$SubQuery="where SubDept.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery=" where DeptList.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
	
	
	
/*$SqlEmpList = "SELECT * from 
HrmAuditEmployeeDetails as EmpList
LEFT JOIN $HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN $HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN $HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID  where BioMetricID in($BioIDs)
$SubQuery ";*/
$SqlEmpList = "SELECT * from 
HrmAuditEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID   
$SubQuery ";


	//$Stmtshift1=$iconn->query($SqlShift);
	
                    $query = $iconn->query($SqlEmpList);
                   
                    while($row = $query->fetch_array()){
                    $BioMetricID=$row['BioMetricID'];						
					$SqlAuditBasic="SELECT * FROM `HrmAuditBasicDetails` where BioMetricID='$BioMetricID'";
					 $StmtBasicAudit=$iconn->query($SqlAuditBasic);
					$RowBasicAudit=$StmtBasicAudit->fetch_array();
					 
					 $SqlCount="SELECT * FROM  HrmAuditAttendnessAuditLog  	
					where BioMetricID='$BioMetricID' and Month(Date)='$Month' and Year(Date)='$Year'";
					$StmtCount=$iconn->query($SqlCount);
					$RowCount=$StmtCount->fetch_array();
					 $Count=mysqli_num_rows($StmtCount); 
					
				  $AttStartDate=$StartDate;
				  
				 // $AttStartDate=$RowBasicAudit['AttStartDate'];
				  $AttEndDate= "";
				  //$AttEndDate=$RowBasicAudit['AttEndDate'];
				  $WeekOffStartDate=$RowBasicAudit['WeekOffStartDate'];
				  
				  if( $RowBasicAudit['EmpAudStatus']==0){
				  $AttStartDate="";
				  $WeekOffStartDate="";
				  $EmpSatus='( In Active )';
				  }else{
					  $EmpSatus='';
				  }
				/*	<?php echo  $RowBasicAudit['AttEndDate'];?>*/
				/*	<?php echo  $AttEndDate['AttEndDate'];?>*/
	
					?> 
					
						
						 <tr>
                          <td><?php echo $row['BioMetricID']; ?></td>
						  <td><?php echo $row['EmpName'].' ' .$EmpSatus; ?></td>
						  <td> <input  type="date" class="form-control"  name="AttStartDate" id="AttStartDate<?php echo $BioMetricID; ?>"
				   value="<?php echo  $AttStartDate;?>" placeholder="Enter "></td>
				   
						 <td> <input  type="date" class="form-control"  
						 name="AttEndDate" id="AttEndDate<?php echo $BioMetricID; ?>"
				   value="<?php echo  $AttEndDate ?>" placeholder="Enter "></td>
				   
				   <td> 
				   <?php if($WeekOffStartDate!=''){ ?>
				   <input readonly type="date" class="form-control"  name="WeekOffStartDate" id="WeekOffStartDate<?php echo $BioMetricID; ?>"
				   value="<?php echo $WeekOffStartDate  ;?>" placeholder="Enter ">
				   
				   <?php }  else{ ?>
				  
				   <input  type="date" class="form-control"  name="WeekOffStartDate" id="WeekOffStartDate<?php echo $BioMetricID; ?>"
				   value="<?php echo $WeekOffStartDate  ;?>" placeholder="Enter ">
				   
				    <?php }  ?>
				   </td>
				   
						
				   

				   
						 
 <td>
						 <select  class="form-control select2"  name="StartShift" id="StartShift<?php echo $BioMetricID; ?>">

			
<option value="">Select</option>
				   <?php 
				   $SqlShift="SELECT * FROM `HrmShiftDetails`";
	               $Stmtshift = $iconn->query($SqlShift);
				   
				   while($RowShift=$Stmtshift->fetch_array()) { 
				$StartShift=$RowBasicAudit['StartShift'];
				$ShiftID=$RowShift['ShiftID'];
				?>  
				
				<option <?php  if($ShiftID==$StartShift){ ?> selected=="selected" <?php } ?>
				Value="<?php echo $RowShift['ShiftID'];?>"><?php echo $RowShift['ShiftName'];?></OPTION>
				
				<?php } ?>
				</select> 
				</td>
				
				<td>
				 <select  class="form-control select2"  name="ShiftOrder" id="ShiftOrder<?php echo $BioMetricID; ?>">
                  <option value="">Select</option>
                  <option <?php if($RowBasicAudit['ShiftOrder']=='A,B'){ ?>selected=="selected" <?php } ?>
				  value="A,B">A,B</option>
                  <option <?php if($RowBasicAudit['ShiftOrder']=='B,A'){ ?>selected=="selected" <?php } ?>
				  value="B,A">B,A</option>
				  <option <?php if($RowBasicAudit['ShiftOrder']=='A,B,C'){ ?>selected=="selected" <?php } ?>
				  value="A,B,C">A,B,C</option>
				  <option <?php if($RowBasicAudit['ShiftOrder']=='G'){ ?>selected=="selected" <?php } ?>
				  value="G">G</option>
                 
                 </select>
				</td>
				   
			<!--	<td> <input  type="text" class="form-control"  name="ShiftOrder" id="ShiftOrder<?php echo $BioMetricID; ?>"
				   value="<?php echo  $RowBasicAudit['ShiftOrder'];?>" placeholder="Enter Shift Order"></td>
				   -->
				  				   <td> <input  type="number" class="form-control"  name="AttAbsents" id="AttAbsents<?php echo $BioMetricID; ?>"
				   value="" placeholder="Enter Number of Absents "></td>
				  
				  <td> <input  type="number" class="form-control"  name="AttLeave" id="AttLeave<?php echo $BioMetricID; ?>"
				   value="" placeholder="Enter Leave Day"></td> 
				   
				<td> 
			<?php if($Count==0){ ?>
			
			<button  id="BtnAddDetails"  value="<?php echo $row["BioMetricID"]; ?>" align="Center" class="btn btn-success btn-sm btn-flat">
						 Add </button> 
						
		<?php 	} else{ ?>
			
			<button  id="BtnAddDetails"  value="<?php echo $row["BioMetricID"]; ?>" align="Center" class="btn btn-danger btn-sm btn-flat">
						 Edit </button> 
			<?php 	} ?>
			
				  </td>
				
				
				
				
				
				
				
				
                        </tr>
                      <?php
                    }
                  ?>
                </tbody>
              </table>
           <?php
}


?>
		
	
	
	