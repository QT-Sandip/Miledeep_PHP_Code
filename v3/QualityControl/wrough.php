<?php 
include "../includes/config.php";


$SqlRmBd="SELECT * FROM `RM_BILL_DETAILS` order by BILL_ID ";
$StmtRmBd=$iconn->query($SqlRmBd);
while($RowRmBd=$StmtRmBd -> fetch_array()){
$LOT_ID=$RowRmBd['LOT_ID'];

$oldGradingRow = legacyAuditFetchRowById('PPC_GRADING_HL', 'LOT_ID', $LOT_ID, 'QualityControl');
$SqlGrading="UPDATE `PPC_GRADING_HL` SET `STATUS`='1' WHERE LOT_ID='$LOT_ID'";
$StmtGrading=$iconn->query($SqlGrading);
if($StmtGrading){
	legacyAuditLogUpdateById('PPC_GRADING_HL', 'LOT_ID', $LOT_ID, $oldGradingRow, ['STATUS' => '1'], ['Remarks' => 'Grading Status Updated'], 'QualityControl');
}
}

echo $SqlGrading;

/*

for($i=20; $i <101; $i++){
	$SS_QL="INSERT INTO `ACC_PURCHASE_RATES` (`COUNT`, `USD`, `TARCK_ID`) VALUES ('$i','','1')";
	$STMT_LIST=$iconn->query($SS_QL);
	
}


*/

	



?>
