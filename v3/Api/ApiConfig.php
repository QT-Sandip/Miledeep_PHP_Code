<?php 
session_start();

 $RootFolder=$_SERVER['DOCUMENT_ROOT']; 
 $TcpdfPath= $RootFolder."/Libraries/tcpdf/tcpdf.php";
 $cp=$RootFolder."/".$_SESSION["IncludePath"];  
 
 
 include ($cp."Includes/DbTables.php"); //remove Once Fixed
 
 $_SERVER['PHP_SELF'];

 
$Startyear=$_SESSION["StartYear"];
$Currentyear=date('Y');
$endYear=$Currentyear+1;


$Provider="SilverLights Software Solutions";
$AppName="Camaroo ";
$AppVersion='2.O';
$CopyRight="©".date('Y').  " - SilverLights Software Solutions";
$IncludesPath=$RootFolder."/partials/";


$BaseUrl = "https://$_SERVER[HTTP_HOST]";
$ApiUrl=$_SESSION["SideBarPath"].'Api/ApiV1.php';// uma Nov-03-2022
$PageTitleLink="index";
$path=$_SERVER[REQUEST_URI]; 
 
 $info = new SplFileInfo($path);
 $PageExtension= $info->getExtension();  
if($PageExtension=='php'){
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $path=substr($path, 0, -4);
   // header('Location: '.$path);
}
}



$UserID=$_SESSION["UserID"];
if(isset($_GET['Token'])){
$Token=$_GET['Token'];	
include "TokenDecripter.php"; 
 $UserID=$_SESSION["UserID"];	
echo	$DbName=$_SESSION["DbName"]; //exit;	
}


$IsLogin= isset($IsLogin) ? $IsLogin:false;

if(!$IsLogin && $UserID==''){
	//header('Location: '. $BaseUrl);
}


	$DbHost="localhost";
	$DbName="u177334400_sme";
	$DbUserName="u177334400_sme";
	$DbPassword="_0d21gjF";
	
		$DbHost=$_SESSION["DbHost"];
	$DbName=$_SESSION["DbName"];
	$DbUserName=$_SESSION["DbUserName"];
	$DbPassword=$_SESSION["DbPassword"];
	

  $iconn = mysqli_connect("$DbHost","$DbUserName","$DbPassword","$DbName") or die("DB Error!".mysqli_error()); 
 
 $CurrentClient=$_SESSION["SideBarPath"];

  $MailTempFolder=$RootFolder."/TempAutoMails/";
  $MailReadTempFolder=$BaseUrl."/TempAutoMails/";

/*
if(!checkClient()){
	 header('Location: '. $BaseUrl);
}

*/

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
 $BaseDist="https://uiwrapper.silverlights.in/Basewrapper/Nazox_v1.0/Admin/Vertical/dist/";	 
}
}	

/*
function checkClient(){
 
 $myVar = $_SERVER[REQUEST_URI];
 $myVar=explode('/',$myVar);
 $BaseUrl = "https://$_SERVER[HTTP_HOST]";
 
 $CurrentLoginUrl = str_replace($BaseUrl, '', $_SESSION["SideBarPath"]);
  
  $CurrentUrl= "/".$myVar[1]."/".$myVar[2]."/";
  
  if($CurrentUrl!=$CurrentLoginUrl){
	 
	 return false;
  }else{
  return true;
  }

}
	*/
	


?>