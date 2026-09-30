<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$Type=$_POST['Type'];
$SubmissionDate=$_POST['SubmissionDate'];

if($Type=='Update'){
$BaseRates=$_POST['BaseRates'];
$RatesVariation=$_POST['RateVarc'];

for($i=0; $i < count($BaseRates) ; $i++ ){

$AryRates=explode('/',$BaseRates[$i]);
$RateId=(int)$AryRates[0];
$Rate=(int)$AryRates[1];

$oldBaseRate = legacyAuditFetchRowById('RmBaseRates', 'COST_RATE_ID', $RateId, 'Accounts');

     $SqlRmBaseRates="UPDATE RmBaseRates SET `USD`='$Rate' where COST_RATE_ID='$RateId'";
     $StmtRmBaseRates=$iconn->query($SqlRmBaseRates);
if($StmtRmBaseRates){
  legacyAuditLogUpdateById('RmBaseRates', 'COST_RATE_ID', $RateId, $oldBaseRate, array('USD'=>$Rate), array(), 'Accounts');
}
}

for($i=0; $i < count($RatesVariation); $i++ ){

$AryRatesVaric=explode('/',$RatesVariation[$i]);
$RateId=(int)$AryRatesVaric[0];
$Rate=(int)$AryRatesVaric[1];

$oldBaseRateVar = legacyAuditFetchRowById('RmBaseRatesVariations', 'COST_RATE_ID', $RateId, 'Accounts');

$SqlRmBaseRates="UPDATE `RmBaseRatesVariations` SET `USD`='$Rate' where COST_RATE_ID='$RateId'";
$StmtRmBaseRates=$iconn->query($SqlRmBaseRates);
if($StmtRmBaseRates){
  legacyAuditLogUpdateById('RmBaseRatesVariations', 'COST_RATE_ID', $RateId, $oldBaseRateVar, array('USD'=>$Rate), array(), 'Accounts');
}
}
}else{

$StmtRmBaseRates=true;	
}

if($SubmissionDate!=""){
	
	 $SqlPurHis="SELECT COUNT(*) as Total FROM `RmPurchaseRatesHistory` WHERE  RmPriceDate='$SubmissionDate'";
	$StmtPurHis=$iconn->query($SqlPurHis);
	$RowPurHis=$StmtPurHis->fetch_array();
	if($RowPurHis['Total'] > 0){
		$IsRateAval=true;
	}else{
		$IsRateAval=false;
	}
	
}



if($StmtRmBaseRates){
	
$SqlBaseRates="SELECT * FROM RmBaseRates   ORDER by COUNT ASC ";
$StmtBaseRates=$iconn->query($SqlBaseRates);

 while($row=$StmtBaseRates->fetch_array()){
	 $AryBaseRates[]=$row;
	 $AryBaseCounts[]=$row['COUNT'];
 }


$PriceTable='<table id="" class="table table-bordered table-hover"><tbody>';	
	$count=0;	
	$BaseIndex=0;
	
      foreach($AryBaseRates as $RowBaseRates){
		   ++$BaseIndex;
		
		   $BasePrice=$RowBaseRates['USD']; 
		   $BaseCount=$RowBaseRates['COUNT'];
		   $RefBaseCount=$RowBaseRates['COUNT'];
		   $RunningCount=$RowBaseRates['LowerCount']; 		    
		   $LowerCount=$RowBaseRates['LowerCount']; 
		   $UpperCount=$RowBaseRates['UpperCount']; 
		   $PointingCount=$RowBaseRates['PointingCount']; 
		   
		   
		   
	while($RunningCount!=$AryBaseRates[$BaseIndex]['LowerCount'] ){
		

		
		 if($BaseCount == $RunningCount){		 		
			  $AryRow=array();
			  $AryRow['Count']=$BaseCount;
			  $AryRow['Price']=$BasePrice;	
			  $AryRowData[]=$AryRow;			 
		 }else if($RunningCount >= $LowerCount && $UpperCount >= $RunningCount  && $RunningCount < 30){
			   $BaseCount=$RunningCount;		   			   
			   $SqlVariation="SELECT * FROM `RmBaseRatesVariations`  
			   WHERE LOWER_LIMIT  <= $RunningCount and UPPER_LIMIT>=$RunningCount ORDER by LOWER_LIMIT asc LIMIT 1";	  
			   $StmtVariation=$iconn->query($SqlVariation);
		       $num_rows= $StmtVariation->num_rows; 
		       $RowVariation=$StmtVariation->fetch_array();
		       $PriceVariation=$RowVariation['USD'];
			   
			   $Diff=$AryBaseRates[$BaseIndex-1]['UpperCount']-$RunningCount;
			   $Diff=$Diff+1;
			   $RunningPrice=$BasePrice +(($Diff)*$PriceVariation);
			   
			   $AryRow['Count']=$BaseCount;
			   $AryRow['Price']=$RunningPrice;		
			   $AryRowData[]=$AryRow;

		  }else if($RunningCount >= $LowerCount && $UpperCount >= $RunningCount  && $RunningCount < 100){
			   $BaseCount=$RunningCount;		   
			   
			   $SqlVariation="SELECT * FROM `RmBaseRatesVariations`  WHERE LOWER_LIMIT  <= $RunningCount and UPPER_LIMIT>=$RunningCount ORDER by LOWER_LIMIT asc LIMIT 1";	  
			   $StmtVariation=$iconn->query($SqlVariation);
		       $num_rows= $StmtVariation->num_rows; 
		       $RowVariation=$StmtVariation->fetch_array();
		       $PriceVariation=$RowVariation['USD'];
			   
			   
			   $SqlBaseRates="SELECT * FROM RmBaseRates where COUNT='$PointingCount' ";
			   $StmtBaseRates=$iconn->query($SqlBaseRates);
			   $RowBaseRates=$StmtBaseRates->fetch_array();

			   $RunningPrice=$RowBaseRates['USD'] + $PriceVariation;
			   
			   $AryRow['Count']=$BaseCount;
			   $AryRow['Price']=$RunningPrice;		
			   $AryRowData[]=$AryRow;
				
		  }else{
			  
		 $SqlVariation="SELECT * FROM `RmBaseRatesVariations`  WHERE LOWER_LIMIT  <= $RunningCount and UPPER_LIMIT>=$RunningCount ORDER by LOWER_LIMIT asc LIMIT 1";	  
		 $StmtVariation=$iconn->query($SqlVariation);
		 $num_rows= $StmtVariation->num_rows; 
		 $RowVariation=$StmtVariation->fetch_array();
		 $PriceVariation=$RowVariation['USD'];
		
		 if($RunningCount > $RefBaseCount){
			$RunningPrice=$BasePrice -(($RunningCount-$RefBaseCount)*$PriceVariation);
		 }else if($RunningCount <= $RefBaseCount){
			 $RunningPrice=$BasePrice -(($RefBaseCount-$RunningCount)*$PriceVariation);
		 }else{
			 $BaseCount=$RunningCount;
			 $RunningPrice=$BasePrice; 
		 }	


		   $AryRow=array();
		   $AryRow['Count']=$RunningCount;
		   $AryRow['Price']=$RunningPrice;		   
		   $AryRowData[]=$AryRow;
		   }
		   
		   $RunningCount++;
		   if($RunningCount==201){
		   break;
		   }
	}
   }
   
   
   /**************************************Date Printing               **************************/

		//print_r($AryRowData);
		
		if($SubmissionDate!=''){
		 date_default_timezone_set('Asia/Calcutta');
		$CreateDate=date('Y-m-d h:i:s');
		$StmtInsertUpdateHistory = null;
		if(!$IsRateAval){
			$SqlInsertUpdateHistory="INSERT INTO `RmPurchaseRatesHistory`( `UserID`, `RmPriceDate`,`PostedDate`) 
			VALUES ('0','$SubmissionDate','$CreateDate')"; 
			$StmtInsertUpdateHistory=$iconn->query($SqlInsertUpdateHistory);
			if($StmtInsertUpdateHistory){
			  legacyAuditLogInsertById('RmPurchaseRatesHistory', 'ID', $iconn->insert_id, array('UserID'=>'0','RmPriceDate'=>$SubmissionDate), array(), 'Accounts');
			}
			
		}
		
		if($StmtInsertUpdateHistory){
		 $row['Error']=null;
		$row['Msg']='Rates Added Sucessfully';
		echo json_encode($row);
	 }else{
		$row['Error']='Rates Already Exist for Selected Date';
		$row['Msg']='Failed To Add Rates';
		 echo json_encode($row);
	 }
	
	 
	 				$TrackID = $iconn->insert_id;

		}
		
		  $Size=count($AryRowData);
		  $LoopSize=10;
		  
		  for($i=0; $i < $LoopSize ; $i++){ 
		  $index=$i;
		  
		
		
			  $PriceTable.='<tr>';
			  for($j=0; $j < $LoopSize ; $j++){
			
				 $TextCount=  $AryRowData[$index]['Count'];
				$TextPrice= (int)$AryRowData[$index]['Price'];
				
				 if(in_array($AryRowData[$index]['Count'],$AryBaseCounts)){
					 $TextClR="Red";
				 }else{
					 $TextClR="Black";
				 }
						 
			 $PriceTable.='<td align="CENTER"  style="color:'.$TextClR.'"><B>'.  $TextCount .'</B></td>
							<td align="CENTER" style="color:'.$TextClR.'" > '. $TextPrice.'</td>';

			 $index+=$LoopSize;
			 
			 if( $SubmissionDate!='' && $TrackID!=''){

	
	 $SqlInsertPurchaseRates="INSERT INTO `RmPurchaseRates`( `RmCount`, `RmPrice`, `DateID`) 
	VALUES ('$TextCount','$TextPrice','$TrackID')";  
	$StmtInsertPurchaseRates=$iconn->query($SqlInsertPurchaseRates);
	if($StmtInsertPurchaseRates){
	  legacyAuditLogInsertById('RmPurchaseRates', 'ID', $iconn->insert_id, array('RmCount'=>$TextCount,'RmPrice'=>$TextPrice,'DateID'=>$TrackID), array(), 'Accounts');
	}
}
			 } 
			 $PriceTable.='</tr>';	

			 
		 
		 } 
		  
 $PriceTable.='</tbody></table>';

if($SubmissionDate==''){
	
echo  $PriceTable;
}
}

