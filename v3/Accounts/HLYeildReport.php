<?php 
$PageTitle="HL Yeild";
$PageTitleFolder="Production";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlDeptList="SELECT * FROM PpcVAStations";
$StmtDept=$iconn->query($SqlDeptList);


$today_date=date('Y-m-d');
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
													<label for="exampleInputEmail1">Gain/Loss</label>
                                                       <select class="form-control select2" name="Gain_Loss" id="Gain_Loss">
                                                            
										<option value="">Gain & Loss </option>
										<option value="Gain">Gain</option>
										<option value="Loss">Loss</option>
								
                                                        </select>
													</div>
                                                </div>
												
		<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">From Date</label>
                  <input type="date" class="form-control" name='Month' id="Month"
				  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $today_date; ?>">
          </div>
		  
		  
		  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">To Date</label>
                  <input type="date" class="form-control" name='Year' id="Year"
				  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $today_date; ?>">
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
                                    <div class="card-body" id="showDataTable">
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
           <?php //include ($IncludesPath."ShowDataTable.php");?>


    </body>
</html>

<script>
$('document').ready(function(){
		getData();

		$(document).on('click','#list',function(e){
			e.preventDefault();

			getData();
    	});
    });


function getData(){
	
	
			var PsId=$('#Gain_Loss').val();
			var Year=$('#Year').val();
			var Month=$('#Month').val();

			//alert(YEAR);


			$.ajax({
		  url:'Ajax/AjHLYeildList.php', 
		  type:'POST',
		  data:{'PsId' : PsId,'Month': Month,'Year' : Year,'Type':2},
		  success:function(data){
			 showData(data);

					}
			});
	
}

</script>

<script>
function showData(data){
			
					$("#showDataTable").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        scrollY: "600px",
        scrollX:true,
        sScrollXInner: "150%", 
        paging:true,
        destroy: true,
        fixedColumns: true,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel',  'colvis']// 'pdf',
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}


</script>


