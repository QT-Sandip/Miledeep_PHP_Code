<?php

session_start();


$PoID = $_GET['id'];
$Type = $_GET['Type'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");


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
				<th   align="center"><b>Igst On Intra</b></th>
				<th   align="center"><b>Document Type</b></th>
				<th  align="center"><b>Document Number</b></th>
				<th  align="center"><b>Document Date (DD/MM/YYYY)</b></th>
				<th  align="center"><b>Buyer GSTIN</b></th>
				<th  align="center"><b>Buyer Legal Name</b></th>
				<th   align="center"><b>Buyer Trade Name</b></th>
				<th   align="center"><b>Buyer POS</b></th>
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

	$AryPriceBreakUp = getBreakUpData($iconn, $RowBreakup['ID']);
	$Qty = $AryPriceBreakUp['Qty'];
	$UsdValue = $AryPriceBreakUp['UsdValue'];
	$TotalMcs = $AryPriceBreakUp['TotalMcs'];
	$Price = $AryPriceBreakUp['Price'];
	$UnitPrice = $AryPriceBreakUp['UnitPrice'];

	$AryPaymentData = getPaymentValues($iconn, $PoID, '');


	$InvValueUSD = $AryPaymentData['InvValueUSD'];
	$InvValueInr = $AryPaymentData['InvValueINR'];
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$DiscountPerc = $AryPaymentData['DiscountPerc'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$IgstPerc = $AryPaymentData['IgstPerc'];
	$IgstValue = $AryPaymentData['IgstValue'];

	// $IgstPerc = $IgstPerc ? $IgstPerc : $ArrayData['GSTRatePerc'];

	$FOBValueINR = $AryPaymentData['FOBValueINR'];
	$IgstValueFOB = ($FOBValueINR * $IgstPerc) / 100;



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
	$UnitPrice = $AryPriceBreakUp['UnitPrice'];
	$GrossAmount = $AryPaymentData['FOBValueINR'];
	$Discount = $AryPaymentData['DiscountAmount'];
	$PreTaxValue = $ArrayData['PreTaxValue'];
	$Taxablevalue = $GrossAmount;
	$GSTRatePerc = $ArrayData['GSTRatePerc'];
	$InvSgstAmt = $ArrayData['InvSgstAmt'];
	$InvCgstAmt = $ArrayData['InvCgstAmt'];
	$InvIgstAmt = $IgstValueFOB;
	$InvCessRatePerc = $ArrayData['InvCessRatePerc'];
	$InvCessAmtAdval = $ArrayData['InvCessAmtAdval'];
	$InvCessNonAdvalAmt = $ArrayData['InvCessNonAdvalAmt'];
	$InvStateCessRatePerc = $ArrayData['InvStateCessRatePerc'];
	$InvStateCessAdvalAmt = $ArrayData['InvStateCessAdvalAmt'];
	$InvStateCessNonAdvalAmt = $ArrayData['InvStateCessNonAdvalAmt'];
	$InvOtherCharges = $ArrayData['InvOtherCharges'];
	$ItemTotal = $GrossAmount + $IgstValueFOB;
	$BatchName = $ArrayData['BatchName'];
	$BatchExpiryDate = $ArrayData['BatchExpiryDate'];
	$WarrantyDate = $ArrayData['WarrantyDate'];
	$TotalTaxableValue = $GrossAmount;

	$BatchSgstAmt = $ArrayData['BatchSgstAmt'];
	$BatchCgstAmt = $ArrayData['BatchCgstAmt'];
	$BatchIgstAmt = $AryPaymentData['IgstValue'];
	$BatchCessAmt = $ArrayData['BatchCessAmt'];
	$BatchStateCessAmt = $ArrayData['BatchStateCessAmt'];
	$BatchDiscount = $ArrayData['BatchDiscount'];
	$BatchOtherCharges = $ArrayData['BatchOtherCharges'];
	$RoundOff = $ArrayData['RoundOff'];
	//$TotalInvoicevalue=$ArrayData['TotalInvoicevalue'];
	$TotalInvoicevalue = $ItemTotal;
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
			    <td  align="center">' . round($UnitPrice, 3) . '</td>
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

	/* if($PType=='KGS'){
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

function getBreakUpDataExcel($iconn, $GradeID)
{
	//$GradeID=14;


	$CustomInvoiceSql = "SELECT *,SP.PortCode as PortOfLoadingCode,SC.GstCountryCode as DestinationCountryCode
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
	$BuyerPinCode = '999999';//$OrderRow['ConsigneeZipCode'];
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
	$GstPerc = $OrderRow['GstPerc'];
	$QtyUnit = $OrderRow['QtyUnit'];




	$ExporterData = getExporterDetails($ExpID);
	$TranshipmentDate = getFormatedDateNA($CustomInvoiceRow['TranshipmentDate']);

	/*$Testdata=getTestInvoiceValue($iconn,$GradeID,$PoID);
															  echo $Testusd=$Testdata['UsdValue'];exit;*/

	$Quantity = getQuantity($iconn, $GradeID, $QtyUnit);
	$UnitPrice = getUnitPrice($iconn, $GradeID, $QtyUnit, $ExchangeRate);
	$GrossAmount = getGrossAmount($Quantity, $UnitPrice);
	$InvoiceValue = getInvoiceValue($iconn, $GradeID, $PoID, $QtyUnit, $UnitPrice);
	$GstData = getIgstvalue($IGST, $GstPerc, $GrossAmount, $InvoiceValue);




	$ETD = getFormatedDateNA($CustomInvoiceRow['ETD']);
	$ETA = getFormatedDateNA($CustomInvoiceRow['ETA']);

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
	$Data['GrossAmount'] = $GrossAmount;
	$Data['InvDiscount'] = '';
	$Data['PreTaxValue'] = '';
	$Data['Taxablevalue'] = $GrossAmount;
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
	$Data['TotalTaxableValue'] = $InvoiceValue;
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
	$Quantity = str_replace(',', '', $Quantity);
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
function getInvoiceValue($iconn, $GradeID, $PoID, $QtyUnit, $UnitPrice)
{



	$SqlTotal = "SELECT			
			sum(Mcs*TotalSlabs*SlabWeight*Price *ExRate* Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * ExRate*LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` as PGD 
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID 
			left join ScrmCustomInvoices as SCI on SCI.PiID=PGD.InvId 
			WHERE InvId='$PoID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();


	if ($QtyUnit == 'KGS') {

		$UsdValue = $RowTotal['PriceKgs'];


	} else {
		$UsdValue = $RowTotal['PriceLbs'];
	}




	return $UsdValue;


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



?>