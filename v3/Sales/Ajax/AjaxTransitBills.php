<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$Exporter=$_POST['ExporterID'];
$PaymentTermID=$_POST['PaymentTerms'];
 


if($Exporter==''){
	$Query='';
}else{
	if($Exporter==1){
	$Query='and SPD.ExporterID!=5'; 
	}else{	
	$Query=" and SPD.ExporterID in ($Exporter)";
}
	  
}

if($PaymentTermID !=''){ 
	 $Query.=' and  LcStatus='.$PaymentTermID;
}
	

   $sql="SELECT * FROM `ScrmCustomInvoices` as SCI 
LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	    
left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
LEFT JOIN ScrmEirFdaNOtherDetails as SED on SED.PoRefNumber=SCI.PiID
left join ScrmPaymentTerms SPT on SPT.ID=SPD.PaymentTerms
LEFT JOIN ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
WHERE SDB.TransportedDate!='' 
and (SDB.BillDiscountingDate ='' or SDB.BillDiscountingDate is null) 
 $Query 
order by SCI.CInvNo Asc,SCI.CInvDate Asc";  



$StmtPo=$iconn->query($sql);


$content='	<table  id="key-datatable" data-paging="false" 
class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">

      <tr>
					<th><B>S.NO</B></th>
					<th><B>VESSEL NAME</B></th>
                  	 <th    ><B>INVOICE</B></th>	
				   <th    ><B>ORDER NO</B></th>					   
				   <th    ><B>NAME OF THE BUYER</B></th>					   
				   <th    ><B>INVOICE VALUE US$</B></th>						
				   <th    ><B>INR EXCHANGE</B></th>			
				   <th    ><B>PAYMENT TERMS</B></th>			
				   <th    ><B>DUE DATE OF SHIPMENT</B></th>			
                   <th    ><B>REMARKS</B></th>                    		
	  
	  </tr>

    </thead>
	<tbody>';
				

				
$GrandTotalDiscount=0;
 $GrandTotalINRValue=0;

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
			$ShipByDate=$RowPo['ShipByDate'];
			$PaymentTerms=$RowPo['PaymentTerms'];
			$Remarks=$RowPo['BillRemarks'];

		
		$SqlVesselName="SELECT * FROM ScrmLogisticsDetails WHERE PiID='$PiID'";
	    $StmtVesselName=$iconn->query($SqlVesselName);
	    $RowVesselName = $StmtVesselName->fetch_array();
	
	$VessalName=$RowVesselName['VesselName'];
	
	$ShipmentDueDate= date('d-m-Y', strtotime($ShipByDate));	
		
				$AryPrices=getPaymentValues($iconn,$PiID);
				$AryPrices[''];
				
					$TotalDiscount=$AryPrices['DiscountAmount'];
					$ExchangeRate=$AryPrices['ExchangeRate'];
					$InvValueINR=$AryPrices['InvValueINR'];
					$InvValueUSD=$AryPrices['InvValueUSD'];
					$PaymentTerms=$AryPrices['PaymentTerm'];
					
			
	
						
$GrandTotalInvValueUSD+=$InvValueUSD;
$GrandTotalInvValueINR+=$InvValueINR;
			
	$content .= 					
		'<tr>
		 <td   >'. ++$S_NO.'</td>
		 <td   >'.$VessalName.'</td>
		 <td   >'.$RowPo['CInvNo'].'</td>
		 <td   >'.$RowPo['PoNo'].'</td>
		 <td   >'.$RowPo['ConsigneeName'].'</td>
		 <td   >'.number_format($InvValueUSD,2).'</td>
		 <td   >'.number_format($InvValueINR,2).'</td>
		 <td   >'.$PaymentTerms.'</td>
		 <td   >'.$ShipmentDueDate.'</td>
		 <td   >'.$Remarks.'</td>				  
	 </tr>';
			 
}
	  $content .= 					
 '<tr> 
	   <td  >Total</td>				 
       <td  ></td>				 
       <td  ></td>				 
       <td  ></td>				 
	   <td  ></td>				 
	   <td  >'.number_format($GrandTotalInvValueUSD,2).'</td>				 
	   <td  >'.number_format($GrandTotalInvValueINR,2).'</td>				                				 
	   <td  > </td>
	   <td  > </td>
	   <td  > </td>
  </tr>';

	echo	$content .= '</table>';


//function getPaymentValues($iconn,$PoID){
	
	/*
	   $LbsFactor=0.454; 			// To Convert Lbs into Kgs  multiply value
	   $Discount=0;					// Get from Custom Invoice
	   $InsurancePerc=0.11;			// Varible
	   $AntiDumpingDFactor=101.35	// Anti Dumping Duty Factor(Formula)
	   $AntiDumpingMFactor=1.35		// Anti Dumping Duty Factor(Formula)
	   $StandardDutyCharges=125;	// Standard Value
	   $TermName=125;	// Standard Value
	   
	   */
	   
	   /*	$SqlCInvData="SELECT * FROM `ScrmCustomInvoices` as SCI 
						LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
						LEFT JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
						WHERE SCI.PiID=$PoID";
		$StmtCInvData=$iconn->query($SqlCInvData);
		$RowCInvData=$StmtCInvData->fetch_assoc();
		$TermName=$RowCInvData['ShippingTerms'];
		$Discount=$RowCInvData['Discount'];
		$FrieghtCharge=$RowCInvData['FrieghtCharge'];
		$ExchangeRate=$RowCInvData['ExchangeRate'];
		$QtyUnit=$RowCInvData['QtyUnit'];
	   
	   $SqlShipTermsMaster="SELECT * FROM `ScrmShipmentTermsMaster`
		where TermName='$TermName'";
		$StmtShipTermsMaster=$iconn->query($SqlShipTermsMaster);
		$RowShipTermsMaster=$StmtShipTermsMaster->fetch_assoc();
		
				
		$IsFreight=$RowShipTermsMaster['Freight'];		
		$IsDuty=$RowShipTermsMaster['Duty'];
		$InsurancePerc=$RowShipTermsMaster['Insurance'];		
		$AntiDumpingDFactor=$RowShipTermsMaster['AntiDumpingDFactor'];		
		$AntiDumpingMFactor=$RowShipTermsMaster['AntiDumpingMFactor'];		
		$StandardDutyCharges=$RowShipTermsMaster['StandardDutyCharges'];		

		

       $sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
		$SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs 
			FROM `ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on 
			PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'"; 
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();
			
			$NetWgtKgs = $RowTotal['NetWgtLbs']*$LbsFactor;
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			
			
			
			if($QtyUnit=='KGS'){				 
				$Weight = $RowTotal['NetWgtKgs'];	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'] * $LbsFactor;
				$Price = $RowTotal['PriceLbs'];
			}
			
			if($Discount>0){
			$DiscountAmount=($Price*$Discount)/100;
			$PriceAfterDiscount=$Price-$DiscountAmount;				
			}else{
			$PriceAfterDiscount=$Price;
			}
			
			$InsuranceValue= ($PriceAfterDiscount*$InsurancePerc)/100;
			
			$DutiesPrice=(($PriceAfterDiscount-$InsuranceValue-$FrieghtCharge)/$AntiDumpingDFactor)*$AntiDumpingMFactor; 
			$DutiesPrice=$DutiesPrice+$StandardDutyCharges;
			
			
			$InvValueUSD=$PriceAfterDiscount;
			$FOBValueUSD=$PriceAfterDiscount-$FrieghtCharge-$DutiesPrice;
			
			$InvValueINR=$InvValueUSD*$ExchangeRate;
			$FOBValueINR=$FOBValueUSD*$ExchangeRate;
			
		
			
			$AryPrices['ExchangeRate']=$ExchangeRate;
			$AryPrices['DiscountAmount']=$DiscountAmount;
			$AryPrices['InsuranceValue']=$InsuranceValue;
			$AryPrices['DutiesPrice']=$DutiesPrice;
			$AryPrices['FrieghtCharge']=$FrieghtCharge;
			
			$AryPrices['InvValueUSD']=$InvValueUSD;
			$AryPrices['InvValueINR']=$InvValueINR;
			$AryPrices['FOBValueUSD']=$FOBValueUSD;
			$AryPrices['FOBValueINR']=$FOBValueINR;
			$AryPrices['PaymentTerm']=$TermName;
			
			return $AryPrices;
	
}*/
 
?>
