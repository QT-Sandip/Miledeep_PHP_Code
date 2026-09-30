<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$AvalMcs=$_POST['AvalMcs'];
$BuyerInseptionStatus=$_POST['BuyerInseptionStatus'];
$LoadingIndentStatus=$_POST['LoadingIndentStatus'];
$Destination=$_POST['Destination'];
$BookedContainers=$_POST['BookedContainers'];
$VehicleStatus=$_POST['VehicleStatus'];
$Remarks=$_POST['Remarks'];

$Type=$_POST['Type'];
$POID=$_POST['POID'];

$SqlProduction="SELECT * FROM `$ScrmProductionData` WHERE PIID='$POID'"; 
  
  	 $StmtProduction=$iconn->query($SqlProduction);
	  
$LastUpdatedDate=date('Y-m-d');
	 
IF($Type=='AddProductionData'){
	

      
	 $Count=mysqli_num_rows($StmtProduction);
	 if($Count==0){
		 if($AvalMcs>=100){
	 $sql="INSERT INTO `$ScrmProductionData`(`PIID`, `LastUpdatedDate`, `AvalMcs`, `BuyerInseptionStatus`, 
	 `LoadingIndentStatus`, `Destination`, `BookedContainers`, `VehicleStatus`, `Remarks`) 
	 VALUES ('$POID','$LastUpdatedDate','$AvalMcs','$BuyerInseptionStatus','$LoadingIndentStatus',
	 '$Destination','$BookedContainers','$VehicleStatus','$Remarks')"; 
   
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogInsertById($ScrmProductionData, 'ID', $iconn->insert_id, [], [], 'Admin');
	 
	 }
	
	 }
	 else{
	 	 
		 
				 if($AvalMcs>=100){
 
	 $oldProdRow = legacyAuditFetchRowById($ScrmProductionData, 'PIID', $POID, 'Admin');
	 $sql="UPDATE `$ScrmProductionData` SET `LastUpdatedDate`='$LastUpdatedDate',`AvalMcs`='$AvalMcs',`BuyerInseptionStatus`='$BuyerInseptionStatus',`LoadingIndentStatus`='$LoadingIndentStatus',`Destination`='$Destination',
   `BookedContainers`='$BookedContainers',`VehicleStatus`='$VehicleStatus',`Remarks`='$Remarks' WHERE `PIID`='$POID'
 "; 
   
    
   
  	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById($ScrmProductionData, 'PIID', $POID, $oldProdRow, [], [], 'Admin');
	 if($stmt){
		 $SqlLogInsert="INSERT INTO `$ScrmProductionDataLog`(`PIID`, `LastUpdatedDate`, `Mcs`, `BuyerInseptionStatus`, 
	 `LoadingIndentStatus`, `Destination`, `BookedContainers`, `VehicleStatus`, `Remarks`) 
	 VALUES ('$POID','$LastUpdatedDate','$AvalMcs','$BuyerInseptionStatus','$LoadingIndentStatus',
	 '$Destination','$BookedContainers','$VehicleStatus','$Remarks')"; 
   
  	 $StmtLogInsert=$iconn->query($SqlLogInsert); 
	 }
				 }	 
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
