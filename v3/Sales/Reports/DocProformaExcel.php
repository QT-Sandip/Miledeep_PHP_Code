<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();


$PoID = $_GET['id'];
$Type = $_GET['Type'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");

$ClientID = $_SESSION["ClientID"];
$AdminConfigDetails = $_SESSION["ClientConfigData"];
$AdminInvConfigDetails = $_SESSION["ClientInvoiceConfigData"];
$sauceFlag = false;
$FoBCalculation = $AdminConfigDetails['FoBCalculationInCRN'];
$DocType = 'CUI';

// Temporary code: Implement a specific condition below to execute this logic for a designated client.

if ($_SESSION["ClientID"] && $_SESSION["ClientID"] == 'DFLV3') {

	// header('Location: ./DocFOBProformaExcel.php?id=' . $PoID . '&Type=' . $Type);
	// exit;
}

$SqlBreakup = "SELECT * FROM   `ScrmPoGradeData`  as SCD  
left join AdminPackingStyles as APS on APS.PCKID=SCD.PackingStyleID
WHERE InvId='$PoID' ORDER BY `ID` ASC";
$StmtBreakUp = $iconn->query($SqlBreakup);


$SNo = 1;

$content = '';


$content .= '
<table id="testTable" summary="Code page support in different versions of MS Windows."
 rules="groups" frame="hsides" border="2">
 
 <colgroup span="2" align="center"></colgroup>
 <colgroup span="4" align="center"></colgroup>
 
 
 <tr> 
				<th  align="center"><b>Supply Type Code</b></th>
				<th  align="center"><b>Reverse Charge</b></th>
				<th  align="center"><b>e-Comm GSTIN</b></th>
				<th  align="center"><b>Igst On Intra</b></th>
				<th  align="center"><b>Document Type</b></th>
				<th  align="center"><b>Document Number</b></th>
				<th  align="center"><b>Document Date (DD/MM/YYYY)</b></th>
				<th  align="center"><b>Buyer GSTIN</b></th>
				<th  align="center"><b>Buyer Legal Name</b></th>
				<th  align="center"><b>Buyer Trade Name</b></th>
				<th  align="center"><b>Buyer POS</b></th>
				<th  align="center"><b>Buyer Addr1</b></th>
				<th  align="center"><b>Buyer Addr2</b></th>
				<th  align="center"><b>Buyer Location</b></th>
				<th  align="center"><b>Buyer Pin Code</b></th>
				<th  align="center"><b>Buyer State</b></th>
				<th  align="center"><b>Buyer Phone Number</b></th>
				<th  align="center"><b>Buyer Email Id</b></th>
				<th  align="center"><b>Dispatch Name</b></th>
				<th  align="center"><b>Dispatch Addr1</b></th>
				<th  align="center"><b>Dispatch Addr2</b></th>
				<th  align="center"><b>Dispatch Location</b></th>
				<th  align="center"><b>Dispatch Pin Code</b></th>
				<th  align="center"><b>Dispatch State</b></th>
				<th  align="center"><b>Shipping GSTIN</b></th>
				<th  align="center"><b>Shipping Legal Name</b></th>
				<th  align="center"><b>Shipping Trade Name</b></th>
				<th  align="center"><b>Shipping Addr1</b></th>
				<th  align="center"><b>Shipping Addr2</b></th>
				<th  align="center"><b>Shipping Location</b></th>
				<th  align="center"><b>Shipping Pin Code</b></th>
				<th  align="center"><b>Shipping State</b></th>
				<th  align="center"><b>Sl.No</b></th>
				<th  align="center"><b>Product Description</b></th>
				<th  align="center"><b>Is_Service</b></th>
				<th  align="center"><b>HSN Code</b></th>
				<th  align="center"><b>Barcode</b></th>
				<th  align="center"><b>Quantity</b></th>
				<th  align="center"><b>Free Quantity</b></th>
				<th  align="center"><b>Unit</b></th>
				<th  align="center"><b>Unit Price</b></th>
				<th  align="center"><b>Gross Amount</b></th>
				<th  align="center"><b>Discount</b></th>
				<th  align="center"><b>Pre Tax Value</b></th>
				<th  align="center"><b>Taxable Value</b></th>
				<th  align="center"><b>GST Rate (%)</b></th>
				<th  align="center"><b>Sgst Amt(Rs)</b></th>
				<th  align="center"><b>Cgst Amt (Rs)</b></th>
				<th  align="center"><b>Igst Amt (Rs)</b></th>
				<th  align="center"><b>Cess Rate (%)</b></th>
				<th  align="center"><b>Cess Amt Adval (Rs)</b></th>
				<th  align="center"><b>Cess Non Adval Amt (Rs)</b></th>
				<th  align="center"><b>State Cess Rate (%)</b></th>
				<th  align="center"><b>State Cess Adval Amt (Rs)</b></th>
				<th  align="center"><b>State Cess Non-Adval Amt (Rs)</b></th>
				<th  align="center"><b>Other Charges</b></th>
				<th  align="center"><b>Item Total</b></th>
				<th  align="center"><b>Batch Name</b></th>
				<th  align="center"><b>Batch Expiry Dt</b></th>
				<th  align="center"><b>Warranty Dt</b></th>
				<th  align="center"><b>Total Taxable value</b></th>
				<th  align="center"><b>Sgst Amt</b></th>
				<th  align="center"><b>Cgst Amt</b></th>
				<th  align="center"><b>Igst Amt</b></th>
				<th  align="center"><b>Cess Amt</b></th>
				<th  align="center"><b>State Cess Amt</b></th>
				<th  align="center"><b>Discount</b></th>
				<th  align="center"><b>Other charges</b></th>
				<th  align="center"><b>Round off</b></th>
				<th  align="center"><b>Total Invoice value</b></th>
				<th  align="center"><b>Shipping Bill No</b></th>
				<th  align="center"><b>Shipping Bill Dt</b></th>
				<th  align="center"><b>Port</b></th>
				<th  align="center"><b>Refund claim</b></th>
				<th  align="center"><b>Foreign Currency</b></th>
				<th  align="center"><b>Country Code</b></th>
				<th  align="center"><b>Export Duty Amount</b></th>
				<th  align="center"><b>Trans ID</b></th>
				<th  align="center"><b>Trans Name</b></th>
				<th  align="center"><b>Trans Mode</b></th>
				<th  align="center"><b>Distance</b></th>
				<th  align="center"><b>Trans Doc No.</b></th>
				<th  align="center"><b>Trans Doc Date</b></th>
				<th  align="center"><b>Vehicle No.</b></th>
				<th  align="center"><b>Vehicle Type</b></th>
				<th  align="center"><b>Error List</b></th>
				
	
				</tr><tbody>';

$TotalTaxableValue = 0;
while ($RowBreakup = $StmtBreakUp->fetch_assoc()) {

	$ArrayData = getBreakUpDataExcel($iconn, $RowBreakup['ID']);

	$ReverseCharge = $ArrayData['ReverseCharge'];
	$E_Comm_GSTIN = $ArrayData['E_Comm_GSTIN'];
	$IgstOnIntra = $ArrayData['IgstOnIntra'];
	$DocumentDate = $ArrayData['DocumentDate'];
	$DocumentNumber = $ArrayData['DocumentNumber'];
	$SupplyTypeCode = $ArrayData['SupplyTypeCode'];
	$DocumentType = $ArrayData['DocumentType'];
	$BuyerLegalName = $ArrayData['BuyerLegalName'];
	$BuyerTradeName = $ArrayData['BuyerTradeName'];
	$BuyerAddr1 = $ArrayData['BuyerAddr1'];
	$BuyerAddr2 = $ArrayData['BuyerAddr2'];
	$BuyerLocation = $ArrayData['BuyerLocation'];
	$BuyerPinCode = $ArrayData['BuyerPinCode'];
	$BuyerGSTIN = $ArrayData['BuyerGSTIN'];
	$BuyerPOS = $ArrayData['BuyerPOS'];
	$BuyerState = $ArrayData['BuyerState'];
	$BuyerPhoneNumber = $ArrayData['BuyerPhoneNumber'];
	$BuyerEmailId = $ArrayData['BuyerEmailId'];
	$DispatchName = $ArrayData['DispatchName'];
	$DispatchAddr1 = $ArrayData['DispatchAddr1'];
	$DispatchAddr2 = $ArrayData['DispatchAddr2'];
	$DispatchLocation = $ArrayData['DispatchLocation'];
	$DispatchPinCode = $ArrayData['DispatchPinCode'];
	$DispatchState = $ArrayData['DispatchState'];
	$ShippingGSTIN = $ArrayData['ShippingGSTIN'];
	$ShippingLegalName = $ArrayData['ShippingLegalName'];
	$ShippingTradeName = $ArrayData['ShippingTradeName'];
	$ShippingAddr1 = $ArrayData['ShippingAddr1'];
	$ShippingAddr2 = $ArrayData['ShippingAddr2'];
	$ShippingLocation = $ArrayData['ShippingLocation'];
	$ShippingPinCode = $ArrayData['ShippingPinCode'];
	$ShippingState = $ArrayData['ShippingState'];
	$ProductDescription = $ArrayData['ProductDescription'];
	$Is_Service = $ArrayData['Is_Service'];
	$HSNcode = $ArrayData['HSNcode'];
	$Barcode = $ArrayData['Barcode'];
	$Quantity = $ArrayData['Quantity'];
	$FreeQuantity = $ArrayData['FreeQuantity'];
	$Unit = $ArrayData['Unit'];
	$UnitPrice = round($ArrayData['UnitPrice'], 2);
	$GrossAmount = $ArrayData['GrossAmount'];
	$Discount = $ArrayData['Discount'];
	$PreTaxValue = $ArrayData['PreTaxValue'];
	$Taxablevalue = $ArrayData['Taxablevalue'];
	$GSTRatePerc = $ArrayData['GSTRatePerc'];
	$InvSgstAmt = $ArrayData['InvSgstAmt'];
	$InvCgstAmt = $ArrayData['InvCgstAmt'];
	$InvIgstAmt = $ArrayData['InvIgstAmt'];
	$InvCessRatePerc = $ArrayData['InvCessRatePerc'];
	$InvCessAmtAdval = $ArrayData['InvCessAmtAdval'];
	$InvCessNonAdvalAmt = $ArrayData['InvCessNonAdvalAmt'];
	$InvStateCessRatePerc = $ArrayData['InvStateCessRatePerc'];
	$InvStateCessAdvalAmt = $ArrayData['InvStateCessAdvalAmt'];
	$InvStateCessNonAdvalAmt = $ArrayData['InvStateCessNonAdvalAmt'];
	$InvOtherCharges = $ArrayData['InvOtherCharges'];
	$ItemTotal = $ArrayData['ItemTotal'];
	$BatchName = $ArrayData['BatchName'];
	$BatchExpiryDate = $ArrayData['BatchExpiryDate'];
	$WarrantyDate = $ArrayData['WarrantyDate'];
	$TotalTaxableValue = $ArrayData['TotalTaxableValue'];

	$BatchSgstAmt = $ArrayData['BatchSgstAmt'];
	$BatchCgstAmt = $ArrayData['BatchCgstAmt'];
	$BatchIgstAmt = $ArrayData['BatchIgstAmt'];
	$BatchCessAmt = $ArrayData['BatchCessAmt'];
	$BatchStateCessAmt = $ArrayData['BatchStateCessAmt'];
	$BatchDiscount = $ArrayData['BatchDiscount'];
	$BatchOtherCharges = $ArrayData['BatchOtherCharges'];
	$RoundOff = $ArrayData['RoundOff'];
	//$TotalInvoicevalue=$ArrayData['TotalInvoicevalue'];
	$TotalInvoicevalue = $BatchIgstAmt + $TotalTaxableValue;
	$ShippingBillNo = $ArrayData['ShippingBillNo'];
	$Port = $ArrayData['Port'];
	$ShippingBillDt = $ArrayData['ShippingBillDt'];
	$RefundClaim = $ArrayData['RefundClaim'];
	$ForeignCurrency = $ArrayData['ForeignCurrency'];
	$CountryCode = $ArrayData['CountryCode'];
	$ExportDutyAmount = $ArrayData['ExportDutyAmount'];
	$TransID = $ArrayData['TransID'];
	$TransName = $ArrayData['TransName'];
	$TransMode = $ArrayData['TransMode'];
	$Distance = $ArrayData['Distance'];
	$TransDocNo = $ArrayData['TransDocNo'];
	$TransDocDate = $ArrayData['TransDocDate'];
	$VehicleNo = $ArrayData['VehicleNo'];
	$VehicleType = $ArrayData['VehicleType'];
	$ErrorList = $ArrayData['ErrorList'];



	$content .= '	
			
				<tr>
			    <td  align="center">' . $SupplyTypeCode . '</td>
			    <td  align="center">' . $ReverseCharge . '</td>
			    <td  align="center">' . $E_Comm_GSTIN . '</td>
			    <td  align="center">' . $IgstOnIntra . '</td>
			    <td  align="center">' . $DocumentType . '</td>
			    <td  align="center">' . $DocumentNumber . '</td>
				<td  align="center">' . $DocumentDate . '</td>
			    <td  align="center">' . $BuyerGSTIN . '</td>
			    <td  align="center">' . $BuyerLegalName . '</td>
			    <td  align="center">' . $BuyerTradeName . '</td>
			    <td  align="center">' . $BuyerPOS . '</td>
			    <td  align="center">' . $BuyerAddr1 . '</td>
				<td  align="center">' . $BuyerAddr2 . '</td>
			    <td  align="center">' . $BuyerLocation . '</td>
			    <td  align="center">' . $BuyerPinCode . '</td>
			    <td  align="center">' . $BuyerState . '</td>
			    <td  align="center">' . $BuyerPhoneNumber . '</td>
			    <td  align="center">' . $BuyerEmailId . '</td>
				<td  align="center">' . $DispatchName . '</td>
			    <td  align="center">' . $DispatchAddr1 . '</td>
			    <td  align="center">' . $DispatchAddr2 . '</td>
			    <td  align="center">' . $DispatchLocation . '</td>
			    <td  align="center">' . $DispatchPinCode . '</td>
			    <td  align="center">' . $DispatchState . '</td>
				<td  align="center">' . $ShippingGSTIN . '</td>
			    <td  align="center">' . $ShippingLegalName . '</td>
			    <td  align="center">' . $ShippingTradeName . '</td>
			    <td  align="center">' . $ShippingAddr1 . '</td>
			    <td  align="center">' . $ShippingAddr2 . '</td>
			    <td  align="center">' . $ShippingLocation . '</td>
				<td  align="center">' . $ShippingPinCode . '</td>
			    <td  align="center">' . $ShippingState . '</td>
			    <td  align="center">' . $SNo . '</td>
			    <td  align="center">' . $ProductDescription . '</td>
			    <td  align="center">' . $Is_Service . '</td>
			    <td  align="center">' . $HSNcode . '</td>
			    <td  align="center">' . $Barcode . '</td>
			    <td  align="center">' . str_replace(',', '', number_format($Quantity, 3)) . '</td>
			    <td  align="center">' . $FreeQuantity . '</td>
			    <td  align="center">' . $Unit . '</td>
			    <td  align="center">' . $UnitPrice . '</td>
			    <td  align="center">' . number_format($GrossAmount, 2) . '</td>
			    <td  align="center">' . $Discount . '</td>
			    <td  align="center">' . $PreTaxValue . '</td>
			    <td  align="center">' . number_format($Taxablevalue, 2) . '</td>
			    <td  align="center">' . $GSTRatePerc . '</td>
			    <td  align="center">' . $InvSgstAmt . '</td>
			    <td  align="center">' . $InvCgstAmt . '</td>
			    <td  align="center">' . round($InvIgstAmt, 2) . '</td>
			    <td  align="center">' . $InvCessRatePerc . '</td>
			    <td  align="center">' . $InvCessAmtAdval . '</td>
			    <td  align="center">' . $InvCessNonAdvalAmt . '</td>
			    <td  align="center">' . $InvStateCessRatePerc . '</td>
			    <td  align="center">' . $InvStateCessAdvalAmt . '</td>
			    <td  align="center">' . $InvStateCessNonAdvalAmt . '</td>
			    <td  align="center">' . $InvOtherCharges . '</td>
			    <td  align="center">' . number_format($ItemTotal, 2) . '</td>
			    <td  align="center">' . $BatchName . '</td>
			    <td  align="center">' . $BatchExpiryDate . '</td>
			    <td  align="center">' . $WarrantyDate . '</td>
			    <td  align="center">' . number_format($TotalTaxableValue, 2) . '</td>
			    <td  align="center">' . $BatchSgstAmt . '</td>
			    <td  align="center">' . $BatchCgstAmt . '</td>
			    <td  align="center">' . number_format($BatchIgstAmt, 2) . '</td>
			    <td  align="center">' . $BatchCessAmt . '</td>
			    <td  align="center">' . $BatchStateCessAmt . '</td>
			    <td  align="center">' . $BatchDiscount . '</td>
			    <td  align="center">' . $BatchOtherCharges . '</td>
			    <td  align="center">' . $RoundOff . '</td>
			    <td  align="center">' . number_format($TotalInvoicevalue, 2) . '</td>
			    <td  align="center">' . $ShippingBillNo . '</td>
			    <td  align="center">' . $ShippingBillDt . '</td>
			    <td  align="center">' . $Port . '</td>
			    <td  align="center">' . $RefundClaim . '</td>
			    <td  align="center">' . $ForeignCurrency . '</td>
			    <td  align="center">' . $CountryCode . '</td>
				 <td  align="center">' . $ExportDutyAmount . '</td>
			    <td  align="center">' . $TransID . '</td>
			    <td  align="center">' . $TransName . '</td>
			    <td  align="center">' . $TransMode . '</td>
			    <td  align="center">' . $Distance . '</td>
			    <td  align="center">' . $TransDocNo . '</td>
			    <td  align="center">' . $TransDocDate . '</td>
			    <td  align="center">' . $VehicleNo . '</td>
			    <td  align="center">' . $VehicleType . '</td>
			    <td  align="center">' . $ErrorList . '</td>
				</tr>';

	$SNo++;
}

$content .= '</tbody></table>
			<br>
			<br>
			';


$ArrayFilename = getfilename($iconn, $ExpID, $PoID);

$InvShortCode = $ArrayFilename['InvShortCode'];
$CInvNo = $ArrayFilename['CInvNo'];
$FinancialYear = $ArrayFilename['FinancialYear'];
$CustomsInvNo = $ArrayFilename['CustomsInvNo'];

//$filename=$InvShortCode.'-AZ_SD-'.$CInvNo.'-'.$FinancialYear.'.xls';
$filename = $CustomsInvNo . '_Excel.xls';

header("Content-Type: application/vnd.ms-excel;base64");
header('Content-Disposition: attachment;filename=' . $filename . '');
header("Pragma: ");
header("Cache-Control: ");


echo "<html><head><style>
table, th, td {
  border: 1px solid black;
}
</style></head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
echo '<body style="border: 1px solid #ccc">';
echo $content;
echo "</body>";
echo "</html>";

function getBreakUpDataExcel($iconn, $GradeID)
{
	//$GradeID=14;
	global $ClientID, $AdminInvConfigDetails, $sauceFlag, $FoBCalculation, $DocType;

	$CustomInvoiceSql = "SELECT *,SC.id AS DestinationCountry,SP.PortCode as PortOfLoadingCode,SC.GstCountryCode as DestinationCountryCode,
	(SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1)
		GstPerc,
		SPM.GstPerc GstPercP
	FROM ScrmPoData as SPD
	
	left JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID
	left JOIN ScrmCustomInvoices as SCI on SPD.PoID=SCI.PiID
	left JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortofDischarge
	left JOIN ScrmPortCities as SP on SP.PortId=SPD.PortOfLoading
	left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
	left JOIN ScrmCountriesList as SL on SL.id=SP.Country
	left JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SCI.PiID
	LEFT join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
	LEFT join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType
	LEFT join ScrmCurrencyMaster as SCM on SCM.CurrencyCode=SPD.UnitCurrency
	WHERE SPGD.ID='$GradeID'";

	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);

	$OrderRow = $CustomInvoiceExec->fetch_assoc();

	$PoID = $OrderRow['PiID'];
	$ExpID = $OrderRow['ExporterID'];
	$BuyerLegalName = $OrderRow['ConsigneeName'];
	$BuyerTradeName = $OrderRow['ConsigneeName'];
	$BuyerAddr = $OrderRow['ConsigneeAddress'];
	$Addresslength = round(strlen($BuyerAddr) / 2);
	$BuyerAddr = str_split($BuyerAddr, $Addresslength);

	$BuyerAddr1 = $BuyerAddr[0];
	$BuyerAddr2 = $BuyerAddr[1];
	$BuyerLocation = getCountryDetails($iconn, $OrderRow['ConsigneeCountry']);
	$BuyerPinCode = '999999'; //$OrderRow['ConsigneeZipCode'];
	$CInvDate = getFormatedDateNA($OrderRow['CInvDate']);
	$Variety = $OrderRow['Variety'];
	$Description = $OrderRow['Description'];
	$IGST = $OrderRow['IGST'];
	$HSNcode = $OrderRow['HSCode'];
	$Rate = $OrderRow['Price'];
	$ExchangeRate = $OrderRow['ExRate'];
	$CurrencyCode = $OrderRow['CurrencyCode'];
	$PortOfLoadingCode = $OrderRow['PortOfLoadingCode'];
	$DestinationCountryCode = $OrderRow['DestinationCountryCode'];
	$GstPerc = !empty($OrderRow['GstPerc']) ? $OrderRow['GstPerc'] : $OrderRow['GstPercP'];
	$QtyUnit = $OrderRow['QtyUnit'];
	$DestinationCountry = $OrderRow['DestinationCountry'];

	$SqlGradeData = "SELECT COUNT(*) AS Varieties FROM ScrmPoGradeData WHERE InvId = '$PoID'";
	$GradeDataExec = $iconn->query($SqlGradeData);
	$GradeRow = $GradeDataExec->fetch_assoc();

	$NoOfVarieties = $GradeRow['Varieties'];


	$ExporterData = getExporterDetails($ExpID);
	$TranshipmentDate = getFormatedDateNA($CustomInvoiceRow['TranshipmentDate']);

	/* $Testdata = getTestInvoiceValue($iconn, $GradeID, $PoID);
	echo $Testusd = $Testdata['UsdValue'];
	exit; */

	if ($FoBCalculation && $FoBCalculation == 1) {
		$checkSauceVariety = checkSauceVarietyExists($iconn, $GradeID);
		/* Added this code block to re calculate the prices based on FOB Value */
		if ($AdminInvConfigDetails['CLFCharges'] == 1) {
			$AdminConfigDetails['HasCLFCharges'] = 1;
		}

		/* Add date check if needed */
		if ($checkSauceVariety == 'Yes') {
			$sauceFlag = true;
			$AryVarietyData = getInvoiceValue($iconn, $GradeID, $PoID, $QtyUnit, $ExchangeRate);
			$InvoiceValue = $AryVarietyData['InvoiceValueINR'];
			$Quantity = $AryVarietyData['Qty'];
			$UnitPrice = $InvoiceValue / $Quantity;
			$GrossAmount = $InvoiceValue;
			$ShrimpPrice = $AryVarietyData['ShrimpPrice'];
			$SaucePrice = $AryVarietyData['SaucePrice'];

			$TotalInvoiceValue = getInvoiceValue($iconn, '', $PoID, $QtyUnit, $ExchangeRate)['InvoiceValueINR'];
			$InvPaymentValues = getShrimpPaymentValues($iconn, $PoID, '', $AdminConfigDetails, '');
			$FOBValueINR = $InvPaymentValues['FOBValueINR'];
			$GrossAmount = ($GrossAmount / $TotalInvoiceValue) * $FOBValueINR;
			$UnitPrice = $GrossAmount / $Quantity;
		} else {
			$Quantity = getQuantity($iconn, $GradeID, $QtyUnit);
			$UnitPrice = getUnitPrice($iconn, $GradeID, $QtyUnit, $ExchangeRate);
			$AryVarietyData = getInvoiceValue($iconn, $GradeID, $PoID, $QtyUnit, $ExchangeRate);
			$InvoiceValue = $AryVarietyData['InvoiceValueINR'];
			$GrossAmount = getGrossAmount($Quantity, $UnitPrice);
			$InvoiceValue = getInvoiceValue($iconn, '', $PoID, $QtyUnit, $ExchangeRate)['InvoiceValueINR'];
			if ($sauceFlag == true) {
				$InvPaymentValues = getShrimpPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetails, '');
			} else {
				$InvPaymentValues = getPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetails);
			}
			/* CHINA */
			// if ($DestinationCountry == 26) {
			// 	$FOBValueUSD = round($InvPaymentValues['InvValueUSD'] - $InvPaymentValues['FrieghtCharge'] - $InvPaymentValues['InsuranceValue'], 2);
			// 	$FOBValueINR = $FOBValueUSD * $ExchangeRate;
			// 	$GrossAmount = ($GrossAmount / $InvoiceValue) * $FOBValueINR;
			// 	$UnitPrice = $GrossAmount / $Quantity;
			// }
			// else if ($DestinationCountry == 13) { /* BELGIUM */
			// 	$InvoiceValueUsd = getInvoiceValue($iconn, $GradeID, $PoID, $QtyUnit, $ExchangeRate)['InvoiceValueUsd'];
			// 	$FreightCharge = $InvPaymentValues['FrieghtCharge'];
			// 	$FreightperVariety = $FreightCharge / $NoOfVarieties;
			// 	$FOBValueUSD = round($InvoiceValueUsd - $FreightperVariety, 2);
			// 	$GrossAmount = $FOBValueUSD * $ExchangeRate;
			// 	$UnitPrice = $GrossAmount / $Quantity;
			// }
			// else {
			// }
			if ($DestinationCountry == 26) {
				$InvValueUSD = $InvPaymentValues['InvValueUSD'];
				$FreightCharge = $InvPaymentValues['FrieghtCharge'];
				$ShippingTerm = $InvPaymentValues['PaymentTerm'];
				if ($ShippingTerm == 'CIF') {
					$InsurancePerc = getInsurancePerc($iconn, $DestinationCountry);
					$InsuranceValue = round(($InvValueUSD * $InsurancePerc) / 100, 2);
				}
				$FOBValueINR = ($InvValueUSD - $FreightCharge - $InsuranceValue) * $ExchangeRate;
			} else if ($DestinationCountry == 13) {
				$FOBValueINR = $InvPaymentValues['InvValueINRWithOutDuties'];
			} else {
				$FOBValueINR = $InvPaymentValues['FOBValueINR'];
			}
			$GrossAmount = ($GrossAmount / $InvoiceValue) * $FOBValueINR;
			$UnitPrice = $GrossAmount / $Quantity;
		}
	} else {
		$Quantity = getQuantity($iconn, $GradeID, $QtyUnit);
		$UnitPrice = getUnitPrice($iconn, $GradeID, $QtyUnit, $ExchangeRate);
		$GrossAmount = getGrossAmount($Quantity, $UnitPrice);
		$InvoiceValue = getInvoiceValue($iconn, $GradeID, $PoID, $QtyUnit, $UnitPrice);
		$FOBValueINR = $InvoiceValue;
	}

	$GstData = getIgstvalue($IGST, $GstPerc, $GrossAmount, $FOBValueINR);


	$ETD = getFormatedDateNA($CustomInvoiceRow['ETD']);
	$ETA = getFormatedDateNA($CustomInvoiceRow['ETA']);

	$Data['IsSauce'] = $checkSauceVariety;
	// $Data['SupplyTypeCode'] = $IGST == 'IGST' ? 'EXPWP' : 'EXPWOP';
	// $Data['ReverseCharge'] = $IGST == 'IGST' ? 'GST' : 'NO GST';
	$Data['SupplyTypeCode'] = 'EXPWOP';
	$Data['ReverseCharge'] = '';
	$Data['E_Comm_GSTIN'] = '';
	$Data['IgstOnIntra'] = 'No';
	$Data['DocumentType'] = 'Tax Invoice';
	$Data['DocumentNumber'] = $DocumentNumber = $OrderRow['CustomsInvNo'];
	$Data['DocumentDate'] = $DocumentDate = getCustomInvDate($CInvDate);
	$Data['BuyerGSTIN'] = 'URP';
	$Data['BuyerLegalName'] = $BuyerLegalName;
	$Data['BuyerTradeName'] = $BuyerTradeName;
	$Data['BuyerPOS'] = 'OTHER COUNTRIES';
	$Data['BuyerAddr1'] = $BuyerAddr1;
	$Data['BuyerAddr2'] = $BuyerAddr2;
	$Data['BuyerLocation'] = $BuyerLocation;
	$Data['BuyerPinCode'] = $BuyerPinCode;
	$Data['BuyerState'] = 'OTHER COUNTRIES';
	$Data['BuyerPhoneNumber'] = '';
	$Data['BuyerEmailId'] = '';
	$Data['DispatchName'] = '';
	$Data['DispatchAddr1'] = '';
	$Data['DispatchAddr2'] = '';
	$Data['DispatchLocation'] = '';
	$Data['DispatchPinCode'] = '';
	$Data['DispatchState'] = '';
	$Data['ShippingGSTIN'] = '';
	$Data['ShippingLegalName'] = '';
	$Data['ShippingTradeName'] = '';
	$Data['ShippingAddr1'] = '';
	$Data['ShippingAddr2'] = '';
	$Data['ShippingLocation'] = '';
	$Data['ShippingPinCode'] = '';
	$Data['ShippingState'] = '';
	$Data['SlNo'] = '';
	$Data['ProductDescription'] = getProdDesc($Variety, $Description);
	$Data['Is_Service'] = 'NO';
	$Data['HSNcode'] = $HSNcode;
	$Data['Barcode'] = '';
	$Data['Quantity'] = $Quantity;
	$Data['FreeQuantity'] = '';
	$Data['Unit'] = 'KILOGRAMS';
	$Data['UnitPrice'] = $UnitPrice;
	$Data['ShrimpPrice'] = $ShrimpPrice;
	$Data['SaucePrice'] = $SaucePrice;
	$Data['GrossAmount'] = $GrossAmount;
	$Data['InvDiscount'] = '';
	$Data['PreTaxValue'] = '';
	$Data['Taxablevalue'] = $GrossAmount;
	$Data['FOBValueINR'] = $FOBValueINR;
	$Data['GSTRatePerc'] = $GstData['GSTRatePerc'];
	$Data['InvSgstAmt'] = '';
	$Data['InvCgstAmt'] = '';
	$Data['InvIgstAmt'] = $GstData['IgstAmount'];
	$Data['InvCessRatePerc'] = '';
	$Data['InvCessAmtAdval'] = '';
	$Data['InvCessNonAdvalAmt'] = '';
	$Data['InvStateCessRatePerc'] = '';
	$Data['InvStateCessAdvalAmt'] = '';
	$Data['InvStateCessNonAdvalAmt'] = '';
	$Data['InvOtherCharges'] = '';
	$Data['ItemTotal'] = $GrossAmount + $GstData['IgstAmount'];
	$Data['BatchName'] = '';
	$Data['BatchExpiryDate'] = '';
	$Data['WarrantyDate'] = '';
	$Data['TotalTaxableValue'] = $FOBValueINR;
	$Data['BatchSgstAmt'] = '';
	$Data['BatchCgstAmt'] = '';
	$Data['BatchIgstAmt'] = $GstData['TotalIgstAmount'];
	$Data['BatchCessAmt'] = '';
	$Data['BatchStateCessAmt'] = '';
	$Data['BatchDiscount'] = '';
	$Data['BatchOtherCharges'] = '';
	$Data['RoundOff'] = '';
	$Data['TotalInvoicevalue'] = $InvoiceValue;
	$Data['ShippingBillNo'] = '';
	$Data['ShippingBillDt'] = '';
	$Data['Port'] = $PortOfLoadingCode;
	$Data['RefundClaim'] = '';
	$Data['ForeignCurrency'] = $CurrencyCode;
	$Data['CountryCode'] = $DestinationCountryCode;
	$Data['ExportDutyAmount'] = '';
	$Data['TransID'] = '';
	$Data['TransName'] = '';
	$Data['TransMode'] = '';
	$Data['Distance'] = '';
	$Data['TransDocNo'] = '';
	$Data['TransDocDate'] = '';
	$Data['VehicleNo'] = '';
	$Data['VehicleType'] = '';
	$Data['ErrorList'] = '';



	return $Data;
}

function getCustomInvDate($CInvDate)
{
	$CInvDate = strtotime($CInvDate);
	return $CInvDate = date("d/m/Y", $CInvDate);
}

function getProdDesc($Variety, $Description)
{
	if ($Description == '') {
		return $Variety;
	} else {
		return $Description;
	}
}

function getQuantity($iconn, $GradeID, $QtyUnit)
{
	if ($QtyUnit == 'KGS') {
		$Quantity = getInvoiceWeight($iconn, $GradeID)['NetWgtKgs'];
	} else {
		$Quantity = getInvoiceWeight($iconn, $GradeID)['NetWgtLbs'];
		$Quantity = $Quantity * 0.454;
	}
	// echo $Quantity = str_replace(',', '', $Quantity);
	return $Quantity;
}

function getUnitPrice($iconn, $GradeID, $QtyUnit, $ExchangeRate)
{


	$UnitPrice = (getInvoiceWeight($iconn, $GradeID)['UnitPrice']) * $ExchangeRate;


	return $UnitPrice;
}

function getCountryDetails($iconn, $CountryID)
{

	$SqlCountries = "SELECT * FROM `ScrmCountriesList` where id='$CountryID'";
	$StmtCountries = $iconn->query($SqlCountries);
	$RowCountries = $StmtCountries->fetch_assoc();
	return $RowCountries['name'];
}

function getGrossAmount($Quantity, $UnitPrice)
{
	return ($Quantity * $UnitPrice);
}

function getInvoiceValue($iconn, $GradeID, $PoID, $QtyUnit, $ExchangeRate)
{

	$Qry = '';
	if ($GradeID != null) {
		$Qry = "ID='$GradeID' AND";
	}
	/* $SqlTotal = "SELECT
			sum(Mcs*TotalSlabs*SlabWeight*Price *ExRate* Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * ExRate*LbsFactor) as PriceLbs FROM
			`ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID
			left join ScrmCustomInvoices as SCI on SCI.PiID=PGD.InvId
			WHERE InvId='$PoID'"; */

	$SqlTotal = "SELECT sum(Mcs) as MCS,
			sum(Price) as Price,PS.Uom,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price ELSE 0 END) AS PriceOZKgs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * Price ELSE 0 END) AS PriceOZLbs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * MFactor ELSE 0 END) AS PriceKgs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor ELSE 0 END) AS PriceLbs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454
			ELSE Mcs * TotalSlabs * SlabWeight * MFactor END) AS NetWgtKgs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor
			ELSE Mcs * TotalSlabs * SlabWeight * LbsFactor END) AS NetWgtLbs,
			sum(Mcs*TotalSlabs*SlabWeight*MFactor*Price / 0.454) AS UsdValue,
			GlazeStatus,
			VL.GlazePerc,ISF.BLDate,
			SPD.QtyUnit As QtyUnit
			FROM `ScrmPoGradeData` as PGD
			LEFT JOIN (
                SELECT
                    *,
                    CASE
                        WHEN HasSauce = 'Yes' THEN ShrimpPackingStyle
                        ELSE PackingStyle
                    END AS EffectivePackingStyle
                FROM ScrmPIVarietyList
            ) AS VL ON VL.VarietyID = PGD.VarietyID
			left join ScrmISFDetails as ISF on ISF.PIID=PGD.InvId
			left join ScrmPoData as SPD on SPD.PoID=PGD.InvId
			left join AdminPackingStyles as PS on VL.EffectivePackingStyle=PS.PCKID WHERE $Qry InvId='$PoID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	$IsSauce = checkSauceExists($iconn, $PoID);
	$SauceTotalUsdValue = 0;
	$SauceTotalKgs = 0;
	$SauceTotalLbs = 0;
	$CupsPrice = 0;
	if ($IsSauce && $IsSauce == 1) {
		$SqlSauce = "SELECT PGD.Mcs, PGD.VarietyID, PGD.CUPS, PGD.CupsPrice, PS.*, SPD.QtyUnit
			FROM ScrmPoGradeData as PGD
			LEFT JOIN ScrmPoData as SPD ON SPD.PoID = PGD.InvId
			LEFT JOIN ScrmPIVarietyList as SPVL ON SPVL.VarietyID = PGD.VarietyID
			LEFT JOIN AdminPackingStyles as PS ON SPVL.SaucePackingStyle = PS.PCKID
			WHERE InvId='$PoID' AND SPVL.HasSauce = 'Yes'";
		$StmtSauce = $iconn->query($SqlSauce);
		while ($RowSauce = $StmtSauce->fetch_assoc()) {
			$SauceLbsWeight = $RowSauce['TotalSlabs'] * $RowSauce['SlabWeight'] * $RowSauce['Mcs'];
			if ($RowSauce['Uom'] == 'OZ') {
				$OzToLbs = $RowSauce['SlabWeight'] * $RowSauce['LbsFactor'];
				$SauceLbsWeight *= $OzToLbs / $RowSauce['SlabWeight'];
			}
			$SauceKgsWeight = $SauceLbsWeight * 0.454;
			if ($RowSauce['QtyUnit'] == 'LBS') {
				$QtyCalc = $SauceKgsWeight;
				$RowSauce['CupsPrice'] = $RowSauce['CupsPrice'] / 0.454;
			} else {
				$QtyCalc = $SauceLbsWeight;
			}
			$SauceTotalUsdValue += $QtyCalc * $RowSauce['CupsPrice'];
			$SauceTotalKgs += $SauceKgsWeight;
			$SauceTotalLbs += $SauceLbsWeight;
			$CupsPrice = $RowSauce['CupsPrice'];
		}
	}

	if ($QtyUnit == 'KGS') {
		$WeightKgs = $RowTotal['NetWgtKgs'] + $SauceKgsWeight;
		$WeightKgs += $SauceTotalKgs;
		$InvoiceValue = $RowTotal['PriceOZKgs'] > 0 ? $RowTotal['PriceOZKgs'] + $RowTotal['PriceKgs'] : $RowTotal['PriceKgs'];
		$AryPriceData['ShrimpValueUsd'] = $InvoiceValue;
		$AryPriceData['SauceValueUsd'] = $SauceTotalUsdValue;
	} else {
		$WeightLbs = $RowTotal['NetWgtLbs'];
		$WeightLbs += $SauceTotalLbs;
		$InvoiceValue = $RowTotal['PriceOZLbs'] > 0 ? $RowTotal['PriceOZLbs'] + $RowTotal['PriceLbs'] : $RowTotal['PriceLbs'];
		$AryPriceData['ShrimpValueUsd'] = $InvoiceValue;
		$AryPriceData['SauceValueUsd'] = $SauceTotalUsdValue;
		// $InvoiceValue += $SauceTotalUsdValue * 0.454;
	}

	$InvoiceValue += $SauceTotalUsdValue;

	$AryPriceData['ShrimpPrice'] = $RowTotal['Price'];
	$AryPriceData['SaucePrice'] = $CupsPrice;
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	// $AryPriceData['Qty'] = $Qty;
	$AryPriceData['InvoiceValueUsd'] = $InvoiceValue;
	$AryPriceData['ExchangeRate'] = $ExchangeRate;
	$AryPriceData['InvoiceValueINR'] = $InvoiceValue * $ExchangeRate;

	$AryPriceData['NetWgtKgs'] = $WeightKgs;
	$AryPriceData['NetWgtLbs'] = $WeightLbs;
	$AryPriceData['Qty'] = $WeightLbs * 0.454;

	return $AryPriceData;
}

function getIgstvalue($IGST, $GstPerc, $GrossAmount, $InvoiceValue)
{
	if ($IGST == 'IGST') {
		$GSTRatePerc = $GstPerc;
		$IgstAmount = ($GrossAmount * $GSTRatePerc) / 100;
		$TotalIgstAmount = ($InvoiceValue * $GSTRatePerc) / 100;
	} else {
		$GSTRatePerc = '0';
		$IgstAmount = '';
		$TotalIgstAmount = '0';
	}

	$ArrayGstValues['GSTRatePerc'] = $GSTRatePerc;
	$ArrayGstValues['IgstAmount'] = $IgstAmount;
	$ArrayGstValues['TotalIgstAmount'] = $TotalIgstAmount;
	return $ArrayGstValues;
}

function checkSauceVarietyExists($iconn, $PoGradeID)
{
	$SqlQuery = "SELECT HasSauce From ScrmPoGradeData AS SPGD
	LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = SPGD.VarietyID where SPGD.ID = $PoGradeID";
	$Stmt = $iconn->query($SqlQuery);
	$Row = $Stmt->fetch_assoc();
	return $Row['HasSauce'];
}
