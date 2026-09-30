<?php 
$PageTitle="Available Reports";
$PageTitleFolder="SalesNew";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

 $PoID=$_GET['PoID'];




 $SqlPo="SELECT * FROM `$TblScrmPoData` where PoID='$PoID'"; 
$StmtPo=$iconn->query($SqlPo);
$RowPo=$StmtPo->fetch_array();
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

						
                       
        
                                         <!-- end col -->
                        </div> <!-- end row -->

                        <div class="row">
                            <div class="col-12">
		         <h2 class="card-title" align="center">Reports for Purchase Order - <span style="color:Tomato; bold"><?php echo $RowPo['PoNo'] ?></span></h2>

	  <!--  <section class="content">-->
						
	<div class="modal fade" id="myModal" role="dialog">
    
</div>
                                <div class="card">
                                    <div class="card-body" id="PIList">

                                        

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


<Script>

  $('document').ready(function(){

	var DocType='1';
     var InvId="<?php echo $PoID; ?>";
  	getAvalDos(DocType,InvId);




      /*	$(document).on('click','#list',function(e){
		var DocType=$('#DocType').val();

  		
  						
						 $.ajax({
  		  url:'Ajax/AjaxDocList.php',
  		  type:'POST',
  		    data:{'Type' : 'DocCreate','DocType':DocType,'InvId' : InvId},
  		  success:function(data){
  			  $("#CreateDocID").html(data);
            //  $('#example1').DataTable();
			  $('.modheading').text('Select Document');
			  $('#myModal').modal('toggle');
  			}
  			}); 
						
						
						
						
						
  			//return false;
      	});

*/
      });
	  
	  
	  function getAvalDos(DocType,InvId){
		      $.ajax({
  		  url:'Ajax/AjaxAvalPoRepList.php',
  		  type:'POST',
  		    data:{'Type' : 'DocList','DocType':DocType,'InvId' : InvId},
  		  success:function(data){
  			 showData(data);
  			}
  			}); 
	  }

  </script>





  <script>
  $(function(){
    $('#DocType').change(function(){
     var DocType=$('#DocType').val();
     var InvId="<?php echo $PoID; ?>";
	getAvalDos(DocType,InvId);
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

function showData(data){
			
					$("#PIList").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}

</script>
