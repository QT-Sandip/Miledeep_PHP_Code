<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$TodayDate=$_GET['TodayDate'];
 $Url=$_SESSION["SideBarPath"];


if(!$TodayDate ==''){
//$PresentMonth = date('F', strtotime($TodayDate));
$PresentMonth = date('m', strtotime($TodayDate));
$PresentYear = date('Y', strtotime($TodayDate));
}else{
$TodayDate=date('Y-m-d');
$PresentMonth=date('m');
$PresentYear=date('Y');
}

if($_SESSION['UserID'] == '430' || $_SESSION['UserID'] == '441'){
    header('location: ATimeCard');
}
//$TodayDate='2021-07-27';
/*********************To Set Shift Data ****************************/

 $SqlHrmHolidays="SELECT EmpName, DepartmentName, 
AttID, EmpList.BioMetricID, Date, TimeIn, TimeOut,GraceTime,ShiftDtl.ShiftID, 
TIMESTAMPDIFF(MINUTE,StartTime,AttLog.TimeIn) as LateDiff, OnTime,StartTime,ShiftName,
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
(SELECT MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) as Diff 
FROM HrmDailyAttendnessLog as AttLog LEFT JOIN HrmEmployeeDetails as EmpList 
ON EmpList.BioMetricID=AttLog.BioMetricID LEFT JOIN HrmDepartments as Dept 
ON EmpList.DeptID=Dept.DeptID LEFT JOIN HrmShiftDetails as ShiftDtl 
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))= 
(SELECT MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) 
 where AttLog.Month=$PresentMonth and AttLog.Year=$PresentYear and AttLog.Shift='0' and EmpList.EmpStatus=1 ORDER BY AttLog.Date DESC, AttLog.TimeIn DESC";

$StmtHrmHolidays=$iconn->query($SqlHrmHolidays);

while($DataRow=$StmtHrmHolidays->fetch_array()){
	
	$AttId=$DataRow['AttID'];
	$Shift=$DataRow['ShiftID'];
 if($DataRow['LateDiff'] > $DataRow['GraceTime']){
	$OnTime	=0; 
	}else{
	$OnTime	=1;
	}	
	//$OldAtt = legacyAuditFetchRowById('HrmDailyAttendnessLog', 'AttId', $AttId, 'Hrm');
	$SqlAtt="UPDATE `HrmDailyAttendnessLog` set `OnTime`='$OnTime',`Shift`='$Shift' WHERE AttId='$AttId'";	
	$StmtAtt=$iconn->query($SqlAtt);
	if($StmtAtt){
		//legacyAuditLogUpdateById('HrmDailyAttendnessLog', 'AttId', $AttId, $OldAtt, null, array(), 'Hrm');
	}				
}

//exit;
/*********************To Set Shift Data ****************************/


$SqlAttDetails="SELECT count(*) as PresentCount,
(SELECT count(*) as InTime FROM HrmDailyAttendnessLog WHERE Date=AttLog.Date and OnTime=1 ) as OnTime,
(SELECT Count(*) from HrmEmployeeDetails WHERE EmpStatus=1) as EmpCount
FROM HrmDailyAttendnessLog  as AttLog WHERE Date='$TodayDate'"; 
$StmtAttDetails=$iconn->query($SqlAttDetails);
$AttDataRow=$StmtAttDetails->fetch_array();

$PresentCount=$AttDataRow['PresentCount'];
$OnTime=$AttDataRow['OnTime'];
$EmpCount=$AttDataRow['EmpCount'];


	$SqlShiftDetails="SELECT count(*) as PresentCount,ShiftName,ShiftColor 
	FROM `HrmDailyAttendnessLog`  as AttLog 
	left join HrmShiftDetails AS SftDtls on SftDtls.ShiftID=AttLog.Shift
	WHERE Date='$TodayDate' GROUP by AttLog.Shift";
$StmtShiftDetails=$iconn->query($SqlShiftDetails);
while($AttShiftRow=$StmtShiftDetails->fetch_array()){
$AttShiftRow['Color']= $AttShiftRow['ShiftColor'];//sprintf('#%06X', mt_rand(0, 0xFFFFFF));
$Series[]=$AttShiftRow['PresentCount']+0;
$Lables[]=$AttShiftRow['ShiftName'];
$Colors[]=$AttShiftRow['Color'];

$AryShiftDtls[]=$AttShiftRow;
	
}


$SqlBirthDays="SELECT * FROM `HrmEmployeeDetails` as EmpDtls
left join HrmEmpPersonalDetails as PrsnlDtls on EmpDtls.EmpID=PrsnlDtls.EmpID
WHERE EmpStatus=1 AND
  DATE_ADD(DateOfBirth, 
                INTERVAL YEAR(CURDATE())-YEAR(DateOfBirth)
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(DateOfBirth),1,0)
                
               
                YEAR)BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)";
$StmtBirthDays=$iconn->query($SqlBirthDays);


$SqlLeaves="SELECT HED.BioMetricID,Reason,EmpName,StartDate,EndDate FROM `HrmLeaveRegister` HLR LEFT 
JOIN HrmEmployeeDetails HED on  HLR.EmpID=HED.EmpID WHERE 
'$TodayDate' BETWEEN StartDate AND EndDate;";
$StmtLeaves=$iconn->query($SqlLeaves);

$SqlOnDuty="SELECT BioMetricID,Reason,EmpName,StartDate,EndDate FROM `HrmOnDutyRegister` HLR LEFT 
JOIN HrmEmployeeDetails HED on  HLR.EmpID=HED.EmpID WHERE '$TodayDate' BETWEEN StartDate AND EndDate;";
$StmtOnDuty=$iconn->query($SqlOnDuty);


  $SqlLate="SELECT AttLog.BioMetricID, EmpDtls.EmpID,
EmpName,DepartmentName,Designation,TimeIn,TimeOut,TIMESTAMPDIFF(MINUTE,StartTime,AttLog.TimeIn) as LateDiff
from HrmEmployeeDetails as EmpDtls
LEFT JOIN HrmDesignations  as DsgnDtls on EmpDtls.DesigID=DsgnDtls.DesigID 
LEFT JOIN HrmDepartments  as DeptDtls on EmpDtls.DeptID=DeptDtls.DeptID 
LEFT JOIN HrmDailyAttendnessLog  as AttLog on EmpDtls.BioMetricID=AttLog.BioMetricID 
LEFT JOIN HrmShiftDetails  as ShiftDtl on ShiftDtl.ShiftID=AttLog.Shift 
WHERE Date='$TodayDate' and AttLog.OnTime=0";
$StmtLate=$iconn->query($SqlLate);


$DateStart = date('Y-m');
$DateEnd=$DateStart.'-31 23:59:59';
$DateStart=$DateStart.'-01';



   $SqlMonthlyAtt="SELECT Date,count(*) as PresentCount,
(SELECT count(*) as InTime FROM HrmDailyAttendnessLog WHERE Date=AttLog.Date and OnTime=1 ) as OnTime,
(SELECT Count(*) from HrmEmployeeDetails WHERE EmpStatus=1 and DateOfCreation<AttLog.Date) as EmpCount 
FROM HrmDailyAttendnessLog as AttLog 
WHERE Date BETWEEN '$DateStart' and '$DateEnd'
GROUP BY Date";

//echo $SqlMonthlyAtt;

 //exit;

$StmtMonAtt=$iconn->query($SqlMonthlyAtt);


while($MonAttRow=$StmtMonAtt->fetch_array()){
	$Present[]=$MonAttRow['PresentCount'];
	$Absent[]=round($MonAttRow['EmpCount']) - round($MonAttRow['PresentCount']);
	$OnTimeData[]=$MonAttRow['OnTime'];
	$Total[]=$MonAttRow['EmpCount'];
	$Date[]=$MonAttRow['Date'];
}

?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/Jlsf/User.css"  rel="stylesheet" type="text/css" />

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
										
										
										<div class="form-group  col-lg-3">
				
                    <input class="form-control" type="date"
					placeholder="Select Date" value="<?php echo $TodayDate  ?>"  max="<?php echo date("Y-m-d") ?>"
					id="SelectedDate" name="SelectedDate" >                 
                  </div>

												
												<div align="center" class="col-lg-3">
                                                   
                                                       <button id="refresh" align="center" type="button" class="btn btn-primary waves-effect waves-light">
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
                                                        <p class="text-truncate font-size-14 mb-2">Present</p>
                                                        <h4 class="mb-0"><?php echo $PresentCount ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-stack-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body border-top py-3">
                                                <div class="text-truncate">
                                                    <span class="badge badge-soft-success font-size-11"><i class="mdi mdi-menu-up"> </i> </span>
                                                    <span class="text-muted ml-2">Today Present</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Absent</p>
                                                        <h4 class="mb-0"><?php echo ($EmpCount- $PresentCount) ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body border-top py-3">
                                                <div class="text-truncate">
                                                    <span class="badge badge-soft-success font-size-11"><i class="mdi mdi-menu-up"> </i>  </span>
                                                    <span class="text-muted ml-2">Today Absents</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">OnTime</p>
                                                        <h4 class="mb-0"><?php echo $OnTime ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body border-top py-3">
                                                <div class="text-truncate">
                                                    <span class="badge badge-soft-success font-size-11"><i class="mdi mdi-menu-up"> </i> </span>
                                                    <span class="text-muted ml-2">Today On Time Commings</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="card">
                                    <div class="card-body">
                                       <!-- <div class="float-right d-none d-md-inline-block">
                                            <div class="btn-group mb-2">
                                                <button type="button" class="btn btn-sm btn-light">Today</button>
                                                <button type="button" class="btn btn-sm btn-light active">Weekly</button>
                                                <button type="button" class="btn btn-sm btn-light">Monthly</button>
                                            </div>
                                        </div> -->
                                        <h4 class="card-title mb-4">Monthly Attendance Analytics</h4>
                                        <div>
                                            <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                        </div>
                                    </div>

                                    <div class="card-body border-top text-center">
                                        <div class="row">
                                           

                                            <div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate"><i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>Absents</p>
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
                                                    <p class="mb-2 text-muted text-truncate"><i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>Leaves</p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"></h5>
                                                        <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div><div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate"><i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>OnDuty</p>
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
                                        <!--<div class="float-right">
                                            <select class="custom-select custom-select-sm">
                                                <option selected>Apr</option>
                                                <option value="1">Mar</option>
                                                <option value="2">Feb</option>
                                                <option value="3">Jan</option>
                                            </select>
                                        </div>-->
                                        <h4 class="card-title mb-4">ShiftWise Present Details</h4>

                                        <div id="donut-chart" class="apex-charts"></div>

                                        <div class="row">
                                            <?php foreach($AryShiftDtls as $ShiftRow){ ?>
												<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													class="mdi mdi-circle font-size-10 mr-1" 
													style="color: <?php echo $ShiftRow['Color'] ?>;">
													</i> <?php echo $ShiftRow['ShiftName'] ?></p>
                                                    <h5><?php echo $ShiftRow['PresentCount'] ?></h5>
                                                </div>
                                            </div>
										<?php	} ?>
											
											
                                           
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        

                                        <h4 class="card-title mb-4">On Time Vs Late </h4>
                                        <div class="text-center">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div>
                                                        <div class="mb-3">
                                                            <div id="radialchart-1" class="apex-charts"></div>
                                                        </div>

                                                        <p class="text-muted text-truncate mb-2">Late Comming</p>
                                                        <h5 class="mb-0"><?php echo $PresentCount -$OnTime ?></h5>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="mt-5 mt-sm-0">
                                                        <div class="mb-3">
                                                            <div id="radialchart-2" class="apex-charts"></div>
                                                        </div>

                                                        <p class="text-muted text-truncate mb-2">On Time</p>
                                                        <h5 class="mb-0"><?php echo $OnTime ?></h5>
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
                                        

                                        <h4 class="card-title mb-4">Today BirthDay</h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($BirthRow=$StmtBirthDays->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                            <i class="ri-edit-2-fill"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13"><?php echo date("d-M", strtotime($BirthRow['DateOfBirth'])); ?>
															<small class="text-muted">08:00 am</small></h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo $BirthRow['EmpName'] .'-'.$BirthRow['BioMetricID'] ?> 
															BirthDay On  
															<?php echo date("d-M", strtotime($BirthRow['DateOfBirth'])); ?></p>
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
                                        

                                        <h4 class="card-title mb-4">Today Leaves</h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($LeavesRow=$StmtLeaves->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                            <i class="ri-edit-2-fill"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13"><?php echo date("d-M", strtotime($LeavesRow['StartDate'])); ?>
															<small class="text-muted">08:00 am</small></h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo $LeavesRow['EmpName'].'-'.$LeavesRow['BioMetricID'] ?> 
															Leave End On  
															<?php echo date("d-M", strtotime($LeavesRow['EndDate'])); ?></p>
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
                                        

                                        <h4 class="card-title mb-4">Today OnDuty</h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($OnDutyRow=$StmtOnDuty->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                            <i class="ri-edit-2-fill"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13"><?php echo date("d-M", strtotime($OnDutyRow['StartDate'])); ?>
															<small class="text-muted">08:00 am</small></h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo $OnDutyRow['EmpName'].'-'.$OnDutyRow['BioMetricID'] ?> 
															BirthDay On  
															<?php echo date("d-M", strtotime($OnDutyRow['EndDate'])); ?></p>
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
                                        

                                        <h4 class="card-title mb-4">Late Commings</h4>

                                        <div class="table-responsive">
                                           <table  id="key-datatable" data-paging="false" 
										   class="table dt-responsive nowrap  w-100" >										
	
                                                <thead class="thead-light">
                                                    <tr>
                                                      <!--  <th style="width: 20px;">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="ordercheck">
                                                                <label class="custom-control-label" for="ordercheck">&nbsp;</label>
                                                            </div>
                                                        </th>  -->
                                                        
														<th>Employee Id</th>
                                                        <th>Employee Name</th>
                                                        <th>Department</th>
                                                        <th>Designation</th>
														<th>Late(Min)</th>
                                                        <th>Time In</th>
                                                        <th>Time Out</th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
												<?php while($LateRow=$StmtLate->fetch_array()){ ?>
                                                    <tr>
                                                      <!--  <td>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="ordercheck1">
                                                                <label class="custom-control-label" for="ordercheck1">&nbsp;</label>
                                                            </div>
                                                        </td> -->
                                                      <!-- <td><?php //echo  $LateRow['BioMetricID'] ?></td>-->
                                                       <td><a target="_blank" href="TimeCardPrint?Month=<?php  echo $PresentMonth;?>&Year=<?php  echo $PresentYear;?>&EmpID=<?php  echo $LateRow['EmpID'];?>" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><?php echo  $LateRow['BioMetricID'] ?> </a></td>
                                                        <td><?php echo  $LateRow['EmpName'] ?></td>
                                                        <td><?php echo  $LateRow['DepartmentName'] ?></td>
                                                        <td><?php echo  $LateRow['Designation'] ?></td>
														<td><?php echo  $LateRow['LateDiff'] ?></td>
                                                        <td><?php echo  $LateRow['TimeIn'] ?></td>
                                                        <td><?php echo  $LateRow['TimeOut'] ?></td>
                                                        
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
              $(document).on("click", ".div-old-menu .nav-link.dropdown-toggle", function () {
                     if($(this).hasClass("menu_active")) {
                            $(this).removeClass("menu_active").css("color", "#FFFFFF");
                            $(this).closest("li.nav-item").removeClass("show");
                            $(this).closest("li.nav-item").find("ul.dropdown-menu, div.dropdown-menu").removeClass("show");    
                     }else {
                            $(this).addClass("menu_active").css("color", "#FFFFFF");
                            $(this).closest("li.nav-item").addClass("show");
                            $(this).closest("li.nav-item").find("ul.dropdown-menu, div.dropdown-menu").addClass("show");    
                     }
              });                     
        </script>		
		
<script>
$(document).on('click','#refresh',function(e){
	
     var SelectedDate =$('#SelectedDate').val(); 

	 	 var Url='<?php echo $Url ?>'+ "/Hrm/index?TodayDate="+SelectedDate;
		window.open(Url,"_self");


 });

</script>
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

var AryPresent=<?php echo json_encode($Present,true); ?>;
var AryAbsent=<?php echo json_encode($Absent,true); ?>;
var AryOnTime=<?php echo json_encode($OnTimeData,true); ?>;
var AryTotal=<?php echo json_encode($Total,true); ?>;
var AryDate=<?php echo json_encode($Date,true); ?>;


var options={series:[
{name:"Present",type:"column",data:AryPresent},
{name:"Absent",type:"column",data: AryAbsent},
{name:"OnTime",type:"column",data: AryOnTime},


  

], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1}},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
colors:["#5664d2","#eeb902","#ff0000","#1cbb8c"],
labels: AryDate},
chart=new ApexCharts(document.querySelector("#line-column-chart"),options);
chart.render();


$(document).ready(function(){
	showData();
});

function showData(){
			
					//$("#EmpList").html(data);
 
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

    </body>
</html>
