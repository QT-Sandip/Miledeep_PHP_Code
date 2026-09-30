<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

$DeptID  = $_POST['Dept'];
$SubDept = $_POST['SubDept'];
$Month   = $_POST['Month'];
$Year    = $_POST['Year'];
$Type    = $_POST['Type'];
$EmpCategory    = $_POST['EmpCategory'];
$WorkerCategory    = $_POST['WorkerCategory'];

$date_start = $Year . "-" . $Month . "-01";
$date_end   = $Year . "-" . $Month . "-31 23:59:59";

if ($Type != 'List') {

    if ($_POST['Type'] == 'ColumnsOnly') {
        $Month      = (int) $_POST['Month'];
        $Year       = (int) $_POST['Year'];
        $date_start = "$Year-$Month-01";
        $date_end   = date("Y-m-t", strtotime($date_start));

        $list   = array();
        $list[] = array('data' => 'EmpName', 'title' => 'Name');
        $list[] = array('data' => 'BioMetricID', 'title' => 'Bio Metric ID');
        $list[] = array('data' => 'EmpCategory', 'title' => 'Category');
        $list[] = array('data' => 'WorkerSubCategory', 'title' => 'Sub Category');

        $PublicHoliday = array();
        $SqlHolidays   = "SELECT Date FROM HrmHolidays WHERE Date BETWEEN '$date_start' AND '$date_end'";
        $query         = $iconn->query($SqlHolidays);
        while ($row = $query->fetch_assoc()) {
            $PublicHoliday[] = $row['Date'];
        }

        $DaysInMonth = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);

        for ($d = 1; $d <= $DaysInMonth; $d++) {
            $time      = mktime(12, 0, 0, $Month, $d, $Year);
            $day       = date('D', $time);
            $CheckDate = date('Y-m-d', $time);

            $Ph    = in_array($CheckDate, $PublicHoliday) ? "<span style='color:red'><b>PH</b></span>" : "";
            $label = "$d<br>$day<br>$Ph";

            $list[] = array('data' => "Day$d", 'title' => $label);
        }

        // Add summary columns
        $list[] = array('data' => 'Presents', 'title' => 'Presents');
        $list[] = array('data' => 'WeekOffs', 'title' => 'Week Offs');
        $list[] = array('data' => 'PublicHolidays', 'title' => 'PH');
        $list[] = array('data' => 'Leaves', 'title' => 'Leaves');
        $list[] = array('data' => 'Absents', 'title' => 'Absents');

        header("Content-Type: application/json");
        echo json_encode($list);
        exit;
    }

    $sql = "SELECT * FROM HrmSubDepartments WHERE  DeptID='$DeptID'";

    $stmt           = $iconn->query($sql);
    $ary_prod_codes = array();

    ?>  <option value="">All Sub Departments</option> <?php

    while ($row = $stmt->fetch_array()) {?>

                    <option value="<?php echo $row["SubDeptID"]; ?>"><?php echo $row["SubDeptName"]; ?></option>


                <?php }

} else {

    if (1) {

        $SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories`";
        $SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);
        $WorkerSubCategorylist = [];
        while ($RowWorkerType = $SqlWorkerCategoryResult->fetch_array()){
            $WorkerSubCategorylist[$RowWorkerType['CategoryID']] = $RowWorkerType['CategoryName'];
        }

        $list = array();

        $SqlHolidays = "SELECT *  FROM `HrmHolidays` WHERE Date BETWEEN '$date_start' and '$date_end'";
        $query    = $iconn->query($SqlHolidays);
        $Holidays = mysqli_num_rows($query);

        while ($HolidaysRows = $query->fetch_assoc()) {
            $PublicHoliday[] = $HolidaysRows['Date'];
        }

        $DaysInMonth = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
        for ($d = 1; $d <= $DaysInMonth; $d++) {
            $time = mktime(12, 0, 0, $Month, $d, $Year);
            $list[] = date('Y-m-d', $time);
        }

        $request = $_REQUEST;
        $columns = ['EmpName','BioMetricID'];
        $limit       = intval($request['length']);
        $offset      = intval($request['start']);
        $searchValue = $iconn->real_escape_string($request['search']['value']);
        $searchQueryParts = [];

        $searchQueryParts[] = "EmpStatus='1'";
        if (!empty($EmpCategory)) {
            $searchQueryParts[] = "EmpCategory='$EmpCategory'";
        }
        if (!empty($WorkerCategory)) {
            $searchQueryParts[] = "WorkerSubCategory='$WorkerCategory'";
        }
        if (!empty($SubDept)) {
            $searchQueryParts[] = "SubDeptID='$SubDept'";
        } else if (!empty($DeptID)) {
            $searchQueryParts[] = "DeptID='$DeptID' ";
        }

        if (!empty($searchValue)) {
            $searchQueryParts[] = "($columns[0] LIKE '%$searchValue%' OR $columns[1] LIKE '%$searchValue%')";
        }

        $orderBy = "";
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        if($orderColumn == "BioMetricID"){
            $orderBy = " order by `".$orderColumn."` ".$orderType;
        }else if($orderColumn == "EmpName"){
            $orderBy = " order by `".$orderColumn."` ".$orderType;
        }

        // Combine conditions with AND
        $searchQuery   = count($searchQueryParts) > 0 ? 'WHERE ' . implode(' AND ', $searchQueryParts) : '';
        $totalRecords  = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];
        $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
                    HrmEmployeeDetails
        $searchQuery")->fetch_assoc()['count'];
        // Fetch data

        $data = [];
        $sql  = "SELECT * from
        HrmEmployeeDetails
        $searchQuery
        $orderBy
        LIMIT $offset, $limit";
        //echo $sql; exit;
        $result = $iconn->query($sql);
        // Only proceed if query is successful and has rows
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $singlerow                   = array();
                $singlerow['EmpName']    = $row['EmpName'];
                $singlerow['EmpCategory']    = $row['EmpCategory'];
                $singlerow['WorkerSubCategory']    = $WorkerSubCategorylist[$row['WorkerSubCategory']];
                $singlerow['BioMetricID']        = $row['BioMetricID'];

                $BioMetricID = $row['BioMetricID'];
                $Presents    = 0;
                $value = $list[0];
                $date_start = $value;
                $date_end   = $value . ' 23:59:59';
                $date_end   = count($list) - 1;
                $date_end   = $list[$date_end] . ' 23:59:59';

                $sql = "select * from HrmMonthDays d
                            left join ( SELECT * from HrmDailyAttendnessLog  WHERE Month(Date)='$Month' and Year(Date)='$Year'
                            and BioMetricID='$BioMetricID' GROUP by Date ) t on t.Date = d.Date
                            WHERE d.Month='$Month' and d.Year='$Year'";
                            //echo $sql; exit;
                $query_m = $iconn->query($sql);
                while ($row_m = $query_m->fetch_assoc()) {
                    $presents = array();
                    $time_dif = array();
                    if ($row_m['AttID'] == '') {
                        $ATT_AB = true;
                    } else {
                        $ATT_AB = false;
                        $Presents++;
                    }
                    $TimeIn  = $row_m['TimeIn'];
                    $TimeOut = $row_m['TimeOut'];
                    $Status  = $row_m['Status'];

                    if ($TimeIn == '00:00:00') {
                        $TimeInText = "<span style=color:red><b>" . SUBSTR($TimeIn, 0, 5) . "</b></span>";
                    } else {
                        $TimeInText = "<span style=color:green><b>" . SUBSTR($TimeIn, 0, 5) . "</b></span>";
                    }

                    if ($TimeOut == '00:00:00') {
                        $TimeOutText = "<span style=color:red><b>" . SUBSTR($TimeOut, 0, 5) . "</b></span>";
                    } else {
                        $TimeOutText = "<span style=color:green><b>" . SUBSTR($TimeOut, 0, 5) . "</b></span>";
                    }

                    $vaalue = 'P';

                    $date      = $row_m['Date'];
                    $timestamp = strtotime($date);
                    $date      = date('Y-m-d', $timestamp);

                    $present_date  = date('d');
                    $present_month = date('m');

                    $ame_day = date('D', strtotime($value));
                    $e_day   = date('d', strtotime($value));
                    $day_key = "Day".$row_m['Day'];
                    if ($present_month == $Month && $present_date < $e_day) {
                        $singlerow[$day_key]        = '--';
                    } else if (in_array($date, $PublicHoliday)) {
                        $singlerow[$day_key]        = '<span style=color:orange><b>PH</b></span>';
                    } else if ($Status == 3) {
                        $singlerow[$day_key]        = '<span style=color:blue><b>WO</b></span>';
                    } else if ($Status == 2) {
                        $singlerow[$day_key]        = '<span style=color:red><b>A</b></span>';
                    } else if ($Status == 4) {
                        $singlerow[$day_key]        = '<span style=color:red><b>PH</b></span>';
                    } else if ($ATT_AB) {
                        $singlerow[$day_key]        = '<span style=color:red><b>A</b></span>';
                    } else if (!$ATT_AB) {
                        $singlerow[$day_key]        = $TimeInText . "<br>" . $TimeOutText;
                    }
                }

                $WeekOffs     = round($Presents / 6);
                $TotalPayDays = $Presents + $WeekOffs + $Holidays + $Leaves;
                if ($DaysInMonth > $TotalPayDays) {
                    $Absents = $DaysInMonth - $TotalPayDays;
                } else {
                    $Absents = 0;
                }

                $singlerow['Presents'] = $Presents;
                $singlerow['WeekOffs']    = $WeekOffs;
                $singlerow['PublicHolidays']    = $Holidays;
                $singlerow['Leaves'] = $Leaves;
                $singlerow['Absents'] = $Absents;
                //echo "<pre>"; print_r($singlerow); exit;
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

        $data = ' <table id="MonAttTable" class="table table-bordered"
    style="background:#fff;margin-top:6px !important;margin-bottom:-6px !important; height:100px">
   <thead>
                  <th >Name</th>
                  <th>Bio Metric ID</th>
                  <th>Category</th>';

        $list = array();

        $SqlHolidays = "SELECT *  FROM `HrmHolidays` WHERE Date BETWEEN
 '$date_start' and '$date_end'";
        $query    = $iconn->query($SqlHolidays);
        $Holidays = mysqli_num_rows($query);

        while ($HolidaysRows = $query->fetch_assoc()) {
            $PublicHoliday[] = $HolidaysRows['Date'];
        }

        $DaysInMonth = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
        for ($d = 1; $d <= $DaysInMonth; $d++) {
            $time = mktime(12, 0, 0, $Month, $d, $Year);
            if (date('m', $time) == $Month) {
                $date = date('d', $time);
            }

            $day       = date('D', $time);
            $CheckDate = date('Y-m-d', $time);
            if (in_array($CheckDate, $PublicHoliday, true)) {
                $Ph = "<span style=color:red><b>PH</b></span>";
            } else {
                $Ph = "";
            }
            $list[] = date('Y-m-d', $time);
            $data .= "<th>" . $d . "<br>" . $day . "<br>" . $Ph . "</th>";
        }

        $data .= "<th>Presents</th>";
        $data .= "<th>Week Offs</th>";
        $data .= "<th>Public Holidays</th>";
        $data .= "<th>Leaves</th>";
        $data .= "<th>Absents</th>";

        //ECHO $DatesArray=implode(',', $list);

        //print_r($list);
        //exit;
        $Leaves = 0;

        $data .= ' </thead>
                <tbody>';

        if ($SubDept != '') {
            $sql = "SELECT * FROM HrmEmployeeDetails where SubDeptID='$SubDept' and EmpStatus='1'";
        } else if ($DeptID != '') {
            $sql = "SELECT * FROM HrmEmployeeDetails where DeptID = '$DeptID' and EmpStatus='1'";
        } else {
            $sql = "SELECT * FROM HrmEmployeeDetails where EmpStatus='1'";
        }

        $query = $iconn->query($sql);

        while ($row = $query->fetch_assoc()) {
            $status = ($row['status']) ? '<span class="label label-warning pull-right">ontime</span>' :
            '<span class="label label-danger pull-right">late</span>';
            $data .= "<tr>
                            <td >" . $row['EmpName'] . "</td>
                            <td>" . $row['BioMetricID'] . "</td>
                            <td>" . $row['EmpCategory'] . "</td>";
            $BioMetricID = $row['BioMetricID'];
            $Presents    = 0;

            // foreach($list as $value){

            $value = $list[0];

            $date_start = $value;
            $date_end   = $value . ' 23:59:59';
            $date_end   = count($list) - 1;
            $date_end   = $list[$date_end] . ' 23:59:59';

            $sql = "SELECT * FROM HrmDailyAttendnessLog where BioMetricID='$BioMetricID' and
                 Date BETWEEN '$date_start' AND '$date_end' order by Date asc";

            $sql = "select * from HrmMonthDays d
                            left join ( SELECT * from HrmDailyAttendnessLog  WHERE Month(Date)='$Month' and Year(Date)='$Year'
                            and BioMetricID='$BioMetricID' GROUP by Date ) t on t.Date = d.Date
                            WHERE d.Month='$Month' and d.Year='$Year'";

            $query_m = $iconn->query($sql);
            while ($row_m = $query_m->fetch_assoc()) {
                $presents = array();
                $time_dif = array();

                if ($row_m['AttID'] == '') {
                    $ATT_AB = true;
                } else {
                    $ATT_AB = false;
                    $Presents++;
                }

                $TimeIn  = $row_m['TimeIn'];
                $TimeOut = $row_m['TimeOut'];
                $Status  = $row_m['Status'];

                if ($TimeIn == '00:00:00') {
                    $TimeInText = "<span style=color:red><b>" . SUBSTR($TimeIn, 0, 5) . "</b></span>";
                } else {
                    $TimeInText = "<span style=color:green><b>" . SUBSTR($TimeIn, 0, 5) . "</b></span>";
                }

                if ($TimeOut == '00:00:00') {
                    $TimeOutText = "<span style=color:red><b>" . SUBSTR($TimeOut, 0, 5) . "</b></span>";
                } else {
                    $TimeOutText = "<span style=color:green><b>" . SUBSTR($TimeOut, 0, 5) . "</b></span>";
                }

                $vaalue = 'P';

                $date      = $row_m['Date'];
                $timestamp = strtotime($date);
                $date      = date('Y-m-d', $timestamp);

                $present_date  = date('d');
                $present_month = date('m');

                $ame_day = date('D', strtotime($value));
                $e_day   = date('d', strtotime($value));

                if ($present_month == $Month && $present_date < $e_day) {
                    $data .= "<td>--</td>";
                } else if (in_array($date, $PublicHoliday)) {
                    $data .= "<td><span style=color:orange><b>PH</b></span></td>";
                } else if ($Status == 3) {
                    $data .= "<td><span style=color:blue><b>WO</b></span></td>";
                } else if ($Status == 2) {
                    $data .= "<td><span style=color:red><b>A</b></span></td>";
                } else if ($Status == 4) {
                    $data .= "<td><span style=color:red><b>PH</b></span></td>";
                } else if ($ATT_AB) {
                    $data .= "<td><span style=color:red><b>A</b></span></td>";

                } else if (!$ATT_AB) {
                    $data .= "<td>" . $TimeInText . "<br>" . $TimeOutText . "</td>";
                }

                /*
            else if($ame_day=='Sun'){
            $data.= "<td><span style=color:blue><b>H</b></span></td>";
            }else{
            $data.="<td><span style=color:red><b>A</b></span></td>";
            }*/

            }
            $WeekOffs     = round($Presents / 6);
            $TotalPayDays = $Presents + $WeekOffs + $Holidays + $Leaves;
            if ($DaysInMonth > $TotalPayDays) {
                $Absents = $DaysInMonth - $TotalPayDays;
            } else {
                $Absents = 0;
            }

            $data .= "<td>" . $Presents . "</td>";
            $data .= "<td>" . $WeekOffs . "</td>";
            $data .= "<td>" . $Holidays . "</td>";
            $data .= "<td>" . $Leaves . "</td>";
            $data .= "<td>" . $Absents . "</td>";

            $data .= "</tr>";
        }

        echo $data .= '</tbody></table>';

        ?>
<!--<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" ></script>-->
<script src="https://cdn.datatables.net/fixedcolumns/3.3.0/js/dataTables.fixedColumns.min.js"></script>
<script>
$(document).ready( function () {
    // $('#example1').DataTable();
    var table = $('#MonAttTable').DataTable( {
        scrollY:"400px",
         scrollX:true,

  sScrollXInner: "150%",
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true
        /*"columnDefs": [
            { "width": "20%", "targets": 0 }
          ]*/
    } );
} );
</script>
<?php }}?>
