<?php
session_start();
include "../../includes/config.php";



$CodeListID=$_POST['CodeListID'];
	
echo $SqlCodeList="SELECT * FROM `QcCodeList` where CLID='$CodeListID'";
$StmtCodeList=$iconn->query($SqlCodeList);
$Data=$StmtCodeList->fetch_array();
echo json_encode($Data);




?>