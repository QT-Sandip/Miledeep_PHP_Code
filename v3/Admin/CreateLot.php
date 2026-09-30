<?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 
 $LotID=$_GET['LotID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


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
//$ApiUrl=$_SESSION["SideBarPath"].$ApiUrl."createLot"; 

IF($LotID==''){
$PageTitle="Create Lot";
$ApiUrl=$_SESSION["SideBarPath"].$ApiUrl."createLot"; 

}ELSE{
 $SqlRmLots="SELECT * FROM `RmLotDetatils` WHERE RmLotID=$LotID";  
$StmtRmLots=$iconn->query($SqlRmLots);
 $LotCount=mysqli_num_rows($StmtRmLots); 
$RowRmLots=$StmtRmLots->fetch_array(); 
$PageTitle="Edit Lot";
$ApiUrl=$_SESSION["SideBarPath"].$ApiUrl."postEditLot"; 
	
}



$PurchaseDate= $RowRmLots['PurchaseDate'];
//echo$PurchaseDate = date("d-m-Y", strtotime($PurchaseDate));


$SqlRmPurchaseStations="SELECT * FROM `RmPurchaseStations` ";
$StmtRmPurchaseStations=$iconn->query($SqlRmPurchaseStations);

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

				  <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
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
                  <label for="exampleInputEmail1">Lot Number</label>
                  <input type="text" class="form-control" name="LotNumber" id="LotNumber"
				  placeholder="Enter LOT NUMBER" value="<?php echo $RowRmLots['LotNumber'];?>">
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
                  <label for="exampleInputEmail1">Purchase Date</label>
                  <input type="date" class="form-control" name='PurchaseDate' id="PurchaseDate"
				  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $PurchaseDate; ?>">
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
                  <label for="exampleInputPassword1">Purchase Count</label>
                 <input type="text" class="form-control"  name="PurchaseCount" id="PurchaseCount"
				 placeholder="Enter Purchase Count" value="<?php echo $RowRmLots['PurchaseCount'];?>">
        </div>


		<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Purchase Weight(Kgs)</label>
                 <input type="text" class="form-control"  name="PurchaseWeight" id="PurchaseWeight"
				 placeholder="Enter Purchase Weight" value="<?php echo $RowRmLots['PurchaseWeight'];?>">
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
  $('#CL').attr('disabled','disabled');


			//alert('Hi');

		    var ApiUrl='<?php echo $ApiUrl; ?>';
			//alert();
            var GraderID= $('#GraderID').val();
			var AgentID= $('#AgentID').val();
			var LotNumber= $('#LotNumber').val();
			var PurchaseCount= $('#PurchaseCount').val();
			var DriverID= $('#DriverID').val();
			var VehicalID= $('#VehicalID').val();
			var PurchaseWeight= $('#PurchaseWeight').val();
			var FarmAddress= $('#FarmAddress').val();
			var PurchaseCategoryID= $('#PurchaseCategoryID').val();
			var PurchaseDate= $('#PurchaseDate').val();
            var PurchaseStationID= $('#PurchaseStationID').val();
            var Remarks= $('#Remarks').val();
            var PostedUserID= '<?PHP ECHO $UserID ?>'
            
			var validation=false;


			if(LotNumber==""){
			alert("LOT NUMBER is empty");
			$('#LotNumber').focus();
			}else if(VehicalID=="Select Vehical"){
			alert("Please select VehicalNumber");
			$('#VehicalID').focus();
			}else if(DriverID=="Select Driver"){
			alert("Please select Driver");
			$('#DriverID').focus();
			}else if(GraderID=="Select Grader"){
			alert("Please select Grader");
			$('#GraderID').focus();
			}else if(PurchaseDate==""){
			alert("Purchase date is empty");
			$('#PurchaseDate').focus();
			}else if(AgentID ==""){
			alert("Please Select Agent");
			$('#AgentID').focus();
			}else if(PurchaseWeight==""){
			alert("Purchase weight is empty");
			}else if(PurchaseCount==""){
				alert("Purchase count is empty");

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
				'LotNumber': LotNumber,
				'PurchaseCount': PurchaseCount,
				'DriverID': DriverID,
				'VehicalID': VehicalID,
				'PurchaseWeight': PurchaseWeight,
				'FarmAddress': FarmAddress,
				'PurchaseCategoryID': PurchaseCategoryID,
				'PurchaseDate': PurchaseDate,
				'PurchaseStationID': PurchaseStationID,	
				'Remarks': Remarks,
				'PostedUserID': PostedUserID,
				'SOURCE': 'WEB'


				};
			$.ajax({
		  url:ApiUrl,
		  type:'POST',
		  dataType: 'json',
		  data: JSON.stringify(arr),
		  success:function(data){
			//  console.log("test");
			 console.log(data);
			   var obj = JSON.parse(JSON.stringify(data));
			  // alert(obj.status);
			 if(obj.error==null){
				$('#CL').removeAttr('disabled');
				alert(obj.msg);

				location.reload();

				}else{
				alert(obj.error);
				}

			}});


    	});
		
		//uma
    	$(document).on('click','#Update',function(e){
		e.preventDefault();
  $('#CL').attr('disabled','disabled');


			//alert('Hi');
		    var ApiUrl='<?php echo $ApiUrl; ?>';

		
            var GraderID= $('#GraderID').val();
			var AgentID= $('#AgentID').val();
			var LotNumber= $('#LotNumber').val();
			var PurchaseCount= $('#PurchaseCount').val();
			var DriverID= $('#DriverID').val();
			var VehicalID= $('#VehicalID').val();
			var PurchaseWeight= $('#PurchaseWeight').val();
			var FarmAddress= $('#FarmAddress').val();
			var PurchaseCategoryID= $('#PurchaseCategoryID').val();
			var PurchaseDate= $('#PurchaseDate').val();
            var PurchaseStationID= $('#PurchaseStationID').val();
            var Remarks= $('#Remarks').val();
            var PostedUserID= '<?PHP ECHO $UserID ?>'
            var TicketID= '<?PHP ECHO $TicketID ?>'
            var LotID= '<?PHP ECHO $LotID ?>'
            
			var validation=false;

//alert(LotID);
			if(LotNumber==""){
				//alert('Enter Lot Number')
			alert("LOT NUMBER is empty");
			$('#LotNumber').focus();
			}else if(VehicalID=="Select Vehical"){
			alert("Please select VehicalNumber");
			$('#VehicalID').focus();
			}else if(DriverID=="Select Driver"){
			alert("Please select Driver");
			$('#DriverID').focus();
			}else if(GraderID=="Select Grader"){
			alert("Please select Grader");
			$('#GraderID').focus();
			}else if(PurchaseDate==""){
			alert("Purchase date is empty");
			$('#PurchaseDate').focus();
			}else if(AgentID ==""){
			alert("Please Select Agent");
			$('#AgentID').focus();
			}else if(PurchaseWeight==""){
			alert("Purchase weight is empty");
			}else if(PurchaseCount==""){
				alert("Purchase count is empty");

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
				'LotNumber': LotNumber,
				'PurchaseCount': PurchaseCount,
				'DriverID': DriverID,
				'VehicalID': VehicalID,
				'PurchaseWeight': PurchaseWeight,
				'FarmAddress': FarmAddress,
				'PurchaseCategoryID': PurchaseCategoryID,
				'PurchaseDate': PurchaseDate,
				'PurchaseStationID': PurchaseStationID,	
				'Remarks': Remarks,
				'PostedUserID': PostedUserID,
				'TicketID': TicketID,
				'LotID': LotID,
				'SOURCE': 'WEB'


				};
				
				//console.log(JSON.stringify(arr));
				//return;
			$.ajax({
		  url:ApiUrl,
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
