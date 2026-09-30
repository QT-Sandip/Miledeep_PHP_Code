<?PHP 
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID'];
IF($TYPE=='SWAB'){
	
	$Sql_Delete="DELETE FROM `QcIHSWABResult` WHERE ID='$D_ID'";
		$stmt=$iconn->query($Sql_Delete);
		if($stmt){
			legacyAuditLogDeleteById('QcIHSWABResult', 'ID', $D_ID, null, $_POST, [
				'ReferenceNo' => $D_ID,
				'Remarks' => 'IH SWAB result deleted',
			], 'QualityControl');
		}

}
elseIF($TYPE=='ICENWATER'){
	
	$Sql_Delete="DELETE FROM `QcIHIceNWaterResult` WHERE ID='$D_ID'";
		$stmt=$iconn->query($Sql_Delete);
		if($stmt){
			legacyAuditLogDeleteById('QcIHIceNWaterResult', 'ID', $D_ID, null, $_POST, [
				'ReferenceNo' => $D_ID,
				'Remarks' => 'IH ice/water result deleted',
			], 'QualityControl');
		}

	
}elseIF($TYPE=='TPCWATER'){
	
	$Sql_Delete="DELETE FROM `QcIHTPCMicroAnalysis` WHERE ID='$D_ID'";
		$stmt=$iconn->query($Sql_Delete);
		if($stmt){
			legacyAuditLogDeleteById('QcIHTPCMicroAnalysis', 'ID', $D_ID, null, $_POST, [
				'ReferenceNo' => $D_ID,
				'Remarks' => 'IH TPC micro analysis deleted',
			], 'QualityControl');
		}

	
}

elseif($TYPE=='IH_MB_REPORT'){
    $sql="DELETE  FROM `QcIHMBParmResult` WHERE `ID`='$D_ID'";
	$stmt=$iconn->query($sql);
	if($stmt){
		legacyAuditLogDeleteById('QcIHMBParmResult', 'ID', $D_ID, null, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'IH MB parameter result deleted',
		], 'QualityControl');
	}
 } 

	

?>
