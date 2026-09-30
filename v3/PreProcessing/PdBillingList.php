<?php 
$PageTitle="PD Billing List";
$PageTitleFolder="PreProcessing";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$TypeID =$_GET['ID'];

IF($TypeID==1){
	
$PageTitle="Daily Deheading Summary ";
$PageTitleFolder="PreProcessing";
	
}ELSE IF($TypeID==3){

$PageTitle="Daily ValueAddition Summary ";
$PageTitleFolder="PreProcessing";	
	
}

?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

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
                                               

                                                
												
												<div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Month" 
														id="Month">
															<?php
															$start = strtotime('first day of this month');
                                                     for ($i = 0; $i < 12; $i++) {
                                                          $time = strtotime(sprintf('%d months', $i), $start);   
													   
                                                          $label = date('F', $time);   
                                                          $value = date('n', $time);
                                                          echo "<option value='$value'>$label</option>";
                                                         }
														 
														 ?>
                                                        </select>
													</div>
                                                </div>
												
												<div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Year" 
														id="Year">
                                                           <?php
														
														for($year; $Startyear <= $Currentyear; $Startyear++) { ?>
                                                            
                                                                 <option value="<?php echo $Startyear ?>"<?php if($Startyear==$Currentyear){ echo 'selected="selected"'; }?>>
																 <?php echo $Startyear ?></option>
                                                             
                                                            <?php  } 
                                                         
                                                            ?>
                                                        </select>
													</div>
                                                </div>
                                                </div>
												
												<div align="center" class="col-lg-12">
                                                     
                                                       <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button>
											
											<a href="#" id="AddBill" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> Add Bill</a>
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
                                    <div class="card-body" id="EmpList">
                                        <h4 class="card-title">Dailywise ValueAddition Data</h4>
                                        

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
</html>

<script>
$('document').ready(function(){
		getData();

		$(document).on('click','#list',function(e){
			e.preventDefault();

			getData();
    	});



    	
    });


	
	$(document).on('click','#AddBill',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	  var PoID =$(this).data('id');
	  
	 //$('#Update').val(PoID);
	
	
		 $.ajax({
		   url:'Ajax/AjaxPDBillPopUp.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			 
			 $('.modal-title').text('PD Billing Details');
			$("#PopUpTable").html(data);
		
			$('#myModal').modal('show');

					}
			});
			
    	});	

	$(document).on('click','#EditPDBill',function(e){
	 			e.preventDefault();

	//var PoNo=$(this).data('value');
	  var PdBillID =$(this).data('id');
	  
	 //$('#Update').val(PoID);
	
	
		 $.ajax({
		   url:'Ajax/AjaxPDBillPopUp.php',
		  type:'POST',
		  data:{'PdBillID':PdBillID},
		  success:function(data){
			 
			 $('.modal-title').text('PD Billing Details');
			$("#PopUpTable").html(data);
		
			$('#myModal').modal('show');

					}
			});
			
    	});	
	
	$(document).on('click','.GenerateBill',function(e){
	e.preventDefault();
	//var InvId=$(this).val();
		//var InvId=$(this).attr('id');
		var ToDate=$('#ToDate').val();
		var FromDate=$('#FromDate').val();
		var InvNo=$('#InvNo').val();
		var StationId=$('#StationId').val();
		
		if(StationId==''){
			alert('Please Select Station');
			return;	
		}
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxAddPDBill.php',
    data: {'ToDate':ToDate,'FromDate':FromDate,
	'InvNo':InvNo,'Type':'GenerateBill','StationId':StationId},
	
	//dataType: 'json',
    
    success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			//getPiList();

			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							//location.reload();
							getData();
//getPiList();
				
			}
	 }
  });

	
});

$(document).on('click','.UpdateBill',function(e){
	e.preventDefault();
	
		var BillClosedDate=$('#BillClosedDate').val();
		var PdBillID =$(this).data('id');
		
		if(BillClosedDate==''){
			alert('Please Select Date');
			return;	
		}

	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxAddPDBill.php',
    data: {'BillClosedDate':BillClosedDate,'PdBillID':PdBillID,'Type':'UpdateBill'},
	
	//dataType: 'json',
    
    success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			//getPiList();

			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
			getData();
							//location.reload();
//getPiList();
				
			}
	 }
  });

	
});


  
  $(document).on('click', '.Delete', function(e){
	  var PdBillID =$(this).data('id');


   		//var r = confirm("Do you Want To Delete This Bill "+POID+"?");
   		var r = confirm("Do you Want To Delete This Bill ?");
  if (r == true) { 
		$.ajax({
		  url:'Ajax/AjaxAddPDBill.php',
		  type:'POST',
		  data:{'Type':'PDBillDelete','PdBillID':PdBillID},
		  success:function(response){
			 
					
					var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);

			}else{
			alert(data['msg']);
getData();
				
			}

			}
			});	

} else {
			
  }			
  });
  
  


function getData(){
	
			
			var Year=$('#Year').val();
			var Month=$('#Month').val();
			


			$.ajax({
		  url:'Ajax/AjPDBillingList.php',
		  type:'POST',
		  data:{'Month': Month,'Year' : Year},
		  success:function(data){
			 showData(data);

					}
			});
	
}

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
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );
			

}
</script>
