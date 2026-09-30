<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
if (1) {
    $request   = $_REQUEST;
    $columns   = ['BioMetricID', 'EmpName', 'DepartmentName'];
    $limit  = intval($request['length']);
    $offset = intval($request['start']);

    $Type      = $_POST['Type'];
    $DeptID    = $_POST['DeptID'];
    $SubDeptID = $_POST['SubDeptID'];
    $Month     = $_POST['Month'];
    $Year      = $_POST['Year'];
    if ($Type == 'Dept') {

    } else {
        $OrderBYVal  = intval($request['order'][0]['column']);
        $searchValue = $iconn->real_escape_string($request['search']['value']);

        $searchQueryParts = [];

        if (!empty($SubDeptID)) {
            $searchQueryParts[] = "ED.SubDeptID='$SubDeptID'";
        } else if (!empty($DeptID)) {
            $searchQueryParts[] = "ED.DeptID='$DeptID' ";
        }

        if (!empty($searchValue)) {
            $searchQueryParts[] = "(ED." . $columns[0] . " LIKE '%" . $searchValue . "%' OR " . $columns[1] . " LIKE '%" . $searchValue . "%' )";
        }

        $orderBy = "";

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType   = $request['order'][0]['dir'];

        if ($orderColumn == "BioMetricID") {
            $orderBy = " order by `ED`.`" . $orderColumn . "` " . $orderType;
        } else if ($orderColumn == "EmpName") {
            $orderBy = " order by `ED`.`" . $orderColumn . "` " . $orderType;
        }

        $searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';

        $totalRecords  = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];			

        $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails as ED  WHERE ED.`EmpStatus`=1  $searchQuery ")->fetch_assoc()['count'];

        $data = [];
        $sql  = "SELECT *, 
						(SELECT COUNT(*) from HrmLeaveRegister
				WHERE EmpID=ED.EmpID and LeaveType=0 and month(StartDate)='$Month'and month(EndDate)='$Month') as Cl,
						(SELECT COUNT(*) from HrmLeaveRegister WHERE EmpID=ED.EmpID and LeaveType=1 and month(StartDate)='$Month' and month(EndDate)='$Month') as SL,
						(SELECT COUNT(*) from HrmLeaveRegister WHERE EmpID=ED.EmpID and LeaveType=2 and
				 month(StartDate)='$Month' and month(EndDate)='$Month') as Other
				 		FROM HrmEmployeeDetails as ED  WHERE ED.`EmpStatus`=1  $searchQuery
			$orderBy
			LIMIT $offset, $limit";
        //echo $sql; exit;
        $result = $iconn->query($sql);
        // Only proceed if query is successful and has rows
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $CasualLeave = $row['Cl'];
		            $SickLeave   = $row['SL'];
		            $OtherLeave  = $row['Other'];
		            $DeptID      = $row['DeptID'];

		            $SqlDept        = "SELECT * FROM HrmDepartments WHERE DeptID='$DeptID'";
		            $StmtDept       = $iconn->query($SqlDept);
		            $RowDept        = $StmtDept->fetch_array();
		            $DepartmentName = $RowDept['DepartmentName'];
		            $TotalLeaves    = $CasualLeave + $SickLeave + $OtherLeave;

                $singlerow                   = array();
                $singlerow['BioMetricID']    = $row['BioMetricID'];
                $singlerow['EmpName']        = $row['EmpName'];
                $singlerow['DepartmentName'] = $DepartmentName;
                $singlerow['CasualLeave']   = $CasualLeave;
                $singlerow['SickLeave']   = $SickLeave;
                $singlerow['TotalLeaves']   = $TotalLeaves;

                $singlerow['Tools'] = '<a target="_blank" href="EmpLeaveCreate?EmpID='.$row['EmpID'].'" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>';
                $data[]             = $singlerow;
            }
        }
        echo json_encode([
            "draw"            => intval($request['draw']),
            "recordsTotal"    => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data"            => $data, // could be empty, and that's okay
        ]);
        exit;
    }
} else {
$Type=$_POST['Type']; 
$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$Month=$_POST['Month'];
$Year=$_POST['Year'];


if($Type=='Dept'){

	
}

else {
	
	if($SubDeptID!=''){
		$Query=' and SubDeptID='.$SubDeptID;	
	}
	
	else if($DeptID!=''){
		$Query='and DeptID='.$DeptID;
	}
	else{
	$Query='';	
	}
?>
	
  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
											<tr>
                  <th>Bio Id </th>
				  <th>Name</th>
				  <th>Department</th>
                  <th>CLs</th>
                  <th>SLs</th>
				  <th>Total Leaves</th>
				  <th style="width: 20%">Tools</th>
				  </tr>
                </thead>
                <tbody>
				
<?php 


  $SqlLeaveList= "
 SELECT *, (SELECT COUNT(*) from HrmLeaveRegister 
WHERE EmpID=ED.EmpID and LeaveType=0 and month(StartDate)='$Month'and month(EndDate)='$Month') as Cl, 
(SELECT COUNT(*) from HrmLeaveRegister WHERE EmpID=ED.EmpID and LeaveType=1 and month(StartDate)='$Month' and month(EndDate)='$Month') as SL, 
(SELECT COUNT(*) from HrmLeaveRegister WHERE EmpID=ED.EmpID and LeaveType=2 and
 month(StartDate)='$Month' and month(EndDate)='$Month') as Other 
 FROM HrmEmployeeDetails as ED  WHERE ED.`EmpStatus`=1 $Query"; 

                    $StmtLeaveList = $iconn->query($SqlLeaveList);
					
					
                   
                    while($RowLeaveList = $StmtLeaveList->fetch_array()){ 
					
					$CasualLeave=$RowLeaveList['Cl'];
					$SickLeave=$RowLeaveList['SL'];
					$OtherLeave=$RowLeaveList['Other'];
					$DeptID=$RowLeaveList['DeptID'];
					
					$SqlDept="SELECT * FROM HrmDepartments WHERE DeptID='$DeptID'";
					$StmtDept = $iconn->query($SqlDept);
					$RowDept = $StmtDept->fetch_array();
					$DepartmentName=$RowDept['DepartmentName'];
					$TotalLeaves=$CasualLeave+$SickLeave+$OtherLeave;
				
				 
					?>
						
						 <tr>
                          <td><?php echo $RowLeaveList['BioMetricID']; ?></td>
						  <td><?php echo $RowLeaveList['EmpName']; ?></td>
						  <td><?php echo $DepartmentName; ?></td>
						  <td><?php echo $CasualLeave; ?></td>                         
                          <td><?php echo $SickLeave; ?></td>
                          <td><?php echo $TotalLeaves; ?></td>
						
						
                          <td>
						
						 <a target="_blank" href="EmpLeaveCreate?EmpID=<?php  echo $RowLeaveList['EmpID'];?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
                         
                        
						<a href="#" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
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