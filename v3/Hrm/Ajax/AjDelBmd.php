<?php
include "../../includes/config.php";

$ID=$_POST['ID'];
$TYPE=$_POST['TYPE'];
$IPAddress=$_POST['IPAddress'];


IF($ID!=''){
	
	$oldBmd = legacyAuditFetchRowById('HrmBioMetricDevices', 'ID', $ID, 'Hrm');
	$SQL_DELETE="DELETE FROM `HrmBioMetricDevices` WHERE `ID`= '$ID'"; 

	$stmt=$iconn->query($SQL_DELETE);


IF($stmt){
	legacyAuditLogDeleteById('HrmBioMetricDevices', 'ID', $ID, $oldBmd, $_POST, ['ID' => $ID, 'Remarks' => 'Biometric device deleted'], 'Hrm');
ECHO "SUCESS"	;
	
}ELSE{
	
ECHO "FAILED"	;
		
	
}

}


else if($TYPE=='Updatedate'){
	
	date_default_timezone_set('Asia/Kolkata');
 $LastUpdatedDate = date('Y-m-d h:i:s'); 

	$oldBmdUpdate = legacyAuditFetchRowById('HrmBioMetricDevices', 'IPAddress', $IPAddress, 'Hrm');
	 $SqlUpdate="UPDATE `HrmBioMetricDevices` SET `LastUpdatedDate`='$LastUpdatedDate' WHERE `IPAddress`='$IPAddress'"; 

	$StmtUpdate=$iconn->query($SqlUpdate);
	if($StmtUpdate){
		legacyAuditLogUpdateById('HrmBioMetricDevices', 'IPAddress', $IPAddress, $oldBmdUpdate, $_POST, ['IPAddress' => $IPAddress, 'Remarks' => 'Biometric device last updated date'], 'Hrm');
	}
	/*IF($StmtUpdate){

ECHO "SUCESS"	;
	
}ELSE{
	
ECHO "FAILED"	;
		
	
}*/

}



?>
