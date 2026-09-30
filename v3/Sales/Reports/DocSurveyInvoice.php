<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");


$PoID=$_GET['id'];
$Type=$_GET['Type'];

$SqlCustom="SELECT ExporterID  FROM  ScrmPoData WHERE PoID='$PoID'";
$StmtCustom=$iconn->query($SqlCustom);
$RowCustom=$StmtCustom->fetch_assoc();

$ExpID=$RowCustom['ExporterID'];



class MYPDF extends TCPDF { 
 
    public function Header() {		
	 	
	
	

		global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 8);
		$this->Cell(100, 0, 'INVOICE'.$Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
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

/*if($Type==''){
$Type='I';
}*/
	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	$pdf->SetCreator(PDF_CREATOR); 	
    $pdf->SetTitle('INVOICE'.$Header);  
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
    $pdf->SetAutoPageBreak(TRUE, 5);
    $pdf->SetFont('helvetica', '', 11);  
	$pdf->AddPage();  

	
	$TableData=getCustomInvData($iconn,$PoID);
	$pdf->writeHTML($TableData);
	$pdf->SetFont('helvetica', '', 6);


$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];
 
$file_name=$InvShortCode.'-SUR-'.$CInvNo.'-'.$FinancialYear.'.pdf';



//echo $Type;
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


			


function getCustomInvData($iconn,$PoID){	
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



	$AryData=getReportData($iconn,$PoID);
		
				
		
		$ExporterID=$AryData['ExporterID'];
		$ExporterName=$AryData['ExporterName'];
		$ExporterAddress=$AryData['ExporterAddress'];
		$ExporterUsfdaNo=$AryData['ExporterUsfdaNo'];
		$DestinationCountryId=$AryData['DestinationCountryId'];
		$ExporterIeCode=$AryData['ExporterIeCode'];
		$ExporterGstNo=$AryData['ExporterGstNo'];
		$LcStatus=$AryData['LcStatus'];
		/*$BLDate=$AryData['BLDate']; 
		$SBDate=$AryData['SBDate']; 
		$BLNo=$AryData['BLNo']; 
		$SBNo=$AryData['SBNo']; 
		$LcNumber=$AryData['LcNumber']; 
		$LcDate=$AryData['LcDate']; */
		$CIDescription=$AryData['CIDescription']; 
		$BenfName=$AryData['BenfName']; 
		$BenfAccNo=$AryData['BenfAccNo']; 
		$BenfBankName=$AryData['BenfBankName']; 
		$BenfBankAddress=$AryData['BenfBankAddress']; 
		$BenfBankCode=$AryData['BenfBankCode']; 
		$AdvanceAmount=$AryData['AdvanceAmount']; 
		$AMCDeductionUSD=$AryData['AMCDeductionUSD']; 
		$AMCDeductionINR=$AryData['AMCDeductionINR']; 
		
		
		
		$ConsigneeID=$AryData['ConsigneeID'];
		$BuyerID=$AryData['BuyerID'];
		$ConsigneeName=$AryData['ConsigneeName'];
		$ConsigneeAddress=$AryData['ConsigneeAddress'];
		$BuyerName=$AryData['BuyerName'];
		$BuyerAddress=$AryData['BuyerAddress'];
		$PoNo=$AryData['PoNo'];
		
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$FinalDestination=$AryData['FinalDestination'];
		$PlaceOfReceiptBy=$AryData['PlaceOfReceiptBy'];
		$QtyUnit=$AryData['QtyUnit'];
		
		
		$InvNo=$AryData['InvNo'];
		$InvDate=$AryData['InvDate'];
		$PoNo=$AryData['PoNo'];
		$PoDate=$AryData['PoDate'];
		$TextSize=$AryData['TextSize'];
		
		$ProcessorName=$AryData['ProcessorName'];
		$ProcessorAddress=$AryData['ProcessorAddress'];
		$ProcessorUsfdaNo=$AryData['ProcessorUsfdaNo'];
		$ProcessorFEI=$AryData['ProcessorFEI'];
		$ProcessorApprovalNo=$AryData['ProcessorApprovalNo'];
		
		$ShowShippingTerms=$AryData['ShowShippingTerms'];
		$ShowInvoicevalue=$AryData['ShowInvoicevalue'];
		
		$ShowBrand=$AryData['ShowBrand'];
		$ShowFrightCharge=$AryData['ShowFrightCharge'];
		$ShowInsurance=$AryData['ShowInsurance'];
		$ShippingTerms=$AryData['ShippingTerms'];
		$ProcessorIeCustomsNo=$AryData['ProcessorIeCustomsNo'];
		$ExporterRecAdd=$AryData['ExporterRecAdd'];
		$PaymentTerms=$AryData['PaymentTerms'];
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$DestinationCountry=$AryData['DestinationCountry'];
		$DeliveryTerms=$AryData['DeliveryTerms'];
		$ShowDeliveryTerms=$AryData['ShowDeliveryTerms'];
		$QtyUnit=$AryData['QtyUnit'];
		$TotalMcs=$AryData['TotalMcs'];
		$IGST=$AryData['IGST'];
		$CInvCode=$AryData['CInvCode'];
		$NoOfDays=$AryData['NoOfDays'];
		$ShipByDate=$AryData['ShipByDate'];
		$SignURL=$AryData['SignURL'];
		$AuthSign=$AryData['AuthSign'];
		$PreparedBy=$AryData['PreparedBy'];
		$BankID=$AryData['BankID'];
		$showExpUsFda=$AryData['showExpUsFda'];
		$showExpIECode=$AryData['showExpIECode'];
		$showExpGSTNo=$AryData['showExpGSTNo'];
		$ShowContent=$AryData['ShowContent'];
		$ShowBankDetails=$AryData['ShowBankDetails'];
		//$ShowPoNo=$AryData['ShowPoNo'];
		$showSignature=$AryData['showSignature'];
				

		
		$showProUsFda=$AryData['showProUsFda'];
		$showProFEI=$AryData['showProFEI'];
		$PortOfLoading=$AryData['PortOfLoading'];
		$showAdvance=$AryData['showAdvance'];
		$ShowNoofDays=$AryData['ShowNoofDays'];
		
		$ContainerNo=$AryData['ContainerNo'];
		$SealNo=$AryData['SealNo'];
		
		
		
		$showIgst=$AryData['showIgst'];
		$showDiscount=$AryData['showDiscount'];
		
		$AryPaymentData=getPaymentValues($iconn,$PoID);
		$InsuranceValue=$AryPaymentData['InsuranceValue'];

		
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
				
				$content .= '
				<tr>
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>'.$ExporterName.' 
				<BR> '.$ExporterAddress.'<BR>';
				
				if($showExpUsFda){
				$content .= '	USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>';
				}
				
				if($showExpIECode){
				$content .= ' IE CODE  : '.$ExporterIeCode;
								
				}
				
				if($showExpGSTNo){
				$content .= '<BR> GST NO  : '.$ExporterGstNo;
				}
				
				
				$content .= '</font></th></tr>';

				
		/*	$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				<br>
				</font></th>
			
				</tr>
				
				 '; */
				
				
				if($ConsigneeID!=$BuyerID){
		$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>Applicant</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				<br><b><u>Consignee</u></b><br>
				'.$BuyerName.',<br>'.$BuyerAddress.'
				</font></th>
				</tr>';
			}else{
				$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				
				
				</font></th>
				</tr>';	
			}
			
			
							
			$content .= '	<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">'.$PlaceOfReceiptBy.'</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR>  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">'.$PortOfLoading.'</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">'.$PortOfDischarge.'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">'.$FinalDestination.'</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				<br> '.$InvNo.',Dated '.$InvDate.' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Buyer`s Order No. & Date. </b>
				<br> '.$PoNo.$PoDate.' </font></th>
				</tr>';
				
				/*if($ShippingTerms=='FOB'){
					$FinalDestination=$PortOfLoading;
				}
				*/
				
				$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# '.$ProcessorApprovalNo.'</b>
				<br>
				<u><b> Packer</u> :</b><br> '.$ProcessorName.'<br> '.$ProcessorAddress.' ';
				
				if($showProUsFda){			
				$content .= '<br> USFDA REGISTRATION NO  : '.$ProcessorUsfdaNo;
				}
				
				if($showProFEI){			
				$content .= '<BR> FEI # :  '.$ProcessorFEI;
				}
		
				$content .= '</font></th></tr>';
	
	
	
			
			
			
			
			
				
				
	
			//  if($ShowShippingTerms){
				
			// 	$content .= '	<tr> 
           		
            //     <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br>
			// 	<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN # '.$ProcessorIeCustomsNo.'
			// 	<br> '.$ExporterRecAdd.' </font></th>
			// 	</tr>';
			// 	}else{
				
			//  $content .= '	<tr> 
           		
            //     <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br></font></th>
			// 	</tr>';
			// 	}
				
			$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  '.$DestinationCountry.'</b></font></th>
				</tr>
				<tr> 
				<th  align="LEFT"  width="100%" ><font size="7"><b><u> Terms of Delivery and Payment:</u>
				</b><br><BR> ' .$PaymentTerms .' 
				';
				if($ShowNoofDays){
					$content .= ' ' .$NoOfDays .'';
				}
				$content .= '<br>
				 SHIPMENT TERM : '.$ShippingTerms .' '.strtoupper($FinalDestination).'';
				
				if($ShowDeliveryTerms){				
				$content .= '('.$DeliveryTerms.')';
				}
				
				if($ShowInsurance){
					$content .= '<BR><b> INSURANCE: '.number_format($InsuranceValue,2).'</b>'; 
				 }
				/*if($LcStatus==1){
				$content .= '
				<br> D.C NUMBER : '.$LcNumber.'  ,DATED : '.$LcDate.'<br> BL NUMBER : '.$BLNo.'    ,DATED :'.$BLDate.' <br> SB NUMBER : '.$SBNo.'  ,DATED : '.$SBDate.' ';
				}
				else{
				$content .= '
				<br> BL NUMBER : '.$BLNo.'    ,DATED :'.$BLDate.' <br> SB NUMBER : '.$SBNo.'  ,DATED : '.$SBDate.'';
				}*/
				
				$content .= '</font> </th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> 
				
				
				
				
				
				
				<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> '.$TotalMcs.'</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br></font><font size="10"><b align="center">'.$ContainerNo.'</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br></font><font size="10"><b align="center">'.$SealNo.'</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in LBS</font></th>
               <th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Remarks </font></th>
                </tr>
				
				</table>
				</th>
				</tr>';
				
				
				$description_head='<B><u>Description of Goods: </u></B><BR>1 X 40 FCL IN FROZEN FORM OTHER THAN AFD';
				/*if($ShowPoNo){
					$description_head .='<BR>PO NO. '.$PoNo.'<BR>';
				}*/
				
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$Whitspace='<b style="color:white"> yuhju</b>';
				$FontSize="7";
				//$TOTAL_MCS=0;
			$content.='<tr >
				
			<table border="0" cellspacing="0" cellpadding="0">
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$description_head.' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			
			</tr>
			'
			;

			//   $SqlVarietyMcs="
			// SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,
			// PackingStyleID,BAP FROM 
			// `ScrmCIGradeData` 
			// left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			//  WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand"; 
			 
			 $SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,
			PackingStyleID,BAP FROM 
			`ScrmPoGradeData` 
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
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
			
			if($ShowBrand){
			$content.='<B> BRAND </B>: '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
			}
			
							
		$content.=' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>
			

			
			<tr>
			
			<th align="center" width="40%" ><font size="'.$FontSize.'"> <u>'.$TextSize.'</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="28%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>';
			
			
			// 	   $SqlVariety="SELECT * FROM `ScrmCIGradeData` as PGD
			// left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'
			// and Variety='$Variety' and Brand='$Brand'  
			// and	PackingStyleID='$PackingStyleID' order by abs(Grade)";  
			
			$SqlVariety="SELECT * FROM `ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'
			and Variety='$Variety' and Brand='$Brand'  
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)"; 
			$StmtVariety=$iconn->query($SqlVariety);
				
			while($RowVariety=$StmtVariety->fetch_assoc()){
				
				$AryPriceBreakUp=getBreakUpData($iconn,$RowVariety['ID']);
				$Qty=$AryPriceBreakUp['Qty'];
				$UsdValue=$AryPriceBreakUp['UsdValue']; 
				$TotalMcs=$AryPriceBreakUp['TotalMcs'];
				$Price=$AryPriceBreakUp['Price'];
				$UnitPrice=$AryPriceBreakUp['UnitPrice'];
				
				
				
			$content.='
				<tr>
				<td align="center" width="40%"  ><font size="'.$FontSize.'">
				'.$RowVariety['Grade'].'</font></td>
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.$TotalMcs.'</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.number_format($Qty,3).'	</font></td>
				
				<td align="center" width="28%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				</font></td>
				
				</tr>';
			}


			}
			
			
			
			
				$AryPriceData=getBreakUpData($iconn,$RowVariety['ID'],$PoID);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				$TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
		
			
			
			$content.='
				<tr>
				
				<td align="center" width="40%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black;border-bottom: 1px solid black; border-right: 1px solid black;" ><font size="7">
				'.$TotalMcs.'</font></td>
				
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;"><font size="7">
				'.number_format($Qty,3).'</font></td>
				
				<td align="center" width="28%" style="border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;"><font size="7">
				</font></td>
				
				
				</tr>';
				
				//$AryPaymentData=getPaymentValues($iconn,$PoID);
				$ExchangeRate=$AryPaymentData['ExchangeRate'];
				$DiscountPerc=$AryPaymentData['DiscountPerc'];
				$DiscountAmount=$AryPaymentData['DiscountAmount'];
				$IgstPerc=$AryPaymentData['IgstPerc'];
				$IgstValue=$AryPaymentData['IgstValue'];
		
				$InvValueUSD=$AryPaymentData['InvValueUSD']-$AdvanceAmount;
				//$InvValueInr=$AryPaymentData['InvValueINR'];
				$InvValueInr=$InvValueUSD*$ExchangeRate;
			
				
				
			
			$content.= '</table>
			  </tr>';

$BILL_WORD=numberTowords($InvValueUSD);

$FrieghtCharge=$AryPaymentData['FrieghtCharge'];
$DiscountAmount=$AryPaymentData['DiscountAmount'];
$InvValueUSDWithOutDuties=$AryPaymentData['InvValueUSDWithOutDuties'];
$InvValueINRWithOutDuties=$AryPaymentData['InvValueINRWithOutDuties'];
$DutiesPrice=$AryPaymentData['DutiesPrice'];
$FOBValueUSD=$AryPaymentData['FOBValueUSD'];



$NetWgtKgs=$AryPriceData['NetWgtKgs'];
$NetWgtLbs=$AryPriceData['NetWgtLbs'];
$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];
				
		

				
				
			$content.='<tr>
			<table border ="0" cellspacing="0" cellpadding="0">'; 
	 		$content.='<tr>
			<td><font size="7"><br> SHIPPING TERMS   : '.$ShippingTerms.'  '.$FinalDestination.'
			<BR>
            </font></td>
			</tr>';
			
			
			
			$NetWgtKgs=$AryPriceData['NetWgtKgs'];
$NetWgtLbs=$AryPriceData['NetWgtLbs'];
// $GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
// $GrossWeightLbs=$AryPriceData['GrossWeightLbs'];
/**uma 20-12-2023* */


$TotalGrossWtKgs=$AryData['TotalGrossWtKgs'];

if($TotalGrossWtKgs!=''){
	if($QtyUnit=='KGS'){
	$GrossWeightKgs=$TotalGrossWtKgs;
	$GrossWeightLbs=$TotalGrossWtKgs*2.204;
	}else{
	$GrossWeightKgs=$TotalGrossWtKgs*0.454;
	$GrossWeightLbs=$TotalGrossWtKgs;
	}
}
else{
	$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];			
	$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];
}

			
			if($QtyUnit=='KGS'){
			$content.='<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7"><b>NET WEIGHT: </b></font></td>
			<td align="left" width="15%" ><font size="7"> <b>'.number_format($NetWgtKgs,3).' KGS</b></font></td>
			</tr>';
			
			$content.='<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7"><b>GROSS WEIGHT:</b> </font></td>
			<td align="left" width="15%" ><font size="7"> <b>'.number_format($GrossWeightKgs,3).' KGS</b></font></td>
			</tr>';	
				
			}else{
			
			$content.='<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7">NET WEIGHT </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: '.number_format($NetWgtLbs,3).' LBS</b></font></td>
			<td align="left" width="60%" ><font size="7"> <b> '.number_format($NetWgtKgs,3).' KGS</b></font></td>
			</tr>';
			
			$content.='<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7">GROSS WEIGHT </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: '.number_format($GrossWeightLbs,3).' LBS</b></font></td>
			<td align="left" width="30%" ><font size="7"> <b> '.number_format($GrossWeightKgs,3).' KGS</b></font></td>
			</tr>';
			}
		

			$content.='</table></tr>';
			
			
			$content.='<tr>';
				 
		
		
               $content.='</tr>
				
						
	        
				<tr>  
           		<!--<th  align="LEFT"  width="100%"><font size="7"> AMOUNT CHARGABLE IN US.$: 
				<b>'.$BILL_WORD.'</b><BR></font></th>  -->  
				
				 
				
                
</tr>
<tr>
<th  align="bottom"  width="70%"><font size="8"><B><U> DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>';
				
           
		  $content.='<th  align="center"  width="30%"><font size="7"> Signature:<br><br>
				<B> '.$AuthSign.',</B><BR><BR><BR>
				 
				<!--<img src="'.$SignURL.'" 
				 width="100" height="30"style="width: 100" align="center" align="middle"/>-->
								
				
				<p STYLE="text-align:center" > Authorised Signature   </p></font></th>';
				
$content.='</tr>
				
				
				</table><p STYLE="text-align:left"><font size="7">CC: SURVEYOR </font></p>'; 
	
	return $content;
	
	}
	
 
 
 $SqlPaymentTerms="SELECT * FROM ScrmPoData as SPD 
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 
 
 

	
		
function getReportData($iconn,$PoID){
		
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


	  /*$CustomInvoiceSql="SELECT *  
		FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
		WHERE SCV.PiID='$PoID'"; */
		
		//  $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
        // SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading  
		// FROM ScrmCustomInvoices as SCV 
		// left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
		// left JOIN ScrmShipmentDispatchDetails as SSDD on SSDD.PIID=SCV.PiID
	    // left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        // left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
        // left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        // left JOIN ScrmCountriesList as SL on SL.id=SP.Country
		// WHERE SCV.PiID='$PoID'"; 	
		
		$CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
        SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading ,SCF.name as FinalDestinationCountry,SPCF.Name as FinalDestination
		,SCV.ContainerNo FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID
	    left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
		left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SL on SL.id=SP.Country
		left JOIN ScrmCountriesList as SCF on SCF.id=SPCF.Country
        left JOIN ScrmISFDetails as ISF on ISF.PIID=SCV.PiID
		WHERE SCV.PiID='$PoID'"; 
			$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
			$CustomInvData = $CustomInvoiceExec->fetch_assoc();
			$IGST=$CustomInvData['IGST'];
			$OverSeasCommission=$CustomInvData['OverSeasCommission'];
			$CInvDate=$CustomInvData['CInvDate'];
			$CInvCode=$CustomInvData['CInvCode'];
			$BLDate=date('d-m-Y',strtotime($CustomInvData['BLDate']));
			$SBDate=date('d-m-Y',strtotime($CustomInvData['SBDate']));
			$BLNo=$CustomInvData['BLNo'];
			$SBNo=$CustomInvData['SBNo'];
			$LcNumber=$CustomInvData['LcNumber'];
			$LcDate=getFormatedDateNA($CustomInvData['LcDate']);
			$CIDescription=$CustomInvData['CIDescription'];
			$NoOfDays=$CustomInvData['NoOfDays'];
			$TotalGrossWt=$CustomInvData['TotalGrossWt'];


			if($NoOfDays==0){
				$NoOfDays='';

			}
			else{

				$NoOfDays=$NoOfDays.' DAYS FROM BL';
			}
			

		 $SqlDedINR="SELECT * FROM `ScrmDatewiseValues` where FromDate<='$CInvDate' and Type= 'AMCDeductionINR' ORDER BY ID DESC limit 1";
		 
		 $SqlDedUSD="SELECT * FROM `ScrmDatewiseValues` where FromDate<='$CInvDate' and Type= 'AMCDeductionUSD' ORDER BY ID DESC limit 1";
		 
			$StmtDedINR=$iconn->query($SqlDedINR);
			$StmtDedUSD=$iconn->query($SqlDedUSD);
           
           $RowDedINR=$StmtDedINR->FETCH_ARRAY();
           $RowDedUSD=$StmtDedUSD->FETCH_ARRAY();

		  

			

  //$SqlMcsTotal="SELECT sum(Mcs) as Mcs FROM `ScrmCIGradeData` where InvId='$PoID'  ";
  $SqlMcsTotal="SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
  $StmtMcsTotal=$iconn->query($SqlMcsTotal);
  $RowMcs=$StmtMcsTotal->fetch_assoc();
  $TotalMcs=$RowMcs['Mcs'];		
  



  $SqlScrmPoData="SELECT *,SCDB.ConsigneeName as BuyerName,
 SCDB.ConsigneeAddress as BuyerAddress,SPD.Status as PoStatus,ALD.SignURL,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,

ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,
ABD.SwiftCode as SwiftCode,
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode 
	FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmConsigneeDetails as SCDB on SPD.BuyerID=SCDB.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN AdminLoginDetails as ALD on SC.id=SPD.ApprovedBy
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms

 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];
 $LcStatus=$PoDataRow['LcStatus'];
 

 $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
$StmtLogin=$iconn->query($SqlLogin); 
$RowLogin = $StmtLogin->fetch_assoc();
$SignURL=$RowLogin['SignURL'];
		
		$ExporterData=getExporterDetails($ExpID);
		$ProcessorData=getExporterDetails($ProcessorID);
		
		if($CustomInvData['BankID']==0){
			 $BankID=$PoDataRow['BankID'];
}
else{
	$BankID=$CustomInvData['BankID'];
}

 $SqlBank="SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
$StmtBank=$iconn->query($SqlBank); 
$RowBank = $StmtBank->fetch_assoc();
$BenfName=$RowBank['AccntName'];
$BenfAccNo=$RowBank['AccountNumber'];
$BenfBankName=$RowBank['BankName'];
$BenfBankAddress=$RowBank['BankAddress'];
$BenfBankCode=$RowBank['SwiftCode'];
		
		
		/*if($CustomInvData['Discount']!=0){
		$AryData['showDiscount']=true;	
		}else{
		$AryData['showDiscount']=false;	
		}	*/
		
		if($CustomInvData['IGST']=='DISCOUNT'){
		$AryData['showDiscount']=true;	
		}else{
		$AryData['showDiscount']=false;	
		}
		
		
		
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
		
		/*if($PoDataRow['DestinationCountry']!=26 && $BankID!=5){
		$AryData['ShowBankDetails']=true;	//uma
		}
		else{
		$AryData['ShowBankDetails']=false;	
		}

		if($PoDataRow['DestinationCountry']!=26 && $BankID==5){
		$AryData['ShowContent']=true;	//uma
		}else{
		$AryData['ShowContent']=false;	
		}
		*/
		/*if($PoDataRow['DestinationCountry']!=26){
		$AryData['ShowPoNo']=true;	//uma
		}else{
		$AryData['ShowPoNo']=false;	
		}
		*/
		/*if($PoDataRow['ShippingTerms']=='FOB'){
		$AryData['ShowInvoicevalue']=true;	//uma
		}else{
		$AryData['ShowInvoicevalue']=false;	
		}*/
		
		
		if($PoDataRow['DestinationCountry']!=26){
		//if($PoDataRow['DestinationCountry']!=26 && $PoDataRow['ShippingTerms']!='FOB'){  //uma
		$AryData['ShowDeliveryTerms']=true;	
		}else{
		$AryData['ShowDeliveryTerms']=false;	
		}
		

		if($ExpID!=5 && $PoDataRow['DestinationCountry']!=26){
		$AryData['showExpUsFda']=true;	
		}
		else{
		$AryData['showExpUsFda']=false;	
		}
		if($IGST !='DISCOUNT'){			
		 $AryData['ShowNoofDays']=true;	
		}else{
		$AryData['ShowNoofDays']=false;
		}
		
		if($ExpID==5 || $PoDataRow['DestinationCountry']!=26){
		$AryData['showExpIECode']=true;
		$AryData['showExpGSTNo']=true;
		}
		else{
			$AryData['showExpIECode']=false;
		$AryData['showExpGSTNo']=false;
		}
		
		
		if($PoDataRow['DestinationCountry']!=26){
		$AryData['showSignature']=true;	
		}else{
		$AryData['showSignature']=false;	
		}
		
		if($PoDataRow['DestinationCountry']!=26){
		$AryData['showProFEI']=true;	
		}else{
		$AryData['showProFEI']=false;	
		}
		
		if($PoDataRow['ShowBrand']==1){
		$AryData['ShowBrand']=true;	
		}else{
		$AryData['ShowBrand']=false;	
		}
		if($PoDataRow['ShowFrightCharge']==1 && $PoDataRow['ShowFrieght']==1){
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
		
		if($CustomInvData['AdvanceAmount']>0){
		$AryData['showAdvance']=true;	
		}else{
		$AryData['showAdvance']=false;	
		}
		
		
		
		if($PoDataRow['PoNo'] ==''){			
		$AryData['PoDate']='';	
		}else{
		$AryData['PoDate']= "& Dated :".getFormatedDate($PoDataRow['PoDate']);
		}
		
		/*if($PoDataRow['DestinationCountry']==26){
		$QtyUnit='KGS';	//uma
		}else{
		$QtyUnit='LBS';	
		}
		*/
		/*if($PoDataRow['DestinationCountry']==26){
		$AryData['TextSize']='SIZE(PCS/LB)';	//uma
		}else{*/
		$AryData['TextSize']='SIZE';	
		//}
		 
		
		
		
		/* Return Data*/
		$AryData['PoStatus']=$PoDataRow['PoStatus']; 
		$AryData['TotalGrossWtKgs']=$TotalGrossWt;  
		$AryData['ApprovedBy']=$ApprovedBy; 
		$AryData['PaymentTerms']=$PaymentTerms; 
		$AryData['DeliveryTerms']=$DeliveryTerms; 
		$AryData['TotalMcs']=$TotalMcs; 
		$AryData['ExporterID']=$ExpID; 
		$AryData['LcStatus']=$LcStatus; 
		/*$AryData['BLDate']=$BLDate; 
		$AryData['SBDate']=$SBDate; 
		$AryData['BLNo']=$BLNo; 
		$AryData['SBNo']=$SBNo; 
		$AryData['LcNumber']=$LcNumber; 
		$AryData['LcDate']=$LcDate; */
		$AryData['CIDescription']=$CIDescription; 
		$AryData['BenfName']=$BenfName; 
		$AryData['BenfAccNo']=$BenfAccNo; 
		$AryData['BenfBankName']=$BenfBankName; 
		$AryData['BenfBankAddress']=$BenfBankAddress; 
		$AryData['BenfBankCode']=$BenfBankCode;
		$AryData['AdvanceAmount']=$CustomInvData['AdvanceAmount'];
		$AryData['AMCDeductionINR']=$RowDedINR['Value'];
		$AryData['AMCDeductionUSD']=$RowDedUSD['Value'];
 
		
		$AryData['ExporterName']=$ExporterData['ExpName'];;
		$AryData['ExporterAddress']=$ExporterData['ExpAddress'];
		$AryData['ExporterUsfdaNo']=$ExporterData['ExpUsfdaNo'];
		$AryData['ExporterIeCode']=$ExporterData['ExpIeCode'];
		$AryData['ExporterGstNo']=$ExporterData['ExpGstNo'];
		$AryData['DestinationCountryId']=$ExporterData['DestinationCountry'];
		$AryData['ExporterRecAdd']=$ExporterData['ImporterRecordAddress']; 
		$AryData['AuthSign']=$ExporterData['AuthSign']; 
		
		
		
		$AryData['ConsigneeID']=$PoDataRow['ConsigneeID'];
		$AryData['BuyerID']=$PoDataRow['BuyerID'];
		$AryData['ConsigneeName']=$PoDataRow['ConsigneeName'];
		$AryData['ConsigneeAddress']=$PoDataRow['ConsigneeAddress'];
		$AryData['BuyerName']=$PoDataRow['BuyerName'];
		$AryData['BuyerAddress']=$PoDataRow['BuyerAddress'];
		$AryData['PoNo']=$PoDataRow['PoNo'];
		$AryData['QtyUnit']=$PoDataRow['QtyUnit'];
		
		
		
		$AryData['PortOfDischarge']=strtoupper($CustomInvData['PortofDischarge']).' '.strtoupper($CustomInvData['CountryCode']).', '.$CustomInvData['PortofDischargeCountry'];
		
		
		 /*if($PoDataRow['DestinationCountry']==26){
			
			$AryData['PlaceOfReceiptBy']=str_ireplace("PORT"," ",$CustomInvData['PlaceOfRecipt']);
		$AryData['PortOfLoading']=str_ireplace("PORT"," ",strtoupper($CustomInvData['PortOfLoading'])).','.strtoupper($CustomInvData['PortOfLoadingCountry']);
			}
			ELSE{*/
				$AryData['PlaceOfReceiptBy']=$CustomInvData['PlaceOfRecipt'];
		$AryData['PortOfLoading']=strtoupper($CustomInvData['PortOfLoading']).','.strtoupper($CustomInvData['PortOfLoadingCountry']);
			//}
		
		
			$AryData['FinalDestination']=strtoupper($CustomInvData['FinalDestination']).','.strtoupper($CustomInvData['FinalDestinationCountry']);

          
		
		//$AryData['InvNo']=getInvoiceNumber($iconn,$ExpID,$PoID) ;
		$AryData['InvNo']=$CustomInvData['CustomsInvNo'];
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
		//$AryData['QtyUnit']=$PoDataRow['QtyUnit'];
		$AryData['ShipByDate']=$PoDataRow['ShipByDate'];
		$AryData['SignURL']=$SignURL;
		$AryData['PreparedBy']=$PoDataRow['PreparedBy'];
		$AryData['BankID']=$PoDataRow['BankID'];
		$AryData['IGST']=$IGST;
		$AryData['CInvCode']=$CInvCode;
		$AryData['NoOfDays']=$NoOfDays;			

		
		$AryData['ContainerNo']=$CustomInvData['ContainerNo'];
		$AryData['SealNo']=$CustomInvData['LinerSealNo'];
		//$AryData['SealNo']=$CustomInvData['SealNo'];

		
		return $AryData;
	}
	
	
function getBreakUpData($iconn,$GradeID,$PiID){
		
		if($PiID==''){
			$Qry="WHERE ID='$GradeID'";
		}else{
		$Qry="WHERE InvId='$PiID'";	
		}
		
		$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
							
			// $SqlTotal="SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
			// sum(Price) as Price,			
			// sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			// sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			// `ScrmCIGradeData` 
			// as PGD left join AdminPackingStyles as PS 
			// on PGD.PackingStyleID=PS.PCKID $Qry";
			
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

			$SqlQtyUnit="SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'"; 
			$StmtQtyUnit=$iconn->query($SqlQtyUnit);
			$RowQtyUnit=$StmtQtyUnit->fetch_assoc();
			$QtyUnit=$RowQtyUnit['QtyUnit'];
			$UnitPrice=number_format($RowTotal['Price'],2);

				
			if($QtyUnit=='KGS'){
			$Qty=$RowTotal['NetWgtKgs'];
			$UsdValue=$RowTotal['PriceKgs'];
			 $UnitPrice=number_format($RowTotal['Price'],2); 
			}else{
			//$Qty=$RowTotal['NetWgtLbs']*0.454;
			$Qty=$RowTotal['NetWgtLbs'];
			$UsdValue=$RowTotal['PriceLbs'];
			$UnitPrice=round(($UsdValue/$Qty),6);
			}
			
			$AryPriceData['Price']=$RowTotal['Price'];
			$AryPriceData['TotalMcs']=$RowTotal['MCS'];
			$AryPriceData['Qty']=$Qty;
			$AryPriceData['UsdValue']=$UsdValue;
			$AryPriceData['UnitPrice']=$UnitPrice;
			
			/*$AryPriceData['NetWgtKgs']=$RowTotal['NetWgtKgs'];
			$AryPriceData['NetWgtLbs']=$RowTotal['NetWgtLbs'];
			$AryPriceData['GrossWeightKgs']=$RowTotal['GrossWeightKgs'];
			$AryPriceData['GrossWeightLbs']=$RowTotal['GrossWeightLbs'];*/
			
				if($QtyUnit=='KGS'){
				 $NetWgtKgs=$RowTotal['NetWgtKgs'];
				$GrossWeightKgs=$RowTotal['GrossWeightKgs'];
			}
			else{
				$NetWgtKgs=$RowTotal['NetWgtLbs']*0.454; 
				$GrossWeightKgs=$RowTotal['GrossWeightLbs']*0.454;
				$NetWgtLbs=$RowTotal['NetWgtLbs'];
				$GrossWeightLbs=$RowTotal['GrossWeightLbs'];
				
			}
			
			
			 $AryPriceData['NetWgtKgs']=$NetWgtKgs;
			$AryPriceData['NetWgtLbs']=$NetWgtLbs;
			$AryPriceData['GrossWeightKgs']=$GrossWeightKgs;
			$AryPriceData['GrossWeightLbs']=$GrossWeightLbs;

return 	$AryPriceData;		
		
	}


function getBankDetails($iconn,$BankID){
	
$SqlBank="SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
$StmtBank=$iconn->query($SqlBank); 
$AryBankData = $StmtBank->fetch_assoc();

return $AryBankData;

}	


?>