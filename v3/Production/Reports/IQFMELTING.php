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
$monthName = date("F", mktime(0, 0, 0, $Month, 10));


if ($Type == 1) {
	$Query = "month(ProductionDate)='$Month'  and year(ProductionDate)='$Year'  ";
} else {
	$Query = "ProductionDate between '$Month'  and '$Year'  ";
}
if ($PsID != '') {
	$Query .= "and Unit='$PsID'";
}

if ($Type == 1) {
	$Header = 'IQF Packing Report (Melting)- (' . $monthName . ')';
} else {
	$Header = 'IQF Packing Report (Melting)- (' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}


$content1 .= '<h3 align="center">' . $Header . '</h3>';
echo $content1;

$table_data = getP_TABLE($iconn, $Month, $Year);
echo $table_data;

if ($Type == 1) {

	$file_name = 'IQF Report(Melting) -' . $Month . '.pdf';
} else {

	$file_name = 'IQF Report(Melting) -' . $Month . '-' . $Year . '.pdf';
}

function getP_TABLE($iconn, $Month, $Year)
{
	global $Query;

	$SqlLots = "SELECT *,PPD.NetWeight as Weight,PPD.Remarks from  ProdProductionData as PPD
	left join  RmLotDetatils as RLD on RLD.RmLotID=PPD.LotID 
	LEFT JOIN PpcVAStations AS PVS ON PPD.Unit = PVS.StationID
	where $Query and PPD.LotID='' order by ProductionDate,LotNumber asc";
	// $SqlLots = "SELECT *,PPD.NetWeight as Weight from  ProdProductionData as PPD
	// left join  RmLotDetatils as RLD on RLD.RmLotID=PPD.LotID 
	// where $Query and PPD.LotID=''  order by PurchaseDate,LotNumber asc";

	$StmtLots = $iconn->query($SqlLots);
	$Count = mysqli_num_rows($StmtLots);

	$content = '';

	$size = 40;
	$size1 = 60;

	$S_NO = 1;
	$width = 120 / 15;
	$width_s = $width - 5;
	$width_w = $width + 5;


	$content .= '<table width=100% border="1" cellspacing="0" cellpadding="3">
			<tr>  
           		<th  align="center" ><b>S No.</b></th>              
				<th  align="center" ><b>Source Station</b></th>
				<th  align="center" ><b> Tub No</b></th>
				<th  align="center" ><b>IQF Date</b></th>			
				<th  align="center" ><b>Shift</b></th>			
				<th  align="center" ><b>Variety</b></th>
                <th  align="center" ><b>Count</b></th>
                <th  align="center" ><b>Grade</b> </th>
                <th  align="center" ><b>Brand </b></th>
                <th  align="center" ><b>Category</b></th>
                <th  align="center" ><b>Cartons</b></th>
                <th  align="center" ><b>Packing</b> </th>               
				<th  align="center" ><b>Net Qty </b></th>
                <th  align="center" ><b>Remarks</b></th>
            </tr>';

	$TotalWeight = 0;
	while ($r_lots = $StmtLots->fetch_array()) {

		$PurchaseDate = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		$ProductionDate = date("d-m-Y", strtotime($r_lots['ProductionDate']));

		$content .='
			<tr>
				<td align="center" >' . $S_NO . '</th>
				<td align="center" >' . $r_lots['UnitName'] . '</td>
				<td align="center" >' . $r_lots['TubNo'] . '</td>
				<td align="center" >' . $ProductionDate . '</td>
				<td align="center" >' . $r_lots['Shift'] . '</td>
				<td align="center" >' . $r_lots['Variety'] . '</td>
				<td align="center" >' . $r_lots['Count'] . '</td>
				<td align="center" >' . $r_lots['Grade'] . '</td>
				<td align="center" >' . $r_lots['Brand'] . '</td>
				<td align="center" >' . $r_lots['Category'] . '</td>				 
				<td align="center" >' . $r_lots['Mcs'] . '</td>
				<td align="center" >' . $r_lots['PackingStyle'] . '</td>
				<td align="center" >' . $r_lots['Weight'] . ' Kg</td>				 
				<td align="center" >' . $r_lots['Remarks'] . ' </td>
			</tr>';
		$S_NO++;
		$TotalWeight += $r_lots['Weight'];
	}
	$content .='
		<tr>
			<td align="center" ><b></b> </td>
			<td align="center" ><b>Totals</b></td>				 
			<td align="center" ><b></b></td>					   
			<td align="center" ><b></b> </td>
			<td align="center" ><b></b> </td>
			<td align="center" ><b></b> </td>
			<td align="center" ><b></b> </td>
			<td align="center" ><b></b> </td>
			<td align="center" ><b></b> </td>
			<td align="center" ><b></b> </td>
			<td align="center" ><b></b> </td>
			<td align="center" ><b></b></td>				 
			<td align="left" 	><b>' . $TotalWeight . ' Kgs</b></td>
			<td align="center" ><b></b> </td>
		</tr>';
	$content .= '</table>';

	return $content;
}

?>