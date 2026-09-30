<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


$Type = $_POST['Type'];
$ClickedId = $_POST['ClickedId']; //D_ID
$LotId = $_POST['LotId'];
$Q_ID = $_POST['Q_ID'];


if ($Type == 'MoreCounts') {
	$SqlDelLot = "DELETE  FROM `$RmLotDetatils` WHERE `RmLotID`='$ClickedId'";
	$StmtDelLot = $iconn->query($SqlDelLot);

	if ($StmtDelLot) {

		$error = null;
		$Msg = 'Count Deleted Successfully';

		$SqlRmLotDetatils = "SELECT * FROM `$RmLotDetatils` where RmLotID='$ClickedId'";
		$StmtRmLotDetatils = $iconn->query($SqlRmLotDetatils);

		while ($RowRmLotDetatils = $StmtRmLotDetatils->fetch_array()) {
?>

			<td>
			<td id="<?php echo $RowRmLotDetatils['RmLotID']; ?>"> <?php echo $RowRmLotDetatils['COUNT']; ?></td>

			</td>

			<td>
			<td id="<?php echo $RowRmLotDetatils['RmLotID']; ?>"> <?php echo $RowRmLotDetatils['PurchaseWeight']; ?></td>
			</td>



		<?php
		}
	} else {
		$error =  $iconn->error;
	}
	$data['error'] = $error;
	$data['Msg'] = $Msg;

	echo json_encode($data);
}





if ($Type == 'RM_DEFECTS') {
	$sql = "DELETE  FROM `RM_DEFECTS_DETAILS` WHERE `ID`='$ClickedId'";
	$stmt = $iconn->query($sql);


	if ($stmt) {
		echo "SUCCESS";
	}
}



if ($Type == 'HL_GRDNG_DELETE') {

	echo $COUNT = $_POST['COUNT'];

	$sql = "SELECT * FROM `PPC_GRADING_HL` where LOT_ID='$LotId'";
	$stmt = $iconn->query($sql);
	$RowGradingHl = $stmt->fetch_array();
	$GradingJson = $RowGradingHl['HOSO_W_DATA'];
	$IsMix = $RowGradingHl['IsMix'];


	$GradingJson = json_decode($GradingJson, true);

	foreach ($GradingJson  as $value) {
		if ($value['Count'] != $COUNT) {
			$ary_samples[] = $value;
		}
	}

	$GradingJson = json_encode($ary_samples, true);

	if ($IsMix == 1) {
		$MixIds = $RowGradingHl['MixIds'];
		$sql = "update `PPC_GRADING_HL` set HOSO_W_DATA='$GradingJson' where MixIds='$MixIds'";
	} else {
		$sql = "update `PPC_GRADING_HL` set HOSO_W_DATA='$GradingJson'  where LOT_ID='$LotId'";
	}
	$stmt = $iconn->query($sql);
	echo 	$sql;

	exit;


	if ($stmt) {

		$sql = "SELECT * FROM `PPC_GRADING_HL` where LOT_ID='$LotId'";
		$stmt = $iconn->query($sql);

		while ($row = $stmt->fetch_array()) {
		?>



			<td>
			<td id="<?php echo $row['ID']; ?>"> <?php echo $row['NAME']; ?></td>

			</td>

			<td>
			<td id="<?php echo $row['ID']; ?>"> <?php echo $row['WEIGHT']; ?></td>
			</td>

		<?php
		}
	}
}



if ($Type == 'GRDNG_DELETE') {


	$sql = "SELECT * FROM `PPC_GRADING_HL` where LOT_ID='$LotId'";
	$stmt = $iconn->query($sql);
	$RowGradingHl = $stmt->fetch_array();
	$GradingJson = $RowGradingHl['HOSO_W_DATA'];
	$IsMix = $RowGradingHl['IsMix'];


	if ($IsMix == 1) {
		$MixIds = $RowGradingHl['MixIds'];
		$sql = "delete from `PPC_GRADING_HL`  where MixIds='$MixIds'";
	} else {
		$sql = "delete from  `PPC_GRADING_HL`  where LOT_ID='$LotId'";
	}
	$stmt = $iconn->query($sql);
	if ($stmt) {
		echo "SUCCESS";
	} else {
		echo	"FAILURE";
	}
}

if ($Type == 'ReturnLot') {

	$SqlReturnLot = "UPDATE RmLotDetatils SET `LotStatus`='2' WHERE `RmLotID`='$LotId'";
	$SqlReturnRMLot = "UPDATE RmArrivalDetatils set LotStatus=2 WHERE RmArrivalID=(SELECT RmReferenceID from RmLotDetatils WHERE RmLotID='$LotId')";
	
	if ($iconn->query($SqlReturnLot) && $iconn->query($SqlReturnRMLot)) {
		$error = null;
		$Msg = 'Updated Successfully';
	} else {
		$error =  $iconn->error;
	}

	$data['error'] = $error;
	$data['Msg'] = $Msg;

	echo json_encode($data);
}



if ($Type == 'CancelBooking') {

	$SqlReturnLot = "UPDATE RmBookingDetatils SET `LotStatus`='2' WHERE `RmBookingID`='$LotId'";
	if ($iconn->query($SqlReturnLot)) {
		$error = null;
		$Msg = 'Updated Successfully';
	} else {
		$error =  $iconn->error;
	}
	$data['error'] = $error;
	$data['Msg'] = $Msg;

	echo json_encode($data);
}



if ($Type == 'VAReport') {
	$sql = "DELETE  FROM `PPC_VALUE_ADDITION` WHERE `V_ID`='$ClickedId'";
	$stmt = $iconn->query($sql);


	if ($stmt) {

		$sql = "SELECT * FROM `PPC_VALUE_ADDITION` where V_ID='$ClickedId'";
		$stmt = $iconn->query($sql);

		while ($row = $stmt->fetch_array()) {
		?>



			<td>
			<td id="<?php echo $row['ID']; ?>"> <?php echo $row['NAME']; ?></td>

			</td>

			<td>
			<td id="<?php echo $row['ID']; ?>"> <?php echo $row['WEIGHT']; ?></td>
			</td>

		<?php
		}
	}
}
///////////////////////
if ($Type == 'VA') {

	$sql = "DELETE  FROM `CS_PALLET_INPUT_DATA` WHERE `PALLEDT_ID`='$Q_ID'";
	$stmt = $iconn->query($sql);


	if ($stmt) {
		echo 'SUCCESS';
	} else {
		echo 'FAILURE';
	}
}
///////////////






if ($Type == 'SOACKING_REPORT') {
	$sql = "DELETE  FROM `soaking_report` WHERE `id`='$ClickedId'";
	$stmt = $iconn->query($sql);


	if ($stmt) {

		$sql = "SELECT * FROM `soaking_report` where id='$ClickedId'";
		$stmt = $iconn->query($sql);

		while ($row = $stmt->fetch_array()) {
		?>


			<td>
			<td id="<?php echo $row['ID']; ?>"> <?php echo $row['NAME']; ?></td>

			</td>

			<td>
			<td id="<?php echo $row['ID']; ?>"> <?php echo $row['WEIGHT']; ?></td>
			</td>

<?php
		}
	}
}

?>