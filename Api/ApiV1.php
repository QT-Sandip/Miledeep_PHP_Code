	<?php
header('Access-Control-Allow-Origin: *'); 
require_once("Rest.inc.php");
include_once __DIR__.'/../config/Dotenv.php';
/*
 ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_reporting(0);*/
	
	class API extends REST {
		
		const DB_SERVER = (getenv('DB_HOST')) ? getenv('DB_HOST') : $_ENV['DB_HOST']; 
		const DB_USER = (getenv('DB_USER')) ? getenv('DB_USER') : $_ENV['DB_USER'];
		const DB_PASSWORD = (getenv('DB_PASSWORD')) ? getenv('DB_PASSWORD') : $_ENV['DB_PASSWORD'];
		const DB = (getenv('DB_NAME')) ? getenv('DB_NAME') : $_ENV['DB_NAME'];

		private $db = NULL; 
		
	
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect(DB_SERVER,DB_USER,DB_PASSWORD,DB,true);					// Initiate Database connection
		}


		private function dbConnect($DB_SERVER,$DB_USER,$DB_PASSWORD,$DB,$IsAuthDb){
			
			if($IsAuthDb){
			$this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
			}else{
			$this->db = mysql_connect($DB_SERVER,$DB_USER,$DB_PASSWORD);
			}
		
			if($this->db){
				if($IsAuthDb){
				mysql_select_db(self::DB,$this->db);
				}else{
				mysql_select_db($DB,$this->db);
				}
			}
				
			
		}


			public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['f'])));
			
if((int)method_exists($this,$func) > 0){
				$this->$func();
		
}else{
				$this->response('',404);
}				
}
		
public function updateUserDetails(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
		
			$UserID = $data['userID'];
			$UserName = $data['username'];
			$UserRoleID=$data['role'];
			$PhoneNumber=$data['PhoneNumber'];
			$DesignationID=$data['designation'];
			$AccessIp=$data['AccessIp'];
			
			
		 $sqlUpdate="UPDATE `AdminLoginDetails` SET 
		`UserName`='$UserName',
		`DesignationID`='$DesignationID',
		`AccessIPs`='$AccessIp',
		`PhoneNumber`='$PhoneNumber',
		`UserRoleID`='$UserRoleID'
		WHERE `UserID`='$UserID'";
		$stmtUpdate = mysql_query($sqlUpdate,$this->db);	

		if($stmtUpdate){		
			
		$responseData['Token']=$Token='TokenDetails';
		$responseData['Msg']="Message";
		$responseData['Error']=null;
		}else{
		$responseData['Token']=$Token='TokenDetails';
		$responseData['Msg']="Message";
		$responseData['Error']='Unable To Update';
		}

		$this->response($this->json($responseData), 200);

}			



		
		
		
public function login(){
			
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			$UserLoginID = $data['UserLoginID'];
			$UserPassword = base64_encode($data['UserPassword']);
			$DeviceID=$data['DeviceID'];
			$AccessPin=$data['AccessPin'];
			
		  $sqlLogin="SELECT * FROM `AdminLoginDetails` as ALD
left join AdminRegisteredClients as ARC on ALD.ClientID=ARC.ClientAccountID
left join AdminClientDbDetails as ACDB on ACDB.ClientID=ALD.ClientID
left join AdminConfigDetails as ACD on 1=1
WHERE UserLoginID='$UserLoginID'";
		  $SqlCaLogin = mysql_query($sqlLogin); 
		  $rowLogin = mysql_fetch_array($SqlCaLogin,MYSQL_ASSOC);
		  //print_r($rowLogin);
			
			
		if(mysql_num_rows($sqlca_c_user) =='0'){
			$responseData = array('Error' => "No User Exist", "Msg" => "No User Exist Please Register");			
		}else if($rowLogin['Status']==0){
			$responseData = array('Error' => "User Access Blocked", "Msg" => "User Access Blocked");
		}else if($rowLogin['UserPwd']!=$UserPassword){
			$responseData = array('Error' => "User Id Or Password Incorrect", "Msg" => "User Id Or Password Incorrect");
		}else{
			
			
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));  
		$dateTime=$dateTime->format("Y-m-d  H:i:s"); 
			
		$responseData=$rowLogin;
		$UserID=$rowLogin['UserID'];
		
		$responseData['ApiUrl']=$rowLogin['ServerUrl'].$rowLogin['Path']."Api/ApiV1.php?f=";
		$Token['UserID']=$rowLogin['UserID'];
		$Token['DeptID']=$rowLogin['DeptID'];
		$Token['SubDeptID']=$rowLogin['SubDeptID'];
		$Token['UnitID']=$rowLogin['UnitID'];
		$Token['AccessType']=$rowLogin['AccessType'];
		$Token=$this->ConvertToJson($Token);
		$Token=$this->urlEncode64($Token);
		
		$responseData['Token']=$Token;
		$responseData['Msg']="Message";
		$responseData['Error']=null;

		$sqlUpdate="UPDATE `AdminLoginDetails` SET `DeviceID`='$DeviceID',`LastLogin`='$dateTime' WHERE `UserID`='$UserID'";
		$sqlca_update = mysql_query($sqlUpdate,$this->db);		
			
		
		}
		
		$this->response($this->json($responseData), 200);
} 


public function updateToken(){
	

	 $Token=$this->getToken(); 
	 $UserID=$Token['UserID'];
	
	if($UserID==''){
	$responseData['Msg']="Message";
	$responseData['Error']="Session Expaired ! Please Login";	
	$this->response($this->json($responseData), 200);
	}else if($this->SessionTime($UserID)){
	
	
	 $sqlLogin="SELECT * FROM `AdminLoginDetails` as ALD
left join AdminRegisteredClients as ARC on ALD.ClientID=ARC.ClientAccountID
left join AdminClientDbDetails as ACDB on ACDB.ClientID=ALD.ClientID
left join AdminConfigDetails as ACD on 1=1
WHERE UserID='$UserID'";
		  $SqlCaLogin = mysql_query($sqlLogin); 
		  $rowLogin = mysql_fetch_array($SqlCaLogin,MYSQL_ASSOC);
		//  print_r($rowLogin);
			
			
		if(mysql_num_rows($sqlca_c_user) =='0'){
			$responseData = array('Error' => "No User Exist", "Msg" => "No User Exist Please Register");			
		}else if($rowLogin['Status']==0){
			$responseData = array('Error' => "User Access Blocked", "Msg" => "User Access Blocked");
		}else{
			
			
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));  
		$dateTime=$dateTime->format("Y-m-d  H:i:s"); 
			
		$responseData=$rowLogin;
		$UserID=$rowLogin['UserID'];
		
		$responseData['ApiUrl']=$rowLogin['ServerUrl'].$rowLogin['Path']."Api/ApiV1.php?f=";
		$Token['UserID']=$rowLogin['UserID'];
		$Token['DeptID']=$rowLogin['DeptID'];
		$Token['SubDeptID']=$rowLogin['SubDeptID'];
		$Token['UnitID']=$rowLogin['UnitID'];
		$Token['AccessType']=$rowLogin['AccessType'];
		$Token=$this->ConvertToJson($Token);
		$Token=$this->urlEncode64($Token);
		
		$responseData['Token']=$Token;
		$responseData['Msg']="Message";
		$responseData['Error']=null;

		$sqlUpdate="UPDATE `AdminLoginDetails` SET `LastLogin`='$dateTime' WHERE `UserID`='$UserID'";
		$sqlca_update = mysql_query($sqlUpdate,$this->db);		
			
		
		}
		
		$this->response($this->json($responseData), 200);
	
	}else{
	$responseData['Msg']="Message";
	$responseData['Error']="Session Expaired ! Please Login";	
	$this->response($this->json($responseData), 200);
	}


}


public function getToken(){
	$Headers = apache_request_headers();
 	$Token=$Headers['Token'];
	$Token=$this->urlDecode64($Token);
	$Token=json_decode($Token,true);
	return $Token;
	
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
	
	if($TimeDiff < 2){
		return true;
	}else{
	//return false;
	return true;
	}
}


public function getUserList(){
	
	$json = file_get_contents('php://input');
	$data = json_decode($json,true);
	$ClientID=$data['ClientID'];
	
	 $SqlSession="SELECT * FROM `AdminLoginDetails` as ALD 
LEFT join AdminDepartments as AD on ALD.DeptID=AD.DeptID
LEFT join AdminRegisteredClients as ARC on ARC.ClientAccountID=ALD.ClientID where ALD.ClientID='$ClientID'";
	$StmtSession = mysql_query($SqlSession);
	while($RowData = mysql_fetch_array($StmtSession,MYSQL_ASSOC)){
	$RowData['UserPassword']='';	
	$Data[]=$RowData;	
	}
	

	$responseData['Error']=null;	
	$responseData['Data']=$Data;	
	$this->response($this->json($responseData), 200);
	
}
	

function urlEncode64($input) {
 return strtr(base64_encode($input), '+/=', '._-');
}

function urlDecode64($input) {
 return base64_decode(strtr($input, '._-', '+/='));
} 	


private function ConvertToJson($data){
			if(is_array($data)){
				return json_encode($data);
			}
			
		//	mysql_close($this->db);
			
			
		
}


	
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