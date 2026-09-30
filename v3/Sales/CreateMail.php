<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "SalesNew";
$PageTitle = "Mail";

$_SESSION['Token'];

//$PoRefNumber=$_GET['id'];
$PoID = $_GET['PoID'];

/* $SqlAvalRep="SELECT * FROM `$ScrmExportAvalReports` where  ReportType=$DocType 
and ReportCat='$ReportCat' and OptionType =2 and ReportStatus=1 Order by SortOrder asc";   */
$SqlAvalRep = "SELECT * FROM `ScrmExportAvalReports` where    ReportStatus=1 and OptionType !=1 Order by SortOrder asc";
$StmtAval = $iconn->query($SqlAvalRep);


$SqlMailTempleate = "SELECT * FROM `ScrmManualMails` where Status=1";
$StmtMailTempleate = $iconn->query($SqlMailTempleate);


$SqlInvoice = "SELECT * FROM ScrmCustomInvoices as SCI
 left join ScrmPoData as POD on SCI.PiID=POD.PoID 
 WHERE  POD.Status not in (3,7)  order by PiID desc";
$StmtInvoice = $iconn->query($SqlInvoice);

//ECHO $SqlInvoice; EXIT;

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {



}
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

					<?php } ?>

					<div class="modal fade" id="myModal1" role="dialog">

						<div class="modal-dialog"> <!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title modheading"></h4>
								</div>
								<div class="modal-body checkdiv1">

									<?php

									$Name = '';
									?>

									<?php while ($RowAvalRep = $StmtAval->fetch_array()) {

										$TableName = $RowAvalRep['TableName'];
										$QueryID = $RowAvalRep['QueryID'];
										$PoID = $RowAvalRep['PoID'];
										$ReportName = $RowAvalRep['ReportName'];
										$DisplayId = $RowAvalRep['DisplayParam'];

										$SqlReport = "select * from $TableName where $PoID='$InvID'";
										$StmtReports = $iconn->query($SqlReport);
										$AvalCount = mysqli_num_rows($StmtReports);


										?>

										<div class="checkbox">
											<label>


												<input type="checkbox" id="<?php echo $RowAvalRep['ID']; ?>"
													value="<?php echo $ReportName; ?>">
												<?php echo $ReportName; ?>

											</label>
										</div>

									<?php } ?>


								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>



					<?php include($IncludesPath . "page-title.php");
					$PoID = $_GET['PoID'];
					?>



					<form role="form" method="post" enctype="multipart/form-data">

						<div class="card">


							<div class="card-body">
								<div class="row">

									<div class="col-lg-3">
										<label>Select Template </label>

										<select class="form-control select2" name="TempleateID" id="TempleateID">
											<option value=" ">Select</option>

											<?php while ($RowTempleate = $StmtMailTempleate->fetch_array()) {

												?>

												<option Value="<?php echo $RowTempleate['MailID']; ?>">
													<?php echo $RowTempleate['Template']; ?>
												</OPTION>

											<?php } ?>
											<select>


									</div>

									<div class="form-group col-sm-6">
										<label for="exampleInputEmail1">Select Invoice</label>

										<select multiple class="form-control" name="SelectedInvId[]" id="SelectedInvId">


											<?php while ($RowPIRef = $StmtInvoice->fetch_array()) {
												$InvNo = $RowPIRef['CustomsInvNo'];
												$RefPoID = $RowPIRef['PiID'];
												//$CInvDate=$RowPIRef['CInvDate'];
												$CurrentMonth = date("m", strtotime($RowPIRef['CInvDate']));

												if ($CurrentMonth <= 3) {



													$NxtYear = date('y', strtotime($RowPIRef['CInvDate']));
													$CurrentYear = date('Y', strtotime($RowPIRef['CInvDate'])) - 1;


												} else {

													$CurrentYear = date('Y', strtotime($RowPIRef['CInvDate']));
													$NxtYear = date('y', strtotime($RowPIRef['CInvDate'])) + 1;

												}



												?>
												<option value="<?php echo $RefPoID ?>">
													<?php echo $InvNo . '(' . $CurrentYear . '-' . $NxtYear . ')'; ?>
												</option>
											<?php } ?>
											<select>

									</div>


									<div class="form-group col-sm-6">
										<label for="exampleInputPassword1">From </label>
										<input readonly type="text" class="form-control" name="FromEmailAddress"
											id="FromEmailAddress" placeholder="Enter mail Id" value="">
										</input>
									</div>

									<div class="form-group col-sm-6">
										<label for="exampleInputPassword1">To </label>
										<input readonly type="text" class="form-control" name="ToEmailAddress"
											id="ToEmailAddress" placeholder="Enter mail Id(use , for multiple mail ids)"
											value="">
										</input>
									</div>

									<div class="form-group col-sm-6">
										<label for="exampleInputPassword1">Cc </label>
										<input readonly type="text" class="form-control" name="CcHeader" id="CcHeader"
											placeholder="Enter Cc mail Id" value="">
										</input>
									</div>
									<div class="form-group col-sm-6">

										<label for="exampleInputPassword1">Subject</label>

										<input readonly type="text" class="form-control" name="Subject" id="Subject"
											placeholder="Enter Subject" value="">
										</input>
									</div>
									<div class="form-group col-sm-6">

										<label for="exampleInputPassword1">Message</label>

										<!-- <input  readonly  type="text" class="form-control" name="ComposeEmail" style='white-space:pre'
				  id="ComposeEmail" placeholder="Enter Message" value="">
				 </input>-->
										<textarea rows='4' readonly type="text" class="form-control" name="Message"
											style='white-space:pre' id="Message" placeholder="Enter Message" value="">
				 </textarea>

									</div>
									<!--  <div class="form-group col-sm-6">

				  <label for="exampleInputEmail1">Documents to be Submitted</label>
				   <input   autocomplete='off' type="text" class="form-control"  name="DocumentsList" id="DocumentsList" 
				   placeholder="Click To Select" value="<?php echo $RowEIRBank['DocumentsList'] ?>">
				<input type="hidden" class="form-control"  name="type" id="type"  >
				</div>-->




								</div>




								<div class="col-xs-12 " align="right">

									<!--<a target="" href="DocAddReports.php?PoID=<?php echo $PoRefNumber ?>"
	   class="btn btn-danger  "> Close</a>-->
									<!-- <button type="submit"  name='submit' class="btn btn-success">Submit</button>-->
									<a target="" href="SentMailsList.php" class="btn btn-danger  "> Close</a>

									<button class="btn btn-warning waves-effect waves-light" type="submit"
										name="confirm" value="<?php echo $PoID ?>" id="sentmail">Sent Mail</button>
								</div>

							</div>
					</form>
				</div>
			</div>
			<!-- end col -->

			<!-- End Page-content -->

			<?php include($IncludesPath . "footer.php"); ?>

		</div>
		<!-- end main content-->

	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>



</body>

<Script>


	$('document').ready(function () {

		$("#SelectedInvId").select2();
		//$("#TempleateID").select2();
		//$("#Variety").select2();

		// var arr=[];  
		/*$('.checkdiv input').change(function() {		
		var arr=[];	  if (this.checked) {    arr.push(this.value);  } 
		else {   arr.splice(arr.indexOf(this.value), 1);  } 
		//$('#PRODUCT_NAME').val(arr + '');  });$('#PRODUCT_NAME').click(function() {	
		$('#myModal').modal('toggle'); });
		
		$('#DocumentsList').click(function() {  
		$('#myModal1').modal('toggle'); });    var ar=[];  var type=[];	
		$('.checkdiv1 input').change(function() {  if (this.checked) {
			ar.push(this.value);	 type.push(this.id);
			}  else {   ar.splice(ar.indexOf(this.value), 1); 
			type.splice(type.indexOf(this.value), 1);   } 
			$('#DocumentsList').val(ar + ''); $('#type').val(type + '');  });*/


	});

	/* $(document).on('change','#TempleateID',function(e){
	   e.preventDefault();
	   var MailID=$(this).val();
	   var Type='AutoFillDetails';

		$.ajax({
		  url:'Ajax/AjDropDowns',
		  type:'POST',
		  data:{'DeptId':DeptId,'Type':Type},
		  success:function(data){
			$("#SubDeptID").html(data);
					}
			})
		
	
		});*/


	$(document).on('change', '#TempleateID', function (e) {
		e.preventDefault();
		//var ClientID=$(this).val();
		var MailID = $(this).val();
		var Type = 'AutoFillDetails';

		$.ajax({
			url: 'Ajax/AjInputvalues.php',
			type: 'POST',
			dataType: 'json',

			data: { 'MailID': MailID, 'Type': Type },
			success: function (response) {
				// alert("hi");
				var myJSON = JSON.parse(JSON.stringify(response));


				$("#ToEmailAddress").val(myJSON['ToMail']);
				$("#CcHeader").val(myJSON['CCMail']);
				$("#Subject").val(myJSON['Subject']);
				$("#Message").val(myJSON['Message']);
				$("#FromEmailAddress").val(myJSON['FromMail']);


			}
		});


	});

</script>
<script>
	$(document).on('click', '#sentmail', function (e) {
		//function copycontent(){
		var Message = document.getElementById('Message').value;
		//document.getElementById('target').innerText = content;
		//alert(content);
		//} 


		var InvId = $(this).val();

		//getReportsList(DocType,InvId);	 
		e.preventDefault();


		var SelectedInvId = $('#SelectedInvId').val();
		var TempleateID = $('#TempleateID').val();
		var InvIds = SelectedInvId.join();
		var ToEmailAddress = $('#ToEmailAddress').val();
		var CcHeader = $('#CcHeader').val();
		var Subject = $('#Subject').val();
		//var ComposeEmail=$('#ComposeEmail').val();
		var Message = $('#Message').val();
		if (TempleateID == '') {
			//$("#TempleateID").focus();
			alert("Please Select Template");
			return;
		}


		else if (InvIds == '') {
			//$("#SelectedInvId").focus();
			alert("Please Select Invoice");
			return;
		}

		//alert(InvIds);
		else {


			$.ajax({
				url: 'Testmail.php',
				type: 'POST',
				data: {
					'InvIds': InvIds, 'ToEmailAddress': ToEmailAddress, 'Subject': Subject,
					'Message': Message, 'CcHeader': CcHeader, 'TempleateID': TempleateID, 'Type': 'SemiManualMail'
				},
				success: function (data) {
					//showData(data);
					alert(data);
					location.reload();
					/* $('.modal-title').text('Po Id'+INT_LOT_NUM);
					$("#PopUpTable").html(data);
					$('#myModal').modal('show');*/
				}
			});
		}
	});

</script>

<script>