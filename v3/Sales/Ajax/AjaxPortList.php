<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$CountryID = $_POST['CountryID'];
$Query = "";
if ($CountryID != '') {
	$Query = "WHERE Country='$CountryID'";
}


$SqlPorts = "SELECT PortId,PortCode,SCP.Name as PortName,SCL.name as CountryName FROM `ScrmPortCities` as SCP
left join ScrmCountriesList as SCL on SCL.id=SCP.Country 
$Query ORDER BY PortId  desc";
$StmtPorts = $iconn->query($SqlPorts);


$data .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">
		
		
		<tr>
		<th>S.No</th>
		<th>Country</th>
		<th>Port</th>
		<th>Port Code</th>
		<th>Actions</th>
		</tr>
		</thead>
		
		<tbody>';



$S_No = 1;
while ($RowPorts = $StmtPorts->fetch_array()) {
	$data .= '<tr>
	<td >' . $S_No . '</td>
	<td >' . $RowPorts['CountryName'] . '</td>
	<td >' . $RowPorts['PortName'] . '</td>
	<td >' . $RowPorts['PortCode'] . '</td>
	<td ><a target="_blank" href="AddPort?Id=' . $RowPorts['PortId'] . '" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit PortsList" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a></td>
	</tr>';

	$S_No++;
}

echo $data .= '</tbody></table>';
