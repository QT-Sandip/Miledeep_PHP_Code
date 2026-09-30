<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$Country=$_POST['CountryId'];
	

 $sql="SELECT * FROM $TblScrmPortCities where Country='$Country'";
$stmt=$iconn->query($sql);

echo '<option value="">Select Port</option>';

while($Data=$stmt->fetch_array()){
	

	echo '<option value="'.$Data['PortId'].'">'.$Data['Name'].'</option>';
}





?>