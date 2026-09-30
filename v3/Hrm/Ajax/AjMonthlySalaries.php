<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

if (1) {

    function getWeekOff($PresentDays, $MonthDays)
    {
        if ($PresentDays == 0) {
            return 0;
        } elseif ($MonthDays == 30 || $MonthDays == 28) {
            return 4;
        } else {
            return 5;
        }
    }

    $DeptID    = $_POST['Dept'];
    $SubDeptID = $_POST['SubDept'];
    $Month     = $_POST['Month'];
    $Year      = $_POST['Year'];
    $Date      = $_POST['Date'];

    $date_start = $Date;
    $date_end   = $date_start . ' 23:59:59';

    $request = $_REQUEST;
    $columns = ['BioMetricID', 'EmpName'];

    $limit  = (int) $_POST['length'];
    $limit  = $limit < 0 ? 20 : $limit;
    $offset = max(0, (int) $_POST['start']);

    $OrderBYVal  = intval($request['order'][0]['column']);
    $searchValue = $iconn->real_escape_string($request['search']['value']);

    $searchQueryParts = [];

    if (!empty($SubDeptID)) {
        $searchQueryParts[] = "HED.DeptID='$DeptID' and HED.SubDeptID='$SubDeptID'";
    } else if (!empty($DeptID)) {
        $searchQueryParts[] = "HED.DeptID='$DeptID' ";
    }

    if (!empty($searchValue)) {
        $searchQueryParts[] = "(" . $columns[0] . " LIKE '%" . $searchValue . "%' OR " . $columns[1] . " LIKE '%" . $searchValue . "%' )";
    }

    $orderBy = "";

    $orderColumn = $columns[$request['order'][0]['column']];
    $orderType   = $request['order'][0]['dir'];

    if ($orderColumn == "BioMetricID") {
        $orderBy = " order by `" . $orderColumn . "` " . $orderType;
    } else if ($orderColumn == "EmpName") {
        $orderBy = " order by `HED`.`" . $orderColumn . "` " . $orderType;
    }

    $searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';

    $totalRecords = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];

    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
        HrmEmployeeDetails AS HED
        LEFT JOIN HrmEmpSalaryDetails AS HSD ON HSD.EmpID=HED.EmpID Where 1=1 $searchQuery ")->fetch_assoc()['count'];
    $data = [];
    $sql  = "SELECT HED.EmpName,BioMetricID,HSD.`EmpID`,HSD. `Basic`,HSD. `HRA`,HSD.`DailyAllowance`, HSD.`SpecialAllowance`,HSD. `MedicalAllowance`,HSD. `Conveyence`, HSD.`Others`, HSD.`Incentives`
            FROM
        HrmEmployeeDetails AS HED
        LEFT JOIN HrmEmpSalaryDetails AS HSD ON HSD.EmpID=HED.EmpID where 1=1 $searchQuery
            $orderBy LIMIT $offset, $limit";
    //echo $sql; exit;
    $result = $iconn->query($sql);
    // Only proceed if query is successful and has rows
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $BioMetricID       = $row['BioMetricID'];
            $EmployeeName      = $row['EmpName'];
            $BasicSalary       = $row['Basic'];
            $MonthlyHRA        = $row['HRA'];
            $DailyAllowance    = $row['DailyAllowance'];
            $SpecialAllowance  = $row['SpecialAllowance'];
            $MedicalAllowance  = $row['MedicalAllowance'];
            $MonthlyConveyence = $row['Conveyence'];
            $Others            = $row['Others'];
            $Incentives        = $row['Incentives'];
            $MonthDays         = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
            if ($Month == '2') {
                $MonthDays = 30;
            }
            $SalaryPerDay     = ($BasicSalary / $MonthDays);
            $HRAPerDay        = ($MonthlyHRA / $MonthDays);
            $ConveyencePerDay = ($MonthlyConveyence / $MonthDays);

            $SqlAttendance  = "SELECT COUNT(AttID) as PresentDays FROM `HrmDailyAttendnessLog` WHERE BioMetricID='$BioMetricID' and MONTH (Date)='$Month' and YEAR (Date)='$Year'";
            $StmtAttendance = $iconn->query($SqlAttendance);

            while ($rowAttendance = $StmtAttendance->fetch_array()) {

                $PresentDays      = $rowAttendance['PresentDays'];
                $WeekOff          = getWeekOff($PresentDays);
                $PresentDays      = $PresentDays + $WeekOff;
                $TotalBasicSalary = $SalaryPerDay * $PresentDays;
                $HRA              = $HRAPerDay * $PresentDays;
                $Conveyence       = $ConveyencePerDay * $PresentDays;
                $TotalBasicSalary = Round($TotalBasicSalary, 2);

            }

            $TotalSalary = $TotalBasicSalary + $Conveyence;

            $TotalSalary = Round($TotalSalary, 2);
            $PF          = (($TotalSalary / 100) * 12);
            $PF          = Round($PF, 2);

            $TotalSalary = $TotalSalary + $HRA + $DailyAllowance + $SpecialAllowance + $MedicalAllowance + $Others + $Incentives;
            if ($TotalSalary > 21000) {

                $ESI = 0;

            } else {
                $ESI = (($TotalSalary / 100) * 0.75);

                $ESI = Round($ESI, 2);
            }
            $TotalDeductions = ROUND($PF + $ESI);

            $NetPayableSalary = Round($TotalSalary - ($TotalDeductions));

            $singlerow                     = array();
            $singlerow['BioMetricID']      = $BioMetricID;
            $singlerow['EmpName']          = $EmployeeName;
            $singlerow['PresentDays']      = $PresentDays;
            $singlerow['BasicSalary']      = $BasicSalary;
            $singlerow['HRA']              = Round($HRA, 2);
            $singlerow['Conveyence']       = Round($Conveyence, 2);
            $singlerow['TotalBasicSalary'] = $TotalBasicSalary;
            $singlerow['Incentives']       = $Incentives;
            $singlerow['TotalSalary']      = Round($TotalSalary, 2);
            $singlerow['PF']               = $PF;
            $singlerow['ESI']              = $ESI;
            $singlerow['TotalDeductions']  = $TotalDeductions;
            $singlerow['NetPayableSalary'] = $NetPayableSalary;
            $data[]                        = $singlerow;
        }
    }
    echo json_encode([
        "draw"            => intval($request['draw']),
        "recordsTotal"    => intval($totalRecords),
        "recordsFiltered" => intval($totalFiltered),
        "data"            => $data, // could be empty, and that's okay
    ]);
    exit;

} else {

$DeptID=$_POST['Dept'];
$SubDeptID=$_POST['SubDept'];
$Month=$_POST['Month'];
$Year=$_POST['Year'];
$Date=$_POST['Date'];

	
	$date_start=$Date;
	$date_end=$date_start.' 23:59:59';
	
	$data=' <table  id="key-datatable" data-paging="false" class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">
                 
                  
                  <th>Bio ID</th>
                  <th>Name</th>
                 
                  <th>No Of WageDays</th>
                  <th>Basic Salary</th>
                  <th>HRA</th>
                  <th>Conveyence</th>
                  <th>Earned Wages</th>
                  <th>Incentives</th>
                  <th>Total Earned Wages</th>
                  <th>LESS : PF @12%</th>
                  <th>LESS : ESI @0.75%</th>
                  <th>Total Deductions</th>
                  <th>Net Payable</th>
                 <!-- <th>Tools</th> -->
                </thead>
                <tbody id >';
				
				
if($SubDeptID!=''){
	$SubQuery="where HED.DeptID='$DeptID' and HED.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery="where HED.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
	
 $SqlEmpList = "
SELECT HED.EmpName,BioMetricID,HSD.`EmpID`,HSD. `Basic`,HSD. `HRA`,HSD.`DailyAllowance`,
HSD.`SpecialAllowance`,HSD. `MedicalAllowance`,HSD. `Conveyence`, HSD.`Others`, HSD.`Incentives` FROM 
HrmEmployeeDetails AS HED
LEFT JOIN HrmEmpSalaryDetails AS HSD ON HSD.EmpID=HED.EmpID $SubQuery ";
					
 //echo $SqlEmpList ;
//exit;				  

                    $query = $iconn->query($SqlEmpList);
                    while($row = $query->fetch_array()){
						
						$BioMetricID=$row['BioMetricID'];						
		                $EmployeeName= $row['EmpName'];	
		                $BasicSalary= $row['Basic'];	
		                $MonthlyHRA= $row['HRA'];	
		                $DailyAllowance= $row['DailyAllowance'];	
		                $SpecialAllowance= $row['SpecialAllowance'];	
		                $MedicalAllowance= $row['MedicalAllowance'];	
		                $MonthlyConveyence= $row['Conveyence'];	
		                $Others= $row['Others'];	
		                $Incentives= $row['Incentives'];
						$MonthDays=cal_days_in_month(CAL_GREGORIAN,$Month,$Year);
						if($Month=='2'){
						$MonthDays=30;	
						}
						$SalaryPerDay=($BasicSalary/$MonthDays);
						$HRAPerDay=($MonthlyHRA/$MonthDays);
						$ConveyencePerDay=($MonthlyConveyence/$MonthDays);
			
	$SqlAttendance="SELECT COUNT(AttID) as PresentDays FROM `HrmDailyAttendnessLog` WHERE BioMetricID='$BioMetricID' and MONTH (Date)='$Month' and YEAR (Date)='$Year'";
	$StmtAttendance = $iconn->query($SqlAttendance);



   while( $rowAttendance = $StmtAttendance->fetch_array()){
	   
	   $PresentDays=$rowAttendance['PresentDays'];
	   $WeekOff=getWeekOff($PresentDays);
	   $PresentDays=$PresentDays+$WeekOff;
	   $TotalBasicSalary=$SalaryPerDay*$PresentDays;
	   $HRA=$HRAPerDay*$PresentDays;
	   $Conveyence=$ConveyencePerDay*$PresentDays;
	   $TotalBasicSalary=Round($TotalBasicSalary,2);
	   
   }

$TotalSalary= $TotalBasicSalary+$Conveyence;

$TotalSalary= Round($TotalSalary,2);
$PF=(($TotalSalary/100)*12);
$PF= Round($PF,2);

$TotalSalary=$TotalSalary+$HRA+$DailyAllowance+$SpecialAllowance+$MedicalAllowance+$Others+$Incentives;
IF($TotalSalary > 21000){
	
	$ESI=0;
	
}ELSE{
$ESI=(($TotalSalary/100)*0.75);

$ESI= Round($ESI,2);
}
$TotalDeductions=ROUND($PF+$ESI);

$NetPayableSalary =Round( $TotalSalary-($TotalDeductions));
	
	
				
					 $data .="
                        <tr>
                        
                          <td>".$BioMetricID."</td>
                          <td>".$EmployeeName."</td>
                          <td>".$PresentDays."</td>
                          <td>".$BasicSalary."</td>
                          <td>".Round($HRA,2)."</td>
                          <td>".Round($Conveyence,2)."</td>
                          <td>".$TotalBasicSalary."</td>
                          <td>".$Incentives."</td>
						  <td>".Round($TotalSalary,2)."</td>
						  <td>".$PF."</td>
						  <td>".$ESI."</td>
						  <td>".$TotalDeductions."</td>
						  <td>".$NetPayableSalary."</td>
                         <!-- <td>
                            <button class='btn btn-success btn-sm btn-flat edit' data-id='".$row['attid']."'><i class='fa fa-edit'></i> Edit</button>
                            <button class='btn btn-danger btn-sm btn-flat delete' data-id='".$row['attid']."'><i class='fa fa-trash'></i> Delete</button>
                          </td> -->
                        </tr>
                      ";
                    }

				echo $data .='</tbody></table>';
				
				
				
				
function getWeekOff	($PresentDays,$MonthDays){
	if($PresentDays==0){
		return 0;
	}elseif($MonthDays==30 || $MonthDays==28 ){
	return 4;
	}else{
	return 5;
	}
}

}

?>