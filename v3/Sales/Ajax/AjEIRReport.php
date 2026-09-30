<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
?>
<!--<link href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet" />-->
<link href="https://cdn.datatables.net/fixedcolumns/3.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet" />
<?php

$DeptID=$_POST['Dept'];
$SubDept=$_POST['SubDept'];
$Month=$_POST['Month'];
$Year=$_POST['Year'];
$Type=$_POST['Type'];

 $date_start=$Year."-".$Month."-01";
	$date_end=$Year."-".$Month."-31 23:59:59";

if($Type!='List'){

	$ary_prod_codes=array();

	
	
?>	<option value="">All Sub Departments</option> <?php

				
					

}else{
	$SqlPo="SELECT *,SLD.VesselName,SLD.VOY,SLL.Name as CHA,SLM.LinerName as ShippingCompany,SSD.ExchangeRate as CIExchangeRate,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
	SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SCL.name as DestinationCountry,SP.Name as PortOfLoading FROM $ScrmCustomInvoices as SCI 
  left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID

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

left join ScrmLogisticsDetails as SLD on SLD.PiID=SCI.PiID  
LEFT JOIN $ScrmLogisticsMaster as SLL on SLL.ID=SLD.ConsolidatorNameAddress
LEFT JOIN $ScrmLinerMaster as SLM on SLM.LinerID=SLD.SteamshipCompany  
LEFT JOIN AdminUserDetails as AUD on AUD.UserID=SEA.EnteredBy 
 order by CInvNo Asc,CInvDate Asc";  	
	$StmtPo=$iconn->query($SqlPo);
	$width_s="100";
	$width="100";
	//SSDD.ETD,SSDD.ETA,
	 //left JOIN ScrmShipmentDispatchDetails as SSDD on SSDD.PIID=SCI.PiID
	 $data=' <table id="MonAttTable" class="no-wrap table-bordered" 
	 style="background:#fff;margin-top:6px !important;margin-bottom:-6px !important; height:100px"
	>
   <thead>
   
     

           		

                <th  width="30px"; ><B>S No.</B></th>             
				<th  ><B>Invoice #</B></th>
				<th  ><B>Date</B></th>	
				   <th ><B>Buyer Order #</B></th>					   
				   <th  ><B>Buyer Order Date</B></th>
				   <th ><B>Name of the Buyer</B></th>					   
				   
				   <th align="center" ; ><B>Country</B></th>					   
				   <th ><B>Local Agent</B></th>			
				   <th ><B>Sales Commission @</B></th>			
				   <th ><B>OverSeas Comm</B></th>
				   <th ><B>Sales Commission @</B></th>			
				   <th ><B>Port of Loading</B></th>	
				   <th ><B>CHA</B></th>	
				   <th>  <B>Terms</B></th>
				   <th ><B>Port of Discharge</B></th>			

                <th>  <B>Final Destination</B></th>
                <th > <B>Net Weight Kgs</B></th>
			    <th>  <B>Qty/Mcs</B></th>

                <th>  <B>Variety</B></th>
                <th>  <B>Product</B></th>
                <th>  <B>Pack Type</B></th>
                <th > <B>Sizes</B></th>
                <th  ><B>Brand</B></th>
				<th  ><B>Production Codes</B></th>

                <th>  <B>Invoice Value US$</B></th>';
				
				$data .= ' <th  <font size="2"><B>Terms of Payment</B></font></th>
                <th>  <B>Advance Recd in USD</B></th>
                <th>  <B>Rate(Advance)</B></th>
                <th>  <B>Balance Bill in USD</B></th>
                <th>  <B>Rate(Bank)</B></th>
                <th>  <B>Total Bill US$</B></th>';
				
               $data .= ' <th>  <B>Bill Value INR</B></th>
                <th>  <B>Bill Ref No</B></th>
                <th>  <B>Bill Discounted Date</B></th>
                <th>  <B>Bill Date of Realization</B></th>
                <th>  <B>Realization Early Days</B></th>
                <th>  <B>Realization Late Days</B></th>
                <th>  <B>Discount Details in USD</B></th>
                <th>  <B>E-BRC Realized USD</B></th>
                <th>  <B>E_BRC_No</B></th>
                <th>  <B>E_BRC_Date</B></th>
                <th>  <B>Advance Ref No </B></th>
                <th>  <B>Advance Date</B></th>
                <th>  <B>LC No</B></th>
                <th>  <B>LC Date</B></th>
                <th>  <B>LC Receipt From</B></th>
                <th>  <B>AWB No</B></th>
                <th>  <B>AWB Date</B></th>
                <th>  <B>Date of Shipment</B></th>
                <th>  <B>Road Transporter</B></th>
                <th>  <B>Vehicle #</B></th>
                <th>  <B>Shipping Company</B></th>
                <th>  <B>Container</B></th>
                <th>  <B>Freight Rate US $</B></th>
                <th>  <B>Ocean Freight Rate INR</B></th>
			    <th>  <B>Freight Cost Per Kg</B></th>
                <th>  <B>THC and Others if Extra</B></th>
				<th>  <B>Total Freight INR</B></th>
                <th>  <B>Container #</B></th>
                <th>  <B>Line Seal #</B></th>
                <th>  <B>Entered By</B></th>
				';
				if($Exporter!=5){
					$data .= ' <th>  <B>Pack Seal</B></th>
                ';
				}
               $data .= '
                <th>  <B>B/L No</B></th>
                <th>  <B>Feder Vessel Name/VOY</B></th>
                <th>  <B> Vessel ETD</B></th>
                <th>  <B> Vessel ETA</B></th>
                <th>  <B>Container Gate Out at Destination</B></th>
				<th>  <B>FDA/EU Hold Date</B></th>
				<th>  <B>FDA/EU Release/CFIA Date</B></th>
                <th>  <B>FDA Entry No</B></th>
                <th>  <B>FDA Entry Date</B></th>
                <th>  <B>Agent Invoice</B></th>
                <th>  <B>Actual A Duty Paid US$</B></th>
                <th>  <B>CVD Paid US$</B></th>
                <th>  <B>Others HMF US$</B></th>
                <th>  <B>Others MPF US$</B></th>
				';
				if($Exporter!=5){
					$data .= ' 
                <th>  <B>Others Charges</B></th>
					 ';
				}ELSE{
               $data .= '
                <th>  <B>Others ALL US$</B></th>';
				}
                 $data .= '
                <th>  <B>Total Paid US$</B></th>
                <th>  <B>Shipping Bill #</B></th>
                <th>  <B>Date</B></th>
                <th>  <B>LET Export Date</B></th>	
                <th>  <B>FOb Value  as per SB US$</B></th>
                <th>  <B>FOb Value  as per SB INR</B></th>
                <th>  <B>Drawback Receivable 3%</B></th> 
                <th>  <B>RODTEP Receivable 2.50%</B></th>
				<th>  <B>Drawback/ST Received </B></th>
                <th>  <B>Drawback/ST Recd Date</B></th>
				<th>  <B>MEIS Received </B></th>
                <th>  <B>MEIS APPL.Date</B></th>
				<th>  <B>`Q` Certificate #</B></th>
                <th>  <B>Date Issue</B></th>
                <th>  <B>FOB value INR</B></th>
                <th>  <B>Plant Approval #</B></th>
                <th>  <B>HC No & Date</B></th>
                <th>  <B>GSP No & Date</B></th>
                <th>  <B>Co # & Date</B></th>
                <th>  <B>Insurance Details</B></th>';
				
 
               


		
$data .=' </thead>
                <tbody>';

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
			$VesselNameVOY=$RowPo['VesselName'].'/'.$RowPo['VOY'];
			
			$TotalFreight=$RowPo['ThcNOthers']+$RowPo['OceanFreightINR'];
			$OtherCharges=explode(',',$RowPo['OtherCharges']);
			$OtherChargesSum=array_sum($OtherCharges);
			$ExporterData=getExporterDetails($ExpID);

			
			$InvNO=$ExporterData['InvShortCode'].$RowPo['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

			$SBDate=getFormatedDate($RowPo['SBDate']);
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
			
			$AdvanceDate=getFormatedDate($RowPo['AdvanceDate']);
			$LcDate=getFormatedDate($RowPo['LcDate']);
			$AwbDate=getFormatedDate($RowPo['AwbDate']);
			$ShipByDate=getFormatedDate($RowPo['ShipByDate']);
			$ContainerOutDate=getFormatedDate($RowPo['ContainerOutDate']);
			$FdaNEuHoldDate=getFormatedDate($RowPo['FdaNEuHoldDate']);
			$FdaNEuReleaseDate=getFormatedDate($RowPo['FdaNEuReleaseDate']);
			$FdaEntryDate=getFormatedDate($RowPo['FdaEntryDate']);
			$QDateIssue=getFormatedDate($RowPo['QDateIssue']);
			$ETD=getFormatedDate($RowPo['ETD']);
			$ETA=getFormatedDate($RowPo['ETA']);
			
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
			
			$TotalPaidUSD=$OtherChargesSum+$ActualDutyPaidUSD+$CvdPaidUSD+$OthersHMFUSD+$OthersMPFUSD;

				

			

		
		
			$RowProcessor=$ExporterData=getExporterDetails($ProcessorID);
			
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
			
		    $data .= 
					
					'<tr>
				
                <td ><font size="2"><B>'.++$S_NO.'</B></font></td>              
				<td  align="center" width="'.$width_s.'%" ><font size="2"><B style="color: blue">'.$InvNO.'</B></font></td>
				<td ><font size="2"><B>'.$InvDate.'</B></font></td>	
				   <td  align="center"  width="'.$width_s.'%"><font size="2"><B>'.$RowPo['PoNo'].'</B></font></td>					   
				   <td  align="center"  width="'.$width_s.'%"><font size="2"><B>'.$PoDate.'</B></font></td>
				   <td  align="center"  width="35%"><font size="2"><B>'.$RowPo['ConsigneeName'].'</B></font></td>					   
				   
				   <td ><font size="2"><B>'.strtoupper($RowPo['DestinationCountry']).'</B></font></td>					   
				   <td ><font size="2"><B>'.$RowPo['LocalAgentName'].'</B></font></td>			
				   <td ><font size="2"><B>'.$RowPo['LocalAgentComisn'].'</B></font></td>			
				   <td ><font size="2"><B>'.$RowPo['OverseasAgent'].'</B></font></td>
				   <td ><font size="2"><B>'.$RowPo['OverseasAgentComisn'].'</B></font></td>			
				   <td ><font size="2"><B>'.$RowPo['PortOfLoading'].'</B></font></td>	
				   <td ><font size="2"><B>'.$RowPo['CHA'].'</B></font></td>	
				   <td ><font size="2"><B>'.$RowPo['ShippingTerms'].'</B></font></td>
				   <td ><font size="2"><B>'.strtoupper($RowPo['PortofDischarge']).' '.$RowPo['CountryCode'].','.strtoupper($RowPo['PortofDischargeCountry']).'</B></font></td>			

                <td ><font size="2"><B>'.$RowPo['FinalDestination'].'</B></font></td>
				<td ><font size="2"><B>'.number_format($NetWgtKgs,3).'</B></font></td>';
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
			
	
	$data .= '<td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['Mcs'].'</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['ShortName'].' VANNAMEI SHRIMPS</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['FreezingType'].'</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['PackingStyle'].'</font></td>
				 <td  align="center" width="'.$width_s.'%" ><font size="2">'.$RowVariety['Grade'].'</font></td>
				 <td  align="left" width="'.$width_s.'%"><font size="2">'.$RowVariety['Brand'].'</font></td>
                 <td  <B></B></td>';}}
				if($VarietyCount!=1){
				$data .= '     <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="left" width="'.$width_s.'%"></td>
                 <td  <B></B></td>';}
		 
				 //}
              $data .= '   <td  ><font size="2"><B>'.number_format($TotalDiscount,2).'</B></font></td>
				
               <td ><font size="1"><B>'.$RowPo['PaymentName'].'</B></font></td>
                <td  ><font size="2"><B>'.number_format($AdvanceAmount,2).'</B></font></td>
               <td  ><font size="2"><B>'.$RowPo['ExchangeRateperUnit'].'</B></font></td>
                <td  ><font size="2"><B>'.number_format($BalanceAmountUS,2).'</B></font></td>
               <td  ><font size="2"><B>'.$RowPo['ExchangeRate'].'</B></font></td>
                <td  ><font size="2"><B>'.number_format($TotalDiscount,2).'</B></font></td>';
             $data .= '   <td  ><font size="2"><B>'.round($FOBValue).'.00</B></font></td>
			
                <td  ><font size="2"><B>'.$RowPo['BillRefNo'].'</B></font></td>
                <td  ><font size="2"><B>'.$BillDiscountDate.'</B></font></td>
                <td  ><font size="2"><B>'.$BillRealizationDate.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['RealizationEarlyDate'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['RealizationLateDate'].'</B></font></td>
                <td  ><font size="2"><B>'.number_format($DiscountAmount,2).'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['EbrcReleasedUSD'].'</B></font></td>
                <td  ><font size="2"><B>'.$EbrcNumber[0].'<br>'.$EbrcNumber[1].'<br>'.$EbrcNumber[2].'</B></font></td>
                <td  ><font size="2"><B>'.$EbrcDate.'<br>'.$EbrcDate2.'<br>'.$EbrcDate3.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['AdvanceRefNo'].'</B></font></td>
			  <td  ><font size="2"><B>'.$AdvanceDate.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['LcNumber'].'</B></font></td>
                <td  ><font size="2"><B>'.$LcDate.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['LcReceiptFrom'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['AwbNumber'].'</B></font></td>
                <td  ><font size="2"><B>'.$AwbDate.'</B></font></td>
                <td  ><font size="2"><B>'.$ShipByDate.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['RoadTransport'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['Vehicle'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['ShippingCompany'].'</B></font></td>
				
                <td  ><font size="2"><B>40FT</B></font></td>
                <td  ><font size="2"><B>'.number_format($RowPo['FrieghtCharge'],2).'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['OceanFreightINR'].'</B></font></td>';
				
				$data .= '  <td  ><font size="2"><B>'.$RowPo['FreightCostPerKg'].'</B></font></td>';
			$data .= '  <td  ><font size="2"><B>'.$RowPo['ThcNOthers'].'</B></font></td>';
/*if($Exporter==5){              
$data .= '<th>  <B>55</B></th>';}*/
  
              $data .= ' <td  ><font size="2"><B>'.$TotalFreight.'</B></font></td> 
			  <td  ><font size="2"><B>'.$RowPo['ContainerNo'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['SealNo'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['UserName'].'</B></font></td>';
				if($Exporter!=5){
				$data .= '     <td  ><font size="2"><B>'.$RowPo['PackSealNo'].'</B></font></td>';}
               $data .= ' <td  ><font size="2"><B>'.$RowPo['BLNo'].'</B></font></td>
                <td  ><font size="2"><B>'.$VesselNameVOY.'</B></font></td>
                <td  ><font size="2"><B>'.$ETD.'</B></font></td>
                <td  ><font size="2"><B>'.$ETA.'</B></font></td>
              <td  ><font size="2"><B>'.$ContainerOutDate.'</B></font></td>';
			  //if($Exporter!=5){

           $data .= '   <td  ><font size="2"><B>'.$FdaNEuHoldDate.'</B></font></td>
              <td  ><font size="2"><B>'.$FdaNEuReleaseDate.'</B></font></td>';//}
			  /*else{
				    $data .= '<th>  <B>65</B></th>';}*/

             $data .= '   <td  ><font size="2"><B>'.$RowPo['FdaEntryNumber'].'</B></font></td>
                <td  ><font size="2"><B>'.$FdaEntryDate.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['AgentInv'].'</B></font></td>
                <td  ><font size="2"><B>'.$ActualDutyPaidUSD.'</B></font></td>
                <td  ><font size="2"><B>'.$CvdPaidUSD.'</B></font></td>
                <td  ><font size="2"><B>'.$OthersHMFUSD.'</B></font></td>
                <td  ><font size="2"><B>'.$OthersMPFUSD.'</B></font></td>';
            // if($Exporter!=5){
			 $data .= '    <td  ><font size="2"><B>'.$OtherChargesSum.'</B></font></td>';
             
                $data .= '  <td  ><font size="2"><B>'.$TotalPaidUSD.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['SBNo'].'</B></font></td>
                <td  ><font size="2"><B>'.$SBDate.'</B></font></td>
				           
                <td  ><font size="2"><B>'.$LetExportDate.'</B></font></td>
            <td  ><font size="2"><B>'.$RowPo['FobSbUsd'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['FobSbInr'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['DrawBackRecvd'].'</B></font></td>';
				//if($Exporter!=5){
             $data .= '    <td  ><font size="2"><B>'.$RowPo['RodtepRecvd'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['DrawBackRcvdAmount'].'</B></font></td>
                <td  ><font size="2"><B>'.$DrawBackRcvdDate.'</B></font></td>
                <td  <B></B></td>
                <td  ><font size="2"><B>'.$MeisApplDate.'</B></font></td>';//}
				
				
				
              $data .= '  <td  ><font size="2"><B>'.$RowTotal['QCertNo'].'</B></font></td>
                <td  ><font size="2"><B>'.$QDateIssue.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['FobVaueINR'].'</B></font></td>
                <td  ><font size="2"><B>'.$RowProcessor['ExpApprovalNo'].'</B></font></td>
                 <td  ><font size="2"><B>'.$HCNumberDate.'</B></font></td>
                <td  ><font size="2"><B>'.$GPSNumberGPSDate.'</B></font></td>
                <td  ><font size="2"><B>'.$CoNumberCoDate.'</B></font></td>
                <td  ><font size="2"><B>'.$RowPo['InsuranceDtls'].'</B></font></td>
                
			
				 </tr>';
				

						$StmtvarietyId=$iconn->query($SqlVarietyId);	
if($VarietyCount>1){

			//while($RowVariety=$Stmtvariety->fetch_array()){
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
		    $data .= 
					
					'<tr>
				 <td  align="center" width="3%">'.$S_NO.'</td>

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
					$data .= '<td  align="center" width="'.$width_s.'%"></td>
                ';
				}
				  
				$data .= ' </tr>';
			}}
				 /**uma**/
				 
				 
				 

 $S_NO++;			 
}				
				

					
echo $data .='</tbody></table>';
}


?>

<script src="https://cdn.datatables.net/fixedcolumns/3.3.0/js/dataTables.fixedColumns.min.js"></script>
<script>
$(document).ready( function () {
    // $('#example1').DataTable();
    var table = $('#MonAttTable').DataTable( {
        scrollY:"400px",
         scrollX:true,
          //sScrollX: "100%",
  sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true
        /*"columnDefs": [
            { "width": "20%", "targets": 0 }
          ]*/
    } );
} );
</script>



<script>
/*
$(document).ready( function () {
   var table = $('#MonAttTable').DataTable( {
	    ordering : false,
		 bDestroy: true,
         bAutoWidth: false,
        scrollY:        "300px",
        scrollX:        true,
        scrollCollapse: true,
        paging:         false,
        fixedColumns:   {
            leftColumns: 3
         },
		 columnDefs: [
    { width: "20%", targets: 1 }
  ]
    });
} );
*/

var oTable = $('#MonAttTable').dataTable( {
        "sScrollX": "90%",
		 scrollY:        "60%",
		ordering : false,
		paging:         false,
        "sScrollXInner": "100%",
        "bScrollCollapse": true
    } );


new $.fn.dataTable.FixedColumns( oTable , {
            leftColumns: 3 //specifies how many left columns should be fixed.
        });
</script>

<style>
   table {border-collapse:collapse; table-layout:fixed; width:300px;height:300px;}
   table td {border:solid 1px #fab; width:100px; word-wrap:break-word;}
   table th {border:solid 1px #fab; width:100px; word-wrap:break-word;}
   </style>
