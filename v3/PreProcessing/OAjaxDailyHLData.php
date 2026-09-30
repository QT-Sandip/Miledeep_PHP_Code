<?php
session_start();

include "../includes/config.php";
include "../includes/Globals.php";
/*$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];*/

 $FROM_DATE=$_POST['FROM_DATE']; 
$TO_DATE=$_POST['TO_DATE'];
$TYPE=$_POST['TYPE'];
$UNIT=$_POST['UNIT'];
$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31  23:59:59';
$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['ACCESS_TYPE'];


 $accessType=$_SESSION['ACCESS_TYPE'];



if($TYPE ==2){

 /*$sql="SELECT DISTINCT(PURCHASE_DATE) FROM `inputmaterial_details`
 where PURCHASE_DATE  BETWEEN '$date_start' AND '$date_end' ORDER BY PURCHASE_DATE DESC";*/
 
   $sql="SELECT DISTINCT(PURCHASE_DATE) FROM `inputmaterial_details`
 where PURCHASE_DATE  BETWEEN '$FROM_DATE' AND '$TO_DATE' ORDER BY PURCHASE_DATE DESC"; 
$stmt=$iconn->query($sql); 
}

$content .= '<table id="example1" class="table table-bordered" >


			<thead>

                  <th align="CENTER">ID</th>
                  <th align="CENTER">Grading Date</th>
				  <th align="CENTER">HON Weight</th>
				  <th align="CENTER">HL Weight</th>
				  <th align="CENTER">Avg Yield</th>
				  <th align="CENTER">Tools</th>
                </thead>
                <tbody>';

	$PURCHASE_AMOUNT=0;
	$sno=1;

	while($row=$stmt->fetch_array()){

$date_start=$row['PURCHASE_DATE'];
$date_end=$row['PURCHASE_DATE'].' 23:59:59';

  $sql="SELECT * FROM `PPC_GRADING_HL` where SUBMISSION_DATE
 BETWEEN '$date_start' AND '$date_end' ORDER BY SUBMISSION_DATE DESC"; 
$StmtPpcHL=$iconn->query($sql); 

$PURCHASE_DATE = getFormatedDate($row['PURCHASE_DATE']);
 $IDS=array();
 $AryMixedLots=array();
 $HlWeight=0;

while($RHlGrd=$StmtPpcHL->fetch_array()){

	// print_r( $RHlGrd);
	 $IsMix=$RHlGrd['IsMix'];
	$MixIds=$RHlGrd['MixIds'];
	 $LOT_ID=$RHlGrd['LOT_ID'];
	$json=$RHlGrd['HOSO_W_DATA'];


	if($IsMix ==1){
		$MixIds=explode('_',$MixIds);
		foreach($MixIds as $LOT_ID){
		 $IDS[]=$LOT_ID;
		}
	 }else{
		 $IDS[]=$LOT_ID;
	 }




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
	 }
	 }


	$IDS=array_unique($IDS);
	 $IDS = implode(',', $IDS);


	 if($IDS !=''){

	  $sql_LOTS="SELECT SUM(WEIGHT_IN_KGS) as PURCHASE_WEIGHT  FROM `inputmaterial_details`
	  where id in ($IDS)";
	  $stmt_LOTS=$iconn->query($sql_LOTS);
	  $RowPurWgt=$stmt_LOTS->fetch_array();
	  $PURCHASE_WEIGHT=$RowPurWgt['PURCHASE_WEIGHT'];
	 }else{
		$PURCHASE_WEIGHT=0;
	 }

	 $YIELD=round(($HlWeight/$PURCHASE_WEIGHT)*100,2);


				$content .= '<tr>

                <td  align="CENTER" width="2%"><font size="'.$text.'">'.$sno.'</font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$PURCHASE_DATE.'</B></font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.round($PURCHASE_WEIGHT,2).'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.round($HlWeight,2).'</font></td>

				<td  align="CENTER" ><font size="'.$text.'">'.$YIELD.'</font></td>

                ';

                $content.=  '<td>
                <a target="_blank" href="/Accounts/GradingReportPrint.php?from_date='.$row['PURCHASE_DATE'] .'&to_date='.$row['PURCHASE_DATE'].'"
                 class="btn btn-primary" >View</button></td>


		</tr>';
$sno++;

	}
	$content.='</tbody></table>'	;

echo $content;





	?>
