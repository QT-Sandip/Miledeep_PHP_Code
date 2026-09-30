<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$FromDate = $_POST['FromDate'];
$ToDate = $_POST['ToDate'];

if (!$FromDate || !$ToDate) {
    echo '<table id="key-datatable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th class="text-center">Invalid date range provided.</th>
                </tr>
            </thead>
          </table>';
    exit;
}

$DateFilter = "BETWEEN '$FromDate' AND '$ToDate'";


$SqlTotalEntries = "SELECT COUNT(*) AS TotalEntries FROM QcRmAbReport WHERE AnalysisDate $DateFilter";
$TotalEntriesResult = $iconn->query($SqlTotalEntries);
$TotalEntriesRow = $TotalEntriesResult->fetch_assoc();
$TotalEntries = $TotalEntriesRow['TotalEntries'];


$SqlCount = "SELECT DISTINCT(AnalysisDate) FROM QcRmAbReport WHERE AnalysisDate $DateFilter";
$StmtCount = $iconn->query($SqlCount);

echo '<table id="key-datatable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>Report ID</th>
                <th>Analysis Date</th>
                <th>Report Date</th>
                <th>Total Reports</th>
                <th>Day Total</th>
                <th>Night Total</th>
                <th>Rm</th>
                <th>Grading</th>
                <th>Farm</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

$RowNumber = 0;

if ($StmtCount->num_rows > 0) {
    while ($RowCount = $StmtCount->fetch_array()) {
        $AnalysisDate = $RowCount['AnalysisDate'];

        $SqlTotalReports = "
            SELECT 
                ReportDate, 
                AbRepID, 
                Shift, 
                (SELECT COUNT(*) FROM QcRmAbReport WHERE AnalysisDate = '$AnalysisDate') AS TotalReports,
                (SELECT COUNT(*) FROM QcRmAbReport WHERE Shift = 'DAY' AND AnalysisDate = '$AnalysisDate') AS DayTotal,
                (SELECT COUNT(*) FROM QcRmAbReport WHERE Shift = 'NIGHT' AND AnalysisDate = '$AnalysisDate') AS NightTotal
                ,(SELECT IFNULL(SUM(BreakUpwiseUsedWells-StandardUsedWells), 0) FROM QcRmAbReport as QRA left join `QcRmAbUtilizedKitBreakupDetails` as QRAB on QRAB.AbReportID=QRA.AbRepID WHERE  AnalysisDate = '$AnalysisDate' 
                and SampleDetails='Raw Material Sample' ) AS UsedWellsForRaw,
                (SELECT IFNULL(SUM(BreakUpwiseUsedWells-StandardUsedWells), 0) FROM QcRmAbReport as QRA left join `QcRmAbUtilizedKitBreakupDetails` as QRAB on QRAB.AbReportID=QRA.AbRepID WHERE  AnalysisDate = '$AnalysisDate' and
                 SampleDetails='Grading Sample' ) AS UsedWellsForGrading, 
                (SELECT IFNULL(SUM(BreakUpwiseUsedWells-StandardUsedWells), 0) FROM QcRmAbReport as QRA left join `QcRmAbUtilizedKitBreakupDetails` as QRAB on QRAB.AbReportID=QRA.AbRepID WHERE  AnalysisDate = '$AnalysisDate' and 
                SampleDetails='Farm Sample' ) AS UsedWellsForFarm
            FROM QcRmAbReport 
            WHERE AnalysisDate = '$AnalysisDate'
            LIMIT 1";

        $StmtTotalReports = $iconn->query($SqlTotalReports);
        $RowTotalReports = $StmtTotalReports->fetch_array();

        $ReportDate = $RowTotalReports['ReportDate'];
        $Total = $RowTotalReports['UsedWellsForRaw'] + $RowTotalReports['UsedWellsForGrading'] + $RowTotalReports['UsedWellsForFarm'];
        // Increment row count
        $RowNumber++;

        // Generate table rows
        echo "
        <tr>
            <td>{$RowTotalReports['AbRepID']}</td>
            <td>" . date("d-m-Y", strtotime($AnalysisDate)) . "</td>
            <td>" . date("d-m-Y", strtotime($ReportDate)) . "</td>
            <td>{$RowTotalReports['TotalReports']}</td>
            <td>{$RowTotalReports['DayTotal']}</td>
            <td>{$RowTotalReports['NightTotal']}</td>
            <td>{$RowTotalReports['UsedWellsForRaw']}</td>
            <td>{$RowTotalReports['UsedWellsForGrading']}</td>
            <td>{$RowTotalReports['UsedWellsForFarm']}</td>
            <td>" . $Total . "</td>

            <td>
                <a target='_blank' href='AbReportCreate.php?DOA=" . date("d-m-Y", strtotime($AnalysisDate)) . "&DOR=" . date("d-m-Y", strtotime($ReportDate)) . "&FromDate=" . $FromDate . "&ToDate=" . $ToDate . "'class='mr-3 text-primary' title='Add'>
                    <i class='mdi mdi-plus font-size-18'></i>
                </a>
               
            </td>
        </tr>";
    }
}
//  else {
//     // No results found
//     echo '<tr><td colspan="11" class="text-center">No data available in table</td></tr>';
// }

// End table body
echo '</tbody></table>';

// Display the message
if ($RowNumber > 0) {
    echo "<p>Showing 1 to $RowNumber of $RowNumber entries</p>";
}
?>