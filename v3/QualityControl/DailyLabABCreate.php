<?php


session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

extract($_POST);
extract($_GET);

$DOA = $_GET['DOA'];
$DOR = $_GET['DOR'];
$Year = $_GET['Year'];
$Month = $_GET['Month'];
//$Month=1;


$DateFilter = getDateFilter($Year, $Month);

$PageTitle = "Daily Lab AB Reports";

$PageTitleFolder = "Quality Control";
$DtofAnalysis = date("Y-m-d", strtotime($DOA));

$SqlLots = " select * from RmLotDetatils as RLD where RLD.PurchaseDate !='' 
  and RLD.PurchaseDate $DateFilter
				 and not exists (select 1 from QcRmAbReport QRAR where QRAR.LotID = RLD.RmLotID)
				 ORDER BY  RLD.PurchaseDate DESC, RLD.LotNumber";
$StmtLots = $iconn->query($SqlLots);

$SqlDropDownOptions = "SELECT * FROM `QcABOptions`  ORDER BY SortOrder ASC   ";
$StmtDropDownOptions = $iconn->query($SqlDropDownOptions);
$ArrayDropDownOptions = array();
while ($RowDropDownOptions = $StmtDropDownOptions->FETCH_ARRAY()) {
	$ArrayDropDownOptions[] = $RowDropDownOptions;
}

//$SqlRmAbReport="SELECT * FROM `$QcRmAbReport` where  `AnalysisDate`='$DtofAnalysis'  order by AbRepID desc";
$SqlRmAbReport = "SELECT * FROM `QcRmAbReport` as QRAR 
LEFT JOIN RmLotDetatils as RLD on RLD.RmLotID=QRAR.LotID 
 LEFT JOIN QcABUsers as QAU on QAU.UserID=QRAR.TestedBy where AnalysisDate='$DtofAnalysis'
 order by QRAR.ID desc";
$StmtRmAbReport = $iconn->query($SqlRmAbReport);
//doubt
//echo $SqlRmAbReport; exit;
$SqlABUsers = "SELECT * FROM `QcABUsers` ";
$StmtABUsers = $iconn->query($SqlABUsers);


if (isset($_POST['submit'])) {
	$LoginId = $_SESSION["UserID"];

	$DtofAnalysis = date("d-m-Y", strtotime($AnalysisDate));
	$DtofCompletion = date("d-m-Y", strtotime($ReportDate));
	$PostedDate = date("Y-m-d");

	$SqllotCount = "	SELECT * FROM `QcRmAbReport`  WHERE LotID='$LotId'";
	$StmtlotCount = $iconn->query($SqllotCount);
	$Count = mysqli_num_rows($StmtlotCount);

	if ($Count == 0) {
		$SqlAbReportInsert = "INSERT INTO `QcRmAbReport`
		( `AnalysisDate`, `ReportDate`,`PostedDate`,`LotID`,
     `Parameter`, `Sulphite` , `UserID`, `Remarks`, `Shift`, `TestedBy`,`AbStatus`) VALUES
	('$AnalysisDate','$ReportDate','$PostedDate','$LotId','$Parameter',
	'$Sulphite' ,'$LoginId','$Remarks','$Shift','$TestedBy','$AbStatus')";
		$StmtAbReportInsert = $iconn->query($SqlAbReportInsert);

		$Insertid = $iconn->insert_id;
		legacyAuditLogInsertById('QcRmAbReport', 'AbRepID', $Insertid, $_POST, ['Remarks' => 'Daily Lab AB Report Created'], 'QualityControl');
		echo "<script>alert('Success')</script>";

		header('Location: DailyLabABCreate.php?DOA=' . $DtofAnalysis . '&DOR=' . $DtofCompletion . '&Year=' . $Year . '&Month=' . $Month);
	} else {
		echo "<script>alert('Failed')</script>";

		header('Location: DailyLabABCreate.php?DOA=' . $DtofAnalysis . '&DOR=' . $DtofCompletion . '&Year=' . $Year . '&Month=' . $Month);
	}
}



?>







<!DOCTYPE html>

<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css" rel="stylesheet" type="text/css" />

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
					<div class="modal fade" id="myModal" role="dialog">

						<div class="modal-dialog">

							<!-- Modal content-->

							<div class="modal-content">

								<div class="modal-header">

									<button type="button" class="close" data-dismiss="modal">&times;</button>

									<h4 class="modal-title modheading"></h4>

								</div>

								<div class="modal-body">


									<div class="box-body" id="ATT_DATA">

									</div>




								</div>

								<div class="modal-footer">

									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>



								</div>

							</div>

						</div>

					</div>




					<!-- Main content -->



					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">



											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Lot No</label>

												<select class="form-control select2" name="LotId" id="LotId">
													<option Value=""> Select Lot</option>

													<?php while ($RowLotsList = $StmtLots->fetch_array()) {


														//$STATION_ID=$RowRmProcessingStations['ID'];			
													?>

														<option Value="<?php echo $RowLotsList['RmLotID']; ?>">
															<?php echo $RowLotsList['LotNumber']; ?></OPTION>
													<?php } ?>
													<select>
											</div>

											<div class="form-group col-lg-3">

												<label for="exampleInputEmail1">Count & Weight</label>


												<input readonly type="text" class="form-control" name='Count' id="Count" placeholder='Enter Count,Weight'>



											</div>


											<div class="form-group col-lg-3">

												<label for="exampleInputEmail1">Analysis Date</label>

												<input required type="date" class="form-control" name='AnalysisDate' id="AnalysisDate" value="<?php echo date("Y-m-d", strtotime($DOA)); ?>">

											</div>



											<div class="form-group col-lg-3">

												<label for="exampleInputPassword1">Report Date</label>

												<input required type="date" class="form-control" name='ReportDate' id="ReportDate" value="<?php echo date("Y-m-d", strtotime($DOR)); ?>">

											</div>








											<div class="form-group col-lg-3">

												<label for="exampleInputEmail1">Parameter</label>
												<select class="form-control select2" name="Parameter" id="Parameter">
													<option Value=""> Select </option>

													<?php foreach ($ArrayDropDownOptions as $DropDownOptions) {
														if ($DropDownOptions['Category'] == 'Parameter') {
															$Options = $DropDownOptions['Name'];
															$Value = $DropDownOptions['OptID']; ?>

															<option value="<?php echo $Value ?>"><?php echo $Options ?></option>
													<?php  }
													} ?>
												</select>

											</div>


											<div class="form-group col-lg-3">

												<label for="exampleInputPassword1"> Loading Status</label>

												<select class="form-control select2" name="AbStatus" id="AbStatus">
													<option Value=""> Select </option>

													<?php foreach ($ArrayDropDownOptions as $DropDownOptions) {
														if ($DropDownOptions['Category'] == 'TestStatus') {
															$Options = $DropDownOptions['Name'];
															$Value = $DropDownOptions['OptID']; ?>

															<option value="<?php echo $Value ?>"><?php echo $Options ?></option>
													<?php  }
													} ?>
												</select>



											</div>




											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Shift</label>
												<select class="form-control select2" name="Shift" id="Shift">
													<option Value=""> Select </option>

													<?php foreach ($ArrayDropDownOptions as $DropDownOptions) {
														if ($DropDownOptions['Category'] == 'Shift') {
															$Options = $DropDownOptions['Name'];
															$Value = $DropDownOptions['OptID']; ?>

															<option value="<?php echo $Value ?>"><?php echo $Options ?></option>
													<?php  }
													} ?>
													<select>
											</div>


											<div class="form-group col-lg-3">

												<label for="exampleInputPassword1">Tested By</label>


												<select class="form-control select2" name="TestedBy" id="TestedBy">
													<option Value=""> Select </option>

													<?php while ($RowABUsers = $StmtABUsers->fetch_array()) { ?>
														<option value="<?php echo $RowABUsers['UserID'] ?>"><?php echo $RowABUsers['UserName'] ?></option>

													<?php  } ?>


												</select>

											</div>

											<div class="form-group col-lg-3">

												<label for="exampleInputEmail1">Sulphite</label>


												<input required type="text" class="form-control" name='Sulphite' id="Sulphite" placeholder="Enter Sulphite">



											</div>




											<div class="form-group col-lg-3">

												<label for="exampleInputEmail1">Remarks</label>


												<input required type="text" class="form-control" name='Remarks' id="Remarks" placeholder="Enter Remarks">



											</div>
										</div>








										<div class="col-xs-12 box-footer" align="right">

											<!--<a href="DailyLabReports.php?id=0" class="btn btn-danger">Close</a>-->
											<a target="" href="#" onclick="window.close();" class="btn btn-danger  "> Close</a>


											<button type="submit" name='submit' class="btn btn-success">Submit</button>
										</div>
								</form>

							</div> <!-- end row -->

						</div> <!-- end card body-->
					</div> <!-- end card -->






					<div id="tdetails" class="table-responsive col-lg-12">
						<div class="card">

							<div class="card-body" id="ABReportList">






								<!--<table id="example1" class="table table-bordered table-hover">-->
								<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

									<thead class="thead-light">

										<tr>

											<th>Lot Number</th>
											<th>Parameter</th>
											<th>Loading Status</th>
											<th>Shift</th>
											<th>Remarks</th>
											<th>Tested By</th>
											<th>Actions</th>



										</tr>

									</thead>
									<tbody>



										<?php

										//echo $sql;



										while ($RowRmAbReport = $StmtRmAbReport->fetch_array()) {

											$AbRepID = $RowRmAbReport['AbRepID'];
											$LotID = $RowRmAbReport['LotID'];

											$SqlOptions = "SELECT *,
(SELECT Name from QcABOptions WHERE OptID=QcAb.AbStatus) as AbStatusDetails,
(SELECT Name from QcABOptions WHERE OptID=QcAb.Shift) as Shift,
(SELECT Name from QcABOptions WHERE OptID=QcAb.Parameter) as Parameter
FROM `QcRmAbReport` as QcAb WHERE LotID='$LotID'";
											$StmtOptions = $iconn->query($SqlOptions);
											$RowOptions = $StmtOptions->fetch_array();
										?>
											<tr>


												<td id="<?php echo $RowRmAbReport['AbRepID']; ?>"> <?php echo $RowRmAbReport['LotNumber']; ?></td>
												<td id="<?php echo $RowRmAbReport['AbRepID']; ?>"> <?php echo $RowOptions['Parameter']; ?></td>
												<td id="<?php echo $RowRmAbReport['AbRepID']; ?>"> <?php echo $RowOptions['AbStatusDetails']; ?></td>

												<td id="<?php echo $RowRmAbReport['AbRepID']; ?>"> <?php echo $RowOptions['Shift']; ?></td>
												<td id="<?php echo $RowRmAbReport['AbRepID']; ?>"> <?php echo $RowRmAbReport['Remarks']; ?></td>
												<td id="<?php echo $RowRmAbReport['AbRepID']; ?>"> <?php echo $RowRmAbReport['UserName']; ?></td>
												<td>

													<a target="_blank" href="DailyLabABEditt.php?AbRepID=<?php echo $AbRepID ?>" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>



												</td>

											</tr>

										<?php

										}

										?>





									</tbody>

								</table>
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
		showData(data);
	});


	$(document).on('change', '#LotId', function(e) {
		e.preventDefault();
		var LotId = $(this).val();
		var Type = 'Count';

		$.ajax({
			url: 'Ajax/AjInputvalues.php',
			type: 'POST',
			dataType: 'json',

			data: {
				'LotId': LotId,
				'Type': Type
			},
			success: function(response) {
				var myJSON = JSON.parse(JSON.stringify(response));

				//alert(myJSON['IECCode']);
				$("#Count").val(myJSON['PurchaseCount'] + 'C & Wt ' + myJSON['PurchaseWeight']);


			}
		})


	});

	function showData(data) {

		$("#ABReportList").html(data);

		var table = $('#key-datatable').DataTable({
			scrollY: "600px",
			scrollX: true,
			//sScrollX: "100%",
			sScrollXInner: "150%",
			// scrollCollapse:true,
			paging: true,
			destroy: true,
			fixedColumns: true,
			bSort: true,
			"bStateSave": true,
			"fnStateSave": function(oSettings, oData) {
				localStorage.setItem('key-datatable', JSON.stringify(oData));
			},
			"fnStateLoad": function(oSettings) {
				return JSON.parse(localStorage.getItem('key-datatable'));
			}
		});
	}
</script>
