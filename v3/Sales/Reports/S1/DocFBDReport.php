<style>
table tr td{
	
	    font-size: 17px;
    text-align: left;
}

table tr th{
	
	    font-size: 13px;
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
$Year=$_GET['to_date']; 	 
 }
 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

if($Type==1){
			$Query= "month(CInvDate)='$Month'  and year(CInvDate)='$Year'  ";
		 }else {
			$Query= "CInvDate between '$Month'  and '$Year'  "; 
		 }

$Exporter=$_GET['Exporter']; 
   
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

$file_name='FBD Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	

  $monthName = date("F", mktime(0, 0, 0, $Month, 10));

 if($Type==1){
	  $Header='FBD Report-('.$monthName.')';

 }
 else{
 $Header='FBD Report-('.getFormatedDate($Month).' To '.getFormatedDate($Year).')';
	 
 }
 	
		
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

 
	WHERE  $Query and BillStatus!='1'
	and CHAR_LENGTH(SEA.BillRefNo)> 15	  order by CInvNo Asc,CInvDate Asc";
			  $StmtPo=$iconn->query($SqlPo);
	

	
			 
			  

	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=13;
			  $width_s=8;
			  $width_w=$width+5;
              
			    $content .= '
				<h3 align="center">'.$ExporterName.'</h3>
			  <h3 align="center">'.$Header.'</h3>
			  
			  
			  <table width="100% "border="1" cellspacing="0" cellpadding="5">
			   <tr>  

           		

                <th  align="center"   ><B>S No.</B></th>              
				<th  align="center" ><B>Inv No</B></th>
				<th  align="center" ><B>Bill Value US$</B></th>	
				<th  align="center"  ><B>Name of the Buyer</B></th>					   
				<th  align="center"  ><B>Buyer Order #</B></th>					   
				<th  align="center"  ><B>Bank</B></th>					   
				<th  align="center"  ><B>Ref Bill No.</B></th>			
				<th  align="center"  ><B>Bill Discount Date</B></th>			
				<th  align="center"  ><B>Due date As per bank</B></th>			
                <th  align="center" ><B>Terms of Payment</B></th>
                <th  align="center" ><B>Container FbdEta</B></th>
                <th  align="center" ><B>POD / LINE</B></th>
                <th  align="center" ><B>Container Number</B></th>
                <th  align="center" ><B>FDA INFO</B></th>
                <th  align="center" ><B>APPROVAL #</B></th>
                <th  align="center" ><B>US BROKER</B></th>
                <th  align="center" ><B>Buyer Agent</B></th>
                
    
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
		$FbdEta=getFormatedDateNA($RowTenPlusTwo['FbdEta']);


		

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
			
			
			
			$AryPaymentData=getPaymentValues($iconn,$PiID);
			$InsuranceValue=$AryPaymentData['InsuranceValue'];
			$ExchangeRate=$AryPaymentData['ExchangeRate'];
			$DiscountPerc=$AryPaymentData['DiscountPerc'];
			$DiscountAmount=$AryPaymentData['DiscountAmount'];
			$IgstPerc=$AryPaymentData['IgstPerc'];
			$IgstValue=$AryPaymentData['IgstValue'];
			$InvValueUSD=$AryPaymentData['InvValueUSD']+$InsuranceValue;
			$InvValueInr=$AryPaymentData['InvValueINR'];
			
			$TotalMcs=$AryPaymentData['TotalMcs'];
			$Price=$AryPaymentData['Price'];
$TotalFOBUS+=$FOBValueUS;
$TotalFOB+=$FOBValue;
$GrandTotalDiscount+=$InvValueUSD;
$TotalNetWgtKgs+=$NetWgtKgs;
$TotalNetWgtLbs+=$NetWgtLbs;

$BillDiscountDate=getFormatedDate($RowPo['BillDiscountDate']);
$DueDate=getFormatedDate($RowPo['DueDate']);

			
			/*********ma*******/
			
		    $content .= 
					
					'<tr>
				 <td  align="center"  >'.$S_NO.'</td>
				 <td  align="center"  >'.$RowPo['CInvNo'].'</td>
				 <td  align="center"  >'.number_format($InvValueUSD,2).'</td>
				 <td  align="left"    >'.$RowPo['ConsigneeName'].'</td>
				 <td  align="center"  >'.$PoNo.'</td>
				 <td  align="center"  >'.$BankName.'</td>
				 <td  align="center"  >'.$RowPo['BillRefNo'].'</td>
				 <td  align="center"  >'.$BillDiscountDate.'</td>
				 <td  align="center"  >'.$DueDate.'</td>
				 <td  align="center"  >'.$PaymentTerms.'</td>
				 <td  align="center"  >'.$FbdEta.'</td>
				 <td  align="center"  >'.$RowPo['PortofDischarge'].'/'.$RowPo['SteamshipCompany'].'</td>
				 <td  align="center"  >'.$ContainerNo.'</td>
				 <td  align="center"  >'.$RowPo['FdaEntryNumber'].'</td>
				 <td  align="center"  >'.$RowProcessor['ExpApprovalNo'].'</td>
				 <td  align="center"  >'.$RowPo['UsBroker'].'</td>
				 <td  align="center"  >'.$RowPo['BuyerAgent'].'</td>
				  
				 </tr>';

 $S_NO++;			 
}

		  $content .= 
					
		 '<tr>  
			   <td  align="center">Total</td>				 
               <td  align="center" ></td>				 	 
               <td  align="center" >'.number_format($GrandTotalDiscount,2).'</td>				 
               <td  align="center" ></td>				 
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
			   <td  align="center" ></td>
 
		 </tr>';

		$content .= '</table>';


	echo $content;	


?>