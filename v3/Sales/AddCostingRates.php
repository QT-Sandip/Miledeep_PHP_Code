<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$PageTitle = "Costing Rates";
$PageTitleFolder = "Sales";

$SType = $_GET['SType'];

$TodayDate = date('Y-m-d');

if (isset($_GET['id'])) {
	$IsEdit = 1;
	//   $DateId=base64_url_decode($_GET['id']);
	$DateId = $_GET['id'];
} else {
	$IsEdit = 0;
}
//if($_SESSION["DeptID"]!=8 || $_SESSION["AccessType"]!=1){
/*if($_SESSION["DeptID"]!=8 || $_SESSION["AccessType"]!=2){
header('Location: index.php?Msg=You Dont Have Access To Change Purchase Rates ');
}*/


function getCostingRates($iconn, $LOT_ID)
{

	global $SalesCostingRates, $SType;

	$size = 40;
	$size1 = 60;
	$textw = '30%';
	$text = 2;

	$IsEdit = false;
	if (isset($_GET['id'])) {
		$IsEdit = true;
		$DateId = $_GET['id'];
		$sql = "SELECT * FROM SalesCostingRates WHERE TrackID='$DateId' order by CostID asc";
	} else {
		$sql = "select * from SalesCostingRatesRange where SType ='$SType' order by ABS(Grade) ASC ";
	}
	// echo $sql; 
	$stmt = $iconn->query($sql);
	$content = '<table >
							
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
				<tr>
				
				
				<th  align="CENTER" width="4%"><font size="' . $text . '"> <B>Grade</B></font></th>
				<th  align="CENTER" width="4%"><font size="' . $text . '"> <B>Rate (USD)</B></font></th>
				
				</tr>
				</thead>
				';

	$count = 0;
	while ($PostDetails = $stmt->fetch_array()) {
		$CostRateID = $count;

		$content .= '<th  id="Grade' . $CostRateID . '"  align="CENTER" width="4%"><font size="' . $text . '"><B>' . $PostDetails['Grade'] . '</B></font></th>';
		if ($IsEdit) {
			$content .= '<th><input required type="text" step="0.5" class="form-control"  name="DayCode" id="DayCode' . $CostRateID . '
				
				"placeholder="Enter " value="' . $PostDetails['USD'] . '"></th></tr>';
		} else {
			$content .= '<th><input required type="text" step="0.5" class="form-control"  name="DayCode" id="DayCode' . $CostRateID . '
				
				"placeholder="Enter " value=""></th></tr>';
		}


		$count++;
	}

	if (!$IsEdit) {
		$content .= '<tr>
				<th  align="CENTER" width="4%"><font size="' . $text . '">Select Date</th>
				<th  align="CENTER" width="4%"><font size="' . $text . '">
				 <input type="date" value="" max="' . $TodayDate . '";ass="form-control" name="SelectDate" id="SelectDate"></input>
				 </th>
				</tr>';
	}

	$content .= '</table>';

	$DATA[] = $count;
	$DATA[] = $content;
	$DATA[] = $IsEdit;
	//return json_encode($content);
	return $DATA;
}




?>



<!DOCTYPE html>

<html lang="en">
<!-- <link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" /> -->

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

					<!-- Main content -->


					<!-- Small boxes (Stat box) -->

					<div id="tdetails" class="table-responsive">
						<div align="center" class="card">

							<div class="card-body">

								<table id="CostingRatesTable" class="table table-bordered table-hover" style="width:50%">
								</table>

								<tr>

									<button id='SubmitBtn' value="" onClick="reply_click()" class="btn btn-success">
										Submit</button>

								</tr>
							</div>
						</div>


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
<script>
	$('document').ready(function() {
		var data = '<?php echo json_encode(getCostingRates($iconn, $LOT_ID)); ?>';

		var data = data.replace("[", "");
		var data = data.replace("]", "");
		var data = data.replace(/["']/g, "");
		var strArray = data.split(",");
		var IsEdit = strArray[2];


		$("#CostingRatesTable").html(strArray[1]);
		$("#SubmitBtn").val(strArray[0]);

	});
</script>


<script>
	function reply_click() {

		//  alert('Hi');

		var InvID = $("#SubmitBtn").val();
		var SelectDate = $("#SelectDate").val();
		var IsEdit = '<?php echo $IsEdit ?>';
		var DateId = '<?php echo $DateId ?>';
		var SType = '<?php echo $SType ?>';
		//alert(IsEdit); 
		if (!IsEdit && SelectDate == '') {
			alert("Please Date");
			document.getElementById("SelectDate").focus();
			return;
		}


		// 
		var Cost = [];
		var grades = [];
		for (i = 0; i < InvID; i++) {


			var DayCodeID = '#DayCode' + i;
			var GradeID = '#Grade' + i;
			var Grade = $(GradeID + "").text();
			var DayCode = $(DayCodeID + "").val();
			Cost.push(DayCode);
			grades.push(Grade);



			if (!Number(DayCode)) {
				alert('Only Number Allowed for Count ' + Grade);

				document.getElementById("DayCode" + i).focus();

				return;

			}

			if (DayCode == '' || DayCode == 0) {
				alert('Enter Price for  ' + Grade);

				document.getElementById("DayCode" + i).focus();

				return;

			}
		}
		$('#myModal').modal('show');

		$.ajax({
			url: 'Ajax/AjaxUpdateCostingRates.php',
			type: 'POST',
			data: {
				'IsEdit': IsEdit,
				'DateId': DateId,
				'Grades': grades,
				'Rates': Cost,
				'SelectDate': SelectDate,
				'SType': SType
			},
			success: function(data) {

				if (data != '') {
					alert('Success');
					console.log(data);
					// window.location = "coasting-rates-history";
					// window.location = "index.php?Msg=Costing Rates Updated Successfully";
					window.location = "Reports/coasting-rates-print.php?id="+data;
				} else {
					window.location = "index.php?Msg=Costing Rates Updated Successfully";

					alert(data);
				}


			}
		});


		$(document).ajaxStart(function() {
			$('#myModal').modal('show');
		});
		$(document).ajaxComplete(function() {
			$('#myModal').modal('hide');
		});
	}
</script>


<script>
	$(document).ready(function() {
		showData();



	});




	function showData(data) {

		$("#ClientList").html(data);

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