<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");
$ClientConfigData = $_SESSION['ClientConfigData'];
$EnableTrackCodeInAbReport = $ClientConfigData['EnableTrackCodeInAbReport'];//anil
$PageTitle = "Antibiotic Reports List";

$PageTitleFolder = "Quality Control";

$Currentyear = date('Y');
$CurrentMonth = date('m');


$Type = 0;
$uid = $_SESSION['UserID'];

if ($_SESSION["UserID"] == '') {
	header('Location: index.php?msg=Please enter your User name and Password.');
}
$PlantList = [];
if(!empty($EnableTrackCodeInAbReport)){
	$PlantSql = "SELECT ProcessingStationID,
						CASE 
						WHEN `ReportName` IS NOT NULL THEN `ReportName` ELSE `StationName` END AS `StationName`
				FROM RmProcessingStations";
	$stmtPlant=$iconn->query($PlantSql);
	
	while ($Plantdetails = $stmtPlant->fetch_assoc()){
		$PlantList[] =  $Plantdetails;
	}
}
?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css" rel="stylesheet" type="text/css" />

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

					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">

									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<label>Select Year </label>

												<select class="form-control select2" name="YEAR" id="YEAR">
													<?php
													for ($Startyear; $Startyear <= $endYear; $Startyear++) { ?>

														<option <?php if ($Startyear == $Currentyear) { ?> selected="selected" <?php } ?> value="<?php echo $Startyear ?>"><?php echo $Startyear ?></option>

													<?php }   ?>
												</select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label>Select Month </label>

												<select class="form-control select2" name="MONTH" id="MONTH">
													<?php
													for ($i = 0; $i < 12; $i++) {
														$time = strtotime(sprintf('%d months', $i), $start);
														$label = date('F', $time);
														$value = date('m', $time); ?>
														<option <?php if ($value == $CurrentMonth) { ?> selected="selected" <?php } ?> value="<?php echo $value ?>"><?php echo $label ?></option>


													<?php       }  ?>
													<select>
											</div>
										</div>
										<?php 
										if(!empty($EnableTrackCodeInAbReport)){?>
											<div class="form-group col-sm-3">

												<label for="PlantId">Plant</label>

												
													<select class="form-control select2" name="PlantId" id="PlantId">
													<!-- <option value="">Select</option> -->
													<?php foreach ($PlantList as $row){ ?>
														<option value="<?php echo $row['ProcessingStationID'] ?>"
														<?php if($row['ProcessingStationID']==$ROW_CODE_LIST['PlantId']) echo 'selected="selected"';?>
														><?php echo $row['StationName'] ?></option>
													
													<?php  }?>
												
														
												</select>
										
											</div> 
										<?php } ?> 
									</div>

									<div align="center" class="col-lg-12">

										<button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light"> Search<i class='ri-arrow-right-line align-middle ml-2'>
											</i></button>

										<!--  <a href="DailyLabABCreate.php?DOA=<?php echo date("d-m-Y") ?>&DOR=<?php echo date('d-m-Y', strtotime("+1 day")); ?>&Year=<?php echo  $Currentyear; ?>" 
				  class="btn btn-primary">Create Report</a> 
-->
										<button id="createreport" href="#" class="btn btn-success">Create Report</button>
									</div>




								</div>
								<!-- end row -->
							</div>
						</div>
					</div>

					<!-- end col -->
				</div> <!-- end row -->
				<div class="container-fluid">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body" id="ABReportList">


								</div> <!-- end card body-->
							</div> <!-- end card -->
						</div><!-- end col-->
					</div>
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

</body>

</html>

<script>
	$('document').ready(function() {
		<?php 
		if(!empty($EnableTrackCodeInAbReport)){?>
			var PlantId = "<?php echo !empty($_SESSION['UnitID']) ? $_SESSION['UnitID'] : '';?>";
			if(PlantId){
				
				$("#PlantId option").each(function () {
					if ($(this).val() == '') {
						return true;
					}
					if ($(this).val() !== PlantId) {
						$(this).remove();
					}
				});
			}

			var isFound = false;

			$("#PlantId option").each(function () {
				if ($(this).prop("selected") && $(this).val() != '') {
					isFound = true;
					return false;
				}
				if (parseInt($(this).val()) === parseInt(PlantId) && PlantId != 0) {
					isFound = true;
					$("#PlantId").val(PlantId).trigger('change');
					return false; // break loop
				}
			});

			//$("#PlantId").prop("disabled", isFound);
		<?php }?>
		$(function() {
			$('#list').click(function() {


				var Month = $('#MONTH').val();
				var Year = $('#YEAR').val();
				var Type = <?php echo   $Type ?>;



				$.ajax({
					url: 'Ajax/AjDlyLbRprts.php',
					type: 'POST',
					data: {
						'Year': Year,
						'Month': Month,
						'Type': Type,
						'PlantId': $('#PlantId').val()
					},
					success: function(data) {

						showData(data);
					}
				});
			});


			$('#createreport').click(function() {


				var Month = $('#MONTH').val();
				var Year = $('#YEAR').val();
				var Type = <?php echo   $Type ?>;
				var queryString = '';
				<?php 
				if(!empty($EnableTrackCodeInAbReport)){?>
					var PlantId = $('#PlantId').val();
					queryString = '&PlantId='+PlantId
				<?php }?>

				var today = new Date();

				var DOR = (today.getDate() + 1).toString().padStart(2, "0") + '-' + (today.getMonth() + 1).toString().padStart(2, "0") + '-' + today.getFullYear();
				var DOA = (today.getDate()).toString().padStart(2, "0") + '-' + (today.getMonth() + 1).toString().padStart(2, "0") + '-' + today.getFullYear();



				window.open("RmAbReport.php?DOA=" + DOA + "&DOR=" + DOR + "&Year=" + Year + "&Month=" + Month+queryString, "_blank");
			});



		});

	});






	$(document).ajaxStart(function() {
		$("#wait").css("display", "block");
	});
	$(document).ajaxComplete(function() {
		$("#wait").css("display", "none");
	});
</script>


<Script>
	$('document').ready(function() {

		$(document).on('change', '#DATA_TYPE', function(e) {

			e.preventDefault();



			var DATE = $('#sms').val();
			var DATA_TYPE = $('#DATA_TYPE').val();
			//alert(DATE);
			$.ajax({
				url: 'ajax/sendsms.php',
				type: 'POST',
				data: {
					'TYPE': 'SHOW_DATA',
					'DATE': DATE,
					'DATA_TYPE': DATA_TYPE
				},
				success: function(data) {
					console.log(data);

					$('#FULL_NAME').text('');
					var string = data.toString();

					var lines = string.split('<br>');

					$('.modheading').text('Send SMS');

					var number = lines.length;
					container = document.getElementById('FULL_NAME');

					//container.innerHTML = lines;




					for (i = 0; i < number; i++) {
						container.appendChild(document.createTextNode(lines[i] + "\n"));
						var input = document.createElement("input");
						input.type = "number";
						container.appendChild(input);
					}


				}

			});




		});

		$(document).on('click', '#sms,#D_F_NAME', function(e) {
			e.preventDefault();
			$('#FULL_NAME').text('');


			var DATE = $(this).val();
			var DATA_TYPE = $('#DATA_TYPE').val();
			//alert(DATE);
			$.ajax({
				url: 'ajax/sendsms.php',
				type: 'POST',
				data: {
					'TYPE': 'SHOW_DATA',
					'DATE': DATE,
					'DATA_TYPE': DATA_TYPE
				},
				success: function(data) {
					console.log(data);
					encodeURIComponent("\n")
					//  var obj = JSON.parse(data);
					var string = data.toString();

					var lines = string.split('<br>');

					$('.modheading').text('Send SMS');


					var number = lines.length;
					container = document.getElementById('FULL_NAME');

					//container.innerHTML = lines;


					//container.style.whiteSpace = 'pre';

					for (i = 0; i < number; i++) {
						container.appendChild(document.createTextNode(lines[i] + "\n"));
						var input = document.createElement("input");
						input.type = "number";
						container.appendChild(input);
					}


					//  $('#FULL_NAME').text(lines);

					$('#myModal').modal('toggle');
				}

			});

		});



		$(document).on('click', '#addSHORTNAme', function(e) {
			e.preventDefault();



			var NUMBER = $('#SHORT_NAME').val();
			var MSG = $('#FULL_NAME').val();

			$.ajax({
				url: 'ajax/sendsms.php',
				type: 'POST',
				data: {
					'TYPE': 'MSG',
					'NUMBER': NUMBER,
					'MSG': MSG
				},
				success: function(data) {
					console.log(data);
					var obj = JSON.parse(data);

					alert(obj.status);


					$('.modheading').text('Send SMS');
					$('#FULL_NAME').val('');

					//$('#myModal').modal('toggle');						
				}

			});

		});


		$('#continuebtn').on('click', function() {

			$('form').submit();
		});
	});

	function showData(data) {

		$("#ABReportList").html(data);

		var table = $('#key-datatable').DataTable({
			scrollY: "600px",
			scrollX: true,
			//sScrollX: "100%",
			sScrollXInner: "100%",
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
			},language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],
		});
	}
</script>
