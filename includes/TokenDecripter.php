<?php
$IsLogin=true;
session_start() ;
session_destroy() ;

$_SESSION["IsLogin"]="LoginPage";
include ($_SERVER['DOCUMENT_ROOT']."/SerpDbConfig/config.php");

$UserID=$Token;

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
 
 

$StmtLogin=$iconn->query($SqlLogin);
$RowLoginData=$StmtLogin->fetch_array();

if($StmtLogin->num_rows==1){
	
$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$CurrentDateTime=$dateTime->format("Y-m-d  H:i:s"); 
		$UserID=$RowLoginData["UserID"];
		$Status=$RowLoginData["Status"];
		if($Status==1){
		
	
		$sql_update="UPDATE AdminLoginDetails SET `LastLogin`='$CurrentDateTime'
		WHERE `UserID`='$UserID'";
		
		$up=$iconn->query($sql_update);
	
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
	
	
	$DbHost=$_SESSION["DbHost"];
	$DbName=$_SESSION["DbName"];
	$DbUserName=$_SESSION["DbUserName"];
	$DbPassword=$_SESSION["DbPassword"];
	
	

 $iconn = mysqli_connect("$DbHost","$DbUserName","$DbPassword","$DbName") or die("DB Error!".mysqli_error()); 
 

	
	
}
}

?>