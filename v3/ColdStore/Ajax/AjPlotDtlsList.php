<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

    $YEAR=$_POST['YEAR'];
	$MONTH=$_POST['MONTH'];

	
	
	$sqlPlotData="SELECT * FROM `ProdPlotData` WHERE MONTH(PlotDate)='$MONTH' 
	AND YEAR(PlotDate)='$YEAR'";	
	$stmtPlotData=$iconn->query($sqlPlotData);
	
			

	 $data .='<table  id="key-datatable" data-paging="false" 
	 class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">
		
		
		<th>ID</th>
		<th>Plot Date</th>
		<th>HeadOn Pending</th>
		<th>Local HeadOn</th>
		<th>OutSide HeadOn</th>
		<th>Grading Wt</th>
		<th>Headless Wt</th>
		<th>Peeling Wt</th>
		<th>Total Wt</th>
		<th>Actions</th>
		
		</thead>
		
		<tbody>';
	 while($RowPlotData=$stmtPlotData->fetch_array()){

		
$TotalWt=($RowPlotData['PendingHOn']+$RowPlotData['OutsideHOn']+$RowPlotData['LocalHOn']);

			
			
				$data .='
				<tr>
		
		<td >'.$RowPlotData['PlotID'].'</td>
		<td >'.$RowPlotData['PlotDate'].'</td>
		<td >'.$RowPlotData['PendingHOn'].'</td>
		<td >'.$RowPlotData['LocalHOn'].'</td>
		<td >'.$RowPlotData['OutsideHOn'].'</td>
		<td >'.$RowPlotData['GradingWt'].'</td>
		<td >'.$RowPlotData['HLWt'].'</td>
		<td >'.$RowPlotData['PeelingWt'].'</td>
		
		<td >'.$TotalWt.'</td>
		<td > 
	  
	<a href="AddPlotDetails.php?ID='.$RowPlotData['PlotID'].'" 
	 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
	 title="Update Status" data-original-title="View">
	 <i class="mdi mdi-pencil font-size-18"></i></a>	 </td >
			</tr>';
			 
		}
					
		echo $data .='</tbody></table>';
	
		
	
	
?>