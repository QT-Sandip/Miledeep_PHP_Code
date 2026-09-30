<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Repacking";
$PageTitleFolder="ColdStore";

  $SqlCsBLData = "SELECT * FROM `CsStoresBlockData`"; 
	$StmtCsBLData = $iconn->query($SqlCsBLData);
	
	$SqlCsFLData = "SELECT * FROM `CsStoresFloorData`";
	$StmtFLBLData = $iconn->query($SqlCsFLData);
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
                                                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Blocks </label>
                  <select class="form-control select2" name="block" id="block">
				  				   <option value="">Select Block</option>

				<?php  while($row = $StmtCsBLData->fetch_array()){ ?>
				   
				 <option value="<?php echo $row['BlockID'] ?>"><?php echo $row['BlockName'] ?></option>
				<?php  } ?>
				 				 
				 </select>
				 
				 
				</div>
				 <div class="form-group col-sm-3" >
                 <label for="exampleInputEmail1">Floors </label>
                  <select class="form-control select2" name="floor" id="floor">
				   <option value="">Select Floor</option>
				 <?php  while($row = $StmtFLBLData->fetch_assoc()){ ?>
				   
				 <option value="<?php echo $row['FloorID'] ?>"><?php echo $row['FloorName'] ?></option>
				<?php  } ?>
				 				 
				 </select>
				 
				</div>
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Rack ID</label>
				 <input class="form-control" name="rackid" id="rackid">				  
				  
					
				</div>
				
				
                   </div>
												
												<div align="right" class="col-lg-12">
               <!--uma-->                                      
                                                   <br>
                 <button id="list" name='submit' class="btn btn-success"><i class= 'fa fa-search'></i>Search</button>
				 
				 <button id="print" name='print'  class="btn btn-primary"> <span class="glyphicon glyphicon-print"></span>
				
Print</button>
              
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
                                        <h4 class="card-title">Purchased Lot - List</h4>
                                        

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
			
			var block=$('#block').val();
			var floor=$('#floor').val();
			var rackid=$('#rackid').val();
			
			//var INV_ID=<?php echo $INVOICE_ID ?>;
			
			//alert(S_DEPT);
			//alert(DATE);
			$.ajax({
		  url:'Ajax/AjaxRepackSearch.php',
		  type:'POST',
		  data:{'block':block,'floor': floor,'rackid':rackid,'type':'repck'},
		  success:function(data){
			   //console.log(data);
 /*$("#ATT_DATA").html(data);
 $('#example1').DataTable()*/
			 showData(data);

 //$('#example1').DataTable()
					}
			})
			//return false;
			
			
    		
    
    	});	
		
		//uma

$(document).on('click','#print',function(e){			
			e.preventDefault();
			
			var block=$('#block').val();
			var floor=$('#floor').val();
			var rackid=$('#rackid').val();
			
			
			//window.location.href = "/ColdStore/CsStockOutPrint.php?block=" + block + "&floor=" + floor + "&rackid=" + rackid + "&type=repck" ;
window.open("CsStockOutPrint.php?block=" + block + "&floor=" + floor + "&rackid=" + rackid + "&type=repck" 
, "_blank");
		
    	});
		
		//uma

		
		
		
$(document).on('click','.repck_bt',function(e){
	e.preventDefault();
	var rck_nme=$(this).val();
	var BAR_CODE_ID=$(this).attr('id');
	
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxRepackDetails.php',
    data: {'BAR_CODE_ID':BAR_CODE_ID},
	
	//dataType: 'json',
    
    success: function(response){
		//console.log(response);
		//	var data = JSON.parse(response);
			$('#myModal').modal('show');
              $('#REPACK_TABLE').html(response);
			
			
			
    // return response;
	 }
  });
  
});



$(document).on('click','.in_repck_bt',function(e){
	e.preventDefault();
	var rck_nme=$(this).val();
	var PALLET_ID=$(this).attr('id');
	
	
	
	var mcs_out='mcs_out'+PALLET_ID;
	var mcs_aval='mcs_aval'+PALLET_ID;
	var OUT='OUT_TYPE'+PALLET_ID;
	var reason_out='reason_out'+PALLET_ID;

	var mcs_aval_no=$('#'+mcs_aval).text();
	var mcs_out_no=$('#'+mcs_out).val();
	var OUT_TYPE=$('#'+OUT).val();
	var bid=$('#bid').text();
	var REMARKS=$('#'+reason_out).val();
	
	var aval_mcs = mcs_aval_no - mcs_out_no;
	
	
	
	
	if(aval_mcs < 0 ){	

alert(mcs_aval_no);
	alert(mcs_out_no);	
		alert('Mcs Exceeds Actual value');
		return;		
	}else if(  mcs_aval_no == mcs_out_no){
		var aval_mcs = mcs_aval_no - mcs_out_no;
	}else {
		
	}
	
	
	
	
	
	//return;
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxPartialRepack.php',
    data: {'aval_mcs':aval_mcs,'out_mcs' : mcs_out_no, 'PALLET_ID' :PALLET_ID,'OUT_TYPE':OUT_TYPE,'bid':bid,'REMARKS':REMARKS},
	
	//dataType: 'json',
    
    success: function(response){
		var data = JSON.parse(response);
			if(data['sts'] == 'failed'){
			alert(data['msg']);
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
				
				
			}
	 }
  });
  
});


$(document).on("click", ".confirm_rpck", function(e){
	e.preventDefault();
	var bid=$('#bid').text();
	var rckid=$('#rckid').text();
	
//	alert (bid);
//	alert (rckid);
	
		var OUT_TYPE=$('#OUT_TYPE').val();
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxRepackSearch.php',
	method:"post",
			  data:{'rckid':rckid,'bid':bid,'OUT_TYPE' : OUT_TYPE,'type':"repck_updt"},
			  success:function(data){
				  //console.log(data);
				    var data = JSON.parse(data);
			if(data['sts'] == 'failed'){
			alert(data['msg']);
			}else{
				$('#tr'+bid).remove();
				alert(data['msg']);
			}
	}
	
	});
	});
	
		
</script>




</body>
   

</html>




<script> 

/*function showData(data){
			
					$("#EmpList").html(data);
 
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
*/


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
