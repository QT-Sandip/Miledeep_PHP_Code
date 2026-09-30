<?php 
$PageTitle="Plot Details";
$PageTitleFolder="ColdStore";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");




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
				  						
												
		
                                               
			<div class="col-lg-12 box-footer"  align="center">
			
			 <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light"> Search <i class="ri-arrow-right-line align-middle ml-2"></i>
			</button>
			  	
							 <a href="AddPlotDetails.php" class="btn btn-primary waves-effect waves-light">
				<i class="fa fa-plus"></i>Add Data</a> 
				
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
                                    <div class="card-body" id="UserDataTable">
                                      
                                        

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
							  

			
			var YEAR=$('#YEAR').val();
			var MONTH=$('#MONTH').val();
			

			$.ajax({
		  url:'Ajax/AjPlotDtlsList.php', 
		  type:'POST',
		  data:{'YEAR': YEAR,'MONTH':MONTH},
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
		

		
		//uma



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
		 bSort : true,
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } ).columns.adjust();	
		}

</script>

