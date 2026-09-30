<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$Type=$_POST['Type'];

/*$Year=$_POST['Year'];
$Month=$_POST['Month'];*/
$PurchaseStationId=$_POST['PsId'];

$ToDate=$_POST['ToDate'];
$FromDate=$_POST['Fromdate'];

//$Type=1;

$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['AccessType'];

if($PurchaseStationId !=0){
  $Query="and VA.StationID='$PurchaseStationId'";
}else{
    $Query="";
}
 $accessType=$_SESSION['AccessType'];


  /*$sql="SELECT ProcessingDate,
 SUM(SentWeight) as SentWeight,
 SUM(ReceivedWeight) as ReceivedWeight,
 SUM(WtAftrVA) as WtAftrVA, SUM(ReturnWeight) as ReturnWeight FROM `PpcValueAddition` VA
where Year(ProcessingDate)='$Year' and Month(ProcessingDate)='$Month' $Query GROUP BY ProcessingDate order by ProcessingDate DESC
"; */

 $sql="SELECT ProcessingDate,
 SUM(SentWeight) as SentWeight,
 SUM(ReceivedWeight) as ReceivedWeight,
 SUM(WtAftrVA) as WtAftrVA, SUM(ReturnWeight) as ReturnWeight FROM `PpcValueAddition` VA
where ProcessingDate between '$FromDate' and '$ToDate' $Query GROUP BY ProcessingDate order by ProcessingDate DESC
"; 

$stmt=$iconn->query($sql);

$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
          <th >ID</th>
          <th >Processing Date</th>
				  <th >Sent Weight(Kgs)</th>
				  <th >Received Weight(Kgs)</th>
				  <th >Difference Weight(Kgs)</th>
				  <th >Returned Weight(Kgs)</th>
				  <th >VA Weight(Kgs)</th>
				  <th >Yield(%)</th>
				  <th >Tools</th>
      </thead>
    <tbody>';

	$PURCHASE_AMOUNT=0;
	$sno=1;

	while($row=$stmt->fetch_array()){

	

$ProcessingDate = getFormatedDate($row['ProcessingDate']);
 $WeightDifference=round($row['SentWeight']-$row['ReceivedWeight'],2);

if($WeightDifference > 0){
	 $WeightDifference;

		$CLR='#f00';

			 $DIFF='<td  >
			   <!--<div class="bg-active color-palette"  >
				<span class="label"  style="background:'.$CLR.'">'. $WeightDifference.'</span>
				</div>-->
				<div  class=" badge badge-warning font-size-10 "
	style="width:100px;background:'.$CLR.';color:white"
					 >
							 '.$WeightDifference.'
							 </div>
				</td>';
		}else {
			 $DIFF='<td >'.round($WeightDifference,2).'</td>';

		 }


$Yield=$row['WtAftrVA']/($row['SentWeight']-$WeightDifference);
$Yield=round($Yield *100) .'%';

$QueryParms='Month='.$row['ProcessingDate'].'&Year='.$row['ProcessingDate'] .'&PsID='.$PurchaseStationId.'&Type='.$Type;
	$QueryParms=urlEncode64($QueryParms);

				$content .= '<tr>

                <td>'.$sno.'</td>
                <td><B>'.$ProcessingDate.'</B></td>
                <td>'.round($row['SentWeight'],2).'</td>
				        <td>'.round($row['ReceivedWeight'],2).'</td>
               '.$DIFF.'
			          <td>'.round($row['ReturnWeight'],2).'</td>
                <td>'.round($row['WtAftrVA'],2).'</td>

				          <td >'.$Yield.'</td>';

              $content.=  '<td>
				  
			   <a target="_blank" href="Reports/PpcValueAdditionPrint.php?Q='.$QueryParms.'"
						 class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top"
						 title="View" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>
		
				  </td>';
              

	 


	  $content.=' </td></tr>';
$sno++;

	}
	$content.='</tbody></table>'	;

echo $content;

?>
