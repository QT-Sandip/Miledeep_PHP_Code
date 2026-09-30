<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PiID=$_POST['PiID'];

if($PiID!=''){
	
	$SqlPICount="SELECT * FROM ScrmPIData WHERE PoID = '$PiID'";
	$StmtPICount=$iconn->query($SqlPICount);
	$RowPICount=$StmtPICount->fetch_array();
	$ExporterID=$RowPICount['ExporterID'];

	 $SqlPiDetails="SELECT max(InvNo) as InvNO from ScrmPIData where ExporterID='$ExporterID'";
	$StmtPiDetails=$iconn->query($SqlPiDetails);
	$RowPiDetails=$StmtPiDetails->fetch_array();
	$InvNO=$RowPiDetails['InvNO']+1;

	$SqlDuplicatePI="CREATE  TABLE ScrmPiDataTemp SELECT * FROM ScrmPIData WHERE PoID = '$PiID'";
	$StmtDuplicatePI=$iconn->query($SqlDuplicatePI);
	  if(!$StmtDuplicatePI){
		  echo mysqli_errno($iconn);
	 }else{
		 
		 echo "Hi";
	 }
	 
	 //$SqlDuplicatePI="UPDATE ScrmPiDataTemp SET PoID = NULL,InvNo='$InvNO',Status=0";
	 $SqlDuplicatePI="UPDATE ScrmPiDataTemp SET PoID = NULL,InvNo='$InvNO',PoNo='',PoDate='',Status=0";
	 $StmtDuplicatePI=$iconn->query($SqlDuplicatePI);	 
	 if(!$StmtDuplicatePI){
		  echo mysqli_errno($iconn);;
	 }else{
		 
		 echo "Hi";
	 } 
	 
	 $SqlDuplicatePI="INSERT INTO ScrmPIData SELECT * FROM ScrmPiDataTemp";
	 $StmtDuplicatePI=$iconn->query($SqlDuplicatePI);
		$DupInvID = $iconn->insert_id;		 
	 if(!$StmtDuplicatePI){
		  echo mysqli_errno($iconn);;
	 }else{
		legacyAuditLogInsertById('ScrmPIData', 'PoID', $DupInvID, $_POST, [
			'ReferenceNo' => $DupInvID,
			'Remarks' => 'PI duplicated from ' . $PiID,
		], 'Exports');
		 echo "Hi";
	 }
	 
	 $SqlDuplicatePI="DROP  TABLE IF EXISTS ScrmPiDataTemp;";
	 $StmtDuplicatePI=$iconn->query($SqlDuplicatePI);
	 if(!$StmtDuplicatePI){
		  echo mysqli_errno($iconn);;
	 }else{		 
		 echo "Hi";
	 }
	

			    $SqlGradeBreakUp="CREATE  TABLE ScrmPoGradeDataTemp SELECT * FROM ScrmPoGradeData WHERE InvId = '$PiID'";
				$StmtGradeBreakUp=$iconn->query($SqlGradeBreakUp);
				if(!$StmtGradeBreakUp){
		  echo mysqli_errno($iconn);;
	 }else{		 
		 echo "Hi";
	 }
				$SqlGradeBreakUp="UPDATE ScrmPoGradeDataTemp SET ID = NULL,InvId='$DupInvID'";
				$StmtGradeBreakUp=$iconn->query($SqlGradeBreakUp);
				
				if(!$StmtGradeBreakUp){
		  echo mysqli_errno($iconn);;
	 }else{		 
		 echo "Hi";
	 }
				
				$SqlGradeBreakUp="INSERT INTO ScrmPoGradeData SELECT * FROM ScrmPoGradeDataTemp";
				$StmtGradeBreakUp=$iconn->query($SqlGradeBreakUp);
				$DupGradeId = $iconn->insert_id;
				
				if(!$StmtGradeBreakUp){
		  echo mysqli_errno($iconn);;
	 }else{
		legacyAuditLogInsertById('ScrmPoGradeData', 'ID', $DupGradeId, $_POST, [
			'ReferenceNo' => $DupInvID,
			'Remarks' => 'PI grade data duplicated from ' . $PiID,
		], 'Exports');		 
		 echo "Hi";
	 }
				
				$SqlGradeBreakUp="DROP  TABLE IF EXISTS ScrmPoGradeDataTemp;";
				$StmtGradeBreakUp=$iconn->query($SqlGradeBreakUp);

	 
	 

	 
		 
}

?>
