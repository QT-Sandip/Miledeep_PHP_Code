<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$ContractorID=$_POST['ContractorID'];
	

 $sql="SELECT * FROM PpcSubContractorMaster where ContactorID='$ContractorID'";
$stmt=$iconn->query($sql);

$SelectType="Select Batch";

echo '<option value="">'.$SelectType.'</option>';

while($Data=$stmt->fetch_array()){
	

	echo '<option value="'.$Data['SubContactorID'].'">'.$Data['SubContractorName'].'-'.$Data['Area'].'</option>';
}





?>