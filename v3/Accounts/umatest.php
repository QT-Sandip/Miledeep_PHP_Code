<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


	/***First script rates update start**/
	$SqlBackUp=	"SELECT RmLotID FROM `RmLotDetatils` WHERE 
	PurchaseDate BETWEEN '2022-09-01' and '2022-09-13' 
	ORDER BY `RmLotID` DESC";
	//$SqlBackUp=	"SELECT RmLotID FROM `RmLotDetatils` WHERE `RmLotID`='16489'";
	
	
	
	$StmtBackUp=$iconn->query($SqlBackUp);
	while($RowBackup=$StmtBackUp->fetch_array()){
		$LotID=$RowBackup['RmLotID'];
		
		
		$SqlGrade="SELECT * FROM `PpcHLGradingBreakup` WHERE LotID='$LotID'";
		$StmtGrade=$iconn->query($SqlGrade);
		while($RowGrade=$StmtGrade->fetch_array()){
			
					$DataID=$RowGrade['DataID'];
					$PackingGrade=$RowGrade['PackingGrade'];


$Sqlrates="SELECT * FROM `SalesCostingRates` WHERE Grade='$PackingGrade' and TrackID=46";
		$Stmtrates=$iconn->query($Sqlrates);
	 $Rowrates=$Stmtrates->fetch_array();
	 $Usd=$Rowrates['USD'];
	 echo $LotID ;
		echo '<br>';
	$oldRow = legacyAuditFetchRowById('PpcHLGradingBreakup', 'DataID', $DataID, 'Accounts');
	echo $Sqlupdate="UPDATE `PpcHLGradingBreakup` SET `Usd`='$Usd' WHERE `DataID`='$DataID' ";
		$Stmtupdate=$iconn->query($Sqlupdate);
		if($Stmtupdate){
		    legacyAuditLogUpdateById('PpcHLGradingBreakup', 'DataID', $DataID, $oldRow, array('Usd' => $Usd), array(), 'Accounts');
		}
		echo '<br>';
		
	}
	}
	
	/***First script rates update end**/

	echo "Success";


?>
