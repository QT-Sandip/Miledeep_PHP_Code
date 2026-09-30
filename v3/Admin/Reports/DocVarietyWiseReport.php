<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
$Exporter=$_GET['Exporter']; 
   
if($Exporter==''){
	$Query='';
}
else{
if($Exporter==1){
	$Query='and SPD.ExporterID!=5';
}
	else{
	
	$Query=' and SPD.ExporterID='.$Exporter;
	}
	 $SqlExporter="SELECT * FROM $TblAdminExporterDetails where ExpID=$Exporter"; 
				  $StmtExporter=$iconn->query($SqlExporter);

$RowExporter= $StmtExporter->fetch_array();	
			 $ExporterName=$RowExporter['ExpName'];
	  
}

$file_name='Variety Wise Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	 $SqlPo="SELECT * FROM `$ScrmCustomInvoices` as SCI 
	 LEFT JOIN $TblScrmPIData as SPD on SPD.PoID=SCI.PiID
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID

LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
LEFT JOIN $TblScrmCountriesList as SCL on SCL.id=SPD.DestinationCountry	 
	WHERE  CInvDate BETWEEN '$from_date' and '$date_end' $Query  order by CInvNo Asc,CInvDate Asc";
			  $StmtPo=$iconn->query($SqlPo);
	

	
			 
			  

	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=11;
			  $width_s=8;
			  $width_w=$width+5;
              
			    $content .= '
				<h3 align="center">'.$ExporterName.'</h3>
			  <h3 align="center"> Variety Wise Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="5">
			   <tr>  

           		

                <th  align="center"  width="3%"><B>S No.</B></th>  
				<th  align="center" width="'.$width_s.'%" ><B>Inv No</B></th>
				<th  align="center"  width="'.$width_s.'%"><B>Inv Date</B></th>			
				<th  align="center"  width="35%"><B> Buyer Name</B></th>	
                <th  align="center"  width="'.$width.'%" <B>Unit Code </B></th>
                <th  align="center"  width="'.$width.'%" <B>Country Name </B></th>
                <th  align="center"  width="'.$width.'%" <B>Variety Name </B></th>
                <th  align="center"  width="'.$width.'%" <B>Qty in CBs </B></th>
			    <th  align="center"  width="'.$width.'%"><B>Net Weight(KGS)</B></th>			

				<!--<th  align="center"  width="'.$width_s.'%"><B> Total Inv Value in US$</B></th>-->					   
				<th  align="center"  width="'.$width.'%"><B> Value in US$</B></th>					   
				<th  align="center"  width="'.$width.'%"><B> Value in INR ₹</B></th>			
			<!--	<th  align="center"  width="'.$width.'%"><B>Net Weight(LBS)</B></th>	-->		
                
    
               </tr> 
			   
			  '; 
			  
	
		//	
$TotalFOBUS=0;
$TotalFOB=0;
$GrandTotalDiscount=0;
$TotalNetWgtKgs=0;
$TotalNetWgtLbs=0;

		while($RowPo = $StmtPo->fetch_array()){
			$ProcessorID=$RowPo['ProcessorID'];
			$Discount=$RowPo['Discount'];
			$PiID=$RowPo['PiID'];
			$FrieghtCharge=$RowPo['FrieghtCharge'];
			 $DestinationCountryId=$RowPo['DestinationCountry']; 
			 $ExchangeRate=$RowPo['ExchangeRate'];
			$CInvCode=$RowPo['CInvCode'];
			$IGST=$RowPo['IGST'];
			$ShippingTerms=$RowPo['ShippingTerms'];
			$CInvCode=$RowPo['CInvCode'];
			$DestinationCountry=$RowPo['name'];
			
			if($CInvCode==16052900){
				$POType='Cooked';
				
			}

else if($CInvCode==03061720){
				$POType='Raw';
				
			}
else{
	$POType='Raw & Cooked';
}



			
			
			$SqlProcessor="SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$ProcessorID'";
			$StmtProcessor=$iconn->query($SqlProcessor);
			$RowProcessor=$StmtProcessor->fetch_array();
			
			/********uma********/
			
						 $content .= 
					
					'<tr>
				 <th  align="center" width="3%">'.$S_NO.'</th>
				 <th  align="center" width="'.$width_s.'%">'.$RowPo['CInvNo'].'</th>
				 <th  align="center" width="'.$width_s.'%">'.date('d-m-Y',strtotime($RowPo['CInvDate'])).'</th>
				 <th  align="left" width="35%">'.$RowPo['ConsigneeName'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowProcessor['ExpApprovalNo'].'</th>
				 <th  align="center" width="'.$width.'%" >'.strtoupper($DestinationCountry).'</th>';
			
			
			
			
			
			$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
			 $SqlTotal=" sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs"; 
		
			/*********ma *******/
			
			

			 $SqlVariety="SELECT distinct(VarietyID) FROM `$ScrmCIGradeData`  WHERE InvId='$PiID'"; 
		$Stmtvariety=$iconn->query($SqlVariety);
				$VarietyCount=mysqli_num_rows($Stmtvariety);

		   while($RowVariety=$Stmtvariety->fetch_array()){
			 $VarietyID= $RowVariety['VarietyID'];
			
			$SqlGradeData="SELECT ShortName,$SqlTotal FROM `$ScrmCIGradeData` SPDT LEFT join 
			$ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID LEFT join 
			$TblAdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID 
			
			WHERE InvId='$PiID' and PIL.VarietyID='$VarietyID'"; 
			//$StmtGradeData=$iconn->query($SqlGradeData);
			$AryPrice = array();
			//$AryPriceKgs = array();

			/*$AryPriceLbs[]='';
			$AryPriceKgs[]='';*/
						/*while($RowGradeData=$StmtGradeData->fetch_array()){

$AryPrice[]=$RowGradeData;
		//$AryPriceKgs[]=$RowGradeData['PriceKgs'];
						}*/
								//print_r($AryPriceLbs); 	
							$StmtGradeData=$iconn->query($SqlGradeData);
		
		$RowGradeData=$StmtGradeData->fetch_array();
		if( $RowPo['QtyUnit']=='KGS'){				
				$Price = $RowGradeData['PriceKgs'];	
			}else{
				
				$Price = $RowGradeData['PriceLbs'];
			}
		$PriceLbs=$RowGradeData['PriceLbs'];
		$PriceKgs=$RowGradeData['PriceKgs'];
					$StmtGradeData=$iconn->query($SqlGradeData);

				 if($VarietyCount==1){
				$content .='	
			     <th  align="center" width="'.$width.'%" >'.$RowGradeData['ShortName'].'</th>
			     <th  align="center" width="'.$width.'%" >'.$RowGradeData['MCS'].'</th>
				 <th  align="center" width="'.$width.'%">'.number_format($RowGradeData['NetWgtLbs']*0.454,3).'</th>
				<!-- <th width="'.$width.'%" align="center">'.number_format($RowGradeData['PriceLbs'],2).'</th>
			<th width="'.$width.'%" align="center">'.number_format($RowGradeData['PriceKgs']*75.92,2).'</th>
				--> ';

				
						$content .= SingleVarietyPrice($PriceLbs,$PriceKgs);

				 }
				  
			else{	 
			while($RowGradeData=$StmtGradeData->fetch_array()){
			$AryPrice[]=$RowGradeData;
			
			if( $RowPo['QtyUnit']=='KGS'){				
				$Price = $RowGradeData['PriceKgs'];	
			}else{
				
				$Price = $RowGradeData['PriceLbs'];
			}

			$content .=' <tr>
			     <th  align="center" width="3%"></th>
				 <th  align="center" width="'.$width_s.'%"></th>
				 <th  align="center" width="'.$width_s.'%"></th>
				 <th  align="left" width="35%"></th>
				 <th  align="center" width="'.$width.'%" ></th>
				 <th  align="center" width="'.$width.'%" ></th>
				 
			     <th  align="center" width="'.$width.'%" >'.$RowGradeData['ShortName'].'</th>
			     <th  align="center" width="'.$width.'%" >'.$RowGradeData['MCS'].'</th>
				 <th  align="center" width="'.$width.'%">'.number_format($RowGradeData['NetWgtLbs']*0.454,3).'</th>
				 <!-- <th width="'.$width.'%" align="center">'.number_format($RowGradeData['PriceLbs'],2).'</th>
			<th width="'.$width.'%" align="center">'.number_format($RowGradeData['PriceKgs'],2).'</th>
				-->';


				
										$content .= MultipleVarietyPrice($AryPrice);
}}}
				  
			$content .=' 	 </tr>';

 $S_NO++;			 
}


		$content .= '</table>';


	echo $content;	
		
			function MultipleVarietyPrice($AryPrice){

	 global $RowPo,$Discount,$IGST,$ExchangeRate,$FrieghtCharge,$DestinationCountryId,$ShippingTerms,$DestinationCountry;

		foreach($AryPrice as $value){
			//$Price= explode(',', $value['PriceKgs']);
			//echo($Price);
			
			if( $RowPo['QtyUnit']=='KGS'){				
				$Price = $value['PriceKgs'];	
			}else{
				
				$Price = $value['PriceLbs'];
			}

			//echo $Price;exit;
			
			
			
					
			
			$DiscountAmount=($Price*$Discount)/100; 
			
			IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}
				
			
					 						 /**uma(16/01/22)**/

					 if($ShippingTerms=='CIF'){
									$InvPrice= ($TotalDiscount*0.11)/100;
									$TotalDiscount=$TotalDiscount-$InvPrice;
								 }
								 else{
								$TotalDiscount=$TotalDiscount;	 
								 }
								 
							/**uma(16/01/22)**/
					 
					 $DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35; 
					 $DutiesPrice=$DutiesPrice+125;
					 
					 if($DestinationCountryId=='26'||$DestinationCountry=='UAE'||$ShippingTerms=='CNF'){
				
						$FOBValue= ($TotalDiscount-$FrieghtCharge)*$ExchangeRate;
						$FOBValueUS=$TotalDiscount-$FrieghtCharge;
					 }
					 ELSE{
						 
				$FOBValue=	($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate;	 
				$FOBValueUS=	$TotalDiscount-$FrieghtCharge-$DutiesPrice;	 
				
					 }
					 /**uma(16/01/22)**/
					 IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}
			/**uma(16/01/22)**/	
$TotalFOBUS+=$FOBValueUS;
$TotalFOB+=$FOBValue;
$GrandTotalDiscount+=$TotalDiscount;
$TotalNetWgtKgs+=$NetWgtKgs;
$TotalNetWgtLbs+=$NetWgtLbs;
			
			
			
			
			
			

$content .= '
			<th width="'.$width.'%" align="center">'.number_format($TotalDiscount,2).'</th>
			<th width="'.$width.'%" align="center">'.number_format($FOBValue,2).'</th>
			';		}
						 
					
			
			return $content;
	}
	
	
	function SingleVarietyPrice($PriceLbs,$PriceKgs){

//Print_r($AryPriceKgs);
	 global $RowPo,$Discount,$IGST,$ExchangeRate,$FrieghtCharge,$DestinationCountryId,$ShippingTerms,$DestinationCountry;
		
				
			
			if( $RowPo['QtyUnit']=='KGS'){				
				$Price = $PriceKgs;	
			}else{
				
				$Price = $PriceLbs;
			}

			
			
			$DiscountAmount=($Price*$Discount)/100; 
			
			IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}
				
			
					 						 /**uma(16/01/22)**/

					 if($ShippingTerms=='CIF'){
									$InvPrice= ($TotalDiscount*0.11)/100;
									$TotalDiscount=$TotalDiscount-$InvPrice;
								 }
								 else{
								$TotalDiscount=$TotalDiscount;	 
								 }
								 
							/**uma(16/01/22)**/
					 
					 $DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35; 
					 $DutiesPrice=$DutiesPrice+125;
					 
					 if($DestinationCountryId=='26'||$DestinationCountry=='UAE'||$ShippingTerms=='CNF'){
				
						$FOBValue= ($TotalDiscount-$FrieghtCharge)*$ExchangeRate;
						$FOBValueUS=$TotalDiscount-$FrieghtCharge;
					 }
					 ELSE{
						 
				$FOBValue=	($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate;	 
				$FOBValueUS=	$TotalDiscount-$FrieghtCharge-$DutiesPrice;	 
				
					 }
					 /**uma(16/01/22)**/
					 IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}
			/**uma(16/01/22)**/	
$TotalFOBUS+=$FOBValueUS;
$TotalFOB+=$FOBValue;
$GrandTotalDiscount+=$TotalDiscount;
$TotalNetWgtKgs+=$NetWgtKgs;
$TotalNetWgtLbs+=$NetWgtLbs;
			
			$content .= '
			<th width="'.$width.'%" align="center">'.number_format($TotalDiscount,2).'</th>
			<th width="'.$width.'%" align="center">'.number_format($FOBValue,2).'</th>
			';
			
			
			return $content;
	}
	

?>