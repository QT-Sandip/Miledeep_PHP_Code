<?php
//ini_set('display_errors', 1);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$Type=$_POST['Type'];

//$Year=$_POST['Year'];
//$Month=$_POST['Month'];
$PurchaseStationId=$_POST['PsId'];
$ToDate=$_POST['ToDate'];
$FromDate=$_POST['Fromdate'];


$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['ACCESS_TYPE'];

if($PurchaseStationId !=0){
  $Query="and VA.StationID='$PurchaseStationId'";
}else{
    $Query="";
}
 $accessType=$_SESSION['ACCESS_TYPE'];


 /* $sql="SELECT ProcessingDate,
 SUM(HonWeight) as HonWeight,
 SUM(ReceivedWeight) as ReceivedWeight,
 SUM(WtAfterDH) as WtAfterDH, SUM(ReturnWeight) as ReturnWeight FROM `PpcDeheading` VA
where Year(ProcessingDate)='$Year' and Month(ProcessingDate)='$Month' $Query GROUP BY ProcessingDate order by ProcessingDate DESC"; */

   $sql="SELECT ProcessingDate,
 SUM(HonWeight) as HonWeight,
 SUM(ReceivedWeight) as ReceivedWeight,
 SUM(WtAfterDH) as WtAfterDH, SUM(ReturnWeight) as ReturnWeight FROM `PpcDeheading` VA
where ProcessingDate between '$FromDate' and '$ToDate' AND VA.CreatedFrom=0 $Query GROUP BY ProcessingDate order by ProcessingDate DESC"; 

$stmt=$iconn->query($sql);

$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">										
		
<thead class="thead-light">
          
				  <th>S.No</th>
                  <th>DeHeading Date</th>
				  <th>Sent(Kgs)</th>
				  <th>Received(Kgs)</th>
				 <th>Difference(Kgs) </th> 
				 <th>Return(Kgs)</th> 
				 <th>HeadLess(Kgs)</th> 
				  <th>Yield %</th>
				  <th>Actions</th>
      </thead>
    <tbody>';

	$PURCHASE_AMOUNT=0;
	$sno=1;

	while($row=$stmt->fetch_array()){



$ProcessingDate = getFormatedDate($row['ProcessingDate']);
 $WeightDifference=round($row['HonWeight']-$row['ReceivedWeight'],2);

if($WeightDifference > 0){
	 $WeightDifference;

		$CLR='#f00';

			 $DIFF='<td >
			 
			 <div  class=" badge badge-warning font-size-10 "
	style="width:100px;background:'.$CLR.';color:white"
					 >
							 '.$WeightDifference.'
							 </div>	
			   <!--<div class="bg-active color-palette"  >
				<span class="label"  style="background:'.$CLR.'">'. $WeightDifference.'</span>
				</div>-->
				</td>';
		}else {
			 $DIFF='<td  >'.round($WeightDifference,2).'</td>';

		 }


//$Yield=$row['WtAfterDH']/($row['HonWeight']-$WeightDifference);
$Yield=$row['WtAfterDH']/($row['ReceivedWeight']-$row['ReturnWeight']);
$Yield=round($Yield *100) .'%';
//$Type=1;
$QueryParms='Month='.$row['ProcessingDate'].'&Year='.$row['ProcessingDate'] .'&PsID='.$PurchaseStationId.'&Type='.$Type;
	$QueryParms=urlEncode64($QueryParms);
	
				$content .= '<tr>

                <td>'.$sno.'</td>
                <td><B>'.$ProcessingDate.'</B></td>
                <td>'.round($row['HonWeight'],2).'</td>
               <td>'.round($row['ReceivedWeight'],2).'</td>'.$DIFF.'
				<td>'.round($row['ReturnWeight'],2).'</td>
                <td>'.round($row['WtAfterDH'],2).'</td>
				<td>'.$Yield.'</td>';
				$content.=  '<td> <a target="_blank" href="Reports/DeheadingReportPrint.php?Q='.$QueryParms .'"
						 class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top"
						 title="View" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>
	
				 </td></tr>';
$sno++;

	}
	$content.='</tbody></table>'	;

echo $content;

?>
