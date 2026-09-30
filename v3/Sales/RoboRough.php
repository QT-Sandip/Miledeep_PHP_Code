<?php 
$PageTitle="Proforma Invoice List";
$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");




$SqlOrderStatus="SELECT * FROM `ScrmForwardContractsDetails` ";
	$OrdersExec=$iconn->query($SqlOrderStatus);
	
	while($OrderStatusRow=$OrdersExec->fetch_array()){
		$FCNumber=$OrderStatusRow['FCNumber'];
		$NetValue=$OrderStatusRow['NetValue'];
		/*$PoID=$OrderStatusRow['PIID'];
		$BnkChrges=$OrderStatusRow['BnkChrges'];
		$EtraAmount=$OrderStatusRow['EtraAmount'];
		$OtherAmount=$OrderStatusRow['OtherAmount'];
		
		$AryPrices=getPaymentValues($iconn,$PoID);
		$InvValueUSD=$AryPrices['InvValueUSD'];
		
$SqlUtilisedValues="SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
$StmtUtilisedValues=$iconn->query($SqlUtilisedValues);
$RowUtilisedValues=$StmtUtilisedValues->fetch_array();
$UtilizedValue=$RowUtilisedValues['UtilizedValue'];

 $ShortFall=$InvValueUSD+$EtraAmount-$BnkChrges-$UtilizedValue-$OtherAmount;

if($ShortFall==0){
	$Status=1;
}else{
	$Status=0;
}
*/

$SqlUtilisedValues="SELECT COALESCE(sum(UtilizedValue),0) as UtilizedValue FROM `ScrmCollectionBreakUP`
 WHERE FcNumber='$FCNumber'";
$StmtUtilisedValues=$iconn->query($SqlUtilisedValues);
$RowUtilisedValues=$StmtUtilisedValues->fetch_array();
$UtilizedValue=$RowUtilisedValues['UtilizedValue'];
$BalanceValue=$NetValue-$UtilizedValue;

		
echo	 $SqlUpdate="update ScrmForwardContractsDetails set 
	 UtilizedValue='$UtilizedValue' ,
	 BalancedValue='$BalanceValue' 
	 where FCNumber='$FCNumber'";
	$StmtUpdate=$iconn->query($SqlUpdate);
		
	}


	


?>