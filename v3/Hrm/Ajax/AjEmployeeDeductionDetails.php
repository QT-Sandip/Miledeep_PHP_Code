<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$request = $_REQUEST;
$columns = ['BioMetricID', 'EmpName', 'EsiNumber', 'PFNumber'];

$limit  = intval($request['length']);
$offset = intval($request['start']);

$Type      = $request['Type'];
$DeptID    = intval($request['DeptID']);
$SubDeptID = intval($request['SubDeptID']);
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
	
	if($_POST['EmpID'] !=''){
		
		$EmpID = $_POST['EmpID'];
		
		
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
    } 

    // Combine conditions with AND
    $searchQuery  = count($searchQueryParts) > 0 ? ' and ' . implode(' AND ', $searchQueryParts) : '';
    $totalRecords = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];

/*SELECT *,EmpList.BioMetricID as BioMetricID,EmpList.EmpName as EmpName,EmpList.EmpID as EmployeeId from 
HrmEmployeeDetails as EmpList
LEFT JOIN EmpDeductionDetails as Deduction ON Deduction.EmpID=EmpList.EmpID
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID 
where EmpList.EmpStatus='1'
$SubQuery*/

    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
	            HrmEmployeeDetails as EmpList
LEFT JOIN EmpDeductionDetails as Deduction ON Deduction.EmpID=EmpList.EmpID
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID 
where EmpList.EmpStatus='1'
	$searchQuery")->fetch_assoc()['count'];
    // Fetch data

    $data = [];
    $sql  = "SELECT `Deduction`.`EsiNumber`,`Deduction`.`ESI`,`Deduction`.`PFNumber`,`Deduction`.`PFPerc`,`Deduction`.`DailyDeduction`,`Deduction`.`MonthlyDeduction`,EmpList.BioMetricID as BioMetricID,EmpList.EmpName as EmpName,EmpList.EmpID as EmployeeId from 
HrmEmployeeDetails as EmpList
LEFT JOIN EmpDeductionDetails as Deduction ON Deduction.EmpID=EmpList.EmpID
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID 
where EmpList.EmpStatus='1'
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
            $singlerow['EsiNumber'] = $row['EsiNumber'];
            $singlerow['ESI']    = $row['ESI'];
            $singlerow['PFNumber']        = $row['PFNumber'];
            $singlerow['PFPerc'] = $row['PFPerc'];
            $singlerow['DailyDeduction'] = $row['DailyDeduction'];
            $singlerow['MonthlyDeduction'] = $row['MonthlyDeduction'];            
            $singlerow['Tools'] = '<a href="EmployeeDeductionDetailsEdit?EmpID='.$row['EmployeeId'].'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>';
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
	
	
	
             <table id="scroll-vertical-datatable" 
										class="table dt-responsive wrap w-100">
                                            <thead>
											<tr>
                  <th>Bio Id </th>
				  <th>Name</th>
				  <th>Esi Number</th>
				  <th>ESI</th>
				  <th>PF Number</th>
                  <th>PF Percentage</th>
                <!--  <th>Mess Charge Per Day</th>-->
				  <th>Daily Deduction</th>
				  <th>Monthly Deduction</th>
				  
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
	
$SqlEmpList = "SELECT *,EmpList.BioMetricID as BioMetricID,EmpList.EmpName as EmpName,EmpList.EmpID as EmployeeId from 
HrmEmployeeDetails as EmpList
LEFT JOIN EmpDeductionDetails as Deduction ON Deduction.EmpID=EmpList.EmpID
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
					 <td><?php echo $row['EsiNumber']; ?></td>

						  <td><?php echo $row['ESI']; ?></td>
						  <td><?php echo $row['PFNumber']; ?></td>

						  <td><?php echo $row['PFPerc']; ?></td>                         
                          <!--<td><?php //echo $row['MessChargePerDay']; ?></td>-->
                          <td><?php echo $row['DailyDeduction']; ?></td>
                          <td><?php echo $row['MonthlyDeduction']; ?></td>
                          
						
						
                          <td>
						
						 <a target="_blank" href="EmployeeDeductionDetailsEdit?EmpID=<?php  echo $row['EmployeeId'];?>" 
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

}
?>
