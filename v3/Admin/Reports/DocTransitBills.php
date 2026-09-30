<style>
table tr td{
	
	 font-size: 15px;
    text-align: left;
}

table tr th{
	
	    font-size: 12px;
    text-align: left;
	 background: white;
  position: sticky;
  top: 0;
}


</style>

<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$B_TYPE=$_GET['TYPE'];
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


$SqlProStations="SELECT DISTINCT LcStatus from  ScrmPaymentTerms order by LcStatus desc";
		
        $StmtProStations=$iconn->query($SqlProStations);

while($Row=$StmtProStations->fetch_array()){
          $LcStatus=$Row['LcStatus'];
		  if($LcStatus==0){
          $BillName='TRANSIT BILLS NON-LC BASED:';
		  $Header=''; 

		  }
		  else{
		  $BillName='TRANSIT BILLS LC BASED:';
		  $Header='STATEMENT OF PURCHASE ORDERS ';

		  }
          $table_data=getP_TABLE($iconn,$from_date,$to_date,$BillName,$LcStatus,$Header);
          
		 
			echo $table_data['TABLE'];
			
        }
		
  

$file_name='TransitBills Report -'.$from_date.'-'.$to_date.'.xlsx';


/**uma**/

function getP_TABLE($iconn,$from_date,$to_date,$BillName,$LcStatus,$Header){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


/**uma**/

		$date_end=$to_date.' 23:59:59';
global $ExporterName;		
	//and  LcStatus='$LcStatus'
	
		
  $SqlPo="SELECT * FROM `$ScrmCustomInvoices` as SCI 
LEFT JOIN $TblScrmPIData as SPD on SPD.PoID=SCI.PiID
LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	    
left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
LEFT JOIN ScrmEirFdaNOtherDetails as SED on SED.PoRefNumber=SCI.PiID
left join ScrmPaymentTerms SPT on SPT.ID=SPD.PaymentTerms
WHERE  SCI.PiID  in(SELECT PoRefNumber FROM `ScrmEirAgentNDiscountDetails` WHERE CHAR_LENGTH(BillRefNo)>15)   and  LcStatus='$LcStatus' and BillStatus!=1
 $Query  order by CInvNo Asc,CInvDate Asc"; 
//and CInvDate BETWEEN '$from_date' and '$date_end'			  
$StmtPo=$iconn->query($SqlPo);
	

 
	$content='';
	$size=40;	 
	$size1=60;	
			  	
			  
			  $S_NO=1;
			  $width=11;
			  $width_s=8;
			  $width_w=$width+5;
              
			  $content .= '
		<!--<h3 align="center">'.$ExporterName.'</h3>-->
			  <h3 align="center"><B> '.$Header.'</B></h3>
			  			  
			  <table border="1" cellspacing="0" cellpadding="5">
			  <h3 align="left" style="color:red;"> '.$BillName.'</h3>

	<tr>             		
                   <th  align="center"  ><B>S.NO</B></th>
                   <th  align="center"  ><B></B></th>
				   <th  align="center"  ><B>INVOICE</B></th>	
				   <th  align="center"  ><B>ORDER NO</B></th>					   
				   <th  align="center"  ><B>NAME OF THE BUYER</B></th>					   
				   <th  align="center"  ><B>INVOICE VALUE US$</B></th>						
				   <th  align="center"  ><B>INR EXCHANGE</B></th>			
				   <th  align="center"  ><B>PAYMENT TERMS</B></th>			
				   <th  align="center"  ><B>DUE DATE OF SHIPMENT</B></th>			
                   <th  align="center"  ><B>REMARKS</B></td>                    
    </tr> 			   
			'; 
			  
	
		//	

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
			
$cookie_duration = 30 * 24 * 60 * 60; // Cookie Duration is for 30 Days. Change it accordingly.

setcookie ("ExRate", $_POST['ExchangeRate'], time()+3600);
		//isset($_COOKIE[$ExRate]);
//$ExchangeRate = $ExRate;
echo $_COOKIE['ExRate'];
		
		$SqlVesselName="SELECT * FROM ScrmLogisticsDetails WHERE PiID='$PiID'";
	    $StmtVesselName=$iconn->query($SqlVesselName);
	    $RowVesselName = $StmtVesselName->fetch_array();
	
	$VessalName=$RowVesselName['VesselName'];
	
	$ShipmentDueDate= date('d-m-Y', strtotime($ShipByDate));	
		
	$SqlPayment="SELECT * FROM `ScrmPaymentTerms` WHERE ID='$PaymentTerms'";
	$StmtPayment=$iconn->query($SqlPayment);
	$RowPayment = $StmtPayment->fetch_array();
	
	$PaymentTerm=$RowPayment['PaymentMode'];
	
					
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
				if($DestinationCountryId=='26'||$CountryName=='UAE'||$ShippingTerms=='CNF'){
					$FOBValue= ($TotalDiscount-$FrieghtCharge)*$ExchangeRate;
					$FOBValueUS=$TotalDiscount-$FrieghtCharge;
					}
					ELSE{
						
				$FOBValue=	($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate;	 
				$FOBValueUS=	$TotalDiscount-$FrieghtCharge-$DutiesPrice;	 
				$INRValue=$TotalDiscount*$ExchangeRate;	
					 }
					 /**uma(16/01/22)**/
					 IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}
			/**uma(16/01/22)**/	
$INRValue=$TotalDiscount*$ExchangeRate;	
$GrandTotalDiscount+=$TotalDiscount;
$GrandTotalINRValue+=$INRValue;
			
	$content .= 					
		'<tr>
		 <td  align="center"  >'.$S_NO.'</td>
		 <td  align="center"  >'.$VessalName.'</td>
		 <td  align="center"  >'.$RowPo['CInvNo'].'</td>
		 <td  align="center"  >'.$RowPo['PoNo'].'</td>
		 <td  align="left"	  >'.$RowPo['ConsigneeName'].'</td>
		 <td  align="center"  >'.number_format($TotalDiscount,2).'</td>
		 <td  align="center"  >'.number_format($INRValue,2).'</td>
		 <td  align="center"  >'.$PaymentTerm.'</td>
		 <td  align="center"  >'.$ShipmentDueDate.'</td>
		 <td  align="center"  >'.$Remarks.'</td>				  
	 </tr>';
 $S_NO++;			 
}
	  $content .= 					
 '<tr> 
	   <td  align="center" >Total</td>				 
       <td  align="center" ></td>				 
       <td  align="center" ></td>				 
       <td  align="center" ></td>				 
	   <td  align="center" ></td>				 
	   <td  align="center" >'.number_format($GrandTotalDiscount,2).'</td>				 
	   <td  align="center" >'.number_format($GrandTotalINRValue,2).'</td>				                				 
	   <td  align="center" > </td>
	   <td  align="center" > </td>
	   <td  align="center" > </td>
  </tr>';

		$content .= '</table>';
$Data['TABLE']=$content;

	return $Data;	
}
?>