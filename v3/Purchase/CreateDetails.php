<?php
$Status = $_SESSION['Status'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


extract($_POST);
extract($_GET);
//$UNIT=$_SESSION["UNIT"];
$UserTypeId = $_GET["Type"];

if ($_SESSION['Msg'] != '') {
	$Msg = $_SESSION['Msg'];
	echo "<script>alert(' $Msg ');</script>";
	$_SESSION['Msg'] = '';
}

if ($UserTypeId == 1) {
	$UserType = 'Driver';
} else if ($UserTypeId == 2) {
	$UserType = 'Grader';
} else if ($UserTypeId == 3) {
	$UserType = 'Agent';
} else if ($UserTypeId == 4) {
	$UserType = 'Supervisor';
}


$PageTitle = "New " . $UserType;

$PageTitleFolder = "Purchase";


//$SqlPurchaseStations="SELECT * FROM `$RmPurchaseStations` where UnitID='$UNIT'"; 
$SqlPurchaseStations = "SELECT * FROM `RmPurchaseStations`";
$StmtPurchaseStations = $iconn->query($SqlPurchaseStations);



if (isset($_POST['submit'])) {
	$SqlUserCount = "SELECT count(*) as Total FROM `RmUsersList` WHERE 
	UserName='$UserName' and UserType='$UserTypeId'";
	$StmtUserCount = $iconn->query($SqlUserCount);

	$RowUserCount = $StmtUserCount->fetch_array();

	if ($RowUserCount['Total'] == 0) {
		$SqlAddUser = "INSERT INTO `RmUsersList` 
	(`UnitID`, `UserName`, `UserType`) 
	VALUES ('$UnitCode','$UserName','$UserTypeId')";


		$StmtAddUser = $iconn->query($SqlAddUser);

		if ($StmtAddUser) {
			$insertedUserId = $iconn->insert_id;
			legacyAuditLogInsertById('RmUsersList', 'ID', $insertedUserId, array(
				'UnitID' => $UnitCode,
				'UserName' => $UserName,
				'UserType' => $UserTypeId,
			), array(
				'ReferenceNo' => $insertedUserId,
				'Remarks' => $UserType . ' created from legacy purchase screen',
			), 'Purchase');
			$_SESSION['Msg'] = $UserType . ' Created Succesfully';
			header('Location: CreateDetails.php?Type=' . $UserTypeId);
		}
	} else {
		echo "<script>alert('Already Exist');</script>";
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
												<label for="exampleInputEmail1">Purchase Station</label>

												<select class="form-control select2" name="UnitCode" id="UnitCode">
													<?php while ($RowPurchaseStations = $StmtPurchaseStations->fetch_array()) { ?>
														<option Value="<?php echo  $RowPurchaseStations['ProcessingStationID']; ?>">
															<?php echo $RowPurchaseStations['StationName']; ?></OPTION>
													<?php } ?>
													<select>
											</div>
											<?php ?>
											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Name</label>
												<input type="text" class="form-control" name="UserName" id="UserName" placeholder="Enter Name" value="">
											</div>
										</div>

										<div class="col-xs-12 box-footer" align="right">
											<a href="Users?Type=2" class="btn btn-danger">Close</a>
											<button type="submit" name='submit' class="btn btn-success">Submit</button>
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



</body>

</html>
