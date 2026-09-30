<?php
include_once __DIR__.'/../config/Dotenv.php';
// $IsLogin=true;
// session_start() ;
// session_destroy() ;

// $_SESSION["IsLogin"]="LoginPage";
// include ($_SERVER['DOCUMENT_ROOT']."/SerpDbConfig/config.php");

// //$UserID=$UserID;

// $SqlLogin="SELECT UserID,UserName,AccessType,Ad.DeptID,SubDeptID,UnitID,IsDev,
// CDB.ClientID,DbHost,DbName,DbUserName,DbPassword,StartYear,
// (select ServerUrl from AdminConfigDetails order by ConfigID Desc limit 1) as CurrentServerUrl,
// Path as CurrentClientPath,
// Directory as CurrentDirectory,
// SideBarHeaderTbl,SideBarOptionTbl,LoginDtls.Status

// FROM AdminLoginDetails as LoginDtls
// left join AdminDepartments as Ad on Ad.DeptID=LoginDtls.DeptID
// left join AdminClientDbDetails as CDB on CDB.ClientID=LoginDtls.ClientID
// left join AdminRegisteredClients As ARC on ARC.ClientAccountID=LoginDtls.ClientID
// WHERE UserID='$UserID'"; 
 
 

// $StmtLogin=$iconn->query($SqlLogin);
// $RowLoginData=$StmtLogin->fetch_array();

// if($StmtLogin->num_rows==1){
	
// $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
// 		$CurrentDateTime=$dateTime->format("Y-m-d  H:i:s"); 
// 		$UserID=$RowLoginData["UserID"];
// 		$Status=$RowLoginData["Status"];
// 		if($Status==1){
		
	
// 		$sql_update="UPDATE AdminLoginDetails SET `LastLogin`='$CurrentDateTime'
// 		WHERE `UserID`='$UserID'";
		
// 		$up=$iconn->query($sql_update);
	
// 	$_SESSION["UserID"]=$RowLoginData["UserID"];
// 	$_SESSION["UserName"]=$RowLoginData["UserName"];
// 	$_SESSION["AccessType"]=$RowLoginData["AccessType"];
// 	$_SESSION["DeptID"]=$RowLoginData["DeptID"];
// 	$_SESSION["SubDeptID"]=$RowLoginData["SubDeptID"];
// 	$_SESSION["UnitID"]=$RowLoginData["UnitID"];
// 	$_SESSION["IsDev"]=$RowLoginData["IsDev"];
// 	$_SESSION["ClientID"]=$RowLoginData["ClientID"];
	
// 	$_SESSION["DbHost"]=$RowLoginData["DbHost"];
// 	$_SESSION["DbName"]=$RowLoginData["DbName"];
// 	$_SESSION["DbUserName"]=$RowLoginData["DbUserName"];
// 	$_SESSION["DbPassword"]=$RowLoginData["DbPassword"];
// 	$_SESSION["StartYear"]=$RowLoginData["StartYear"];
	
// 	$_SESSION["IncludePath"]=$RowLoginData["CurrentClientPath"].$RowLoginData["CurrentDirectory"];
// 	$_SESSION["ClientPath"]=$RowLoginData["CurrentServerUrl"].$RowLoginData["CurrentClientPath"].$RowLoginData["CurrentDirectory"]; 
// 	$_SESSION["SideBarPath"]=$RowLoginData["CurrentServerUrl"].$RowLoginData["CurrentClientPath"]; 
	
	
// 	$DbHost=$_SESSION["DbHost"];
// 	$DbName=$_SESSION["DbName"];
// 	$DbUserName=$_SESSION["DbUserName"];
// 	$DbPassword=$_SESSION["DbPassword"];
	
	

//  $iconn = mysqli_connect("$DbHost","$DbUserName","$DbPassword","$DbName") or die("DB Error!".mysqli_error()); 
 

	
	
// }
// }

//    $authConnection=getAuthDbConnection();	
//    getSessionDetails($authConnection,$UserID);

   class AuthConfig{
	private $data;
	public $conn;

	public function __construct(){

	}

	function getAuthDbConnection(){
// 		$DbHost = '193.202.44.54';
// 		$DbName = 'u681530362_serp';
// 		$DbUserName = 'u681530362_serp';
// 		$DbPassword = '@4pU3XSq@$';
		
		$DbHost = (getenv('DB_HOST')) ? getenv('DB_HOST') : $_ENV['DB_HOST'];
		$DbName = (getenv('DB_NAME')) ? getenv('DB_NAME') : $_ENV['DB_NAME'];
		$DbUserName = (getenv('DB_USER')) ? getenv('DB_USER') : $_ENV['DB_USER'];
		$DbPassword = (getenv('DB_PASSWORD')) ? getenv('DB_PASSWORD') : $_ENV['DB_PASSWORD'];

		
		$Authconn = mysqli_connect("$DbHost","$DbUserName","$DbPassword","$DbName") or die("DB Error!".mysqli_error()); 
//	  echo "hii";
	  
	  
		return $Authconn;
	  
	  }

	  function logData($Data){

		$Authconn=$this->getAuthDbConnection();

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$CurrentDateTime=$dateTime->format("Y-m-d  H:i:s"); 
		$LoginIP=$_SERVER['REMOTE_ADDR'];
		$ClientID=$_SESSION["ClientID"];
		$UserID=$_SESSION["UserID"];

		$url = $_SERVER['REQUEST_URI'];

$parts = parse_url($url);

$url = $parts['host'].$parts['path'];
if($Data!=''){
$url=$Data;
}



		 $sqlLog="INSERT INTO `AdminLoginAccessLog`(`UserID`, `ClientID`, `LoginDate`, `LoginIP`,`url`) 
		VALUES ('$UserID','$ClientID','$CurrentDateTime','$LoginIP','$url')";

		$StmtIpAccess=$Authconn->query($sqlLog);

		if($StmtIpAccess){
			//echo "hi";
		}else{
			//echo "fails";
		}

		//mysqli_close($Authconn);
	  }


	  function getSessionDetails($UserID){

		// session_start() ;
		// session_destroy() ;
		

		$Authconn=$this->getAuthDbConnection();
		
		  $SqlLogin="SELECT UserID,UserName,AccessType,Ad.DeptID,SubDeptID,UnitID,IsDev,
		CDB.ClientID,DbHost,DbName,DbUserName,DbPassword,StartYear,
		(select ServerUrl from AdminConfigDetails order by ConfigID Desc limit 1) as CurrentServerUrl,
		Path as CurrentClientPath,
		Directory as CurrentDirectory,
		SideBarHeaderTbl,SideBarOptionTbl,LoginDtls.Status
		
		FROM AdminLoginDetails as LoginDtls
		left join AdminDepartments as Ad on Ad.DeptID=LoginDtls.DeptID
		left join AdminClientDbDetails as CDB on CDB.ClientID=LoginDtls.ClientID
		left join AdminRegisteredClients As ARC on ARC.ClientAccountID=LoginDtls.ClientID
		WHERE UserID='$UserID'";  
		 
		 
		
		$StmtLogin=$Authconn->query($SqlLogin);
		$RowLoginData=$StmtLogin->fetch_array();
		
		if($StmtLogin->num_rows==1){
			
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
				$CurrentDateTime=$dateTime->format("Y-m-d  H:i:s"); 
				$UserID=$RowLoginData["UserID"];
				$Status=$RowLoginData["Status"];
				if($Status==1){
				
			
				$sql_update="UPDATE AdminLoginDetails SET `LastLogin`='$CurrentDateTime'
				WHERE `UserID`='$UserID'";
				
				$up=$Authconn->query($sql_update);
			
			$_SESSION["UserID"]=$RowLoginData["UserID"];
			$_SESSION["UserName"]=$RowLoginData["UserName"];
			$_SESSION["AccessType"]=$RowLoginData["AccessType"];
			$_SESSION["DeptID"]=$RowLoginData["DeptID"];
			$_SESSION["SubDeptID"]=$RowLoginData["SubDeptID"];
			$_SESSION["UnitID"]=$RowLoginData["UnitID"];
			$_SESSION["IsDev"]=$RowLoginData["IsDev"];
			$_SESSION["ClientID"]=$RowLoginData["ClientID"];
			
			$_SESSION["DbHost"]=$RowLoginData["DbHost"];
			$_SESSION["DbName"]=$RowLoginData["DbName"];
			$_SESSION["DbUserName"]=$RowLoginData["DbUserName"];
			$_SESSION["DbPassword"]=$RowLoginData["DbPassword"];
			$_SESSION["StartYear"]=$RowLoginData["StartYear"];
			
			$_SESSION["IncludePath"]=$RowLoginData["CurrentClientPath"].$RowLoginData["CurrentDirectory"];
			$_SESSION["ClientPath"]=$RowLoginData["CurrentServerUrl"].$RowLoginData["CurrentClientPath"].$RowLoginData["CurrentDirectory"]; 
			$_SESSION["SideBarPath"]=$RowLoginData["CurrentServerUrl"].$RowLoginData["CurrentClientPath"]; 
			
		}
		
		}

		return $_SESSION;
		}




   }

?>