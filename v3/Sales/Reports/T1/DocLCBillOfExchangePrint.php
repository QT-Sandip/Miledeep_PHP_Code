<?php
session_start();

$PoID = $_GET['id'];
$ClientPath = $_SESSION["ClientPath"];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");


$AdminConfigDetails = json_decode(json_encode($_SESSION['ClientConfigData']), true);
/* Folder Includes */
$Type = $_GET['Type'];

$SqlScrmPoData = "SELECT * FROM ScrmPoData as SPD 
  left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmBankDetailsMaster as ABD on ABD.ID=SPD.BankID
 WHERE PoID='$PoID'";

$StmtPoData = $iconn->query($SqlScrmPoData);
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID = $PoDataRow['ExporterID'];


class MYPDF extends TCPDF
{

	public function Header()
	{

		global $Status, $ClientPath;


		//$image_file = $ClientPath.'images/billofexchangewatermark.jpg'; 

		$this->Image($image_file, 0, 0, 230, 290, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);


	}

	public function Footer()
	{
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Lc Bill of Exchange Report : ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(5);
$pdf->SetMargins(10, 10, 10, 5);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

$table_data = getFirstpage($iconn, $PoID, $ClientPath);
$pdf->writeHTML($table_data);
$pdf->AddPage();

$table_data = getSecondPage($iconn, $PoID, $ClientPath);
$pdf->writeHTML($table_data);
//$pdf->AddPage(); 

// $table_data=getThirdPage($iconn,$PoID,$ClientPath);
// $pdf->writeHTML($table_data);
// $pdf->SetFont('helvetica', '', 6);

//$file_name='LCBillofExchangeReport-'.$PoID.'.pdf';


$ArrayFilename = getfilename($iconn, $ExpID, $PoID);

$InvShortCode = $ArrayFilename['InvShortCode'];
$CInvNo = $ArrayFilename['CInvNo'];
$FinancialYear = $ArrayFilename['FinancialYear'];

$file_name = $InvShortCode . '-BillofExchange-' . $CInvNo . '-' . $FinancialYear . '.pdf';


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
$pdf->Ln();

function getFirstPage($iconn, $PoID, $ClientPath)
{
	$Imageurl = $ClientPath . "images/1.png";
	$Description = "(Second of the same  tenor and date being unpaid)";
	$Description1 = "FIRST";
	$content = getBillofexchangePage($iconn, $PoID, $Imageurl, $Description, $Description1);
	return $content;
}

function getSecondPage($iconn, $PoID, $ClientPath)
{
	$Imageurl = $ClientPath . "images/2.png";
	$Description = "(First of the same  tenor and date being unpaid)";
	$Description1 = "SECOND";
	$content = getBillofexchangePage($iconn, $PoID, $Imageurl, $Description, $Description1);
	return $content;
}

// function getThirdPage($iconn,$PoID,$ClientPath){
// $Imageurl=$ClientPath."images/Copy_Black.png";
// $Description="(Second of the same  tenor and date being unpaid)";
// $content=getBillofexchangePage($iconn,$PoID,$Imageurl,$Description);
// return $content;
// }

function getBillofexchangePage($iconn, $PoID, $Imageurl, $Description, $Description1)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	//include ($cp."Includes/IncludePage.php");
	global $PoDataRow, $AdminConfigDetails;

	$CustomInvoiceSql = "SELECT *,SCD.LcNo AS LcNo,SCD.LcDate AS LcDate,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading,
SCF.name as FinalDestinationCountry,SPCF.Name as FinalDestination,SPCF.CountryCode as FinalDestinationCountryCode,SCCD.name as ConsigneeCountry
FROM ScrmCustomInvoices as SCV 
left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID
left JOIN ScrmCommercialData as SCD on SCD.PoID=SCV.PiID
left JOIN ScrmPaymentTerms as SPT on SCD.ComPaymentTerms=SPT.ID	
 left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
 left JOIN ScrmCountriesList as SCF on SCF.id=SPCF.Country
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN ScrmCountriesList as SL on SL.id=SP.Country
 left JOIN ScrmISFDetails as ISF on ISF.PIID=SCV.PiID
 left JOIN ScrmShippingBillDetails as SSBD on SSBD.InvID=SCV.PiID
 left JOIN ScrmBillOfExchange as SB on SB.PIID=SCV.PiID
 left JOIN AdminExporterDetails as AED on AED.ExpID=SSD.ExporterID
 left JOIN ScrmConsigneeDetails as SCDD on SCDD.ConID=SSD.ConsigneeID
 left JOIN ScrmCountriesList as SCCD on SCCD.id=SCDD.ConsigneeCountry
 WHERE SCV.PiID='$PoID'";
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();






	$LcStatus = $PoDataRow['LcStatus'];
	$ExpID = $PoDataRow['ExporterID'];
	$ExpName = $CustomInvoiceRow['ExpName'];
	$ProcessorID = $PoDataRow['ProcessorID'];

	//$FinalDestination=$CustomInvoiceRow['FinalDestination']; 
	$FinalDestination = $CustomInvoiceRow['FinalDestination'] . ' ' . $CustomInvoiceRow['FinalDestinationCountryCode'] . ' ,' . $CustomInvoiceRow['FinalDestinationCountry'];

	$PoNo = $PoDataRow['PoNo'];
	$PoDate = getFormatedDateStar($PoDataRow['PoDate']);

	$CountryName = $PoDataRow['name'];
	$BankName = $PoDataRow['BankName'];
	$BankAddress = $PoDataRow['BankAddress'];
	//$PaymentTerms=$PoDataRow['PaymentName']; 
	$FrieghtCharge = $CustomInvoiceRow['FrieghtCharge'];
	$ExchangeRate = $CustomInvoiceRow['ExchangeRate'];
	$AdvanceAmount = $CustomInvoiceRow['AdvanceAmount'];
	$Status = $CustomInvoiceRow['Status'];
	$BLDate = getFormatedDateStar($CustomInvoiceRow['BLDate']);
	$BLNo = $CustomInvoiceRow['BLNo'];
	$PoNumber = $PoDataRow['PoNo'];
	$IssuedBy = $CustomInvoiceRow['IssuedBy'];
	$ToAddress = $CustomInvoiceRow['ToAddress'];
	$DraftDate = getFormatedDateStar($CustomInvoiceRow['DraftDate']);
	$IGST = $CustomInvoiceRow['IGST'];
	$Discount = $CustomInvoiceRow['Discount'];
	$CreditNo = $CustomInvoiceRow['LcNo'];
	$CreditDate = getFormatedDateStar($CustomInvoiceRow['LcDate']);
	$InvNO = $CustomInvoiceRow['CustomsInvNo'];
	$InvDate = getFormatedDateNA($CustomInvoiceRow['CInvDate']);
	$ComShippingTerms = $CustomInvoiceRow['ComShippingTerms'];
	$ComPaymentTerms = $CustomInvoiceRow['PaymentName'];
	//$ConsigneeNameandAddress=$CustomInvoiceRow['ConsigneeName'].' , '.$CustomInvoiceRow['ConsigneeAddress'];
	$ConsigneeName = $CustomInvoiceRow['ConsigneeName'];
	$ConsigneeCountry = $CustomInvoiceRow['ConsigneeCountry'];
	$ConsigneeAddress = $CustomInvoiceRow['ConsigneeAddress'];

	$AryTranshipmentData = getTranshipmentData($PoID);
	$VesselName = $AryTranshipmentData['VesselName'] . ' / ' . $AryTranshipmentData['VOYNO'];
	//$VesselName = $AryTranshipmentData['VesselName']; 
	if ($VesselName == '') {
		$VesselName = '****';
	}


	if ($IssuedBy == '') {
		$IssuedBy = $CustomInvoiceRow['ConsigneeName'] . ' , ' . $ConsigneeAddress;
	}

	if ($ComPaymentTerms != '') {
		$PaymentTerms = $CustomInvoiceRow['PaymentName'];
		$NoOfDays = '( ' . $CustomInvoiceRow['NoOfDays'] . ' Days From ' . $CustomInvoiceRow['ConditionDate'] . ' )';

	} else {
		$PaymentTerms = $PoDataRow['PaymentName'];
		$NoOfDays = '(' . $PoDataRow['NoOfDays'] . ' Days)';
	}
	if ($ComShippingTerms != '') {
		$ShippingTerms = $CustomInvoiceRow['ComShippingTerms'];
	} else {
		$ShippingTerms = $PoDataRow['ShippingTerms'];
	}

	/***NON LC Calculation start***/



	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


	$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	if ($PoDataRow['QtyUnit'] == 'KGS') {
		$Price = $RowTotal['PriceKgs'];
	} else {

		$Price = $RowTotal['PriceLbs'];
	}



	$AryPaymentData = getPaymentValues($iconn, $PoID, 'CMI', $AdminConfigDetails);

	$TotalDiscount = $AryPaymentData['InvValueUSD'];

	if ($LcStatus == 0) {
		$ShowBLno = true;
		$ShowPono = "<b>PO#: " . $PoNumber . " DT:" . $PoDate . "</b>";

		// echo $AdvanceAmount; exit;

		if ($AdvanceAmount > 0) {

			$TotalDiscount = $TotalDiscount - $AdvanceAmount;
		}

		$ExchangeInvoicevalue = $TotalDiscount;
		$BillWord = numberTowords($ExchangeInvoicevalue);



	}



	/***NON LC Calculation End***/ else {
		//$ExchangeInvoicevalue=$CustomInvoiceRow['ExchangeInvoicevalue'];
		$ExchangeInvoicevalue = $TotalDiscount;
		$BillWord = numberTowords($ExchangeInvoicevalue);
		$LcAddress = 'LC REFERENCE NUMBER. ' . $CreditNo;
		$ShowBLno = true;
		$ShowCreditNo = "letter of Credit No. <U>" . $CreditNo . "DT: " . $CreditDate . "</U>";
		$NoOfDays = '';

		//$ShowPono=true;


	}
	if ($ShippingTerms == 'DDP' & $LcStatus == 1) {
		$ShippingTerms = '(' . $ShippingTerms . ' ' . strtoupper($FinalDestination) . ')';
	}

	if ($CreditNo == '') {
		$CreditNo = '****';
	}
	if ($PaymentTerms == '') {
		$PaymentTerms = '****';
	}
	$SqlVarietyMcs = "SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP,PrintStatus,CIDescription
FROM `ScrmCIGradeData` SCD
left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
left join `ScrmBrandsMaster` SBM on  SBM.BrandName=SCD.Brand
WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);

	$content = '';
	$Whitspace = '<b style="color:white">  yuhjutgfrtgujkiolmnbvgftrkliomkjhykihuyy</b>';

	$FontSize = '10px';
	$content .= '	<br><br><br><br><br><br><br><br>
		 <p style="width: 100px; display: table;text-align:right"> DATE  <b><U>' . $DraftDate . ' </U></b></p>
		 <p style="width: 100px; display: table;text-align:center"> <b><U> BILL OF EXCHANGE </U>  </b></p>
     
		 <p style="width: 100px; display: table;text-align:left">
		 <b>NO. <U>' . $InvNO . ' DT:
				 ' . $InvDate . ' </U></b>';
	if ($ShowBLno) {
		$content .= ' <br>BL NO: <b><U>' . $BLNo . '</U> ,DATE:
				 <U> ' . $BLDate . ' </U></b>';
	}
	$content .= ' </p>
      
     
				<p style="width: 200px; display: table; text-align:left">
				<b><U>' . $PaymentTerms . ' ' . $NoOfDays . ' </U></b>  
			' . addSpace(0) . '   this <B>' . $Description1 . '</B> of Exchange ' . $Description . ' Pay to the order of the
				<U > ' . $BankName . '  
				 ' . $BankAddress . '  </U> the sum of </p>
				
				
				<p style="width: 200px; display: table;text-align:left"> <b>U.S.$.
				' . number_format($ExchangeInvoicevalue, 2) . '<font size="10"> (' . strtoupper($BillWord) . ')</font></b> </p>
				

				

				 
				<p style="width: 200px; display: table;text-align:left">For value received towards:';

	$TotalMcs = 0;
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {

		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		if ($LcStatus == 0) {

			if ($BrandPrintStatus) {
				$Brand = ', BRAND: ' . $VarietyMcsRow['Brand'];
			} else {
				$Brand = '-';

			}
			//$Brand=', BRAND: '.$VarietyMcsRow['Brand'];
		}
		$TotalMcs += $VarietyMcsRow['VarietyMcs'];

		if ($VarietyMcsRow['CIDescription'] != '') {
			$ArrayVariety[] = $VarietyMcsRow['CIDescription'];
		} else {
			$ArrayVariety[] = $VarietyMcsRow['Variety'];
		}

		// $ArrayVariety[]=$VarietyMcsRow['Variety'];
// $ArrayCIDescription[]=$VarietyMcsRow['CIDescription'];
		$Variety = implode(',', array_unique($ArrayVariety));
		// $CIDescription=implode(',',array_unique($ArrayCIDescription));
	}
	unset($ArrayVariety);
	// unset($ArrayCIDescription); 
	$content .= '<font size="10">
			<b> ' . $TotalMcs . ' CARTONS OF ' . $Variety . '
			 </b></font>';


	// }
	$content .= 'Shipped to ' . addSpace(1) . '<b>' . strtoupper($ConsigneeName) . '  , ' . $ConsigneeCountry . ' through ' . $VesselName . '</b>
				 
  
and Place the same against ' . $ShowPono . ' ' . $ShowCreditNo . ' Issued by <b><U>' . strtoupper($IssuedBy) . '</U></b></p>
';




	$content .= '	

				';

	if ($ToAddress == '') {


		$content .= ' <table   border="0" cellspacing="1"  cellpadding="5" >
<tr>
<th style="width:70%" >To <br>******<br>******<br>****** </th> 
</tr>

</table>';

	} else {

		$content .= '<p style="width: 200px;font-size: 9px; display: table;text-align:left;"><b>
		To <br>' . strtoupper(nl2br($ToAddress)) . '
		</b><br>
		';


	}



	$content .= '	</p>';

	$content .= '<p style="width: 200px;font-size: 12px; display: table;text-align:RIGHT;"><b>
		For ' . strtoupper($ExpName) . '
		<br><br><br>Authorized Signatory</b></P>
		';
	return $content;
}

function addSpace($SpaceCount)
{

	for ($i = 0; $i < $SpaceCount; $i++) {
		$Space .= '&nbsp;';
	}

	return $Space;
}

function getTranshipmentData($PoID)
{
	global $iconn;
	$sqlFields = "VesselName,VOYNO";
	$sqlQuery = "SELECT $sqlFields FROM `ScrmTranshipmentPortsList` WHERE PIID=$PoID and Status=1 order by TspID asc LIMIT 1";
	$stmtquery = $iconn->query($sqlQuery);

	$dataRow = $stmtquery->fetch_assoc();
	return $dataRow;
}


// function IsEmpty($Value){
// if($Value==''){
// $IsEmpty=True;
// }
// else{
// $IsEmpty=False;
// }
// return $IsEmpty
// }
?>