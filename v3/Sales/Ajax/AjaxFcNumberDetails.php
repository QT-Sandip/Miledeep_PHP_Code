<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$FCNumber=$_POST['FCNumber'];
$PoID=$_POST['PoID'];

 $SqlFCNumber="SELECT * FROM `ScrmForwardContractsDetails` WHERE FCNumber='$FCNumber'; "; 
$StmtSqlFCNumber=$iconn->query($SqlFCNumber);
$Data=$StmtSqlFCNumber->fetch_array();
$AvalFcBalance=$Data['NetValue']-$Data['UtilizedValue'];
$Data['Balance']=$AvalFcBalance;


$AryPrices=getPaymentValues($iconn,$PoID);
$InvValueUSD=$AryPrices['InvValueUSD'];


$SqlUtilisedValues="SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
$StmtUtilisedValues=$iconn->query($SqlUtilisedValues);
$RowUtilisedValues=$StmtUtilisedValues->fetch_array();
$UtilizedValue=$RowUtilisedValues['UtilizedValue'];

$ShortFall=$InvValueUSD-$UtilizedValue;

if($AvalFcBalance > $ShortFall){
$Data['WithDrawFcBalance']=$ShortFall;	
}else{
$Data['WithDrawFcBalance']=$AvalFcBalance;	
}

echo json_encode($Data,true);

	  
	  


?>