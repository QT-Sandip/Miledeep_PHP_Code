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
include ($cp."Includes/IncludePage.php");

$B_TYPE=$_GET['TYPE'];
$from_date=$_GET['from_date']; 
$to_date=$_GET['to_date']; 
 $Exporter=$_GET['Exporter'];
 $PaymentTerms=$_GET['PaymentTerms']; 
 $PaymentTermID=$_GET['PaymentTerms']; 
$SQuery='';
if($Exporter==''){
	$Query='';
}

else{
	if($Exporter==1){
		$Query='and SPD.ExporterID!=5';
	}else{	
	$Query=" and SPD.ExporterID in ($Exporter)";
	}
	
	
	

	
  $SqlExporter="SELECT * FROM AdminExporterDetails where ExpID IN ($Exporter)"; 
$StmtExporter=$iconn->query($SqlExporter);
$RowExporter= $StmtExporter->fetch_array();	
$ExporterName=$RowExporter['ExpName'];
	  
}
if($PaymentTerms!=''){
	$SQuery='where LcStatus='.$PaymentTerms;
}

/*if($PaymentTermID !=''){ 
	 $Query.=" and  LcStatus='$PaymentTermID'";
}*/

//exit;


	

	$SqlProStations="SELECT  distinct LcStatus from  ScrmPaymentTerms $SQuery order by LcStatus desc";
	      

//echo $SqlProStations;exit;
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
          $table_data=getP_TABLE($iconn,$BillName,$LcStatus,$Header);
          
		 
			echo $table_data['TABLE'];
			
        }
		

//$file_name='TransitBills Report -'.$from_date.'-'.$to_date.'.xlsx';
$file_name='TransitBills Report.xlsx';


/**uma**/

function getP_TABLE($iconn,$BillName,$LcStatus,$Header){
	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


/**uma**/

		$date_end=$to_date.' 23:59:59';
global $ExporterName,$Query;		
	//and  LcStatus='$LcStatus'
	
		
  /* $SqlPo="SELECT * FROM `ScrmCustomInvoices` as SCI 
LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	    
left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
LEFT JOIN ScrmEirFdaNOtherDetails as SED on SED.PoRefNumber=SCI.PiID
left join ScrmPaymentTerms SPT on SPT.ID=SPD.PaymentTerms
WHERE  SCI.PiID  in(SELECT PoRefNumber FROM `ScrmEirAgentNDiscountDetails` WHERE CHAR_LENGTH(BillRefNo)>15)   and  LcStatus='$LcStatus' and BillStatus!=1
 $Query  order by CInvNo Asc,CInvDate Asc"; */
 
//LEFT JOIN ScrmEirFdaNOtherDetails as SED on SED.PoRefNumber=SCI.PiID 
   $SqlPo="SELECT * FROM `ScrmCustomInvoices` as SCI 
LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	    
left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID

left join ScrmPaymentTerms SPT on SPT.ID=SPD.PaymentTerms
LEFT JOIN ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
WHERE SDB.TransportedDate!='' 
 and (SDB.BillDiscountingDate ='' or SDB.BillDiscountingDate is null) 
 $Query and  LcStatus='$LcStatus'
order by SCI.CInvNo Asc,SCI.CInvDate Asc";
//and CInvDate BETWEEN '$from_date' and '$date_end'	

//echo 'Hi';  
//echo $Query; 		  
 
$StmtPo=$iconn->query($SqlPo);
	//echo $SqlPo; exit;		 

 
	$content='';
	$size=40;	 
	$size1=60;	
			  	
			  
			  $S_NO=1;
			  $width=11;
			  $width_s=8;
			  $width_w=$width+5;
              
			  $content .= '
	
			  <h3 align="center"><B> '.$Header.'</B></h3>			  			  
			  <table border="1" cellspacing="0" cellpadding="5">
			  <h3 align="left" style="color:red;"> '.$BillName.'</h3>

	<tr>             		
                   <th  align="center"  ><B>S.NO</B></th>
                   <th  align="center"  ><B>VESSEL NAME</B></th>
				   <th  align="center"  ><B>INVOICE</B></th>	
				   <th  align="center"  ><B>ORDER NO</B></th>					   
				   <th  align="center"  ><B>NAME OF THE BUYER</B></th>					   
				   <th  align="center"  ><B>INVOICE VALUE US$</B></th>						
				   <th  align="center"  ><B>INR EXCHANGE</B></th>			
				   <th  align="center"  ><B>PAYMENT TERMS</B></th>			
				   <th  align="center"  ><B>DUE DATE OF SHIPMENT</B></th>			
                   <th  align="center"  ><B>REMARKS</B></th>                    
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
		 <td  align="center"  >'.$S_NO.'</td>
		 <td  align="center"  >'.$VessalName.'</td>
		 <td  align="center"  >'.$RowPo['CInvNo'].'</td>
		 <td  align="center"  >'.$RowPo['PoNo'].'</td>
		 <td  align="left"	  >'.$RowPo['ConsigneeName'].'</td>
		 <td  align="center"  >'.number_format($InvValueUSD,2).'</td>
		 <td  align="center"  >'.number_format($InvValueINR,2).'</td>
		 <td  align="center"  >'.$PaymentTerms.'</td>
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
	   <td  align="center" >'.number_format($GrandTotalInvValueUSD,2).'</td>				 
	   <td  align="center" >'.number_format($GrandTotalInvValueINR,2).'</td>				                				 
	   <td  align="center" > </td>
	   <td  align="center" > </td>
	   <td  align="center" > </td>
  </tr>';

		$content .= '</table>';
$Data['TABLE']=$content;

	return $Data;	
}
?>