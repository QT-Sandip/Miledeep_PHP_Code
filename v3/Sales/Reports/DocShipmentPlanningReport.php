<style>
	table tr td {

		font-size: 16px;
		text-align: left;
	}

	table tr th {

		font-size: 13px;
		text-align: left;
		background: white;
		position: sticky;
		top: 0;
	}
</style>
<?php

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");
$Header = 'ORDER`S LIST REPORT';



$Year = $_GET['Year'];
$Exporter = $_GET['Exporter'];

/*if($Year==''){
	echo "Unable To Parse Year";
	exit;	
 }*/

$DateFilter = getFinancialYear($Year);

$Query = '';
if ($Exporter != '') {
	$Query .= "and ExporterID in ($Exporter)";
}

/*
	
	$SqlExporter="SELECT * FROM $TblAdminExporterDetails where ExpID=$Exporter"; 
				  $StmtExporter=$iconn->query($SqlExporter);

			 $RowExporter= $StmtExporter->fetch_array();	
			 $ExporterName=$RowExporter['ExpName'];
			 
			 */



$file_name = 'Orders List -' . $from_date . '-' . $to_date . '.xlsx';

$date_end = $to_date . ' 23:59:59';

$sql = "SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
WHERE AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) 
$Query order by PoID desc";

$stmt = $iconn->query($sql);
$RowData = $stmt->fetch_array();
$PoID = $RowData['PoID'];
$AryPoID = explode(',', $PoID);
$OrderCount = Count($AryPoID);
echo '<h4><b>' . $OrderCount . ' -  Order Found </b> </h4>';

$SqlGroup = "SELECT ExpID 
		FROM `AdminExporterDetails` ORDER BY ExpID";
$StmtGroup = $iconn->query($SqlGroup);

while ($RowData = $StmtGroup->fetch_array()) {

	// echo json_encode($RowData['ExpID']);
	// exit;

	$AryData = getTableDate($RowData['ExpID'], $PoID);

	if ($AryData['RowCount'] > 0) {
		// echo '<h3>' . $RowData['ShortName']  . '</h3>';
		echo '<br>';
		echo $AryData['TableData'];
	}
}


function getTableDate($ExporterID, $PoID)
{
	global $iconn, $Query;
	$sql = "SELECT *,PoID,PoNo,PODate,PreparedBy,Name,ShipByDate,
	sum(SPGD.Mcs) as Mcs,GROUP_CONCAT(SPGD.Grade) as Grade FROM ScrmPoData as SPD
	LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
	LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
	LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
	WHERE   SPD.ExporterID = ($ExporterID)
	and SPD.PoID  in($PoID) 
	GROUP by PoID order by ShipByDate asc";
	// GROUP by PoID order by PoID Asc";
	$stmt = $iconn->query($sql);


	$data = '
		<table border="1" cellspacing="0" cellpadding="3">											
			<thead class="thead-light">
				<th  align="center"   >S No.</th>              
				<th  align="center"  >REPORT ID</th>
				<th  align="center" style="min-width:150px;" >Invoice NO</th>
				<th  align="center" style="min-width:250px;" >ORDER NO</th>
				<th  align="center" style="min-width:200px;" ><B> BUYER NAME</B></th>					   
				<!-- <th  align="center"  ><B> BUYER AGENT</B></th>					   
				<th  align="center"  ><B> PLANT</B></th>-->					   
				<th  align="center" style="min-width:200px;" ><B>BRAND</B></th>	
				<th  align="center" style="min-width:150px;" ><B>VARIETY</B></th>
				<th  align="center" style="min-width:150px;" ><B>TYPE</B></th>
            	<th  align="center" style="min-width:100px;" ><B>MASTER CARTONS</B></th>
				<th  align="center" style="min-width:150px;" ><B>PACK</B></th>			
				<th  align="center" style="min-width:120px;" ><B>GRADE</B></th>						   
				<!--<th  align="center"  ><B>PACKAGING READY</B></th>				   
				<th  align="center"  ><B>CODE ISSUE</B></th>			
				<th  align="center"  ><B>LAB SAMPLE</B></th>	 		
				<th  align="center"  ><B>QTY</B></th>			
				<th  align="center"  ><B>MAJOR GRADE</B></th>	-->			
				<th  align="center" style="min-width:120px;" ><B>SHIPMENT DATE AS PER PO</B></th>			
				<th  align="center"  ><B>DESTINATION</B></th>			
				<!-- <th  align="center"  ><B>DATA ENTERED BY</B></th> -->
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
		$PoNo = mb_strimwidth($row['PoNo'], 0, 20, "...");

		$ShipByDate = getFormatedDate($row['ShipByDate']);
		$PoDate = getFormatedDate($row['PoDate']);

		$InvNum = $row['InvNumFull'];
		$PoID = $row['PoID'];
		$BuyerAgent = $row['PoID'];
		$Plant = $row['PoID'];
		// $Brand = $row['Brand'];
		$Grade = $row['Grade'];
		$PreparedBy = $row['PreparedBy'];
		$Destination = $row['Name'];
		$ShipByDate = $row['ShipByDate'];
		$Rcount = 0;
		$AryPoID[] = $PoID;
		$data .= '<tr> 
		<td> ' . ++$Sno . '</td>		
		<td> ' . $PoID . '</td> 
		<td> ' . $InvNum . '</td> 
		<td> ' . $PoNo . '-(' . $PoDate . ')</td> 
		<td> ' . $ConsigneeName . '</td>
		<!--<td> ' . $BuyerAgent . '</td>
		<td> ' . $Plant . '</td>
		<td> ' . $Brand . '</td>-->
		';

		// $SqlVariety = "SELECT sum(Mcs) as Mcs,ShortName,
		// 		 GROUP_CONCAT(APS.PackingStyle) as PackingStyle,SCI.IGST,
		// 		GROUP_CONCAT(SPDT.Grade,'-',SPDT.Mcs) as Grade	FROM `ScrmPoGradeData` SPDT 
		// 		lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID 
		// 		LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=SPDT.InvID
		// 		left Join ScrmPIVarietyList as SVL on 
		// 		SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
		// 		and SPDT.VarietyID in ($VarietyID) GROUP By SPDT.VarietyID ORDER BY SPDT.VarietyID";
		$SqlVariety = "SELECT GROUP_CONCAT(Brand) as Brand, GROUP_CONCAT(Mcs) as Mcs, GROUP_CONCAT(ShortName) as ShortName,
				 GROUP_CONCAT(APS.PackingStyle) as PackingStyle,
				GROUP_CONCAT(SPDT.Grade) as Grade, GROUP_CONCAT(SVL.TypeProduct) as TypeProduct	FROM `ScrmPoGradeData` SPDT 
				lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID 
				left Join ScrmPIVarietyList as SVL on 
				SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
				 ORDER BY SPDT.VarietyID";
		$Stmtvariety = $iconn->query($SqlVariety);
		$RowCount = mysqli_num_rows($Stmtvariety);
		if ($RowCount == 0) {

			$data .= '<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				<td ></td>
				</tr>';
		}

		while ($RowVariety = $Stmtvariety->fetch_array()) {

			if ($Rcount == 0) {
			} else {
				$data .= '
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>';
			}

			// $data .= '
			// 	 <td>' . $RowVariety['ShortName'] . '</td>
			// 	 <td>' . $RowVariety['TypeProduct'] . '</td>
			// 	 <td>' . $RowVariety['Mcs'] . '</td>
			// 	 <td>' . $RowVariety['PackingStyle'] . '</td>
			// 	 <td>' . $RowVariety['Grade'] . '' . $RowVariety['GradeMcs'] . '</td>
			// 	 <td>' . getFormatedDate($ShipByDate) . '</td>
			// 	';

			// $data .= '
			// 	<td>' . $RowVariety['ShortName'] . '</td>';
				// <td>' . $RowVariety['TypeProduct'] . '</td>';
				if (!empty($RowVariety['Brand'])) {
					$brandValues = explode(',', $RowVariety['Brand']);
	
					if (count($brandValues) === 1) {
						$data .= '<td>' . trim($brandValues[0]) . '</td>';
					} else {
						$data .= '<td style="padding: 0;">
									<table border="0" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
									<tbody>';
						$lastIndex = count($brandValues) - 1;
						foreach ($brandValues as $index => $Brand) {
							$borderStyle = $index === $lastIndex ? '' : 'border-bottom: 2px solid #808080;';
							$data .=
								'<tr>
									<td style="' . $borderStyle . ' padding: 5px;">' . trim($Brand) . '</td>
								</tr>';
						}
						$data .= '</tbody></table></td>';
					}
				}
				if (!empty($RowVariety['ShortName'])) {
					$shortNameValues = explode(',', $RowVariety['ShortName']);
	
					if (count($shortNameValues) === 1) {
						$data .= '<td>' . trim($shortNameValues[0]) . '</td>';
					} else {
						$data .= '<td style="padding: 0;">
									<table border="0" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
									<tbody>';
						$lastIndex = count($shortNameValues) - 1;
						foreach ($shortNameValues as $index => $ShortName) {
							$borderStyle = $index === $lastIndex ? '' : 'border-bottom: 2px solid #808080;';
							$data .=
								'<tr>
									<td style="' . $borderStyle . ' padding: 5px;">' . trim($ShortName) . '</td>
								</tr>';
						}
						$data .= '</tbody></table></td>';
					}
				}
				if (!empty($RowVariety['TypeProduct'])) {
				$typeProductValues = explode(',', $RowVariety['TypeProduct']);

				if (count($typeProductValues) === 1) {
					$data .= '<td>' . trim($typeProductValues[0]) . '</td>';
				} else {
					$data .= '<td style="padding: 0;">
								<table border="0" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
								<tbody>';
					$lastIndex = count($typeProductValues) - 1;
					foreach ($typeProductValues as $index => $TypeProduct) {
						$borderStyle = $index === $lastIndex ? '' : 'border-bottom: 2px solid #808080;';
						$data .=
							'<tr>
								<td style="' . $borderStyle . ' padding: 5px;">' . trim($TypeProduct) . '</td>
							</tr>';
					}
					$data .= '</tbody></table></td>';
				}
			}
			if (!empty($RowVariety['Mcs'])) {
				$mcsValues = explode(',', $RowVariety['Mcs']);

				if (count($mcsValues) === 1) {
					$data .= '<td>' . trim($mcsValues[0]) . '</td>';
				} else {
					$data .= '<td style="padding: 0;">
								<table border="0" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
								<tbody>';
					$lastIndex = count($mcsValues) - 1;
					foreach ($mcsValues as $index => $mcs) {
						$borderStyle = $index === $lastIndex ? '' : 'border-bottom: 2px solid #808080;';
						$data .=
							'<tr>
								<td style="' . $borderStyle . ' padding: 5px;">' . trim($mcs) . '</td>
							</tr>';
					}
					$data .= '</tbody></table></td>';
				}
			}
			if (!empty($RowVariety['PackingStyle'])) {
				$packingStyleValues = explode(',', $RowVariety['PackingStyle']);

				if (count($packingStyleValues) === 1) {
					$data .= '<td>' . trim($packingStyleValues[0]) . '</td>';
				} else {
					$data .= '<td style="padding: 0;">
						<table border="0" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
						<tbody>';
					$lastIndex = count($packingStyleValues) - 1;
					foreach ($packingStyleValues as $index => $PackingStyle) {
						$borderStyle = $index === $lastIndex ? '' : 'border-bottom: 2px solid #808080;';
						$data .=
							'<tr>
								<td style="' . $borderStyle . ' padding: 5px;">' . trim($PackingStyle) . '</td>
							</tr>';
					}
					$data .= '</tbody></table></td>';
				}
			}
			if (!empty($RowVariety['Grade'])) {
				$gradeValues = explode(',', $RowVariety['Grade']);

				if (count($gradeValues) === 1) {
					$data .= '<td>' . trim($gradeValues[0]) . '</td>';
				} else {
					$data .= '<td style="padding: 0;">
						<table border="0" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
						<tbody>';
					$lastIndex = count($gradeValues) - 1;
					foreach ($gradeValues as $index => $grade) {
						$borderStyle = $index === $lastIndex ? '' : 'border-bottom: 2px solid #808080;';
						$data .=
							'<tr>
								<td style="' . $borderStyle . ' padding: 5px;">' . trim($grade) . '</td>
							</tr>';
					}
					$data .= '</tbody></table></td>';
				}
			}

			$data .= '<td>' . getFormatedDate($ShipByDate) . '</td>';

			if ($Rcount == 0) {
				$data .= '
				 <td >' . $Destination . '</td>
				<!-- <td >' . $PreparedBy . '</td> -->';
			} else {
				$data .= '
				 <td ></td>
				 <!-- <td ></td> -->';
			}
			$Rcount++;
			$data .= '  </tr>';
		}
	}
	$data .= '</tbody></table>';
	$AryData['TableData'] = $data;
	$AryData['RowCount'] = $Rcount;

	return $AryData;
}
?>