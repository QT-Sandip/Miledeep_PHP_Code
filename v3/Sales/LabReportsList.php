

<?php 
$PageTitle="Pending Contracts";
$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

 $CurrentMonth=date('m');	
//echo $endYear;exit;

if($CurrentMonth >3){	
$FinClsYear=$Currentyear+1;	
}else{
$FinClsYear=$Currentyear;
}
 //echo $endYear;exit;

$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);


$SqlExporter="SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
$StmtExporter=$iconn->query($SqlExporter);

?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Jlsf/User.css"  rel="stylesheet" type="text/css" />

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
    			
                     <div class="form-group col-lg-2">
                	  <label>Select Year: </label>
                   <select class="form-control select2" name="Year" id="Year">
                         
                           <?php
							 						
                            for($Startyear; $Startyear <= $endYear; $Startyear++) { 
						
									$year=($Startyear-1).'-'.($Startyear); ?>
                                                            
                        <option  
						
						<?php  if($Startyear == $FinClsYear){ ?> selected="selected" 
						<?php   }   	?>			
						
						
						
						
				   
				   value="<?php echo $Startyear-1 ?>"><?php echo $year ?></option>
							
                                                         
							<?php }   ?>
                   <select>
				  </div>

					<div class="col-lg-3">
                    <label>Select Exporter: </label>
				
				<select class="form-control select2" name="Exporter" id="Exporter">
			 

				   <?php while($row_add=$StmtExporter->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'];?></OPTION>
				
				<?php } ?>
				<option value="" >All</option>
				<select> 

				
					</div>
					
					
				  
				  <div class="col-lg-3">
                    <label>Select Consignee: </label>
				
				<select class="form-control select2" name="Consignee" id="Consignee">
			 <option value="" >All</option>

				   <?php while($RowConsignee=$StmtConsignee->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowConsignee['ConID'];?>"><?php echo $RowConsignee['ConsigneeName'].' - '.$RowConsignee['ConsigneeCountry'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div>
					
					<div class="col-lg-3">
                    <label>Select Country: </label>
				
				<select class="form-control select2" name="Country" id="Country">
			 <option value="" >All</option>

				   <?php while($RowCountries=$StmtCountries->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowCountries['id'];?>"><?php echo $RowCountries['name'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div>
					
					
				  </div>
				  
				  
                <div align="center" class="col-lg-12">
                                                     
                <button id="list" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button>
				

				
				 
											<!--  <button  id="LotList" 
				value="<?php echo $_SESSION["ClientPath"].'Reports/DocShipmentPlanningReport.php' ?>"
								 class="btn btn-success" >View</button>
				  <!--  <button  id="Download" 
					value="<?php echo $_SESSION["ClientPath"].'Reports/DocShipmentReportDownload.php'?>"
								 class="btn btn-success" >Download Excel</button> -->


							<!--		 <button  id="Download" 
					value="<?php echo $_SESSION["ClientPath"].'Reports/DocShipmentPlanningReportDownload.php'?>"
								 class="btn btn-success" >Download Excel</button> -->
											    
	</div>
									 
										</div>
                        <!-- end row -->
									</div>
									</div>
									</div>
        
                                         <!-- end col -->
                        </div> <!-- end row -->

                        <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="POList">
                                        <!--<h4 class="card-title">Proforma Invoice List</h4>-->
                                        

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
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


<script>



  $('#list').click(function(){

		getList();
   
   

  });




function getList(){
   
    var Exporter=$('#Exporter').val();
	var Year=$('#Year').val();
	var Consignee=$('#Consignee').val();
	var Country=$('#Country').val();
	var InvType=$('#InvType').val();
	 

		
   $.ajax({
		  url:'Ajax/AjaxLabReportsList',
		  type:'POST',
		   // data:{'Exporter':Exporter,'YEAR':YEAR,'TYPE':'POLIST'},
		    data:{'Exporter':Exporter,'Year':Year,'Consignee':Consignee,'Country':Country,'InvType':InvType},
		  success:function(data){
			  			  		showData(data);

			}
			});	
  //});
}
</script>

<script>

function showData(data){
			
					$("#POList").html(data);
						    var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "100%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : true,
		 order: [[1, 'desc']],
          dom: 'Blfrtip',
        buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'Lab Reports List', footer: true,
            exportOptions: {
                columns: ':visible'
            }
        }],
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );

		}
$("#Country, #Consignee").on("change", function () {
    $("#POList").html("");
});
</script>

