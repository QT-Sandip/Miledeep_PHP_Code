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

$file_name='FBD Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	 $SqlPo="SELECT *,SPC.Name as PortofDischarge,SLM.LinerName as SteamshipCompany FROM `$ScrmCustomInvoices` as SCI 
	 LEFT JOIN $TblScrmPIData as SPD on SPD.PoID=SCI.PiID
	 LEFT JOIN $ScrmShipmentDetails as SD on SD.PiID=SCI.PiID
	   left JOIN $ScrmLogisticsDetails as SLD on SLD.PiID=SCI.PiID

LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
LEFT JOIN $TblAdminBankDetails as ABD on ABD.ID=SPD.BankID	
left JOIN $ScrmEirAgentNDiscountDetails as SEA on SEA.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmEIRBankNFieghtDetaails as SEB on SEB.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmEirFdaNOtherDetails as SEF on SEF.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmLinerMaster as SLM on SLM.LinerID=SLD.SteamshipCompany 
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID

 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge

 
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
			  <h3 align="center"> FBD Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="5">
			   <tr>  

           		

                <th  align="center"  width="3%"><B>S No.</B></th>              
				 
				<!--<th  align="center" width="'.$width_s.'%" ><B>Report ID</B></th>-->
				<th  align="center" width="'.$width_s.'%" ><B>Inv No</B></th>
				<th  align="center"  width="'.$width.'%"><B>Bill Value US$</B></th>	
				   <th  align="center"  width="35%"><B>Name of the Buyer</B></th>					   
				   <th  align="center"  width="'.$width_s.'%"><B>Buyer Order #</B></th>					   
				   <th  align="center"  width="'.$width.'%"><B>Bank</B></th>					   
				   <th  align="center"  width="'.$width.'%"><B>Ref Bill No.</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>Bill Discount Date</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>Due date As per bank</B></th>			
                <th  align="center"  width="'.$width.'%" <B>Terms of Payment</B></th>
                <th  align="center"  width="'.$width.'%" <B>Container FbdEta</B></th>
                <th  align="center"  width="'.$width.'%" <B>POD / LINE</B></th>
                <th  align="center"  width="'.$width.'%" <B>Container Number</B></th>
                <th  align="center"  width="'.$width.'%" <B>FDA INFO</B></th>
                <th  align="center"  width="'.$width.'%" <B>APPROVAL #</B></th>
                <th  align="center"  width="'.$width.'%" <B>US BROKER</B></th>
                <th  align="center"  width="'.$width.'%" <B>Buyer Agent</B></th>
                
    
               </tr> 
			   
			  '; 
			  
	
		//	
$TotalFOBUS=0;
$TotalFOB=0;
$GrandTotalDiscount=0;
$TotalNetWgtKgs=0;
$TotalNetWgtLbs=0;

		while($RowPo = $StmtPo->fetch_array()){
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
			
			/********uma********/
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
			//echo $TotalDiscount; 
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
			
			/*********ma*******/
			
		    $content .= 
					
					'<tr>
				 <th  align="center" width="3%">'.$S_NO.'</th>

			<!--	 <th  align="center" width="'.$width_s.'%">'.$RowPo['PiID'].'</th>-->
				 <th  align="center" width="'.$width_s.'%">'.$RowPo['CInvNo'].'</th>
				  <th  align="center" width="'.$width.'%">'.number_format($TotalDiscount,2).'</th>

				 <th  align="left" width="35%">'.$RowPo['ConsigneeName'].'</th>
				 <th  align="center" width="'.$width_s.'%">'.$PoNo.'</th>
				 <th  align="center" width="'.$width.'%">'.$BankName.'</th>
				 <th  align="center" width="'.$width.'%">'.$RowPo['BillRefNo'].'</th>
				 <th  align="center" width="'.$width.'%">'.$BillDiscountDate.'</th>
				 <th  align="center" width="'.$width.'%" >'.$DueDate.'</th>
				 <th  align="center" width="'.$width.'%" >'.$PaymentTerms.'</th>
				 <th  align="center" width="'.$width.'%" >'.$FbdEta.'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowPo['PortofDischarge'].'/'.$RowPo['SteamshipCompany'].'</th>
				 
				 <th  align="center" width="'.$width.'%" >'.$ContainerNo.'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowPo['FdaEntryNumber'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowProcessor['ExpApprovalNo'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowPo['UsBroker'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowPo['BuyerAgent'].'</th>
				  
				 </tr>';

 $S_NO++;			 
}

		  $content .= 
					
		 '<tr>             <th  align="center" width="15%">Total</th>				 
               <th  align="center" ></th>				 
              		 
               <th  align="center" >'.number_format($GrandTotalDiscount,2).'</th>				 
               <th  align="center" ></th>				 
               				 
			   <th  align="center" > </th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
			   <th  align="center" ></th>
 
		 </tr>';

		$content .= '</table>';
$filename='FBDReport -'.$from_date.'-'.$to_date.'.xls';

		header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=".$filename."");


	echo $content;	


?>