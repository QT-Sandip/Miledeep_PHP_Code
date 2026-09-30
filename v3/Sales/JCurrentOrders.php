<style>
	label {
		font-size: 15px;
		color: black;
	}
</style>

<style>
	th {
		font-size: 12px;
	}


	.table tr:hover {
		background-color: #343a40 !important;
		color: #fff !important;
	}


	.table td,
	.table th {
		font-size: 12px;
		padding: 7px !important;
		vertical-align: top;
		border-top: 1px solid #eff2f7;
	}

	.customWrap {
		white-space: nowrap !important;
	}

	.warning-row {
		background-color: #f7ff84 !important;
		color: #111;
		font-weight: 500 !important;
	}

	.success-row {
		background-color: #fff !important;
	}

	.deadline-row {
		background-color: #df411d !important;
		color: #ffffff !important;
		font-weight: 500 !important;
	}

	.custom-box {
		width: 16px;
		/* Width of the box */
		height: 16px;
		/* Height of the box */
		border: 2px solid lightgray;
		/* Add a black border */
		border-radius: 4px;
		/* Optional: Rounded corners */
		display: inline-block;
		/* Ensure it behaves like an inline element */
		margin-right: 8px;
		/* Space between the box and the number */
	}

	.box-number {
		font-size: 14px;
		/* Adjust the font size */
		vertical-align: middle;
		/* Align the number vertically with the box */
	}

	.box-with-number {
		display: inline-flex;
		align-items: center;
		/* Vertically align the box and number */
	}

	.modal-content {
		overflow-x: auto !important;
		scroll-behavior: smooth;
	}
</style>

<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
//include "../includes/config.php";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$CurrentUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$AccessType = $_SESSION["AccessType"];

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsShowPlantFilter = $ArrayClientConfigData['ShowPlantFilterForDashboardCounts'];

$warningColor = '#f7ff84';
$deadlineColor = '#df411d';
function checkEmpty($Value)
{


	if ($Value == '') {
		$Value = '--';
	}

	return $Value;
}




if ($_GET['chbkExp'] == '') {
	$chbkExp = 'true';
} else {
	$chbkExp = $_GET['chbkExp'];
}


if ($_GET['chbkShip'] == '') {
	$chbkShip = 'true';
} else {
	$chbkShip = $_GET['chbkShip'];
}

//Start Year From Config
//End Year If Current Year+1

$Year = $_GET['Year'];
$CurrentMonth = date('m');

$Currentyear = empty($Year) ? date('Y') : $Year;

if ($CurrentMonth > 3 && $Year == '') {
	$FinClsYear = $Currentyear;
} elseif (!empty($Year)) {
	$FinClsYear = $Year;
} else {
	$FinClsYear = $Currentyear - 1;
}

$UnitID = $_SESSION['UnitID'];
// echo $UnitID;

if ($CurrentMonth > 3) {
	$RunningFinStartYear = date('Y');
} else {
	$RunningFinStartYear = (date('Y') - 1);
}

$SelectedPlantID = ($UnitID != 0) ? $UnitID : $_GET['PlantID'];

$SelectedExporterID = $_GET['ExporterID'];

$DateStart = $FinClsYear . '-04-01';
$DateEnd = ($FinClsYear + 1) . '-03-31';

$NextDateStart = ($FinClsYear + 1) . '-04-01';
$NextDateEnd = ($FinClsYear + 2) . '-03-31';

$PastYearStart = ($FinClsYear - 1) . '-04-01';
$PastYearEnd = ($FinClsYear) . '-03-31';
$PastNextYear = $FinClsYear . "-03-31";

$NextDateFilter = "BETWEEN '$NextDateStart' and '$NextDateEnd'";
$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
$PastDateFilter = "BETWEEN '$PastYearStart' and '$PastYearEnd'";


$CurrentFincialYear = $DateStart;

$sqlQuery = "SELECT * FROM AdminExporterDetails WHERE ExpType in(2,3)";
$stmt = $iconn->query($sqlQuery);
while ($row = $stmt->fetch_assoc()) {
	$Data[] = $row;
}
$resProcessingStations = $Data;
$SelectedPlantQry = "";
if ($SelectedPlantID) {
	$SelectedPlantQry = "AND SPD.ProcessorID IN($SelectedPlantID)";
}

$sqlExporter = "SELECT ExpID,ExpName FROM `AdminExporterDetails` WHERE `ExpType` = 1";
$stmtExporter = $iconn->query($sqlExporter);
while ($rowExp = $stmtExporter->fetch_assoc()) {
	$DataExp[] = $rowExp;
}
$resExporters = $DataExp;
if ($SelectedExporterID) {
	$SelectedPlantQry = $SelectedPlantQry . "AND SPD.ExporterID IN($SelectedExporterID)";
}

//$DateFilter;
$AryPos = array();

$SqlLate = "SELECT SPD.*,ConsigneeName,SCL.name as CountryName,
  SPC.Name as PortOfDischarge,SDB.CInvNo,
`ShippedOn`, `BLNumber`, `ETA`, `ProductionStartedDate`, `TransportedDate`,SDB.TransferDate,
`ReturnedDate`, SDB.ContainerNo, `AvailableDate`,
 `ClosedDate`, `DeliveredDate`,COALESCE(DATEDIFF(SPD.ShipByDate,now())-1,0) as DaysRemaining,
 COALESCE(DATEDIFF(SPD.ShipByDate,SDB.TransportedDate),0) as DelayedDays,SCI.CustomsInvNo as CInvNo
 from ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID= SCD.ConID
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
left join ScrmShipmentDetailsBreif as SDB on SPD.PoID=SDB.PiID
left join ScrmCustomInvoices as SCI on SPD.PoID=SCI.PiID
WHERE SDB.CInvNo = ''  $SelectedPlantQry
order by SPD.ShipByDate DESC";
$StmtLate = $iconn->query($SqlLate);

while ($LateRow = $StmtLate->fetch_array()) {

	if ($RunningFinStartYear == $FinClsYear) {
		$AryPos[] = $LateRow;
	}
}


$SqlLate = "SELECT SPD.*,ConsigneeName,SCL.name as CountryName,
  SPC.Name as PortOfDischarge,SDB.CInvNo,
`ShippedOn`, `BLNumber`, `ETA`, `ProductionStartedDate`, `TransportedDate`,SDB.TransferDate,
`ReturnedDate`, `ContainerNo`, `AvailableDate`,
 `ClosedDate`, `DeliveredDate`,COALESCE(DATEDIFF(SPD.ShipByDate,now())-1,0) as DaysRemaining,
 COALESCE(DATEDIFF(SPD.ShipByDate,SDB.TransportedDate),0) as DelayedDays
 from ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID= SCD.ConID
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
left join ScrmShipmentDetailsBreif as SDB on SPD.PoID=SDB.PiID
WHERE SDB.CInvNo=''  and SDB.TransportedDate!='' AND SPD.PoDate $DateFilter $SelectedPlantQry
order by SDB.TransportedDate DESC";
$StmtLate = $iconn->query($SqlLate);

while ($LateRow = $StmtLate->fetch_array()) {

	if ($RunningFinStartYear == $FinClsYear) {
		$AryPos[] = $LateRow;
	}
}

//$OrderOnHand= count($AryPos);


$SqlLate = "SELECT SPD.*,ConsigneeName,SCL.name as CountryName,SPC.Name as PortOfDischarge,SCI.CustomsInvNo as CInvNo,
`ShippedOn`, `BLNumber`, `ETA`, `ProductionStartedDate`, `TransportedDate`,`ReturnedDate`, 
SCI.ContainerNo, `AvailableDate`,SDB.TransferDate,
 `ClosedDate`, `DeliveredDate`,COALESCE(DATEDIFF(SPD.ShipByDate,now())-1,0) as DaysRemaining,
 COALESCE(DATEDIFF(SPD.ShipByDate,SDB.TransportedDate),0) as DelayedDays
 from ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID= SCD.ConID
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
left join ScrmShipmentDetailsBreif as SDB on SPD.PoID=SDB.PiID
left join ScrmCustomInvoices as SCI on SPD.PoID=SCI.PiID
WHERE SCI.CInvNo !='' and  SDB.FinYear='$FinClsYear' AND SCI.CInvDate $DateFilter $SelectedPlantQry
order by SCI.CustomsInvNo DESC";

//echo $SqlLate;
$StmtLate = $iconn->query($SqlLate);

while ($LateRow = $StmtLate->fetch_array()) {
	$AryPos[] = $LateRow;
}


$Closed = 0;
$OnSea = 0;
$InTransport = 0;
//$OrderOnHand=0;
//$OpeningBalance=0;
//$Delivered=0;

foreach ($AryPos as $PoItem) {

	if ($PoItem['ReturnedDate'] != '') {
		//$CurrentStatus='Returned';
	} else if ($PoItem['ClosedDate'] != '') {
		$Closed++;
	} else if ($PoItem['DeliveredDate'] != '') {
		//$Delivered++;
	} else if ($PoItem['ShippedOn'] != '') {

		$OnSea++;
	} else if ($PoItem['TransportedDate'] != '') {
		$InTransport++;
	} else if ($PoItem['ProductionStartedDate'] != '') {
		//$CurrentStatus='Production';
	} else {
		//$CurrentStatus='Issued';
	}

	//$OpeningBalance='';


}



?>

<!doctype html>
<html lang="en">

<head>
	<?php include($IncludesPath . "title-meta.php"); ?>

	<!-- jquery.vectormap css -->
	<link href="<?php echo $BaseDist ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css"
		rel="stylesheet" type="text/css" />

	<!-- DataTables -->
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
		rel="stylesheet" type="text/css" />

	<!-- Responsive datatable examples -->
	<link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
		rel="stylesheet" type="text/css" />
	<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

	<?php include($IncludesPath . "head-css.php"); ?>

	<style>
		/* .table tr:hover td.child {
		color: #000000 !important;
	}
	a#PoDetails {
		padding-left: 26px;
	}
	table.dataTable.dtr-inline.collapsed>tbody>tr[role="row"]>td.dtr-control:before, 
	table.dataTable.dtr-inline.collapsed>tbody>tr[role="row"]>th.dtr-control:before {
		top: 16px;
	} */
	</style>

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

					<?php //include ($IncludesPath."page-title.php");
					?>
					<?php include "PopUpTable.php";

					?>

					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">

										<div class="form-group col-lg-3">
											<label>Select Year: </label>
											<select class="form-control select2" name="Year" id="Year">
												<?php

												for ($Startyear; $Startyear < $endYear; $Startyear++) {

													$year = ($Startyear) . '-' . ($Startyear + 1); ?>

													<option <?php echo $Startyear;
													if ($Startyear == $FinClsYear) { ?>
															selected="selected" <?php } ?> value="<?php echo $Startyear ?>">
														<?php echo $year ?>
													</option>


												<?php } ?>
												<select>
										</div>
										<?php
										if ($IsShowPlantFilter) {
											?>
											<div class="col-lg-3 mb-3">
												<label class="form-label" for="drpExporter">Exporter</label>
												<select class="form-control select2" name="drpExporter" id="drpExporter">
													<option value="">All</option>
													<?php
													foreach ($resExporters as $item)
														echo '<option value="' . $item['ExpID'] . '" ' . (($item['ExpID'] == $SelectedExporterID) ? 'selected' : '') . '>' . $item['ExpName'] . '</option>';
													?>
												</select>
											</div>
											<div class="col-lg-3 mb-3">
												<label class="form-label" for="drpPlant">Plant</label>
												<select class="form-control select2" name="drpPlant" id="drpPlant">
													<option value="">Select</option>
													<?php
													foreach ($resProcessingStations as $item)
														echo '<option value="' . $item['ExpID'] . '" ' . (($item['ExpID'] == $SelectedPlantID) ? 'selected' : '') . '>' . $item['ExpName'] . '</option>';
													?>
												</select>
											</div>
											<?php
										}
										?>
									</div>


									<div align="CENTER" class="col-lg-12">

										<button id="list" align="left" type="button"
											class="btn btn-primary waves-effect waves-light">
											Search <i class="ri-arrow-right-line align-middle ml-2"></i>
										</button>
										<?php if ($AccessType < 3) { ?>
											<a target="_blank" href="PIGeneration.php"
												class="btn btn-primary waves-effect waves-light">
												<i class="fa fa-plus"></i> New PI</a>
										<?php } ?>

									</div>



									<!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>-->


								</div>
								<!-- end row -->
							</div>
						</div>



						<div class="col-xl-8">
							<div class="row">
							</div>
							<div class="row"> </div>
							<!-- end row -->



						</div>

						<div class="col-xl-4">





						</div>
					</div>

					<div class="row">

						<div class="col-lg-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-lg-10">
											<h4 class="card-title mb-4">Order Book</h4>
										</div>
										<div class="col-lg-2">
											<div class="box-with-number">
												<div class="custom-box"
													style="background-color: <?php echo $deadlineColor; ?>!important"></div>
												<span id="deadline-count" class="box-number"></span>
												<!-- This is the number adjacent to the box -->
											</div>
											<div class="box-with-number">
												<div class="custom-box"
													style="background-color: <?php echo $warningColor; ?>!important"></div>
												<span id="warning-count" class="box-number"></span>
												<!-- This is the number adjacent to the box -->
											</div>
											<div class="box-with-number">
												<div class="custom-box"></div>
												<span id="success-count" class="box-number"></span>
												<!-- This is the number adjacent to the box -->
											</div>
											<?php
											$warningCount = 0;
											$successCount = 0;
											$deadlineCount = 0;

											?>

										</div>

									</div>
									<div class="table-responsive ">
										<!--<table id="scroll-vertical-datatable" 
											class="table dt-responsive wrap w-500" >-->
										<table id="key-datatable" data-paging="false"
											class="table dt-responsive nowrap w-100  ">

											<thead class="thead-light">


												<th style="min-width: 25px;width: 25px;">Sno</th>
												<th style="min-width: 30px;width: 30px;">Id</th>
												<th>Inv No</th>
												<th>Po No</th>
												<th>Po Date</th>
												<th>Consignee</th>
												<th>Ship By</th>
												<th>Days Rem / Delayed </th>

												<th>Dest Port</th>
												<th>Dest Country</th>
												<th>Transfer Date </th>
												<th>Days </th>
												<th>Transport Date </th>
												<th>Container No </th>
												<th>Shipped On </th>
												<th>BL No. </th>
												<th>ETA</th>
												<th>Status </th>


											</thead>
											<tbody>
												<?php foreach ($AryPos as $LateRow) {
													if ($LateRow['ReturnedDate'] != '') {
														$CurrentStatus = 'Returned';
														$StatusColour = '#90EE90';
													} else if ($LateRow['ClosedDate'] != '') {
														$CurrentStatus = 'Closed';
														$StatusColour = '#90EE90';
													} else if ($LateRow['DeliveredDate'] != '') {
														$CurrentStatus = 'Delivered';
														$StatusColour = '#FFFF8F';
													} else if ($LateRow['ShippedOn'] != '') {
														$CurrentStatus = 'Freight';
														$StatusColour = '#ADD8E6';
													} else if ($LateRow['TransportedDate'] != '') {
														$CurrentStatus = 'Transported';
														$StatusColour = '#A9A9A9';
													} else if ($LateRow['TransferDate'] != '') {
														$CurrentStatus = 'Transferred';
														$StatusColour = '#A9A9A9';
													} else if ($LateRow['ProductionStartedDate'] != '') {
														$CurrentStatus = 'Production';
														$StatusColour = '#FA8072';
													} else {
														$CurrentStatus = 'Issued';
														$StatusColour = '#FFFFFF';
													}

													if ($LateRow['DaysRemaining'] > 0 && $LateRow['DaysRemaining'] < 6) {
														$RowStatus = "warning-row";
														$StatusColour = $warningColor;
													}



													if ($LateRow['DaysRemaining'] > 0 && $LateRow['DaysRemaining'] < 6) {
														$RowStatus = "warning-row";
														$StatusColour = $warningColor;
													} else if ($LateRow['DaysRemaining'] <= 0 && $LateRow['TransportedDate'] == '') {
														$StatusColour = $deadlineColor;
														$RowStatus = "deadline-row";
													} else {
														$RowStatus = "success-row";
													}

													if ($LateRow['CInvNo'] != '' && $LateRow['TransportedDate'] != '') {
														$RowStatus = "success-row";
													}


													if ($LateRow['ReturnedDate'] != '') {
														$StatusColour = $deadlineColor;
														$RowStatus = "deadline-row";
													}

													if ($LateRow['TransportedDate'] != '') {
														$Days = $LateRow['DelayedDays'];
														if ($Days > 0) {
															$StatusColor = 'green';
														} else {
															$StatusColor = 'red';
														}
													} else {
														$StatusColor = 'blue';
														$Days = $LateRow['DaysRemaining'];
													}

													switch ($RowStatus) {
														case "success-row":
															$successCount++;
															break;
														case "warning-row":
															$warningCount++;
															break;
														case "deadline-row":
															$deadlineCount++;
															break;
														default:
															break;
													}




													//echo checkEmpty($LateRow['CInvNo']);
												
													//ECHO $LateRow['TransferDate'];
													//EXIT;
												
													?>

													<tr class="<?php echo $RowStatus . ' ' . $CurrentStatus ?>">
														<td><?php echo ++$i ?></td>

														<td>
															<a target="_blank" id="PoDetails" href="#"
																data-id="<?php echo $LateRow['PoID'] ?>"
																data-value="<?php echo $LateRow['PoNo'] ?>"
																class="fill-div"><?php echo $LateRow['PoID'] ?> </a>
														</td>
														<td><?php echo checkEmpty($LateRow['CInvNo']) ?></td>
														<td><?php echo checkEmpty(substr($LateRow['PoNo'], 0, 20) . '<br>' . substr($LateRow['PoNo'], 20)) ?>
														</td>
														<td data-order="<?php echo checkEmpty($LateRow['PoDate']); ?>">
															<?php echo checkEmpty(getFormatedDate($LateRow['PoDate'])) ?>
														</td>
														<td><?php echo checkEmpty(substr($LateRow['ConsigneeName'], 0, 30) . '<br>' . substr($LateRow['ConsigneeName'], 30)) ?>
														</td>
														<td><?php echo checkEmpty(getFormatedDate($LateRow['ShipByDate'])) ?>
														</td>
														<td>
															<div class=" badge badge-warning font-size-10 "
																style="background:<?php echo $StatusColor; ?>!important;">
																<?php echo $Days; ?>
															</div>
														</td>
														<td><?php echo checkEmpty($LateRow['PortOfDischarge']) ?></td>
														<td><?php echo checkEmpty($LateRow['CountryName']) ?></td>
														<td><?php echo checkEmpty(getFormatedDate($LateRow['TransferDate'])); ?>
														</td>
														<td> <?php
														// Determine the date to use (either TransportedDate or TransferDate)
														$finalDate = !empty($LateRow['TransportedDate'])
															? $LateRow['TransportedDate']
															: (!empty($LateRow['TransferDate']) ? $LateRow['TransferDate'] : date("Y-m-d"));
														echo checkEmpty(getDaysDifference($finalDate, $LateRow['ProductionStartedDate']));
														?></td>
														<td><?php echo checkEmpty(getFormatedDate($LateRow['TransportedDate'])) ?>
														</td>
														<td><?php echo checkEmpty($LateRow['ContainerNo']) ?></td>
														<td><?php echo checkEmpty(getFormatedDate($LateRow['ShippedOn'])) ?>
														</td>
														<td><?php echo checkEmpty($LateRow['BLNumber']) ?></td>
														<td><?php echo checkEmpty(getFormatedDate($LateRow['ETA'])) ?></td>

														<td><?php echo $CurrentStatus ?></td>

													</tr>

												<?php } ?>
											</tbody>
										</table>
										<!-- <div class="mt-3 mb-3">Showing 1 to <?php echo count($AryPos) ?> out of
											<?php echo count($AryPos) ?> entries
										</div> -->
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- end row -->

				</div> <!-- container-fluid -->
			</div>
			<!-- End Page-content -->

			<?php
			include($IncludesPath . "footer.php");

			// echo file_get_contents($BaseDist."partials/footer.php"); 
			?>
		</div>
		<!-- end main content-->

	</div>
	<!-- END layout-wrapper -->
	<?php


	include($IncludesPath . "vendor-scripts.php");
	include($IncludesPath . "NewScripts.php");

	?>

	<script>
		function fnShowDataTable() {
			var table = $('#key-datatable').DataTable({
				scrollY: "600px",
				scrollX: true,
				sScrollXInner: "150%",
				paging: false,
				destroy: true,
				fixedColumns: true,
				scrollCollapse: true,
				bInfo: true,
				responsive: false,
				initComplete: function () {
					showCounts();
				}

			});

			table.on('draw.dt', function () {
				showCounts();
			});


		}

		function showCounts() {
			$('#deadline-count').html(jQuery('#key-datatable tbody tr.deadline-row').length)
			$('#warning-count').html(jQuery('#key-datatable tbody tr.warning-row').length)
			$('#success-count').html(jQuery('#key-datatable tbody tr.success-row.Issued').length + jQuery('#key-datatable tbody tr.success-row.Production').length)
		}



		$(document).ready(function () {
			fnShowDataTable();

			let UnitID = <?php echo $UnitID ?>;
			if (UnitID != 0) {
				$('#drpPlant').val(UnitID).attr('disabled', true)
			}


		});
	</script>
	<script>
		$(document).on('click', '#PoDetails', function (e) {
			e.preventDefault();

			var PoNo = $(this).data('value');
			var PoID = $(this).data('id');

			$('#Update').val(PoID);

			showPoPopUp(PoNo, PoID);




		});

		function showPoPopUp(PoNo, PoID) {

			$.ajax({
				url: 'Ajax/AjaxPopUpPoDetails.php',
				type: 'POST',
				data: {
					'PoID': PoID
				},
				success: function (data) {
					//  $("#Update").hide();

					$('.modal-title').text('PO Status Details - ' + PoNo);
					$("#PopUpTable").html(data);

					$('#myModal').modal('show');
				}
			});

		}



		$(document).on('click', '#list', function (e) {



			// alert(Url);
			var SelectedDate = $('#Year').val();
			var SelectedPlant = $('#drpPlant').val();
			var SelectedExporter = $('#drpExporter').val();
			// alert(SelectedDate);

			var Url = '<?php echo $CurrentUrl ?>?Year=' + SelectedDate;
			if (SelectedPlant && SelectedPlant != "")
				Url += '&PlantID=' + SelectedPlant;
			if (SelectedExporter && SelectedExporter != "")
				Url += '&ExporterID=' + SelectedExporter;
			window.open(Url, "_self");

			// alert(SelectedDate);

		});



		$(document).on('click', '#Update', function (e) {

			var PoID = $('#Update').data('id');
			var PoID = $('#Update').val();

			var Url = 'CreateShipmentStatus.php?PoID=' + PoID;
			window.open(Url, "_self");
		});

		$(document).on('click', '#BuyerDetails', function (e) {

			var ConID = $('#ConID').val();



			$.ajax({
				url: 'Ajax/AjaxBuyerDetails.php',
				type: 'POST',
				data: {
					'ConID': ConID
				},
				success: function (data) {

					//$('.modal-title').text('Do You Want To Shipped This Po '+INT_LOT_NUM+' ?');
					$("#PopUpTable").html(data);
					$('#PopUpTable').DataTable({
						"ordering": false
					});
					$('#myModal').modal('show');



					//$('#myModal').modal('show');
				}
			});

		});
	</script>


</body>

</html>
