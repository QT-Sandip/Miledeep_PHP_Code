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
include_once($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}


//$date=$_GET['date'];
// $Year = $_GET['Year'];
// $Month = $_GET['Month'];
// $StationID = $_GET['PsID'];
// $Type = $_GET['Type'];
$from_date = date('Y-m-d', strtotime('-1 day'));
$to_date = date('Y-m-d');
$Year = isset($_GET['Year']) ? $_GET['Year'] : $to_date;
$Month = isset($_GET['Month']) ? $_GET['Month'] : $from_date;
$StationID = isset($_GET['PsID']) ? $_GET['PsID'] : '';
$Type = isset($_GET['Type']) ? $_GET['Type'] : '2';
$version = isset($_GET['version']) ? $_GET['version'] : '';
$createdFrom = " AND CreatedFrom=1 ";
if($version == 'v2'){
	$createdFrom = " AND CreatedFrom=0 ";
}

$monthName = date("F", mktime(0, 0, 0, $Month, 10));

if ($Type == 1) {
	$Header = 'Deheading Report-(' . $monthName . ')';
} else {
	$Header = 'Deheading Report-(' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}

if ($Type == 1) {
	$Query = "month(ProcessingDate)='$Month'  and year(ProcessingDate)='$Year' ";
} else {
	$Query = "ProcessingDate between '$Month'  and '$Year'";
}

// echo $Query;

//echo $Query ;exit;

$content1 .= '
			  			  <h3 align="center">' . $Header . '</h3>';
echo $content1;

$UnitName = '';
if ($StationID == '') {

	$SqlProStations = "SELECT * FROM `PpcVAStations`";
	$StmtProStations = $iconn->query($SqlProStations);

	while ($Row = $StmtProStations->fetch_array()) {
		$StationID = $Row['StationID'];
		$UnitName = $Row['UnitName'];
		//$Query.="and PS.StationID='$StationID'";

		$table_data = getP_TABLE($iconn, $Year, $Month, $UnitName, $StationID);

		if ($table_data['TOTAL_SENT_WEIGHT'] != 0) {
			//$pdf->writeHTML($table_data['TABLE']);
			echo $table_data['TABLE'];
		}
	}
} else {
	$SqlProStations = "SELECT * FROM `PpcVAStations` where StationID='$StationID' ";
	$StmtProStations = $iconn->query($SqlProStations);
	$Row = $StmtProStations->fetch_array();
	$UnitName = $Row['UnitName'];
	//$Query.="and PS.StationID='$StationID'";

	$table_data = getP_TABLE($iconn, $Year, $Month, $UnitName, $StationID);
	//$pdf->writeHTML($table_data['TABLE']);
	echo $table_data['TABLE'];
}

/*if($StationID !=''){
  $Query.="and PS.StationID='$StationID'";
}*/



$file_name = 'DE HEADING REPORT -' . $monthName . '.pdf';


function getP_TABLE($iconn, $Year, $Month, $UnitName, $StationID)
{

	global $Type, $Query, $createdFrom;


	$sql_lots = "SELECT
    imd.LotNumber,
    imd.PurchaseDate,
    imd.PurchaseCount AS HonCount,
    VA.CheckingCount,
    VA.ProcessingDate,
    VA.HonWeight,
    VA.WtAfterDH,
    VA.CountAfterDH,
    VA.ReceivedWeight,
    VA.ReturnWeight,
    RMG.UserName AS Grader,
    RMA.UserName AS Agent,
    NetCount,
    VA.ShiftID,
    VA.StationID,
    VA.Remarks,
    VA.IceConsumption,
    VA.HonCount AS HONCount,
    PS.UnitName
FROM
    PpcDeheading AS VA
LEFT JOIN RmLotDetatils AS imd ON imd.RmLotID = VA.LotID
LEFT JOIN RmUsersList AS RMG ON RMG.ID = imd.GraderID
LEFT JOIN RmUsersList AS RMA ON RMA.ID = imd.AgentID
LEFT JOIN PpcVAStations AS PS ON VA.StationID = PS.StationID
WHERE
    $Query $createdFrom AND PS.StationID = '$StationID' AND LotStatus != 2

UNION ALL

SELECT
    'melting' AS LotNumber,
    NULL AS PurchaseDate,
    NULL AS HonCount,
    VA.CheckingCount,
    VA.ProcessingDate,
    VA.HonWeight,
    VA.WtAfterDH,
    VA.CountAfterDH,
    VA.ReceivedWeight,
    VA.ReturnWeight,
    NULL AS Grader,
    NULL AS Agent,
    NULL AS NetCount,
    VA.ShiftID,
    VA.StationID,
    VA.Remarks,
    VA.IceConsumption,
    VA.HonCount AS HONCount,
    PS.UnitName
FROM
    PpcDeheading AS VA
LEFT JOIN PpcVAStations AS PS ON VA.StationID = PS.StationID
WHERE $Query $createdFrom AND
    VA.LotID = 'melting' AND PS.StationID = '$StationID'
";

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
	$width = 97 / 12;
	$width_s = $width - 5;
	$width_w = $width + 5;


	$content .= '

			  
	<table width=100% border="1" cellspacing="0" cellpadding="3">	
			  			  			  <h3 align="center"> ' . $UnitName . '</h3>

	<tr>

                <th  align="center"  ><B>S No.</B></th> 
				<th  align="center"  ><B>RM Date </B></th>
				 <th  align="center" ><B>Lot No </B></th>
				 <th  align="center" ><B>Agent Name </B></th>
				 <th  align="center" ><B>Grader Name</B></th>
               
                <th  align="center"  ><B>De Heading Date </B></th>
				<th  align="center"  ><B>HON Weight</B></th>
				<th  align="center"  ><B>HON Count</B></th>
				<th  align="center"  ><B>Checking Count</B></th>
				<th  align="center"  ><B>HL Yield</B></th>
				<th  align="center"  ><B>Count Net Yield</B></th>
				<th  align="center"  ><B>HON Qty Received</B></th>
				<th  align="center"  ><B>HON Return </B></th>
				<th  align="center"  ><B>HL Qty </B></th>
				<th  align="center"  ><B>HL Count </B></th>
				<th  align="center"  ><B>Ice Cost(Cans)</B></th>
				<th  align="center"  ><B>Amount</B></th>
		<!--	<th  align="center"  ><B>S Yeild </B></th>	-->
		<!--	<th  align="center"  ><B>Yeild </B></th>		-->
				<th  align="center"  ><B>Remarks </B></th>
               </tr>

			  ';

	$TOTAL_SENT_WEIGHT = 0;
	$TotalHoReceivedWeight = 0;
	$TotalHoReturnWeight = 0;
	$TotalHLWeight = 0;

	while ($r_lots = $stmt_lots->fetch_array()) {

		if(isset($r_lots['HONCount']) && $r_lots['HONCount'] != null && $r_lots['HONCount']!= '' && $r_lots['HONCount']>0){
			$HonCount = $r_lots['HONCount'];
		}else{
			$HonCount = $r_lots['HonCount'];
		}
		$SENT_WEIGHT = $r_lots['HonWeight'];
		$WtAfterDH = $r_lots['WtAfterDH'];
		$RECEIVED_WEIGHT = $r_lots['ReceivedWeight'];
		$RETURN_WEIGHT = $r_lots['ReturnWeight'];

		//ROBO START

		//$Variety=$r_lots['VARIETY'];
		$VarietyID = 4; //uma
		$CountObtained = $HonCount;
		$ShedIDD = $r_lots['StationID'];

		$IceCanCost = 200;

		$IceCans = $r_lots['IceConsumption'];
		$IceCost = ($IceCans * $IceCanCost);
		$TotalIceCost += $IceCost;


		$TOTAL_SENT_WEIGHT += $SENT_WEIGHT;
		$TotalHoReceivedWeight += $r_lots['ReceivedWeight'];
		$TotalHoReturnWeight += $RETURN_WEIGHT;
		$TotalHLWeight += $r_lots['WtAfterDH'];




		if ($ShedIDD == 9 || $ShedIDD == 12) {

			$Location = 0;
		} else {


			$Location = 1;
		}
		$SqlPeelingPrices = "SELECT PricePerKg FROM `PpcPeelingPrices` 
	WHERE LocationID='$Location' AND VarietyID='$VarietyID' AND
	    LowerLimit < '$CountObtained' and UpperLimit >= '$CountObtained'";
		$StmtPeelingPrices = $iconn->query($SqlPeelingPrices);
		$RowPeelingPrices = $StmtPeelingPrices->fetch_array();
		$PeelingAmount = $RowPeelingPrices['PricePerKg'];
		$Payment =	($PeelingAmount * $r_lots['ReceivedWeight']);
		$TotalPayment += $Payment;

		//ROBO END
		$yield = round(($WtAfterDH / ($RECEIVED_WEIGHT - $RETURN_WEIGHT)) * 100, 2);
		$yield1 = $yield . '%';
		$yield2 = trim(str_replace("()", "", $yield1));
		$PurchaseDate = '';
		if($r_lots['LotNumber']!='melting'){
			$PurchaseDate = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		}
		$ProcessingDate = date("d-m-Y", strtotime($r_lots['ProcessingDate']));
		//$r_lots['ANTI_BIOTIC_STATUS'];


		

		// $STANDARD_YIELD=getStandardYeild($iconn,$HonCount,"HL");

		if ($yield1 < $STANDARD_YIELD) {
			$COLOR = 'red';
		} else {
			$COLOR = 'GREEN';
		}



		$HLYield = ($r_lots['WtAfterDH'] / $r_lots['HonWeight']) * 100;

		$RETURN_WEIGHT = isset($r_lots['ReturnWeight']) ? $r_lots['ReturnWeight'] : "-";
		$content .=

			'<tr>
			<td  align="center" >' . $S_NO . '</td>
			<td  align="center" >' . $PurchaseDate . '</td>
			<td  align="center" >' . $r_lots['LotNumber'] . '</td>
			<td  align="center" >' . $r_lots['Agent'] . '</td>
			<td  align="center" >' . $r_lots['Grader'] . '</td>
			
			<td  align="center" >' . $ProcessingDate . '</td>
			<td  align="center" >' . $r_lots['HonWeight'] . '</td>
			<td  align="center" >' . $HonCount . '</td>
			<td  align="center" >' . $r_lots['CheckingCount'] . '</td>
			<td  align="center" >' . number_format($HLYield, 2) . '%</td>
			<td  align="center" >' . $r_lots['NetCount'] . '%</td>
			<td  align="center" >' . $r_lots['ReceivedWeight'] . '</td>
			<td  align="center" >' . $RETURN_WEIGHT . ' </td>
			<td  align="center" >' . $r_lots['WtAfterDH'] . '</td>
			<td  align="center" >' . $r_lots['CountAfterDH'] . '</td>
			<td  align="center" >' . $IceCost . '/' . $IceCans . '</td>
			<td  align="center" >' . $Payment . '</td>
	<!--	<td  align="center" >' . $STANDARD_YIELD . '</td>						-->
	<!--	<td  style="color:' . $COLOR . '" align="center" >' . $yield1 . ' </td>	-->
			<td  align="center" >' . $r_lots['Remarks'] . '</td>
	 </tr>';

		$S_NO++;
	}

	$content .=

		'<tr>
               <td  align="center" ></td>            				 
			   <td  style="color:' . $COLOR . '" align="center" >Total </td>
			   <td  align="center" ></td>				 
               <td  align="center" ></td>				                
               <td  align="center" ></td>				                
               <td  align="center" ></td>				                
			   <td  align="center" ><B>' . number_format($TOTAL_SENT_WEIGHT, 2) . '</B></td>
			   <td  align="center" ></td>
               <td  align="center" ></td>
               <td  align="center" ></td> 
			   <td  align="center" ></td>
			   <td  align="center" ><b>' . number_format($TotalHoReceivedWeight, 2) . '</b></td>
			   <td  align="center" ><b>' . number_format($TotalHoReturnWeight, 2) . '</b></td>
			   <td  align="center" ><b>' . number_format($TotalHLWeight, 2) . '</b></td>
			   	<td  align="center" ></td>
			   
			   <td  align="center" ><b>' . $TotalIceCost . '</b></td>
			   <td  align="center" ><b>' . $TotalPayment . '</b></td>
			   <td  align="center" ></td>
</tr>';



	$content .= '</table>';




	$Data['TOTAL_SENT_WEIGHT'] = $TOTAL_SENT_WEIGHT;
	$Data['TABLE'] = $content;

	return $Data;
}

?>