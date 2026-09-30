<?php 
$PageTitle="Lots Lists";
$PageTitleFolder="Value Addition";

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
													<label for="exampleInputEmail1">Station</label>
                                                       <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
                                                            <option value="">All Purchase Station</option>
														
															<?php while($DataRow=$StmtDept->fetch_array()){?>	
				 
												<option value="<?php echo $DataRow['PurchaseStationID'] ;?>" >
												<?php  echo $DataRow['StationName']; ?></option>
												<?php    } ?>
                                                        </select>
													</div>
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
                                                          $value = date('m', $time);
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
												
													<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">From Date</label>
                  <input  type="date" class="form-control" name='FromDate' id="FromDate"
				  placeholder="Enter From Date" value="<?php echo date('Y-m-d')?>" max="<?php echo date('Y-m-d')?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">To Date</label>
                  <input  type="date" class="form-control" name='ToDate' id="ToDate"
				  placeholder="Enter To Date" value="<?php echo date('Y-m-d')?>" max="<?php echo date('Y-m-d')?>">
                </div>
						
												
												<div align="center" class="col-lg-12">
                                                     
                                                       <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
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
			var FromDate=$('#FromDate').val();
			var ToDate=$('#ToDate').val();

			//alert(YEAR);


			$.ajax({
		  url:'Ajax/AjaxVALotsList.php',
		  type:'POST',
		  //data:{'PsId' : PsId,'Month': Month,'Year' : Year,'Type': 1},
		  data:{'PsId' : PsId,'ToDate': ToDate,'FromDate' : FromDate,'Type': 1},
		  success:function(data){
			 showData(data);

					}
			});
	
}

</script>

<script>

	/*	function showData(data){
			
			$("#EmpList").html(data);	

		var Stable=	$("#key-datatable").DataTable({
		order: [[0, 'desc']],
	   keys:!0,
			language:{
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")}

				});			

}
*/

</script>
