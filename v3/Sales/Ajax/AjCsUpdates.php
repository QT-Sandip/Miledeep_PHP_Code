<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$Type = $_POST['Type'];

$ConsolidateGrossweightPopup = $_SESSION["ClientConfigData"]["ConsolidateGrossweightPopup"];

if ($Type == 'GrosswtUpdate') {

	$InvId = $_POST['InvoiceID'];
	$Backurl = $_POST['Backurl'];
	$AryBreakUp = $_POST['AryBreakUp'];
	$AryBreakUp = json_decode($AryBreakUp, true);

	foreach ($AryBreakUp as $Item) {

		$ArrayData = getupdatedQuery($iconn, $Item, $InvId, $ConsolidateGrossweightPopup, $Backurl);
	}
	$SqlTotalGrossWeight = "SELECT sum(GrossWt) as TotalGrossWt FROM `ScrmPoGradeData` where InvId='$InvId'";
	$StmtTotalGrossWeight = $iconn->query($SqlTotalGrossWeight);
	$RowTotalGrossWeight = $StmtTotalGrossWeight->fetch_array();
	$TotalGrossWeight = $RowTotalGrossWeight['TotalGrossWt'];


	$oldCsData = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $InvId, 'Sales');
	$SqlCustomInvoice = "UPDATE `ScrmCustomInvoices` SET `TotalGrossWt`='$TotalGrossWeight' WHERE `PiID`='$InvId' ";
	$StmtCustomInvoice = $iconn->query($SqlCustomInvoice);
	if($StmtCustomInvoice){ legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $InvId, $oldCsData, ['TotalGrossWt' => $TotalGrossWeight], ['Remarks' => 'Total gross weight updated'], 'Sales'); }


	if ($StmtCustomInvoice) {
		$error = null;

		$Msg = 'Updated Successfully';
	} else {
		$error = $iconn->error;
	}
	$data['error'] = $error;
	$data['Msg'] = $Msg;
	$data['TotalGrossWt'] = $TotalGrossWt;

	echo json_encode($data);

} else if ($Type == 'CurrentMcsUpdate') {

	$ScrmPGDID = $_POST['GradeId'];
	$CurrentMcs = $_POST['CurrentMcs'];

	$oldMcsData = legacyAuditFetchRowById('ScrmPoGradeData', 'ID', $ScrmPGDID, 'Sales');
	$sql = "UPDATE `ScrmPoGradeData` SET 
	`CurrentMcs`='$CurrentMcs'
	
	WHERE `ID`='$ScrmPGDID'";
	$stmt = $iconn->query($sql);
	if($stmt){ legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $ScrmPGDID, $oldMcsData, ['CurrentMcs' => $CurrentMcs], ['Remarks' => 'Current MCS updated'], 'Sales'); }

	if ($stmt) {
		$error = null;
		$Msg = 'Updated Successfully';
	} else {
		$error = $iconn->error;
	}
	$data['error'] = $error;
	$data['Msg'] = $Msg;

	echo json_encode($data);

}

function getupdatedQuery($iconn, $Item, $InvId, $ConsolidateGrossweightPopup, $Backurl)
{
	// echo $Backurl;exit;
	$GrossWeight = $Item['GrossWeight'];
	$PackingStyleID = $Item['PckId'];
	$PoGradeID = $Item['PckId'];

	if ($ConsolidateGrossweightPopup == 0) {


		if ($Backurl != '') {
			$SqlPoGradeData = "UPDATE `ScrmPoGradeData` SET `GrossWt`='$GrossWeight' WHERE `ID`='$PoGradeID'";
		} else {
			$SqlPoGradeData = "UPDATE `ScrmPoGradeData` SET `GrossWt`='$GrossWeight' , `PIGrossWt`='$GrossWeight' WHERE `ID`='$PoGradeID'"; //uma

		}

		$StmtPoGradeData = $iconn->query($SqlPoGradeData);

	} else {


		$SqlMcs = "SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` WHERE PackingStyleID='$PackingStyleID' and InvId='$InvId'";
		$StmtMcs = $iconn->query($SqlMcs);
		$RowMcs = $StmtMcs->fetch_array();
		$GrossWeightPerCarton = $GrossWeight / $RowMcs['Mcs'];


		$SqlMcs = "SELECT  ID as PoGradeID,Mcs FROM `ScrmPoGradeData` WHERE PackingStyleID='$PackingStyleID' and InvId='$InvId'";
		$StmtMcs = $iconn->query($SqlMcs);
		while ($RowMcs = $StmtMcs->fetch_array()) {
			$Mcs = $RowMcs['Mcs'];
			$PoGradeID = $RowMcs['PoGradeID'];
			$GrossWeightPackingStyle = $GrossWeightPerCarton * $Mcs;


			// $GrossWeightPackingStyle = round($GrossWeightPackingStyle, 2);

			if ($Backurl != '') {

				$SqlPoGradeData = "UPDATE `ScrmPoGradeData` SET `GrossWt`='$GrossWeightPackingStyle' WHERE `InvId`='$InvId' and `ID`in ($PoGradeID)";
			} else {


				$SqlPoGradeData = "UPDATE `ScrmPoGradeData` SET `GrossWt`='$GrossWeightPackingStyle' ,`PIGrossWt`='$GrossWeightPackingStyle'
WHERE `InvId`='$InvId' and  `ID` in ($PoGradeID)";
			}

			$StmtPoGradeData = $iconn->query($SqlPoGradeData);
		}
	}
	return "Success";
}
