<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$AvalMcs=$_POST['AvalMcs'];

$InvNo=$_POST['InvNo'];
$NoContainers=$_POST['NoContainers'];
$InvDate=$_POST['InvDate'];
$ExporterID=$_POST['ExporterID'];

$StartYear=date('Y',strtotime($InvDate));

	for ($i=0; $i < $NoContainers ; $i++){
		$Series=$i+1;
		$GenInvNo=$InvNo+$i;
		$GenInvNo = sprintf( '%03d', $GenInvNo);
		if(checkInvoiceNo($iconn,$StartYear,$GenInvNo)){
		$AryAvalInvNo[]=$GenInvNo;
		}else{
		$NoContainers++;
		$AryNotAvalInvNo[]=$GenInvNo;
		}
		
	 }
	 
	 
	 $AvalInvNo=implode(',',$AryAvalInvNo);
	 $NotAvalInvNo=implode(',',$AryNotAvalInvNo);
	 $ArysqlCount=implode(',',$ArysqlCount);
	 
	 if(count($AryNotAvalInvNo) > 0){
	 $Data['Error']="Invoice Numbers Already Exist";
	 }else{
	 $Data['Error']=null;
	 }
	 $Data['AvalNo']=$AvalInvNo;
	 $Data['NotAvalNo']=$NotAvalInv;
	 
	 echo json_encode($Data);


	function checkInvoiceNo($iconn,$StartYear,$InvNo){
		global $ExporterID;
			 $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01'; 
			  $FinEndDate=$EndYear.'-03-31';
	
	//if($ExporterID==5){
	$sqlCount="SELECT count(*) as Count FROM `ScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and Status not in(2,4,7) and ExporterID='$ExporterID'";
	/*}else{
	$sqlCount="SELECT count(*) as Count FROM `ScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and Status not in(2,4,7) and ExporterID BETWEEN '1' and '3'";	
	}*/
	
	//echo $sqlCount;
	$StmtCount=$iconn->query($sqlCount);
	$RowCount=$StmtCount->fetch_array();
	if($RowCount['Count']==0){	
		return true;		
	}else{
		return false;
	}






	 
	
}
?>