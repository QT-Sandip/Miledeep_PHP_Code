<?php
$PageTitle = "Edit Reglazing Data";
$PageTitleFolder = "Production";

$Status = $_SESSION['Status'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$LotId = $_GET['LotID'];
$ProdID = $_GET['ProdID'];

// $SqlPoList = "SELECT * FROM `ScrmPIData` WHERE Status=1 order by PoID desc";
$SqlPoList = "SELECT * FROM `ScrmPoData` WHERE PoID not in (SELECT PiID from ScrmCustomInvoices) order by PoID desc";
$StmtPoList = $iconn->query($SqlPoList);

$SqlPacking = "SELECT * FROM `AdminPackingStyles` where Status=1 order by abs(PackingStyle) Asc";
$StmtPacking = $iconn->query($SqlPacking);

$SqlGrades = "SELECT * FROM `ProductionGrades` ORDER BY `ID` ASC";
$StmtGrades = $iconn->query($SqlGrades);

$SqlLots = "SELECT * FROM `RmLotDetatils` where RmReferenceID='$LotId'";
$StmtLots = $iconn->query($SqlLots);

$SqlIqfData = "SELECT * FROM `ProdReglazingData` where ProdID='$ProdID'";
$StmtIqfData = $iconn->query($SqlIqfData);
$RowIqfData = $StmtIqfData->fetch_array();

// while ($RowIqfData = $StmtIqfData->fetch_array()) {
// 	$ArrayStock[] = $RowIqfData;
// }

// $Unit = '';
// $LotNo = '';

// $SIZE = count($ArrayStock);

// if ($SIZE != 0) {
// 	$SIZE = $SIZE - 1;
// 	$Unit = $ArrayStock[$SIZE]['Unit'];
// 	$LotNo = $ArrayStock[$SIZE]['PoID'];
// }


$SqlVAStations = "SELECT * FROM PpcVAStations";
$StmtVAStations = $iconn->query($SqlVAStations);

$SqlProdVariety = "SELECT * FROM `PpcOptions` WHERE DeptID='5'";
$StmtProdVariety = $iconn->query($SqlProdVariety);
$AryOptions = array();
while ($RowOptions = $StmtProdVariety->FETCH_ARRAY()) {
	$AryOptions[] = $RowOptions;
}

$SqlShift = "SELECT * FROM `PpcOptions` WHERE DeptID=4";
$StmtShift = $iconn->query($SqlShift);
$AryShiftOptions = array();
while ($RowShiftOptions = $StmtShift->FETCH_ARRAY()) {
	$AryShiftOptions[] = $RowShiftOptions;
}

$SqlHLVAVarietyList = "SELECT ShortName FROM `ScrmProductList`  WHERE Status='1' GROUP BY ShortName ORDER BY ShortName";
// $SqlHLVAVarietyList = "SELECT ShortName FROM `PpcHLVAVarietyList` WHERE Status='1'";
$StmtHLVAVarietyList = $iconn->query($SqlHLVAVarietyList);

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {

	if (isset($Variety) && $Variety != null && $Variety != '') {
		$SqlPacking = "SELECT * FROM `AdminPackingStyles` WHERE PCKID='$PackingStyleID'";
		$StmtPacking = $iconn->query($SqlPacking);
		$RowPacking = $StmtPacking->fetch_array();
		$PackingStyle = $RowPacking['PackingStyle'];

		$SqlInsert = "UPDATE `ProdReglazingData` SET `Mcs`='$Mcs',`PackingType`='$PackingType',
			`LoosePouches`='$LoosePouches',`Count`='$Count',`Grade`='$Grade',`FreezingType`='$FreezingType',
			`PackingStyleID`='$PackingStyleID',`Variety`='$Variety',
			`ProductionDate`='$ProductionDate',`PoID`='$PoID',`Group`='$Group',
			`Category`='$Category',`Unit`='$Unit',`Shift`='$Shift',`PackingStyle`='$PackingStyle',`ProductionLine`='$ProductionLine',
			`Remarks`='$Remarks',`TubNo`='$TubNo',`NetWeight`='$NetWeight',`Brand`='$Brand' WHERE ProdID='$ProdID'
			";

		$stmt = $iconn->query($SqlInsert);

		if ($stmt) {
			// echo $SqlInsert;
			// exit;
			echo '<script language="javascript" type="text/javascript"> 
					alert("Success");
					window.location = "EditReglazingReport.php?ProdID=' . $ProdID . '"
			</script>';

			echo '<script language="javascript" type="text/javascript">window.close();</script>';
		}
	} else {
		echo '<script language="javascript" type="text/javascript"> 
                alert("Please select Variety");
		</script>';
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

					<?php
					if ($Status != '') {
						//Show Alert 
					?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<i class="mdi mdi-check-all mr-2"></i>
							<?php echo $Status;
							$_SESSION['Status'] = '';	  ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>

					<?php } ?>

					<?php include($IncludesPath . "page-title.php"); ?>




					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Production Date</label>
												<input required type="date" class="form-control" name='ProductionDate' id="ProductionDate" value="<?php echo $RowIqfData['ProductionDate']; ?>" placeholder="">
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Name of Station</label>
												<select class="form-control select2" name="Unit" id="Unit">
													<option value="">Select Station</option>

													<?php while ($DataRow = $StmtVAStations->fetch_array()) {
													?>

														<option <?php if ($RowIqfData['Unit'] == $DataRow['StationID']) { ?>selected=="selected" <?php } ?> value="<?php echo $DataRow['StationID']; ?>">
															<?php echo $DataRow['UnitName']; ?></option>
													<?php    }  ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Dummy/Master Cartons</label>
												<select class="form-control select2" name="PackingType" id="PackingType">
													<option value="">Select</option>

													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'PackingType') {
															$Option = $value['Name'];

													?>
															<option <?php if ($RowIqfData['PackingType'] == $Option) { ?>selected=="selected" <?php } ?> value="<?php echo $Option; ?>">
																<?php echo $Option; ?></option>

													<?php    }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Po Number</label>
												<select class="form-control select2" name="PoID" id="PoID">
													<option value="">Select Po</option>

													<?php while ($RowPoList = $StmtPoList->fetch_array()) { ?>

														<option <?php if ($RowIqfData['PoID'] == $RowPoList['PoID']) { ?>selected=="selected" <?php } ?> value="<?php echo $RowPoList['PoID'] ?>"><?php echo $RowPoList['PoNo'] ?></option>

													<?php  } ?>
												</select>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Variety</label>
												<select class="form-control select2" name="Variety" id="Variety">
													<option value="">Select Variety</option>

													<?php while ($RowHLVAVarietyList = $StmtHLVAVarietyList->FETCH_ARRAY()) {

														$Option = $RowHLVAVarietyList['ShortName'];

													?>
														<option <?php if ($RowIqfData['Variety'] == $Option) { ?>selected="selected" <?php } ?> value="<?php echo $Option; ?>">
															<?php echo $Option; ?></option>

													<?php    } ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Freezing Type</label>
												<select class="form-control select2" name="FreezingType" id="FreezingType">
													<option value="">Select Freezing Type</option>

													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'Freeezing') {
															$Option = $value['Name'];

													?>
															<option <?php if ($RowIqfData['FreezingType'] == $Option) { ?>selected=="selected" <?php } ?> value="<?php echo $Option; ?>">
																<?php echo $Option; ?></option>

													<?php    }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Group</label>
												<select class="form-control select2" name="Group" id="Group">
													<option value="">Select Group</option>

													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'ProductGroup') {
															$Option = $value['Name'];

													?>
															<option <?php if ($RowIqfData['Group'] == $Option) { ?>selected=="selected" <?php } ?> value="<?php echo $Option; ?>">
																<?php echo $Option; ?></option>

													<?php    }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Tub No</label>
												<input required type="text" class="form-control" name='TubNo' id="TubNo" value="<?php echo $RowIqfData['TubNo'] ?>" placeholder="Enter Tub No ">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Brand</label>
												<input required type="text" class="form-control" name='Brand' id="Brand" value="<?php echo $RowIqfData['Brand'] ?>" placeholder="Enter Brand ">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1"> Count</label>
												<input required type="text" class="form-control" name='Count' id="Count" value="<?php echo $RowIqfData['Count'] ?>" placeholder="Enter Count">
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">No.of.Cartons</label>
												<input required type="number" class="form-control" name='Mcs' id="Mcs" value="<?php echo $RowIqfData['Mcs'] ?>" placeholder="Enter number of cartons">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Grade</label>
												<select class="form-control select2" name="Grade" id="Grade">
													<option value="">Select Grade</option>
													<?php while ($RowGrades = $StmtGrades->fetch_array()) { ?>
														<option <?php if ($RowIqfData['Grade'] == $RowGrades['value']) { ?>selected=="selected" <?php } ?> value="<?php echo $RowGrades['value'] ?>"><?php echo $RowGrades['value']; ?></option>
													<?php  } ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Packing Style</label>
												<select required class="form-control select2" name="PackingStyleID" id="PackingStyleID">
													<option value="">Select Packing</option>
													<?php while ($RowPacking = $StmtPacking->fetch_array()) { ?>
														<option <?php if ($RowIqfData['PackingStyleID'] == $RowPacking['PCKID']) { ?>selected=="selected" <?php } ?> value="<?php echo $RowPacking['PCKID'] ?>"><?php echo $RowPacking['PackingStyle']; ?></option>
													<?php  } ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Net Weight</label>
												<input required type="text" class="form-control" name='NetWeight' id="NetWeight" value="<?php echo $RowIqfData['NetWeight'] ?>" placeholder="Enter Net Weight">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Loose Boxes</label>
												<input required type="text" class="form-control" name='LoosePouches' id="LoosePouches" value="<?php echo $RowIqfData['LoosePouches'] ?>" placeholder="Enter Loose Boxes">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Shift</label>
												<select class="form-control select2" name="Shift" id="Shift">
													<option value="">Select Shift</option>

													<?php foreach ($AryShiftOptions as $value) {
														if ($value['Value'] == 'SHIFTS') {
															$Option = $value['Name']; ?>
															<option <?php if ($RowIqfData['Shift'] == $Option) { ?>selected=="selected" <?php } ?> value="<?php echo $Option; ?>">
																<?php echo $Option; ?></option>
													<?php    }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Category</label>
												<select class="form-control select2" name="Category" id="Category">
													<option value="">Select Category</option>

													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'ProductCategory') {
															$Option = $value['Name'];	 ?>
															<option <?php if ($RowIqfData['Category'] == $Option) { ?>selected=="selected" <?php } ?> value="<?php echo $Option; ?>">
																<?php echo $Option; ?></option>
													<?php    }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Line Number</label>
												<select class="form-control select2" name="ProductionLine" id="ProductionLine">
													<option value="">Select Line Number</option>
													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'IQFLine') {
															$Option = $value['Name']; ?>
															<option <?php if ($RowIqfData['ProductionLine'] == $Option) { ?>selected=="selected" <?php } ?> value="<?php echo $Option; ?>">
																<?php echo $Option; ?></option>

													<?php    }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Line Remarks</label>
												<input required type="text" class="form-control" name='Remarks' id="Remarks" value="<?php echo $RowIqfData['Remarks']; ?>" placeholder="Enter Line Remarks">
											</div>

											<div align="right" style="align-self: center" class="col-lg-12">
												<a target="" href="#" onclick="window.close();" class="btn btn-danger  "> Close</a>
												<button type="submit" name='submit' class="btn btn-success"><span class="glyphicon glyphicon-plus"></span>Update</button>
											</div>
								</form>
							</div> <!-- end row -->
						</div>
					</div> <!-- end row -->
				</div>
			</div> <!-- end row -->
		</div> <!-- end card body-->
	</div>
	<!-- end main content-->
	<?php include($IncludesPath . "footer.php"); ?>
	</div>
	<!-- END layout-wrapper -->

	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>

</body>
<script>
	$('document').ready(function() {
		// showData();
		let packingStyleData = null,
			netWeight = 0;

		fetchPackingStyleData();

		$('#PackingStyleID').on('change keyup', function() {
			fetchPackingStyleData();
		});

		$('#Mcs, #LoosePouches').on('change keyup', function() {
			calculateNetWeight();
		});

		function fetchPackingStyleData() {
			let PCKID = $('#PackingStyleID').val();
			if (PCKID != '') {
				$.ajax({
					type: 'POST',
					url: 'Ajax/AjaxCreateStockOptions.php',
					dataType: 'json',
					data: {
						'Type': 'fetchPackingSlabs',
						'PCKID': PCKID,
					},
					success: function(response) {
						packingStyleData = JSON.parse(JSON.stringify(response));
						calculateNetWeight();
					}
				});
			}
		}

		function calculateNetWeight() {
			let mcs = $('#Mcs').val();
			let looseBags = $('#LoosePouches').val();

			if (packingStyleData) {
				netWeight = mcs * packingStyleData['TotalSlabs'] * packingStyleData['SlabWeight'] * packingStyleData['MFactor'];
			}
			if (looseBags > 0) {
				netWeight += looseBags * packingStyleData['SlabWeight'] * packingStyleData['MFactor'];
			}

			$('#NetWeight').val(netWeight.toFixed(3));
		}
	});
</script>

</html>