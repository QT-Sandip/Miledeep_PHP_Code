<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $Type=$_POST['Type']; 

if($Type=='CurrentMcsUpdate'){
	 
	 $ScrmPGDID=$_POST['GradeId']; 
	 $CurrentMcs=$_POST['CurrentMcs'];
	
	 $oldGradeRow = legacyAuditFetchRowById('ScrmPoGradeData', 'ID', $ScrmPGDID, 'Admin');
     $sql="UPDATE `ScrmPoGradeData` SET 
	 `CurrentMcs`='$CurrentMcs'
	 
	 WHERE `ID`='$ScrmPGDID'";
	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $ScrmPGDID, $oldGradeRow, [], [], 'Admin');
	
	if($stmt){
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
