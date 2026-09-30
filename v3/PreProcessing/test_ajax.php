<?php
session_start();
include "../includes/config.php";

include "../includes/Globals.php";

$STATION=$_POST['STATION'];

    $from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];

  $UNIT=$STATION;
  $TYPE=$_GET['TYPE'];  // melting =1 NEW =0


 $month_name= date("F", strtotime($from_date));
  
  $originalDate1 = $to_date;
$newDate1 = date("d-m-Y", strtotime($originalDate1));

$originalDate2 = $from_date;
$newDate2 = date("d-m-Y", strtotime($originalDate2));

$date=$newDate2. ' TO  ' .$newDate1;

 
 
 
 $UNIT_NAME='';
      if($UNIT ==0){
		
      $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS`";
      $StmtProStations=$iconn->query($SqlProStations);

      	while($Row=$StmtProStations->fetch_array()){
          $UNIT=$Row['ID'];
		            //$UNIT=$Row['UNIT_ID'];

          $UNIT_NAME=$Row['UNIT_NAME'];
          /*$table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
          
		  if($table_data['TOTAL_SENT_WEIGHT']!=0){
			$pdf->writeHTML($table_data['TABLE']);
			}*/
        }
      }else{
		  
		  
        $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$UNIT' ";
		
        $StmtProStations=$iconn->query($SqlProStations);
$Row=$StmtProStations->fetch_array();
		
  $UNIT_NAME=$Row['UNIT_NAME'];      
       /*$table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
      $pdf->writeHTML($table_data['TABLE']);*/
      }
if($UNIT!=0){
$query="and PS.ID='$UNIT'";
}else{
  $query="";
}
if($TYPE==1){
$query.="and imd.id='0'";
}else{
 // $query="";
}
//global $UNIT,$TYPE;
	$date_end=$to_date.' 23:59:59';
	

		
 $sql_lots="SELECT  PROCESSING_DATE,VARIETY,
sum(SENT_WEIGHT) as SENT_WEIGHT,
sum(HL_QTY_RCVD) as HL_QTY_RCVD,
sum(WgtAfterValueAddition) as WgtAfterValueAddition ,
sum(RETURN_WEIGHT) as RETURN_WEIGHT ,
UNIT_NAME
from PPC_VALUE_ADDITION
left JOIN RM_PROCESSING_STATIONS on PPC_VALUE_ADDITION.STATION_ID=RM_PROCESSING_STATIONS.ID
where PROCESSING_DATE BETWEEN '$from_date' AND '$date_end' and VARIETY!='DeHeading' and STATION_ID='$STATION'
GROUP by PROCESSING_DATE,VARIETY order by PROCESSING_DATE ASC
";
	
		


			 $stmt_lots=$iconn->query($sql_lots);
			  $r_lots = $stmt_lots->fetch_array();
			  $stmt_lots=$iconn->query($sql_lots);
				


$content .= '<table id="example1" class="table table-bordered" >


			<thead>








   
								
				<th  align="center"> S No.</th>
                <th  align="center">VA Date</th>
                <th  align="center">Station</th>               
				<th  align="center">Sent Weight</th>
				<th  align="center">Type Of Product</th>
				<th  align="center">Recieved Weight</th>			
				<th  align="center"> HL Return</th>
				<th  align="center">Weight After VA</th>
				<th  align="center">YEILD</th>
                <tbody>
';
												 
	$S_NO=1;						
							


								
 $TOTAL_SENT_WEIGHT=0;
		//
		while($r_lots = $stmt_lots->fetch_array()){




$SENT_WEIGHT=$r_lots['SENT_WEIGHT'];
$RECEIVED_WEIGHT=$r_lots['HL_QTY_RCVD'];
$WgtAfterVa=round($r_lots['WgtAfterValueAddition']);
$RETURN_WEIGHT=$r_lots['RETURN_WEIGHT'];

$TOTAL_SENT_WEIGHT+=$SENT_WEIGHT;

	$yield=round(($WgtAfterVa/($SENT_WEIGHT-$RETURN_WEIGHT))*100,2);

	$yield1=$yield.'%';
	$yield2=trim(str_replace("()", "",$yield1));

	 $PROCESSING_DATE=date("d-m-Y", strtotime($r_lots['PROCESSING_DATE']));
	// $PROCESSING_DATE=date("d-m-Y", strtotime($r_lots['PROCESSING_DATE']));
	$r_lots['ANTI_BIOTIC_STATUS'];


		$COUNT=$r_lots['EXP_COUNT'];

		//$STANDARD_YIELD=getStandardYeild($iconn,$COUNT);

		if($yield1 < $STANDARD_YIELD ){
			$COLOR='red';
		}else{
			$COLOR='GREEN';
		}



		$RETURN_WEIGHT= isset($r_lots['RETURN_WEIGHT']) ? $r_lots['RETURN_WEIGHT'] : "-";
		if($UNIT_NAME==''){
     $UNIT_NAME= $r_lots['UNIT_NAME'];
  }

	
				$content .= '<tr>
	
	
  <td  align="CENTER" ><B>'. $S_NO.'</B></td>
  <td  align="CENTER" > '. $PROCESSING_DATE.'</td>
  <td  align="CENTER"  > '.$r_lots['UNIT_NAME'].'</td>
  <td  align="CENTER"  >'.$r_lots['SENT_WEIGHT'].'</td>
  <td  align="CENTER"  >'.$r_lots['VARIETY'].'</td>
  <td  align="CENTER"  >'.$r_lots['HL_QTY_RCVD'].'</td>
  <td  align="CENTER"  >'.$RETURN_WEIGHT.'</td>
  <td  align="CENTER"  >'.$WgtAfterVa.'</td>
  <td  align="CENTER"  >'.$yield1.'</td>
  </tr>'; 

  $S_NO++;
} 
echo $content.='</tbody></table>';


?>






<script type="text/javascript">

 $('#example1').DataTable();

</script>


<script type="text/javascript" 
src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	 <script type="text/javascript">
			 $("#btnPrint").live("click", function () {


					divToPrint=document.getElementById("example1");
					var date="<?php  echo  $ShowDate ?>";
					var originalTitle = "GRADING DETAILS - "+date;
					var printWindow = window.open('', '', 'height=600,width=1000');

 				 	// printWindow.document.write(date.outerHTML);
 				 	 printWindow.document.write(originalTitle);
					 printWindow.document.write(divToPrint.outerHTML);
					 printWindow.document.close();
					 printWindow.print();
			 });
			 
			 
			 

</script>



</html>
