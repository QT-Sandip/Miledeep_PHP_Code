<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$AvalMcs=$_POST['AvalMcs'];
$Type=$_POST['Type'];
$GradeID=$_POST['GradeID'];
$PoID=$_POST['PoID'];

$SqlProduction="SELECT * FROM `ScrmProductionDataAvalMcs` WHERE GradeID='$GradeID'"; 
$StmtProduction=$iconn->query($SqlProduction);
	  
$LastUpdatedDate=date('Y-m-d');
	 
 IF($Type=='UpdateMcs'){
	
	
      
	 $Count=mysqli_num_rows($StmtProduction);
	 if($Count==0){
		 //if($AvalMcs>=100){
	 $sql="INSERT INTO `ScrmProductionDataAvalMcs`(`GradeID`,`PoID`,`AvalMcs`,`LastUpdatedDate`) 
	 VALUES ('$GradeID','$PoID','$AvalMcs','$LastUpdatedDate')"; 
   
  	 $stmt=$iconn->query($sql);
	 if($stmt){ legacyAuditLogInsertById('ScrmProductionDataAvalMcs', 'GradeID', $GradeID, $_POST, ['Remarks' => 'Available MCS data created'], 'Sales'); }
	 
	 //}
	
	 }
	 else{
 
	 $oldAvalMcs = legacyAuditFetchRowById('ScrmProductionDataAvalMcs', 'GradeID', $GradeID, 'Sales');
	 $sql="UPDATE `ScrmProductionDataAvalMcs` SET `AvalMcs`='$AvalMcs',`LastUpdatedDate`='$LastUpdatedDate'
	 WHERE `GradeID`='$GradeID'
 "; 
   
    
   
   	 $stmt=$iconn->query($sql);
	 if($stmt){
		legacyAuditLogUpdateById('ScrmProductionDataAvalMcs', 'GradeID', $GradeID, $oldAvalMcs, $_POST, [
			'Remarks' => 'Available MCS data updated',
		], 'Sales');
	 }
	 /*if($stmt){
		 $SqlLogInsert="INSERT INTO `$ScrmProductionDataLog`(`PIID`,`GradeID`, `LastUpdatedDate`, `AvalMcs`, `BuyerInseptionStatus`, 
	 `LoadingIndentStatus`, `Destination`, `BookedContainers`, `VehicleStatus`, `Remarks`) 
	 VALUES ('$POID','$GradeID','$LastUpdatedDate','$AvalMcs','$BuyerInseptionStatus','$LoadingIndentStatus',
	 '$Destination','$BookedContainers','$VehicleStatus','$Remarks')"; 
   
  	 $StmtLogInsert=$iconn->query($SqlLogInsert); 
	 }*/
				 
	 }
	 if($stmt){
		 $row['sts']='success';
		$row['msg']='Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
}

?>
