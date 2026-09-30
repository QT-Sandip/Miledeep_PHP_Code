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
//include "../includes/config.php";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


function checkEmpty($Value){

	
		if($Value==''){
		$Value='--';	
		}
		
		return $Value;
	}



if($_GET['Month']!=''){
$PresentMonth=$_GET['Month'];
}else{
$PresentMonth=date('m');
}

if($_GET['Year']!=''){
$Year=$_GET['Year'];
}else{
$Year=date('Y');
}

if($_GET['PID']!=''){
$PID=$_GET['PID'];
$Qry= " and UnitID=$PID";
}else{
$Qry= "";
}



$CurrentFinYear=getCurrentFinancialYear();
$DateFilter=getDateFilter($Year,$PresentMonth); 

$AccessType=$_SESSION['AccessType']; 
$UnitID=$_SESSION['UnitID']; 

if($AccessType!=1){
	$SQry="where ProcessingStationID=$UnitID";
	$Qry= " and UnitID=$UnitID";
	//$processorQry= " and ProcessorID=$UnitID";
}

 $SqlStoreFacility="SELECT * FROM `RmProcessingStations` $SQry"; 

$StmtStoreFacility=$iconn->query($SqlStoreFacility);

$SqlActiveStockIds="SELECT GROUP_CONCAT(StockID) as ActiveStockID FROM `CsStockData` as CSD 
				LEFT Join CsBarCodes as CBC on CSD.BarCodeID=CBC.BarCodeID 
			   WHERE CBC.BarCodeStatus=1 
			   and (CSD.AvalMcs!=0 or CSD.LooseBags!=0) $Qry";
$StmtActiveStockIds=$iconn->query($SqlActiveStockIds);




$RowActiveStockID=$StmtActiveStockIds->fetch_array();
$ActiveStockID=$RowActiveStockID['ActiveStockID'];




 $SqlAttDetails="SELECT COUNT(*) as Total,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE AvailableDate!='' and  (TransportedDate='' or TransportedDate is null) ) as OrderOnHand, 
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE ProductionStartedDate!='' and (TransportedDate='' or TransportedDate is null) ) as RunningOrders,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE ReturnedDate!='' ) as Returns from ScrmShipmentDetailsBreif"; 

$StmtAttDetails=$iconn->query($SqlAttDetails);





$AttDataRow=$StmtAttDetails->fetch_array();

$Closed=$AttDataRow['Closed'];
$OnSea=$AttDataRow['OnSea'];
$InTransport=$AttDataRow['InTransport'];
$OrderOnHand=$AttDataRow['OrderOnHand'];
$RunningOrders=$AttDataRow['RunningOrders'];
$Returns=$AttDataRow['Returns'];


// u177334400_jme_live /@rm~u[Ir?Q9$
$AryPos=array();
 $SqlLate="SELECT SPD.*,ConsigneeName,SCL.name as CountryName,SPC.Name as PortOfDischarge,SDB.CInvNo,LotNo,
`ShippedOn`, `BLNumber`, `ETA`, `ProductionStartedDate`, `TransportedDate`,`ReturnedDate`, `ContainerNo`, `AvailableDate`,
 `ClosedDate`, `DeliveredDate`,COALESCE(DATEDIFF(SPD.ShipByDate,now())-1,0) as DaysRemaining,
 COALESCE(DATEDIFF(SPD.ShipByDate,SDB.TransportedDate),0) as DelayedDays
 from ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID= SCD.ConID
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
left join ScrmShipmentDetailsBreif as SDB on SPD.PoID=SDB.PiID
where (SDB.TransportedDate ='' or  SDB.TransportedDate is   null) 
$processorQry
order by DaysRemaining DESC";
$StmtLate=$iconn->query($SqlLate);

while($LateRow=$StmtLate->fetch_array()){
	
	if($RunningFinStartYear==$FinClsYear){
	$AryPos[]=$LateRow;
	}
	
} 


 
  $SqlSales="SELECT ProductionTypeID,ProductionDate,
sum((AvalMcs*TotalSlabs*SlabWeight*MFactor)+(LooseBags*SlabWeight*MFactor)) as AvalKgs,
(SELECT COALESCE(sum((OutMcs*TotalSlabs*SlabWeight*MFactor)+(OutLooseBags*SlabWeight*MFactor)),0) 
 from  CsStockOutDetails as CSOD_O  left join CsStockData as CSD_O on CSOD_O.StockID=CSD_O.StockID
left join AdminPackingStyles as APS on APS.PCKID=CSD_O.PackingStyleID WHERE  CSD_O.ProductionDate=CSD.ProductionDate ) as ProdOutKgs,
(SELECT COALESCE(sum(OutMcs*TotalSlabs*SlabWeight*MFactor),0) 
 from  CsStockOutDetails as CSOD_O  left join CsStockData as CSD_O on CSOD_O.StockID=CSD_O.StockID
left join AdminPackingStyles as APS on APS.PCKID=CSD_O.PackingStyleID WHERE  STR_TO_DATE(CSOD_O.PostedDate, '%Y-%m-%d')=CSD.ProductionDate ) as OutKgs
FROM `CsStockData` as CSD 
left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
WHERE  CSD.ProductionDate  $DateFilter $Qry
 group by ProductionDate"; 
 

$SalesExec=$iconn->query($SqlSales);


while($SalesRow=$SalesExec->fetch_array()){
	if($SalesRow['ProductionDate'] !=''){		
	$InvIds=round($SalesRow['AvalKgs']+$SalesRow['ProdOutKgs'],2);	
	$MonthName[]=getFormatedDate($SalesRow['ProductionDate']);
	$Orders[]=round($SalesRow['AvalKgs']+$SalesRow['ProdOutKgs'],2);	
	$Shipments[]=round($SalesRow['OutKgs'],2);
	}
}





$TotalOrders=array_sum($Orders);
$TotalShipments=array_sum($Shipments);
 

/*****************************************Block Wise Date ***************************/

  $SqlShiftDetails="SELECT COALESCE(BlockName,'OnFloor') as BlockName ,
sum(TotalSlabs*SlabWeight*AvalMcs*MFactor) as KGS,
sum(SlabWeight*MFactor*LooseBags) as LKGS,
COALESCE(StoreCapacity,'0') as StoreCapacity,
COALESCE(ColorCode,'#FF0000') as ColorCode FROM `CsStockData` as CSD 
left join CsBarCodes as CBC on CSD.BarCodeID=CBC.BarCodeID
left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
left join CsStoresRackData as CSRD on CSRD.BarCodeID=CBC.BarCodeID
left join CsStoresBlockData as CSBD on CSBD.BlockID=CSRD.BlockID
WHERE CSD.StockID in ($ActiveStockID) group by CSRD.BlockID";
//WHERE (CBC.BarCodeStatus=1) group by CSRD.BlockID";

//echo $SqlShiftDetails;
$StmtShiftDetails=$iconn->query($SqlShiftDetails);
while($AttShiftRow=$StmtShiftDetails->fetch_array()){
$AttShiftRow['Color']= $AttShiftRow['ColorCode'];
$AttShiftRow['Total']=round(($AttShiftRow['KGS']+$AttShiftRow['LKGS']+0)/1000,2);
$Series[]=$AttShiftRow['KGS']+$AttShiftRow['LKGS']+0;
$Lables[]=$AttShiftRow['BlockName'];
$Colors[]=$AttShiftRow['ColorCode'];
//$TotalPos=array_sum($Series);

$AryShiftDtls[]=$AttShiftRow;
	
}

$TotalStoreStock=round(array_sum($Series),2);
 
/*****************************************Block Wise Date ***************************/

/*****************************************Variety Wise Date ***************************/


  $SqlPresentStockVarityWise="SELECT ShortName,sum(AvalMcs) as MCS ,
(sum(AvalMcs * TotalSlabs * SlabWeight *MFactor)) as KGS,
(sum(LooseBags  * SlabWeight *MFactor)) as LKGS,
PackingStyleID from CsStockData as CPID 
left JOIN CsBarCodes as CBD on CBD.BarCodeID=CPID.BarCodeID
left JOIN AdminPackingStyles as CPS on CPID.PackingStyleID=CPS.PCKID 
left join ScrmPIVarietyList as VL on VL.VarietyID=CPID.VarietyID
WHERE CPID.StockID in ($ActiveStockID)   GROUP by ShortName"; 
//WHERE (CBD.BarCodeStatus=1)   GROUP by ShortName"; 

$StmtPresentStockVarityWise=$iconn->query($SqlPresentStockVarityWise);
$AryVarity=array();
$AryKgs=array();
$AryMcs=array();
while($RowPresentStock=$StmtPresentStockVarityWise->fetch_array()){
	$AryVarity[]=$RowPresentStock['ShortName'];
	$AryMcs[]=$RowPresentStock['MCS'];
	$TotalKgs=$RowPresentStock['KGS']+$RowPresentStock['LKGS'];
	 $AryKgs[]=$TotalKgs;
	
	
$data_d['value']= round($TotalKgs);
$data_d['color'] =sprintf("#%06x",rand(0,16777215));
$data_d['highlight'] ='#32CD32';
$data_d['label'] =$RowPresentStock['ShortName'];
$Cjs['label']=$RowPresentStock['ShortName'];
$Cjs['y']=round(($TotalKgs/1000),2);
$dounat_DATA[]=$data_d;
$dataPoints[]=$Cjs;
}

//$OverAllKgs=round(array_sum($AryKgs),2);
$OverAllKgs=round((array_sum($AryKgs)/1000),2);

/*****************************************Variety Wise Date ***************************/


/*****************************************Port Wise Date ***************************/


$SqlPorts="SELECT COALESCE(SC.name,'Na') as CountryName,
Max(SCI.PoDate) as LastShipment,
COALESCE(SPC.Name,'Na') as PortName,COUNT(*) as Total FROM `ScrmShipmentDetailsBreif` as  SSD LEFT join
ScrmPoData  as SCI on SCI.PoID=SSD.PiID
left join ScrmPortCities  as SPC on SCI.PortOfDischarge=SPC.PortId
left join ScrmCountriesList  as SC on SC.id=SPC.Country
WHERE SCI.PoDate $DateFilter
 GROUP by PortofDischarge ORDER by Total DESC;";
$StmtPorts=$iconn->query($SqlPorts);
//and SC.id=157

$PortsExec=$iconn->query($SqlPorts);
while($RowPorts=$PortsExec->fetch_array()){
$RowPorts['Color']= sprintf('#%06X', mt_rand(0, 0xFFFFFF));;
$PortSeries[]=$RowPorts['Total']+0;
$PortLables[]=$RowPorts['PortName'];
$PortColors[]=$RowPorts['Color'];

$AryPortDtls[]=$RowPorts;
	
}

/*****************************************Grade Wise***************************/


 $SqlPresentStockGradeWise="SELECT Grade as X_Axis,sum(AvalMcs) as MCS ,
(sum(AvalMcs * TotalSlabs * SlabWeight *MFactor)) as KGS,
(sum(LooseBags  * SlabWeight *MFactor)) as LKGS,
PackingStyleID from CsStockData as CPID 
left JOIN CsBarCodes as CBD on CBD.BarCodeID=CPID.BarCodeID
left JOIN AdminPackingStyles as CPS on CPID.PackingStyleID=CPS.PCKID 
left join ScrmPIVarietyList as VL on VL.VarietyID=CPID.VarietyID
WHERE CPID.StockID in ($ActiveStockID) and PackingTypeID='DUMMY' 
and (PoID=0 or PoID is null) group by Grade ORDER by Abs(Grade)"; 

$StmtPresentStockGradeWise=$iconn->query($SqlPresentStockGradeWise);
$AryVarity=array();
$AryKgs=array();
$AryMcs=array();

while($RowPresentStock=$StmtPresentStockGradeWise->fetch_array()){
	if(($RowPresentStock['KGS']+$RowPresentStock['LKGS'])>0){
	$AryGradeBar3[]=$RowPresentStock['X_Axis'];
	$AryMcsBar3[]=$RowPresentStock['MCS'];
	
	$TotalKgsBar3=$RowPresentStock['KGS']+$RowPresentStock['LKGS'];
	$AryKgsBar3[]=$TotalKgsBar3;
	//$AryMcsBar3[]=$TotalKgs;
	}
}

$TotalKgsBar3=round(array_sum($AryKgsBar3),2);
$TotalMcsBar3=round(array_sum($AryMcsBar3),2);
$TotalDummyKgs=$TotalKgsBar3;
$TotalDummyMcs=$TotalMcsBar3;


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

                        <?php //include ($IncludesPath."page-title.php");?>
						<?php include "PopUpTable.php"; 
						
						?>

						 
						 
				<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										<div class="row">
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                       <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
                                                            <option value="">All  Station</option>
														
															<?php while($DataRow=$StmtStoreFacility->fetch_array()){?>	
				 
												<option value="<?php echo $DataRow['ProcessingStationID'] ;?>"
												<?php if($DataRow['ProcessingStationID']==$PID){ echo 'selected="selected"'; }?>												>
												<?php  echo $DataRow['StationName']; ?></option>
												<?php    } ?>
												
                                                        </select>
													</div>
                                                </div>
												

												
												<div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Year" 
														id="Year">
                                                           <?php
														
														for($year; $Startyear <= $endYear; $Startyear++) { ?>
                                                            
                                                                 <option value="<?php echo $Startyear ?>"
																 <?php if($Startyear==$Year
																 ){ echo 'selected="selected"'; }?>>
																 <?php echo $Startyear ?></option>
                                                             
                                                            <?php  } 
                                                         
                                                            ?>
                                                        </select>
													</div>
                                                </div>
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
                                                          echo "<option value='$value'";
														    if($PresentMonth==$value){ echo 'selected="selected"'; }
														 echo ">$label</option>";
                                                         }
														 
														 ?>
                                                        </select>
													</div>
                                                </div>
												
												<div align="center" class="col-lg-3">
                                                     
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
									
						
						
                            <div class="col-xl-8">
                               <!-- <div class="row">
                                    <div class="col-md-4">
                                        <div class="card">
                                             <div class="card-body">
                                                <div class="media">
                                                   <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Order On Hand</p>
                                                        <h4 class="mb-0"><?php echo $OrderOnHand ?></h4>
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
                                                        <p class="text-truncate font-size-14 mb-2">Running Orders</p>
                                                        <h4 class="mb-0"><?php echo $RunningOrders ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                   <!-- <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Returns</p>
                                                        <h4 class="mb-0"><?php echo $Returns ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>-->
                                <!-- end row --> 

								
								<!--/****************************/  -->
								 <div class="card">
                                    <div class="card-body"> 
									<h4 class="card-title mb-4">Daily Inward Vs OutWard (Kgs)</h4>
                                    <div>
                                    <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                    </div>
                                    </div>
									
									

                                    <div class="card-body border-top text-center">
                                        <div class="row">
                                           

                                            <div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
													<input  type = "checkbox" id="chbkExp" <?php if($chbkExp !='false'){ ?>
																checked
																<?php } ?>>
													Inward- <?php echo $TotalOrders ?> Kgs</p>
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
													<input  type = "checkbox" id="chbkShip" 
													<?php if($chbkShip !='false'){ ?>
																checked
																<?php } ?>
														>

													OutWard 
													- <?php echo
													$TotalShipments ?> Kgs</p>
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
								
								
								<div class="card">
                                    <div class="card-body">
									<h4 class="card-title mb-4">Available Dummy Stock -<?php echo $TotalKgsBar3 ?> Kgs </h4>
                                    <div>
                                    <div id="production-chart" class="apex-charts" dir="ltr"></div>
                                    </div>
                                    </div> 
									
									

                                    <div class="card-body border-top text-center">
                                        <div class="row">
                                           

                                            
											
											<div class="col-sm-6">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
												

													<b>Dummy Stock Kgs </b>
													- <?php echo
													$TotalDummyKgs ?> Kgs</p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"></h5>
                                                       
                                                    </div>
                                                </div>
                                            </div>
											
											<div class="col-sm-6">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
													

													<b>Dummy Stock Mcs</b> - <?php echo $TotalDummyMcs ?> Mcs</p>
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
                                       
                                        <h4 class="card-title mb-4">Store Details -<?php echo $TotalStoreStock ?> Kgs</h4>

                                        <div id="donut-chart" class="apex-charts"></div>

                                        <div class="row">
										
                                            <?php foreach($AryShiftDtls as $ShiftRow){ ?>
												<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													class="mdi mdi-circle font-size-10 mr-1" 
													style="color: <?php echo $ShiftRow['Color'] ?>;">
													</i> <?php echo $ShiftRow['BlockName'] ?></p>
                                                    <h6><?php echo $ShiftRow['Total'] ?> T</h6>
                                                </div>
                                            </div>
										<?php	} ?>
											
											
                                           
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        

                                        <h4 class="card-title mb-4">Stock Details- <?php echo $OverAllKgs ?> T</h4>
                                        <div class="text-center">
                                            <div class="row">
                                                <div id="chartContainer" style="height: 250px; width: 100%;"></div>

                                                
                                                
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div> 
								
								 
								
								
								                           
								
								
                            </div>
                        </div>
                        <!-- end row -->

                   
                      <!--  <div class="row">
                            
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        
                                        <h4 class="card-title mb-4">Current Orders</h4>

                                        <div class="table-responsive ">
                                           
											<table id="key-datatable" data-paging="false"  
										class="table dt-responsive nowrap w-100  " >
				
                                                <thead class="thead-light">
                                                     
                                                        
														<th>Id</th>
														<th>Inv No</th>
                                                        <th>Po No</th>
                                                        <th>Po Date</th>
                                                        <th>Internal Lot Number</th>
                                                        <th>Consignee</th>
														<th>Ship By</th>
                                                        <th>Dest Port</th>
                                                        <th>Dest Country</th>
                                                        <th>Transport Date </th>
                                                        <th>Container No </th>
                                                        <th>Shipped On </th>
                                                        <th>BL No. </th>
                                                        <th>ETA</th>
                                                        <th>Days Rem / Delayed </th>
                                                        <th>Current </th>
                                                        
                                                   
                                                </thead>
                                                <tbody>
												<?php /*foreach($AryPos as $LateRow){ 
												if($LateRow['ReturnedDate']!=''){
													$CurrentStatus='Returned';
													$StatusColour='#90EE90';
													
												}else if($LateRow['ClosedDate']!=''){ 
													$CurrentStatus='Closed';
													$StatusColour='#90EE90';
													
												} else if($LateRow['DeliveredDate']!=''){
													$CurrentStatus='Delivered';
													$StatusColour='#FFFF8F';

												}
												else if($LateRow['ShippedOn']!=''){
													$CurrentStatus='Freight';
													$StatusColour='#ADD8E6';

													
												}
												else if($LateRow['TransportedDate']!=''){
													$CurrentStatus='Transported';
													$StatusColour='#A9A9A9';

													
												}	
												else if($LateRow['ProductionStartedDate']!=''){
													$CurrentStatus='Production';
												$StatusColour='#FA8072';}

												else{
												$CurrentStatus='Issued';
												$StatusColour='#FFFFFF';
	
												}
												
												if($LateRow['DaysRemaining'] > 0 && $LateRow['DaysRemaining'] < 6){													
													$RowStatus="warning-row";
													$StatusColour='#f7ff84';
												}
												
												
												
												 if($LateRow['DaysRemaining'] > 0 && $LateRow['DaysRemaining'] < 6){													
													$RowStatus="warning-row";
													$StatusColour='#f7ff84';
												}else if($LateRow['DaysRemaining'] <= 0 && $LateRow['TransportedDate']==''){
													$StatusColour='#df411d';
													$RowStatus="deadline-row";
												} else{
													$RowStatus="success-row";
												}
												
												if($LateRow['CInvNo']!='' && $LateRow['TransportedDate'] !='' ){
												$RowStatus="success-row";	
												}
												
												
												if($LateRow['ReturnedDate']!=''){
													$StatusColour='#df411d';
													$RowStatus="deadline-row";
												}
												
												if($LateRow['TransportedDate']!=''){
												$Days=$LateRow['DelayedDays'];
													if($Days>0){
													$StatusColor='green';
													}else{
													$StatusColor='red';
													}														
												}else{
													$StatusColor='blue';
												$Days=$LateRow['DaysRemaining'];	
												}
												
												
												//echo checkEmpty($LateRow['CInvNo']);
											
												
												
												?>
												
                                                    <tr class="<?php echo $RowStatus?>">
                                                     
                                                       <td >
													   <a target="_blank" id="PoDetails" href="#"
													   data-id="<?php echo $LateRow['PoID'] ?>" data-value="<?php echo $LateRow['PoNo'] ?>"
													   class="fill-div"><?php echo  $LateRow['PoID'] ?> </a></td>
                                                        <td ><?php echo  checkEmpty($LateRow['CInvNo']) ?></td>
                                                        <td ><?php echo checkEmpty(substr( $LateRow['PoNo'],0,20) .'<br>'.substr( $LateRow['PoNo'],20)) ?></td>
                                                        <td ><?php echo  checkEmpty(getFormatedDate($LateRow['PoDate'])) ?></td>
														                                                                                                                 <td ><?php echo  checkEmpty($LateRow['LotNo']) ?></td>

                                                        <td  ><?php echo checkEmpty(substr( $LateRow['ConsigneeName'],0,30) .'<br>'.substr( $LateRow['ConsigneeName'],30)) ?></td>
														<td  ><?php echo checkEmpty( getFormatedDate($LateRow['ShipByDate'])) ?></td>
                                                        <td ><?php echo  checkEmpty($LateRow['PortOfDischarge']) ?></td>
                                                        <td ><?php echo  checkEmpty($LateRow['CountryName']) ?></td>
                                                        <td  ><?php echo checkEmpty( getFormatedDate($LateRow['TransportedDate'])) ?></td>
                                                        <td ><?php echo  checkEmpty($LateRow['ContainerNo']) ?></td>
                                                        <td  ><?php echo checkEmpty( getFormatedDate($LateRow['ShippedOn'])) ?></td>
                                                        <td ><?php echo  checkEmpty($LateRow['BLNumber']) ?></td>
                                                        <td ><?php echo  checkEmpty(getFormatedDate($LateRow['ETA'])) ?></td>
                                                        <td >
														<div  class=" badge badge-warning font-size-10 "
	                                            style="width:70px;background:<?php echo $StatusColor; ?>;" >
												<?php echo $Days; ?>
							                           </div>	</td>
                                                        <td ><?php echo  $CurrentStatus ?></td>
                                                        
                                                    </tr>
													
												<?php }*/ ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>-->
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
  <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>


  <script>
  $(document).on('click','#list',function(e){
	
	
	
     var SID =$('#PurchaseStationID').val(); 
     var Year =$('#Year').val(); 
     var Month =$('#Month').val(); 
	 //alert(Month);
	// 
	 var Url='<?php echo $CurrentUrl ?>?Year='+Year +'&Month='+Month+'&PID='+SID;
	 //alert(Url);
	 
		window.open(Url,"_self");
	
		// alert(SelectedDate);

 });
  
  $(function () {
	  "use strict";
var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	
	data: [{
		type: "pie",
		yValueFormatString: "#,##0.\" T\"",
		indexLabel: "{label} ({y})",
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();
  })


</script>

	<script>
$(document).ready( function () {
    // $('#example1').DataTable();
    var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
  sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : false 
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
	  
	
	//alert(PoNo);
	
		 $.ajax({
		   url:'Ajax/AjaxPopUpPoDetails.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			 
			 $('.modal-title').text('PO Status Details - '+PoNo);
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


 
 $(document).on('click','#chbkExp',function(e){
	var chbkExp = $("#chbkExp").is(':checked');
	var chbkShip = $("#chbkShip").is(':checked');
	var SelectedDate='<?php echo $PresentYear ?>';
		
		updateGraph(chbkExp,chbkShip);
 }); 
 
 $(document).on('click','#chbkShip',function(e){
	var chbkExp = $("#chbkExp").is(':checked');
	var chbkShip = $("#chbkShip").is(':checked');
	var SelectedDate='<?php echo $PresentYear ?>';

		
		updateGraph(chbkExp,chbkShip);
 });

 $(document).on('click','#Update',function(e){
	 
	 var PoID =$('#Update').data('id');
	 var PoID =$('#Update').val();
	
	var Url='CreateShipmentStatus.php?PoID='+ PoID;
		window.open(Url,"_blank");
 });
 
 $(document).on('click','#BuyerDetails',function(e){
	 
	 var ConID=$('#ConID').val();
	
	
	
		 $.ajax({
		   url:'Ajax/AjaxBuyerDetails.php',
		  type:'POST',
		  data:{'ConID':ConID },
		  success:function(data){
			 
			 //$('.modal-title').text('Do You Want To Shipped This Po '+INT_LOT_NUM+' ?');
			$("#PopUpTable").html(data);
	/*	$('#PopUpTable').DataTable({
		"bPaginate": false,
		autoWidth: true,
        scrollY: 600,
        scrollX: true,
        scroller: true
    });*/
			$('#myModal').modal('show');
			
		
	
			//$('#myModal').modal('show');
					}
			});
			
    	});	



</script>
<script>

var ArySeries=<?php echo json_encode($Series); ?>;
var AryLables=<?php echo json_encode($Lables,true); ?>;
var AryColors=<?php echo json_encode($Colors,true); ?>;

var AryPortSeries=<?php echo json_encode($PortSeries); ?>;
var AryPortLables=<?php echo json_encode($PortLables,true); ?>;
var AryPortColors=<?php echo json_encode($PortColors,true); ?>;





options={series : ArySeries,chart:{height:230,type:"donut"},
labels : AryLables,
plotOptions:{pie:{donut:{size:"75%"}}},
dataLabels:{enabled:!1},legend:{show:!1},
colors : AryColors};

Portoptions={series : AryPortSeries,chart:{height:230,type:"donut"},
labels : AryPortLables,
plotOptions:{pie:{donut:{size:"75%"}}},
dataLabels:{enabled:!1},legend:{show:!1},
colors : AryPortColors};

(chart=new ApexCharts(document.querySelector("#donut-chart"),options)).render();
(chart=new ApexCharts(document.querySelector("#donut-chart-port"),Portoptions)).render();

/*Donut Chart End */




var AryShipMents=<?php echo json_encode($Shipments,true); ?>;
var AryOrders=<?php echo json_encode($Orders,true); ?>;
var AryXlables=<?php echo json_encode($MonthName,true); ?>;





var AryExportKgs=<?php echo json_encode($ExportKgs,true); ?>;
var AryShippedKgs=<?php echo json_encode($ShippedKgs,true); ?>;
var AryShipmentXlables=<?php echo json_encode($Grade,true); ?>;

var AryPresent=<?php echo json_encode($Present,true); ?>;
var AryAbsent=<?php echo json_encode($Absent,true); ?>;
var AryOnTime=<?php echo json_encode($OnTimeData,true); ?>;
var AryTotal=<?php echo json_encode($Total,true); ?>;
var AryDate=<?php echo json_encode($Date,true); ?>;


var SalesOptions={series:
[

//{name:"Total Kgs X 1000",type:"line",data:AryKgs},
{name:"Inward",type:"column",data:AryOrders},
{name:"OutWard",type:"column",data:AryShipMents}

], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1}},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
//colors:["#eeb902","#666","#ff0000"],
colors:["#666","#ff0000"],
labels: AryXlables};

var shipmentOptions={
	series:
[
{name:"Export in Kgs",type:"column",data:AryExportKgs} ,	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1}},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
//colors:["#5664d2","#eeb902","#ff0000"],
colors:["#5664d2","#ff0000"],
labels: AryShipmentXlables

};


var AryMcsBar3=<?php echo json_encode($AryMcsBar3,true); ?>;
var AryKgsBar3=<?php echo json_encode($AryKgsBar3,true); ?>;
var AryGradeBar3=<?php echo json_encode($AryGradeBar3,true); ?>;

var ProductionOptions={
	series:
[
{name:"Dummy Mcs",type:"column",data:AryMcsBar3} ,	
{name:"Dummy Kgs",type:"column",data:AryKgsBar3}
], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1}},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
//colors:["#5664d2","#eeb902","#ff0000"],
colors:["#5664d2","#ff0000"],
labels: AryGradeBar3

};




chart=new ApexCharts(document.querySelector("#line-column-chart"),SalesOptions);
chart.render();


chartProduction=new ApexCharts(document.querySelector("#production-chart"),ProductionOptions);
chartProduction.render();

//updateGraph();

function updateGraph(chbkExp,chbkShip){
	//alert(chbkExp);

//var AryShipmentXlables=<?php echo json_encode($Grade,true); ?>;

if(chbkExp==true && chbkShip ==true){	
var Series=[
{name:"Inward",type:"column",data:AryOrders},
{name:"OutWard",type:"column",data:AryShipMents}
];	
var Colors={colors:["#5664d2","#ff0000"]};
}else if(chbkExp){	
var Series=[{name:"Inward",type:"column",data:AryOrders}];
var Colors={colors:["#5664d2"]};
}else if(chbkShip){
var Series=[{name:"OutWard",type:"column",data:AryShipMents}];
var Colors={colors:["#ff0000"]};
}else{	
var Series=[
{name:"Inward",type:"column",data:AryOrders},
{name:"OutWard",type:"column",data:AryShipMents}
];	
var Colors={colors:["#5664d2","#ff0000"]};	
}	
	
chart.updateSeries(Series);	
chart.updateOptions(Colors);	
}






</script>

    </body>
</html>
