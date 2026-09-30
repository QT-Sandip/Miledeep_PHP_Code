<?php


	echo $DbHost=$_SESSION["DbHost"];
	echo  $DbName=$_SESSION["DbName"]; 
	echo $DbUserName=$_SESSION["DbUserName"];
	echo	$DbPassword=$_SESSION["DbPassword"];
		$DeptID=$_SESSION["DeptID"];

		$AuthConn = mysqli_connect("$DbHost","$DbUserName","$DbPassword","$DbName") or die("Client Db Error! Invalid Credential  Error In Admin Login only".mysqli_error()); 
	
 

	/*  $SqlAuth="SELECT * from AdminDepartments where DeptID='$DeptID'"; 
	  $StmtAuth=$AuthConn->query($SqlAuth);
	  $RowAuthData=$StmtAuth->fetch_array();
	  
	  $_SESSION["SideBarHeaderTbl"]=$RowAuthData["SideBarHeaderTbl"]; 
	  $_SESSION["SideBarOptionTbl"]=$RowAuthData["SideBarOptionTbl"]; */
?>