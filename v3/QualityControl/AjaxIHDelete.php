<?PHP 
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID'];
IF($TYPE=='SWAB'){
	$oldRow = legacyAuditFetchRowById('QcIHSWABResult', 'ID', $D_ID, 'QualityControl');
	$Sql_Delete="DELETE FROM `QcIHSWABResult` WHERE ID='$D_ID'";
		$stmt=$iconn->query($Sql_Delete);
		if($stmt){
			legacyAuditLogDeleteById('QcIHSWABResult', 'ID', $D_ID, $oldRow, $_POST, ['Remarks' => 'SWAB result deleted'], 'QualityControl');
		}
}
elseIF($TYPE=='ICENWATER'){
	$oldRow = legacyAuditFetchRowById('QcIHIceNWaterResult', 'ID', $D_ID, 'QualityControl');
	$Sql_Delete="DELETE FROM `QcIHIceNWaterResult` WHERE ID='$D_ID'";
		$stmt=$iconn->query($Sql_Delete);
		if($stmt){
			legacyAuditLogDeleteById('QcIHIceNWaterResult', 'ID', $D_ID, $oldRow, $_POST, ['Remarks' => 'Ice and water result deleted'], 'QualityControl');
		}
	
}elseIF($TYPE=='TPCWATER'){
	$oldRow = legacyAuditFetchRowById('QcIHTPCMicroAnalysis', 'ID', $D_ID, 'QualityControl');
	$Sql_Delete="DELETE FROM `QcIHTPCMicroAnalysis` WHERE ID='$D_ID'";
		$stmt=$iconn->query($Sql_Delete);
		if($stmt){
			legacyAuditLogDeleteById('QcIHTPCMicroAnalysis', 'ID', $D_ID, $oldRow, $_POST, ['Remarks' => 'TPC micro analysis deleted'], 'QualityControl');
		}
	
}

elseif($TYPE=='IH_MB_REPORT'){
    $oldRow = legacyAuditFetchRowById('QcIHMBParmResult', 'ID', $D_ID, 'QualityControl');
    $sql="DELETE  FROM `QcIHMBParmResult` WHERE `ID`='$D_ID'";
	$stmt=$iconn->query($sql);
	if($stmt){
		legacyAuditLogDeleteById('QcIHMBParmResult', 'ID', $D_ID, $oldRow, $_POST, ['Remarks' => 'IH MB parm result deleted'], 'QualityControl');
	}
 } 

	

?>
