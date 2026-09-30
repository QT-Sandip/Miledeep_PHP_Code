<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Includes/Globals.php");

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

$file_name='FBD Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	
	 $SqlPo="SELECT *,SLL.Name as CHA,SLM.LinerName as ShippingCompany,SSD.ExchangeRate as CIExchangeRate,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
	SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SCL.name as DestinationCountry,SP.Name as PortOfLoading FROM $ScrmCustomInvoices as SCI 
  left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
  left JOIN $ScrmShipmentDispatchDetails as SD on SD.PIID=SCI.PiID
  left JOIN $ScrmLogisticsDetails as SLD on SLD.PiID=SCI.PiID

 LEFT JOIN $TblScrmPIData as SPD on SPD.PoID=SCI.PiID
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


 WHERE  CInvDate BETWEEN '$from_date' and '$date_end' $Query  order by CInvNo Asc,CInvDate Asc"; 
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
			  <h3 align="center">Export Information Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="5">
			   <tr>  

           		

                <th  align="center"  width="3%"><B>S No.</B></th>             
				<th  align="center" width="'.$width_s.'%" ><B>Invoice #</B></th>
				<th  align="center"  width="'.$width.'%"><B>Date</B></th>	
				   <th  align="center"  width="'.$width_s.'%"><B>Buyer Order #</B></th>					   
				   <th  align="center"  width="'.$width_s.'%"><B>Buyer Order Date</B></th>
				   <th  align="center"  width="35%"><B>Name of the Buyer</B></th>					   
				   
				   <th  align="center"  width="'.$width.'%"><B>Country</B></th>					   
				   <th  align="center"  width="'.$width.'%"><B>Local Agent</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>Sales Commission @</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>OverSeas Comm</B></th>
				   <th  align="center"  width="'.$width.'%"><B>Sales Commission @</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>Port of Loading</B></th>	
				   <th  align="center"  width="'.$width.'%"><B>CHA</B></th>	
				   <th  align="center"  width="'.$width.'%" <B>Terms</B></th>
				   <th  align="center"  width="'.$width.'%"><B>Port of Discharge</B></th>			

                <th  align="center"  width="'.$width.'%" <B>Final Destination</B></th>
                <th  align="center"  width="'.$width.'%" <B>Net Weight Kgs</B></th>
			    <th  align="center"  width="'.$width.'%" <B>Qty/Mcs</B></th>

                <th  align="center"  width="'.$width.'%" <B>Variety</B></th>
                <th  align="center"  width="'.$width.'%" <B>Product</B></th>
                <th  align="center"  width="'.$width.'%" <B>Pack Type</B></th>
                <th  align="center"  width="'.$width.'%" <B>Sizes</B></th>
                <th  align="center"  width="'.$width.'%" <B>Brand</B></th>
				<th  align="center"  width="'.$width.'%" <B>Production Codes</B></th>

                <th  align="center"  width="'.$width.'%" <B>Invoice Value US$</B></th>';
				
				$content .= ' <th  align="center"  width="'.$width.'%" <font size="2"><B>Terms of Payment</B></font></th>
                <th  align="center"  width="'.$width.'%" <B>Advance Recd in USD</B></th>
                <th  align="center"  width="'.$width.'%" <B>Rate(Advance)</B></th>
                <th  align="center"  width="'.$width.'%" <B>Balance Bill in USD</B></th>
                <th  align="center"  width="'.$width.'%" <B>Rate(Bank)</B></th>
                <th  align="center"  width="'.$width.'%" <B>Total Bill US$</B></th>';
				
               $content .= ' <th  align="center"  width="'.$width.'%" <B>Bill Value INR</B></th>
                <th  align="center"  width="'.$width.'%" <B>Bill Ref No</B></th>
                <th  align="center"  width="'.$width.'%" <B>Bill Discounted Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>Bill Date of Realization</B></th>
                <th  align="center"  width="'.$width.'%" <B>Realization Early Days</B></th>
                <th  align="center"  width="'.$width.'%" <B>Realization Late Days</B></th>
                <th  align="center"  width="'.$width.'%" <B>Discount Details in USD</B></th>
                <th  align="center"  width="'.$width.'%" <B>E-BRC Realized USD</B></th>
                <th  align="center"  width="'.$width.'%" <B>E_BRC_No</B></th>
                <th  align="center"  width="'.$width.'%" <B>E_BRC_Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>Advance Ref No </B></th>
                <th  align="center"  width="'.$width.'%" <B>Advance Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>LC No</B></th>
                <th  align="center"  width="'.$width.'%" <B>LC Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>LC Receipt From</B></th>
                <th  align="center"  width="'.$width.'%" <B>AWB No</B></th>
                <th  align="center"  width="'.$width.'%" <B>AWB Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>Date of Shipment</B></th>
                <th  align="center"  width="'.$width.'%" <B>Road Transporter</B></th>
                <th  align="center"  width="'.$width.'%" <B>Vehicle #</B></th>
                <th  align="center"  width="'.$width.'%" <B>Shipping Company</B></th>
                <th  align="center"  width="'.$width.'%" <B>Container</B></th>
                <th  align="center"  width="'.$width.'%" <B>Freight Rate US $</B></th>
                <th  align="center"  width="'.$width.'%" <B>Ocean Freight Rate INR</B></th>
				';
				//if($Exporter!=5){
					$content .= ' <th  align="center"  width="'.$width.'%" <B>Freight Cost Per Kg</B></th>
                ';
				//}
               $content .= '
				 <th  align="center"  width="'.$width.'%" <B>THC and Others if Extra</B></th>
				 ';
				/*if($Exporter==5){
					$content .= ' <th  align="center"  width="'.$width.'%" <B>CHA Charges</B></th>
                ';
				}*/
               $content .= '
                
                <th  align="center"  width="'.$width.'%" <B>Total Freight INR</B></th>
                <th  align="center"  width="'.$width.'%" <B>Container #</B></th>
                <th  align="center"  width="'.$width.'%" <B>Line Seal #</B></th>
                <th  align="center"  width="'.$width.'%" <B>Entered By</B></th>
				';
				if($Exporter!=5){
					$content .= ' <th  align="center"  width="'.$width.'%" <B>Pack Seal</B></th>
                ';
				}
               $content .= '
                <th  align="center"  width="'.$width.'%" <B>B/L No</B></th>
                <th  align="center"  width="'.$width.'%" <B>Feder Vessel Name/VOY</B></th>
                <th  align="center"  width="'.$width.'%" <B> Vessel ETD</B></th>
                <th  align="center"  width="'.$width.'%" <B> Vessel ETA</B></th>
                <th  align="center"  width="'.$width.'%" <B>Container Gate Out at Destination</B></th>
				';
				//if($Exporter!=5){
					$content .= ' 
					<th  align="center"  width="'.$width.'%" <B>FDA/EU Hold Date</B></th>
					<th  align="center"  width="'.$width.'%" <B>FDA/EU Release/CFIA Date</B></th>
                ';
				//}
				/*ELSE{
               $content .= '
                <th  align="center"  width="'.$width.'%" <B>FDA/EU Release Date</B></th>';
				}*/
                 $content .= ' <th  align="center"  width="'.$width.'%" <B>FDA Entry No</B></th>
                <th  align="center"  width="'.$width.'%" <B>FDA Entry Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>Agent Invoice</B></th>
                <th  align="center"  width="'.$width.'%" <B>Actual A Duty Paid US$</B></th>
                <th  align="center"  width="'.$width.'%" <B>CVD Paid US$</B></th>
                <th  align="center"  width="'.$width.'%" <B>Others HMF US$</B></th>
                <th  align="center"  width="'.$width.'%" <B>Others MPF US$</B></th>
				';
				if($Exporter!=5){
					$content .= ' 
                <th  align="center"  width="'.$width.'%" <B>Others Charges</B></th>
					 ';
				}
				ELSE{
               $content .= '
                <th  align="center"  width="'.$width.'%" <B>Others ALL US$</B></th>';
				}
                 $content .= '
                <th  align="center"  width="'.$width.'%" <B>Total Paid US$</B></th>
                <th  align="center"  width="'.$width.'%" <B>Shipping Bill #</B></th>
                <th  align="center"  width="'.$width.'%" <B>Date</B></th>
				';
				//if($Exporter!=5){
					$content .= ' 
                <th  align="center"  width="'.$width.'%" <B>LET Export Date</B></th>
					 ';
				//}
				/*ELSE{
               $content .= '
                <th  align="center"  width="'.$width.'%" <B>LEO Date</B></th>
';				}*/
                 $content .= '
                <th  align="center"  width="'.$width.'%" <B>FOb Value  as per SB US$</B></th>
                <th  align="center"  width="'.$width.'%" <B>FOb Value  as per SB INR</B></th>
                <th  align="center"  width="'.$width.'%" <B>Drawback Receivable 3%</B></th>
				
				';
				//if($Exporter!=5){
					$content .= ' 
                <th  align="center"  width="'.$width.'%" <B>RODTEP Receivable 2.50%</B></th>
				<th  align="center"  width="'.$width.'%" <B>Drawback/ST Received </B></th>
                <th  align="center"  width="'.$width.'%" <B>Drawback/ST Recd Date</B></th>
				<th  align="center"  width="'.$width.'%" <B>MEIS Received </B></th>
                <th  align="center"  width="'.$width.'%" <B>MEIS APPL.Date</B></th>
				
					 ';
				
                 $content .= '
                  <th  align="center"  width="'.$width.'%" <B>`Q` Certificate #</B></th>
                <th  align="center"  width="'.$width.'%" <B>Date Issue</B></th>
                <th  align="center"  width="'.$width.'%" <B>FOB value INR</B></th>
                <th  align="center"  width="'.$width.'%" <B>Plant Approval #</B></th>
                <th  align="center"  width="'.$width.'%" <B>HC No & Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>GSP No & Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>Co # & Date</B></th>
                <th  align="center"  width="'.$width.'%" <B>Insurance Details</B></th>

				
               <!-- <th  align="center"  width="'.$width.'%" <B>BILLING INFORMATION</B>
				 <table border="1" cellspacing="0" cellpadding="5">
				
				 <tr>
<td align="center"  width="'.$width.'%"> <B>Bill Value INR</B></td>
<td align="center"  width="'.$width.'%"> <B>Bill Ref No</B></td>
<td align="center"  width="'.$width.'%"> <B>Bill Discounted Date</B></td>
<td align="center"  width="'.$width.'%"> <B>Date of Realization</B></td>
</tr></table>
</th>-->
				
                
    
               </tr> 
			   
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
			// print_r($OtherCharges);
          $OtherChargesSum=array_sum($OtherCharges);
			//echo $OtherChargesSum=array_sum($OtherChargesSum);
			$ExporterData=getExporterDetails($ExpID);

			
			$InvNO=$ExporterData['InvShortCode'].$RowPo['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

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
							

		
			 

			
			/*if($AwbNumber=='01-01-1970'){
				
				$QDateIssue='';
			}*/
			if($QDateIssue=='01-01-1970'){
				
				$QDateIssue='';
			}
			
			if($FdaEntryDate=='01-01-1970'){
				
				$FdaEntryDate='';
			}if($FdaNEuReleaseDate=='01-01-1970'){
				
				$FdaNEuReleaseDate='';
			}if($FdaNEuHoldDate=='01-01-1970'){
				
				$FdaNEuHoldDate='';
			}if($ContainerOutDate=='01-01-1970'){
				
				$ContainerOutDate='';
			}if($AwbDate=='01-01-1970'){
				
				$AwbDate='';
			}if($ShipByDate=='01-01-1970'){
				
				$ShipByDate='';
			}if($LcDate=='01-01-1970'){
				
				$LcDate='';
			}if($AdvanceDate=='01-01-1970'){
				
				$AdvanceDate='';
			}
			if($EbrcDate=='01-01-1970'){
				
				$EbrcDate='';
			}if($EbrcDate2=='01-01-1970'){
				
				$EbrcDate2='';
			}if($EbrcDate3=='01-01-1970'){
				
				$EbrcDate3='';
			}
			
			if($CoDate=='01-01-1970'){
				
				$CoDate='';
			}if($GPSDate=='01-01-1970'){
				
				$GPSDate='';
			}if($HCDate=='01-01-1970'){
				
				$HCDate='';
			}if($MeisApplDate=='01-01-1970'){
				
				$MeisApplDate='';
			}if($DrawBackRcvdDate=='01-01-1970'){
				
				$DrawBackRcvdDate='';
			}if($LetExportDate=='01-01-1970'){
				
				$LetExportDate='';
			}if($InvDate=='01-01-1970'){
				
				$InvDate='';
			}
			if($PoDate=='01-01-1970'){
				
				$PoDate='';
			}
			if($BillDiscountDate=='01-01-1970'){
				
				$BillDiscountDate='';
			}
			if($BillRealizationDate=='01-01-1970'){
				
				$BillRealizationDate='';
			}
			
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
			
			$ETD=date('d-m-Y',strtotime($RowPo['ETD']));
			$ETA=date('d-m-Y',strtotime($RowPo['ETA']));

			if($ETD=='01-01-1970'){
				
				$ETD='';
			}if($ETA=='01-01-1970'){
				
				$ETA='';
			}
			
			
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
					 
					// if($DestinationCountryId=='26'){
					 if($CountryName=='China'||$CountryName=='UAE'||$ShippingTerms=='CNF'){
				
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
			
			$BalanceAmountUS=$TotalDiscount-$AdvanceAmount;
			/**uma(16/01/22)**/	
$TotalFOBUS+=$FOBValueUS;
$TotalFOB+=$FOBValue;
$GrandTotalDiscount+=$TotalDiscount;
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
					
					'<tr>
				
                <th  align="center"  width="3%"><font size="2"><B>'.$S_NO.'</B></font></th>              
				<th  align="center" width="'.$width_s.'%" ><font size="2"><B>'.$InvNO.'</B></font></th>
				<th  align="center"  width="'.$width.'%"><font size="2"><B>'.$InvDate.'</B></font></th>	
				   <th  align="center"  width="'.$width_s.'%"><font size="2"><B>'.$RowPo['PoNo'].'</B></font></th>					   
				   <th  align="center"  width="'.$width_s.'%"><font size="2"><B>'.$PoDate.'</B></font></th>
				   <th  align="center"  width="35%"><font size="2"><B>'.$RowPo['ConsigneeName'].'</B></font></th>					   
				   
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.strtoupper($RowPo['DestinationCountry']).'</B></font></th>					   
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.$RowPo['LocalAgentName'].'</B></font></th>			
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.$RowPo['LocalAgentComisn'].'</B></font></th>			
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.$RowPo['OverseasAgent'].'</B></font></th>
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.$RowPo['OverseasAgentComisn'].'</B></font></th>			
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.$RowPo['PortOfLoading'].'</B></font></th>	
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.$RowPo['CHA'].'</B></font></th>	
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.$RowPo['ShippingTerms'].'</B></font></th>
				   <th  align="center"  width="'.$width.'%"><font size="2"><B>'.strtoupper($RowPo['PortofDischarge']).' '.$RowPo['CountryCode'].','.strtoupper($RowPo['PortofDischargeCountry']).'</B></font></th>			

                <th  align="center"  width="'.$width.'%"><font size="2"><B>'.$RowPo['FinalDestination'].'</B></font></th>
				<th  align="center"  width="'.$width.'%"><font size="2"><B>'.number_format($NetWgtKgs,3).'</B></font></th>';
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
			
	
	$content .= '     <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['Mcs'].'</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['ShortName'].' VANNAMEI SHRIMPS</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['FreezingType'].'</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['PackingStyle'].'</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['Grade'].'</font></td>
				 <td  align="left" width="'.$width_s.'%"><font size="2">'.$RowVariety['Brand'].'</font></td>
                 <th  align="center"  width="'.$width.'%" <B></B></th>';}}
				if($VarietyCount!=1){
				$content .= '     <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="left" width="'.$width_s.'%"></td>
                 <th  align="center"  width="'.$width.'%" <B></B></th>';}
		 
				 //}
              $content .= '   <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.number_format($TotalDiscount,2).'</B></font></th>
				
               <th  align="center"  width="'.$width.'%"><font size="1"><B>'.$RowPo['PaymentName'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.number_format($AdvanceAmount,2).'</B></font></th>
               <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['ExchangeRateperUnit'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.number_format($BalanceAmountUS,2).'</B></font></th>
               <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['ExchangeRate'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.number_format($TotalDiscount,2).'</B></font></th>';
             $content .= '   <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.round($FOBValue).'.00</B></font></th>
			
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['BillRefNo'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$BillDiscountDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$BillRealizationDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['RealizationEarlyDate'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['RealizationLateDate'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.number_format($DiscountAmount,2).'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['EbrcReleasedUSD'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$EbrcNumber[0].'<br>'.$EbrcNumber[1].'<br>'.$EbrcNumber[2].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$EbrcDate.'<br>'.$EbrcDate2.'<br>'.$EbrcDate3.'<br>'.$EbrcDate4.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['AdvanceRefNo'].'</B></font></th>
			  <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$AdvanceDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['LcNumber'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$LcDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['LcReceiptFrom'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['AwbNumber'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$AwbDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$ShipByDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['RoadTransport'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['VehicleNumber'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['ShippingCompany'].'</B></font></th>
				
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>40FT</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.number_format($RowPo['FrieghtCharge'],2).'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['OceanFreightINR'].'</B></font></th>';
				
				$content .= '  <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['FreightCostPerKg'].'</B></font></th>';
			$content .= '  <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['ThcNOthers'].'</B></font></th>';
  
              $content .= ' <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$TotalFreight.'</B></font></th> 
			  <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['ContainerNo'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['SealNo'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['UserName'].'</B></font></th>';
				if($Exporter!=5){
				$content .= '     <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['PackSealNo'].'</B></font></th>';}
               $content .= ' <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['BLNo'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$VesselNameVOY.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$ETD.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$ETA.'</B></font></th>
              <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$ContainerOutDate.'</B></font></th>';

           $content .= '   <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$FdaNEuHoldDate.'</B></font></th>
              <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$FdaNEuReleaseDate.'</B></font></th>';//}
			 
             $content .= '   <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['FdaEntryNumber'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$FdaEntryDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['AgentInv'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$ActualDutyPaidUSD.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$CvdPaidUSD.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$OthersHMFUSD.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$OthersMPFUSD.'</B></font></th>';
			 $content .= '    <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$OtherChargesSum.'</B></font></th>';
             
                $content .= '  <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$TotalPaidUSD.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['SBNo'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$SBDate.'</B></font></th>
				           
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$LetExportDate.'</B></font></th>
            <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['FobSbUsd'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['FobSbInr'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['DrawBackRecvd'].'</B></font></th>';
				//if($Exporter!=5){
             $content .= '    <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['RodtepRecvd'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['DrawBackRcvdAmount'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$DrawBackRcvdDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%"><font size="2"> <B>'.$MeisRcvdAmount.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$MeisApplDate.'</B></font></th>';//}
				
              $content .= '  <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowTotal['QCertNo'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$QDateIssue.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['FobVaueINR'].'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowProcessor['ExpApprovalNo'].'</B></font></th>
                 <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$HCNumberDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$GPSNumberGPSDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$CoNumberCoDate.'</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="2"><B>'.$RowPo['InsuranceDtls'].'</B></font></th>
                
			
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
				 <td  align="center" width="3%"></td>

				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$TotalMcs.'</font></td>
				 <td  align="center" width="'.$width_s.'%"><font size="2">'.$ShortName.' VANNAMEI SHRIMPS</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$FreezingType.'</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$PackingStyle.'</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$TotalGrades.'</font></td>
				 <td  align="left" width="'.$width_s.'%"><font size="2">'.$Brand.'</font></td>
                 <td  align="left" width="'.$width_s.'%"><font size="2">'.$DayCode.'</font></td>
				 
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				  <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				  <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				  <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				  <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				   <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 ';
				if($Exporter!=5){
					$content .= '<td  align="center" width="'.$width_s.'%"></td>
                ';
				}
				  
				$content .= ' </tr>';
			}}
				 /**uma**/
				 
				 
				 

 $S_NO++;			 
}

		

		$content .= '</table>';

$filename='Export Information Report -'.$from_date.'-'.$to_date.'.xls';

		header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=".$filename."");

	echo $content;	


?>