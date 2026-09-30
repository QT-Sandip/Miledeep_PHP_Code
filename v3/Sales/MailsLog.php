
<?php  

//function mailLog($from,$mailto,$CcHeader,$ReportIds,$subject,$message,$Template,$SentDateTime)
function mailLog($from,$mailto,$CcHeader,$ReportIds,$subject,$message,$Template,$SentDateTime,$Type,$PoID)

{

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	/*if($Type=='ManualMail'){
		$PoID=$PoID;
	}
	else if($Type=='SemiManualMail'){
		 $InvIds=explode(',',$InvIds); 

				$PoID=$InvIds;

	}*/
	$subject=str_replace("'","",$subject);
	$subject=str_replace("`","",$subject);
	
	$message=str_replace("'","",$message);
	$message=str_replace("`","",$message);
	
	/* $SqlScrmManualMails="INSERT INTO `ScrmMailsLog`(`FromMail`, `ToMail`, `CCMail`, `ReportIds`, `Subject`, `Message`, `Template`,`SentDateTime`)
		VALUES('$from','$mailto','$CcHeader','$ReportIds','$subject','$message','$Template','$SentDateTime')"; 
	*/
	$SqlScrmManualMails="INSERT INTO `ScrmMailsLog`(`FromMail`, `ToMail`, `CCMail`, `ReportIds`, `Subject`, `Message`, `Template`,`SentDateTime`,`PoId`)
		VALUES('$from','$mailto','$CcHeader','$ReportIds','$subject','$message','$Template','$SentDateTime','$PoID')"; 
		$StmtScrmManualMails=$iconn->query($SqlScrmManualMails);
		legacyAuditLogInsertById('ScrmMailsLog', 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'Mail logged'], 'Sales');
		

	}
	?>
