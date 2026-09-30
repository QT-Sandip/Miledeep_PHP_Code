<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$AvalMcs=$_POST['AvalMcs'];
$BuyerInseptionStatus=$_POST['BuyerInseptionStatus'];
$LoadingIndentStatus=$_POST['LoadingIndentStatus'];
$Destination=$_POST['Destination'];
$LabResultStatus=$_POST['LabResultStatus'];
$VehicleStatus=$_POST['VehicleStatus'];
$Remarks=$_POST['Remarks'];
$LinerId=$_POST['LinerId'];

$Type=$_POST['Type'];
 $POID=$_POST['POID']; 
$GradeID=$_POST['GradeID'];

$SqlProduction="SELECT * FROM `$ScrmProductionData` WHERE PIID='$POID'"; 
  
  	 $StmtProduction=$iconn->query($SqlProduction);
	  
$LastUpdatedDate=date('Y-m-d');
	 
IF($Type=='AddProductionData'){
	

      
	 $Count=mysqli_num_rows($StmtProduction);
	 if($Count==0){
		 //if($AvalMcs>=100){
	 $sql="INSERT INTO `$ScrmProductionData`(`PIID`,`GradeID`, `LastUpdatedDate`,  `BuyerInseptionStatus`, 
	 `LoadingIndentStatus`, `Destination`,  `VehicleStatus`, `Remarks`, `LabResultStatus`, `LinerId`) 
	 VALUES ('$POID','$GradeID','$LastUpdatedDate','$BuyerInseptionStatus','$LoadingIndentStatus',
	 '$Destination','$VehicleStatus','$Remarks','$LabResultStatus','$LinerId')"; 
   
   	 $stmt=$iconn->query($sql);
	 if($stmt){
		legacyAuditLogInsertById($ScrmProductionData, 'PIID', $POID, $_POST, [
			'Remarks' => 'Production data created',
		], 'Sales');
	 }
	 
	 //}
	
	 }
	 else{
		$oldProdData = legacyAuditFetchRowById($ScrmProductionData, 'PIID', $POID, 'Sales');
		 
				 //if($AvalMcs>=100){
 
	 $sql="UPDATE `$ScrmProductionData` SET `LastUpdatedDate`='$LastUpdatedDate',`BuyerInseptionStatus`='$BuyerInseptionStatus',`LoadingIndentStatus`='$LoadingIndentStatus',`Destination`='$Destination',
   `VehicleStatus`='$VehicleStatus',`Remarks`='$Remarks',`LabResultStatus`='$LabResultStatus',`LinerId`='$LinerId' WHERE `PIID`='$POID'
 "; 
   
    
   
   	 $stmt=$iconn->query($sql);
	 if($stmt){
		legacyAuditLogUpdateById($ScrmProductionData, 'PIID', $POID, $oldProdData, $_POST, [
			'Remarks' => 'Production data updated',
		], 'Sales');
	 }
	 if($stmt){ legacyAuditLogUpdateById($ScrmProductionData, 'PIID', $POID, $oldProdData, $_POST, ['Remarks' => 'Production data updated'], 'Sales'); }
	/* if($stmt){
		 $SqlLogInsert="INSERT INTO `$ScrmProductionDataLog`(`PIID`,`GradeID`, `LastUpdatedDate`, `AvalMcs`, `BuyerInseptionStatus`, 
	 `LoadingIndentStatus`, `Destination`,  `VehicleStatus`, `Remarks`) 
	 VALUES ('$POID','$GradeID','$LastUpdatedDate','$AvalMcs','$BuyerInseptionStatus','$LoadingIndentStatus',
	 '$Destination','$VehicleStatus','$Remarks','$LabResultStatus')"; 
   
  	 $StmtLogInsert=$iconn->query($SqlLogInsert); 
	 }
		*/		// }	 
	 }
	 if($stmt){
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
