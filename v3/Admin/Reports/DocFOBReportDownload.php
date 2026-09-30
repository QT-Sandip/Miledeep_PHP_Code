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

$table_data=getP_TABLE($iconn);
	echo $table_data;



function getP_TABLE($iconn){

 global $from_date,$to_date,$Exporter,$ScrmCIGradeData,	$ScrmCustomInvoices,$TblScrmPIData,$ScrmShipmentDetails,
 $TblScrmConsigneeDetails,$TblAdminExporterDetails;




   
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


$filename='FOB Report -'.$from_date.'-'.$to_date.'.xls';

		$date_end=$to_date.' 23:59:59';  
	
		
	 $SqlPo="SELECT * FROM `$ScrmCustomInvoices` as SCI LEFT JOIN $TblScrmPIData as SPD on SPD.PoID=SCI.PiID
LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID

	WHERE  CInvDate BETWEEN '$from_date' and '$date_end' $Query  order by CInvNo Asc,CInvDate Asc";
			  $StmtPo=$iconn->query($SqlPo);
	

	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=11;
			  $width_s=10;
			  $width_w=$width+5;
              
			    $content .= '
				<h3 align="center">'.$ExporterName.'</h3>
			  <h3 align="center"> FOB Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" id="example1" cellspacing="0" cellpadding="5">
			   <tr>  

           		 <th  align="center"  width="3%"><B>S No.</B></th>              
				 
				<th  align="center" width="'.$width_s.'%" ><B>Report ID</B></th>
				<th  align="center" width="'.$width_s.'%" ><B>C Inv No</B></th>
				   <th  align="center"  width="'.$width_s.'%"><B> C Inv Date</B></th>			
				   <th  align="center"  width="35%"><B> Buyer Name</B></th>					   
				   <th  align="center"  width="'.$width_s.'%"><B> Total Inv Value in US$</B></th>					   
				   <th  align="center"  width="'.$width.'%"><B>FOB Value in US$</B></th>					   
				   <th  align="center"  width="'.$width.'%"><B>FOB Value in INR ₹</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>Net Weight(KGS)</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>Net Weight(LBS)</B></th>			
                <th  align="center"  width="'.$width.'%" <B>EIA Code </B></th>
                
    
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
					 //$DutiesPrice=round(($DutiesPrice+125),2);
					 $DutiesPrice=$DutiesPrice+125;
					 
					 if($DestinationCountryId=='26'||$CountryName=='UAE'||$ShippingTerms=='CNF'){
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


			/*********ma*******/
			
		    $content .= 
					
					'<tr>
				 <th  align="center" width="3%">'.$S_NO.'</th>
				 <th  align="center" width="'.$width_s.'%">'.$RowPo['PiID'].'</th>

				 <th  align="center" width="'.$width_s.'%">'.$RowPo['CInvNo'].'</th>
				 <th  align="center" width="'.$width_s.'%">'.date('d-m-Y',strtotime($RowPo['CInvDate'])).'</th>
				 <th  align="center" width="35%">'.$RowPo['ConsigneeName'].'</th>
				 <th  align="center" width="'.$width_s.'%">'.number_format($TotalDiscount,2).'</th>
				 <th  align="center" width="'.$width.'%">'.number_format($FOBValueUS,2).'</th>
				 <th  align="center" width="'.$width.'%">'.number_format($FOBValue,2).'</th>
				 <th  align="center" width="'.$width.'%">'.number_format($NetWgtKgs,3).'</th>
				 <th  align="center" width="'.$width.'%">'.number_format($NetWgtLbs,3).'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowProcessor['ExpApprovalNo'].'</th>
				
				 </tr>';

 $S_NO++;			 
}
		  $content .= 
					
		 '<tr>              <th  align="center" width="15%">Total</th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" >'.number_format($GrandTotalDiscount,2).'</th>				 
               <th  align="center" >'.number_format($TotalFOBUS,2).'</th>				 
               				 
			   <th  align="center" >'.number_format($TotalFOB,2).' </th>
			   <th  align="center" >'.number_format($TotalNetWgtKgs,3).' </th>
			   <th  align="center" >'.number_format($TotalNetWgtLbs,3).' </th>				 
 
		 </tr>';

		$content .= '</table>';
		
		header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=".$filename."");

		

	echo $content;	
}

?>