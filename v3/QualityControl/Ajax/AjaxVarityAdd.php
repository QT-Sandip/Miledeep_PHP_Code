<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $api = API_CUSTOM_INVOICES;

// JSON -> ARRAY -- json_decode 


$AryBreakUp = json_decode($_POST["AryBreakUp"]);

// var_dump($AryBreakUp);

foreach($AryBreakUp as $item) {
      //$item->rowID;
     //$item->Description;
  
	$SqlUpdate="UPDATE ScrmPoGradeData SET QcDescription='".trim($item->Description)."' WHERE ID='".trim($item->rowID)."'";
	 $StmtUpdate= $iconn->query($SqlUpdate);
	 if($StmtUpdate){
		legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', trim($item->rowID), null, $_POST, [
			'ReferenceNo' => trim($item->rowID),
			'Remarks' => 'QC description updated',
		], 'QualityControl');
	 }
	 
}

	 

// $bodyContent = array(
//     'ExporterID' => $ExporterID
// );

?>
