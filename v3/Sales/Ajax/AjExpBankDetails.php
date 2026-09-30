<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$ExpID=$_POST['ExpID'];
	

//$sql="SELECT * FROM ScrmBankDetailsMaster where ExpID='$ExpID'";
$sql="SELECT * FROM ScrmBankDetailsMaster where ExpID='$ExpID' and BankStatus=1";
$stmt=$iconn->query($sql);
if(mysqli_num_rows($stmt)==0){
	//$sql="SELECT * FROM ScrmBankDetailsMaster ";
	$sql="SELECT * FROM ScrmBankDetailsMaster where BankStatus=1";

}

$stmt=$iconn->query($sql);

//$rowcount=mysqli_num_rows($stmt);


echo '<option value="">Select Bank</option>';
while($Data=$stmt->fetch_array()){
	

	// echo '<option value="'.$Data['ID'].'">'.$Data['AccntName'].'</option>';
	
	echo '<option value="'.$Data['ID'].'">'.$Data['AccntName'].' - '.$Data['BankName'].' - '.$Data['AccountNumber'].'</option>';
}





?>
