<?php
$Status = $_SESSION['Status'];
$UserID = $_SESSION['UserID'];
$LotIDLotID = $_GET['LotID'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$PageTitle = "Rise Ticket";
$PageTitleFolder = "Purchase";

extract($_POST);
extract($_GET);
$UNIT = $_SESSION["UNIT"];
$today_date = date('Y-m-d');


//ORIGINAL
$IssueQuery = "SELECT * FROM `AdminGeneralIssues`  WHERE `IssueStatus` = 1 ";
$IssueExec = $iconn->query($IssueQuery);


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
												<label for="exampleInputEmail1">Issue Category</label>

												<select class="form-control select2" name="IssueID" id="IssueID">
													<option value="">Select</option>
													<?php while ($RowIssues = $IssueExec->fetch_array()) {
													?>
														<option Value="<?php echo $RowIssues['IssueID']; ?>">
															<?php echo $RowIssues['IssueName']; ?></OPTION>
													<?php } ?>
													<select>
											</div>





											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Description</label>
												<input type="text" class="form-control" name="Desc" id="Desc" placeholder="Enter Description" value="<?php echo $RowRmLots['LotNumber']; ?>">
											</div>





											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Remarks</label>
												<input type="text" class="form-control" name="Remarks" id="Remarks" placeholder="Enter Remarks" value="<?php echo $RowRmLots['Remarks']; ?>">
											</div>






										</div>
										<!-- /.box-body -->


								</form>

								<div class="col-xs-12 box-footer" align="right">

									<?php if ($LotCount == 0) { ?>
										<button type="submt" id="CL" class="btn btn-success">Rise Issue</button>

									<?php } else { ?>
										<button type="Update" id="Update" class="btn btn-success">Edit Lot</button>

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
	$('document').ready(function() {

		$(document).on('click', '#CL', function(e) {
			e.preventDefault();

			//$('#CL').attr('disabled','disabled');

			//alert('Hi');


			var IssueID = $('#IssueID').val();
			var Desc = $('#Desc').val();
			var Remarks = $('#Remarks').val();
			var PostedUserID = '<?PHP echo $UserID ?>';
			var LotID = '<?PHP echo $LotID ?>';

			var validation = false;


			if (Desc == "") {
				alert("Description is empty");
				$('#Desc').focus();
			} else if (Remarks == "") {
				alert("Remarks is Empty");
				$('#VehicalID').focus();
			} else {
				validation = true;
			}

			if (validation == false) {
				$('#CL').attr('enabled', 'enabled');
				return;
			}


			var arr = {
				'IssueID': IssueID,
				'Desc': Desc,
				'Remarks': Remarks,
				'PostedUserID': PostedUserID,
				'LotID': LotID
			};
			$.ajax({
				url: 'Ajax/ajax-create-ticket',
				type: 'POST',

				data: arr,
				success: function(data) {
					//  console.log("test");
					console.log(data);
					var obj = JSON.parse(data);
					// alert(obj.status);
					if (obj.Error == null) {
						alert(obj.Msg);
						window.close();
						//location.reload();

					} else {
						alert(obj.Msg);
					}

				}
			});


		});
	});
</script>

<script>
	$(document).ajaxStart(function() {
		$("#wait").css("display", "block");
	});
	$(document).ajaxComplete(function() {
		$("#wait").css("display", "none");
	});
</script>




</html>