<?php
$PageTitle = "Consignment Out";
$PageTitleFolder = "ColdStore";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$UnitID = $_SESSION['UnitID'];
$AccessType = $_SESSION['AccessType'];

if ($AccessType != 1) {
	//$SQry=" and ProcessingStationID=$UnitID";

}

$SqlUnit = "SELECT * FROM `AdminGeneralOptions` WHERE OptionType='CS_STATIONS' ";
$StmtUnit = $iconn->query($SqlUnit);

$SqlColdstore = "SELECT * FROM `CsStores` where StoreStatus=1";
$StmtColdstore = $iconn->query($SqlColdstore);

$SqlTenant = "SELECT * FROM `CsTenant` where TenantStatus=1";
$StmtTenant = $iconn->query($SqlTenant);

$SqlPos = "SELECT PoID,PoNo,ConsigneeName,LotNo FROM ScrmPoData 
		Left Join ScrmConsigneeDetails on ScrmPoData.ConsigneeID=ScrmConsigneeDetails.ConID 
        left JOIN ScrmShipmentDetailsBreif as SDBF on SDBF.PiID=ScrmPoData.PoID 
		where   (SDBF.TransportedDate='' or SDBF.TransportedDate is Null) 
		and ScrmPoData.Status!=4  ORDER by ConsigneeName Asc"; //SDBF.ProductionStartedDate!=''  and


$StmtPos = $iconn->query($SqlPos);


?>

<!doctype html>
<html lang="en">

<link href="<?php echo  $href; ?>" rel="stylesheet" type="text/css" />

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
					<?php include "PopUpTable.php"; ?>


					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">

									<div class="row">
										<!--  <div class="col-lg-3">
                      <div class="form-group">
                     <select class="form-control select2" name="UnitID" id="UnitID">
					 					 <!-- <option value="">Select Unit</option>

                      <?php while ($RowUnit = $StmtUnit->fetch_array()) { ?>	
				      <option value="<?php echo $RowUnit['OptID']; ?>" >
					  <?php echo $RowUnit['Name']; ?></option>
					 <?php    } ?>
                       </select>
				    </div>
                  </div>-->

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">ColdStores</label>
											<select required class="form-control select2" name="ColdstoreID" id="ColdstoreID">
												<?php while ($RowColdstore = $StmtColdstore->fetch_array()) { ?>
													<option value="<?php echo $RowColdstore['StoreID'] ?>">
														<?php echo $RowColdstore['StoreName'] ?></option>

												<?php  } ?>

											</select>
										</div>

										<div class="form-group col-sm-3">
											<label for="exampleInputEmail1">Stock belongs to</label>
											<select required class="form-control select2" name="TenantID" id="TenantID">
												<option value="BOTH">All </option>
												<?php while ($RowTenant = $StmtTenant->fetch_array()) { ?>
													<option value="<?php echo $RowTenant['TenantID'] ?>">
														<?php echo $RowTenant['TenantName'] ?></option>

												<?php  } ?>


											</select>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="exampleInputEmail1">Purchase Order</label>
												<select class="form-control select2" name="PoID" id="PoID">
													<option value="">Select Purchase Order</option>

													<?php while ($DataRow = $StmtPos->fetch_array()) {

														$OPTIONS = $DataRow['PoNo'];
													?>
														<option value="<?php echo $DataRow['PoID']; ?>">
															<?php echo $OPTIONS ?></option>
													<?php    } ?>
												</select>
											</div>
										</div>


										<div align="center" class="col-lg-12">

											<button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">Search <i class="ri-arrow-right-line align-middle ml-2"></i>
											</button>

											<button id="LoadToCons" align="center" type="button" class="btn btn-success waves-effect waves-light">Load <i class="ri-arrow-right-line align-middle ml-2"></i>

											</button>
										</div>


										<!--<div align="center" class="col-lg-3">
                                                     
         
													
			</div>-->

									</div>
									<!-- end row -->
								</div>
							</div>
						</div>

						<!-- end col -->
					</div> <!-- end row -->

					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body" id="ShowDataTable">
									<h4 class="card-title">Consignment Details </h4>


								</div> <!-- end card body-->
							</div> <!-- end card -->
						</div><!-- end col-->
					</div>
					<!-- end row-->


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
	<?php include($IncludesPath . "ShowDataTable.php"); ?>


</body>


</html>

<script>
	$('document').ready(function() {

		$(document).on('click', '#list', function(e) {
			e.preventDefault();

			getData();
		});

	});



	$(document).on('click', '#LoadToCons', function(e) {
		e.preventDefault();

		var PoID = $('#PoID').val();
		var UnitID = $('#UnitID').val();
		var ColdstoreID = $('#ColdstoreID').val();
		var TenantID = $('#TenantID').val();

		if (PoID == '') {
			$("#PoID").focus();
			alert("Select Po");
			return;
		} else {



			$.ajax({
				url: 'Ajax/AjaxPoOutPopUp.php',
				type: 'POST',
				data: {
					'PoID': PoID,
					'TYPE': 'KICKKCKC',
					'TenantID': TenantID,
					'ColdstoreID': ColdstoreID
				},
				success: function(data) {
					$(".modal-header").html("").html('<h4 class="modal-title">Are you sure to Load the consignment?</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>');
					$("#PopUpTable").html(data);
					$('#myModal').modal('show');
					$("#Update").css("display", "none");
					// var popClose = '';
					// $(".modal-header").append(popClose);
				}
			});
		}
	});



	$(document).on('click', '#CSOUT', function(e) {
		e.preventDefault();
		$('#myModal').modal('hide');
		var PoID = $('#PoID').val();
		var UnitID = $('#UnitID').val();
		var ColdstoreID = $('#ColdstoreID').val();
		var TenantID = $('#TenantID').val();


		if (PoID == '') {
			$("#PoID").focus();
			alert("Select Po");
			return;
		} else {



			$.ajax({
				url: 'Ajax/AjaxPoSearch.php',
				type: 'POST',
				//data:{'PoID':PoID , 'TYPE':'UPDATE','UnitID':UnitID},
				data: {
					'PoID': PoID,
					'TYPE': 'UPDATE',
					'TenantID': TenantID,
					'ColdstoreID': ColdstoreID
				},
				success: function(data) {

					var data = JSON.parse(data);
					if (data['sts'] == 'failed') {
						alert(data['msg']);
					} else {
						alert(data['msg']);
						var curl = 'CsPoList.php';
						window.location.reload();
					}

				}
			});
		}
	});

	function getData() {

		var PoID = $('#PoID').val();
		var UnitID = $('#UnitID').val();
		var ColdstoreID = $('#ColdstoreID').val();
		var TenantID = $('#TenantID').val();

		if (PoID == '') {
			$("#PoID").focus();
			alert("Select Po");
			return;
		} else {

			$.ajax({
				url: 'Ajax/AjaxPoSearch.php',
				type: 'POST',
				//data:{'PoID':PoID , 'TYPE':'LIST','UnitID':UnitID},
				data: {
					'PoID': PoID,
					'TYPE': 'LIST',
					'TenantID': TenantID,
					'ColdstoreID': ColdstoreID
				},
				success: function(data) {
					showData(data);
				}
			});
		}



	}
</script>