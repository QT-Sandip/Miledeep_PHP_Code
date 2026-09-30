<?php 
$PageTitle="ETA/ETD Status";
$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

 $CurrentMonth=date('m');	

 
if($CurrentMonth > 3){	
$FinClsYear=$Currentyear+1;	
}else{
$FinClsYear=$Currentyear;
}

if($SelectedYear!=''){
	
if($CurrentMonth > 3){	
$FinClsYear=$SelectedYear+1;	
}else{
$FinClsYear=$SelectedYear;
}
}



$SqlExporter="SELECT ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1";
$StmtExporter=$iconn->query($SqlExporter);

$SqlProcessor="SELECT ExpID,ExpName FROM `AdminExporterDetails` where ExpType=2";
$StmtProcessor=$iconn->query($SqlProcessor);

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

    <!--<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">-->
    <body data-sidebar="dark" class="sidebar-enable">
	
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
                    <label>Select Exporter: </label>
				
				<select class="form-control select2" name="ExporterID" id="ExporterID">
			 <option value="" >All</option>

			<?php while($RowExporter=$StmtExporter->fetch_array()) { ?>  
				
				<option Value="<?php echo $RowExporter['ExpID'];?>"><?php echo $RowExporter['ExpName'];?></OPTION>
				
				<?php } ?>
				</select> 
</div> 

<div class="row">
    			<div class="col-lg-3">
                    <label>Select Processor: </label>
				
				<select class="form-control select2" name="ProcessorID" id="ProcessorID">
			 <option value="" >All</option>

			<?php while($RowProcessor=$StmtProcessor->fetch_array()) { ?>  
				
				<option Value="<?php echo $RowProcessor['ExpID'];?>"><?php echo $RowProcessor['ExpName'];?></OPTION>
				
				<?php } ?>
				</select> 
</div>	

         <div class="col-lg-3">
                    <label>ETD/ETA </label>
				
				<select class="form-control select2" name="ETD_ETA" id="ETD_ETA">
			 <option value="" >All</option>
			 <option value="ETD" >ETD</option>
			 <option value="ETA" >ETA</option>
</select> 
</div>
					
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">From Date</label>
          <input   type="date" class="form-control" name='FromDate' id="FromDate">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">To Date</label>
          <input   type="date" class="form-control" name='ToDate' id="ToDate">
                </div>
				
				   <!--<div class="form-group col-lg-2">
                	  <label>Select Year: </label>
                   <select class="form-control select2" name="YEAR" id="YEAR">
                         
                           <?php
							 						
                            /*for($Startyear; $Startyear <= $endYear; $Startyear++) { 
						
									$year=($Startyear-1).'-'.($Startyear); ?>
                                                            
                        <option  
						
						<?php  if($Startyear == $FinClsYear){ ?> selected="selected" 
						<?php   }   	?>			
						
						
						
						
				   
				   value="<?php echo $Startyear-1 ?>"><?php echo $year ?></option>
							
                                                         
							<?php }  */ ?>
                   <select>
				  </div>-->
				  </div>
				  
				  
                <div align="RIGHT" class="col-lg-6">
                                                     
                <button id="list" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button>

                    <button  id="Download" 
					value="<?php echo $_SESSION["ClientPath"].'Reports/DocETAStatusReportDownload.php'?>"
					class="btn btn-success" >Download Excel</button>
					
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

<Script>

$(document).on('click','#Download',function(e){	
   
	var Exporter=$('#ExporterID').val();
    var Processor=$('#ProcessorID').val();
	 var ETD_ETA=$('#ETD_ETA').val(); 
	var ToDate=$('#ToDate').val();
	var FromDate=$('#FromDate').val();
    var Link=$(this).val();
   
    var dynamic_link=Link+'?Exporter='+Exporter+"&TYPE=POLIST"+"&Processor="+Processor";
    window.open(dynamic_link);
});

$('document').ready(function(){
	
	getPiList(); 
    	$('#elisa').on('click',function(){
    alert("test");
    		$('form').submit();
    	});
    });    

</script>

<script>
  $(function () {
   $('#example1').dataTable({
   "order": [[ 0, 'desc' ]]
});
  })
</script>




<script>
$(function(){
  $('#list').click(function(){

getPiList();
   
   

  });
});


function getPiList(){
	   var ExporterID=$('#ExporterID').val();
	   var ProcessorID=$('#ProcessorID').val();
	   var ToDate=$('#ToDate').val();
	   var FromDate=$('#FromDate').val();
	    var ETD_ETA=$('#ETD_ETA').val(); 
	

		
   $.ajax({
		  url:'Ajax/AjaxEtaPoList.php',
		  type:'POST',
		   data:{'Exporter':ExporterID,'Processor':ProcessorID,'FromDate':FromDate,'ToDate':ToDate,'ETD_ETA':ETD_ETA},
		  success:function(data){
			  			  		showData(data);

			
			}
			});	
}


</script>

