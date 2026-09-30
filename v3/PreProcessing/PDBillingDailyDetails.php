<?php 
$PageTitle="Lots Lists";
$PageTitleFolder="Value Addition";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlContractor="SELECT * FROM `PpcContractMaster`";
$StmtContractor=$iconn->query($SqlContractor);


?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

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
          <div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1"> Contractor</label>
                 <select class="form-control select2" id="ContactorID" name="ContactorID" required>
				  <option value="">Select Contractor</option>
				 <?php
				
				 while($RowContractor=$StmtContractor->fetch_array()){  ?> 
				
					<option <?php if($RowSubContract['ContactorID']==$RowContractor['ContractorID']){  ?>selected=="selected" <?php } ?>
					
					value="<?php echo $RowContractor['ContractorID'] ;?>" >
				 <?php  echo $RowContractor['ContrctrName']; ?>
					</option>
					<?php }   ?>
				</select>
			</div> 
												
											
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Date</label>
                  <input  type="date" class="form-control" name='FromDate' id="FromDate"
				  placeholder="Enter From Date" value="<?php echo date('Y-m-d')?>" max="<?php echo date('Y-m-d')?>">
                </div>
				
	          <div align="center" class="col-lg-12">
           <button id="list" align="center" type="button" 
		   class="btn btn-primary waves-effect waves-light">
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
		//showData(data);


		$(document).on('click','#list',function(e){
			e.preventDefault();

			getData();
    	});
    });


function getData(){
	
		
			var ContactorID=$('#ContactorID').val();
            var FromDate=$('#FromDate').val();
			
			$.ajax({
		  url:'Ajax/AjaxPDBillingDetailsList.php',
		  type:'POST',
		  data:{'ContactorID' : ContactorID,'FromDate' : FromDate,'Type': 2},
		  success:function(data){
			 showData(data);

					}
			});
	
}

</script>

<script>
$(document).on('change','#ContactorID',function(e){
       e.preventDefault();
		getData();
		
    
    	});
		

$(document).on('change','#Month',function(e){			
			e.preventDefault();		
			getData();
});				


</script>


