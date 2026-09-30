<?php
//ini_set( 'display_errors', 1 );
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 
 $LotID=$_GET['LotID'];
 $BillID=$_GET['LotID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
IF($LotID==''){
$PageTitle="Bill Details";
}ELSE{
 $SqlRmLots="SELECT * FROM `RmLotDetatils` WHERE RmLotID=$LotID";  
$StmtRmLots=$iconn->query($SqlRmLots);
 $LotCount=mysqli_num_rows($StmtRmLots); 
$RowRmLots=$StmtRmLots->fetch_array(); 
$PageTitle="Bill Details";	
}
$PageTitleFolder="Purchase";


 $SqlBillLots="SELECT * FROM `RmBillDetails` WHERE ArrivalID='$LotID'";  
$StmtBillLots=$iconn->query($SqlBillLots);
$LotCount=mysqli_num_rows($StmtBillLots); 
$RowRmBills=$StmtBillLots->fetch_array(); 

//echo $RowRmBills['FinancierID']; 

extract($_POST);
extract($_GET);
$UNIT=$_SESSION["UNIT"];
$today_date=date('Y-m-d');


//ORIGINAL
$SqlConfig = "SELECT * FROM `AdminConfigDetails`";
$StmtConfig = $iconn->query($SqlConfig);
$RowConfig=$StmtConfig->fetch_assoc();
//print_r($_SESSION);
$ApiUrl=$RowConfig['ApiUrl'];
$ApiUrl=$_SESSION["SideBarPath"].$ApiUrl."createLot"; 


$SqlFinaciers="SELECT * FROM `RmFinanciers` WHERE Status=1";
$StmtFinanciers=$iconn->query($SqlFinaciers);


 $SqlCIGradeEdit = "SELECT * FROM `RmLotDetatils` as RLD left join RmBillBreakup as RBB on RLD.RmLotID=RBB.LotID where RmReferenceID=$LotID";


$StmtCIGradeEdit = $iconn->query($SqlCIGradeEdit);

$PurchaseDate= $RowRmLots['PurchaseDate'];
//echo$PurchaseDate = date("d-m-Y", strtotime($PurchaseDate));


$SqlRmPurchaseStations="SELECT * FROM `RmPurchaseStations` ";
$StmtRmPurchaseStations=$iconn->query($SqlRmPurchaseStations);
$SqlRmProcessingStations="SELECT * FROM `RmProcessingStations` ";
$StmtRmProcessingStations=$iconn->query($SqlRmProcessingStations);

$SqlRmPurchaseCategory="SELECT * FROM `RmPurchaseCategory` ";
$StmtRmPurchaseCategory=$iconn->query($SqlRmPurchaseCategory);



$SqlRmVehicalDetails="SELECT * FROM `RmVehicalDetails`";
$StmtRmVehicalDetails=$iconn->query($SqlRmVehicalDetails);



$SqlRmUsersList="SELECT * FROM `RmUsersList`";
$StmtRmUsersList=$iconn->query($SqlRmUsersList);

/*$SqlRmFin="SELECT * FROM `$RmFinanciers`";
$StmtRmFin=$iconn->query($SqlRmFin);*/

$AryUserList=array();
while($RowRmUsersList=$StmtRmUsersList->fetch_array()) {
$AryUserList[]=$RowRmUsersList;
}




extract($_POST);
extract($_GET);






 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
				<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
					 					<div class="row">

			 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Purchase Station</label>

				  <select disabled class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
				   <?php while($RowRmPurchaseStations=$StmtRmPurchaseStations->fetch_array()) {
				?>
				<option 
				<?php if($RowRmPurchaseStations['PurchaseStationID']==$RowRmLots["PurchaseStationID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowRmPurchaseStations['PurchaseStationID'];?>">
				<?php echo $RowRmPurchaseStations['StationName'];?></OPTION>
				<?php } ?>
				<select>
			</div>

			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Processing Station</label>

				  <select disabled class="form-control select2" name="ProcessingStationID" id="ProcessingStationID">
				   <?php while($RowRmPurchaseStations=$StmtRmProcessingStations->fetch_array()) {
				?>
				<option 
				<?php if($RowRmPurchaseStations['ProcessingStationID']==$RowRmLots["ProcessingStationID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowRmPurchaseStations['ProcessingStationID'];?>">
				<?php echo $RowRmPurchaseStations['StationName'];?></OPTION>
				<?php } ?>
				<select>
			</div>

			<div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Purchase Type</label>
				 <select disabled class="form-control select2" name="PurchaseCategoryID" id="PurchaseCategoryID">
				 
				   <?php while($RowRmPurchaseCategory=$StmtRmPurchaseCategory->fetch_array()) {
				?>
				<option 
				<?php if($RowRmPurchaseCategory['CatID']==$RowRmLots["PurchaseCategoryID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowRmPurchaseCategory['CatID'];?>">
				<?php echo $RowRmPurchaseCategory['PurchaseType'];?></OPTION>
				<?php } ?>
				<select>
				  
			</div>


			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Lot Number</label>
                  <input readonly type="text" class="form-control" name="LotNumber" id="LotNumber"
				  placeholder="Enter LOT NUMBER" value="<?php echo $RowRmLots['LotNumber'];?>">
            </div>
	
		
			
		   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Vehicle Number</label>

				  <select  disabled class="form-control select2" name="VehicalID" id="VehicalID">
					
				   <?php while($RowRmVehicalDetails=$StmtRmVehicalDetails->fetch_array()) {

				?>
				<option
               <?php if($RowRmVehicalDetails['ID']==$RowRmLots["VehicalID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowRmVehicalDetails['ID'];?>">
				<?php echo $RowRmVehicalDetails['VehicalNumber'];?></OPTION>
				<?php } ?>
				<select>
			</div>

			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Driver Name</label>

				  <select  disabled class="form-control select2" name="DriverID" id="DriverID">
				   <?php foreach($AryUserList as $SingleItem){
				if($SingleItem['UserType']==1){
				?>
				<option 
				<?php if($SingleItem['ID']==$RowRmLots["DriverID"]){ ?> selected="selected" <?php } ?>Value="<?php echo $SingleItem['ID'];?>">
				<?php echo $SingleItem['UserName'];?></OPTION>
				<?php }} ?>
				<select>
			 </div>

			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grader Name</label>

				  <select disabled class="form-control select2" name="GraderID" id="GraderID">
				   <?php foreach($AryUserList as $SingleItem){
				if($SingleItem['UserType']==2){
				?>
				<option <?php if($SingleItem['ID']==$RowRmLots["GraderID"]){ ?> selected="selected" <?php } ?> Value="<?php echo $SingleItem['ID'];?>">
				<?php echo $SingleItem['UserName'];?></OPTION>
				<?php }} ?>
				<select>
			</div>

         


				
		  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Purchase Date</label>
                  <input readonly type="date" class="form-control" name='PurchaseDate' id="PurchaseDate"
				  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $PurchaseDate; ?>">
          </div>



          <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Agent Name</label>

				  <select disabled class="form-control select2" name="AgentID" id="AgentID">
				   <?php foreach($AryUserList as $SingleItem){
				if($SingleItem['UserType']==3){
				?>
				<option 
				<?php if($SingleItem['ID']==$RowRmLots["AgentID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $SingleItem['ID'];?>">
				<?php echo $SingleItem['UserName'];?></OPTION>
				<?php }} ?>
				<select>
		 </div>

				
         <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Purchase Count</label>
                 <input readonly type="text" class="form-control"  name="PurchaseCount" id="PurchaseCount"
				 placeholder="Enter Purchase Count" value="<?php echo $RowRmLots['PurchaseCount'];?>">
        </div>


		<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Purchase Weight(Kgs)</label>
                 <input readonly type="text" class="form-control"  name="PurchaseWeight" id="PurchaseWeight"
				 placeholder="Enter Purchase Weight" value="<?php echo $RowRmLots['PurchaseWeight'];?>">
        </div>

		
				
		
		<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Farm Location</label>
                 <input readonly type="text" class="form-control"  name="FarmAddress" id="FarmAddress"
				 placeholder="Enter Farm Location"value="<?php echo $RowRmLots['FarmAddress'];?>">
         </div>

		 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Remarks</label>
                 <input readonly type="text" class="form-control"  name="Remarks" id="Remarks"
				 placeholder="Enter Remarks"value="<?php echo $RowRmLots['Remarks'];?>">
          </div>

		  </div>


		  <div class="card-body" >
				  <h4 class="card-title">Bill Details</h4><hr>
				<div class="row">
			
			
			<div class="form-group col-sm-3" >
							<label for="exampleInputEmail1">Bill Name</label>

							<select class="form-control select2" name="FinancierID" id="FinancierID">
								<option Value="Other">Other</option>
							<?php while($FinRow=$StmtFinanciers->fetch_array()) { ?>
								
						<option <?php if($FinRow['ID']==$RowRmBills['FinancierID']){ ?>  selected="selected" <?php } ?>
						Value="<?php echo $FinRow['ID'];?>">
						<?php echo $FinRow['FinancierName'] .' - '. $FinRow['PanNumber'];?></OPTION>
				<?php } ?>
				<select>
			</div>
			
			<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Addition/Deduction Per Kg</label>
                  <input required type="number" step="any" class="form-control"  name="Deductions" id="Deductions" value="<?php echo $RowRmBills['Deductions'] ?>"
				  placeholder="Enter Price/Kg"  >
                </div>
			
				
				
				
				<div class="form-group col-sm-3"  >
                  <label for="exampleInputEmail1">Remarks</label>
                  <input required type="text" class="form-control" name='BillRemarks' id="BillRemarks"  value="<?php echo  $RowRmBills['Remarks'] ?>"
				  placeholder="Enter Remarks">
                </div>  
				
				 </div> <!-- end card body-->
			  
            


                                   
                                </div> <!-- end card -->

		  <hr>
									</hr>


									<table id="key-datatable" data-paging="false" class="table  wrap w-100">

										<thead class="thead-light">



											<th>Species</th>
											<th>Variety</th>
											
											
											<th>Weight</th>
											<th>Count</th>
											<th>Rate</th>
											<!-- <th >Actions</th> -->

										</thead>


										<tbody>
											<?php
											$AryPoGradeID = array();
											while ($RowData = $StmtCIGradeEdit->fetch_array()) {

												$AryPoGradeID[] = $PoGradeID = $RowData['RmLotID'];
												$CIDescription = $RowCIGradeEdit['CIDescription'];
												if ($CIDescription == '') {
													$Variety = $RowCIGradeEdit['Variety'];
												} else {
													$Variety = $CIDescription;
												}
												$RmLotID=$RowData['RmLotID'];
												$PurchaseWeight=$RowData['PurchaseWeight'];
												$PurchaseCount=$RowData['PurchaseCount'];
												$PurchaseRate=$RowData['Rate'];
												$LotNumber=$RowData['LotNumber'];
												$Species='VM';
												$Variety='HL';
												

												?>
												<tr>
													
												<td> <input readonly type="text" onkeydown="return false" class="form-control"
															name="Species"
															id="Species<?php echo $PoGradeID; ?>" placeholder=""
															value="<?php echo $Species; ?>"> </td>

													<td> <input readonly type="text" onkeydown="return false" class="form-control"
															name="Variety"
															id="Variety<?php echo $PoGradeID; ?>" placeholder=""
															value="<?php echo $Variety; ?>"> </td>

															<td> <input readonly type="text" class="form-control" name="Weight"
															id="Weight<?php echo $PoGradeID; ?>" placeholder="Enter Codes"
															value="<?php echo $PurchaseWeight; ?>"> </td>
													

															<td> <input readonly type="text" onkeydown="return false" class="form-control"
															name="Count"
															id="Count<?php echo $PoGradeID; ?>" placeholder=""
															value="<?php echo $PurchaseCount; ?>"> </td>
													
														
															

													<td> <input type="text" class="form-control"
															name="Rate" id="Rate<?php echo $PoGradeID; ?>"
															placeholder=""
															value="<?php echo $PurchaseRate; ?>"> </td>

															

													

													<!-- <td > </td>	 -->





												</tr>

											<?php } ?>


										</tbody>

									</table>
									<div class="col-xs-12 box-footer" align="right">
										<button type="button" id="close" name="close" class="btn btn-danger">Close
										</button>

										<button class="btn  btn-success Edit" data-id="<?php echo $PoGradeID; ?>"
											data-toggle="tooltip" data-placement="top" title="" value=""
											data-original-title="Edit">Save</button>

									</div>





				
              <!-- /.box-body -->


            </form>

			

             </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
</html>				


<script>
	$('document').ready(function () {
		//	showData();
	});


	$('#close').click(function (e) {

		window.location.href = 'CustomInvoiceList';
		window.close();
	});


	// function update_click(ClickedId){
	$(document).on('click', '.Edit', function (e) {
		e.preventDefault();
		var BillID = '<?php echo $BillID ?>';
		var CICount = '<?php echo $CICount ?>';
		var AryPoGradeID = <?php echo json_encode($AryPoGradeID); ?>;
		var ClickedId = $(this).data('id');

		var FinancierID = $('#FinancierID').val();
		var Deductions = $('#Deductions').val();
		var Remarks = $('#BillRemarks').val();
		
		
															
															
		var AryBreakUp = [];

		for (i = 0; i < AryPoGradeID.length; i++) {
			ClickedId = AryPoGradeID[i];

			var WeightID = '#Weight' + ClickedId;
			var CountID = '#Count' + ClickedId;
			var RateID = '#Rate' + ClickedId;


			var Weight = $(WeightID + "").val();
			var Count = $(CountID + "").val();
			var Rate = $(RateID + "").val();

			var jsonData = {};

			jsonData['LotID'] = ClickedId;
			jsonData['Weight'] = Weight;
			jsonData['Count'] = Count;
			jsonData['Rate'] = Rate;

			AryBreakUp.push(jsonData);
		}





		var r = confirm("Are You Sure Do You Want To Update?");
		if (r == true) {
			$.ajax({
				url: 'Ajax/AjaxCreateBill.php',
				type: 'POST',
				data: {
					'BillID': BillID,
					'FinancierID': FinancierID,
					'Deductions': Deductions,
					'Remarks': Remarks,
					'AryBreakUp': JSON.stringify(AryBreakUp)
				},
				success: function (data) {
					var data = JSON.parse(data);
					if (data['sts'] == 'Failed') {
						alert(data['msg']);
					} else {
						alert(data['msg']);
						location.reload();
					}
				}

			});
		} else { }


	});



</script>

<script>

$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  });
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });




</script>




</html>
