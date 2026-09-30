<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$Type = $_POST['Type'];
$LotId = $_POST['LotId'];
$KitBreakupID = $_POST['KitBreakupID'];
$Parameter = $_POST['Parameter'];


if ($Type == 'Count') {
    // Retrieve LotIds as an array from the POST request
    $LotIds = isset($_POST['LotIds']) ? $_POST['LotIds'] : [];

    if (!empty($LotIds)) {
        // Sanitize LotIds and prepare them for the IN clause
        $SelectedLotIds = implode(",", array_map('intval', $LotIds));

        // Updated SQL query with IN clause
        $SqlLot = "
            SELECT 
                group_concat(distinct PurchaseCount) as PurchaseCount, 
                group_concat(distinct PurchaseWeight)as PurchaseWeight, 
                group_concat(distinct LotNumber) as LotNumber, 
                group_concat(distinct RLD.FarmAddress) as FarmAddress, 
                group_concat(distinct FarmerName  ) as FarmerName
            FROM 
                `RmLotDetatils` AS RLD
            LEFT JOIN 
                `RmBookingDetatils` AS RBD 
                ON RBD.BookingNumber = RLD.LotNumber
            LEFT JOIN 
                `RmHarvestingDetails` AS RHD 
                ON RHD.BookingID = RBD.RmBookingID
            WHERE 
                RmLotID IN ($SelectedLotIds)
        ";


        $StmtLot = $iconn->query($SqlLot);

        // Fetch all results into an array
        $Data = [];
        while ($row = $StmtLot->fetch_assoc()) {
            $Data[] = $row;
        }

        // Return JSON-encoded array
        echo json_encode($Data);
    } else {
        echo json_encode(['error' => 'No LotIds provided.']);
    }
} else if ($Type == 'GetKitbreakupLotNumbers') {
    // $SqlLot="SELECT * FROM `RmLotDetatils` WHERE RmLotID='$LotId'";
    $SqlKitBreakupLots = "SELECT KitBreakupID,KitBreakupLotNo FROM `QcKitsDetails` as QKD 
	left join QcKitsBreakupDetails as QKBD on QKBD.KitID=QKD.KitID
WHERE Parameter='$Parameter' and BalanceWells!=0";

    $StmtKitBreakupLots = $iconn->query($SqlKitBreakupLots);
    while ($KitBreakupLotsData = $StmtKitBreakupLots->fetch_array()) {
        $ArrayData[] = $KitBreakupLotsData;
    }
    echo json_encode($ArrayData);

} else if ($Type == 'Delete') {
    $AbReportID = $_POST['AbReportID'];
    // $SqlLot="SELECT * FROM `RmLotDetatils` WHERE RmLotID='$LotId'";
    $SqlKitData = "SELECT QRAB.KitBreakUpID,BreakUpwiseUsedWells,BreakUpwiseDamagedWells,UtilizedWells,BalanceWells,DamagedWells FROM `QcRmAbUtilizedKitBreakupDetails` as QRAB 
    left join QcKitsBreakupDetails as QKBD on QKBD.KitBreakupID=QRAB.KitBreakUpID WHERE AbReportID='$AbReportID'";

    $StmtKitData = $iconn->query($SqlKitData);
    while ($Data = $StmtKitData->fetch_array()) {
        $KitBreakUpID = $Data['KitBreakUpID'];
        $UpdatedUsedWells = $Data['UtilizedWells'] - $Data['BreakUpwiseUsedWells'];
        $UpdatedDamagedWells = $Data['DamagedWells'] - $Data['BreakUpwiseDamagedWells'];
        $UpdatedBalanceWells = $Data['BalanceWells'] + $Data['BreakUpwiseUsedWells'] + $Data['BreakUpwiseDamagedWells'];

        $SqlkitDataUpdate = "UPDATE `QcKitsBreakupDetails` SET `UtilizedWells`='$UpdatedUsedWells',`BalanceWells`='$UpdatedBalanceWells',`DamagedWells`='$UpdatedDamagedWells'
         WHERE `KitBreakupID`='$KitBreakUpID'";

        $StmtKitUpdate = $iconn->query($SqlkitDataUpdate);
        if ($StmtKitUpdate) {
            legacyAuditLogUpdateById('QcKitsBreakupDetails', 'KitBreakupID', $KitBreakUpID, null, $_POST, [
                'ReferenceNo' => $AbReportID,
                'Remarks' => 'Kit breakup wells updated on AB report delete',
            ], 'QualityControl');
        }
    }
    $SqlDeleteUtilizedKitsData = "DELETE FROM `QcRmAbUtilizedKitBreakupDetails` WHERE AbReportID='$AbReportID'";
    $StmtDeleteUtilizedKitsData = $iconn->query($SqlDeleteUtilizedKitsData);
    if ($StmtDeleteUtilizedKitsData) {
        legacyAuditLogDeleteById('QcRmAbUtilizedKitBreakupDetails', 'AbReportID', $AbReportID, null, $_POST, [
            'ReferenceNo' => $AbReportID,
            'Remarks' => 'Utilized kit breakup details deleted',
        ], 'QualityControl');
        $SqlDeleteAbReportData = "DELETE FROM `QcRmAbReport` WHERE AbRepID='$AbReportID'";
        $StmtDeleteAbReportData = $iconn->query($SqlDeleteAbReportData);
        if ($StmtDeleteAbReportData) {
            legacyAuditLogDeleteById('QcRmAbReport', 'AbRepID', $AbReportID, null, $_POST, [
                'ReferenceNo' => $AbReportID,
                'Remarks' => 'AB report deleted',
            ], 'QualityControl');
        }

        $error = null;
        $Msg = 'Deleted Successfully';

    } else {
        $error = $iconn->error;
    }
    $data['error'] = $error;
    $data['Msg'] = $Msg;
    echo json_encode($Data);

} else if ($Type == 'GetKitDetails') {
    // Retrieve KitBreakupIDs as an array from the POST request
    $KitBreakupIDs = isset($_POST['KitBreakupIDs']) ? $_POST['KitBreakupIDs'] : [];

    if (!empty($KitBreakupIDs)) {
        // Sanitize KitBreakupIDs and prepare them for the IN clause
        $SanitizedKitBreakupIDs = implode(",", array_map('intval', $KitBreakupIDs));

        // Updated SQL query with IN clause
        $SqlKitData = "
            SELECT BalanceWells, KitOpeningDate 
            FROM `QcKitsBreakupDetails` 
            WHERE KitBreakupID IN ($SanitizedKitBreakupIDs)
        ";

        $StmtKitData = $iconn->query($SqlKitData);

        // Fetch all results into an array
        $Data = [];
        while ($row = $StmtKitData->fetch_assoc()) {
            $Data[] = $row;
        }

        // Return JSON-encoded array
        echo json_encode($Data);
    } else {
        echo json_encode(['error' => 'No KitBreakupIDs provided.']);
    }
} else if ($Type == 'GetStandardWells') {

    $SpkBlkStd = $_POST['SpkBlkStd'];
    $AnalysisDate = $_POST['AnalysisDate'];
    $Shift = $_POST['Shift'];
    $Parameter = $_POST['Parameter'];
    $TotalStandardUsedWells = 0;

    
    $AdminConfigDetails = isset($_SESSION['ClientConfigData']) ? $_SESSION['ClientConfigData'] : [];
    $clientID = isset($AdminConfigDetails['ClientID']) ? strtoupper(trim($AdminConfigDetails['ClientID'])) : "";


    if ($SpkBlkStd == 1) {
        $SqlAutoDeductCount = "SELECT count(*) as AutoDeductCount,(SELECT sum(StandardUsedWells) FROM `QcRmAbUtilizedKitBreakupDetails` 
        where AbReportID in (QRAB.AbRepID))  as StandardUsedWells FROM `QcRmAbReport` as QRAB
 WHERE  AnalysisDate='$AnalysisDate' and Parameter='$Parameter'
        and Shift='$Shift' and SpkBlkStd='1' ";

       $StmtAutoDeductCount = $iconn->query($SqlAutoDeductCount);
        $RowAutoDeductCount = $StmtAutoDeductCount->fetch_array();
    //     if ($RowAutoDeductCount['StandardUsedWells'] == 0) {
    //         $TotalStandardUsedWells = 8;

    //     }
    // }

      if ($RowAutoDeductCount['StandardUsedWells'] == 0) {
           
            if ($clientID === "SMEV3") {
                $TotalStandardUsedWells = 6;
            } else {
                $TotalStandardUsedWells = 8;
            }
        }
    }

    // Updated SQL query with IN clause


    // Return JSON-encoded array

    echo json_encode(["standardWells" => $TotalStandardUsedWells]);
    // echo json_encode($TotalStandardUsedWells);

}




?>
