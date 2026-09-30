<?php
//ini_set('display_errors', 1);

include "../includes/config.php";
include "../includes/Globals.php";
/*$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];*/


$FROM_DATE=$_POST['FROM_DATE']; 
$TO_DATE=$_POST['TO_DATE'];
$TYPE=$_POST['TYPE'];
$UNIT=$_POST['UNIT'];
/*$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31  23:59:59';*/
$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['ACCESS_TYPE'];


 $accessType=$_SESSION['ACCESS_TYPE'];

if($TYPE==1){
	$Query="and VARIETY ='DeHeading' GROUP BY PROCESSING_DATE order by PROCESSING_DATE DESC ";
}else if($TYPE==3){
$Query="and VARIETY !='DeHeading' GROUP BY PROCESSING_DATE order by PROCESSING_DATE DESC ";

}

if($TYPE !=2){

 $sql="SELECT PROCESSING_DATE,
 SUM(SENT_WEIGHT) as SENT_WEIGHT,
 SUM(HL_QTY_RCVD) as RECIEVED_WEIGHT,
 SUM(WgtAfterValueAddition) as WEIGHT_AFT_VA, SUM(RETURN_WEIGHT) as RETURN_WEIGHT FROM `PPC_VALUE_ADDITION`
where PROCESSING_DATE  BETWEEN '$FROM_DATE' AND '$TO_DATE' $Query";

}

//echo $sql; exit;
$stmt=$iconn->query($sql);






$content .= '<table id="example1" class="table table-bordered" >


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



$PURCHASE_DATE = getFormatedDate($row['PROCESSING_DATE']);
 $DIFF_WEIGHT=round($row['SENT_WEIGHT']-$row['RECIEVED_WEIGHT'],2);

if($DIFF_WEIGHT > 0){
	 $DIFF_WEIGHT;

		$CLR='#f00';

			 $DIFF='<td align="CENTER" width="10%">
			   <div class="bg-active color-palette" align="center" >
				<span class="label" align="center" style="background:'.$CLR.'">'. $DIFF_WEIGHT.'</span>
				</div>
				</td>';
		}else {
			 $DIFF='<td  align="CENTER" width="10%"><font size="'.$text.'">'.round($DIFF_WEIGHT,2).'</font></td>';

		 }


$YIELD=$row['WEIGHT_AFT_VA']/($row['SENT_WEIGHT']-$DIFF_WEIGHT);
$YIELD=round($YIELD *100) .'%';


				$content .= '<tr>

                <td  align="CENTER" width="2%"><font size="'.$text.'">'.$sno.'</font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$PURCHASE_DATE.'</B></font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.round($row['SENT_WEIGHT'],2).'</font></td>
				        <td  align="CENTER" ><font size="'.$text.'">'.round($row['RECIEVED_WEIGHT'],2).'</font></td>
               '.$DIFF.'
			          <td  align="CENTER" ><font size="'.$text.'">'.round($row['RETURN_WEIGHT'],2).'</font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.round($row['WEIGHT_AFT_VA'],2).'</font></td>

				          <td  align="CENTER" ><font size="'.$text.'">'.$YIELD.'</font></td>';

              if($TYPE==1){
                $content.=  '<td> <a target="_blank" href="DeheadingReportPrint.php?from_date='.$row['PROCESSING_DATE'] .'&to_date='.$row['PROCESSING_DATE'].'"
                 class="btn btn-primary" >View</button></td>

                ';
              }else{


                $content.=  '<td> <a target="_blank" href="PpcValueAdditionPrint.php?from_date='.$row['PROCESSING_DATE'] .'&to_date='.$row['PROCESSING_DATE'].'"
                  class="btn btn-primary" >View</button></td>';
              }

	 /* $content.='<td align="CENTER" width="10%" >
	  <a  target="_blank" class="btn btn-primary" href="#?id='. $row['id'].'"
	  >Update Soon</a> '; */


	  $content.=' </td></tr>';
$sno++;

	}
	$content.='</tbody></table>'	;

echo $content;

?>
