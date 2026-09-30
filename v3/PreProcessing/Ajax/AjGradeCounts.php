<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$LotID=$_POST['LotID'];

if($LotID!=0){
  $SqlCount="SELECT HLCount,HLWeight FROM PpcHLGradingBreakup where LotID='$LotID'";
}

else{
	$SqlCount="SELECT DISTINCT HLCount FROM `PpcHLGradingBreakup` ORDER bY HLCount ASC";
}
$StmtCount=$iconn->query($SqlCount);


	echo ' <option value=""> Select</option>';


while($RowCount=$StmtCount->fetch_array()){
	if($LotID!=0){
	$HLWeight='- '.$RowCount['HLWeight'].'Kgs';
	}
	echo ' 
	<option value="'.$RowCount['HLCount'].'">'.$RowCount['HLCount'].'C  '.$HLWeight.'</option>';
}





?>