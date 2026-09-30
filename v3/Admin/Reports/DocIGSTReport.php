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



$file_name='IGST Report -'.$from_date.'-'.$to_date.'.pdf';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	 $SqlPo="SELECT * FROM `$ScrmCustomInvoices` as SCI 
	  left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID

	 LEFT JOIN $TblScrmPIData as SPD on SPD.PoID=SCI.PiID 
	WHERE IGST='IGST' and CInvDate BETWEEN '$from_date' and '$date_end' $Query"; 
			  
		  
	
			  
			  $StmtPo=$iconn->query($SqlPo);
			 	  
 
	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=11;
			  $width_s=10;
			  $width_w=$width+5;
              
			  
			  $content .= '
			  				<h3 align="center">'.$ExporterName.'</h3>

			  <h3 align="center"> IGST Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="5" width="100%">
			   <tr>  

           		

                <th  align="center"   ><B>S No.</B></th> 
				<th  align="center"  ><B> Report ID</B></th>
				
				 
				<th  align="center"  ><B> Invoice No</B></th>
				   <th  align="center"  ><B> Date</B></th>			
				   <th  align="center"  ><B>FOB Value</B></th>			
				 <th  align="center"  ><B>IGST Value</B></th>
                <th  align="center"   <B>EIA Code </B></th>
                
    
               </tr> 
			   
			  '; 
			  
	
		//	
$TotalIGST=0;		
$TotalFOB=0;		
		while($RowPo = $StmtPo->fetch_array()){
			$ProcessorID=$RowPo['ProcessorID'];
			$Discount=$RowPo['Discount'];
			$PiID=$RowPo['PiID'];
			$FrieghtCharge=$RowPo['FrieghtCharge'];
			 $DestinationCountryId=$RowPo['DestinationCountry']; 
			 $ExchangeRate=$RowPo['ExchangeRate'];
			 $CInvCode=$RowPo['CInvCode'];
			 			$IGST=$RowPo['IGST'];





			
			
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
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			
			// $RowPo['QtyUnit'];
			
			if( $RowPo['QtyUnit']=='KGS'){				
				$Weight = $RowTotal['NetWgtKgs'];	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'] *0.454;
				//$Weight = $RowTotal['NetWgtKgs'];// Suspisios
				$Price = $RowTotal['PriceLbs'];
			}
			
			$DiscountAmount=($Price*$Discount)/100;
				 $TotalDiscount=$Price-$DiscountAmount;
			$DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35; 
					 //$DutiesPrice=round(($DutiesPrice+125),2);
					 $DutiesPrice=$DutiesPrice+125;
					 
					 if($DestinationCountryId=='26'){
						$FOBValue= ($TotalDiscount-$FrieghtCharge)*$ExchangeRate;
					 }
					 ELSE{
				$FOBValue=	($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate;	 
					 }
					 
					 $InvValue=$Price*$ExchangeRate;
			 		
					if($CInvCode=='03061720'){
						
						$IgstPerc=5;
						$IGSTValue=($InvValue*$IgstPerc)/100;
					}else{
						$IgstPerc=12;
						$IGSTValue=($InvValue*$IgstPerc)/100;
					}
//echo $IGSTValue;
$TotalFOB+=$FOBValue;
$TotalIGST+=$IGSTValue;
			/*********ma*******/

		    $content .= 
					
					'<tr>
				 <td  align="center" >'.$S_NO.'</td>
				 				 <td  align="center" >'.$RowPo['PiID'].'</td>


				 <td  align="center" >'.$RowPo['CInvNo'].'</td>
				 <td  align="center" >'.date('d-m-Y',strtotime($RowPo['CInvDate'])).'</td>
				 <td  align="center" >'.number_format($FOBValue,2).'</td>
				 <td  align="center"  >'.number_format($IGSTValue,2).'</td>
				 <td  align="center"  >'.$RowProcessor['ExpApprovalNo'].'</td>
				  
				 </tr>';

 $S_NO++;			 
}
		  $content .= 
					
		 '<tr>               			 
               <td  align="center" >Total</td>				 
               <td  align="center" ></td>				 
               <td  align="center" ></td>				 
               <td  align="center" ></td>				 
               <td  align="center" >'.number_format($TotalFOB,2).'</td>				 
               				 
			   <td  align="center" >'.number_format($TotalIGST,2).' </td>
				               <td  align="center" ></td>				 
 
		 </tr>';

		$content .= '</table>';


	echo $content;	


?>