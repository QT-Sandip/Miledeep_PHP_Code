<?php

$Status = $_SESSION['Status'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$LotId = $_GET['ID'];
$SoakRefId = $_GET['SckID'];
$SqlLots = "SELECT * FROM `RmLotDetatils` where RmLotID='$LotId'";
$StmtLots = $iconn->query($SqlLots);
$Row = $StmtLots->fetch_array();
$LotNo = $Row['LotNumber'];

$PageTitle = "Reglazing " . $LotNo;
$PageTitleFolder = "Production";


$SqlPoList = "SELECT * FROM `ScrmPoData` WHERE PoID not in (SELECT PiID from ScrmCustomInvoices) order by PoID desc";
$StmtPoList = $iconn->query($SqlPoList);

$SqlPacking = "SELECT * FROM `AdminPackingStyles` where Status=1 order by abs(PackingStyle) Asc";
$StmtPacking = $iconn->query($SqlPacking);

$SqlGrades = "SELECT * FROM `ProductionGrades` ORDER BY `ID` ASC";
$StmtGrades = $iconn->query($SqlGrades);

if (isset($_POST['submit'])) {
	$SqlDataCnt = "SELECT count(ProdID) as cnt FROM `ProdReglazingData` where LotID='$LotId'";
	$StmtData = $iconn->query($SqlDataCnt);
	$RowData = $StmtData->fetch_array();
	$SIZE = $RowData['cnt'];

	$Unit = '';
	$LotNo = '';

	if ($SIZE != 0) {
		$SIZE = $SIZE - 1;
		$Unit = $ArrayStock[$SIZE]['Unit'];
		$LotNo = $ArrayStock[$SIZE]['PoID'];
	}
}

$SqlVAStations = "SELECT * FROM PpcVAStations";
$StmtVAStations = $iconn->query($SqlVAStations);

$SqlProdVariety = "SELECT * FROM `PpcOptions` WHERE DeptID='5'";
$StmtProdVariety = $iconn->query($SqlProdVariety);
$AryOptions = array();
while ($RowOptions = $StmtProdVariety->FETCH_ARRAY()) {
	$AryOptions[] = $RowOptions;
}

$SqlHLVAVarietyList = "SELECT ShortName FROM `ScrmProductList` GROUP BY ShortName ORDER BY ShortName";
// $SqlHLVAVarietyList = "SELECT ShortName FROM `PpcHLVAVarietyList` WHERE Status='1'";
$StmtHLVAVarietyList = $iconn->query($SqlHLVAVarietyList);

$SqlShift = "SELECT * FROM `PpcOptions` WHERE DeptID=4";
$StmtShift = $iconn->query($SqlShift);
$AryShiftOptions = array();
while ($RowShiftOptions = $StmtShift->FETCH_ARRAY()) {
	$AryShiftOptions[] = $RowShiftOptions;
}

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {
	if (isset($Variety) && $Variety != null && $Variety != '') {
		$SqlPacking = "SELECT * FROM `AdminPackingStyles` WHERE PCKID='$PackingStyleID'";
		$StmtPacking = $iconn->query($SqlPacking);
		$RowPacking = $StmtPacking->fetch_array();
		$PackingStyle = $RowPacking['PackingStyle'];

		$SqlInsert = "INSERT INTO `ProdReglazingData`(`Mcs`, `PackingType`, `LoosePouches`,
		`Count`, `Grade`, `FreezingType`, `PackingStyleID`,  `Variety`, `ProductionDate`, `PoID`, 
		`LotID`, `Group`, `Category`, `Unit`, `Shift`,`PackingStyle`,
		`ProductionLine`, `Remarks`, `TubNo`, `NetWeight`, `Brand`)
		VALUES('$Mcs','$PackingType','$LoosePouches','$Count','$Grade','$FreezingType','$PackingStyleID',
		'$Variety','$ProductionDate','$PoID','$LotId','$Group','$Category','$Unit','$Shift','$PackingStyle',
		'$ProductionLine','$Remarks','$TubNo','$NetWeight','$Brand')";

		$stmt = $iconn->query($SqlInsert);
		$ProdID = $iconn->insert_id;

		if ($stmt) {
			legacyAuditLogInsertById('ProdReglazingData', 'ProdID', $ProdID, $_POST, [
				'ReferenceNo' => $ProdID,
				'Remarks' => 'Reglazing report created',
			], 'Production');
			echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "Reglazing.php?ID=' . $LotId . '";
		</script>';
		}
	} else {
		echo '<script language="javascript" type="text/javascript"> 
                alert("Please select Variety");
		</script>';
	}
}
?>


<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href ?>" rel="stylesheet" type="text/css" />

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
												<label for="exampleInputEmail1">Production Date</label>
												<input required type="date" class="form-control" name='ProductionDate' id="ProductionDate" value="" placeholder="" max="<?php echo date("Y-m-d"); ?>">
												</select>
											</div>



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Name of Station</label>
												<select class="form-control select2" name="Unit" id="Unit">
													<option value="">Select Station</option>

													<?php while ($DataRow = $StmtVAStations->fetch_array()) {
													?>
														<option value="<?php echo $DataRow['StationID']; ?>">
															<?php echo $DataRow['UnitName']; ?></option>
													<?php    }  ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Dummy/Master Cartons</label>
												<select class="form-control select2" name="PackingType" id="PackingType">
													<option value="">Select</option>
													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'PackingType') {
															$Option = $value['Name']; ?>
															<option value="<?php echo $Option; ?>"><?php echo $Option; ?></option>
													<?php  }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Po Number</label>
												<select class="form-control select2" name="PoID" id="PoID">
													<option value="">Select Po</option>
													<?php while ($RowPoList = $StmtPoList->fetch_array()) { ?>
														<option value="<?php echo $RowPoList['PoID'] ?>"><?php echo $RowPoList['PoNo'] ?></option>
													<?php  } ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Variety</label>
												<select class="form-control select2" name="Variety" id="Variety" required>
													<option value="">Select Variety</option>
													<?php while ($RowHLVAVarietyList = $StmtHLVAVarietyList->FETCH_ARRAY()) {
														$Option = $RowHLVAVarietyList['ShortName']; ?>
														<option value="<?php echo $Option; ?>"><?php echo $Option; ?></option>
													<?php } ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Freezing Type</label>
												<select class="form-control select2" name="FreezingType" id="FreezingType">
													<option value="">Select Freezing Type</option>
													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'Freeezing') {
															$Option = $value['Name']; ?>
															<option value="<?php echo $Option; ?>"><?php echo $Option; ?></option>
													<?php }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Group</label>
												<select class="form-control select2" name="Group" id="Group">
													<option value="">Select Group</option>
													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'ProductGroup') {
															$Option = $value['Name']; ?>
															<option value="<?php echo $Option; ?>"><?php echo $Option; ?></option>
													<?php }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Tub No</label>
												<input required type="text" class="form-control" name='TubNo' id="TubNo" value="" placeholder="Enter Tub No ">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Brand</label>
												<input required type="text" class="form-control" name='Brand' id="Brand" value="" placeholder="Enter Brand ">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1"> Count</label>
												<input required type="text" class="form-control" name='Count' id="Count" value="" placeholder="Enter Count">
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">No.of.Cartons</label>
												<input required type="number" class="form-control" name='Mcs' id="Mcs" placeholder="Enter number of cartons">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Grade</label>
												<select class="form-control select2" name="Grade" id="Grade">
													<option value="">Select Grade</option>
													<?php while ($RowGrades = $StmtGrades->fetch_array()) { ?>
														<option value="<?php echo $RowGrades['value'] ?>"><?php echo $RowGrades['value']; ?></option>
													<?php  } ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Packing Style</label>
												<select required class="form-control select2" name="PackingStyleID" id="PackingStyleID">
													<option value="">Select Packing</option>
													<?php while ($RowPacking = $StmtPacking->fetch_array()) { ?>
														<option value="<?php echo $RowPacking['PCKID'] ?>"><?php echo $RowPacking['PackingStyle']; ?></option>
													<?php  } ?>
												</select>

											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Net Weight</label>
												<input required type="text" class="form-control" name='NetWeight' id="NetWeight" value="" placeholder="Enter Net Weight">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Loose Boxes</label>
												<input required type="text" class="form-control" name='LoosePouches' id="LoosePouches" value="" placeholder="Enter Loose Boxes">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Shift</label>
												<select class="form-control select2" name="Shift" id="Shift">
													<option value="">Select Shift</option>
													<?php foreach ($AryShiftOptions as $value) {
														if ($value['Value'] == 'SHIFTS') {
															$Option = $value['Name']; ?>
															<option value="<?php echo $Option; ?>"><?php echo $Option; ?></option>
													<?php }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Category</label>
												<select class="form-control select2" name="Category" id="Category">
													<option value="">Select Category</option>
													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'ProductCategory') {
															$Option = $value['Name']; ?>
															<option value="<?php echo $Option; ?>"><?php echo $Option; ?></option>
													<?php }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Line Number</label>
												<select class="form-control select2" name="ProductionLine" id="ProductionLine">
													<option value="">Select Line Number</option>
													<?php foreach ($AryOptions as $value) {
														if ($value['Value'] == 'IQFLine') {
															$Option = $value['Name']; ?>
															<option value="<?php echo $Option; ?>"><?php echo $Option; ?></option>
													<?php }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Line Remarks</label>
												<input required type="text" class="form-control" name='Remarks' id="Remarks" value="" placeholder="Enter Line Remarks">
											</div>

											<div align="right" style="align-self: center" class="col-lg-12">
												<?php if ($LotId != 0) { ?>
													<a target="" onclick="window.close();" class="btn btn-danger  "> Close</a>
												<?php } ?> <button type="submit" name='submit' class="btn btn-success"><span class="glyphicon glyphicon-plus"></span>Submit</button>
											</div>
										</div>
									</div>
								</form>
							</div> <!-- end row -->
						</div>
					</div> <!-- end row -->

				</div>
			</div> <!-- end row -->

		</div> <!-- end card body-->

		<div class="col-12">
			<div class="card">
				<div class="card-body" id="ShowDataTable">
					<table id="tblDataTable" data-paging="false" class="table dt-responsive nowrap w-100">

						<thead class="thead-light">
							<tr>
								<th>Lot No</th>
								<th>Grade</th>
								<th>Freezing<br> Type</th>
								<th>Packing<br> Style</th>
								<th>Date</th>
								<th>Brand</th>
								<th>Shift</th>
								<th>Mcs</th>
								<th>Options</th>
							</tr>
						</thead>
						<tbody>
						</tbody>

					</table>
					
				</div>
			</div>
		</div>


	</div>
	<!-- end main content-->
	<?php include($IncludesPath . "footer.php"); ?>
	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>
	<?php //include($IncludesPath . "ShowDataTable.php"); 
	?>

	<!-- Required datatable js -->
	<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

	<script>
		var isLoading = false;
		var currentPage = 1;
		var LotId = '<?php echo $LotId; ?>';

		function fnShowDataTable() {
			var table = $('#tblDataTable').DataTable({
				scrollY: "600px",
				scrollX: true,
				sScrollXInner: "100%",
				paging: false,
				destroy: true,
				fixedColumns: true,
				scrollCollapse: true,
				bInfo: false,
				dom: 'Blfrtip',
				// dom: '<"top-left"Blfrtip>',
				buttons: [{
					extend: 'excel',
					text: 'Download Excel',
					className: 'btnExcelDataTable btn-left-align',
					title: 'Grade Master HL Count List  <?php echo date("d-m-Y") ?>',
					footer: true,
					exportOptions: {
						columns: ':visible'
					}
				}],
			});
		}
		var table = $("#tblDataTable").DataTable({
			columns: [{
					title: "Lot No"
				},
				{
					title: "Grade"
				},
				{
					title: "Freezing Type"
				},
				{
					title: "Packing Style"
				},
				{
					title: "Date"
				},
				{
					title: "Brand"
				},
				{
					title: "Shift"
				},
				{
					title: "MCS"
				},
				{
					title: "Options"
				}
			]
		});

		function fnSuccess(data) {
			/*if(currentPage == 1){
        	$("#ShowDataTable").html(data);
    	}else{
        	$("#tblDataTable").DataTable().row.add($(data)).draw();
        }*/
			//$("#tblDataTable").DataTable().row.add($(data)).draw();


			var data = JSON.parse(data);
			data = data.Data;
			data.forEach(function(rowData) {
				var rowArray = [];
				rowArray.push(rowData.PoNo || "");
				rowArray.push(rowData.Grade || "");
				rowArray.push(rowData.FreezingType || "");
				rowArray.push(rowData.PackingStyle || "");
				rowArray.push(rowData.ProductionDate || "");
				rowArray.push(rowData.Brand || "");
				rowArray.push(rowData.Shift || "");
				rowArray.push(rowData.Mcs || "");

				var editButton = '<a target="_blank" href="EditReglazingReport.php?ProdID=' + (rowData.ProdID || "") + '&LotID=' + (rowData.LotID || "") + '" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>';

				var deleteButton = '<button class="btn btn-link text-danger Delete" data-id="' + (rowData.ProdID || "") + '" data-toggle="tooltip" data-placement="top" title="Delete"><i class="mdi mdi-trash-can font-size-16"></i></button>';

				rowArray.push(editButton + deleteButton);
				table.row.add(rowArray).draw();
			});
			loadInProgress = false;
			isLoading = false;

			//fnShowDataTable();

			/*$("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" 
			)
			    .insertAfter("#tblDataTable_filter label");*/

			/*$('html, body').animate({
			    scrollTop: $("#divDataList").offset().top - 50
			}, 10);*/
		}

		function ShowList(pageNo, searchkey, loadAll, LotId) {
			$.ajax({
				url: 'Ajax/ajax-reglazing.php',
				type: 'POST',
				data: {
					LotId: LotId,
					PageNo: pageNo,
					//SearchKey: searchkey,
					LoadAll: loadAll,
				},
				success: function(data) {
					fnSuccess(data);
					return false;
				},
				error: function() {
					// Handle errors
					loadInProgress = false;
					isLoading = false;
				}
			});
		}


		$(document).ready(function() {
			ShowList("1", "", "0", LotId);

			let lastScrollTop = 0;

			$(window).scroll(function() {
				var scrollTop = $(this).scrollTop();
				var windowHeight = $(window).height();
				var documentHeight = $(document).height();

				var scrollingDown = scrollTop > lastScrollTop;
				lastScrollTop = scrollTop;

				if (scrollingDown && scrollTop + windowHeight >= documentHeight - 5 && !isLoading) {
					isLoading = true;
					currentPage++;
					ShowList(currentPage, "", "0", LotId);

					setTimeout(function() {
						isLoading = false;
					}, 1000);
				}
			});
		});

		$(document).on("click", "#btnMoreSearch", function() {
			var searchkey = $("#tblDataTable_filter input").val();
			if (searchkey.length > 0) {
				ShowList("1", searchkey, "0", LotId);
			}
		});

		$(document).on("click", "#btnLoadAll", function() {
			ShowList("1", "", "1", LotId);
		});

		$(document).on("click", ".btnPageno", function() {
			var pageno = $(this).attr("data-pageno");
			var searchkey = $("#tblDataTable_filter input").val();
			if (searchkey.length > 0) {
				ShowList("1", searchkey, "0", LotId);
			} else {
				ShowList(pageno, "", "0", LotId);
			}
		});

		$("#btnSearch").on("click", function() {
			ShowList();
		})
	</script>

	<script>
		$('document').ready(function() {
			// showData();
			let packingStyleData = null,
				netWeight = 0;
			$('#PackingStyleID').on('change keyup', function() {
				let PCKID = $('#PackingStyleID').val();
				if (PCKID != '') {
					$.ajax({
						type: 'POST',
						url: 'Ajax/AjaxCreateStockOptions.php',
						dataType: 'json',
						data: {
							'Type': 'fetchPackingSlabs',
							'PCKID': PCKID,
						},
						success: function(response) {
							packingStyleData = JSON.parse(JSON.stringify(response));
							calculateNetWeight();
						}
					});
				}
			});

			$('#Mcs, #LoosePouches').on('change keyup', function() {
				calculateNetWeight();
			});

			function calculateNetWeight() {
				let mcs = $('#Mcs').val();
				let looseBags = $('#LoosePouches').val();

				if (packingStyleData) {
					netWeight = mcs * packingStyleData['TotalSlabs'] * packingStyleData['SlabWeight'] * packingStyleData['MFactor'];
				}
				if (looseBags > 0) {
					netWeight += looseBags * packingStyleData['SlabWeight'] * packingStyleData['MFactor'];
				}

				$('#NetWeight').val(netWeight.toFixed(3));
			}
		});

		$(document).on('click', '.Delete', function(e) {
			var ReglazingID = $(this).data('id');

			var r = confirm("Are You Sure You Want To Delete " + ReglazingID + "?");
			if (r == true) {

				//alert(IqfID);		  
				$.ajax({
					url: 'Ajax/AjDltAl.php',
					type: 'POST',
					data: {
						'TYPE': 'ReglazingDelete',
						'Q_ID': ReglazingID
					},
					success: function(response) {
						var data = JSON.parse(response);
						if (data['sts'] == 'Failed') {
							alert(data['msg']);
							location.reload();
						} else {
							$('#myModal').modal('hide');
							alert(data['msg']);
							location.reload();
						}
					}
				});
			} else {}
		});

		// function reply(clicked_id) {
		// 	alert(clicked_id);
		// 	$.ajax({
		// 		url: 'AjDltAl.php',
		// 		type: 'POST',
		// 		data: {
		// 			'TYPE': 'VA',
		// 			'Q_ID': clicked_id
		// 		},
		// 		success: function(data) {
		// 			console.log(data);

		// 			$("#CODE_LIST").html(data);
		// 			alert("Deleted");
		// 			location.reload();
		// 		}
		// 	});
		// }
	</script>

	<script>
		function getGradeBreakUpData() {
			var PoId = $('#PoID').val();
			var Grade = $('#Grade').val();


			if (PoId == '') {
				$("#Grade").val(Grade);
				return;
			}
			$.ajax({
				type: 'POST',
				url: 'Ajax/AjaxCreateStockOptions.php',
				dataType: 'json',
				data: {
					'Type': 'Packing',
					'PoId': PoId,
					'Grade': Grade
				},
				success: function(response) {
					var myJSON = JSON.parse(JSON.stringify(response));
					$("#PackingStyleID").val(myJSON['PackingStyleID']);
					$("#Brand").val(myJSON['Brand']);
					$("#FreezingType").val(myJSON['FreezingType']);
				}
			});
		}
	</script>
</body>

</html>
