<?php

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resBap = $resDropdownsAPI->Data->Bap;
$resSpecies = $resDropdownsAPI->Data->Species;
$List = $resDropdownsAPI->Data;
extract($_POST);
extract($_GET);
$TraceId = $_GET['ID'];

if ($TraceId == '') {
	$PageTitle = "Create Traceability";
} else {
	$PageTitle = "Edit Traceability";
}
$PageTitleFolder = "Quality Control";

$sql = "SELECT * FROM `QcTracebilityList` where TraceID='$TraceId' ";
$stmt = $iconn->query($sql);
$RowTrace = $stmt->fetch_array();
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsSplitTracebilityAndPondID = $ArrayClientConfigData['TracebilityIDAndPondIDSeparator'];
$RestrictTracebilityFormFields = $ArrayClientConfigData['RestrictTracebilityFormFields'];

$EnableTracebilityNewFileds = !empty($ArrayClientConfigData['EnableTracebilityNewFileds']) ? json_decode($ArrayClientConfigData['EnableTracebilityNewFileds'], true) : [];
//["FarmName","WaterSpreadArea","CertificateNo","SpeciesName","PreHarvestCertificateDateofIssue","PreHarvestCertificateValidUpto","MpedaElisaScreeningLaboratory","FarmIdentification","APSADA"]

$EditAccess = '';
if(!empty($RestrictTracebilityFormFields) && !empty($_GET['ID'])){
	$EditAccess = 'readonly="readonly"';
}
if (isset($_POST['submit'])) {

	// echo '<script>
	// document.addEventListener("DOMContentLoaded", function () {
	// 	var usedQuantityElement = document.getElementById("UsedQuantity");

	// 		var isResetQty = confirm("Do you want to reset the Used Qty to 0?");

	// 		if (isResetQty) {
	// 			usedQuantityElement.value = 0;
	// 		} else {
	// 			usedQuantityElement.value = 1;
	// 		}
	// 			});
	// 			</script>';
	$InsertColumns = $InsertValues = $updateColumns = '';
	if(!empty($EnableTracebilityNewFileds)){
		$EnableTracebilityNewDBFileds = array_diff($EnableTracebilityNewFileds, ['APSADA']);
		foreach($EnableTracebilityNewDBFileds as $key => $val){
			$InsertColumns .= "`$val`,";
			$InsertValues .= "'" . $$val . "',";
			$updateColumns .= "`$val` = '" . $$val . "',";
		}
		$InsertColumns = ','.rtrim($InsertColumns, ',');
	    $InsertValues = ','.rtrim($InsertValues, ',');
		$updateColumns = ','.rtrim($updateColumns, ',');
	}

	$Bap = $_POST['txtBap'];
	$ID = $_GET['ID'];
	$TraceAry = explode('/', $Tracebility);
	if ($IsSplitTracebilityAndPondID) {
		//Read from database 
		$Tracebility = $Tracebility . '-' . $PondNumber;
		$PondNumber = $PondNumber;
	} else {
		//Split from TraceAry
		$TraceNumber = $TraceAry[0];
		$PondNumber = $TraceAry[1];
	}
	if ($ID == '') {
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$TIME_STAMP = $dateTime->format("Y-m-d  H:i:s");

		if ($IsSplitTracebilityAndPondID) {
			$SqlCount = "SELECT COUNT(*) AS Total FROM QcTracebilityList WHERE Tracebility='$Tracebility' AND PondNumber='$PondNumber'";
		} else {
			$SqlCount = "SELECT COUNT(*) AS Total FROM QcTracebilityList WHERE Tracebility='$Tracebility'";
		}
		$StmtCount = $iconn->query($SqlCount);
		$RowCount = $StmtCount->fetch_array();
		$Count = $RowCount['Total'];
		if ($Count > 0) {
			echo "<script>alert('Traceability already added ,Please avoid Duplicate entries');</script>";
		} else {
			$sql_org_insert = "INSERT INTO QcTracebilityList (`FarmerName`, `AddressContinued`, `TraceNumber`,`Tracebility`,`PondNumber`, `PondArea`, 
			`RegAuthority`,`FarmPhoneNumber`,`FarmerCode`,`Tonnage`,`ExpiryDate`,`StartDate`,`Bap`,Certification,L2Status,FarmType,FromDateForLease,EndDateForLease
			$InsertColumns) VALUES ('$FarmerName','$AddressContinued','$TraceNumber',
			'$Tracebility','$PondNumber', '$PondArea', '$RegAuthority','$FarmPhoneNumber','$FarmerCode','$Tonnage','$ExpiryDate','$StartDate','$Bap','$Certification','$L2Status','$FarmType','$FromDateForLease','$EndDateForLease'
			$InsertValues)";
		}
		$stmt1 = $iconn->query($sql_org_insert);
	//    print_r($sql_org_insert);
	// 	exit;
		$MB_REPORT_ID = $iconn->insert_id;
		legacyAuditLogInsertById('QcTracebilityList', 'TraceID', $MB_REPORT_ID, $_POST, ['Remarks' => 'Traceability Created'], 'QualityControl');
	} else {

		// if ($UsedQuantity == 0) {
		// 	$UsedQtyQuery = " ,`UsedQuantity` = '0'";
		// 	$sqlUpdateCodeList = "UPDATE `QcCodeList` SET ResetUsedQuantity = '0' WHERE TracebilityID = '$ID'";
		// 	$stmtUpdateCodeList = $iconn->query($sqlUpdateCodeList);
		// } else if ($UsedQuantity) {
		// 	$UsedQtyQuery = "";
		// }
		$TraceNumber = $TraceNumber;
		$PondNumber = $PondNumber;
		$oldRow = legacyAuditFetchRowById('QcTracebilityList', 'TraceID', $ID, 'QualityControl');
		$sql_org_update = "UPDATE `QcTracebilityList` SET `FarmerName`='$FarmerName',`AddressContinued`='$AddressContinued',
		`TraceNumber`='$TraceNumber',`Tracebility`='$Tracebility',`PondNumber`='$PondNumber',`PondArea` = '$PondArea', `InvoiceNo` = '$InvoiceNo', `UsedDate` = '$UsedDate', `DueDate`='$DueDate',`FarmPhoneNumber`='$FarmPhoneNumber',
		`RegAuthority`='$RegAuthority',`FarmerCode`='$FarmerCode' ,`Tonnage`='$Tonnage' ,`ExpiryDate`='$ExpiryDate' ,`StartDate`='$StartDate',`Bap`='$Bap' ,
		`Certification`='$Certification',`L2Status`='$L2Status' ,`FarmType`='$FarmType' ,`FromDateForLease`='$FromDateForLease' ,`EndDateForLease`='$EndDateForLease'
		$updateColumns
		WHERE TraceID='$ID'";
		//echo '<pre>';echo $sql_org_update;die;
		$stmt1 = $iconn->query($sql_org_update);
		legacyAuditLogUpdateById('QcTracebilityList', 'TraceID', $ID, $oldRow, $_POST, ['Remarks' => 'Traceability Updated'], 'QualityControl');
	}
	if ($stmt1) {
		echo "<script>alert('Success');
		//window.close();
		 window.location = 'TraceabilityList';
		</script>";
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

					<?php include($IncludesPath . "page-title.php"); ?>
					<!-- Main content -->
					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post" id="traceabilityForm" name="traceabilityForm"
									onsubmit="confirmSubmit(event)">
									<!-- <form role="form" method="post" onsubmit="return confirmResetQty(event)"> -->
									<div class="card-body">

										<div class="row">
											<?php 
											if(!empty($EnableTracebilityNewFileds) && in_array('FarmName', $EnableTracebilityNewFileds)){ ?>
												<div class="form-group col-sm-3">
													<label for="FarmName">Farm Name </label>
													<input required type="text" class="form-control" name='FarmName'
														id="FarmName" placeholder="Enter Farm Name" <?php //echo $EditAccess;?>
														value="<?php echo $RowTrace['FarmName'] ?>">
												</div>
											<?php } ?>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Farmer Name </label>
												<input required type="text" class="form-control" name='FarmerName'
													id="FarmerName" placeholder="Enter Farmer Name" <?php echo $EditAccess;?>
													value="<?php echo $RowTrace['FarmerName'] ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Address</label>
												<input required type="text" class="form-control" name='AddressContinued'
													id="AddressContinued" placeholder="Enter Address" <?php echo $EditAccess;?>
													value="<?php echo $RowTrace['AddressContinued'] ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Farmer Code</label>
												<input type="text" class="form-control" name='FarmerCode'
													id="FarmerCode" placeholder=" Enter Farmer Code " <?php echo $EditAccess;?>
													value="<?php echo $RowTrace['FarmerCode'] ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Traceability</label>
												<input required type="text" class="form-control" name='Tracebility'
													id="Tracebility" placeholder=" Enter Traceability" <?php echo $EditAccess;?>
													value="<?php echo ($IsSplitTracebilityAndPondID) ? removePondIDFromString($RowTrace['Tracebility']) : $RowTrace['Tracebility']; ?>">
											</div>

											<?php if ($IsSplitTracebilityAndPondID) { ?>
												<div class="form-group col-sm-3">
													<label for="exampleInputEmail1">Pond ID</label>
													<input required type="text" class="form-control" name='PondNumber'
														id="PondNumber" placeholder="Enter Pond ID" <?php echo $EditAccess;?>
														value="<?php echo $RowTrace['PondNumber'] ?>"
														oninput="validateNumber()">
												</div>
											<?php } ?>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Pond Area</label>
												<input type="text" class="form-control" name='PondArea'
													id="PondArea" placeholder="Enter Pond Area" <?php echo $EditAccess;?>
													value="<?php echo $RowTrace['PondArea'] ?>"
													>
											</div>
											<?php 
											if(!empty($EnableTracebilityNewFileds) && in_array('WaterSpreadArea', $EnableTracebilityNewFileds)){ ?>
												<div class="form-group col-sm-3">
													<label for="WaterSpreadArea">Water Spread Area</label>
													<input type="text" class="form-control" name='WaterSpreadArea'
														id="WaterSpreadArea" placeholder="Enter Water Spread Area" <?php //echo $EditAccess;?>
														value="<?php echo $RowTrace['WaterSpreadArea'] ?>"
														>
												</div>
											<?php }?>

											<div class="form-group col-sm-3">
												<label for="RegAuthority">Reg Authority</label>
												<!-- <input type="text" class="form-control" name='RegAuthority'
													id="RegAuthority" placeholder="Enter Reg Authority" <?php echo $EditAccess;?>
													value="<?php echo $RowTrace['RegAuthority'] ?>"> -->
													<select  class="form-control" <?php echo empty($EditAccess)?'name="RegAuthority"':'';?> id="RegAuthority" <?php echo !empty($EditAccess)?'disabled':'';?>>
														<option value="">Select</option>
														<option value="MPEDA" <?php echo $RowTrace['RegAuthority']=='MPEDA'?'selected="selected"':''; ?>>MPEDA</option>
														<option value="CAA"  <?php echo $RowTrace['RegAuthority']=='CAA'?'selected="selected"':''; ?>>CAA</option>
														<?php 
														if(!empty($EnableTracebilityNewFileds)  && in_array('APSADA', $EnableTracebilityNewFileds)){?>
															<option value="APSADA"  <?php echo $RowTrace['RegAuthority']=='APSADA'?'selected="selected"':''; ?>>APSADA</option>
														<?php } ?>
													</select>
													<?php
													$regAuthorities = ['MPEDA', 'CAA'];
													if (!empty($EnableTracebilityNewFileds) && in_array('APSADA', $EnableTracebilityNewFileds)) {
														$regAuthorities[] = 'APSADA';
													}
													if(!empty($EditAccess)){
														$RegAuthorityVal = '';														
														if (in_array($RowTrace['RegAuthority'], $regAuthorities)) {
															$RegAuthorityVal = $RowTrace['RegAuthority'];
														}?>
														<input type="hidden" name="RegAuthority" value="<?php echo $RegAuthorityVal;?>">
													<?php }?>
											</div>
											<div class="form-group col-sm-3">
												<label for="Certification">Certification (MPEDA/CAA<?php echo !empty($EnableTracebilityNewFileds)  && in_array('APSADA', $EnableTracebilityNewFileds) ? "/APSADA" : ''?>)</label>
												<div class="form-check form-control pl-4">
													<input type="radio" class="form-check-input" name="Certification" id="Certification1" value="1" <?php echo $RowTrace['Certification']=='1'?'checked="checked"':''; ?>>
													<label class="form-check-label mr-5" for="Certification1">YES</label>

													<input type="radio" class="form-check-input" name="Certification" id="Certification0" value="0" <?php echo $RowTrace['Certification']=='0'?'checked="checked"':''; ?>>
													<label class="form-check-label" for="Certification0">NO</label>
												</div>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Phone Number</label>
												<input type="text" class="form-control" name='FarmPhoneNumber'
													id="FarmPhoneNumber" placeholder="Enter Phone Number" <?php echo $EditAccess;?>
													value="<?php echo $RowTrace['FarmPhoneNumber'] ?>">
											</div>											

											<div class="form-group col-sm-3">
												<label for="Tonnage">Tonnage </label>
												<input type="text" class="form-control" name='Tonnage' id="Tonnage"
													placeholder="Enter Tonnage in kgs"
													value="<?php echo $RowTrace['Tonnage'] ?>">
											</div>
											<input type="hidden" name="UsedQuantity" id="UsedQuantity" value="">

											

											  <!-- <div class="form-group col-sm-3 mb-3">
														<label for="txtBap" class="form-label">Bap</label>
														<select class="drpSelect form-control" name="txtBap"
															id="txtBap">
															<option value=""> Select</option>
															<?php if (!empty($resBap)) { ?>
															<?php foreach ($resBap as $Item) { ?>
																<option value="<?php echo $Item->ID ?>">
																<?php echo $Item->Name ?>
																</option>
															<?php } ?>
														<?php } ?>
														</select>
													</div> -->

											<div class="form-group col-sm-3 mb-3">
												<label for="txtBap" class="form-label">Bap</label>
												<select class="drpSelect form-control" name="txtBap" id="txtBap">
													<option value=""> Select</option>
													<?php if (!empty($resBap)) { ?>
														<?php foreach ($resBap as $Item) { ?>
															<option value="<?php echo $Item->Name ?>"
																<?php if ($RowTrace['Bap'] == $Item->Name) echo 'selected'; ?>>
																<?php echo $Item->Name ?>
															</option>
														<?php } ?>
													<?php } ?>
												</select>
											</div>
											<?php 
											if(!empty($EnableTracebilityNewFileds) && in_array('FarmIdentification', $EnableTracebilityNewFileds)){
												$FarmIdentificationArr = ['BAP','ASC','BAP & ASC','NON CERTIFIED']; ?>
												<div class="form-group col-sm-3 mb-3">
													<label for="FarmIdentification" class="form-label">Farm Identification</label>
													<select class="drpSelect form-control" name="FarmIdentification" id="FarmIdentification">
														<option value=""> Select</option>
														<?php if (!empty($FarmIdentificationArr)) { ?>
															<?php foreach ($FarmIdentificationArr as $Item) { ?>
																<option value="<?php echo $Item ?>"
																	<?php if ($RowTrace['FarmIdentification'] == $Item) echo 'selected'; ?>>
																	<?php echo $Item ?>
																</option>
															<?php } ?>
														<?php } ?>
													</select>
												</div>
											<?php }?>
											<div class="col col-md-3 mb-3">
												<label for="StartDate">Start Date</label>
												<input type="date" class="form-control" name="StartDate" id="StartDate"
													value='<?php echo $RowTrace['StartDate'] ?>' />
											</div>
											<div class="col col-md-3 mb-3">
												<label for="ExpiryDate">Expiry Date</label>
												<input type="date" class="form-control" name="ExpiryDate"
													id="ExpiryDate" value='<?php echo $RowTrace['ExpiryDate'] ?>' />
											</div>
											

											<div class="form-group col-sm-3">
												<label for="L2Status">L2 Status </label>
												<select  class="form-control" name="L2Status" id="L2Status">
													<option value="">Select</option>
													<option value="0" <?php echo $RowTrace['L2Status']=='0'?'selected="selected"':''; ?>>Pending</option>
													<option value="1"  <?php echo $RowTrace['L2Status']=='1'?'selected="selected"':''; ?>>Completed</option>
												</select>
											</div>
											<div class="form-group col-sm-3">
												<label for="FarmType">Farm Type</label>
												<select  class="form-control" name="FarmType" id="FarmType">
													<option value="">Select</option>
													<option value="Lease" <?php echo $RowTrace['FarmType']=='Lease'?'selected="selected"':''; ?>>Lease</option>
													<option value="Own"  <?php echo $RowTrace['FarmType']=='Own'?'selected="selected"':''; ?>>Own</option>
												</select>
											</div>

											<div class="col col-md-3 mb-3">
												<label for="FromDateForLease">Start Date For Lease</label>
												<input type="date" class="form-control" name="FromDateForLease" id="FromDateForLease"
													value='<?php echo $RowTrace['FarmType']=='Lease'?$RowTrace['FromDateForLease']:''; ?>' <?php echo $RowTrace['FarmType']=='Own'?'readonly':''?>/>
											</div>
											<div class="col col-md-3 mb-3">
												<label for="EndDateForLease">End Date  For Lease</label>
												<input type="date" class="form-control" name="EndDateForLease"
													id="EndDateForLease" value='<?php echo $RowTrace['FarmType']=='Lease'?$RowTrace['EndDateForLease']:''; ?>' <?php echo $RowTrace['FarmType']=='Own'?'readonly':''?> />
											</div>
											<?php 
											if(!empty($EnableTracebilityNewFileds) && in_array('CertificateNo', $EnableTracebilityNewFileds)){ ?>
												<div class="form-group col-sm-3">
													<label for="CertificateNo">Certificate No</label>
													<input required type="text" class="form-control" name='CertificateNo'
														id="CertificateNo" placeholder="Enter Certificate No" <?php //echo $EditAccess;?>
														value="<?php echo $RowTrace['CertificateNo'] ?>">
												</div>
											<?php }?>
											<?php 
											if(!empty($EnableTracebilityNewFileds) && in_array('SpeciesName', $EnableTracebilityNewFileds)){ ?>
												<div class="form-group col-sm-3">
													<label for="CertificaSpeciesNameteNo">Species Name</label>
													<select  class="form-control" name="SpeciesName" id="SpeciesName">
														<option value="">Select</option>
														<?php
															foreach ($resSpecies as $item) {
																echo '<option value="' . $item->ShortCode . '" ' .
																	($RowTrace['SpeciesName'] == $item->ShortCode ? 'selected' : '') .
																	'>' . $item->SpeciesName . '</option>';
															}
														?>
													</select>
												</div>
											<?php }?>
											<?php 
											if(!empty($EnableTracebilityNewFileds) && in_array('PreHarvestCertificateDateofIssue', $EnableTracebilityNewFileds)){ ?>
												<div class="form-group col-sm-3">
													<label for="PreHarvestCertificateDateofIssue">Pre Harvest Certificate Date of Issue</label>
													<input required1 type="date" class="form-control" name='PreHarvestCertificateDateofIssue'
														id="PreHarvestCertificateDateofIssue" placeholder="Enter Pre Harvest Certificate Date of Issue" <?php //echo $EditAccess;?>
														value="<?php echo $RowTrace['PreHarvestCertificateDateofIssue'] ?>">
												</div>
											<?php }?>
											<?php 
											if(!empty($EnableTracebilityNewFileds) && in_array('PreHarvestCertificateValidUpto', $EnableTracebilityNewFileds)){ ?>
												<div class="form-group col-sm-3">
													<label for="PreHarvestCertificateValidUpto">Pre Harvest Certificate Valid Upto</label>
													<input required1 type="date" class="form-control" name='PreHarvestCertificateValidUpto'
														id="PreHarvestCertificateValidUpto" placeholder="Enter Pre Harvest Certificate Valid Upto" <?php //echo $EditAccess;?>
														value="<?php echo $RowTrace['PreHarvestCertificateValidUpto'] ?>">
												</div>
											<?php }?>
											<?php 
											if(!empty($EnableTracebilityNewFileds) && in_array('MpedaElisaScreeningLaboratory', $EnableTracebilityNewFileds)){ 
												$MpedaElisaScreeningLaboratoryArr = ['KAKINADA','ONGOLE','NELLORE','BAPATLA','MACHILIPATNAM','BHIMAVARAM','AMALAPURAM'];
												?>
												<div class="form-group col-sm-3">
													<label for="MpedaElisaScreeningLaboratory">Mpeda Elisa Screening Laboratory</label>
													<select  class="form-control" name="MpedaElisaScreeningLaboratory" id="MpedaElisaScreeningLaboratory">
														<option value="">Select</option>
														<?php 
														foreach($MpedaElisaScreeningLaboratoryArr as $item){ ?>
															<option value="<?php echo $item;?>" <?php echo $RowTrace['MpedaElisaScreeningLaboratory'] == $item?'selected="selected"':''; ?>><?php echo $item;?></option>
														<?php } ?>
													</select>
												</div>
											<?php }?>						

										</div>
										<div class="col-xs-12 box-footer" align="right">
											<a  href="TraceabilityList" 
												class="btn btn-danger"> Close</a>
											<button type="submit" name='submit' class="btn btn-success">Submit</button>
										</div>
									</div>
								</form>

							</div> <!-- end row -->

						</div> <!-- end card body-->
					</div> <!-- end card -->


				</div>
				<!-- End Page-content -->

				<?php include($IncludesPath . "footer.php"); ?>

			</div>
			<!-- end main content-->

		</div>
		<!-- END layout-wrapper -->
		<?php include($IncludesPath . "vendor-scripts.php"); ?>
		<?php include($IncludesPath . "NewScripts.php"); ?>


		<script type="text/javascript">
			$(document).ready(function(){
				$('#FarmType').on('change',function(){
					if($(this).val() == 'Own'){
						$('#FromDateForLease,#EndDateForLease').val('').prop('readonly',true).attr('required',false)
					}else{
						$('#FromDateForLease,#EndDateForLease').prop('readonly',false).attr('required',true)
					}
				})
			})
			function validateNumber() {
				const input = document.getElementById('PondNumber');
				const value = input.value;
				// Replace all non-numeric characters
				input.value = value.replace(/[^0-9]/g, '');
			}

			//function confirmResetQty(event) {
			//var usedQuantityElement = document.getElementById("UsedQuantity");

			//var isResetQty = confirm("Do you want to reset the Used Qty to 0?");

			// If user clicks "OK", set the value to 0, else set it to 1
			//	if (isResetQty) {
			//		usedQuantityElement.value = 0;
			//	} else {
			//		usedQuantityElement.value = 1;
			//	}

			// Allow the form to be submitted after the script runs
			//	return true;
			//}
		</script>
</body>

</html>
