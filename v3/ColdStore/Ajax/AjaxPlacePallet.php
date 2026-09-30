<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

		$RowID = $_POST['rck_id'];
		$BarCodeID = $_POST['bid'];
		$sql = "SELECT * FROM `CsStoresRackData` WHERE `BarCodeID`='$BarCodeID'";
		$query_stmt = $iconn->query($sql);		
		$count=$query_stmt->num_rows;
		
        if($count=='0'){	
		$oldRackData = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $RowID, 'ColdStore');
		$SqlStoreData = "UPDATE `CsStoresRackData` SET `BarCodeID`='$BarCodeID',`Status`='1' WHERE `RowID`='$RowID'";		
		
		if($iconn->query($SqlStoreData)){
			legacyAuditLogUpdateById('CsStoresRackData', 'RowID', $RowID, $oldRackData, $_POST, ['Remarks' => 'Place pallet on rack'], 'ColdStore');
			$Data['Error'] = null;			
			$Data['Msg'] = 'Data Updated';			
			$oldBarCodeData = legacyAuditFetchRowById('CsBarCodes', 'BarCodeID', $BarCodeID, 'ColdStore');
			$sql = "UPDATE `CsBarCodes` SET `OnFloor`='1' WHERE `BarCodeID`='$BarCodeID'";
		    $iconn->query($sql);
			legacyAuditLogUpdateById('CsBarCodes', 'BarCodeID', $BarCodeID, $oldBarCodeData, $_POST, ['Remarks' => 'Set OnFloor=1 - Place pallet'], 'ColdStore');
		}else{
			$Data['Error'] = 'Data Upload Failed';			
			$Data['Msg'] = 'Data Upload Failed';	
			}
			
		}else{
		
		$row=$query_stmt->fetch_array();		
		/*		
		$sql_upd = "UPDATE `CS_BAR_CODE_DATA` SET `BAR_CODE_STATUS`='2' WHERE `BAR_CODE_ID`='$bid'";
		$iconn->query($sql_upd);
		
		$sql = "UPDATE `CS_BAR_CODE_DATA` SET `ON_FLOOR`='1' WHERE `BAR_CODE_ID`='$bid'";
		$iconn->query($sql);
		
		*/
		
		
			$Data['Error'] = 'the Bar Code is filled with another Rack Number '.$row['RackName'];			
			$Data['Msg'] = 'Data Upload Failed';
		}
		
		echo json_encode($Data);





 ?>
