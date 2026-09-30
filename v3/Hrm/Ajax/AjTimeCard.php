<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$request = $_REQUEST;
$columns = ['BioMetricID', 'EmpName', 'Designation'];

$limit  = intval($request['length']);
$offset = intval($request['start']);

$Type      = $request['Type'];
$DeptID    = intval($request['DeptID']);
$SubDeptID = intval($request['SubDeptID']);
$Month     = $request['Month'];
$Year      = $request['Year'];


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
		$oldData = legacyAuditFetchRowById('HrmEmployeeDetails', 'EmpID', $EmpID, 'Hrm');
		$SqlEmpUpdate = "UPDATE HrmEmployeeDetails SET `EmpStatus`='$EmpStatus' WHERE `EmpID`='$EmpID'";
		$StmtEmpUpdate = $iconn->query($SqlEmpUpdate);
		if($StmtEmpUpdate) legacyAuditLogUpdateById('HrmEmployeeDetails', 'EmpID', $EmpID, $oldData, null, ['EmpID' => $EmpID, 'Remarks' => 'Updated Employee Status'], 'Hrm');
		}
		
		
	else	if($Type=='DeleteEmployee'){
		$oldData = legacyAuditFetchRowById('HrmEmployeeDetails', 'EmpID', $EmpID, 'Hrm');
		$SqlEmpUpdate = "Delete from `HrmEmployeeDetails`  WHERE `EmpID`='$EmpID'";	
		$StmtEmpUpdate = $iconn->query($SqlEmpUpdate);
		if($StmtEmpUpdate) legacyAuditLogDeleteById('HrmEmployeeDetails', 'EmpID', $EmpID, $oldData, null, ['EmpID' => $EmpID, 'Remarks' => 'Deleted Employee'], 'Hrm');
		}
		
		exit;
		
		
		
	}
	$searchValue      = $iconn->real_escape_string($request['search']['value']);
    $searchQueryParts = [];

    if (!empty($SubDeptID)) {
        $searchQueryParts[] = "SubDept.SubDeptID='$SubDeptID'";
    } else if (!empty($DeptID)) {
        $searchQueryParts[] = "DeptList.DeptID='$DeptID'";
    } else {
        $searchQueryParts = [];
    }

    if (!empty($searchValue)) {
        $searchQueryParts[] = "($columns[0] LIKE '%$searchValue%' OR $columns[1] LIKE '%$searchValue%' OR $columns[2] LIKE '%$searchValue%')";
    }

    $orderBy = "";

    $orderColumn = $columns[$request['order'][0]['column']];
    $orderType   = $request['order'][0]['dir'];

    if ($orderColumn == "BioMetricID") {
        $orderBy = " order by `EmpList`.`" . $orderColumn . "` " . $orderType;
    } else if ($orderColumn == "EmpName") {
        $orderBy = " order by `EmpList`.`" . $orderColumn . "` " . $orderType;
    } else if ($orderColumn == "Designation") {
        $orderBy = " order by `Desgn`.`" . $orderColumn . "` " . $orderType;
    }

    // Combine conditions with AND
    $searchQuery  = count($searchQueryParts) > 0 ? ' and ' . implode(' AND ', $searchQueryParts) : '';
    $totalRecords = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];

/*SELECT * from
HrmEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID Where EmpStatus ='1'
$SubQuery*/

    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
	            HrmEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID Where EmpStatus ='1'
	$searchQuery")->fetch_assoc()['count'];
    // Fetch data

    $data = [];
    $sql  = "SELECT  EmpList.BioMetricID,EmpList.EmpName,DeptList.DepartmentName,EmpList.EmpStatus,EmpList.EmpID from
	HrmEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID Where EmpStatus ='1'
	$searchQuery
	$orderBy
	LIMIT $offset, $limit";
    //echo $sql;exit;
    $result = $iconn->query($sql);
    // Only proceed if query is successful and has rows
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $singlerow                   = array();
            $singlerow['BioMetricID']    = $row['BioMetricID'];
            $singlerow['EmpName']        = $row['EmpName'];
            $singlerow['DepartmentName'] = $row['DepartmentName'];
            if ($row['EmpStatus'] == '1') {
                $singlerow['EmpStatus'] = '<button class="btn btn-link badge badge-success font-size-12 EmpActive" type="button"
					 data-toggle="popover" data-trigger="focus" title="The person is currrently  working"
					 data-content="The person is currrently  working"
								data-id="' . $row['EmpID'] . '"  id="act' . $row['EmpID'] . '"
								value="' . $row['EmpStatus'] . '">Active</button>';
            } else {
                $singlerow['EmpStatus'] = '<button  class="btn btn-link badge badge-warning font-size-12 EmpActive"
					 data-toggle="popover" data-trigger="focus" title="The person is currrently Not working" data-content="The person is currrently Not working"

					 data-id="' . $row['EmpID'] . '" id="inact' . $row['EmpID'] . '"
							 value="' . $row['EmpStatus'] . '">
							 In Active
							 </button>';
            }
            $singlerow['Tools'] = '<a target="blank" href="TimeCardPrint?Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $row['EmpID'] . '" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Timecard" title="Timecard" data-original-title="View"><i class="dripicons-print font-size-18"></i>
						 </a>
						 <a target="blank" href="PunchesReport.php?Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $row['EmpID'] . '" class="mr-3 text-warning" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Punching Report" title="Punching Report"  data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>';
            $data[] = $singlerow;
        }
    }
    // Output JSON in DataTables format
    echo json_encode([
        "draw"            => intval($request['draw']),
        "recordsTotal"    => $totalRecords,
        "recordsFiltered" => $totalFiltered,
        "data"            => $data, // could be empty, and that's okay
    ]);
    exit;

    if(0){

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

if($SubDeptID!=''){
	$SubQuery=" and  SubDept.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery=" and  DeptList.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
	
$SqlEmpList = "SELECT * from 
HrmEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID Where EmpStatus ='1'
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
						
						  
						<!--<a target="blank" href="TimeCardPrintE?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>-->
                      

			
						
						  
						<a target="blank" href="TimeCardPrint?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Timecard" title="Timecard" data-original-title="View"><i class="dripicons-print font-size-18"></i>
						 </a>
						 
						<!-- <a target="blank" href="TimeCardAuditPrint.php?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Timecard Audit Report" title="Timecard Audit Report"  data-original-title="View"><i class="dripicons-print font-size-18"></i>
						 </a> -->
						 
						 <a target="blank" href="PunchesReport.php?Month=<?php  echo $Month;?>&Year=<?php  echo $Year;?>&EmpID=<?php  echo $row['EmpID'];?>" class="mr-3 text-warning" data-toggle="tooltip" data-placement="top"
						 data-toggle="popover" data-content="Punching Report" title="Punching Report"  data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>
                      

				</td>
				
				
				
				
				
				
                        </tr>
                      <?php
                    }
                  ?>
                </tbody>
              </table>
           <?php
       }
}


?>
