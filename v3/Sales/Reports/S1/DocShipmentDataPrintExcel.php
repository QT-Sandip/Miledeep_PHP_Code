



<?php
// ini_set( 'display_errors', 1 );

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
SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading 
,SCF.name as FinalDestinationCountry,SPCF.Name as FinalDestination,SPCF.CountryCode as FinalDestinationCountryCode,SCI.ContainerNo
FROM ScrmCustomInvoices as SCI
left JOIN ScrmPoData as SSD on SSD.PoID=SCI.PiID
  left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
left JOIN ScrmCountriesList as SCF on SCF.id=SPCF.Country
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN ScrmCountriesList as SL on SL.id=SP.Country
 left JOIN ScrmLogisticsDetails as SLD on SLD.PiID=SCI.PiID
 left JOIN ScrmLinerMaster as SLM on SLM.LinerID=SCI.ShippingLineID
 left JOIN ScrmISFDetails as ISF on ISF.PIID=SCI.PiID


  WHERE SCI.PiID='$PoID'"; 

$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 

	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
	$SqlScrmPoData="SELECT *,SCDB.BuyerName as BuyerName,
	SCD.ConsigneeName as ConsigneeName FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
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
// $Buyer=$PoDataRow['ConsigneeName'];
// $BuyerName=$PoDataRow['BuyerName'];

$ConsigneeID = $PoDataRow['ConsigneeID'];
	$BuyerID = $PoDataRow['BuyerID'];



	if ($BuyerID == '' || $BuyerID == 0) {
		$BuyerName = $PoDataRow['ConsigneeName'];
		//$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];
	} else if ($ConsigneeID != $BuyerID) {
		$BuyerName = $PoDataRow['BuyerName'];
		//$ConsigneeAddress = $PoDataRow['BuyerAddress'];
	} else {
		$BuyerName = $PoDataRow['ConsigneeName'];
		//$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];

	}
$PaymentName=$PoDataRow['PaymentName'];
$ShippingTerms=$PoDataRow['ShippingTerms'];
$ProcessorBAPNo=$PoDataRow['ProcessorBAPNo']; 
 $CountryID=$PoDataRow['DestinationCountry'];
 
$ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
$ExporterName=$ExporterData['ExpName'];
$PackSealNo=$CustomInvoiceRow['PackSealNo'];
//$SealNo=$CustomInvoiceRow['SealNo'];
$SealNo=$CustomInvoiceRow['LinerSealNo'];
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
$FinalDestination=strtoupper($CustomInvoiceRow['FinalDestination'].'  '.$CustomInvoiceRow['FinalDestinationCountryCode'].' ,'.$CustomInvoiceRow['FinalDestinationCountry']);
$PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
//$TranshipmentPort=$CustomInvoiceRow['TranshipmentPort'];

$LinerCode=$CustomInvoiceRow['LinerCode'];
//$TranshipmentDate=getFormatedDateNA($CustomInvoiceRow['TranshipmentDate']);


/*$ETD=date('d-m-Y',strtotime($CustomInvoiceRow['ETD']));
$ETA=date('d-m-Y',strtotime($CustomInvoiceRow['ETA']));*/
 $ETD=getFormatedDateNA($CustomInvoiceRow['ETD']);
//$ETA=getFormatedDateNA($CustomInvoiceRow['ETA']);
		

$InvNO=$CustomInvoiceRow['CustomsInvNo'];

$SqlPOgradeData="SELECT * FROM `ScrmCIGradeData` where InvId='$PoID'  ";
$StmtPOgradeData=$iconn->query($SqlPOgradeData);

WHILE($RowPOgradeData = $StmtPOgradeData->fetch_assoc()){

$MCS=	$RowPOgradeData['Mcs']; 
   $TotalMcs+=$MCS; 
}

$SqlVessel=" SELECT TranshipDt,ToTOPID as PortofTranshipment,
(SELECT VesselName FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` asc Limit 1) as originatingVessel ,
(SELECT VOYNO FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` asc Limit 1) as originatingVoy ,
(SELECT VesselName FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` desc Limit 1) as FinalImportingVessel ,
(SELECT VOYNO FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` desc Limit 1) as FinalImportingVoy 
FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' ORDER BY `TspID` desc Limit 1;";
	   
$StmtVessel=$iconn->query($SqlVessel);
$RowVessel=$StmtVessel->fetch_assoc();
$VesselName=$RowTenPlusTwo['originatingVessel'];

$ETA = getFormatedDateNA($RowVessel['TranshipDt']);
$TranshipmentDate = getFormatedDateNA($RowVessel['TranshipDt']);
$TranshipmentPort = $RowVessel['PortofTranshipment'];
	   
$OriginatingVesselNumber = $RowVessel['originatingVessel'].' '.$RowVessel['originatingVoy']; 
$FinalVesselNumber = $RowVessel['FinalImportingVessel'].' '.$RowVessel['FinalImportingVoy'];
		
		$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
			 $SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();
			
			$NetWgtKgs = $RowTotal['NetWgtKgs'];
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
		 	//$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

			 $TotalGrossWtKgs = $CustomInvoiceRow['TotalGrossWt'];
			 if($TotalGrossWtKgs!=''){
				 if($QtyUnit=='KGS'){
				 //$GrossWeightKgs=$TotalGrossWtKgs;
				 $GrossWeightLbs=$TotalGrossWtKgs*2.204;
				 }else{
				 //$GrossWeightKgs=$TotalGrossWtKgs*0.454;
				 $GrossWeightLbs=$TotalGrossWtKgs;
				 }
			 }
			 else{
				 //$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];			
				 $GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			 }
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
			 
			 <td colspan="3" align="center" >'.$OriginatingVesselNumber.'</td>
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
			 
			 <td colspan="3" align="center" >'.$FinalVesselNumber.'</td>
			 </tr>
			 
			 <tr> 
             <td colspan="3" align="center" >Port Of Discharge</td>    
			 
			 <td colspan="3" align="center" >'.strtoupper($PortOfDischarge).'   '.$CustomInvoiceRow['CountryCode'].','.strtoupper($CustomInvoiceRow['PortofDischargeCountry']).'</td>
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

					$BrandPrintStatus = $RowBreakup['PrintStatus'];
		
		if($BrandPrintStatus){
			$Brand = $RowBreakup['Brand'];
			}
			else{
			$Brand='-';
			
			}
				
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

header("Content-Type: application/vnd.ms-excel;base64");
header('Content-Disposition: attachment;filename='.$filename.'');
header("Pragma: ");
header("Cache-Control: ");
    
  

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


