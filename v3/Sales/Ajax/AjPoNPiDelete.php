<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 //$TYPE=$_POST['TYPE'];
//echo $TYPE=$_GET['TYPE'];
//echo $DeleteID=$_GET['D_ID']; 
 $DeleteID=$_POST['D_ID']; 
//$InvId=$_POST['InvId']; 
//$InvId=$_POST['D_ID']; 

//uma
$SqlPoStatus="SELECT Status FROM `ScrmPIData` WHERE PoID='$DeleteID'";
$StmtPoStatus=$iconn->query($SqlPoStatus);
$RowPoStatus=$StmtPoStatus->fetch_array();
$PoStatus=$RowPoStatus['Status'];
if($PoStatus==0){
	$TYPE='PIDeleteBeforeApproval';
}
//uma
 IF($TYPE=='PIDeleteBeforeApproval'){
	
	$DeleteID=$_POST['D_ID'];
	  //$DeleteID=$_GET['D_ID']; 
	  $oldPiDelete = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$DeleteID'")->fetch_assoc();
	  $SqlPIDelete="DELETE FROM `ScrmPIData` WHERE PoID='$DeleteID'";
  	 $StmtPIDelete=$iconn->query($SqlPIDelete);


	 if($StmtPIDelete){
		legacyAuditLogDeleteById('ScrmPIData', 'PoID', $DeleteID, $oldPiDelete, $_POST, [
			'ReferenceNo' => $DeleteID,
			'Remarks' => 'PI deleted before approval',
		], 'Exports');

   $oldPoGradeDelete = $iconn->query("SELECT * FROM `ScrmPoGradeData` WHERE `InvId`='$DeleteID'")->fetch_assoc();
   $SqlBreakUpDelete="DELETE  FROM `ScrmPoGradeData` WHERE `InvId`='$DeleteID'";
   	 $StmtBreakUpDelete=$iconn->query($SqlBreakUpDelete);
	if($StmtBreakUpDelete){
		legacyAuditLogDeleteById('ScrmPoGradeData', 'InvId', $DeleteID, $oldPoGradeDelete, $_POST, [
			'ReferenceNo' => $DeleteID,
			'Remarks' => 'PI grade data deleted before approval',
		], 'Exports');
	}

	 }

}

ELSE {


	$oldPiDelete2 = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$DeleteID'")->fetch_assoc();
	$SqlPIDelete="DELETE FROM `ScrmPIData` WHERE PoID='$DeleteID'";
  	 $StmtPIDelete=$iconn->query($SqlPIDelete);
	 if($StmtPIDelete){
		legacyAuditLogDeleteById('ScrmPIData', 'PoID', $DeleteID, $oldPiDelete2, $_POST, [
			'ReferenceNo' => $DeleteID,
			'Remarks' => 'PI deleted',
		], 'Exports');

	 $oldPoDelete = $iconn->query("SELECT * FROM `ScrmPoData` WHERE PoID='$DeleteID'")->fetch_assoc();
	 $SqlPoDelete="DELETE FROM `ScrmPoData` WHERE PoID='$DeleteID'";
   	 $StmtPoDelete=$iconn->query($SqlPoDelete);
	if($StmtPoDelete){
		legacyAuditLogDeleteById('ScrmPoData', 'PoID', $DeleteID, $oldPoDelete, $_POST, [
			'ReferenceNo' => $DeleteID,
			'Remarks' => 'PO data deleted with PI',
		], 'Exports');
	}

	 $oldPoGradeDelete2 = $iconn->query("SELECT * FROM `ScrmPoGradeData` WHERE `InvId`='$DeleteID'")->fetch_assoc();
     $SqlBreakUpDelete="DELETE  FROM `ScrmPoGradeData` WHERE `InvId`='$DeleteID'";
   	 $StmtBreakUpDelete=$iconn->query($SqlBreakUpDelete);
	 if($StmtBreakUpDelete){
		legacyAuditLogDeleteById('ScrmPoGradeData', 'InvId', $DeleteID, $oldPoGradeDelete2, $_POST, [
			'ReferenceNo' => $DeleteID,
			'Remarks' => 'PO grade data deleted with PI',
		], 'Exports');
	 }

	 $SqlSHpmtDtlsDelete="DELETE  FROM `ScrmShipmentDetails` WHERE `PiID`='$DeleteID'";
  	 $StmtSHpmtDtlsDelete=$iconn->query($SqlSHpmtDtlsDelete);

	 $SqlBOEDelete="DELETE FROM `ScrmBillOfExchange` WHERE PIID='$DeleteID'";
  	 $stmtBOEDelete=$iconn->query($SqlBOEDelete);

	 $SqlDispatchDelete="DELETE FROM `ScrmShipmentDispatchDetails` WHERE PIID='$DeleteID'";
  	 $stmtDispatchDelete=$iconn->query($SqlDispatchDelete);

	 $SqlTPTDelete="DELETE FROM `ScrmTenPlusTwoDetails` WHERE PiID='$DeleteID'";
  	 $stmtTPTDelete=$iconn->query($SqlTPTDelete);


     $SqlCIDelete="DELETE FROM `ScrmCustomInvoices` WHERE PiID='$DeleteID'";
  	 $stmtCIDelete=$iconn->query($SqlCIDelete);

	 $SqlShipmentBriefDelete="DELETE FROM `ScrmShipmentDetailsBreif` WHERE PiID='$DeleteID'";
  	 $stmtShipmentBriefDelete=$iconn->query($SqlShipmentBriefDelete);

	  $SqllogisticsDelete="DELETE FROM `ScrmLogisticsDetails` WHERE PiID='$DeleteID'";
  	 $stmtlogisticsDelete=$iconn->query($SqllogisticsDelete);

	$SqlAgentnDiscountDelete="DELETE FROM `ScrmEirAgentNDiscountDetails` WHERE PoRefNumber='$DeleteID'";
  	 $stmtAgentnDiscountDelete=$iconn->query($SqlAgentnDiscountDelete);

	$SqlBankNFieghtDelete="DELETE FROM `ScrmEIRBankNFieghtDetaails` WHERE PoRefNumber='$DeleteID'";
  	 $stmtBankNFieghtDelete=$iconn->query($SqlBankNFieghtDelete);

	$SqlEirFdaNOtherDelete="DELETE FROM `ScrmEirFdaNOtherDetails` WHERE PoRefNumber='$DeleteID'";
  	 $stmtEirFdaNOtherDelete=$iconn->query($SqlEirFdaNOtherDelete);



	 IF($stmtCIDelete){

		 $oldCiGrade = $iconn->query("SELECT * FROM `ScrmCIGradeData` WHERE InvId='$DeleteID'")->fetch_assoc();
		 $SqlCIGradeDelete="DELETE FROM `ScrmCIGradeData` WHERE InvId='$DeleteID'";
   	     $stmtCIGradeDelete=$iconn->query($SqlCIGradeDelete);
		 if($stmtCIGradeDelete){
			legacyAuditLogDeleteById('ScrmCIGradeData', 'InvId', $DeleteID, $oldCiGrade, $_POST, [
				'ReferenceNo' => $DeleteID,
				'Remarks' => 'CI grade data deleted with PI',
			], 'Exports');
		 }
		 
	 }
	 
	 
	
	  
	 }
	
	
    
	 
	 
}


 ?>
