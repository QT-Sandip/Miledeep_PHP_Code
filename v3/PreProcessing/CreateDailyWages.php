<?php


session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitleFolder="PreProcessing";
$PageTitle="Create Daily Wages";
$SubContactorID=$_GET['SubContactorID'];
$Month=$_GET['Month'];
$Year=$_GET['Year'];
$ContractorID=$_GET['ContractorID'];

$SqlWorkers="SELECT * FROM `PpcWorkersMaster` where SubContractorID='$SubContactorID'";
$StmtWorkers=$iconn->query($SqlWorkers);


$SqlLot="SELECT * FROM `RmLotDetatils` where  month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'";
$StmtLot=$iconn->query($SqlLot);

$SqlShifts="SELECT * FROM `PpcOptions` Order by OptID Asc";
$StmtShifts=$iconn->query($SqlShifts);	
$ArrayOptions=array();
WHILE($RowShifts=$StmtShifts->FETCH_ARRAY()){
	$ArrayOptions[]=$RowShifts;
}

 $SqlDailywage=" SELECT RLD.LotNumber,PPO.Name as Shift,PO.Name as Variety,
 VDW.NoOfWorkers,VDW.WorkerID,VDW.Date,VDW.Kgs,VDW.ID,VDW.Count FROM 
 `PpcVaDailywageData` as VDW 
 left join RmLotDetatils as RLD on RLD.RmLotID=VDW.LotID 
 left join PpcOptions as PPO on PPO.OptID=VDW.ShiftID
 left join PpcOptions as PO on PO.OptID=VDW.VarietyID
 where VDW.SubContractorID='$SubContactorID' and VDW.ContractorID='$ContractorID' 
 and month(Date)='$Month'  and year(Date)='$Year'"; 
$StmtDailywage=$iconn->query($SqlDailywage);
//EXIT;
extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	

	 $SqlInsert="INSERT INTO `PpcVaDailywageData`(`LotID`, `ShiftID`, `Count`,`Kgs`,`VarietyID`, `NoOfWorkers`, `Date`, 
	 `ContractorID`, `SubContractorID`,`WorkerID`,`DailyWage`)
	 VALUES ('$LotID','$ShiftID','$Count','$Kgs','$VarietyID','$NoOfWorkers','$Date',
	 '$ContractorID','$SubContactorID','$WorkerID' ,'$DailyWage' )"; 
	$StmtInsert=$iconn->query($SqlInsert);
	$InsertId=$iconn->insert_id;
	
	//ECHO $SqlInsert;
	//EXIT;
	
	if($StmtInsert){
		legacyAuditLogInsertById('PpcVaDailywageData', 'ID', $InsertId, $_POST, [
			'ReferenceNo' => $InsertId,
			'Remarks' => 'Daily wages created',
		], 'PreProcessing');
		
		echo "<script>alert('Success');</script>";
		
	}
	
	
		header('Location:CreateDailyWages.php?SubContactorID='.$SubContactorID. 
				'&Month='.$Month.'&Year='.$Year .'&ContractorID='.$ContractorID);

}
 ?>
 
 
<!DOCTYPE html>

<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

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
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">

    	<div class="row">
										
									<!--	 <div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1"> Lot Number</label>
                 <select class="form-control select2" id="LotID" name="LotID" required>
				  <option value="">Select Lot</option>
				 <?php
				
				 while($RowLot=$StmtLot->fetch_array()){  ?> 
				
					<option <?php if($RowSubContract['ContactorID']==$RowLot['RmLotID']){  ?>selected=="selected" <?php } ?>
					
					value="<?php echo $RowLot['RmLotID'] ;?>" >
				 <?php  echo $RowLot['LotNumber']; ?>
					</option>
					<?php }   ?>
						</select>
					</div> 

    
                 <div class="form-group col-sm-3" >

                  <label for="exampleInputPassword1"> Shift  </label>
				 <select class="form-control select2" name="ShiftID" id="ShiftID">
				 				  <option value="">Select Shift</option>

				   <?php foreach($ArrayOptions as $value) {
					   if($value['Value']=='SHIFTS'){
						
				?>
				<option 
				
				Value="<?php echo $value['OptID'];?>">
				<?php echo $value['Name'];?></OPTION>
				   <?php }} ?>
				<select>
				  
			   </div>-->
			   
			   <div class="form-group col-sm-3" >

                  <label for="exampleInputPassword1"> Variety  </label>
				 <select class="form-control select2" name="VarietyID" id="VarietyID">
				 				  <option value="">Select Variety</option>

				   <?php foreach($ArrayOptions as $value) {
					   if($value['Value']=='VARIETY'){
						
				?>
				<option 
				
				Value="<?php echo $value['OptID'];?>">
				<?php echo $value['Name'];?></OPTION>
				   <?php }} ?>
				<select>
				  
			   </div>
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Count</label>
                  <input type="text" class="form-control"  name="Count" id="Count" 
				  placeholder="Enter Count" value="<?php echo $RowSubContract['Count'] ?>" required>
                </div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Kgs</label>
                  <input type="text" class="form-control"  name="Kgs" id="Kgs" 
				  placeholder="Enter Kgs" value="<?php echo $RowSubContract['Kgs'] ?>" required>
                </div>
				
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> No.Of Workers</label>
                  <input type="text" class="form-control"  name="NoOfWorkers" id="NoOfWorkers" 
				  placeholder="Enter No.of.Workers" value="<?php echo $RowSubContract['NoOfWorkers'] ?>" required>
                </div>
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Daily Wage</label>
                  <input type="text" class="form-control"  name="DailyWage" id="DailyWage" 
				  placeholder="Enter Daily Wage" value="<?php echo $RowSubContract['DailyWage'] ?>" required>
                </div>
				
				
	
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Date</label>
                  <input type="date" class="form-control"  name="Date" id="Date" 
				  placeholder="" value="<?php echo date('Y-m-d') ?>" required>
                </div>
				
				
                </div>
                </div>
					
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  				<a href="DailyWagesList.php" class="btn btn-danger">Close</a>

                <button type="submit"  name='submit' class="btn btn-success">Add</button>
              </div>
            </form>

   </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                          





<div id="tdetails" class="table-responsive col-lg-12" >  
      	   <div class="card">

	  <div class="card-body" id="JobStatusList">
									
										
										
          
                    

 <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      

      <tr>
	  
	<!--  <th>Lot Number</th>
	  <th>Shift</th>-->
	  <th>Worker Name</th>
	  <th>Date</th>
	  <th>Variety</th>
	  <th>Count</th>
	  <th>No.of.Workers</th>
	  <th>Kgs</th>
	  <th>Actions</th>
	  
	 <!-- <th>Tools</th>-->
	  
	

      </tr>

    </thead>
    <tbody>

	

	<?php

	//echo $sql;

	

   while($RowDailywage=$StmtDailywage->fetch_array()){
	 $WorkerID =$RowDailywage['WorkerID'] ;
 /*$SqlWorkers="SELECT * FROM `PpcWorkersMaster` where WorkerID='$WorkerID'"; 
$StmtWorkers=$iconn->query($SqlWorkers);
$RowWorkers=$StmtWorkers->fetch_array();*/


	?>
	<tr>

		
	<!--<td id="<?php  echo $RowDailywage['ID'];?>" > <?php echo $RowDailywage['LotNumber']; ?></td>
	<td id="<?php  echo $RowDailywage['ID'];?>" > <?php echo $RowDailywage['Shift']; ?></td>-->
	<td id="<?php  echo $RowDailywage['ID'];?>" > <?php echo $RowWorkers['WorkerName']; ?></td>
	<td id="<?php  echo $RowDailywage['ID'];?>" > <?php echo date('d-m-Y',strtotime($RowDailywage['Date'])); ?></td>
	<td id="<?php  echo $RowDailywage['ID'];?>" > <?php echo $RowDailywage['Variety']; ?></td>
	<td id="<?php  echo $RowDailywage['ID'];?>" > <?php echo $RowDailywage['Count']; ?></td>
	<td id="<?php  echo $RowDailywage['ID'];?>" > <?php echo $RowDailywage['NoOfWorkers']; ?></td>
	<td id="<?php  echo $RowDailywage['ID'];?>" > <?php echo $RowDailywage['Kgs']; ?></td>
    
	<td>

                        
<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowDailywage['ID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
								 
	
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
	showData(data);
	
	
});
function showData(data){
			
					$("#JobStatusList").html(data);
 
 		var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : true,
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}


</script>
<Script>
$('document').ready(function(){
	//showData(data);
	
	$(document).on('click', '.Delete', function(e){
e.preventDefault();
	//alert('hiiiii');
	
	 var DailyWageID =$(this).data('id');
	 var Type = 'DeleteDailyWage';
	  var r = confirm("Are You Sure Do You Want To Delete?");
  if (r == true) {
  
	
	$.ajax({
	
	type: 'POST',
     url: 'Ajax/AjaxDeleteDailyWage.php',
    data: {'DailyWageID': DailyWageID,'Type' : Type},
   
    success: function(data){
		
		var obj = JSON.parse(JSON.stringify(data));
		 
		 
    
		 if(obj.error == null){
			 alert("Deleted Successfully");

			location.reload();
		 }else{
			 alert(obj.error);
		 }
			 

     

     }
  });
 }else{}
  });
    
});
</script>
