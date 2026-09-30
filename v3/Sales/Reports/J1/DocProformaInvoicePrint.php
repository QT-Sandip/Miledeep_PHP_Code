<?php
$PoID = $_GET['id'];
//ini_set( 'display_errors', 1 );
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");



$ID = $_GET['id'];
$Type = $_GET['Type'];



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

$style = '<style>
th {
    font-size: 12px;
	padding : 10px;
}
td {
    font-size: 12px;
	padding : 8px;
}
p {
	//line-height: 0.1;
}
</style>
';

$SqlStatus = "SELECT * FROM ScrmPIData  WHERE PoID='$PoID'";

//echo $SqlStatus; exit;
$StmtStatus = $iconn->query($SqlStatus);
$PoStatus = $StmtStatus->fetch_assoc();
$Status = $PoStatus['Status'];
$DocType = 'PI';
$CountryID = $PoStatus['DestinationCountry'];
$BuyerID = $PoStatus['ConsigneeID'];
$InvNumFull = $PoStatus['InvNumFull'];
$InvDate = date("d-m-Y", strtotime($PoStatus['InvDate']));

$AryLables = getInvoiceLables($DocType, $CountryID, $BuyerID);
$AryShowConditions = getInvoiceShowConditions($DocType, $CountryID, $BuyerID);


class MYPDF extends TCPDF
{

	public function Header()
	{

		global $Status;

		if ($Status == 0) {
			$StatusTerm = "(Approval Pending)";
		} else if ($Status == 2) {
			$StatusTerm = "(Cancelled)";
		} else if ($Status == 4) {
			$StatusTerm = "(Rejected)";
		}
		$StatusTerm = '';
		global $Header;
		$image_file = 'images/ds_logo.jpg';
		$this->SetFont('helvetica', 'B', 8);
		$this->Cell(110, 0, 'PROFORMA INVOICE ' . $Header, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetTextColor(255, 0, 0);
		$this->SetFont('helvetica', '', 8);
		$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
	}

	public function Footer()
	{
		global $InvNumFull, $InvDate;
		$this->SetFont('helvetica', 'I', 8);
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . '(' . $InvNumFull . ' / Dt.' . $InvDate . ')', 0, false, 'C', 0, '', 0, false, 'T', 'M');

		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Proforma Invoice: ' . $Header);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(5);
$pdf->SetMargins(10, 10, 10, 5);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();


$AryData = getReportData($iconn, $ID);


//$table_data=getProformaDetails($iconn,$ID);
$HeaderTable = getHeaderTable($iconn, $ID);
$BreakUpTable = getBreakTable($iconn, $ID);
$FooterTable = getFooterTable($iconn, $ID);



$pdf->writeHTML($HeaderTable . $BreakUpTable . $FooterTable);
/*$pdf->writeHTML($HeaderTable);
	$pdf->writeHTML($BreakUpTable);
	$pdf->writeHTML($FooterTable);*/
//$pdf->writeHTML($table_data);
$pdf->SetFont('helvetica', '', 6);
//echo $pdf->getAliasNbPages(); exit;

//$file_name=getPIInvoiceNumber($iconn,$ExpID,$PoID); 
$file_name = str_replace('/', '_', $InvNumFull);

$file_name = $Fname . $file_name . '.pdf';
// $file_name="DFL-".$Fname."-".$file_name.'.pdf';



ob_end_clean();
if ($Type == '') {
	$Type = 'I';
} else {
	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '', $file_name);;
	$file_name = $Fname . $file_name;
}


$pdf->Output($file_name, $Type);


function getHeaderTable($iconn, $ID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	global $AryData, $style, $AryLables;

	$content = '';


	$size = 45;
	$size1 = 55;






	$ReferenceNo = $AryData['ReferenceNo'];
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
	$DeliveryTermCode = $AryData['DeliveryTermCode'];

	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$PortofLoading = $AryData['PortofLoading'];
	$DestinationCountry = $AryData['DestinationCountry'];
	//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$ShowCWiseDeliveryTermCode = $AryData['ShowCWiseDeliveryTermCode'];
	$ShowCWiseDeliveryTerms = $AryData['ShowCWiseDeliveryTerms'];

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
	$OrgVesselName = $AryData['OrgVesselName'];





	$content .= $style;
	$content .= '
	 
	 <table border="1" cellspacing="0" cellpadding="0">  
				
				 
				 
				 <tr>

				 
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
								
				}
				
				if($showExpGSTNo){
				$content .= '<BR> GST NO  : '.$ExporterGstNo;
				}*/


	$content .= '</font></th></tr>';






	$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '</font></th>
				</tr>';
	if ($showNotifyParty) {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Notify Party</u></b>
				<br>' . $NotifyPartyName . ',<br>' . nl2br($NPartyAddress) . '
				<!--<br>TEL:' . $NPartyContactNumber . '-->
				
				
				</font></th>
				</tr>';
	}

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
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel  <BR>  
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
				<th  align="LEFT"  ><font size="7" > <b>Invoice No. & Date.
				 ' . $InvNo . ',Dated ' . $InvDate . ' </b></font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Purchase Order No. & Date. 
				<br> ' . $PoNo . $PoDate . ' </b></font></th>
				</tr>';







	$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Reference(s) :
				' . $ReferenceNo . '  </b>
				
				</font></th></tr >';
	if ($showImporter) {

		$content .= '	<tr> 
           		
		
                <th  align="left" width="100%"><font size="7">
				<b><u> Importer of Record:</u></b> <br>  ' . $ImporterName . '
				<br> ' . nl2br($ImporterAddress) . ' </font></th>
				</tr>';
	}
	if ($showBuyer) {

		$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> <b><u>Buyer 
				(if other than consignee) :</b></u><br>' . $BuyerName . ',<br>' . nl2br($BuyerAddress) . '<br></font></th>
				</tr>';
	}


	$content .= '<tr >
				<th  align="LEFT"  ><font size="7" > <u><b>' . $AryLables['Manufacturer'] . '</b></u><br> ' . $ProcessorName . '
				<br> ' . nl2br($ProcessorAddress) . '<br>
				' . $AryLables['ApprovalNo'] . '' . $ProcessorApprovalNo . '';




	if ($ShowCWiseUsfda) {
		$content .= '<br><u>BIO TERRORISM /USFDA REGISTRATION NO  : ' . $ProcessorUsfdaNo . '</u>';
	}

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
				<tr> ';


	$content .= '<th border="1"  align="left" width="50%"><font size="7"> Terms of Delivery  ';
	if ($ShowCWiseDeliveryTerms) {
		$content .= '	<br><b align="center">  ' . $DeliveryTerms . '</b>';
	}
	if ($ShowCWiseDeliveryTermCode) {
		$content .= '	<b><br align="center">' . $DeliveryTermCode . '</b>';
	}
	$content .= '</font></th>';

	$content .= '<th  align="LEFT"  width="50%" ><font size="7"><b> <u>Terms of Payment:</u>
				</b><br><BR> ' . $PaymentTerms . ' <br>
				<!--
				 SHIPMENT TERM : ' . $ShippingTerms . ' 
				 ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . ' -->';




	$content .= '</font> </th>
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
	global $AryData, $style, $AryLables;
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

	$PortOfDischarge = strtoupper($AryData['PortOfDischarge']);
	$FinalDestination = strtoupper($AryData['FinalDestination']);
	//$ShowCertification=$AryData['ShowCertification'];
	$Certification = $AryData['Certification'];

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
	$ShowCWiseBrand = $AryData['ShowCWiseBrand'];
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

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$showProFEI = $AryData['showProFEI'];






	$content = '
				<table border="1" cellspacing="0" cellpadding="2">
				<tr>
				<th  align="LEFT" width="58.5%" ><font size="7">  Description of Goods <b align="center">' . $SealNo . '</b></font></th>
                <th  align="center" width="13.5%" >
				<font size="7">Quantity in <BR>' . $QtyUnit . ' ' . $AryLables['UnitInBreakUp'] . ' </font></th>
                <th  align="center" width="13.5%" ><font size="7">Rate per <br>' . $QtyUnit . '' . $AryLables['UnitInBreakUp'] . '/' . $ShippingTerms . '<b>' . strtoupper($w) . '</b></font></th>
                <th  align="center" width="14.5%" ><font size="7">Amount in ' . $UnitCurrency . ' ' . '<b> ' . strtoupper($w) . '</b></font></th>
                </tr>';


	//$content.='<tr>';

	$FontSize = "7px";


	/*GROUP_CONCAT(BuyerItemCode) BuyerItemCode,
			GROUP_CONCAT(Description) 
			
			
*/
	$CurrentCount = 0;
	$SqlVarietyMcs = "
			SELECT sum(McS) As VarietyMcs, BuyerItemCode, Description as Description,Variety,SBM.PrintStatus,
			PGD.Treatment,PGD.Grade,Aps.PackingStyle,Brand,PGD.PackingStyleID,BAP FROM `ScrmPoGradeData` as PGD
			left join `AdminPackingStyles` as Aps on PGD.PackingStyleID=Aps.PCKID
			left join `ScrmBrandsMaster` SBM on  SBM.BrandName=PGD.Brand
			 WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand Order by PGD.ID asc";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);

	//ECHO $SqlVarietyMcs; EXIT;
	$RowCount = mysqli_num_rows($StmtVarietyMcs);
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {
		$CurrentCount++;

		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];
		$BAP =	$VarietyMcsRow['BAP'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		$BuyerItemCode =	$VarietyMcsRow['BuyerItemCode'];
		//$BuyerSUPC=	$VarietyMcsRow['BuyerSUPC']; 
		$BreakUpDescription =	$VarietyMcsRow['Description'];
		$PackingStyleID =	$VarietyMcsRow['PackingStyleID'];
		$Treatment =	$VarietyMcsRow['Treatment'];
		if ($BAP == 'NA') {
			$BAP = '';
		} else {
			$BAP = $BAP;
		}
		if ($Treatment == '') {
			$Treatment = '';
		} else {
			$Treatment = '';
			//$Treatment='('.$Treatment.')';  
		}



		/*$content.='<tr>';
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
			</tr>'; 
 */

		$Whitspace = '<b style="color:white">    </b>';
		$FontSize = "7";
		//$TOTAL_MCS=0;
		$content .= '';
		//  echo $BreakUpDescription; exit;
		if ($BreakUpDescription != '') {
			$Description = $BreakUpDescription;
		} else {
			$Description = $VarietyMcsRow['Variety'];
			if ($Treatment != '') {
				$Description .= $Treatment . '<br>';
			}

			if ($Certification != '') {
				$Description .= $Certification . '<br>';
			}
			//	$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$Certification.'<br>';

		}
		if ($DestinationCountryId == 26) {
			$Description .= '<u><br>PACKING : ' . $VarietyMcsRow['PackingStyle'] . '</u> NET WEIGHT PER CARTON 

			 <B><br>' . $BAP . '</B>';
		} else {
			$Description .= '<br>PACKING : ' . $VarietyMcsRow['PackingStyle'] . '' . $Whitspace . ' ';
		}

		if ($BreakUpDescription != '') {
			$Description = $BreakUpDescription . '<br>PACKING : ' . $VarietyMcsRow['PackingStyle'];
		}


		//	echo $Description; exit;

		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
			. nl2br($Description) . '</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';

		if ($CurrentCount < 2) {
			$content .= '<tr>';
			if ($BuyerItemCode != '') {
				$content .= '
			<th align="center" width="10%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '"> <u>Item Code</u></font></th>
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


			$content .= '<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';
		}





		$SqlVariety = "SELECT * FROM `ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID			
			WHERE InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
			and	PGD.PackingStyleID='$PackingStyleID' order by abs(PGD.Grade)";

		$StmtVariety = $iconn->query($SqlVariety);

		$ArySubBreakUp = array();

		while ($RowVariety = $StmtVariety->fetch_assoc()) {

			$ArySubBreakUp[] = $RowVariety['ID'];
			//$BrandPrintStatus=$RowVariety['PrintStatus'];
			//echo $BuyerItemCode;$RowVariety['BuyerItemCode']; exit;
			$AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['ID']);
			$Qty = $AryPriceBreakUp['Qty'];
			$UsdValue = $AryPriceBreakUp['UsdValue'];
			$TotalMcs = $AryPriceBreakUp['TotalMcs'];
			$Price = $AryPriceBreakUp['Price'];


			$content .= '<tr>';

			if ($BuyerItemCode != '') {
				$content .= '
				<td align="center" width="10%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '">
				 ' . $RowVariety['BuyerItemCode'] . ' 
				</font></td>
				<td align="center" width="10%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '">
				 ' . $RowVariety['Grade'] . '
				</font>
				</td>';
			} else {
				$content .= '<td align="center" width="20%"  style="border-right:1px solid white;" ><font size="' . $FontSize . '">
				 ' . $RowVariety['Grade'] . ' 
				</font></td>';
			}


			$content .= '
				<td align="center" width="18.5%" style="border-right:1px solid white;"  ><font size="' . $FontSize . '">
				' . $TotalMcs . '  </font></td>';
			if ($ShowCWiseBrand && $BrandPrintStatus) {
				$content .= '<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
				' . $RowVariety['Brand'] . '  </font></td>';
			} else {
				$content .= '<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
				</font></td>';
			}
			$content .= '<td align="center" width="13.5%" style="border-right:1px solid black;"  ><font size="' . $FontSize . '">
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

		if ($RowCount > 1) {
			$content .= '
				<tr>
				<td align="center" width="20%"  style="border-right:1px solid white;" ><font size="' . $FontSize . '">
				<b>Total</b	>
				</font></td>
				<td align="center" width="18.5%" style="
				border-top:1px solid black;
				border-bottom:1px solid black;
				" ><font size="' . $FontSize . '">
				' . $TotalMcs . '</font>
				</td>
				
				<td align="center" width="20%" style="border-right:1px solid black;">
				<font size="' . $FontSize . '"></font>
				</td>
				
				<td align="center" width="13.5%" ><font size="' . $FontSize . '">
				' . number_format($Qty, 3) . '</font>
				</td>
				<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font>
				</td>
				
				
					
				<td align="center" width="14.3%" style="
				border-top:1px solid black;
				border-bottom:1px solid black;
				">
														<font size="' . $FontSize . '">
				' . number_format($UsdValue, 2) . '</font></td>
				
				
				
				</tr>';
		}
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

	$content .= '</table>';


	return $content;
}


function getFooterTable($iconn, $ID)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	global $AryData, $style;
	$content = $style;


	$size = 45;
	$size1 = 55;




	$AryPriceData = getBreakUpData($iconn, $RowVariety['ID'], $ID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];
	$WtPerCtnTxt = $AryPriceData['WtPerCtnTxt'];


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

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$showProFEI = $AryData['showProFEI'];
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
	$LcStatus = $AryData['LcStatus'];


	$BILL_WORD = numberTowords($UsdValue);
	$content .= '
				<table border="1" cellspacing="0" cellpadding="2">
			
					

					<tr>  
					<td ><font size="7">
				
				 SHIPMENT DATE :  ' . getFormatedDate($ShipByDate) . ' 
				';

	if ($ShowBank) {
		$content .= '<p style="margin-left: 1em;"><b>BANK DETAILS  </b>' . $BankDetails . '</p>';
		$content .= '<p><b>Negotiating BANK  </b>' . $NegotiatingBank . '</p>';
		if ($LcStatus != 1) {
			$content .= '<p><b>ROUTING THROUGH   </b>' . $RoutingThrough . '</p>';
		}
	}

	$content .= '</font></td></tr>';

	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];


	/*
			if($QtyUnit=='KGS'){
				$content.='<BR><BR><b> NET WEIGHT <b style="color:white">------</b>: '.number_format($NetWgtKgs, 3, '.', '').' KGS</b>
				<BR><b> GROSS WEIGHT <b style="color:white">-</b>: '.number_format($GrossWeightKgs, 3, '.', '').' KGS</b> 
				</font></th>';	
				}else{
				$content.='<BR><BR> NET WEIGHT<b style="color:white">-----------------------------------</b>: 
				<b>'.number_format($NetWgtLbs, 3, '.', '').'LBS</b>
				<b style="color:white">--------------------------</b><b>'.number_format(($NetWgtLbs*0.454),3,'.','').' KGS</b>
				<BR> GROSS WEIGHT<b style="color:white">------------------------------</b>: 
				<b>'.number_format($GrossWeightLbs,3,'.','').'LBS</b>
				<b style="color:white">--------------------------</b><b>'.number_format(($GrossWeightLbs*0.454),3,'.','').' KGS</b> 
				</font></th>';	
				}
				*/


	$formated_date = date("Y-m-d", strtotime($InvDate));

	$AryDescription = getInvoiceDescription($iconn, $formated_date, $ExporterID);
	$ProformaNote = $AryDescription['ProformaNote'];


	$content .= '
				<tr>  
           		<th  align="LEFT"  width="70%"><font size="7"> AMOUNT CHARGABLE (IN WORDS) <br> (' . $UnitCurrency . ' : <b>' . $BILL_WORD . ' ONLY )</b><BR> ';
	if ($ShowCWiseWeightPerctText) {
		$content .= '<font size="6.5">' . nl2br($WtPerCtnTxt) . '<br></font>';
	}
	$content .= '<BR> <br> <b><font size="9">' . $ProformaNote . '</font></b>';

	if ($showRemarks) {
		$content .= '<br><br>' . $Remarks . '';
	}

	$content .= '</font></th>  	<th  align="center"  width="30%"><font size="7"> For <b>' . $AuthSign . '</b> <BR> <BR> <BR> 
						 
				<img src="' . $SignURL . '" 
				 width="100" height="30"style="width: 100" align="center"     valign="middle"/>				
				<p STYLE="text-align:center" > AUTHORISED SIGNATORY <BR> Signature & Date </p></font></th>
				</tr>';



	$content .= '</table>';

	return $content;
}



function getReportData($iconn, $PoID)
{

	global $AryShowConditions, $DocType;


	$ShowCWiseExporterGstNo = $AryShowConditions['ExporterGstNo'];
	$ShowCWiseReferenceNo = $AryShowConditions['ReferenceNo'];
	$ShowCWiseBuyer = $AryShowConditions['Buyer'];



	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

	$SqlMcsTotal = "SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
	$StmtMcsTotal = $iconn->query($SqlMcsTotal);
	$RowMcs = $StmtMcsTotal->fetch_assoc();
	$TotalMcs = $RowMcs['Mcs'];




	$SqlScrmPoData = "SELECT *,SPD.Status as PoStatus,ALD.SignURL,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,

ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,SPCL.Name AS LoadingPortName,
ABD.SwiftCode as SwiftCode,
SCL.name as PIDestinationCountry ,SC.name as PortofDischargeCountry ,SPC.Name as PortOfDischarge ,SPC.CountryCode as PortOfDischargeCountryCode ,SCPL.name as PortofLoadingCountry ,SPCL.Name as PortofLoading ,SPCF.Name as FinalDestination ,SPC.CountryCode as CountryCode 
	FROM ScrmPIData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmBuyerDetails as SBD on SPD.BuyerID=SBD.BuyerID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPortCities as SPCL on SPD.PortofLoading=SPCL.PortId
 left JOIN ScrmPortCities as SPCF on SPD.FinalDestination=SPCF.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN ScrmCountriesList as SCPL on SCPL.id=SPCL.Country
 left JOIN AdminLoginDetails as ALD on SC.id=SPD.ApprovedBy
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID
 left JOIN ScrmOrderCertification as SOC on SOC.CerificationID=SPD.Certification

 WHERE PoID='$PoID'";

	//ECHO  $SqlScrmPoData; EXIT;
	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ApprovedBy = $PoDataRow['ApprovedBy'];
	$Remarks = $PoDataRow['Remarks'];
	$DeliveryTerm = $PoDataRow['DeliveryTerms'];
	$DeliveryTermCode = getDeliveryTermCode($DeliveryTerm);
	$CountryID = $PoDataRow['DestinationCountry'];
	$LoadingPortName = $PoDataRow['LoadingPortName'];

	$SqlOrgVessel = "SELECT VesselName FROM `ScrmTranshipmentPortsList`  WHERE 
FromTOPID='$LoadingPortName' and PIID='$PoID'";
	//exit;
	$StmtOrgVessel = $iconn->query($SqlOrgVessel);
	$RowOrgVessel = $StmtOrgVessel->fetch_assoc();

	$OrgVesselName = $RowOrgVessel['VesselName'];


	$SqlLogin = "SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
	$StmtLogin = $iconn->query($SqlLogin);
	$RowLogin = $StmtLogin->fetch_assoc();
	$SignURL = $RowLogin['SignURL'];

	$ExporterData = getExporterDetails($ExpID);
	//$ProcessorData=getExporterDetails($ProcessorID);

	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);


	if ($Remarks != '') {
		$AryData['showRemarks'] = true;
	} else {
		$AryData['showRemarks'] = false;
	}

	if ($PoDataRow['NotifyPartyID'] != 0) {
		$AryData['showNotifyParty'] = true;
	} else {
		$AryData['showNotifyParty'] = false;
	}
	if ($PoDataRow['ImporterID'] != 0) {
		$AryData['showImporter'] = true;
	} else {
		$AryData['showImporter'] = false;
	}

	if ($PoDataRow['BuyerID'] != 0) {
		$AryData['showBuyer'] = true;
	} else {
		$AryData['showBuyer'] = false;
	}

	if ($PoDataRow['ShippingTerms'] == 'DDP') {
		$AryData['ShowShippingTerms'] = true;
	} else {
		$AryData['ShowShippingTerms'] = false;
	}

	/*if($PoDataRow['QtyUnit']=='LBS'){
		$AryData['ShowDeliveryTerms']=true;	
		}else{
		$AryData['ShowDeliveryTerms']=false;	
		}*/

	if ($PoDataRow['Certification'] != 0) {
		//$AryData['ShowCertification']=true;	
		$AryData['Certification'] = '(' . $PoDataRow['CerificationName'] . ')';
	} else {
		//$AryData['ShowCertification']=false;	

	}


	/*if($ExpID==5 &&
		$ExporterData['ExpUsfdaNo']!='' 
		&& $PoDataRow['DestinationCountry']!=26){
		$AryData['showExpUsFda']=true;	
		}else{
		$AryData['showExpUsFda']=false;	
		}
		
		if($ExpID==5){
		$AryData['showExpIECode']=true;	
		}else{
		$AryData['showExpIECode']=false;	
		}
		
		if($ExpID==5){
		$AryData['showExpGSTNo']=true;	
		}else{
		$AryData['showExpGSTNo']=false;	
		}
		
		if($PoDataRow['DestinationCountry']!=26){
		$AryData['showProFEI']=true;	
		}else{
		$AryData['showProFEI']=false;	
		}*/

	/*if($PoDataRow['DestinationCountry']==157){
		$AryData['ShowCWiseUsfda']=true;	
		}else{
		$AryData['ShowCWiseUsfda']=false;	
		}*/
	if ($PoDataRow['ShowBank'] != 0) {
		$AryData['ShowBank'] = true;
	} else {
		$AryData['ShowBank'] = false;
	}




	if ($PoDataRow['PoNo'] == '') {
		$AryData['PoDate'] = '';
	} else {
		$AryData['PoDate'] = " & Dated :" . getFormatedDate($PoDataRow['PoDate']);
	}


	/* Return Data*/
	$AryData['ReferenceNo'] = $PoDataRow['ReferenceNo'];
	$AryData['LcStatus'] = $PoDataRow['LcStatus'];
	$AryData['PoStatus'] = $PoDataRow['PoStatus'];
	$AryData['ApprovedBy'] = $ApprovedBy;
	$AryData['Remarks'] = $Remarks;
	$AryData['PaymentTerms'] = $PaymentTerms;
	$AryData['DeliveryTerms'] = $DeliveryTerms;
	$AryData['TotalMcs'] = $TotalMcs;
	$AryData['ExporterID'] = $ExpID;
	//$AryData['ShowBank']=$PoDataRow['ShowBank']; 
	$AryData['NotifyPartyName'] = $PoDataRow['NotifyPartyName'];
	$AryData['NPartyAddress'] = $PoDataRow['NPartyAddress'];
	$AryData['NPartyContactNumber'] = $PoDataRow['NPartyContactNumber'];
	$AryData['ImporterName'] = $PoDataRow['ImporterName'];
	$AryData['ImporterAddress'] = $PoDataRow['ImporterAddress'];
	//$AryData['Certification']=$PoDataRow['Certification']; 
	$AryData['DeliveryTermCode'] = $DeliveryTermCode;
	$AryData['OrgVesselName'] = $OrgVesselName;

	$AryData['ExporterName'] = $ExporterData['ExpName'];;
	$AryData['ExporterAddress'] = $ExporterData['ExpAddress'];
	$AryData['ExporterUsfdaNo'] = $ExporterData['ExpUsfdaNo'];
	$AryData['ExporterIeCode'] = $ExporterData['ExpIeCode'];
	$AryData['ExporterGstNo'] = $ExporterData['ExpGstNo'];
	$AryData['DestinationCountryId'] = $ExporterData['DestinationCountry'];
	//$AryData['ExporterRecAdd']=$ExporterData['ImporterRecordAddress']; 
	$AryData['AuthSign'] = $ExporterData['AuthSign'];



	$AryData['ConsigneeID'] = $PoDataRow['ConsigneeID'];
	$AryData['BuyerID'] = $PoDataRow['BuyerID'];
	$AryData['ConsigneeName'] = $PoDataRow['ConsigneeName'];
	$AryData['ConsigneeAddress'] = $PoDataRow['ConsigneeAddress'];
	$AryData['BuyerName'] = $PoDataRow['BuyerName'];
	$AryData['BuyerAddress'] = $PoDataRow['BuyerAddress'];


	$AryData['PortOfDischarge'] = strtoupper($PoDataRow['PortOfDischarge']) . ' ' . strtoupper($PoDataRow['PortOfDischargeCountryCode']) . ', ' . $PoDataRow['PortofDischargeCountry'];
	$AryData['PortofLoading'] = strtoupper($PoDataRow['PortofLoading']) . ' , ' . strtoupper($PoDataRow['PortofLoadingCountry']);
	$AryData['FinalDestination'] = strtoupper($PoDataRow['FinalDestination'] . ', ' . $PoDataRow['PortofDischargeCountry']);


	//$AryData['InvNo']=getPIInvoiceNumber($iconn,$ExpID,$PoID) ;
	$AryData['InvNo'] = $PoDataRow['InvNumFull'];
	$AryData['InvDate'] = getFormatedDate($PoDataRow['InvDate']);
	$AryData['PoNo'] = $PoDataRow['PoNo'];



	$AryData['ProcessorName'] = $ProcessorData['ExpName'];
	//$AryData['ProcessorAddress']=$ProcessorData['PackerAddress'];
	$AryData['ProcessorAddress'] = $ProcessorData['ExpAddress'];
	$AryData['ProcessorUsfdaNo'] = $ProcessorData['ExpUsfdaNo'];
	$AryData['ProcessorFEI'] = $ProcessorData['ExpFeiNo'];
	$AryData['ProcessorApprovalNo'] = $ProcessorData['ExpApprovalNo'];
	//$AryData['ProcessorIeCustomsNo']=$ProcessorData['ExpIeCustomsNo'];
	$AryData['ShowCWiseWeightPerctText'] = $AryShowConditions['WeightPerctText'];
	$AryData['ShowCWiseDeliveryTermCode'] = $AryShowConditions['DeliveryTermCode'];
	$AryData['ShowCWiseBrand'] = $AryShowConditions['Brand'];
	$AryData['ShowCWiseDeliveryTerms'] = $AryShowConditions['DeliveryTerms'];
	$AryData['ShowCWiseUsfda'] = $AryShowConditions['Usfda'];


	$AryData['ShippingTerms'] = $PoDataRow['ShippingTerms'];
	$AryData['PaymentTerms'] = $PoDataRow['PaymentName'];
	$AryData['DestinationCountry'] = $PoDataRow['PIDestinationCountry'];

	/*if($PoDataRow['DeliveryTerms'] =='CY/SD')
				{
					$PoDataRow['DeliveryTerms']='CY/SD <br> Store Delivery';
				}*/

	$AryData['DeliveryTerms'] = $PoDataRow['DeliveryTerms'];
	//$AryData['PortofLoadingCountry']=$PoDataRow['PortofLoadingCountry'];
	//$AryData['PortofLoading']=$PoDataRow['PortofLoading'];
	$AryData['QtyUnit'] = $PoDataRow['QtyUnit'];
	$AryData['UnitCurrency'] = $PoDataRow['UnitCurrency'];
	$AryData['ShipByDate'] = $PoDataRow['ShipByDate'];
	$AryData['SignURL'] = $SignURL;
	$AryData['PreparedBy'] = $PoDataRow['PreparedBy'];
	$AryData['BankID'] = $PoDataRow['BankID'];


	return $AryData;
}


function getBreakUpData($iconn, $GradeID, $PiID)
{

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

	$SqlTotal = "SELECT sum(Mcs) as MCS,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(Mcs*TotalSlabs*Price ) as PriceBags
			FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];

	if ($PiID != '') {
		$SqlWtPerCtn = "SELECT 
			round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,
			round(GrossWt/Sum(Mcs),2) as GrossWt FROM `ScrmPoGradeData` as SPGD 
				left JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID 
				WHERE InvId='$InvId' GROUP by PackingStyleID;";

		//	ECHO $SqlWtPerCtn; EXIT;  
		$StmtWtPerCtn = $iconn->query($SqlWtPerCtn);
		$RowWtPerCtn = $StmtWtPerCtn->fetch_assoc();
		$NetWtPerCtn = $RowWtPerCtn['NetWtLbs'];
		$GrossWtPerCtn = $RowWtPerCtn['GrossWt'];
		//$WtPerCtnTxt='EACH M/CTNS. CONSISTS OF NET WT. '.$NetWtPerCtn.' LBS AND GROSS WT. '.$GrossWtPerCtn.' LBS.';
	}

	$SqlQtyUnit = "SELECT QtyUnit from ScrmPIData WHERE PoID='$InvId'";
	$StmtQtyUnit = $iconn->query($SqlQtyUnit);
	$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
	$QtyUnit = $RowQtyUnit['QtyUnit'];



	if ($QtyUnit == 'BAGS') {
		$Qty = $RowTotal['MCS'] * $RowTotal['TotalSlabs'];
		$UsdValue = $RowTotal['PriceBags'];
	} else if ($QtyUnit == 'KGS') {
		$Qty = $RowTotal['NetWgtKgs'];
		$UsdValue = $RowTotal['PriceKgs'];
	} 
	else {
		$Qty = $RowTotal['NetWgtLbs'];
		$UsdValue = $RowTotal['PriceLbs'];
	}

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['UsdValue'] = $UsdValue;

	$AryPriceData['NetWgtKgs'] = $RowTotal['NetWgtKgs'];
	$AryPriceData['NetWgtLbs'] = $RowTotal['NetWgtLbs'];
	$AryPriceData['GrossWeightKgs'] = $RowTotal['GrossWeightKgs'];
	$AryPriceData['GrossWeightLbs'] = $RowTotal['GrossWeightLbs'];
	$AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;


	return 	$AryPriceData;
}


function getBankDetails($iconn, $BankID)
{

	$SqlBank = "SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$AryBankData = $StmtBank->fetch_assoc();

	return $AryBankData;
}
