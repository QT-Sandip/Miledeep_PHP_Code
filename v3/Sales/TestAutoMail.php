
<?php  

	function automail($InvID,$MailID,$Edited){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

   
	//$SqlScrmReports="SELECT * FROM `ScrmExportAvalReports` where ID in (1,4)";
	  $SqlScrmReports="SELECT * FROM `ScrmAutoMails` WHERE MailID='$MailID' and Status='1'"; 
	$StmtScrmReports=$iconn->query($SqlScrmReports); 
		while($RowAvalRep=$StmtScrmReports->fetch_array()){
			  $ReportName=$RowAvalRep['ReportName'];
			  $filename = "SSIPL-PI-055-22-23.pdf"; 
$mailto = $RowAvalRep['ToMail'];
   $subject = "Request for 'Q' certificate";
   if($Edited="Edited"){
  /* $message = "Dear Team, \r\n "  
   ."Kindly release the issue of the Q certificate for the attached Invoice. \r\n"
   ."Best Regards, \r\n"."Team - Exports \r\n"."** Contact Documentation team** \r\n
Mr.Naidu  +91 9908302435 \r\n
Ms.Vijaya +91 9398615175 \r\n
Mr.Ramesh Naidu +91 9666499381 \r\n";*/

$message = "Dear Team, \r\n  
Kindly release the issue of the Q certificate for the attached Invoice. \r\n
Best Regards, \r\n Team - Exports \r\n 
** Contact Documentation team** \r\n
Mr.Naidu  +91 9908302435 \r\n
Ms.Vijaya +91 9398615175 \r\n
Mr.Ramesh Naidu +91 9666499381 \r\n";

}
   else{
	   $message = "Edited"; 
   }
    $path = 'Documents';
	$file = $path . "/" . $filename;
    $file = $filename;
$from =$RowAvalRep['FromMail'] ;

			  
			  
			  
			  $Rand=(rand(10,100));
			  $AryData=array();
			  $FileName=str_replace(' ','_', $ReportName );
			  $FileName=$FileName."_".$InvID.".pdf";
					 $url="https://sme.silverlights.in/SalesNew/Reports/".$RowAvalRep['PrintLink'].'='.$InvID.'&Type=F&FileName='.$FileName;
					 $content =file_get_contents( $url );
					  $url = 'https://sme.silverlights.in/SalesNew/Reports/TempFolder/'.$FileName; 
					  $content =file_get_contents( $url );
					   $content = chunk_split(base64_encode($content)); 
					  $AryData['Attachment']=$content;
					  $AryData['FileName']= $FileName;
					  
					  $AryMailBody[]=$AryData;
		}
		


    // a random hash will be necessary to send mixed content
    $separator = md5(time());

    // carriage return type (RFC)
    $eol = "\r\n";

    // main header (multipart mandatory)
    $headers = "From:" .$from. $eol;
	//$headers = "From: name <test@test.com>" . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
    $headers .= "This is a MIME encoded message." . $eol;

    // message
    $body = "--" . $separator . $eol;
    $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
    $body .= "Content-Transfer-Encoding: 8bit" . $eol;
    $body .= $message . $eol;
	$body .= "--" . $separator . $eol;
	
	foreach($AryMailBody as $Row){
		$FileName=$Row['FileName'];
		$Attachment=$Row['Attachment'];
	
	$body .= "Content-Type: application/octet-stream; name=\"" . $FileName . "\"" . $eol;
    $body .= "Content-Transfer-Encoding: base64" . $eol;
    $body .= "Content-Disposition: attachment" . $eol;
    $body .= $Attachment . $eol;
	$body .= "--" . $separator . $eol ;
	}

    

   $body .= "--" . $separator . "--";

    //SEND Mail
    if (mail($mailto, $subject, $body, $headers)) {
        echo "mail send ... OK ".$filename; // or use booleans here
    } else {
        echo "mail send ... ERROR!";
        print_r( error_get_last() );
    }
	
	
		foreach($AryMailBody as $Row){
		$FileName=$Row['FileName'];
		echo '<br>';
		 $path = $_SERVER['DOCUMENT_ROOT'].'/SalesNew/Reports/TempFolder/'.$FileName;
		unlink($path);
	}
	return ;
	}
	?>