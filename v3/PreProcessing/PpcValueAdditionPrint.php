<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$Year=$_GET['Year'];
  $Month=$_GET['Month'];
  $StationID=$_GET['PsId'];
  $Type=$_GET['Type'];

$dateObj   = DateTime::createFromFormat('!m', $Month);
$monthName = $dateObj->format('F');

   $content1 .= '<h3 align="center">Value Addition Report - ('.$monthName.') </h3>';
echo $content1;

    $UnitName='';
      if($StationID ==''){
		
      $SqlProStations="SELECT * FROM `PpcVAStations`";
      $StmtProStations=$iconn->query($SqlProStations);

      	while($Row=$StmtProStations->fetch_array()){
          $StationID=$Row['StationID'];
          $UnitName=$Row['UnitName'];
          $table_data=getP_TABLE($iconn,$Year,$Month,$UnitName);
          
		  if($table_data['TOTAL_SENT_WEIGHT']!=0){
			//$pdf->writeHTML($table_data['TABLE']);
			echo $table_data['TABLE'];
			}
        }
      }else{
		  
		  
        $SqlProStations="SELECT * FROM `PpcVAStations` where StationID='$StationID' ";
		
        $StmtProStations=$iconn->query($SqlProStations);
$Row=$StmtProStations->fetch_array();
		
  $UnitName=$Row['UnitName'];      
       $table_data=getP_TABLE($iconn,$Year,$Month,$UnitName);
      //$pdf->writeHTML($table_data['TABLE']);
     echo $table_data['TABLE'];
      }


$file_name='Value Addition Report -'.$Year.'-'.$Month.'.pdf';

//$pdf->Output($file_name, 'I');
			

//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$Year,$Month,$UnitName){
global $StationID,$Type;
	$date_end=$Month.' 23:59:59';


				/*<?php		$mon=$_POST['month']?>*/

if($StationID!=''){
$query="and PS.StationID='$StationID'";
}else{
  $query="";
}
if($Type==1){
$query.="and imd.RmLotID='0'";
}else{
  $query="";
}
	
		
	 $sql_lots="SELECT DISTINCT
	  imd.RmLotID,
	  imd.LotNumber,
	  imd.PurchaseDate,
	  imd.PurchaseWeight,
	  VA.SentCount,
	  VA.ProcessingDate,
	  VA.SentWeight,
    VA.ReceivedWeight,
    VA.ReturnWeight,
	  VA.WtAftrVA,
	 
	  VA.StationID ,
	  VA.Remarks ,
	  VA.ProcessingDate ,
	  VA.IceConsumption ,
	  PS.UnitName,PO.Name as Variety
	  from RmLotDetatils as imd
			 left join PpcValueAddition as VA on imd.RmLotID=VA.LotID
			 left join PpcVAStations as PS on VA.StationID =PS.StationID
			 left join PpcOptions as PO on PO.OptID =VA.VarietyID
			  where  Year(ProcessingDate)='$Year' and  Month(ProcessingDate)='$Month'
			   $query
			  order by PurchaseDate ASC,LotNumber ASC,VarietyID ASC";
		


			 $stmt_lots=$iconn->query($sql_lots);
			  $r_lots = $stmt_lots->fetch_array();
			  $stmt_lots=$iconn->query($sql_lots);
				


//


	$content='';


	$size=40;

	$size1=60;


  if($UnitName==''){
     $UnitName= $r_lots['UnitName'];
  }


			  $S_NO=1;
			  $width=120/14;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '
	  <table border="1" cellspacing="0" cellpadding="3">
	  			  			  <h3 align="center"> '.$UnitName.'</h3>

			   <tr>



                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
				        <th  align="center" width="'.$width.'%" ><B> Lot No </B></th>

                <th  align="center"  width="'.$width.'%" ><B>RM Date </B></th>
              <!--  <th  align="center"  width="'.$width.'%" ><B>VA Date </B></th> -->
                <th  align="center" width="'.$width.'%"><B>Station </B></th>
                <th  align="center" width="'.$width.'%" ><B>SentCount</B></th>
               <th  align="center" width="'.$width.'%" ><B>HL Qty</B></th>
				       <th  align="center" width="'.$width.'%" ><B>Type Of Product</B></th>

				<th  align="center" width="'.$width.'%" ><B>Recieved Weight</B></th>
				<th  align="center" width="'.$width.'%" ><B>HL Return </B></th>
        <th  align="center" width="'.$width.'%" ><B>Weight After VA </B></th>
        <th  align="center" width="'.$width.'%" ><B>Ice cost (Cans)</B></th>
        <th  align="center" width="'.$width.'%" ><B>Amount</B></th>
				<th  align="center" width="'.$width.'%"><B>Yeild </B></th>
				<th  align="center" width="'.$width.'%"><B>Remarks </B></th>



               </tr>

			  ';

	$TOTAL_SENT_WEIGHT=0;
		//
		while($r_lots = $stmt_lots->fetch_array()){

$IceCanCost=200;

$IceCans=$r_lots['IceConsumption'];
$SentWeight=$r_lots['SentWeight'];
$ReceivedWeight=$r_lots['ReceivedWeight'];
$WgtAfterVa=$r_lots['WtAftrVA'];
$ReturnWeight=$r_lots['ReturnWeight'];
$TOTAL_SENT_WEIGHT+=$SentWeight;
$Variety=$r_lots['Variety'];
$CountObtained=$r_lots['SentCount'];
$ShedIDD=$r_lots['StationID'];


$IceCost= ($IceCans*$IceCanCost);

$TotalIceCost+=$IceCost;


/*
IF($Variety =='PD'){
	
  $VarietyID=1;
}ELSEIF($Variety=='PDTO'){
	
	
 $VarietyID=2;	
	
}ELSEIF($Variety=='PUD'){
	
	
 $VarietyID=3;	
	
}ELSEIF($Variety=='DeHeading'){
	
	
 $VarietyID=4;	
	
}*/


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
		
		$Payment=	($PeelingAmount*$r_lots['ReceivedWeight']);
		$TotalPayment+=$Payment;

	   

	$yield=round(($WgtAfterVa/($SentWeight-$ReturnWeight))*100,2);

	$yield1=$yield.'%';
	$yield2=trim(str_replace("()", "",$yield1));

	 $PurchaseDate=date("d-m-Y", strtotime($r_lots['PurchaseDate']));
	 $ProcessingDate=date("d-m-Y", strtotime($r_lots['ProcessingDate']));
	$r_lots['ANTI_BIOTIC_STATUS'];


		$SentCount=$r_lots['EXP_COUNT'];

		//$STANDARD_YIELD=getStandardYeild($iconn,$SentCount);

		if($yield1 < $STANDARD_YIELD ){
			$COLOR='red';
		}else{
			$COLOR='GREEN';
		}



		$ReturnWeight= isset($r_lots['ReturnWeight']) ? $r_lots['ReturnWeight'] : "-";
				    $content .=

					'<tr>
				 <th  align="center" width="'.$width_s.'%">'.$S_NO.'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['LotNumber'].'</th>
				 <th  align="center" width="'.$width.'%">'.$PurchaseDate.'</th>
				<!-- <th  align="center" width="'.$width.'%">'.$ProcessingDate.'</th> -->
				 <th  align="center" width="'.$width.'%" >'.$r_lots['UnitName'].'</th>
         <th  align="center" width="'.$width.'%">'.$r_lots['SentCount'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['SentWeight'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Variety'].'</th>';
if($SentWeight!=$ReceivedWeight){

			$content .=	 '<th style="color:red" align="center" width="'.$width.'%">'.$r_lots['ReceivedWeight'].'</th>';
}
else	{
				$content .=	 '<th  align="center" width="'.$width.'%">'.$r_lots['ReceivedWeight'].'</th>';

}			
				$content .= '<th  align="center" width="'.$width.'%">'.$ReturnWeight .' </th>
         <th  align="center" width="'.$width.'%">'. $WgtAfterVa .' </th>
         <th  align="center" width="'.$width.'%">'. $IceCost .'/'.$IceCans.'</th>
         <th  align="center" width="'.$width.'%">'. $Payment .'</th>

				 <th style="color:'.$COLOR .'" align="center" width="'.$width.'%">'.$yield1.' </th>

				  <th  align="center" width="'.$width.'%">'.$r_lots['Remarks'].'</th>

				 </tr>';

 $S_NO++;
}

      $content .=

    '<tr>

   <th  align="center" width="50"><B>Total </B></th>
   <th  align="center" ></th>
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

   </tr>';

		$content .= '</table>';



		$Data['TOTAL_SENT_WEIGHT']=$TOTAL_SENT_WEIGHT;
		$Data['TABLE']=$content;
		
	return $Data;

}

?>
