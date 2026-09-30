<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

if (1) {

    $request = $_REQUEST;
    $columns = ['BioMetricID', 'EmpName', 'DepartmentName'];

    $limit  = intval($request['length']);
    $offset = intval($request['start']);

    $Date      = $_POST['Date'];

    $date_start = $Date;
    $date_end   = $date_start . ' 23:59:59';

    $DateFilter = "Between '$date_start' and '$date_end'";

    $DeptID      = intval($request['DeptID']);
    $SubDeptID   = intval($request['SubDeptID']);
    $OrderBYVal  = intval($request['order'][0]['column']);
    $searchValue = $iconn->real_escape_string($request['search']['value']);

    $searchQueryParts = [];

    if (!empty($SubDeptID)) {        
        $searchQueryParts[] = "EmpList.SubDeptID='$SubDeptID'";        
    } else if (!empty($DeptID)) {        
        $searchQueryParts[] = "EmpList.DeptID='$DeptID' ";        
    }

    if (!empty($searchValue)) {
        $searchQueryParts[] = "(EmpList.".$columns[0]." LIKE '%".$searchValue."%' OR EmpList.".$columns[1]." LIKE '%".$searchValue."%' OR ".$columns[2]." LIKE '%".$searchValue."%')";
    }

    $orderBy = "";

    $orderColumn = $columns[$request['order'][0]['column']];
    $orderType   = $request['order'][0]['dir'];

    if ($orderColumn == "BioMetricID") {
        $orderBy = " order by `EmpList`.`" . $orderColumn . "` " . $orderType .", TimeIn DESC";
    } else if ($orderColumn == "EmpName") {
        $orderBy = " order by `EmpList`.`" . $orderColumn . "` " . $orderType .", TimeIn DESC";
    } 

    // Combine conditions with AND
    $searchQuery   = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';
    
    $totalRecords  = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];
    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails as EmpList
        LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
        left join HrmDailyAttendnessLog as ATT on ATT.BioMetricID=EmpList.BioMetricID and ATT.Date $DateFilter
        where EmpList.EmpStatus=1  $searchQuery ")->fetch_assoc()['count'];

    $data = [];
    $sql  = "SELECT EmpName, DepartmentName, EmpList.BioMetricID ,TimeIn,TimeOut,OnTime
        FROM HrmEmployeeDetails as EmpList
        LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
        left join HrmDailyAttendnessLog as ATT on ATT.BioMetricID=EmpList.BioMetricID and ATT.Date $DateFilter
        where EmpList.EmpStatus=1  $searchQuery
        $orderBy
        LIMIT $offset, $limit";
        //echo $sql; exit;
    $result = $iconn->query($sql);
    // Only proceed if query is successful and has rows
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
					$EmpBioID = $row['BioMetricID'];
					$Shift    = $row['ShiftName'];
					
					if ($row['TimeOut'] != "00:00:00" && $row['TimeOut'] != '') {					
						$TimeOut = date('H:i:s', strtotime($row['TimeOut']));
					} else {
						$TimeOut = "--:-- --";
					}					
					
					if ($TimeOut == "12:00 AM") {
						$TimeOut = " -- ";
					}

					if ($row['TimeIn'] != "00:00:00" && $row['TimeIn'] != '') {					
						$TimeIn = date('H:i:s', strtotime($row['TimeIn']));
					} else {					
						$TimeIn = "--:-- --";
					}

					if ($TimeIn == " ") {
						$TimeIn = " -- ";
					}

					if ($row['OnTime'] == 1) {
						$status = ("on time") ? '<span class="label label-success pull-right">ontime</span>' : '<span class="label label-danger pull-right">late</span>';
					} else {
						$status = ("late") ? '<span class="label label-danger pull-right">late</span>' : '<span class="label label-success pull-right">ontime</span>';
					}

					$ShiftId        = $row['SHIFT'];
					$AttendanceDate = date("d-m-Y", strtotime($row['Date']));
					
					if ($AttendanceDate == '01-01-1970') {
						$AttendanceDate = '--';
					}
            $singlerow                   = array();
            $singlerow['BioMetricID']    = $EmpBioID;
            $singlerow['EmpName']        = $row['EmpName'];
            $singlerow['DepartmentName'] = $row['DepartmentName'];
            $singlerow['Shift']    = $Shift . $status;
            $singlerow['TimeIn']    = "<input  type='time' class='form-control'  name='TimeIn' id='TimeIn" . $EmpBioID . "'
				  placeholder='' value='" . $TimeIn . "'>";
				  $singlerow['TimeOut'] = "<input  type='time' class='form-control'  name='TimeOut' id='TimeOut" . $EmpBioID . "'
				  placeholder='' value='" . $TimeOut . "'>";
            
            $singlerow['Tools'] = "<button  id='BtnEditManualAttend'  value='" . $EmpBioID . "' align='Center' class='btn btn-success btn-sm btn-flat'>
						 <i class='fa fa-edit'></i> </button>";
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
} else {

$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$Date=$_POST['Date'];

	
	$date_start=$Date;
	$date_end=$date_start.' 23:59:59';
	
	$DateFilter="Between '$date_start' and '$date_end'";
	
	
	
	$data=' <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										

                                            <thead class="thead-light">
                 
                  <th>Bio ID</th>
                  <th>Name</th>        
                  <th>Department</th>
                  <th>Shift/Status</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                 
                  <th>Tools</th>
                </thead>
                <tbody id >';
				
			if($SubDeptID!=''){
	$SubQuery=" and  EmpList.SubDeptID='$SubDeptID'";
}else if($DeptID!=''){
	//$SubQuery=" and Dept.DeptID='$DeptID'";
	$SubQuery=" and EmpList.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
	
 /*$SqlEmpList="SELECT EmpName, DepartmentName, EmpList.BioMetricID ,TimeIn,TimeOut,OnTime
 FROM HrmEmployeeDetails as EmpList 
 LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
 left join HrmDailyAttendnessLog as ATT on ATT.BioMetricID=EmpList.BioMetricID
 where EmpList.EmpStatus=1 and ATT.Date $DateFilter $SubQuery ORDER BY TimeIn DESC";
 */
 $SqlEmpList="SELECT EmpName, DepartmentName, EmpList.BioMetricID ,TimeIn,TimeOut,OnTime
 FROM HrmEmployeeDetails as EmpList 
 LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID 
 left join HrmDailyAttendnessLog as ATT on ATT.BioMetricID=EmpList.BioMetricID and ATT.Date $DateFilter
 where EmpList.EmpStatus=1  $SubQuery   ORDER BY TimeIn DESC";
 
				
					
           
          $query = $iconn->query($SqlEmpList);
					
					
					if(mysqli_num_rows($query) == 0){
							$TimeOut= "--" ;
							$TimeIn= "--" ;
							$Shift ='NA';
							$status ='';
						}
					
					
					
					
                    while($RowAtt = $query->fetch_array()){
						 
						$EmpBioID=$RowAtt['BioMetricID'];						
						$Shift=$RowAtt['ShiftName'];	
						if($RowAtt['TimeOut']!="00:00:00" && $RowAtt['TimeOut']!=''){
							//$TimeOut= date('h:i A', strtotime($RowAtt['TimeOut']));
							 $TimeOut= date('H:i:s', strtotime($RowAtt['TimeOut'])); 
							}
							
							else { 
							$TimeOut= "--:-- --" ;
							}
						//ECHO	$TimeOut;
					if($TimeOut =="12:00 AM"){
						
						$TimeOut=" -- ";
					}

                  //echo 	$TimeOut; exit;
							//if($RowAtt['TimeIn']!="00:00:00"){
							if($RowAtt['TimeIn']!="00:00:00" && $RowAtt['TimeIn']!=''){
							//$TimeIn= date('h:i A', strtotime($RowAtt['TimeIn']));
							$TimeIn= date('H:i:s', strtotime($RowAtt['TimeIn']));
							}else{ 
							//$TimeIn= "--" ;
							$TimeIn= "--:-- --" ;
							}
							
							 if($TimeIn ==" "){
						
						         $TimeIn=" -- ";
					        }					
						
							
							if($RowAtt['OnTime']==1){
								$status = ("on time")?'<span class="label label-success pull-right">ontime</span>':'<span class="label label-danger pull-right">late</span>';
								}else{
									$status = ("late")?'<span class="label label-danger pull-right">late</span>':'<span class="label label-success pull-right">ontime</span>';
								}
								
								 
						$ShiftId=$RowAtt['SHIFT'];						 	
		                $AttendanceDate = date("d-m-Y", strtotime($RowAtt['Date']));
                   if($AttendanceDate=='01-01-1970'){
	
	                     $AttendanceDate='--';
                    }
					
						

					 $data .="
                        <tr>
                          
                         
                          <td >".$EmpBioID."</td>
                          <td id=empname".$EmpBioID.">".$RowAtt['EmpName']."</td>		
                          <td>".$RowAtt['DepartmentName']."</td>
						  <td>".$Shift.$status."</td>
                         <!-- <td id=TimeIn".$EmpBioID.">".$TimeIn."</td>
						  <td id=TimeOut".$EmpBioID.">".$TimeOut."</td>-->
						  
						
				  
 <td > <input  type='time' class='form-control'  name='TimeIn' id='TimeIn".$EmpBioID."'
				  placeholder='' value='".$TimeIn."'> </td>
	 
	 <td > <input  type='time' class='form-control'  name='TimeOut' id='TimeOut".$EmpBioID."'
				  placeholder='' value='".$TimeOut."'> </td>
	
	
						  
                          <td>
						
						  
						<!-- <button  id='BtnEditManualAttend'  value= '".$EmpBioID."' align='Center' class='btn btn-success btn-sm btn-flat'>
						 <i class='fa fa-edit'></i> Update</button>-->
						 
 <button  id='BtnEditManualAttend'  value='".$EmpBioID."' align='Center' class='btn btn-success btn-sm btn-flat'>
						 <i class='fa fa-edit'></i> </button>

                
                        <!--    <button class='btn btn-danger btn-sm btn-flat delete' data-id='".$EmpBioID."'><i class='fa fa-trash'></i> </button>-->
                          </td>
                        </tr>
                      ";
                    }
					
					echo $data .='</tbody></table>';


}
?>