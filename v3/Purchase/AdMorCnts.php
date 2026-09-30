<?php
$Status = $_SESSION['Status'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");



$LotId = $_GET['ID'];
/*$sql_LOTS="SELECT * FROM `$RmLotDetatils` where RmLotID='$LotId'";
$stmt_LOTS=$iconn->query($sql_LOTS);
//$ROW_LOTS=$stmt_LOTS->fetch_array();
*/
$SqlLots="SELECT * FROM `$RmLotDetatils` where RmReferenceID='$LotId'";
$StmtLots=$iconn->query($SqlLots);
/*
$SqlLots = "SELECT * FROM `$RmLotDetatils` where RmLotID='$LotId'";
$StmtLots = $iconn->query($SqlLots);
*/





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
							$_SESSION['Status'] = ''; ?>
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
												<label for="exampleInputEmail1">Enter Count</label>
												<input required type="text" class="form-control" name='PurchaseCount'
													id="PurchaseCount" value="" placeholder="Enter Count">
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Enter Weight</label>
												<input required type="text" class="form-control" name='PurchaseWeight'
													id="PurchaseWeight" value="" placeholder="Enter Weight">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Enter Net Weight</label>
												<input required type="text" class="form-control" name='NetWeight'
													id="NetWeight" value="" placeholder="Enter Net Weight">
											</div>



								</form>


								<div class="col-xs-12 box-footer" align="right">
									<button id="AddCount" class="btn btn-success"><span
											class="glyphicon glyphicon-plus"></span>ADD</button>
								</div>
							</div> <!-- end row -->

						</div> <!-- end card body-->
					</div> <!-- end card -->
				</div><!-- end col-->
			</div>
			<!-- end row-->

		</div> <!-- container-fluid -->
	




	<div class="col-12">
		<div class="card">
			<div class="card-body" id="CountDetails">

				<table id="datatable-buttons" class="table table-striped 
										table-bordered dt-responsive nowrap">
					<thead>
						<tr>

							<th align='center'>Lot No</th>
							<th align='center'>Weight</th>
							<th align='center'>Count</th>
							<th align='center'>Net Weight</th>
							<th align='center'>Delete</th>

						</tr>
					</thead>
					<tbody>

						<?php

						while ($RowLots = $StmtLots->fetch_array()) {

							?>
							<tr>

								<td id="<?php echo $RowLots['RmLotID']; ?>"> <?php echo $RowLots['LotNumber']; ?> </td>
								<td id="<?php echo $RowLots['RmLotID']; ?>"> <?php echo $RowLots['PurchaseWeight']; ?> </td>
								<td id="<?php echo $RowLots['RmLotID']; ?>"> <?php echo $RowLots['PurchaseCount']; ?> </td>
								<td id="<?php echo $RowLots['RmLotID']; ?>"> <?php echo $RowLots['NET_WT']; ?> </td>
								<TD>
									<!--<button id="<?php echo $RowLots['RmLotID']; ?>"
	onClick="reply_click(this.id)" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Delete</button>
	-->

									<button class="btn btn-link text-danger Delete" onClick="reply_click(this.id)"
										id="<?php echo $RowLots['RmLotID']; ?>" data-toggle="tooltip" data-placement="top"
										title="" data-original-title="Delete"><i
											class="mdi mdi-trash-can font-size-18"></i></button>

								</TD>

							</tr>


							<?php

						}
						?>

					</tbody>

				</table>
			</div>
		</div>
		

	</div>
	<!-- end main content-->

	</div>
	<!-- END layout-wrapper -->
	<?php include($IncludesPath . "footer.php"); ?>


	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>



</body>

</html>


<script>
	$('document').ready(function () {
		showData();

		$(document).on('click', '#AddCount', function (e) {
			e.preventDefault();

			var LotId = '<?php echo $LotId ?>';

			var PurchaseCount = $('#PurchaseCount').val();
			var PurchaseWeight = $('#PurchaseWeight').val();
			var NetWeight = $('#NetWeight').val();


			var Validation = false;

			if (PurchaseCount == "") {
				alert("Enter Count");
				$('#PurchaseCount').focus();
			} else if (PurchaseWeight == "") {
				alert("Enter Weight");
				$('#PurchaseWeight').focus();
			}

			else if (NetWeight == "") {
				alert("Enter Net Weight");
				$('#NetWeight').focus();
			}

			else {
				Validation = true;
			}

			if (Validation == false) {
				return;
			}


			var Obj = [{
				'PurchaseWeight': PurchaseWeight,
				'PurchaseCount': PurchaseCount,
				'NetWeight': NetWeight
			}];

			var JsonObj = JSON.stringify(Obj);

			var DataObj = {
				'LotId': LotId,
				'DATA': JsonObj
			};




			alert(LotId);
			$.ajax({
				url: 'Ajax/AjaxPostSubLots.php',
				type: 'POST',
				data: DataObj,
				success: function (data) {
					//console.log(data);
					var obj = JSON.parse(data);
					if (obj.Error == null) {
						alert(obj.Msg);
						location.reload();
					} else {
						alert(obj.Error);
					}


				}
			});
		});

	});
</script>


<script>
	function reply_click(clicked_id) {
		//alert(clicked_id);
		//var ref_id='<?php echo $REFERENCE_ID ?>';
		var r = confirm("Are You Sure You Want To Delete " + clicked_id + "?");
		if (r == true) {
			$.ajax({
				url: 'Ajax/AjaxDelete.php',//AjDltAl.php
				type: 'POST',
				data: { 'Type': 'MoreCounts', 'ClickedId': clicked_id },
				success: function (data) {

					var obj = JSON.parse(JSON.stringify(data));

					//showData(data);
					if (obj.error == null) {
						alert("Count Deleted Successfully");
						//alert(obj.Msg);

						location.reload();

					} else {
						alert(obj.error);

					}


					/* console.log(data);
	   
					$("#PACK_SIZE").html(data);
					alert("Deleted");
					*/

				}
			});
		}
		else { }

	}

	function showData(data) {

		$("#CountDetails").html(data);

		var Stable = $('#scroll-vertical-datatable').DataTable({
			"scrollY": "450px",
			"scrollCollapse": true,
			"paging": false,
			"language": {
				"paginate": {
					"previous": "<i class='mdi mdi-chevron-left'>",
					"next": "<i class='mdi mdi-chevron-right'>"
				}
			},
			"drawCallback": function () {
				$('.dataTables_paginate > .pagination').addClass('pagination-rounded');
			},
			buttons: ['copy', 'excel', 'pdf', 'colvis']
		});

		Stable.buttons().container()
			.appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
	}



</script>


</html>