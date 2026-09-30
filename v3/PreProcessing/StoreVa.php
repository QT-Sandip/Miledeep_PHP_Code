<?php 
$PageTitle="Value Addition Store";
$PageTitleFolder="Preprocessing";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 $SqlStations="SELECT * FROM PpcVAStations where UnitStatus=1"; 
$StmtStations=$iconn->query($SqlStations);
?>

<!doctype html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
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

                        <?php include ($IncludesPath."page-title.php");?>
						
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										<div class="row">
                                                <div class="col-lg-3">
                                                    <div class="form-group">
													                    <label>Processing Sheds </label>

                                                       <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
                                                            <option value="">All Processing Sheds</option>
														
															<?php while($DataRow=$StmtStations->fetch_array()){?>	
				 
												<option value="<?php echo $DataRow['StationID'] ;?>" >
												<?php  echo $DataRow['UnitName']; ?></option>
												<?php    } ?>
                                                        </select>
													</div>
													</div>
													<div class="col-md-3 col-sm-6 col-xs-12">
                    <label>Select From Date: </label>
                    <input type='date' value='<?php echo date("Y-m-d"); ?>'
					class="form-control" name="Fromdate" id="Fromdate"></input>
					</div>

						<div class="col-md-3 col-sm-6 col-xs-12">
					<label>Select To Date: </label>
                    <input type='date' value='<?php echo date("Y-m-d"); ?>' class="form-control"
					name="ToDate" id="ToDate"></input>
					</div>
                                               
												
												<!--<div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Month" 
														id="Month">
															<?php
															/*$start = strtotime('first day of this month');
                                                     for ($i = 0; $i < 12; $i++) {
                                                          $time = strtotime(sprintf('%d months', $i), $start);   
													   
                                                          $label = date('F', $time);   
                                                          $value = date('n', $time);
                                                          echo "<option value='$value'>$label</option>";
                                                         }*/
														 
														 ?>
                                                        </select>
													</div>
                                                </div>
												
												<div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Year" 
														id="Year">
                                                           <?php
														
														/*for($year; $Startyear <= $Currentyear; $Startyear++) { ?>
                                                            
                                                                 <option value="<?php echo $Startyear ?>"<?php if($Startyear==$Currentyear){ echo 'selected="selected"'; }?>>
																 <?php echo $Startyear ?></option>
                                                             
                                                            <?php  } */
                                                         
                                                            ?>
                                                        </select>
													</div>
                                                </div>-->
												
												<div align="center" class="col-lg-12">
                                                     
                         <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">Search <i class="ri-arrow-right-line align-middle ml-2"></i> </button>
						<a target="_blank" href="AddVAReport.php?LotID=0"   class="btn btn-success waves-effect" >
<i class="mdi mdi-plus font-size-18"></i>Add Va</a>

											
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
                                        <h4 class="card-title">Purchased Lot - List</h4>
                                        

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


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

<script>
$('document').ready(function(){
		getData();

		$(document).on('click','#list',function(e){
			e.preventDefault();

			getData()
    	});



    	
    });


function getData(){
	
			var MONTH=$('#MONTH').val();
			var PsId=$('#PurchaseStationID').val();
			/*var Year=$('#Year').val();
			var Month=$('#Month').val();*/
            var Fromdate=$('#Fromdate').val();
			var ToDate=$('#ToDate').val();
		


			$.ajax({
		  url:'Ajax/AjaxStoreVAList.php',
		  type:'POST',
		  //data:{'PsId' : PsId,'Month': Month,'Year' : Year,'Type': 1},
		  data:{'PsId' : PsId,'Fromdate': Fromdate,'ToDate' : ToDate,'Type': 1},
		  success:function(data){
			 showData(data);

					}
			});
	
}

</script>

