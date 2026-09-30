<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$Country=$_POST['CountryId'];
//$ID=$_POST['ID'];
	

 $sql="SELECT * FROM ScrmPortCities where Country='$Country'";
$stmt=$iconn->query($sql);

/* $SqlPortDetails="SELECT ReferencePortID FROM `ScrmPortCities` Where  PortId='$ID'";
 $StmtPortDetails=$iconn->query($SqlPortDetails);
 $RowPortDetails=$StmtPortDetails->fetch_array();*/


while($Data=$stmt->fetch_array()){
	

	echo'<option value="'.$Data['PortId'].'">'.$Data['Name'].'</option>';
}
echo'<option value="0">Other/New Port</option>';


//echo $Content;


?>