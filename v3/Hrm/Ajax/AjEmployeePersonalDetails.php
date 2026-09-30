<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$request = $_REQUEST;
$columns = ['BioMetricID', 'EmpName', 'PhoneNumber'];

$limit  = intval($request['length']);
$offset = intval($request['start']);

$Type      = $request['Type'];
$DeptID    = intval($request['DeptID']);
$SubDeptID = intval($request['SubDeptID']);
$Type=$_POST['Type'];
$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];


if($Type=='Dept'){

	$SqlSubDept="SELECT SubDeptID,SubDeptName FROM HrmSubDepartments WHERE `DeptID`='$DeptID'";
	$StmtSubDept=$iconn->query($SqlSubDept);	
?>	
			<option value="">Select Sub Departments</option> 

			<?php	while($row=$StmtSubDept->fetch_array()){ ?>
			<option value="<?php echo $row["SubDeptID"]; ?>"><?php echo $row["SubDeptName"]; ?></option>
			<?php   } 
}else {

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
    } else if ($orderColumn == "PhoneNumber") {
        $orderBy = " order by `PersonalDetails`.`" . $orderColumn . "` " . $orderType;
    }

    // Combine conditions with AND
    $searchQuery  = count($searchQueryParts) > 0 ? ' and ' . implode(' AND ', $searchQueryParts) : '';
    $totalRecords = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];

/*SELECT *,EmpList.BioMetricID as BioMetricID ,EmpList.EmpName as EmpName,
 EmpList.EmpID as EmployeeId from
HrmEmployeeDetails as EmpList
LEFT JOIN HrmEmpPersonalDetails as PersonalDetails ON PersonalDetails.EmpID=EmpList.EmpID
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID 
where EmpList.EmpStatus='1'
$SubQuery*/

    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
	            HrmEmployeeDetails as EmpList
LEFT JOIN HrmEmpPersonalDetails as PersonalDetails ON PersonalDetails.EmpID=EmpList.EmpID
LEFT JOIN HrmDepartments as DeptList ON DeptList.DeptID=EmpList.DeptID 
LEFT JOIN HrmSubDepartments as SubDept ON SubDept.SubDeptId=EmpList.SubDeptID 
LEFT JOIN HrmDesignations as Desgn ON Desgn.DesigID=EmpList.DesigID 
where EmpList.EmpStatus='1'
	$searchQuery")->fetch_assoc()['count'];
    // Fetch data

    $data = [];
    $sql  = "SELECT `PersonalDetails`.`PhoneNumber`,`PersonalDetails`.`AadharNumber`,`PersonalDetails`.`PANNo`,`PersonalDetails`.`ESICNo`,`PersonalDetails`.`PresentAddress`,EmpList.BioMetricID as BioMetricID ,EmpList.EmpName as EmpName,
 EmpList.EmpID as EmployeeId from
	HrmEmployeeDetails as EmpList
LEFT JOIN HrmEmpPersonalDetails as PersonalDetails ON PersonalDetails.EmpID=EmpList.EmpID
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
            $singlerow['PhoneNumber'] = $row['PhoneNumber'];
            $singlerow['AadharNumber']    = $row['AadharNumber'];
            $singlerow['PANNo']        = $row['PANNo'];
            $singlerow['ESICNo'] = $row['ESICNo'];
            $singlerow['PresentAddress'] = $row['PresentAddress'];
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
            $singlerow['Tools'] = '
            <a href="EmployeePersonalDetailsEdit?EmpID='.$row['EmployeeId'].'" 
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
										class="table dt-responsive nowrap w-100">
                                            <thead>
											<tr>
                  <th>Bio Id </th>
                  <th>Name</th>
				  <th>Phone Number</th>
				  <th>Aadhar Number</th>
				  <th>PAN Number</th>
				  <th>ESIC</th>
				  <th>Address</th>

				  
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
	
  $SqlEmpList = "SELECT *,EmpList.BioMetricID as BioMetricID ,EmpList.EmpName as EmpName,
 EmpList.EmpID as EmployeeId from 
HrmEmployeeDetails as EmpList
LEFT JOIN HrmEmpPersonalDetails as PersonalDetails ON PersonalDetails.EmpID=EmpList.EmpID
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
						  <td><?php echo $row['PhoneNumber']; ?></td>
					 <td><?php echo $row['AadharNumber']; ?></td> 
						  <td><?php echo $row['PANNo']; ?></td>                         
						  <td><?php echo $row['ESICNo']; ?></td>
                          <td><?php echo $row['PresentAddress']; ?></td>						  
						  
                          
                        
						
						
                          <td>
						
						 <a target="_blank" href="EmployeePersonalDetailsEdit?EmpID=<?php  echo $row['EmployeeId'];?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
						 
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