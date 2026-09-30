<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Type=$_POST['Type'];
$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];


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
	
	
	
            <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										

                                            <thead class="thead-light">
											<tr>
                  <th>Bio Id </th>
                  <th>Name</th>
                  <th>Bank Name</th>
				  <th>Account Number</th>
				  <th>IFSC Code</th>
				  
				  <th >Tools</th>
				  </tr>
                </thead>
                <tbody>
				
                  
			      
<?php 

if($SubDeptID!=''){
	$SubQuery="and SubDept.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery="and DeptList.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
	
  $SqlEmpList = "SELECT *,EmpList.BioMetricID as BioMetricID,EmpList.EmpName as EmpName,EmpList.EmpID as EmployeeId  
 from HrmEmployeeDetails as EmpList
LEFT JOIN HrmEmpBankAccountDetails as BankDetails ON BankDetails.EmpID=EmpList.EmpID
LEFT JOIN AdminBankDetails as AdminBankDetails ON AdminBankDetails.ID=BankDetails.BankID
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID 
where EmpList.EmpStatus='1' 
$SubQuery "; 



                    $query = $iconn->query($SqlEmpList);
                   
                    while($row = $query->fetch_array()){ ?>
						
						 <tr>
                          <td><?php echo $row['BioMetricID']; ?></td>
						  <td><?php echo $row['EmpName']; ?></td>
						  <td><?php echo $row['BankName']; ?></td>
						  <td><?php echo $row['AccNumber']; ?></td>
						  <td><?php echo $row['IFSC']; ?></td>                         
                          
                        
						
						
                          <td>
						
						 <a target="_blank" href="EmployeeBankDetailsEdit?EmpID=<?php  echo $row['EmployeeId'];?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                        						
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