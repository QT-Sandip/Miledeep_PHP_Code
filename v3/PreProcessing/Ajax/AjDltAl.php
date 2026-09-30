<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID'];
$LOT_ID=$_POST['LOT_ID'];
$Q_ID=$_POST['Q_ID'];


if($TYPE=='MORE_COUNTS'){
		$oldMoreCountsData = legacyAuditFetchRowById('inputmaterial_details', 'id', $D_ID, 'PreProcessing');
	    $sql="DELETE  FROM `inputmaterial_details` WHERE `id`='$D_ID'";
	$stmt=$iconn->query($sql);


  	if($stmt){
		legacyAuditLogDeleteById('inputmaterial_details', 'id', $D_ID, $oldMoreCountsData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'More counts deleted from inputmaterial_details',
		], 'PreProcessing');

			$sql="SELECT * FROM `inputmaterial_details` where id='$D_ID'";
			$stmt=$iconn->query($sql);

	 while($row=$stmt->fetch_array()){
		 ?>

	<td >
	<td id="<?php  echo $row['ID'];?>" > <?php echo $row['COUNT']; ?></td>

	</td>

    <td>
	<td id="<?php  echo $row['ID'];?>" > <?php echo $row['WEIGHT_IN_KGS']; ?></td>
	</td>



<?php
	   }

	}

}



if($TYPE=='RM_DEFECTS'){
		$oldRmDefectsData = legacyAuditFetchRowById('RM_DEFECTS_DETAILS', 'I_ID', $D_ID, 'PreProcessing');
	    $sql="DELETE  FROM `RM_DEFECTS_DETAILS` WHERE `I_ID`='$D_ID'";
	$stmt=$iconn->query($sql);


  	if($stmt){
		legacyAuditLogDeleteById('RM_DEFECTS_DETAILS', 'I_ID', $D_ID, $oldRmDefectsData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'RM defects deleted',
		], 'PreProcessing');

			$sql="SELECT * FROM `RM_DEFECTS_DETAILS` where I_ID='$D_ID'";
			$stmt=$iconn->query($sql);

	 while($row=$stmt->fetch_array()){
?>


<td >
	<td id="<?php  echo $row['ID'];?>" > <?php echo $row['NAME']; ?></td>

	</td>

    <td>
	<td id="<?php  echo $row['ID'];?>" > <?php echo $row['WEIGHT']; ?></td>
	</td>

<?php
	   }

	}

}



if($TYPE=='HL_GRDNG_DELETE'){

echo $COUNT=$_POST['COUNT'];

	$sql="SELECT * FROM `PPC_GRADING_HL` where LOT_ID='$LOT_ID'";
	$stmt=$iconn->query($sql);
	$RowGradingHl=$stmt->fetch_array();
	$oldHlGradingData = $RowGradingHl;
	$GradingJson=$RowGradingHl['HOSO_W_DATA'];
	$IsMix=$RowGradingHl['IsMix'];
	$oldHlGradingPk = ($IsMix == 1) ? $RowGradingHl['MixIds'] : $LOT_ID;


	 $GradingJson=json_decode($GradingJson,true);

	 foreach($GradingJson  as $value){
	 				if($value['Count'] != $COUNT){
	 					$ary_samples[]=$value;
	 				}
	 			}

	     $GradingJson=json_encode($ary_samples,true);

			 if($IsMix==1){
				 	$MixIds=$RowGradingHl['MixIds'];
				 	$sql="update `PPC_GRADING_HL` set HOSO_W_DATA='$GradingJson' where MixIds='$MixIds'";
				 }else{
				 	$sql="update `PPC_GRADING_HL` set HOSO_W_DATA='$GradingJson'  where LOT_ID='$LOT_ID'";
			 }
			 	$stmt=$iconn->query($sql);
			if($stmt){
				$hlPkColumn = ($IsMix == 1) ? 'MixIds' : 'LOT_ID';
				legacyAuditLogUpdateById('PPC_GRADING_HL', $hlPkColumn, $oldHlGradingPk, $oldHlGradingData, $_POST, [
					'ReferenceNo' => $oldHlGradingPk,
					'Remarks' => 'HL grading count deleted (HOSO_W_DATA updated)',
				], 'PreProcessing');
			}
echo 	$sql;

exit;


  	if($stmt){

			$sql="SELECT * FROM `PPC_GRADING_HL` where LOT_ID='$LOT_ID'";
			$stmt=$iconn->query($sql);

	 while($row=$stmt->fetch_array()){
?>



<td >
	<td id="<?php  echo $row['ID'];?>" > <?php echo $row['NAME']; ?></td>

	</td>

    <td>
	<td id="<?php  echo $row['ID'];?>" > <?php echo $row['WEIGHT']; ?></td>
	</td>

<?php
	   }

	}

}



if($TYPE=='GRDNG_DELETE'){
$LotID=$_POST['LotID'];

	$sql="SELECT * FROM `PpcHLGradingDetails` where LotID='$LotID'";
	$stmt=$iconn->query($sql);
	$RowGradingHl=$stmt->fetch_array();
	$oldGradingDetailsData = $RowGradingHl;
	$oldGradingDetailsPk = ($RowGradingHl['IsMix'] == 1) ? $RowGradingHl['MixIDs'] : $LotID;
	//$GradingJson=$RowGradingHl['HOSO_W_DATA'];
	$IsMix=$RowGradingHl['IsMix'];


			 if($IsMix==1){
				 	$MixIds=$RowGradingHl['MixIDs'];
				 	$SqlDetails="delete from `PpcHLGradingDetails`  where MixIDs='$MixIds'";
				 	$SqlBreakup="DELETE FROM `PpcHLGradingBreakup` WHERE MixedID='$MixIds'";
				 }else{
				 	$SqlDetails="delete from  `PpcHLGradingDetails`  where LotID='$LotID'";
				 	$SqlBreakup="delete from  `PpcHLGradingBreakup`  where LotID='$LotID'";
			 }
			 	$StmtDetails=$iconn->query($SqlDetails);
			 	$StmtBreakup=$iconn->query($SqlBreakup);
if($StmtDetails&&$StmtBreakup){
		$gdPkColumn = ($IsMix == 1) ? 'MixIDs' : 'LotID';
		legacyAuditLogDeleteById('PpcHLGradingDetails', $gdPkColumn, $oldGradingDetailsPk, $oldGradingDetailsData, $_POST, [
			'ReferenceNo' => $oldGradingDetailsPk,
			'Remarks' => 'Grading details and breakup deleted',
		], 'PreProcessing');
$row['sts']='success';
		$row['msg']='Deleted  Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Delete';
		 echo json_encode($row);
	 }
}



if($TYPE=='GRDNGID_DELETE'){

$GradeID=$_POST['GradeID'];
	$oldGradingBreakupData = legacyAuditFetchRowById('PpcHLGradingBreakup', 'DataID', $GradeID, 'PreProcessing');

 $sql="DELETE  FROM `PpcHLGradingBreakup` WHERE `DataID`='$GradeID'"; 
	$stmt=$iconn->query($sql);
	

  	if($stmt){
		legacyAuditLogDeleteById('PpcHLGradingBreakup', 'DataID', $GradeID, $oldGradingBreakupData, $_POST, [
			'ReferenceNo' => $GradeID,
			'Remarks' => 'Grading ID deleted',
		], 'PreProcessing');
$row['sts']='success';
		$row['msg']='Deleted  Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Delete';
		 echo json_encode($row);
	 }


}


if($TYPE=='VA_REPORT'){
		$oldVaData = legacyAuditFetchRowById('PpcValueAddition', 'VLID', $D_ID, 'PreProcessing');
	    $sql="DELETE  FROM `PpcValueAddition` WHERE `VLID`='$D_ID'"; 
	$stmt=$iconn->query($sql);


  	if($stmt){
		legacyAuditLogDeleteById('PpcValueAddition', 'VLID', $D_ID, $oldVaData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'Value addition report deleted',
		], 'PreProcessing');
$row['sts']='success';
		$row['msg']='Deleted  Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Delete';
		 echo json_encode($row);
	 }
			
	}
if($TYPE=='DeheadingReport'){
		$oldDeheadingData = legacyAuditFetchRowById('PpcDeheading', 'DHID', $D_ID, 'PreProcessing');
	    $sql="DELETE  FROM `PpcDeheading` WHERE `DHID`='$D_ID'"; 
	$stmt=$iconn->query($sql);


  	if($stmt){
		legacyAuditLogDeleteById('PpcDeheading', 'DHID', $D_ID, $oldDeheadingData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'Deheading report deleted',
		], 'PreProcessing');
$row['sts']='success';
		$row['msg']='Deleted  Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Delete';
		 echo json_encode($row);
	 }
			
	}


///////////////////////
if($TYPE=='VA'){

	$oldCsPalletData = legacyAuditFetchRowById('CS_PALLET_INPUT_DATA', 'PALLEDT_ID', $Q_ID, 'PreProcessing');
	$sql="DELETE  FROM `CS_PALLET_INPUT_DATA` WHERE `PALLEDT_ID`='$Q_ID'";
	$stmt=$iconn->query($sql);
	

  	if($stmt){
		legacyAuditLogDeleteById('CS_PALLET_INPUT_DATA', 'PALLEDT_ID', $Q_ID, $oldCsPalletData, $_POST, [
			'ReferenceNo' => $Q_ID,
			'Remarks' => 'Pallet input data deleted',
		], 'PreProcessing');
ECHO 'SUCCESS';

	}ELSE{
		ECHO 'FAILURE';
	}

}
///////////////






/*if($TYPE=='SOACKING_REPORT'){
	 $sql="DELETE  FROM `soaking_report` WHERE `id`='$D_ID'";
	$stmt=$iconn->query($sql);


  	if($stmt){

			$sql="SELECT * FROM `soaking_report` where id='$D_ID'";
			$stmt=$iconn->query($sql);

	 while($row=$stmt->fetch_array()){
?>


<td >
	<td id="<?php  echo $row['ID'];?>" > <?php echo $row['NAME']; ?></td>

	</td>

    <td>
	<td id="<?php  echo $row['ID'];?>" > <?php echo $row['WEIGHT']; ?></td>
	</td>

<?php
	   }

	}

}*/

?>
