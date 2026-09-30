<?php 
session_start();
//include "DbTables.php";
include_once __DIR__.'/../config/Dotenv.php';
include_once __DIR__.'/../config/BaseUrl.php';
$BaseUrlConfig = new BaseUrl;
$BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
$BaseUrl = $BaseUrlConfig->protocol."://$BaseUrlConfig->host";

$Startyear='2022';
$endYear=date('Y');
$RootFolder=$_SERVER['DOCUMENT_ROOT']; 
$Provider="SilverLights Software Solutions";
$AppName="S-ERP";
$AppVersion='2.O';
$CopyRight="©".date('Y').  " - SilverLights Software Solutions";
$IncludesPath=$RootFolder."/partials/";


$ApiUrl=$BaseUrl.'/Api/ApiV1.php';
$PageTitleLink="index";
//$path=$_SERVER[REQUEST_URI]; 
 
 $info = new SplFileInfo($path);
 $PageExtension= $info->getExtension();  
if($PageExtension=='php'){
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $path=substr($path, 0, -4);
    header('Location: '.$current_url.$path);
}
}


$IsLogin= isset($IsLogin) ? $IsLogin:false;


if($IsDev==''){
	$IsDev=0;
}

if(!$IsLogin && $UserID==''){
	header('Location: '. $BaseUrl);
}


$db_name=(getenv('DB_NAME')) ? getenv('DB_NAME') : $_ENV['DB_NAME'];
$db_user=(getenv('DB_USER')) ? getenv('DB_USER') : $_ENV['DB_USER'];
$db_password=(getenv('DB_PASSWORD')) ? getenv('DB_PASSWORD') : $_ENV['DB_PASSWORD'];
$db_host=(getenv('DB_HOST')) ? getenv('DB_HOST') : $_ENV['DB_HOST'];



//echo $db_password;
  $iconn = mysqli_connect("$db_host","$db_user","$db_password","$db_name");

//  or die("DB Error!".mysqli_error()); 

if($BaseDist==''){
 $SqlValidate="SELECT * from AdminClientDetails order by ClientID Desc limit 1"; 
$StmtValidate=$iconn->query($SqlValidate);
$ValidateRow=$StmtValidate->fetch_array();
$BaseDist=$ValidateRow['ThemeUrl'];	
$AppLogo=$ValidateRow['AppLogoUrl'];	
$ClientName=$ValidateRow['Name'];	
$ClientLogo=$ValidateRow['LogoUrl'];	
$ClientBg=$ValidateRow['BGImageUrl'];

// if($BaseDist==''){
//  $BaseDist="https://serp.silverlights.in/Basewrapper/Nazox_v1.0/Admin/Vertical/dist/";	 
// }
$BaseDist=$BaseUrl."/assetsv2/";
}	





?>