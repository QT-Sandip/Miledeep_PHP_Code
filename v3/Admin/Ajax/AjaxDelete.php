<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 $TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID']; 
$ShippedOutDate=$_POST['ShippedOutDate']; 
$InvId=$_POST['InvId']; 
$Remarks=$_POST['Remarks']; 
$Status=$_POST['Status']; 
$LoginId=$_SESSION["UserID"];


if($TYPE=='App_del'){
 $oldRow = legacyAuditFetchRowById($ScrmApplicants, 'ID', $D_ID, 'Admin');
 $SQL_DEL="DELETE FROM `$ScrmApplicants` WHERE ID='$D_ID'"; 
$stmt=$iconn->query($SQL_DEL);
legacyAuditLogDeleteById($ScrmApplicants, 'ID', $D_ID, $oldRow, [], [], 'Admin');

}

ELSE IF($TYPE=='ArrivalsDelete'){
	
	$LotId=$_POST['LotId'];
	$oldArrivalsRow = legacyAuditFetchRowById('RmLotStatusDetails', 'LotID', $LotId, 'Admin');
	$SqlDeleteArrivals="DELETE FROM `RmLotStatusDetails` WHERE LotID='$LotId'";
	$StmtDeleteArrivals=$iconn->query($SqlDeleteArrivals);

	 if($StmtDeleteArrivals){
		legacyAuditLogDeleteById('RmLotStatusDetails', 'LotID', $LotId, $oldArrivalsRow, [], [], 'Admin');

        $row['sts']='success';
		$row['msg']='Deleted Successfully';
		echo json_encode($row);
	 }
	 
	 	
	 else{
			$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);  
		  }
	
}
ELSE IF($TYPE=='BookingDelete'){
	
	$BookingId=$_POST['BookingId'];
	// $INVOICE_ID=$_POST['INVOICE_ID'];
	$oldBookingRow = legacyAuditFetchRowById('RmLotDetatils', 'RmLotID', $BookingId, 'Admin');

      $SqlDeleteBookings="DELETE FROM `RmLotDetatils` WHERE RmLotID='$BookingId'"; 
	      $StmtDeleteBookings=$iconn->query($SqlDeleteBookings);
	 
	 if($StmtDeleteBookings){
		legacyAuditLogDeleteById('RmLotDetatils', 'RmLotID', $BookingId, $oldBookingRow, [], [], 'Admin');
		$oldStatusRow = legacyAuditFetchRowById('RmLotStatusDetails', 'LotID', $BookingId, 'Admin');
		$SqlDeleteArrivals="DELETE FROM `RmLotStatusDetails` WHERE LotID='$BookingId'";
		$StmtDeleteArrivals=$iconn->query($SqlDeleteArrivals);
		legacyAuditLogDeleteById('RmLotStatusDetails', 'LotID', $BookingId, $oldStatusRow, [], [], 'Admin');

        $row['sts']='success';
		$row['msg']='Deleted Successfully';
		echo json_encode($row);
	 }
	 
	 	
	 else{
			$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);  
		  }
	
}

ELSE IF($TYPE=='BREAKUP_DELETE'){
	
	$BREAKUP_ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['INVOICE_ID'];
	 $oldBreakupRow = legacyAuditFetchRowById('ScrmPoGradeData', 'ID', $BREAKUP_ID, 'Admin');

      $sql="DELETE  FROM `ScrmPoGradeData` WHERE `ID`='$BREAKUP_ID'";
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogDeleteById('ScrmPoGradeData', 'ID', $BREAKUP_ID, $oldBreakupRow, [], [], 'Admin');
	
}

ELSE IF($TYPE=='PI_DELETE'){
	
	$D_ID=$_POST['D_ID'];
	$oldPiData = legacyAuditFetchRowById('ScrmPIData', 'PoID', $D_ID, 'Admin');

      $sql="UPDATE `ScrmPIData` SET `Status`='7' WHERE `PoID`='$D_ID'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPIData', 'PoID', $D_ID, $oldPiData, [], [], 'Admin');
	 
	 if($stmt){
	  //$sqlCI="DELETE FROM `ScrmCustomInvoices` WHERE PiID='$D_ID'"; 
	  $oldCiRow = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $D_ID, 'Admin');
	  $sqlCI="UPDATE `ScrmCustomInvoices` SET `Status`='0' WHERE `PiID`='$D_ID'"; 
    
   	 $stmtCI=$iconn->query($sqlCI);
	 legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $D_ID, $oldCiRow, [], [], 'Admin');
	 
	 $oldPoData = legacyAuditFetchRowById('ScrmPoData', 'PoID', $D_ID, 'Admin');
	 $sqlPO="UPDATE `ScrmPoData` SET `Status`='7' WHERE `PoID`='$D_ID'"; 
    
   	 $stmtPO=$iconn->query($sqlPO);
	 legacyAuditLogUpdateById('ScrmPoData', 'PoID', $D_ID, $oldPoData, [], [], 'Admin');
	 }
}
ELSE IF($TYPE=='McsUpdate'){
	
	$D_ID=$_POST['D_ID'];
		 $Mcs=$_POST['Mcs'];
	$oldMcsRow = legacyAuditFetchRowById('ScrmCIGradeData', 'ID', $D_ID, 'Admin');

      $sql="UPDATE `ScrmCIGradeData` SET `Mcs`='$Mcs' WHERE ID='$D_ID'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmCIGradeData', 'ID', $D_ID, $oldMcsRow, [], [], 'Admin');
	
}
ELSE IF($TYPE=='PO_DELETE'){
	
	$D_ID=$_POST['D_ID'];
	$oldPoDeleteRow = legacyAuditFetchRowById('ScrmPoData', 'PoID', $D_ID, 'Admin');

      $sql="DELETE FROM `ScrmPoData` WHERE PoID='$D_ID'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogDeleteById('ScrmPoData', 'PoID', $D_ID, $oldPoDeleteRow, [], [], 'Admin');
	
}
ELSE IF($TYPE=='CustomDelete'){
	
	$PiID=$_POST['PiID'];
	$oldCustomInvRow = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $PiID, 'Admin');

       $sqlCI="DELETE FROM `ScrmCustomInvoices` WHERE PiID='$PiID'"; 
    
   	 $stmtCI=$iconn->query($sqlCI);
	 legacyAuditLogDeleteById('ScrmCustomInvoices', 'PiID', $PiID, $oldCustomInvRow, [], [], 'Admin');
	 
	 	 if($stmtCI){
	 $oldCIGradeRow = legacyAuditFetchRowById('ScrmCIGradeData', 'InvId', $PiID, 'Admin');

 $sqlCI="DELETE FROM `ScrmCIGradeData` WHERE InvId='$PiID'"; 
    
   	 $stmtCI=$iconn->query($sqlCI);
	 legacyAuditLogDeleteById('ScrmCIGradeData', 'InvId', $PiID, $oldCIGradeRow, [], [], 'Admin');
	 
			 }

	 
}

ELSE IF($TYPE=='PO_CANCEL'){
	
	$D_ID=$_POST['D_ID'];
	$oldPoCancelRow = legacyAuditFetchRowById('ScrmPoData', 'PoID', $D_ID, 'Admin');

      $sql="UPDATE `ScrmPoData` SET `Status`='2' WHERE PoID='$D_ID'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPoData', 'PoID', $D_ID, $oldPoCancelRow, [], [], 'Admin');
	
}
ELSE IF($TYPE=='PI_CANCEL'){
	
	$D_ID=$_POST['D_ID'];
	$oldPiCancelRow = legacyAuditFetchRowById('ScrmPIData', 'PoID', $D_ID, 'Admin');

      $sql="UPDATE `ScrmPIData` SET `Status`='2' WHERE PoID='$D_ID'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPIData', 'PoID', $D_ID, $oldPiCancelRow, [], [], 'Admin');
	
}

ELSE IF($TYPE=='SHIPPED_PO'){
	
	$D_ID=$_POST['D_ID'];
	$oldShippedPoRow = legacyAuditFetchRowById('ScrmPoData', 'PoID', $InvId, 'Admin');

      $sql="UPDATE `ScrmPoData` SET `ShippedOutDate`='$ShippedOutDate',`Status`='3' WHERE PoID='$InvId'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPoData', 'PoID', $InvId, $oldShippedPoRow, [], [], 'Admin');
	 
	 if($stmt){
		 $oldShippedPiRow = legacyAuditFetchRowById('ScrmPIData', 'PoID', $InvId, 'Admin');
		 $SqlPI="UPDATE `ScrmPIData` SET `ShippedOutDate`='$ShippedOutDate',`Status`='3' WHERE PoID='$InvId'"; 
    
   	 $StmtPI=$iconn->query($SqlPI);
		 legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldShippedPiRow, [], [], 'Admin');
		 $row['sts']='success';
		$row['msg']='PO Shipped  Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed To Shipped PO';
		 echo json_encode($row);
	 }
	
}
ELSE IF($TYPE=='UPDATEETA'){
	echo 'hii';
	$PiID=$_POST['PiID'];
		 $FbdEta=$_POST['ETA'];
		 $FbdEtaRemarks=$_POST['FbdEtaRemarks'];
	$oldLogisticsRow = legacyAuditFetchRowById('ScrmLogisticsDetails', 'PiID', $PiID, 'Admin');

      $sql="UPDATE `ScrmLogisticsDetails` SET `FbdEta`='$FbdEta',`FbdEtaRemarks`='$FbdEtaRemarks'   WHERE  `PiID`='$PiID'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmLogisticsDetails', 'PiID', $PiID, $oldLogisticsRow, [], [], 'Admin');
	 
	/* if($stmt){
		 $row['sts']='success';
		$row['msg']=' Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
	*/
}

ELSE IF($TYPE=='PI_STATUS'){
	
	
	
	$D_ID=$_POST['D_ID'];
	 $PoNo=$_POST['PoNo']; 
     $PoDate=$_POST['PoDate']; 

 $SqlPI="SELECT * FROM `ScrmPIData` WHERE PoID='$InvId' ";
	$StmtPI=$iconn->query($SqlPI);
	$RowPI=$StmtPI->fetch_array();
	$PoRefId=$RowPI['PoRefId'];
	$InvNo=$RowPI['InvNo'];
	$InvDate=$RowPI['InvDate'];
	$ExporterID=$RowPI['ExporterID'];
	$ProcessorID=$RowPI['ProcessorID'];
	$LotNo=$RowPI['LotNo'];
	$SciName=$RowPI['SciName'];
	$ConsigneeID=$RowPI['ConsigneeID'];
	$BuyerID=$RowPI['BuyerID'];
	/*$PoNo=$RowPI['PoNo'];
	$PoDate=$RowPI['PoDate'];*/
	$DestinationCountry=$RowPI['DestinationCountry'];
	$TypeCountry=$RowPI['TypeCountry'];
	$PoStatus=$RowPI['PoStatus'];
	$YEAR=$RowPI['Year'];
	$BankID=$RowPI['BankID'];
	$PortOfDischarge=$RowPI['PortOfDischarge'];
	$FinalDestination=$RowPI['FinalDestination'];
	$PaymentTerms=$RowPI['PaymentTerms'];
	$ShippingTerms=$RowPI['ShippingTerms'];
	$DeliveryTerms=$RowPI['DeliveryTerms'];
	$PreparedBy=$RowPI['PreparedBy'];
	$PRInstrctns=$RowPI['PRInstrctns'];
	$ShipByDate=$RowPI['ShipByDate'];
	




	 $sqlpo=" SELECT * FROM `ScrmPoData`   WHERE PoID='$InvId'";
	$stmtPO=$iconn->query($sqlpo);

	$sqlShipment=" SELECT * FROM `ScrmShipmentDetailsBreif`   WHERE PiID='$InvId'";
	$stmtShipment=$iconn->query($sqlShipment);
	$Shipmentcount=mysqli_num_rows($stmtShipment);
	$count=mysqli_num_rows($stmtPO);
		//else  if($Status==5||$Status=='1'){
		  if($PoNo!=''&&$PoDate!=''){

		 
		
		 
			 

			 // if($PoNo!=''&&$PoDate!=''){
				  
			  
		 $oldPiStatusRow = legacyAuditFetchRowById('ScrmPIData', 'PoID', $InvId, 'Admin');
		 $sql="UPDATE `ScrmPIData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Remarks`='$Remarks',`Status`='5',`ApprovedBy`='$LoginId' WHERE PoID='$InvId'"; 
    
   	 $stmt1=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiStatusRow, [], [], 'Admin');
	if($Shipmentcount==0){
	$SqlShipmentInsert="INSERT INTO `ScrmShipmentDetailsBreif`(`PiID`,`AvailableDate`) VALUES('$InvId','$PoDate')";
        $StmtShipmentInsert=$iconn->query($SqlShipmentInsert);
		legacyAuditLogInsertById('ScrmShipmentDetailsBreif', 'ID', $iconn->insert_id, [], [], 'Admin');
	} 
	else{
		$oldShipmentBriefRow = legacyAuditFetchRowById('ScrmShipmentDetailsBreif', 'PiID', $InvId, 'Admin');
		$SqlShipmentInsert="UPDATE `ScrmShipmentDetailsBreif` SET `AvailableDate`='$PoDate' WHERE `PiID`='$InvId'";
        $StmtShipmentInsert=$iconn->query($SqlShipmentInsert);
		legacyAuditLogUpdateById('ScrmShipmentDetailsBreif', 'PiID', $InvId, $oldShipmentBriefRow, [], [], 'Admin');
	}	
	
	 if($count==0){
	 $SqlInsert=" INSERT INTO `ScrmPoData`(`PoID`, `PoRefId`, `InvNo`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
	  `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`, `BankID`
	  , `PortOfDischarge`, `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, `PreparedBy`,
	  `PRInstrctns`, `ShipByDate`,  `Status`, `LoginId`, `ApprovedBy`, `Remarks`) 
	  VALUES ('$InvId','$PoRefId','$InvNo','$InvDate','$ExporterID','$ProcessorID',
'$LotNo','$SciName','$ConsigneeID','$BuyerID','$PoNo','$PoDate','$DestinationCountry',
'$TypeCountry','$PoStatus','$YEAR','$BankID','$PortOfDischarge',
'$FinalDestination','$PaymentTerms','$ShippingTerms','$DeliveryTerms','$PreparedBy' ,'$PRInstrctns',
	 '$ShipByDate','5','$LoginId','$LoginId','$Remarks')";
	 	 $stmt2=$iconn->query($SqlInsert);
		 legacyAuditLogInsertById('ScrmPoData', 'PoID', $InvId, [], [], 'Admin');
}
	 else{
	$oldPoStatusRow = legacyAuditFetchRowById('ScrmPoData', 'PoID', $InvId, 'Admin');
	$SqlInsert="UPDATE `ScrmPoData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Remarks`='$Remarks',`Status`='5',`ApprovedBy`='$LoginId' WHERE PoID='$InvId'"; 
	 $stmt2=$iconn->query($SqlInsert);
	 legacyAuditLogUpdateById('ScrmPoData', 'PoID', $InvId, $oldPoStatusRow, [], [], 'Admin');

	 }
	 
			 
	 
	if($stmt2){
	$row['sts']='success';
		$row['msg']='PI Status Updated  Sucessfully';
		echo json_encode($row);
	 }
			
	 else{
			$row['sts']='Failed';
		$row['msg']='Update failed please enter po number and po date';
		 echo json_encode($row);  
		  }
	 
	 //}
		 
		  
	// }
		 
	 }
else	if($Status!=''&&$Status!='1'){
	$oldPiStRow = legacyAuditFetchRowById('ScrmPIData', 'PoID', $InvId, 'Admin');
      $sql="UPDATE `ScrmPIData` SET `Remarks`='$Remarks',`Status`='$Status' WHERE PoID='$InvId'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiStRow, [], [], 'Admin');
}
else if($Status==''){
	 $oldPiRemRow = legacyAuditFetchRowById('ScrmPIData', 'PoID', $InvId, 'Admin');
	 $sql="UPDATE `ScrmPIData` SET `Remarks`='$Remarks' WHERE PoID='$InvId'"; 
    
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiRemRow, [], [], 'Admin');
}else if($Status=='1'){
	 $oldPiSt1Row = legacyAuditFetchRowById('ScrmPIData', 'PoID', $InvId, 'Admin');
	 //$sql="UPDATE `ScrmPIData` SET `Remarks`='$Remarks' WHERE PoID='$InvId'"; 
  $sql="UPDATE `ScrmPIData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Remarks`='$Remarks',`Status`='$Status',`ApprovedBy`='$LoginId' WHERE PoID='$InvId'"; 
   	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiSt1Row, [], [], 'Admin');


}
if($stmt){
if($count==0){
	 $SqlInsert=" INSERT INTO `ScrmPoData`(`PoID`, `PoRefId`, `InvNo`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
	  `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`, `BankID`
	  , `PortOfDischarge`, `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, `PreparedBy`,
	  `PRInstrctns`, `ShipByDate`,  `Status`, `LoginId`, `ApprovedBy`, `Remarks`) 
	  VALUES ('$InvId','$PoRefId','$InvNo','$InvDate','$ExporterID','$ProcessorID',
'$LotNo','$SciName','$ConsigneeID','$BuyerID','$PoNo','$PoDate','$DestinationCountry',
'$TypeCountry','$PoStatus','$YEAR','$BankID','$PortOfDischarge',
'$FinalDestination','$PaymentTerms','$ShippingTerms','$DeliveryTerms','$PreparedBy' ,'$PRInstrctns',
	 '$ShipByDate','5','$LoginId','$LoginId','$Remarks')";
	 	 $stmt2=$iconn->query($SqlInsert);
		 legacyAuditLogInsertById('ScrmPoData', 'PoID', $InvId, [], [], 'Admin');
}
	 else{
	$oldPoStRow = legacyAuditFetchRowById('ScrmPoData', 'PoID', $InvId, 'Admin');
	$SqlInsert="UPDATE `ScrmPoData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Remarks`='$Remarks',`Status`='$Status',`ApprovedBy`='$LoginId' WHERE PoID='$InvId'"; 
	 $stmt2=$iconn->query($SqlInsert);
	 legacyAuditLogUpdateById('ScrmPoData', 'PoID', $InvId, $oldPoStRow, [], [], 'Admin');

	 }
		 $row['sts']='success';
		$row['msg']='PI Status Updated  Sucessfully';
	 echo json_encode($row);}
}

 ?>
