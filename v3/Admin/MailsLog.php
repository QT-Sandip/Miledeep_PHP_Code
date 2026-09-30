
<?php  

function mailLog($from,$mailto,$CcHeader,$ReportIds,$subject,$message,$Template,$SentDateTime){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	
	$subject=str_replace("'","",$subject);
	$subject=str_replace("`","",$subject);
	
	$message=str_replace("'","",$message);
	$message=str_replace("`","",$message);
	
	 $SqlScrmManualMails="INSERT INTO `ScrmMailsLog`(`FromMail`, `ToMail`, `CCMail`, `ReportIds`, `Subject`, `Message`, `Template`,`SentDateTime`)
		VALUES('$from','$mailto','$CcHeader','$ReportIds','$subject','$message','$Template','$SentDateTime')"; 
 		$StmtScrmManualMails=$iconn->query($SqlScrmManualMails);	
		$MailLogId = $iconn->insert_id;
		legacyAuditLogInsertById('ScrmMailsLog', 'ID', $MailLogId, [], [], 'Admin');

	}
	?>
