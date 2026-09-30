<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

$request = $_REQUEST;
$columns = ['Date', 'BioMetricID', 'EmpName', 'DepartmentName'];

$limit  = intval($request['length']);
$offset = intval($request['start']);

$Type      = $request['Type'];
$DeptID    = intval($request['DeptID']);
$SubDeptID = intval($request['SubDeptID']);
$Date      = $request['Date'];

$date_start       = $Date;
$date_end         = $date_start . ' 23:59:59';
$searchValue      = $iconn->real_escape_string($request['search']['value']);
$searchQueryParts = [];

if (!empty($SubDeptID)) {
    $searchQueryParts[] = "EmpList.SubDeptID='$SubDeptID'";
} else if (!empty($DeptID)) {
    $searchQueryParts[] = "EmpList.DeptID='$DeptID'";
} else {
    $searchQueryParts = [];
}

if (!empty($searchValue)) {
    $searchQueryParts[] = "(`EmpList`.$columns[1] LIKE '%$searchValue%' OR `EmpList`.$columns[2] LIKE '%$searchValue%')";
}

$orderBy = "";

$orderColumn = $columns[$request['order'][0]['column']];
$orderType   = $request['order'][0]['dir'];

if ($orderColumn == "Date") {
    $orderBy = " order by AttLog.Date DESC,
					AttLog.TimeIn DESC ";
} else if ($orderColumn == "BioMetricID") {
    $orderBy = " order by `EmpList`.`" . $orderColumn . "` " . $orderType;
} else if ($orderColumn == "EmpName") {
    $orderBy = " order by `EmpList`.`" . $orderColumn . "` " . $orderType;
} else if ($orderColumn == "PhoneNumber") {
    $orderBy = " order by `PersonalDetails`.`" . $orderColumn . "` " . $orderType;
}

// Combine conditions with AND
$searchQuery  = count($searchQueryParts) > 0 ? ' and ' . implode(' AND ', $searchQueryParts) : '';
$totalRecords = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];

/*echo "SELECT EmpName, DepartmentName,ShiftDtl.ShiftID, AttID, EmpList.BioMetricID, Date, TimeIn, TimeOut,GraceTime,
OnTime,StartTime,ShiftName,
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
(SELECT  MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) as Diff
FROM HrmDailyAttendnessLog as AttLog
LEFT JOIN HrmEmployeeDetails as EmpList ON EmpList.BioMetricID=AttLog.BioMetricID
LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
LEFT JOIN HrmShiftDetails  as ShiftDtl
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=
(SELECT  MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails )
where  EmpList.EmpStatus=1
and AttLog.Date BETWEEN '$date_start' AND '$date_end'
$SubQuery
ORDER BY AttLog.Date DESC,
AttLog.TimeIn DESC";

exit;*/

/*SELECT EmpName, DepartmentName,ShiftDtl.ShiftID, AttID, EmpList.BioMetricID, Date, TimeIn, TimeOut,GraceTime,
OnTime,StartTime,ShiftName,
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
(SELECT  MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) as Diff
FROM HrmDailyAttendnessLog as AttLog
LEFT JOIN HrmEmployeeDetails as EmpList ON EmpList.BioMetricID=AttLog.BioMetricID
LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
LEFT JOIN HrmShiftDetails  as ShiftDtl
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=
(SELECT  MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails )
where  EmpList.EmpStatus=1
and AttLog.Date BETWEEN '$date_start' AND '$date_end'
$SubQuery*/

$totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
	            HrmDailyAttendnessLog as AttLog
LEFT JOIN HrmEmployeeDetails as EmpList ON EmpList.BioMetricID=AttLog.BioMetricID
LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
LEFT JOIN HrmShiftDetails  as ShiftDtl
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=
(SELECT  MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails )
where  EmpList.EmpStatus=1
					and AttLog.Date BETWEEN '$date_start' AND '$date_end'
	$searchQuery")->fetch_assoc()['count'];
// Fetch data

$data = [];
$sql  = "SELECT EmpName, DepartmentName,ShiftDtl.ShiftID, AttID, EmpList.BioMetricID, Date, TimeIn, TimeOut,GraceTime,
OnTime,StartTime,ShiftName,
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
(SELECT  MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) as Diff
from
	HrmDailyAttendnessLog as AttLog
LEFT JOIN HrmEmployeeDetails as EmpList ON EmpList.BioMetricID=AttLog.BioMetricID
LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
LEFT JOIN HrmShiftDetails  as ShiftDtl
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=
(SELECT  MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails )
where  EmpList.EmpStatus=1
					and AttLog.Date BETWEEN '$date_start' AND '$date_end'
	$searchQuery
	$orderBy
	LIMIT $offset, $limit";
//echo $sql;exit;
$result = $iconn->query($sql);
// Only proceed if query is successful and has rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        if ($row['TimeOut'] != "00:00:00") {
            $TimeOut = date('h:i A', strtotime($row['TimeOut']));
        } else {
            $TimeOut = "--";
        }

        if ($row['TimeIn'] != "00:00:00") {
            $TimeIn = date('h:i A', strtotime($row['TimeIn']));
        } else {
            $TimeIn = "--";
        }

        if ($TimeIn != "--") {
            if ($row['OnTime'] == 1) {
                $status = ("on time") ? '<span class="label label-success pull-right">ontime</span>' : '<span class="label label-danger pull-right">late</span>';
            } else {
                $status = ("late") ? '<span class="label label-danger pull-right">late</span>' : '<span class="label label-success pull-right">ontime</span>';
            }
        } else {
            $status = "";
        }

        if ($TimeIn != '--' && $TimeOut != '--') {
            
            $loginTime = strtotime($TimeIn);
            $loginOut  = strtotime($TimeOut);

            if ($row['ShiftID'] == 4) {

                $NSFT_DATE  = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeOut'] . ' +1 day'));
                $NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeIn']));

                $NSFT_DATE  = strtotime($NSFT_DATE);
                $NSPFT_DATE = strtotime($NSPFT_DATE);

                round(abs($NSFT_DATE - $NSPFT_DATE) / 60, 2);
                $row['WorkMin'] = round(abs($NSPFT_DATE - $NSFT_DATE) / 60, 2);
            } else {
                $diff = $loginOut - $loginTime;
            }
            $diff       = $row['WorkMin'];
            $Work_Hours = (int) ($diff / 60);
            $Work_Min   = $diff % 60;

            $diff = $diff / (60);

            if ($diff > 0) {
                $ot = $diff - 8;
            } else {
                $ot = 0;
            }

            $diff = round($diff, 2);
            $ot   = round($ot, 2);
            $ot   = $ot * 60;

            $Ot_Hours = (int) ($ot / 60);
            $Ot_Min   = $ot % 60;

            $diff = $Work_Hours . ' H : ' . $Work_Min . ' M';
            $ot   = $Ot_Hours . ' H : ' . $Ot_Min . ' M';

        } else {
            $diff = 0;
        }

        $singlerow                   = array();
        $singlerow['Date']           = date('d-m-Y', strtotime($row['Date']));
        $singlerow['BioMetricID']    = $row['BioMetricID'];
        $singlerow['EmpName']        = $row['EmpName'];
        $singlerow['DepartmentName'] = $row['DepartmentName'];
        $singlerow['ShiftName']      = $row['ShiftName'];
        $singlerow['TimeIn']         = $TimeIn . ' ' . $status;
        $singlerow['TimeOut']        = $row['TimeOut'];
        $singlerow['diff'] = $diff;        
        
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
if (0) {

    $data = '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >

                                            <thead class="thead-light">

                  <th>Date</th>
                  <th>Bio ID</th>
                  <th>Name</th>
                  <th>Department</th>
                  <th>Shift</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Hours</th>
                 <!-- <th>Tools</th> -->
                </thead>
                <tbody id >';

    if ($SubDeptID != '') {
        $SubQuery = "and EmpList.SubDeptID='$SubDeptID'";
    } else if ($DeptID != '') {
        $SubQuery = "and Dept.DeptID='$DeptID'";
    } else {
        $SubQuery = "";
    }

    $SqlEmpList = "SELECT EmpName, DepartmentName,ShiftDtl.ShiftID, AttID, EmpList.BioMetricID, Date, TimeIn, TimeOut,GraceTime,
OnTime,StartTime,ShiftName,
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
(SELECT  MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) as Diff
FROM HrmDailyAttendnessLog as AttLog
LEFT JOIN HrmEmployeeDetails as EmpList ON EmpList.BioMetricID=AttLog.BioMetricID
LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
LEFT JOIN HrmShiftDetails  as ShiftDtl
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=
(SELECT  MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails )
where  EmpList.EmpStatus=1
					and AttLog.Date BETWEEN '$date_start' AND '$date_end'
					$SubQuery
					ORDER BY AttLog.Date DESC,
					AttLog.TimeIn DESC";

    $query = $iconn->query($SqlEmpList);

    while ($row = $query->fetch_array()) {

        $BioMetricID = $row['BioMetricID'];

        if ($row['TimeOut'] != "00:00:00") {
            $TimeOut = date('h:i A', strtotime($row['TimeOut']));
        } else {
            $TimeOut = "--";
        }

        if ($row['TimeIn'] != "00:00:00") {
            $TimeIn = date('h:i A', strtotime($row['TimeIn']));
        } else {
            $TimeIn = "--";
        }

        $ShiftName = $row['ShiftName'];
        $next_day  = date('Y-m-d', strtotime('+1 day', strtotime($date_start)));
        $Shift     = 'ROTATION';

        if ($TimeIn != "--") {
            if ($row['OnTime'] == 1) {
                $status = ("on time") ? '<span class="label label-success pull-right">ontime</span>' : '<span class="label label-danger pull-right">late</span>';
            } else {
                $status = ("late") ? '<span class="label label-danger pull-right">late</span>' : '<span class="label label-success pull-right">ontime</span>';
            }
        } else {
            $status = "";
        }

        if ($TimeIn != '--' && $TimeOut != '--') {
            //echo $TimeOut;
            $loginTime = strtotime($TimeIn);
            $loginOut  = strtotime($TimeOut);

//                     exit;

            if ($row['ShiftID'] == 4) {

                $NSFT_DATE  = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeOut'] . ' +1 day'));
                $NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeIn']));

                $NSFT_DATE  = strtotime($NSFT_DATE);
                $NSPFT_DATE = strtotime($NSPFT_DATE);

                round(abs($NSFT_DATE - $NSPFT_DATE) / 60, 2);
                $row['WorkMin'] = round(abs($NSPFT_DATE - $NSFT_DATE) / 60, 2);
            } else {
                $diff = $loginOut - $loginTime;
            }
            $diff       = $row['WorkMin'];
            $Work_Hours = (int) ($diff / 60);
            $Work_Min   = $diff % 60;

            $diff = $diff / (60);

            if ($diff > 0) {
                $ot = $diff - 8;
            } else {
                $ot = 0;
            }

            $diff = round($diff, 2);
            $ot   = round($ot, 2);
            $ot   = $ot * 60;

            $Ot_Hours = (int) ($ot / 60);
            $Ot_Min   = $ot % 60;

            $diff = $Work_Hours . ' H : ' . $Work_Min . ' M';
            $ot   = $Ot_Hours . ' H : ' . $Ot_Min . ' M';

        } else {
            $diff = 0;
        }

        $data .= "
                        <tr>
                          <td>" . date('d-m-Y', strtotime($row['Date'])) . "</td>
                          <td>" . $row['BioMetricID'] . "</td>
                          <td>" . $row['EmpName'] . "</td>
                          <td>" . $row['DepartmentName'] . "</td>
                          <td>" . $ShiftName . "</td>
                          <td>" . $TimeIn . ' ' . $status . "</td>
						  <td>" . $TimeOut . "</td>
						  <td>" . $diff . "</td>
                         <!-- <td>
                            <button class='btn btn-success btn-sm btn-flat edit' data-id='" . $row['attid'] . "'><i class='fa fa-edit'></i> Edit</button>
                            <button class='btn btn-danger btn-sm btn-flat delete' data-id='" . $row['attid'] . "'><i class='fa fa-trash'></i> Delete</button>
                          </td> -->
                        </tr>
                      ";
    }

    echo $data .= '</tbody></table>';

}
