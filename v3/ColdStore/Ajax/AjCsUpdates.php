<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $Type=$_POST['Type']; 

if($Type=='CurrentMcsUpdate'){
	 
	 $ScrmPGDID=$_POST['GradeId']; 
	 $CurrentMcs=$_POST['CurrentMcs'];
	
	 $oldGradeData = legacyAuditFetchRowById('ScrmPoGradeData', 'ID', $ScrmPGDID, 'ColdStore');
     $sql="UPDATE `ScrmPoGradeData` SET 
	 `CurrentMcs`='$CurrentMcs'
	 
	 WHERE `ID`='$ScrmPGDID'";
	 $stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $ScrmPGDID, $oldGradeData, $_POST, ['Remarks' => 'Current MCS updated'], 'ColdStore');
		$error = null;
					$Msg = 'Updated Successfully';
				}else{
					$error =  $iconn->error;
				}	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
}

?>
