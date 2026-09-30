<?php
$Status = $_SESSION['Status'];
$UserID = $_SESSION['UserID'];
$LotID = $_GET['LotID'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if ($LotID == '') {
	$PageTitle = "Create Lot";
} else {

	$PageTitle = "Edit Lot";
}
$PageTitleFolder = "Purchase";

extract($_POST);
extract($_GET);
$UNIT = $_SESSION["UNIT"];
$today_date = date('Y-m-d');


$ApiUrl = BASEURL . 'Api?f=createLot';
//ORIGINAL
$SqlRmLots = "SELECT * FROM `RmLotDetatils` WHERE RmLotID='$LotID'";
$StmtRmLots = $iconn->query($SqlRmLots);
$LotCount = mysqli_num_rows($StmtRmLots);

$RowRmLots = $StmtRmLots->fetch_array();

$PageTitle = "Edit Lot-".$RowRmLots['LotNumber'];


$SqlRmPurchaseStations = "SELECT * FROM `RmPurchaseStations` ";
$StmtRmPurchaseStations = $iconn->query($SqlRmPurchaseStations);

$SqlRmPurchaseCategory = "SELECT * FROM `RmPurchaseCategory` ";
$StmtRmPurchaseCategory = $iconn->query($SqlRmPurchaseCategory);



$SqlRmVehicalDetails = "SELECT * FROM `RmVehicalDetails`";
$StmtRmVehicalDetails = $iconn->query($SqlRmVehicalDetails);



$SqlRmUsersList = "SELECT * FROM `RmUsersList`";
$StmtRmUsersList = $iconn->query($SqlRmUsersList);

/*$SqlRmFin="SELECT * FROM `$RmFinanciers`";
$StmtRmFin=$iconn->query($SqlRmFin);*/

$AryUserList = array();
while ($RowRmUsersList = $StmtRmUsersList->fetch_array()) {
	$AryUserList[] = $RowRmUsersList;
}




extract($_POST);
extract($_GET);






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

					<?php include($IncludesPath . "page-title.php"); ?>




					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Supervisor Name</label>

												<select class="form-control select2" name="SupevisorID"
													id="SupevisorID">
													<option value=""> Select </option>

													<?php foreach ($AryUserList as $SingleItem) {
														if ($SingleItem['UserType'] == 4) {
															?>
															<option <?php if ($SingleItem['ID'] == $RowRmLots["SupevisorID"]) { ?>
																	selected="selected" <?php } ?>
																Value="<?php echo $SingleItem['ID']; ?>">
																<?php echo $SingleItem['UserName']; ?>
															</OPTION>
														<?php }
													} ?>
													<select>
											</div>








											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Purchase Count</label>
												<input type="text" class="form-control" name="PurchaseCount"
													id="PurchaseCount" placeholder="Enter Purchase Count"
													value="<?php echo $RowRmLots['PurchaseCount']; ?>" readonly>
											</div>

											
											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Purchase Weight(Kgs)</label>
												<input type="text" class="form-control" name="PurchaseWeight"
													id="PurchaseWeight" placeholder="Enter Purchase Weight"
													value="<?php echo $RowRmLots['PurchaseWeight']; ?>" readonly>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Production Weight(Kgs) </label>
												<input type="text" class="form-control" name="ProductionWeight"
													id="ProductionWeight" placeholder="Enter Production Weight"
													value="<?php echo $RowRmLots['ProductionWeight']; ?>">
											</div>


											


											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Count Net Weight(Kgs)</label>
												<input type="text" class="form-control" name="NetWeight" id="NetWeight"
													placeholder="Enter Net Weight"
													value="<?php echo $RowRmLots['NetWeight']; ?>">
											</div>




											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Seal No</label>
												<input type="text" class="form-control" name="SealNo" id="SealNo"
													placeholder="Enter Seal No"
													value="<?php echo $RowRmLots['SealNo']; ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Remarks</label>
												<input type="text" class="form-control" name="Remarks" id="Remarks"
													placeholder="Enter Remarks"
													value="<?php echo $RowRmLots['Remarks']; ?>">
											</div>
										</div>


								</form>

								<div class="col-xs-12 box-footer" align="right">
									<a target="" href="#" onclick="window.close();" class="btn btn-danger  "> Close</a>
									<?php if ($LotCount == 0) { ?>
										<button type="submt" id="CL" class="btn btn-success">Create Lot</button>

									<?php } else { ?>
										<button type="Update" id="Update" class="btn btn-success">Save</button>

									<?php } ?>



								</div>

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



</body>

</html>

<Script>

	$('document').ready(function () {

		$(document).on('click', '#Update', function (e) {
			e.preventDefault();
			// $('#CL').attr('disabled','disabled');


			//alert('Hi');

/*
			var GraderID = $('#GraderID').val();
			var AgentID = $('#AgentID').val();
			var LotNumber = $('#LotNumber').val();
			
			var DriverID = $('#DriverID').val();
			var VehicalID = $('#VehicalID').val();
		
			var FarmAddress = $('#FarmAddress').val();
			var PurchaseCategoryID = $('#PurchaseCategoryID').val();
			var PurchaseDate = $('#PurchaseDate').val();
			var PurchaseStationID = $('#PurchaseStationID').val();*/

			var PurchaseWeight = $('#PurchaseWeight').val();
			var ProductionWeight = $('#ProductionWeight').val();
			var PurchaseCount = $('#PurchaseCount').val();
			var Remarks = $('#Remarks').val();
			var SealNo = $('#SealNo').val();
			var SupevisorID = $('#SupevisorID').val();
			var NetWeight = $('#NetWeight').val();
			
			var PostedUserID = '<?PHP echo $UserID ?>'
			var LotID = '<?PHP echo $LotID ?>'

			var validation = false;


			 if (PurchaseWeight == "") {
				alert("Purchase weight is empty");
			} else if (ProductionWeight == "") {
				alert("Production Weight is empty");
			}else if (PurchaseCount == "") {
				alert("Purchase count is empty");
			} else {
				validation = true;
			}

			/*if (validation == false) {
				$('#CL').removeAttr('disabled');
				return;
			}

			if (VehicalID == 'Other') {
				VehicalID = $('#Remarks').val();
			}

			*/


			var AryData = {

				
				'PurchaseCount': PurchaseCount,
				'PurchaseWeight': PurchaseWeight,
				'ProductionWeight': ProductionWeight,
				'Remarks': Remarks,
				'SealNo': SealNo,
				'SupevisorID': SupevisorID,
				'NetWeight': NetWeight,
				'PostedUserID': PostedUserID,
				'LotID': LotID,
				'SOURCE': 'WEB'


			};
			$.ajax({
				url: 'Ajax/AjaxCreateLot.php',
				type: 'POST',
				dataType: 'json',
				data: {'Data' : AryData} ,
				success: function (data) {
					alert(data);

				}
			});


		});
	});

</script>

<script>

	$(document).ajaxStart(function () {
		$("#wait").css("display", "block");
	});
	$(document).ajaxComplete(function () {
		$("#wait").css("display", "none");
	});




</script>




</html>