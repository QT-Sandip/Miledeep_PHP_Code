	<?php
header('Access-Control-Allow-Origin: *'); 
/*include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	*/	
 /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_reporting(0);*/


	
	require_once("Rest.inc.php");
	
	class API extends REST {
		
	
		
		
	 //public $db_host,$db_user,$db_password,$db_name;
		
		//exit;
/*

		public $data = "";
	    const DB_SERVER = $db_host; 
		const DB_USER = $db_user;
		const DB_PASSWORD = $db_password;
		const DB = $db_name;	    
		
		*/
		
		const DB_SERVER = "localhost"; 
		const DB_USER = "u177334400_DFL";
		const DB_PASSWORD = "@Rm8nTCMv";
		const DB = "u177334400_DFL";
		private $db = NULL;
		
		/*const DB_SERVER = "localhost"; 
		const DB_USER = "u177334400_DevJME";
		const DB_PASSWORD = "o3^P[UB?UB";
		const DB = "u177334400_DevJME";
		private $db = NULL;
		*/
	
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		/*
		 *  Database connection 
		*/
		private function dbConnect(){
			//echo "connected";
			
			
			
			$this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
		
			if($this->db)
				mysql_select_db(self::DB,$this->db);
				
			
		}
		/*
		* Public method for access api.
		* This method dynmically call the method based on the query string
        	*
	        */
			public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['f'])));
			if((int)method_exists($this,$func) > 0){
				
				
				$headers = apache_request_headers();
				

				$USER_ID=0;
			foreach ($headers as $header => $value) {
				IF($header==strtolower('USER_ID')){
				$USER_ID=$value;
			}
			}
				
				
				/* 
				echo  $USER_ID;
				
				if($func=='getdashboard' ){
				$this->$func($USER_ID);
				}else if($func!='registration' && $USER_ID ==0){					
				$this->response('Please Login And Try Again',400);
				}else if($func=='registration' ){
				$this->$func($USER_ID);
				} else if( $this->SessionTime($USER_ID)){
				$this->response('Session Time Out ..Please Login And Try Again',400);
				}else{
			    $this->$func($USER_ID);
				}
				
				$USER_ID=$this->logData($func);*/ 
			//if any issue in login 	
				
				
				$USER_ID=$this->logData($func);
				 $this->$func($USER_ID);
				 
				
			
				
			
			
}else{
				$this->response('',404);
			}				// If the method not exist with in this class, response would be "Page not found".
}
		
	
public function SessionTime($USER_ID){
	

	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$CurrentDateTime=$dateTime->format("Y-m-d  H:i:s");
	
	 $SqlSession="select LastLogin  from `AdminLoginDetails` WHERE `UserID`='$USER_ID'";
	$StmtSession = mysql_query($SqlSession);
	$RowData = mysql_fetch_array($StmtSession,MYSQL_ASSOC);
	$LastLoginTime=$RowData['LastLogin'];
	
	
	$CurrentDateTime = strtotime($CurrentDateTime);
	$LastLoginTime = strtotime($LastLoginTime);
	$TimeDiff = $CurrentDateTime - $LastLoginTime;
	 $TimeDiff=round($TimeDiff/60);
	
	if($TimeDiff > 10){
		return true;
	}else{
	return false;
	}
}


public function logData($func){

	
	
		 $json = file_get_contents('php://input');


$USER_ID=0;
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$DATE=$dateTime->format("Y-m-d H:i:s");
foreach ($headers as $header => $value) {
	IF($header=='USER_ID'){
    $USER_ID=$value;
	}
}
	$data['FUNCTION'] =$func;
	$data['USER_ID'] =$USER_ID;
	$data['DATE'] =$DATE;
	
	$this->pushFcm('1',$data);
	
/*
		$sql_update="UPDATE `AdminLoginDetails` SET `LastLogin`='$DATE' WHERE `UserID`='$USER_ID'";
		$sqlca_register = mysql_query($sql_update);
	*/
	 $json = file_get_contents('php://input');
	
  $ss_register="INSERT INTO `USER_MOBILE_ACTIVITY`(  `USER_ID`,`METHOD`,`DATA`,`DATE`) 
				VALUES ('$USER_ID','$func','$json','$DATE')";

			
				$sqlca_register = mysql_query($ss_register);
				return $USER_ID;
				
}

function urlEncode64($input) {
 return strtr(base64_encode($input), '+/=', '._-');
}

function urlDecode64($input) {
 return base64_decode(strtr($input, '._-', '+/='));
} 

public function getToken(){
	$Headers = apache_request_headers();
 	$Token=$Headers['Token'];
	$Token=$this->urlDecode64($Token);
	$Token=json_decode($Token,true);
	return $Token;
	
}


public function getDashBoard($USER_ID){
	
	$Token=$this-> getToken();
	$DeptID=$Token['DeptID'];
	
	 $SqlDashBoard="SELECT Title,TypeID,Icon,Link,Package,ClassType,SubLink  FROM AppDashboards WHERE DeptID='$DeptID' or DeptID=0  ORDER by SortOrder";	
	 $sqlca_c_user = mysql_query($SqlDashBoard);
		
	if(mysql_num_rows($sqlca_c_user) !='0'){
		while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$result['Title'] = $rlt['Title'];
					$result['TypeId'] = $rlt['TypeID'];
					$result['Icon'] = $rlt['Icon'];
					$result['Link'] = $rlt['Package'].$rlt['Link'];
					$result['SubLink'] = $rlt['Package'].$rlt['SubLink'];
					$result['ClassType'] = $rlt['ClassType'];
					$Data[]=$result;
		}
		
		
		$DropDownData=$this->getDropDownData(1);
		
		
		$temp = array(
		'error' => null, 
		"Data"=>$Data,
		"DropDownData"=>$DropDownData,
		);			
		$this->response($this->json($temp), 200);
		
	 
	}else{
	  $temp = array('error' => "No Access To User Please Contact Admin");
	  $this->response($this->json($temp), 200);
		}
}
		
		
	
//uma
public function getEmpList(){
	
	$STATUS_SUCCESS='SUCCESS';
	$STATUS_FAILURE='FAILURE';
	
	$sqlca="SELECT HRM_EMPLOYEE_LIST.EMPLOYEE_ID, HRM_EMPLOYEE_LIST.EMPLOYEE_NAME, 
	HRM_EMPLOYEE_LIST.EMPLOYEE_DESIGNATION,HRM_EMPLOYEE_LIST.EMPLOYEE_CODE,
	HRM_EMPLOYEE_LIST.PHONE_NUMBER,HRM_EMPLOYEE_LIST.USER_IMAGE,HRM_EMPLOYEE_LIST.UNIT,
	HRM_DEPARTMENT_MASTER.DEPARTMENT_NAME,HRM_SUB_DEPARTMENT_MASTER.SUB_DEPARTMENT_NAME
FROM HRM_EMPLOYEE_LIST
LEFT JOIN HRM_DEPARTMENT_MASTER ON HRM_EMPLOYEE_LIST.DEPARTMENT_ID=HRM_DEPARTMENT_MASTER.DEPARTMENT_ID 
LEFT JOIN HRM_SUB_DEPARTMENT_MASTER  ON      HRM_SUB_DEPARTMENT_MASTER.SUB_ID = HRM_EMPLOYEE_LIST.SUB_ID";	
		$sqlca_c_user = mysql_query($sqlca);
if(mysql_num_rows($sqlca_c_user) =='0'){
			$temp = array('status' => $STATUS_FAILURE, "msg" => "No Lots Found");
			$this->response($this->json($temp), 200);
		}else{
			
				while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$result[] = $rlt;
				}
		$temp = array('status' => $STATUS_SUCCESS, "InwardList"=>$result, "msg" => "Successful","ProcessingUnits" => $ary_pu);			
		$this->response($this->json($temp), 200);
		
		}
}



		
		
		
public function login($USER_LOGIN_ID,$USER_PASSWORD,$device_id){
			
			

		$STATUS_SUCCESS='SUCCESS';
		$STATUS_FAILURE='FAILURE';
			
		 $ss_c_user="SELECT * FROM `AdminLoginDetails`
		 WHERE UserLoginID='$USER_LOGIN_ID' and UserPassword='$USER_PASSWORD'";
				
		$sqlca_c_user = mysql_query($ss_c_user); 
			
			
		if(mysql_num_rows($sqlca_c_user) =='0'){
			$msg = array('status' => $STATUS_FAILURE, "msg" => "No User Exist Please Register");
			$this->response($this->json($msg), 200);
		}else{
			
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$DATE_TIME=$dateTime->format("Y-m-d  H:i:s"); 
			
		//	
					
			
		$result = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC);
		$UserID=$records['USER_ID']=$result['UserID'];
		$records['DEPARTMENT_ID']=$result['DeptID'];
		$records['SUB_DEPT_ID']=$result['SubDeptID'];
		$records['USER_NAME']=$result['UserName'];
		$records['USER_TYPE']=$result['UserType'];
		$records['UNIT_ID']=$result['UnitID'];
		$records['ACCESS_TYPE']=$result['AccessType'];
		$records['status']="Success";
		$records['msg']="User Login";
		$sql_update="UPDATE `AdminLoginDetails` SET `DeviceID`='$device_id',
		`LastLogin`='$DATE_TIME' WHERE `UserID`='$UserID'";
		$sqlca_update = mysql_query($sql_update,$this->db);		
			
		$this->response($this->json($records), 200);
		
		}
}
		
			
public function RiseIssue(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$AryCurrentDate=$this->getCurrentDateTime();
			
			$PostedDateTime=$AryCurrentDate['TimeStamp'];			
			$Year=$AryCurrentDate['Year'];


			$LotID = $data['LotID'];
			$PostedUserID = $data['PostedUserID'];
			$IssueID = $data['IssueID'];
			$Remarks = $data['Remarks'];			
			$Description = $data['Desc'];			
			
			
			
			
				$SqlTickets="INSERT INTO `AdminTickets` 
				( `LotID`, `PostedUserID`, 
				`PostedDateTime`, `IssueID`, `Remarks`,`Description`) VALUES  
				('$LotID','$PostedUserID',	
				'$PostedDateTime','$IssueID','$Remarks','$Description')";																
				
				$StmtTickets = mysql_query($SqlTickets,$this->db);
			
			
				if($StmtTickets){			
				$temp = array('Error' => null ,"Msg" => "Tickets Posted");			
				$this->response($this->json($temp), 200);
				}else{
				$records = array('Error' => "Unable To Rise Ticket. Error ".mysql_errno($this->db));
				$this->response($this->json($records), 200);
				}

}			



//ROBO

			
public function PdBilling(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$AryCurrentDate=$this->getCurrentDateTime();
			
			$PostedDateTime=$AryCurrentDate['TimeStamp'];			
			$Year=$AryCurrentDate['Year'];


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
			
			
			
			
				$SqlPdBilling="INSERT INTO `PpcInHousePDBilling`(`ContractorId`, `SubContractorId`, `LotId`, `VarietyId`, `Count`, `Date`, `Quantity`, `Category`, `NoOfWorkers`, `PostedUserID`, `PostedDateTime`) VALUES
				('$ContractorId','$SubContractorId',	
				'$LotId','$VarietyId','$Count','$Date','$Quantity','$Category','$NoOfWorkers','$PostedUserID','$PostedDateTime')";																
				
				$StmtPdBilling = mysql_query($SqlPdBilling,$this->db);
			
			
				if($StmtPdBilling){			
				$temp = array('Error' => null ,"Msg" => "Sucessfully added ");			
				$this->response($this->json($temp), 200);
				}else{
				$records = array('Error' => "Unable To add data. Error ".mysql_errno($this->db));
				$this->response($this->json($records), 200);
				}

}			


//robo
			
			public function createLot(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);					
			
			$PostedUserID = $data['PostedUserID'];
			$LotNumber = $data['LotNumber'];
			$PurchaseStationID = $data['PurchaseStationID'];
			$VehicalID = $data['VehicalID'];
			$AgentID = $data['AgentID'];
			$DriverID =$data['DriverID'];
			$GraderID = $data['GraderID'];
			
			$PurchaseCategoryID=$data['PurchaseCategoryID'];
			$PurchaseCount=$data['PurchaseCount'];
			$PurchaseWeight=$data['PurchaseWeight'];
			$FarmAddress=$data['FarmAddress'];
			$PurchaseDate=date("Y-m-d", strtotime($data['PurchaseDate']));
			
			
			
			
			
			$LotNumber=$this->getFormatedLotNumber($PurchaseStationID,$LotNumber);

			$LotAval=$this->checkLotNoAval($PurchaseDate,$LotNumber);
			
				if(!$LotAval){			
				$records = array('error' => "Lot Already Exist");
				$this->response($this->json($records), 200);
				}else if($PostedUserID==''){
				$records = array('error' => "Session Expired,Please Login and Try Again");
				$this->response($this->json($records), 200);
				}					
			
			
			$AryCurrentDate=$this->getCurrentDateTime();
			
			$CreatedDateTime=$AryCurrentDate['TimeStamp'];			
			$Year=$AryCurrentDate['Year'];	
			
			
			
			
				$SqlPostLot="INSERT INTO `RmLotDetatils` 
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
				
				$StmtPostLot = mysql_query($SqlPostLot,$this->db);
			
			
				if($StmtPostLot){			
				$temp = array('error' => null ,"msg" => "Lot Posted Successful");			
				$this->response($this->json($temp), 200);
				}else{
				$records = array('error' => "Unable To Post Lot. Error ".mysql_errno($this->db));
				$this->response($this->json($records), 200);
				}
		
		}
		
	public function getFormatedLotNumber($PurchaseStationID,$LotNumber){
			$SqlLotCode="SELECT LotCode FROM `RmPurchaseStations` 
			WHERE PurchaseStationID='$PurchaseStationID'";  
			$StmtLotCode = mysql_query($SqlLotCode);
			
			
			if(mysql_num_rows($StmtLotCode) =='0'){
			$LotNumber=0;
			}else {
			$RowData = mysql_fetch_array($StmtLotCode,MYSQL_ASSOC);
			$LotCode=$RowData['LotCode'];
			$LotNumber =  str_replace("O","0",$LotNumber);
			$LotNumber = ltrim($LotNumber, "0");
			$LotNumber = preg_replace("/[^0-9]/", "", $LotNumber );
			$LotNumber = sprintf( '%04d', $LotNumber);			
			$LotNumber = $LotCode.$LotNumber;
			}
			
						
			return $LotNumber;
	}

	
			public function createBooking(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);					
			
			$PostedUserID = $data['PostedUserID'];
			$BookingNumber = $data['BookingNumber'];
			$BookingStationID = $data['BookingStationID'];
			$VehicalID = $data['VehicalID'];
			$AgentID = $data['AgentID'];
			$DriverID =$data['DriverID'];
			$GraderID = $data['GraderID'];
			
			$PurchaseCategoryID=$data['PurchaseCategoryID'];
			$BookingCount=$data['BookingCount'];
			$BookingWeight=$data['BookingWeight'];
			$FarmAddress=$data['FarmAddress'];
			$BookingDate=date("Y-m-d", strtotime($data['BookingDate']));
			
			
			
			
			
			$BookingNumber=$this->getFormatedBookingNumber($BookingStationID,$BookingNumber);

			$LotAval=$this->checkLotNoAval($BookingDate,$BookingNumber);
			
				if(!$LotAval){			
				$records = array('error' => "Booking Number Already Exist");
				$this->response($this->json($records), 200);
				}else if($PostedUserID==''){
				$records = array('error' => "Session Expired,Please Login and Try Again");
				$this->response($this->json($records), 200);
				}					
			
			
			$AryCurrentDate=$this->getCurrentDateTime();
			
			$CreatedDateTime=$AryCurrentDate['TimeStamp'];			
			$Year=$AryCurrentDate['Year'];	
			
			
			
			
				$SqlPostLot="INSERT INTO `RmBookingDetatils`
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
				$StmtPostLot = mysql_query($SqlPostLot,$this->db);
			
			
				if($StmtPostLot){			
				$temp = array('error' => null ,"msg" => "Lot Posted Successful");			
				$this->response($this->json($temp), 200);
				}else{
				$records = array('error' => "Unable To Post Lot. Error ".mysql_errno($this->db));
				$this->response($this->json($records), 200);
				}
		
		}
		
		
			public function getFormatedBookingNumber($BookingStationID,$BookingNumber){
			$SqlLotCode="SELECT LotCode FROM `RmPurchaseStations` 
			WHERE PurchaseStationID='$BookingStationID'";  
			$StmtLotCode = mysql_query($SqlLotCode);
			
			
			if(mysql_num_rows($StmtLotCode) =='0'){
			$LotNumber=0;
			}else {
			$RowData = mysql_fetch_array($StmtLotCode,MYSQL_ASSOC);
			$LotCode=$RowData['LotCode'];
			$BookingNumber =  str_replace("O","0",$BookingNumber);
			$BookingNumber = ltrim($BookingNumber, "0");
			$BookingNumber = preg_replace("/[^0-9]/", "", $BookingNumber );
			$BookingNumber = sprintf( '%04d', $BookingNumber);			
			$BookingNumber = $LotCode.$BookingNumber;
			}
			
						
			return $BookingNumber;
	}
		

	public function checkBookingNoAval($BookingDate,$BookingNumber,$LotId){
		//echo $LotId;
			if($LotId!=''){
			$Qry="and RmBookingID!='$LotId'";	
			}
			
			$CheckYear=explode('-', $BookingDate); 		
			
			if($CheckYear[1] > 3){
			$DateStart=$CheckYear[0].'-04-01';  //2021-04-01
			$DateEnd=($CheckYear[0]+1).'-03-31'; //2022-03-31
			}else{
			$DateStart=($CheckYear[0]-1).'-04-01';  //2021-04-01
			$DateEnd=($CheckYear[0]).'-03-31'; //2022-03-31
			}
			
		 	
			
		 	 $SqlLotDetails="SELECT * FROM `RmBookingDetatils`
			WHERE BookingNumber='$BookingNumber' and 
			BookingDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";  
				
			$StmtLotDtls = mysql_query($SqlLotDetails);
			if(mysql_num_rows($StmtLotDtls) =='0'){
				return true;
			}else{
				return false;
			}
	}	
	
	
	public function checkLotNoAval($PurchaseDate,$LotNumber,$LotId){
		//echo $LotId;
			if($LotId!=''){
			$Qry="and RmLotID!='$LotId'";	
			}
			
			$CheckYear=explode('-', $PurchaseDate); 		
			
			if($CheckYear[1] > 3){
			$DateStart=$CheckYear[0].'-04-01';  //2021-04-01
			$DateEnd=($CheckYear[0]+1).'-03-31'; //2022-03-31
			}else{
			$DateStart=($CheckYear[0]-1).'-04-01';  //2021-04-01
			$DateEnd=($CheckYear[0]).'-03-31'; //2022-03-31
			}
			
		 	
			
		 	 $SqlLotDetails="SELECT * FROM `RmLotDetatils`
			WHERE LotNumber='$LotNumber' and 
			PurchaseDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";  
				
			$StmtLotDtls = mysql_query($SqlLotDetails);
			if(mysql_num_rows($StmtLotDtls) =='0'){
				return true;
			}else{
				return false;
			}
	}	
	
	public function getCurrentDateTime(){
			$TimeFormat = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
			$TimeStamp=$TimeFormat->format("Y-m-d  H:i:s"); 
		 	$Year=$TimeFormat->format("Y"); 
			$AryCurrentDate['TimeStamp']=$TimeStamp;			
			$AryCurrentDate['Year']=$Year;			
			
			return $AryCurrentDate;
	}
	
	
	public function PostRmDefects(){
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';	
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			$LOT_ID=$data['LOT_ID'];
			$USER_ID=$data['USER_ID'];
			$ARY_COUNTS = $data['DATA'];
			$ARY_COUNTS = json_decode($ARY_COUNTS,true);
					
			
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
			$SUBMISSION_DATE=$dateTime->format("Y-m-d  H:i:s"); 
			
			
			foreach($ARY_COUNTS as $value){
				$ROW_COUNT++;
				$S_LOT_NO=$S_LOT_NO.'-'.$ROW_COUNT;
				 $WEIGHT_IN_KGS=$value['TotalWeight'];
				$DefectName=$value['DefectName'];
				 $ss_post="INSERT INTO `RM_DEFECTS_DETAILS`(`LOT_ID`, `USER_ID`, `SUBMISSION_DATE`,
				 `NAME`, `WEIGHT`, `TUBS`, `COUNT`, `RATE`, `STATUS`)
				 VALUES ('$LOT_ID','$USER_ID','$SUBMISSION_DATE','$DefectName','$WEIGHT_IN_KGS',
				 '0','0','0','0')";																
				
			$sqlca_post = mysql_query($ss_post,$this->db);
			
			}
			if($sqlca_post){
				
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Lot Created Successful");
				$temp = array('status' => $STATUS_SUCCESS, "LOT_ID" => $LOT_ID."", "InwardList"=>$result, "msg" => "Successful","ProcessingUnits" => $ary_pu);			
				$this->response($this->json($temp), 200);
				}else{
					 echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" => "Error occured Please Contact Admin ");
				$this->response($this->json($records), 200);
				}	
		}		
		

		
	public function postHOSOWDataWeb($UNIT_ID,$LOT_NO){

			$json = file_get_contents('php://input');
			$data = json_decode($json,true);

			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$dt=$dateTime->format("Y-m-d  H:i:s");

			$JSON_DATA=$data;

			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';

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




			if($TYPE_ID==$Head_On_Weightment_ID ){
					$TABLE='PPC_WEIGHTMENT_HON';
			}else if($TYPE_ID==$Head_less_Weightment_ID){
					$TABLE='PPC_WEIGHTMENT_HL';
			}else if($TYPE_ID==$Head_On_Grading_ID){
					$TABLE='PPC_GRADING_HON';
			}else if($TYPE_ID==$Head_less_Grading_ID){
					$TABLE='PPC_GRADING_HL';
			}


			if($MIX==1){
				$MixIds=$LOT_ID;
				$LOT_ID_ARY=explode('_',$LOT_ID);
			}else{
				$MixIds='NA';
				$LOT_ID_ARY[]=$LOT_ID;
			}

			foreach($LOT_ID_ARY as $LOT_ID){

			$sql_pu="SELECT count(*) as TOTAL FROM `$TABLE` where LOT_ID='$LOT_ID' and DATA_TYPE='$DATA_TYPE'";
			$sqlca_pu = mysql_query($sql_pu);
			$rlt = mysql_fetch_array($sqlca_pu,MYSQL_ASSOC);

			if($rlt['TOTAL'] == 0){

			if($TYPE_ID==$Head_less_Grading_ID){
			 $ss_post="INSERT INTO `$TABLE`( `LOT_ID`,`DATA_TYPE`,`GRADING_MACHINE`,`USER_ID`, `SUBMISSION_DATE`,`IsMix`,`MixIds`,`HOSO_W_DATA`)
			 VALUES  ('$LOT_ID','$DATA_TYPE','$GradingMachine','$USER_ID','$dt','$MIX','$MixIds','$HOSO_W_DATA')";
			}else{
				 $ss_post="INSERT INTO `$TABLE`( `LOT_ID`,`DATA_TYPE`,`USER_ID`, `SUBMISSION_DATE`,`IsMix`,`MixIds`,`HOSO_W_DATA`)
			 VALUES  ('$LOT_ID','$DATA_TYPE','$USER_ID','$dt','$MIX','$MixIds','$HOSO_W_DATA')";
			}
			 $sqlca_post = mysql_query($ss_post,$this->db);

			}else if($From='Web'){

			if($TYPE_ID==$Head_less_Grading_ID){
			 $ss_post="Update `$TABLE` set HOSO_W_DATA='$HOSO_W_DATA' where LOT_ID='$LOT_ID'";
			}else{

			}
			 $sqlca_post = mysql_query($ss_post,$this->db);
			}else{
				$sqlca_post=false;
				$msg='Report Already Exist';
			}
			}


				if($sqlca_post){
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Successful");
				$this->response($this->json($records), 200);

				}else{
					// echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" =>$msg);
				$this->response($this->json($records), 200);
				}

}	

public function postPendingGrading(){

			$LotID=intval($_GET['LotID']);
			
			
			$SqlGradingData="SELECT * FROM `PpcHLGradingDetails` WHERE LotID='$LotID'";
			$StmtGradingData = mysql_query($SqlGradingData);
			$RowGradingData = mysql_fetch_array($StmtGradingData,MYSQL_ASSOC);
			$GradingData=$RowGradingData['GradingData'];
			
			
			echo $SqlGradesBreakUp="select * from `PpcHLGradingBreakup` where LotID='$LotID'";
			$StmtGradesBradkUp = mysql_query($SqlGradesBreakUp,$this->db);
			$RowGradingBrk = mysql_fetch_array($StmtGradesBradkUp,MYSQL_ASSOC);
			
			if($RowGradingBrk['LotID']==''){
			
			
			$this->postGradingBreakUp($LotID,$GradingData);
			}else {
			echo 'error';
			}
			
			
			
	
}	
			
public function postGrading($UNIT_ID,$LOT_NO){
	
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
			$PostedDate=$dateTime->format("Y-m-d  h:i:s"); 
			
			$JSON_DATA=$data;
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
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
			
			
			
			
			if($TypeID=='7'){
			$TABLE='PpcHonGradingDetails';
			}else if($TypeID=='8'){
			$TABLE='PpcHLGradingDetails';
			}
			
			
			
			
			
			$SqlGradingData="SELECT count(*) as TOTAL FROM `$TABLE` where LotID='$LotID' ";
			$StmtGradingData = mysql_query($SqlGradingData);
			$RowGradingData = mysql_fetch_array($StmtGradingData,MYSQL_ASSOC);
			$IsGrades=false;
			
			if($RowGradingData['TOTAL'] == 0){
			
			if($TypeID=='7'){
			 $ss_post="INSERT INTO `$TABLE`( `LotID`, `UserID`, `GradingDate`, `StartTime`, `EndTime`, `BreakTime`, `PostedDate`, `MachineID`, `GradingData`)
			 VALUES  ('$LotID','$UserID','$GradingDate','$StartTime','$EndTime','$BreakTime','$PostedDate','$MachineID','$GradingData')";
			$IsGrades=$this->postHonGradingBreakUp($LotID,$GradingData);
			}else{
				$ss_post="INSERT INTO `$TABLE`( `LotID`, `UserID`, `GradingDate`, `StartTime`, `EndTime`, `BreakTime`, `PostedDate`, `MachineID`, `GradingData`)
				VALUES  ('$LotID','$UserID','$GradingDate','$StartTime','$EndTime','$BreakTime','$PostedDate','$MachineID','$GradingData')";
				$IsGrades=$this->postGradingBreakUp($LotID,$GradingData);
			
			}
				if($IsGrades){
					$sqlca_post = mysql_query($ss_post,$this->db);
					}
			
			}else{
				$sqlca_post=false;
				$msg='Report Already Exist';
			}
			
				
				if($sqlca_post){
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Successful");
				$this->response($this->json($records), 200);
				
				}else{
					// echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" =>$msg);
				$this->response($this->json($records), 200);
				}		
	
}	

public function postGradingBreakUp($LotID,$GradingData){
			
			$this->postGradingDefects($LotID,$GradingData);

			$GradingData=json_decode($GradingData,true);
			
			foreach($GradingData  as $value){				
				//$AryCounts[]=$value; 
				if(!in_array($value['Count'],$AryCounts,true)){
				$AryCounts[]= $value['Count'];
				}
			}
			
			sort($AryCounts);
	
			foreach($AryCounts as $HLCount){	
				
			if($HLCount !=0 && $HLCount !='Peeling' && $HLCount !='g2'){

			$HLWeight=0;		
			
			foreach($GradingData  as $value){			
			if($value['Count'] == $HLCount){
				 $HLWeight=$HLWeight + $value['NetWeight'];
			}			
			}
			$SqlGradesBreakUp="INSERT INTO `PpcHLGradingBreakup`(`LotID`,`HLWeight`,`HLCount`) 
			VALUES ('$LotID','$HLWeight','$HLCount')";
			$StmtGradesBradkUp = mysql_query($SqlGradesBreakUp,$this->db);
			}
			
				}
			if($StmtGradesBradkUp){
				$this->getCostingBreakUp($LotID);
				return true;
			}else{
				return false;
			}

}


function getCostingBreakUp($LotID){
	
			//$LotID=13007;
			
			$SqlFinalGrading="SELECT * FROM `PpcHLGradingBreakup` WHERE LotID='$LotID' order by HLCount ASC";
			$StmtFinalGrading=mysql_query($SqlFinalGrading,$this->db);
			
				
			 $SqlTrackId="SELECT * FROM `SalesCostingRatesHistory` 
				where FromDate <= (select PurchaseDate from RmLotDetatils where RmLotID='$LotID')
				order by FromDate DESC, ID DESC  LIMIT 1";
			 $StmtTrackId=mysql_query($SqlTrackId,$this->db);
			 $RowTrackId=mysql_fetch_array($StmtTrackId,MYSQL_ASSOC);
			 $TrackID=$RowTrackId['ID'];
				
			
			while($RowFinalCounts=mysql_fetch_array($StmtFinalGrading,MYSQL_ASSOC)){
				
			$HLCount	=$RowFinalCounts['HLCount'];			
			$HlWeight	=$RowFinalCounts['HlWeight'];							
				
			$SqlCostingRates="SELECT * FROM SalesCostingRates WHERE TrackID='$TrackID' 
			and LowerLimit < '$HLCount' and UpperLimit >= '$HLCount'";
			$StmtCostingRates=mysql_query($SqlCostingRates,$this->db);
			$RowCostingRates =mysql_fetch_array($StmtCostingRates,MYSQL_ASSOC);
			
			$Usd=$RowCostingRates['USD'];	
			$Grade=$RowCostingRates['Grade'];
			
			 $GradesUsdQuery="UPDATE PpcHLGradingBreakup set PackingGrade='$Grade',Usd='$Usd' 
			where LotID='$LotID' and HLCount='$HLCount'";
			$GradesUsdExec=mysql_query($GradesUsdQuery,$this->db);
			
			}
	}

public function updateCostingRates(){
	
		$SqlLots="SELECT *,
(SELECT COUNT(*) from PpcHLGradingBreakup WHERE LotID=RMD.RmLotID ) as GradedCount
from RmLotDetatils as RMD WHERE RMD.PurchaseDate BETWEEN '2022-04-01' and '2022-04-09'";
			$StmtLots=mysql_query($SqlLots,$this->db);
				while($RowLots=mysql_fetch_array($StmtLots,MYSQL_ASSOC)){
					if($RowLots['GradedCount'] > 0){
					echo $LotID=$RowLots['RmLotID'];
					$this->getCostingBreakUp($LotID);
					}
				}

}

public function postHonGradingBreakUp($LotID,$GradingData){
			
			//$this->postGradingDefects($LotID,$GradingData);

			 $GradingData=json_decode($GradingData,true);
			
			foreach($GradingData  as $value){				
				//$AryCounts[]=$value; 
				if(!in_array($value['Count'],$AryCounts,true)){
				$AryCounts[]= $value['Count'];
				}
			}
			
			sort($AryCounts);
	
			foreach($AryCounts as $HonCount){	
				
			if($HonCount !=0 ){

			$HonWeight=0;		
			
			foreach($GradingData  as $value){			
			if($value['Count'] == $HonCount){
				 $HonWeight=$HonWeight + $value['NetWeight'];
			}			
			}
			$SqlHonGradesBreakUp="INSERT INTO `PpcHonGradingBreakup`(`LotID`, `HonWeight`, `HonCount`) 
			VALUES ('$LotID','$HonWeight','$HonCount')";
			$StmtHonGradesBreakUp = mysql_query($SqlHonGradesBreakUp,$this->db);
			}
			
				}
			if($StmtHonGradesBreakUp){
				return true;
			}else{
				return false;
			}

}



public function postGradingDefects($LotID,$GradingData){
	

			$GradingData=json_decode($GradingData,true);
			
			foreach($GradingData  as $value){				
			
				if($value['Defect'] !='' && !in_array($value['Defect'],$AryDefects,true)){
				$AryDefects[]= $value['Defect'];
				}
			}
			
			sort($AryDefects);
	
			foreach($AryDefects as $DefectID){	
				
			$HLWeight=0;		
			
			foreach($GradingData  as $value){			
			if($value['Defect'] == $DefectID){
				 $HLWeight=$HLWeight + $value['NetWeight'];
				 $HLCount=$value['Count'];
			}			
			}
			$SqlGradesDefects="INSERT INTO `PpcHLGradingDefects`(`LotID`,`HLWeight`,`HLCount`,`DefectID`) 
			VALUES ('$LotID','$HLWeight','$HLCount','1')";
			$StmtGradesDefects = mysql_query($SqlGradesDefects,$this->db);
			} 
			
				
			if($StmtGradesDefects){ 
				return true;
			}else{
				return false;
			}

}


public function PostFinancier(){
	
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
			$dt=$dateTime->format("Y-m-d  h:i:s"); 
			
			$JSON_DATA=$data;
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
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
			
			 $ss_post="INSERT INTO `RM_FINANCIERS`(`FINANCIER_NAME`, `BANK_NAME`, `BANK_ACC_NO`, `GST_NO`, `IFSC_CODE`, `PanNumber`, `FinancierAddress`) 
	VALUES ('$FinancierNAme','$BankName','$BankAccNo','$GstNo','$IfscCode','$PanNumber','$FinancierAddress')";
			

			 $sqlca_post = mysql_query($ss_post,$this->db);
			
			/*}else{
				$sqlca_post=false;
				$msg='Report Already Exist';
			}*/
			
				
				if($sqlca_post){
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Successful");
				$this->response($this->json($records), 200);
				
				}else{
					// echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" =>$msg);
				$this->response($this->json($records), 200);
				}		
	
}	



public function PostOrgno(){
	
			$json = file_get_contents('php://input');
			 $data = json_decode($json,true);
			
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
			$dt=$dateTime->format("d/m/y  h:i A"); 
			
			 $JSON_DATA=$data;
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			$PostedUserID = $data['PostedUserID'];
			$LotID = $data['LotID'];
			$DATA = $data['DATA'];
			 $DATA = json_decode($DATA,true);
			
			//print_r($DATA);
			//exit;
			
			
			
			foreach($DATA as $value){
				
				$CheckedCount=$value['CheckedCount'];
				$BlackGills=$value['BlackGills'];
				$DroopingHead=$value['DroopingHead'];
				$Soft=$value['Soft'];
				$BlackSpot=$value['BlackSpot'];
				$Broken=$value['Broken'];
				$ImproperLegs=$value['ImproperLegs'];
				$Bends=$value['Bends'];
				$BigPieces=$value['BigPieces'];
				$SmallPieces=$value['SmallPieces'];
				$SmallPieces=$value['BlackTail'];
				$Remarks=$value['Remarks'];
				$FitForHonPack="";
				//$Remarks="";
				
				$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
				$PostedDate=$dateTime->format("Y-m-d  h:i:s"); 
				
				
			


  
				$ss_post="INSERT INTO `QcRmOrgnDetails`(`LotID`, `PostedDate`, `CheckedCount`,
				`BlackGills`, `DroopingHead`, `Soft`, `BlackSpot`, `Broken`, `ImproperLegs`, `Bends`,
				`BigPieces`, `SmallPieces`, `FitForHonPack`, `Remarks`, `PostedUserID`)
					VALUES ('$LotID','$PostedDate','$CheckedCount','$BlackGills','$DroopingHead',
							'$Soft','$BlackSpot','$Broken','$ImproperLegs',
							'$Bends','$BigPieces','$SmallPieces','$FitForHonPack',
							'$Remarks','$PostedUserID')";

			
			    $sqlca_post = mysql_query($ss_post,$this->db);
			}
			
			
			
			
			
			
				
				if($sqlca_post){
					
				$records = array('Error' => null, "msg" => "Successful");
				$this->response($this->json($records), 200);
				
				}else {
				$Error= mysql_errno($this->db);
				$records = array('Error' => $Error, "msg" => "Failed");
				$this->response($this->json($records), 200);
				}		
	
}	



public function getValueAdditionData(){
			
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
		
		 
			
			
			$UNIT_ID=intval($_GET['UNIT_ID']);
			
			//SHIFTS
			
			$ShiftQuery = "SELECT * FROM `PpcOptions` WHERE Value ='SHIFTS' and Status =1";		
			$ShiftExec = mysql_query($ShiftQuery);
			
			while($RowShift = mysql_fetch_array($ShiftExec,MYSQL_ASSOC)){
					$ShiftsData[] = $RowShift;
					$ShiftsNames[] = $RowShift['Name'];
				}				
				$AryShifts['Data']=$ShiftsData;
				$AryShifts['Names']=$ShiftsNames;
				
			//VARIETY	
			
			$VarietyQuery = "SELECT * FROM `PpcOptions` WHERE Value ='VARIETY' and Status =1";		
			$VarietyExec = mysql_query($VarietyQuery);
			
			while($RowVariety = mysql_fetch_array($VarietyExec,MYSQL_ASSOC)){
					$VarietyData[] = $RowVariety;
					$VarietyNames[] = $RowVariety['Name'];
				}				
				$AryVariety['Data']=$VarietyData;
				$AryVariety['Names']=$VarietyNames;

			//RmVAStations

			$StationsQuery = "SELECT * FROM `RmVAStations` WHERE UnitStatus=1";		
			$StationsExec = mysql_query($StationsQuery);
			
			while($RowStations = mysql_fetch_array($StationsExec,MYSQL_ASSOC)){
					$StationsData[] = $RowStations;
					$StationsNames[] = $RowStations['UnitName'];
				}				
				$AryStations['Data']=$StationsData;
				$AryStations['Names']=$StationsNames;
				
		
			
			$WorkersContractor=$this->getWorkers($UNIT_ID);
			$Wages=$this->getWageRateKgs($UNIT_ID);
			
	
			
				$records = array(
				'Varity' => $AryVariety,
				"Shift" => $AryShifts,
				"WorkersContractor"=>$WorkersContractor,
				"PerKgWages"=>$Wages,
				"Units"=>$AryStations
				); 
				$this->response($this->json($records), 200);					
}

public function getRmVaStations($UNIT_ID){
		
			
			 $sql_pu="SELECT * FROM `RM_PROCESSING_STATIONS` WHERE UNIT_ID='$UNIT_ID' order by UNIT_NAME";
				
		$sqlca_pu = mysql_query($sql_pu);
			
			
		while($rlt = mysql_fetch_array($sqlca_pu,MYSQL_ASSOC)){
					$ary_pu[] = $rlt;
				}			
				return $ary_pu;	
}

	


public function PostRmStatus(){
	
	$LotID=intval($_GET['LotID']);
 	$Option=$_GET['Option'];
 	$PostedUserID=$_GET['PostedUserID'];
	

		
		
		
		for($i=0; $i< 3; $i++ ){
				
				$CheckedCount=0;
				$BlackGills=0;
				$DroopingHead=0;
				$Soft=0;
				$BlackSpot=0;
				$Broken=0;
				$ImproperLegs=0;
				$Bends=0;
				$BigPieces=0;
				$SmallPieces=0;
				$SmallPieces=0;
				$Remarks=0;
				$FitForHonPack="";
				$Remarks=$Option;
				
				$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
				$PostedDate=$dateTime->format("Y-m-d  h:i:s"); 
				
				
			


  
				$ss_post="INSERT INTO `QcRmOrgnDetails`(`LotID`, `PostedDate`, `CheckedCount`,
				`BlackGills`, `DroopingHead`, `Soft`, `BlackSpot`, `Broken`, `ImproperLegs`, `Bends`,
				`BigPieces`, `SmallPieces`, `FitForHonPack`, `Remarks`, `PostedUserID`)
					VALUES ('$LotID','$PostedDate','$CheckedCount','$BlackGills','$DroopingHead',
							'$Soft','$BlackSpot','$Broken','$ImproperLegs',
							'$Bends','$BigPieces','$SmallPieces','$FitForHonPack',
							'$Remarks','$PostedUserID')";

			
			    $sqlca_post = mysql_query($ss_post,$this->db);
			}
			
			
			
			
			
			
				
				if($sqlca_post){
					
				$records = array('Error' => null, "msg" => "Successful");
				$this->response($this->json($records), 200);
				
				}else {
				$Error= mysql_errno($this->db);
				$records = array('Error' => $Error, "msg" => "Failed");
				$this->response($this->json($records), 200);
				}		
	
}	

	public function LotList($UNIT_ID,$LOT_NO){
	
	        $Year='2021';
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';
			
			$DateStart="2020-07-01";
			$DateEnd="2020-07-31 23:59:59";

			 $json = file_get_contents('php://input');
			 $data = json_decode($json,true);
			
				$UNIT_ID = $data['UNIT_ID'];			
				$STATUS = $data['STATUS'];
				$REMARKS = $data['REMARKS'];
				$TYPE_ID = $data['TYPE_ID'];
			//	$TYPE_ID = 8;
			if($TYPE_ID==''){	
				$temp = array('status' => $STATUS_FAILURE, "msg" => "Please Update App");			
				$this->response($this->json($temp), 200);
			}else{
				
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
				
				if($REMARKS=='HON Grading'){
					$REMARKS='Head On Grading';
				}else if($REMARKS=='HL Grading'){
					$REMARKS='Headless Grading';
				}
				
				if($REMARKS=='HON Weightment'){
					$REMARKS='Head On Grading';
				}else if($REMARKS=='HL Grading'){
					$REMARKS='Headless Grading';
				}
				
				$limit=500;
				
				//  $Query="and IMD.PURCHASE_DATE between '$DateStart' and '$DateEnd'";
				
				 
				if($TYPE_ID==$Creates_ID){
				$sqlca="SELECT *
			FROM `RmLotDetatils` as RMD left join  
			RmPurchaseStations as RPS on RMD.PurchaseStationID=RPS.PurchaseStationID 
			where RMD.LotStatus='1'  and PurchaseDate >= DATE_SUB(now(), INTERVAL 6 DAY)
			ORDER BY RMD.PurchaseStationID Asc ";
				
				}else if($TYPE_ID==$ph_sample_ID){
				$sqlca="SELECT * FROM `RmLotDetatils` as IMD 
				where IMD.SAMPLE_TYPE=='PRE HARVEST' 
				ORDER BY  IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";	
				}else if($TYPE_ID==$Lot_LIST_ID){
				$sqlca="SELECT * FROM `RmLotDetatils` as RMD 
				left join  QcRmAbReport 
				 as ABR on RMD.RmLotID=ABR.LotID
				where RMD.LotStatus='1'  
				ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC LIMIT 100";
				}else if($TYPE_ID==$RM_ANALYSIS_ID){
				 $sqlca="select * from RmLotDetatils as RMD where  
							 not exists (select 1 from QcRmOrgnDetails as  RMDD where RMDD.LotID = RMD.RmLotID )
							and  LotStatus=1							
							ORDER BY  RMD.PurchaseDate DESC, RMD.LotNumber DESC limit $limit";
				}else if($TYPE_ID==$RM_DEFECTS_ID){
				 $sqlca="select * from RmLotDetatils as IMD where IMD.PurchaseDate !='' 
				 and not exists (select 1 from RmBillDetails RMDD where RMDD.LotID = IMD.RmLotID )
                 and LotStatus=1	
				 ORDER BY  IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";
				}else if($TYPE_ID==$Head_On_Weightment_ID){
					$sqlca="select * from RmLotDetatils as IMD where not exists 
				(select 1 from PPC_WEIGHTMENT_HON t2 where t2.LOT_ID = IMD.RmLotID  ) 
				AND IMD.SAMPLE_TYPE='' 
				ORDER BY IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";
				
				}else if($TYPE_ID==$Head_less_Weightment_ID){
				$sqlca="select * from RmLotDetatils as IMD where not exists 
				(select 1 from PPC_WEIGHTMENT_HL t2 where t2.LOT_ID = IMD.RmLotID  ) 
				AND IMD.SAMPLE_TYPE=''   
				ORDER BY IMD.LotNumber DESC, IMD.LotNumber DESC LIMIT $limit ";
				
				}else if($TYPE_ID==$Head_On_Grading_ID){
				$sqlca="select * from RmLotDetatils as IMD where not exists 
				(select 1 from PpcHonGradingDetails t2 where t2.LotID = IMD.RmLotID )  
				ORDER BY IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit";
				
				}else if($TYPE_ID==$Head_less_Grading_ID){
				 $sqlca="select * from RmLotDetatils as RMD left join  QcRmAbReport 
				 as ABR on RMD.RmLotID=ABR.LotID where not exists 
				(select 1 from PpcHLGradingDetails HlGD where HlGD.LotID = RMD.RmLotID ) 
				and  LotStatus=1						
				ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC LIMIT  $limit"; 
				//AND t2.DATA_TYPE='2'
				}else if($TYPE_ID==$PpcValueAddition){
				$sqlca="select * from RmLotDetatils as IMD where 
					 LotStatus=1 
					ORDER BY IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT $limit"; 
				
				}else if($TYPE_ID==$SOAKING_ID){
				$sqlca="select * from RmLotDetatils as RMD left join  QcRmAbReport 
				 as ABR on RMD.RmLotID=ABR.LotID where 
				  LotStatus=1						
				ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC limit 500"; 
				
				}else{
				$sqlca="SELECT * FROM `RmLotDetatils` as IMD where IMD.SAMPLE_TYPE!='PRE HARVEST' 
				ORDER BY IMD.PurchaseDate DESC, IMD.LotNumber DESC LIMIT 500";	
				}
				
			//echo $sqlca; 
	
		
				
		$sqlca_c_user = mysql_query($sqlca);
			
			
		if(mysql_num_rows($sqlca_c_user) =='0'){
			$temp = array('Error' => "No Lots Found");
			$this->response($this->json($temp), 200);
		}else{
			
				while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$result[] = $rlt;
				}
				
	
		
		$Msg= mysql_num_rows($sqlca_c_user) ." Lots Avaiable";
					
		$temp = array('Error' => null,"Msg" => $Msg, "LotList"=>$result );	
		$this->response($this->json($temp), 200);
		
			}}
			
}





public function preHarvestData($UNIT_ID,$LOT_NO){
	
	if($UNIT_ID==''){
		$UNIT_ID=intval($_GET['UNIT_ID']);
	}
			
			

		$STATUS_SUCCESS='SUCCESS';
		$STATUS_FAILURE='FAILURE';
		
		IF($LOT_NO==''){
		$sqlca="SELECT * FROM `inputmaterial_details` where UNIT_ID='$UNIT_ID' and SAMPLE_TYPE='PRE HARVEST' ORDER BY id DESC";	
		}ELSE{
		$sqlca="SELECT * FROM `inputmaterial_details` where LOT_NO='$LOT_NO' and SAMPLE_TYPE='PRE HARVEST' ORDER BY id DESC";	
		}
			
		
				
		$sqlca_c_user = mysql_query($sqlca);
			
			
		if(mysql_num_rows($sqlca_c_user) =='0'){
			$temp = array('status' => $STATUS_FAILURE, "msg" => "No User Exist Please Register");
			$this->response($this->json($temp), 200);
		}else{
		while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$result[] = $rlt;
				}
				
				
		
		$sql_pu="SELECT * FROM `PROCESSING_UNITS`";
				
		$sqlca_pu = mysql_query($sql_pu);
			
			
		while($rlt = mysql_fetch_array($sqlca_pu,MYSQL_ASSOC)){
					$ary_pu[] = $rlt;
				}		
		
				
			
					
		$temp = array('status' => $STATUS_SUCCESS, "InwardList"=>$result, "msg" => "Successful","ProcessingUnits" => $ary_pu);
			
		$this->response($this->json($temp), 200);
		
		}
}




public function PostToProcessing(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$JSON_DATA=$data;
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			$REFERENCE_ID_OLD = $data['REFERENCE_ID'];
			$PROCESSING_UNIT_ID = $data['PROCESSING_UNIT_ID'];
			$PROCESSING_TYPE = $data['PROCESSING_TYPE'];
			$WEIGHT_IN_KGS = $data['
			'];
			$DRIVER_NAME=$data['DRIVER_NAME'];
			$VEHICAL_NO=$data['VEHICAL_NO'];
			$AVAL_WEIGHT_TO_PROCESS=$data['AVAL_WEIGHT_TO_PROCESS'];
			$WEIGHT_UNDER_PROCESSING=$data['WEIGHT_UNDER_PROCESSING'];
			$LOT_NUMBER=$data['LOT_NUMBER'];
			$FROM_UNIT_ID=$data['FROM_UNIT_ID'];
			$INPUT_MATERIAL_FORM=$data['INPUT_MATERIAL_FORM'];
			
			$RE_ASSIGN=$data['RE_ASSIGN'];
			$SUB_REF_ID=$data['SUB_REF_ID'];
			$REMARKS=$data['REMARKS'];
		
			
			
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
			$dt=$dateTime->format("d/m/y  h:i A"); 
			
			$sql="SELECT count(*) as total from MATERIAL_PROCESSING_DATA WHERE LOT_NUMBER='$LOT_NUMBER'";
			
			
			$result=mysql_query($sql);
			$data=mysql_fetch_assoc($result);
			$count=$data['total']+1;
			
			$REFERENCE_ID= $LOT_NUMBER."/".$count;
			
			if($RE_ASSIGN=='1'){
				$SUB_REF_ID=$REFERENCE_ID_OLD;
			}
			
		
			
			if($PROCESSING_UNIT_ID==$FROM_UNIT_ID && $PROCESSING_TYPE!='SEND TO PRODUCTION'){	
			$STATUS='5'; 
			$RECIEVED_WEIGHT=$WEIGHT_IN_KGS;
			}else{
			$STATUS='0';
			$RECIEVED_WEIGHT='';			
			}
			
			
			
			
			 $ss_post="INSERT INTO `MATERIAL_PROCESSING_DATA`( `REQUESTED_DATE`,`REFERENCE_ID`, `ASSIGNED_UNIT_ID`, 
																`PROCESS_NAME`, `WEIGHT_IN_KGS`,`DRIVER_NAME`,`VEHICAL_NO`,`LOT_NUMBER`,
																`FROM_UNIT_ID`,`INPUT_MATERIAL_FORM`,`SUB_REF_ID`,`STATUS`,`RECIEVED_WEIGHT`	)

																			VALUES  
																			
																			('$dt','$REFERENCE_ID','$PROCESSING_UNIT_ID','$PROCESSING_TYPE',
																			'$WEIGHT_IN_KGS','$DRIVER_NAME','$VEHICAL_NO','$LOT_NUMBER',
																			'$FROM_UNIT_ID','$INPUT_MATERIAL_FORM','$SUB_REF_ID','$STATUS','$RECIEVED_WEIGHT')";

			
			$sqlca_post = mysql_query($ss_post,$this->db);
			
				
				if($sqlca_post){
					//$this-> postTimeTrack($REFERENCE_ID,"Request Sent","0");
					
				if($RE_ASSIGN=='1'){
					
					if($REMARKS =="SEND TO PRE PROCESSING"){
						
					$sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET `RE_ASSIGN`=$RE_ASSIGN,
					`RE_ASSIGN_AVAL_WEIGHT`=$AVAL_WEIGHT_TO_PROCESS ,`STATUS`='6'
					WHERE `REFERENCE_ID`='$REFERENCE_ID_OLD'";
					}else{
						
						$sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET `RE_ASSIGN`=$RE_ASSIGN,
					`RE_ASSIGN_AVAL_WEIGHT`=$AVAL_WEIGHT_TO_PROCESS 
					WHERE `REFERENCE_ID`='$REFERENCE_ID_OLD'";
					}
					
					
					$sqlca_update = mysql_query($sql_update,$this->db);	
					
					}else{
					
					
					$sql_update="UPDATE `inputmaterial_details` SET `AVAL_WEIGHT_TO_PROCESS`=$AVAL_WEIGHT_TO_PROCESS,
					`WEIGHT_UNDER_PROCESSING`=$WEIGHT_UNDER_PROCESSING 
					WHERE `LOT_NO`='$LOT_NUMBER'";
					
					$sqlca_update = mysql_query($sql_update,$this->db);
				
				
					}
					
					
					
				
				$this->pushFcm($PROCESSING_UNIT_ID,$JSON_DATA);
					
			
				
					
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Regsitration Successful");
				$this->response($this->json($records), 200);
				
				}else{
					 echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" => "Regsitration Failed");
				$this->response($this->json($records), 200);
				}		


			
}




public function PostReturnWeight(){
			
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$JSON_DATA=$data;
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			$REFERENCE_ID_OLD = $data['REFERENCE_ID'];
			$PROCESSING_UNIT_ID = $data['PROCESSING_UNIT_ID'];
			$PROCESSING_TYPE = $data['PROCESSING_TYPE'];
			$WEIGHT_IN_KGS = $data['WEIGHT_IN_KGS'];
			$DRIVER_NAME=$data['DRIVER_NAME'];
			$VEHICAL_NO=$data['VEHICAL_NO'];
			$AVAL_WEIGHT_TO_PROCESS=$data['AVAL_WEIGHT_TO_PROCESS'];
			$WEIGHT_UNDER_PROCESSING=$data['WEIGHT_UNDER_PROCESSING'];
			$LOT_NUMBER=$data['LOT_NUMBER'];
			$FROM_UNIT_ID=$data['FROM_UNIT_ID'];
			$INPUT_MATERIAL_FORM=$data['INPUT_MATERIAL_FORM'];
			
			$RE_ASSIGN=$data['RE_ASSIGN'];
			$SUB_REF_ID=$data['SUB_REF_ID'];
		
		/*check weather weight avaiable or not  */


			$SQL_SUB_M_P_D="SELECT * FROM MATERIAL_PROCESSING_DATA WHERE REFERENCE_ID='$REFERENCE_ID_OLD'";						
			$STMT_SUB_M_P_D = mysql_query($SQL_SUB_M_P_D);			
			$SUB_M_P_D_ROW = mysql_fetch_array($STMT_SUB_M_P_D,MYSQL_ASSOC);
			
			$SUB_M_P_D_ROW['RE_ASSIGN_AVAL_WEIGHT'];
			
			
			if($SUB_M_P_D_ROW['RE_ASSIGN_AVAL_WEIGHT']>=$WEIGHT_IN_KGS){
				
				$BALANCE_WEIGHT=$SUB_M_P_D_ROW['RE_ASSIGN_AVAL_WEIGHT']-$WEIGHT_IN_KGS;
			
			
			$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
			$dt=$dateTime->format("d/m/y  h:i A"); 
			
			$sql="SELECT count(*) as total from MATERIAL_PROCESSING_DATA WHERE LOT_NUMBER='$LOT_NUMBER'";

			$result=mysql_query($sql);
			$data=mysql_fetch_assoc($result);
			$count=$data['total']+1;
			
			$REFERENCE_ID= $LOT_NUMBER."/".$count;
			
			$SUB_REF_ID=$REFERENCE_ID_OLD;
			
			$STATUS='0';
			
			
			
							
			
			$ss_post="INSERT INTO `SUB_MATERIAL_PROCESSING_DATA`( `REFERENCE_ID`,`RETURN_WEIGHT`,`SUB_REFERENCE_ID`)

																			VALUES  
																			
																			('$REFERENCE_ID','$WEIGHT_IN_KGS','$SUB_REF_ID')";

			
			$sqlca_post = mysql_query($ss_post,$this->db);
		

			 $ss_post="INSERT INTO `MATERIAL_PROCESSING_DATA`( `REQUESTED_DATE`,`REFERENCE_ID`, `ASSIGNED_UNIT_ID`, 
																`PROCESS_NAME`, `WEIGHT_IN_KGS`,`DRIVER_NAME`,`VEHICAL_NO`,`LOT_NUMBER`,
																`FROM_UNIT_ID`,`INPUT_MATERIAL_FORM`,`SUB_REF_ID`,`STATUS`	)

																			VALUES  
																			
																			('$dt','$REFERENCE_ID','$PROCESSING_UNIT_ID','$PROCESSING_TYPE',
																			'$WEIGHT_IN_KGS','$DRIVER_NAME','$VEHICAL_NO','$LOT_NUMBER',
																			'$FROM_UNIT_ID','$INPUT_MATERIAL_FORM','$SUB_REF_ID','$STATUS')";

			
			$sqlca_post = mysql_query($ss_post,$this->db);
			
			
			
				
				if($sqlca_post){
					
					//$this-> postTimeTrack($REFERENCE_ID,"Request Sent","0");
					
					
					
						$TOTAL_RETURN_WEIGHT='0';
						$sql_return_weight="SELECT * FROM SUB_MATERIAL_PROCESSING_DATA WHERE SUB_REFERENCE_ID='$SUB_REF_ID'";
						
						$sqlca_return_weight = mysql_query($sql_return_weight);
			
			
							while($rlt = mysql_fetch_array($sqlca_return_weight,MYSQL_ASSOC)){
								$TOTAL_RETURN_WEIGHT =$TOTAL_RETURN_WEIGHT + $rlt['RETURN_WEIGHT'];
								}	
					
					$sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET 
					`IS_RETURN_WEIGHT`='$TOTAL_RETURN_WEIGHT',
					`RE_ASSIGN_AVAL_WEIGHT`='$BALANCE_WEIGHT'
					
					WHERE `REFERENCE_ID`='$REFERENCE_ID_OLD'";
					$sqlca_update = mysql_query($sql_update,$this->db);	
					
					
				
				$this->pushFcm($PROCESSING_UNIT_ID,$JSON_DATA);
					
			
				
					
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Regsitration Successful");
				$this->response($this->json($records), 200);
				
				}else{
					// echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" => "Regsitration Failed");
				$this->response($this->json($records), 200);
				}		
			}else{
					// echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" => "Above Weight");
				$this->response($this->json($records), 200);
				}	

			
}
			
				
			
		
		   
public function ProcessRequests($UNIT_ID , $STATUS){
			
		
			
			$json = file_get_contents('php://input');
			 $data = json_decode($json,true);
			
			if($data!= ""){
				$UNIT_ID = $data['UNIT_ID'];
				$STATUS = $data['STATUS'];
				$USER_TYPE = $data['USER_TYPE'];
			}
			
			
			
	

		$STATUS_SUCCESS='SUCCESS';
		$STATUS_FAILURE='FAILURE';
		
		if($USER_TYPE=='Post Processing Supervisor'){
		 $sqlca="SELECT * FROM MATERIAL_PROCESSING_DATA where 
			 ASSIGNED_UNIT_ID=$UNIT_ID 
			 and STATUS=$STATUS
			 and PROCESS_NAME='SEND TO PRODUCTION'
			 

			 ORDER BY `LOT_NUMBER` DESC";
		
		}else{
			 
			 
		 $sqlca="SELECT * FROM MATERIAL_PROCESSING_DATA where ASSIGNED_UNIT_ID='$UNIT_ID' 
			 and STATUS='$STATUS' and PROCESS_NAME !='SEND TO PRODUCTION' 
			 
			 ORDER BY `LOT_NUMBER` DESC";
			 
		
		}
			
				
		$sqlca_c_user = mysql_query($sqlca);
			
		
				
		if(mysql_num_rows($sqlca_c_user) =='0'){
			$temp = array('status' => $STATUS_FAILURE, "msg" => "No User Exist Please Register");
			$this->response($this->json($temp), 200);
		}else{
			
		
		
		while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
			
			
					$reference_id=$rlt['LOT_NUMBER'];
					$FROM_UNIT_ID=$rlt['FROM_UNIT_ID'];
					$ASSIGNED_UNIT_ID=$rlt['ASSIGNED_UNIT_ID'];
					$rlt['Deatails']=$this->varityDetails($reference_id);

					if($rlt['Deatails']!=''){
					
					if($rlt['STATUS'] == '6' && $rlt['RE_ASSIGN_AVAL_WEIGHT'] == '0'){
					}else if($rlt['STATUS'] == '7' && $rlt['RE_ASSIGN_AVAL_WEIGHT'] == '0' && $rlt['IS_RETURN_WEIGHT'] == '0'){
						
					}else{
					
					$rlt['FROM_UNIT_DETAILS']=$this->getUnitDetails($FROM_UNIT_ID);
					$rlt['UNIT_DETAILS']=$this->getUnitDetails($ASSIGNED_UNIT_ID);
					
					$result[] = $rlt;

					}
					
					}
					}
				
				
			
					
		$temp = array('status' => $STATUS_SUCCESS, "InwardList"=>$result, "msg" => "Successful");
			
		$this->response($this->json($temp), 200);
		
		}
}



public function sendSecurityCheck(){
			
		if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

		$REFERENCE_ID=intval($_GET['REFERENCE_ID']);
			
	

		$STATUS_SUCCESS='SUCCESS';
		$STATUS_FAILURE='FAILURE';
		
		
			$sql_update="UPDATE `inputmaterial_details` SET `STATUS`='0'

					WHERE REFERENCE_ID=$REFERENCE_ID";
					
					$sqlca_update = mysql_query($sql_update,$this->db);
					
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Regsitration Successful");
				$this->response($this->json($records), 200);
			
	}

	public function PpcValueAddition(){
		
	
	
	 $json = file_get_contents('php://input');
	 $JsonData = json_decode($json,true);
	 
	 $PostedUserID = $JsonData['PostedUserID'];
	 $LotID = $JsonData['LotID'];
	 $Data = $JsonData['Data'];
	 $JsonData = json_decode($Data,true);
	 
	$AryCurrentDate= $this->getCurrentDateTime();
	 $PostedDate=$AryCurrentDate['TimeStamp'];	
	
	if($PostedUserID==''){
	$records = array('status' => $STATUS_FAILURE, "msg" =>"Update Progress Try After some Time" );
	$this->response($this->json($records), 200);	
	}
		
	
	foreach($JsonData as $value){	

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
		$DATE_TIME=$dateTime->format("Y-m-d"); 
		$TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 
	 
	  $sql_org_insert="INSERT INTO `PpcValueAddition` 
	  ( `LotID`, `SentCount`, `SentWeight`, `ReceivedWeight`, 
	  `ReturnWeight`, `ProcessingDate`, `PostedDate`, `WtAftrVA`, `CountAftrVA`, 
	  `VarietyID`, `ShiftID`, `StationID`, `PostedUserID`, `Remarks`) 
	 VALUES ('$LotID','$SentCount','$SentWeight','$ReceivedWeight',
	 '$ReturnWeight','$ProcessingDate','$PostedDate','$WtAftrVA','$CountAftrVA','$VarietyID',
	 '$ShiftID','$StationID','$PostedUserID','$Remarks')";
	 
	$sqlca_post = mysql_query($sql_org_insert,$this->db);
	}
	
	
	if($sqlca_post){
		
	$records = array('Error' => "Data saved successfully", "Msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
	
	}else {
	
	$Error="Oops! Something went wrong".mysql_errno($this->db);
	
	$records = array('Error' => $Error );
	$this->response($this->json($records), 200);

	}		


	}



public function varityDetails($LOT_NO){
		
			
		$sql_query = "SELECT * FROM `inputmaterial_details` where LOT_NO='$LOT_NO'";
			
			$sqlca_c_user = mysql_query($sql_query);
			
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$result = $rlt;
				}
				
				return $result;	
}





public function getAcceptRejectRequest(){
			
if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
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
			$DATE_TIME=$dateTime->format("d/m/y  h:i A"); 
			
			
			
			
			

if($RECIEVED_WEIGHT==''){
	
	if($OPTION==2){
		
		$WEIGHT=$AVAL_WEIGHT+$REJECT_WEIGHT;
		$sql_update="UPDATE `inputmaterial_details` SET `AVAL_WEIGHT_TO_PROCESS`='$WEIGHT' 
					
					WHERE $WEIGHT='$LOT_NUM'";
					
		$sqlca_update = mysql_query($sql_update,$this->db);
	}
			
			$sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET `STATUS`=$OPTION , `ACCEPTED_DATE`='$DATE_TIME'
					
					WHERE REFERENCE_ID='$REFERENCE_ID'";
}else {
	
	if( $OPTION =='7'  || $OPTION =='8'){
		
		if($OPTION =='7'){
			$sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET `STATUS`=$OPTION , `COMPLEATED_TIME`='$DATE_TIME' ,
		`RECIEVED_WEIGHT`='$RECIEVED_WEIGHT',
		`WEIGHT_AFTER_PROCESS_IN_KGS`='$RECIEVED_WEIGHT',RE_ASSIGN_AVAL_WEIGHT='$RECIEVED_WEIGHT' WHERE REFERENCE_ID='$REFERENCE_ID'";
		}else if($OPTION =='8'){
			$sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET `STATUS`=$OPTION , `COMPLEATED_TIME`='$DATE_TIME' ,
		`RECIEVED_WEIGHT`='$RECIEVED_WEIGHT',
		`WEIGHT_AFTER_PROCESS_IN_KGS`='$RECIEVED_WEIGHT' WHERE REFERENCE_ID='$REFERENCE_ID'";
		}
		
		
		
		
	}else{
	 $sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET `STATUS`='$OPTION' , `ACCEPTED_DATE`='$DATE_TIME' ,`RECIEVED_WEIGHT`='$RECIEVED_WEIGHT'
					
					WHERE REFERENCE_ID='$REFERENCE_ID'";
	}
}
					
				$sqlca_update = mysql_query($sql_update,$this->db);
			
				
				if($sqlca_update){
					
					if( $OPTION =='7'  || $OPTION =='8'){
						/**/
		
		
		 $sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where REFERENCE_ID='$REFERENCE_ID'";
			
		$sqlca_c_user = mysql_query($sql_query);
			
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$LOT_NO=$rlt['LOT_NUMBER'];	
				}
				
		 $sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$LOT_NO'";
			
		$sqlca_c_user = mysql_query($sql_query);
		
		$IS_COMPLETED=0;//false	
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					
			
					if($rlt['RE_ASSIGN_AVAL_WEIGHT']=='0'){
					
					if( $rlt['STATUS']=='6' || $rlt['STATUS']=='7' || $rlt['STATUS']=='8'){
					$IS_COMPLETED=1;	
					}else{
						$IS_COMPLETED=0;
						break;	
					}
					}else{
					$IS_COMPLETED=0;
					break;					
					}
					
				
				}
				
				
				 
				if($IS_COMPLETED==1){
					
					$sql_query = "SELECT * FROM `inputmaterial_details` where LOT_NO='$LOT_NO'";			
					$sqlca_c_user = mysql_query($sql_query);
					$rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC);
					
					$rlt['AVAL_WEIGHT_TO_PROCESS'];
					if($rlt['AVAL_WEIGHT_TO_PROCESS']==0){
					$IS_COMPLETED=1;	
					}else{
					 $IS_COMPLETED=0;
					}
					
					 
					
					 $sql_imd_update="UPDATE `inputmaterial_details` SET `STATUS`='$IS_COMPLETED' WHERE LOT_NO='$LOT_NO'";
					 $sqlca_imd_update = mysql_query($sql_imd_update,$this->db);
				}
				
		
		
}
					
					 
				
				$this-> pushFcm($UNIT_ID,$data);	

				//$this-> postTimeTrack($REFERENCE_ID,"TEST",$OPTION);
				if($UNIT_ID!=''){
					
				
			
				$this-> ProcessRequests($UNIT_ID,$OPTION);
				
				
				
				
				}else{
				$temp = array('status' => $STATUS_SUCCESS, "msg" => "Successful");
			
				$this->response($this->json($temp), 200);	
				}
				
				}else{
				echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" => "Regsitration Failed");
				$this->response($this->json($records), 200);
				}		


			
}	


		

	

public function postProcessedData(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			$DATA = $data['DATA'];
			$ary_data=json_decode($DATA,true);
			
			
			
			
			
			
			
			
			$WEIGHT_AFTER_PROCESS_IN_KGS = $data['WEIGHT_AFTER_PROCESS_IN_KGS'];
			$PROCESSING_ID = $data['PROCESSING_ID'];
			$LOT_NUMBER = $data['LOT_NUMBER'];
			$REFERENCE_ID = $data['REFERENCE_ID'];
			$STATUS="6";
			
			date_default_timezone_set('Asia/Kolkata');
			$COMPLEATED_TIME=date('Y/m/d h.i A');
			
			
			
			
			 $sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET 
					`WEIGHT_AFTER_PROCESS_IN_KGS`=$WEIGHT_AFTER_PROCESS_IN_KGS,
					`COMPLEATED_TIME`='$COMPLEATED_TIME',
					`STATUS`=$STATUS, 
					`RE_ASSIGN_AVAL_WEIGHT`='$WEIGHT_AFTER_PROCESS_IN_KGS' 
					
					WHERE PROCESSING_ID=$PROCESSING_ID";
					
			$sqlca_update = mysql_query($sql_update,$this->db);


			
				
			if($sqlca_update){
				//$this ->postTimeTrack($REFERENCE_ID,$STATUS_MSG,$STATUS);
				
			$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$LOT_NUMBER'";
			
			$sqlca_c_user = mysql_query($sql_query);
			
			
			$C_GROSS_WEIGHT='0';  //Compleated Weight
			
			$C_ASSIGNED_WEIGHT='0'; //Completated Assigned Weight
			
			$OA_ASSIGNES_WEIGHT='0';  // Over All assigned Weight
			
			$WEIGHT_UNDER_PROCESS='0';
			
			$OVER_ALL_STATUS='';
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
				$OA_ASSIGNES_WEIGHT= $OA_ASSIGNES_WEIGHT+$rlt['WEIGHT_IN_KGS'];
				
				if($rlt['STATUS'] == 3){
				$C_GROSS_WEIGHT=$C_GROSS_WEIGHT+$rlt['WEIGHT_AFTER_PROCESS_IN_KGS'];	
				
				$C_ASSIGNED_WEIGHT=$C_ASSIGNED_WEIGHT+$rlt['WEIGHT_IN_KGS'];	
				}
				}
			
			$WEIGHT_UNDER_PROCESS=$OA_ASSIGNES_WEIGHT-$C_ASSIGNED_WEIGHT;
			
			
	$sql_update="UPDATE `inputmaterial_details` SET `WEIGHT_AFTER_PROCESS`='$C_GROSS_WEIGHT',
			`WEIGHT_UNDER_PROCESSING`='$WEIGHT_UNDER_PROCESS' WHERE REFERENCE_ID='$LOT_NUMBER'";
					
			$sqlca_update = mysql_query($sql_update,$this->db);
				
				
				foreach($ary_data as $item) { 
					 $grade = $item['Grade']; 
					 $weight = $item['Weight']; 
	
					 $ss_post="INSERT INTO `GRADE_DETAILS`( `PROCESSING_ID`,`GRADE`, `WEIGHT`) 
					 VALUES  ('$PROCESSING_ID','$grade','$weight')";

			
				$sqlca_post = mysql_query($ss_post,$this->db);
				}				
					
				
				}else{
					 echo mysql_errno($this->db);
				$records = array('status' => $STATUS_FAILURE, "msg" => "Regsitration Failed");
				$this->response($this->json($records), 200);
				}		


			
}




public function getOverAllDetails(){
			
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
		
			
			$REFERENCE_ID=$_GET['REFERENCE_ID'];
			$UNIT_ID=intval($_GET['UNIT_ID']);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
						
			$postDetails=$this->varityDetails($REFERENCE_ID);
			$process=$this-> parllelProcessList($REFERENCE_ID,$UNIT_ID);
			
			
			$records = array('postDetails' => $postDetails, "process" => $process);
			$this->response($this->json($records), 200);
		
}	




public function parllelProcessList($ref_id,$UNIT_ID){
		
			if($UNIT_ID==''){
				$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$ref_id'";
			}else{
				$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$ref_id' and ASSIGNED_UNIT_ID='$UNIT_ID'";
				
				$sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$ref_id'";
			}
			
			//echo $sql_query;
			$sqlca_c_user = mysql_query($sql_query);
			
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
				
				if($rlt['STATUS'] > 5 && $rlt['RE_ASSIGN_AVAL_WEIGHT'] ==0 ){}else{
				
					$PROCESSING_ID=$rlt['PROCESSING_ID'];
					$FROM_UNIT_ID=$rlt['FROM_UNIT_ID'];
					$ASSIGNED_UNIT_ID=$rlt['ASSIGNED_UNIT_ID'];
					$rlt['FROM_UNIT_DETAILS']=$this->getUnitDetails($FROM_UNIT_ID);
					$rlt['UNIT_DETAILS']=$this->getUnitDetails($ASSIGNED_UNIT_ID);
					$rlt['Details']=$this->getGradesData($PROCESSING_ID);
					$result[] = $rlt;
								}
				}
				
				return $result;	
}

public function getGradesData($PROCESSING_ID){
		
			
			$sql_query = "SELECT * FROM `GRADE_DETAILS` where PROCESSING_ID='$PROCESSING_ID'";
			
			$sqlca_c_user = mysql_query($sql_query);
			
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
				
				
					$result[] = $rlt;
				}
				
				return $result;	
}



public function getUnitDetails($UNIT_ID){
		
			
			$sql_query = "SELECT * FROM `PROCESSING_UNITS` where UNIT_ID='$UNIT_ID'";
			
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
				
				
					$data = $rlt;
				}
				
				return $data;	
}


public function getDropDownData($UnitID){
		
/*		
		if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
		*/
		 
			
			
			$UNIT_ID=intval($_GET['UNIT_ID']);
			$UNIT_ID=1;
			
			
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
		
			
			  $superVisors=$this->getSuperVisors($UNIT_ID);
			  $driver=$this-> getDrivers($UNIT_ID);
			  $vehi=$this-> getVehicles($UNIT_ID);
			  $units=$this-> getUnits($UNIT_ID);
			  $Machinery=$this->getMachinery($UNIT_ID);
			  $BankNames=$this->getBankNames($UNIT_ID);
			  $AgentNames=$this->getAgents($UNIT_ID);
			  $GraderNames=$this->getGraders($UNIT_ID);
			  $BillNames=$this->getBillNames($UNIT_ID);
			  $PurchaseCategory=$this->getPurchaseCategory($UNIT_ID);
			  
			  $Contractor=$this->getContractor($UNIT_ID);
			  $SubContractor=$this->getSubContractor($UNIT_ID);
			  $WorkersContractor=$this->getWorkers($UNIT_ID);
			  
			
			
				$records = array(
				"SuperVisors" => $superVisors,
				"Agents" => $AgentNames,
				"Graders" => $GraderNames,
				"Finaciers" => $BillNames,
				"Drivers" => $driver,
				"Vehicals" => $vehi,
				"Units"=>$units,
				"BankNames"=>$BankNames,
				"Machinery"=>$Machinery,
				"PurchaseCategory"=>$PurchaseCategory,
				"Contractor"=>$Contractor,
				"SubContractor"=>$SubContractor,
				"WorkersContractor"=>$WorkersContractor
			
				);
				
				if($_GET['UNIT_ID']!=''){
				$this->response($this->json($records), 200);
				}else {
					return $records;
				}
			 
					
}	


public function getContractor($UNIT_ID){			
			$sql_query = "SELECT * FROM `PpcContractMaster` ";			
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['ContrctrName'];
					}				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;				
				return $data;	
}


public function getSubContractor($UNIT_ID){			
			$sql_query = "SELECT * FROM `PpcSubContractorMaster`";			
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['SubContractorName'];
					}				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;				
				return $data;	
}

public function getWorkers($UNIT_ID){			
			$sql_query = "SELECT * FROM `PpcSubContractorMaster`";			
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['SubContractorName'];
					}				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;				
				return $data;	
}


public function getWageRateKgs($UNIT_ID){			
			$sql_query = "SELECT * FROM `PpcDailyWageRateKgs`";			
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					}				
				$data['Data']=$RowData;				
				return $data;	
}



public function getSuperVisors($UNIT_ID){			
			$sql_query = "SELECT * FROM `AdminLoginDetails` where `UnitID`='$UNIT_ID'";			
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['UserName'];
					}				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;				
				return $data;	
}

public function getMachinery($UNIT_ID){
			 $sql_query = "SELECT * FROM `AdminGradingMachines` Order by MachineName Asc";		
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['MachineName'];
					}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;
				
				return $data;		
}


public function getDrivers($UNIT_ID){
			 $sql_query = "SELECT * FROM `RmUsersList` where UserType='1' Order by UserName Asc";		
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['UserName'];
					}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;
				
				return $data;		
}



public function getGraders($UNIT_ID){
			$sql_query = "SELECT * FROM `RmUsersList` where UserType='2' order by UserName asc";		
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
						$RowData[] = $rlt;
					$PcNames[] = $rlt['UserName'];
					}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;
				
				return $data;		
}
public function getAgents($UNIT_ID){
			$sql_query = "SELECT * FROM `RmUsersList` where UserType='3' order by UserName asc";		
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
						$RowData[] = $rlt;
					$PcNames[] = $rlt['UserName'];
					}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;
				
				return $data;	
}


public function getBillNames($UNIT_ID){
			$sql_query = "SELECT * FROM `RmFinanciers` order by FinancierName asc";		
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
				
					$RowData[] = $rlt;
					$PcNames[] = $rlt['FinancierName'].' - '.$rlt['PanNumber'];
					}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;
				
				return $data;	
}

public function getPurchaseCategory($UNIT_ID){
			$sql_query = "SELECT * FROM `RmPurchaseCategory`";		
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['PurchaseType'];
				}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;
				
				return $data;	
}

public function getVehicles($UNIT_ID){
		
			
			$sql_query = "SELECT * FROM `RmVehicalDetails`  order by VehicalNumber asc";
			
			$sqlca_c_user = mysql_query($sql_query);
			$data;
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
				
					$RowData[] = $rlt;
					$PcNames[] = $rlt['VehicalNumber'];
				}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;
				
				return $data;	
}




public function getBankNames($UNIT_ID){
	
	    $sql_pu="SELECT * FROM `AdminBankNamesMaster` order  by BankName asc";				
		$sqlca_pu = mysql_query($sql_pu);	
		while($rlt = mysql_fetch_array($sqlca_pu,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['BankName'];
				}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;
						
   return $data;	
}


public function getUnits($UNIT_ID){
		
			
			 $sql_pu="SELECT * FROM `RmPurchaseStations` WHERE ProcessingStationID='$UNIT_ID'";
			 $sql_pu="SELECT * FROM `RmPurchaseStations` ";
				
		$sqlca_pu = mysql_query($sql_pu);
			
			
		while($rlt = mysql_fetch_array($sqlca_pu,MYSQL_ASSOC)){
					$RowData[] = $rlt;
					$PcNames[] = $rlt['StationName'];
				}
				
				$data['Data']=$RowData;
				$data['Names']=$PcNames;	
				return $data;	
}

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
			 
			 
	$TempTable='HrmDailyAttendnessTemp';
	
	$SqlPreviousDate="Select * from `HrmDailyAttendnessLog` order by Date DESC LIMIT 1  ";
	$SqlcaPreviousDate= mysql_query($SqlPreviousDate,$this->db);
	$RowPreviousDate = mysql_fetch_array($SqlcaPreviousDate,MYSQL_ASSOC);
	$LastUpdatedDate=$RowPreviousDate['Date'];
	
    $sql="delete from  $TempTable where Date < '$LastUpdatedDate'";
    $sqlca_post = mysql_query($sql,$this->db);
 
foreach($ATT_DATA as $value){
  $employee_id=$value[1];
  $date=$value[3];	
  $time_in=$value[3];	
  $time_out=$value[3];
  $status='0'; 
  $SqlTempArray[]="('$employee_id','$date','$time_in','0')";
}

 $SqlTempValues=implode(',',$SqlTempArray);

$SqlTemp="INSERT INTO $TempTable 
 (`EmpID`, `Date`, `TimeIn`, `Satus`) VALUES $SqlTempValues";
$sqlca_post = mysql_query($SqlTemp,$this->db);

if($sqlca_post){
	
	$SqlDeleteLog="delete from  `HrmDailyAttendnessLog` where Date ='$LastUpdatedDate'";
	$SqlcaDeleteLog = mysql_query($SqlDeleteLog,$this->db);
	
}

$sql="select EmpID,Date,min(ai.TimeIn) as TimeIn, max(ai.TimeIn) as TimeOut, count(*) as count
from $TempTable ai
group by EmpID,Date";

//having min(ai.time_in) < max(ai.time_in)
$sqlca_bplco = mysql_query($sql,$this->db);

while($row = mysql_fetch_array($sqlca_bplco,MYSQL_ASSOC)){
$employee_id=$row['EmpID'];
$date=$row['Date'];
$time_in=$row['TimeIn'];

if($row['count'] > 1){
	
$time_out=$row['TimeOut'];

}else{
	$time_out='00:00:00';
}

 
$TimeDiff = strtotime($time_out)-strtotime($time_in) ;

if( $TimeDiff > 3){
	
	$time_out=$time_out;
}else{
	
 $time_out='00:00:00';	
}


$status='0';
$SqlAryValues[]="('$employee_id','$date','$time_in','$time_out','0')";
}

$SqlAttValues=implode(',',$SqlAryValues);


$SqlAtt="INSERT INTO `HrmDailyAttendnessLog`(`BioMetricID`, `Date`, `TimeIn`, `TimeOut`,`Status`) VALUES 
$SqlAttValues";
$StmtAtt = mysql_query($SqlAtt,$this->db);

if($StmtAtt){
$SqlDeleteDup="DELETE t1 FROM HrmDailyAttendnessLog  t1 INNER JOIN HrmDailyAttendnessLog  t2 
WHERE t1.AttID < t2.AttID AND  
t1.Date = t2.Date and 
t1.BioMetricID=t2.BioMetricID and 
t1.TimeIn=t2.TimeIn and 
t2.TimeOut=t2.TimeOut";
$StmtDup = mysql_query($SqlDeleteDup,$this->db);
	$records = array('Error' => false, "Msg" => "Data Saved successfully");
	$this->response($this->json($records), 200);
}else{
	$records = array('Error' => "Unable To Submit Data", "Msg" => "Data saved successfully");
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

public function postAttData(){
	
		 $input_data = file_get_contents('php://input');
		 $jd_data = json_decode($input_data,true);
		 
		
	$STATUS_SUCCESS='SUCCESS';
	$STATUS_FAILURE='FAILURE';			
	
	$DATA = $jd_data['DATA'];
	 $DATA = json_decode($DATA,true);
	
	//print_r($jd_data);	
	
	foreach($DATA as $value){	
		
		$employee_id = $value['employee_id'];
		$date = $value['date'];
		$time_in = $value['time_in'];
		$time_out = $value['time_out'];
		$num_hr = $value['num_hr'];
		
		if($time_in==''){
			$time_in='00:00:00';
		}
		
		if($time_out==''){
			$time_out='00:00:00';
		}
		
		
		$sql="SELECT count(*) as total,status   from `attendance` where  `employee_id`='$employee_id' and `date`='$date' ";
		 $result= mysql_query($sql,$this->db);
		 $data=mysql_fetch_assoc($result);
		 $count=$data['total'];
		 $status=$data['status'];
			
				if($count > 0){
					//echo $rq="update";
					if($status==0 ){
						$sql="UPDATE `attendance` SET `time_in`='$time_in',`time_out`='$time_out' WHERE `employee_id`='$employee_id' and `date`='$date'";
						//$rq="update";
					}
				}else{
						$sql="INSERT INTO `attendance`(`employee_id`, `date`, `time_in`,`time_out`, `status`) VALUES ('$employee_id','$date','$time_in','$time_out','0')";
						//$rq= "insert";
				}
			
	$sqlca_post = mysql_query($sql,$this->db);
	
	//echo $sql;
	}
	
	if($sqlca_post){
		
	$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
	
	}else {
			echo mysql_errno($this->db);
	$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
	$this->response($this->json($records), 200);
	}		

}



public function getIndentDetails(){
		
		
		$IndentCode=$_GET['IndentCode'];
			
		$sql_query = "SELECT *,
(SELECT GROUP_CONCAT(RLD.LotNumber) from RmLotDetatils as RLD WHERE RLD.RmLotID in (RID.LotID)) as LotNumber


FROM `RmIndentDetails` as RID
left join IndentCategoryList as ICL on ICL.CategoryId=RID.Category
left join RmVAStations as RVS on RVS.StationID=RID.VAStationId
left join RmVehicalDetails as RVD on RVD.ID=RID.VehicalID where IndentCode='$IndentCode'";
			
			$sqlca_c_user = mysql_query($sql_query);
			
			$result = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC);
			$result['Error']=null;
			$result['Msg']="Success";
			
				
				
				
				$records = array('IndentDetails' => $result);
				$this->response($this->json($records), 200);

				
				}
				
				
public function postIndentDetails(){
		
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$Token=$this->getToken();
			$UserID=$Token['UserID'];
			

			$CategoryType = $data['CategoryType'];
			$IndentCode = $data['IndentCode'];
			$EmptyCrates = $data['EmptyCrates'];
			$NetCrates = $data['NetCrates'];
			$IceCrates = $data['IceCrates'];
			$Remarks = $data['Remarks'];
			
			$sqlIndentDetails = "SELECT * FROM `RmIndentDetails` where IndentCode='$IndentCode'";			
			$stmtIndentDetails = mysql_query($sqlIndentDetails);
			$rowIndentDetails = mysql_fetch_array($stmtIndentDetails,MYSQL_ASSOC);
			
			$Outwardby=$rowIndentDetails['Outwardby'];
			$Inwardby=$rowIndentDetails['Inwardby'];
			
			$AryCurrentDate=$this->getCurrentDateTime();
			
			$TimeStamp=$AryCurrentDate['TimeStamp'];
			
			if($CategoryType=='Inward'){

			
			if($Outwardby==''){
			$result['Error']="Outward Details Not Found";
			$result['Msg']="Outward Details Not Found";
			$this->response($this->json($result), 200);
			}else if($Inwardby!=''){
			$result['Error']="Inward Details Already Entered";
			$result['Msg']="Inward Details Already Entered";
			$this->response($this->json($result), 200);			
			}else {
			$sqlIndentUpdate = "update `RmIndentDetails`  set
			InwardDateTime='$TimeStamp',
			InwardIceCrates='$IceCrates',
			InwardEmptyCrates='$EmptyCrates',
			InwardNetCrates='$NetCrates',
			Inwardby='$UserID',
			Remarks1='$Remarks'			
			where IndentCode='$IndentCode'";			
			$stmtIndentUpdate = mysql_query($sqlIndentUpdate);
			
			if($stmtIndentUpdate){
			$result['Error']=null;
			$result['Msg']="Details Submitted Successful";
			$this->response($this->json($result), 200);
			}
			}
			}else if($CategoryType=='OutWard'){
			if($Outwardby!=''){
			$result['Error']="Outward Details Already Entered";
			$result['Msg']="Outward Details Already Entered";
			$this->response($this->json($result), 200);			
			}else{
			$sqlIndentUpdate = "update `RmIndentDetails`  set
			OutwardDateTime='$TimeStamp',
			OutwardIceCrates='$IceCrates',
			OutwardEmptyCrates='$EmptyCrates',
			OutwardNetCrates='$NetCrates',
			Outwardby='$UserID',
			Remarks2='$Remarks'			
			where IndentCode='$IndentCode'";			
			$stmtIndentUpdate = mysql_query($sqlIndentUpdate);
			
			if($stmtIndentUpdate){
			$result['Error']=null;
			$result['Msg']="Details Submitted Successful";
			$this->response($this->json($result), 200);
			}
			}				
				
			}
			
		
				
				}


				
				
public function truncate(){
		
		
		
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

public function updateDashBoard(){
		
		$sql_query_imd = "SELECT * FROM `inputmaterial_details` where STATUS='0'";
			
		$sqlca_imd = mysql_query($sql_query_imd);			
			
		while($rlt = mysql_fetch_array($sqlca_imd,MYSQL_ASSOC)){
		
		 $LOT_NO=$rlt['LOT_NO'];	
					
		 $sql_query = "SELECT * FROM `MATERIAL_PROCESSING_DATA` where LOT_NUMBER='$LOT_NO'";			
		 $sqlca_c_user = mysql_query($sql_query);		
		 $IS_COMPLETED=0;//false	
			
			while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					
			
					if($rlt['RE_ASSIGN_AVAL_WEIGHT']=='0'){
					
					if( $rlt['STATUS']=='6' || $rlt['STATUS']=='7' || $rlt['STATUS']=='8'){
					$IS_COMPLETED=1;	
					}else{
						$IS_COMPLETED=0;
						break;	
					}
					}else{
					$IS_COMPLETED=0;
					break;					
					}
					
					if($IS_COMPLETED==1){
					 $sql_imd_update="UPDATE `inputmaterial_details` SET `STATUS`='1' WHERE LOT_NO='$LOT_NO'";
					 $sqlca_imd_update = mysql_query($sql_imd_update,$this->db);
					}
}
		
}

echo "Done";
							
}				

				
				
public function postEditLot(){
	
			$Error=null;
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);			
		
			$LotNumber = $data['LotNumber'];
			$TicketID = $data['TicketID'];
			$GraderID = $data['GraderID'];
			$AgentID = $data['AgentID'];
			$PurchaseCount=$data['PurchaseCount'];
			$DriverID=$data['DriverID'];
			$VehicalID=$data['VehicalID'];
			$PurchaseCategoryID=$data['PurchaseCategoryID'];
			$PurchaseStationID=$data['PurchaseStationID'];
			$PurchaseWeight=$data['PurchaseWeight'];
			$FarmAddress=$data['FarmAddress'];
			$PurchaseDate=$data['PurchaseDate'];
			$PostedUserID=$data['PostedUserID'];
			$LotID=$data['LotID'];
			$PostedDate=date('Y-m-d H:i:s');
			//IP ADDRESS
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			
			/*********************************/
			
			$LotNumber=$this->getFormatedLotNumber($PurchaseStationID,$LotNumber);

			$LotAval=$this->checkLotNoAval($PurchaseDate,$LotNumber,$LotID);
			
				if(!$LotAval){
				$Error="Lot Already Exist";					
				}else if($PostedUserID==''){
				$Error="Session Expired,Please Login and Try Again";
				}else{
			
			 $SqlLotDetails="SELECT * FROM `RmLotDetatils` WHERE RmLotID='$LotID'";						
			$StmtLotDetails = mysql_query($SqlLotDetails);			
			$RowLotDetails = mysql_fetch_array($StmtLotDetails,MYSQL_ASSOC);
			
			$LotJsonData=json_encode($RowLotDetails);
			
			
			$SqlEditData="INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, `IPAddress`) VALUES ('$TicketID','$LotJsonData','$PostedDate','$PostedUserID','$ipaddress')";
			
			 $SqlPostData = mysql_query($SqlEditData,$this->db);
			

			if($SqlPostData){
			
			
		 $SqlUpdate="UPDATE `RmLotDetatils` SET 
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
							
			$StmtUpdate = mysql_query($SqlUpdate,$this->db);
			}
			if($StmtUpdate){
				$this->UpdateTicketStatus($TicketID,1);
				$Error=null;
				$Msg="Lot Updated";
			}else if(!$SqlPostData){
				$Error="Ticket Already Closed";
			}else {
				$Error="Unable to Update Lot Please Contact Admin";
			}
			}
			
			$Response = array('Error' => $Error, "Msg" => $Msg);
		

			$this->response($this->json($Response), 200);				
			
			
		 										
}
				


public function postEditBooking(){
	
			$Error=null;
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);			
		
			$BookingNumber = $data['BookingNumber'];
			$GraderID = $data['GraderID'];
			$AgentID = $data['AgentID'];
			$BookingCount=$data['BookingCount'];
			$DriverID=$data['DriverID'];
			$VehicalID=$data['VehicalID'];
			$PurchaseCategoryID=$data['PurchaseCategoryID'];
			$BookingStationID=$data['BookingStationID'];
			$BookingWeight=$data['BookingWeight'];
			$FarmAddress=$data['FarmAddress'];
			$BookingDate=$data['BookingDate'];
			$PostedUserID=$data['PostedUserID'];
			$LotID=$data['LotID'];
			$PostedDate=date('Y-m-d H:i:s');
			
			$BookingNumber=$this->getFormatedBookingNumber($BookingStationID,$BookingNumber);

			$LotAval=$this->checkLotNoAval($BookingDate,$BookingNumber,$LotID);
			
				if(!$LotAval){
				$Error="Lot Already Exist";					
				}else if($PostedUserID==''){
				$Error="Session Expired,Please Login and Try Again";
				}else{
			
			 $SqlLotDetails="SELECT * FROM `RmBookingDetatils` WHERE RmBookingID='$LotID'";						
			$StmtLotDetails = mysql_query($SqlLotDetails);			
			$RowLotDetails = mysql_fetch_array($StmtLotDetails,MYSQL_ASSOC);
			
			$LotJsonData=json_encode($RowLotDetails);
			
		 $SqlUpdate="UPDATE `RmBookingDetatils` SET 
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
							
			$StmtUpdate = mysql_query($SqlUpdate,$this->db);
			
			if($StmtUpdate){
				
				$Msg="Booking Updated";
			}else {
				$Error="Unable to Update Booking Please Contact Admin";
			}
			}
			
			$Response = array('Error' => $Error, "Msg" => $Msg);
			$this->response($this->json($Response), 200);				
	 										
}
			
			
			
				
public function postEditBill(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);			
		
			$TicketID = $data['TicketID'];
			$FinancierID=$data['FinancierID'];
			$Deductions=$data['Deductions'];
			$Remarks=$data['Remarks'];
			$PostedUserID=$data['PostedUserID'];
			$LotID=$data['LotID'];
			$PostedDate=date('Y-m-d H:i:s');
			//IP ADDRESS
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			
			/*********************************/
			
			 $SqlBillDetails="SELECT * FROM `RmBillDetails` WHERE LotID='$LotID'";						
			$StmtBillDetails = mysql_query($SqlBillDetails);			
			$RowBillDetails = mysql_fetch_array($StmtBillDetails,MYSQL_ASSOC);
			
			$BillJsonData=json_encode($RowBillDetails);
			
			
			$SqlEditData="INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, `IPAddress`) VALUES ('$TicketID','$BillJsonData','$PostedDate','$PostedUserID','$ipaddress')";
			
			 $SqlPostData = mysql_query($SqlEditData,$this->db);
			

			if($SqlPostData){
			
			
		   $SqlUpdate="UPDATE `RmBillDetails` SET 
							`FinancierID`='$FinancierID',
							 `Deductions`='$Deductions',
							 `Remarks`='$Remarks'
							  WHERE `LotID`='$LotID'";
							
			$StmtUpdate = mysql_query($SqlUpdate,$this->db);



			if($StmtUpdate){
				$this->UpdateTicketStatus($TicketID,1);
				$Response = array('Error' => null, "Msg" => "Lot Updated");
			}else{
				$Response = array('Error' => "Unable to Update Lot Please Contact Admin", "Msg" => "Lot Updated");
			}

			$this->response($this->json($Response), 200);
			$this->UpdateTicketStatus($TicketID,1);
							
							
							
			
			}
			
			else{
			$records = array('Error'=> "Ticket Already Closed");
			$this->response($this->json($records), 200);
		}
		 										
}
								
				
public function UpdateTicketStatus($TicketID,$Status){
	
			$AryCurrentDate=$this->getCurrentDateTime();
			$FixedDate=$AryCurrentDate['TimeStamp'];		
					
		
		$SqlUpdate="UPDATE `AdminTickets` SET 
							`TicketStatus`='$Status',
							`FixedDate`='$FixedDate'
							  WHERE `TicketID`='$TicketID'";
							
		$StmtUpdate = mysql_query($SqlUpdate,$this->db);
	
}


public function postDeleteGrading(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);			
		
			$TicketID = $data['TicketID'];
			$LotID = $data['LotID'];
			$PostedUserID = $data['PostedUserID'];
			$AryCurrentDate= $this->getCurrentDateTime();
			$PostedDate=$AryCurrentDate['TimeStamp'];
			
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			
			/*********************************/
			
			 $DataQuery="SELECT * FROM `PpcHLGradingDetails`
						left join RmBillDetails on PpcHLGradingDetails.LotID=RmBillDetails.LotID 
						WHERE  PpcHLGradingDetails.LotID='$LotID'";						
			$DataExec = mysql_query($DataQuery);			
			$RowData = mysql_fetch_array($DataExec,MYSQL_ASSOC);
			
			$LotJsonData=json_encode($RowData);
			
			
			$SqlEditData="INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, 
			`IPAddress`) VALUES ('$TicketID','$LotJsonData','$PostedDate','$PostedUserID','$ipaddress')";
			
			 $SqlPostData = mysql_query($SqlEditData,$this->db);
			

			if($SqlPostData){
			
			
			$DeleteHlQuery="delete from PpcHLGradingDetails where LotID='$LotID'";							
			$DeleteHlBrkQuery="delete from PpcHLGradingBreakup where LotID='$LotID'";							
			$DeleteHlDefQuery="delete from PpcHLGradingDefects where LotID='$LotID'";							
			$DeleteBillQuery="delete from RmBillDetails where LotID='$LotID'";							
			$DeleteBillBrkQuery="delete from RmBillBreakup where LotID='$LotID'";							
			
			$DeleteExec = mysql_query($DeleteHlQuery,$this->db);
			$DeleteExec = mysql_query($DeleteHlBrkQuery,$this->db);
			$DeleteExec = mysql_query($DeleteHlDefQuery,$this->db);
			$DeleteExec = mysql_query($DeleteBillQuery,$this->db);
			$DeleteExec = mysql_query($DeleteBillBrkQuery,$this->db);


			$this->UpdateTicketStatus($TicketID,1);

			$records = array('Error' => null, "Msg" => "Grading Deleted");
			$this->response($this->json($records), 200);

			}
			
			else{
			$records = array('Error'=> "Ticket Already Closed");
			$this->response($this->json($records), 200);
		}
												
}



public function postDeleteBillOnly(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);			
		
			$TicketID = $data['TicketID'];
			$LotID = $data['LotID'];
			$PostedUserID = $data['PostedUserID'];
			$AryCurrentDate= $this->getCurrentDateTime();
			$PostedDate=$AryCurrentDate['TimeStamp'];
			
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			
			/*********************************/
			
		$DataQuery="SELECT * FROM `PpcHLGradingDetails 
		left join RmBillDetails on PpcHLGradingDetails.LotID=RmBillDetails.LotID 
						WHERE  PpcHLGradingDetails.LotID='$LotID'";						
			$DataExec = mysql_query($DataQuery);			
			$RowData = mysql_fetch_array($DataExec,MYSQL_ASSOC);
			
			$LotJsonData=json_encode($RowData);
			
			
			$SqlEditData="INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, 
			`IPAddress`) VALUES ('$TicketID','$LotJsonData','$PostedDate','$PostedUserID','$ipaddress')";
			
			 $SqlPostData = mysql_query($SqlEditData,$this->db);
			

			if($SqlPostData){				

			$DeleteBillQuery="delete from RmBillDetails where LotID='$LotID'";							
			$DeleteBillBrkQuery="delete from RmBillBreakup where LotID='$LotID'";							

			$DeleteExec = mysql_query($DeleteBillQuery,$this->db);
			$DeleteExec = mysql_query($DeleteBillBrkQuery,$this->db);


			$this->UpdateTicketStatus($TicketID,1);

			$records = array('Error' => null, "Msg" => "Only Bill is Deleted");
			$this->response($this->json($records), 200);

			}
			
			else{
			$records = array('Error'=> "Ticket Already Closed");
			$this->response($this->json($records), 200);
		}
												
}

public function postHonDeleteGrading(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);			
		
			$TicketID = $data['TicketID'];
			$LotID = $data['LotID'];
			$PostedUserID = $data['PostedUserID'];
			$AryCurrentDate= $this->getCurrentDateTime();
			$PostedDate=$AryCurrentDate['TimeStamp'];
			
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			
			/*********************************/
			
			 $DataQuery="SELECT * FROM `PpcHonGradingDetails`
						left join RmBillDetails on PpcHonGradingDetails.LotID=RmBillDetails.LotID 
						WHERE  PpcHonGradingDetails.LotID='$LotID'";						
			$DataExec = mysql_query($DataQuery);			
			$RowData = mysql_fetch_array($DataExec,MYSQL_ASSOC);
			
			$LotJsonData=json_encode($RowData);
			
			
			$SqlEditData="INSERT INTO `AdminEditLogData`(`TicketID`, `Data`, `PostedDate`, `PostedUserID`, 
			`IPAddress`) VALUES ('$TicketID','$LotJsonData','$PostedDate','$PostedUserID','$ipaddress')";
			
			 $SqlPostData = mysql_query($SqlEditData,$this->db);
			

			if($SqlPostData){
			
			
			$DeleteHlQuery="delete from PpcHonGradingDetails where LotID='$LotID'";							
			$DeleteHlBrkQuery="delete from PpcHonGradingBreakup where LotID='$LotID'";							
			$DeleteBillQuery="delete from RmBillDetails where LotID='$LotID'";							
			$DeleteBillBrkQuery="delete from RmBillBreakup where LotID='$LotID'";							
			
			$DeleteExec = mysql_query($DeleteHlQuery,$this->db);
			$DeleteExec = mysql_query($DeleteHlBrkQuery,$this->db);
			$DeleteExec = mysql_query($DeleteBillQuery,$this->db);
			$DeleteExec = mysql_query($DeleteBillBrkQuery,$this->db);


			$this->UpdateTicketStatus($TicketID,1);

			$records = array('Error' => null, "Msg" => "Hon Grading Deleted");
			$this->response($this->json($records), 200);

			}
			
			else{
			$records = array('Error'=> "Ticket Already Closed");
			$this->response($this->json($records), 200);
		}
												
}



public function deleteProcessRequest(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			
			$WEIGHT_IN_KGS = $data['WEIGHT_IN_KGS'];
			$REFERENCE_ID = $data['REFERENCE_ID'];
			$LOT_NUMBER=$data['LOT_NUMBER'];
			$AVAL_WEIGHT_TO_PROCESS=$data['AVAL_WEIGHT_TO_PROCESS'];
			$WEIGHT_UNDER_PROCESSING=$data['WEIGHT_UNDER_PROCESSING'];
			
			$SQL_SUB_M_P_D="SELECT * FROM MATERIAL_PROCESSING_DATA WHERE REFERENCE_ID='$REFERENCE_ID'";						
			$STMT_SUB_M_P_D = mysql_query($SQL_SUB_M_P_D);			
			$SUB_M_P_D_ROW = mysql_fetch_array($STMT_SUB_M_P_D,MYSQL_ASSOC);
			
			
			$REFERENCE_ID_OLD=$SUB_M_P_D_ROW['SUB_REF_ID'];
			

			if($REFERENCE_ID_OLD=='' || $REFERENCE_ID_OLD=='NULL'){
			
			
		$sql_update="UPDATE `inputmaterial_details` SET 
							`AVAL_WEIGHT_TO_PROCESS`='$WEIGHT_IN_KGS',
							`WEIGHT_UNDER_PROCESSING`='$WEIGHT_UNDER_PROCESSING'
							WHERE `LOT_NO`='$LOT_NUMBER'";
			
			}else{
			
			$SQL_SUB_M_P_D="SELECT * FROM MATERIAL_PROCESSING_DATA WHERE REFERENCE_ID='$REFERENCE_ID_OLD'";						
			$STMT_SUB_M_P_D = mysql_query($SQL_SUB_M_P_D);			
			$SUB_M_P_D_ROW = mysql_fetch_array($STMT_SUB_M_P_D,MYSQL_ASSOC);
				
				
				$RE_ASSIGN_AVAL_WEIGHT=$SUB_M_P_D_ROW['RE_ASSIGN_AVAL_WEIGHT'];
				
				$AVAL_WEIGHT_TO_PROCESS=$RE_ASSIGN_AVAL_WEIGHT+$WEIGHT_IN_KGS;
			
			 $sql_update="UPDATE `MATERIAL_PROCESSING_DATA` SET `RE_ASSIGN_AVAL_WEIGHT`=$AVAL_WEIGHT_TO_PROCESS
					WHERE `REFERENCE_ID`='$REFERENCE_ID_OLD'";	
					
					
			
			}
			
			 
			$sqlca_update = mysql_query($sql_update,$this->db);
							
		if($sqlca_update){					
				$sql_update="DELETE FROM `MATERIAL_PROCESSING_DATA` WHERE
					 `REFERENCE_ID`='$REFERENCE_ID'";
					$sqlca_update = mysql_query($sql_update,$this->db);	
					
					$records = array('status' => $STATUS_SUCCESS, "msg" => "Deleted Successful");
				$this->response($this->json($records), 200);
						
		}else{
			$records = array('status' => $STATUS_FAILURE, "msg" => "Unable to Delete");
			$this->response($this->json($records), 200);
		}												
}







private function pushFcm($UNIT_ID ,$data){
	
	
	
		$key="AAAAtyt5poE:APA91bFgmx8I9ZOiXwT-y-WkaUizpbKEhGgOCDEATzkrf4p8OUWx0WXFGRe35zzJXpk7_l0wXD2IwbXWZ0AwrFkj9KFznud-RkDY1M86LsCMU4nMLlhx7C9Y59KwpjpsS6Wf6H5V2oiR";
//test kry tcf	
//	$key="AAAArECVdjA:APA91bFbj_JGXKU7aFC2FPqNaknFIGM2Vk1w4ibtmuTc3DrAB9ZlVnxSmFmHEgDDA0GHNm2_KJnIBnTtPCvPBe50pa3J9nqQBpcmiBRtz7SEWMv9VWPX-bx7Cduhj2ZgnQ31vtXA9wk_ST1rJunYFg3A0HZ_oe1Sng";

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
	
	
	$UserID=$data['USER_ID'];
	
	
	 $sqlca="SELECT * FROM `AdminLoginDetails` where UserID='$UserID'";
	 $sqlca_c_user = mysql_query($sqlca);
	 $rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC);
	$USER_NAME= $rlt['UserName'];
	
	$title=$data['FUNCTION'] ;
	//$title="God is calling you" ;
	$body=$USER_NAME.' - '.$data['DATE'];
	//$body="Truly my soul silently waits for God; From Him comes my salvation.  Psalm 62:1";
	//$Url="https://www.youtube.com/watch?v=ekrlrFSOB0s";
	 
	 $sqlca="SELECT * FROM `AdminLoginDetails` where UserLoginID='9912275234'";
	 $sqlca_c_user = mysql_query($sqlca);	 
				
		
		
		$registrationIDs=array();	
			
			
		while($rlt = mysql_fetch_array($sqlca_c_user,MYSQL_ASSOC)){
					$result[] = $rlt;
					array_push($registrationIDs,$rlt['DeviceID']);
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



	

		




// 'to' => $singleID ;  // expecting a single ID
 //'registration_ids' => $registrationIDs ;  // expects an array of ids
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
	'Authorization: key=' .$key,
	'Content-Type: application/json'
);
 
$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
$result = curl_exec($ch );
curl_close( $ch );
//echo $result . "\n\n";

						
		/*	$error = array('status' => "Failed", "msg" => "Invalid Request Paramaters");
			$this->response($this->json($error), 200);
			
			*/

}




public function getPoList(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			
			
			$SQL_INVOICE_DATA="SELECT * FROM `INVOICE_DATA` where INS_STATUS=0  order by ID DESC limit 100";						
			$STMT_SQL_INVOICE_DATA = mysql_query($SQL_INVOICE_DATA);
			

				while($rlt = mysql_fetch_array($STMT_SQL_INVOICE_DATA,MYSQL_ASSOC)){
						
						$rlt['SAMPLING_LIST'] = $this->getSamplingPlan($rlt['ID']);
						//$rlt['TOTAL_NO_MCS'] = $rlt['SAMPLING_LIST']['TOTAL_MCS'];
						$result[] = $rlt;
						
						
				}		

//echo count($result);				
			if(count($result)!=0){					
				$records = array('status' => $STATUS_SUCCESS, "PO_LIST" => $result);
				$this->response($this->json($records), 200);
						
		}else{
			$records = array('status' => $STATUS_FAILURE, "msg" => "No Records Found");
			$this->response($this->json($records), 200);
		}												
}


public function getSamplingPlan($ID){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';		
			
			//$ID=$_GET['INV_ID'];
			
			$TOTAL_MCS=0;
			
			$sql_code_list="SELECT sum(T_O_MCS) as O_MCS FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID'";						
			$stmt_code = mysql_query($sql_code_list);
			$rlt = mysql_fetch_array($stmt_code,MYSQL_ASSOC);
			$O_MCS=$rlt['O_MCS'];
			
			 $TOTAL_SAMPLES =(sqrt($O_MCS)+1)/2;
			$TOTAL_SAMPLES =round($TOTAL_SAMPLES);
			
			$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `CODE_LIST_ID` ASC";						
			$stmt_code = mysql_query($sql_code_list);
			
			$sample=1;

				while($rlt = mysql_fetch_array($stmt_code,MYSQL_ASSOC)){
						
						$TOTAL_MCS= $TOTAL_MCS+$rlt['T_O_MCS'];
						$ARY_GRADES = explode(',', $rlt['GRADES']);
						$ARY_MCS = explode(',', $rlt['MCS']);
						
						$count=0;
						foreach($ARY_GRADES as $VALUE){
						
						$MCS=$ARY_MCS[$count];
						
						
						$EACH_SAMPLE=($MCS/$O_MCS)*$TOTAL_SAMPLES;
						$EACH_SAMPLE =round($EACH_SAMPLE);
						
						if($EACH_SAMPLE > 0){
						
						$rlt['GRADES']=$VALUE;	
						$rlt['MCS']=$MCS;
						$rlt['EACH_SAMPLE']=$EACH_SAMPLE;	
						$rlt['TOTAL_SAMPLES']=$TOTAL_SAMPLES;	
						$rlt['SERIES']=$sample.'-'. ($sample+$EACH_SAMPLE-1);	
						$sample=$sample+$EACH_SAMPLE;
						$result[] = $rlt;
						}
						
						$count++;
						}
						
				}
				
	return $result;

	
}


public function PostInspectionData(){
			
			$json = file_get_contents('php://input');
			//$data = json_decode($json,true);
			 $ary_data = json_decode($json,true);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			//$DATA = $data['DATA'];
			$ary_data=json_decode($ary_data,true);
			
		//	print_r($ary_data);
			foreach($ary_data as $item) { 
			
				
					  $BIG_PIECES_UN=$item['BigPieces'];
					  $SMALL_PIECES_UN=$item['SmallPieces'];
					  $UNIFORMITY=round($BIG_PIECES_UN/$SMALL_PIECES_UN,1);  //c
					  $GLAZE=round((($FROZEN_WEIGHT- $DEGLAZED_WEIGHT)/$FROZEN_WEIGHT)*100,1);  //c
					  $NO_OF_SAMPLES=$item['Sample_id'];
					  $COUNT=0;
					  $USE_BY_DATE=0;
					  $DECLARED_NET_WT=0;
					  $TOTAL_DEFECT_G=0;
					  $TOTAL_DEFECT_P=0;
					  $OTHER_DEFECTS=0;
					  
					  $APPEARANCE='GOOD';  //c
					  $TEXTURE_COLOUR='';  //c
					  $ODOR_FLAVOUR='';  //c
					  $FOREIGN_MATTER='NIL';  //c
					  $HEAVY_FILTH='NIL';  //c

			  $BIG_PIECES=$item['BigPieces'];
			  $BLACK_SPOT_MEAT=$item['BlackSpotMeat'];
			  $BLACK_TAIL=$item['BlackTail'];
			  $BROKEN_DAMAGE=$item['BrokenOrDamage'];
			  $CODE_LIST_ID=$item['CODE_LIST_ID'];  //c //
			  $DAY_CODE=$item['DAY_CODE'];  //
			  $DECOMPOSITION=$item['Decomposition'];  //c
			  $DEGLAZED_WEIGHT=$item['Deglazedwt']; //Deglazedwt
			  $DEHYDRATION=$item['Dehydration'];  //c
			  $DISCOLORATION=$item['Discoloration'];  //c
			  $FROZEN_WEIGHT=$item['FrozenWt'];
			  $GRADE=$item['GRADE']; //
			  $HANGING_MEAT=$item['HangingMeat']; //
			  $INV_ID=$item['INVOICE_ID']; //
			  $IMPROPER_DEVINED=$item['ImproperDeveined']; //
			  $IMPROPER_PEELED=$item['ImproperPeeled']; //
			  $LongThroatMeat=$item['LongThroatMeat']; //
			  $SampleProfile=$item['SampleProfile']; //
			  $SampleProfile2=$item['SampleProfile2']; //
			  $SMALL_PIECES=$item['SmallPieces'];
			  $SOFT_SHELL=$item['SoftShell'];
			  $TRACEABILITY=$item['TRACABILITY']; //
			  $TAIL_OFF=$item['TailFanOff']; //
			  $TOTAL_PIECES=$item['TotalPieces'];
			  $VARITY_ID=$item['VARITY_ID'];

			  $sql_count="select count(*) as TOTAL from INSP_DATA where INV_ID='$INV_ID' and CODE_LIST_ID='$CODE_LIST_ID' and NO_OF_SAMPLES='$NO_OF_SAMPLES'";
					$sqlca_post = mysql_query($sql_count,$this->db);					
					$rlt = mysql_fetch_array($sqlca_post,MYSQL_ASSOC);
					$TOTAL=$rlt['TOTAL'];
					
					if($TOTAL==0 && $FROZEN_WEIGHT!=''){
					
					$ss_post="INSERT INTO `INSP_DATA`(
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
                     
			
				$sqlca_post = mysql_query($ss_post,$this->db);
				
				 $SAMPLE_ID = mysql_insert_id();
				
					  $FROZEN_W=$item['FrozenWt_img'];  //c //
					  $DEGLAZED_W=$item['Deglazedwt_img'];  //c //
					  $TOTAL_PIECES=$item['TotalPieces_img'];  //c //
					  $SAMPLE_1=$item['SampleProfile_img'];  //c //
					  $SAMPLE_2=$item['SampleProfile2_img'];  //c // 
				
				  $sql_images="INSERT INTO `PRODUCT_INSP_IMAGESS`(`INV_ID`, `SAMPLE_ID`, `FROZEN_W`, `DEGLAZED_W`, `TOTAL_PIECES`,
				`SAMPLE_1`, `SAMPLE_2`) VALUES ('$INV_ID','$SAMPLE_ID','$FROZEN_W','$DEGLAZED_W','$TOTAL_PIECES',
				'$SAMPLE_1','$SAMPLE_2')";				
					
				$ss_sql_images = mysql_query($sql_images,$this->db);
				
					}else{
						
					}
			}
				
				
					
				
			
					 //echo mysql_errno($this->db);
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Data Updated");
				$this->response($this->json($records), 200);
				
			//	*/
				}

				
				public function PostImageData(){
			
			$json = file_get_contents('php://input');
			//$ary_data = json_decode($json,true);
			print_r($_POST);
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
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
				
					
				
			
				//	 echo mysql_errno($this->db);
				$records = array('status' => $STATUS_SUCCESS, "msg" => "Data Updated");
				$this->response($this->json($records), 200);
				
			//	*/
			
				}
				
	

public function getBLOCKData(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			
			
			$SQL_INVOICE_DATA="SELECT * FROM `CS_BLOCK_DATA_TABLE` order by R_POSITION asc";						
			$STMT_SQL_INVOICE_DATA = mysql_query($SQL_INVOICE_DATA);

				while($rlt = mysql_fetch_array($STMT_SQL_INVOICE_DATA,MYSQL_ASSOC)){
						
				
						$result[] = $rlt;
				}		
		
			if(count($result)!=0){					
				$records = array('status' => $STATUS_SUCCESS, "BLOCK_DATA" => $result);
				$this->response($this->json($records), 200);
						
		}else{
			$records = array('status' => $STATUS_FAILURE, "msg" => "No Records Found");
			$this->response($this->json($records), 200);
		}												
}	
				
				
public function getFloorData(){
			
			$json = file_get_contents('php://input');
			$BLOCK_ID=intval($_GET['BLOCK_ID']);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';			
			
			
			
			$SQL_INVOICE_DATA="SELECT * FROM `CS_FLOOR_DATA` where BLOCK_ID='1'";						
			
			$STMT_SQL_INVOICE_DATA = mysql_query($SQL_INVOICE_DATA);

				while($rlt = mysql_fetch_array($STMT_SQL_INVOICE_DATA,MYSQL_ASSOC)){
						
				
						$result[] = $rlt;
				}		

//echo count($result);				
			if(count($result)!=0){					
				$records = array('status' => $STATUS_SUCCESS, "FloorData" => $result);
				$this->response($this->json($records), 200);
						
		}else{
			$records = array('status' => $STATUS_FAILURE, "msg" => "No Records Found");
			$this->response($this->json($records), 200);
		}												
}


public function getRackData(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			
			$STATUS_SUCCESS='SUCCESS';
			$STATUS_FAILURE='FAILURE';	
			$FLOOR_ID=$data['FLOOR_ID'];			
			$BLOCK_ID=$data['BLOCK_ID'];			
			
		//	ORDER BY `POSITION` DESC
			
			$SQL_CS_RACK_DATA="SELECT * FROM `CS_RACK_DATA` where BLOCK_ID ='$BLOCK_ID' and FLOOR_ID='$FLOOR_ID' ";						
			$STMT_CS_RACK_DATA = mysql_query($SQL_CS_RACK_DATA);

			while($rlt = mysql_fetch_array($STMT_CS_RACK_DATA,MYSQL_ASSOC)){
				$RackData[] = $rlt;
			}


			$SQL_CS_PRESENT_STOCK="SELECT RACK FROM `CS_PRESENT_STOCK` where BLOCK_ID='$BLOCK_ID' and  VARIETY !=''";						
			$STMT_CS_PRESENT_STOCK = mysql_query($SQL_CS_PRESENT_STOCK);

			while($rlt = mysql_fetch_array($STMT_CS_PRESENT_STOCK,MYSQL_ASSOC)){
				$Stock_Data[] = $rlt['RACK'];
			}				

//echo count($result);				
			if(count($RackData)!=0){					
				$records = array('status' => $STATUS_SUCCESS, "RackData" => $RackData,"STOCK_DATA" => $Stock_Data);
				$this->response($this->json($records), 200);
						
		}else{
			$records = array('status' => $STATUS_FAILURE, "msg" => "No Records Found");
			$this->response($this->json($records), 200);
		}												
}
//soaking report - 09-02-2020  11/09/2021  soakingReport(OrgnName) disabled 
public function soakingReportt(){
	
	 $json = file_get_contents('php://input');
	 $data = json_decode($json,true);
	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$dt=$dateTime->format("Y/m/d  h:i A"); 
	
	 $JSON_DATA=$data;
	
	$STATUS_SUCCESS='SUCCESS';
	$STATUS_FAILURE='FAILURE';			
	
	 $USER_ID = $data['USER_ID'];
	
	$LOT_ID = $data['LOT_ID'];
	$DATA = $data['DATA'];
	 $DATA = json_decode($DATA,true);
	
// 	print_r($DATA);die();	
	
	foreach($DATA as $value){	
		
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
// 		$createdon = $value['CreatedDateTime'];
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$dt=$dateTime->format("d/m/Y  h:i A"); 
		
		$AryCurrentDate= $this->getCurrentDateTime();
	    $PostedDate=$AryCurrentDate['TimeStamp'];
				
		$sql = "insert into soaking_report(`user_id`,`LOT_ID`,`TIME1`, `T_NO`, `L_NO`, `Variety`, `QTY`, `C1`, `Salt_kg`, `CHEM`, `SUP1_SIGN`, `TIME2`, `C2`, `APP_QT`, `SUP2_SIGN`, `DEST`, `Remarks`, `TimePeriod`, `Source`, `CreatedDateTime`) 
			values('$USER_ID', '$LOT_ID','$time1', '$tno', '$lno', '$variety', $qty, $c1, '$salt_kg', '$chem', '$sup1_sign', '$time2', '$c2', '$APP_QT', '$sup2_sign', '$dest', '$remarks', '$timeperiod', '$source', '$PostedDate')";
// 			echo $sql;die();	
	$sqlca_post = mysql_query($sql,$this->db);
	}
	if($sqlca_post){
		
	$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
	
	}else {
			echo mysql_errno($this->db);
	$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
	$this->response($this->json($records), 200);
	}		

}



public function postSoakingReport(){
	
	 $json = file_get_contents('php://input');
	 $data = json_decode($json,true);
	
	 $JSON_DATA=$data;
	 
	 $PostedUserID = $data['PostedUserID'];
	
	$LotID = $data['LotID'];
	$SoakingData = $data['Data'];
	$SoakingData = json_decode($SoakingData,true);
	
// 	print_r($DATA);die();	
	
	foreach($SoakingData as $value){	
	 
	    
		
		$SoakingDate = date('Y-m-d',strtotime($value['SoakingDate']));
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
// 		$createdon = $value['CreatedDateTime'];
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$dt=$dateTime->format("d/m/Y  h:i A"); 
		
		$AryCurrentDate= $this->getCurrentDateTime();
	    $PostedDate=$AryCurrentDate['TimeStamp'];
				
		 	$sql = "INSERT INTO `ProdSoakingDetails`( `SoakingDate`, `LotID`, `Source`, `Variety`, `TubNumber`, `SaltQty`, `ChemicalQty`, `SoakInTime`, 
`SoakInCount`, `SoakInQty`,`SoakOutTime`, `SoakOutCount`, `SoakOutQty`, `Destination`, `PostedUserID`, `Remarks`) 
VALUES ('$SoakingDate','$LotID','$Source','$Variety','$TubNumber','$SaltQty','$ChemicalQty','$SoakInTime',
'$SoakInCount','$SoakInQty','$SoakOutTime','$SoakOutCount','$SoakOutQty','$Destination','$PostedUserID','$Remarks')
";
		//echo $sql;die();	



	$sqlca_post = mysql_query($sql,$this->db);
	}
	if($sqlca_post){
		
	$records = array('Error' => null, "Msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
	
	}else {
			 mysql_errno($this->db);
	$records = array('Error' => "Unable To Post Data", "Msg" => "Oops! Something went wrong");
	$this->response($this->json($records), 200);
	}		

}


//soaking report - 01-03-2020  // IQF INFEED #TEJA
public function iqfReport(){
	
	 $json = file_get_contents('php://input');
	 $data = json_decode($json,true);
	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$dt=$dateTime->format("d/m/y  h:i A"); 
	
	 $JSON_DATA=$data;
	
	$STATUS_SUCCESS='SUCCESS';
	$STATUS_FAILURE='FAILURE';			
	
	 $USER_ID = $data['USER_ID'];
	
	$LOT_ID = $data['LOT_ID'];
	$DATA = $data['DATA'];
	 $DATA = json_decode($DATA,true);
	
// 	print_r($DATA);die();	
	
	foreach($DATA as $value){	
		
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
// 		$createdon = $value['CreatedDateTime'];
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$DATE=$dateTime->format("d/m/Y  h:i A"); 
				
		 $sql = "INSERT INTO `PP_IQF_IN_FEED`( `LOT_ID`, `USER_ID`, `TIME`, `PRODUCT`, `GRADE`,
		`PACKING`, `BS_RATIO`, `VEIN`, `SOFT`, `BROKEN`, `SMELL`, `COL`, `SPOTS`, 
		`SUPERVISOR`, `REMARKS`, `LINE`, `DATE`) VALUES ('$LOT_ID','$USER_ID','$TIME','$PRODUCT','$GRADE',
		'$PACKING','$BS_RATIO','$VEIN','$SOFT','$BROKEN','$SMELL','$COL','$SPOTS',
		'$SUPERVISOR','$REMARKS','$LINE','$DATE')";	
			
			// 			echo $sql;die();	
	$sqlca_post = mysql_query($sql,$this->db);
	}
	if($sqlca_post){
		
	$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
	
	}else {
			echo mysql_errno($this->db);
	$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
	$this->response($this->json($records), 200);
	}		

}




//soaking report - 06-03-2020  // PACKING REPORT #TEJA
public function PackingReport(){
	
	 $json = file_get_contents('php://input');
	 $data = json_decode($json,true);
	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$dt=$dateTime->format("d/m/y  h:i A"); 
	
	 $JSON_DATA=$data;
	
	$STATUS_SUCCESS='SUCCESS';
	$STATUS_FAILURE='FAILURE';			
	
	 $USER_ID = $data['USER_ID'];
	
	$LOT_ID = $data['LOT_ID'];
	$DATA = $data['DATA'];
	 $DATA = json_decode($DATA,true);
	
// 	print_r($DATA);die();	
	
	foreach($DATA as $value){	
		
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
// 		$createdon = $value['CreatedDateTime'];
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$DATE=$dateTime->format("d/m/Y  h:i A"); 
				
		 $sql = "INSERT INTO `PP_PACKING`( `LOT_ID`, `USER_ID`, `TIME`, `PRODUCT`, `GRADE`,
		`PACKING`, `BS_RATIO`, `VEIN`, `SOFT`, `BROKEN`, `SMELL`, `COL`, `SPOTS`, 
		`SUPERVISOR`, `REMARKS`, `LINE`, `DATE`) VALUES ('$LOT_ID','$USER_ID','$TIME','$PRODUCT','$GRADE',
		'$PACKING','$BS_RATIO','$VEIN','$SOFT','$BROKEN','$SMELL','$COL','$SPOTS',
		'$SUPERVISOR','$REMARKS','$LINE','$DATE')";	
			
			// 			echo $sql;die();	
	$sqlca_post = mysql_query($sql,$this->db);
	}
	if($sqlca_post){
		
	$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
	
	}else {
			echo mysql_errno($this->db);
	$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
	$this->response($this->json($records), 200);
	}		

}



//Coldstore input - 13-03-2020  // Coldstore REPORT #TEJA
public function csInputReport(){
	
	 $json = file_get_contents('php://input');
	 $data = json_decode($json,true);
	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$dt=$dateTime->format("d/m/y  h:i A"); 
	
	 $JSON_DATA=$data;
	
	$STATUS_SUCCESS='SUCCESS';
	$STATUS_FAILURE='FAILURE';			
	
	 $USER_ID = $data['USER_ID'];
	
	$PURCH_LOT_NUM = $data['LOT_ID'];
	$DATA = $data['DATA'];
	$DATA = json_decode($DATA,true);
	
// 	print_r($DATA);die();	
	
	foreach($DATA as $value){	
		
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
// 		$createdon = $value['CreatedDateTime'];
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$DATE_TIME=$dateTime->format("Y-m-d"); 
		$ENTRY_TIME=$dateTime->format("Y-m-d  h:i:S"); 
		
		if($INT_LOT_NUMBER==''){
			$INT_LOT_NUMBER='NA';
		}
		
		if($BAR_CODE_ID=''){
			$BAR_CODE_ID=1;
		}else{
		$BAR_CODE_ID=1;
		
		$SQL_CS_BAR_CODE_DATA="SELECT count(*) as TOTAL FROM `CS_BAR_CODE_DATA` where  BAR_CODE_VALUE='$BAR_CODE_ID'";						
		$STMT_CS_BAR_CODE_DATA = mysql_query($SQL_CS_BAR_CODE_DATA);
		$TOTAL=$STMT_CS_BAR_CODE_DATA['TOTAL'];
		
		if($TOTAL > 0  ){
		$BAR_CODE_ID = $value['BARCODE_ID'];	
		}else{
			$BAR_CODE_DATE = date("Y-m-d");	
			$BAR_CODE_VALUE = str_replace("-","",$BAR_CODE_DATE);	
			$BAR_CODE_VALUE=$value['BARCODE_ID'];
	 
			$sql_org_insert="INSERT INTO `CS_BAR_CODE_DATA`(`BAR_CODE_DATE`, `BAR_CODE_VALUE`, `BAR_CODE_STATUS`) 
								VALUES ('$BAR_CODE_DATE','$BAR_CODE_ID','1')";
			$stmt1=mysql_query($sql_org_insert);
			$BAR_CODE_ID = mysql_insert_id();
		}
		
		}
		
				
		 $PACKING_STYLE = $TOTAL_SLABS.' X ' .$SLAB_WEIGHT.' '. $UOM;
	 
	 $sql_org_insert="INSERT INTO `CS_PALLET_INPUT_DATA`(`CARTONS_NUMBER`, `DUMMY_MASTER`, `GRADES`, `TYPE`, `TOTAL_SLABS`, 
	 `SLAB_WEIGHT`, `FORM`, `DATE_TIME`, `INT_LOT_NUMBER`,
	 `PURCH_LOT_NUM`, `GROUP`, `CATEGORY`,`JME_MOURYA`, `SHIFT`, `BAR_CODE_ID`, `UOM`,`PACKING_STYLE`, `REMARKS`)
	 VALUES ('$CARTONS_NUMBER','$DUMMY_MASTER','$GRADES','$TYPE','$TOTAL_SLABS','$SLAB_WEIGHT',
	 '$FORM','$DATE_TIME','$INT_LOT_NUMBER','$PURCH_LOT_NUM','$GROUP','$CATEGORY','$JME_MOURYA','$SHIFT','$BAR_CODE_ID','$UOM',
	 '$PACKING_STYLE',
	 '$REMARKS')";	
			
			 			//echo $sql_org_insert;
	$sqlca_post = mysql_query($sql_org_insert,$this->db);
	}
	
	
	if($sqlca_post){
		
	$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
	
	}else {
	
	
	$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong ".$sql_org_insert. mysql_errno($this->db));
	$this->response($this->json($records), 200);

	}		

}



//HOHL Report
public function HOHLReport(){
	
	$json = file_get_contents('php://input');
	$data = json_decode($json,true);
	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$dt=$dateTime->format("d/m/y  h:i A"); 
	
	 $JSON_DATA=$data;
	
	$STATUS_SUCCESS='SUCCESS';
	$STATUS_FAILURE='FAILURE';			
	
	 $USER_ID = $data['USER_ID'];
	
	$LOT_ID = $data['LOT_ID'];
	$DATA = $data['DATA'];
	 $DATA = json_decode($DATA,true);
	
// 	print_r($DATA);die();	
	
	foreach($DATA as $value){	
		
		$hl_count=$value['Hl_COUNT'];
		$ho_count=$value['HO_COUNT'];
		$peelingKg=$value['PEELING_KG'];
		$dc=$value['DC'];
		$bs=$value['BS'];
		$hl_perc_ho=$value['HL_PERC_HO'];
		$checked_by=$value['CHECKED_BY'];
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$dt=$dateTime->format("Y-m-d  H:i:s"); 
				
		$ss_post="INSERT INTO `HOHL_report`(`user_id`,`LOT_ID`,`Hl_COUNT`,`HO_COUNT` ,`PEELING_KG`, `DC`, 
		`BS`, `HL_PERC_HO`, `CHECKED_BY`,`CreatedDateTime`)
			VALUES ('$USER_ID', '$LOT_ID', '$hl_count','$ho_count','$peelingKg','$dc','$bs','$hl_perc_ho','$checked_by','$dt')";
// 			echo $ss_post;die();
	$sqlca_post = mysql_query($ss_post,$this->db);
	}
	if($sqlca_post){
		
	$records = array('status' => $STATUS_SUCCESS, "msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
	
	}else {
			echo mysql_errno($this->db);
	$records = array('status' => $STATUS_FAILURE, "msg" => "Oops! Something went wrong");
	$this->response($this->json($records), 200);
	}		

}


function getPaymentValues($iconn,$PoID,$DocType){
	
	/*
	   $LbsFactor=0.454; 			// To Convert Lbs into Kgs  multiply value
	   $Discount=0;					// Get from Custom Invoice
	   $InsurancePerc=0.11;			// Varible
	   $AntiDumpingDFactor=101.35	// Anti Dumping Duty Factor(Formula)
	   $AntiDumpingMFactor=1.35		// Anti Dumping Duty Factor(Formula)
	   $StandardDutyCharges=125;	// Standard Value
	   $TermName=125;	// Standard Value
	   $CenttoUsd=100;	// Standard Value 1 usd=100 cents
	   
	   */
	   
	 //  echo $DocType; exit;
	 
	/* $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	$PoID=$IpData['PoID'];*/
	
	
	   $CenttoUsd=100;
	   if($DocType=='CMI'){
		 $SqlCInvData="SELECT *,ExchngRte as ExRate FROM ScrmPoData as SPD 
			LEFT JOIN ScrmCommercialData as SCD on 
			SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";
	   }else{
		  $SqlCInvData="SELECT * FROM ScrmPoData as SPD 
			LEFT JOIN ScrmCustomInvoices as SCI on 
			SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";  
	   }
		//echo $SqlCInvData; 
		
		$StmtCInvData = mysql_query($SqlCInvData,$this->db);
		$RowCInvData = mysql_fetch_array($StmtCInvData,MYSQL_ASSOC);
			
		
		$TermName=$RowCInvData['ShippingTerms'];
		$Discount=$RowCInvData['Discount'];
		$FrieghtCharge=$RowCInvData['Freight'];
		$ExchangeRate=$RowCInvData['ExRate'];
		$QtyUnit=$RowCInvData['QtyUnit'];
		$CInvCode=$RowCInvData['CInvCode'];
		
		if($CInvCode=='03061720'){
			$IgstPerc=5;
		}else{
			$IgstPerc=12;	
		}
		$QtyUnit=$RowCInvData['QtyUnit'];
	   
	    $SqlShipTermsMaster="SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'";
	
	   $StmtShipTermsMaster = mysql_query($SqlShipTermsMaster,$this->db);
		$RowShipTermsMaster = mysql_fetch_array($StmtShipTermsMaster,MYSQL_ASSOC);
		
		
		// Update With ExportId Condition
		$SqlExporterTaxRates="SELECT * FROM `ScrmExporterTaxRates`";
		
		$StmtExporterTaxRates = mysql_query($SqlExporterTaxRates,$this->db);
		$RowExporterTaxRates = mysql_fetch_array($StmtExporterTaxRates,MYSQL_ASSOC);
		
		
		$AntiDumpingDutyPerc=$RowExporterTaxRates['AntiDumpingDuty'];
		$MerchandiseProcessFeePerc=$RowExporterTaxRates['MerchandiseProcessFee'];
		$HarbourMaintenanceFeePerc=$RowExporterTaxRates['HarbourMaintenanceFee'];
		$ISFStaticDuty=$RowExporterTaxRates['ISFStaticDuty'];
		$InsurancePerc=$RowExporterTaxRates['InsurancePremiumRate'];
		
		 $TotalDutyPerc=100+$AntiDumpingDutyPerc+
						$MerchandiseProcessFeePerc+
						$HarbourMaintenanceFeePerc; 
		
		
				
		$IsFreight=$RowShipTermsMaster['Freight'];		
		$IsDuty=$RowShipTermsMaster['Duty'];
		$Insurance=$RowShipTermsMaster['Insurance'];		
		$AntiDumpingDFactor=$RowShipTermsMaster['AntiDumpingDFactor'];		
		$AntiDumpingMFactor=$RowShipTermsMaster['AntiDumpingMFactor'];		
		$StandardDutyCharges=$RowShipTermsMaster['StandardDutyCharges'];		

		

       $sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
		$SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs 
			FROM `ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS on 
			PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'"; 
			$StmtTotal = mysql_query($SqlTotal,$this->db);
			$RowTotal = mysql_fetch_array($StmtTotal,MYSQL_ASSOC);
			
			$NetWgtKgs = $RowTotal['NetWgtLbs']*$LbsFactor;
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			$TotalMcs = $RowTotal['MCS'];
			
			
			
			if($QtyUnit=='KGS'){				 
				$Weight = $RowTotal['NetWgtKgs'];	
				$InvoiceValue = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'] * $LbsFactor;
				$InvoiceValue = $RowTotal['PriceLbs'];
			}
			
			if($Discount>0){
			$DiscountAmount=($InvoiceValue*$Discount)/100;
			$InvoiceValueAfterDiscount=$InvoiceValue;
			$InvoiceValueAfterDiscount=$InvoiceValue-$DiscountAmount;	// By Rantagiri 04/17/2023 5.44			
			}else{
			$InvoiceValueAfterDiscount=$InvoiceValue;
			}
			
			if($Insurance==1){
				$InsuranceValue= ($PriceAfterDiscount*$InsurancePerc)/100;
			}else{
				$InsuranceValue=0;
			}
			
			if($IsDuty==1){
				
			$MerchandiseProcessFee=(($InvoiceValueAfterDiscount-$InsuranceValue-$FrieghtCharge)/$TotalDutyPerc)*$MerchandiseProcessFeePerc ; 		
			//$MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);
			if($MerchandiseProcessFee > $ISFStaticDuty){
				$ISFStatic=true;
				$MerchandiseProcessFee=$ISFStaticDuty;
				$TotalDutyPerc=$TotalDutyPerc-$MerchandiseProcessFeePerc;
				 $EffInvValue=$InvoiceValueAfterDiscount-$InsuranceValue-$FrieghtCharge-$MerchandiseProcessFee;
			}else{
				$ISFStatic=false;
				 $EffInvValue=$InvoiceValueAfterDiscount-$InsuranceValue-$FrieghtCharge;
				 $MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);
			
			}
			
			$AntiDumpingCharges=round((($EffInvValue*$AntiDumpingDutyPerc)/$TotalDutyPerc),2);			
			$HarbourMaintenanceFee=round((($EffInvValue*$HarbourMaintenanceFeePerc)/$TotalDutyPerc),2);
			
			if($ISFStatic){			
			$DutiesPrice=$AntiDumpingCharges+$HarbourMaintenanceFee;
			}else{
			$DutiesPrice=$AntiDumpingCharges+$MerchandiseProcessFee+$HarbourMaintenanceFee;
			}
				
			}else{
			$DutiesPrice=$DutiesPrice+$StandardDutyCharges;
			}
			 
			$InvValueUSD=$InvoiceValueAfterDiscount;
			if($ISFStatic){
			$InvValueUSDWithOutDuties=$InvoiceValueAfterDiscount-$FrieghtCharge-$ISFStaticDuty;
			$FOBValueUSD=$InvoiceValueAfterDiscount-$FrieghtCharge-$DutiesPrice-$ISFStaticDuty;
			}else{
			$InvValueUSDWithOutDuties=$InvoiceValueAfterDiscount-$FrieghtCharge;
			$FOBValueUSD=$InvoiceValueAfterDiscount-$FrieghtCharge-$DutiesPrice;	
			}
			
			
			$InvValueINR=$InvValueUSD*$ExchangeRate;
			$FOBValueINR=$FOBValueUSD*$ExchangeRate;
			
			$IgstValue=($InvValueINR*$IgstPerc)/100;
			
		
			
			$AryPrices['DiscountPerc']=$Discount;
			$AryPrices['ExchangeRate']=$ExchangeRate;
			$AryPrices['DiscountAmount']=$DiscountAmount;
			$AryPrices['InsuranceValue']=$InsuranceValue;
			$AryPrices['DutiesPrice']=$DutiesPrice;
			$AryPrices['FrieghtCharge']=$FrieghtCharge;
			
			$AryPrices['InvValueUSD']=$InvValueUSD;
			$AryPrices['InvValueUSDWithOutDuties']=$InvValueUSDWithOutDuties;
			$AryPrices['InvValueINR']=$InvValueINR;
			$AryPrices['FOBValueUSD']=$FOBValueUSD;
			$AryPrices['FOBValueINR']=$FOBValueINR;
			$AryPrices['PaymentTerm']=$TermName;
			$AryPrices['IgstPerc']=$IgstPerc;
			$AryPrices['IgstValue']=$IgstValue;
			$AryPrices['AntiDumpingDuty']=$AntiDumpingCharges;
			$AryPrices['MerchantDuty']=$MerchandiseProcessFee;
			$AryPrices['HarborDuty']=$HarbourMaintenanceFee;
			$AryPrices['ISFStatic']=$ISFStatic;
			$AryPrices['CenttoUsd']=$CenttoUsd;
			$AryPrices['TotalMcs']=$TotalMcs;
			
			return $AryPrices;
	
}


function getBillRealizationDetails(){
	
	//$Error=true; 
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);	
	$PoID=$IpData['PoID'];


$FeildsList="
	SRD.RezDueDate,SRD.RezRef1,SRD.RezValue1,SRD.RezDate1,
	SRD.RezRef2,SRD.RezValue2,SRD.RezDate2,
	SRD.RezRef3,SRD.RezValue3,SRD.RezDate3,
	SRD.FBKCharges, SRD.OtherCharges, SRD.RezStatus,
SCI.CustomsInvNo,SCI.CInvDate,SPD.ConsigneeID,SCD.ConsigneeName,SCI.PaymentType";

 $SqlBillDtls="SELECT $FeildsList FROM `ScrmCustomInvoices` SCI
LEFT JOIN ScrmPoData SPD ON SPD.PoID=SCI.PiID
LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmRealizationDetails SRD ON SRD.PoID=SPD.PoID 
where SPD.PoID='$PoID'";

$StmtBillDtls = mysql_query($SqlBillDtls,$this->db);
$RowBillDtls= mysql_fetch_array($StmtBillDtls,MYSQL_ASSOC);

	$AryBillDetails=array();
	 
	 $AryBillDetails['DueDate']=$RowBillDtls['RezDueDate'];
	 $AryBillDetails['BankRefNo1']=$RowBillDtls['RezRef1'];
	 $AryBillDetails['RealisedValue1']=$RowBillDtls['RezValue1'];
	 $AryBillDetails['RealisedDate1']=$RowBillDtls['RezDate1'];
	 $AryBillDetails['BankRefNo2']=$RowBillDtls['RezRef2'];
	 $AryBillDetails['RealisedValue2']=$RowBillDtls['RezValue2'];
	 $AryBillDetails['RealisedDate2']=$RowBillDtls['RezDate2'];
	 $AryBillDetails['BankRefNo3']=$RowBillDtls['RezRef3'];
	 $AryBillDetails['RealisedValue3']=$RowBillDtls['RezValue3'];
	 $AryBillDetails['RealisedDate3']=$RowBillDtls['RezDate3'];
	 
	 $AryBillDetails['FBKCharges']=$RowBillDtls['FBKCharges'];
	 $AryBillDetails['OtherCharges']=$RowBillDtls['OtherCharges'];
	 $AryBillDetails['RezStatus']=$RowBillDtls['RezStatus'];
	
		$AryBillDetails['CustomsInvNo']=$RowBillDtls['CustomsInvNo'];
		$AryBillDetails['CInvDate']=$RowBillDtls['CInvDate'];
		$AryBillDetails['ConsigneeName']=$RowBillDtls['ConsigneeName'];
		$AryBillDetails['PaymentType']=$RowBillDtls['PaymentType'];
					
				
$SqlColctByNego="SELECT SPC.DueDte,SCBP.ColType,SCBP.BankRefNo,SCBP.UtilizedValue,UtilizedDate,SpotRate
 FROM `ScrmPaymentCollections` SPC
LEFT JOIN ScrmCollectionBreakUP SCBP ON SCBP.PiID=SPC.PIID
WHERE SPC.PIID='$PoID' ORDER BY SCBP.ColBrkID ;";

$SqlUtillizedvalue="SELECT SUM(UtilizedValue) AS TotalValue FROM 
`ScrmCollectionBreakUP` WHERE PiID='$PoID'";

			
				
$StmtUtillizedvalue= mysql_query($SqlUtillizedvalue,$this->db);
$RowUtillizedvalue= mysql_fetch_array($StmtUtillizedvalue,MYSQL_ASSOC);

$StmtColctByNego= mysql_query($SqlColctByNego,$this->db);
WHILE($RowColctByNego= mysql_fetch_array($StmtColctByNego,MYSQL_ASSOC)){			
				

$BankRefNo=$RowColctByNego['BankRefNo'];
$FcNumber=$RowColctByNego['FcNumber'];
$UtilizedValue=$RowColctByNego['UtilizedValue'];
$UtilizedDate=$RowColctByNego['UtilizedDate'];
$ColType=$RowColctByNego['ColType'];
$SpotRate=$RowColctByNego['SpotRate'];
$UtilizedValueINR=$UtilizedValue*$SpotRate;

    $AryBillBreakupDetails['UtilizedValue']=$UtilizedValue;
    $AryBillBreakupDetails['UtilizedDate']=$UtilizedDate;
    $AryBillBreakupDetails['ColType']=$ColType;
    $AryBillBreakupDetails['SpotRate']=$SpotRate;
    $AryBillBreakupDetails['UtilizedValueINR']=$UtilizedValueINR;
	$AryBillBreakupDetails['BankRefNo']=$BankRefNo;
	$GrandUtilizedValue+=$UtilizedValue;
	$GrandValueINR+=$UtilizedValueINR;
	
	$AryBreakUp[]=$AryBillBreakupDetails;

}
      $RealizedValue=$RowUtillizedvalue['TotalValue'];
      $AryPriceDATA=$this->getPaymentValues($PoID); 
      $InvValueUSD=$AryPriceDATA['InvValueUSD'];
	  $ShortFallValue=$InvValueUSD-$RealizedValue;
	
   
	$AryBillDetails['GrandValueINR']=$GrandValueINR;
	$AryBillDetails['GrandUtilizedValue']=$GrandUtilizedValue;
	$AryBillDetails['RealizedValue']=$RealizedValue;
	$AryBillDetails['InvValueUSD']=$InvValueUSD;
	$AryBillDetails['ShortFallValue']=$ShortFallValue;
	
	
	
	$data['BillRealization']=$AryBillDetails;
	$data['BillBreakUp']=$AryBreakUp;
   
	if($StmtColctByNego){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	
}


 
function saveBillRealizationDetails(){

	//$Error=true;
	
	 $RawIpData = file_get_contents('php://input');
	 $IpData = json_decode($RawIpData,true);
	 	
	 $PoID=$IpData['PIID'];
	 $RezDueDate=$IpData['DueDate'];
	 $RezRef1=$IpData['BankRefNo1'];
	 $RezValue1=$IpData['RealisedValue1'];
	 $RezDate1=$IpData['RealisedDate1'];
	 $RezRef2=$IpData['BankRefNo2'];
	 $RezValue2=$IpData['RealisedValue2'];
	 $RezDate2=$IpData['RealisedDate2'];
	 $RezRef3=$IpData['BankRefNo3'];
	 $RezValue3=$IpData['RealisedValue3'];
	 $RezDate3=$IpData['RealisedDate3'];
	 
	 $FBKCharges=$IpData['FBKCharges'];
	 $OtherCharges=$IpData['OtherCharges'];
	 $RezStatus=$IpData['RezStatus'];
	 
	$timezone = new DateTimeZone('Asia/Kolkata');
    $datetime = new DateTime('now', $timezone);
    $PostedDateTime = $datetime->format('Y-m-d H:i:s');
	
	 $SqlCheckRezDetails="SELECT PoID,(RezValue1+RezValue2+RezValue3) as TotalRez,
	 FBKCharges,OtherCharges FROM `ScrmRealizationDetails` WHERE PoID='$PoID'";
	 $StmtCheckRezDetails= mysql_query($SqlCheckRezDetails,$this->db);
	 $RowRealization = mysql_fetch_array($StmtCheckRezDetails,MYSQL_ASSOC);
	 
	 $TotalRealised=$RezValue1+$RezValue2+$RezValue3+$FBKCharges+$OtherCharges;
	 
	
	 
	  $AryPriceDATA=$this->getPaymentValues($PoID); 
      $InvValueUSD=$AryPriceDATA['InvValueUSD'];
	  $ShortFallValue=$InvValueUSD-$RealizedValue;
	  $RezStatus=0;
	  $Error=false;
	  if($TotalRealised==$InvValueUSD){
	  $RezStatus=1;
	 
	  }
	  
	  if($TotalRealised>$InvValueUSD){
	  $Error=true;
	  $Msg="Realization Value Exceed Shortfall Value";
	  }
	  
	 if(mysql_num_rows($StmtCheckRezDetails)>0){
	 $IsUpdate=true;
	 }else{
	 $IsUpdate=false; 
	 }
	 
	 if(!$IsUpdate && !$Error){

$SqlRelzDetails="INSERT INTO `ScrmRealizationDetails`
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
	 $StmtRelzDetails= mysql_query($SqlRelzDetails,$this->db);
	 }else if(!$Error){
	$SqlRelzDetails="update `ScrmRealizationDetails` 
	set  
	`RezDueDate`='$RezDueDate',`RezRef1`='$RezRef1',`RezValue1`='$RezValue1',`RezDate1`='$RezDate1',
	`RezRef2`='$RezRef2',`RezValue2`='$RezValue2',`RezDate2`='$RezDate2',
	`RezRef3`='$RezRef3',`RezValue3`='$RezValue3',`RezDate3`='$RezDate3',
	`FBKCharges`='$FBKCharges', `OtherCharges`='$OtherCharges',
	`RezStatus`='$RezStatus' where PoID='$PoID'
	";
	 $StmtRelzDetails= mysql_query($SqlRelzDetails,$this->db);
	 }


	if($StmtRelzDetails){
				$data['Error']=null;
				$data['Msg']="Success";
				$records = array('Data' => $data);
	$this->response($this->json($records), 200);
				
	}else {
	$data['Error']=$Msg;	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}

 
function getRealizationList(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 $BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];

$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}
if($BillsType=="0"){
	
	/*$IQuery='and POD.PoID in 
(SELECT PIID FROM ScrmPaymentCollections as SPC where SPC.PIID=POD.PoID and SPC.Status=1)
and POD.PoID not in (SELECT PoID FROM ScrmRealizationDetails )';*/

$IQuery='and POD.PoID in 
(SELECT PIID FROM ScrmPaymentCollections as SPC where SPC.PIID=POD.PoID and SPC.Status=0)';
}else if($BillsType=="1"){

//$IQuery=" and POD.PoID  in (SELECT PoID FROM `ScrmRealizationDetails`)";	
$IQuery=" and POD.PoID  in (SELECT PIID FROM ScrmPaymentCollections as SPC where SPC.PIID=POD.PoID and SPC.Status=1)";	
}

if($Year!=''){
	$Error=false;
}
	
	
	if(!$Error){
		 $SqlFields='POD.PoID,
		POD.PoNO,
		SCD.ConsigneeName,
		SCP.Type,
		SCI.CustomsInvNo as CInvNo,
		(SELECT BankRefNo FROM `ScrmCollectionBreakUP` as SCB WHERE POD.PoID=SCB.PiID order by ColBrkID desc LIMIT 1) as BankRefNo,
		SCI.CInvDate';
		
	   $SqlRealization="SELECT $SqlFields FROM ScrmCustomInvoices as SCI 
			left join ScrmPoData as POD on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
			left join ScrmPaymentCollections as SCP on POD.PoID=SCP.PIID
            left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID 			
			WHERE SCI.CInvDate $DateFilter $IQuery and POD.Status not in (3,7)   
            and TransportedDate!=''			
			$Query $SQuery  $SubQuery order by CommercialID desc";
	
	$StmtRealization = mysql_query($SqlRealization,$this->db);
	
		while($RowRealization = mysql_fetch_array($StmtRealization,MYSQL_ASSOC)){
					
					 $InvDate=$this->getFormatedDate($RowRealization['CInvDate']);
					$Type=$RowRealization['Type'];
					if($Type==1){
					$TransactionType='Collection';	
					}
					else{
					$TransactionType='Negotiation';	
					}
					$RowRealization['Sno']=++$Sno;		
					$RowRealization['TransactionType']=$TransactionType;		
					$RowRealization['CInvDate']=$InvDate;		
					
					$AryRowRealization[] = $RowRealization;
				}
				
				
				$data['Link']="RealizationForm?PIID=";
				$data['RealizationList']=$AryRowRealization;
	
	if($StmtRealization){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}

function postFDADetails(){

	$Error=true;
	
	 $RawIpData = file_get_contents('php://input');
	 $IpData = json_decode($RawIpData,true);
	
	 $PoID=$IpData['PoID'];
	 $ContainerGateOutDate=$IpData['ContainerGateOutDate'];
	 $FDAEUHoldDate=$IpData['FDAEUHoldDate'];
	 $FDAEUReleaseCFIADate=$IpData['FDAEUReleaseCFIADate'];
	 $FDAEntryNumber=$IpData['FDAEntryNumber'];
	 $FDAEntryDate=$IpData['FDAEntryDate'];
	 $AgentInvoice=$IpData['AgentInvoice'];
	 $EmptyContainerEntryNumber=$IpData['EmptyContainerEntryNumber'];
	 $EmptyContainerReturnDate=$IpData['EmptyContainerReturnDate'];
	 $ActualPaidUSD=$IpData['ActualPaidUSD'];
	 $PostedUserId=$IpData['PostedUserId'];
	 
	 $timezone = new DateTimeZone('Asia/Kolkata');
    $datetime = new DateTime('now', $timezone);
    $PostedDateTime = $datetime->format('Y-m-d H:i:s');
	
	 
	 $SqlCount="SELECT * FROM `ScrmFdaDetails` WHERE PoID='$PoID'";
	 $StmtCount= mysql_query($SqlCount,$this->db);
	 
	$RowCount = mysql_fetch_array($StmtCount,MYSQL_ASSOC);
	 $IsEdit=$RowCount['IsEdit']+1;
	 $Count=mysql_num_rows($StmtCount);
	 if($Count==0){

$SqlInsert="INSERT INTO `ScrmFdaDetails`(`PoID`, `ContainerGateOutDate`, `FDAEUHoldDate`, `FDAEUReleaseCFIADate`, `FDAEntryNumber`, `FDAEntryDate`, `AgentInvoice`,`ActualPaidUSD`,`EmptyContainerEntryNumber`,`EmptyContainerReturnDate`, `PostedUserID`, `PostedDateTime`, `IsEdit`) VALUES('$PoID','$ContainerGateOutDate','$FDAEUHoldDate','$FDAEUReleaseCFIADate','$FDAEntryNumber','$FDAEntryDate','$AgentInvoice','$ActualPaidUSD','$EmptyContainerEntryNumber','$EmptyContainerReturnDate','$PostedUserId','$PostedDateTime','0') ";
	 }
	 else{
		$SqlInsert=" UPDATE `ScrmFdaDetails` SET `ContainerGateOutDate`='$ContainerGateOutDate',`FDAEUHoldDate`='$FDAEUHoldDate',`FDAEUReleaseCFIADate`='$FDAEUReleaseCFIADate',`FDAEntryNumber`='$FDAEntryNumber',`FDAEntryDate`='$FDAEntryDate',`AgentInvoice`='$AgentInvoice',`ActualPaidUSD`='$ActualPaidUSD',`EmptyContainerEntryNumber`='$EmptyContainerEntryNumber',`EmptyContainerReturnDate`='$EmptyContainerReturnDate',`PostedUserID`='$PostedUserId',
		`PostedDateTime`='$PostedDateTime',`IsEdit`='$IsEdit' WHERE `PoID`='$PoID'";
	 }
	 //echo $SqlInsert; exit;
 $StmtInsert= mysql_query($SqlInsert,$this->db);
	if($StmtInsert){
				$data['Error']=null;
				$data['Msg']="Success";
				$records = array('Data' => $data);
	$this->response($this->json($records), 200);
				
	}else {
	$data['Error']="Unable To Post Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}
function getForwardContractorList(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 $BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	

$Query='';	
$IQuery='';	

if($Exporter!=""){
	$Query='and ExpID in ('.$Exporter.')';
}

if($BillsType=="0"){
	
	$IQuery=" and BalancedValue!=0";
}else if($BillsType=="1"){

$IQuery=" and BalancedValue =0";	
}

if($Year!=''){
	$Error=false;
}
	
	
	
	if(!$Error){
		 $SqlFields='FCID,
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
		BalancedValue as BalanceUSD
		';
		
	   $SqlForwardContractors="SELECT $SqlFields FROM `ScrmForwardContractsDetails` where FinYear='$Year'   $IQuery $Query order by FCID desc"; 
	
	$StmtForwardContractors = mysql_query($SqlForwardContractors,$this->db);
	
		while($RowForwardContractors = mysql_fetch_array($StmtForwardContractors,MYSQL_ASSOC)){
					$InvNo=$RowForwardContractors['CInvNo'];
					$InvDate=$RowCollection['CInvDate'];
					$ExpID=$RowCollection['ExporterID'];
					$ProcID=$RowCollection['ProcessorID'];
					$RowForwardContractors['Sno']=++$Sno;		
					$RowForwardContractors['CurrentDate']=date('Y-m-d');
					
					$CurrentDate = new DateTime(date('Y-m-d'));
                  
					$AryRowForwardContractors[] = $RowForwardContractors;
				}
				
				
				$data['AddLink']="AddForwardContract";
				$data['EditLink']="AddForwardContract?FCID=";
				$data['ForwardContractorsList']=$AryRowForwardContractors;
	
	if($StmtForwardContractors){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}

function getNegotiationList(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 $BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];

$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}
if($BillsType=="0"){
	//$IQuery=' SIFD.PIID not in (SELECT PoID FROM `ScrmCommercialData`) ';
	$IQuery="SCI.CInvDate $DateFilter and POD.PoID not in (SELECT PIID FROM `ScrmPaymentNegotitions`) ";
}else if($BillsType=="1"){
//$IQuery="SCI.CInvDate $DateFilter and SCI.PiID  in (SELECT PoID FROM `ScrmCommercialData`)";	
$IQuery="SCI.CInvDate $DateFilter and POD.PoID  in (SELECT PIID FROM `ScrmPaymentNegotitions`)";	
}

if($Year!=''){
	$Error=false;
}
	
	
	if(!$Error){
		 $SqlFields='POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCD.ConsigneeName,
		SPN.BnkRefNo as BankRefNo,
		SCI.CInvNo,
		SCI.CInvDate';
		
	   $SqlNegotiation="SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
			left join ScrmPaymentNegotitions as SPN on POD.PoID=SPN.PIID
            left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID 			
			WHERE $IQuery and POD.Status not in (3,7)  and SCI.PaymentType='Negotiations'
            and TransportedDate!=''			
			$Query $SQuery  $SubQuery order by CommercialID desc"; 
	
	$StmtNegotiation = mysql_query($SqlNegotiation,$this->db);
	
		while($RowNegotiation = mysql_fetch_array($StmtNegotiation,MYSQL_ASSOC)){
					$InvNo=$RowNegotiation['CInvNo'];
					$InvDate=$RowNegotiation['CInvDate'];
					$ExpID=$RowNegotiation['ExporterID'];
					$ProcID=$RowNegotiation['ProcessorID'];
					$RowNegotiation['Sno']=++$Sno;		
					$RowNegotiation['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);							
					$AryRowNegotiation[] = $RowNegotiation;
				}
				
				
				$data['Link']="AddNegotiation?PIID=";
				$data['NegotiationList']=$AryRowNegotiation;
	
	if($StmtNegotiation){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}

function getCollectionList(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 $BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
    $PaymentType=$IpData['PaymentType'];
   $DateFilter=$this->getFinancialYear($Year);
	
	
$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	
$PQuery='';

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}
if($BillsType=="0"){
	
	$IQuery="SCI.CInvDate $DateFilter and POD.PoID not  in (SELECT PIID FROM `ScrmPaymentCollections` where Status!=0 ) ";
	
	/*$IQuery="SCI.CInvDate $DateFilter and POD.PoID not  in (SELECT PIID FROM `ScrmPaymentCollections`)";*/
}else if($BillsType=="1"){


$IQuery="SCI.CInvDate $DateFilter and POD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 ) ";

/*$IQuery="SCI.CInvDate $DateFilter and POD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where  Type='$PaymentType')";*/	
}

if($PaymentType=="1"){
	
	$PQuery=" and SCI.PaymentType='Collections'";
}
else if($PaymentType=="2"){
	
	$PQuery=" and SCI.PaymentType='Negotiations'";
}

if($Year!=''){
	$Error=false;
}
	
	
	if(!$Error){
		 $SqlFields='POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCD.ConsigneeName,
		SCI.CInvNo,
		(Select BankRefNo from ScrmCollectionBreakUP  where PIID=POD.PoID order by ColBrkID  desc limit 1) as BankRefNo,
		SCI.CInvDate';
		
	   $SqlCollection="SELECT $SqlFields FROM  ScrmCustomInvoices as SCI 
			left join ScrmPoData as POD on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
			left join ScrmPaymentCollections as SPC on POD.PoID=SPC.PIID 
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID 
			WHERE $IQuery and POD.Status not in (3,7)   
            and TransportedDate!=''			
			$Query $SQuery  $SubQuery $PQuery order by CommercialID desc"; 
	
	$StmtCollection = mysql_query($SqlCollection,$this->db);
	
		while($RowCollection = mysql_fetch_array($StmtCollection,MYSQL_ASSOC)){
					$InvNo=$RowCollection['CInvNo'];
					$InvDate=$RowCollection['CInvDate'];
					$ExpID=$RowCollection['ExporterID'];
					$ProcID=$RowCollection['ProcessorID'];
					$RowCollection['Sno']=++$Sno;		
					$RowCollection['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);							
					$AryRowCollection[] = $RowCollection;
				}
				
				
				$data['Link']="AddCollection?PIID=";
				$data['CollectionList']=$AryRowCollection;
	
	if($StmtCollection){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}



function getPendingPaymentsData(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 //$BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	
$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	
$PQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}



if($Year!=''){
	$Error=false;
}
	
	                                                
										
	if(!$Error){
		 $SqlFields='POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		SCD.ConsigneeName,
		SPC.Name as PortOfDischarge,
		SIF.ETA_POD,
		SIF.EstimatedETA as ETAModifyDate,
		SIF.GetOutFull,
		SIF.FDAClearDate,
		SIF.ShippingOnboardDt,
		SPN.DueDate,
		SPN.BankDueDate,
		COALESCE(DATEDIFF(SIF.ETA_POD,now())-1,0) as ETATransitDays,
		COALESCE(DATEDIFF(SIF.ETA_POD,SIF.ShippingOnboardDt),0) as TransitDays,
		SCI.CInvNo,
		(SELECT sum(Mcs)  FROM `ScrmPoGradeData` WHERE InvId=POD.PoID)as Mcs,
		SPT.PaymentName,
		SCI.CInvDate';
		
	  $SqlPendingPayments="SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmISFDetails as SIF on POD.PoID=SIF.PIID 
			left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 
			left join ScrmPaymentNegotitions as SPN on POD.PoID=SPN.PIID 
			WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7)   $Query $SQuery  $SubQuery  order by POD.PoID desc";
			 
	
	$StmtPendingPayments = mysql_query($SqlPendingPayments,$this->db);
	
		while($RowPendingPayments = mysql_fetch_array($StmtPendingPayments,MYSQL_ASSOC)){
			
			
			
					$InvNo=$RowPendingPayments['CInvNo'];
					$InvDate=$RowPendingPayments['CInvDate'];
					$ExpID=$RowPendingPayments['ExporterID'];
					$ProcID=$RowPendingPayments['ProcessorID'];
					$RowPendingPayments['Sno']=++$Sno;		
					$RowPendingPayments['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);	
					$ArrayPaymentValues=$this->getPaymentValues($PoID);	
                   $RowPendingPayments['TotalInvoiceValue']=$ArrayPaymentValues['InvValueUSD'];	$RowPendingPayments['RemainingInvoiceValue']=$RemainingInvoiceValue;			
					$AryRowPendingPayments[] = $RowPendingPayments;
				}
				
				
				//$data['Link']="AddCollection?PIID=";
				$data['Data']=$AryRowPendingPayments;
	
	if($StmtPendingPayments){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}

function getSalesOrderwiseData(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 //$BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	$Month=$IpData['Month'];
	
$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	
$MQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}
if($Month!=""){
	$MQuery='and Month(POD.PoDate)='.$Month;
}



if($Year!=''){
	$Error=false;
}
	
	                                                
										
	if(!$Error){
		 $SqlFields='POD.PoID,
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
		
	  $SqlCountryWiseData="SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 
			WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7)   $Query $SQuery  $SubQuery $MQuery order by POD.PoID desc";
			 
	
	$StmtCountryWiseData = mysql_query($SqlCountryWiseData,$this->db);
	
		while($RowCountryWiseData = mysql_fetch_array($StmtCountryWiseData,MYSQL_ASSOC)){
			
			
			
					$InvNo=$RowCountryWiseData['CInvNo'];
					$InvDate=$RowCountryWiseData['CInvDate'];
					$ExpID=$RowCountryWiseData['ExporterID'];
					$ProcID=$RowCountryWiseData['ProcessorID'];
					$RowCountryWiseData['Sno']=++$Sno;		
					$RowCountryWiseData['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);	
					$ArrayPaymentValues=$this->getPaymentValues($PoID);	
                   $RowCountryWiseData['TotalInvoiceValue']=$ArrayPaymentValues['InvValueUSD'];				
					$AryRowCountryWiseData[] = $RowCountryWiseData;
				}
				
				
				//$data['Link']="AddCollection?PIID=";
				$data['Data']=$AryRowCountryWiseData;
	
	if($StmtCountryWiseData){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}


function getProformaInvoiceList(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 //$BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	 $PageNo=$IpData['PageNo'];
	
	
$Query='';	
$SubQuery='';
$SQuery='';	


if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}

$RecordsPerPage=100;

 $LimitMax=$PageNo*$RecordsPerPage;
 $LimitStartMin=$LimitMax-$RecordsPerPage;
$Limit=$LimitStartMin.','.$LimitMax;


if($Year!=''){
	$Error=false;
}
	 $SqlCustomInvoices="SELECT COALESCE(GROUP_CONCAT(SCI.PiID),0) as CompleatedPIID FROM `ScrmCustomInvoices` as SCI left Join ScrmShipmentDetailsBreif as SSDB on SCI.PiID=SSDB.PiID
	WHERE SSDB.TransportedDate=''";
	$StmtCInv=mysql_query($SqlCustomInvoices,$this->db);
	$RowCInv=mysql_fetch_array($StmtCInv,MYSQL_ASSOC);
	 $CompleatedPIID=$RowCInv['CompleatedPIID'];

                                         
										
	if(!$Error){
		 $SqlFields='POD.PoID,
		SCD.ConsigneeName,
		POD.PoNo,
		POD.InvNumFull as InvNo,
		POD.InvDate,
		POD.ShipByDate,
		SSC.Name as PoStatus,
		SSC.Color,
		AED.ExpName as ProcessorName';
		
	  $SqlPIData="SELECT $SqlFields
 FROM ScrmPIData as POD 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join AdminExporterDetails as  AED on AED.ExpID=POD.ProcessorID
WHERE POD.PoID not in ($CompleatedPIID)  $Query $SQuery  $SubQuery  LIMIT $RecordsPerPage OFFSET $LimitStartMin";
			 
	
	$StmtPIData = mysql_query($SqlPIData,$this->db);
	
		while($RowPIData = mysql_fetch_array($StmtPIData,MYSQL_ASSOC)){
			//$InvNo=$RowCountryWiseData['InvNo'];
					//$InvDate=$RowCountryWiseData['CInvDate'];
					//$ExpID=$RowCountryWiseData['ExporterID'];
					//$ProcID=$RowCountryWiseData['ProcessorID'];
					$RowPIData['Sno']=++$Sno;		
					$AryRowPIData[] = $RowPIData;
				}
				
				
				$data['TotalCount']="533";
				$data['RemainingCount']="10";
				$data['EditLink']="PoEdit?id=";
				$data['PrintLink']="DocProformaInvoicePrint.php?id=";
				$data['InvoiceList']=$AryRowPIData;
	
	if($StmtPIData){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}



function getSalesShipmentsData(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 //$BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	$Month=$IpData['Month'];
	
$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	
$MQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}
if($Month!=""){
	$MQuery='and Month(POD.PoDate)='.$Month;
}



if($Year!=''){
	$Error=false;
}
	
	                                                
										
	if(!$Error){
		 $SqlFields='POD.PoID,
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
		
	  $SqlCountryWiseData="SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 
			WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7)   $Query $SQuery  $SubQuery $MQuery order by POD.PoID desc";
			 
	
	$StmtCountryWiseData = mysql_query($SqlCountryWiseData,$this->db);
	
		while($RowCountryWiseData = mysql_fetch_array($StmtCountryWiseData,MYSQL_ASSOC)){
			
			
			
					$InvNo=$RowCountryWiseData['CInvNo'];
					$InvDate=$RowCountryWiseData['CInvDate'];
					$ExpID=$RowCountryWiseData['ExporterID'];
					$ProcID=$RowCountryWiseData['ProcessorID'];
					$RowCountryWiseData['Sno']=++$Sno;		
					$RowCountryWiseData['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);	
					$ArrayPaymentValues=$this->getPaymentValues($PoID);	
                   $RowCountryWiseData['TotalInvoiceValue']=$ArrayPaymentValues['InvValueUSD'];				
					$AryRowCountryWiseData[] = $RowCountryWiseData;
				}
				
				
				//$data['Link']="AddCollection?PIID=";
				$data['Data']=$AryRowCountryWiseData;
	
	if($StmtCountryWiseData){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}
function getCountrywisePoData(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 //$BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	
$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	
$PQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}



if($Year!=''){
	$Error=false;
}
	
	                                                
										
	if(!$Error){
		 $SqlFields='POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		SCD.ConsigneeName,
		SPC.Name as PortOfDischarge,
		SPC.name as DestinationCountry,
		SCI.CInvNo,
		(SELECT sum(Mcs)  FROM `ScrmPoGradeData` WHERE InvId=POD.PoID)as Mcs,
		SPT.PaymentName,
		SCI.CInvDate';
		
	  $SqlCountryWiseData="SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmPaymentTerms as SPT on POD.PaymentTerms=SPT.ID 
			WHERE SSDB.TransportedDate!='' and POD.Status not in (3,7)   $Query $SQuery  $SubQuery  order by POD.PoID desc";
			 
	
	$StmtCountryWiseData = mysql_query($SqlCountryWiseData,$this->db);
	
		while($RowCountryWiseData = mysql_fetch_array($StmtCountryWiseData,MYSQL_ASSOC)){
			
			
			
					$InvNo=$RowCountryWiseData['CInvNo'];
					$InvDate=$RowCountryWiseData['CInvDate'];
					$ExpID=$RowCountryWiseData['ExporterID'];
					$ProcID=$RowCountryWiseData['ProcessorID'];
					$RowCountryWiseData['Sno']=++$Sno;		
					$RowCountryWiseData['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);	
					$ArrayPaymentValues=$this->getPaymentValues($PoID);	
                   $RowCountryWiseData['TotalInvoiceValue']=$ArrayPaymentValues['InvValueUSD'];				
					$AryRowCountryWiseData[] = $RowCountryWiseData;
				}
				
				
				//$data['Link']="AddCollection?PIID=";
				$data['Data']=$AryRowCountryWiseData;
	
	if($StmtCountryWiseData){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}
function getPortWisePoData(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 //$BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	$PortID=$IpData['PortID'];
$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	
$PQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}

if($PortID!=""){
	$PQuery.='and POD.PortOfDischarge='.$PortID;
}



if($Year!=''){
	$Error=false;
}
	
	                                                
	
	if(!$Error){
		 $SqlFields='POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		POD.PoDate,
		POD.ShipByDate,
		SCD.ConsigneeName,
		SPC.Name as PortOfDischarge,
		SCL.name as DestinationCountry,
		SSDB.ShippedOn,
		SSDB.ReturnedDate,
		
		SSDB.DeliveredDate,
		SSDB.TransportedDate,
		SSDB.ProductionStartedDate,
		SIF.ETA_POD,
		COALESCE(DATEDIFF(SIF.ETA_POD,now())-1,0) as ETATransitDays,
		COALESCE(DATEDIFF(SIF.ETA_POD,SIF.ShippingOnboardDt),0) as TransitDays,
		SCI.CInvNo,
		SCI.ContainerNo,
		SCI.CInvDate';
		
	  $SqlPortWiseData="SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id
			left join ScrmShipmentDetailsBreif as SSDB on POD.PoID=SSDB.PiID
			left join ScrmISFDetails as SIF on 
			POD.PoID=SIF.PIID WHERE SSDB.TransportedDate!='' and SSDB.DeliveredDate=''
			and POD.Status not in (3,7)   $Query $SQuery  $SubQuery $PQuery order by POD.PoID desc";
			 
	$StmtPortWiseData = mysql_query($SqlPortWiseData,$this->db);
	
		while($RowPortWiseData = mysql_fetch_array($StmtPortWiseData,MYSQL_ASSOC)){
			
			
			
			if($RowPortWiseData['ReturnedDate']!=''){
			$CurrentStatus='Returned';
			$StatusColour='#90EE90';									
			}
			else if($RowPortWiseData['ClosedDate']!=''){ 
			$CurrentStatus='Closed';
			$StatusColour='#90EE90';
			} 
			else if($RowPortWiseData['DeliveredDate']!=''){
				$CurrentStatus='Delivered';
				$StatusColour='#FFFF8F';
				}
			else if($RowPortWiseData['ShippedOn']!=''){
				$CurrentStatus='OnBoard';
				$StatusColour='#ADD8E6';
				}
			else if($RowPortWiseData['TransportedDate']!=''){
				$CurrentStatus='Transported';
				$StatusColour='#A9A9A9';
                }	
			else if($RowPortWiseData['ProductionStartedDate']!=''){
				$CurrentStatus='Production';
				$StatusColour='#FA8072';}
			else{
					$CurrentStatus='Issued';
					$StatusColour='#FFFFFF';
	                }
				
					$InvNo=$RowPortWiseData['CInvNo'];
					$InvDate=$RowPortWiseData['CInvDate'];
					$ExpID=$RowPortWiseData['ExporterID'];
					$ProcID=$RowPortWiseData['ProcessorID'];
					$RowPortWiseData['CurrentStatus']=$CurrentStatus;
					$RowPortWiseData['StatusColour']=$StatusColour;
					$RowPortWiseData['Sno']=++$Sno;		
					$RowPortWiseData['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);							
					$AryRowPortWiseData[] = $RowPortWiseData;
				}
				
				
				//$data['Link']="AddCollection?PIID=";
				$data['Data']=$AryRowPortWiseData;
	
	if($StmtPortWiseData){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}

function getShipingBillList(){
	$Error=true;
	
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 $BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];

$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}
if($BillsType=="0"){
	$IQuery=' SIFD.PIID not in (SELECT InvID FROM `ScrmShippingBillDetails`) ';
}else if($BillsType=="1"){
	$IQuery="SCI.CInvDate $DateFilter and SCI.PiID  in 
		(SELECT InvID FROM `ScrmShippingBillDetails`)";	
}

if($Year!=''){
	$Error=false;
}
	
	
	if(!$Error){
		$SqlFields='POD.PoID,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCD.ConsigneeName,
		SCI.CInvNo,
		SCOM.ShippingBillNo,
		SCI.CInvDate';
		
	 $SqlShipingBills="SELECT $SqlFields FROM  ScrmPoData as POD
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmShippingBillDetails as SCOM on POD.PoID=SCOM.InvID 
			WHERE $IQuery and POD.Status not in (3,7)   
			$Query $SQuery  $SubQuery order by SCOM.InvID desc";  
	
	$StmtShipingBills = mysql_query($SqlShipingBills,$this->db);
	
		while($RowShipingBill = mysql_fetch_array($StmtShipingBills,MYSQL_ASSOC)){
					$InvNo=$RowShipingBill['CInvNo'];
					$InvDate=$RowShipingBill['CInvDate'];
					$ExpID=$RowShipingBill['ExporterID'];
					$ProcID=$RowShipingBill['ProcessorID'];
					$RowShipingBill['Sno']=++$Sno;		
					$RowShipingBill['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);							
					$AryRowShipingBill[] = $RowShipingBill;
				}
				
				$data['Error']=null;
				$data['Msg']="Success";
				$data['Link']="ShippingBillForm?PoID=";
				$data['Data']=$AryRowShipingBill;
	
	if($StmtShipingBills){		
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}


function getShipBillDetails(){
	
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	$PoID=$IpData['PoID'];
	
	$SqlFeilds='SPD.ExporterID,SPD.ProcessorID, SCI.CInvNo,SCI.CInvDate,SCD.ConsigneeName,SPCL.Name AS LoadingPort,
SPCD.Name AS DischargePort,SCL.name AS CountryName,SCI.Freight,SCI.TotalNetWt,
SCI.Duty,SPBD.ShippingBillNo,SPBD.ShippingBillDate,SPBD.LEODate,SPBD.SBFOBINR,
SPBD.ExchangeRate as SBExchangeRate,
SPBD.SBFOBUSD,
SPBD.SBFreight
';
	
	 $SqlShippingBillDetails="SELECT $SqlFeilds
FROM `ScrmPoData` SPD
left join ScrmCustomInvoices SCI on SCI.PiID=SPD.PoID
left join ScrmConsigneeDetails SCD on SCD.ConID=SPD.ConsigneeID
left join ScrmPortCities SPCD on SPCD.PortId=SPD.PortOfDischarge
left join ScrmPortCities SPCL on SPCL.PortId=SPD.PortofLoading
left join ScrmCountriesList SCL on SCL.id=SPD.DestinationCountry
left join ScrmShippingBillDetails SPBD on SPBD.InvID=SPD.PoID
WHERE SPD.PoID=$PoID";
	
$PaymentData=$this->getPaymentValues($PoID);

	$StmtShippingBillDetails= mysql_query($SqlShippingBillDetails,$this->db);
	$RowShippingBillDetails = mysql_fetch_array($StmtShippingBillDetails,MYSQL_ASSOC);
					
					$InvNo=$RowShippingBillDetails['CInvNo'];
					$InvDate=$RowShippingBillDetails['CInvDate'];
					$ExpID=$RowShippingBillDetails['ExporterID'];
					$ProcID=$RowShippingBillDetails['ProcessorID'];
					
						
					$RowShippingBillDetails['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);							
					
				$RowShippingBillDetails['InvValueUSD']=$PaymentData['InvValueUSD'];
				$RowShippingBillDetails['FOBValueINR']=$PaymentData['FOBValueINR'];
				$RowShippingBillDetails['TotalMcs']=$PaymentData['TotalMcs'];


	
	if($StmtShippingBillDetails){
				$data['Error']=null;
				$data['Msg']="Success";
				$data['Data']=$RowShippingBillDetails;		
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}

}



function postShippingBillDetails(){
//echo "hi"; exit;
	//global $iconn;
	$Error=true;
	
	 $RawIpData = file_get_contents('php://input');
	 $IpData = json_decode($RawIpData,true);
	
	 $InvID=$IpData['InvID'];
	 $ShippingBillNo=$IpData['ShippingBillNo'];
	 $ShippingBillDate=$IpData['ShippingBillDate'];
	 $LEODate=$IpData['LEODate'];
	 $ExchangeRate=$IpData['ExchangeRate'];
	 $SBFOBUSD=$IpData['SBFOBUSD'];
	 $SBFOBINR=$IpData['SBFOBINR'];
	 $SBFreight=$IpData['SBFreight'];
	 $PostedUserId=$IpData['PostedUserId'];
	 $timezone = new DateTimeZone('Asia/Kolkata');
    $datetime = new DateTime('now', $timezone);
	 $PostedDateTime=date('Y-m-d H:i:s');
	 
	 $SqlCount="SELECT * FROM `ScrmShippingBillDetails` WHERE InvID='$InvID'";
	 /*$StmtCount=$iconn->query($SqlCount);
	 $RowCount=$StmtCount->fetch_array();*/
	 
	$StmtCount= mysql_query($SqlCount,$this->db);
	 //var_dump($StmtCount);
	$RowCount = mysql_fetch_array($StmtCount,MYSQL_ASSOC);
	 $IsEdit=$RowCount['IsEdit']+1;
	 $Count=mysql_num_rows($StmtCount);
	 if($Count==0){

$SqlInsert="INSERT INTO `ScrmShippingBillDetails`(`InvID`, `ShippingBillNo`, `ShippingBillDate`, `LEODate`, `ExchangeRate`, `SBFOBUSD`, `SBFOBINR`, `SBFreight`, `PostedUserId`, `PostedDateTime`, `IsEdit`) VALUES('$InvID','$ShippingBillNo','$ShippingBillDate','$LEODate','$ExchangeRate'
,'$SBFOBUSD','$SBFOBINR','$SBFreight','$PostedUserId','$PostedDateTime','0') ";
	 }
	 else{
		$SqlInsert=" UPDATE `ScrmShippingBillDetails` SET `ShippingBillNo`='$ShippingBillNo',`ShippingBillDate`='$ShippingBillDate',`LEODate`='$LEODate',`ExchangeRate`='$ExchangeRate',`SBFOBUSD`='$SBFOBUSD',`SBFOBINR`='$SBFOBINR',`SBFreight`='$SBFreight',`PostedUserId`='$PostedUserId',`PostedDateTime`='$PostedDateTime',`IsEdit`='$IsEdit'
		WHERE `InvID`='$InvID'";
	 }
	 //echo $SqlInsert; exit;
 $StmtInsert= mysql_query($SqlInsert,$this->db);
	if($StmtInsert){
				$data['Error']=null;
				$data['Msg']="Success";
				$records = array('Data' => $data);
	$this->response($this->json($records), 200);
				
	}else {
	$data['Error']="Unable To Post Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}

function getPendingInsuranceList(){
	$Error=true; 
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	$BillsType=$IpData['BillsType'];	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];

$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	

if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and POD.ConsigneeID='.$Consignee;
}

 $SqlPoIds="SELECT PoIDs FROM `ScrmInsuranceData`";
	$StmtPoIds=mysql_query($SqlPoIds,$this->db);
	while($RowPoIds= mysql_fetch_array($StmtPoIds,MYSQL_ASSOC)){
		$ArrayPoId[]=$RowPoIds['PoIDs'];
		$PoIDs=implode(',',$ArrayPoId);
	}

//echo $PoIDs; exit;

if($Country!=""){
	$SQuery='and POD.DestinationCountry='.$Country;
}
if($BillsType=="0"){
	if($PoIDs==''){
	$IQuery="";	
	}
	else{
	$IQuery=" and SCI.PiID not in ($PoIDs)";
	}
}else if($BillsType=="1"){
	$IQuery=" and SCI.PiID  in ($PoIDs)";	
}

if($Year!=''){
	$Error=false;
}
	
	
	if(!$Error){
		 $SqlFields='POD.PoID,
		SCI.CInvNo,
		SCI.ContainerNo,
		SIFD.OriginatingVessel,
		SIFD.ShippingOnboardDt,
		SPCL.Name as PortofLoading,
		SPCD.Name as PortOfDischarge,
		SCL.name as DestinationCountry,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCI.CInvDate
		';
		
	   $SqlInsuranceList="SELECT $SqlFields FROM ScrmCustomInvoices as SCI 
			left join ScrmPoData as POD on SCI.PiID=POD.PoID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
			left join ScrmShipmentDetailsBreif as SCDB on POD.PoID=SCDB.PiID 
			left join ScrmPortCities as SPCL on POD.PortofLoading=SPCL.PortId 
			left join ScrmPortCities as SPCD on POD.PortOfDischarge=SPCD.PortId 
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
			WHERE  POD.Status not in (3,7) and SCDB.TransportedDate!='' and SCI.CInvDate $DateFilter $IQuery
			$Query $SQuery  $SubQuery order by CommercialID desc"; 
	
	$StmtInsuranceList = mysql_query($SqlInsuranceList,$this->db);
	
		while($RowInsuranceList = mysql_fetch_array($StmtInsuranceList,MYSQL_ASSOC)){
					 $PoID=$RowInsuranceList['PoID'];
					$InvNo=$RowInsuranceList['CInvNo'];
					$InvDate=$RowInsuranceList['CInvDate'];
					$ExpID=$RowInsuranceList['ExporterID'];
					$ProcID=$RowInsuranceList['ProcessorID'];
					$RowInsuranceList['Sno']=++$Sno;
					$ArrayPaymentValues=$this->getPaymentValues($PoID);
					//$PaymentData=$this->getPaymentValues($PoID)['InvValueUSD'];

					$RowInsuranceList['CInvNo']=$this->getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID);	
					$RowInsuranceList['TotalInvoiceValue']=$ArrayPaymentValues['InvValueUSD'];						
					 $RowInsuranceList['InvoiceValuewithPercentage']=$InvoiceValuewithPercentage;
					$AryRowInsuranceList[] = $RowInsuranceList;
				}
				
				
				$data['Link']="AddNegotiation?PIID=";
				$data['Data']=$AryRowInsuranceList;
	
	if($StmtInsuranceList){	
                $data['Error']=null;
				$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}

function createInsuranceTemplate(){
//echo "hi"; exit;
	//global $iconn;
	$Error=true;
	
	 $RawIpData = file_get_contents('php://input');
	 $IpData = json_decode($RawIpData,true);
	
	 $PoIDs=$IpData['PoIDs'];
	 //$InsuranceNo=$IpData['InsuranceNo'];
	 $InsuranceType=$IpData['InsuranceType'];
	 $PostedUserId=$IpData['PostedUserId'];
	 $timezone = new DateTimeZone('Asia/Kolkata');
     $datetime = new DateTime('now', $timezone);
	 $PostedDateTime = $datetime->format('Y-m-d H:i:s');
	 
	 $SqlInsuranceCount="SELECT MAX(InsuranceNo) AS InsuranceNo FROM ScrmInsuranceData where InsuranceType='$InsuranceType'";
	 $StmtInsuranceCount=mysql_query($SqlInsuranceCount,$this->db);
	 $RowInsuranceCount=mysql_fetch_array($StmtInsuranceCount,MYSQL_ASSOC);
	 $Count=$RowInsuranceCount['InsuranceNo'];
	 $InsuranceNo=$Count+1;
	 $InsuranceNo = str_pad($InsuranceNo, 3, '0', STR_PAD_LEFT);
	
	$InsuranceFullNo=$InsuranceType.$InsuranceNo; 
	 
	 if($InsuranceType==1){
	 $InsuranceTypePercentage='0.4';//(1)rejection->0.4 and (0)transit->0.08
	 }else{
		$InsuranceTypePercentage='0.08'; 
	 }
	 $ExplodePoIds=explode(',',$PoIDs);
	 $ArrayCount=count($ExplodePoIds);
	
	 for($i=0;$i<$ArrayCount;$i++){
		  $PoID=$ExplodePoIds[$i];
	 $ArrayPaymentValues=$this->getPaymentValues($PoID);
	 $InvoiceValue=$ArrayPaymentValues['InvValueUSD'];
	 //$InvoiceValue=105000;
	 $ArrayInvoicevaluewithInsPercentage[]=(($InvoiceValue*10)/100)+$InvoiceValue;//Insurancepercentage=10%;
	 $ExcessValue=implode(',',$ArrayInvoicevaluewithInsPercentage);
	 }
	

  $SqlInsert="INSERT INTO `ScrmInsuranceData`(`PoIDs`, `InsuranceNo`,`InsuranceFullNo`, `InsuranceType`, `InsuranceTypePercentage`, `ExcessValue`, `PostedUserID`, `PostedTime`) VALUES ('$PoIDs','$InsuranceNo','$InsuranceFullNo','$InsuranceType','$InsuranceTypePercentage'
,'$ExcessValue','$PostedUserId','$PostedDateTime') ";
	 //}
	 
 $StmtInsert= mysql_query($SqlInsert,$this->db);
	if($StmtInsert){
				$data['Error']=null;
				$data['Msg']="Success";
				$records = array('Data' => $data);
	$this->response($this->json($records), 200);
				
	}else {
	$data['Error']="Unable To Post Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}



function getInsuranceTemplatesList(){
	$Error=true; 
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	
		 $SqlFields='
		 SID.InsuranceID,
		 SID.PoIDs,
		SID.InsuranceFullNo as InsuranceNo,
		SID.PostedTime as GeneratedTime';
		
	    $SqlInsuranceTemplatesList="SELECT $SqlFields FROM  ScrmInsuranceData as SID
			 order by SID.InsuranceID desc"; 
	
	$StmtInsuranceTemplatesList = mysql_query($SqlInsuranceTemplatesList,$this->db);
	
		while($RowInsuranceTemplatesList = mysql_fetch_array($StmtInsuranceTemplatesList,MYSQL_ASSOC)){
			$PoIDs=$RowInsuranceTemplatesList['PoIDs'];
			 $SqlCustomInvoice="SELECT CustomsInvNo FROM `ScrmCustomInvoices` 
			where PiID in ($PoIDs)";
			unset($ArrayInvoiceNo);
			$StmtCustomInvoice=mysql_query($SqlCustomInvoice,$this->db);
			while($RowCustomInvoice = mysql_fetch_array($StmtCustomInvoice,MYSQL_ASSOC)){
			 $ArrayInvoiceNo[]=$RowCustomInvoice['CustomsInvNo'];
			$InvoiceNo=implode(',',$ArrayInvoiceNo);
			
			}
			
					 
					$InvNo=$RowInsuranceTemplatesList['CustomsInvNo'];
					$RowInsuranceTemplatesList['Sno']=++$Sno;
					$RowInsuranceTemplatesList['InvoiceNo']=$InvoiceNo;
					$RowInsuranceTemplatesList['GeneratedTime']=date('d-m-Y H:i:s',strtotime($RowInsuranceTemplatesList['GeneratedTime']));
					
					 $AryRowInsuranceTemplatesList[] = $RowInsuranceTemplatesList;
				}
				
				
			
				$data['InsuranceTemplatesList']=$AryRowInsuranceTemplatesList;
	
	if($StmtInsuranceTemplatesList){	
                $data['Error']=null;
				$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	//}
}


function getInsuranceTemplateInvoicesList(){
	$Error=true; 
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	$InsuranceID=$IpData['InsuranceID'];
	
	$SqlPoIds="SELECT PoIDs FROM `ScrmInsuranceData` where InsuranceID='$InsuranceID' ";
	$StmtPoIds=mysql_query($SqlPoIds,$this->db);
	while($RowPoIds= mysql_fetch_array($StmtPoIds,MYSQL_ASSOC)){
		$ArrayPoId[]=$RowPoIds['PoIDs'];
		$PoIDs=implode(',',$ArrayPoId);
		
	}
		
	$SqlFields='
		 SCI.PiID,
		 SCI.CustomsInvNo as InvoiceNo';
		
	    $SqlInsuranceTemplateInvoicesList="SELECT $SqlFields FROM   ScrmCustomInvoices as SCI where  SCI.PiID in ($PoIDs)"; 
	
	$StmtInsuranceTemplateInvoicesList = mysql_query($SqlInsuranceTemplateInvoicesList,$this->db);
	
		while($RowInsuranceTemplateInvoicesList = mysql_fetch_array($StmtInsuranceTemplateInvoicesList,MYSQL_ASSOC)){
			 
					$RowInsuranceTemplateInvoicesList['Sno']=++$Sno;
					$AryRowInsuranceTemplateInvoicesList[] = $RowInsuranceTemplateInvoicesList;
				}
				
				
			
				$data['InsuranceTemplateInvoicesList']=$AryRowInsuranceTemplateInvoicesList;
	
	if($StmtInsuranceTemplateInvoicesList){	
                $data['Error']=null;
				$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	//}
}

function createPaymentsTemplate(){
//echo "hi"; exit;
	//global $iconn;
	$Error=true;
	
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	$InsuranceIDs=$IpData['InsuranceIDs'];
	$PostedUserId=$IpData['PostedUserId'];
	$Timezone = new DateTimeZone('Asia/Kolkata');
    $Datetime = new DateTime('now', $Timezone);
	$PostedDateTime = $Datetime->format('Y-m-d H:i:s');
	 //duplicate insurance add case is not validated 
	 $SqlInsurancePaymentCount="SELECT MAX(PaymentNo) AS PaymentNo FROM ScrmInsurancePayments ";
	 $StmtInsurancePayment=mysql_query($SqlInsurancePaymentCount,$this->db);
	 $RowInsurancePayment=mysql_fetch_array($StmtInsurancePayment,MYSQL_ASSOC);
	 $Count=$RowInsurancePayment['PaymentNo'];
	 $PaymentNo=$Count+1;
	 $PaymentNo = str_pad($PaymentNo, 3, '0', STR_PAD_LEFT);
	
	$PaymentFullNo='PAYMENT'.$PaymentNo; 
	 
	if($InsuranceIDs!=''){
   $SqlInsert="INSERT INTO `ScrmInsurancePayments`(`InsuranceID`,`PaymentNo`, `PaymentFullNo`, `PostedUserID`, `PostedDateTime`) VALUES ('$InsuranceIDs','$PaymentNo','$PaymentFullNo','$PostedUserId','$PostedDateTime') ";
	 $StmtInsert= mysql_query($SqlInsert,$this->db);

	}
	 
	if($StmtInsert){
				$data['Error']=null;
				$data['Msg']="Success";
				$records = array('Data' => $data);
	$this->response($this->json($records), 200);
				
	}else {
	$data['Error']="Unable To Post Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}


function PostPaymentsInsuranceData(){//to update data of payments insurance 

	$Error=true;
	
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	
	$PostedUserId=$IpData['PostedUserId'];
	$InsurancePaymentID =$IpData['InsurancePaymentID'];
	$PaymentDate =$IpData['PaymentDate'];
	$ReferenceNo =$IpData['ReferenceNo'];
	$OurReferenceNo=$IpData['OurReferenceNo'];
	$PaymentInsExchangeRate =$IpData['PaymentInsExchangeRate'];
	$Remarks=$IpData['Remarks'];
	$Amount =$IpData['Amount'];
	$Timezone = new DateTimeZone('Asia/Kolkata');
    $Datetime = new DateTime('now', $Timezone);
	$PostedDateTime = $Datetime->format('Y-m-d H:i:s');
	 
	
		$SqlInsert=" UPDATE `ScrmInsurancePayments` SET 
		`ReferenceNo`='$ReferenceNo',`OurReferenceNo`='$OurReferenceNo',`Amount`='$Amount',`PaymentDate`='$PaymentDate',`PaymentInsExchangeRate`='$PaymentInsExchangeRate',`Remarks`='$Remarks',`PostedUserID`='$PostedUserId',`PostedDateTime`='$PostedDateTime' WHERE `InsurancePaymentID`='$InsurancePaymentID'";
	
 $StmtInsert= mysql_query($SqlInsert,$this->db);
	if($StmtInsert){
				$data['Error']=null;
				$data['Msg']="Success";
				$records = array('Data' => $data);
	$this->response($this->json($records), 200);
				
	}else {
	$data['Error']="Unable To Post Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}

function DeleteInsurancePaymentsTemplate(){

	$Error=true;
	
	 $RawIpData = file_get_contents('php://input');
	 $IpData = json_decode($RawIpData,true);
	
	 $InsurancePaymentID=$IpData['InsurancePaymentID'];
	 $PostedUserId=$IpData['PostedUserId'];
	 $timezone = new DateTimeZone('Asia/Kolkata');
    $datetime = new DateTime('now', $timezone);
	 $PostedDateTime=date('Y-m-d H:i:s');
	 

  $SqlDelete="DELETE FROM `ScrmInsurancePayments` WHERE InsurancePaymentID='$InsurancePaymentID'";
  
 $StmtDelete= mysql_query($SqlDelete,$this->db);
	if($StmtDelete){
				$data['Error']=null;
				$data['Msg']="Success";
				$records = array('Data' => $data);
	$this->response($this->json($records), 200);
				
	}else {
	$data['Error']="Unable To Delete Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}
function getInsurancePaymentsList(){
	
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
		
	
		 $SqlFields='
		InsurancePaymentID,
		PaymentFullNo as PaymentFullNo,
		PostedDateTime as GeneratedTime,
		ReferenceNo';
		
	  $SqlPaymentTemplatesList="SELECT $SqlFields FROM `ScrmInsurancePayments` Order by PaymentNo asc"; 
	
	$StmtPaymentTemplatesList = mysql_query($SqlPaymentTemplatesList,$this->db);
	
	while($RowPaymentTemplatesList = mysql_fetch_array($StmtPaymentTemplatesList,MYSQL_ASSOC)){
		$RowPaymentTemplatesList['Sno']=++$Sno;
		$AryRowPaymentTemplatesList[] = $RowPaymentTemplatesList;
		

	}
		return $AryRowPaymentTemplatesList;

}


function getPendingInsurancePaymentsList(){
	$Error=true; 
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	$Year=$IpData['Year'];	
	
if($Year!=''){
	$Error=false;
}
	
	
	if(!$Error){
		 $SqlFields='
		 SID.InsuranceID,
		 POD.PoID,
		SID.PoIDs,
		SID.InsuranceFullNo as InsuranceNo
		';
		
	  $SqlPendingInsuranceList="SELECT $SqlFields FROM  ScrmInsuranceData as SID
			left join ScrmPoData as POD on POD.PoID in(SID.PoIDs)
			left join ScrmInsurancePayments as SIP on SIP.InsuranceID =SID.InsuranceID
			WHERE SID.InsuranceID not in (SELECT InsuranceID FROM `ScrmInsurancePayments`)  order by SID.InsuranceID desc"; 
	
	$StmtPendingInsuranceList = mysql_query($SqlPendingInsuranceList,$this->db);
	
		while($RowPendingInsuranceList = mysql_fetch_array($StmtPendingInsuranceList,MYSQL_ASSOC)){
			$PoIDs=$RowPendingInsuranceList['PoIDs'];
			 $SqlCustomInvoice="SELECT CustomsInvNo FROM `ScrmCustomInvoices` 
			where PiID in ($PoIDs)";
			$StmtCustomInvoice=mysql_query($SqlCustomInvoice,$this->db);
			while($RowCustomInvoice = mysql_fetch_array($StmtCustomInvoice,MYSQL_ASSOC)){
			 $ArrayInvoiceNo[]=$RowCustomInvoice['CustomsInvNo'];
			$InvoiceNo=implode(' , ',$ArrayInvoiceNo);
			
			}
			
					$RowPendingInsuranceList['Sno']=++$Sno;
					$RowPendingInsuranceList['InvoiceNo']=$InvoiceNo;
					$ArrayPaymentValues=$this->getPaymentValues($PoID);
					$AryRowPendingInsuranceList[] = $RowPendingInsuranceList;
				}
				
				$data['PendingPaymentsInsuranceList']=$AryRowPendingInsuranceList;
	            $data['PaymentsInsuranceTemplatesList']=$this->getInsurancePaymentsList();
	
	if($StmtPendingInsuranceList){	
                $data['Error']=null;
				$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	}
}
function getInsurancePaymentData(){
	$Error=true; 
	$RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	$InsurancePaymentID=$IpData['InsurancePaymentID'];	
	
$SqlInsPaymentFields='InsuranceID,
PaymentFullNo as PaymentNo,
ReferenceNo,
OurReferenceNo,
PaymentDate,
Remarks,
PaymentInsExchangeRate as ExchangeRate
';


    $SqlInsuranceIDs="SELECT $SqlInsPaymentFields  FROM `ScrmInsurancePayments` where InsurancePaymentID='$InsurancePaymentID'";
	$StmtInsuranceIDs=mysql_query($SqlInsuranceIDs,$this->db);
	$RowInsuranceIDs= mysql_fetch_array($StmtInsuranceIDs,MYSQL_ASSOC);
		$InsuranceIDs=$RowInsuranceIDs['InsuranceID'];
		$PaymentNo=$RowInsuranceIDs['PaymentNo'];
		
	 $SqlPoIDs="SELECT PoIDs,InsuranceFullNo as InsuranceNo,InsuranceTypePercentage,ExcessValue FROM `ScrmInsuranceData` where InsuranceID in ($InsuranceIDs)";
	$StmtPoIDs=mysql_query($SqlPoIDs,$this->db);
	unset($ArrayPoIDs);
	while($RowPoIDs= mysql_fetch_array($StmtPoIDs,MYSQL_ASSOC)){
		$PoIDs=$RowPoIDs['PoIDs'];
		$ExcessValue=explode(',',$RowPoIDs['ExcessValue']);
		//$PoIDs=implode(',',$ArrayPoIDs);
		
	//}

		 $SqlFields='POD.PoID,
		SCI.CustomsInvNo as CInvNo,
		SCI.ContainerNo,
		SIFD.OriginatingVessel,
		SIFD.ShippingOnboardDt,
		SPCL.Name as POL,
		SPCD.Name as POD,
		SCD.name as DestinationCountry,
		SCL.name as PortofLoadingCountry,
		POD.ExporterID,
		POD.ProcessorID,
		POD.PoNO,
		SCI.CInvDate
		';
		
	  $SqlPoDetails="SELECT $SqlFields FROM ScrmCustomInvoices as SCI 
			left join ScrmPoData as POD on SCI.PiID=POD.PoID
			left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
			left join ScrmPortCities as SPCL on POD.PortofLoading=SPCL.PortId 
			left join ScrmPortCities as SPCD on POD.PortOfDischarge=SPCD.PortId 
			left join ScrmCountriesList as SCD on POD.DestinationCountry=SCD.id 
			left join ScrmCountriesList as SCL on SPCL.Country=SCL.id 
			WHERE POD.PoID in ($PoIDs)  order by PoID asc"; 
	
	$StmtPoDetails = mysql_query($SqlPoDetails,$this->db);
	
	$TotalPremiumValue=0;
	$TotalInvoiceValuewithPercentage=0;
	$index=0;
		while($RowPoDetails = mysql_fetch_array($StmtPoDetails,MYSQL_ASSOC)){
					 $PoID=$RowPoDetails['PoID'];
					 $InsuranceNo=$RowPoIDs['InsuranceNo'];
					 $POL=$RowPoDetails['POL'];
					 $POD=$RowPoDetails['POD'];
					 $DestinationCountry=$RowPoDetails['DestinationCountry'];
					 $PortofLoadingCountry=$RowPoDetails['PortofLoadingCountry'];
					 
					 $PortofDischarge=$POD.','.$DestinationCountry;
					 $PortofLoading=$POL.','.$PortofLoadingCountry;
					 $InsuranceTypePercentage=$RowPoIDs['InsuranceTypePercentage'];
					
					$RowPoDetails['Sno']=++$Sno;
					$RowPoDetails['PaymentNo']=$PaymentNo;
					$RowPoDetails['InsuranceNo']=$InsuranceNo;
					$ArrayPaymentValues=$this->getPaymentValues($PoID);
					$TotalInvoiceValue=$ArrayPaymentValues['InvValueUSD'];
					$InvoiceValuewithPercentage=($TotalInvoiceValue*10)/100;//10 is static
					$PremiumValue=($TotalInvoiceValue*$InsuranceTypePercentage)/100;
					$TotalPremiumValue +=$PremiumValue;
					$TotalInvoiceValuewithPercentage +=$InvoiceValuewithPercentage;
					
					$RowPoDetails['TotalInvoiceValue']=$TotalInvoiceValue;	
					$RowPoDetails['PremiumValue']=$PremiumValue;	
					$RowPoDetails['PortofDischarge']=$PortofDischarge;	
					$RowPoDetails['PortofLoading']=$PortofLoading;	
					/*$RowPoDetails['InvoiceValuewithPercentage']=$InvoiceValuewithPercentage;*/	
					//$RowPoDetails['ExtraInvoiceValue']=$ExcessValue[$index];	
					$RowPoDetails['InvoiceValuewithPercentage']=$ExcessValue[$index];	
					
						
					
		
		$AryRowPoDetails[] = $RowPoDetails;
					
				}
		$RowPaymentDetails['ReferenceNo']=$RowInsuranceIDs['ReferenceNo'];
		$RowPaymentDetails['OurReferenceNo']=$RowInsuranceIDs['OurReferenceNo'];
		$RowPaymentDetails['PaymentDate']=$RowInsuranceIDs['PaymentDate'];
		$RowPaymentDetails['Remarks']=$RowInsuranceIDs['Remarks'];	
		$RowPaymentDetails['ExchangeRate']=$RowInsuranceIDs['ExchangeRate'];
		$RowPaymentDetails['TotalAmount']=$TotalPremiumValue;
		$RowPaymentDetails['TotalPremiumValue']=$TotalPremiumValue;
		$RowPaymentDetails['TotalInvoiceValuewithPercentage']=$TotalInvoiceValuewithPercentage;
		
				
}              
                $AryRowPaymentDetails[] = $RowPaymentDetails;
				$data['InsurancePaymentData']=$AryRowPaymentDetails;
				$data['InsuranceInvoicesDataList']=$AryRowPoDetails;
				
	
	if($StmtPoDetails){	
                $data['Error']=null;
				$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	

}

function DeleteInsuranceTemplate(){

	$Error=true;
	
	 $RawIpData = file_get_contents('php://input');
	 $IpData = json_decode($RawIpData,true);
	
	  $PoID=$IpData['PoID'];
	 $InsuranceID=$IpData['InsuranceID'];
	 $PostedUserId=$IpData['PostedUserId'];
	 $timezone = new DateTimeZone('Asia/Kolkata');
    $datetime = new DateTime('now', $timezone);
	 $PostedDateTime = $datetime->format('Y-m-d H:i:s');
	 
if($PoID==''){
  $SqlDelete="DELETE FROM `ScrmInsuranceData` WHERE InsuranceID='$InsuranceID'";
  
}	
else{
	$SqlInsuranceData="SELECT PoIDs FROM `ScrmInsuranceData` WHERE InsuranceID='$InsuranceID'";
	$StmtInsuranceData=mysql_query($SqlInsuranceData,$this->db);
	$RowInsuranceData=mysql_fetch_array($StmtInsuranceData,MYSQL_ASSOC);
	$ArrayPoIDs=explode(',',$RowInsuranceData['PoIDs']);
   $UpdatedPoIds = implode(',',array_diff($ArrayPoIDs, array($PoID)));


	$SqlDelete="UPDATE `ScrmInsuranceData` SET `PoIDs`='$UpdatedPoIds',`PostedUserID`='$PostedUserId',`PostedTime`='$PostedDateTime' WHERE `InsuranceID`='$InsuranceID'";
}
	 
 $StmtDelete= mysql_query($SqlDelete,$this->db);
	if($StmtDelete){
				$data['Error']=null;
				$data['Msg']="Success";
				$records = array('Data' => $data);
	$this->response($this->json($records), 200);
				
	}else {
	$data['Error']="Unable To Delete Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}

function getPendingContracts(){
	
	 $RawIpData = file_get_contents('php://input');
	
	$IpData = json_decode($RawIpData,true);	
	$Year=$IpData['Year'];	
	$Exporter=$IpData['Exporter'];	
	$Processor=$IpData['Processor'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	
$Query='';	
$SQuery='';	

if($Exporter!=""){
	$SQuery.='and SPD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SQuery.='and SPD.ConsigneeID='.$Consignee;
}
if($Country!=""){
	$SQuery.='and SPD.DestinationCountry='.$Country;
}

if($Processor!=""){
	$SQuery.='and SPD.ProcessorID='.$Processor;
}


if($Year!=''){
	$Error=false;
}


 $SqlFields='
		SPD.PoID,
        SPD.ShipByDate,
		
		SCD.ConsigneeName,
		SPCD.Name as PortOfDischarge,
		SCL.name as DestinationCountry,
		SPD.ExporterID,
		SPD.ProcessorID,
		SPD.PoNO
		';




	  $SqlInsuranceList="SELECT  $SqlFields  FROM ScrmPoData as SPD 
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmPortCities as SPCD on SPCD.PortId=SPD.PortOfDischarge
					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
					 WHERE SDB.TransportedDate=''  
					 $SQuery order by PoID desc"; 
	
	$StmtInsuranceList = mysql_query($SqlInsuranceList,$this->db);
	
		while($RowPendingContracts = mysql_fetch_array($StmtInsuranceList,MYSQL_ASSOC)){
		$ExpID=$RowPendingContracts['ExporterID'];
		$ProcID=$RowPendingContracts['ProcessorID'];
		$AryShortCodes=$this->getShortInvShortCodes($ExpID,$ProcID);
		$Exp_Proc=$AryShortCodes['ExpShortCode'].''.$AryShortCodes['ApprovalNo'];
		$RowPendingContracts['Sno']=++$C;
		$RowPendingContracts['ExpProc']=$Exp_Proc;
		$RowPendingContracts['Total']=$Total;
		$RowPendingContracts['Payment']=$Payment;
		$RowPendingContracts['BuyerSample']=$BuyerSample;
		$RowPendingContracts['LabSample']=$LabSample;
		//$AryRowInsuranceList[]=$RowPendingContracts;
		$AryRowInsuranceList=$this->getPoBreakUP($AryRowInsuranceList,$RowPendingContracts,$RowPendingContracts['PoID']);

		
	
		

		
		}
		
		$data['Error']=null;
				$data['Msg']=$C;
				$data['ExcelLink']="#";
				$data['PdfLink']="#";
				$data['Data']=$AryRowInsuranceList;
	
	if($StmtShipingBills){		
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']=null;	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
}

function getPoBreakUp($AryData,$AryRow,$PoID){
	
	$AryG1['GradeName']="G1";$AryG1['GradeValue']="6/8";
	$AryG2['GradeName']="G2";$AryG2['GradeValue']="8/12";
	$AryG3['GradeName']="G3";$AryG3['GradeValue']="13/15";
	$AryG4['GradeName']="G4";$AryG4['GradeValue']="16/20";
	$AryG5['GradeName']="G5";$AryG5['GradeValue']="21/25";
	$AryG6['GradeName']="G6";$AryG6['GradeValue']="26/30";
	$AryG7['GradeName']="G7";$AryG7['GradeValue']="31/35";
	$AryG8['GradeName']="G8";$AryG8['GradeValue']="31/40";
	$AryG9['GradeName']="G9";$AryG9['GradeValue']="41/50";
	$AryG10['GradeName']="G10";$AryG10['GradeValue']="51/60";
	$AryG11['GradeName']="G11";$AryG11['GradeValue']="61/70";
	$AryG12['GradeName']="G12";$AryG12['GradeValue']="71/90";
	$AryG13['GradeName']="G13";$AryG13['GradeValue']="91/110";
	$AryG14['GradeName']="G14";$AryG14['GradeValue']="100/150";
	$AryG15['GradeName']="G15";$AryG15['GradeValue']="150/200";
	$AryG16['GradeName']="G16";$AryG16['GradeValue']="200/250";
	
	$AryGrades = array (
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
  $AryG16);



	
$SqlFields="PGD.GradeGroup,SPGD.Grade,SPGD.Mcs,SPGD.FreezingType,SPGD.GlazeType,
SPGD.GlazePerc,SPGD.Treatment,SPGD.Brand,SPGD.BuyerItemCode,
APS.PackingStyle,SPVL.ShortName";


 $SqlPoBreakUp="SELECT $SqlFields FROM `ScrmPoGradeData`
 as SPGD 
left JOIN ScrmPIVarietyList as SPVL on SPGD.VarietyID=SPVL.VarietyID
LEFT join AdminPackingStyles as APS On APS.PCKID=SPGD.PackingStyleID
LEFT join ProductionGrades as PGD On PGD.value=SPGD.Grade
WHERE InvId='$PoID' group by SPGD.ID;";

	$StmtSqlPoBreakUp = mysql_query($SqlPoBreakUp,$this->db);
	$IsGradeAval=$StmtPoBreakUp->num_rows;
			if($IsGradeAval==0){
			  $AryData[]=$AryRow;
			   }
	$C=0;
		while($RowSqlPoBreakUp = mysql_fetch_array($StmtSqlPoBreakUp,MYSQL_ASSOC)){
		if($C==0){
		$RowPendingContracts['ExpProc']=$Exp_Proc;
		$RowPendingContracts['Total']=$Total;
		$RowPendingContracts['Payment']=$Payment;
		$RowPendingContracts['BuyerSample']=$BuyerSample;
		$RowPendingContracts['LabSample']=$LabSample;
	
		}
		
		$AryRow['Brand']=$RowSqlPoBreakUp['Brand'];
		$AryRow['Variety']=$RowSqlPoBreakUp['ShortName'];
		$AryRow['Form']=$RowSqlPoBreakUp['FreezingType'];
		$AryRow['Packing']=$RowSqlPoBreakUp['PackingStyle'];
		$AryRow['Glaze']=$RowSqlPoBreakUp['GlazeType'];
		$Mcs=$RowSqlPoBreakUp['Mcs'];
		$GradeGroup=trim($RowSqlPoBreakUp['GradeGroup']);
		$Grade=$RowSqlPoBreakUp['Grade'];
		
		foreach($AryGrades as $Item){
			 $GradeName=$Item['GradeName'];
			  $GradeRef=$Item['GradeValue'];
			 
			if($GradeRef == $Grade
			&& $GradeRef == $GradeGroup
			){
				
			$AryRow[$GradeName]=$Mcs;	
			}else{
			$AryRow[$GradeName]='';	
			}
			
		}
		$AryData[]=$AryRow;
	
		$C++;
	    
		}
		
		
	return $AryData;

}






/************Reports****************/

function getEtd_EtaReport(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	
	 $FromDate=$IpData['FromDate'];	
	$ToDate=$IpData['ToDate'];	
	$Exporter=$IpData['Exporter'];	
	$Processor=$IpData['Processor'];	
	$EtdEta=$IpData['EtdEta'];
	
$Query='';	
$SubQuery='';
$SQuery='';	


if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Processor!=""){
	$SubQuery='and POD.ProcessorID='.$Processor;
}
if($EtdEta!=""){
	$SQuery='and POD.DestinationCountry='.$EtdEta;
}

$DateFilter= "SCI.CInvDate between '$FromDate' and '$ToDate'";

		 $SqlFields='ConsigneeName,POD.ExporterID,SCI.PiID,ISF.ContainerNo ,PoNo,COALESCE(DATEDIFF(EstimatedETA,BLDate),0) as ETADays ,ProcessorID ,BLNo,CustomsInvNo as InvNo,
 SPC.Name as Destination,SCL.name as DestinationCountry,ShippingOnboardDt,ETA_POD,
 DeliveryTerms as CYSD,LinerName,ContainerGateOutDate,EmptyContainerReturnDate';
		
	  $SqlEtaEtdReport="SELECT $SqlFields
 from  ScrmCustomInvoices  SCI 
left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
left join ScrmCountriesList  SCL on SCL.id=POD.DestinationCountry
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID
left join ScrmFdaDetails  SFD on SFD.PoID=POD.PoID
WHERE  $DateFilter   $Query $SubQuery  $SQuery and SFD.EmptyContainerReturnDate!='' order by POD.PoID desc";
			 
	
	$StmtEtaEtdReport = mysql_query($SqlEtaEtdReport,$this->db);
	
		while($RowEtaEtdReport = mysql_fetch_array($StmtEtaEtdReport,MYSQL_ASSOC)){
			
$PoID=$RowEtaEtdReport['PiID'];
$ProcessorID=$RowEtaEtdReport['ProcessorID'];
$ProcessorData=$this->getExporterDetails($ProcessorID );
$ProcessorApprovalNo=$ProcessorData['ExpApprovalNo'];
$FinalDestination=$RowEtaEtdReport['Destination'].',<br>'.$RowEtaEtdReport['DestinationCountry'];
$ShippingOnboardDt =$this->getFormatedDatelines($RowEtaEtdReport['ShippingOnboardDt']);
$EtaDate = $this->getFormatedDatelines($RowEtaEtdReport['ETA_POD']);
$GateOutFull = $this->getFormatedDatelines($RowEtaEtdReport['ContainerGateOutDate']);
$EmptyCYDate = $this->getFormatedDatelines($RowEtaEtdReport['EmptyContainerReturnDate']);
			
			$SqlProduct="SELECT ProductDescription  FROM `ScrmPoGradeData` as SPD
left join ScrmPIVarietyList as SPV on SPV.VarietyID=SPD.VarietyID
  WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand  Order by SPD.ID asc"; 
			$StmtProduct=mysql_query($SqlProduct,$this->db);
			unset($AryProductDescription);
			
			while($RowProduct=mysql_fetch_array($StmtProduct,MYSQL_ASSOC)){
				
			$AryProductDescription[]=$RowProduct['ProductDescription'];
			
			$ProductDescription=implode(',<br>',$AryProductDescription);

			}
			
					$RowEtaEtdReport['Sno']=++$Sno;		
					$RowEtaEtdReport['Product']=$ProductDescription;		
					$RowEtaEtdReport['Processor']=$ProcessorApprovalNo;		
					$RowEtaEtdReport['ShippingOnboardDt']=$ShippingOnboardDt;$RowEtaEtdReport['EtaDate']=$EtaDate;		
					$RowEtaEtdReport['FinalDestination']=$FinalDestination;		
					$RowEtaEtdReport['EmptyCYDate']=$EmptyCYDate;
					$RowEtaEtdReport['GateOutFull']=$GateOutFull;		
					$RowEtaEtdReport['TransitDays']=$TransitDays;
					$RowEtaEtdReport['TransitDaysasperLiner']=$TransitDaysasperLiner;
					$RowEtaEtdReport['EntryNumber']=$EntryNumber;		
					$AryRowEtaEtdReport[] = $RowEtaEtdReport;
				}
				
				$data['Data']=$AryRowEtaEtdReport;
	
	if($StmtEtaEtdReport){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	//}
}

function PoList(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 $Year=$IpData['year'];	
	$Type=$IpData['Type'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	$InvType=$IpData['InvType'];
	
$Query='';
$SubQuery='';
$SQuery='';
$IQuery='';	
if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and SCD.ConID='.$Consignee;
}

if($Country!=""){
	$SQuery='and SCL.id='.$Country;
}
if($InvType=="0"){

$IQuery=' POD.PoID  not in (SELECT PiID FROM `ScrmCustomInvoices` )';
}

else if($InvType=="1"){
//	echo 'checkkk';
$DateStart=$Year.'-04-01';
$DateEnd=($Year+1).'-03-31';
$DateFilter= "between '$DateStart' and '$DateEnd'";

$IQuery=" SCI.CInvDate $DateFilter and POD.PoID  in 
(SELECT PiID FROM `ScrmCustomInvoices` )";	
}

			$sql="SELECT PoID,CInvNo,StockOutDate,CInvDate,name,PoNO,PoDate,ConsigneeName,POD.ExporterID as ExporterID 
			FROM ScrmPoData as POD 
			left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
			left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
			left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
			WHERE $IQuery  $SubQuery $SQuery order by SCI.CInvID Asc" ; 
				$StmtEtaEtdReport = mysql_query($sql,$this->db);
				$i='0';
					while($row = mysql_fetch_array($StmtEtaEtdReport,MYSQL_ASSOC)){
						
								$ExporterID = $row['ExporterID'];
			$PoID = $row['PoID'];
			$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 25, "...");
			$PoNumber=mb_strimwidth($row['PoNO'], 0, 15, "...");
			$PoNumber=$row['PoNO'];
			$ConsigneeName=$row['ConsigneeName'];

			if($row['StockOutDate']!=''){
			$PoStatus="Stock Out";
			$StockOutDate=date_create($RowRealization['StockOutDate']);

			$PoStatus=date_format($StockOutDate,"d-m-Y");
			$CLR="Green";
			}else{
			$PoStatus='Pending';
			$CLR="red";
			}

					$RowEtaEtdReport['Sno']=++$Sno;		
					$RowEtaEtdReport['ID']=$row['PoID'];		
					$RowEtaEtdReport['InvNo']='doubt';	
$CInvDate=date_create($row['CInvDate']);
	$RowEtaEtdReport['CInvDate']= date_format($CInvDate,"d-m-Y");					
					$RowEtaEtdReport['PoNo']=$PoNumber;		
					$PoDate=date_create($row['PoDate']);
	$RowEtaEtdReport['PoDate']= date_format($PoDate,"d-m-Y");					
					$RowEtaEtdReport['Country']=$row['name'];		
					$RowEtaEtdReport['Buyer']=$ConsigneeName;
					$RowEtaEtdReport['PInvNo']='doubt';
					$RowEtaEtdReport['StockStatus ']=$PoStatus;
$RowEtaEtdReport['CLR ']=$CLR;		
										
					$AryRowEtaEtdReport[] = $RowEtaEtdReport;
				}
				
				$data['PoListData']=$AryRowEtaEtdReport;
	
	if($StmtEtaEtdReport){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	//}
}
function getPIInvoiceNumber($iconn,$ExpID,$PoID){
  $ExporterData=getExporterDetails($ExpID);
  $InvShortCode=$ExporterData['InvShortCode'];
  $InvDateFormat=$ExporterData['InvDateFormat'];

 $CustomInvoiceExec="SELECT * FROM ScrmPIData   WHERE PoID='$PoID'";
 $StmtPIListDetails = mysql_query($CustomInvoiceExec,$this->db);
	
$CustomInvoiceRow = mysql_fetch_array($StmtPIListDetails,MYSQL_ASSOC);
	
$CurrentMonth = date('n',strtotime($CustomInvoiceRow['InvDate']));   
if($InvDateFormat=='Short'){		
if($CurrentMonth<=3){
 $NxtYear = date("y",strtotime($CustomInvoiceRow['InvDate']));  	
 $CurrentYear = date("y",strtotime($CustomInvoiceRow['InvDate']))-1;  	
} 
else{ 
$CurrentYear = date("y",strtotime($CustomInvoiceRow['InvDate']));  	 
$NxtYear = date("y",strtotime($CustomInvoiceRow['InvDate']))+1;  	
 
}
}else{	

if($CurrentMonth<=3){
 $NxtYear = date('y',strtotime($CustomInvoiceRow['InvDate']));  	
 $CurrentYear = date('y',strtotime($CustomInvoiceRow['InvDate']))-1;  		
}else{ 
$CurrentYear = date('y',strtotime($CustomInvoiceRow['InvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['InvDate']))+1;  	
  
}
}
 
 return $InvShortCode.'PI/'.$CustomInvoiceRow['InvNo'].'/'.$CurrentYear.'-'.$NxtYear; 
  
	
}
function SFDetails(){
	$Error=true; 
	 $RawIpData = file_get_contents('php://input');
	$IpData = json_decode($RawIpData,true);
	 $Year=$IpData['year'];	
	$Type=$IpData['Type'];	
	$Exporter=$IpData['Exporter'];	
	$Consignee=$IpData['Consignee'];	
	$Country=$IpData['Country'];
	$InvType=$IpData['InvType'];
	
$Query='';
$SubQuery='';
$SQuery='';
$IQuery='';	
if($Exporter!=""){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}
if($Consignee!=""){
	$SubQuery='and SCD.ConID='.$Consignee;
}

if($Country!=""){
	$SQuery='and SCL.id='.$Country;
}
if($InvType=="0"){

$IQuery=' POD.PiID  not in (SELECT PiID FROM `ScrmISFDetails` )';
}

else if($InvType=="1"){
//	echo 'checkkk';
$DateStart=$Year.'-04-01';
$DateEnd=($Year+1).'-03-31';
$DateFilter= "between '$DateStart' and '$DateEnd'";

$IQuery=" SCI.CInvDate $DateFilter and POD.PoID  in 
(SELECT PiID FROM `ScrmISFDetails` )";	
}

		$sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID FROM 
 ScrmPoData as POD
 left join ScrmISFDetails as ISF on ISF.PIID=POD.PoID
left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
WHERE $IQuery and POD.Status not in (3,7)    
$Query $SQuery  $SubQuery order by ISF.PIID desc";  
		$StmtEtaEtdReport = mysql_query($sql,$this->db);
				$i='0';
					while($row = mysql_fetch_array($StmtEtaEtdReport,MYSQL_ASSOC)){
						print_r($row);
						exit;
							$PO_TIME_STAMP = strtotime($row['DO_SAMPLING']);
							$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);

							if($DO_SAMPLING=='01-01-1970'){
								$DO_SAMPLING='*****';
							}

							$PO_TIME_STAMP = strtotime($row['InvDate']);
							$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);
							 
							$PODate = strtotime($row['PoDate']);
							$PO_Date = date("d-m-Y", $PODate);

							$CInvDate = strtotime($row['CInvDate']);
							$CInvDate = date("d-m-Y", $CInvDate);

							if($PO_Date=='01-01-1970'){
								$PO_Date='*****';
							} 

							if($INVOICE_DATE=='01-01-1970'){
								
								$INVOICE_DATE='****';
							}

							if($CInvDate=='01-01-1970'){
								$CInvDate='*****';
							} 

							if($CInvDate=='01-01-1970'){
								
								$CInvDate='****';
							}

							//$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 30, "...");
							$ConsigneeName=$row['ConsigneeName'];

		
							//uma	
								if($row['IsfID']==''){
									$PoStatus='Pending';
									$CLR='red';
								}else if($row['IsfID']!=''){
									$PoStatus='Completed';
									$CLR='#008000';
								}
								

                             $PoID=$row['PoID'];


					$RowEtaEtdReport['Sno']=++$Sno;		
					$RowEtaEtdReport['ID']=$row['PoID'];		
					$RowEtaEtdReport['InvNo']='doubt';	
	$RowEtaEtdReport['CInvDate']= $CInvDate;					
					$PoDate=date_create($row['PoDate']);
	$RowEtaEtdReport['PoDate']= date_format($PoDate,"d-m-Y");	
                    	$RowEtaEtdReport['PoNo']=$row['PoNo'];		
					
					$RowEtaEtdReport['Country']=$row['CountryName'];
					
					$RowEtaEtdReport['Buyer']=$ConsigneeName;
					$RowEtaEtdReport['PInvNo']='doubt';
					$RowEtaEtdReport['StockStatus ']=$PoStatus;
$RowEtaEtdReport['CLR ']=$CLR;		
										
					$AryRowEtaEtdReport[] = $RowEtaEtdReport;
				}
				
				$data['SFdetailsData']=$AryRowEtaEtdReport;
	
	if($StmtEtaEtdReport){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	//}
}
function GetPIListDetails(){
	
	
	    $Error=false; 
        $RawIpData = file_get_contents('php://input');
        $IpData = json_decode($RawIpData,true);
	
	
	    $Year=$IpData['year'];
	    $EXPORTER=$IpData['Exporter'];
		$Consignee=$IpData['Consignee'];	
        $Country=$IpData['Country'];
$Query='';	
	

if($EXPORTER!=" "){
	$Query='and POD.ExporterID in ('.$EXPORTER.')';
}
if($Consignee!=""){
	echo 'checkk';
	$SubQuery='and SCD.ConID='.$Consignee;
}

if($Country!=""){
		//echo 'checkk';

	$SQuery='and SCL.id='.$Country;
}
$DateStart=$Year.'-04-01';
$DateEnd=($Year+1).'-03-31';

//$DateFilter="BETWEEN '$DateStart' and '$DateEnd'";
	
	
	if(!$Error){
	
	$sqlGetPIListDetails ="SELECT *,SCL.name as CountryName,POD.ExporterID as ExporterID,SSC.Name as PoStatus
 FROM ScrmPIData as POD 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
WHERE POD.PoID not in (SELECT PiID FROM `ScrmCustomInvoices`)    
$Query $SubQuery $SQuery  order by InvNo asc ";  
	
	$StmtPIListDetails = mysql_query($sqlGetPIListDetails,$this->db);
	
	  $i=1;
		while($RowRealization = mysql_fetch_array($StmtPIListDetails,MYSQL_ASSOC)){
			
			$ExporterID = $row['ExporterID'];
$PO_TIME_STAMP = strtotime($row['DO_SAMPLING']);
$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);

if($DO_SAMPLING=='01-01-1970'){
	$DO_SAMPLING='*****';
}

$PO_TIME_STAMP = strtotime($row['InvDate']);
$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);
 
$PODate = strtotime($row['PoDate']);
$PO_Date = date("d-m-Y", $PODate);

$CInvDate = strtotime($row['CInvDate']);
$CInvDate = date("d-m-Y", $CInvDate);

if($PO_Date=='01-01-1970'){
	$PO_Date='*****';
} 

if($INVOICE_DATE=='01-01-1970'){
	
	$INVOICE_DATE='****';
}


	 $ArrayPIListDetails=array();
    
	 $ArrayPIListDetails['sno']=$i;
	$ArrayPIListDetails['ID']=$RowRealization['PoID'];
	$ArrayPIListDetails['Processor']=$this->getExporterDetails($RowRealization['ProcessorID'])['ExpApprovalNo'];
;
	$ArrayPIListDetails['P.InvNo']=$RowRealization['InvNumFull'];	
	
	
	
	$date=date_create($RowRealization['InvDate']);
	$ArrayPIListDetails['InvDate']= date_format($date,"d-m-Y");
	
	$ArrayPIListDetails['PoNo']=$RowRealization['PoNo'];
	$ArrayPIListDetails['ConsigneeName']=$RowRealization['ConsigneeName'];
	$date=date_create($RowRealization['ShipByDate']);
	$ArrayPIListDetails['ShipByDate']= date_format($date,"d-m-Y");

	$ArrayPIListDetails['Status']=$RowRealization['PoStatus'];
	
	
    $i++;
	$resp[]=$ArrayPIListDetails;
		}
	$data['GetPIListDetailsData']=$resp;
	
	if($sqlGetPIListDetails){	
    $data['Error']=null;
	$data['Msg']="Success";	
	$records = array('Data' => $data,);
	$this->response($this->json($records), 200);
	}else {
	$data['Error']="Unable To Fetch Data";	
	$records = array('Data' => $data);
	$this->response($this->json($records), 200);
	}
	
	
	}
		
}










/************Reports****************/

/***************Functions*****************/

function getInvoiceNumber($InvNo,$InvDate,$ExpID,$ProcID){

	$InvoiceNo='--'; 
	 $CurrentMonth = date('n',strtotime($InvDate));

	$AryInvCodes=$this->getShortInvShortCodes($ExpID,$ProcID);

	$ProcShortCode=$AryInvCodes['ProcShortCode'];
	$ExpShortCode=$AryInvCodes['ExpShortCode'];  

if($CurrentMonth<=3){
  $NxtYear = date("y",strtotime($InvDate));  	
 $CurrentYear = date("y",strtotime($InvDate))-1;  	
} 
else{ 
$CurrentYear = date("y",strtotime($InvDate));  	 
$NxtYear = date("y",strtotime($InvDate))+1;  	
 
}

$InvoiceNo=$ExpShortCode.$ProcShortCode.$CurrentYear.$NxtYear.'/'.$InvNo;



return $InvoiceNo; 
  
	
}


function getShortInvShortCodes($ExpID,$ProcID) {


 $SqlShortCodes="SELECT InvShortCode as ProcShortCode ,ExpApprovalNo as ApprovalNo,
(SELECT InvShortCode FROM `AdminExporterDetails` WHERE ExpID='$ExpID') as ExpShortCode
FROM `AdminExporterDetails` WHERE ExpID='$ProcID';";
$StmtShortCodes = mysql_query($SqlShortCodes,$this->db);

$RowShipingBill = mysql_fetch_array($StmtShortCodes,MYSQL_ASSOC);


return $RowShipingBill;


}

function getFormatedDate($DATE){

$TIME_STAMP = strtotime($DATE);
 $formated_date = date("d-m-Y", $TIME_STAMP);
 if($formated_date=='01-01-1970'){
	$formated_date=''; 
 }
return $formated_date;

}

function getFinancialYear($FinClsYear){
$DateStart=$FinClsYear.'-04-01';
$DateEnd=($FinClsYear+1).'-03-31';

return $DateFilter="BETWEEN '$DateStart' and '$DateEnd'";

}

function getExporterDetails($Id) {

$SqlExporter="SELECT * FROM `AdminExporterDetails` WHERE ExpID='$Id'"; 
$StmtExporter=mysql_query($SqlExporter,$this->db);
$RowExporter=mysql_fetch_array($StmtExporter,MYSQL_ASSOC);

return $RowExporter;


}

function getFormatedDatelines($DATE){

$TIME_STAMP = strtotime($DATE);
 $formated_date = date("d-m-Y", $TIME_STAMP);
 if($formated_date=='01-01-1970'){
	$formated_date='--'; 
 }
return $formated_date;
}
/***************Functions*****************/

	
private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
			
			mysql_close($this->db);
			
			
		}
}
	// Initiiate Library
	$api = new API;
	$api->processApi();
?>