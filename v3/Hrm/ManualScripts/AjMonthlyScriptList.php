<?php
//ini_set( 'display_errors', 1 );

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$SplitStaffAndWorkerSalarySheets = $ArrayClientConfigData['SplitStaffAndWorkerSalarySheets'];
$DeptID=   $_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$Year=     $_POST['Year'];
$Month=     $_POST['Month'];

$date = "$Year-$Month-01";
// Start of month
$startOfMonth = date('Y-m-01', strtotime($date));
// End of month
$endOfMonth = date('Y-m-t', strtotime($date));

if($SplitStaffAndWorkerSalarySheets){
	if($startOfMonth>='2025-01-01'){
		$ReportQuery="SELECT * FROM `HrmDailyScripts` where  Status=1 and ReportType=3 and  ReportID IN(10,11,12,13,14) ORDER by SortOrder Asc";	
	}else{
		$ReportQuery="SELECT * FROM `HrmDailyScripts` where  Status=1 and ReportType=3 and ReportID IN(4,5,9) ORDER by SortOrder Asc";
	}
}else{
	$ReportQuery="SELECT * FROM `HrmDailyScripts` where  Status=1 and ReportType=3 and ReportID IN(4,5,9) ORDER by SortOrder Asc";	
}

//$ReportQuery="SELECT * FROM `HrmDailyScripts` where  Status=1 and ReportType=3 and EffectiveDate>='$startOfMonth' ORDER by SortOrder Asc";   
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
	$QueryParms='DeptID='.$DeptID.'&SubDeptID='.$SubDeptID .'&Month='.$Month.'&Year='.$Year.'&ReportSubCategory='.$RowReport['ReportSubCategory'];
	$QueryParms=urlEncode64($QueryParms);
	
	?>
			
				<tr>

                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $RowReport['ReportName']; ?></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo date("F", mktime(0, 0, 0, $Month, 10)); ; ?></B></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $Year; ?></font></td>			
  
                <td  align="left" width="5%"><font size="'.$text.'">
				<!--<a  target="_blank" href="<?php //echo $RowReport['Path']. 
				//$RowReport['Url'].'?Month='.$Month.'&Year='.$Year .'&PsID='.$PsID ;?>"class="btn btn-success">Print</a>
				-->
				<?php 				
	                if (preg_match('/^15 Days Worker Salary Sheet/', $RowReport['ReportName'])) {
					    echo '<a target="_blank" href="' . $RowReport['Url'] . '?Q=' . $QueryParms . '" class="btn btn-success">Run</a>';
	                }else{
					    echo '<a target="_blank" href="' . $RowReport['Url'] . '?Q=' . $QueryParms . '" class="btn btn-success">Run</a>';
	                }
				?>

<!-- 				<a  target="_blank" href="<?php echo  
				$RowReport['Url'].'?Q='.$QueryParms;?>"class="btn btn-success">Run</a> -->
				</font></td>  
              
		</tr>


	<?php } ?>
	</tbody>
	</table>



	