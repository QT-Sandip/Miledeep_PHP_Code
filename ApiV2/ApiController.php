<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// ini_set('log_errors', 1);
// ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/api_php_errors.log');


include_once 'Config/Config.php';
include_once 'Global/Util.php';
// include_once 'App/Controller/AppUtilController';
include_once 'App/Controller/AppUtilController.php';


class ApiController
{
	private $db;
	private $restApi;
	private $db_table = "ScrmPIData";
	private $appUtilController;


	// Db connection
	public function __construct($db)
	{
		$this->db = $db;
		$this->restApi = new REST();
		$this->appUtilController = new AppUtilController($this->db); //uma
	}
	// GET ALL

	/**********************************Dont Change Above************************************************* */

	function getToken()
	{
		$authToken = $this->restApi->getRequestHeader();
		return $payload = Token::Verify($authToken);
	}
	public function getDashBoard($USER_ID)
	{

		$Token = $this->getToken();
		$DeptID = $Token['DeptID'];

		$SqlDashBoard = "SELECT Title,TypeID,Icon,Link,Package,ClassType,SubLink  FROM AppDashboards WHERE DeptID='$DeptID' or DeptID=0  ORDER by SortOrder";
		$sqlca_c_user = mysql_query($SqlDashBoard);

		if (mysql_num_rows($sqlca_c_user) != '0') {
			while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
				$result['Title'] = $rlt['Title'];
				$result['TypeId'] = $rlt['TypeID'];
				$result['Icon'] = $rlt['Icon'];
				$result['Link'] = $rlt['Package'] . $rlt['Link'];
				$result['SubLink'] = $rlt['Package'] . $rlt['SubLink'];
				$result['ClassType'] = $rlt['ClassType'];
				$Data[] = $result;
			}


			$DropDownData = $this->getDropDownData(1);


			$temp = array(
				'error' => null,
				"Data" => $Data,
				"DropDownData" => $DropDownData,
			);
			$this->response($this->json($temp), 200);
		} else {
			$temp = array('error' => "No Access To User Please Contact Admin");
			$this->response($this->json($temp), 200);
		}
	}





	//uma
	public function getEmpList()
	{

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$sqlca = "SELECT HRM_EMPLOYEE_LIST.EMPLOYEE_ID, HRM_EMPLOYEE_LIST.EMPLOYEE_NAME, 
	HRM_EMPLOYEE_LIST.EMPLOYEE_DESIGNATION,HRM_EMPLOYEE_LIST.EMPLOYEE_CODE,
	HRM_EMPLOYEE_LIST.PHONE_NUMBER,HRM_EMPLOYEE_LIST.USER_IMAGE,HRM_EMPLOYEE_LIST.UNIT,
	HRM_DEPARTMENT_MASTER.DEPARTMENT_NAME,HRM_SUB_DEPARTMENT_MASTER.SUB_DEPARTMENT_NAME
FROM HRM_EMPLOYEE_LIST
LEFT JOIN HRM_DEPARTMENT_MASTER ON HRM_EMPLOYEE_LIST.DEPARTMENT_ID=HRM_DEPARTMENT_MASTER.DEPARTMENT_ID 
LEFT JOIN HRM_SUB_DEPARTMENT_MASTER  ON      HRM_SUB_DEPARTMENT_MASTER.SUB_ID = HRM_EMPLOYEE_LIST.SUB_ID";
		$sqlca_c_user = mysql_query($sqlca);
		if (mysql_num_rows($sqlca_c_user) == '0') {
			$temp = array('status' => $STATUS_FAILURE, "msg" => "No Lots Found");
			$this->response($this->json($temp), 200);
		} else {

			while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
				$result[] = $rlt;
			}
			$temp = array('status' => $STATUS_SUCCESS, "InwardList" => $result, "msg" => "Successful", "ProcessingUnits" => $ary_pu);
			$this->response($this->json($temp), 200);
		}
	}




	public function getBioMetricDevices()
	{



		$sql = "SELECT * FROM `HrmBioMetricDevices`";
		$sqlca_bplco = mysql_query($sql, $this->db);

		while ($row = mysql_fetch_array($sqlca_bplco, MYSQL_ASSOC)) {
			$row['DeviceUserID'] = 'admin';
			$row['DevicePassword'] = 'PIPL9910';
			$row['DevicePort'] = '0';
			$Data[] = $row;
		}

		$records = array('Error' => null, "Msg" => "", "Data" => $Data);
		$this->response($this->json($records), 200);
	}



	public function login($USER_LOGIN_ID, $USER_PASSWORD, $device_id)
	{



		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$ss_c_user = "SELECT * FROM `AdminLoginDetails`
		 WHERE UserLoginID='$USER_LOGIN_ID' and UserPassword='$USER_PASSWORD'";

		$sqlca_c_user = mysql_query($ss_c_user);


		if (mysql_num_rows($sqlca_c_user) == '0') {
			$msg = array('status' => $STATUS_FAILURE, "msg" => "No User Exist Please Register");
			$this->response($this->json($msg), 200);
		} else {

			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$DATE_TIME = $dateTime->format("Y-m-d  H:i:s");

			// 


			$result = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC);
			$UserID = $records['USER_ID'] = $result['UserID'];
			$records['DEPARTMENT_ID'] = $result['DeptID'];
			$records['SUB_DEPT_ID'] = $result['SubDeptID'];
			$records['USER_NAME'] = $result['UserName'];
			$records['USER_TYPE'] = $result['UserType'];
			$records['UNIT_ID'] = $result['UnitID'];
			$records['ACCESS_TYPE'] = $result['AccessType'];
			$records['status'] = "Success";
			$records['msg'] = "User Login";
			$sql_update = "UPDATE `AdminLoginDetails` SET `DeviceID`='$device_id',
		`LastLogin`='$DATE_TIME' WHERE `UserID`='$UserID'";
			$sqlca_update = mysql_query($sql_update, $this->db);

			$this->response($this->json($records), 200);
		}
	}


	public function RiseIssue()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$AryCurrentDate = $this->getCurrentDateTime();

		$PostedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];


		$LotID = $data['LotID'];
		$PostedUserID = $data['PostedUserID'];
		$IssueID = $data['IssueID'];
		$Remarks = $data['Remarks'];
		$Description = $data['Desc'];




		$SqlTickets = "INSERT INTO `AdminTickets` 
				( `LotID`, `PostedUserID`, 
				`PostedDateTime`, `IssueID`, `Remarks`,`Description`) VALUES  
				('$LotID','$PostedUserID',	
				'$PostedDateTime','$IssueID','$Remarks','$Description')";

		$StmtTickets = mysql_query($SqlTickets, $this->db);


		if ($StmtTickets) {
			$temp = array('Error' => null, "Msg" => "Tickets Posted");
			$this->response($this->json($temp), 200);
		} else {
			$records = array('Error' => "Unable To Rise Ticket. Error " . mysql_errno($this->db));
			$this->response($this->json($records), 200);
		}
	}



	//ROBO


	public function PdBilling()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$AryCurrentDate = $this->getCurrentDateTime();

		$PostedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];


		$ContractorId = $data['ContractorId'];
		$SubContractorId = $data['SubContractorId'];
		$LotId = $data['LotId'];
		$VarietyId = $data['VarietyId'];
		$Count = $data['Count'];
		$Date = $data['Date'];
		$Quantity = $data['Quantity'];
		$Category = $data['Category'];
		$NoOfWorkers = $data['NoOfWorkers'];
		$PostedUserID = $data['PostedUserID'];




		$SqlPdBilling = "INSERT INTO `PpcInHousePDBilling`(`ContractorId`, `SubContractorId`, `LotId`, `VarietyId`, `Count`, `Date`, `Quantity`, `Category`, `NoOfWorkers`, `PostedUserID`, `PostedDateTime`) VALUES
				('$ContractorId','$SubContractorId',	
				'$LotId','$VarietyId','$Count','$Date','$Quantity','$Category','$NoOfWorkers','$PostedUserID','$PostedDateTime')";

		$StmtPdBilling = mysql_query($SqlPdBilling, $this->db);


		if ($StmtPdBilling) {
			$temp = array('Error' => null, "Msg" => "Sucessfully added ");
			$this->response($this->json($temp), 200);
		} else {
			$records = array('Error' => "Unable To add data. Error " . mysql_errno($this->db));
			$this->response($this->json($records), 200);
		}
	}


	//robo

	public function createLot()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$PostedUserID = $data['PostedUserID'];
		$LotNumber = $data['LotNumber'];
		$PurchaseStationID = $data['PurchaseStationID'];
		$VehicalID = $data['VehicalID'];
		$AgentID = $data['AgentID'];
		$DriverID = $data['DriverID'];
		$GraderID = $data['GraderID'];

		$PurchaseCategoryID = $data['PurchaseCategoryID'];
		$PurchaseCount = $data['PurchaseCount'];
		$PurchaseWeight = $data['PurchaseWeight'];
		$FarmAddress = $data['FarmAddress'];
		$PurchaseDate = date("Y-m-d", strtotime($data['PurchaseDate']));





		$LotNumber = $this->getFormatedLotNumber($PurchaseStationID, $LotNumber);

		$LotAval = $this->checkLotNoAval($PurchaseDate, $LotNumber);

		if (!$LotAval) {
			$records = array('Error' => "Lot Already Exist");
			$this->response($this->json($records), 200);
		}

		/*else if ($PostedUserID == '') {
																		$records = array('error' => "Session Expired,Please Login and Try Again");
																		$this->response($this->json($records), 200);
																		}*/


		$AryCurrentDate = $this->getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];




		$SqlPostLot = "INSERT INTO `RmLotDetatils` 
				( `PostedUserID`, `LotNumber`, 
				`PurchaseStationID`, `VehicalID`, `AgentID`, 
				`DriverID`, `GraderID`,
				`PurchaseCategoryID`, `PurchaseCount`, `PurchaseWeight`, 
				`FarmAddress`, `CreatedDateTime`, `PurchaseDate`,
				`Year`, `LotStatus`) VALUES  
				('$PostedUserID','$LotNumber',	
				'$PurchaseStationID','$VehicalID','$AgentID',
				'$DriverID','$GraderID',
				'$PurchaseCategoryID','$PurchaseCount','$PurchaseWeight',
				'$FarmAddress','$CreatedDateTime','$PurchaseDate',
				'$Year','1')";

		$StmtPostLot = mysql_query($SqlPostLot, $this->db);


		if ($StmtPostLot) {
			$temp = array('Error' => null, "msg" => "Lot Posted Successful");
			$this->response($this->json($temp), 200);
		} else {
			$records = array('Error' => "Unable To Post Lot. Error " . mysql_errno($this->db));
			$this->response($this->json($records), 200);
		}
	}

	public function getFormatedLotNumber($PurchaseStationID, $LotNumber)
	{
		$SqlLotCode = "SELECT LotCode FROM `RmPurchaseStations` 
			WHERE PurchaseStationID='$PurchaseStationID'";
		$StmtLotCode = mysql_query($SqlLotCode);


		if (mysql_num_rows($StmtLotCode) == '0') {
			$LotNumber = 0;
		} else {
			$RowData = mysql_fetch_array($StmtLotCode, MYSQL_ASSOC);
			$LotCode = $RowData['LotCode'];
			$LotNumber = str_replace("O", "0", $LotNumber);
			$LotNumber = ltrim($LotNumber, "0");
			$LotNumber = preg_replace("/[^0-9]/", "", $LotNumber);
			$LotNumber = sprintf('%04d', $LotNumber);
			$LotNumber = $LotCode . $LotNumber;
		}


		return $LotNumber;
	}


	public function createBooking()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$PostedUserID = $data['PostedUserID'];
		$BookingNumber = $data['BookingNumber'];
		$BookingStationID = $data['BookingStationID'];
		$VehicalID = $data['VehicalID'];
		$AgentID = $data['AgentID'];
		$DriverID = $data['DriverID'];
		$GraderID = $data['GraderID'];

		$PurchaseCategoryID = $data['PurchaseCategoryID'];
		$BookingCount = $data['BookingCount'];
		$BookingWeight = $data['BookingWeight'];
		$FarmAddress = $data['FarmAddress'];
		$BookingDate = date("Y-m-d", strtotime($data['BookingDate']));





		$BookingNumber = $this->getFormatedBookingNumber($BookingStationID, $BookingNumber);

		$LotAval = $this->checkLotNoAval($BookingDate, $BookingNumber);

		if (!$LotAval) {
			$records = array('error' => "Booking Number Already Exist");
			$this->response($this->json($records), 200);
		} else if ($PostedUserID == '') {
			$records = array('error' => "Session Expired,Please Login and Try Again");
			$this->response($this->json($records), 200);
		}


		$AryCurrentDate = $this->getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];




		$SqlPostLot = "INSERT INTO `RmBookingDetatils`
				(`PostedUserID`, `BookingNumber`, 
				`BookingStationID`, `VehicalID`, `AgentID`,
				`DriverID`, `GraderID`, `PurchaseCategoryID`,
				`BookingCount`, `BookingWeight`, `FarmAddress`,
				`CreatedDateTime`, `BookingDate`, `Year`,
				`LotStatus`) VALUES  
				('$PostedUserID','$BookingNumber',	
				'$BookingStationID','$VehicalID','$AgentID',
				'$DriverID','$GraderID',
				'$PurchaseCategoryID','$BookingCount','$BookingWeight',
				'$FarmAddress','$CreatedDateTime','$BookingDate',
				'$Year','1')";
		//echo $SqlPostLot;
		//exit;
		$StmtPostLot = mysql_query($SqlPostLot, $this->db);


		if ($StmtPostLot) {
			$temp = array('error' => null, "msg" => "Lot Posted Successful");
			$this->response($this->json($temp), 200);
		} else {
			$records = array('error' => "Unable To Post Lot. Error " . mysql_errno($this->db));
			$this->response($this->json($records), 200);
		}
	}


	public function getFormatedBookingNumber($BookingStationID, $BookingNumber)
	{
		$SqlLotCode = "SELECT LotCode FROM `RmPurchaseStations` 
			WHERE PurchaseStationID='$BookingStationID'";
		$StmtLotCode = mysql_query($SqlLotCode);


		if (mysql_num_rows($StmtLotCode) == '0') {
			$LotNumber = 0;
		} else {
			$RowData = mysql_fetch_array($StmtLotCode, MYSQL_ASSOC);
			$LotCode = $RowData['LotCode'];
			$BookingNumber = str_replace("O", "0", $BookingNumber);
			$BookingNumber = ltrim($BookingNumber, "0");
			$BookingNumber = preg_replace("/[^0-9]/", "", $BookingNumber);
			$BookingNumber = sprintf('%04d', $BookingNumber);
			$BookingNumber = $LotCode . $BookingNumber;
		}


		return $BookingNumber;
	}


	public function checkBookingNoAval($BookingDate, $BookingNumber, $LotId)
	{
		//echo $LotId;
		if ($LotId != '') {
			$Qry = "and RmBookingID!='$LotId'";
		}

		$CheckYear = explode('-', $BookingDate);

		if ($CheckYear[1] > 3) {
			$DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
			$DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
		} else {
			$DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
			$DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
		}



		$SqlLotDetails = "SELECT * FROM `RmBookingDetatils`
			WHERE BookingNumber='$BookingNumber' and 
			BookingDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";

		$StmtLotDtls = mysql_query($SqlLotDetails);
		if (mysql_num_rows($StmtLotDtls) == '0') {
			return true;
		} else {
			return false;
		}
	}


	public function checkLotNoAval($PurchaseDate, $LotNumber, $LotId)
	{
		//echo $LotId;
		if ($LotId != '') {
			$Qry = "and RmLotID!='$LotId'";
		}

		$CheckYear = explode('-', $PurchaseDate);

		if ($CheckYear[1] > 3) {
			$DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
			$DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
		} else {
			$DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
			$DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
		}



		$SqlLotDetails = "SELECT * FROM `RmLotDetatils`
			WHERE LotNumber='$LotNumber' and 
			PurchaseDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";

		$StmtLotDtls = mysql_query($SqlLotDetails);
		if (mysql_num_rows($StmtLotDtls) == '0') {
			return true;
		} else {
			return false;
		}
	}

	public function getCurrentDateTime()
	{
		$TimeFormat = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$TimeStamp = $TimeFormat->format("Y-m-d  H:i:s");
		$Year = $TimeFormat->format("Y");
		$AryCurrentDate['TimeStamp'] = $TimeStamp;
		$AryCurrentDate['Year'] = $Year;

		return $AryCurrentDate;
	}


	public function PostRmDefects()
	{

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);
		$LOT_ID = $data['LOT_ID'];
		$USER_ID = $data['USER_ID'];
		$ARY_COUNTS = $data['DATA'];
		$ARY_COUNTS = json_decode($ARY_COUNTS, true);


		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$SUBMISSION_DATE = $dateTime->format("Y-m-d  H:i:s");


		foreach ($ARY_COUNTS as $value) {
			$ROW_COUNT++;
			$S_LOT_NO = $S_LOT_NO . '-' . $ROW_COUNT;
			$WEIGHT_IN_KGS = $value['TotalWeight'];
			$DefectName = $value['DefectName'];
			$ss_post = "INSERT INTO `RM_DEFECTS_DETAILS`(`LOT_ID`, `USER_ID`, `SUBMISSION_DATE`,
				 `NAME`, `WEIGHT`, `TUBS`, `COUNT`, `RATE`, `STATUS`)
				 VALUES ('$LOT_ID','$USER_ID','$SUBMISSION_DATE','$DefectName','$WEIGHT_IN_KGS',
				 '0','0','0','0')";

			$sqlca_post = mysql_query($ss_post, $this->db);
		}
		if ($sqlca_post) {

			$records = array('status' => $STATUS_SUCCESS, "msg" => "Lot Created Successful");
			$temp = array('status' => $STATUS_SUCCESS, "LOT_ID" => $LOT_ID . "", "InwardList" => $result, "msg" => "Successful", "ProcessingUnits" => $ary_pu);
			$this->response($this->json($temp), 200);
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Error occured Please Contact Admin ");
			$this->response($this->json($records), 200);
		}
	}


	public function getCurrentTimeStamp()
	{
		$TimeFormat = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$TimeStamp = $TimeFormat->format("Y-m-d  H:i:s");
		$Year = $TimeFormat->format("Y");
		$AryCurrentDate['TimeStamp'] = $TimeStamp;
		$AryCurrentDate['Year'] = $Year;

		return $AryCurrentDate;
	}


	public function postHlGradingReport()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$AryCurrentDate = $this->getCurrentTimeStamp();

		$PostedDate = $AryCurrentDate['TimeStamp'];




		$UserID = $data['UserID'];
		$LotID = $data['LotID'];
		$MachineID = $data['GradingMachine'];
		$GradingDate = $data['GradingDate'];
		$StartTime = $data['StartTime'];
		$EndTime = $data['EndTime'];
		$BreakTime = $data['BreakTime'];
		$Count = $data['Count'];
		$Weight = $data['Weight'];
		$NoCrates = $data['NoCrates'];
		$Mix = $data['Mix'];
		$StationID = $data['StationID'];
		$CreatedFrom = $data['CreatedFrom'];
		$field = $value = '';
		$where = " AND CreatedFrom=1";
		if (isset($CreatedFrom)) {
			$field = ',CreatedFrom';
			$value = ",$CreatedFrom";
			$where = " AND CreatedFrom=$CreatedFrom";
		}


		if ($Mix == 1) {
			$MixIds = $LotID;
			$LotIDAry = explode('_', $LotID);
		} else {
			$MixIds = 'NA';
			$LotIDAry[] = $LotID;
		}

		//print_r($LotIDAry);

		foreach ($LotIDAry as $LotID) {
			$SqlGradingReport = "SELECT count(*) as Total FROM 
		`PpcHLGradingDetails` where LotID='$LotID' $where";
			$StmtGradingReport = mysql_query($SqlGradingReport);
			$GradingReportRow = mysql_fetch_array($StmtGradingReport, MYSQL_ASSOC);

			$GradingReportRow['Total'];

			if ($GradingReportRow['Total'] == 0) {

				$HlGradingSql = "INSERT INTO `PpcHLGradingDetails` 
		 (`LotID`, `UserID`, `GradingDate`, `StartTime`, `EndTime`,
		 `BreakTime`, `PostedDate`, `MachineID`, `StationID`,`GradingData`,
		 `IsMix`, `MixIDs`, `Status`$field) 
			VALUES ('$LotID','$UserID','$GradingDate','$StartTime','$EndTime',
					'$BreakTime','$PostedDate','$MachineID','$StationID','','$Mix','$MixIds','1'$value)";

				$HlGradingSqlExec = mysql_query($HlGradingSql, $this->db);
			} else {
				$HlGradingSqlExec = false;
				$Msg = 'Report Already Exist';
			}
		}


		if ($HlGradingSqlExec) {
			$records = array('Error' => null, "Msg" => "Report Added Successful");
			$this->response($this->json($records), 200);
		} else {
			// echo mysql_errno($this->db);
			$records = array('Error' => 'Report Already Exist');
			$this->response($this->json($records), 200);
		}
	}


	public function postHlGradingBreakUp()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$AryCurrentDate = $this->getCurrentTimeStamp();

		$PostedDate = $AryCurrentDate['TimeStamp'];

		$UserID = $data['UserID'];
		$LotID = $data['LotID'];
		$Defect = $data['Defect'];
		$Count = $data['Count'];
		$Weight = $data['Weight'];
		$NoCrates = $data['NoCrates'];
		$Mix = $data['Mix'];




		$SqlGradingReport = "SELECT * FROM `PpcHLGradingDetails` where LotID='$LotID'  AND CreatedFrom=0";
		$StmtGradingReport = mysql_query($SqlGradingReport);
		$GradingReportRow = mysql_fetch_array($StmtGradingReport, MYSQL_ASSOC);
		$IsMix = $GradingReportRow['IsMix'];
		$MixIDs = $GradingReportRow['MixIDs'];

		if ($IsMix == 1) {
			$SqlGradingReport = "SELECT * FROM `PpcHLGradingBreakup` 
	where MixedID='$MixIDs' and HLCount='$Count'   AND CreatedFrom=0";
			$StmtGradingReport = mysql_query($SqlGradingReport);
			$RowCount = mysql_num_rows($StmtGradingReport);
		} else {
			$SqlGradingReport = "SELECT * FROM `PpcHLGradingBreakup` 
	where LotID='$LotID' and HLCount='$Count'   AND CreatedFrom=0";
			$StmtGradingReport = mysql_query($SqlGradingReport);
			$RowCount = mysql_num_rows($StmtGradingReport);
		}


		if ($RowCount > 0) {

			$GradingReportRow = mysql_fetch_array($StmtGradingReport, MYSQL_ASSOC);
			$HLWeight = $GradingReportRow['HLWeight'] + $Weight;
			$NoCrates = $GradingReportRow['NoCrates'] + $NoCrates;
			$DataID = $GradingReportRow['DataID'];

			$HlGradingSql = "Update  `PpcHLGradingBreakup` set 
	 HLWeight='$HLWeight' ,NoCrates='$NoCrates' where DataID ='$DataID' ";

			$HlGradingSqlExec = mysql_query($HlGradingSql, $this->db);
		} else {
			$HlGradingSql = "INSERT INTO `PpcHLGradingBreakup` 
		( `LotID`, `MixedID`, `HLWeight`, `HLCount`, `NoCrates`, `DefectID`,`PackingGrade`, `Usd`,CreatedFrom) VALUES 
		('$LotID','$MixIDs','$Weight','$Count','$NoCrates','$Defect','','',0)";

			$HlGradingSqlExec = mysql_query($HlGradingSql, $this->db);
		}


		if ($HlGradingSqlExec) {

			$this->getCostingBreakUp($LotID);
			$records = array('Error' => null, "Msg" => "Report Added Successful");
			$this->response($this->json($records), 200);
		} else {
			$records = array('Error' => 'Report Already Exist');
			$this->response($this->json($records), 200);
		}
	}


	public function postHOSOWDataWeb($UNIT_ID, $LOT_NO)
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("Y-m-d  H:i:s");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$USER_ID = $data['USER_ID'];
		$LOT_ID = $data['LOT_ID'];
		$HOSO_W_DATA = $data['HOSO_W_DATA'];
		$DATA_TYPE = $data['DATA_TYPE'];
		$DATA_TYPE = 'Headless Grading';
		$TYPE_ID = $data['TYPE_ID'];
		$MIX = $data['MIX'];
		$GradingMachine = $data['GradingMachine'];
		$From = $data['From'];

		$Head_On_Weightment_ID = "5";
		$Head_less_Weightment_ID = "6";
		$Head_On_Grading_ID = "7";
		$Head_less_Grading_ID = "8";




		if ($TYPE_ID == $Head_On_Weightment_ID) {
			$TABLE = 'PPC_WEIGHTMENT_HON';
		} else if ($TYPE_ID == $Head_less_Weightment_ID) {
			$TABLE = 'PPC_WEIGHTMENT_HL';
		} else if ($TYPE_ID == $Head_On_Grading_ID) {
			$TABLE = 'PPC_GRADING_HON';
		} else if ($TYPE_ID == $Head_less_Grading_ID) {
			$TABLE = 'PPC_GRADING_HL';
		}


		if ($MIX == 1) {
			$MixIds = $LOT_ID;
			$LOT_ID_ARY = explode('_', $LOT_ID);
		} else {
			$MixIds = 'NA';
			$LOT_ID_ARY[] = $LOT_ID;
		}

		foreach ($LOT_ID_ARY as $LOT_ID) {

			$sql_pu = "SELECT count(*) as TOTAL FROM `$TABLE` where LOT_ID='$LOT_ID' and DATA_TYPE='$DATA_TYPE'";
			$sqlca_pu = mysql_query($sql_pu);
			$rlt = mysql_fetch_array($sqlca_pu, MYSQL_ASSOC);

			if ($rlt['TOTAL'] == 0) {

				if ($TYPE_ID == $Head_less_Grading_ID) {
					$ss_post = "INSERT INTO `$TABLE`( `LOT_ID`,`DATA_TYPE`,`GRADING_MACHINE`,`USER_ID`, `SUBMISSION_DATE`,`IsMix`,`MixIds`,`HOSO_W_DATA`)
			 VALUES  ('$LOT_ID','$DATA_TYPE','$GradingMachine','$USER_ID','$dt','$MIX','$MixIds','$HOSO_W_DATA')";
				} else {
					$ss_post = "INSERT INTO `$TABLE`( `LOT_ID`,`DATA_TYPE`,`USER_ID`, `SUBMISSION_DATE`,`IsMix`,`MixIds`,`HOSO_W_DATA`)
			 VALUES  ('$LOT_ID','$DATA_TYPE','$USER_ID','$dt','$MIX','$MixIds','$HOSO_W_DATA')";
				}
				$sqlca_post = mysql_query($ss_post, $this->db);
			} else if ($From = 'Web') {

				if ($TYPE_ID == $Head_less_Grading_ID) {
					$ss_post = "Update `$TABLE` set HOSO_W_DATA='$HOSO_W_DATA' where LOT_ID='$LOT_ID'";
				} else {
				}
				$sqlca_post = mysql_query($ss_post, $this->db);
			} else {
				$sqlca_post = false;
				$msg = 'Report Already Exist';
			}
		}


		if ($sqlca_post) {
			$records = array('status' => $STATUS_SUCCESS, "msg" => "Successful");
			$this->response($this->json($records), 200);
		} else {
			// echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => $msg);
			$this->response($this->json($records), 200);
		}
	}

	public function postPendingGrading()
	{

		$LotID = intval($_GET['LotID']);


		$SqlGradingData = "SELECT * FROM `PpcHLGradingDetails` WHERE LotID='$LotID'  AND CreatedFrom=0";
		$StmtGradingData = mysql_query($SqlGradingData);
		$RowGradingData = mysql_fetch_array($StmtGradingData, MYSQL_ASSOC);
		$GradingData = $RowGradingData['GradingData'];


		$SqlGradesBreakUp = "select * from `PpcHLGradingBreakup` where LotID='$LotID'  AND CreatedFrom=0";
		$StmtGradesBradkUp = mysql_query($SqlGradesBreakUp, $this->db);
		$RowGradingBrk = mysql_fetch_array($StmtGradesBradkUp, MYSQL_ASSOC);

		if ($RowGradingBrk['LotID'] == '') {


			$this->postGradingBreakUp($LotID, $GradingData);
		} else {
			echo 'error';
		}
	}

	public function postGrading($UNIT_ID, $LOT_NO)
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$PostedDate = $dateTime->format("Y-m-d  h:i:s");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$UserID = $data['UserID'];

		$LotID = $data['LotID'];
		$StartTime = $data['StartTime'];
		$EndTime = $data['EndTime'];
		$BreakTime = $data['BreakTime'];
		$GradingDate = date("Y-m-d", strtotime($data['GradingDate']));
		//$GradingData = date("Y-m-d", strtotime($data['GradingData']));
		$GradingData = $data['GradingData'];
		$MachineID = $data['MachineID'];
		$TypeID = $data['TypeID'];



		$where = $feild = $value = '';
		if ($TypeID == '7') {
			$TABLE = 'PpcHonGradingDetails';
		} else if ($TypeID == '8') {
			$TABLE = 'PpcHLGradingDetails';
			$where = 'AND CreatedFrom=0';
			$feild = ',CreatedFrom';
			$value = ',0';
		}





		$SqlGradingData = "SELECT count(*) as TOTAL FROM `$TABLE` where LotID='$LotID' $where";
		$StmtGradingData = mysql_query($SqlGradingData);
		$RowGradingData = mysql_fetch_array($StmtGradingData, MYSQL_ASSOC);
		$IsGrades = false;

		if ($RowGradingData['TOTAL'] == 0) {

			if ($TypeID == '7') {
				$ss_post = "INSERT INTO `$TABLE`( `LotID`, `UserID`, `GradingDate`, `StartTime`, `EndTime`, `BreakTime`, `PostedDate`, `MachineID`, `GradingData`)
			 VALUES  ('$LotID','$UserID','$GradingDate','$StartTime','$EndTime','$BreakTime','$PostedDate','$MachineID','$GradingData')";
				$IsGrades = $this->postHonGradingBreakUp($LotID, $GradingData);
			} else {
				$ss_post = "INSERT INTO `$TABLE`( `LotID`, `UserID`, `GradingDate`, `StartTime`, `EndTime`, `BreakTime`, `PostedDate`, `MachineID`, `GradingData`$feild)
				VALUES  ('$LotID','$UserID','$GradingDate','$StartTime','$EndTime','$BreakTime','$PostedDate','$MachineID','$GradingData'$value)";
				$IsGrades = $this->postGradingBreakUp($LotID, $GradingData);
			}
			if ($IsGrades) {
				$sqlca_post = mysql_query($ss_post, $this->db);
			}
		} else {
			$sqlca_post = false;
			$msg = 'Report Already Exist';
		}


		if ($sqlca_post) {
			$records = array('status' => $STATUS_SUCCESS, "msg" => "Successful");
			$this->response($this->json($records), 200);
		} else {
			// echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => $msg);
			$this->response($this->json($records), 200);
		}
	}

	public function postGradingBreakUp($LotID, $GradingData)
	{

		$this->postGradingDefects($LotID, $GradingData);

		$GradingData = json_decode($GradingData, true);

		foreach ($GradingData as $value) {
			//$AryCounts[]=$value; 
			if (!in_array($value['Count'], $AryCounts, true)) {
				$AryCounts[] = $value['Count'];
			}
		}

		sort($AryCounts);

		foreach ($AryCounts as $HLCount) {

			if ($HLCount != 0 && $HLCount != 'Peeling' && $HLCount != 'g2') {

				$HLWeight = 0;

				foreach ($GradingData as $value) {
					if ($value['Count'] == $HLCount) {
						$HLWeight = $HLWeight + $value['NetWeight'];
					}
				}
				$SqlGradesBreakUp = "INSERT INTO `PpcHLGradingBreakup`(`LotID`,`HLWeight`,`HLCount`,CreatedFrom) 
			VALUES ('$LotID','$HLWeight','$HLCount',0)";
				$StmtGradesBradkUp = mysql_query($SqlGradesBreakUp, $this->db);
			}
		}
		if ($StmtGradesBradkUp) {
			$this->getCostingBreakUp($LotID);
			return true;
		} else {
			return false;
		}
	}



	function getCostingBreakUp($LotID)
	{

		//$LotID=13007;

		$SqlFinalGrading = "SELECT * FROM `PpcHLGradingBreakup` WHERE LotID='$LotID' AND CreatedFrom=0 order by HLCount ASC";
		$StmtFinalGrading = mysql_query($SqlFinalGrading, $this->db);


		$SqlRotDetails = "SELECT RAD.Species as SType,RLD.PurchaseDate FROM `RmArrivalDetatils` as RAD 
		LEFT JOIN  `RmLotDetatils` as RLD  on RAD.RmArrivalID=RLD.RmReferenceID
			left JOIN RmPurchaseStations as RPS  on RPS.PurchaseStationID=RLD.PurchaseStationID
			WHERE RmLotID='$LotID'";
		$StmtLotDetails = mysql_query($SqlRotDetails, $this->db);
		$RowLotDetails = mysql_fetch_array($StmtLotDetails, MYSQL_ASSOC);
		$SType = $RowLotDetails['SType'];
		$PurchaseDate = $RowLotDetails['PurchaseDate'];

		$SqlTrackId = "SELECT * FROM `SalesCostingRatesHistory` 
		where FromDate <= '$PurchaseDate' and SType='$SType'
		order by FromDate DESC, ID DESC  LIMIT 1";
		$StmtTrackId = mysql_query($SqlTrackId, $this->db);
		$RowTrackId = mysql_fetch_array($StmtTrackId, MYSQL_ASSOC);
		$TrackID = $RowTrackId['ID'];


		while ($RowFinalCounts = mysql_fetch_array($StmtFinalGrading, MYSQL_ASSOC)) {

			$HLCount = $RowFinalCounts['HLCount'];
			$HlWeight = $RowFinalCounts['HlWeight'];

			$SqlCostingRates = "SELECT * FROM SalesCostingRates WHERE TrackID='$TrackID' 
	and LowerLimit <= '$HLCount' and UpperLimit >= '$HLCount'";
			$StmtCostingRates = mysql_query($SqlCostingRates, $this->db);
			$RowCostingRates = mysql_fetch_array($StmtCostingRates, MYSQL_ASSOC);

			$Usd = $RowCostingRates['USD'];
			$Grade = $RowCostingRates['Grade'];

			$GradesUsdQuery = "UPDATE PpcHLGradingBreakup set PackingGrade='$Grade',Usd='$Usd' 
	where LotID='$LotID' and HLCount='$HLCount'";
			$GradesUsdExec = mysql_query($GradesUsdQuery, $this->db);
		}
	}


	function getMbilldata($PoID)
	{
		$query = "SELECT * FROM `ScrmClearingAgentBreakupDetails` WHERE PoID='$PoID'";
		//return $query; 
		$StmtScrmClearingAgentBreakupDetailsData = mysql_query($query, $this->db);
		$data = array();
		while ($RowClearingAgentBreakupDetailData = mysql_fetch_array($StmtScrmClearingAgentBreakupDetailsData, MYSQL_ASSOC)) {
			$datakey = $RowClearingAgentBreakupDetailData["TypeofCharges"];
			//OrtherCharges Others
			if ($datakey == "OrtherCharges" || $datakey == "Others") {
				$datakey = "OtherCharges";
			}
			//return $datakey; 
			$data[$datakey][] = $RowClearingAgentBreakupDetailData;
		}
		return $data;
	}

	public function updateCostingRates()
	{

		$SqlLots = "SELECT *,
(SELECT COUNT(*) from PpcHLGradingBreakup WHERE LotID=RMD.RmLotID  AND CreatedFrom=0) as GradedCount
from RmLotDetatils as RMD WHERE RMD.PurchaseDate BETWEEN '2022-04-01' and '2022-04-09'";
		$StmtLots = mysql_query($SqlLots, $this->db);
		while ($RowLots = mysql_fetch_array($StmtLots, MYSQL_ASSOC)) {
			if ($RowLots['GradedCount'] > 0) {
				echo $LotID = $RowLots['RmLotID'];
				$this->getCostingBreakUp($LotID);
			}
		}
	}

	public function postHonGradingBreakUp($LotID, $GradingData)
	{

		//$this->postGradingDefects($LotID,$GradingData);

		$GradingData = json_decode($GradingData, true);

		foreach ($GradingData as $value) {
			//$AryCounts[]=$value; 
			if (!in_array($value['Count'], $AryCounts, true)) {
				$AryCounts[] = $value['Count'];
			}
		}

		sort($AryCounts);

		foreach ($AryCounts as $HonCount) {

			if ($HonCount != 0) {

				$HonWeight = 0;

				foreach ($GradingData as $value) {
					if ($value['Count'] == $HonCount) {
						$HonWeight = $HonWeight + $value['NetWeight'];
					}
				}
				$SqlHonGradesBreakUp = "INSERT INTO `PpcHonGradingBreakup`(`LotID`, `HonWeight`, `HonCount`) 
			VALUES ('$LotID','$HonWeight','$HonCount')";
				$StmtHonGradesBreakUp = mysql_query($SqlHonGradesBreakUp, $this->db);
			}
		}
		if ($StmtHonGradesBreakUp) {
			return true;
		} else {
			return false;
		}
	}



	public function postGradingDefects($LotID, $GradingData)
	{


		$GradingData = json_decode($GradingData, true);

		foreach ($GradingData as $value) {

			if ($value['Defect'] != '' && !in_array($value['Defect'], $AryDefects, true)) {
				$AryDefects[] = $value['Defect'];
			}
		}

		sort($AryDefects);

		foreach ($AryDefects as $DefectID) {

			$HLWeight = 0;

			foreach ($GradingData as $value) {
				if ($value['Defect'] == $DefectID) {
					$HLWeight = $HLWeight + $value['NetWeight'];
					$HLCount = $value['Count'];
				}
			}
			$SqlGradesDefects = "INSERT INTO `PpcHLGradingDefects`(`LotID`,`HLWeight`,`HLCount`,`DefectID`) 
			VALUES ('$LotID','$HLWeight','$HLCount','1')";
			$StmtGradesDefects = mysql_query($SqlGradesDefects, $this->db);
		}


		if ($StmtGradesDefects) {
			return true;
		} else {
			return false;
		}
	}


	public function PostFinancier()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("Y-m-d  h:i:s");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$FinancierNAme = $data['FinancierNAme'];
		$BankName = $data['BankName'];
		$BankAccNo = $data['BankAccNo'];
		$GstNo = $data['GstNo'];
		$IfscCode = $data['IfscCode'];
		$PanNumber = $data['PanNumber'];
		$FinancierAddress = $data['FinancierAddress'];


		//$Jsd=json_decode($HOSO_W_DATA,true);


		/*$sql_pu="SELECT count(*) as TOTAL FROM `$TABLE` where LOT_ID='$LOT_ID' and DATA_TYPE='$DATA_TYPE'";
																		$sqlca_pu = mysql_query($sql_pu);
																		$rlt = mysql_fetch_array($sqlca_pu,MYSQL_ASSOC);*/

		//if($rlt['TOTAL'] == 0){

		$ss_post = "INSERT INTO `RM_FINANCIERS`(`FINANCIER_NAME`, `BANK_NAME`, `BANK_ACC_NO`, `GST_NO`, `IFSC_CODE`, `PanNumber`, `FinancierAddress`) 
	VALUES ('$FinancierNAme','$BankName','$BankAccNo','$GstNo','$IfscCode','$PanNumber','$FinancierAddress')";


		$sqlca_post = mysql_query($ss_post, $this->db);

		/*}else{
																		$sqlca_post=false;
																		$msg='Report Already Exist';
																		}*/


		if ($sqlca_post) {
			$records = array('status' => $STATUS_SUCCESS, "msg" => "Successful");
			$this->response($this->json($records), 200);
		} else {
			// echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => $msg);
			$this->response($this->json($records), 200);
		}
	}



	public function PostOrgno()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("d/m/y  h:i A");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$PostedUserID = $data['PostedUserID'];
		$LotID = $data['LotID'];
		$DATA = $data['DATA'];
		$DATA = json_decode($DATA, true);

		//print_r($DATA);
		//exit;



		foreach ($DATA as $value) {

			$CheckedCount = $value['CheckedCount'];
			$BlackGills = $value['BlackGills'];
			$DroopingHead = $value['DroopingHead'];
			$Soft = $value['Soft'];
			$BlackSpot = $value['BlackSpot'];
			$Broken = $value['Broken'];
			$ImproperLegs = $value['ImproperLegs'];
			$Bends = $value['Bends'];
			$BigPieces = $value['BigPieces'];
			$SmallPieces = $value['SmallPieces'];
			$SmallPieces = $value['BlackTail'];
			$Remarks = $value['Remarks'];
			$FitForHonPack = "";
			//$Remarks="";

			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$PostedDate = $dateTime->format("Y-m-d  h:i:s");






			$ss_post = "INSERT INTO `QcRmOrgnDetails`(`LotID`, `PostedDate`, `CheckedCount`,
				`BlackGills`, `DroopingHead`, `Soft`, `BlackSpot`, `Broken`, `ImproperLegs`, `Bends`,
				`BigPieces`, `SmallPieces`, `FitForHonPack`, `Remarks`, `PostedUserID`)
					VALUES ('$LotID','$PostedDate','$CheckedCount','$BlackGills','$DroopingHead',
							'$Soft','$BlackSpot','$Broken','$ImproperLegs',
							'$Bends','$BigPieces','$SmallPieces','$FitForHonPack',
							'$Remarks','$PostedUserID')";


			$sqlca_post = mysql_query($ss_post, $this->db);
		}







		if ($sqlca_post) {

			$records = array('Error' => null, "msg" => "Successful");
			$this->response($this->json($records), 200);
		} else {
			$Error = mysql_errno($this->db);
			$records = array('Error' => $Error, "msg" => "Failed");
			$this->response($this->json($records), 200);
		}
	}



	public function getValueAdditionData()
	{

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}




		$UNIT_ID = intval($_GET['UNIT_ID']);

		//SHIFTS

		$ShiftQuery = "SELECT * FROM `PpcOptions` WHERE Value ='SHIFTS' and Status =1";
		$ShiftExec = mysql_query($ShiftQuery);

		while ($RowShift = mysql_fetch_array($ShiftExec, MYSQL_ASSOC)) {
			$ShiftsData[] = $RowShift;
			$ShiftsNames[] = $RowShift['Name'];
		}
		$AryShifts['Data'] = $ShiftsData;
		$AryShifts['Names'] = $ShiftsNames;

		//VARIETY 

		$VarietyQuery = "SELECT * FROM `PpcOptions` WHERE Value ='VARIETY' and Status =1";
		$VarietyExec = mysql_query($VarietyQuery);

		while ($RowVariety = mysql_fetch_array($VarietyExec, MYSQL_ASSOC)) {
			$VarietyData[] = $RowVariety;
			$VarietyNames[] = $RowVariety['Name'];
		}
		$AryVariety['Data'] = $VarietyData;
		$AryVariety['Names'] = $VarietyNames;

		//RmVAStations

		$StationsQuery = "SELECT * FROM `RmVAStations` WHERE UnitStatus=1";
		$StationsExec = mysql_query($StationsQuery);

		while ($RowStations = mysql_fetch_array($StationsExec, MYSQL_ASSOC)) {
			$StationsData[] = $RowStations;
			$StationsNames[] = $RowStations['UnitName'];
		}
		$AryStations['Data'] = $StationsData;
		$AryStations['Names'] = $StationsNames;



		$WorkersContractor = $this->getWorkers($UNIT_ID);
		$Wages = $this->getWageRateKgs($UNIT_ID);



		$records = array(
			'Varity' => $AryVariety,
			"Shift" => $AryShifts,
			"WorkersContractor" => $WorkersContractor,
			"PerKgWages" => $Wages,
			"Units" => $AryStations
		);
		$this->response($this->json($records), 200);
	}

	public function getRmVaStations($UNIT_ID)
	{


		$sql_pu = "SELECT * FROM `RM_PROCESSING_STATIONS` WHERE UNIT_ID='$UNIT_ID' order by UNIT_NAME";

		$sqlca_pu = mysql_query($sql_pu);


		while ($rlt = mysql_fetch_array($sqlca_pu, MYSQL_ASSOC)) {
			$ary_pu[] = $rlt;
		}
		return $ary_pu;
	}




	public function PostRmStatus()
	{

		$LotID = intval($_GET['LotID']);
		$Option = $_GET['Option'];
		$PostedUserID = $_GET['PostedUserID'];





		for ($i = 0; $i < 3; $i++) {

			$CheckedCount = 0;
			$BlackGills = 0;
			$DroopingHead = 0;
			$Soft = 0;
			$BlackSpot = 0;
			$Broken = 0;
			$ImproperLegs = 0;
			$Bends = 0;
			$BigPieces = 0;
			$SmallPieces = 0;
			$SmallPieces = 0;
			$Remarks = 0;
			$FitForHonPack = "";
			$Remarks = $Option;

			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$PostedDate = $dateTime->format("Y-m-d  h:i:s");






			$ss_post = "INSERT INTO `QcRmOrgnDetails`(`LotID`, `PostedDate`, `CheckedCount`,
				`BlackGills`, `DroopingHead`, `Soft`, `BlackSpot`, `Broken`, `ImproperLegs`, `Bends`,
				`BigPieces`, `SmallPieces`, `FitForHonPack`, `Remarks`, `PostedUserID`)
					VALUES ('$LotID','$PostedDate','$CheckedCount','$BlackGills','$DroopingHead',
							'$Soft','$BlackSpot','$Broken','$ImproperLegs',
							'$Bends','$BigPieces','$SmallPieces','$FitForHonPack',
							'$Remarks','$PostedUserID')";


			$sqlca_post = mysql_query($ss_post, $this->db);
		}







		if ($sqlca_post) {

			$records = array('Error' => null, "msg" => "Successful");
			$this->response($this->json($records), 200);
		} else {
			$Error = mysql_errno($this->db);
			$records = array('Error' => $Error, "msg" => "Failed");
			$this->response($this->json($records), 200);
		}
	}

	public function LotList($UNIT_ID, $LOT_NO)
	{

		$Year = '2021';
		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$DateStart = "2020-07-01";
		$DateEnd = "2020-07-31 23:59:59";

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$UNIT_ID = $data['UNIT_ID'];
		$STATUS = $data['STATUS'];
		$REMARKS = $data['REMARKS'];
		$TYPE_ID = $data['TYPE_ID'];
		// $TYPE_ID = 8;
		if ($TYPE_ID == '') {
			$temp = array('status' => $STATUS_FAILURE, "msg" => "Please Update App");
			$this->response($this->json($temp), 200);
		} else {

			$NewLotID = "1";
			$PpcValueAddition = "17";


			$New_Lot_ID = "1";
			$ph_sample_ID = "2";
			$Lot_LIST_ID = "3";
			$RM_ANALYSIS_ID = "4";
			$Head_On_Weightment_ID = "5";
			$Head_less_Weightment_ID = "6";
			$Head_On_Grading_ID = "7";
			$Head_less_Grading_ID = "8";
			$HO_HL_REP_ID = "9";
			$FINAL_INSPECTION_ID = "10";
			$COLD_STORE_ID = "11";
			$Antibiotic_Testing_ID = "12";
			$SOAKING_ID = "13";
			$IQF_ID = "14";
			$IQF_OF_INSPECTION_ID = "15";
			$PACKING_ID = "16";
			$ATTENDANCE_ID = "17";
			$RM_DEFECTS_ID = "180"; //Socking Id conflict
			$SOAKING_ID = "18";
			$Creates_ID = "19";

			if ($REMARKS == 'HON Grading') {
				$REMARKS = 'Head On Grading';
			} else if ($REMARKS == 'HL Grading') {
				$REMARKS = 'Headless Grading';
			}

			if ($REMARKS == 'HON Weightment') {
				$REMARKS = 'Head On Grading';
			} else if ($REMARKS == 'HL Grading') {
				$REMARKS = 'Headless Grading';
			}

			$limit = 500;

			// $Query="and IMD.PURCHASE_DATE between '$DateStart' and '$DateEnd'";


			if ($TYPE_ID == $Creates_ID) {
				$sqlca = "SELECT *
			FROM `RmLotDetatils` as RMD left join  
			RmPurchaseStations as RPS on RMD.PurchaseStationID=RPS.PurchaseStationID 
			where RMD.LotStatus='1'  and PurchaseDate >= DATE_SUB(now(), INTERVAL 6 DAY)
			ORDER BY RMD.PurchaseStationID Asc ";
			} else if ($TYPE_ID == $ph_sample_ID) {
				$sqlca = "SELECT * FROM `RmLotDetatils` as IMD 
				where IMD.SAMPLE_TYPE=='PRE HARVEST' 
				ORDER BY  IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";
			} else if ($TYPE_ID == $Lot_LIST_ID) {
				$sqlca = "SELECT * FROM `RmLotDetatils` as RMD 
				left join  QcRmAbReport 
				 as ABR on RMD.RmLotID=ABR.LotID
				where RMD.LotStatus='1'  
				ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC LIMIT 100";
			} else if ($TYPE_ID == $RM_ANALYSIS_ID) {
				$sqlca = "select * from RmLotDetatils as RMD where  
							 not exists (select 1 from QcRmOrgnDetails as  RMDD where RMDD.LotID = RMD.RmLotID )
							and  LotStatus=1							
							ORDER BY  RMD.PurchaseDate DESC, RMD.LotNumber DESC limit $limit";
			} else if ($TYPE_ID == $RM_DEFECTS_ID) {
				$sqlca = "select * from RmLotDetatils as IMD where IMD.PurchaseDate !='' 
				 and not exists (select 1 from RmBillDetails RMDD where RMDD.ArrivalID = IMD.RmLotID )
                 and LotStatus=1	
				 ORDER BY  IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";
			} else if ($TYPE_ID == $Head_On_Weightment_ID) {
				$sqlca = "select * from RmLotDetatils as IMD where not exists 
				(select 1 from PPC_WEIGHTMENT_HON t2 where t2.LOT_ID = IMD.RmLotID  ) 
				AND IMD.SAMPLE_TYPE='' 
				ORDER BY IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";
			} else if ($TYPE_ID == $Head_less_Weightment_ID) {
				$sqlca = "select * from RmLotDetatils as IMD where not exists 
				(select 1 from PPC_WEIGHTMENT_HL t2 where t2.LOT_ID = IMD.RmLotID  ) 
				AND IMD.SAMPLE_TYPE=''   
				ORDER BY IMD.LotNumber DESC, IMD.LotNumber DESC LIMIT $limit ";
			} else if ($TYPE_ID == $Head_On_Grading_ID) {
				$sqlca = "select * from RmLotDetatils as IMD where not exists 
				(select 1 from PpcHonGradingDetails t2 where t2.LotID = IMD.RmLotID )  
				ORDER BY IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";
			} else if ($TYPE_ID == $Head_less_Grading_ID) {
				$sqlca = "select * from RmLotDetatils as RMD left join  QcRmAbReport 
				 as ABR on RMD.RmLotID=ABR.LotID where not exists 
				(select 1 from PpcHLGradingDetails HlGD where HlGD.LotID = RMD.RmLotID  AND HlGD.CreatedFrom=0) 
				and  LotStatus=1						
				ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC LIMIT  $limit";
				//AND t2.DATA_TYPE='2'
			} else if ($TYPE_ID == $PpcValueAddition) {
				$sqlca = "select * from RmLotDetatils as IMD where 
					 LotStatus=1 
					ORDER BY IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";
			} else if ($TYPE_ID == $SOAKING_ID) {
				$sqlca = "select * from RmLotDetatils as RMD left join  QcRmAbReport 
				 as ABR on RMD.RmLotID=ABR.LotID where 
				  LotStatus=1						
				ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC limit 500";
			} else {
				$sqlca = "SELECT * FROM `RmLotDetatils` as IMD where IMD.SAMPLE_TYPE!='PRE HARVEST' 
				ORDER BY IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT 500";
			}

			//echo $sqlca; 



			$sqlca_c_user = mysql_query($sqlca);


			if (mysql_num_rows($sqlca_c_user) == '0') {
				$temp = array('Error' => "No Lots Found");
				$this->response($this->json($temp), 200);
			} else {

				while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
					$result[] = $rlt;
				}



				$Msg = mysql_num_rows($sqlca_c_user) . " Lots Avaiable";

				$temp = array('Error' => null, "Msg" => $Msg, "LotList" => $result);
				$this->response($this->json($temp), 200);
			}
		}
	}





	public function preHarvestData($UNIT_ID, $LOT_NO)
	{

		if ($UNIT_ID == '') {
			$UNIT_ID = intval($_GET['UNIT_ID']);
		}



		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		if ($LOT_NO == '') {
			$sqlca = "SELECT * FROM `inputmaterial_details` where UNIT_ID='$UNIT_ID' and SAMPLE_TYPE='PRE HARVEST' ORDER BY id DESC";
		} else {
			$sqlca = "SELECT * FROM `inputmaterial_details` where LOT_NO='$LOT_NO' and SAMPLE_TYPE='PRE HARVEST' ORDER BY id DESC";
		}



		$sqlca_c_user = mysql_query($sqlca);


		if (mysql_num_rows($sqlca_c_user) == '0') {
			$temp = array('status' => $STATUS_FAILURE, "msg" => "No User Exist Please Register");
			$this->response($this->json($temp), 200);
		} else {
			while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
				$result[] = $rlt;
			}



			$sql_pu = "SELECT * FROM `PROCESSING_UNITS`";

			$sqlca_pu = mysql_query($sql_pu);


			while ($rlt = mysql_fetch_array($sqlca_pu, MYSQL_ASSOC)) {
				$ary_pu[] = $rlt;
			}




			$temp = array('status' => $STATUS_SUCCESS, "InwardList" => $result, "msg" => "Successful", "ProcessingUnits" => $ary_pu);

			$this->response($this->json($temp), 200);
		}
	}




	public function PostToProcessing()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$REFERENCE_ID_OLD = $data['REFERENCE_ID'];
		$PROCESSING_UNIT_ID = $data['PROCESSING_UNIT_ID'];
		$PROCESSING_TYPE = $data['PROCESSING_TYPE'];
		$WEIGHT_IN_KGS = $data['
			'];
		$DRIVER_NAME = $data['DRIVER_NAME'];
		$VEHICAL_NO = $data['VEHICAL_NO'];
		$AVAL_WEIGHT_TO_PROCESS = $data['AVAL_WEIGHT_TO_PROCESS'];
		$WEIGHT_UNDER_PROCESSING = $data['WEIGHT_UNDER_PROCESSING'];
		$LOT_NUMBER = $data['LOT_NUMBER'];
		$FROM_UNIT_ID = $data['FROM_UNIT_ID'];
		$INPUT_MATERIAL_FORM = $data['INPUT_MATERIAL_FORM'];

		$RE_ASSIGN = $data['RE_ASSIGN'];
		$SUB_REF_ID = $data['SUB_REF_ID'];
		$REMARKS = $data['REMARKS'];



		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("d/m/y  h:i A");

		$sql = "SELECT count(*) as total from MATERIAL_PROCESSING_DATA WHERE LOT_NUMBER='$LOT_NUMBER'";


		$result = mysql_query($sql);
		$data = mysql_fetch_assoc($result);
		$count = $data['total'] + 1;

		$REFERENCE_ID = $LOT_NUMBER . "/" . $count;

		if ($RE_ASSIGN == '1') {
			$SUB_REF_ID = $REFERENCE_ID_OLD;
		}



		if ($PROCESSING_UNIT_ID == $FROM_UNIT_ID && $PROCESSING_TYPE != 'SEND TO PRODUCTION') {
			$STATUS = '5';
			$RECIEVED_WEIGHT = $WEIGHT_IN_KGS;
		} else {
			$STATUS = '0';
			$RECIEVED_WEIGHT = '';
		}




		$ss_post = "INSERT INTO `MATERIAL_PROCESSING_DATA`( `REQUESTED_DATE`,`REFERENCE_ID`, `ASSIGNED_UNIT_ID`, 
																`PROCESS_NAME`, `WEIGHT_IN_KGS`,`DRIVER_NAME`,`VEHICAL_NO`,`LOT_NUMBER`,
																`FROM_UNIT_ID`,`INPUT_MATERIAL_FORM`,`SUB_REF_ID`,`STATUS`,`RECIEVED_WEIGHT`	)

																			VALUES  
																			
																			('$dt','$REFERENCE_ID','$PROCESSING_UNIT_ID','$PROCESSING_TYPE',
																			'$WEIGHT_IN_KGS','$DRIVER_NAME','$VEHICAL_NO','$LOT_NUMBER',
																			'$FROM_UNIT_ID','$INPUT_MATERIAL_FORM','$SUB_REF_ID','$STATUS','$RECIEVED_WEIGHT')";


		$sqlca_post = mysql_query($ss_post, $this->db);


		if ($sqlca_post) {
			//$this-> postTimeTrack($REFERENCE_ID,"Request Sent","0");

			if ($RE_ASSIGN == '1') {

				if ($REMARKS == "SEND TO PRE PROCESSING") {

					$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET `RE_ASSIGN`=$RE_ASSIGN,
					`RE_ASSIGN_AVAL_WEIGHT`=$AVAL_WEIGHT_TO_PROCESS ,`STATUS`='6'
					WHERE `REFERENCE_ID`='$REFERENCE_ID_OLD'";
				} else {

					$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET `RE_ASSIGN`=$RE_ASSIGN,
					`RE_ASSIGN_AVAL_WEIGHT`=$AVAL_WEIGHT_TO_PROCESS 
					WHERE `REFERENCE_ID`='$REFERENCE_ID_OLD'";
				}


				$sqlca_update = mysql_query($sql_update, $this->db);
			} else {


				$sql_update = "UPDATE `inputmaterial_details` SET `AVAL_WEIGHT_TO_PROCESS`=$AVAL_WEIGHT_TO_PROCESS,
					`WEIGHT_UNDER_PROCESSING`=$WEIGHT_UNDER_PROCESSING 
					WHERE `LOT_NO`='$LOT_NUMBER'";

				$sqlca_update = mysql_query($sql_update, $this->db);
			}




			$this->pushFcm($PROCESSING_UNIT_ID, $JSON_DATA);




			$records = array('status' => $STATUS_SUCCESS, "msg" => "Regsitration Successful");
			$this->response($this->json($records), 200);
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Regsitration Failed");
			$this->response($this->json($records), 200);
		}
	}




	public function PostReturnWeight()
	{


		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$REFERENCE_ID_OLD = $data['REFERENCE_ID'];
		$PROCESSING_UNIT_ID = $data['PROCESSING_UNIT_ID'];
		$PROCESSING_TYPE = $data['PROCESSING_TYPE'];
		$WEIGHT_IN_KGS = $data['WEIGHT_IN_KGS'];
		$DRIVER_NAME = $data['DRIVER_NAME'];
		$VEHICAL_NO = $data['VEHICAL_NO'];
		$AVAL_WEIGHT_TO_PROCESS = $data['AVAL_WEIGHT_TO_PROCESS'];
		$WEIGHT_UNDER_PROCESSING = $data['WEIGHT_UNDER_PROCESSING'];
		$LOT_NUMBER = $data['LOT_NUMBER'];
		$FROM_UNIT_ID = $data['FROM_UNIT_ID'];
		$INPUT_MATERIAL_FORM = $data['INPUT_MATERIAL_FORM'];

		$RE_ASSIGN = $data['RE_ASSIGN'];
		$SUB_REF_ID = $data['SUB_REF_ID'];

		/*check weather weight avaiable or not */


		$SQL_SUB_M_P_D = "SELECT * FROM MATERIAL_PROCESSING_DATA WHERE REFERENCE_ID='$REFERENCE_ID_OLD'";
		$STMT_SUB_M_P_D = mysql_query($SQL_SUB_M_P_D);
		$SUB_M_P_D_ROW = mysql_fetch_array($STMT_SUB_M_P_D, MYSQL_ASSOC);

		$SUB_M_P_D_ROW['RE_ASSIGN_AVAL_WEIGHT'];


		if ($SUB_M_P_D_ROW['RE_ASSIGN_AVAL_WEIGHT'] >= $WEIGHT_IN_KGS) {

			$BALANCE_WEIGHT = $SUB_M_P_D_ROW['RE_ASSIGN_AVAL_WEIGHT'] - $WEIGHT_IN_KGS;


			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$dt = $dateTime->format("d/m/y  h:i A");

			$sql = "SELECT count(*) as total from MATERIAL_PROCESSING_DATA WHERE LOT_NUMBER='$LOT_NUMBER'";

			$result = mysql_query($sql);
			$data = mysql_fetch_assoc($result);
			$count = $data['total'] + 1;

			$REFERENCE_ID = $LOT_NUMBER . "/" . $count;

			$SUB_REF_ID = $REFERENCE_ID_OLD;

			$STATUS = '0';





			$ss_post = "INSERT INTO `SUB_MATERIAL_PROCESSING_DATA`( `REFERENCE_ID`,`RETURN_WEIGHT`,`SUB_REFERENCE_ID`)

																			VALUES  
																			
																			('$REFERENCE_ID','$WEIGHT_IN_KGS','$SUB_REF_ID')";


			$sqlca_post = mysql_query($ss_post, $this->db);


			$ss_post = "INSERT INTO `MATERIAL_PROCESSING_DATA`( `REQUESTED_DATE`,`REFERENCE_ID`, `ASSIGNED_UNIT_ID`, 
																`PROCESS_NAME`, `WEIGHT_IN_KGS`,`DRIVER_NAME`,`VEHICAL_NO`,`LOT_NUMBER`,
																`FROM_UNIT_ID`,`INPUT_MATERIAL_FORM`,`SUB_REF_ID`,`STATUS`	)

																			VALUES  
																			
																			('$dt','$REFERENCE_ID','$PROCESSING_UNIT_ID','$PROCESSING_TYPE',
																			'$WEIGHT_IN_KGS','$DRIVER_NAME','$VEHICAL_NO','$LOT_NUMBER',
																			'$FROM_UNIT_ID','$INPUT_MATERIAL_FORM','$SUB_REF_ID','$STATUS')";


			$sqlca_post = mysql_query($ss_post, $this->db);




			if ($sqlca_post) {

				//$this-> postTimeTrack($REFERENCE_ID,"Request Sent","0");



				$TOTAL_RETURN_WEIGHT = '0';
				$sql_return_weight = "SELECT * FROM SUB_MATERIAL_PROCESSING_DATA WHERE SUB_REFERENCE_ID='$SUB_REF_ID'";

				$sqlca_return_weight = mysql_query($sql_return_weight);


				while ($rlt = mysql_fetch_array($sqlca_return_weight, MYSQL_ASSOC)) {
					$TOTAL_RETURN_WEIGHT = $TOTAL_RETURN_WEIGHT + $rlt['RETURN_WEIGHT'];
				}

				$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET 
					`IS_RETURN_WEIGHT`='$TOTAL_RETURN_WEIGHT',
					`RE_ASSIGN_AVAL_WEIGHT`='$BALANCE_WEIGHT'
					
					WHERE `REFERENCE_ID`='$REFERENCE_ID_OLD'";
				$sqlca_update = mysql_query($sql_update, $this->db);



				$this->pushFcm($PROCESSING_UNIT_ID, $JSON_DATA);




				$records = array('status' => $STATUS_SUCCESS, "msg" => "Regsitration Successful");
				$this->response($this->json($records), 200);
			} else {
				// echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" => "Regsitration Failed");
				$this->response($this->json($records), 200);
			}
		} else {
			// echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Above Weight");
			$this->response($this->json($records), 200);
		}
	}





	public function ProcessRequests($UNIT_ID, $STATUS)
	{



		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		if ($data != "") {
			$UNIT_ID = $data['UNIT_ID'];
			$STATUS = $data['STATUS'];
			$USER_TYPE = $data['USER_TYPE'];
		}





		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		if ($USER_TYPE == 'Post Processing Supervisor') {
			$sqlca = "SELECT * FROM MATERIAL_PROCESSING_DATA where 
			 ASSIGNED_UNIT_ID=$UNIT_ID 
			 and STATUS=$STATUS
			 and PROCESS_NAME='SEND TO PRODUCTION'
			 

			 ORDER BY `LOT_NUMBER` DESC";
		} else {


			$sqlca = "SELECT * FROM MATERIAL_PROCESSING_DATA where ASSIGNED_UNIT_ID='$UNIT_ID' 
			 and STATUS='$STATUS' and PROCESS_NAME !='SEND TO PRODUCTION' 
			 
			 ORDER BY `LOT_NUMBER` DESC";
		}


		$sqlca_c_user = mysql_query($sqlca);



		if (mysql_num_rows($sqlca_c_user) == '0') {
			$temp = array('status' => $STATUS_FAILURE, "msg" => "No User Exist Please Register");
			$this->response($this->json($temp), 200);
		} else {



			while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {


				$reference_id = $rlt['LOT_NUMBER'];
				$FROM_UNIT_ID = $rlt['FROM_UNIT_ID'];
				$ASSIGNED_UNIT_ID = $rlt['ASSIGNED_UNIT_ID'];
				$rlt['Deatails'] = $this->varityDetails($reference_id);

				if ($rlt['Deatails'] != '') {

					if ($rlt['STATUS'] == '6' && $rlt['RE_ASSIGN_AVAL_WEIGHT'] == '0') {
					} else if ($rlt['STATUS'] == '7' && $rlt['RE_ASSIGN_AVAL_WEIGHT'] == '0' && $rlt['IS_RETURN_WEIGHT'] == '0') {
					} else {

						$rlt['FROM_UNIT_DETAILS'] = $this->getUnitDetails($FROM_UNIT_ID);
						$rlt['UNIT_DETAILS'] = $this->getUnitDetails($ASSIGNED_UNIT_ID);

						$result[] = $rlt;
					}
				}
			}




			$temp = array('status' => $STATUS_SUCCESS, "InwardList" => $result, "msg" => "Successful");

			$this->response($this->json($temp), 200);
		}
	}



	public function sendSecurityCheck()
	{

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$REFERENCE_ID = intval($_GET['REFERENCE_ID']);



		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';


		$sql_update = "UPDATE `inputmaterial_details` SET `STATUS`='0'

					WHERE REFERENCE_ID=$REFERENCE_ID";

		$sqlca_update = mysql_query($sql_update, $this->db);

		$records = array('status' => $STATUS_SUCCESS, "msg" => "Regsitration Successful");
		$this->response($this->json($records), 200);
	}

	public function PpcValueAddition()
	{



		$json = file_get_contents('php://input');
		$JsonData = json_decode($json, true);

		$PostedUserID = $JsonData['PostedUserID'];
		$LotID = $JsonData['LotID'];
		$Data = $JsonData['Data'];
		$JsonData = json_decode($Data, true);

		$AryCurrentDate = $this->getCurrentDateTime();
		$PostedDate = $AryCurrentDate['TimeStamp'];

		if ($PostedUserID == '') {
			$records = array('status' => $STATUS_FAILURE, "msg" => "Update Progress Try After some Time");
			$this->response($this->json($records), 200);
		}


		foreach ($JsonData as $value) {

			$SentCount = $value['HlCount'];
			$SentWeight = $value['HlWeightKg'];
			$ReceivedWeight = $value['RecievedWeightHl'];
			$ReturnWeight = $value['ReturnHlWeight'];
			$ProcessingDate = date("Y-m-d", strtotime($value['VaDate']));
			$WtAftrVA = $value['WeightAFterVA'];
			$CountAftrVA = $value['CountAfterVA'];
			$VarietyID = $value['Varity'];
			$ShiftID = $value['Shift'];
			$StationID = $value['StationId'];
			$Remarks = $value['Reamrks'];



			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$DATE_TIME = $dateTime->format("Y-m-d");
			$TIME_STAMP = $dateTime->format("Y-m-d  H:i:s");

			$sql_org_insert = "INSERT INTO `PpcValueAddition` 
	  ( `LotID`, `SentCount`, `SentWeight`, `ReceivedWeight`, 
	  `ReturnWeight`, `ProcessingDate`, `PostedDate`, `WtAftrVA`, `CountAftrVA`, 
	  `VarietyID`, `ShiftID`, `StationID`, `PostedUserID`, `Remarks`) 
	 VALUES ('$LotID','$SentCount','$SentWeight','$ReceivedWeight',
	 '$ReturnWeight','$ProcessingDate','$PostedDate','$WtAftrVA','$CountAftrVA','$VarietyID',
	 '$ShiftID','$StationID','$PostedUserID','$Remarks')";

			$sqlca_post = mysql_query($sql_org_insert, $this->db);
		}


		if ($sqlca_post) {

			$records = array('Error' => "Data saved successfully", "Msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		} else {

			$Error = "Oops! Something went wrong" . mysql_errno($this->db);

			$records = array('Error' => $Error);
			$this->response($this->json($records), 200);
		}
	}



	public function varityDetails($LOT_NO)
	{


		$sql_query = "SELECT * FROM `inputmaterial_details` where LOT_NO='$LOT_NO'";

		$sqlca_c_user = mysql_query($sql_query);


		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$result = $rlt;
		}

		return $result;
	}





	public function getAcceptRejectRequest()
	{

		if ($this->get_request_method() != "POST") {
			$this->response('', 406);
		}

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		//$PROCESSING_ID = $data['PROCESSING_ID'];
		$OPTION = $data['STATUS'];
		$STATUS = $data['STATUS'];
		$UNIT_ID = $data['UNIT_ID'];
		$REFERENCE_ID = $data['REFERENCE_ID'];
		$RECIEVED_WEIGHT = $data['RECIEVED_WEIGHT'];
		$REJECT_WEIGHT = $data['REJECT_WEIGHT'];
		$LOT_NUM = $data['LOT_NUM'];
		$AVAL_WEIGHT = $data['AVAL_WEIGHT'];

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$DATE_TIME = $dateTime->format("d/m/y  h:i A");






		if ($RECIEVED_WEIGHT == '') {

			if ($OPTION == 2) {

				$WEIGHT = $AVAL_WEIGHT + $REJECT_WEIGHT;
				$sql_update = "UPDATE `inputmaterial_details` SET `AVAL_WEIGHT_TO_PROCESS`='$WEIGHT' 
					
					WHERE $WEIGHT='$LOT_NUM'";

				$sqlca_update = mysql_query($sql_update, $this->db);
			}

			$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET `STATUS`=$OPTION , `ACCEPTED_DATE`='$DATE_TIME'
					
					WHERE REFERENCE_ID='$REFERENCE_ID'";
		} else {

			if ($OPTION == '7' || $OPTION == '8') {

				if ($OPTION == '7') {
					$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET `STATUS`=$OPTION , `COMPLEATED_TIME`='$DATE_TIME' ,
		`RECIEVED_WEIGHT`='$RECIEVED_WEIGHT',
		`WEIGHT_AFTER_PROCESS_IN_KGS`='$RECIEVED_WEIGHT',RE_ASSIGN_AVAL_WEIGHT='$RECIEVED_WEIGHT' WHERE REFERENCE_ID='$REFERENCE_ID'";
				} else if ($OPTION == '8') {
					$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET `STATUS`=$OPTION , `COMPLEATED_TIME`='$DATE_TIME' ,
		`RECIEVED_WEIGHT`='$RECIEVED_WEIGHT',
		`WEIGHT_AFTER_PROCESS_IN_KGS`='$RECIEVED_WEIGHT' WHERE REFERENCE_ID='$REFERENCE_ID'";
				}
			} else {
				$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET `STATUS`='$OPTION' , `ACCEPTED_DATE`='$DATE_TIME' ,`RECIEVED_WEIGHT`='$RECIEVED_WEIGHT'
					
					WHERE REFERENCE_ID='$REFERENCE_ID'";
			}
		}

		$sqlca_update = mysql_query($sql_update, $this->db);


		if ($sqlca_update) {

			if ($OPTION == '7' || $OPTION == '8') {
				/**/


				$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where REFERENCE_ID='$REFERENCE_ID'";

				$sqlca_c_user = mysql_query($sql_query);


				while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
					$LOT_NO = $rlt['LOT_NUMBER'];
				}

				$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$LOT_NO'";

				$sqlca_c_user = mysql_query($sql_query);

				$IS_COMPLETED = 0; //false 

				while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {


					if ($rlt['RE_ASSIGN_AVAL_WEIGHT'] == '0') {

						if ($rlt['STATUS'] == '6' || $rlt['STATUS'] == '7' || $rlt['STATUS'] == '8') {
							$IS_COMPLETED = 1;
						} else {
							$IS_COMPLETED = 0;
							break;
						}
					} else {
						$IS_COMPLETED = 0;
						break;
					}
				}



				if ($IS_COMPLETED == 1) {

					$sql_query = "SELECT * FROM `inputmaterial_details` where LOT_NO='$LOT_NO'";
					$sqlca_c_user = mysql_query($sql_query);
					$rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC);

					$rlt['AVAL_WEIGHT_TO_PROCESS'];
					if ($rlt['AVAL_WEIGHT_TO_PROCESS'] == 0) {
						$IS_COMPLETED = 1;
					} else {
						$IS_COMPLETED = 0;
					}



					$sql_imd_update = "UPDATE `inputmaterial_details` SET `STATUS`='$IS_COMPLETED' WHERE LOT_NO='$LOT_NO'";
					$sqlca_imd_update = mysql_query($sql_imd_update, $this->db);
				}
			}



			$this->pushFcm($UNIT_ID, $data);

			//$this-> postTimeTrack($REFERENCE_ID,"TEST",$OPTION);
			if ($UNIT_ID != '') {



				$this->ProcessRequests($UNIT_ID, $OPTION);
			} else {
				$temp = array('status' => $STATUS_SUCCESS, "msg" => "Successful");

				$this->response($this->json($temp), 200);
			}
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Regsitration Failed");
			$this->response($this->json($records), 200);
		}
	}






	public function postProcessedData()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$DATA = $data['DATA'];
		$ary_data = json_decode($DATA, true);








		$WEIGHT_AFTER_PROCESS_IN_KGS = $data['WEIGHT_AFTER_PROCESS_IN_KGS'];
		$PROCESSING_ID = $data['PROCESSING_ID'];
		$LOT_NUMBER = $data['LOT_NUMBER'];
		$REFERENCE_ID = $data['REFERENCE_ID'];
		$STATUS = "6";

		date_default_timezone_set('Asia/Kolkata');
		$COMPLEATED_TIME = date('Y/m/d h.i A');




		$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET 
					`WEIGHT_AFTER_PROCESS_IN_KGS`=$WEIGHT_AFTER_PROCESS_IN_KGS,
					`COMPLEATED_TIME`='$COMPLEATED_TIME',
					`STATUS`=$STATUS, 
					`RE_ASSIGN_AVAL_WEIGHT`='$WEIGHT_AFTER_PROCESS_IN_KGS' 
					
					WHERE PROCESSING_ID=$PROCESSING_ID";

		$sqlca_update = mysql_query($sql_update, $this->db);




		if ($sqlca_update) {
			//$this ->postTimeTrack($REFERENCE_ID,$STATUS_MSG,$STATUS);

			$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$LOT_NUMBER'";

			$sqlca_c_user = mysql_query($sql_query);


			$C_GROSS_WEIGHT = '0'; //Compleated Weight

			$C_ASSIGNED_WEIGHT = '0'; //Completated Assigned Weight

			$OA_ASSIGNES_WEIGHT = '0'; // Over All assigned Weight

			$WEIGHT_UNDER_PROCESS = '0';

			$OVER_ALL_STATUS = '';
			while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
				$OA_ASSIGNES_WEIGHT = $OA_ASSIGNES_WEIGHT + $rlt['WEIGHT_IN_KGS'];

				if ($rlt['STATUS'] == 3) {
					$C_GROSS_WEIGHT = $C_GROSS_WEIGHT + $rlt['WEIGHT_AFTER_PROCESS_IN_KGS'];

					$C_ASSIGNED_WEIGHT = $C_ASSIGNED_WEIGHT + $rlt['WEIGHT_IN_KGS'];
				}
			}

			$WEIGHT_UNDER_PROCESS = $OA_ASSIGNES_WEIGHT - $C_ASSIGNED_WEIGHT;


			$sql_update = "UPDATE `inputmaterial_details` SET `WEIGHT_AFTER_PROCESS`='$C_GROSS_WEIGHT',
			`WEIGHT_UNDER_PROCESSING`='$WEIGHT_UNDER_PROCESS' WHERE REFERENCE_ID='$LOT_NUMBER'";

			$sqlca_update = mysql_query($sql_update, $this->db);


			foreach ($ary_data as $item) {
				$grade = $item['Grade'];
				$weight = $item['Weight'];

				$ss_post = "INSERT INTO `GRADE_DETAILS`( `PROCESSING_ID`,`GRADE`, `WEIGHT`) 
					 VALUES  ('$PROCESSING_ID','$grade','$weight')";


				$sqlca_post = mysql_query($ss_post, $this->db);
			}
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Regsitration Failed");
			$this->response($this->json($records), 200);
		}
	}




	public function getOverAllDetails()
	{

		if ($this->get_request_method() != "GET") {
			$this->response('', 406);
		}


		$REFERENCE_ID = $_GET['REFERENCE_ID'];
		$UNIT_ID = intval($_GET['UNIT_ID']);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$postDetails = $this->varityDetails($REFERENCE_ID);
		$process = $this->parllelProcessList($REFERENCE_ID, $UNIT_ID);


		$records = array('postDetails' => $postDetails, "process" => $process);
		$this->response($this->json($records), 200);
	}




	public function parllelProcessList($ref_id, $UNIT_ID)
	{

		if ($UNIT_ID == '') {
			$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$ref_id'";
		} else {
			$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$ref_id' and ASSIGNED_UNIT_ID='$UNIT_ID'";

			$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$ref_id'";
		}

		//echo $sql_query;
		$sqlca_c_user = mysql_query($sql_query);


		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {

			if ($rlt['STATUS'] > 5 && $rlt['RE_ASSIGN_AVAL_WEIGHT'] == 0) {
			} else {

				$PROCESSING_ID = $rlt['PROCESSING_ID'];
				$FROM_UNIT_ID = $rlt['FROM_UNIT_ID'];
				$ASSIGNED_UNIT_ID = $rlt['ASSIGNED_UNIT_ID'];
				$rlt['FROM_UNIT_DETAILS'] = $this->getUnitDetails($FROM_UNIT_ID);
				$rlt['UNIT_DETAILS'] = $this->getUnitDetails($ASSIGNED_UNIT_ID);
				$rlt['Details'] = $this->getGradesData($PROCESSING_ID);
				$result[] = $rlt;
			}
		}

		return $result;
	}

	public function getGradesData($PROCESSING_ID)
	{


		$sql_query = "SELECT * FROM `GRADE_DETAILS` where PROCESSING_ID='$PROCESSING_ID'";

		$sqlca_c_user = mysql_query($sql_query);


		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {


			$result[] = $rlt;
		}

		return $result;
	}



	public function getUnitDetails($UNIT_ID)
	{


		$sql_query = "SELECT * FROM `PROCESSING_UNITS` where UNIT_ID='$UNIT_ID'";

		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {


			$data = $rlt;
		}

		return $data;
	}


	public function getDropDownData($UnitID)
	{

		/* 
																		if($this->get_request_method() != "GET"){
																			$this->response('',406);
																			}
																		*/



		$UNIT_ID = intval($_GET['UNIT_ID']);
		$UNIT_ID = 1;



		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';



		$superVisors = $this->getSuperVisors($UNIT_ID);
		$driver = $this->getDrivers($UNIT_ID);
		$vehi = $this->getVehicles($UNIT_ID);
		$units = $this->getUnits($UNIT_ID);
		$Machinery = $this->getMachinery($UNIT_ID);
		$BankNames = $this->getBankNames($UNIT_ID);
		$AgentNames = $this->getAgents($UNIT_ID);
		$GraderNames = $this->getGraders($UNIT_ID);
		$BillNames = $this->getBillNames($UNIT_ID);
		$PurchaseCategory = $this->getPurchaseCategory($UNIT_ID);

		$Contractor = $this->getContractor($UNIT_ID);
		$SubContractor = $this->getSubContractor($UNIT_ID);
		$WorkersContractor = $this->getWorkers($UNIT_ID);



		$records = array(
			"SuperVisors" => $superVisors,
			"Agents" => $AgentNames,
			"Graders" => $GraderNames,
			"Finaciers" => $BillNames,
			"Drivers" => $driver,
			"Vehicals" => $vehi,
			"Units" => $units,
			"BankNames" => $BankNames,
			"Machinery" => $Machinery,
			"PurchaseCategory" => $PurchaseCategory,
			"Contractor" => $Contractor,
			"SubContractor" => $SubContractor,
			"WorkersContractor" => $WorkersContractor

		);

		if ($UNIT_ID != '') {
			$this->response($this->json($records), 200);
		} else {
			return $records;
		}
	}


	public function getContractor($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `PpcContractMaster` ";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['ContrctrName'];
		}
		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;
		return $data;
	}


	public function getSubContractor($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `PpcSubContractorMaster`";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['SubContractorName'];
		}
		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;
		return $data;
	}

	public function getWorkers($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `PpcSubContractorMaster`";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['SubContractorName'];
		}
		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;
		return $data;
	}


	public function getWageRateKgs($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `PpcDailyWageRateKgs`";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
		}
		$data['Data'] = $RowData;
		return $data;
	}



	public function getSuperVisors($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `AdminLoginDetails` where `UnitID`='$UNIT_ID'";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['UserName'];
		}
		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;
		return $data;
	}

	public function getMachinery($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `AdminGradingMachines` Order by MachineName Asc";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['MachineName'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;

		return $data;
	}


	public function getDrivers($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `RmUsersList` where UserType='1' Order by UserName Asc";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['UserName'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;

		return $data;
	}



	public function getGraders($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `RmUsersList` where UserType='2' order by UserName asc";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['UserName'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;

		return $data;
	}
	public function getAgents($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `RmUsersList` where UserType='3' order by UserName asc";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['UserName'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;

		return $data;
	}


	public function getBillNames($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `RmFinanciers` order by FinancierName asc";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {

			$RowData[] = $rlt;
			$PcNames[] = $rlt['FinancierName'] . ' - ' . $rlt['PanNumber'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;

		return $data;
	}

	public function getPurchaseCategory($UNIT_ID)
	{
		$sql_query = "SELECT * FROM `RmPurchaseCategory`";
		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['PurchaseType'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;

		return $data;
	}

	public function getVehicles($UNIT_ID)
	{


		$sql_query = "SELECT * FROM `RmVehicalDetails`  order by VehicalNumber asc";

		$sqlca_c_user = mysql_query($sql_query);
		$data;

		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {

			$RowData[] = $rlt;
			$PcNames[] = $rlt['VehicalNumber'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;

		return $data;
	}




	public function getBankNames($UNIT_ID)
	{

		$sql_pu = "SELECT * FROM `AdminBankNamesMaster` order  by BankName asc";
		$sqlca_pu = mysql_query($sql_pu);
		while ($rlt = mysql_fetch_array($sqlca_pu, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['BankName'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;

		return $data;
	}


	public function getUnits($UNIT_ID)
	{


		$sql_pu = "SELECT * FROM `RmPurchaseStations` WHERE ProcessingStationID='$UNIT_ID'";
		$sql_pu = "SELECT * FROM `RmPurchaseStations` ";

		$sqlca_pu = mysql_query($sql_pu);


		while ($rlt = mysql_fetch_array($sqlca_pu, MYSQL_ASSOC)) {
			$RowData[] = $rlt;
			$PcNames[] = $rlt['StationName'];
		}

		$data['Data'] = $RowData;
		$data['Names'] = $PcNames;
		return $data;
	}

	public function postAttend()
	{
		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';
		$CHECK_IN = 0;
		$CHECK_OUT = 1;

		$json = file_get_contents('php://input');
		$ary_data = json_decode($json, true);

		$CHECK_STATUS = $ary_data['status'];
		$IP = $ary_data['ip'];
		$ATT_DATA = $ary_data['ATT_DATA'];


		$TempTable = 'HrmDailyAttendnessTemp';

		$SqlPreviousDate = "Select * from `HrmDailyAttendnessLog` order by Date DESC LIMIT 1  ";
		$SqlcaPreviousDate = mysql_query($SqlPreviousDate, $this->db);
		$RowPreviousDate = mysql_fetch_array($SqlcaPreviousDate, MYSQL_ASSOC);
		$LastUpdatedDate = $RowPreviousDate['Date'];

		$sql = "delete from  $TempTable where Date < '$LastUpdatedDate'";
		$sqlca_post = mysql_query($sql, $this->db);

		foreach ($ATT_DATA as $value) {
			$employee_id = $value[1];
			$date = $value[3];
			$time_in = $value[3];
			$time_out = $value[3];
			$status = '0';
			$SqlTempArray[] = "('$employee_id','$date','$time_in','0')";
		}

		$SqlTempValues = implode(',', $SqlTempArray);

		echo $SqlTemp = "INSERT INTO $TempTable 
 (`EmpID`, `Date`, `TimeIn`, `Satus`) VALUES $SqlTempValues";
		$sqlca_post = mysql_query($SqlTemp, $this->db);

		if ($sqlca_post) {

			$SqlDeleteLog = "delete from  `HrmDailyAttendnessLog` where Date ='$LastUpdatedDate'";
			$SqlcaDeleteLog = mysql_query($SqlDeleteLog, $this->db);
		}

		$sql = "select EmpID,Date,min(ai.TimeIn) as TimeIn, max(ai.TimeIn) as TimeOut, count(*) as count
from $TempTable ai
group by EmpID,Date";

		//having min(ai.time_in) < max(ai.time_in)
		$sqlca_bplco = mysql_query($sql, $this->db);

		while ($row = mysql_fetch_array($sqlca_bplco, MYSQL_ASSOC)) {
			$employee_id = $row['EmpID'];
			$date = $row['Date'];
			$time_in = $row['TimeIn'];

			if ($row['count'] > 1) {

				$time_out = $row['TimeOut'];
			} else {
				$time_out = '00:00:00';
			}


			$TimeDiff = strtotime($time_out) - strtotime($time_in);

			if ($TimeDiff > 3) {

				$time_out = $time_out;
			} else {

				$time_out = '00:00:00';
			}


			$status = '0';
			$SqlAryValues[] = "('$employee_id','$date','$time_in','$time_out','0')";
		}

		$SqlAttValues = implode(',', $SqlAryValues);


		$SqlAtt = "INSERT INTO `HrmDailyAttendnessLog`(`BioMetricID`, `Date`, `TimeIn`, `TimeOut`,`Status`) VALUES 
$SqlAttValues";
		$StmtAtt = mysql_query($SqlAtt, $this->db);

		if ($StmtAtt) {
			$SqlDeleteDup = "DELETE t1 FROM HrmDailyAttendnessLog  t1 INNER JOIN HrmDailyAttendnessLog  t2 
WHERE t1.AttID < t2.AttID AND  
t1.Date = t2.Date and 
t1.BioMetricID=t2.BioMetricID and 
t1.TimeIn=t2.TimeIn and 
t2.TimeOut=t2.TimeOut";
			$StmtDup = mysql_query($SqlDeleteDup, $this->db);
			$records = array('Error' => false, "Msg" => "Data Saved successfully");
			$this->response($this->json($records), 200);
		} else {
			$records = array('Error' => "Unable To Submit Data", "Msg" => "");
			$this->response($this->json($records), 200);
		}
	}





	/*


									  public function postAttend(){

										  $STATUS_SUCCESS='SUCCESS';
										  $STATUS_FAILURE='FAILURE'; 
										  $CHECK_IN=0;
										  $CHECK_OUT=1; 

											  $json = file_get_contents('php://input');
										  $ary_data = json_decode($json,true);

											  $CHECK_STATUS= $ary_data['status'];
											  $IP= $ary_data['ip'];
										  $ATT_DATA=$ary_data['ATT_DATA'];

									  if($CHECK_STATUS==$CHECK_IN){

									  $sql="TRUNCATE biometric_punching_log_check_in";
									  $sqlca_post = mysql_query($sql,$this->db);

									  foreach($ATT_DATA as $value){
									  $employee_id=$value[1];
									  $date=$value[3]; 
									  $time_in=$value[3]; 
									  $time_out=$value[3];
									  $status='0'; 

										  $sql="INSERT INTO `biometric_punching_log_check_in`(`employee_id`, `date`, `time_in`, `status`) VALUES 
																																																		  ('$employee_id','$date','$time_in','0')";
										  $sqlca_post = mysql_query($sql,$this->db);
										  } 


										  $sql="delete a from biometric_punching_log_check_in a left join
																																																		  ( select MIN(time_in) maxtimestamp, id from biometric_punching_log_check_in group by employee_id,date )
																																																		   b on a.time_in = maxtimestamp and a.date = date and a.employee_id = employee_id where b.maxtimestamp IS NULL";
										  $sqlca_post = mysql_query($sql,$this->db);

										  $sql="SELECT * from biometric_punching_log_check_in";
										  $sqlca_bplco = mysql_query($sql,$this->db);

										  while($row = mysql_fetch_array($sqlca_bplco,MYSQL_ASSOC)){
										  $employee_id=$row['employee_id'];
										  $date=$row['date'];
										  $time_in=$row['time_in'];
										  $time_out=$row['time_in'];
										  $status='0';


											  $sql="SELECT count(*) as total  from `attendance` where  `employee_id`='$employee_id' and `date`='$date'";

											  $result=mysql_query($sql);
											  $data=mysql_fetch_assoc($result);
											  $count=$data['total'];


										  if($count > 0){
									  $sql="UPDATE `attendance` SET `time_in`='$time_in' WHERE `employee_id`='$employee_id' and `date`='$date'";
										  }else{
									  $sql="INSERT INTO `attendance`(`employee_id`, `date`, `time_in`, `status`) VALUES ('$employee_id','$date','$time_in','0')";
										  }

										  $sqlca_post = mysql_query($sql,$this->db);
										  }




										  }else{

										  $sql="TRUNCATE biometric_punching_log_check_out";
										  $sqlca_post = mysql_query($sql,$this->db);

										  foreach($ATT_DATA as $value){
										  $employee_id=$value[1];
										  $date=$value[3]; 
										  $time_in=$value[3]; 
										  $time_out=$value[3];
										  $status='0'; 


											  $sql="INSERT INTO `biometric_punching_log_check_out`(`employee_id`, `date`,  `status`, `time_out`, `num_hr`) VALUES 
																																																		  ('$employee_id','$date','0','$time_out','0')";
											  $sqlca_post = mysql_query($sql,$this->db);

											  } 


											  $sql="delete a from biometric_punching_log_check_out a left join
																																																		  ( select MAX(time_out) maxtimestamp, id from biometric_punching_log_check_out group by employee_id,date )
																																																		   b on a.time_out = maxtimestamp and a.date = date and a.employee_id = employee_id where b.maxtimestamp IS NULL";
											  $sqlca_post = mysql_query($sql,$this->db);

											  $sql="SELECT * from biometric_punching_log_check_out";
											  $sqlca_bplco = mysql_query($sql,$this->db);

											  while($row = mysql_fetch_array($sqlca_bplco,MYSQL_ASSOC)){
											  $employee_id=$row['employee_id'];
											  $date=$row['date'];
											  $time_in=$row['time_in'];
											  $time_out=$row['time_out'];
											  $status='0';


												  $sql="SELECT count(*) as total  from `attendance` where  `employee_id`='$employee_id' and `date`='$date'";

												  $result=mysql_query($sql);
												  $data=mysql_fetch_assoc($result);
												  $count=$data['total'];


											  if($count > 0){
										  $sql="UPDATE `attendance` SET `time_out`='$time_out' WHERE `employee_id`='$employee_id' and `date`='$date'";
											  }else{
									  $sql="INSERT INTO `attendance`(`employee_id`, `date`, `time_out`, `status`) VALUES ('$employee_id','$date','$time_out','0')";
										  }

										  $sqlca_post = mysql_query($sql,$this->db);
										  }



										  }

										  echo "Success";

										  }


										  */
	//uma

	public function postAttData()
	{

		$input_data = file_get_contents('php://input');
		$jd_data = json_decode($input_data, true);


		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$DATA = $jd_data['DATA'];
		$DATA = json_decode($DATA, true);

		//print_r($jd_data); 

		foreach ($DATA as $value) {

			$employee_id = $value['employee_id'];
			$date = $value['date'];
			$time_in = $value['time_in'];
			$time_out = $value['time_out'];
			$num_hr = $value['num_hr'];

			if ($time_in == '') {
				$time_in = '00:00:00';
			}

			if ($time_out == '') {
				$time_out = '00:00:00';
			}


			$sql = "SELECT count(*) as total,status   from `attendance` where  `employee_id`='$employee_id' and `date`='$date' ";
			$result = mysql_query($sql, $this->db);
			$data = mysql_fetch_assoc($result);
			$count = $data['total'];
			$status = $data['status'];

			if ($count > 0) {
				//echo $rq="update";
				if ($status == 0) {
					$sql = "UPDATE `attendance` SET `time_in`='$time_in',`time_out`='$time_out' WHERE `employee_id`='$employee_id' and `date`='$date'";
					//$rq="update";
				}
			} else {
				$sql = "INSERT INTO `attendance`(`employee_id`, `date`, `time_in`,`time_out`, `status`) VALUES ('$employee_id','$date','$time_in','$time_out','0')";
				//$rq= "insert";
			}

			$sqlca_post = mysql_query($sql, $this->db);

			//echo $sql;
		}

		if ($sqlca_post) {

			$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
			$this->response($this->json($records), 200);
		}
	}


	public function postBioMetricData()
	{


		$CheckIn = 0;
		$CheckOut = 1;

		$JsonIpData = file_get_contents('php://input');
		$AryData = json_decode($JsonIpData, true);

		$Error = $AryData['Error'];
		$ClientID = $AryData['ClientID'];
		$UserID = $AryData['UserID'];
		$MachineIp = $AryData['MachineIp'];
		$UserIp = $AryData['UserIp'];
		$AttendanceData = $AryData['AttendanceData'];


		$TempTable = 'HrmDailyAttendnessTemp';

		$SqlPreviousDate = "Truncate $TempTable";
		$SqlcaPreviousDate = mysql_query($SqlPreviousDate, $this->db);




		foreach ($AttendanceData as $RowAttData) {

			$BioMetricId = $RowAttData['BioMetricId'];
			$Date = date("Y-m-d", strtotime($RowAttData['Date']));
			$TimeIn = date("H:i:s", strtotime($RowAttData['Date']));
			$Status = $RowAttData['PunchType'];
			$DateBioID = str_replace('-', '', $Date);
			$DateBioID = $DateBioID . $BioMetricId;
			$SqlTempArray[] = "('$BioMetricId','$DateBioID','$Date','$TimeIn','$Status','$MachineIp')";
		}

		$SqlTempValues = implode(',', $SqlTempArray);

		$SqlTemp = "INSERT INTO $TempTable (`BioMetricId`,`DateBioID`, `Date`, `TimeIn`, `Status`,`DeviceID`) VALUES $SqlTempValues";
		$sqlca_post = mysql_query($SqlTemp, $this->db);
		$SqlTemp = "INSERT INTO HrmDailyAttendnessBackUp (`BioMetricId`,`DateBioID`, `Date`, `TimeIn`, `Status`,`DeviceID`) VALUES $SqlTempValues";
		$sqlca_post = mysql_query($SqlTemp, $this->db);

		if ($sqlca_post) {


			$SqlDeleteDuplicateRecords = "DELETE t1 FROM HrmDailyAttendnessTemp  t1 INNER JOIN HrmDailyAttendnessTemp  t2 
					WHERE t1.AttID < t2.AttID AND  
					t1.Date = t2.Date and 
					t1.BioMetricID=t2.BioMetricID and 
					t1.TimeIn=t2.TimeIn and 
					t2.TimeOut=t2.TimeOut;";
			$DeleteDuplicateRecordsExec = mysql_query($SqlDeleteDuplicateRecords, $this->db);


			$SqlMaxTimeINReocrds = "DELETE t1.* FROM HrmDailyAttendnessTemp t1 LEFT JOIN (SELECT AttID, min(TimeIn) TimeIn
								FROM HrmDailyAttendnessTemp WHERE Status=0 GROUP BY Date,BioMetricID) 
								t2 ON t1.AttID = t2.AttID WHERE Status=0 and t2.TimeIn IS NULL;";



			$MaxTimeINReocrdsExec = mysql_query($SqlMaxTimeINReocrds, $this->db);




			$SqlMinTimeOutReocrds = "DELETE t1.* FROM HrmDailyAttendnessTemp t1
								LEFT JOIN (SELECT AttID, max(TimeIn) TimeIn
								FROM HrmDailyAttendnessTemp WHERE Status=1
								GROUP BY Date,BioMetricID) t2 ON t1.AttID = t2.AttID WHERE  Status=1  and  t2.TimeIn IS NULL;";
			$MinTimeOutReocrdsExec = mysql_query($SqlMinTimeOutReocrds, $this->db);
		}



		$sql = "select ai.BioMetricID,ai.DateBioID,ai.Date,ai.Status,COALESCE(AL.DateBioID ,0) as Ref,
(SELECT COALESCE(min(TimeIn),'0') from HrmDailyAttendnessTemp WHERE BioMetricID=ai.BioMetricID and Date=ai.Date and Status=0 ) as TimeIn,
(SELECT COALESCE(max(TimeIn),'0') from HrmDailyAttendnessTemp WHERE BioMetricID=ai.BioMetricID and Date=ai.Date and Status=1 ) as TimeOut,
count(*) as count,ai.DeviceID
from HrmDailyAttendnessTemp ai  LEFT JOIN HrmDailyAttendnessLog AL on  ai.DateBioID=AL.DateBioID
group by BioMetricID,Date;";
		$sqlca_bplco = mysql_query($sql, $this->db);

		while ($row = mysql_fetch_array($sqlca_bplco, MYSQL_ASSOC)) {
			$DateBioID = $row['DateBioID'];
			$employee_id = $row['BioMetricID'];
			$date = $row['Date'];
			$TimeIn = $row['TimeIn'];
			$TimeOut = $row['TimeOut'];
			$DeviceID = $row['DeviceID'];
			$Status = $row['Status'];
			$Status = $row['Ref'];

			if ($row['Ref'] == 0) {
				$SqlAryValues[] = "('$employee_id','$DateBioID','$date','$TimeIn','$TimeOut','$Status','$DeviceID')";
			} else {

				if ($row['TimeIn'] == '0' && $row['TimeOut'] != '0') {
					$SqlUpdate = "UPDATE HrmDailyAttendnessLog set TimeOut='$TimeOut' WHERE DateBioID='$DateBioID'";
				} else if ($row['TimeOut'] == '0' && $row['TimeIn'] != '0') {
					$SqlUpdate = "UPDATE HrmDailyAttendnessLog set TimeIn='$TimeIn' WHERE DateBioID='$DateBioID'";
				}

				$UpdateExec = mysql_query($SqlUpdate, $this->db);
			}
		}

		$SqlAttValues = implode(',', $SqlAryValues);


		$SqlAtt = "INSERT INTO `HrmDailyAttendnessLog`(`BioMetricID`,`DateBioID`, `Date`, `TimeIn`, `TimeOut`,`Status`,`DeviceID`) VALUES 
$SqlAttValues";
		if ($SqlAttValues != '') {
			$StmtAtt = mysql_query($SqlAtt, $this->db);
		} else {
			$StmtAtt = true;
		}

		if ($StmtAtt) {
			$SqlDeleteDup = "DELETE t1 FROM HrmDailyAttendnessLog  t1 INNER JOIN HrmDailyAttendnessLog  t2 
WHERE t1.AttID < t2.AttID AND  
t1.Date = t2.Date and 
t1.BioMetricID=t2.BioMetricID and 
t1.TimeIn=t2.TimeIn and 
t2.TimeOut=t2.TimeOut";
			//$StmtDup = mysql_query($SqlDeleteDup,$this->db);
			$records = array('Error' => false, "Msg" => "Data Saved successfully");
			$this->response($this->json($records), 200);
		} else {
			$records = array('Error' => "Unable To Submit Data", "Msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		}
		/*$records = array('Error' => "Unable To Submit Data", "Msg" => "Data saved successfully");
																		$this->response($this->json($records), 200);*/
	}



	public function getIndentDetails()
	{


		$IndentCode = $_GET['IndentCode'];

		$sql_query = "SELECT *,
(SELECT GROUP_CONCAT(RLD.LotNumber) from RmLotDetatils as RLD WHERE RLD.RmLotID in (RID.LotID)) as LotNumber


FROM `RmIndentDetails` as RID
left join IndentCategoryList as ICL on ICL.CategoryId=RID.Category
left join RmVAStations as RVS on RVS.StationID=RID.VAStationId
left join RmVehicalDetails as RVD on RVD.ID=RID.VehicalID where IndentCode='$IndentCode'";

		$sqlca_c_user = mysql_query($sql_query);

		$result = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC);
		$result['Error'] = null;
		$result['Msg'] = "Success";




		$records = array('IndentDetails' => $result);
		$this->response($this->json($records), 200);
	}


	public function postIndentDetails()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$Token = $this->getToken();
		$UserID = $Token['UserID'];


		$CategoryType = $data['CategoryType'];
		$IndentCode = $data['IndentCode'];
		$EmptyCrates = $data['EmptyCrates'];
		$NetCrates = $data['NetCrates'];
		$IceCrates = $data['IceCrates'];
		$Remarks = $data['Remarks'];

		$sqlIndentDetails = "SELECT * FROM `RmIndentDetails` where IndentCode='$IndentCode'";
		$stmtIndentDetails = mysql_query($sqlIndentDetails);
		$rowIndentDetails = mysql_fetch_array($stmtIndentDetails, MYSQL_ASSOC);

		$Outwardby = $rowIndentDetails['Outwardby'];
		$Inwardby = $rowIndentDetails['Inwardby'];

		$AryCurrentDate = $this->getCurrentDateTime();

		$TimeStamp = $AryCurrentDate['TimeStamp'];

		if ($CategoryType == 'Inward') {


			if ($Outwardby == '') {
				$result['Error'] = "Outward Details Not Found";
				$result['Msg'] = "Outward Details Not Found";
				$this->response($this->json($result), 200);
			} else if ($Inwardby != '') {
				$result['Error'] = "Inward Details Already Entered";
				$result['Msg'] = "Inward Details Already Entered";
				$this->response($this->json($result), 200);
			} else {
				$sqlIndentUpdate = "update `RmIndentDetails`  set
			InwardDateTime='$TimeStamp',
			InwardIceCrates='$IceCrates',
			InwardEmptyCrates='$EmptyCrates',
			InwardNetCrates='$NetCrates',
			Inwardby='$UserID',
			Remarks1='$Remarks'			
			where IndentCode='$IndentCode'";
				$stmtIndentUpdate = mysql_query($sqlIndentUpdate);

				if ($stmtIndentUpdate) {
					$result['Error'] = null;
					$result['Msg'] = "Details Submitted Successful";
					$this->response($this->json($result), 200);
				}
			}
		} else if ($CategoryType == 'OutWard') {
			if ($Outwardby != '') {
				$result['Error'] = "Outward Details Already Entered";
				$result['Msg'] = "Outward Details Already Entered";
				$this->response($this->json($result), 200);
			} else {
				$sqlIndentUpdate = "update `RmIndentDetails`  set
			OutwardDateTime='$TimeStamp',
			OutwardIceCrates='$IceCrates',
			OutwardEmptyCrates='$EmptyCrates',
			OutwardNetCrates='$NetCrates',
			Outwardby='$UserID',
			Remarks2='$Remarks'			
			where IndentCode='$IndentCode'";
				$stmtIndentUpdate = mysql_query($sqlIndentUpdate);

				if ($stmtIndentUpdate) {
					$result['Error'] = null;
					$result['Msg'] = "Details Submitted Successful";
					$this->response($this->json($result), 200);
				}
			}
		}
	}




	public function truncate()
	{



		/* 
																		$sql_query="truncate inputmaterial_details";
																		mysql_query($sql_query);

																		$sql_query="truncate GRADE_DETAILS";
																		mysql_query($sql_query);

																		$sql_query="truncate MATERIAL_PROCESSING_DATA";
																		mysql_query($sql_query);

																		$sql_query="truncate TIME_TRACK";
																		mysql_query($sql_query);

																		$sql_query="truncate SUB_MATERIAL_PROCESSING_DATA";
																		mysql_query($sql_query);

																		*/

		echo "test";
	}

	public function updateDashBoard()
	{

		$sql_query_imd = "SELECT * FROM `inputmaterial_details` where STATUS='0'";

		$sqlca_imd = mysql_query($sql_query_imd);

		while ($rlt = mysql_fetch_array($sqlca_imd, MYSQL_ASSOC)) {

			$LOT_NO = $rlt['LOT_NO'];

			$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$LOT_NO'";
			$sqlca_c_user = mysql_query($sql_query);
			$IS_COMPLETED = 0; //false 

			while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {


				if ($rlt['RE_ASSIGN_AVAL_WEIGHT'] == '0') {

					if ($rlt['STATUS'] == '6' || $rlt['STATUS'] == '7' || $rlt['STATUS'] == '8') {
						$IS_COMPLETED = 1;
					} else {
						$IS_COMPLETED = 0;
						break;
					}
				} else {
					$IS_COMPLETED = 0;
					break;
				}

				if ($IS_COMPLETED == 1) {
					$sql_imd_update = "UPDATE `inputmaterial_details` SET `STATUS`='1' WHERE LOT_NO='$LOT_NO'";
					$sqlca_imd_update = mysql_query($sql_imd_update, $this->db);
				}
			}
		}

		echo "Done";
	}



	public function postEditLot()
	{

		$Error = "No Access To Edit";

		/* $json = file_get_contents('php://input');
																		$data = json_decode($json, true);

																		$LotNumber = $data['LotNumber'];
																		$TicketID = $data['TicketID'];
																		$GraderID = $data['GraderID'];
																		$AgentID = $data['AgentID'];
																		$PurchaseCount = $data['PurchaseCount'];
																		$DriverID = $data['DriverID'];
																		$VehicalID = $data['VehicalID'];
																		$PurchaseCategoryID = $data['PurchaseCategoryID'];
																		$PurchaseStationID = $data['PurchaseStationID'];
																		$PurchaseWeight = $data['PurchaseWeight'];
																		$FarmAddress = $data['FarmAddress'];
																		$PurchaseDate = $data['PurchaseDate'];
																		$PostedUserID = $data['PostedUserID'];
																		$LotID = $data['LotID'];
																		$PostedDate = date('Y-m-d H:i:s');
																		//IP ADDRESS
																		$ipaddress = $_SERVER['REMOTE_ADDR'];

																		/*********************************/

		/* $LotNumber = $this->getFormatedLotNumber($PurchaseStationID, $LotNumber);

																		$LotAval = $this->checkLotNoAval($PurchaseDate, $LotNumber, $LotID);

																		if (!$LotAval) {
																			$Error = "Lot Already Exist";
																		} else if ($PostedUserID == '') {
																			$Error = "Session Expired,Please Login and Try Again";
																		} else {

																			$SqlLotDetails = "SELECT * FROM `RmLotDetatils` WHERE RmLotID='$LotID'";
																			$StmtLotDetails = mysql_query($SqlLotDetails);
																			$RowLotDetails = mysql_fetch_array($StmtLotDetails, MYSQL_ASSOC);

																			$LotJsonData = json_encode($RowLotDetails);


																			$SqlEditData = "INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, `IPAddress`) VALUES ('$TicketID','$LotJsonData','$PostedDate','$PostedUserID','$ipaddress')";

																			$SqlPostData = mysql_query($SqlEditData, $this->db);


																			if ($SqlPostData) {


																				$SqlUpdate = "UPDATE `RmLotDetatils` SET 
																																																																																											`GraderID`='$GraderID',
																																																																																											 `AgentID`='$AgentID',
																																																																																											 `DriverID`='$DriverID',
																																																																																											 `LotNumber`='$LotNumber',
																																																																																											 `VehicalID`='$VehicalID',
																																																																																											 `PurchaseCount`='$PurchaseCount',
																																																																																											 `PurchaseCategoryID`='$PurchaseCategoryID',
																																																																																											 `PurchaseStationID`='$PurchaseStationID',
																																																																																											 `PurchaseWeight`='$PurchaseWeight',
																																																																																											 `FarmAddress`='$FarmAddress',
																																																																																											 `PurchaseDate`='$PurchaseDate',
																																																																																											 `PostedUserID`='$PostedUserID'
																																																																																											  WHERE `RmLotID`='$LotID'";

																				$SqlUpdate = "UPDATE `RmLotDetatils` SET 
																																																																																				 `PurchaseCount`='$PurchaseCount',
																																																																																				 `PurchaseWeight`='$PurchaseWeight'
																																																																																				  WHERE `RmLotID`='$LotID'";

																				$StmtUpdate = mysql_query($SqlUpdate, $this->db);
																			}
																			if ($StmtUpdate) {
																				$this->UpdateTicketStatus($TicketID, 1);
																				$Error = null;
																				$Msg = "Lot Updated";
																			} else if (!$SqlPostData) {
																				$Error = "Ticket Already Closed";
																			} else {
																				$Error = "Unable to Update Lot Please Contact Admin";
																			}
																		}

																		*/

		$Response = array('Error' => $Error, "Msg" => $Msg);


		$this->response($this->json($Response), 200);
	}



	public function postEditBooking()
	{

		$Error = "No Access To Edit";

		/*$json = file_get_contents('php://input');
																		$data = json_decode($json, true);

																		$BookingNumber = $data['BookingNumber'];
																		$GraderID = $data['GraderID'];
																		$AgentID = $data['AgentID'];
																		$BookingCount = $data['BookingCount'];
																		$DriverID = $data['DriverID'];
																		$VehicalID = $data['VehicalID'];
																		$PurchaseCategoryID = $data['PurchaseCategoryID'];
																		$BookingStationID = $data['BookingStationID'];
																		$BookingWeight = $data['BookingWeight'];
																		$FarmAddress = $data['FarmAddress'];
																		$BookingDate = $data['BookingDate'];
																		$PostedUserID = $data['PostedUserID'];
																		$LotID = $data['LotID'];
																		$PostedDate = date('Y-m-d H:i:s');

																		$BookingNumber = $this->getFormatedBookingNumber($BookingStationID, $BookingNumber);

																		$LotAval = $this->checkLotNoAval($BookingDate, $BookingNumber, $LotID);

																		if (!$LotAval) {
																			$Error = "Lot Already Exist";
																		} else if ($PostedUserID == '') {
																			$Error = "Session Expired,Please Login and Try Again";
																		} else {

																			$SqlLotDetails = "SELECT * FROM `RmBookingDetatils` WHERE RmBookingID='$LotID'";
																			$StmtLotDetails = mysql_query($SqlLotDetails);
																			$RowLotDetails = mysql_fetch_array($StmtLotDetails, MYSQL_ASSOC);

																			$LotJsonData = json_encode($RowLotDetails);

																			$SqlUpdate = "UPDATE `RmBookingDetatils` SET 
																																																																																											`GraderID`='$GraderID',
																																																																																											 `AgentID`='$AgentID',
																																																																																											 `DriverID`='$DriverID',
																																																																																											 `BookingNumber`='$BookingNumber',
																																																																																											 `VehicalID`='$VehicalID',
																																																																																											 `BookingCount`='$BookingCount',
																																																																																											 `PurchaseCategoryID`='$PurchaseCategoryID',
																																																																																											 `BookingStationID`='$BookingStationID',
																																																																																											 `BookingWeight`='$BookingWeight',
																																																																																											 `FarmAddress`='$FarmAddress',
																																																																																											 `BookingDate`='$BookingDate',
																																																																																											 `PostedUserID`='$PostedUserID'
																																																																																											  WHERE `RmBookingID`='$LotID'";

																			$StmtUpdate = mysql_query($SqlUpdate, $this->db);

																			if ($StmtUpdate) {

																				$Msg = "Booking Updated";
																			} else {
																				$Error = "Unable to Update Booking Please Contact Admin";
																			}
																		}
																		*/
		$Response = array('Error' => $Error, "Msg" => $Msg);
		$this->response($this->json($Response), 200);
	}




	public function postEditBill()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$TicketID = $data['TicketID'];
		$FinancierID = $data['FinancierID'];
		$Deductions = $data['Deductions'];
		$Remarks = $data['Remarks'];
		$FinancialDate = $data['FinancialDate'];
		$CreditDays = $data['CreditDays'];
		$CreditDate = $data['CreditDate'];
		$InterestRate = $data['InterestRate'];
		$CreditRemarks = $data['CreditRemarks'];
		$BillAgentID = $data['AgentID'];
		$PostedUserID = $data['PostedUserID'];
		$LotID = $data['LotID'];
		$PostedDate = date('Y-m-d H:i:s');
		//IP ADDRESS
		$ipaddress = $_SERVER['REMOTE_ADDR'];

		/*********************************/

		$SqlBillDetails = "SELECT  RLD.RmReferenceID as ArrivalID,RBD.* FROM RmLotDetatils as RLD
							left join RmBillDetails as RBD on RBD.ArrivalID=RLD.RmReferenceID 
							WHERE  RLD.RmLotID='$LotID'";
		$StmtBillDetails = mysql_query($SqlBillDetails);
		$RowBillDetails = mysql_fetch_array($StmtBillDetails, MYSQL_ASSOC);

		$ArrivalID = $RowBillDetails['ArrivalID'];

		$BillJsonData = json_encode($RowBillDetails);


		$SqlEditData = "INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, `IPAddress`) VALUES ('$TicketID','$BillJsonData','$PostedDate','$PostedUserID','$ipaddress')";

		$SqlPostData = mysql_query($SqlEditData, $this->db);




		$SqlUpdate = "UPDATE `RmBillDetails` SET 
		`FinancierID`='$FinancierID',
		 `Deductions`='$Deductions',
		 `Remarks`='$Remarks',`CreditDate`='$CreditDate',`FinancialDate`='$FinancialDate',
		 `InterestRate`='$InterestRate',`CreditRemarks`='$CreditRemarks',`BillAgentID` = '$BillAgentID'
		  WHERE `ArrivalID`='$ArrivalID'";


		if ($SqlPostData) {

			$SqlUpdate = "UPDATE  `RmArrivalDetatils` set `RmSupplierID`='$FinancierID' , `AgentID`='$BillAgentID' WHERE `RmArrivalID`='$ArrivalID'";
			$StmtUpdate = mysql_query($SqlUpdate, $this->db);

			$SqlUpdate = "UPDATE  `RmLotDetatils` set AgentID='$BillAgentID' WHERE `RmReferenceID`='$ArrivalID'";
			$StmtUpdate = mysql_query($SqlUpdate, $this->db);


			$SqlUpdate = "UPDATE `RmBillDetails` SET 
							`FinancierID`='$FinancierID',
							 `Deductions`='$Deductions',
							 `Remarks`='$Remarks',`CreditDate`='$CreditDate',`FinancialDate`='$FinancialDate',`CreditDays`='$CreditDays',
							 `InterestRate`='$InterestRate',`CreditRemarks`='$CreditRemarks',`BillAgentID` = '$BillAgentID'
							  WHERE `ArrivalID`='$ArrivalID'";

			$StmtUpdate = mysql_query($SqlUpdate, $this->db);



			if ($StmtUpdate) {
				$this->UpdateTicketStatus($TicketID, 1);
				$Response = array('Error' => null, "Msg" => "Lot Updated");
			} else {
				$SqlEditData = "delete from  `AdminEditLogData` where TicketID='$TicketID'";

				$SqlPostData = mysql_query($SqlEditData, $this->db);
				$Response = array('Error' => "Unable to Update Lot Please Contact Admin", "Msg" => "Lot Updated");
			}

			$this->response($this->json($Response), 200);
			$this->UpdateTicketStatus($TicketID, 1);
		} else {
			$records = array('Error' => "Ticket Already Closed");
			$this->response($this->json($records), 200);
		}
	}


	public function UpdateTicketStatus($TicketID, $Status)
	{

		$AryCurrentDate = $this->getCurrentDateTime();
		$FixedDate = $AryCurrentDate['TimeStamp'];


		$SqlUpdate = "UPDATE `AdminTickets` SET 
							`TicketStatus`='$Status',
							`FixedDate`='$FixedDate'
							  WHERE `TicketID`='$TicketID'";

		$StmtUpdate = mysql_query($SqlUpdate, $this->db);
	}


	public function postDeleteGrading()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$TicketID = $data['TicketID'];
		$LotID = $data['LotID'];
		$PostedUserID = $data['PostedUserID'];
		$AryCurrentDate = $this->getCurrentDateTime();
		$PostedDate = $AryCurrentDate['TimeStamp'];

		$ipaddress = $_SERVER['REMOTE_ADDR'];

		/*********************************/

		$DataQuery = "SELECT RLD.RmReferenceID as ArrivalID,HLG.*,RBD.* FROM RmLotDetatils as RLD
		left join PpcHLGradingDetails as HLG on HLG.LotID=RLD.RmLotID  AND HlG.CreatedFrom=0
		left join RmBillDetails as RBD on RBD.ArrivalID=RLD.RmReferenceID 
		WHERE  RLD.RmLotID='$LotID'";
		$DataExec = mysql_query($DataQuery);
		$RowData = mysql_fetch_array($DataExec, MYSQL_ASSOC);
		$ArrivalID = $RowData['ArrivalID'];

		// $DataQuery = "SELECT * FROM `PpcHLGradingDetails`
		// 				left join RmBillDetails on PpcHLGradingDetails.LotID=RmBillDetails.ArrivalID 
		// 				WHERE  PpcHLGradingDetails.LotID='$LotID'";
		// $DataExec = mysql_query($DataQuery);
		// $RowData = mysql_fetch_array($DataExec, MYSQL_ASSOC);

		$LotJsonData = json_encode($RowData);


		$SqlEditData = "INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, 
			`IPAddress`) VALUES ('$TicketID','$LotJsonData','$PostedDate','$PostedUserID','$ipaddress')";

		$SqlPostData = mysql_query($SqlEditData, $this->db);


		if ($SqlPostData) {


			$DeleteHlQuery = "delete from PpcHLGradingDetails where LotID='$LotID' AND CreatedFrom=0 ";
			$DeleteHlBrkQuery = "delete from PpcHLGradingBreakup where LotID='$LotID' AND CreatedFrom=0 ";
			$DeleteHlDefQuery = "delete from PpcHLGradingDefects where LotID='$LotID'";
			$DeleteBillQuery = "delete from RmBillDetails where ArrivalID='$ArrivalID'";
			$DeleteBillBrkQuery = "delete from RmBillBreakup where ArrivalID='$ArrivalID'";

			$DeleteExec = mysql_query($DeleteHlQuery, $this->db);
			$DeleteExec = mysql_query($DeleteHlBrkQuery, $this->db);
			$DeleteExec = mysql_query($DeleteHlDefQuery, $this->db);
			$DeleteExec = mysql_query($DeleteBillQuery, $this->db);
			$DeleteExec = mysql_query($DeleteBillBrkQuery, $this->db);


			$this->UpdateTicketStatus($TicketID, 1);

			$records = array('Error' => null, "Msg" => "Grading Deleted");
			$this->response($this->json($records), 200);
		} else {
			$records = array('Error' => "Ticket Already Closed");
			$this->response($this->json($records), 200);
		}
	}



	public function postDeleteBillOnly()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$TicketID = $data['TicketID'];
		$LotID = $data['LotID'];
		$PostedUserID = $data['PostedUserID'];
		$AryCurrentDate = $this->getCurrentDateTime();
		$PostedDate = $AryCurrentDate['TimeStamp'];

		$ipaddress = $_SERVER['REMOTE_ADDR'];

		/*********************************/



		$DataQuery = "SELECT RLD.RmReferenceID as ArrivalID,HLG.*,RBD.* FROM RmLotDetatils as RLD
        				left join PpcHLGradingDetails as HLG on HLG.LotID=RLD.RmLotID  AND HLG.CreatedFrom=0
						left join RmBillDetails as RBD on RBD.ArrivalID=RLD.RmReferenceID 
						WHERE  RLD.RmLotID='$LotID'";
		$DataExec = mysql_query($DataQuery);
		$RowData = mysql_fetch_array($DataExec, MYSQL_ASSOC);
		$ArrivalID = $RowData['ArrivalID'];

		$LotJsonData = json_encode($RowData);


		$SqlEditData = "INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, 
			`IPAddress`) VALUES ('$TicketID','$LotJsonData','$PostedDate','$PostedUserID','$ipaddress')";

		$SqlPostData = mysql_query($SqlEditData, $this->db);


		if ($SqlPostData) {

			$DeleteBillQuery = "delete from RmBillDetails where ArrivalID='$ArrivalID'";
			$DeleteBillBrkQuery = "delete from RmBillBreakup where ArrivalID='$ArrivalID'";

			$DeleteExec = mysql_query($DeleteBillQuery, $this->db);
			$DeleteExec = mysql_query($DeleteBillBrkQuery, $this->db);


			$this->UpdateTicketStatus($TicketID, 1);

			$records = array('Error' => null, "Msg" => "Only Bill is Deleted");
			$this->response($this->json($records), 200);
		} else {
			$records = array('Error' => "Ticket Already Closed");
			$this->response($this->json($records), 200);
		}
	}

	public function postHonDeleteGrading()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$TicketID = $data['TicketID'];
		$LotID = $data['LotID'];
		$PostedUserID = $data['PostedUserID'];
		$AryCurrentDate = $this->getCurrentDateTime();
		$PostedDate = $AryCurrentDate['TimeStamp'];

		$ipaddress = $_SERVER['REMOTE_ADDR'];

		/*********************************/


		$DataQuery = "SELECT RLD.RmReferenceID as ArrivalID,HLG.*,RBD.* FROM RmLotDetatils as RLD
		left join PpcHonGradingDetails as HLG on HLG.LotID=RLD.RmLotID
		left join RmBillDetails as RBD on RBD.ArrivalID=RLD.RmReferenceID 
		WHERE  RLD.RmLotID='$LotID'";


		// $DataQuery = "SELECT * FROM `PpcHonGradingDetails`
		// 				left join RmBillDetails on PpcHonGradingDetails.LotID=RmBillDetails.ArrivalID 
		// 				WHERE  PpcHonGradingDetails.LotID='$LotID'";
		$DataExec = mysql_query($DataQuery);
		$RowData = mysql_fetch_array($DataExec, MYSQL_ASSOC);
		$ArrivalID = $RowData['ArrivalID'];

		$LotJsonData = json_encode($RowData);


		$SqlEditData = "INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, 
			`IPAddress`) VALUES ('$TicketID','$LotJsonData','$PostedDate','$PostedUserID','$ipaddress')";

		$SqlPostData = mysql_query($SqlEditData, $this->db);


		if ($SqlPostData) {


			$DeleteHlQuery = "delete from PpcHonGradingDetails where LotID='$LotID'";
			$DeleteHlBrkQuery = "delete from PpcHonGradingBreakup where LotID='$LotID'";
			$DeleteBillQuery = "delete from RmBillDetails where ArrivalID='$ArrivalID'";
			$DeleteBillBrkQuery = "delete from RmBillBreakup where ArrivalID='$ArrivalID'";

			$DeleteExec = mysql_query($DeleteHlQuery, $this->db);
			$DeleteExec = mysql_query($DeleteHlBrkQuery, $this->db);
			$DeleteExec = mysql_query($DeleteBillQuery, $this->db);
			$DeleteExec = mysql_query($DeleteBillBrkQuery, $this->db);


			$this->UpdateTicketStatus($TicketID, 1);

			$records = array('Error' => null, "Msg" => "Hon Grading Deleted");
			$this->response($this->json($records), 200);
		} else {
			$records = array('Error' => "Ticket Already Closed");
			$this->response($this->json($records), 200);
		}
	}



	public function deleteProcessRequest()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';


		$WEIGHT_IN_KGS = $data['WEIGHT_IN_KGS'];
		$REFERENCE_ID = $data['REFERENCE_ID'];
		$LOT_NUMBER = $data['LOT_NUMBER'];
		$AVAL_WEIGHT_TO_PROCESS = $data['AVAL_WEIGHT_TO_PROCESS'];
		$WEIGHT_UNDER_PROCESSING = $data['WEIGHT_UNDER_PROCESSING'];

		$SQL_SUB_M_P_D = "SELECT * FROM MATERIAL_PROCESSING_DATA WHERE REFERENCE_ID='$REFERENCE_ID'";
		$STMT_SUB_M_P_D = mysql_query($SQL_SUB_M_P_D);
		$SUB_M_P_D_ROW = mysql_fetch_array($STMT_SUB_M_P_D, MYSQL_ASSOC);


		$REFERENCE_ID_OLD = $SUB_M_P_D_ROW['SUB_REF_ID'];


		if ($REFERENCE_ID_OLD == '' || $REFERENCE_ID_OLD == 'NULL') {


			$sql_update = "UPDATE `inputmaterial_details` SET 
							`AVAL_WEIGHT_TO_PROCESS`='$WEIGHT_IN_KGS',
							`WEIGHT_UNDER_PROCESSING`='$WEIGHT_UNDER_PROCESSING'
							WHERE `LOT_NO`='$LOT_NUMBER'";
		} else {

			$SQL_SUB_M_P_D = "SELECT * FROM MATERIAL_PROCESSING_DATA WHERE REFERENCE_ID='$REFERENCE_ID_OLD'";
			$STMT_SUB_M_P_D = mysql_query($SQL_SUB_M_P_D);
			$SUB_M_P_D_ROW = mysql_fetch_array($STMT_SUB_M_P_D, MYSQL_ASSOC);


			$RE_ASSIGN_AVAL_WEIGHT = $SUB_M_P_D_ROW['RE_ASSIGN_AVAL_WEIGHT'];

			$AVAL_WEIGHT_TO_PROCESS = $RE_ASSIGN_AVAL_WEIGHT + $WEIGHT_IN_KGS;

			$sql_update = "UPDATE `MATERIAL_PROCESSING_DATA` SET `RE_ASSIGN_AVAL_WEIGHT`=$AVAL_WEIGHT_TO_PROCESS
					WHERE `REFERENCE_ID`='$REFERENCE_ID_OLD'";
		}


		$sqlca_update = mysql_query($sql_update, $this->db);

		if ($sqlca_update) {
			$sql_update = "DELETE FROM `MATERIAL_PROCESSING_DATA` WHERE
					 `REFERENCE_ID`='$REFERENCE_ID'";
			$sqlca_update = mysql_query($sql_update, $this->db);

			$records = array('status' => $STATUS_SUCCESS, "msg" => "Deleted Successful");
			$this->response($this->json($records), 200);
		} else {
			$records = array('status' => $STATUS_FAILURE, "msg" => "Unable to Delete");
			$this->response($this->json($records), 200);
		}
	}







	private function pushFcm($UNIT_ID, $data)
	{



		$key = "AAAAtyt5poE:APA91bFgmx8I9ZOiXwT-y-WkaUizpbKEhGgOCDEATzkrf4p8OUWx0WXFGRe35zzJXpk7_l0wXD2IwbXWZ0AwrFkj9KFznud-RkDY1M86LsCMU4nMLlhx7C9Y59KwpjpsS6Wf6H5V2oiR";
		//test kry tcf 
		// $key="AAAArECVdjA:APA91bFbj_JGXKU7aFC2FPqNaknFIGM2Vk1w4ibtmuTc3DrAB9ZlVnxSmFmHEgDDA0GHNm2_KJnIBnTtPCvPBe50pa3J9nqQBpcmiBRtz7SEWMv9VWPX-bx7Cduhj2ZgnQ31vtXA9wk_ST1rJunYFg3A0HZ_oe1Sng";

		/*
																		if($data['PROCESSING_TYPE']!=''){ 
																			$sqlca="SELECT * FROM `login` where UNIT_ID='$UNIT_ID' and USER_TYPE='INCHARGE'";

																		$title="Required " .$data['PROCESSING_TYPE'];
																		$body="Weight ".$data['WEIGHT_IN_KGS'] ."Kgs";
																			}else{
																				$sqlca="SELECT * FROM `login` where UNIT_ID='$UNIT_ID' and USER_TYPE='INCHARGE'";
																				$title ="Request Accepted";
																			}
																			*/


		$UserID = $data['USER_ID'];


		$sqlca = "SELECT * FROM `AdminLoginDetails` where UserID='$UserID'";
		$sqlca_c_user = mysql_query($sqlca);
		$rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC);
		$USER_NAME = $rlt['UserName'];

		$title = $data['FUNCTION'];
		//$title="God is calling you" ;
		$body = $USER_NAME . ' - ' . $data['DATE'];
		//$body="Truly my soul silently waits for God; From Him comes my salvation.  Psalm 62:1";
		//$Url="https://www.youtube.com/watch?v=ekrlrFSOB0s";

		$sqlca = "SELECT * FROM `AdminLoginDetails` where UserLoginID='9912275234'";
		$sqlca_c_user = mysql_query($sqlca);



		$registrationIDs = array();


		while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
			$result[] = $rlt;
			array_push($registrationIDs, $rlt['DeviceID']);
		}


		//array_push($registrationIDs,'dwEYtI7AMPU:APA91bEGwLfe66u1fHJwNx5nRvYSZD84tsRwl78Wujx-HNJNfo6p1sA_Tnap7F8fBEIrNNNPFav4eQee-R0j83Vx73jkAzXYA_Ekqw3VLjorWUrOP6svspZ6XGkR79o98xE2de1WbE5w');



		$fcmData = array(
			'title' => $title,
			'message' => $body,
			'url' => $Url,
			'URL' => $Url,
			'professional_id' => $professional_id,
			'status' => $status,
			'order_id' => $order_id,
			'distance' => $distance,
			'time' => $time,
			'bussiness_name' => $bussiness_name,
			'user_id' => $user_id,
			'fare' => $fare,

			'is_background' => true,
			'image' => '22',
			'payload' => "default",
			'timestamp' => date('Y-m-d G:i:s')
		);


		//print_r($registrationIDs ) ;


		$fcmMsg = array(
			'body' => $body,
			'title' => $title,
			'id' => '22',
			'sound' => "none",
			'color' => "#203E78"
		);










		// 'to' => $singleID ; // expecting a single ID
		//'registration_ids' => $registrationIDs ; // expects an array of ids
		// 'priority' => 'high' ; // options are normal and high, if not set, defaults to high.

		$fcmFields = array(
			//'to' => $singleID,
			'registration_ids' => $registrationIDs,
			'priority' => 'high',
			'notification' => $fcmMsg,
			'data' => $fcmData
		);

		//echo API_ACCESS_KEY;

		$headers = array(
			'Authorization: key=' . $key,
			'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
		$result = curl_exec($ch);
		curl_close($ch);
		//echo $result . "\n\n";


		/* $error = array('status' => "Failed", "msg" => "Invalid Request Paramaters");
																		$this->response($this->json($error), 200);

																		*/
	}




	public function getPoList()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';



		$SQL_INVOICE_DATA = "SELECT * FROM `INVOICE_DATA` where INS_STATUS=0  order by ID DESC limit 100";
		$STMT_SQL_INVOICE_DATA = mysql_query($SQL_INVOICE_DATA);


		while ($rlt = mysql_fetch_array($STMT_SQL_INVOICE_DATA, MYSQL_ASSOC)) {

			$rlt['SAMPLING_LIST'] = $this->getSamplingPlan($rlt['ID']);
			//$rlt['TOTAL_NO_MCS'] = $rlt['SAMPLING_LIST']['TOTAL_MCS'];
			$result[] = $rlt;
		}

		//echo count($result); 
		if (count($result) != 0) {
			$records = array('status' => $STATUS_SUCCESS, "PO_LIST" => $result);
			$this->response($this->json($records), 200);
		} else {
			$records = array('status' => $STATUS_FAILURE, "msg" => "No Records Found");
			$this->response($this->json($records), 200);
		}
	}


	public function getSamplingPlan($ID)
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		//$ID=$_GET['INV_ID'];

		$TOTAL_MCS = 0;

		$sql_code_list = "SELECT sum(T_O_MCS) as O_MCS FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID'";
		$stmt_code = mysql_query($sql_code_list);
		$rlt = mysql_fetch_array($stmt_code, MYSQL_ASSOC);
		$O_MCS = $rlt['O_MCS'];

		$TOTAL_SAMPLES = (sqrt($O_MCS) + 1) / 2;
		$TOTAL_SAMPLES = round($TOTAL_SAMPLES);

		$sql_code_list = "SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `CODE_LIST_ID` ASC";
		$stmt_code = mysql_query($sql_code_list);

		$sample = 1;

		while ($rlt = mysql_fetch_array($stmt_code, MYSQL_ASSOC)) {

			$TOTAL_MCS = $TOTAL_MCS + $rlt['T_O_MCS'];
			$ARY_GRADES = explode(',', $rlt['GRADES']);
			$ARY_MCS = explode(',', $rlt['MCS']);

			$count = 0;
			foreach ($ARY_GRADES as $VALUE) {

				$MCS = $ARY_MCS[$count];


				$EACH_SAMPLE = ($MCS / $O_MCS) * $TOTAL_SAMPLES;
				$EACH_SAMPLE = round($EACH_SAMPLE);

				if ($EACH_SAMPLE > 0) {

					$rlt['GRADES'] = $VALUE;
					$rlt['MCS'] = $MCS;
					$rlt['EACH_SAMPLE'] = $EACH_SAMPLE;
					$rlt['TOTAL_SAMPLES'] = $TOTAL_SAMPLES;
					$rlt['SERIES'] = $sample . '-' . ($sample + $EACH_SAMPLE - 1);
					$sample = $sample + $EACH_SAMPLE;
					$result[] = $rlt;
				}

				$count++;
			}
		}

		return $result;
	}


	public function PostInspectionData()
	{

		$json = file_get_contents('php://input');
		//$data = json_decode($json,true);
		$ary_data = json_decode($json, true);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		//$DATA = $data['DATA'];
		$ary_data = json_decode($ary_data, true);

		// print_r($ary_data);
		foreach ($ary_data as $item) {


			$BIG_PIECES_UN = $item['BigPieces'];
			$SMALL_PIECES_UN = $item['SmallPieces'];
			$UNIFORMITY = round($BIG_PIECES_UN / $SMALL_PIECES_UN, 1); //c
			$GLAZE = round((($FROZEN_WEIGHT - $DEGLAZED_WEIGHT) / $FROZEN_WEIGHT) * 100, 1); //c
			$NO_OF_SAMPLES = $item['Sample_id'];
			$COUNT = 0;
			$USE_BY_DATE = 0;
			$DECLARED_NET_WT = 0;
			$TOTAL_DEFECT_G = 0;
			$TOTAL_DEFECT_P = 0;
			$OTHER_DEFECTS = 0;

			$APPEARANCE = 'GOOD'; //c
			$TEXTURE_COLOUR = ''; //c
			$ODOR_FLAVOUR = ''; //c
			$FOREIGN_MATTER = 'NIL'; //c
			$HEAVY_FILTH = 'NIL'; //c

			$BIG_PIECES = $item['BigPieces'];
			$BLACK_SPOT_MEAT = $item['BlackSpotMeat'];
			$BLACK_TAIL = $item['BlackTail'];
			$BROKEN_DAMAGE = $item['BrokenOrDamage'];
			$CODE_LIST_ID = $item['CODE_LIST_ID']; //c //
			$DAY_CODE = $item['DAY_CODE']; //
			$DECOMPOSITION = $item['Decomposition']; //c
			$DEGLAZED_WEIGHT = $item['Deglazedwt']; //Deglazedwt
			$DEHYDRATION = $item['Dehydration']; //c
			$DISCOLORATION = $item['Discoloration']; //c
			$FROZEN_WEIGHT = $item['FrozenWt'];
			$GRADE = $item['GRADE']; //
			$HANGING_MEAT = $item['HangingMeat']; //
			$INV_ID = $item['INVOICE_ID']; //
			$IMPROPER_DEVINED = $item['ImproperDeveined']; //
			$IMPROPER_PEELED = $item['ImproperPeeled']; //
			$LongThroatMeat = $item['LongThroatMeat']; //
			$SampleProfile = $item['SampleProfile']; //
			$SampleProfile2 = $item['SampleProfile2']; //
			$SMALL_PIECES = $item['SmallPieces'];
			$SOFT_SHELL = $item['SoftShell'];
			$TRACEABILITY = $item['TRACABILITY']; //
			$TAIL_OFF = $item['TailFanOff']; //
			$TOTAL_PIECES = $item['TotalPieces'];
			$VARITY_ID = $item['VARITY_ID'];

			$sql_count = "select count(*) as TOTAL from INSP_DATA where INV_ID='$INV_ID' and CODE_LIST_ID='$CODE_LIST_ID' and NO_OF_SAMPLES='$NO_OF_SAMPLES'";
			$sqlca_post = mysql_query($sql_count, $this->db);
			$rlt = mysql_fetch_array($sqlca_post, MYSQL_ASSOC);
			$TOTAL = $rlt['TOTAL'];

			if ($TOTAL == 0 && $FROZEN_WEIGHT != '') {

				$ss_post = "INSERT INTO `INSP_DATA`(
					 `INV_ID`,`VARITY_ID`,`CODE_LIST_ID`, `GRADE`, `TRACEABILITY`, `DAY_CODE`,
					 `NO_OF_SAMPLES`, `FROZEN_WEIGHT`, `DEGLAZED_WEIGHT`,
					 `COUNT`, `PRODUCTION_DATE`, `USE_BY_DATE`, `DECLARED_NET_WT`,
					 `TOTAL_PIECES`, `UNIFORMITY`, `GLAZE`, `APPEARANCE`,
					 `TEXTURE_COLOUR`, `ODOR_FLAVOUR`, `FOREIGN_MATTER`,
					 `HEAVY_FILTH`, `DEHYDRATION`, `DISCOLORATION`,
					 `DECOMPOSITION`, `BROKEN_DAMAGE`, `HANGING_MEAT`,
					 `BLACK_SPOT_MEAT`, `SOFT_SHELL`, `IMPROPER_PEELED`,
					 `IMPROPER_DEVINED`, `TAIL_OFF`, `BLACK_TAIL`, `TOTAL_DEFECT_G`,
					 `TOTAL_DEFECT_P`, `OTHER_DEFECTS`)
					 VALUES (
					 '$INV_ID','$VARITY_ID','$CODE_LIST_ID','$GRADE','$TRACEABILITY','$DAY_CODE','$NO_OF_SAMPLES','$FROZEN_WEIGHT','$DEGLAZED_WEIGHT',
					 '$COUNT','$PRODUCTION_DATE','$USE_BY_DATE','$DECLARED_NET_WT','$TOTAL_PIECES','$UNIFORMITY','$GLAZE',
					 '$APPEARANCE','$TEXTURE_COLOUR','$ODOR_FLAVOUR','$FOREIGN_MATTER','$HEAVY_FILTH','$DEHYDRATION','$DISCOLORATION',
					 '$DECOMPOSITION','$BROKEN_DAMAGE','$HANGING_MEAT','$BLACK_SPOT_MEAT','$SOFT_SHELL','$IMPROPER_PEELED',
					 '$IMPROPER_DEVINED','$TAIL_OFF',
					 '$BLACK_TAIL','$TOTAL_DEFECT_G','$TOTAL_DEFECT_P','$OTHER_DEFECTS'
					 )";


				$sqlca_post = mysql_query($ss_post, $this->db);

				$SAMPLE_ID = mysql_insert_id();

				$FROZEN_W = $item['FrozenWt_img']; //c //
				$DEGLAZED_W = $item['Deglazedwt_img']; //c //
				$TOTAL_PIECES = $item['TotalPieces_img']; //c //
				$SAMPLE_1 = $item['SampleProfile_img']; //c //
				$SAMPLE_2 = $item['SampleProfile2_img']; //c // 

				$sql_images = "INSERT INTO `PRODUCT_INSP_IMAGESS`(`INV_ID`, `SAMPLE_ID`, `FROZEN_W`, `DEGLAZED_W`, `TOTAL_PIECES`,
				`SAMPLE_1`, `SAMPLE_2`) VALUES ('$INV_ID','$SAMPLE_ID','$FROZEN_W','$DEGLAZED_W','$TOTAL_PIECES',
				'$SAMPLE_1','$SAMPLE_2')";

				$ss_sql_images = mysql_query($sql_images, $this->db);
			} else {
			}
		}





		//echo mysql_errno($this->db);
		$records = array('status' => $STATUS_SUCCESS, "msg" => "Data Updated");
		$this->response($this->json($records), 200);

		// */
	}


	public function PostImageData()
	{

		$json = file_get_contents('php://input');
		//$ary_data = json_decode($json,true);
		print_r($_POST);
		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		/*
																		echo $name = $_POST['name'];

																		//getting file info from the request 
																		$fileinfo = pathinfo($_FILES['image']['name']);

																		//getting the file extension 
																		$extension = $fileinfo['extension'];

																		//file url to store in the database 
																		$file_url = $upload_url . getFileName() . '.' . $extension;

																		//file path to upload in the server 
																		$file_path = $upload_path . getFileName() . '.'. $extension; 

																		//trying to save the file in the directory 
																		try{
																			//saving the file 
																			move_uploaded_file($_FILES['image'],$name,$file_path);
																			$sql = "INSERT INTO `db_images`.`images` (`img_id`, `url`, `name`) VALUES (NULL, '$file_url', '$name');";
																			$stmt_code = mysql_query($sql);

																			//adding the path and name to database 
																			if(($stmt_code){

																				//filling response array with values 
																				$response['error'] = false; 
																				$response['url'] = $file_url; 
																				$response['name'] = $name;
																			}
																		//if some error occurred 
																		}
																		catch(Exception $e){
																			$response['error']=true;
																			$response['message']=$e->getMessage();
																		} 




																			// echo mysql_errno($this->db);
																			$records = array('status' => $STATUS_SUCCESS, "msg" => "Data Updated");
																			$this->response($this->json($records), 200);

																		// */
	}


	public function getMenu()
	{

		$Token = $this->getToken();
		// print_r($Token);

		$DeptID = $Token['DeptID'];
		$UserID = $Token['UserID'];
		$SubDeptID = $Token['SubDeptID'];
		$UnitID = $Token['UnitID'];
		$AccessType = $Token['AccessType'];

		if ($DeptID == 7) {
			$SqlDashBoard = "SELECT Title,TypeID,Icon,Link,Package,ClassType,SubLink  FROM AppDashboards 
	   WHERE TypeID ='$SubDeptID'  and DeptID='$DeptID' or DeptID=0 
		   ORDER by SortOrder";
		} else {
			$SqlDashBoard = "SELECT Title,TypeID,Icon,Link,Package,ClassType,SubLink  FROM AppDashboards WHERE DeptID='$DeptID' or DeptID=0  ORDER by SortOrder";
		}


		$sqlca_c_user = mysql_query($SqlDashBoard);

		if (mysql_num_rows($sqlca_c_user) != '0') {
			while ($rlt = mysql_fetch_array($sqlca_c_user, MYSQL_ASSOC)) {
				$result['Title'] = $rlt['Title'];
				$result['TypeId'] = $rlt['TypeID'];
				$result['Icon'] = $rlt['Icon'];
				$result['Link'] = $rlt['Package'] . $rlt['Link'];
				$result['SubLink'] = $rlt['Package'] . $rlt['SubLink'];
				$result['ClassType'] = $rlt['ClassType'];
				$Data[] = $result;
			}


			// $DropDownData=$this->getDropDownData(1);


			$temp = array(
				'Error' => null,
				"Data" => $Data
				// "DropDownData"=>$DropDownData,
			);
			$this->response($this->json($temp), 200);
		} else {
			$temp = array('error' => "No Access To User Please Contact Admin");
			$this->response($this->json($temp), 200);
		}
	}


	public function getBLOCKData()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';



		$SQL_INVOICE_DATA = "SELECT * FROM `CS_BLOCK_DATA_TABLE` order by R_POSITION asc";
		$STMT_SQL_INVOICE_DATA = mysql_query($SQL_INVOICE_DATA);

		while ($rlt = mysql_fetch_array($STMT_SQL_INVOICE_DATA, MYSQL_ASSOC)) {


			$result[] = $rlt;
		}

		if (count($result) != 0) {
			$records = array('status' => $STATUS_SUCCESS, "BLOCK_DATA" => $result);
			$this->response($this->json($records), 200);
		} else {
			$records = array('status' => $STATUS_FAILURE, "msg" => "No Records Found");
			$this->response($this->json($records), 200);
		}
	}


	public function getFloorData()
	{

		$json = file_get_contents('php://input');
		$BLOCK_ID = intval($_GET['BLOCK_ID']);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';



		$SQL_INVOICE_DATA = "SELECT * FROM `CS_FLOOR_DATA` where BLOCK_ID='1'";

		$STMT_SQL_INVOICE_DATA = mysql_query($SQL_INVOICE_DATA);

		while ($rlt = mysql_fetch_array($STMT_SQL_INVOICE_DATA, MYSQL_ASSOC)) {


			$result[] = $rlt;
		}

		//echo count($result); 
		if (count($result) != 0) {
			$records = array('status' => $STATUS_SUCCESS, "FloorData" => $result);
			$this->response($this->json($records), 200);
		} else {
			$records = array('status' => $STATUS_FAILURE, "msg" => "No Records Found");
			$this->response($this->json($records), 200);
		}
	}


	public function getRackData()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';
		$FLOOR_ID = $data['FLOOR_ID'];
		$BLOCK_ID = $data['BLOCK_ID'];

		// ORDER BY `POSITION` DESC

		$SQL_CS_RACK_DATA = "SELECT * FROM `CS_RACK_DATA` where BLOCK_ID ='$BLOCK_ID' and FLOOR_ID='$FLOOR_ID' ";
		$STMT_CS_RACK_DATA = mysql_query($SQL_CS_RACK_DATA);

		while ($rlt = mysql_fetch_array($STMT_CS_RACK_DATA, MYSQL_ASSOC)) {
			$RackData[] = $rlt;
		}


		$SQL_CS_PRESENT_STOCK = "SELECT RACK FROM `CS_PRESENT_STOCK` where BLOCK_ID='$BLOCK_ID' and  VARIETY !=''";
		$STMT_CS_PRESENT_STOCK = mysql_query($SQL_CS_PRESENT_STOCK);

		while ($rlt = mysql_fetch_array($STMT_CS_PRESENT_STOCK, MYSQL_ASSOC)) {
			$Stock_Data[] = $rlt['RACK'];
		}

		//echo count($result); 
		if (count($RackData) != 0) {
			$records = array('status' => $STATUS_SUCCESS, "RackData" => $RackData, "STOCK_DATA" => $Stock_Data);
			$this->response($this->json($records), 200);
		} else {
			$records = array('status' => $STATUS_FAILURE, "msg" => "No Records Found");
			$this->response($this->json($records), 200);
		}
	}
	//soaking report - 09-02-2020 11/09/2021 soakingReport(OrgnName) disabled 
	public function soakingReportt()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("Y/m/d  h:i A");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$USER_ID = $data['USER_ID'];

		$LOT_ID = $data['LOT_ID'];
		$DATA = $data['DATA'];
		$DATA = json_decode($DATA, true);

		// print_r($DATA);die(); 

		foreach ($DATA as $value) {

			$time1 = $value['TIME1'];
			$tno = $value['T_NO'];
			$lno = $value['L_NO'];
			$variety = $value['Variety'];
			$qty = $value['QTY'];
			$c1 = $value['C1'];
			$salt_kg = $value['Salt_kg'];
			$chem = $value['CHEM'];
			$sup1_sign = $value['SUP1_SIGN'];
			$time2 = $value['TIME2'];
			$c2 = $value['C2'];
			$APP_QT = $value['APP_QT'];
			$sup2_sign = $value['SUP2_SIGN'];
			$dest = $value['DEST'];
			$remarks = $value['Remarks'];
			$timeperiod = $value['TimePeriod'];
			$source = $value['Source'];
			// $createdon = $value['CreatedDateTime'];
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$dt = $dateTime->format("d/m/Y  h:i A");

			$AryCurrentDate = $this->getCurrentDateTime();
			$PostedDate = $AryCurrentDate['TimeStamp'];

			$sql = "insert into soaking_report(`user_id`,`LOT_ID`,`TIME1`, `T_NO`, `L_NO`, `Variety`, `QTY`, `C1`, `Salt_kg`, `CHEM`, `SUP1_SIGN`, `TIME2`, `C2`, `APP_QT`, `SUP2_SIGN`, `DEST`, `Remarks`, `TimePeriod`, `Source`, `CreatedDateTime`) 
			values('$USER_ID', '$LOT_ID','$time1', '$tno', '$lno', '$variety', $qty, $c1, '$salt_kg', '$chem', '$sup1_sign', '$time2', '$c2', '$APP_QT', '$sup2_sign', '$dest', '$remarks', '$timeperiod', '$source', '$PostedDate')";
			// echo $sql;die(); 
			$sqlca_post = mysql_query($sql, $this->db);
		}
		if ($sqlca_post) {

			$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
			$this->response($this->json($records), 200);
		}
	}



	public function postSoakingReport()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$JSON_DATA = $data;

		$PostedUserID = $data['PostedUserID'];

		$LotID = $data['LotID'];
		$SoakingData = $data['Data'];
		$SoakingData = json_decode($SoakingData, true);

		// print_r($DATA);die(); 

		foreach ($SoakingData as $value) {



			$SoakingDate = date('Y-m-d', strtotime($value['SoakingDate']));
			$SoakInTime = $value['SoakInTime'];
			$Source = $value['Source'];
			$Variety = $value['Variety'];
			$TubNumber = $value['TubNumber'];
			$SoakInCount = $value['SoakInCount'];
			$SoakInQty = $value['SoakInQty'];
			$SaltQty = $value['SaltQty'];
			$ChemicalQty = $value['ChemicalQty'];
			$SoakOutTime = $value['SoakOutTime'];
			$SoakOutCount = $value['SoakOutCount'];
			$SoakOutQty = $value['SoakOutQty'];
			$Destination = $value['Destination'];
			$Remarks = $value['Remarks'];
			// $createdon = $value['CreatedDateTime'];
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$dt = $dateTime->format("d/m/Y  h:i A");

			$AryCurrentDate = $this->getCurrentDateTime();
			$PostedDate = $AryCurrentDate['TimeStamp'];

			$sql = "INSERT INTO `ProdSoakingDetails`( `SoakingDate`, `LotID`, `Source`, `Variety`, `TubNumber`, `SaltQty`, `ChemicalQty`, `SoakInTime`, 
`SoakInCount`, `SoakInQty`,`SoakOutTime`, `SoakOutCount`, `SoakOutQty`, `Destination`, `PostedUserID`, `Remarks`) 
VALUES ('$SoakingDate','$LotID','$Source','$Variety','$TubNumber','$SaltQty','$ChemicalQty','$SoakInTime',
'$SoakInCount','$SoakInQty','$SoakOutTime','$SoakOutCount','$SoakOutQty','$Destination','$PostedUserID','$Remarks')
";
			//echo $sql;die(); 



			$sqlca_post = mysql_query($sql, $this->db);
		}
		if ($sqlca_post) {

			$records = array('Error' => null, "Msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		} else {
			mysql_errno($this->db);
			$records = array('Error' => "Unable To Post Data", "Msg" => "Oops! Something went wrong");
			$this->response($this->json($records), 200);
		}
	}


	//soaking report - 01-03-2020 // IQF INFEED #TEJA
	public function iqfReport()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("d/m/y  h:i A");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$USER_ID = $data['USER_ID'];

		$LOT_ID = $data['LOT_ID'];
		$DATA = $data['DATA'];
		$DATA = json_decode($DATA, true);

		// print_r($DATA);die(); 

		foreach ($DATA as $value) {

			$TIME = $value['TIME'];
			$PRODUCT = $value['PRODUCT'];
			$GRADE = $value['GRADE'];
			$PACKING = $value['PACKING'];
			$BS_RATIO = $value['BS_RATIO'];
			$VEIN = $value['VEIN'];
			$SOFT = $value['SOFT'];
			$BROKEN = $value['BROKEN'];
			$SMELL = $value['SMELL'];
			$COL = $value['COL'];
			$SPOTS = $value['SPOTS'];
			$SUPERVISOR = $value['SUPERVISOR'];
			$sup2_sign = $value['SUP2_SIGN'];
			$dest = $value['DEST'];
			$REMARKS = $value['REMARKS'];
			$LINE = $value['LINE'];
			//$DATE = $value['DATE'];
			// $createdon = $value['CreatedDateTime'];
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$DATE = $dateTime->format("d/m/Y  h:i A");

			$sql = "INSERT INTO `PP_IQF_IN_FEED`( `LOT_ID`, `USER_ID`, `TIME`, `PRODUCT`, `GRADE`,
		`PACKING`, `BS_RATIO`, `VEIN`, `SOFT`, `BROKEN`, `SMELL`, `COL`, `SPOTS`, 
		`SUPERVISOR`, `REMARKS`, `LINE`, `DATE`) VALUES ('$LOT_ID','$USER_ID','$TIME','$PRODUCT','$GRADE',
		'$PACKING','$BS_RATIO','$VEIN','$SOFT','$BROKEN','$SMELL','$COL','$SPOTS',
		'$SUPERVISOR','$REMARKS','$LINE','$DATE')";

			// echo $sql;die(); 
			$sqlca_post = mysql_query($sql, $this->db);
		}
		if ($sqlca_post) {

			$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
			$this->response($this->json($records), 200);
		}
	}




	//soaking report - 06-03-2020 // PACKING REPORT #TEJA
	public function PackingReport()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("d/m/y  h:i A");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$USER_ID = $data['USER_ID'];

		$LOT_ID = $data['LOT_ID'];
		$DATA = $data['DATA'];
		$DATA = json_decode($DATA, true);

		// print_r($DATA);die(); 

		foreach ($DATA as $value) {

			$TIME = $value['TIME'];
			$PRODUCT = $value['PRODUCT'];
			$GRADE = $value['GRADE'];
			$PACKING = $value['PACKING'];
			$BS_RATIO = $value['BS_RATIO'];
			$VEIN = $value['VEIN'];
			$SOFT = $value['SOFT'];
			$BROKEN = $value['BROKEN'];
			$SMELL = $value['SMELL'];
			$COL = $value['COL'];
			$SPOTS = $value['SPOTS'];
			$SUPERVISOR = $value['SUPERVISOR'];
			$sup2_sign = $value['SUP2_SIGN'];
			$dest = $value['DEST'];
			$REMARKS = $value['REMARKS'];
			$LINE = $value['LINE'];
			//$DATE = $value['DATE'];
			// $createdon = $value['CreatedDateTime'];
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$DATE = $dateTime->format("d/m/Y  h:i A");

			$sql = "INSERT INTO `PP_PACKING`( `LOT_ID`, `USER_ID`, `TIME`, `PRODUCT`, `GRADE`,
		`PACKING`, `BS_RATIO`, `VEIN`, `SOFT`, `BROKEN`, `SMELL`, `COL`, `SPOTS`, 
		`SUPERVISOR`, `REMARKS`, `LINE`, `DATE`) VALUES ('$LOT_ID','$USER_ID','$TIME','$PRODUCT','$GRADE',
		'$PACKING','$BS_RATIO','$VEIN','$SOFT','$BROKEN','$SMELL','$COL','$SPOTS',
		'$SUPERVISOR','$REMARKS','$LINE','$DATE')";

			// echo $sql;die(); 
			$sqlca_post = mysql_query($sql, $this->db);
		}
		if ($sqlca_post) {

			$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
			$this->response($this->json($records), 200);
		}
	}



	//Coldstore input - 13-03-2020 // Coldstore REPORT #TEJA
	public function csInputReport()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("d/m/y  h:i A");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$USER_ID = $data['USER_ID'];

		$PURCH_LOT_NUM = $data['LOT_ID'];
		$DATA = $data['DATA'];
		$DATA = json_decode($DATA, true);

		// print_r($DATA);die(); 

		foreach ($DATA as $value) {

			$CARTONS_NUMBER = $value['CARTONS_NUMBER'];
			$DUMMY_MASTER = $value['DUMMY_MASTER'];
			$GRADES = $value['GRADES'];
			$TYPE = $value['TYPE'];
			$TOTAL_SLABS = $value['TOTAL_SLABS'];
			$SLAB_WEIGHT = $value['SLAB_WEIGHT'];
			$FORM = $value['VARIETY'];
			$DATE_TIME = $value['DATE_TIME'];
			$INT_LOT_NUMBER = $value['INT_LOT_NUMBER'];
			$GROUP = $value['GROUP'];
			$CATEGORY = $value['CATEGORY'];
			$JME_MOURYA = $value['JME_MOURYA'];
			$SHIFT = $value['SHIFT'];
			$UOM = $value['UOM'];
			$REMARKS = $value['REMARKS'];
			$LINE = $value['LINE'];
			$BAR_CODE_ID = $value['BARCODE_ID'];
			//$DATE = $value['DATE'];
			// $createdon = $value['CreatedDateTime'];
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$DATE_TIME = $dateTime->format("Y-m-d");
			$ENTRY_TIME = $dateTime->format("Y-m-d  h:i:S");

			if ($INT_LOT_NUMBER == '') {
				$INT_LOT_NUMBER = 'NA';
			}

			if ($BAR_CODE_ID = '') {
				$BAR_CODE_ID = 1;
			} else {
				$BAR_CODE_ID = 1;

				$SQL_CS_BAR_CODE_DATA = "SELECT count(*) as TOTAL FROM `CS_BAR_CODE_DATA` where  BAR_CODE_VALUE='$BAR_CODE_ID'";
				$STMT_CS_BAR_CODE_DATA = mysql_query($SQL_CS_BAR_CODE_DATA);
				$TOTAL = $STMT_CS_BAR_CODE_DATA['TOTAL'];

				if ($TOTAL > 0) {
					$BAR_CODE_ID = $value['BARCODE_ID'];
				} else {
					$BAR_CODE_DATE = date("Y-m-d");
					$BAR_CODE_VALUE = str_replace("-", "", $BAR_CODE_DATE);
					$BAR_CODE_VALUE = $value['BARCODE_ID'];

					$sql_org_insert = "INSERT INTO `CS_BAR_CODE_DATA`(`BAR_CODE_DATE`, `BAR_CODE_VALUE`, `BAR_CODE_STATUS`) 
								VALUES ('$BAR_CODE_DATE','$BAR_CODE_ID','1')";
					$stmt1 = mysql_query($sql_org_insert);
					$BAR_CODE_ID = mysql_insert_id();
				}
			}


			$PACKING_STYLE = $TOTAL_SLABS . ' X ' . $SLAB_WEIGHT . ' ' . $UOM;

			$sql_org_insert = "INSERT INTO `CS_PALLET_INPUT_DATA`(`CARTONS_NUMBER`, `DUMMY_MASTER`, `GRADES`, `TYPE`, `TOTAL_SLABS`, 
	 `SLAB_WEIGHT`, `FORM`, `DATE_TIME`, `INT_LOT_NUMBER`,
	 `PURCH_LOT_NUM`, `GROUP`, `CATEGORY`,`JME_MOURYA`, `SHIFT`, `BAR_CODE_ID`, `UOM`,`PACKING_STYLE`, `REMARKS`)
	 VALUES ('$CARTONS_NUMBER','$DUMMY_MASTER','$GRADES','$TYPE','$TOTAL_SLABS','$SLAB_WEIGHT',
	 '$FORM','$DATE_TIME','$INT_LOT_NUMBER','$PURCH_LOT_NUM','$GROUP','$CATEGORY','$JME_MOURYA','$SHIFT','$BAR_CODE_ID','$UOM',
	 '$PACKING_STYLE',
	 '$REMARKS')";

			//echo $sql_org_insert;
			$sqlca_post = mysql_query($sql_org_insert, $this->db);
		}


		if ($sqlca_post) {

			$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		} else {


			$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong " . $sql_org_insert . mysql_errno($this->db));
			$this->response($this->json($records), 200);
		}
	}



	//HOHL Report
	public function HOHLReport()
	{

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("d/m/y  h:i A");

		$JSON_DATA = $data;

		$STATUS_SUCCESS = 'SUCCESS';
		$STATUS_FAILURE = 'FAILURE';

		$USER_ID = $data['USER_ID'];

		$LOT_ID = $data['LOT_ID'];
		$DATA = $data['DATA'];
		$DATA = json_decode($DATA, true);

		// print_r($DATA);die(); 

		foreach ($DATA as $value) {

			$hl_count = $value['Hl_COUNT'];
			$ho_count = $value['HO_COUNT'];
			$peelingKg = $value['PEELING_KG'];
			$dc = $value['DC'];
			$bs = $value['BS'];
			$hl_perc_ho = $value['HL_PERC_HO'];
			$checked_by = $value['CHECKED_BY'];
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$dt = $dateTime->format("Y-m-d  H:i:s");

			$ss_post = "INSERT INTO `HOHL_report`(`user_id`,`LOT_ID`,`Hl_COUNT`,`HO_COUNT` ,`PEELING_KG`, `DC`, 
		`BS`, `HL_PERC_HO`, `CHECKED_BY`,`CreatedDateTime`)
			VALUES ('$USER_ID', '$LOT_ID', '$hl_count','$ho_count','$peelingKg','$dc','$bs','$hl_perc_ho','$checked_by','$dt')";
			// echo $ss_post;die();
			$sqlca_post = mysql_query($ss_post, $this->db);
		}
		if ($sqlca_post) {

			$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
			$this->response($this->json($records), 200);
		} else {
			echo mysql_errno($this->db);
			$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
			$this->response($this->json($records), 200);
		}
	}

	function getPaymentValues($PoID, $DocType)
	{

		// $LbsFactor = 0.454; // To Convert Lbs into Kgs multiply value
		// $Discount = 0; // Get from Custom Invoice
		// $InsurancePerc = 0.11; // Varible
		// $AntiDumpingDFactor = 101.35; // Anti Dumping Duty Factor(Formula)
		// $AntiDumpingMFactor = 1.35; // Anti Dumping Duty Factor(Formula)
		// $StandardDutyCharges = 125; // Standard Value
		// $TermName = 125; // Standard Value
		// $CenttoUsd = 100; // Standard Value 1 usd=100 cents

		// echo $DocType; exit;

		// $RawIpData = file_get_contents('php://input');
		// $IpData = json_decode($RawIpData, true);
		// $PoID = $IpData['PoID'];

		$Token = $this->getToken();
		$ClientId = $Token['ClientID'];

		$rowInvClientDetails = $this->appUtilController->getClientInvoiceConfig($ClientId);
		$rowClientDetails = $this->appUtilController->getClientConfig($ClientId);
		$DiscountType = $rowClientDetails['DiscountType'];

		$CenttoUsd = 100;
		if ($DocType == 'CMI') {
			$SqlCInvData = "SELECT *,ExchngRte as ExRate,SPD.ExporterID as ExpID FROM ScrmPoData as SPD
				LEFT JOIN ScrmCommercialData as SCD on SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";
		} else {
			$SqlCInvData = "SELECT *,SPD.ExporterID as ExpID, SCDD.Freight, SCI.Freight AS CustomInvoiceFreight FROM ScrmPoData as SPD
				LEFT JOIN ScrmCommercialData as SCDD on SPD.PoID=SCDD.PoID LEFT JOIN ScrmCustomInvoices as SCI on
				SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";
		}

		$StmtCInvData = mysql_query($SqlCInvData, $this->db);
		$RowCInvData = mysql_fetch_array($StmtCInvData, MYSQL_ASSOC);

		$ExporterID = $RowCInvData['ExpID'];

		$CustomInvoiceFreight = $RowCInvData['CustomInvoiceFreight'];
		$CommercialFrieghtCharge = $RowCInvData['Freight'];

		if ($DocType == 'CMI') {
			$ArrayFreight = $this->checkFreight($PoID);
			$FrieghtCharge = $ArrayFreight['FrieghtCharge'];
			$ExchangeRate = $ArrayFreight['ExchangeRate'];
		} else {
			$FrieghtCharge = $RowCInvData['Freight'];
			$ExchangeRate = $RowCInvData['ExRate'];
		}

		$TermName = $RowCInvData['ShippingTerms'];
		$Discount = $RowCInvData['Discount'];

		$QtyUnit = $RowCInvData['QtyUnit'];
		$CInvCode = $RowCInvData['CInvCode'];

		$UnitCurrency = $RowCInvData['UnitCurrency'];
		$DestinationCountryId = $RowCInvData['DestinationCountry'];

		// $SqlGstPerc = "SELECT DISTINCT GstPerc,FDAEntryDate FROM `ScrmPoGradeData` as SPGD 
		// left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
		// left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId
		// left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

		$SqlGstPerc = "SELECT DISTINCT (SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID
			AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1) GstPerc,
			SPM.GstPerc GstPercP,FDAEntryDate,CInvDate,ISF.BLDate FROM `ScrmPoGradeData` as SPGD
			left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
			left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
			left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId
			left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

		// $StmtGstPerc = $iconn->query($SqlGstPerc);
		// $RowGstPerc = $StmtGstPerc->fetch_assoc();

		$StmtGstPerc = mysql_query($SqlGstPerc, $this->db);
		$RowGstPerc = mysql_fetch_array($StmtGstPerc, MYSQL_ASSOC);

		$FDAEntryDate = $RowGstPerc['FDAEntryDate'];
		$IgstPerc = !empty($RowGstPerc['GstPerc']) ? $RowGstPerc['GstPerc'] : $RowGstPerc['GstPercP'];
		$CInvDate = $RowGstPerc['CInvDate'];
		$BLDate = $RowGstPerc['BLDate'];

		$CanadaLbsFactor = $ClientId == 'SMEV3' && $DestinationCountryId == 23 && $CInvDate >= '2026-03-01' ? 2.2046 : 'LbsFactor';

		// if ($CInvCode == '03061720') {
		//     $IgstPerc = 5;
		// } else {
		//     $IgstPerc = 12;
		// }

		$QtyUnit = $RowCInvData['QtyUnit'];

		if ($FDAEntryDate != '') {
			$Query = " and FromDate<='$FDAEntryDate'";
			$DocType = !isset($DocType) ? $DocType : 'CMI';
		} else {
			$Query = " and FromDate<='$CInvDate'";
			$DocType = !isset($DocType) ? $DocType : 'CUI';
		}

		$SqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'";
		$StmtShipTermsMaster = mysql_query($SqlShipTermsMaster, $this->db);
		$RowShipTermsMaster = mysql_fetch_array($StmtShipTermsMaster, MYSQL_ASSOC);

		// Update With ExportId Condition
		// $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates`";
		$SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1";

		$StmtExporterTaxRates = mysql_query($SqlExporterTaxRates, $this->db);
		$RowExporterTaxRates = mysql_fetch_array($StmtExporterTaxRates, MYSQL_ASSOC);
		$AntiDumpingDutyPerc = $RowExporterTaxRates['AntiDumpingDuty'];
		$CountervailingDutyPerc = $RowExporterTaxRates['CountervailingDuty'];
		$MerchandiseProcessFeePerc = $RowExporterTaxRates['MerchandiseProcessFee'];
		$HarbourMaintenanceFeePerc = $RowExporterTaxRates['HarbourMaintenanceFee'];
		$ISFStaticDuty = $RowExporterTaxRates['ISFStaticDuty'];
		$InsurancePerc = $RowExporterTaxRates['InsurancePremiumRate'];
		$ClearanceChargePerc = $RowExporterTaxRates['ClearanceCharge'];
		$ReciprocalTariffsPerc = $RowExporterTaxRates['ReciprocalTariffs'];
		$ClearingAndForwardingCharges = $RowExporterTaxRates['ClearingCharges'];
		$TariffPerc = $RowExporterTaxRates['Tariff'];

		$SqlReciprocalTariffsExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' and FromDate<='$CInvDate' ORDER BY ExpTRID DESC limit 1";
		$StmtReciprocalTariffsExporterTaxRates = mysql_query($SqlReciprocalTariffsExporterTaxRates, $this->db);
		$RowReciprocalTariffsExporterTaxRates = mysql_fetch_array($StmtReciprocalTariffsExporterTaxRates, MYSQL_ASSOC);
		$ReciprocalTariffsPerc = $RowReciprocalTariffsExporterTaxRates['ReciprocalTariffs'];
		$TariffPerc = $RowReciprocalTariffsExporterTaxRates['Tariff'];

		$MbillData = $this->getMbilldata($PoID);

		// Temporary logic added only for the DEV client. This will be removed soon.
		if ($BLDate && $ClientId == 'DFLV3') {
			$blDate = new DateTime($BLDate);
			$startDate = new DateTime('2025-04-05');
			$endDate = new DateTime('2025-04-10');

			if ($blDate <= $endDate) {

				$reciprocalSql = "SELECT ReciprocalTariff,TariffRate  FROM `ReciprocalTariffs` WHERE PoID='$PoID'";
				$reciprocalStmt = mysql_query($reciprocalSql, $this->db);
				$reciprocalRow = mysql_fetch_array($reciprocalStmt, MYSQL_ASSOC);
				if ($reciprocalRow) {
					$ReciprocalTariff = $reciprocalRow['ReciprocalTariff'];
					$TariffRate = $reciprocalRow['TariffRate'];
					if (!empty($ReciprocalTariff) && !empty($TariffRate) && $ReciprocalTariff == 1) {
						$ReciprocalTariffsPerc = $TariffRate;
					}
				}
			}
		}

		// $ReciprocalTariffsPerc = $RowReciprocalTariffsExporterTaxRates['ReciprocalTariffs'];

		// $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
		// $MerchandiseProcessFeePerc +
		// $HarbourMaintenanceFeePerc;

		$SqlRevPoTaxRates = "
			SELECT *
				FROM ScrmPoTaxRates
				WHERE PoID = '$PoID'
				AND InvoiceType = (
					CASE 
						WHEN EXISTS (
							SELECT 1 
							FROM ScrmPoTaxRates 
							WHERE PoID = '$PoID' 
							AND InvoiceType = 'CMI'
						)
						THEN 'CMI'
						ELSE 'CUI'
					END
				)
				ORDER BY ID DESC LIMIT 1	
			";
		$StmtRevPoTaxRates = mysql_query($SqlRevPoTaxRates, $this->db);
		if ($StmtRevPoTaxRates && mysql_num_rows($StmtRevPoTaxRates) > 0) {
			$RowRevPoTaxRates = mysql_fetch_array($StmtRevPoTaxRates, MYSQL_ASSOC);
			$AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
			$AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
			$CountervailingDutyPerc = $RowRevPoTaxRates['CountervailingDuty'];
			$MerchandiseProcessFeePerc = $RowRevPoTaxRates['MerchandiseProcessFee'];
			$HarbourMaintenanceFeePerc = $RowRevPoTaxRates['HarbourMaintenanceFee'];
			$ISFStaticDuty = $RowRevPoTaxRates['ISFStaticDuty'];
			$InsurancePerc = $RowRevPoTaxRates['InsurancePremiumRate'];
			$ReciprocalTariffsPerc = $RowRevPoTaxRates['ReciprocalTariff'];
			$ClearingAndForwardingCharges = $RowRevPoTaxRates['ClearingCharges'];
			$TariffPerc = $RowRevPoTaxRates['Tariff'];
		}

		$TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
			$MerchandiseProcessFeePerc +
			$HarbourMaintenanceFeePerc + $CountervailingDutyPerc +
			$ReciprocalTariffsPerc + $TariffPerc;

		$IsFreight = $RowShipTermsMaster['Freight'];
		$IsDuty = $RowShipTermsMaster['Duty'];
		$Insurance = $RowShipTermsMaster['Insurance'];
		$AntiDumpingDFactor = $RowShipTermsMaster['AntiDumpingDFactor'];
		$AntiDumpingMFactor = $RowShipTermsMaster['AntiDumpingMFactor'];
		$StandardDutyCharges = $RowShipTermsMaster['StandardDutyCharges'];

		$ConfigDetails['CanadaLbsFactor'] = $CanadaLbsFactor;
		$ConfigDetails['DestCountryID'] = $DestinationCountryId;
		$ConfigDetails['ClientId'] = $ClientId;
		$AryInvBreakUpData = $this->getGradeBreakUpData($PoID, $ConfigDetails);

		$IsSauce = $AryInvBreakUpData['HasSauce'];

		// if ($RowCInvData['CInvDate'] >= '2025-10-14') {
		// $IsSauce = $this->checkSauceExists($PoID);
			$SauceTotalUsdValue = 0;
			$SauceTotalKgs = 0;
			$SauceTotalLbs = 0;
			if ($IsSauce == 'Yes') {
				$SqlSauce = "SELECT PGD.Mcs, PGD.VarietyID, PGD.CUPS, PGD.CupsPrice, PS.*, SPD.QtyUnit
					FROM ScrmPoGradeData as PGD
					LEFT JOIN ScrmPoData as SPD ON SPD.PoID = PGD.InvId
					LEFT JOIN ScrmPIVarietyList as SPVL ON SPVL.VarietyID = PGD.VarietyID
					LEFT JOIN AdminPackingStyles as PS ON SPVL.SaucePackingStyle = PS.PCKID
					WHERE InvId='$PoID' AND SPVL.HasSauce = 'Yes'";
				$StmtSauce = mysql_query($SqlSauce, $this->db);
				while ($RowSauce = mysql_fetch_array($StmtSauce, MYSQL_ASSOC)) {
					$SauceLbsWeight = $RowSauce['TotalSlabs'] * $RowSauce['SlabWeight'] * $RowSauce['Mcs'];
					if ($RowSauce['Uom'] == 'OZ') {
						$OzToLbs = $RowSauce['SlabWeight'] * $RowSauce['LbsFactor'];
						$SauceLbsWeight *= $OzToLbs / $RowSauce['SlabWeight'];
					}
					$SauceKgsWeight = $SauceLbsWeight * 0.454;
					if ($RowSauce['QtyUnit'] == 'LBS' && $PType == 'KGS') {
						$QtyCalc = $SauceKgsWeight;
						$RowSauce['CupsPrice'] = $RowSauce['CupsPrice'] / 0.454;
					} else {
						$QtyCalc = $SauceLbsWeight;
					}
					$SauceTotalUsdValue += $QtyCalc * $RowSauce['CupsPrice'];
					$SauceTotalKgs += $SauceKgsWeight;
					$SauceTotalLbs += $SauceLbsWeight;
				}
			}
		// }


		$TotalMcs = $AryInvBreakUpData['MCS'];

		// if ($QtyUnit == 'KGS') {
		// 	$Weight = $AryInvBreakUpData['NetWgtKgs'] + $SauceKgsWeight;
		// 	$InvoiceValue = $AryInvBreakUpData['PriceKgs'];
		// } else {
		// 	$Weight = $AryInvBreakUpData['NetWgtLbs'];
		// 	$InvoiceValue = $AryInvBreakUpData['PriceLbs'];
		// }
		$InvoiceValue = $AryInvBreakUpData['UsdValue'] + $SauceTotalUsdValue;

		$AryPrices['ShrimpValueUsd'] = $InvoiceValue;
		$AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;

		// if ($RowCInvData['CInvDate'] >= '2025-10-14') {
		// 	$InvoiceValue += $SauceTotalUsdValue;
		// }

		if ($Discount > 0) {
			if ($DiscountType == 0) {
				$DiscountAmount = ($InvoiceValue * $Discount) / 100;
				$InvoiceValueAfterDiscount = $InvoiceValue;
				$InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; // By Rantagiri 04/17/2023 5.44 
			} else {
				$DiscountAmount = $Discount;
				$InvoiceValueAfterDiscount = $InvoiceValue;
				$InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount;
			}
		} else {
			$InvoiceValueAfterDiscount = $InvoiceValue;
		}

		if ($Insurance == 1) {
			// $InsuranceValue = ($PriceAfterDiscount * $InsurancePerc) / 100;
			$InsuranceValue = ($InvoiceValueAfterDiscount * $InsurancePerc) / 100;
		} else {
			$InsuranceValue = 0;
		}

		$FrieghtCharge = empty($CommercialFrieghtCharge) ? $CustomInvoiceFreight : $CommercialFrieghtCharge;

		if ($IsDuty == 1) {
			$ClearanceChargeFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) * $ClearanceChargePerc) / 100; //#jme uma ClearanceCharge calculation included //01-04-2024
			$MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
			// $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
			if ($MerchandiseProcessFeePerc == 0) {
				$ISFStatic = false;
				$MerchandiseProcessFee = $ISFStaticDuty;
				$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
				// $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
			} else if ($ISFStaticDuty != 0 && $MerchandiseProcessFee > $ISFStaticDuty) {
				$ISFStatic = true;
				$MerchandiseProcessFee = $ISFStaticDuty;
				$TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
				$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges - $MerchandiseProcessFee;
			} else {
				$ISFStatic = false;
				$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
				$MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
				// $MerchandiseProcessFee = $ISFStaticDuty;
			}

			$EffInvValue = $EffInvValue - $ClearanceChargeFee;

			$AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
			$HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);
			$CountervailingDutyFee = round((($EffInvValue * $CountervailingDutyPerc) / $TotalDutyPerc), 2);
			$ReciprocalTariffsFee = 0;
			$TariffFee = 0;
			if ($ReciprocalTariffsPerc > 0) {
				$ReciprocalTariffsFee = round((($EffInvValue * $ReciprocalTariffsPerc) / $TotalDutyPerc), 2);
			}

			if ($TariffPerc > 0) {
				$TariffFee = round((($EffInvValue * $TariffPerc) / $TotalDutyPerc), 2);
			}

			if ($ISFStatic) {
				$DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee + $TariffFee;
			} else {
				$DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee + $TariffFee;
			}
		} else {
			$DutiesPrice = $DutiesPrice + $StandardDutyCharges;
		}
		$InvValueUSD = $InvoiceValueAfterDiscount;

		if ($ISFStatic) {
			$InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty - $InsuranceValue; //#jme uma ClearanceCharge calculation included //01-04-2024
			$InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
			$FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty - $InsuranceValue - $ClearanceChargeFee; //#jme uma ClearanceCharge calculation included //01-04-2024
		} else {

			// $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge;
			// $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
			// $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice;

			// $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge-$InsuranceValue;
			$InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $InsuranceValue;
			$InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
			$FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $InsuranceValue - $ClearanceChargeFee;
		}

		$HasCFLCharges = $rowInvClientDetails['CLFCharges'];

		if ($HasCFLCharges && $RowCInvData['DestinationCountry'] == '157') {
			$InvValueUSDWithOutDuties = $InvValueUSDWithOutDuties - $ClearingAndForwardingCharges;
			$FOBValueUSD -= $ClearingAndForwardingCharges;
			$AryPrices['ClearingAndForwardingCharges'] = $ClearingAndForwardingCharges;
		}

		$InvValueINR = $InvValueUSD * $ExchangeRate;
		$FOBValueINR = $FOBValueUSD * $ExchangeRate;
		$IgstValue = ($InvValueINR * $IgstPerc) / 100;

		$AryPrices['DiscountPerc'] = $Discount;
		$AryPrices['ExchangeRate'] = $ExchangeRate;
		$AryPrices['UnitCurrency'] = $UnitCurrency;

		//fetch Currency Symbol
		$SqlCurrency = "SELECT CurrenySymbol, CurrencyCode FROM `ScrmCurrencyMaster` WHERE CurrencyCode='$UnitCurrency'";
		$StmtCurrency = mysql_query($SqlCurrency, $this->db);
		$RowCurrency = mysql_fetch_array($StmtCurrency, MYSQL_ASSOC);
		$AryPrices['CurrencySymbol'] = $RowCurrency['CurrenySymbol'];

		$AryPrices['DiscountAmount'] = $DiscountAmount;
		$AryPrices['InsuranceValue'] = $InsuranceValue;
		$AryPrices['DutiesPrice'] = $DutiesPrice;
		$AryPrices['FrieghtCharge'] = $CommercialFrieghtCharge;
		$AryPrices['CustomInvoiceFrieghtCharge'] = $CustomInvoiceFreight;

		$AryPrices['InvValueUSD'] = $InvValueUSD;
		$AryPrices['InvValueUSDWithOutDuties'] = $InvValueUSDWithOutDuties;
		$AryPrices['InvValueINR'] = $InvValueINR;
		$AryPrices['FOBValueUSD'] = $FOBValueUSD;
		$AryPrices['FOBValueINR'] = $FOBValueINR;
		$AryPrices['PaymentTerm'] = $TermName;
		$AryPrices['IgstPerc'] = $IgstPerc;
		$AryPrices['IgstValue'] = $IgstValue;
		$AryPrices['AntiDumpingDuty'] = $AntiDumpingCharges;
		$AryPrices['AntiDumpingDutyPerc'] = $AntiDumpingDutyPerc;
		$AryPrices['MerchantDuty'] = $MerchandiseProcessFee;
		$AryPrices['HarborDuty'] = $HarbourMaintenanceFee;
		$AryPrices['ISFStatic'] = $ISFStatic;
		$AryPrices['CenttoUsd'] = $CenttoUsd;
		$AryPrices['TotalMcs'] = $TotalMcs;
		$AryPrices['CountervailingDuty'] = $CountervailingDutyFee;
		$AryPrices['CountervailingDutyPerc'] = $CountervailingDutyPerc;
		$AryPrices['ReciprocalTariffsFee'] = $ReciprocalTariffsFee;
		$AryPrices['ReciprocalTariffsPerc'] = $ReciprocalTariffsPerc;
		$AryPrices['TariffFee'] = $TariffFee;
		$AryPrices['TariffPerc'] = $TariffPerc;
		$AryPrices['NetWgtKgs'] = $AryInvBreakUpData['NetWgtKgs'] + $SauceTotalKgs;
		$AryPrices['NetWgtLbs'] = $AryInvBreakUpData['NetWgtLbs'] + $SauceTotalLbs;
		$AryPrices['CanadaLbsFactor'] = $CanadaLbsFactor;
		$AryPrices['HasSauce'] = $AryInvBreakUpData['HasSauce'];
		$AryPrices['InsurancePerc'] = $InsurancePerc;
		// $AryPrices['Token'] = json_encode($Token);
		// $AryPrices['Query'] = $AryInvBreakUpData['Query'];
		// $AryPrices['GradeData'] = json_encode($AryInvBreakUpData);
		return $AryPrices;
	}


	function getGradeBreakUpData($PoID, $ConfigDetails)
	{
		$DestCountryID = $ConfigDetails['DestCountryID'];
		$CanadaLbsFactor = $ConfigDetails['CanadaLbsFactor'];
		$ClientId = $ConfigDetails['ClientId'];

		$Qry = "WHERE InvId='$PoID'";
		$PriceQry = 'sum(Price) as Price';

		$sqlExt = "sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
			sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,";

		if ($ClientId == 'SMEV3' && $DestCountryID == '23') {
			$sqlExt .= "CASE WHEN PS.Uom = 'GRAMS' THEN
			sum(Mcs * TotalSlabs * SlabWeight * Mfactor * $CanadaLbsFactor)
			ELSE sum(Mcs * TotalSlabs * SlabWeight * LbsFactor)
			END AS NetWgtLbs,
			CASE WHEN PS.Uom = 'OZ' THEN
			sum(Mcs * TotalSlabs * SlabWeight / (1000 * $CanadaLbsFactor))
			WHEN PS.Uom = 'LBS' THEN
			sum(Mcs * TotalSlabs * SlabWeight * LbsFactor / $CanadaLbsFactor)
			ELSE sum(Mcs * TotalSlabs * SlabWeight * MFactor)
			END AS NetWgtKgs";

			$PriceExt = "sum(CASE WHEN PS.Uom = 'GRAMS' THEN
				Mcs * TotalSlabs * SlabWeight * Mfactor * $CanadaLbsFactor * Price
				ELSE Mcs * TotalSlabs * SlabWeight * LbsFactor * Price END) AS PriceLbs,
				sum(CASE WHEN PS.Uom = 'OZ' THEN
				Mcs * TotalSlabs * SlabWeight / (1000 * $CanadaLbsFactor) * Price
				WHEN PS.Uom = 'LBS' THEN
				(Mcs * TotalSlabs * SlabWeight * LbsFactor / $CanadaLbsFactor) * price
				ELSE Mcs * TotalSlabs * SlabWeight * MFactor * Price END) AS PriceKgs";
		} else {
			$sqlExt .= "sum(CASE WHEN PS.Uom = 'OZ' THEN
			Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454
			ELSE Mcs * TotalSlabs * SlabWeight * MFactor END) as NetWgtKgs,
			sum(Mcs * TotalSlabs * SlabWeight * LbsFactor) as NetWgtLbs";

			$PriceExt = "sum(CASE WHEN PS.Uom = 'OZ' AND SPVL.HasSauce = 'Yes' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price)
				WHEN PS.Uom = 'LBS' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price)
				WHEN PS.Uom = 'OZ' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price)
				ELSE (Mcs * TotalSlabs * SlabWeight * Mfactor * Price) END) as PriceKgs,
				sum(CASE WHEN PS.Uom = 'OZ' AND SPVL.HasSauce = 'Yes' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price)
				WHEN PS.Uom = 'KGS' THEN (Mcs * TotalSlabs * SlabWeight * MFactor * Price)
				WHEN PS.Uom = 'LBS' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price)
				-- WHEN PS.Uom = 'GRAMS' THEN (Mcs * TotalSlabs * SlabWeight * MFactor * 0.454 * Price)
				ELSE (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price) END) as PriceLbs";
		}

		$SqlTotal = "SELECT SPVL.HasSauce, sum(Mcs) as MCS,TotalSlabs,InvId, $sqlExt, $PriceQry, $PriceExt,
			CASE WHEN SPVL.HasSauce = 'Yes' THEN sum(Mcs * TotalSlabs * Price)
			ELSE 0 END as PriceSauce,
			sum(TraysQuantity) as TraysQuantity
			FROM `ScrmPoGradeData`
			as PGD left join ScrmPIVarietyList as SPVL
			on SPVL.VarietyID=PGD.VarietyID
			left join AdminPackingStyles as PS
			on PS.PCKID = (CASE WHEN SPVL.HasSauce = 'Yes' THEN SPVL.ShrimpPackingStyle
			ELSE PGD.PackingStyleID END) $Qry";
		// echo $SqlTotal;exit;
		$StmtTotal = mysql_query($SqlTotal, $this->db);
		$RowTotal = mysql_fetch_array($StmtTotal, MYSQL_ASSOC);
		$InvId = $RowTotal['InvId'];
		$WtPerCtnTxt = '';
		if ($PoID != '') {
			$SqlWtPerCtn = "SELECT
				round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,
				round(GrossWt/Sum(Mcs),2) as GrossWt FROM $gradeTable as SPGD
				left JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID
				WHERE InvId='$InvId' GROUP by PackingStyleID";
			$StmtWtPerCtn = mysql_query($SqlWtPerCtn, $this->db);
			$RowWtPerCtn = mysql_fetch_array($StmtWtPerCtn, MYSQL_ASSOC);
			$NetWtPerCtn = $RowWtPerCtn['NetWtLbs'];
			$GrossWtPerCtn = $RowWtPerCtn['GrossWt'];
			$WtPerCtnTxt = 'EACH M/CTNS. CONSISTS OF NET WT. ' . $NetWtPerCtn . ' LBS AND GROSS WT. ' . $GrossWtPerCtn . ' LBS.';
		}

		$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'";
		$StmtQtyUnit = mysql_query($SqlQtyUnit, $this->db);
		$RowQtyUnit = mysql_fetch_array($StmtQtyUnit, MYSQL_ASSOC);
		$QtyUnit = $RowQtyUnit['QtyUnit'];

		switch ($QtyUnit) {
			case 'BAGS':
			case 'TRAYS':
			case 'RINGS':
			case 'UNITS':
				$Qty = $RowTotal['MCS'] * $RowTotal['TotalSlabs'];
				$UsdValue = $RowTotal['PriceSauce'];
				break;
			case 'KGS':
				$Qty = $RowTotal['NetWgtKgs'];
				$UsdValue = $RowTotal['PriceKgs'];
				break;
			default: // LBS or any other value
				$Qty = $RowTotal['NetWgtLbs'];
				$UsdValue = $RowTotal['PriceLbs'];
				break;
		}

		$AryPriceData['Price'] = $RowTotal['Price'];
		$AryPriceData['TotalMcs'] = $RowTotal['MCS'];
		$AryPriceData['Qty'] = $Qty;
		$AryPriceData['UsdValue'] = $UsdValue;

		// if ($DestCountryID == '23') {
		// 	$AryPriceData['UsdValue'] = $Qty * $RowTotal['Price'];
		// }

		$AryPriceData['NetWgtKgs'] = round($RowTotal['NetWgtKgs'], 2);
		$AryPriceData['NetWgtLbs'] = round($RowTotal['NetWgtLbs'], 2);
		$AryPriceData['GrossWeightKgs'] = $RowTotal['GrossWeightKgs'];
		$AryPriceData['GrossWeightLbs'] = $RowTotal['GrossWeightLbs'];
		$AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;
		$AryPriceData['HasSauce'] = $RowTotal['HasSauce'];
		$AryPriceData['Query'] = $SqlTotal;

		return $AryPriceData;
	}


	function getBillRealizationDetails()
	{

		//$Error=true; 
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$PoID = $IpData['PoID'];


		$FeildsList = "
	SRD.RezDueDate,SRD.RezRef1,SRD.RezValue1,SRD.RezDate1,
	SRD.RezRef2,SRD.RezValue2,SRD.RezDate2,
	SRD.RezRef3,SRD.RezValue3,SRD.RezDate3,
	SRD.FBKCharges, SRD.OtherCharges, SRD.RezStatus,
SCI.CustomsInvNo,SCI.CInvDate,SPD.ConsigneeID,SCD.ConsigneeName,SCI.PaymentType";

		$SqlBillDtls = "SELECT $FeildsList FROM `ScrmCustomInvoices` SCI
LEFT JOIN ScrmPoData SPD ON SPD.PoID=SCI.PiID
LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmRealizationDetails SRD ON SRD.PoID=SPD.PoID 
where SPD.PoID='$PoID'";

		$StmtBillDtls = mysql_query($SqlBillDtls, $this->db);
		$RowBillDtls = mysql_fetch_array($StmtBillDtls, MYSQL_ASSOC);

		$AryBillDetails = array();

		$AryBillDetails['DueDate'] = $RowBillDtls['RezDueDate'];
		$AryBillDetails['BankRefNo1'] = $RowBillDtls['RezRef1'];
		$AryBillDetails['RealisedValue1'] = str_replace(',', '', $RowBillDtls['RezValue1']);
		$AryBillDetails['RealisedDate1'] = $RowBillDtls['RezDate1'];
		$AryBillDetails['BankRefNo2'] = $RowBillDtls['RezRef2'];
		$AryBillDetails['RealisedValue2'] = str_replace(',', '', $RowBillDtls['RezValue2']);
		$AryBillDetails['RealisedDate2'] = $RowBillDtls['RezDate2'];
		$AryBillDetails['BankRefNo3'] = $RowBillDtls['RezRef3'];
		$AryBillDetails['RealisedValue3'] = str_replace(',', '', $RowBillDtls['RezValue3']);
		$AryBillDetails['RealisedDate3'] = $RowBillDtls['RezDate3'];

		$AryBillDetails['FBKCharges'] = str_replace(',', '', $RowBillDtls['FBKCharges']);
		$AryBillDetails['OtherCharges'] = str_replace(',', '', $RowBillDtls['OtherCharges']);
		$AryBillDetails['RezStatus'] = $RowBillDtls['RezStatus'];

		$AryBillDetails['CustomsInvNo'] = $RowBillDtls['CustomsInvNo'];
		$AryBillDetails['CInvDate'] = $RowBillDtls['CInvDate'];
		$AryBillDetails['ConsigneeName'] = $RowBillDtls['ConsigneeName'];
		$AryBillDetails['PaymentType'] = $RowBillDtls['PaymentType'];


		$SqlColctByNego = "SELECT SPC.DueDte,SCBP.ColType,SCBP.BankRefNo,SCBP.UtilizedValue,UtilizedDate,SpotRate,BnkChrges
 FROM `ScrmPaymentCollections` SPC
LEFT JOIN ScrmCollectionBreakUP SCBP ON SCBP.PiID=SPC.PIID
WHERE SPC.PIID='$PoID' ORDER BY SCBP.ColBrkID ;";

		$SqlUtillizedvalue = "SELECT SUM(UtilizedValue) AS TotalValue FROM 
`ScrmCollectionBreakUP` WHERE PiID='$PoID'";



		$StmtUtillizedvalue = mysql_query($SqlUtillizedvalue, $this->db);
		$RowUtillizedvalue = mysql_fetch_array($StmtUtillizedvalue, MYSQL_ASSOC);

		$StmtColctByNego = mysql_query($SqlColctByNego, $this->db);
		while ($RowColctByNego = mysql_fetch_array($StmtColctByNego, MYSQL_ASSOC)) {

			$AryBillDetails['DueDate'] = $RowColctByNego['DueDte'];
			$BankRefNo = $RowColctByNego['BankRefNo'];
			$FcNumber = $RowColctByNego['FcNumber'];
			$UtilizedValue = $RowColctByNego['UtilizedValue'];
			$UtilizedDate = $RowColctByNego['UtilizedDate'];
			$ColType = $RowColctByNego['ColType'];
			$SpotRate = $RowColctByNego['SpotRate'];
			$BankCharges = $RowColctByNego['BnkChrges'];
			$UtilizedValueINR = $UtilizedValue * $SpotRate;

			$AryBillBreakupDetails['UtilizedValue'] = $UtilizedValue;
			$AryBillBreakupDetails['UtilizedDate'] = $UtilizedDate;
			$AryBillBreakupDetails['ColType'] = $ColType;
			$AryBillBreakupDetails['SpotRate'] = $SpotRate;
			$AryBillBreakupDetails['UtilizedValueINR'] = $UtilizedValueINR;
			$AryBillBreakupDetails['BankRefNo'] = $BankRefNo;
			$GrandUtilizedValue += $UtilizedValue;
			$GrandValueINR += $UtilizedValueINR;

			$AryBreakUp[] = $AryBillBreakupDetails;
		}
		$RealizedValue = $RowUtillizedvalue['TotalValue'];
		$AryPriceDATA = $this->getPaymentValues($PoID, '');
		$InvValueUSD = $AryPriceDATA['InvValueUSD'];
		// $ShortFallValue = (round($InvValueUSD, 2)) - $RealizedValue;
		$ShortFallValue = (round($InvValueUSD, 2)) - $RealizedValue - $BankCharges;


		$AryBillDetails['GrandValueINR'] = $GrandValueINR;
		$AryBillDetails['GrandUtilizedValue'] = number_format($GrandUtilizedValue, 2);
		$AryBillDetails['RealizedValue'] = $RealizedValue;
		$AryBillDetails['InvValueUSD'] = $InvValueUSD;
		$AryBillDetails['ShortFallValue'] = $ShortFallValue;
		$AryBillDetails['BankCharges'] = $BankCharges;



		$data['BillRealization'] = $AryBillDetails;
		$data['BillBreakUp'] = $AryBreakUp;

		if ($StmtColctByNego) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}



	function saveBillRealizationDetails()
	{
		//$Error=true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$PoID = $IpData['PIID'];
		$RezDueDate = $IpData['DueDate'];
		$RezRef1 = $IpData['BankRefNo1'];
		$RezValue1 = $IpData['RealisedValue1'];
		$RezDate1 = $IpData['RealisedDate1'];
		$RezRef2 = $IpData['BankRefNo2'];
		$RezValue2 = $IpData['RealisedValue2'];
		$RezDate2 = $IpData['RealisedDate2'];
		$RezRef3 = $IpData['BankRefNo3'];
		$RezValue3 = $IpData['RealisedValue3'];
		$RezDate3 = $IpData['RealisedDate3'];

		$FBKCharges = $IpData['FBKCharges'];
		$OtherCharges = $IpData['OtherCharges'];
		$RezStatus = $IpData['RezStatus'];

		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = $datetime->format('Y-m-d H:i:s');

		// $SqlCheckRezDetails = "SELECT PoID,(RezValue1+RezValue2+RezValue3) as TotalRez,
		//  FBKCharges,OtherCharges FROM `ScrmRealizationDetails` WHERE PoID='$PoID'";

		$SqlCheckRezDetails = "SELECT PoID,(RezValue1+RezValue2+RezValue3) as TotalRez,BnkChrges, FBKCharges,OtherCharges FROM `ScrmPaymentCollections` as SPC 
	 left join ScrmRealizationDetails as SRD on SRD.PoID=SPC.PIID WHERE PoID='$PoID'"; //uma
		$StmtCheckRezDetails = mysql_query($SqlCheckRezDetails, $this->db);
		$RowRealization = mysql_fetch_array($StmtCheckRezDetails, MYSQL_ASSOC);

		// $TotalRealised = $RezValue1 + $RezValue2 + $RezValue3 + $FBKCharges + $OtherCharges;
		$TotalRealised = $RezValue1 + $RezValue2 + $RezValue3 + $FBKCharges + $OtherCharges + $RowRealization['BnkChrges']; //uma

		$AryPriceDATA = $this->getPaymentValues($PoID, '');
		// $InvValueUSD = $AryPriceDATA['InvValueUSD'];
		$InvValueUSD = round($AryPriceDATA['InvValueUSD'], 2);
		$TotalRealised = round($TotalRealised, 2);
		// $ShortFallValue = $InvValueUSD - $RealizedValue;

		// echo "real -- " . $TotalRealised . " -- inv -- " . $InvValueUSD;
		// exit;
		$RezStatus = 0;
		$Error = false;
		if ($TotalRealised == $InvValueUSD) {
			$RezStatus = 1;
		}

		if ($TotalRealised > $InvValueUSD) {
			$Error = true;
			$Msg = "Realization Value Exceed Shortfall Value";
		}

		if (mysql_num_rows($StmtCheckRezDetails) > 0) {
			$IsUpdate = true;
		} else {
			$IsUpdate = false;
		}

		if (!$IsUpdate && !$Error) {

			$SqlRelzDetails = "INSERT INTO `ScrmRealizationDetails`
	(`PoID`, `RezDueDate`,
	`RezRef1`,`RezValue1`,`RezDate1`,
	`RezRef2`,`RezValue2`,`RezDate2`,
	`RezRef3`,`RezValue3`,`RezDate3`,
	`FBKCharges`, `OtherCharges`, `RezStatus`)
	VALUES(
	'$PoID','$RezDueDate',
	'$RezRef1','$RezValue1','$RezDate1',
	'$RezRef2','$RezValue2','$RezDate2',
	'$RezRef3','$RezValue3','$RezDate3',
	'$FBKCharges', '$OtherCharges', '$RezStatus') ";
			$StmtRelzDetails = mysql_query($SqlRelzDetails, $this->db);
		} else if (!$Error) {
			$SqlRelzDetails = "update `ScrmRealizationDetails` 
	set  
	`RezDueDate`='$RezDueDate',`RezRef1`='$RezRef1',`RezValue1`='$RezValue1',`RezDate1`='$RezDate1',
	`RezRef2`='$RezRef2',`RezValue2`='$RezValue2',`RezDate2`='$RezDate2',
	`RezRef3`='$RezRef3',`RezValue3`='$RezValue3',`RezDate3`='$RezDate3',
	`FBKCharges`='$FBKCharges', `OtherCharges`='$OtherCharges',
	`RezStatus`='$RezStatus' where PoID='$PoID'
	";
			$StmtRelzDetails = mysql_query($SqlRelzDetails, $this->db);
		}


		if ($StmtRelzDetails) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = $Msg;
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}


	function getRealizationList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$BillsType = $IpData['BillsType'];
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];

		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';

		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}
		if ($BillsType == "0") {


			// $IQuery = 'and POD.PoID in 
			// (SELECT PIID FROM ScrmPaymentCollections as SPC where SPC.PIID=POD.PoID and SPC.Status=0)';

			$IQuery = " and POD.PoID  in (SELECT PoID FROM `ScrmRealizationDetails` where RezStatus=0)";
		} else if ($BillsType == "1") {

			$IQuery = " and POD.PoID  in (SELECT PoID FROM `ScrmRealizationDetails` where RezStatus=1)";
			// $IQuery = " and POD.PoID  in (SELECT PIID FROM ScrmPaymentCollections as SPC where SPC.PIID=POD.PoID and SPC.Status=1)";
		}

		if ($Year != '') {
			$DateFilter = $this->getFinancialYear($Year);
			$Error = false;
		}


		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.PoNO,
		SCD.ConsigneeName,
		SCP.Type,
		SCI.CustomsInvNo as CInvNo,
		(SELECT BankRefNo FROM `ScrmCollectionBreakUP` as SCB WHERE POD.PoID=SCB.PiID order by ColBrkID desc LIMIT 1) as BankRefNo,
		SCI.CInvDate';

			$SqlRealization = "SELECT $SqlFields FROM ScrmCustomInvoices as SCI 
			left join ScrmPoData as POD on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
			left join ScrmPaymentCollections as SCP on POD.PoID=SCP.PIID
            left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID 			
			WHERE SCI.CInvDate $DateFilter $IQuery and POD.Status not in (3,7,4)   
            and TransportedDate!=''			
			$Query $SQuery  $SubQuery order by CommercialID desc";

			$StmtRealization = mysql_query($SqlRealization, $this->db);

			while ($RowRealization = mysql_fetch_array($StmtRealization, MYSQL_ASSOC)) {

				$InvDate = $this->getFormatedDate($RowRealization['CInvDate']);
				$Type = $RowRealization['Type'];
				if ($Type == 1) {
					$TransactionType = 'Collection';
				} else {
					$TransactionType = 'Negotiation';
				}
				$RowRealization['Sno'] = ++$Sno;
				$RowRealization['TransactionType'] = $TransactionType;
				$RowRealization['CInvDate'] = $InvDate;

				$AryRowRealization[] = $RowRealization;
			}


			$data['Link'] = "RealizationForm?PIID=";
			$data['RealizationList'] = $AryRowRealization;

			if ($StmtRealization) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function postFDADetails()
	{

		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$PoID = $IpData['PoID'];
		$ContainerGateOutDate = $IpData['ContainerGateOutDate'];
		$FDAEUHoldDate = $IpData['FDAEUHoldDate'];
		$FDAEUReleaseCFIADate = $IpData['FDAEUReleaseCFIADate'];
		$FDAEntryNumber = $IpData['FDAEntryNumber'];
		$FDAEntryDate = $IpData['FDAEntryDate'];
		$AgentInvoice = $IpData['AgentInvoice'];
		$EmptyContainerEntryNumber = $IpData['EmptyContainerEntryNumber'];
		$EmptyContainerReturnDate = $IpData['EmptyContainerReturnDate'];
		$ActualPaidUSD = $IpData['ActualPaidUSD'];
		$PostedUserId = $IpData['PostedUserId'];
		$FDAExam = $IpData['FDAExam'];

		$IsFDAExamSelected = (strtoupper($FDAExam) === 'YES') ? 1 : 0;
		$FDAContainerStatus = $IpData['FDAContainerStatus'];
		$ShippingNotes = $IpData['ShippingNotes'];
		$Remarks = $IpData['Remarks'];
		$NoticeDate1 = $IpData['NoticeDate1'];
		$NoticeDate2 = $IpData['NoticeDate2'];
		$NoticeDate3 = $IpData['NoticeDate3'];
		$NoticeDate4 = $IpData['NoticeDate4'];
		$SampleReceivedDate = $IpData['SampleReceivedDate'];
		$SampleCollectionDate = $IpData['SampleCollectionDate'];

		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = $datetime->format('Y-m-d H:i:s');
		// $PostedDateTime=date('Y-m-d H:i:s');

		$SqlCount = "SELECT * FROM `ScrmISFDetails` WHERE PIID='$PoID'";
		$StmtCount = mysql_query($SqlCount, $this->db);

		$RowCount = mysql_fetch_array($StmtCount, MYSQL_ASSOC);
		$IsEdit = $RowCount['IsEdit'] + 1;
		$Count = mysql_num_rows($StmtCount);

		$ExistingIsFDAExamSelected = 0;

		if ($Count > 0) {
			$ExistingIsFDAExamSelected = (int) $RowCount['IsFDAExamSelected'];
		}
		if (strtoupper($FDAExam) === 'YES') {
			$IsFDAExamSelected = 1;
		} else {
			// Keep previous value (do not reset)
			$IsFDAExamSelected = $ExistingIsFDAExamSelected;
		}

		// echo $IsFDAExamSelected;
		// exit;

		if ($Count == 0) {
			$SqlInsert = "INSERT INTO `ScrmISFDetails`( `PIID`, `FDAExam`, `GetOutFull`,  `FDAEUHoldDate`, `FDAEUReleaseCFIADate`, `FDAEntryNumber`,
			 `FDAEntryDate`, `EmptyContainerEntryNumber`, `EmptyContainerReturnDate`, `FDAContainerStatus`, `ShippingNotes`, `FDARemarks`, 
			 `NoticeDate1`, `NoticeDate2`, `NoticeDate3`, `NoticeDate4`, `SampleCollectionDate`, `SampleReceivedDate`, `IsEdit`,
		 `PostedUserID`, `PostedDateTime`,`IsFDAExamSelected`)

		 values('$PoID','$FDAExam','$ContainerGateOutDate','$FDAEUHoldDate','$FDAEUReleaseCFIADate','$FDAEntryNumber',
		 '$FDAEntryDate','$EmptyContainerEntryNumber','$EmptyContainerReturnDate','$FDAContainerStatus','$ShippingNotes',
		 '$Remarks','$NoticeDate1','$NoticeDate2','$NoticeDate3','$NoticeDate4','$SampleCollectionDate','$SampleReceivedDate','$IsEdit'
		 ,'$PostedUserId','$PostedDateTime', '$IsFDAExamSelected')";
		} else {

			$SqlInsert = " UPDATE `ScrmISFDetails` SET `FDAExam`='$FDAExam',`GetOutFull`='$ContainerGateOutDate',`FDAEUHoldDate`='$FDAEUHoldDate',
	 `FDAEUReleaseCFIADate`='$FDAEUReleaseCFIADate',`FDAEntryNumber`='$FDAEntryNumber',
		`FDAEntryDate`='$FDAEntryDate',`EmptyContainerEntryNumber`='$EmptyContainerEntryNumber',
		`EmptyReturn`='$EmptyContainerReturnDate',`PostedUserID`='$PostedUserId',`PostedDateTime`='$PostedDateTime',
		`IsEdit`='$IsEdit',`FDAContainerStatus`='$FDAContainerStatus',`ShippingNotes`='$ShippingNotes',
		`FDARemarks`='$Remarks',`NoticeDate1`='$NoticeDate1',`NoticeDate2`='$NoticeDate2',`NoticeDate3`='$NoticeDate3'
		,`NoticeDate4`='$NoticeDate4',`SampleCollectionDate`='$SampleCollectionDate',`SampleReceivedDate`='$SampleReceivedDate',`IsFDAExamSelected`='$IsFDAExamSelected' 
		WHERE `PIID`='$PoID'";
		}

		// echo print_r($SqlInsert);
		// exit;
		$StmtInsert = mysql_query($SqlInsert, $this->db);
		if ($StmtInsert) {

			$SqlDeliverDateUpdate = "UPDATE  `ScrmShipmentDetailsBreif`  
                          SET DeliveredDate='$EmptyContainerReturnDate' WHERE PiID='$PoID'";

			$StmtDeliverDateUpdate = mysql_query($SqlDeliverDateUpdate, $this->db);

			$this->postPoRejectStatus($PoID, $FDAContainerStatus);

			$data['Error'] = null;
			$data['Msg'] = "Success";
			//$data['Msg'] = $SampleReceivedDate;
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Post Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function PostClearingAgentDetails()
	{


		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$PoID = $IpData['PoID'];
		$ClearingAgentEntryDate = $IpData['EntryDate'];
		$ClearingAgentEntryNumber = $IpData['ClearingAgentEntryNumber'];
		$CandFCharges = $IpData['CandFCharges'];
		$ADCharges = $IpData['ADCharges'];
		$HmDutyCharges = $IpData['HmDutyCharges'];
		$MPCharges = $IpData['MPCharges'];
		$CVDCharges = $IpData['CVDutyCharges'];
		$RTCharges = $IpData['RTDutyCharges'];
		$InvValue = $IpData['InvValue'];
		$EnteredInvValue = $IpData['EnteredInvValue'];

		$ClearingAgentInvoiceNo = $IpData['ClearingAgentInvoiceNo'];
		$ClearingAgentInvoiceDate = $IpData['ClearingAgentInvoiceDate'];
		$PostedUserID = $IpData['PostedUserID'];


		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = $datetime->format('d-m-Y H:i:s');


		$SqlPostClearingAgentDetails = "SELECT * FROM `ScrmClearingAgentDetails` WHERE PoID='$PoID'";

		$StmtPostClearingAgent = mysql_query($SqlPostClearingAgentDetails, $this->db);
		$postClearAgentCount = mysql_num_rows($StmtPostClearingAgent);
		$RowPostClearingAgent = mysql_fetch_array($StmtPostClearingAgent, MYSQL_ASSOC);
		$IsEdit = $RowPostClearingAgent['IsEdit'];
		$IsEdit += 1;

		if ($postClearAgentCount == 0) {

			$InsertClearingAgent = "INSERT INTO `ScrmClearingAgentDetails`(`PoID`,`ClearingAgentEntryNumber`, `ClearingAgentEntryDate`,`CandFCharges`, `ADCharges`, `HmDutyCharges`,
	                          `MPCharges`,`CVDCharges`,`RTCharges`, `InvValue`, `EnteredInvValue`, `ClearingAgentInvoiceNo`, `ClearingAgentInvoiceDate`, `PostedDateTime`, `PostedUserID`, `IsEdit`)
	                           VALUES ('$PoID','$ClearingAgentEntryNumber','$ClearingAgentEntryDate','$CandFCharges','$ADCharges','$HmDutyCharges','$MPCharges','$CVDCharges','$RTCharges',
							   '$InvValue','$EnteredInvValue','$ClearingAgentInvoiceNo',
	                           '$ClearingAgentInvoiceDate','$PostedDateTime','$PostedUserID',0)";

			$StmtClearingAgent = mysql_query($InsertClearingAgent, $this->db);
		} else {

			$UpdateClearingAgent = "UPDATE `ScrmClearingAgentDetails` SET `ClearingAgentEntryNumber`='$ClearingAgentEntryNumber',`ClearingAgentEntryDate`='$ClearingAgentEntryDate',`CandFCharges`='$CandFCharges',
	                            `ADCharges`='$ADCharges',`HmDutyCharges`='$HmDutyCharges',`MPCharges`='$MPCharges',`CVDCharges`='$CVDCharges',`RTCharges`='$RTCharges',
								`InvValue`='$InvValue',`EnteredInvValue`='$EnteredInvValue',
								`ClearingAgentInvoiceNo`='$ClearingAgentInvoiceNo', `ClearingAgentInvoiceDate`='$ClearingAgentInvoiceDate',
								`PostedDateTime`='$PostedDateTime',`PostedUserID`='$PostedUserID',
					            `IsEdit`='$IsEdit' WHERE `PoID`='$PoID'";

			$StmtClearingAgent = mysql_query($UpdateClearingAgent, $this->db);
		}

		if ($StmtClearingAgent) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Post Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}


	function PostClearingAgentBreakUpDetails()
	{

		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);


		$PoID = $IpData['PoID'];
		$TypeofCharges = $IpData['TypeofCharges'];
		$BillNumber = $IpData['BillNumber'];
		$BillDate = $IpData['BillDate'];
		$BillAmount = $IpData['BillAmount'];
		$PostedUserID = $IpData['PostedUserID'];


		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = $datetime->format('d-m-Y H:i:s');



		$SqlPostClearingAgentBreakUp = "INSERT INTO `ScrmClearingAgentBreakupDetails`( `PoID`, `TypeofCharges`, `BillNumber`, `BillDate`, 
	`BillAmount`, `PostedDateTime`, `IsEdit`, `PostedUserID`) VALUES ('$PoID','$TypeofCharges','$BillNumber',
	'$BillDate','$BillAmount','$PostedDateTime',0,'$PostedUserID')";

		$StmtPostClearingAgentBreakUp = mysql_query($SqlPostClearingAgentBreakUp, $this->db);

		/*$ArraygetClearingAgentBreakupList[]=$this->getClearingAgentBreakupList($PoID);
																		$data['getClearingAgentBreakupList']=$ArraygetClearingAgentBreakupList;*/
		if ($StmtPostClearingAgentBreakUp) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Post Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getClearingAgentBreakupList()
	{

		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);


		$PoID = $IpData['PoID'];

		$SqlClearingAgentBreakup = "SELECT ClearingAgentDetailsBreakupID as BreakupID,TypeofCharges,BillNumber,BillDate,BillAmount FROM `ScrmClearingAgentBreakupDetails` WHERE PoID='$PoID'";
		$StmtClearingAgentBreakup = mysql_query($SqlClearingAgentBreakup, $this->db);
		while ($RowClearingAgentBreakup = mysql_fetch_array($StmtClearingAgentBreakup, MYSQL_ASSOC)) {
			$ArrayClearingAgentBreakup[] = $RowClearingAgentBreakup;
		}

		$data['getClearingAgentBreakupList'] = $ArrayClearingAgentBreakup;
		if ($StmtClearingAgentBreakup) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Post Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getInvoicesPrintPagesList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$InvID = $IpData['PoID'];
		$ReportCat = $IpData['ReportCat'];
		$DocType = $IpData['DocType'];




		$SqlFields = 'TableName,QueryID,ReportName,PoID,DisplayParam,OptionType,PrintLink';

		$SqlInvoicesPrintsList = "SELECT $SqlFields FROM `ScrmExportAvalReports` where ReportType='$DocType'
	  and ReportCat='$ReportCat' and OptionType !=1 and ReportStatus=1 Order by SortOrder asc";


		$StmtInvoicesPrintsList = mysql_query($SqlInvoicesPrintsList, $this->db);

		while ($RowAvalRep = mysql_fetch_array($StmtInvoicesPrintsList, MYSQL_ASSOC)) {

			$TableName = $RowAvalRep['TableName'];
			$QueryID = $RowAvalRep['QueryID'];
			$PoID = $RowAvalRep['PoID'];
			$ReportName = $RowAvalRep['ReportName'];
			$DisplayId = $RowAvalRep['DisplayParam'];
			$OptionType = $RowAvalRep['OptionType'];


			$SqlReport = "select * from $TableName where $PoID='$InvID'";
			$StmtReports = mysql_query($SqlReport, $this->db);
			while ($RowRep = mysql_fetch_array($StmtReports, MYSQL_ASSOC)) {

				$QueryParamVal = $RowRep["$QueryID"];
				if ($DisplayId != '') {
					$DisplayName = $RowRep["$DisplayId"];
				} else {
					$DisplayName = '';
				}

				$RowAval['Count'] = 1;

				if ($DisplayName != '') {
					$DisplayName = ' (' . $DisplayName . ')';
				}



				if ($RowAval['Count'] > 0) {
					$Url = "Reports/" . $RowAvalRep['PrintLink'] . "id=" . $InvID;
				}

				//echo $ReportName; exit;
				$ArrayInvoicesPrintsList['ReportName'] = $ReportName . $DisplayName;
				$ArrayInvoicesPrintsList['Url'] = $Url;

				$AryRowAvalreports[] = $ArrayInvoicesPrintsList;
			}
		}

		//$data['Link']="AddCollection?PIID=";
		$data['AvailableReports'] = $AryRowAvalreports;

		if ($StmtInvoicesPrintsList) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getClearingAgentDetails()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		//$Year=$IpData['Year'];
		$StatusType = $IpData['StatusType'];
		$Country = $IpData['Country'];



		$Query = '';


		if ($StatusType == "0") {
			$Query .= ' and POD.PoID not in (SELECT PoID FROM `ScrmClearingAgentDetails`)';
		} else {
			$Query .= ' and POD.PoID in (SELECT PoID FROM `ScrmClearingAgentDetails`)';
		}


		$DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";
		//$DateFilter= $this->getFinancialYear($Year);

		$SqlFields = 'ConsigneeName,SCI.PiID,CustomsInvNo,
 SPC.Name as PortOfDischarge,ISF.BLNo,SNP.NotifyPartyName';

		$SqlClearingAgentDetails = "SELECT $SqlFields
 from  ScrmCustomInvoices  SCI 
left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.PortOfDischarge
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmNotifyparty  SNP on SNP.NotifyPartyID=POD.NotifyPartyID
left join ScrmShipmentDetailsBreif  SSDB on SSDB.PiID=POD.PoID
WHERE   $DateFilter and TransportedDate!='' $Query  and POD.DestinationCountry='$Country'
order by POD.PoID desc";


		$StmtClearingAgentDetails = mysql_query($SqlClearingAgentDetails, $this->db);

		while ($RowClearingAgentDetails = mysql_fetch_array($StmtClearingAgentDetails, MYSQL_ASSOC)) {


			$ArrayClearingAgentDetails['PoID'] = $RowClearingAgentDetails['PiID'];
			$ArrayClearingAgentDetails['InvoiceNo'] = $RowClearingAgentDetails['CustomsInvNo'];
			$ArrayClearingAgentDetails['Consignee'] = $RowClearingAgentDetails['ConsigneeName'];
			$ArrayClearingAgentDetails['BLNo'] = $RowClearingAgentDetails['BLNo'];
			$ArrayClearingAgentDetails['PortOfDischarge'] = $RowClearingAgentDetails['PortOfDischarge'];
			$ArrayClearingAgentDetails['NotifyPartyName'] = $RowClearingAgentDetails['NotifyPartyName'];



			$AryResponse[] = $ArrayClearingAgentDetails;
		}



		$data['ClearingAgentsList'] = $AryResponse;

		if ($StmtClearingAgentDetails) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function deleteClearingAgentDetails()
	{

		$Error = false;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);


		$PoID = $IpData['PoID'];


		$SqldeleteClearingAgentDetails = "DELETE FROM `ScrmClearingAgentDetails` WHERE PoID='$PoID'";

		$StmtdeleteClearingAgent = mysql_query($SqldeleteClearingAgentDetails, $this->db);


		if ($StmtdeleteClearingAgent) {
			$SqlClearingAgentBreakupDetails = "DELETE FROM `ScrmClearingAgentBreakupDetails` WHERE PoID='$PoID'";

			$StmtdeleteClearingAgentBreakup = mysql_query($SqlClearingAgentBreakupDetails, $this->db);
		}


		if ($StmtdeleteClearingAgent) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Delete Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getForwardContractorList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$BillsType = $IpData['BillsType'];
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];


		$Query = '';
		$IQuery = '';

		if ($Exporter != "") {
			$Query = 'and ExpID in (' . $Exporter . ')';
		}

		if ($BillsType == "0") {
			$IQuery = " and BalancedValue!=0";
		} else if ($BillsType == "1") {
			$IQuery = " and round(BalancedValue) = 0";
		}

		if ($Year != '') {
			$Error = false;
		}



		if (!$Error) {
			$SqlFields = 'FCID,
				BookingDt as BookedDate,
				SpotRte as SpotRate,
				FCNumber as ForwardContractNo,
				StartDt as StartDate,
				EndDt as EndDate,
				Month as Month,
				TotalRate as BookedRate,
				NetValue as BookedValue,
				UtilizedValue as Utilisation,
				DATEDIFF(EndDt,now()) as Diff,
				round(BalancedValue) as BalanceUSD';

			$SqlForwardContractors = "SELECT $SqlFields FROM `ScrmForwardContractsDetails` where FinYear='$Year' $IQuery $Query order by FCID desc";

			$StmtForwardContractors = mysql_query($SqlForwardContractors, $this->db);

			while ($RowForwardContractors = mysql_fetch_array($StmtForwardContractors, MYSQL_ASSOC)) {
				$InvNo = $RowForwardContractors['CInvNo'];
				$InvDate = $RowCollection['CInvDate'];
				$ExpID = $RowCollection['ExporterID'];
				$ProcID = $RowCollection['ProcessorID'];
				$RowForwardContractors['Sno'] = ++$Sno;
				$RowForwardContractors['CurrentDate'] = date('Y-m-d');
				$RowForwardContractors['BalanceUSD'] = $RowForwardContractors['BalanceUSD'] < 0 ? 0 : $RowForwardContractors['BalanceUSD'];

				$CurrentDate = new DateTime(date('Y-m-d'));

				if($BillsType != "1" && round($RowForwardContractors['BalanceUSD']) > 0) {
					$AryRowForwardContractors[] = $RowForwardContractors;
				} else if($BillsType == "1") {
					$AryRowForwardContractors[] = $RowForwardContractors;
				}

			}


			$data['AddLink'] = "AddForwardContract";
			$data['EditLink'] = "AddForwardContract?FCID=";
			$data['ForwardContractorsList'] = $AryRowForwardContractors;

			if ($StmtForwardContractors) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getClearingAgentFormData()
	{


		$Error = false;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);


		$PoID = $IpData['PoID'];



		if (!$Error) {

			$SqlFields = "SCI.CustomsInvNo,SPD.PoNo,SCD.ConsigneeName,SCI.CInvDate,ISF.BLNo,ISF.ContainerNo,FDAEntryNumber,
FDAEntryDate,NotifyPartyName,ClearingAgentInvoiceNo,ClearingAgentInvoiceDate,SCAD.CandFCharges,SCAD.CVDCharges,SCAD.RTCharges,
SCAD.ADCharges as CAAntiDumpingCharges,SCAD.HmDutyCharges as CAHmDutyCharges ,SCAD.MPCharges as CaMPFCharges,
SCAD.EnteredInvValue as EnteredInvValue";

			$SqlClearingAgentFormData = "SELECT  $SqlFields
 FROM `ScrmCustomInvoices` as SCI 
 LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
 left join ScrmShippingBillDetails as SCOM on SPD.PoID=SCOM.InvID 
 LEFT JOIN  ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
 left JOIN ScrmPortCities as SPC on SPC.PortId=SPD.FinalDestination 
 left JOIN ScrmClearingAgentDetails as SCAD on SCAD.PoID=SPD.PoID 
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID 
 left JOIN ScrmISFDetails as ISF on ISF.PIID=SPD.PoID 
 where  SPD.PoID='$PoID' ";

			$StmtClearingAgentFormData = mysql_query($SqlClearingAgentFormData, $this->db);



			while ($RowClearingAgentFormData = mysql_fetch_array($StmtClearingAgentFormData, MYSQL_ASSOC)) {

				$CandFCharges = $RowClearingAgentFormData['CandFCharges'];
				$ArrayCharges = $this->getPaymentValues($PoID, '');


				$SqlBreakupAMount = "SELECT sum(BillAmount) as BillAmount FROM `ScrmClearingAgentBreakupDetails` WHERE PoID='$PoID'";
				$StmtBreakupAmount = mysql_query($SqlBreakupAMount, $this->db);
				$RowBreakupAmount = mysql_fetch_array($StmtBreakupAmount, MYSQL_ASSOC);

				$BreakupBillAmount = $RowBreakupAmount['BillAmount'];

				$AryPaymentValues = $this->getPaymentValues($PoID, '');


				if ($RowClearingAgentFormData['CAAntiDumpingCharges'] == '') {
					$AntiDumpingCharges = $ArrayCharges['AntiDumpingDuty'];
				} else {
					$AntiDumpingCharges = $RowClearingAgentFormData['CAAntiDumpingCharges'];
				}

				if ($RowClearingAgentFormData['CAHmDutyCharges'] == '') {
					$HarbourMaintenanceFee = $ArrayCharges['HarborDuty'];
				} else {
					$HarbourMaintenanceFee = $RowClearingAgentFormData['CAHmDutyCharges'];
				}

				if ($RowClearingAgentFormData['CaMPFCharges'] == '') {
					$MerchandiseProcessFee = $ArrayCharges['MerchantDuty'];
				} else {
					$MerchandiseProcessFee = $RowClearingAgentFormData['CaMPFCharges'];
				}
				//uma cvd calculation included //18-03-2024
				if ($RowClearingAgentFormData['CVDCharges'] == '') {
					$CountervailingDutyFee = $ArrayCharges['CountervailingDuty'];
				} else {
					$CountervailingDutyFee = $RowClearingAgentFormData['CVDCharges'];
				}

				if ($RowClearingAgentFormData['RTCharges'] == '') {
					$ReciprocalTariffsFee = $ArrayCharges['ReciprocalTariffsFee'];
				} else {
					$ReciprocalTariffsFee = $RowClearingAgentFormData['RTCharges'];
				}

				// $TotalCharges = $CandFCharges + $AntiDumpingCharges + $HarbourMaintenanceFee + $MerchandiseProcessFee;
				$TotalCharges = $CandFCharges + $AntiDumpingCharges + $HarbourMaintenanceFee + $MerchandiseProcessFee + $CountervailingDutyFee + $ReciprocalTariffsFee;
				//uma cvd calculation included //18-03-2024
				//response//


				$ArrayClearingAgentFormData['InvoiceNo'] = $RowClearingAgentFormData['CustomsInvNo'];
				$ArrayClearingAgentFormData['PoNo'] = $RowClearingAgentFormData['PoNo'];
				$ArrayClearingAgentFormData['ConsigneeName'] = $RowClearingAgentFormData['ConsigneeName'];
				$ArrayClearingAgentFormData['CInvDate'] = $RowClearingAgentFormData['CInvDate'];
				$ArrayClearingAgentFormData['BLNo'] = $RowClearingAgentFormData['BLNo'];
				$ArrayClearingAgentFormData['ContainerNo'] = $RowClearingAgentFormData['ContainerNo'];
				$ArrayClearingAgentFormData['EntryNumber'] = $RowClearingAgentFormData['FDAEntryNumber'];
				$ArrayClearingAgentFormData['EntryDate'] = $RowClearingAgentFormData['FDAEntryDate'];
				$ArrayClearingAgentFormData['NotifyPartyName'] = $RowClearingAgentFormData['NotifyPartyName'];
				$ArrayClearingAgentFormData['ClearingAgentInvoiceNo'] = $RowClearingAgentFormData['ClearingAgentInvoiceNo'];
				$ArrayClearingAgentFormData['ClearingAgentInvoiceDate'] = $RowClearingAgentFormData['ClearingAgentInvoiceDate'];
				$ArrayClearingAgentFormData['CandFCharges'] = $CandFCharges;
				$ArrayClearingAgentFormData['AntiDumpingCharges'] = $AntiDumpingCharges;
				$ArrayClearingAgentFormData['HarbourMaintenanceFee'] = $HarbourMaintenanceFee;
				$ArrayClearingAgentFormData['MerchandiseProcessFee'] = $MerchandiseProcessFee;
				$ArrayClearingAgentFormData['CountervailingDutyFee'] = $CountervailingDutyFee;
				$ArrayClearingAgentFormData['ReciprocalTariffsFee'] = $ReciprocalTariffsFee;
				$ArrayClearingAgentFormData['InvValueUSD'] = $AryPaymentValues['InvValueUSD'];
				$ArrayClearingAgentFormData['EnteredInvValue'] = $RowClearingAgentFormData['EnteredInvValue'];

				$ArrayClearingAgentFormData['TotalCharges'] = $TotalCharges;
				$ArrayClearingAgentFormData['GrandTotalCharges'] = $TotalCharges + $BreakupBillAmount;



				//response//

				$Response[] = $ArrayClearingAgentFormData;
			}

			$data['ClearingAgentFormData'] = $Response;
			if ($StmtClearingAgentFormData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getNegotiationList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$BillsType = $IpData['BillsType'];
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$DateFilter = $this->getFinancialYear($Year);

		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';

		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}
		if ($BillsType == "0") {
			//$IQuery=' SIFD.PIID not in (SELECT PoID FROM `ScrmCommercialData`) ';
			$IQuery = "SCI.CInvDate $DateFilter and POD.PoID not in (SELECT PIID FROM `ScrmPaymentNegotitions`) ";
		} else if ($BillsType == "1") {
			//$IQuery="SCI.CInvDate $DateFilter and SCI.PiID  in (SELECT PoID FROM `ScrmCommercialData`)"; 
			$IQuery = "SCI.CInvDate $DateFilter and POD.PoID  in (SELECT PIID FROM `ScrmPaymentNegotitions`)";
		}

		if ($Year != '') {
			$Error = false;
		}


		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCD.ConsigneeName,
		SPN.BnkRefNo as BankRefNo,
		SCI.CInvNo,
		SCI.CInvDate';

			$SqlNegotiation = "SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
			left join ScrmPaymentNegotitions as SPN on POD.PoID=SPN.PIID
            left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID 			
			WHERE $IQuery and POD.Status not in (3,7)  and SCI.PaymentType='Negotiations'
            and TransportedDate!=''			
			$Query $SQuery  $SubQuery order by CommercialID desc";

			$StmtNegotiation = mysql_query($SqlNegotiation, $this->db);

			while ($RowNegotiation = mysql_fetch_array($StmtNegotiation, MYSQL_ASSOC)) {
				$InvNo = $RowNegotiation['CInvNo'];
				$InvDate = $RowNegotiation['CInvDate'];
				$ExpID = $RowNegotiation['ExporterID'];
				$ProcID = $RowNegotiation['ProcessorID'];
				$RowNegotiation['Sno'] = ++$Sno;
				$RowNegotiation['CInvNo'] = $this->getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID);
				$AryRowNegotiation[] = $RowNegotiation;
			}


			$data['Link'] = "AddNegotiation?PIID=";

			$data['NegotiationList'] = $AryRowNegotiation;

			if ($StmtNegotiation) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getCollectionList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$BillsType = $IpData['BillsType'];
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$PaymentType = $IpData['PaymentType'];
		$DateFilter = $this->getFinancialYear($Year);


		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';
		$PQuery = '';

		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}
		if ($BillsType == "0") {

			$IQuery = "SCI.CInvDate $DateFilter and POD.PoID not  in (SELECT PIID FROM `ScrmPaymentCollections` where Status!=0 ) and  POD.Status not in (2,3,4) ";

			/*$IQuery="SCI.CInvDate $DateFilter and POD.PoID not  in (SELECT PIID FROM `ScrmPaymentCollections`)";*/
		} else if ($BillsType == "1") {


			$IQuery = "SCI.CInvDate $DateFilter and POD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 ) and  POD.Status not in (2,3,4)";

			/*$IQuery="SCI.CInvDate $DateFilter and POD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where  Type='$PaymentType')";*/
		}

		if ($PaymentType == "1") {

			$PQuery = " and SCI.PaymentType='Collections'";
		} else if ($PaymentType == "2") {

			$PQuery = " and SCI.PaymentType='Negotiations'";
		}

		if ($Year != '') {
			$Error = false;
		}


		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCD.ConsigneeName,
		SCI.CustomsInvNo as CInvNo,
		(Select BankRefNo from ScrmCollectionBreakUP  where PIID=POD.PoID order by ColBrkID  desc limit 1) as BankRefNo,
		SCI.CInvDate';

			$SqlCollection = "SELECT $SqlFields FROM  ScrmCustomInvoices as SCI 
			left join ScrmPoData as POD on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
			left join ScrmPaymentCollections as SPC on POD.PoID=SPC.PIID 
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID 
			WHERE $IQuery and POD.Status not in (3,7)   
            and TransportedDate!=''			
			$Query $SQuery  $SubQuery $PQuery order by CommercialID desc";
			$StmtCollection = mysql_query($SqlCollection, $this->db);

			while ($RowCollection = mysql_fetch_array($StmtCollection, MYSQL_ASSOC)) {
				$InvNo = $RowCollection['CInvNo'];
				$InvDate = $RowCollection['CInvDate'];
				$ExpID = $RowCollection['ExporterID'];
				$ProcID = $RowCollection['ProcessorID'];
				$RowCollection['Sno'] = ++$Sno;
				$RowCollection['CInvNo'] = $InvNo;
				$AryRowCollection[] = $RowCollection;
			}


			$data['Link'] = "AddCollection?PIID=";
			$data['CreateBankLetterLink'] = "AddBillofExchange?PIID=";
			$data['CollectionList'] = $AryRowCollection;

			if ($StmtCollection) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function PostStoreDeliveryDetails()
	{

		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$StoreId = $IpData['StoreID'];
		$StoreName = $IpData['name'];
		$StoreAddress1 = $IpData['address1'];
		$StoreAddress2 = $IpData['address2'];
		$PhoneNumber = $IpData['phone'];
		$EmailID = $IpData['emailID'];
		$State = $IpData['state'];
		$CountryID = $IpData['drpcountry'];
		$PortofDischarge = $IpData['drpportDischarge'];
		$FinalDestination = $IpData['drpfinalDestination'];
		$ContactPerson = $IpData['contactPerson'];
		$BuyerID = $IpData['drpBuyer'];
		$UserID = $IpData['userid'];
		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = $datetime->format('Y-m-d H:i:s');

		$IsSetStoreName = $this->isSetEmpty($StoreName);
		$IsSetBuyerID = $this->isSetEmpty($BuyerID);
		$IsSetStoreAddress1 = $this->isSetEmpty($StoreAddress1);
		//if(!empty($StoreName) && !empty($BuyerID) && !empty($StoreAddress1)){
		if ($IsSetStoreName && $IsSetBuyerID && $IsSetStoreAddress1) {
			$SqlPostStoreDelivery = "SELECT * FROM `ScrmStoreDeliveryDetails` where StoreID='$StoreId'";

			$StmtPostStoreDelivery = mysql_query($SqlPostStoreDelivery, $this->db);
			$RowPostStoreDelivery = mysql_fetch_array($StmtPostStoreDelivery, MYSQL_ASSOC);
			$PostStoreCount = mysql_num_rows($StmtPostStoreDelivery);

			$IsEdit = $RowPostStoreDelivery['IsEdit'] + 1;

			if ($PostStoreCount == 0) {
				$SqlInsertStoreDelivery = "INSERT INTO `ScrmStoreDeliveryDetails`( `StoreName`, `StoreAddress1`, `StoreAddress2`, `ContactPerson`,
	                    `PhoneNumber`, `EmailID`, `State`, `CountryID`, `BuyerID`, `PortofDischarge`, 
	                     `FinalDestination`, `PostedDateTime`, `UserID`, `IsEdit`, `StoreStatus`) 				
	                      VALUES ('$StoreName','$StoreAddress1','$StoreAddress2','$ContactPerson','$PhoneNumber','$EmailID',
	                     '$State','$CountryID','$BuyerID','$PortofDischarge','$FinalDestination','$PostedDateTime','$UserID','0','1')";
				$StmtInsert = mysql_query($SqlInsertStoreDelivery, $this->db);
			} else {
				$SqlUpdateStoreDelivery = "UPDATE `ScrmStoreDeliveryDetails` SET `StoreName`='$StoreName',`StoreAddress1`='$StoreAddress1',
	                    `StoreAddress2`='$StoreAddress2',`ContactPerson`='$ContactPerson',`PhoneNumber`='$PhoneNumber',`EmailID`='$EmailID',`State`='$State',`CountryID`='$CountryID',
	                     `BuyerID`='$BuyerID',`PortofDischarge`='$PortofDischarge',`FinalDestination`='$FinalDestination',
	                    `PostedDateTime`='$PostedDateTime',`UserID`='$UserID',`IsEdit`='$IsEdit'
						  WHERE `StoreID`='$StoreId'";
				$StmtInsert = mysql_query($SqlUpdateStoreDelivery, $this->db);
			}
			if ($StmtInsert) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Post Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		} else {
			$data['Error'] = "Name and Buyer and Address are Mandatory";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}


	function getEditPageStoreDeliveryDetails()
	{

		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$StoreID = $IpData['id'];



		$SqlStoreDeliveryDetails = "SELECT  `StoreName`, `StoreAddress1`, `StoreAddress2`, `ContactPerson`, `PhoneNumber`, `EmailID`,
	 `State`, `CountryID`, `BuyerID`, `PortofDischarge`, `FinalDestination` FROM `ScrmStoreDeliveryDetails` where StoreID='$StoreID'";

		$StmtStoreDeliveryDetails = mysql_query($SqlStoreDeliveryDetails, $this->db);

		while ($RowStoreDeliveryDetails = mysql_fetch_array($StmtStoreDeliveryDetails, MYSQL_ASSOC)) {


			$Response[] = $RowStoreDeliveryDetails;
		}


		$data['EditPageStoreDeliveryDetails'] = $Response;

		if ($StmtStoreDeliveryDetails) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getStoreDeliveryDetails()
	{

		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);


		$getStoreDeliveryDetails = "SELECT StoreID,StoreName,StoreAddress1,ConsigneeName,SCL.name as Country,SPC.Name as PortofDischarge,
	SPCL.Name as FinalDestination FROM `ScrmStoreDeliveryDetails` as SSD left join ScrmConsigneeDetails as SCD on SCD.ConID=SSD.BuyerID 
	left join ScrmCountriesList as SCL on SCL.id=SSD.CountryID left join ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge 
	left join ScrmPortCities as SPCL on SPCL.PortId=SSD.FinalDestination ORDER By StoreID desc";

		$StmtgetStoreDelivery = mysql_query($getStoreDeliveryDetails, $this->db);

		while ($RowgetStoreDelivery = mysql_fetch_array($StmtgetStoreDelivery, MYSQL_ASSOC)) {

			$ArrayStoreDeliveryDetails = array();

			$ArrayStoreDeliveryDetails['StoreID'] = $RowgetStoreDelivery['StoreID'];
			$ArrayStoreDeliveryDetails['StoreName'] = $RowgetStoreDelivery['StoreName'];
			$ArrayStoreDeliveryDetails['StoreAddress'] = $RowgetStoreDelivery['StoreAddress1'];
			$ArrayStoreDeliveryDetails['Buyer'] = $RowgetStoreDelivery['ConsigneeName'];
			$ArrayStoreDeliveryDetails['PortofDischarge'] = $RowgetStoreDelivery['PortofDischarge'];
			$ArrayStoreDeliveryDetails['FinalDestination'] = $RowgetStoreDelivery['FinalDestination'];

			$Response[] = $ArrayStoreDeliveryDetails;
		}


		$data['StoreDeliveryDetails'] = $Response;

		if ($StmtgetStoreDelivery) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getPendingPaymentsData()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		//$BillsType=$IpData['BillsType']; 
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$Brand = $IpData['Brand'];
		$Month = $IpData['Month'];
		$JoinQuery = $IpData['JoinQuery'];
		$ClientQuery = $IpData['ClientQuery'];
		$FdaRelease = $IpData['FdaRelease'];
		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Bank = $IpData['Bank'];
		$PaymentStatus = $IpData['PaymentStatus'];


		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';
		$PQuery = '';

		// $DateFilter = $this->getFinancialYear($Year); //uma
		//$DateFilter = 'SCI.CInvDate ' . $this->getFinancialClsYear($Year, $Month); //uma 
		if ($Year != '') {
			$FromDate = $Year . '-04-01';
			$ToDate = ($Year + 1) . '-03-31';
			$Year = '';
		}
		$DateFilter = "SCI.CInvDate BETWEEN '$FromDate' AND '$ToDate' ";

		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Bank != "") {
			$Query .= " and SCOD.BankID = $Bank ";
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}

		if ($Brand != '') {
			$SQuery .= " and POD.PoID  in (SELECT DISTINCT(InvId) FROM `ScrmPoGradeData` WHERE Brand='$Brand')";
		}

		if ($FdaRelease != "") {

			if ($FdaRelease == "YES") {
				$Query .= 'and SIF.FDAEUReleaseCFIADate != ""';
			} else {
				$Query .= 'and (SIF.FDAEUReleaseCFIADate ="" OR SIF.FDAEUReleaseCFIADate IS NULL)';
			}
		}

		//if ($Year != '') {
		if ($FromDate != '' && $ToDate != '') {
			$Error = false;
		}

		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNo,
		SCD.ConsigneeName,
		SPC.Name as PortOfDischarge,
		SIF.ETA_POD,SIF.ETA_FD,
		SIF.EstimatedETA as ETAModifyDate,
		SIF.GetOutFull,
		SIF.FDAClearDate,
		SCL.name as DestinationCountry,
		SIF.ShippingOnboardDt,
		SPCL.BnkChrges,SPCL.OtherAmount,SPCL.EtraAmount,
		COALESCE(DATEDIFF(SIF.ETA_POD,now())-1,0) as ETATransitDays,
		COALESCE(DATEDIFF(SIF.ETA_POD,SIF.ShippingOnboardDt),0) as TransitDays,
		SCI.CInvNo,SCI.CustomsInvNo,SCI.ContainerNo,
		(SELECT sum(Mcs)  FROM `ScrmPoGradeData` WHERE InvId=POD.PoID)as Mcs,
		SPT.PaymentName,
		SCI.CInvDate,
		SCI.PaymentType,
		SIF.FDAEUReleaseCFIADate, SIF.FDAEUHoldDate, SIF.FDAContainerStatus, SBDM.BankDetails,
		SBDM.BankName, SBDM.AccntName, SBDM.AccountNumber';



			// $SqlPendingPayments = "SELECT $SqlFields FROM  ScrmPoData as POD
			// left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			// left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			// left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			// left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			// left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			// left join ScrmISFDetails as SIF on POD.PoID=SIF.PIID 
			// left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 			 
			// left join ScrmPaymentCollections as SPCL on SPCL.PIID=POD.PoID			 
			// WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7)  and SCI.CInvDate $DateFilter  $Query $SQuery  $SubQuery 
			// and POD.PoID not in(SELECT PIID FROM `ScrmPaymentCollections` WHERE Status=1)
			//  order by POD.PoID desc";

			$SqlPendingPayments = "SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmISFDetails as SIF on POD.PoID=SIF.PIID 
			left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 			 
			left join ScrmPaymentCollections as SPCL on SPCL.PIID=POD.PoID
			left join ScrmCommercialData as SCOD on SCOD.PoID=SCI.PiID
			left join ScrmBankDetailsMaster as SBDM on SBDM.ID=SCOD.BankID
			$JoinQuery		 
			WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7,4)  and  $DateFilter  $Query $SQuery  $SubQuery 
			$ClientQuery
			order by POD.PoID desc";


			$StmtPendingPayments = mysql_query($SqlPendingPayments, $this->db);
			while ($RowPendingPayments = mysql_fetch_array($StmtPendingPayments, MYSQL_ASSOC)) {

				$PoID = $RowPendingPayments['PoID'];
				$PaymentType = $RowPendingPayments['PaymentType'];


				if ($PaymentType == 'Collections') {
					$SqlUtilisedValues = "SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
				} else {
					$SqlUtilisedValues = "SELECT PoID,(RezValue1+RezValue2+RezValue3) as UtilizedValue,BnkChrges,
					FBKCharges,OtherCharges,RezRef1,RezRef2,RezRef3 FROM `ScrmPaymentCollections` as SPC 
	 				left join ScrmRealizationDetails as SRD on SRD.PoID=SPC.PIID WHERE PoID='$PoID'"; //uma
				}
				$StmtUtilisedValues = mysql_query($SqlUtilisedValues, $this->db);
				$RowUtilisedValues = mysql_fetch_array($StmtUtilisedValues, MYSQL_ASSOC);
				$UtilizedValue = $RowUtilisedValues['UtilizedValue'];


				//
				$SqlColctByNego = "SELECT SPC.DueDte,SCBP.ColType,SCBP.BankRefNo,SCBP.UtilizedValue,UtilizedDate,SpotRate,BnkChrges
 				FROM `ScrmPaymentCollections` SPC LEFT JOIN ScrmCollectionBreakUP SCBP ON SCBP.PiID=SPC.PIID
				WHERE SPC.PIID='$PoID' ORDER BY SCBP.ColBrkID ;";

				$StmtColctByNego = mysql_query($SqlColctByNego, $this->db);
				$RowColctByNego = mysql_fetch_array($StmtColctByNego, MYSQL_ASSOC);
				$DueDte = $RowColctByNego['DueDte'];


				//
				$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
				$InvValueUSD = $ArrayPaymentValues['InvValueUSD'];
				$BankCharges = $RowPendingPayments['BnkChrges'];
				$ExtraAmount = $RowPendingPayments['EtraAmount'];
				$OtherAmount = $RowPendingPayments['OtherAmount'];
				$UnitCurrency = $ArrayPaymentValues['UnitCurrency'];
				$InvDate = $RowPendingPayments['CInvDate'];

				$ShortFall = $InvValueUSD + $ExtraAmount - $BankCharges - $UtilizedValue - $OtherAmount;

				if ($UnitCurrency == 'INR') {
					$ExchangeRateQuery = "SELECT * FROM `ScrmExchangeRates` where  FromDate <= '$InvDate' ORDER BY FromDate  DESC limit 1";
					$StmtExchangeRateQuery = mysql_query($ExchangeRateQuery, $this->db);
					$RowExchangeRateQuery = mysql_fetch_array($StmtExchangeRateQuery, MYSQL_ASSOC);
					$ShortFall = $ShortFall / $RowExchangeRateQuery['ExchangeRate'];
				}

				$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
				$InvValueUSD = $ArrayPaymentValues['InvValueUSD'];

				$InvNo = $RowPendingPayments['CInvNo'];
				$ExpID = $RowPendingPayments['ExporterID'];
				$ProcID = $RowPendingPayments['ProcessorID'];
				$ShippingOnboardDt = $this->getFormatedDatelines($RowPendingPayments['ShippingOnboardDt']);
				$GetOutFull = $this->getFormatedDatelines($RowPendingPayments['GetOutFull']);
				$ETA_FD = $this->getFormatedDatelines($RowPendingPayments['ETA_FD']);
				$ETAModifyDate = $this->getFormatedDatelines($RowPendingPayments['ETAModifyDate']);
				$FDAClearDate = $this->getFormatedDatelines($RowPendingPayments['FDAClearDate']);
				$DueDte = $this->getFormatedDatelines($DueDte);

				$EmptyReturn = $this->getFormatedDatelines($RowPendingPayments['EmptyReturn']);
				$FDAEUReleaseCFIADate = $this->getFormatedDatelines($RowPendingPayments['FDAEUReleaseCFIADate']);
				$FDAEUHoldDate = $this->getFormatedDatelines($RowPendingPayments['FDAEUHoldDate']);

				$RowPendingPayments['ShippingOnboardDt'] = $ShippingOnboardDt;
				$RowPendingPayments['ETADate'] = $ETA_FD;
				$RowPendingPayments['ETAModifyDate'] = $ETAModifyDate;
				$RowPendingPayments['GetOutFull'] = $GetOutFull;
				$RowPendingPayments['FDACleared'] = $FDAEUReleaseCFIADate;
				$RowPendingPayments['DueOn'] = $DueDte;
				$RowPendingPayments['FDAEUReleaseCFIADate'] = $FDAEUReleaseCFIADate;
				$RowPendingPayments['FDAEUHoldDate'] = $FDAEUHoldDate;
				$RowPendingPayments['FDAContainerStatus'] = $RowPendingPayments['FDAContainerStatus'];
				// $RowPendingPayments['BankDetails'] = $RowPendingPayments['BankDetails'];
				$RowPendingPayments['BankDetails'] = $RowPendingPayments['BankName'] . ' - ' . $RowPendingPayments['AccountNumber'];
				$RowPendingPayments['Sno'] = ++$Sno;
				// $RowPendingPayments['CInvNo'] = $this->getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID);
				$RowPendingPayments['CInvNo'] = $RowPendingPayments['CustomsInvNo'];
				$RowPendingPayments['UnitCurrency'] = $UnitCurrency;
				if ($UnitCurrency == 'INR') {
					$RowPendingPayments['TotalInvoiceValueINR'] = number_format($InvValueUSD, 2);
				} else {
					$RowPendingPayments['TotalInvoiceValue'] = number_format($InvValueUSD, 2);
				}


				$RowPendingPayments['RemainingInvoiceValue'] = number_format($ShortFall, 2);

				$SqlTranshipmentPortsList = "SELECT `FromDate` FROM `ScrmTranshipmentPortsList` WHERE `PIID` = '$PoID' ORDER BY `TspID` DESC LIMIT 1";
				$StmtTranshipmentPorts = mysql_query($SqlTranshipmentPortsList, $this->db);
				$RowTranshipmentPorts = mysql_fetch_array($StmtTranshipmentPorts, MYSQL_ASSOC);
				$DepartureDate = $RowTranshipmentPorts['FromDate'];

				$RowPendingPayments['DepartureDate'] = $this->getFormatedDatelines($DepartureDate);

				if (
					$PaymentStatus != 'ADVISED PENDING' ||
					($PaymentStatus == 'ADVISED PENDING' && $RowUtilisedValues['UtilizedValue'] > 0
						&& ($RowUtilisedValues['RezRef1'] == ''
							|| $RowUtilisedValues['RezRef2'] == '' || $RowUtilisedValues['RezRef3'] == ''))
				) {
					$AryRowPendingPayments[] = $RowPendingPayments;
				}
			}


			//$data['Link']="AddCollection?PIID=";
			$data['Data'] = $AryRowPendingPayments;

			if ($StmtPendingPayments) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$data['Query'] = $SqlPendingPayments;
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getSalesOrderwiseData()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		//$BillsType=$IpData['BillsType']; 
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$Month = $IpData['Month'];

		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';
		$MQuery = '';

		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}
		if ($Month != "") {
			$MQuery = 'and Month(POD.PoDate)=' . $Month;
		}



		if ($Year != '') {
			$Error = false;
		}



		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		SCD.ConsigneeName,
		SPC.Name as PortOfDischarge,
		POD.PoNo,
		POD.PoDate,
		SCL.name as DestinationCountry,
		SCI.CInvNo,
		(SELECT sum(Mcs)  FROM `ScrmPoGradeData` WHERE InvId=POD.PoID)as Mcs,
		SPT.PaymentName,
		SCI.CInvDate';

			$SqlCountryWiseData = "SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 
			WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7)   $Query $SQuery  $SubQuery $MQuery order by POD.PoID desc";


			$StmtCountryWiseData = mysql_query($SqlCountryWiseData, $this->db);

			while ($RowCountryWiseData = mysql_fetch_array($StmtCountryWiseData, MYSQL_ASSOC)) {



				$InvNo = $RowCountryWiseData['CInvNo'];
				$InvDate = $RowCountryWiseData['CInvDate'];
				$ExpID = $RowCountryWiseData['ExporterID'];
				$ProcID = $RowCountryWiseData['ProcessorID'];
				$RowCountryWiseData['Sno'] = ++$Sno;
				$RowCountryWiseData['CInvNo'] = $this->getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID);
				$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
				$RowCountryWiseData['TotalInvoiceValue'] = $ArrayPaymentValues['InvValueUSD'];
				$AryRowCountryWiseData[] = $RowCountryWiseData;
			}


			//$data['Link']="AddCollection?PIID=";
			$data['Data'] = $AryRowCountryWiseData;

			if ($StmtCountryWiseData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}


	function getProformaInvoiceList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		//$BillsType=$IpData['BillsType']; 
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$PageNo = $IpData['PageNo'];


		$Query = '';
		$SubQuery = '';
		$SQuery = '';


		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}

		$RecordsPerPage = 100;

		$LimitMax = $PageNo * $RecordsPerPage;
		$LimitStartMin = $LimitMax - $RecordsPerPage;
		$Limit = $LimitStartMin . ',' . $LimitMax;


		if ($Year != '') {
			$Error = false;
		}
		$SqlCustomInvoices = "SELECT COALESCE(GROUP_CONCAT(SCI.PiID),0) as CompleatedPIID FROM `ScrmCustomInvoices` as SCI left Join ScrmShipmentDetailsBreif as SSDB on SCI.PiID=SSDB.PiID
	WHERE SSDB.TransportedDate=''";
		$StmtCInv = mysql_query($SqlCustomInvoices, $this->db);
		$RowCInv = mysql_fetch_array($StmtCInv, MYSQL_ASSOC);
		$CompleatedPIID = $RowCInv['CompleatedPIID'];



		if (!$Error) {
			$SqlFields = 'POD.PoID,
		SCD.ConsigneeName,
		POD.PoNo,
		POD.InvNumFull as InvNo,
		POD.InvDate,
		POD.ShipByDate,
		SSC.Name as PoStatus,
		SSC.Color,
		AED.ExpName as ProcessorName';

			$SqlPIData = "SELECT $SqlFields
 FROM ScrmPIData as POD 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join AdminExporterDetails as  AED on AED.ExpID=POD.ProcessorID
WHERE POD.PoID not in ($CompleatedPIID)  $Query $SQuery  $SubQuery  LIMIT $RecordsPerPage OFFSET $LimitStartMin";


			$StmtPIData = mysql_query($SqlPIData, $this->db);

			while ($RowPIData = mysql_fetch_array($StmtPIData, MYSQL_ASSOC)) {
				//$InvNo=$RowCountryWiseData['InvNo'];
				//$InvDate=$RowCountryWiseData['CInvDate'];
				//$ExpID=$RowCountryWiseData['ExporterID'];
				//$ProcID=$RowCountryWiseData['ProcessorID'];
				$RowPIData['Sno'] = ++$Sno;
				$AryRowPIData[] = $RowPIData;
			}


			$data['TotalCount'] = "533";
			$data['RemainingCount'] = "10";
			$data['EditLink'] = "PoEdit?id=";
			$data['PrintLink'] = "DocProformaInvoicePrint.php?id=";
			$data['InvoiceList'] = $AryRowPIData;

			if ($StmtPIData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}



	function getSalesShipmentsData()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		//$BillsType=$IpData['BillsType']; 
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$Month = $IpData['Month'];

		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';
		$MQuery = '';

		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}
		if ($Month != "") {
			$MQuery = 'and Month(POD.PoDate)=' . $Month;
		}



		if ($Year != '') {
			$Error = false;
		}



		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		SCD.ConsigneeName,
		SPC.Name as PortOfDischarge,
		POD.PoNo,
		POD.PoDate,
		POD.ShippingTerms,
		POD.DeliveryTerms,
		SCL.name as DestinationCountry,
		SCI.CInvNo,
		(SELECT sum(Mcs)  FROM `ScrmPoGradeData` WHERE InvId=POD.PoID)as Mcs,
		SPT.PaymentName,
		SCI.CInvDate';

			$SqlCountryWiseData = "SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 
			WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7)   $Query $SQuery  $SubQuery $MQuery order by POD.PoID desc";


			$StmtCountryWiseData = mysql_query($SqlCountryWiseData, $this->db);

			while ($RowCountryWiseData = mysql_fetch_array($StmtCountryWiseData, MYSQL_ASSOC)) {



				$InvNo = $RowCountryWiseData['CInvNo'];
				$InvDate = $RowCountryWiseData['CInvDate'];
				$ExpID = $RowCountryWiseData['ExporterID'];
				$ProcID = $RowCountryWiseData['ProcessorID'];
				$RowCountryWiseData['Sno'] = ++$Sno;
				$RowCountryWiseData['CInvNo'] = $this->getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID);
				$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
				$RowCountryWiseData['TotalInvoiceValue'] = $ArrayPaymentValues['InvValueUSD'];
				$AryRowCountryWiseData[] = $RowCountryWiseData;
			}


			//$data['Link']="AddCollection?PIID=";
			$data['Data'] = $AryRowCountryWiseData;

			if ($StmtCountryWiseData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}
	function getCountrywisePoData()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		//$BillsType=$IpData['BillsType']; 
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];

		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';
		$PQuery = '';

		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}



		if ($Year != '') {
			$Error = false;
		}



		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		SCD.ConsigneeName,
		SPC.Name as PortOfDischarge,
		SPC.name as DestinationCountry,
		SCI.CInvNo,
		(SELECT sum(Mcs)  FROM `ScrmPoGradeData` WHERE InvId=POD.PoID)as Mcs,
		SPT.PaymentName,
		SCI.CInvDate';

			$SqlCountryWiseData = "SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 
			WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7)   $Query $SQuery  $SubQuery  order by POD.PoID desc";


			$StmtCountryWiseData = mysql_query($SqlCountryWiseData, $this->db);

			while ($RowCountryWiseData = mysql_fetch_array($StmtCountryWiseData, MYSQL_ASSOC)) {



				$InvNo = $RowCountryWiseData['CInvNo'];
				$InvDate = $RowCountryWiseData['CInvDate'];
				$ExpID = $RowCountryWiseData['ExporterID'];
				$ProcID = $RowCountryWiseData['ProcessorID'];
				$RowCountryWiseData['Sno'] = ++$Sno;
				$RowCountryWiseData['CInvNo'] = $this->getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID);
				$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
				$RowCountryWiseData['TotalInvoiceValue'] = $ArrayPaymentValues['InvValueUSD'];
				$AryRowCountryWiseData[] = $RowCountryWiseData;
			}


			//$data['Link']="AddCollection?PIID=";
			$data['Data'] = $AryRowCountryWiseData;

			if ($StmtCountryWiseData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}
	function getPortWisePoData()
	{
		$Error = true;
		$Token = $this->getToken();
		$ClientId = $Token['ClientID'];

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		//$BillsType=$IpData['BillsType']; 
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$PortID = $IpData['PortID'];
		$Brand = $IpData['Brand'];
		$Month = $IpData['Month'];
		$Type = $IpData['Type'];
		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Fbdstatus = $IpData['Fbdstatus'];
		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';
		$PQuery = '';
		$DeliveredQuery = "and (SIF.EmptyReturn is null or  SIF.EmptyReturn='')";
		// if ($ClientId == 'DFLV3') {
		// 	$DeliveredQuery = "and ((SIF.EmptyReturn IS NULL OR SIF.EmptyReturn = '')
		// 		OR (POD.DestinationCountry = 157 AND SIF.FDAExam = 'Yes'))";
		// }
		// $DateFilter = $this->getFinancialYear($Year); //uma
		//$DateFilter = 'SCI.CInvDate ' . $this->getFinancialClsYear($Year, $Month); //uma 
		if ($Year != '') {
			$FromDate = $Year . '-04-01';
			$ToDate = ($Year + 1) . '-03-31';
			$Year = '';
		}
		$DateFilter = " SCI.CInvDate BETWEEN '$FromDate' AND '$ToDate' ";
		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery .= 'and POD.DestinationCountry=' . $Country;
		}

		if ($PortID != "") {
			$PQuery .= 'and POD.PortOfDischarge=' . $PortID;
			$DeliveryQry = '';
		} else {
			//$DeliveryQry = " and SSDB.DeliveredDate=''"; // this line is commented by the appovalof surya becauase total onwater and popup list is misamatched
		}

		if ($Brand != '') {
			$SQuery .= " and POD.PoID  in (SELECT DISTINCT(InvId) FROM `ScrmPoGradeData` WHERE Brand='$Brand')";
		}

		if ($Fbdstatus != "") {

			if ($Fbdstatus == "FDA RELEASE") {
				$Query .= " AND SIF.FDAExam = 'Proceed' ";
			}

			if ($Fbdstatus == "FDA PENDING") {
				$Query .= "  AND SIF.FDAExam = 'Yes' ";
			}

			if ($Fbdstatus == "GATE OUT") {
				$Query .= "   AND SIF.GetOutFull IS NOT NULL   AND SIF.GetOutFull != '' ";
			}

			// 		if ($Fbdstatus == "ON WATER") {
			//     $Query .= "   AND SIF.ShippingOnboardDt IS NOT NULL   AND (SIF.GetOutFull IS NULL OR SIF.GetOutFull = '') ";
			// }
		}




		// if ($Month != "") {
		// $DateFilter = " Month(SCI.CInvDate)='$Month'  and  Year(SCI.CInvDate)= '$Year'";
		// } else if ($Month == "") {
		// $DateFilter = 'SCI.CInvDate ' . $this->getFinancialYear($Year); //uma
		// }

		//if ($Year != '') {
		if ($FromDate != '' || $ToDate != '') {
			$Error = false;
		}

		if ($Type === 'Delivered') {
			$DeliveredQuery = "";
		}


		if (!$Error) {
			$SqlFields = 'POD.PoID as POID,POD.ExporterID,POD.ProcessorID,POD.PoNO,POD.PoDate,POD.DeliveryTerms,POD.ShipByDate,SCD.ConsigneeCode,
		SCD.ConsigneeName,SPC.Name as PortOfDischarge,SPCF.Name as FinalDestination,SIF.ShippingOnboardDt as ShippedOn,SSDB.ReturnedDate,
		SSDB.DeliveredDate,SSDB.TransportedDate,SSDB.ProductionStartedDate,SIF.ETA_POD,SIF.EstimatedETA,SIF.GetOutFull,SIF.EmptyReturn,
		SIF.FDAEntryNumber,SIF.FDAExam,SLM.LinerName,COALESCE(DATEDIFF(ETA_FD,ShippingOnboardDt),0) as TransitDaysPerLiner,
		COALESCE(DATEDIFF(EstimatedETA,BLDate),0) as TransitDays,
		SCI.CustomsInvNo,SCI.ContainerNo,SCI.CInvDate';

			// $SqlPortWiseData = "SELECT $SqlFields FROM  ScrmPoData as POD
			// left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			// left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			// left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			// left join ScrmPortCities as SPCF on POD.FinalDestination=SPCF.PortId
			// left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			// left join ScrmISFDetails as SIF on POD.PoID=SIF.PIID
			// left join ScrmLinerMaster  SLM on SLM.LinerID=SIF.ShippingLineID			 
			// WHERE SSDB.TransportedDate!='' $DeliveryQry
			// and POD.Status not in (2,3,4)   $Query $SQuery  $SubQuery $PQuery order by SIF.EstimatedETA asc"; 




			$SqlPortWiseData = "SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmPortCities as SPCF on POD.FinalDestination=SPCF.PortId
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmISFDetails as SIF on POD.PoID=SIF.PIID
			left join ScrmLinerMaster  SLM on SLM.LinerID=SIF.ShippingLineID			 
			WHERE  $DateFilter and (SSDB.TransportedDate!='' AND SSDB.TransportedDate IS NOT NULL) $DeliveryQry and  (SCI.StockOutDate !='' AND  SCI.StockOutDate is not null)
					$DeliveredQuery
			and POD.Status not in (2,3,4)   $Query $SQuery  $SubQuery $PQuery order by SIF.EstimatedETA asc"; //uma 


			// echo print_r($SqlPortWiseData );
			// exit;



			$StmtPortWiseData = mysql_query($SqlPortWiseData, $this->db);

			$AryWholeData = array();
			$ArySortedData = array();
			while ($RowPortWiseData = mysql_fetch_array($StmtPortWiseData, MYSQL_ASSOC)) {
				$AryWholeData[] = $RowPortWiseData;
				if ($RowPortWiseData['EstimatedETA'] != '') {
					$ArySortedData[] = $RowPortWiseData;
				}
			}

			foreach ($AryWholeData as $RowPortWiseData) {
				if ($RowPortWiseData['EstimatedETA'] == '') {
					$ArySortedData[] = $RowPortWiseData;
				}
			}

			//while ($RowPortWiseData = mysql_fetch_array($StmtPortWiseData, MYSQL_ASSOC)) {
			foreach ($ArySortedData as $RowPortWiseData) {

				$POID = $RowPortWiseData['POID'];
				$EstimatedETA = $RowPortWiseData['EstimatedETA'];
				$Variety = $this->getVariety($POID);
				//$POID=1175;

				if ($RowPortWiseData['ReturnedDate'] != '') {
					$CurrentStatus = 'Returned';
					$StatusColour = '#90EE90';
				} else if ($RowPortWiseData['ClosedDate'] != '') {
					$CurrentStatus = 'Closed';
					$StatusColour = '#90EE90';
				} else if ($RowPortWiseData['DeliveredDate'] != '') {
					$CurrentStatus = $ClientId == 'DFLV3' && $RowPortWiseData['FDAExam'] == 'Yes' ? 'On Water' : 'Delivered';
					$StatusColour = '#FFFF8F';
				} else if ($RowPortWiseData['ShippedOn'] != '') {
					$CurrentStatus = 'On Water';
					$StatusColour = '#ADD8E6';
				} else if ($RowPortWiseData['TransportedDate'] != '') {
					$CurrentStatus = 'Transported';
					$StatusColour = '#A9A9A9';
				} else if ($RowPortWiseData['ProductionStartedDate'] != '') {
					$CurrentStatus = 'Production';
					$StatusColour = '#FA8072';
				} else {
					$CurrentStatus = 'Issued';
					$StatusColour = '#FFFFFF';
				}


				$ActualEtaDate = $this->getEtaFDDate($POID);
				if ($EstimatedETA == '') {
					$EstimatedETA = $this->getFormatedDatelines($ActualEtaDate);
				} else {
					$EstimatedETA = $this->getFormatedDatelines($EstimatedETA);
				}

				//$ArrayData[]='';

				$InvNo = $RowPortWiseData['CInvNo'];
				$InvDate = $RowPortWiseData['CInvDate'];
				$ExpID = $RowPortWiseData['ExporterID'];
				$ProcID = $RowPortWiseData['ProcessorID'];

				$ArrayData['CurrentStatus'] = $CurrentStatus;
				// $ArrayData['StatusColour'] = $StatusColour;
				$Variety_Brand = explode("$", $Variety);
				$ArrayData['Sno'] = ++$Sno;
				$ArrayData['POID'] = $RowPortWiseData['POID'];
				$ArrayData['CInvNo'] = $RowPortWiseData['CustomsInvNo'];
				$ArrayData['ConsigneeName'] = $RowPortWiseData['ConsigneeName'];
				$ArrayData['ConsigneeCode'] = $RowPortWiseData['ConsigneeCode'];
				$ArrayData['PoNO'] = $RowPortWiseData['PoNO'];
				$ArrayData['Product'] = $Variety_Brand[0];
				$ArrayData['Brand'] = $Variety_Brand[1];
				$ArrayData['ContainerNo'] = $RowPortWiseData['ContainerNo'];
				$ArrayData['PortOfDischarge'] = $RowPortWiseData['PortOfDischarge'];
				$ArrayData['FinalDestination'] = $RowPortWiseData['FinalDestination'];
				$ArrayData['ShipByDate'] = $this->getFormatedDatelines($RowPortWiseData['ShipByDate']);
				$ArrayData['ShippedOn'] = $this->getFormatedDatelines($RowPortWiseData['ShippedOn']);
				$ArrayData['EtaFD'] = $EstimatedETA;
				$ArrayData['GateOutDate'] = $this->getFormatedDatelines($RowPortWiseData['GetOutFull']);
				$ArrayData['EntryNumber'] = $RowPortWiseData['FDAEntryNumber'];
				$ArrayData['Liner'] = $RowPortWiseData['LinerName'];
				$ArrayData['DeliveryTerms'] = $RowPortWiseData['DeliveryTerms'];
				$ArrayData['TransitDays'] = $RowPortWiseData['TransitDays'];
				$ArrayData['TransitDaysPerLiner'] = $RowPortWiseData['TransitDaysPerLiner'];
				$ArrayData['EmptyReturnedDate'] = $this->getFormatedDatelines($RowPortWiseData['EmptyReturn']);

				$SqlTranshipmentPortsList = "SELECT `FromDate` FROM `ScrmTranshipmentPortsList` WHERE `PIID` = '$POID' ORDER BY `TspID` DESC LIMIT 1";
				$StmtTranshipmentPorts = mysql_query($SqlTranshipmentPortsList, $this->db);
				$RowTranshipmentPorts = mysql_fetch_array($StmtTranshipmentPorts, MYSQL_ASSOC);
				$DepartureDate = $RowTranshipmentPorts['FromDate'];
				// $ArrayData['DepartureDate'] = $DepartureDate;
				$ArrayData['DepartureDate'] = $this->getFormatedDatelines($DepartureDate);


				//$ArrayData['CInvNo'] = $this->getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID);
				//$AryRowPortWiseData[] = $RowPortWiseData;
				$AryRowPortWiseData[] = $ArrayData;
			}


			//$data['Link']="AddCollection?PIID=";
			$data['Data'] = $AryRowPortWiseData;

			if ($StmtPortWiseData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getShipingBillList()
	{
		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$BillsType = $IpData['BillsType'];
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$Processor = $IpData['Processor'];

		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';

		if ($Exporter != "") {
			$Query = ' and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Processor != "") {
			$Query .= ' and POD.ProcessorID in (' . $Processor . ')';
		}
		if ($Consignee != "") {
			$SubQuery = ' and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = ' and POD.DestinationCountry=' . $Country;
		}

		if ($BillsType == "0") {
			$IQuery = ' SCI.PiID not in (SELECT InvID FROM `ScrmShippingBillDetails`) ';
		} else if ($BillsType == "1") {
			$IQuery = "SCI.CInvDate $DateFilter and SCI.PiID  in (SELECT InvID FROM `ScrmShippingBillDetails`)";
		}

		if ($Year != '') {
			$Error = false;
		}


		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCD.ConsigneeName,
		SCI.CInvNo,
		SCOM.ShippingBillNo,
		SCI.CInvDate';

			$SqlShipingBills = "SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmShippingBillDetails as SCOM on POD.PoID=SCOM.InvID 
			WHERE $IQuery and POD.Status not in (3,7)   
			$Query $SQuery  $SubQuery order by SCOM.InvID desc";

			$StmtShipingBills = mysql_query($SqlShipingBills, $this->db);

			while ($RowShipingBill = mysql_fetch_array($StmtShipingBills, MYSQL_ASSOC)) {
				$InvNo = $RowShipingBill['CInvNo'];
				$InvDate = $RowShipingBill['CInvDate'];
				$ExpID = $RowShipingBill['ExporterID'];
				$ProcID = $RowShipingBill['ProcessorID'];
				$RowShipingBill['Sno'] = ++$Sno;
				$RowShipingBill['CInvNo'] = $this->getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID);
				$AryRowShipingBill[] = $RowShipingBill;
			}

			$data['Error'] = null;
			$data['Msg'] = "Success";
			$data['Link'] = "ShippingBillForm?PoID=";
			$data['Data'] = $AryRowShipingBill;

			if ($StmtShipingBills) {
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getFDAList()
	{
		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$BillsType = $IpData['BillsType'];
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];

		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';
		$DateFilter = $this->getFinancialYear($Year);


		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}

		if ($BillsType == "0") {
			$IQuery = "  SCI.PiID not in  (SELECT PIID FROM `ScrmISFDetails`) or SCI.PiID not in (SELECT PIID FROM `ScrmISFDetails` where EmptyReturn!='')";
		} else if ($BillsType == "1") {
			$IQuery = "  SCI.PiID  in (SELECT PIID FROM `ScrmISFDetails` where EmptyReturn!='')";
		}

		if ($Year != '') {
			$Error = false;
		}


		if (!$Error) {
			$SqlFields = 'POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCD.ConsigneeName,
		SCI.CInvNo,
		SCOM.ShippingBillNo,
		SCI.CInvDate';

			$SqlFDAList = "SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmShippingBillDetails as SCOM on POD.PoID=SCOM.InvID 
			WHERE $IQuery and POD.Status not in (3,7)  and SCI.CInvDate $DateFilter 
			$Query $SQuery  $SubQuery order by SCOM.InvID desc";

			$StmtFDAList = mysql_query($SqlFDAList, $this->db);

			while ($RowFDAList = mysql_fetch_array($StmtFDAList, MYSQL_ASSOC)) {
				$InvNo = $RowFDAList['CInvNo'];
				$InvDate = $RowFDAList['CInvDate'];
				$ExpID = $RowFDAList['ExporterID'];
				$ProcID = $RowFDAList['ProcessorID'];
				$RowFDAList['Sno'] = ++$Sno;
				$RowFDAList['CInvNo'] = $this->getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID);
				$AryRowFDAList[] = $RowFDAList;
			}

			$data['Error'] = null;
			$data['Msg'] = "Success";
			$data['Link'] = "AddFDADetails?PoID=";
			$data['Data'] = $AryRowFDAList;

			if ($StmtFDAList) {
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getShipBillDetails()
	{

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$PoID = $IpData['PoID'];

		$SqlFeilds = 'SPD.ExporterID,SPD.ProcessorID, SCI.CustomsInvNo as CInvNo,SCI.CInvDate,SCD.ConsigneeName,SPCL.Name AS LoadingPort,
		ISF.ContainerNo ,ISF.BLNo,
SPCD.Name AS DischargePort,SCL.name AS CountryName,SCI.Freight,SCI.TotalNetWt,
SCI.Duty,SPBD.ShippingBillNo,SPBD.ShippingBillDate,SPBD.LEODate,SPBD.SBFOBINR,
SPBD.ExchangeRate as SBExchangeRate,
SPBD.SBFOBUSD,
SPBD.SBFreight
';

		$SqlShippingBillDetails = "SELECT $SqlFeilds
FROM `ScrmPoData` SPD
left join ScrmCustomInvoices SCI on SCI.PiID=SPD.PoID
left join ScrmConsigneeDetails SCD on SCD.ConID=SPD.ConsigneeID
left join ScrmPortCities SPCD on SPCD.PortId=SPD.PortOfDischarge
left join ScrmPortCities SPCL on SPCL.PortId=SPD.PortofLoading
left join ScrmCountriesList SCL on SCL.id=SPD.DestinationCountry
left join ScrmShippingBillDetails SPBD on SPBD.InvID=SPD.PoID
left join ScrmISFDetails ISF on ISF.PIID=SPD.PoID
WHERE SPD.PoID=$PoID";

		$PaymentData = $this->getPaymentValues($PoID, '');

		$StmtShippingBillDetails = mysql_query($SqlShippingBillDetails, $this->db);
		$RowShippingBillDetails = mysql_fetch_array($StmtShippingBillDetails, MYSQL_ASSOC);

		$InvNo = $RowShippingBillDetails['CInvNo'];
		$InvDate = $RowShippingBillDetails['CInvDate'];
		$ExpID = $RowShippingBillDetails['ExporterID'];
		$ProcID = $RowShippingBillDetails['ProcessorID'];


		$RowShippingBillDetails['CInvNo'] = $InvNo;

		/*$RowShippingBillDetails['InvValueUSD']=$PaymentData['InvValueUSD'];
																		$RowShippingBillDetails['FOBValueINR']=$PaymentData['FOBValueINR'];*/

		$RowShippingBillDetails['InvValueUSD'] = $RowShippingBillDetails['SBFOBUSD'];
		$RowShippingBillDetails['FOBValueINR'] = number_format($RowShippingBillDetails['SBFOBINR'], 2);
		$RowShippingBillDetails['TotalMcs'] = $PaymentData['TotalMcs'];



		if ($StmtShippingBillDetails) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$data['Data'] = $RowShippingBillDetails;
			$data['FDADetails'] = $this->getFDADetails($PoID);

			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}



	function postShippingBillDetails()
	{
		//echo "hi"; exit;
		//global $iconn;
		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$InvID = $IpData['InvID'];
		$ShippingBillNo = $IpData['ShippingBillNo'];
		$ShippingBillDate = $IpData['ShippingBillDate'];
		$LEODate = $IpData['LEODate'];
		$ExchangeRate = $IpData['ExchangeRate'];
		$SBFOBUSD = $IpData['SBFOBUSD'];
		$SBFOBINR = $IpData['SBFOBINR'];
		$SBFreight = $IpData['SBFreight'];
		$PostedUserId = $IpData['PostedUserId'];
		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = date('Y-m-d H:i:s');

		$SqlCount = "SELECT * FROM `ScrmShippingBillDetails` WHERE InvID='$InvID'";
		/*$StmtCount=$iconn->query($SqlCount);
																		$RowCount=$StmtCount->fetch_array();*/

		$StmtCount = mysql_query($SqlCount, $this->db);
		//var_dump($StmtCount);
		$RowCount = mysql_fetch_array($StmtCount, MYSQL_ASSOC);
		$IsEdit = $RowCount['IsEdit'] + 1;
		$Count = mysql_num_rows($StmtCount);
		if ($Count == 0) {

			$SqlInsert = "INSERT INTO `ScrmShippingBillDetails`(`InvID`, `ShippingBillNo`, `ShippingBillDate`, `LEODate`, `ExchangeRate`, `SBFOBUSD`, `SBFOBINR`, `SBFreight`, `PostedUserId`, `PostedDateTime`, `IsEdit`) VALUES('$InvID','$ShippingBillNo','$ShippingBillDate','$LEODate','$ExchangeRate'
,'$SBFOBUSD','$SBFOBINR','$SBFreight','$PostedUserId','$PostedDateTime','0') ";
		} else {
			$SqlInsert = " UPDATE `ScrmShippingBillDetails` SET `ShippingBillNo`='$ShippingBillNo',`ShippingBillDate`='$ShippingBillDate',`LEODate`='$LEODate',`ExchangeRate`='$ExchangeRate',`SBFOBUSD`='$SBFOBUSD',`SBFOBINR`='$SBFOBINR',`SBFreight`='$SBFreight',`PostedUserId`='$PostedUserId',`PostedDateTime`='$PostedDateTime',`IsEdit`='$IsEdit'
		WHERE `InvID`='$InvID'";
		}
		//echo $SqlInsert; exit;
		$StmtInsert = mysql_query($SqlInsert, $this->db);
		if ($StmtInsert) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Post Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getPendingInsuranceList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$BillsType = $IpData['BillsType'];
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$InsuranceID = $IpData['InsuranceID'];

		$Query = '';
		$SubQuery = '';
		$SQuery = '';
		$IQuery = '';
		$PQuery = '';

		if ($Exporter != "") {
			$Query = 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SubQuery = 'and POD.ConsigneeID=' . $Consignee;
		}
		$DateFilter = $this->getFinancialYear($Year);


		if ($InsuranceID != "") {
			$PQuery = 'where InsuranceID=' . $InsuranceID;
		}

		$SqlPoIds = "SELECT PoIDs FROM `ScrmInsuranceData` $PQuery";
		$StmtPoIds = mysql_query($SqlPoIds, $this->db);
		while ($RowPoIds = mysql_fetch_array($StmtPoIds, MYSQL_ASSOC)) {
			$ArrayPoId[] = $RowPoIds['PoIDs'];
			$PoIDs = implode(',', $ArrayPoId);
		}

		//echo $PoIDs; exit;

		if ($Country != "") {
			$SQuery = 'and POD.DestinationCountry=' . $Country;
		}
		if ($BillsType == "0") {
			if ($PoIDs == '') {
				$IQuery = "";
			} else {
				$IQuery = " and SCI.PiID not in ($PoIDs)";
			}
		} else if ($BillsType == "1") {
			$IQuery = " and SCI.PiID  in ($PoIDs)";
		}

		if ($Year != '') {
			$Query .= "and SCI.CInvDate " . $DateFilter;
		}


		//if (!$Error) {
		$SqlFields = 'POD.PoID,
		SCI.CustomsInvNo,
		SCI.ContainerNo,
		SIFD.ShippingOnboardDt,
		SPCL.Name as PortofLoading,
		SPCD.Name as PortOfDischarge,
		SCL.name as DestinationCountry,
		POD.ExporterID,
		POD.ProcessorID,
		POD.QtyUnit,
		POD.PoNO,
		SCI.CInvDate

		,ICN.CertificationNumber,
		ICN.ID AS CertificationID
		';

		$SqlInsuranceList = "SELECT $SqlFields FROM ScrmCustomInvoices as SCI 
			left join ScrmPoData as POD on SCI.PiID=POD.PoID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
			left join ScrmShipmentDetailsBreif as SCDB on POD.PoID=SCDB.PiID 
			left join ScrmPortCities as SPCL on POD.PortofLoading=SPCL.PortId 
			left join ScrmPortCities as SPCD on POD.PortOfDischarge=SPCD.PortId 
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
			LEFT JOIN InsuranceCertifactionNumbers ICN ON ICN.InvoiceID = POD.PoID
			WHERE  POD.Status not in (3,7) and SCDB.TransportedDate!=''  $IQuery
			$Query $SQuery  $SubQuery order by CommercialID desc";

		$StmtInsuranceList = mysql_query($SqlInsuranceList, $this->db);
		$TotalInvoiceValuewithPercentage = 0;
		$TotalInvValueUSD = 0;

		while ($RowInsuranceList = mysql_fetch_array($StmtInsuranceList, MYSQL_ASSOC)) {
			$PoID = $RowInsuranceList['PoID'];
			$InvNo = $RowInsuranceList['CustomsInvNo'];
			$InvDate = $RowInsuranceList['CInvDate'];
			$ExpID = $RowInsuranceList['ExporterID'];
			$ProcID = $RowInsuranceList['ProcessorID'];
			$PortofLoading = $RowInsuranceList['PortofLoading'];
			$QtyUnit = $RowInsuranceList['QtyUnit'];

			$SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 
				FromTOPID='$PortofLoading' and PIID='$PoID'";

			$StmtOrgVessel = mysql_query($SqlOrgVessel, $this->db);
			$RowOrgVessel = mysql_fetch_array($StmtOrgVessel, MYSQL_ASSOC);



			$VesselName = $RowOrgVessel['VesselName'] . $RowOrgVessel['VOYNO'];


			$RowInsuranceList['Sno'] = ++$Sno;
			$ArrayPaymentValues = $this->getPaymentValues($PoID, '');

			$InvValueUSD = $ArrayPaymentValues['InvValueUSD'];
			$TenPercentofInvoiceValue = ($ArrayPaymentValues['InvValueUSD'] * 10) / 100;
			$InvoiceValuewithPercentage = $TenPercentofInvoiceValue + $ArrayPaymentValues['InvValueUSD'];
			$TotalInvValueUSD += $InvValueUSD;
			$TotalInvoiceValuewithPercentage += $InvoiceValuewithPercentage;


			$RowInsuranceList['CInvNo'] = $InvNo;
			$RowInsuranceList['TotalInvoiceValue'] = number_format($InvValueUSD, 2);
			$RowInsuranceList['InvoiceValuewithPercentage'] = number_format($InvoiceValuewithPercentage, 2);
			$RowInsuranceList['ShippingOnboardDt'] = $this->getFormatedDatelines($RowInsuranceList['ShippingOnboardDt']);
			$RowInsuranceList['VesselName'] = $VesselName;
			$RowInsuranceList['CInvDate'] = $InvDate;

			$RowInsuranceList['Link'] = "Reports/DocCustomInv.php?PType=" . $QtyUnit . "&id=" . $PoID;
			if ($ArrayPaymentValues['HasSauce'] == 'Yes') {
				$RowInsuranceList['Link'] = "Reports/DocCustomInvSauce.php?PType=" . $QtyUnit . "&id=" . $PoID;
			}
			$AryRowInsuranceList[] = $RowInsuranceList;
		}


		$ArrayPendingInsuranceTotals['TotalInvoices'] = $Sno;
		$ArrayPendingInsuranceTotals['TotalInvoiceValueUSD'] = number_format($TotalInvValueUSD, 2);
		$ArrayPendingInsuranceTotals['TotalInvoiceValuewithPercentage'] = number_format($TotalInvoiceValuewithPercentage, 2);
		//$ArrayPendingPaymentTotals['TotalMcs']=$TotalMcs;

		$data['Totals'] = $ArrayPendingInsuranceTotals;
		$data['Data'] = $AryRowInsuranceList;

		if ($StmtInsuranceList) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}

		//}
	}

	function createInsuranceTemplate()
	{
		//echo "hi"; exit;
		//global $iconn;
		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$PoIDs = $IpData['PoIDs'];
		//$InsuranceNo=$IpData['InsuranceNo'];
		$InsuranceType = $IpData['InsuranceType'];
		$RejectionValue = $IpData['RejectionValue'];
		$TransitValue = $IpData['TransitValue'];
		$PostedUserId = $IpData['PostedUserId'];
		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = $datetime->format('Y-m-d H:i:s');

		$SqlInsuranceAvailableCount = "SELECT Count(*) as AvailableCount FROM `ScrmInsuranceData`  where PoIDs='$PoIDs'";
		$StmtInsuranceAvailableCount = mysql_query($SqlInsuranceAvailableCount, $this->db);
		$RowInsuranceAvailableCount = mysql_fetch_array($StmtInsuranceAvailableCount, MYSQL_ASSOC);
		$AvailableCount = $RowInsuranceAvailableCount['AvailableCount'];

		$SqlInsuranceCount = "SELECT MAX(InsuranceNo) AS InsuranceNo FROM ScrmInsuranceData where InsuranceType='$InsuranceType'";
		$StmtInsuranceCount = mysql_query($SqlInsuranceCount, $this->db);
		$RowInsuranceCount = mysql_fetch_array($StmtInsuranceCount, MYSQL_ASSOC);
		$Count = $RowInsuranceCount['InsuranceNo'];
		$InsuranceNo = $Count + 1;
		$InsuranceNo = str_pad($InsuranceNo, 3, '0', STR_PAD_LEFT);

		$InsuranceFullNo = $InsuranceType . $InsuranceNo;

		// For all CLients (1)rejection->0.4 and (0)transit->0.08
		// For CAPL (1)rejection->0.5 and (0)transit->0.1

		if ($InsuranceType == 'REJECT') {
			$InsuranceTypePercentage = $RejectionValue;
		} else {
			$InsuranceTypePercentage = $TransitValue;
		}

		$ExplodePoIds = explode(',', $PoIDs);
		$ArrayCount = count($ExplodePoIds);

		for ($i = 0; $i < $ArrayCount; $i++) {
			$PoID = $ExplodePoIds[$i];
			$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
			$InvoiceValue = $ArrayPaymentValues['InvValueUSD'];
			//$InvoiceValue=105000;
			$ArrayInvoicevaluewithInsPercentage[] = (($InvoiceValue * 10) / 100) + $InvoiceValue; //Insurancepercentage=10%;
			$ExcessValue = implode(',', $ArrayInvoicevaluewithInsPercentage);
		}

		if ($AvailableCount == 0) {
			$SqlInsert = "INSERT INTO `ScrmInsuranceData`(`PoIDs`, `InsuranceNo`,`InsuranceFullNo`, `InsuranceType`, `InsuranceTypePercentage`, `ExcessValue`, `PostedUserID`, `PostedTime`) VALUES ('$PoIDs','$InsuranceNo','$InsuranceFullNo','$InsuranceType','$InsuranceTypePercentage'
,'$ExcessValue','$PostedUserId','$PostedDateTime') ";

			//}

			$StmtInsert = mysql_query($SqlInsert, $this->db);
			if ($StmtInsert) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Post Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
			// }

		} else {
			$data['Error'] = "Data Already Exists With this POIds";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getInsuranceTemplatesList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$SqlFields = '
		 SID.InsuranceID,
		 SID.PoIDs,
		SID.InsuranceFullNo as InsuranceNo,
		SID.PostedTime as GeneratedTime';

		// $SqlInsuranceTemplatesList = "SELECT $SqlFields FROM  ScrmInsuranceData as SID
		// 	 order by SID.InsuranceID desc";

		/* $SqlInsuranceTemplatesList = "SELECT $SqlFields FROM  ScrmInsuranceData as SID
			 WHERE SID.InsuranceID not in (SELECT InsuranceID FROM `ScrmInsurancePayments`)
			 order by SID.InsuranceID desc"; */
		$SqlInsuranceTemplatesList = "SELECT  $SqlFields
										FROM ScrmInsuranceData AS SID
										WHERE NOT EXISTS (
											SELECT 1
											FROM ScrmInsurancePayments SIP
											WHERE FIND_IN_SET(SID.InsuranceID, SIP.InsuranceID)
										)
										ORDER BY SID.InsuranceID DESC";

		$StmtInsuranceTemplatesList = mysql_query($SqlInsuranceTemplatesList, $this->db);

		while ($RowInsuranceTemplatesList = mysql_fetch_array($StmtInsuranceTemplatesList, MYSQL_ASSOC)) {
			$PoIDs = $RowInsuranceTemplatesList['PoIDs'];
			$SqlCustomInvoice = "SELECT CustomsInvNo FROM `ScrmCustomInvoices` 
			where PiID in ($PoIDs)";
			unset($ArrayInvoiceNo);
			$StmtCustomInvoice = mysql_query($SqlCustomInvoice, $this->db);
			while ($RowCustomInvoice = mysql_fetch_array($StmtCustomInvoice, MYSQL_ASSOC)) {
				$ArrayInvoiceNo[] = $RowCustomInvoice['CustomsInvNo'];
				$InvoiceNo = implode(',', $ArrayInvoiceNo);
			}


			$InvNo = $RowInsuranceTemplatesList['CustomsInvNo'];
			$RowInsuranceTemplatesList['Sno'] = ++$Sno;
			$RowInsuranceTemplatesList['InvoiceNo'] = $InvoiceNo;
			$RowInsuranceTemplatesList['GeneratedTime'] = date('d-m-Y H:i:s', strtotime($RowInsuranceTemplatesList['GeneratedTime']));

			$AryRowInsuranceTemplatesList[] = $RowInsuranceTemplatesList;
		}



		$data['InsuranceTemplatesList'] = $AryRowInsuranceTemplatesList;

		if ($StmtInsuranceTemplatesList) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}

		//}
	}


	function getInsuranceTemplateInvoicesList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$InsuranceID = $IpData['InsuranceID'];

		$SqlPoIds = "SELECT PoIDs FROM `ScrmInsuranceData` where InsuranceID='$InsuranceID' ";
		$StmtPoIds = mysql_query($SqlPoIds, $this->db);
		while ($RowPoIds = mysql_fetch_array($StmtPoIds, MYSQL_ASSOC)) {
			$ArrayPoId[] = $RowPoIds['PoIDs'];
			$PoIDs = implode(',', $ArrayPoId);
		}

		$SqlFields = '
		 SCI.PiID,
		 SCI.CustomsInvNo as InvoiceNo';

		$SqlInsuranceTemplateInvoicesList = "SELECT $SqlFields FROM   ScrmCustomInvoices as SCI where  SCI.PiID in ($PoIDs)";

		$StmtInsuranceTemplateInvoicesList = mysql_query($SqlInsuranceTemplateInvoicesList, $this->db);

		while ($RowInsuranceTemplateInvoicesList = mysql_fetch_array($StmtInsuranceTemplateInvoicesList, MYSQL_ASSOC)) {

			$RowInsuranceTemplateInvoicesList['Sno'] = ++$Sno;
			$AryRowInsuranceTemplateInvoicesList[] = $RowInsuranceTemplateInvoicesList;
		}



		$data['InsuranceTemplateInvoicesList'] = $AryRowInsuranceTemplateInvoicesList;

		if ($StmtInsuranceTemplateInvoicesList) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}

		//}
	}

	function createPaymentsTemplate()
	{
		//echo "hi"; exit;
		//global $iconn;
		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$InsuranceIDs = $IpData['InsuranceIDs'];
		$PostedUserId = $IpData['PostedUserId'];
		$Timezone = new DateTimeZone('Asia/Kolkata');
		$Datetime = new DateTime('now', $Timezone);
		$PostedDateTime = $Datetime->format('Y-m-d H:i:s');
		//duplicate insurance add case is not validated 

		if ($InsuranceIDs != '') {
			$SqlCheckAvailable = "SELECT Count(*) AS PaymentCount FROM ScrmInsurancePayments where InsuranceID in ($InsuranceIDs) ";
			$StmtCheckAvailable = mysql_query($SqlCheckAvailable, $this->db);
			$RowCheckAvailable = mysql_fetch_array($StmtCheckAvailable, MYSQL_ASSOC);
			$PaymentCount = $RowCheckAvailable['PaymentCount'];

			if ($PaymentCount == 0) {
				$SqlInsurancePaymentCount = "SELECT MAX(PaymentNo) AS PaymentNo FROM ScrmInsurancePayments ";
				$StmtInsurancePayment = mysql_query($SqlInsurancePaymentCount, $this->db);
				$RowInsurancePayment = mysql_fetch_array($StmtInsurancePayment, MYSQL_ASSOC);
				$Count = $RowInsurancePayment['PaymentNo'];
				$PaymentNo = $Count + 1;
				$PaymentNo = str_pad($PaymentNo, 3, '0', STR_PAD_LEFT);

				$PaymentFullNo = 'PAYMENT' . $PaymentNo;

				//if ($InsuranceIDs != '') {
				$SqlInsert = "INSERT INTO `ScrmInsurancePayments`(`InsuranceID`,`PaymentNo`, `PaymentFullNo`, `PostedUserID`, `PostedDateTime`) VALUES ('$InsuranceIDs','$PaymentNo','$PaymentFullNo','$PostedUserId','$PostedDateTime') ";
				$StmtInsert = mysql_query($SqlInsert, $this->db);

				if ($StmtInsert) {
					$data['Error'] = null;
					$data['Msg'] = "Success";
					$records = array('Data' => $data);
					$this->response($this->json($records), 200);
				}
			} else {
				$data['Error'] = "Id already Exists";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		} else {
			$data['Error'] = "Please Select ID";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}


	function PostPaymentsInsuranceData()
	{ //to update data of payments insurance 

		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$PostedUserId = $IpData['PostedUserId'];
		$InsurancePaymentID = $IpData['InsurancePaymentID'];
		$PaymentDate = $IpData['PaymentDate'];
		$ReferenceNo = $IpData['ReferenceNo'];
		$OurReferenceNo = $IpData['OurReferenceNo'];
		$PaymentInsExchangeRate = $IpData['PaymentInsExchangeRate'];
		$Remarks = $IpData['Remarks'];
		$Amount = $IpData['Amount'];
		$Timezone = new DateTimeZone('Asia/Kolkata');
		$Datetime = new DateTime('now', $Timezone);
		$PostedDateTime = $Datetime->format('Y-m-d H:i:s');

		$DebitNote = $IpData['DebitNote'];


		$SqlInsert = " UPDATE `ScrmInsurancePayments` SET 
		`ReferenceNo`='$ReferenceNo',`OurReferenceNo`='$OurReferenceNo',`Amount`='$Amount',`PaymentDate`='$PaymentDate',`PaymentInsExchangeRate`='$PaymentInsExchangeRate',`Remarks`='$Remarks',`PostedUserID`='$PostedUserId',`PostedDateTime`='$PostedDateTime' 
		,`DebitNote` = '$DebitNote' 
		WHERE `InsurancePaymentID`='$InsurancePaymentID'";

		$StmtInsert = mysql_query($SqlInsert, $this->db);
		if ($StmtInsert) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Post Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function DeleteInsurancePaymentsTemplate()
	{

		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$InsurancePaymentID = $IpData['InsurancePaymentID'];
		$PostedUserId = $IpData['PostedUserId'];
		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = date('Y-m-d H:i:s');


		$SqlDelete = "DELETE FROM `ScrmInsurancePayments` WHERE InsurancePaymentID='$InsurancePaymentID'";

		$StmtDelete = mysql_query($SqlDelete, $this->db);
		if ($StmtDelete) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Delete Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}
	function getInsurancePaymentsList()
	{

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$Year = $IpData['Year'];
		if ($Year != '') {
			$DateStart = $Year . '-04-01';
			$DateEnd = ($Year + 1) . '-03-31';
			//$InsuranceDateFilter = " where PaymentDate between '$DateStart' and '$DateEnd'";
			$InsuranceDateFilter = " where (PaymentDate between '$DateStart' and '$DateEnd' OR  DATE(PostedDateTime)  BETWEEN '$DateStart' and '$DateEnd')";
		}

		$SqlFields = '
		InsurancePaymentID,
		PaymentFullNo as PaymentFullNo,
		PostedDateTime as GeneratedTime,
		OurReferenceNo';

		$SqlPaymentTemplatesList = "SELECT $SqlFields FROM `ScrmInsurancePayments` $InsuranceDateFilter Order by PaymentNo asc";

		$StmtPaymentTemplatesList = mysql_query($SqlPaymentTemplatesList, $this->db);

		while ($RowPaymentTemplatesList = mysql_fetch_array($StmtPaymentTemplatesList, MYSQL_ASSOC)) {
			$GeneratedTime = $this->getFormatedDatewithTime($RowPaymentTemplatesList['GeneratedTime']);
			$RowPaymentTemplatesList['Sno'] = ++$Sno;
			$RowPaymentTemplatesList['GeneratedTime'] = $GeneratedTime;
			$AryRowPaymentTemplatesList[] = $RowPaymentTemplatesList;
		}
		return $AryRowPaymentTemplatesList;
	}


	function getPendingInsurancePaymentsList()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$Year = $IpData['Year'];
		$Query = '';
		if ($Year != '') {
			$DateStart = $Year . '-04-01';
			$DateEnd = ($Year + 1) . '-03-31';
			//$InsuranceDateFilter = " where SIP.PaymentDate between '$DateStart' and '$DateEnd'";
			$InsuranceDateFilter = " where (SIP.PaymentDate between '$DateStart' and '$DateEnd' OR  DATE(PostedDateTime)  BETWEEN '$DateStart' and '$DateEnd')";
			$InsuranceDateFilter1 = " AND DATE(SID.`PostedTime`)  BETWEEN '$DateStart' and '$DateEnd'";
			$Error = false;
		}


		if (!$Error) {

			$SqlInsuranceIDs = "SELECT InsuranceID FROM `ScrmInsurancePayments` SIP $InsuranceDateFilter";
			$StmtInsuranceIDs = mysql_query($SqlInsuranceIDs, $this->db);
			while ($RowInsuranceIDs = mysql_fetch_array($StmtInsuranceIDs, MYSQL_ASSOC)) {
				$ArrayInsuranceID[] = $RowInsuranceIDs['InsuranceID'];
				$InsuranceID = implode(',', $ArrayInsuranceID);
			}
			if ($InsuranceID != '') {
				$Query = "AND SID.InsuranceID NOT in ($InsuranceID) ";
			}
			$SqlFields = '
				SID.InsuranceID,
				POD.PoID,
				SID.PoIDs,
				SID.InsuranceFullNo as InsuranceNo';

			$SqlPendingInsuranceList = "SELECT $SqlFields FROM  ScrmInsuranceData as SID
			left join ScrmPoData as POD on POD.PoID in(SID.PoIDs)
			left join ScrmInsurancePayments as SIP on SIP.InsuranceID =SID.InsuranceID
			WHERE 1
			$Query $InsuranceDateFilter1 order by SID.InsuranceID desc";

			$StmtPendingInsuranceList = mysql_query($SqlPendingInsuranceList, $this->db);

			while ($RowPendingInsuranceList = mysql_fetch_array($StmtPendingInsuranceList, MYSQL_ASSOC)) {
				$PoIDs = $RowPendingInsuranceList['PoIDs'];
				$SqlCustomInvoice = "SELECT CustomsInvNo FROM `ScrmCustomInvoices` 
			where PiID in ($PoIDs)";
				$StmtCustomInvoice = mysql_query($SqlCustomInvoice, $this->db);
				unset($ArrayInvoiceNo);
				while ($RowCustomInvoice = mysql_fetch_array($StmtCustomInvoice, MYSQL_ASSOC)) {
					$ArrayInvoiceNo[] = $RowCustomInvoice['CustomsInvNo'];
					$InvoiceNo = implode(' , ', $ArrayInvoiceNo);
				}


				$RowPendingInsuranceList['Sno'] = ++$Sno;
				$RowPendingInsuranceList['InvoiceNo'] = $InvoiceNo;
				//$ArrayPaymentValues = $this->getPaymentValues($PoID);
				$AryRowPendingInsuranceList[] = $RowPendingInsuranceList;
			}

			$data['PendingPaymentsInsuranceList'] = $AryRowPendingInsuranceList;
			$data['PaymentsInsuranceTemplatesList'] = $this->getInsurancePaymentsList();

			if ($StmtPendingInsuranceList) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}
	function getInsurancePaymentData()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$InsurancePaymentID = $IpData['InsurancePaymentID'];
		$Hide10PercentColumn = !empty($IpData['Hide10PercentColumn']) ? $IpData['Hide10PercentColumn'] : '';

		$SqlInsPaymentFields = 'InsuranceID,
PaymentFullNo as PaymentNo,
ReferenceNo,
OurReferenceNo,
PaymentDate,PostedDateTime,
Remarks,
PaymentInsExchangeRate as ExchangeRate
,DebitNote
';


		$SqlInsuranceIDs = "SELECT $SqlInsPaymentFields  FROM `ScrmInsurancePayments` where InsurancePaymentID='$InsurancePaymentID'";
		$StmtInsuranceIDs = mysql_query($SqlInsuranceIDs, $this->db);
		$RowInsuranceIDs = mysql_fetch_array($StmtInsuranceIDs, MYSQL_ASSOC);
		$InsuranceIDs = $RowInsuranceIDs['InsuranceID'];
		$PaymentNo = $RowInsuranceIDs['PaymentNo'];

		$SqlPoIDs = "SELECT PoIDs,InsuranceFullNo as InsuranceNo,InsuranceTypePercentage,ExcessValue FROM `ScrmInsuranceData` where InsuranceID in ($InsuranceIDs)";
		$StmtPoIDs = mysql_query($SqlPoIDs, $this->db);
		unset($ArrayPoIDs);
		$TotalPremiumValue = 0;
		$TotalInvoiceValuewithPercentage = 0;
		while ($RowPoIDs = mysql_fetch_array($StmtPoIDs, MYSQL_ASSOC)) {
			$PoIDs = $RowPoIDs['PoIDs'];
			$ExcessValue = explode(',', $RowPoIDs['ExcessValue']);
			//$PoIDs=implode(',',$ArrayPoIDs);

			//}

			$SqlFields = 'POD.PoID,
		SCI.CustomsInvNo as CInvNo,
		SCI.ContainerNo,		
		SIFD.ShippingOnboardDt,
		SPCL.Name as POL,
		SPCD.Name as POD,
		SCD.name as DestinationCountry,
		SCL.name as PortofLoadingCountry,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCI.CInvDate,
		SCOD.ConsigneeName
		,ICN.CertificationNumber
		';

			$SqlPoDetails = "SELECT $SqlFields FROM ScrmCustomInvoices as SCI 
			left join ScrmPoData as POD on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCOD on SCOD.ConID = POD.ConsigneeID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmPortCities as SPCL on POD.PortofLoading=SPCL.PortId 
			left join ScrmPortCities as SPCD on POD.PortOfDischarge=SPCD.PortId 
			left join ScrmCountriesList as SCD on POD.DestinationCountry=SCD.id 
			left join ScrmCountriesList as SCL on SPCL.Country=SCL.id 
			LEFT JOIN InsuranceCertifactionNumbers ICN ON ICN.InvoiceID = POD.PoID
			WHERE POD.PoID in ($PoIDs)  order by PoID asc";

			$StmtPoDetails = mysql_query($SqlPoDetails, $this->db);


			$index = 0;
			while ($RowPoDetails = mysql_fetch_array($StmtPoDetails, MYSQL_ASSOC)) {
				$PoID = $RowPoDetails['PoID'];
				$InsuranceNo = $RowPoIDs['InsuranceNo'];
				$POL = $RowPoDetails['POL'];
				$POD = $RowPoDetails['POD'];
				$DestinationCountry = $RowPoDetails['DestinationCountry'];
				$PortofLoadingCountry = $RowPoDetails['PortofLoadingCountry'];
				$ShippingOnboardDt = $this->getformateddateLines($RowPoDetails['ShippingOnboardDt']);

				$PortofDischarge = $POD . ',' . $DestinationCountry;
				$PortofLoading = $POL . ',' . $PortofLoadingCountry;
				$InsuranceTypePercentage = $RowPoIDs['InsuranceTypePercentage'];


				$SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 
				FromTOPID='$POL' and PIID='$PoID'";
				//exit;
				$StmtOrgVessel = mysql_query($SqlOrgVessel, $this->db);
				$RowOrgVessel = mysql_fetch_array($StmtOrgVessel, MYSQL_ASSOC);

				$OrgVesselName = $RowOrgVessel['VesselName'];
				$OrgVOYNO = $RowOrgVessel['VOYNO'];

				$VesselName = $OrgVesselName . '  ' . $OrgVOYNO;

				$RowPoDetails['Sno'] = ++$Sno;
				$RowPoDetails['PaymentNo'] = $PaymentNo;
				$RowPoDetails['InsuranceNo'] = $InsuranceNo;
				$RowPoDetails['ConsigneeName'] = $RowPoDetails['ConsigneeName'];
				$RowPoDetails['VesselName'] = $VesselName;
				$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
				$TotalInvoiceValue = $ArrayPaymentValues['InvValueUSD'];
				//$InvoiceValuewithPercentage = ($TotalInvoiceValue * 10) / 100; //10 is static
				//$InvoiceValuewithPercentage = $ExcessValue[$index];

				$InvoiceValuewithPercentage = (($TotalInvoiceValue * 10) / 100) + $TotalInvoiceValue;

				//$PremiumValue = ($TotalInvoiceValue * $InsuranceTypePercentage) / 100;
				$dateFilter = $RowPoDetails['CInvDate'];
				//$dateFilter = !empty($RowInsuranceIDs['PaymentDate'])?$RowInsuranceIDs['PaymentDate']:$RowInsuranceIDs['PostedDateTime'];

				//$year = date('Y', strtotime($dateFilter));
				$RowPoDetails['CInvDate'] = $RowPoDetails['CInvDate']; //
				if (!empty($Hide10PercentColumn) && $Hide10PercentColumn != '0000-00-00' && $dateFilter >= $Hide10PercentColumn) {
					$PremiumValue = ($TotalInvoiceValue * $InsuranceTypePercentage) / 100;
				} else {
					$PremiumValue = ($InvoiceValuewithPercentage * $InsuranceTypePercentage) / 100;
				}
				$TotalPremiumValue += $PremiumValue;
				$TotalInvoiceValuewithPercentage += $InvoiceValuewithPercentage;

				$RowPoDetails['TotalInvoiceValue'] = number_format($TotalInvoiceValue, 2);
				$RowPoDetails['PremiumValue'] = number_format($PremiumValue, 2);
				$RowPoDetails['PortofDischarge'] = $PortofDischarge;
				$RowPoDetails['PortofLoading'] = $PortofLoading;
				$RowPoDetails['ShippingOnboardDt'] = $ShippingOnboardDt;
				/*$RowPoDetails['InvoiceValuewithPercentage']=$InvoiceValuewithPercentage;*/
				//$RowPoDetails['ExtraInvoiceValue']=$ExcessValue[$index]; 
				//$RowPoDetails['InvoiceValuewithPercentage'] = number_format($ExcessValue[$index], 2);
				// $InvoiceValue = $ArrayPaymentValues['InvValueUSD'];
				// $InvoiceValuewithPercentage = (($InvoiceValue * 10) / 100) + $InvoiceValue;
				$RowPoDetails['InvoiceValuewithPercentage'] = number_format($InvoiceValuewithPercentage, 2);




				$AryRowPoDetails[] = $RowPoDetails;

				$index++;
			}
			$RowPaymentDetails['ReferenceNo'] = $RowInsuranceIDs['ReferenceNo'];
			$RowPaymentDetails['OurReferenceNo'] = $RowInsuranceIDs['OurReferenceNo'];
			$RowPaymentDetails['PaymentDate'] = $RowInsuranceIDs['PaymentDate'];
			$RowPaymentDetails['Remarks'] = $RowInsuranceIDs['Remarks'];
			$RowPaymentDetails['ExchangeRate'] = $RowInsuranceIDs['ExchangeRate'];
			$RowPaymentDetails['TotalAmount'] = $TotalPremiumValue;
			$RowPaymentDetails['TotalPremiumValue'] = number_format($TotalPremiumValue, 2);
			$RowPaymentDetails['TotalInvoiceValuewithPercentage'] = number_format($TotalInvoiceValuewithPercentage, 2);
			$RowPaymentDetails['DebitNote'] = $RowInsuranceIDs['DebitNote'];
		}



		$AryRowPaymentDetails[] = $RowPaymentDetails;
		$data['InsurancePaymentData'] = $AryRowPaymentDetails;
		$data['InsuranceInvoicesDataList'] = $AryRowPoDetails;


		if ($StmtPoDetails) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function DeleteInsuranceTemplate()
	{

		$Error = true;

		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$PoID = $IpData['PoID'];
		$InsuranceID = $IpData['InsuranceID'];
		$PostedUserId = $IpData['PostedUserId'];
		$timezone = new DateTimeZone('Asia/Kolkata');
		$datetime = new DateTime('now', $timezone);
		$PostedDateTime = $datetime->format('Y-m-d H:i:s');

		if ($PoID == '') {
			$SqlDelete = "DELETE FROM `ScrmInsuranceData` WHERE InsuranceID='$InsuranceID'";
		} else {
			$SqlInsuranceData = "SELECT PoIDs FROM `ScrmInsuranceData` WHERE InsuranceID='$InsuranceID'";
			$StmtInsuranceData = mysql_query($SqlInsuranceData, $this->db);
			$RowInsuranceData = mysql_fetch_array($StmtInsuranceData, MYSQL_ASSOC);
			$ArrayPoIDs = explode(',', $RowInsuranceData['PoIDs']);
			$UpdatedPoIds = implode(',', array_diff($ArrayPoIDs, array($PoID)));


			$SqlDelete = "UPDATE `ScrmInsuranceData` SET `PoIDs`='$UpdatedPoIds',`PostedUserID`='$PostedUserId',`PostedTime`='$PostedDateTime' WHERE `InsuranceID`='$InsuranceID'";
		}

		$StmtDelete = mysql_query($SqlDelete, $this->db);
		if ($StmtDelete) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Delete Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getPendingContracts()
	{

		$RawIpData = file_get_contents('php://input');
		$Token = $this->getToken();
		$UserID = $Token['UserID'];
		$UserAcessType = $this->appUtilController->getUserRoleID($UserID);
		$AccessType = $UserAcessType['AccessType'];

		$IpData = json_decode($RawIpData, true);
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Processor = $IpData['Processor'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$Brand = $IpData['Brand'];
		$ClientID = $IpData['ClientID'];
		$Month = trim($IpData['Month']);
		$FromDate = trim($IpData['FromDate']);
		$ToDate = trim($IpData['ToDate']);
		$DashBoard = trim($IpData['DashBoard']);

		$DateFilter = '';
		if ($Month != '') {
			$Month = sprintf('%02d', $Month);
			if ($Month > 3) {
				$DateStart = $Year . '-' . $Month . '-01';
				$DateEnd = ($Year) . '-' . $Month . '-31';
			} else {
				$DateStart = ($Year + 1) . '-' . $Month . '-01';
				$DateEnd = ($Year + 1) . '-' . $Month . '-31';
			}
			$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
		} else {
			$DateStart = $Year . '-04-01';
			$DateEnd = ($Year + 1) . '-03-31';
			$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
		}
		if ($DashBoard) {
			if ($Year != '') {
				$FromDate = $Year . '-04-01';
				$ToDate = ($Year + 1) . '-03-31';
			}
			$DateFilter = " BETWEEN '$FromDate' and '$ToDate' ";
		}

		$Query = '';
		$SQuery = '';

		if ($Exporter != "") {
			$SQuery .= ' and SPD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SQuery .= ' and SPD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery .= ' and SPD.DestinationCountry=' . $Country;
		}

		if ($Processor != "") {
			$SQuery .= ' and SPD.ProcessorID=' . $Processor;
		}

		if ($Brand != '') {
			$SQuery .= " and SPD.PoID  in (SELECT DISTINCT(InvId) FROM `ScrmPoGradeData` WHERE Brand='$Brand')";
		}


		if ($Year != '') {
			$Error = false;
		}


		$SqlFields = '
		SPD.PoID,SCM.CurrenySymbol,
        SPD.ShipByDate,
		SPT.PaymentName,
        SPT.ShortName,
		SCD.ConsigneeName,
		SCD.ConsigneeCode,
		SPCD.Name as PortOfDischarge,
		SCL.name as DestinationCountry,
		SPD.ExporterID,
		SPD.ProcessorID,
		SOC.CerificationName as Cerification,
		SPD.PoNO,
		SPD.PoDate,
		SPD.InvNumFull,
		SPD.Remarks,
		SPD.FinalDestination,
		SPCD2.Name as FinalDestinationPort';

		//and SPD.PoID=776


		$SqlInsuranceList = "SELECT  $SqlFields  FROM ScrmPoData as SPD 
					left join ScrmCustomInvoices as SCI on SPD.PoID=SCI.PiID
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmPortCities as SPCD on SPCD.PortId=SPD.PortOfDischarge
					left join ScrmPortCities as SPCD2 on SPCD2.PortId=SPD.FinalDestination
					left join ScrmPaymentTerms as SPT on SPT.ID=SPD.PaymentTerms
					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
					left join ScrmCurrencyMaster as SCM on SCM.CurrencyCode=SPD.UnitCurrency
					left join ScrmOrderCertification as SOC on SOC.CerificationID=SPD.Certification
					WHERE 1=1
					 $SQuery and  ((SCI.CInvID ='' OR SCI.CInvID IS NULL) OR (SCI.StockOutDate='' OR SCI.StockOutDate IS NULL)) and SPD.Status not in (2,3,4) order by PoID desc";

		//and SPD.PoID=992 

		$StmtInsuranceList = mysql_query($SqlInsuranceList, $this->db);

		while ($RowPendingContracts = mysql_fetch_array($StmtInsuranceList, MYSQL_ASSOC)) {
			$PoID = $RowPendingContracts['PoID'];
			$ExpID = $RowPendingContracts['ExporterID'];
			$ProcID = $RowPendingContracts['ProcessorID'];
			$CurrenySymbol = $RowPendingContracts['CurrenySymbol'];
			$AryShortCodes = $this->getShortInvShortCodes($ExpID, $ProcID);
			$Exp_Proc = $AryShortCodes['ExpShortCode'] . '' . $AryShortCodes['ApprovalNo'];
			$ShipByDate = $this->getFormatedDatelines($RowPendingContracts['ShipByDate']);

			// $Arraypaymentvalues[] = $this->getInvoiceValue($PoID);
			if (!empty($RowPendingContracts['ShipByDate'])) {
				$ShipByDatee = new DateTime($RowPendingContracts['ShipByDate']);
				$CurrentDate = new DateTime(date('Y-m-d'));

				if ($CurrentDate > $ShipByDatee) {
					$interval = $CurrentDate->diff($ShipByDatee);
					$daysDifference = $interval->days;
				} else {
					$daysDifference = 'No Delay Days';
				}
			} else {
				$daysDifference = '--';
			}


			$RowPendingContracts['Sno'] = ++$C;
			$RowPendingContracts['ExpProc'] = $Exp_Proc;
			$RowPendingContracts['PoDate'] = $this->getFormatedDatelines($RowPendingContracts['PoDate']);

			// $RowPendingContracts['Total']=$Total=0;
			$RowPendingContracts['Payment'] = $RowPendingContracts['ShortName'] ?: $RowPendingContracts['PaymentName'];
			$RowPendingContracts['ConsigneeName'] = $RowPendingContracts['ConsigneeCode'] ?: $RowPendingContracts['ConsigneeName'];
			// $RowPendingContracts['Payment']=$RowPendingContracts['ShortName'] ;
			$RowPendingContracts['BuyerSample'] = $BuyerSample;
			$RowPendingContracts['LabSample'] = $LabSample;
			$RowPendingContracts['ShipByDate'] = $ShipByDate;
			$RowPendingContracts['DelayDays'] = $daysDifference;
			// if ($AccessType == 1) {
			$RowPendingContracts['InvoiceValue'] = $this->getInvoiceValue($PoID);
			// }
			$AryBreakUp = array();
			$SqlGroup = "SELECT GROUP_CONCAT(SPGD.ID) as VarityGroup,sum(Mcs) as GroupMcsTotal  FROM `ScrmPoGradeData` as SPGD 
			left JOIN ScrmPIVarietyList as SPVL on SPGD.VarietyID=SPVL.VarietyID
			LEFT join AdminPackingStyles as APS On APS.PCKID=SPGD.PackingStyleID
			WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand Order by SPGD.ID asc";
			$StmtGroup = mysql_query($SqlGroup, $this->db);

			while ($RowGroup = mysql_fetch_array($StmtGroup, MYSQL_ASSOC)) {
				$VarityGroup = $RowGroup['VarityGroup'];
				$RowBreakUp = $this->getPoBreakUP($VarityGroup, $ClientID);
				$RowBreakUp['Total'] = $RowGroup['GroupMcsTotal'];
				$RowBreakUp['Payment'] = $RowGroup['Payment'];
				$AryBreakUp[] = $RowBreakUp;
			}
			//$AryRowInsuranceList[]=$RowPendingContracts;
			//$AryRowInsuranceList=$this->getPoBreakUP($AryRowInsuranceList,$RowPendingContracts,$RowPendingContracts['PoID']);

			$RowPendingContracts['BreakUpData'] = $AryBreakUp;
			$AryRowInsuranceList[] = $RowPendingContracts;
		}

		$data['Error'] = null;
		$data['Msg'] = $C;
		$data['ExcelLink'] = "#";
		$data['PdfLink'] = "#";
		$data['Data'] = $AryRowInsuranceList;

		if ($StmtShipingBills) {
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = null;
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}
	function getPendingOrdersonHand()
	{

		$RawIpData = file_get_contents('php://input');
		$Token = $this->getToken();
		$UserID = $Token['UserID'];
		$UserAcessType = $this->appUtilController->getUserRoleID($UserID);
		$AccessType = $UserAcessType['AccessType'];

		$IpData = json_decode($RawIpData, true);
		$Year = $IpData['Year'];
		$Exporter = $IpData['Exporter'];
		$Processor = $IpData['Processor'];
		$Consignee = $IpData['Consignee'];
		$Country = $IpData['Country'];
		$Brand = $IpData['Brand'];

		$Query = '';
		$SQuery = '';

		if ($Exporter != "") {
			$SQuery .= ' and SPD.ExporterID in (' . $Exporter . ')';
		}
		if ($Consignee != "") {
			$SQuery .= ' and SPD.ConsigneeID=' . $Consignee;
		}
		if ($Country != "") {
			$SQuery .= ' and SPD.DestinationCountry=' . $Country;
		}

		if ($Processor != "") {
			$SQuery .= ' and SPD.ProcessorID=' . $Processor;
		}

		if ($Brand != '') {
			$SQuery .= " and SPD.PoID  in (SELECT DISTINCT(InvId) FROM `ScrmPoGradeData` WHERE Brand='$Brand')";
		}


		if ($Year != '') {
			$Error = false;
		}


		$SqlFields = '
		SPD.PoID,SCM.CurrenySymbol,
        SPD.ShipByDate,
		SPT.PaymentName,
        SPT.ShortName,
		SCD.ConsigneeName,
		SCD.ConsigneeCode,
		SPCD.Name as PortOfDischarge,
		SCL.name as DestinationCountry,
		SPD.ExporterID,
		SPD.ProcessorID,
		SOC.CerificationName as Cerification,
		SPD.PoNO,
		SPD.PoDate,
		SPD.InvNumFull,
		SPD.Remarks,
		SPD.FinalDestination,
		SPCD2.Name as FinalDestinationPort';

		//and SPD.PoID=776


		$SqlInsuranceList = "SELECT  $SqlFields  FROM ScrmPoData as SPD 
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmPortCities as SPCD on SPCD.PortId=SPD.PortOfDischarge
					left join ScrmPortCities as SPCD2 on SPCD2.PortId=SPD.FinalDestination
					left join ScrmPaymentTerms as SPT on SPT.ID=SPD.PaymentTerms
					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
					left join ScrmCurrencyMaster as SCM on SCM.CurrencyCode=SPD.UnitCurrency
					left join ScrmOrderCertification as SOC on SOC.CerificationID=SPD.Certification
					WHERE SDB.TransportedDate='' 
					 $SQuery and  SPD.PoID NOT in 
					(SELECT PiID FROM `ScrmCustomInvoices`  where StockOutDate!='') and SPD.Status not in (2,3,4) order by ShipByDate asc";

		//and SPD.PoID=992 

		$StmtInsuranceList = mysql_query($SqlInsuranceList, $this->db);

		while ($RowPendingContracts = mysql_fetch_array($StmtInsuranceList, MYSQL_ASSOC)) {
			$PoID = $RowPendingContracts['PoID'];
			$ExpID = $RowPendingContracts['ExporterID'];
			$ProcID = $RowPendingContracts['ProcessorID'];
			$CurrenySymbol = $RowPendingContracts['CurrenySymbol'];
			$AryShortCodes = $this->getShortInvShortCodes($ExpID, $ProcID);
			$Exp_Proc = $AryShortCodes['ExpShortCode'] . '' . $AryShortCodes['ApprovalNo'];
			$ShipByDate = $this->getFormatedDatelines($RowPendingContracts['ShipByDate']);

			// $Arraypaymentvalues[] = $this->getInvoiceValue($PoID);


			$RowPendingContracts['Sno'] = ++$C;
			$RowPendingContracts['ExpProc'] = $Exp_Proc;
			// $RowPendingContracts['Total']=$Total=0;
			$RowPendingContracts['Payment'] = $RowPendingContracts['ShortName'] ?: $RowPendingContracts['PaymentName'];
			$RowPendingContracts['ConsigneeName'] = $RowPendingContracts['ConsigneeCode'] ?: $RowPendingContracts['ConsigneeName'];
			// $RowPendingContracts['Payment']=$RowPendingContracts['ShortName'] ;
			$RowPendingContracts['BuyerSample'] = $BuyerSample;
			$RowPendingContracts['LabSample'] = $LabSample;
			$RowPendingContracts['ShipByDate'] = $ShipByDate;
			// if ($AccessType == 1) {
			$RowPendingContracts['InvoiceValue'] = $CurrenySymbol . ' ' . $this->getInvoiceValue($PoID);
			// }
			$AryBreakUp = array();
			$SqlGroup = "SELECT GROUP_CONCAT(SPGD.ID) as VarityGroup,sum(Mcs) as GroupMcsTotal  FROM `ScrmPoGradeData` as SPGD 
			left JOIN ScrmPIVarietyList as SPVL on SPGD.VarietyID=SPVL.VarietyID
			LEFT join AdminPackingStyles as APS On APS.PCKID=SPGD.PackingStyleID
			WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand Order by SPGD.ID asc";
			$StmtGroup = mysql_query($SqlGroup, $this->db);

			while ($RowGroup = mysql_fetch_array($StmtGroup, MYSQL_ASSOC)) {
				$VarityGroup = $RowGroup['VarityGroup'];
				$RowBreakUp = $this->getPoBreakUP($VarityGroup, '');
				$RowBreakUp['Total'] = $RowGroup['GroupMcsTotal'];
				$RowBreakUp['Payment'] = $RowGroup['Payment'];
				$AryBreakUp[] = $RowBreakUp;
			}
			//$AryRowInsuranceList[]=$RowPendingContracts;
			//$AryRowInsuranceList=$this->getPoBreakUP($AryRowInsuranceList,$RowPendingContracts,$RowPendingContracts['PoID']);

			$RowPendingContracts['BreakUpData'] = $AryBreakUp;
			$AryRowInsuranceList[] = $RowPendingContracts;
		}

		$data['Error'] = null;
		$data['Msg'] = $C;
		$data['ExcelLink'] = "#";
		$data['PdfLink'] = "#";
		$data['Data'] = $AryRowInsuranceList;

		if ($StmtShipingBills) {
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = null;
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getInvoiceValue($PoID)
	{

		// $Qry = "WHERE InvId='$PoID'";


		$SqlTotal = "SELECT  sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
				sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` as PGD 
				left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";

		$StmtTotal = mysql_query($SqlTotal, $this->db);
		$RowTotal = mysql_fetch_array($StmtTotal, MYSQL_ASSOC);

		$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$PoID'";
		$StmtQtyUnit = mysql_query($SqlQtyUnit, $this->db);
		$RowQtyUnit = mysql_fetch_array($StmtQtyUnit, MYSQL_ASSOC);
		$QtyUnit = $RowQtyUnit['QtyUnit'];
		if ($QtyUnit == 'KGS') {
			// $Qty=$RowTotal['NetWgtKgs'];
			$UsdValue = $RowTotal['PriceKgs'];
			// $UnitPrice=number_format($RowTotal['Price'],2);
		} else {
			// $Qty=$RowTotal['NetWgtLbs']*0.454;
			$UsdValue = $RowTotal['PriceLbs'];
			// $UnitPrice=round(($UsdValue/$Qty),6);
		}
		// echo $UsdValue; exit;

		return round($UsdValue, 2);
	}

	function getPoBreakUp($VarityGroup, $ClientID)
	{

		$AryG1['GradeName'] = "G1";
		$AryG1['GradeValue'] = "6/8";
		$AryG2['GradeName'] = "G2";
		$AryG2['GradeValue'] = "8/12";
		$AryG3['GradeName'] = "G3";
		$AryG3['GradeValue'] = "13/15";
		$AryG4['GradeName'] = "G4";
		$AryG4['GradeValue'] = "16/20";
		$AryG5['GradeName'] = "G5";
		$AryG5['GradeValue'] = "21/25";
		$AryG6['GradeName'] = "G6";
		$AryG6['GradeValue'] = "26/30";
		$AryG7['GradeName'] = "G7";
		$AryG7['GradeValue'] = "31/35";
		$AryG8['GradeName'] = "G8";
		$AryG8['GradeValue'] = "31/40";
		$AryG9['GradeName'] = "G9";
		$AryG9['GradeValue'] = "41/50";
		$AryG10['GradeName'] = "G10";
		$AryG10['GradeValue'] = "51/60";
		$AryG11['GradeName'] = "G11";
		$AryG11['GradeValue'] = "61/70";
		$AryG12['GradeName'] = "G12";
		$AryG12['GradeValue'] = "71/90";
		$AryG13['GradeName'] = "G13";
		$AryG13['GradeValue'] = "91/110";
		$AryG14['GradeName'] = "G14";
		$AryG14['GradeValue'] = "100/150";
		$AryG15['GradeName'] = "G15";
		$AryG15['GradeValue'] = "150/200";
		$AryG16['GradeName'] = "G16";
		$AryG16['GradeValue'] = "200/250";
		$AryG17['GradeName'] = "G17";
		$AryG17['GradeValue'] = "Broken";
		$AryG18['GradeName'] = "G18";
		$AryG18['GradeValue'] = "All Mix";
		if ($ClientID != '' && $ClientID == 'SMEV3') {
			$AryGrades = array(
				$AryG1,
				$AryG2,
				$AryG3,
				$AryG4,
				$AryG5,
				$AryG6,
				$AryG7,
				$AryG8,
				$AryG9,
				$AryG10,
				$AryG11,
				$AryG12,
				$AryG13,
				$AryG14,
				$AryG15,
				$AryG16,
				$AryG17,
				$AryG18
			);
		} else {
			$AryGrades = array(
				$AryG1,
				$AryG2,
				$AryG3,
				$AryG4,
				$AryG5,
				$AryG6,
				$AryG7,
				$AryG8,
				$AryG9,
				$AryG10,
				$AryG11,
				$AryG12,
				$AryG13,
				$AryG14,
				$AryG15,
				$AryG16
			);
		}
		//974



		$SqlFields = "PGD.GradeGroup,GROUP_CONCAT(SPGD.Mcs , '&' , SPGD.Price) as Mcs,SPGD.Price,SPGD.Grade,SPGD.FreezingType,SPGD.GlazeType,
SPGD.GlazePerc,SPGD.Treatment,SPGD.Brand,SPGD.BuyerItemCode,BAP,Species,TypeProduct,
APS.PackingStyle,SPVL.ShortName";

		$AryData = array();
		$SqlPoBreakUp = "SELECT $SqlFields FROM `ScrmPoGradeData`
 as SPGD 
left JOIN ScrmPIVarietyList as SPVL on SPGD.VarietyID=SPVL.VarietyID
LEFT join AdminPackingStyles as APS On APS.PCKID=SPGD.PackingStyleID
LEFT join ProductionGrades as PGD On PGD.value=SPGD.Grade
WHERE SPGD.ID in (0,$VarityGroup) group by SPGD.ID;";
		$AryRow = array();

		foreach ($AryGrades as $Item) {
			$GradeName = $Item['GradeName'];
			$GradeRef = $Item['GradeValue'];
			$AryRow[$GradeName] = '';
		}
		$StmtSqlPoBreakUp = mysql_query($SqlPoBreakUp, $this->db);
		$IsGradeAval = $StmtPoBreakUp->num_rows;
		/*if($IsGradeAval==0){
																		$AryData[]=$AryRow;
																			}*/

		$C = 0;
		while ($RowSqlPoBreakUp = mysql_fetch_array($StmtSqlPoBreakUp, MYSQL_ASSOC)) {



			$AryRow['Brand'] = $RowSqlPoBreakUp['Brand'];
			$AryRow['Certification'] = $RowSqlPoBreakUp['BAP'];
			$AryRow['Variety'] = $RowSqlPoBreakUp['ShortName'];
			$AryRow['Species'] = $RowSqlPoBreakUp['Species'];
			$AryRow['TypeProduct'] = $RowSqlPoBreakUp['TypeProduct'];
			$AryRow['Form'] = $RowSqlPoBreakUp['FreezingType'];
			$AryRow['Packing'] = $RowSqlPoBreakUp['PackingStyle'];
			if ($RowSqlPoBreakUp['GlazePerc'] > 0) {
				$AryRow['Glaze'] = $RowSqlPoBreakUp['GlazeType'] . '-' . $RowSqlPoBreakUp['Treatment'] . ' ' . $RowSqlPoBreakUp['GlazePerc'] . '%';
			} else {
				$AryRow['Glaze'] = $RowSqlPoBreakUp['GlazeType'] . '-' . $RowSqlPoBreakUp['Treatment'];
			}
			$AryRow['Price'] = $RowSqlPoBreakUp['Price'];
			$AryRow['Grade'] = $RowSqlPoBreakUp['Grade'];
			$Mcs = $RowSqlPoBreakUp['Mcs'];
			$GradeGroup = trim($RowSqlPoBreakUp['GradeGroup']);
			$Grade = $RowSqlPoBreakUp['Grade'];

			foreach ($AryGrades as $Item) {
				$GradeName = $Item['GradeName'];
				$GradeRef = $Item['GradeValue'];

				if ($ClientID != '' && $ClientID == 'SMEV3') {
					if ($AryRow[$GradeName] != '') {
						//$AryRow[$GradeName]=''; 
					} else if ($GradeRef == $Grade) {
						$AryRow[$GradeName] = $Mcs;
					} else if ($GradeRef == $GradeGroup) {
						$AryRow[$GradeName] = '(' . $Grade . ') ' . $Mcs;
					} else {
						$GradeValues = explode('/', $Grade);
						$GradeRefValues = explode('/', $GradeRef);
						if ($GradeName == 'G17') {
							if (count($GradeValues) < 2) {
								$GradeValues = explode(' ', $Grade);
								if ($GradeValues[0] == $GradeRefValues[0]) {
									$AryRow[$GradeName] = $Mcs;
								}
							} else {
								if ($GradeValues[0] == $GradeRefValues[0]) {
									$AryRow[$GradeName] = $Mcs;
								}
							}
						} else if ($GradeName != 'G17' && count($GradeValues) > 1 && $GradeValues[0] >= $GradeRefValues[0] && $GradeValues[1] <= $GradeRefValues[1]) {
							$AryRow[$GradeName] = $Mcs;
						}
						if ($GradeName == 'G18') {
							$GradeValues = explode(' ', $Grade);
							$GradeRefValues = explode(' ', $GradeRef);
							// if (count($GradeValues) < 2) {
								// $GradeValues = explode(' ', $Grade);
								if (strcasecmp($GradeValues[0], $GradeRefValues[0]) === 0) {
									$AryRow[$GradeName] = $Mcs;
								}
							// }
						}
					}
				} else {
					if ($AryRow[$GradeName] != '') {
						//$AryRow[$GradeName]=''; 
					} else if ($GradeRef == $Grade) {
						$AryRow[$GradeName] = $Mcs;
					} else if ($GradeRef == $GradeGroup) {
						$AryRow[$GradeName] = '(' . $Grade . ') ' . $Mcs;
					} else if ($AryRow[$GradeName] != '') {
						//$AryRow[$GradeName]=''; 
					} else {
						$GradeValues = explode('/', $Grade);
						$GradeRefValues = explode('/', $GradeRef);
						if ($isNumericRange && $GradeValues[0] > $GradeRefValues[0] && $GradeValues[1] <= $GradeRefValues[1]) {
							$AryRow[$GradeName] = '(' . $Grade . ') - ' . $Mcs;
						}
					}
				}
				// echo $AryRow[$GradeName];
			}


			$C++;
		}
		$AryData[] = $AryRow;


		return $AryRow;
	}






	/************Reports****************/

	function getEtd_EtaReport()
	{



		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$Type = $IpData['Type'];
		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Exporter = $IpData['Exporter'];
		$Processor = $IpData['Processor'];
		$Country = $IpData['Country'];
		$Month = $IpData['Month'];
		$Year = $IpData['Year'];
		$Buyer = $IpData['Buyer'];
		$Brand = $IpData['Brand'];
		$ConsigneeID = $IpData['ConsigneeID'];
		$Fbdstatus = $IpData['Fbdstatus'];




		$Query = '';
		$SubQuery = '';
		$SQuery = '';




		if ($Exporter != "") {
			$Query .= ' and POD.ExporterID in (' . $Exporter . ') ';
		}
		if ($Processor != "") {
			$Query .= ' and POD.ProcessorID=' . $Processor . ' ';
		}
		if ($Country != "") {
			$Query .= ' and POD.DestinationCountry=' . $Country . ' ';
		}

		if ($ConsigneeID != "") {
			$Query .= ' and POD.ConsigneeID=' . $ConsigneeID . ' ';
		}

		if ($Brand != '') {
			$Query .= " and POD.PoID  in (SELECT DISTINCT(InvId) FROM `ScrmPoGradeData` WHERE Brand='$Brand')";
		}





		if ($Type == 'Dashboard') {

			/* "FromDate" => "",
																										  "ToDate" => "",
																										  "Exporter" => $Exporter,
																										  "Processor" => "",
																										  "EtdEta" => "",
																										  "Type" => $Type,
																										  "Year" => $Year,
																										  "Month" => $Month,
																										  "Buyer" => $Buyer,
																										  "Brand" => $Brand,
																										  "Country" => $Country

																										  */
			$DateFilter = "SCI.CInvDate " . $this->getDateFilter($Month, $Year);
			if ($Year) {
				$FromDate = $Year . '-04-01';
				$ToDate = ($Year + 1) . '-03-31';
			}
			$DateFilter = " SCI.CInvDate between '$FromDate' and '$ToDate' ";
		} else {
			$DateFilter = " SCI.CInvDate between '$FromDate' and '$ToDate' ";
		}


		$SqlFields = 'ConsigneeName,POD.ExporterID,SCI.PiID,ISF.ContainerNo ,PoNo,
		 COALESCE(DATEDIFF(EstimatedETA,BLDate),0) as TransitDays,
		 COALESCE(DATEDIFF(ETA_FD,ShippingOnboardDt),0) as TransitDaysperLiner,
		 ProcessorID ,BLNo,CustomsInvNo as InvNo,ISF.FDAEntryNumber,
 SPC.Name as Destination,SCL.name as DestinationCountry,ShippingOnboardDt,EstimatedETA,
 DeliveryTerms as CYSD,LinerName,GetOutFull,EmptyReturn';

		$SqlEtaEtdReport = "SELECT $SqlFields
 from  ScrmCustomInvoices  SCI 
left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
left join ScrmCountriesList  SCL on SCL.id=POD.DestinationCountry
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID
WHERE  $DateFilter   $Query $SubQuery  $SQuery and ISF.EmptyReturn!='' and SCI.StockOutDate !=''
and POD.Status not in (4) order by POD.PoID desc";


		$StmtEtaEtdReport = mysql_query($SqlEtaEtdReport, $this->db);



		while ($RowEtaEtdReport = mysql_fetch_array($StmtEtaEtdReport, MYSQL_ASSOC)) {

			$PoID = $RowEtaEtdReport['PiID'];
			$TransitDays = $RowEtaEtdReport['TransitDays'];
			$TransitDaysperLiner = $RowEtaEtdReport['TransitDaysperLiner'];
			$EntryNumber = $RowEtaEtdReport['FDAEntryNumber'];
			$ProcessorID = $RowEtaEtdReport['ProcessorID'];
			$ProcessorData = $this->getExporterDetails($ProcessorID);
			$ProcessorApprovalNo = $ProcessorData['ExpApprovalNo'];
			$FinalDestination = $RowEtaEtdReport['Destination'] . ',<br>' . $RowEtaEtdReport['DestinationCountry'];
			$ShippingOnboardDt = $this->getFormatedDatelines($RowEtaEtdReport['ShippingOnboardDt']);
			$EtaDate = $this->getFormatedDatelines($RowEtaEtdReport['EstimatedETA']);
			$GateOutFull = $this->getFormatedDatelines($RowEtaEtdReport['GetOutFull']);
			$EmptyCYDate = $this->getFormatedDatelines($RowEtaEtdReport['EmptyReturn']);

			$SqlProduct = "SELECT GROUP_CONCAT(SPV.Grade,'-',ProductDescription) as ProductDescription FROM `ScrmPoGradeData` as SPD
			left join ScrmPIVarietyList as SPV on SPV.VarietyID=SPD.VarietyID
			  WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand  Order by SPD.ID asc";
			$StmtProduct = mysql_query($SqlProduct, $this->db);
			unset($AryProductDescription);

			while ($RowProduct = mysql_fetch_array($StmtProduct, MYSQL_ASSOC)) {

				$AryProductDescription[] = $RowProduct['ProductDescription'];

				$ProductDescription = implode(',', $AryProductDescription);
			}

			$RowEtaEtdReport['Sno'] = ++$Sno;
			$RowEtaEtdReport['Product'] = $ProductDescription;
			$RowEtaEtdReport['Processor'] = $ProcessorApprovalNo;
			$RowEtaEtdReport['ShippingOnboardDt'] = $ShippingOnboardDt;
			$RowEtaEtdReport['EtaDate'] = $EtaDate;
			$RowEtaEtdReport['FinalDestination'] = $FinalDestination;
			$RowEtaEtdReport['EmptyCYDate'] = $EmptyCYDate;
			$RowEtaEtdReport['GateOutFull'] = $GateOutFull;
			$RowEtaEtdReport['TransitDays'] = $TransitDays;
			$RowEtaEtdReport['TransitDaysasperLiner'] = $TransitDaysperLiner;
			$RowEtaEtdReport['EntryNumber'] = $EntryNumber;
			$AryRowEtaEtdReport[] = $RowEtaEtdReport;
		}

		$data['Data'] = $AryRowEtaEtdReport;

		if ($StmtEtaEtdReport) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}

		//}
	}


	function getDateFilter($Month, $FinClsYear)
	{

		if ($Month != '') {
			$Month = sprintf('%02d', $Month);
			if ($Month > 3) {
				$DateStart = $FinClsYear . '-' . $Month . '-01';
				$DateEnd = ($FinClsYear) . '-' . $Month . '-31';
			} else {
				$DateStart = ($FinClsYear + 1) . '-' . $Month . '-01';
				$DateEnd = ($FinClsYear + 1) . '-' . $Month . '-31';
			}
			$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
		} else {
			$DateStart = $FinClsYear . '-04-01';
			$DateEnd = ($FinClsYear + 1) . '-03-31';
			$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
		}

		return $DateFilter;
	}


	function getPendingPaymentsReport()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Exporter = $IpData['Exporter'];
		$Processor = $IpData['Processor'];
		$Country = $IpData['Country'];
		$Consignee = $IpData['Consignee'];

		$Query = '';

		if ($Exporter != "") {
			$Query .= 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Processor != "") {
			$Query .= 'and POD.ProcessorID=' . $Processor;
		}
		if ($Country != "") {
			$Query .= 'and POD.DestinationCountry=' . $Country;
		}
		if ($Consignee != "") {
			$Query .= 'and POD.ConsigneeID=' . $Consignee;
		}

		$DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";

		$SqlFields = 'SCI.PiID,CustomsInvNo,PoNo,ConsigneeName,SPCS.DueDte,
SCI.ContainerNo,Name as Port,PaymentName,ShippingOnboardDt,EstimatedETA,ETA_FD,GetOutFull,FDAClearDate,SPCS.BnkChrges,SPCS.OtherAmount,SPCS.EtraAmount,
 (SELECT sum(Mcs) FROM `ScrmPoGradeData` where InvId=POD.PoID)as NoofCarton, SCI.CInvDate,
 ISF.FDAEUReleaseCFIADate, ISF.FDAEUHoldDate, ISF.FDAContainerStatus, SCOD.PoID, SBDM.BankDetails, SBDM.BankName, SBDM.AccntName, SBDM.AccountNumber';

		// $SqlPendingPaymentsReport = "SELECT $SqlFields
		//  from  ScrmCustomInvoices  SCI 
		// left join ScrmPoData  POD on POD.PoID=SCI.PiID
		// left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
		// left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
		// left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
		// left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
		// left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID
		// left join ScrmPaymentCollections as SPCS on POD.PoID=SPCS.PIID

		// WHERE  $DateFilter  $Query and TransportedDate!='' and POD.Status not in (2,3,4)
		// and POD.PoID not in(SELECT PIID FROM `ScrmPaymentCollections` WHERE Status=1)
		// order by POD.PoID desc";

		$SqlPendingPaymentsReport = "SELECT $SqlFields
 from  ScrmCustomInvoices  SCI 
left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID
left join ScrmPaymentCollections as SPCS on POD.PoID=SPCS.PIID
left join ScrmCommercialData as SCOD on SCOD.PoID=SCI.PiID
left join ScrmBankDetailsMaster as SBDM on SBDM.ID=SCOD.BankID

WHERE  $DateFilter  $Query and TransportedDate!='' and POD.Status not in (2,3,4)
and POD.PoID not in(SELECT PoID from ScrmRealizationDetails where RezStatus=1)
order by POD.PoID desc";


		$StmtPendingPaymentsReport = mysql_query($SqlPendingPaymentsReport, $this->db);

		$TotalInvoiceValueUSD = 0;
		$TotalRemainingInvoiceValueUSD = 0;
		$TotalInvoices = 0;
		$AryWholeData = array();
		$ArySortedData = array();



		while ($RowPendingPaymentsReport = mysql_fetch_array($StmtPendingPaymentsReport, MYSQL_ASSOC)) {
			$AryWholeData[] = $RowPendingPaymentsReport;
			if ($RowPendingPaymentsReport['EstimatedETA'] != '') {
				$ArySortedData[] = $RowPendingPaymentsReport;
			}
		}

		usort($ArySortedData, $this->build_sorter('EstimatedETA', 'ASC'));

		foreach ($AryWholeData as $RowPendingPaymentsReport) {
			if ($RowPendingPaymentsReport['EstimatedETA'] == '') {
				$ArySortedData[] = $RowPendingPaymentsReport;
			}
		}

		foreach ($ArySortedData as $RowPendingPaymentsReport) {


			$PoID = $RowPendingPaymentsReport['PiID'];
			$BankCharges = $RowPendingPaymentsReport['BnkChrges'];
			$ExtraAmount = $RowPendingPaymentsReport['EtraAmount'];
			$OtherAmount = $RowPendingPaymentsReport['OtherAmount'];

			$GetOutFull = $this->getFormatedDatelines($RowPendingPaymentsReport['GetOutFull']);

			$ShippingOnboardDt = $this->getFormatedDatelines($RowPendingPaymentsReport['ShippingOnboardDt']);
			$ETA_FD = $this->getFormatedDatelines($RowPendingPaymentsReport['ETA_FD']);
			//$ETA_POD =$this-> getFormatedDatelines($RowPendingPaymentsReport['ETA_POD']);
			$EstimatedETA = $this->getFormatedDatelines($RowPendingPaymentsReport['EstimatedETA']);
			$FDAClearDate = $this->getFormatedDatelines($RowPendingPaymentsReport['FDAClearDate']);
			$DueDte = $this->getFormatedDatelines($RowPendingPaymentsReport['DueDte']);
			$EmptyReturn = $this->getFormatedDatelines($RowPendingPaymentsReport['EmptyReturn']);
			$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
			$InvValueUSD = $ArrayPaymentValues['InvValueUSD'];
			$UnitCurrency = $ArrayPaymentValues['UnitCurrency'];
			$InvDate = $RowPendingPaymentsReport['CInvDate'];

			// echo print_r($InvValueUSD);
			// exit;

			$SqlUtilisedValues = "SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
			$StmtUtilisedValues = mysql_query($SqlUtilisedValues, $this->db);
			$RowUtilisedValues = mysql_fetch_array($StmtUtilisedValues, MYSQL_ASSOC);
			$UtilizedValue = $RowUtilisedValues['UtilizedValue'];

			$ShortFall = $InvValueUSD + $ExtraAmount - $BankCharges - $UtilizedValue - $OtherAmount;

			if ($UnitCurrency == 'INR') {
				$ExchangeRateQuery = "SELECT * FROM `ScrmExchangeRates` where  FromDate <= '$InvDate' ORDER BY FromDate  DESC limit 1";
				$StmtExchangeRateQuery = mysql_query($ExchangeRateQuery, $this->db);
				$RowExchangeRateQuery = mysql_fetch_array($StmtExchangeRateQuery, MYSQL_ASSOC);
				$ShortFall = $ShortFall / $RowExchangeRateQuery['ExchangeRate'];
			}
			$Mcs = $RowPendingPaymentsReport['NoofCarton'];
			if (true) {
				$ArrayPendingPaymentReport['Sno'] = ++$Sno;
				$ArrayPendingPaymentReport['InvoiceNo'] = $RowPendingPaymentsReport['CustomsInvNo'];

				$ArrayPendingPaymentReport['PoNo'] = $RowPendingPaymentsReport['PoNo'];
				$ArrayPendingPaymentReport['Buyer'] = $RowPendingPaymentsReport['ConsigneeName'];
				$ArrayPendingPaymentReport['ContainerNo'] = $RowPendingPaymentsReport['ContainerNo'];
				$ArrayPendingPaymentReport['Port'] = $RowPendingPaymentsReport['Port'];
				$ArrayPendingPaymentReport['PaymentTerms'] = $RowPendingPaymentsReport['PaymentName'];
				$ArrayPendingPaymentReport['NoofCarton'] = $Mcs;

				$ArrayPendingPaymentReport['InvoiceValueUSD'] = $InvValueUSD;
				$ArrayPendingPaymentReport['RemainingInvoiceValueUSD'] = $ShortFall;
				$ArrayPendingPaymentReport['OnBoardDate'] = $ShippingOnboardDt;
				//$ArrayPendingPaymentReport['ETADate']=$ETA_POD;
				$ArrayPendingPaymentReport['ETADate'] = $ETA_FD;
				$ArrayPendingPaymentReport['ETAModifyDate'] = $EstimatedETA;
				$ArrayPendingPaymentReport['GateOutFullDate'] = $GetOutFull;
				$ArrayPendingPaymentReport['FDACleared'] = $FDAClearDate;
				$ArrayPendingPaymentReport['DueOn'] = $DueDte;
				$ArrayPendingPaymentReport['BankDueOn'] = 'Doubt';
				$ArrayPendingPaymentReport['FDAEUReleaseCFIADate'] = $this->getFormatedDatelines($RowPendingPaymentsReport['FDAEUReleaseCFIADate']);
				$ArrayPendingPaymentReport['FDAEUHoldDate'] = $this->getFormatedDatelines($RowPendingPaymentsReport['FDAEUHoldDate']);
				$ArrayPendingPaymentReport['FDAContainerStatus'] = $RowPendingPaymentsReport['FDAContainerStatus'];
				// $ArrayPendingPaymentReport['BankDetails'] = $RowPendingPaymentsReport['BankDetails'];
				$ArrayPendingPaymentReport['BankDetails'] = $RowPendingPaymentsReport['BankName'] . ' - ' . $RowPendingPaymentsReport['AccountNumber'];
				$TotalInvoiceValueUSD += $InvValueUSD;
				$TotalRemainingInvoiceValueUSD += $ShortFall;
				$TotalMcs += $Mcs;
				$AryRowPendingPaymentReport[] = $ArrayPendingPaymentReport;

				// echo json_encode($TotalInvoiceValueUSD, JSON_PRETTY_PRINT);
				// exit;
			}
		}
		$ArrayPendingPaymentTotals['TotalInvoices'] = $Sno;
		$ArrayPendingPaymentTotals['TotalInvoiceValueUSD'] = $TotalInvoiceValueUSD;
		$ArrayPendingPaymentTotals['TotalRemainingInvoiceValueUSD'] = $TotalRemainingInvoiceValueUSD;
		//$ArrayPendingPaymentTotals['TotalMcs']=$TotalMcs;

		// echo print_r($data);
		// exit;

		$data['Totals'] = $ArrayPendingPaymentTotals;
		$data['PendingPaymentReportData'] = $AryRowPendingPaymentReport;

		// echo json_encode($data, JSON_PRETTY_PRINT);
		// echo print_r($data);
		// exit;
		if ($StmtPendingPaymentsReport) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getFDAReleaseInformation()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Exporter = $IpData['Exporter'];
		$Processor = $IpData['Processor'];
		$Country = $IpData['Country'];
		$Consignee = $IpData['Consignee'];

		$Query = '';

		if ($Exporter != "") {
			$Query .= 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Processor != "") {
			$Query .= 'and POD.ProcessorID=' . $Processor;
		}
		if ($Country != "") {
			$Query .= 'and POD.DestinationCountry=' . $Country;
		}
		if ($Consignee != "") {
			$Query .= 'and POD.ConsigneeID=' . $Consignee;
		}

		// $DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";
		$DateFilter = "ISF.FDAEUReleaseCFIADate between '$FromDate' and '$ToDate'";

		$SqlFields = 'SCI.PiID,SCI.CInvDate,SCI.CustomsInvNo,PoNo,POD.ProcessorID,
		ISF.FDAEntryNumber,ISF.FDAEUReleaseCFIADate,SPCS.BnkChrges,SPCS.OtherAmount,SPCS.EtraAmount,SCD.`ConsigneeName`';

		$SqlPendingPaymentsReport = "SELECT $SqlFields,
		(SELECT TranshipDt FROM ScrmTranshipmentPortsList WHERE PIID=SCI.PiID and Status=1 ORDER BY `TspID` DESC LIMIT 1) As ETA_Date
		from ScrmCustomInvoices SCI 
		left join ScrmPoData  POD on POD.PoID=SCI.PiID 
		left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
		left join ScrmPaymentCollections as SPCS on POD.PoID=SPCS.PIID
		LEFT JOIN ScrmConsigneeDetails SCD ON SCD.`ConID`=POD.ConsigneeID
		WHERE  $DateFilter  $Query and POD.Status not in (2,3,4)
		and POD.PoID not in (SELECT PoID from ScrmRealizationDetails where RezStatus=1)
		order by POD.PoID desc";

		$StmtPendingPaymentsReport = mysql_query($SqlPendingPaymentsReport, $this->db);

		$TotalInvoiceValueUSD = 0;

		while ($RowPendingPaymentsReport = mysql_fetch_array($StmtPendingPaymentsReport, MYSQL_ASSOC)) {

			$PoID = $RowPendingPaymentsReport['PiID'];
			$BankCharges = $RowPendingPaymentsReport['BnkChrges'];
			$ExtraAmount = $RowPendingPaymentsReport['EtraAmount'];
			$OtherAmount = $RowPendingPaymentsReport['OtherAmount'];
			$CInvDate = $this->getFormatedDatelines($RowPendingPaymentsReport['CInvDate']);
			$ETA_Date = $this->getFormatedDatelines($RowPendingPaymentsReport['ETA_Date']);
			$FDAEUReleaseCFIADate = $this->getFormatedDatelines($RowPendingPaymentsReport['FDAEUReleaseCFIADate']);
			$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
			$InvValueUSD = $ArrayPaymentValues['InvValueUSD'];
			$ProcessorID = $RowPendingPaymentsReport['ProcessorID'];

			$SqlUtilisedValues = "SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
			$StmtUtilisedValues = mysql_query($SqlUtilisedValues, $this->db);
			if ($StmtUtilisedValues) {
				$RowUtilisedValues = mysql_fetch_array($StmtUtilisedValues, MYSQL_ASSOC);
				$UtilizedValue = $RowUtilisedValues['UtilizedValue'];
			}

			$ShortFall = $InvValueUSD + $ExtraAmount - $BankCharges - $UtilizedValue - $OtherAmount;
			// if ($ShortFall > 0) {
			$ArrayPendingPaymentReport['Sno'] = ++$Sno;
			$ArrayPendingPaymentReport['InvoiceNo'] = $RowPendingPaymentsReport['CustomsInvNo'];
			$ArrayPendingPaymentReport['CInvDate'] = $CInvDate;
			$ArrayPendingPaymentReport['InvoiceValueUSD'] = $InvValueUSD;
			$ArrayPendingPaymentReport['FDAEntryNumber'] = $RowPendingPaymentsReport['FDAEntryNumber'];
			$ArrayPendingPaymentReport['FDAEUReleaseCFIADate'] = $FDAEUReleaseCFIADate;
			$ArrayPendingPaymentReport['ETA_Date'] = $ETA_Date;
			if (!empty($FDAEUReleaseCFIADate)) {
				$inputDate = DateTime::createFromFormat('d-m-Y', $FDAEUReleaseCFIADate);
				if ($inputDate) {
					$today = new DateTime();
					if ($inputDate <= $today) {
						$overdueDays = $today->diff($inputDate)->format('%a');
						$ArrayPendingPaymentReport['OverDueDays'] = (int) $overdueDays;
					} else {
						$ArrayPendingPaymentReport['OverDueDays'] = '--';
					}
				} else {
					$ArrayPendingPaymentReport['OverDueDays'] = '--';
				}
			} else {
				$ArrayPendingPaymentReport['OverDueDays'] = '--';
			}

			$SqlProcessor = "SELECT * FROM `AdminExporterDetails` WHERE ExpID='$ProcessorID'";
			$StmtProcessor = mysql_query($SqlProcessor, $this->db);
			if ($StmtProcessor) {
				$RowProcessor = mysql_fetch_array($StmtProcessor, MYSQL_ASSOC);
				$ArrayPendingPaymentReport['ExpApprovalNo'] = $RowProcessor['ExpApprovalNo'];
			}

			$TotalInvoiceValueUSD += $InvValueUSD;
			$TotalRemainingInvoiceValueUSD += $ShortFall;
			$ArrayPendingPaymentReport['ConsigneeName'] = $RowPendingPaymentsReport['ConsigneeName'];
			$AryRowPendingPaymentReport[] = $ArrayPendingPaymentReport;

			// echo json_encode($TotalInvoiceValueUSD, JSON_PRETTY_PRINT);
			// exit;
			// }
		}
		$ArrayPendingPaymentTotals['TotalInvoices'] = $Sno;
		$ArrayPendingPaymentTotals['TotalInvoiceValueUSD'] = $TotalInvoiceValueUSD;

		$data['Totals'] = $ArrayPendingPaymentTotals;
		$data['FdaReleaseInfoData'] = $AryRowPendingPaymentReport;

		if ($StmtPendingPaymentsReport) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function build_sorter($key, $dir)
	{
		return function ($a, $b) use ($key, $dir) {
			$t1 = strtotime(is_array($a) ? $a[$key] : $a->$key);
			$t2 = strtotime(is_array($b) ? $b[$key] : $b->$key);
			if ($t1 == $t2)
				return 0;
			return (strtoupper($dir) == 'ASC' ? ($t1 < $t2) : ($t1 > $t2)) ? -1 : 1;
		};
	}

	// function build_sorter($key, $dir)
	// {
	// 	return function ($a, $b) use ($key, $dir) {
	// 		$val1 = trim(is_array($a) ? $a[$key] : $a->$key);
	// 		$val2 = trim(is_array($b) ? $b[$key] : $b->$key);

	// 		// Push empty or '--' values to the end
	// 		if (($val1 == '' || $val1 == '--') && ($val2 == '' || $val2 == '--')) {
	// 			return 0;
	// 		}
	// 		if ($val1 == '' || $val1 == '--') {
	// 			return 1;
	// 		}
	// 		if ($val2 == '' || $val2 == '--') {
	// 			return -1;
	// 		}

	// 		$t1 = strtotime($val1);
	// 		$t2 = strtotime($val2);

	// 		if ($t1 == $t2) {
	// 			return 0;
	// 		}

	// 		return (strtoupper($dir) == 'ASC'
	// 			? ($t1 < $t2)
	// 			: ($t1 > $t2))
	// 			? -1
	// 			: 1;
	// 	};
	// }

	function getPendingNegotiationReport()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Exporter = $IpData['Exporter'];
		$Processor = $IpData['Processor'];
		$Country = $IpData['Country'];
		$Consignee = $IpData['Consignee'];

		$Query = '';



		if ($Exporter != "") {
			$Query .= 'and POD.ExporterID in (' . $Exporter . ')';
		}
		if ($Processor != "") {
			$Query .= 'and POD.ProcessorID=' . $Processor;
		}
		if ($Country != "") {
			$Query .= 'and POD.DestinationCountry=' . $Country;
		}
		if ($Consignee != "") {
			$Query .= 'and POD.ConsigneeID=' . $Consignee;
		}

		$DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";

		$SqlFields = 'ConsigneeName as BuyerName,SCI.StockOutDate,SCI.PiID,CustomsInvNo as InvNo,ISF.ETA_FD,ISF.GetOutFull,SPCL.BnkChrges,SPCL.OtherAmount,SPCL.EtraAmount,
 SPC.Name as FinalDestination,ShippingOnboardDt,EstimatedETA,PaymentName as PaymentTerms,SSDB.DeliveredDate,
 (SELECT sum(Mcs) FROM `ScrmPoGradeData` where InvId=POD.PoID)as NoofCartons';

		$SqlPendingNegotiationReport = "SELECT $SqlFields
 from  ScrmCustomInvoices  SCI 
left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID
left join ScrmPaymentTerms  SPT on SPT.ID=POD.PaymentTerms
left join ScrmShipmentDetailsBreif  SSDB on SSDB.PiID=POD.PoID
left join ScrmPaymentCollections as SPCL on SPCL.PIID=POD.PoID
WHERE  $DateFilter  $Query  
and POD.PoID not  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 ) and TransportedDate!='' and POD.Status not in (2,3,4)

order by ISF.EstimatedETA asc,ISF.ETA_FD asc";

		$StmtPendingNegotiationReport = mysql_query($SqlPendingNegotiationReport, $this->db);

		$TotalInvoiceValueUSD = 0;
		$TotalInvoices = 0;



		$AryWholeData = array();
		$ArySortedData = array();
		while ($RowPendingNegotiationReport = mysql_fetch_array($StmtPendingNegotiationReport, MYSQL_ASSOC)) {
			$AryWholeData[] = $RowPendingNegotiationReport;
			if ($RowPendingNegotiationReport['EstimatedETA'] != '' || $RowPendingNegotiationReport['ETA_FD'] != '') {
				$EstimatedETA = $RowPendingNegotiationReport['EstimatedETA'] ?: $RowPendingNegotiationReport['ETA_FD'];
				$RowPendingNegotiationReport['EstimatedETA'] = $EstimatedETA;
				$ArySortedData[] = $RowPendingNegotiationReport;
			}
		}
		usort($ArySortedData, $this->build_sorter('EstimatedETA', 'ASC'));


		$SqlPendingNegotiationReport = "SELECT $SqlFields
		from  ScrmCustomInvoices  SCI 
	   left join ScrmPoData  POD on POD.PoID=SCI.PiID
	   left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
	   left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
	   left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
	   left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID
	   left join ScrmPaymentTerms  SPT on SPT.ID=POD.PaymentTerms
	   left join ScrmShipmentDetailsBreif  SSDB on SSDB.PiID=POD.PoID
	   left join ScrmPaymentCollections as SPCL on SPCL.PIID=POD.PoID
	   WHERE  $DateFilter  $Query  
	   and POD.PoID not  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 ) and TransportedDate!='' and POD.Status not in (2,3,4)
	   order by SCI.StockOutDate asc";

		$StmtPendingNegotiationReport = mysql_query($SqlPendingNegotiationReport, $this->db);

		while ($row = mysql_fetch_array($StmtPendingNegotiationReport, MYSQL_ASSOC)) {
			if ($row['EstimatedETA'] == '' && $row['ETA_FD'] == '') {
				$ArySortedData[] = $row;
			}
		}


		// Priority order: delivered, ETA, onboard, then stock-out date.
		$getNegotiationSortValue = function ($row) {
			$dateCandidates = array(
				array(0, isset($row['GetOutFull']) ? $row['GetOutFull'] : ''),
				array(1, !empty($row['EstimatedETA']) ? $row['EstimatedETA'] : (isset($row['ETA_FD']) ? $row['ETA_FD'] : '')),
				array(2, isset($row['ShippingOnboardDt']) ? $row['ShippingOnboardDt'] : ''),
				array(3, isset($row['StockOutDate']) ? $row['StockOutDate'] : '')
			);

			foreach ($dateCandidates as $candidate) {
				$value = trim((string) $candidate[1]);
				if ($value === '' || $value === '--' || $value === '0000-00-00') {
					continue;
				}
				$timestamp = strtotime($value);
				// Reject invalid dates and MySQL zero-date placeholders parsed before the Unix epoch.
				if ($timestamp !== false && $timestamp > 0) {
					return array($candidate[0], $timestamp);
				}
			}

			return array(4, PHP_INT_MAX);
		};

		usort($ArySortedData, function ($a, $b) use ($getNegotiationSortValue) {
			$sortA = $getNegotiationSortValue($a);
			$sortB = $getNegotiationSortValue($b);
			if ($sortA[0] != $sortB[0]) {
				return ($sortA[0] < $sortB[0]) ? -1 : 1;
			}
			if ($sortA[1] != $sortB[1]) {
				return ($sortA[1] < $sortB[1]) ? -1 : 1;
			}
			$piA = isset($a['PiID']) ? (int) $a['PiID'] : 0;
			$piB = isset($b['PiID']) ? (int) $b['PiID'] : 0;
			if ($piA == $piB) {
				return 0;
			}
			return ($piA < $piB) ? -1 : 1;
		});


		$TotalValueInUSD = 0;
		$TotalValueInPOUND = 0;
		$TotalValueInEURO = 0;
		$TotalValueInGBP = 0;
		$TotalValueInINR = 0;
		$TotalValueInCAD = 0;


		foreach ($ArySortedData as $RowPendingNegotiationReport) {
			// while($RowPendingNegotiationReport = mysql_fetch_array($StmtPendingNegotiationReport,MYSQL_ASSOC)){

			$PoID = $RowPendingNegotiationReport['PiID'];
			$ShippingOnboardDt = $this->getFormatedDatelines($RowPendingNegotiationReport['ShippingOnboardDt']);
			$EstimatedETA = $this->getFormatedDatelines($RowPendingNegotiationReport['EstimatedETA']);
			$EstimatedETA = $RowPendingNegotiationReport['EstimatedETA'] ?: $RowPendingNegotiationReport['ETA_FD'];
			//$EstimatedETA =$RowPendingNegotiationReport['ETA_FD'] ; 

			$EstimatedETA = $this->getFormatedDatelines($EstimatedETA);
			//$EstimatedETA =$PoID;
			//$DeliveredDate =$this-> getFormatedDatelines($RowPendingNegotiationReport['DeliveredDate']);
			$GetOutFull = $this->getFormatedDatelines($RowPendingNegotiationReport['GetOutFull']);
			// $GetOutFullRaw = $RowPendingNegotiationReport['GetOutFull'];



			$SqlUtilisedValues = "SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
			$StmtUtilisedValues = mysql_query($SqlUtilisedValues, $this->db);
			$RowUtilisedValues = mysql_fetch_array($StmtUtilisedValues, MYSQL_ASSOC);
			$UtilizedValue = $RowUtilisedValues['UtilizedValue'];

			$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
			$InvValueUSD = $ArrayPaymentValues['InvValueUSD'];
			$InvValueINR = $ArrayPaymentValues['InvValueINR'];
			$UnitCurrency = $ArrayPaymentValues['UnitCurrency'];
			$CurrencySymbol = $ArrayPaymentValues['CurrencySymbol'];
			$BankCharges = $RowPendingNegotiationReport['BnkChrges'];
			$ExtraAmount = $RowPendingNegotiationReport['EtraAmount'];
			$OtherAmount = $RowPendingNegotiationReport['OtherAmount'];


			$ShortFall = $InvValueUSD + $ExtraAmount - $BankCharges - $UtilizedValue - $OtherAmount;

			// if ($UnitCurrency != 'USD') {
			// 	switch ($UnitCurrency) {
			// 		default:
			// 			$AryPrices['InvValue' . $UnitCurrency] = $ShortFall;
			// 			break;
			// 	}
			// }
			/* $ArrayPaymentValues = $this->getPaymentValues($PoID);
			$InvValueUSD = $ArrayPaymentValues['InvValueUSD']; */

			switch ($UnitCurrency) {
				case 'USD':
					$TotalValueInUSD += $ShortFall;
					break;
				case 'POUND':
					$TotalValueInPOUND += $ShortFall;
					break;
				case 'EURO':
					$TotalValueInEURO += $ShortFall;
					break;
				case 'GBP':
					$TotalValueInGBP += $ShortFall;
					break;
				case 'INR':
					$TotalValueInINR += $ShortFall;
					break;
				case 'CAD':
					$TotalValueInCAD += $ShortFall;
					break;
			}


			$InvNo = $RowPendingNegotiationReport['CInvNo'];
			$RowPendingNegotiationReport['Sno'] = ++$Sno;
			$RowPendingNegotiationReport['ETADate'] = $EstimatedETA;
			$RowPendingNegotiationReport['OnBoardDate'] = $ShippingOnboardDt;
			$RowPendingNegotiationReport['DeliveredDate'] = $GetOutFull;
			// $RowPendingNegotiationReport['DeliveredDateRaw'] = $GetOutFullRaw;
			$RowPendingNegotiationReport['UnitCurrency'] = $UnitCurrency;
			$RowPendingNegotiationReport['CurrencySymbol'] = $CurrencySymbol;
			// $RowPendingNegotiationReport['TotalInvoiceUSDValue'] = number_format($ShortFall, 2);
			switch ($UnitCurrency) {
				default:
					$RowPendingNegotiationReport['TotalInvoiceCurrencyValue'] = number_format($ShortFall, 2);
					break;
			}
			// $TotalInvoiceValueUSD += $ShortFall;
			$AryRowPendingNegotiationReport[] = $RowPendingNegotiationReport;
		}

		// Preserve the existing date priority; use stock-out date only for the final undated records.
		$getVisibleNegotiationSortValue = function ($row) {
			$dateCandidates = array(
				array(0, isset($row['DeliveredDate']) ? $row['DeliveredDate'] : ''),
				array(1, isset($row['ETADate']) ? $row['ETADate'] : ''),
				array(2, isset($row['OnBoardDate']) ? $row['OnBoardDate'] : ''),
				array(3, isset($row['StockOutDate']) ? $row['StockOutDate'] : '')
			);

			foreach ($dateCandidates as $candidate) {
				$value = trim((string) $candidate[1]);
				if ($value === '' || $value === '--' || $value === '0000-00-00') {
					continue;
				}
				$timestamp = strtotime($value);
				if ($timestamp !== false && $timestamp > 0) {
					return array($candidate[0], $timestamp);
				}
			}

			return array(4, PHP_INT_MAX);
		};

		if (!empty($AryRowPendingNegotiationReport)) {
			usort($AryRowPendingNegotiationReport, function ($a, $b) use ($getVisibleNegotiationSortValue) {
				$sortA = $getVisibleNegotiationSortValue($a);
				$sortB = $getVisibleNegotiationSortValue($b);
				if ($sortA[0] != $sortB[0]) {
					return ($sortA[0] < $sortB[0]) ? -1 : 1;
				}
				if ($sortA[1] != $sortB[1]) {
					return ($sortA[1] < $sortB[1]) ? -1 : 1;
				}
				$piA = isset($a['PiID']) ? (int) $a['PiID'] : 0;
				$piB = isset($b['PiID']) ? (int) $b['PiID'] : 0;
				if ($piA == $piB) {
					return 0;
				}
				return ($piA < $piB) ? -1 : 1;
			});
			foreach ($AryRowPendingNegotiationReport as $index => $reportRow) {
				$AryRowPendingNegotiationReport[$index]['Sno'] = $index + 1;
			}
			$Sno = count($AryRowPendingNegotiationReport);
		}

		$ArrayPendingNegotiationTotals['TotalInvoices'] = $Sno;
		// $ArrayPendingNegotiationTotals['TotalInvoiceValueUSD'] = number_format($TotalInvoiceValueUSD, 2);
		$ArrayPendingNegotiationTotals['TotalInvoiceValueUSD'] = number_format($TotalValueInUSD, 2);
		$ArrayPendingNegotiationTotals['TotalInvoiceValuePOUND'] = number_format($TotalValueInPOUND, 2);
		$ArrayPendingNegotiationTotals['TotalInvoiceValueEURO'] = number_format($TotalValueInEURO, 2);
		$ArrayPendingNegotiationTotals['TotalInvoiceValueGBP'] = number_format($TotalValueInGBP, 2);
		$ArrayPendingNegotiationTotals['TotalInvoiceValueINR'] = number_format($TotalValueInINR, 2);
		$ArrayPendingNegotiationTotals['TotalInvoiceValueCAD'] = number_format($TotalValueInCAD, 2);

		$data['Totals'] = $ArrayPendingNegotiationTotals;

		// usort($AryRowPendingNegotiationReport, $this->build_sorter('DeliveredDateRaw', 'ASC'));

		$data['PendingNegotiationReportData'] = $AryRowPendingNegotiationReport;

		if ($StmtPendingNegotiationReport) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getBillRealizationReport()
	{
		$Error = true;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];



		$DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";

		$SqlFields = 'SCI.PiID,CustomsInvNo,SCI.CInvDate,PoNo,ShippingOnboardDt,
ConsigneeName,ETA_POD,FDAClearDate,SPCS.DueDte,
(SELECT BankRefNo FROM `ScrmCollectionBreakUP` as SCB WHERE POD.PoID=SCB.PiID order by ColBrkID desc LIMIT 1) as ReferenceNo,
 (SELECT SCB.ColDate 
     FROM ScrmCollectionBreakUP AS SCB 
     WHERE POD.PoID = SCB.PiID 
     ORDER BY SCB.ColBrkID DESC 
     LIMIT 1) AS ColDate,
GetOutFull,EmptyReturn';

		$SqlBillRealizationReport = "SELECT $SqlFields
 from  ScrmCustomInvoices  SCI 
left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID
left join ScrmShipmentDetailsBreif  SSDB on SSDB.PiID=POD.PoID
left join ScrmPaymentCollections as SPCS on POD.PoID=SPCS.PIID
left join ScrmRealizationDetails as SRD on POD.PoID=SRD.PoID
WHERE  $DateFilter and  SSDB.TransportedDate!=''
and POD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 )
and SCI.PaymentType='Negotiations' and SRD.RezStatus=0 
order by POD.PoID desc";


		$StmtBillRealizationReport = mysql_query($SqlBillRealizationReport, $this->db);

		while ($RowBillRealizationReport = mysql_fetch_array($StmtBillRealizationReport, MYSQL_ASSOC)) {


			$PoID = $RowBillRealizationReport['PiID'];
			$CInvDate = $this->getFormatedDatelines($RowBillRealizationReport['CInvDate']);
			$GetOutFull = $this->getFormatedDatelines($RowBillRealizationReport['GetOutFull']);

			$ShippingOnboardDt = $this->getFormatedDatelines($RowBillRealizationReport['ShippingOnboardDt']);
			$ETA_POD = $this->getFormatedDatelines($RowBillRealizationReport['ETA_POD']);
			$FDAClearDate = $this->getFormatedDatelines($RowBillRealizationReport['FDAClearDate']);
			$EmptyReturn = $this->getFormatedDatelines($RowBillRealizationReport['EmptyReturn']);
			$DueOn = $this->getFormatedDatelines($RowBillRealizationReport['DueDte']);
			$ColDate = $this->getFormatedDatelines($RowBillRealizationReport['ColDate']);




			$BillRealizationReport['Sno'] = ++$Sno;
			$BillRealizationReport['InvoiceNo'] = $RowBillRealizationReport['CustomsInvNo'];
			$BillRealizationReport['InvoiceDate'] = $CInvDate;
			$BillRealizationReport['PoNo'] = $RowBillRealizationReport['PoNo'];
			$BillRealizationReport['Buyer'] = $RowBillRealizationReport['ConsigneeName'];
			$BillRealizationReport['GateOutFullDate'] = $GetOutFull;
			$BillRealizationReport['EmptyCYDate'] = $EmptyReturn;
			$BillRealizationReport['PODDate'] = $ETA_POD;
			$BillRealizationReport['ReferenceNo'] = $RowBillRealizationReport['ReferenceNo'];
			$BillRealizationReport['FDACleared'] = $FDAClearDate;
			$BillRealizationReport['DueOn'] = $DueOn;
			//$BillRealizationReport['BankDueOn']=$BankDueOn; 
			$BillRealizationReport['BankDueOn'] = $DueOn;
			$BillRealizationReport['OnBoardDate'] = $ShippingOnboardDt;
			//$BillRealizationReport['NegotiationON']=$NegotiationON;
			$BillRealizationReport['NegotiationON'] = $RowBillRealizationReport['ColDate'];

			$ArrayPaymentValues = $this->getPaymentValues($PoID, '');
			$BillRealizationReport['InvoiceValueUSD'] = $ArrayPaymentValues['InvValueUSD'];
			$BillRealizationReport['InvoiceValueINR'] = number_format($ArrayPaymentValues['InvValueINR'], 2);


			$AryRowBillRealizationReport[] = $BillRealizationReport;
		}



		$data['BillRealizationReportData'] = $AryRowBillRealizationReport;

		if ($StmtBillRealizationReport) {
			$data['Error'] = null;
			$data['Msg'] = "Success";
			$records = array('Data' => $data, );
			$this->response($this->json($records), 200);
		} else {
			$data['Error'] = "Unable To Fetch Data";
			$records = array('Data' => $data);
			$this->response($this->json($records), 200);
		}
	}

	function getFDAExaminationReportData()
	{

		$Error = false;
		$RawIpData = file_get_contents('php://input');

		$IpData = json_decode($RawIpData, true);


		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Exporter = $IpData['Exporter'];
		//$Cancelled=$IpData['Cancelled'];
		$Status = $IpData['Status'];
		$FDAExmStatus = $IpData['FDAEStatus'];

		// print_r($IpData);
		// exit;



		$Query = '';


		if ($Exporter != "") {
			$Query = ' and SPD.ExporterID in (' . $Exporter . ')';
		}

		if ($Status != "") {
			$Query .= " and ISF.FDAContainerStatus ='$Status'";
		}
		if (!empty($FDAExmStatus)) {
			if ($FDAExmStatus == 'Yes') {
				$Query .= " and ISF.FDAExam in ('Other Reasons','Yes')";
			} else {
				$Query .= " and ISF.FDAExam ='$FDAExmStatus'AND ISF.IsFDAExamSelected = 1";
			}
		}

		// if (!empty($Status)) {
		// 	if ($Status === 'Yes' || $Status === 'Proceed') {
		// 		$Query .= " AND ISF.FDAExam = '$Status'";
		// 	} else {
		// 		$Query .= " AND ISF.FDAContainerStatus = '$Status'";
		// 	}
		// } else {
		// 	$Query .= " and ISF.FDAExam in ('Other Reasons','Yes')";
		// }
		/*$DateStart=$Year.'-04-01';
																		$DateEnd=($Year+1).'-03-31';*/
		//$DateFilter=$this->getFinancialYear($Year); 

		$DateFilter = "BETWEEN '$FromDate' and '$ToDate'";



		if (!$Error) {

			$SqlFields = "SCI.CustomsInvNo,SCI.CInvDate,ISF.FDAContainerStatus,ISF.EstimatedETA
,SPD.ExporterID,SPD.ProcessorID,SPD.InvNo,ConsigneeName,SCI.PiID,PoNo,PODate,PreparedBy,SCL.name as DestinationCountry,
ISF.ContainerNo as ctnnmbr,ISF.FDAEntryNumber,ISF.FDAExam,ISF.SampleCollectionDate,ISF.SampleReceivedDate,
ISF.NoticeDate1,ISF.NoticeDate2,ISF.NoticeDate3,ISF.NoticeDate4,ISF.FDARemarks,ConsigneeCode,ISF.FDAEUReleaseCFIADate";

			/* $SqlFDAExminationReportData = "SELECT $SqlFields
																																																																																																																																																FROM ScrmISFDetails  ISF  
																																																																																																																																																left join ScrmCustomInvoices as  SCI on ISF.PIID=SCI.PiID
																																																																																																																																																left join ScrmPoData as SPD  on SPD.PoID=SCI.PiID
																																																																																																																																																LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
																																																																																																																																																LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
																																																																																																																																																LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
																																																																																																																																																left join ScrmCountriesList  SCL on SCL.id=SPD.DestinationCountry
																																																																																																																																																WHERE  SCI.CInvDate $DateFilter $Query";*/

			$SqlFDAExminationReportData = "SELECT $SqlFields
FROM ScrmISFDetails  ISF  
left join ScrmCustomInvoices as  SCI on ISF.PIID=SCI.PiID
left join ScrmPoData as SPD  on SPD.PoID=SCI.PiID
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
left join ScrmCountriesList  SCL on SCL.id=SPD.DestinationCountry
WHERE  SCI.CInvDate $DateFilter $Query order by CInvNo asc";
			$StmtFDAExminationReport = mysql_query($SqlFDAExminationReportData, $this->db);

			$i = '1';
			while ($RowFDAExminationReport = mysql_fetch_array($StmtFDAExminationReport, MYSQL_ASSOC)) {





				$ArrayRowFDAExminationReport = array();
				$ArrayRowFDAExminationReport['sno'] = $i;
				$InvNo = $RowFDAExminationReport['CustomsInvNo'];
				$InvDate = $RowFDAExminationReport['CInvDate'];
				$ExpID = $RowFDAExminationReport['ExporterID'];
				$ProcID = $RowFDAExminationReport['ProcessorID'];
				$PoID = $RowFDAExminationReport['PiID'];
				$EstimatedETA = $RowFDAExminationReport['EstimatedETA'];
				$SampleReceivedDate = $this->getFormatedDatelines($RowFDAExminationReport['SampleReceivedDate']);
				$SampleCollectionDate = $this->getFormatedDatelines($RowFDAExminationReport['SampleCollectionDate']);
				$NoticeDate1 = $this->getFormatedDate($RowFDAExminationReport['NoticeDate1']);
				$NoticeDate2 = $this->getFormatedDate($RowFDAExminationReport['NoticeDate2']);
				$NoticeDate3 = $this->getFormatedDate($RowFDAExminationReport['NoticeDate3']);
				$NoticeDate4 = $this->getFormatedDate($RowFDAExminationReport['NoticeDate4']);

				//$this->CheckEmptyDate($NoticeDate1);
				$ArrayNoticeDates = array_filter([$NoticeDate1, $NoticeDate2, $NoticeDate3, $NoticeDate4]);

				$NoticeDates = implode(',<br>', $ArrayNoticeDates);

				$FDAExam = $RowFDAExminationReport['FDAExam'];

				if ($FDAExam == 'Yes') {
					$Examination = 'FDA';
				}

				$ActualEtaDate = $this->getEtaFDDate($PoID);
				if ($EstimatedETA == '') {
					$EstimatedETA = $this->getFormatedDatelines($ActualEtaDate);
				} else {
					$EstimatedETA = $this->getFormatedDatelines($EstimatedETA);
				}

				// $SqlVarietyMcs = "SELECT Description,Variety,
				// (SELECT GROUP_CONCAT(NULLIF(DATE_FORMAT(ProductionDate,'%d-%m-%Y'),''),'') FROM `ScrmCIGradeData` where InvId='$PoID') as ProductionDate
				// 			 FROM `ScrmPoGradeData`  WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand 
				// 			 Order by ScrmPoGradeData.ID asc";

				$SqlVarietyMcs = "SELECT GROUP_CONCAT(SPVL.Grade,'-',ProductDescription ORDER BY SPGD.ID ASC, ABS(SPVL.Grade)) as Variety,
				GROUP_CONCAT(Brand) as brand FROM  `ScrmPoGradeData` as SPGD
				left join `ScrmPIVarietyList`  as  SPVL on  SPVL.VarietyID=SPGD.VarietyID WHERE InvId='$PoID'";


				$StmtVarietyMcs = mysql_query($SqlVarietyMcs, $this->db);
				unset($AryDescription);
				unset($AryVariety);
				while ($VarietyMcsRow = mysql_fetch_array($StmtVarietyMcs, MYSQL_ASSOC)) {
					$AryDescription[] = $VarietyMcsRow['Description'];
					$AryVariety[] = $VarietyMcsRow['Variety'];
					$ProductionDate = $VarietyMcsRow['ProductionDate'];

					$Variety = implode(',', $AryVariety);
					$Variety = str_replace(',', ',<br>', $Variety);
					$Description = implode(',', $AryDescription);
				}
				if ($Description == '') {
					$Description = $Variety;
				} else {
					$Description;
				}

				$Remarks = $RowFDAExminationReport['FDARemarks'];

				/***response***/
				$ArrayRowFDAExminationReport['InvNo'] = $InvNo;
				$ArrayRowFDAExminationReport['BuyerPoNo'] = $RowFDAExminationReport['ConsigneeCode'];

				// $ArrayRowFDAExminationReport['BuyerPoNo'] = $RowFDAExminationReport['ConsigneeCode'] . ' ,<br>' . $RowFDAExminationReport['PoNo'];
				$ArrayRowFDAExminationReport['Variety'] = $Description;
				$ArrayRowFDAExminationReport['DestinationCountry'] = $RowFDAExminationReport['DestinationCountry'];
				$ArrayRowFDAExminationReport['Containerno'] = $RowFDAExminationReport['ctnnmbr'];
				$ArrayRowFDAExminationReport['ArrivalDate'] = $EstimatedETA;
				$ArrayRowFDAExminationReport['EntryNo'] = $RowFDAExminationReport['FDAEntryNumber'];
				$ArrayRowFDAExminationReport['Examination'] = $Examination;
				$ArrayRowFDAExminationReport['NoticeDate'] = $NoticeDates;
				//$ArrayRowFDAExminationReport['NoticeDate POPUP']='doubt'; 
				$ArrayRowFDAExminationReport['SampleCollectionDate'] = $SampleCollectionDate;
				$ArrayRowFDAExminationReport['SampleReceivedDate'] = $SampleReceivedDate;
				//$ArrayRowFDAExminationReport['Notes']='doubt';
				$ArrayRowFDAExminationReport['ProductionDates'] = $ProductionDate;
				$ArrayRowFDAExminationReport['Status'] = $RowFDAExminationReport['FDAContainerStatus'];
				$ArrayRowFDAExminationReport['FDAExam'] = $FDAExam;
				$ArrayRowFDAExminationReport['Remarks'] = $Remarks;
				$ArrayRowFDAExminationReport['FDAEUReleaseCFIADate'] = $this->getFormatedDatelines($RowFDAExminationReport['FDAEUReleaseCFIADate']);
				/***response***/

				$i++;
				$Response[] = $ArrayRowFDAExminationReport;
			}

			$data['getFDAExminationReportData'] = $Response;
			// var_dump($Response);
			if ($StmtFDAExminationReport) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getMpedaReportData()
	{


		$Error = false;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);


		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Exporter = $IpData['Exporter'];


		$Query = '';

		if ($Exporter != '') {
			$Query = ' and SPD.ExporterID=' . $Exporter;
		}

		$DateFilter = "SCOM.ShippingBillDate between '$FromDate' and '$ToDate'";

		if (!$Error) {

			$SqlFields = "SCI.CustomsInvNo,SCI.PiID,QtyUnit,
SCI.CInvDate,SPD.ExporterID,SPC.Name as PortOfFinalDestination,
SPD.ProcessorID,SCOM.ShippingBillNo , SCOM.ShippingBillDate, SCOM.SBFOBUSD,SCOM.SBFOBINR ,SPD.ExporterID as ExporterID";

			$SqlMpedaReportData = "SELECT  $SqlFields
 FROM `ScrmCustomInvoices` as SCI 
 LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
 left join ScrmShippingBillDetails as SCOM on SPD.PoID=SCOM.InvID 
 LEFT JOIN  ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
 left JOIN  ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
 left JOIN ScrmPortCities as SPC on SPC.PortId=SPD.FinalDestination 
 where  $DateFilter  $Query  and SCOM.ShippingBillNo!='' and SPD.Status not in (2,3,4,7)";

			$StmtMpedaReportData = mysql_query($SqlMpedaReportData, $this->db);


			$i = 0;
			$TotalNetWgtKgs = 0;
			$TotalFOBValueINR = 0;
			$TotalFOBValueInForeignCurrency = 0;

			while ($RowMpedaReportData = mysql_fetch_array($StmtMpedaReportData, MYSQL_ASSOC)) {


				$PoID = $RowMpedaReportData['PiID'];

				$SqlVarietyMcs = "SELECT Description,Variety
					FROM `ScrmPoGradeData`  WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand 
					Order by ScrmPoGradeData.ID asc";
				$StmtVarietyMcs = mysql_query($SqlVarietyMcs, $this->db);

				unset($AryDescription);
				unset($AryVariety);
				$VarietyCount = 0;
				while ($VarietyMcsRow = mysql_fetch_array($StmtVarietyMcs, MYSQL_ASSOC)) {
					$AryDescription[] = $VarietyMcsRow['Description'];
					$AryVariety[] = '(' . ++$VarietyCount . ')' . $VarietyMcsRow['Variety'];
					//$Variety=implode('',$AryVariety);
					$Variety = implode('<br>', $AryVariety);
					//echo $Description=implode(',',$AryDescription); 
				}
				$Description = $Variety;
				/*$Description=$Variety;
																																			if($Description==''){

																																			$Description=$Variety;
																																				}
																																				else{
																																				$Description; 
																																				}
																																			*/



				$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				  sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				  sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				  sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


				$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
		           sum(Price) as Price,			
		           sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
		           sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` 
		           as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
				$StmtTotal = mysql_query($SqlTotal, $this->db);
				$RowTotal = mysql_fetch_array($StmtTotal, MYSQL_ASSOC);

				$NetWgtLbs = floatval($RowTotal['NetWgtLbs']);
				//$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
				//$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

				$RowMpedaReportData['QtyUnit'];

				if ($RowMpedaReportData['QtyUnit'] == 'KGS') {

					$NetWgtKgs = floatval($RowTotal['NetWgtKgs']);
					//$Price = $RowTotal['PriceKgs']; 
				} else {

					$NetWgtKgs = floatval($RowTotal['NetWgtLbs'] * 0.454);
					//$Price = $RowTotal['PriceLbs'];
				}
				//response//
				$ArrayMpedaReport['sno'] = ++$i;
				$ArrayMpedaReport['ShippingBillNo'] = $RowMpedaReportData['ShippingBillNo'];
				$date = date_create($RowMpedaReportData['ShippingBillDate']);
				$ArrayMpedaReport['ShippingBillDate'] = date_format($date, "d-m-Y");
				$ArrayMpedaReport['InvoiceNo'] = $RowMpedaReportData['CustomsInvNo'];
				$ArrayMpedaReport['Item'] = $Description;
				$ArrayMpedaReport['MPEDAqualityinkgs'] = $NetWgtKgs;
				// $TotalNetWgtKgs += $RowTotal['NetWgtKgs'];
				$TotalNetWgtKgs += $NetWgtKgs;

				$ArrayMpedaReport['FOBValueINR'] = number_format($RowMpedaReportData['SBFOBINR'], 2);
				$TotalFOBValueINR += $RowMpedaReportData['SBFOBINR'];

				$ArrayMpedaReport['FOBValueInForeignCurrency'] = number_format($RowMpedaReportData['SBFOBUSD'], 2);
				$TotalFOBValueInForeignCurrency += $RowMpedaReportData['SBFOBUSD'];

				$ArrayMpedaReport['PortOfDestination'] = $RowMpedaReportData['PortOfFinalDestination'];
				//response//
				//$i++;

				$Response[] = $ArrayMpedaReport;
			}
			$ArrayTotals['TotalInvoices'] = $i;
			$ArrayTotals['TotalNetWgtKgs'] = $TotalNetWgtKgs;
			$ArrayTotals['TotalFOBValueINR'] = number_format($TotalFOBValueINR, 2);
			$ArrayTotals['TotalFOBValueInForeignCurrency'] = number_format($TotalFOBValueInForeignCurrency, 2);

			$data['Totals'] = $ArrayTotals;
			$data['getMpedaReportData'] = $Response;
			if ($StmtMpedaReportData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function getShipmentReportData()
	{

		$Error = false;
		$RawIpData = file_get_contents('php://input');

		$IpData = json_decode($RawIpData, true);


		$FromDate = $IpData['FromDate'];
		$Exporter = $IpData['Exporter'];
		$ToDate = $IpData['ToDate'];
		$Country = $IpData['Country'];
		// $DocType = 'CMI';


		$Query = '';


		if ($Exporter != "") {
			$Query .= ' and SPD.ExporterID in (' . $Exporter . ')';
		}

		if ($Country != "") {
			$Query .= ' and SPD.DestinationCountry=' . $Country;
		}



		$DateFilter = "BETWEEN '$FromDate' and '$ToDate'";



		if (!$Error) {

			$SqlFields = "SCI.CustomsInvNo,SCI.CInvDate,QtyUnit,PoNo,PODate,ConsigneeName,DeliveryTerms,ShippingTerms,SPC.Name as PortofLoading,StockOutDate,
SPFD.Name as DischargePortName,SPF.Name as PortofFinalDestination,ShippingOnboardDt,ShippingBillNo,ShippingBillDate,BLDate,BLNo,ISF.ContainerNo,SSBD.ExchangeRate as ShippingBillExchangeRate,
SPCL.name as PortofLoadingCountry,SPFDC.name as PortofDischargeCountry,
SPT.PaymentName as PoPaymentTerm,SCPT.PaymentName as CommercialPaymentTerm,
SCL.name as DestinationCountry,SCL.ShowFrightCharge,SCI.PiID,SCI.LinerSealNo,SCI.CustomSealNo,SLM.LinerName,SSBD.LEODate,
SCOD.Freight as CustomFreight,SCI.ExRate as ExRate,ISF.FDAEntryNumber,ISF.FDAEntryDate";
			$SqlShipmentReportData = "SELECT $SqlFields,SSTM.*,SPD.DestinationCountry as DCountryID
FROM ScrmCustomInvoices as  SCI 
left join ScrmPoData as SPD  on SPD.PoID=SCI.PiID
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortofLoading 
LEFT JOIN ScrmCountriesList as SPCL on SPCL.id=SPC.Country 
LEFT JOIN ScrmPortCities as SPF on SPF.PortId=SPD.FinalDestination 
LEFT JOIN ScrmPortCities as SPFD on SPFD.PortId=SPD.PortOfDischarge 
left join ScrmCountriesList  SCL on SCL.id=SPD.DestinationCountry
left join ScrmCountriesList  SPFDC on SPFDC.id=SPFD.Country
left join ScrmISFDetails  ISF on ISF.PIID=SPD.PoID
left join ScrmShippingBillDetails  SSBD on SSBD.InvID=SPD.PoID
left join ScrmCommercialData  SCOD on SCOD.PoID=SPD.PoID
left join ScrmPaymentTerms  SPT on SPT.ID=SPD.PaymentTerms
left join ScrmPaymentTerms  SCPT on SCPT.ID=SCOD.ComPaymentTerms
left join ScrmLinerMaster  SLM on SLM.LinerID=SCI.ShippingLineID
LEFT JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
WHERE  SCI.CInvDate $DateFilter and SPD.Status not in (2,3,4) $Query";

			// echo $SqlShipmentReportData;
			// exit;
			$StmtShipmentReportData = mysql_query($SqlShipmentReportData, $this->db);

			$i = '0';
			$TotalAntiDumpingDuty = 0;
			$TotalCountervailingDuty = 0;
			$TotalMerchantDuty = 0;
			$TotalHarborDuty = 0;
			$TotalReciprocalTariffsFee = 0;
			$TotalCustomFreight = 0;
			$TotalNetWeightKgs = 0;
			$TotalNetWeightLbs = 0;
			$TotalInvoiceValueUSD = 0;
			$TotalFOBValueUSD = 0;
			$TotalInvoices = 0;


			while ($RowShipmentReportData = mysql_fetch_array($StmtShipmentReportData, MYSQL_ASSOC)) {
				$ArrayShipmentReportData = array();
				$FDAEntryNumber = '';
				$FDAEntryDate = '';
				$InvNo = $RowShipmentReportData['CustomsInvNo'];
				$InvDate = $this->getFormatedDatelines($RowShipmentReportData['CInvDate']);
				$PODate = $this->getFormatedDatelines($RowShipmentReportData['PODate']);
				$ShippingOnboardDt = $this->getFormatedDatelines($RowShipmentReportData['ShippingOnboardDt']);
				$ShippingBillDate = $this->getFormatedDatelines($RowShipmentReportData['ShippingBillDate']);
				$StockOutDate = $this->getFormatedDatelines($RowShipmentReportData['StockOutDate']);
				$BLDate = $this->getFormatedDatelines($RowShipmentReportData['BLDate']);
				$PoID = $RowShipmentReportData['PiID'];
				$SBFOBUSD = $RowShipmentReportData['SBFOBUSD'];
				$ShippingBillExchangeRate = $RowShipmentReportData['ShippingBillExchangeRate'];
				$TermName = $RowShipmentReportData['TermName'];
				$DCountryID = $RowShipmentReportData['DCountryID'];
				$ArrayPaymentValues = $this->getPaymentValues($PoID, $DocType);
				// echo print_r($ArrayPaymentValues);
				// exit;
				//$ArrayPaymentValues=$this->getPaymentValues($PoID);
				$InvValueUSD = $ArrayPaymentValues['InvValueUSD'];
				$FOBValueUSD = $ArrayPaymentValues['FOBValueUSD'];
				$FrieghtCharge = $ArrayPaymentValues['FrieghtCharge'];

				if ($RowShipmentReportData['CommercialPaymentTerm'] == '') {
					$PaymentName = $RowShipmentReportData['PoPaymentTerm'];
				} else {
					$PaymentName = $RowShipmentReportData['CommercialPaymentTerm'];
				}
				$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
			 sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
			 sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
			 sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs
			 ,GROUP_CONCAT(PGD.`Grade` ORDER BY PGD.ID ASC SEPARATOR ',<br>') AS Grade
			 ,GROUP_CONCAT(
					CASE
						WHEN PGD.Description <> '' THEN PGD.Description
						ELSE (
							SELECT SPVL.BuyerProdDescription
							FROM ScrmPIVarietyList AS SPVL
							WHERE SPVL.VarietyID = PGD.VarietyID
						)
					END
					ORDER BY PGD.ID ASC
					SEPARATOR ',<br>'
				) AS Description
			 ,GROUP_CONCAT(PS.`PackingStyle` ORDER BY PGD.ID ASC SEPARATOR ',<br>') AS PackingStyle";


				$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt,
			 sum(Price) as Price,sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			 sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` 
			 as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
				$StmtTotal = mysql_query($SqlTotal, $this->db);
				$RowTotal = mysql_fetch_array($StmtTotal, MYSQL_ASSOC);
				$NetWgtLbs = $RowTotal['NetWgtLbs'];

				$ArrayShipmentReportData['Grade'] = $RowTotal['Grade'];
				$ArrayShipmentReportData['ProductDescription'] = $RowTotal['Description'];
				$ArrayShipmentReportData['PackingStyle'] = $RowTotal['PackingStyle'];
				if ($RowShipmentReportData['QtyUnit'] == 'KGS') {

					$NetWgtKgs = $RowTotal['NetWgtKgs'];
				} else {

					$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
				}

				// echo print_r($RowShipmentReportData);exit;
				/***response***/
				if ($RowShipmentReportData['ShowFrightCharge'] == 1 && ($RowShipmentReportData['DCountryID'] == 157 && $RowShipmentReportData['ShippingTerms'] == 'DDP')) {
					// if ($RowShipmentReportData['ShowFrightCharge'] == 1 && $RowShipmentReportData['ShowFrieght'] == 1 && ($RowShipmentReportData['DCountryID'] == 157 && $RowShipmentReportData['ShippingTerms'] == 'DDP')) {
					$ArrayShipmentReportData['AntiDumpingDuty'] = $ArrayPaymentValues['AntiDumpingDuty'];
					$ArrayShipmentReportData['MerchantDuty'] = $ArrayPaymentValues['MerchantDuty'];
					$ArrayShipmentReportData['HarborDuty'] = $ArrayPaymentValues['HarborDuty'];
					$ArrayShipmentReportData['CountervailingDuty'] = $ArrayPaymentValues['CountervailingDuty'];
					$ArrayShipmentReportData['ReciprocalTariffsFee'] = $ArrayPaymentValues['ReciprocalTariffsFee'];
					$ArrayShipmentReportData['TariffFee'] = $ArrayPaymentValues['TariffFee'];
					$TotalAntiDumpingDuty += $ArrayPaymentValues['AntiDumpingDuty'];
					$TotalCountervailingDuty += $ArrayPaymentValues['CountervailingDuty'];
					$TotalMerchantDuty += $ArrayPaymentValues['MerchantDuty'];
					$TotalHarborDuty += $ArrayPaymentValues['HarborDuty'];
					$TotalReciprocalTariffsFee += $ArrayPaymentValues['ReciprocalTariffsFee'];
					$TotalTariffFee += $ArrayPaymentValues['TariffFee'];

					$FrieghtCharge = $ArrayPaymentValues['FrieghtCharge'];
					$FDAEntryNumber = $RowShipmentReportData['FDAEntryNumber'];
					$FDAEntryDate = $RowShipmentReportData['FDAEntryDate'];
				} else {
					$FOBValueUSD = $ArrayPaymentValues['InvValueUSDWithOutDuties'];
				}

				// echo $FOBValueUSD;
				// exit;

				if ($DCountryID == 26 && $TermName == 'CIF') {
					$InsurancePerc = $ArrayPaymentValues['InsurancePerc'] ? $ArrayPaymentValues['InsurancePerc'] : $this->getInsurancePerc($DCountryID);
					$InsuranceValue = round(($InvValueUSD * $InsurancePerc) / 100, 2);
					$FOBValueUSD = $InvValueUSD - $FrieghtCharge - $InsuranceValue;
				}

				$ArrayShipmentReportData['CustomFreight'] = $ArrayPaymentValues['CustomInvoiceFrieghtCharge'];
				$ArrayShipmentReportData['ArrayPaymentValues'] = $ArrayPaymentValues;
				$ArrayShipmentReportData['sno'] = ++$i;
				$ArrayShipmentReportData['InvNo'] = $InvNo;
				$ArrayShipmentReportData['InvoiceDate'] = $InvDate;
				$ArrayShipmentReportData['PoNo'] = $RowShipmentReportData['PoNo'];
				$ArrayShipmentReportData['PoDate'] = $PODate;
				$ArrayShipmentReportData['StockOutDate'] = $StockOutDate;
				$ArrayShipmentReportData['Consignee'] = $RowShipmentReportData['ConsigneeName'];
				$ArrayShipmentReportData['CYSD'] = $RowShipmentReportData['DeliveryTerms'];
				$ArrayShipmentReportData['TermsofDelivery'] = $RowShipmentReportData['ShippingTerms'];
				$ArrayShipmentReportData['TermsofPayment'] = $PaymentName;
				$ArrayShipmentReportData['PortofLoading'] = $RowShipmentReportData['PortofLoading'] . ' , ' . $RowShipmentReportData['PortofLoadingCountry'];
				$ArrayShipmentReportData['Country'] = $RowShipmentReportData['DestinationCountry'];
				$ArrayShipmentReportData['PortOfDischarge'] = $RowShipmentReportData['DischargePortName'] . ' , ' . $RowShipmentReportData['PortofDischargeCountry'];
				$ArrayShipmentReportData['FinalDestination'] = $RowShipmentReportData['PortofFinalDestination'];
				$ArrayShipmentReportData['OnBoardDate'] = $ShippingOnboardDt;
				$ArrayShipmentReportData['ShippingBillNo'] = $RowShipmentReportData['ShippingBillNo'];
				$ArrayShipmentReportData['ShippingBillDate'] = $ShippingBillDate;
				$ArrayShipmentReportData['ShippingBillExchangeRate'] = $ShippingBillExchangeRate;
				$ArrayShipmentReportData['ExRate'] = $RowShipmentReportData['ExRate'];
				$ArrayShipmentReportData['BLNo'] = $RowShipmentReportData['BLNo'];
				$ArrayShipmentReportData['BLDate'] = $BLDate;
				$ArrayShipmentReportData['Containerno'] = $RowShipmentReportData['ContainerNo'];
				// $ArrayShipmentReportData['QuantityNetWeightKgs'] = number_format($NetWgtKgs, 2);
				// $ArrayShipmentReportData['QuantityNetWeightLbs'] = number_format($NetWgtLbs, 2);
				$ArrayShipmentReportData['QuantityNetWeightKgs'] = $ArrayPaymentValues['NetWgtKgs'] ?: number_format($NetWgtKgs, 2);
				$ArrayShipmentReportData['QuantityNetWeightLbs'] = $ArrayPaymentValues['NetWgtLbs'] ?: number_format($NetWgtLbs, 2);
				$ArrayShipmentReportData['InvoiceValueUSD'] = number_format($InvValueUSD, 2);
				$ArrayShipmentReportData['FOBValue'] = number_format($FOBValueUSD, 2);
				$ArrayShipmentReportData['Freight'] = number_format($FrieghtCharge, 2);
				$ArrayShipmentReportData['LinerSealNo'] = $RowShipmentReportData['LinerSealNo'];
				$ArrayShipmentReportData['CustomSealNo'] = $RowShipmentReportData['CustomSealNo'];
				$ArrayShipmentReportData['FDAEntryNumber'] = $FDAEntryNumber;
				$ArrayShipmentReportData['FDAEntryDate'] = $this->getFormatedDatelines($FDAEntryDate);

				$ArrayShipmentReportData['LEODate'] = $RowShipmentReportData['LEODate'];
				$ArrayShipmentReportData['LinerName'] = $RowShipmentReportData['LinerName'];

				$TotalCustomFreight += $RowShipmentReportData['CustomFreight'];
				$TotalNetWeightKgs += $NetWgtKgs;
				$TotalNetWeightLbs += $NetWgtLbs;
				$TotalInvoiceValueUSD += $InvValueUSD;
				$TotalFOBValueUSD += $FOBValueUSD;


				/***response***/


				$Response[] = $ArrayShipmentReportData;
			}

			$ArrayTotals['TotalInvoices'] = $i;
			$ArrayTotals['TotalAntiDumpingDuty'] = number_format($TotalAntiDumpingDuty, 2);
			$ArrayTotals['TotalCountervailingDuty'] = number_format($TotalCountervailingDuty, 2);
			$ArrayTotals['TotalMerchantDuty'] = number_format($TotalMerchantDuty, 2);
			$ArrayTotals['TotalHarborDuty'] = number_format($TotalHarborDuty, 2);
			$ArrayTotals['TotalReciprocalTariffsFee'] = number_format($TotalReciprocalTariffsFee, 2);
			$ArrayTotals['TotalTariffFee'] = number_format($TotalTariffFee, 2);
			$ArrayTotals['TotalCustomFreight'] = number_format($TotalCustomFreight, 2);
			$ArrayTotals['TotalNetWeightKgs'] = number_format($TotalNetWeightKgs, 2);
			$ArrayTotals['TotalNetWeightLbs'] = number_format($TotalNetWeightLbs, 2);
			$ArrayTotals['TotalInvoiceValueUSD'] = number_format($TotalInvoiceValueUSD, 2);
			$ArrayTotals['TotalFOBValueUSD'] = number_format($TotalFOBValueUSD, 2);

			$data['Totals'] = $ArrayTotals;
			// echo print_r($Response);
			// exit;
			$data['ShipmentReportData'] = $Response;
			$data['PaymentValues'] = $ArrayPaymentValues;

			if ($StmtShipmentReportData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}

	function approveClearingAgentReportData()
	{
		$Error = false;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);
		$PoID = $IpData['PoID'];
		if (!$Error) {
			if ($PoID != '') {
				$sqlApproveClearingAgent = "UPDATE ScrmClearingAgentDetails SET `ApprovalStatus`=1 WHERE `PoID`=$PoID";
				$stmtApproveClearingAgent = mysql_query($sqlApproveClearingAgent, $this->db);
				if ($stmtApproveClearingAgent) {
					$data['Error'] = '';
					$data['Msg'] = "PO Approved Successfully";
					$records = array('Data' => $data);
					$this->response($this->json($records), 200);
				} else {
					$data['Error'] = "Unable To Post Data";
					$records = array('Data' => $data);
					$this->response($this->json($records), 200);
				}
			} else {
				$data['Error'] = "Unable To Approve";
				$records = array('Data' => $data);
			}
		} else {
			$data['Error'] = "Unable To Approve";
			$records = array('Data' => $data);
		}
		$this->response($this->json($records), 200);
	}

	function getClearingAgentReportData()
	{


		$Error = false;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$Exporter = $IpData['Exporter'];
		$Country = $IpData['Country'];
		$Consignee = $IpData['Consignee'];
		$ClearingAgent = $IpData['ClearingAgent'];
		$DateSelection = $IpData['DateSelection'];
		$FromDate = $IpData['FromDate'];
		$ToDate = $IpData['ToDate'];
		$Approval = $IpData['Approval'];
		$ApprovalStatus = $IpData['ApprovalStatus'];

		$DocType = 'CMI';

		$Query = '';

		if ($DateSelection == "EntryDate") {
			// $DateFilter = "ClearingAgentEntryDate between '$FromDate' and '$ToDate' ";
			$DateFilter = " SSFD.FDAEntryDate between '$FromDate' and '$ToDate' ";
		} else {
			$DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";
		}

		if ($Exporter != '') {
			$Query .= ' and SPD.ExporterID=' . $Exporter;
		}

		if ($ClearingAgent != "") {
			$Query .= ' and SPD.NotifyPartyID in (' . $ClearingAgent . ')';
		}

		if ($Country != "") {
			$Query .= ' and SPD.DestinationCountry=' . $Country;
		}
		if ($Consignee != "") {
			$Query .= ' and SPD.ConsigneeID=' . $Consignee;
		}

		$ApprovalFields = "";
		if ($Approval == 'YES') {
			$ApprovalFields = ",ApprovalStatus";
			if ($ApprovalStatus != '') {
				if ($ApprovalStatus == 0) {
					$Query = " AND ApprovalStatus = 0";
				}
				if ($ApprovalStatus == 1) {
					$Query = " AND ApprovalStatus = 1";
				}
				if ($ApprovalStatus == 2) {
					$Query = " AND ApprovalStatus IS NULL";
				}
			}
		}



		$StmtClearingAgentTransactionsData = mysql_query('SELECT * FROM `ClearingAgentTransaction`', $this->db);
		$ClearingAgentTransactionQuery = '';
		$ArrayClearingAgentTransaction = [];
		while ($RowClearingAgentTransactionData = mysql_fetch_array($StmtClearingAgentTransactionsData, MYSQL_ASSOC)) {
			$ClearingAgentTransactionQuery .= "(SELECT SUM(BillAmount) FROM `ScrmClearingAgentBreakupDetails` WHERE PoID=SPD.PoID AND TypeofCharges='" . $RowClearingAgentTransactionData['code'] . "') AS `" . $RowClearingAgentTransactionData['name'] . "`, ";
			$ArrayClearingAgentTransaction[] = $RowClearingAgentTransactionData['name'];
		}

		// Remove the last comma to ensure a valid SQL query
		$ClearingAgentTransactionQuery = rtrim($ClearingAgentTransactionQuery, ", ");




		if (!$Error) {

			$SqlFields = "SCI.CustomsInvNo,SCI.PiID,QtyUnit,ClearingAgentEntryDate,SCAD.ADCharges as CAAntiDumpingCharges,
SCAD.HmDutyCharges as CAHmDutyCharges,SCAD.MPCharges as CaMPFCharges,SCAD.EnteredInvValue,NotifyPartyName,ClearingAgentEntryNumber,CVDCharges,
SCI.CInvDate,ShippingTerms,SPC.Name as PortOfFinalDestination,SCL.name as Country,SCOM.SBFOBUSD,SSFD.FDAEntryDate,SSFD.FDAEntryNumber,
(SELECT sum(BillAmount) FROM `ScrmClearingAgentBreakupDetails` WHERE PoID=SPD.PoID and TypeofCharges='CartageCharges') as CartageCharges ,
(SELECT sum(BillAmount) FROM `ScrmClearingAgentBreakupDetails` WHERE PoID=SPD.PoID and TypeofCharges='DemurrageCharges') as DemurrageCharges,
(SELECT sum(BillAmount) FROM `ScrmClearingAgentBreakupDetails` WHERE PoID=SPD.PoID and TypeofCharges='OverSeasLABChargesOverSeasLABCharges') as OverSeasLABCharges,
(SELECT sum(BillAmount) FROM `ScrmClearingAgentBreakupDetails` WHERE PoID=SPD.PoID and TypeofCharges='OverSeasClaimCharges') as OverSeasClaimCharges,
(SELECT sum(BillAmount) FROM `ScrmClearingAgentBreakupDetails` WHERE PoID=SPD.PoID and TypeofCharges='OverSeasRepackingCharges') as OverSeasRepackingCharges,
SSFD.BLDate,SCAD.ClearingAgentDetailsID";

			$SqlFields .= ", " . $ClearingAgentTransactionQuery;

			$SqlClearingAgentReportData = "SELECT  $SqlFields$ApprovalFields
 FROM `ScrmCustomInvoices` as SCI 
 LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
 left join ScrmShippingBillDetails as SCOM on SPD.PoID=SCOM.InvID 
 LEFT JOIN  ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
  left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
 left JOIN ScrmPortCities as SPC on SPC.PortId=SPD.FinalDestination 
 LEFT JOIN ScrmISFDetails AS SSFD ON SSFD.PIID=SPD.PoID 
 left JOIN ScrmClearingAgentDetails as SCAD on SCAD.PoID=SPD.PoID 
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID 
 
 where  $DateFilter and SPD.Status not in (2,3,4)and SPD.DestinationCountry=157 $Query  ";

			// error_log("⚠️ SqlClearingAgentReportData: " . print_r($SqlClearingAgentReportData, true));

			$StmtClearingAgentReportData = mysql_query($SqlClearingAgentReportData, $this->db);


			$i = 0;
			$TotalGrossWeightKgs = 0;
			$TotalNetWgtLbs = 0;
			$TotalFOBValueInForeignCurrency = 0;
			$TotalInvoiceValueUSD = 0;

			$Token = $this->getToken();
			$ClientId = $Token['ClientID'];






			while ($RowClearingAgentReportData = mysql_fetch_array($StmtClearingAgentReportData, MYSQL_ASSOC)) {


				$PoID = $RowClearingAgentReportData['PiID'];
				$BLDate = $RowClearingAgentReportData['BLDate'];

				$StmtScrmClearingAgentBreakupDetailsData = mysql_query("SELECT * FROM `ScrmClearingAgentBreakupDetails` WHERE PoID='$PoID'", $this->db);
				$RowClearingAgentBreakupDetailData = mysql_fetch_array($StmtScrmClearingAgentBreakupDetailsData, MYSQL_ASSOC);

				$CInvDate = $this->getFormatedDatelines($RowClearingAgentReportData['CInvDate']);
				// if ($RowClearingAgentReportData['ClearingAgentEntryDate'] != '') {
				// $EntryDate = $this->getFormatedDatelines($RowClearingAgentReportData['ClearingAgentEntryDate']);
				// } else {
				$EntryDate = $this->getFormatedDatelines($RowClearingAgentReportData['FDAEntryDate']);
				// }

				$ArrayPaymentValues = $this->getPaymentValues($PoID, $DocType);

				$InvValueUSD = $ArrayPaymentValues['InvValueUSD'];
				$FOBValueUSD = $ArrayPaymentValues['FOBValueUSD'];

				$MbillData = $this->getMbilldata($PoID);

				// Temporary logic added only for the DEV client. This will be removed soon.
				if ($BLDate && $ClientId == 'DFLV3') {
					$blDate = new DateTime($BLDate);
					$startDate = new DateTime('2025-04-05');
					$endDate = new DateTime('2025-04-09');

					if ($blDate >= $startDate && $blDate <= $endDate) {
						$RowClearingAgentReportData['CAAntiDumpingCharges'] = '';
						$RowClearingAgentReportData['CAHmDutyCharges'] = '';
						$RowClearingAgentReportData['CaMPFCharges'] = '';
						$RowClearingAgentReportData['CVDCharges'] = '';
						$RowClearingAgentReportData['RTCharges'] = '';
					}
				}

				if ($RowClearingAgentReportData['CAAntiDumpingCharges'] == '') {
					$AntiDumpingCharges = $ArrayPaymentValues['AntiDumpingDuty'];
				} else {
					$AntiDumpingCharges = $RowClearingAgentReportData['CAAntiDumpingCharges'];
				}


				if ($RowClearingAgentReportData['CAHmDutyCharges'] == '') {
					$HarbourMaintenanceFee = $ArrayPaymentValues['HarborDuty'];
				} else {
					$HarbourMaintenanceFee = $RowClearingAgentReportData['CAHmDutyCharges'];
				}

				if ($RowClearingAgentReportData['CaMPFCharges'] == '') {
					$MerchandiseProcessFee = $ArrayPaymentValues['MerchantDuty'];
				} else {
					$MerchandiseProcessFee = $RowClearingAgentReportData['CaMPFCharges'];
				}

				//uma cvd calculation included //18-03-2024
				if ($RowClearingAgentReportData['CVDCharges'] == '') {
					$CountervailingDutyFee = $ArrayPaymentValues['CountervailingDuty'];
				} else {
					$CountervailingDutyFee = $RowClearingAgentReportData['CVDCharges'];
				}

				if ($RowClearingAgentReportData['RTCharges'] == '') {
					$ReciprocalTariffsFee = $ArrayPaymentValues['ReciprocalTariffsFee'];
				} else {
					$ReciprocalTariffsFee = $RowClearingAgentReportData['RTCharges'];
				}



				// $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates`";
				// $StmtExporterTaxRates = mysql_query($SqlExporterTaxRates, $this->db);
				// $RowExporterTaxRates = mysql_fetch_array($StmtExporterTaxRates, MYSQL_ASSOC);
				// $AntiDumpingDutyPerc = $RowExporterTaxRates['AntiDumpingDuty'];
				// $CountervailingDuty = $RowExporterTaxRates['CountervailingDuty'];


				$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				  sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				  sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				  sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


				$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt ,sum(GrossWt) as EnteredGrossWt,
		           sum(Price) as Price,			
		           sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
		           sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` 
		           as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
				$StmtTotal = mysql_query($SqlTotal, $this->db);
				$RowTotal = mysql_fetch_array($StmtTotal, MYSQL_ASSOC);

				$NetWgtLbs = $RowTotal['NetWgtLbs'];
				$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
				$GrossWeightLbs = $RowTotal['GrossWeightLbs'];




				if ($RowClearingAgentReportData['QtyUnit'] == 'KGS') {

					$NetWgtKgs = $RowTotal['NetWgtKgs'];
					$EnteredGrossWt = $RowTotal['EnteredGrossWt'];
					$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
				} else {

					$NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
					$EnteredGrossWt = $RowTotal['EnteredGrossWt'] * 0.454;
					$GrossWeightKgs = $RowTotal['GrossWeightLbs'] * 0.454;
				}

				if ($EnteredGrossWt != '') {
					$GrossWeightKgs = $EnteredGrossWt;
				}



				//response//
				$ArrayClearingAgentReportData['sno'] = ++$i;
				$ArrayClearingAgentReportData['CInvDate'] = $CInvDate;
				$ArrayClearingAgentReportData['MbillData'] = $MbillData;
				$ArrayClearingAgentReportData['PoID'] = $PoID;
				$ArrayClearingAgentReportData['InvoiceNo'] = $RowClearingAgentReportData['CustomsInvNo'];
				$ArrayClearingAgentReportData['TermsofDelivery'] = $RowClearingAgentReportData['ShippingTerms'];
				$ArrayClearingAgentReportData['Country'] = $RowClearingAgentReportData['Country'];
				// $ArrayClearingAgentReportData['USEntryNumber'] = $RowClearingAgentReportData['ClearingAgentEntryNumber'];
				$ArrayClearingAgentReportData['USEntryNumber'] = $RowClearingAgentReportData['FDAEntryNumber'];
				$ArrayClearingAgentReportData['CustomsEntryDate'] = $EntryDate;
				$ArrayClearingAgentReportData['QuantityinLbs'] = round($NetWgtLbs, 3);
				$ArrayClearingAgentReportData['QuantityinconvKgsGross'] = round($GrossWeightKgs, 3);
				$ArrayClearingAgentReportData['USAntiDumpingDuty'] = $AntiDumpingCharges;
				$ArrayClearingAgentReportData['CountervailingDuty'] = $CountervailingDutyFee;
				$ArrayClearingAgentReportData['ReciprocalTariffsFee'] = $ReciprocalTariffsFee;
				// $ArrayClearingAgentReportData['ReciprocalTariffsPerc'] = $ReciprocalTariffsPerc;
				$ArrayClearingAgentReportData['HMF'] = $HarbourMaintenanceFee;
				$ArrayClearingAgentReportData['MPF'] = $MerchandiseProcessFee;
				$ArrayClearingAgentReportData['ADD'] = $ArrayPaymentValues['AntiDumpingDutyPerc'];
				$ArrayClearingAgentReportData['RTP'] = $ArrayPaymentValues['ReciprocalTariffsPerc'];
				$ArrayClearingAgentReportData['ReciprocalTariffsPercent'] = $ReciprocalTariffsPercent;
				$ArrayClearingAgentReportData['TariffPerc'] = $ArrayPaymentValues['TariffPerc'];
				$ArrayClearingAgentReportData['TariffFee'] = $ArrayPaymentValues['TariffFee'];

				$ArrayClearingAgentReportData['EnteredvalueFobValue'] = number_format($FOBValueUSD, 2);
				// $ArrayClearingAgentReportData['EnteredvalueFobValue'] = $RowClearingAgentReportData['SBFOBUSD'];
				$ArrayClearingAgentReportData['InvoiceValue'] = number_format($InvValueUSD, 2);
				$ArrayClearingAgentReportData['AgentName'] = $RowClearingAgentReportData['NotifyPartyName'];
				$ArrayClearingAgentReportData['CartageCharges'] = $RowClearingAgentReportData['CartageCharges'];
				$ArrayClearingAgentReportData['DemurrageCharges'] = $RowClearingAgentReportData['DemurrageCharges'];
				$ArrayClearingAgentReportData['OverSeasClaimCharges'] = $RowClearingAgentReportData['OverSeasClaimCharges'];
				$ArrayClearingAgentReportData['OverseasLabTestingCharges'] = $RowClearingAgentReportData['OverseasLabTestingCharges'];
				$ArrayClearingAgentReportData['OverSeasRepackingCharges'] = $RowClearingAgentReportData['OverSeasRepackingCharges'];
				$ArrayClearingAgentReportData['EnteredInvValue'] = $RowClearingAgentReportData['EnteredInvValue'];

				$TotalClearingAgentTransactions = 0;

				foreach ($ArrayClearingAgentTransaction as $item) {
					$value = isset($RowClearingAgentReportData[$item]) ? $RowClearingAgentReportData[$item] : null;
					$ArrayClearingAgentReportData[$item] = $value;
					// Add to total
					$TotalClearingAgentTransactions += (float) $value;
				}


				$TotalClearingAgentTransactions += (float) $AntiDumpingCharges;
				$TotalClearingAgentTransactions += (float) $CountervailingDutyFee;
				$TotalClearingAgentTransactions += (float) $ReciprocalTariffsFee;
				$TotalClearingAgentTransactions += (float) $HarbourMaintenanceFee;
				$TotalClearingAgentTransactions += (float) $MerchandiseProcessFee;

				$ArrayClearingAgentReportData['TotalClearingAgentTransactions'] = $TotalClearingAgentTransactions;
				$ArrayClearingAgentReportData['BillDate'] = $RowClearingAgentBreakupDetailData['BillDate'];
				$ArrayClearingAgentReportData['BillNumber'] = $RowClearingAgentBreakupDetailData['BillNumber'];

				$TotalNetWgtLbs += $NetWgtLbs;
				$TotalGrossWeightKgs += $GrossWeightKgs;
				$TotalFOBValueInForeignCurrency += $RowClearingAgentReportData['SBFOBUSD'];
				$TotalInvoiceValueUSD += $InvValueUSD;

				if ($Approval == "YES") {
					if (isset($RowClearingAgentReportData['ClearingAgentDetailsID']) && $RowClearingAgentReportData['ClearingAgentDetailsID'] != '') {
						$ArrayClearingAgentReportData['AddApproval'] = 1;
					} else {
						$ArrayClearingAgentReportData['AddApproval'] = 0;
					}
					$ArrayClearingAgentReportData['ApprovalStatus'] = $RowClearingAgentReportData['ApprovalStatus'];
				}
				//response//
				//$i++;

				$Response[] = $ArrayClearingAgentReportData;
			}
			$ArrayTotals['TotalInvoices'] = $i;
			$ArrayTotals['TotalNetWgtLbs'] = number_format($TotalNetWgtLbs, 3);
			$ArrayTotals['TotalGrossWeightKgs'] = number_format($TotalGrossWeightKgs, 3);
			$ArrayTotals['TotalInvoiceValueUSD'] = number_format($TotalInvoiceValueUSD, 2);
			$ArrayTotals['TotalEnteredvalueFobValue'] = number_format($TotalFOBValueInForeignCurrency, 2);

			$data['Totals'] = $ArrayTotals;
			$data['getClearingAgentReportData'] = $Response;
			$data['ArrayClearingAgentTransactionsData'] = $ArrayClearingAgentTransaction;
			if ($StmtClearingAgentReportData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}


	function getForwardContractUtilisedReportData()
	{


		$Error = false;
		$RawIpData = file_get_contents('php://input');
		$IpData = json_decode($RawIpData, true);

		$Exporter = $IpData['Exporter'];
		$ForwardContractNo = $IpData['ForwardContractNo'];

		$Query = '';
		$FQuery = '';



		if ($ForwardContractNo == "") {
			//$FQuery.=' FCID in (SELECT DISTINCT FcID FROM `ScrmCollectionBreakUP` ORDER by FcID asc)';
			$FQuery .= '';
		} else {
			$FQuery .= ' and  FCID=' . $ForwardContractNo;
		}

		if ($Exporter != '') {
			$Query .= ' and SFCD.ExpID=' . $Exporter;
		}


		if (!$Error) {

			$SqlFields = "FCID,BalancedValue,FCNumber,NetValue,UtilizedValue as TotalUtilisedValue";

			// $SqlFCUtilizedReportData="SELECT  $SqlFields FROM `ScrmForwardContractsDetails` as SFCD   
			// 						  where   BalancedValue=0  $FQuery  $Query  ";
			$SqlFCUtilizedReportData = "SELECT  $SqlFields FROM `ScrmForwardContractsDetails` as SFCD   
  where  SFCD.Status=1 $FQuery  $Query   ";

			$StmtFCUtilizedReportData = mysql_query($SqlFCUtilizedReportData, $this->db);



			while ($RowFCUtilizedReportData = mysql_fetch_array($StmtFCUtilizedReportData, MYSQL_ASSOC)) {


				$FCID = $RowFCUtilizedReportData['FCID'];
				$FCBalancedValueID = $RowFCUtilizedReportData['BalancedValue'];
				$FCNumber = $RowFCUtilizedReportData['FCNumber'];
				$NetValue = $RowFCUtilizedReportData['NetValue'];
				$TotalUtilisedValue = $RowFCUtilizedReportData['TotalUtilisedValue'];


				$SqlInvoiceswiseUtilisedValue = "SELECT CustomsInvNo,UtilizedValue,UtilizedDate
		from `ScrmCollectionBreakUP` as SCBU left join ScrmCustomInvoices as SCI on SCI.PiID=SCBU.PiID
		where FCNumber ='$FCNumber'  ORDER BY `ColBrkID` Asc";

				$StmtInvoiceswiseUtilisedValue = mysql_query($SqlInvoiceswiseUtilisedValue, $this->db);
				/*
																																			$ArrayCustomsInvNo=array();
																																			$ArrayUtilizedValue=array();
																																			$ArrayUtilizedDate=array();
																																			*/
				$CustomsInvoiceNumber = '';
				$UtilizedValue = '';
				$UtilizedDate = '';
				unset($ArrayCustomsInvNo);
				unset($ArrayUtilizedValue);
				unset($ArrayUtilizedDate);
				while ($RowInvoiceswiseUtilisedValue = mysql_fetch_array($StmtInvoiceswiseUtilisedValue, MYSQL_ASSOC)) {

					$ArrayCustomsInvNo[] = $RowInvoiceswiseUtilisedValue['CustomsInvNo'];
					$ArrayUtilizedValue[] = number_format($RowInvoiceswiseUtilisedValue['UtilizedValue'], 2);
					$ArrayUtilizedDate[] = $this->getFormatedDatelines($RowInvoiceswiseUtilisedValue['UtilizedDate']);
					//print_r($ArrayCustomsInvNo);

					$CustomsInvoiceNumber = implode(', <br>', $ArrayCustomsInvNo);
					$UtilizedValue = implode(', <br>', $ArrayUtilizedValue);
					$UtilizedDate = implode(', <br>', $ArrayUtilizedDate);
				}


				//response//
				$ArrayFCUtilisedReportData['sno'] = ++$i;
				$ArrayFCUtilisedReportData['ForwardContractNumber'] = $FCNumber;
				$ArrayFCUtilisedReportData['InvoiceNo'] = $CustomsInvoiceNumber;
				$ArrayFCUtilisedReportData['TotalFcValue'] = number_format($NetValue, 2);
				$ArrayFCUtilisedReportData['UtilizedValue'] = $UtilizedValue;
				$ArrayFCUtilisedReportData['UtilizedDate'] = $UtilizedDate;
				$ArrayFCUtilisedReportData['TotalUsedAmount'] = number_format($TotalUtilisedValue, 2);
				$ArrayFCUtilisedReportData['BalanceAmount'] = number_format($FCBalancedValueID, 2);




				//response//
				//$i++;

				$Response[] = $ArrayFCUtilisedReportData;
			}


			//$data['Totals']=$ArrayTotals;
			$data['getFCUtilisedReportData'] = $Response;
			if ($StmtFCUtilizedReportData) {
				$data['Error'] = null;
				$data['Msg'] = "Success";
				$records = array('Data' => $data, );
				$this->response($this->json($records), 200);
			} else {
				$data['Error'] = "Unable To Fetch Data";
				$records = array('Data' => $data);
				$this->response($this->json($records), 200);
			}
		}
	}


	/************Reports****************/

	/***************Functions*****************/

	function getInvoiceNumber($InvNo, $InvDate, $ExpID, $ProcID)
	{

		$InvoiceNo = '--';
		$CurrentMonth = date('n', strtotime($InvDate));

		$AryInvCodes = $this->getShortInvShortCodes($ExpID, $ProcID);

		$ProcShortCode = $AryInvCodes['ProcShortCode'];
		$ExpShortCode = $AryInvCodes['ExpShortCode'];

		if ($CurrentMonth <= 3) {
			$NxtYear = date("y", strtotime($InvDate));
			$CurrentYear = date("y", strtotime($InvDate)) - 1;
		} else {
			$CurrentYear = date("y", strtotime($InvDate));
			$NxtYear = date("y", strtotime($InvDate)) + 1;
		}

		$InvoiceNo = $ExpShortCode . $ProcShortCode . $CurrentYear . $NxtYear . '/' . $InvNo;



		return $InvoiceNo;
	}


	function getFDADetails($PoID)
	{


		$SqlFDADetails = "SELECT GetOutFull,EmptyReturn,`FDAEUHoldDate`, `FDAEUReleaseCFIADate`,
`FDAEntryNumber`, `FDAEntryDate`,  `EmptyContainerEntryNumber`,  `FDAExam`, `FDAContainerStatus`, `ShippingNotes`, `FDARemarks`, 
`NoticeDate1`, `NoticeDate2`, `NoticeDate3`, `NoticeDate4`, `SampleCollectionDate`, `SampleReceivedDate` FROM `ScrmISFDetails`
 WHERE PIID='$PoID'";
		$StmtFDADetails = mysql_query($SqlFDADetails, $this->db);

		$RowFDADetails = mysql_fetch_array($StmtFDADetails, MYSQL_ASSOC);



		$ArrayFDADetails['ContainerGateOutDate'] = $RowFDADetails['GetOutFull'];
		$ArrayFDADetails['EmptyContainerReturnDate'] = $RowFDADetails['EmptyReturn'];
		$ArrayFDADetails['EmptyContainerEntryNumber'] = $RowFDADetails['EmptyContainerEntryNumber'];
		$ArrayFDADetails['FDAEUHoldDate'] = $RowFDADetails['FDAEUHoldDate'];
		$ArrayFDADetails['FDAEUReleaseCFIADate'] = $RowFDADetails['FDAEUReleaseCFIADate'];
		$ArrayFDADetails['FDAEntryNumber'] = $RowFDADetails['FDAEntryNumber'];
		$ArrayFDADetails['FDAEntryDate'] = $RowFDADetails['FDAEntryDate'];
		$ArrayFDADetails['FDAExam'] = $RowFDADetails['FDAExam'];
		$ArrayFDADetails['Status'] = $RowFDADetails['FDAContainerStatus'];
		$ArrayFDADetails['Remarks'] = $RowFDADetails['FDARemarks'];
		$ArrayFDADetails['ShippingNotes'] = $RowFDADetails['ShippingNotes'];
		$ArrayFDADetails['NoticeDate1'] = $RowFDADetails['NoticeDate1'];
		$ArrayFDADetails['NoticeDate2'] = $RowFDADetails['NoticeDate2'];
		$ArrayFDADetails['NoticeDate3'] = $RowFDADetails['NoticeDate3'];
		$ArrayFDADetails['NoticeDate4'] = $RowFDADetails['NoticeDate4'];
		$ArrayFDADetails['SampleReceivedDate'] = $RowFDADetails['SampleReceivedDate'];
		$ArrayFDADetails['SampleCollectionDate'] = $RowFDADetails['SampleCollectionDate'];


		return $ArrayFDADetails;
	}

	function getShortInvShortCodes($ExpID, $ProcID)
	{


		$SqlShortCodes = "SELECT InvShortCode as ProcShortCode ,ExpApprovalNo as ApprovalNo,
(SELECT InvShortCode FROM `AdminExporterDetails` WHERE ExpID='$ExpID') as ExpShortCode
FROM `AdminExporterDetails` WHERE ExpID='$ProcID';";
		$StmtShortCodes = mysql_query($SqlShortCodes, $this->db);

		$RowShipingBill = mysql_fetch_array($StmtShortCodes, MYSQL_ASSOC);


		return $RowShipingBill;
	}



	function getFormatedDate($DATE)
	{

		$TIME_STAMP = strtotime($DATE);
		$formated_date = date("d-m-Y", $TIME_STAMP);
		if ($formated_date == '01-01-1970') {
			$formated_date = '';
		}
		return $formated_date;
	}

	function getFinancialYear($FinClsYear)
	{
		$DateStart = $FinClsYear . '-04-01';
		$DateEnd = ($FinClsYear + 1) . '-03-31';

		return $DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
	}

	function getFinancialClsYear($FinClsYear, $Month) //uma
	{
		if ($Month != '') {
			$Month = sprintf('%02d', $Month);
			if ($Month > 3) {
				$DateStart = $FinClsYear . '-' . $Month . '-01';
				$DateEnd = ($FinClsYear) . '-' . $Month . '-31';
			} else {
				$DateStart = ($FinClsYear + 1) . '-' . $Month . '-01';
				$DateEnd = ($FinClsYear + 1) . '-' . $Month . '-31';
			}
			$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
		} else {
			$DateStart = $FinClsYear . '-04-01';
			$DateEnd = ($FinClsYear + 1) . '-03-31';
			$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
		}

		return $DateFilter;
	}

	function isSetEmpty($Value)
	{

		return isset($Value) && !empty($Value);
	}

	function getExporterDetails($Id)
	{

		$SqlExporter = "SELECT * FROM `AdminExporterDetails` WHERE ExpID='$Id'";
		$StmtExporter = mysql_query($SqlExporter, $this->db);
		$RowExporter = mysql_fetch_array($StmtExporter, MYSQL_ASSOC);

		return $RowExporter;
	}

	function getFormatedDatelines($DATE)
	{

		$TIME_STAMP = strtotime($DATE);
		$formated_date = date("d-m-Y", $TIME_STAMP);
		if ($formated_date == '01-01-1970') {
			$formated_date = '--';
		}
		return $formated_date;
	}
	function getFormatedDatewithTime($DATE)
	{

		$TIME_STAMP = strtotime($DATE);
		$formated_date = date("d-m-Y H:i:s", $TIME_STAMP);
		/*if ($formated_date == '01-01-1970 ') {
																		$formated_date = '--';
																		}*/
		return $formated_date;
	}

	function checkFreight($PoID)
	{

		$SqlCustomsfreight = "SELECT SCI.Freight as CustomFreight,SCI.ExRate as CustomExrate,SCD.Freight as ComFreight,
	SCD.ExchngRte as ComExrate FROM `ScrmCustomInvoices` as SCI left join ScrmCommercialData as SCD on SCD.PoID=SCI.PiID 
	WHERE PiID=$PoID;
	";
		$StmtCustomsfreight = mysql_query($SqlCustomsfreight, $this->db);
		$RowCustomsfreight = mysql_fetch_array($StmtCustomsfreight, MYSQL_ASSOC);

		if ($RowCustomsfreight['ComFreight'] != 0) {
			$FrieghtCharge = $RowCustomsfreight['ComFreight'];
			$ExchangeRate = $RowCustomsfreight['ComExrate'];
		} else {
			$FrieghtCharge = $RowCustomsfreight['CustomFreight'];
			$ExchangeRate = $RowCustomsfreight['CustomExrate'];
		}
		$ArrayFreight['FrieghtCharge'] = $FrieghtCharge;
		$ArrayFreight['ExchangeRate'] = $ExchangeRate;
		return $ArrayFreight;
	}


	function getEtaFDDate($PoID)
	{
		//echo $PoID; 
		$sql = "SELECT TranshipDt FROM `ScrmPoData` as SPD 
		left join ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
		left join ScrmTranshipmentPortsList  as STL  on STL.ToTOPID=SPC.Name
		WHERE  SPD.PoID='$PoID' and STL.PIID='$PoID'";


		$stmt = mysql_query($sql, $this->db);
		$row = mysql_fetch_array($stmt, MYSQL_ASSOC);

		$TranshipDt = $row['TranshipDt'];

		return $TranshipDt;
	}


	function getVariety($PoID)
	{

		$sql = "SELECT GROUP_CONCAT(SPVL.Grade,'-',ProductDescription) as Variety, GROUP_CONCAT(Brand) as brand FROM  `ScrmPoGradeData` as SPGD 
		left join `ScrmPIVarietyList`  as  SPVL on  SPVL.VarietyID=SPGD.VarietyID WHERE InvId='$PoID'";

		$stmt = mysql_query($sql, $this->db);
		$row = mysql_fetch_array($stmt, MYSQL_ASSOC);

		$Variety = $row['Variety'];
		$Variety = str_replace(',', ',<br>', $Variety);

		$Brand = str_replace(',', ',<br>', $row['brand']);

		return $Variety . '$' . $Brand;
	}

	function postPoRejectStatus($PoID, $FDAContainerStatus) //uma if status=reject in fda status page then update status in podata and pidata (21-12-2023)
	{

		$rejectedStatusArray = ["Rejected", "Cancelled", "Destroyed", "Container ROB", "Return Container"];
		if ($FDAContainerStatus != '' && in_array($FDAContainerStatus, $rejectedStatusArray)) {
			// $sqlStatus = "SELECT Code FROM `ScrmShipmentStatusCodes` WHERE Name='$FDAContainerStatus'";
			// $StmtStatus = mysql_query($sqlStatus, $this->db);
			// $row = mysql_fetch_array($StmtStatus, MYSQL_ASSOC);
			// $Status = $row['Code'];

			// if ($Status == '') {
			$Status = 4; // except FDAContainerStatus=cancelled ,all other status are updated to rejected in polist
			// }
		} else {
			$Status = 5;
		}


		$sqlPiUpdate = "UPDATE `ScrmPIData` set Status='$Status' WHERE PoID='$PoID'";
		$sqlPoUpdate = "UPDATE `ScrmPoData` set Status='$Status' WHERE PoID='$PoID'";

		$stmtPiUpdate = mysql_query($sqlPiUpdate, $this->db);
		$stmtPoUpdate = mysql_query($sqlPoUpdate, $this->db);

		if ($stmtPiUpdate && $stmtPoUpdate) {
			return 'Success';
		}
	}

	function checkSauceExists($PoID)
	{
		$SqlQuery = "SELECT COUNT(*) as SauceCount FROM ScrmPIVarietyList WHERE VarietyID IN 
    	(Select VarietyID FROM ScrmPoGradeData WHERE InvID = $PoID) AND HasSauce = 'Yes'";
		$Stmt = mysql_query($SqlQuery, $this->db);
		$Row = mysql_fetch_array($Stmt, MYSQL_ASSOC);
		return $Row['SauceCount'] > 0;
	}

	function getInsurancePerc($CountryID)
	{
		$sql_ins = "SELECT * FROM `ScrmInsuranceCharges` WHERE Country IN($CountryID)";
		$stmt_ins = mysql_query($sql_ins, $this->db);
		$row_ins = mysql_fetch_array($stmt_ins, MYSQL_ASSOC);
		return $row_ins['InsurancePremiumRate'];
	}
	function StoreCertificateNumber_API()
	{
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		$AryCurrentDate = $this->getCurrentDateTime();

		$PostedDateTime = $AryCurrentDate['TimeStamp'];
		$CertificationID = $data['CertificationID'];
		$CertificationNumber = $data['CertificationNumber'];
		$InvID = $data['InvID'];
		$UserId = $data['UserId'];

		if (!empty($CertificationID)) {
			$sql = "UPDATE `InsuranceCertifactionNumbers` SET `InvoiceID`='$InvID',
					`CertificationNumber`='$CertificationNumber',`UpdatedOn`= '$PostedDateTime',`UpdatedBy`='$UserId'
					WHERE `ID`='$CertificationID'";
		} else {
			$sql = "INSERT INTO `InsuranceCertifactionNumbers` 
				( `InvoiceID`, `CertificationNumber`, 
				`CreatedOn`, `CreatedBy`) VALUES  
				('$InvID','$CertificationNumber',	
				'$PostedDateTime','$UserId')";
		}
		$stmt = mysql_query($sql, $this->db);
		if ($stmt) {
			$temp = array('Error' => null, "Msg" => "Posted Successfully.");
			$this->response($this->json($temp), 200);
		} else {
			$records = array('Error' => "Unable To Post, Please try again. Error: " . mysql_errno($this->db));
			$this->response($this->json($records), 200);
		}
	}


	/**********************************Dont Change Below************************************************* */

	public function response($data, $status)
	{

		// $this->set_headers($status);
		echo $data;
		exit;
	}

	private function json($data)
	{
		if (is_array($data)) {
			return json_encode($data);
		}

		mysql_close($this->db);
	}
}
