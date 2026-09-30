<?php
//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


function callBackUrl()
{

	header("Location: custom-invoices");
}

function getPaymentDueDate($iconn, $NoOfDays, $ConditionDate, $PoID)
{
	$DueDate = '';
	if ($NoOfDays == '' or $NoOfDays == 0) {

	} else if ($ConditionDate == 'InvoiceDate') {
		//ScrmCustomInvoices
		$SqlDate = "SELECT ADDDATE(CInvDate,$NoOfDays) as DueDate FROM `ScrmCustomInvoices` WHERE PiID=$PoID";
	} else if ($ConditionDate == 'BLDate') {
		//ScrmISFDetails
		$SqlDate = "SELECT ADDDATE(BLDate,$NoOfDays) as DueDate FROM `ScrmISFDetails` WHERE PIID=$PoID";
	} else if ($ConditionDate == 'OnBoardDate') {
		//ScrmISFDetails
		$SqlDate = "SELECT ADDDATE(ShippingOnboardDt,$NoOfDays) as DueDate FROM `ScrmISFDetails` WHERE PIID=$PoID";
	} else if ($ConditionDate == 'GetOut') {
		//FDA
	} else if ($ConditionDate == 'FDADate') {
		//FDA
	} else if ($ConditionDate == 'LCDate') {
		//ScrmCommercialData
		$SqlDate = "SELECT ADDDATE(LcDate,$NoOfDays) as DueDate FROM `ScrmCommercialData` WHERE PoID=$PoID";
	}

	if ($SqlDate != '') {

		$StmtDueDate = $iconn->query($SqlDate);
		$RowDueDate = $StmtDueDate->fetch_array();
		$DueDate = $RowDueDate['DueDate'];
	}
	return $DueDate;
}


function numberToMcswords($number)
{
	$number = number_format($number, 2, '.', '');
	list($integer, $fraction) = explode(".", (string) $number);

	$output = "";

	if ($integer { 0} == "-") {
		$output = "negative ";
		$integer = ltrim($integer, "-");
	} else if ($integer { 0} == "+") {
		$output = "positive ";
		$integer = ltrim($integer, "+");
	}

	if ($integer { 0} == "0") {
		$output .= "zero";
	} else {
		$integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
		$group = rtrim(chunk_split($integer, 3, " "), " ");
		$groups = explode(" ", $group);

		$groups2 = array();
		foreach ($groups as $g) {
			$groups2[] = convertThreeDigit($g { 0}, $g { 1}, $g { 2});
		}

		for ($z = 0; $z < count($groups2); $z++) {
			if ($groups2[$z] != "") {
				$output .= $groups2[$z] . convertGroup(11 - $z) . (
					$z < 11
					&& !array_search('', array_slice($groups2, $z + 1, -1))
					&& $groups2[11] != ''
					&& $groups[11]{ 0} == '0'
					? "  "
					: " "
				);
			}
		}

		$output = rtrim($output, ", ");

	}
	if ($fraction > 0) {
		$output .= " Dollars and ";
		if ($fraction < 20) {
			$output .= convertTwoDigit($fraction) . " cents";
		} elseif ($fraction < 100) {
			$output .= convertTwoDigit(substr($fraction, 0, 1));
			if (convertDigit(substr($fraction, 1, 1)) != 'zero') {
				$output .= "  " . convertDigit(substr($fraction, 1, 1)) . " cents";
			} else {
				$output .= "  MASTER CARTONS ONLY";

			}
		}

	} else {
		$output .= "  MASTER CARTONS ONLY";
	}




	return strtoupper($output);
	//uma
}

/*function DeleteCollectionBreakup($FCNumber){
	global $iconn;
	$SqlUtilisedValue="SELECT NetValue,
	(select sum(UtilizedValue) from ScrmCollectionBreakUP where FCNumber='$FCNumber') as UtilizedValue
	FROM `ScrmForwardContractsDetails` where FCNumber='$FCNumber'";

	$StmtUtilisedValue=$iconn->query($SqlUtilisedValue);
	$RowUtilised=$StmtUtilisedValue->fetch_array();
	$NetValue=$RowUtilised['NetValue'];
	$UtilizedValue=$RowUtilised['UtilizedValue'];
	$Balance=$NetValue-$UtilizedValue;

	$SqlFcDetails="UPDATE `ScrmForwardContractsDetails` SET `UtilizedValue` ='$UtilizedValue',
 BalancedValue='$Balance' where FCNumber='$FCNumber'";
$StmtFcDetails=$iconn->query($SqlFcDetails);

if($StmtFcDetails){
$SqlCollectiondelete="DELETE FROM `ScrmCollectionBreakUP`  WHERE FcNumber='$FCNumber'";
$StmtCollectiondelete=$iconn->query($SqlCollectiondelete);
}
	return 'success'; 


}*/

function postUpdateGrossweight($iconn, $InvId, $PoGradeId, $Backurl, $Mcs)
{

	$table = 'ScrmPIData';
    $poCheckQry = "SELECT COUNT(`InvNo`) CNT FROM ScrmPoData WHERE  `PoID`=$InvId";
    $StmtpoCheck = $iconn->query($poCheckQry);
    $resPoCheck = $StmtpoCheck->fetch_array();
    if(!empty($resPoCheck['CNT'])){
        $table = 'ScrmPoData';
    }


	/* $Sqlgrossweight = "SELECT QtyUnit,sum($Mcs*GrossWeight*MFactor) as GrossweightKgs,sum($Mcs*GrossWeight*LbsFactor) as GrossweightLbs 
		FROM `ScrmPoGradeData` as SPGD left join ScrmPoData as SPD on SPD.PoID=SPGD.InvId 
		left join AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID WHERE InvId='$InvId' and ID='$PoGradeId'";  */
	$Sqlgrossweight = "SELECT QtyUnit,sum($Mcs*GrossWeight*MFactor) as GrossweightKgs,sum($Mcs*GrossWeight*LbsFactor) as GrossweightLbs 
		FROM `ScrmPoGradeData` as SPGD left join $table as SPD on SPD.PoID=SPGD.InvId 
		left join AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID WHERE InvId='$InvId' and ID='$PoGradeId'";
	$Stmtgrossweight = $iconn->query($Sqlgrossweight);
	$RowGrossweight = $Stmtgrossweight->fetch_array();
	//echo '<pre>';print_r($RowGrossweight);die;
	$QtyUnit = $RowGrossweight['QtyUnit'];
	if ($QtyUnit == 'KGS') {
		$GrossWeight = $RowGrossweight['GrossweightKgs'];
	} else {
		$GrossWeight = $RowGrossweight['GrossweightLbs'];
	}
	// $GrossWeight=round($GrossWeight,2); 

	if ($Backurl != '') {

		$SqlSdb = "UPDATE `ScrmPoGradeData` SET `GrossWt`='$GrossWeight' WHERE `ID`='$PoGradeId'";
	} else {


		$SqlSdb = "UPDATE `ScrmPoGradeData` SET `GrossWt`='$GrossWeight', PIGrossWt='$GrossWeight' WHERE `ID`='$PoGradeId'"; //uma 


	}

	// echo $SqlSdb;
	$StmtSdb = $iconn->query($SqlSdb);

	if ($StmtSdb) {

		$SqlGrosweight="SELECT sum(GrossWt) as GrossWt FROM `ScrmPoGradeData` WHERE InvId='$InvId'";
$StmtGrosweight=$iconn->query($SqlGrosweight);
$RowGrosweight=$StmtGrosweight->FETCH_ARRAY();
$GrossWeight = round($RowGrosweight['GrossWt'],2);


	$SqlCustomInvoice = "UPDATE `ScrmCustomInvoices` SET `TotalGrossWt`='$GrossWeight' WHERE `PiID`='$InvId' ";
	$StmtCustomInvoice = $iconn->query($SqlCustomInvoice);
	}

	return 'success';


}

function postUpdateShipmentBreif($iconn, $CInvDate, $CInvNo, $PIId, $StockOutDate)
{
	$AryCInvNo = explode("-", $CInvDate);
	$FinYear = $AryCInvNo[0];
	$CurrentMonth = $AryCInvNo[1];

	if ($CurrentMonth > 3) {
		$FinYear = $FinYear;
	} else {
		$FinYear = $FinYear - 1;
	}

	$SqlSdb = "UPDATE `ScrmShipmentDetailsBreif` SET 
			`CInvNo`='$CInvNo',
			`CInvDate`='$CInvDate',
			`TransportedDate`='$StockOutDate',
			`FinYear`='$FinYear' WHERE PiID='$PIId'";

	$StmtSdb = $iconn->query($SqlSdb);

	return 'success';


}

function postUpdateCharges($iconn, $Discount, $PIId)
{


	$SqlPOData = "UPDATE ScrmPoData SET ChargeAmount='$Discount' WHERE PoID='$PIId'";
	$SqlPIData = "UPDATE ScrmPIData SET ChargeAmount='$Discount' WHERE PoID='$PIId'";

	$StmtPOData = $iconn->query($SqlPOData);
	$StmtPIData = $iconn->query($SqlPIData);
	if ($StmtPOData && $StmtPIData) {
		return 'success';
	} else {
		return 'Unable to Update';
	}

}

function postUpdatePoReceivedStatus($iconn, $PiIds, $LoginId)
{
	//echo "i";exit;
	$PIId = explode(',', $PiIds);
	$PiCount = count($PIId);

	for ($i = 0; $i < $PiCount; $i++) {
		$InvId = $PIId[$i];

		$SqlPI = "SELECT * FROM `ScrmPIData` WHERE PoID='$InvId' ";
		$StmtPI = $iconn->query($SqlPI);
		$RowPI = $StmtPI->fetch_array();
		$PoRefId = $RowPI['PoRefId'];
		$InvNo = $RowPI['InvNo'];
		$InvNumFull = $RowPI['InvNumFull'];
		$InvDate = $RowPI['InvDate'];
		$ExporterID = $RowPI['ExporterID'];
		$ProcessorID = $RowPI['ProcessorID'];
		$LotNo = $RowPI['LotNo'];
		$SciName = $RowPI['SciName'];
		$ConsigneeID = $RowPI['ConsigneeID'];
		$BuyerID = $RowPI['BuyerID'];
		$PoNo = $RowPI['PoNo'];
		$PoDate = $RowPI['PoDate'];
		$DestinationCountry = $RowPI['DestinationCountry'];
		$TypeCountry = $RowPI['TypeCountry'];
		$PoStatus = $RowPI['PoStatus'];
		$YEAR = $RowPI['Year'];
		$BankID = $RowPI['BankID'];
		$PortOfDischarge = $RowPI['PortOfDischarge'];
		$FinalDestination = $RowPI['FinalDestination'];
		$PaymentTerms = $RowPI['PaymentTerms'];
		$ShippingTerms = $RowPI['ShippingTerms'];
		$DeliveryTerms = $RowPI['DeliveryTerms'];
		$PreparedBy = $RowPI['PreparedBy'];
		$PRInstrctns = $RowPI['PRInstrctns'];
		$ShipByDate = $RowPI['ShipByDate'];
		$UnitCurrency = $RowPI['UnitCurrency'];
		$QtyUnit = $RowPI['QtyUnit'];
		$ImporterID = $RowPI['ImporterID'];
		$NotifyPartyID = $RowPI['NotifyPartyID'];
		$Remarks = $RowPI['Remarks'];
		$ReferenceNo = $RowPI['ReferenceNo'];
		$Agent = $RowPI['Agent'];
		$Certification = $RowPI['Certification'];
		$PortofLoading = $RowPI['PortofLoading'];
		$ShowBank = $RowPI['ShowBank'];
		$UltimateConsigneeID = $RowPI['UltimateConsigneeID'];



		$sqlpo = " SELECT * FROM `ScrmPoData`   WHERE PoID='$InvId'";
		$stmtPO = $iconn->query($sqlpo);
		$count = mysqli_num_rows($stmtPO);


		$sqlShipment = " SELECT * FROM `ScrmShipmentDetailsBreif`   WHERE PiID='$InvId'";
		$stmtShipment = $iconn->query($sqlShipment);
		$Shipmentcount = mysqli_num_rows($stmtShipment);

		$SqlPI = "UPDATE `ScrmPIData` SET `Status`='5',`ApprovedBy`='$LoginId'
		 WHERE PoID in ($InvId) ";

		$StmtPI = $iconn->query($SqlPI);


		if ($Shipmentcount == 0) {
			$SqlShipmentInsert = "INSERT INTO `ScrmShipmentDetailsBreif`(`PiID`,`AvailableDate`) VALUES('$InvId','$PoDate')";
			$StmtShipmentInsert = $iconn->query($SqlShipmentInsert);
		} else {
			$SqlShipmentInsert = "UPDATE `ScrmShipmentDetailsBreif` SET `AvailableDate`='$PoDate' WHERE `PiID`='$InvId'";
			$StmtShipmentInsert = $iconn->query($SqlShipmentInsert);
		}


		if ($count == 0) {
			$SqlInsert = " INSERT INTO `ScrmPoData`(`PoID`, `PoRefId`, `InvNo`,`InvNumFull`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
	  `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`, `BankID`
	  , `PortOfDischarge`, `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, `PreparedBy`,
	  `PRInstrctns`, `ShipByDate`,  `Status`,`QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`, `Remarks`,`ImporterID`, `NotifyPartyID`, `ReferenceNo`, `Agent`, `Certification`, `PortofLoading`, `ShowBank`, `UltimateConsigneeID`) 
	  VALUES ('$InvId','$PoRefId','$InvNo','$InvNumFull','$InvDate','$ExporterID','$ProcessorID',
'$LotNo','$SciName','$ConsigneeID','$BuyerID','$PoNo','$PoDate','$DestinationCountry',
'$TypeCountry','$PoStatus','$YEAR','$BankID','$PortOfDischarge',
'$FinalDestination','$PaymentTerms','$ShippingTerms','$DeliveryTerms','$PreparedBy' ,'$PRInstrctns',
	 '$ShipByDate','5','$QtyUnit','$UnitCurrency','$LoginId','$LoginId','$Remarks','$ImporterID','$NotifyPartyID','$ReferenceNo','$Agent','$Certification','$PortofLoading','$ShowBank','$UltimateConsigneeID')";
			$stmt2 = $iconn->query($SqlInsert);
		} else {
			$SqlInsert = "UPDATE `ScrmPoData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Remarks`='$Remarks',`Status`='5',
	`ApprovedBy`='$LoginId' WHERE PoID='$InvId'";
			$stmt2 = $iconn->query($SqlInsert);

		}
	}

	return 'success';


}
function getFullPiInvoiceNumbertoSave($ExpID, $InvDate, $InvNo)
{
	$ExporterData = getExporterDetails($ExpID);
	$InvShortCode = $ExporterData['InvShortCode'];
	$ProformaInvShortcode = $ExporterData['ProformaInvShortcode'];
	$InvDateFormat = $ExporterData['InvDateFormat'];

	$CurrentMonth = date('n', strtotime($InvDate));
	if ($InvDateFormat == 'Short') {
		if ($CurrentMonth <= 3) {
			$NxtYear = date("y", strtotime($InvDate));
			$CurrentYear = date("y", strtotime($InvDate)) - 1;
		} else {
			$CurrentYear = date("y", strtotime($InvDate));
			$NxtYear = date("y", strtotime($InvDate)) + 1;

		}
	} else {

		if ($CurrentMonth <= 3) {
			$NxtYear = date('y', strtotime($InvDate));
			$CurrentYear = date('Y', strtotime($InvDate)) - 1;
		} else {
			$CurrentYear = date('Y', strtotime($InvDate));
			$NxtYear = date('y', strtotime($InvDate)) + 1;

		}
	}
	$InvNumFull = $InvShortCode . $ProformaInvShortcode . $InvNo . '/' . $CurrentYear . '-' . $NxtYear;
	//$InvNumFull= $InvShortCode.'PI/'.$InvNo.'/'.$CurrentYear.'-'.$NxtYear;
	return $InvNumFull;


}


function getInvoiceFullNumbertosave($ExporterID, $ProcessorID, $InvDate, $InvNo)
{


	$ProcessorData = getExporterDetails($ProcessorID);
	$ExporterData = getExporterDetails($ExporterID);
	$ProcShortCode = $ProcessorData['InvShortCode'];
	$ExpShortCode = $ExporterData['InvShortCode'];
	$InvDateFormat = $ExporterData['InvDateFormat'];

	$CurrentMonth = date('n', strtotime($InvDate));

	if ($InvDateFormat == 'Short') {
		if ($CurrentMonth <= 3) {
			$NxtYear = date("y", strtotime($InvDate));
			$CurrentYear = date("y", strtotime($InvDate)) - 1;
		} else {
			$CurrentYear = date("y", strtotime($InvDate));
			$NxtYear = date("y", strtotime($InvDate)) + 1;

		}
	} else {

		if ($CurrentMonth <= 3) {
			$NxtYear = date('y', strtotime($InvDate));
			$CurrentYear = date('Y', strtotime($InvDate)) - 1;
		} else {
			$CurrentYear = date('Y', strtotime($InvDate));
			$NxtYear = date('y', strtotime($InvDate)) + 1;

		}
	}

	/*if($CurrentMonth<=3){
		$NxtYear = date("y",strtotime($InvDate)); 
		$CurrentYear = date("y",strtotime($InvDate))-1; 
	} 
	else{ 
	$CurrentYear = date("y",strtotime($InvDate)); 
	$NxtYear = date("y",strtotime($InvDate))+1; 

	}*/

	$CustomsInvNo = $ExpShortCode . $ProcShortCode . $CurrentYear . $NxtYear . '/' . $InvNo;



	return $CustomsInvNo;


}

function getPIInvoiceNumber($iconn, $ExpID, $PoID)
{
	$ExporterData = getExporterDetails($ExpID);
	$InvShortCode = $ExporterData['InvShortCode'];
	$ProformaInvShortcode = $ExporterData['ProformaInvShortcode'];
	$InvDateFormat = $ExporterData['InvDateFormat'];

	$CustomInvoiceSql = "SELECT * FROM ScrmPIData   WHERE PoID='$PoID'";
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();


	$CurrentMonth = date('n', strtotime($CustomInvoiceRow['InvDate']));
	if ($InvDateFormat == 'Short') {
		if ($CurrentMonth <= 3) {
			$NxtYear = date("y", strtotime($CustomInvoiceRow['InvDate']));
			$CurrentYear = date("y", strtotime($CustomInvoiceRow['InvDate'])) - 1;
		} else {
			$CurrentYear = date("y", strtotime($CustomInvoiceRow['InvDate']));
			$NxtYear = date("y", strtotime($CustomInvoiceRow['InvDate'])) + 1;

		}
	} else {

		if ($CurrentMonth <= 3) {
			$NxtYear = date('y', strtotime($CustomInvoiceRow['InvDate']));
			$CurrentYear = date('Y', strtotime($CustomInvoiceRow['InvDate'])) - 1;
		} else {
			$CurrentYear = date('Y', strtotime($CustomInvoiceRow['InvDate']));
			$NxtYear = date('y', strtotime($CustomInvoiceRow['InvDate'])) + 1;

		}
	}

	return $InvShortCode . $ProformaInvShortcode . $CustomInvoiceRow['InvNo'] . '/' . $CurrentYear . '-' . $NxtYear;


}
function getDeliveryTermCode($Deliveryterm)
{
	if ($Deliveryterm == 'CY/CY') {
		$DeliveryTermCode = 'PORT DELIVERY';
	} else if ($Deliveryterm == 'CY/SD') {
		$DeliveryTermCode = 'STORE DELIVERY';
	} else {
		$DeliveryTermCode = '';
	}
	return $DeliveryTermCode;
}

// function getInvoiceNumber($iconn,$ExpID,$PoID){


// $CustomInvoiceSql="SELECT PI.ProcessorID as ProcessorID ,
// 					PI.ExporterID as ExporterID,CInvDate,CInvNo FROM ScrmPIData  as PI 
// 					left join ScrmCustomInvoices as SC on SC.PiID=PI.PoID 
// 					WHERE PiID='$PoID'"; 
// $CustomInvoiceExec=$iconn->query($CustomInvoiceSql);
// $InvoiceNo='--'; 
// if($CustomInvoiceExec->num_rows!=0){
// $CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();

// $ExporterID=$CustomInvoiceRow['ExporterID'];
// $ProcessorID =$CustomInvoiceRow['ProcessorID'];
// $ProcessorData=getExporterDetails($ProcessorID );
// $ExporterData=getExporterDetails($ExporterID);
// $ProcShortCode=$ProcessorData['InvShortCode'];
// $ExpShortCode=$ExporterData['InvShortCode'];

// $CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate'])); 

// if($CurrentMonth<=3){
// $NxtYear = date("y",strtotime($CustomInvoiceRow['CInvDate'])); 
// $CurrentYear = date("y",strtotime($CustomInvoiceRow['CInvDate']))-1; 
// } 
// else{ 
// $CurrentYear = date("y",strtotime($CustomInvoiceRow['CInvDate'])); 
// $NxtYear = date("y",strtotime($CustomInvoiceRow['CInvDate']))+1; 

// }

// $InvoiceNo=$ExpShortCode.$ProcShortCode.$CurrentYear.$NxtYear.'/'.$CustomInvoiceRow['CInvNo'];

// }

// return $InvoiceNo; 


// }


function getInvoiceNumber($iconn, $ExporterID, $ProcessorID, $InvDate, $InvNo, $DocType)
{ //NEW METHOD
	//global $iconn;
	$ProcessorData = getExporterDetails($ProcessorID);
	$ExporterData = getExporterDetails($ExporterID);
	$ProcShortCode = $ProcessorData['InvShortCode'];
	$ExpShortCode = $ExporterData['InvShortCode'];
	$InvDateFormat = $ExporterData['InvDateFormat'];

	$CurrentMonth = date('n', strtotime($InvDate));

	if ($InvDateFormat == 'Short') {
		if ($CurrentMonth <= 3) {
			$NxtYear = date("y", strtotime($InvDate));
			$CurrentYear = date("y", strtotime($InvDate)) - 1;
		} else {
			$CurrentYear = date("y", strtotime($InvDate));
			$NxtYear = date("y", strtotime($InvDate)) + 1;

		}
	} else {

		if ($CurrentMonth <= 3) {
			$NxtYear = date('y', strtotime($InvDate));
			$CurrentYear = date('Y', strtotime($InvDate)) - 1;
		} else {
			$CurrentYear = date('Y', strtotime($InvDate));
			$NxtYear = date('y', strtotime($InvDate)) + 1;

		}
	}

	$AryElements = array();
	$AryElements['ExpShortCode'] = $ExpShortCode;
	$AryElements['ProcShortCode'] = $ProcShortCode;
	$AryElements['CurrentYear'] = $CurrentYear;
	$AryElements['NxtYear'] = $NxtYear;
	$AryElements['InvNo'] = $InvNo;

	//var_dump($AryElements);

	//$CustomsInvNo=$ExpShortCode.$ProcShortCode.$CurrentYear.$NxtYear.'/'.$InvNo;

	$InvNo = "";
		$SqlInvFormat = "SELECT * FROM ScrmInvNumberPattern Where ExporterID='$ExporterID' and DocType='$DocType' order by SortOrder asc";
	$InvFormatExec = $iconn->query($SqlInvFormat);

	//ECHO $SqlInvFormat; EXIT;
	while ($OrderStatusRow = $InvFormatExec->fetch_array()) {
		//echo $OrderStatusRow['DateFormat'];
		if ($OrderStatusRow['Static'] == 1) {
			$InvNo .= $OrderStatusRow['ElementName'];
		} else if ($OrderStatusRow['DateFormat'] != '') {
			if ($OrderStatusRow['DateFormat'] == 'YY') {
				$Year = $AryElements['CurrentYear'];
			} else {
				$Year = $AryElements['CurrentYear'] . $OrderStatusRow['DateSeperator'] . $AryElements['NxtYear'];
			}

			$InvNo .= $OrderStatusRow['NormalSeparator'] . $Year;
			// echo "hii";
		} else if ($OrderStatusRow['ElementName'] == 'InvNo') {
			$LeadingZeros = $OrderStatusRow['LeadingZeros'];
			$LeadingZeros = '%0' . $LeadingZeros . 'd';
			$InvNo .= $OrderStatusRow['NormalSeparator'] . sprintf($LeadingZeros, $AryElements['InvNo']);
		} else {
			$ElementName = $OrderStatusRow['ElementName'];
			$InvNo .= $AryElements[$ElementName] . $OrderStatusRow['NormalSeparator'];
			/*if($OrderStatusRow['ElementName']==1){
				$InvNo.=$OrderStatusRow['NormalSeparator'].$AryElements[$ElementName];
				}else{
					$InvNo.=$OrderStatusRow['NormalSeparator'].$AryElements[$ElementName];
				}
				*/
		}

		//ECHO $InvNo; ECHO '<BR>';


	}

	return $InvNo;
}




function getInvoiceDescription($iconn, $InvDate, $ExpID)
{
	// $ExpID; exit;
	$SqlDescMaster = "SELECT * FROM `ScrmDescriptionMaster` WHERE EffectiveFromDate 
			<=  '$InvDate' ORDER by EffectiveFromDate desc limit 1; ";
	$StmtDescMaster = $iconn->query($SqlDescMaster);
	return $RowDescMaster = $StmtDescMaster->fetch_assoc();

}

function getShrimpPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetails, $PType)
{
	/*
		$LbsFactor=0.454; // To Convert Lbs into Kgs multiply value
		$Discount=0; // Get from Custom Invoice
		$InsurancePerc=0.11; // Varible
		$AntiDumpingDFactor=101.35 // Anti Dumping Duty Factor(Formula)
		$AntiDumpingMFactor=1.35 // Anti Dumping Duty Factor(Formula)
		$StandardDutyCharges=125; // Standard Value
		$TermName=125; // Standard Value
		$CenttoUsd=100; // Standard Value 1 usd=100 cents

		*/

	// echo $PoID; exit;
	$DiscountType=$AdminConfigDetails['DiscountType'];

	// $SqlCInvDate = "SELECT CInvDate FROM `ScrmCustomInvoices` WHERE PiID=$PoID";
	// $StmtCInvDate = $iconn->query($SqlCInvDate);
	// $RowCInvDate = $StmtCInvDate->fetch_assoc();
	// $CInvDate = $RowCInvDate['CInvDate'];

	$CenttoUsd = 100;
	if ($DocType == 'CMI') {
		$SqlCInvData = "SELECT *,ExchngRte as ExRate,SPD.ExporterID as ExpID FROM ScrmPoData as SPD 
			LEFT JOIN ScrmCommercialData as SCD on 
			SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";


	} else {
		$SqlCInvData = "SELECT *,SPD.ExporterID as ExpID FROM ScrmPoData as SPD 
			LEFT JOIN ScrmCustomInvoices as SCI on 
			SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";
	}
		// echo $SqlCInvData; 


	$StmtCInvData = $iconn->query($SqlCInvData);
	$RowCInvData = $StmtCInvData->fetch_assoc();
	$TermName = $RowCInvData['ShippingTerms'];
	$Discount = $RowCInvData['Discount'];
	$FrieghtCharge = $RowCInvData['Freight'];
	$ExchangeRate = $RowCInvData['ExRate'];
	$QtyUnit = $RowCInvData['QtyUnit'];
	$CInvCode = $RowCInvData['CInvCode'];
	$ExporterID = $RowCInvData['ExpID'];

	/**uma(14-10-2023) */
	// $SqlGstPerc = "SELECT DISTINCT GstPerc FROM `ScrmPoGradeData` as SPGD 
	// 	left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
	// 	left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

		$SqlGstPerc = "SELECT DISTINCT (SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1)
		GstPerc,
		SPM.GstPerc GstPercP,FDAEntryDate,CInvDate,TypeProduct,ISF.BLDate FROM `ScrmPoGradeData` as SPGD 
		left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
		left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
		left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId		
		 left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'"; 

	$StmtGstPerc = $iconn->query($SqlGstPerc);
	$RowGstPerc = $StmtGstPerc->fetch_assoc();
	$StmtRawCookedGstPerc = $iconn->query($SqlGstPerc);#jme
	
	//while($RowHsCode=$StmtHsCode->fetch_assoc()){
	// $ArrayHsCode[]=$RowHsCode['HSCode'];
	// $CInvCode = implode(',', $ArrayHsCode);

	//}

// echo $RowGstPerc;

// { "GstPerc": "5", "FDAEntryDate": null, "CInvDate": "2024-07-12", "TypeProduct": "RAW" } Harinath
	$IgstPerc = !empty($RowGstPerc['GstPerc'])?$RowGstPerc['GstPerc']:$RowGstPerc['GstPercP']; 
	$FDAEntryDate = $RowGstPerc['FDAEntryDate'];
	$CInvDate = $RowGstPerc['CInvDate'];
	$BLDate = $RowGstPerc['BLDate'];

	// if($CInvCode=='03061720'){
	// $IgstPerc=5;
	// }else{
	// $IgstPerc=12; 
	// }

	/**uma (14-10-2023)*/
	$QtyUnit = $RowCInvData['QtyUnit'];
	$Query=" and FromDate<='$CInvDate'";
	if ($DocType == 'CMI') {
		if($FDAEntryDate!=''){
			$Query=" and FromDate<='$FDAEntryDate'";
		}
	}
	// else{
	// $Query=" and FromDate<='$CInvDate'";
	// }

	
	 $SqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'"; 
	$StmtShipTermsMaster = $iconn->query($SqlShipTermsMaster);
	$RowShipTermsMaster = $StmtShipTermsMaster->fetch_assoc();
	
	// SELECT * FROM `ScrmShipmentTermsMaster` where TermName='CIF'
	// { "ID": "4", "TermName": "CIF", "Cost": "0", 
	// 	"Insurance": "1", "Freight": "1", 
	// 	"Duty": "1", "AntiDumpingDFactor": "101.35", 
	// 	"AntiDumpingMFactor": "1.35", 
	// 	"StandardDutyCharges": "0.00", 
	// 	"Status": "1" } Harinath
	

	// Update With ExportId Condition
	//$SqlExporterTaxRates="SELECT * FROM `ScrmExporterTaxRates`";
	// $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where FromDate<='$CInvDate'  ORDER BY ExpTRID DESC limit 1";
	$SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1"; 
	$StmtExporterTaxRates = $iconn->query($SqlExporterTaxRates);
	$RowExporterTaxRates = $StmtExporterTaxRates->fetch_assoc();


	// SELECT * FROM `ScrmExporterTaxRates` where ExpID='1' and FromDate<='2024-07-12' ORDER BY ExpTRID DESC limit 1;
	// { "ExpTRID": "3", "ExpID": "1", "CountryID": "", "ClearanceCharge": "1", "AntiDumpingDuty": "1.35", 
	// 	"MerchandiseProcessFee": "0", "HarbourMaintenanceFee": "0", "CountervailingDuty": "4.36", 
	// 	"ServiceTaxPercentage": "0", "DrawbackRateperKG": "23.6", "DrawbackPercentage": "0", 
	// 	"BLServiceTax": "0", "ISFStaticDuty": "0", "InsurancePremiumRate": "0.11", "UsClearance": "", 
	// 	"IGSTRaw": "", "IGSTBrainFrozen": "", "IGSTCooked": "", "IGSTCookedInShell": "", "IGSTBlanched": "", 
	// 	"FromDate": "2024-04-01", "UptoDate": "", "PostedDate": "2024-08-06 16:47:55" 
	// } Harinath

	
	$AntiDumpingDutyPerc = $RowExporterTaxRates['AntiDumpingDuty'];
	$CountervailingDutyPerc = $RowExporterTaxRates['CountervailingDuty'];//uma cvd calculation included //18-03-2024
	$MerchandiseProcessFeePerc = $RowExporterTaxRates['MerchandiseProcessFee']; 
	$HarbourMaintenanceFeePerc = $RowExporterTaxRates['HarbourMaintenanceFee'];
	$ISFStaticDuty = $RowExporterTaxRates['ISFStaticDuty'];
	$InsurancePerc = $RowExporterTaxRates['InsurancePremiumRate'];
	$ReciprocalTariffsPerc = $RowExporterTaxRates['ReciprocalTariffs'];
	$ClearingAndForwardingCharges = $RowExporterTaxRates['ClearingCharges'];
	$TariffPerc = $RowExporterTaxRates['Tariff'];
	$PrintTariff = 1;
	$PrintReciprocalTariff = 1;

	$taxDateQuery = '';
	if ($DocType == 'PI') {
		$taxDateQuery = " and FromDate<='$PInvDate'";
	}
	else {
		$taxDateQuery = " and FromDate<='$CInvDate'";
	}

	$SqlReciprocalTariffsExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $taxDateQuery ORDER BY ExpTRID DESC limit 1"; 
	$StmtReciprocalTariffsExporterTaxRates = $iconn->query($SqlReciprocalTariffsExporterTaxRates);
	$RowReciprocalTariffsExporterTaxRates = $StmtReciprocalTariffsExporterTaxRates->fetch_assoc();
	$ReciprocalTariffsPerc = $RowReciprocalTariffsExporterTaxRates['ReciprocalTariffs'];
	$TariffPerc = $RowExporterTaxRates['Tariff'];

	// Temporary logic added only for the DEV client. This will be removed soon.
	if ($BLDate && $AdminConfigDetails['ClientID'] == 'DFLV3' && $AdminConfigDetails['ComInvPrintReciprocal'] && $AdminConfigDetails['ComInvPrintReciprocal'] == true) {
		$blDate = new DateTime($BLDate);
		$startDate = new DateTime('2025-04-05');
		$endDate = new DateTime('2025-04-10');

		if ( $blDate <= $endDate) {
			$reciprocalSql = "SELECT * FROM `ReciprocalTariffs` WHERE PoID=$PoID";
			$reciprocalStmt = $iconn->query($reciprocalSql);
			$reciprocalRow = $reciprocalStmt->fetch_assoc();
			$ReciprocalTariff = $reciprocalRow['ReciprocalTariff'];
			$TariffRate = $reciprocalRow['TariffRate'];

			if (!empty($ReciprocalTariff) && !empty($TariffRate) && $ReciprocalTariff == 1) {
				$ReciprocalTariffsPerc = $TariffRate;
			}
		}
		
		
	}



	// if ($DocType == 'CMI') {
	$ClearanceChargePerc = $RowExporterTaxRates['ClearanceCharge'];//uma ClearanceCharge calculation included //01-04-2024
	// }
	// $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
	// $MerchandiseProcessFeePerc +
	// $HarbourMaintenanceFeePerc;

	$SqlRevPoTaxRates = "SELECT * FROM `ScrmPoTaxRates` where PoID='$PoID' AND InvoiceType = '$DocType' ORDER BY ID DESC";
	$StmtRevPoTaxRates = $iconn->query($SqlRevPoTaxRates);
	if ($StmtRevPoTaxRates && $StmtRevPoTaxRates->num_rows > 0) {
		$RowRevPoTaxRates = $StmtRevPoTaxRates->fetch_assoc();
		// echo json_encode($RowRevPoTaxRates);exit;
		$AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
		$CountervailingDutyPerc = $RowRevPoTaxRates['CountervailingDuty'];
		$MerchandiseProcessFeePerc = $RowRevPoTaxRates['MerchandiseProcessFee']; 
		$HarbourMaintenanceFeePerc = $RowRevPoTaxRates['HarbourMaintenanceFee'];
		$ISFStaticDuty = $RowRevPoTaxRates['ISFStaticDuty'];
		$InsurancePerc = $RowRevPoTaxRates['InsurancePremiumRate'];
		$ReciprocalTariffsPerc = $RowRevPoTaxRates['ReciprocalTariff'];
		$ClearingAndForwardingCharges = $RowRevPoTaxRates['ClearingCharges'];
		$TariffPerc = $RowRevPoTaxRates['Tariff'];
		$PrintTariff = $RowRevPoTaxRates['PrintTariff'];
		$PrintReciprocalTariff = $RowRevPoTaxRates['PrintReciprocalTariff'];
		if(!$PrintTariff){
			$TariffPerc = 0;
		}
		if(!$PrintReciprocalTariff){
			$ReciprocalTariffsPerc = 0;
		}
	}

	$TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
	$MerchandiseProcessFeePerc +
	$HarbourMaintenanceFeePerc + $CountervailingDutyPerc +
	$ReciprocalTariffsPerc + $TariffPerc; //uma cvd calculation included //18-03-2024

// echo $TotalDutyPerc; exit;

	$IsFreight = $RowShipTermsMaster['Freight'];
	$IsDuty = $RowShipTermsMaster['Duty'];
	$Insurance = $RowShipTermsMaster['Insurance'];
	$AntiDumpingDFactor = $RowShipTermsMaster['AntiDumpingDFactor'];
	$AntiDumpingMFactor = $RowShipTermsMaster['AntiDumpingMFactor'];
	$StandardDutyCharges = $RowShipTermsMaster['StandardDutyCharges'];



	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


	// $SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
	// 		sum(Price) as Price,			
	// 		sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
	// 		sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
    // 		SUM(Mcs * TotalSlabs * SlabWeight * MFactor * Price / 0.454) AS UsdValue,
	// 		GlazeStatus,
	// 		SPV.GlazePerc,ISF.BLDate
	// 		FROM `ScrmPoGradeData` as PGD
	// 		left join ScrmPIVarietyList as SPV on SPV.VarietyID=PGD.VarietyID
	// 		left join ScrmISFDetails as ISF on ISF.PIID=PGD.InvId
	// 		left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";

	$SqlTotal = "SELECT sum(Mcs) as MCS, $sqlExt,
			sum(Price) as Price,PS.Uom,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price ELSE 0 END) AS PriceOZKgs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * Price ELSE 0 END) AS PriceOZLbs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * MFactor ELSE 0 END) AS PriceKgs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor ELSE 0 END) AS PriceLbs,
    		sum(Mcs*TotalSlabs*SlabWeight*MFactor*Price / 0.454) AS UsdValue,
			GlazeStatus,
			VL.GlazePerc,ISF.BLDate
			FROM `ScrmPoGradeData` as PGD

LEFT JOIN ScrmPIVarietyList VL
    ON VL.VarietyID = PGD.VarietyID

LEFT JOIN ScrmISFDetails ISF
    ON ISF.PIID = PGD.InvId

LEFT JOIN AdminPackingStyles PS
    ON PS.PCKID =
       IF(
           VL.HasSauce = 'Yes' AND IFNULL(PGD.CUPS, 0) > 0,
           VL.ShrimpPackingStyle,
           VL.PackingStyle
       )

			
			WHERE InvId='$PoID'";
	$StmtTotal = $iconn->query($SqlTotal);
	// echo $SqlTotal; exit;
	$RowTotal = $StmtTotal->fetch_assoc();




	// SELECT sum(Mcs) as MCS, sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs, sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs, 
	// sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs, sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs , 
	// sum(Price) as Price, sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs, 
	// sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs, GlazeStatus, SPV.GlazePerc FROM `ScrmPoGradeData` as PGD 
	// left join ScrmPIVarietyList as SPV on SPV.VarietyID=PGD.VarietyID 
	// left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='3326'
	
	// { "MCS": "3285", "NetWgtKgs": "15569.8", "GrossWeightKgs": "17422.25", "NetWgtLbs": "34290.4", "GrossWeightLbs": "38375", 
	// 	"Price": "29.050000000000004", "PriceKgs": "56498.59", "PriceLbs": "124430.92", "GlazeStatus": "No", "GlazePerc": "" }

	// SELECT sum(Mcs) as MCS, sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs, sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs, 
	// sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs, sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs , 
	// sum(Price) as Price, sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs, 
	// sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` as PGD 
	// left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='3326'
	// { "MCS": "3285", "NetWgtKgs": "15569.8", "GrossWeightKgs": "17422.25", "NetWgtLbs": "34290.4", "GrossWeightLbs": "38375", 
	// 	"Price": "29.050000000000004", "PriceKgs": "56498.59", "PriceLbs": "124430.92" } Harinath

	
	$IsSauce = checkSauceExists($iconn, $PoID);
	$SauceTotalUsdValue = 0;
	$SauceTotalKgs = 0;
	$SauceTotalLbs = 0;
	if ($IsSauce && $IsSauce == 1) {
		$SqlSauce = "SELECT PGD.Mcs, PGD.VarietyID, PGD.CUPS, PGD.CupsPrice, PS.*, SPD.QtyUnit
			FROM ScrmPoGradeData as PGD
			LEFT JOIN ScrmPoData as SPD ON SPD.PoID = PGD.InvId
			LEFT JOIN ScrmPIVarietyList as SPVL ON SPVL.VarietyID = PGD.VarietyID
			LEFT JOIN AdminPackingStyles as PS ON SPVL.SaucePackingStyle = PS.PCKID
			WHERE InvId='$PoID' AND SPVL.HasSauce = 'Yes' AND PGD.CUPS > 0";
		$StmtSauce = $iconn->query($SqlSauce);
		while ($RowSauce = $StmtSauce->fetch_assoc()) {
			$SauceLbsWeight = $RowSauce['TotalSlabs'] * $RowSauce['SlabWeight'] * $RowSauce['Mcs'];
			if ($RowSauce['Uom'] == 'OZ') {
                $OzToLbs = $RowSauce['SlabWeight'] * $RowSauce['LbsFactor'];
                $SauceLbsWeight *= $OzToLbs / $RowSauce['SlabWeight'];
            }
            $SauceKgsWeight = $SauceLbsWeight * 0.454;
			if ($RowSauce['QtyUnit'] == 'LBS' && $PType == 'KGS') {
				$QtyCalc = $SauceKgsWeight;
				$RowSauce['CupsPrice'] = $RowSauce['CupsPrice'] / 0.454;
			} else {
				$QtyCalc = $SauceLbsWeight;
			}
            $SauceTotalUsdValue += $QtyCalc * $RowSauce['CupsPrice'];
			// echo $SauceTotalUsdValue;exit;
            $SauceTotalKgs += $SauceKgsWeight;
            $SauceTotalLbs += $SauceLbsWeight;
		}
	}

	$NetWgtKgs = $RowTotal['NetWgtLbs'] * $LbsFactor;
	$NetWgtKgs += $SauceKgsWeight;
	$NetWgtLbs = $RowTotal['NetWgtLbs'];
	$NetWgtLbs += $SauceTotalLbs;

	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	$GrossWeightLbs = $RowTotal['GrossWeightLbs'];


	if ($QtyUnit == 'KGS') {
		$Weight = $RowTotal['NetWgtKgs'] + $SauceKgsWeight;
		$InvoiceValue = $RowTotal['PriceOZKgs'] > 0 ? $RowTotal['PriceOZKgs'] + $RowTotal['PriceKgs'] : $RowTotal['PriceKgs'];
		$AryPrices['ShrimpValueUsd'] = $InvoiceValue;
		$AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;
		// $InvoiceValue += $SauceTotalUsdValue; // Add Sauce USD Value
	} else {
		$Weight = $RowTotal['NetWgtLbs'] * $LbsFactor;
		$InvoiceValue = $RowTotal['PriceOZLbs'] > 0 ? $RowTotal['PriceOZLbs'] + $RowTotal['PriceLbs'] : $RowTotal['PriceLbs'];
		$AryPrices['ShrimpValueUsd'] = $InvoiceValue;
		$AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;
		// $InvoiceValue += $SauceTotalUsdValue * 0.454;
	}

	$InvoiceValue += $SauceTotalUsdValue;

	// echo $SauceTotalUsdValue;exit;
	// $InvoiceValue = $RowTotal['UsdValue'];
	
	

	if ($Discount > 0) {

		if(isset($DiscountType) && $DiscountType == 0) {
			$DiscountAmount = ($InvoiceValue * $Discount) / 100;
			$InvoiceValueAfterDiscount = $InvoiceValue; 
			$InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; // By Rantagiri 04/17/2023 5.44 
		}

		else{
			$DiscountAmount =$Discount;
			$InvoiceValueAfterDiscount = $InvoiceValue;
			$InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; 
		}

	} 

	else {
		$InvoiceValueAfterDiscount = $InvoiceValue;
	}

	if ($Insurance == 1) {
		//$InsuranceValue= ($PriceAfterDiscount*$InsurancePerc)/100;
		$InsuranceValue = ($InvoiceValueAfterDiscount * $InsurancePerc) / 100;
	} else {
		$InsuranceValue = 0;
	}

	// $InsuranceValue = 0;


	if ($IsDuty == 1) {

		$ClearanceChargeFee = (($InvoiceValueAfterDiscount- $InsuranceValue - $FrieghtCharge)*$ClearanceChargePerc)/100;//#jme uma ClearanceCharge calculation included //01-04-2024

		$MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
		//$MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);

		if ($MerchandiseProcessFeePerc == 0) {
			$ISFStatic = false;
			$MerchandiseProcessFee = $ISFStaticDuty;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
			// $MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);

		} else if ($ISFStaticDuty != 0 && $MerchandiseProcessFee > $ISFStaticDuty) {
			$ISFStatic = true;
				$MerchandiseProcessFee = $ISFStaticDuty; 
			$TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges - $MerchandiseProcessFee;
		} 
		else {
			$ISFStatic = false;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges; 
			$MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
			// $MerchandiseProcessFee=$ISFStaticDuty;
		}
		// ECHO $MerchandiseProcessFee; EXIT;

		// echo 'InvValueUSD: ' . $InvoiceValueAfterDiscount;
		// echo 'EffInvValue: ' . $EffInvValue;
		// echo '<br>MerchandiseProcessFee: ' . $MerchandiseProcessFee;
		// echo '<br>InsuranceValue: ' . $InsuranceValue;
		// echo '<br>FrieghtCharge: ' . $FrieghtCharge;
		// echo '<br>ClearanceChargeFee: ' . $ClearanceChargeFee;
		// echo '<br>ISFStaticDuty: ' . $ISFStaticDuty;
		// echo '<br>ISFStatic: ' . $ISFStatic;
		// echo '<br>TotalDutyPerc: ' . $TotalDutyPerc;
		// echo '<br>AntiDumpingDutyPerc: ' . $AntiDumpingDutyPerc;
		// echo '<br>HarbourMaintenanceFeePerc: ' . $HarbourMaintenanceFeePerc;
		// echo '<br>CountervailingDutyPerc: ' . $CountervailingDutyPerc;
		// echo '<br>ReciprocalTariffsPerc: ' . $ReciprocalTariffsPerc;
		// echo '<br>InvoiceValueAfterDiscount: ' . $InvoiceValueAfterDiscount;
		// echo '<br>InvoiceValue: ' . $InvoiceValue;
		// echo '<br>DiscountAmount: ' . $DiscountAmount;
		// echo '<br>InsurancePerc: ' . $InsurancePerc;
		// echo '<br>SauceTotalUsdValue: ' . $SauceTotalUsdValue;
		// echo '<br>ShrimpTotalUsdValue: ' . $AryPrices['ShrimpValueUsd'];
		// exit;
		$EffInvValue=$EffInvValue-$ClearanceChargeFee;//#jme uma ClearanceCharge calculation included //01-04-2024
		
		// if ($IsSauce && $IsSauce == 1) {
		// 	$EffInvValue = $EffInvValue - $SauceTotalUsdValue; // Deduct Sauce USD Value from Effective Invoice Value
		// }

		$AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
		$HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);
		$CountervailingDutyFee = round((($EffInvValue * $CountervailingDutyPerc) / $TotalDutyPerc), 2);//uma cvd calculation included //18-03-2024
		$ReciprocalTariffsFee = 0;
		if ($ReciprocalTariffsPerc > 0) {
			$ReciprocalTariffsFee = round((($EffInvValue * $ReciprocalTariffsPerc) / $TotalDutyPerc), 2);//uma cvd calculation included //18-03-2024
		}
		if($TariffPerc > 0){
			$TariffFee = round((($EffInvValue * $TariffPerc) / $TotalDutyPerc), 2);
		}
		if ($ISFStatic) {
			// $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee;
				$DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee+$CountervailingDutyFee+$ReciprocalTariffsFee+$TariffFee; 
				// $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee+$CountervailingDutyFee+$ClearanceChargeFee; //uma ClearanceCharge calculation included //01-04-2024
		} else {
			// $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee;
			$DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee+$CountervailingDutyFee+$ReciprocalTariffsFee+$TariffFee;//uma cvd calculation included //18-03-2024
			// $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee+$CountervailingDutyFee+$ClearanceChargeFee;//uma ClearanceCharge calculation included //01-04-2024
		}

	} else {
		$DutiesPrice = $DutiesPrice + $StandardDutyCharges; 
	}

	// echo $DutiesPrice; exit;


	$InvValueUSD = $InvoiceValueAfterDiscount; 
// echo $DutiesPrice; exit;
	if ($ISFStatic) {

		//commented by uma
		// $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty;
		// $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		// $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty; 

		//commented by uma

		$InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty-$InsuranceValue;//#jme uma ClearanceCharge calculation included //01-04-2024
		$InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		$FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty-$InsuranceValue-$ClearanceChargeFee;//#jme uma ClearanceCharge calculation included //01-04-2024
	} else {
		// $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge;
		// $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		// $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice;

		// $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge-$InsuranceValue;
		$InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge-$InsuranceValue;//#jme uma ClearanceCharge calculation included //01-04-2024
		$InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		$FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice-$InsuranceValue-$ClearanceChargeFee;//#jme uma ClearanceCharge calculation included //01-04-2024
	}

	$HasCFLCharges = $AdminConfigDetails['HasCLFCharges'];
	if($HasCFLCharges){
		$InvValueUSDWithOutDuties = $InvValueUSDWithOutDuties - $ClearingAndForwardingCharges;
		$FOBValueUSD -= $ClearingAndForwardingCharges;
	}

	$InvValueINR = $InvValueUSD * $ExchangeRate;
	$FOBValueINR = $FOBValueUSD * $ExchangeRate;



	$IgstValueBasedonProducttype=getIgstValueBasedonProducttype($iconn, $PoID,$ExchangeRate); //if Cooked and raw both are existed in same po

// print_r($IgstValueBasedonProducttype); exit;
	$IgstValue = ($InvValueINR * $IgstPerc) / 100;



	$AryPrices['DiscountPerc'] = $Discount;
	$AryPrices['ExchangeRate'] = $ExchangeRate;
	$AryPrices['DiscountAmount'] = $DiscountAmount;
	$AryPrices['InsuranceValue'] = $InsuranceValue;
	$AryPrices['DutiesPrice'] = $DutiesPrice;
	$AryPrices['FrieghtCharge'] = $FrieghtCharge;

	$AryPrices['InvValueUSD'] = $InvValueUSD;
	$AryPrices['InvValueUSDWithOutDuties'] = $InvValueUSDWithOutDuties;
	$AryPrices['InvValueINRWithOutDuties'] = $InvValueINRWithOutDuties;
	$AryPrices['InvValueINR'] = $InvValueINR;
	$AryPrices['FOBValueUSD'] = $FOBValueUSD;
	$AryPrices['FOBValueINR'] = $FOBValueINR;
	$AryPrices['PaymentTerm'] = $TermName;
	$AryPrices['IgstPerc'] = $IgstPerc;
	$AryPrices['IgstValue'] = $IgstValue;
	$AryPrices['AntiDumpingDuty'] = $AntiDumpingCharges; 
	$AryPrices['MerchantDuty'] = $MerchandiseProcessFee; 
	$AryPrices['HarborDuty'] = $HarbourMaintenanceFee; 
	$AryPrices['CountervailingDuty'] = $CountervailingDutyFee; //uma cvd calculation included //18-03-2024
	$AryPrices['ReciprocalTariffsFee'] = $ReciprocalTariffsFee; 
	$AryPrices['TariffFee'] = $TariffFee;
	$AryPrices['TariffPerc'] = $TariffPerc;
	$AryPrices['PrintTariff'] = $PrintTariff;
	$AryPrices['PrintReciprocalTariff'] = $PrintReciprocalTariff;

	$AryPrices['ClearanceCharge'] = round($ClearanceChargeFee,2); //uma ClearanceCharge calculation included //01-04-2024
	$AryPrices['ISFStatic'] = $ISFStatic; 
	$AryPrices['CenttoUsd'] = $CenttoUsd;
	$AryPrices['ActualPrice'] = $InvoiceValue;
	$AryPrices['HarbourMaintenanceFeePerc'] = $HarbourMaintenanceFeePerc;
	$AryPrices['CountervailingDutyPerc'] = $CountervailingDutyPerc;//uma cvd calculation included //18-03-2024
	$AryPrices['ReciprocalTariffsPerc'] = $ReciprocalTariffsPerc;

	$AryPrices['ClearanceChargePerc'] = $ClearanceChargePerc;//uma ClearanceCharge calculation included //01-04-2024
	$AryPrices['AntiDumpingDutyPerc'] = $AntiDumpingDutyPerc;
	$AryPrices['MerchandiseProcessFeePerc'] = $MerchandiseProcessFeePerc;

	$AryPrices['IgstValueBasedonProducttype'] = $IgstValueBasedonProducttype;////if Cooked and raw both are existed in same po #jme
	$AryPrices['ClearingAndForwardingCharges'] = $ClearingAndForwardingCharges;


	return $AryPrices;

}

function getPaymentValues($iconn, $PoID, $DocType, $AdminConfigDetails)
{
	$DiscountType = $AdminConfigDetails['DiscountType'];

	// $SqlCInvDate = "SELECT CInvDate FROM `ScrmCustomInvoices` WHERE PiID=$PoID";
	// $StmtCInvDate = $iconn->query($SqlCInvDate);
	// $RowCInvDate = $StmtCInvDate->fetch_assoc();
	// $CInvDate = $RowCInvDate['CInvDate'];

	$ClientId = $_SESSION['ClientID'];

	$PoTableName = "ScrmPIData";
	$SqlPIData = "SELECT * FROM ScrmPoData as SPD WHERE SPD.PoID = $PoID";
	$StmtPIData = $iconn->query($SqlPIData);

	if ($StmtPIData->num_rows > 0) {
		$PoTableName = "ScrmPoData";
	}

	$PType = $AdminConfigDetails['PType'];

	$gradeTableName = ($ClientId == 'DFLV3' && ($DocType == 'PI' || $DocType == 'CMI') && $PoTableName == 'ScrmPoData'
		&& $PType == 'UNITS') ? 'ScrmPIGradeData' : 'ScrmPoGradeData';

	if ($AdminConfigDetails['enableCustomInvoicePoData'] == 1 && $DocType == 'PI') {
		$gradeTableName = "ScrmPIGradeData";
	}

	if ($gradeTableName == 'ScrmPIGradeData') {
		$checkDataExists = "SELECT * FROM $gradeTableName WHERE InvId=$PoID";
		$checkStmt = $iconn->query($checkDataExists);
		if ($checkStmt->num_rows == 0) {
			$gradeTableName = "ScrmPoGradeData";
		}
	}

	$CenttoUsd = 100;

	if ($DocType == 'CMI') {
		$SqlCInvData = "SELECT *, ExchngRte as ExRate, SPD.ExporterID as ExpID FROM $PoTableName as SPD
			LEFT JOIN ScrmCommercialData as SCD on
			SPD.PoID = SCD.PoID WHERE SPD.PoID = $PoID";
	} else {
		$SqlCInvData = "SELECT *, SPD.ExporterID as ExpID, SPD.Freight as PIFreight FROM $PoTableName as SPD
			LEFT JOIN ScrmCustomInvoices as SCI on
			SPD.PoID = SCI.PiID WHERE SPD.PoID = $PoID";
	}

	$StmtCInvData = $iconn->query($SqlCInvData);
	$RowCInvData = $StmtCInvData->fetch_assoc();

	$TermName = $RowCInvData['ShippingTerms'];
	$Discount = $RowCInvData['Discount'];
	$FrieghtCharge = $DocType == 'PI' ? $RowCInvData['PIFreight'] : $RowCInvData['Freight'];
	$ExchangeRate = $RowCInvData['ExRate'];
	$QtyUnit = $RowCInvData['QtyUnit'];
	$CInvCode = $RowCInvData['CInvCode'];
	$ExporterID = $RowCInvData['ExpID'];
	$PInvDate = $RowCInvData['InvDate'];
	$DestinationCountryId = $RowCInvData['DestinationCountry'];

	/* uma(14-10-2023) */
	// $SqlGstPerc = "SELECT DISTINCT GstPerc FROM `ScrmPoGradeData` as SPGD
	// 	left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
	// 	left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

	$SqlGstPerc = "SELECT DISTINCT(SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG
		WHERE SPMG.ParentId=SPM.ID AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1)
		GstPerc, SPM.GstPerc GstPercP, FDAEntryDate, CInvDate, TypeProduct, ISF.BLDate FROM $gradeTableName as SPGD
		left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
		left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
		left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId
		left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

	$StmtGstPerc = $iconn->query($SqlGstPerc);
	$RowGstPerc = $StmtGstPerc->fetch_assoc();
	$StmtRawCookedGstPerc = $iconn->query($SqlGstPerc); #jme

	// while ($RowHsCode = $StmtHsCode->fetch_assoc()) {
	// 	$ArrayHsCode[] = $RowHsCode['HSCode'];
	// 	$CInvCode = implode(',', $ArrayHsCode);
	// }

	$IgstPerc = !empty($RowGstPerc['GstPerc']) ? $RowGstPerc['GstPerc'] : $RowGstPerc['GstPercP'];
	$FDAEntryDate = $RowGstPerc['FDAEntryDate'];
	$CInvDate = $RowGstPerc['CInvDate'];
	$BLDate = $RowGstPerc['BLDate'];

	// if ($CInvCode == '03061720') {
	// 	$IgstPerc = 5;
	// } else {
	// 	$IgstPerc = 12;
	// }

	/* uma (14-10-2023) */
	$QtyUnit = $RowCInvData['QtyUnit'];
	$Query = " and FromDate<='$CInvDate'";
	if ($DocType == 'CMI') {
		if ($FDAEntryDate != '') {
			$Query = " and FromDate<='$FDAEntryDate'";
		}
	} else if ($DocType == 'PI') {
		$Query = " and FromDate<='$PInvDate'";
	}

	$SqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'";
	$StmtShipTermsMaster = $iconn->query($SqlShipTermsMaster);
	$RowShipTermsMaster = $StmtShipTermsMaster->fetch_assoc();

	// Update With ExportId Condition
	// $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates`";
	// $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where FromDate<='$CInvDate' ORDER BY ExpTRID DESC limit 1";

	$SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query ORDER BY ExpTRID DESC limit 1";
	$StmtExporterTaxRates = $iconn->query($SqlExporterTaxRates);
	$RowExporterTaxRates = $StmtExporterTaxRates->fetch_assoc();
	$AntiDumpingDutyPerc = $RowExporterTaxRates['AntiDumpingDuty'];
	$CountervailingDutyPerc = $RowExporterTaxRates['CountervailingDuty'];
	$MerchandiseProcessFeePerc = $RowExporterTaxRates['MerchandiseProcessFee'];
	$HarbourMaintenanceFeePerc = $RowExporterTaxRates['HarbourMaintenanceFee'];
	$ISFStaticDuty = $RowExporterTaxRates['ISFStaticDuty'];
	$InsurancePerc = $RowExporterTaxRates['InsurancePremiumRate'];
	$ReciprocalTariffsPerc = $RowExporterTaxRates['ReciprocalTariffs'];
	$ClearanceChargePerc = $RowExporterTaxRates['ClearanceCharge'];
	$ClearingAndForwardingCharges = $RowExporterTaxRates['ClearingCharges'];
	$TariffPerc = $RowExporterTaxRates['Tariff'];
	$PrintTariff = 1;
	$PrintReciprocalTariff = 1;

	$taxDateQuery = '';
	if ($DocType == 'PI') {
		$taxDateQuery = " and FromDate<='$PInvDate'";
	} else {
		$taxDateQuery = " and FromDate<='$CInvDate'";
	}

	// $SqlReciprocalTariffsExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' and FromDate<='$CInvDate' ORDER BY ExpTRID DESC limit 1";
	$SqlReciprocalTariffsExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $taxDateQuery ORDER BY ExpTRID DESC limit 1";
	$StmtReciprocalTariffsExporterTaxRates = $iconn->query($SqlReciprocalTariffsExporterTaxRates);
	$RowReciprocalTariffsExporterTaxRates = $StmtReciprocalTariffsExporterTaxRates->fetch_assoc();
	$ReciprocalTariffsPerc = $RowReciprocalTariffsExporterTaxRates['ReciprocalTariffs'];
	$TariffPerc = $RowReciprocalTariffsExporterTaxRates['Tariff'];

	// Temporary logic added only for the DEV client. This will be removed soon.
	if ($BLDate && $AdminConfigDetails['ClientID'] == 'DFLV3' && $AdminConfigDetails['ComInvPrintReciprocal'] && $AdminConfigDetails['ComInvPrintReciprocal'] == true) {
		$blDate = new DateTime($BLDate);
		$startDate = new DateTime('2025-04-05');
		$endDate = new DateTime('2025-04-10');

		if ($blDate <= $endDate) {
			$reciprocalSql = "SELECT * FROM `ReciprocalTariffs` WHERE PoID=$PoID";
			$reciprocalStmt = $iconn->query($reciprocalSql);
			$reciprocalRow = $reciprocalStmt->fetch_assoc();
			$ReciprocalTariff = $reciprocalRow['ReciprocalTariff'];
			$TariffRate = $reciprocalRow['TariffRate'];

			if (!empty($ReciprocalTariff) && !empty($TariffRate) && $ReciprocalTariff == 1) {
				$ReciprocalTariffsPerc = $TariffRate;
			}
		}
	}

	// $ETA_FDate = 0;

	// $checkETAFDQry = "SELECT EstimatedETA FROM `ScrmISFDetails` where PIID ='$PoID'";
	// $StmtETAFD = $iconn->query($checkETAFDQry);
	// $RowETAFD = $StmtETAFD->fetch_assoc();

	// if (!empty($RowETAFD['EstimatedETA']) && $RowETAFD['EstimatedETA'] > '2026-03-01') {
	// 	$ETA_FDate = 1;
	// }

	// $fincYear = (int)(new DateTime($CInvDate))->format('Y');

	// if ($DocType == 'CMI' && ($fincYear == 2025 || $fincYear == 2026) && $ETA_FDate == 1) {
	// 	$AdCMIQuery = "";
	// 	$SqlAdCMIRates = "SELECT AntiDumpingDuty FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' ORDER BY ExpTRID DESC limit 1";
	// 	$StmtAdCMIRates = $iconn->query($SqlAdCMIRates);
	// 	$RowAdCMIRates = $StmtAdCMIRates->fetch_assoc();
	// 	$AntiDumpingDutyPerc = $RowAdCMIRates['AntiDumpingDuty'];
	// }

	$SqlRevPoTaxRates = "SELECT * FROM `ScrmPoTaxRates` where PoID = '$PoID' AND InvoiceType = '$DocType' ORDER BY ID DESC";
	$StmtRevPoTaxRates = $iconn->query($SqlRevPoTaxRates);
	if ($StmtRevPoTaxRates && $StmtRevPoTaxRates->num_rows > 0) {
		$RowRevPoTaxRates = $StmtRevPoTaxRates->fetch_assoc();
		$AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
		$CountervailingDutyPerc = $RowRevPoTaxRates['CountervailingDuty'];
		$MerchandiseProcessFeePerc = $RowRevPoTaxRates['MerchandiseProcessFee'];
		$HarbourMaintenanceFeePerc = $RowRevPoTaxRates['HarbourMaintenanceFee'];
		$ISFStaticDuty = $RowRevPoTaxRates['ISFStaticDuty'];
		$InsurancePerc = $RowRevPoTaxRates['InsurancePremiumRate'];
		$ReciprocalTariffsPerc = $RowRevPoTaxRates['ReciprocalTariff'];
		$ClearingAndForwardingCharges = $RowRevPoTaxRates['ClearingCharges'];
		$TariffPerc = $RowRevPoTaxRates['Tariff'];
		$PrintTariff = $RowRevPoTaxRates['PrintTariff'];
		$PrintReciprocalTariff = $RowRevPoTaxRates['PrintReciprocalTariff'];
		if (!$PrintTariff) {
			$TariffPerc = 0;
		}
		if (!$PrintReciprocalTariff) {
			$ReciprocalTariffsPerc = 0;
		}
	}

	$TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
		$MerchandiseProcessFeePerc + $HarbourMaintenanceFeePerc + $CountervailingDutyPerc +
		$ReciprocalTariffsPerc + $TariffPerc;

	$IsFreight = $RowShipTermsMaster['Freight'];
	$IsDuty = $RowShipTermsMaster['Duty'];
	$Insurance = $RowShipTermsMaster['Insurance'];
	$AntiDumpingDFactor = $RowShipTermsMaster['AntiDumpingDFactor'];
	$AntiDumpingMFactor = $RowShipTermsMaster['AntiDumpingMFactor'];
	$StandardDutyCharges = $RowShipTermsMaster['StandardDutyCharges'];

	$InvoiceValue = 0;
	$IsSauce = checkSauceExists($iconn, $PoID);

	$AdminConfigDetails['ClientId'] = $ClientId;
	$AdminConfigDetails['GradeTableName'] = $gradeTableName;
	$AdminConfigDetails['DestCountryID'] = $DestinationCountryId;
	$AryBreakupData = getGradeBreakUpData($iconn, '', $PoID, $AdminConfigDetails);
	// echo json_encode($AryBreakupData, JSON_PRETTY_PRINT);exit;
	$InvoiceValue = $AryBreakupData['UsdValue'];

	if ($Discount > 0) {
		if (isset($DiscountType) && $DiscountType == 0) {
			$DiscountAmount = ($InvoiceValue * $Discount) / 100;
			$InvoiceValueAfterDiscount = $InvoiceValue;
			$InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; // By Rantagiri 04/17/2023 5.44
		} else {
			$DiscountAmount = $Discount;
			$InvoiceValueAfterDiscount = $InvoiceValue;
			$InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount;
		}
	} else {
		$InvoiceValueAfterDiscount = $InvoiceValue;
	}

	if ($Insurance == 1) {
		// $InsuranceValue = ($PriceAfterDiscount * $InsurancePerc) / 100;
		$InsuranceValue = ($InvoiceValueAfterDiscount * $InsurancePerc) / 100;
	} else {
		$InsuranceValue = 0;
	}

	if ($IsDuty == 1) {
		$ClearanceChargeFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge) * $ClearanceChargePerc) / 100;
		$MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
		// $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);

		if ($MerchandiseProcessFeePerc == 0) {
			$ISFStatic = false;
			$MerchandiseProcessFee = $ISFStaticDuty;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
			// $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
		} else if ($ISFStaticDuty != 0 && $MerchandiseProcessFee > $ISFStaticDuty) {
			$ISFStatic = true;
			$MerchandiseProcessFee = $ISFStaticDuty;
			$TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges - $MerchandiseProcessFee;
		} else {
			$ISFStatic = false;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
			$MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
			// $MerchandiseProcessFee = $ISFStaticDuty;
		}

		// $HasCFLCharges = $AdminConfigDetails['HasCLFCharges'];
		// if ($HasCFLCharges) {
		// 	$EffInvValue = $EffInvValue - $ClearingAndForwardingCharges;
		// }

		$EffInvValue = $EffInvValue - $ClearanceChargeFee;
		$AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
		$HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);
		$CountervailingDutyFee = round((($EffInvValue * $CountervailingDutyPerc) / $TotalDutyPerc), 2);
		$ReciprocalTariffsFee = 0;

		if ($ReciprocalTariffsPerc > 0) {
			$ReciprocalTariffsFee = round((($EffInvValue * $ReciprocalTariffsPerc) / $TotalDutyPerc), 2);
		}

		if ($TariffPerc > 0) {
			$TariffFee = round((($EffInvValue * $TariffPerc) / $TotalDutyPerc), 2);
		}

		if ($ISFStatic) {
			$DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee + $TariffFee;
		} else {
			$DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee + $TariffFee;
		}
	} else {
		$DutiesPrice = $DutiesPrice + $StandardDutyCharges;
	}

	$InvValueUSD = $InvoiceValueAfterDiscount;

	if ($ISFStatic) {
		$InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty - $InsuranceValue;
		$InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		$FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty - $InsuranceValue - $ClearanceChargeFee;
	} else {
		$InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $InsuranceValue;
		$InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		$FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $InsuranceValue - $ClearanceChargeFee;
	}

	$HasCFLCharges = $AdminConfigDetails['HasCLFCharges'];
	if ($HasCFLCharges) {
		$InvValueUSDWithOutDuties = $InvValueUSDWithOutDuties - $ClearingAndForwardingCharges;
		$FOBValueUSD -= $ClearingAndForwardingCharges;
	}

	$InvValueINR = $InvValueUSD * $ExchangeRate;
	$FOBValueINR = $FOBValueUSD * $ExchangeRate;

	// If Cooked and raw both are existed in same po
	$IgstValueBasedonProducttype = getIgstValueBasedonProducttype($iconn, $PoID, $ExchangeRate, $AdminConfigDetails);
	$IgstValue = ($InvValueINR * $IgstPerc) / 100;

	$AryPrices['DiscountPerc'] = $Discount;
	$AryPrices['ExchangeRate'] = $ExchangeRate;
	$AryPrices['DiscountAmount'] = $DiscountAmount;
	$AryPrices['InsuranceValue'] = $InsuranceValue;
	$AryPrices['DutiesPrice'] = $DutiesPrice;
	$AryPrices['FrieghtCharge'] = $FrieghtCharge;

	$AryPrices['InvValueUSD'] = $InvValueUSD;
	$AryPrices['InvValueUSDWithOutDuties'] = $InvValueUSDWithOutDuties;
	$AryPrices['InvValueINRWithOutDuties'] = $InvValueINRWithOutDuties;
	$AryPrices['InvValueINR'] = $InvValueINR;
	$AryPrices['FOBValueUSD'] = $FOBValueUSD;
	$AryPrices['FOBValueINR'] = $FOBValueINR;
	$AryPrices['PaymentTerm'] = $TermName;
	$AryPrices['IgstPerc'] = $IgstPerc;
	$AryPrices['IgstValue'] = $IgstValue;
	$AryPrices['AntiDumpingDuty'] = $AntiDumpingCharges;
	$AryPrices['MerchantDuty'] = $MerchandiseProcessFee;
	$AryPrices['HarborDuty'] = $HarbourMaintenanceFee;
	$AryPrices['CountervailingDuty'] = $CountervailingDutyFee;
	$AryPrices['ReciprocalTariffsFee'] = $ReciprocalTariffsFee;
	$AryPrices['TariffFee'] = $TariffFee;
	$AryPrices['TariffPerc'] = $TariffPerc;
	$AryPrices['PrintTariff'] = $PrintTariff;
	$AryPrices['PrintReciprocalTariff'] = $PrintReciprocalTariff;

	$AryPrices['ClearanceCharge'] = round($ClearanceChargeFee, 2);
	$AryPrices['ISFStatic'] = $ISFStatic;
	$AryPrices['CenttoUsd'] = $CenttoUsd;
	$AryPrices['ActualPrice'] = $InvoiceValue;
	$AryPrices['HarbourMaintenanceFeePerc'] = $HarbourMaintenanceFeePerc;
	$AryPrices['CountervailingDutyPerc'] = $CountervailingDutyPerc;
	$AryPrices['ReciprocalTariffsPerc'] = $ReciprocalTariffsPerc;

	$AryPrices['ClearanceChargePerc'] = $ClearanceChargePerc;
	$AryPrices['AntiDumpingDutyPerc'] = $AntiDumpingDutyPerc;
	$AryPrices['MerchandiseProcessFeePerc'] = $MerchandiseProcessFeePerc;

	$AryPrices['IgstValueBasedonProducttype'] = $IgstValueBasedonProducttype;
	$AryPrices['ClearingAndForwardingCharges'] = $ClearingAndForwardingCharges;

	return $AryPrices;
}


function getIgstValueBasedonProducttype($iconn,$PoID,$ExchangeRate,$AdminConfigDetails){

 /* $SqlGstPerc = "SELECT DISTINCT QtyUnit,GstPerc,FDAEntryDate,CInvDate,TypeProduct,
 sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price ELSE 0 END) AS PriceOZKgs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * Price ELSE 0 END) AS PriceOZLbs,
			sum(CASE 
				WHEN PS.Uom = 'KGS' THEN Mcs * TotalSlabs * SlabWeight * Price
				WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor 
				WHEN PS.Uom = 'LBS' THEN Mcs * TotalSlabs * SlabWeight * Price 
				WHEN PS.Uom = 'GRAMS' AND QtyUnit = 'LBS' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor 
				WHEN PS.Uom = 'GRAMS' AND QtyUnit = 'KGS' THEN Mcs * TotalSlabs * SlabWeight * Price * MFactor ELSE 0 END
			) AS PriceKgs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor ELSE 0 END) AS PriceLbs,
 			SUM(Mcs * TotalSlabs * SlabWeight * MFactor * Price / 0.454) AS UsdValue,
			SCI.Discount FROM `ScrmPoGradeData` as SPGD 
		left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
		left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID = SPGD.InvId
		left join AdminPackingStyles as PS on SPGD.PackingStyleID=PS.PCKID
		left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId		
		left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType AND SPM.FType = SPVL.FreezingType
		WHERE InvId='$PoID' AND GstPerc != 0 GROUP BY TypeProduct ORDER BY GstPerc DESC "; */
	$SqlGstPerc = "SELECT *,CASE WHEN GstPercC!= '' THEN GstPercC ELSE GstPercP END AS GstPerc FROM (
 			SELECT DISTINCT QtyUnit,
 						(SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1)
						GstPercC,
						SPM.GstPerc GstPercP,FDAEntryDate,CInvDate,TypeProduct,
				 sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price ELSE 0 END) AS PriceOZKgs,
				sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * Price ELSE 0 END) AS PriceOZLbs,
			sum(CASE 
				WHEN PS.Uom = 'KGS' THEN Mcs * TotalSlabs * SlabWeight * Price
				WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor 
				WHEN PS.Uom = 'LBS' THEN Mcs * TotalSlabs * SlabWeight * Price 
				WHEN PS.Uom = 'GRAMS' AND QtyUnit = 'LBS' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor 
				WHEN PS.Uom = 'GRAMS' AND QtyUnit = 'KGS' THEN Mcs * TotalSlabs * SlabWeight * Price * MFactor ELSE 0 END
			) AS PriceKgs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor ELSE 0 END) AS PriceLbs,
 			SUM(Mcs * TotalSlabs * SlabWeight * MFactor * Price / 0.454) AS UsdValue,
			SCI.Discount FROM `ScrmPoGradeData` as SPGD 
		left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
		left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID = SPGD.InvId
		left join AdminPackingStyles as PS on SPGD.PackingStyleID=PS.PCKID
		left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId		
		left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType AND SPM.FType = SPVL.FreezingType
		WHERE InvId='$PoID' ) a
		WHERE (GstPercC != 0 OR GstPercP != 0) GROUP BY TypeProduct ORDER BY GstPerc DESC";
$StmtRawCookedGstPerc = $iconn->query($SqlGstPerc);
// echo $SqlGstPerc;
// exit;

	while($RowGstPerc=$StmtRawCookedGstPerc->fetch_assoc()){
		$DiscountType=$AdminConfigDetails['DiscountType'];
		if ($RowGstPerc['Discount'] > 0) {

			if(isset($DiscountType) && $DiscountType == 0) {
				
				$DiscountAmount = ($RowGstPerc['PriceKgs'] * $RowGstPerc['Discount']) / 100;
				$InvoiceValueAfterDiscount = $RowGstPerc['PriceKgs']; 
				$InvoiceValueAfterDiscount = $RowGstPerc['PriceKgs'] - $DiscountAmount; // By Rantagiri 04/17/2023 5.44 
			}

			else{
				$DiscountAmount = $RowGstPerc['Discount'];
				$InvoiceValueAfterDiscount = $RowGstPerc['PriceKgs'];
				$InvoiceValueAfterDiscount = $RowGstPerc['PriceKgs'] - $DiscountAmount; 
			}

		}else {
			$InvoiceValueAfterDiscount = $RowGstPerc['PriceKgs'];
		}
		$IgstPerc = $RowGstPerc['GstPerc'];
		//$IgstValue = ($RowGstPerc['PriceKgs'] * $ExchangeRate * $IgstPerc) / 100;
		$IgstValue = ($InvoiceValueAfterDiscount * $ExchangeRate * $IgstPerc) / 100;
		$ArrayData['IgstPerc']=$RowGstPerc['GstPerc'];
		$ArrayData['IgstValue']= round($IgstValue); 
		$ArrayIgstData[]=$ArrayData;

	}
	return $ArrayIgstData;

}

function getExporterDetails($Id, $CountryID, $DocType)
{
	global $iconn;
	global $TblAdminExporterDetails;
	// echo $CountryID; exit;
	$TblAdminExporterDetails;
	$sql_exp = "SELECT * FROM `AdminExporterDetails` WHERE ExpID='$Id'";
	$stmt_exp = $iconn->query($sql_exp);
	$row_exp = $stmt_exp->fetch_array();
//ECHO $sql_exp; EXIT;

	//Write Method Here

	$AryAdd = getCountryBasedAdd($Id, $CountryID, $DocType);
	$Count = $AryAdd['Count'];
	//$Count=null;
	if ($Count != null) {
		$row_exp['ExpName'] = $AryAdd['ExporterName'];
		$row_exp['ExpAddress'] = $AryAdd['ExporterAddress'];
		//$row_exp['ExpUsfdaNo']=$AryAdd['ExpUsfdaNo']; 
	}

	return $row_exp;


}

function postContainerNo($PoId, $CotainerNo, $SealNo, $DocType)
{
	//echo $DocType; exit;
	global $iconn;
	if ($DocType == 'ISF') {

		$SqlUpdate = "UPDATE `ScrmCustomInvoices` SET `ContainerNo`='$CotainerNo',`LinerSealNo`='$SealNo' WHERE `PiID`='$PoId'";
	} else {
		$SqlUpdate = "UPDATE `ScrmISFDetails` SET `ContainerNo`='$CotainerNo',`SealNo`='$SealNo' WHERE `PIID`='$PoId'";
	}
	$StmtUpdate = $iconn->query($SqlUpdate);

	return 'success';

}

function getInvoiceLables($DocType, $CountryID, $BuyerID)
{
	global $iconn;
	// $SqlExporter="SELECT * FROM `ScrmCountryWiseLabelConditions` WHERE 
// Doctype='$DocType' 
// and CountryID in (0,$CountryID) order by CountryID desc limit 1" ;


		$SqlExporter = "SELECT * FROM `ScrmCountryWiseLabelConditions` WHERE 
Doctype='$DocType' 
and CountryID in (0,$CountryID) and BuyerID in (0,$BuyerID) order by CountryID desc ,BuyerID desc limit 1"; //uma

//echo $SqlExporter; 
// exit;
	$StmtExporter = $iconn->query($SqlExporter);
	$RowExporter = $StmtExporter->fetch_array();
	return $RowExporter;

}

function getInvoiceShowConditions($DocType, $CountryID, $BuyerID)
{
	global $iconn;
	$SqlShowConditions = "SELECT * FROM `ScrmCountryWiseShowConditions` WHERE CountryID in (0,$CountryID) and 
	DocType='$DocType' order by CountryID desc limit 1";
	//echo $SqlShowConditions; exit;
	$StmtShowConditions = $iconn->query($SqlShowConditions);
	$RowShowConditions = $StmtShowConditions->fetch_array();
	return $RowShowConditions;

}

function getCountryBasedAdd($Id, $CountryID, $DocType)
{
	global $iconn;
	$SqlExporter = "SELECT * FROM `ScrmCountrywiseAddresses` 
	 WHERE ExporterID='$Id'  and CountryID in (0,'$CountryID') and DocType in ('','$DocType') order by CountryID desc limit 1"; 
	// ECHO $SqlExporter; EXIT;

	$StmtExporter = $iconn->query($SqlExporter);
	$RowExporter = $StmtExporter->fetch_array();
	$Count = mysqli_num_rows($StmtExporter);
	if ($Count == 0) {
		$Count = null;
	}
	$ArrayExporter['Count'] = $Count;
	$ArrayExporter['ExporterName'] = $RowExporter['ExporterName'];
	$ArrayExporter['ExporterAddress'] = $RowExporter['ExporterAddress'];

	return $ArrayExporter;
}

function getPortDetails($PortID)
{
	global $iconn;

	$sqlPortDetails = "SELECT SCL.Name as CountryName,SPC.CountryCode as CountryCode,SPC.name as PortName 
		  FROM `ScrmPortCities` as SPC LEFT JOIN ScrmCountriesList as SCL 
		  on SPC.Country=SCL.id WHERE PortId='$PortID'";
	$stmtPortDetails = $iconn->query($sqlPortDetails);
	$RowExp = $stmtPortDetails->fetch_array();
	$FinalDest = $RowExp['PortName'] . ' ' . $RowExp['CountryCode'] . ',' . $RowExp['CountryName'];
	return strtoupper($FinalDest);

}

/*function getProcessorDetails($Id) {
global $iconn;
global $TblAdminExporterDetails;

	$TblAdminExporterDetails;
	$sql_exp="SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$Id'";
$stmt_exp=$iconn->query($sql_exp);
return $row_exp=$stmt_exp->fetch_array();


}*/
function getImporterDetails($Id)
{
	global $iconn;
	global $TblAdminExporterDetails;
	$sql_pro = "SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$Id'";
	$stmt_pro = $iconn->query($sql_pro);
	return $row_pro = $stmt_pro->fetch_array();
}
function getFormatedMonthDate($DATE)
{

	$TIME_STAMP = strtotime($DATE);
	$formated_date = date("F d,Y", $TIME_STAMP);
	if ($formated_date == 'January 01,1970') {
		$formated_date = '';
	}
	return $formated_date;
}

function getGradeBreakUpData($iconn, $GradeID, $PiID, $ConfigDetails)
{
	$ClientId = $ConfigDetails['ClientId'];
	$gradeTable = $ConfigDetails['GradeTableName'];
	$poTable = $gradeTable == 'ScrmPoGradeData' ? 'ScrmPoData' : 'ScrmPIData'; 
	$DestCountryID = $ConfigDetails['DestCountryID'];
	$CanadaLbsFactor = $ConfigDetails['CanadaLbsFactor'] ? $ConfigDetails['CanadaLbsFactor'] : 2.20462;
	if ($PiID == '') {
		$Qry = "WHERE ID in ($GradeID)";
		$PriceQry = 'Price';
	} else {
		$Qry = "WHERE InvId='$PiID'";
		$PriceQry = 'sum(Price) as Price';
	}

	$sqlExt = "sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
		sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,";

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
			sum(Mcs * TotalSlabs * SlabWeight * LbsFactor) as NetWgtLbs";

		$PriceExt = "sum(CASE WHEN PS.Uom = 'OZ' AND SPVL.HasSauce = 'Yes' THEN (Mcs * TotalSlabs * Price)
			WHEN PS.Uom = 'LBS' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price)
			WHEN PS.Uom = 'OZ' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price)
			ELSE (Mcs * TotalSlabs * SlabWeight * Mfactor * Price) END) as PriceKgs,
			sum(CASE WHEN PS.Uom = 'OZ' AND SPVL.HasSauce = 'Yes' THEN (Mcs * TotalSlabs * Price)
			WHEN PS.Uom = 'KGS' THEN (Mcs * TotalSlabs * SlabWeight * MFactor * Price)
			WHEN PS.Uom = 'GRAMS' THEN (Mcs * TotalSlabs * SlabWeight * MFactor * 0.454 * Price)
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
			break;
		default: // LBS or any other value
			$Qty = $RowTotal['NetWgtLbs'];
			$UsdValue = $RowTotal['PriceLbs'];
			break;
	}

	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['UsdValue'] = $UsdValue;

	// if ($DestCountryID == '23') {
	// 	$AryPriceData['UsdValue'] = $Qty * $RowTotal['Price'];
	// }

	$AryPriceData['NetWgtKgs'] = $RowTotal['NetWgtKgs'];
	$AryPriceData['NetWgtLbs'] = $RowTotal['NetWgtLbs'];
	$AryPriceData['GrossWeightKgs'] = $RowTotal['GrossWeightKgs'];
	$AryPriceData['GrossWeightLbs'] = $RowTotal['GrossWeightLbs'];
	$AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;
	$AryPriceData['HasSauce'] = $RowTotal['HasSauce'];

	return $AryPriceData;
}

function getInvoiceWeight($iconn, $GradeID, $PiID)
{

	if ($PiID == '') {
		$Qry = "WHERE ID='$GradeID'";
	} else {
		$Qry = "WHERE InvId='$PiID'";
	}
	//Change From ScrmCIGradeData to ScrmPoGradeData because during 
	// Customs Creation No Data Aval to Show Gross Weigth and duties

	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

	$SqlTotal = "SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();
	$InvId = $RowTotal['InvId'];

	$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'";
	$StmtQtyUnit = $iconn->query($SqlQtyUnit);
	$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
	$QtyUnit = $RowQtyUnit['QtyUnit'];


	if ($QtyUnit == 'KGS') {
		$Qty = $RowTotal['NetWgtKgs'];
		$GrossQty = $RowTotal['GrossWeightKgs']; //uma
		$UsdValue = $RowTotal['PriceKgs'];
		$UnitPrice = number_format($RowTotal['Price'], 2);
	} else {
		$Qty = $RowTotal['NetWgtLbs'] * 0.454;
		$UsdValue = $RowTotal['PriceLbs'];
		$UnitPrice = round(($UsdValue / $Qty), 6);
		$GrossQty = $RowTotal['GrossWeightLbs']; //uma
	}
	if ($QtyUnit == 'KGS') {
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	} else {
		$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $RowTotal['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $RowTotal['NetWgtLbs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	}


	$AryPriceData['Price'] = $RowTotal['Price'];
	$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
	$AryPriceData['Qty'] = $Qty;
	$AryPriceData['UsdValue'] = $UsdValue;
	$AryPriceData['UnitPrice'] = $UnitPrice;
	$AryPriceData['GrossQty'] = $GrossQty; //uma

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

function checkSauceExists($iconn, $PiID) {
    $SqlQuery = "SELECT COUNT(*) as SauceCount FROM ScrmPIVarietyList WHERE VarietyID IN 
    (Select VarietyID FROM ScrmPoGradeData WHERE InvID = $PiID) AND HasSauce = 'Yes'";
    $Stmt = $iconn->query($SqlQuery);
    $Row = $Stmt->fetch_assoc();
    return $Row['SauceCount'] > 0;
}

function getInsurancePerc($iconn, $CountryID)
{
	$sql_ins = "SELECT * FROM `ScrmInsuranceCharges` WHERE Country IN($CountryID)";
	$stmt_ins = $iconn->query($sql_ins);
	$row_ins = $stmt_ins->fetch_array();
	return $row_ins['InsurancePremiumRate'];
}

function numberTowords($number)
{

	//echo $number; exit;
	$number = number_format($number, 2, '.', '');
	list($integer, $fraction) = explode(".", (string) $number);

	$output = "";

	if ($integer { 0} == "-") {
		$output = "negative ";
		$integer = ltrim($integer, "-");
	} else if ($integer { 0} == "+") {
		$output = "positive ";
		$integer = ltrim($integer, "+");
	}

	if ($integer { 0} == "0") {
		$output .= "zero";


	} else {
		$integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
		$group = rtrim(chunk_split($integer, 3, " "), " ");

		$groups = explode(" ", $group);

		$groups2 = array();
		foreach ($groups as $g) {
			$groups2[] = convertThreeDigit($g { 0}, $g { 1}, $g { 2});

			//VAR_DUMP ($groups2); 
		}

	// EXIT; 

		for ($z = 0; $z < count($groups2); $z++) {

			if ($groups2[$z] != "") {

				$output .= $groups2[$z] . convertGroup(11 - $z) . (
					$z < 11
					&& !array_search('', array_slice($groups2, $z + 1, -1))
					&& $groups2[11] != ''
					&& $groups[11]{ 0} == '0'
					? "  "
					: " "
				);


			}
		}

		$output = rtrim($output, ", ");

	}


	/*
		if ($fraction > 0)
		{
			$output .= " Dollar and";
			for ($i = 0; $i < strlen($fraction); $i++)
			{
				$output .= " " . convertDigit($fraction{$i})." cents";
			}
		}*/


	/***uma 13-06-2023 cmmented with permission of surya***/
	//echo $fraction; exit;

	if ($fraction > 0) {
		$output .= " Dollars and ";
		if($fraction < 10){
			$output .= convertDigit($fraction) . " cents";
		} elseif ($fraction < 20) {
			$output .= convertTwoDigit($fraction) . " cents";
		} elseif ($fraction < 100) {
			$output .= convertTwoDigit(substr($fraction, 0, 1));
			if (convertDigit(substr($fraction, 1, 1)) != 'zero') {
				$output .= "  " . convertDigit(substr($fraction, 1, 1)) . " cents";
			} else {
				$output .= " cents";

			}
		}


	} else {
		//$output .= " Dollars and no cents";
		$output .= " ";
	}

	/***uma 13-06-2023 cmmented with permission of surya***/


	return strtoupper($output);
	//uma 
}

function numberTowordsRupees($number)
{
	list($integer, $fraction) = explode(".", (string) $number);

	$output = "Ruppees ";

	if ($integer { 0} == "-") {
		$output = "negative ";
		$integer = ltrim($integer, "-");
	} else if ($integer { 0} == "+") {
		$output = "positive ";
		$integer = ltrim($integer, "+");
	}

	if ($integer { 0} == "0") {
		$output .= "zero";
	} else {
		$integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
		$group = rtrim(chunk_split($integer, 3, " "), " ");
		$groups = explode(" ", $group);

		$groups2 = array();
		foreach ($groups as $g) {
			$groups2[] = convertThreeDigit($g { 0}, $g { 1}, $g { 2});
		}

		for ($z = 0; $z < count($groups2); $z++) {
			if ($groups2[$z] != "") {
				$output .= $groups2[$z] . convertGroup(11 - $z) . (
					$z < 11
					&& !array_search('', array_slice($groups2, $z + 1, -1))
					&& $groups2[11] != ''
					&& $groups[11]{ 0} == '0'
					? "  "
					: " "
				);
			}
		}

		$output = rtrim($output, ", ");

	}



	//uma

	if ($fraction > 0) {
		$output .= "   Only";
		if ($fraction < 20) {
			$output .= convertTwoDigit($fraction);
		} elseif ($fraction < 100) {
			$output .= convertTwoDigit(substr($fraction, 0, 1));
			$output .= "  " . convertDigit(substr($fraction, 1, 1));
		}
		//$rettxt .= "Dollar and no cents";
	} else {
		$output .= "  Only";
	}




	return strtoupper($output);
	//uma
}








function convertGroup($index)
{
	switch ($index) {
		case 11:
			return " decillion";
		case 10:
			return " nonillion";
		case 9:
			return " octillion";
		case 8:
			return " septillion";
		case 7:
			return " sextillion";
		case 6:
			return " quintrillion";
		case 5:
			return " quadrillion";
		case 4:
			return " trillion";
		case 3:
			return " billion";
		case 2:
			return " million";
		case 1:
			return " thousand";
		case 0:
			return "";
	}
}


function getfilename($iconn, $ExpID, $PoID)
{
	$ExporterData = getExporterDetails($ExpID);
	$InvShortCode = $ExporterData['InvShortCode'];

	$CustomInvoiceSql = "SELECT *,PI.ExporterID as ExporterID ,CustomsInvNo FROM ScrmPIData  as PI
left join ScrmCustomInvoices as SC on SC.PiID=PI.PoID WHERE PiID='$PoID'";
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	$ProcessorID = $CustomInvoiceRow['ProcessorID'];
	$CustomsInvNo = $CustomInvoiceRow['CustomsInvNo'];
	$ProcessorData = getExporterDetails($ProcessorID);
	$ProcessorInvShortCode = $ProcessorData['InvShortCode'];

	$CurrentMonth = date('n', strtotime($CustomInvoiceRow['CInvDate']));
	if ($CustomInvoiceRow['ExporterID'] == 5) {
		if ($CurrentMonth <= 3) {
			$NxtYear = date("y", strtotime($CustomInvoiceRow['CInvDate']));
			$CurrentYear = date("y", strtotime($CustomInvoiceRow['CInvDate'])) - 1;
		} else {
			$CurrentYear = date("y", strtotime($CustomInvoiceRow['CInvDate']));
			$NxtYear = date("y", strtotime($CustomInvoiceRow['CInvDate'])) + 1;

		}
	} else {

		if ($CurrentMonth <= 3) {
			$NxtYear = date('y', strtotime($CustomInvoiceRow['CInvDate']));
			$CurrentYear = date('Y', strtotime($CustomInvoiceRow['CInvDate'])) - 1;
		} else {
			$CurrentYear = date('Y', strtotime($CustomInvoiceRow['CInvDate']));
			$NxtYear = date('y', strtotime($CustomInvoiceRow['CInvDate'])) + 1;

		}
	}

	$AryFilename['InvShortCode'] = $InvShortCode;
	$AryFilename['CustomsInvNo'] = $CustomsInvNo;
	$AryFilename['ProcessorInvShortCode'] = $ProcessorInvShortCode;
	$AryFilename['CInvNo'] = $CustomInvoiceRow['CInvNo'];
	$AryFilename['FinancialYear'] = $CurrentYear . '-' . $NxtYear;

	//return $InvShortCode.$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear; 
	return $AryFilename;


}


function convertThreeDigit($digit1, $digit2, $digit3)
{
	$buffer = "";
	$And = 'And ';

	if ($digit2 != "0" && $digit3 != "0") {
		$And = '';
	}
	if ($digit1 == "0" && $digit2 == "0") {
		$And = '';
	}

	if ($digit1 == "0" && $digit2 == "0" && $digit3 == "0") {
		return "";
	}

	if ($digit1 != "0") {
		$buffer .= convertDigit($digit1) . " hundred";
		if ($digit2 != "0" || $digit3 != "0") {
			$buffer .= "  ";
		}
	}

	if ($digit2 != "0") {
		// if($digit2 != "0"&& $digit3 == "0" ){
			//uma//
		if( $buffer=="" ){
			$buffer .= convertTwoDigit($digit2, $digit3); 
			}
			else{
			$buffer .= $And . convertTwoDigit($digit2, $digit3);
			// $buffer .= "And ".convertTwoDigit($digit2, $digit3); 
				}
				//uma//
		// $buffer .= $And . convertTwoDigit($digit2, $digit3);


	} else if ($digit3 != "0") {


		// $buffer .= $And . convertDigit($digit3);
		$buffer .= convertDigit($digit3);
	}

	return $buffer;
}

function convertTwoDigit($digit1, $digit2)
{
	if ($digit2 == "0") {
		switch ($digit1) {
			case "1":
				return "ten";
			case "2":
				return "twenty";
			case "3":
				return "thirty";
			case "4":
				return "forty";
			case "5":
				return "fifty";
			case "6":
				return "sixty";
			case "7":
				return "seventy";
			case "8":
				return "eighty";
			case "9":
				return "ninety";
		}
	} else if ($digit1 == "1") {
		switch ($digit2) {
			case "1":
				return "eleven";
			case "2":
				return "twelve";
			case "3":
				return "thirteen";
			case "4":
				return "fourteen";
			case "5":
				return "fifteen";
			case "6":
				return "sixteen";
			case "7":
				return "seventeen";
			case "8":
				return "eighteen";
			case "9":
				return "nineteen";
		}
	} else {
		$temp = convertDigit($digit2);
		switch ($digit1) {
			case "2":
				return "twenty $temp";
			case "3":
				return "thirty $temp";
			case "4":
				return "forty $temp";
			case "5":
				return "fifty $temp";
			case "6":
				return "sixty $temp";
			case "7":
				return "seventy $temp";
			case "8":
				return "eighty $temp";
			case "9":
				return "ninety $temp";
		}
	}
}

function convertDigit($digit)
{
	switch ($digit) {
		case "0":
			return "zero";
		case "1":
			return "one";
		case "2":
			return "two";
		case "3":
			return "three";
		case "4":
			return "four";
		case "5":
			return "five";
		case "6":
			return "six";
		case "7":
			return "seven";
		case "8":
			return "eight";
		case "9":
			return "nine";
	}
}
/********decimal */

//uma//////


	// Convert Rupees to words
	function convert_rupees_to_words($num) {
		// global $rupee_words, $rupee_tens;

	list($integer, $fraction) = explode(".", (string) round($num, 2));

	$rupee_words = array(
		"zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten",
		"eleven", "twelve", "thirteen", "fourteen", "fifteen", "sixteen", "seventeen", "eighteen", "nineteen"
	);

	// Array of tens in Rupees
	$rupee_tens = array(
		"", "", "twenty", "thirty", "forty", "fifty", "sixty", "seventy", "eighty", "ninety"
	);

		$words = '';
		if ($num >= 10000000) {
			$words .= convert_rupees_to_words(floor($num / 10000000)) . " crore ";
			$num %= 10000000;
		}
		if ($num >= 100000) {
			$words .= convert_rupees_to_words(floor($num / 100000)) . " lakh ";
			$num %= 100000;
		}
		if ($num >= 1000) {
			$words .= convert_rupees_to_words(floor($num / 1000)) . " thousand ";
			$num %= 1000;
		}
		if ($num >= 100) {
			$words .= $rupee_words[floor($num / 100)] . " hundred ";
			$num %= 100;
		}
		if ($num >= 20) {
			$words .= $rupee_tens[floor($num / 10)] . " ";
			$num %= 10;
		}
		if ($num > 0) {
			$words .= $rupee_words[$num];
		}

		if ($fraction > 0) {
			$words .= " and ";
			if ($fraction < 10) {
				$words .= convertDigit($fraction) . " PAISE";
			} elseif ($fraction < 100) {
				$words .= convertTwoDigit(substr($fraction, 0, 1), substr($fraction, 1, 2));
				// if (convertDigit(substr($fraction, 1, 1)) != 'zero') {
				// 	$words .= "  " . convertDigit(substr($fraction, 1, 1)) . " cents";
				// }
			}
			$words .= ' PAISE';
		}
		return strtoupper($words);
	}


// }
//uma//////

//function postUserEditLog($PostedUserID,$PageUrl,$ReferenceID,$Remarks){
function postUserEditLog($ArgumentsArray)
{

	global $iconn;
	$PostedUserID = $ArgumentsArray['PostedUserId'];
	$PageUrl = $ArgumentsArray['PageUrl'];
	$ReferenceID = $ArgumentsArray['ReferenceID'];
	$Remarks = $ArgumentsArray['Remarks'];

	$timezone = new DateTimeZone('Asia/Kolkata');
	$datetime = new DateTime('now', $timezone);
	$PostedDateTime = $datetime->format('Y-m-d H:i:s');


	$SqlInsert = "INSERT INTO `UserEditLog`(`PostedUserID`, `PostedDateTime`, `PageUrl`,`ReferenceID`, `Remarks`) 
	VALUES ('$PostedUserID','$PostedDateTime','$PageUrl','$ReferenceID','$Remarks')";


	$StmtInsert = $iconn->query($SqlInsert);
	if ($StmtInsert) {
		return 'success';
	}
}



function getExporterTaxRates($iconn, $ExporterID) {
	$SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1"; 
	$StmtExporterTaxRates = $iconn->query($SqlExporterTaxRates);
	$RowExporterTaxRates = $StmtExporterTaxRates->fetch_assoc();

	return $RowExporterTaxRates;
}

?>