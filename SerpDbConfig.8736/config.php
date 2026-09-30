<?php 
session_start();
//include "DbTables.php";

$Startyear='2022';
$endYear=date('Y');
$RootFolder=$_SERVER['DOCUMENT_ROOT']; 
$Provider="SilverLights Software Solutions";
$AppName="S-ERP";
$AppVersion='2.O';
$CopyRight="©".date('Y').  " - SilverLights Software Solutions";
$IncludesPath=$RootFolder."/partials/";


$BaseUrl = "https://$_SERVER[HTTP_HOST]";
$ApiUrl=$BaseUrl.'/Api/ApiV1.php';
$PageTitleLink="index";
$path=$_SERVER[REQUEST_URI]; 
 
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


$db_name='u177334400_dev_serp';
$db_user='u177334400_dev_serp';
$db_password='lV#7]i^k';
$db_host='localhost';

$db_name='u177334400_serp';
$db_user='u177334400_serp';
$db_password='@4pU3XSq@$';
$db_host='localhost';	

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

if($BaseDist==''){
 $BaseDist="https://serp.silverlights.in/Basewrapper/Nazox_v1.0/Admin/Vertical/dist/";	 
}
}	





?>