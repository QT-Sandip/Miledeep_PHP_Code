<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$QueryParm = $_GET['Q'];
$QueryParms = $_GET['Q'];

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
if(!empty($_POST['request']) && $_POST['request'] == 'McsStatus'){
	$invId = $_POST['InvId'];
	$VarietyID = $_POST['VarietyID'];//pograde id
	$CLID = $_POST['CLID'];
	$Grade = $_POST['Grade'];
	$where .= '';
	if($Grade){
		$where = " AND Grades='$Grade' ";
	}
	$sqlMcs = "SELECT SUM(`McsTotal` ) AS McsTotal FROM QcCodeList WHERE InvID = '$invId' AND `VarietyID` ='$VarietyID' AND CLID!='$CLID' $where";
	$stmtMcs = $iconn->query($sqlMcs);
	$mcsRes = $stmtMcs->fetch_array(); 
	if($mcsRes['McsTotal'] && $stmtMcs){
		echo json_encode(['status' => 'success','mcs' => $mcsRes['McsTotal']]);die;;
	}else{
		echo json_encode( ['status' => 'success','mcs' => 0]);die;;
	}
}

extract($_POST);
extract($_GET);
$ClientConfigData = $_SESSION['ClientConfigData'];
$IsShowQcAddOnFields = $ClientConfigData['IsShowQcAddOnFields'];  // uncomment this line after auth-login.php converted to API modal
// $IsShowQcAddOnFields = $ClientConfigData['IsShowQcAddOnFields']; // remove this line after auth-login.php converted to API modal


$ClientID = $_SESSION["ClientID"];


if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}


$PoID = $_GET['PoID'];
$CLID = $_GET['CLID'];


// $SqlTrace = "SELECT * FROM QcTracebilityList   ORDER by ABS(Tracebility)";


$SqlCodeList = "SELECT *,  APS.PackingStyle as PackingStyleName,CL.VarietyID as GradeID 
	FROM QcCodeList as CL
 left join  ScrmPoGradeData  as SPGD on SPGD.ID=CL.VarietyID
 left join AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID
 left join  ScrmPIVarietyList  as QPV on QPV.VarietyID=SPGD.VarietyID
 left join  QcTracebilityList  as QTL on QTL.TraceID=CL.TracebilityID
 where CL.InvID='$PoID'  and CL.CLID='$CLID'  ORDER BY CLID ASC";
$StmtCodeList = $iconn->query($SqlCodeList);
$RowCodeList = $StmtCodeList->fetch_array();


$SqlTrace = "SELECT TraceID, Tracebility, Tonnage, ExpiryDate, 
                (SELECT SUM(UsedQuantityInCodeList) 
                 FROM QcCodeList as QCL 
                 WHERE QTL.TraceID = QCL.TracebilityID 
                 AND HarvestDt >= QTL.StartDate) AS UsedQuantity,
                (SELECT DATEDIFF(CURDATE(), MIN(HarvestDt))  
                 FROM QcCodeList as QCL  
                 WHERE QTL.TraceID = QCL.TracebilityID 
                 AND HarvestDt >= QTL.StartDate) AS UsedDays 
             FROM QcTracebilityList as QTL 
             WHERE TraceID = '".$RowCodeList['TracebilityID']."' ";

$StmtTrace = $iconn->query($SqlTrace);
$RowTraceSeleted = $StmtTrace->fetch_array();


$StmtCodeList1 = $iconn->query($SqlCodeList);

$SqlPoList = "SELECT *, SPV.ShortName as PoShortname,SPD.Grade AS Grade,SPD.ID as GradeID , SPV.Species as Species FROM `ScrmPoGradeData` as SPD
  left join ScrmPIVarietyList As SPV on SPD.VarietyID=SPV.VarietyID
left join AdminPackingStyles as APS on SPD.PackingStyleID=APS.PCKID
WHERE  SPD.InvId='$PoID'";

$StmtPoList = $iconn->query($SqlPoList);
$StmtPoList1 = $iconn->query($SqlPoList);
$StmtPoList2 = $iconn->query($SqlPoList);

$RowVarietyName = $StmtPoList1->fetch_array();

$VarietyName = $RowVarietyName['PoShortname'];

$SqlInvoice = "SELECT * FROM ScrmPoData where PoID='$PoID'";
$StmtInvoice = $iconn->query($SqlInvoice);
$InvoiceDetails = $StmtInvoice->fetch_array();

$SqlLabUsers = "SELECT * FROM QcLabTechnologist where LbuStatus=1";
$StmtLab = $iconn->query($SqlLabUsers);
$StmtLab1 = $iconn->query($SqlLabUsers);

$SqlCount = "SELECT count(*) as count FROM QcCodeList WHERE InvID='$PoID'";
$StmtCount = $iconn->query($SqlCount);
$RowCount = $StmtCount->fetch_array();

$Count = $RowCount['count'];
$PageTitleFolder = "Quality Control";
$PageTitle = "CodeList - " . $InvoiceDetails['PoNo'];

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {

	$sqlMcs = "SELECT (
						(SELECT `Mcs` FROM ScrmPoGradeData WHERE `ID`=$VarietyID AND `InvId`=$PoID)
							-
						COALESCE(SUM(McsTotal), 0)
						) AS `RemainMcs`
				FROM QcCodeList WHERE InvID = $PoID AND `VarietyID` =$VarietyID AND Grades = '$Grade' AND PackingStyleID = '$PackingStyleID' AND CLID!=$CLID";
	$stmtMcs = $iconn->query($sqlMcs);
	$mcsRes = $stmtMcs->fetch_assoc()['RemainMcs']; 
	if(($mcsRes - $McsGrade) >= 0){
		$LoginId = $_SESSION["UserID"];

		$DtofAnalysis = date("d-m-Y", strtotime($AnalysisDate));
		$DtofCompletion = date("d-m-Y", strtotime($ReportDate));
		$PostedDate = date("Y-m-d");

		$PoGradeID = $QcVarietyID = $VarietyID;

		$sqlPackingStyle = "SELECT TotalSlabs, SlabWeight, MFactor FROM AdminPackingStyles WHERE PCKID = '$PackingStyleID'";
		$stmtPacking = $iconn->query($sqlPackingStyle);
		$rowPackingStyle = $stmtPacking->fetch_array();
		$TotalSlabs = $rowPackingStyle['TotalSlabs'];
		$SlabWeight = $rowPackingStyle['SlabWeight'];
		$MFactor = $rowPackingStyle['MFactor'];

		$UsedQty = $McsGrade * $TotalSlabs * $SlabWeight * $MFactor;

		$oldRowCodeList = legacyAuditFetchRowById('QcCodeList', 'CLID', $CLID, 'QualityControl');
		$sql_org_insert = "UPDATE `QcCodeList` SET `InvID`='$PoID',`VarietyID`='$QcVarietyID',`PoGradeID`='$PoGradeID',
		`Brand`='$Brand',`McsTotal`='$McsGrade',`Grades`='$Grade',`McsGrade`='$McsGrade',`GlazePerc`='$GlazePerc',
		`DayCode`='$DayCode',`TracebilityID`='$TracebilityID',`Yeild`='$Yeild',`PackingStyleID`='$PackingStyleID',
		`BestBeforeDt`='$BestBeforeDt',`BestBeforeDtText`='$BestBeforeDtText',`BuyerCode`='$BuyerCode',`PurchaseLotCode`='$PurchaseLotCode',`HarvestDt`='$HarvestDt',
		`ReceivingDt`='$ReceivingDt',`JulianCode`='$JulianCode',`PreparedByID`='$PreparedBy',`PsiID`='$PSIID',`PsipSentDate`='$PSIPSentDate',
		`UsedQuantityInCodeList`='$UsedQty',`ResetUsedQuantity`='$UsedQty',`SerialNumber`='$SerialNumber',`ItemNumber`='$ItemNumber',`UPCCode`='$UPCCode',
		`HeadlessYield`='$HeadlessYield',`PhosphateOrNonPhosphateChemicalBatchNo`='$PhosphateOrNonPhosphateChemicalBatchNo',`SaltOrNpt`='$SaltOrNpt',
		`PackingMasterCartons`='$PackingMasterCartons',`PackingPouch`='$PackingPouch'   ,`ApprovedByID`='$ApprovedBy'   
		WHERE `CLID`='$CLID'";

		// echo $sql_org_insert;
		// exit;
		$stmt1 = $iconn->query($sql_org_insert);
		legacyAuditLogUpdateById('QcCodeList', 'CLID', $CLID, $oldRowCodeList, $_POST, ['Remarks' => 'Updated Code List entry'], 'QualityControl');
		$sqlSelectUsedQty = "SELECT SUM(UsedQuantity) AS UsedQuantity FROM QcTracebilityList WHERE TraceID = '$TracebilityID'";
		$stmtSelectUsedQty = $iconn->query($sqlSelectUsedQty);
		$rowSelectUsedQty = $stmtSelectUsedQty->fetch_array();
		$UsedQuantity = $rowSelectUsedQty['UsedQuantity'];

		$TotalUsedQty = $UsedQty + $UsedQuantity;
		$oldRowTraceEdit = legacyAuditFetchRowById('QcTracebilityList', 'TraceID', $TracebilityID, 'QualityControl');
		$SqlUpdateUsedQty = "UPDATE QcTracebilityList SET UsedQuantity = '$TotalUsedQty' WHERE TraceID = '$TracebilityID'";
		$stmtInsertUsedQty = $iconn->query($SqlUpdateUsedQty);
		legacyAuditLogUpdateById('QcTracebilityList', 'TraceID', $TracebilityID, $oldRowTraceEdit, $_POST, ['Remarks' => 'Updated Tracebility Used Quantity'], 'QualityControl');
		if ($stmt1) {
			echo '<script>alert("Exports")</script>';
			header('Location: DocClEdit.php?Q=' . $QueryParm);
		} else {
			echo '<script>alert("Exports")</script>';
		}
	}else{
		$mcsRes = ($mcsRes > 0) ? $mcsRes : 0;
		echo '<script>alert("Exceeded! Maximum allowed MCS is ' . $mcsRes . '")</script>';
	}
}

?>



<!DOCTYPE html>

<html lang="en">
<link href="<?php echo $href; ?>" rel="stylesheet" type="text/css" />

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark"> <!--class="vertical-collpsed"-->


	<?php include($IncludesPath . "PreLoader.php"); ?>

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


					<?php include($IncludesPath . "page-title.php"); ?>
					<?php include "PopUpTable.php"; ?>

					<div class="modal fade" id="myModal" role="dialog">

						<div class="modal-dialog">

							<!-- Modal content-->

							<div class="modal-content">

								<div class="modal-header">

									<button type="button" class="close" data-dismiss="modal">&times;</button>

									<h4 class="modal-title modheading"></h4>

								</div>

								<div class="modal-body">


									<div class="box-body" id="ATT_DATA">
									</div>
								</div>

								<div class="modal-footer">

									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div>

							</div>

						</div>

					</div>

					<div class="row">
						<div class="col-12">
							<div class="card">
								<form role="form" id="PalletForm" method="post" onsubmit="return validateForm()">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Day Code</label>
												<input required type="text" class="form-control" name="DayCode"
													id="DayCode" placeholder="Enter Day Code"
													value="<?php echo $RowCodeList['DayCode']; ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Variety</label>
												<select class="form-control select2" name="VarietyID" id="VarietyID"
													required>
													<option value="">Select Variety</option>
													<?php while ($RowPoList = $StmtPoList->fetch_array()) { ?>
														<option <?php if ($RowPoList['GradeID'] == $RowCodeList['GradeID']) { ?> selected=="selected" <?php } ?>
															value="<?php echo $RowPoList['GradeID']; ?>" data-grade_mcs="<?php echo $RowPoList['Mcs'];?>">
															<?php echo $RowPoList['Species'] . '-' . $RowPoList['TypeProduct'] . '-' . $RowPoList['PoShortname'] . '(' . $RowPoList['Grade'] . '-' . $RowPoList['PackingStyle'] . '-' . $RowPoList['Mcs'] . ' Mcs )'; ?>
														</option>
													<?php } ?>
												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Grade</label>
												<input readonly type="text" id="Grade" name="Grade"
													class="form-control hidden"
													value="<?php echo $RowCodeList['Grades']; ?>">


											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Packing Style</label>
												<input hidden type="text" id="PackingStyleID" name="PackingStyleID"
													class="form-control hidden"
													value="<?php echo $RowCodeList['PackingStyleID']; ?>">
												<input readonly type="text" id="PackStyle" name="PackStyle"
													class="form-control "
													value="<?php echo $RowCodeList['PackingStyleName']; ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Brand</label>
												<input readonly type="text" class="form-control" name="Brand" id="Brand"
													placeholder="Enter Brand"
													value="<?php echo $RowCodeList['Brand']; ?> ">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Yield</label>
												<input required type="text" class="form-control" name="Yeild" id="Yeild"
													placeholder="Enter Yield"
													value="<?php echo $RowCodeList['Yeild'] ?>">
											</div>
											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Glaze(%)</label>
												<input readOnly required type="number" class="form-control"
													name='GlazePerc' id="GlazePerc" placeholder="Enter Glaze Percentage"
													value="<?php echo $RowCodeList['GlazePerc'] ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Mcs</label>
												<input required type="Number" class="form-control" name="McsGrade"
													id="McsGrade" placeholder="Enter Mcs"
													value="<?php echo $RowCodeList['McsGrade'] ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Harvest Date</label>
												<input type="date" class="form-control" name="HarvestDt" id="HarvestDt"
													placeholder="Enter date"
													value="<?php echo $RowCodeList['HarvestDt'] ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Receiving Date</label>
												<input type="date" class="form-control" name="ReceivingDt"
													id="ReceivingDt" placeholder="Enter date"
													value="<?php echo $RowCodeList['ReceivingDt'] ?>">
											</div>



											<div class="form-group col-lg-3">

												<label for="exampleInputPassword1">Traceability</label>
												<button href="#" id="AddTraceability" class="btn btn-success"><b> New
													</b></button>

												<select class="form-control select2" name="TracebilityID"
													id="TracebilityID" required>
												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Name Of Farmer </label>
												<input required readonly type="text" class="form-control"
													name='NAME_OF_FARMER' id="NAME_OF_FARMER"
													value="<?php echo $RowCodeList['FarmerName'] ?>"
													placeholder="Enter Name Of Farmer">
											</div>

											<div class="form-group col-lg-6">
												<label for="exampleInputEmail1">Address Of Farmer </label>
												<input required readonly type="text" class="form-control"
													name='ADD_OF_FARMER' id="ADD_OF_FARMER"
													value="<?php echo $RowCodeList['AddressContinued'] ?>"
													placeholder="Enter Address Of Farmer">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Prepared By </label>
												<select class="form-control select2" name="PreparedBy" id="PreparedBy">
													<option value="">Select</option>
													<?php while ($RowLab = $StmtLab->fetch_array()) { ?>
														<option <?php if ($RowCodeList['PreparedByID'] == $RowLab['LUID']) { ?> selected="selected" <?php } ?>
															value="<?php echo $RowLab['LUID'] ?>">
															<?php echo $RowLab['LbuName'] ?>
														</option>

													<?php } ?>
												</select>
											</div>
										</div>
									</div>

									<div class="form-group col-lg-12">
										<hr>
										<div class="row">

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Purchase Bill No</label>
												<input type="text" class="form-control" name="PurchaseLotCode"
													id="PurchaseLotCode" placeholder="Enter Purchase Bill No(Optional)"
													value="<?php echo $RowCodeList['PurchaseLotCode'] ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Buyer Code</label>
												<input type="text" class="form-control" name="BuyerCode" id="BuyerCode"
													placeholder="Enter Buyer Code"
													value="<?php echo $RowCodeList['BuyerCode'] ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Julian Code & Internal Code</label>
												<input type="text" class="form-control" name="JulianCode"
													id="JulianCode" placeholder="Enter Julian Code & (Internal code)"
													value="<?php echo $RowCodeList['JulianCode'] ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Best Before Date</label>
												<input type="date" class="form-control" name="BestBeforeDt"
													id="BestBeforeDt" placeholder="Enter date"
													value="<?php echo $RowCodeList['BestBeforeDt'] ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Best Before Date Text</label>
												<input type="text" class="form-control" name="BestBeforeDtText"
													id="BestBeforeDtText" placeholder="Enter date text"
													value="<?php echo $RowCodeList['BestBeforeDtText'] ?>">
											</div>

											<?php if ($IsShowQcAddOnFields == 1) { ?>
												<div class="form-group col-lg-12">

													<hr>
													<div class="row">

														<div class="form-group col-lg-3">
															<label for="exampleInputPassword1">PSI ID</label>
															<input type="text" class="form-control" name="PSIID" id="PSIID"
																placeholder="Enter PSI ID"
																value="<?php echo $RowCodeList['PsiID'] ?>">
														</div>
														<div class="form-group col-lg-3">
															<label for="exampleInputPassword1">PSIP Sent Date</label>
															<input type="date" class="form-control" name="PSIPSentDate"
																id="PSIPSentDate" placeholder="Enter  Date"
																value="<?php echo $RowCodeList['PsipSentDate'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label for="SerialNumber">SerialNumber</label>
															<input type="text" class="form-control" name="SerialNumber"
																id="SerialNumber" placeholder="Enter Serial Number"
																value="<?php echo $RowCodeList['SerialNumber'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label for="ItemNumber">Item Number</label>
															<input type="text" class="form-control" name="ItemNumber"
																id="ItemNumber" placeholder="Enter Item Number"
																value="<?php echo $RowCodeList['ItemNumber'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label for="UPCCode">UPC Code</label>
															<input type="text" class="form-control" name="UPCCode"
																id="UPCCode" placeholder="Enter UPC code"
																value="<?php echo $RowCodeList['UPCCode'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label for="HeadlessYield">Headless Yield(%)</label>
															<input type="text" class="form-control" name="HeadlessYield"
																id="HeadlessYield" placeholder="Enter Headless Yield(%)"
																value="<?php echo $RowCodeList['HeadlessYield'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label
																for="PhosphateOrNonPhosphateChemicalBatchNo">Phosphate/NonPhosphate
																Chemical Batch No</label>
															<input type="text" class="form-control"
																name="PhosphateOrNonPhosphateChemicalBatchNo"
																id="PhosphateOrNonPhosphateChemicalBatchNo"
																placeholder="Enter Phosphate/NonPhosphate Chemical Batch Number"
																value="<?php echo $RowCodeList['PhosphateOrNonPhosphateChemicalBatchNo'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label for="SaltOrNpt">Salt</label>
															<input type="text" class="form-control" name="SaltOrNpt"
																id="SaltOrNpt" placeholder="Enter Salt"
																value="<?php echo $RowCodeList['SaltOrNpt'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label for="PackingMasterCartons">Packing Master Cartons</label>
															<input type="text" class="form-control"
																name="PackingMasterCartons" id="PackingMasterCartons"
																placeholder="Enter Packing Master Cartons"
																value="<?php echo $RowCodeList['PackingMasterCartons'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label for="PackingPouch">Packing Pouch</label>
															<input type="text" class="form-control" name="PackingPouch"
																id="PackingPouch" placeholder="Enter Packing Pouch"
																value="<?php echo $RowCodeList['PackingPouch'] ?>">
														</div>

														<div class="form-group col-lg-3">
															<label for="exampleInputEmail1">Approved By </label>
															<select class="form-control select2" name="ApprovedBy"
																id="ApprovedBy">
																<option value="">Select</option>
																<?php while ($RowLab = $StmtLab1->fetch_array()) { ?>
																	<option <?php if ($RowCodeList['ApprovedByID'] == $RowLab['LUID']) { ?>
																			selected="selected" <?php } ?>
																		value="<?php echo $RowLab['LUID'] ?>">
																		<?php echo $RowLab['LbuName'] ?>
																	</option>

																<?php } ?>
															</select>
														</div>

													</div>
												</div>

											<?php } ?>
										</div>
									</div>
									<div class="col-xs-12 box-footer" align="right">
										<a href="DocClCreate.php?Q=<?php echo $QueryParm ?>"
											class="btn btn-danger">Close</a>
										<button type="submit" name='submit' class="btn btn-success">Update</button>
									</div>
							</div>

							</form>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body" id="ShowDataTable">
								<table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
									<!-- <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered"> -->
									<thead class="thead-light">

										<tr>
											<th>Bill No</th>
											<th>Day Code</th>
											<th>Variety</th>
											<th>Grade</th>
											<th>Packing Style</th>
											<th>Brand</th>
											<th>Yield %</th>
											<th>Total Mcs</th>
											<th>Farm Reg Number</th>
											<th>Buyer Code</th>
											<th>Best Before Date</th>
											<th>Harvest Date</th>
											<th>Receiving Date</th>
										</tr>

									</thead>

									<tbody>

										<?php
										while ($RowCodeList = $StmtCodeList1->fetch_array()) {
											$CodeListId = $RowCodeList['TracebilityID'];
											$PackingStyleID = $RowCodeList['PackingStyleID'];
											$VarietyID = $RowCodeList['VarietyID'];
											$SerialNumber = $RowCodeList['SerialNumber'];
											$ItemNumber = $RowCodeList['ItemNumber'];
											$UPCCode = $RowCodeList['UPCCode  '];
											$HeadlessYield = $RowCodeList['HeadlessYield'];
											$PhosphateOrNonPhosphateChemicalBatchNo = $RowCodeList['PhosphateOrNonPhosphateChemicalBatchNo'];
											$SaltOrNpt = $RowCodeList['SaltOrNpt'];
											$PackingMasterCartons = $RowCodeList['PackingMasterCartons'];
											$PackingPouch = $RowCodeList['PackingPouch'];

											$SqlPackingStyle = "SELECT * FROM AdminPackingStyles where PCKID='$PackingStyleID'";
											$StmtPackingStyle = $iconn->query($SqlPackingStyle);
											$RowPackingStyle = $StmtPackingStyle->fetch_array();
											$PackingStyle = $RowPackingStyle['PackingStyle'];

											$SqlTracebility = "SELECT * FROM `QcTracebilityList` WHERE TraceID='$CodeListId'";
											$StmtTracebility = $iconn->query($SqlTracebility);
											$RowTrace = $StmtTracebility->fetch_array();

											$FarmTracebility = $RowTrace['Tracebility'];
											$QueryParms = 'PoID=' . $PoID;
											$QueryParm = urlEncode64($QueryParms);

											$BestBeforeDt = date("d-m-Y", strtotime($RowCodeList['BestBeforeDt']));
											$HarvestDt = date("d-m-Y", strtotime($RowCodeList['HarvestDt']));
											$ReceivingDt = date("d-m-Y", strtotime($RowCodeList['ReceivingDt']));

											if ($BestBeforeDt == '01-01-1970') {

												$BestBeforeDt = '--';
											}
											if ($HarvestDt == '01-01-1970') {

												$HarvestDt = '--';
											}
											if ($ReceivingDt == '01-01-1970') {

												$ReceivingDt = '--';
											}
											?>

											<tr>
												<td> <?php echo $RowCodeList['PurchaseLotCode']; ?></td>
												<td> <?php echo $RowCodeList['DayCode']; ?></td>
												<td> <?php echo $VarietyName; ?></td>
												<td> <?php echo $RowCodeList['Grades']; ?></td>
												<td> <?php echo $PackingStyle; ?></td>
												<td> <?php echo $RowCodeList['Brand']; ?></td>
												<td> <?php echo $RowCodeList['Yeild']; ?></td>
												<td> <?php echo $RowCodeList['McsGrade']; ?></td>
												<td> <?php echo $FarmTracebility; ?></td>
												<td> <?php echo $RowCodeList['BuyerCode']; ?></td>
												<td> <?php echo $BestBeforeDt; ?></td>
												<td> <?php echo $HarvestDt; ?></td>
												<td> <?php echo $ReceivingDt; ?></td>
											</tr>

											<?php

										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div> <!-- end row -->
				<!-- </div>  -->
				<!-- end card body-->
			</div>

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
	<?php // include($IncludesPath . "ShowDataTable.php"); 
	?>



</body>

</html>
<Script>
	$('#McsGrade').change(function () {
		var array = $('#McsGrade').val().split(",");
		for (var i = 0, sum = 0; i < array.length; sum += parseInt(array[i++]));
		$('#McsTotal').val(sum);

	});


	$('#TracebilityID').change(function () {
		document.getElementById("NAME_OF_FARMER").disabled = true;
		document.getElementById("ADD_OF_FARMER").disabled = true;


		var FarmId = $('#TracebilityID').val();

		$.ajax({
			type: 'POST',
			url: 'Ajax/AjaxFarmerList.php',
			dataType: 'json',
			data: {
				'Type': 'FARMERS',
				'FarmId': FarmId
			},
			success: function (response) {
				var myJSON = JSON.parse(JSON.stringify(response));
				// console.log(myJSON);
				//alert(myJSON['Farmer_Name']); 
				$("#NAME_OF_FARMER").val(myJSON['FarmerName']);
				$("#ADD_OF_FARMER").val(myJSON['AddressContinued']);

			}
		});


	});


	$('#VarietyID').change(function () {
		var VarietyID = $('#VarietyID').val();
		//alert(VarietyID);
		var PoId = '<?php echo $PoID; ?>';
		//document.getElementById("Grades").disabled=true;
		document.getElementById("PackStyle").disabled = true;
		document.getElementById("GlazePerc").readOnly = true;

		/*if(VarietyID!=''){

			document.getElementById("Grades").disabled=true;
			document.getElementById("PackStyle").disabled=true;
			document.getElementById("GlazePerc").readOnly=true;
			document.getElementById("McsGrade").readOnly=true;
			
		}else{
			document.getElementById("Grades").disabled=false;
			document.getElementById("PackStyle").disabled=false;
			document.getElementById("GlazePerc").disabled=false;
			document.getElementById("McsGrade").disabled=false;
			
		}	 */
		$.ajax({
			type: 'POST',
			url: 'Ajax/AjaxDependantDropDowns.php',
			dataType: 'json',
			data: {
				'Type': 'CodeListAutoFillValues',
				'VarietyID': VarietyID,
				'PoId': PoId
			},
			success: function (response) {
				var myJSON = JSON.parse(JSON.stringify(response));

				//$("#VarietyID").html(response);

				$("#Grade").val(myJSON['Grade']);
				// $("#Grades").val(myJSON['Grade']).change();		 
				$("#PackingStyleID").val(myJSON['PackingStyleID']);
				$("#PackStyle").val(myJSON['PackingStyle']);
				$("#GlazePerc").val(myJSON['GlazePerc']);
				// $("#McsGrade").val(myJSON['Mcs']);
				$("#Brand").val(myJSON['Brand']);

			}
		});
	});
</script>


<Script>
	$(document).on('click', '#AddTraceability', function (e) {
		e.preventDefault();
		//var YEAR=$('#YEAR').val();

		//alert(YEAR);



		$.ajax({
			url: 'Ajax/AjaxPopupAddTraceability.php',
			type: 'POST',
			data: {
				'Type': 'ADDTRACEABILITY',
			},
			success: function (data) {

				$("#PopUpTable").html(data);
				$('#myModal').modal('show');
			}
		});
		//}
	});


	$(document).on('click', '.ButtonAddTrace', function (e) {
		e.preventDefault();
		var Tracebility = $('#Tracebility').val();
		var FarmerName = $('#FarmerName').val();
		var AddressContinued = $('#AddressContinued').val();
		var RegAuthority = $('#RegAuthority').val();
		var TracebilityID = $('#TracebilityID').val();

		if (Tracebility == '') {
			alert('Please Enter Tracebility');
			return;
		}
		if (FarmerName == '') {
			alert('Please Enter FarmerName');
			return;
		}
		if (AddressContinued == '') {
			alert('Please Enter AddressContinued');
			return;
		}



		$.ajax({
			type: 'POST',
			url: 'Ajax/AjaxNewFarmer.php',
			data: {
				'Tracebility': Tracebility,
				'FarmerName': FarmerName,
				'AddressContinued': AddressContinued,
				'RegAuthority': RegAuthority,
				'Type': 'postNewTracebility'
			},


			success: function (response) {
				var data = JSON.parse(response);
				if (data['Error'] == null) {
					$('#myModal').modal('hide');
					alert(data['Msg']);
					$("#TracebilityID").html(data['TracebilityList']);
				} else {

					alert(data['Error']);
				}


			}
		});


	});

	$('document').ready(function () {

		//alert('hiiiii');
		$('#FARM_REG_IDd').on("keyup", function () {
			/* Get input value on change */
			//alert('hiiii');
			var inputVal = $(this).val();

			//alert(inputVal);
			var resultDropdown = $(this).siblings(".result");
			if (inputVal.length) {
				$.get("AjaxFarmerList.php", {
					term: inputVal
				}).done(function (data) {
					// Display the returned data in browser

					//console.log('test');
					//console.log(data);
					//alert(data);
					resultDropdown.html(data);
				});
			} else {
				resultDropdown.empty();
			}

		});

		// Set search input value on click of result item
		$(document).on("click", ".result p", function () {
			var iid = $(this).attr('id');
			//alert(iid);
			$(this).parents(".username").find('#cnameid').val($(this).text());
			$(this).parents(".username").find('#cnameid1').val(iid);

			$(this).parent(".result").empty();
			//alert(cat);
			$.ajax({
				type: 'POST',
				url: 'AjaxFarmerList.php',
				data: {
					'id': iid,
					type: 'detils'
				},
				success: function (data) {
					//alert(data);
					console.log(data);
					var obj = JSON.parse(data);

					var REG_NO = obj.Farm_ID;
					//ALERT(REG_NO);
					var res = REG_NO.split("/");
					var F_NAME = obj.Farmer_Name;
					var F_pond_id = obj.No_of_Ponds;
					var F_ADD = obj.Address;
					var F_ADD_C = obj.Address_continued;
					//alert(F_ADD_C);

					$('#FARM_REG_ID').val(res[0] + '/00');
					$('#NAME_OF_FARMER').val(F_NAME);
					$('#ADD_OF_FARMER').val(F_ADD + F_ADD_C);

				}
			});
			$(this).parent(".result").empty();

		});

	});

$("#TracebilityID").select2({
  placeholder: "Select Farm Reg Number", // Placeholder for better UX
  allowClear: true, // Allows clearing selection
  ajax: {
    url: "Ajax/ajax-traceability-dropdown.php",
    dataType: 'json',
    delay: 500,
    data: function (params) {
      return {
        q: params.term, // Search term
        page: params.page
      };
    },
    processResults: function (data, params) {
      console.log(data.Data.TraceabilityData);

      params.page = params.page || 1;

      let results = data.Data.TraceabilityData.map(item => ({
        id: item.id,
        text: item.text
      }));
      // // Add "Choose Category" only on the first page
      // if (params.page === 1) {
      //   results.unshift({ id: "", text: "Select Farm Reg Number", disabled: true });
      // }

      return {
        results: results,
        pagination: {
          more: (params.page * 100) < data.Data.TotalRows
        }
      };
    },
    cache: true
  },
  escapeMarkup: function (markup) { return markup; } // Allow custom formatting
});

// **Manually add the default value to Select2**
var selectedId = "<?php echo $RowTraceSeleted['TraceID']; ?>";
var selectedText = "<?php echo $RowTraceSeleted['Tracebility']; ?>";
if (selectedId && selectedText) {
  let option = new Option(selectedText, selectedId, true, true);
  $("#TracebilityID").append(option).trigger("change");
}
<?php
if($PoID){?>
	$('document').ready(function () {
		getTheTotalCodeListMcs('');
	})
	$(document).on('keyup', '#McsGrade', function (e) {
		if ($(this).attr('data-grade_mcs') === undefined) {
			var fromMcsTxt = 1;
			getTheTotalCodeListMcs(fromMcsTxt);
		}else{
			var txtMcs = $(this).val()
			var mcs = $(this).data('mcs_total');
			var grade_mcs = $(this).data('grade_mcs');
			if(mcs == 0){
				if((grade_mcs - txtMcs) < 0){
					var mcsAllowedLimit = grade_mcs;
					alert('Exceeded! Maximum allowed MCS is '+mcsAllowedLimit);
					$('#McsGrade').val('');
					return;
				}
			}else{
				if(((grade_mcs - mcs) - txtMcs) < 0){
					var mcsAllowedLimit = (grade_mcs - mcs) > 0 ? grade_mcs - mcs : 0;
					alert('Exceeded! Maximum allowed MCS is '+mcsAllowedLimit);
					$(this).val('');
					return;
				}
			}
		}
	})
	$(document).on('change', '#VarietyID', function (e) {
		getTheTotalCodeListMcs('');
	})
	function getTheTotalCodeListMcs(fromMcsTxt = ''){
		var VarietyID = $('#VarietyID').val();
		if(VarietyID){
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: { 
					'request': 'McsStatus',
					'InvId': '<?php echo $PoID;?>',
					'VarietyID' : VarietyID,
					'CLID' : '<?php echo $CLID;?>',
					//'Grade' : $('#Grade').val()
				},
				success: function (response) {	
					let grade_mcs = $('#VarietyID option:selected').data('grade_mcs');
					var mcs = response.mcs ? response.mcs : 0;	
					$('#McsGrade').attr('data-mcs_total', mcs);	
					$('#McsGrade').attr('data-grade_mcs', grade_mcs);	

					if(fromMcsTxt == 1){
						var txtMcs = $('#McsGrade').val()
						if(mcs == 0){
							if((grade_mcs - txtMcs) < 0){
								var mcsAllowedLimit = grade_mcs;
								alert('Exceeded! Maximum allowed MCS is '+mcsAllowedLimit);
								$('#McsGrade').val('');
								return;
							}
						}else{
							if(((mcs - grade_mcs) - txtMcs) < 0){
								var mcsAllowedLimit = (grade_mcs - mcs) > 0 ? grade_mcs - mcs : 0;
								alert('Exceeded! Maximum allowed MCS is '+mcsAllowedLimit);
								$('#McsGrade').val('');
								return;
							}
						}
					}
				}
			});
		}else{
			alert('Please select a Variety.');
			$('#McsGrade').removeAttr('data-mcs_total');	
			$('#McsGrade').removeAttr('data-grade_mcs');
		}
	}
<?php }?>
</script>
