<?php 
$PageTitle="FBD List";
$PageTitleFolder="SalesNew";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

 $CurrentMonth=date('m');	

 if($_GET['Year']=='Na'){
$SelectedYear='';
}else{
$SelectedYear=$_SESSION["SelectedYear"];
}
//echo $_SESSION["SelectedYear"];
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



$SqlExporter="SELECT * FROM `$TblAdminExporterDetails`";
$StmtExporter=$iconn->query($SqlExporter);

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
				
				<select class="form-control select2" name="NAME_OF_PROCESSOR" id="NAME_OF_PROCESSOR">
			 <option value=" " >All</option>

				   <?php while($row_add=$StmtExporter->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'].' - '.$row_add['ExpApprovalNo'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div>
					
					
				   <div class="form-group col-lg-2">
                	  <label>Select Year: </label>
                   <select class="form-control select2" name="YEAR" id="YEAR">
                         
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
				  </div>
				  
				  
                <div align="RIGHT" class="col-lg-6">
                                                     
                <button id="list" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button>
					
				
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
                                    <div class="card-body" id="FBDList">
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






<Script>

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
//$(document).on('click','#BtnEditETA',function(e){			
$(document).on('click','.BtnEditETA',function(e){			
			e.preventDefault();

			//var PiID=$(this).val();
				  var PiID =$(this).data('id');

			var ETA='#ETA'+PiID;
			var FbdEtaRemarks='#FbdEtaRemarks'+PiID;
			
	  
	  var ETA=$(ETA+"").val();
	  var FbdEtaRemarks=$(FbdEtaRemarks+"").val();
	var r = confirm("Do you Want To Submit?");
  if (r == true) {   

$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxDelete.php',
    data: {'ETA':ETA,'FbdEtaRemarks':FbdEtaRemarks,'TYPE':'UPDATEETA','PiID':PiID},
	

    success: function(response){
					getPiList();

		
	 }
  });
  }
else {
			//location.reload();
  }	
		
    	});	
		
	
</script>


<script>
$(function(){
  $('#list').click(function(){

getPiList();
   
   

  });
});


function getPiList(){
	   var EXPORTER=$('#NAME_OF_PROCESSOR').val();
	var YEAR=$('#YEAR').val();
	  

		
   $.ajax({
		  url:'Ajax/AjaxFBDPoList.php',
		  type:'POST',
		    data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'POLIST'},
		  success:function(data){
			  			  		showData(data);

			 /* $("#ATT_DATA").html(data);
			  $('#example1').dataTable({
   "order": [[ 0, 'desc' ]]
});*/
 //$('#example1').DataTable()		  
		
			// alert(data);
			//location.reload();
			}
			});	
}




$(document).on('click','#BtnTrack',function(e){			
			e.preventDefault();

		var ContainerTrackingUrl=$(this).val();
		var ContainerNo='#ContainerNo';
		var ContainerNo=$(ContainerNo+"").val();
		//alert(ContainerNo);
		copyToClipboard(ContainerNo);
		
		
		function copyToClipboard(ContainerNo) {
    var dummy = document.createElement("input");
    // to avoid breaking orgain page when copying more words
    // cant copy when adding below this code
    // dummy.style.display = 'none'
    document.body.appendChild(dummy);
    //Be careful if you use texarea. setAttribute('value', value), which works with "input" does not work with "textarea". – Eduard
    dummy.value = ContainerNo;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
}
	
open(ContainerTrackingUrl);




		
    	});


</script>

<script>

function showData(data){
			
			$("#FBDList").html(data);	

$('#key-datatable').dataTable({
        "bStateSave": true,
		order: [[1, 'desc']],
		keys:!0,
			language:{ 
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    });
			
 
}

</script>