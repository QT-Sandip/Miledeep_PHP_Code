<?php
//session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


$uid = $_SESSION['r_id'];
$D_ID = $_SESSION["D_ID"];

$AccessType = $_SESSION["ACCESS_TYPE"];
$YEAR = $_POST['YEAR'];
$EXPORTER = $_POST['EXPORTER'];
$Buyer = $_POST['Buyer'];
$Country = $_POST['Country'];
$TYPE = $_POST['TYPE'];

$DateFilter = getFinancialYear($YEAR);

$_SESSION["SelectedYear"] = $_POST['YEAR'];

//
$Query = '';

/*if($YEAR!=" "){
	//$Query='and year(InvDate)='.$YEAR;
	$Query='and InvDate '.$DateFilter;
}
*/
if ($EXPORTER != " ") {
	$Query .= 'and POD.ExporterID in (' . $EXPORTER . ')';
}

$sql = "SELECT *,POD.PoID as PoID, name as CountryName,POD.ExporterID as ExporterID,
GROUP_CONCAT(SPDG.Grade) as Grade,
GROUP_CONCAT(APS.PackingStyle) as PackingStyle,
GROUP_CONCAT(SPVL.ShortName) as ShortName
 
 FROM ScrmPoData as POD 
left join ScrmPackingData as SPD on SPD.PIID=POD.PoID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
left join ScrmShipmentDispatchDetails as  SSDD on SSDD.PIID=POD.PoID
left join ScrmPoGradeData as SPDG on SPDG.InvId=POD.PoID
left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPDG.VarietyID
left join AdminPackingStyles as APS on APS.PCKID=SPDG.PackingStyleID
left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=POD.PoID
WHERE  AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null)
 $Query   group by POD.PoID ";

//echo $sql;exit;
$stmt = $iconn->query($sql);

$data = '<table  id="key-datatable" data-paging="false" class="table  wrap w-150" >
<thead class="thead-light">
	<tr>
	   <th>S.No</th>	
	   <th style="min-width: 200px">Buyer</th>
	   <th  style="min-width: 200px">Destination</th>
	   <th  style="min-width: 200px">Shipment</th>
	   <th  style="min-width: 200px">Po Number</th>	
	   <th  style="min-width: 200px">Brand</th>	
	   <th  style="min-width: 200px">Processor</th>
	   <th  style="min-width: 200px">Pack</th>	
	   <th  style="min-width: 200px">Form</th>
	   <th  style="min-width: 200px">Variety</th>			
	   <th  style="min-width: 200px">Grade</th>		
	   <th  style="min-width: 200px">Spec</th>		
	   <th  style="min-width: 200px">B.UP</th>
	   <th  style="min-width: 200px">Confirmation Date</th>
	   <th  style="min-width: 200px">Order Date</th>		
	   <th  style="min-width: 200px">Order Remarks</th>		
	   <th  style="min-width: 200px">Master Cases</th>		
	   <th  style="min-width: 200px">Inner Bags</th>		   		
	   <th  style="min-width: 200px">Priority</th>	  
       <th  style="min-width: 200px">Actions</th>
</tr>
</thead>
<tbody>';

$Sno = 1;
while ($row = $stmt->fetch_array()) {

	$LastUpdatedDate = date('d-m-Y', strtotime($row['LastUpdatedDate']));
	$PoDate = date('d-m-Y', strtotime($row['PoDate']));
	$InvoiceDate = date('d-m-Y', strtotime($row['InvDate']));

	if ($row['AvalMcs'] == '') {
		$AvalMcs = '0';
	} else {
		$AvalMcs = $row['AvalMcs'];
	}

	$ConsigneeName = mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
	$PoNo = mb_strimwidth($row['PoNo'], 0, 15, "...");

	$PoID = $row['PoID'];
	$Grade = $row['Grade'];
	$PackingStyle = $row['PackingStyle'];
	$ShortName = $row['ShortName'];
	
	$data .= '<tr> 
	<td> ' . $Sno . '</td>
	<td> ' . $ConsigneeName . '</td>
	<td></td>
	<td></td>
	<td> ' . $PoNo . '</td> 
	<td></td> 
	<td></td> 
	<td> ' . $PackingStyle . '</td>
	<td> </td>
	<td> ' . $ShortName . '</td>
	<td> ' . $Grade . '</td>
	<td></td>
	<td></td>
	<td> <input  type="date" class="form-control"  name="MaterialStatus" id="MaterialStatus' . $PoID . '" value="' . $row['MaterialStatus'] . '" placeholder="Enter " /></td>
	<td> <input  type="date" class="form-control"  name="MailSentDate" id="MailSentDate' . $PoID . '" value="' . $row['MailSentDate'] . '" placeholder="Enter " /></td>
	<td><input type="text" class="form-control" placeholder="Enter Remarks" /></td>
	<td> <input  type="text" class="form-control"  name="ApprovalDate" id="ApprovalDate" value="' . $row['ApprovalDate'] . '" placeholder="Enter" /></td>
	<td> <input  type="text" class="form-control"  name="OrderedDate" id="OrderedDate" value="' . $row['OrderedDate'] . '" placeholder="Enter" ></td>
	<td> 
		<select class="form-control select2">
			<option>Select Priority</option>
			<option>High</option>
			<option>Low</option>
		</select>
		<!-- <input  type="date" class="form-control"  name="ReceivedDate" id="ReceivedDate" value="' . $row['ReceivedDate'] . '"placeholder="Enter" />-->
	</td>
	<td> ';
	if ($AvalMcs == 0) {
		$data .= '	<button  id="BtnAddMcs"  value="' . $row["PoID"] . '" align="Center" class="btn btn-success btn-sm btn-flat">Add </button>';
	} else {
		$data .= '	<button  id="BtnAddMcs"  value="' . $row["PoID"] . '" align="Center" class="btn btn-success btn-sm btn-flat">Edit </button>';
	}
	$data .= '</td></tr>';
	$Sno++;

}
echo $data .= '</tbody></table>';

?>