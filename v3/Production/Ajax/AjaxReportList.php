<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/UrlEncode.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

$api = API_HOTOFINISHED_REPORT;
if ($methodType == "yearChange") {

    $fyYear  = intval($_POST["FYYear"]);
    $plantID = trim($_POST["PlantID"]);

    $fromDate = $fyYear . "-04-01";
    $toDate   = ($fyYear + 1) . "-03-31";

    

    $bodyContent = array(
        'FromDate' => $fromDate,
        'ToDate'   => $toDate,
        'DataType' => trim($_POST["DataType"]),
        'PlantID'  => trim($_POST["PlantID"])

    );
	// echo print_r($bodyContent);
	// exit;
	//error_log(print_r($bodyContent, true));

    $result = CallAPI($api, "GET", "", $bodyContent);
	// echo print_r($result);
	// exit;
// error_log(print_r($bodyContent, true));
	header('Content-Type: application/json');
	$response = array('ArrivalsList' => $result->ArrivalsList, 'Error' => $error, 'Msg' => $msg);

	echo json_encode($response);
	exit;

	//echo print_r($response);
	// exit;

}
else if($methodType == "dateChange") {
	$api = API_HOTOFINISHED_REPORT;

	$bodyContent = array(
		'ArrivalDate' => trim($_POST["ArrivalDate"]),
		'DataType' => trim($_POST["DataType"]),
		'PlantID' => trim($_POST["PlantID"])
	);

	$result = CallAPI($api, "GET", "", $bodyContent);
	// echo json_encode($result);exit;

	header('Content-Type: application/json');
	$response = array('ArrivalsList' => $result->ArrivalsList, 'Error' => $error, 'Msg' => $msg);

	echo json_encode($response);




}else if($methodType == "DatesChange") {
	$api = API_HOTOFINISHED_REPORT;
	$FromDate = $_POST["FromDate"];
	$ToDate = $_POST["ToDate"];
	if(!$FromDate && !$ToDate && $_POST["FYYear"]){		
		$fyYear  = intval($_POST["FYYear"]);

		$FromDate = $fyYear . "-04-01";
		$ToDate   = ($fyYear + 1) . "-03-31";
	}


	$bodyContent = array(
		'FromDate' => trim($FromDate),
		'ToDate' => trim($ToDate),
		'DataType' => trim($_POST["DataType"]),
		'PlantID' => trim($_POST["PlantID"])
	);
	$result = CallAPI($api, "GET", "", $bodyContent);
	header('Content-Type: application/json');
	$response = array('ArrivalsList' => $result->ArrivalsList, 'Error' => $error, 'Msg' => $msg);
	echo json_encode($response);
	exit;

}  else {


	$Type = $_POST['Type'];
	$Month = $_POST['Month'];
	$MonthName = $_POST['Month'];
	if ($Type == 1) {
		$Month = sprintf("%02d", $Month);
		$MonthName = date("F", mktime(0, 0, 0, $Month, 10));
	}
	$Year = $_POST['Year'];

	$PsID = $_POST['PsId'];
	$AccessType = $_SESSION['AccessType'];
	$DeptID = $_SESSION['DeptID'];

	// echo $DeptID;
	// exit;
	if ($DeptID == 10) {
		$ReportQuery = "SELECT * FROM `ProdAvalReports` where  Status=1 order by ReportName Asc";
	} else {
		$ReportQuery = "SELECT * FROM `ProdAvalReports` where  Status=1";
	}

	$ReportExec = $iconn->query($ReportQuery);
	// $errorInfo = $ReportExec->errorInfo();
	// var_dump($errorInfo);
}
?>
<?php 

if($methodType == "dateChange") {	

} else {
?>

<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

	<thead class="thead-light">

		<?php if ($Type == 1) { ?>

			<th align="CENTER">Report Name</th>
			<th align="CENTER">Month</th>
			<th align="CENTER">Year</th>
			<th align="CENTER">Actions</th>

		<?php } else { ?>

			<th align="CENTER">Report Name</th>
			<th align="CENTER">Start Date</th>
			<th align="CENTER">End Date</th>
			<th align="CENTER">Actions</th>

		<?php } ?>
	</thead>
	<tbody>



		<?php
		while ($RowReport = $ReportExec->fetch_array()) {
			$QueryParms = 'Month=' . $Month . '&Year=' . $Year . '&PsID=' . $PsID . '&Type=' . $Type;
			$QueryParms = urlEncode64($QueryParms);

		?>

			<tr>

				<td align="left" width="10%">
					<font size="'.$text.'"><B><?php echo $RowReport['ReportName']; ?></B></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><B><?php echo $MonthName; ?></B></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><B><?php echo $Year; ?></B></font>
				</td>

				<td align="left" width="5%">
					<font size="'.$text.'">
						<!--<a  target="_blank" href="<?php echo $BaseUrl . $RowReport['Path'] .
															$RowReport['Url'] . '?Q=' . $QueryParms; ?>"class="btn btn-success">Print</a>-->
						<a target="_blank" href="<?php echo $_SESSION["SideBarPath"] . $RowReport['Path'] .
														$RowReport['Url'] . '?Q=' . $QueryParms; ?>"
							class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
							title="" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>

					</font>



				</td>

			</tr>


		<?php } ?>
	</tbody>
</table>

<?php } ?> 