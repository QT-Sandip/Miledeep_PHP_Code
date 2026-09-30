<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");

$AdminConfigDetails = json_decode(json_encode($_SESSION['ClientConfigData']), true);

$style = '<style>
th {
    font-size: 8px;
	padding : 10px;
	
}
td {
    font-size:  8px;
	padding : 10px;
	
}
p {
	line-height: 0.9;
}
</style> 
';


$PoID = $_GET['id'];
$Type = $_GET['Type'];
$PType = $_GET['PType'];
if ($PType == '') {
	$PType = 'LBS';
}

$SqlStatus = "SELECT * FROM ScrmPoData  WHERE PoID='$PoID'";
$StmtStatus = $iconn->query($SqlStatus);
$PoStatus = $StmtStatus->fetch_assoc();
$Status = $PoStatus['Status'];
$DocType = 'CMI';
$CountryID = $PoStatus['DestinationCountry'];
$BuyerID = $PoStatus['ConsigneeID'];

$AryLables = getInvoiceLables($DocType, $CountryID, $BuyerID);
$AryShowConditions = getInvoiceShowConditions($DocType, $CountryID, $BuyerID);


$SqlCustom = "SELECT IGST,SPD.ExporterID as ExporterID,CustomsInvNo,CInvDate FROM `ScrmCustomInvoices`  as SCV
left join ScrmPoData as SPD on  SPD.PoID=SCV.PiID
WHERE PiID='$PoID'";
$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom->fetch_assoc();
$IGST = $RowCustom['IGST'];
$ExpID = $RowCustom['ExporterID'];
$CustomsInvNo = $RowCustom['CustomsInvNo'];
$CustInvDate = date("d-m-Y", strtotime($RowCustom['CInvDate']));



class MYPDF extends TCPDF
{

	public function Header()
	{
		//	
		/*getCustomInvData($iconn,$PoID);
																																			   $IGST=$AryData['IGST'];*/
		global $IGST, $CustomsInvNo;

		//ECHO $CustomsInvNo; EXIT;
		/*if($IGST=='IGST'){
																																				   $Header2='SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
																																			   }
																																			   else{
																																				   $Header2='';
																																			   }*/

		global $Header;
		$image_file = 'images/ds_logo.jpg';
		if ($this->page == 1) {  // show header in page1 only

			$this->SetFont('helvetica', 'B', 13);
			$this->Cell(0, 0, 'COMMERCIAL INVOICE' . $Header, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
			$this->SetFont('helvetica', ' ', 8);
			$this->Cell(130, 0, '' . $Header2, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

			$this->SetTextColor(255, 0, 0);

			$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
		}
	}

	public function Footer()
	{

		global $IGST, $CustomsInvNo, $CustInvDate;

		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . '(' . $CustomsInvNo . ' / Dt.' . $CustInvDate . ')', 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('COMMERCIAL INVOICE' . $Header);
$pdf->SetFont('helvetica', '', 19);
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(7);
$pdf->SetHeaderMargin(4);
$pdf->SetMargins(10, 15, 10, 10);
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
//$pdf->SetFont('helvetica', '', 13);


/*$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];
$ProcessorInvShortCode=$ArrayFilename['ProcessorInvShortCode'];

$file_name=$InvShortCode.'-'.$ProcessorInvShortCode.'-'.$CInvNo.'-'.$FinancialYear.'-CI'.'.pdf';*/

$file_name = str_replace('/', '_', $CustomsInvNo);
$Fname = '-CI';
$file_name = $file_name . $Fname . '.pdf';
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


function getHeaderTable($iconn, $ID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	global $AryData, $style, $PType, $AryLables, $DocType;

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
	$ConsigneeCountry = $AryData['ConsigneeCountry'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$DestinationCountryName = $AryData['DestinationCountryName'];
	$VesselNumber = $AryData['VesselNumber'];

	$ShowCWiseLc = $AryData['ShowCWiseLc'];
	$ShowCWiseLcDate = $AryData['ShowCWiseLcDate'];
	$ShowCWiseLcIssuedBy = $AryData['ShowCWiseLcIssuedBy'];
	$ShowCWiseWeightPerctText = $AryData['ShowCWiseWeightPerctText'];
	$ShowCWiseNotifyPrty = $AryData['ShowCWiseNotifyPrty'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$ReferenceNo = $AryData['ReferenceNo'];
	$PoDate = $AryData['PoDate'];
	$ShowBank = $AryData['ShowBank'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$ShowCWiseBuyer = $AryData['ShowCWiseBuyer'];
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
	$PortofLoading = $AryData['PortOfLoading'];
	$DestinationCountry = $AryData['DestinationCountry'];
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
	$ShippingTerms = $AryData['ShippingTerms'];

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$ShowCWiseApprovalNo = $AryData['ShowCWiseApprovalNo'];
	$showProFEI = $AryData['showProFEI'];

	$ContainerNo = $AryData['ContainerNo'];
	$SealNo = $AryData['SealNo'];
	$BLDate = $AryData['BLDate'];
	//$BLDate=getFormatedDateDMMY($AryData['BLDate']);
	$SBDate = $AryData['SBDate'];
	$BLNo = $AryData['BLNo'];
	$SBNo = $AryData['SBNo'];
	$OrgVesselName = $AryData['OrgVesselName'];
	$OrgVOYNO = $AryData['OrgVOYNO'];
	$ByRoad = $AryData['ByRoad'];
	$ComShippingTermExt = $AryData['ComShippingTermExt'];
	$ShowBLNo = $AryData['ShowBLNo'];
	$ShowBLDate = $AryData['ShowBLDate'];
	$ShowSBNo = $AryData['ShowSBNo'];
	$ShowSBDate = $AryData['ShowSBDate'];

	// if($ShowBLNo==0){

	//     $BLNo='';	

	// }ELSE{

	// $BLNo=$BLNo;	
	// }

	// if($ShowBLDate==0){

	//  $BLDate='';	
	// }ELSE{

	// $BLDate=$BLDate;	
	// }	


	if ($ComShippingTermExt != '') {

		$ShippingTermsDisplay = $AryData['ShippingTerms'];
		$ShippingTermsDisplay = '';
	}


	$content .= $style;
	$content .= '
	 
	 <table border="1" cellspacing="0" cellpadding="0">  ';
	if ($PType == 'KGS') {

		$content .= '<tr><th width="100%"> <br><br> <b> IRN NO: ' . $IRNNumber . '<br></b> </th></tr>';
	}
	$content .= '	 <tr>
				 
				
				
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';

	$content .= '
				
				<tr>
				<th  align="LEFT" ><b>EXPORTER & IMPORTER ON RECORDS :</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '';

	/*	if($showExpUsFda){
																				   $content .= '	USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>';
																				   }
																				   
																				   if($showExpIECode){
																				   $content .= ' IE CODE  : '.$ExporterIeCode;
																								   
																				   }
																				   
																				   if($showExpGSTNo){
																				   $content .= '<BR> GST NO  : '.$ExporterGstNo;
																				   }*/


	$content .= ' </th></tr>';






	$content .= '
				<tr> 
				<th  align="LEFT" ><b><u>Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '  </th>
				</tr>';
	// if ($ShowCWiseNotifyPrty) {}
	if ($showNotifyParty) {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><b><u>Notify Party</u></b>
				<br>' . $NotifyPartyName . ',<br>' . nl2br($NPartyAddress) . '
				
				
				 </th>
				</tr>';
	}
	// }
	$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgfgtyhjmnkih</b>';
	/*<tr>  
																					 
																				  <th  align="LEFT" width="50%" > Pre-Carriage by
																				  <br  align="center">REFER CONTAINER </th>
																				  <th  align="LEFT" width="50%" > Place of Receipt by
																				  <br align="center">ANY PORT, INDIA </th>
																				  below lines
																				  <th  align="left"  width="12.5%"style="border-right: 1px solid black;"> Shipping Marks: <br><br><BR align="center"> NO MARKS </th>
																				  <th  align="LEFT" width="10%" style="border-right: 1px solid black;"> No. & kind of packings <br><BR><b> '.$TotalMcs.'</b> MCS </th>
																				  <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"> Container Number <br><br><br><b align="center">1 X 40 FCL</b> </th>
																				  <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"> Seal Number <br><br><br><b align="center">'.$SealNo.'</b> </th>
																				  
																				  </tr>*/
	$content .= '<tr>  
           		
				<th  align="LEFT" width="50%" style="font-size: 7px;"> Vessel / Flight No. 
               <BR align="LEFT"> ' . $OrgVesselName . '  <br> ' . $OrgVOYNO . '
			    <br> </th>
				<th  align="LEFT" width="50%" style="font-size: 7px;"> Port of Loading
				<br align="center">' . $PortofLoading . ' </th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" > Port of Discharge
				<br align="center">' . $PortOfDischarge . ' </th>
				<th  align="LEFT" width="50%" > Final Destination
				<br align="center"> ' . $FinalDestination . ',' . $DestinationCountryName . ' </th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" >
				 Marks & Nos  
				</th>
				<th  align="LEFT" width="50%" >
				 No.& Kind of Pkgs <br><b align="center" >' . $TotalMcs . ' M/Cs. </b> 
				</th>
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  > <b> Invoice No. & Date.
				 ' . $InvNo . ',Dated ' . $InvDate . ' </b> </th>
				</tr>
				
				<tr> 
				<th  align="LEFT" > <b> Purchase Order No. & Date.
				<br> ' . $PoNo . $PoDate . '</b>  </th>
				</tr>';







	$content .= '		<tr > 
				<th  align="LEFT"  > <b> Other Reference(s) :
				' . $ReferenceNo . '  </b>
				
				 </th></tr >';
	if ($showImporter) {

		$content .= '	<tr> 
           		
		
                <th  align="left" width="100%">
				<b><u> Importer of Record:</u></b> <br>  ' . $ImporterName . '
				<br> ' . nl2br($ImporterAddress) . '</th>
				</tr>';
	}
	if ($ShowCWiseBuyer) {
		if ($BuyerID != 0) {

			$content .= '	<tr> 
           		
                <th  align="left" width="100%">  <b><u>Buyer 
				(if other than consignee) :</b></u><br>' . $BuyerName . ',<br>' . nl2br($BuyerAddress) . '<br> </th>
				</tr>';
		}
	}


	$content .= '<tr >
				<th  align="LEFT"  > 
				 <u><b>' . $AryLables['Manufacturer'] . '</u> :</b><br> ' . $ProcessorName . '<br> ' . nl2br($ProcessorAddress) . '';
	if ($ShowCWiseApprovalNo) {
		$content .= '<br>  ' . $AryLables['ApprovalNo'] . ' ' . $ProcessorApprovalNo . '';
	}


	if ($ShowCWiseUsfda) {
		$content .= '<br> BIO TERRORISM / USFDA REGISTRATION NO.  ' . $ProcessorUsfdaNo;
	}

	/*if($showProFEI){			
																				  $content .= '<BR> FEI # :  '.$ProcessorFEI;
																				  }
																		  */
	$content .= ' </th></tr>';



	$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="49%">  Country of Origin of Goods 	
				<br><b align="center"> INDIA</b> </th>
				
				<th border="1"  align="left" width="51%">  Country of Final Destination  
				<br><b align="center">  ' . $DestinationCountry . '</b> </th>
				
				
				
               
				</tr>
				<tr> 
				
			
				
				<th  align="LEFT"  width="100%" > <b> <u>Terms of Payment:</u>
				</b><br><BR> ' . $PaymentTerms . ' <br>
				 
				 <!--SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . ' -->
				';




	$content .= '  </th>
				</tr>
			<tr>  
           		
				<th  align="LEFT" > <b><u> CONTAINER NO.  
				' . $ContainerNo . '</b></u> <b style="color:white">______________</b>   <b><u>SEAL NO. ' . $SealNo . '</b></u>
                 <br><br>';
	if ($ShowBLNo) {
		$content .= '<b>' . $AryLables['BLNo'] . ' ' . $BLNo . '</b>';
	}
	if ($ShowBLDate) {
		$content .= '<b style="color:white">_________________</b>  <b>' . $AryLables['BLDate'] . '   ' . $BLDate . '</b>';
	}

	if ($ShowSBNo) {
		$content .= '<br><b>' . $AryLables['SBNo'] . ' ' . $SBNo . '</b>';
	}
	if ($ShowSBDate) {
		$content .= '<b style="color:white">_________________</b>  <b>' . $AryLables['SBDate'] . '   ' . $SBDate . '</b>';
	}


	$content .= ' </th>
				</tr>
				<tr>  
           		
				<th  align="left" > <b  ><u> Shipping Term</u></b>
                  <br>
				 <span align="left"> <b>' . $ShippingTermsDisplay . ' ' . $ComShippingTermExt /* $FinalDestination.','.$DestinationCountryName*/ . '</span></b>
				
				
				 </th>
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
	global $AryData, $style, $PType, $DocType, $AryLables, $AdminConfigDetails;
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
	$ShowCWiseBuyer = $AryData['ShowCWiseBuyer'];
	$showImporter = $AryData['showImporter'];
	$showNotifyParty = $AryData['showNotifyParty'];
	$ShippingTerms = $AryData['ShippingTerms'];
	//echo $AryData['ComShippingTermExt']; exit;
	$ComShippingTermExt = $AryData['ComShippingTermExt'];
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
	$CurrencyShortCode = $AryData['CurrencyShortCode'];
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
	$showIgst = $AryData['showIgst'];
	$showDiscount = $AryData['showDiscount'];
	$TextSize = $AryData['TextSize'];

	if ($QtyUnit == 'KGS') {
		$Unit = 'KG';
	} elseif ($QtyUnit == 'LBS') {
		$Unit = 'LB';
	} else {
		$Unit = $QtyUnit;
	}



	$content = '
				<table border="1" cellspacing="0" cellpadding="2">
				<tr>
				<th  align="LEFT" width="58.5%" >   Description of Goods <b align="center">' . $SealNo . '</b> </th>
                <th  align="center" width="13.5%" >
				 Quantity in <br>' . $QtyUnit . ' ' . $AryLables['UnitInBreakUp'] . '  </th>
                <th  align="center" width="13.5%" > Rate/' . $Unit . ' ' . $AryLables['UnitInBreakUp'] . ' <br>' . $UnitCurrency . ' </th>
                <th  align="center" width="14.5%" > Amount in ' . $UnitCurrency . '  </th>
                </tr>';

	$CurrentCount = 0;


	// $SqlVarietyMcs="SELECT sum(McS) As VarietyMcs,BuyerItemCode,PrintStatus,
	// (SELECT CIDescription from ScrmCIGradeData WHERE PoGradeID=ScrmPoGradeData.ID) as Description,Variety,Treatment,Grade,
	//  PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmPoGradeData` 
	//  left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
	//  left join `ScrmBrandsMaster` SBM on  SBM.BrandName=Brand
	//  WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand 
	//  Order By ID asc";

	$SqlVarietyMcs = "SELECT sum(McS) As VarietyMcs,BuyerItemCode,PrintStatus,
			CIDescription as Description,Variety,Treatment,Grade,
			 PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmCIGradeData` 
			 left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 left join `ScrmBrandsMaster` SBM on  SBM.BrandName=Brand
			 WHERE InvId='$ID' GROUP by CIDescription
			 Order By ID asc";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	$RowCount = mysqli_num_rows($StmtVarietyMcs);

	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {
		$CurrentCount++;


		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];
		$BAP = $VarietyMcsRow['BAP'];
		$BuyerItemCode = $VarietyMcsRow['BuyerItemCode'];
		$BreakUpDescription = $VarietyMcsRow['Description'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];
		$Treatment = $VarietyMcsRow['Treatment'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
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


		$description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
		$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
		$Whitspace = '<b style="color:white"> yuhju</b>';
		$FontSize = "7";
		//$TOTAL_MCS=0;
		$content .= '
				
			
			
			';



		//$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$BreakUpDescription.'<br>';
		if ($BrandPrintStatus) {

			$ShowBrand = 'BRAND : ' . $VarietyMcsRow['Brand'] . '';
		}

		if ($BreakUpDescription != '') {
			$Description = $BreakUpDescription . '<br>';
		} else {
			$Description = $VarietyMcsRow['Variety'] . '' . $Treatment . '<br>';
		}

		if ($DestinationCountryId != 26) {
			$Description .= 'PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' ' . $ShowBrand . '<B><br>' . $BAP . '</B>';
		} else {
			$Description .= '	PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' ' . $ShowBrand . ' ';
		}

		if ($BreakUpDescription != '') {
			$Description = $BreakUpDescription . '<br>';
		}


		//$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].''.$Whitspace.' ';	
		//$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].''.$Whitspace.' ';	


		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" > '
			. nl2br($Description) . ' </td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;">  </td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;">  </td>
			</tr>';
		if ($CurrentCount < 2) {

			$content .= '<tr>';
			/*if($BuyerItemCode!=''){
																																																						   $content.='
																																																						   <th align="center" width="10%" style="border-right:1px solid white;"  >  <u>Item Code</u> </th>
																																																						   <th align="center" width="10%" style="border-right:1px solid white;"  >  <u>SIZE</u> </th>';
																																																						   }else{*/
			$content .= '<th align="center" width="20%" style="border-right:1px solid white;" >  <u>SIZE</u> </th>';
			//}
			$content .= '<th align="center" width="18.5%" style="border-right:1px solid white;" > <u>NO. OF CTNS</u> </th>
			
		<td align="center" width="20%" style="border-right:1px solid black;">  </td>
		<td align="center" width="13.5%" style="border-right:1px solid black;">  </td>
			<td align="center" width="13.5%" style="border-right:1px solid black;">  </td>
			</tr>';
		}
		// echo  $SqlVariety="SELECT * FROM `ScrmPoGradeData` as PGD
		// left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
		// InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
		// and	PackingStyleID='$PackingStyleID' order by abs(Grade)"; exit;  //uma

		$SqlVariety = "SELECT * FROM `ScrmCIGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and CIDescription='$BreakUpDescription' and Brand='$Brand' 
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";

		$StmtVariety = $iconn->query($SqlVariety);
		$ArySubBreakUp = array();

		$TotalSubMcs = 0;
		$TotalSubLbs = 0;
		$TotalSubamountUSD = 0;


		while ($RowVariety = $StmtVariety->fetch_assoc()) {

			//$ArySubBreakUp[]=$RowVariety['ID']; 
			$ArySubBreakUp[] = $RowVariety['PoGradeID']; //uma


			$AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['PoGradeID'], '');
			$Qty = $AryPriceBreakUp['Qty'];
			$UsdValue = $AryPriceBreakUp['UsdValue'];
			$TotalMcs = $AryPriceBreakUp['TotalMcs'];
			$Price = $AryPriceBreakUp['Price'];
			$UnitPrice = $AryPriceBreakUp['UnitPrice'];

			$TotalSubMcs = $TotalSubMcs + $TotalMcs;

			$TotalSubLbs = $TotalSubLbs + $Qty;

			$TotalSubamountUSD = $TotalSubamountUSD + $UsdValue;

			$AryPaymentData = getPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails);

			//echo $DocType; exit;
			$InvValueUSD = $AryPaymentData['InvValueUSD'];
			$InvValueInr = $AryPaymentData['InvValueINR'];
			$ExchangeRate = $AryPaymentData['ExchangeRate'];
			$DiscountPerc = $AryPaymentData['DiscountPerc'];
			$DiscountAmount = $AryPaymentData['DiscountAmount'];
			$IgstPerc = $AryPaymentData['IgstPerc'];
			$IgstValue = $AryPaymentData['IgstValue'];



			/*uma if($PType=='KGS'){
																																																								  $Price=sprintf("%0.7f",$UnitPrice);
																																																							  }else{
																																																								  $Price=sprintf("%0.2f",$Price);
																																																							  }*/




			$content .= '<tr>';

			/*if($BuyerItemCode!=''){
																																																						   $content.='
																																																						   <td align="center" width="10%" style="border-right:1px solid white;"  > 
																																																								'.$RowVariety['BuyerItemCode'].' 
																																																								</td><td align="center" width="10%" style="border-right:1px solid white;"  > 
																																																								'.$RowVariety['Grade'].' 
																																																								</td>';
																																																						   }else{*/
			$content .= '<td align="center" width="20%"  style="border-right:1px solid white;" > 
				 ' . $RowVariety['Grade'] . ' 
				 </td>';
			//}

			//<td align="center" width="20%" style="border-right:1px solid black;"  > 
			//		'.$RowVariety['Brand'].'   </td>		
			$content .= '
				<td align="center" width="18.5%" style="border-right:1px solid white;"  > 
				' . $TotalMcs . '   </td>
				<td align="center" width="20%" style="border-right:1px solid black;"  > 
				 </td>	
				<td align="center" width="13.5%" style="border-right:1px solid black;"  > 
				' . number_format($Qty, 3) . '  </td>
				<td align="center" width="13.5%" style="border-right:1px solid black;"  > 
				' . $Price . ' </td>
				
				<td align="center" width="14.5%" style="border-right:1px solid black;" > 
				' . number_format($UsdValue, 2) . ' </td>
				
				</tr>';
		}

		$SubVarityBrkUp = implode(',', $ArySubBreakUp);

		$AryPriceData = getBreakUpData($iconn, $SubVarityBrkUp);
		$Qty = $AryPriceData['Qty'];
		$UsdValue = $AryPriceData['UsdValue'];
		$TotalMcs = $AryPriceData['TotalMcs'];
		$Price = $AryPriceData['Price'];

		if ($RowCount > 1) {
			$content .= '
				<tr>
				<td align="center" width="20%"  style="border-right:1px solid white;" > 
				<b>Total</b	>
				 </td>
				<td align="center" width="18.5%" style="
				border-top:1px solid black;
				border-bottom:1px solid black;
				" >
														
														 
				' . $TotalSubMcs . ' </td>
				
				<td align="center" width="20%" style="border-right:1px solid black;">
														
														  </td>
				<td align="center" width="13.5%" > 
				' . number_format($TotalSubLbs, 3) . ' </td>
				<td align="center" width="13.5%" style="border-right:1px solid black;">
														  </td>
				<td align="center" width="14.5%" style="border-top:1px solid black;
				border-bottom:1px solid black;">
														 
				' . number_format($TotalSubamountUSD, 2) . ' </td>
				
				</tr>';
		}
	}
	// echo $ID; exit;
	$AryPriceData = getBreakUpData($iconn, $RowVariety['PoGradeID'], $ID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];


	$content .= ' 
				<tr>
				
				<td align="center" width="20%" style="border-top: 1px solid black;" > <B>
				GRAND TOTAL </B> </td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; " > 
				' . $TotalMcs . ' </td>
				<td align="center" width="20%" style="border-top: 1px solid black; border-right: 1px solid black;" > 
				 </td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"> 
				' . number_format($Qty, 3) . ' </td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" > 
				 </td>
				<td align="center" width="14.5%" style="
				border-top: 1px solid black;
				border-bottom: 1px solid black;
				border-right: 1px solid black;"> 
				<b>' . number_format($UsdValue, 2) . '</b> </td>
				
				
				</tr>';


	//$showDiscount=true;
	if ($showDiscount) {
		$content .= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" > <B>
				LESS TRADE DISCOUNT ' . $DiscountPerc . '%</B> </td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" > <B>
				' . number_format($DiscountAmount, 2) . '</B> </td>
				</tr><tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;border-right: 1px solid black;" > <B>
				TOTAL AMOUNT:</B> </td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;" > <B>
				' . number_format($InvValueUSD, 2) . '</B> </td>
				</tr>';
	}

	//Static DFL # Guru Prasad Dont want Display
	$showIgst = false;
	if ($showIgst) {
		$content .= '	<tr> 
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" > <B>
				INVOICE VALUE IN R.S@' . $ExchangeRate . '  </B> </td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" > <B>
				' . number_format($InvValueInr, 2) . '</B> </td>
				</tr>
				<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;border-bottom: 1px solid black;" > <B>
				IGST@' . $IgstPerc . ' % </B> </td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;" > <B>
				' . number_format($IgstValue, 2) . '</B> </td>
				</tr>';
	}

	$content .= '</table>';


	return $content;
}


function getFooterTable($iconn, $ID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $AryData, $style, $DocType, $AdminConfigDetails;
	$content = $style;


	$size = 45;
	$size1 = 55;

	// echo $ID; exit;


	$AryPriceData = getBreakUpData($iconn, $RowVariety['PoGradeID'], $ID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];
	$GlazePerc = $AryPriceData['GlazePerc'];
	$ProductWeightWOGLbs = $AryPriceData['ProductWeightWOGLbs'];
	$ProductWeightWOGKgs = $AryPriceData['ProductWeightWOGKgs'];
	$WtPerCtnTxt = $AryPriceData['WtPerCtnTxt'];


	//	print_r($AryPriceData); exit;


	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];
	$TotalGrossWtKgs = $AryData['TotalGrossWtKgs'];
	$UnitCurrency = $AryData['UnitCurrency'];

	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$CIDescriptionspl = $AryData['CIDescriptionspl'];
	$CurrenySymbol = $AryData['CurrenySymbol'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];
	$ShowBank = $AryData['ShowBank'];
	$ShowCWiseBankDetails = $AryData['ShowCWiseBankDetails'];
	$ShowCWiseProductSpecification = $AryData['ShowCWiseProductSpecification'];
	$ShowCWiseSpecification = $AryData['ShowCWiseSpecification'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$ShowCWiseBuyer = $AryData['ShowCWiseBuyer'];
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

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$showProFEI = $AryData['showProFEI'];
	$ShowBank = $AryData['ShowBank'];
	$ShowInvoicevalue = $AryData['ShowInvoicevalue'];
	$ShowFrightCharge = $AryData['ShowFrightCharge'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$ComDescriptionSpl = $AryData['ComInvDescription'];
	$ShowCWiseWeightPerctText = $AryData['ShowCWiseWeightPerctText'];
	$ShowCWiseComSplNote = $AryData['ShowCWiseComSplNote'];
	$ShowCWiseComDesNote = $AryData['ShowCWiseComDesNote'];





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


	$BILL_WORD = numberTowords($UsdValue);


	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];

	$AryPaymentData = getPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails);

	//print_r($AryPaymentData); exit;
	$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
	$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
	$DutiesPrice = $AryPaymentData['DutiesPrice'];
	$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
	$AntiDumpingDuty = round($AryPaymentData['AntiDumpingDuty'], 2);
	$MerchantDuty = round($AryPaymentData['MerchantDuty'], 2);
	$HarborDuty = round($AryPaymentData['HarborDuty'], 2);
	$FOBValueINR = round($AryPaymentData['FOBValueINR']);
	$InvValueUSD = $AryPaymentData['InvValueUSD'];
	$ISFStatic = $AryPaymentData['ISFStatic'];
	$AntiDumpingDutyPerc = $AryPaymentData['AntiDumpingDutyPerc'];
	$HarbourMaintenanceFeePerc = $AryPaymentData['HarbourMaintenanceFeePerc'];
	$CountervailingDutyPerc = $AryPaymentData['CountervailingDutyPerc'];
	$MerchandiseProcessFeePerc = $AryPaymentData['MerchandiseProcessFeePerc'];
	$ClearanceCharge = $AryPaymentData['ClearanceCharge'];
	$ClearanceChargePerc = $AryPaymentData['ClearanceChargePerc'];
	$CountervailingDuty = round($AryPaymentData['CountervailingDuty'], 2);

	$ReciprocalTariffsFee = round($AryPaymentData['ReciprocalTariffsFee'], 2);
	$ReciprocalTariffsPerc = $AryPaymentData['ReciprocalTariffsPerc'];


	$content .= $style;
	$content .= '
			<table border="1" cellspacing="0" cellpadding="2">
			
			<tr>
			<td width="58.5%">
			
			<table border="0" cellspacing="0" cellpadding="0">';
	$ShowInvoicevalue = true;
	// echo 	$ShowFrightCharge; exit;
	if ($ShowFrightCharge) {

		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> INVOICE VALUE   </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ ' . number_format($InvValueUSD, 2) . '</b> </td>
			</tr>';

		$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> LESS OCEAN FRIEGHT   </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ <u>' . number_format($FrieghtCharge, 2) . '</u></b> </td>
			</tr>';
		if ($ClearanceChargePerc != 0) {
			$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> CLEARANCE CHARGES @ ' . $ClearanceChargePerc . '%) </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ <u>' . number_format($ClearanceCharge, 2) . '</u></b> </td>
			</tr>';
		}

		if ($ISFStatic) {
			if ($MerchandiseProcessFeePerc != 0) {
				$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> (LESS : MERCHANDISE PROCESS FEE @ ' . $MerchandiseProcessFeePerc . '%) </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ ' . number_format($MerchantDuty, 2) . '</b> </td>
			</tr>';
			}
		}
		$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> SUB TOTAL </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ ' . number_format($InvValueUSDWithOutDuties, 2) . '</b> </td>
			</tr>';
		$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> (LESS : ANTI-DUMPING DUTY @ ' . $AntiDumpingDutyPerc . '%)  </td>
			<td align="left" width="30%" >  <b>: $ ' . number_format($AntiDumpingDuty, 2) . '</b> </td>
			</tr>';
		//uma cvd calculation included //23-04-2024

		if ($CountervailingDutyPerc != 0) {
			$content .= '
				<tr>
				<td  width="1%"></td>
				<td align="left" width="70%"> (LESS : COUNTER VAILING DUTY @ ' . $CountervailingDutyPerc . '%)</td>
				<td align="left" width="30%" > <b> : $ ' . number_format($CountervailingDuty, 2) . '</b></td>
				</tr>';
		}
		//uma cvd calculation included //23-04-2024
		if (!$ISFStatic) {
			if ($MerchandiseProcessFeePerc != 0) {
				$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> (LESS : MERCHANDISE PROCESS FEE @ ' . $MerchandiseProcessFeePerc . '%) </td>
			<td align="left" width="30%" >  <b>: $ ' . number_format($MerchantDuty, 2) . '</b> </td>
			</tr>';
			}
		}
		if ($HarbourMaintenanceFeePerc != 0) {
			$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> (LESS : HARBOR MAINTENANCE FEE @ ' . $HarbourMaintenanceFeePerc . '%) </td>
			<td align="left" width="30%" >  <b>: $ ' . number_format($HarborDuty, 2) . '</b> </td>
			</tr>';
		}

		if ($ReciprocalTariffsPerc != 0) {
			$content .= '
						<tr>
						<td  width="1%"></td>
						<td align="left" width="70%"> (LESS : RECIPROCAL TARIFFS @ ' . $ReciprocalTariffsPerc . '%) </td>
						<td align="left" width="30%" >  <b>: $ ' . number_format($ReciprocalTariffsFee, 2) . '</b></td>
						</tr>';
		}
		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> TOTAL DUTY </td>
			<td align="left" width="30%" >  <b>: $ <u>' . number_format($DutiesPrice, 2) . '</u></b> </td>
			</tr>';

		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> FOB VALUE  IN ' . $UnitCurrency . '   </td>
			<td align="left" width="30%" >  <b>: $ ' . number_format($FOBValueUSD, 2) . '</b> </td>
			</tr>';
	} else if ($ShowInvoicevalue) {

		//echo "hii"; exit;
		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="35%"> INVOICE VALUE ' . $UnitCurrency . ' ' . $CurrenySymbol . ' </td>
			<td align="left" width="15%" >  <b>: </b> </td>
			<td align="left" width="60%" >  <b> ' . number_format($InvValueUSD, 2) . '</b> </td>
			</tr>';

		/*$content.='<tr><td  width="1%"></td>
																																					 <td align="left" width="35%"> FOB VALUE innn IN US $  </td>
																																					 <!--<td align="left" width="15%" >  <b>: '.number_format($InvValueUSDWithOutDuties,2).'</b> </td>-->
																																					 <td align="left" width="15%" >  <b>: </b> </td>
																																					 <td align="left" width="60%" ><b> '.number_format($InvValueUSDWithOutDuties,2).'</b>  </td>

																																					 <!--<td align="left" width="60%" >  
																																					 <b> FOB VALUE  :  '.round($InvValueINRWithOutDuties).'.00</b> </td>-->
																																					 </tr>'; */
	} else {
		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="35%"> FOB VALUE IN ' . $CurrencyShortCode . '  </td>
			<td align="left" width="15%" >  <b>: </b> </td>
			<td align="left" width="60%" > <b> ' . number_format($InvValueUSDWithOutDuties, 2) . '</b> </td>

			<!--<td align="left" width="50%" >  
			<b> FOB VALUE  :  ' . round($InvValueINRWithOutDuties) . '.00</b> 
			</td>-->
			</tr>';
	}



	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];

	if ($QtyUnit == 'KGS') {
		$GrossWeightKgs = $TotalGrossWtKgs;
		$GrossWeightLbs = $TotalGrossWtKgs * 2.204;
	} else {
		$GrossWeightKgs = $TotalGrossWtKgs * 0.454;
		$GrossWeightLbs = $TotalGrossWtKgs;
	}





	$content .= '</table></td>';

	$content .= '<td width="41.5%">
			
			<table border="1" cellspacing="0" cellpadding="2">';

	if ($QtyUnit == 'KGS') {
		$content .= '<tr>
			<td align="left" width="30%"> <b>GROSS WEIGHT:</b>  </td>
			<td align="left" width="35%" >  <b>' . number_format($GrossWeightKgs, 3) . ' KGS</b> </td>
			</tr>';
		$content .= '<tr>
			<td align="left" width="30%"> <b>NET WEIGHT: </b> </td>
			<td align="left" width="35%" >  <b>' . number_format($NetWgtKgs, 3) . ' KGS</b> </td>
			</tr>';

		if ($GlazePerc != 0) {
			$content .= '<tr>
			<td align="left" width="30%"> <b>NET WEIGHT WITHOUT GLAZE </b> </td>
			<td align="left" width="35%" >  <b> ' . number_format($ProductWeightWOGKgs, 3) . ' KGS</b> </td>
			</tr>';
		}
	} else {
		$content .= '<tr>
			<td align="left" width="30%"> GROSS WEIGHT  </td>
			<td align="left" width="35%" >  <b>: ' . number_format($GrossWeightLbs, 3) . ' LBS</b> </td>
			<td align="left" width="35%" >  <b> ' . number_format($GrossWeightKgs, 3) . ' KGS</b> </td>
			
			</tr>';

		$content .= '<tr>
			<td align="left" width="30%"> NET WEIGHT  </td>
			<td align="left" width="35%" >  <b>: ' . number_format($NetWgtLbs, 3) . ' LBS</b> </td>
			<td align="left" width="35%" >  <b> ' . number_format($NetWgtKgs, 3) . ' KGS</b> </td>
			
			</tr>';

		if ($GlazePerc != 0) {
			$content .= '<tr>
			<td align="left" width="30%"> <b>NET WEIGHT WITHOUT GLAZE</b>  </td>
			<td align="left" width="35%" >  <b> ' . number_format($ProductWeightWOGLbs, 3) . ' LBS</b> </td>
			</tr>';
		}
	}



	$content .= '</table>';



	$content .= '</td></tr></table>';








	$content .= '<table border="1" cellspacing="0" cellpadding="5"><tr>';

	$formated_date = date("Y-m-d", strtotime($InvDate));
	$AryDescription = getInvoiceDescription($iconn, $formated_date);
	$CommercialNote = $AryDescription['CommercialNote'];

	$ArrayProductDescription = getProductSpec($iconn, $ID);



	$content .= '
				<table border="1" cellspacing="0" cellpadding="2">
				<tr>  
           		<td  align="LEFT"  width="100%"> <b> AMOUNT CHARGABLE (IN ' . $UnitCurrency . '): 
				(' . $BILL_WORD . ' ONLY)</b> <BR> 
				 ';
	if ($ShowCWiseWeightPerctText) {
		$content .= '' . nl2br($WtPerCtnTxt) . '<br>';
	}
	if ($ShowCWiseProductSpecification) {
		//$content.=getProductSpec($iconn,$ID);
		$content .= $ArrayProductDescription['Desc'];
	}

	if ($ShowCWiseSpecification) {
		$content .= $ArrayProductDescription['Speciesdesc'];
	}



	$content .= '
				
				 
				</td> 
				</tr>
				</table>';

	//$ShowBank=true;
	if ($ShowBank && $ShowCWiseBankDetails) {
		$content .= '
				<table border="1" cellspacing="0" cellpadding="2">
				<tr><td style="margin-left: 1em;">
				<b>BANK DETAILS  </b>' . $BankDetails . '';
		$content .= '<br><b>Negotiating BANK  </b>' . $NegotiatingBank;
		$content .= '<br><b>ROUTING THROUGH   </b>' . $RoutingThrough . '
				</td></tr></table>';
	}







	$content .= '</tr>';
	if ($CommercialNote != '' && $ShowCWiseComDesNote) {
		$content .= '<tr>  
           		<th  align="LEFT"  width="100%">';
		$ComDescriptionSpl = str_replace(' ', '&nbsp;', $ComDescriptionSpl);
		//if($CommercialNote==''){
		if ($CommercialNote != '' && $ShowCWiseComDesNote) {
			//echo $CommercialNote; 
			//echo $ComDescriptionSpl; exit;
			$content .= ' 
				 ' . nl2br($CommercialNote) . '<br> ';
		}
		if ($ComDescriptionSpl != '' && $ShowCWiseComSplNote) {
			$content .= ' <br>' . nl2br($ComDescriptionSpl) . '<br> ';
		}
		$content .= '
				</th> 
				</tr>';

	}




	$content .= '<tr  >
				<th  align="bottom"  width="70%"> <br><br><br><br><B><U> DECLARATION:</U></B> WE HEREBY DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT </th>
           
		   <th  align="center"  width="30%">  
				<B> for ' . $AuthSign . ',</B><BR><BR><BR>
				 
							
				
				<p STYLE="text-align:center" > <b>AUTHORISED SIGNATORY <br> Signature & Date </b></p> </th>
</tr>


				
				
				</table>';

	return $content;
}











function getReportData($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $AryShowConditions, $DocType;

	$ShowCWiseLc = $AryShowConditions['LcNo'];
	$ShowCWiseLcDate = $AryShowConditions['LcDate'];
	$ShowCWiseLcIssuedBy = $AryShowConditions['LcIssuedBy'];
	$ShowCWiseBankDetails = $AryShowConditions['BankDetails'];
	$ShowCWiseUsfda = $AryShowConditions['Usfda'];
	$ShowCWiseComSplNote = $AryShowConditions['ComSplNote'];
	$ShowCWiseComDesNote = $AryShowConditions['ComDesNote'];
	$ShowCWiseProductSpecification = $AryShowConditions['ProductSpecification'];
	$ShowCWiseSpecification = $AryShowConditions['Species'];
	$ShowCWiseBuyer = $AryShowConditions['Buyer'];
	$ShowBLNo = $AryShowConditions['BLNo'];
	$ShowBLDate = $AryShowConditions['BLDate'];
	$ShowSBNo = $AryShowConditions['SBNo'];
	$ShowSBDate = $AryShowConditions['SBDate'];


	$CustomInvoiceSql = "SELECT *,SCD.ComShippingTermExt,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
        SC.name as PortofDischargeCountry,
		        SCD.LcNo as CLcNo,
        SCD.LcDate as CLcDate,
        SCD.ShowBLNo,
        SCD.ShowBLDate,
        SCD.IssuedBy as CIssuedBy,SP.Name AS LoadingPortName,
		SCD.ComDescriptionSpl as CComDescriptionSpl,
		SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading ,
		SCV.ContainerNo	as ContainerNo ,SPCF.Name as FinalDestination,SCV.ByRoad,SCV.CustomsInvNo
		FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID 
	    left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
		left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SL on SL.id=SP.Country
		 left JOIN ScrmISFDetails as SID on SID.PIID=SCV.PiID
		left JOIN ScrmCommercialData as SCD on SCD.PoID=SCV.PiID
		 left JOIN ScrmPaymentTerms as SPT on SCD.ComPaymentTerms=SPT.ID
		 left JOIN ScrmCurrencyMaster as SCM on SCM.CurrencyCode=SSD.UnitCurrency
		 left JOIN ScrmShippingBillDetails as SSBD on SSBD.InvID=SCV.PiID

		WHERE SCV.PiID='$PoID'";
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvData = $CustomInvoiceExec->fetch_assoc();
	$IGST = $CustomInvData['IGST'];
	$OverSeasCommission = $CustomInvData['OverSeasCommission'];
	$CInvDate = $CustomInvData['CInvDate'];
	$CInvCode = $CustomInvData['CInvCode'];
	$NoOfDays = $CustomInvData['NoOfDays'];
	$ComInvDescription = $CustomInvData['CComDescriptionSpl'];
	$ComShippingTerms = $CustomInvData['ComShippingTerms'];
	$ComPaymentTerms = $CustomInvData['PaymentName'];

	$TotalGrossWt = $CustomInvData['TotalGrossWt'];
	$VesselNumber = $CustomInvData['OriginatingVessel'] . '<br> ' . $CustomInvData['VoyOrg'];
	//$BLDate=getFormatedDateNA($CustomInvData['BLDate']);
	$BLDate = getFormatedDateDMMY($CustomInvData['BLDate']);
	$SBDate = getFormatedDateNA($CustomInvData['ShippingBillDate']);
	$BLNo = $CustomInvData['BLNo'];
	// $SBNo = $CustomInvData['SBNo'];
	$SBNo = $CustomInvData['ShippingBillNo'];
	$LcNo = $CustomInvData['CLcNo'];
	$LcDate = getFormatedDateNA($CustomInvData['CLcDate']);
	$LcIssuedBy = $CustomInvData['CIssuedBy'];
	$LoadingPortName = $CustomInvData['LoadingPortName'];
	$ByRoad = $CustomInvData['ByRoad'];
	$ShowBLDate = $CustomInvData['ShowBLDate'];
	$ShowBLNo = $CustomInvData['ShowBLNo'];

	if ($CustomInvData['BankID'] == 0) {
		$BankID = $PoDataRow['BankID'];
	} else {
		$BankID = $CustomInvData['BankID'];
	}



	$SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 
FromTOPID='$LoadingPortName' and PIID='$PoID'";
	//exit;
	$StmtOrgVessel = $iconn->query($SqlOrgVessel);
	$RowOrgVessel = $StmtOrgVessel->fetch_assoc();

	$OrgVesselName = $RowOrgVessel['VesselName'];
	$OrgVOYNO = $RowOrgVessel['VOYNO'];

	if ($NoOfDays == 0) {
		$NoOfDays = '';
	} else {

		$NoOfDays = $NoOfDays . ' DAYS FROM BL';
	}

	$SqlMcsTotal = "SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
	$StmtMcsTotal = $iconn->query($SqlMcsTotal);
	$RowMcs = $StmtMcsTotal->fetch_assoc();
	$TotalMcs = $RowMcs['Mcs'];




	$SqlScrmPoData = "SELECT *,SPD.Status as PoStatus,ALD.SignURL,
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
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 left JOIN ScrmCurrencyMaster as SCRM on SCRM.CurrencyCode=SPD.UnitCurrency

 WHERE PoID='$PoID'";


	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ApprovedBy = $PoDataRow['ApprovedBy'];
	$CountryID = $PoDataRow['DestinationCountry'];

	if ($CustomInvData['BankID'] == 0) {
		$BankID = $PoDataRow['BankID'];
	} else {
		$BankID = $CustomInvData['BankID'];
	}

	$SqlLogin = "SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
	$StmtLogin = $iconn->query($SqlLogin);
	$RowLogin = $StmtLogin->fetch_assoc();
	$SignURL = $RowLogin['SignURL'];

	$ExporterData = getExporterDetails($ExpID);
	//$ProcessorData=getExporterDetails($ProcessorID);
	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);

	if ($ByRoad != '') {
		$FinalDestination = $ByRoad;
	} else {
		$FinalDestination = $CustomInvData['FinalDestination'];
	}

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
		$AryData['ShowInvoicevalue'] = true;	//uma
	} else {
		$AryData['ShowInvoicevalue'] = true;
	}


	//if($PoDataRow['DestinationCountry']==26){
	if ($PoDataRow['DestinationCountry'] != 26 && $PoDataRow['ShippingTerms'] != 'FOB') {  //uma
		$AryData['ShowDeliveryTerms'] = true;
	} else {
		$AryData['ShowDeliveryTerms'] = false;
	}


	//if($ExpID!=5 && $PoDataRow['DestinationCountry']!=26){
	/*if($ShowCWiseUsfda){
																			$AryData['showExpUsFda']=true;	
																			}
																			else{
																			$AryData['showExpUsFda']=false;	
																			}*/

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

	if ($PoDataRow['ImporterID'] != 0) {
		$AryData['showImporter'] = true;
	} else {
		$AryData['showImporter'] = false;
	}

	if ($CustomInvData['ShowBank'] == 1) {
		$AryData['ShowBank'] = true;
	} else {
		$AryData['ShowBank'] = false;
	}

	if ($ShowBLNo == 1) {
		$AryData['ShowBLNo'] = true;
	} else {
		$AryData['ShowBLNo'] = false;
	}

	if ($ShowBLDate == 1) {
		$AryData['ShowBLDate'] = true;
	} else {
		$AryData['ShowBLDate'] = false;
	}
	if ($ShowSBDate == 1 && $SBDate != 'NA') {
		$AryData['ShowSBDate'] = true;
	} else {
		$AryData['ShowSBDate'] = false;
	}
	if ($ShowSBNo == 1 && $SBNo != '') {
		$AryData['ShowSBNo'] = true;
	} else {
		$AryData['ShowSBNo'] = false;
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




	if ($PoDataRow['PoNo'] == '') {
		$AryData['PoDate'] = '';
	} else {
		$AryData['PoDate'] = "  & Dated :" . getFormatedDate($PoDataRow['PoDate']);
	}
	if ($IGST != 'DISCOUNT') {
		$AryData['ShowNoofDays'] = true;
	} else {
		$AryData['ShowNoofDays'] = false;
	}
	$AryData['ComShippingTermExt'] = $CustomInvData['ComShippingTermExt'];

	if ($ComShippingTerms != '') {
		$ShippingTerms = $CustomInvData['ComShippingTerms'];
	} else {
		$ShippingTerms = $PoDataRow['ShippingTerms'];
	}

	if ($ComPaymentTerms != '') {
		$PaymentTerms = $CustomInvData['PaymentName'];
	} else {
		$PaymentTerms = $PoDataRow['PaymentName'];
	}

	// $CustomInvData['ComShippingTermExt'];

	/* Return Data*/
	$AryData['ComShippingTermExt'] = $CustomInvData['ComShippingTermExt'];
	$AryData['TotalGrossWtKgs'] = $TotalGrossWt;
	$AryData['PoStatus'] = $PoDataRow['PoStatus'];
	$AryData['ConsigneeCountry'] = $PoDataRow['ConsigneeCountry'];
	$AryData['DestinationCountryName'] = $PoDataRow['DestinationCountryName'];
	$AryData['ApprovedBy'] = $ApprovedBy;
	//$AryData['PaymentTerms']=$PaymentTerms; 
	$AryData['DeliveryTerms'] = $DeliveryTerms;
	$AryData['TotalMcs'] = $TotalMcs;
	$AryData['ExporterID'] = $ExpID;
	$AryData['ComInvDescription'] = $ComInvDescription;

	$AryData['ShowCWiseLc'] = $ShowCWiseLc;
	$AryData['ShowCWiseLcDate'] = $ShowCWiseLcDate;
	$AryData['ShowCWiseLcIssuedBy'] = $ShowCWiseLcIssuedBy;
	$AryData['ShowCWiseWeightPerctText'] = $AryShowConditions['WeightPerctText'];
	$AryData['ShowCWiseNotifyPrty'] = $AryShowConditions['NotifyPrty'];
	$AryData['ShowCWiseBankDetails'] = $ShowCWiseBankDetails;
	$AryData['ShowCWiseProductSpecification'] = $ShowCWiseProductSpecification;
	$AryData['ShowCWiseSpecification'] = $ShowCWiseSpecification;
	$AryData['ShowCWiseComDesNote'] = $ShowCWiseComDesNote;
	$AryData['ShowCWiseComSplNote'] = $ShowCWiseComSplNote;
	$AryData['ShowCWiseBuyer'] = $ShowCWiseBuyer;
	$AryData['ShowBLNo'] = $ShowBLNo;
	$AryData['ShowBLDate'] = $ShowBLDate;
	$AryData['OrgVesselName'] = $OrgVesselName;
	$AryData['OrgVOYNO'] = $OrgVOYNO;
	$AryData['ByRoad'] = $ByRoad;
	$AryData['ShowCWiseUsfda'] = $AryShowConditions['Usfda'];
	$AryData['ShowCWiseApprovalNo'] = $AryShowConditions['ApprovalNo'];
	// $AryData['ShowSBNo'] = $ShowSBNo;
	// $AryData['ShowSBDate'] = $ShowSBDate;

	if ($ShowCWiseLc && $ShowCWiseLcDate && $ShowCWiseLcIssuedBy) {
		if ($LcNo == '') {
			$AryData['PaymentTerms'] = $PaymentTerms;
		} else {
			$AryData['PaymentTerms'] = $PaymentTerms . ' LC NO.' . $LcNo . ', DATED ' . $LcDate . ',' . $LcIssuedBy;
		}
	} else {
		$AryData['PaymentTerms'] = $PaymentTerms;
	}


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
	$AryData['ReferenceNo'] = $PoDataRow['ReferenceNo'];
	$AryData['BuyerID'] = $PoDataRow['BuyerID'];
	$AryData['ConsigneeName'] = $PoDataRow['ConsigneeName'];
	$AryData['ConsigneeAddress'] = $PoDataRow['ConsigneeAddress'];
	$AryData['BuyerName'] = $PoDataRow['BuyerName'];
	$AryData['BuyerAddress'] = $PoDataRow['BuyerAddress'];



	$AryData['PortOfDischarge'] = strtoupper($CustomInvData['PortofDischarge']) . ' ' . strtoupper($CustomInvData['CountryCode']) . ', ' . $CustomInvData['PortofDischargeCountry'];
	$AryData['PortOfDischarge'] = strtoupper($CustomInvData['PortofDischarge']) . ', ' . $CustomInvData['PortofDischargeCountry'];

	$AryData['PlaceOfReceiptBy'] = $CustomInvData['PlaceOfRecipt'];
	$AryData['CurrenySymbol'] = $CustomInvData['CurrenySymbol'];
	$AryData['PortOfLoading'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	//$AryData['FinalDestination']=strtoupper($CustomInvData['FinalDestination']);
	$AryData['FinalDestination'] = strtoupper($FinalDestination);
	$AryData['VesselNumber'] = $VesselNumber;

	//$AryData['InvNo']=getInvoiceNumber($iconn,$ExpID,$PoID) ;
	$AryData['InvNo'] = $CustomInvData['CustomsInvNo'];
	$AryData['InvDate'] = getFormatedDate($CInvDate);
	$AryData['PoNo'] = $PoDataRow['PoNo'];



	$AryData['ProcessorName'] = $ProcessorData['ExpName'];
	$AryData['ProcessorAddress'] = $ProcessorData['ExpAddress'];
	//$AryData['ProcessorAddress']=$ProcessorData['PackerAddress'];
	$AryData['ProcessorUsfdaNo'] = $ProcessorData['ExpUsfdaNo'];
	$AryData['ProcessorFEI'] = $ProcessorData['ExpFeiNo'];
	$AryData['ProcessorApprovalNo'] = $ProcessorData['ExpApprovalNo'];
	$AryData['ProcessorIeCustomsNo'] = $ProcessorData['ExpIeCustomsNo'];


	$AryData['ShippingTerms'] = $ShippingTerms;

	$AryData['DestinationCountry'] = $PoDataRow['name'];
	$AryData['DeliveryTerms'] = $PoDataRow['DeliveryTerms'];
	$AryData['QtyUnit'] = $PoDataRow['QtyUnit'];
	$AryData['ShipByDate'] = $PoDataRow['ShipByDate'];
	$AryData['SignURL'] = $SignURL;
	$AryData['PreparedBy'] = $PoDataRow['PreparedBy'];
	$AryData['BankID'] = $BankID;
	//$AryData['BankID']=$PoDataRow['BankID'];
	$AryData['IGST'] = $IGST;
	$AryData['CInvCode'] = $CInvCode;
	$AryData['NoOfDays'] = $NoOfDays;
	$AryData['UnitCurrency'] = $PoDataRow['UnitCurrency'];
	$AryData['CurrencyShortCode'] = $PoDataRow['CurrencyShortCode'];


	$AryData['NotifyPartyName'] = $PoDataRow['NotifyPartyName'];
	$AryData['NPartyAddress'] = $PoDataRow['NPartyAddress'];
	$AryData['NPartyContactNumber'] = $PoDataRow['NPartyContactNumber'];
	$AryData['ImporterName'] = $PoDataRow['ImporterName'];
	$AryData['ImporterAddress'] = $PoDataRow['ImporterAddress'];


	$AryData['ContainerNo'] = $CustomInvData['ContainerNo'];
	$AryData['CIDescriptionspl'] = $CustomInvData['CIDescriptionspl'];
	// $AryData['SealNo']=$CustomInvData['SealNo'];
	$AryData['SealNo'] = $CustomInvData['LinerSealNo'];
	$AryData['IRNNumber'] = $CustomInvData['IRNNumber'];
	$AryData['BLDate'] = $BLDate;
	//$BLDate=getFormatedDateDMMY($AryData['BLDate']);
	$AryData['SBDate'] = $SBDate;
	$AryData['BLNo'] = $BLNo;
	$AryData['SBNo'] = $SBNo;


	return $AryData;
}


function getBreakUpData($iconn, $GradeID, $PiID)
{
	global $PType, $DocType;

	if ($PiID == '') {
		$Qry = "WHERE PGD.ID in ('$GradeID')";
		$PriceQry = 'Price';
	} else {
		$Qry = "WHERE InvId='$PiID'";
		$PriceQry = 'sum(Price) as Price';
	}

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
				sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as GlazeWeightKgs,
				 sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as GlazeWeightLbs";

	$SqlTotal = "SELECT sum(Mcs) as MCS,InvId,GlazePerc,  $sqlExt  ,
			$PriceQry,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";


	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];
	$GlazePerc = $RowTotal['GlazePerc'];


	if ($PiID != '') {
		$SqlWtPerCtn = "SELECT 
			round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,
			round(sum(GrossWt)/Sum(Mcs),2) as GrossWt FROM `ScrmPoGradeData` as SPGD
				left JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID 
				WHERE InvId='$InvId' GROUP by PackingStyleID;";
		$StmtWtPerCtn = $iconn->query($SqlWtPerCtn);
		$RowWtPerCtn = $StmtWtPerCtn->fetch_assoc();
		$NetWtPerCtn = $RowWtPerCtn['NetWtLbs'];
		$GrossWtPerCtn = $RowWtPerCtn['GrossWt'];
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
		$Qty = $RowTotal['NetWgtLbs'];
		$UsdValue = $RowTotal['PriceLbs'];
		$UnitPrice = round(($UsdValue / $Qty), 7);
	}
	if ($QtyUnit == 'KGS') {
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
		$GlazeWeight = $RowTotal['GlazeWeightKgs'];
		//echo $AryPriceData['ProductWeightWOG']=$NetWgtKgs-$GlazeWeightKgs;exit;


	} else {
		$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $RowTotal['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
		$GlazeWeight = $RowTotal['GlazeWeightLbs'];
		//$AryPriceData['ProductWeightWOG']=$NetWgtLbs-$GlazeWeightLbs;
	}


	/*if($QtyUnit=='KGS'){
																				   //$AryPriceData['Qty']=$Qty;
																				   $AryPriceData['Qty']=$RowTotal['NetWgtKgs'];
																			   }
																			   
																			   else{
																				   $AryPriceData['Qty']=$RowTotal['NetWgtLbs']; 
																			   }*/

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];

	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['UnitPrice'] = $UnitPrice;
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['GlazePerc'] = $GlazePerc;


	/*$AryPriceData['NetWgtKgs']=$RowTotal['NetWgtKgs'];
																			   $AryPriceData['NetWgtLbs']=$RowTotal['NetWgtLbs'];
																			   $AryPriceData['GrossWeightKgs']=$RowTotal['GrossWeightKgs'];
																			   $AryPriceData['GrossWeightLbs']=$RowTotal['GrossWeightLbs'];*/

	$AryPriceData['NetWgtKgs'] = $NetWgtKgs;
	$AryPriceData['NetWgtLbs'] = $NetWgtLbs;
	$AryPriceData['GrossWeightKgs'] = $GrossWeightKgs;
	$AryPriceData['GrossWeightLbs'] = $GrossWeightLbs;
	$GlazeWeightKgs = $RowTotal['GlazeWeightKgs'];
	$GlazeWeightLbs = $RowTotal['GlazeWeightLbs'];
	$AryPriceData['ProductWeightWOGKgs'] = $NetWgtKgs - $GlazeWeightKgs;
	$AryPriceData['ProductWeightWOGLbs'] = $NetWgtLbs - $GlazeWeightLbs;
	$AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;


	return $AryPriceData;
}


function getBankDetails($iconn, $BankID)
{

	$SqlBank = "SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$AryBankData = $StmtBank->fetch_assoc();

	return $AryBankData;
}

function getProductSpec($iconn, $PoID)
{

	$SqlSpecies = "SELECT GROUP_CONCAT(DISTINCT(SpeciesName)) as SpeciesName FROM `ScrmPoGradeData` as SPDG 
LEFT JOIN ScrmPIVarietyList as SPVL ON SPDG.VarietyID=SPVL.VarietyID 
left join ScrmSpeciesMaster as SSM on SSM.ShortCode=SPVL.Species WHERE InvId='$PoID'";
	$StmtSpecies = $iconn->query($SqlSpecies);
	$AryStmtSpecies = $StmtSpecies->fetch_assoc();
	$Desc = '<BR> THIS PRODUCT HAS NOT BEEN REFUSED BY ANY COUNTRY PRIOR TO SHIPMENT TO THE U.S.A.<br>';
	$Speciesdesc = 'SPECIES : ' . $AryStmtSpecies['SpeciesName'];
	$ArrayDescription['Desc'] = $Desc;
	$ArrayDescription['Speciesdesc'] = $Speciesdesc;

	return $ArrayDescription;
}
