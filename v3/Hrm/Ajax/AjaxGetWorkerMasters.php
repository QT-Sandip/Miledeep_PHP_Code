<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$request = $_REQUEST;
$columns = ['BioMetricID', 'EmpName'];

$limit = intval($request['length']);
$offset = intval($request['start']);
$Type = $request['Type'];
$MonthSegment = intval($request['monthsegment']);

$searchValue = $iconn->real_escape_string($request['search']['value']);

$searchQueryParts = [];

if($Type == "Village"){
    $TableData['TableName']="EmpWorkerVillageMaster";
    $TableData['Field']="VillageName";    
    $TableData['ID']="VID";    
}else if($Type == "Batch"){
    $TableData['TableName']="EmpWorkerBatchMaster";
    $TableData['Field']="BatchName";    
    $TableData['ID']="BatchID";    
} else if($Type == "Section"){
    $TableData['TableName']="EmpWorkerSectionsMaster";
    $TableData['Field']="SectionName";    
    $TableData['ID']="SectionID";    
} 

if (!empty($searchValue)) {
    $searchQueryParts[] = "($columns[0] LIKE '%$searchValue%' OR $columns[1] LIKE '%$searchValue%' OR $columns[2] LIKE '%$searchValue%')";
}

if (!empty($EmpCategory)) {
    $searchQueryParts[] = "HRD.EmpCategory = '$EmpCategory'";
}

if (!empty($WorkerSubCategoryID)) {
    $searchQueryParts[] = "HRD.WorkerSubCategory = $WorkerSubCategoryID";
}

if (!empty($DeptID)) {
    $searchQueryParts[] = "HRD.DeptID = $DeptID";
}

if (!empty($SubDeptID)) {
    $searchQueryParts[] = "HRD.SubDeptID = $SubDeptID";
}

// if (!empty($MonthSegment)) {
//     $searchQueryParts[] = "Deductions.MonthSegment = $MonthSegment";
// }

// Combine conditions with AND
$searchQuery = count($searchQueryParts) > 0 ? 'WHERE ' . implode(' AND ', $searchQueryParts) : '';

// $searchQuery = $searchValue ?
//     "WHERE $columns[0] LIKE '%$searchValue%' OR $columns[1] LIKE '%$searchValue%' OR $columns[2] LIKE '%$searchValue%'" 
//     : '';

// Total records
$totalRecords = $iconn->query("SELECT COUNT(*) as count FROM {$TableData['TableName']}")->fetch_assoc()['count'];
$totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
            {$TableData['TableName']} $searchQuery")->fetch_assoc()['count'];

// Fetch data
$data = [];
$sql = "SELECT {$TableData['ID']} as ID,{$TableData['Field']} as Name
        FROM
            {$TableData['TableName']}
        ORDER BY  {$TableData['ID']} ASC
        LIMIT $offset, $limit";
        $result = $iconn->query($sql);

        // Only proceed if query is successful and has rows
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Editable fields
                $empId = $row['ID'];
                // Display ID with editable span
                $row['ID'] = '<span data-pk="' . $empId . '" data-name="EmpName" data-url="update.php">' . $empId . '</span>';
                // Editable field
                $row['Field'] = '<span data-pk="' . $row['Name'] . '" data-name="EmpName" data-url="update.php">' . $row['Name'] . '</span>';
                // DT_RowAttr
                $row['DT_RowAttr'] = [
                    'data-empid' => $empId
                ];
                // Button — use raw ID, not the <span>
                $row['edit_button'] = '<button class="btn btn-sm btn-primary edit-btn" data-id="' . $empId . '">Edit</button>
                        <button type="button" id="btnDelete" name="btnDelete" data-id="' . $empId . '" class="btn btn-link p-0 deleteWorkerMaster">
                        <i class="mdi mdi-delete font-size-24 text-danger"></i>
                    </button>           ';
                $data[] = $row;
            }
        }
        // Output JSON in DataTables format
        echo json_encode([
            "draw" => intval($request['draw']),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data" => $data // could be empty, and that's okay
        ]);
