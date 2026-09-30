<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include "../SalesInculdes/SalesConstants.php"; 
include ($_SERVER['DOCUMENT_ROOT']."/tcpdf/tcpdf.php");

$PoID=$_GET['id'];
$Type=$_GET['Type'];

 /*
 $SqlStatus="SELECT * FROM $TblScrmPIData  WHERE PoID='$PoID'";
$StmtStatus=$iconn->query($SqlStatus); 
$PoStatus = $StmtStatus->fetch_assoc();
*/

  $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
  SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading FROM $ScrmCustomInvoices as SCV 
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN $TblScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN $TblScrmCountriesList as SL on SL.id=SP.Country

 WHERE SCV.PiID='$PoID'";  	
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
			$IGST=$CustomInvoiceRow['IGST'];
			$OverSeasCommission=$CustomInvoiceRow['OverSeasCommission'];


 //exit;
 $SqlScrmPoData="SELECT * FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'";  
 
 
 $SqlPaymentTerms="SELECT * FROM $TblScrmPoData as SPD 
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 
 
 
$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);
$RowPaymentTerms = $StmtPaymentTerms->fetch_assoc();
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];




$ExporterData=getExporterDetails($ExpID);
$ProcessorData=getExporterDetails($ProcessorID);

$PaymentTerms=$PoDataRow['PaymentTerms'];
$DeliveryTerms=$PoDataRow['DeliveryTerms'];
$BankID=$PoDataRow['BankID'];
$Status=$PoStatus['Status']; 




class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global 
	
	//$Status,
	$IGST;
	
	if($IGST=='IGST'){
		$Header2='SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
	}
	else{
		$Header2='';
	}
	/*
	if($Status==0){
	$StatusTerm="(Approval Pending)";
	}else if($Status==2){
	$StatusTerm="(cancelled)";
	}else if($Status==4){
	$StatusTerm="(Rejected)";
}
*/
		global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 8);
		$this->Cell(120, 0, 'CUSTOMS INVOICE CUM PACKING LIST'.$Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', ' ', 8);
		$this->Cell(130, 0, ''.$Header2, 0, 0, 'R', 0, '', 0, false, 'T', 'M');

		 $this->SetTextColor(255,0,0);
		
		$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');

    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}

if($Type==''){
$Type='I';
//$Header=' -  VIEW ONLY';
}
	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	$pdf->SetCreator(PDF_CREATOR); 	
    $pdf->SetTitle('CUSTOMS INVOICE CUM PACKING LIST'.$Header);  
    $pdf->SetTitle(''.$Header2);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10);	
	$pdf->SetHeaderMargin(4);	     
    $pdf->SetMargins(10, 10, 10,5);  	
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->SetFont('helvetica', '', 11);  
	$pdf->AddPage();  

	
	$TableData=getCustomInvData($iconn,$ID);
	$pdf->writeHTML($TableData);
	$pdf->SetFont('helvetica', '', 6);


$CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate']));   

if($PoDataRow['ExporterID']==5){		

if($CurrentMonth<=3){ 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))-1;  		
}else{ 
$CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	 
}
}else{		
if($CurrentMonth<=3){
	
 	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	

	
} 
else{ 
 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
  
}
		}
/***uma 28-07-2022**/

$file_name=$ExporterData['InvShortCode'].'-CUS-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear.'.pdf';
//sme/pi/233/2022-23




//echo $Type;
ob_end_clean();

$pdf->Output($file_name, $Type);


			


function getCustomInvData($iconn,$ID){
	
global $PoDataRow,$CountryName,$RowPaymentTerms,$PoID;
	
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include "../SalesInculdes/SalesConstants.php"; 

  
global $CustomInvoiceRow,$ExpID ;
global $PoDataRow ;
global $ExporterData ;
global $ProcessorData ;
global $TblAdminBankDetails ;
global $BankID ;
global $TblScrmPaymentTerms ;
global $TblAdminExporterDetails ;
global $SignURL ,$OverSeasCommission;
	
	//Exporter /Shipper Details
			$CountryName=$PoDataRow['name']; 

			//$ExporterID=$ExporterData['ExpID'];
			$ExporterName=$ExporterData['ExpName'];
			$ExporterAddress=$ExporterData['ExpAddress']; 
			$ExporterUsfdaNo=$ExporterData['ExpUsfdaNo'];
			$ExporterApprovalNo=$ExporterData['ExpApprovalNo'];
			$ExporterIeCustomsNo=$ExporterData['ExpIeCustomsNo']; 
			$ExporterGstNo=$ExporterData['ExpGstNo']; 
			$ExporterIeCode=$ExporterData['ExpIeCode'];
			$ExporterRecAdd=$ExporterData['ImporterRecordAddress'];
			$LutNumber=$ExporterData['LutNumber'];
            $AuthSign=$ExporterData['AuthSign'];			
	//Processor/Packer
			$ProcessorIeCustomsNo=$ProcessorData['ExpIeCustomsNo'];
			$ProcessorID=$PoDataRow['ProcessorID'];	
			$ProcessorNamee=$ProcessorData['ExpName'];
			
	
			
			
			$ProcessorName=$ProcessorData['ExpName'];
			//$ProcessorAddress=$ProcessorData['ExpAddress'];
			$ProcessorAddress=$ProcessorData['PackerAddress'];
			$ProcessorUsfdaNo=$ProcessorData['ExpUsfdaNo'];
			$ProcessorApprovalNo=$ProcessorData['ExpApprovalNo'];
			$ProcessorFEI=$ProcessorData['ExpFeiNo'];
			$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

			$InvDate=getFormatedDate($CustomInvoiceRow['CInvDate']);
			$PoDate=getFormatedDate($CustomInvoiceRow['CpoDate']);
			//$PreCarriageBy=$CustomInvoiceRow['CInvNo'];
			$PlaceOfReceiptBy=$CustomInvoiceRow['PlaceOfRecipt'];
			$PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
			$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
			$SealNo=$CustomInvoiceRow['SealNo'];
			$VesselNumber=$CustomInvoiceRow['VesselNumber'];
			$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
			$ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
			$CInvCode=$CustomInvoiceRow['CInvCode'];
			$IGST=$CustomInvoiceRow['IGST'];
			$PortOfDischarge=$CustomInvoiceRow['PortofDischarge'];
			$FinalDestination=$CustomInvoiceRow['FinalDestination'];
			$NoOfDays=$CustomInvoiceRow['NoOfDays'];
			$Discount=$CustomInvoiceRow['Discount'];
			//$CInvCode=$CustomInvoiceRow['CInvCode'];
			
			if($NoOfDays==0){
				$NoOfDays='';
			}else{
				$NoOfDays=$NoOfDays.'DAYS FROM BL';
			}
			
			$PoNo=$PoDataRow['PoNo'];
			$PoDate=getFormatedDate($PoDataRow['PoDate']);
			
			$PoDate= "& Dated :".$PoDate;
			$ConsigneeName=$PoDataRow['ConsigneeName'];
			$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];
			$DestinationCountry=$PoDataRow['name'];
			$ByPayment=$PoDataRow['ByPayment'];
			$PreparedBy=$PoDataRow['PreparedBy'];
			$DeliveryTerms=$PoDataRow['DeliveryTerms'];
			$ShippingTerms=$PoDataRow['ShippingTerms'];
			$QtyUnit=$PoDataRow['QtyUnit']; 
			$DestinationCountryId=$PoDataRow['DestinationCountry'];


			$PaymentTerms=$RowPaymentTerms['PaymentName'];
//echo $ID;
			$AryWeightData=getPoWeights($iconn,$PoID);
			//print_r($AryWeightData);
			$InsPrice=$AryWeightData['InsurancePrice'];
			  $TotalMcs=$AryWeightData['MCS'];  


			
	
			
	$content='';
	                                                                  

	$size=45;	
	$size1=55;	

	

$StyleTbl='Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 10px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';
  
  $StyleTd='Style=" margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;"';
 
	 $content .= '<br><br><table border="1" cellspacing="0" cellpadding="0">  
				
				 <tr> 
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';
				

				
	//	if($PoDataRow['ExporterID']==5){		
			$content .= '<tr>
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>'.$ExporterName.' 
				<BR> '.$ExporterAddress.'<BR>';
				
				if($PoDataRow['ExpUsfdaNo']==!'' && $CountryName !='China' ){
					$content .= 'USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>';					
				}
				$content .= 'IE CODE  : '.$ExporterIeCode.'
				<BR>GST NO  : '.$ExporterGstNo.'</font></th>
				</tr>';

				
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				<br>
				</font></th>
			
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">'.$PlaceOfReceiptBy.'</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR align="center">  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">'.strtoupper($PortOfLoading).','.strtoupper($CustomInvoiceRow['PortOfLoadingCountry']).'</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">'.strtoupper($PortOfDischarge).' '.$CustomInvoiceRow['CountryCode'].','.strtoupper($CustomInvoiceRow['PortofDischargeCountry']).'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">'.$FinalDestination.'
				<br></font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				<br> '.$InvNO.',Dated '.$InvDate.' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Buyer`s Order No. & Date. </b>
				<br> '.$PoNo.$PoDate.' </font></th>
				</tr>';
				
				if($ShippingTerms=='FOB'){
					$FinalDestination=$PortOfLoading.',INDIA';
				}else{
					$FinalDestination=$FinalDestination;
				}
			
			$content .= '<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# '.$ProcessorApprovalNo.'</b>
				<br>
				<u><b> Packer</u> :</b><br> '.$ProcessorName.'<br> '.$ProcessorAddress.'  ';
			if($CountryName!='China'){
				$content .= '<br> USFDA REGISTRATION NO  : '.$ProcessorUsfdaNo.'<BR> FEI # :  '.$ProcessorFEI.'';
			}
				$content .= '</font></th></tr>';
			
				
	
			 if($PoDataRow['ShippingTerms']=='DDP'){
				
			 $content .= '<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br>
				<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN  # '.$ProcessorIeCustomsNo.'
				<br> '.$ExporterRecAdd .' </font></th>
				</tr>';
				}else{
				
			 $content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br></font></th>
				</tr>';}
				
			$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  '.strtoupper($DestinationCountry).'</b></font></th>
				</tr>';
				
				if($IGST=='DISCOUNT'){
					$NoOfDays ='';
				}
				
				$content .= '	<tr> 
				<th  align="LEFT"  width="100%" ><font size="7"><b><u> Terms of Delivery and Payment:</u>
				</b><br><BR> ' .$PaymentTerms .' ' .$NoOfDays .' <br>
				 SHIPMENT TERM : '.$ShippingTerms .'  '.strtoupper($FinalDestination).'';
				 if($ShippingTerms=='CIF'){
					$content .= '<BR><b> INSURANCE: '.number_format($InsPrice,2).'</b>'; 
				 }
				if($DestinationCountryId!=26 && $PoDataRow['ShippingTerms']!='FOB'){
				
				
				$content .= '('.$DeliveryTerms.')';
				}
				
				
				if($OverSeasCommission != ''){
					$content .= '<BR> OVERSEAS COMMISSION: '. strtoupper($OverSeasCommission) .''; 
				 }
				$content .= '<br></font> </th>
				</tr> ';
			
				
			$content .= '	</table>
				</th>
				
				</tr> 
				
				
				
				
				
				
				<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> '.$TotalMcs.'</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br></font><font size="10"><b align="center">'.$ContainerNumber.'</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br></font><font size="10"><b align="center">'.$SealNo.'</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in KGS </font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>USD/KGS  '.$ShippingTerms .'
				<br><b>'.strtoupper($FinalDestination).' </b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in U.S.D <br>'.$ShippingTerms .'
				<br><b> '.strtoupper($FinalDestination).' </b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>
				
				';
				
				$description_head='<B><u>Description of Goods: </u></B><BR>1 X 40 FCL IN FROZEN FORM OTHER THAN AFD';
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$Whitspace='<b style="color:white"> yuhju</b>';
				$FontSize="7";
				//$TOTAL_MCS=0;
			$content.='<tr >
				
			<table border="0" cellspacing="0" cellpadding="1">
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$description_head.' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			
			</tr>
			'
			;
			

			  $SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP FROM 
			`$ScrmCIGradeData` 
			left join `$TblAdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand"; 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
			while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){
				
				
				$Variety=$VarietyMcsRow['Variety'];
				$Grade=$VarietyMcsRow['Grade'];
				$Brand=$VarietyMcsRow['Brand'];
				 $BAP=	$VarietyMcsRow['BAP']; 
				 $PackingStyleID=	$VarietyMcsRow['PackingStyleID']; 
  if($BAP=='NA'){
	 $BAP='';  
   }
  ELSE{
	$BAP=$BAP;  
  }
				
				$content.='
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].'<br>
			PACKING : '.$VarietyMcsRow['PackingStyle'].' NET WEIGHT PER CARTON.'.$Whitspace.' 
			';
			
			if($DestinationCountryId!=26){
			$content.='<B> BRAND </B>: '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
			}
			
							
		$content.=' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>
			

			
			<tr>
			
			<th align="center" width="40%" ><font size="'.$FontSize.'"> <u>SIZE</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>';
			
			//UMA
				   $SqlVariety="SELECT * FROM `$ScrmCIGradeData` as PGD
			left join $TblAdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'
			and Variety='$Variety' and Brand='$Brand'  
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)"; 
			$StmtVariety=$iconn->query($SqlVariety);
				
			while($RowVariety=$StmtVariety->fetch_assoc()){
				
				$RowVariety=getGradeWeight($RowVariety);
				
				
				
			$content.='
				<tr>
				<td align="center" width="40%"  ><font size="'.$FontSize.'">
				'.$RowVariety['Grade'].'</font></td>
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.$RowVariety['Mcs'].'</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.number_format($RowVariety['Qty'],3).'	</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
					'.$RowVariety['Price'].'</font></td>
				<td align="center" width="14.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.number_format($RowVariety['GradePrice'],2).'</font></td>
				
				</tr>';
			}


			}
			
			
			
			
			$Weight=$AryWeightData['TotalWeight'];
			$Price=$AryWeightData['TotalPrice'];
			$Mcs=$AryWeightData['MCS'];
			
		
			
			
			$content.='
				<tr>
				
				<td align="center" width="40%" style="border-top: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; border-right: 1px solid black;" ><font size="7">
				'.$Mcs.'</font></td>
				
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.number_format($Weight,3).'</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" >
				<font size="7">
				</font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.number_format($Price,2).'</font></td>
				
				
				</tr>';
				IF($IGST=='IGST'){
					
					
				$InvValue=$Price*$ExchangeRate;
					
					if($CInvCode=='03061720'){
						
						$IgstPerc=5;
						$IGSTValue=($InvValue*$IgstPerc)/100;
					}else{
						$IgstPerc=12;
						$IGSTValue=($InvValue*$IgstPerc)/100;
					}
				
			$content.= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				INVOICE VALUE IN R.S@'.$ExchangeRate.'</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				'.number_format($InvValue,2).'</B></font></td>
				</tr><tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;" ><font size="7"><B>
				IGST@'.$IgstPerc.'%</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				'.number_format($IGSTValue,2).'</B></font></td>
				</tr>';
						
				}
				
				else IF($IGST=='DISCOUNT'){
				
				$DiscountAmount=($Price*$Discount)/100;
				 $TotalDiscount=$Price-$DiscountAmount;		
			$content.= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				LESS TRADE DISCOUNT '.$Discount.'%</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				'.number_format($DiscountAmount,2).'</B></font></td>
				</tr><tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;" ><font size="7"><B>
				TOTAL AMOUNT:</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				'.number_format($TotalDiscount,2).'</B></font></td>
				</tr>';
						
				}
			$content.= '</table>
			  </tr>';

  $SqlBank="SELECT * FROM $TblAdminBankDetails WHERE ID='$BankID'";
$StmtBank=$iconn->query($SqlBank); 
$RowBank = $StmtBank->fetch_assoc();
$BenfName=$RowBank['AccntName'];
$BenfAccNo=$RowBank['AccountNumber'];
$BenfBankName=$RowBank['BankName'];
$BenfBankAddress=$RowBank['BankAddress'];
$BenfBankCode=$RowBank['SwiftCode'];

//echo $Price;
	 
	 if($TotalDiscount==''){

				$BILL_WORD=numberTowords($Price);
	 }else{
		 
				$BILL_WORD=numberTowords($TotalDiscount);
	 }		
	 /**uma(16/01/22)**/

								 if($ShippingTerms=='CIF'){
									$InvPrice= ($Price*0.11)/100;
									$Price=$Price-$InvPrice;
								 }
								 else{
								$Price=$Price;	 
								 }
								/**uma(16/01/22)**/

				$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				
			$content.='<tr> '.
           		 $TOTAL.'
				 
				 
	
				</tr>

				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> SHIPPING TERMS   : '.$ShippingTerms.'  '.$FinalDestination.'
				
				
				<BR> '; IF($IGST=='DISCOUNT'){
					
					$DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35; 
					 $DutiesPrice=round(($DutiesPrice+125),2);

				/*$content.='<br> INVOICE VALUE US $ <b style="color:white">---------------------------------------</b>: <b>'.round($TotalDiscount).'.00</b>
				<BR> LESS OCEAN FRIEGHT PREPAID IN US $<b style="color:white">------------</b>: <b><u>'.round($FrieghtCharge).'.00</u></b> 
				<BR><BR> SUB TOTAL<b style="color:white">------------------------------------------------------</b>: <b>'.round(($TotalDiscount-$FrieghtCharge)).'.00 </b>';
			*/if($CountryName=='China'||$CountryName=='UAE'||$ShippingTerms=='CNF'){//UMA12/04/2022
				$content.='	
				 FOB VALUE IN US$ :<b style="color:white">------------------------------------------</b>:
				<b>'.number_format(($TotalDiscount-$FrieghtCharge),2).' '.$Whitspace.'    FOB VALUE    :'.round((($TotalDiscount-$FrieghtCharge)*$ExchangeRate)).'.00</b>';
				}
 

			else{
			$content.='<br> INVOICE VALUE US $ <b style="color:white">---------------------------------------</b>: <b>'.number_format($TotalDiscount,2).'</b>
				<BR> LESS OCEAN FRIEGHT PREPAID IN US $<b style="color:white">------------</b>: <b><u>'.round($FrieghtCharge).'.00</u></b> 
				<BR><BR> SUB TOTAL<b style="color:white">------------------------------------------------------</b>: <b>'.number_format(($TotalDiscount-$FrieghtCharge),2).' </b>	<BR> LESS DUTIES, PORT TAXES & CLEARANCE US$<b style="color:white">--</b>: <b><u>'.round($DutiesPrice,2).'</u> </b>
				<BR> <BR> FOB VALUE IN US$ :<b style="color:white">------------------------------------------</b>:
				<b>'.number_format(($TotalDiscount-$FrieghtCharge-$DutiesPrice),2).' '.$Whitspace.'    FOB VALUE    :'.round((($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate)).'.00</b>';}
	
			}
			
			ELSE if($PoDataRow['ShippingTerms']=='FOB'){
				$content.='	<br> INVOICE VALUE US $ <b style="color:white">---------------------------------------</b>: <b>'.number_format($Price,2).'</b>
				
				<BR> FOB VALUE IN US$ :<b style="color:white">------------------------------------------</b>:
				<b>'.number_format(($Price-$FrieghtCharge),2).' '.$Whitspace.'    FOB VALUE    :'.round((($Price-$FrieghtCharge)*$ExchangeRate)).'.00</b>';
				}
			
ELSE{//echo $Price-$FrieghtCharge; exit;
	
					$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				/*$content.='<br> INVOICE VALUE US $ <b style="color:white">---------------------------------------</b>: <b>'.round($Price).'.00</b>
				<BR> LESS OCEAN FRIEGHT PREPAID IN US $<b style="color:white">------------</b>: <b><u>'.round($FrieghtCharge).'.00</u></b> 
				<BR><BR> SUB TOTAL<b style="color:white">------------------------------------------------------</b>: <b>'.round(($Price-$FrieghtCharge)).'.00 </b>';
				*/if($CountryName!='China' && $CountryName!='UAE'&&$ShippingTerms!='CNF'){//UMA(12/04/2022)
				
			$content.='<br> INVOICE VALUE US $ <b style="color:white">---------------------------------------</b>: <b>'.number_format($Price,2).'</b>
				<BR> LESS OCEAN FRIEGHT PREPAID IN US $<b style="color:white">------------</b>: <b><u>'.round($FrieghtCharge).'.00</u></b> 
				<BR><BR> SUB TOTAL<b style="color:white">------------------------------------------------------</b>: <b>'.number_format(($Price-$FrieghtCharge),2).' </b>




			<BR> LESS DUTIES, PORT TAXES & CLEARANCE US$<b style="color:white">--</b>: <b><u>'.round($DutiesPrice,2).'</u> </b>
				<BR> <BR> FOB VALUE IN US$ :<b style="color:white">------------------------------------------</b>:
				<b>'.number_format(($Price-$FrieghtCharge-$DutiesPrice),2).''.$Whitspace.'    FOB VALUE    :'.round((($Price-$FrieghtCharge-$DutiesPrice)*$ExchangeRate)).'.00</b>';}


else{
				
			$content.='	
				 <BR> FOB VALUE IN US$ <b style="color:white">-----------------------</b>:
				<b>'.number_format(($Price-$FrieghtCharge),2).' '.$Whitspace.'    FOB VALUE    :'.round((($Price-$FrieghtCharge)*$ExchangeRate)).'.00</b>';}


}				
			 $NetWgtKgs = $RowTotal['NetWgtKgs']; 
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
				
				
				
				if($QtyUnit=='KGS'){
				$content.='<BR><BR><b> NET WEIGHT <b style="color:white">------</b>: '.number_format($NetWgtKgs, 3, '.', '').' KGS</b>
				<BR><b> GROSS WEIGHT <b style="color:white">-</b>: '.number_format($GrossWeightKgs, 3, '.', '').' KGS</b> 
				</font></th>';	
				}else{
				$content.='<BR><BR> NET WEIGHT<b style="color:white">-----------------------------------</b>: 
				<b>'.number_format($NetWgtLbs, 3, '.', '').'LBS</b>
				<b style="color:white">------------------------------</b><b>'.number_format(($NetWgtLbs*0.454),3,'.','').' KGS</b>
				<BR> GROSS WEIGHT:<b style="color:white">-----------------------------</b>: 
				<b>'.number_format($GrossWeightLbs,3,'.','').'LBS</b>
				<b style="color:white">------------------------------</b><b>'.number_format(($GrossWeightLbs*0.454),3,'.','').' KGS</b> 
				</font></th>';	 
				}
				
				
				$ShipmentUnder="1) WE HEREBY DECLARE THAT WE SHALL CLAIM THE BENEFITS UNDER CUSTOMS NOTIFICATION NO. 07/2020 DATED: 28-01-2020. AS WELL AS CHAPTER 3 OF FTP AS APPLICABLE. FISH AND CRUSTACEANS, MOLLUSCS AND OTHER AQUATIC INVERTEBRATES
<br>2) THIS SHIPMENT IS MADE UNDER DUTY DRAW BACK SCHEME AS WELL AS UNDER RoDTEP SCHEME.
<br>3) ITC HS CODE <b>".$CInvCode."</b>,DRAW BACK SCHEME CODE:19, SL.NO. OF EXPORT PRODUCT :030601B, RATE OF ENTITLEMENT : 3% ON FOB OR Rs.23.6/KG CAP, IN FROZEN FORM OTHER THAN AFD
";

 IF ($IGST=='LUT'){
$ShipmentUnder.="<br>4)SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF INTEGRATED TAX: LUT No. $LutNumber "  ;
}
			
				$Decleration="
I/WE, IN REGARD TO MY/OUR CLAIM UNDER RoDTEP SCHEME MADE IN THIS SHIPPING BILL OR BILL OF EXPORT, HEREBY DECLARE THAT:
<br>1. I/ WE UNDERTAKE TO ABIDE BY THE PROVISIONS, INCLUDING CONDITIONS, RESTRICTIONS, EXCLUSIONS AND TIME-LIMITS AS PROVIDED UNDER RoDTEP SCHEME, AND RELEVANT NOTIFICATIONS, REGULATIONS, ETC., AS AMENDED FROM TIME TO TIME.

<br>2. ANY CLAIM MADE IN THIS SHIPPING BILL OR BILL OF EXPORT IS NOT WITH RESPECT TO ANY DUTIES OR TAXES OR LEVIES WHICH ARE EXEMPTED OR REMITTED OR CREDITED UNDER ANY OTHER MECHANISM OUTSIDE RoDTEP.

<br>3. I/WE UNDERTAKE TO PRESERVE AND MAKE AVAILABLE RELEVANT DOCUMENTS RELATING TO THE EXPORTED GOODS FOR THE PURPOSES OF AUDIT IN THE MANNER AND FOR THE TIME PERIOD PRESCRIBED IN THE CUSTOMS AUDIT REGULATIONS, 2018
			";
               $content.='</tr>
				
						
	        
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> AMOUNT CHARGABLE IN US.$: <b>'.$BILL_WORD.'</b></font><BR> 
				<font size="5"><b>SHIPPED UNDER:</b><br>'.$ShipmentUnder.'<br>
				<BR><b> DECLARATION:</b><br>'.$Decleration.'<BR>	<br>			
				<b> INFORMATION CODE: RODTEPY</b><br>
				
				</font></th>    
				
				 
				
                
</tr>
<tr>
<th  align="bottom"  width="70%"><font size="8"><B><U> DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>
           
		   <th  align="center"  width="30%"><font size="7"> Signature:<br><br>
				<B> '.$AuthSign.',</B><BR><BR><BR>
				 
				<!--<img src="'.$SignURL.'" 
				 width="100" height="30"style="width: 100" align="center" align="middle"/>-->
								
				
				<p STYLE="text-align:center" > Authorised Signature   </p></font></th>
</tr>
				
				
				</table>'; 
	
	return $content;
	
	}
	
	
	function getPoWeights($iconn,$PoID){
		
		global $PoDataRow;
		
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
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			
			
			
			if( $PoDataRow['QtyUnit']=='KGS'){				
				$Weight = $RowTotal['NetWgtKgs'];	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'] *0.454;
				$Price = $RowTotal['PriceLbs'];
			}
			
			$RowTotal['TotalWeight']=$Weight;
			$RowTotal['TotalPrice']=$Price;
			
			$InsPrice=($Price*0.11)/100;
			$RowTotal['InsurancePrice']=$InsPrice;
			return $RowTotal;
		
		
		
	}
	
	
		function getGradeWeight($RowVariety){
		global $PoDataRow;
	
			if( $PoDataRow['QtyUnit']=='KGS'){
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['MFactor'];
				$UsdValue=($Qty)*$RowVariety['Price'];				
				}else{
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['LbsFactor']*0.454;										
				 $UsdValue=($Qty)*$RowVariety['Price']/0.454;
				}
		$RowVariety['Qty']=$Qty;
		$RowVariety['GradePrice']=$UsdValue;
		
		if($CountryName!='China'){	
		$RowVariety['Price']=round(($UsdValue/$Qty),6);
		}

			return $RowVariety;
		
		
		
	}


?>