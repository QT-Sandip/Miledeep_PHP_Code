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
	  $oldPiDelRow = legacyAuditFetchRowById('ScrmPIData', 'PoID', $DeleteID, 'Admin');
	  $SqlPIDelete="DELETE FROM `ScrmPIData` WHERE PoID='$DeleteID'";   
   	 $StmtPIDelete=$iconn->query($SqlPIDelete);
	 legacyAuditLogDeleteById('ScrmPIData', 'PoID', $DeleteID, $oldPiDelRow, [], [], 'Admin');
	 
	 if($StmtPIDelete){
		 $oldGradeDelRow = legacyAuditFetchRowById('ScrmPoGradeData', 'InvId', $DeleteID, 'Admin');
    $SqlBreakUpDelete="DELETE  FROM `ScrmPoGradeData` WHERE `InvId`='$DeleteID'";   
   	 $StmtBreakUpDelete=$iconn->query($SqlBreakUpDelete); 
	 legacyAuditLogDeleteById('ScrmPoGradeData', 'InvId', $DeleteID, $oldGradeDelRow, [], [], 'Admin');
	 
	 }
     
}

ELSE {
	
	$oldPiFullRow = legacyAuditFetchRowById('ScrmPIData', 'PoID', $DeleteID, 'Admin');
	$SqlPIDelete="DELETE FROM `ScrmPIData` WHERE PoID='$DeleteID'";   
   	 $StmtPIDelete=$iconn->query($SqlPIDelete);
	 legacyAuditLogDeleteById('ScrmPIData', 'PoID', $DeleteID, $oldPiFullRow, [], [], 'Admin');
	 if($StmtPIDelete){
		 
	 $oldPoFullRow = legacyAuditFetchRowById('ScrmPoData', 'PoID', $DeleteID, 'Admin');
	 $SqlPoDelete="DELETE FROM `ScrmPoData` WHERE PoID='$DeleteID'";   
   	 $StmtPoDelete=$iconn->query($SqlPoDelete);	 
	 legacyAuditLogDeleteById('ScrmPoData', 'PoID', $DeleteID, $oldPoFullRow, [], [], 'Admin');
	 
	 $oldGradeFullRow = legacyAuditFetchRowById('ScrmPoGradeData', 'InvId', $DeleteID, 'Admin');
     $SqlBreakUpDelete="DELETE  FROM `ScrmPoGradeData` WHERE `InvId`='$DeleteID'";   
   	 $StmtBreakUpDelete=$iconn->query($SqlBreakUpDelete);
	 legacyAuditLogDeleteById('ScrmPoGradeData', 'InvId', $DeleteID, $oldGradeFullRow, [], [], 'Admin');
	 
	 $oldShipDtlsRow = legacyAuditFetchRowById('ScrmShipmentDetails', 'PiID', $DeleteID, 'Admin');
	 $SqlSHpmtDtlsDelete="DELETE  FROM `ScrmShipmentDetails` WHERE `PiID`='$DeleteID'";   
   	 $StmtSHpmtDtlsDelete=$iconn->query($SqlSHpmtDtlsDelete);
	 legacyAuditLogDeleteById('ScrmShipmentDetails', 'PiID', $DeleteID, $oldShipDtlsRow, [], [], 'Admin');
	 
	 $oldBoERow = legacyAuditFetchRowById('ScrmBillOfExchange', 'PIID', $DeleteID, 'Admin');
	 $SqlBOEDelete="DELETE FROM `ScrmBillOfExchange` WHERE PIID='$DeleteID'";  
   	 $stmtBOEDelete=$iconn->query($SqlBOEDelete);
	 legacyAuditLogDeleteById('ScrmBillOfExchange', 'PIID', $DeleteID, $oldBoERow, [], [], 'Admin');
	 
	 $oldDispatchRow = legacyAuditFetchRowById('ScrmShipmentDispatchDetails', 'PIID', $DeleteID, 'Admin');
	 $SqlDispatchDelete="DELETE FROM `ScrmShipmentDispatchDetails` WHERE PIID='$DeleteID'";  
   	 $stmtDispatchDelete=$iconn->query($SqlDispatchDelete);
	 legacyAuditLogDeleteById('ScrmShipmentDispatchDetails', 'PIID', $DeleteID, $oldDispatchRow, [], [], 'Admin');
	 
	 $oldTptRow = legacyAuditFetchRowById('ScrmTenPlusTwoDetails', 'PiID', $DeleteID, 'Admin');
	 $SqlTPTDelete="DELETE FROM `ScrmTenPlusTwoDetails` WHERE PiID='$DeleteID'";  
   	 $stmtTPTDelete=$iconn->query($SqlTPTDelete);
	 legacyAuditLogDeleteById('ScrmTenPlusTwoDetails', 'PiID', $DeleteID, $oldTptRow, [], [], 'Admin');
	 
	 $oldCiFullRow = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $DeleteID, 'Admin');
     $SqlCIDelete="DELETE FROM `ScrmCustomInvoices` WHERE PiID='$DeleteID'";  
   	 $stmtCIDelete=$iconn->query($SqlCIDelete);
	 legacyAuditLogDeleteById('ScrmCustomInvoices', 'PiID', $DeleteID, $oldCiFullRow, [], [], 'Admin');
	 
	 $oldShipBriefRow = legacyAuditFetchRowById('ScrmShipmentDetailsBreif', 'PiID', $DeleteID, 'Admin');
	 $SqlShipmentBriefDelete="DELETE FROM `ScrmShipmentDetailsBreif` WHERE PiID='$DeleteID'";  
   	 $stmtShipmentBriefDelete=$iconn->query($SqlShipmentBriefDelete);
	 legacyAuditLogDeleteById('ScrmShipmentDetailsBreif', 'PiID', $DeleteID, $oldShipBriefRow, [], [], 'Admin');
	 
	 $oldLogisticsFullRow = legacyAuditFetchRowById('ScrmLogisticsDetails', 'PiID', $DeleteID, 'Admin');
	  $SqllogisticsDelete="DELETE FROM `ScrmLogisticsDetails` WHERE PiID='$DeleteID'";  
   	 $stmtlogisticsDelete=$iconn->query($SqllogisticsDelete);
	 legacyAuditLogDeleteById('ScrmLogisticsDetails', 'PiID', $DeleteID, $oldLogisticsFullRow, [], [], 'Admin');
	
	$oldAgentRow = legacyAuditFetchRowById('ScrmEirAgentNDiscountDetails', 'PoRefNumber', $DeleteID, 'Admin');
	$SqlAgentnDiscountDelete="DELETE FROM `ScrmEirAgentNDiscountDetails` WHERE PoRefNumber='$DeleteID'";  
   	 $stmtAgentnDiscountDelete=$iconn->query($SqlAgentnDiscountDelete);
	 legacyAuditLogDeleteById('ScrmEirAgentNDiscountDetails', 'PoRefNumber', $DeleteID, $oldAgentRow, [], [], 'Admin');
	 
	$oldBankRow = legacyAuditFetchRowById('ScrmEIRBankNFieghtDetaails', 'PoRefNumber', $DeleteID, 'Admin');
	$SqlBankNFieghtDelete="DELETE FROM `ScrmEIRBankNFieghtDetaails` WHERE PoRefNumber='$DeleteID'";  
   	 $stmtBankNFieghtDelete=$iconn->query($SqlBankNFieghtDelete);
	 legacyAuditLogDeleteById('ScrmEIRBankNFieghtDetaails', 'PoRefNumber', $DeleteID, $oldBankRow, [], [], 'Admin');
	
	$oldEirRow = legacyAuditFetchRowById('ScrmEirFdaNOtherDetails', 'PoRefNumber', $DeleteID, 'Admin');
	$SqlEirFdaNOtherDelete="DELETE FROM `ScrmEirFdaNOtherDetails` WHERE PoRefNumber='$DeleteID'";  
   	 $stmtEirFdaNOtherDelete=$iconn->query($SqlEirFdaNOtherDelete);
	 legacyAuditLogDeleteById('ScrmEirFdaNOtherDetails', 'PoRefNumber', $DeleteID, $oldEirRow, [], [], 'Admin');
	 
	 
	 
	 IF($stmtCIDelete){
		 $oldCIGradeFullRow = legacyAuditFetchRowById('ScrmCIGradeData', 'InvId', $DeleteID, 'Admin');
		 $SqlCIGradeDelete="DELETE FROM `ScrmCIGradeData` WHERE InvId='$DeleteID'";  
   	     $stmtCIGradeDelete=$iconn->query($SqlCIGradeDelete); 
		 legacyAuditLogDeleteById('ScrmCIGradeData', 'InvId', $DeleteID, $oldCIGradeFullRow, [], [], 'Admin');
		 
	 }
	 
	 
	
	  
	 }
	
	
    
	 
	 
}


 ?>
