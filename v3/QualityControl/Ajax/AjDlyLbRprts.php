<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
$ClientConfigData = $_SESSION['ClientConfigData'];
$EnableTrackCodeInAbReport = $ClientConfigData['EnableTrackCodeInAbReport'];//anil

 $Type=$_POST['Type']; 
 $Year=$_POST['Year']; 
 $Month=$_POST['Month']; 
 $PlantId = $_POST['PlantId'];   
  
  $DateStart=$Year.'-'.$Month.'-01'; 
  $DateEnd=$Year.'-'.$Month.'-31  23:59:59'; 
  

 $DateFilter="BETWEEN '$DateStart' and '$DateEnd'"; 


 //$DateFilter=getDateFilter($Year,$Month);

 

  	$SqlCount="select distinct(AnalysisDate) from QcRmAbReport WHERE ReportDate $DateFilter"; 
  	if(!empty($PlantId)){
		$SqlCount .= " AND PlantId = '$PlantId' ";
  	}

//echo $SqlCount;exit;

$StmtCount=$iconn->query($SqlCount);
 ?>

<table  id="key-datatable" data-paging="false" class="table table-hover table-bordered" >										
		
<thead class="thead-light">

      <tr>
	  
	  
	 
        <th>Report ID</th>

        <th>Analysis Date</th>

        <th>Report Date</th>

		<th>Total Reports</th>

		<th>Day Total</th>
		
		<th>Night Total</th>
		<th >Actions</th>

      

      </tr>

    </thead>

	

	

    <tbody>

	

	<?php

	//echo $sql;

	$PlantIdSql = '';
	if(!empty($PlantId)){
		$PlantIdSql = " AND PlantId = '$PlantId' ";
  	}
   while($RowCount=$StmtCount->fetch_array()){

$AnalysisDate = $RowCount['AnalysisDate'];
	 $SqlTotalReports="select ReportDate,AbRepID,Shift,( SELECT count(*)FROM `QcRmAbReport` where    AnalysisDate='$AnalysisDate' $PlantIdSql) as TotalReports ,
(SELECT count(*)FROM `QcRmAbReport`  where `Shift`='DAY' and AnalysisDate='$AnalysisDate' $PlantIdSql) as DayTotal,
(SELECT count(*)FROM `QcRmAbReport`  where `Shift`='NIGHT' and  AnalysisDate='$AnalysisDate' $PlantIdSql) as NightTotal


 FROM `QcRmAbReport` where    AnalysisDate='$AnalysisDate'  $PlantIdSql";
 
 
 $StmtTotalReports=$iconn->query($SqlTotalReports);
 $RowTotalReports=$StmtTotalReports->fetch_array();
$ReportDate = $RowTotalReports['ReportDate'];

	?>
						 <tr>

        <td id="<?php  echo $RowTotalReports['AbRepID'];?>" > <?php echo $RowTotalReports['AbRepID']; ?></td>
		
		
        <td id="c_name<?php  echo $RowTotalReports['AbRepID'];?>"><?php echo  date("d-m-Y", strtotime($AnalysisDate)) ?></td>

		<td id="a_type<?php  echo $RowTotalReports['AbRepID'];?>"><?php echo date("d-m-Y", strtotime($ReportDate)) ?></td>

		<td id="p_number<?php  echo $RowTotalReports['AbRepID'];?>"><?php echo $RowTotalReports['TotalReports']; ?></td>

		<td id="address<?php  echo $RowTotalReports['AbRepID'];?>"><?php echo $RowTotalReports['DayTotal']; ?></td>
		<td id="address<?php  echo $RowTotalReports['AbRepID'];?>"><?php echo $RowTotalReports['NightTotal']; ?></td>

      <td> 
		<?php 
		$queryString = '';
		if(!empty($EnableTrackCodeInAbReport) && !empty($PlantId)){ 
			$queryString = '&PlantId='.$PlantId;
		 } ?>
	 <a target="_blank" 
	  href="RmAbReport.php?DOA=<?php echo date("d-m-Y", strtotime($AnalysisDate))?>&DOR=<?php echo  date("d-m-Y", strtotime($ReportDate));?>&Year=<?php echo  $Year.$queryString;?>"
	class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
	title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
    <a target="_blank" href="DailyLabReportsPrint.php?ReportDate=<?php echo $ReportDate?>&shift=<?php echo $RowTotalReports['Shift'].$queryString ;?>"
	class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
	title="" data-original-title="Add"><i class="mdi mdi-printer font-size-18"></i></a>               
						 
					
						 </td>		


        </tr>

	 
   <?php } ?>  

	 

     </tbody>

  </table>

