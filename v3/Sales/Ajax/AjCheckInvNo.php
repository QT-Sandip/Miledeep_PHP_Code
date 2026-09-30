<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$CheckCInvNoIn = $ArrayClientConfigData['CheckCInvNoIn'];


$AvalMcs = $_POST['AvalMcs'];

$InvNo = $_POST['InvNo'];
$InvDate = $_POST['InvDate'];
$PoID = $_POST['PoID'];

if ($PoID != '') {
	$Data = checkInvoiceNo($InvDate, $InvNo, $PoID);
	echo json_encode($Data);
}





function checkInvoiceNo($InvDate, $CInvNo, $PoID)
{
	global $iconn, $CheckCInvNoIn;

	$SqlExpDetails = "SELECT ExporterID,ProcessorID FROM `ScrmPoData` WHERE PoID='$PoID'";
	$StmtExpDetails = $iconn->query($SqlExpDetails);
	$ExpDetails = $StmtExpDetails->fetch_array();
	$ExporterID = $ExpDetails['ExporterID'];
	$ProcessorID = $ExpDetails['ProcessorID'];


	$StartYear = date('Y', strtotime($InvDate));
	$Month = date('m', strtotime($InvDate));
	if ($Month < 4) {
		$StartYear--;
	}
	$EndYear = $StartYear + 1;
	$FinStartDate = $StartYear . '-04-01';
	$FinEndDate = $EndYear . '-03-31';

	$Query = '';

	if ($CheckCInvNoIn == '') {
		$Query = "CInvDate BETWEEN '$FinStartDate' and '$FinEndDate'";
	} else {
		$Query = "PoDate BETWEEN '$FinStartDate' and '$FinEndDate'";
	}

	$SqlInvCount = "SELECT * FROM `ScrmCustomInvoices` as SCI left Join ScrmPoData as SPD on SPD.PoID=SCI.PiID 
					WHERE $Query
					and SPD.ExporterID='$ExporterID' and ProcessorID='$ProcessorID'
					and CInvNo='$CInvNo'";
	$StmtInvCount = $iconn->query($SqlInvCount);
	$InvCount = mysqli_num_rows($StmtInvCount);

	$RowCount = $StmtInvCount->fetch_array();
	if ($InvCount == 0) {
		$Data['Error'] = null;
	} else if ($RowCount['PiID'] == $PoID) {
		$Data['Error'] = null;
	} else {
		$Data['Error'] = 'Invoice Number Already Exist';
	}
	return $Data;

}
?>