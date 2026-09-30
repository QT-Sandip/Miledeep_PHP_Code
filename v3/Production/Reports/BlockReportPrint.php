<style>
	table tr td {

		font-size: 17px;
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
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$Month = $_GET['Month'];
$Year = $_GET['Year'];
$PsID = $_GET['PsID'];
$Type = $_GET['Type'];





if ($Type == 1) {
	$Query = "month(ProductionDate)='$Month'  and year(ProductionDate)='$Year'  ";
} else {
	$Query = "ProductionDate between '$Month'  and '$Year'  ";
}
if ($PsID != '') {
	$PsID = $_GET['PsID'];
	$Query .= "and Unit='$PsID'";
	// $Query .= "and Source='$PsID'";
}

$monthName = date("F", mktime(0, 0, 0, $Month, 10));


if ($Type == 1) {
	$Header = 'Block Packing Report-(' . $monthName . ')';
} else {
	$Header = 'Block Packing Report-(' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}

$content1 .= ' <h3 align="center">' . $Header . '</h3>';
echo $content1;

$table_data = getP_TABLE($iconn, $Month, $Year);

echo $table_data;

function getP_TABLE($iconn, $Month, $Year)
{
	global $Query;


	$SqlBlockData = "SELECT  PPD.Mcs,RLD.LotNumber,RLD.PurchaseDate,PPD.TubNo,PPD.ProductionDate,PPD.Variety,PPD.Count,
	PPD.Grade,PPD.Brand,PPD.SLAB_WEIGHT,PPD.NetWeight,PackingStyle,PPD.Remarks,UnitName from RmLotDetatils as RLD 
	left join ProdProductionData as PPD on RLD.RmLotID=PPD.LotID 
	LEFT JOIN PpcVAStations AS PVS ON PPD.Unit = PVS.StationID
	where  $Query and PPD.FreezingType='BLOCK' order by RLD.PurchaseDate,RLD.LotNumber asc";


	$SqlBlockData;

	$stmt_lots = $iconn->query($SqlBlockData);
	$Count = mysqli_num_rows($stmt_lots);




	$content = '';
	$size = 40;
	$size1 = 60;

	$S_NO = 1;
	$width = 120 / 15;
	$width_s = $width - 5;
	$width_w = $width + 5;
	$content .= '<table width=100% border="1" cellspacing="0" cellpadding="3">
			   <tr>  
                <th  align="center"  ><b>S No.</b></th>              
				<th  align="center"  ><b>Source Station</b></th>
				<th  align="center"  ><b> Id No</b></th>
				<th  align="center"  ><b>RM Date</b></th>			
				<th  align="center"  ><b> Date</b></th>			
				<th  align="center"  ><b>Variety</b></th>
                <th  align="center"  ><b>Count</b></th>
                <th  align="center"  ><b>Grade </b></th>
                <th  align="center"  ><b>Brand </b></th>
                <th  align="center"  ><b>Mcs </b></th>
                <th  align="center"  ><b>Packing</b></th>
                <th  align="center"  ><b>Net Qty</b></th>
                <th  align="center"  ><b>Remarks</b> </th>
               </tr>';

	$TotalWeight = 0;

	while ($r_lots = $stmt_lots->fetch_array()) {


		$PurchaseDate = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		$ProductionDate = date("d-m-Y", strtotime($r_lots['ProductionDate']));

		$content .=

			'<tr>
				 <td  align="center" >' . $S_NO . '</td>
				 <td  align="center" >' . $r_lots['UnitName'] . '</td>
				 <td  align="center" >' . $r_lots['LotNumber'] . '</td>
				 <td  align="center" >' . $PurchaseDate . '</td>
				 <td  align="center" >' . $ProductionDate . '</td>
				 <td  align="center" >' . $r_lots['Variety'] . '</td>
				 <td  align="center" >' . $r_lots['Count'] . '</td>
				 <td  align="center" >' . $r_lots['Grade'] . '</td>
				 <td  align="center" >' . $r_lots['Brand'] . '</td>				 
				 <td  align="center" >' . $r_lots['Mcs'] . '</td>
				 <td  align="center" >' . $r_lots['PackingStyle'] . '</td>
				 <td  align="center" >' . $r_lots['NetWeight'] . ' Kg</td>				 
				 <td  align="center" >' . $r_lots['Remarks'] . ' </td>
				 
				 </tr>';

		$S_NO++;
		$TotalWeight += $r_lots['NetWeight'];
	}

	$content .=

		'<tr>			   
		 
				<td  align="center" ><b></b> </td>
               <td  align="center" ><b>Total Lots</b></td>				 
			   <td  align="center" ><b>' . $Count . '</b> </td>
			   <td  align="center" ><b></b> </td>
			   <td  align="center" ><b></b> </td>
			   <td  align="center" ><b></b> </td>
			   <td  align="center" ><b></b> </td>
			   <td  align="center" ><b></b> </td>
			   <td  align="center" ><b></b> </td>
			   <td  align="center" ><b></b> </td>
				<td  align="center" ><b>Total Weight</b></td>				 
			   <td  align="left" ><b>' . $TotalWeight . ' Kgs</b></td>
							   <td  align="center" ><b></b> </td>
 
		 </tr>';

	$content .= '</table>';


	return $content;
}

?>