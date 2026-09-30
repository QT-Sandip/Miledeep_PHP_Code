<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
$TodayDate=$dateTime->format("Y-m-d");
$TodayDate=date('Y-m-d');
//$TodayDate='2021-10-20';

$PresentMonth=date('m');
$Year=date('Y');

$SqlDeptList="SELECT * FROM $RmPurchaseStations";
$StmtDept=$iconn->query($SqlDeptList);

  $SqlGrading="select RmLotID,LotNumber,CreatedDateTime,PurchaseDate from RmLotDetatils as RMD  where not exists 
				(select 1 from PpcHLGradingDetails HlGD where HlGD.LotID = RMD.RmLotID ) and Year > 2020 and LotStatus =1     
				ORDER BY RMD.PurchaseDate DESC";  
  
  $SqlBills="select RmLotID,LotNumber,CreatedDateTime,PurchaseDate from RmLotDetatils as RMD  
				where not exists 
				(select 1 from RmBillDetails RMBL where RMBL.LotID = RMD.RmLotID ) and Year > 2020 and  LotStatus =1   
				ORDER BY RMD.PurchaseDate DESC";  

  $SqlBillsPending="SELECT RmLotID,LotNumber,RBD.CreatedDateTime,PurchaseDate FROM RmBillDetails as RBD
			LEFT join RmLotDetatils as RMD on RMD.RmLotID=RBD.LotID WHERE BillStatus=0  and LotStatus =1 
				ORDER BY RMD.PurchaseDate DESC";   

	   $SqlTodayStatus="SELECT COUNT(*) as AbLots,
(SELECT count(*) from QcRmOrgnReport WHERE DATE(PostedDate)='$TodayDate' ) as RmQReports,
(SELECT count(*) from RmLotDetatils WHERE DATE(CreatedDateTime)='$TodayDate') as TodayLots,
(SELECT count(*) from PpcHLGradingDetails WHERE GradingDate='$TodayDate' ) as TodayGrading,
(SELECT count(*) from RmBillDetails WHERE DATE(CreatedDateTime)='$TodayDate' ) as TodayBills

 FROM RmLotDetatils as RMD 
LEFT join QcRmAbReport as RAB on RMD.RmLotID=RAB.LotID WHERE DATE(PurchaseDate)='$TodayDate' 
and AbStatus=11"; 
				
				
$StmtGrading=$iconn->query($SqlGrading);
$StmtBills=$iconn->query($SqlBills);
$StmtBilAppPending=$iconn->query($SqlBillsPending);
$TodayStatusExec=$iconn->query($SqlTodayStatus);
$TodayStatusRow=$TodayStatusExec->fetch_array();


$SqlTodayLots="SELECT *,RUL_G.UserName as GraderName,RUL_A.UserName as AgentName  from RmLotDetatils as RLD
left join RmPurchaseStations  as RPS on RLD.PurchaseStationID=RPS.PurchaseStationID
left join RmUsersList  as RUL_G on RLD.GraderID=RUL_G.ID
left join RmUsersList  as RUL_A on RLD.AgentID=RUL_A.ID
left join RmPurchaseCategory  as RPC on RLD.PurchaseCategoryID=RPC.CatID
where DATE(CreatedDateTime)='$TodayDate' $Query"; 
$StmtTodayLots=$iconn->query($SqlTodayLots);


$PreviousMonth=$PresentMonth-1;

   $RmBrkUpQuery="SELECT PurchaseType,
		count(*) as PrevLots,
        sum(PurchaseWeight) as PrevWeight,
(select count(*)  from RmLotDetatils where month(PurchaseDate)='$PresentMonth' and Year='$Year' and LotStatus=1 and PurchaseCategoryID=RPC.CatID) as PrstLots,
(select sum(PurchaseWeight) from RmLotDetatils where month(PurchaseDate)='$PresentMonth' and Year='$Year' and LotStatus=1 and PurchaseCategoryID=RPC.CatID) as PrstWeight ,

(select sum(PurchaseWeight) from RmLotDetatils where month(PurchaseDate)='$PreviousMonth' and Year='$Year' and LotStatus=1) as PrevTotalWeight,
(select sum(PurchaseWeight) from RmLotDetatils where month(PurchaseDate)='$PresentMonth' and Year='$Year' and LotStatus=1) as PrstTotalWeight

FROM RmLotDetatils as RMD
left Join RmPurchaseCategory as RPC on RMD.PurchaseCategoryID=RPC.CatID
WHERE month(PurchaseDate)='$PreviousMonth'  and Year='$Year' and LotStatus=1 group by RMD.PurchaseCategoryID"  ; 
$RmBrkExec=$iconn->query($RmBrkUpQuery);

while($RmBrkRow=$RmBrkExec->fetch_array()){
	
	$RmBrkRow['WeightPerc']=(($RmBrkRow['PrstWeight']-$RmBrkRow['PrevWeight'])/$RmBrkRow['PrevWeight'])*100;
	$RmBrkRow['TotalWeightPerc']=(($RmBrkRow['PrstTotalWeight']-$RmBrkRow['PrevTotalWeight'])/$RmBrkRow['PrevTotalWeight'])*100;
	$PurchaseDate[]=$RmBrkRow;
}






$PrstPurCatQuery="SELECT PurchaseType,Color,sum(PurchaseWeight) as PurchaseWeight FROM RmLotDetatils as RMD 
LEFT join RmPurchaseCategory as RPC on RMD.PurchaseCategoryID=RPC.CatID 
WHERE month(PurchaseDate)='$PresentMonth' and Year='$Year' and PurchaseWeight !=0  and LotStatus=1
GROUP by RMD.PurchaseCategoryID";
$PrstPurCatExec=$iconn->query($PrstPurCatQuery);
while($PrstPurCatRow=$PrstPurCatExec->fetch_array()){
//$AttShiftRow['Color']=sprintf('#%06X', mt_rand(0, 0xFFFFFF));
$Series[]=round($PrstPurCatRow['PurchaseWeight']);
$Lables[]=round($PrstPurCatRow['PurchaseType']);
$Colors[]=$PrstPurCatRow['Color'];
$AryShiftDtls[]=$PrstPurCatRow;
	
} 

$PrevPurCatQuery="SELECT PurchaseType,Color,sum(PurchaseWeight) as PurchaseWeight FROM RmLotDetatils as RMD 
LEFT join RmPurchaseCategory as RPC on RMD.PurchaseCategoryID=RPC.CatID 
WHERE month(PurchaseDate)='$PresentMonth' and Year='$Year' and PurchaseWeight !=0  and LotStatus=1
GROUP by RMD.PurchaseCategoryID";
$PrevPurCatExec=$iconn->query($PrevPurCatQuery);
while($PrevPurCatRow=$PrevPurCatExec->fetch_array()){
//$AttShiftRow['Color']=sprintf('#%06X', mt_rand(0, 0xFFFFFF));
$PrevSeries[]=round($PrevPurCatRow['PurchaseWeight']);
$PrevLables[]=round($PrevPurCatRow['PurchaseType']);
$PrevColors[]=$PrevPurCatRow['Color'];
$PrevAryShiftDtls[]=$PrevPurCatRow;
	
} 




   $SqlMonthlyAtt="SELECT count(*) As PreviousLots ,sum(PurchaseWeight) as PreviousWeight,PurchaseDate,PurchaseCount,
 (SELECT COALESCE(sum(PurchaseWeight),0) from RmLotDetatils WHERE day(PurchaseDate)=day(RMD.PurchaseDate)  
 and month(PurchaseDate)='$PresentMonth' and Year='$Year' and LotStatus=1) as PresentWeight,
  (SELECT count(*) from RmLotDetatils WHERE day(PurchaseDate)=day(RMD.PurchaseDate)  
 and month(PurchaseDate)='$PresentMonth' and Year='$Year' and LotStatus=1) as PresentLots
 from RmLotDetatils as RMD WHERE  month(PurchaseDate)='$PreviousMonth' and Year='$Year'
 and LotStatus=1 GROUP by PurchaseDate";

$StmtMonAtt=$iconn->query($SqlMonthlyAtt);
 

while($MonAttRow=$StmtMonAtt->fetch_array()){
	$PresentWeight[]=round($MonAttRow['PresentWeight']);
	$PreviousWeight[]=round($MonAttRow['PreviousWeight']);
	$PreviousLots[]=round($MonAttRow['PreviousLots']);
	$PresentLots[]=round($MonAttRow['PresentLots']);
	$Date[]=date("d-m-y", strtotime($MonAttRow['PurchaseDate']));
}





?>




                        <div class="row">
                            <div class="col-xl-8">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Today Lots</p>
                                                        <h4 class="mb-0"><?php echo $TodayStatusRow['TodayLots'] ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-stack-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>

                                           <!-- <div class="card-body border-top py-3">
                                                <div class="text-truncate">
                                                    <span class="badge badge-soft-success font-size-11"><i class="mdi mdi-menu-up"> </i> 2.4% </span>
                                                    <span class="text-muted ml-2">From previous period</span>
                                                </div>
                                            </div> -->
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Today Grading</p>
                                                        <h4 class="mb-0"><?php echo $TodayStatusRow['TodayGrading'] ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--<div class="card-body border-top py-3">
                                                <div class="text-truncate">
                                                    <span class="badge badge-soft-success font-size-11"><i class="mdi mdi-menu-up"> </i> 2.4% </span>
                                                    <span class="text-muted ml-2">From previous period</span>
                                                </div>
                                            </div>-->
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Today Bills</p>
                                                        <h4 class="mb-0"><?php echo $TodayStatusRow['TodayBills'] ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                           <!-- <div class="card-body border-top py-3">
                                                <div class="text-truncate">
                                                    <span class="badge badge-soft-success font-size-11"><i class="mdi mdi-menu-up"> </i> 2.4% </span>
                                                    <span class="text-muted ml-2">From previous period</span>
                                                </div>
                                            </div>-->
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="card">
                                    <div class="card-body">
                                        <div class="float-right d-none d-md-inline-block">
                                           <!-- <div class="btn-group mb-2">
                                                <button type="button" class="btn btn-sm btn-light">Today</button>
                                                <button type="button" class="btn btn-sm btn-light active">Weekly</button>
                                                <button type="button" class="btn btn-sm btn-light">Monthly</button>
                                            </div>-->
                                        </div>
                                        <h4 class="card-title mb-4">Monthly Purchase Analytics</h4>
                                        <div>
                                            <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                        </div>
                                    </div>

                                    <div class="card-body border-top text-center">
                                        <div class="row">
										
										<?php 
										
										foreach($PurchaseDate as $MData){ ?>


                                            <div class="col-sm-3">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<i class="mdi mdi-circle text-primary font-size-10 mr-1">
													</i><?php echo $MData['PurchaseType'] ?></p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"><?php echo round($MData['PrstWeight']/1000,2) ?> T</h5>
                                                        
														<?php if($MData['WeightPerc'] > 0){ ?>
														
														<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>
															<?php echo round($MData['WeightPerc'],2) ?> %
                                                        </div>
														
														<?php }else {?>
														<div class="text-danger">
                                                            <i class="mdi mdi-menu-down font-size-14"> </i>
															<?php echo round($MData['WeightPerc'],2) ?> %
                                                        </div>
														<?php }?>
														
                                                    </div>
                                                </div>
                                            </div>
											
										<?php }	?>
											
											

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <!--<div class="float-right">
                                            <select class="custom-select custom-select-sm">
                                                <option selected>Apr</option>
                                                <option value="1">Mar</option>
                                                <option value="2">Feb</option>
                                                <option value="3">Jan</option>
                                            </select>
                                        </div>-->
                                        <h4 class="card-title mb-4">Purchase Based On Category</h4>

                                        <div id="donut-chart" class="apex-charts"></div>

                                        <div class="row">
                                            <?php foreach($AryShiftDtls as $ShiftRow){ ?>
												<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													class="mdi mdi-circle font-size-10 mr-1" 
													style="color: <?php echo $ShiftRow['Color'] ?>;">
													</i> <?php echo $ShiftRow['PurchaseType'] ?></p>
                                                    <h5><?php echo $ShiftRow['PurchaseWeight'] ?></h5>
                                                </div>
                                            </div>
										<?php	} ?>
											
											
                                           
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="dropdown float-right">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                               </div>
                                        </div>

                                        <h4 class="card-title mb-4">Quality Control</h4>
                                        <div class="text-center">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div>
                                                        <div class="mb-3">
                                                            <div id="radialchart-1" class="apex-charts"></div>
                                                        </div>

                                                        <p class="text-muted text-truncate mb-2">AB+ Lots</p>
                                                        <h5 class="mb-0"><?php echo $TodayStatusRow['AbLots'] ?></h5>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="mt-5 mt-sm-0">
                                                        <div class="mb-3">
                                                            <div id="radialchart-2" class="apex-charts"></div>
                                                        </div>

                                                        <p class="text-muted text-truncate mb-2">Rm Reports</p>
                                                        <h5 class="mb-0"><?php echo $TodayStatusRow['RmQReports'] ?></h5>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row">
                            
							<div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="dropdown float-right">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                              <a  target="_blank" href="../Purchase/BillsList"class="dropdown-item">View</a>
											  </div>
                                        </div>

                                        <h4 class="card-title mb-4">Grading Pending</h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($GradingRow=$StmtGrading->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
													
											 
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle">
														 <a  target="_blank" href="../Purchase/LotPreview?LotID=<?php echo $GradingRow['RmLotID'] ?> "class="ri-edit-2-fill"></a>
                                                           
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13">Posted On <?php echo date("d-M-y", strtotime($GradingRow['CreatedDateTime'])); ?>
															<small class="text-muted"><?php echo date("H:i:s", strtotime($GradingRow['CreatedDateTime'])); ?></small></h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo $GradingRow['LotNumber'] ?> 
															Purchased On  
															<?php echo date("d-M-y", strtotime($GradingRow['PurchaseDate'])); ?></p>
                                                        </div>
                                                    </div>
                                                </li>
											  <?php } ?>
                                              
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

							
							
							
							
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="dropdown float-right">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <!-- item-->
												<a  target="_blank" href="../Purchase/BillsList"class="dropdown-item">View</a>
                                                <!-- item-->
                                               
                                            </div>
                                        </div>

                                        <h4 class="card-title mb-4">Bills Pending</h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($RowBills=$StmtBills->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle">
														 <a  target="_blank" href="../Purchase/LotPreview?LotID=<?php echo $RowBills['RmLotID'] ?> "class="ri-edit-2-fill"></a>
                                                           
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13">Posted On <?php echo date("d-M-y", strtotime($RowBills['CreatedDateTime'])); ?>
															<small class="text-muted"><?php echo date("H:i:s", strtotime($RowBills['CreatedDateTime'])); ?></small></h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo $RowBills['LotNumber'] ?> 
															Purchased On  
															<?php echo date("d-M-y", strtotime($RowBills['PurchaseDate'])); ?></p>
                                                        </div>
                                                    </div>
                                                </li>
											  <?php } ?>
                                              
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          
<!---->

							<div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="dropdown float-right">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                              
                                            </div>
                                        </div>

                                        <h4 class="card-title mb-4">Bill Approval Pending</h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($RowPending=$StmtBilAppPending->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle">
														 <a  target="_blank" href="../Purchase/LotPreview?LotID=<?php echo $RowPending['RmLotID'] ?> "class="ri-edit-2-fill"></a>
                                                           
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13"><?php echo date("d-M-y", strtotime($RowPending['CreatedDateTime'])); ?>
															<small class="text-muted"><?php echo date("H:i:s", strtotime($RowPending['CreatedDateTime'])) ?></small></h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo $RowPending['LotNumber'] ?> 
															Bill Rised On  
															<?php echo date("d-M-y", strtotime($RowPending['CreatedDateTime'])); ?></p>
                                                        </div>
                                                    </div>
                                                </li>
											  <?php } ?>
                                              
                                            </ul>
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
                                        <div class="dropdown float-right">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Sales Report</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Profit</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                            </div>
                                        </div>

                                        <h4 class="card-title mb-4">Today Lots</h4>

                                        <div class="table-responsive">
                                            <table class="table table-centered datatable dt-responsive nowrap" data-page-length="5" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                <thead class="thead-light">
                                                    <tr>
                                                       
                                                        
														<th>Lot Number</th>
                                                        <th>Purchase Type</th>
                                                        <th>Agent</th>
                                                        <th>Grader</th>
                                                        
                                                        <th>Weight</th>
                                                        <th>Count</th>
                                                        <th>Purchase Date</th>
                                                        <th>Posted Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
												<?php while($LateRow=$StmtTodayLots->fetch_array()){ ?>
                                                    <tr>
                                                       
                                                        
                                                       <td><?php echo  $LateRow['LotNumber'] ?></td>
                                                        
                                                        <td><?php echo  $LateRow['PurchaseType'] ?></td>
                                                        <td><?php echo  $LateRow['AgentName'] ?></td>
                                                        <td><?php echo  $LateRow['GraderName'] ?></td>
														<td><?php echo  $LateRow['PurchaseWeight'] ?></td>
														<td><?php echo  $LateRow['PurchaseCount'] ?></td>
                                                        <td><?php echo  date("d-m-y", strtotime($LateRow['PurchaseDate'])) ?></td>
                                                        <td><?php echo  date("d-m-y H:i:s", strtotime($LateRow['CreatedDateTime'])) ?></td>
                                                        
                                                    </tr>
													
												<?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->'	;




<script>

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


var AryPresentWeight=<?php echo json_encode($PresentWeight,true); ?>;
var AryPreviousWeight=<?php echo json_encode($PreviousWeight,true); ?>;
var AryPresentLots=<?php echo json_encode($PresentLots,true); ?>;
var AryPreviousLots=<?php echo json_encode($PreviousLots,true); ?>;
var AryDate=<?php echo json_encode($Date,true); ?>;


var options={series:[
{name:"Present Total Lots",type:"column",data: AryPresentLots},
{name:"Previous Month Weight",type:"line",data: AryPreviousWeight},
{name:"Present Month Weight",type:"line",data:AryPresentWeight},
{name:"Previous Total Lots",type:"line",data:AryPreviousLots}

], 
chart:{height:280,type:"line",toolbar:{show:!1}},
stroke:{width:[0,2,2,2],curve:"smooth"},
plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
colors:["#1cbb8c","#ff0000","#eeb902","#5664d2"],
labels: AryDate},
chart=new ApexCharts(document.querySelector("#line-column-chart"),options);
chart.render();

</script>