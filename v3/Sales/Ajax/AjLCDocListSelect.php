<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Data=$_POST['Data']; 
$PoId=$_POST['PoId']; 

$AryData=json_decode($Data);

 $oldDocData = legacyAuditFetchRowById('ScrmDocListData', 'PoRefID', $PoId, 'Sales');
 $SqlDocListDelete="delete from `ScrmDocListData` where PoRefID='$PoId'";
 $StmtDocListDelete=$iconn->query($SqlDocListDelete);
 legacyAuditLogDeleteById('ScrmDocListData', 'PoRefID', $PoId, $oldDocData, [], ['Remarks' => 'Doc list data deleted'], 'Sales');

foreach($AryData as $DataItem){
	$AryDataItem= explode('&',$DataItem);
	$DocID=$AryDataItem[0];
	$DocOrg=$AryDataItem[1];
	$DocCopy=$AryDataItem[2];
	
$SqlDocListInsert="INSERT INTO `ScrmDocListData`(`PoRefID`, `DocID`,
	`Originals`, `Copies`) VALUES('$PoId','$DocID','$DocOrg','$DocCopy')"; 
 $StmtDocListInsert=$iconn->query($SqlDocListInsert);
	legacyAuditLogInsertById('ScrmDocListData', 'PoRefID', $PoId, ['DocID' => $DocID], ['Remarks' => 'Doc list item added'], 'Sales');
}

if($StmtDocListInsert){
	$Response=array("Error" => null,"Msg" => "Data Updated"); 
}else{
	$Response=array("Error" => "Data Update Failed","Msg" => ""); 
}

echo json_encode($Response);
?>
