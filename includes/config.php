<?php 
session_start(); 

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);




include_once ($_SERVER['DOCUMENT_ROOT']."/ApiV2/Config/Database.php"); 
include_once ("AuthConfig.php"); 
include_once ($_SERVER['DOCUMENT_ROOT']."/includes/LegacyAudit.php");
include_once __DIR__.'/../config/BaseUrl.php';
$BaseUrlConfig = new BaseUrl;
$BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
$BaseUrl = $BaseUrlConfig->protocol."://$BaseUrlConfig->host";

$database = new Database();
$authConfig = new AuthConfig();
$authConfig->logData("");

$authToken=$_SESSION["Token"]; 

if(isset($_GET['Token'])){
  $authToken=$_GET['Token'];
  $UserID=$_GET['UserID'];

  $_SESSION= $authConfig->getSessionDetails($UserID);  
}



$AryDbConfig = $database->getDbConfig($authToken); 

if ($AryDbConfig != null) {
  
   $DbHost = $AryDbConfig['AuthDbHost'];
  $DbName = $AryDbConfig['AuthDbName'];
  $DbUserName = $AryDbConfig['AuthDbUserName'];
  $DbPassword = $AryDbConfig['AuthDbPassword'];
  $iconn = mysqli_connect("$DbHost","$DbUserName","$DbPassword","$DbName") or die("Connection Error! Unable To Reach Server,Please Login Again !".mysqli_error()); 

  if(!$iconn){
    // echo "Config Error ";
    //  echo mysqli_error();
    // exit;
    header('Location:'.$BaseUrl.'/session-expaired');	
  }
}else{
  echo "Invalid Config Error ";
  header('Location:'.$BaseUrl.'/session-expaired');	
  exit;
}



$RootFolder=$_SERVER['DOCUMENT_ROOT']; 
$IncludesPath=$RootFolder."/partials/"; 
$cp=$RootFolder."/".$_SESSION["IncludePath"];  
 
 
$Startyear=$_SESSION["StartYear"]; 
$Currentyear=date('Y');
$endYear=$Currentyear+1;
$UserID=$_SESSION["UserID"];
$CurrentClient=$_SESSION["SideBarPath"];
$MailTempFolder=$RootFolder."/TempAutoMails/";
$MailReadTempFolder=$BaseUrl."/TempAutoMails/";






  $BaseDist=$BaseUrl."/assetsv2/";	

//  header('Location:'.$BaseUrl.'/maintance');	

if(!function_exists("getUserDetails")) {
   
function getUserDetails($VerifiedBy){
	
  global $authConfig;
  $Authconn = $authConfig->getAuthDbConnection();  
	
	
	$SqlVerifiedBy="SELECT * FROM `AdminLoginDetails` WHERE UserID='$VerifiedBy'";
	$StmtVerifiedBy=$Authconn->query($SqlVerifiedBy); 
	$RowVerifiedBy=$StmtVerifiedBy->fetch_array();
	$Verified=$RowVerifiedBy['UserName'];
  mysqli_close($Authconn);
	
	return $Verified;
	
}

}





?>
