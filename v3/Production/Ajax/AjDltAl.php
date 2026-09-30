<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID'];
$LOT_ID=$_POST['LOT_ID'];
$Q_ID=$_POST['Q_ID'];


if($TYPE=='MORE_COUNTS'){
	$oldData = legacyAuditFetchRowById('inputmaterial_details', 'id', $D_ID, 'Production');
	    $sql="DELETE  FROM `inputmaterial_details` WHERE `id`='$D_ID'";
	$stmt=$iconn->query($sql);


  	if($stmt){
		legacyAuditLogDeleteById('inputmaterial_details', 'id', $D_ID, $oldData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'More counts deleted',
		], 'Production');

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
	$oldData = legacyAuditFetchRowById('RM_DEFECTS_DETAILS', 'I_ID', $D_ID, 'Production');
	    $sql="DELETE  FROM `RM_DEFECTS_DETAILS` WHERE `I_ID`='$D_ID'";
	$stmt=$iconn->query($sql);


  	if($stmt){
		legacyAuditLogDeleteById('RM_DEFECTS_DETAILS', 'I_ID', $D_ID, $oldData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'RM defects deleted',
		], 'Production');

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
	$GradingJson=$RowGradingHl['HOSO_W_DATA'];
	$IsMix=$RowGradingHl['IsMix'];


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
echo 	$sql;

if($stmt){
	$updId = $IsMix ? $MixIds : $LOT_ID;
	$updField = $IsMix ? 'MixIds' : 'LOT_ID';
	legacyAuditLogUpdateById('PPC_GRADING_HL', $updField, $updId, $RowGradingHl, $_POST, [
		'ReferenceNo' => $updId,
		'Remarks' => 'HL grading entry deleted',
	], 'Production');
}

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


	$sql="SELECT * FROM `PPC_GRADING_HL` where LOT_ID='$LOT_ID'";
	$stmt=$iconn->query($sql);
	$RowGradingHl=$stmt->fetch_array();
	$GradingJson=$RowGradingHl['HOSO_W_DATA'];
	$IsMix=$RowGradingHl['IsMix'];
	$oldGradingData = $RowGradingHl;


			 if($IsMix==1){
				 	$MixIds=$RowGradingHl['MixIds'];
				 	$sql="delete from `PPC_GRADING_HL`  where MixIds='$MixIds'";
				 }else{
				 	$sql="delete from  `PPC_GRADING_HL`  where LOT_ID='$LOT_ID'";
			 }
			 	$stmt=$iconn->query($sql);
if($stmt){
		$delId = $IsMix ? $MixIds : $LOT_ID;
		$delField = $IsMix ? 'MixIds' : 'LOT_ID';
		legacyAuditLogDeleteById('PPC_GRADING_HL', $delField, $delId, $oldGradingData, $_POST, [
			'ReferenceNo' => $delId,
			'Remarks' => 'Grading data deleted',
		], 'Production');
	echo "SUCCESS";
}else{
echo	"FAILURE";
}
}


///////////////////////
if($TYPE=='IQfDelete'){
	$oldData = legacyAuditFetchRowById('ProdProductionData', 'ProdID', $Q_ID, 'Production');
	$sql="DELETE  FROM `ProdProductionData` WHERE `ProdID`='$Q_ID'";
	$stmt=$iconn->query($sql);
	

  	if($stmt){
		legacyAuditLogDeleteById('ProdProductionData', 'ProdID', $Q_ID, $oldData, $_POST, [
			'ReferenceNo' => $Q_ID,
			'Remarks' => 'IQF report deleted',
		], 'Production');
$row['sts']='success';
		$row['msg']='Deleted  Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Delete';
		 echo json_encode($row);
	 }

}
///////////////

if($TYPE=='ReglazingDelete'){
	$oldData = legacyAuditFetchRowById('ProdReglazingData', 'ProdID', $Q_ID, 'Production');
	$sql="DELETE  FROM `ProdReglazingData` WHERE `ProdID`='$Q_ID'";
	$stmt=$iconn->query($sql);
	

  	if($stmt){
		legacyAuditLogDeleteById('ProdReglazingData', 'ProdID', $Q_ID, $oldData, $_POST, [
			'ReferenceNo' => $Q_ID,
			'Remarks' => 'Reglazing report deleted',
		], 'Production');
$row['sts']='success';
		$row['msg']='Deleted  Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Delete';
		 echo json_encode($row);
	 }

}




if($TYPE=='SOACKING_REPORT'){
	$oldData = legacyAuditFetchRowById('ProdSoakingDetails', 'SckID', $D_ID, 'Production');
	 $sql="DELETE  FROM `ProdSoakingDetails` WHERE `SckID`='$D_ID'"; 
	$stmt=$iconn->query($sql);


  	
if($stmt){
		legacyAuditLogDeleteById('ProdSoakingDetails', 'SckID', $D_ID, $oldData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'Soaking report deleted',
		], 'Production');
	$row['sts']='success';
		$row['msg']='Deleted  Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Delete';
		 echo json_encode($row);
	 }
}

?>
