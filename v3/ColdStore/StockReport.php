<?php 
$PageTitle="Stock Report";
$PageTitleFolder="ColdStore";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$UnitID=$_SESSION['UnitID'];
$AccessType=$_SESSION['AccessType']; 

if($AccessType!=1){
	//$SQry="where ProcessingStationID=$UnitID";
	
} 

$SqlColdstore="SELECT * FROM `CsStores` $SQry"; 
$StmtColdstore=$iconn->query($SqlColdstore); 

$SqlTenant="SELECT * FROM `CsTenant`"; 
$StmtTenant=$iconn->query($SqlTenant);

//echo $SqlPoList="SELECT * FROM `ScrmPoData`  where PoStatus=0 ORDER BY ShipmentDate DESC";exit;
 $SqlPoList="SELECT * FROM `ScrmPoData` as CPD  
 left join ScrmShipmentDetailsBreif as SDB on CPD.PoID=SDB.PiID

  WHERE  (TransportedDate='' or TransportedDate is null) order by PoID DESC "; //ProductionStartedDate!=''and
$StmtPoList=$iconn->query($SqlPoList);

 $SqlOptions="SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='CS' ORDER BY OptID asc"; 
$StmtOptions=$iconn->query($SqlOptions);
WHILE($RowOptions=$StmtOptions->FETCH_ARRAY()){
	$AryOptions[]=$RowOptions;
}

$SqlVarietyList="SELECT * FROM `ScrmPIVarietyList` ORDER by ABS(ShortName)";
$StmtVarietyList=$iconn->query($SqlVarietyList); 

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
                                              <!-- <div class="form-group col-lg-2" >              
                  <label for="exampleInputEmail1">Unit</label>
				
               <select class="form-control select2" name="Station" id="Station">
			     <option value="BOTH">ALL</option>
			   <?php /*foreach($AryOptions as $value){
					if($value['OptionType']=='CS_STATIONS'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['OptID'];
			   ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}*/?>
					
				<select>
                </div>-->
				
				<div class="form-group col-sm-3" >
				  <label for="exampleInputEmail1">ColdStores</label>
                 <select required class="form-control select2" name="ColdstoreID" id="ColdstoreID">
					<?php while ($RowColdstore=$StmtColdstore->fetch_array()){ ?>
				     <option value="<?php echo $RowColdstore['StoreID'] ?>">
					 <?php echo $RowColdstore['StoreName']?></option>
				   
				 <?php  }?>	 
                  				 
				</select>
            </div>
			
			<div class="form-group col-sm-3" >
				  <label for="exampleInputEmail1">Stock belongs to</label>
                 <select required class="form-control select2" name="TenantID" id="TenantID">
				 <option value="BOTH">All </option>
					<?php while ($RowTenant=$StmtTenant->fetch_array()){ ?>
				     <option value="<?php echo $RowTenant['TenantID'] ?>">
					 <?php echo $RowTenant['TenantName']?></option>
				   
				 <?php  }?>	 
				 
                  				 
				</select>
            </div>
				
				<div class="form-group col-sm-3" >
				  
                  <label for="exampleInputEmail1">Variety</label>
				 <select required class="form-control select2" name="VarietyID" id="VarietyID">
				 				 <!-- <option value="">Select Variety</option> -->
					 <option value="BOTH">ALL</option>

					<?php while ($RowVData=$StmtVarietyList->fetch_array()){ ?>
				     <option value="<?php echo $RowVData['VarietyID'] ?>">
					 <?php echo $RowVData['ShortName'].' - '.$RowVData['TypeProduct'] ?></option>
				   
				 <?php  }?>	  
				</select>
            </div>
					
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputPassword1">Packing Type</label>
				 <select class="form-control select2" name="PackData" id="PackData">	
				 <option value="BOTH">ALL</option>
					 <?php foreach($AryOptions as $value){
					if($value['OptionType']=='PACKING_TYPE'){
						$OPTIONS= $value['Name'];
						//$VALUE= $value['OptID']; 
						?>
			 
					<option value="<?php echo $OPTIONS ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
		
				<select>
                </div>
				
				 <div class="form-group col-lg-3" >
				  
                  <label for="exampleInputEmail1">Category</label>
                 <select class="form-control select2" name="Category" id="Category">
				 <option value="BOTH" >ALL</option>
					 <?php foreach($AryOptions as $value){
					if($value['OptionType']=='PRODUCT_CATEGORY'){
						$OPTIONS= $value['Name'];
						//$VALUE= $value['OptID']; 
						?>
			 
					<option value="<?php echo $OPTIONS ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
                </div>

				 
				 <div class="form-group col-lg-3" >
                  <label for="exampleInputPassword1">Group</label>
				 <select class="form-control select2" name="Group" id="Group">				  
				  <option value='BOTH' >All</option>
				  
					 <?php foreach($AryOptions as $value){
					if($value['OptionType']=='PRODUCT_GROUP'){
						$OPTIONS= $value['Name'];
						//$VALUE= $value['OptID']; 
						?>
			 
					<option value="<?php echo $OPTIONS ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
					</select>
				
				</div>
				
				
				  <div class="form-group col-lg-3" >              
					 
					 
                  <label for="exampleInputEmail1">Freezing Type</label>
				  
               <select class="form-control select2" name="FreezingType" id="FreezingType">
			   <option value="BOTH" >ALL</option>
					 <?php foreach($AryOptions as $value){
					if($value['OptionType']=='FREEZING'){
						$OPTIONS= $value['Name'];
						//$VALUE= $value['OptID'];
						?>
			 
					<option value="<?php echo $OPTIONS ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
				<select>
                   </div>
				   
				   <div class="form-group col-sm-3" >              
					 
					 
                  <label for="exampleInputEmail1">Soaking</label>
				  
               <select class="form-control select2" name="Soaking" id="Soaking">
			   <option value="BOTH" >ALL</option>
					 <?php foreach($AryOptions as $value){
					if($value['OptionType']=='Soaking'){
						$OPTIONS= $value['Name'];
						//$VALUE= $value['VALUE']; 
						?>
			 
					<option value="<?php echo $OPTIONS ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
				<select>
                   </div>
				     <div class="form-group col-sm-3" >              
				<label for="exampleInputEmail1">Ab Status</label>
               <select class="form-control select2" name="AntiBiotic" id="AntiBiotic">
			     <option value="BOTH" >ALL</option>
					<?php foreach($AryOptions as $value){
					if($value['OptionType']=='AbStatus'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Name']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
					
		
				<select>
            </div>				

				
			 <div class="form-group col-lg-3" >              
					 
					 
                  <label for="exampleInputEmail1">PO Number</label>
				  
               <select class="form-control select2" name="PONmbr" id="PONmbr">
			   <option value="BOTH" >ALL</option>
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
						<div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="StockReport">
                                        

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


<script> 
  $("#PONmbr").select2();      
$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			var PackData=$('#PackData').val();
			var Group=$('#Group').val();
			var Category=$('#Category').val();
			var FreezingType=$('#FreezingType').val();
			var UNIT=$('#Station').val();
			var PONmbr=$('#PONmbr').val();
			var VarietyID=$('#VarietyID').val();
			var AntiBiotic=$('#AntiBiotic').val();
			var Soaking=$('#Soaking').val();
			var ColdstoreID=$('#ColdstoreID').val();
			var TenantID=$('#TenantID').val();
			//var INV_ID=<?php echo $INVOICE_ID ?>;
			
			$.ajax({ 
		  url:'Ajax/AjaxStockReport.php',
		  type:'POST',
		  data:{'Group':Group,'VarietyID':VarietyID,'AntiBiotic':AntiBiotic,'Category': Category, 'PackData' :PackData,'FreezingType':FreezingType,'UNIT':UNIT,'PONmbr':PONmbr,'Soaking':Soaking,
		  'TenantID' : TenantID,'ColdstoreID' : ColdstoreID},
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
			var PONmbr=$('#PONmbr').val();
			var AntiBiotic=$('#AntiBiotic').val();
			var Soaking=$('#Soaking').val();
			var VarietyID=$('#VarietyID').val();
			var ColdstoreID=$('#ColdstoreID').val();
			var TenantID=$('#TenantID').val();


			
window.open("Reports/CsStockReportPrint.php?Group=" + Group + "&Category=" + Category + "&PackData=" + PackData + "&FreezingType="+ FreezingType + "&UNIT=" + UNIT + "&PONmbr=" + PONmbr+ "&AntiBiotic=" + AntiBiotic+ "&Soaking=" + Soaking+ "&VarietyID=" + VarietyID+ "&ColdstoreID=" 
+ ColdstoreID + "&TenantID=" + TenantID
, "_blank");

		
    	});
	
//uma

		
		
</script>


<script>

function showData(data){
			
		$("#StockReport").html(data);	

var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
		  responsive: false,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
         scrollCollapse:false,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : false,
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



</body>
</html>