<?php
$Disable = 'style="background: gray; color: white;"';

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($cp . "Includes/CommercialInvoiceLock.php");

$ClientID = $_SESSION['ClientID'];
$HasNegotiationDiscount = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$ForwardContractExceedsValue = $ClientConfigData->ForwardContractExceedsValue; //anil


$PageTitleFolder = "Sales";
$PoID = $_GET['PIID'];

$AryPrices = getShrimpPaymentValues($iconn, $PoID, '', $_SESSION['ClientConfigData'], 'LBS');
// $AryPrices = getPaymentValues($iconn, $PoID);

// exit;
$InvValueUSD = $AryPrices['InvValueUSD'];


$SqlPaymentTerms = "SELECT NoOfDays,ConditionDate FROM `ScrmCommercialData` as SCD
left join `ScrmPaymentTerms` as SPT on SCD.ComPaymentTerms=SPT.ID WHERE PoID='$PoID'";
$StmtPaymentTerms = $iconn->query($SqlPaymentTerms);
$RowPaymentTerms = $StmtPaymentTerms->fetch_array();
$NoOfDays = $RowPaymentTerms['NoOfDays'];
$ConditionDate = $RowPaymentTerms['ConditionDate'];
//Globals
$DueDate = getPaymentDueDate($iconn, $NoOfDays, $ConditionDate, $PoID);


$SqlUtilisedValues = "SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
$StmtUtilisedValues = $iconn->query($SqlUtilisedValues);
$RowUtilisedValues = $StmtUtilisedValues->fetch_array();
$UtilizedValueOld = $RowUtilisedValues['UtilizedValue'];
$UtilizedValueOld = $RowUtilisedValues['UtilizedValue'];

$ShortFallExcCharges = round($InvValueUSD, 2) - round($UtilizedValueOld, 2);


//(ShortFall+EtraAmount)-(BnkChrges+OtherAmount);

$SqlPI = "SELECT * FROM `ScrmPoData` SPID
LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID=SPID.PoID
LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID=SPID.BuyerID
LEFT JOIN ScrmCurrencyMaster SCM ON SCM.CurrencyCode=SPID.UnitCurrency
WHERE SPID.PoID='$PoID'";
$StmtPI = $iconn->query($SqlPI);
$RowPI = $StmtPI->fetch_array();
$ExporterID = $RowPI['ExporterID'];
$PoID = $RowPI['PoID'];
$CurrenySymbol = $RowPI['CurrenySymbol'];
$CustomsInvNo = $RowPI['CustomsInvNo'];

if ($RowPI['PaymentType'] == 'Collections') {
	$PaymentType = 1;
	$PageTitle = "Collections (" . $CustomsInvNo . ')';
	$LocationURL = 'AddCollection?PIID=' . $PoID;
} else {
	$PaymentType = 2;
	$PageTitle = "Negotiations (" . $CustomsInvNo . ')';
	$LocationURL = 'AddCollection?PIID=' . $PoID;
}



$BuyerName = $RowPI['BuyerName'];

$SqlPaymentTerms = "SELECT * FROM `ScrmPaymentTerms` WHERE Status='1'";
$StmtPaymentTerms = $iconn->query($SqlPaymentTerms);

$SqlCollections = "SELECT * FROM `ScrmPaymentCollections` WHERE PIID='$PoID'";
$StmtCollections = $iconn->query($SqlCollections);
$Count = mysqli_num_rows($StmtCollections);




$SqlFcDetails = "SELECT * FROM `ScrmForwardContractsDetails` where Status=1 and BalancedValue > $ForwardContractExceedsValue";
$StmtFcDetails = $iconn->query($SqlFcDetails);

$SqlSbDetails = "SELECT ShippingBillDate,ETA_POD,ISF.BLDate FROM `ScrmShippingBillDetails` as SB
 left join ScrmISFDetails as ISF on ISF.PIID=SB.InvID where InvID='$PoID'";
$StmtSbDetails = $iconn->query($SqlSbDetails);
$RowSb = $StmtSbDetails->fetch_array();

$ShippingBillDate = '';
$ETA_POD = $RowSb['ETA_POD'];
if ($RowSb['BLDate'] !== null && trim($RowSb['BLDate']) != '') {
	$ShippingBillDate = $RowSb['BLDate'];
}
$RowCollections = $StmtCollections->fetch_array();

if ($RowCollections['DueDte'] != '') {
	$DueDate = $RowCollections['DueDte'];
}

// $ShortFall=$InvValueUSD-$UtilizedValueOld+$RowCollections['EtraAmount']-$RowCollections['BnkChrges']
// 						-$RowCollections['OtherAmount'];
$ShortFall = (round($InvValueUSD, 2) + $RowCollections['EtraAmount']) - ($UtilizedValueOld + $RowCollections['BnkChrges'] + $RowCollections['OtherAmount']);

$ShortFall = number_format($ShortFall, 2);

$AryPriceWeightData = getInvoiceWeight($iconn, $GradeID, $PoID);
$AryPriceData = getShrimpPaymentValues($iconn, $PoID, '', $_SESSION['ClientConfigData'], 'LBS');
//print_r($AryPriceData);

$NetWeight = $AryPriceWeightData['Qty'];
$GrossWeight = $AryPriceWeightData['GrossWeightKgs'];
// $InvValueUSD = $AryPriceData['InvValueUSD'];

extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {
	$auditLogger = legacyAuditGetLogger('Sales');
	$existingPaymentCollection = legacyAuditFetchRowById('ScrmPaymentCollections', 'PIID', $PoID, 'Sales');
	$existingRealization = legacyAuditFetchRowById('ScrmRealizationDetails', 'PoID', $PoID, 'Sales');

	/*$ShortFall=$InvValueUSD+$EtraAmount-$BnkChrges-$UtilizedValue-$UtilizedValueOld-$OtherAmount;

		if($ShortFall==0){
			$Status=1;
		}else{
			$Status=0;
		}
		*/

	if (!$HasNegotiationDiscount) {
		$NegotiationDiscount = '';
	}
	if ($Count == 0) {
		$SqlInsert = "INSERT INTO `ScrmPaymentCollections`(
	`PIID`, `CollectionRte`,
	`DueDte`,  `ETAPOD`, `BLDate`,`BnkChrges`, `OtherAmount`, `EtraAmount`,
	`Comments`,`Status`,`Type`)
	VALUES ('$PoID','$CollectionRte',
	'$DueDte','$ETAPOD','$BLDate','$BnkChrges','$OtherAmount',
	'$EtraAmount','$Comments','$Status','$PaymentType')";
	} else {

		$SqlInsert = "UPDATE `ScrmPaymentCollections` SET
	  `CollectionRte`='$CollectionRte',
      `DueDte`='$DueDte',
      `ETAPOD`='$ETAPOD',
      `BLDate`='$BLDate',
      `BnkChrges`='$BnkChrges',
      `OtherAmount`='$OtherAmount',
      `EtraAmount`='$EtraAmount',
      `Status`='$Status',
      `Type`='$PaymentType',
      `Comments`='$Comments'
	   WHERE `PiID`='$PoID'";
	}

	$StmtInsert = $iconn->query($SqlInsert);



	if ($StmtInsert && $ColType != '') {

		$SqlBreakUp = "INSERT INTO `ScrmCollectionBreakUP`( `PiID`, `ColType`, `FcID`,
		`FcNumber`, `BankRefNo`, `ColDate`, `UtilizedValue`,
		`UtilizedDate`, `SpotRate`,`Col_Neg`) VALUES (
		'$PoID',
		'$ColType',
		'$FcID',
		'$FCNumber',
		'$BankRef',
		'$ColDate',
		'$UtilizedValue',
		'$UtilizedDate',
		'$SpotRate',
		'$PaymentType'
		) ";
		$StmtInsert = $iconn->query($SqlBreakUp);
		if ($StmtInsert) {
			$insertedBreakUpId = $iconn->insert_id;
			legacyAuditLogInsertById('ScrmCollectionBreakUP', 'ID', $insertedBreakUpId, $_POST, array(
				'ReferenceNo' => $PoID,
				'Remarks' => 'Collection breakup created',
			), 'Sales');
		}
	}



	if ($StmtInsert) {

		$SqlUtilisedValue = "SELECT NetValue,
	(select sum(UtilizedValue) from ScrmCollectionBreakUP where FCNumber='$FCNumber') as UtilizedValue
	FROM `ScrmForwardContractsDetails` where FCNumber='$FCNumber'";

		$StmtUtilisedValue = $iconn->query($SqlUtilisedValue);
		$RowUtilised = $StmtUtilisedValue->fetch_array();
		$NetValue = $RowUtilised['NetValue'];
		$UtilizedValue = $RowUtilised['UtilizedValue'];
		$Balance = $NetValue - $UtilizedValue;


		$SqlFcDetailsUp = "UPDATE `ScrmForwardContractsDetails` SET `UtilizedValue` ='$UtilizedValue',
 BalancedValue='$Balance' where FCNumber='$FCNumber'";
		$StmtFcDetailsUp = $iconn->query($SqlFcDetailsUp);
		if ($StmtFcDetailsUp && $auditLogger && $FCNumber != '') {
			$fcRows = $auditLogger->fetchRowsByConditions('ScrmForwardContractsDetails', array('FCNumber' => $FCNumber));
			if (!empty($fcRows)) {
				legacyAuditLogUpdateById('ScrmForwardContractsDetails', 'FCID', $fcRows[0]['FCID'], $fcRows[0], array(
					'UtilizedValue' => $UtilizedValue,
					'BalancedValue' => $Balance,
				), array(
					'ReferenceNo' => $PoID,
					'Remarks' => 'Forward contract utilization updated from collection',
				), 'Sales');
			}
		}
	}


	$SqlPaymentStatus = "SELECT BnkChrges,OtherAmount,EtraAmount,
(SELECT sum(UtilizedValue) from ScrmCollectionBreakUP WHERE PiID=ScrmPaymentCollections.PIID) as UtilizedValue
FROM `ScrmPaymentCollections` WHERE PIID=$PoID";
	$StmtPaymentStatus = $iconn->query($SqlPaymentStatus);
	$RowPaymentStatus = $StmtPaymentStatus->fetch_array();

	$BnkChrges = $RowPaymentStatus['BnkChrges'];
	$OtherAmount = $RowPaymentStatus['OtherAmount'];
	$UtilizedValue = $RowPaymentStatus['UtilizedValue'];
	$EtraAmount = $RowPaymentStatus['EtraAmount'];
	$InvValueUSD = round($AryPriceData['InvValueUSD'], 2);


	//$ShortFall=$InvValueUSD+$EtraAmount-$BnkChrges-$UtilizedValue-$OtherAmount;
	$ShortFall = $InvValueUSD - $UtilizedValue + $EtraAmount - $BnkChrges - $OtherAmount;
	$ShortFall = round($ShortFall, 2);
	if ($ShortFall == 0 || $NegotiationDiscount == 'YES') {
		$Status = 1;
	} else {
		$Status = 0;
	}


	$SqlRezCount = "SELECT * FROM `ScrmRealizationDetails` WHERE PoID='$PoID'";
	$StmtRezCount = $iconn->query($SqlRezCount);
	$RezCount = mysqli_num_rows($StmtRezCount);

	if ($PaymentType == 1 && $Status == 1) {
		$RezStatus = 1;
	} else {
		$RezStatus = 0;
	}

	if ($RezCount == 0 && $Status == 1) {
		// $SqlRezInsert = "INSERT INTO `ScrmRealizationDetails`(
		// `PoID`, `RezDueDate`,`FBKCharges`,  `OtherCharges`, `Type`,`RezStatus`)
		// VALUES ('$PoID','$DueDte','$BnkChrges','$OtherAmount','$PaymentType','$RezStatus')";//commented by uma 21-03-2024

		$SqlRezInsert = "INSERT INTO `ScrmRealizationDetails`(
		`PoID`, `RezDueDate`,`Type`,`RezStatus`)
		VALUES ('$PoID','$DueDte','$PaymentType','$RezStatus')";
	} else if ($RezCount > 0) {
		//	$SqlRezInsert = "Delete from ScrmRealizationDetails where `PoID`='$PoID'";
		$SqlRezInsert = "UPDATE `ScrmRealizationDetails` SET
		`RezDueDate`='$DueDte',
		`Type`='$PaymentType',
		`RezStatus`='$RezStatus'
		 WHERE `PoID`='$PoID'";
	} else {
		$SqlRezInsert = "Delete from ScrmRealizationDetails where `PoID`='$PoID'";

		// $SqlRezInsert= "UPDATE `ScrmRealizationDetails` SET

		// `RezDueDate`='$DueDte',
		// `FBKCharges`='$BnkChrges',
		// `OtherCharges`='$OtherAmount',
		// `Type`='$PaymentType',
		// `RezStatus`='$RezStatus'
		//  WHERE `PoID`='$PoID'";
	}

	$StmtRezInsert = $iconn->query($SqlRezInsert);


	//echo $ShortFall; exit;
	$SqlInsert = "UPDATE `ScrmPaymentCollections` SET `Status`='$Status' WHERE `PiID`='$PoID'";
	$StmtInsert = $iconn->query($SqlInsert);


	if ($StmtInsert) {
		if ($existingPaymentCollection) {
			legacyAuditLogUpdateById('ScrmPaymentCollections', 'PIID', $PoID, $existingPaymentCollection, $_POST, array(
				'ReferenceNo' => $PoID,
				'Remarks' => 'Payment collection updated',
			), 'Sales');
		} else {
			legacyAuditLogInsertById('ScrmPaymentCollections', 'PIID', $PoID, $_POST, array(
				'ReferenceNo' => $PoID,
				'Remarks' => 'Payment collection created',
			), 'Sales');
		}
		if ($StmtRezInsert) {
			$currentRealization = legacyAuditFetchRowById('ScrmRealizationDetails', 'PoID', $PoID, 'Sales');
			if ($existingRealization && $currentRealization) {
				legacyAuditLogUpdateById('ScrmRealizationDetails', 'PoID', $PoID, $existingRealization, $_POST, array(
					'ReferenceNo' => $PoID,
					'Remarks' => 'Realization details updated from collection',
				), 'Sales');
			} else if (!$existingRealization && $currentRealization) {
				legacyAuditLogInsertById('ScrmRealizationDetails', 'PoID', $PoID, $_POST, array(
					'ReferenceNo' => $PoID,
					'Remarks' => 'Realization details created from collection',
				), 'Sales');
			} else if ($existingRealization && !$currentRealization) {
				legacyAuditLogDeleteById('ScrmRealizationDetails', 'PoID', $PoID, $existingRealization, $_POST, array(
					'ReferenceNo' => $PoID,
					'Remarks' => 'Realization details deleted from collection',
				), 'Sales');
			}
		}


		echo '<script language="javascript" type="text/javascript">
                alert("Success");
				window.location ="' . $LocationURL . '"

        </script>';
		// window.location ="'.$LocationURL.'";
		//header('Location: PIList.php');

		//automail($PIId,"1","");
	}
}


$isCommercialInvoiceLocked = isCompletedCollection($iconn, $PoID);
?>


<!DOCTYPE html>
<html lang="en">

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">


	<?php
	include($IncludesPath . "PreLoader.php"); ?>

	<!-- Begin page -->
	<div id="layout-wrapper">


		<?php
		include($IncludesPath . "topbar.php");
		include($IncludesPath . "sidebar.php");
		?>



		<!-- ============================================================== -->
		<!-- Start right Content here -->
		<!-- ============================================================== -->
		<div class="main-content">

			<div class="page-content">
				<div class="container-fluid">

					<?php
					if ($Statuss != '') {
						//Show Alert
						?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<i class="mdi mdi-check-all mr-2"></i>
							<?php echo $Status;
							$_SESSION['Status'] = ''; ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>

					<?php } ?>

					<?php include($IncludesPath . "page-title.php"); ?>




					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" onsubmit="return validateForm()" method="post" id="frmAddCollections">
									<div class="card-body">

										<!--<h5 style="color:Brown;"><?php echo $BuyerName; ?>/Wt:<?php echo $NetWeight; ?>/USD:<?php echo $InvValueUSD; ?>$</h5>
				-->
										<div class="row">


											<!---<div class="form-group col-lg-3">
					<label for="exampleInputPassword1">Payment Terms<span style="color:red">*</span></label>

				   <select  required class="form-control select2" name="ByPayment" id="ByPayment">
					<option value="">Select Payment</option>
						<?php
						$PaymentTerms = $RowPI['PaymentTerms'];

						while ($RowPayment = $StmtPaymentTerms->fetch_array()) { ?>
					<option
					 <?php if ($PaymentTerms == $RowPayment['ID']) { ?> selected="selected"  <?php } ?>
					value="<?php echo $RowPayment['ID'] ?>">
					<?php echo $RowPayment['PaymentName'] ?></option>

					<?php } ?>
						</select>
				</div> -->

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">BL Date</label>
												<input <?php echo $Disable ?> readonly type="date"
													onkeydown="return false" class="form-control" name='BLDate'
													id="BLDate" placeholder="" value="<?PHP echo $ShippingBillDate ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">ETA POD</label>
												<input <?php echo $Disable ?> readonly type="date"
													onkeydown="return false" class="form-control" name='ETAPOD'
													id="ETAPOD" placeholder="" value="<?PHP echo $ETA_POD ?>">
											</div>



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Invoice Value</label>
												<input <?php echo $Disable ?> readonly type="decimal"
													class="form-control" name='InvValueUSD' id="InvValueUSD"
													placeholder="Enter Short Fall "
													value="<?PHP echo number_format($InvValueUSD, 2) ?>">
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Short Fall</label>
												<input <?php echo $Disable ?> readonly type="decimal"
													class="form-control" name='ShortFall' id="ShortFall"
													placeholder="Enter Short Fall " value="<?PHP echo $ShortFall ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Collection Rate</label>
												<input type="decimal" class="form-control" name='CollectionRte'
													id="CollectionRte" placeholder="Enter Collection Rate"
													value="<?PHP echo $RowCollections['CollectionRte'] ?>">
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Due Date</label>
												<input type="date" onkeydown="return false" class="form-control"
													name='DueDte' id="DueDte" placeholder=""
													value="<?PHP echo $DueDate ?>">
											</div>









											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Bank Charges</label>
												<input type="decimal" class="form-control" name='BnkChrges'
													id="BnkChrges" placeholder="Enter Bank Charges"
													value="<?PHP echo $RowCollections['BnkChrges'] ?>">
											</div>



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Other Amount(Deductions)</label>
												<input type="decimal" class="form-control" name='OtherAmount'
													id="OtherAmount" placeholder="Enter Other Amount"
													value="<?PHP echo $RowCollections['OtherAmount'] ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Extra Amount</label>
												<input type="decimal" class="form-control" name='EtraAmount'
													id="EtraAmount" placeholder="Enter Extra Amount "
													value="<?PHP echo $RowCollections['EtraAmount'] ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Comments</label>
												<input type="text" class="form-control" name='Comments' id="Comments"
													placeholder="Comments"
													value="<?PHP echo $RowCollections['Comments'] ?>">
											</div>

										</div>

										<hr>


										<div class="row">
											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Payment Mode
												</label>
												<select class="form-control select2" id="ColType" name="ColType">
													<option value="">Select</option>

													<option <?php if ($RowSelect['PaymentMode'] == 'Forward Contract') { ?>selected="selected" <?php } ?> value="Forward Contract">
														Forward Contract</option>

													<option <?php if ($RowSelect['PaymentMode'] == 'Spot Rate') { ?>selected="selected" <?php } ?> value="Spot Rate">Spot Rate
													</option>


												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Forward Contract Number</label>
												<input hidden id="FcID" name="FcID" value="">
												<select class="form-control select2" name="FCNumber" id="FCNumber">
													<option value="">Forward Contract Number</option>
													<?php
													while ($RowFcDetails = $StmtFcDetails->fetch_array()) { ?>
														<option value="<?php echo $RowFcDetails['FCNumber'] ?>">
															<?php echo $RowFcDetails['FCNumber'] ?>
														</option>

													<?php } ?>
												</select>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Bank Ref No</label>
												<input type="text" class="form-control" name='BankRef' id="BankRef"
													placeholder="" value="">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Collection/Negotiation Date:</label>
												<input type="date" onkeydown="return false" class="form-control"
													name='ColDate' id="ColDate" placeholder="" value="">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Utilized Value
													<?php echo $CurrenySymbol ?>
												</label>
												<input type="number" step="0.01" value="0.00" class="form-control"
													name='UtilizedValue' id="UtilizedValue" placeholder="" value="">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Utilized Date</label>
												<input type="date" onkeydown="return false" class="form-control"
													name='UtilizedDate' id="UtilizedDate" placeholder="" value="">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Spot Rate:</label>
												<!--<input  type="number" class="form-control" name='SpotRate' id="SpotRate"
					placeholder="" value="">-->

												<input class="form-control" oninput="" step="0.000001" value="0.000000"
													type="number" name="SpotRate" id="SpotRate">
											</div>
											<?php
											if ($PaymentType == 2 && $HasNegotiationDiscount) { ?>
												<div class="form-group col-sm-3">
													<label for="exampleInputEmail1">Partially Discount:</label>
													<select class="form-control select2" name="NegotiationDiscount"
														id="NegotiationDiscount">
														<option value="">Select One</option>
														<option value="YES" <?php if ($RowCollections['Status'] == 1) { ?>selected="selected" <?php } ?>>Yes</option>
														<option value="NO" <?php if ($RowCollections['Status'] == 0) { ?>selected="selected" <?php } ?>>No</option>
													</select>
												</div>
											<?php } ?>
											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Balance</label>
												<input readonly <?php echo $Disable ?> type="number"
													class="form-control" name='Balance' id="Balance" placeholder=""
													value="">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Value INR</label>
												<input readonly <?php echo $Disable ?>type="number" class="form-control"
													name='ValInr' id="ValInr" placeholder="" value="">
											</div>


										</div>


										<div class="col-xs-12 box-footer" align="right">

											<!-- <a href="CollectionsList" class="btn btn-danger  "> Close</a> -->
											<button type="button" class="btn btn-danger" onclick="goBack()">
												Close
											</button>



											<?php if ($Count == 0) { ?>


												<button type="submit" name='submit' class="btn btn-primary">
													Add</button>


											<?php } else {
													if(!$isCommercialInvoiceLocked){
?>


												<button type="submit" name='submit' class="btn btn-primary">
													Update</button>




											<?php } } ?>
										</div>

								</form>
								<!-- end col -->
							</div> <!-- end row -->

						</div> <!-- end card body-->



						<div class="col-12">


							<div class="card">

								<div class="card-body" id="ShowDataTable">


									<table id="key-datatable" data-paging="false"
										class="table dt-responsive nowrap w-100">

										<thead class="thead-light">
											<tr>


												<th>Transaction Type</th>
												<th>Transaction Ref.No</th>
												<th>Bank Ref.No</th>
												<th>Utilised Value</th>
												<th>Utilized Date</th>
												<th>Rate</th>
												<th>Utilized in INR</th>

												<th> Actions</th>

											</tr>
										</thead>
										<tbody>
											<?php
											//echo $sql;
											$SqlPaymentBrkUp = "SELECT * FROM `ScrmCollectionBreakUP` WHERE PiID='$PIID'";
											$StmtPaymentBrkUp = $iconn->query($SqlPaymentBrkUp);

											$TotalUtilizedValue = 0;
											$TotalUtilizedValueinINR = 0;
											while ($row = $StmtPaymentBrkUp->fetch_array()) {
												$PO_ID = $row['ID'];
												$UtilizedinINR = $row['UtilizedValue'] * $row['SpotRate'];
												$UtilizedValue = $row['UtilizedValue'];
												$TotalUtilizedValue += $UtilizedValue;
												$TotalUtilizedValueinINR += $UtilizedinINR;

												?>
												<tr>

													<td>
														<?php echo $row['ColType']; ?>
													</td>
													<td>
														<?php echo $row['FcNumber']; ?>
													</td>
													<td>
														<?php echo $row['BankRefNo']; ?>
													</td>
													<td>
														<?php echo $row['UtilizedValue'] . $CurrenySymbol; ?>
													</td>
													<td>
														<?php echo $row['UtilizedDate']; ?>
													</td>
													<td>
														<?php echo $row['SpotRate']; ?>
													</td>
													<td>
														<?php echo $UtilizedinINR; ?>
													</td>

													<td>

														<button class="btn btn-link text-danger Delete"
															data-id="<?php echo $row['ColBrkID']; ?>"
															data-value="<?php echo $row['FcNumber']; ?>"
															data-toggle="tooltip" data-placement="top" title="" value=""
															data-original-title="Delete"><i
																class="mdi mdi-trash-can font-size-18"></i></button>

													</td>


												</tr>

												<?php

											}
											?>

											<tr>
												<td><span style="color:red">Total</span></td>
												<td></td>
												<td></td>
												<td>
													<?php echo $TotalUtilizedValue . $CurrenySymbol; ?>
												</td>
												<td></td>
												<td></td>
												<td>
													<?php echo $TotalUtilizedValueinINR; ?>
												</td>

												<td></td>
											</tr>

										</tbody>

									</table>
								</div>

							</div> <!-- end card body-->
						</div> <!-- end card -->


					</div> <!-- end card -->
				</div><!-- end col-->
			</div>
			<!-- end row-->

		</div> <!-- container-fluid -->
	</div>
	<!-- End Page-content -->

	<?php include($IncludesPath . "footer.php"); ?>

	</div>
	<!-- end main content-->

	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
		type="text/javascript"></script>





	<script>

		$(document).ready(function () {
			$(document).on('click', '.Delete', function (e) {
				var CollectionBreakUpID = $(this).data('id');
				var FcNumber = $(this).data('value');
				var PoID = '<?php echo $PIID; ?>';

				var r = confirm("Do you want to delete this ?");
				if (r == true) {

					$.ajax({
						url: 'Ajax/AjaxDeleteCollections.php',
						type: 'POST',
						data: {
							'TYPE': 'CollectionBreakUpdelete',
							'FcNumber': FcNumber,
							'CollectionBreakUpID': CollectionBreakUpID,
							'PoID': PoID
						},

						success: function (response) {
							var data = JSON.parse(response);
							if (data['sts'] == 'Failed') {
								alert(data['msg']);

							} else {
								$('#myModal').modal('hide');
								alert(data['msg']);
								location.reload();

							}
						}
					});

				} else {

				}
			});


			function setTwoNumberDecimal(el) {
				el.value = parseFloat(el.value).toFixed(5);
			};

			function validateForm() {

				var ShortFall = '<?php echo $ShortFallExcCharges ?>';
				//var ShortFall =$("#ShortFall").val();
				var UtilizedValue = $("#UtilizedValue").val();
				var BnkChrges = $("#BnkChrges").val();
				var EtraAmount = $("#EtraAmount").val();
				var OtherAmount = $("#OtherAmount").val();
				var Balance = $("#Balance").val();
				var ColType = $("#ColType").val();
				var TotalExtraAmount = Number(ShortFall) + Number(EtraAmount) - Number(BnkChrges) - Number(OtherAmount);

				if (ShortFall == '0') {
					alert('No Short Fall Amount');
					return false;
				}
				if (UtilizedValue > TotalExtraAmount) {
					alert('Utilized Value Should Not Exceed ' + TotalExtraAmount);
					return false;
				} else if (ColType == 'Forward Contract' && Number(UtilizedValue) > Number(Balance)) {
					alert('Utilized Value Should Not Exceed Bal ' + Balance);
					return false;
				} else {
					return true;
				}
			}

			function updateINR() {

				UtilizedValue = $("#UtilizedValue").val();
				SpotRate = $("#SpotRate").val();
				UtzValInr = (UtilizedValue * SpotRate);
				UtzValInr = parseFloat(UtzValInr).toFixed(2);;
				$('#ValInr').val(UtzValInr);
			}



			$("#BnkChrges").change(function (e) {
				e.preventDefault();
				updateShortFall();
			});
			$("#OtherAmount").change(function (e) {
				e.preventDefault();
				updateShortFall();
			});

			$("#EtraAmount").change(function (e) {
				e.preventDefault();
				updateShortFall();
			});


			function updateShortFall() {

				var SFallExcChar = '<?php echo $ShortFallExcCharges ?>';
				var BnkChrges = $("#BnkChrges").val();
				var EtraAmount = $("#EtraAmount").val();
				var OtherAmount = $("#OtherAmount").val();
				var ShortFallExcCharges = Number(SFallExcChar) + Number(EtraAmount) - Number(BnkChrges) - Number(OtherAmount);
				$('#ShortFall').val((ShortFallExcCharges));
			}


			$("#UtilizedValue").change(function (e) {
				e.preventDefault();
				updateINR();
			});

			$("#SpotRate").change(function (e) {
				e.preventDefault();
				updateINR();
			});


			$("#FCNumber").change(function (e) {
				e.preventDefault();
				getFcNumber();
			});

			$('#ColDate').off('change');
			$('#ColDate').on('change', function () {
				const colDate = $(this).val();
				const utilizedDate = $('#UtilizedDate').val();

				if (utilizedDate === '') {
					$('#UtilizedDate').val(colDate);
				}
			});


			$('#UtilizedDate').off('change');
			$('#UtilizedDate').on('change', function () {
				const utilizedDate = $(this).val();
				if (utilizedDate === '') {
					$('#ColDate').change();
				}
			});


			$("#ColType").change(function (e) {
				e.preventDefault();

				var ColType = $("#ColType").val();



				if (ColType != 'Forward Contract') {
					$('#CollectionRte').prop('required', false);
					document.getElementById("FCNumber").disabled = true;
					//$("#FCNumber").val("");
					document.getElementById("SpotRate").readOnly = false;
					document.getElementById("SpotRate").style.backgroundColor = 'WHITE';
					document.getElementById("SpotRate").style.color = 'black';
					$('#FCNumber').val('').change();
					$('#SpotRate').val('');
					$('#Balance').val('0');
					$('#CollectionRte').val('');
					$('#FcID').val('');
				} else {
					$('#CollectionRte').prop('required', true);
					document.getElementById("FCNumber").disabled = false;
					$('#SpotRate').disabled = true;
					document.getElementById("SpotRate").readOnly = true;
					document.getElementById("SpotRate").style.backgroundColor = 'GRAY';
					document.getElementById("SpotRate").style.color = 'white';
				}


				if (ColType == "") {
					$('#CollectionRte, #FCNumber, #BankRef, #ColDate, #UtilizedValue, #UtilizedDate').prop('required', false);
					$('#SpotRate').prop('required', false);
					$('#CollectionRte, #SpotRate').val("");
				} else {
					$(' #FCNumber, #BankRef, #ColDate, #UtilizedValue, #UtilizedDate').prop('required', true);
				}



			});




			function getFcNumber() {


				var FCNumber = $('#FCNumber').val();
				var $PoID = <?php echo $PoID ?>

				//alert(FCNumber);

				if (FCNumber == '') {
					return;
				}

				$.ajax({
					url: 'Ajax/AjaxFcNumberDetails.php',
					type: 'POST',
					dataType: 'json',
					data: {
						'FCNumber': FCNumber,
						'PoID': $PoID
					},
					success: function (response) {
						var myJSON = JSON.parse(JSON.stringify(response));
						var NetValue = myJSON['NetValue'];
						var WithDrawFcBalance = myJSON['WithDrawFcBalance'];
						var FCID = myJSON['FCID'];
						var UtilizedValue = myJSON['UtilizedValue'];
						var BalancedValue = myJSON['BalancedValue'];
						var TotalRate = myJSON['TotalRate'];
						var SpotRte = myJSON['SpotRte'];
						var Balance = myJSON['Balance'];
						$('#FcID').val(FCID);
						$('#UtilizedValue').val(WithDrawFcBalance);
						$('#SpotRate').val(TotalRate);
						$('#CollectionRte').val(TotalRate);
						$('#Balance').val(Balance);
						$('#ValInr').val(TotalRate * WithDrawFcBalance);


						var ShortFall = $("#ShortFall").val();
						if (WithDrawFcBalance >= ShortFall) {
							$('#UtilizedValue').val(ShortFall);
						}
					}

				});
			}


		});
	</script>

	<script>
		function goBack() {
			const paymentType = '<?php echo $PaymentType; ?>';
			if (paymentType) {
				window.location.href = paymentType && paymentType == 2 ? 'NegotiationList' : 'CollectionsList';
			} else if (document.referrer) {
				window.location.href = document.referrer;
			}
		}
	</script>

</body>

</html>
