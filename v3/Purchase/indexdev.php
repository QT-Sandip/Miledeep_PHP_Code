<?php 
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


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
$Qry= " and PurchaseStationID=$PID";
}else{
$Qry= "";
}



$CurrentFinYear=getCurrentFinancialYear();
$DateFilter=getDateFilter($Year,$PresentMonth); 

$SqlDeptList="SELECT * FROM RmPurchaseStations"; 
$StmtDept=$iconn->query($SqlDeptList);


$SqlLotIds="SELECT COALESCE(GROUP_CONCAT(RmLotID),0) as LotIDs FROM RmLotDetatils as RMD  
WHERE DATE(RMD.PurchaseDate) $DateFilter $Qry"; 
$StmtLotIds=$iconn->query($SqlLotIds);
$RowLotIds=$StmtLotIds->fetch_array();
$LotIDs=$RowLotIds['LotIDs'];

$SqlBilledLotIds="SELECT GROUP_CONCAT(LotID) as LotIDs 
 FROM `RmBillBreakup` WHERE LotID in ($LotIDs)"; 
$StmtBilledLotIds=$iconn->query($SqlBilledLotIds);
$RowBilledLotIds=$StmtBilledLotIds->fetch_array();
 $BilledLotIDs=$RowBilledLotIds['LotIDs']; 
if($LotIDs==''){	
$LotIDs=0;	
}

if($BilledLotIDs==''){	
$BilledLotIDs=0;	
}



 $SqlGrading="SELECT
   RMD.RmLotID,
   RMD.LotNumber,
   RMD.PurchaseDate,
   RMD.LotStatus,
   RMD.FarmAddress,
   RmPC.PurchaseType,
   RMD.PurchaseWeight,
   GD.GradingDate,
   GD.LotID as GdAval,
   RMBD.LotID as BillAval,
   RMBD.BillStatus,
   QCAbO.Name as AbStatus,
   RmFin.FinancierName as FinancierName,
  RMBD.LotID AS CostingAval,
  COALESCE(DATEDIFF(GD.GradingDate,RMD.PurchaseDate),0) as DaysRemaining,
  (SELECT COUNT(*) from QcRmOrgnReport as ORGD WHERE  ORGD.LotID=RMD.RmLotID ) as OrgnReport
  from RmLotDetatils as RMD
  LEFT join PpcHLGradingDetails as GD on RMD.RmLotID=GD.LotID
  LEFT join RmBillDetails as RMBD on RMD.RmLotID=RMBD.LotID
  LEFT JOIN QcRmAbReport as QCAbR on RMD.RmLotID=QCAbR.LotID
  LEFT JOIN QcABOptions as QCAbO on QCAbO.OptID=QCAbR.AbStatus
  LEFT JOIN RmFinanciers as RmFin on RmFin.ID=RMBD.FinancierID
  LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RMD.PurchaseCategoryID
where not exists (select 1 from RmBillDetails RMBL where RMBL.LotID = RMD.RmLotID ) 
and RMD.RmLotID in ($LotIDs) and LotStatus =1 ORDER BY RMD.PurchaseDate DESC;";   
$StmtTodayLots=$iconn->query($SqlGrading);


  $SqlTodayStatus="SELECT COUNT(*) as TotalLots,
(SELECT count(*) from RmLotDetatils as RMOR  WHERE RMOR.RmLotID in ($LotIDs) and RMOR.LotStatus=2 ) as ReturnLots, 
(SELECT count(*) from QcRmAbReport as RAB WHERE RAB.LotID in ($LotIDs)  and RAB.AbStatus=11 ) as AbLots, 
(SELECT count(*) from PpcHLGradingDetails as HLGD  WHERE HLGD.LotID in ($LotIDs) ) as GradedLots, 
(SELECT count(*) from RmBillDetails as RBD  WHERE RBD.ArrivalID in ($LotIDs) ) as BilledLots 
FROM RmLotDetatils as RMD where RMD.RmLotID in ($LotIDs)";
$TodayStatusExec=$iconn->query($SqlTodayStatus); 
$TodayStatusRow=$TodayStatusExec->fetch_array();

$TileOne=$TodayStatusRow['TotalLots'];
$TileTwo=$TodayStatusRow['AbLots'];
$TileThree=$TodayStatusRow['ReturnLots'];
$TileFour=$TodayStatusRow['BorderLots']+0;
$TileFive=$TodayStatusRow['TotalLots']-($TodayStatusRow['GradedLots']+$TodayStatusRow['ReturnLots']);
$TileSix=$TodayStatusRow['TotalLots']-($TodayStatusRow['BilledLots']+$TodayStatusRow['ReturnLots']);
 
 $SqlBilledWeight="SELECT PurchaseCategoryID,PurchaseType,Color,sum(Weight) as PurchaseWeight FROM RmBillDetails as RBD 
LEFT join RmLotDetatils as RMD on RMD.RmLotID=RBD.ArrivalID 
LEFT join RmBillBreakup as RBU on RBU.ArrivalID=RBD.ArrivalID 
LEFT join RmPurchaseCategory as RPC on RMD.PurchaseCategoryID=RPC.CatID 
WHERE RMD.RmLotID in ($BilledLotIDs) and  RMD.LotStatus=1
GROUP by RMD.PurchaseCategoryID";   
$BilledWeightExec=$iconn->query($SqlBilledWeight);
while($DateRow=$BilledWeightExec->fetch_array()){
$Series[]=round($DateRow['PurchaseWeight']);
$Lables[]=$DateRow['PurchaseType'];
$Colors[]=$DateRow['Color'];
$AryDateRow[]=$DateRow;
$TotalHonQty+=$DateRow['PurchaseWeight'];
}



$SqlBorderCounts="SELECT count(*) as BorderCounts FROM `RmBillBreakup` WHERE LotID in ($BilledLotIDs)
and (
(Count >= 30 and Count < 32.25) ||
(Count >= 32.26 and Count < 35.25) ||
(Count >= 40 and Count < 42.25) ||
(Count >= 42.26 and Count < 45.25) ||
(Count >= 50 and Count < 52.25) ||
(Count >= 60 and Count < 62.25) ||
(Count >= 70 and Count < 72.25) ||
(Count >= 80 and Count < 82.25) ||
(Count >= 90 and Count < 92.25) ||
(Count >= 100 and Count < 102.25)) and Description='Hl Grading Hon'";

$StmtBorderCounts=$iconn->query($SqlBorderCounts);
$RowBorderCounts=$StmtBorderCounts->fetch_array();
$BorderCounts=$RowBorderCounts['BorderCounts'];
$TileFour=$BorderCounts;


 




   $SqlMonthlyAtt="SELECT PurchaseDate,
(SELECT COALESCE(sum(Weight),0) from RmBillBreakup 
LEFT join RmLotDetatils RLD1 on RLD1.RmLotID=LotID  
WHERE RLD1.PurchaseDate=RMD.PurchaseDate and RLD1.PurchaseCategoryID=1 and LotID in ($BilledLotIDs)) as ByYield,
(SELECT COALESCE(sum(Weight),0)  from RmBillBreakup 
LEFT join RmLotDetatils RLD1 on RLD1.RmLotID=LotID  
WHERE RLD1.PurchaseDate=RMD.PurchaseDate and RLD1.PurchaseCategoryID=2 
and LotID in ($BilledLotIDs)) as SiteWeightment,
(SELECT COALESCE(sum(Weight),0)  from RmBillBreakup 
LEFT join RmLotDetatils RLD1 on RLD1.RmLotID=LotID  
WHERE RLD1.PurchaseDate=RMD.PurchaseDate and RLD1.PurchaseCategoryID=3 
and LotID in ($BilledLotIDs)) as PlantWeightment,
(SELECT COALESCE(sum(Weight),0)  from RmBillBreakup 
LEFT join RmLotDetatils RLD1 on RLD1.RmLotID=LotID  
WHERE RLD1.PurchaseDate=RMD.PurchaseDate and RLD1.PurchaseCategoryID not in (1,2,3) 
and LotID in ($BilledLotIDs)) as Other

FROM RmBillDetails as RBD 
LEFT join RmLotDetatils as RMD on RMD.RmLotID=RBD.ArrivalID 
WHERE RMD.RmLotID in ($BilledLotIDs) and  RMD.LotStatus=1
GROUP by RMD.PurchaseDate;"; 

$StmtMonAtt=$iconn->query($SqlMonthlyAtt);
 

while($MonAttRow=$StmtMonAtt->fetch_array()){
	$Bar1[]=round($MonAttRow['ByYield']);
	$Bar2[]=round($MonAttRow['SiteWeightment']);
	$Bar3[]=round($MonAttRow['PlantWeightment']);
	$Bar4[]=round($MonAttRow['Other']);
	$Xaxis[]=date("d-m-y", strtotime($MonAttRow['PurchaseDate']));
}

 $SqlGradeBreakUp="SELECT PackingGrade,sum(HLWeight) as HLWeight FROM `PpcHLGradingBreakup` 
WHERE LotID in ($BilledLotIDs) GROUP by PackingGrade ORDER by abs(PackingGrade);";

$StmtGradeBreakUp=$iconn->query($SqlGradeBreakUp);
 

while($MonAttRow=$StmtGradeBreakUp->fetch_array()){
	$G2Bar1[]=round($MonAttRow['HLWeight']);
	$G2Xaxis[]=$MonAttRow['PackingGrade'];
	$TotalGradingKgs+=$MonAttRow['HLWeight'];
}

 $SqlHonPacked="SELECT COALESCE(sum(Weight),0) as HonPacked 
from RmBillBreakup WHERE Description='Hon Packed' and LotID in ($BilledLotIDs) ";
$StmtHonPacked=$iconn->query($SqlHonPacked);
$RowHeadonPacked=$StmtHonPacked->fetch_array();
$HonPacked=$RowHeadonPacked['HonPacked'];


$AvgYield=round(($TotalGradingKgs/($TotalHonQty-$HonPacked))*100,2);

  $SqlPresentStock="SELECT DefectName,sum(DefectWeight) as DefectWeight FROM `RmDefectsDetails`  as RDD 
left join RmDefectsList as RDL ON RDD.DefectID=RDL.DefectID 
left join RmLotDetatils as RLD On RLD.RmLotID=RDD.LotID
WHERE RLD.RmLotID in ($BilledLotIDs) and RLD.LotStatus=1
GROUP by RDD.DefectID;";  

 $SqlPresentStock="SELECT RUL.UserName,AgentID,count(*) as SuppliedLots FROM RmLotDetatils  as RMD 
 left join RmUsersList as RUL on RMD.AgentID=RUL.ID WHERE RmLotID in ($BilledLotIDs)  GROUP by AgentID;"; 

$StmtPresentStock=$iconn->query($SqlPresentStock);
$AryVarity=array();
$AryKgs=array();
$AryMcs=array();
while($RowPresentStock=$StmtPresentStock->fetch_array()){
	$AryVarity[]=$RowPresentStock['UserName'];
	$AryMcs[]=$RowPresentStock['SuppliedLots'];
	$TotalKgs=$RowPresentStock['SuppliedLots'];
	$TotalAgentLots+=$RowPresentStock['SuppliedLots'];
	$AryKgs[]=round($TotalKgs,2);
	
	
$data_d['value']= round($RowPresentStock['SuppliedLots']);
$data_d['color'] =sprintf("#%06x",rand(0,16777215));
$data_d['highlight'] ='#32CD32';
$data_d['label'] =$RowPresentStock['UserName'];
$Cjs['label']=$RowPresentStock['UserName'];
$Cjs['y']=round($TotalKgs);
$dounat_DATA[]=$data_d;
$dataPoints[]=$Cjs;
}





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

<?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

        <!-- Begin page -->
        <div id="layout-wrapper">
			<?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			
			//exit; 
			?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                       

				<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										<div class="row">
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                       <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
                                                            <option value="">All Purchase Station</option>
														
															<?php while($DataRow=$StmtDept->fetch_array()){?>	
				 
												<option value="<?php echo $DataRow['PurchaseStationID'] ;?>"
 <?php if($DataRow['PurchaseStationID']==$PID){ echo 'selected="selected"'; }?>												>
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
							
							<div class="row">
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Total Lots</p>
                                                        <h4 class="mb-0"><?php echo $TileOne ?></h4>
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
                                                        <p class="text-truncate font-size-14 mb-2">AB+ Lots</p>
                                                        <h4 class="mb-0"><?php echo $TileTwo ?></h4>
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
                                                        <p class="text-truncate font-size-14 mb-2">Return Lots</p>
                                                        <h4 class="mb-0"><?php echo $TileThree ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
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
                                                        <p class="text-truncate font-size-14 mb-2">Border Counts</p>
                                                        <h4 class="mb-0"><?php echo $TileFour ?></h4>
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
                                                        <p class="text-truncate font-size-14 mb-2">Grading Pendings</p>
                                                        <h4 class="mb-0"><?php echo $TileFive ?></h4>
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
                                                        <p class="text-truncate font-size-14 mb-2">Bill Pendings</p>
                                                        <h4 class="mb-0"><?php echo $TileSix ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                          
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="card">
                                    <div class="card-body">
                                        <div class="float-right d-none d-md-inline-block">
                                           
                                        </div>
                                        <h4 class="card-title mb-4">Daily Purchase Analytics</h4>
                                        <div>
                                            <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                        </div>
                                    </div>

                                </div>
								
								 
								<div class="card">
                                    <div class="card-body">
									<h4 class="card-title mb-4">Headless Details - <?php echo round(($TotalGradingKgs/1000),2) ?> T</h4>
                                    <div>
                                    <div id="production-chart" class="apex-charts" dir="ltr"></div>
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
													Hon Qty- <?php echo round(($TotalHonQty/1000),2) ?> T</p>
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
													<input  type = "checkbox" id="chbkExp" <?php if($chbkExp !='false'){ ?>
																checked
																<?php } ?>>
													Hon Packed- <?php echo 
													round(($HonPacked/1000),2) ?> T</p>
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
													<input  type = "checkbox" id="chbkExp" <?php if($chbkExp !='false'){ ?>
																checked
																<?php } ?>>
													Avg Yield- <?php echo $AvgYield ?> %</p>
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
                                        
                                        <h4 class="card-title mb-4">Purchase Quantity - <?php echo round($TotalHonQty/1000,2) ?> T </h4>

                                        <div id="donut-chart" class="apex-charts"></div>

                                        <div class="row">
                                            <?php foreach($AryDateRow as $DateRow){ ?>
												<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													class="mdi mdi-circle font-size-8 mr-1" 
													style="color: <?php echo $DateRow['Color'] ?>;">
													</i> <?php echo $DateRow['PurchaseType'] ?></p>
                                                    <b><?php echo round(($DateRow['PurchaseWeight']/1000),2) ."T"  ?></b>
                                                </div>
                                            </div>
										<?php	} ?>
											
											
                                           
                                        </div>
                                    </div>
                                </div>

                                 <div class="card">
                                    <div class="card-body">
                                        

                                        <h4 class="card-title mb-4">Supplied Lots(Agent wise) - <?php echo $TotalAgentLots ?> Lots</h4>
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


                        <div class="row">
                            
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        

                                        <h4 class="card-title mb-4">Running Lots</h4>

                                        <div class="table-responsive">
                                            <table id="key-datatable" data-paging="false"  
										class="table dt-responsive nowrap w-100  " >
				
                                                <thead class="thead-light">
														<th>Lot Number</th>
                                                        <th>Purchase Type</th>
                                                        <th>Location</th>
                                                      
                                                        <th>Weight</th>
                                                        <th>Count</th>
                                                        <th>Purchase Date</th>
                                                        <th>Grading Date</th>
                                                       
                                                        <th>Delayed Days</th> 
														<th>Status</th>
                                                    
                                                </thead>
                                                <tbody>
												<?php while($BillRow=$StmtTodayLots->fetch_array()){ 
		if($BillRow['LotStatus']== 2){
			$STATUS='Lot Return';
			$STATUS_CLR='#f00';
		}else if($BillRow['GdAval']== ''){
			$STATUS='Grading Not Aval';
			$STATUS_CLR='#f00';
		}else if($BillRow['OrgnReport'] == 0){
			$STATUS='Rm Report Not Aval';
			$STATUS_CLR='#f00';
		}else if($BillRow['BillAval']== ''){
			$STATUS='Bill Not Aval';
			$STATUS_CLR='#f00';
			$AddBill=true;
		}else if($BillRow['BillStatus']== '0'){
			$STATUS='Bill Approval Pending';
			$STATUS_CLR='#f00';
			//$AddBill=true;
		}else if($BillRow['BillAval']!= ''){
		 $STATUS='All Clear';
		 $STATUS_CLR='Green';		 
		} ?>
                                                    <tr>
                                                       
                                                        
                                                       <td><a target="blank" href="LotPreview?LotID=<?php echo $BillRow['RmLotID'] ?>">
													   <?php echo  $BillRow['LotNumber'] ?></a>
													   </td>
                                                        
                                                        <td><?php echo  $BillRow['PurchaseType'] ?></td>
                                                       
                                                        <td><?php echo  $BillRow['FarmAddress'] ?></td>
														<td><?php echo  $BillRow['PurchaseWeight'] ?></td>
														<td><?php echo  $BillRow['PurchaseCount'] ?></td>
                                                        <td><?php echo  date("d-m-Y", strtotime($BillRow['PurchaseDate'])) ?></td>
                                                        <td><?php echo getFormatedDateNA($BillRow['GradingDate'])  ?></td>
														 <td><?php echo  $BillRow['DaysRemaining'] ?> Days</td>
													   <td align="CENTER" width="7%">
													<div style="background:<?php echo $STATUS_CLR ?>" 
													class="btn btn-link badge badge-success font-size-12 " 
													align="center" >
									<span class="label" align="center"  ><?php echo $STATUS ?></span>
									</div>
			   
				</td>
                                                    </tr>
													
												<?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
        
		//include ($IncludesPath."right-sidebar.php");
		include ($IncludesPath."vendor-scripts.php");
		
		?>

        <!-- apexcharts -->
        <script src="<?php echo $BaseDist ?>assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- jquery.vectormap map -->
        <script src="<?php echo $BaseDist ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-us-merc-en.js"></script>

        <!-- Required datatable js -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <!-- Responsive examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <script src="<?php echo $BaseDist ?>assets/js/pages/dashboard.init.js"></script>

        <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>
		
<script>

$(document).on('click','#list',function(e){
	
	
	
     var SID =$('#PurchaseStationID').val(); 
     var Year =$('#Year').val(); 
     var Month =$('#Month').val(); 
	// alert(SelectedDate);
	 
	 var Url='<?php echo $CurrentUrl ?>?Year='+Year+'&Month='+Month+'&PID='+SID;
	// alert(Url);
	 
		window.open(Url,"_self");
	
		// alert(SelectedDate);

 });


var ArySeries=<?php echo json_encode($Series); ?>;
var AryLables=<?php echo json_encode($Lables,true); ?>;
var AryColors=<?php echo json_encode($Colors,true); ?>;

options={series : ArySeries,chart:{height:230,type:"donut"},
labels : AryLables,
plotOptions:{pie:{donut:{size:"75%"}}},
dataLabels:{enabled:!1},legend:{show:!1},
colors : AryColors};

(chart=new ApexCharts(document.querySelector("#donut-chart"),options)).render();

/*Donut Chart End */



var AryBar1=<?php echo json_encode($Bar1,true); ?>;
var AryBar2=<?php echo json_encode($Bar2,true); ?>;
var AryBar3=<?php echo json_encode($Bar3,true); ?>;
var AryBar4=<?php echo json_encode($Bar4,true); ?>;
var AryXaxis=<?php echo json_encode($Xaxis,true); ?>;


var options={series:[
{name:"By Yiled",type:"column",data: AryBar1},
{name:"Site Weightment",type:"column",data: AryBar2},
{name:"Plant Weightment",type:"column",data:AryBar3},
{name:"Others",type:"column",data:AryBar4}
], 
chart:{height:280,type:"line",toolbar:{show:!1}},
stroke:{width:[0,2,2,2],curve:"smooth"},
plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
colors:["#5664d2","#ff0000","#eeb902","#5664d2"],
labels: AryXaxis},
chart=new ApexCharts(document.querySelector("#line-column-chart"),options);
chart.render();


	
var AryG2Bar1=<?php echo json_encode($G2Bar1,true); ?>;
var AryG2Xaxis=<?php echo json_encode($G2Xaxis,true); ?>;	
	
var shipmentOptions={
	series:
[
{name:"Grading in Kgs",type:"column",data:AryG2Bar1}
], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1}},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
//colors:["#5664d2","#252b3b","#ff0000"],
colors:["#5664d2"],
labels: AryG2Xaxis

};

chartProduction=new ApexCharts(document.querySelector("#production-chart"),shipmentOptions);
chartProduction.render();

</script>

  <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>


  <script>
  $(function () {
	  "use strict";
var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	
	data: [{
		type: "pie",
		yValueFormatString: "#,##0.\" Lots\"",
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
        scrollY: "450px",
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

 /*function upDateCurrentMcs(ClickedId){
	 alert('hi');
 }*/
</script>

    </body>
</html>
