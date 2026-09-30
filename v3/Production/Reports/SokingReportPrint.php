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
	$Query = "month(SoakingDate)='$Month'  and year(SoakingDate)='$Year'  ";
} else {
	$Query = "SoakingDate between '$Month'  and '$Year'  ";
}
if ($PsID != '') {
	$PsID = $_GET['PsID'];
	$Query .= "and Source='$PsID'";
}




$monthName = date("F", mktime(0, 0, 0, $Month, 10));


if ($Type == 1) {
	$Header = 'Soaking Report-(' . $monthName . ')';
} else {
	$Header = 'Soaking Report-(' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}
$content1 .= '
			  			  <h3 align="center">' . $Header . '</h3>';
echo $content1;

$table_data = getP_TABLE($iconn, $Month, $Year);

echo $table_data;

if ($Type == 1) {

	$file_name = 'SOAKING Report -' . $Month . '.pdf';
} else {

	$file_name = 'SOAKING Report -' . $Month . '-' . $Year . '.pdf';
}

function getP_TABLE($iconn, $Month, $Year)
{
	global $Query;


	$sql_lots = "SELECT DISTINCT imd.RmReferenceID, imd.LotNumber, imd.PurchaseDate, imd.PurchaseCount, 
	SCK.TubNumber, SCK.SoakingDate,SCK.LotID, SCK.Variety,PHVL.ShortName AS ShortName,PHVL.ProductID AS ProductID, SCK.SoakInQty, SCK.SoakInCount,SCK.SoakingInCount,SCK.Remarks,
	SCK.SoakOutCount, SCK.SoakOutQty, SCK.Destination, SCK.TimePeriod, SCK.PostedTimeDate,PVS.UnitName
	from RmLotDetatils as imd 
	left join ProdSoakingDetails as SCK on imd.RmLotID=SCK.LotID
	LEFT JOIN PpcVAStations AS PVS ON SCK.Source = PVS.StationID
	left join PpcHLVAVarietyList as PHVL on PHVL.VarietyID=SCK.Variety AND PHVL.Status = 1
	where $Query and LotStatus!=2 AND SCK.CreatedFrom=0 order by SoakingDate asc,LotNumber asc,SCK.Variety asc";
	// $sql_lots = "SELECT DISTINCT imd.RmReferenceID, imd.LotNumber, imd.PurchaseDate, imd.PurchaseCount, 
	// SCK.TubNumber, SCK.SoakingDate,SCK.LotID, PHVL.ShortName AS Variety, SCK.SoakInQty, SCK.SoakInCount,
	// SCK.SoakOutCount, SCK.SoakOutQty, SCK.Destination, SCK.TimePeriod, SCK.PostedTimeDate
	// from RmLotDetatils as imd 
	// left join ProdSoakingDetails as SCK on imd.RmLotID=SCK.LotID
	// left join PpcHLVAVarietyList as PHVL on PHVL.VarietyID=SCK.Variety
	// where $Query and LotStatus!=2 order by SoakingDate asc,LotNumber asc,SCK.Variety asc";

	$stmt_lots = $iconn->query($sql_lots);
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
                 <th  align="center"  ><B>S No.</B></th>
				 <th  align="center" style="width: 120px;" ><B> Tub No </B></th>
				 <th  align="center" style="width: 80px;"><B> Id No </B></th>
				 <th  align="center" style="width: 120px;"><B>Rm Date</B></th>
				 <th  align="center" style="width: 200px;" ><B>Source Station</B></th>
				 <th  align="center" style="width: 120px;"><B>Soaking Date </B></th>
				 <th  align="center" style="width: 150px;" ><B>Variety</B></th>
                 <th  align="center"  ><B>Count </B></th>
                 <th  align="center"  ><B>In Quantity </B></th>
                 <th  align="center"  ><B>In Count </B></th>
                 <th  align="center"  ><B>Soaking In Count </B></th>
				 <th  align="center"  ><B> Time Period</B></th>
	  			 <th  align="center"  ><B>Out Count</B></th>
				 <th  align="center"  ><B>Dest</B></th>
				 <th  align="center"  ><B>Out Quantity </B></th>
				 <th  align="center"  style="width: 70px;" ><B>Yield %</B></th>
				 <th  align="center" style="width: 250px;" ><B>Remarks</B></th>
               </tr>';

	$TotalSoakInQuantity = 0;
	$TotalSoakOutQuantity = 0;
	while ($r_lots = $stmt_lots->fetch_array()) {

		$PURCHASE_DATE = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		$CreatedDateTime = date("d-m-Y", strtotime($r_lots['SoakingDate']));
		$TotalSoakInQuantity += $r_lots['SoakInQty'];
		$TotalSoakOutQuantity += $r_lots['SoakOutQty'];

		$InQty = $r_lots['SoakInQty'];
		$OutQty = $r_lots['SoakOutQty'];
		$Yield = (($OutQty / $InQty) * 100) - 100;

		if ($CreatedDateTime == '01-01-1970') {

			$CreatedDateTime = 'Not Updated';
		}

		$variety = $r_lots['ShortName'];
		if ($r_lots['ProductID']) {
			$variety = $r_lots['ProductID'];
		}
		if (!$variety) {
			$variety = $r_lots['Variety'];
		}
		$content .=

			'<tr>
				 <td  align="center"  >' . $S_NO . '</td>
				 <td  align="center" >' . $r_lots['TubNumber'] . '</td>
				 <td  align="center" >' . $r_lots['LotNumber'] . '</td>
				 <td  align="center" >' . $PURCHASE_DATE . '</td>
				 <td  align="center" >' . $r_lots['UnitName'] . '</td>
				 <td  align="center" >' . $CreatedDateTime . '</td>
				 <td  align="center"  >' . $variety . '</td>
				 <td  align="center"  >' . $r_lots['PurchaseCount'] . '</td>

				 <td  align="center" >' . $r_lots['SoakInQty'] . '</td>
				 <td  align="center" >' . $r_lots['SoakInCount'] . '</td>
				 <td  align="center" >' . $r_lots['SoakingInCount'] . '</td>
				 <td  align="center" >' . $r_lots['TimePeriod'] . '</td>
				 <td  align="center" >' . $r_lots['SoakOutCount'] . ' </td>

				 <td  align="center" >' . $r_lots['Destination'] . ' </td>
				 <td  align="center" >' . $r_lots['SoakOutQty'] . ' </td>
				 <td  align="center" >' . round($Yield, 2) . '% </td>
				 <td  align="center" >' . $r_lots['Remarks'] . ' </td>
				 </tr>';

		$S_NO++;
	}
	$content .=

		'<tr>
		  <td  align="center" width=""><b></b> </td>
               <td  align="center" width=""><b>Total Lots</b></td>				 
			   <td  align="center" width=""><b>' . $Count . '</b> </td>	
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b>' . $TotalSoakInQuantity . '</b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b>' . $TotalSoakOutQuantity . '</b></td>
			   <td  align="center" width=""><b></b> </td>
			   <td  align="center" width=""><b></b> </td>
		 </tr>';
	$content .= '</table>';

	return $content;
}

?>