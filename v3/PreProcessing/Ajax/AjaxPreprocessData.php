<?php
//ini_set('display_errors', 1);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");


/*$FROM_DATE=$_POST['FROM_DATE']; 
$TO_DATE=$_POST['TO_DATE'];
$TYPE=$_POST['TYPE'];
$UNIT=$_POST['UNIT'];*/

/*$ToDate=$_POST['ToDate'];
$Fromdate=$_POST['Fromdate'];*/
$Type=$_POST['Type'];

$Year=$_POST['Year'];
$Month=$_POST['Month'];
$PurchaseStationId=$_POST['PsId'];

$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['ACCESS_TYPE'];

if($PurchaseStationId !=0){
  $Query="and VA.StationID='$PurchaseStationId'";
}else{
    $Query="";
}
 $accessType=$_SESSION['ACCESS_TYPE'];


  $sql="SELECT ProcessingDate,
 SUM(HonWeight) as HonWeight,
 SUM(ReceivedWeight) as ReceivedWeight,
 SUM(WtAfterDH) as WtAfterDH, SUM(ReturnWeight) as ReturnWeight FROM `PpcDeheading` VA
where Year(ProcessingDate)='$Year' and Month(ProcessingDate)='$Month' $Query GROUP BY ProcessingDate order by ProcessingDate DESC
"; 
//

//where ProcessingDate BETWEEN '$Fromdate' AND '$ToDate' $Query

/*if($Type==1){
	$Query="and VARIETY ='DeHeading' GROUP BY ProcessingDate order by ProcessingDate DESC ";
}else if($Type==3){
$Query="and VARIETY !='DeHeading' GROUP BY ProcessingDate order by ProcessingDate DESC ";

}

if($Type !=2){

 /*$sql="SELECT ProcessingDate,
 SUM(HonWeight) as HonWeight,
 SUM(ReceivedWeight) as ReceivedWeight,
 SUM(WtAfterDH) as WtAfterDH, SUM(ReturnWeight) as ReturnWeight FROM `PpcDeheading`
where ProcessingDate  BETWEEN '$FROM_DATE' AND '$TO_DATE' $Query";*/

/*$sql="SELECT ProcessingDate,
 SUM(HonWeight) as HonWeight,
 SUM(ReceivedWeight) as ReceivedWeight,
 SUM(WtAfterDH) as WtAfterDH, SUM(ReturnWeight) as ReturnWeight FROM `PpcDeheading`
where Year(ProcessingDate)='$Year' and Month(ProcessingDate)='$Month' $Query";

}*/

//echo $sql; exit;
$stmt=$iconn->query($sql);






$content .= '<table id="key-datatable" 
										class="table dt-responsive wrap w-100">
		

			<thead>
          <th align="CENTER">ID</th>
          <th align="CENTER">Processing Date</th>
				  <th align="CENTER">Sent Weight</th>
				  <th align="CENTER">Received Weight</th>
				  <th align="CENTER">Difference Weight</th>
				  <th align="CENTER">Returned Weight</th>
				  <th align="CENTER">HL Weight</th>
				  <th align="CENTER">Yield</th>
				  <th align="CENTER">Tools</th>
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

			 $DIFF='<td align="CENTER" width="10%">
			   <div class="bg-active color-palette" align="center" >
				<span class="label" align="center" style="background:'.$CLR.'">'. $WeightDifference.'</span>
				</div>
				</td>';
		}else {
			 $DIFF='<td  align="CENTER" width="10%"><font size="'.$text.'">'.round($WeightDifference,2).'</font></td>';

		 }


$Yield=$row['WtAfterDH']/($row['HonWeight']-$WeightDifference);
$Yield=round($Yield *100) .'%';
//$Type=1;
$QueryParms='Month='.$row['ProcessingDate'].'&Year='.$row['ProcessingDate'] .'&PsID='.$PurchaseStationId.'&Type='.$Type;
	$QueryParms=urlEncode64($QueryParms);
	
				$content .= '<tr>

                <td  align="CENTER" width="2%"><font size="'.$text.'">'.$sno.'</font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$ProcessingDate.'</B></font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.round($row['HonWeight'],2).'</font></td>
				        <td  align="CENTER" ><font size="'.$text.'">'.round($row['ReceivedWeight'],2).'</font></td>
               '.$DIFF.'
			          <td  align="CENTER" ><font size="'.$text.'">'.round($row['ReturnWeight'],2).'</font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.round($row['WtAfterDH'],2).'</font></td>

				          <td  align="CENTER" ><font size="'.$text.'">'.$Yield.'</font></td>';

             // if($Type==1){
				 

                //$content.=  '<td> <a target="_blank" href="Reports/DeheadingReportPrint.php?from_date='.$row['ProcessingDate'] .'&to_date='.$row['ProcessingDate'].'"
                $content.=  '<td> <a target="_blank" href="Reports/DeheadingReportPrint.php?Q='.$QueryParms .'"
                 class="btn btn-primary" >View</button></td>

                ';
              /*}else{


               $content.=  '<td> <a target="_blank" href="PpcValueAdditionPrint.php?from_date='.$row['ProcessingDate'] .'&to_date='.$row['ProcessingDate'].'"
                  class="btn btn-primary" >View</button></td>';
              }*/

	 /* $content.='<td align="CENTER" width="10%" >
	  <a  target="_blank" class="btn btn-primary" href="#?id='. $row['id'].'"
	  >Update Soon</a> '; */


	  $content.=' </td></tr>';
$sno++;

	}
	$content.='</tbody></table>'	;

echo $content;

?>
