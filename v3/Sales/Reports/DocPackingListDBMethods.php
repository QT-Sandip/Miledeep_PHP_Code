<?php
function getReportData($iconn, $PoID)
{

	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryShowConditions, $DocType;

	$ShowCWiseLc = $AryShowConditions['LcNo'];
	$ShowCWiseLcDate = $AryShowConditions['LcDate'];
	$ShowCWiseLcIssuedBy = $AryShowConditions['LcIssuedBy'];
	$ShowCWiseComSplNote = $AryShowConditions['ComSplNote'];
	$ShowCWiseComDesNote = $AryShowConditions['ComDesNote'];
	$ShowCWiseBuyer = $AryShowConditions['Buyer'];
	$ShowCWiseProductSpecification = $AryShowConditions['ProductSpecification'];
	$ShowCWiseSpecification = $AryShowConditions['Species'];
	$ShowCWiseGrossWeight = $AryShowConditions['GrossweightKgs'];
	$ShowCWiseNetWeight = $AryShowConditions['NetweightKgs'];





	$CustomInvoiceSql = "SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
        SC.name as PortofDischargeCountry,
		 SCD.LcNo as CLcNo,
        SCD.LcDate as CLcDate,
		SCD.ShowBLNo,
        SCD.ShowBLDate,SCD.ShowShippingDetails,
        SCD.IssuedBy as CIssuedBy,
		SCD.ComDescriptionSpl as CComDescriptionSpl,ComRemarks as PackingRemarks,
		SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading ,
		SCV.ContainerNo	as ContainerNo ,SPCF.Name as FinalDestination,SCV.ByRoad,SCV.CustomsInvNo,SCV.CustomsInvNo,SBD.ShippingBillNo
		,SCD.ComShippingTermExt, SCFDC.name as FinalDestinationCountry 
		FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID 
		 left JOIN ScrmCountriesList as SCL on SSD.DestinationCountry=SCL.id
	    left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
		left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SL on SL.id=SP.Country
 		left JOIN ScrmCountriesList as SCFDC on SCFDC.id=SSD.FinalDestinationCountry

		left JOIN ScrmISFDetails as SID on SID.PIID=SCV.PiID
		left JOIN ScrmCommercialData as SCD on SCD.PoID=SCV.PiID
		left JOIN ScrmPaymentTerms as SPT on SCD.ComPaymentTerms=SPT.ID
		left JOIN ScrmShippingBillDetails as SBD on SCV.PiID =  SBD.InvID

		WHERE SCV.PiID='$PoID'";

	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvData = $CustomInvoiceExec->fetch_assoc();
	$IGST = $CustomInvData['IGST'];
	$OverSeasCommission = $CustomInvData['OverSeasCommission'];
	$CInvDate = $CustomInvData['CInvDate'];
	$CInvCode = $CustomInvData['CInvCode'];
	$NoOfDays = $CustomInvData['NoOfDays'];
	$TotalGrossWt = $CustomInvData['TotalGrossWt'];
	$ComInvDescription = $CustomInvData['CComDescriptionSpl'];
	$PackingRemarks = $CustomInvData['PackingRemarks'];
	$VesselNumber = $CustomInvData['OriginatingVessel'] . '<br> ' . $CustomInvData['VoyOrg'];
	$BLDate = getFormatedDateDMMY($CustomInvData['BLDate']);
	$SBDate = getFormatedDateNA($CustomInvData['SBDate']);
	$BLNo = $CustomInvData['BLNo'];
	$SBNo = $CustomInvData['SBNo'];
	$DataLogger = $CustomInvData['DataLogger'];
	$DataLoggerPositions = $CustomInvData['DataLoggerPositions'];
	$LcNo = $CustomInvData['CLcNo'];
	$LcDate = getFormatedDateNA($CustomInvData['CLcDate']);
	$LcIssuedBy = ($CustomInvData['CIssuedBy']);
	$ComDeliveryTerms = $CustomInvData['ComDeliveryTerms'];
	$PortOfLoading = $CustomInvData['PortOfLoading'];
	$ComPaymentTerms = $CustomInvData['PaymentName'];
	$ByRoad = $CustomInvData['ByRoad'];
	$ShowBLDate = $CustomInvData['ShowBLDate'];
	$ShowBLNo = $CustomInvData['ShowBLNo'];
	$ShippingBillNo = $CustomInvData['ShippingBillNo'];
	$ShippingBillDate = $CustomInvData['ShippingBillDate'];
	$ShowShippingDetails = $CustomInvData['ShowShippingDetails'];

	$SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 
FromTOPID='$PortOfLoading' and PIID='$PoID'";
	//exit;
	$StmtOrgVessel = $iconn->query($SqlOrgVessel);
	$RowOrgVessel = $StmtOrgVessel->fetch_assoc();

	$OrgVesselName = $RowOrgVessel['VesselName'];
	$OrgVOYNO = $RowOrgVessel['VOYNO'];


	//	$ComPaymentTerms=$CustomInvData['PaymentName'];
	if ($NoOfDays == 0) {
		$NoOfDays = '';
	} else {

		$NoOfDays = $NoOfDays . ' DAYS FROM BL';
	}

	$SqlMcsTotal = "SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
	$StmtMcsTotal = $iconn->query($SqlMcsTotal);
	$RowMcs = $StmtMcsTotal->fetch_assoc();
	$TotalMcs = $RowMcs['Mcs'];




	$SqlScrmPoData = "SELECT *,SPD.Status as PoStatus,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,
SCC.name as ConsigneeCountry,
ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,
ABD.SwiftCode as SwiftCode,
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode ,SCL.name as DestinationCountryName
	FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
  left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
  left JOIN ScrmCountriesList as SCC on SCC.id=SCD.ConsigneeCountry
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country 
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
  left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 WHERE PoID='$PoID'";


	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ApprovedBy = $PoDataRow['ApprovedBy'];
	$CountryID = $PoDataRow['DestinationCountry'];

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

	/* if ($CustomInvData['Discount'] == 'DISCOUNT') {
		$AryData['showDiscount'] = true;
	} else {
		$AryData['showDiscount'] = false;
	} */

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

	if ($CustomInvData['DataLogger'] != '') {
		$AryData['ShowDataLogger'] = true;	//uma
	} else {
		$AryData['ShowDataLogger'] = false;
	}




	//if($PoDataRow['DestinationCountry']==26){
	if ($PoDataRow['DestinationCountry'] != 26 && $PoDataRow['ShippingTerms'] != 'FOB') {  //uma
		$AryData['ShowDeliveryTerms'] = true;
	} else {
		$AryData['ShowDeliveryTerms'] = false;
	}

	if ($PoDataRow['ImporterID'] != 0) {
		$AryData['showImporter'] = true;
	} else {
		$AryData['showImporter'] = false;
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
	$AryData['showExpGSTNo'] = true;
	/* if ($ExpID == 5) {
		$AryData['showExpIECode'] = true;
	} else {
		$AryData['showExpIECode'] = false;
	} */

	/* if ($ExpID == 5) {
		$AryData['showExpGSTNo'] = true;
	} else {
		$AryData['showExpGSTNo'] = false;
	} */

	if ($PoDataRow['ShowBank'] != 0) {
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

	$AryData['ShowCWiseWeightPerctText'] = $AryShowConditions['WeightPerctText'];



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


	if ($ComPaymentTerms != '') {
		$PaymentTerms = $CustomInvData['PaymentName'];
	} else {
		$PaymentTerms = $PoDataRow['PaymentName'];
	}
	//$PaymentTerms=$PoDataRow['PaymentName']	;


	/* Return Data*/
	$AryData['TotalGrossWtKgs'] = $TotalGrossWt;
	$AryData['PoStatus'] = $PoDataRow['PoStatus'];
	$AryData['ApprovedBy'] = $ApprovedBy;
	//$AryData['PaymentTerms']=$PaymentTerms; 
	$AryData['DeliveryTerms'] = $DeliveryTerms;
	$AryData['TotalMcs'] = $TotalMcs;
	$AryData['ExporterID'] = $ExpID;
	$AryData['DataLoggerPositions'] = $DataLoggerPositions;
	$AryData['DataLogger'] = $DataLogger;
	$AryData['ConsigneeCountry'] = $PoDataRow['ConsigneeCountry'];


	$AryData['ExporterName'] = $ExporterData['ExpName'];
	;
	$AryData['ExporterAddress'] = $ExporterData['ExpAddress'];
	$AryData['ExporterUsfdaNo'] = $ExporterData['ExpUsfdaNo'];
	$AryData['ExporterIeCode'] = $ExporterData['ExpIeCode'];
	$AryData['ExporterGstNo'] = $ExporterData['ExpGstNo'];
	$AryData['DestinationCountryId'] = $ExporterData['DestinationCountry'];
	$AryData['CountryId'] = $PoDataRow['DestinationCountry'];
	$AryData['ExporterRecAdd'] = $ExporterData['ImporterRecordAddress'];
	$AryData['AuthSign'] = $ExporterData['AuthSign'];
	$AryData['ShowCWiseLc'] = $ShowCWiseLc;
	$AryData['ComInvDescription'] = $ComInvDescription;
	$AryData['PackingRemarks'] = $PackingRemarks;
	$AryData['ShowCWiseLcDate'] = $ShowCWiseLcDate;
	$AryData['ShowCWiseLcIssuedBy'] = $ShowCWiseLcIssuedBy;
	$AryData['ShowCWiseWeightPerctText'] = $AryShowConditions['WeightPerctText'];
	$AryData['ShowCWiseNotifyPrty'] = $AryShowConditions['NotifyPrty'];
	$AryData['ShowCWiseComDesNote'] = $ShowCWiseComDesNote;
	$AryData['ShowCWiseComSplNote'] = $ShowCWiseComSplNote;
	$AryData['ShowCWiseBuyer'] = $ShowCWiseBuyer;

	$AryData['OrgVesselName'] = $OrgVesselName;
	$AryData['OrgVOYNO'] = $OrgVOYNO;
	$AryData['ByRoad'] = $ByRoad;
	$AryData['ShowCWiseProductSpecification'] = $ShowCWiseProductSpecification;
	$AryData['ShowCWiseSpecification'] = $ShowCWiseSpecification;
	$AryData['ShowCWiseGrossWeight'] = $ShowCWiseGrossWeight;
	$AryData['ShowCWiseNetWeight'] = $ShowCWiseNetWeight;
	$AryData['ShowCWiseUsfda'] = $AryShowConditions['Usfda'];
	$AryData['ShowCWiseApprovalNo'] = $AryShowConditions['ApprovalNo'];
	$AryData['ShowShippingBillnDate'] = $AryShowConditions['ShippingBillNoAndDate'];


	if ($ShowCWiseLc && $ShowCWiseLcDate && $ShowCWiseLcIssuedBy) {
		//$AryData['PaymentTerms']=$PaymentTerms.' LC NO.'.$LcNo.' DATED '.$LcDate.','.$LcIssuedBy;
		if ($LcNo == '') {
			$AryData['PaymentTerms'] = $PaymentTerms;
		} else {
			$AryData['PaymentTerms'] = $PaymentTerms . ' LC NO.' . $LcNo . ', DATED ' . $LcDate . ',' . $LcIssuedBy;
		}
	} else {
		$AryData['PaymentTerms'] = $PaymentTerms;
	}

	if ($ByRoad != '') {
		$FinalDestination = $ByRoad;
	} else {
		$FinalDestination = $CustomInvData['FinalDestination'];
		$FinalDestinationCountry = $CustomInvData['FinalDestinationCountry'] ? strtoupper($CustomInvData['FinalDestinationCountry']) : '';

	}

	$AryData['ConsigneeID'] = $PoDataRow['ConsigneeID'];
	$AryData['ReferenceNo'] = $PoDataRow['ReferenceNo'];
	$AryData['BuyerID'] = $PoDataRow['BuyerID'];
	$AryData['ConsigneeName'] = $PoDataRow['ConsigneeName'];
	$AryData['ConsigneeAddress'] = $PoDataRow['ConsigneeAddress'];
	$AryData['BuyerName'] = $PoDataRow['BuyerName'];
	$AryData['BuyerAddress'] = $PoDataRow['BuyerAddress'];
	$AryData['DestinationCountryName'] = $PoDataRow['DestinationCountryName'];



	$AryData['PortOfDischarge'] = strtoupper($CustomInvData['PortofDischarge']) . ' ' . strtoupper($CustomInvData['CountryCode']) . ', ' . $CustomInvData['PortofDischargeCountry'];

	$AryData['PlaceOfReceiptBy'] = $CustomInvData['PlaceOfRecipt'];
	$AryData['PortOfLoading'] = strtoupper($CustomInvData['PortOfLoading']) . ',' . strtoupper($CustomInvData['PortOfLoadingCountry']);
	$AryData['FinalDestination'] = strtoupper($FinalDestination);
	$AryData['FinalDestinationCountry'] = strtoupper($FinalDestinationCountry);

	$AryData['VesselNumber'] = $VesselNumber;

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
	$AryData['DestinationCountry'] = $PoDataRow['name'];
	$AryData['DeliveryTerms'] = $PoDataRow['DeliveryTerms'];
	$AryData['QtyUnit'] = $PoDataRow['QtyUnit'];
	$AryData['ShipByDate'] = $PoDataRow['ShipByDate'];
	$AryData['SignURL'] = $SignURL;
	$AryData['PreparedBy'] = $PoDataRow['PreparedBy'];
	$AryData['BankID'] = $PoDataRow['BankID'];
	$AryData['IGST'] = $IGST;
	$AryData['CInvCode'] = $CInvCode;
	$AryData['NoOfDays'] = $NoOfDays;
	$AryData['UnitCurrency'] = $PoDataRow['UnitCurrency'];


	$AryData['NotifyPartyName'] = $PoDataRow['NotifyPartyName'];
	$AryData['NPartyAddress'] = $PoDataRow['NPartyAddress'];
	$AryData['NPartyContactNumber'] = $PoDataRow['NPartyContactNumber'];
	$AryData['ImporterName'] = $PoDataRow['ImporterName'];
	$AryData['ImporterAddress'] = $PoDataRow['ImporterAddress'];


	$AryData['ContainerNo'] = $CustomInvData['ContainerNo'];
	// $AryData['SealNo']=$CustomInvData['SealNo'];
	$AryData['SealNo'] = $CustomInvData['LinerSealNo'];
	$AryData['IRNNumber'] = $CustomInvData['IRNNumber'];
	$AryData['BLDate'] = $BLDate;
	$AryData['SBDate'] = $SBDate;
	$AryData['BLNo'] = $BLNo;
	$AryData['SBNo'] = $SBNo;
	$AryData['ShippingBillNo'] = $ShippingBillNo;
	$AryData['ShippingBillDate'] = $ShippingBillDate;
	$AryData['ShowShippingDetails'] = $ShowShippingDetails;

	$AryData['ComShippingTermExt'] = $CustomInvData['ComShippingTermExt'];

	return $AryData;
}


function getBreakUpData($iconn, $GradeID, $PiID = null)
{
	global $PType;

	if ($PiID == '') {
		$Qry = "WHERE PGD.ID in ($GradeID)";
	} else {
		$Qry = "WHERE InvId='$PiID'";
	}

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
				sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as GlazeWeightKgs,
				 sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as GlazeWeightLbs";

	/*$SqlTotal="SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
				   sum(Price) as Price,			
				   sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
				   sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
				   `ScrmPoGradeData` 
				   as PGD left join AdminPackingStyles as PS 
				   on PGD.PackingStyleID=PS.PCKID $Qry"; */

	$SqlTotal = "SELECT sum(Mcs) as MCS,InvId,GlazePerc,sum(GrossWt) as EnteredGrossWt, $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 	as PGD 
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID
			
			$Qry";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];
	$GlazePerc = $RowTotal['GlazePerc'];



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

	/* if ($PType == 'KGS') {
		$QtyUnit = 'KGS';
	} */

	$EnteredGrossWt = $RowTotal['EnteredGrossWt'];
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
		$GrossWeight = $RowTotal['GrossWeightKgs'];
		$GlazeWeight = $RowTotal['GlazeWeightKgs'];
		//echo $AryPriceData['ProductWeightWOG']=$NetWgtKgs-$GlazeWeightKgs;exit;


	} else {
		$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $RowTotal['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
		$GrossWeight = $RowTotal['GrossWeightLbs'];
		$GlazeWeight = $RowTotal['GlazeWeightLbs'];
		//$AryPriceData['ProductWeightWOG']=$NetWgtLbs-$GlazeWeightLbs;
	}



	/*if($QtyUnit=='KGS'){
					   //$AryPriceData['Qty']=$Qty;
					   $AryPriceData['Qty']=$RowTotal['NetWgtKgs'];
				   }

				   else{
					   $AryPriceData['Qty']=$RowTotal['NetWgtLbs']; 
				   }
				   */
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

	//  $AryPriceData['GrossWeight']=$GrossWeight;
	$AryPriceData['NetWgtKgs'] = $NetWgtKgs;
	$AryPriceData['NetWgtLbs'] = $NetWgtLbs;
	$AryPriceData['GrossWeightKgs'] = $GrossWeightKgs;
	$AryPriceData['GrossWeightLbs'] = $GrossWeightLbs;
	$GlazeWeightKgs = $RowTotal['GlazeWeightKgs'];
	$GlazeWeightLbs = $RowTotal['GlazeWeightLbs'];
	$AryPriceData['ProductWeightWOGKgs'] = $NetWgtKgs - $GlazeWeightKgs;
	$AryPriceData['ProductWeightWOGLbs'] = $NetWgtLbs - $GlazeWeightLbs;
	$AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;

	if ($EnteredGrossWt != 0) { //uma
		$AryPriceData['GrossWeight'] = $EnteredGrossWt;
	} else {
		$AryPriceData['GrossWeight'] = $GrossWeight;
	}

	//$AryPriceData['EnteredGrossWt']=$EnteredGrossWt;
	//$AryPriceData['ProductWeightWOG']=$Qty-$GlazeWeight;

	return $AryPriceData;
}


function getBankDetails($iconn, $BankID)
{

	$SqlBank = "SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$AryBankData = $StmtBank->fetch_assoc();

	return $AryBankData;
}
function checkValueAval($Value)
{
	if ($Value == '') {
		$Value = '';
	} else {
		$Value = '(' . $Value . ')';
	}
	return $Value;
}

function getProductSpec($iconn, $PoID)
{

	$SqlSpecies = "SELECT GROUP_CONCAT(DISTINCT(SpeciesName)) as SpeciesName FROM `ScrmPoGradeData` as SPDG 
	LEFT JOIN ScrmPIVarietyList as SPVL ON SPDG.VarietyID=SPVL.VarietyID 
	left join ScrmSpeciesMaster as SSM on SSM.ShortCode=SPVL.Species WHERE InvId='$PoID'";
	$StmtSpecies = $iconn->query($SqlSpecies);
	$AryStmtSpecies = $StmtSpecies->fetch_assoc();
	$Desc = '<BR> THIS PRODUCT HAS NOT BEEN REFUSED BY ANY COUNTRY PRIOR TO SHIPMENT TO THE U.S.A.<br>';
	$Speciesdesc = $AryStmtSpecies['SpeciesName'];
	$ArrayDescription['Desc'] = $Desc;
	$ArrayDescription['Speciesdesc'] = $Speciesdesc;

	return $ArrayDescription;
}

function getShrimpData($iconn, $VarietyID)
{

	$SqlTotal = "SELECT SPVL.PackingStyle as PackingStyle, SPVL.ShrimpPackingStyle, PS.* FROM `ScrmPIVarietyList` AS SPVL
		LEFT JOIN AdminPackingStyles as PS ON SPVL.ShrimpPackingStyle = PS.PCKID
		WHERE SPVL.VarietyID = '$VarietyID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	$AryShrimpData['ShrimpPackingStyle'] = $RowTotal['PackingStyle'];
	$AryShrimpData['ShrimpUom'] = $RowTotal['Uom'];
	$AryShrimpData['ShrimpTotalSlabs'] = $RowTotal['TotalSlabs'];
	$AryShrimpData['ShrimpSlabWeight'] = $RowTotal['SlabWeight'];
	$AryShrimpData['ShrimpMFactor'] = $RowTotal['MFactor'];
	$AryShrimpData['ShrimpLbsFactor'] = $RowTotal['LbsFactor'];

	return $AryShrimpData;
}

function getSauceData($iconn, $VarietyID)
{

	$SqlTotal = "SELECT HasSauce, SaucePackingStyle, PS.* FROM `ScrmPIVarietyList`
		LEFT JOIN AdminPackingStyles as PS ON SaucePackingStyle = PS.PCKID
		WHERE VarietyID = '$VarietyID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	$ArySauceData['HasSauce'] = $RowTotal['HasSauce'];
	$ArySauceData['SaucePackingStyle'] = $RowTotal['PackingStyle'];
	$ArySauceData['SauceUom'] = $RowTotal['Uom'];
	$ArySauceData['SauceTotalSlabs'] = $RowTotal['TotalSlabs'];
	$ArySauceData['SauceSlabWeight'] = $RowTotal['SlabWeight'];
	$ArySauceData['SauceMFactor'] = $RowTotal['MFactor'];
	$ArySauceData['SauceLbsFactor'] = $RowTotal['LbsFactor'];
	return $ArySauceData;
}