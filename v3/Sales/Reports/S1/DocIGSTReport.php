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



$QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  
    $Type=$_GET['Type']; 

      if($Type==2||$Type==1){

  $Month=$_GET['Month']; 
  $Year=$_GET['Year']; 
  $PsID=$_GET['PsID']; 
	  }  

 
else{
$Month=$_GET['from_date']; 
$Year=$_GET['to_date']; }

//echo $Month; exit;

   $Exporter=$_GET['Exporter']; 
   
   if($Type==1){
			$Query= "month(CInvDate)='$Month'  and year(CInvDate)='$Year'  ";
		 }else {
			$Query= "CInvDate between '$Month'  and '$Year'  "; 
		 }
   
/*if($Exporter==''){
	$Query.='';
}
else{
if($Exporter==1){
	$Query.='and SPD.ExporterID!=5';
}*/
	if($Exporter!=''){
	
	$Query.=' and SPD.ExporterID='.$Exporter;
	//}
	 $SqlExporter="SELECT * FROM $TblAdminExporterDetails where ExpID=$Exporter"; 
				  $StmtExporter=$iconn->query($SqlExporter);

$RowExporter= $StmtExporter->fetch_array();	
			 $ExporterName=$RowExporter['ExpName'];
	  
}


$monthName = date("F", mktime(0, 0, 0, $Month, 10));

 if($Type==1){
	  $Header='IGST Report-('.$monthName.')';

 }
 else{
 $Header='IGST Report-('.getFormatedDate($Month).' To '.getFormatedDate($Year).')';
	 
 }

$file_name='IGST Report -'.$from_date.'-'.$to_date.'.pdf';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	 $SqlPo="SELECT * FROM `ScrmCustomInvoices` as SCI 
	  left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
	 LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID 
	 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
     left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
	WHERE $Query and IGST='IGST'  "; 
			  
		  
	
			  
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

			  <h3 align="center">  '.$Header.'</h3>
			   
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
			$DestinationCountryId=$RowPo['DestinationCountry']; 
			$FrieghtCharge=$RowPo['FrieghtCharge'];
			 
			 $ExchangeRate=$RowPo['ExchangeRate'];
			 $CInvCode=$RowPo['CInvCode'];
			 			$IGST=$RowPo['IGST'];


            $SqlProcessor="SELECT * FROM `AdminExporterDetails` WHERE ExpID='$ProcessorID'";
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
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `ScrmCIGradeData` 
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
			
			$AryPaymentData=getPaymentValues($iconn,$PiID);
			$InsuranceValue=$AryPaymentData['InsuranceValue'];
			$ExchangeRate=$AryPaymentData['ExchangeRate'];
			$DiscountPerc=$AryPaymentData['DiscountPerc'];
			$DiscountAmount=$AryPaymentData['DiscountAmount'];
			$IgstPerc=$AryPaymentData['IgstPerc'];
			$IGSTValue=$AryPaymentData['IgstValue'];
			$InvValueUSD=$AryPaymentData['InvValueUSD'];
			$InvValueInr=$AryPaymentData['InvValueINR'];
			$UsdValue=$AryPaymentData['UsdValue']; 
			$TotalMcs=$AryPaymentData['TotalMcs'];
			$Price=$AryPaymentData['Price'];
			
		$InvValueUSDWithOutDuties=$AryPaymentData['InvValueUSDWithOutDuties'];
$InvValueINRWithOutDuties=$AryPaymentData['InvValueINRWithOutDuties'];

if($RowPo['ShowFrightCharge']==1 && $RowPo['ShowFrieght']==1){
		$FOBValueUSD=$AryPaymentData['FOBValueUSD'];
		$FOBValueINR=$AryPaymentData['FOBValueINR'];	
		}
		
		else{
		//$AryData['ShowFrightCharge']=false;	
		
		$FOBValueUSD=$AryPaymentData['InvValueUSDWithOutDuties'];
$FOBValueINR=$AryPaymentData['InvValueINRWithOutDuties'];
		}
			
			
$TotalFOB+=$FOBValueINR;
$TotalIGST+=$IGSTValue;
			/*********ma*******/
$fmt = new NumberFormatter($locale = 'en_IN', NumberFormatter::CURRENCY);
$FOBValue= $fmt->format($FOBValueINR); 
$IGSTValue= $fmt->format($IGSTValue); 

		    $content .= 
					
					'<tr>
				 <td  align="center" >'.$S_NO.'</td>
				 				 <td  align="center" >'.$RowPo['PiID'].'</td>


				 <td  align="center" >'.$RowPo['CInvNo'].'</td>
				 <td  align="center" >'.date('d-m-Y',strtotime($RowPo['CInvDate'])).'</td>
				<!-- <td  align="center" >'.number_format($FOBValue,2).'</td>-->
				 <td  align="center" >'.$FOBValue.'</td>
				 <td  align="center"  >'.$IGSTValue.'</td>
				 <td  align="center"  >'.$RowProcessor['ExpApprovalNo'].'</td>
				  
				 </tr>';

 $S_NO++;			 
}
$fmt = new NumberFormatter($locale = 'en_IN', NumberFormatter::CURRENCY);

$TotalFOB= $fmt->format($TotalFOB); 
$TotalIGST= $fmt->format($TotalIGST); 
		  $content .= 
					
		 '<tr>               			 
               <td  align="center" >Total</td>				 
               <td  align="center" ></td>				 
               <td  align="center" ></td>				 
               <td  align="center" ></td>				 
               <td  align="center" >'.$TotalFOB.'</td>				 
               				 
			   <td  align="center" >'.$TotalIGST.' </td>
			 <td  align="center" ></td>				 
 
		 </tr>';

		$content .= '</table>';


	echo $content;	


?>