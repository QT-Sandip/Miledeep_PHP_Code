<?php 
session_start();
extract($_POST);
extract($_GET);

$PageTitle="Consignment Details";
$PageTitleFolder="ColdStore";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlPoList="SELECT * FROM `ScrmPoData`  where Status in(1,5) ORDER BY ShipByDate DESC";
$StmtPoList=$iconn->query($SqlPoList);

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
                                               <div class="form-group col-lg-2" >              
                  <label for="exampleInputEmail1">Unit</label>
				
               <select class="form-control select2" name="Station" id="Station">
			   <?php foreach($AryOptions as $value){
					if($value['OptionType']=='CS_STATIONS'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['CsOptID'];
			   ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
				<select>
                </div>
				
				
				
			 <div class="form-group col-lg-2" >              
					 
					 
                  <label for="exampleInputEmail1">PO Number</label>
				  
               <select class="form-control select2" name="PoID" id="PoID">
			   <option value="" >ALL</option>
					 <?php WHILE($RowPoList=$StmtPoList->FETCH_ARRAY()){
					 ?>
			 
					<option value="<?php echo $RowPoList['PoID'] ?>">
					<?php echo $RowPoList['PoNo'] ?></option>
					<?php  }?>
		
				<select>
                   </div>
                   </div>
				  
				
                              
				
				 <div align="right" class="form-group col-lg-6" >
			  
                  <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button> 
				 				  </input>

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
                                    <div class="card-body" id="StockReport">
                                        

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

<script>
 
  //$("#PoID").select2();      
$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			var PackData=$('#PackData').val();
			var Group=$('#Group').val();
			var Category=$('#Category').val();
			var FreezingType=$('#FreezingType').val();
			var UNIT=$('#Station').val();
			var PoID=$('#PoID').val();
			
			//var INV_ID=<?php echo $INVOICE_ID ?>;
			
			$.ajax({ 
		  url:'Ajax/AjaxPoSearch.php',
		  type:'POST',
		  data:{'PoID':PoID , 'TYPE':'LIST','UNIT':UNIT},
		  success:function(data){
			  			 showData(data);

			 
					}
			})
			
			
    		
    
    	});	




//umask

$(document).on('click','#print',function(e){			
			e.preventDefault();
			
			var PackData=$('#PackData').val();
			var Group=$('#Group').val();
			var Category=$('#Category').val();
			var FreezingType=$('#FreezingType').val();
			var UNIT=$('#Station').val();
			var PoID=$('#PoID').val();
			
window.open("Reports/CsLoadingReportPrintt.php?PoID=" + PoID + "&UNIT=" + UNIT + "&TYPE=LIST" 
, "_blank");

		
    	});
	
//uma

		
		
</script>




<script>

function showData(data){
			
		$("#StockReport").html(data);	

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
