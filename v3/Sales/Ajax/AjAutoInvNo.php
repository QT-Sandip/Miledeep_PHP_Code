<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$ExporterID=$_POST['ExpID'];
$InvDate=$_POST['InvDate'];
	



$SqlPIAutoGeneration="SELECT PIAutoGeneration FROM `AdminConfigDetails`";
$StmtPIAutoGeneration=$iconn->query($SqlPIAutoGeneration);
$RowPIAutoGeneration=$StmtPIAutoGeneration->fetch_array();
$ISPIAutoGeneration=$RowPIAutoGeneration['PIAutoGeneration'];

if($ISPIAutoGeneration){

$Qry='and InvDate  '.getCurrentData($InvDate);
 $SqlPiDetails="SELECT max(InvNo) as InvNO from ScrmPIData where ExporterID='$ExporterID' $Qry";
$StmtPiDetails=$iconn->query($SqlPiDetails);
$RowPiDetails=$StmtPiDetails->fetch_array();
echo $InvNO=$RowPiDetails['InvNO']+1;
 
}

function getCurrentData($InvDate){


	// $InvDate ='2023-09-03'; // commented by uma
	$Currentyear= date('Y', strtotime($InvDate));
	$CurrentMonth= date('m', strtotime($InvDate));

		if($CurrentMonth > 3 ){	
		$DateStart=$Currentyear.'-04-01';	
		$DateEnd=($Currentyear+1).'-03-31';	
		}else {
			$DateStart=($Currentyear-1).'-04-01';	
			$DateEnd=($Currentyear).'-03-31';	
		}

		$DateFilter="BETWEEN '$DateStart' and '$DateEnd'";
		
		return $DateFilter;
		
}



?>