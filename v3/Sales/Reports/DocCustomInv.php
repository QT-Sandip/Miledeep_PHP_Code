<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");

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
	//line-height: 0.01;
}
</style>
';


$PoID = $_GET['id'];
$Type = $_GET['Type'];
$PType = $_GET['PType'];
if ($PType == '') {
	$PType = 'LBS';
}

$AdminConfigDetails = json_decode(json_encode($_SESSION['ClientConfigData']), true);

// Temporary line added only for the DEV client. This will be removed soon.
$AdminConfigDetails['ComInvPrintReciprocal'] = true;

$ArrayInvoiceConfigData = json_decode(json_encode($_SESSION['ClientInvoiceConfigData']), true);
$HasCLFCharges = $ArrayInvoiceConfigData['CLFCharges'];
$HasTariff = $ArrayInvoiceConfigData['Tariff'];

$SqlCustom = "SELECT DestinationCountry,ConsigneeID,IGST,SPD.ExporterID as ExporterID,CustomsInvNo,CInvDate FROM `ScrmCustomInvoices`  as SCV
left join ScrmPoData as SPD on  SPD.PoID=SCV.PiID
WHERE PiID='$PoID'";

$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom->fetch_assoc();
$IGST = $RowCustom['IGST'];
$ExpID = $RowCustom['ExporterID'];
$DocType = 'CUI';
$CountryID = $RowCustom['DestinationCountry'];
$BuyerID = $RowCustom['ConsigneeID'];
$CustomsInvNo = $RowCustom['CustomsInvNo'];
$CustInvDate = date("d-m-Y", strtotime($RowCustom['CInvDate']));

$AryLables = getInvoiceLables($DocType, $CountryID, $BuyerID);
$AryShowConditions = getInvoiceShowConditions($DocType, $CountryID, $BuyerID);

$RevisedPoSql = "SELECT * FROM `ReciprocalTariffs` WHERE PoID=$PoID";
$RevisedPoStmt = $iconn->query($RevisedPoSql);
$RevisedPoRow = $RevisedPoStmt->fetch_assoc();
$RevisedPo = $RevisedPoRow['RevisedPo'];


$SqlRevPoTaxRates = "SELECT * FROM `ScrmPoTaxRates` where PoID='$PoID' AND InvoiceType = '$DocType' ORDER BY ID DESC";
$StmtRevPoTaxRates = $iconn->query($SqlRevPoTaxRates);

if ($StmtRevPoTaxRates && $StmtRevPoTaxRates->num_rows > 0) {
	$RevPoTaxRatesRecord = $StmtRevPoTaxRates->fetch_assoc();
	$RevisedPo = $RevPoTaxRatesRecord['RevisedPo'];
}
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

		global $Header, $RevisedPo;
		$PageHeader = 'INVOICE / PACKING LIST' . $Header;
		if ($RevisedPo) {
			$PageHeader = "INVOICE / PACKING LIST" . $Header . " (REVISED)";
		}
		$image_file = 'images/ds_logo.jpg';
		$this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 0, $PageHeader, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', ' ', 8);
		$this->Cell(130, 0, '' . $Header2, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

		$this->SetTextColor(255, 0, 0);

		$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
	}

	public function Footer()
	{
		global $CustomsInvNo, $CustInvDate;
		$this->SetFont('helvetica', 'I', 8);
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . '(' . $CustomsInvNo . ' / Dt.' . $CustInvDate . ')', 0, false, 'C', 0, '', 0, false, 'T', 'M');

		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('INVOICE / PACKING LIST' . $Header);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(5);
$pdf->SetMargins(10, 15, 10, 5);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(true, 5);
$pdf->AddPage();

$AryData = getReportData($iconn, $PoID);
$HeaderTable = getHeaderTable($iconn, $PoID);
$BreakUpTable = getBreakTable($iconn, $PoID);
$FooterTable = getFooterTable($iconn, $PoID);

$pdf->writeHTML($HeaderTable . $BreakUpTable . $FooterTable);

/*$TableData=getCustomInvData($iconn,$PoID);
   $pdf->writeHTML($TableData);*/
$pdf->SetFont('helvetica', '', 6);


/*$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];*/

//$file_name=$InvShortCode.'-CUS-'.$CInvNo.'-'.$FinancialYear.'.pdf';
$Fname = '-CUS';
$file_name = str_replace('/', '_', $CustomsInvNo);
$file_name = $file_name . $Fname . '.pdf';

ob_end_clean();

if ($Type == '') {
	$Type = 'I';
} else {
	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '_', $file_name);
	;
	$file_name = $Fname . $file_name;
}

$pdf->Output($file_name, $Type);


function getHeaderTable($iconn, $ID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	global $AryData, $style, $PType, $AryLables;

	$content = '';


	$size = 45;
	$size1 = 55;






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


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];
	$ReferenceNo = $AryData['ReferenceNo'];

	$ShowBank = $AryData['ShowBank'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$showBuyer = $AryData['showBuyer'];
	$showImporter = $AryData['showImporter'];
	$showNotifyParty = $AryData['showNotifyParty'];
	$ShowCWiseDeliveryTerms = $AryData['ShowCWiseDeliveryTerms'];
	$ShippingTerms = $AryData['ShippingTerms'];
	$ShowCWiseReferenceNo = $AryData['ShowCWiseReferenceNo'];
	$ShowCWiseDeliveryTermCode = $AryData['ShowCWiseDeliveryTermCode'];

	$ProcessorIeCustomsNo = $AryData['ProcessorIeCustomsNo'];
	//$ExporterRecAdd=$AryData['ExporterRecAdd'];
	$NotifyPartyName = $AryData['NotifyPartyName'];
	$NPartyAddress = $AryData['NPartyAddress'];
	$NPartyContactNumber = $AryData['NPartyContactNumber'];
	$ImporterName = $AryData['ImporterName'];
	$ImporterAddress = $AryData['ImporterAddress'];
	$ShowCWiseBuyer = $AryData['ShowCWiseBuyer'];
	$DeliveryTermCode = $AryData['DeliveryTermCode'];



	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$PortofLoading = $AryData['PortOfLoading'];
	$DestinationCountry = $AryData['DestinationCountry'];
	$DestinationCountryName = $AryData['DestinationCountryName'];
	//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$IRNNumber = $AryData['IRNNumber'];
	$QtyUnit = $AryData['QtyUnit'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$TotalMcs = $AryData['TotalMcs'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$Remarks = $AryData['Remarks'];

	$showRemarks = $AryData['showRemarks'];
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$showProFEI = $AryData['showProFEI'];
	$ContainerNo = $AryData['ContainerNo'];





	$content .= $style;
	$content .= '
	 
	 <table border="1" cellspacing="0" cellpadding="0">  ';
	if ($PType == 'KGS') {

		$content .= '<tr><th width="100%"> <br><br><font size="9"><b> IRN NO: ' . $IRNNumber . '<br></b></font></th></tr>';
	}
	$content .= '	 <tr>
				 
				
				
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';

	$content .= '
				
				<tr>
				<th  align="LEFT" ><font size="7"><b>Exporter :</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '';

	/*	if($showExpUsFda){
																				   $content .= '	USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>';
																				   }

																				   if($showExpIECode){
																				   $content .= ' IE CODE  : '.$ExporterIeCode;

																				   }*/

	if ($showExpGSTNo) {
		$content .= '<BR><U> GST NO  : ' . $ExporterGstNo . '</U>';
	}


	$content .= '</font></th></tr>';






	$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '</font></th>
				</tr>';
	if ($showNotifyParty) {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b> <u>Notify Party</u></b>
				<br>' . $NotifyPartyName . ',<br>' . nl2br($NPartyAddress) . '
				
				
				</font></th>
				</tr>';
	}

	/*	$content .= '<tr>  

																				   <th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
																				   <br  align="center">REFER CONTAINER</font></th>
																				   <th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
																				   <br align="center">ANY PORT, INDIA</font></th>
																				   below lines
																				   <th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
																				   <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> '.$TotalMcs.'</b> MCS</font></th>
																				   <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br><b align="center">1 X 40 FCL</b></font></th>
																				   <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br><b align="center">'.$SealNo.'</b></font></th>

																				   </tr>';*/
	$content .= '<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel/Flight No.  <BR>  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">' . $PortofLoading . '</font></th>
				
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
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.
				 ' . $InvNo . ',Dated ' . $InvDate . ' </b></font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Purchase Order No. & Date. 
				<br> ' . $PoNo . $PoDate . '</b> </font></th>
				</tr>';







	/*$content .= '		<tr > 
																				  <th  align="LEFT"  ><font size="7" ><b> Other Reference(s) :
																				  <b style="color:white">_________________________</b>  </b>

																				  </font></th></tr >';*/
	$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Reference(s) : </b>';
	if ($ShowCWiseReferenceNo) {
		$content .= '<b>' . $ReferenceNo . '</b>';
	}
	$content .= '	</font></th></tr >';
	if ($showImporter) {

		$content .= '	<tr> 
           		
		
                <th  align="left" width="100%"><font size="7">
				<b><u> Importer of Record:</u></b> <br>  ' . $ImporterName . '
				<br> ' . nl2br($ImporterAddress) . ' </font></th>
				</tr>';
	}
	//$showBuyer=true;
	if ($ShowCWiseBuyer) {
		if ($BuyerID != 0) {
			$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> <b><u>Buyer 
				(if other than consignee) :</b></u><br>' . $BuyerName . ',<br>' . nl2br($BuyerAddress) . '<br></font></th>
				</tr>';
		}
	}

	$content .= '<tr >
				<th  align="LEFT"  ><font size="7" >
				 <u><b>' . $AryLables['Manufacturer'] . '</u> :</b><br> ' . $ProcessorName . '<br> ' . nl2br($ProcessorAddress) . '<br>
				 ' . $AryLables['ApprovalNo'] . '' . $ProcessorApprovalNo . '';




	/*if($ShowCWiseUsfda){			
																				  $content .= '<br>BIO TERRORISM / USFDA REGISTRATION NO  : '.$ProcessorUsfdaNo;
																				  }*/

	/*if($showProFEI){			
																				  $content .= '<BR> FEI # :  '.$ProcessorFEI;
																				  }
																		  */
	$content .= '</font></th></tr>';



	$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
				
				<th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  ' . $DestinationCountry . '</b></font></th>
				
				
				
               
				</tr>
				<tr>';
	if ($ShowDeliveryTerms) {
		$content .= '<th border="1"  align="left" width="50%"><font size="7"> Terms of Delivery ';
		if ($ShowCWiseDeliveryTerms) {

			$content .= '<br><b align="center">  ' . $DeliveryTerms . '</b>';
		}
		if ($ShowCWiseDeliveryTermCode) {
			$content .= '	<b><br align="center">' . $DeliveryTermCode . '</b>';
		}
		$content .= '</font></th>';
	}

	$content .= '<th  align="LEFT"  width="50%" ><font size="7"><b> <u>Terms of Payment:</u>
				</b><br><BR> ' . $PaymentTerms . ' <br>
				 
				 <!--SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . ' -->
				';



	if ($ConsigneeID == '63') {
		$FinalDestination .= ', ' . $DestinationCountryName;
	}

	$content .= '</font> </th>
				</tr>
				<tr>
				<th  border="0"  align="left" width="100%"><font size="7">Container No         <b align="center"> ' . $ContainerNo . '</b></font></th>
				
				</tr>
				<tr>
				<th  align="right" ><font size="7">
                  <br>  <b>' . $ShippingTerms . ' ' . $FinalDestination . '</b></font></th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> ';




	$content .= '</table>';

	return $content;
}

function getBreakTable($iconn, $ID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	global $AryData, $style, $PType, $AdminConfigDetails, $DocType;
	$content = $style;


	$size = 45;
	$size1 = 55;






	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];
	$CodeType = $AryData['CodeType'];

	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$TotalGrossWtKgs = $AryData['TotalGrossWtKgs'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];

	$ShowBank = $AryData['ShowBank'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$showBuyer = $AryData['showBuyer'];
	$showImporter = $AryData['showImporter'];
	$showNotifyParty = $AryData['showNotifyParty'];
	$ShippingTerms = $AryData['ShippingTerms'];
	$ProcessorIeCustomsNo = $AryData['ProcessorIeCustomsNo'];
	//$ExporterRecAdd=$AryData['ExporterRecAdd'];
	$NotifyPartyName = $AryData['NotifyPartyName'];
	$NPartyAddress = $AryData['NPartyAddress'];
	$NPartyContactNumber = $AryData['NPartyContactNumber'];
	$ImporterName = $AryData['ImporterName'];
	$ImporterAddress = $AryData['ImporterAddress'];

	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$PortofLoading = $AryData['PortofLoading'];
	$DestinationCountry = $AryData['DestinationCountry'];
	//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$QtyUnit = $AryData['QtyUnit'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$TotalMcs = $AryData['TotalMcs'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$Remarks = $AryData['Remarks'];
	$showRemarks = $AryData['showRemarks'];
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];
	$ShowCWiseBrand = $AryData['ShowCWiseBrand'];

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$showProFEI = $AryData['showProFEI'];
	$showIgst = $AryData['showIgst'];
	$showDiscount = $AryData['showDiscount'];
	$TextSize = $AryData['TextSize'];





	$content = '
				<table border="1" cellspacing="0" cellpadding="2">
				<tr>
				<th  align="LEFT" width="58.5%" ><font size="7">  Description of Goods <b align="center">' . $SealNo . '</b></font></th>
                <th  align="center" width="13.5%" >
				<font size="7">Quantity in ' . $PType . ' </font></th>
                <th  align="center" width="13.5%" ><font size="7">Rate per <br>' . $PType . '<b>' . strtoupper($w) . '</b></font></th>
                <th  align="center" width="14.5%" ><font size="7">Amount in ' . $UnitCurrency . ' ' . '<b> ' . strtoupper($w) . '</b></font></th>
                </tr>';


	$CurrentCount = 0;
	$SqlVarietyMcs = "
			SELECT sum(McS) As VarietyMcs, BuyerItemCode,Description,Variety,Treatment,Grade,PackingStyle,Brand,SBM.PrintStatus,
			PackingStyleID,BAP FROM `ScrmPoGradeData` 
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			left join `ScrmBrandsMaster` SBM on  SBM.BrandName=Brand
			 WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand 
			 Order by ScrmPoGradeData.ID asc";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {


		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];
		$BAP = $VarietyMcsRow['BAP'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		$BuyerItemCode = $VarietyMcsRow['BuyerItemCode'];
		$BreakUpDescription = $VarietyMcsRow['Description'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];
		$Treatment = $VarietyMcsRow['Treatment'];
		if ($BAP == 'NA') {
			$BAP = '';
		} else {
			$BAP = $BAP;
		}
		if ($Treatment == '') {
			$Treatment = '';
		} else {
			$Treatment = '(' . $Treatment . ')';
			$Treatment = '';
		}




		$FontSize = "7px";
		/*  $content.='<tr>';
																																						if($BuyerItemCode!=''){
																																					$content.='
																																					<th align="center" width="10%" style="border-right:1px solid white;"  ><font size="'.$FontSize.'"> <u>Item Code</u></font></th>
																																					<th align="center" width="10%" style="border-right:1px solid white;"  ><font size="'.$FontSize.'"> <u>SIZE</u></font></th>';
																																					}else{
																																					$content.='<th align="center" width="20%" style="border-right:1px solid white;" ><font size="'.$FontSize.'"> <u>SIZE</u></font></th>';
																																					}
																																					$content.='<th align="center" width="18.5%" style="border-right:1px solid white;" ><font size="'.$FontSize.'"><u>NO. OF CTNS</u></font></th>
																																					<th align="center" width="20%" style="border-right:1px solid black;"><font size="'.$FontSize.'"><u>Brand</u></font></th>
																																					<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="'.$FontSize.'"></font></td>
																																					<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="'.$FontSize.'"></font></td>
																																					</tr>';*/

		$description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
		$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
		$Whitspace = '<b style="color:white"> yuhju</b>';
		$FontSize = "7";
		//$TOTAL_MCS=0;
		$content .= '
				
			
			
			';



		//$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$BreakUpDescription.'<br>';

		if ($BreakUpDescription != '') {
			$Description = $BreakUpDescription . '<br>';
		} else {
			$Description = $VarietyMcsRow['Variety'] . '' . $Treatment . '<br>';
			//$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$BreakUpDescription.'<br>';
		}

		if ($DestinationCountryId != 26) {
			$Description .= '<u>PACKING : ' . $VarietyMcsRow['PackingStyle'] . '</u>

			 <B><br>' . $BAP . '</B>';
		} else {
			$Description .= '	PACKING : ' . $VarietyMcsRow['PackingStyle'] . '' . $Whitspace . ' ';
		}



		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
			. $Description . '</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';
		$CurrentCount++;
		if ($CurrentCount < 2) {

			$content .= '<tr>';
			if ($BuyerItemCode != '') {
				$content .= '
			<th align="center" width="10%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '"> <u>
			' . $CodeType . '</u></font></th>
			<th align="center" width="10%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '"> <u>SIZE</u></font></th>';
			} else {
				$content .= '<th align="center" width="20%" style="border-right:1px solid white;" ><font size="' . $FontSize . '"> <u>SIZE</u></font></th>';
			}
			$content .= '<th align="center" width="18.5%" style="border-right:1px solid white;" ><font size="' . $FontSize . '"><u>NO. OF CTNS</u></font></th>
			';
			if ($ShowCWiseBrand && $BrandPrintStatus) {
				$content .= '<th align="center" width="20%" style="border-right:1px solid black;"><font size="' . $FontSize . '"><u>Brand</u></font></th>';
			} else {
				$content .= '<th align="center" width="20%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></th>';
			}
			$content .= '
			<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';
		}



		$SqlVariety = "SELECT * FROM `ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";

		$StmtVariety = $iconn->query($SqlVariety);
		$ArySubBreakUp = array();

		while ($RowVariety = $StmtVariety->fetch_assoc()) {

			$ArySubBreakUp[] = $RowVariety['ID'];
			//$BuyerItemCode=$RowVariety['BuyerItemCode'];
			//echo $BuyerItemCode;$RowVariety['BuyerItemCode']; exit;
			/*$AryPriceBreakUp=getBreakUpData($iconn,$RowVariety['ID']);
																																																							  $Qty=$AryPriceBreakUp['Qty'];
																																																							  $UsdValue=$AryPriceBreakUp['UsdValue']; 
																																																							  $TotalMcs=$AryPriceBreakUp['TotalMcs'];
																																																							  $Price=$AryPriceBreakUp['Price'];*/

			$AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['ID']);
			$Qty = $AryPriceBreakUp['Qty'];
			$UsdValue = $AryPriceBreakUp['UsdValue'];
			$TotalMcs = $AryPriceBreakUp['TotalMcs'];
			$Price = $AryPriceBreakUp['Price'];
			$UnitPrice = $AryPriceBreakUp['UnitPrice'];
			$AryPaymentData = getPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails);
			// $FOBValueUSD * $ExchangeRate;

			$InvValueUSD = $AryPaymentData['InvValueUSD'];
			$InvValueInr = $AryPaymentData['InvValueINR'];
			$ExchangeRate = $AryPaymentData['ExchangeRate'];
			$DiscountPerc = $AryPaymentData['DiscountPerc'];
			$DiscountAmount = $AryPaymentData['DiscountAmount'];
			$IgstPerc = $AryPaymentData['IgstPerc'];
			$IgstValue = $AryPaymentData['IgstValue'];

			$FOBValueINR = $AryPaymentData['FOBValueINR'];
			$IgstValueFOB = ($FOBValueINR * $IgstPerc) / 100;

			/*if($PType=='KGS' ){
																																																								 $Price=sprintf("%0.7f",$UnitPrice);
																																																							 }else{
																																																								 //$Price=sprintf("%0.2f",$Price);
																																																								 $Price;
																																																							 }*/


			if ($QtyUnit == 'LBS' && $PType == 'KGS') {
				$Price = sprintf("%0.7f", $UnitPrice);
			} else {

				$Price;
			}




			$content .= '<tr>';

			if ($BuyerItemCode != '') {
				$content .= '
			<td align="center" width="10%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '">
				' . $RowVariety['BuyerItemCode'] . '
				</font></td><td align="center" width="10%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '">
				 ' . $RowVariety['Grade'] . ' 
				</font></td>';
			} else {
				$content .= '<td align="center" width="20%"  style="border-right:1px solid white;" ><font size="' . $FontSize . '">
				' . $RowVariety['Grade'] . ' 
				</font></td>';
			}


			$content .= '
				<td align="center" width="18.5%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '">
				' . $TotalMcs . '  </font></td>
				';
			if ($ShowCWiseBrand && $BrandPrintStatus) {
				$content .= '<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
				' . $RowVariety['Brand'] . '  </font></td>';
			} else {
				$content .= '<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
				</font></td>';
			}
			$content .= '
				<td align="center" width="13.5%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
				' . number_format($Qty, 3) . ' </font></td>
				<td align="center" width="13.5%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
				' . $Price . '</font></td>
				
				<td align="center" width="14.5%" style="border-right:1px solid black;" ><font size="' . $FontSize . '">
				' . number_format($UsdValue, 2) . '</font></td>
				
				</tr>';
		}

		$SubVarityBrkUp = implode(',', $ArySubBreakUp);

		$AryPriceData = getBreakUpData($iconn, $SubVarityBrkUp);
		$Qty = $AryPriceData['Qty'];
		$UsdValue = $AryPriceData['UsdValue'];
		$TotalMcs = $AryPriceData['TotalMcs'];
		$Price = $AryPriceData['Price'];


		$content .= '
				<tr>
				<td align="center" width="20%"  style="border-right:1px solid white;" ><font size="' . $FontSize . '">
				<b>Total</b	>
				</font></td>
				<td align="center" width="18.5%" style="
				border-top:1px solid black;
				border-bottom:1px solid black;
				" >
														
														<font size="' . $FontSize . '">
				' . $TotalMcs . '</font></td>
				
				<td align="center" width="20%" style="border-right:1px solid black;">
														
														<font size="' . $FontSize . '"></font></td>
				<td align="center" width="13.5%" ><font size="' . $FontSize . '">
				' . number_format($Qty, 3) . '</font></td>
				<td align="center" width="13.5%" style="border-right:1px solid black;">
														<font size="' . $FontSize . '"></font></td>
				<td align="center" width="14.5%" style="border-top:1px solid black;
				border-bottom:1px solid black;">
														<font size="' . $FontSize . '">
				' . number_format($UsdValue, 2) . '</font></td>
				
				</tr>';
	}

	$AryPriceData = getBreakUpData($iconn, $RowVariety['ID'], $ID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];


	$content .= ' 
				<tr>
				
				<td align="center" width="20%" style="border-top: 1px solid black;" ><font size="7"><B>
				GRAND TOTAL </B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; " ><font size="7">
				' . $TotalMcs . '</font></td>
				<td align="center" width="20%" style="border-top: 1px solid black; border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				' . number_format($Qty, 3) . '</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="14.5%" style="
				border-top: 1px solid black;
				border-bottom: 1px solid black;
				border-right: 1px solid black;"><font size="7">
				<b>' . number_format($UsdValue, 2) . '</b></font></td>
				
				
				</tr>';


	//$showDiscount=true;
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

	//Static DFL # Guru Prasad Dont want Display
	//$showIgst=false;

	// Added IGST block in Footer Table - NITHIN #Dec 3rd 2025
	// if ($showIgst) {
	// 	//temporarily changed DEVI Client condition
	// 	if ($_SESSION["ClientID"] && $_SESSION["ClientID"] == 'DFLV3333') {
	// 		$content .= '	<tr>
	// 		<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
	// 		FOB VALUE IN R.S@' . $ExchangeRate . '  </B></font></td>
	// 		<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
	// 		' . number_format($FOBValueINR, 2) . '</B></font></td>
	// 		</tr>
	// 	<tr>
	// 	<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
	// 	IGST@' . $IgstPerc . ' % </B></font></td>
	// 	<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
	// 	' . number_format($IgstValueFOB, 2) . '</B></font></td>
	// 	</tr>';
	// 	} else {
	// 		$content .= '	<tr>
	// 			<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7"><B>
	// 			INVOICE VALUE IN R.S@' . $ExchangeRate . '  </B></font></td>
	// 			<td align="center" width="14.5%" style="border-top: 1px solid black;" ><font size="7"><B>
	// 			' . number_format($InvValueInr, 2) . '</B></font></td>
	// 			</tr>
	// 			<tr>
	// 			<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
	// 			IGST@' . $IgstPerc . ' % </B></font></td>
	// 			<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;" ><font size="7"><B>
	// 			' . number_format($IgstValue, 2) . '</B></font></td>
	// 			</tr>';
	// 	}
	// }

	$content .= '</table>';


	return $content;
}


function getFooterTable($iconn, $ID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $AryData, $style, $AdminConfigDetails, $HasCLFCharges, $HasTariff, $DocType;
	$content = $style;


	$size = 45;
	$size1 = 55;




	$AryPriceData = getBreakUpData($iconn, $RowVariety['ID'], $ID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];
	$WtPerCtnTxt = $AryPriceData['WtPerCtnTxt'];

	//print_r($AryPriceData); exit;


	$ProductTypes = $AryData['ProductTypes'];
	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];
	$TotalGrossWtKgs = $AryData['TotalGrossWtKgs'];

	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];
	$ShowBank = $AryData['ShowBank'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$showBuyer = $AryData['showBuyer'];
	$showImporter = $AryData['showImporter'];
	$showNotifyParty = $AryData['showNotifyParty'];
	$ShippingTerms = $AryData['ShippingTerms'];
	$ProcessorIeCustomsNo = $AryData['ProcessorIeCustomsNo'];
	//$ExporterRecAdd=$AryData['ExporterRecAdd'];
	$NotifyPartyName = $AryData['NotifyPartyName'];
	$NPartyAddress = $AryData['NPartyAddress'];
	$NPartyContactNumber = $AryData['NPartyContactNumber'];
	$ImporterName = $AryData['ImporterName'];
	$ImporterAddress = $AryData['ImporterAddress'];

	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$PortofLoading = $AryData['PortofLoading'];
	$DestinationCountry = $AryData['DestinationCountry'];
	//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$QtyUnit = $AryData['QtyUnit'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$TotalMcs = $AryData['TotalMcs'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$Remarks = $AryData['Remarks'];
	$OverSeasCommission = $AryData['OverSeasCommission'];
	$showOverSeasCommission = $AryData['showOverSeasCommission'];
	$showRemarks = $AryData['showRemarks'];
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];
	$showIgst = $AryData['showIgst'];

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$showProFEI = $AryData['showProFEI'];
	$ShowBank = $AryData['ShowBank'];
	$ShowInvoicevalue = $AryData['ShowInvoicevalue'];
	$ShowFrightCharge = $AryData['ShowFrightCharge'];
	$ShowCWiseWeightPerctText = $AryData['ShowCWiseWeightPerctText'];





	$AryBankData = getBankDetails($iconn, $BankID);

	//print_r($AryBankData); exit;


	$BenfName = $AryBankData['AccntName'];
	$BenfAccNo = $AryBankData['AccountNumber'];
	$BenfBankName = $AryBankData['BankName'];
	$BenfBankAddress = $AryBankData['BankAddress'];
	$BenfBankCode = $AryBankData['SwiftCode'];

	$BankDetails = $AryBankData['BankDetails'];
	$RoutingThrough = $AryBankData['RoutingThrough'];
	$NegotiatingBank = $AryBankData['NegotiatingBank'];
	$CurrenySymbol = $AryData['CurrenySymbol'];


	if ($CurrenySymbol == '₹') {
		$BILL_WORD = convert_rupees_to_words($UsdValue);
	} else {
		$BILL_WORD = numberTowords($UsdValue);
	}

	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];

	$AdminConfigDetails['HasCLFCharges'] = $HasCLFCharges;
	$AryPaymentData = getPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails);

	//print_r($AryPaymentData); exit;
	$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
	$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
	$DutiesPrice = $AryPaymentData['DutiesPrice'];
	$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
	$AntiDumpingDuty = round($AryPaymentData['AntiDumpingDuty'], 2);
	$CountervailingDuty = round($AryPaymentData['CountervailingDuty'], 2);
	$ReciprocalTariffsFee = round($AryPaymentData['ReciprocalTariffsFee'], 2);

	$MerchantDuty = round($AryPaymentData['MerchantDuty'], 2);
	$HarborDuty = round($AryPaymentData['HarborDuty'], 2);
	$FOBValueINR = round($AryPaymentData['FOBValueINR']);
	$InvValueUSD = $AryPaymentData['InvValueUSD'];
	$ISFStatic = $AryPaymentData['ISFStatic'];
	$CenttoUsd = $AryPaymentData['CenttoUsd'];
	$AntiDumpingDutyPerc = $AryPaymentData['AntiDumpingDutyPerc'];
	$HarbourMaintenanceFeePerc = $AryPaymentData['HarbourMaintenanceFeePerc'];
	$CountervailingDutyPerc = $AryPaymentData['CountervailingDutyPerc'];
	$MerchandiseProcessFeePerc = $AryPaymentData['MerchandiseProcessFeePerc'];
	$ReciprocalTariffsPerc = $AryPaymentData['ReciprocalTariffsPerc'];
	$TariffPerc = $AryPaymentData['TariffPerc'];
	$TariffFee = round($AryPaymentData['TariffFee'], 2);
	$PrintTariff = $AryPaymentData['PrintTariff'];
	$PrintReciprocalTariff = $AryPaymentData['PrintReciprocalTariff'];
	$ClearingAndForwardingCharges = $AryPaymentData['ClearingAndForwardingCharges'];

	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$IgstPerc = $AryPaymentData['IgstPerc'];
	$IgstValue = $FOBValueINR * $IgstPerc / 100;


	$content .= '
			<table border="1" cellspacing="0" cellpadding="2">
			
			<tr>
			<td width="58.5%">
			
			<table border="0" cellspacing="0" cellpadding="0">';
	//$ShowFrightCharge=true;
	if ($ShowFrightCharge) {

		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><font size="7">INVOICE VALUE</font></td>
        <td align="left" width="10%"><font size="7"><b>:</b></font></td>
        <td width="20%">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><font size="7"><b>$</b></font></td>
					<td width="75%" align="right"><font size="7"><b>' . number_format($InvValueUSD, 2) . '</b></font></td>
				</tr>
			</table>
		</td>
    	</tr>';

		$content .= '
		<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><font size="7">LESS : OCEAN FREIGHT</font></td>
        <td align="left" width="10%"><font size="7"><b>:</b></font></td>
        <td width="20%">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><font size="7"><b>$</b></font></td>
					<td width="75%" align="right"><font size="7"><b>' . number_format($FrieghtCharge, 2) . '</b></font></td>
				</tr>
			</table>
		</td>
    	</tr>';

		if ($HasCLFCharges && $ClearingAndForwardingCharges > 0) {
			$content .= '
			<tr>
				<td width="1%"></td>
				<td align="left" width="70%"><font size="7">LESS : CLEARING & FORWARDING CHARGES</font></td>
				<td align="left" width="10%"><font size="7"><b>:</b></font></td>
				<td width="20%" style="border-bottom:1px solid black;margin:0 auto;">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="10%" align="left"><font size="7"><b>$</b></font></td>
							<td width="75%" align="right"><font size="7"><b>' . number_format($ClearingAndForwardingCharges, 2) . '</b></font></td>
						</tr>
					</table>
				</td>
				</tr>';
		}

		if ($ISFStatic) {
			$content .= '
			<tr>
            <td width="1%"></td>
            <td align="left" width="70%"><font size="7">LESS : MERCHANDISE PROCESS FEE @ 0.3464%</font></td>
            <td align="left" width="10%"><font size="7"><b>:</b></font></td>
			<td width="20%" style="border-bottom:1px solid black;margin:0 auto;">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="10%" align="left"><font size="7"><b>$</b></font></td>
						<td width="75%" align="right"><font size="7"><b>' . number_format($MerchantDuty, 2) . '</b></font></td>
					</tr>
				</table>
			</td>
        	</tr>';
		}
		$content .= '
		<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><font size="7"><b>SUB TOTAL</b></font></td>
        <td align="left" width="10%"><font size="7"><b>:</b></font></td>
        <td width="20%">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><font size="7"><b>$</b></font></td>
					<td width="75%" align="right">
						<font size="7"><b>' . ($InvValueUSDWithOutDuties > 0 ? number_format($InvValueUSDWithOutDuties, 2) : '0.00') . '</b></font>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';
		$content .= '
		<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><font size="7">LESS : ANTI-DUMPING DUTY @ ' . $AntiDumpingDutyPerc . '%</font></td>
		<td width="30%" align="left">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="15%" align="left"><font size="7"><b>: $</b></font></td>
					<td width="45%" align="right">
						<font size="7"><b>' . ($AntiDumpingDuty > 0 ? number_format($AntiDumpingDuty, 2) : '0.00') . '</b></font>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';
		//uma cvd calculation included //18-03-2024

		if ($CountervailingDutyPerc != 0) {
			$content .= '
			<tr>
            <td width="1%"></td>
            <td align="left" width="70%"><font size="7">LESS : COUNTERVAILING DUTY @ ' . $CountervailingDutyPerc . '%</font></td>
			<td width="30%" align="left">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" align="left"><font size="7"><b>: $</b></font></td>
						<td width="45%" align="right">
							<font size="7"><b>' . ($CountervailingDuty > 0 ? number_format($CountervailingDuty, 2) : '0.00') . '</b></font>
						</td>
					</tr>
				</table>
			</td>
        	</tr>';
		}

		//uma cvd calculation included //18-03-2024
		if (!$ISFStatic) {
			$content .= '
			<tr>
            <td width="1%"></td>
            <td align="left" width="70%"><font size="7">LESS : MERCHANDISE PROCESS FEE @ ' . $MerchandiseProcessFeePerc . '%</font> </td>
			<td width="30%" align="left">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" align="left"><font size="7"><b>: $</b></font></td>
						<td width="45%" align="right">
							<font size="7"><b>' . ($MerchantDuty > 0 ? number_format($MerchantDuty, 2) : '0.00') . '</b></font>
						</td>
					</tr>
				</table>
			</td>
        	</tr>';
		}
		$content .= '
		<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><font size="7">LESS : HARBOR MAINTENANCE FEE @ ' . $HarbourMaintenanceFeePerc . '%</font></td>
		<td width="30%" align="left">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="15%" align="left"><font size="7"><b>: $</b></font></td>
					<td width="45%" align="right">
						<font size="7"><b>' . ($HarborDuty > 0 ? number_format($HarborDuty, 2) : '0.00') . '</b></font>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';

		if ($PrintReciprocalTariff && $PrintReciprocalTariff == 1 && $ReciprocalTariffsPerc != 0) {
			$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"><font size="7">LESS : RECIPROCAL TARIFFS @ ' . $ReciprocalTariffsPerc . '% </font></td>
			<td width="30%" align="left">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" align="left"><font size="7"><b>: $</b></font></td>
						<td width="45%" align="right">
							<font size="7"><b>' . ($ReciprocalTariffsFee > 0 ? number_format($ReciprocalTariffsFee, 2) : '0.00') . '</b></font>
						</td>
					</tr>
				</table>
			</td>
			</tr>';
		}

		if ($PrintTariff && $PrintTariff == 1 && $HasTariff && $TariffFee > 0) {
			$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"><font size="7">LESS : TARIFF @ ' . $TariffPerc . '% </font></td>
			<td width="30%" align="left">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" align="left"><font size="7"><b>: $</b></font></td>
						<td width="45%" align="right">
							<font size="7"><b>' . ($TariffFee > 0 ? number_format($TariffFee, 2) : '0.00') . '</b></font>
						</td>
					</tr>
				</table>
			</td>
			</tr>';
		}

		$content .= '
		<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><font size="7"><b>TOTAL DUTY</b></font></td>
		<td align="left" width="10%"><font size="7"><b>:</b></font></td>
        <td width="20%">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><font size="7"><b>$</b></font></td>
					<td width="75%" align="right">
						<font size="7"><b>' . ($DutiesPrice > 0 ? number_format($DutiesPrice, 2) : '0.00') . '</b></font>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';

		$content .= '
		<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><font size="7"><b>FOB VALUE IN USD</b></font></td>
		<td align="left" width="10%"><font size="7"><b>: </b></font></td>
		<td style="border-bottom:1px solid black; border-top:1px solid black; width:20%; margin:0 auto;">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><font size="7"><b>$</b></font></td>
					<td width="75%" align="right">
						<font size="7"><b>' . ($FOBValueUSD > 0 ? number_format($FOBValueUSD, 2) : '0.00') . '</b></font>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';
	}
	/*else if($ShowInvoicevalue){

																				   //echo "hii"; exit;
																				   $content.='<tr>
																			   <td  width="1%"></td>
																			   <td align="left" width="35%"><font size="7">INVOICE VALUE US $</font></td>
																			   <td align="left" width="15%" ><font size="7"> <b>: </b></font></td>
																			   <td align="left" width="60%" ><font size="7"> <b> '.number_format($InvValueUSD,2).'</b></font></td>
																			   </tr>';


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
																			   }*/



	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];

	//echo $TotalGrossWtKgs; exit;
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
	/*$GrossWeightKgs=$TotalGrossWtKgs;
																			   $GrossWeightLbs=$TotalGrossWtKgs*2.204;
																			   */

	$SalesCommission = ($NetWgtKgs * $OverSeasCommission) / $CenttoUsd;


	$content .= '</table></td>';

	$content .= '<td width="41.5%">
		<table border="0" cellspacing="0" cellpadding="0">';
	if ($QtyUnit == 'KGS') {
		$content .= '<tr>
			<td align="left" width="30%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7"><b>NET WEIGHT: </b></font></td>
			<td align="left" width="35%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7"> <b>' . number_format($NetWgtKgs, 3) . ' KGS</b></font></td>
			</tr>';

		$content .= '<tr>
			<td align="left" width="30%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7"><b>GROSS WEIGHT:</b> </font></td>
			<td align="left" width="35%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7"> <b>' . number_format($GrossWeightKgs, 3) . ' KGS</b></font></td>
			</tr>';
	} else {

		$content .= '<tr>
			<td align="left" width="30%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7">NET WEIGHT </font></td>
			<td align="left" width="35%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7"> <b>: ' . number_format($NetWgtLbs, 3) . ' LBS</b></font></td>
			<td align="left" width="35%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7"> <b> ' . number_format($NetWgtKgs, 3) . ' KGS</b></font></td>
			</tr>';

		$content .= '<tr>
			<td align="left" width="30%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7">GROSS WEIGHT </font></td>
			<td align="left" width="35%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7"> <b>: ' . number_format($GrossWeightLbs, 3) . ' LBS</b></font></td>
			<td align="left" width="35%" style="border-bottom: 1px solid #333333;border-right: 1px solid #333333;"><font size="7"> <b> ' . number_format($GrossWeightKgs, 3) . ' KGS</b></font></td>
			</tr>';
	}

	$content .= '</table>';

	$content .= '
		<table cellspacing="0" cellpadding="2"><tr>
		<td></td>
		<td></td>
		<td></td>
		</tr>';

	if ($showIgst) {
		$content .= '<tr>
				<td align="right" width="50%" style="border:1px solid black;"><font size="7">
				FOB VALUE USD</font></td>
				<td align="center" width="50%" style="border: 1px solid black;"><font size="7"><B>$
				' . number_format($FOBValueUSD, 2) . '</B></font></td>
				</tr>
				<tr>
				<td align="right" width="50%" style="border: 1px solid black;" ><font size="7">
				FOB VALUE INR @' . $ExchangeRate . '</font></td>
				<td align="center" width="50%" style="border: 1px solid black;"><font size="7"><B>Rs. 
				' . number_format($FOBValueINR, 2) . '</B></font></td>
				</tr>
				<tr>
				<td align="right" width="50%" style="border: 1px solid black;" ><font size="7">
				IGST @' . $IgstPerc . '%</font></td>
				<td align="center" width="50%" style="border: 1px solid black;" ><font size="7"><B>Rs. 
				' . number_format($IgstValue, 2) . '</B></font></td>
				</tr>';
	}

	$content .= '</table></td>';
	$content .= '</tr></table>';
	$content .= '</td></tr></table>';


	/* $ShowBank = false;
	if ($ShowBank) {
		$content .= '
			<table border="1" cellspacing="0" cellpadding="2">
			<tr><td>
			<p style="margin-left: 1em;"><b>BANK DETAILS  </b>' . $BankDetails . '</p>';
		$content .= '<p><b>Negotiating BANK  </b>' . $NegotiatingBank . '</p>';
		$content .= '<p><b>ROUTING THROUGH   </b>' . $RoutingThrough . '</p>
																				  </td></tr></table>';
	} */




	$content .= '<table border="1"><tr>';

	$formated_date = date("Y-m-d", strtotime($InvDate));
	$AryDescription = getInvoiceDescription($iconn, $formated_date, $ExporterID);
	$CInvNote = $AryDescription['CustomNote'];
	$Space = addSpace(50);



	/*$ShipmentUnder="1) WE HEREBY DECLARE THAT WE SHALL CLAIM THE BENEFITS UNDER CUSTOMS NOTIFICATION NO. 88 & 89/2017 DATED: 21-09-2017. AS WELL AS CHAPTER 3 OF FTP AS APPLICABLE. FISH AND CRUSTACEANS, MOLLUSCS AND OTHER AQUATIC INVERTEBRATES
																	  <br>2) THIS SHIPMENT IS MADE UNDER DUTY DRAW BACK SCHEME AS WELL AS UNDER RoDTEP SCHEME.
																	  <br>3) ITC HS CODE <b>".$CInvCode."</b>,DRAW BACK SCHEME CODE:19, SL.NO. OF EXPORT PRODUCT :030601B/160501B, RATE OF ENTITLEMENT : 3.0%/3.0% ON FOB OR Rs.23.6/KG . ";

																	   IF ($IGST=='LUT'){
																	  $ShipmentUnder.="<br>4)SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF INTEGRATED TAX: LUT No. $LutNumber "  ;
																	  }
																	  $Decleration="
																	  WE, IN REGARD TO OUR CLAIM UNDER RoDTEP SCHEME MADE IN THIS SHIPPING BILL , HEREBY DECLARE THAT:
																	  <br>1. I/ WE UNDERTAKE TO ABIDE BY THE PROVISIONS, INCLUDING CONDITIONS, RESTRICTIONS, EXCLUSIONS AND TIME-LIMITS AS PROVIDED UNDER RoDTEP SCHEME, AND RELEVANT NOTIFICATIONS, REGULATIONS, ETC., AS AMENDED FROM TIME TO TIME.

																	  <br>2. ANY CLAIM MADE IN THIS SHIPPING BILL OR BILL OF EXPORT IS NOT WITH RESPECT TO ANY DUTIES OR TAXES OR LEVIES WHICH ARE EXEMPTED OR REMITTED OR CREDITED UNDER ANY OTHER MECHANISM OUTSIDE RoDTEP.

																	  <br>3. I/WE UNDERTAKE TO PRESERVE AND MAKE AVAILABLE RELEVANT DOCUMENTS RELATING TO THE EXPORTED GOODS FOR THE PURPOSES OF AUDIT IN THE MANNER AND FOR THE TIME PERIOD PRESCRIBED IN THE CUSTOMS AUDIT REGULATIONS, 2018.SHRIMP/PRAWN IN FROZEN FORM OTHER THAN AFD.
																				  ";*/


	$content .= '</tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="9"><br><span align="right"><b>
				' . $ProductTypes . '' . $Space . '</b></span></font><font size="7"><br><br>' . nl2br($CInvNote) . '</font> ';
	if ($showOverSeasCommission) {
		$content .= '<br><font size="10"><b>SALES COMMISSION  ' . $UnitCurrency . ' ' . $OverSeasCommission . ' CENTS PER KG $ 
				' . number_format($SalesCommission, 2) . '</b></font> ';
	}


	$content .= '
				
				</th> 
				</tr>
				<tr>
				<th  align="bottom"  width="70%">
				<font size="7"> <br>AMOUNT CHARGABLE (IN ' . $UnitCurrency . ' ) (IN WORDS): 
				<b>(' . $BILL_WORD . ' ONLY)</b></font><BR>
				';
	if ($ShowCWiseWeightPerctText) {
		$content .= '<font size="6.5">' . nl2br($WtPerCtnTxt) . '<br></font>';
	}

	$content .= '	<BR><BR>
				<font size="7"><B><U> DECLARATION:</U></B> WE HEREBY DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT
				
				<br><br> <u>' . nl2br($Remarks) . '</u></font></th>
           
		   <th  align="center"  width="30%"><font size="7"> For <b>' . $AuthSign . '</b> <BR> <BR> <BR> 
						 
				<img src="' . $SignURL . '" 
				 width="100" height="30"style="width: 100" align="center"     valign="middle"/>				
				<p STYLE="text-align:center" > AUTHORISED SIGNATORY <BR> Signature & Date </p></font></th>
</tr>


				
				
				</table>';

	return $content;
}











function getReportData($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	global $AryShowConditions, $DocType, $PType;


	$ShowCWiseExporterGstNo = $AryShowConditions['ExporterGstNo'];
	$ShowCWiseReferenceNo = $AryShowConditions['ReferenceNo'];
	$ShowCWiseBuyer = $AryShowConditions['Buyer'];


	$CustomInvoiceSql = "SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
        SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading ,SCV.ContainerNo	as ContainerNo ,
		SPCF.Name as FinalDestination,SCV.CustomsInvNo,SCV.Remarks as CustomRemarks
		FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID 
	    left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
		left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SL on SL.id=SP.Country
		WHERE SCV.PiID='$PoID'";
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvData = $CustomInvoiceExec->fetch_assoc();
	$IGST = $CustomInvData['IGST'];
	$OverSeasCommission = $CustomInvData['OverSeasCommission'];
	$CInvDate = $CustomInvData['CInvDate'];
	$CInvCode = $CustomInvData['CInvCode'];
	$NoOfDays = $CustomInvData['NoOfDays'];
	$ProductTypes = $CustomInvData['ProductTypes'];
	$TotalGrossWt = $CustomInvData['TotalGrossWt'];
	$CodeType = $CustomInvData['CodeType'];

	if ($NoOfDays == 0) {
		$NoOfDays = '';
	} else {

		$NoOfDays = $NoOfDays . ' DAYS FROM BL';
	}

	$SqlMcsTotal = "SELECT sum(Mcs) as Mcs FROM `ScrmCIGradeData` where InvId='$PoID'  ";
	$StmtMcsTotal = $iconn->query($SqlMcsTotal);
	$RowMcs = $StmtMcsTotal->fetch_assoc();
	$TotalMcs = $RowMcs['Mcs'];




	$SqlScrmPoData = "SELECT *,SPD.Status as PoStatus,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,

ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,
SCL.name as DestinationCountryName,
ABD.SwiftCode as SwiftCode,
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode ,CurrenySymbol
	FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country 
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
  left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID
left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
left JOIN ScrmCurrencyMaster as SCM on SCM.CurrencyCode=SPD.UnitCurrency
 WHERE PoID='$PoID'";


	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ApprovedBy = $PoDataRow['ApprovedBy'];
	$CountryID = $PoDataRow['DestinationCountry'];
	$DeliveryTerm = $PoDataRow['DeliveryTerms'];
	$DeliveryTermCode = getDeliveryTermCode($DeliveryTerm);


	// $SqlLogin = "SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
	// $StmtLogin = $iconn->query($SqlLogin);
	// $RowLogin = $StmtLogin->fetch_assoc();
	// $SignURL = $RowLogin['SignURL'];

	$ExporterData = getExporterDetails($ExpID);
	//$ProcessorData=getExporterDetails($ProcessorID);

	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);



	if ($PoDataRow['NotifyPartyID'] != 0) {
		$AryData['showNotifyParty'] = true;
	} else {
		$AryData['showNotifyParty'] = false;
	}

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
		$AryData['ShowInvoicevalue'] = false;	//uma
	} else {
		$AryData['ShowInvoicevalue'] = false;
	}


	//if($PoDataRow['DestinationCountry']==26){
	if ($PType == 'LBS') {  //uma
		$AryData['ShowDeliveryTerms'] = true;
	} else {
		$AryData['ShowDeliveryTerms'] = false;
	}


	if ($ExpID != 5 && $PoDataRow['DestinationCountry'] != 26) {
		$AryData['showExpUsFda'] = false;
	}
	/*else if($ExpID==5 && $PoDataRow['DestinationCountry']!=26){
																			$AryData['showExpUsFda']=true;	
																			}
																			*/ else {
		$AryData['showExpUsFda'] = false;
	}
	$AryData['showExpIECode'] = true;

	/*if($ExpID==5){
																			$AryData['showExpIECode']=true;	
																			}else{
																			$AryData['showExpIECode']=false;	
																			}*/

	if ($ShowCWiseExporterGstNo) {
		$AryData['showExpGSTNo'] = true;
	} else {
		$AryData['showExpGSTNo'] = false;
	}
	//$AryData['showExpGSTNo']=true;
	if ($PoDataRow['ShowBank'] != 0) {
		$AryData['ShowBank'] = true;
	} else {
		$AryData['ShowBank'] = false;
	}

	if ($PoDataRow['DestinationCountry'] != 26) {
		$AryData['showProFEI'] = true;
	} else {
		$AryData['showProFEI'] = false;
	}

	if ($PoDataRow['ShowBrand'] == 1) {
		$AryData['ShowBrand'] = false;
	} else {
		$AryData['ShowBrand'] = false;
	}
	//if($PoDataRow['ShowFrightCharge']==1 && $PoDataRow['ShowFrieght']==1){
	if ($PoDataRow['ShowFrightCharge'] == 1) {
		$AryData['ShowFrightCharge'] = true;
	} else {
		$AryData['ShowFrightCharge'] = false;
	}

	if ($PoDataRow['Insurance'] != 0) {
		$AryData['ShowInsurance'] = true;
	} else {
		$AryData['ShowInsurance'] = false;
	}


	if ($CustomInvData['OverSeasCommission'] != '') {
		$AryData['showOverSeasCommission'] = true;
	} else {
		$AryData['showOverSeasCommission'] = false;
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






	/* Return Data*/
	$AryData['ProductTypes'] = $ProductTypes;
	$AryData['TotalGrossWtKgs'] = $TotalGrossWt;
	$AryData['PoStatus'] = $PoDataRow['PoStatus'];
	$AryData['ApprovedBy'] = $ApprovedBy;
	$AryData['PaymentTerms'] = $PaymentTerms;
	//$AryData['DeliveryTerms']=$DeliveryTerms; 
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
	$AryData['ShowCWiseReferenceNo'] = $ShowCWiseReferenceNo;
	$AryData['ShowCWiseBuyer'] = $ShowCWiseBuyer;
	$AryData['ShowCWiseWeightPerctText'] = $AryShowConditions['WeightPerctText'];
	$AryData['ShowCWiseDeliveryTerms'] = $AryShowConditions['DeliveryTerms'];
	$AryData['ShowCWiseBrand'] = $AryShowConditions['Brand'];
	$AryData['ShowCWiseDeliveryTermCode'] = $AryShowConditions['DeliveryTermCode'];
	$AryData['ShowCWiseUsfda'] = $AryShowConditions['Usfda'];


	$AryData['ConsigneeID'] = $PoDataRow['ConsigneeID'];
	$AryData['ReferenceNo'] = $PoDataRow['ReferenceNo'];
	$AryData['BuyerID'] = $PoDataRow['BuyerID'];
	$AryData['ConsigneeName'] = $PoDataRow['ConsigneeName'];
	$AryData['ConsigneeAddress'] = $PoDataRow['ConsigneeAddress'];
	$AryData['BuyerName'] = $PoDataRow['BuyerName'];
	$AryData['BuyerAddress'] = $PoDataRow['BuyerAddress'];
	$AryData['CurrenySymbol'] = $PoDataRow['CurrenySymbol'];
	$AryData['DeliveryTermCode'] = $DeliveryTermCode;


	$AryData['PortOfDischarge'] = strtoupper($CustomInvData['PortofDischarge']) . ' ' . strtoupper($CustomInvData['CountryCode']) . ', ' . $CustomInvData['PortofDischargeCountry'];

	$AryData['PlaceOfReceiptBy'] = $CustomInvData['PlaceOfRecipt'];
	$AryData['PortOfLoading'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	if ($PoDataRow['ConsigneeID'] == '63') {
		$AryData['FinalDestination'] = strtoupper($CustomInvData['FinalDestination']);
		$AryData['DestinationCountry'] = $CustomInvData['FinalDestination'];
		$AryData['DestinationCountryName'] = $PoDataRow['DestinationCountryName'];
	} else {
		$AryData['FinalDestination'] = strtoupper($CustomInvData['FinalDestination'] . ', ' . $PoDataRow['DestinationCountryName']);
		$AryData['DestinationCountry'] = $PoDataRow['name'];
	}


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



	/* if ($PoDataRow['DeliveryTerms'] == 'CY/SD') {
		$PoDataRow['DeliveryTerms'] = 'CY/SD <br> Store Delivery';
	} */

	$AryData['DeliveryTerms'] = $PoDataRow['DeliveryTerms'];
	$AryData['QtyUnit'] = $PoDataRow['QtyUnit'];
	$AryData['ShipByDate'] = $PoDataRow['ShipByDate'];
	$AryData['SignURL'] = $SignURL;
	$AryData['PreparedBy'] = $PoDataRow['PreparedBy'];
	$AryData['BankID'] = $PoDataRow['BankID'];
	$AryData['IGST'] = $IGST;
	$AryData['CInvCode'] = $CInvCode;
	$AryData['NoOfDays'] = $NoOfDays;
	$AryData['CodeType'] = $CodeType;
	$AryData['UnitCurrency'] = $PoDataRow['UnitCurrency'];


	$AryData['NotifyPartyName'] = $PoDataRow['NotifyPartyName'];
	$AryData['NPartyAddress'] = $PoDataRow['NPartyAddress'];
	$AryData['NPartyContactNumber'] = $PoDataRow['NPartyContactNumber'];


	$AryData['ContainerNo'] = $CustomInvData['ContainerNo'];
	$AryData['SealNo'] = $CustomInvData['SealNo'];
	$AryData['IRNNumber'] = $CustomInvData['IRNNumber'];
	$AryData['OverSeasCommission'] = $CustomInvData['OverSeasCommission'];
	$AryData['Remarks'] = $CustomInvData['CustomRemarks'];



	return $AryData;
}


function getBreakUpData($iconn, $GradeID, $PiID)
{
	global $PType;

	if ($PiID == '') {
		$Qry = "WHERE ID in ($GradeID)";
		$PriceQry = 'Price';
	} else {
		$Qry = "WHERE InvId='$PiID'";
		$PriceQry = 'sum(Price) as Price';
	}

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

	$SqlTotal = "SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,$PriceQry ,	
						
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];

	if ($PiID != '') {
		$ArrayGrossweightText = array();
		$ArrayNetweightText = array();

		$SqlWtPerCtn = "SELECT 
	round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,
	round(sum(GrossWt)/Sum(Mcs),2) as GrossWt FROM `ScrmPoGradeData` as SPGD 
		left JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID 
		WHERE InvId='$InvId' GROUP by PackingStyleID;";
		$StmtWtPerCtn = $iconn->query($SqlWtPerCtn);
		while ($RowWtPerCtn = $StmtWtPerCtn->fetch_assoc()) {
			$NetWtPerCtn = $RowWtPerCtn['NetWtLbs'];
			$GrossWtPerCtn = $RowWtPerCtn['GrossWt'];
			$ArrayGrossweightText[] = $GrossWtPerCtn;
			$ArrayNetweightText[] = $NetWtPerCtn;
		}

		$NetWtPerCtn = implode(" LBS , ", $ArrayNetweightText);
		$GrossWtPerCtn = implode(" LBS , ", $ArrayGrossweightText);

		$WtPerCtnTxt = 'EACH M/CTNS. CONSISTS OF NET WT. ' . $NetWtPerCtn . ' LBS AND GROSS WT. ' . $GrossWtPerCtn . ' LBS.';
	}


	$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'";
	$StmtQtyUnit = $iconn->query($SqlQtyUnit);
	$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
	$QtyUnit = $RowQtyUnit['QtyUnit'];

	/*	if($PType=='KGS'){
																					   $QtyUnit='KGS';
																				   }
																			   */

	if ($QtyUnit == 'KGS') {
		$Qty = $RowTotal['NetWgtKgs'];
		$UsdValue = $RowTotal['PriceKgs'];
		$UnitPrice = number_format($RowTotal['Price'], 2);
	} else {
		//$Qty=$RowTotal['NetWgtLbs']*0.454;// Changed cause Lb and Kg Separate
		$Qty = $RowTotal['NetWgtLbs'] * 0.454;
		$UsdValue = $RowTotal['PriceLbs'];


		$UnitPrice = round(($UsdValue / $Qty), 7);
		$UnitPrice = $UnitPrice + 0.0000001;
	}
	if ($QtyUnit == 'KGS') {
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	} else {
		$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $RowTotal['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	}

	if ($PType == 'KGS' && $QtyUnit == 'KGS') {
		//$AryPriceData['Qty']=$Qty;
		$AryPriceData['Qty'] = $RowTotal['NetWgtKgs'];
	} else if ($PType == 'KGS' && $QtyUnit != 'KGS') {
		//$AryPriceData['Qty']=$RowTotal['NetWgtKgs']; 
		$AryPriceData['Qty'] = $RowTotal['NetWgtLbs'] * 0.454;
	} else {
		$AryPriceData['Qty'] = $RowTotal['NetWgtLbs'];
	}

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];

	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['UnitPrice'] = $UnitPrice;

	/*$AryPriceData['NetWgtKgs']=$RowTotal['NetWgtKgs'];
																			   $AryPriceData['NetWgtLbs']=$RowTotal['NetWgtLbs'];
																			   $AryPriceData['GrossWeightKgs']=$RowTotal['GrossWeightKgs'];
																			   $AryPriceData['GrossWeightLbs']=$RowTotal['GrossWeightLbs'];*/

	$AryPriceData['NetWgtKgs'] = $NetWgtKgs;
	$AryPriceData['NetWgtLbs'] = $NetWgtLbs;
	$AryPriceData['GrossWeightKgs'] = $GrossWeightKgs;
	$AryPriceData['GrossWeightLbs'] = $GrossWeightLbs;
	$AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;

	return $AryPriceData;
}


function getBankDetails($iconn, $BankID)
{

	$SqlBank = "SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$AryBankData = $StmtBank->fetch_assoc();

	return $AryBankData;
}

function addSpace($SpaceCount)
{

	for ($i = 0; $i < $SpaceCount; $i++) {
		$Space .= '&nbsp;';
	}

	return $Space;
}
