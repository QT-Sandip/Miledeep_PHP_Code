<?php 
$PageTitle="Stock Summary";
$PageTitleFolder="ColdStore";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$StartDate = date("Y-m").'-01'; 
    $PurDate = date("Y-m-d");
	 $PurchaseDate=date('d-m-Y', strtotime($PurDate)); 
	 
	 $SqlOptions="SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='CS' ORDER BY OptID asc"; 
$StmtOptions=$iconn->query($SqlOptions);
WHILE($RowOptions=$StmtOptions->FETCH_ARRAY()){
	$AryOptions[]=$RowOptions;
}
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
                                                <div class="form-group col-lg-3">
                    <label for="exampleInputEmail1">Select FromDate: </label>
                    <input type='date' value='<?php echo $StartDate;?>'  class="form-control" name="FromDate" id="FromDate"></input>
					</div>
					
						<div class="form-group col-lg-3">
					<label for="exampleInputEmail1">Select To Date: </label>
                    <input type='date' value='<?php echo $PurDate;?>' class="form-control" name="ToDate" id="ToDate"></input>
					</div>
												
												
                                               <div class="form-group col-lg-2" >              
                  <label for="exampleInputEmail1">Unit</label>
				
               <select class="form-control select2" name="Station" id="Station">
			     <option value="BOTH">ALL</option>
			   <?php foreach($AryOptions as $value){
					if($value['OptionType']=='CS_STATIONS'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['OptID'];
			   ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
				<select>
                </div>
				
				<div class="form-group col-lg-2" >
                  <label for="exampleInputPassword1">Packing Type</label>
				 <select class="form-control select2" name="PackData" id="PackData">	
				 <option value="BOTH">ALL</option>
					 <?php foreach($AryOptions as $value){
					if($value['OptionType']=='PACKING_TYPE'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Value']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
		
				<select>
                </div>
			
				
				 	<div class="col-md-2 col-sm-6 col-xs-12">         				 
                  <label for="exampleInputEmail1">Variety</label>
				  
               <select class="form-control select2" name="Variety" id="Variety">
			  <option value="" >Select Packingtype for Variety</option>
			  </select>
                   </div>
				   
				   
				   
				   
			 <div class="form-group col-sm-2" >
                  <label for="exampleInputEmail1">Exchange Rate</label>
                  <input required type="text" class="form-control" name='Exchange' 
				  id="Exchange" placeholder="Enter Exchange Rate">
                </div>
			  
			
				
				
                                                </div>
												
												<div align="center" class="col-lg-12">
                                                     
                                                       <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button>
											
																		  <button id="print" name='print' class="btn btn-success"><span class="glyphicon glyphicon-print"></span> Print</button>

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
                                    <div class="card-body" id="EmpList">
                                        

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
</html>

<script>
$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			var FromDate=$('#FromDate').val();
			var ToDate=$('#ToDate').val();
			var Station=$('#Station').val();
			var PackData=$('#PackData').val();
            var Variety=$('#Variety').val();
			var Exchange=$('#Exchange').val();
						

			
			 $("#print").show();

			$(document).on('click','#print',function(e){			
			e.preventDefault();
			
			var FromDate=$('#FromDate').val();
			var ToDate=$('#ToDate').val();
			var Station=$('#Station').val();
			var PackData=$('#PackData').val();
            var Variety=$('#Variety').val();
			var Exchange=$('#Exchange').val();
						
			
window.open("Reports/CsSummaryGradewiseReportPrint.php?FromDate=" + FromDate + "&ToDate=" + ToDate + "&Station=" + Station +
 "&PackData=" + PackData + "&Variety=" + Variety + "&Exchange=" + Exchange , "_blank");

		
    	});
			
			 $.ajax({
		  url:'Ajax/AjaxCostingReport.php',
		  type:'POST',
		  data:{'FromDate':FromDate,'ToDate': ToDate,'Station':Station,'PackData': PackData,'Variety':Variety,'Exchange':Exchange},
		  success:function(data){
			   
						 showData(data);

			/*$("#ATT_DATA").html(data);
			$('#example1').DataTable();*/

 //$('#example1').DataTable()
					}
			})
			//return false;
			
			
    		
    
    	});	
		


$(document).on('change','#PackData',function(e){
		
    	e.preventDefault();
			               
			var PackData=$(this).val();
			
			//alert(DEPT_CODE);
			$.ajax({
		  url:'Ajax/AjDrpDwn.php',
		  type:'POST',
		   data:{'Type':'PckStyle','PackData':PackData},
		  success:function(data){
			$("#Variety").html(data);
			//$("#PACK_SIZE").html('<option value="">All Designation</option> ');
					}
			})

    	});



</script>


<script>

function showData(data){
			
		$("#EmpList").html(data);	

		$('#key-datatable').dataTable({
        "bStateSave": true,
		order: [[0, 'desc']],
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
