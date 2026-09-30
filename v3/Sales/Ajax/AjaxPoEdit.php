<?php





// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL); 

// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// echo "hoo"; exit;





include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include($cp . "Includes/IncludePage.php");





$InvId = $_POST['InvId'];

$Backurl = $_POST['Backurl']; //uma

$Data = $_POST['Data'];

$PoData = json_decode($Data, true);

$AryBreakUp = $PoData['Breakup'];

$status = $PoData['Status'];

// $ConsolidateGrossweightPopup = $_SESSION["ClientConfigData"]["ConsolidateGrossweightPopup"];//by uma(28-02-2024)

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);

$ConsolidateGrossweightPopup = $ArrayClientConfigData['ConsolidateGrossweightPopup']; //uma(01-04-2024)

$EnableCustomInvoicePoData = $ArrayClientConfigData['enableCustomInvoicePoData'];

$POIDD = $PoData['PoID'];

$SqlCount = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$POIDD'";

$StmtCount = $iconn->query($SqlCount);

$RowCustomInvoice = $StmtCount->fetch_array();

$CInvDate = $RowCustomInvoice['CInvDate'];





$Count = 0;

if ($InvId != '') {

	$Status = false;

	$Status = updatePo($PoData);

	if ($Status) {

		$poGradeIds = [];

		foreach ($AryBreakUp as $Item) {

			$Status = updateBreakUp($Item, $Backurl, $PoData['PoID']);

			if (empty($CInvDate) && !empty($Item['PoGradeId'])) {
				$poGradeIds[] = (int) $Item['PoGradeId'];

				$poGradeIds = array_unique($poGradeIds); // remove duplicates
				$idList = implode(',', $poGradeIds);

				if (!empty($idList)) {

					$deleteSql = " DELETE FROM ScrmPIGradeData  WHERE PoGradeId NOT IN ($idList)  AND InvId='$POIDD'";

					$iconn->query($deleteSql);
					$delRows = legacyAuditFetchRowById('ScrmPIGradeData', 'PoGradeId', $idList, 'Sales');
					legacyAuditLogDeleteById('ScrmPIGradeData', 'PoGradeId', $POIDD, $delRows, $_POST, ['Remarks' => 'Removed PI grade items not in breakup'], 'Sales');
				}
			}
		}

	}

	if ($Status) {

		if ($ConsolidateGrossweightPopup == 0) {

			$SqlPackingStyle = "SELECT ID as PoGradeID FROM `ScrmPoGradeData` WHERE InvId='$InvId' order by ID asc";

		} else {

			$SqlPackingStyle = "SELECT DISTINCT(PackingStyleID) as PoGradeID FROM `ScrmPoGradeData` WHERE InvId='$InvId' order by ID asc";

		}



		$StmtPackingStyle = $iconn->query($SqlPackingStyle);





		$AryPackingStyleIds = array();

		while ($RowPackingStyle = $StmtPackingStyle->fetch_array()) {

			$AryPackingStyleIds[] = $RowPackingStyle['PoGradeID'];

		}





		$SqlGrosweight = "SELECT sum(GrossWt) as GrossWt FROM `ScrmPoGradeData` WHERE InvId='$InvId'";

		$StmtGrosweight = $iconn->query($SqlGrosweight);

		$RowGrosweight = $StmtGrosweight->FETCH_ARRAY();

		$GrossWt = $RowGrosweight['GrossWt'];







		$Response['Error'] = null;

		$Response['Msg'] = 'Updated Successfully';

		$Response['CallBackUrl'] = 'PIList';

		$Response['CallBackUrl'] = null; // Method From Url

		$Response['AryPackingStyleIds'] = $AryPackingStyleIds; // Method From Url

		$Response['GrossWt'] = $GrossWt;

	} else {

		$Response['Error'] = 'Unable To Update';

		$Response['Msg'] = 'Pls Contact Admin';

	}





	echo json_encode($Response);

}



function updatePo($PoData)
{

	global $iconn, $status, $CInvDate, $EnableCustomInvoicePoData;

	$PostedUserId = $_SESSION['UserID'];

	//$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));

	$PageUrl = 'PoEdit';



	$PoID = $PoData['PoID'];

	$ExporterID = $PoData['ExporterID'];

	$ProcessorID = $PoData['ProcessorID'];

	$ReferenceNo = $PoData['ReferenceNo'];

	$InvNo = $PoData['InvNo'];

	$InvDate = $PoData['InvDate'];

	$PoNo = $PoData['PoNo'];

	$PoDate = $PoData['PoDate'];

	$ConsigneeID = $PoData['ConsigneeID'];

	$UltimateConsigneeID = $PoData['UltimateConsigneeID'];

	$BuyerID = $PoData['BuyerID'];

	$NotifyPartyID = $PoData['NotifyPartyID'];

	$ImporterID = $PoData['ImporterID'];

	$Agent = $PoData['Agent'];

	$Certification = $PoData['Certification'];

	$PortofLoading = $PoData['PortofLoading'];

	$PlaceofReceipt = $PoData['PlaceofReceipt'];

	$DestinationCountry = $PoData['DestinationCountry'];

	$FinalDestinationCountry = $PoData['FinalDestinationCountry'];

	$PortOfDischarge = $PoData['PortOfDischarge'];

	$FinalDestination = $PoData['FinalDestination'];

	$ShipByDate = $PoData['ShipByDate'];

	$ShipByDateTextformat = $PoData['ShipByDateTextformat'];

	$BankID = $PoData['BankID'];

	$ShowBank = $PoData['ShowBank'];

	$ByPayment = $PoData['ByPayment'];

	$ShippingTerms = $PoData['ShippingTerms'];

	$DeliveryTerms = $PoData['DeliveryTerms'];

	$QtyUnit = $PoData['QtyUnit'];

	$UnitCurrency = $PoData['UnitCurrency'];

	$Freight = $PoData['Freight'];

	$ChargeName = $PoData['ChargeName'];

	$ProductionPlant = $PoData['ProductionPlant'];

	$ChargeAmount = $PoData['ChargeAmount'];

	$Remarks = $PoData['Remarks'];

	$PreparedBy = $PoData['PreparedBy'];

	$OtherCharges = $PoData['OtherCharges'];

	$DdpPrice = $PoData['DdpPrice'];





	//$InvNumFull=getFullPiInvoiceNumbertoSave($ExporterID,$InvDate,$InvNo);

	$InvNumFull = getInvoiceNumber($iconn, $ExporterID, $ProcessorID, $InvDate, $InvNo, 'PI');



	$SqlPiUpdate = "UPDATE `ScrmPIData` SET `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',

`ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',`ConsigneeID`='$ConsigneeID',

`BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',`DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',

`Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',

`FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',

`DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',

`QtyUnit`='$QtyUnit',

`UnitCurrency`='$UnitCurrency',`ShipByDate`='$ShipByDate',`ShipByDateTextformat`='$ShipByDateTextformat',

`Remarks`='$Remarks',`ImporterID`='$ImporterID',`NotifyPartyID`='$NotifyPartyID',

`ReferenceNo`='$ReferenceNo',`Agent`='$Agent',`Certification`='$Certification',

`PortofLoading`='$PortofLoading',`PlaceofReceipt`='$PlaceofReceipt',`ShowBank`='$ShowBank',

`UltimateConsigneeID`='$UltimateConsigneeID',

`Freight`='$Freight', `ChargeName`='$ChargeName',`ChargeAmount`='$ChargeAmount',`ProductionPlantID`='$ProductionPlant',`OtherCharges`='$OtherCharges', `FinalDestinationCountry`='$FinalDestinationCountry' WHERE PoID='$PoID'";



	// Fetch the old data before updating

	$sqlOldData = "SELECT * FROM ScrmPoData WHERE PoID = '$PoID'";

	$stmt = $iconn->query($sqlOldData);

	$oldData = $stmt->fetch_array();

	$stmt->close();

	$status = $oldData['Status'];





	// `ProductionPlantID`='$ProductionPlant',

	$SqlPoUpdate = "UPDATE `ScrmPoData` SET 

 `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',

 `ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',

 `LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',

 `BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',

 `DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',

 `Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',

 `FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',

 `DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',

 `QtyUnit`='$QtyUnit',`UnitCurrency`='$UnitCurrency',

 `ShipByDate`='$ShipByDate',`ShipByDateTextformat`='$ShipByDateTextformat',`Remarks`='$Remarks',`ImporterID`='$ImporterID',

 `NotifyPartyID`='$NotifyPartyID',`ReferenceNo`='$ReferenceNo',

 `Agent`='$Agent',`Certification`='$Certification', 

 `PortofLoading`='$PortofLoading',`PlaceofReceipt`='$PlaceofReceipt',`ShowBank`='$ShowBank',

 `UltimateConsigneeID`='$UltimateConsigneeID',

 `Freight`='$Freight',`ChargeName`='$ChargeName',`ChargeAmount`='$ChargeAmount',`ProductionPlantID`='$ProductionPlant',

 `OtherCharges`='$OtherCharges', `FinalDestinationCountry`='$FinalDestinationCountry'

 WHERE PoID ='$PoID'";

	//echo $SqlPiUpdate;

	//echo $SqlPoUpdate;



	if ($EnableCustomInvoicePoData == 1) {

		if (empty($CInvDate)) {

			$StmtPiUpdate = $iconn->query($SqlPiUpdate);

		}

		$StmtPoUpdate = $iconn->query($SqlPoUpdate);

	} else {

		$StmtPiUpdate = $iconn->query($SqlPiUpdate);

		$StmtPoUpdate = $iconn->query($SqlPoUpdate);

	}



	if ($StmtPiUpdate || $StmtPoUpdate) {

		$ArgumentsArray['PostedUserId'] = $PostedUserId;

		$ArgumentsArray['PageUrl'] = $PageUrl;

		$ArgumentsArray['Remarks'] = $Remarks;

		$ArgumentsArray['ReferenceID'] = $PoID;



		postUserEditLog($ArgumentsArray);

		// echo $rest;exit;



		// Check if old data was fetched successfully

		if ($oldData != null && $status != null && $status === "5") {

			// Array of fields to check

			$fieldsToCheck = [

				'ExporterID',

				'ProcessorID',

				// 'ReferenceNo',

				// 'InvNo',

				// 'InvDate',

				// 'PoNo',

				// 'PoDate',

				// 'ConsigneeID',

				// 'ImporterID',

				// 'NotifyPartyID',

				// 'BuyerID',

				// 'UltimateConsigneeID',

				// 'Agent',

				'Certification',

				// 'PortofLoading',

				'DestinationCountry',

				// 'PortOfDischarge',

				// 'FinalDestination',

				// 'ShipByDate',

				// 'ShipByDateTextformat',

				// 'BankID',

				// 'ShowBank',

				// 'ShippingTerms',

				// 'PreparedBy',

				// 'Remarks',

				// 'ChargeAmount',

				// 'ProductionPlantID',

				// 'ChargeName',

				// 'Freight',

				// 'UnitCurrency',

				// 'QtyUnit',

				// 'DeliveryTerms',

				// 'InvNumFull'

			];

			$sqlOldData = "SELECT * FROM ScrmPoData WHERE PoID = '$PoID'";

			$stmt = $iconn->query($sqlOldData);

			$newData = $stmt->fetch_array();

			$stmt->close();

			// Loop through the fields to check for changes

			foreach ($fieldsToCheck as $field) {

				$oldValue = trim((string) $oldData[$field]);

				$newValue = trim((string) $newData[$field]);

				if ($oldValue != $newValue) { // Use variable variable to get the new value

					if ((($oldValue != null && strlen($oldValue) > 0) || ($newValue != null && strlen($newValue) > 0))) {

						if (($oldValue != "0" && strlen($newValue) > 0)) {

							logChange($iconn, $PoID, $field, $oldValue, $newValue, 0);

						}

					}

				}

			}

		}



		return true;

	} else {

		return false;

	}

}



// Compare old and new values, and log changes into audit table

function logChange($mysqli, $PoID, $field, $oldValue, $newValue, $itemId)
{

	global $iconn;

	if (!$mysqli instanceof mysqli) {

		die("Invalid database connection.");

	}



	$stmt = $mysqli->prepare("

        INSERT INTO ScrmPoAuditLog (fieldName, previousValue, currentValue, poId, itemId, modifyDate) 

        VALUES (?, ?, ?, ?, ?, NOW())

    ");



	if (!$stmt) {

		die("Prepare failed: " . $mysqli->error);

	}



	$stmt->bind_param("sssii", $field, $oldValue, $newValue, $PoID, $itemId);



	if ($field == 'VarietyID') {

		$oldBrandQuery = "SELECT BMS.BrandID,BMS.BrandName,APS.PCKID,APS.PackingStyle,SVL.Glaze,SVL.FreezingType FROM `ScrmPIVarietyList` AS SVL

		 LEFT JOIN AdminPackingStyles AS APS ON SVL.PackingStyle=APS.PCKID 

		LEFT JOIN ScrmBrandsMaster AS BMS ON SVL.BrandName=BMS.BrandID WHERE VarietyID='$oldValue'";



		$oldBrandStmt = $iconn->query($oldBrandQuery);

		$oldRow = $oldBrandStmt->fetch_array();



		$oldBrandID = $oldRow['BrandID'];

		$oldBrandName = $oldRow['BrandName'];

		$oldPCKID = $oldRow['PCKID'];

		$oldPackingStyle = $oldRow['PackingStyle'];

		$oldGlaze = $oldRow['Glaze'];

		$oldFreezingType = $oldRow['FreezingType'];



		$newBrandQuery = "SELECT BMS.BrandID,BMS.BrandName,APS.PCKID,APS.PackingStyle,SVL.Glaze,SVL.FreezingType FROM `ScrmPIVarietyList` AS SVL

	    LEFT JOIN AdminPackingStyles AS APS ON SVL.PackingStyle=APS.PCKID 

		LEFT JOIN ScrmBrandsMaster AS BMS ON SVL.BrandName=BMS.BrandID WHERE VarietyID='$newValue'";



		$newBrandStmt = $iconn->query($newBrandQuery);

		$newRow = $newBrandStmt->fetch_array();



		$newBrandID = $newRow['BrandID'];

		$newBrandName = $newRow['BrandName'];

		$newPCKID = $newRow['PCKID'];

		$newPackingStyle = $newRow['PackingStyle'];

		$newGlaze = $newRow['Glaze'];

		$newFreezingType = $newRow['FreezingType'];



		if ($oldBrandID != $newBrandID) {

			$stmt = $mysqli->prepare("

        INSERT INTO ScrmPoAuditLog (fieldName, previousValue, currentValue, poId, itemId, modifyDate) 

        VALUES (?, ?, ?, ?, ?, NOW())");

			if (!$stmt) {

				die("Prepare failed: " . $mysqli->error);

			}

			$stmt->bind_param("sssii", 'Brand', $oldBrandName, $newBrandName, $PoID, $itemId);

		}

		if ($oldPCKID != $newPCKID) {

			$stmt = $mysqli->prepare("

        INSERT INTO ScrmPoAuditLog (fieldName, previousValue, currentValue, poId, itemId, modifyDate) 

        VALUES (?, ?, ?, ?, ?, NOW())");

			if (!$stmt) {

				die("Prepare failed: " . $mysqli->error);

			}

			$stmt->bind_param("sssii", 'PackingStyle', $oldPackingStyle, $newPackingStyle, $PoID, $itemId);

		}

		if ($oldGlaze != $newGlaze) {

			$stmt = $mysqli->prepare("

        INSERT INTO ScrmPoAuditLog (fieldName, previousValue, currentValue, poId, itemId, modifyDate) 

        VALUES (?, ?, ?, ?, ?, NOW())");

			if (!$stmt) {

				die("Prepare failed: " . $mysqli->error);

			}

			$stmt->bind_param("sssii", 'Glaze', $oldGlaze, $newGlaze, $PoID, $itemId);

		}

		if ($oldFreezingType != $newFreezingType) {

			$stmt = $mysqli->prepare("

        INSERT INTO ScrmPoAuditLog (fieldName, previousValue, currentValue, poId, itemId, modifyDate) 

        VALUES (?, ?, ?, ?, ?, NOW())");

			if (!$stmt) {

				die("Prepare failed: " . $mysqli->error);

			}

			$stmt->bind_param("sssii", 'Block', $oldFreezingType, $newFreezingType, $PoID, $itemId);

		}

		if (!$stmt->execute()) {

			die("Execute failed: " . $stmt->error);

		}



		$stmt->close();

	}

}







//function updateBreakUp($Item){

function updateBreakUp($Item, $Backurl, $PoID)
{

	global $iconn, $InvId, $status, $EnableCustomInvoicePoData, $CInvDate;



	/***uma ****/

	$SqlCustom = "SELECT count(*) as Total FROM ScrmCustomInvoices where PiID='$InvId'";

	$StmtCustom = $iconn->query($SqlCustom);

	$RowCustom = $StmtCustom->fetch_array();

	$CustomTotal = $RowCustom['Total'];

	/***uma ****/



	$PoGradeId = $Item['PoGradeId'];

	$Variety = $Item['Variety'];

	$VarietyId = $Item['VarietyId'];

	$Description = trim($Item['Description']);

	$BuyerItemCode = $Item['code'];

	$Mcs = $Item['MCS'];

	$Price = $Item['Price'];

	$CUPS = $Item['CUPS'];

	$CUPSPrice = $Item['CupsPrice'];

	$TraysQuantity = $Item['TraysQuantity'];
	$DdpPrice = $Item['DdpPrice'];






	$SqlPIVarietyList = "SELECT * FROM `ScrmPIVarietyList` as SPV 

		left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName

		left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle

		WHERE VarietyID='$VarietyId'";

	$StmtPIVarietyList = $iconn->query($SqlPIVarietyList);

	$RowPIVarietyList = $StmtPIVarietyList->fetch_array();



	$PoGradeId = $Item['PoGradeId'];

	//Causing Grouping Disturbance

	//$Variety = $RowPIVarietyList['BuyerProdDescription'] ? $RowPIVarietyList['BuyerProdDescription'] : $Item['Description'];



	if (trim($Item['Description']) == '' && trim($RowPIVarietyList['BuyerProdDescription']) == '') {

		$Variety = $RowPIVarietyList['FullName'];

	} else if (trim($Item['Description']) == '') {

		$Variety = trim($RowPIVarietyList['BuyerProdDescription']);

		$Description = '';

	} else if (trim($Item['Description']) != '') {

		$Variety = $Description = trim($Item['Description']);

	}



	$QcDescription = $RowPIVarietyList['BuyerProdDescription'] ? $RowPIVarietyList['BuyerProdDescription'] : $Item['Description'];





	$VarietyId = $Item['VarietyId'];

	$Description = trim($Item['Description']);

	$code = $Item['code'];

	$Mcs = $Item['MCS'];

	$Price = $Item['Price'];





	$VarietyName = $Variety;

	$Glaze = $RowPIVarietyList['Glaze'];

	$GlazePerc = $RowPIVarietyList['GlazePerc'];

	$Treatment = $RowPIVarietyList['Treatment'];

	$FreezingType = $RowPIVarietyList['FreezingType'];

	$Grade = $RowPIVarietyList['Grade'];

	$PackingStyleID = $RowPIVarietyList['PCKID'];

	$Brand = $RowPIVarietyList['BrandName'];

	$DeviSupc = $RowPIVarietyList['DeviSUPC'];

	$HasSauce = $RowPIVarietyList['HasSauce'];

	if ($HasSauce == 'No' || $HasSauce == '') {

		$CUPS = '';

		$CUPSPrice = '';

	}

	/**uma**/



	$SqlMcs = "SELECT Mcs FROM `ScrmPoGradeData` WHERE ID='$PoGradeId' and InvId='$InvId' ";

	$StmtMcs = $iconn->query($SqlMcs);

	$RowMcs = $StmtMcs->fetch_array();

	$OldMcs = $RowMcs['Mcs'];



	if ($status != null && $status === "5") {

		// Fetch the old data before updating, create audit log

		$sqlOldData = "SELECT * FROM ScrmPoGradeData WHERE ID='$PoGradeId'";

		$StmtMcs = $iconn->query($sqlOldData);

		$oldData = $StmtMcs->fetch_array();

	}

	/**uma**/


	$SqlPoGradeData = "
    UPDATE ScrmPoGradeData SET 
        Grade = '$Grade',
        Mcs = '$Mcs',
        FreezingType = '$FreezingType',
        PackingStyleID = '$PackingStyleID',
        GlazeType = '$Glaze',
        GlazePerc = '$GlazePerc',
        QcDescription = '$QcDescription',
        Variety = '$VarietyName',
        VarietyID = '$VarietyId',
        Treatment = '$Treatment',
        Price = '$Price',
        Brand = '$Brand',
        BAP = '$BAP',
        DeviSupc = '$DeviSupc',
        BuyerItemCode = '$BuyerItemCode',
        Description = '$Description',
        CUPS = '$CUPS',
        CUPSPrice = '$CUPSPrice',
        TraysQuantity = '$TraysQuantity',
        DdpPrice = '$DdpPrice'
";

	// ✅ Only add PIMcs when NOT backurl
	if ($Backurl == '') {
		$SqlPoGradeData .= ", PIMcs = '$Mcs'";
	}

	// Final WHERE
	$SqlPoGradeData .= " WHERE ID IN ($PoGradeId)";

	// Execute
	$StmtPoGradeData = $iconn->query($SqlPoGradeData);

	legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $PoGradeId, $oldData, $_POST, ['Remarks' => ''], 'Sales');

	if ($EnableCustomInvoicePoData == 1 && empty($CInvDate) && $status === "5") {

		// 🔍 Check if PI record exists
		$checkSql = "SELECT 1 FROM ScrmPIGradeData WHERE PoGradeId = '$PoGradeId' LIMIT 1";
		$checkResult = $iconn->query($checkSql);

		if ($checkResult && $checkResult->num_rows > 0) {

			// 🔁 UPDATE EXISTING RECORD
			$updateSql = "
                UPDATE ScrmPIGradeData SET
                    Grade = '$Grade',
                    Mcs = '$Mcs',
                    FreezingType = '$FreezingType',
                    PackingStyleID = '$PackingStyleID',
                    GlazeType = '$Glaze',
                    GlazePerc = '$GlazePerc',
                    QcDescription = '$QcDescription',
                    Variety = '$VarietyName',
                    VarietyID = '$VarietyId',
                    Treatment = '$Treatment',
                    Price = '$Price',
                    Brand = '$Brand',
                    DeviSupc = '$DeviSupc',
                    BuyerItemCode = '$BuyerItemCode',
                    Description = '$Description',
                    InvId = '$InvId',
                    BAP = '$BAP',
                    CUPS = '$CUPS',
                    CUPSPrice = '$CUPSPrice',
                    TraysQuantity = '$TraysQuantity'
                WHERE PoGradeId = '$PoGradeId'
            ";

			$iconn->query($updateSql);

		} else {

			// 🆕 INSERT NEW RECORD
			$insertSql = "
                INSERT INTO ScrmPIGradeData (
                    PoGradeId, Grade, Mcs, FreezingType, PackingStyleID,
                    GlazeType, GlazePerc, QcDescription, Variety, VarietyID,
                    Treatment, Price, Brand, DeviSupc, BuyerItemCode,
                    Description, InvId, BAP, CUPS, CUPSPrice, TraysQuantity
                ) VALUES (
                    '$PoGradeId', '$Grade', '$Mcs', '$FreezingType', '$PackingStyleID',
                    '$Glaze', '$GlazePerc', '$QcDescription', '$VarietyName', '$VarietyId',
                    '$Treatment', '$Price', '$Brand', '$DeviSupc', '$BuyerItemCode',
                    '$Description', '$InvId', '$BAP', '$CUPS', '$CUPSPrice', '$TraysQuantity'
                )
            ";

			$iconn->query($insertSql);
			legacyAuditLogInsertById('ScrmPIGradeData', 'PoGradeId', $iconn->insert_id, $_POST, ['Remarks' => ''], 'Sales');
		}
	}

	if ($StmtPoGradeData) {
		updateQcCodeList($PoID, $PoGradeId, $PackingStyleID);
		if ($OldMcs != $Mcs) { // commented by uma on 01-03-2024 for testing purpose
			postUpdateGrossweight($iconn, $InvId, $PoGradeId, $Backurl, $Mcs);
		}
		/***uma ****/
		if ($CustomTotal > 0) {
			$SqlCIGradeData = "UPDATE `ScrmCIGradeData` SET `Grade`='$Grade',`Mcs`='$Mcs',`FreezingType`='$FreezingType',

					`PackingStyleID`='$PackingStyleID',`GlazeType`='$Glaze',`Variety`='$VarietyName',`GlazePerc`='$GlazePerc',

					`Treatment`='$Treatment',`Brand`='$Brand',`Price`='$Price',`BAP`='$BAP',`VarietyID`='$VarietyId',`CIDescription`='$Description',

					`BuyerItemCode`='$BuyerItemCode' WHERE `PoGradeID` in ($PoGradeId)";

			$StmtCIGradeData = $iconn->query($SqlCIGradeData);
			legacyAuditLogUpdateById('ScrmCIGradeData', 'PoGradeID', $PoGradeId, null, $_POST, ['Remarks' => ''], 'Sales');
		}



		$SqlGrosweight = "SELECT sum(GrossWt) as GrossWt FROM `ScrmPoGradeData` WHERE InvId='$InvId'";

		$StmtGrosweight = $iconn->query($SqlGrosweight);

		$RowGrosweight = $StmtGrosweight->FETCH_ARRAY();
		$GrossWt = $RowGrosweight['GrossWt'];



		/***uma ****/

		if ($status != null && $status === "5" && $sqlOldData && $oldData) {

			$sqlOldData = "SELECT * FROM ScrmPoGradeData WHERE ID='$PoGradeId'";

			$StmtMcs = $iconn->query($sqlOldData);

			$newData = $StmtMcs->fetch_array();

			// Array of fields to check

			$fieldsToCheck = [

				'ID',

				'VarietyID',

				'Description',

				'Mcs',

				'Price',

				'BuyerItemCode'

			];



			// Loop through the fields to check for changes

			foreach ($fieldsToCheck as $field) {

				// Compare values only if they are different

				$oldValue = trim((string) $oldData[$field]);

				$newValue = trim((string) $newData[$field]);

				if ($oldValue !== $newValue) {

					if ((($oldValue != null && strlen($oldValue) > 0) || ($newValue != null && strlen($newValue) > 0))) {

						if (($oldValue != "0" && strlen($newValue) > 0)) {

							logChange($iconn, $PoID, $field, $oldValue, $newValue, $newData['ID']);

						}

					}

					// Log the change if the values are different

					// logChange($iconn, $PoID, $field, (string) $oldData[$field], (string) $newData[$field], $newData['ID']);

				}

			}

		}
		return true;
	} else {
		return false;
	}

}

function updateQcCodeList($InvID, $PoGradeID, $PackingStyleID)
{

	global $iconn, $Count;

	$SqlCodeListCount = "SELECT count(*) as Count FROM `QcCodeList` WHERE InvID='$InvID' and PoGradeID='$PoGradeID'";

	$StmtCodeListCount = $iconn->query($SqlCodeListCount);

	$RowCodeListCount = $StmtCodeListCount->fetch_array();

	$Count = $RowCodeListCount['Count'];

	if ($Count > 0) {

		$oldQcRow = legacyAuditFetchRowById('QcCodeList', 'PoGradeID', $PoGradeID, 'Sales');
		$SqlUpdateQcCodeList = "UPDATE `QcCodeList` SET `PackingStyleID`='$PackingStyleID' WHERE InvID='$InvID' and PoGradeID='$PoGradeID'";

		$iconn->query($SqlUpdateQcCodeList);
		legacyAuditLogUpdateById('QcCodeList', 'PoGradeID', $PoGradeID, $oldQcRow, $_POST, ['Remarks' => ''], 'Sales');

	}

}
