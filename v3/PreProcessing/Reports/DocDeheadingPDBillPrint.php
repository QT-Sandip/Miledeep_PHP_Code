<style>


th {
  height: 1px;
}
</style>

<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$QueryParms=$_GET['Q']; 
 

 $AccessType=$_SESSION["AccessType"];
 $AccessType=1;

 $Month=$_GET['Month']; 
 $Year=$_GET['Year'];
 $Type=$_GET['Type'];
  $PsID=$_GET['StationID'];
 $EntryType=$_GET['EntryType'];
 $ContractorID=$_GET['ContractorID'];
 
 $table_data=getDS_TABLE($iconn,$Year,$Month,$PsID,$ContractorID,$EntryType);
	
		 
function getDS_TABLE($iconn,$Year,$Month,$PsID,$ContractorID,$EntryType){

global $ContractorID,$Type,$AccessType;
$Query='';
if($Type==1){
	$Query= "month(Date)='$Month'  and year(Date)='$Year'  ";	
 }else {
	$Query= "Date between '$Month'  and '$Year'  "; 
 }	

	if($PsID!=''){
		$Query .= "and PDBD.StationID='$PsID'";
	}	
		

 
$SqlBatchIDs="SELECT count(*) as Count,GROUP_CONCAT(BatchID) as SUBID 
  ,GROUP_CONCAT(ContactorID) as ContactorID FROM `PpcDailyBatchDetails` PDBD left join 
  PpcSubContractorMaster PSCM on PSCM.SubContactorID=PDBD.BatchID where $Query and PDBD.Type='$EntryType' ";
 $StmtBatchIDs=$iconn->query($SqlBatchIDs);
$RowBatchIDs= $StmtBatchIDs->fetch_array();
$ContactorIDs=$RowBatchIDs['ContactorID'];
$SubContactorID=$RowBatchIDs['SUBID']; 
$Count=$RowBatchIDs['Count']; 

if($Count!=0){
 $SqlContractor="SELECT ContractorID,ContrctrName FROM `PpcContractMaster`  WHERE ContractorID in ($ContactorIDs)";
$StmtContractor=$iconn->query($SqlContractor);

	 
 
$i=0;				  
$Whitespacee='<b style="color:white">  jhyg</b>';


	$text=4;
	$text1=2;
	
	$SNO=0;
	$PREV_DATE='';
    $ExporterName=$_SESSION["ClientName"];




$width=100;

if($AccessType!=1){
	
$width_s=7.7;
}else{
	
$width_s=6.25;	
}
$widthh=25;
$size=9;
$size1=10;
$height=1;
$BgClr='LIGHT GREY';






while($RowContractor= $StmtContractor->fetch_array()){
	
$ContractorName=$RowContractor['ContrctrName'];
$ContractorID=$RowContractor['ContractorID'];

//echo $Query; exit;
 $table_data=getP_TABLE($iconn,$Query,$Year,$Month,$ContractorName,$ContractorID,$SubContactorID);
      
     echo $table_data['TABLE'];		
}
}


}


function getP_TABLE($iconn,$Query,$Year,$Month,$ContractorName,$ContractorID,$SubContactorID){
	
	  $SqlDailyWages="SELECT * FROM `PpcDailyBatchDetails` PDBD
LEFT JOIN PpcSubContractorMaster PSM ON PSM.SubContactorID=PDBD.BatchID
LEFT JOIN PpcOptions HSD ON HSD.OptID=PDBD.ShiftID
WHERE  ContactorID=$ContractorID and $Query order by Date asc";		  
//echo $SqlDailyWages;exit;
			 
$StmtDailyWages=$iconn->query($SqlDailyWages);

  $content .= '
			  <table border="1" cellspacing="0" cellpadding="5" align="center" width=100% >
			   
				
				
            <h3 align="left"><b>CONTRACTOR: '.$ContractorName.'</b>  <B align="right">  DATE: '.getFormatedDate($Month).' TO '.getFormatedDate($Year).'</B></h3>

	           <!--<tr width="100%"> 	<th   width="'.$width.'%" ><font size="10" align="left"> <b>CONTRACTOR: '.$ContractorName.'</b></font>   '.$Whitespacee.''.$Whitespacee.' <font size="10" align="right">
			   <B>DATE: '.getFormatedDate($Month).' TO '.getFormatedDate($Year).'</B></font></th> </tr>   -->
				 

				<tr  >  

				<th bgcolor="'.$BgClr.'" align="left"  width="'.$width_s.'%"> <font size="'.$size.'">S.NO  </FONT> </th>   
				<th bgcolor="'.$BgClr.'" align="left"  width="'.$width_s.'%"> <font size="'.$size.'">BATCH</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">SHIFT</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">NO .OF WORKERS</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">DATE</FONT> </th>    
				  
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">COUNT</FONT> </th> 
				
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">VARIETY</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">QTY(Kgs)</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">RATE</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Transport TOTAL</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">MESTRI CHARGES TOTAL</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">BONUS</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">MESTRI DAY TOTAL</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Avg Amount</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Avg Qty</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Cost to company per person</FONT> </th>    
				
				</tr> 
				
				  ';

	
				  $Recordcount=mysqli_num_rows($StmtDailyWages);
$S_NO=1;



$TotalHLWeight='';
$TotalWageAmount='';
 
 
while($RowDailyWages = $StmtDailyWages->fetch_array()){
	
	
	$TransPortTotal='';
$MestriCharges='';
$Bonus='';
$AvgAmount='';
$AvgQuantity='';
$AvgAmountPerPerson='';

$BatchName=$RowDailyWages['SubContractorName']; 		
$BatchID=$RowDailyWages['BatchID']; 				
$ShiftName=$RowDailyWages['Name']; 		
$ShiftID=$RowDailyWages['OptID']; 		
$VarietyType=$RowDailyWages['Type']; 		
$NoOfPersons=$RowDailyWages['Persons']; 
$ProcessingDate=date("d-m-Y", strtotime($RowDailyWages['Date']));
$BreakupID=$RowDailyWages['ID']; 
$Adjustments=$RowDailyWages['Adjustments']; 
$Extras=$RowDailyWages['Extras']; 
$AutoCharge=$RowDailyWages['AutoCharge']; 
$MestriComissions=$RowDailyWages['Comissions']; 

	$AryRawData=getBreakUpDetails($RowDailyWages);
	$Item=$AryRawData[0];

	$RmLotNumber=$Item['LotNumber'];	
	$TransPortTotal=$Item['TransPortTotal'];
	$MestriCharges=$Item['MestriCharges'];
	$Bonus=$Item['Bonus'];
	$AvgDayPrice=$Item['AvgDayPrice'];
	$AvgDayHlKgs=$Item['AvgDayHlKgs'];	
	$AvgDayCostToCmny=$Item['AvgDayCostToCmny'];
	$TotalMastriCharges=$Item['OtherCharges'];

$content .='<tr nobr="true">
				<th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$BatchName.'</font></th> 
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$ShiftName.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$NoOfPersons.'</font></th>
				 <th  align="center" width="'.$width_s.'%" ><font size="'.$size.'">'.$ProcessingDate.'</font></th>
				  
				 
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th> 
				 <th  align="left" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				 <th style="color:'.$COLOR .'" align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
                 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$TransPortTotal.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$MestriCharges.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$Bonus.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>';
	$content .= '<th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$AvgDayPrice.'</font></th>
				<th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$AvgDayHlKgs.'</font></th>
				<th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$AvgDayCostToCmny.'</font></th>
				
                 </tr>';

$TotalMestriAmount=0;
$TotalHLWeight=0;
$TotalAmountPerDay=0;

foreach($AryRawData as $Item){
	//print_r($Item); exit;
	$RmLotNumber=$Item['LotNumber'];
	$HLQty=$Item['Qty'];
	$PricePerKg=$Item['PricePerKg'];
	$LotCount=$Item['PurchaseCount'];
	$TransPortTotal=$Item['TransPortTotal'];
	$MestriCharges=$Item['MestriCharges'];
	$Bonus=$Item['Bonus'];
	$AvgDayPrice=$Item['AvgDayPrice'];
	$AvgDayHlKgs=$Item['AvgDayHlKgs'];
	$VarietyName=$Item['Variety'];
	$AvgDayCostToCmny=$Item['AvgDayCostToCmny'];
	$TotalAmountPerDay=$Item['Price'];

	 
		$content .='<tr nobr="true">
				<th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$S_NO++.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th> 
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				 <th  align="center" width="'.$width_s.'%" ><font size="'.$size.'"></font></th>
				  
				 
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'. $LotCount .' </font></th> 
				 <th  align="left" width="'.$width_s.'%"><font size="'.$size.'">'. $VarietyName .' </font></th>
				 <th style="color:'.$COLOR .'" align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$HLQty.'   </font></th>
                 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.round($PricePerKg,2).'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.round($TotalAmountPerDay,2).'</font></th>';
	$content .= '<th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				<th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				<th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
				
                 </tr>';



$TransPortTotal='';
$MestriCharges='';
$Bonus='';
$AvgAmount='';
$AvgQuantity='';
$AvgAmountPerPerson='';
$TotalHLWeight +=$HLQty; 
$TotalMestriAmount +=$TotalAmountPerDay; 




}


	//$AryRawData=getBreakUpDetails($RowDailyWages);
	$Item=$AryRawData[0];

	$RmLotNumber=$Item['LotNumber'];	
	$TransPortTotal=$Item['TransPortTotal'];
	$MestriCharges=$Item['MestriCharges'];
	$Bonus=$Item['Bonus'];
	$AvgDayPrice=$Item['AvgDayPrice'];
	$AvgDayHlKgs=$Item['AvgDayHlKgs'];	
	$AvgDayCostToCmny=$Item['AvgDayCostToCmny'];
	$TotalMastriCharges=$Item['OtherCharges'];
	
	//$TotalMestriAmount +=$TotalMastriCharges;
	$TotalWageAmount=$TotalMestriAmount+$TransPortTotal+$MestriCharges+$Bonus;

$content .=

    '<tr>

   <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"><B>Total </B></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="2"></font>'.$NoOfPersons.'</th>
   <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"><B>'.round(($TotalHLWeight),2).'Kgs</B></font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2">'.$TransPortTotal.'</font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2">'.$MestriCharges.'</font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2">'.$Bonus.'</font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2"></font>'.$TotalMestriAmount.'</th>
     <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2">'.$TotalBill.'</font></th>

 
  <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"><B>'.round(($TotalWageAmount),2).'</B></font></th>
   
   
   
    </tr>';
     
}
	 $content .= '<br></table>
					  <table border="0" cellspacing="0" cellpadding="5" align="center" >
					  <BR><BR>
   
    <!--  <BR><BR>
   
<tr>
<th  align="left" width="100%"><font size="'.$size1.'">Average Rate Per Kg:<B>'.round(($uperterm/$TotalHLWeight),2).'</B></font></th>

   </tr>
			
   <BR><BR>
  <tr>
<th  align="center" width="'.$widthh.'%"><font size="'.$size1.'"><B>Prepared By</B></font></th>
 <th  align="center" width="'.$widthh.'%"><font size="'.$size1.'"><B>Checked By</B></font></th>
 <th  align="center" width="'.$widthh.'%"><font size="'.$size1.'"><B>Verified By</B></font></th>
 <th  align="center" width="'.$widthh.'%"><font size="'.$size1.'"><B>Approved By</B></font></th>
   </tr>-->
					  
					  </table>

		';
		
	//return $content;
		$Data['TABLE']=$content;
		
	return $Data;
}



function getBreakUpDetails($RowDailyWages){
global $iconn;	


//echo json_encode($RowDailyWages);
$BatchName=$RowDailyWages['SubContractorName']; 		
$BatchID=$RowDailyWages['BatchID']; 				
$ShiftName=$RowDailyWages['Name']; 		
$ShiftID=$RowDailyWages['OptID']; 		
$VarietyType=$RowDailyWages['Type']; 		
$NoOfPersons=$RowDailyWages['Persons']; 
$ProcessingDate=date("d-m-Y", strtotime($RowDailyWages['Date']));
$BreakupID=$RowDailyWages['ID']; 
$Adjustments=$RowDailyWages['Adjustments']; 
$Extras=$RowDailyWages['Extras']; 
$AutoCharge=$RowDailyWages['AutoCharge']; 
$MestriComissions=$RowDailyWages['Comissions']; 


 $TransPortTotal=($AutoCharge*$NoOfPersons)+$Adjustments;
 $MestriCharges=	($MestriComissions*$NoOfPersons)+$Extras;
// $Extras;


if($VarietyType==1){
$LotCount=$LotDetails['RmCount'];			
$VarietyID=1;
}else{
$LotCount=$RowBreakUpDtls['Count'];
}



if($ShiftID ==2 || $ShiftID ==4 || $ShiftID ==5){
$ShiftID =1;	
$ShiftNm =1;	
}
if ($ShiftID ==3){	
$ShiftNm =2;	
}else{
$ShiftNm =1;	
}
//$VarietyID=1;
$AryRawData=getHLWageDetails($VarietyID,$ShiftNm,$BreakupID );

$KgBonus=0;
$TotalBouns=0;
$Price=0;
$TotalHlKgs=0;
foreach($AryRawData as $Item){


  $Today=  date('l',strtotime($ProcessingDate));;

$PricePerKg=$Item['PricePerKg'];
$HlQty=$Item['Qty'];
$Price+=$Item['Price'];
$TotalHlKgs+=$Item['Qty'];

//HL Conditions



}

if($Today=='Sunday' && $ShiftID==3 && $VarietyType==1){
$KgBonus=round($Price,2);	
}

//VA Conditions

if($Today=='Sunday'  && $VarietyType!=1){	
 $KgBonus=($Price/4);
}

if($ShiftID==3 && $VarietyType !=1){
$PersonBonus=	$NoOfPersons*50;
}

$TotalBouns=round($KgBonus+$PersonBonus,2);



$OtherCharges=$TransPortTotal+$MestriCharges+$TotalBouns;
$AvgDayPrice=$Price/$NoOfPersons;
$AvgDayHlKgs=$TotalHlKgs/$NoOfPersons;
$AvgDayCostToCmny=($Price+$OtherCharges)/$NoOfPersons;

$index=count($AryRawData)-1;
$index=0;

$AryRawData[0]['Bonus']=$TotalBouns;
$AryRawData[0]['TransPortTotal']=$TransPortTotal;
$AryRawData[0]['MestriCharges']=$MestriCharges;
$AryRawData[0]['OtherCharges']=$OtherCharges;
$AryRawData[0]['AvgDayPrice']=round($AvgDayPrice,2);
$AryRawData[0]['AvgDayHlKgs']=round($AvgDayHlKgs,2);
$AryRawData[$index]['AvgDayCostToCmny']=round($AvgDayCostToCmny,2);

return $AryRawData;	
}


function getHLWageDetails($VarietyID,$ShiftID,$BreakupID ){
	global $iconn;
//echo $VarietyID; 
	
	if($VarietyID==1){
	$Qry="(SELECT PricePerKg FROM `PpcPeelingPrices` WHERE VarietyID=$VarietyID 
			and  LowerLimit <=RMD.PurchaseCount 
			and UpperLimit >= RMD.PurchaseCount and ShiftID=$ShiftID)";
			
	 $SqlBrkDetailsQry="SELECT RMD.RmLotID,'HL' as ShortName, RMD.LotNumber,RMD.PurchaseCount,sum(BBD.HLQty) as Qty,
	 $Qry as PricePerKg,sum(BBD.HLQty * $Qry) as Price FROM `PpcDailyBatchBreakUpDeatils` 
	 as BBD left JOIN RmLotDetatils as RMD on BBD.LotID=RMD.RmLotID 
				WHERE DailyBatchID=$BreakupID group by RMD.RmLotID 
				ORDER by RMD.PurchaseCount asc ";
	}else{
	$Qry="(SELECT PricePerKg FROM `PpcPeelingPrices` WHERE VarietyID=BBD.VarietyID and 
	LowerLimit <=BBD.Count and UpperLimit >= BBD.Count and ShiftID=$ShiftID) ";
		
		 $SqlBrkDetailsQry="SELECT RMD.RmLotID,RMD.LotNumber,
		 BBD.Count as PurchaseCount,sum(BBD.HLQty) as Qty, ShortName,$Qry as PricePerKg,
 sum(BBD.HLQty * $Qry) as Price FROM `PpcDailyBatchPeelingBreakUpDeatils` as BBD 
left JOIN RmLotDetatils as RMD on BBD.LotID=RMD.RmLotID
left join ScrmPIVarietyList as SPL on SPL.VarietyID=BBD.VarietyID
WHERE DailyBatchID=$BreakupID group by RMD.RmLotID,BBD.Count ORDER by BBD.Count asc";	
	}
//echo	$SqlBrkDetailsQry; exit;
	$StmtBrkDetailsQry=$iconn->query($SqlBrkDetailsQry);
	while($RowLotDtls=$StmtBrkDetailsQry->fetch_array()){
	$RowLotDtls['Bonus']='';
	$RowLotDtls['TransPortTotal']='';
	$RowLotDtls['MestriCharges']='';
	$BreakupCount=mysqli_num_rows($StmtBrkDetailsQry);
	$RowLotDtls['Variety']=$RowLotDtls['ShortName'];
	
	$Data[]=$RowLotDtls;
	}		
	
	return $Data;
}
	


?>
