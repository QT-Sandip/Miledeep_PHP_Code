<?php 
$PageTitle="BarCodes List";
$PageTitleFolder="ColdStore";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


 $SqlBlockList="SELECT * FROM `CsStoresBlockData`"; 
$StmtBlockList=$iconn->query($SqlBlockList);
  
$SqlFloorList="SELECT * FROM `CsStoresFloorData`";
$StmtFloorList=$iconn->query($SqlFloorList);

$SqlStockHolderList="SELECT * FROM `CsTenant`";
$StmtStockHolderList=$iconn->query($SqlStockHolderList);


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
         <!--   <div class="col-lg-3">
			
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

												
		-->										
		
		
		<div class="col-lg-3">
            <div class="form-group">
               
         <select class="form-control select2" name="TenantID" id="TenantID">
                                                            
			    <option value="">Select </option>
			<?php while($RowStockHolder=$StmtStockHolderList->fetch_array()){?>	
				 
			<option value="<?php echo $RowStockHolder['TenantID'] ;?>" >
			<?php  echo $RowStockHolder['TenantName']; ?></option>
			<?php    } ?>
												
                </select>	
	       </div>
        </div>
					
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
							  

			var block=$('#BlockID').val();
			var YEAR=$('#YEAR').val();
			var MONTH=$('#MONTH').val();
			var TenantID=$('#TenantID').val();
			

			$.ajax({
		  url:'Ajax/AjaxBarcodeList.php',
		  type:'POST',
		  data:{'block':block,'YEAR': YEAR,'MONTH':MONTH,'TenantID':TenantID,'type':'repck'},
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

