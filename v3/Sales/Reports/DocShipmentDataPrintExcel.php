



<?php
//ini_set( 'display_errors', 1 );

session_start();


 $PoID=$_GET['id'];
 $Type=$_GET['Type'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


	
	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
include "../SalesInculdes/SalesConstants.php"; 
//composer require phpoffice/phpexcel;
 
	
	$CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading FROM ScrmCustomInvoices as SCI
  left join`ScrmShipmentDetails` as  SSD on SSD.PiID=SCI.PiID
  left join ScrmShipmentDispatchDetails as SD on SD.PIID=SSD.PiID
  left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN ScrmCountriesList as SL on SL.id=SP.Country
 left JOIN ScrmLogisticsDetails as SLD on SLD.PiID=SCI.PiID
 left JOIN ScrmLinerMaster as SLM on SLM.LinerID=SLD.SteamshipCompany


  WHERE SCI.PiID='$PoID'"; 

$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 

	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
	$SqlScrmPoData="SELECT *,SCD.ConsigneeName as BuyerName FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN AdminExporterDetails as AED on AED.ExpID=SPD.ProcessorID
 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();

 $SqlBreakup="SELECT * FROM `ScrmCIGradeData`  as SCD
left join AdminPackingStyles as APS on APS.PCKID=SCD.PackingStyleID
 WHERE InvId='$PoID'"; 
$StmtBreakUp=$iconn->query($SqlBreakup); 

		
$ExpID=$PoDataRow['ExporterID'];
$PoNo=$PoDataRow['PoNo'];
$Buyer=$PoDataRow['ConsigneeName'];
$BuyerName=$PoDataRow['BuyerName'];
$PaymentName=$PoDataRow['PaymentName'];
$ShippingTerms=$PoDataRow['ShippingTerms'];
$ProcessorBAPNo=$PoDataRow['ProcessorBAPNo']; 
 $CountryID=$PoDataRow['DestinationCountry'];
 
$ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
$ExporterName=$ExporterData['ExpName'];
$PackSealNo=$CustomInvoiceRow['PackSealNo'];
$SealNo=$CustomInvoiceRow['SealNo'];
$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
$CInvCode=$CustomInvoiceRow['CInvCode'];
$VehicalNumber=$CustomInvoiceRow['VehicalNumber'];
$SBNo=$CustomInvoiceRow['SBNo'];
$BLNo=$CustomInvoiceRow['BLNo'];
$Discount=$CustomInvoiceRow['Discount'];
$IGST=$CustomInvoiceRow['IGST'];
$CInvDate=$CustomInvoiceRow['CInvDate'];
$BLDate=$CustomInvoiceRow['BLDate'];
$PortOfDischarge=$CustomInvoiceRow['PortofDischarge'];
$FinalDestination=$CustomInvoiceRow['FinalDestination'];
$PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
$TranshipmentPort=$CustomInvoiceRow['TranshipmentPort'];
$LinerCode=$CustomInvoiceRow['LinerCode'];
$TranshipmentDate=getFormatedDateNA($CustomInvoiceRow['TranshipmentDate']);

/*$ETD=date('d-m-Y',strtotime($CustomInvoiceRow['ETD']));
$ETA=date('d-m-Y',strtotime($CustomInvoiceRow['ETA']));*/
 $ETD=getFormatedDateNA($CustomInvoiceRow['ETD']);
$ETA=getFormatedDateNA($CustomInvoiceRow['ETA']);
		

$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

$SqlPOgradeData="SELECT * FROM `$ScrmCIGradeData` where InvId='$PoID'  ";
$StmtPOgradeData=$iconn->query($SqlPOgradeData);

WHILE($RowPOgradeData = $StmtPOgradeData->fetch_assoc()){

$MCS=	$RowPOgradeData['Mcs']; 
   $TotalMcs+=$MCS; 
}

 $SqlTenPlusTwo="SELECT * FROM `$ScrmLogisticsDetails` as STD LEFT JOIN $ScrmLogisticsMaster 
as SL on SL.ID=STD.ConsolidatorNameAddress WHERE PiID='$PoID'"; 
			$StmtTenPlusTwo=$iconn->query($SqlTenPlusTwo);
		$RowTenPlusTwo=$StmtTenPlusTwo->fetch_assoc();
		
		
		$VesselName=$RowTenPlusTwo['VesselName'];
		$VOY=$RowTenPlusTwo['VOY'];
		$ImportingVessel=$RowTenPlusTwo['ImportingVessel'];
		$ImportingVOY=$RowTenPlusTwo['ImportingVOY'];
		
		$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
			 $SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `$ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();
			
			$NetWgtKgs = $RowTotal['NetWgtKgs'];
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			if( $PoDataRow['QtyUnit']=='KGS'){				
				$Weight = $RowTotal['NetWgtKgs'];	
				//$Weight = $RowTotal['NetWgtLbs']*0.454;	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'];
				//$Weight = $RowTotal['NetWgtKgs'];
				$Price = $RowTotal['PriceLbs'];
			}
			
		 IF($IGST=='DISCOUNT'){
				
				$DiscountAmount=($Price*$Discount)/100;
				$TotalDiscount=$Price-$DiscountAmount;
		 }
		 ELSE{
			$TotalDiscount=$Price; 
		 }
				

		
		
	$content='';

	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $widtd=40;
			  $widtd_s=15;
			  $widtd_ss=10;
              //$FontSize=0;
			  
			 
			  $content .= '
<table id="testTable" summary="Code page support in different versions of MS Windows."
 rules="groups" frame="hsides" border="2">
 
 <colgroup span="2" align="center"></colgroup>
 <colgroup span="4" align="center"></colgroup>

 <tbody>

			 <tr> 
             <td colspan="6" align="center" ><u>SHIPMENT DATA SHEET</u></td>
			 <tr>
			 <tr> 
             <td colspan="3" align="center" >Supplier Name </td>    
			 
			 <td colspan="3" align="center" >'.$ExporterName.'</td>
			 </tr> 
			 
			 <tr> 
             <td colspan="3" align="center" >Buyer Name</td>    
			 
			 <td colspan="3" align="center" >'.$BuyerName.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Payment Terms</td>    
			 <td colspan="3" align="center" ></td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Shipment Terms</td>    
			 
			 <td colspan="3" align="center" >'.$ShippingTerms.'  '.$FinalDestination.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Country of Origin</td>    
			 
			 <td colspan="3" align="center" >'.strtoupper($CustomInvoiceRow['PortOfLoadingCountry']).'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Purchase Order Number</td>    
			 
			 <td colspan="3" align="center" >'.$PoNo.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Invoice Number</td>
			 
    			 <td colspan="3" align="center" >'.$InvNO.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Invoice Date</td>
			 
			 <td colspan="3" align="center" align="center" >'.getFormatedDate($CInvDate).'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Total Invoice Amount</td> 
			 
			 <td colspan="3" align="center" >'.number_format($TotalDiscount,2).'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Total Pounds Shipped</td> 
			 
  			 <td colspan="3" align="center" >'.number_format($GrossWeightLbs,3).'</td>
			 </tr>

			 <tr> 
             <td colspan="3" align="center" >BL Number</td>
			 	 
			 <td colspan="3" align="center" >'.$BLNo.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Container Number</td> 
             			 
			 <td colspan="3" align="center" >'.$ContainerNumber.'</td>
			 </tr> 
			 
			 <tr> 
             <td colspan="3" align="center" >Seal Number</td>    
			 
			 <td colspan="3" align="center" >'.$SealNo.'</td>
			 </tr> 
			 
			 <tr> 
             <td colspan="3" align="center" >SCAC CODE</td>    
			 
			 <td colspan="3" align="center" >'.$LinerCode.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >BAP # of Plant</td>    
			 
			 <td colspan="3" align="center" >'.$ProcessorBAPNo.'</td>
			 </tr>

			 <tr> 
             <td colspan="3" align="center" >Port Of Loading</td>    
			 
			 <td colspan="3" align="center" >'.strtoupper($PortOfLoading).','.strtoupper($CustomInvoiceRow['PortOfLoadingCountry']).'</td>
			 </tr> 
			 
			 <tr> 
             <td colspan="3" align="center" >Shipped Onboard Date</td>    
			 
			 <td colspan="3" align="center" >'.getFormatedDate($BLDate).'</td>
			 </tr>

			 <tr> 
             <td colspan="3" align="center" >Originating Vessel Name</td>    
			 
			 <td colspan="3" align="center" >'.$VesselName.' '.$VOY.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Port Of Transhipment</td>    
			 
			 <td colspan="3" align="center" >'.$TranshipmentPort.'</td>
			 </tr>

			 <tr> 
             <td colspan="3" align="center" >Date Of Transhipment</td>    
			 
			 <td colspan="3" align="center" >'.$TranshipmentDate.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Final Importing Vessel Name</td>    
			 
			 <td colspan="3" align="center" >'.$ImportingVessel.' '.$ImportingVOY.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Port Of Discharge</td>    
			 
			 <td colspan="3" align="center" >'.strtoupper($PortOfDischarge).' , '.$CustomInvoiceRow['CountryCode'].','.strtoupper($CustomInvoiceRow['PortofDischargeCountry']).'</td>
			 </tr> 
			 
			 <tr> 
             <td colspan="3" align="center" >ETA Port Of Discharge Date</td>    
			 
			 <td colspan="3" align="center" >'.$ETA.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Final Destination</td>    
			 
			 <td colspan="3" align="center" >'.$FinalDestination.'</td>
			 </tr>

			 <tr> 
             <td colspan="3" align="center" >ETA Final Destination Date</td>    
			 
			 <td colspan="3" align="center" >'.$ETA.'</td>
			 </tr>
			 </tbody>
			 </table>
			 
			 <table >
				
				<tr > 
				<th  align="center"><b>Item Size</b></th>
				<th  colspan="5" align="center"><b>Item Description</b></th>
				<th  align="center"><b>Package</b></th>
				<th   align="center"><b>Quantity (Cases)</b></th>
				<th   align="center"><b>Price Per LB</b></th>
				<th  align="center"><b>Brand Name</b></th>
				
	
				</tr>';
				while($RowBreakup = $StmtBreakUp->fetch_assoc()){
					$Grade=$RowBreakup['Grade'];
					$Mcs=$RowBreakup['Mcs'];
					$Price=$RowBreakup['Price'];
					$Brand=$RowBreakup['Brand'];
					$Variety=$RowBreakup['Variety'];
					$PackingStyle=$RowBreakup['PackingStyle'];
				
			$content .= '	
			
				<tr>
			    <td  align="center">'.$Grade.'</td>
				<td colspan="5"  align="center" >'.$Mcs.' CARTONS OF '.$Variety.'</td>
				<td  align="center">'.$PackingStyle.'</td>
				<td  align="center">'.$Mcs.'</td>
				<td  align="center">'.$Price.'</td>
				<td  align="center">'.$Brand.'</td>
				
				</tr>';
			
}
				
			$content .= '</table>
			<br>
			<br>
			';
			 
		 
			
			
			
$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];
 
$filename=$InvShortCode.'-AZ_SD-'.$CInvNo.'-'.$FinancialYear.'.xls';
//$objPHPExcel = new PHPExcel(); 
//$file_name=$file_name.'- CI.pdf';
/**uma 05-05-2023*/

//$filename='DocShipmentData -'.$PoNo.'.xls';
//ob_end_clean();
		
			//header("Content-Disposition: attachment; filename=".$filename."");*/
			//ob_end_clean();
			 //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			 //header('Content-Disposition: attachment;filename='.$filename.'');
			// header('Content-Type: application/force-download');

			//header('Content-Type: application/octet'):
header("Content-Type: application/vnd.ms-excel;base64");
header('Content-Disposition: attachment;filename='.$filename.'');
header("Pragma: ");
header("Cache-Control: ");
    
  

   //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
   //$objWriter->save('php://output');
   
   //$content='';
			
echo "<html><head><style>
table, th, td {
  border: 1px solid black;
}
</style></head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
echo '<body style="border: 1px solid #ccc">';
echo $content;
echo "</body>";
echo "</html>";

			



?>


