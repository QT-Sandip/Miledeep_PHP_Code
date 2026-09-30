<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Amount=$_POST['Amount'];

$Type=$_POST['Type'];
$EmpBioID=$_POST['EmpBioID'];
$Month=$_POST['Month'];
$Year=$_POST['Year'];

$SqlEmployee="SELECT * FROM `HrmEmployeeDetails` WHERE BioMetricID='$EmpBioID'"; 
  
  	 $StmtEmployee=$iconn->query($SqlEmployee);
	 $RowEmployee=$StmtEmployee->fetch_array();
	 	 $EmpID=$RowEmployee['EmpID']; 

$SqlCount="SELECT MDID FROM `EmpMessDeductionDetails` WHERE BioID='$EmpBioID' and Month='$Month' and Year='$Year'"; 
  
  	 $StmtCount=$iconn->query($SqlCount);
	 $RowCount=$StmtCount->fetch_array();
	 $MDID=$RowCount['MDID']; 
	 
IF($Type=='AddMessDeduction'){
	

      
	 $Count=mysqli_num_rows($StmtCount); 
	 if($Count==0){
	 $sql="INSERT INTO `EmpMessDeductionDetails`(`EmpID`, `BioID`, `Amount`, `Month`, `Year`) 
	 VALUES ('$EmpID','$EmpBioID','$Amount','$Month','$Year')"; 
   
  	 $stmt=$iconn->query($sql);
	 
	 }else{
	 $oldMessDed = legacyAuditFetchRowById('EmpMessDeductionDetails', 'MDID', $MDID, 'Hrm');
		 
		 
	 $sql="UPDATE `EmpMessDeductionDetails` SET `Amount`='$Amount' WHERE `MDID`='$MDID'"; 
   
  	 $stmt=$iconn->query($sql);
		 
	 }
	 if($stmt){
		if($Count==0){
			legacyAuditLogInsertById('EmpMessDeductionDetails', 'MDID', $iconn->insert_id, $_POST, ['EmpBioID' => $EmpBioID, 'Remarks' => 'Mess deduction added'], 'Hrm');
		}else{
			legacyAuditLogUpdateById('EmpMessDeductionDetails', 'MDID', $MDID, $oldMessDed, $_POST, ['EmpBioID' => $EmpBioID, 'Remarks' => 'Mess deduction updated'], 'Hrm');
		}
		 $row['sts']='success';
		$row['msg']=' Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
	 
	
}
?>
