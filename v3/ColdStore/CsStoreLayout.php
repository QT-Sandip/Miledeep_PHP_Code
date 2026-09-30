<?php 
$PageTitle="Store";
$PageTitleFolder="ColdStore";

	$bid=$_GET['BID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$UnitID=$_SESSION['UnitID'];


 $SqlBlockList="SELECT * FROM `CsStoresBlockData` ";  //where ProcessingStationID ='$UnitID'
$StmtBlockList=$iconn->query($SqlBlockList);
  
 
$SqlFloorList="SELECT * FROM `CsStoresFloorData`"; // where ProcessingStationID ='$UnitID'
$StmtFloorList=$iconn->query($SqlFloorList);


?>


<style>
.btn btn-info blockactive{
    background-color: red;
}
div.tab-content {
  overflow: auto;
  
  
}
</style>
<!doctype html>
<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
         <?php include ($IncludesPath."DbLinks.php");?>
         <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
	
	 <?php include ($IncludesPath."PreLoader.php"); ?>
	 
	 
	 
	    <div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
	   <h4 class="modal-title">Pallet Details</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
       
      </div>
	  <?php
        $sql = "SELECT GROUP_CONCAT(CsStockData.Grade) as Grade,GROUP_CONCAT(ShortName) as ShortName FROM `CsStockData`
				left join ScrmPIVarietyList as VL on VL.VarietyID=CsStockData.VarietyID
				where BarCodeID='$bid'";
		$query = $iconn->query($sql);
		 $row= $query->fetch_assoc();
		$Grade =$row['Grade'] ;
		$ShortName =$row['ShortName'] ;
       
	
	  ?>
      <div class="modal-body">
        <p> Varity  --> <?php echo $ShortName; ?></p>
        <p> Grade  --><?php echo $Grade; ?></p>
		<input type="hidden" id="rck_id" value="">
	  
      </div>
	  <div class="modal-footer">
	  	  <button type="button" class="btn btn-primary confirm_rck" id="confirm_rck" data-dismiss="modal">Submit</button>
    
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
    
</div>

<div id="palletDetails" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
	   <h4 class="modal-title">Pallet Details</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
       
      </div>
      
	  <div class="modal-body">	
		<div class="row">	  
		<div class="col-md-12">		
		<table id="REPACK_TABLE" class="table table-bordered ">
		</table>
		</div>
		</div>  
      </div>	  
	  <div class="modal-footer">
	  	 
		 <button type="button" class="btn btn-primary" id="PlacePallet" data-dismiss="modal">Place</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
	


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
                                    <div class="card-body">
							<input type='hidden' id='bid' value='<?php echo $bid; ?>'>		 
		<div class="row">
            <div class="col-lg-3">
			
                <div class="form-group">
                <select class="form-control select2" name="BlockID" id="BlockID">
                                                            
			    <option value="">Select Block</option>
			<?php while($DataRow=$StmtBlockList->fetch_array()){?>	
				 
			<option value="<?php echo $DataRow['BlockID'] ;?>" >
			<?php  echo $DataRow['BlockName']; ?></option>
			<?php    } ?>
												
                </select>
            </div>
        </div>

												
												
		<div class="col-lg-3">
            <div class="form-group">
               
         <select class="form-control select2" name="FloorID" id="FloorID">
                                                            
			    <option value="">Select Floor</option>
			<?php while($FloorDataRow=$StmtFloorList->fetch_array()){?>	
				 
			<option value="<?php echo $FloorDataRow['FloorID'] ;?>" >
			<?php  echo $FloorDataRow['FloorName']; ?></option>
			<?php    } ?>
												
                </select>	
	       </div>
        </div>
												
												

		
			<div class="form-group col-sm-3" >  
               <select class="form-control select2" name="RackID" id="RackID">
				<option value="">Select Rack Name</option>
				<select>
            </div>	
												
												
		<div align="center" class="col-lg-3">
                                                     
            <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light"> Search <i class="ri-arrow-right-line align-middle ml-2"></i>
			</button>
		</div>
                                               
				
			<div class="bg-active color-palette col-lg-12" align="center" >				  
				  <span class="label" align="center" style="background:#134867">Selected</span>
				  <span class="label" align="center" style="background:#00a65a">Empty</span>	
				  <span class="label" align="center" style="background:#f39c12">Dummy</span>
				  <span class="label" align="center" style="background:red">Master</span>
			</div><br><br>				
												 


			</div>
                        <!-- end row -->
			</div>
			</div>
			</div>
        
                                         <!-- end col -->
            </div> <!-- end row -->

						
						
						 <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body row bckendset" id="UserDataTable">
                                       

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>




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


 <link href="<?php echo $_SESSION['ClientPath'] ?>StoreCss/custom.css" rel="stylesheet">
 <link href="<?php echo $_SESSION['ClientPath'] ?>StoreCss/style.css" rel="stylesheet">

		<?php include ($IncludesPath."vendor-scripts.php");?>
      	<?php include ($IncludesPath."NewScripts.php");?>
		

    </body>
</html>

<!--<script>
$('.stick-top').affix({
  offset: {top: 50}
});
</script>--> 



<script>

	$('#BlockID').change(function(){
		  $("#rackid").select2();
	var block= $('#BlockID').val();
	//alert(block);	

	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxRackNumberSearch.php',
	method:"post",
			  data:{'id':block},
			  success:function(data){
				$('#RackID').html(data);
				
	}
	});

	});
	
	
	
	$(document).on("click", "#list", function(e){
	e.preventDefault();	

	var BlockID=$('#BlockID').val();
	var FloorID=$('#FloorID').val();
	var RackID=$('#RackID').val();
	
	if(BlockID==''){
		alert('Please Select Block');
		return;
	}else if(FloorID == ''){
		alert('Please Select Floor	');
		return;
	}
	
	$('.bckendset').html('');
	
		  
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxStoreLayout.php',
	method:"post",
			  data:{'BlockID':BlockID,'FloorID': FloorID, 'RackID': RackID},
			  success:function(data){
			  $('.bckendset').html(data);
	}
	});
	});
	
	
	$(document).on('click','#Update',function(e){
	 
	
	 var StockID =$('#Update').val();
	 var RowID =$('#PopUpRackId').val();
	
	//alert(RowID);
	
	var r = confirm("Are You Sure Do You Want To Update?");
  if (r == true) {	
	$.ajax({
		   url:'Ajax/AjaxShuffleRack.php',
		  type:'POST',
		  data:{'StockID':StockID,'RowID':RowID,'Type':'AddToPallet'},
		  success:function(data){
					getData();
					}
			});

	
	
 }else{}
	
 });
	// serch code 
	
	$(document).on("click", "#listtt", function(e){
	e.preventDefault();

	
	var blockid=$("#block_id").val();
	
	var rck_nme=$("#rackid").val();
	//console.log(search_rck);
	$('.bckendset').html('');
	//return false;
	  //url:"getcategory.php",
			  
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxRackNumberSearch.php',
	method:"post",
			  data:{'id':blockid,'rck_nme':rck_nme},
			  success:function(data){
				$('.bckendset').html(data);
	//console.log(response);
	}
	});
	});
	
	//filled error msg code 
	$(document).on("click", ".filled", function(e){
	e.preventDefault();
	//console.log('hiiiii');
	var id=$(this).attr('id');
	var bid=$('#bid').val();
	var rck_id=$('#rck_id').val(id);
	 $('#PlacePallet').val(id);
	
		$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxPalletDetails.php',
    data: {'RACK_ID':id},
	
	//dataType: 'json',
    
    success: function(response){
		console.log(response);
			$('#palletDetails').modal('show');
              $('#REPACK_TABLE').html(response);
	 }
  });
	

	});
	
	//vilble rck clickpopup code 
	
	$(document).on("click", ".available", function(e){
	e.preventDefault();
	
	
	var id=$(this).attr('id');
	var bid=$('#bid').val();
	var rck_id=$('#rck_id').val(id);
	
	alert(bid);
	
	if(bid==0){
		alert("Rack Empty");
		return; 
	}else{
		$('#myModal').modal('show');
		//window.open("CsStoreLayout.php","_self");
	}
	});
	
	
	//confirming the vilble rck code
	
 $(document).on('click','#PlacePallet',function(e){
	e.preventDefault();
	
	var BarCodeID ="<?php echo $bid;?>";
	var RowID =$('#PlacePallet').val();

	 if(BarCodeID==''){
		 alert("Barcode Id required");
		 return;
	 }
	/* alert(BiometricId);
	 alert(RowID);
	 return;*/
	
	//alert(RowID);
	
	var r = confirm("Are You Sure Do You Want To Update?");
  if (r == true) {	
$.ajax({
		   url:'Ajax/AjaxAddtoPallet.php',
		  type:'POST',
		  data:{'BarCodeID':BarCodeID,'RowID':RowID,'Type':'AddStocktoPallet'},
		  success:function(data){
					//getData();
					//location.reload();
				//var Url="CsStoreLayout.php";
					//window.open("CsStoreLayout.php","_self");
					window.open("CsBarCodeLabelPrint.php?BID="+BarCodeID,"_self");
					}
			});

	
	
 }else{}
	});
	
	$(document).on("click", ".confirm_rck", function(e){
	e.preventDefault();
	//$('#myModal').modal('show');
    
	//console.log('hiiiii');
	var rck_id=$('#rck_id').val();
	var bid=$('#bid').val();
	
	
	//alert(rck_id);
	//return false;
 $.ajax({
    type: 'POST',
    url: 'Ajax/AjaxPlacePallet.php',
	method:"post",
			  data:{'rck_id':rck_id,'bid':bid},
			  success:function(data){
				  console.log(data);
				  if(data=='success'){
					 //alert(data);
var curl='CsOnFloorStock.php?id=0';					 
					 	//window.location.replace(curl);
	//window.open("CsStoreLayout.php","_self");
	window.open("CsBarCodeLabelPrint.php?BID="+bid,"_self");
					 //window.reload();
				  }else {
					 //alert(data) ;
					// window.open("CsStoreLayout.php","_self");
					window.open("CsBarCodeLabelPrint.php?BID="+bid,"_self");
				  }
				//$('.bckendset').html(data);
	//console.log(response);
	}
	
	});
	});
	
	
	</script>


