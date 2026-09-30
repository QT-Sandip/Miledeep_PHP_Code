<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$Month = $_POST['Month'];
$Year = $_POST['Year'];

$selectedMonth = (int)$Month;
$selectedYear = (int)$Year;

$today = new DateTime();
$todayDay = (int)$today->format('j');
$todayMonth = (int)$today->format('n');
$todayYear = (int)$today->format('Y');

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

$response = [];

$monthName = date('F', mktime(0, 0, 0, $selectedMonth, 1));

// --- First Half (1–15) ---
$firstStart = new DateTime("$selectedYear-$selectedMonth-01");
$firstEnd = new DateTime("$selectedYear-$selectedMonth-15");
$firstLabel = "First Half ({$firstStart->format('Y M d')} - {$firstEnd->format('d')})";

$firstRemaining = 0;
if ($selectedMonth == $todayMonth && $selectedYear == $todayYear && $todayDay < 15) {
    $firstRemaining = 15 - $todayDay;
}

if ($firstRemaining > 0) {
    $firstLabel .= " - $firstRemaining Days left";
}

// --- Second Half (16–end) ---
$secondStart = new DateTime("$selectedYear-$selectedMonth-16");
$secondEnd = new DateTime("$selectedYear-$selectedMonth-$daysInMonth");
$secondLabel = "Second Half ({$secondStart->format('Y M d')} - {$secondEnd->format('d')})";

// Always calculate second half days left from today to end of month (if future)
if (
    $selectedMonth == $todayMonth &&
    $selectedYear == $todayYear &&
    $todayDay < $daysInMonth
) {
    $secondRemaining = $daysInMonth - max($todayDay, 15);
    if ($secondRemaining > 0) {
        $secondLabel .= " - $secondRemaining Days left";
    }
}


// Prepare JSON output
$response = [$firstLabel,$secondLabel];
echo json_encode(array("data"=>$response));
exit;						
?>