<?php 
$PageTitle="Available Reports List";
$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$uid=$_SESSION['r_id'];

 $PoID=$_GET['PoID'];

$StartDate = date("Y-m-d",strtotime("yesterday"));
$EndDate = date("Y-m-d");

$AccessType=$_SESSION["ACCESS_TYPE"];
$DepartId=$_SESSION["DeptID"];
 

if($AccessType=='1'){
$SqlAvalRep="SELECT * FROM `ScrmGeneralReports` where AccessType >= 1 and AdminAccess=1 and RepStatus=1 ORDER BY SortOrder,ReportName ASC";		
}else if($AccessType =='2'){
$SqlAvalRep="SELECT * FROM `ScrmGeneralReports` where DepartmentID='$DepartId' and AccessType >= 2 and AdminAccess=1 and RepStatus=1 ORDER BY SortOrder,ReportName ASC";			
}else{
$SqlAvalRep="SELECT * FROM `ScrmGeneralReports` where DepartmentID='$DepartId' and AccessType = 3 and AdminAccess=1 and RepStatus=1 ORDER BY SortOrder,ReportName ASC";		
}
//echo $SqlAvalRep; exit;

$StmtRep=$iconn->query($SqlAvalRep);

$SqlSelect="SELECT * FROM `$TblAdminExporterDetails` where ExpID!=3";
//$SqlSelect="SELECT DISTINCT ExpName FROM `$TblAdminExporterDetails`";
	$StmtSelect=$iconn->query($SqlSelect);

?>


<!doctype html>
<html lang="en">

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
                    <label>Select Exporter: </label>
				
				<select class="form-control select2" name="Exporter" id="Exporter">
			 <option value=" " >All</option>

				   <?php while($RowSelect=$StmtSelect->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowSelect['ExpID'];?>"><?php echo $RowSelect['ExpName'].'('.$RowSelect['ExpApprovalNo'].')';?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div>
					




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

	  <!--  <section class="content">-->
						
	<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
       
    </div>
</div>
                                <div class="card">
                                    <div class="card-body" id="PIList">

                                   	<table id="example" class="table table-bordered table-hover">

								<thead class="process_dt_dt" id="process_dt_dt">

								<?php 
								
								while($Row=$StmtRep-> fetch_array()) {
								

						?>
								 
								 <tr >
								 <td><?php echo $Row['ReportName'] ?></td>
								 
								 <td><button  id="LotList" 
								 value="<?php echo $Row['ReportLink'] ?>"
								 class="btn btn-primary" >View</button>
								 
								 
								
								 
								 
								 </td>
								 
								 <td><button  id="Download" 
								 value="<?php echo $Row['ReportExcel'] ?>"
								 class="btn btn-primary" >Download Excel</button>
								 </td>
								 

								 <!--<td> <a target="_blank" href="DocFOBReportDownload?from_date=<?php  echo $Fromdate;?>&to_date=<?php  echo $ToDate;?>&Exporter=<?php  echo $Exporter;?>" 
						 class="btn btn-primary"  ><i class="mdi mdi-pencil font-size-18"></i>Download Excel</a>
                        
								 </td>-->
								 
								</tr>
								
								<?php $c++;		

								} ?>

								</thead>

								</table>

     

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
						<!-- </section>-->
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
      

        <!-- Required datatable js -->
		 <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
		  
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
		 <script src="<?php echo $BaseDist ?>assets/js/pages/form-advanced.init.js"></script>

		 
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>
        
        <!-- Responsive examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

        <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

    </body>




<script type="text/javascript">

$('document').ready(function(){
//showData(data);
$(document).on('click','#LotList',function(e){	


		var from_date='';
	//	var to_date=$('#TO_DATE').val();
		var to_date='';
		var Link=$(this).val();
		var Exporter=$('#Exporter').val();
	//	alert(Link);


	var dynamic_link=Link+'?from_date='+from_date+'&'+'to_date='+to_date+'&'+'Exporter='+Exporter;
	window.open(dynamic_link);

	});
	
	$(document).on('click','#Download',function(e){	


		var from_date=$('#Fromdate').val();
	//	var to_date=$('#TO_DATE').val();
		var to_date=$('#ToDate').val();
		var Link=$(this).val();
		var Exporter=$('#Exporter').val();
	//	alert(Link);


	var dynamic_link=Link+'?from_date='+from_date+'&'+'to_date='+to_date+'&'+'Exporter='+Exporter;
	window.open(dynamic_link);

	});





		
		});

</script>





<script>
if ( $('[type="date"]').prop('type') != 'date' ) {
    $('[type="date"]').datepicker();
}
</script>



