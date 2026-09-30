<?php 
$PageTitle="Applicants List";
$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

 

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
										<?php include "PopUpTable.php"; ?>

						
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										
	  
				  	
      <div class="row">
    			

			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1"> Buyer Name</label>
				  <select class="form-control select2" name="BUYER" id="BUYER">
				  
				  <option value="">All</option>
				  
				   <?php
				   $SqlConsignee="SELECT * FROM `ScrmConsigneeDetails` ORDER BY `ConID` DESC";
$StmtConsignee=$iconn->query($SqlConsignee);
				   while ($RowConsignee=$StmtConsignee->fetch_array()){ 

						?>
							<option value="<?php echo $RowConsignee['ConID'] ;?>" ><?php echo $RowConsignee['ConsigneeName'].' - '.$RowConsignee['ConsigneeCountry'] ?></option>
				
					
<?php 				
	 }   ?>

			  </select>
				  
					
		
                </div>	
				
		 
	</div>
 
				  
				  
                <div align="center" class="col-lg-12">
                                                     
                <button id="list" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button>
					<a href="CreateApplicant.php" class="btn btn-success waves-effect waves-light" >
				<i class="fa fa-plus"></i> New Applicant</a>

				</div>

				 
											    <!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>--> 
	
									 
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
                                        <!--<h4 class="card-title">Proforma Invoice List</h4>-->
                                        
				
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



<script>

$('document').ready(function(){

getApplicantList();

});

$(function(){
  $('#list').click(function(){

getApplicantList();
   
   

  });
});
	
function getApplicantList(){
	   var BUYER=$('#BUYER').val();
			

			$.ajax({ 
		  url:'Ajax/AjaxApplicantList.php',
		  type:'POST',
		  data:{'BUYER':BUYER},
		  success:function(data){
			  showData(data);
 
					}
			});
}	





</script>


