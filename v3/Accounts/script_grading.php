<?php
//ini_set('display_errors', 1);
include "../includes/config.php";

include "../includes/AjaxGradingCreate.php";
 $SqlRmBd="SELECT * from inputmaterial_details
 where UNIT_ID=2 and UNIT=2 and PURCHASE_DATE between  
 '2020-10-01' and 
 '2020-10-31'";
$StmtRmBd=$iconn->query($SqlRmBd);
while($RowRmBd=$StmtRmBd -> fetch_array()){
	 $LOT_ID=$RowRmBd['id'];
	
	
	// echo getGradingBreakUp($iconn,$LOT_ID);
	
			echo  $RAW_LOT_NO = $RowRmBd['LOT_NO'];
			  
			  $B=substr ($RAW_LOT_NO ,0,1 );
		 $RAW_LOT_NO=substr ($RAW_LOT_NO ,1 ); 
			$RAW_LOT_NO =  str_replace("O","0",$RAW_LOT_NO);
			$RAW_LOT_NO = ltrim($RAW_LOT_NO, "0");  
			
			 $RAW_LOT_NO = sprintf( '%04d', $RAW_LOT_NO);

 $RAW_LOT_NO=$B.$RAW_LOT_NO;
 
 $RAW_LOT_NO = $RowRmBd['REFERENCE_ID'];

$oldRow = legacyAuditFetchRowById('inputmaterial_details', 'id', $LOT_ID, 'Accounts');
echo $Sqlup="update  inputmaterial_details set LOT_NO='$RAW_LOT_NO' where id='$LOT_ID'";
$Stmtup=$iconn->query($Sqlup);
if($Stmtup){
    legacyAuditLogUpdateById('inputmaterial_details', 'id', $LOT_ID, $oldRow, array('LOT_NO' => $RAW_LOT_NO), array(), 'Accounts');
}
echo '<br>';
}






?>
