<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


 //$StockID=$_POST['StockID'];  
 $ToBlockID=$_POST['ToBlockID'];  
 $FromRackID=$_POST['FromRackID'];  
$ToRackID=$_POST['ToRackID'];
$Type=$_POST['Type'];
$BarCodeID=$_POST['StockID'];
 if($FromRackID==''){
	$SqlRack="SELECT * FROM `CsStoresRackData` WHERE BarCodeID ='$BarCodeID'";
   $StmtRack=$iconn->query($SqlRack);
   $Count=mysqli_num_rows($StmtRack);
   $RowRack=$StmtRack->fetch_array(); 
   $FromRackID=$RowRack['RowID'];
 }
   $SqlRack="SELECT * FROM `CsStoresRackData` WHERE BarCodeID ='$BarCodeID' and RowID!='$FromRackID'";
   $StmtRack=$iconn->query($SqlRack);
   $Count=mysqli_num_rows($StmtRack);
   $RowRack=$StmtRack->fetch_array();		

if($Type=='ShuffleRack'){
  
   if($Count=='0'){

// updte forwd rck
	 $oldFromRackData = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $FromRackID, 'ColdStore');
         $SqlFromrack = "UPDATE `CsStoresRackData` SET `BarCodeID`='0',`Status`='0' WHERE `RowID`='$FromRackID' and
		 `BarCodeID`='$BarCodeID' ";			
      
		 $StmtFromrack=$iconn->query($SqlFromrack);
		 if($StmtFromrack){
			 legacyAuditLogUpdateById('CsStoresRackData', 'RowID', $FromRackID, $oldFromRackData, $_POST, ['Remarks' => 'Source rack cleared - ShuffleRack'], 'ColdStore');
		 }

         if($StmtFromrack){
        //$row_id=$row['ROW_ID'];
//updte to rck		
		$oldToRackData = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $ToRackID, 'ColdStore');
		$SqlTorack = "UPDATE `CsStoresRackData` SET `BarCodeID`='$BarCodeID',`Status`='1' 
		WHERE `RowID`='$ToRackID'  and BlockID='$ToBlockID'";
		//echo $sql;
		$StmtToRack=$iconn->query($SqlTorack);
		if($StmtToRack){
			legacyAuditLogUpdateById('CsStoresRackData', 'RowID', $ToRackID, $oldToRackData, $_POST, ['Remarks' => 'Destination rack assigned - ShuffleRack'], 'ColdStore');
		}
   }
		if($StmtToRack){
		 $row['sts']='success';
		$row['msg']=' Sucess';
		echo json_encode($row);
	 }
	 
	 else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }

		}
		
		
		else{

			
		/*$SqlUpdate = "UPDATE `CsBarCodes` SET `BarCodeStatus`='2' WHERE `BarCodeID`='$BarCodeID'";
		$iconn->query($SqlUpdate);*/
		
			 $row['sts']='success';
			$row['msg']='This Bar Code occupied another Rack Number '.$RowRack['RackName'];
			echo json_encode($row);

		}

}
  /* $RackBarCodeID=$RowRack['BarCodeID'];
   
	
	
	$SqlStock="SELECT * FROM `CsStockData` WHERE StockID='$StockID'";
	$StmtStock=$iconn->query($SqlStock);  
	$RowStock=$StmtStock->fetch_array();
	$StockBarCodeID=$RowStock['BarCodeID']; 
	
	if($RackBarCodeID==0){
		 $SqlUpdate="UPDATE `CsStoresRackData` SET `BarCodeID`='$StockBarCodeID' ,Status=1 WHERE `RowID`='$RowID'";
		$Stmtupdate=$iconn->query($SqlUpdate);
		if($Stmtupdate){
		$SqlFloorUpdate="UPDATE `CsBarCodes` SET `OnFloor`='1' WHERE `BarCodeID`='$StockBarCodeID'";
		$StmtFloorUpdate=$iconn->query($SqlFloorUpdate);
		}

}else{
	$SqlUpdate="UPDATE `CsStockData` SET `BarCodeID`='$RackBarCodeID' WHERE `StockID`='$StockID'";
		$Stmtupdate=$iconn->query($SqlUpdate);
}
*/

?>
