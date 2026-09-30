<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$FCNumber=$_POST['FCNumber'];

 $SqlFCNumber="SELECT * FROM `ScrmForwardContractsDetails` WHERE FCNumber='$FCNumber'; "; 
$StmtSqlFCNumber=$iconn->query($SqlFCNumber);
$RowCount=$StmtSqlFCNumber->num_rows;

 if($RowCount > 0){
	echo  'Fc Number Already Used';
 }		  
	  


?>