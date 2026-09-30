<?php 
$PageTitle="Crate Details";
$PageTitleFolder="Purchase";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlDeptList="SELECT * FROM RmPurchaseStations";
$StmtDept=$iconn->query($SqlDeptList);
?>

<!doctype html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />     


		<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
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
                                                       <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
                                                            
										<option value="">All Purchase Station</option>

															<?php while($DataRow=$StmtDept->fetch_array()){?>	

												<option value="<?php echo $DataRow['PurchaseStationID'] ;?>" >
												<?php  echo $DataRow['StationName']; ?></option>
												<?php    } ?>
                                                        </select>
													</div>
                                                </div>
												
												<div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Month" 
														id="Month">
															<?php
															$start = strtotime('first day of this month');
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
												
												<div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Year" 
														id="Year">
                                                           <?php
														
														//for($year; $Startyear <= $Currentyear; $Startyear++) { 
														for($year; $Startyear <= $endYear; $Startyear++) { 
																				//$year=($Startyear-1).'-'.($Startyear);
?>
                                                            
                                                                 <option <?php if($Startyear==$Currentyear){ echo 'selected="selected"'; }?>
																 				   value="<?php echo $Startyear ?>"><?php echo $Startyear ?></option>

																<!-- value="<?php echo $Startyear ?>"><?php echo $Startyear ?></option>-->
                                                             
                                                            <?php  } 
                                                         
                                                            ?>
                                                        </select>
													</div>
                                                </div>
												
												<div align="center" class="col-lg-12">
                                                     
                                                       <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button>
											
												 <a href="AddIndentDetails.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New OutPass</a>	
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

			getData();
    	});



    	/*$('#print').on('click',function(){
			var MONTH=$('#MONTH').val();
			var YEAR=$('#YEAR').val();
			var UNIT=$('#UNIT').val();
			window.open('../Accounts/LotReportPrint.php?Y='+YEAR+'&M='+MONTH+'&U='+UNIT);


    		//alert('print');
    	});*/
    });


function getData(){
	
	var MONTH=$('#MONTH').val();
			var PsId=$('#PurchaseStationID').val();
			var Year=$('#Year').val();
			var Month=$('#Month').val();

			//alert(YEAR);


			$.ajax({
		  url:'Ajax/AjaxIndentList.php',
		  type:'POST',
		  data:{'PsId' : PsId,'Month': Month,'Year' : Year,'Type': 1},
		  success:function(data){
			 showData(data);

					}
			});
	
}

</script>

