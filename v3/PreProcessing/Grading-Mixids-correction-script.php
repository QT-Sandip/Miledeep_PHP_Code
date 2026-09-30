<?php
// ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$PageTitle = "Barcode List";
$PageTitleFolder = "Cold Store";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$ClientID = $_SESSION["ClientID"];



$iconn->query("SET SESSION group_concat_max_len = 10000"); // Prevent truncation

$sql = "SELECT ReceivingID FROM `RmReceivingDetails` WHERE ReceivingID IN (2652)";
$stmt = $iconn->query($sql);

while ($row1 = $stmt->fetch_array()) {
    $ReceivedID = $row1['ReceivingID'] ? $row1['ReceivingID'] : null;

    if (empty($ReceivedID)) {
        echo "Skipping empty ReceivingID <br>";
        continue;
    }

    // Debugging - Print current ReceivingID
    echo "Processing ReceivingID: $ReceivedID <br>";

    // Check if TrackID exists in PpcHLGradingDetails before updating
    $sqlCheck = "SELECT COUNT(*) as total FROM `PpcHLGradingDetails` WHERE TrackID = $ReceivedID";
    $resCheck = $iconn->query($sqlCheck);
    $rowCheck = $resCheck->fetch_assoc();

    if ($rowCheck['total'] == 0) {
        echo "No records found for TrackID = $ReceivedID <br>";
        continue;
    }

    $sqlGradingQuery = "UPDATE `PpcHLGradingDetails` 
SET `MixIDs` = (SELECT GROUP_CONCAT(LotID) FROM `PpcHLGradingDetails` WHERE TrackID =  $ReceivedID) 
WHERE TrackID = $ReceivedID";

    if (!$iconn->query($sqlGradingQuery)) {
        echo "Error in sqlGradingQuery for TrackID $ReceivedID: " . $iconn->error . "<br>";
    } else {
        echo "Updated MixIDs for TrackID $ReceivedID <br>";
    }

    echo "<hr/>";

    $sqlGradingBreakupQuery = "UPDATE `PpcHLGradingBreakup` 
SET `MixedID` = (
    SELECT GROUP_CONCAT(LotID) 
    FROM `PpcHLGradingDetails` 
    WHERE TrackID = $ReceivedID
)
WHERE LotID IN (
    SELECT LotID 
    FROM `PpcHLGradingDetails` 
    WHERE TrackID = $ReceivedID
)";

    if (!$iconn->query($sqlGradingBreakupQuery)) {
        echo "Error in sqlGradingBreakupQuery for TrackID $ReceivedID: " . $iconn->error . "<br>";
    } else {
        echo "Updated MixedID for TrackID $ReceivedID <br>";
    }

    echo "<hr/>";
}

exit;
?>