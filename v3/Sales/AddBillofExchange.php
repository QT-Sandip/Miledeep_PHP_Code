<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$PageTitleFolder = "Sales";
$PageTitle = "Bill of Exchange(LC) Creation";

$SqlBankMaster = "SELECT * FROM `ScrmBankDetailsMaster`  WHERE BankStatus=1 and  ADCode!=''";
$StmtBankMaster = $iconn->query($SqlBankMaster);


$PIId = $_GET['PIID'];

$SqlBuyerID = "SELECT ConsigneeID,BuyerID FROM ScrmPIData WHERE PoID='$PIId'";
$StmtBuyerID = $iconn->query($SqlBuyerID);
$RowBuyerID = $StmtBuyerID->fetch_array();

if ($RowBuyerID['ConsigneeID'] != '') {

	$ConsigneeID = $RowBuyerID['ConsigneeID'];

} else {
	$ConsigneeID = $RowBuyerID['BuyerID'];

}

$SqlCInvoice = "SELECT CInvNo,ConsigneeName,ConsigneeAddress ,LcStatus
		FROM `ScrmCustomInvoices` as SCI
left join ScrmPIData as SPD	on SPD.PoID=SCI.PiID	
left join ScrmPaymentTerms as SPT	on SPT.ID=SPD.PaymentTerms	
left join ScrmConsigneeDetails as SCD on SCD.ConID=$ConsigneeID	
WHERE PiID='$PIId'";

//ECHO $SqlCInvoice; EXIT;
$StmtCInvoice = $iconn->query($SqlCInvoice);
$RowCInvoice = $StmtCInvoice->fetch_array();
$Status = $RowCInvoice['LcStatus'];



$SqlCount = "SELECT * FROM `ScrmBillOfExchange` WHERE PIID='$PIId'";
$StmtCount = $iconn->query($SqlCount);
$Count = mysqli_num_rows($StmtCount);
$RowBillofExchage = $StmtCount->fetch_array();
$ToAddress = $RowBillofExchage['ToAddress'];
if ($ToAddress == '') {

	$ToAddress = $RowCInvoice['ConsigneeName'] . $RowCInvoice['ConsigneeAddress'];
}


extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {
	if ($Count == 0) {
		$SqlInsert = "INSERT INTO `ScrmBillOfExchange`(`PIID`,`CreditNo`,`CreditDate`,`DraftDate`,
		`ExchangeInvoicevalue`,`IssuedBy`,`ToAddress`, `BankID`, `BankDate`, `AddOnPoint`,`Status`) 
		VALUES  ('$PIId','$CreditNo','$CreditDate','$DraftDate','$ExchangeInvoicevalue','$IssuedBy','$ToAddress',
		'$BankID','$BankDate','$AddOnPoint','$Status')";
		$StmtInsert = $iconn->query($SqlInsert);

		if ($StmtInsert) {
			legacyAuditLogInsertById('ScrmBillOfExchange', 'PIID', $PIId, $_POST, [
				'ReferenceNo' => $PIId,
				'Remarks' => 'Bill of exchange created',
			], 'Sales');
			echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "NegotiationList.php";
        </script>';
		}

	} else {
		$oldBillData = legacyAuditFetchRowById('ScrmBillOfExchange', 'PIID', $PIId, 'Sales');
		$SqlUpdate = "UPDATE `ScrmBillOfExchange` SET `CreditNo`='$CreditNo',
			`CreditDate`='$CreditDate',`DraftDate`='$DraftDate',`ExchangeInvoicevalue`='$ExchangeInvoicevalue',`IssuedBy`='$IssuedBy',`ToAddress`='$ToAddress' 
			,`BankID`='$BankID',`BankDate`='$BankDate',`AddOnPoint`='$AddOnPoint' 
			WHERE `PIID`='$PIId'";
		$StmtUpdate = $iconn->query($SqlUpdate);


		if ($StmtUpdate) {
			legacyAuditLogUpdateById('ScrmBillOfExchange', 'PIID', $PIId, $oldBillData, $_POST, [
				'ReferenceNo' => $PIId,
				'Remarks' => 'Bill of exchange updated',
			], 'Sales');

			echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "NegotiationList.php";
        </script>';

		}

	}

}

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



					<?php include($IncludesPath . "page-title.php"); ?>
					<?php include "PopUpTable.php"; ?>




					<!-- <div class="row">
							<div class="col-12">
								<div class="card"> -->
					<h3 align="LEFT"><B>Bill of Exchange Details</B></h3>

					<form role="form" method="post">
						<div class="card">
							<div class="card-body">


								<?php
								if ($Status != 1) { ?>
									<div class="row">

										<div class="form-group col-sm-12">
											<?php echo '<span style="color: red; font-size: 14px;">' . '* Fileds are disabled because  Non Lc payment mode is selected  in Proforma invoice</span>'; ?>
										</div>
									</div>


								<?php } ?>

								<div class="row">


									<div class="form-group col-sm-3">
										<label for="exampleInputEmail1">Custom Inv No</label>
										<input readonly type="text" class="form-control" name='CInvNo' id="CInvNo"
											value="<?php echo $RowCInvoice['CInvNo']; ?>">

									</div>




									<div class="form-group col-lg-3">
										<label for="exampleInputEmail1">Draft Date</label>
										<input type="date" class="form-control" name='DraftDate' id="DraftDate"
											placeholder="Enter Draft Date"
											value="<?php echo $RowBillofExchage['DraftDate'] ?>">

									</div>

									<!-- <div class="form-group col-lg-3" >
				  <label for="exampleInputEmail1">Invoice Value</label>
				<input  type="text" class="form-control" name='ExchangeInvoicevalue' id="ExchangeInvoicevalue" 
				  placeholder="Enter Invoice Value" value="<?php //echo $RowBillofExchage['ExchangeInvoicevalue']?>">		
		
				</div> -->
									<?php if ($Status == 1) { ?>





										<div class="form-group col-lg-4">
											<label for="exampleInputEmail1">To Address(LC)</label>

											<textarea name="ToAddress" class="form-control" rows="4" cols="50"
												id="ToAddress"
												placeholder="Enter To Address"><?php echo $RowBillofExchage['ToAddress'] ?></textarea>


										</div>

										<div class="form-group col-lg-4">
											<label for="exampleInputEmail1">Issued By</label>

											<textarea name="IssuedBy" class="form-control" rows="4" cols="50" id="IssuedBy"
												placeholder="Enter Issued By"><?php echo $RowBillofExchage['IssuedBy'] ?></textarea>


										</div>

									<?php } else { ?>


										<div class="form-group col-lg-4">
											<label for="exampleInputEmail1">To Address(NON-LC)</label>

											<textarea name="ToAddress" class="form-control" rows="4" cols="50"
												id="ToAddress"
												placeholder="Enter To Address"><?php echo $ToAddress ?></textarea>


										</div>

										<div class="form-group col-sm-4">
											<label for="exampleInputEmail1">Issued By</label>
											<input readonly type="text" class="form-control" name='IssuedBy' id="IssuedBy"
												placeholder="Enter , for next line " value="">
										</div>



									<?php } ?>

								</div>

							</div>

						</div>

						<h3 align="LEFT"><B> Bank Letters</B></h3>

						<div class="card">


							<div class="card-body">
								<div class="row">


									<div class="form-group col-sm-3">
										<label for="exampleInputEmail1">Bank(AD Code)</label>

										<select class="form-control select2" name="BankID" id="BankID">
											<option value=" ">Select</option>

											<?php while ($RowBankMaster = $StmtBankMaster->fetch_array()) {
												$BankName = $RowBankMaster['BankName'];
												$BankID = $RowBankMaster['ID'];
												?>

												<option value="<?php echo $BankID ?>" <?php if ($BankID == $RowBillofExchage['BankID'])
													   echo 'selected="selected"'; ?>>
													<?php echo $BankName . ' - ' . $RowBankMaster['ADCode']; ?>
												</option>
											<?php } ?>
											<select>

									</div>

									<div class="form-group col-sm-3">
										<label for="exampleInputPassword1">Bank Letter Date</label>
										<input type="date" onkeydown="return false"
											value="<?PHP echo $RowBillofExchage['BankDate']; ?>" class="form-control"
											name='BankDate' id="BankDate" placeholder="Enter Bank Letter Date">
									</div>

									<!-- <div class="form-group col-sm-3">
						  <label for="exampleInputPassword1"> SB Value(US$)</label>
						  <input  type="text" class="form-control" name='SBValue' 
						  id="SBValue" placeholder="Enter SB Value in US$" value="<?php //echo $RowEIRBank['SBValue'];?>">
						</div> -->



									<button class="btn btn-warning waves-effect waves-light" data-toggle="popover"
										data-trigger="focus" data-id="<?php echo $row['PoID']; ?> " id="BtnPopUpBulk"
										value=" <?php echo $row['PoID'] ?>"> Documents to be Submitted
									</button>




									<div class="form-group col-lg-12">
										<label for="exampleInputEmail1">Add On Point</label>

										<textarea name="AddOnPoint" class="form-control" rows="4" id="AddOnPoint"
											placeholder="Enter 1st point after insert , Enter 2nd point"><?php echo $RowBillofExchage['AddOnPoint'] ?></textarea>


									</div>

								</div>



								<div class="col-xs-12 box-footer" align="right">
									<a target="" href="NegotiationList.php" class="btn btn-danger  "> Close</a>

									<button type="submit" name='submit' class="btn btn-primary">Submit</button>






					</form>
					<!-- end col -->

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



</body>
<script>

	$(document).on('click', '#BtnPopUpBulk', function (e) {
		e.preventDefault();
		//var YEAR=$('#PoRefNumber').val();
		var PoId = '<?php echo $PoRefNumber; ?>';
		//alert(PoId);


		$.ajax({
			url: 'Ajax/AjCertificatesList.php',
			type: 'POST',
			data: { 'PoId': PoId },
			success: function (data) {


				$("#PopUpTable").html(data);
				//$("#myModal").setTitle('Documents List');
				$('#Update').hide();
				$('#myModal').modal('show');
			}
		});
		//}
	});


	$(document).on('click', '.StatusBulkUpdate', function (e) {
		e.preventDefault();
		var PoId = '<?php echo $PIId; ?>';
		var Count = $(this).val();




		var OriginalIDs = [];
		var CopiesIDs = [];
		var AryData = [];

		for (var i = 1; i <= Count; i++) {
			var DocDataId = '#DocId' + i;
			var OriginalID = '#Original' + i;
			var CopyID = '#Copies' + i;

			var DocId = $(DocDataId).val();
			var Copy = $(CopyID).val();
			var Original = $(OriginalID).val();
			//alert(DocId);

			if (Original != '' || Copy != '') {
				var Data = DocId + '&' + Original + '&' + Copy;
				AryData.push(Data);
				OriginalIDs.push(Original);
				CopiesIDs.push(Copy);
			}

		}

		var jsonData = JSON.stringify(AryData);
		/*if(Status==''){
			alert('Please Select Status');
			return;	
		}
		if(SelectedbulkInvId==''){
			alert('Please Select Invoices');
			return;	
		}*/



		$.ajax({
			type: 'POST',
			dataType: "json",
			url: 'Ajax/AjLCDocListSelect.php',
			data: { 'Data': jsonData, 'PoId': PoId },


			success: function (response) {
				response = JSON.stringify(response);
				var data = JSON.parse(response);
				if (data['Error'] == null) {
					$('#myModal').modal('hide');
					alert(data['Msg']);
					//location.reload();
				} else {
					alert(data['Error']);
					$('#myModal').modal('hide');
				}
			}
		});


	});

</script>

</html>
