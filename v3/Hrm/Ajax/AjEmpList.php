<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $Type=$_POST['Type']; 
$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$EmpStatus=$_POST['EmpStatus'];
$EmpCategory=$_POST['EmpCategory'];


if($Type=='Dept'){

	$SqlSubDept="SELECT * FROM HrmSubDepartments WHERE `DeptID`='$DeptID'";
	$StmtSubDept=$iconn->query($SqlSubDept);
?>
			<option value="">Select Sub Departments</option>

			<?php	while($row=$StmtSubDept->fetch_array()){ ?>
			<option value="<?php echo $row["SubDeptID"]; ?>"><?php echo $row["SubDeptName"]; ?></option>
			<?php   }
}else {

	if($_POST['EmpID'] !=''){

		$EmpID = $_POST['EmpID'];




		if($Type =='EmpStatus'){
		$EmpStatus=$_POST['EmpStatus'];

		if($EmpStatus=='0'){
			$EmpStatus='1';
		}else if($EmpStatus=='1'){
			$EmpStatus='0';
		}
		$oldEmp = legacyAuditFetchRowById('HrmEmployeeDetails', 'EmpID', $EmpID, 'Hrm');
		 $SqlEmpUpdate = "UPDATE HrmEmployeeDetails SET `EmpStatus`='$EmpStatus' WHERE `EmpID`='$EmpID'";
		}


	else	if($Type=='DeleteEmployee'){
		$oldEmp = legacyAuditFetchRowById('HrmEmployeeDetails', 'EmpID', $EmpID, 'Hrm');
			 $SqlEmpUpdate = "Delete from `HrmEmployeeDetails`  WHERE `EmpID`='$EmpID'";
		}

		$StmtEmpUpdate = $iconn->query($SqlEmpUpdate);
		if($StmtEmpUpdate){
			if($Type =='EmpStatus'){
				legacyAuditLogUpdateById('HrmEmployeeDetails', 'EmpID', $EmpID, $oldEmp, $_POST, ['EmpID' => $EmpID, 'Remarks' => 'Employee status toggled'], 'Hrm');
			}else if($Type=='DeleteEmployee'){
				legacyAuditLogDeleteById('HrmEmployeeDetails', 'EmpID', $EmpID, $oldEmp, $_POST, ['EmpID' => $EmpID, 'Remarks' => 'Employee deleted'], 'Hrm');
			}
		}




	}

?>



      <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >


<thead class="thead-light">

                  <th>Bio Id </th>
				  <th>Name</th>
				  <th>Category</th>
				  <th>Department</th>
                  <th>SubDepartment </th>
                  <th>Designation</th>
                  <th>Contractor</th>
				  <th>Status</th>
				  <th >Tools</th>

                </thead>
                <tbody>

<?php
$SubQuery = " 1 ";

if($EmpCategory!=''){
    $SubQuery .= " AND EmpCategory = '".$EmpCategory."' ";
}

if($SubDeptID!=''){

    if($EmpStatus!=''){
        $SubQuery .= " AND SubDept.SubDeptID='$SubDeptID' AND EmpStatus='$EmpStatus'";
    }
    else{
        $SubQuery .= " AND SubDept.SubDeptID='$SubDeptID'";
    }

}else if($DeptID !=''){

    if($EmpStatus!=''){
        $SubQuery .= " AND DeptList.DeptID='$DeptID' AND EmpStatus='$EmpStatus'";
    }
    else{
        $SubQuery .= " AND DeptList.DeptID='$DeptID'";
    }

}else{

    if($EmpStatus!=''){
        $SubQuery .= " AND EmpStatus='$EmpStatus'";
    }
}

$SqlEmpList = "SELECT * from
HrmEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID
LEFT JOIN HrmContractorList as HCL ON HCL.ContractorID=EmpList.ContractorID
$SubQuery ";

//echo $SqlEmpList;

                    $query = $iconn->query($SqlEmpList);
                    if($query){
                    while($row = $query->fetch_array()){ ?>

						 <tr>
                          <td><?php echo $row['BioMetricID']; ?></td>
						  <td><?php echo $row['EmpName']; ?></td>
						  <td><?php echo $row['EmpCategory']; ?></td>
						  <td><?php echo $row['DepartmentName']; ?></td>
						  <td><?php echo $row['SubDeptName']; ?></td>                         
                          <td><?php echo $row['Designation']; ?></td>
                          <td><?php echo $row['ContractorName']; ?></td>
						<td>
						<div class="button-items">
						<?php if($row['EmpStatus']=='1'){ ?>
						
						
             

						
					   <button class="btn btn-link badge badge-success font-size-12 EmpActive"
					   type="button"
					 data-toggle="popover" data-trigger="focus" title="The person is currently  working" 
					 data-content="The person is currently  working"
								data-id="<?php echo $row['EmpID']; ?>"  id="act<?php echo $row['EmpID']; ?>"
								value="<?php echo $row['EmpStatus']; ?>">Active</button>
                          
					<?php } else { ?>
					
											

					 <button  class="btn btn-link badge badge-warning font-size-12 EmpActive" 
					 data-toggle="popover" data-trigger="focus" title="The person is currrently Not working" data-content="The person is currrently Not working"
					 
					 data-id="<?php echo $row['EmpID']; ?>" id="inact<?php echo $row['EmpID']; ?>" 
							 value="<?php echo $row['EmpStatus']; ?>">
							 In Active
							 </button>
                          
						<?php } ?>
						</div>
						</td>
						
                          <td>
						
						<!-- <a target="_blank" href="EmployeeOnDutyCreate?EmpID=<?php  echo $row['BioMetricID'];?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>-->
                         
						 <a target="_blank" href="EmployeeCreate?EmpID=<?php  echo $row['EmpID'];?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                        
						<a href="CreateAllEmployeeDetails?EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>
                      

			<button class="btn btn-link text-danger Delete" data-id="<?php echo $row['EmpID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value="<?php echo $row['EmpStatus']; ?>"
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
				</td>
				
				
				
				
				
				
                        </tr>
                      <?php
                    }
                }
                  ?>
                </tbody>
              </table>
           <?php
}


?>
