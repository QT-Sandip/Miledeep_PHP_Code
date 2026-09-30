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
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
	$Header = 'Value Addition Report-(' . $monthName . ')';
} else {
	$Header = 'Value Addition Report-(' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}

if ($Type == 1) {
	$Query = "month(ProcessingDate)='$Month'  and year(ProcessingDate)='$Year' ";
} else {
	$Query = "ProcessingDate between '$Month'  and '$Year'";
}

$content1 .= '<h3 align="center">' . $Header . '</h3>';
echo $content1;

$UnitName = '';

$table_data = getP_TABLE($iconn, $Year, $Month, $UnitName, $StationID);

echo $table_data['TABLE'];
$file_name = 'Value Addition Report -' . $Year . '-' . $Month . '.pdf';

function getP_TABLE($iconn, $Year, $Month, $UnitName, $StationID)
{
	$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
	$hasWageSelector = $ArrayClientConfigData['hasWageSelector'];
	global $Query, $Type;
	$date_end = $Month . ' 23:59:59';


	if ($StationID != '') {
		$Query .= "and PS.StationID='$StationID'";
	}

	$sql_lots = "SELECT DISTINCT
	  imd.RmLotID,imd.LotNumber,imd.PurchaseDate,imd.PurchaseWeight,VA.SentCount,VA.ProcessingDate,VA.SentWeight,
      VA.ReceivedWeight,VA.ReturnWeight,VA.WtAftrVA,VA.StationID,VA.Remarks,VA.ProcessingDate,VA.IceConsumption,
	  PS.UnitName,PO.ShortName as Variety, ASD.ShiftName,AGO.Name as WageName
	  from PpcValueAddition as VA 
	left join RmLotDetatils as imd on imd.RmLotID=VA.LotID
	left join PpcVAStations as PS on VA.StationID =PS.StationID
	left join PpcHLVAVarietyList as PO on PO.VarietyID =VA.VarietyID
	left join AdminShiftDetails as ASD on ASD.ShiftID = VA.ShiftID
	left join AdminGeneralOptions as AGO on AGO.OptionType='WAGE_TYPE' and AGO.Status=1 and AGO.OptID = VA.WageID
	where  $Query  and  LotStatus!=2  order by PurchaseDate ASC,LotNumber ASC,VA.VarietyID ASC";



	$stmt_lots = $iconn->query($sql_lots);
	$r_lots = $stmt_lots->fetch_array();
	$stmt_lots = $iconn->query($sql_lots);
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

	$content .= '<table width=100% border="1" cellspacing="0" cellpadding="3">
		<tr>
            <th align="center"><B>S No.</B></th>
		    <th align="center" style="min-width: 100px;"><B> Lot No </B></th>
            <th align="center" style="min-width: 100px;"><B>RM Date </B></th>
			<th align="center" style="min-width: 100px;"><B>VA Date </B></th> 
			<th align="center" style="min-width: 100px;"><B>Shift </B></th>';
	if ($hasWageSelector == 1) {
		$content .= '<th align="center" style="min-width: 100px;"><B>Wages </B></th>';
	}
	$content .= '<th align="center" style="min-width: 100px;"><B>Station </B></th>
            <th align="center"><B>Sent Count</B></th>
			<th align="center"><B>HL Qty</B></th>
			<th align="center" style="min-width: 180px;"><B>Type Of Product</B></th>
			<th align="center"><B>Received Weight</B></th>
			<th align="center"><B>HL Return </B></th>
			<th align="center"><B>Weight After VA </B></th>
			<th align="center"><B>Ice cost (Cans)</B></th>
			<th align="center"><B>Amount</B></th>
			<th align="center" style="min-width: 70px;"><B>Yeild </B></th> 		
			<th align="center" style="min-width: 150px;"><B>Remarks </B></th>
        </tr>';

	$TOTAL_SENT_WEIGHT = 0;
	$TotalReceivedWeight = 0;
	$TotalReturnWeight = 0;
	$TotalWgtAfterVa = 0;
	$TotalIceCost = 0;
	$TotalPayment = 0;

	while ($r_lots = $stmt_lots->fetch_array()) {

		$IceCanCost = 200;

		$IceCans = $r_lots['IceConsumption'];
		$SentWeight = $r_lots['SentWeight'];
		$ReceivedWeight = $r_lots['ReceivedWeight'];
		$WgtAfterVa = $r_lots['WtAftrVA'];
		$ReturnWeight = $r_lots['ReturnWeight'];
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

		$PeelingAmount = $RowPeelingPrices['PricePerKg'];

		$Payment = ($PeelingAmount * $r_lots['ReceivedWeight']);
		$TotalPayment += $Payment;

		$yield = round(($WgtAfterVa / ($SentWeight - $ReturnWeight)) * 100, 2);

		$yield1 = $yield . '%';
		$yield2 = trim(str_replace("()", "", $yield1));

		$PurchaseDate = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		$ProcessingDate = date("d-m-Y", strtotime($r_lots['ProcessingDate']));

		$ReturnWeight = isset($r_lots['ReturnWeight']) ? $r_lots['ReturnWeight'] : "-";

		$content .= '<tr>
				 <td align="center">' . $S_NO . '</td>
				 <td align="center">' . $r_lots['LotNumber'] . '</td>
				 <td align="center">' . $PurchaseDate . '</td>
				 <td align="center">' . $ProcessingDate . '</td> 
				 <td align="center">' . $r_lots['ShiftName'] . '</td>';
		if ($hasWageSelector == 1) {
			$content .= '<td align="center">' . $r_lots['WageName'] . '</td>';
		}
		$content .= '<td align="center">' . $r_lots['UnitName'] . '</td>
				 <td align="center">' . $r_lots['SentCount'] . '</td>
				 <td align="center">' . $r_lots['SentWeight'] . '</td>
				 <td align="center">' . $r_lots['Variety'] . '</td>';
		if ($SentWeight != $ReceivedWeight) {
			$content .= '<td style="color:red" align="center" >' . $r_lots['ReceivedWeight'] . '</td>';
		} else {
			$content .= '<td  align="center" >' . $r_lots['ReceivedWeight'] . '</td>';
		}
		$content .= '<td align="center">' . $ReturnWeight . ' </td>
					<td align="center">' . $WgtAfterVa . ' </td>
					<td align="center">' . $IceCost . '/' . $IceCans . '</td>
					<td align="center">' . $Payment . '</td>
					<td align="center">' . $yield1 . ' </td> 
					<td align="center">' . $r_lots['Remarks'] . '</td>
		</tr>';

		$S_NO++;
	}

	$content .= '
		<tr>
			<td align="center"><B>Total </B></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"><B>' . number_format($TOTAL_SENT_WEIGHT, 2) . '</B></td>
			<td align="center"></td>
			<td align="center"><B>' . number_format($TotalReceivedWeight, 2) . '</B></td>
			<td align="center"><B>' . number_format($TotalReturnWeight, 2) . '</B></td>
			<td align="center"><B>' . number_format($TotalWgtAfterVa, 2) . '</B></td>
			<td align="center"><B>' . $TotalIceCost . '</B></td>
			<td align="center"><B>' . $TotalPayment . '</B></td>
			<td align="center"></td>
			<td align="center"></td>
   		</tr>';

	$content .= '</table>';

	$Data['TOTAL_SENT_WEIGHT'] = $TOTAL_SENT_WEIGHT;
	$Data['TABLE'] = $content;

	return $Data;
}

?>