<?php


session_start();

$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];
$MbReportID = $_GET['RID'];
$Type = $_GET['TYPE'];

$edit_id = isset($_GET['edit_id']) ? $_GET['edit_id'] : '';
$clid = isset($_GET['clid']) ? $_GET['clid'] : '';
$doa = isset($_GET['doa']) ? $_GET['doa'] : '';
$doc = isset($_GET['doc']) ? $_GET['doc'] : '';
$tpc = isset($_GET['tpc']) ? $_GET['tpc'] : '';
$listeria = isset($_GET['listeria']) ? $_GET['listeria'] : '';

if (!empty($doa)) {
	$doa_parts = explode('-', $doa);
	if (count($doa_parts) === 3) {
		$doa = $doa_parts[2] . '-' . $doa_parts[1] . '-' . $doa_parts[0];
	}
}
if (!empty($doc)) {
	$doc_parts = explode('-', $doc);
	if (count($doc_parts) === 3) {
		$doc = $doc_parts[2] . '-' . $doc_parts[1] . '-' . $doc_parts[0];
	}
}

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$ClientConfigData = $_SESSION['ClientConfigData'];
$ClientConfigData = (object) $_SESSION['ClientConfigData'];

$IsShowQcAddOnFields = $ClientConfigData->IsShowQcAddOnFields;  // uncomment this line after auth-login.php converted to API modal
// $IsShowQcAddOnFields = $ClientConfigData['IsShowQcAddOnFields']; // remove this line after auth-login.php converted to API modal


$SqlMbReport = "select * from QcMBReport where ID='$MbReportID'";
$StmtMbReport = $iconn->query($SqlMbReport);
$RowMbReport = $StmtMbReport->fetch_array();
$MbReporttID = $RowMbReport['ID'];

// $SqlMbResult = "select * from QcMBResult where MbReportID='$MbReporttID'";
// $StmtMbResult = $iconn->query($SqlMbResult);


$TypeProduct = $RowMbReport['TypeProduct'];
$InvID = $RowMbReport['InvID'];

if ($TypeProduct != '') {

	$SqlCodeList = "SELECT *,Group_concat(Grades) as Grades ,sum(McsTotal) as McsTotal  FROM QcCodeList  as CL 
 left join ScrmPoGradeData as SPGD on CL.PoGradeID=SPGD.ID
 left join ScrmPIVarietyList as PL on SPGD.VarietyID=PL.VarietyID
 left join QcTracebilityList as TL on TL.TraceID=CL.TracebilityID where CL.InvID='$InvID' 
 GROUP by DayCode,CL.PackingStyleID,TracebilityID,PL.ShortName,CL.Brand";
} else {


	$SqlCodeList = "SELECT *,Group_concat(Grades) as Grades,sum(McsTotal) as McsTotal FROM QcCodeList  as CL 
	left join ScrmPoGradeData as SPGD on CL.PoGradeID=SPGD.ID
    left join ScrmPIVarietyList as PL on SPGD.VarietyID=PL.VarietyID
	left join QcTracebilityList as TL on TL.TraceID=CL.TracebilityID where CL.InvID='$InvID'
	 GROUP by DayCode,CL.PackingStyleID,TracebilityID,PL.ShortName,CL.Brand";
}


$StmtCodeList = $iconn->query($SqlCodeList);

extract($_POST);
extract($_GET);


// if (isset($_POST['submit'])) {
// 	$DOA = date("d-m-Y", strtotime($DOA));
// 	$DOC = date("d-m-Y", strtotime($DOC));

// 	$sql_org_insert = "INSERT INTO QcMBResult( `MbReportID`, `CLID`, `DateOfAnalysis`, `DateOfIssuance`, `Tpc`, `Listeria`) 
// 	VALUES ('$MbReporttID','$CLID','$DOA','$DOC','$TPC','$Listeria')";
// 	$stmt1 = $iconn->query($sql_org_insert);
// 	//	$M_T_REPORT_ID = $iconn->insert_id;

// 	if ($stmt1) {
// 		header('Location: DocAddCombinedResult.php?RID=' . $MbReportID . '&Q=' . $QueryParm);
// 	}
// }
if (isset($_POST['submit'])) {
	$DOA = date("d-m-Y", strtotime($DOA));
	$DOC = date("d-m-Y", strtotime($DOC));

	if ($editMode == '1' && $RecordID) {
		$oldRow = legacyAuditFetchRowById('QcMBResult', 'ID', $RecordID, 'QualityControl');
		// Update existing record
		$sql_update = "UPDATE QcMBResult 
                       SET `DateOfAnalysis` = '$DOA', 
                           `DateOfIssuance` = '$DOC', 
                           `Tpc` = '$TPC', 
                           `Listeria` = '$Listeria' 
                       WHERE `ID` = '$RecordID'";

		if ($iconn->query($sql_update)) {
			legacyAuditLogUpdateById('QcMBResult', 'ID', $RecordID, $oldRow, $_POST, ['Remarks' => 'MB Result Updated'], 'QualityControl');
			header('Location: DocAddCombinedResult.php?RID=' . $MbReportID . '&Q=' . $QueryParm);
		} else {
			echo "Error updating record: " . $iconn->error;
		}
	} else {
		// Insert new record
		$sql_org_insert = "INSERT INTO QcMBResult( `MbReportID`, `CLID`, `DateOfAnalysis`, `DateOfIssuance`, `Tpc`, `Listeria`) 
                           VALUES ('$MbReporttID','$CLID','$DOA','$DOC','$TPC','$Listeria')";

		if ($iconn->query($sql_org_insert)) {
			$insertId = $iconn->insert_id;
			legacyAuditLogInsertById('QcMBResult', 'ID', $insertId, $_POST, ['Remarks' => 'MB Result Created'], 'QualityControl');
			header('Location: DocAddCombinedResult.php?RID=' . $MbReportID . '&Q=' . $QueryParm);
		} else {
			echo "Error inserting record: " . $iconn->error;
		}
	}
}

$SqlMbResult = "select * from QcMBResult where MbReportID='$MbReporttID'";
$StmtMbResult = $iconn->query($SqlMbResult);

?>

<!DOCTYPE html>

<html lang="en">
<!--<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />-->

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
							$_SESSION['Status'] = ''; ?>
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
									<input type="hidden" id="editMode" name="editMode"
										value="<?php echo $edit_id ? '1' : '0'; ?>">
									<input type="hidden" id="RecordID" name="RecordID" value="<?php echo $edit_id; ?>">
									<div class="card-body">

										<div class="row">
											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Production Codes</label>
												<select class="form-control select2" name="CLID" id="P_CODES" required>

													<?php $ary_prod_codes = array(); ?>
													<option value="">Select Production Code</option>
													<?php while ($RowCl = $StmtCodeList->fetch_array()) {
														$CLID = $RowCl['CLID'];
														$DayCode = $RowCl['DayCode'];
														$Tracebility = $RowCl['Tracebility'];
														$Grades = $RowCl['Grades'];
														$McsGrade = $RowCl['McsTotal'];

														?>

														<option value="<?php echo $CLID; ?>" <?php echo ($CLID == $clid) ? 'selected' : ''; ?>>
															<?php echo $DayCode . '-' . $Tracebility . '-' . $Grades . '-' . $RowCl['ShortName'] . '-' . $McsGrade . ' Mcs'; ?>
														</option>
													<?php } ?>
													<select>
											</div>


											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Date of analysis</label>
												<input required type="date" class="form-control" name='DOA' id="DOA"
													placeholder="Enter Date of analysis" value="<?php echo $doa; ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Date of Completion</label>
												<input required type="date" class="form-control" name='DOC' id="DOC"
													placeholder="Date of Completion" value="<?php echo $doc; ?>">
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Tpc</label>
												<input required type="text" class="form-control" name='TPC' id="TPC"
													placeholder="Ex: 500/300/200(For multiple Values)"
													value="<?php echo $tpc; ?>">

											</div>
											<?php if ($IsShowQcAddOnFields == 1) { ?>


												<div class="form-group col-lg-3">
													<label for="exampleInputPassword1">Listeria</label>
													<input type="text" class="form-control" name="Listeria" id="Listeria"
														placeholder="Listeria" value="<?php echo $listeria; ?>">
												</div>
											<?php } ?>
										</div>



										<div class="col-xs-12 box-footer" align="right">

											<!-- <a  id='btn_close' class="btn btn-danger">Close</a>-->
											<a href="DocAddReports.php?Q=<?php echo $QueryParm ?>"
												class="btn btn-danger">Close</a>
											<button type="submit" name='submit' class="btn btn-success"
												id="submitBtn">Save</button>


										</div>
								</form>

							</div> <!-- end row -->

						</div> <!-- end card body-->







						<div class="card">

							<div class="card-body" id="ABReportList">

								<table id="key-datatable" data-paging="false" class="table dt-responsive wrap w-100">

									<thead class="thead-light">

										<tr>

											<th>Day Code </th>
											<th>Traceability</th>
											<th>Date of Analysis</th>
											<th>Date Of Completion</th>
											<th>Tpc</th>
											<?php if ($IsShowQcAddOnFields == 1) { ?>
												<th>Listeria</th>
											<?php } ?>
											<th>Actions</th>

										</tr>

									</thead>
									<tbody>



										<?php
										while ($RowMbResult = $StmtMbResult->fetch_array()) {

											$CLID = $RowMbResult['CLID'];
											$SqlTracebility = "SELECT * FROM QcCodeList as cl 

left join QcTracebilityList as TL on TL.TraceID=cl.TracebilityID  WHERE CLID='$CLID'";

											$StmtTracebility = $iconn->query($SqlTracebility);
											$RowTracebility = $StmtTracebility->fetch_array();
											$Tracebility = $RowTracebility['Tracebility'];

											?>
											<td>
												<?php echo $RowTracebility['DayCode']; ?>
											</td>
											<td>
												<?php echo $Tracebility; ?>
											</td>
											<td>
												<?php echo $RowMbResult['DateOfAnalysis']; ?>
											</td>
											<td>
												<?php echo $RowMbResult['DateOfIssuance']; ?>
											</td>
											<td>
												<?php echo $RowMbResult['Tpc']; ?>
											</td>
											<?php if ($IsShowQcAddOnFields == 1) { ?>
												<td>
													<?php echo $RowMbResult['Listeria']; ?>
												</td>
											<?php } ?>
											<td>
												<button class="btn btn-link Edit"
													data-id="<?php echo $RowMbResult['ID']; ?>"
													data-clid="<?php echo $RowMbResult['CLID']; ?>"
													data-daycode="<?php echo $RowTracebility['DayCode']; ?>"
													data-traceability="<?php echo $Tracebility; ?>"
													data-doa="<?php echo $RowMbResult['DateOfAnalysis']; ?>"
													data-doc="<?php echo $RowMbResult['DateOfIssuance']; ?>"
													data-tpc="<?php echo $RowMbResult['Tpc']; ?>"
													data-listeria="<?php echo $RowMbResult['Listeria']; ?>" title="Edit">
													<i class="mdi mdi-pencil font-size-16"></i>
												</button>

												<button class="btn btn-link text-danger Delete"
													data-id="<?php echo $RowMbResult['ID']; ?>" data-toggle="tooltip"
													data-placement="top" title="Delete" value=""
													data-original-title="Delete">
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


<Script>
	$('document').ready(function () {
		showData(data);
	});

	$(document).on('click', '.Delete', function (e) {
		var clicked_id = $(this).data('id');
		var ref_id = '<?php echo $MbReportID ?>';



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
				success: function (data) {
					console.log(data);
					alert("Deleted");
					location.reload();

				}
			});

		} else { }
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
			"fnStateSave": function (oSettings, oData) {
				localStorage.setItem('key-datatable', JSON.stringify(oData));
			},
			"fnStateLoad": function (oSettings) {
				return JSON.parse(localStorage.getItem('key-datatable'));
			}
		});

	}

	$('#btn_close').on("click", function () {
		window.open('', '_parent', '');
		window.close();
	});
	$(document).on('click', '.Edit', function () {
		const clid = $(this).data('clid');
		const id = $(this).data('id');
		const doa = $(this).data('doa');
		const doc = $(this).data('doc');
		const tpc = $(this).data('tpc');
		const listeria = $(this).data('listeria');
		console.log({
			id,
			clid,
			doa,
			doc,
			tpc,
			listeria
		});
		$('#P_CODES').val(clid).change();
		$('#DOA').val(doa.split('-').reverse().join('-'));
		$('#DOC').val(doc.split('-').reverse().join('-'));
		$('#TPC').val(tpc);
		if ($('#Listeria').length) {
			$('#Listeria').val(listeria);
		}
		$('#editMode').val(1);
		$('#RecordID').val(id);
		$('#submitBtn').text('Update');
	});
</script>
