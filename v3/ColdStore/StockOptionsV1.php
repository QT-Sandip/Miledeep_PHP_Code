<?php 
$PageTitle="Stock Options";
$PageTitleFolder="ColdStore";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$UnitID=$_SESSION['UnitID']; 
 $SqlBlockList="SELECT * FROM `CsStoresBlockData` where ProcessingStationID='$UnitID'"; 
$StmtBlockList=$iconn->query($SqlBlockList);
  
$SqlFloorList="SELECT * FROM `CsStoresFloorData`";
$StmtFloorList=$iconn->query($SqlFloorList);

$UnitID=$_SESSION['UnitID'];
$AccessType=$_SESSION['AccessType']; 

if($AccessType!=1){
	$SQry="where StoreID in ($UnitID)";
	
}

 $SqlColdstore="SELECT * FROM `CsStores` $SQry"; 
$StmtColdstore=$iconn->query($SqlColdstore); 

$SqlTenant="SELECT * FROM `CsTenant`"; 
$StmtTenant=$iconn->query($SqlTenant);

?>

<!doctype html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
         <?php include ($IncludesPath."DbLinks.php");?>
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

                        <?php include ($IncludesPath."page-title.php");?>
					    <?php include "PopUpTable.php"; ?>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
		<div class="row">
          
			
			 <div class="form-group col-sm-3" >
				  <label for="exampleInputEmail1">ColdStores</label>
                 <select required class="form-control select2" name="ColdstoreID" id="ColdstoreID">
					<?php while ($RowColdstore=$StmtColdstore->fetch_array()){ ?>
				     <option value="<?php echo $RowColdstore['StoreID'] ?>">
					 <?php echo $RowColdstore['StoreName']?></option>
				   
				 <?php  }?>	 
                  				 
				</select>
            </div>
			
			<div class="form-group col-sm-3" >
				  <label for="exampleInputEmail1">Stock belongs to</label>
                 <select required class="form-control select2" name="TenantID" id="TenantID">
				 <option value="">All </option>
					<?php while ($RowTenant=$StmtTenant->fetch_array()){ ?>
				     <option value="<?php echo $RowTenant['TenantID'] ?>">
					 <?php echo $RowTenant['TenantName']?></option>
				   
				 <?php  }?>	 
				 
                  				 
				</select>
            </div>
			
                <div class="form-group col-sm-3">
				<label for="exampleInputEmail1">Blocks</label>
                <select class="form-control select2" name="BlockID" id="BlockID">
                                                            
			    <option value="">Select Block</option>
			<?php while($DataRow=$StmtBlockList->fetch_array()){?>	
				 
			<option value="<?php echo $DataRow['BlockID'] ;?>" >
			<?php  echo $DataRow['BlockName']; ?></option>
			<?php    } ?>
												
                </select>
            </div>
        

												
												
		
            <div class="form-group col-lg-3">
               <label for="exampleInputEmail1">Floor</label>
         <select class="form-control select2" name="FloorID" id="FloorID">
                                                            
			    <option value="">Select Floor</option>
			<?php while($FloorDataRow=$StmtFloorList->fetch_array()){?>	
				 
			<option value="<?php echo $FloorDataRow['FloorID'] ;?>" >
			<?php  echo $FloorDataRow['FloorName']; ?></option>
			<?php    } ?>
												
                </select>	
	       </div>
        								
												

		
			<div class="form-group col-lg-3" > 
<label for="exampleInputEmail1">Racks</label>			
               <select class="form-control select2" name="RackID" id="RackID">
				<option value="">Select Rack Name</option>
				<select>
            </div>	
						
						
						

				
				  						
												
		<div align="center" class="col-lg-12">
                                                     
            <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light"> Search <i class="ri-arrow-right-line align-middle ml-2"></i>
			</button>
		</div>
                                               
												
												 


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
                                    <div class="card-body" id="ShowDataTable">
                                      
                                        

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

		<?php include ($IncludesPath."vendor-scripts.php");?>
      	<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>


    </body>
</html>

<!--<script>
$('.stick-top').affix({
  offset: {top: 50}
});
</script>--> 


<script>
$('#BlockID').change(function(){
		  $("#RackID").select2();
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
	

$(document).on('click','#list',function(e){			
			e.preventDefault();
			var block=$('#BlockID').val();				  
			if(block==''){
		alert('Please Select Block');
		return;
	}

			getFloorStockList();
    	});
		
//uma

$(document).on('click','#repck_bt',function(e){			
			e.preventDefault();
			var StockID=$(this).val();
			var BarCodeID=$(this).val();

			//alert(YEAR);

				

		$.ajax({
		   url:'Ajax/AjaxPopupStockout.php',
		  type:'POST',
		  data:{'StockID':StockID,},
		  success:function(data){
			 $('.modal-title').text('Pallet Details');

			$("#Update").hide();
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});
			//}
    	});	
		

$(document).on('click','#print',function(e){			
			e.preventDefault();
			
			var block=$('#block').val();
			var floor=$('#floor').val();
			var rackid=$('#rackid').val();
			
			
window.open("/ColdStore/CsPrint.php?block=" + block + "&floor=" + floor + "&rackid=" + rackid + "&type=repck");

    	});
		
		//uma

$(document).on('click','#unplace',function(e){			
			e.preventDefault();
			var BID=$(this).val();
			var r = confirm("Do you want to unplace this stock with BarcodeID "+BID+"?");
  if (r == true) { 
			$.ajax({
		  url:'Ajax/AjaxAddStockV1.php',
		  type:'POST',
		  data:{'BID':BID,'type':'PALLET_UNPLACE'},
		  success:function(response){
			  var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			getFloorStockList();
			

			}else{
				
				var L = confirm(data['msg'] +" Do you want to place this in another location?");
  if (L == true) {
			/*alert(data['msg']);
            showData(data);*/
			//alert("hi");
			window.open("CsStoreLayout.php?BID=" + BID );

			}
			else {
			//location.reload();
  }	
		  }
		  }
			});	

   }else {
			//location.reload();
  }			
			
    	});		
		
$(document).on('click','.repck_bt',function(e){
	e.preventDefault();
	var rck_nme=$(this).val();
	var BAR_CODE_ID=$(this).attr('id');
	
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxRepackDetails.php',
    data: {'BAR_CODE_ID':BAR_CODE_ID},
	
	//dataType: 'json',
    
    success: function(response){
		//console.log(response);
		//	var data = JSON.parse(response);
			$('#myModal').modal('show');
              $('#REPACK_TABLE').html(response);
			
			
			
    // return response;
	 }
  });
  
});



$(document).on('click','.in_repck_bt',function(e){
	e.preventDefault();
	var rck_nme=$(this).val();
	var PALLET_ID=$(this).attr('id');
	
	
	
	var mcs_out='mcs_out'+PALLET_ID;
	var mcs_aval='mcs_aval'+PALLET_ID;
	var LooseBags_aval='LooseBags_aval'+PALLET_ID;
	var OUT='OUT_TYPE'+PALLET_ID;
	var LooseBags_out='LooseBags_out'+PALLET_ID;

	var mcs_aval_no=$('#'+mcs_aval).text();
	var LooseBags_aval_no=$('#'+LooseBags_aval).text();
	var mcs_out_no=$('#'+mcs_out).val();
	var OUT_TYPE=$('#'+OUT).val();
	var LooseBagsOut=$('#'+LooseBags_out).val();
	var bid=$('#bid').text();
	
	var aval_mcs = mcs_aval_no - mcs_out_no;
	var aval_loose = LooseBags_aval_no - LooseBagsOut;
	
	
	
	
	if(aval_mcs < 0 ){	
		alert(mcs_aval_no);
		alert(mcs_out_no);	
		alert('Mcs Exceeds Actual value');
		return;		
	}else if(  mcs_aval_no == mcs_out_no){
		var aval_mcs = mcs_aval_no - mcs_out_no;
	}else if(aval_loose < 0) {
		alert('Loose Exceeds Actual Value');
		return;
	}else if(mcs_out_no == 0 && LooseBagsOut == 0) {
		alert('Mcs or Loose bags Should be More than 0');
		return;
	}else if(mcs_out_no == '' && LooseBagsOut == '') {
		alert('Mcs or Loose bags Should be More than 0');
		return;
	}else {
	}
	
	
	
	
	
	//return;
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxPartialRepack.php',
    data: {'aval_mcs':aval_mcs,'out_mcs' : mcs_out_no, 'AvalLooseBags' : aval_loose,'LooseBagsOut' :LooseBagsOut, 'PALLET_ID' :PALLET_ID,
	'OUT_TYPE':OUT_TYPE,'bid':bid},
	
	//dataType: 'json',
    
    success: function(response){
		var data = JSON.parse(response);
			if(data['Error'] == null){
			$('#myModal').modal('hide');
			alert(data['Msg']);
			getFloorStockList();
			}else{
			$('#myModal').modal('hide');
			alert(data['Error']);
				
				
			}
	 }
  });
  
});


$(document).on("click", ".confirm_rpck", function(e){
	e.preventDefault();
	var bid=$('#bid').text();
	var rckid=$('#rckid').text();
	
//	alert (bid);
//	alert (rckid);
	
		var OUT_TYPE=$('#OUT_TYPE').val();
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxRepackSearch.php',
	method:"post",
			  data:{'rckid':rckid,'bid':bid,'OUT_TYPE' : OUT_TYPE,'type':"repck_updt"},
			  success:function(data){
				  //console.log(data);
				    var data = JSON.parse(data);
			if(data['sts'] == 'failed'){
			alert(data['msg']);
			}else{
				$('#tr'+bid).remove();
				alert(data['msg']);
			}
	}
	
	});
	});
	
	function getFloorStockList(){
	var block=$('#BlockID').val();
			var floor=$('#FloorID').val();
			var rackid=$('#RackID').val();
			var ColdstoreID=$('#ColdstoreID').val();
			var TenantID=$('#TenantID').val();
			
			$.ajax({
		  url:'Ajax/AjaxAddStockV1.php',
		  type:'POST',
		  data:{'block':block,'floor': floor,'rackid':rackid,'TenantID' : TenantID,'ColdstoreID' : ColdstoreID,'type':'repck'},
		  success:function(data){
			   //console.log(data);
            //$("#UserDataTable").html(data);
            //$('#example1').DataTable();
						 showData(data);

					}
			})
		}
	
		
</script>


