<?php
// session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$DateId = $_GET['id'];
$Species = $_GET['Stype'];

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resSpecies = $resDropdownsAPI->Data->Species;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCentersNew;

// $Token = $_SESSION['Token'];
// echo $Token;
// exit;
?>

<!doctype html>
<html lang="en">

<head>

	<?php include_once ROOT . 'includes/head.php' ?>

	<style>
		#tblDataTable_wrapper .dt-buttons.btn-group.flex-wrap,
		.dt-buttons.btn-group.flex-wrap {
			margin-top: 0px;
		}

		.buttons-pdf {
			background: #4550a8;
		}
	</style>
</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

	<?php include_once ROOT . 'includes/menu.php' ?>

	<!-- ============================================================== -->
	<!-- Start right Content here -->
	<!-- ============================================================== -->
	<div class="main-content">

		<div class="page-content">
			<div class="container-fluid">

				<?php include_once ROOT . 'includes/page-header.php' ?>

				<!-- start page title -->
				<div class="row">
					<div class="col-12">

						<div class="card">
							<div class="card-body">
								<div class="row">

									<div class="col col-md-3 mb-3">
										<label for="txtsubmissionDate">Effected Date</label>
										<input type="date" class="form-control" name="txtsubmissionDate" id="txtsubmissionDate" placeholder="Enter Base Price" value="<?php echo date('Y-m-d') ?>">
									</div>

									<div class="col col-md-3 mb-3">
										<label for="drpStype">Species</label>
										<select class="drpSelect form-control" id="drpStype" name="drpStype">
											<option value="">Select</option>
											<?php
											foreach ($resSpecies as $item)
												echo '<option value="' . $item->ShortCode . '">' . $item->SpeciesName . ' (' . $item->ShortCode . ')</option>';
											?>
										</select>
									</div>

									<div class="col col-md-3 mb-3">
										<label for="drpPurchaseCenter">Purchase Centers</label>
										<select class="drpSelect form-control" id="drpPurchaseCenter" name="drpPurchaseCenter" multiple="multiple">
											<?php $i = 0 ?>
											<?php foreach ($resPurchaseCenters as $item) { ?>
												<option value="<?php echo $item->PurchaseStationID ?>"><?php echo ++$i . '. ' . $item->StationName ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="text-center mt-1">
										<a href="PurchaseRatesHis" class="btn btn-danger">Close</a>
										<button id="btnSearch" type="button" class="btn btn-primary waves-effect waves-light">
											Search
										</button>
										<button id="btnSubmit" align="center" type="submit" class="btn btn-success waves-effect waves-light">
											Submit
										</button>
										<button id="btnFill" align="center" type="submit" class="btn btn-info waves-effect waves-light">
											Fill Rates
										</button>
									</div>
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-body">

								<div id="divDataList">
									<!-- -->
								</div>
							</div>
						</div>
					</div>
				</div>
			</div> <!-- container-fluid -->
		</div>
		<!-- End Page-content -->
		<?php include_once ROOT . 'includes/footer.php' ?>
	</div>
	<!-- end main content-->
</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

<script>
	$(document).ready(function() {

		var dateId = '<?php echo $DateId ?>';
		var species = '<?php echo $Species ?>';
		var btnFill = $('#btnFill');

		if (dateId) {
			btnFill.hide();
		}


		btnFill.prop('disabled', true);

		if (species != '') {
			$("#drpStype").val(species).trigger("change");
			$("#drpStype").prop('disabled', true);
			ShowList();
		} else {
			$("#drpStype").prop('disabled', false);
		}
		var isEdit = '';
		if (dateId != '') {
			isEdit = 1;
		} else {
			isEdit = 0;
		}

		$('#drpPurchaseCenter').select2();

		$(document).on('click', '#btnSearch', function() {
			var species = $("#drpStype").val();
			if (species != '') {
				ShowList();
			} else {
				alert('Select Species');
			}
		});



		$(document).on('input', '.baseRate', function() {
			var anyValueEntered = false;
			$('.baseRate').each(function() {
				if ($(this).val() !== '') {
					anyValueEntered = true;
					return false;
				}
			});
			btnFill.prop('disabled', !anyValueEntered);
		});

		$(document).on('click', '#btnFill', function() {
			$('#tblDataTable tbody').each(function() {
				var firstValue = '';
				$(this).find('td').each(function() {
					var cellValue = $(this).find('.baseRate').val();
					if (cellValue !== undefined && cellValue.trim() !== '') {
						firstValue = cellValue.trim();
					} else if (firstValue !== '') {
						$(this).find('.baseRate').val(firstValue);
					}
				});
			});
		});

		function ShowList() {
			$.ajax({
				url: 'Ajax/ajax-add-purchase-rates-m1.php',
				type: 'POST',
				data: {
					methodType: 'getList',
					species: $("#drpStype").val(),
					dateId: dateId
				},
				success: function(data) {

					if (data.content == '<p align="center" >Note : No Data Available </p>') {
						$("#btnSubmit").prop('disabled', true);
						$("#divDataList").html(data.content);
					} else {
						$("#btnSubmit").prop('disabled', false);
						$("#divDataList").html(data.content);
						var RmPriceDate = data.RmPriceDate;
						var CenterID = data.CenterID;

						$("input[name='hdnDate'][value='" + RmPriceDate + "']").val(RmPriceDate);
						$("input[name='hdnDate'][value='" + CenterID + "']").val(CenterID);

						if (CenterID) {
							var purchaseCenterValues = CenterID.split(',');
							$("#drpPurchaseCenter").val(purchaseCenterValues).trigger("change");
							$("#txtsubmissionDate").val(RmPriceDate);
						}
					}
					return false;
				}
			});
		}

		$(document).on('click', '#btnSubmit', function() {
			var isValid = true;


			var centers = $("#drpPurchaseCenter").val();

			if (centers != '') {
				$('#tblDataTable .baseRate').each(function() {
					if ($(this).val() === '') {
						$(this).css('border', '2px solid red').focus();
						isValid = false;
						return false;
					}
				});
				if (isValid) {
					submitValues();
				}
			} else {
				alert("Select Purchase Cneters");
			}
		});

		function submitValues() {

			var arrayData = [];
			$('#tblDataTable .baseRate').each(function(index) {
				var baseRate = $(this).val();
				var rmCount = $('#tblDataTable .hdnRmCount').eq(index).val();
				arrayData.push(rmCount + '/' + baseRate);
			});

			var purchaseCenter = $("#drpPurchaseCenter").val().join(',');

			$.ajax({
				url: 'Ajax/ajax-add-purchase-rates-m1.php',
				type: 'POST',
				data: {
					methodType: 'updateData',
					arrayData: arrayData,
					dateId: dateId,
					isEdit: isEdit,
					purchaseCenter: purchaseCenter,
					species: $("#drpStype").val(),
					submissionDate: $("#txtsubmissionDate").val(),
				},
				success: function(data) {

					if (!data.Error) {
						alert(data.Msg);
						var trackId = data.TrackId;
						var id = '';
						if (dateId != '' || dateId != 0) {
							id = dateId;
						} else {
							id = trackId;
						}
						window.open("RM_RatesPrnt.php?id=" + id, '_blank');
					} else {
						alert(data.Error);
					}
					return false;
				}
			});
		}
	});
</script>
</body>

</html>