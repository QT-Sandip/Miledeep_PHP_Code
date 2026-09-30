<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
?>

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


$Year = $_GET['Year'];
$Month = $_GET['Month'];
$StationID = $_GET['PsID'];
$Type = $_GET['Type'];

$monthName = date("F", mktime(0, 0, 0, $Month, 10));

if ($Type == 1) {
	$Header = 'Value Addition Melting Report-(' . $monthName . ')';
} else {
	$Header = 'Value Addition Melting Report-(' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}

if ($Type == 1) {
	$Query = "month(ProcessingDate)='$Month'  and year(ProcessingDate)='$Year' ";
} else {
	$Query = "ProcessingDate between '$Month'  and '$Year'";
}

$content1 .= '<h3 align="center">' . $Header . '</h3>';
echo $content1;



$UnitName = '';
if ($StationID == '') {
	$SqlProStations = "SELECT * FROM `PpcVAStations`";
	$StmtProStations = $iconn->query($SqlProStations);

	while ($Row = $StmtProStations->fetch_array()) {
		$StationID = $Row['StationID'];
		$UnitName = $Row['UnitName'];
		$table_data = getP_TABLE($iconn, $Year, $Month, $UnitName, $StationID);

		if ($table_data['TOTAL_SENT_WEIGHT'] != 0) {
			echo $table_data['TABLE'];
		}
	}
} else {


	$SqlProStations = "SELECT * FROM `PpcVAStations` where StationID='$StationID' ";

	$StmtProStations = $iconn->query($SqlProStations);
	$Row = $StmtProStations->fetch_array();

	$UnitName = $Row['UnitName'];
	$table_data = getP_TABLE($iconn, $Year, $Month, $UnitName, $StationID);
	echo $table_data['TABLE'];
}


$file_name = 'Value Addition Melting Report -' . $Year . '-' . $Month . '.pdf';


function getP_TABLE($iconn, $Year, $Month, $UnitName, $StationID)
{
	global $Type, $Query;

	$date_end = $Month . ' 23:59:59';
	/*if($StationID !=''){
	 $Query.="and PS.StationID='$StationID'";
   }*/
	//$Query.="and VA.LotID='0'";


	$sql_lots = "SELECT DISTINCT
	  imd.RmLotID,
	  imd.LotNumber,
	  imd.PurchaseDate,
	  imd.PurchaseWeight,
	  VA.SentCount,
	  VA.ProcessingDate,
	  VA.SentWeight,
      VA.ReceivedWeight,
      VA.ReturnWeight,
	  VA.BrokenWeight,
	  VA.WtAftrVA,
	 
	  VA.StationID ,
	  VA.Remarks ,
	  VA.ProcessingDate ,
	  VA.IceConsumption ,
	  PS.UnitName,PO.ShortName as Variety
	  from PpcValueAddition as VA
			 left join RmLotDetatils as imd on imd.RmLotID=VA.LotID
			 left join PpcVAStations as PS on VA.StationID =PS.StationID
			 left join PpcHLVAVarietyList as PO on PO.VarietyID =VA.VarietyID
			  where  $Query and VA.LotID='0'and PS.StationID='$StationID' order by PurchaseDate ASC,LotNumber ASC,VA.VarietyID ASC";



	$stmt_lots = $iconn->query($sql_lots);
	$r_lots = $stmt_lots->fetch_array();
	$stmt_lots = $iconn->query($sql_lots);



	//


	$content = '';


	$size = 40;

	$size1 = 60;


	if ($UnitName == '') {
		$UnitName = $r_lots['UnitName'];
	}


	$S_NO = 1;
	$width = 120 / 14;
	$width_s = $width - 5;
	$width_w = $width + 5;


	$content .= '
	  <table width=100% border="1" cellspacing="0" cellpadding="3">
	  	<h3 align="center"> ' . $UnitName . '</h3>
			<tr>
                <th  align="center" ><B>S No.</B></th>
                <th  align="center" ><B>VA Date </B></th> 
                <th  align="center"><B>Station </B></th>
                <th  align="center" ><B>Sent Count</B></th>
                <th  align="center" ><B>HL Qty(Kgs)</B></th>
		        <th  align="center" ><B>Type Of Product</B></th>
				<th  align="center" ><B>Received Weight(Kgs)</B></th>
				<th  align="center" ><B>Broken Weight</B></th>
				<th  align="center" ><B>HL Return(Kgs) </B></th>
                <th  align="center" ><B>Weight After VA (Kgs)</B></th>
        		<th  align="center" ><B>Yield (%)</B></th>
				<th  align="center" ><B>Remarks </B></th>
            </tr>';

	$TOTAL_SENT_WEIGHT = 0;
	$TotalReceivedWeight = 0;
	$TotalReturnWeight = 0;
	$TotalWgtAfterVa = 0;

	while ($r_lots = $stmt_lots->fetch_array()) {

		$IceCanCost = 200;

		$IceCans = $r_lots['IceConsumption'];
		$SentWeight = $r_lots['SentWeight'];
		$ReceivedWeight = $r_lots['ReceivedWeight'];
		$WgtAfterVa = $r_lots['WtAftrVA'];
		$ReturnWeight = $r_lots['ReturnWeight'];
		$BrokenWeight = $r_lots['BrokenWeight'];
		$TOTAL_SENT_WEIGHT += $SentWeight;
		$TotalReceivedWeight += $r_lots['ReceivedWeight'];
		$TotalReturnWeight += $ReturnWeight;
		$TotalWgtAfterVa += $WgtAfterVa;
		$Variety = $r_lots['Variety'];
		$CountObtained = $r_lots['SentCount'];
		$ShedIDD = $r_lots['StationID'];
		$VarietyID = $r_lots['VarietyID'];


		$IceCost = ($IceCans * $IceCanCost);

		$TotalIceCost += $IceCost;

		if ($ShedIDD == 9 || $ShedIDD == 12) {

			$Location = 0;
		} else {


			$Location = 1;
		}

		$SqlPeelingPrices = "SELECT PricePerKg FROM `PpcPeelingPrices` WHERE 
       LocationID='$Location' AND VarietyID='$VarietyID' AND
	    LowerLimit < '$CountObtained' and UpperLimit >= '$CountObtained'";
		// exit;
		$StmtPeelingPrices = $iconn->query($SqlPeelingPrices);
		$RowPeelingPrices = $StmtPeelingPrices->fetch_array();

		$PeelingAmount = $RowPeelingPrices['PricePerKg'];

		$Payment = ($PeelingAmount * $r_lots['ReceivedWeight']);
		$TotalPayment += $Payment;



		$yield = round(($WgtAfterVa / ($SentWeight - $ReturnWeight)) * 100, 2);

		$yield1 = $yield . '%';
		$yield2 = trim(str_replace("()", "", $yield1));

		$PurchaseDate = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		$ProcessingDate = date("d-m-Y", strtotime($r_lots['ProcessingDate']));


		if ($yield1 < $STANDARD_YIELD) {
			$COLOR = 'red';
		} else {
			$COLOR = 'GREEN';
		}



		$ReturnWeight = isset($r_lots['ReturnWeight']) ? $r_lots['ReturnWeight'] : "-";
		$content .=

			'<tr>
				 <td  align="center">' . $S_NO . '</td>
				 <td  align="center">' . $ProcessingDate . '</td> 
				 <td  align="center" >' . $r_lots['UnitName'] . '</td>
                 <td  align="center">' . $r_lots['SentCount'] . '</td>
				 <td  align="center">' . $r_lots['SentWeight'] . '</td>
				 <td  align="center" >' . $r_lots['Variety'] . '</td>';
		if ($SentWeight != $ReceivedWeight) {

			$content .= '<td style="color:red" align="center">' . $r_lots['ReceivedWeight'] . '</td>';
		} else {
			$content .= '<td  align="center">' . $r_lots['ReceivedWeight'] . '</td>';
		}
		$content .= '<td  align="center">' . $BrokenWeight . ' </td>
				<td  align="center">' . $ReturnWeight . ' </td>
         		<td  align="center">' . $WgtAfterVa . ' </td>
         
				 <td style="color:' . $COLOR . '" align="center">' . $yield1 . ' </td>

				  <td  align="center">' . $r_lots['Remarks'] . '</td>

				 </tr>';

		$S_NO++;
	}

	$content .=
		'<tr>
		<td  align="center" width="50"><B>Total </B></td>
		<td  align="center" ></td>
		<td  align="center" ></td>
		<td  align="center" ></td>
		<td  align="center"><B>' . number_format($TOTAL_SENT_WEIGHT, 2) . '</B></td>
		<td  align="center" ></td>
		<td  align="center"><B>' . number_format($TotalReceivedWeight, 2) . '</B></td>
		<td  align="center"><B>' . number_format($TotalReturnWeight, 2) . '</B></td>
		<td  align="center"><B>' . number_format($TotalWgtAfterVa, 2) . '</B></td>
		<td  align="center" ></td>
		<td  align="center" ></td>
	</tr>';

	$content .= '</table>';
	$Data['TOTAL_SENT_WEIGHT'] = $TOTAL_SENT_WEIGHT;
	$Data['TABLE'] = $content;

	return $Data;
}

?>