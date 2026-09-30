<?php
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

$request = $_REQUEST;

$SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories`";
$SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);
$WorkerSubCategorylist = [];
while ($RowWorkerType = $SqlWorkerCategoryResult->fetch_array()){
    $WorkerSubCategorylist[$RowWorkerType['CategoryID']] = $RowWorkerType['CategoryName'];
}
$columns = ['BioMetricID', 'EmpName', 'DepartmentName', 'SubDeptName', 'Designation', 'ContractorName'];

$limit       = intval($request['length']);
$offset      = intval($request['start']);

$EmpStatus   = $request['EmpStatus'];
$EmpCategory=$request['EmpCategory'];
$WorkerCategory=$request['WorkerCategory'];
$DeptID      = intval($request['DeptID']);
$SubDeptID   = intval($request['SubDeptID']);
$OrderBYVal = intval($request['order'][0]['column']);
$searchValue = $iconn->real_escape_string($request['search']['value']);

$searchQueryParts = [];
if (!empty($EmpCategory)) {
    $searchQueryParts[] = " EmpCategory='$EmpCategory' ";
}

if (!empty($EmpCategory) && !empty($WorkerCategory)) {
    $searchQueryParts[] = " WorkerSubCategory='$WorkerCategory' ";
}

if (!empty($SubDeptID)) {
    if (!empty($EmpStatus)) {
        $searchQueryParts[] = "SubDept.SubDeptID='$SubDeptID' and EmpStatus='$EmpStatus'";
    } else {
        $searchQueryParts[] = "SubDept.SubDeptID='$SubDeptID'";
    }
} else if (!empty($DeptID)) {
    if (!empty($EmpStatus)) {
        $searchQueryParts[] = "DeptList.DeptID='$DeptID' and EmpStatus='$EmpStatus'";
    } else {
        $searchQueryParts[] = "DeptList.DeptID='$DeptID' ";
    }
} else {
    if ($EmpStatus !="") {
        $searchQueryParts[] = " EmpStatus='$EmpStatus'";
    } else {
        $searchQueryParts[] = "";
    }
}

if (!empty($searchValue)) {
    $searchQueryParts[] = "($columns[0] LIKE '%$searchValue%' OR $columns[1] LIKE '%$searchValue%' OR $columns[2] LIKE '%$searchValue%' OR $columns[3] LIKE '%$searchValue%' OR $columns[4] LIKE '%$searchValue%')";
}

$orderBy = "";


    $orderColumn = $columns[$request['order'][0]['column']];
    $orderType = $request['order'][0]['dir'];

    if($orderColumn == "BioMetricID"){
        $orderBy = " order by `EmpList`.`".$orderColumn."` ".$orderType;
    }else if($orderColumn == "EmpName"){
        $orderBy = " order by `EmpList`.`".$orderColumn."` ".$orderType;
    }else if($orderColumn == "DepartmentName"){
        $orderBy = " order by `DeptList`.`".$orderColumn."` ".$orderType;
    }else if($orderColumn == "SubDeptName"){
        $orderBy = " order by `SubDept`.`".$orderColumn."` ".$orderType;
    }else if($orderColumn == "Designation"){
        $orderBy = " order by `Desgn`.`".$orderColumn."` ".$orderType;
    }




// Combine conditions with AND
$searchQuery   = count($searchQueryParts) > 0 ? 'WHERE ' . implode(' AND ', $searchQueryParts) : '';
$totalRecords  = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];
$totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
            HrmEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID
LEFT JOIN HrmContractorList as HCL ON HCL.ContractorID=EmpList.ContractorID
$searchQuery")->fetch_assoc()['count'];
// Fetch data

$data = [];
$sql  = "SELECT BioMetricID,EmpName,DepartmentName,SubDeptName,Designation,ContractorName,EmpID,EmpStatus,EmpCategory,WorkerSubCategory from
HrmEmployeeDetails as EmpList
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID
LEFT JOIN HrmContractorList as HCL ON HCL.ContractorID=EmpList.ContractorID
$searchQuery
$orderBy
LIMIT $offset, $limit";
//echo $sql; exit;
$result = $iconn->query($sql);
// Only proceed if query is successful and has rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $singlerow                   = array();
        $singlerow['BioMetricID']    = $row['BioMetricID'];
        $singlerow['EmpName']        = $row['EmpName'];
        $singlerow['EmpCategory']        = $row['EmpCategory'];
        $singlerow['WorkerSubCategory']        = $WorkerSubCategorylist[$row['WorkerSubCategory']];
        $singlerow['DepartmentName'] = $row['DepartmentName'];
        $singlerow['SubDeptName']    = $row['SubDeptName'];
        $singlerow['Designation']    = $row['Designation'];
        $singlerow['ContractorName'] = $row['ContractorName'];
        if ($row['EmpStatus'] == '1') {
            $singlerow['Status'] = '<button class="btn btn-link badge badge-success font-size-12 EmpActive" type="button" data-toggle="popover" data-trigger="focus" title="The person is currently  working" data-content="The person is currently  working" ata-id="' . $row['EmpID'] . '"  id="act' . $row['EmpID'] . '" value="' . $row['EmpStatus'] . '" data-id="' . $row['EmpID'] . '">Active</button>';
        } else {
            $singlerow['Status'] = '<button class="btn btn-link badge badge-warning font-size-12 EmpActive" data-toggle="popover" data-trigger="focus" title="The person is currrently Not working" data-content="The person is currrently Not working" data-id="' . $row['EmpID'] . '" id="inact' . $row['EmpID'] . '" value="' . $row['EmpStatus'] . '" data-id="' . $row['EmpID'] . '">In Active</button>';
        }
        $singlerow['Tools'] = '
            <a href="EmployeeCreate?EmpID=' . $row['EmpID'] . '" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
            <a href="CreateAllEmployeeDetails?EmpID=' . $row['EmpID'] . '" class="mr-1 text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i></a>
            <a href="javascript:void(0)" class="mr-3 text-danger" data-id="' . $row['EmpID'] . '" data-toggle="tooltip" data-placement="top" title="" value="' . $row['EmpStatus'] . '" data-original-title="Delete" ><i class="mdi mdi-trash-can font-size-18"></i></a>';
        $data[] = $singlerow;
    }
}

echo json_encode([
    "draw"            => intval($request['draw']),
    "recordsTotal"    => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data"            => $data,
]);
exit;
?>
