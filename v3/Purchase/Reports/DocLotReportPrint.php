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
// session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
//include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php"); 

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
$SType = $_GET['SType'];

$checkDate = '2023-10-26'; // till this date Data is present in an old Table

if ($PsID != '') {
	$PsID = $_GET['PsID'];
	$Query = "and PurchaseStationID='$PsID'";
} else {
	$Query = "";
}

if ($SType != '') {
	$Query .= "and RADS.Species ='$SType'";
}




echo $LotListTable = getP_TABLE($iconn);



function getP_TABLE($iconn)
{

	// $date_end=$date.' 23:59:59';
	global $Query, $Month, $Year, $Type, $checkDate;

	if ($Type == 1) {
		$Query = "month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
	} else {
		$Query = "PurchaseDate between '$Month'  and '$Year'  $Query";
	}

	$sqlFields = "LotNumber,RADS.Species AS VarietyName,PurchaseCount,PurchaseWeight, PurchaseDate, RLD.CreatedDateTime, 
	RLD.FarmAddress, PurchaseType,RUL.UserName,VehicalNumber,AbStatus ,RUAG.UserName AS AgentName";

	$tablename = '';
	if ($Year < $checkDate && $Month < $checkDate) {
		$tablename = '`RmLotDetatilsOld`';
	} else {
		$tablename = '`RmLotDetatils`';
		$sqlFields .= ", BreakupID, SupplierID";
	}

	$SqlLotDetails = "select $sqlFields
	from $tablename AS RLD 
	LEFT JOIN RmPurchaseCategory as RMC ON RMC.CatID=RLD.PurchaseCategoryID 
	LEFT JOIN RmUsersList as RUL ON RUL.ID=RLD.GraderID
	LEFT JOIN RmUsersList as RUAG ON RUAG.ID=RLD.AgentID
	LEFT JOIN RmVehicalDetails as RVD ON RVD.ID=RLD.VehicalID
	LEFT JOIN RmArrivalDetatils as RADS ON RADS.RmArrivalID=RLD.RmReferenceID
	LEFT JOIN RmBillDetails as RBD ON RBD.ArrivalID=RLD.RmLotID 
	LEFT JOIN (Select LotID, Group_Concat(AbStatus) as AbStatus from QcRmAbReport GROUP BY LotID) as QAR ON QAR.LotID=RLD.RmLotID
	WHERE $Query
	ORDER BY PurchaseDate ASC,LotNumber ASC";

	// ECHO $SqlLotDetails;  exit;

	$StmtLotDetails = $iconn->query($SqlLotDetails);

	$content = '';


	//$PO_TMSP = strtotime($date);
	//$PurchaseDate = date("d-m-Y", $PO_TMSP);

	$size = 40;

	$size1 = 60;
	$TextL = 5;
	$TextS = 2;


	$content .= '<table border="1" width="100%" cellspacing="0" cellpadding="3">	

						
				

				

				<thead>  

        

                <th>SNo</th>
                <th>Lot Number</th>
                <th>Variety</th>
                <th>AB Status </th>
                <th>Purchase Date</th>
				<th>Purchase Type</th>
                <th>Grader</th>
                <th>Agent Name</th>
                <th>Bill Name </th>
                <th>Vehical</th>
				<th>Farm Location</th>
				<th>Weight(Kgs)</th>
				<th>Count</th>
				</thead>';


	$C = 0;
	$SNo = 1;
	$TotalWeight = 0;
	while ($RowLotDetails = $StmtLotDetails->fetch_array()) {

		$LotWeight = $RowLotDetails['PurchaseWeight'];
		$TotalWeight = $TotalWeight + $LotWeight;
		$PurchaseDate = date("d-m-Y", strtotime($RowLotDetails['PurchaseDate']));
		$LotNumber = $RowLotDetails['LotNumber'];
		$PurchaseType = $RowLotDetails['PurchaseType'];
		$VehicalNumber = $RowLotDetails['VehicalNumber'];
		$Grader = $RowLotDetails['UserName'];
		$PurchaseCount = $RowLotDetails['PurchaseCount'];
		$FarmAddress = $RowLotDetails['FarmAddress'];
		$AbStatus = $RowLotDetails['AbStatus'];
		$SupplierID = $RowLotDetails['SupplierID'] ? $RowLotDetails['SupplierID'] : '4'; // SupplierID = 4 has FinancierName ='Others'
		$BreakUpID = $RowLotDetails['BreakUpID'];
		$AgentName = $RowLotDetails['AgentName'];
		$VarietyName = $RowLotDetails['VarietyName'];


		$SqlABReport = "SELECT Group_Concat(Name) As Name FROM `QcABOptions` WHERE OptID IN (" . $AbStatus . ")";
		$StmtABReport = $iconn->query($SqlABReport);
		if ($StmtABReport->num_rows > 0) {
			$RowABReport = $StmtABReport->fetch_assoc();
			$ABStatus = $RowABReport['Name'];
		}


		$SqlFinanciers = "SELECT * FROM `RmSuppliers` WHERE ID='$SupplierID'";
		$StmtFinanciers = $iconn->query($SqlFinanciers);
		$RowFinanciers = $StmtFinanciers->fetch_array();
		$FinancierName = $RowFinanciers['FinancierName'];


		$Variety = getVariety($BreakUpID);



		$content .= '
			
				
				<tr>  

              

				<td>' . $SNo . '</td>
				<td >' . $LotNumber . '</td>
				<td >' . $VarietyName . '</td>
				<td >' . $ABStatus . '</td>
				<td  >' . $PurchaseDate . '</td>
				<td  >' . $PurchaseType . '</td>
				<td  >' . $Grader . '</td>
				<td  >' . $AgentName . '</td>
				<td  >' . $FinancierName . '</td>
				<td  >' . $VehicalNumber . '</td>
                <td  >' . $FarmAddress . '</td>
                <td  >' . $LotWeight . '</td>
                <td  >' . $PurchaseCount . '</td>
                

				


               </tr>';

		$C++;
		$SNo++;
	}


	$content .= '

				
		<tr> 
           		

                <th >Total Lots</th>
                <th>' . $C . '</th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>Total Weight(Kgs)</th>
               

				<th>' . $TotalWeight . ' Kgs</th>
				 <th></th>

   
               </tr>';

	$content .= '
		
		</table>';






	return $content;
}

function getVariety($BreakUpID)
{
	global $iconn;
	$SqlFinanciers = "SELECT * FROM `RmArrivalBreakUpDetails` WHERE ArrivalBrkUpID='$BreakUpID'";
	$StmtFinanciers = $iconn->query($SqlFinanciers);
	$RowFinanciers = $StmtFinanciers->fetch_array();
	return $Variety = $RowFinanciers['Variety'];
}

?>