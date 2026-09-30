<?php

session_start();


$PageTitle = "Commercial Invoice";
$PageTitleFolder = "Sales";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($cp . "Includes/CommercialInvoiceLock.php");

$PoID = $_GET['PIID'];
$ExporterID = $_GET['ExporterID'];

$SqlBank="SELECT * FROM `ScrmBankDetailsMaster` WHERE `BankStatus` = '1'";
	$StmtBank=$iconn->query($SqlBank);

$SqlPaymentTerms = "SELECT * FROM ScrmPaymentTerms  WHERE `Status` = '1'";
$StmtPaymentTerms = $iconn->query($SqlPaymentTerms);

$SqlShipmentTerms = "SELECT * FROM `ScrmShipmentTermsMaster` WHERE Status=1";
$StmtShipmentTerms = $iconn->query($SqlShipmentTerms);

$SqlPacking = "SELECT * FROM `$TblAdminPackingStyles` Where Status='1' order by abs(PackingStyle) Asc  limit 100";

$StmtPacking = $iconn->query($SqlPacking);
while ($RowPacking = $StmtPacking->fetch_array()) {
	$ArrayPacking[] = $RowPacking;
}

$SQL_OPTIONS = "SELECT * FROM `OPTIONS` WHERE  DEPT='CS' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS = $iconn->query($SQL_OPTIONS);
$ARY_OPTIONS = array();
while ($ROW = $STMT_OPTIONS->FETCH_ARRAY()) {
	$ARY_OPTIONS[] = $ROW;
}


$SqlCIGradeEdit = "SELECT * FROM `ScrmCIGradeData` as SCGD
LEFT JOIN  AdminPackingStyles on SCGD.PackingStyleID= AdminPackingStyles.PCKID
 where SCGD.InvId='$PoID' ORDER BY `ID` ASC";


$StmtCIGradeEdit = $iconn->query($SqlCIGradeEdit);
$BreakupCount=mysqli_num_rows($StmtCIGradeEdit);

if($BreakupCount==0){
	$SqlCIGradeEdit = "SELECT * FROM `ScrmPoGradeData` as SCGD
	LEFT JOIN  AdminPackingStyles on SCGD.PackingStyleID= AdminPackingStyles.PCKID
	 where SCGD.InvId='$PoID' ORDER BY `ID` ASC";
}
//$RowCIGradeEdit = $StmtCIGradeEdit->fetch_array();
$StmtCIGradeEdit = $iconn->query($SqlCIGradeEdit);


$SqlPI = "SELECT
SPD.PoID,SPD.InvNo,SPD.InvDate,SPD.PoNo,SPD.ConsigneeID,PaymentTerms,ShippingTerms,
SPD.Status,SCD.ConsigneeName,ContainerNo,
SCD.ConsigneeAddress,SCD.ConsigneeCountry
,SCI.CInvNo,SCI.Discount,SCI.Freight
,SCI.ExRate,CustomsInvNo,SPD.BankID as PoBankID
FROM `ScrmPoData` SPD
LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID
LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID=SPD.PoID
WHERE SPD.PoID='$PoID'";
$StmtPI = $iconn->query($SqlPI);
$RowPI = $StmtPI->fetch_array();

$InvNo = $RowPI['InvNo'];
$Discount = $RowPI['Discount'];
$Freight = $RowPI['Freight'];
$ExchangeRate = $RowPI['ExRate'];
$ExporterID = $RowPI['ExporterID'];
$InvDate = $RowPI['InvDate'];
$PoNo = $RowPI['PoNo'];
$CInvNo = $RowPI['CustomsInvNo'];
$ConsigneeName = $RowPI['ConsigneeName'];
$ConsigneeAddress = $RowPI['ConsigneeAddress'];
$ContainerNo = $RowPI['ContainerNo'];
$PoBankID = $RowPI['PoBankID'];





//$CInvNo = getInvoiceNumber($iconn, $ExporterID, $PoID);
//$PoNo=getPIInvoiceNumber($iconn,$ExporterID,$PoID);


$SqlCIDescriptionSpl = "SELECT ComDescriptionSpl,DataLoggerPositions,DataLogger FROM `ScrmISFDetails` where PIID='$PoID'";
$StmtCIDescriptionSpl = $iconn->query($SqlCIDescriptionSpl);
$RowCIDescriptionSpl = $StmtCIDescriptionSpl->fetch_array();
//echo $SqlCIDescriptionSpl;
$SqlCIInv = "SELECT * FROM `ScrmCommercialData` where PoID='$PoID'";
$StmtCIInv = $iconn->query($SqlCIInv);
$RowCIInv = $StmtCIInv->fetch_array();
$CommercialCount=mysqli_num_rows($StmtCIInv);

if ($RowCIInv['ComPaymentTerms'] != 0) {
	$PaymentTerms = $RowCIInv['ComPaymentTerms'];
} else {
	$PaymentTerms = $RowPI['PaymentTerms'];

}

if ($RowCIInv['ComShippingTerms'] != '') {
	$ShippingTerms = $RowCIInv['ComShippingTerms'];
} else {
	$ShippingTerms = $RowPI['ShippingTerms'];

}

if ($RowCIInv['Discount'] != '') {
	$Discount = $RowCIInv['Discount'];
}

if ($RowCIInv['Freight'] != '') {
	$Freight = $RowCIInv['Freight'];
}

if ($RowCIInv['ExchngRte'] != '') {
	$ExchangeRate = $RowCIInv['ExchngRte'];
}

if($RowCIInv['BankID']==0){
	$BankID=$PoBankID;
	}
	else{
	$BankID=$RowCIInv['BankID'];
	}
$CICount = mysqli_num_rows($StmtCIInv);


extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {
}


$SqlPaymntType = "SELECT LcStatus FROM `ScrmPaymentTerms` WHERE ID='$PaymentTerms'";
$StmtPaymntType = $iconn->query($SqlPaymntType);
$RowPaymntType = $StmtPaymntType->fetch_array();

$LcStatusType = $RowPaymntType['LcStatus'];
$isCommercialInvoiceLocked =false;
$isOnlyCompletedCommercialInvoiceStatus = isOnlyCompletedCommercialInvoiceStatus($iconn, $PoID);

if($isOnlyCompletedCommercialInvoiceStatus){
	$isCommercialInvoiceLocked = isCommercialInvoiceLockedByPiId($iconn, $PoID);
}
?>





<!DOCTYPE html>

<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>
<style>
	div.dataTables_wrapper div.dataTables_filter {
    text-align: left !important;
}

div.dataTables_wrapper div.dataTables_filter input {
    margin-left: 0.5em;
}

</style>

</head>

<body data-sidebar="dark">
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



					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body" id="ShowDataTable">
									<div class="row">
										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1"> Custom Invoice</label>
											<input readonly required type="text" class="form-control" name='CInvNo'
												id="CInvNo" style="background: gray;color: white;"
												placeholder="Enter Custom Invoice No" value="<?php echo $CInvNo; ?>">
										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">PO No</label>
											<input readonly required type="text" class="form-control" name='CInvNo'
												id="CInvNo" style="background: gray;color: white;"
												placeholder="Enter  PO No" value="<?php echo $PoNo; ?>">
										</div>


										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Consignee</label>
											<input readonly required type="text" class="form-control"
												name='ConsigneeName' id="ConsigneeName"
												style="background: gray;color: white;"
												placeholder="Enter Consignee Name" value="<?php echo $ConsigneeName ?>">
										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Address</label>
											<input readonly required type="text" class="form-control"
												name='ConsigneeAddress' id="ConsigneeAddress"
												style="background: gray;color: white;"
												placeholder="Enter Consignee Address"
												value="<?php echo $ConsigneeAddress ?>">
										</div>


										<div class="form-group col-lg-3">
											<label for="exampleInputPassword1">Payment Terms<span
													style="color:red">*</span></label>

											<select required class="form-control select2" name="ComPaymentTerms"
												id="ComPaymentTerms">
												<option value="">Select Payment</option>
												<?php while ($RowPayment = $StmtPaymentTerms->fetch_array()) {
													if ($RowPayment['LcStatus'] == 1) {
														$LcStatus = 'LC';

													} else {
														$LcStatus = 'Non LC';

													}
													?>
													<option <?php if ($PaymentTerms == $RowPayment['ID']) { ?>selected=='selected' <?php } ?>
														value="<?php echo $RowPayment['ID'] ?>">
														<?php echo $RowPayment['PaymentName'] . ' - ' . $LcStatus ?>
													</option>

												<?php } ?>
											</select>
										</div>



										<div class="form-group col-lg-3">
											<label for="exampleInputEmail1">Shipment Terms<span
													style="color:red">*</span></label>

											<select required class="form-control select2" name="ComShippingTerms"
												id="ComShippingTerms">
												<option value="">Select Shipment Terms</option>

												<?php while ($RowShipterms = $StmtShipmentTerms->fetch_array()) { ?>

													<option <?php if ($RowShipterms['TermName'] == $ShippingTerms) { ?>
															selected='selected' <?php } ?>
														value="<?php echo $RowShipterms['TermName'] ?>">
														<?php echo $RowShipterms['TermName'] ?></option>
												<?php } ?>
												<select>

										</div>

										<div class="form-group col-lg-3">
											<label for="exampleInputEmail1">Shipment Terms Ext</label>
											<input type="text" class="form-control" name='ComShippingTermExt'
												id="ComShippingTermExt" placeholder="Enter ShippingTerm Ext"
												value="<?php echo $RowCIInv['ComShippingTermExt'] ?>">
										</div>

										<div class="form-group col-lg-3">
											<label for="exampleInputEmail1">Discount</label>
											<input type="decimal" class="form-control" name='Discount' id="Discount"
												placeholder="Enter Discount" value="<?php echo $Discount ?>">
										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Freight<span
													style="color:red">*</span></label>
											<input required type="text" class="form-control" name='Freight' id="Freight"
												placeholder="Enter Freight" value="<?php echo $Freight ?>">

										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">ExRate<span
													style="color:red">*</span></label>
											<input required type="text" class="form-control" name='ExchngRte'
												id="ExchngRte" placeholder="Enter Exchange Rate"
												value="<?php echo $ExchangeRate; ?>">

										</div>
										<!--<div class="form-group col-lg-3" >
				<label for="exampleInputEmail1">Show Bank<span style="color:red">*</span></label>
				<select required class="form-control select2" name="ShowBank" id="ShowBank">
					 <option  value="0">No</option>
					<option  value="1">Yes</option>

				</select>
				</div>-->

				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Bank Details</label>
                   <select  class="form-control select2" name="BankID" id="BankID">
				 				  <option value="">Select Bank</option>

				   <?php
				   while ($RowBank=$StmtBank->fetch_array()){ ?>
				     <option <?php if($BankID==$RowBank['ID']){ ?> selected="selected"  <?php } ?>


					 value="<?php echo $RowBank['ID'] ?>">
					 <?php echo $RowBank['AccntName'].' -
					 '.$RowBank['BankName'].'-'.$RowBank['AccountNumber'].'-'.$RowBank['RoutingThrough'] ?></option>

				 <?php  }?>
				  </select>
                </div>
										<div class="form-group col-lg-3">
											<label for="exampleInputEmail1">Show Bank<span
													style="color:red">*</span></label>
											<select required class="form-control select2" name="ShowBank" id="ShowBank">
												<option <?php if ($RowCIInv['ShowBank'] == 0) { ?> selected="selected"
													<?php } ?> value="0">No</option>
												<option <?php if ($RowCIInv['ShowBank'] == 1 || !$RowCIInv) { ?> selected="selected"
													<?php } ?> value="1">Yes</option>

											</select>
										</div>

										<div class="form-group col-lg-3">
											<label for="exampleInputEmail1">Show BL No<span
													style="color:red">*</span></label>
											<select required class="form-control select2" name="ShowBLNo" id="ShowBLNo">
												<option <?php if ($RowCIInv['ShowBLNo'] == 0) { ?> selected="selected"
													<?php } ?> value="0">No</option>
												<option <?php if ($RowCIInv['ShowBLNo'] == 1 || !$RowCIInv) { ?> selected="selected"
													<?php } ?> value="1">Yes</option>

											</select>
										</div>

										<div class="form-group col-lg-3">
											<label for="exampleInputEmail1">Show BL Date<span
													style="color:red">*</span></label>
											<select required class="form-control select2" name="ShowBLDate" id="ShowBLDate">
												<option <?php if ($RowCIInv['ShowBLDate'] == 0) { ?> selected="selected"
													<?php } ?> value="0">No</option>
												<option <?php if ($RowCIInv['ShowBLDate'] == 1 || !$RowCIInv) { ?> selected="selected"
													<?php } ?> value="1">Yes</option>

											</select>
										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Data Logger</label>
											<input type="text" class="form-control" name='DataLogger' id="DataLogger"
												placeholder="Enter Data Logger Details"
												value="<?php echo $RowCIDescriptionSpl['DataLogger'] ?>">

										</div>
										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Placed AT</label>
											<input type="text" class="form-control" name='DataLoggerPositions'
												id="DataLoggerPositions" placeholder="Enter Data Logger Positions"
												value="<?php echo $RowCIDescriptionSpl['DataLoggerPositions'] ?>">

										</div>

										<?php if ($LcStatusType == 1) { ?>

											<div class="form-group  col-sm-3">
												<label for="exampleInputEmail1">Lc No </label>
												<input type="text" class="form-control" name='LcNo' id="LcNo"
													placeholder="Enter Lc No" value="<?php echo $RowCIInv['LcNo']; ?>">
											</div>

											<div class="form-group  col-sm-3">
												<label for="exampleInputEmail1">Lc Date</label>
												<input type="date" onkeydown="return false" class="form-control"
													name='LcDate' id="LcDate" placeholder="Enter End Date"
													value="<?php echo $RowCIInv['LcDate']; ?>">
											</div>

										<?php } else {

										} ?>
										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Issued By</label>
											<input name="IssuedBy" class="form-control" id="IssuedBy"
												placeholder="Enter Issued By"
												value="<?php echo $RowCIInv['IssuedBy']; ?>"></input>

										</div>
										<div class="form-group col-lg-3">
											<label for="exampleInputEmail1">Show Shipping Details<span
													style="color:red">*</span></label>
											<select required class="form-control select2" name="ShowShippingDetails" id="ShowShippingDetails">
												<option <?php if ($RowCIInv['ShowShippingDetails'] == 0) { ?> selected="selected"
													<?php } ?> value="0">No</option>
												<option <?php if ($RowCIInv['ShowShippingDetails'] == 1 || !$RowCIInv) { ?> selected="selected"
													<?php } ?> value="1">Yes</option>

											</select>
										</div>

										<div class="form-group col-lg-12">
											<label for="exampleInputEmail1">Special Notes(Commercial
												Description)</label>

											<textarea name="ComDescriptionSpl" class="form-control" rows="2"
												id="ComDescriptionSpl"
												placeholder="Enter Special Notes"><?php echo $RowCIInv['ComDescriptionSpl'] ?></textarea>


										</div>

										<div class="form-group col-lg-12">
											<label for="exampleInputEmail1">Special Notes(Packing List Description)
											</label>

											<textarea name="ComRemarks" class="form-control" rows="2" id="ComRemarks"
												placeholder="Enter Remarks"><?php echo $RowCIInv['ComRemarks'] ?></textarea>


										</div>
									</div>



									<hr>
									</hr>


									<table id="key-datatable" data-paging="false" class="table  wrap w-100">

										<thead class="thead-light">



											<th>Product Description</th>
											<th style="width: 140px;">Specs</th>
											<th>Packing</th>
											<th>Grade</th>
											<th>Cartons</th>
											<th style="width: 140px;">Codes</th>
											<!--<th>Freezing Date</th>
											<th>Consumption Date </th>-->
											<th>ProductionDate</th>
											<th>Best Before Date</th>
											<!-- <th >Actions</th> -->

										</thead>


										<tbody>
											<?php
											$AryPoGradeID = array();
											while ($RowCIGradeEdit = $StmtCIGradeEdit->fetch_array()) {

												$AryPoGradeID[] = $PoGradeID = $RowCIGradeEdit['ID'];
												$CIDescription = $RowCIGradeEdit['CIDescription'];
												if ($CIDescription == '') {
													$Variety = $RowCIGradeEdit['Variety'];
												} else {
													$Variety = $CIDescription;
												}


												$Specfications = $RowCIGradeEdit['Specfications'];

												?>
												<tr>
													<td>

														<textarea name="CIDescription" class="form-control"
															id="CIDescription<?php echo $PoGradeID; ?>"
															placeholder="Enter Product Description"><?php echo $Variety ?></textarea>
													</td>

													<td>
														<textarea name="Specfications" style="width: 140px;"
															class="form-control" id="Specfications<?php echo $PoGradeID; ?>"
															placeholder="Enter Specfications"><?php echo $Specfications ?> </textarea>
													</td>

													<td>
														<?php echo $RowCIGradeEdit['PackingStyle']; ?>
													</td>
													<td>
														<?php echo $RowCIGradeEdit['Grade']; ?>
													</td>
													<td>
														<?php echo $RowCIGradeEdit['Mcs']; ?>
													</td>



													<!--<td > <select required class="form-control select2" name="PackingStyleID" id="PackingStyleID<?php echo $PoGradeID; ?>" >
				  <option value="">Select </option>
				   <?php foreach ($ArrayPacking as $RowPacking) { ?>
					 <option <?php if ($RowCIGradeEdit['PackingStyleID'] == $RowPacking['PCKID']) { ?> selected=="selected" <?php } ?>
					 value="<?php echo $RowPacking['PCKID'] ?>"><?php echo $RowPacking['PackingStyle'] . ' - ' . $RowPacking['GrossWeight'] . '(GW)'; ?></option>


				 <?php } ?>
				  </select> </td>-->

													<!--<td > <select class="form-control select2" name="Grade" id="Grade<?php echo $PoGradeID; ?>" >
					<option value="">Select </option>
					 <?php foreach ($ARY_OPTIONS as $value) {
						 if ($value['TYPE'] == 'GRADES') {
							 $OPTIONS = $value['OPTIONS'];
							 $VALUE = $value['VALUE']; ?>

					<option value="<?php echo $VALUE ?>" <?php if ($VALUE == $RowCIGradeEdit['Grade'])
						   echo 'selected="selected"'; ?>><?php echo $OPTIONS; ?></option>
					<?php }
					 } ?>
				</select> </td>

<td > <input  type="number" step="any" class="form-control"  name="Mcs" id="Mcs<?php echo $PoGradeID; ?>"
				  placeholder="Enter Mcs" value="<?php echo $RowCIGradeEdit['Mcs']; ?>"> </td>
	-->
													<td> <input type="text" class="form-control" name="Codes"
															style="width: 140px;" id="Codes<?php echo $PoGradeID; ?>"
															placeholder="Enter Codes"
															value="<?php echo $RowCIGradeEdit['Codes']; ?>"> </td>

													<!--<td> <input type="date" onkeydown="return false" class="form-control"
															name="FreezingDate" id="FreezingDate<?php //echo $PoGradeID; ?>"
															placeholder=""
															value="<?php //echo $RowCIGradeEdit['FreezingDate']; ?>"> </td>

													<td> <input type="date" onkeydown="return false" class="form-control"
															name="ConsumptionDate"
															id="ConsumptionDate<? php // echo $PoGradeID; ?>" placeholder=""
															value="<?php //echo $RowCIGradeEdit['ConsumptionDate']; ?>"> </td>-->

													<td> <input type="date" onkeydown="return false" class="form-control"
															name="ProductionDate"
															id="ProductionDate<?php echo $PoGradeID; ?>" placeholder=""
															value="<?php echo $RowCIGradeEdit['ProductionDate']; ?>"> </td>

													<td> <input type="date" onkeydown="return false" class="form-control"
															name="BestBeforeDate"
															id="BestBeforeDate<?php echo $PoGradeID; ?>" placeholder=""
															value="<?php echo $RowCIGradeEdit['BestBeforeDate']; ?>"> </td>

													<!-- <td > </td>	 -->





												</tr>

											<?php } ?>


										</tbody>

									</table>
									<div class="col-xs-12 box-footer" align="right">
										<button type="button" id="close" name="close" class="btn btn-danger">Close
										</button>
										<?php if(!$isCommercialInvoiceLocked){ ?>
										<button class="btn  btn-success Edit" data-id="<?php echo $PoGradeID; ?>"
											data-toggle="tooltip" data-placement="top" title="" value=""
											data-original-title="Edit">Save</button>



										<?php }  if($CommercialCount!=0 && !$isCommercialInvoiceLocked) { ?>

											<button data-id="<?php echo $_GET["PIID"] ?>" style="width:100px;" class="btn btn-warning btnDelete">
													Delete</button>
										<?php } ?>

									</div>
								</div>
							</div>

						</div>
					</div>

				</div> <!-- end row -->

			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
	</div>
	<!-- end row-->

	<?php include($IncludesPath . "footer.php"); ?>

	</div>
	<!-- end main content-->
	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>
	<?php //include ($IncludesPath."ShowDataTable.php");?>








	<script>
		$('document').ready(function () {
			showData();
		});


		$('#close').on("click", function (e) {

			window.location.href =  'commercial-invoice'; //'CustomInvoiceList';
			// window.close();
		});


		// function update_click(ClickedId){
		$(document).on('click', '.Edit', function (e) {
			e.preventDefault();
			var InvId = '<?php echo $PoID ?>';
			var CICount = '<?php echo $CICount ?>';
			var AryPoGradeID = <?php echo json_encode($AryPoGradeID); ?>;
			var ClickedId = $(this).data('id');

			var CIDescriptionspl = $('#CIDescriptionspl').val();
			var LcNo = $('#LcNo').val();
			var LcDate = $('#LcDate').val();
			var IssuedBy = $('#IssuedBy').val();
			var ShowBank = $('#ShowBank').val();
			var ShowBLDate = $('#ShowBLDate').val();
			var ShowBLNo = $('#ShowBLNo').val();
			var Discount = $('#Discount').val();
			var Freight = $('#Freight').val();
			var ExchngRte = $('#ExchngRte').val();
			var ComDescriptionSpl = $('#ComDescriptionSpl').val();
			var ComRemarks = $('#ComRemarks').val();
			var Status = $('#Status').val();
			var ComPaymentTerms = $('#ComPaymentTerms').val();
			var ComShippingTerms = $('#ComShippingTerms').val();
			var ComShippingTermExt = $('#ComShippingTermExt').val();
			var DataLoggerPositions = $('#DataLoggerPositions').val();
			var DataLogger = $('#DataLogger').val();
			var BankID = $('#BankID').val();
			var ShowShippingDetails = $('#ShowShippingDetails').val();
			var ContainerWeight = $('#ContainerWeight').val();

			var AryBreakUp = [];

			for (i = 0; i < AryPoGradeID.length; i++) {
				ClickedId = AryPoGradeID[i];

				var PackingStyle = '#PackingStyleID' + ClickedId;
				var GradeID = '#Grade' + ClickedId;
				var McsID = '#Mcs' + ClickedId;
				var CodesID = '#Codes' + ClickedId;
				var CIDescriptionID = '#CIDescription' + ClickedId;
				var FreezingDateID = '#FreezingDate' + ClickedId;
				var ConsumptionDateID = '#ConsumptionDate' + ClickedId;
				var ProductionDateID = '#ProductionDate' + ClickedId;
				var BestBeforeDateID = '#BestBeforeDate' + ClickedId;
				var SpecficationsID = '#Specfications' + ClickedId;


				var PackingStyleID = $(PackingStyle + "").val();
				var Grade = $(GradeID + "").val();
				var Mcs = $(McsID + "").val();
				var Codes = $(CodesID + "").val();
				var CIDescription = $(CIDescriptionID + "").val();
				var FreezingDate = $(FreezingDateID + "").val();
				var ConsumptionDate = $(ConsumptionDateID + "").val();
				var ProductionDate = $(ProductionDateID + "").val();
				var BestBeforeDate = $(BestBeforeDateID + "").val();
				var Specfications = $(SpecficationsID + "").val();



				var jsonData = {};

				jsonData['CIGradeID'] = ClickedId;
				jsonData['PackingStyleID'] = PackingStyleID;
				jsonData['Grade'] = Grade;
				jsonData['Mcs'] = Mcs;
				jsonData['Codes'] = Codes;
				jsonData['FreezingDate'] = FreezingDate;
				jsonData['ConsumptionDate'] = ConsumptionDate;
				jsonData['ProductionDate'] = ProductionDate;
				jsonData['BestBeforeDate'] = BestBeforeDate;
				jsonData['Specfications'] = Specfications;
				jsonData['CIDescription'] = CIDescription;

				AryBreakUp.push(jsonData);


			}

			//alert(Specfications);


			var r = confirm("Are You Sure Do You Want To Update?");
			if (r == true) {
				$.ajax({
					url: 'Ajax/AjaxDelete.php',
					type: 'POST',
					data: {
						'TYPE': 'UPDATECOMMERCIALINVOICE',
						'InvId': InvId,
						'LcNo': LcNo,
						'CICount': CICount,
						'LcDate': LcDate,
						'ShowBank': ShowBank,
						'ShowBLNo': ShowBLNo,
						'ShowBLDate': ShowBLDate,
						'ShowShippingDetails': ShowShippingDetails,
						'IssuedBy': IssuedBy,
						'Discount': Discount,
						'Freight': Freight,
						'ExchngRte': ExchngRte,
						'ComDescriptionSpl': ComDescriptionSpl,
						'Status': Status,
						'ComRemarks': ComRemarks,
						'ComShippingTerms': ComShippingTerms,
						'ComShippingTermExt': ComShippingTermExt,
						'ComPaymentTerms': ComPaymentTerms,
						'DataLogger': DataLogger,
						'DataLoggerPositions': DataLoggerPositions,
						'BankID': BankID,
						'ContainerWeight':ContainerWeight,
						'AryBreakUp': JSON.stringify(AryBreakUp)
					},
					success: function (data) {

						//	  console.log(data);

						var data = JSON.parse(data);
						if (data['sts'] == 'Failed') {
							alert(data['msg']);


						} else {

							alert(data['msg']);

							location.reload();


						}
					}
					/*	  var obj = JSON.parse(JSON.stringify(data));
					 if(obj.error == null){
						 alert("Updated Successfully");
						 window.location.href ='CustomInvoiceList';
						 window.close();
						//showData(data,false);
						//showData(data);
						//location.reload();
					 }else{
						 alert(obj.error);
					 }

						}*/
				});
			} else { }


		});



	</script>

	<script>

		function showData(data) {

			var IsSort = false;
			/*if(sort==false){
				IsSort=false;
			}else{
				IsSort=true;
			}*/
			$("#ShowDataTable").html(data);

			var table = $('#key-datatable').DataTable({
    dom: '<"top-left"f>rt<"bottom"lip><"clear">',

    scrollY: "350px",
    scrollX: true,
    sScrollXInner: "150%",
    paging: true,
    destroy: true,
    fixedColumns: true,
    bSort: IsSort,

    language: {
        lengthMenu: "Show _MENU_ records",
        paginate: {
            previous: "<i class='mdi mdi-chevron-left'>",
            next: "<i class='mdi mdi-chevron-right'>"
        }
    },

    drawCallback: function () {
        $(".dataTables_paginate > .pagination")
            .addClass("pagination-rounded");
    },

    buttons: [],
    bStateSave: true,

    fnStateSave: function (oSettings, oData) {
        localStorage.setItem('key-datatable', JSON.stringify(oData));
    },

    fnStateLoad: function (oSettings) {
        return JSON.parse(localStorage.getItem('key-datatable'));
    }
});



		}

		// $('#key-datatable tbody').on('keydown', 'td', function (e) {

		// 	if (e.keyCode === 37 || e.keyCode === 39) { // Left or Right arrow key
		// 		e.preventDefault();
		// 	}
		// });

		$(document).on("click", ".btnDelete", function() {
			var OrderID = $(this).attr("data-id");
			if (confirm('Are you sure you want to delete?')) {
				$.ajax({
					url: 'Ajax/AjaxDeleteCommercialInv.php',
					type: 'POST',
					data: {                    
						OrderID: OrderID
					},
					success: function (data) {
						console.log(data);
						if (data.Error) {
							alert(data.Error);
						}else{
							alert(data.Msg);
							window.location.href = "commercial-invoice";			
						}                    
					}
				});
			}
		})
	</script>





</body>

</html>
