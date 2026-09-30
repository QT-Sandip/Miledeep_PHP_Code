<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $Type=$_POST['Type']; 

$MailID=$_POST['MailID'];

if($Type=='AutoFillDetails'){
			//$SqlClient=	"SELECT * FROM `$ClientBankDetails` WHERE ClientID='$ClientID'";
		 	$SqlMail="SELECT * FROM `ScrmManualMails` WHERE MailID='$MailID'"; 
	$StmtMail=$iconn->query($SqlMail); 
			$Data=$StmtMail->fetch_array();
echo json_encode($Data,true);			
					
}			

		
	
?>