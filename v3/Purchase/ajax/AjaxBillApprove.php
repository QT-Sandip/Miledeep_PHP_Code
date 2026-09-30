<?php
session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$LotID=$_POST['LotID']; 
$PostedUserID=$_POST['PostedUserID'];
    
	
	$SqlRmBillDetails="update `RmBillDetails` set BillStatus=1,ApprovedBy='$PostedUserID' 
	where ArrivalID='$LotID'";	
	$StmtBillDetails=$iconn->query($SqlRmBillDetails);

	$SqlRmBillDetails="SELECT * FROM `RmBillDetails` where ArrivalID='$LotID'";
	$StmtBillDetails=$iconn->query($SqlRmBillDetails);
	$RowBillDetails=$StmtBillDetails->fetch_array();
	$RmSupplierID=$RowBillDetails['FinancierID'];

	$SqlRmBillDetails="update `RmArrivalDetatils` set RmSupplierID=$RmSupplierID where RmArrivalID='$LotID'";	
	$StmtBillDetails=$iconn->query($SqlRmBillDetails);
	
	if($StmtBillDetails){
	$Response = array('Error' => null, "Msg" => "Bill Approved Successfully");
    echo json_encode($Response);
	}else{
	$Response = array('Error' => "Error Unable To Create Bill");
    echo json_encode($Response);
	}

?>
