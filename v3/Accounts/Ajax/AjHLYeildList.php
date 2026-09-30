<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$Month=$_POST['Month'];
$Year=$_POST['Year'];
$Type=$_POST['Type'];
 $PsID=$_POST['PsId']; 


 $showQuantity=false;
 
 if($PsID!=''){
	  $Query="and Unit='$PsID'";
  }else{
	   $Query="";
  }

$accessType=$_SESSION['AccessType'];
//ROBO
if($Type==1){
			$Query= "month(VA.ProcessingDate)='$Month'  and year(VA.ProcessingDate)='$Year' ";
		 }else {
			$Query= "VA.ProcessingDate between '$Month'  and '$Year'"; 
		 }
  $table_data=getP_TABLE($iconn,$Year,$Month,$UnitName);	  
	  echo $table_data['TABLE'];

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


function getP_TABLE($iconn,$Year,$Month,$UnitName){

global $Type,$Query,$hl_stmt;


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

	$size=40;

	$size1=60;

			  $S_NO=1;
			  $width=97/12;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '

	<table  id="scroll-vertical-datatable" data-paging="false" 
class="table dt-responsive nowrap  ">										
		
<thead class="thead-light">	
			  

	

                 <th> Deheading Date </th>
                <th> RmDate </th>
                <th > Unit</th>
                <th > LotNumber</th>
                <th > AgentName</th>
                <th > GraderName</th>
                <th > Purchase Weight</th>
                <th > BillCount</th>
				<th > Checking Count-1</th>
				<th > Checking Count-2</th> 
				<th > HL.QTY</th>
				<th > Grading QTY</th>
				<th > HL YIELD</th>
				<th > CountNetYield </th>  
				<th > Count Net Qty(Kgs) </th>  
				<th > StandardYield </th>  
			    <th > (+) OR (-) Kgs/Count NetYield%</th>
			    <th > Supervisor</th>
			    <th > PHONE NO</th>
			    <th > BATCH1</th>
			    <th > BATCH2</th>
			    <th > BATCH3</th>
			    <th > BATCH4</th>
			    <th > BATCH5</th>
			    <th > BATCH6</th>
               </thead>

			  ';

		while($RowLotsData = $StmtLotsData->fetch_array()){
		
			$RmLotID=$RowLotsData['RmLotID'];
			
			$SqlDeheadingData=" Select  VA.LotID,VA.WtAfterDH,VA.CountAfterDH,VA.NetCount,
 VA.ProcessingDate,VA.CheckingCount,VA.StationID, RUS.UserName as SupevisorName
 from PpcDeheading as VA 
left join RmUsersList as RUS ON RUS.ID=VA.SupervisorID where LotID='$RmLotID'";

$StmtDeheadingData=$iconn->query($SqlDeheadingData);

$Count=0;
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
  $Count +=1; 
}
 $CheckingCount2 += $RowDeheadingData['CheckingCount'];
 $NetCount += $RowDeheadingData['NetCount'];
 $HLCount +=$RowDeheadingData['CountAfterDH'];



}
 $CheckingCount2Avg=$CheckingCount2/$Count; 
$NetCountAvg=$NetCount/$Count;
$HLCountAvg=$HLCount/$Count;
//echo $HLCountAvg ;exit;

unset($ProcessingStation);
unset($Supevisor);


		 $GradingBrkLotID=$RowLotsData['RmLotID'];
		 $IsMix=$RowLotsData['IsMix'];
		 $MixIDs=$RowLotsData['MixIDs'];



	 
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
$PurchaseWeight=$RowLotsData['ProductionWeight'];  // Alter Entry At Lot Creation
$ProductionWeight=$RowLotsData['PurchaseWeight'];
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

$CountNetYeiild=round((($HLQty/$Cal1)-$PurchaseWeight),2);//ProductionWeight here it holds purchaseweight value

 global $PsID;

 if($PsID ==''){
$IsValid=true;
}else if($PsID=='Gain' && $CountNetYeiild >0){
$IsValid=true;
}else if($PsID !='Gain' && $CountNetYeiild < 0){
$IsValid=true;
}else {
$IsValid=false;	
}

if($IsValid){

$content .='<tr>
			 
			<td >'.$ProcessingDate.'</td>
			<td >'.$PurchaseDate.'</td>
			<td >'.$PrcsngStnName.'</td>
		
			<td >'.$LotNumber.'</td>
			<td >'.$AgentName.'</td>
			<td >'.$GraderName.'</td>
			<td >'.$PurchaseWeight .' </td>
		<!--	<td >'.$ProductionWeight .' </td> -->
			<td >'.$BillCount.'</td>
			<td >'.$CheckingCount1.'</td>
			<td >'.number_format($CheckingCount2Avg,2).'</td>	
			<td >'.$HLQty.'</td>
			<td >'.$GradQty.'</td>
			<td >'.$HLYeild.'</td> 
			
			<td >'.$NetCountAvg.'%</td>
			<td >'.$CountNetQty.'</td>
			<td >'.$StandardYeild.'%</td>
			<td >'.$CountNetYeiild.'</td>
			<td >'.$SupevisorName.'</td>
		    <td ></td>						
		    <td >'.$Batch1.'</td>						
		    <td >'.$Batch2.'</td>						
		    <td >'.$Batch3.'</td>						
		    <td >'.$Batch4.'</td>						
		    <td >'.$Batch5.'</td>						
		    <td >'.$Batch6.'</td>						
	
	 </tr>';

 $S_NO++;
}
}




		$content .= '</table>';

		
		

		$Data['TOTAL_SENT_WEIGHT']=$TOTAL_SENT_WEIGHT;
		$Data['TABLE']=$content;
		
	return $Data;

}



	?>
	