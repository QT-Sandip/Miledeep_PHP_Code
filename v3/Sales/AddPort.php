<?php
$Status = $_SESSION['Status'];
$UserID = $_SESSION['UserID'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


$PageTitle = "Add Port";
$ID = $_GET['Id'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));



$PageTitleFolder = "Master";
$SqlCountry = "SELECT * FROM `ScrmCountriesList` ORDER by id asc";
$StmtCountry = $iconn->query($SqlCountry);

$SqlPort = "SELECT * FROM `ScrmPortCities` ORDER BY `PortId` ASC";
$StmtPort = $iconn->query($SqlPort);

$SqlPortDetails = "SELECT * FROM `ScrmPortCities` Where  PortId='$ID'";
$StmtPortDetails = $iconn->query($SqlPortDetails);
$RowPortDetails = $StmtPortDetails->fetch_array();
$Country = $RowPortDetails['Country'];


extract($_POST);
extract($_GET);




if (isset($_POST['submit'])) {



	if ($ID == '') {
		$ReferencePortID = getReferencePortID($PortId,'');
		$SqlInsert = "INSERT INTO `ScrmPortCities`(`ReferencePortID`, `Country`, `Name`, `PortCode`, `IsPortofLoading`) 
VALUES ('$ReferencePortID','$CountryID','$Name','$PortCode','$IsPortofLoading')";
$StmtInsert = $iconn->query($SqlInsert);
		if ($StmtInsert) {
			$insertedPortId = $iconn->insert_id;
			legacyAuditLogInsertById('ScrmPortCities', 'PortId', $insertedPortId, $_POST, array(
				'ReferenceNo' => $insertedPortId,
				'Remarks' => 'Port created',
			), 'Sales');


			echo '<script language="javascript" type="text/javascript"> 
			alert("Success");
			window.location = "PortsList.php";
	</script>';
		}

	} else {
		$existingPort = legacyAuditFetchRowById('ScrmPortCities', 'PortId', $ID, 'Sales');
		$ReferencePortID = getReferencePortID($PortId, $ID);

		$SqlInsert = "UPDATE `ScrmPortCities` SET `ReferencePortID`='$ReferencePortID',`Country`='$CountryID',
		`Name`='$Name',`PortCode`='$PortCode',`IsPortofLoading`='$IsPortofLoading' WHERE `PortId`='$ID'"; 
		$StmtInsert = $iconn->query($SqlInsert);

		if ($StmtInsert) {
			legacyAuditLogUpdateById('ScrmPortCities', 'PortId', $ID, $existingPort, $_POST, array(
				'ReferenceNo' => $ID,
				'Remarks' => 'Port updated',
			), 'Sales');
			$ArgumentsArray[PostedUserId] = $PostedUserId;
			$ArgumentsArray[PageUrl] = $PageUrl;
			$ArgumentsArray[Remarks] = $Remarks;
			$ArgumentsArray[ReferenceID] = $ID;
			postUserEditLog($ArgumentsArray);

			echo '<script language="javascript" type="text/javascript"> 
			alert("Success");
			window.location = "PortsList.php";
	</script>';
		}

	}

	//echo $SqlInsert; exit;


	//}
	/*else{
		   
		   echo "<script>alert('Already Exists');</script>";
	   }
			*/


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


					<?php include($IncludesPath . "page-title.php"); ?>




					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">


											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1"> Country<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="CountryID"
													id="CountryID">
													<option value="">Select</option>
													<?php
													while ($RowCountry = $StmtCountry->fetch_array()) { ?>
														<option <?php if ($RowPortDetails['Country'] == $RowCountry['id']) { ?>
																selected="selected" <?php } ?>
															value="<?php echo $RowCountry['id']; ?>">
															<?php echo $RowCountry['name']; ?></option>
													<?php } ?>

												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1"> Port<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="PortId" id="PortId">
													<option value="">Select</option>
													<?php
													while ($RowPort = $StmtPort->fetch_array()) { ?>
														<option value="<?php echo $RowPort['PortId']; ?>">
															<?php echo $RowPort['Name']; ?></option>
													<?php } ?>


												</select>
											</div>



											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Port Name<span
														style="color:red">*</span></label>
												<input required type="text" class="form-control" name="Name" id="Name"
													placeholder="Enter Port Name"
													value="<?php echo $RowPortDetails['Name']; ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Port Code</label>
												<input type="text" class="form-control" name="PortCode" id="PortCode"
													placeholder="Enter Port Code"
													value="<?php echo $RowPortDetails['PortCode']; ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1"> Is PortofLoading<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="IsPortofLoading" id="IsPortofLoading">
													<option <?php  if($RowPortDetails['IsPortofLoading']==0) { ?> echo selected="selected"<?php } ?>
													value="0">No</option>

													<option <?php  if($RowPortDetails['IsPortofLoading']==1) { ?> echo selected="selected"<?php } ?>
													value="1">Yes</option>
													<?php
													// while ($RowPort = $StmtPort->fetch_array()) { ?>
														<!-- <option value="0">	No ?></option> -->
													<!-- <?php //} ?> -->


												</select>
											</div>


										</div>

										<!-- /.box-body -->


										<div class="col-xs-12 box-footer" align="right">
											<a target="" href="PortsList.php" class="btn btn-danger  "> Close</a>

											<button type="submit" style="" name='submit'
												class="btn btn-success">Submit</button>

										</div>





									</div>
									<!-- /.box-body -->


								</form>



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

	<?php
	function getReferencePortID($PortId, $ID)
	{
		global $iconn;
		if ($PortId == 0) {
			$SqlreferenceId = "SELECT PortId FROM `ScrmPortCities` order by PortId DESC LIMIT 1";
			$StmtReferenceId = $iconn->query($SqlreferenceId);
			$RowReferenceId = $StmtReferenceId->fetch_array();
			if ($ID != '') {
				$ReferencePortID = $RowReferenceId['PortId'];
			} else {
				$ReferencePortID = $RowReferenceId['PortId'] + 1;
			}
		} else {
			$ReferencePortID = $PortId;
		}
		return $ReferencePortID;
	}

	?>


	<script>

		$("document").ready(function () {

			var ID = '<?php echo $ID; ?>';
			if (ID != '') {
				var Country = '<?php echo $Country; ?>';

				getPortDetails(Country);
			}
		});


		$('#CountryID').change(function () {
			var CountryId = $('#CountryID').val();

			getPortDetails(CountryId);
		});

		function getPortDetails(CountryId) {
			if (CountryId == '') {
				return;
			}
			$.ajax({
				type: 'POST',
				url: 'Ajax/AjPortDropdown.php',
				data: { 'CountryId': CountryId },
				success: function (response) {

					$("#PortId").html(response);


				}
			});
		}
	</script>

</body>

</html>
