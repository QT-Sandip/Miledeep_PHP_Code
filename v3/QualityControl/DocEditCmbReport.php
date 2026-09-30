<?php
/*
ini_set('display_errors', 1);
error_reporting(E_ALL);*/
$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

extract($_POST);
extract($_GET);


//$ClientID=$_SESSION["ClientID"];
$REFERENCE_ID = $_GET['RID'];

$PageTitle = "Edit Microbiology Report";

$PageTitleFolder = "Quality Control";



$PoID = $_GET['PoID'];
$COUNTRY = $_GET['COUNTRY'];

$sql_invoice = "SELECT * FROM `ScrmPoData` where PoID='$PoID'";
$stmt_invoice = $iconn->query($sql_invoice);
$invoice_details = $stmt_invoice->fetch_array();

$sql = "SELECT * FROM `QcMBReport` where ID='$REFERENCE_ID'";
$stmt = $iconn->query($sql);
$post_details = $stmt->fetch_array();
$country = $post_details['Country'];
/*
$sql_varities="SELECT * FROM `VARITIES_DATA`";
$stmt_varities=$iconn->query($sql_varities);
*/
$SqlLab = "SELECT * FROM `QcLabTechnologist` where LbuStatus=1";
$StmtLab = $iconn->query($SqlLab);
$StmtLab1 = $iconn->query($SqlLab);


if ($REFERENCE_ID != '') {

	/* $sql="SELECT * FROM `TEST_RESULT_PARMS` where TEST_ID='4' and COUNTRY='$country'"; 
$stmt=$iconn->query($sql);	
$TEST_RESULT_PARMS=$stmt->fetch_array();
*/
	$sql = "select * from QcMBResult where MbReportID='$REFERENCE_ID'  ";

	$stmt = $iconn->query($sql);
}



if (isset($_POST['submit'])) {
	$D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));


	$sql_update = " UPDATE `QcMBReport` SET `TypeProduct`='$TypeProduct',`ReportID`='$ReportNo',
	`TestedBy`='$TestedBy',`VerifiedBy`='$VerifiedBy',`InvID`='$PoID'
	WHERE ID='$REFERENCE_ID'";


	$stmt1 = $iconn->query($sql_update);
	//$M_T_REPORT_ID = $iconn->insert_id;
	//echo $iid;
	if ($stmt1) {
		header('Location: DocAddReports.php?Q=' . $QueryParm);
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


											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Test Report Number</label>
												<input required type="text" class="form-control" name="ReportNo" id="ReportNo"
													value="<?php echo $post_details['ReportID']; ?>" placeholder="Enter Test Report Number">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Product Type</label>
												<select class="form-control select2" name="TypeProduct" id="TypeProduct">



													<option value="Raw"
														<?php if ($post_details['TypeProduct'] == 'Raw') echo 'selected="selected"'; ?>>Raw</option>
													<option value="COOKED"
														<?php if ($post_details['TypeProduct'] == 'COOKED') echo 'selected="selected"'; ?>>COOKED</option>

													</option>

													<select>
											</div>




											<!--<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Country</label>
                 <select class="form-control select2" name="Country" id="Country">
				 <option value="CHINA" <?php if ($post_details['Country'] == "CHINA") echo 'selected="selected"'; ?> >CHINA</option>
				 <option value="USA" <?php if ($post_details['Country'] == "USA") echo 'selected="selected"'; ?> >USA</option>
				  <option value="NEWZEALAND" <?php if ($post_details['Country'] == "NEWZEALAND") echo 'selected="selected"'; ?> >NEWZEALAND</option>
				   <option value="GENERAL" <?php if ($post_details['Country'] == "GENERAL") echo 'selected="selected"'; ?> >GENERAL</option>
				  <select>               

			   </div>-->



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Tested By</label>

												<select class="form-control select2" name="TestedBy" id="TestedBy">
													<?php while ($RowLab = $StmtLab->fetch_array()) { ?>
														<option <?php if ($post_details['TestedBy'] == $RowLab['LbuName']) { ?> selected="selected" <?php } ?>value="<?php echo $RowLab['LbuName'] ?>"><?php echo $RowLab['LbuName'] ?></option>

													<?php  } ?>
												</select>



											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Verified By </label>

												<select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
													<?php while ($RowLab = $StmtLab1->fetch_array()) { ?>
														<option <?php if ($post_details['VerifiedBy'] == $RowLab['LbuName']) { ?> selected="selected" <?php } ?>value="<?php echo $RowLab['LbuName'] ?>"><?php echo $RowLab['LbuName'] ?></option>

													<?php  } ?>
												</select>
											</div>


										</div>


										<div class="col-xs-12 box-footer" allign="right">


											<a href="DocAddCombinedResult.php?RID=<?php echo $REFERENCE_ID ?>&Q=<?php echo $QueryParm ?>&TYPE=ADD" class="btn btn-primary"><i class="fa fa-plus"></i> Add Result</a>
											<a href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>
											<button type="submit" name='submit' class="btn btn-success">Submit</button>

										</div>
								</form>

							</div> <!-- end row -->

						</div> <!-- end card body-->
					</div> <!-- end card -->






					<div id="tdetails" class="table-responsive col-lg-12">
						<div class="card">

							<div class="card-body" id="ABReportList">


								<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

									<thead class="thead-light">

										<tr>



											<!--<?php if ($TEST_RESULT_PARMS['PROD_REG'] != 'NA') { ?>
		<th><?php echo $TEST_RESULT_PARMS['PROD_REG']; ?></th>
		<?php } ?>
		
		<?php if ($TEST_RESULT_PARMS['DOA_DOC'] != 'NA') { ?>
		<th><?php echo $TEST_RESULT_PARMS['DOA_DOC']; ?></th>
		<?php } ?>
		
		<?php if ($TEST_RESULT_PARMS['VARITY'] != 'NA') { ?>
		<th><?php echo $TEST_RESULT_PARMS['VARITY']; ?></th>
		<?php } ?>
		<?php if ($TEST_RESULT_PARMS['TPC'] != 'NA') { ?>
		<th><?php echo $TEST_RESULT_PARMS['TPC']; ?></th>
		<?php } ?>
		-->
											<th>Day Code </th>
											<th>Traceability</th>
											<th>Date of Analysis</th>
											<th>Date Of Completion</th>
											<th>Tpc</th>
											<th>Actions</th>



										</tr>

									</thead>
									<tbody>



										<?php

										while ($row = $stmt->fetch_array()) {
											$CLID = $row['CLID'];

											$SqlCode = "SELECT * FROM `$TblQcCodeList` as cl LEFT JOIN $TblQcTracebilityList as tl on tl.TraceID=cl.TracebilityID
LEFT JOIN $TblQcPoVarityList as pv on pv.VarietyID=cl.VarietyID

WHERE CLID='$CLID'";
											$stmtCode = $iconn->query($SqlCode);
											$RowCode = $stmtCode->fetch_array();



										?>





											<!-- <?php if ($post_details['PROD_REG'] != 'NA') { ?>
		<td id="<?php echo $row['ID']; ?>" > <?php echo $RowCode['DayCode'] . '&' . $RowCode['Tracebility']; ?></td>
		<?php } ?>
		
		<?php if ($post_details['DOA_DOC'] != 'NA') { ?>
		<td id="c_name<?php echo $row['ID']; ?>"><?php $DOA_DOC = $row['DOA_DOC'];
													echo $row['DateOfAnalysis'] . '/' . $row['DateOfIssuance']; ?></td>
		<?php } ?>
		
		<?php if ($post_details['TypeProduct'] != 'NA') { ?>
		<td id="a_type<?php echo $row['ID']; ?>"><?php echo $RowCode['ShortName']; ?></td>

		<?php } ?>
		<?php if ($post_details['TPC'] != 'NA') { ?>
		<td id="p_number<?php echo $row['ID']; ?>"><?php echo $row['Tpc']; ?></td>
		<?php } ?>
		
		<td >  	
	 	 <button id="<?php echo $row['ID']; ?>" onClick="reply_click(this.id)" class=" glyphicon glyphicon-remove btn btn-danger"> Delete</button>
		 
		 <button class="btn btn-link text-danger Delete" 
		data-id="<?php echo $row['ID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>
	   </td>		
		-->
											<td> <?php echo $RowCode['DayCode']; ?></td>
											<td> <?php echo $RowCode['Tracebility']; ?></td>
											<td> <?php echo $row['DateOfAnalysis']; ?></td>
											<td> <?php echo $row['DateOfIssuance']; ?></td>
											<td> <?php echo $row['Tpc']; ?></td>
											<td>
												<a href="DocAddCombinedResult.php?RID=<?php echo $REFERENCE_ID ?>&Q=<?php echo $QueryParm ?>&TYPE=EDIT&edit_id=<?php echo $row['ID']; ?>&clid=<?php echo $RowCode['CLID']; ?>&doa=<?php echo $row['DateOfAnalysis']; ?>&doc=<?php echo $row['DateOfIssuance']; ?>&tpc=<?php echo $row['Tpc']; ?>&listeria=<?php echo $row['ListeriaValue']; ?>"
													class="btn btn-link Edit" data-id="<?php echo $row['ID']; ?>" data-toggle="tooltip" data-placement="top" title="Edit">
													<i class="mdi mdi-pencil font-size-16"></i>
												</a>
												<button class="btn btn-link text-danger Delete"
													data-id="<?php echo $row['ID']; ?>" data-toggle="tooltip" data-placement="top"
													title="Delete" value="" data-original-title="Delete">
													<i class="mdi mdi-trash-can font-size-16"></i></button>
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

<script type="text/javascript">
	$('document').ready(function() {

		showData(data);
	});
	$(document).on('click', '.Delete', function(e) {
		var clicked_id = $(this).data('id');
		var ref_id = '<?php echo $REFERENCE_ID ?>';



		var r = confirm("Do you Want To Delete?");
		if (r == true) {

			$.ajax({
				url: 'Ajax/AjaxBDdelete.php',
				type: 'POST',
				data: {
					'Type': 'MBResultDelete',
					'D_ID': clicked_id,
					'ref_id': ref_id
				},
				success: function(data) {
					console.log(data);
					alert("Deleted");
					location.reload();

				}
			});

		} else {}
	});



	/*function reply_click(clicked_id)
  {
	 
     
	  
	  	
	  var ref_id='<?php echo $REFERENCE_ID ?>';
		 
		  
		$.ajax({
		  url:'Ajax/AjaxBDdelete.php',
		  type:'POST',
		  data:{'Type':'MBResultDelete','D_ID':clicked_id,'ref_id':ref_id},
		  success:function(data){
			  console.log(data);
			  
			 $("#PACK_SIZE").html(data);
			 alert("Deleted");
			 location.reload();
			}
			});	
			
			
					 
  }*/
</script>



<Script>
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

	$('#btn_close').on("click", function() {
		window.open('', '_parent', '');
		window.close();
	});
</script>