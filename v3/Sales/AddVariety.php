<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


$PageTitleFolder = "Master";
$PageTitle = "Add Variety ";
$AccessType = $_SESSION['AccessType'];
$ClientConfigData = (array) $_SESSION["ClientConfigData"];
// echo json_encode($ClientConfigData, JSON_PRETTY_PRINT);exit;

$VarietyID = $_GET['id'];

$SqlVariety = "SELECT * FROM `ScrmProductList`";
$StmtVariety1 = $iconn->query($SqlVariety);


$SqlPIVariety = "SELECT * FROM `ScrmPIVarietyList` WHERE VarietyID='$VarietyID'";
$StmtPIVariety = $iconn->query($SqlPIVariety);
$RowPIVariety = $StmtPIVariety->fetch_array();

$OurSupcCode = $RowPIVariety['DeviSUPC'];

$SqlSpecies = "SELECT * FROM `ScrmSpeciesMaster`  WHERE Status=1  ORDER BY SortOrder asc";
$StmtSpecies = $iconn->query($SqlSpecies);


$SqlFreezingType = "SELECT * FROM `ScrmFreezingTypeMaster`  WHERE Status=1 ORDER BY SortOrder asc";
$StmtFreezingType = $iconn->query($SqlFreezingType);

$SqlPreparation = "SELECT * FROM `ScrmPreparationMaster`  WHERE Status=1  ORDER BY SortOrder asc";
$StmtPreparation = $iconn->query($SqlPreparation);


$SqlGlazeType = "SELECT * FROM `ScrmGlazeTypeMaster` where Status=1 ORDER BY SortOrder asc";
$StmtGlazeType = $iconn->query($SqlGlazeType);


$SqlTreatments = "SELECT * FROM `ScrmTreatmentsMaster` where Status=1 ORDER BY SortOrder asc";
$StmtTreatments = $iconn->query($SqlTreatments);


$SqlBrands = "SELECT * FROM `ScrmBrandsMaster` where Status=1";
$Brands = $iconn->query($SqlBrands);


$SqlPackingStyles = "SELECT * FROM `AdminPackingStyles` WHERE Status=1";
$PackingStyles = $iconn->query($SqlPackingStyles);


$SqlGrades = "SELECT * FROM `ProductionGrades` ";
$Grades = $iconn->query($SqlGrades);


$SqlConsigneeDetails = "SELECT * FROM `ScrmConsigneeDetails` WHERE Status=1 ";
$ConsigneeDetails = $iconn->query($SqlConsigneeDetails);

$TotalInvoices = 0;

if ($VarietyID != '') {
	// $sqlInvCount = "SELECT COUNT(*) as TotalInvoices FROM `ScrmPoGradeData` WHERE VarietyID=$VarietyID"; 
	$sqlInvCount = "
    SELECT COUNT(DISTINCT SPGD.InvId) AS TotalInvoices
    FROM ScrmPoGradeData AS SPGD
    INNER JOIN ScrmPIData AS SPD 
        ON SPD.PoID = SPGD.InvId
    WHERE SPGD.VarietyID = $VarietyID
";

$stmtInvCount = $iconn->query($sqlInvCount);
$RowInvCount = $stmtInvCount->fetch_array();
$TotalInvoices = $RowInvCount['TotalInvoices'];
	// $ProformaInvoiceNo=$RowInvCount['ProformaInvoiceNo'];

}

$allPackingStyles = [];
while ($row = $PackingStyles->fetch_array()) {
	$allPackingStyles[] = $row;
}


extract($_POST);
extract($_GET);

function checkEmpty($Value)
{
	if ($Value == '') {
		$Value = '';
	} else {
		$Value = '_' . $Value;
	}
	return $Value;
}

if (isset($_POST['submit'])) {


	$SqlVariety = "SELECT * FROM `ScrmProductList` where ShortName='$ShortName'";
	$StmtVariety = $iconn->query($SqlVariety);
	$RowVariety = $StmtVariety->fetch_array();
	$ShortName = $RowVariety['ShortName'];
	$FullName = $RowVariety['FullName'];
	$ProductID = $RowVariety['ProductID'];





	$ProductFormm = checkEmpty($ShortName);
	$FreezingTypee = checkEmpty($FreezingType);
	$PreparationTypee = checkEmpty($PreparationType);
	$GlazePercc = checkEmpty($GlazePerc);
	$Glazee = checkEmpty($Glaze);
	$Treatmentt = checkEmpty($Treatment);


	// $ProductDescription=$Species.$ProductFormm.$FreezingTypee.$PreparationTypee.$GlazePercc.$Glazee.$Treatmentt; 

	if ($VarietyID == '') {

		$SqlItemCount = "SELECT count(*) as Count FROM `ScrmPIVarietyList` WHERE TypeProduct='$TypeProduct' and ShortName='$ShortName' 
		and Glaze='$Glaze' and GlazePerc='$GlazePerc' and Treatment='$Treatment' and Grade='$Grade' and FreezingType='$FreezingType' 
		and ProductID='$ProductID' and ProductDescription='$ProductDescription' and Species='$Species' 
		and PreparationType='$PreparationType' and BrandName='$BrandName' and GlazeStatus='$GlazeStatus' 
		and PackingStyle='$PackingStyle'";

		//and UltimateConsignee='$UltimateConsignee'
		$StmtItemCount = $iconn->query($SqlItemCount);
		$RowItemCount = $StmtItemCount->fetch_array();
		$ItemCount = $RowItemCount['Count'];

		if ($ItemCount == 0 && $BuyerSUPC != '') {
			$SqlBuyerCount = "SELECT count(*) as Count FROM `ScrmPIVarietyList` WHERE BuyerSUPC='$BuyerSUPC'";
			$StmtBuyerCount = $iconn->query($SqlBuyerCount);
			$RowBuyerCount = $StmtBuyerCount->fetch_array();
			$BuyerCount = $RowBuyerCount['Count'];
		}


		if ($ItemCount != 0) {
			echo '<script>alert("Variety Already exists")</script>';
		} else if ($BuyerCount != 0) {
			echo '<script>alert("Buyer Code Already exists")</script>';
		} else {
			$SqlInsert = "
				INSERT INTO `ScrmPIVarietyList`(`TypeProduct`,
				`ShortName`, `FullName`, `Category`, `Status`, `BuyerID`, `ItemCode`,
				`Glaze`, `GlazePerc`, `Treatment`, `Grade`, `FreezingType`, `ProductID`,
				`ProductDescription`,`Species`, `PreparationType`,
				`BrandName`, `GlazeStatus`, `PackingStyle`, `UltimateConsignee`,
				`BuyerProdDescription`, `BuyerSUPC`, `DeviSUPC`, `HasSauce`, `SaucePackingStyle`, `ShrimpPackingStyle`) VALUES 
				('$TypeProduct','$ShortName','$FullName'
				,'$Category','1','$BuyerID','$ItemCode','$Glaze',
				'$GlazePerc','$Treatment','$Grade','$FreezingType',
				'$ProductID','$ProductDescription','$Species',
				'$PreparationType','$BrandName','$GlazeStatus','$PackingStyle',
				'$UltimateConsignee','$BuyerProdDescription','$BuyerSUPC','$DeviSUPC', 
				'$HasSauce','$SaucePackingStyle', '$ShrimpPackingStyle')";
			$StmtInsert = $iconn->query($SqlInsert);
		}


		if ($StmtInsert) {
			$insertedVarietyId = $iconn->insert_id;
			legacyAuditLogInsertById('ScrmPIVarietyList', 'VarietyID', $insertedVarietyId, $_POST, array(
				'ReferenceNo' => $insertedVarietyId,
				'Remarks' => 'Variety created',
			), 'Sales');
			//header('Location: VarietyList');
			header('Location: variety-list');
		}
	} else {
		$SqlItemCount = "SELECT count(*) as Count FROM `ScrmPIVarietyList` WHERE 
			Species='$Species' 
			and ProductID='$ProductID' 
			and FreezingType='$FreezingType'
			and TypeProduct='$TypeProduct'
			and PreparationType='$PreparationType'
			and GlazeStatus='$GlazeStatus'
			and GlazePerc='$GlazePerc'
			and Glaze='$Glaze'
			and Treatment='$Treatment' 
			and BrandName='$BrandName' 
			and Grade='$Grade' 
			and PackingStyle='$PackingStyle'
			and ShortName='$ShortName'   
			and ProductDescription='$ProductDescription' 
			and VarietyID !='$VarietyID'";

		//and UltimateConsignee='$UltimateConsignee'
		$StmtItemCount = $iconn->query($SqlItemCount);
		$RowItemCount = $StmtItemCount->fetch_array();
		$ItemCount = $RowItemCount['Count'];

		if ($ItemCount == 0 && $BuyerSUPC != '') {
            $SqlBuyerCount = "SELECT count(*) as Count FROM `ScrmPIVarietyList` WHERE BuyerSUPC='$BuyerSUPC' AND VarietyID !='$VarietyID'";
			$StmtBuyerCount = $iconn->query($SqlBuyerCount);
			$RowBuyerCount = $StmtBuyerCount->fetch_array();
			$BuyerCount = $RowBuyerCount['Count'];
		}



        if ($TotalInvoices > 0) {
            echo '<script>alert("This variety has been used in invoices, this variety can not be edited")</script>';
            $StmtInsert = false;
        } else
		if ($DeviSUPC == '') {
			echo '<script>alert("Invalid Combination ")</script>';
		} else if ($ItemCount != 0) {
			echo '<script>alert("Variety Already exists")</script>';
		} else if ($BuyerCount != 0) {
			echo '<script>alert("Buyer Code Already exists")</script>';
		} else {
			$existingVariety = legacyAuditFetchRowById('ScrmPIVarietyList', 'VarietyID', $VarietyID, 'Sales');
			$SqlInsert = "
			UPDATE `ScrmPIVarietyList` set 
			`TypeProduct`='$TypeProduct',
			`ShortName`='$ShortName',
			`FullName`='$FullName',
			`Category`='$Category',
			`BuyerID`='$BuyerID',
			`ItemCode`='$ItemCode',
			`Glaze`='$Glaze',
			`GlazePerc`='$GlazePerc',
			`Treatment`='$Treatment',
			`Grade`='$Grade',
			`FreezingType`='$FreezingType',
			`ProductID`='$ProductID',
			`ProductDescription`='$ProductDescription',
			`Species`='$Species',
			`PreparationType`='$PreparationType',
			`BrandName`='$BrandName', `GlazeStatus`='$GlazeStatus',
			`PackingStyle`='$PackingStyle', `UltimateConsignee`='$UltimateConsignee',
			`BuyerProdDescription`='$BuyerProdDescription', `BuyerSUPC`='$BuyerSUPC',
            `DeviSUPC`='$DeviSUPC',
            `HasSauce`='$HasSauce', `SaucePackingStyle`='$SaucePackingStyle', `ShrimpPackingStyle`='$ShrimpPackingStyle'
            where  VarietyID ='$VarietyID'";
			$StmtInsert = $iconn->query($SqlInsert);
		}

		if ($StmtInsert) {
			legacyAuditLogUpdateById('ScrmPIVarietyList', 'VarietyID', $VarietyID, $existingVariety, $_POST, array(
				'ReferenceNo' => $VarietyID,
				'Remarks' => 'Variety updated',
			), 'Sales');
			//header('Location: VarietyList');
			header('Location: variety-list');
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

					<?php
					if ($Status != '') {
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

					<?php include($IncludesPath . "page-title.php");
					include "PopUpTable.php"; ?>




					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">


											<div class="form-group col-sm-6">
												<label for="exampleInputEmail1">Our SUPC<span style="color:red">*</span></label>
												<input readonly style="background: gray; color: white;" required type="text" class="form-control" name='DeviSUPC' id="DeviSUPC" placeholder="" value="<?php echo $RowPIVariety['DeviSUPC'] ?>">
												<small id="supcRangeMessage" class="form-text" style="display:none;"></small>

											</div>

											<div class="form-group col-sm-6">
												<label for="exampleInputEmail1">Buyer SUPC</label>
												<input type="text" class="form-control" name='BuyerSUPC' id="BuyerSUPC" placeholder="Enter Buyer SUPC" value="<?php echo $RowPIVariety['BuyerSUPC'] ?>">

											</div>



										</div>
										<HR>
										</HR>

										<div class="row">


											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Species<span style="color:red">*</span></label>
												<select required class="form-control select2" name="Species" id="Species">
													<option value=""> Select </option>
													<?php while ($RowSpecies = $StmtSpecies->fetch_array()) { ?>

														<option <?php if ($RowSpecies['ShortCode'] == $RowPIVariety['Species']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowSpecies['ShortCode']; ?>"><?php echo $RowSpecies['SpeciesName']; ?></OPTION>

													<?php } ?>
												</select>

											</div>



											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Product Form<span style="color:red">*</span></label>
												<select required class="form-control select2" name="ShortName" id="ShortName">
													<option value=""> Select </option>
													<?php while ($RowVariety = $StmtVariety1->fetch_array()) { ?>

														<option <?php if ($RowVariety['ShortName'] == $RowPIVariety['ShortName']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowVariety['ShortName']; ?>"><?php echo $RowVariety['ShortName']; ?></OPTION>

													<?php } ?>
												</select>

											</div>


											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Freezing Type<span style="color:red">*</span></label>
												<select required class="form-control select2" name="FreezingType" id="FreezingType">
													<option value=""> Select </option>
													<?php while ($RowFreezingType = $StmtFreezingType->fetch_array()) { ?>

														<option <?php if ($RowFreezingType['ShortCode'] == $RowPIVariety['FreezingType']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowFreezingType['ShortCode']; ?>"><?php echo $RowFreezingType['FreezingTypeName']; ?></OPTION>

													<?php } ?>
												</select>

											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Type of Product<span style="color:red">*</span></label>
												<select required class="form-control select2" name="TypeProduct" id="TypeProduct">
													<option value=""> Select</option>
													<option <?php if ($RowPIVariety['TypeProduct'] == 'RAW') { ?> selected="selected" <?php } ?> value="RAW">RAW</option>
													<option <?php if ($RowPIVariety['TypeProduct'] == 'COOKED') { ?> selected="selected" <?php } ?> value="COOKED">COOKED</option>
													<option <?php if ($RowPIVariety['TypeProduct'] == 'BLANCHED') { ?> selected="selected" <?php } ?> value="BLANCHED">BLANCHED</option>
												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Preparation<span style="color:red">*</span></label>
												<select required class="form-control select2" name="PreparationType" id="PreparationType">
													<option value=""> Select </option>
													<?php while ($RowPreparation = $StmtPreparation->fetch_array()) { ?>

														<option <?php if ($RowPreparation['ShortCode'] == $RowPIVariety['PreparationType']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowPreparation['ShortCode']; ?>"><?php echo $RowPreparation['PreparationName']; ?></OPTION>

													<?php } ?>
												</select>

											</div>





											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Glaze Status<span style="color:red">*</span></label>
												<select required class="form-control select2" name="GlazeStatus" id="GlazeStatus">
													<option value=""> Select</option>
													<option <?php if ($RowPIVariety['GlazeStatus'] == 'Yes') { ?> selected="selected" <?php } ?> value="Yes">Yes</option>
													<option <?php if ($RowPIVariety['GlazeStatus'] == 'No') { ?> selected="selected" <?php } ?> value="No">No</option>
												</select>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Glaze %<span style="color:red">*</span></label>
												<input required type="text" class="form-control" name='GlazePerc' id="GlazePerc" placeholder="Enter Glaze(%)" value="<?php echo $RowPIVariety['GlazePerc'] ?>" <?php if ($RowPIVariety['GlazeStatus'] == 'No' || $RowPIVariety['GlazeStatus'] == '') { ?> disabled <?php } ?>>

											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Glaze Type<span style="color:red">*</span></label>
												<select required class="form-control select2" name="Glaze" id="Glaze">
													<option value=""> Select </option>
													<?php while ($RowGlazeType = $StmtGlazeType->fetch_array()) { ?>

														<option <?php if ($RowGlazeType['ShortCode'] == $RowPIVariety['Glaze']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowGlazeType['ShortCode']; ?>"><?php echo $RowGlazeType['GlazeType']; ?></OPTION>

													<?php } ?>
												</select>

											</div>


											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Treatment<span style="color:red">*</span></label>
												<select required class="form-control select2" name="Treatment" id="Treatment">
													<option value=""> Select </option>
													<?php while ($RowTreatments = $StmtTreatments->fetch_array()) { ?>

														<option <?php if ($RowTreatments['ShortCode'] == $RowPIVariety['Treatment']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowTreatments['ShortCode']; ?>"><?php echo $RowTreatments['TreatmentName']; ?></OPTION>

													<?php } ?>
												</select>

											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Brand<span style="color:red">*</span></label>
												<select required class="form-control select2" name="BrandName" id="BrandName">
													<option value=""> Select </option>
													<?php while ($RowBrands = $Brands->fetch_array()) { ?>

														<option <?php if ($RowBrands['BrandID'] == $RowPIVariety['BrandName']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowBrands['BrandID']; ?>"><?php echo $RowBrands['BrandName']; ?></OPTION>

													<?php } ?>
												</select>

											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Grade<span style="color:red">*</span></label>
												<select required class="form-control select2" name="Grade" id="Grade">
													<option value=""> Select </option>
													<?php while ($RowGrades = $Grades->fetch_array()) { ?>

														<option <?php if ($RowGrades['value'] == $RowPIVariety['Grade']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowGrades['value']; ?>"><?php echo $RowGrades['value']; ?></OPTION>

													<?php } ?>
												</select>

											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Packing Style<span style="color:red">*</span></label>
												<select required class="form-control select2" name="PackingStyle" id="PackingStyle">
													<option value=""> Select </option>
													<?php foreach ($allPackingStyles as $RowPackingStyles) {
														$gross = !empty($RowPackingStyles['GrossWeight']) ? $RowPackingStyles['GrossWeight'] : 0;
														$netWeight = ($RowPackingStyles['TotalSlabs'] * $RowPackingStyles['SlabWeight']) * ($RowPackingStyles['Uom'] != 'KGS' ? $RowPackingStyles['MFactor'] : 1);
													?>
														<option
															value="<?php echo $RowPackingStyles['PCKID']; ?>"
															<?php if ($RowPackingStyles['PCKID'] == $RowPIVariety['PackingStyle']) echo 'selected'; ?>>
															<?php echo str_replace(' ', '', $RowPackingStyles['PackingStyle']) . " (G-" . $gross . " N-" . $netWeight . " Kgs)"; ?>
														</option>
													<?php } ?>

												</select>

											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Group Company</label>
												<select class="form-control select2" name="UltimateConsignee" id="UltimateConsignee">
													<option value=""> Select </option>
													<?php while ($RowConsigneeDetails = $ConsigneeDetails->fetch_array()) { ?>

														<option <?php if ($RowConsigneeDetails['ConID'] == $RowPIVariety['UltimateConsignee']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowConsigneeDetails['ConID']; ?>"><?php echo $RowConsigneeDetails['ConsigneeName']; ?></OPTION>

													<?php } ?>
												</select>

											</div>

										<?php if ($ClientConfigData['HasSauce'] == 1) { ?>
											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Has Sauce<span style="color:red">*</span></label>
												<select required class="form-control select2" name="HasSauce" id="HasSauce">
													<option value=""> Select</option>
													<option <?php if ($RowPIVariety['HasSauce'] == 'Yes') { ?> selected="selected" <?php } ?> value="Yes">Yes</option>
													<option <?php if ($RowPIVariety['HasSauce'] == 'No') { ?> selected="selected" <?php } ?> value="No">No</option>
												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Sauce Packing Style<span style="color:red">*</span></label>
												<select required class="form-control select2" name="SaucePackingStyle" id="SaucePackingStyle">
													<option value=""> Select </option>
													<?php foreach ($allPackingStyles as $RowPackingStyles) {
														$gross = !empty($RowPackingStyles['GrossWeight']) ? $RowPackingStyles['GrossWeight'] : 0;
														$netWeight = ($RowPackingStyles['TotalSlabs'] * $RowPackingStyles['SlabWeight']) * ($RowPackingStyles['Uom'] != 'KGS' ? $RowPackingStyles['MFactor'] : 1);
													?>
														<option
															value="<?php echo $RowPackingStyles['PCKID']; ?>"
															<?php if ($RowPackingStyles['PCKID'] == $RowPIVariety['SaucePackingStyle']) echo 'selected'; ?>>
															<?php echo str_replace(' ', '', $RowPackingStyles['PackingStyle']) . " (G-" . $gross . " N-" . $netWeight . " Kgs)"; ?>
														</option>
													<?php } ?>

												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Shrimp Packing Style<span style="color:red">*</span></label>
												<select required class="form-control select2" name="ShrimpPackingStyle" id="ShrimpPackingStyle">
													<option value=""> Select </option>
													<?php foreach ($allPackingStyles as $RowPackingStyles) {
														$gross = !empty($RowPackingStyles['GrossWeight']) ? $RowPackingStyles['GrossWeight'] : 0;
														$netWeight = ($RowPackingStyles['TotalSlabs'] * $RowPackingStyles['SlabWeight']) * ($RowPackingStyles['Uom'] != 'KGS' ? $RowPackingStyles['MFactor'] : 1);
													?>
														<option
															value="<?php echo $RowPackingStyles['PCKID']; ?>"
															<?php if ($RowPackingStyles['PCKID'] == $RowPIVariety['ShrimpPackingStyle']) echo 'selected'; ?>>
															<?php echo str_replace(' ', '', $RowPackingStyles['PackingStyle']) . " (G-" . $gross . " N-" . $netWeight . " Kgs)"; ?>
														</option>
													<?php } ?>

												</select>
											</div>
										<?php } ?>

											<div class="form-group col-sm-4">
												<label for="exampleInputEmail1">Product Description</label>
												<input style="background: gray; color: white;" type="text" class="form-control" name='ProductDescription' id="ProductDescription" placeholder="" value="<?php echo $RowPIVariety['ProductDescription'] ?>">

											</div>

											<div class="form-group col-lg-12">
												<label for="exampleInputEmail1">Buyer Product Description <span style="color:red">*</span></label>

												<textarea name="BuyerProdDescription" class="form-control" rows="2" cols="50" id="BuyerProdDescription" required placeholder="Enter Buyer Product Description"><?php echo $RowPIVariety['BuyerProdDescription'] ?></textarea>
											</div>


										</div>

										<div class="row">
											<div class="col-lg-12">
												<div style="margin: 8px 0 20px; padding: 18px; border: 1px solid #d9e2ef; border-radius: 10px; background: linear-gradient(135deg, #f8fbff 0%, #ffffff 58%, #f5fff9 100%); box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);">
													<div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px;">
														<div>
															<div style="font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #64748b; margin-bottom: 4px;">Reference Guide</div>
															<h6 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Our SUPC Generation Rules</h6>
														</div>
														<span style="display: inline-flex; align-items: center; min-height: 28px; padding: 5px 10px; border-radius: 999px; background: #e8f7ef; color: #137947; font-size: 12px; font-weight: 700;">Auto generated after selection</span>
													</div>

													<div class="row" style="margin-left: -6px; margin-right: -6px;">
														<div class="col-lg-3 col-md-6" style="padding-left: 6px; padding-right: 6px; margin-bottom: 12px;">
															<div style="height: 100%; padding: 14px; border-radius: 8px; border: 1px solid #dbeafe; background: #ffffff;">
																<div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;">BLOCK</div>
																<div style="font-size: 22px; font-weight: 800; color: #1d4ed8; line-height: 1;">1001 ... 1999, 11000 ...</div>
																<div style="font-size: 12px; color: #64748b; margin-top: 8px;">Block products always start with 1.</div>
															</div>
														</div>
														<div class="col-lg-3 col-md-6" style="padding-left: 6px; padding-right: 6px; margin-bottom: 12px;">
															<div style="height: 100%; padding: 14px; border-radius: 8px; border: 1px solid #ccfbf1; background: #ffffff;">
																<div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;">IQF / SIQF</div>
																<div style="font-size: 22px; font-weight: 800; color: #0f766e; line-height: 1;">2001 ... 2999, 21000 ...</div>
																<div style="font-size: 12px; color: #64748b; margin-top: 8px;">IQF/SIQF products always start with 2.</div>
															</div>
														</div>
														<div class="col-lg-3 col-md-6" style="padding-left: 6px; padding-right: 6px; margin-bottom: 12px;">
															<div style="height: 100%; padding: 14px; border-radius: 8px; border: 1px solid #fde68a; background: #ffffff;">
																<div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;">COOKED</div>
																<div style="font-size: 22px; font-weight: 800; color: #b45309; line-height: 1;">3001 ... 3999, 31000 ...</div>
																<div style="font-size: 12px; color: #64748b; margin-top: 8px;">Cooked products always start with 3.</div>
															</div>
														</div>
														<div class="col-lg-3 col-md-6" style="padding-left: 6px; padding-right: 6px; margin-bottom: 12px;">
															<div style="height: 100%; padding: 14px; border-radius: 8px; border: 1px solid #e9d5ff; background: #ffffff;">
																<div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;">BLANCHED</div>
																<div style="font-size: 22px; font-weight: 800; color: #7e22ce; line-height: 1;">4001 ... 4999, 41000 ...</div>
																<div style="font-size: 12px; color: #64748b; margin-top: 8px;">Blanched products always start with 4.</div>
															</div>
														</div>
													</div>

													<div style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-radius: 8px; background: #f8fafc; border: 1px solid #e5e7eb; color: #475569; font-size: 12px;">
														<span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; display: inline-block; flex: 0 0 auto;"></span>
														<span>Code is generated only after Type of Product, Freezing Type and Preparation are selected. First digit is the product family; remaining digits are the running sequence, so 1999 continues as 11000.</span>
													</div>
												</div>
											</div>
										</div>
										<div class="text-center">
											<?php
											if ($TotalInvoices > 0) {
												echo '<p style="color: #FF0000">This variety has been used in " <span style="font-size: 17px;">' . $TotalInvoices . '</span> "  invoices, this variety can not be edited </p>';
											?>
												<button name='view' id="btnView" data-id="<?php echo $VarietyID ?> " data-value="<?php echo $OurSupcCode ?> " class="btn btn-success">Click to View</button>
											<?php
											}
											?>
										</div>
										<div class="col-xs-12 box-footer" align="right">

											<a target="" href="variety-list" class="btn btn-danger  "> Close</a>
											<?php if ($VarietyID == '') { ?>

												<button type="submit" name='submit' id="btnSubmit" class="btn btn-success" disabled>ADD</button>
												<?php // } else if ($AccessType < 3 && $TotalInvoices == 0) { 
												?>

											<?php } ?>
											<button type="submit" name="submit" class="btn btn-success" 
												<?php echo ($TotalInvoices > 0) ? 'disabled' : ''; ?>>
												Update
											</button>




										</div>
									</div>







								</form>
								<!-- end col -->
							</div> <!-- end row -->

						</div> <!-- end card body-->
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

	<script>
		$(document).ready(function() {

			$('#SaucePackingStyle').select2();
			$('#ShrimpPackingStyle').select2();


			// Set value, trigger, then disable

			$('#SaucePackingStyle').trigger('change');
			$('#SaucePackingStyle').prop('disabled', true);

			$('#ShrimpPackingStyle').trigger('change');
			$('#ShrimpPackingStyle').prop('disabled', true);

			var VarietyId = '<?php echo $VarietyID ?>';

			//alert(VarietyId);
			$('#GlazeStatus').change(function() {
				var GlazeStatus = $('#GlazeStatus').val();

				if (GlazeStatus != 'Yes') {
					document.getElementById("GlazePerc").disabled = true;
					$("#GlazePerc").val("0");
				} else {
					document.getElementById("GlazePerc").disabled = false;
					$("#GlazePerc").val("");
				}
				updateDescription();
			});

			$('#HasSauce').change(function() {
				var HasSauce = $('#HasSauce').val();

				if (HasSauce != 'Yes') {
					// Clear selection and disable
					$("#SaucePackingStyle").val("").trigger('change');
					$("#SaucePackingStyle").prop("disabled", true);

					$("#ShrimpPackingStyle").val("").trigger('change');
					$("#ShrimpPackingStyle").prop("disabled", true);
				} else {
					// Enable without clearing
					$("#SaucePackingStyle").prop("disabled", false).trigger('change');

					$("#ShrimpPackingStyle").prop("disabled", false).trigger('change');
				}

				updateDescription();
			});

			// Trigger change on page load (optional)
			$('#HasSauce').trigger('change');


			$('#GlazePerc').change(function() {
				updateDescription();
			});

			$('#Species').change(function() {
				updateDescription();
			});
			$('#ShortName').change(function() {
				updateDescription();
			});
			$('#PreparationType').change(function() {
				updateDescription();

			if (VarietyId != '' && !isCurrentDeviSupcInExpectedRange()) {
				showSupcRangeMessage('Current saved SUPC does not match the selected prefix rule. Suggested code refreshed below.', 'warn');
				getDeviSupcCode();
			}
			});

			$('#Glaze').change(function() {
				updateDescription();
			});

			$('#Treatment').change(function() {
				updateDescription();
			});

			//if(VarietyId==''){
			$('#TypeProduct').change(function() {
				getDeviSupcCode();
			});

			$('#FreezingType').change(function() {
				getDeviSupcCode();
			});
			$('#PreparationType').change(function() {
				getDeviSupcCode();
			});

		});









		//}
	</script>
	<script>
		function getExpectedDeviSupcRange() {
			var TypeProduct = $("#TypeProduct").val();
			var FreezingType = $("#FreezingType").val();
			var prefix = null;

			if (TypeProduct == 'COOKED') {
				prefix = 3;
			} else if (TypeProduct == 'BLANCHED') {
				prefix = 4;
			} else if (FreezingType == 'IQF' || FreezingType == 'SIQF') {
				prefix = 2;
			} else if (FreezingType == 'BLOCK') {
				prefix = 1;
			}

			if (prefix == null) {
				return null;
			}

			return {
				prefix: prefix.toString()
			};
		}

		function isCurrentDeviSupcInExpectedRange() {
			var range = getExpectedDeviSupcRange();
			var codeValue = $('#DeviSUPC').val().toString();
			var code = parseInt(codeValue, 10);

			if (range == null || isNaN(code)) {
				return true;
			}

			return codeValue.charAt(0) == range.prefix && codeValue.length >= 4;
		}

		function showSupcRangeMessage(message, type) {
			var color = type == 'warn' ? '#b45309' : '#137947';
			var background = type == 'warn' ? '#fff7ed' : '#ecfdf5';
			$('#supcRangeMessage')
				.css({ color: color, background: background, padding: '6px 8px', borderRadius: '6px', marginTop: '6px' })
				.text(message)
				.show();
		}

		function getDeviSupcCode() {

			var TypeProduct = $('#TypeProduct').val();
			var FreezingType = $('#FreezingType').val();
			var PreparationType = $('#PreparationType').val();
			var VarietyID = '<?php echo  $VarietyID ?>';
			var OurSupcCode = '<?php echo  $OurSupcCode ?>';

			if (TypeProduct != '' && FreezingType != '' && PreparationType != '') {

			} else {
				return;
			}




			$.ajax({
				url: 'Ajax/AjaxClientCode.php',
				type: 'POST',
				dataType: "json",
				data: {
					'TypeProduct': TypeProduct,
					'FreezingType': FreezingType,
					'PreparationType': PreparationType,
					'VarietyID': VarietyID
				},
				success: function(data) {
					var JsonData = JSON.parse(JSON.stringify(data));

					if (JsonData.Error == null) {
						$("#btnSubmit").removeAttr("disabled", true);
                        $("#DeviSUPC").val(JsonData.Code);
						if (isCurrentDeviSupcInExpectedRange()) {
							showSupcRangeMessage('SUPC is matching the selected prefix rule.', 'ok');
						}
					} else {
						$("#btnSubmit").attr("disabled", true);
						$("#DeviSUPC").val("");
						alert(JsonData.Error);
					}
					//$("#BuyerSUPC").val(data);

				}
			});


		}




		function updateDescription() {
			var Species = $('#Species').val();
			var ShortName = $('#ShortName').val();
			var PreparationType = $('#PreparationType').val();
			var Glaze = $('#Glaze').val();
			var Treatment = $('#Treatment').val();
			var GlazePerc = $('#GlazePerc').val();
			var Desc = Species + '-' + ShortName + '-' + PreparationType;
			var Desc = Species + '-' + ShortName + '-' + PreparationType;
			if (GlazePerc > 0) {
				var Desc = Species + '-' + ShortName + '-' + PreparationType + '-' + GlazePerc + '%' + '' + Glaze + '-' + Treatment;
			} else {
				var Desc = Species + '-' + ShortName + '-' + PreparationType + '-' + Glaze + '-' + Treatment;
			}
			$("#ProductDescription").val(Desc);
		}
	</script>

	<script>
		$(document).on('click', '#btnView', function(e) {
			e.preventDefault();


			var VarietyID = $(this).data('id');
			var OurSupcCode = $(this).data('value');

			// $('#Update').val(PoID);
			showPoPopUp(VarietyID, OurSupcCode);

		});

		function showPoPopUp(VarietyID, OurSupcCode) {

			$.ajax({
				url: 'Ajax/AjaxShowVarietyUsedInvoices.php',
				type: 'POST',
				data: {
					'VarietyID': VarietyID
				},
				success: function(data) {
					$("#Update").hide();

					$('.modal-title').text('Invoices List for SUPC - ' + OurSupcCode);
					$("#PopUpTable").html(data);

					$('#myModal').modal('show');


					var table = $('#key-datatable').DataTable({
						bSort: false,

					});


				}
			});

		}
	</script>
