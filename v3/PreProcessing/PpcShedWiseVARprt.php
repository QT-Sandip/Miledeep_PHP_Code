<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";

  //$date=$_GET['date'];
  $from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];
   
   $UNIT=$_GET['U']; 
  $TYPE=$_GET['TYPE'];  // melting =1 NEW =0


 $month_name= date("F", strtotime($from_date));
   $content1 .= '<h3 align="center">Value Addition Report - ('. getFormatedDate($from_date).' To '. getFormatedDate($to_date).') </h3>';
echo $content1;



    $UNIT_NAME='';
     
		$date_end=$to_date.' 23:59:59';

  
        $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$UNIT' ";
		$StmtProStations=$iconn->query($SqlProStations);
        $RowProStations=$StmtProStations->fetch_array();
		$UNIT_NAME=$RowProStations['UNIT_NAME'];  
		
		
	 $SqlPpcVA="SELECT DISTINCT(VARIETY) FROM `PPC_VALUE_ADDITION` where 
	STATION_ID='$UNIT' and PROCESSING_DATE BETWEEN '$from_date' AND '$date_end'  ";
		$StmtPpcVA=$iconn->query($SqlPpcVA);
		
		    

		
      WHILE($RowPpcVA=$StmtPpcVA->fetch_array()){
		  
		  $VarietyName=$RowPpcVA['VARIETY'];
		  
		          $table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME,$VarietyName);
     
    
        
		 echo $table_data['TABLE'];
	  }

  
      
$file_name='Value Addition Report -'.$from_date.'-'.$to_date.'.pdf';

//$pdf->Output($file_name, 'I');
			

//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME,$VarietyName){
global $UNIT,$TYPE,$VarietyName ;
	$date_end=$to_date.' 23:59:59';


				/*<?php		$mon=$_POST['month']?>*/

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
	
		
	 $sql_lots="SELECT DISTINCT
	  imd.id,
	  imd.LOT_NO,
	  imd.ANTI_BIOTIC_STATUS,
	  imd.PURCHASE_DATE,
	  imd.WEIGHT_IN_KGS,
	  VA.COUNT,
	  VA.PROCESSING_DATE,
	  VA.SENT_WEIGHT,
    VA.HL_QTY_RCVD,
    VA.RETURN_WEIGHT,
	  VA.WgtAfterValueAddition,
	  VA.VARIETY ,
	  VA.SHIFT ,
	  VA.STATION_ID ,
	  VA.REMARKS ,
	  VA.PROCESSING_DATE ,
	  VA.IceConsumption ,
	  PS.UNIT_NAME
	  from inputmaterial_details as imd
			 left join PPC_VALUE_ADDITION as VA on imd.id=VA.LOT_ID
			 left join RM_PROCESSING_STATIONS as PS on VA.STATION_ID =PS.ID
			  where  PROCESSING_DATE BETWEEN '$from_date' AND '$date_end'
			 and VA.VARIETY='$VarietyName' $query
			  order by PURCHASE_DATE ASC,LOT_NO ASC,VARIETY ASC";
		


			 $stmt_lots=$iconn->query($sql_lots);
			  $r_lots = $stmt_lots->fetch_array();
			  $stmt_lots=$iconn->query($sql_lots);
				


//


	$content='';


	$size=40;

	$size1=60;


  if($UNIT_NAME==''){
     $UNIT_NAME= $r_lots['UNIT_NAME'];
  }


			  $S_NO=1;
			  $width=120/14;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '
	  <table border="1" cellspacing="0" cellpadding="3">
	  			  			  <h3 align="center"> '.$UNIT_NAME.'</h3>

			   <tr>



                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
				        <th  align="center" width="'.$width.'%" ><B> Lot No </B></th>

                <th  align="center"  width="'.$width.'%" ><B>RM Date </B></th>
              <!--  <th  align="center"  width="'.$width.'%" ><B>VA Date </B></th> -->
                
                <th  align="center" width="'.$width.'%" ><B>Count</B></th>
               <th  align="center" width="'.$width.'%" ><B>HL Qty</B></th>
				       <th  align="center" width="'.$width.'%" ><B>Type Of Product</B></th>

				<th  align="center" width="'.$width.'%" ><B>Recieved Weight</B></th>
				<th  align="center" width="'.$width.'%" ><B>HL Return </B></th>
        <th  align="center" width="'.$width.'%" ><B>Weight After VA </B></th>
        <th  align="center" width="'.$width.'%" ><B>Ice cost (Cans)</B></th>
        <th  align="center" width="'.$width.'%" ><B>Amount</B></th>

				<th  align="center" width="'.$width.'%"><B>Grand Total </B></th>



               </tr>

			  ';

	$TOTAL_SENT_WEIGHT=0;
		//
		while($r_lots = $stmt_lots->fetch_array()){

$IceCanCost=200;

$IceCans=$r_lots['IceConsumption'];
$SENT_WEIGHT=$r_lots['SENT_WEIGHT'];
$RECEIVED_WEIGHT=$r_lots['HL_QTY_RCVD'];
$WgtAfterVa=$r_lots['WgtAfterValueAddition'];
$RETURN_WEIGHT=$r_lots['RETURN_WEIGHT'];
$TOTAL_SENT_WEIGHT+=$SENT_WEIGHT;
$Variety=$r_lots['VARIETY'];
$CountObtained=$r_lots['COUNT'];
$ShedIDD=$r_lots['STATION_ID'];


$IceCost= ($IceCans*$IceCanCost);

$TotalIceCost+=$IceCost;



IF($Variety =='PD'){
	
  $VarietyID=1;
}ELSEIF($Variety=='PDTO'){
	
	
 $VarietyID=2;	
	
}ELSEIF($Variety=='PUD'){
	
	
 $VarietyID=3;	
	
}ELSEIF($Variety=='DeHeading'){
	
	
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

	   

	$yield=round(($WgtAfterVa/($SENT_WEIGHT-$RETURN_WEIGHT))*100,2);

	$yield1=$yield.'%';
	$yield2=trim(str_replace("()", "",$yield1));

	 $PURCHASE_DATE=date("d-m-Y", strtotime($r_lots['PURCHASE_DATE']));
	 $PROCESSING_DATE=date("d-m-Y", strtotime($r_lots['PROCESSING_DATE']));
	$r_lots['ANTI_BIOTIC_STATUS'];


		$COUNT=$r_lots['EXP_COUNT'];

		//$STANDARD_YIELD=getStandardYeild($iconn,$COUNT);

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
				<!-- <th  align="center" width="'.$width.'%">'.$PROCESSING_DATE.'</th> -->
				 
                 <th  align="center" width="'.$width.'%">'.$r_lots['COUNT'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['SENT_WEIGHT'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['VARIETY'].'</th>';
if($SENT_WEIGHT!=$RECEIVED_WEIGHT){

			$content .=	 '<th style="color:red" align="center" width="'.$width.'%">'.$r_lots['HL_QTY_RCVD'].'</th>';
}
else	{
				$content .=	 '<th  align="center" width="'.$width.'%">'.$r_lots['HL_QTY_RCVD'].'</th>';

}			
				$content .= '<th  align="center" width="'.$width.'%">'.$RETURN_WEIGHT .' </th>
         <th  align="center" width="'.$width.'%">'. $WgtAfterVa .' </th>
         <th  align="center" width="'.$width.'%">'. $IceCost .'/'.$IceCans.'</th>
         <th  align="center" width="'.$width.'%">'. $Payment .'</th>
				  <th  align="center" width="'.$width.'%"></th>

				 </tr>';

 $S_NO++;
}
$GrandTotal= $TotalIceCost+ $TotalPayment;
      $content .=

    '<tr>

   <th  align="center" width="50"><B>Total </B></th>
   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>

    <th  align="center" width=""><B>'. $TOTAL_SENT_WEIGHT.'</B></th>
	 			   <th  align="center" ></th>
               <th  align="center" ></th>
			  
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			  
			   <th  align="center" >'.$TotalIceCost.'</th>
			   <th  align="center" >'.$TotalPayment.'</th>
			   	 <th  align="center" >'.$GrandTotal.'</th>		   

   </tr>';

		$content .= '</table>';



		$Data['TOTAL_SENT_WEIGHT']=$TOTAL_SENT_WEIGHT;
		$Data['TABLE']=$content;
		
	return $Data;

}

?>
