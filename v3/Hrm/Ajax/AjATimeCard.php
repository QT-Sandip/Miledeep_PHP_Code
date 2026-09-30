<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $Type=$_POST['Type']; 
$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$Month=$_POST['Month'];
$Year=$_POST['Year'];


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
				//$Type=$_POST['Type'];

		
		
		if($Type =='EmpStatus'){
		$EmpStatus=$_POST['EmpStatus'];
		
		if($EmpStatus=='0'){
			$EmpStatus='1';
		}else if($EmpStatus=='1'){
			$EmpStatus='0';
		}
		$oldAuditEmp = legacyAuditFetchRowById('HrmAuditEmployeeDetails', 'EmpID', $EmpID, 'Hrm');
		 $SqlEmpUpdate = "UPDATE HrmAuditEmployeeDetails SET `EmpStatus`='$EmpStatus' WHERE `EmpID`='$EmpID'";
		}
		
		
	else	if($Type=='DeleteEmployee'){
		$oldAuditEmp = legacyAuditFetchRowById('HrmAuditEmployeeDetails', 'EmpID', $EmpID, 'Hrm');
			 $SqlEmpUpdate = "Delete from `HrmAuditEmployeeDetails`  WHERE `EmpID`='$EmpID'";	
		}
		
		$StmtEmpUpdate = $iconn->query($SqlEmpUpdate);
		if($StmtEmpUpdate){
			if($Type =='EmpStatus'){
				legacyAuditLogUpdateById('HrmAuditEmployeeDetails', 'EmpID', $EmpID, $oldAuditEmp, $_POST, ['EmpID' => $EmpID, 'Remarks' => 'Employee status toggled'], 'Hrm');
			}else if($Type=='DeleteEmployee'){
				legacyAuditLogDeleteById('HrmAuditEmployeeDetails', 'EmpID', $EmpID, $oldAuditEmp, $_POST, ['EmpID' => $EmpID, 'Remarks' => 'Employee deleted'], 'Hrm');
			}
		}
		
		
		
		
	}

?>
	
	
	
              <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
											<tr>
                  <th>Bio Id </th>
				  <th>Name</th>
				  <th>Department</th>
                 
				  <th>Status</th>
				  <th style="width: 20%">Tools</th>
				  </tr>
                </thead>
                <tbody>
				
<?php 

$BioIDs="2, 3, 11, 12, 13, 14, 15, 16, 17, 18, 26, 27, 28, 30, 40, 41, 42, 61, 62, 63, 64, 65, 66, 67, 69, 77, 78, 83, 85, 86, 87, 89, 201, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 217, 218, 219, 220, 222, 223, 224, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 240, 242, 243, 248, 249, 252, 268, 270, 271, 273, 275, 277, 285, 293, 702, 704, 705, 706, 708, 710, 712, 713, 714, 715, 716, 717, 718, 719, 851, 852, 853, 854, 855, 859, 862, 1001, 1002, 1003, 1005, 1006, 1007, 1009, 1051, 1052, 1053, 1054, 1055, 1056, 1058, 1059, 1061, 1062, 1063, 1064, 1065, 1066, 1067, 1068, 1070, 1073, 1081, 1090, 1094, 1097, 1251, 1252, 1259, 1301, 1302, 1304, 1306, 1307, 1309, 1311, 1312, 1352, 1354, 1356, 1359, 1401, 1402, 1403, 1404, 1405, 1406, 1407, 1408, 1409, 1410, 1412, 1414, 1417, 1501, 1504, 1512, 1518, 1520, 1714, 1716, 1717, 1718, 1719, 1721, 1722, 1723, 1724, 1736, 1748, 1749, 1750, 1751, 1753, 1754, 1755, 1756, 1757, 1759, 1760, 1763, 1764, 1767, 1768, 1771, 1772, 1773, 1774, 1776, 1778, 1779, 1780, 1782, 1783, 1784, 1785, 1786, 1787, 1792, 1793, 1794, 1795, 1796, 1797, 1798, 1800, 1803, 1808, 1809, 1813, 1814, 1904, 2381, 2501, 2503, 2505, 2509, 2520, 2521, 2523, 2527, 2900, 3637, 3668, 3672, 3682, 3684, 3691, 3692, 3710, 3717, 3723, 3724, 3726, 3733, 3735, 3736, 3737, 3738, 3762, 3807, 3818, 6501, 6502, 6503, 6504, 6505, 6506, 6507, 6508, 6509, 6510, 6511, 6512, 6513, 6514, 6515, 6516, 6517, 6518, 6519, 6520, 6521, 6522, 6523, 6524, 6525, 6526, 6527, 6528, 6529, 6530, 6531, 6532, 6533, 6534, 6536, 6537, 6538, 6539, 6540, 6541, 6542, 6543, 6544, 6545, 6546, 6547, 6548, 6549, 6550, 6551, 6552, 6553, 6554, 6555, 6556, 6557, 6558, 6559, 6560, 6561, 6562, 6563, 6564, 6565, 6566, 6567, 6568, 6569, 6570, 6571, 6572, 6573, 6574, 6575, 6576, 6577, 6578, 6579, 6580, 6581, 6582, 6583, 6584, 6585, 6586, 6587, 6588, 6589";

if($SubDeptID!=''){
	$SubQuery="and SubDept.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery="and DeptList.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
/*	
$SqlEmpList = "SELECT * from 
HrmAuditEmployeeDetails as EmpList
LEFT JOIN $HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN $HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN $HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID  where BioMetricID in($BioIDs)
$SubQuery ";
*/

$SqlEmpList = "SELECT * from 
HrmAuditEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID WHERE EmpList.EmpStatus=1
$SubQuery ";

                    $query = $iconn->query($SqlEmpList);
                   
                    while($row = $query->fetch_array()){ ?>
						
						 <tr>
                          <td><?php echo $row['BioMetricID']; ?></td>
						  <td><?php echo $row['EmpName']; ?></td>
						  <td><?php echo $row['DepartmentName']; ?></td>
						 
						<td>
						<div class="button-items">
						<?php if($row['EmpStatus']=='1'){ ?>
						
						
             

						
					   <button class="btn btn-link badge badge-success font-size-12 EmpActive"
					   type="button"
					 data-toggle="popover" data-trigger="focus" title="The person is currrently  working" 
					 data-content="The person is currrently  working"
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
						
						  
					<!--<a target="blank" href="#" id="TimecardPopup" data-id="<?php echo $row['EmpID'];?>"
						class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-16"></i>
						 </a>  -->
						 
						 <?php
						 if($_SESSION['UserID'] != '430' && $_SESSION['UserID'] != '441')
						 {  
						 ?>
						 <a target="blank" href="TimeCardPrintE?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>
						<?php } ?>
                      

			
						
						  
						<!--<a target="blank" href="TimeCardPrint?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Timecard" title="Timecard" data-original-title="View"><i class="dripicons-print font-size-18"></i>
						 </a> -->
						 <?php
						 if($_SESSION['UserID'] != '430' && $_SESSION['UserID'] != '441')
						 {  
						 ?>						 
						 <a target="blank" href="TimeCardAPrint.php?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Timecard Audit Report" title="Timecard Audit Report"  data-original-title="View"><i class="dripicons-print font-size-18"></i>
						 </a> 
						<?php } ?>	
						 
						 <a target="blank" href="TimeCardAPrintPdf.php?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Timecard Audit Report" title="Timecard Audit Report"  data-original-title="View"><i class="dripicons-print font-size-18"></i>
						 </a> 
						 
						<!-- <a target="blank" href="PunchesReport.php?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-warning" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Punching Report" title="Punching Report"  data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a> -->
                      

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
