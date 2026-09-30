<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$Status = $_SESSION['Status'];
$UserID = $_SESSION['UserID'];
$LotID = $_GET['LotID'];
$TicketID = $_GET['TicketID'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_DROPDOWNS;
$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resRmAgents = $resDropdownsAPI->Data->RmAgents; // 



$SqlRmLot = "SELECT RmReferenceID as ArrivalID,LotNumber,PurchaseDate FROM `RmLotDetatils` WHERE RmLotID='$LotID'";
// $SqlRmLot = "SELECT * FROM `RmBillDetails`";
$StmtRmLot = $iconn->query($SqlRmLot);
$RowRmLot = $StmtRmLot->fetch_array();

// echo json_encode($RowRmLot, JSON_PRETTY_PRINT);
// exit;
$LotNumber = $RowRmLot['LotNumber'];
$ArrivalID = $RowRmLot['ArrivalID'];
$BillDate = $RowRmLot['PurchaseDate'];
$CreditDate = date('Y-m-d', strtotime($BillDate . ' + 89 days'));

$PageTitle = "Edit Bill - " . $LotNumber . " (" . date('d-m-Y', strtotime($BillDate)) . ")";
$PageTitleFolder = "Accounts";

extract($_POST);
extract($_GET);
$UNIT = $_SESSION["UNIT"];
$today_date = date('Y-m-d');


//ORIGINAL
$SqlRmBills = "SELECT * FROM `RmBillDetails` WHERE ArrivalID='$ArrivalID'";
$StmtRmBills = $iconn->query($SqlRmBills);
$LotCount = mysqli_num_rows($StmtRmLots);

$RowRmBills = $StmtRmBills->fetch_array();


$SqlRmFin = "SELECT * FROM `RmSuppliers`";
$StmtRmFin = $iconn->query($SqlRmFin);

// $sqlAgent = "SELECT ID as UserID ,AgentName as UserName FROM `RmAgentsDetails`";
// $stmtAgent = $iconn->query($sqlAgent);
// // $dataAgent = $stmtAgent->fetch_array();

// $errorInfo = $stmtAgent->errorInfo();
// var_dump($errorInfo);

// var_dump($resRmAgents);



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
												<label for="exampleInputEmail1">Financier Name</label>

												<select class="form-control select2" name="FinancierID" id="FinancierID">
													<?php while ($RowRmFin = $StmtRmFin->fetch_array()) { ?>
														<option <?php if ($RowRmFin['ID'] == $RowRmBills["FinancierID"]) { ?> selected="selected" <?php } ?> Value="<?php echo $RowRmFin['ID']; ?>">
															<?php echo $RowRmFin['FinancierName']; ?></option>
													<?php } ?>
													<select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Agent Name</label>

												<select class="form-control select2" name="drpAgent" id="drpAgent">
													<option value="">Select</option>
													<?php foreach ($resRmAgents as $Agent) { ?>
														<option <?php if ($Agent->UserID == $RowRmBills["BillAgentID"]) { ?> selected="selected" <?php  } ?> Value="<?php echo $Agent->UserID; ?>">
															<?php echo $Agent->UserName; ?></option>
													<?php } ?>
													<select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Deductions</label>
												<input type="number" step="0.01" class="form-control" name="Deductions" id="Deductions" placeholder="Enter Deductions(if any)" value="<?php echo $RowRmBills['Deductions']; ?>">
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Remarks</label>
												<input type="text" class="form-control" name="Remarks" id="Remarks" placeholder="Enter Remarks" value="<?php echo $RowRmBills['Remarks']; ?>">
											</div>
										</div>
										<div class="row">


											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Financed date instead of Financial date</label>
												<input type="date" class="form-control" name="txtFinanceDate" id="txtFinanceDate" min="<?php echo $BillDate ?>" max="<?php echo $CreditDate ?>" value="<?php echo $RowRmBills['FinancialDate'] ?>">
											</div>


											<div class="form-group col-sm-3">
												<label for="txtCreditDate">Due date instead of credit date</label>
												<input type="date" class="form-control" name="txtCreditDate" id="txtCreditDate" value="<?php echo $CreditDate; ?>" readonly>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Interest Rate Instead of Credit %</label>
												<input type="text" class="form-control" name="txtInterestRate" id="txtInterestRate" placeholder="Enter Interest Rate" value="<?php echo $RowRmBills['InterestRate']; ?>">
											</div>
											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Remarks</label>
												<input type="text" class="form-control" name="txtCreditRemarks" id="txtCreditRemarks" placeholder="Enter Credit Remarks" value="<?php echo $RowRmBills['CreditRemarks']; ?>">
											</div>

										</div>
										<div>
											<span id="daysDiff" style="color: red;"><?php echo 'Credit Period : ' . $RowRmBills['CreditDays'] . ' Days'; ?></span>
											<input type="hidden" id="hdnDaysDiff" name="hdnDaysDiff" value="<?php echo $RowRmBills['CreditDays']; ?>">
										</div>

										<!-- /.box-body -->


								</form>

								<div class="col-xs-12 box-footer" align="right">

									<button type="Update" id="Update" class="btn btn-success">Edit Lot</button>

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

		$(document).on('change', '#txtFinanceDate', function() {
			var financeDate = $('#txtFinanceDate').val();
			var creditDate = '<?php echo $CreditDate; ?>';

			if (!financeDate) {
				$('#daysDiff').text('');
				$('#hdnDaysDiff').val('');
				$('#txtCreditRemarks').val('');
			} else {
				var financeDateObj = new Date(financeDate);
				var creditDateObj = new Date(creditDate);

				var timeDiff = creditDateObj - financeDateObj;
				var daysDiff = timeDiff / (1000 * 60 * 60 * 24);
				daysDiff = daysDiff + 1;

				$('#daysDiff').text('Credit Period : ' + daysDiff + ' Days');
				$('#txtCreditRemarks').val('Credit Period : ' + daysDiff + ' Days');
				$('#hdnDaysDiff').val(daysDiff);
			}

		});


		$(document).on('click', '#Update', function(e) {
			e.preventDefault();
			$('#CL').attr('disabled', 'disabled');


			//alert('Hi');


			var FinancierID = $('#FinancierID').val();
			var Deductions = $('#Deductions').val();
			var Remarks = $('#Remarks').val();
			var financeDate = $('#txtFinanceDate').val(); // 23-04-2024 added new fields
			var creditDate = $('#txtCreditDate').val(); // 23-04-2024 added new fields
			var interestRate = $('#txtInterestRate').val(); // 26-04-2024 added new fields
			var creditRemarks = $('#txtCreditRemarks').val(); // 26-04-2024 added new fields
			var creditDays = $('#hdnDaysDiff').val(); // 26-04-2024 added new fields
			var agent = $('#drpAgent').val(); // 27-04-2024 added new fields
			var PostedUserID = '<?PHP echo $UserID ?>'
			var TicketID = '<?PHP echo $TicketID ?>'
			var LotID = '<?PHP echo $LotID ?>'

			var validation = false;


			if (FinancierID == "") {
				alert("Please select Financier");
				$('#LotNumber').focus();
			} else if (Deductions == "Select Vehical") {
				alert("Deductions is empty");
				$('#Deductions').focus();
			} else if (Remarks == "Select Driver") {
				alert("Remarks is empty");
				$('#Remarks').focus();
			} else {
				validation = true;
			}

			if (validation == false) {
				$('#CL').removeAttr('disabled');
				return;
			}



			var arr = {

				'FinancierID': FinancierID,
				'Deductions': Deductions,
				'Remarks': Remarks,
				'PostedUserID': PostedUserID,
				'TicketID': TicketID,
				'LotID': LotID,
				'CreditDate': creditDate, // 23-04-2024 added new fields
				'FinancialDate': financeDate, // 23-04-2024 added new fields
				'InterestRate': interestRate, // 23-04-2024 added new fields
				'CreditRemarks': creditRemarks, // 23-04-2024 added new fields
				'CreditDays': creditDays, // 01-05-2024 added new fields
				'AgentID': agent, // 27-04-2024 added new fields
				'SOURCE': 'WEB'


			};
			$.ajax({
				// url: '<?php echo $ApiUrl ?>?f=postEditBill',
				url: 'Ajax/ajax-edit-bill',
				type: 'POST',
				dataType: 'json',
				data: arr,
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