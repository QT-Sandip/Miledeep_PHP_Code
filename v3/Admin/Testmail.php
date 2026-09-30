<?php  
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
date_default_timezone_set("Asia/Kolkata");

include ("MailsLog.php");


$filename = "SSIPL-PI-055-22-23.pdf";
 $TempleateID = $_POST['TempleateID'];
//$InvIds = $_POST['InvIds'];
$InvIds=explode(',',$_POST['InvIds']); 
$SentDateTime=date('Y-m-d H:i:s');

$Type = $_POST['Type'];

$mailto = $_POST['ToEmailAddress'];

$CcHeader = $_POST['CcHeader'];
   $subject = $_POST['Subject'];
    $message = $_POST['Message'];
    $PoID = $_POST['InvId'];
	include "SalesInculdes/SalesConstants.php"; 

    //$DocumentsList = $_POST['DocumentsList'];
	 $DocumentsList=explode(',',$_POST['DocumentsList']); 

    $path = 'Documents';
	$file = $path . "/" . $filename;
    $file = $filename;
//$from = "developers@silverlights.in";




if($Type=="SemiManualMail"){
  $SqlMail="SELECT * FROM `ScrmManualMails` WHERE MailID='$TempleateID'"; 
	$StmtMail=$iconn->query($SqlMail); 
	$RowMail=$StmtMail->fetch_assoc();
	$ReportIds=$RowMail['ReportIds'];
	$from=$RowMail['FromMail'];
	
	
$SqlScrmReports="SELECT * FROM `ScrmExportAvalReports` where ID in ($ReportIds)"; 
	$StmtScrmReports=$iconn->query($SqlScrmReports);
	while($RowAvalRep=$StmtScrmReports->fetch_assoc()){
 	
			  foreach($InvIds as $i =>$PoID) {
				  include "SalesInculdes/SalesConstants.php"; 

	$i=$i+1;
	          $QueryID=$RowAvalRep['QueryID'];
			  $ReportName=$RowAvalRep['ReportName'];
			  $Rand=(rand(10,100));
			  $AryData=array();
			  $FileName=str_replace(' ','_', $ReportName );
			  
						$FileName=$CInvNo."".$FileName."".$CurrentYear."_".$NxtYear.".pdf";
						$FileWriteUrl=$_SESSION["ClientPath"]."Reports/".$RowAvalRep['PrintLink'].$QueryID.'='.$PoID.'&Type=F&FileName='.$FileName."&Token=".$_SESSION['UserID']; 
						$content =file_get_contents($FileWriteUrl);
			
			
					  $FileReadUrl = $MailReadTempFolder.$FileName;
					  $content =file_get_contents( $FileReadUrl );
					  $content = chunk_split(base64_encode($content)); 
					  $AryData['Attachment']=$content;
					  $AryData['FileName']= $FileName;
					  $AryMailBody[]=$AryData;
		
}
}

 
}

   else if($Type=="ManualMail"){
	   $from = $_POST['FromEmailAddress'];
	   //$ReportIds = $_POST['DocumentsList'];
	   
	   	
$ReportId=array();
	foreach($DocumentsList as $i =>$key) {
	$i=$i+1;
	
	 $SqlScrmReports="SELECT * FROM `ScrmExportAvalReports` where ReportName='$key'"; 
	$StmtScrmReports=$iconn->query($SqlScrmReports); 
		$RowAvalRep=$StmtScrmReports->fetch_assoc();
			  $QueryID=$RowAvalRep['QueryID']; 
			  $ReportName=$RowAvalRep['ReportName'];
			  $ReportId[]=$RowAvalRep['ID'];
			  $Rand=(rand(10,100));
			  $AryData=array();
			  $FileName=str_replace(' ','_', $ReportName );
			 
			  			  $FileName=$CInvNo."".$FileName."".$CurrentYear."_".$NxtYear.".pdf";

					 	$FileWriteUrl=$_SESSION["ClientPath"]."Reports/".$RowAvalRep['PrintLink'].$QueryID.'='.$PoID.'&Type=F&FileName='.$FileName."&Token=".$_SESSION['UserID']; 
						$content =file_get_contents($FileWriteUrl);
			
			
					  $FileReadUrl = $MailReadTempFolder.$FileName;
					  $content =file_get_contents( $FileReadUrl );
					   $content = chunk_split(base64_encode($content)); 
					  $AryData['Attachment']=$content;
					  $AryData['FileName']= $FileName;
					  $AryMailBody[]=$AryData;
		}
		$ReportIds = implode(',', $ReportId);
		
   }

    // a random hash will be necessary to send mixed content
    //$separator = md5(time());
    $separator = md5(uniqid(time()));

    // carriage return type (RFC)
    $eol = "\r\n";

    // main header (multipart mandatory)
    $headers = "From:" .$from. $eol;
	$headers .= "CC:" .$CcHeader . $eol;

	//$headers = "From: name <test@test.com>" . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
    $body = "--" . $separator . $eol;
   
    $body .= "Content-Transfer-Encoding: 7bit" . $eol;
    $body .= "This is a MIME encoded message." . $eol;

    // message
    $body = "--" . $separator . $eol;
    $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
    $body .= "Content-Transfer-Encoding: 8bit" . $eol;
    $body .= $message . $eol;
	
	
	foreach($AryMailBody as $Row){
		$FileName=$Row['FileName'];
		$Attachment=$Row['Attachment'];
	$body .= "--" . $separator . $eol;
	$body .= "Content-Type: application/octet-stream; name=\"" . $FileName . "\"" . $eol;
    $body .= "Content-Transfer-Encoding: base64" . $eol;
   // $body .= "Content-Disposition: attachment" . $eol;
	$body .= "Content-Disposition: attachment; filename=\"".$FileName."\"".$eol.$eol;
    $body .= $Attachment.$eol.$eol;
 
	
	//$body .= $Attachment . $eol;
	
	}

     // $body .= "--" . $separator . $eol ; 
   $body .= "--" . $separator . "--";
   
  

   
    if (mail($mailto, $subject, $body, $headers)) {
        echo "mail send ... OK ".$subject; // or use booleans here
		mailLog($from,$mailto,$CcHeader,$ReportIds,$subject,$message,$Template,$SentDateTime);
    } else {	
        echo "mail send ... ERROR!";
		//echo "Mailer Error: " . $mail->ErrorInfo;
		//print_r(error_get_last());
    }
	
	
	
	
		foreach($AryMailBody as $Row){
		$FileName=$Row['FileName'];
	///	echo '<br>';
		 $path = $MailTempFolder.$FileName;
		 unlink($path);
	}
	
	?>