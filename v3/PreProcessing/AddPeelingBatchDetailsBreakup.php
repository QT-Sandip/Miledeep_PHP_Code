<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
$DailyBatchId=$_GET['DailyBatchId'];

/*$SqlLots="SELECT * FROM `RmLotDetatils` WHERE RmLotID in (SELECT DISTINCT LotID FROM `PpcHLGradingBreakup`) and PurchaseDate > now() - INTERVAL 15 day order by RmLotID desc";*/

$SqlVariety="SELECT * FROM `ScrmPIVarietyList_old` WHERE Status=1";
$StmtVariety=$iconn->query($SqlVariety);	

  $SqlBatch="SELECT ContrctrName,Date,SubContractorName,BatchID FROM  PpcDailyBatchDetails as PDB  
left join PpcSubContractorMaster as PSC  on PSC.SubContactorID=PDB.BatchID
left join PpcContractMaster as PCM  on PCM.ContractorID=PSC.ContactorID
 WHERE ID='$DailyBatchId'"; 
$StmtBatch=$iconn->query($SqlBatch);	
$RowBatch=$StmtBatch->fetch_array();

$BatchID=$RowBatch['BatchID'];
$SelectedDate=$RowBatch['Date'];
$Date=getFormatedDatelines($RowBatch['Date']);
$Batch=$RowBatch['ContrctrName'].'/'.$RowBatch['SubContractorName'].'(Batch)';



  $SqlLots="SELECT * FROM `RmLotDetatils` WHERE RmLotID in (SELECT DISTINCT LotID FROM `PpcHLGradingBreakup`) and PurchaseDate between DATE_SUB('$SelectedDate', INTERVAL 50 DAY) and '$SelectedDate' order by RmLotID desc";

$StmtLots=$iconn->query($SqlLots);

 $SqlBreakup="SELECT * FROM `PpcDailyBatchBreakUpDeatils` as PDB 
left join RmLotDetatils as  RLD on RLD.RmLotID=PDB.LotID
WHERE DailyBatchID='$DailyBatchId'";
$StmtBreakup=$iconn->query($SqlBreakup);	




$PostedDateTime=date('Y-m-d h:i:s');
$PostedUserId=$_SESSION['UserID'];


extract($_POST);
extract($_GET);

$PageTitle="Add BreakUp";
$PageTitleFolder="PreProcessing";

 ?>



<!DOCTYPE html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

   <!-- <body data-sidebar="dark">-->
	<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">

	
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
					
					

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
                     <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
									
									
										<div class="row">

    		<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Batch</label>
                  <input disabled type="text" class="form-control"  style="background:grey;color:white"
				  placeholder="Enter HL Qty(Kgs)" value="<?php echo $Batch; ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date</label>
                  <input disabled type="text" class="form-control"  style="background:grey;color:white"
				  placeholder="Enter HL Qty(Kgs)" value="<?php echo $Date; ?>">
                </div>
				
				
				
			   	<div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Lots<span style="color:red">*</span>  </label>
				 <select required class="form-control select2" name="LotID" id="LotID">
				 <option value=""> Select</option>
				 <option value="0"> Melting</option>
				   <?php while($RowLots=$StmtLots->fetch_array()) {
				?>
				<option Value="<?php echo $RowLots['RmLotID'];?>">
				<?php echo $RowLots['LotNumber'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
			   
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Variety<span style="color:red">*</span>  </label>
				 <select required class="form-control select2" name="VarietyId" id="VarietyId">
				 <option value=""> Select</option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) {
				?>
				<option Value="<?php echo $RowVariety['VarietyID'];?>">
				<?php echo $RowVariety['ShortName'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
			   
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Count<span style="color:red">*</span>  </label>
				 <select required class="form-control select2" name="Count" id="Count">
				 <option value=""> Select</option>
				   
				</select>
				  
			   </div>
				
			   
			   
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">HL Qty(Kgs)<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='HLQty' id="HLQty"
				  placeholder="Enter HL Qty(Kgs)">
                </div>
				
				
				</div>
				
				
				

				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <!--<a target="" href="#"  onclick="window.close();" class="btn btn-danger  "> Close</a>-->
			  <a target="" href="DailyContractorDetails.php"  class="btn btn-danger  "> Close</a>

                <button type="submit"  name='submit' id="PostData" class="btn btn-success">Add</button>
              </div>
            </form>
       <!-- end col -->
</div> <!-- end row -->

</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->

                        
		<div   class="col-12">

    <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

</div>
</div>

</div>
  </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>

  
       
    </body>
	
	
	<script>
	$('document').ready(function(){
		 var DailyBatchId="<?php echo $DailyBatchId ?>";
		
getData(DailyBatchId);

    
    });
	
	$("#LotID").change(function(e){
			e.preventDefault();	
			var LotID=$('#LotID').val(); 
			if(LotID!=''){
			getCounts();
			//getData();
			}
		});

	
	$(document).on('click','#PostData',function(e){			
			e.preventDefault();

			

		   
	  var BatchID="<?php echo $BatchID ?>";
	  var DailyBatchId="<?php echo $DailyBatchId ?>";
	  var LotID=$("#LotID").val();
	  var HLQty=$("#HLQty").val();
	  var VarietyId=$("#VarietyId").val();
	  var Count=$("#Count").val();
	if(LotID==''){
		alert("Please Select Lot");
		return;
	}
	
	if(VarietyId==''){
		alert("Please Select Variety");
		return;
	}
	
	if(Count==''){
		alert("Please Select Count");
		return;
	}
	  if(HLQty==''){
		alert("Please Enter Quantity");
		return;
	}  
	
	
	  
	var r = confirm("Do you Want To Submit?");
  if (r == true) {   

$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxAddPeelingBreakupDailyContractorDetails.php',
    data: {'LotID':LotID,'HLQty':HLQty,'BatchID':BatchID,'DailyBatchId':DailyBatchId,'Count':Count,'VarietyId':VarietyId,'TYPE':'AddBreakupDetails'},
	
/*success: function(response){
	getData(DailyBatchId);
	
		
	 }*/
	   success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			
//location.reload();
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
           getData(DailyBatchId);				
			}
	 }
	 
  });
  }
else {
			
  }	
		
    	});
		
		    $(document).on('click', '.Delete', function(e){
	  var BreakupID =$(this).data('id');
	  var DailyBatchID =$(this).data('value');


   		var r = confirm("Do you Want To Delete This ?");
  if (r == true) { 
			  
		$.ajax({
		
		  url:'Ajax/AjaxAddPeelingBreakupDailyContractorDetails.php',
		  type:'POST',
		  data:{'TYPE':'PeelingBreakUpDelete','BreakupID':BreakupID},
		  success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
           getData(DailyBatchID);				
			}
	 }
			});	

} else {
		
  }			
  });	
		
		function getData(DailyBatchId){
	
			var DailyBatchId=DailyBatchId;
			

			$.ajax({
		  url:'Ajax/AjaxPeelingBreakupDailyContractorDetails.php',
		  type:'POST',
		 
		  data:{'DailyBatchId' : DailyBatchId},
		  success:function(data){
			 showData(data);

					}
			});
	
}

function getCounts(){
var LotID=$('#LotID').val(); 
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjGradeCounts.php',
    data: {'LotID' : LotID},  
    success: function(response){
		 $("#Count").html(response); 
	 }
  });	  
  }
  
	</script>
	<script>
  
  /*
  $(document).on('click', '.Delete', function(e){
	  var VaID =$(this).data('id');

   		var r = confirm("Are You Sure You Want To Delete "+VaID+"?");
   if (r == true) {

		 //alert(VaID);		  
		$.ajax({
		  url:'Ajax/AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'VA_REPORT','D_ID':VaID},
		  success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
location.reload();
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							location.reload();
				
			}
	 }
			});	
}
  else{}			
  }); 
*/
  
  
  
  
</script>

