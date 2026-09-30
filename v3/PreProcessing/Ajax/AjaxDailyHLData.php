<?php
session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$Type=$_POST['Type'];

$Year=$_POST['Year'];
$Month=$_POST['Month'];
$PurchaseStationId=$_POST['PsId'];


$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['ACCESS_TYPE'];


 $accessType=$_SESSION['ACCESS_TYPE'];



if($Type ==2){

 
   $sql="SELECT DISTINCT(PurchaseDate) FROM `RmLotDetatils`
 where Year(PurchaseDate)='$Year' and Month(PurchaseDate)='$Month'   ORDER BY PurchaseDate DESC"; 
$stmt=$iconn->query($sql); 
}

$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">										
		
<thead class="thead-light">

                  <th>ID</th>
                  <th>Grading Date</th>
				  <th>Hon Weight(Kgs)</th>
				  <th>HL Weight(Kgs)</th>
				  <th>Grading Weight(Kgs)</th>
				  <th>Yield(%)</th>
				  <th>Actions</th>
                </thead>
                <tbody>';

	$PURCHASE_AMOUNT=0;
	$sno=1;

	while($row=$stmt->fetch_array()){

$date_start=$row['PurchaseDate'];
$date_end=$row['PurchaseDate'].' 23:59:59';

  $sql="SELECT * FROM `PpcHLGradingDetails` PHD
  left join PpcHLGradingBreakup PGB on PGB.LotID=PHD.LotID
  where GradingDate
 BETWEEN '$date_start' AND '$date_end' ORDER BY GradingDate DESC";  
 
$StmtPpcHL=$iconn->query($sql); 

$PurchaseDate = getFormatedDate($row['PurchaseDate']);
 $IDS=array();
 //$AryMixedLots=array();
 $GradingWeight=0;
 

while($RHlGrd=$StmtPpcHL->fetch_array()){

	// print_r( $RHlGrd);
	 
	 $IsMix=$RHlGrd['IsMix'];
	$MixIds=$RHlGrd['MixIDs'];
	 $LOT_ID=$RHlGrd['LotID'];
	//$json=$RHlGrd['GradingData'];


	if($IsMix ==1){
		$MixIds=explode('_',$MixIds);
		foreach($MixIds as $LOT_ID){
		 $IDS[]=$LOT_ID;
		}
	 }else{
		 $IDS[]=$LOT_ID;
	 }

 $GradingWeight+=$RHlGrd['HLWeight'];
 

/*
	if($IsMix ==1){

	 if(in_array($MixIds,$AryMixedLots)){
		  continue;
	 }else{
		$AryMixedLots[]= $MixIds;
		$data_hl=json_decode($json,true);
	 foreach($data_hl as $value){
		 $HlWeight+=$value['NetWeight'];
	 }
	 }


	 }else{
		$data_hl=json_decode($json,true);
	 foreach($data_hl as $value){
		 $HlWeight+=$value['NetWeight'];
	 }
	 }*/
	 }


	$IDS=array_unique($IDS);
	 $IDS = implode(',', $IDS);


	 if($IDS !=''){

	 /* $sql_LOTS="SELECT SUM(PurchaseWeight) as PurchaseWeight  FROM `RmLotDetatils`
	  where RmLotID  in ($IDS)";*/
	  
	  $sql_LOTS="SELECT SUM(PurchaseWeight) as PurchaseWeight , (select sum(WtAfterDH) from PpcDeheading PDH WHERE PDH.LotID IN ($IDS) )as WtAfterDH FROM `RmLotDetatils` as RMD where RmLotID IN ($IDS) and LotStatus!=2";
	  $stmt_LOTS=$iconn->query($sql_LOTS);
	  $RowPurWgt=$stmt_LOTS->fetch_array();
	  $PurchaseWeight=$RowPurWgt['PurchaseWeight'];
	  $WtAfterDH=$RowPurWgt['WtAfterDH'];
	 }else{
		$PurchaseWeight=0;
		$WtAfterDH=0;
	 }

	 $YIELD=round(($GradingWeight/$PurchaseWeight)*100,2);
$QueryParms='Month='.$row['PurchaseDate'].'&Year='.$row['PurchaseDate'].'&Type='.$Type;
	$QueryParms=urlEncode64($QueryParms);


				$content .= '<tr>

                <td>'.$sno.'</td>
                <td><B>'.$PurchaseDate.'</B></td>
                <td>'.round($PurchaseWeight,2).'</td>
				<td>'.round($WtAfterDH,2).'</td>
				<td>'.round($GradingWeight,2).'</td>

				<td>'.$YIELD.'</td>

                ';

                $content.=  '<td>
               

<a  target="_blank"  href="Reports/GradingReportPrint.php?Q='.$QueryParms.'"
						 class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top"
						 title="View" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>


		</tr>';
$sno++;

	}
	$content.='</tbody></table>'	;

echo $content;





	?>
