<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

$style = '<style>
th {
    font-size: 6px;
	padding : 10px;
}
td {
    font-size: 6px;
	padding : 10px;
}
p {
	line-height: 0.9;
}
</style>
';


$PoID=$_GET['id'];
$Type=$_GET['Type'];
$PType=$_GET['PType']; 
if($PType==''){
	$PType='LBS';
}


$SqlCustom="SELECT IGST,SPD.ExporterID as ExporterID FROM `ScrmCustomInvoices`  as SCV
left join ScrmPoData as SPD on  SPD.PoID=SCV.PiID
WHERE PiID='$PoID'";
$StmtCustom=$iconn->query($SqlCustom);
$RowCustom=$StmtCustom->fetch_assoc();
$IGST=$RowCustom['IGST'];
$ExpID=$RowCustom['ExporterID'];


class MYPDF extends TCPDF { 
 
    public function Header() {		
	//	
	/*getCustomInvData($iconn,$PoID);
	$IGST=$AryData['IGST'];*/
	global  $IGST;
	/*if($IGST=='IGST'){
		$Header2='SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
	}
	else{
		$Header2='';
	}*/

		global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 13);
		$this->Cell(0, 0, 'COMMERCIAL INVOICE'.$Header, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', ' ', 8);
		$this->Cell(130, 0, ''.$Header2, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

		 $this->SetTextColor(255,0,0);
		
		$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');

    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	$pdf->SetCreator(PDF_CREATOR); 	
    $pdf->SetTitle('COMMERCIAL INVOICE'.$Header);  
   // $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10);	
	$pdf->SetHeaderMargin(5);	     
    $pdf->SetMargins(10, 15, 10,5);  	
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
    $pdf->SetAutoPageBreak(false, 5);  
	$pdf->AddPage();  

	$AryData=getReportData($iconn,$PoID);
	$HeaderTable=getHeaderTable($iconn,$PoID);
	$BreakUpTable=getBreakTable($iconn,$PoID);
	$FooterTable=getFooterTable($iconn,$PoID);
	
	$pdf->writeHTML($HeaderTable.$BreakUpTable.$FooterTable);
	
	/*$TableData=getCustomInvData($iconn,$PoID);
	$pdf->writeHTML($TableData);*/
	$pdf->SetFont('helvetica', '', 6);


//$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];
 
$file_name=$InvShortCode.'-CUS-'.$CInvNo.'-'.$FinancialYear.'.pdf';
   
ob_end_clean();

if($Type==''){
$Type='I';
}else{
	$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}

$pdf->Output($file_name, $Type);


function getHeaderTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryData,$style,$PType;
					
	$content='';
	                                                                  

	$size=45;	
	$size1=55;	


		
		
				
		
		$ExporterID=$AryData['ExporterID'];
		$ExporterName=$AryData['ExporterName'];
		$ExporterAddress=$AryData['ExporterAddress'];
		$ExporterUsfdaNo=$AryData['ExporterUsfdaNo'];
		$DestinationCountryId=$AryData['DestinationCountryId'];
		$ExporterIeCode=$AryData['ExporterIeCode'];
		$ExporterGstNo=$AryData['ExporterGstNo'];
		
		$ConsigneeID=$AryData['ConsigneeID'];
		$BuyerID=$AryData['BuyerID'];
		$ConsigneeName=$AryData['ConsigneeName'];
		$ConsigneeAddress=$AryData['ConsigneeAddress'];
		$ConsigneeCountry=$AryData['ConsigneeCountry'];
		$BuyerName=$AryData['BuyerName'];
		$BuyerAddress=$AryData['BuyerAddress'];
		
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$FinalDestination=$AryData['FinalDestination'];
		$DestinationCountryName=$AryData['DestinationCountryName'];
		$VesselNumber=$AryData['VesselNumber']; 
		
		
		$InvNo=$AryData['InvNo'];
		$InvDate=$AryData['InvDate'];
		$PoNo=$AryData['PoNo'];
		$ReferenceNo=$AryData['ReferenceNo'];
		$PoDate=$AryData['PoDate'];
		 $ShowBank=$AryData['ShowBank']; 
		
		$ProcessorName=$AryData['ProcessorName'];
		$ProcessorAddress=$AryData['ProcessorAddress'];
		$ProcessorUsfdaNo=$AryData['ProcessorUsfdaNo'];
		$ProcessorFEI=$AryData['ProcessorFEI'];
		$ProcessorApprovalNo=$AryData['ProcessorApprovalNo'];
		
		$ShowShippingTerms=$AryData['ShowShippingTerms'];
		$showBuyer=$AryData['showBuyer'];
		$showImporter=$AryData['showImporter'];
		$showNotifyParty=$AryData['showNotifyParty'];
		$ShippingTerms=$AryData['ShippingTerms'];
		$ProcessorIeCustomsNo=$AryData['ProcessorIeCustomsNo'];
		//$ExporterRecAdd=$AryData['ExporterRecAdd'];
		$NotifyPartyName=$AryData['NotifyPartyName']; 
		$NPartyAddress=$AryData['NPartyAddress']; 
		$NPartyContactNumber=$AryData['NPartyContactNumber']; 
		$ImporterName=$AryData['ImporterName']; 
		$ImporterAddress=$AryData['ImporterAddress'];
		
        $PaymentTerms=$AryData['PaymentTerms'];
		$PortOfDischarge=$AryData['PortOfDischarge'];
 		$PortofLoading=$AryData['PortOfLoading']; 
		$DestinationCountry=$AryData['DestinationCountry'];
		//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
		$DeliveryTerms=$AryData['DeliveryTerms'];
		$ShowDeliveryTerms=$AryData['ShowDeliveryTerms'];
		$IRNNumber=$AryData['IRNNumber'];
		$QtyUnit=$AryData['QtyUnit'];
		 $UnitCurrency=$AryData['UnitCurrency']; 
		$TotalMcs=$AryData['TotalMcs'];
		$ShipByDate=$AryData['ShipByDate'];
		$SignURL=$AryData['SignURL'];
		$AuthSign=$AryData['AuthSign'];
		$PreparedBy=$AryData['PreparedBy'];
		$BankID=$AryData['BankID'];
		$Remarks=$AryData['Remarks'];
		$showRemarks=$AryData['showRemarks'];
		$showExpUsFda=$AryData['showExpUsFda'];
		$showExpIECode=$AryData['showExpIECode'];
		$showExpGSTNo=$AryData['showExpGSTNo'];
		$ShippingTerms=$AryData['ShippingTerms'];
		
		$showProUsFda=$AryData['showProUsFda'];
		$showProFEI=$AryData['showProFEI'];
		
		$ContainerNo=$AryData['ContainerNo'];
		$SealNo=$AryData['SealNo'];
		$BLDate=$AryData['BLDate']; 
		$SBDate=$AryData['SBDate']; 
		$BLNo=$AryData['BLNo']; 
		$SBNo=$AryData['SBNo']; 


 

	 $content .= $style; 
	 $content .= '
	 
	 <table border="1" cellspacing="0" cellpadding="0">  ';
	 if($PType=='KGS'){
				
	 $content .= '<tr><th width="100%"> <br><br><font size="7"><b> IRN NO: '.$IRNNumber.'<br></b></font></th></tr>';}
			$content .= '	 <tr>
				 
				
				
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';
				
				$content .= '
				
				<tr>
				<th  align="LEFT" ><font size="7"><b>Exporter :</b>
				<br>'.$ExporterName.' 
				<BR> '.$ExporterAddress.'' ;
				
			/*	if($showExpUsFda){
				$content .= '	USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>';
				}
				
				if($showExpIECode){
				$content .= ' IE CODE  : '.$ExporterIeCode;
								
				}
				
				if($showExpGSTNo){
				$content .= '<BR> GST NO  : '.$ExporterGstNo;
				}*/
				
				
				$content .= '</font></th></tr>';
				
		
		
		
		
		
			$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.','.$ConsigneeCountry.'</font></th>
				</tr>';
			if($showNotifyParty){
				$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b> <u>Notify Party</u></b>
				<br>'.$NotifyPartyName.',<br>'.$NPartyAddress.'<br>TEL:'.$NPartyContactNumber.'
				
				
				</font></th>
				</tr>';	
			}
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgfgtyhjmnkih</b>';
				/*<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">ANY PORT, INDIA</font></th>
				below lines
				<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> '.$TotalMcs.'</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br><b align="center">1 X 40 FCL</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br><b align="center">'.$SealNo.'</b></font></th>
                
				</tr>*/
				$content .= '<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Flight No. 
               <BR align="center"><b>  '.$VesselNumber.' </b>
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">'.$PortofLoading.'</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">'.$PortOfDischarge.'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">'.$FinalDestination.','.$DestinationCountryName.'</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" >
				<font size="7">Marks & Nos </font>
				</th>
				<th  align="LEFT" width="50%" >
				<font size="7">No.& Kind of Pkgs <br><b align="center" >'.$TotalMcs.' M/Cs. </b></font>
				</th>
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				 '.$InvNo.',Dated '.$InvDate.' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Purchase Order No. & Date. </b>
				<br> '.$PoNo.$PoDate.' </font></th>
				</tr>';
				
				
				
				
				
				
				
				$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Reference(s) :
				<b style="color:white">_________________________</b>'.$ReferenceNo.'  </b>
				
				</font></th></tr >';
				if($showImporter){
				
				$content .= '	<tr> 
           		
		
                <th  align="left" width="100%"><font size="7">
				<b><u> Importer of Record:</u></b> <br>  '.$ImporterName.'
				<br> '.$ImporterAddress.' </font></th>
				</tr>';
				}if($showBuyer){
				
			 $content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> <b><u>Buyer 
				(if other than consignee) :</b></u><br>'.$BuyerName.',<br>'.$BuyerAddress.'<br></font></th>
				</tr>';
				}
				
				
				$content .= '<tr >
				<th  align="LEFT"  ><font size="7" >
				 <u><b>MANUFACTURER/PACKER`S NAME AND ADDRESS</u> :</b><br> '.$ProcessorName.'<br> '.$ProcessorAddress.'';
				
				
				
				
				if($showProUsFda){			
				$content .= '<br><b> BIO TERRORISM / USFDA REGISTRATION NO.</b>  '.$ProcessorUsfdaNo;
				}
				
				/*if($showProFEI){			
				$content .= '<BR> FEI # :  '.$ProcessorFEI;
				}
		*/
				$content .= '</font></th></tr>';
	
	
			 
			$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="49%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
				
				<th border="1"  align="left" width="51%"><font size="7"> Country of Final Destination  
				<br><b align="center">  '.$DestinationCountry.'</b></font></th>
				
				
				
               
				</tr>
				<tr> 
				
			
				
				<th  align="LEFT"  width="100%" ><font size="7"><b> <u>Terms of Payment:</u>
				</b><br><BR> ' .$PaymentTerms .' <br>
				 
				 <!--SHIPMENT TERM : '.$ShippingTerms .' '.strtoupper($PortOfDischarge).', '.
				strtoupper($DestinationCountry).' -->
				';
				
				
				
				
				$content .= '</font> </th>
				</tr>
			<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u> CONTAINER NO.  
				'.$ContainerNo.'</b></u> <b style="color:white">________________________</b>   <b><u>SEAL NO. '.$SealNo.'</b></u>
                 <br><br> <b>BL NO. '.$BLNo.'</b> <b style="color:white">_________________________</b>  <b>BL Date: '.$BLDate.'</b>
				
				
				</font></th>
				</tr>
				<tr>  
           		
				<th  align="LEFT" ><font size="7"><b> Terms of Delivery 
				</b>  
                 <br> 
                 <br> 
				 '.$ShippingTerms.' '.$PortOfDischarge.'
				
				
				</font></th>
				</tr>
				
				</table>
				</th>
				
				</tr> ';
				
				
				
				
				$content.='</table>'; 
	
	return $content;
	
	}

function getBreakTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryData,$style,$PType;			
	$content=$style;
	                                                                  

	$size=45;	
	$size1=55;	


		
		
				
		
		$ExporterID=$AryData['ExporterID'];
		$ExporterName=$AryData['ExporterName'];
		$ExporterAddress=$AryData['ExporterAddress'];
		$ExporterUsfdaNo=$AryData['ExporterUsfdaNo'];
		$DestinationCountryId=$AryData['DestinationCountryId'];
		$ExporterIeCode=$AryData['ExporterIeCode'];
		$ExporterGstNo=$AryData['ExporterGstNo'];
		
		$ConsigneeID=$AryData['ConsigneeID'];
		$BuyerID=$AryData['BuyerID'];
		$ConsigneeName=$AryData['ConsigneeName'];
		$ConsigneeAddress=$AryData['ConsigneeAddress'];
		$BuyerName=$AryData['BuyerName'];
		$BuyerAddress=$AryData['BuyerAddress'];
		
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$FinalDestination=$AryData['FinalDestination'];
		$TotalGrossWtKgs=$AryData['TotalGrossWtKgs'];
		
		
		$InvNo=$AryData['InvNo'];
		$InvDate=$AryData['InvDate'];
		$PoNo=$AryData['PoNo'];
		$PoDate=$AryData['PoDate'];
		 $ShowBank=$AryData['ShowBank']; 
		
		$ProcessorName=$AryData['ProcessorName'];
		$ProcessorAddress=$AryData['ProcessorAddress'];
		$ProcessorUsfdaNo=$AryData['ProcessorUsfdaNo'];
		$ProcessorFEI=$AryData['ProcessorFEI'];
		$ProcessorApprovalNo=$AryData['ProcessorApprovalNo'];
		
		$ShowShippingTerms=$AryData['ShowShippingTerms'];
		$showBuyer=$AryData['showBuyer'];
		$showImporter=$AryData['showImporter'];
		$showNotifyParty=$AryData['showNotifyParty'];
		$ShippingTerms=$AryData['ShippingTerms'];
		$ProcessorIeCustomsNo=$AryData['ProcessorIeCustomsNo'];
		//$ExporterRecAdd=$AryData['ExporterRecAdd'];
		$NotifyPartyName=$AryData['NotifyPartyName']; 
		$NPartyAddress=$AryData['NPartyAddress']; 
		$NPartyContactNumber=$AryData['NPartyContactNumber']; 
		$ImporterName=$AryData['ImporterName']; 
		$ImporterAddress=$AryData['ImporterAddress'];
		
        $PaymentTerms=$AryData['PaymentTerms'];
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$PortofLoading=$AryData['PortofLoading'];
		$DestinationCountry=$AryData['DestinationCountry'];
		//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
		$DeliveryTerms=$AryData['DeliveryTerms'];
		$ShowDeliveryTerms=$AryData['ShowDeliveryTerms'];
		$QtyUnit=$AryData['QtyUnit'];
		 $UnitCurrency=$AryData['UnitCurrency']; 
		$TotalMcs=$AryData['TotalMcs'];
		$ShipByDate=$AryData['ShipByDate'];
		$SignURL=$AryData['SignURL'];
		$AuthSign=$AryData['AuthSign'];
		$PreparedBy=$AryData['PreparedBy'];
		$BankID=$AryData['BankID'];
		$Remarks=$AryData['Remarks'];
		$showRemarks=$AryData['showRemarks'];
		$showExpUsFda=$AryData['showExpUsFda'];
		$showExpIECode=$AryData['showExpIECode'];
		$showExpGSTNo=$AryData['showExpGSTNo'];
		
		$showProUsFda=$AryData['showProUsFda'];
		$showProFEI=$AryData['showProFEI'];
		$showIgst=$AryData['showIgst'];
		$showDiscount=$AryData['showDiscount'];
			$TextSize=$AryData['TextSize'];
				
			if($QtyUnit=='KGS'){
				$Unit='KG';
			}
				elseif($QtyUnit=='LBS'){
					$Unit='LB';
				}
				ELSE{
					$Unit=$QtyUnit;
			}
				
			
				
				$content='
				<table border="0" cellspacing="0" cellpadding="2">
				<tr>
				<th  align="LEFT" width="30.5%" style="border-bottom:1px  solid black;border-left:1px  solid black"><font size="7">  Description of Goods <b align="center">'.$SealNo.'</b></font></th>
				
				
				
				<th  align="LEFT" width="14%" style="border-bottom:1px  solid black;"><font size="7">  SIZE</font></th>	
				
				<th  align="LEFT" width="14%" style="border-bottom:1px  solid black;border-right:1px  solid black"><font size="7"> NO. OF CTNS</font></th>
				
                <th  align="center" width="13.5%" style="border-bottom:1px  solid black;border-right:1px  solid black">
				<font size="7">Quantity in '.$QtyUnit.' </font></th>
				
                <th  align="center" width="13.5%" style="border-bottom:1px  solid black;border-right:1px  solid black"><font size="7">Rate/'.$Unit.' <br>'.$UnitCurrency .'</font></th>
				
                <th  align="center" width="14.5%" style="border-bottom:1px  solid black;border-right:1px  solid black"><font size="7">Amount in '.$UnitCurrency.' </font></th>
                </tr>';
			
			
$CurrentCount=0;

			$SqlVarietyMcs="SELECT sum(McS) As VarietyMcs,BuyerItemCode,
			(SELECT CIDescription from ScrmCIGradeData WHERE PoGradeID=ScrmPoGradeData.ID) as Description,Variety,Treatment,Grade,
			 PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmPoGradeData` 
			 left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand 
			 Order By ID asc";
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
			$RowCount=mysqli_num_rows($StmtVarietyMcs); 

			while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){
				$CurrentCount++;
				
				$Variety=$VarietyMcsRow['Variety'];
				$Grade=$VarietyMcsRow['Grade'];
				$Brand=$VarietyMcsRow['Brand'];
				 $BAP=	$VarietyMcsRow['BAP']; 
				 $BuyerItemCode=	$VarietyMcsRow['BuyerItemCode']; 
				 $BreakUpDescription=	$VarietyMcsRow['Description']; 
				 $PackingStyleID=	$VarietyMcsRow['PackingStyleID']; 
				 $Treatment=	$VarietyMcsRow['Treatment']; 
  if($BAP=='NA'){
	 $BAP='';  
   }
  ELSE{
	$BAP=$BAP;  
  }
  if($Treatment==''){
	 $Treatment='';  
   }
  ELSE{
	$Treatment='('.$Treatment.')'; 
	$Treatment='';	
  }
  
  
 
				
				$FontSize="7px";
				
				
				$description_head='<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$Whitspace='<b style="color:white"> yuhju</b>';
				$FontSize="7";
				//$TOTAL_MCS=0;
			$content.='
				
			
			
			'
			;
			
  
  
		//$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$BreakUpDescription.'<br>';
		
		if($BreakUpDescription!=''){
	     $Description= $BreakUpDescription.'<br>';
		}else{
		$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>';
		}	
		
		if($DestinationCountryId!=26){
			$Description.='PACKING : '.$VarietyMcsRow['PackingStyle'].' BRAND : '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
		}else{
			$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].' BRAND : '.$VarietyMcsRow['Brand'].' ';
		}

		if($BreakUpDescription!=''){
	     $Description= $BreakUpDescription.'<br>';
		}		
		
		
		//$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].''.$Whitspace.' ';	
		//$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].''.$Whitspace.' ';	
			
			
				$content.='
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.nl2br($Description).'</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>';
			if($CurrentCount < 2){
			$content.='<tr>';
			if($BuyerItemCode!=''){
			$content.='
			<th align="center" width="10%" style="border-right:1px solid white;"  ><font size="'.$FontSize.'"> <u>Item Code</u></font></th>
			<th align="center" width="10%" style="border-right:1px solid white;"  ><font size="'.$FontSize.'"> <u>SIZE</u></font></th>';
			}else{
			$content.='<th align="center" width="20%" style="border-right:1px solid white;" ><font size="'.$FontSize.'"> <u>SIZE</u></font></th>';
			}
			$content.='<th align="center" width="18.5%" style="border-right:1px solid white;" ><font size="'.$FontSize.'"><u>NO. OF CTNS</u></font></th>
			
		<td align="center" width="20%" style="border-right:1px solid black;"><font size="'.$FontSize.'"></font></td>
		<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>';
			}
			
			  $SqlVariety="SELECT * FROM `ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";   
		
			$StmtVariety=$iconn->query($SqlVariety);
			$ArySubBreakUp=array();
				
			while($RowVariety=$StmtVariety->fetch_assoc()){ 
			
				$ArySubBreakUp[]=$RowVariety['ID'];

					
				$AryPriceBreakUp=getBreakUpData($iconn,$RowVariety['ID']);
				$Qty=$AryPriceBreakUp['Qty'];
				$UsdValue=$AryPriceBreakUp['UsdValue']; 
				$TotalMcs=$AryPriceBreakUp['TotalMcs'];
				$Price=$AryPriceBreakUp['Price'];
				$UnitPrice=$AryPriceBreakUp['UnitPrice'];
				$AryPaymentData=getPaymentValues($iconn,$ID);
				
		
				$InvValueUSD=$AryPaymentData['InvValueUSD'];
				$InvValueInr=$AryPaymentData['InvValueINR'];
				$ExchangeRate=$AryPaymentData['ExchangeRate'];
				$DiscountPerc=$AryPaymentData['DiscountPerc'];
				$DiscountAmount=$AryPaymentData['DiscountAmount'];
				$IgstPerc=$AryPaymentData['IgstPerc'];
				$IgstValue=$AryPaymentData['IgstValue'];
				
				//print_r($AryPriceBreakUp); exit;
				
				if($PType=='KGS'){
					$Price=sprintf("%0.7f",$UnitPrice);
				}else{
					$Price=sprintf("%0.2f",$Price);
				}
				
				//print_r($AryPriceBreakUp); exit;
				
				
			$content.='<tr>';
			
			if($BuyerItemCode!=''){
			$content.='
			<td align="center" width="10%" style="border-right:1px solid white;"  ><font size="'.$FontSize.'">
				 '.$RowVariety['BuyerItemCode'].' 
				</font></td><td align="center" width="10%" style="border-right:1px solid white;"  ><font size="'.$FontSize.'">
				 '.$RowVariety['Grade'].' 
				</font></td>';
			}else{
			$content.='<td align="center" width="20%"  style="border-right:1px solid white;" ><font size="'.$FontSize.'">
				 '.$RowVariety['Grade'].' 
				</font></td>';
			}
				
		//<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
		//		'.$RowVariety['Brand'].'  </font></td>		
			$content.='
				<td align="center" width="18.5%" style="border-right:1px solid white;"  ><font size="'.$FontSize.'">
				'.$TotalMcs.'  </font></td>
				<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
				</font></td>	
				<td align="center" width="13.5%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
				'.number_format($Qty,3).' </font></td>
				<td align="center" width="13.5%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
				'.$Price.'</font></td>
				
				<td align="center" width="14.5%" style="border-right:1px solid black;" ><font size="'.$FontSize.'">
				'.number_format($UsdValue,3).'</font></td>
				
				</tr>';
			}
			
			 $SubVarityBrkUp=implode(',',$ArySubBreakUp); 
			
				$AryPriceData=getBreakUpData($iconn,$SubVarityBrkUp);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				 $TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
			
			
			$content.='
				<tr>
				<td align="center" width="20%"  style="border-right:1px solid white;" ><font size="'.$FontSize.'">
				<b>Total</b	>
				</font></td>
				<td align="center" width="18.5%" style="
				border-top:1px solid black;
				border-bottom:1px solid black;
				" >
														
														<font size="'.$FontSize.'">
				'.$TotalMcs.'</font></td>
				
				<td align="center" width="20%" style="border-right:1px solid black;">
														
														<font size="'.$FontSize.'"></font></td>
				<td align="center" width="13.5%" ><font size="'.$FontSize.'">
				'.number_format($Qty,3).'</font></td>
				<td align="center" width="13.5%" style="border-right:1px solid black;">
														<font size="'.$FontSize.'"></font></td>
				<td align="center" width="14.5%" style="border-top:1px solid black;
				border-bottom:1px solid black;">
														<font size="'.$FontSize.'">
				'.number_format($UsdValue,2).'</font></td>
				
				</tr>';


			}
			
				$AryPriceData=getBreakUpData($iconn,$RowVariety['ID'],$ID);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				$TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
				
			
			$content.=' 
				<tr>
				
				<td align="center" width="20%" style="border-top: 1px solid black;" ><font size="7"><B>
				GRAND TOTAL </B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; " ><font size="7">
				'.$TotalMcs.'</font></td>
				<td align="center" width="20%" style="border-top: 1px solid black; border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.number_format($Qty,3).'</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="14.5%" style="
				border-top: 1px solid black;
				border-bottom: 1px solid black;
				border-right: 1px solid black;"><font size="7">
				'.number_format($UsdValue,2).'</font></td>
				
				
				</tr>';
			
			
			//$showDiscount=true;
			if($showDiscount){		
				$content.= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				LESS TRADE DISCOUNT '.$DiscountPerc.'%</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				'.number_format($DiscountAmount,2).'</B></font></td>
				</tr><tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				TOTAL AMOUNT:</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;" ><font size="7"><B>
				'.number_format($InvValueUSD,2).'</B></font></td>
				</tr>';
				}
				
				//Static DFL # Guru Prasad Dont want Display
				$showIgst=false;
				if($showIgst){
					$content.= '	<tr> 
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				INVOICE VALUE IN R.S@'.$ExchangeRate.'  </B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				'.number_format($InvValueInr,2).'</B></font></td>
				</tr>
				<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				IGST@'.$IgstPerc.' % </B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				'.number_format($IgstValue,2).'</B></font></td>
				</tr>';			
				}
			
			$content.= '</table>';
 
	
	return $content;
	
	}			


function getFooterTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
					
	global $AryData,$style;			
	$content=$style;
	                                                                  

	$size=45;	
	$size1=55;	


		
				
				$AryPriceData=getBreakUpData($iconn,$RowVariety['ID'],$ID);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				$TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
				$GlazePerc=$AryPriceData['GlazePerc'];
			 	$ProductWeightWOGLbs=$AryPriceData['ProductWeightWOGLbs'];
			 	$ProductWeightWOGKgs=$AryPriceData['ProductWeightWOGKgs'];
			 	$WtPerCtnTxt=$AryPriceData['WtPerCtnTxt']; 
				
		
	//	print_r($AryPriceData); exit;
				
		
		$ExporterID=$AryData['ExporterID'];
		$ExporterName=$AryData['ExporterName'];
		$ExporterAddress=$AryData['ExporterAddress'];
		$ExporterUsfdaNo=$AryData['ExporterUsfdaNo'];
		$DestinationCountryId=$AryData['DestinationCountryId'];
		$ExporterIeCode=$AryData['ExporterIeCode'];
		 $ExporterGstNo=$AryData['ExporterGstNo'];
		 $TotalGrossWtKgs=$AryData['TotalGrossWtKgs'];
		$UnitCurrency=$AryData['UnitCurrency'];
		
		$ConsigneeID=$AryData['ConsigneeID'];
		$BuyerID=$AryData['BuyerID'];
		$ConsigneeName=$AryData['ConsigneeName'];
		$ConsigneeAddress=$AryData['ConsigneeAddress'];
		$BuyerName=$AryData['BuyerName'];
		$BuyerAddress=$AryData['BuyerAddress'];
		
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$FinalDestination=$AryData['FinalDestination'];
		$CIDescriptionspl=$AryData['CIDescriptionspl'];
		
		
		$InvNo=$AryData['InvNo'];
		$InvDate=$AryData['InvDate'];
		$PoNo=$AryData['PoNo'];
		$PoDate=$AryData['PoDate'];
		 $ShowBank=$AryData['ShowBank']; 
		
		$ProcessorName=$AryData['ProcessorName'];
		$ProcessorAddress=$AryData['ProcessorAddress'];
		$ProcessorUsfdaNo=$AryData['ProcessorUsfdaNo'];
		$ProcessorFEI=$AryData['ProcessorFEI'];
		$ProcessorApprovalNo=$AryData['ProcessorApprovalNo'];
		
		$ShowShippingTerms=$AryData['ShowShippingTerms'];
		$showBuyer=$AryData['showBuyer'];
		$showImporter=$AryData['showImporter'];
		$showNotifyParty=$AryData['showNotifyParty'];
		$ShippingTerms=$AryData['ShippingTerms'];
		$ProcessorIeCustomsNo=$AryData['ProcessorIeCustomsNo'];
		//$ExporterRecAdd=$AryData['ExporterRecAdd'];
		$NotifyPartyName=$AryData['NotifyPartyName']; 
		$NPartyAddress=$AryData['NPartyAddress']; 
		$NPartyContactNumber=$AryData['NPartyContactNumber']; 
		$ImporterName=$AryData['ImporterName']; 
		$ImporterAddress=$AryData['ImporterAddress'];
		
        $PaymentTerms=$AryData['PaymentTerms'];
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$PortofLoading=$AryData['PortofLoading'];
		$DestinationCountry=$AryData['DestinationCountry'];
		//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
		$DeliveryTerms=$AryData['DeliveryTerms'];
		$ShowDeliveryTerms=$AryData['ShowDeliveryTerms'];
		$QtyUnit=$AryData['QtyUnit'];
		 $UnitCurrency=$AryData['UnitCurrency']; 
		$TotalMcs=$AryData['TotalMcs'];
		$ShipByDate=$AryData['ShipByDate'];
		$SignURL=$AryData['SignURL'];
		$AuthSign=$AryData['AuthSign'];
		$PreparedBy=$AryData['PreparedBy'];
		$BankID=$AryData['BankID'];
		$Remarks=$AryData['Remarks'];
		$showRemarks=$AryData['showRemarks'];
		$showExpUsFda=$AryData['showExpUsFda'];
		$showExpIECode=$AryData['showExpIECode'];
		$showExpGSTNo=$AryData['showExpGSTNo'];
		
		$showProUsFda=$AryData['showProUsFda'];
		$showProFEI=$AryData['showProFEI'];
		 $ShowBank=$AryData['ShowBank']; 
		 $ShowInvoicevalue=$AryData['ShowInvoicevalue']; 
		 $ShowFrightCharge=$AryData['ShowFrightCharge']; 
		 $UnitCurrency=$AryData['UnitCurrency']; 
		 $ComDescriptionSpl=$AryData['ComInvDescription']; 
		


 

$AryBankData=getBankDetails($iconn,$BankID);

//print_r($AryBankData); exit;


$BenfName=$AryBankData['AccntName'];
$BenfAccNo=$AryBankData['AccountNumber'];
$BenfBankName=$AryBankData['BankName'];
$BenfBankAddress=$AryBankData['BankAddress'];
$BenfBankCode=$AryBankData['SwiftCode'];

$BankDetails=$AryBankData['BankDetails'];
$RoutingThrough=$AryBankData['RoutingThrough'];
$NegotiatingBank=$AryBankData['NegotiatingBank'];
	 

				$BILL_WORD=numberTowords($UsdValue);

			
						$NetWgtKgs=$AryPriceData['NetWgtKgs'];
						$NetWgtLbs=$AryPriceData['NetWgtLbs'];
						$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
						$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];
						
						$AryPaymentData=getPaymentValues($iconn,$ID);
						
						//print_r($AryPaymentData); exit;
$FrieghtCharge=$AryPaymentData['FrieghtCharge'];
$DiscountAmount=$AryPaymentData['DiscountAmount'];
$InvValueUSDWithOutDuties=$AryPaymentData['InvValueUSDWithOutDuties'];
$InvValueINRWithOutDuties=$AryPaymentData['InvValueINRWithOutDuties'];
 $DutiesPrice=$AryPaymentData['DutiesPrice'];
$FOBValueUSD=$AryPaymentData['FOBValueUSD'];
$AntiDumpingDuty=round($AryPaymentData['AntiDumpingDuty'],2);
$MerchantDuty=round($AryPaymentData['MerchantDuty'],2);
$HarborDuty=round($AryPaymentData['HarborDuty'],2);
$FOBValueINR=round($AryPaymentData['FOBValueINR']);
$InvValueUSD=$AryPaymentData['InvValueUSD'];
$ISFStatic=$AryPaymentData['ISFStatic'];


				
			$content.=$style;
			$content.='
			<table border="1" cellspacing="0" cellpadding="2">
			
			<tr>
			<td width="58.5%">
			
			<table border="0" cellspacing="0" cellpadding="0">';
			$ShowInvoicevalue=true;
			//$ShowFrightCharge=false;
			if($ShowFrightCharge){
			
			$content.='<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">INVOICE VALUE  </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
			<td align="left" width="29%" ><font size="7"> <b> $ '.number_format($InvValueUSD,2).'</b></font></td>
			</tr>';

			$content.='
			<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">LESS OCEAN FRIEGHT  </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
			<td align="left" width="29%" ><font size="7"> <b> $ <u>'.number_format($FrieghtCharge,2).'</u></b></font></td>
			</tr>';
			if($ISFStatic){
			$content.='
			<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">(LESS : MERCHANDISE PROCESS FEE @ 0.3464%)</font></td>
			<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
			<td align="left" width="44%" ><font size="7"> <b> $ '.number_format($MerchantDuty,2).'</b></font></td>
			</tr>';	
			}
			$content.='
			<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">SUB TOTAL</font></td>
			<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
			<td align="left" width="29%" ><font size="7"> <b> $ '.number_format($InvValueUSDWithOutDuties,2).'</b></font></td>
			</tr>';
			$content.='
			<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">(LESS : ANTI-DUMPING DUTY @ 1.35%) </font></td>
			<td align="left" width="44%" ><font size="7"> <b>: $ '.number_format($AntiDumpingDuty,2).'</b></font></td>
			</tr>';
			if(!$ISFStatic){
			$content.='
			<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">(LESS : MERCHANDISE PROCESS FEE @ 0.3464%)</font></td>
			<td align="left" width="44%" ><font size="7"> <b>: $ '.number_format($MerchantDuty,2).'</b></font></td>
			</tr>';
			}
			$content.='<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">(LESS : HARBOR MAINTENANCE FEE @ 0.125%)</font></td>
			<td align="left" width="44%" ><font size="7"> <b>: $ '.number_format($HarborDuty,2).'</b></font></td>
			</tr>';
			
			$content.='<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">TOTAL DUTY</font></td>
			<td align="left" width="44%" ><font size="7"> <b>: $ <u>'.number_format($DutiesPrice,2).'</u></b></font></td>
			</tr>';
			
			$content.='<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">FOB VALUE  IN USD  </font></td>
			<td align="left" width="45%" ><font size="7"> <b>: $ '.number_format($FOBValueUSD,2).'</b></font></td>
			</tr>';
			}
			else if($ShowInvoicevalue){
				
				//echo "hii"; exit;
				$content.='<tr>
			<td  width="1%"></td>
			<td align="left" width="35%"><font size="7">INVOICE VALUE US $</font></td>
			<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
			<td align="left" width="60%" ><font size="7"> <b> '.number_format($InvValueUSD,2).'</b></font></td>
			</tr>';
			
			/*$content.='<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE innn IN US $ </font></td>
			<!--<td align="left" width="15%" ><font size="7"> <b>: '.number_format($InvValueUSDWithOutDuties,2).'</b></font></td>-->
			<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
			<td align="left" width="60%" ><b> '.number_format($InvValueUSDWithOutDuties,2).'</b><font size="7"></font></td>

			<!--<td align="left" width="60%" ><font size="7"> 
			<b> FOB VALUE  :  '.round($InvValueINRWithOutDuties).'.00</b></font></td>-->
			</tr>'; */
			}else{
			$content.='<tr>
			<td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN US $ </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
			<td align="left" width="60%" ><font size="7"><b> '.number_format($InvValueUSDWithOutDuties,2).'</b></font></td>

			<!--<td align="left" width="50%" ><font size="7"> 
			<b> FOB VALUE  :  '.round($InvValueINRWithOutDuties).'.00</b></font>
			</td>-->
			</tr>';	
			}
			
			
			
			$NetWgtKgs=$AryPriceData['NetWgtKgs'];
			$NetWgtLbs=$AryPriceData['NetWgtLbs'];
			$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];			
			$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];
			
			if($QtyUnit=='KGS'){
			$GrossWeightKgs=$TotalGrossWtKgs;
			$GrossWeightLbs=$TotalGrossWtKgs*2.204; 
			}else{
			$GrossWeightKgs=$TotalGrossWtKgs*0.454;
			$GrossWeightLbs=$TotalGrossWtKgs;
			}
			
			
			
		

			$content.='</table></td>';
			
			$content.='<td width="41.5%">
			
			<table border="1" cellspacing="0" cellpadding="2">';
			
			if($QtyUnit=='KGS'){
				$content.='<tr>
			<td align="left" width="30%"><font size="7"><b>GROSS WEIGHT:</b> </font></td>
			<td align="left" width="35%" ><font size="7"> <b>'.number_format($GrossWeightKgs,3).' KGS</b></font></td>
			</tr>';	
			$content.='<tr>
			<td align="left" width="30%"><font size="7"><b>NET WEIGHT: </b></font></td>
			<td align="left" width="35%" ><font size="7"> <b>'.number_format($NetWgtKgs,3).' KGS</b></font></td>
			</tr>';
			
			if($GlazePerc!=0){
				$content.='<tr>
			<td align="left" width="30%"><font size="7"><b>NET WEIGHT WITHOUT GLAZE </b></font></td>
			<td align="left" width="35%" ><font size="7"> <b> '.number_format($ProductWeightWOGKgs,3).' KGS</b></font></td>
			</tr>';
			}
				
			}else{
				$content.='<tr>
			<td align="left" width="30%"><font size="7">GROSS WEIGHT </font></td>
			<td align="left" width="35%" ><font size="7"> <b>: '.number_format($GrossWeightLbs,3).' LBS</b></font></td>
			<!--<td align="left" width="35%" ><font size="7"> <b> '.number_format($GrossWeightKgs,3).' KGS</b></font></td>
			-->
			</tr>';
			
			$content.='<tr>
			<td align="left" width="30%"><font size="7">NET WEIGHT </font></td>
			<td align="left" width="35%" ><font size="7"> <b>: '.number_format($NetWgtLbs,3).' LBS</b></font></td>
			<!--<td align="left" width="35%" ><font size="7"> <b> '.number_format($NetWgtKgs,3).' KGS</b></font></td>
			-->
			</tr>';
			
			if($GlazePerc!=0){
				$content.='<tr>
			<td align="left" width="30%"><font size="7"><b>NET WEIGHT WITHOUT GLAZE</b> </font></td>
			<td align="left" width="35%" ><font size="7"> <b> '.number_format($ProductWeightWOGLbs,3).' LBS</b></font></td>
			</tr>';
			}
			}
			
			
			
			$content.='</table>';
			
			
			
			$content.='</td></tr></table>'; 
			
			
				
				
				
				
				
				
				$content.='<table border="1"><tr>';
				 
		$formated_date = date("Y-m-d", strtotime($InvDate));
        $AryDescription=getInvoiceDescription($iconn,$formated_date);
	$CommercialNote=$AryDescription['CommercialNote']; 
		
		$content.='
				<table border="1" cellspacing="0" cellpadding="2">
				<tr>  
           		<td  align="LEFT"  width="100%"><font size="7"><b> AMOUNT CHARGABLE (IN '.$UnitCurrency.'): 
				('.$BILL_WORD.' ONLY)</b></font><BR> 
				<font size="6.5">'.
				nl2br($WtPerCtnTxt).'<br>';
				
				
				$content.=getProductSpec($iconn,$ID);
				$content.='
				
				</font>
				</td> 
				</tr>
				</table>';
		
		$ShowBank=true;
				if($ShowBank){
				$content.='
				<table border="1" cellspacing="0" cellpadding="2">
				<tr><td style="margin-left: 1em;">
				<b>BANK DETAILS  </b>'.$BankDetails.'';
				$content.='<br><b>Negotiating BANK  </b>'.$NegotiatingBank;
				$content.='<br><b>ROUTING THROUGH   </b>'.$RoutingThrough.'
				</td></tr></table>';
				}
				
	
				
				
				

			
	$content.='</tr>';
	//if($CommercialNote==''){
	if($CommercialNote!=''){
		//echo $CommercialNote; 
				$content.='<tr>  
           		<th  align="LEFT"  width="100%"> 
				<font size="7">'.nl2br($CommercialNote).'<br>';
				if($ComDescriptionSpl!=''){
					$content.=nl2br($ComDescriptionSpl).'<br>';
					}
					$content.='</font>
				</th> 
				</tr>';
	}
				
				
				
				$content.='<tr>
				<th  align="bottom"  width="70%"><font size="7"><B><U> DECLARATION:</U></B> WE HEREBY DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>
           
		   <th  align="center"  width="30%"><font size="7"> 
				<B> for '.$AuthSign.',</B><BR><BR><BR>
				 
							
				
				<p STYLE="text-align:center" > <b>AUTHORISED SIGNATORY <br> Signature & Date </b></p></font></th>
</tr>


				
				
				</table>';
	
	return $content;
	
	}
	

 

 
 

 

	
		
function getReportData($iconn,$PoID){
		
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


	  /*$CustomInvoiceSql="SELECT *  
		FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
		WHERE SCV.PiID='$PoID'"; */
		
	 $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
        SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading ,SCV.ContainerNo	as ContainerNo 
		FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID 
	    left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SL on SL.id=SP.Country
		 left JOIN ScrmISFDetails as SID on SID.PIID=SCV.PiID
		WHERE SCV.PiID='$PoID'"; 
			$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
			$CustomInvData = $CustomInvoiceExec->fetch_assoc();
			$IGST=$CustomInvData['IGST'];
			$OverSeasCommission=$CustomInvData['OverSeasCommission'];
			$CInvDate=$CustomInvData['CInvDate'];
			$CInvCode=$CustomInvData['CInvCode'];
			$NoOfDays=$CustomInvData['NoOfDays'];
			$ComInvDescription=$CustomInvData['ComDescriptionSpl'];
			
			 $TotalGrossWt=$CustomInvData['TotalGrossWt'];
			$VesselNumber=$CustomInvData['OriginatingVessel'].'<br> ' .$CustomInvData['VoyOrg'];
          $BLDate=getFormatedDateDMMY($CustomInvData['BLDate']);
		$SBDate=getFormatedDateNA($CustomInvData['SBDate']);
		$BLNo=$CustomInvData['BLNo'];
		$SBNo=$CustomInvData['SBNo'];
		
			if($NoOfDays==0){
				$NoOfDays='';

			}
			else{

				$NoOfDays=$NoOfDays.' DAYS FROM BL';
			}

  $SqlMcsTotal="SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
  $StmtMcsTotal=$iconn->query($SqlMcsTotal);
  $RowMcs=$StmtMcsTotal->fetch_assoc();
  $TotalMcs=$RowMcs['Mcs'];		
  



  $SqlScrmPoData="SELECT *,SCDB.ConsigneeName as BuyerName,
 SCDB.ConsigneeAddress as BuyerAddress,SPD.Status as PoStatus,ALD.SignURL,
SCD.ConsigneeName as ConsigneeName,
SCD.ConsigneeAddress as ConsigneeAddress,
SCL.name as ConsigneeCountry,
SCLD.name as DestinationCountryName,
SPD.UnitCurrency as UnitCurrency,

ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,
ABD.SwiftCode as SwiftCode,
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode 
	FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmConsigneeDetails as SCDB on SPD.BuyerID=SCDB.ConID
 left JOIN ScrmCountriesList as SCL on SCD.ConsigneeCountry=SCL.id
 left JOIN ScrmCountriesList as SCLD on SPD.DestinationCountry=SCLD.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN AdminLoginDetails as ALD on SC.id=SPD.ApprovedBy
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
  left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID

 WHERE PoID='$PoID'";  
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];

 $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
$StmtLogin=$iconn->query($SqlLogin); 
$RowLogin = $StmtLogin->fetch_assoc();
$SignURL=$RowLogin['SignURL'];
		
		$ExporterData=getExporterDetails($ExpID);
		$ProcessorData=getExporterDetails($ProcessorID);
		
		
		
		if($PoDataRow['NotifyPartyID']!=0){
		$AryData['showNotifyParty']=true;	
		}else{
		$AryData['showNotifyParty']=false;	
		}
		
		if($CustomInvData['Discount']!=0){
		$AryData['showDiscount']=true;	
		}else{
		$AryData['showDiscount']=false;	
		}		
		
		/*if($CustomInvData['Discount']=='DISCOUNT'){
		$AryData['showDiscount']=true;	
		}else{
		$AryData['showDiscount']=false;	
		}*/
		
		if($CustomInvData['IGST']=='IGST'){
		$AryData['showIgst']=true;	
		}else{
		$AryData['showIgst']=false;	
		}
		
		
		
		if($PoDataRow['ShippingTerms']=='DDP'){
		$AryData['ShowShippingTerms']=false;	//uma
		}else{
		$AryData['ShowShippingTerms']=false;	
		}
		
		if($PoDataRow['ShippingTerms']=='FOB'){
		$AryData['ShowInvoicevalue']=true;	//uma
		}else{
		$AryData['ShowInvoicevalue']=true;	
		}
		
		
		//if($PoDataRow['DestinationCountry']==26){
		if($PoDataRow['DestinationCountry']!=26 && $PoDataRow['ShippingTerms']!='FOB'){  //uma
		$AryData['ShowDeliveryTerms']=true;	
		}else{
		$AryData['ShowDeliveryTerms']=false;	
		}
		

		if($ExpID!=5 && $PoDataRow['DestinationCountry']!=26){
		$AryData['showExpUsFda']=false;	
		}
		/*else if($ExpID==5 && $PoDataRow['DestinationCountry']!=26){
		$AryData['showExpUsFda']=true;	
		}
		*/else{
		$AryData['showExpUsFda']=false;	
		}
		$AryData['showExpIECode']=true;
		$AryData['showExpGSTNo']=true;
		/*if($ExpID==5){
		$AryData['showExpIECode']=true;	
		}else{
		$AryData['showExpIECode']=false;	
		}*/
		
		/*if($ExpID==5){
		$AryData['showExpGSTNo']=true;	
		}else{
		$AryData['showExpGSTNo']=false;	
		}*/
		
	if($PoDataRow['ImporterID']!=0){
		$AryData['showImporter']=true;	
		}else{
		$AryData['showImporter']=false;	
		}	
		
		if($PoDataRow['ShowBank']!=0){
		$AryData['ShowBank']=true;	
		}else{
		$AryData['ShowBank']=false;	
		}
		
		if($PoDataRow['DestinationCountry']!=26){
		$AryData['showProFEI']=true;	
		}else{
		$AryData['showProFEI']=false;	
		}
		
		if($PoDataRow['ShowBrand']==1){
		$AryData['ShowBrand']=false;	
		}else{
		$AryData['ShowBrand']=false;	
		}
		//if($PoDataRow['ShowFrightCharge']==1 && $PoDataRow['ShowFrieght']==1){
		if($PoDataRow['ShowFrightCharge']==1){
		$AryData['ShowFrightCharge']=true;	
		}
		
		else{
		$AryData['ShowFrightCharge']=false;	
		}
		
		if($PoDataRow['Insurance']!=0){
		$AryData['ShowInsurance']=true;	
		}else{
		$AryData['ShowInsurance']=false;	
		}
		
		if($PoDataRow['DestinationCountry']!=26){
		$AryData['showProUsFda']=true;	
		}else{
		$AryData['showProUsFda']=false;	
		}
		
		
		
		if($PoDataRow['PoNo'] ==''){			
		$AryData['PoDate']='';	
		}else{
		$AryData['PoDate']= "  & Dated :".getFormatedDate($PoDataRow['PoDate']);
		}
		if($IGST !='DISCOUNT'){			
		 $AryData['ShowNoofDays']=true;	
		}else{
		$AryData['ShowNoofDays']=false;
		}
		
		
		 
		
		
		
		/* Return Data*/
		$AryData['TotalGrossWtKgs']=$TotalGrossWt; 
		$AryData['PoStatus']=$PoDataRow['PoStatus']; 
		$AryData['ConsigneeCountry']=$PoDataRow['ConsigneeCountry']; 
		$AryData['DestinationCountryName']=$PoDataRow['DestinationCountryName']; 
		$AryData['ApprovedBy']=$ApprovedBy; 
		$AryData['PaymentTerms']=$PaymentTerms; 
		$AryData['DeliveryTerms']=$DeliveryTerms; 
		$AryData['TotalMcs']=$TotalMcs; 
		$AryData['ExporterID']=$ExpID; 
		$AryData['ComInvDescription']=$ComInvDescription; 
		
		$AryData['ExporterName']=$ExporterData['ExpName'];;
		$AryData['ExporterAddress']=$ExporterData['ExpAddress'];
		$AryData['ExporterUsfdaNo']=$ExporterData['ExpUsfdaNo'];
		$AryData['ExporterIeCode']=$ExporterData['ExpIeCode'];
		$AryData['ExporterGstNo']=$ExporterData['ExpGstNo'];
		$AryData['DestinationCountryId']=$ExporterData['DestinationCountry'];
		$AryData['ExporterRecAdd']=$ExporterData['ImporterRecordAddress']; 
		$AryData['AuthSign']=$ExporterData['AuthSign']; 
		
		
		
		$AryData['ConsigneeID']=$PoDataRow['ConsigneeID'];
		$AryData['ReferenceNo']=$PoDataRow['ReferenceNo'];
		$AryData['BuyerID']=$PoDataRow['BuyerID'];
		$AryData['ConsigneeName']=$PoDataRow['ConsigneeName'];
		$AryData['ConsigneeAddress']=$PoDataRow['ConsigneeAddress'];
		$AryData['BuyerName']=$PoDataRow['BuyerName'];
		$AryData['BuyerAddress']=$PoDataRow['BuyerAddress'];
		
		
		
		$AryData['PortOfDischarge']=strtoupper($CustomInvData['PortofDischarge']).' '.strtoupper($CustomInvData['CountryCode']).', '.$CustomInvData['PortofDischargeCountry'];
		$AryData['PortOfDischarge']=strtoupper($CustomInvData['PortofDischarge']).', '.$CustomInvData['PortofDischargeCountry'];
		
		$AryData['PlaceOfReceiptBy']=$CustomInvData['PlaceOfRecipt'];
		$AryData['PortOfLoading']=strtoupper($CustomInvData['PortOfLoading']).','.strtoupper($CustomInvData['PortOfLoadingCountry']);
	 	$AryData['FinalDestination']=strtoupper($CustomInvData['FinalDestination']);
        $AryData['VesselNumber']=$VesselNumber; 
		
		$AryData['InvNo']=getInvoiceNumber($iconn,$ExpID,$PoID) ;
		$AryData['InvDate']=getFormatedDate($CInvDate);			
		$AryData['PoNo']=$PoDataRow['PoNo'];
		
		
		
		$AryData['ProcessorName']=$ProcessorData['ExpName'];
		$AryData['ProcessorAddress']=$ProcessorData['PackerAddress'];
		$AryData['ProcessorUsfdaNo']=$ProcessorData['ExpUsfdaNo'];
		$AryData['ProcessorFEI']=$ProcessorData['ExpFeiNo'];
		$AryData['ProcessorApprovalNo']=$ProcessorData['ExpApprovalNo'];
		$AryData['ProcessorIeCustomsNo']=$ProcessorData['ExpIeCustomsNo'];
		
		
		$AryData['ShippingTerms']=$PoDataRow['ShippingTerms'];
		$AryData['PaymentTerms']=$PoDataRow['PaymentName'];
		$AryData['DestinationCountry']=$PoDataRow['name'];
		$AryData['DeliveryTerms']=$PoDataRow['DeliveryTerms'];
		$AryData['QtyUnit']=$PoDataRow['QtyUnit'];
		$AryData['ShipByDate']=$PoDataRow['ShipByDate'];
		$AryData['SignURL']=$SignURL;
		$AryData['PreparedBy']=$PoDataRow['PreparedBy'];
		$AryData['BankID']=$PoDataRow['BankID'];
		$AryData['IGST']=$IGST;
		$AryData['CInvCode']=$CInvCode;
		$AryData['NoOfDays']=$NoOfDays;
		$AryData['UnitCurrency']=$PoDataRow['UnitCurrency'];
					
					
		$AryData['NotifyPartyName']=$PoDataRow['NotifyPartyName']; 
		$AryData['NPartyAddress']=$PoDataRow['NPartyAddress']; 
		$AryData['NPartyContactNumber']=$PoDataRow['NPartyContactNumber'];
        $AryData['ImporterName']=$PoDataRow['ImporterName']; 
		$AryData['ImporterAddress']=$PoDataRow['ImporterAddress']; 
		
		
		$AryData['ContainerNo']=$CustomInvData['ContainerNo'];
		$AryData['CIDescriptionspl']=$CustomInvData['CIDescriptionspl'];
		 $AryData['SealNo']=$CustomInvData['SealNo'];
		$AryData['IRNNumber']=$CustomInvData['IRNNumber'];
		$AryData['BLDate']=$BLDate; 
		$AryData['SBDate']=$SBDate; 
		$AryData['BLNo']=$BLNo; 
		$AryData['SBNo']=$SBNo;
		
        
		return $AryData;
	}
	
	
function getBreakUpData($iconn,$GradeID,$PiID){
	global $PType;
		
		if($PiID==''){
			$Qry="WHERE PGD.ID in ($GradeID)";
		}else{
		$Qry="WHERE InvId='$PiID'";	
		}
		
		$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
				sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as GlazeWeightKgs,
				 sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as GlazeWeightLbs";
							
		 	 $SqlTotal="SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry"; 
			
		
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();	
			$InvId=$RowTotal['InvId'];
            $GlazePerc=$RowTotal['GlazePerc'];
			
			
			if($PiID!=''){
			$SqlWtPerCtn="SELECT 
			round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,
			round(GrossWt/Sum(Mcs),2) as GrossWt FROM `ScrmPoGradeData` as SPGD 
				left JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID 
				WHERE InvId='$InvId' GROUP by PackingStyleID;"; 
			$StmtWtPerCtn=$iconn->query($SqlWtPerCtn);
			$RowWtPerCtn=$StmtWtPerCtn->fetch_assoc();
			$NetWtPerCtn=$RowWtPerCtn['NetWtLbs'];
			$GrossWtPerCtn=$RowWtPerCtn['GrossWt'];
			$WtPerCtnTxt='EACH M/CTNS. CONSISTS OF NET WT. '.$NetWtPerCtn.' LBS AND GROSS WT. '.$GrossWtPerCtn.' LBS.';
			}


			$SqlQtyUnit="SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'"; 
			$StmtQtyUnit=$iconn->query($SqlQtyUnit);
			$RowQtyUnit=$StmtQtyUnit->fetch_assoc();
			 $QtyUnit=$RowQtyUnit['QtyUnit'];
			 
			/*	if($PType=='KGS'){
					$QtyUnit='KGS';
				}
			*/
				
			if($QtyUnit=='KGS'){
			$Qty=$RowTotal['NetWgtKgs'];
			$UsdValue=$RowTotal['PriceKgs'];
			$UnitPrice=number_format($RowTotal['Price'],2);
			}else{
			//$Qty=$RowTotal['NetWgtLbs']*0.454;// Changed cause Lb and Kg Separate
			$Qty=$RowTotal['NetWgtLbs'];
			$UsdValue=$RowTotal['PriceLbs'];
			$UnitPrice=round(($UsdValue/$Qty),7);
			}
			if($QtyUnit=='KGS'){
				  $NetWgtKgs=$RowTotal['NetWgtKgs'];
				$GrossWeightKgs=$RowTotal['GrossWeightKgs'];
				 $GlazeWeight=$RowTotal['GlazeWeightKgs'];
				//echo $AryPriceData['ProductWeightWOG']=$NetWgtKgs-$GlazeWeightKgs;exit;
			

			}
			
			else{
				$NetWgtKgs=$RowTotal['NetWgtLbs']*0.454;
				$GrossWeightKgs=$RowTotal['GrossWeightLbs']*0.454;
				$NetWgtLbs=$RowTotal['NetWgtLbs'];
				$GrossWeightLbs=$RowTotal['GrossWeightLbs'];
				$GlazeWeight=$RowTotal['GlazeWeightLbs'];
			//$AryPriceData['ProductWeightWOG']=$NetWgtLbs-$GlazeWeightLbs;
			}
			
			
			/*if($QtyUnit=='KGS'){
				//$AryPriceData['Qty']=$Qty;
				$AryPriceData['Qty']=$RowTotal['NetWgtKgs'];
			}
			
			else{
				$AryPriceData['Qty']=$RowTotal['NetWgtLbs']; 
			}*/
			
			$AryPriceData['Price']=$RowTotal['Price'];
			$AryPriceData['TotalMcs']=$RowTotal['MCS'];
			
			$AryPriceData['UsdValue']=$UsdValue;
			$AryPriceData['UnitPrice']=$UnitPrice;
			$AryPriceData['Qty']=$Qty;
			$AryPriceData['GlazePerc']=$GlazePerc;
			
			
			/*$AryPriceData['NetWgtKgs']=$RowTotal['NetWgtKgs'];
			$AryPriceData['NetWgtLbs']=$RowTotal['NetWgtLbs'];
			$AryPriceData['GrossWeightKgs']=$RowTotal['GrossWeightKgs'];
			$AryPriceData['GrossWeightLbs']=$RowTotal['GrossWeightLbs'];*/
			
            $AryPriceData['NetWgtKgs']=$NetWgtKgs;
			$AryPriceData['NetWgtLbs']=$NetWgtLbs;
			$AryPriceData['GrossWeightKgs']=$GrossWeightKgs;
			$AryPriceData['GrossWeightLbs']=$GrossWeightLbs;
            $GlazeWeightKgs=$RowTotal['GlazeWeightKgs'];
			$GlazeWeightLbs=$RowTotal['GlazeWeightLbs'];
			 $AryPriceData['ProductWeightWOGKgs']=$NetWgtKgs-$GlazeWeightKgs;
			$AryPriceData['ProductWeightWOGLbs']=$NetWgtLbs-$GlazeWeightLbs;
			$AryPriceData['WtPerCtnTxt']=$WtPerCtnTxt;
			
			
return 	$AryPriceData;		
		
	}


function getBankDetails($iconn,$BankID){
	
 $SqlBank="SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
$StmtBank=$iconn->query($SqlBank); 
$AryBankData = $StmtBank->fetch_assoc();

return $AryBankData;

}	

function getProductSpec($iconn,$PoID){
	
 $SqlSpecies="SELECT GROUP_CONCAT(DISTINCT(SpeciesName)) as SpeciesName FROM `ScrmPoGradeData` as SPDG 
LEFT JOIN ScrmPIVarietyList as SPVL ON SPDG.VarietyID=SPVL.VarietyID 
left join ScrmSpeciesMaster as SSM on SSM.ShortCode=SPVL.Species WHERE InvId='$PoID'";
$StmtSpecies=$iconn->query($SqlSpecies); 
$AryStmtSpecies = $StmtSpecies->fetch_assoc();
$Desc='<BR> THIS PRODODUCT HAS NOT BEEN REFUSED BY ANY OTHER COUNTRY PRIOR TO SHIPMENT THE U.S.A <br> SPECIES : '.$AryStmtSpecies['SpeciesName'];
return $Desc ;

}	


?>