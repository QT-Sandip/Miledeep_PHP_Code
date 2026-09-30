<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");



 $SqlVaStations=" SELECT * FROM `PpcVAStations` WHERE UnitStatus=1";
$StmtVaStations=$iconn->query($SqlVaStations);

$SqlFinanciers=" SELECT * FROM `RmFinanciers` WHERE Status=1";
$StmtFinanciers=$iconn->query($SqlFinanciers);

$SqlContractor=" SELECT * FROM `PpcContractMaster` WHERE Status=1";
$StmtContractor=$iconn->query($SqlContractor);


extract($_POST);
extract($_GET);


$PageTitle="Daily Contractor Details";
$PageTitleFolder="PreProcessing";


 ?>



<!DOCTYPE html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

   <!-- <body data-sidebar="dark">-->
	<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">

	
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
                  <label for="exampleInputPassword1">Station<span style="color:red">*</span></label>
				  
				<select required class="form-control select2" name="StationID" id="StationID">
				  <option value=""> All</option>
				<?php while($RowStations=$StmtVaStations->fetch_array()) {	?>
				<option 
				<?php if($RowStations['StationID']==$RowRmLots["StationID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowStations['StationID'];?>">
				<?php echo $RowStations['UnitName'];?></option>
				<?php } ?>
				</select>
				 </div>
			   
			    <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1"> Type<span style="color:red">*</span></label>
				<select  required class="form-control select2" name="EntryType" id="EntryType">
				<option value=""> Select</option>
				 <option value="1">HL</option>
				<option value="2">Peeling</option>
				 </select>
				 </div>
			  
             <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Supplier Type</label>
				<select  class="form-control select2" name="Type" id="Type">
				 <option value=""> Select</option>
				 <option value="1">Contractor</option>
				 <option value="2">Mestri</option>
				 </select>
				 </div>
								 
				
				
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Contractor/Mestri </label>
				<select  class="form-control select2" name="ContractorID" id="ContractorID">
				<option value=""> Select</option>
				<?php while($RowContractor=$StmtContractor->fetch_array()) {	?>
				<option 
				<?php if($RowContractor['ContractorID']==$RowRmLots["StationID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowContractor['ContractorID'];?>">
				<?php echo $RowContractor['ContrctrName'];?></OPTION>
				<?php } ?>
				</select>
				  
			   </div>
			   
			  
			   
			 <!--  <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Batch<span style="color:red">*</span></label>
				<select required class="form-control select2" name="BatchID" id="BatchID">
				 
				</select>
				  
			   </div>-->
			   
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date</label>
                  <input required type="date" class="form-control" name='Date' id="Date"
				 max="<?php echo date("Y-m-d"); ?>" placeholder="Enter  Date" 
				 value="<?php echo date("Y-m-d");?>">
                
				</div>
			
				
				</div>
				
				<div align="center" class="col-lg-12">
				<button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> </button>
				<button  id="DailyDataList" 
				value="<?php echo $_SESSION["ClientPath"].'Reports/hl-pd-bill-print' ?>"
								 class="btn btn-success" >View</button>									
													</div>
       <!-- end col -->
</div> <!-- end row -->

</div> <!-- end card body-->
                                </div> <!-- end card -->
								
								
                            </div><!-- end col-->
<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="ShowDataTable">
                                      
                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
   
  </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>

  
       
    </body>
	
<script>

$('document').ready(function(){
		//getData();

		$(document).on('click','#list',function(e){
			e.preventDefault();

			getData();
    	});



    	
    });


</script>
	
	

<script>
$("#Type").change(function(e){
			e.preventDefault();	
			var Type=$('#Type').val(); 
			if(Type!=''){
			getContractors();
			//getData();
			}
		});	

$("#ContractorID").change(function(e){
			e.preventDefault();	
			var ContractorID=$('#ContractorID').val(); 
			if(ContractorID!=''){
			//getBatches();
			//getData();
			}
		});			
		
function getContractors(){
var Type=$('#Type').val(); 
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjSupplierDetails.php',
    data: {'Type' : Type},  
    success: function(response){
		 $("#ContractorID").html(response); 
	 }
  });	  
  }
  
  function getBatches(){
var ContractorID=$('#ContractorID').val(); 
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjBatchDetails.php',
    data: {'ContractorID' : ContractorID},  
    success: function(response){
		 $("#BatchID").html(response); 
	 }
  });	  
  }
$(document).on('click','#DailyDataList',function(e){	

var Date=$('#Date').val();
var ContractorID=$('#ContractorID').val();
var EntryType=$('#EntryType').val();
var StationID=$('#StationID').val();
var QueryParms='Month='+Date+'&Year='+Date+'&ContractorID='+ContractorID+'&EntryType='+EntryType+'&StationID='+StationID;
//var QueryParms=urlEncode64(QueryParm);
var Link=$(this).val();
//alert(QueryParms);
		/*var Year=$('#YEAR').val();
		
		var Exporter=$('#NAME_OF_PROCESSOR').val();*/
	//	alert(Link);


	var dynamic_link=Link+'?'+QueryParms;
	window.open(dynamic_link);

	});
</script>


<script>
			   
	
function getData(){
	
			var PsId=$('#StationID').val();
			var SupplierType=$('#Type').val();
			var ContractorID=$('#ContractorID').val();
			var EntryType=$('#EntryType').val();
			//var BatchID=$('#BatchID').val();
			var Date=$('#Date').val();
			
			var QueryParms='Month='+Date+'&Year='+Date+'&ContractorID='+ContractorID+'&EntryType='+EntryType+'&StationID='+PsId;

			
			if(PsId==''){
				alert("Please Select Station");
				return;
			}else if(EntryType==''){
				alert("Please Select  Type");
				return;
			}else if(SupplierType==''){
				alert("Please Supplier  Type");
				return;
			}
			$.ajax({
		  url:'Ajax/AjaxDailyHLContractorDetails.php',
		  type:'POST',
		 
		  data:{'SupplierType' : SupplierType,'QueryParms' : QueryParms,
		  'ContractorID': ContractorID,'PsId' : PsId,'Date': Date,'EntryType': EntryType},
		  success:function(data){
			 showData(data);

					}
			});
	
}

 </script>

</html>				

