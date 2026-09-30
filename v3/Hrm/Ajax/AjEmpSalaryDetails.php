<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$request = $_REQUEST;
$columns = ['BioMetricID', 'EmpName', 'Designation'];

$limit       = intval($request['length']);
$offset      = intval($request['start']);

$Type      = $request['Type'];
$DeptID    = intval($request['DeptID']);
$SubDeptID = intval($request['SubDeptID']);

if ($Type == 'Dept') {

    $SqlSubDept  = "SELECT SubDeptID,SubDeptName FROM HrmSubDepartments WHERE `DeptID`='$DeptID'";
    $StmtSubDept = $iconn->query($SqlSubDept);?>
		<option value="">Select Sub Departments</option>
		<?php	while ($row = $StmtSubDept->fetch_array()) {?>
				<option value="<?php echo $row["SubDeptID"]; ?>"><?php echo $row["SubDeptName"]; ?></option>
			<?php }
} else {

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
    $searchQuery   = count($searchQueryParts) > 0 ? ' and ' . implode(' AND ', $searchQueryParts) : '';
    $totalRecords  = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];

/*SELECT *,EmpList.BioMetricID as BioMetricID,EmpList.EmpName as EmpName,EmpList.EmpID as EmployeeId from 
HrmEmployeeDetails as EmpList
LEFT JOIN HrmEmpSalaryDetails as SalaryDetails ON SalaryDetails.EmpID=EmpList.EmpID
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID 
where EmpList.EmpStatus='1'*/

    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
	            HrmEmployeeDetails as EmpList
LEFT JOIN HrmEmpSalaryDetails as SalaryDetails ON SalaryDetails.EmpID=EmpList.EmpID
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID 
where EmpList.EmpStatus='1'
	$searchQuery")->fetch_assoc()['count'];
    // Fetch data

    $data = [];
    $sql  = "SELECT *,EmpList.BioMetricID as BioMetricID,EmpList.EmpName as EmpName,EmpList.EmpID as EmployeeId from
	HrmEmployeeDetails as EmpList
LEFT JOIN HrmEmpSalaryDetails as SalaryDetails ON SalaryDetails.EmpID=EmpList.EmpID
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
            $singlerow['Designation']        = $row['Designation'];
            $singlerow['Basic'] = $row['Basic'];
            $singlerow['HRA']    = $row['HRA'];
            $singlerow['DailyAllowance']    = $row['DailyAllowance'];                        
            $singlerow['SpecialAllowance']        = $row['SpecialAllowance'];
            $singlerow['MedicalAllowance'] = $row['MedicalAllowance'];
            $singlerow['Conveyence']    = $row['Conveyence'];
            $singlerow['Incentives']    = $row['Incentives'];                        
            $singlerow['Others']    = $row['Others'];                        
            $singlerow['Tools'] = '<a target="_blank" href="EmployeeSalaryDetailsEdit?EmpID='.$row['EmployeeId'].'" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>';
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

}
?>