<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

if (1) {
    $request = $_REQUEST;
    $columns = ['BioMetricID', 'EmpName', 'DepartmentName'];

    $DATE  = $_POST['CURRDATE'];
    $Month = $_POST['Month'];
    $Year  = $_POST['Year'];

    $limit  = intval($request['length']);
    $offset = intval($request['start']);

    $date_start = $Date;
    $date_end   = $date_start . ' 23:59:59';

    $DateFilter = "Between '$date_start' and '$date_end'";

    $DeptID      = intval($request['DeptID']);
    $SubDeptID   = intval($request['SubDeptID']);
    $OrderBYVal  = intval($request['order'][0]['column']);
    $searchValue = $iconn->real_escape_string($request['search']['value']);

    $searchQueryParts = [];

    if (!empty($SubDeptID)) {
        $searchQueryParts[] = "HrmEmployeeDetails.SubDeptID='$SubDeptID'";
    } else if (!empty($DeptID)) {
        $searchQueryParts[] = "HrmEmployeeDetails.DeptID='$DeptID' ";
    }

    if (!empty($searchValue)) {
        $searchQueryParts[] = "(HrmEmployeeDetails." . $columns[0] . " LIKE '%" . $searchValue . "%' OR HrmEmployeeDetails." . $columns[1] . " LIKE '%" . $searchValue . "%' OR " . $columns[2] . " LIKE '%" . $searchValue . "%')";
    }

    $orderBy = "";

    $orderColumn = $columns[$request['order'][0]['column']];
    $orderType   = $request['order'][0]['dir'];

    if ($orderColumn == "BioMetricID") {
        $orderBy = " order by `HrmEmployeeDetails`.`" . $orderColumn . "` " . $orderType;
    } else if ($orderColumn == "EmpName") {
        $orderBy = " order by `HrmEmployeeDetails`.`" . $orderColumn . "` " . $orderType;
    }

    $searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';

    $totalRecords  = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];
    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM `HrmEmployeeDetails` 
					  JOIN HrmDepartments ON HrmEmployeeDetails.DeptID= HrmDepartments.DeptID
 			where HrmEmployeeDetails.EmpStatus=1  $searchQuery ")->fetch_assoc()['count'];

    $data = [];
    $sql  = "SELECT *,HrmEmployeeDetails.EmpID from `HrmEmployeeDetails`
					  JOIN HrmDepartments ON HrmEmployeeDetails.DeptID= HrmDepartments.DeptID
 			where HrmEmployeeDetails.EmpStatus=1  $searchQuery
			$orderBy
			LIMIT $offset, $limit";
    //echo $sql; exit;
    $result = $iconn->query($sql);
    // Only proceed if query is successful and has rows
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {            
            $EmpID = $row['EmpID'];
		        $MonthDate = date($Year . '-' . $Month . '-01');

		        $StartDate = date('Y-m-01', strtotime($MonthDate));
		        $EndDate = date('Y-m-31', strtotime($MonthDate));

		        $SqlOD = "SELECT SUM(DaysTotal) as count FROM `HrmOnDutyRegister` WHERE
							`EmpID`='$EmpID'  and(
							`StartDate` BETWEEN
							'$StartDate' AND '$EndDate'OR `EndDate` BETWEEN
							'$StartDate' AND '$EndDate' )";

		        $StmtOD = $iconn->query($SqlOD);
		        $RowOD   = $StmtOD->fetch_array();

		        $ODCount = $RowOD['count'];
		        
		        if ($ODCount == '') {
		            $ODCount = '0';
		        }

		        $TotalODCount = $ODCount;

		        if ($TotalODCount < 0) {
		            $TotalODCount = '0';
		        }
           
            $singlerow                   = array();
            $singlerow['BioMetricID']    = $row['BioMetricID'];
            $singlerow['EmpName']        = $row['EmpName'];
            $singlerow['DepartmentName'] = $row['DepartmentName'];
            $singlerow['TotalODCount']          = $TotalODCount;
            $singlerow['TimeIn']         = "<input  type='time' class='form-control'  name='TimeIn' id='TimeIn" . $EmpBioID . "'
				  placeholder='' value='" . $TimeIn . "'>";
            $singlerow['TimeOut'] = "<input  type='time' class='form-control'  name='TimeOut' id='TimeOut" . $EmpBioID . "'
				  placeholder='' value='" . $TimeOut . "'>";

            $singlerow['Tools'] = '<a target="" href="EmployeeOnDutyCreate?EmpID='.$EmpID.'" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-plus font-size-18"></i></a>';
            $data[] = $singlerow;
        }
    }
    echo json_encode([
        "draw"            => intval($request['draw']),
        "recordsTotal"    => $totalRecords,
        "recordsFiltered" => $totalFiltered,
        "data"            => $data, // could be empty, and that's okay
    ]);
    exit;

} else {
$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$DATE=$_POST['CURRDATE'];

$Month=$_POST['Month'];
$Year=$_POST['Year'];

	
	?>
	
	<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">     
				   <th>Bio Id </th>
				  <th>Name</th>
				  <th>Department</th>			                   
                  <th>Total</th>
                  <th>Tools</th>
                                  
				<!-- <th>Tools</th>-->
                </thead>
                <tbody id >
				
		<?php		if($SubDeptID !='' ){
					  
					 /* $sql = "SELECT *,SUM($HrmCashAdvance.AdvAmount)as AdvAmount,SUM($HrmCashAdvance.due_amount) as dueamount 
					  FROM `$HrmEmployeeDetails` LEFT JOIN $HrmCashAdvance ON 
					  $HrmEmployeeDetails.`BioMetricID`=$HrmCashAdvance.EmpID 
					  JOIN $HrmDepartments ON
					  $HrmEmployeeDetails.DeptID= $HrmDepartments.DeptID
					  LEFT JOIN HRM_FINAL_ATTENDANCE ON 
					  $HrmEmployeeDetails.`BioMetricID`=HRM_FINAL_ATTENDANCE.EMP_ID
					  where $HrmEmployeeDetails.`SUB_ID`='$SubDeptID' 
					  AND HRM_FINAL_ATTENDANCE.MONTH='$MONTH' and  HRM_FINAL_ATTENDANCE.YEAR='$YEAR'
					  and $HrmEmployeeDetails.`EMP_STATUS`=1 
					  group by $HrmEmployeeDetails.`BioMetricID` order by $HrmEmployeeDetails.`BioMetricID`";
				 */
				 
				 $sql = "
				 
				 SELECT *,HrmEmployeeDetails.EmpID as EmployeeId
					  FROM `HrmEmployeeDetails` 
					  JOIN HrmDepartments ON HrmEmployeeDetails.DeptID= HrmDepartments.DeptID
					  where HrmEmployeeDetails.`SubDeptID`='$SubDeptID' 
					  and HrmEmployeeDetails.`EmpStatus`=1 
					  group by HrmEmployeeDetails.`BioMetricID` order by HrmEmployeeDetails.`BioMetricID`";
				
				}else if($DeptID !='' ){
					
					 $sql = "SELECT * FROM `HrmDepartments` where DeptID= '$DeptID'";
					$query = $iconn->query($sql);
                    $row = $query->fetch_array();
					//$M_DEPT = $row['DEPARTMENT_NAME'];
					
					 
					 $sql = "SELECT *,HrmEmployeeDetails.EmpID as EmployeeId FROM 
					 `HrmEmployeeDetails` 
					  JOIN HrmDepartments ON HrmEmployeeDetails.DeptID= HrmDepartments.DeptID
					  where HrmEmployeeDetails.DeptID = '$DeptID'  and HrmEmployeeDetails.`EmpStatus`=1 
					  group by HrmEmployeeDetails.`BioMetricID` order by HrmEmployeeDetails.`BioMetricID`"; 
				
				}else {
					 
					 $sql = "SELECT *,HrmEmployeeDetails.EmpID as EmployeeId FROM `HrmEmployeeDetails` 					
					  JOIN HrmDepartments ON HrmEmployeeDetails.DeptID= HrmDepartments.DeptID
					   where HrmEmployeeDetails.`EmpStatus`=1 
					  group by HrmEmployeeDetails.`BioMetricID` order by HrmEmployeeDetails.`BioMetricID`";
				
				}
				
		//echo	 $sql; exit;
				
	 

                    $query = $iconn->query($sql);
                    while($row = $query->fetch_array()){
				//$EmpID=$row['BioMetricID'];
				$EmpID=$row['EmployeeId']; 

						
					
					$MonthDate= date($Year.'-'.$Month.'-01');
					
					
					$StartDate = date('Y-m-01', strtotime($MonthDate));
					
					$EndDate = date('Y-m-31', strtotime($MonthDate));
					
					
					 
			 	$SqlOD=	 "SELECT SUM(DaysTotal) as count FROM `HrmOnDutyRegister` WHERE 
					`EmpID`='$EmpID'  and( 
					`StartDate` BETWEEN 
					'$StartDate' AND '$EndDate'OR `EndDate` BETWEEN 
					'$StartDate' AND '$EndDate' )"; 
					 
					  $StmtOD=$iconn->query($SqlOD);
					
					$RowOD = $StmtOD->fetch_array();
					 $ODCount=$RowOD['count'];
					if($ODCount==''){
						$ODCount='0';
					}
						
					 $TotalODCount=$ODCount;
					
					 
					 if($TotalODCount<0){
						 $TotalODCount='0';
					 }
					 
					 ?>
					 
					 
                        <tr>
                          <td><?php echo $row['BioMetricID'];?></td>                        
						  <td><?php echo $row['EmpName'];?></td>
					      <td><?php echo $row['DepartmentName'];?></td>                        
                          <td><?php echo $TotalODCount;?></td>
                         
                          <td>
						 
					   <a target="" href="EmployeeOnDutyCreate?EmpID=<?php  echo $EmpID;?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-plus font-size-18"></i></a>
                      
					    
						<!--<a href="#" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>  
                         <!--   <button class='btn btn-success btn-sm btn-flat edit' data-id='<?php $row['EMPLOYEE_CODE']?>'><i class='fa fa-edit'></i> Add</button>-->
                      <!--  </td>-->
                        </tr>
                      
                 <?php   }?>
					
					</tbody></table>
<?php } ?>