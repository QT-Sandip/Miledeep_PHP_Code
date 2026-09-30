<?php

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

if (isset($_POST["type"]) && !empty($_POST["type"]) && $_POST['type'] == "update") {

	$type = $_POST["type"];

	$PoID = trim($_POST["PoID"]);
	$ListPrority = trim($_POST["ListPrority"]);

$oldPcData = legacyAuditFetchRowById('ScrmPoData', 'PoID', $PoID, 'Sales');
	$sqlUpdateScr = " update ScrmPoData set Priority = '" . $ListPrority . "' where PoID = '" . $PoID . "' ";
	$iconn->query($sqlUpdateScr);
	legacyAuditLogUpdateById('ScrmPoData', 'PoID', $PoID, $oldPcData, ['Priority' => $ListPrority], ['Remarks' => 'Order priority updated'], 'Sales');
	header('Content-Type: application/json');
	echo json_encode(["Error" => null, "Msg" => "Data Saved Successfully"]);
	exit;
}

$Header = 'ORDER`S LIST REPORT';
$Year = $_POST['Year'];
$Exporter = $_POST['Exporter'];
$Consignee = $_POST['Consignee'];
$Country = $_POST['Country'];
$Priority = $_POST['Priority'];
$drpProcessorList = $_POST['drpProcessorList'];

$DateFilter = getFinancialYear($Year);

$Query = '';
if ($Exporter != '') {
	$Query .= " and ExporterID in ($Exporter)";
}

if ($Consignee != "") {
	$Query .= ' and SPD.ConsigneeID=' . $Consignee;
}

if ($Country != "") {
	$Query .= '  and SPD.DestinationCountry=' . $Country;
}

if ($Priority != "") {
	$Query .= " and SPD.Priority='" . $Priority . "'";
}

if ($drpProcessorList != "") {
	//$Query.=" and SPD.ProductionPlantID ='".$drpProcessorList."'";
	$Query .= " AND FIND_IN_SET('$drpProcessorList', SPD.ProductionPlantID)";
}

$file_name = 'Orders List -' . $from_date . '-' . $to_date . '.xlsx';
$date_end = $to_date . ' 23:59:59';

$sql = "SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
WHERE  (SDB.TransportedDate ='' or SDB.TransportedDate is null) and SPD.Status not in (2,3,4)
$Query order by PoID desc";

//ECHO $sql; EXIT;

$stmt = $iconn->query($sql);
$RowData = $stmt->fetch_array();
$PoID = $RowData['PoID'];
$AryPoID = explode(',', $PoID);
$OrderCount = Count($AryPoID);

if ($PoID != '') {
	$AryData = getTableDate($PoID);
	echo $AryData['TableData'];
} else {
	echo 'No data available in table';
}
//}

function getTableDate($PoID)
{
	global $iconn, $Query;
	$sql = "SELECT *,PoID,PoNo,PODate,PreparedBy,Name,ShipByDate,Priority,
	sum(SPGD.Mcs) as Mcs,GROUP_CONCAT(SPGD.Grade) as Grade,
	GROUP_CONCAT(Distinct SPGD.Brand) as Brand FROM ScrmPoData as SPD
	LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
	LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
	LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
	WHERE    SPD.PoID  in($PoID) 
	GROUP by PoID order by PoID Asc";

	$stmt = $iconn->query($sql);

	$data = '<table  id="key-datatable" data-paging="false" 
	class="table  wrap w-100" >										

	<thead class="thead-light">
	<th style="min-width: 60px" align="center" class="sticky etaStatusCol1"  >S No.</th>  
	<th style="min-width: 250px;" align="center" class="sticky etaStatusCol1" >ORDER NO</th>
	<th style="min-width: 400px" align="center"  ><B>BUYER</B></th>					   
	<th style="min-width: 80px" align="center"  ><B>BRAND</B></th>	
	<th style="min-width: 140px" align="center"  ><B>VARIETY</B></th>
	<th style="min-width: 70px" align="center"  ><B>MCS</B></th>
	<th style="min-width: 120px" align="center"  ><B>PACK</B></th>			
	<th style="min-width: 180px" align="center"  ><B>GRADE</B></th>						   
	<th style="min-width: 150px" align="center"  ><B>SHIPMENT DATE AS PER PO</B></th>			
	<th style="min-width: 120px" align="center"  ><B>DESTINATION</B></th>	
	<th  style="min-width: 200px">Priority</th>	  
	<th  style="min-width: 60px">Actions</th>
	</thead>
	<tbody>';

	$Sno = 0;
	while ($row = $stmt->fetch_array()) {

		$LastUpdatedDate = date('d-m-Y', strtotime($row['LastUpdatedDate']));
		if ($row['AvalMcs'] == '') {
			$AvalMcs = '0';
		} else {
			$AvalMcs = $row['AvalMcs'];
		}

		$ConsigneeName = mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
		// $PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");
		$PoNo = $row['PoNo'];

		$ShipByDate = getFormatedDate($row['ShipByDate']);
		$PoDate = getFormatedDate($row['PoDate']);
		$ConsigneeName = $row['ConsigneeName'];

		$PoID = $row['PoID'];
		$Brand = $row['Brand'];

		$Destination = $row['Name'];
		$ShipByDate = $row['ShipByDate'];
		$Rcount = 0;
		$AryPoID[] = $PoID;
		$data .= '<tr> 
			<td class="sticky etaStatusCol1"> ' . ++$Sno . '</td>
			<td class="sticky etaStatusCol1"> ' . $PoNo . '-(' . $PoDate . ')</td> 
			<td> ' . $ConsigneeName . '</td>
			<td> ' . $Brand . '</td>';

		/*$SqlVariety="SELECT sum(Mcs) as Mcs,ShortName,
		GROUP_CONCAT(APS.PackingStyle) as PackingStyle,
		GROUP_CONCAT(SPDT.Grade,'-',SPDT.Mcs) as Grade	FROM `ScrmPoGradeData` SPDT lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID LEFT join ScrmPackingData SPD on SPDT.ID=SPD.POGradeId left Join ScrmPIVarietyList as SVL on 
		SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
		GROUP By SPDT.Variety";*/

		$SqlVariety = "SELECT sum(Mcs) as Mcs,GROUP_CONCAT(Distinct SVL.Species,'-',SVL.ShortName,'-',SVL.PreparationType) as ShortName,
			GROUP_CONCAT(Distinct APS.PackingStyle) as PackingStyle,
			GROUP_CONCAT(Distinct SPDT.Grade,'-',SPDT.Mcs) as Grade	FROM `ScrmPoGradeData` SPDT
			lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID 
			LEFT join ScrmPackingData SPD on SPDT.ID=SPD.POGradeId
			left Join ScrmPIVarietyList as SVL on SVL.VarietyID=SPDT.VarietyID 
			WHERE InvId='$PoID' GROUP By SPDT.InvId ";

		$Stmtvariety = $iconn->query($SqlVariety);
		$RowCount = mysqli_num_rows($Stmtvariety);

		if ($RowCount == 0) {

			$data .= '<td ></td>

					<td ></td>
					<td ></td>
					<td ></td>
					<td >' . getFormatedDatelines($ShipByDate) . '</td>
					<td >' . $Destination . '</td>
					</tr>';
		} else {
			while ($RowVariety = $Stmtvariety->fetch_array()) {

				if ($Rcount == 0) {
				} else {
					$data .= '
						<td ></td>
						<td ></td>
						<td ></td>
						<td ></td>';
				}
				$data .= '

					<td>' . $RowVariety['ShortName'] . '</td>
					<td>' . $RowVariety['Mcs'] . '</td>
					<td>' . $RowVariety['PackingStyle'] . '</td>
					<td>' . $RowVariety['Grade'] . '' . $RowVariety['GradeMcs'] . '</td>
					<!--<td>' . getFormatedDatelines($ShipByDate) . '</td>-->

					';

				if ($Rcount == 0) {
					$data .= '
						<td>' . getFormatedDatelines($ShipByDate) . '</td>
						<td >' . $Destination . '</td>';
				} else {
					$data .= '
						<td ></td>
						<td ></td>';
				}
				$Rcount++;
				$data .= '<td>                                                                      
						<select class="form-control drpSelect drpListPrority">
						<option value="">Select Priority</option>
						<option value="High" ' . ($row['Priority'] == "High" ? "selected" : "") . ' >High Priority</option>
						<option value="Low" ' . ($row['Priority'] == "Low" ? "selected" : "") . ' >Low Priority</option>
						<select>                                    
						</td>	  
						<td>
						<button data-poid="' . $row['PoID'] . '" data-pogradid="' . $item->POGradeId . '" align="Center" class="btn btn-success btn-sm btn-flat btnSavePackOrder"> Save </button>          
						</td>';
				$data .= ' </tr>';
			}
		}
	}
	$data .= '</tbody></table>';
	$AryData['TableData'] = $data;
	return $AryData;
}
?>
