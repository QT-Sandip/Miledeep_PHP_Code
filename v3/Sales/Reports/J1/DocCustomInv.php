<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");

$AdminConfigDetails = json_decode(json_encode($_SESSION['ClientConfigData']), true);

$PoID = $_GET['id'];
$Type = $_GET['Type'];

$SqlCustom = "SELECT IGST,SPD.ExporterID as ExporterID ,SPD.ConsigneeID,SPD.DestinationCountry FROM `ScrmCustomInvoices`  as SCV
left join ScrmPoData as SPD on  SPD.PoID=SCV.PiID
WHERE PiID='$PoID'";

//echo $SqlCustom; exit;
$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom->fetch_assoc();
$IGST = $RowCustom['IGST'];
$ExpID = $RowCustom['ExporterID'];
$CountryID = $RowCustom['DestinationCountry'];
$BuyerID = $RowCustom['ConsigneeID'];
$DocType = 'CUI';
$AryShowConditions = getInvoiceShowConditions($DocType, $CountryID, $BuyerID);


class MYPDF extends TCPDF
{

	public function Header()
	{
		//	
		/*getCustomInvData($iconn,$PoID);
																																																																																																										 $IGST=$AryData['IGST'];*/
		global $IGST;
		if ($IGST == 'IGST') {
			$Header2 = 'SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
		} else {
			$Header2 = '';
		}

		global $Header;
		$image_file = 'images/ds_logo.jpg';
		$this->SetFont('helvetica', 'B', 8);
		$this->Cell(120, 0, 'CUSTOMS INVOICE CUM PACKING LIST' . $Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', ' ', 8);
		$this->Cell(130, 0, '' . $Header2, 0, 0, 'R', 0, '', 0, false, 'T', 'M');

		$this->SetTextColor(255, 0, 0);

		$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
	}

	public function Footer()
	{
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}

/*if($Type==''){
$Type='I';
}*/

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('CUSTOMS INVOICE CUM PACKING LIST' . $Header);
$pdf->SetTitle('' . $Header2);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(4);
$pdf->SetMargins(10, 10, 10, 5);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();


$TableData = getCustomInvData($iconn, $PoID);
$pdf->writeHTML($TableData);
$pdf->SetFont('helvetica', '', 6);


$ArrayFilename = getfilename($iconn, $ExpID, $PoID);

$InvShortCode = $ArrayFilename['InvShortCode'];
$CInvNo = $ArrayFilename['CInvNo'];
$FinancialYear = $ArrayFilename['FinancialYear'];

$file_name = $InvShortCode . '-CUS-' . $CInvNo . '-' . $FinancialYear . '.pdf';

//sme/pi/233/2022-23




//echo $Type;
ob_end_clean();

if ($Type == '') {
	$Type = 'I';
} else {
	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '', $file_name);
	;
	$file_name = $Fname . $file_name;
}

$pdf->Output($file_name, $Type);





function getCustomInvData($iconn, $PoID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $DocType, $AdminConfigDetails;
	$AryData = getReportData($iconn, $PoID);



	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];

	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$PlaceOfReceiptBy = $AryData['PlaceOfReceiptBy'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$ShowInvoicevalue = $AryData['ShowInvoicevalue'];

	$ShowBrand = $AryData['ShowBrand'];
	$ShowFrightCharge = $AryData['ShowFrightCharge'];
	$ShowInsurance = $AryData['ShowInsurance'];
	$ShippingTerms = $AryData['ShippingTerms'];
	$ProcessorIeCustomsNo = $AryData['ProcessorIeCustomsNo'];
	$ExporterRecAdd = $AryData['ExporterRecAdd'];
	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$DestinationCountry = $AryData['DestinationCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$QtyUnit = $AryData['QtyUnit'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$CurrencyShortCode = $AryData['CurrencyShortCode'];
	$TotalMcs = $AryData['TotalMcs'];
	$IGST = $AryData['IGST'];
	$CInvCode = $AryData['CInvCode'];
	$NoOfDays = $AryData['NoOfDays'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];

	$showProUsFda = $AryData['showProUsFda'];
	$ShowNoofDays = $AryData['ShowNoofDays'];
	$showProFEI = $AryData['showProFEI'];
	$PortOfLoading = $AryData['PortOfLoading'];

	$ContainerNo = $AryData['ContainerNo'];
	$SealNo = $AryData['SealNo'];
	$QCertificateNo = $AryData['QCertificateNo'];


	$showIgst = $AryData['showIgst'];
	$showDiscount = $AryData['showDiscount'];
	$ShowCWiseBrand = $AryData['ShowCWiseBrand'];

	$AryPaymentData = getPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetails);
	$InsuranceValue = $AryPaymentData['InsuranceValue'];

	$showNotifyParty = $AryData['showNotifyParty'];
	$NotifyPartyName = $AryData['NotifyPartyName'];
	$NPartyAddress = $AryData['NPartyAddress'];
	$NPartyContactNumber = $AryData['NPartyContactNumber'];

	$content = '';


	$size = 45;
	$size1 = 55;



	$StyleTbl = 'Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 10px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';

	$StyleTd = 'Style=" margin: 0;
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
				<br>' . $ExporterName . ' 
				<BR> ' . $ExporterAddress . '<BR>';

	if ($showExpUsFda) {
		$content .= '	USFDA REGISTRATION NO  : ' . $ExporterUsfdaNo . '<BR>';
	}

	if ($showExpIECode) {
		$content .= ' IE CODE  : ' . $ExporterIeCode;
	}

	if ($showExpGSTNo) {
		$content .= '<BR> GST NO  : ' . $ExporterGstNo;
	}


	$content .= '</font></th></tr>';


	/*	$content .= '		<tr>  

																																																								 <th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
																																																								 <br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
																																																								 <br>
																																																								 </font></th>

																																																								 </tr>

																																																								  '; */


	if ($BuyerID == '' || $BuyerID == 0) {


		$content .= '		<tr>  
						   
						<th  align="LEFT" ><font size="7"><b><u>Consignee</u></b>
						<br>' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
						
						
						</font></th>
						</tr>';
	} elseif ($ConsigneeID != $BuyerID) {
		$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>Applicant</u></b>
				<br>' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
				<br><b><u>Consignee</u></b><br>
				' . $BuyerName . ',<br>' . $BuyerAddress . '
				</font></th>
				</tr>';
	} else {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
				
				
				</font></th>
				</tr>';
	}

	if ($showNotifyParty) {
		$content .= '		<tr>  
		   
		<th  align="LEFT" ><font size="7"><b><u>Notify Party</u></b>
		<br>' . $NotifyPartyName . ',<br>' . nl2br($NPartyAddress) . '
		<!--<br>TEL:' . $NPartyContactNumber . '-->
		
		
		</font></th>
		</tr>';
	}


	$content .= '	<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">' . $PortOfLoading /*$PlaceOfReceiptBy*/ . '</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR>  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">' . $PortOfLoading . '</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">' . $PortOfDischarge . '</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">' . $FinalDestination . '</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				<br> ' . $InvNo . ',Dated ' . $InvDate . ' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Buyer`s Order No. & Date. </b>
				<br> ' . $PoNo . $PoDate . ' </font></th>
				</tr>';

	if ($ShippingTerms == 'FOB') {
		$FinalDestination = $PortOfLoading;
	}


	$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# ' . $ProcessorApprovalNo . '</b>
				<br>
				<u><b> PROCESSED & PACKED BY</u> :</b><br> ' . $ProcessorName . '<br> ' . $ProcessorAddress . ' ';

	if ($showProUsFda) {
		$content .= '<br> USFDA REGISTRATION NO  : ' . $ProcessorUsfdaNo;
	}

	if ($showProFEI) {
		$content .= '<BR> FEI # :  ' . $ProcessorFEI;
	}

	$content .= '</font></th></tr>';












	if ($ShowShippingTerms) {

		$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br>' . $BuyerName . ',' . $BuyerAddress . '<br>
				<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN # ' . $ProcessorIeCustomsNo . '
				<br> ' . $ExporterRecAdd . ' </font></th>
				</tr>';
	} else {

		$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br> ' . $BuyerName . ',<br>  ' . $BuyerAddress . '</font></th>
				</tr>';
	}

	$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  ' . $DestinationCountry . '</b></font></th>
				</tr>
				<tr> 
				<th  align="LEFT"  width="100%" ><font size="7"><b><u> Terms of Delivery and Payment:</u>
				</b><br><BR> ' . $PaymentTerms . ' ';
	if ($ShowNoofDays) {
		$content .= ' ' . $NoOfDays . '';
	}
	$content .= '<br> SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($FinalDestination) . '';

	if ($ShowDeliveryTerms) {
		$content .= '(' . $DeliveryTerms . ')<br>';
	}
	if ($ShowInsurance) {
		$content .= '<BR><b> INSURANCE: ' . number_format($InsuranceValue, 2) . '</b>';
	}

	$content .= '</font> </th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> 	<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> ' . $TotalMcs . '</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br></font><font size="10"><b align="center">' . $ContainerNo . '</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br></font><font size="10"><b align="center">' . $SealNo . '</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in KGS </font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>' . $UnitCurrency . '/KGS ' . $ShippingTerms . ' <br><b>' . strtoupper($FinalDestination) . '</b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in ' . $UnitCurrency . ' <br>' . $ShippingTerms . '<br><b> ' . strtoupper($FinalDestination) . '</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>';


	$description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL IN FROZEN FORM OTHER THAN AFD';
	$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
	$Whitspace = '<b style="color:white"> yuhju</b>';
	$FontSize = "7";
	//$TOTAL_MCS=0;
	$content .= '<tr >
				
			<table border="0" cellspacing="0" cellpadding="0">
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
		. $description_head . ' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			
			</tr>
			';

	$SqlVarietyMcs = "
			SELECT sum(Mcs) As VarietyMcs,Variety,Grade,PackingStyle,Brand,
			PackingStyleID,BAP,PrintStatus FROM 
			`ScrmPoGradeData` PGD
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			LEFT JOIN (SELECT PrintStatus,BrandName FROM `ScrmBrandsMaster` GROUP BY BrandName) SBM ON SBM.BrandName=PGD.Brand
			 WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand,BrandName Order by PGD.ID asc";

	// ECHO $SqlVarietyMcs; EXIT;
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	$TotalUsdValue = 0;
	$TotalGlazeValue = 0;
	$TotalMcsValue = 0;

	$TotalQtyValue = 0;

	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {

		// echo $VarietyMcsRow['VarietyMcs']; exit;

		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];

		$BAP = $VarietyMcsRow['BAP'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];

		if ($BAP == 'NA') {
			$BAP = '';
		} else {
			$BAP = $BAP;
		}

		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
			. $VarietyMcsRow['VarietyMcs'] . ' CARTONS OF ' . $VarietyMcsRow['Variety'] . '<br>
			PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' NET WEIGHT PER CARTON.' . $Whitspace . ' 
			';


		if ($BrandPrintStatus) {
			$BrandName = '<B> BRAND </B>: ' . $VarietyMcsRow['Brand'] . ' ';
		} else {
			$BrandName = '';
		}
		/*		if($ShowBrand){
																																																																																																													 $Brandp=$VarietyMcsRow['Brand'];
																																																																																																													 if( $VarietyMcsRow['Brand']=='NEUTRAL'){
																																																																																																														 $Brandp='';
																																																																																																													 }
																																																																																																												 $content.='<B> BRAND </B>: '.$Brandp.' <B><br>'.$BAP.'</B>';
																																																																																																												 }*/


		$content .= ' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>
			

			
			<tr>
			
			<th align="center" width="20%" ><font size="' . $FontSize . '"> <u>SIZE</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid white;"><font size="' . $FontSize . '"><u>QTY IN CS</u></font></th>
			';
		if ($ShowCWiseBrand && $BrandPrintStatus) {
			$content .= '<th align="center" width="20%" style="border-right:1px solid black;"><font size="' . $FontSize . '"><u>Brand</u></font></th>';
		} else {
			$content .= '<th align="center" width="20%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></th>';
		}

		$content .= '
			
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';


		$SqlVariety = "SELECT * FROM `ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'
			and Variety='$Variety' and Brand='$Brand'  
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";
		$StmtVariety = $iconn->query($SqlVariety);


		while ($RowVariety = $StmtVariety->fetch_assoc()) {

			$AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['ID'], '');
			$Qty = $AryPriceBreakUp['Qty'];
			$UsdValue = $AryPriceBreakUp['UsdValue'];
			$TotalMcs = $AryPriceBreakUp['TotalMcs'];
			$Price = $AryPriceBreakUp['Price'];
			$UnitPrice = $AryPriceBreakUp['UnitPrice'];
			$TotalUsdValue = $TotalUsdValue + $UsdValue;
			$TotalGlazeValue = $TotalGlazeValue + $AryPriceBreakUp['TotalQtyAfterGlaze'];
			$TotalMcsValue = $TotalMcsValue + $TotalMcs;
			$TotalQtyValue = $TotalQtyValue + $Qty;
			$content .= '
				<tr>
				<td align="center" width="20%"  ><font size="' . $FontSize . '">
				' . $RowVariety['Grade'] . '</font></td>
				<td align="center" width="18.5%" style="border-right: 1px solid white;"><font size="' . $FontSize . '">
				' . $TotalMcs . '</font></td>';
			if ($ShowCWiseBrand && $BrandPrintStatus) {
				$content .= '<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
				' . $RowVariety['Brand'] . '  </font></td>';
			} else {
				$content .= '<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
				</font></td>';
			}
			$content .= '<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($Qty, 3) . '	</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
					' . $UnitPrice . '</font></td>
				<td align="center" width="14.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($UsdValue, 2) . '</font></td>
				
				</tr>';
		}
	}




	$AryPriceData = getBreakUpData($iconn, $RowVariety['ID'], $PoID);
	// $Qty = $AryPriceData['Qty'];
	// $UsdValue = $AryPriceData['UsdValue'];
	// $TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];



	$content .= '
				<tr>
				
				<td align="center" width="20%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black;border-bottom: 1px solid black; border-right: 1px solid white;" ><font size="7">
				' . $TotalMcsValue . '</font></td>
				<td align="center" width="20%" style="border-top: 1px solid black;border-bottom: 1px solid black; border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;"><font size="7">
				' . number_format($TotalQtyValue, 3) . '</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;" >
				<font size="7">
				</font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;"><font size="7">
				' . number_format($TotalUsdValue, 2) . '</font></td>
				
				
				</tr>';

	//$AryPaymentData=getPaymentValues($iconn,$PoID);
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$DiscountPerc = $AryPaymentData['DiscountPerc'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$IgstPerc = $AryPaymentData['IgstPerc'];
	$IgstValue = $AryPaymentData['IgstValue'];

	$InvValueUSD = $AryPaymentData['InvValueUSD'];
	$InvValueInr = $AryPaymentData['InvValueINR'];
	$IgstPercentage = $AryPaymentData['IgstValueBasedonProducttype'];
	// var_dump($IgstPercentage);
	// exit;


	if ($showDiscount) {
		$content .= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				LESS TRADE DISCOUNT ' . $DiscountPerc . '%</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				' . number_format($DiscountAmount, 2) . '</B></font></td>
				</tr><tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				TOTAL AMOUNT:</B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;" ><font size="7"><B>
				' . number_format($InvValueUSD, 2) . '</B></font></td>
				</tr>';
	}


	if ($showIgst) {
		$content .= '<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
				INVOICE VALUE IN R.S@' . $ExchangeRate . '  </B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
				' . number_format(round($InvValueInr)) . '</B></font></td>
				</tr>';
		// $content .= '<tr>
		// 		<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
		// 		IGST@' . $IgstPerc . ' % </B></font></td>
		// 		<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
		// 		' . number_format($IgstValue, 2) . '</B></font></td>
		// 		</tr>';
		foreach ($IgstPercentage as $Item) {
			if ($Item['IgstValue'] > 0) {
				$content .= '<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				IGST@' . $Item['IgstPerc'] . ' % </B></font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
				' . number_format($Item['IgstValue']) . '</B></font></td>
				</tr>';
			}
		}
	}



	$content .= '</table>
			  </tr>';

	$BILL_WORD = numberTowords($InvValueUSD);

	$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
	//$InvValueINRWithOutDuties=$AryPaymentData['InvValueUSDWithOutDuties']*$ExchangeRate;
	$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
	$DutiesPrice = $AryPaymentData['DutiesPrice'];
	$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
	$FOBValueINR = round($AryPaymentData['FOBValueINR']);
	$AntiDumpingDuty = round($AryPaymentData['AntiDumpingDuty'], 2);
	$AntiDumpingDutyPerc = $AryPaymentData['AntiDumpingDutyPerc'];
	$HarbourMaintenanceFeePerc = $AryPaymentData['HarbourMaintenanceFeePerc'];
	$CountervailingDutyPerc = $AryPaymentData['CountervailingDutyPerc'];
	$MerchandiseProcessFeePerc = $AryPaymentData['MerchandiseProcessFeePerc'];
	$CountervailingDuty = round($AryPaymentData['CountervailingDuty'], 2);
	$ReciprocalTariffsFee = round($AryPaymentData['ReciprocalTariffsFee'], 2);
	$ReciprocalTariffsPerc = $AryPaymentData['ReciprocalTariffsPerc'];

	$ClearanceCharge = $AryPaymentData['ClearanceCharge'];
	$ClearanceChargePerc = $AryPaymentData['ClearanceChargePerc'];
	$MerchantDuty = round($AryPaymentData['MerchantDuty'], 2);
	$HarborDuty = round($AryPaymentData['HarborDuty'], 2);

	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];





	$content .= '<tr>
			<table border ="0" cellspacing="0" cellpadding="0">';
	$content .= '<tr>
			<td><font size="7"><br> SHIPPING TERMS   : ' . $ShippingTerms . '  ' . $FinalDestination . '</font></td>
			</tr>';
	$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FREIGHT</font></td>
			<td align="left" width="30%" ><font size="7"><b>:  <u>' . number_format($FrieghtCharge, 2) . '</u></b></font></td>
			</tr>';


	// echo $ShowFrightCharge; exit;

	if ($ShowInsurance) {
		// $content .= '<BR><b> INSURANCE: '.number_format($InsuranceValue,2).'</b>'; 

		$content .= '<tr>
			<td  width="1%"></td><td align="left" width="35%"><font size="7">CIF INSURANCE </font></td>
			<td align="left" width="60%" ><font size="7"> <b>: ' . number_format($InsuranceValue, 2) . '</b></font></td>
			</tr>';
	}
	if ($ShowFrightCharge) {

		if ($ClearanceChargePerc != 0) {
			$content .= '
					<tr>
					<td  width="1%"></td>
					<td align="left" width="35%"><font size="7">CLEARANCE CHARGES @ ' . $ClearanceChargePerc . '%)</font></td>
				<td align="left" width="30%" ><font size="7"><b>: $ ' . number_format($ClearanceCharge, 2) . '</b></font></td>
	
					</tr>';
		}
		$content .= '<tr>
			<td  width="1%"></td><td align="left" width="35%"><font size="7">(LESS : ANTI-DUMPING DUTY @ ' . $AntiDumpingDutyPerc . '%)</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: $ ' . number_format($AntiDumpingDuty, 2) . '</b></font></td>
			</tr>';

		//uma cvd calculation included //23-04-2024

		if ($CountervailingDutyPerc != 0) {
			$content .= '
				<tr>
				<td  width="1%"></td>
				<td align="left" width="35%"><font size="7">(LESS : COUNTER VAILING DUTY @ ' . $CountervailingDutyPerc . '%) </font></td>
				<td align="left" width="60%" ><font size="7"> <b>: $ ' . number_format($CountervailingDuty, 2) . '</b></font></td>
				</tr>';
		}
		//uma cvd calculation included //23-04-2024
		if (!$ISFStatic) {
			$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="35%"><font size="7">(LESS : MERCHANDISE PROCESS FEE @ ' . $MerchandiseProcessFeePerc . '%)</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: $ ' . number_format($MerchantDuty, 2) . '</b></font></td>
			</tr>';
		}
		if ($HarbourMaintenanceFeePerc != 0) {
			$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="35%"><font size="7">(LESS : HARBOR MAINTENANCE FEE @ ' . $HarbourMaintenanceFeePerc . '%)</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: $ ' . number_format($HarborDuty, 2) . '</b></font></td>
			</tr>';
		}

		if ($ReciprocalTariffsPerc != 0) {
			$content .= '
						<tr>
						<td  width="1%"></td>
						<td align="left" width="35%"><font size="7">(LESS : RECIPROCAL TARIFFS @ ' . $ReciprocalTariffsPerc . '%) </font></td>
						<td align="left" width="60%" ><font size="7"> <b>: $ ' . number_format($ReciprocalTariffsFee, 2) . '</b></font></td>
						</tr>';
		}
		$content .= '<tr>
			<td  width="1%"></td><td align="left" width="35%"><font size="7">INVOICE VALUE ' . $CurrencyShortCode . '</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: ' . number_format($TotalUsdValue, 2) . '</b></font></td>
			</tr>';

		// $content.='<tr><td  width="1%"></td>
		// <td align="left" width="35%"><font size="7">LESS OCEAN FRIEGHT PREPAID IN '.$CurrencyShortCode.'</font></td>
		// <td align="left" width="30%" ><font size="7"><b>:  <u>'.number_format($FrieghtCharge,2).'</u></b></font></td>
		// </tr>';
		// $content.='<tr><td  width="1%"></td>
		// <td align="left" width="35%"><font size="7">SUB TOTAL</font></td>
		// <td align="left" width="60%" ><font size="7"> <b>: '.number_format($InvValueUSDWithOutDuties,2).'</b></font></td>
		// </tr>';
		// $content.='<tr><td  width="1%"></td>
		// <td align="left" width="35%"><font size="7">LESS DUTIES, PORT TAXES & CLEARANCE '.$CurrencyShortCode.' </font></td>
		// <td align="left" width="60%" ><font size="7"> <b>: <u>'.number_format($DutiesPrice,2).'</u></b></font></td>
		// </tr>';
		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN ' . $CurrencyShortCode . ' </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($FOBValueUSD, 2) . '</b></font></td>
			<td align="left" width="60%" ><font size="7"> <b> FOB VALUE  :  ' . round($FOBValueINR) . '.00</b></font></td>
			</tr>';
	}
	//echo $InvValueINRWithOutDuties;
	else if ($ShowInvoicevalue) {

		$content .= '<tr>
			<td  width="1%"></td><td align="left" width="35%"><font size="7">INVOICE VALUE ' . $CurrencyShortCode . '</font></td>
			<td align="left" width="60%" ><font size="7"> <b>: ' . number_format($InvValueUSD, 2) . '</b></font></td>
			</tr>';

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN ' . $CurrencyShortCode . ' </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($InvValueUSDWithOutDuties, 2) . '</b></font></td>
			<td align="left" width="60%" ><font size="7"> 
			<b> FOB VALUE  :  ' . round($InvValueINRWithOutDuties) . '.00</b></font></td>
			</tr>';
	} else {
		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="35%"><font size="7">FOB VALUE IN ' . $CurrencyShortCode . ' </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($InvValueUSDWithOutDuties, 2) . '</b></font></td>
			<td align="left" width="60%" ><font size="7"> 
			<b> FOB VALUE  :  ' . round($InvValueINRWithOutDuties) . '.00</b></font></td>
			</tr>';
	}



	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];



	/**uma 20-12-2023* */

	// $GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
	// $GrossWeightLbs=$AryPriceData['GrossWeightLbs'];

	$TotalGrossWtKgs = $AryData['TotalGrossWtKgs'];

	if ($TotalGrossWtKgs != '') {
		if ($QtyUnit == 'KGS') {
			$GrossWeightKgs = $TotalGrossWtKgs;
			$GrossWeightLbs = $TotalGrossWtKgs * 2.204;
		} else {
			$GrossWeightKgs = $TotalGrossWtKgs * 0.454;
			$GrossWeightLbs = $TotalGrossWtKgs;
		}
	} else {
		$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
		$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];
	}

	/**uma 20-12-2023* */

	if ($QtyUnit == 'KGS') {
		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7"><b>NET WEIGHT: </b></font></td>
			<td align="left" width="15%" ><font size="7"> <b>' . number_format($NetWgtKgs, 3) . ' KGS</b></font></td>
			</tr>';

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7"><b>GROSS WEIGHT:</b> </font></td>
			<td align="left" width="15%" ><font size="7"> <b>' . number_format($GrossWeightKgs, 3) . ' KGS</b></font></td>
			</tr>';

		if (!empty($TotalGlazeValue)) {
			$content .= '<tr><td width="1%"></td>
					<td align="left" width="25%"><font size="7"><b>FROZEN WEIGHT:</b> </font></td>
					<td align="left" width="15%"><font size="7"><b>' . number_format($TotalGlazeValue, 3) . ' KGS</b></font></td>
				</tr>';
		}
	} else {

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7">NET WEIGHT </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($NetWgtLbs, 3) . ' LBS</b></font></td>
			<td align="left" width="60%" ><font size="7"> <b> ' . number_format($NetWgtKgs, 3) . ' KGS</b></font></td>
			</tr>';

		$content .= '<tr><td  width="1%"></td>
			<td align="left" width="25%"><font size="7">GROSS WEIGHT </font></td>
			<td align="left" width="15%" ><font size="7"> <b>: ' . number_format($GrossWeightLbs, 3) . ' LBS</b></font></td>
			<td align="left" width="30%" ><font size="7"> <b> ' . number_format($GrossWeightKgs, 3) . ' KGS</b></font></td>
			</tr>';
	}


	$content .= '<tr><td align="left" width="100%" ><font size="7"> Q CERTIFICATE NO : ' . $QCertificateNo . '</font></td></tr></table></tr>';


	$content .= '<tr>';

	//$content.='<td align="left" width="15%" ><font size="7"> Q CERTIFICATE NO : '.$QCertificateNo.'</font></td>';


	$ShipmentUnder = ' 1) WE HEREBY DECLARE THAT WE SHALL CLAIM THE BENEFITS UNDER CUSTOMS NOTIFICATION NO.132/2016 
CUSTOMS NOTIFICATION DATED 31-10-2016, AS WELL AS CHAPTER 3 OF FTP 2015-2020 AS APPLICABLE.<br> FISH AND 
CRUSTACEANS, MOLLUSCS AND OTHER AQUATIC INVERTEBRATES.
<br> 2) THIS SHIPMENT IS MADE UNDER DUTY DRAW BACK SCHEME AS WELL AS UNDER RoDTEP SCHEME.
<br> 3) ITC HS CODES: <b>' . $CInvCode . '</b> DRAW BACK SCHEME CODE: 19, .SL.NO. OF EXPORT PRODUCT: 03060101A, RATE OF ENTITLEMENT: 3% ON FOB OR Rs.23.6/KG CAP <br> ';
	if ($IGST == 'LUT') {
		$ShipmentUnder .= '4)SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF INTEGRATED TAX: LUT No. ' . $LutNumber . '';
	}

	$Decleration = ' " I/We, in regard to my/our claim under RoDTEP scheme made in this Shipping Bill or Bill of Export, hereby declare that:
				<br> 1). I/ We undertake to abide by the provisions, including conditions, restrictions, exclusions and time-limits as provided
				 under RoDTEP scheme, and relevant notifications,  regulations, etc., as amended  <br> from time to time.
				<br> 2). Any claim made in this shipping bill or bill of export is not with respect to any duties or taxes or levies which are 
				exempted or remitted or credited under any other  mechanism outside RoDTEP.
				<br> 3). I/We undertake to preserve and make available relevant documents relating to the exported goods for the 
				purposes of audit in the manner and for the time period  prescribed in the Customs Audit  <br> Regulations, 2018."';
	$content .= '</tr>
				
						
	        
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> AMOUNT CHARGABLE IN  ' . $CurrencyShortCode . ': 
				<b>' . $BILL_WORD . '</b></font><BR> 
				<font size="5"><br>' . $ShipmentUnder . '</font>
				<font size="6"><br><BR>' . $Decleration . '<BR>	<br>			
				<b> INFORMATION CODE: RODTEPY</b><br>
				
				</font></th>    
				
				 
				
                
</tr>
<tr>
<th  align="bottom"  width="70%"><font size="8"><B><U> DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>
           
		   <th  align="center"  width="30%"><font size="7"> Signature:<br><br>
				<B> ' . $AuthSign . ',</B><BR><BR><BR>
				 
				<!--<img src="' . $SignURL . '" 
				 width="100" height="30"style="width: 100" align="center" align="middle"/>-->
								
				
				<p STYLE="text-align:center" > Authorised Signature   </p></font></th>
</tr>
				
				
				</table>';

	return $content;
}












$SqlPaymentTerms = "SELECT * FROM $TblScrmPoData as SPD 
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'";





function getReportData($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $DocType;

	/*$CustomInvoiceSql="SELECT *  
																																																						FROM ScrmCustomInvoices as SCV 
																																																						left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
																																																						WHERE SCV.PiID='$PoID'"; */

	//26-09-2023 #teja
	//left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID

	$CustomInvoiceSql = "SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,SCV.ContainerNo,
        SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading,
		FD.Name as FinalDestination  ,SCV.ContainerNo as ContainerNo,
		FD.CountryCode as FDCountryCode,
		FDC.name as FinalDestinationCountry
		FROM ScrmCustomInvoices as SCV 
		
		left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID
	    left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
        left JOIN ScrmPortCities as FD on FD.PortId=SSD.FinalDestination
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SL on SL.id=SP.Country
		left JOIN ScrmCountriesList as FDC on FDC.id=FD.Country
		left JOIN ScrmISFDetails as ISF on ISF.PIID=SCV.PiID
		WHERE SCV.PiID='$PoID'";
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvData = $CustomInvoiceExec->fetch_assoc();
	$IGST = $CustomInvData['IGST'];
	$OverSeasCommission = $CustomInvData['OverSeasCommission'];
	$CInvDate = $CustomInvData['CInvDate'];
	//$CInvCode=$CustomInvData['CInvCode'];
	$NoOfDays = $CustomInvData['NoOfDays'];
	$TotalGrossWt = $CustomInvData['TotalGrossWt'];

	if ($NoOfDays == 0) {
		$NoOfDays = '';
	} else {

		$NoOfDays = $NoOfDays . ' DAYS FROM BL';
	}

	$SqlMcsTotal = "SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
	$StmtMcsTotal = $iconn->query($SqlMcsTotal);
	$RowMcs = $StmtMcsTotal->fetch_assoc();
	$TotalMcs = $RowMcs['Mcs'];

	$SqlHsCode = "SELECT DISTINCT HSCode FROM `ScrmPoGradeData` as SPGD 
  left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
  left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";
	$StmtHsCode = $iconn->query($SqlHsCode);
	while ($RowHsCode = $StmtHsCode->fetch_assoc()) {
		$ArrayHsCode[] = $RowHsCode['HSCode'];
		$CInvCode = implode(',', $ArrayHsCode);
	}


	$SqlScrmPoData = "SELECT *,SCDB.BuyerName as BuyerName,
 SCDB.BuyerAddress as BuyerAddress,SPD.Status as PoStatus,ALD.SignURL,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,

ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,
ABD.SwiftCode as SwiftCode,
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode   
	FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN AdminLoginDetails as ALD on SC.id=SPD.ApprovedBy
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
 left JOIN ScrmCurrencyMaster as SCRM on SCRM.CurrencyCode=SPD.UnitCurrency
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID

 WHERE PoID='$PoID'";


	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();
	// echo json_encode($PoDataRow, JSON_PRETTY_PRINT);
	// exit;
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ApprovedBy = $PoDataRow['ApprovedBy'];
	$CountryID = $PoDataRow['DestinationCountry'];


	$SqlLogin = "SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
	$StmtLogin = $iconn->query($SqlLogin);
	$RowLogin = $StmtLogin->fetch_assoc();
	$SignURL = $RowLogin['SignURL'];

	//$ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
	$ExporterData = getExporterDetails($ExpID, $CountryID, $DocType);
	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);

	/*$ExporterData=getExporterDetails($ExpID);
																																																						 $ProcessorData=getExporterDetails($ProcessorID);*/




	if ($CustomInvData['Discount'] != 0) {
		$AryData['showDiscount'] = true;
	} else {
		$AryData['showDiscount'] = false;
	}

	/*if($CustomInvData['Discount']=='DISCOUNT'){
																																																						 $AryData['showDiscount']=true;	
																																																						 }else{
																																																						 $AryData['showDiscount']=false;	
																																																						 }*/

	if ($CustomInvData['IGST'] == 'IGST') {
		$AryData['showIgst'] = true;
	} else {
		$AryData['showIgst'] = false;
	}


	if ($PoDataRow['ShippingTerms'] == 'DDP') {
		$AryData['ShowShippingTerms'] = false;	//uma
	} else {
		$AryData['ShowShippingTerms'] = false;
	}

	if ($PoDataRow['ShippingTerms'] == 'FOB') {
		$AryData['ShowInvoicevalue'] = true;	//uma
	} else {
		$AryData['ShowInvoicevalue'] = false;
	}


	//if($PoDataRow['DestinationCountry']==26){
	if ($PoDataRow['DestinationCountry'] != 26 && $PoDataRow['ShippingTerms'] != 'FOB') {  //uma
		$AryData['ShowDeliveryTerms'] = true;
	} else {
		$AryData['ShowDeliveryTerms'] = false;
	}


	if ($ExpID != 2 && $PoDataRow['DestinationCountry'] == 157) {
		$AryData['showExpUsFda'] = true;
	}
	/*else if($ExpID==5 && $PoDataRow['DestinationCountry']!=26){
																																																						 $AryData['showExpUsFda']=true;	
																																																						 }
																																																						 */ else {
		$AryData['showExpUsFda'] = false;
	}
	$AryData['showExpIECode'] = true;
	$AryData['showExpGSTNo'] = true;
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

	if ($PoDataRow['DestinationCountry'] != 26) {
		$AryData['showProFEI'] = true;
	} else {
		$AryData['showProFEI'] = false;
	}

	if ($PoDataRow['ShowBrand'] == 1) {
		$AryData['ShowBrand'] = true;
	} else {
		$AryData['ShowBrand'] = false;
	}


	// echo $PoDataRow['ShowFrieght']; exit;
	// if($PoDataRow['ShowFrightCharge']==1 && $PoDataRow['ShowFrieght']==1){
	if ($PoDataRow['ShowFrightCharge'] == 1) {
		$AryData['ShowFrightCharge'] = 1;
	} else {
		$AryData['ShowFrightCharge'] = 0;
	}
	//echo $PoDataRow['ShowFrieght']; exit;
	if ($PoDataRow['Insurance'] != 0) {
		$AryData['ShowInsurance'] = true;
	} else {
		$AryData['ShowInsurance'] = false;
	}

	if ($PoDataRow['DestinationCountry'] != 26) {
		$AryData['showProUsFda'] = true;
	} else {
		$AryData['showProUsFda'] = false;
	}



	if ($PoDataRow['PoNo'] == '') {
		$AryData['PoDate'] = '';
	} else {
		$AryData['PoDate'] = "& Dated :" . getFormatedDate($PoDataRow['PoDate']);
	}
	if ($IGST != 'DISCOUNT') {
		$AryData['ShowNoofDays'] = true;
	} else {
		$AryData['ShowNoofDays'] = false;
	}

	if ($PoDataRow['NotifyPartyID'] != 0) {
		$AryData['showNotifyParty'] = true;
	} else {
		$AryData['showNotifyParty'] = false;
	}



	/* Return Data*/
	$AryData['PoStatus'] = $PoDataRow['PoStatus'];
	$AryData['TotalGrossWtKgs'] = $TotalGrossWt;
	$AryData['ApprovedBy'] = $ApprovedBy;
	$AryData['PaymentTerms'] = $PaymentTerms;
	$AryData['DeliveryTerms'] = $DeliveryTerms;
	$AryData['TotalMcs'] = $TotalMcs;
	$AryData['ExporterID'] = $ExpID;

	$AryData['ExporterName'] = $ExporterData['ExpName'];
	;
	$AryData['ExporterAddress'] = $ExporterData['ExpAddress'];
	$AryData['ExporterUsfdaNo'] = $ExporterData['ExpUsfdaNo'];
	$AryData['ExporterIeCode'] = $ExporterData['ExpIeCode'];
	$AryData['ExporterGstNo'] = $ExporterData['ExpGstNo'];
	$AryData['DestinationCountryId'] = $ExporterData['DestinationCountry'];
	$AryData['ExporterRecAdd'] = $ExporterData['ImporterRecordAddress'];
	$AryData['AuthSign'] = $ExporterData['AuthSign'];



	$AryData['ConsigneeID'] = $PoDataRow['ConsigneeID'];
	$AryData['BuyerID'] = $PoDataRow['BuyerID'];
	$AryData['ConsigneeName'] = $PoDataRow['ConsigneeName'];
	$AryData['ConsigneeAddress'] = $PoDataRow['ConsigneeAddress'];
	$AryData['BuyerName'] = $PoDataRow['BuyerName'];
	$AryData['BuyerAddress'] = $PoDataRow['BuyerAddress'];



	//$AryData['PortOfDischarge']=strtoupper($CustomInvData['PortofDischarge']).' '.strtoupper($CustomInvData['CountryCode']).', '.$CustomInvData['PortofDischargeCountry'];
	$AryData['PortOfDischarge'] = strtoupper($CustomInvData['PortofDischarge']) . ', ' . $CustomInvData['PortofDischargeCountry'];

	$AryData['PlaceOfReceiptBy'] = $CustomInvData['PlaceOfRecipt'];
	$AryData['PortOfLoading'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	//$AryData['FinalDestination']=strtoupper($CustomInvData['FinalDestination']) .' '.strtoupper($CustomInvData['FDCountryCode']).', '.strtoupper($CustomInvData['FinalDestinationCountry']);;
	$AryData['FinalDestination'] = strtoupper($CustomInvData['FinalDestination']) . ', ' . strtoupper($CustomInvData['FinalDestinationCountry']);
	;


	//$AryData['InvNo']=getInvoiceNumber($iconn,$ExpID,$PoID) ;
	$AryData['InvNo'] = $CustomInvData['CustomsInvNo'];
	$AryData['InvDate'] = getFormatedDate($CInvDate);
	$AryData['PoNo'] = $PoDataRow['PoNo'];



	$AryData['ProcessorName'] = $ProcessorData['ExpName'];
	//$AryData['ProcessorAddress']=$ProcessorData['PackerAddress'];
	$AryData['ProcessorAddress'] = $ProcessorData['ExpAddress'];
	$AryData['ProcessorUsfdaNo'] = $ProcessorData['ExpUsfdaNo'];
	$AryData['ProcessorFEI'] = $ProcessorData['ExpFeiNo'];
	$AryData['ProcessorApprovalNo'] = $ProcessorData['ExpApprovalNo'];
	$AryData['ProcessorIeCustomsNo'] = $ProcessorData['ExpIeCustomsNo'];


	$AryData['ShippingTerms'] = $PoDataRow['ShippingTerms'];
	$AryData['PaymentTerms'] = $PoDataRow['PaymentName'];
	$AryData['DestinationCountry'] = $PoDataRow['name'];
	$AryData['DeliveryTerms'] = $PoDataRow['DeliveryTerms'];
	$AryData['QtyUnit'] = $PoDataRow['QtyUnit'];
	$AryData['UnitCurrency'] = $PoDataRow['UnitCurrency'];
	$AryData['CurrencyShortCode'] = $PoDataRow['CurrencyShortCode'];
	$AryData['ShipByDate'] = $PoDataRow['ShipByDate'];
	$AryData['SignURL'] = $SignURL;
	$AryData['PreparedBy'] = $PoDataRow['PreparedBy'];
	$AryData['BankID'] = $PoDataRow['BankID'];
	$AryData['IGST'] = $IGST;
	$AryData['CInvCode'] = $CInvCode;
	$AryData['NoOfDays'] = $NoOfDays;
	global $AryShowConditions, $DocType;



	$AryData['ContainerNo'] = $CustomInvData['ContainerNo'];
	//$AryData['SealNo']=$CustomInvData['SealNo'];
	$AryData['SealNo'] = $CustomInvData['LinerSealNo'];
	$AryData['QCertificateNo'] = $CustomInvData['QCertificateNo'];
	$AryData['ShowCWiseBrand'] = $AryShowConditions['Brand'];

	$AryData['NotifyPartyName'] = $PoDataRow['NotifyPartyName'];
	$AryData['NPartyAddress'] = $PoDataRow['NPartyAddress'];
	$AryData['NPartyContactNumber'] = $PoDataRow['NPartyContactNumber'];

	return $AryData;
}


function getBreakUpData($iconn, $GradeID, $PiID)
{

	if ($PiID == '') {
		$Qry = "WHERE ID='$GradeID'";
	} else {
		$Qry = "WHERE InvId='$PiID'";
	}

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

	$SqlTotal = "SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			GlazeStatus,
			SPV.GlazePerc, PS.Uom, SPD.DestinationCountry FROM 
			`ScrmPoGradeData` as PGD 
			left join ScrmPIVarietyList as SPV on SPV.VarietyID=PGD.VarietyID 
			left join AdminPackingStyles as PS 	on PGD.PackingStyleID=PS.PCKID 
			left join ScrmPoData as SPD	on SPD.PoID = PGD.InvID $Qry";

	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];

	// Added a country check to ensure the correct quantity unit is used for USA and NON-USA Invoices
	if ($RowTotal['DestinationCountry'] != 157) {
		$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'";
		$StmtQtyUnit = $iconn->query($SqlQtyUnit);
		$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
		$QtyUnit = $RowQtyUnit['QtyUnit'];
	} else {
		$QtyUnit = $RowTotal['Uom'];
	}

	if ($QtyUnit == 'KGS') {
		$Qty = $RowTotal['NetWgtKgs'];
		$UsdValue = $RowTotal['PriceKgs'];
		$UnitPrice = number_format($RowTotal['Price'], 2);
	} else if ($QtyUnit == 'OZ') {
		$Qty = $RowTotal['NetWgtLbs'] * 0.454; //35000 * 0.454 = 15890
		$UsdValue = $RowTotal['PriceLbs']; //136500
		$UnitPrice = round(($UsdValue / $Qty), 6);
	} else if ($QtyUnit == 'GRAMS') {
		$Qty = $RowTotal['NetWgtKgs']; //15680
		$UnitPrice = round(($RowTotal['Price'] / 0.454), 6); //8.590308
		$UsdValue = $Qty * $UnitPrice;
	} else {
		$Qty = $RowTotal['NetWgtLbs'] * 0.454; //35000 * 0.454 = 15890
		$UsdValue = $RowTotal['PriceLbs']; //136500
		$UnitPrice = round(($UsdValue / $Qty), 6);
	}


	// $UsdValue = $RowTotal['PriceKgs'];
	// $UnitPrice = number_format($RowTotal['Price'], 2);


	if ($QtyUnit == 'KGS') {
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	} else {
		$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $RowTotal['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	}

	$TotalQtyAfterGlaze = 0;
	$GlazeWeight = 0;
	if ($RowTotal['GlazeStatus'] == 'Yes' && $RowTotal['GlazePerc'] > 0) {
		$GlazeWeight = ($Qty * $RowTotal['GlazePerc']) / 100;
		$TotalQtyAfterGlaze = $Qty - $GlazeWeight;
	}

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['UnitPrice'] = $UnitPrice;
	$AryPriceData['GlazeWeight'] = $GlazeWeight;
	$AryPriceData['TotalQtyAfterGlaze'] = $TotalQtyAfterGlaze;

	/*$AryPriceData['NetWgtKgs']=$RowTotal['NetWgtKgs'];
																																																							$AryPriceData['NetWgtLbs']=$RowTotal['NetWgtLbs'];
																																																							$AryPriceData['GrossWeightKgs']=$RowTotal['GrossWeightKgs'];
																																																							$AryPriceData['GrossWeightLbs']=$RowTotal['GrossWeightLbs'];*/


	$AryPriceData['NetWgtKgs'] = $NetWgtKgs;
	$AryPriceData['NetWgtLbs'] = $NetWgtLbs;
	$AryPriceData['GrossWeightKgs'] = $GrossWeightKgs;
	$AryPriceData['GrossWeightLbs'] = $GrossWeightLbs;

	return $AryPriceData;
}


function getBankDetails($iconn, $BankID)
{

	$SqlBank = "SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$AryBankData = $StmtBank->fetch_assoc();

	return $AryBankData;
}
