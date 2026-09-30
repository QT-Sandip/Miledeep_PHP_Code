<style>
	table tr td {

		font-size: 12px;
		text-align: left;
	}

	table tr th {

		font-size: 15px;
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

session_start();
include ($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ConsolidateLotCount = $ClientConfigData->ConsolidateLotCount;

$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}


// $Year = $_GET['Year'];
// $Month = $_GET['Month'];
// $StationID = $_GET['PsID'];
// // $StationID=1;
// $Type = $_GET['Type'];
$from_date = date('Y-m-d', strtotime('-1 day'));
$to_date = date('Y-m-d');
$Year = isset($_GET['Year']) ? $_GET['Year'] : $to_date;
$Month = isset($_GET['Month']) ? $_GET['Month'] : $from_date;
$StationID = isset($_GET['PsID']) ? $_GET['PsID'] : '';
$Type = isset($_GET['Type']) ? $_GET['Type'] : '2';
$AgentID = $_GET['Agent']; //uma to get grading report at border counts--> view
$version = isset($_GET['version']) ? $_GET['version'] : '';
$createdFrom = " AND CreatedFrom=1 ";
$created_From = 1;//v3
if($version == 'v2'){
	$createdFrom = " AND CreatedFrom=0 ";
	$created_From = 0;//v2
}


$checkDate = '2023-10-26'; // till this date Data is present in an old Table

if ($Year < $checkDate && $Month < $checkDate) {

		$tablename = '`RmLotDetatilsOld`';

	} else {

		$tablename = '`RmLotDetatils`';

		

	}

$Query = "";


if ($Type == 1) {
	$Query = "month(GradingDate )='$Month'  and year(GradingDate )='$Year' ";
} else {
	$Query = "GradingDate  between '$Month'  and '$Year'";
}
if ($StationID != '') {
	$Query .= "and PS.StationID='$StationID'";
}

if ($AgentID != '') {
	if ($Type == 1) {
		$Query = "month(PurchaseDate )='$Month'  and year(PurchaseDate )='$Year' ";
	} else {
		$Query = "PurchaseDate  between '$Month'  and '$Year'";
	}
	$Query .= "and IM.AgentID='$AgentID'";
}

$text = 1;
$text1 = 1;

$ShowDate = getFormatedDate($FROM_DATE);

$SqlCosting = "SELECT * FROM ProductionGradesMaster order by abs(Grade) asc";
$StmtCosting = $iconn->query($SqlCosting);

while ($RowCosting = $StmtCosting->fetch_assoc()) {
	$ArrayCostingRates[] = $RowCosting;
}


$aval_hl_reports_sql = "select IM.RmLotID,IM.PurchaseDate, IM.LotNumber, RUG.UserName as GraderName, RUA.UserName as AgentName, 
   IM.PurchaseWeight,IM.ProductionWeight, IM.PurchaseCount, AGM.MachineName, HW.IsMix,HW.GradingDate, HW.MixIDs,
SUM(round(VA.WtAfterDH,2)) as WAVA,AVG(round(NetCount,2)) as AvgNetCount,
sum(round(VA.ReceivedWeight ,2)) as ReceivedWeight,
 round(avg(VA.CountAfterDH ),2) as CAVA,
 sum(round(VA.ReturnWeight ,2))  as ReturnWeight
 from ".$tablename." AS IM 

left join PpcDeheading  as VA on VA.LotID=IM.RmLotID $createdFrom
left join PpcHLGradingDetails as HW ON HW.LotID=IM.RmLotID  AND HW.CreatedFrom=$created_From
left join RmUsersList as RUG ON RUG.ID=IM.GraderID 
left join RmUsersList as RUA ON RUA.ID=IM.AgentID 
left join AdminGradingMachines as AGM ON AGM.MachineID=HW.MachineID
left join PpcVAStations as PS on HW.StationID =PS.StationID
 WHERE $Query   group by IM.RmLotID order by
	IM.PurchaseDate,IM.LotNumber ASC ";

//echo '<pre>'.$aval_hl_reports_sql; die;
$stmt_hl = $iconn->query($aval_hl_reports_sql);
//echo $aval_hl_reports_sql;
//exit;
$BILLED_LOTS = $stmt_hl->num_rows;

$table = getGrading($iconn, $stmt_hl);

echo $table;

function getGrading($iconn, $hl_stmt)
{

	global $FROM_DATE, $ShowDate;
	global $text;
	global $text1;
	global $version;
	global $ConsolidateLotCount;	
	global $tablename;

	$ArrayCostingRates = getGradesBup();

	$ArrayDayGrades = array();

	$content = getTableHead($ArrayCostingRates);

	$SNO = 1;
	$PREV_DATE = '';
	$i = 0;
	$j = 0;

	$H_ON = 0;
	$TOTAL_HO_COUNT = 0;
	$avg_count = 0;

	while ($DataRow = $hl_stmt->fetch_array()) {

		$RmLotID = $DataRow['RmLotID'];
		$GradingBrkLotID = $DataRow['RmLotID'];
		$IsMix = $DataRow['IsMix'];
		$MixIDs = $DataRow['MixIDs'];
		$MixIDs = $DataRow['MixIDs'];
		if($version == 'v2' && $ConsolidateLotCount == 0 && $RmLotID){
			$LotWieghtSql = "SELECT SUM(RLD2.PurchaseWeight) PurchaseWeight,SUM(RLD2.ProductionWeight)  ProductionWeight
							FROM $tablename RLD
							JOIN $tablename RLD2 ON RLD2.RmReferenceID = RLD.RmReferenceID AND RLD2.PurchaseCount = RLD.PurchaseCount
							WHERE RLD.`RmLotID` IN($RmLotID)";
			$stmtLotWieght = $iconn->query($LotWieghtSql);
			$LotWieghtRes = $stmtLotWieght->fetch_assoc();
		}

		if ($IsMix == 1) {
			if (in_array($MixIDs, $AryMixedLots)) {
				continue;
			} else {
				$AryMixedLots[] = $MixIDs;
				$IDS = str_replace('_', ',', $MixIDs);
				$GradingBrkLotID = str_replace('_', ',', $MixIDs);
				$DataRow = getMixedData($IDS, $DataRow);
				$CountNetQty = $DataRow['CountNetQty'];
			}
		} else {
			// $DataRow = getMixedData($RmLotID, $DataRow); //uma
			$HLQty = isset($DataRow['WAVA']) ? $DataRow['WAVA'] : 'NA';
			$CountNetYield = $DataRow['AvgNetCount'];
			$CalHon = $CountNetYield / 100;
			$CountNetQty = $HLQty / $CalHon;
		}


		$ReceivedWeight = $DataRow['ReceivedWeight'];
		$ReturnWeight = $DataRow['ReturnWeight'];
		$HLWeight = $DataRow['HLWeight'];
		$HLCount = $DataRow['HLCount'];
		$GradData = getGradesData($GradingBrkLotID);

		$RmQty = $DataRow['ProductionWeight'];
		if($LotWieghtRes['ProductionWeight']){
			$RmQty = $LotWieghtRes['ProductionWeight'];
		}
		$GradingMachine = $DataRow['MachineName'];
		$RmDate = getFormatedDate($DataRow['PurchaseDate']);
		$GradingDate = getFormatedDate($DataRow['GradingDate']);
		$LotNo = $DataRow['LotNumber'];
		$GradName = $DataRow['GraderName'];
		$AgentName = $DataRow['AgentName'];
		$RmCount = $DataRow['PurchaseCount'];
		$RmAvgCount = $DataRow['RmAvgCount'];
		$Weight = $DataRow['PurchaseWeight'];
		$PurchaseDate = $DataRow['PurchaseDate'];
		$RmQty = $DataRow['PurchaseWeight'];
		if($LotWieghtRes['PurchaseWeight']){
			$RmQty = $LotWieghtRes['PurchaseWeight'];
		}

		$HLCount = isset($DataRow['CAVA']) ? $DataRow['CAVA'] : 'NA';
		$HLQty = isset($DataRow['WAVA']) ? $DataRow['WAVA'] : 'NA';
		$HLYeild = round(($HLQty / $RmQty) * 100, 2);

		// $CountNetYield = $DataRow['AvgNetCount'];
		// $CalHon = $CountNetYield / 100;
		// $CountNetQty = $HLQty / $CalHon;
		// $CountNetYieldPerc = round(($CountNetQty - $Weight), 2);
		
		$CountNetYieldPerc = round(($CountNetQty - $RmQty), 2); // sriram

		// $standardYield = getStandardYield($RmCount);
		$standardYield = getStandardYield($RmCount, $PurchaseDate);
		$stanYield = $standardYield / 100;
		$standardYieldPerc = round((($HLQty / $stanYield) - $CountNetQty), 2);

		$GradQty = $GradData['GradQty'];
		$GradCount = $GradData['GradCount'];
		$GradCount = round($GradCount / $RmQty, 2);
		$GradYield = round(($GradQty / $RmQty) * 100, 2);
		$QtyVariation = round(($GradQty - $HLQty), 2);

		if ($IsMix == 1) {
			$CountVariation = round(($RmAvgCount - $GradCount), 2);
		} else {
			$CountVariation = round(($RmCount - $GradCount), 2);
		}

		$RmWeighTotal += $RmQty;
		$HLWEIGHTALL += $HLQty;
		$GradingTotal += $GradQty;
		$content .= '
		<tr>
			<td  align="CENTER" ><B>' . $GradingMachine . '</B></font></td>
			<td  align="CENTER" ><B>' . $RmDate . '</B></font></td>
			<td  align="CENTER" ><B>' . $GradingDate . '</B></font></td>
			<td  align="CENTER" ><B>' . $LotNo . '</B></font></td>
			<td  align="CENTER" ><B>' . $GradName . '</B></font></td>
			<td  align="CENTER" ><B>' . $AgentName . '</B></font></td>
			<td  align="CENTER" ><B>' . $RmCount . '</B></font></td>
			<td  align="CENTER" ><B>' . $RmQty . '</B></font></td>
			<td  align="CENTER" ><B>' . $HLCount . '</B></font></td>
			<td  align="CENTER" ><B>' . $HLQty . '</B></font></td>';
		$GradData['Trows'];
		$content .= $GradData['Trows'];
		if ($GradQty == 0) {
			$content .= '<td  align="CENTER" ><B>NA</B></font></td>';
		} else {
			$content .= '<td  align="CENTER" ><B>' . $GradQty . '</B></font></td>';
		}

		$content .= '<td  align="CENTER" >' . $GradYield . '</font></td>';

		if ($QtyVariation < 0) {
			$content .= '<td style="color:red" align="CENTER"></font>' . $QtyVariation . '</td>';
		} else {
			$content .= '	<td  align="CENTER" ></font>' . $QtyVariation . '</td>';
		}

		$content .= '<td  align="CENTER">' . $CountNetYieldPerc . '</font></td>';
		$content .= '<td  align="CENTER">' . $standardYieldPerc . '</font></td>';

		$content .= '<td  align="CENTER" >' . $GradCount . '</font></td>';

		if ($CountVariation < 0) {
			$content .= '<td style="color:red" align="CENTER" >' . $CountVariation . '</font></td>';
		} else {
			$content .= '<td align="CENTER" >' . $CountVariation . '</font></td>';
		}
		$content .= '</tr>';
		$SNO++;

		foreach ($GradData['TotalData'] as $D) {
			$ArrayDayGrades[] = $D;
		}
	}

	$content .= '<tr><td align="CENTER" ><B>TOTAL</B></font></td>';
	$content .= '<td align="CENTER" ><B></B></font></td>';
	$content .= '<td align="CENTER" ><B></B></font></td>';
	$content .= '<td align="CENTER" ><B></B></font></td>';
	$content .= '<td align="CENTER" ><B></B></font></td>';
	$content .= '<td align="CENTER" ><B></B></font></td>';
	$content .= '<td align="CENTER" ><B></B></font></td>';
	$content .= '<td align="CENTER" ><B>' . $RmWeighTotal . '</B></font></td>';
	$content .= '<td align="CENTER" ><B></B></font></td>';
	$content .= '<td align="CENTER" ><B>' . $HLWEIGHTALL . '</B></font></td>';

	//uma costing rates
	foreach ($ArrayCostingRates as $RowCosting) {
		$weight = 0;
		$T_HL = 0;

		foreach ($ArrayDayGrades as $RowGrades) {
			foreach ($RowGrades as $RowGrade) {

				if ($RowGrade['GRADE'] == $RowCosting['Grade']) {

					$weight += $RowGrade['WEIGHT'];
					$T_HL += $RowGrade['WEIGHT'];
					$T_HL += $RowGrade['WEIGHT'];
				}
			}
		}
		$content .= '<td  align="CENTER" ><B>' . $weight . '</B></font></td>';
	}
	$ArrayDayGrades = array();
	$HL_GRADING_VAR = $HLWEIGHTALL - $GradingTotal;

	$content .= '<td align="CENTER" ><B>' . $GradingTotal . '</B></font></td>';
	$content .= '<td align="CENTER" ><B></B></font></td>';
	$content .= '<td align="CENTER" ><B>' . round(($GradingTotal - $HLWEIGHTALL), 2) . '</B></font></td>';

	$content .= '<td align="CENTER" ><B></B></font></td>
				<td align="CENTER" ><B></B></font></td>
				<td align="CENTER" ><B></B></font></td>
				</tr>';
	$DAY_PUR_TOT = 0;
	$T_HL = 0;

	$content .= '</table>';
	return $content;
}


function getGradesBup()
{
	global $iconn;
	global $text;
	global $text1;
	global $ArrayCostingRates;

	return $ArrayCostingRates;
}

//function getGradesData($data){
function getGradesData($GradingBrkLotID)
{
	global $iconn;
	global $text1,$createdFrom;

	$content = '';
	$GradQty = 0;
	$GradCount = 0;

	$SqlGrdBrk = "SELECT sum(HLWeight) as HLWeight,HLCount FROM `PpcHLGradingBreakup` 
			   WHERE LotID in ($GradingBrkLotID) $createdFrom GROUP by HLCount ORDER by HLCount ASC";

	$StmtGrdBrk = $iconn->query($SqlGrdBrk);
	while ($RowBrk = $StmtGrdBrk->fetch_array()) {

		$HLCount = $RowBrk['HLCount'];
		$HLWeight = $RowBrk['HLWeight'];
		$AryCounts[] = $HLCount;
		$ArySamples[] = $RowBrk;
		$GradQty += $HLWeight;
		$GradCount += ($HLWeight * $HLCount * 2.2);
	}

	$ary_lot_grade = array();
	$AryGrades = getGradesBup();

	foreach ($AryGrades as $RowCosting) {

		$LowLimit = $RowCosting['LowerLimit'];
		$UpLimit = $RowCosting['UpperLimit'];

		$HLWeight = 0;

		foreach ($ArySamples as $RowGrading) {
			$CheckCount = $RowGrading['HLCount'];
			$CheckCount = number_format(floor($RowGrading['HLCount']), 2);
			//$CheckCount = number_format(round($RowGrading['HLCount'], 0), 2);
			if ($CheckCount >= $LowLimit && $CheckCount <= $UpLimit) {
				$HLWeight += $RowGrading['HLWeight'];
				$HLCount = $RowGrading['HLCount'];
				$CheckCount;
				// break;
			} else {
				// $HLWeight='0';
			}
		}

		$content .= '<td  align="CENTER" >' . $HLWeight . '</font></td>';

		$sample['GRADE'] = $RowCosting['Grade'];
		$sample['WEIGHT'] = $HLWeight;
		$sample['COUNT'] = $HLCount;
		$ary_lot_grade[] = $sample;
	}

	$ArrayDayGrades[] = $ary_lot_grade;

	$Data['GradCount'] = $GradCount;
	$Data['GradQty'] = $GradQty;
	$Data['Trows'] = $content;
	$Data['TotalData'] = $ArrayDayGrades;

	return $Data;
}


function getMixedData($IDS, $DataRow)
{
	global $iconn,$tablename,$createdFrom;

	$sql_LOTS = "SELECT
	GROUP_CONCAT(LotNumber SEPARATOR ', ') as LotNumber,
	GROUP_CONCAT(PurchaseCount SEPARATOR ', ') as PurchaseCount,
	AVG(PurchaseCount) as RmAvgCount,
	(select SUM(round((WtAfterDH/(NetCount/100)),2)) from PpcDeheading WHERE  LotID in ($IDS) $createdFrom ) as AvgNetCount,
	sum(round(PurchaseWeight,2)) as PurchaseWeight,
	sum(round(ProductionWeight,2)) as ProductionWeight,
	(select sum(round(WtAfterDH,2)) from PpcDeheading WHERE  LotID in ($IDS) $createdFrom ) as WAVA,
	(select GROUP_CONCAT(CountAfterDH  SEPARATOR ', ')  from PpcDeheading WHERE  LotID in ($IDS) $createdFrom ) as CAVA
	from ".$tablename."
	WHERE RmLotID in ($IDS)";
	// echo $sql_LOTS;exit;

	// $CountNetYield = $DataRow['AvgNetCount'];
	// 	$CalHon = $CountNetYield / 100;
	// 	$CountNetQty = $HLQty / $CalHon;

	$stmt_LOTS = $iconn->query($sql_LOTS);
	$ROW_LOTS = $stmt_LOTS->fetch_array();

	$LotNumbers = $ROW_LOTS['LotNumber'];
	$PurchaseCount = $ROW_LOTS['PurchaseCount'];
	$PurchaseWeight = $ROW_LOTS['PurchaseWeight'];
	$LotNumbers1 = $ROW_LOTS['LotNumber'];
	$AvgNetCount = $ROW_LOTS['AvgNetCount'];
	$ProductionWeight = $ROW_LOTS['ProductionWeight'];

	if ($ProductionWeight != 0) {
		$LotWeight = $ProductionWeight;
	} else {
		$LotWeight = $PurchaseWeight;
	}
	$DataRow['WAVA'] = $ROW_LOTS['WAVA'];
	$DataRow['CAVA'] = $ROW_LOTS['CAVA'];
	$DataRow['RmAvgCount'] = $ROW_LOTS['RmAvgCount'];
	$DataRow['LotNumber'] = $LotNumbers;
	$DataRow['PurchaseCount'] = $PurchaseCount;
	$DataRow['PurchaseWeight'] = $PurchaseWeight;
	$DataRow['CountNetQty'] = $AvgNetCount;
	$DataRow['ProductionWeight'] = $LotWeight;
	return $DataRow;
}

function getTableHead($Grades)
{
	global $ShowDate;
	$text = 2;
	$content = '';
	$content .= '<table id="ConsutaBPM" border="1" cellspacing="0" cellpadding="1">
				<tr>
					<th align="CENTER"><B>Grading Machine</B></font></th>
					<th align="CENTER"><B>Rm Date </B></font></th>
					<th align="CENTER"><B>Grading Date </B></font></th>
					<th align="CENTER"><B>Lot No</B></font></th>
					<th align="CENTER"><B>Grader Name</B></font></th>
					<th align="CENT-R"><B>Agent Name</B></font></th>
					<th align="CENTER"><B>Rm Count</B></font></th>
					<th align="CENTER"><B>Rm Qty</B></font></th>
					<th align="CENTER"><B>HL Count/KG</B></font></th>
					<th align="CENTER"><B>HL Qty</B></font></th>';
	foreach ($Grades as $RowCosting) {
		$content .= '<th  align="CENTER"><B>' . $RowCosting['Grade'] . '</B></font></th>';
	}
	$content .= '
					<th align="CENTER"><B>Grading Total</B></font></th>
					<th align="CENTER"><B>Grading Yield</B></font></th>
					<th align="CENTER"><B>Variation HL To Grading</B></font></th>
					<th align="CENTER"><B>CountNet Yield Perc</B></font></th>
					<th align="CENTER"><B>Standard Yield</B></font></th>
					<th align="CENTER"><B>Avg Count</B></font></th>
					<th align="CENTER"><B>Diff Count</B></font></th>
				</tr>';
	return $content;
}

function getStandardYield($PurhaseCount, $PurchaseDate)
{
	global $iconn;

	// $sqlSYield = "SELECT * FROM `RmStandardYields` WHERE Type='HL' and  EffectiveDate<='$PurchaseDate' AND '$PurhaseCount'  BETWEEN LowerCount AND UpperCount order by ID desc limit 1";
	// $sqlSYield = "SELECT * FROM `RmStandardYields` WHERE Type='HL' and EffectedDateID='$DateID' 
	// AND (($PurhaseCount BETWEEN LowerCount AND UpperCount) or ($PurhaseCount >= LowerCount )) order by ID  desc limit 1";
	$yields = [];
	$counts = explode(',', $PurhaseCount);

foreach ($counts as $count) {
    $sqlSYield = "SELECT Yield 
            FROM RmStandardYields 
            WHERE Type = 'HL' 
              AND EffectiveDate <= '$PurchaseDate' 
              AND $count BETWEEN LowerCount AND UpperCount 
            ORDER BY ID DESC 
            LIMIT 1";

   $stmtSYield = $iconn->query($sqlSYield);
   $rowSYield = $stmtSYield->fetch_array();

	// if ($rowSYield) {
	$yields[] = $rowSYield['Yield'];
}
	$averageYield = 0;
if (count($yields) > 0) {
    $averageYield = array_sum($yields) / count($yields);
}
	// $stmtSYield = $iconn->query($sqlSYield);
	// // echo $iconn->error;
	// $rowSYield = $stmtSYield->fetch_array();

	// if ($rowSYield) {
	$StandardYeild = $averageYield;
	// }
	return $StandardYeild;
}
?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript">
	$("#btnPrint").live("click", function () {


		divToPrint = document.getElementById("ConsutaBPM");
		var date = "<?php echo $ShowDate ?>";
		var originalTitle = "GRADING DETAILS - " + date;
		var printWindow = window.open('', '', 'height=600,width=1000');

		// printWindow.document.write(date.outerHTML);
		printWindow.document.write(originalTitle);
		printWindow.document.write(divToPrint.outerHTML);
		printWindow.document.close();
		printWindow.print();
	});
</script>