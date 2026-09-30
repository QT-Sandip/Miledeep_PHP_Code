<?php 
$PageTitle="FBD Report";
$PageTitleFolder="SalesNew";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

 $CurrentMonth=date('m');	
//echo $endYear;exit;

if($CurrentMonth >3){	
$FinClsYear=$Currentyear+1;	
}else{
$FinClsYear=$Currentyear;
}
 //echo $endYear;exit;



$SqlExporter="SELECT * FROM `$TblAdminExporterDetails`";
$StmtExporter=$iconn->query($SqlExporter);


$SqlConsignee="SELECT * FROM `$TblScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlCountries="SELECT * FROM `$TblScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);

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
				  
				  
				  <div class="col-lg-3">
                    <label>Select Buyer: </label>
				
				<select class="form-control select2" name="Buyer" id="Buyer">
			 <option value=" " >All</option>

				   <?php while($RowConsignee=$StmtConsignee->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowConsignee['ConID'];?>"><?php echo $RowConsignee['ConsigneeName'].' - '.$RowConsignee['ConsigneeCountry'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div>
					
			<div class="col-lg-3">
                    <label>Select Country: </label>
				
				<select class="form-control select2" name="Country" id="Country">
			 <option value=" " >All</option>

				   <?php while($RowCountries=$StmtCountries->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowCountries['id'];?>"><?php echo $RowCountries['name'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div>
				  </div>
				  
				  
                <div align="CENTER" class="col-lg-12">
                                                     
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
                                    <div class="card-body" id="PIList">
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
    
    	$(document).on('click','#payBtn',function(e){
			
			//alert("test");
    	e.preventDefault();
			 $('.trremove').remove();
                         
			var refid=$(this).val();			
						var PO_ID = document.getElementById('PO_ID'); //or grab it by tagname etc
						PO_ID.value = ""+refid;  
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
  $(function () {
   $('#example1').dataTable({
   "order": [[ 0, 'desc' ]]
});
  })
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
			  console.log(data);			  
		
			 alert("DONE");
location.reload();
			}
			});	
   			
  }
  
  //function reply_click(clicked_id) {
	    $(document).on('click', '.Delete', function(e){
	  var PIID =$(this).data('id');


   		var r = confirm("Do you Want To Delete This PI "+PIID+"?");
  if (r == true) { 
		 //alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'PI_DELETE','D_ID':PIID},
		  success:function(data){
			  console.log(data);
			  alert("Deleted");
			  		showData(data);

			/*  
			 $("#example1").html(data);
			 alert("Deleted");
			location.reload();*/
			//reload(AjaxDelete.php);
			}
			});	

} else {
			//location.reload();
  }			
  });
  
 
  

  
$(document).on('click','#BtnPIStatus',function(e){			
			e.preventDefault();
		//	alert("INT_LOT_NUM Required");

			var POID=$(this).val();
			//alert(INT_LOT_NUM);
		
						var UNIT=$('#JME_MOURYA').val();
			if(POID ==''){
				$("#POID").focus();
				alert("PO Number Required");
				return;
			}else{
				
				

		$.ajax({
		   url:'Ajax/AjaxPopUpPIStatus.php',
		  type:'POST',
		  data:{'POID':POID , 'TYPE':'KK','UNIT':UNIT},
		  success:function(data){
			 
			 //$('.modal-title').text('Do You Want To Shipped This Po '+INT_LOT_NUM+' ?');
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});
			}
    	});	
		
  	
$(document).on('click','.Status_Update',function(e){
	e.preventDefault();
	//var InvId=$(this).val();
	var InvId=$(this).attr('id');
		var Remarks='Remarks'+InvId;
		var Status='Status'+InvId;

		var Remarks=$('#'+Remarks).val();
		var Status=$('#'+Status).val();

	
	
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxDelete.php',
    data: {'Remarks':Remarks,'TYPE':'PI_STATUS','InvId':InvId,'Status':Status},
	
	//dataType: 'json',
    
    success: function(response){
		var data = JSON.parse(response);
			if(data['sts'] == 'failed'){
			alert(data['msg']);
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							location.reload();

				
			}
	 }
  });
  
});
	
  
  
$(document).on('click','#BtnViewPoStatus',function(e){			
			e.preventDefault();
		//	alert("INT_LOT_NUM Required");

			var INT_LOT_NUM=$(this).val();
			//alert(INT_LOT_NUM);
		
						var UNIT=$('#JME_MOURYA').val();
			if(INT_LOT_NUM ==''){
				$("#INT_LOT_NUM").focus();
				alert("PO Number Required");
				return;
			}else{
				
				

		$.ajax({
		   url:'Ajax/AjPopUpPoStatus.php',
		  type:'POST',
		  data:{'INT_LOT_NUM':INT_LOT_NUM , 'TYPE':'KK','UNIT':UNIT},
		  success:function(data){
			 
			 $('.modal-title').text('Do You Want To Shipped This Po '+INT_LOT_NUM+' ?');
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});
			}
    	});	
		
		
		
$(document).on('click','.shipped_out',function(e){
	e.preventDefault();
	//var InvId=$(this).val();
	var InvId=$(this).attr('id');
		var ShippedOutDate='ShippedOutDate'+InvId;

		var ShippedOutDate=$('#'+ShippedOutDate).val();

	
	
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxDelete.php',
    data: {'ShippedOutDate':ShippedOutDate,'TYPE':'SHIPPED_PO','InvId':InvId},
	
	//dataType: 'json',
    
    success: function(response){
		var data = JSON.parse(response);
			if(data['sts'] == 'failed'){
			alert(data['msg']);
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							location.reload();

				
			}
	 }
  });
  
});
		
		
		
//uma
  
</script>


<script>
  function delete_click(status) {

	var po_id=$('#PO_ID').val();
	var po_id=status;
	//alert(status);
	//alert(po_id);
	//return;
	  
		$.ajax({
		  url:'po_delete.php',
		  type:'POST',
		  data:{'STATUS':status,'INVOICE_ID':po_id},
		  success:function(data){
			  console.log(data);			  
		
			 alert(data);
			location.reload();
			}
			});	
   			
  }
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
	var Buyer=$('#Buyer').val();
	var Country=$('#Country').val();
	  

		
   $.ajax({
		  url:'Ajax/AjFDBRep.php',
		  type:'POST',
		    data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'Buyer':Buyer,'Country':Country,'TYPE':'POLIST'},
		  success:function(data){
			 // $("#PIList").html(data);
			  		showData(data);

			
			}
			});	
}
</script>

<script>



$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  }); 
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });
  	

</script>


<script>

function showData(data){
			
			$("#PIList").html(data);	

		var Stable=	$("#key-datatable").DataTable({
					order: [[0, 'desc']],
			keys:!0,
			language:{
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")}});			
 
}

</script>
