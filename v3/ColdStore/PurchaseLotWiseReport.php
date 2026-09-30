<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Purchase Lot Wise";
$PageTitleFolder="Purchase Lot Wise";

$sql="SELECT RmLotID, 
(SELECT sum(AvalMcs*TotalSlabs*SlabWeight*MFactor) from CsStockData as MCPID where MCPID.StockID=MCPID.StockID) as KGS,
(SELECT sum(LooseBags*SlabWeight*MFactor) from CsStockData as MCPID where MCPID.StockID=MCPID.StockID) as LKGS 
 FROM `CsStockData` as MCPID
 left join AdminPackingStyles as CSP on CSP.PCKID = MCPID.PackingStyleID 
 left join ScrmPoData as SPD on SPD.PoID = MCPID.PoID 
 left join CsBarCodes as CBD on MCPID.BarCodeID=CBD.BarCodeID 
 left join CsStoresRackData as CSR on CSR.BarCodeID=CBD.BarCodeID where
 

 `ProductionTypeID`='PRODUCTION'  GROUP by RmLotID DESC limit 50";

$stmt=$iconn->query($sql);

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
                                    <div class="card-body" id="ClientList">
                    

<table id="datatable-buttons" 
										class="table table-striped 
										table-bordered dt-responsive nowrap w-100" >                        
              

    <thead>

      <tr>
	  
	  

	 
       
      <th>S.No</th>
      <th>Lot Number</th>
      <th>Weight Kgs</th>
      <th>Loose Kgs</th>	  
	  <th>Total</th>
      
	 
      </tr>

    </thead>

	
			

    <tbody>

	

	<?php

	//echo $sql;

	
$s_no=1;
   while($row=$stmt->fetch_array()){


	?>
		


		<td id="<?php  echo $row['ID'];?>" > <?php echo $s_no ?></td>
		<td id="<?php  echo $row['ID'];?>" > <?php echo $row['RmLotID']?></td>  
		<td id="<?php  echo $row['ID'];?>" > <?php echo round($row['KGS'],2)?></td>  
		<td id="<?php  echo $row['ID'];?>" > <?php echo round($row['LKGS'],2)?></td>  
		<td id="<?php  echo $row['ID'];?>" > <?php echo round($row['KGS']+ $row['LKGS'])?></td>  
            

		</td>
      	
	
</tr>

	  <?php 

  $s_no++; }

	  ?>

	  

	 

     </tbody>

  </table>
</div>
</div>
</div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  </div>

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
    
    	$(document).on('click','#payBtn',function(e){
			
			//alert("test");
    	e.preventDefault();
			 $('.trremove').remove();
                         
			var refid=$(this).val();
			
						var PO_ID = document.getElementById('PO_ID'); //or grab it by tagname etc
						PO_ID.value = ""+refid;  

					
					//	alert(refid);

						var large2='TEST'                  
                      						
						$('.modheading').text('Update Status');

						$('#myModal').modal('toggle');						

		 
			
			//return false;
			
    		
    
    	});
    
    	$('#elisa').on('click',function(){
    alert("test");
    		$('form').submit();
    	});
    });    

</script>




<script>
  function status_update_click(status) {

	var po_id=$('#PO_ID').val();
	//alert(status);
	
	  
		$.ajax({
		  url:'po_status.php',
		  type:'POST',
		  data:{'STATUS':status,'INVOICE_ID':po_id},
		  success:function(data){
			  //console.log(data);			  
		
			 alert("DONE");
location.reload();
			}
			});	
   			
  }
</script>
<script>
<script>
$(document).ready(function(){
	  // showData();

	
    
	});


function showData(data){
			
					$("#ClientList").html(data);
 
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
</script>


</html>
