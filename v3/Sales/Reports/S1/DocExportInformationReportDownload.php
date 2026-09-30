<style>



th{
	
 font-size: 12px;
  text-align: left;
  background: white;
  position: sticky;
  top: 0;
 left: 260px;
}

.sticky-row {
		top: 0;
    position: -webkit-sticky;
  position: sticky;
  background-color: white;
  
 
}


.sticky-col {
  position: -webkit-sticky;
  position: sticky;
  background-color: white;
  top: 13%;
  
  
 
}

.first-col {
  width: 100px;
  min-width: 100px;
  max-width: 100px;
  left: 0px;
}

.second-col {
  width: 150px;
  min-width: 150px;
  max-width: 150px;
  left: 100px;
}

</style>



<?php
//ini_set( 'display_errors', 1 );

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include($cp."Includes/IncludePage.php");



$QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  /* $Month=$_GET['Month']; 
  $Year=$_GET['Year']; 
 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; */
      $Type=$_GET['Type']; 

   if($Type==2||$Type==1){

  $Month=$_GET['Month']; 
  $Year=$_GET['Year']; 
	$PsID=$_GET['PsID'];
	}
  
 else{
$Month=$_GET['from_date']; 
$Year=$_GET['to_date']; }

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
	  $Header='Export Information Report-('.$monthName.')';

 }
 else{
 $Header='Export Information Report-('.getFormatedDate($Month).' To '.getFormatedDate($Year).')';
	 
 }
$file_name='Export Information Report -'.getFormatedDate($Month).'-'.getFormatedDate($Year).'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	
	 $SqlPo="SELECT *,SCI.PiID as PiID,SLL.Name as CHA,SLM.LinerName as ShippingCompany,SSD.ExchangeRate as CIExchangeRate,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
	SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SCL.name as DestinationCountry,SP.Name as PortOfLoading 
	FROM $ScrmCustomInvoices as SCI 
  left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
  left JOIN $ScrmShipmentDispatchDetails as SD on SD.PIID=SCI.PiID
  left JOIN $ScrmLogisticsDetails as SLD on SLD.PiID=SCI.PiID

 LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
 LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN $TblScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN $TblScrmCountriesList as SL on SL.id=SP.Country
 LEFT JOIN $TblScrmCountriesList as SCL on SCL.id=SPD.DestinationCountry	 
 LEFT JOIN $TblScrmPaymentTerms as SCPT on SCPT.ID=SPD.PaymentTerms	 
left JOIN $ScrmEirAgentNDiscountDetails as SEA on SEA.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmEIRBankNFieghtDetaails as SEB on SEB.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmEirFdaNOtherDetails as SEF on SEF.PoRefNumber=SPD.PoID 
LEFT JOIN $ScrmLogisticsMaster as SLL on SLL.ID=SLD.ConsolidatorNameAddress 
LEFT JOIN $ScrmLinerMaster as SLM on SLM.LinerID=SLD.SteamshipCompany 
LEFT JOIN AdminUserDetails as AUD on AUD.UserID=SEA.EnteredBy 
	 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms


 WHERE  $Query and NameofRepresentative!=''   order by CInvNo Asc,CInvDate Asc"; 
	 //echo $SqlPo; exit;
	
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
			  <h3 align="center">'.$Header.'</h3>
			  
			  
			  <div class="view">
  <div class="wrapper">
			  <table   border="1" cellspacing="0" cellpadding="5">
			   <thead>  

           		

                <td class="sticky-row  first-col" align="center" ><B>S No.</B></td>             
				<td class="sticky-row  second-col" align="center"  ><B>Invoice #</B></td>
				<th  align="center"   ><B>Date</B></th>	
				   <th  align="center"   ><B>Buyer Order #</B></th>					   
				   <th  align="center"   ><B>Buyer Order Date</B></th>
				   <th  align="center"  width="35%"><B>Name of the Buyer</B></th>					   
				   
				   <th  align="center"   ><B>Country</B></th>					   
				   <th  align="center"   ><B>Local Agent</B></th>			
				   <th  align="center"   ><B>Sales Commission @</B></th>			
				   <th  align="center"   ><B>OverSeas Comm</B></th>
				   <th  align="center"   ><B>Sales Commission @</B></th>			
				   <th  align="center"   ><B>Port of Loading</B></th>	
				   <th  align="center"   ><B>CHA</B></th>	
				   <th  align="center"    <B>Terms</B></th>
				   <th  align="center"   ><B>Port of Discharge</B></th>			

                <th  align="center"    <B>Final Destination</B></th>
                <th  align="center"    <B>Net Weight Kgs</B></th>
			    <th  align="center"    <B>Qty/Mcs</B></th>

                <th  align="center"    <B>Variety</B></th>
                <th  align="center"    <B>Product</B></th>
                <th  align="center"    <B>Pack Type</B></th>
                <th  align="center"    <B>Sizes</B></th>
                <th  align="center"    <B>Brand</B></th>
				<th  align="center"    <B>Production Codes</B></th>

                <th  align="center"    <B>Invoice Value US$</B></th>';
				
				$content .= ' <th  align="center"     <B>Terms of Payment</B> </th>
                <th  align="center"    <B>Advance Recd in USD</B></th>
                <th  align="center"    <B>Rate(Advance)</B></th>
                <th  align="center"    <B>Balance Bill in USD</B></th>
                <th  align="center"    <B>Rate(Bank)</B></th>
                <th  align="center"    <B>Total Bill US$</B></th>';
				
               $content .= ' <th  align="center"    <B>Bill Value INR</B></th>
                <th  align="center"    <B>Bill Ref No</B></th>
                <th  align="center"    <B>Bill Discounted Date</B></th>
                <th  align="center"    <B>Bill Date of Realization</B></th>
                <th  align="center"    <B>Realization Early Days</B></th>
                <th  align="center"    <B>Realization Late Days</B></th>
                <th  align="center"    <B>Discount Details in USD</B></th>
                <th  align="center"    <B>E-BRC Realized USD</B></th>
                <th  align="center"    <B>E_BRC_No</B></th>
                <th  align="center"    <B>E_BRC_Date</B></th>
                <th  align="center"    <B>Advance Ref No </B></th>
                <th  align="center"    <B>Advance Date</B></th>
                <th  align="center"    <B>LC No</B></th>
                <th  align="center"    <B>LC Date</B></th>
                <th  align="center"    <B>LC Receipt From</B></th>
                <th  align="center"    <B>AWB No</B></th>
                <th  align="center"    <B>AWB Date</B></th>
                <th  align="center"    <B>Date of Shipment</B></th>
                <th  align="center"    <B>Road Transporter</B></th>
                <th  align="center"    <B>Vehicle #</B></th>
                <th  align="center"    <B>Shipping Company</B></th>
                <th  align="center"    <B>Container</B></th>
                <th  align="center"    <B>Freight Rate US $</B></th>
                <th  align="center"    <B>Ocean Freight Rate INR</B></th>
				';
				//if($Exporter!=5){
					$content .= ' <th  align="center"    <B>Freight Cost Per Kg</B></th>
                ';
				//}
               $content .= '
				 <th  align="center"    <B>THC and Others if Extra</B></th>
				 ';
				/*if($Exporter==5){
					$content .= ' <th  align="center"    <B>CHA Charges</B></th>
                ';
				}*/
               $content .= '
                
                <th  align="center"    <B>Total Freight INR</B></th>
                <th  align="center"    <B>Container #</B></th>
                <th  align="center"    <B>Line Seal #</B></th>
                <th  align="center"    <B>Entered By</B></th>
				';
				if($Exporter!=5){
					$content .= ' <th  align="center"    <B>Pack Seal</B></th>
                ';
				}
               $content .= '
                <th  align="center"    <B>B/L No</B></th>
                <th  align="center"    <B>Feder Vessel Name/VOY</B></th>
                <th  align="center"    <B> Vessel ETD</B></th>
                <th  align="center"    <B> Vessel ETA</B></th>
                <th  align="center"    <B>Container Gate Out at Destination</B></th>
				';
				//if($Exporter!=5){
					$content .= ' 
					<th  align="center"    <B>FDA/EU Hold Date</B></th>
					<th  align="center"    <B>FDA/EU Release/CFIA Date</B></th>
                ';
				//}
				/*ELSE{
               $content .= '
                <th  align="center"    <B>FDA/EU Release Date</B></th>';
				}*/
                 $content .= ' <th  align="center"    <B>FDA Entry No</B></th>
                <th  align="center"    <B>FDA Entry Date</B></th>
                <th  align="center"    <B>Agent Invoice</B></th>
                <th  align="center"    <B>Actual A Duty Paid US$</B></th>
                <th  align="center"    <B>CVD Paid US$</B></th>
                <th  align="center"    <B>Others HMF US$</B></th>
                <th  align="center"    <B>Others MPF US$</B></th>
				';
				if($Exporter!=5){
					$content .= ' 
                <th  align="center"    <B>Others Charges</B></th>
					 ';
				}
				ELSE{
               $content .= '
                <th  align="center"    <B>Others ALL US$</B></th>';
				}
                 $content .= '
                <th  align="center"    <B>Total Paid US$</B></th>
                <th  align="center"    <B>Shipping Bill #</B></th>
                <th  align="center"    <B>Date</B></th>
				';
				//if($Exporter!=5){
					$content .= ' 
                <th  align="center"    <B>LET Export Date</B></th>
					 ';
				//}
				/*ELSE{
               $content .= '
                <th  align="center"    <B>LEO Date</B></th>
';				}*/
                 $content .= '
                <th  align="center"    <B>FOb Value  as per SB US$</B></th>
                <th  align="center"    <B>FOb Value  as per SB INR</B></th>
                <th  align="center"    <B>Drawback Receivable 3%</B></th>
				
				';
				//if($Exporter!=5){
					$content .= ' 
                <th  align="center"    <B>RODTEP Receivable 2.50%</B></th>
				<th  align="center"    <B>Drawback/ST Received </B></th>
                <th  align="center"    <B>Drawback/ST Recd Date</B></th>
				<th  align="center"    <B>MEIS Received </B></th>
                <th  align="center"    <B>MEIS APPL.Date</B></th>
				
					 ';
				
                 $content .= '
                  <th  align="center"    <B>`Q` Certificate #</B></th>
                <th  align="center"    <B>Date Issue</B></th>
                <th  align="center"    <B>FOB value INR</B></th>
                <th  align="center"    <B>Plant Approval #</B></th>
                <th  align="center"    <B>HC No & Date</B></th>
                <th  align="center"    <B>GSP No & Date</B></th>
                <th  align="center"    <B>Co # & Date</B></th>
                <th  align="center"    <B>Insurance Details</B></th>

				
               <!-- <th  align="center"    <B>BILLING INFORMATION</B>
				 <table border="1" cellspacing="0" cellpadding="5">
				
				 <tr>
<td align="center"   > <B>Bill Value INR</B></td>
<td align="center"   > <B>Bill Ref No</B></td>
<td align="center"   > <B>Bill Discounted Date</B></td>
<td align="center"   > <B>Date of Realization</B></td>
</tr></table>
</th>-->
				
                
    </thead>
               </tr><tbody> 
			   
			  '; 
			  
	
		//	
$TotalFOBUS=0;
$TotalFOB=0;
$GrandTotalDiscount=0;
$TotalNetWgtKgs=0;
$TotalNetWgtLbs=0;
			$OtherChargesSum=0;


		while($RowPo = $StmtPo->fetch_array()){
						$PoID=$RowPo['PiID'];

include "../SalesInculdes/SalesConstants.php"; 

			$ProcessorID=$RowPo['ProcessorID'];
			$ExpID=$RowPo['ExporterID'];
			$Discount=$RowPo['Discount'];
			$PiID=$RowPo['PiID'];
			 $FrieghtCharge=$RowPo['FrieghtCharge'];
			 $DestinationCountryId=$RowPo['DestinationCountry']; 
			$CInvCode=$RowPo['CInvCode'];
			$IGST=$RowPo['IGST'];
			$ShippingTerms=$RowPo['ShippingTerms'];
			$CountryName=$RowPo['DestinationCountry'];
			$AdvanceAmount=$RowPo['AdvanceAmount'];
			$PortOfLoading=$RowPo['PortOfLoading'];
			$ActualDutyPaidUSD=$RowPo['ActualDutyPaidUSD'];
			$CvdPaidUSD=$RowPo['CvdPaidUSD'];
			$OthersHMFUSD=$RowPo['OthersHMFUSD'];
			$OthersMPFUSD=$RowPo['OthersMPFUSD'];
			$ExchangeRate=$RowPo['CIExchangeRate'];
			$AwbNumber=$RowPo['AwbNumber'];
			$MeisRcvdAmount=$RowPo['MeisRcvdAmount'];
			$SBDate=date('d-m-Y',strtotime($RowPo['SBDate']));
			$TotalFreight=$RowPo['ThcNOthers']+$RowPo['OceanFreightINR'];
			 $OtherCharges=explode(',',$RowPo['OtherCharges']);
			 $NameofRepresentative =$RowPo['NameofRepresentative '];

			// print_r($OtherCharges);
          $OtherChargesSum=array_sum($OtherCharges);
			//echo $OtherChargesSum=array_sum($OtherChargesSum);
			$ExporterData=getExporterDetails($ExpID);

			$CurrentYear=Date(Y);
			$NxtYear=$CurrentYear+1;
						 $InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);

			//$InvNO=$ExporterData['InvShortCode'].$RowPo['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

			$InvDate=getFormatedDate($RowPo['CInvDate']);
			$PoDate=getFormatedDate($RowPo['PoDate']);
			$BillDiscountDate=getFormatedDate($RowPo['BillDiscountDate']);
			$BillRealizationDate=getFormatedDate($RowPo['BillRealizationDate']);
			
			$LetExportDate=getFormatedDate($RowPo['LetExportDate']);
			$DrawBackRcvdDate=getFormatedDate($RowPo['DrawBackRcvdDate']);
			$MeisApplDate=getFormatedDate($RowPo['MeisApplDate']);
			$HCDate=getFormatedDate($RowPo['HCDate']);
			$GPSDate=getFormatedDate($RowPo['GPSDate']);
			$CoDate=getFormatedDate($RowPo['CoDate']);
			$EbrcDate=getFormatedDate($RowPo['EbrcDate']);
			$EbrcDate2=getFormatedDate($RowPo['EbrcDate2']);
			$EbrcDate3=getFormatedDate($RowPo['EbrcDate3']);
			$EbrcDate4=getFormatedDate($RowPo['EbrcDate4']);
			
			$AdvanceDate=getFormatedDate($RowPo['AdvanceDate']);
			$LcDate=getFormatedDate($RowPo['LcDate']);
			$AwbDate=getFormatedDate($RowPo['AwbDate']);
			$ShipByDate=getFormatedDate($RowPo['ShipByDate']);
			$ContainerOutDate=getFormatedDate($RowPo['ContainerOutDate']);
			$FdaNEuHoldDate=getFormatedDate($RowPo['FdaNEuHoldDate']);
			$FdaNEuReleaseDate=getFormatedDate($RowPo['FdaNEuReleaseDate']);
			$FdaEntryDate=getFormatedDate($RowPo['FdaEntryDate']);
			$QDateIssue=getFormatedDate($RowPo['QDateIssue']);
			$EbrcNumber=explode(',',$RowPo['EbrcNumber']);
							

			
			$HCNumberDate=$RowPo['HCNumber'].' / '.$HCDate;
			$GPSNumberGPSDate=$RowPo['GPSNumber'].' / '.$GPSDate;
			$CoNumberCoDate=$RowPo['CoNumber'].' / '.$CoDate;
				
				if($RowPo['HCNumber']==''&&$HCDate==''){
				
			$HCNumberDate='';
			}if($RowPo['GPSNumber']==''&&$GPSDate==''){
				
			$GPSNumberGPSDate='';
			}if($RowPo['CoNumber']==''&&$CoDate==''){
				
			$CoNumberCoDate='';
			}
					$ETD=getFormatedDateNA($RowPo['ETD']);
					$ETA=getFormatedDateNA($RowPo['ETA']);

			
			$TotalPaidUSD=$OtherChargesSum+$ActualDutyPaidUSD+$CvdPaidUSD+$OthersHMFUSD+$OthersMPFUSD;

$SqlTenplus="SELECT * FROM `$ScrmLogisticsDetails` WHERE PiID='$PiID'";
$StmtTenPlus=$iconn->query($SqlTenplus);
$RowTenPlus=$StmtTenPlus->fetch_array();

			$VesselNameVOY=$RowTenPlus['VesselName'].'/'.$RowTenPlus['VOY'];
			
			$SqlProcessor="SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$ProcessorID'";
			$StmtProcessor=$iconn->query($SqlProcessor);
			$RowProcessor=$StmtProcessor->fetch_array();
			
			/********uma********/
			$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,QCertNo";
			
			
			 $SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `$ScrmCIGradeData` 
			as PGD 
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID 
			left join QcCoveringLetter as QCL on QCL.InvID=PGD.InvId 
			WHERE PGD.InvId='$PiID'"; 
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();
			
			
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			
			 $RowPo['QtyUnit'];
			
			if( $RowPo['QtyUnit']=='KGS'){				
				$NetWgtKgs = $RowTotal['NetWgtKgs'];	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$NetWgtKgs = $RowTotal['NetWgtLbs'] *0.454;
			
				$Price = $RowTotal['PriceLbs'];
			}
			
			
			$UsdValue=$Price; 
		
			$AryPaymentData=getPaymentValues($iconn,$PiID);
			$InsuranceValue=$AryPaymentData['InsuranceValue'];
			$ExchangeRate=$AryPaymentData['ExchangeRate'];
			$DiscountPerc=$AryPaymentData['DiscountPerc'];
			$DiscountAmount=$AryPaymentData['DiscountAmount'];
			$IgstPerc=$AryPaymentData['IgstPerc'];
			$IgstValue=$AryPaymentData['IgstValue'];
			$InvValueUSD=$AryPaymentData['InvValueUSD'];
			$InvValueInr=$AryPaymentData['InvValueINR'];
			
			$TotalMcs=$AryPaymentData['TotalMcs'];
			$Price=$AryPaymentData['Price'];
		

if($RowPo['ShowFrightCharge']==1 && $RowPo['ShowFrieght']==1){
		$FOBValueUSD=$AryPaymentData['FOBValueUSD'];
		$FOBValueINR=$AryPaymentData['FOBValueINR'];	
		}
		
		else{
			
		
		$FOBValueUSD=$AryPaymentData['InvValueUSDWithOutDuties'];
$FOBValueINR=$AryPaymentData['InvValueINRWithOutDuties'];
		}
			
			
			
			/*$DiscountAmount=($Price*$Discount)/100; 
			
			IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}
				
			
					 						 

					 if($ShippingTerms=='CIF'){
									$InvPrice= ($TotalDiscount*0.11)/100;
									$TotalDiscount=$TotalDiscount-$InvPrice;
								 }
								 else{
								$TotalDiscount=$TotalDiscount;	 
								 }
								 
							
					 
					 $DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35; 
					 $DutiesPrice=$DutiesPrice+125;
					 
					
					 if($CountryName=='China'||$CountryName=='UAE'||$ShippingTerms=='CNF'){
				
						$FOBValue= ($TotalDiscount-$FrieghtCharge)*$ExchangeRate;
						$FOBValueUS=$TotalDiscount-$FrieghtCharge;
					 }
					 ELSE{
						 
				$FOBValue=	($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate;	 
				$FOBValueUS=	$TotalDiscount-$FrieghtCharge-$DutiesPrice;	 
				
					 }
					 
					 IF($IGST=='DISCOUNT'){
			 $TotalDiscount=$Price-$DiscountAmount;	
			}
			else{
			 $TotalDiscount=$Price;	
			}*/
			
			$BalanceAmountUS=$InvValueUSD-$AdvanceAmount;
			/**uma(16/01/22)**/	
$TotalFOBUS+=$FOBValueUSD;
$TotalFOB+=$FOBValueINR;
$GrandTotalDiscount+=$InvValueUSD;
$TotalNetWgtKgs+=$NetWgtKgs;
$TotalNetWgtLbs+=$NetWgtLbs;
			
		/***18/07/2022**/	
		//$SqlVarietyId="SELECT distinct(VarietyID) FROM `$ScrmCIGradeData`  WHERE InvId='$PiID'"; 
		$SqlVarietyId="SELECT distinct(PackingStyleID),VarietyID FROM `$ScrmCIGradeData` WHERE InvId='$PiID'"; 
		$StmtvarietyId=$iconn->query($SqlVarietyId);
		$VarietyCount=mysqli_num_rows($StmtvarietyId);
			
					
		
			/***18/07/2022**/
			
			
			
			/*********uma*******/
			
		    $content .= 
					
					' <tr>
				
                <td class="sticky-col first-col" align="center"  > <B>'.$S_NO.'</B> </td> 
				
				<td class="sticky-col second-col" align="center"   > <B>'.$InvNO.'</B> </td>
				<td  align="center"   > <B>'.$InvDate.'</B> </td>';
				 if($NameofRepresentative==''){ 
				$content .=  '<td  align="center"  ><font color="red">'.$RowPo['PoNo'].'</font> </td>';
				 }
				 
				 else{ 
				$content .=  '<td  align="center"  >'.$RowPo['PoNo'].'</td>';
				 }

				$content .= '	
				   <td  align="center"   > <B>'.$PoDate.'</B> </td>
				   <td  align="center"  width="35%"> <B>'.$RowPo['ConsigneeName'].'</B> </td>					   
				   
				   <td  align="center"   > <B>'.strtoupper($RowPo['DestinationCountry']).'</B> </td>					   
				   <td  align="center"   > <B>'.$RowPo['LocalAgentName'].'</B> </td>			
				   <td  align="center"   > <B>'.$RowPo['LocalAgentComisn'].'</B> </td>			
				   <td  align="center"   > <B>'.$RowPo['OverseasAgent'].'</B> </td>
				   <td  align="center"   > <B>'.$RowPo['OverseasAgentComisn'].'</B> </td>			
				   <td  align="center"   > <B>'.$RowPo['PortOfLoading'].'</B> </td>	
				   <td  align="center"   > <B>'.$RowPo['CHA'].'</B> </td>	
				   <td  align="center"   > <B>'.$RowPo['ShippingTerms'].'</B> </td>
				   <td  align="center"   > <B>'.strtoupper($RowPo['PortofDischarge']).' '.$RowPo['CountryCode'].','.strtoupper($RowPo['PortofDischargeCountry']).'</B> </td>			

                <td  align="center"   > <B>'.$RowPo['FinalDestination'].'</B> </td>
				<td  align="center"   > <B>'.number_format($NetWgtKgs,3).'</B> </td>';
if($VarietyCount==1){
			   while($RowVarietyId=$StmtvarietyId->fetch_array()){

	
				 $VarietyID= $RowVarietyId['VarietyID'];
				 $PackingStyleID= $RowVarietyId['PackingStyleID'];

$SqlVariety="SELECT ShortName,sum(Mcs) as Mcs,FreezingType,PackingStyle,Brand,Grade FROM `$ScrmCIGradeData` SPDT LEFT join 
			$ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID LEFT join 
			$TblAdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID 
			WHERE SPDT.InvId='$PiID' and SPDT.VarietyID='$VarietyID'and SPDT.PackingStyleID='$PackingStyleID'"; 
			$Stmtvariety=$iconn->query($SqlVariety);	
			$RowVariety=$Stmtvariety->fetch_array();
			
	
	$content .= '     <td  align="center"   > '.$RowVariety['Mcs'].' </td>
				 <td  align="center"   > '.$RowVariety['ShortName'].' VANNAMEI SHRIMPS </td>
				 <td  align="center"   > '.$RowVariety['FreezingType'].' </td>
				 <td  align="center"   > '.$RowVariety['PackingStyle'].' </td>
				 <td  align="center"   > '.$RowVariety['Grade'].' </td>
				 <td  align="left"  > '.$RowVariety['Brand'].' </td>
                 <td  align="center"    <B></B></td>';}}
				if($VarietyCount!=1){
				$content .= '     <td  align="center"   ></td>
				 <td  align="center"  ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				 <td  align="left"  ></td>
                 <td  align="center"    <B></B></td>';}
		 
				 //}
              $content .= '   <td  align="center"    > <B>'.number_format($InvValueUSD,2).'</B> </td>
				
               <td  align="center"   ><B>'.$RowPo['PaymentName'].'</B> </td>
                <td  align="center"    > <B>'.number_format($AdvanceAmount,2).'</B> </td>
               <td  align="center"    > <B>'.$RowPo['ExchangeRateperUnit'].'</B> </td>
                <td  align="center"    > <B>'.number_format($BalanceAmountUS,2).'</B> </td>
               <td  align="center"    > <B>'.$RowPo['ExchangeRate'].'</B> </td>
                <td  align="center"    > <B>'.number_format($InvValueUSD,2).'</B> </td>';
             $content .= '   <td  align="center"    > <B>'.round($FOBValueINR).'.00</B> </td>
			
                <td  align="center"    > <B>'.$RowPo['BillRefNo'].'</B> </td>
                <td  align="center"    > <B>'.$BillDiscountDate.'</B> </td>
                <td  align="center"    > <B>'.$BillRealizationDate.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['RealizationEarlyDate'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['RealizationLateDate'].'</B> </td>
                <td  align="center"    > <B>'.number_format($DiscountAmount,2).'</B> </td>
                <td  align="center"    > <B>'.$RowPo['EbrcReleasedUSD'].'</B> </td>
                <td  align="center"    > <B>'.$EbrcNumber[0].'<br>'.$EbrcNumber[1].'<br>'.$EbrcNumber[2].'</B> </td>
                <td  align="center"    > <B>'.$EbrcDate.'<br>'.$EbrcDate2.'<br>'.$EbrcDate3.'<br>'.$EbrcDate4.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['AdvanceRefNo'].'</B> </td>
			  <td  align="center"    > <B>'.$AdvanceDate.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['LcNumber'].'</B> </td>
                <td  align="center"    > <B>'.$LcDate.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['LcReceiptFrom'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['AwbNumber'].'</B> </td>
                <td  align="center"    > <B>'.$AwbDate.'</B> </td>
                <td  align="center"    > <B>'.$ShipByDate.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['RoadTransport'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['VehicleNumber'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['ShippingCompany'].'</B> </td>
				
                <td  align="center"    > <B>40FT</B> </td>
                <td  align="center"    > <B>'.number_format($RowPo['FrieghtCharge'],2).'</B> </td>
                <td  align="center"    > <B>'.$RowPo['OceanFreightINR'].'</B> </td>';
				
				$content .= '  <td  align="center"    > <B>'.$RowPo['FreightCostPerKg'].'</B> </td>';
			$content .= '  <td  align="center"    > <B>'.$RowPo['ThcNOthers'].'</B> </td>';
  
              $content .= ' <td  align="center"    > <B>'.$TotalFreight.'</B> </td> 
			  <td  align="center"    > <B>'.$RowPo['ContainerNo'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['SealNo'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['UserName'].'</B> </td>';
				if($Exporter!=5){
				$content .= '     <td  align="center"    > <B>'.$RowPo['PackSealNo'].'</B> </td>';}
               $content .= ' <td  align="center"    > <B>'.$RowPo['BLNo'].'</B> </td>
                <td  align="center"    > <B>'.$VesselNameVOY.'</B> </td>
                <td  align="center"    > <B>'.$ETD.'</B> </td>
                <td  align="center"    > <B>'.$ETA.'</B> </td>
              <td  align="center"    > <B>'.$ContainerOutDate.'</B> </td>';

           $content .= '   <td  align="center"    > <B>'.$FdaNEuHoldDate.'</B> </td>
              <td  align="center"    > <B>'.$FdaNEuReleaseDate.'</B> </td>';//}
			 
             $content .= '   <td  align="center"    > <B>'.$RowPo['FdaEntryNumber'].'</B> </td>
                <td  align="center"    > <B>'.$FdaEntryDate.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['AgentInv'].'</B> </td>
                <td  align="center"    > <B>'.$ActualDutyPaidUSD.'</B> </td>
                <td  align="center"    > <B>'.$CvdPaidUSD.'</B> </td>
                <td  align="center"    > <B>'.$OthersHMFUSD.'</B> </td>
                <td  align="center"    > <B>'.$OthersMPFUSD.'</B> </td>';
			 $content .= '    <td  align="center"    > <B>'.$OtherChargesSum.'</B> </td>';
             
                $content .= '  <td  align="center"    > <B>'.$TotalPaidUSD.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['SBNo'].'</B> </td>
                <td  align="center"    > <B>'.$SBDate.'</B> </td>
				           
                <td  align="center"    > <B>'.$LetExportDate.'</B> </td>
            <td  align="center"    > <B>'.$RowPo['FobSbUsd'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['FobSbInr'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['DrawBackRecvd'].'</B> </td>';
				//if($Exporter!=5){
             $content .= '    <td  align="center"    > <B>'.$RowPo['RodtepRecvd'].'</B> </td>
                <td  align="center"    > <B>'.$RowPo['DrawBackRcvdAmount'].'</B> </td>
                <td  align="center"    > <B>'.$DrawBackRcvdDate.'</B> </td>
                <td  align="center"   >  <B>'.$MeisRcvdAmount.'</B> </td>
                <td  align="center"    > <B>'.$MeisApplDate.'</B> </td>';//}
				
              $content .= '  <td  align="center"    > <B>'.$RowTotal['QCertNo'].'</B> </td>
                <td  align="center"    > <B>'.$QDateIssue.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['FobVaueINR'].'</B> </td>
                <td  align="center"    > <B>'.$RowProcessor['ExpApprovalNo'].'</B> </td>
                 <td  align="center"    > <B>'.$HCNumberDate.'</B> </td>
                <td  align="center"    > <B>'.$GPSNumberGPSDate.'</B> </td>
                <td  align="center"    > <B>'.$CoNumberCoDate.'</B> </td>
                <td  align="center"    > <B>'.$RowPo['InsuranceDtls'].'</B> </td>
                
			
				 </tr>';
				
				 /**uma**/
				 
				
						$StmtvarietyId=$iconn->query($SqlVarietyId);	
if($VarietyCount>1){

			while($RowVarietyId=$StmtvarietyId->fetch_array()){

	
				 $VarietyID= $RowVarietyId['VarietyID'];
				 $PackingStyleID= $RowVarietyId['PackingStyleID'];

		
		$SqlVariety="SELECT ShortName, Mcs,FreezingType,PackingStyle,Brand,Grade FROM `$ScrmCIGradeData` SPDT LEFT join 
			$ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID LEFT join 
			$TblAdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID 
			WHERE SPDT.InvId='$PiID' and SPDT.VarietyID='$VarietyID'and SPDT.PackingStyleID='$PackingStyleID'"; 
			$Stmtvariety=$iconn->query($SqlVariety);
			$TotalGrades='';
			$TotalMcs='';
			$ary_Mcs='';
		while($RowVariety=$Stmtvariety->fetch_array()){
			$Grades=$RowVariety['Grade'];
			$Mcs=$RowVariety['Mcs'];
			$ShortName=$RowVariety['ShortName'];
			$PackingStyle=$RowVariety['PackingStyle'];
			$FreezingType=$RowVariety['FreezingType'];
			$Brand=$RowVariety['Brand'];
			
  if(!in_array($Grades,$ary_code,true)){ 
		$ary_code[]=$Grades;
	  
	  IF($TotalGrades==''){
		  $TotalGrades=$Grades;
	  }
	  else{
		  $TotalGrades=$TotalGrades.','.$Grades;
	  }  
	  }else{
	  //echo $DAY_CODE;
	  }
	  
	  if(!in_array($Mcs,$ary_Mcs,true)){ 
		$ary_Mcs[]=$Mcs;
	  
	  IF($TotalMcs==''){
		  $TotalMcs=$Mcs;
	  }
	  else{
		  $TotalMcs=$TotalMcs.','.$Mcs;
	  }  
	  }else{
	  //echo $DAY_CODE;
	  }
			
		}
		    $content .= 
					
					'<tr>
				 <td  align="center" ></td>

				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"   > '.$TotalMcs.' </td>
				 <td  align="center"  > '.$ShortName.' VANNAMEI SHRIMPS </td>
				 <td  align="center"   > '.$FreezingType.' </td>
				 <td  align="center"   > '.$PackingStyle.' </td>
				 <td  align="center"   > '.$TotalGrades.' </td>
				 <td  align="left"  > '.$Brand.' </td>
                 <td  align="left"  > '.$DayCode.' </td>
				 
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				  <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				  <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				  <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				 <td  align="center"   ></td>
				  <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				   <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 ';
				if($Exporter!=5){
					$content .= '<td  align="center"  ></td>
                ';
				}
				  
				$content .= ' </tr>';
			}}
				 /**uma**/
				 
				 
				 

 $S_NO++;			 
}

		

		$content .= ' </tbody></table></div></div>';
$filename=$Header.'.xls';

header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=".$filename."");

	echo $content;	


?>