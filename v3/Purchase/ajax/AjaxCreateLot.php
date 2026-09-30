<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$Data=$_POST['Data'];

var_dump($Data);

$PurchaseCount=trim($Data['PurchaseCount']);
$PurchaseWeight=trim($Data['PurchaseWeight']);
$ProductionWeight=trim($Data['ProductionWeight']);
$Remarks=trim($Data['Remarks']);
$SealNo=trim($Data['SealNo']);
$NetWeight=trim($Data['NetWeight']);
$PostedUserID=trim($Data['PostedUserID']);
$LotID=trim($Data['LotID']);

$SqlRmLotDetatils="update RmLotDetatils set
                 PurchaseCount='$PurchaseCount',
                 PurchaseWeight='$PurchaseWeight',
                 ProductionWeight='$ProductionWeight',
                 Remarks='$Remarks',
                 SealNo='$SealNo',
                 NetWeight='$NetWeight',
                 PostedUserID='$PostedUserID' where RmLotID='$LotID'";
$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
if($StmtRmLotDetatils){
    echo 'Success';
}else{
    echo 'Failure';    
}




?>