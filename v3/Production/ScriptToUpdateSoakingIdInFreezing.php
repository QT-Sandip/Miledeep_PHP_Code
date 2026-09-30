<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");



$sqlVarietyID = "SELECT  TrackID,GradingBreakUpID FROM `ProdFreezingData` WHERE TypeofMaterial='SKM' and SoakingID=0 and GradingBreakupID!=0";
$stmtVarietyID = $iconn->query($sqlVarietyID);

// $sqlVarietyID = "SELECT TrackID,SckID,TubNumber,TubCode,VABreakupID FROM `ProdSoakingDetails` 
// WHERE TrackID in(SELECT distinct TrackID FROM `ProdFreezingData` WHERE TypeofMaterial='SKM' and SoakingID=0) order by TrackID desc";


// $data = '';
while ($dataRowVarietyID = $stmtVarietyID->fetch_array()) {
    $TrackID = $dataRowVarietyID['TrackID'];
    $GradingBreakUpID = $dataRowVarietyID['GradingBreakUpID'];

    $sql = "SELECT TrackID,SckID,TubNumber,TubCode,VABreakupID FROM `ProdSoakingDetails` 
WHERE TrackID = '$TrackID' and VABreakupID='$GradingBreakUpID'";
    $stmt = $iconn->query($sql);

    while ($dataRow = $stmt->fetch_array()) {

        $SckID = $dataRow['SckID'];
        $TubNumber = $dataRow['TubNumber'];
        $TubCode = $dataRow['TubCode'];
        $VABreakupID = $dataRow['VABreakupID'];

        echo $sqlUpdateProductID = "UPDATE `ProdFreezingData` SET `SoakingID`='$SckID ' WHERE  TrackID='$TrackID' and TubNo='$TubNumber' and TubCode='$TubCode' and GradingBreakupID='$VABreakupID' ";


        $stmtUpdateProductID = $iconn->query($sqlUpdateProductID);
        if ($stmtUpdateProductID) {
            echo 'HI';

        }
    }
}
echo json_encode($data, JSON_PRETTY_PRINT);
