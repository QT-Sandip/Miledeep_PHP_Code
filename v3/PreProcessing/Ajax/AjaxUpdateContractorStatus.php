<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$ContractorID=$_POST['ContractorID'];
$Status=$_POST['Status']; 
$Type=$_POST['Type']; 


 IF($Type=='ContractorStatus'){
	
	if($Status==1){
		$Status=0;
	}
else{
	$Status=1;
}
      $oldContractorData = legacyAuditFetchRowById('PpcContractMaster', 'ContractorID', $ContractorID, 'PreProcessing');
      $sql="UPDATE `PpcContractMaster` SET `Status`='$Status' WHERE `ContractorID`='$ContractorID'"; 
   
  	 $stmt=$iconn->query($sql);
	 
	 if($stmt){
		legacyAuditLogUpdateById('PpcContractMaster', 'ContractorID', $ContractorID, $oldContractorData, $_POST, [
			'ReferenceNo' => $ContractorID,
			'Remarks' => 'Contractor status updated',
		], 'PreProcessing');
		
		 $row['sts']='success';
		$row['msg']='Status UpdatedSucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
	
}


 ?>
