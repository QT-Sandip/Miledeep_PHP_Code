<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$Grades = $_POST['Grades'];
$Rates = $_POST['Rates'];
$FromDate = $_POST['SelectDate'];
$IsEdit = $_POST['IsEdit'];
$DateID = $_POST['DateId'];
$SType = $_POST['SType'];

// PRINT_R($Grades); 
// PRINT_R($Rates); 
/*
$FromDate='2023-07-01';
$PostedDate='2023-07-20'; 
$SType='VM';

	UpdateCostingrates($FromDate, $PostedDate,$SType);
*/


$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$PostedDate = $dateTime->format("Y-m-d H:i:s");
$UserID = $_SESSION['UserID'];

if ($IsEdit == 1) {
} else if ($IsEdit == 0) {
	$sql = "SELECT * FROM SalesCostingRatesHistory order by FromDate DESC,PostedDate DESC LIMIT 1";
	$stmt = $iconn->query($sql);
	$PostDetails = $stmt->fetch_array();
	$DateID = $PostDetails['ID'];


	$SqlInsert = "INSERT INTO `SalesCostingRatesHistory` 
  (`FromDate`,`SType`, `PostedDate`,`UserID`) VALUES ('$FromDate','$SType', '$PostedDate','$UserID')";
	$StmtInsert = $iconn->query($SqlInsert);
	$TrackID = $iconn->insert_id;
	if($StmtInsert){
		legacyAuditLogInsertById('SalesCostingRatesHistory', 'ID', $TrackID, $_POST, [
			'Remarks' => 'Costing rates history created',
		], 'Sales');
	}
}


for ($i = 0; $i < count($Grades); $i++) {

	$GRAD = $Grades[$i];
	$RATE = $Rates[$i];

	$SqlGradeData = "select * from SalesCostingRatesRange where SType ='$SType' and Grade='$GRAD'";
	$StmtGradeData = $iconn->query($SqlGradeData);
	$RowGradeData = $StmtGradeData->fetch_array();
	$LowerLimit = $RowGradeData['LowerLimit'];
	$UpperLimit = $RowGradeData['UpperLimit'];

	if ($IsEdit == 1) {
		$TrackID = $DateID; //UMA 09-04-2024
		$oldCostRateData = legacyAuditFetchRowById('SalesCostingRates', 'TrackID', $TrackID, 'Sales');
		//   $SqlInsert="UPDATE `SalesCostingRates` 
		//   SET `LowerLimit`='$LowerLimit',`UpperLimit`='$UpperLimit',
		// `Grade`='$Grades',`USD`='$Rates' WHERE `TrackID`='$TrackID'";

		$SqlInsert = "UPDATE `SalesCostingRates` 
		SET `LowerLimit`='$LowerLimit',`UpperLimit`='$UpperLimit',
		`Grade`='$GRAD',`USD`='$RATE' WHERE `TrackID`='$TrackID'  and Grade='$GRAD'";
		
	} else if ($IsEdit == 0 && $DateId !== 0) {

		//  $SqlGradeData="select * from SalesCostingRatesRange where SType ='$SType' and Grade='$GRAD'";
		// $StmtGradeData=$iconn->query($SqlGradeData);
		// $RowGradeData=$StmtGradeData->fetch_array();
		// $LowerLimit=$RowGradeData['LowerLimit'];
		// $UpperLimit=$RowGradeData['UpperLimit'];

		$SqlInsert = "INSERT INTO `SalesCostingRates`(`LowerLimit`, `UpperLimit`, `Grade`, `USD`, `TrackID`)
		VALUES ('$LowerLimit', '$UpperLimit','$GRAD','$RATE','$TrackID')";
	}
	$StmtInsert = $iconn->query($SqlInsert);
	if($StmtInsert){
		if($IsEdit == 1){
			legacyAuditLogUpdateById('SalesCostingRates', 'TrackID', $TrackID, $oldCostRateData, $_POST, [
				'Remarks' => 'Costing rate updated',
			], 'Sales');
		}else{
			legacyAuditLogInsertById('SalesCostingRates', 'TrackID', $TrackID, $_POST, [
				'Remarks' => 'Costing rate inserted',
			], 'Sales');
		}
	}
}

$response = array();

if ($StmtInsert) {

	echo $TrackID;

	UpdateCostingrates($FromDate, $PostedDate, $SType);

	if ($IsEdit) {
		$Msg = 'Costing Rates Updated For Date - ';
	} else {
		$Msg = 'Costing Rates Added For Date - ';
	}
} else {
	echo 'Failed';
}



//uma//
function UpdateCostingrates($FromDate, $PostedDate, $SType)
{
	global $iconn;
	$SqlBackUp = "SELECT RmLotID FROM `RmLotDetatils` as RLD 
	 left JOIN RmPurchaseStations as RPS  on RPS.PurchaseStationID=RLD.PurchaseStationID
	 WHERE  RPS.SType='$SType'   and
	 PurchaseDate BETWEEN '$FromDate' and '$PostedDate' 
	 ORDER BY `RmLotID` DESC";
	//$SqlBackUp=	"SELECT RmLotID FROM `RmLotDetatils` WHERE `RmLotID`='16489'";

	$SqlCostingRates = "SELECT * FROM `SalesCostingRatesHistory` 
	WHERE SType='$SType' and  FromDate BETWEEN '$FromDate' and '$PostedDate' order By PostedDate Desc";

	$StmtCostingRates = $iconn->query($SqlCostingRates);
	$RowCostingRates = $StmtCostingRates->fetch_array();
	$DateID = $RowCostingRates['ID'];

	$StmtBackUp = $iconn->query($SqlBackUp);
	while ($RowBackup = $StmtBackUp->fetch_array()) {
		$LotID = $RowBackup['RmLotID'];
		if ($LotID != '') {



			$SqlGrade = "SELECT * FROM `PpcHLGradingBreakup` WHERE LotID='$LotID'";
			$StmtGrade = $iconn->query($SqlGrade);
			while ($RowGrade = $StmtGrade->fetch_array()) {

				$DataID = $RowGrade['DataID'];
				$PackingGrade = $RowGrade['PackingGrade'];


				$Sqlrates = "SELECT * FROM `SalesCostingRates` WHERE Grade='$PackingGrade' and TrackID='$DateID'";
				$Stmtrates = $iconn->query($Sqlrates);
				$Rowrates = $Stmtrates->fetch_array();
				$Usd = $Rowrates['USD'];


				$oldPpcData = legacyAuditFetchRowById('PpcHLGradingBreakup', 'DataID', $DataID, 'Sales');
				$Sqlupdate = "UPDATE `PpcHLGradingBreakup` SET `Usd`='$Usd' WHERE `DataID`='$DataID' ";
				$Stmtupdate = $iconn->query($Sqlupdate);
				if($Stmtupdate){
					legacyAuditLogUpdateById('PpcHLGradingBreakup', 'DataID', $DataID, $oldPpcData, ['Usd' => $Usd], ['Remarks' => 'HL Grading breakup USD updated from costing rates'], 'Sales');
				}
				//echo '<br>';

			}
		}
	}
}
//uma//
