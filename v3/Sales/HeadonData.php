<?php 
$PageTitle="Head On Report";
$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$uid=$_SESSION['r_id'];
 $CurrentMonth=date('m');	

 /*if($_GET['Year']=='Na'){
$FinClsYear=getCurrentFinancialYear();
}else{
$FinClsYear=$_SESSION["SelectedYear"];
}
*/


$SqlExporter="SELECT GROUP_CONCAT(ExpID) as ExpID,ExpDisplayName FROM `AdminExporterDetails` GROUP BY ExpGroup";
$StmtExporter=$iconn->query($SqlExporter);


$SqlConsignee="SELECT * FROM `$TblScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlCountries="SELECT * FROM `$TblScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);

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
			 <option value="" >All</option>

				   <?php while($row_add=$StmtExporter->fetch_array()) { 
				
				?>  
				
				<!--<option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'].' - '.$row_add['ExpApprovalNo'];?></OPTION>-->
				<option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpDisplayName'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div>
					
					
				  <!-- <div class="form-group col-lg-2">
                	  <label>Select Year: </label>
                   <select class="form-control select2" name="YEAR" id="YEAR">
                         			 <option value="" >All</option>

                           
							 						
                             <?php
							 						
                           /* for($Startyear; $Startyear < $endYear; $Startyear++) { 
						
									$year=($Startyear).'-'.($Startyear+1); ?>
                                                            
                        <option  
						
						<?php echo $Startyear; if($Startyear == $FinClsYear){ ?> selected="selected" 
						<?php   }   	?>			
						
						
						
						
				   
				   value="<?php echo $Startyear ?>"><?php echo $year ?></option>
							
                                                         
							<?php } */  ?>
                   <select>
				  </div>-->
				  
				  
				
				  </div>
				  
				  
                <div align="CENTER" class="col-lg-12">
                                                     
                <button id="list" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button>
					
					<button  id="LotList" 
				value="<?php echo $_SESSION["ClientPath"].'Reports/DocHeadonReport.php' ?>"
								 class="btn btn-success" >View</button>
				    <button  id="Download" 
					value="<?php echo $_SESSION["ClientPath"].'Reports/DocHeadonReportDownload.php'?>"
								 class="btn btn-success" >Download Excel</button>
				 
					
					
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

        

		<?php include ($IncludesPath."vendor-scripts.php");
      
include ($IncludesPath."NewScripts.php");
;?>
      
    </body>




<Script>

$('document').ready(function(){
	
	getPiList();
    

    });    
	
	$(document).on('click','#PoDetails',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	  var PoID =$(this).data('id');
	  
	 $('#Update').val(PoID);
	  
	
	//alert(PoNo);
	
		 $.ajax({
		   url:'Ajax/AjaxPopUpPoDetails.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			 
			 $('.modal-title').text('PO Status Details - '+PoNo);
			 $('#Update').text('Update Status');
			$("#PopUpTable").html(data);
		/*$('#PopUpTable').DataTable({
		"bPaginate": false,
		autoWidth: true,
       
    });*/
			$('#myModal').modal('show');
			
		
	
			//$('#myModal').modal('show');
					}
			});
			
    	});	
	
	$(document).on('click','#Update',function(e){
	 
	 var PoID =$('#Update').data('id');
	 var PoID =$('#Update').val();
	
	var Url='CreateShipmentStatus.php?PoID='+ PoID;
		//window.open(Url,"_self");
		window.open(Url,"_blank");
 });
 
		
	$(document).on('click','#LotList',function(e){	



		var Year=$('#YEAR').val();
		
		var Link=$(this).val();
		var Exporter=$('#NAME_OF_PROCESSOR').val();
	//	alert(Link);


	//var dynamic_link=Link+'?Year='+Year+'&Exporter='+Exporter;
	var dynamic_link=Link+'?Exporter='+Exporter;
	window.open(dynamic_link);

	});
	
	$(document).on('click','#Download',function(e){	


	var Year=$('#YEAR').val();
		
		var Link=$(this).val();
		var Exporter=$('#NAME_OF_PROCESSOR').val();
	//	alert(Link);


	//var dynamic_link=Link+'?Year='+Year+'&Exporter='+Exporter;
	var dynamic_link=Link+'?Exporter='+Exporter;
	window.open(dynamic_link);

	});


</script>



<script>

		
$(document).on('click','.update_avalmcs',function(e){
	e.preventDefault();
	var PoID=$(this).val();
	var GradeId=$(this).attr('id');
		var AvalMcs='AvalMcs'+GradeId;

		var AvalMcs=$('#'+AvalMcs).val();
		if(GradeId==''){
			alert("No data available");
			
			return;
			
		}
		if(AvalMcs==''){
			alert("Please enter mcs");
			
			return;
			
		}
//alert(PoID);
//alert(AvalMcs);
	
	
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjUpdateAvalMcs.php',
    data: {'AvalMcs':AvalMcs,'Type':'UpdateMcs','GradeID':GradeId,'PoID':PoID},
	
	//dataType: 'json',
    
    success: function(response){
		var data = JSON.parse(response);
			if(data['sts'] == 'failed'){
			alert(data['msg']);
			}else{
				//$('#myModal').modal('hide');
			alert(data['msg']);
							//location.reload();

				
			}
	 }
  });
  
});


/*$(document).on('click','#BtnAddMcs',function(e){			
			e.preventDefault();

			var POGradeId=$(this).val();
			/*var AvalMcs='#AvalMcs'+POID;
			
			
	  
	  var AvalMcs=$(AvalMcs+"").val();
	  			var AvalMcs=$('#AvalMcs').val();
	  			var MaterialStatus=$('#MaterialStatus').val();
	  			var MailSentDate=$('#MailSentDate').val();
	  			var ApprovalDate=$('#ApprovalDate').val();
	  			var OrderedDate=$('#OrderedDate').val();
	  			var ReceivedDate=$('#ReceivedDate').val();

	
var r = confirm("Are You Sure Do You Want To Submit?");
  if (r == true) {
$.ajax({
    type: 'POST',
    url: 'Ajax/AjAddPackingData.php',
    data: {'AvalMcs':AvalMcs,'MaterialStatus':MaterialStatus,'MailSentDate':MailSentDate,'ApprovalDate':ApprovalDate,
	'OrderedDate':OrderedDate,'ReceivedDate':ReceivedDate,'Type':'AddPackingData','POGradeId':POGradeId},
    //dataType: 'json',
    success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
						getPiList();

			}else{
				$('#myModal').modal('hide');
			//alert(data['msg']);
							//location.reload();
							
			getPiList();

				
			}
	 }
  });
}else{}	

		
    	});	*/
  
 
  
  
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
		  url:'Ajax/AjHeadonData.php',
		  type:'POST',
		    //data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'PRODUCTIONDATALIST'},
		    data:{'EXPORTER':EXPORTER,'TYPE':'PRODUCTIONDATALIST'},
		  success:function(data){
			 // $("#PIList").html(data);
			  		showData(data);

			
			}
			});	
}
</script>





<script>


function showData(data){
			console.log(data);
			$("#PIList").html(data);	
			
	
 var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
         //scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : false,
		 "orderable": false,
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

  