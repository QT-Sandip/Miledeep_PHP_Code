<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$BillID=$_POST['BillID'];
$FinancierID=$_POST['FinancierID'];
$Deductions=$_POST['Deductions'];
$Yield=$_POST['Yield'];
$Remarks=$_POST['Remarks'];
$AryBreakUp=$_POST['AryBreakUp'];

$SqlCheckupBrkUp="select count(*) Total from RmBillDetails where ArrivalID=$BillID";
$StmtCheckupBrkUp=$iconn->query($SqlCheckupBrkUp);
$rowCheckupBrkUp=$StmtCheckupBrkUp->fetch_array();

if($rowCheckupBrkUp['Total'] > 0){

 $SqlRmBillDetails="update `RmBillDetails` set
	
	 `UserID`='$PostedUserID',
	 `FinancierID`='$FinancierID',
	  `CreatedDateTime`='$CreatedDateTime',
	  `Deductions`='$Deductions',
	  `Yield`='$Yield',
	  `Remarks`='$Remarks' where `ArrivalID`='$BillID'";

}else{
	$SqlRmBillDetails="INSERT INTO `RmBillDetails`
	(`ArrivalID`, `UserID`,`FinancierID`, `CreatedDateTime`,`Deductions`,`Yield`,`Remarks`)
	VALUES ('$BillID','$PostedUserID','$FinancierID','$CreatedDateTime','$Deductions','$Yield','$Remarks')";	
	

}


$StmtBillDetails=$iconn->query($SqlRmBillDetails);
$AryBreakUp=json_decode($AryBreakUp,true);

foreach($AryBreakUp as $Item){

$LotID=$Item['ArrivalID'];
$Weight=$Item['Weight'];
$Count=$Item['Count'];
$Rate=$Item['Rate'];
$Variety=$Item['Variety'];

$SqlCheckupBrkUp="select count(*) Total from RmBillBreakup where ArrivalID=$LotID";
$StmtCheckupBrkUp=$iconn->query($SqlCheckupBrkUp);
$rowCheckupBrkUp=$StmtCheckupBrkUp->fetch_array();

if($rowCheckupBrkUp['Total'] > 0){
		$SqlRmBillBreakUp="update  `RmBillBreakup` set
	`BillID`='$BillID',
	`Description`='$Variety',
	 `Weight`='$Weight',
	  `Count`='$Count',
	   `Rate`='$Rate' where ArrivalID='$LotID' ";
}else{

$SqlRmBillBreakUp="INSERT INTO `RmBillBreakup`
(`LotID`,`BillID`,`Description`, `Weight`, `Count`, `Rate`) 
VALUES ('$ArrivalID','$BillID','$Variety','$Weight','$Count','$Rate')";
}
$StmtRmBillBreakUp=$iconn->query($SqlRmBillBreakUp);

}


if($StmtRmBillBreakUp){

$row['sts']='success';
$row['msg']=' Success';
echo json_encode($row);
}else{
$row['sts']='Failed';
$row['msg']='Failed';
echo json_encode($row);
}

 ?>
