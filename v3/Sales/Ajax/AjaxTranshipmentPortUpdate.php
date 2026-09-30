<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$Type=$_POST['Type'];





 IF($Type=='TranshipmentPortInsert'){
$PIID=$_POST['InvId']; 
$FromTOPID=$_POST['FromTOPID']; 
$ToTOPID=$_POST['ToTOPID']; 
$FromDate=$_POST['FromDate']; 
$TranshipDt=$_POST['TranshipDt']; 
$VesselName=$_POST['VesselName'];
$VOYNO=$_POST["VOYNO"];

IF($VesselName==''){
	
	 $row['sts']='Please enter data in the fields';
	 $row['msg']=' Please enter data in the fields';
	 echo json_encode($row);
}else{
	
$sql="INSERT INTO `ScrmTranshipmentPortsList` 
(`PIID`, `FromTOPID`, `ToTOPID`, `FromDate`, `TranshipDt`, `VesselName`, 
`VOYNO`, `Status`) VALUES ('$PIID','$FromTOPID','$ToTOPID','$FromDate','$TranshipDt','$VesselName','$VOYNO','1')"; 
   
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogInsertById('ScrmTranshipmentPortsList', 'PIID', $PIID, $_POST, [
	 	'Remarks' => 'Transhipment port created',
	 ], 'Sales');
 	 
 	 if($stmt){
		
		 $row['sts']='success';
		$row['msg']=' Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Shipped PO';
		 echo json_encode($row);
	 }
	 
}
	
}

else IF($Type=='UpdateTranshipmentPorts'){

	

$TranshipmentID=$_POST['TranshipmentID']; 
$DeparturePort=$_POST['DeparturePort']; 
$ArrivalPort=$_POST['ArrivalPort']; 
$DepartureDate=$_POST['DepartureDate']; 
$VesselName=$_POST['VesselName']; 
$VOYNO=$_POST['VOYNO'];
$ArrivalDate=$_POST["ArrivalDate"];

/*IF($VesselName==''){
	
	 $row['sts']='Please enter Data in the feilds';
	 $row['msg']=' Please enter Data in the feilds';
	 echo json_encode($row);
}else{*/
	
$oldTransData = legacyAuditFetchRowById('ScrmTranshipmentPortsList', 'TspID', $TranshipmentID, 'Sales');
$sql="UPDATE `ScrmTranshipmentPortsList` SET `FromTOPID`='$DeparturePort',`ToTOPID`='$ArrivalPort',
`FromDate`='$DepartureDate',`TranshipDt`='$ArrivalDate',`VesselName`='$VesselName',`VOYNO`='$VOYNO' WHERE `TspID`='$TranshipmentID'"; 
   
  	 $stmt=$iconn->query($sql);
	 
	 if($stmt){
	 	legacyAuditLogUpdateById('ScrmTranshipmentPortsList', 'TspID', $TranshipmentID, $oldTransData, $_POST, [
	 		'Remarks' => 'Transhipment port updated',
	 	], 'Sales');
	 }
 	 
 	 if($stmt){
		
		 $row['sts']='success';
		$row['msg']=' Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Shipped PO';
		 echo json_encode($row);
	 }
	 
}
	


 else IF($Type=='TranshipmentPortDelete'){
	
	$TspID=$_POST["TspID"];

       $sql="delete from `ScrmTranshipmentPortsList` WHERE `TspID`='$TspID'"; 
   
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogDeleteById('ScrmTranshipmentPortsList', 'TspID', $TspID, $oldDelTransData, [], ['Remarks' => 'Transhipment port deleted'], 'Sales');
 	 
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
