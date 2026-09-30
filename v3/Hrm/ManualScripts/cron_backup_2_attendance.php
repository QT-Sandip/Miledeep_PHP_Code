<?php 
set_time_limit(0);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

/*$CurrentYear  = 2025;
$CurrentMonth = 11;

$CurrentMonthStartDate = (new DateTime("$CurrentYear-$CurrentMonth-01"))
                            ->format('Y-m-d');
// Last day of current month
$EndDate = (new DateTime("$CurrentYear-$CurrentMonth-01"))
                ->modify('last day of this month')
                ->format('Y-m-d');*/
             

// Today
$EndDate = (new DateTime())->format('Y-m-d');

// 5 days ago (including today = last 5 days)
$CurrentMonthStartDate = (new DateTime())
                ->modify('-4 days')
                ->format('Y-m-d');  

$sql_last_record = " SELECT IPAddress FROM `HrmBioMetricDevices` where DeviceLocation = 'JLSF VIZAG OFFICE' limit 1  ";   
$result_last_record = $iconn->query($sql_last_record);
if ($result_last_record && $result_last_record->num_rows > 0) {
    $row_last_record = $result_last_record->fetch_assoc();
    $last_recordip = $row_last_record['IPAddress'];

    $sql_head_ip = " SELECT DeviceID FROM `HrmDailyAttendnessBackUp` ORDER BY `AttID` DESC LIMIT 1;  ";   
    $result_head_ip = $iconn->query($sql_head_ip);
    if ($result_head_ip && $result_head_ip->num_rows > 0) {
        $row_head_ip = $result_head_ip->fetch_assoc();
        if($last_recordip == $row_head_ip['DeviceID']){
            $CurrentMonthStartDate = (new DateTime())
                ->modify('-34 days')
                ->format('Y-m-d');
        }
    }

}                              

//echo $StartDate; // 2025-10-31
//echo $EndDate;   // 2025-11-30
if($_GET['regen']){
    $sql = "SELECT 
        DateBioID,  
        BioMetricID,
        `Date`,
        MIN(CASE WHEN Status = 0 THEN TimeIn END) AS TimeIn,
        MAX(CASE WHEN Status = 1 THEN TimeIn END) AS TimeOut,
        DeviceID,
        Status
    FROM HrmDailyAttendnessBackUp    
    WHERE `Date` BETWEEN '$CurrentMonthStartDate' AND '$EndDate'
    and BioMetricID >0
    GROUP BY BioMetricID, `Date`
    ORDER BY BioMetricID, `Date`;
    ";
    //echo $sql; exit;
    $SqlAryValues = [];
    $result = $iconn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $DateBioID = $row['DateBioID'];
            $employee_id = $row['BioMetricID'];
            $date = $row['Date'];
            $TimeIn = $row['TimeIn'];
            $TimeOut = $row['TimeOut'];
            $DeviceID = $row['DeviceID'];
            $Status = $row['Status'];

            $SqlAryValues[] = "('$employee_id','$DateBioID','$date','$TimeIn','$TimeOut','$Status','$DeviceID')";
                //echo "<pre>"; print_r($SqlAryValues); exit; 
        }   

        $batchSize = 200;
        $total = count($SqlAryValues);      
        for ($i = 0; $i < $total; $i += $batchSize) {
            $batch = array_slice($SqlAryValues, $i, $batchSize);
            $SqlAttValues = implode(',', $batch);

            if ($SqlAttValues != '') {

                $SqlAtt = "INSERT IGNORE INTO `Temp_HrmDailyAttendnessLog`(`BioMetricID`,`DateBioID`, `Date`, `TimeIn`, `TimeOut`,`Status`,`DeviceID`) VALUES $SqlAttValues";
                $StmtAtt = $iconn->prepare($SqlAtt);
                $StmtAtt->execute();

           }

        }

        $sqlNight = "
            SELECT BioMetricID, Date, MAX(TimeIn) AS TimeOut
            FROM HrmDailyAttendnessBackUp
            WHERE TimeIn > '00:00:00'
              AND TimeIn < '10:00:00'
              AND Status = 1
              AND Date >= '$CurrentMonthStartDate'
              AND Date < '$EndDate'
            GROUP BY DateBioID
            ORDER BY AttID ASC
        ";
        //echo "<div>".$sqlNight."</div>";
        $resultNight = $iconn->query($sqlNight);

        if ($resultNight && $resultNight->num_rows > 0) {
            while ($row = $resultNight->fetch_assoc()) {
                $previous_day = date("Y-m-d", strtotime($row['Date'] . " -1 day"));
                $bioId   = $row['BioMetricID'];
                $TimeOut = $row['TimeOut'];

                // Find matching previous day record
                $checkPrev = "
                    SELECT Date, BioMetricID,TimeIn 
                    FROM Temp_HrmDailyAttendnessLog 
                    WHERE Date = '$previous_day' 
                      AND BioMetricID = '$bioId'
                ";
                //echo "<div>".$checkPrev."</div>"; 
                $resPrev = $iconn->query($checkPrev);
                $prevRow = $resPrev->fetch_assoc();

                if (!empty($prevRow) && $prevRow['TimeIn']>$TimeOut) {
                    // Update regardless of existing TimeOut
                    $SqlUpdateNight = "
                        UPDATE Temp_HrmDailyAttendnessLog 
                        SET TimeOut = '$TimeOut' 
                        WHERE Date = '$previous_day' 
                          AND BioMetricID = '$bioId'
                    ";
                    echo $SqlUpdateNight; echo "<br>";
                    $iconn->query($SqlUpdateNight);
                    $TotalUpdated++;
                }
            }
        }

        $sqlMainTableInsert = "INSERT INTO HrmDailyAttendnessLog (
            DateBioID,
            BioMetricID,
            DeviceID,
            `Date`,
            TimeIn,
            Status,
            TimeOut,
            OnTime,
            Shift,
            BreakTimeIn,
            BreakTimeOut,
            NextDayOut,
            IsManual,
            CStatus,
            Minutes
        )
        SELECT
        DateBioID,
        BioMetricID,
        DeviceID,
        `Date`,
        TimeIn,
        Status,
        TimeOut,
        OnTime,
        Shift,
        BreakTimeIn,
        BreakTimeOut,
        NextDayOut,
        IsManual,
        CStatus,
        Minutes
        FROM Temp_HrmDailyAttendnessLog
        ON DUPLICATE KEY UPDATE
        TimeIn          = VALUES(TimeIn),
        TimeOut         = VALUES(TimeOut),
        Status          = VALUES(Status),
        OnTime          = VALUES(OnTime),
        Shift           = VALUES(Shift),
        BreakTimeIn     = VALUES(BreakTimeIn),
        BreakTimeOut    = VALUES(BreakTimeOut),
        NextDayOut      = VALUES(NextDayOut),
        IsManual        = VALUES(IsManual),
        CStatus         = VALUES(CStatus),
        Minutes         = VALUES(Minutes);";
        $iconn->query($sqlMainTableInsert);

        $sqlTempTableTruncate = "TRUNCATE TABLE Temp_HrmDailyAttendnessLog";
        $iconn->query($sqlTempTableTruncate);

        $sqlUpdateMinutes = "UPDATE HrmDailyAttendnessLog
        SET Minutes =
            CASE        
                WHEN TimeOut < TimeIn THEN
                    TIMESTAMPDIFF(
                        MINUTE,
                        CONCAT(`Date`, ' ', TimeIn),
                        CONCAT(DATE_ADD(`Date`, INTERVAL 1 DAY), ' ', TimeOut)
                    )        
                ELSE
                    TIMESTAMPDIFF(
                        MINUTE,
                        CONCAT(`Date`, ' ', TimeIn),
                        CONCAT(`Date`, ' ', TimeOut)
                    )
            END
        WHERE
            TimeIn  IS NOT NULL
            AND TimeOut IS NOT NULL
            AND TimeIn  <> '00:00:00'
            AND TimeOut <> '00:00:00' 
            AND Date >= '$CurrentMonthStartDate'
            AND Date < '$EndDate'
            ";
        $iconn->query($sqlUpdateMinutes);    

    }
}

if( $_GET['showdata'] ){
   $sqlT = " select * from Temp_HrmDailyAttendnessLog ";
    //echo $sql; exit;
    echo '<table>
            <thead> 
                <tr>
            <th>BioMetricID</th> 
            <th>Date</th>
            <th>FirstInPunch</th>
            <th>LastOutPunch </th>
                </tr>
            </thead><tbody>';
    $result = $iconn->query($sqlT);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
            <td>".$row['BioMetricID']."</td> 
            <td>".$row['Date']."</td> 
            <td>".$row['TimeIn']."</td> 
            <td>".$row['TimeOut']."</td> 
                </tr>";
        }   
    }
    echo "</tbody></table>";
}