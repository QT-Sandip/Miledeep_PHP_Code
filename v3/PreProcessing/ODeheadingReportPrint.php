<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
  //$date=$_GET['date'];
$from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];
  $UNIT=$_GET['U'];


 //$month_name= date("F", strtotime($date));
 $month_name= date("F", strtotime($from_date));
  $content1 .= '
			  			  <h3 align="center"> Deheading Report-('.getFormatedDate($from_date).' To '.getFormatedDate($to_date).')</h3>';
echo $content1;


$UNIT_NAME='';
  if($UNIT ==0){

  $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS`";
  $StmtProStations=$iconn->query($SqlProStations);

  	while($Row=$StmtProStations->fetch_array()){
      $UNIT=$Row['ID'];
      $UNIT_NAME=$Row['UNIT_NAME'];
	  
      $table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
	
	  if($table_data['TOTAL_SENT_WEIGHT']!=0){
      //$pdf->writeHTML($table_data['TABLE']);
	  echo $table_data['TABLE'];
	  }
    }
  }else{
    $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$UNIT' ";
    $StmtProStations=$iconn->query($SqlProStations);
	$Row=$StmtProStations->fetch_array();
	$UNIT_NAME=$Row['UNIT_NAME'];
   $table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
   //$pdf->writeHTML($table_data['TABLE']);
   echo $table_data['TABLE'];
  }





$file_name='DE HEADING REPORT -'.$from_date.'-'.$to_date.'.pdf';

//$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME){

global $UNIT;

if($UNIT !=0){
  $Query="and PS.ID='$UNIT'";
}else{
    $Query="";
}
	$date_end=$to_date.' 23:59:59';


	 $sql_lots="SELECT
	  imd.LOT_NO,
	  imd.ANTI_BIOTIC_STATUS,
	  imd.PURCHASE_DATE,
	  imd.WEIGHT_IN_KGS,
	  VA.COUNT,
	  VA.PROCESSING_DATE,
	  VA.SENT_WEIGHT,
	  VA.WgtAfterValueAddition,
	   VA.CountAfterValueAddition,
	  VA.HL_QTY_RCVD,
	  VA.RETURN_WEIGHT,
	  VA.VARIETY ,
	  VA.SHIFT ,
	  VA.STATION_ID ,
	  VA.REMARKS ,
	  VA.IceConsumption ,
	  PS.UNIT_NAME
	  from inputmaterial_details as imd
			 left join PPC_VALUE_ADDITION as VA on imd.id=VA.LOT_ID
			 left join RM_PROCESSING_STATIONS as PS on VA.STATION_ID =PS.ID
			  where VA.PROCESSING_DATE BETWEEN '$from_date' AND '$date_end'
			  and VA.VARIETY ='DeHeading' $Query
			  order by PURCHASE_DATE ASC,LOT_NO ASC,VARIETY ASC";
			  $stmt_lots=$iconn->query($sql_lots);
			  $r_lots = $stmt_lots->fetch_array();
			  $stmt_lots=$iconn->query($sql_lots);

	$content='';


	$size=40;

	$size1=60;



if($UNIT_NAME==''){
   $UNIT_NAME= $r_lots['UNIT_NAME'];
}

			  $S_NO=1;
			  $width=97/12;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '

			  
	<table border="1" cellspacing="0" cellpadding="3">	
			  			  			  <h3 align="center"> '.$UNIT_NAME.'</h3>

	<tr>

                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
				 <th  align="center" width="'.$width.'%" B>Lot No </B></th>

                <th  align="center"  width="'.$width.'%" ><B>RM Date </B></th>
                <th  align="center"  width="'.$width.'%" ><B>De Heading Date </B></th>





				<th  align="center" width="'.$width.'%" ><B>HON Weight</B></th>
				<th  align="center" width="'.$width.'%" ><B>HON Count</B></th>
				<th  align="center" width="'.$width.'%" ><B>HON Qty Recieved</B></th>
				<th  align="center" width="'.$width.'%" ><B>HON Return </B></th>
				<th  align="center" width="'.$width.'%" ><B>HL Qty </B></th>
				<th  align="center" width="'.$width.'%" ><B>HL Count </B></th>
				<th  align="center" width="'.$width.'%" ><B>Ice Cost(Cans)</B></th>
				<th  align="center" width="'.$width.'%" ><B>Amount</B></th>
				<th  align="center" width="'.$width.'%"><B>S Yeild </B></th>
				<th  align="center" width="'.$width.'%"><B>Yeild </B></th>
				<th  align="center" width="'.$width.'%"><B>Remarks </B></th>



               </tr>

			  ';

$TOTAL_SENT_WEIGHT=0;
		//
		while($r_lots = $stmt_lots->fetch_array()){




$SENT_WEIGHT=$r_lots['SENT_WEIGHT'];
$WgtAfterValueAddition=$r_lots['WgtAfterValueAddition'];
$RECEIVED_WEIGHT=$r_lots['HL_QTY_RCVD'];
$RETURN_WEIGHT=$r_lots['RETURN_WEIGHT'];

//ROBO START

$Variety=$r_lots['VARIETY'];
$CountObtained=$r_lots['COUNT'];
$ShedIDD=$r_lots['SHEDID'];

$IceCanCost=200;

$IceCans=$r_lots['IceConsumption'];
$IceCost= ($IceCans*$IceCanCost);
$TotalIceCost+=$IceCost;


$TOTAL_SENT_WEIGHT+=$SENT_WEIGHT;



IF($Variety=='DeHeading'){
	
	
 $VarietyID=4;	
	
}


IF($ShedIDD == 9 || $ShedIDD == 12 ){
	
  $Location= 0;
}ELSE{
	
	
 $Location=1;	
	
}



 $SqlPeelingPrices="SELECT PricePerKg FROM `PpcPeelingPrices` WHERE 
       LocationID='$Location' AND VarietyID='$VarietyID' AND
	    LowerLimit < '$CountObtained' and UpperLimit >= '$CountObtained'";
       $StmtPeelingPrices=$iconn->query($SqlPeelingPrices);
		$RowPeelingPrices=$StmtPeelingPrices->fetch_array();
	
		$PeelingAmount=$RowPeelingPrices['PricePerKg'];
		
		$Payment=	($PeelingAmount*$r_lots['HL_QTY_RCVD']);
		$TotalPayment+=$Payment;

//ROBO END

	 $yield=round(($WgtAfterValueAddition/($RECEIVED_WEIGHT-$RETURN_WEIGHT))*100,2); 

	$yield1=$yield.'%';
	$yield2=trim(str_replace("()", "",$yield1));

	 $PURCHASE_DATE=date("d-m-Y", strtotime($r_lots['PURCHASE_DATE']));
	 $PROCESSING_DATE=date("d-m-Y", strtotime($r_lots['PROCESSING_DATE']));
	$r_lots['ANTI_BIOTIC_STATUS'];


		 $COUNT=$r_lots['COUNT'];

		 $STANDARD_YIELD=getStandardYeild($iconn,$COUNT,"HL");

		if($yield1 < $STANDARD_YIELD ){
			$COLOR='red';
		}else{
			$COLOR='GREEN';
		}





		$RETURN_WEIGHT= isset($r_lots['RETURN_WEIGHT']) ? $r_lots['RETURN_WEIGHT'] : "-";
				    $content .=

					'<tr>
				 <th  align="center" width="'.$width_s.'%">'.$S_NO.'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['LOT_NO'].'</th>
				 <th  align="center" width="'.$width.'%">'.$PURCHASE_DATE.'</th>
				 <th  align="center" width="'.$width.'%">'.$PROCESSING_DATE.'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['SENT_WEIGHT'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['COUNT'].'</th>



				 <th  align="center" width="'.$width.'%">'.$r_lots['HL_QTY_RCVD'].'</th>
				  <th  align="center" width="'.$width.'%">'.$RETURN_WEIGHT .' </th>
				  <th  align="center" width="'.$width.'%">'.$r_lots['WgtAfterValueAddition'].'</th>
					 <th  align="center" width="'.$width.'%">'.$r_lots['CountAfterValueAddition'].'</th>
					 <th  align="center" width="'.$width.'%">'.$IceCost.'/'.$IceCans.'</th>
					 <th  align="center" width="'.$width.'%">'.$Payment.'</th>
					 <th  align="center" width="'.$width.'%">'.$STANDARD_YIELD.'</th>
				 <th style="color:'.$COLOR .'" align="center" width="'.$width.'%">'.$yield1.' </th>

				  <th  align="center" width="'.$width.'%">'.$r_lots['REMARKS'].'</th>

				 </tr>';

 $S_NO++;
}

$content .=

'<tr>
               <th  align="center" ></th>				 
               				 


<th style="color:'.$COLOR .'" align="center" >Total </th>
<th  align="center" ></th>				 
               <th  align="center" ></th>				 
               
<th  align="center" ><B>'. $TOTAL_SENT_WEIGHT.'</B></th>
			 <th  align="center" ></th>
               <th  align="center" ></th>
               <th  align="center" ></th> 
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" >'.$TotalIceCost.'</th>
			   <th  align="center" >'.$TotalPayment.'</th>
			   <th  align="center" ></th>


</tr>';



		$content .= '</table>';

		
		

		$Data['TOTAL_SENT_WEIGHT']=$TOTAL_SENT_WEIGHT;
		$Data['TABLE']=$content;
		
	return $Data;

}

?>
