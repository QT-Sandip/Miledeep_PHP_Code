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

$ExporterData=getExporterDetails($ExpID);
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
			  $width=40;
			  $width_s=15;
			  $width_ss=10;
              //$FontSize=0;
			  
			  $content .= '
			  
			 <h3 align="CENTER" width="75%"><br><br><br><u>SHIPMENT DATA SHEET</u><br></h3>
			  
			 <table border="1" cellspacing="0" cellpadding="4" align="center">

			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Supplier Name </B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$ExporterName.'</td>
			 </tr> 
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Buyer Name</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$BuyerName.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Payment Terms</B></th>    
			 
			 <!--<th  align="center" width="'.$width.'%">'.$PaymentName.'</th>-->
			 <td align="center" width="'.$width.'%"></td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Shipment Terms</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$ShippingTerms.'  '.$FinalDestination.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Country of Origin</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.strtoupper($CustomInvoiceRow['PortOfLoadingCountry']).'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Purchase Order Number</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$PoNo.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Invoice Number</B></th>
			 
    			 <td  align="center" width="'.$width.'%">'.$InvNO.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Invoice Date</B></th>
			 
			 <td  align="center" width="'.$width.'%">'.getFormatedDate($CInvDate).'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Total Invoice Amount</B></th> 
			 
			 <td  align="center" width="'.$width.'%">'.number_format($TotalDiscount,2).'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Total Pounds Shipped</B></th> 
			 
  			 <td  align="center" width="'.$width.'%">'.number_format($GrossWeightLbs,3).'</td>
			 </tr>

			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>BL Number</B></th>
			 	 
			 <td  align="center" width="'.$width.'%">'.$BLNo.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Container Number</B></th> 
             			 
			 <td  align="center" width="'.$width.'%">'.$ContainerNumber.'</td>
			 </tr> 
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Seal Number</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$SealNo.'</td>
			 </tr> 
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>SCAC CODE</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$LinerCode.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>BAP # of Plant</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$ProcessorBAPNo.'</td>
			 </tr>

			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Port Of Loading</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.strtoupper($PortOfLoading).','.strtoupper($CustomInvoiceRow['PortOfLoadingCountry']).'</td>
			 </tr> 
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Shipped Onboard Date</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.getFormatedDate($BLDate).'</td>
			 </tr>

			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Originating Vessel Name</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$VesselName.' '.$VOY.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Port Of Transhipment</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$TranshipmentPort.'</td>
			 </tr>

			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Date Of Transhipment</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$TranshipmentDate.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Final Importing Vessel Name</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$ImportingVessel.' '.$ImportingVOY.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Port Of Discharge</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.strtoupper($PortOfDischarge).' , '.$CustomInvoiceRow['CountryCode'].','.strtoupper($CustomInvoiceRow['PortofDischargeCountry']).'</td>
			 </tr> 
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>ETA Port Of Discharge Date</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$ETA.'</td>
			 </tr>
			 
			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>Final Destination</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$FinalDestination.'</td>
			 </tr>

			 <tr> 
             <th  align="center" width="'.$width.'%" ><B>ETA Final Destination Date</B></th>    
			 
			 <td  align="center" width="'.$width.'%">'.$ETA.'</td>
			 </tr>
			 </table>
			 
			 <th width="100%"><br><br><br>
  <table border="1" cellspacing="0" cellpadding="5" align="center">
				
				<tr > 
				<th  align="CENTER"  width="'.$width_s.'%"><b>Item Size</b></th>
				<th  align="CENTER" width="'.$width_s.'%"><b>Item Description</b></th>
				<th  align="CENTER" width="'.$width_s.'%"><b>Package</b></th>
				<th  align="CENTER"  width="'.$width_s.'%"><b>Quantity (Cases)</b></th>
				<th  align="CENTER" width="'.$width_s.'%"><b>Price Per LB</b></th>
				<th  align="CENTER" width="'.$width_s.'%"><b>Brand Name</b></th>
				
	
				</tr>';
				while($RowBreakup = $StmtBreakUp->fetch_assoc()){
					$Grade=$RowBreakup['Grade'];
					$Mcs=$RowBreakup['Mcs'];
					$Price=$RowBreakup['Price'];
					$Brand=$RowBreakup['Brand'];
					$Variety=$RowBreakup['Variety'];
					$PackingStyle=$RowBreakup['PackingStyle'];
				
			$content .= '	<tr> 
				
				<td  align="CENTER"  width="'.$width_s.'%">'.$Grade.'</td>
				<td  align="CENTER"  width="30%">'.$Mcs.' CARTONS OF '.$Variety.'</td>
				<td  align="CENTER"  width="'.$width_s.'%">'.$PackingStyle.'</td>
				<td  align="CENTER"  width="'.$width_s.'%">'.$Mcs.'</td>
				<td  align="CENTER"  width="'.$width_s.'%">'.$Price.'</td>
				<td  align="CENTER"  width="'.$width_s.'%">'.$Brand.'</td>
				
				</tr>';
			
}
				
			$content .= '</table><br><br><br></th>'; 
			  
	
			//$content .= '</table>';
			/**uma 05-05-2023*/
			
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
		header("Content-Type: application/xls");
			//header("Content-Disposition: attachment; filename=".$filename."");*/
			//ob_end_clean();
			 //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			 //header('Content-Disposition: attachment;filename='.$filename.'');
			// header('Content-Type: application/force-download');
			//header('Content-Type: application/octet'):
header('Content-Disposition: attachment;filename='.$filename.'');
// Fix for crappy IE bug in download.
header("Pragma: ");
header("Cache-Control: ");
    
   

   //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
   //$objWriter->save('php://output');
			
			echo $content;
			//ob_end_clean();
	//echo $content;	

//}

?>