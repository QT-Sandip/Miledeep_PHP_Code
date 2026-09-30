<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitle = "Create Stock";
$PageTitleFolder = "Cold Store";
$UserID = $_SESSION['UserID'];
$AccessType=$_SESSION['AccessType']; 
//Query Parms
$UnitID = $_SESSION['UnitID'];
$BarCodeType = $_GET['TYPE'];
$BarCodeID = $_GET['BID'];

//Page Constants

$TodayDate = date('Y-m-d');


// if ($BarCodeType != '') {
// 	$BarCodeType = 0;
// }

if ($BarCodeID == '') {
	$BarCodeType = 0;
}else{
	$BarCodeType = 1;
}

if($AccessType!=1){
	//$SQry="where StoreID in ($UnitID)";
	
}

$SqlStoreFacility="SELECT * FROM `CsStores` $SQry"; 

$StmtStoreFacility=$iconn->query($SqlStoreFacility);

 $SqlStockData = "SELECT *,CPS.PackingStyle,CPID.Grade as GradeName,
CGOV.ShortName as VarietyName,CPID.FreezingType from CsStockData as CPID
left join AdminPackingStyles as CPS on CPID.PackingStyleID= CPS.PckID 
left join ScrmPoData AS CS_POS on CS_POS.PoID=CPID.PoID 
left join ScrmPIVarietyList AS CGOV on CGOV.VarietyID=CPID.VarietyID 
left join AdminShiftDetails AS HSD on HSD.ShiftID=CPID.ShiftID 
where BarCodeID ='$BarCodeID' and (AvalMcs !=0  or LooseBags !=0)";

//ECHO $SqlStockData; EXIT;
$StmtStockData = $iconn->query($SqlStockData);
while ($row = $StmtStockData->fetch_array()) {
	$ARY_STOCK[] = $row;
}

$SqlShifts = "SELECT * FROM `AdminShiftDetails` order by ShiftID asc";
$StmtShifts = $iconn->query($SqlShifts);


$GeneralOptionsQuery = "SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='CS' or DeptID='7' ORDER BY SortOrder asc";
$GeneralOptionsExec = $iconn->query($GeneralOptionsQuery);
$ARY_OPTIONS = array();
while ($ROW = $GeneralOptionsExec->FETCH_ARRAY()) {
	$ARY_OPTIONS[] = $ROW;
}



//$GetPoData="select * from ScrmPoData where PoStatus = 1 order by PoID  DESC  limit 100";
$GetPoData = "SELECT * FROM `ScrmPoData` as CPD  
 left join ScrmShipmentDetailsBreif as SDB on CPD.PoID=SDB.PiID

  WHERE  (TransportedDate='' or TransportedDate is null) order by PoID DESC"; //ProductionStartedDate!=''and
$StmtPoData = $iconn->query($GetPoData);

$SqlPackingStyles = "SELECT * FROM `AdminPackingStyles` ORDER by Uom,ABS(PackingStyle)";
$StmtPacking = $iconn->query($SqlPackingStyles);

$SqlGrades = "SELECT * FROM `ProductionGrades`  ORDER by ABS(value)";
$StmtGrades = $iconn->query($SqlGrades);


$SqlVarietyList = "SELECT * FROM `ScrmPIVarietyList` ORDER by ABS(ShortName)";
$StmtVarietyList = $iconn->query($SqlVarietyList);

$SqlCsTenant = "SELECT * FROM `CsTenant` ORDER by SortOrder asc";
$StmtCsTenant = $iconn->query($SqlCsTenant);

$SqlProductList = "SELECT * FROM `ScrmProductList` ORDER by ABS(ShortName)";
$StmtProductList = $iconn->query($SqlProductList);


extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {

	if ($BarCodeType == 0) {

		$SqlBarCodes = "SELECT * FROM CsBarCodes ORDER by BarCodeID DESC LIMIT 1";
		$StmtBarCodes = $iconn->query($SqlBarCodes);
		$RecentBarCode = $StmtBarCodes->fetch_array();

		$LastBarCodeID = $RecentBarCode['BarCodeID'];

		if ($LastBarCodeID == '') {
			$LastBarCodeID = 1;
		} else {
			$LastBarCodeID = $LastBarCodeID + 1;
		}


		date_default_timezone_set("Asia/Kolkata");
		$BarCodeDate = date("Y-m-d");
		$BarCodeValue = str_replace("-", "", $BarCodeDate);
		$BarCodeValue = $BarCodeValue . $LastBarCodeID;

		$InsertBarCodeData = "INSERT INTO `CsBarCodes`(`BarCodeDate`, `BarCodeValue`, `BarCodeStatus`, `OnFloor`, `PostedUserID`) 
	 VALUES ('$BarCodeDate','$BarCodeValue','1','$OnFloor','$UserID')";


		$stmt1 = $iconn->query($InsertBarCodeData);
		$BarCodeID = $iconn->insert_id;
	}

	if ($LOT_NUM != '') {
		//$UnitID=$UNIT;
		$INT_LOT_NUMBER = $LOT_NUM;
	}
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
	$PostedDate = $dateTime->format("Y-m-d  H:i:s");
	//$ProductionDate=date('Y-m-d');	  
	if ($PoGradeId != 0) {
		$SqlVarietyId = "SELECT * FROM `ScrmPoGradeData` WHERE ID='$PoGradeId'";
		$StmtVarietyId = $iconn->query($SqlVarietyId);
		$RowVarietyId = $StmtVarietyId->fetch_array();
		$VarietyID = $RowVarietyId['VarietyID'];
	}
	if ($AvalMcs > '0' || $LooseBags > '0') {
		$InsertStockData = "INSERT INTO `CsStockData` 
	  ( `AvalMcs`, `LooseBags`, `PackingTypeID`, `Grade`, `PoGradeId`,
	  `FreezingType`, `VarietyID`, `Galze`, `SockingType`, `Salt`, 
	  `ProductionDate`, `PostedDate`, `PoID`, `RmLotID`, `ProductionTypeID`,
	  `ProductCategoryID`, `UnitID`, `ShiftID`, `BarCodeID`, 
	  `PackingStyleID`, `Remarks`, `StockHolderID`, `AbStatus`,
	  `ActualMcs`,
	  `ActualLooseBags` ,`ProdDayCode`)
	VALUES ('$AvalMcs','$LooseBags','$PackingTypeID','$GradeID','$PoGradeId',
	 '$FreezingTypeID','$VarietyID','$Galze','$SockingTypeID',
	 '$Salt','$ProductionDate','$PostedDate','$PoID','$RmLotID',
	 '$ProductionTypeID','$ProductCategoryID','$UnitID',
	 '$ShiftID','$BarCodeID','$PackingStyleID',
	 '$Remarks','$StockHolderID','$AbStatus','$AvalMcs','$LooseBags','$ProdDayCode')";

		$stmt1 = $iconn->query($InsertStockData);

		if ($stmt1) {
			$iid = $iconn->insert_id;

			header('Location: CreateStock.php?BID=' . $BarCodeID);
		} else {
			echo ("Error description: " . mysqli_error($iconn));
		}
	} else {
		echo '<script language="javascript" type="text/javascript"> 
                alert("Please Enter Valid Mcs");

        </script>';
	}
}



?>



<!DOCTYPE html>

<html lang="en">
<link href="<?php echo $href; ?>" rel="stylesheet" type="text/css" />

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark"> <!--class="vertical-collpsed"-->


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

								<form role="form" id="PalletForm" method="post">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Transaction Date<span
														style="color:red">*</span></label>
												<input required type="date" class="form-control" name="ProductionDate"
													id="ProductionDate" max="<?php echo $TodayDate; ?>"
													value="<?php echo $TodayDate; ?>"
													placeholder="Enter Production Date">
											</div>


											<div class="form-group col-sm-3">


												<label for="exampleInputEmail1">Group<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="ProductionTypeID"
													id="ProductionTypeID">
													<?php foreach ($ARY_OPTIONS as $value) {
														if ($value['OptionType'] == 'PRODUCT_GROUP') {
															$OPTIONS = $value['Name'];
															$VALUE = $value['Name']; ?>

															<option value="<?php echo $VALUE ?>">
																<?php echo $OPTIONS ?>
															</option>
														<?php }
													} ?>


													<select>
											</div>


											<div class="form-group col-sm-3">
												<label required for="exampleInputEmail1">Stock Belongs to<span
														style="color:red">*</span></label>
												<select class="form-control select2" name="StockHolderID"
													id="StockHolderID">
													<?php while ($RowCsTenant = $StmtCsTenant->fetch_array()) { ?>
														<option value="<?php echo $RowCsTenant['TenantID'] ?>">
															<?php echo $RowCsTenant['TenantName'] ?>
														</option>

													<?php } ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Store<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="UnitID"
													id="UnitID">
													<?php while ($RowCsTenant = $StmtStoreFacility->fetch_array()) { ?>
														<option value="<?php echo $RowCsTenant['StoreID'] ?>">
															<?php echo $RowCsTenant['StoreName'] ?>
														</option>

													<?php } ?>
												</select>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Shift</label>
												<select class="form-control select2" name="ShiftID" id="ShiftID">
													<?php while ($RowShifts = $StmtShifts->fetch_array()) {
														//if($value['OptionType']=='Shifts'){
														$OPTIONS = $RowShifts['ShiftName'];
														$VALUE = $RowShifts['ShiftID']; ?>

														<option value="<?php echo $VALUE ?>">
															<?php echo $OPTIONS ?>
														</option>
													<?php } ?>



													<select>
											</div>









											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Production Code<span
														style="color:red">*</span></label>
												<input required type="text" class="form-control" name="ProdDayCode"
													id="ProdDayCode" placeholder="Enter Production Code">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Batch Code<span
														style="color:red">*</span></label>
												<input required type="text" class="form-control" name="RmLotID"
													id="RmLotID" placeholder="Enter Batch Code">
											</div>






											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Po Number</label>
												<select class="form-control select2" name="PoID" id="PoID">
													<option value="">Select PO</option>
													<?php while ($RowPoData = $StmtPoData->fetch_array()) { ?>
														<option value="<?php echo $RowPoData['PoID'] ?>">
															<?php
															$LotNo = '-' . $RowPoData['LotNo'] ?: '';
															echo $RowPoData['PoNo'] . $LotNo ?>
														</option>

													<?php } ?>
												</select>
											</div>
                                       <!-- <div class="form-group col-sm-3">

												<label for="exampleInputEmail1">Variety <span style="color:red">*</span></label>
												<input hidden type="text" id="PoGradeId" name="PoGradeId"
													class="form-control hidden" value="">
												<select required class="form-control select2" name="VarietyID"
													id="VarietyID">
													<option value="">Select Variety</option>
													<?php while ($RowProductData = $StmtProductList->fetch_array()) { ?>
														<option value="<?php echo $RowProductData['ProductID'] ?>">
															<?php echo $RowProductData['ShortName'] ?>
														</option>

													<?php } ?>
												</select>
											</div> -->


											<div class="form-group col-sm-3">

												<label for="exampleInputEmail1">Variety<span
														style="color:red">*</span></label>
												<input hidden type="text" id="PoGradeId" name="PoGradeId"
													class="form-control hidden" value="">
												<select required class="form-control select2" name="VarietyID"
													id="VarietyID">
													<option value="">Select Variety</option>
													<?php while ($RowVData = $StmtVarietyList->fetch_array()) { ?>
														<option value="<?php echo $RowVData['VarietyID'] ?>">
															<?php echo $RowVData['ShortName'] . ' - ' . $RowVData['TypeProduct'] ?>
														</option>

													<?php } ?>
												</select>
											</div>
											
												

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Packing Type</label>
												<select class="form-control select2" name="PackingTypeID"
													id="PackingTypeID">
													<?php foreach ($ARY_OPTIONS as $value) {
														if ($value['OptionType'] == 'PACKING_TYPE') {
															$OPTIONS = $value['Name'];
															$VALUE = $value['Name']; ?>

															<option value="<?php echo $VALUE ?>">
																<?php echo $OPTIONS ?>
															</option>
														<?php }
													} ?>
													<select>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Packing Style<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="PackingStyleID"
													id="PackingStyleID">
													<?php while ($RowPAcking = $StmtPacking->fetch_array()) { ?>
														<option value="<?php echo $RowPAcking['PCKID'] ?>">
															<?php echo $RowPAcking['PackingStyle'] ?>
														</option>

													<?php } ?>

												</select>
											</div>






											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Grade<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="GradeID"
													id="GradeID">
													<?php while ($RowGrades = $StmtGrades->fetch_array()){ ?>
														

															<option value="<?php echo $RowGrades['value'] ?>">
																<?php echo $RowGrades['Options'] ?>
															</option>
														<?php 
													} ?>

													<select>
											</div>






											<div class="form-group col-sm-3">


												<label for="exampleInputEmail1">Freezing Type<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="FreezingTypeID"
													id="FreezingTypeID">
													<?php foreach ($ARY_OPTIONS as $value) {
														if ($value['OptionType'] == 'FREEZING') {
															$OPTIONS = $value['Name'];
															$VALUE = $value['Name']; ?>

															<option value="<?php echo $VALUE ?>">
																<?php echo $OPTIONS ?>
															</option>
														<?php }
													} ?>

													<select>
											</div>






											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Glaze(%)<span
														style="color:red">*</span></label>
												<input required type="number" class="form-control" name='Galze'
													id="Galze" value="0" placeholder="Enter Glaze %">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Soaking<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="SockingTypeID"
													id="SockingTypeID">
													<?php foreach ($ARY_OPTIONS as $value) {
														if ($value['OptionType'] == 'Soaking') {
															$OPTIONS = $value['Name'];
															$VALUE = $value['Name']; ?>

															<option value="<?php echo $VALUE ?>">
																<?php echo $OPTIONS ?>
															</option>
														<?php }
													} ?>

													<select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Salt<span
														style="color:red">*</span></label>
												<input required type="number" class="form-control" name='Salt' id="Salt"
													placeholder="Enter Salt %">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Mcs<span
														style="color:red">*</span></label>
												<input required type="number" class="form-control" name='AvalMcs'
													id="AvalMcs" placeholder="Enter No Of Mcs">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Loose Bags<span
														style="color:red">*</span></label>
												<input required type="number" class="form-control" name='LooseBags'
													id="LooseBags" value="0" placeholder="Enter No Of Loose Bags">
											</div>






											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Product Category</label>
												<select class="form-control select2" name="ProductCategoryID"
													id="ProductCategoryID">
													<?php foreach ($ARY_OPTIONS as $value) {
														if ($value['OptionType'] == 'PRODUCT_CATEGORY') {
															$OPTIONS = $value['Name'];
															$VALUE = $value['Name']; ?>

															<option value="<?php echo $VALUE ?>">
																<?php echo $OPTIONS ?>
															</option>
														<?php }
													} ?>



													<select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Ab Status</label>
												<select class="form-control select2" name="AbStatus" id="AbStatus">
													<?php foreach ($ARY_OPTIONS as $value) {
														if ($value['OptionType'] == 'AbStatus') {
															$OPTIONS = $value['Name'];
															$VALUE = $value['Name']; ?>

															<option value="<?php echo $VALUE ?>">
																<?php echo $OPTIONS ?>
															</option>
														<?php }
													} ?>



													<select>
											</div>



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Remarks</label>
												<input type="text" class="form-control" name='Remarks' id="Remarks"
													placeholder="Enter Remarks">
											</div>





										</div>

										<!-- /.box-body -->


										<div class="col-xs-12 box-footer" align="right">


											<button type="submit" name='submit' class="btn btn-success">Add To
												Pallet</button>

											<?php if ($BarCodeID == '') {
											} else { ?>
												<a id='' href="CreateStock?TYPE=0&BID=" class="btn btn-primary">Create New
													Pallet</a>
												<a id='' href="CsStoreLayout.php?BID=<?php echo $BarCodeID ?>"
													class="btn btn-warning">Place Pallet</a>

											<?php } ?>
										</div>





									</div>
									<!-- /.box-body -->


								</form>
							</div>

							<div class="">
								<div class="card">
									<div class="card-body" id="ShowDataTable">
										<table id="key-datatable" data-paging="false"
											class="table table-bordered  nowrap w-100">
											<thead class="thead-light">

												<tr>

													<th>Po Number</th>
													<th>Type</th>
													<th>Grade</th>
													<th>Freezing Type</th>
													<th>Packing Style </th>
													<th>Variety</th>
													<th>Production Date</th>
													<th>Ab Status</th>
													<th>Shift</th>
													<th>Mcs</th>
													<th>Loose</th>
													<th>Options</th>


												</tr>

											</thead>

											<tbody>

												<?php

												//echo $sql;
												
												foreach ($ARY_STOCK as $row) {


													$urltxt = 'id=' . $row['StockID'] . '&TYPE=' . $TYPE . '&BID=' . $BarCodeID;

													$newDate = getFormatedDate($row['ProductionDate']);

													?>


													<tr>

														<td>
															<?php echo $row['PoNo']; ?>
														</td>
														<td>
															<?php echo $row['ProductionTypeID']; ?>
														</td>
														<td>
															<?php echo $row['GradeName']; ?>
														</td>
														<td>
															<?php echo $row['FreezingType']; ?>
														</td>
														<td>
															<?php echo $row['PackingStyle']; ?>
														</td>
														<td>
															<?php echo $row['VarietyName']; ?>
														</td>
														<td>
															<?php echo $newDate; ?>
														</td>
														<td>
															<?php echo $row['AbStatus']; ?>
														</td>
														<td>
															<?php echo $row['ShiftName']; ?>
														</td>
														<td>
															<?php echo $row['AvalMcs']; ?>
														</td>
														<td>
															<?php echo $row['LooseBags']; ?>
														</td>
														<td>
															<a target="_blank" href="CsStockEdit.php?<?php echo $urltxt; ?>"
																class="mr-3 text-primary" data-placement="top"
																title="PI Edit" data-original-title="Edit"><i
																	class="mdi mdi-pencil font-size-16"></i></a>
													</tr>

													<?php

												}
												?>

											</tbody>

										</table>

									</div>
								</div>
							</div>



						</div> <!-- end row -->

					</div> <!-- end card body-->




					</section>

				</div>

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
<Script>


function getVarietyList(){
	var PoId = $('#PoID').val();
			if (PoId != '') {
				document.getElementById("GradeID").disabled = true;
				document.getElementById("PackingStyleID").disabled = true;
				document.getElementById("FreezingTypeID").disabled = true;
				document.getElementById("Galze").disabled = true;
				document.getElementById("SockingTypeID").disabled = true;
				document.getElementById("Salt").disabled = true;
			} else {
				document.getElementById("GradeID").disabled = false;
				document.getElementById("PackingStyleID").disabled = false;
				document.getElementById("FreezingTypeID").disabled = false;
				document.getElementById("Galze").disabled = false;
				document.getElementById("SockingTypeID").disabled = false;
				document.getElementById("Salt").disabled = false;
			}

				// document.getElementById("GradeID").disabled = true;
				// document.getElementById("PackingStyleID").disabled = true;
				// document.getElementById("FreezingTypeID").disabled = true;
				// document.getElementById("Galze").disabled = true;
				// document.getElementById("SockingTypeID").disabled = true;
				// document.getElementById("Salt").disabled = true;

			$.ajax({
				type: 'POST',
				url: 'Ajax/AjaxCreateStockOptions.php',
				data: { 'Type': 'FORM', 'PoId': PoId },
				success: function (response) {
					$("#VarietyID").html(response);
				}
			});
}

	$('document').ready(function () {

		getVarietyList();



		$('#PoID').change(function () {
			getVarietyList();
		});


		$('#PackingTypeID').change(function () {
			var PackType = $('#PackingTypeID').val();
			var Variety = $('#VarietyID').val();
			//alert(PackType);

			if (PackType == 'DUMMY') {
				document.getElementById("PackingStyleID").disabled = false;
				document.getElementById("PoID").required = false;
			} else {
				document.getElementById("PackingStyleID").disabled = true;
				document.getElementById("PoID").required = true;
			}

			if (Variety == '') {
				return;
			} else {
				getGradeBreakUpData();
			}
		});


		$('#VarietyID').change(function () {

			getGradeBreakUpData();
		});


		$('#SOAKING').change(function () {
			var SOAKING = $('#SOAKING').val();


			if (SOAKING == 'Salt') {
				$('#Salt').val('');
				document.getElementById("Salt").disabled = false;
			} else {
				$('#Salt').val(0);
				document.getElementById("Salt").disabled = true;
			}

		});

		$('#Salt').val(0);
		document.getElementById("Salt").disabled = true;

		var LOT_NUM = '<?php echo $LOT_NUM ?>';


		if (LOT_NUM != '') {

			document.getElementById("UnitID").disabled = true;
		}


		$("#PalletForm").submit(function (event) {
			document.getElementById("GradeID").disabled = false;
			document.getElementById("PackingStyleID").disabled = false;
			document.getElementById("FreezingTypeID").disabled = false;
			document.getElementById("Galze").disabled = false;
			document.getElementById("SockingTypeID").disabled = false;
			document.getElementById("Salt").disabled = false;
		});



	});

</script>
<script>
	function getGradeBreakUpData() {
		var PoId = $('#PoID').val();
		var Variety = $('#VarietyID').val();
		var PackType = $('#PackingTypeID').val();


		if (PoId == '') {
			//$("#VarietyInput").val(Variety);
			var Variety=$('#VarietyID').val();
			var BreakUpID=0;
			//return;
		}else{
			var Variety=0;
			var BreakUpID=$('#VarietyID').val();
		}
		$.ajax({
			type: 'POST',
			url: 'Ajax/AjaxCreateStockOptions.php',
			dataType: 'json',
			data: { 'Type': 'GRADES', 'PoId': PoId, 'Variety': Variety,'BreakUpID':BreakUpID },
			success: function (response) {
				var myJSON = JSON.parse(JSON.stringify(response));

				if (PackType == 'DUMMY') {
					document.getElementById('GradeID').disabled = true;
					document.getElementById('GradeID').readOnly = true;
					document.getElementById("PackingStyleID").disabled = false;
				} else {
					document.getElementById("PackingStyleID").disabled = true;
					document.getElementById('GradeID').disabled = true;
					document.getElementById('GradeID').readOnly = true;
				}

				document.getElementById("GradeID").disabled = true;
				document.getElementById("PackingStyleID").disabled = false;
				document.getElementById("FreezingTypeID").disabled = true;
				document.getElementById("Galze").readOnly = true;
				document.getElementById("SockingTypeID").disabled = true;
				document.getElementById("Salt").disabled = true;




				$("#PoGradeId").val(myJSON['PoGradeID']);
				$("#GradeID").val(myJSON['GradeName']).change();
				$("#PackingStyleID").val(myJSON['PackingStyleID']).change();
				$("#FreezingTypeID").val(myJSON['FreezingTypeID']).change();

				$("#Galze").val(myJSON['GlazePerc']);
				$("#SOAKING").val(myJSON['SockingTypeID']);
				$("#SALT").val(myJSON['Salt']);

			}
		});

	}
</script>