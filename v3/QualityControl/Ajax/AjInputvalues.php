<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $Type=$_POST['Type']; 
$LotId=$_POST['LotId'];


			

if($Type=='Count'){
			$SqlLot="SELECT * FROM `RmLotDetatils` WHERE RmLotID='$LotId'";
			$StmtLot=$iconn->query($SqlLot);
			$Data=$StmtLot->fetch_array();
echo json_encode($Data);			
					
}			

		
	
?>