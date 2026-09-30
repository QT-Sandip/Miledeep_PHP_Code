<?php 
$PageTitle="Stock Options";
$PageTitleFolder="ColdStore";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


 $SqlBlockList="SELECT * FROM `CsStoresBlockData`"; 
$StmtBlockList=$iconn->query($SqlBlockList);
  
$SqlFloorList="SELECT * FROM `CsStoresFloorData`";
$StmtFloorList=$iconn->query($SqlFloorList);


?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/Jlsf/User.css"  rel="stylesheet" type="text/css" />

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

												
												
		<!--<div class="col-lg-3">
            <div class="form-group">
               
         <select class="form-control select2" name="FloorID" id="FloorID">
                                                            
			    <option value="">Select Floor</option>
			<?php while($FloorDataRow=$StmtFloorList->fetch_array()){?>	
				 
			<option value="<?php echo $FloorDataRow['FloorID'] ;?>" >
			<?php  echo $FloorDataRow['FloorName']; ?></option>
			<?php    } ?>
												
                </select>	
	       </div>
        </div>-->
												
												

		
			<!--<div class="form-group col-sm-3" >  
               <select class="form-control select2" name="RackID" id="RackID">
				<option value="">Select Rack Name</option>
				<select>
            </div>	
						-->
						
						 <div class="col-lg-3">
								  <div class="form-group " >
                 
                  <select class="form-control select2" name="YEAR" id="YEAR">
                           <?php
						   for($Startyear; $Startyear <= $endYear; $Startyear++) { ?>
                                                            
              <option  <?php if($Startyear == $Currentyear){ ?> selected="selected" <?php }?>
						 value="<?php echo $Startyear ?>"><?php echo $Startyear ?></option>
							                                
							<?php }   ?>
                   <select>
				  </div>
				  </div>

				  <div class="col-lg-3">
												
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2" name="MONTH" id="MONTH" size='1'>
                                                     <?php
													 
													  $start = strtotime('first day of this month');
													  /*$Date = date('d-m-Y');
													  $Month=*/
													  
                                                     for ($i = 0; $i < 12; $i++) {
                                                          $time = strtotime(sprintf('%d months', $i), $start);   
													   
                                                          $label = date('F', $time);   
                                                          $value = date('n', $time);
                                                          echo "<option value='$value'>$label</option>";
                                                         }
														 
			                                                   
                                                         ?>
                                                      </select>
													</div>
													
                                                </div>
												<div  class="col-lg-3">
                                                     
													 <button id="list"  type="button" class="btn btn-primary waves-effect waves-light"> Search <i class="ri-arrow-right-line align-middle ml-2"></i>
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
                                
                                    <div  id="UserDataTable">
                                      
                                        

                                    </div> <!-- end card body-->
                                 <!-- end card -->
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
		

    </body>
</html>

<!--<script>
$('.stick-top').affix({
  offset: {top: 50}
});
</script>--> 


<script>


$(document).on('click','#list',function(e){			
			e.preventDefault();
							  

			var block=$('#BlockID').val();
			var YEAR=$('#YEAR').val();
			var MONTH=$('#MONTH').val();
			

			$.ajax({
		  url:'Ajax/AjaxAddStock.php',
		  type:'POST',
		  data:{'block':block,'YEAR': YEAR,'MONTH':MONTH,'type':'repck'},
		  success:function(data){
			   //console.log(data);
            //$("#UserDataTable").html(data);
            //$('#example1').DataTable();
						 showData(data);

					}
			})
    	});
//uma

$(document).on('click','#repck_bt',function(e){			
			e.preventDefault();
			var StockID=$(this).val();

			//alert(YEAR);

				

		$.ajax({
		   url:'Ajax/AjaxPopupStockout.php',
		  type:'POST',
		  data:{'StockID':StockID,},
		  success:function(data){
			 $('.modal-title').text('Pallet Details');

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
			
			$.ajax({
		  url:'/ColdStore/Ajax/AjaxAddStock.php',
		  type:'POST',
		  data:{'BID':BID,'type':'PALLET_UNPLACE'},
		  success:function(data){

					}
			})
    	});		
		
$(document).on('click','.repck_bt',function(e){
	e.preventDefault();
	var rck_nme=$(this).val();
	var BAR_CODE_ID=$(this).attr('id');
	
	
	$.ajax({
    type: 'POST',
    url: '/ColdStore/Ajax/AjaxRepackDetails.php',
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
			alert(data['Msg']);
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
    url: '/ColdStore/Ajax/AjaxRepackSearch.php',
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
	
		
</script>
<script>
function showData(data){
			
$("#UserDataTable").html(data);
 
 var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : false,
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );
		}

</script>

