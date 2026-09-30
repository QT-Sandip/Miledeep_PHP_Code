<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

		$Type=$_POST['Type'];
		$SubDeptId=$_POST['SubDeptId'];
		$DESIG_ID=$_POST['DESIG_ID'];
		$SUB_DEPT_CODE=$_POST['SUB_DEPT_CODE'];
	
	if($Type=='SubDept'){
		$DeptId=$_POST['DeptId'];
		 $SqlSubDept="SELECT * FROM HrmSubDepartments WHERE `DeptID`='$DeptId'";
		$StmtSubDept=$iconn->query($SqlSubDept);?>	
		
		<option value="">All Sub Departments</option> 
				
			<?php
			if($SubDeptId==''){
				while($RowSubData=$StmtSubDept->fetch_array()){ ?>	
						
					<option value="<?php echo $RowSubData["SubDeptID"]; ?>">
					<?php echo $RowSubData["SubDeptName"]; ?></option>
				<?php }
				}else{	
				while($RowSubData=$StmtSubDept->fetch_array()){ ?>
					
					<option <?php if($RowSubData['SubDeptID']==$SubDeptId){ ?> selected="selected" <?php }?>
					value="<?php echo $RowSubData["SubDeptID"]; ?>"><?php echo $RowSubData["SubDeptName"]; ?>
					</option>
		
					<?php 						}	}			
					}

	
else	if($Type=='Desig'){
		  // $sql="SELECT * FROM `HRM_DESIGNATION` WHERE SUB_ID='$SUB_DEPT_CODE'"; 
		   $sql="SELECT * FROM `HrmDesignations`"; 
 

	$stmt=$iconn->query($sql);

	
	
?>	<option value="">All Designations</option> <?php

				
	
					
						
						if($DESIG_ID==''){
												while($row=$stmt->fetch_array()){

						?>	
						
					<option value="<?php echo $row["DesigID"]; ?>"><?php echo $row["Designation"]; ?></option>
				<?php  

												}
												
						}else {					while($row=$stmt->fetch_array()){

							
							?>
					<option <?php if($row['DesigID']==$DESIG_ID){ ?> selected="selected" <?php }?> value="<?php echo $row["DesigID"]; ?>"><?php echo $row["Designation"]; ?></option>
		
					<?php 						}				}



						}
						
						
?>