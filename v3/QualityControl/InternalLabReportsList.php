<?php 
/*ini_set( 'display_errors', 1 );
error_reporting( E_ALL ); */
$PageTitle="IH LAB REPORTS";
$PageTitleFolder="Quality Control";
  $TYPE=$_GET['id'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

 $today_date=date('Y-m-d'); 
$YesterdayDate=date('Y-m-d',strtotime("-1 days"));


 $SqlReportNames="SELECT * FROM `QcIHReportsList` where Status='1'";
$StmtReportNames=$iconn->query($SqlReportNames);


?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/Sme/User.css"  rel="stylesheet" type="text/css" />

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
    			


				<div class="form-group col-lg-3">
                	  <label>Select From Date: </label>
                   <input  type="date" class="form-control" name='FROM_DATE' id="FROM_DATE"
				   value='<?php echo $YesterdayDate;?>'>
				  </div>
				  
					
					 <div class="form-group col-lg-3">
                	  <label>Select To Date: </label>
                  <input  type="date" class="form-control" name='TO_DATE' id="TO_DATE"
				   value='<?php echo $today_date;?>'></div>
				  
				  
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Reports</label>             
				  <select class="form-control select2" name="ReportID" id="ReportID">
				  <?php while ($ReportNames=$StmtReportNames->fetch_array()){ ?>
				     <option  
					 value="<?php echo $ReportNames['ReportID'] ?>">
					 <?php echo $ReportNames['ReportName'] ?></option>
				   
				 <?php  }?>			  
				  </select>
   		 
               
                </div>
                                                </div>
                                                

				   <div class="col-xs-12"  align="center">
                 <button id="list" align="right" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button>
					
					
                 <button  id="createreport" type="button"  value="<?php echo $CreateLink ;?>"
					 class="btn btn-success" >Create Report</button>
					
                 </div>
				 
				 
				 
				 </div>

 
											    <!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-lg btn-flat"><i class="fa fa-plus"></i> New Employee</a>--> 
	
									 
										</div>
                        <!-- end row -->
									</div>
									
                                         <!-- end col -->
                        </div> <!-- end row -->

                                <div class="card">
                                    <div class="card-body" id="POList">
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
	
	// $.stickysidebarscroll("#tests",{offset: {top: 10, bottom: 200}});
    
   




$(function(){
  $('#list').click(function(){
   // window.location.href = 'DocPoList.php?YEAR='+$(this).val();
   
   
	var FROM_DATE=$('#FROM_DATE').val();
	var TO_DATE=$('#TO_DATE').val();
	var TYPE=$('#ReportID').val();
//var TYPE=$('#TYPE').val();
	//var TYPE='<?php echo   $TYPE ?>';
	  

	if(TYPE =='1'){		
   $.ajax({
		  url:'Ajax/IH_AjaxBoreWaterAnalysisList.php',
		  type:'POST',
		    data:{'FROM_DATE':FROM_DATE,'TO_DATE':TO_DATE,'TYPE':TYPE},
		  success:function(data){
			  /*$("#ATT_DATA").html(data);
 $('#example1').DataTable()	*/
showData(data); 
		
			 //alert(data);
			//location.reload();
			}
			});	
			
			
	}else if(TYPE =='2'){
		
		$.ajax({
		  url:'Ajax/IH_AjaxBoreWaterAnalysisList.php',
		  type:'POST',
		    data:{'FROM_DATE':FROM_DATE,'TO_DATE':TO_DATE,'TYPE' : TYPE},
		  success:function(data){
			 /* $("#ATT_DATA").html(data);
 $('#example1').DataTable()	*/
showData(data); 
		
			 //alert(data);
			//location.reload();
			}
			});	
		
	}else if(TYPE =='3'){
		
		$.ajax({
		  url:'Ajax/IH_AjaxBoreWaterAnalysisList.php',
		  type:'POST',
		    data:{'FROM_DATE':FROM_DATE,'TO_DATE':TO_DATE,'TYPE' : TYPE},
		  success:function(data){
			  /*$("#ATT_DATA").html(data);
 $('#example1').DataTable()	*/
showData(data); 
		
			 //alert(data);
			//location.reload();
			}
			});	
		
	}else if(TYPE =='4'){
		
		$.ajax({
		  url:'Ajax/IH_AjaxBoreWaterAnalysisList.php',
		  type:'POST',
		    data:{'FROM_DATE':FROM_DATE,'TO_DATE':TO_DATE,'TYPE' : TYPE},
		  success:function(data){
			 
		showData(data);
			 //alert(data);
			//location.reload();
			}
			});	
		
	}else if(TYPE =='5'){
		
		$.ajax({
		  url:'Ajax/IH_AjaxBoreWaterAnalysisList.php',
		  type:'POST',
		    data:{'FROM_DATE':FROM_DATE,'TO_DATE':TO_DATE,'TYPE' : TYPE},
		  success:function(data){
			 /* $("#ATT_DATA").html(data);
 $('#example1').DataTable()*/		  
		showData(data);
			 //alert(data);
			//location.reload();
			}
			});	
		
	}
  });
});
	
		
		
		
		
		
		
    
    });    


</script>




<Script>

$('document').ready(function(){

$(function(){
	$(document).on('click','#createreport',function(e){
  
	var TYPE=$('#ReportID').val();

	if(TYPE =='1'){		
 var CreateLink='IHAddBoreWaterAnlys';			
	}else if(TYPE =='2'){
		 var CreateLink='IHAddOutletWater';			
												
	}else if(TYPE =='3'){		
	 var CreateLink='IHAddSWABReport';					
	}else if(TYPE =='4'){
	 var CreateLink='IHAddIce_WaterReport';		
		
	}else if(TYPE =='5'){
		 var CreateLink='IHAddDlyWaterTPDMicroResult';	
		
	}
		window.open(CreateLink);
		//alert(CreateLink);
  });
});
	
   
    });    


</script>


<script> 

	
		function showData(data){
			
			$("#EmpList").html(data);	

	    var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : true,
		 order: [[1, 'desc']],
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }, language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],
    } );		


}

</script>


</html>

<script>

function showData(data){
			
					$("#POList").html(data);
 
$('#key-datatable').dataTable({
        "bStateSave": true,
		order: [[1, 'desc']],
		keys:!0,
			language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    });
		}

</script>

