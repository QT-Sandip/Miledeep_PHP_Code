<style>
label {
    font-size: 15px;
    color: black;
}

</style>

<style>
th {
    font-size: 12px;
}


.table tr:hover {
  background-color: #343a40 !important;
  color: #fff !important;
}


.table td, .table th {
	 font-size: 12px;
    padding: 7px !important;
    vertical-align: top;
    border-top: 1px solid #eff2f7;
}

.customWrap {
	white-space : nowrap !important;
}

		.warning-row {
            background-color: #f7ff84 !important;
            color: #111;
            font-weight: 500 !important;
        }
        
        .success-row {
            background-color: #fff !important;
        }
        
        .deadline-row {
            background-color: #df411d !important;
            color: #ffffff !important;
            font-weight: 500 !important;
        }

</style>

<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$AccessType=$_SESSION["AccessType"];

function checkEmpty($Value){

	
		if($Value==''){
		$Value='--';	
		}
		
		return $Value;
	}




if($_GET['chbkExp'] == ''){
	$chbkExp='true';
}else{
	$chbkExp=$_GET['chbkExp'];
}


if($_GET['chbkShip'] == ''){
	$chbkShip='true';
}else{
	$chbkShip=$_GET['chbkShip'];
}

//Start Year From Config
//End Year If Current Year+1

$Year=$_GET['Year'];
$CurrentMonth=date('m');


if($Year ==''){
$Currentyear=date('Y');
}else{
$Currentyear=$Year;
}	

if($CurrentMonth > 3 &&  $Year==''){	
$FinClsYear=$Currentyear;	
}else if($Year!=''){
$FinClsYear=$Year;		
}else{
$FinClsYear=$Currentyear-1;
}


if($CurrentMonth > 3){	
$RunningFinStartYear=date('Y');	
}else {
$RunningFinStartYear=(date('Y')-1);		
}

 
$DateStart=$FinClsYear.'-04-01';
$DateEnd=($FinClsYear+1).'-03-31';

$PastYearStart=($FinClsYear-1).'-04-01';
$PastYearEnd=($FinClsYear).'-03-31';
$PastNextYear=$FinClsYear ."-03-31";

$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);

 $DateFilter="BETWEEN '$DateStart' and '$DateEnd'";
 $PastDateFilter="BETWEEN '$PastYearStart' and '$PastYearEnd'";


$CurrentFincialYear=$DateStart;



 



$SqlBuyerData="SELECT ConID,PoID, MAX(PoDate)as LastPoDate, ConsigneeName, count(*) as TotalPo,
(SELECT sum(Mcs*TotalSlabs*SlabWeight*Mfactor) from ScrmPoGradeData as PGD 
 LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID WHERE PGD.InvId=SPD.PoID ) as Kgs,
(SELECT sum(Mcs*TotalSlabs*SlabWeight*Price) from ScrmPoGradeData as PGD 
 LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID WHERE PGD.InvId=SPD.PoID ) as Price 
FROM `ScrmPoData` as SPD
LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID   
 WHERE SPD.PoDate  $DateFilter GROUP by ConID order by TotalPo Desc";
$BuyerDataExec=$iconn->query($SqlBuyerData);



$DelayedShimpentsSql="SELECT PoNo,PoDate,ShipByDate,ShippedOn,
DATEDIFF(SCI.TransportedDate,SPD.ShipByDate) as DelayedDays from ScrmShipmentDetailsBreif as SCI
LEFT JOIN ScrmPoData as SPD on SCI.PiID=SPD.PoID
WHERE  DATE_SUB(SCI.ShippedOn, INTERVAL 10 DAY) > SPD.ShipByDate  and ShippedOn!='' ORDER by ShipByDate ASC";
$DelayedShimpentsExec=$iconn->query($DelayedShimpentsSql);
$TotalDelayedShipments=mysqli_num_rows($DelayedShimpentsExec);  


 $SqlExporter="SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
$StmtExporter=$iconn->query($SqlExporter);


$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);



?>

<!doctype html>
<html lang="en">

    <head>
       <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- jquery.vectormap css -->
        <link href="<?php echo $BaseDist ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />  
		<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
      
<?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark" >
		<?php include "PopUpTable.php"; ?>
<?php include ($IncludesPath."PreLoader.php"); ?>
        <!-- class="sidebar-enable vertical-collpsed" Begin page -->
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

                        <?php //include ($IncludesPath."page-title.php");?>
					

						 
						

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										
	  
				  	
      <div class="row">
    			


					
					
					
				  <div class="form-group col-lg-3">
                	  <label>Select Year: </label>
						<select class="form-control select2" name="Year" id="Year">
                         
                           <?php
							 						
                            for($Startyear; $Startyear < $endYear; $Startyear++) { 
						
									$year=($Startyear).'-'.($Startyear+1); ?>
                                                            
                        <option  
						
						<?php echo $Startyear; if($Startyear == $FinClsYear){ ?> selected="selected" 
						<?php   }   	?>			
						
						
						
						
				   
				   value="<?php echo $Startyear ?>"><?php echo $year ?></option>
							
                                                         
							<?php }   ?>
                   <select>
				  </div>
				  
				  <div class="col-lg-3">
                    <label>Select Exporter: </label>
				
				<select class="form-control select2" name="NAME_OF_PROCESSOR" id="NAME_OF_PROCESSOR">
			 

				   <?php while($row_add=$StmtExporter->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'];?></OPTION>
				
				<?php } ?>
				<option value=" " >All</option>
				<select> 

				
					</div>
					
					
				
				  
				  
				  <div class="col-lg-3">
                    <label>Select Consignee: </label>
				
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
				  
				 
				  
				  
				 <!-- <div class="col-lg-3 ">
                    <label>Select Buyer: </label>
				
				<select class="form-control select2 " name="Buyer" id="Buyer">
			 <option value=" " >All</option>

				   <?php while($RowConsignee=$StmtConsignee->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowConsignee['ConID'];?>"><?php echo $RowConsignee['ConsigneeName'].' - '.$RowConsignee['ConsigneeCountry'];?></OPTION>
				
				<?php } ?>
				<select> 
				</div>
					
			<div class="col-lg-3">
                    <label>Select Country: </label>
				
				<select class=" select2" name="Country" id="Country">
			 <option value=" " >All</option>

				   <?php while($RowCountries=$StmtCountries->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowCountries['id'];?>"><?php echo $RowCountries['name'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div> -->
				  </div> 
				  
				  
                <div align="CENTER" class="col-lg-12">
                                                     
                <button id="list" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button> 
					<?php if($AccessType<3){ ?>
				 <a target="_blank" href="PIGeneration.php" class="btn btn-primary waves-effect waves-light">
				<i class="fa fa-plus"></i> New PI</a>
					<?php } ?>
				
				</div> 
          

				 
											    <!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>--> 
	
									 
										</div>
                        <!-- end row -->
									</div>
									</div>
									
						
						
                            <div class="col-xl-8">
                                <div class="row">
								
								<!--<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Opening Balance</p>
                                                        <h4 class="mb-0"><?php echo $OpeningBalance ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div> -->
									

                                   
									
									<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Pending Contracts</p>
                                                        <button  class="mb-0 text-primary" id="TileID1"><?php echo $OrderOnHand ?></button>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-stack-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>

                                           
                                        </div>
                                    </div>
									
										<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">On Water</p>
                                                        <h4 class="mb-0" id="TileID2"><?php echo $OnSea ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
									
									    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Delivered</p>
                                                        <h4 class="mb-0" id="TileID3"><?php echo $Delivered ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>                                 
                                   
									

									
									
                                    
                                    
                                </div>
								<div class="row">
								
								<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Delivered</p>
                                                        <h4 class="mb-0"id="TileID4" ><?php echo $Delivered ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
								
								 <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Delivered</p>
                                                        <h4 class="mb-0" id="TileID5"><?php echo $Delivered ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
								
								<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">TransPort</p>
                                                        <h4 class="mb-0" id="TileID6"><?php echo $InTransport ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
									
								
									
								
								

                                    
                                </div>
                                <!-- end row -->

                                <div class="card">
								
								
								
									<div class="card-body">
									<h4 class="card-title mb-4">Sales vs Shipments</h4>
                                    <div>
                                    <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                    </div>
                                    </div>

                                    <div class="card-body border-top text-center">
                                        <div class="row">
                                           
										   
										   
											
											
											
											
											
                                           

                                        </div>
                                    </div>
                                    </div>
								
								<!--/****************************/  -->
								 <div class="card">
                                    <div class="card-body">
									<h4 class="card-title mb-4">Export Qty vs To Ship Qty</h4>
                                    <div>
                                    <div id="shipment-chart" class="apex-charts" dir="ltr"></div>
                                    </div>
                                    </div> 
									
									

                                    <div class="card-body border-top text-center">
                                        <div class="row">
                                           

                                            <div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
													<input  type = "checkbox" id="chbkExp" >
													Export Qty- <?php echo $TotalExportKgs ?> Kgs</p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"></h5>
                                                        <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div>
											
											<div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
													<input  type = "checkbox" id="chbkShip">

													To Ship Qty 
													- <?php echo
													$TotalShippedKgs ?> Kgs</p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"></h5>
                                                        <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div>
											
													
                                           

                                        </div>
                                    </div>
                                </div>
                            </div>
							
							
							

                            <div class="col-xl-4">
							
							 
								<div class="card">
                                    <div class="card-body">
                                       
                                        <h4 class="card-title mb-4">Pending Order
										<?php echo $Fi='('. $FinClsYear . '-' .($FinClsYear+1) .')'; ?></h4>

                                     <!--   <div id="donut-chart" class="apex-charts"></div>-->
									 <div id="pendingOrders" style="height: 250px; width: 100%;"></div>

                                        <div class="row">
										<div class="col-4">
                                                
                                            </div>
										 
                                            <?php foreach($AryShiftDtls as $ShiftRow){ ?>
												<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													class="mdi mdi-circle font-size-10 mr-1" 
													style="color: <?php echo $ShiftRow['Color'] ?>;">
													</i> <?php echo $ShiftRow['Name'] ?></p>
                                                    <h5><?php echo $ShiftRow['Total'] ?></h5>
                                                </div>
                                            </div>
										<?php	} ?>
											
											
                                           
                                        </div>
                                    </div>
                                </div>
								
								
								
                                <div class="card">
                                    <div class="card-body">
                                       
                                        <h4 class="card-title mb-4">Shipments
										<?php echo $Fi='('. $FinClsYear . '-' .($FinClsYear+1) .')'; ?></h4>

                                        <div id="donut-chart-shipments" class="apex-charts"></div>

                                        <div class="row">
										<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													
													>
													</i>Total</p>
                                                    <h5><?php echo $TotalPos?></h5>
                                                </div>
                                            </div>
										 
                                            <?php foreach($AryShiftDtls as $ShiftRow){ ?>
												<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													class="mdi mdi-circle font-size-10 mr-1" 
													style="color: <?php echo $ShiftRow['Color'] ?>;">
													</i> <?php echo $ShiftRow['Name'] ?></p>
                                                    <h5><?php echo $ShiftRow['Total'] ?></h5>
                                                </div>
                                            </div>
										<?php	} ?>
											
											
                                           
                                        </div>
                                    </div>
                                </div>
							
                                
								
								
		


                               
								
								                                <div class="card">
                                    <div class="card-body">
                                       
                                        <h4 class="card-title mb-4">Port - Containers</h4>
										<div id="portChart" style="height: 250px; width: 100%;"></div>

                                        <!--<div id="donut-chart-port" class="apex-charts"></div>-->

                                        
                                    </div>
                                </div>
								
								
                            </div>
                        </div>
                        <!-- end row -->
						
						<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Country Wise Details</h4>
                                       
                                        
                                    <div id="countryChart" class="apex-charts" dir="ltr"></div>
                                    </div>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>

                         

                        
                        <!-- end row -->

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <?php
				include ($IncludesPath."footer.php");
				
				// echo file_get_contents($BaseDist."partials/footer.php"); ?>
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->
<?php
        
	
		include ($IncludesPath."vendor-scripts.php");
		include ($IncludesPath."NewScripts.php");
		
		
		?>

	
  <!-- <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script> -->
  <script src="https://uiwrapper.silverlights.in/CanvasJs/canvasjs.min.js"></script> 
	<script>
$(document).ready( function () {
	getList();
    // $('#example1').DataTable();
    var table = $('#key-datatable').DataTable( {
        search: {
            return: true
        },
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
  sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : true 
        /*"columnDefs": [
            { "width": "20%", "targets": 0 }
          ]*/
    } );
} );
</script>	
<script>


 $(document).on('click','#PoDetails',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	  var PoID =$(this).data('id');
	  
	 $('#Update').val(PoID);
	 
	 showPoPopUp(PoNo,PoID);
	  
	

			
    	});
		
function showPoPopUp(PoNo,PoID){
	
			 $.ajax({
		   url:'Ajax/AjaxPopUpPoDetails.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			//  $("#Update").hide();

			 $('.modal-title').text('PO Status Details - '+PoNo);  
			
showData(data);
			$('#myModal').modal('show');
					}
			});

}	

function showData(data){
			
		$("#PopUpTable").html(data);	

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
		 order: [[1, 'desc']],
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );

			

}


function getList(){
	
		var ConID =$('#Year').val(); 

		
		$.ajax({
		   url:'Ajax/AjaxDashboard.php',
		  type:'POST',
		  dataType: 'json',
		  data:{'ConID':ConID },
		  success:function(data){
			var AryData =  JSON.parse(JSON.stringify(data));
			$('#TileID1').text('22');
			$('#TileID2').text('22');
			$('#TileID3').text('22');
			$('#TileID4').text('22');
			$('#TileID5').text('22');
			$('#TileID6').text('22');
			 renderShipmentsGraph(AryData.ArySaleNos);
			 renderExpSaleKgsGraph(AryData.ArySaleQty);
			 renderPortGraph(AryData.AryPortDtls);
			 renderPoGraph(AryData.AryPoDtls);
			 renderPlantShipmentGraph(AryData.AryPoDtls);
			 renderCountryGraph(AryData.AryCountryPoDtls);
		  }
			});
}


$(document).on('click','#TileID1',function(e){
		getBuyerWiseData();
 });
 
 $(document).on('click','#list',function(e){
		getList();
 });
 
function renderCountryGraph(ShipmentData){
	


var AryOrders=ShipmentData.Orders;
var AryXlables=ShipmentData.CountryName;



var SalesOptions={series:
[

//{name:"Total Kgs X 1000",type:"line",data:AryKgs},
{name:"Orders",type:"column",data:AryOrders}

], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1},
events: {
      dataPointSelection: function(event, chartContext, config) {
        getBuyerWiseData();
      }
    }},

plotOptions:{bar:{horizontal:!1,columnWidth:"10%"}},
dataLabels:{enabled:!1},legend:{show:1},
//colors:["#eeb902","#666","#ff0000"],
colors:["#666"],
labels: AryXlables};



countryChart=new ApexCharts(document.querySelector("#countryChart"),SalesOptions);
countryChart.render();
	 
 }
 
 
 function renderShipmentsGraph(ShipmentData){

var AryShipMents=ShipmentData.Shipments;
var AryOrders=ShipmentData.Orders;
var AryXlables=ShipmentData.MonthName;



var SalesOptions={series:
[

//{name:"Total Kgs X 1000",type:"line",data:AryKgs},
{name:"Orders",type:"column",data:AryOrders},
{name:"Shipments",type:"column",data:AryShipMents}

], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1}},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:1},
//colors:["#eeb902","#666","#ff0000"],
colors:["#666","#ff0000"],
labels: AryXlables};

chart=new ApexCharts(document.querySelector("#line-column-chart"),SalesOptions);
chart.render();

	 
 }
 
 
function renderExpSaleKgsGraph(data){

var ShipmentData =  JSON.parse(JSON.stringify(data));

 AryExportKgs=ShipmentData.ExportKgs;
 AryShippedKgs=ShipmentData.ShippedKgs;
 AryShipmentXlables=ShipmentData.Grade;

/*
var AryExportKgs=<?php echo json_encode($ExportKgs,true); ?>;
var AryShippedKgs=<?php echo json_encode($ShippedKgs,true); ?>;
var AryShipmentXlables=<?php echo json_encode($Grade,true); ?>;
*/

var Series=[
{name:"Export in Kgs",type:"column",data:AryExportKgs} ,	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
]

var shipmentOptions={
	series:Series, 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1},
events: {
      dataPointSelection: function(event, chartContext, config) {
        getBuyerWiseData();
      }
    }

},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
colors:["#5664d2","#ff0000"],
//colors:["#ff0000"],
labels: AryShipmentXlables

};

chartShipment=new ApexCharts(document.querySelector("#shipment-chart"),shipmentOptions);

chartShipment.render();


	 
 }
 
 
function renderPoGraph(data){

/*	
var ArySeries=data.PoSeries;
var AryLables=data.PoLables;
var AryColors=data.PoColors;

options={series : ArySeries,chart:{height:230,type:"donut",events: {
      dataPointSelection: function(event, chartContext, config) {
          console.log(config.w.config.series[config.dataPointIndex])
  console.log(config.w.config.labels[config.dataPointIndex])
      }
    }},
labels : AryLables,
plotOptions:{pie:{donut:{size:"75%"}}},
dataLabels:{enabled:1},legend:{show:!1},

colors : AryColors};

(chart=new ApexCharts(document.querySelector("#donut-chart"),options)).render();
*/



var dataPoints=data.dataPoints;

console.log(dataPoints);

	  "use strict";
var chart = new CanvasJS.Chart("pendingOrders", {
	animationEnabled: true,
	
	data: [{
		type: "pie",
		click: onClick,
		yValueFormatString: "#,##0.\"\"",
		indexLabel: "{x} ({y})",
		dataPoints: dataPoints
	}]
});
chart.render();
function onClick(e){
		//	alert(  e.dataPoint.PortID+ ", dataPoint { x:" + e.dataPoint.x + ", y: "+ e.dataPoint.y + " }" ); 
getBuyerWiseData();		
	}


}
function renderPlantShipmentGraph(data){
	
var ArySeries=data.PoSeries;
var AryLables=data.PoLables;
var AryColors=data.PoColors;

options={series : ArySeries,chart:{height:230,type:"donut"},
labels : AryLables,
plotOptions:{pie:{donut:{size:"75%"}}},
dataLabels:{enabled:!1},legend:{show:!1},
colors : AryColors};
chartPort=new ApexCharts(document.querySelector("#donut-chart-shipments"),options);

chartPort.render();



}



function renderPortGraph(data){
var data =  JSON.parse(JSON.stringify(data));

/*var AryPortSeries=data.PortSeries;
var AryPortLables=data.PortLables;
var AryPortColors=data.PortColors;

Portoptions={series : AryPortSeries,chart:{height:230,type:"donut"},
labels : AryPortLables,
plotOptions:{pie:{donut:{size:"75%"}}},
dataLabels:{enabled:1},legend:{show:1},
colors : AryPortColors};

(chart=new ApexCharts(document.querySelector("#donut-chart-port"),Portoptions)).render();
*/

var dataPoints=data.dataPoints;

console.log(dataPoints);

	  "use strict";
var chart = new CanvasJS.Chart("portChart", {
	animationEnabled: true,
	
	data: [{
		type: "pie",
		click: onClick,
		yValueFormatString: "#,##0.\"\"",
		indexLabel: "{x} ({y})",
		dataPoints: dataPoints
	}]
});
chart.render();
function onClick(e){
	//	alert(  e.dataPoint.PortID+ ", dataPoint { x:" + e.dataPoint.x + ", y: "+ e.dataPoint.y + " }" ); 
getBuyerWiseData();		
	}



}	
 
 $(document).on('click','#chbkExp',function(e){
	var chbkExp = $("#chbkExp").is(':checked');
	var chbkShip = $("#chbkShip").is(':checked');
	var SelectedDate='<?php echo $PresentYear ?>';
/*
	 var Url='<?php echo $CurrentUrl ?>?Year='+SelectedDate+'&chbkExp='+chbkExp+'&chbkShip='+chbkShip;
		window.open(Url,"_self");
		*/
		
		updateGraph(chbkExp,chbkShip);
 }); 
 
 $(document).on('click','#chbkShip',function(e){
	var chbkExp = $("#chbkExp").is(':checked');
	var chbkShip = $("#chbkShip").is(':checked');
	var SelectedDate='<?php echo $PresentYear ?>';
/*
	 var Url='<?php echo $CurrentUrl ?>?Year='+SelectedDate+'&chbkExp='+chbkExp+'&chbkShip='+chbkShip;
		window.open(Url,"_self");
		*/
		
		updateGraph(chbkExp,chbkShip);
 });

 $(document).on('click','#Update',function(e){
	 
	 var PoID =$('#Update').data('id');
	 var PoID =$('#Update').val();
	
	var Url='CreateShipmentStatus.php?PoID='+ PoID;
		window.open(Url,"_blank");
 });
 
function getBuyerWiseData(){
	 var ConID=$('#ConID').val();
	 var ConID=2;
	
	
	
		 $.ajax({
		   url:'Ajax/AjaxBuyerDetails.php',
		  type:'POST',
		  data:{'ConID':ConID },
		  success:function(data){
			 
			 //$('.modal-title').text('Do You Want To Shipped This Po '+INT_LOT_NUM+' ?');
			$("#PopUpTable").html(data);
            // $('#PopUpTable').DataTable({
            //     "bPaginate": false,
            //     autoWidth: true,
            //     scrollY: 600,
            //     scrollX: true,
            //     scroller: true
            // });
			$('#myModal').modal('show');
			
		
	
			//$('#myModal').modal('show');
					}
			});
			
 }
 
 $(document).on('click','#BuyerDetails',function(e){
	 
	 getBuyerWiseData();
    	});	



</script>
<script>


function updateGraph(chbkExp,chbkShip){
	
	
//var AryShipmentXlables=<?php echo json_encode($Grade,true); ?>;

if(chbkExp==true && chbkShip ==true){	
var Series=[
{name:"Export in Kgs",type:"column",data:AryExportKgs} ,	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
];	
var Colors={colors:["#5664d2","#ff0000"]};
}else if(chbkExp){	
var Series=[{name:"Export in Kgs",type:"column",data:AryExportKgs}];
var Colors={colors:["#5664d2"]};
}else if(chbkShip){
var Series=[{name:"Shipment Kgs",type:"column",data:AryShippedKgs}];
var Colors={colors:["#ff0000"]};
}else{	
var Series=[
{name:"Export in Kgs",type:"column",data:AryExportKgs} ,	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
];	
var Colors={colors:["#5664d2","#ff0000"]};	
}	
	
chartShipment.updateSeries(Series);	
chartShipment.updateOptions(Colors);	

/*chartShipment.updateSeries(
[	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
]
)	
*/

}



</script>

    </body>
</html>
