<?php
function getReportData($iconn, $PoID)
{

	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

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


	$CustomInvoiceSql = "SELECT *,SCD.ComShippingTermExt,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
        SC.name as PortofDischargeCountry,
		SCD.LcNo as CLcNo,
        SCD.LcDate as CLcDate,
        SCD.ShowBLNo,
        SCD.ShowBLDate,
        SCD.IssuedBy as CIssuedBy,SP.Name AS LoadingPortName,
		SCD.ComDescriptionSpl as CComDescriptionSpl,
		SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading ,
		SCV.ContainerNo	as ContainerNo ,SPCF.Name as FinalDestination,SCV.ByRoad,SCV.CustomsInvNo,SCV.CustomsInvNo,SBD.ShippingBillNo,
		SCFDC.name as FinalDestinationCountry

		FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID 
	    left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
		left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SL on SL.id=SP.Country
 		left JOIN ScrmCountriesList as SCFDC on SCFDC.id=SSD.FinalDestinationCountry

		left JOIN ScrmISFDetails as SID on SID.PIID=SCV.PiID
		left JOIN ScrmCommercialData as SCD on SCD.PoID=SCV.PiID
		 left JOIN ScrmPaymentTerms as SPT on SCD.ComPaymentTerms=SPT.ID
		 left JOIN ScrmCurrencyMaster as SCM on SCM.CurrencyCode=SSD.UnitCurrency
		left JOIN ScrmShippingBillDetails as SBD on SCV.PiID =  SBD.InvID

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
	$SBDate = getFormatedDateNA($CustomInvData['SBDate']);
	$BLNo = $CustomInvData['BLNo'];
	$SBNo = $CustomInvData['SBNo'];
	$LcNo = $CustomInvData['LcNo'];
	$LcDate = getFormatedDateNA($CustomInvData['LcDate']);
	$LcIssuedBy = $CustomInvData['IssuedBy'];
	$LoadingPortName = $CustomInvData['LoadingPortName'];
	$ByRoad = $CustomInvData['ByRoad'];
	$ShowBLDate = $CustomInvData['ShowBLDate'];
	$ShowBLNo = $CustomInvData['ShowBLNo'];
	$ShippingBillNo = $CustomInvData['ShippingBillNo'];
	$ShippingBillDate = $CustomInvData['ShippingBillDate'];

	if ($CustomInvData['BankID'] == 0) {
		$BankID = $PoDataRow['BankID'];
	} else {
		$BankID = $CustomInvData['BankID'];
	}



	$SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList` WHERE FromTOPID='$LoadingPortName' and PIID='$PoID'";
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




	$SqlScrmPoData = "SELECT *,SPD.Status as PoStatus,
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

	if ($CustomInvData['BankID'] == 0) {
		$BankID = $PoDataRow['BankID'];
	} else {
		$BankID = $CustomInvData['BankID'];
	}

	// $SqlLogin = "SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
	// $StmtLogin = $iconn->query($SqlLogin);
	// $RowLogin = $StmtLogin->fetch_assoc();
	// $SignURL = $RowLogin['SignURL'];

	$ExporterData = getExporterDetails($ExpID);
	//$ProcessorData=getExporterDetails($ProcessorID);
	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);

	if ($ByRoad != '') {
		$FinalDestination = $ByRoad;
	} else {
		$FinalDestination = $CustomInvData['FinalDestination'];
		$FinalDestinationCountry = $CustomInvData['FinalDestinationCountry'] ? strtoupper($CustomInvData['FinalDestinationCountry']) : '';
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

	if ($CustomInvData['ShowShippingDetails'] == 1) {
		$AryData['ShowShippingDetails'] = true;
	} else {
		$AryData['ShowShippingDetails'] = false;
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
	$AryData['ShowShippingBillnDate'] = $AryShowConditions['ShippingBillNoAndDate'];

	if ($ShowCWiseLc && $ShowCWiseLcDate && $ShowCWiseLcIssuedBy) {
		if ($LcNo == '') {
			$AryData['PaymentTerms'] = $PaymentTerms;
		} else {
			$PaymentTerms .= ' LC NO.' . $LcNo;
			if ($LcDate != '') {
				$PaymentTerms .= ', <br> DATED ' . $LcDate;
			}
			if ($LcIssuedBy != '') {
				$PaymentTerms .= ', ' . $LcIssuedBy;
			}
			$AryData['PaymentTerms'] = $PaymentTerms;
		}
	} else {
		$AryData['PaymentTerms'] = $PaymentTerms;
	}


	$AryData['ExporterName'] = $ExporterData['ExpName'];
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
	$AryData['FinalDestinationCountry'] = strtoupper($FinalDestinationCountry);

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


	$AryData['NotifyPartyName'] = $PoDataRow['NotifyPartyName'];
	$AryData['NPartyAddress'] = $PoDataRow['NPartyAddress'];
	$AryData['NPartyContactNumber'] = $PoDataRow['NPartyContactNumber'];
	$AryData['ImporterName'] = $PoDataRow['ImporterName'];
	$AryData['ImporterAddress'] = $PoDataRow['ImporterAddress'];


	$AryData['ContainerNo'] = $CustomInvData['ContainerNo'];
	$AryData['CIDescriptionspl'] = $CustomInvData['CIDescriptionspl'];
	$AryData['SealNo'] = $CustomInvData['SealNo'];
	$AryData['IRNNumber'] = $CustomInvData['IRNNumber'];
	$AryData['BLDate'] = $BLDate;
	//$BLDate=getFormatedDateDMMY($AryData['BLDate']);
	$AryData['SBDate'] = $SBDate;
	$AryData['BLNo'] = $BLNo;
	$AryData['SBNo'] = $SBNo;
	$AryData['ShippingBillNo'] = $ShippingBillNo;
	$AryData['ShippingBillDate'] = $ShippingBillDate;

	return $AryData;
}

function getBreakUpData($iconn, $GradeID, $PiID, $ConfigDetails)
{
	$ClientId = $ConfigDetails['ClientId'];
	$gradeTable = $ConfigDetails['GradeTableName'] == '' ? 'ScrmPoGradeData' : $ConfigDetails['GradeTableName'];
	$gradeTable = $ConfigDetails['PType'] == 'UNITS' ? 'ScrmPIGradeData' : $gradeTable;
	$poTable = $gradeTable == 'ScrmPoGradeData' ? 'ScrmPoData' : 'ScrmPIData';
	$DestCountryID = $ConfigDetails['DestCountryID'];
	$CanadaLbsFactor = $ConfigDetails['CanadaLbsFactor'] == '' ? 'LbsFactor' : $ConfigDetails['CanadaLbsFactor'];

	if ($PiID == '') {
		$Qry = "WHERE ID in ($GradeID)";
		$PriceQry = 'Price';
	} else {
		$Qry = "WHERE InvId='$PiID'";
		$PriceQry = 'sum(Price) as Price';
	}

	if ($gradeTable == 'ScrmPIGradeData') {
		$Qry = $GradeID != '' ? "WHERE PoGradeId in ($GradeID)" : "WHERE InvId in ($PiID)";
	}

	$sqlExt = "sum(GrossWt) as GrossWeightKgs,sum(GrossWt) as GrossWeightLbs,";

	if ($ClientId == 'SMEV3' && $DestCountryID == '23') {
		$sqlExt .= "CASE WHEN PS.Uom = 'GRAMS' THEN
			sum(Mcs * TotalSlabs * SlabWeight * Mfactor * $CanadaLbsFactor)
			ELSE sum(Mcs * TotalSlabs * SlabWeight * LbsFactor)
			END AS NetWgtLbs,
			CASE WHEN PS.Uom = 'OZ' THEN
			sum(Mcs * TotalSlabs * SlabWeight / (1000 * $CanadaLbsFactor))
			WHEN PS.Uom = 'LBS' THEN
			sum(Mcs * TotalSlabs * SlabWeight * LbsFactor / $CanadaLbsFactor)
			ELSE sum(Mcs * TotalSlabs * SlabWeight * MFactor)
			END AS NetWgtKgs";

		$PriceExt = "sum(CASE WHEN PS.Uom = 'GRAMS' THEN
			Mcs * TotalSlabs * SlabWeight * Mfactor * $CanadaLbsFactor * Price
			ELSE Mcs * TotalSlabs * SlabWeight * LbsFactor * Price END) AS PriceLbs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN
			Mcs * TotalSlabs * SlabWeight / (1000 * $CanadaLbsFactor) * Price
			WHEN PS.Uom = 'LBS' THEN
			(Mcs * TotalSlabs * SlabWeight * LbsFactor / $CanadaLbsFactor) * price
			ELSE Mcs * TotalSlabs * SlabWeight * MFactor * Price END) AS PriceKgs";
	} else {
		// $sqlExt .= "sum(Mcs * TotalSlabs * SlabWeight * MFactor) as NetWgtKgs,
		// 	sum(Mcs * TotalSlabs * SlabWeight * LbsFactor) as NetWgtLbs";

		$sqlExt .= "sum(CASE WHEN PS.Uom = 'OZ' THEN
			Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454
			ELSE Mcs * TotalSlabs * SlabWeight * MFactor END) as NetWgtKgs,
			sum(Mcs * TotalSlabs * SlabWeight * LbsFactor) as NetWgtLbs,
			sum(Mcs*TotalSlabs*SlabWeight*MFactor*(SPVL.GlazePerc/100)) as GlazeWeightKgs,
			sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(SPVL.GlazePerc/100)) as GlazeWeightLbs";

		$PriceExt = "sum(CASE WHEN PS.Uom = 'OZ' AND SPVL.HasSauce = 'Yes' THEN (Mcs * TotalSlabs * Price)
			WHEN PS.Uom = 'LBS' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price)
			WHEN PS.Uom = 'OZ' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price)
			ELSE (Mcs * TotalSlabs * SlabWeight * Mfactor * Price) END) as PriceKgs,
			sum(CASE WHEN PS.Uom = 'OZ' AND SPVL.HasSauce = 'Yes' THEN (Mcs * TotalSlabs * Price)
			WHEN PS.Uom = 'KGS' THEN (Mcs * TotalSlabs * SlabWeight * MFactor * Price)
			WHEN PS.Uom = 'GRAMS' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price)
			ELSE (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price) END) as PriceLbs";
	}

	$SqlTotal = "SELECT SPVL.HasSauce, sum(Mcs) as MCS,TotalSlabs,InvId, $sqlExt, $PriceQry, $PriceExt,
		CASE WHEN SPVL.HasSauce = 'Yes' THEN sum(Mcs * TotalSlabs * Price)
		ELSE 0 END as PriceSauce,
		sum(TraysQuantity) as TraysQuantity
		FROM $gradeTable
		as PGD left join AdminPackingStyles as PS
		on PGD.PackingStyleID=PS.PCKID
		left join ScrmPIVarietyList as SPVL
		on SPVL.VarietyID=PGD.VarietyID $Qry";
	// echo $SqlTotal;echo '<br>';
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];
	$WtPerCtnTxt = '';
	if ($PiID != '') {
		$SqlWtPerCtn = "SELECT
			round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,
			round(GrossWt/Sum(Mcs),2) as GrossWt FROM $gradeTable as SPGD
			left JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID
			WHERE InvId='$InvId' GROUP by PackingStyleID";
		$StmtWtPerCtn = $iconn->query($SqlWtPerCtn);
		$RowWtPerCtn = $StmtWtPerCtn->fetch_assoc();
		$NetWtPerCtn = $RowWtPerCtn['NetWtLbs'];
		$GrossWtPerCtn = $RowWtPerCtn['GrossWt'];
		$WtPerCtnTxt = 'EACH M/CTNS. CONSISTS OF NET WT. ' . $NetWtPerCtn . ' LBS AND GROSS WT. ' . $GrossWtPerCtn . ' LBS.';
	}

	$SqlQtyUnit = "SELECT QtyUnit from $poTable WHERE PoID='$InvId'";
	$StmtQtyUnit = $iconn->query($SqlQtyUnit);
	$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
	$QtyUnit = $RowQtyUnit['QtyUnit'];

	switch ($QtyUnit) {
		case 'BAGS':
		case 'TRAYS':
		case 'RINGS':
		case 'UNITS':
			$Qty = $RowTotal['MCS'] * $RowTotal['TotalSlabs'];
			$UsdValue = $RowTotal['PriceSauce'];
			break;
		case 'KGS':
			$Qty = $RowTotal['NetWgtKgs'];
			$UsdValue = $RowTotal['PriceKgs'];
			$GrossWt = round($RowTotal['GrossWeightKgs'], 2);
			break;
		default: // LBS or any other value
			$Qty = $RowTotal['NetWgtLbs'];
			$UsdValue = $RowTotal['PriceLbs'];
			$GrossWt = round($RowTotal['GrossWeightLbs'], 2);
			break;
	}

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['GrossWt'] = $GrossWt;

	// if ($DestCountryID == '23') {
	// 	$AryPriceData['UsdValue'] = $Qty * $RowTotal['Price'];
	// }

	$AryPriceData['NetWgtKgs'] = $RowTotal['NetWgtKgs'];
	$AryPriceData['NetWgtLbs'] = $RowTotal['NetWgtLbs'];
	$AryPriceData['GrossWeightKgs'] = round($RowTotal['GrossWeightKgs'], 2);
	$AryPriceData['GrossWeightLbs'] = round($RowTotal['GrossWeightLbs'], 2);
	$GlazeWeightKgs = $RowTotal['GlazeWeightKgs'];
	$GlazeWeightLbs = $RowTotal['GlazeWeightLbs'];
	$AryPriceData['ProductWeightWOGKgs'] = $RowTotal['NetWgtKgs'] - $GlazeWeightKgs;
	$AryPriceData['ProductWeightWOGLbs'] = $RowTotal['NetWgtLbs'] - $GlazeWeightLbs;
	$AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;
	$AryPriceData['HasSauce'] = $RowTotal['HasSauce'];

	return $AryPriceData;
}

function getBreakUpDataOld($iconn, $GradeID, $PiID = null)
{
	global $PType, $DocType, $PoID;

	if ($PiID == '') {
		$Qry = "WHERE PGD.ID in ($GradeID)";
		$PriceQry = 'Price';
	} else {
		$Qry = "WHERE InvId='$PiID'";
		$PriceQry = 'sum(Price) as Price';
	}

	if ($PType == 'UNITS') {
		$gradeTableName = 'ScrmPIGradeData';
		$Qry = "WHERE InvId='$PoID'";
		if ($GradeID != '') {
			$Qry .= "AND PGD.ID in ($GradeID)";
		}
	} else {
		$gradeTableName = 'ScrmPoGradeData';
	}

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
				sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as GlazeWeightKgs,
				 sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as GlazeWeightLbs";

	// if ($DestCountryID == '23') {
	// 	$sqlExt .= "CASE WHEN PS.Uom = 'GRAMS' THEN
	// 				round(sum(Mcs * TotalSlabs * SlabWeight / (1000 * 0.454)), 2)
	// 			ELSE
	// 				sum(Mcs * TotalSlabs * SlabWeight * LbsFactor)
	// 			END AS NetWgtLbs";
	// } else {
	// 	$sqlExt .= "sum(Mcs * TotalSlabs * SlabWeight * LbsFactor) as NetWgtLbs";
	// }

	$SqlTotal = "SELECT sum(Mcs) as MCS,InvId,GlazePerc,  $sqlExt  ,
			$PriceQry,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			$gradeTableName
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";

	if ($PType == 'UNITS') {
		// echo $SqlTotal; echo '<br>';
	}
	// echo $SqlTotal;
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];
	$GlazePerc = $RowTotal['GlazePerc'];

	// echo $SqlTotal; echo '<br>'; echo '<br>';
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

	if ($QtyUnit == 'TRAYS') {
		$AryPriceData['Qty'] = $RowTotal['TraysQuantity'];
		$AryPriceData['UsdValue'] = $RowTotal['Price'] * $RowTotal['TraysQuantity'];
	}


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
	// $Speciesdesc = 'SPECIES : ' . $AryStmtSpecies['SpeciesName'];
	$Speciesdesc = $AryStmtSpecies['SpeciesName'];
	$ArrayDescription['Desc'] = $Desc;
	$ArrayDescription['Speciesdesc'] = $Speciesdesc;

	return $ArrayDescription;
}


function getIsGlazeInvoice($iconn, $PoID)
{

	$SqlGlazePerc = "SELECT count(*) as Total FROM `ScrmPoGradeData` WHERE InvId='$PoID' and GlazePerc > 0";
	$StmtGlazePerc = $iconn->query($SqlGlazePerc);
	$AryStmtSpecies = $StmtGlazePerc->fetch_assoc();

	if ($AryStmtSpecies['Total'] > 0) {
		return true;
	} else {
		return false;
	}
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
	$ArySauceData = array_merge($ArySauceData, getShrimpData($iconn, $VarietyID));

	return $ArySauceData;
}

function checkSauceVarietyExists($iconn, $VarietyID)
{
	$SqlQuery = "SELECT HasSauce FROM ScrmPIVarietyList WHERE VarietyID = $VarietyID";
	$Stmt = $iconn->query($SqlQuery);
	$Row = $Stmt->fetch_assoc();
	return $Row['HasSauce'];
}

function getShrimpData($iconn, $VarietyID)
{

	$IsSauceVariety = checkSauceVarietyExists($iconn, $VarietyID);
	if ($IsSauceVariety == 'Yes') {
		$filterField = 'SPVL.ShrimpPackingStyle';
	} else {
		$filterField = 'SPVL.PackingStyle';
	}

	$SqlTotal = "SELECT SPVL.PackingStyle as PackingStyle, SPVL.ShrimpPackingStyle, PS.* FROM `ScrmPIVarietyList` AS SPVL
		LEFT JOIN AdminPackingStyles as PS ON $filterField = PS.PCKID
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

function getPoGradeData($iconn, $PoGradeID)
{
	global $PoID;
	$SqlTotal = "SELECT * FROM `ScrmPoGradeData` WHERE ID = '$PoGradeID' AND InvId = '$PoID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	return $RowTotal;
}

function getShrimpBreakUpData($iconn, $PiID, $ID)
{
	global $PoID, $PType;

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

	$SqlQuery = "SELECT PS.Uom,sum(Mcs) as MCS,InvId, $sqlExt, sum(Price),
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
            sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*0.454) as NetWgtOz FROM
			`ScrmPoGradeData`
			as PGD
            LEFT JOIN (
                SELECT
                    VarietyID,
                    CASE
                        WHEN HasSauce = 'Yes'
                            THEN ShrimpPackingStyle
                        ELSE PackingStyle
                    END AS EffectivePackingStyle
                FROM ScrmPIVarietyList
            ) AS VL
            ON VL.VarietyID = PGD.VarietyID
            left join AdminPackingStyles as PS
			on PS.PCKID=VL.EffectivePackingStyle WHERE ID = $ID AND InvId = '$PoID'";

	$StmtTotal = $iconn->query($SqlQuery);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];

	if ($PiID != '') {
		$ArrayGrossweightText = array();
		$ArrayNetweightText = array();

		$SqlWtPerCtn = "SELECT
            round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,
            round(sum(GrossWt)/Sum(Mcs),2) as GrossWt FROM `ScrmPoGradeData` as SPGD
            LEFT JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID
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

	if ($RowTotal['Uom'] == 'OZ') {
		$RowTotal['NetWgtKgs'] = $RowTotal['NetWgtLbs'] * 0.454;
	}

	if ($QtyUnit == 'KGS') {
		$Qty = $RowTotal['NetWgtKgs'];
		$UsdValue = $RowTotal['Price'] * $RowTotal['NetWgtKgs'];
		$UnitPrice = number_format($RowTotal['Price'], 2);
	} else {
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
		$AryPriceData['Qty'] = $RowTotal['NetWgtKgs'];
	} else if ($PType == 'KGS' && $QtyUnit != 'KGS') {
		$AryPriceData['Qty'] = $RowTotal['NetWgtLbs'] * 0.454;
	} else {
		$AryPriceData['Qty'] = $RowTotal['NetWgtLbs'];
	}

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];

	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['UnitPrice'] = $UnitPrice;

	$AryPriceData['NetWgtKgs'] = $NetWgtKgs;
	$AryPriceData['NetWgtLbs'] = $NetWgtLbs;
	$AryPriceData['GrossWeightKgs'] = $GrossWeightKgs;
	$AryPriceData['GrossWeightLbs'] = $GrossWeightLbs;
	$AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;

	return $AryPriceData;
}
