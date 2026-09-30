<?php
	include($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	include($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
	$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
	$SeparateDeheadingDataV2AndV3 = $ArrayClientConfigData['SeparateDeheadingDataV2AndV3'];

	$Type = $_POST['Type'];
	$Month = $_POST['Month'];
	$MonthName = $_POST['Month'];
	if($Type == 1) {
		$Month = sprintf("%02d", $Month);
		$MonthName = date("F", mktime(0, 0, 0, $Month, 10));
	}
	$Year = $_POST['Year'];
	$PsID = $_POST['PsId'];
	$AccessType = $_SESSION['AccessType'];
	$DeptID = $_SESSION['DeptID'];

	if($DeptID == 10) {
		$ReportQuery = "SELECT * FROM `PreProcAvalReports` where  Status=1 order by ReportName Asc";
	} else {
		$ReportQuery = "SELECT * FROM `PreProcAvalReports` where  Status=1";
	}
	$ReportExec = $iconn->query($ReportQuery);
?>

<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

	<thead class="thead-light">

		<?php if($Type == 1) { ?>
			<th>Report Name</th>
			<th>Month</th>
			<th>Year</th>
			<th>Actions</th>
		<?php } else { ?>
			<th>Report Name</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Actions</th>
		<?php } ?>
	</thead>
	<tbody>
		<?php
			$ReportsArr = ['Grading Report','Deheading Report','Value Addition Report'];
			while($RowReport = $ReportExec->fetch_array()) {
			$QueryParmsV2 = $QueryParms = 'Month='.$Month.'&Year='.$Year.'&PsID='.$PsID.'&Type='.$Type;
			$QueryParms = urlEncode64($QueryParms);
			$QueryParmsV2 = urlEncode64($QueryParmsV2.'&version=v2');
		?>
		<tr>
			<td><b><?php echo $RowReport['ReportName']; ?></b></td>
			<td><b><?php echo $MonthName; ?></b></td>
			<td><b><?php echo $Year; ?></b></td>
			<td>
				<?php 
				if($SeparateDeheadingDataV2AndV3 == 1 && in_array(trim($RowReport['ReportName']),$ReportsArr)){?>
					<a target="_blank" href="<?php echo $_SESSION["SideBarPath"].$RowReport['Path'].
					$RowReport['Url'].'?Q='.$QueryParmsV2; ?>" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
					title="V2" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
				<?php } ?>
				<!--<a  target="_blank" href="<?php echo $BaseUrl.$RowReport['Path'].
					$RowReport['Url'].'?Q='.$QueryParms; ?>"class="btn btn-success">Print</a>-->
				<a target="_blank" href="<?php echo $_SESSION["SideBarPath"].$RowReport['Path'].
					$RowReport['Url'].'?Q='.$QueryParms; ?>" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
					title="<?php echo ($SeparateDeheadingDataV2AndV3)? 'V3' :''; ?>" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
				
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>