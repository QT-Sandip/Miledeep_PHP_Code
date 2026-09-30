<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include_once ROOT . 'config/api-call.php';

$TYPE = $_POST['TYPE'];
$D_ID = $_POST['D_ID'];
$ShippedOutDate = $_POST['ShippedOutDate'];
$InvId = $_POST['InvId'];
$Remarks = $_POST['Remarks'];
$Status = $_POST['Status'];
$LoginId = $_SESSION["UserID"];
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$EnableCustomInvoicePoData = $ArrayClientConfigData['enableCustomInvoicePoData'];


if ($TYPE == 'App_del') {
	$oldApplicant = $iconn->query("SELECT * FROM `$ScrmApplicants` WHERE ID='$D_ID'")->fetch_assoc();
	$SQL_DEL = "DELETE FROM `$ScrmApplicants` WHERE ID='$D_ID'";
	$stmt = $iconn->query($SQL_DEL);
	if ($stmt) {
		legacyAuditLogDeleteById($ScrmApplicants, 'ID', $D_ID, $oldApplicant, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'Applicant deleted',
		], 'Exports');
	}
} else if ($TYPE == 'BREAKUP_DELETE') {

	$BREAKUP_ID = $_POST['D_ID'];
	$INVOICE_ID = $_POST['INVOICE_ID'];

	$oldPoGrade = $iconn->query("SELECT * FROM `ScrmPoGradeData` WHERE `ID`='$BREAKUP_ID'")->fetch_assoc();
	$sql = "DELETE  FROM `ScrmPoGradeData` WHERE `ID`='$BREAKUP_ID'";
	$stmt = $iconn->query($sql);
	if ($stmt) {
		legacyAuditLogDeleteById('ScrmPoGradeData', 'ID', $BREAKUP_ID, $oldPoGrade, $_POST, [
			'ReferenceNo' => $INVOICE_ID,
			'Remarks' => 'PO grade breakup deleted',
		], 'Exports');
	}

	$SqlCount = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$INVOICE_ID'";

	$StmtCount = $iconn->query($SqlCount);

	$RowCustomInvoice = $StmtCount->fetch_array();

	$CInvDate = $RowCustomInvoice['CInvDate'];

	if ($EnableCustomInvoicePoData == 1 && empty($CInvDate)) {
		$oldPiGrade = $iconn->query("SELECT * FROM `ScrmPIGradeData` WHERE `PoGradeId`='$BREAKUP_ID' AND InvId='$INVOICE_ID'")->fetch_assoc();
		$sql = "DELETE  FROM `ScrmPIGradeData` WHERE `PoGradeId`='$BREAKUP_ID' AND InvId='$INVOICE_ID'";
		$stmt = $iconn->query($sql);
		if ($stmt) {
			legacyAuditLogDeleteById('ScrmPIGradeData', 'PoGradeId', $BREAKUP_ID, $oldPiGrade, $_POST, [
				'ReferenceNo' => $INVOICE_ID,
				'Remarks' => 'PI grade data deleted with breakup',
			], 'Exports');
		}
	}

} else if ($TYPE == 'UPDATECOMMERCIALINVOICE') {


	$PostedUserId = $_SESSION['UserID'];
	//$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));
	$PageUrl = 'AddCommercialInvoice';

	$PIID = $_POST['InvId'];
	$Count = $_POST['CICount'];
	$LcNo = $_POST['LcNo'];
	$LcDate = $_POST['LcDate'];
	$ShowBank = $_POST['ShowBank'];
	$ShowBLNo = $_POST['ShowBLNo'];
	$ShowBLDate = $_POST['ShowBLDate'];
	$ShowShippingDetails = $_POST['ShowShippingDetails'];
	$IssuedBy = $_POST['IssuedBy'];
	$Discount = $_POST['Discount'];
	$Freight = $_POST['Freight'];
	$ExchngRte = $_POST['ExchngRte'];
	$ComDescriptionSpl = $_POST['ComDescriptionSpl'];
	$ComRemarks = $_POST['ComRemarks'];
	$ComShippingTerms = $_POST['ComShippingTerms'];
	$ComPaymentTerms = $_POST['ComPaymentTerms'];
	$Status = $_POST['Status'];
	$AryBreakUp = $_POST['AryBreakUp'];
	$ComShippingTermExt = $_POST['ComShippingTermExt'];
	$DataLogger = $_POST['DataLogger'];
	$DataLoggerPositions = $_POST['DataLoggerPositions'];
	$BankID = $_POST['BankID'];
	$SqlBreakupCount = "SELECT * FROM `ScrmCIGradeData` WHERE InvId='$PIID'";
	$StmtBreakupCount = $iconn->query($SqlBreakupCount);
	$BreakupCount = mysqli_num_rows($StmtBreakupCount);
	if ($BreakupCount == 0) {
		postComBreakUp($PIID, $AryBreakUp);
	} else {

		$AryBreakUp = json_decode($AryBreakUp, true);
		foreach ($AryBreakUp as $Item) {

			$PackingStyleID = $Item['PackingStyleID'];
			$Grade = $Item['Grade'];
			$Mcs = $Item['Mcs'];
						$Codes = $Item['Codes'];
			$CIDescription = $Item['CIDescription'];
			$FreezingDate = $Item['FreezingDate'];
			$ConsumptionDate = $Item['ConsumptionDate'];
			$ProductionDate = $Item['ProductionDate'];
			$BestBeforeDate = $Item['BestBeforeDate'];
			$Specfications = $Item['Specfications'];
			$CIGradeID = $Item['CIGradeID'];

			$oldCiGrade = $iconn->query("SELECT * FROM `ScrmCIGradeData` WHERE `ID`='$CIGradeID'")->fetch_assoc();
			$sql = "UPDATE `ScrmCIGradeData` SET
			`CIDescription`='$CIDescription',
						`Codes`='$Codes',
			`FreezingDate`='$FreezingDate',
			`ConsumptionDate`='$ConsumptionDate',
			`ProductionDate`='$ProductionDate',
			`BestBeforeDate`='$BestBeforeDate',
			`Specfications`='$Specfications'
			WHERE  `ID`='$CIGradeID'";
			$stmt = $iconn->query($sql);
			if ($stmt) {
				legacyAuditLogUpdateById('ScrmCIGradeData', 'ID', $CIGradeID, $oldCiGrade, $_POST, [
					'ReferenceNo' => $PIID,
					'Remarks' => 'CI grade data updated',
				], 'Exports');
			}
		}
	}


	/* $SqlDescrptnUpdt = "UPDATE `ScrmISFDetails` SET
	`ComDescriptionSpl`='$CIDescriptionspl'
	WHERE  `PIID`='$PIID' ";
	$stmtDescrptnUpdt = $iconn->query($SqlDescrptnUpdt); */

	if ($Count == 0) {
		$SqlCIInv = "SELECT * FROM `ScrmCommercialData` where PoID='$PIID'";
		$StmtCIInv = $iconn->query($SqlCIInv);
		$Count = mysqli_num_rows($StmtCIInv);
	}
	$isNewCommercialData = ($Count == 0);
	$oldScrmCommercialData = null;
	if ($Count == 0) {
		$SqlComrclDt = "INSERT INTO `ScrmCommercialData`( `PoID`, `LcNo`,
		`LcDate`, `IssuedBy`, `ShowBank`,`ShowBLNo`,`ShowBLDate`, `Discount`, `Freight`, `ExchngRte`,
		`ComDescriptionSpl`, `Status`,`ComRemarks`,`ComPaymentTerms`,`ComShippingTerms`,
		`ComShippingTermExt`, `BankID`,`ShowShippingDetails`) VALUES(
		'$PIID', '$LcNo', '$LcDate','$IssuedBy','$ShowBank','$ShowBLNo','$ShowBLDate','$Discount','$Freight',
		'$ExchngRte', '$ComDescriptionSpl','$Status','$ComRemarks','$ComPaymentTerms','$ComShippingTerms',
		'$ComShippingTermExt','$BankID','$ShowShippingDetails')";
	} else {

		$oldScrmCommercialData = $iconn->query("SELECT * FROM `ScrmCommercialData` WHERE `PoID`='$PIID'")->fetch_assoc();
		$SqlComrclDt = "
		UPDATE `ScrmCommercialData` SET
		`LcNo`='$LcNo' , `LcDate`='$LcDate' , `IssuedBy`='$IssuedBy' , `ShowBank`='$ShowBank',`ShowBLNo`='$ShowBLNo',`ShowBLDate`='$ShowBLDate',`ShowShippingDetails`='$ShowShippingDetails',
		`Discount`='$Discount',`Freight`='$Freight',`ExchngRte`='$ExchngRte',
		`ComDescriptionSpl`='$ComDescriptionSpl',
		`ComShippingTermExt`='$ComShippingTermExt',
		`Status`='$Status',`ComRemarks`='$ComRemarks',`ComPaymentTerms`='$ComPaymentTerms',
		`ComShippingTerms`='$ComShippingTerms',`BankID`='$BankID'
		WHERE  `PoID`='$PIID' ";

		$ArgumentsArray[PostedUserId] = $PostedUserId;
		$ArgumentsArray[PageUrl] = $PageUrl;
		$ArgumentsArray[Remarks] = $Remarks;
		$ArgumentsArray[ReferenceID] = $PIID;
		postUserEditLog($ArgumentsArray);
	}

	//echo $SqlComrclDt; exit;

	$stmtComrclDt = $iconn->query($SqlComrclDt);
	if ($stmtComrclDt && $isNewCommercialData) {
		legacyAuditLogInsertById('ScrmCommercialData', 'PoID', $PIID, $_POST, [
			'ReferenceNo' => $PIID,
			'Remarks' => 'Commercial data inserted',
		], 'Exports');
	} else if ($stmtComrclDt && !$isNewCommercialData) {
		legacyAuditLogUpdateById('ScrmCommercialData', 'PoID', $PIID, $oldScrmCommercialData, $_POST, [
			'ReferenceNo' => $PIID,
			'Remarks' => 'Commercial data updated',
		], 'Exports');
	}


	if ($stmtComrclDt) {

		$oldIsf = $iconn->query("SELECT * FROM `ScrmISFDetails` WHERE `PIID`='$PIID'")->fetch_assoc();
		$SqlIsf = "UPDATE `ScrmISFDetails` SET `DataLogger`='$DataLogger',`DataLoggerPositions`='$DataLoggerPositions' WHERE `PIID`='$PIID'";
		$StmtIsf = $iconn->query($SqlIsf);
		if ($StmtIsf) {
			legacyAuditLogUpdateById('ScrmISFDetails', 'PIID', $PIID, $oldIsf, $_POST, [
				'ReferenceNo' => $PIID,
				'Remarks' => 'ISF data logger details updated',
			], 'Exports');
		}

		$row['sts'] = 'success';
		$row['msg'] = ' Success';
		echo json_encode($row);
	} else {
		$row['sts'] = 'Failed';
		$row['msg'] = 'Failed';
		echo json_encode($row);
	}
} else if ($TYPE == 'PI_DELETE') {

	$D_ID = $_POST['D_ID'];

	$oldPiData = $iconn->query("SELECT * FROM `ScrmPIData` WHERE `PoID`='$D_ID'")->fetch_assoc();
	$sql = "UPDATE `ScrmPIData` SET `Status`='7' WHERE `PoID`='$D_ID'";

	$stmt = $iconn->query($sql);

	if ($stmt) {
		legacyAuditLogUpdateById('ScrmPIData', 'PoID', $D_ID, $oldPiData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'PI status set to deleted (7)',
		], 'Exports');

		//$sqlCI="DELETE FROM `ScrmCustomInvoices` WHERE PiID='$D_ID'";
		$oldCustomInv = $iconn->query("SELECT * FROM `ScrmCustomInvoices` WHERE `PiID`='$D_ID'")->fetch_assoc();
		$sqlCI = "UPDATE `ScrmCustomInvoices` SET `Status`='0' WHERE `PiID`='$D_ID'";

		$stmtCI = $iconn->query($sqlCI);
		if ($stmtCI) {
			legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $D_ID, $oldCustomInv, $_POST, [
				'ReferenceNo' => $D_ID,
				'Remarks' => 'Custom invoice status reset on PI delete',
			], 'Exports');
		}

		$oldPoData = $iconn->query("SELECT * FROM `ScrmPoData` WHERE `PoID`='$D_ID'")->fetch_assoc();
		$sqlPO = "UPDATE `ScrmPoData` SET `Status`='7' WHERE `PoID`='$D_ID'";

		$stmtPO = $iconn->query($sqlPO);
		if ($stmtPO) {
			legacyAuditLogUpdateById('ScrmPoData', 'PoID', $D_ID, $oldPoData, $_POST, [
				'ReferenceNo' => $D_ID,
				'Remarks' => 'PO status set to deleted (7) on PI delete',
			], 'Exports');
		}
	}
} else if ($TYPE == 'McsUpdate') {

	$D_ID = $_POST['D_ID'];
	$Mcs = $_POST['Mcs'];

	$oldCiGrade = $iconn->query("SELECT * FROM `ScrmCIGradeData` WHERE ID='$D_ID'")->fetch_assoc();
	$sql = "UPDATE `ScrmCIGradeData` SET `Mcs`='$Mcs' WHERE ID='$D_ID'";

	$stmt = $iconn->query($sql);
	if ($stmt) {
		legacyAuditLogUpdateById('ScrmCIGradeData', 'ID', $D_ID, $oldCiGrade, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'CI grade MCS updated',
		], 'Exports');
	}
} else if ($TYPE == 'PO_DELETE') {

	$D_ID = $_POST['D_ID'];

	$oldPoData = $iconn->query("SELECT * FROM `ScrmPoData` WHERE PoID='$D_ID'")->fetch_assoc();
	$sql = "DELETE FROM `ScrmPoData` WHERE PoID='$D_ID'";

	$stmt = $iconn->query($sql);
	if ($stmt) {
		legacyAuditLogDeleteById('ScrmPoData', 'PoID', $D_ID, $oldPoData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'PO deleted',
		], 'Exports');
	}
} else if ($TYPE == 'CustomDelete') {

	$PiID = trim($_POST["PiID"]);

	$bodyContent = array(
		'PiID' => $PiID,
		'CustomsInvoiceStatus' => "Customs Deleted"
	);

	$api = API_CUSTOM_INVOICES;
	$resCustom = CallAPI($api, "POST", "", $bodyContent);

	$oldCiGrade = $iconn->query("SELECT * FROM `ScrmCIGradeData` WHERE InvId='$PiID'")->fetch_assoc();
	$sqlCI = "DELETE FROM `ScrmCIGradeData` WHERE InvId='$PiID'";
	$stmtCI = $iconn->query($sqlCI);
	if ($stmtCI) {
		legacyAuditLogDeleteById('ScrmCIGradeData', 'InvId', $PiID, $oldCiGrade, $_POST, [
			'ReferenceNo' => $PiID,
			'Remarks' => 'CI grade data deleted on customs delete',
		], 'Exports');
	}

	postUpdatePoStatus($iconn, $PiID);
} else if ($TYPE == 'PO_CANCEL') {

	$D_ID = $_POST['D_ID'];

	$oldPoData = $iconn->query("SELECT * FROM `ScrmPoData` WHERE PoID='$D_ID'")->fetch_assoc();
	$sql = "UPDATE `ScrmPoData` SET `Status`='2' WHERE PoID='$D_ID'";

	$stmt = $iconn->query($sql);
	if ($stmt) {
		legacyAuditLogUpdateById('ScrmPoData', 'PoID', $D_ID, $oldPoData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'PO cancelled (Status=2)',
		], 'Exports');
	}
} else if ($TYPE == 'PI_CANCEL') {

	$D_ID = $_POST['D_ID'];

	$oldPiData = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$D_ID'")->fetch_assoc();
	$sql = "UPDATE `ScrmPIData` SET `Status`='2' WHERE PoID='$D_ID'";

	$stmt = $iconn->query($sql);
	if ($stmt) {
		legacyAuditLogUpdateById('ScrmPIData', 'PoID', $D_ID, $oldPiData, $_POST, [
			'ReferenceNo' => $D_ID,
			'Remarks' => 'PI cancelled (Status=2)',
		], 'Exports');
	}
} else if ($TYPE == 'SHIPPED_PO') {

	$D_ID = $_POST['D_ID'];

	$oldPoData = $iconn->query("SELECT * FROM `ScrmPoData` WHERE PoID='$InvId'")->fetch_assoc();
	$sql = "UPDATE `ScrmPoData` SET `ShippedOutDate`='$ShippedOutDate',`Status`='3' WHERE PoID='$InvId'";

	$stmt = $iconn->query($sql);

	if ($stmt) {
		legacyAuditLogUpdateById('ScrmPoData', 'PoID', $InvId, $oldPoData, $_POST, [
			'ReferenceNo' => $InvId,
			'Remarks' => 'PO shipped (Status=3)',
		], 'Exports');

		$oldPiData = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$InvId'")->fetch_assoc();
		$SqlPI = "UPDATE `ScrmPIData` SET `ShippedOutDate`='$ShippedOutDate',`Status`='3' WHERE PoID='$InvId'";

		$StmtPI = $iconn->query($SqlPI);
		if ($StmtPI) {
			legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiData, $_POST, [
				'ReferenceNo' => $InvId,
				'Remarks' => 'PI shipped (Status=3)',
			], 'Exports');
		}

		$row['sts'] = 'success';
		$row['msg'] = 'PO Shipped  Sucessfully';
		echo json_encode($row);
	} else {
		$row['sts'] = 'Failed';
		$row['msg'] = 'Failed To Shipped PO';
		echo json_encode($row);
	}
} else if ($TYPE == 'BulkApproval') {
	$Status = $_POST['Status'];
	$SelectedbulkInvId = $_POST['SelectedbulkInvId'];
	$InvId = implode(',', $SelectedbulkInvId);



	/* $SqlPI="UPDATE `ScrmPIData` SET `Status`='$Status',`ApprovedBy`='$LoginId'
											WHERE PoID in ($InvId) and (PoNo='' or PoDate='')"; */

	$SqlPI = "UPDATE `ScrmPIData` SET `Status`='$Status',`ApprovedBy`='$LoginId'
		 WHERE PoID in ($InvId)";

	$StmtPI = $iconn->query($SqlPI);

	/* $SqlPIList="SELECT * FROM `ScrmPIData` WHERE PoID in ($InvId) and PoNo!='' and PoDate!=''";
									   $StmtPIList=$iconn->query($SqlPIList);

									   while($RowPIList=$StmtPIList->fetch_array()){
										   $Ids[]=$RowPIList['PoID'];
											$PiIds=implode(',',$Ids);
									   }
									   $PiCount=count($PiIds);

									   if($PiCount!=0){
									   postUpdatePoReceivedStatus($iconn,$PiIds,$LoginId);
									   }*/
	if ($Status == 5) {
		PostPOData($InvId);
	}

	if ($StmtPI) {
		foreach ($SelectedbulkInvId as $bulkId) {
			$oldBulkPi = $iconn->query("SELECT * FROM `ScrmPIData` WHERE `PoID`='$bulkId'")->fetch_assoc();
			legacyAuditLogUpdateById('ScrmPIData', 'PoID', $bulkId, $oldBulkPi, $_POST, [
				'ReferenceNo' => $bulkId,
				'Remarks' => 'Bulk approval status=' . $Status,
			], 'Exports');
		}

		$row['sts'] = 'success';
		$row['msg'] = 'Status Updated Sucessfully';

		echo json_encode($row);
	} else {
		$row['sts'] = 'Failed';
		$row['msg'] = 'Status Updation Failed';
		echo json_encode($row);
	}
} else if ($TYPE == 'UPDATEETA') {
	$PostedUserId = $_SESSION['UserID'];
	//$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));
	$PageUrl = 'UpdateFBD';

	$PiID = $_POST['PiID'];
	$EstimatedETA = $_POST['EstimatedETA'];
	$ETARemarks = $_POST['ETARemarks'];
	//$EmptyContainerReturnDate=$_POST['EmptyContainerReturnDate'];
	// $ContainerGateOutDate=$_POST['ContainerGateOutDate'];
	//$EntryNumber=$_POST['EntryNumber'];
	// $IsFDA=$_POST['IsFDA'];
	$IsFDA = $_POST['IsFDAChecked'];

	$sql = "UPDATE `ScrmISFDetails` SET
	  `EstimatedETA`='$EstimatedETA',
	  `ETARemarks`='$ETARemarks',
	  `IsFDA`='$IsFDA'   WHERE  `PiID`='$PiID'";
	$stmt = $iconn->query($sql);

	if ($stmt) {
		$ArgumentsArray[PostedUserId] = $PostedUserId;
		$ArgumentsArray[PageUrl] = $PageUrl;
		$ArgumentsArray[Remarks] = $Remarks;
		$ArgumentsArray[ReferenceID] = $PiID;
		postUserEditLog($ArgumentsArray);
	}

	/*$SqlDeliverDateUpdate="UPDATE  `ScrmShipmentDetailsBreif`
															SET DeliveredDate='$EmptyContainerReturnDate' WHERE PiID='$PiID'";

										$StmtDeliverDateUpdate=$iconn->query($SqlDeliverDateUpdate);*/
	//UpdateFDADetails($EmptyContainerReturnDate,$ContainerGateOutDate,$PiID);



} else if ($TYPE == 'PI_STATUS') {



	$D_ID = $_POST['D_ID'];
	$PoNo = $_POST['PoNo'];
	$PoDate = $_POST['PoDate'];

	$sqlpo = " SELECT * FROM `ScrmPoData`   WHERE PoID='$InvId'";
	$stmtPO = $iconn->query($sqlpo);
	$count = mysqli_num_rows($stmtPO);


	$SqlPI = "SELECT * FROM `ScrmPIData` WHERE PoID='$InvId' ";
	$StmtPI = $iconn->query($SqlPI);
	$RowPI = $StmtPI->fetch_array();
	$PoRefId = $RowPI['PoRefId'];
	$InvNo = $RowPI['InvNo'];
	$InvDate = $RowPI['InvDate'];
	$ExporterID = $RowPI['ExporterID'];
	$ProcessorID = $RowPI['ProcessorID'];
	$LotNo = $RowPI['LotNo'];
	$SciName = $RowPI['SciName'];
	$ConsigneeID = $RowPI['ConsigneeID'];
	$BuyerID = $RowPI['BuyerID'];
	/*$PoNo=$RowPI['PoNo'];
										$PoDate=$RowPI['PoDate'];*/
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

	$InvNumFull = getFullPiInvoiceNumbertoSave($ExporterID, $InvDate, $InvNo);

	$sqlShipment = " SELECT * FROM `ScrmShipmentDetailsBreif`   WHERE PiID='$InvId'";
	$stmtShipment = $iconn->query($sqlShipment);
	$Shipmentcount = mysqli_num_rows($stmtShipment);

	if ($Status == '1') {
		$oldPiStatus1 = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$InvId'")->fetch_assoc();
		$sql = "UPDATE `ScrmPIData` SET `Remarks`='$Remarks',`Status`='$Status' WHERE PoID='$InvId'";

		$stmt = $iconn->query($sql);
		if ($stmt) {
			legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiStatus1, $_POST, [
				'ReferenceNo' => $InvId,
				'Remarks' => 'PI status set to 1 (draft)',
			], 'Exports');
		}
	} else if ($PoNo != '' && $PoDate != '') {




		$oldPiPoDetails = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$InvId'")->fetch_assoc();
		$sql = "UPDATE `ScrmPIData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Remarks`='$Remarks',`Status`='5',`ApprovedBy`='$LoginId' WHERE PoID='$InvId'";

		$stmt1 = $iconn->query($sql);
		if ($stmt1) {
			legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiPoDetails, $_POST, [
				'ReferenceNo' => $InvId,
				'Remarks' => 'PI approved with PO details (Status=5)',
			], 'Exports');
		}
		if ($Shipmentcount == 0) {
			$SqlShipmentInsert = "INSERT INTO `ScrmShipmentDetailsBreif`(`PiID`,`AvailableDate`) VALUES('$InvId','$PoDate')";
			$StmtShipmentInsert = $iconn->query($SqlShipmentInsert);
			if ($StmtShipmentInsert) {
				legacyAuditLogInsertById('ScrmShipmentDetailsBreif', 'PiID', $InvId, $_POST, [
					'ReferenceNo' => $InvId,
					'Remarks' => 'Shipment details created on PI approval',
				], 'Exports');
			}
		} else {
			$oldShipment = $iconn->query("SELECT * FROM `ScrmShipmentDetailsBreif` WHERE `PiID`='$InvId'")->fetch_assoc();
			$SqlShipmentInsert = "UPDATE `ScrmShipmentDetailsBreif` SET `AvailableDate`='$PoDate' WHERE `PiID`='$InvId'";
			$StmtShipmentInsert = $iconn->query($SqlShipmentInsert);
			if ($StmtShipmentInsert) {
				legacyAuditLogUpdateById('ScrmShipmentDetailsBreif', 'PiID', $InvId, $oldShipment, $_POST, [
					'ReferenceNo' => $InvId,
					'Remarks' => 'Shipment available date updated on PI approval',
				], 'Exports');
			}
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
			if ($stmt2) {
				legacyAuditLogInsertById('ScrmPoData', 'PoID', $InvId, $_POST, [
					'ReferenceNo' => $InvId,
					'Remarks' => 'PO created on PI approval',
				], 'Exports');
			}
		} else {
			$oldPoApproval = $iconn->query("SELECT * FROM `ScrmPoData` WHERE PoID='$InvId'")->fetch_assoc();
			$SqlInsert = "UPDATE `ScrmPoData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Remarks`='$Remarks',`Status`='5',
	`ApprovedBy`='$LoginId' WHERE PoID='$InvId'";
			$stmt2 = $iconn->query($SqlInsert);
			if ($stmt2) {
				legacyAuditLogUpdateById('ScrmPoData', 'PoID', $InvId, $oldPoApproval, $_POST, [
					'ReferenceNo' => $InvId,
					'Remarks' => 'PO updated on PI approval (Status=5)',
				], 'Exports');
			}
		}
		//echo  $SqlInsert;


		if ($stmt2) {
			$row['sts'] = 'success';
			$row['msg'] = 'PI Status Updated  Sucessfully';
			echo json_encode($row);
		} else {
			$row['sts'] = 'Failed';
			$row['msg'] = 'Update failed please enter po number and po date';
			echo json_encode($row);
		}
	} else if ($Status != '' && $Status != '1') {
		$oldPiStatusN = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$InvId'")->fetch_assoc();
		$sql = "UPDATE `ScrmPIData` SET `Remarks`='$Remarks',`Status`='$Status' WHERE PoID='$InvId'";

		$stmt = $iconn->query($sql);
		if ($stmt) {
			legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiStatusN, $_POST, [
				'ReferenceNo' => $InvId,
				'Remarks' => 'PI status set to ' . $Status,
			], 'Exports');
		}
	} else if ($Status == '') {
		$oldPiRemarks = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$InvId'")->fetch_assoc();
		$sql = "UPDATE `ScrmPIData` SET `Remarks`='$Remarks' WHERE PoID='$InvId'";

		$stmt = $iconn->query($sql);
		if ($stmt) {
			legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiRemarks, $_POST, [
				'ReferenceNo' => $InvId,
				'Remarks' => 'PI remarks updated',
			], 'Exports');
		}
	} else if ($Status == '1') {
		$oldPiStatus1b = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID='$InvId'")->fetch_assoc();
		$sql = "UPDATE `ScrmPIData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Remarks`='$Remarks',`Status`='$Status',`ApprovedBy`='$LoginId' WHERE PoID='$InvId'";

		$stmt = $iconn->query($sql);
		if ($stmt) {
			legacyAuditLogUpdateById('ScrmPIData', 'PoID', $InvId, $oldPiStatus1b, $_POST, [
				'ReferenceNo' => $InvId,
				'Remarks' => 'PI status set to 1 with PO details',
			], 'Exports');
		}
	}


	if ($stmt) {
		if ($count == 0) {
		} else {

			$oldPoSync = $iconn->query("SELECT * FROM `ScrmPoData` WHERE PoID='$InvId'")->fetch_assoc();
			$SqlInsert = "UPDATE `ScrmPoData` SET `Remarks`='$Remarks',`Status`='$Status',`ApprovedBy`='$LoginId' WHERE PoID='$InvId'";
			$stmt2 = $iconn->query($SqlInsert);
			if ($stmt2) {
				legacyAuditLogUpdateById('ScrmPoData', 'PoID', $InvId, $oldPoSync, $_POST, [
					'ReferenceNo' => $InvId,
					'Remarks' => 'PO status synced from PI status update',
				], 'Exports');
			}
		}
		$row['sts'] = 'success';
		$row['msg'] = 'PI Status Updated  Sucessfully';
		echo json_encode($row);
	}
}



function postUpdatePoStatus($iconn, $PIId)
{
	// $AryCInvNo = explode("-", $CInvDate);
	// $FinYear = $AryCInvNo[0];
	// $CurrentMonth = $AryCInvNo[1];

	// if ($CurrentMonth > 3) {
	// 	$FinYear = $FinYear;
	// } else {
	// 	$FinYear = $FinYear - 1;
	// }

	$oldShipment = $iconn->query("SELECT * FROM `ScrmShipmentDetailsBreif` WHERE PiID='$PIId'")->fetch_assoc();
	$SqlSdb = "UPDATE `ScrmShipmentDetailsBreif` SET `CInvNo`='',`CInvDate`='',`FinYear`='', `TransportedDate` = '', `DeliveredDate` = '' WHERE PiID='$PIId'";

	$StmtSdb = $iconn->query($SqlSdb);

	if ($StmtSdb) {
		legacyAuditLogUpdateById('ScrmShipmentDetailsBreif', 'PiID', $PIId, $oldShipment, [], [
			'ReferenceNo' => $PIId,
			'Remarks' => 'Shipment details cleared on customs delete',
		], 'Exports');

		$oldPoReset = $iconn->query("SELECT * FROM `ScrmPoData` WHERE PoID ='$PIId'")->fetch_assoc();
		$SqlPoStatus = "UPDATE `ScrmPoData` SET `Status`='5' WHERE PoID ='$PIId'";

		$StmtPoStatus = $iconn->query($SqlPoStatus);
		if ($StmtPoStatus) {
			legacyAuditLogUpdateById('ScrmPoData', 'PoID', $PIId, $oldPoReset, [], [
				'ReferenceNo' => $PIId,
				'Remarks' => 'PO status reset to 5 on customs delete',
			], 'Exports');
		}

		$oldPiReset = $iconn->query("SELECT * FROM `ScrmPIData` WHERE PoID ='$PIId'")->fetch_assoc();
		$SqlPiStatus = "UPDATE `ScrmPIData` SET `Status`='5' WHERE PoID ='$PIId'";

		$StmtPiStatus = $iconn->query($SqlPiStatus);
		if ($StmtPiStatus) {
			legacyAuditLogUpdateById('ScrmPIData', 'PoID', $PIId, $oldPiReset, [], [
				'ReferenceNo' => $PIId,
				'Remarks' => 'PI status reset to 5 on customs delete',
			], 'Exports');
		}
	}

	return 'success';
}

function UpdateFDADetails($EmptyContainerReturnDate, $ContainerGateOutDate, $PoID)
{
	global $iconn, $LoginId;
	$timezone = new DateTimeZone('Asia/Kolkata');
	$datetime = new DateTime('now', $timezone);
	$PostedDateTime = $datetime->format('Y-m-d H:i:s');
	$EmptyContainerReturnDate;
	$SqlCount = "SELECT * FROM `ScrmFdaDetails` WHERE PoID='$PoID'";
	$StmtCount = $iconn->query($SqlCount);

	$RowCount = $StmtCount->fetch_array();
	$IsEdit = $RowCount['IsEdit'] + 1;
	$Count = mysqli_num_rows($StmtCount);
	$oldFda = $iconn->query("SELECT * FROM `ScrmFdaDetails` WHERE PoID='$PoID'")->fetch_assoc();
	if ($Count == 0) {

		$SqlInsert = "INSERT INTO `ScrmFdaDetails`(`PoID`, `ContainerGateOutDate`, `EmptyContainerReturnDate`,`PostedUserID`,`PostedDateTime`,`IsEdit`) VALUES('$PoID','$ContainerGateOutDate','$EmptyContainerReturnDate','$LoginId','$PostedDateTime','0') ";
		$StmtInsert = $iconn->query($SqlInsert);
		if ($StmtInsert) {
			legacyAuditLogInsertById('ScrmFdaDetails', 'PoID', $PoID, [], [
				'ReferenceNo' => $PoID,
				'Remarks' => 'FDA details inserted',
			], 'Exports');
		}
	} else {
		$SqlInsert = " UPDATE `ScrmFdaDetails` SET `ContainerGateOutDate`='$ContainerGateOutDate',`EmptyContainerReturnDate`='$EmptyContainerReturnDate',`PostedUserID`='$LoginId',`PostedDateTime`='$PostedDateTime',`IsEdit`='$IsEdit' WHERE `PoID`='$PoID'";
		$StmtInsert = $iconn->query($SqlInsert);
		if ($StmtInsert) {
			legacyAuditLogUpdateById('ScrmFdaDetails', 'PoID', $PoID, $oldFda, [], [
				'ReferenceNo' => $PoID,
				'Remarks' => 'FDA details updated',
			], 'Exports');
		}
	}
	return 'success';
}

function PostPOData($InvId)
{

	global $iconn;


	/*$D_ID=$_POST['D_ID'];
											$PoNo=$_POST['PoNo'];
											$PoDate=$_POST['PoDate'];

									   $sqlpo=" SELECT * FROM `ScrmPoData`   WHERE PoID='$InvId'";
										   $stmtPO=$iconn->query($sqlpo);
										   $count=mysqli_num_rows($stmtPO);*/


	$SqlPI = "SELECT * FROM `ScrmPIData` WHERE PoID in ($InvId) ";
	$StmtPI = $iconn->query($SqlPI);

	while ($RowPI = $StmtPI->fetch_array()) {
		$PoID = $RowPI['PoID'];
		$PoRefId = $RowPI['PoRefId'];
		$InvNo = $RowPI['InvNo'];
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

		$InvNumFull = getFullPiInvoiceNumbertoSave($ExporterID, $InvDate, $InvNo);

		$sqlShipment = " SELECT * FROM `ScrmShipmentDetailsBreif`   WHERE PiID='$PoID'";
		$stmtShipment = $iconn->query($sqlShipment);
		$Shipmentcount = mysqli_num_rows($stmtShipment);

		if ($PoNo != '' && $PoDate != '') {



			if ($Shipmentcount == 0) {
				$SqlShipmentInsert = "INSERT INTO `ScrmShipmentDetailsBreif`(`PiID`,`AvailableDate`) VALUES('$PoID','$PoDate')";
				$StmtShipmentInsert = $iconn->query($SqlShipmentInsert);
				if ($StmtShipmentInsert) {
					legacyAuditLogInsertById('ScrmShipmentDetailsBreif', 'PiID', $PoID, [], [
						'ReferenceNo' => $PoID,
						'Remarks' => 'Shipment details created from PostPOData',
					], 'Exports');
				}
			} else {
				$oldShipmentPost = $iconn->query("SELECT * FROM `ScrmShipmentDetailsBreif` WHERE `PiID`='$PoID'")->fetch_assoc();
				$SqlShipmentInsert = "UPDATE `ScrmShipmentDetailsBreif` SET `AvailableDate`='$PoDate' WHERE `PiID`='$PoID'";
				$StmtShipmentInsert = $iconn->query($SqlShipmentInsert);
				if ($StmtShipmentInsert) {
					legacyAuditLogUpdateById('ScrmShipmentDetailsBreif', 'PiID', $PoID, $oldShipmentPost, [], [
						'ReferenceNo' => $PoID,
						'Remarks' => 'Shipment available date updated from PostPOData',
					], 'Exports');
				}
			}


			// if($count==0){
			$SqlInsert = " INSERT INTO `ScrmPoData`(`PoID`, `PoRefId`, `InvNo`,`InvNumFull`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
		  `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`, `BankID`
		  , `PortOfDischarge`, `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, `PreparedBy`,
		  `PRInstrctns`, `ShipByDate`,  `Status`,`QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`, `Remarks`,`ImporterID`, `NotifyPartyID`, `ReferenceNo`, `Agent`, `Certification`, `PortofLoading`, `ShowBank`, `UltimateConsigneeID`)
		  VALUES ('$PoID','$PoRefId','$InvNo','$InvNumFull','$InvDate','$ExporterID','$ProcessorID',
	'$LotNo','$SciName','$ConsigneeID','$BuyerID','$PoNo','$PoDate','$DestinationCountry',
	'$TypeCountry','$PoStatus','$YEAR','$BankID','$PortOfDischarge',
	'$FinalDestination','$PaymentTerms','$ShippingTerms','$DeliveryTerms','$PreparedBy' ,'$PRInstrctns',
		 '$ShipByDate','5','$QtyUnit','$UnitCurrency','$LoginId','$LoginId','$Remarks','$ImporterID','$NotifyPartyID','$ReferenceNo','$Agent','$Certification','$PortofLoading','$ShowBank','$UltimateConsigneeID')";
			$stmt2 = $iconn->query($SqlInsert);
			if ($stmt2) {
				legacyAuditLogInsertById('ScrmPoData', 'PoID', $PoID, [], [
					'ReferenceNo' => $PoID,
					'Remarks' => 'PO created from PostPOData',
				], 'Exports');
			}
			//}



			/*if($stmt2){
																													 $row['sts']='success';
																														 $row['msg']='PI Status Updated  Sucessfully';
																														 echo json_encode($row);
																													  }

																													  else{
																															 $row['sts']='Failed';
																														 $row['msg']='Update failed please enter po number and po date';
																														  echo json_encode($row);
																														   }*/
		}
	}
	return 'success';
}



function postComBreakUp($PIId, $AryBreakUp)
{
	global $iconn;

	$SqlGradeData = "SELECT * FROM `ScrmPoGradeData` where InvId='$PIId'  ORDER BY ID ASC";
	$StmtGradeData = $iconn->query($SqlGradeData);
	while ($RowGradeData = $StmtGradeData->fetch_array()) {


		$PoGradeID = $RowGradeData['ID'];
		$PoGrade = $RowGradeData['Grade'];
		$PoMcs = $RowGradeData['Mcs'];
		$PoFreezingType = $RowGradeData['FreezingType'];
		$PoPackingStyleID = $RowGradeData['PackingStyleID'];
		$PoGlazeType = $RowGradeData['GlazeType'];
		$PoVariety = $RowGradeData['Variety'];
		$PoGlazePerc = $RowGradeData['GlazePerc'];
		$PoTreatment = $RowGradeData['Treatment'];
		$PoBrand = $RowGradeData['Brand'];
		$PoPrice = $RowGradeData['Price'];
		$PoBAP = $RowGradeData['BAP'];
		$PoVarietyID = $RowGradeData['VarietyID'];
		$BuyerItemCode = $RowGradeData['BuyerItemCode'];
		$TraysQuantity = $RowGradeData['TraysQuantity'];
		$CUPS = $RowGradeData['CUPS'];
		$CupsPrice = $RowGradeData['CupsPrice'];
		$DdpPrice = $RowGradeData['DdpPrice'];
		$Remarks = $RowGradeData['Remarks'];


		$SqlGradeInsert = "INSERT INTO `ScrmCIGradeData`(`PoGradeID`,`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
		`GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`,`BuyerItemCode`,
		`TraysQuantity`,`CUPS`,`CupsPrice`,`DdpPrice`,`Remarks`) VALUES ('$PoGradeID','$PoGrade','$PoMcs','$PoFreezingType',
		'$PIId','$PoPackingStyleID','$PoGlazeType','$PoVariety','$PoGlazePerc','$PoTreatment','$PoBrand','$PoPrice','$PoBAP',
		'$PoVarietyID','$BuyerItemCode','$TraysQuantity','$CUPS','$CupsPrice','$DdpPrice','$Remarks')";

		//  $SqlGradeInsert = "INSERT INTO `ScrmCIGradeData`(`PoGradeID`,`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
		//  `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`,`BuyerItemCode`
		//  ,`CIDescription`,`Codes`,`FreezingDate`,`ConsumptionDate`,`ProductionDate`,`BestBeforeDate`,`Specfications`)
		//  VALUES ('$PoGradeID',
		// '$PoGrade','$PoMcs','$PoFreezingType','$PIId','$PoPackingStyleID','$PoGlazeType','$PoVariety','$PoGlazePerc',
		//  '$PoTreatment','$PoBrand','$PoPrice','$PoBAP','$PoVarietyID','$BuyerItemCode','$CIDescription','$Codes','$FreezingDate'
		//  ,'$ConsumptionDate','$ProductionDate','$BestBeforeDate','$Specfications')";
		$StmtGradeInsert = $iconn->query($SqlGradeInsert);
		if ($StmtGradeInsert) {
			$insertedId = $iconn->insert_id;
			legacyAuditLogInsertById('ScrmCIGradeData', 'ID', $insertedId, [], [
				'ReferenceNo' => $PIId,
				'Remarks' => 'CI grade data inserted from postComBreakUp',
			], 'Exports');
		}

		UpdateComBreakUp($AryBreakUp, $PoGradeID);
	}
	if ($StmtGradeInsert) {
		return true;
	} else {
		return false;
	}
}


function UpdateComBreakUp($AryBreakUp, $GradeID)
{
	global $iconn;

	$AryBreakUp = json_decode($AryBreakUp, true);

	foreach ($AryBreakUp as $Item) {

		$PackingStyleID = $Item['PackingStyleID'];
		$Grade = $Item['Grade'];
		$Mcs = $Item['Mcs'];
		$Codes = $Item['Codes'];
		$CIDescription = $Item['CIDescription'];
		$FreezingDate = $Item['FreezingDate'];
		$ConsumptionDate = $Item['ConsumptionDate'];
		$ProductionDate = $Item['ProductionDate'];
		$BestBeforeDate = $Item['BestBeforeDate'];
		$Specfications = $Item['Specfications'];
		$CIGradeID = $Item['CIGradeID'];
		//$CIGradeID = $GradeID;

		$oldCiGradeBreakup = $iconn->query("SELECT * FROM `ScrmCIGradeData` WHERE `PoGradeID`='$CIGradeID'")->fetch_assoc();
		$sql = "UPDATE `ScrmCIGradeData` SET

			`CIDescription`='$CIDescription',
			`Codes`='$Codes',
			`FreezingDate`='$FreezingDate',
			`ConsumptionDate`='$ConsumptionDate',
			`ProductionDate`='$ProductionDate',
			`BestBeforeDate`='$BestBeforeDate',
			`Specfications`='$Specfications'
			WHERE  `PoGradeID`='$CIGradeID'";
		$stmt = $iconn->query($sql);
		if ($stmt) {
			legacyAuditLogUpdateById('ScrmCIGradeData', 'PoGradeID', $CIGradeID, $oldCiGradeBreakup, [], [
				'ReferenceNo' => $CIGradeID,
				'Remarks' => 'CI grade data updated from UpdateComBreakUp',
			], 'Exports');
		}
	}
}
