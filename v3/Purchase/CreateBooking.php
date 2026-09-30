<?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 
 $LotID=$_GET['LotID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


//ORIGINAL
$SqlConfig = "SELECT * FROM `AdminConfigDetails`";
$StmtConfig = $iconn->query($SqlConfig);
$RowConfig=$StmtConfig->fetch_assoc();
$ApiUrl=$RowConfig['ApiUrl'];


IF($LotID==''){
$PageTitle="New Booking";
$ApiUrl=$_SESSION["SideBarPath"].$ApiUrl."createBooking"; 
}ELSE{
 $SqlRmLots="SELECT * FROM `RmBookingDetatils` WHERE RmBookingID=$LotID";  
$StmtRmLots=$iconn->query($SqlRmLots);
 $LotCount=mysqli_num_rows($StmtRmLots); 
$RowRmLots=$StmtRmLots->fetch_array(); 
$PageTitle="Edit Booking";
$ApiUrl=$_SESSION["SideBarPath"].$ApiUrl."postEditBooking"; 	
}


$PageTitleFolder="Purchase";

extract($_POST);
extract($_GET);
$UNIT=$_SESSION["UNIT"];
 $today_date=date('Y-m-d');






$BookingDate= $RowRmLots['BookingDate'];
//$BookingDate = date("Y-m-d", strtotime($BookingDate));

$SqlRmPurchaseStations="SELECT * FROM `RmPurchaseStations` ";
$StmtRmPurchaseStations=$iconn->query($SqlRmPurchaseStations);

$SqlRmPurchaseCategory="SELECT * FROM `RmPurchaseCategory` ";
$StmtRmPurchaseCategory=$iconn->query($SqlRmPurchaseCategory);



$SqlRmVehicalDetails="SELECT * FROM `RmVehicalDetails`";
$StmtRmVehicalDetails=$iconn->query($SqlRmVehicalDetails);



$SqlRmUsersList="SELECT * FROM `RmUsersList`";
$StmtRmUsersList=$iconn->query($SqlRmUsersList);

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

	
	 <?php include ($IncludesPath."PreLoader.php"); ?>

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
                  <label for="exampleInputEmail1">Booking Station</label>

				  <select class="form-control select2" name="BookingStationID" id="BookingStationID">
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
                  <label for="exampleInputPassword1">Purchase Type</label>
				 <select class="form-control select2" name="PurchaseCategoryID" id="PurchaseCategoryID">
				 
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
                  <label for="exampleInputEmail1">Booking Number</label>
                  <input type="text" class="form-control" name="BookingNumber" id="BookingNumber"
				  placeholder="Enter Booking NUMBER" value="<?php echo $RowRmLots['BookingNumber'];?>">
            </div>
	
		
			
		   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Vehicle Number</label>

				  <select class="form-control select2" name="VehicalID" id="VehicalID">
					
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

				  <select class="form-control select2" name="DriverID" id="DriverID">
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

				  <select class="form-control select2" name="GraderID" id="GraderID">
				   <?php foreach($AryUserList as $SingleItem){
				if($SingleItem['UserType']==2){
				?>
				<option <?php if($SingleItem['ID']==$RowRmLots["GraderID"]){ ?> selected="selected" <?php } ?> Value="<?php echo $SingleItem['ID'];?>">
				<?php echo $SingleItem['UserName'];?></OPTION>
				<?php }} ?>
				<select>
			</div>

         


				
		  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Booking Date</label>
                  <input type="date" class="form-control" name='BookingDate' id="BookingDate"
				  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $BookingDate; ?>">
          </div>



          <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Agent Name</label>

				  <select class="form-control select2" name="AgentID" id="AgentID">
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
                  <label for="exampleInputPassword1">Booking Count</label>
                 <input type="text" class="form-control"  name="BookingCount" id="BookingCount"
				 placeholder="Enter Booking Count" value="<?php echo $RowRmLots['BookingCount'];?>">
        </div>


		<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Booking Weight(Kgs)</label>
                 <input type="text" class="form-control"  name="BookingWeight" id="BookingWeight"
				 placeholder="Enter Booking Weight" value="<?php echo $RowRmLots['BookingWeight'];?>">
        </div>

		<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Booking Rate</label>
                 <input type="text" class="form-control"  name="BookingRate" id="BookingRate"
				 placeholder="Enter Booking Rate" value="<?php echo $RowRmLots['BookingWeight'];?>">
        </div>

		
				
		
		<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Farm Location</label>
                 <input type="text" class="form-control"  name="FarmAddress" id="FarmAddress"
				 placeholder="Enter Farm Location"value="<?php echo $RowRmLots['FarmAddress'];?>">
         </div>

		 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Remarks</label>
                 <input type="text" class="form-control"  name="Remarks" id="Remarks"
				 placeholder="Enter Remarks"value="<?php echo $RowRmLots['Remarks'];?>">
          </div>






				</div>
              <!-- /.box-body -->


            </form>

			<div class="col-xs-12 box-footer"  align="right">
                
			<?php if($LotCount==0){ ?>		
			 <button type="submt" id="CL"    class="btn btn-success">Submit</button>
			
			<?php } else{ ?>
			 <button  type="Update" id="Update"  class="btn btn-success">Update</button>

			<?php } ?>	
			
				
              </div>

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

<Script>

$('document').ready(function(){

    	$(document).on('click','#CL',function(e){
		e.preventDefault();
 // $('#CL').attr('disabled','disabled');


			//alert('Hi');

		
			//alert(ApiUrl);
            var GraderID= $('#GraderID').val();
			var AgentID= $('#AgentID').val();
			var BookingNumber= $('#BookingNumber').val();
			var BookingCount= $('#BookingCount').val();
			var DriverID= $('#DriverID').val();
			var VehicalID= $('#VehicalID').val();
			var BookingWeight= $('#BookingWeight').val();
			var BookingRate= $('#BookingRate').val();
			var FarmAddress= $('#FarmAddress').val();
			var PurchaseCategoryID= $('#PurchaseCategoryID').val();
			var BookingDate= $('#BookingDate').val();
            var BookingStationID= $('#BookingStationID').val();
            var Remarks= $('#Remarks').val();
            var PostedUserID= '<?PHP ECHO $UserID ?>'
            
			var validation=false;


			if(BookingNumber==""){
			alert("Booking NUMBER is empty");
			$('#BookingNumber').focus();
			}else if(VehicalID=="Select Vehical"){
			alert("Please select VehicalNumber");
			$('#VehicalID').focus();
			}else if(DriverID=="Select Driver"){
			alert("Please select Driver");
			$('#DriverID').focus();
			}else if(GraderID=="Select Grader"){
			alert("Please select Grader");
			$('#GraderID').focus();
			}else if(BookingDate==""){
			alert("Booking Date is empty");
			$('#BookingDate').focus();
			}else if(AgentID ==""){
			alert("Please Select Agent");
			$('#AgentID').focus();
			}else if(BookingWeight==""){
			alert("Booking weight is empty");
			}else if(BookingCount==""){
				alert("Booking count is empty");

			}else if(FarmAddress==""){
			}else if(BookingRate==""){
				alert("Booking Rate is empty");

			}else if(FarmAddress==""){
			alert("Farm Location is empty");
			$('#FarmAddress').focus();
			}
			
			else{
				validation=true;
			}

			if(validation==false){
				$('#CL').removeAttr('disabled');
			return;
			}

			if(VehicalID=='Other'){
				VehicalID = $('#Remarks').val();
			}



			var arr = {
				
				'GraderID': GraderID,
				'AgentID': AgentID,
				'BookingNumber': BookingNumber,
				'BookingRate': BookingRate,
				'BookingCount': BookingCount,
				'DriverID': DriverID,
				'VehicalID': VehicalID,
				'BookingWeight': BookingWeight,
				'FarmAddress': FarmAddress,
				'PurchaseCategoryID': PurchaseCategoryID,
				'BookingDate': BookingDate,
				'BookingStationID': BookingStationID,	
				'Remarks': Remarks,
				'PostedUserID': PostedUserID,
				'SOURCE': 'WEB'


				};
			$.ajax({
		  url:'Ajax/AjaxNewBooking',
		  type:'POST',
		  dataType: 'json',
		  data: arr,
		  success:function(data){
			//  console.log("test");
			 console.log(data);
			   var obj = JSON.parse(JSON.stringify(data));
			  // alert(obj.status);
			 if(obj.Error==null){
				$('#CL').removeAttr('disabled');
				alert(obj.msg);

				location.reload();

				}else{
				alert(obj.Error);
				}

			}});


    	});
		
		
		$(document).on('click','#Update',function(e){
		e.preventDefault();
  $('#CL').attr('disabled','disabled');


		

		
            var GraderID= $('#GraderID').val();
			var AgentID= $('#AgentID').val();
            var BookingNumber= $('#BookingNumber').val();
			var BookingCount= $('#BookingCount').val();	
			var DriverID= $('#DriverID').val();
			var VehicalID= $('#VehicalID').val();
			var BookingWeight= $('#BookingWeight').val();
			var FarmAddress= $('#FarmAddress').val();
			var PurchaseCategoryID= $('#PurchaseCategoryID').val();
			var BookingDate= $('#BookingDate').val();
            var BookingStationID= $('#BookingStationID').val();
            var Remarks= $('#Remarks').val();
            var PostedUserID= '<?PHP ECHO $UserID ?>'
            var LotID= '<?PHP ECHO $LotID ?>'
            
			var validation=false;
			
			
		    //var ApiUrl='<?php echo $ApiUrl; ?>';
			
			if(BookingNumber==""){	
			alert("Booking Number is empty");
			$('#BookingNumber').focus();
			}else if(VehicalID=="Select Vehical"){
			alert("Please select VehicalNumber");
			$('#VehicalID').focus();
			}else if(DriverID=="Select Driver"){
			alert("Please select Driver");
			$('#DriverID').focus();
			}else if(GraderID=="Select Grader"){
			alert("Please select Grader");
			$('#GraderID').focus();
			}else if(BookingDate==""){
			alert("Booking date is empty");
			$('#BookingDate').focus();
			}else if(AgentID ==""){
			alert("Please Select Agent");
			$('#AgentID').focus();
			}else if(BookingWeight==""){
			alert("Booking weight is empty");
			}else if(BookingCount==""){
				alert("Booking count is empty");
			}else if(FarmAddress==""){
			alert("Farm Location is empty");
			$('#FarmAddress').focus();
			}
			
			else{
				validation=true;
			}

			if(validation==false){
				$('#CL').removeAttr('disabled');
			return;
			}

			if(VehicalID=='Other'){
				VehicalID = $('#Remarks').val();
			}



			var arr = {
				
				'GraderID': GraderID,
				'AgentID': AgentID,
				'BookingNumber': BookingNumber,
				'BookingCount': BookingCount,
				'DriverID': DriverID,
				'VehicalID': VehicalID,
				'BookingWeight': BookingWeight,
				'FarmAddress': FarmAddress,
				'PurchaseCategoryID': PurchaseCategoryID,
				'BookingDate': BookingDate,
				'BookingStationID': BookingStationID,	
				'Remarks': Remarks,
				'PostedUserID': PostedUserID,
				'LotID': LotID,
				'SOURCE': 'WEB'
				};
				
				//console.log(JSON.stringify(arr));
				//return;
			$.ajax({
		  url:'Ajax/AjaxNewBooking',
		  type:'POST',
		  dataType: 'json',
		  data: JSON.stringify(arr),
		  success:function(data){
			   var obj = JSON.parse(JSON.stringify(data));
			 if(obj.Error==null){
				$('#CL').removeAttr('disabled');
				alert(obj.Msg);
				close();
				}else{
				alert(obj.Error);
				close();
				}

			}});


    	});	
		
		
		
		
		
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
