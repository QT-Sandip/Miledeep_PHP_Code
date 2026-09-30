<?php
$Status = $_SESSION['Status'];
$UserID = $_SESSION['UserID'];
$LotID = $_GET['LotID'];
$TicketID = $_GET['TicketID'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlRmLot = "SELECT LotNumber FROM `RmLotDetatils` WHERE RmLotID='$LotID'";
$StmtRmLot = $iconn->query($SqlRmLot);
$RowRmLot = $StmtRmLot->fetch_array();
$LotNumber = $RowRmLot['LotNumber'];

$PageTitle = "Delete Bill - " . $LotNumber;
$PageTitleFolder = "Purchase";

$RandomNumber =  mt_rand(100000, 999999);

extract($_POST);
extract($_GET);
$UNIT = $_SESSION["UNIT"];
$today_date = date('Y-m-d');


//ORIGINAL
$SqlRmBills = "SELECT * FROM `RmBillDetails` WHERE ArrivalID='$LotID'";
$StmtRmBills = $iconn->query($SqlRmBills);
$LotCount = mysqli_num_rows($StmtRmLots);

$RowRmBills = $StmtRmBills->fetch_array();


$SqlRmFin = "SELECT * FROM `$RmFinanciers`";
$StmtRmFin = $iconn->query($SqlRmFin);





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
												<label for="exampleInputPassword1">Enter Password</label>
												<input type="password" class="form-control" name="Password" id="Password" placeholder="Enter Password To Delete" value="">
											</div>





										</div>
										<!-- /.box-body -->


								</form>

								<div class="col-xs-12 box-footer" align="right">

									<button type="Update" id="Update" class="btn btn-danger">Delete Bill</button>

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

		$(document).on('click', '#Update', function(e) {
			e.preventDefault();
			$('#CL').attr('disabled', 'disabled');


			//alert('Hi');

			var Password = $('#Password').val();
			var PostedUserID = '<?PHP echo $UserID ?>'
			var TicketID = '<?PHP echo $TicketID ?>'
			var LotID = '<?PHP echo $LotID ?>'

			var validation = false;


			if (Password == "") {
				alert("Please Enter Password");
				$('#Password').focus();
			} else {
				validation = true;
			}

			if (validation == false) {
				$('#CL').removeAttr('disabled');
				return;
			}



			var arr = {

				'Password': Password,
				'PostedUserID': PostedUserID,
				'TicketID': TicketID,
				'LotID': LotID,
				'SOURCE': 'WEB'
			};
			$.ajax({
				// url: '<?php echo $ApiUrl ?>?f=postDeleteGrading',
				url: 'Ajax/ajax-delete-bill',
				type: 'POST',
				dataType: 'json',
				data: JSON.stringify(arr),
				success: function(data) {
					var obj = JSON.parse(JSON.stringify(data));
					if (obj.Error == null) {
						$('#CL').removeAttr('disabled');
						alert(obj.Msg);
						close();
					} else {
						alert(obj.Error);
						close();
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