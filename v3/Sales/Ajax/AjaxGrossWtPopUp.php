<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$PoID = $_POST['PoID'];

$AccessType = $_SESSION["AccessType"];
// $ConsolidateGrossweightPopup = $_SESSION["ConsolidateGrossweightPopup"];
$ConsolidateGrossweightPopup = $_SESSION["ClientConfigData"]["ConsolidateGrossweightPopup"]; //by uma(28-02-2024)



// $ConsolidateGrossweightPopup = 1;

// $SqlLate="SELECT VL.FreezingType,PGD.ID AS PGDID,QtyUnit,CurrentMcs,sum(Mcs) as Mcs,PoNo,PoID,
// 	ShortName as Variety,VL.Grade,APS.PackingStyle,Price as LbPrice,
// sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
// sum(Mcs*TotalSlabs*SlabWeight*Price ) as Price,GrossWt,
// PGD.PackingStyleID,PGD.InvId AS InvoiceID
// FROM `ScrmPIData` as SPD
// LEFT JOIN ScrmPoGradeData as PGD on PGD.InvId=SPD.PoID 
// LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
// LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
// WHERE  PoID='$PoID' GROUP BY PGD.Variety,PGD.PackingStyleID,PGD.Grade order by PGD.ID ASC"; 

$SqlLate = getQuery($ConsolidateGrossweightPopup, $PoID);

// echo $SqlLate; exit;

$StmtLate = $iconn->query($SqlLate);

$data .= '<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >
                    <thead class="thead-light">
                    <th>Variety</th>
                    <th>Grade</th>
                    <th>Freezing Type</th>
					<th>Mcs</th>
                    <th>Packing Style</th>
					<th>Net Wt</th>
					<th>Gross Wt</th>
                    </thead>
                    <tbody>';
// echo json_encode($StmtLate->fetch_array(), JSON_PRETTY_PRINT);
while ($LateRow = $StmtLate->fetch_array()) {



	$QtyUnit = $LateRow['QtyUnit'];
	$PoNo = $LateRow['PoNo'];
	$TotalMcs += $LateRow['Mcs'];
	$TotalLbs += $LateRow['Lbs'];
	$InvoiceID = $LateRow['InvoiceID'];
	//$PckngStylID=$LateRow['PackingStyleID'];
	$PckngStylID = $LateRow['PGDID'];
	$PgdID = $PckngStylID;
	// $GrossWt = round($LateRow['GrossWt'], 2);
	$GrossWt = $LateRow['GrossWt'];

	$AryPoGradeID[] = $PoGradeID = $PgdID;

	$data .= '<tr>
		 <td>' .  $LateRow['Variety'] . '</td>
		 <td>' .  $LateRow['Grade'] . '</td>
		<td>' .  $LateRow['FreezingType'] . '</td>
        <td>' .  $LateRow['Mcs'] . '</td>
        <td>' .  $LateRow['PackingStyle'] . '</td>';
	$data .= '<td>' . number_format($LateRow['Lbs'], 2) . '</td>';
	$data .= '
	<td>
		
		<input type="number" class="form-control grossWeight" name="GrossWeight" step="0.001" style="min-width: 120px;"  data-id="GrossWeight' . $PgdID . '" id="GrossWeight' . $PgdID . '" placeholder="Enter Gross Weight in ' . $QtyUnit . '" 
				value="' . ($GrossWt == "0" ? "" : $GrossWt) . '"/> 
	</td>';
	// $data .= '
	// <td>
		
	// 	<input type="text" class="form-control grossWeight" name="GrossWeight" style="min-width: 120px;"  data-id="GrossWeight' . $PgdID . '" id="GrossWeight' . $PgdID . '" placeholder="Enter Gross Weight in ' . $QtyUnit . '" 
	// 			value="' . ($GrossWt == "0" ? "" : $GrossWt) . '" oninput="limitDecimalPlaces(this, 6)"  /> 
	// </td>';
	$data .= '</tr>';
}



echo	 $data .= '</tbody>

		</table>';

// echo  '<script>
// 			function limitDecimalPlaces(input, maxDecimalPlaces) {
// 				var parts = input.value.split(\'.\');
// 				if (parts.length > 1) {
// 					parts[1] = parts[1].substr(0, maxDecimalPlaces);
// 					input.value = parts.join(\'.\');
// 	   			}
// 			}
// 		</script>';

function checkEmpty($Value)
{


	if ($Value == '') {
		$Value = '--';
	}

	return $Value;
}

function getQuery($ConsolidateGrossweightPopup, $PoID)
{
	if ($ConsolidateGrossweightPopup == 0) {
		// 	$SqlLate = "SELECT VL.FreezingType,PGD.ID AS PGDID,QtyUnit,CurrentMcs,sum(Mcs) as Mcs,PoNo,PoID,
		// 	ShortName as Variety,VL.Grade,APS.PackingStyle,Price as LbPrice,
		// sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
		// sum(Mcs*TotalSlabs*SlabWeight*Price ) as Price,GrossWt,
		// PGD.PackingStyleID,PGD.InvId AS InvoiceID
		// FROM `ScrmPIData` as SPD
		// LEFT JOIN ScrmPoGradeData as PGD on PGD.InvId=SPD.PoID 
		// LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
		// LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
		// WHERE  PoID='$PoID' GROUP BY PGD.Variety,PGD.PackingStyleID,PGD.Grade order by PGD.ID ASC";

		$SqlLate = "SELECT VL.FreezingType,PGD.ID AS PGDID,QtyUnit,CurrentMcs,sum(Mcs) as Mcs,PoNo,PoID,
		ShortName as Variety,VL.Grade,APS.PackingStyle,Price as LbPrice,
	sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
	sum(Mcs*TotalSlabs*SlabWeight*Price ) as Price,GrossWt,
	PGD.PackingStyleID,PGD.InvId AS InvoiceID
	FROM `ScrmPIData` as SPD
	LEFT JOIN ScrmPoGradeData as PGD on PGD.InvId=SPD.PoID 
	LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
	LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
	WHERE  PoID='$PoID' GROUP BY PGD.ID order by PGD.ID ASC";
	} else {
		$SqlLate = "SELECT GROUP_CONCAT(VL.FreezingType) as FreezingType,PGD.PackingStyleID AS PGDID,QtyUnit,CurrentMcs,sum(Mcs) as Mcs,PoNo,PoID,
	GROUP_CONCAT(ShortName) as Variety,GROUP_CONCAT(VL.Grade) as Grade,APS.PackingStyle,Price as LbPrice,
sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
sum(Mcs*TotalSlabs*SlabWeight*Price ) as Price,sum(GrossWt) as GrossWt,
PGD.PackingStyleID,PGD.InvId AS InvoiceID
FROM `ScrmPIData` as SPD
LEFT JOIN ScrmPoGradeData as PGD on PGD.InvId=SPD.PoID 
LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
WHERE  PoID='$PoID' GROUP BY PGD.PackingStyleID order by PGD.ID ASC";
	}

	// echo $SqlLate;exit;
	return $SqlLate;
}
