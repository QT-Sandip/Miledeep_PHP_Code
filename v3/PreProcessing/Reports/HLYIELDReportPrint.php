<style>
table tr td{
	
	    font-size: 11px;
    text-align: left;
}

table tr th{
	
	    font-size: 10px;
    text-align: left;
	 background: white;
  position: sticky;
  top: 0;
}


</style>
<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
  $QueryParms=$_GET['Q']; 

 if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  

  //$date=$_GET['date'];
   $Year=$_GET['Year'];
   $Month=$_GET['Month'];
   $StationID=$_GET['PsID'];
   $Type=$_GET['Type']; 



    $monthName = date("F", mktime(0, 0, 0, $Month, 10));

if($Type==1){
	  $Header='HL Yeild Report-('.$monthName.')';

 }
 else{
 $Header='HL Yeild Report-('.getFormatedDate($Month).' To '.getFormatedDate($Year).')';
	 
 }

if($Type==1){
			$Query= "month(VA.ProcessingDate)='$Month'  and year(VA.ProcessingDate)='$Year' ";
		 }else {
			$Query= "VA.ProcessingDate between '$Month'  and '$Year'"; 
		 }
		 
		 
 
  $content1 .= '
			  			  <h3 align="center">'.$Header.'</h3>';
echo $content1;

$UnitName='';
 
     //$table_data=getP_TABLE($iconn,$Year,$Month,$UnitName,$hl_stmt);	  
     $table_data=getP_TABLE($iconn,$Year,$Month,$UnitName);	  
	  echo $table_data['TABLE'];
	
    
  


$file_name='HL Yeild REPORT -'.$monthName.'.pdf';


//uma 09-0-2023//
$SqlCosting="SELECT * FROM SalesCostingRates WHERE TrackID= (SELECT MAX(TrackID) from SalesCostingRates)";
		$StmtCosting=$iconn->query($SqlCosting);

		while($RowCosting = $StmtCosting->fetch_assoc()){
			$ArrayCostingRates[]=$RowCosting;
		}
function getGradesBup(){
	global $iconn;
  global $text;
  global $text1;
  global $ArrayCostingRates;


	return $ArrayCostingRates;
}
function getGradesData($GradingBrkLotID){
  global $iconn;
  global  $text1;

			$GradQty=0;
			$GradCount=0;
			
			   $SqlGrdBrk="SELECT sum(HLWeight) as HLWeight,HLCount FROM `PpcHLGradingBreakup` 
			   WHERE LotID in ($GradingBrkLotID) GROUP by HLCount ORDER by HLCount ASC";


			$StmtGrdBrk=$iconn->query($SqlGrdBrk);
			while($RowBrk=$StmtGrdBrk->fetch_array()){
				
				$HLCount= $RowBrk['HLCount'];
				$HLWeight= $RowBrk['HLWeight'];
				$AryCounts[]= $HLCount;
				$ArySamples[]= $RowBrk;				
				$GradQty+=$HLWeight;
				$GradCount+=($HLWeight*$HLCount *2.2);				
			}
		



			$ary_lot_grade=array();
			$AryGrades=getGradesBup();


		
		
	foreach($AryGrades as $RowCosting){
				
				 $LowLimit=$RowCosting['LowerLimit'];
			 $UpLimit=$RowCosting['UpperLimit'];
			
				

		foreach($ArySamples  as $RowGrading){
			  $CheckCount=$RowGrading['HLCount'];
			
			if($CheckCount >= $LowLimit  && $CheckCount <= $UpLimit){
			 $HLWeight=$RowGrading['HLWeight'];
			 $HLCount=$RowGrading['HLCount'];
			   $CheckCount;
			  break;
			}else{
			 $HLWeight='0';
			}
		}

		//$content.='<td  align="CENTER" >'. $HLWeight.'</font></td>';

		$sample['GRADE']=$RowCosting['Grade'];
		$sample['WEIGHT']=$HLWeight;
		$sample['COUNT']=$HLCount;
		$ary_lot_grade[]=$sample;
	}

		$ArrayDayGrades[]=$ary_lot_grade;

		$Data['GradCount']=$GradCount;
		$Data['GradQty']=$GradQty;
		$Data['Trows']=$content;
		$Data['TotalData']=$ArrayDayGrades;

return $Data;
}

function getMixedData($IDS,$RowLotsData){
global $iconn;

   $sql_LOTS="SELECT
GROUP_CONCAT(LotNumber SEPARATOR ', ') as LotNumber,
 GROUP_CONCAT(PurchaseCount SEPARATOR ', ') as PurchaseCount,
 AVG(PurchaseCount) as RmAvgCount,
 sum(round(PurchaseWeight,2)) as PurchaseWeight,
(select sum(round(WtAfterDH,2)) from PpcDeheading WHERE  LotID in ($IDS)) as WAVA,
 (select GROUP_CONCAT(CountAfterDH  SEPARATOR ', ')  from PpcDeheading WHERE  LotID in ($IDS)) as CAVA
 from `RmLotDetatils`
WHERE RmLotID in ($IDS)";


$stmt_LOTS=$iconn->query($sql_LOTS);
$ROW_LOTS = $stmt_LOTS->fetch_array();

$LotNumbers=$ROW_LOTS['LotNumber'];
$PurchaseCount=$ROW_LOTS['PurchaseCount'];
$LotWeight=$ROW_LOTS['PurchaseWeight'];
$LotNumbers1=$ROW_LOTS['LotNumber'];
$RowLotsData['WAVA']=$ROW_LOTS['WAVA'];
 $RowLotsData['CAVA']=$ROW_LOTS['CAVA'];
$RowLotsData['RmAvgCount']=$ROW_LOTS['RmAvgCount'];
$RowLotsData['LotNumber']=$LotNumbers;
$RowLotsData['PurchaseCount']=$PurchaseCount;
$RowLotsData['PurchaseWeight']=$LotWeight;
return $RowLotsData;
}
//uma 09-0-2023//


function getP_TABLE($iconn,$Year,$Month,$UnitName){

global $Type,$Query,$hl_stmt;

  /*$SqlLotsData="SELECT IM.PurchaseDate,IM.PurchaseStationID,IM.LotNumber,
IM.AgentID,IM.GraderID,IM.ProductionWeight, IM.PurchaseWeight,IM.PurchaseCount,IM.SupevisorID,IM.RmLotID,
 RUG.UserName as GraderName,RUA.UserName as AgentName, RUS.UserName as SupevisorName,
 PHYD.`LotID`, PHYD.`BillCount`, PHYD.`CheckingCount1`,PHYD.CountNetQty, PHYD.`CheckingCount2`,
 PHYD.`Batch1`,PHYD.`Batch2`,PHYD.`Batch3`,PHYD.`Batch4`,
 PHYD.`Batch5`,PHYD.Batch6,HW.IsMix, HW.MixIDs,
 VA.LotID,VA.WtAfterDH,VA.CountAfterDH,VA.NetCount,
 VA.ProcessingDate,VA.CheckingCount,VA.StationID
 FROM `RmLotDetatils` AS IM
 left join PpcDeheading as VA on IM.RmLotID=VA.LotID

left join RmUsersList as RUG ON RUG.ID=IM.GraderID 
left join RmUsersList as RUA ON RUA.ID=IM.AgentID 
left join RmUsersList as RUS ON RUS.ID=VA.SupervisorID
left join PpcHLYeildData as PHYD ON PHYD.LotID=IM.RmLotID
left join PpcHLGradingDetails as HW ON HW.LotID=IM.RmLotID 

where  $Query order by PurchaseDate ASC,LotNumber ASC"; */

  $SqlLotsData="SELECT IM.PurchaseDate,IM.PurchaseStationID,IM.NetWeight as CountNetQty,IM.LotNumber,
IM.AgentID,IM.GraderID,IM.ProductionWeight, IM.PurchaseWeight,IM.PurchaseCount,IM.SupevisorID,IM.RmLotID,
 
 PHYD.`LotID`, PHYD.`BillCount`, PHYD.`CheckingCount1`, PHYD.`CheckingCount2`,
 PHYD.`Batch1`,PHYD.`Batch2`,PHYD.`Batch3`,PHYD.`Batch4`,
 PHYD.`Batch5`,PHYD.Batch6,HW.IsMix, HW.MixIDs,RUG.UserName as GraderName,RUA.UserName as AgentName
FROM `RmLotDetatils` AS IM
left join PpcDeheading as VA on IM.RmLotID=VA.LotID
left join PpcHLYeildData as PHYD ON PHYD.LotID=IM.RmLotID
left join PpcHLGradingDetails as HW ON HW.LotID=IM.RmLotID 
left join RmUsersList as RUG ON RUG.ID=IM.GraderID 
left join RmUsersList as RUA ON RUA.ID=IM.AgentID 

where  $Query group by  VA.LotID order by PurchaseDate ASC,LotNumber ASC";
$StmtLotsData=$iconn->query($SqlLotsData);
//$RowLotsData = $StmtLotsData->fetch_array();

//echo $SqlLotsData; exit;
	

	$size=40;

	$size1=60;

			  $S_NO=1;
			  $width=97/12;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '

			  
	<table width=100% border="1" cellspacing="0" cellpadding="3">	
			   <h3 align="center"> '.$UnitName.'</h3>

	<tr>

                <th  align="CENTER" > <B>Deheading Date </B></font></th>
                <th  align="CENTER" > <B>RmDate </B></font></th>
                <th  align="CENTER" > <B>Unit</B></font></th>
                <th  align="CENTER" > <B>LotNumber</B></font></th>
                <th  align="CENTER" > <B>AgentName</B></font></th>
                <th  align="CENTER" > <B>GraderName</B></font></th>
                <th  align="CENTER" > <B>Purchase Weight</B></font></th>
              <!--  <th  align="CENTER" > <B>Production Weight</B></font></th> -->
                <th  align="CENTER" > <B>BillCount</B></font></th>
				<th  align="CENTER" > <B>CHECKING COUNT-1</B></font></th>
				<th  align="CENTER" > <B>CHECKING COUNT-2</B></font></th> 
				<th  align="CENTER" > <B>HL.QTY</B></font></th>
				<th  align="CENTER" > <B>Grading QTY</B></font></th>
				<th  align="CENTER" > <B>HL YIELD</B></font></th>
				<th  align="CENTER" > <B>CountNetYield </B></font></th>  
				<th  align="CENTER" > <B>Count Net Qty(Kgs) </B></font></th>  
				<th  align="CENTER" > <B>StandardYield </B></font></th>  
			    <th  align="CENTER" > <B>(+) OR (-) Kgs/Count NetYield%</B></font></th>
			    <th  align="CENTER" > <B>Supervisor</B></font></th>
			    <th  align="CENTER" > <B>PHONE NO</B></font></th>
			    <th  align="CENTER" > <B>BATCH1</B></font></th>
			    <th  align="CENTER" > <B>BATCH2</B></font></th>
			    <th  align="CENTER" > <B>BATCH3</B></font></th>
			    <th  align="CENTER" > <B>BATCH4</B></font></th>
			    <th  align="CENTER" > <B>BATCH5</B></font></th>
			    <th  align="CENTER" > <B>BATCH6</B></font></th>
               </tr>

			  ';

		while($RowLotsData = $StmtLotsData->fetch_array()){
		

//while($RowLotsData=$hl_stmt->fetch_array()){
		
			$RmLotID=$RowLotsData['RmLotID'];
			
			$SqlDeheadingData=" Select  VA.LotID,VA.WtAfterDH,VA.CountAfterDH,VA.NetCount,
 VA.ProcessingDate,VA.CheckingCount,VA.StationID, RUS.UserName as SupevisorName
 from PpcDeheading as VA 
left join RmUsersList as RUS ON RUS.ID=VA.SupervisorID where LotID='$RmLotID'";
//left join RmUsersList as RUS ON RUS.ID=VA.SupervisorID where LotID='25444'";

$StmtDeheadingData=$iconn->query($SqlDeheadingData);

$Count=0;
$Countt=0; 
$HLQty=0;
$CheckingCount2=0;
$CheckingCount2Avg=0;
$NetCountAvg=0;
$HLCountAvg=0;
$NetCount=0;
$HLCount=0;
while($RowDeheadingData=$StmtDeheadingData->fetch_array())
{
	
	 $StationID=$RowDeheadingData['StationID'];
	 $Supevisor[]=$RowDeheadingData['SupevisorName'];
	 $SupevisorName=implode(',',$Supevisor);
	 $ProcessingDate=date("d-m-Y", strtotime($RowDeheadingData['ProcessingDate']));


	 $HLQty +=$RowDeheadingData['WtAfterDH'];


$SqlProcessingStns="SELECT * FROM `PpcVAStations` where StationID=$StationID";
$StmtProcessingStns=$iconn->query($SqlProcessingStns);
$RowProcessingStns= $StmtProcessingStns->fetch_array();
$ProcessingStation[]=$RowProcessingStns['UnitName'];
$DHNetCount=$RowDeheadingData['NetCount'];
$PrcsngStnName=implode(',',$ProcessingStation);	
if($DHNetCount!=''  && $DHNetCount!='NILL'){
  $Countt +=1; 
}
 $CheckingCount2 += $RowDeheadingData['CheckingCount'];
 $NetCount += $RowDeheadingData['NetCount'];
 $HLCount +=$RowDeheadingData['CountAfterDH'];

$Count +=1;

}
 //echo $Count; exit;
$CheckingCount2Avg=$CheckingCount2/$Count; 
$NetCountAvg=$NetCount/$Countt;
$HLCountAvg=$HLCount/$Count;
//echo $HLCountAvg ;exit;

unset($ProcessingStation);
unset($Supevisor);


		 $GradingBrkLotID=$RowLotsData['RmLotID'];
		 $IsMix=$RowLotsData['IsMix'];
		 $MixIDs=$RowLotsData['MixIDs'];
/*
	if($IsMix ==1){
	if(in_array($MixIDs,$AryMixedLots)){
		  continue;
	 }else{
		$AryMixedLots[]= $MixIDs;
		$IDS=str_replace('_' , ',' ,$MixIDs);
		$GradingBrkLotID=str_replace('_' , ',' ,$MixIDs);
		$RowLotsData=getMixedData($IDS,$RowLotsData);
 	 }
	 }
	 
*/	



	 
$GradData=getGradesData($GradingBrkLotID);


	if($IsMix ==1){
	$GradQty='<b style="color: red;">Mixed </b>';
	//$GradQty='<b style="color: red;>Mixed </b>';
	 }else{
	$GradQty=$GradData['GradQty'];	 
	 }

//}
$PurchaseDate=date("d-m-Y", strtotime($RowLotsData['PurchaseDate']));
$StationName=$RowLotsData['PurchaseStationID'];
$LotNumber=$RowLotsData['LotNumber'];
$AgentName=$RowLotsData['AgentName'];
$GraderName=$RowLotsData['GraderName'];
$PurchaseWeight=$RowLotsData['PurchaseWeight'];  // Alter Entry At Lot Creation
$PurchaseWeight=getPurchaseWeight($GradingBrkLotID); // Alter Entry At Lot Creation
$ProductionWeight=$RowLotsData['ProductionWeight'];
$HoNQty=$RowLotsData['PurchaseWeight'];

$BillCount=$RowLotsData['PurchaseCount'];  //# Ramesh Info On SME
$CheckingCount1=$RowLotsData['CheckingCount1'];#no need to calculate average because it is single entry in PpcHLYeildData #uma

 $CountNetQty=$RowLotsData['CountNetQty'];
 //$HeadOnQtyEntered=$RowLotsData['HeadOnQtyEntered'];
$Batch1=$RowLotsData['Batch1'];
$Batch2=$RowLotsData['Batch2'];
$Batch3=$RowLotsData['Batch3'];
$Batch4=$RowLotsData['Batch4'];
$Batch5=$RowLotsData['Batch5'];
$Batch6=$RowLotsData['Batch6'];	
//$HLYeild=ROUND((($HLQty/$ProductionWeight)*100),2).'%';  //ProductionWeight here it holds purchaseweight value
$HLYeild=ROUND((($HLQty/$PurchaseWeight)*100),2).'%';  //ProductionWeight here it holds purchaseweight value

//IF($HLCount!=''){
//IF($HLCountAvg!=''){
IF($BillCount!=''){
 //$SqlYeildsList="SELECT * FROM `RmStandardYields` WHERE Type='HL' AND $HLCount BETWEEN LowerCount AND UpperCount ";
 $SqlYeildsList="SELECT * FROM `RmStandardYields` WHERE Type='HL' AND $BillCount BETWEEN LowerCount AND UpperCount ";
$StmtYeildsList=$iconn->query($SqlYeildsList);
$RowYeildsList = $StmtYeildsList->fetch_array();
}

$SqlPurchaseStations="SELECT * FROM `RmPurchaseStations` where PurchaseStationID=$StationName";
$StmtPurchaseStations=$iconn->query($SqlPurchaseStations);
$RowPurchaseStations= $StmtPurchaseStations->fetch_array();




$StationName=$RowPurchaseStations['StationName'];

$StandardYeild=$RowYeildsList['Yield'];
 
//$Cal1=$NetCount/100;
$Cal1=$NetCountAvg/100;

//$CountNetYeiild=round((($HLQty/$Cal1)-$PurchaseWeight),2);
//$CountNetYeiild=round((($HLQty/$Cal1)-$ProductionWeight),2);//ProductionWeight here it holds purchaseweight value
$CountNetYeiild=round((($HLQty/$Cal1)-$PurchaseWeight),2);//ProductionWeight here it holds purchaseweight value

$content .='<tr>
			 
			<td  align="center" >'.$ProcessingDate.'</td>
			<td  align="center" >'.$PurchaseDate.'</td>
			<td  align="center" >'.$PrcsngStnName.'</td>
		
			<td  align="center" >'.$LotNumber.'</td>
			<td  align="center" >'.$AgentName.'</td>
			<td  align="center" >'.$GraderName.'</td>
			<td  align="center" >'.$PurchaseWeight .' </td>
		<!--	<td  align="center" >'.$ProductionWeight .' </td> -->
			<td  align="center" >'.$BillCount.'</td>
			<td  align="center" >'.$CheckingCount1.'</td>
			<td  align="center" >'.number_format($CheckingCount2Avg,2).'</td>	
			<td  align="center" >'.$HLQty.'</td>
			<td  align="center" >'.$GradQty.'</td>
			<td  align="center" >'.$HLYeild.'</td> 
			
			<td  align="center" >'.$NetCountAvg.'%</td>
			<td  align="center" >'.$CountNetQty.'</td>
			<td  align="center" >'.$StandardYeild.'%</td>
			<td  align="center" >'.$CountNetYeiild.'</td>
			<td  align="center" >'.$SupevisorName.'</td>
		    <td  align="center" ></td>						
		    <td  align="center" >'.$Batch1.'</td>						
		    <td  align="center" >'.$Batch2.'</td>						
		    <td  align="center" >'.$Batch3.'</td>						
		    <td  align="center" >'.$Batch4.'</td>						
		    <td  align="center" >'.$Batch5.'</td>						
		    <td  align="center" >'.$Batch6.'</td>						
	
	 </tr>';

 $S_NO++;
}




		$content .= '</table>';

		
		

		$Data['TOTAL_SENT_WEIGHT']=$TOTAL_SENT_WEIGHT;
		$Data['TABLE']=$content;
		
	return $Data;

}

function getPurchaseWeight($LotID){
global $iconn;

	$SqlPurchaseStations="SELECT sum(PurchaseWeight) as PurchaseWeight from RmLotDetatils WHERE RmReferenceID in 
	(SELECT RmReferenceID FROM `RmLotDetatils`  WHERE RmLotID in ($LotID))";

$SqlPurchaseStations="SELECT sum(PurchaseWeight) as PurchaseWeight from RmLotDetatils WHERE RmLotID in ($LotID)";
	$StmtPurchaseStations=$iconn->query($SqlPurchaseStations);
	$RowPurchaseStations= $StmtPurchaseStations->fetch_array();
	return $RowPurchaseStations['PurchaseWeight'];

}

?>
