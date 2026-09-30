<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$DeptID=   $_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$Year=     $_POST['Year'];
$Month=     $_POST['Month'];
$Type=     $_POST['Type'];
$BranchID=     $_POST['BranchID']; 
$Category=     $_POST['Category']; 
$EmpStatus= $_POST['EmpStatus'];
$EmpCategory= $_POST['EmpCategory'];
$EmpSubCategory= $_POST['EmpSubCategory'];
$MonthSegment= $_POST['MonthSegment'];
/*
$year = date('Y', strtotime($Date));

$month = date('F', strtotime($Date));
*/

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$SplitStaffAndWorkerSalarySheets = $ArrayClientConfigData['SplitStaffAndWorkerSalarySheets'];

$date = "$Year-$Month-01";
// Start of month
$startOfMonth = date('Y-m-01', strtotime($date));
// End of month
$endOfMonth = date('Y-m-t', strtotime($date));

if($Type == 5){

 $ReportQuery="SELECT * FROM `HrmAvalReports` where  ReportType= 5 and Status=1 ORDER by SortOrder Asc";
}else{
	
$ReportQuery="SELECT * FROM `HrmAvalReports` where  ReportType= 3 and Status=1 ORDER by SortOrder Asc";	
}
$ReportExec=$iconn->query($ReportQuery);
?>

<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

                  <th align="CENTER">Report Name</th>
                  <th align="CENTER">Month</th>
				  <th align="CENTER">Year</th>
				  <th align="CENTER">Actions</th>
                </thead>
                <tbody>

			   

<?php	
while($RowReport=$ReportExec->fetch_array()){
	if($Type == 5){

 $QueryParms='DeptID='.$DeptID.'&SubDeptID='.$SubDeptID .'&Month='.$Month.'&Year='.$Year.'&BranchID='.$BranchID.'&Category='.$Category.'&EmpStatus='.$EmpStatus;
}else{

	$MonthSegmentData=getMonthSegments($Month,$Year,$MonthSegment);
	 $QueryParms='DeptID='.$DeptID.'&SubDeptID='.$SubDeptID .'&Month='.$Month.'&Year='.$Year.'&EmpCategory='.$EmpCategory.'&EmpSubCategory='.$EmpSubCategory.'&MonthSegment='.$MonthSegment.'&StartDate=&EndDate='.$MonthSegmentData['EndDate'];

}
	$QueryParms=urlEncode64($QueryParms);
	if($SplitStaffAndWorkerSalarySheets && $startOfMonth>='2025-01-01'){
		if($RowReport['Url']=='Reports/MnthlySalaryReport.php'){
			$RowReport['Url']='Reports/MnthlySalaryReportNew.php';
		}
	}
	
	?>
			
				<tr>

                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $RowReport['ReportName']; ?></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo date("F", mktime(0, 0, 0, $Month, 10)); ; ?></B></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $Year; ?></font></td>			
  
                <td  align="left" width="5%"><font size="'.$text.'">
				<!--<a  target="_blank" href="<?php //echo $RowReport['Path']. 
				//$RowReport['Url'].'?Month='.$Month.'&Year='.$Year .'&PsID='.$PsID ;?>"class="btn btn-success">Print</a>
				-->
				<a  target="_blank" href="<?php echo $RowReport['Path']. 
				$RowReport['Url'].'?Q='.$QueryParms;?>"class="btn btn-success">Print</a>
				</font></td>  
              
		</tr>


	<?php } ?>
	</tbody>
	</table>



	