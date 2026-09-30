<?php
//session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

  $AccessType=$_SESSION["ACCESS_TYPE"]; 
$YEAR=$_POST['YEAR'];
 $EXPORTER=$_POST['EXPORTER']; 
 $Buyer=$_POST['Buyer']; 
 $Country=$_POST['Country']; 
$TYPE=$_POST['TYPE'];
$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';
//
	
	
if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}
 $Query;
	
if($Buyer!=" "){
	$SubQuery='and SCD.ConID='.$Buyer;
}
else{
	$SubQuery='';
}

if($Country!=" "){
	$SQuery='and SCL.id='.$Country;
}
else{
	$SQuery='';
}


if($YEAR!=''){
	
  $SqlPo="SELECT *,SPC.Name as PortofDischarge,SLM.LinerName as ShippingCompany FROM `$ScrmCustomInvoices` as SCI 
	 LEFT JOIN $TblScrmPIData as SPD on SPD.PoID=SCI.PiID
LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
LEFT JOIN $TblAdminBankDetails as ABD on ABD.ID=SPD.BankID	
left JOIN $ScrmEirAgentNDiscountDetails as SEA on SEA.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmEIRBankNFieghtDetaails as SEB on SEB.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmEirFdaNOtherDetails as SEF on SEF.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmLinerMaster as SLM on SLM.LinerID=SEB.ShippingCompany 
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID

 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge

  
	WHERE  Year(CInvDate)='$YEAR' $Query  order by CInvNo Asc,CInvDate Asc";
			 
			 $Stmt=$iconn->query($SqlPo);
	

$data=' <table id="key-datatable" class="no-wrap table-bordered" 
	 style="background:#fff;margin-top:6px !important;margin-bottom:-6px !important; height:100px">
	 			
<thead>

         <tr>
	   <th>S No</th>
	   <th>Inv No</th>	
	   <th>Bill Value US$</th>
	   <th>Name of the Buyer</th>		
	   <th>Buyer Order #</th>		
       <th>Bank</th>
       <th>Ref Bill No.</th>
       <th>Bill Discount Date</th>
	   <th>Due date As per bank</th>
	   <th>Terms of Payment</th>
	   <th>Container FbdEta</th>
	   <th>POD / LINE</th>
	   <th>Container Number</th>
	   <th>FDA INFO</th>
	   <th>APPROVAL #</th>
	   <th>US BROKER</th>
	   <th>Buyer Agent</th>		
		</tr>

    </thead>
	<tbody>';
				

$Sno=1;					
  while($RowPo= $Stmt->fetch_array()){
//ROBO	
$PoID=$RowPo['PoID'];

			$SqlPaymentTerms="SELECT * FROM $TblScrmPoData as SPD 
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 
$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);
$RowPaymentTerms = $StmtPaymentTerms->fetch_assoc();

$SqlTenPlusTwo="SELECT * FROM `$ScrmLogisticsDetails`  WHERE PiID='$PoID'";
		$StmtTenPlusTwo=$iconn->query($SqlTenPlusTwo);
		$RowTenPlusTwo=$StmtTenPlusTwo->fetch_assoc();

		$FbdEta=date('d-m-Y',strtotime($RowTenPlusTwo['FbdEta']));

$PO_TIME_STAMP = strtotime($row['InvDate']);
$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);
 
if($FbdEta=='01-01-1970'){
			$FbdEta='NA';
		}
		else{
			$FbdEta;
		}

			$PaymentTerms=$RowPaymentTerms['PaymentName'];


			$BankName=$RowPo['BankName'];
			$ContainerNo=$RowPo['ContainerNo'];
			$PoNo=$RowPo['PoNo'];
			$ProcessorID=$RowPo['ProcessorID'];
			$Discount=$RowPo['Discount'];
			$PiID=$RowPo['PiID'];
			$FrieghtCharge=$RowPo['FrieghtCharge'];
			$DestinationCountryId=$RowPo['DestinationCountry']; 
			$ExchangeRate=$RowPo['ExchangeRate'];
			$CInvCode=$RowPo['CInvCode'];
			$IGST=$RowPo['IGST'];
			$ShippingTerms=$RowPo['ShippingTerms'];
			
			
			$SqlProcessor="SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$ProcessorID'";
			$StmtProcessor=$iconn->query($SqlProcessor);
			$RowProcessor=$StmtProcessor->fetch_array();
$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
			 $SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `$ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PiID'"; 
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();
			
			$NetWgtKgs = $RowTotal['NetWgtKgs'];
			$NetWgtKgs = $RowTotal['NetWgtLbs']*0.454;
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			
			 $RowPo['QtyUnit'];
			
			if( $RowPo['QtyUnit']=='KGS'){				
				$Weight = $RowTotal['NetWgtKgs'];	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'] *0.454;
				//$Weight = $RowTotal['NetWgtKgs'];// Suspisios
				$Price = $RowTotal['PriceLbs'];
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
					 
					 if($DestinationCountryId=='26'){
				
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

$BillDiscountDate=date('d-m-Y',strtotime($RowPo['BillDiscountDate']));
if($BillDiscountDate=='01-01-1970'){
	$BillDiscountDate='';
}
$DueDate=date('d-m-Y',strtotime($RowPo['DueDate']));
if($DueDate=='01-01-1970'){
	$DueDate='';
}			

	
	$data.='<tr> 
	<td id="'.$row['ID'].'" > '. $Sno.'</td>
			
		<td id="'.$row['ID'].'" > '.$RowPo['CInvNo'].'</td>
	    <td id="'.$row['ID'].'" > '. number_format($TotalDiscount,2) .'</td>
		
		<td id="'.$row['ID'].'" > '. $RowPo['ConsigneeName'] .'</td>
		<td id="'.$row['ID'].'" > '. $PoNo.'</td>
		<td id="'.$row['ID'].'" > '.  $BankName.'</td>
		<td id="'.$row['ID'].'" > '. $RowPo['BillRefNo'].'</td>       
		       
		<td id="'.$row['ID'].'" > '. $BillDiscountDate.'</td>       
		<td id="'.$row['ID'].'" > '. $DueDate.'</td>       
		<td id="'.$row['ID'].'" > '. $PaymentTerms.'</td>       
		<td id="'.$row['ID'].'" > '. $FbdEta.'</td>       
		<td id="'.$row['ID'].'" > '. $RowPo['PortofDischarge'].'/'.$RowPo['ShippingCompany'].'</td>       
		<td id="'.$row['ID'].'" > '. $ContainerNo.'</td>       
		<td id="'.$row['ID'].'" > '. $RowPo['FdaEntryNumber'].'</td>       
		<td id="'.$row['ID'].'" > '.$RowProcessor['ExpApprovalNo'].'</td>       
		<td id="'.$row['ID'].'" > '. $RowPo['UsBroker'].'</td>       
		<td id="'.$row['ID'].'" > '. $RowPo['BuyerAgent'].'</td>       
		
		<td id="'.$row['PoID'].'" > ';
		if($AccessType==1) {$data.=' <div class="bg-active color-palette" align="center" >
	
		</div>';}

else{
		$data.=' ';}	
		
		
	$data .='	</td></tr>';
$Sno++;	  
}
	
	echo $data .='</tbody></table>';

					
					



}


 
?>
